<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5tangki').'</span><br>');
?>
<script language=javascript src='js/pabrik_5tangki.js?v=<?= time(); ?>'></script>
<?php
CLOSE_BOX();
OPEN_BOX();
echo"<div id='output' style='height:76vh'><script>loaddata()</script></div>";
CLOSE_BOX();
?>
<script>
	getSelect2();
</script>
<?php
echo close_body();
?>