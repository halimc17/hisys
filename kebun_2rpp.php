<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2rpp').'</span>');

?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<script language=javascript1.2 src='js/kebun_2rpp.js?ver=1.1'></script>
<script language="javascript" src="js/zMaster.js"></script>
<link rel=stylesheet type=text/css href="style/zTable.css">


<?


$optorg=$optper="";
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(3) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optPT.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optPT.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optPT.="</optgroup>";
	}
}
// $optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'",'2','0',true);

$optorg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' order by induk asc, namaorganisasi asc ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$d=getNamaOrg($bar['kodeorganisasi'],'induk');
// 	if($d!=$n){			
// 		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
// 	}
//     $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// 	$n=$d;
// 	if($d!=$n){			
// 		$optorg.="</optgroup>";
// 	}
// }

foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}


$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())

{
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTt="";
$str = "select distinct(a.tahuntanam) as tt from ".$dbname.".setup_blok a 
		left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
		where b.induk like '%".@key($optPT)."%' and (a.tahuntanam!='0' or a.tahuntanam!='') 
		order by a.tahuntanam asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optTt.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
    $optTt.="<option value=" . $bar['tt'] . ">".$bar['tt']."</option>";
}

$arrIP=array("I"=>"INTI","P"=>"PLASMA");
$optIP="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrIP as $res => $bar)
{
	$optIP.="<option value=".$res.">".$bar."</option>";
}

$frm[0]='';
$frm[1]='';
if($_SESSION['language']=='EN'){
    $cap1='PRODUCTION ACHIEVEMENT UNTIL';
    $cap2='ACHIEVEMENT GRAPH UNTIL ';
    $cap3='PRODUCTION vs BUDGET UNTIL';
}else{
    $cap1='REKAPITULASI PENCAPAIAN PRODUKSI SD TGL';
    $cap2='GRAFIK SD TGL ';
    $cap3='PRODUKSI DAN BUDGET SD TGL'; 
}
$arr1 = "##kdorg##tgl2";
echo"<div id=tableheader>";
$frm[0]="<fieldset>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1 width=100%>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg style=\"width:158px;\">" . $optorg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td>
                    <input style=\"width:155px;\" type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='22' maxlength='10' readonly></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button id=tomboldetail class=mybutton onclick=prev1()>".$_SESSION['lang']['preview']."</button>
                    </td>
                </tr>
            </table>
</fieldset>
	";

$arr2 = "##pt2##kdorg2##per2##divisi##tt##ip";
$frm[1]="<fieldset>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1 width=100%>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=pt2 onchange=getUnitThnTnm(this,'kdorg2,tt','divisi','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg2 style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=divisi onchange=getThnTnm(this,'tt','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optDiv . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tt style=\"width:164px;\">" . $optTt . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['intiplasma'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=ip style=\"width:164px;\">" . $optIP . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td>
						<select class=select2 id=per2 style=\"width:164px;\">" . $optper . "</select>
                    </td>
                </tr>
                
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=\"zPreview('kebun_slave_2rpp_v2','" . $arr2 . "','printContainerv2');showheader();\" class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2rpp_v2.php','" . $arr2 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
$hfrm[0]=$_SESSION['lang']['rekap']." / ".$_SESSION['lang']['unitkerja'];
$hfrm[1]=$_SESSION['lang']['rekap']." / ".$_SESSION['lang']['divisi'];

drawTab('FRM',$hfrm,$frm,'','300px');	
echo"</div>";
CLOSE_BOX();


OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainerv2' class='table-scroll' style='overflow:auto;height:73vh;'></div>";

echo"<div id=prev style=display:none>
			<table width=100% border=0>
				<tr>
					<td colspan=2 valign=top>
						<fieldset><legend><b>".$cap1." : <span id=isitglr></span></b></legend>
						<div id=prev1>
						</div>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td valign=top>
						<fieldset><legend><b>".$cap2.": <span id=isitglg></span></b></legend>
						<div id=prev2>
						</div>
						</fieldset>
					</td>
					<td valign=top>
						<fieldset><legend><b>".$cap3." : <span id=isitglp></span></b></legend>
						<div id=prev3>
						</div>
						</fieldset>
					</td>
				</tr>
			</table>			
		</div>";
CLOSE_BOX();
echo close_body();
?>