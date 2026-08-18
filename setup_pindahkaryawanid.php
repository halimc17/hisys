<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript1.2 src=js/setup_pindahkaryawanid.js></script>
<?php

$str="select namakaryawan,karyawanid,lokasitugas from ".$dbname.".datakaryawan  order by namakaryawan asc";
//exit("Error ".$str);

//$res=mysql_query($str);

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());	
$res->setFetchMode(PDO::FETCH_ASSOC);


  $opt="<option value='".$_SESSION['empl']['kodeorganisasi']."'>".$_SESSION['empl']['name']."</option>";
while($bar=$res->fetch())
{
	$opt.="<option value='".$bar['karyawanid']."##{$bar['lokasitugas']}'>".$bar['namakaryawan']." - ".$bar['lokasitugas']."</option>";
}
//echo $opt;
OPEN_BOX('','Pindah Nama Karyawan');
echo "<br><br>Anda sedang menggunakan account :<b>".$_SESSION['empl']['name']."</b><br> ".$_SESSION['empl']['tujuan']."
      <select id=jabatanbaru>".$opt."</select><br>
	  <button class=mybutton onclick=gantiJabatan()>".$_SESSION['lang']['save']."</button>
	  ";
	  
	 
CLOSE_BOX();

echo close_body();
?>
