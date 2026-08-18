<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script languange=javascript1.2 src='js/tax_buktipotongpajak.js?v=1.2'></script>
<?


OPEN_BOX('','<span class=judul>'.strtoupper(getmenu('tax_buktipotongpajak')).'</span>');
#== Prep Option & Query

$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
//$optOrg1 = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","tipe='HOLDING' and kodeorganisasi='".$idOrg."'",'',true);

		
$optOrg=array(''=>$_SESSION['lang']['pilihdata']);
$optAkun=array(''=>$_SESSION['lang']['pilihdata']);
$optSupplier=array(''=>$_SESSION['lang']['all']);
$optPeriode = array(''=>$_SESSION['lang']['pilihdata']);
$optPeriode1= array(''=>$_SESSION['lang']['pilihdata']);
$optOrg1= array(''=>$_SESSION['lang']['pilihdata']);
$optSupplier2=array(''=>$_SESSION['lang']['pilihdata']);
$optAkun1=array(''=>$_SESSION['lang']['pilihdata']);
$str="select kodeorganisasi,namaorganisasi from  ".$dbname.".organisasi 
	  where char_length(kodeorganisasi)=4";
$res=fetchData($str);
foreach ($res as $key => $val) {
	//$optOrg[$val['kodeorg']]=$val['kodeorg']."-".$val['namaorganisasi'];
	$nm[$val['kodeorganisasi']]=$val['namaorganisasi'];
}
 
$lstUnit=getOrgDetail(1);
$dtMul=0;
$listOrg='';
foreach($lstUnit as $row=>$isiDt){
    if(substr($row,0,5)=='Pilih'){
        continue;
    }
    if (substr($row,2,2)!='HO' and substr($row,2,2)!='RO') {
    	continue;
    }
    $optOrg[$row]=$row."-".$nm[$row];
	// if(!isset($optOrg[$row])){
	// 	unset($optOrg[$row]);
	// }
}
$optSupplier3=$optAkun1=$optPeriode1=$optOrg1="<option value>".$_SESSION['lang']['pilihdata']."</option>";
$sSupKpp="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='PAJAK')";
// echo $sSupKpp;
$rSupKpp=fetchData($sSupKpp);
foreach ($rSupKpp as $key => $val) {
	$optSupplier2[$val['supplierid']]=$val['supplierid']."-".$val['namasupplier'];
	$optSupplier3.="<option value='".$val['supplierid']."'>".$val['supplierid']."-".$val['namasupplier']."</option>";
}





$str="select distinct periode from ".$dbname.".tax_buktipotongpajak order by periode desc";
$res=fetchData($str);
foreach ($res as $key => $val) {
    //$optPeriode1[$val['periode']]=$val['periode'];
    $optPeriode1.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}
$str="select distinct kodeorg from ".$dbname.".tax_buktipotongpajak order by kodeorg desc";
$res=fetchData($str);
foreach ($res as $key => $val) {
    $optOrg1.="<option value='".$val['kodeorg']."'>".$val['kodeorg']."</option>";
}
$str="select distinct a.noakun, b.namaakun from ".$dbname.".keu_kasbankdt a 
	  left join ".$dbname.".keu_5akun b on a.noakun=b.noakun
	  where  a.noakun like '213%' and char_length(a.noakun)=7 order by a.noakun asc";
$res=fetchData($str);
foreach ($res as $key => $val) {
	$optAkun1.="<option value='".$val['noakun']."'>".$val['noakun']."-".$val['namaakun']."</option>";
}
// $str="select a.kodesupplier, b.namasupplier from ".$dbname.".keu_kasbankdt a 
	  // left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid group by a.kodesupplier";
// $res=fetchData($str);
// foreach ($res as $key => $val) {
	// $optSupplier[$val['kodesupplier']]=$val['kodesupplier']."-".$val['namasupplier'];
// }

#== Prep List


$tblCr="<table cellspacing=1 border=0>";
$tblCr.="<tr valign=moiddle>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=adddataform()>";
$tblCr.="<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=clearDisplay()>";
$tblCr.="<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>";



#== Prep Form 

# Elements
// $elscari = array();
// $elscari[] = array(
//     makeElement('kodeorgcr','label',$_SESSION['lang']['unit']),
//     makeElement(':','label',':'),
//     makeElement('kodeorgcr','select','',array('style'=>'width:150px'),$optOrg1),
//     makeElement('periodecr','label',$_SESSION['lang']['periode']),
//     makeElement(':','label',':'),
//     makeElement('periodecr','select','',array('style'=>'width:150px'),$optPeriode1),
//     makeElement('noakuncr','label',$_SESSION['lang']['noakun']),
//     makeElement(':','label',':'),
//     makeElement('noakuncr','select','',array('style'=>'width:150px'),$optAkun1),
//     makeElement('caridata','button',$_SESSION['lang']['find'],array('onclick'=>'caridata()'))
// );

$els = array();
$els[] = array(
    makeElement('kodeorg','label',$_SESSION['lang']['unit']),
    makeElement(':','label',':'),
    makeElement('kodeorg','select','',array('style'=>'width:150px','onchange'=>'getperiode()'),$optOrg)
);
$els[] = array(
    makeElement('periode','label',$_SESSION['lang']['periode']),
    makeElement(':','label',':'),
    makeElement('periode','select','',array('style'=>'width:150px','onchange'=>'getnoakun()'),$optPeriode)
);
$els[] = array(
    makeElement('noakun','label',$_SESSION['lang']['noakun']),
    makeElement(':','label',':'),
    makeElement('noakun','select','',array('style'=>'width:150px','onchange'=>'getsupp()'),$optAkun)
);
$els[] = array(
    makeElement('npwp','label',$_SESSION['lang']['npwp']),
    makeElement(':','label',':'),
    makeElement('npwp','select','',array('style'=>'width:150px'),$optPeriode)
);

$els[] = array(
    makeElement('supplier_kpp','label',"KPP"),
    makeElement(':','label',':'),
    makeElement('supplier_kpp','selectsearch','',array('style'=>'width:150px'),$optSupplier2)
);
$els[] = array(
    makeElement('supplier','label',$_SESSION['lang']['namasupplier']),
    makeElement(':','label',':'),
    makeElement('supplier','selectsearch','',array('style'=>'width:150px'),$optSupplier)
);






$elsbutton['btn'] = array(
    makeElement('viewList','button',$_SESSION['lang']['proses'],array('onclick'=>'viewList()')),
    makeElement('viewList','button',$_SESSION['lang']['excel'],array('onclick'=>'viewListExcel(event)'))
);

#===== Show =======
echo "<div id=headerjudul>";
echo $tblCr;
echo "<td><fieldset id='formcari' clear:right;min-height:auto;'>";
echo "<legend>".$_SESSION['lang']['find']."</legend>";
//echo genElement($elscari);
echo"<table>
<tr>";
echo"<td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select id=kodeorgcr style='width:150px'>".$optOrg1."</select></td>";
echo"<td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select id=periodecr style='width:150px'>".$optPeriode1."</select></td>";
echo"<td>".$_SESSION['lang']['noakun']."</td><td>:</td><td><select id=noakuncr style='width:150px'>".$optAkun1."</select></td>";
echo"</tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['kasbank']."</td><td>:</td><td><input type=text class=myinputtext onkeypress='return tanpa_kutip(event)' style='width:150px' id=nokasCr /></td>";
echo"<td>".$_SESSION['lang']['noinvoice']."</td><td>:</td><td><input type=text class=myinputtext onkeypress='return tanpa_kutip(event)' style='width:150px' id=noinvCr /></td>";
echo"<td>KPP</td><td>:</td><td><select id=supplierIdKppcr style=width:150px>".$optSupplier3."</select></td>";
echo"</tr>";
echo "</table>
<button class=mybutton onclick=caridata()>".$_SESSION['lang']['find']."</button>
</fieldset></td></table>";
echo "</div>";
CLOSE_BOX();


OPEN_BOX();
echo "<div id=container>";
echo "</div>";
echo "<script>loadData()</script>";
CLOSE_BOX();


echo "<div id=forms style='display:none'>";
OPEN_BOX();
echo "<fieldset id='formheader' style='float:left'>";
echo "<legend>Cari List Data</legend>";

/*
echo"<table>
	<tr>
		<td>".$_SESSION['lang']['kodeorg']."</td>
		<td>:</td>
		<td><select id=kodeorg style=\"width:150px;\">'".$optOrg."'</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=periode style=\"width:150px;\">'".$optPeriode."'</select></td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['noakun']."</td>
		<td>:</td>
		<td><select id=noakun style=\"width:150px;\">'".$optAkun."'</select></td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['supplier']."</td>
		<td>:</td>
		<td><select id=supplier style=\"width:150px;\">'".$optSupplier."'</select></td>
	</tr>
	
	
	
	</table>
	";
*/	
echo genElement($els);
echo "<div id=hbutton>";
echo genElement($elsbutton);
// echo"<button id=savehead class=mybutton onclick=saveHeader()>".$_SESSION['lang']['save']."</button>";

echo "</div>";
echo "</fieldset>";
CLOSE_BOX();
echo "</div>";

echo "<div id=listtable style='display:none'>";
OPEN_BOX();
echo "<div id=tables>";
echo "</div>";
CLOSE_BOX();
echo "</div>";



echo close_body();
?>