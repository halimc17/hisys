<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
if(isTransactionPeriod())//check if transaction period is normal
{
//limit/page
$limit=20;
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
        and tipetransaksi=2
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
        and tipetransaksi=2
		".$add."
		order by notransaksi desc limit ".$offset.",20";	
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
	if($bar->post<1)
	{

		//tambahkan tombol edit dan delete
		$add="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delBapb('".$bar->notransaksi."');\">";

//	    $add.="<img src=images/application/book_icon.gif class=resicon  title='Post/Close' onclick=\"postingBapb('".$bar->notransaksi."','".$bar->nopo."');\">";
	}  
    else
	{
		$add='';
	}			     
	  
	echo"<tr class=rowcontent>
	  <td>".$no."</td>
	  <td>".$bar->kodegudang."</td>
	  <td title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".getDetailTipeMutasi($bar->tipetransaksi)."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".tanggalnormal($bar->tanggal)."</td>
	  <td>".$bar->kodept."</td>
	  <td>".$bar->nopo."</td>	
	  <td>".$bar->idsupplier."</td> 
	  <td>".$namapembuat."</td>
	  <td>".$namaposting."</td>
	  <td align=center>
	     ".$add."
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewBapb('".$bar->notransaksi."',event);\"> 
	  </td>
	  </tr>";
  }
  echo"<tr><td colspan=11 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariBapb(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariBapb(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";
}
else
{
	echo " Error: Transaction Period missing";
}
?>