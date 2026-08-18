<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
//tentukan table yang akan dikontrol
$TABLENAME='user_activity';
?>

<script language=javascript1.2 src=js/tablebrowser.js></script>
<?
include('master_mainMenu.php');
require_once('lib/admin_validation.php');

OPEN_BOX('','<span class=judul>'.strtoupper('USER ACTIVITY').'</span>');
printTableController($TABLENAME);
CLOSE_BOX();
OPEN_BOX('','<span class=judul>'.strtoupper('USER ACTIVITY').'</span>');
echo"<div id=container style='width:100%;height:400px;overflow:scroll;'></div>";
CLOSE_BOX();
echo close_body();
?>
