<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"getSlave('showadd')\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"getSlave()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";
echo open_body();
require_once('master_mainMenu.php');
?>
<script language='javascript' src='js/zMaster.js'></script>
<script language='javascript' src='js/zSearch.js'></script>
<script language='javascript' src='js/zTools.js'></script>
<script languange='javascript' src='js/sdm_programtraining.js'></script>
<script languange='javascript' src='js/formTable.js'></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<?php	
OPEN_BOX('','<span class=judul>'.getMenu('sdm_programtraining').'</span>');
echo "<div><table><tr>";
foreach($ctl as $el) {
    echo "<td v-align='middle' style='min-width:100px'>".$el."</td>";
}
echo "</tr></table></div>";
CLOSE_BOX();
echo "<div id='workwarp'></div>";
echo "<script>getSlave();</script>";
echo close_body();
?>