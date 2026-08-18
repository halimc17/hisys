<? //@Copy nangkoelframework

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('', '<span class=judul>' . getMenu('log_5regionalprocurement') . '</span><br>');
?>

<script language=javascript src='js/log_5regionalprocurement.js?v=<?php echo time(); ?>'></script>

<?
CLOSE_BOX();
OPEN_BOX();

$tab = "";

$tab .= "<div id='output' style=min-height:400px><script>loaddata()</script></div>";

#= Echo Interface
echo $tab;

CLOSE_BOX();
close_body();
?>