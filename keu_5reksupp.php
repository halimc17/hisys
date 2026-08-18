<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zMysql.php');
echo open_body();
?>


<script language=javascript src='js/keu_5reksupp.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>
<?
include('master_mainMenu.php');
include('lib/zLib.php');

$xxx = $_GET['xxx'];

$optcaristatus = "<option value=''>".$_SESSION['lang']['all']."</option>";
$optsupstat = array('0'=>'tidakaktif','1'=>'AKTIF','2'=>'belumsetuju','3'=>'register','4'=>'blacklist');
foreach ($optsupstat as $key=>$val) {
	$selected = "";
	if($key == 3){
		$selected = "selected";
	}
 	$optcaristatus .= "<option value= '".$key."'>".strtoupper($_SESSION['lang'][strtolower($val)])."</option>";
}

$optcaribadan = "<option value=''>".$_SESSION['lang']['all']."</option>";
$arrtipebadan = getEnum($dbname,'log_5supplier','badanusaha');
foreach($arrtipebadan as $kei=>$fal){
	$optcaribadan .= "<option value='".$kei."'>".ucfirst(strtoupper($fal))."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_5reksupp').'</span></br>');

	$form = "";
  $form .= "<fieldset style='float:left;margin:5px 0px 10px 0px;'>
  					<table>
  						<tr>
   							<td>".$_SESSION['lang']['carisupplierid']."</td> 
    						<td>:</td> 
    						<td><input type=text id=txtNoakun onkeypress='key=getKey(event); if(key==13){loadData1(0)}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\" size=15 maxlength=30 class=myinputtext></td>

    						<td>".$_SESSION['lang']['carinamasupplier']."</td>
    						<td>:</td> 
    						<td><input type=text id=txtsearch onkeypress='key=getKey(event); if(key==13){loadData1(0)}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\" size=25 maxlength=30 class=myinputtext></td>

    						<td>".$_SESSION['lang']['status'] . " " . $_SESSION['lang']['supplier']."</td>
					      <td>:</td> 
					      <td><select id=caristatusup style=\"width:203px;\">".$optcaristatus."</select></td>

					      <td>".$_SESSION['lang']['badanusaha']."</td>
					      <td>:</td> 
					      <td><select id=caribadan style=\"width:203px;\">".$optcaribadan."</select></td>

					      <td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>
     		  			<td><button class=mybutton onclick=cancelsearch()>".$_SESSION['lang']['cancel']."</button></td>
    					</tr>
    				</table>
    				</fieldset>";

	$form .= "<table id=container cellspacing=1 border=0 width=100% class=sortable>
							<script>loadData(0)</script>
	 					</table>";

	 echo $form;
	 

CLOSE_BOX();

?>
<?php echo close_body(); ?>