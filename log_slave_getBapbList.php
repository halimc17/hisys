<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');



if(isTransactionPeriod())//check if transaction period is normal
{
	//limit/page
	$limit=10;
	$page=0;

	//========================
	$gudang=$_POST['gudang'];
	$notransaksi = checkPostGet('tex','');
	$srcposo = checkPostGet('srcposo','');
	$srcnamasup = checkPostGet('srcnamasup','');
	
	//ambil jumlah baris dalam tahun ini
	$add='';//default serach id nothing
	
	if($notransaksi!=''){
		$add.=" and notransaksi like '%".$notransaksi."%'";
	}
	
	if($srcposo!=''){
		$add.=" and nopo like '%".$srcposo."%'";
	}
	
	if($srcnamasup!=''){
		$add.=" and idsupplier in (select supplierid from ".$dbname.".log_5supplier where namasupplier like '%".$srcnamasup."%')";
	}

$namagudang = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$str="select count(*) as jlhbrs from ".$dbname.".log_transaksiht where kodegudang='".$gudang."'
        and tipetransaksi=1
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
	 	$page=intval($_POST['page']);
	    if($page<0)
		  $page=0;
	 }
	 
  
  $offset=$page*$limit;
  

  $str="select * from ".$dbname.".log_transaksiht where kodegudang='".$gudang."'
        and tipetransaksi=1
		".$add."
		order by tanggal desc, notransaksi desc limit ".$offset.",".$limit."";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  $no=$page*$limit;
  while($bar=$res->fetch())
  {
  	$no+=1;
	//===================smbil nama supplier
	  $namasupplier='';
	  $strx="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$bar->idsupplier."'";
	  $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	  $resx->setFetchMode(PDO::FETCH_OBJ);
	  while($barx=$resx->fetch())
	  {
	  	$namasupplier=$barx->namasupplier;
	  }
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
	  
	  $sttpersetujuan1 = $bar->hasilpersetujuan1;
	  $sttpersetujuan2 = $bar->hasilpersetujuan2;
	  
	  if($sttpersetujuan1==0)
	  {
		  $optPst = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->persetujuan1."'");
		  $rowsttp1 = $optPst[$bar->persetujuan1]."<br>(Waiting)";
	  }
	  else
	  {
		  $optPst = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->persetujuan1."'");
		  $rowsttp1 = $optPst[$bar->persetujuan1];
	  }
	  
	  if($sttpersetujuan2==0)
	  {
		  $optPst = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->persetujuan2."'");
		  $rowsttp2 = $optPst[$bar->persetujuan2]."<br>(Waiting)";
	  }
	  else
	  {
		  $optPst = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->persetujuan2."'");
		  $rowsttp2 = $optPst[$bar->persetujuan2];
	  }
	  
	 if($namaposting=='Not Posted' && $bar->post==1)
	  {
	  	$namaposting=" Posted By ???";
	  }
	if($bar->post<1 && ($bar->hasilpersetujuan1==0 or $bar->hasilpersetujuan1==2))
	{

		//tambahkan tombol edit dan delete
		$add="<td align=center style='min-width:20px'><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editBapb('".$bar->notransaksi."','".$bar->nopo."','".tanggalnormal($bar->tanggal)."','".$bar->nosj."','".$bar->nofaktur."','".$bar->idsupplier."');\"></td>";
		$add.="<td align=center style='min-width:20px'><img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delBapb('".$bar->notransaksi."');\"></td>";

	    // $add.="<img src=images/plus.png class=resicon title='Upload' onclick=\"detaildt('" . $bar->notransaksi . "');\">";
	    // <td style='vertical-align:top;' align=center>\"></td>
	}  
    else
	{
		$add="<td align=center style='min-width:20px'></td>";
		$add.="<td align=center style='min-width:20px'></td>";
	}	

	$bgcolor = "";
	if($bar->hasilpersetujuan1==2)
	{
		$bgcolor = "style='background-color:orange'";
	}
	  
	echo"<tr class=rowcontent ".$bgcolor.">
	  <td align=center>".$no."</td>
	  <td>".$bar->kodegudang." - ".$namagudang[$bar->kodegudang]."</td>
	  <td title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".getDetailTipeMutasi($bar->tipetransaksi)."</td>
	  <td>".$bar->notransaksi."</td>
	  <td style='min-width:75px;text-align:center'>".tanggalnormal($bar->tanggal)."</td>
	  <td>".$bar->kodept."</td>
	  <td>".$bar->nopo."</td>	
	  <td>".$namasupplier."</td> 
	  <td align=center>".$namapembuat."</td>";
		
	$subgdg = substr($bar->kodegudang,0,4);
	$countApp = getCountApproval('GR',$subgdg);
	
	for($i=1;$i<=$countApp;$i++)
	{
		$arrDetail = detailApprove($i,$bar->notransaksi,'GR');
		if($arrDetail['status']=='0')
		{
			$statusApp = "<br>(Waiting)";
		}
		else if($arrDetail['status']=='2')
		{
			$statusApp = "<br>(Ditolak)";
		}
		else if($arrDetail['status']=='1')
		{
			$statusApp = "<br>(Disetujui)";
		}
		else
		{
			$statusApp = "";
		}
		echo"<td align=center>".$arrDetail['nama']."".$statusApp."</td>";
	}
	  
	  
   echo"<td align=center>".$namaposting."</td>
	     ".$add."
	    <td align=center style='min-width:20px'><img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewBapb('".$bar->notransaksi."',event);\"></td>
		
	    <td align=center style='min-width:20px'><img src=images/download.png class=resicon title='Upload' onclick=\"detaildt('" . $bar->notransaksi . "');\">  </td>
	  </tr>";
  }
  echo"<tr><td colspan=17 align=center>
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