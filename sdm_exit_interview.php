<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
$where = "kodeparameter='PJDHRD'";
$setup_parameterappl =makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',$where);
if($_SESSION['empl']['bagian'] == $setup_parameterappl['PJDHRD']){
	$youAre = "HRD";
}else{
	$youAre = "USER";
}


OPEN_BOX("","<span class=judul>".getMenu('sdm_exit_interview')."</span>"); //1 O
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script type="text/javascript">
notifnoinvoiceafiliasi="<?php echo $_SESSION['lang']['notifnoinvoiceafiliasi']; ?>";
notifkontrak="<?php echo $_SESSION['lang']['notifkontrak']; ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/sdm_exit_interview.js" /></script>
<style>
	ul.listjawaban{list-style: none;}
	input[type="text"].optjawaban{
		background:none;
		border:none;
		border-bottom :0.5px solid #393939;
		/*width:-webkit-fill-available;*/
		margin-top:5px;
		margin-bottom:5px;
		width:300px;
	}
	.optexample{width:300px;}
	li{margin-top:5px;margin-bottom:5px;}
	input[type="text"].optjawaban:focus{
		background:#FFF;
		box-shadow:none;
		outline-offset: 0px;
		outline: none;
	}
	.colmn_jawaban_right{
		float:right;
	}
	.colmn_jawaban_left{
		float:left;
	}
	.clearfix{
		clear:both;
	}
	.w-400{width:400px;}
	ol.alphabet{list-style-type:lower-latin;}
	.optjawabanboth{float:left;}
	ol.pertanyaan>li{font-weight:bold;}
	ol.pertanyaan>li li{font-weight:normal;}
	textarea{width:400px;margin-top:5px;margin-bottom:5px;}
</style>

<?php


echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=loadlist(1)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td>";
	if($youAre !== "HRD"){
		echo "<fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
		echo $_SESSION['lang']['notransaksi'].' : <input type="text" id="txtsearch" size="20" maxlength="30" class="myinputtext">';
		echo"<button class=mybutton onclick=loadlist(1)>".$_SESSION['lang']['find']."</button>";
		echo"</fieldset>";
	}
	echo "
		</td>
     </tr>
        </table>";
		
CLOSE_BOX();

OPEN_BOX();

echo"<div id=workwarp><script>loadlist(1);</script></div>";
CLOSE_BOX();
echo close_body(); ?>
