<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5validasilembur').'</span><br>');
?>
<script language=javascript src='js/sdm_5validasilembur.js?v=<?php echo time(); ?>'></script>
<?php
CLOSE_BOX();
OPEN_BOX();
echo"<div id='output' style='height:76vh'><script>loaddata()</script></div>";
CLOSE_BOX();
?>
<?php
echo close_body();
?>