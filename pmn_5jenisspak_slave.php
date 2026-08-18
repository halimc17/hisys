<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method','');

$kode = checkPostGet('kode','');
$nama = checkPostGet('nama','');
$penjualan = checkPostGet('penjualan','');
$nonpenjualan = checkPostGet('nonpenjualan','');
$akundebet = checkPostGet('akundebet','');
$akunkredit = checkPostGet('akunkredit','');
$keterangan = checkPostGet('keterangan','');
$file = checkPostGet('file','');
$filenonpenjualan = checkPostGet('filenonpenjualan','');
$arrpenjualan=array("0"=>"x","1"=>"√");


switch ($method) {
	
	case 'insert':
	
		#= cek apakah table dan file sudah ada
		$str = "select count(*) as jumlah from ".$dbname.".pmn_5jenisspk where file='".$file."'";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
			$jumlah=$bar['jumlah'];
			if($jumlah>0){
				exit("Warning:Sudah ada file dan table dengan nama ".$file."");
			}			
	
	
		$str = "insert into  ".$dbname.".pmn_5jenisspk 
				(kode, nama,akundebet,akunkredit,keterangan,file,filenonpenjualan,
				penjualan,nonpenjualan,createby, createtime, updateby) 
		values ('".$kode."','".$nama."','".$akundebet."','".$akunkredit."','".$keterangan."','".$file."','".$filenonpenjualan."',
				'".$penjualan."','".$nonpenjualan."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		
		#= buat tablenya
		$str = "CREATE TABLE ".$file." (`xxx` int NULL);";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		
	break;
	
	case 'update':
		$str = "update ".$dbname.".pmn_5jenisspk set nama='".$nama."',akundebet='".$akundebet."',akunkredit='".$akunkredit."',
			keterangan='".$keterangan."',file='".$file."',filenonpenjualan='".$filenonpenjualan."',penjualan='".$penjualan."',nonpenjualan='".$nonpenjualan."'
			where kode = '".$kode."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	
	break;
	
	
	case'delete':
	
		#= cek apakah sudah ada transaksi / belum
		$str = "select count(*) as jumlah from ".$dbname.".".$file;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
			$jumlah=$bar['jumlah'];
			
		if($jumlah>0){
			exit("Warning:Sudah ada transaksi");
		}			
	
		$str = "delete from ".$dbname.".pmn_5jenisspk where kode='".$kode."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
		
		$str = "DROP TABLE ".$file;
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		
		
	break;

   case'loaddata':
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center>".$_SESSION['lang']['debet']."</td>
				<td align=center>".$_SESSION['lang']['kredit']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['file']."</td>
				<td align=center>".$_SESSION['lang']['file']." ".$_SESSION['lang']['nonsales']."</td>
				<td align=center>".$_SESSION['lang']['sales']."</td>
				<td align=center>".$_SESSION['lang']['nonsales']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		$no = 0;
		$str = "select * from ".$dbname.".pmn_5jenisspk";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td>".$bar['kode']."</td>";
            $tab.="<td>".$bar['nama']."</td>";
            $tab.="<td>".$bar['akundebet']."</td>";
            $tab.="<td>".$bar['akunkredit']."</td>";
            $tab.="<td>".$bar['keterangan']."</td>";
            $tab.="<td>".$bar['file']."</td>";
            $tab.="<td>".$bar['filenonpenjualan']."</td>";
            $tab.="<td align=center>".$arrpenjualan[$bar['penjualan']]."</td>";
            $tab.="<td align=center>".$arrpenjualan[$bar['nonpenjualan']]."</td>";
            $tab.="<td>".getNamaKaryawan($bar['updateby'])."</td>";
            $tab.="<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' 
				onclick=\"edit('".$bar['kode']."','".$bar['nama']."','".$bar['akundebet']."','".$bar['akunkredit']."',
				'".$bar['keterangan']."','".$bar['file']."','".$bar['filenonpenjualan']."','".$bar['penjualan']."','".$bar['nonpenjualan']."');\">
            &nbsp;<img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"delet('".$bar['kode']."','".$bar['file']."');\"></td>";
			
			$tab.="</tr>";
			
        }
		$tab.="</table>";
		echo $tab;
		break;

    default:
}
?>
