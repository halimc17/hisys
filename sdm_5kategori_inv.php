<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/sdm_5kategori_inv.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5kategori_inv').'</span></br>');

echo"<fieldset style='width:500px;'>
      <legend>".$_SESSION['lang']['form']."</legend>
	  <table>
	  
	  <tr>
	      <td>ID ".$_SESSION['lang']['kategori']."</td><td>:</td>
		  <td><input style=width:200px; placeholder='auto generate' type=text class=myinputtext id=idjenis disabled></td>
	      <td><input type=hidden  id=idjenis nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" disabled></td>
	  </tr>

	   <tr>
		  <td>".$_SESSION['lang']['kategori']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=jenis onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\" size=20 maxlength=45></td>
	  </tr>
	  <!--
	   <tr>
	   <td>" . $_SESSION['lang']['jumlahhk'] . "</td><td>:</td><td><input style=width:200px; type=text id=jumlahhk  style='width:200px;' size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=3 value=0 onblur=change_number(this)></td>
	   </tr>
		-->
               
	  <tr><td><td><td>
	  <input type=hidden id=method value=insert>
	<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>	  
	  </td></td></td></tr></table></fieldset>";
?>
<?
CLOSE_BOX();
OPEN_BOX('','');
	echo"<fieldset>
		 <legend>".$_SESSION['lang']['availvhc']." <span id=captiontipe></span><span id=captionkelompok></span></legend>";
      

	echo"
	     <table class=sortable cellspacing=1 border=0 style='width:500px;'>
			<thead>
				<tr class=header>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center>ID ".$_SESSION['lang']['kategori']."</th>
					<th align=center>".$_SESSION['lang']['kategori']."</th>
					<th  align=center>".$_SESSION['lang']['action']."</th>
				</tr>
			</thead> 
			<tbody id=container>
			<script>loadData1(0)</script>
			</tbody>
			<tfoot></tfoot>
		 </table>
		 </fieldset>";

CLOSE_BOX();

// echo "</div>";
?>
<?php echo close_body(); ?>