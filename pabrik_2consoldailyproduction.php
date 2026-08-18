<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/pabrik_2consoldailyproduction.js?ver=2.9'></script>
<?
$optper="";
$str="select distinct substring(tanggal,1,7) as periode from ".$dbname.".pabrik_timbangan order by tanggal desc";
$res=fetchdata($str);
foreach ($res as $val) {
	$optper.="<option value=".$val['periode'].">".$val['periode']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_2consoldailyproduction').'</span><br>');

echo "<div id=filterxxx ><fieldset style='float:left'>
	<table border=0 cellspacing=5>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td><select id=periode style='width:70px'>".$optper."</select></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=loaddata()>".$_SESSION['lang']['preview']."</button></td>
		</tr>
	</table>
	</fieldset>
	</div>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id=container></div>";
echo"<div id=tampil style='display:none;height:75vh;' class='table-scroll'></div>";
CLOSE_BOX();
close_body();
?>