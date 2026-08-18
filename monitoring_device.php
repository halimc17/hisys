<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('monitoring_device').'</span><br>');
?>
<script language='javascript' src='js/monitoring_device.js?v=<?= time(); ?>'></script>
<script language='javascript' src='js/Chart.js?v=<?= time(); ?>'></script>

<script>
	document.addEventListener('DOMContentLoaded', () => {
		loadtableatt();
	});
	// window.addEventListener('load',
	// setInterval(
		// function()
		// {
		// }
	// ,5000);

	// setInterval(ambil_tanggal(),1000);
	// );
</script>
<?php
echo"<table style='width:100%' border=0>
	<tr>
		<td style='width:50%;text-align:center;' valign=top>
			<fieldset>
			<legend>Grafik Sensor Sounding</legend>
			<canvas id='canvas'></canvas>
			</fieldset>
		</td>
		<td style='width:50%;text-align:center;height:100%' valign=top>
			<legend>Tabel Sensor Sounding</legend>
			<div id='table3'></div>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td colspan='2'><hr style='border:1px solid #fff'></td>
	</tr>
	<tr>
		<td style='width:50%;vertical-align:top;border-right:1px solid #fff'>
			<fieldset>
			<legend>Data Absensi Fingerprint</legend>
			<div id='table1'></div>
			</fieldset>
		</td>
		<td style='width:50%;vertical-align:top;border-right:1px solid #fff'>
			<fieldset>
			<legend>Data Sensor Ombrometer</legend>
			<div id='table2'></div>
			</fieldset>

		</td>
	</tr>
</table>";

CLOSE_BOX();
echo close_body();
?>