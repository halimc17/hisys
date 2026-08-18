<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['taskassignment']).'</span>');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/sdm_taskassignment.js?ver=5.6"></script>
<style>
quote{
    color: green;
}
quote::before,quote::after{
	content:' " ';
}
.pointer{
	cursor:pointer;
}
.pointer:hover{
	background:#f1f8fd;
}
.alignleft {
	float: left;
}
.alignright {
	float: right;
}
</style>
<?php
echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=exacData('','buatbaru');>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=exacData();>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td>";
echo "<fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
echo $_SESSION['lang']['subject'].' : <input type="text" id="txtsearch" size="20" maxlength="30" class="myinputtext">';
echo"<button class=mybutton onclick=exacData('&subject='+document.getElementById('txtsearch').value,'viewtask')>".$_SESSION['lang']['find']."</button>";
echo"</fieldset>";
echo "
		</td>
     </tr>
        </table>";
?>
<?php CLOSE_BOX() ?>
<?php OPEN_BOX() ?>
<div id="taskmanagemenhead"></div>
<div id="formelement"></div>
<?php CLOSE_BOX() ?>