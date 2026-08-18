<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini
  $notransaksi='';
   if(isset($_POST['tex']))
  {
  	$notransaksi.=" and notransaksi like '%".$_POST['tex']."%' ";
  } 
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where statuspertanggungjawaban=1 and lunas=0
		".$notransaksi."
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
        where  statuspertanggungjawaban=1 and lunas=0
		".$notransaksi."
		order by tanggalbuat desc  limit ".$offset.",20";	
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
	  
	//==================ambil jumlah pertanggungjawaban
	$strv="select sum(jumlahhrd) as vali from ".$dbname.".sdm_pjdinasdt
	       where notransaksi='".$bar->notransaksi."' and sumber=1";
	$vali=0;
	$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
	$resv->setFetchMode(PDO::FETCH_OBJ);
	while($barv=$resv->fetch())
	{
		$vali=$barv->vali;
	}	   
	 //sisa adalah dp diterima kurang penggunaan
	 $vali=$bar->uangmuka-$vali;
	  
	//===============================================  
   if($bar->statuspertanggungjawaban==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspertanggungjawaban==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else 
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	  
   
   $str1="select sum(jumlahhrd*frekuensi) as jumlah from ".$dbname.".sdm_pjdinasdt
         where notransaksi='".$bar->notransaksi."' and sumber=0";
   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
   $res1->setFetchMode(PDO::FETCH_OBJ);
   
   $usage=0;
   while($bar1=$res1->fetch())
   {
   	 $usage=$bar1->jumlah;
   }	 	 
	  
	echo"<tr class=rowcontent>
	  <td>".$no."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggalbuat)."</td>
	  <td>".$bar->tujuan1."</td>
	  <td align=right>".number_format($bar->uangmuka,2,'.',',')."</td>
	  <td align=right>".number_format($usage,2,'.',',')."</td>
	  <td>".$stpersetujuan."</td>
	  <td align=right>".number_format($vali,2,'.',',')."</td>
                      <td align=right><input type=text class=myinputtextnumber size=14 onkeypress=\"return angka_doang(event);\" value=".($bar->notransaksi!=0?$bar->byticket:0)." id=t".$bar->notransaksi."></td>	  <td align=center>
                         <button class=mybutton onclick=savePenyelesaianPJD('".$bar->notransaksi."','".$vali."')>".$_SESSION['lang']['save']."</button>    
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."',event);\"> 
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