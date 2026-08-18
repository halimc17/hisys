<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');
if(isTransactionPeriod())//check if transaction period is normal
{
	
$nmgudang = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

//limit/page
$limit=15;
$page=0;
//========================
  $gudang=$_POST['gudang'];
   $add='';//default serach id nothing
  if(isset($_POST['tex']))
  {
  	$notransaksi="%".$_POST['tex']."%";
	$add=" and notransaksi like '".$notransaksi."'";
  } 
//ambil jumlah baris dalam tahun ini
$str="select count(*) as jlhbrs from ".$dbname.".log_transaksiht where kodegudang='".$gudang."'
		and tipetransaksi =5
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
	 
  
  $offset=@($page*$limit);
  

  $str="select * from ".$dbname.".log_transaksiht where kodegudang='".$gudang."'
		".$add."
		and tipetransaksi =5
		order by notransaksi desc limit ".$offset.",15";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);	
  $no=@($page*$limit);
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
	
	//====================ambil sub unit
	  $subunit='';
	  $sbUni='';
	  $strSub="select kodeblok from ".$dbname.".log_transaksidt where notransaksi='".$bar->notransaksi."'";
	  $resSub=$owlPDO->query($strSub) or die(print " Gagal: ".PDOException::getMessage());
	  $resSub->setFetchMode(PDO::FETCH_OBJ);
	  while($barSub=$resSub->fetch())
	  {
	  	$subunit=$barSub->kodeblok;
	  }
	  $optSubUnit = makeOption($dbname,'log_transaksiht','notransaksi,subunit',"notransaksi='".$bar->notransaksi."'");
	  $sbUni = $optSubUnit[$bar->notransaksi];
	//====================ambil sub unit
	
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
		$vtipeorg = "";
		$opttipeorg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi = '".$bar->untukunit."'");
		
		
		//tambahkan tombol edit dan delete
		$add="<td align=center width:25px><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editXBast('".$bar->notransaksi."','".$bar->untukunit."','".$subunit."','".$sbUni."','".tanggalnormal($bar->tanggal)."','".$bar->namapenerima."','".$bar->keterangan."','".$opttipeorg[$bar->untukunit]."','".$bar->norequest."');\"></td>";
		
		$add.="<td align=center width:25px><img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delXBapb('".$bar->notransaksi."');\"></td>";

//	    $add.="<img src=images/application/book_icon.gif class=resicon  title='Post/Close' onclick=\"postingBapb('".$bar->notransaksi."','".$bar->nopo."');\">";
	}  
    else
	{
		$add='<td align=center width:25px></td><td align=center width:25px></td>';
	}			     
	  
	echo"<tr class=rowcontent>
	  <td align=center>".$no."</td>
	  <!--<td>".$bar->kodegudang." - ".$nmgudang[$bar->kodegudang]."</td>
	  <td title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".getDetailTipeMutasi($bar->tipetransaksi)."</td>-->
	  <td>".$bar->notransaksi."</td>
	  <td>".tanggalnormal($bar->tanggal)."</td>
	  <td>".getNamaOrg(substr($bar->kodegudang,0,4))."</td>
	  <td>".$bar->untukunit." - ".$nmgudang[$bar->untukunit]."</td>			  
	  <td>".$bar->keterangan."</td>
	  <td>".$namapembuat."</td>
	  <td>".$namaposting."</td>
	  <td><font style=font-size:10px>".$bar->lastupdate."</font></td>
	     ".$add."
	  <td align=center width:25px>
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewBast('".$bar->notransaksi."',event);\"> 
	  </td>
	  <td align=center width:25px>
	     <img src=images/zoom.png class=resicon  title='".$_SESSION['lang']['preview']."' onclick=\"previewhtml('".$bar->notransaksi."',event);\"> 
	  </td>
	  </tr>";
  }
  echo"<tr><td colspan=13 align=center>
       ".(@($page*$limit)+1)." to ".(@($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariBast(".@($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariBast(".@($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";
}
else
{
	echo " Error: Transaction Period missing";
}
?>