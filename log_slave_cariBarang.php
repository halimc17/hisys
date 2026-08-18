<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zFunction.php');
//=============================================

if(isTransactionPeriod())//check if transaction period is normal
{
      //  echo " Error:".$_POST['induk'];
		$txtcari=$_POST['txtcari'];
		$gudang=$_POST['gudang'];
		$tanggal=tanggalsystemn($_POST['tanggal']);
		if($tanggal=='--'){
			exit("Warning : Tanggal harus diisi.");
		}
		
		$pemilikbarang=$_POST['pemilikbarang'];
		$str="select a.kodebarang,a.namabarang,a.satuan from
		      ".$dbname.".log_5masterbarang a where (a.namabarang like '%".$txtcari."%' or kodebarang like '%".$txtcari."%')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		if(owlBaris($res)<1){
			echo"Error: ".$_SESSION['lang']['tidakditemukan'];			
		}
		else
		{
		echo"<br><table class=sortable cellpadding=5 cellspacing=1 border=0 style=width:100%>
		     <thead>
			      <tr class=rowheader>
				      <th align=center>No</th>
					  <th align=center>".$_SESSION['lang']['kodebarang']."</th>
					  <th align=center>".$_SESSION['lang']['namabarang']."</th>
					  <th align=center>".$_SESSION['lang']['satuan']."</th>
					  <th align=center>".$_SESSION['lang']['saldo']."</th>
				  </tr>
		     </thead>
			 <tbody>";
			$no=0;	 
			while($bar=$res->fetch())
			{
				
				//ambil saldo barang
				$saldoqty=0;
				$str1="select saldoakhirqty as saldoqty from ".$dbname.".log_5saldobulanan where kodebarang='".$bar->kodebarang."'
				       and kodegudang='".$gudang."' and periode='".substr($tanggal,0,7)."'";
				$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				while($bar1=$res1->fetch())
				{
					$saldoqty=$bar1->saldoqty;
				}


				//ambil pengeluaran barang yang belum di posting
				$qtynotposted=0;
				$str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
                       b on a.notransaksi=b.notransaksi where a.kodept='".$pemilikbarang."' and b.kodebarang='".$bar->kodebarang."' 
					   and a.tipetransaksi>4
					   and a.kodegudang='".$gudang."'
					   and a.post=0
					   group by kodebarang";
                $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_OBJ); 
				while($bar2=$res2->fetch())
				{
					$qtynotposted=$bar2->jumlah;
				}
				if($qtynotposted=='')
				   $qtynotposted=0;
				
				#dikurangi dengan potensi dari bkm
				// $queryM="select sum(kwantitas) as jlh from ".$dbname.".kebun_pakai_material_vw where kodegudang='".$gudang."' and kodebarang='".$bar->kodebarang."' and jurnal=0";
				$queryM="select sum(kwantitas) as jlh from ".$dbname.".kebun_pakai_material_vw where kodegudang='".$gudang."' and kodebarang='".$bar->kodebarang."' and jurnal=0 and tanggal like '".substr($tanggal,0,7)."%'";
				$dataM = fetchData($queryM);
				$pakaibkm = $dataM[0]['jlh'];
				// $saldoqty=($saldoqty+$qtynotpostedin)-$qtynotposted;
				$saldoqty=$saldoqty-$qtynotposted-$pakaibkm;
				
			  if($saldoqty>0)
			   {
				$no+=1;
				echo"<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"loadField('".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."','".$saldoqty."');\">
				   <td align=center>".$no."</td>
				  <td align=center>".$bar->kodebarang."</td>
				  <td>".$bar->namabarang."</td>
				  <td>".$bar->satuan."</td>
				  <td align=right>".numberformat_kasih_koma($saldoqty)."</td>
			      </tr>";			   	
			   }
			}
		echo    "
				 </tbody>
				 <tfoot></tfoot>
				 </table><i>*Barang yang muncul hanya yang memiliki saldo.</i><br><i>*Saldo = saldo saat ini - pengeluaran yang belum di posting</i>";	
		}  
}
else
{
	echo " Error: Transaction Period missing";
}
?>