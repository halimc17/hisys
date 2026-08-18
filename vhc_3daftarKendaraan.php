<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
error_reporting(0);

$optPeriode="";
for($x=0;$x<=24;$x++)
{
	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$optPeriode.="<option value=".date("Y-m",$dt).">".date("Y-m",$dt)."</option>";
}
// $sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'TRAKSI' ";
// $optKbn="";
// $qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
// $qKbn->setFetchMode(PDO::FETCH_ASSOC);
// while($rKbn=$qKbn->fetch())
// {
	// $optKbn.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
// }



//$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optKodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optKodeorg.="</optgroup>";
	}
}

$sJnsvhc="select jenisvhc,namajenisvhc from ".$dbname.".vhc_5jenisvhc order by namajenisvhc asc";
$optJns="";
$qJnsVhc=$owlPDO->query($sJnsvhc) or die(print " Gagal: ".PDOException::getMessage());
$qJnsVhc->setFetchMode(PDO::FETCH_ASSOC);
while($rJnsvhc=$qJnsVhc->fetch())
{
	$optJns.="<option value=".$rJnsvhc['jenisvhc'].">".$rJnsvhc['namajenisvhc']."</option>";
}
$arrklvhc=getEnum($dbname,'vhc_5master','kelompokvhc');
$optklvhc="";
foreach($arrklvhc as $kei=>$fal)
{
	switch($kei)
	{
		case 'AB':
				 $_SESSION['language']!='EN'?$fal='Alat Berat':$fal='Heavy Equipment';
		break;
		case 'KD':                            
				$_SESSION['language']!='EN'?$fal='Kendaraan':$fal='Vehicle';
		break;
		case 'MS':
				$_SESSION['language']!='EN'? $fal='Mesin':$fal='Machinery';
		break;		
	}
	$optklvhc.="<option value='".$kei."'>".$fal."</option>";
} 
$arr="##kdKbn##klpmkVhc";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/keu_2laporanAnggaranKebun.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?

OPEN_BOX('','<span class=judul>'.getMenu('vhc_3daftarKendaraan').'</span><br>');
?>
<div style="margin-bottom: 30px;">
<fieldset style="float: left;">
<legend><?php echo $_SESSION['lang']['form']?></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unittraksi']?></label></td><td>:</td><td><select class='select2' id="kdKbn" name="kdKbn" style="width:200px">
<option value="0"><?php echo $_SESSION['lang']['all']?></option><?php echo $optKodeorg?></select></td></tr>
<tr><td><label><?php echo @$_SESSION['lang']['kodekelompok']?></label></td><td>:</td><td><select class='select2' id="klpmkVhc" name="klpmkVhc" style="width:200px">
<option value="0"><?php echo $_SESSION['lang']['all']?></option><?php echo $optklvhc?></select></td></tr>
<tr><td><td><td><button onclick="zPreview('vhc_3slave_daftarKendaran','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('vhc_3slave_daftarKendaran','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'vhc_3slave_daftarKendaran.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer' class='table-scroll' style='height:450px;max-width:100%;'></div>
<?php
CLOSE_BOX();
echo close_body();
?>