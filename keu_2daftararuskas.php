<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('keu_2daftararuskas').'</span><br>');

echo "<fieldset style='float:left;'><table>
	<tr><td colspan=2></td>
		<td colspan=4>
		<button onclick=zPreview('keu_slave_2daftararuskas','".@$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'keu_slave_2daftararuskas.php','".@$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		</td>
	</tr>";

echo "</table>";
echo "</fieldset>";
CLOSE_BOX();
?>


<?php
OPEN_BOX();

echo "
<div id='printContainer' class='table-scroll'  style='overflow:auto;height:400px'; >
</div>";

CLOSE_BOX();
echo close_body();					
?>