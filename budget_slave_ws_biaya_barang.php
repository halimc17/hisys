<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
$tab=$_POST['tab'];
if((isset($_POST['txtfind']))!=''){
	$awalan=$_POST['awalan'];
	$txtfind=$_POST['txtfind'];
	$thnBudget=checkPostGet('thnBudget','');
	$kodeOrg=$_SESSION['empl']['lokasitugas'];
	
	# Jangan berdasarkan Lokasi Tugas
	# Jika, ada salah kasih detail akses dan edit transaksi lain
	# Nanti akan ada Bug Harga Satuan
	// $sRegion="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kodeOrg,0,4)."' ";
	// $qRegion=$owlPDO->query($sRegion) or die(print " Gagal: ".PDOException::getMessage());
	// $qRegion->setFetchMode(PDO::FETCH_ASSOC);
	// $rRegion=$qRegion->fetch();
	// $region = $rRegion['regional'];

	$kodews=$_POST['kodews'];
	$kodeorg=substr($kodews,0,4);
	$str="select * from ".$dbname.".bgt_regional_assignment where kodeunit = '".$kodeorg."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$hkef='';
	while($bar= $res->fetch()){
		$region=$bar->regional;
	}
	
	$wh='';
	if($awalan!=''){
		$wh=" and kodebarang like '".$awalan."%' ";
	}
	$str="select * from ".$dbname.".log_5masterbarang where 1=1 ".$wh." and (namabarang like '%".$txtfind."%' or kodebarang like '%".$txtfind."%') and substr(kodebarang,1,1) in ('3','8') ";
		echo"
		<div style=\"overflow:auto; height:332px;\" >
		<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
		<tr class=rowheader>
			<th align=center>No</th>
			<th align=center>Kode Barang</th>
			<th align=center>Nama Barang</th>
			<th align=center>Satuan</th>
			<th align=center>Harga</th>
		</tr>
		</thead>
		<tbody>";
		$no=0;	 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);            
		while($bar=$res->fetch()){

			$sHrg="select distinct hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$region."' and kodebarang='".$bar->kodebarang."' and tahunbudget='".$thnBudget."' and closed=1";
            $qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
            $qHrg->setFetchMode(PDO::FETCH_ASSOC);
			$rHrg=$qHrg->fetch();

			$no+=1;
			if($bar->inactive==1){
				echo"<tr class=rowcontent style='cursor:pointer;background-color:red;'  title='Inactive' >";
					$bar->namabarang=$bar->namabarang. " [Inactive]";
			}else{
					if($tab=='1')
						echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setBrg(1,'".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."','".$rHrg['hargasatuan']."')\" title='Click' >";
					if($tab=='2')
						echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setBrg(2,'".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."','".$rHrg['hargasatuan']."')\" title='Click' >";
				}   
			echo" <td align=center>".$no."</td>
					<td align=center>".$bar->kodebarang."</td>
					<td>".$bar->namabarang."</td>
					<td align=center>".$bar->satuan."</td>";
					
			
			echo"<td align=right>".@number_format($rHrg['hargasatuan'])."</td>";
			
		echo"</tr>";
		}	 
		echo "</tbody>
				<tfoot>
				</tfoot>
				</table></div>";	
}
?>