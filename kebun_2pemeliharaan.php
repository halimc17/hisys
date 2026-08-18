<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
//$optOrg="<option value=\"\">".$_SESSION['lang']['pilihdata']."</option>";
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')or($_SESSION['empl']['tipelokasitugas']=='KANWIL'))
// {
// 	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') order by namaorganisasi asc ";	
// }
// else
// {
// 	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
// }
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch())
// {
// 	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
// }
$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
	$optOrg.="<option value='".$key."'>".$key." - ".$val."</option>";			
}

$sTah="select substr(tanggal,1,4) as tahun from ".$dbname.".kebun_aktifitas group by substr(tanggal,1,4) order by tahun desc";
$qTah=$owlPDO->query($sTah) or die(print " Gagal: ".PDOException::getMessage());
$qTah->setFetchMode(PDO::FETCH_ASSOC);
$optTah="";
while($rTah=$qTah->fetch())
{
	$optTah.="<option value=".$rTah['tahun'].">".$rTah['tahun']."</option>";
}
 
$optKeg="<option value=\"\">".$_SESSION['lang']['all']."</option>";
if($_SESSION['language']=='EN'){
    $zz='namakegiatan1 as namakegiatan';
}else{
    $zz='namakegiatan';
}
$sKeg="select kodekegiatan, ".$zz.", kelompok from ".$dbname.".setup_kegiatan"
        . " where kelompok in ('BBT','TM','TB','TBM','PNN') order by kodekegiatan asc";
$qKeg=$owlPDO->query($sKeg) or die(print " Gagal: ".PDOException::getMessage());
$qKeg->setFetchMode(PDO::FETCH_ASSOC);
while($rKeg=$qKeg->fetch())
{
	$optKeg.="<option value=".$rKeg['kodekegiatan'].">".$rKeg['kodekegiatan']." - ".$rKeg['namakegiatan']." (".$rKeg['kelompok'].")</option>";
}


$nmbarang=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optbarang="<option value=''>".$_SESSION['lang']['all']."</option>";
$ibarang="select distinct(kodebarang) as kodebarang from ".$dbname.".kebun_pakaimaterial order by kodebarang asc ";
$nbarang=$owlPDO->query($ibarang) or die(print " Gagal: ".PDOException::getMessage());
$nbarang->setFetchMode(PDO::FETCH_ASSOC);
while($dbarang=$nbarang->fetch())
{   
  // $whbarang="kodebarang='".$dbarang['kodebarang']."'";
   $optbarang.="<option value=".$dbarang['kodebarang'].">".$dbarang['kodebarang']." - ".$nmbarang[$dbarang['kodebarang']]."</option>";
}

$arr="##kdOrg##kdAfd##tgl1##tgl2##kegiatan##sumber##kdbarang##intiplasma##tipereport";
$arr1="##kdOrg1##kdAfd1##tahun1##kegiatan1";
$optSumber="<option value=\"BKM\">BKM</option>";
//$optSumber.="<option value=\"SPK\">SPK</option>";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/kebun_2pemeliharaan.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel=stylesheet type='text/css' href='style/zTable.css'>
<script>
function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){		
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide Filter";
		document.getElementById('tombolexport').style.display="none";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('tombolexport').style.display="block";
		document.getElementById('showhead').innerHTML="Show Filter";
	}	
}
</script>

<?php    

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2pemeliharaan').'</span>');

$title[0]=$_SESSION['lang']['pemeltanaman'];
$title[1]=$_SESSION['lang']['rotasi']." ".$_SESSION['lang']['pemeltanaman'];
echo"<div id=tableheader>";
$frm[0]="<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr><td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td><td><select class=select2 id=\"kdOrg\" name=\"kdOrg\" style=\"width:155px\" onchange=\"getAfd()\">".$optOrg."</select></td>
<td><label>".$_SESSION['lang']['afdeling']."</label></td><td>:</td><td><select class=select2 id=\"kdAfd\" name=\"kdAfd\" style=\"width:150px\"><option value=\"\"></option></select></td></tr>
<tr><td><label>".$_SESSION['lang']['tanggal']."</label></td><td>:</td><td>
<input type=\"text\" class=\"myinputtext\" id=\"tgl1\" name=\"tgl1\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:60px;\" readonly/> s.d.
<input type=\"text\" class=\"myinputtext\" id=\"tgl2\" name=\"tgl2\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:60px;\" readonly/></td>
<td><label>".$_SESSION['lang']['kegiatan']."</label></td><td>:</td><td><select class=select2 id=\"kegiatan\" name=\"kegiatan\" onchange=getbarang(this.value); style=\"width:150px\">".$optKeg."</select><img onclick=\"z.elSearch('kegiatan',event)\" class=\"resicon\" src=\"images/onebit_02.png\" style=\"position:relative;top:3px;left:3px;\"></td></tr>
<tr><td><label>".$_SESSION['lang']['sumber']."</label></td><td>:</td><td><select class=select2 id=\"sumber\" name=\"sumber\" style=\"width:155px\">".$optSumber."</select></td>
<td><label>".$_SESSION['lang']['namabarang']."</label></td><td>:</td><td><select class=select2 id=\"kdbarang\" name=\"kdbarang\" style=\"width:150px\">".$optbarang."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['intiplasma']."</label></td><td>:</td><td><select class=select2 id=\"intiplasma\" name=\"intiplasma\" style=\"width:155px\"><option value=''>".$_SESSION['lang']['all']."</option><option value='I'>Inti</option><option value='P'>Plasma</option></select></td><td><label>Tipe Report</label></td><td>:</td><td><select class=select2 id=\"tipereport\" name=\"tipereport\" style=\"width:150px\"><option value='pertransaksi'>Per Transaksi</option><option value='perkaryawan'>Per Karyawan</option></select></td></tr>

<tr><td colspan=\"2\"><td colspan=\"2\">
    <button onclick=\"zPreview('kebun_slave_2pemeliharaan','".$arr."','printContainer');showheader();\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'kebun_slave_2pemeliharaan.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    <button onclick=\"Clear0()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button></td></tr>
</table>
</fieldset>
";

$frm[1]="<fieldset>
<legend><b>".$title[1]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr><td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td><td><select class=select2 id=\"kdOrg1\" name=\"kdOrg1\" style=\"width:150px\" onchange=\"getAfd1()\">".$optOrg."</select></td>
<td><label>".$_SESSION['lang']['afdeling']."</label></td><td>:</td><td><select class=select2 id=\"kdAfd1\" name=\"kdAfd1\" style=\"width:150px\"><option value=\"\"></option></select></td></tr>
<tr><td><label>".$_SESSION['lang']['tahun']."</label></td><td>:</td><td><select class=select2 id=\"tahun1\" name=\"tahun1\" style=\"width:150px\"><option value=\"\">".$_SESSION['lang']['pilihdata']."</option>".$optTah."</select></td>
<td><label>".$_SESSION['lang']['kegiatan']."</label></td><td>:</td><td><select class=select2 id=\"kegiatan1\" name=\"kegiatan1\" style=\"width:150px\">".$optKeg."</select>
<img id='kegiatan1' onclick=z.elSearch('kegiatan1',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
</td></tr>

<tr><td colspan=\"2\"><td colspan=\"2\">
    <button onclick=\"zPreview('kebun_slave_2pemeliharaan1','".$arr1."','printContainer');showheader();\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'kebun_slave_2pemeliharaan1.php','".$arr1."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    <button onclick=\"Clear1()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button></td></tr>
</table>
</fieldset>";
//========================
$hfrm[0]=$title[0];
$hfrm[1]=$title[1];
drawTab('FRM',$hfrm,$frm,'','500px');
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
echo "<div id='printContainer' class='table-scroll' style='height:73vh;'></div>";

CLOSE_BOX();
echo close_body();
?>