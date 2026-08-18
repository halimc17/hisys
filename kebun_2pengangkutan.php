<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>

<?php
//for($x=0;$x<=24;$x++)
//{
//	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
//	$optPeriode.="<option value=".date("Y-m",$dt).">".date("Y-m",$dt)."</option>";
//}
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".kebun_spbht order by tanggal desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while($rTgl=$qTgl->fetch()){
	// $thn=explode("-", $rTgl['periode']);
 //   if($thn[1]=='12')
 //   {
 //   $optPeriode.="<option value='".substr($rTgl['periode'],0,4)."'>".substr($rTgl['periode'],0,4)."</option>";
 //   }
   $optPeriode.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}


$optAfdeling=$optPerusahaan=$optPabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
// $sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
// $qPabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
// $qPabrik->setFetchMode(PDO::FETCH_ASSOC);
// while($rPabrik=$qPabrik->fetch()){
// 	$optPerusahaan.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['kodeorganisasi']." - ".$rPabrik['namaorganisasi']."</option>";
// }

$arrunitpt = getOrgDetail(3);
foreach ($arrunitpt as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optPerusahaan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optPerusahaan.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optPerusahaan.="</optgroup>";
	}
}

// $sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
// $qPabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
// $qPabrik->setFetchMode(PDO::FETCH_ASSOC);
// while($rPabrik=$qPabrik->fetch()){
// 	$optPabrik.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['kodeorganisasi']." - ".$rPabrik['namaorganisasi']."</option>";
// }

$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optPabrik.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optPabrik.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optPabrik.="</optgroup>";
	}
}

$sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='DIVISI'";
$qPabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rPabrik=$qPabrik->fetch()){
	$optAfdeling.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['kodeorganisasi']." - ".$rPabrik['namaorganisasi']."</option>";
}

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
$optBrg="";
while($rBrg=$qBrg->fetch())
{
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}
$arr="##periode##idKebun";
?>
<script language=javascript src='js/option.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<!--<script language=javascript src=js/keu_2laporanAnggaranKebun.js></script>-->

<script language=javascript>
	function batal()
	{
		document.getElementById('periode').value='';	
		document.getElementById('idKebun').selectedIndex=0;
		document.getElementById('printContainer').innerHTML='';
	}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanPengangkutan']).'</span>');
?>
<div style="margin-bottom: 30px;">
<fieldset style="float:left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >

<tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td><td><select id="pt" name="pt" onchange="getUnitThnTnm('idKebun,thntanam,','idKebun,afdeling','<?php echo $_SESSION['lang']['all'] ?>')" style="width:180px"><?php echo $optPerusahaan?></select></td> </tr>
<tr><td><label><?php echo $_SESSION['lang']['kebun']?></label></td><td>:</td><td><select id="idKebun" name="idKebun" style="width:180px"  onchange="getAfdeling(this,'afdeling','afdeling,tahuntanam','<?php echo $_SESSION['lang']['all'] ?>','AFDELING')"  ><?php echo $optPabrik?></select></td></tr>
<tr hidden><td><label><?php echo $_SESSION['lang']['divisi']?></label></td><td>:</td><td><select id="afdeling" name="afdeling"  style="width:180px"><?php echo $optAfdeling?></select></td> </tr>
<tr><td><label><?php echo $_SESSION['lang']['tahuntanam']?></label></td><td>:</td><td><select id="thntanam" name="thntanam" onchange="changediv('this')" style="width:180px"><?php echo $optAfdeling?></select></td> </tr>
<tr>
<td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><select id="periode" name="periode" style="width:80px"><?php echo $optPeriode?></select> s.d <select id="periode2" name="periode2" style="width:80px"><?php echo $optPeriode?></select></td></tr>


<tr><td>&nbsp;</td><td colspan="2" align='right'><button onclick="zPreview('kebun_slave_2pengangkutan','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['preview']?></button>

<button onclick="zPdf('kebun_slave_2pengangkutan','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['pdf']?></button>

<button onclick="zExcel(event,'kebun_slave_2pengangkutan.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['excel']?></button>

<button onclick="batal()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button></td></tr>
</table>
</fieldset>
</div>

<?
CLOSE_BOX();
OPEN_BOX();
?>
<div id='both_report'>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'>


</div></fieldset></div>
<?
CLOSE_BOX();
echo close_body();
?>