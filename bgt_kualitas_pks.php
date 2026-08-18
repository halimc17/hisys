<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
?>

<?php
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript1.2 src='js/bgt_kualitas_pks.js'></script>

<?php
//Get Kode Unit (MILL)
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ORDER BY kodeorganisasi";
$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}

$optthnttp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "SELECT distinct(tahunbudget) as tahunbudget FROM ".$dbname.".bgt_pks_kualitas where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutup = '0' ORDER BY tahunbudget desc";
$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch()){
	$optthnttp.="<option value=".$data['tahunbudget'].">".$data['tahunbudget']."</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper('BUDGET: FFB QUALITY').'</span>');
echo"<br />
<fieldset style='float:left;'>
<legend>".$_SESSION['lang']['entryForm']."</legend> 
<table border=0 cellpadding=1 cellspacing=1>
	<tr>
		<td width=95>".$_SESSION['lang']['budgetyear']."</td>
		<td width=7>:</td>
		<td>
			<input type=text class=myinputtextnumber id=thnbudget onkeypress=\"return angka_doang(event);\" style=\"width:50px;\" maxlength=4 />
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td colspan=4>
			<select id=kdpks>".$optOrg."</select>
		</td>
	</tr>
	<tr>
		<td colspan=3>
			<b>CPO / Year</b>
		</td>
		<td style='width:20px'>&nbsp;</td>
		<td colspan=3>
			<b>Kernel / Year</b>
		</td>
	</tr>
	<tr>
		<td>FFA</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpoffa onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder=0 />
		</td>
		
		<td></td>
		
		<td>FFA</td>
		<td width=7>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkffa onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td>Kadar Air</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpokadarair onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder=0 />
		</td>
		
		<td></td>
		
		<td>Kadar Air</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkkadarair onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td>Kadar Kotoran</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpokadarkotoran onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder=0 />
		</td>
		
		<td></td>
		
		<td>Kadar Kotoran</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkkadarkotoran onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td colspan=4></td>
		<td>Broken</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkbroken onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td colspan=7><hr></td>
	</tr>
	<tr>
		<td colspan=3>
			<b>Oil Losses / Year</b>
		</td>
		<td style='width:20px'>&nbsp;</td>
		<td colspan=3>
			<b>Kernel Losses / Year</b>
		</td>
	</tr>
	<tr>
		<td>Fiber Press</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpofiberpress onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossescpo();\" placeholder=0 />
		</td>
		
		<td></td>
		
		<td>USB</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkusb onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossespk();\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td>Nut Press</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cponutpress onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossescpo();\" placeholder=0 />
		</td>
		<td></td>
		<td>Fiber Cyclone</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkfibercyclone onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossespk();\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td>Empty Bunch</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpoemptybunch onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossescpo();\" placeholder=0 />
		</td>
		<td></td>
		<td>LTDS 1</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkltds1 onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossespk();\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td>USB</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpousb onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossescpo();\" placeholder=0 />
		</td>
		<td></td>
		<td>LTDS 2</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkltds2 onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossespk();\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td>Solid Decanter</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cposoliddecanter onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossescpo();\" placeholder=0 />
		</td>
		<td></td>
		<td>Wet Shell/Claybath</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pkclaybath onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossespk();\" placeholder=0 />
		</td>
	</tr>
	<tr>
		<td>Heavy Phase</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpoheavyphase onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossescpo();\" placeholder=0 />
		</td>
		<td colspan=4></td>
	</tr>
	<tr>
		<td>Final Effluent</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpofinaleffluent onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossescpo();\" placeholder=0 />
		</td>
		<td colspan=4></td>
	</tr>
	<tr>
		<td>Sterilizer Condensat</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cposterilizecondensat onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" onkeyup=\"totallossescpo();\" placeholder=0 />
		</td>
		<td colspan=4></td>
	</tr>
	<tr>
		<td><b>Total Losses</b></td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=cpototal onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" value=0 disabled />
		</td>
		<td></td>
		<td><b>Total Losses</b></td>
		<td>:</td>
		<td>
			<input type=text class=myinputtextnumber id=pktotal onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" value=0 disabled />
		</td>
	</tr>
	<tr>
		<td colspan=7 style='text-align:center;padding-top:5px;'>
			<div id=tmblSave>
				<button onclick=simpan() class=mybutton name=saveDt id=saveDt>".$_SESSION['lang']['save']."</button> 
				<button class=mybutton onclick=batal() name=btl id=btl>".$_SESSION['lang']['cancel']."</button>
			</div>
		</td>
	</tr>
</table>
</fieldset>
<input type=hidden id=method value=insert /><script>loadData()</script>";


echo"<fieldset  style='float:left'><legend>".$_SESSION['lang']['tutup']."</legend>
    <div id=closetab>
		<table>
			<tr>
				<td>".$_SESSION['lang']['budgetyear']."</td>
				<td>:</td>
				<td><select id=thnttp style='widht:150px'>".$optthnttp."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
			<td><select id=kdpksttp style='widht:150px'>".$optOrg."</select></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td><br />
				<button class=\"mybutton\"  id=\"saveData\" onclick='closepks()'>".$_SESSION['lang']['tutup']."</button>
			</td>
		</tr>
	</table>";
echo"</div></fieldset>";
CLOSE_BOX();

//LIST DATA
OPEN_BOX();

//tab1
$frm[0].="<fieldset id=tab1 disabled=true>
	<div style=overflow:auto;width:100%; id=container1></div>";
$frm[0].="</fieldset>";

//tab2
$frm[1].="<fieldset id=tab2 disabled=true>
	<div style=overflow:auto;width:100%; id=container2></div>";
$frm[1].="</fieldset>";

//tab title
$hfrm[0]=$_SESSION['lang']['list'];
// $hfrm[1]=$_SESSION['lang']['sebaran'];
drawTab('FRM',$hfrm,$frm,100,'');

CLOSE_BOX();
echo close_body();
?>