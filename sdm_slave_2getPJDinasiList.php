<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8')";
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
$notransaksi=0;
$namakaryawan=0;
//========================
//ambil jumlah baris dalam tahun ini
  if(isset($_POST['tex']))
  {
  	$notransaksi.=$_POST['tex'];
  }
$notransaksi = checkPostGet('tex', '');
$namakaryawan = checkPostGet('tex2', '');

  
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht a 
		left join datakaryawan b on a.karyawanid=b.karyawanid
        where a.notransaksi like '%".$notransaksi."%' and b.namakaryawan like '%".$namakaryawan."%'
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
  
$countApprove = getCountApproval('PJDINAS','');
$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);


$str="select a.*, b.namakaryawan from ".$dbname.".sdm_pjdinasht a
		left join datakaryawan b on a.karyawanid=b.karyawanid
        where a.notransaksi like '%".$notransaksi."%' and b.namakaryawan like '%".$namakaryawan."%'
		order by tanggalbuat desc,notransaksi desc limit ".$offset.",20";	
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
   if($bar->statuspersetujuan==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspersetujuan==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else {
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	
   }

   if($bar->statushrd==2)
     $sthrd=$_SESSION['lang']['ditolak'];
  else if($bar->statushrd==1)
     $sthrd=$_SESSION['lang']['disetujui'];
  else{
     $sthrd=$_SESSION['lang']['wait_approve'];
  }
	echo"<tr class=rowcontent>
	  <td align=center>".$no."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggalbuat)."</td>
	  <td>".$bar->tujuan1."</td>";
		for($i=1;$i<=$countApprove;$i++){
			$arrApp = detailApprove($i,$bar->notransaksi,'PJDINAS');

			if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
				$tngl='';
			}else{
				$tngl=tanggalnormal($arrApp['tanggal']);
			}

			if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
				echo"<td>".$arrApp['nama']."
					<br />".$arrHsl[$arrApp['status']]."
					<br>".$tngl."
				</td>";
			}else{
				echo"<td>&nbsp;</td>";
			}
		}
	  
	  echo"<td align=center>
	     <img src='images/pdf.jpg' class='resicon'  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."',event);\"> 
	  </td>
	  </tr>";
  }
  echo"<tr><td colspan=10 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";
?>