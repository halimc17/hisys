<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/kebun_5kelaspohon.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['kelaspohon']).'</span><br>');

echo"<fieldset style=width:350px;float:left;>
     <legend>".$_SESSION['lang']['form']."</legend> 
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['kelaspohon']."</td><td>:</td>
	   <td><input type=text class=myinputtext id=kelaspohon name=kelaspohon style=\"width:70px;\" maxlength=8 /></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['basisjjg']." / ".$_SESSION['lang']['hari']."</td><td>:</td>
	   <td><input type='text' id='basishari' class='myinputtextnumber' style='width:70px' onkeypress='return angka_doang(event)' value='0' /></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['basisjjg']." / ".$_SESSION['lang']['bulan']."</td><td>:</td>
	   <td><input type='text' id='basisbulan' class='myinputtextnumber' style='width:70px' onkeypress='return angka_doang(event)' value='0' /></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['keterangan']."</td><td>:</td>
	   <td><input type=text class=myinputtext id=namakelas name=namakelas style=\"width:200px;\" maxlength=30 /></td>
	 </tr>
	 <tr>
		<td></td>
		<td><td>
			<input type=hidden value=insert id=proses>
			<button class=mybutton onclick=simpankelas()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
	 </tr>
	</table>
     </fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=width:350px;float:left;><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 cellpadding=3 border=0>
     <thead>
	  <tr class=rowheader>
	   <td width=30px align=center>".$_SESSION['lang']['kelaspohon']."</td>
	   <td width=60px align=center>".$_SESSION['lang']['basisjjg']." / ".$_SESSION['lang']['hari']."</td>
	   <td width=60px align=center>".$_SESSION['lang']['basisjjg']." / ".$_SESSION['lang']['bulan']."</td>
	   <td align=center>".$_SESSION['lang']['keterangan']."</td>
	   <td colspan='2' style='text-align:center;'>".$_SESSION['lang']['action']."</td>
	  </tr>
	 </thead>
	 <tbody id=container>
	 <script>loaddata()</script>";
echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table></fieldset></div>";
CLOSE_BOX();
echo close_body();
?>