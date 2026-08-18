<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript src='js/pabrik_produksiBulanan.js?v=<?php echo time(); ?>'></script>

<?
include('master_mainMenu.php');

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' order by kodeorganisasi desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optpabrik.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

$optper="";
$sPeriode="select distinct substring(tanggal,1,7) as periode from ".$dbname.".pabrik_produksi order by tanggal desc ";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch()){
    $optper.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
}

OPEN_BOX('', '<span class=judul>' . getMenu('pabrik_2produksiBulanan') . '</span><br>');
echo "<fieldset style='float:left'>
	<table>
		<tr>
			<td>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td>:</td>
			<td><select id=kode_pabrik>".$optpabrik."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td><select id=periode>".$optper."</select></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=preview('html')>".$_SESSION['lang']['preview']."</button></td>
		</tr>
	</table>";

CLOSE_BOX();
OPEN_BOX('','');
echo"<div id=container style='height:65vh;'></div>";
CLOSE_BOX();
close_body();
?>  