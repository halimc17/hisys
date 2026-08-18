<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/sdm_5kldiagnosa.js'></script>
<script>

</script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['kelompokdiagnosa']).'</span><br>');

echo"<fieldset style='width:300px;float:left;'>
     <legend><b>".$_SESSION['lang']['kelompokdiagnosa']."</b></legend>
	 <table>
		<tr>
			<td>".$_SESSION['lang']['kodekelompok']." </td>
			<td>:</td>
			<td><input type=text class=myinputtext id=kodekelompok style=width:80px; maxlength=8 /></td>
		</tr>	
		<tr>
			<td style='vertical-align:top;'>".$_SESSION['lang']['deskripsi']."</td>
			<td style='vertical-align:top;'>:</td>
			<td><textarea id=deskripsi></textarea></td>
		</tr>
		<tr>
			<td colspan='2'></td>
			<td>
			<input type=hidden value=insert id=method>
			<button class=mybutton onclick=save()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td>
		</tr>
	 </table>
     </fieldset>";
 
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style='float:left;'>
	<legend><b>".$_SESSION['lang']['list']."</b></legend>
	<table class=sortable cellspacing=1 border=0>
     <thead>
		<tr class=rowheader>
			<td>".$_SESSION['lang']['kodekelompok']."</td>
			<td>".$_SESSION['lang']['deskripsi']."</td>
			<td colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</td>    
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>loadData()</script>";
echo"</tbody>
	<tfoot>
	</tfoot>
	</table></fieldset>";
CLOSE_BOX();
echo close_body();
?>