<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_persetujuanPJD.js'></script>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['persetujuanpjdinas']).'</span>');
echo"<fieldset style=width:900px>
	  <legend  >".$_SESSION['lang']['list']."</legend>
	  <fieldset><legend>".$_SESSION['lang']['find']."</legend>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=9>
	  <button class=mybutton onclick=cariPJD(0)>".$_SESSION['lang']['find']."</button>
	  </fieldset>
	  <fieldset><legend>".$_SESSION['lang']['hasil']."</legend>
	  <table class=sortable cellspacing=1 border=0 style=width:900px>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>".$_SESSION['lang']['notransaksi']."</td>
	  <td align=center>".$_SESSION['lang']['karyawan']."</td>
	  <td align=center>".$_SESSION['lang']['tanggalsurat']."</td>
	  <td align=center>".$_SESSION['lang']['tujuan']."</td>
	  <td align=center>".$_SESSION['lang']['persetujuan']."</td>
	  <td align=center>".$_SESSION['lang']['hrd']."</td>
	  <td align=center>".$_SESSION['lang']['action']."</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>";
$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini
  if(isset($_POST['tex']))
  {
  	$notransaksi.=$_POST['tex'];
  }
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where
		persetujuan=".$_SESSION['standard']['userid']."
		or hrd=".$_SESSION['standard']['userid']."
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
        where
        persetujuan=".$_SESSION['standard']['userid']."
		or hrd=".$_SESSION['standard']['userid']."
		order by tanggalbuat desc  limit ".$offset.",20";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  $no=$page*$limit;
  while($bar=$res->fetch())
  {
  	$no+=1;

	  $per='';
	  if($bar->persetujuan==$_SESSION['standard']['userid'])
	  {
	  	$per='persetujuan';
	  }
	  else
	  {
	  	$per='hrd';
	  }
	  $namakaryawan='';
	  $strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->karyawanid;
	  $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	  $resx->setFetchMode(PDO::FETCH_OBJ);
	  while($barx=$resx->fetch())
	  {
	  	$namakaryawan=$barx->namakaryawan;
	  }
	  $add='';
	  
	  if($bar->statuspersetujuan==0 && $per=='persetujuan')
	  {
	  	$add.="&nbsp <img src=images/onebit_34.png class=resicon  title='".$_SESSION['lang']['disetujui']."' onclick=\"approvePJD('".$bar->notransaksi."','".$bar->karyawanid."',1,'".$per."');\">
		       &nbsp <img src=images/onebit_33.png class=resicon  title='".$_SESSION['lang']['ditolak']."' onclick=\"approvePJD('".$bar->notransaksi."','".$bar->karyawanid."',2,'".$per."');\">
         ";
	  }
	  
	  if($bar->statushrd==0 && $per=='hrd' && $bar->statuspersetujuan!=0)
	  {
	  	$add.="&nbsp <img src=images/onebit_34.png class=resicon  title='".$_SESSION['lang']['disetujui']."' onclick=\"approvePJD('".$bar->notransaksi."','".$bar->karyawanid."',1,'".$per."');\">
		       &nbsp <img src=images/onebit_33.png class=resicon  title='".$_SESSION['lang']['ditolak']."' onclick=\"approvePJD('".$bar->notransaksi."','".$bar->karyawanid."',2,'".$per."');\">
         ";
	  }	
  if($bar->statuspersetujuan==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspersetujuan==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else 
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	  

   if($bar->statushrd==2)
     $sthrd=$_SESSION['lang']['ditolak'];
  else if($bar->statushrd==1)
     $sthrd=$_SESSION['lang']['disetujui'];
  else
     $sthrd=$_SESSION['lang']['wait_approve'];
	 
	echo"<tr class=rowcontent>
	  <td align=center>".$no."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggalbuat)."</td>
	  <td>".$bar->tujuan2."</td>
	  <td>".$stpersetujuan."</td>
	  <td>".$sthrd."</td>	
	  <td align=center>
	    <img src=images/zoom.png class=resicon  title='".$_SESSION['lang']['view']."' onclick=\"previewPJD('".$bar->notransaksi."',event);\">
       ".$add."
	  &nbsp &nbsp
	   <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJDPDF('".$bar->notransaksi."',event);\"> 
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
echo"</tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>";
//drawTab('FRM',$hfrm,$frm,100,900);
CLOSE_BOX();
echo close_body('');
?>