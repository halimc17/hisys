<?php
	require_once('master_validation.php');
	require_once('config/connection.php');

		$txtfind=$_POST['txtfind'];
		$str="select * from ".$dbname.".log_prapoht where nopp like '%".$txtfind."%' and close='2'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
			echo"<table class=data cellspacing=1 cellpadding=2  border=0>
				 <thead>
				 <tr class=rowheader>
				 <td class=firsttd>
				 No.
				 </td>
				 <td>No. PP</td>
				 <td>Kode Barang</td>
				 <td>Nama Barang</td>
				 <td>Jumlah Diminta</td>
				 </tr>
				 </thead>
				 <tbody>";
			$no=0;	 
			while($bar=$res->fetch())
			{
			  	//query detail pp
				$sql="select * from ".$dbname.".log_prapodt where nopp='".$bar->nopp."'";
				$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
				$query->setFetchMode(PDO::FETCH_OBJ);

				$res2=$query->fetch();
				
				//get data dari log_5masterbarang, master barang
				$sql2="select * from ".$dbname.".log_5masterbarang where kodebarang='".$res2->kodebarang."'";
				$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
				$query2->setFetchMode(PDO::FETCH_OBJ);

				$res3=$query2->fetch();
				
				//get data dari log_5masterbarangdt, master barang detail
				$sql3="select * from ".$dbname.".log_5masterbarangdt where kodebarang='".$res3->kodebarang."'";
				$query3=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
				$query3->setFetchMode(PDO::FETCH_OBJ);

				$res4=$query3->fetch();
				
				$no+=1;
				echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setPp('".$bar->nopp."','".$bar->kodebarang."','".$bar->namabarang."','".$bar->jumlah."','".$bar->satuan."')\" title='Click' >
					  <td class=firsttd>".$no."</td>
					   <td>".$bar->nopp."</td>
					  <td>".$bar->kodebarang."</td>
					  <td>".$bar->namabarang."</td>
					  <td>".$bar->jumlah."</td>
					 </tr>";
			}	 
			echo "</tbody>
				  <tfoot>
				  </tfoot>
				  </table>";	   	
?>