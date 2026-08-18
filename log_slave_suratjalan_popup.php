<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
?>
<link rel="stylesheet" type="text/css" href="style/generic.css">
<script language=javascript src='js/generic.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/log_suratjalan.js'></script>
<?php
$tipe = $_GET['tipe'];
$param = $_GET;

switch($tipe) {
    case 'PO':
	
		echo "<fieldset ><label for=po>".$_SESSION['lang']['nopo']." : </label>";
		echo "<input class=myinputtext id='po' onkeypress='key=getKey(event);if(key==13){findPO()}'>";
		echo "<button class=mybutton onclick='findPO()'>".
			$_SESSION['lang']['find']."</button></fieldset>";
		break;
	case 'PL':
		echo "<fieldset style=width:95%><label for=pl>Search : </label>";
		echo "<input class=myinputtext id='pl' onkeypress='key=getKey(event);if(key==13){findPL()}'>";
		echo "<button class=mybutton onclick='findPL()'>".
			$_SESSION['lang']['find']."</button></fieldset >";
		break;
    
	case 'M':
		echo "<fieldset style=width:95%><label for=mat>Search : </label>";
		echo "<input  class=myinputtext id='mat' onkeypress='key=getKey(event);if(key==13){findMat()}'>";
		echo "<button class=mybutton onclick='findMat()'>".
			$_SESSION['lang']['find']."</button></fieldset >";
		break;
	
	default:
	break;
}
?>
<?php if(isset($param['kodept'])){ ?>
<input type=hidden id=kodept value='<?php echo $param['kodept']?>'>
<?php } ?>
<?php if(isset($param['kodeorg'])){ ?>
<input type=hidden id=kodeorg value='<?php echo $param['kodeorg']?>'>
<?php } ?>
<div id='hasilCari'></div>
<div id='progress'></div>