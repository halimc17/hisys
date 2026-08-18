<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


$method=checkPostGet('method','');
$gudang=checkPostGet('gudang','');
$schnotransaksi=checkPostGet('schnotransaksi','');
$nopo=checkPostGet('nopo','');
$notransaksi=checkPostGet('notransaksi','');

$idsupplier=checkPostGet('idsupplier','');
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
$nopo=checkPostGet('nopo','');
$qty=checkPostGet('qty','');
$satuan=checkPostGet('satuan','');
$nopp=checkPostGet('nopp','');
$kodebarang=checkPostGet('kodebarang','');



$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
switch($method){
	
	case'delht':
		$str="delete from ".$dbname.".log_penerimaanpoht where notransaksi='".$notransaksi."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}	
	break;
	
	case'postht':
		$str="update ".$dbname.".log_penerimaanpoht set statusjurnal='1' where notransaksi='".$notransaksi."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}	
	break;
	
	
	case'simpan':
	
		#ambil jumlah lalu
		
		$str="select a.jumlah as jumlah,b.nopo as nopo,a.notransaksi as notransaksi 
	    from ".$dbname.".log_transaksidt a,
	         ".$dbname.".log_transaksiht b
		   where a.notransaksi=b.notransaksi and  
		   b.nopo='".$nopo."' 
	       and a.kodebarang='".$kodebarang."'
	       and a.nopp='".$nopp."'
		   and a.notransaksi<'".$notransaksi."'
		   order by notransaksi desc limit 1";	   
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
			$jumlahlalu=$bar['jumlah'];
			if($jumlahlalu==''){
				$jumlahlalu=0;
			}
			
		#ambil harga satuan	
		$str="select hargasatuan,jumlahpesan,satuan,matauang,kodebarang from ".$dbname.".log_podt where 
	      nopo='".$nopo."' and kodebarang='".$kodebarang."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
			$hargasatuan=$bar['hargasatuan'];	
	
		#cek apakah sudah insert di ht
		$str="select count(*) as jumht from ".$dbname.".log_penerimaanpoht where notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
			$jumht=$bar['jumht'];
	
		if($jumht<1){		
			#insert header dulu
			$str="insert into ".$dbname.".log_penerimaanpoht (
					`tipetransaksi`,`notransaksi`,`tanggal`,
					`kodept`,`nopo`,`unit`,`user`,
					`idsupplier`)
			values(1,'".$notransaksi."','".$tanggal."',
					'".$_SESSION['org']['kodeorganisasi']."','".$nopo."','".$gudang."',".$_SESSION['standard']['userid'].",
						'".$idsupplier."')";
			try {
					$owlPDO->exec($str);
			} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "<br/>"; 
					die(); 
			}			
		}
		
		#delete 1st
		$str="delete from ".$dbname.".log_penerimaanpodt where notransaksi='".$notransaksi."' and nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}	
		
		$str="insert into ".$dbname.".log_penerimaanpodt (
			`notransaksi`,`kodebarang`,
			`satuan`,`jumlah`,`jumlahlalu`,
			`hargasatuan`,`nopp`,`nopo`)
			values('".$notransaksi."','".$kodebarang."',
			'".$satuan."',".$qty.",".$jumlahlalu.",
			".$hargasatuan.",'".$nopp."','".$nopo."')";
		
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}	
	break;
	
	
	
	
	case'getdatapo':
	
			echo"<table class=sortable cellspacing=1 border=0 >
				 <thead>
					 <tr class=rowheader>
					   <td align=center>No.</td>
					   <td align=center>".$_SESSION['lang']['kodebarang']."</td>
					   <td align=center>".$_SESSION['lang']['namabarang']."</td>
					   <td align=center>".$_SESSION['lang']['satuan']."</td>
					   <td align=center>".$_SESSION['lang']['sudahditerima']."</td>
					   <td align=center>".$_SESSION['lang']['kuantitaspo']."</td>		   
					   <td align=center width=75px >".$_SESSION['lang']['diterima']."</td>
					   <td align=center>".$_SESSION['lang']['keterangan']."</td>
					   <td align=center>".$_SESSION['lang']['nopp']."</td>
					   <td align=center>Action</td>
					 </tr>
					 </thead><tbody>
					 ";
				$no=0;	 
				//get PO detail for this nopo
			 $str="select * from ".$dbname.".log_podt where nopo='".$nopo."'";
			 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			 $res->setFetchMode(PDO::FETCH_OBJ);
			 while($bar=$res->fetch()){
				$no++;
				 $qtypo=$bar->jumlahpesan;
				 $jumlah=$qtypo;//default qty adalah jumlah po
				 $namabarang='';
				 $satuan='';
				 //ambil nama barang dan satuan
				 $str2="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
				 $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				 $res2->setFetchMode(PDO::FETCH_OBJ);
				 while($bar1=$res2->fetch()) {
						$namabarang=$bar1->namabarang;
						$satuan=$bar1->satuan;
				 }
					 //cek konversi satuan
					 
				 if($satuan!=$bar->satuan){
						//konversi satuan jika satuan default kodebarang tidak sama dengan satuan po
						$str1="select jumlah from ".$dbname.".log_5stkonversi 
							   where darisatuan='".$satuan."' and satuankonversi='".$bar->satuan."'
							   and kodebarang='".$bar->kodebarang."'";
						$res3=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
						$res3->setFetchMode(PDO::FETCH_OBJ);
						while($bar2=$res3->fetch()){
							$jumlah=round($qtypo/$bar2->jumlah);//mengkonversi satuan
						}	   
				 }

				//==================ambil jumlah lalu====================
				 $jumlahlalu=0;
				//===========khusus untuk edit
				 $sddt='';
				 $jumlahedit=0;
				 //ambil value transaksi
				 $strh="select jumlah from ".$dbname.".log_penerimaanpodt where 
						notransaksi='".$notransaksi."'
								and kodebarang='".$bar->kodebarang."'";
				 $resh=$owlPDO->query($strh) or die(print " Gagal: ".PDOException::getMessage());
				 $resh->setFetchMode(PDO::FETCH_OBJ);
				 while($barh=$resh->fetch()) {
						$jumlahedit=$barh->jumlah;
				  }		 

				 if($notransaksi!='') {//khusus untuk edit, jumlah lalu tidak termasuk jumlah yg di edit
					$sddt=" and a.notransaksi!='".$notransaksi."' ";
				 }
			//++++++++++++++++++++++++++++++
				$strx="select sum(a.jumlah) as jumlah,a.kodebarang as kodebarang 
				from ".$dbname.".log_penerimaanpodt a,
					 ".$dbname.".log_penerimaanpoht b
					   where a.notransaksi=b.notransaksi 
					   and b.nopo='".$nopo."' 
				   and a.kodebarang='".$bar->kodebarang."'
					   ".$sddt."
					   group by kodebarang";
					
					$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
					$resx->setFetchMode(PDO::FETCH_OBJ);
					while($barx=$resx->fetch()){
							$jumlahlalu=$barx->jumlah;
					}			 

					if($notransaksi!='')//jika proses edit
					   $sisa=$jumlahedit;//tampilkan value data yang di edit
					else  
					   $sisa=$jumlah-$jumlahlalu;//jika tidak tampilkan sisa yang belum terima



					if($notransaksi!='' && $jumlahedit==0)//jika bukan barang yang termasuk dalam
					  $disab='disabled';                  //bapb yng di edit maka di disable    
					else{  
					if($sisa<=0)
					  $disab='disabled';
					else
					  $disab=''; 
					}
					$xyz=$jumlah-$jumlahlalu;
					
					 echo"<tr class=rowcontent>
					   <td align=center>".$no."</td>
					   <td align=center>".$bar->kodebarang."</td>
					   <td>".$namabarang."</td>
					   <td  align=center>".$satuan."</td>
					   <td align=right>".number_format($jumlahlalu,2,'.',',')."</td>
					   <td align=right>".number_format($jumlah,2,'.',',')."</td>
					   <td align=center style=max-width:60px ><input type=text ".$disab." class=myinputtextnumber id='qty".$no."' onkeypress=\"return angka_doang(event);\" value='".$sisa."' style=width:70px maxlength=12></td>
					   <td>".$bar->catatan."</td>
					   <td>".$bar->nopp."</td>
					   <td align=center><button class=mybutton id='btn".$bar->kodebarang."' onclick=\"saveItemPo('".$bar->kodebarang."',".$xyz.",'".$bar->nopp."','".$satuan."',".$no.")\" ".$disab.">".$_SESSION['lang']['save']."</button>";"
					 </tr>";	 	
			 }
			//get karyawan yang lokasi tugas sama atau lokasi tugas sama dengan induk
			  $optmengetahui="<option value=''></option>";
			  $str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."' or lokasitugas='".$_SESSION['org']['induk']."'";	 
			  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			  $res->setFetchMode(PDO::FETCH_OBJ);  
			  while($bar=$res->fetch())  {
					$optmengetahui.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
			  }

			echo"</tbody>
			 <tfoot>
				 <tr>
				   <td><td><td><td><td><td><td><td><td><td align=center>
				   <button onclick=selesaiBapb() class=mybutton>".$_SESSION['lang']['done']."</button>
				   </td></td></td></td></td></td></td></td></td></td>
				 </tr>
				 </tfoot>
				 </table>
				 ";						
	
	
	break;
	
	
	
	case'goCariPo':
		echo"<table cellspacing=1 border=0 class=sortable width=100%>
        <thead>
		<tr class=rowheader><td align=center>No</td>
		    <td align=center>".$_SESSION['lang']['nopo']."</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>".$_SESSION['lang']['purchaser']."</td>
		</tr>
		</thead>
		</tbody>";
		$str="select * from ".$dbname.".log_poht where nopo like '%".$nopo."%'
				and kodeorg = '".$_SESSION['org']['kodeorganisasi']."' and stat_release=1 order by tanggal desc,nopo desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch()) {
			$purchaser='';
			if(!empty($bar->purchaser)) {
				$str="select namauser from ".$dbname.".user where karyawanid=".$bar->purchaser;
				$resv=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resv->setFetchMode(PDO::FETCH_OBJ);
				while($barv=$resv->fetch()) {
					$purchaser=$barv->namauser;
				}
			}
			$no+=1;
			echo"
			<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"goPickPo('".$bar->nopo."','".$bar->kodesupplier."','".$nmsup[$bar->kodesupplier]."')\"><td align=center>".$no."</td>
				<td align=left>".$bar->nopo."</td>
				<td>".tanggalnormal($bar->tanggal)."</td>
				<td>".$purchaser."</td>
			</tr>
			";
		}	 			
		echo"</tbody>
		 <tfoot>
		 </tfoot>
		 </table>";		
	break;
	
	
	case'getnotransaksi':
		$dateno=date('Ym');
		$num=1;//default value 
		$str="select max(notransaksi) as notransaksi from ".$dbname.".log_penerimaanpoht 
				where unit='".$gudang."' and notransaksi like '".$dateno."%' order by notransaksi limit 1";	
				
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		if(owlBaris($res)>0){
			while($bar=$res->fetch()){
				$num=$bar->notransaksi;
				if($num!=''){
					$num=intval(substr($num,6,5))+1;
				} else {
					$num=1;	
				}	
			}
		}
		if($num<10)
		$num='0000'.$num;
		else if($num<100)
		$num='000'.$num;
		else if($num<1000)
		$num='00'.$num;	   
		else if($num<10000)
		$num='0'.$num;
		else
		$num=$num;
		$num=$dateno.$num.$gudang;	            	
		echo $num;
	break;
	
	
	case'getlistdata':
		$limit=20;
		$page=0;
		//========================

		//ambil jumlah baris dalam tahun ini
		$add='';//default serach id nothing
		if($schnotransaksi!=''){
			$add=" and notransaksi like '%".$schnotransaksi."%'";
		}
		$str="select count(*) as jlhbrs from ".$dbname.".log_penerimaanpoht where 1=1 ".$add." order by jlhbrs desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$jlhbrs=$bar->jlhbrs;
		}		
		//==================
		 
		if(isset($_POST['page'])){
			$page=$_POST['page'];
			if($page<0)
			$page=0;
		}
	 
		$offset=$page*$limit;
		$str="select * from ".$dbname.".log_penerimaanpoht where 1=1 ".$add." order by notransaksi desc limit ".$offset.",20";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch()){
			$no++;
			//====================ambil username pembuat
			$namapembuat='';
			$stry="select namauser from ".$dbname.".user where karyawanid=".$bar->user;
			$resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
			$resy->setFetchMode(PDO::FETCH_OBJ);
			while($bary=$resy->fetch()){
				$namapembuat=$bary->namauser;
			}   
			
			if($bar->statusjurnal == 0)
			{
				//tambahkan tombol edit dan delete
				$add="<img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"postht('".$bar->notransaksi."');\">";
				$add.="&nbsp <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editht('".$bar->notransaksi."','".tanggalnormal($bar->tanggal)."','".$bar->idsupplier."','".$nmsup[$bar->idsupplier]."','".$bar->nopo."','".$bar->unit."');\">";
				$add.="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delht('".$bar->notransaksi."');\">";
			}
			else
			{
				$add="<img src=images/skyblue/posted.png class=resicon title='Posted'>";
			}

			echo"<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$bar->unit."</td>
				<td>".$bar->notransaksi."</td>
				<td>".tanggalnormal($bar->tanggal)."</td>
				<td>".$bar->kodept."</td>
				<td>".$bar->nopo."</td>	
				<td>".$nmsup[$bar->idsupplier]."</td> 
				<td>".$namapembuat."</td>
				<td align=center>
				 ".$add."
				
				</td>
			</tr>";// <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewBapb('".$bar->notransaksi."',event);\"> 
		}
		echo"<tr><td colspan=11 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
		<br>
		<button class=mybutton onclick=cariBapb(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBapb(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
	break;
}

?>