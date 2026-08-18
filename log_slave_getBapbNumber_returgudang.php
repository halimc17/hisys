<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

// $notransaksi = checkPostGet('notransaksi','');
// $method = checkPostGet('method','');
// $id = checkPostGet('id','');
// $namafile = checkPostGet('namafile','');
if(isTransactionPeriod())//check if transaction period is normal
{
 //default penerimaan barang 
 //tipe 1=masuk normal, 2 retur pengeluaran, 5=pengeluaran normal,6=retur pemsaukan(ke supplier)
 //============================================   
  $gudang	=$_POST['gudang'];
  $periodegdg = $_SESSION['gudang'][$gudang]['tahun'].'-'.$_SESSION['gudang'][$gudang]['bulan'];
  $num=1;//default value 
  $str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht where tipetransaksi<5 and kodegudang='".$gudang."' and tanggal like '".$periodegdg."%' order by notransaksi";	
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  if(owlBaris($res)>0)
  {
  	while($bar=$res->fetch())
	{
		$num=$bar->notransaksi;
		if($num!='')
		{
			$num=intval(substr($num,6,5))+1;
		}
		else
		{
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
        
   $num=$_SESSION['gudang'][$gudang]['tahun'].$_SESSION['gudang'][$gudang]['bulan'].$num."-GR-".$gudang;

  echo $num;
}
else
{
	echo " Error: Transaction Period missing";
}


?>