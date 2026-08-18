<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('help_show').'</span>');
?>

<script language="javascript" src="js/help_show.js"></script>

<?php
echo"<p>
Cari : <input type='text' class='myinputtext' id='sccari' onkeypress='return tanpa_kutip(event);' onkeyup='loaddata(0)' style='width:400px;'' />
<table cellspacing='1' cellpadding='3' border='0' class='sortable'>
	<thead>
	<tr class='rowheader'>
		<td align='center'>".$_SESSION['lang']['nourut']."</td>
		<td align='center'>".$_SESSION['lang']['modul']."</td>
		<td align='center'>".$_SESSION['lang']['judul']."</td>
		<td align='center'>".$_SESSION['lang']['langname']."</td>
	</tr>
	</thead>
	<tbody id='contain'><script>loaddata(0)</script></tbody>
</table>";

CLOSE_BOX();


echo close_body();
?>