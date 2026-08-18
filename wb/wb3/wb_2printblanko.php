<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<br>');
echo"<table border=0 cellpadding=3 style=width:100%>
	<tr>
		<td>
			<label class=label style='font-size:12px;font-weight:bold'>No Tiket<br>
			<input tabindex=6 class=myinputtext style='width:250px;height:40px;font-size:20px' type=text name=notiket id=notiket onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
		</td>
	</tr>
	<tr>
		<td>
			<button tabindex=111110 class=mybutton id=tabprint style=width:265px;height:40px;>Print</button>
		</td>
	</tr>
</table>";
CLOSE_BOX();
?>
<script language=javascript src='js/wb_2printblankko.js?v=<?php echo time(); ?>'></script>
<?
echo close_body();
?>