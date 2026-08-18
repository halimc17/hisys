<?php
        require_once('master_validation.php');
        require_once('config/connection.php');
$thnplaf=$_POST['tahun'];
$find=substr($_SESSION['empl']['lokasitugas'],0,4);

$find=$find.$thnplaf;

$str="select notransaksi from ".$dbname.".sdm_pengobatanht where 
      notransaksi like '".$find."%' order by notransaksi desc limit 1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar=$res->fetch())
{
        $no=substr($bar->notransaksi,8,4);
}	
$no+=1; 
if($no<10)
{
        $no='000'.$no;
} 
else if($no<100)
{
        $no='00'.$no;
}
else if($no<1000)
{
        $no='0'.$no;
}

if($thnplaf!='')
  echo $find.$no;
?>