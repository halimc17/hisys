<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/sdm_5jenisijin.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5jenisijin').'</span></br>');

$arrpot=array('0' => $_SESSION['lang']['tidakpotong'],'1' => $_SESSION['lang']['potong']);
foreach ($arrpot as $key => $value) {
    $optpot.="<option value='".$key."'>".$value."</option>";
}

$arrstatus=array('0'=>'0','1' =>'1','2'=> '2');
foreach ($arrstatus as $key => $value) {
    $statpot.="<option value='".$key."'>".$value."</option>";
}

echo"<fieldset style='width:500px;float:left;'>
	<legend>".$_SESSION['lang']['form']."</legend>
	<table>
		<tr>
			<td>ID ".$_SESSION['lang']['jenisijin']."</td><td>:</td>
			<td><input style=width:200px; placeholder='auto generate' type=text class=myinputtext id=idjenis disabled></td>
			<td><input type=hidden  id=idjenis nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" disabled></td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['jenisijin']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=jenis onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\" size=20></td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['uangmakan']."</td>
	        <td>:</td>
	        <td><select id=umakan style=width:150px;>".$optpot."</select></td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['uangtransport']."</td>
	        <td>:</td>
	        <td><select id=utransport style=width:150px;>".$optpot."</select></td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['potongan']."</td>
	        <td>:</td>
	        <td><select id=statuspot style=width:150px;>".$statpot."</select></td>
		</tr>
		<tr hidden>
			<td>" . $_SESSION['lang']['jumlahhk'] . "</td><td>:</td><td><input style=width:50px; type=text id=jumlahhk  style='width:200px;' size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=3 value=0 onblur=change_number(this)></td>
		</tr>
		<tr hidden>
			<td>" . $_SESSION['lang']['potongan'] . "HK</td><td>:</td><td><input style=width:50px; type=text id=potonganhk  style='width:200px;' size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=3 value=0 onblur=change_number(this)></td>
		</tr>
		<tr>
			<td><td><td>
			<input hidden id=method value=insert>
			<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>	  
			</td></td></td>
		</tr>
	</table>
</fieldset>";
echo"
<fieldset style='text-align:left; height:145px;width:450px;' hidden>
<legend><b><img src=images/info.png align=left height=35px valign=asmiddle>[Info]</b></legend>
<br />
<ol type='a'>
<li>0 = Tidak potong gaji dan cuti.</li>
<li>1 = Potong cuti jika ada, jika cuti habis akan potong gaji.</li>
<li>2 = Tidak dipotong jika disetujui, jika tidak disetujui (potong cuti jika ada, jika cuti habis akan potong gaji).</li>
</ol>
</fieldset>";
?>
<?
CLOSE_BOX();
OPEN_BOX('','');
	echo"
	     <table class=sortable cellspacing=1 cellpadding=7 border=0 width= 100%>
			<thead>
				<tr class=header>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center>ID ".$_SESSION['lang']['jenisijin']."</th>
					<th align=center>".$_SESSION['lang']['jenisijin']."</th>
					<th align=center hidden>".$_SESSION['lang']['uangmakan']."</th>
					<th align=center hidden>".$_SESSION['lang']['uangtransport']."</th>
					<th align=centerr hidden>".$_SESSION['lang']['statuspotongan']."</th>
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