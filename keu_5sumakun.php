<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/keu_5sumakun.js'></script>
<?
include('master_mainMenu.php');


$optakun='';
$sReg="select * from ".$dbname.".keu_5akun where detail=1";
$rReg=$owlPDO->query($sReg) or die(print " Gagal: ".PDOException::getMessage());
$rReg->setFetchMode(PDO::FETCH_OBJ);
while($bReg=$rReg->fetch()){
	$optakun.="<option value='".$bReg->noakun."'>".$bReg->noakun."-".$bReg->namaakun."</option>";	
}


OPEN_BOX('','<span class=judul>'.getMenu('keu_5sumakun').'<br></span>');
echo"<fieldset style=width:350px;float:left;>
     <legend>".$_SESSION['lang']['form']."</legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['noakun']."</td><td>:</td>
	   <td><select style=width:200px id=noakun >".$optakun."</select></td>
	 </tr>
	 
	 <tr>
		<td></td>
		<td></td>
		<td>
			<input type=hidden value=insert id=proses>
			<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
	 </tr>
	</table>
     </fieldset>";
echo"<fieldset style=width:350px;float:left;>
     <legend>".$_SESSION['lang']['find']."</legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['noakun']."</td><td>:</td>
	   <td><input type='text' id=noakunf  class='myinputtext' onkeypress='return tanpa_kutip(event)' ></td>
	 </tr>

	 <tr>
	   <td>".$_SESSION['lang']['namaakun']."</td><td>:</td>
	   <td><input type='text' id=namaakunf  class='myinputtext' onkeypress='return tanpa_kutip(event)' ></td>
	 </tr>
	 
	 <tr>
		<td></td>
		<td></td>
		<td>
			<input type=hidden value=insert id=proses>
			<button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>
			<button class=mybutton onclick=batalx()>".$_SESSION['lang']['cancel']."</button>
		</td>
	 </tr>
	</table>
     </fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=width:350px;float:left;><legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loaddata(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>