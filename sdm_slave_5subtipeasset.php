<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
$proses=$_POST['proses'];
$tipeasset=$_POST['tipeasset'];
$kodesubasset=$_POST['kodesubasset'];
$namasubasset=$_POST['namasubasset'];
$nama=$_POST['nama'];
$umurpenyusutan=$_POST['umurpenyusutan'];

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

switch($proses){
case 'simpan':
	if($kodesubasset == ""){
		$selectQ = "select max(CAST(kodesub AS UNSIGNED)) as maxkodesub from ".$dbname.".sdm_5subtipeasset where kodetipe = '".$tipeasset."' ";
		$res =  fetchData($selectQ);
		$kode = (int)$res[0]['maxkodesub'];
		$kodesubasset = str_pad($kode+1, 2, '0', STR_PAD_LEFT); ;
	}
	$strsimpan = "insert into ".$dbname.".sdm_5subtipeasset values('".$param['kodeorg']."','".$tipeasset."','".$kodesubasset."','".$namasubasset."','".$nama."','".$param['metodepenyusutan']."','".$umurpenyusutan."','".$param['tarifpenyusutan']."')";
	try {
		$owlPDO->exec($strsimpan);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'edit':
	$strsimpan = "update ".$dbname.".sdm_5subtipeasset set namasub='".$namasubasset."', id_namaharta='".$nama."', umurpenyusutan='".$umurpenyusutan."', metodepenyusutan='".$param['metodepenyusutan']."', tarifpenyusutan='".$param['tarifpenyusutan']."' where kodetipe='".$tipeasset."' and kodesub='".$kodesubasset."'";
	try {
		$owlPDO->exec($strsimpan);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
default:
	break;
}
 ##  # BEGIN GET TYPE ASSET ##  #
$str = "select kodetipe, namatipe from ".$dbname.".sdm_5tipeasset";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$namatipe[$bar->kodetipe] = $bar->namatipe;
}
 ##  # END GET TYPE ASSET ##  #
 ##  # BEGIN GET Nama Harta ##  #
$str2 = "select id_namaharta, namaharta from ".$dbname.".keu_5asset_namaharta";
$res = $owlPDO->query($str2)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$namaharta[$bar->id_namaharta] = $bar->namaharta;
}
 ##  # END GET Nama Harta ##  #
$str1 = "select * from ".$dbname.".sdm_5subtipeasset order by  kodetipe, kodesub";
$res = $owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res->fetch()) {
	echo "<tr class=rowcontent>
	<td align=center>".$bar1->kodeorg."</td>
	<td>".$bar1->kodetipe." - ".$namatipe[$bar1->kodetipe]."</td>
	<td align=center>".$bar1->kodesub."</td>
	<td>".$bar1->namasub."</td>
	<td>".$bar1->id_namaharta." - ".@$namaharta[$bar1->id_namaharta]."</td>
	<td align=center>".$bar1->metodepenyusutan."</td>
	<td align=center>".$bar1->umurpenyusutan."</td>
	<td align=center>".$bar1->tarifpenyusutan."</td>
	<td style='text-align:center'><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editSubTipeAset('".$bar1->kodesub."','".$bar1->namasub."','".$bar1->id_namaharta."','".$bar1->umurpenyusutan."','".$bar1->kodetipe."','".$bar1->metodepenyusutan."','".$bar1->tarifpenyusutan."','".$bar1->kodeorg."');\"></td></tr>";
}
?>