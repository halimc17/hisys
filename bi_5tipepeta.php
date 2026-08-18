<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/bi_5tipepeta.js'></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('MAP TYPE').'</span>');

//Get Tipe Feature
$optFeature = "";
$tipefeature = getEnum($dbname,"bi_5tipepeta","tipefeature");
$no = 0;
foreach($tipefeature as $val){
	$optFeature .= "<option value='".$val."'>".ucwords($val)."</option>";
}

//Get Tipe Dokumen
$str="select id_tipedok, nama_tipe from ".$dbname.".bi_5tipedok order by nama_tipe asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optTipeDokumen .= "<option value='".$bar['id_tipedok']."'>".$bar['nama_tipe']."</option>";
}

echo"<fieldset>
	<legend>".$_SESSION['lang']['peta']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['id']."</td>
			<td>:</td>
			<td>
				<input id=id class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr style='display:none'>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<input type=radio name=chk id=chkDasar onclick=\"checkChkTipe();\" value='0' checked>Dasar
				<input type=radio name=chk id=chkPt onclick=\"checkChkTipe();\" value='1'>PT
				<input type=radio name=chk id=chkKegiatan onclick=\"checkChkTipe();\" value='2'>Kegiatan
			</td>
		</tr>
		<tr id='trtipedokumen' style='display:none'>
			<td>".$_SESSION['lang']['tipedokumen']."</td>
			<td>:</td>
			<td>
				<select id='tipedokumen'>".$optTipeDokumen."</select>
			</td>
		</tr>
		<tr  id='trdeskripsi'>
			<td>".$_SESSION['lang']['deskripsi']."</td>
			<td>:</td>
			<td>
				<input id=deskripsi class=myinputtext>
			</td>
		</tr>
		<tr style='display:none'>
			<td>Tipe Feature</td>
			<td>:</td>
			<td>
				<select id='tipefeature'>
					".$optFeature."
				</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type='hidden' id='method' value='insert'>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><div id=anggota style='display:none'></td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
	<div id='container'>
	</div>
	<script>loaddata()</script>";
CLOSE_BOX();
echo close_body();
?>