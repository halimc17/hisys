<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/bi_5tipedokumen.js'></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['dokumen']."(MAP REQUIREMENT)").'</span>');

$optTable = $optNodok = $optJnsKgtn = $optKodeOrg = $optPeriode = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="SHOW TABLES FROM ".$dbname."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_NUM);
while($bar=$res->fetch()){
	if($bar[0]=='admin_list' || $bar[0]=='user')
		continue;
	else
    $optTable.="<option value='".$bar[0]."'>".$bar[0]."</option>";
}

echo"<fieldset style='width:500px;'>
	<legend>".$_SESSION['lang']['dokumen']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<input style='width:195px;' id=tipe class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['deskripsi']."</td>
			<td>:</td>
			<td>
				<input  style='width:195px;' id=deskripsi class=myinputtext>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tabel']."</td>
			<td>:</td>
			<td>
				<select id=tabel onchange=\"getfield();\" style='width:200px;'>".$optTable."</select>
			</td>
		</tr>
		<tr>
			<td>Column (".$_SESSION['lang']['nodok'].")</td>
			<td>:</td>
			<td>
				<select id=nodok style='width:200px;'>".$optNodok."</select>
			</td>
		</tr>
		<tr>
			<td>Column (".$_SESSION['lang']['kegiatan'].")</td>
			<td>:</td>
			<td>
				<select id=jnskgtn style='width:200px;'>".$optJnsKgtn."</select>
			</td>
		</tr>
		<tr>
			<td>Column (".$_SESSION['lang']['kodeorg'].")</td>
			<td>:</td>
			<td>
				<select id=kodeorg style='width:200px;'>".$optKodeOrg."</select>
			</td>
		</tr>
		<tr>
			<td>Column (".$_SESSION['lang']['periode'].")</td>
			<td>:</td>
			<td>
				<select id=periode style='width:200px;'>".$optPeriode."</select>
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