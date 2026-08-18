<?php
    require_once('master_validation.php');
    require_once('lib/nangkoelib.php');
    require_once ('config/connection.php');
    require_once('lib/zLib.php');
    echo open_body();
    require_once('master_mainMenu.php');
    require_once('lib/zDatatables.php');
    require_once('lib/zSelect2Lite.php');

    OPEN_BOX('','<span class=judul>'.getMenu('lgl_5hargasewahm').'</span><br>');
?>

<script language=javascript src='js/lgl_5hargasewahm.js?v=<?php echo time(); ?>'></script>

<?
    CLOSE_BOX();
    OPEN_BOX();
    echo"<div id='output' style=min-height:400px><script>loaddata()</script></div>";
    CLOSE_BOX();
    echo close_body();
?>