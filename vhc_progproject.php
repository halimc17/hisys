<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script languange=javascript1.2 src='js/vhc_progproject.js'></script>

<?


OPEN_BOX('','<span class=judul>'.getMenu('vhc_progproject.php').'</span>');
#== Prep Option & Query

$idOrg=substr($_SESSION['empl']['kodeorganisasi'],0,4);

$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","induk='".$idOrg."' and tipe in ('KEBUN','PABRIK')",'2',true);
//$optMinggu = array(''=>'','1'=>'Ke-1','2'=>'Ke-2','3'=>'Ke-3','4'=>'Ke-4');

$optlok=$optpek = "<option value=''></option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
$str="select DISTINCT (a.kodeorg) as kodeorg, b.namaorganisasi from ".$dbname.".vhc_progproject a 
left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi order by a.kodeorg asc";

$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","tipe in ('KEBUN','PABRIK')",'2',true);
}
else
{
$str="select DISTINCT (a.kodeorg) as kodeorg , b.namaorganisasi from ".$dbname.".vhc_progproject a 
left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where b.induk='".$idOrg."' and tipe in ('KEBUN','PABRIK') order by a.kodeorg asc";    
}
$res = fetchData($str);
foreach($res as $key => $val){
	$optlok.="<option value=".$val['kodeorg'].">".$val['namaorganisasi']."</option>";	
}


#== Prep List

$header = array("No","Lokasi","Pekerjaan","Minggu Ke-","Tanggal Awal","Tanggal Akhir");
$table ="<fieldset id='fieldForm' clear:right;min-height:auto;'>";
$table .="<legend>".$_SESSION['lang']['list']."</legend>";
$table .= "<fieldset style=float:left><legend>Find</legend><table>
		<tr>
			<td>Lokasi</td><td>:</td><td><select style=\"width:150px;\" id=loksrc>" . $optlok . "</select></td>
			<td>Pekerjaan</td><td>:</td><td><input style=\"width:150px;\" id=peksrc class=myinputtext></td>
			<td>Minggu</td><td>:</td><td><input style=\"width:50px;\" id=mgsrc class=myinputtext></td>
		</tr>
		<tr>
			<td>Tanggal Awal</td><td>:</td><td><input type='text' style='width:145px;' class='myinputtext' id='tglawal' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
			<td>Tanggal Akhir</td><td>:</td><td><input type='text' style='width:145px;' class='myinputtext' id='tglakhir' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
			<td colspan=3><button class=mybutton onclick=loadData()>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=batal()>" . $_SESSION['lang']['cancel'] . "</button></td>
		</tr>
	</table></fieldset><div style=clear:both></div><hr>";
$table .= "<table cellspacing='1' border='0' class='sortable' style=min-width:600px>";
$table .= "<thead><tr class='rowheader'>";
foreach($header as $head) {
    $table .= "<td align=center>".$head."</td>";
}
$table .= "<td style='width:30px;' colspan=5 align=center>Action</td>";
$table .= "</tr></thead>";
$table .= "<tbody id='bodyList'>";
$table .= "<script>loadData()</script>";
$table .= "</tbody>";
$table .= "<tfoot id='tfootlist'>";
$table .= "</tfoot>";
$table .= "</table>";
$table .= "</fieldset>";

$tblCr="<table cellspacing=1 border=0>";
$tblCr.="<tr valign=moiddle>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=adddataform()>";
$tblCr.="<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=loadData()>";
$tblCr.="<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>";



#== Prep Form 

# Elements
/*$elscari = array();
$elscari[] = array(
    makeElement('kodeorgcr','label','Kode Organisasi'),
    makeElement(':','label',':'),
    makeElement('kodeorgcr','select','',array('style'=>'width:190px'),$optOrg),
    makeElement('periodecr','label','Periode'),
    makeElement(':','label',':'),
    makeElement('periodecr','select','',array('style'=>'width:100px'),$optPeriode),
    makeElement('carijdwl','button',$_SESSION['lang']['find'],array('onclick'=>'caridata()'))
);*/

$elshead = array();
$elshead[] = array(
    makeElement('Lokasi','label','Lokasi'),
    makeElement(':','label',':'),
    makeElement('kodeorg','select','',array('style'=>'width:190px','onchange'=>'getproject()'),$optOrg)
);
$elshead[] = array(
    makeElement('Project','label','Project'),
    makeElement(':','label',':'),
    makeElement('kodeproject','select','',array('style'=>'width:105px'))
);
$elshead[] = array(
    makeElement('minggu','label','Minggu Ke-'),
    makeElement(':','label',':'),
    makeElement('minggu','textnum','',array('style'=>'width:100px'))
);
$elshead[] = array(
    makeElement('tanggalawal','label','Tanggal Awal'),
    makeElement(':','label',':'),
    makeElement('tanggalawal','tanggal','',array('style'=>'width:100px'))
);
$elshead[] = array(
    makeElement('tanggalakhir','label','Tanggal Akhir'),
    makeElement(':','label',':'),
    makeElement('tanggalakhir','tanggal','',array('style'=>'width:100px'))
);
$elsheadbutton['btn'] = array(
    makeElement('saveButton','button',$_SESSION['lang']['save'],array('onclick'=>'checkHeader()')),
    makeElement('clearButton','button',$_SESSION['lang']['clear'],array('onclick'=>'clearHeader()'))
);

#===== Show =======
echo "<div id=headerjudul>";
echo $tblCr;
/*echo "<td><fieldset id='formcari' clear:right;min-height:auto;'>";
echo "<legend>".$_SESSION['lang']['find']."</legend>";
echo genElement($elscari);
echo "</fieldset></td></table>";*/
echo "</table></div>";

CLOSE_BOX();
# Active Form


echo "<div id=header style='display:none'>";
OPEN_BOX();
echo "<fieldset id='formheader' style='float:left'>";
echo "<legend>".$_SESSION['lang']['header']."</legend>";
echo "<input type=hidden id='kode' />";
echo genElement($elshead);
echo "<div id=hbutton>";
echo genElement($elsheadbutton);
echo "</div>";
echo "</fieldset>";
CLOSE_BOX();
echo "</div>";


echo "<div id=Detail style='display:none'>";
OPEN_BOX();
echo "<div id=detailform >";
echo "</div>";
CLOSE_BOX();
echo "</div>";

echo "<div id=listtabledetail style='display:none'>";
OPEN_BOX();
echo "<div id=tabledetail >";
echo "</div>";
CLOSE_BOX();
echo "</div>";

# Table
echo "<div id=container>";
OPEN_BOX();
echo $table;
CLOSE_BOX();
echo "</div>";

echo close_body();
?>