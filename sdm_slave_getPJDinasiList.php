<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
	$whereKary = " and bagian = 'HHRS'";
} else {
	$whereKary = " and bagian = 'HRA' and
		kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
}


$optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and
	  tipekaryawan in ('0','7','8') ".$whereKary." and
	  karyawanid <>".$_SESSION['standard']['userid']. " order by namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optKar="<option value=''></option>";
while($bar=$res->fetch())
{
	$optKar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
}	

//limit/page
$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini
$notransaksi="";
  if(isset($_POST['tex']))
  {
  	$notransaksi.=$_POST['tex'];
  }
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where notransaksi like '%".$notransaksi."%'
		and createdby=".$_SESSION['standard']['userid']." 
		and jeniskaryawan=0 and namatamu=''
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
  

  $str="select * from ".$dbname.".sdm_pjdinasht 
        where notransaksi like '%".$notransaksi."%'
        and createdby=".$_SESSION['standard']['userid']." 
        and jeniskaryawan=0 and namatamu=''
		order by tanggalbuat desc limit ".$offset.",20";	
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);		
  $no=$page*$limit;
  while($bar=$res->fetch())
  {
  	$no+=1;

	  $namakaryawan='';
	  $strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->karyawanid;
	  $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	  $resx->setFetchMode(PDO::FETCH_OBJ);
	  while($barx=$resx->fetch())
	  {
	  	$namakaryawan=$barx->namakaryawan;
	  }
	  $add='';
	  if($bar->statuspersetujuan==0)
	  {
	  	$add.="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">
		 &nbsp <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">
         ";
	  }
   if($bar->statuspersetujuan==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspersetujuan==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else {
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	
	//$stpersetujuan.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select style='width:100px;'  onchange=ganti(this.options[this.selectedIndex].value,'persetujuan','".$bar->notransaksi."')>".$optKar."</select>";
   }

   if($bar->statushrd==2)
     $sthrd=$_SESSION['lang']['ditolak'];
  else if($bar->statushrd==1)
     $sthrd=$_SESSION['lang']['disetujui'];
  else{
     $sthrd=$_SESSION['lang']['wait_approve'];
	 // $sthrd.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select style='width:100px;'  onchange=ganti(this.options[this.selectedIndex].value,'hrd','".$bar->notransaksi."')>".$optKar."</select>";
  }

  
  	$stat = array('0' =>$_SESSION['lang']['wait_approve'],'1' =>$_SESSION['lang']['disetujui'],'3'=>$_SESSION['lang']['ditolak'] );
  	$strap="select * from ".$dbname.".approval 
        where notransaksi='".$bar->notransaksi."'
		order by level asc";	
	$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
	$resap->setFetchMode(PDO::FETCH_ASSOC);
	while($barap=$resap->fetch())
	{
		$ttl.="Persetujuan ".$barap['level']." : ".$stat[$barap['status']]."\n";
	}

  
	echo"<tr class=rowcontent>
	  <td align=center>".$no."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggalbuat)."</td>
	  <td align=center title='".$ttl."'>".$stpersetujuan."</td>
	  <td align=center>
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."','".$bar->jeniskaryawan."',event);\"> 
       ".$add."
	  </td>
	  </tr>";
  }
  echo"<tr><td colspan=11 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";
?>