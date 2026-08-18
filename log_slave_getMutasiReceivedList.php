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
$limit=15;
$page=0;
//========================
  $gudang=$_POST['gudang'];
//ambil jumlah baris dalam tahun ini
  $add='';//default serach id nothing
  if(isset($_POST['tex']))
  {
  	$notransaksi=$_POST['tex']."%-".$gudang;
	$add=" and notransaksi like '".$notransaksi."'";
  }
$str="select count(*) as jlhbrs from ".$dbname.".log_transaksiht where kodegudang='".$gudang."'
        and tipetransaksi=3
		".$add."
		order by jlhbrs desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$jlhbrs=$bar->jlhbrs;
}		
//==================
		 
  if(isset($_POST['page']))
     {
	 	$page=$_POST['page'];
	    if($page<0)
		  $page=0;
	 }
	 
  
  $offset=$page*$limit;
  

  $str="select * from ".$dbname.".log_transaksiht where kodegudang='".$gudang."'
        and tipetransaksi=3
		".$add."
		order by notransaksi desc limit ".$offset.",15";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  $no=$page*$limit;
  while($bar=$res->fetch())
  {
  	$no+=1;
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
	  $namaposting='Not Posted';
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
	  
	 if($namaposting=='Not Posted' && $bar->post==1)
	  {
	  	$namaposting=" Posted By ???";
	  }		     
	
	$sSj="select distinct nosj from ".$dbname.".log_transaksiht where notransaksi='".$bar->notransaksireferensi."'";
	$qSj=$owlPDO->query($sSj) or die(print " Gagal: ".PDOException::getMessage());
	$qSj->setFetchMode(PDO::FETCH_OBJ);
	$rSj=$qSj->fetch();
	
	echo"<tr class=rowcontent>
	  <td align=center>".$no."</td>
	  <td>".$bar->kodegudang." - ".$optorg[$bar->kodegudang]."</td>
	  <td  align=center title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".getDetailTipeMutasi($bar->tipetransaksi)."</td>
	  <td>".(isset($rSj->nosj)? $rSj->nosj: '')."</td>
          <td>".$bar->notransaksi."</td>
	  <td>".tanggalnormal($bar->tanggal)."</td>
	  <td>".$bar->kodept."</td>
	  <td>".$bar->gudangx."</td>	
	  <td>".$bar->notransaksireferensi."</td> 
	  <td>".$namapembuat."</td>
	  <td>".$namaposting."</td>";
	if($bar->post!=1) {
		echo "<td align=center><img src=images/application/application_delete.png class=resicon  caption='Delete' 
				onclick=\"del('".$bar->notransaksi."');\"></td>";
	}else{
		echo"<td align=center></td>";
	}
	 echo"<td align=center>
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewReceived('".$bar->notransaksi."',event);\"></td>";
	echo "
	  </tr>";
  }
  echo"<tr><td colspan=13 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariBapbReceived(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariBapbReceived(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";
}
else
{
	echo " Error: Transaction Period missing";
}
?>