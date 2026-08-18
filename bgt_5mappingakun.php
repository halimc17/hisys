<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
	<script language=javascript1.2 src='js/bgt_5mappingakun.js?v=<?php echo time(); ?>'></script>
	<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php

include('master_mainMenu.php');

$tipebgt = array(
	'KEBUN',
	'PABRIK',
	'HOLDING',
	'KANWIL',
	'MILL',
	'RND',
	'TC',
	'BULKING'
);

$str="select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by substr(tipe,1,1) asc";
$res = fetchData($str);
foreach($res as $bar){
	$displayText = ($bar['tipe'] == 'KANWIL') ? 'REGIONAL OFFICE' : $bar['tipe'];
	$opttipe.="<option value='".$bar['tipe']."'>".$displayText."</option>";
}
			
foreach($tipebgt as $kei=>$val){
	//$opttipe.="<option value='".$val."'>".$val."</option>";
}


$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
foreach($arrstatus as $kei=>$fal){
	$optstatus.="<option value='".$kei."'>".$fal."</option>";
} 
OPEN_BOX('','<span class=judul>'.getMenu('bgt_5mappingakun').'<br></span>');

echo"<fieldset style='float:left;'>
	<title>Form</title>
	<table>
	 <tr>
		<td>Tipe Organisasi</td>
		<td>:</td>
		<td><select class=select2 id=tipeorg style=\"width:180px;\" >".$opttipe."</select></td>
	</tr>
	 <td></td>
		<td></td>
		<td>
			 <button class=mybutton onclick=loaddata()>" . $_SESSION['lang']['preview'] . "</button>
			 <button class=mybutton onclick=cancelDep()>" . $_SESSION['lang']['cancel'] . "</button>
		
		</td>
     </table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();	 
//echo open_theme($_SESSION['lang']['list']);
echo "<div id=container style=height:65vh></div>";
//echo close_theme();
CLOSE_BOX();
echo close_body();
?>