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
$optPeriode=$optPerusahaan=$optLaporan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sTgl="select distinct substr(tanggal,1,4) as periode from ".$dbname.".kebun_aktifitas WHERE tipetransaksi NOT IN ('BKM','PNN')
order by substr(tanggal,1,4) desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while($rTgl=$qTgl->fetch()){
   $optPeriode.="<option value='".$rTgl['periode']."'>".$rTgl['periode']."</option>";
}


$optAfdeling=$optKebun=$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' and kodeorganisasi in 
(
    select induk from ".$dbname.".organisasi where kodeorganisasi in (".getOrgDetail(24).")
)";
$qPabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rPabrik=$qPabrik->fetch()){
	$optPerusahaan.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['kodeorganisasi']." - ".$rPabrik['namaorganisasi']."</option>";
}

$sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and kodeorganisasi in (".getOrgDetail(24).")";
$qPabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rPabrik=$qPabrik->fetch()){
	$optKebun.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['kodeorganisasi']." - ".$rPabrik['namaorganisasi']."</option>";
}

$sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe IN ('AFDELING','BIBITAN') and length(kodeorganisasi)=6 and kodeorganisasi in (".getOrgDetail(20).")";
$qPabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rPabrik=$qPabrik->fetch()){
	$optAfdeling.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['kodeorganisasi']." - ".$rPabrik['namaorganisasi']."</option>";
}

$sLap="SELECT idlaporan,namalaporan FROM $dbname.kebun_5getpokokreport WHERE status='1' ORDER BY namalaporan asc";
$qLap=$owlPDO->query($sLap) or die(print " Gagal: ".PDOException::getMessage());
$qLap->setFetchMode(PDO::FETCH_ASSOC);
while($rLap=$qLap->fetch()){
	$optLaporan.="<option value=".$rLap['idlaporan'].">".$rLap['namalaporan']."</option>";
}

$arrjenis = array(
    'bkm'  => 'BKM Rawat',
    'spk'  => 'Borongan SPK'
);
foreach ($arrjenis as $key => $val) {
    $opttipe .= "<option value='".$key."'>".$val."</option>";
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
<script language=javascript src='js/kebun_2laporanmonograf.js?v=<?php echo time(); ?>'></script>
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
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2laporanmonograf').'</span>');
?>
<div style="margin-bottom: 30px;">
<fieldset style="float:left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >

<tr>
    <td>
        <label><?php echo $_SESSION['lang']['pt']?></label>
    </td>
    <td>:</td>
    <td>
        <select id="pt" name="pt" onchange="getUnitKebun()" style="width:180px"><?php echo $optPerusahaan?></select>
    </td> 
</tr>
<tr>
    <td>
        <label><?php echo $_SESSION['lang']['kebun']?></label>
    </td>
    <td>:</td>
    <td>
        <select id="idKebun" name="idKebun" style="width:180px"  onchange="getDivisiKebun()"><?php echo $optKebun?></select>
    </td>
</tr>
<tr>
    <td>
        <label><?php echo $_SESSION['lang']['divisi']?></label>
    </td>
    <td>:</td>
    <td>
        <select id="afdeling" name="afdeling" style="width:180px"><?php echo $optAfdeling?></select>
    </td>
</tr>
<tr>
    <td>
        <label><?php echo $_SESSION['lang']['namalaporan']?></label>
    </td>
    <td>:</td>
    <td>
        <select id="idlaporan" name="idlaporan" style="width:180px"><?php echo $optLaporan ?></select>
    </td>
</tr>
<tr>
    <td>
        <label><?php echo $_SESSION['lang']['jenis']?></label>
    </td>
    <td>:</td>
    <td>
        <select id="tipelaporan" name="tipelaporan" style="width:180px"><?php echo $opttipe ?></select>
    </td>
</tr>
<tr>
    <td>
        <label><?php echo $_SESSION['lang']['periode']?></label>
    </td>
    <td>:</td>
    <td>
        <select id="periode" name="periode" style="width:180px"><?php echo $optPeriode?></select>
    </td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td colspan="2" align='right'>
        <button onclick="preview('html')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['preview']?>
        </button>

        <button onclick="preview('excel')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['excel']?>
        </button>

        <button onclick="batal()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?>
        </button>
    </td>
</tr>
</table>
</fieldset>
</div>

<?
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer' class='table-scroll' style='max-height:55vh'>
</div>
<?
CLOSE_BOX();
echo close_body();
?>