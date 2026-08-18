<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/log_3rekalkulasi_stock.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_3rekalkulasi_stock').'</span>');

//=================ambil unit;  
//if($_SESSION['empl']['tipelokasitugas']=='HOLDING') 

$unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
$gudang_detailAkses=" (".$unitDetailAkses.") ";

// create array 
// Hapus tanda kutip tunggal
$stringData = str_replace("'", "", $unitDetailAkses);

// Ubah menjadi array
$arrayData = explode(',', $stringData);
// GUDANGX
// Array untuk menampung klausa-klausa LIKE
$conditions_kodeorganisasi = [];

// Loop melalui setiap nilai dan buat klausa LIKE
foreach ($arrayData as $value) {
    $conditions_kodeorganisasi[] = "kodeorganisasi LIKE '{$value}%'";
}

// Gabungkan semua klausa LIKE dengan 'OR'
$whereClause_gudangx =  "AND (\n    " . implode(" OR\n    ", $conditions_kodeorganisasi) . "\n)";
// AKHIR GUDANGX


if(count($unitDetailAkses) > 0){
	$str = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi
		  where tipe like 'GUDANG%' ".$whereClause_gudangx."
		  order by kodeorganisasi";
}else{
	$str = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi
		  where tipe like 'GUDANG%' and kodeorganisasi like '" . $_SESSION['empl']['lokasitugas'] . "%'
		  order by kodeorganisasi";
}
//else
//$str="select distinct kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
//      where tipe= 'GUDANG' and kodeorganisasi like '%".$_SESSION['empl']['lokasitugas']."%'
//	  order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optunit = "<option value=''></option>";
while ($bar = $res->fetch()) {
	$d=substr($bar->kodeorganisasi,0,4);
	if($d!=$n){	
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optunit.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->kodeorganisasi . " - " . $bar->namaorganisasi . "</option>";
	$n=$d;

	if($d!=$n){		
		$optunit.="</optgroup>";
	}
}
/*
  <tr>
  <td>".$_SESSION['lang']['periode']."</td>
  <td><select id=periode onchange=hideById('printPanel')>".$optper."</select></td>
  </tr>

 */
echo"<br><fieldset style=float:left>
     <legend>Form</legend>
	 <table cellspacing=1 border=0><tr>
	   <td>" . $_SESSION['lang']['daftargudang'] . "</td>
	   <td>:
	   </td>
	   <td>
	     <select id=unit style='width:200px;' onchange=ambilPeriode(this.options[this.selectedIndex].value)>" . $optunit . "</select></td>
	 </tr>
	 <tr>
	   <td colspan=2></td><td><button class=mybutton onclick=getTransaksiGudang()>" . $_SESSION['lang']['proses'] . "</button></td>
	 </tr></table>
	  <fieldset style='width:500px;'>
		  Rekalkulasi stok akan melihat dan memperbaiki konsistensi nilai transaksi dan saldo akhir fisik barang.
	  </fieldset>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('', '');
echo"<span id=printPanel> 
	 </span>    
	 <div style='width:100%;height:359px;overflow:auto;'  id=container>
     </div>";
CLOSE_BOX();
close_body();
?>