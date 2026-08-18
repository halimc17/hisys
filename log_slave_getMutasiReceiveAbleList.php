<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');

$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


if(isTransactionPeriod())//check if transaction period is normal
{
	//limit/page
	$limit=20;
	$page=0;
	
	//========================
	$gudang=$_POST['gudang'];
	$tgl=$_POST['tanggal'];
	$add='';//default serach id nothing
	if(isset($_POST['tex'])) {
		$notransaksi=$_POST['tex']."%";
		$add=" and notransaksi like '".$notransaksi."'";
	}
	$tanggalDt=explode("-",$tgl);
	$tahunnext=$tanggalDt[2]+1;
	
	//ambil jumlah baris dalam tahun ini
	// $str="select count(*) as jlhbrs from ".$dbname.".log_transaksiht where notransaksi in (
	// 	select a.notransaksi from ".$dbname.".log_transaksi_vw a inner join ".$dbname.".log_transaksidt b on a.`kodebarang`=b.`kodebarang` and a.nopp=b.nopp
	// 	where tipetransaksi=7 and gudangx='".$gudang."' and ((a.jumlah-b.jumlah)>0 or a.notransaksireferensi is null))
	// 	".$add." and gudangx='".$gudang."' and tanggal like '".$tanggalDt[2]."%' order by jlhbrs desc";
$str="select count(*) as jlhbrs from ".$dbname.".log_transaksiht where left(tanggal,4)<='".$tahunnext."' and notransaksi in (
                select a.notransaksi from ".$dbname.".log_transaksi_vw a left join ".$dbname.".log_transaksidt b on a.notransaksireferensi=b.notransaksi and a.`kodebarang`=b.`kodebarang`
                where tipetransaksi=7 and gudangx='".$gudang."' and (a.jumlah-b.jumlah>0 or a.notransaksireferensi is null))
		".$add."		
		and gudangx='".$gudang."' order by jlhbrs desc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()) {
		$jlhbrs=$bar->jlhbrs;
	}		
	//==================
	if(isset($_POST['page'])) {
		$page=intval($_POST['page']);
		if($page<0)
			$page=0;
	}
	$offset=$page*$limit;
	
	// $str="select * from ".$dbname.".log_transaksiht where notransaksi in (
	// 	select a.notransaksi from ".$dbname.".log_transaksi_vw a left join ".$dbname.".log_transaksidt b on  a.`kodebarang`=b.`kodebarang`  and a.nopp=b.nopp
	// 	where tipetransaksi=7 and gudangx='".$gudang."' and (a.jumlah-b.jumlah>0 or (a.notransaksireferensi='' or a.notransaksireferensi is null)))
	// 	".$add." and gudangx='".$gudang."'  and tanggal like '".$tanggalDt[2]."%' order by notransaksi desc limit ".$offset.",20"; 
	$str="select * from ".$dbname.".log_transaksiht where left(tanggal,4)<='".$tahunnext."' and notransaksi in (
                select a.notransaksi from ".$dbname.".log_transaksi_vw a left join ".$dbname.".log_transaksidt b on a.notransaksireferensi=b.notransaksi and a.`kodebarang`=b.`kodebarang`
                where tipetransaksi=7 and gudangx='".$gudang."' and (a.jumlah-b.jumlah>0 or (a.notransaksireferensi='' or a.notransaksireferensi is null)))
		".$add."		
		and gudangx='".$gudang."'	
		order by notransaksi desc limit ".$offset.",20";
	 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	 $res->setFetchMode(PDO::FETCH_OBJ);
	 $no=$page*$limit;
	 while($bar=$res->fetch()) {
		
		//====================ambil username pembuat
		$namapembuat='';
		$stry="select namauser from ".$dbname.".user where karyawanid=".$bar->user;
		$resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
		$resy->setFetchMode(PDO::FETCH_OBJ);
		while($bary=$resy->fetch())
		{
		  $namapembuat=$bary->namauser;
		}   
		//====================ambil username posting
		$namaposting='Hold';
		if(intval($bar->postedby)!=0)
		{
			$stry="select namauser from ".$dbname.".user where karyawanid=".$bar->postedby;
			$resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
			$resy->setFetchMode(PDO::FETCH_OBJ);
			while($bary=$resy->fetch())
			{
			  $namaposting=$bary->namauser;
			}
		}
		  
		if($namaposting=='Hold' && $bar->post==1)
		  {
			$namaposting=" Release By ???";
		  }
	//status apakah sudah diterima
		$status=$_SESSION['lang']['belumterima'];
		if(($bar->notransaksireferensi!='')||!is_null($bar->notransaksireferensi!='')){
			$strCek="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw
						  where notransaksi='".$bar->notransaksi."'";
			$resCek=$owlPDO->query($strCek) or die(print " Gagal: ".PDOException::getMessage());
			$resCek->setFetchMode(PDO::FETCH_OBJ);
			$barCek=$resCek->fetch();
			$jumlah=$barCek->jumlah;
			
			$strCekBpb="select sum(jumlah) as diterima from ".$dbname.".log_transaksi_vw
						  where notransaksireferensi='".$bar->notransaksi."'";
			$resCekBpb=$owlPDO->query($strCekBpb) or die(print " Gagal: ".PDOException::getMessage());
			$resCekBpb->setFetchMode(PDO::FETCH_OBJ);
			$barCek=$resCekBpb->fetch();
			$diterima=$barCek->diterima;
			
			$trHide="";
			if($bar->post==0) {
				$add="<td align=center width=25px></td>";
			} else {
				if(($jumlah-$diterima)==$jumlah) {
					 $no+=1;
					$status=$_SESSION['lang']['belumterima'];
					$add="<td align=center width=25px><img src=images/application/application_go.png class=resicon  title='Process' onclick=\"processReceipt('".$bar->notransaksi."','".tanggalnormal($bar->tanggal)."');\"></td>";
				} else if ($jumlah-$diterima>0){
					$no+=1;
					$status=$_SESSION['lang']['sudahditerimasebagian'];
					$add="<td align=center width=25px><img src=images/application/application_go.png class=resicon  title='Process' onclick=\"processReceipt('".$bar->notransaksi."','".tanggalnormal($bar->tanggal)."');\"></td>";
				} else {
					$no+=1;
					$add='<td align=center width=25px></td>';
					$status=$_SESSION['lang']['sudahditerima'];
					//continue;
				}   
			}
		}
		else if($bar->post!=0)
		{
			$no+=1;
			//jika sudah di post oleh sumber maka dapat diterima
			//karena setelah posting baru ada hargasatuan
			$add=" <td align=center width=25px><img src=images/application/application_go.png class=resicon  title='Process' onclick=\"processReceipt('".$bar->notransaksi."','".tanggalnormal($bar->tanggal)."');\"></td>";
	
		}else{
			$no+=1;
			$add="<td align=center width=25px></td>";
		}
		
		$sSj="select distinct nosj from ".$dbname.".log_suratjalandt where notransaksireferensi='".$bar->notransaksi."'";
		$qSj=$owlPDO->query($sSj) or die(print " Gagal: ".PDOException::getMessage());
		$qSj->setFetchMode(PDO::FETCH_OBJ);
		$rSj=$qSj->fetch();
		if($add!='86'){//menampilkan yang belum diterima saja
			echo"<tr ".$trHide." class=rowcontent>
			  <td align=center>".$no."</td>
			  <td>".$bar->kodegudang." - ".$optorg[$bar->kodegudang]."</td>
			  <td title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".getDetailTipeMutasi($bar->tipetransaksi)."</td>
			  <td>".(isset($bar->nosj)? $bar->nosj: '')."</td>
			  <td>".$bar->notransaksi."</td>
			  <td align=center>".tanggalnormal($bar->tanggal)."</td>
			  <td align=center>".$bar->kodept."</td>
			  <td align=center>".$bar->gudangx." - ".$optorg[$bar->gudangx]."</td>			  
			  <td>".$namapembuat."</td>
			  <td>".$namaposting."</td>
			  <td>".$status."</td>
				 ".$add."
			  <td align=center width=25px>
				 <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewMutasi('".$bar->notransaksi."',event);\"> 
			  </td>
			  </tr>";
		}
	}
	echo"<tr><td colspan=13 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
		<br>
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
} else {
	echo " Error: Transaction Period missing";
}
?>