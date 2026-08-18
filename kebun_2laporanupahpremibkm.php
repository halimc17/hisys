<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>

<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<!-- JS ga di pake karena pake zReport.js -->
<!-- Jika ada tambahan fitur bisa pake JS, seperti onchange, dlsb -->
<script language=javascript src=js/kebun_2laporanupahpremibkm.js?ver=<?= time(); ?>></script>

<?
	#= Make Option
	$tipekaryawan = makeOption($dbname,"sdm_5tipekaryawan","id,tipe");

	#= Set Default Option
	$optUnit  = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optDiv   = "<option value=''>".$_SESSION['lang']['all']."</option>";
	$optTipe   = "<option value=''>".$_SESSION['lang']['all']."</option>";

	#= untuk unit ht
	$arrunit=array();
	$arrunit=getOrgDetail(23);
	foreach($arrunit as $val => $nama){
		// if($val == "")
		$optUnit.="<option value='".$val."'>".$val." - ".$nama."</option>";
		$arrkodeunit[$val]=$val;
	} 

	#= Option Tipe Karyawan
	$qTipe = selectQuery($dbname,"sdm_5tipekaryawan","id"," aktif='1' and id!=0");
	$rTipe=$owlPDO->query($qTipe) or die(print " Gagal: ".PDOException::getMessage());
	$rTipe->setFetchMode(PDO::FETCH_ASSOC);
	while($resTipe=$rTipe->fetch()){
		$optTipe.="<option value=".$resTipe['id'].">".$tipekaryawan[$resTipe['id']]."</option>";
	}

	$kegiatan = makeOption($dbname,"setup_klpkegiatan","kodeklp,namakelompok");

	// kegiiatan
	$optkegiatan="<option value=''>".$_SESSION['lang']['all']."</option>";
	$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
	$skegiatan="select distinct kelompok from ".$dbname.".setup_kegiatan WHERE `kelompok` IN ('TB','BBT','TBM','TM','PNN') order by kelompok desc";
	$qkegiatan=$owlPDO->query($skegiatan) or die(print " Gagal: ".PDOException::getMessage());
	$qkegiatan->setFetchMode(PDO::FETCH_ASSOC);
	while($rkegiatan=$qkegiatan->fetch())
	{
		$optkegiatan.="<option value=".$rkegiatan['kelompok'].">".$kegiatan[$rkegiatan['kelompok']]."</option>";
	}


	OPEN_BOX('','<span class=judul>'.getMenu('kebun_2laporanupahpremibkm').'</span>');

	#= Kirim Data
	$arr = "##unit##div##tgl##tglx##tipe##kegiatan";
?>
	<fieldset>
		<legend>Form</legend>
		<table border=0 cellpadding=1 cellspacing=1>
	        <tr>
	            <td><?=$_SESSION['lang']['unit'];?></td>
	            <td>:</td>
	            <td><select class=select2 id=unit onchange="getDivisi()" style="\width:168px;\"><?= $optUnit; ?></select></td>
	        </tr>
			<tr>
	            <td><?=$_SESSION['lang']['divisi'];?></td>
	            <td>:</td>
	            <td><select class=select2 id=div style="\width:168px;\"><?=$optDiv;?></select></td>
	        </tr>
			<tr>
	            <td><?=$_SESSION['lang']['tipekaryawan'];?></td>
	            <td>:</td>
	            <td><select class=select2 id=tipe style="\width:168px;\"><?=$optTipe;?></select></td>
	        </tr>
			<tr>
	            <td>Kelompok <?=$_SESSION['lang']['kegiatan'];?></td>
	            <td>:</td>
	            <td><select class=select2 id=kegiatan style="\width:168px;\"><?=$optkegiatan;?></select></td>
	        </tr>
	        <tr>
	            <td>Dari <?=$_SESSION['lang']['tanggal'];?></td>
	            <td>:</td>
	            <td>
	            	<input type="text" readonly="readonly" class="myinputtext" style="width:165px;padding:2px 0px" id="tgl" onmousemove="setCalendar(this.id);" onkeypress="return" false;="" maxlength="10" autocomplete="off" />
	            </td>
	        </tr>

	        <tr>
	            <td>Sampai <?=$_SESSION['lang']['tanggal'];?></td>
	            <td>:</td>
	            <td>
	            	<input type="text" readonly="readonly" class="myinputtext" style="width:165px;padding:3px 0px" id="tglx" onmousemove="setCalendar(this.id);" onkeypress="return" false;="" maxlength="10" autocomplete="off" />
	            </td>
	        </tr>
	        <tr>
	            <td><td><td>
	            <button onclick="zPreview('kebun_2laporanupahpremibkm_slave','<?= $arr; ?>','printContainer')" class=mybutton name=preview id=preview><?=$_SESSION['lang']['preview'];?></button>
	            <button onclick="zExcel('event','kebun_2laporanupahpremibkm_slave.php','<?= $arr; ?>')" class=mybutton name=preview id=preview><?=$_SESSION['lang']['excel'];?></button>
	            </td>
	        </tr>
        </table>
	</fieldset>
<? 
CLOSE_BOX();
OPEN_BOX();
?>
	<div id="printContainer"></div>
<?
		
	CLOSE_BOX();
	close_body();
?>