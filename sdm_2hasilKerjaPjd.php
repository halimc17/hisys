<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language="javascript" src='js/zTools.js'></script>
<script language=javascript1.2 src=js/sdm_pertanggungjawabanPJD.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pertanggungjawabandinas']).'</span><br>');
echo $frm[1].="<fieldset style='float:left;'><legend>".$_SESSION['lang']['form']."</legend>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input placeholder='".$_SESSION['lang']['all']."' type=text id=txtbabp size=20 class=myinputtext onkeypress=\"key=getKey(event);if(key==13){cariPJDUraian()};;\" maxlength=9> &nbsp;
	  ".$_SESSION['lang']['nama']."
	  <input placeholder='".$_SESSION['lang']['all']."' type=text id=txtnama size=25 class=myinputtext onkeypress=\"key=getKey(event);if(key==13){cariPJDUraian()};;\" maxlength=9>
	  
	  <button class=mybutton onclick=cariPJDUraian(0)>".$_SESSION['lang']['find']."</button>
	  <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
	  </fieldset>";
CLOSE_BOX();
OPEN_BOX();	  
$frm[2].="<fieldset style=float:left><legend>List Data</legend><table class=sortable cellspacing=1 border=0>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>".$_SESSION['lang']['notransaksi']."</td>
	  <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
	  <td align=center>".$_SESSION['lang']['tanggalsurat']."</td>
	  <td align=center>".$_SESSION['lang']['tujuan']."</td>
	  <td align=center>".$_SESSION['lang']['uangmuka']."</td>
	  <td align=center>".$_SESSION['lang']['digunakan']."</td>	  
	  <td align=center>".$_SESSION['lang']['statuspertanggungjawaban']."</td>	  
	  <td align=center>".$_SESSION['lang']['preview']."</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>";
$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini
$notransaksi="";
  if(isset($_POST['tex']))
  {
  	$notransaksi.=" and notransaksi like '%".$_POST['tex']."%' ";
  } 
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where
		persetujuan=".$_SESSION['standard']['userid']."
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
        where
        persetujuan=".$_SESSION['standard']['userid']."
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
	  $add='';
	 /*
	  if($bar->statuspertanggungjawaban==0)
	  {
	  	$add.="&nbsp <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPPJD('".$bar->notransaksi."');\">
         ";
	  }
	 */ 
   if($bar->statuspertanggungjawaban==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspertanggungjawaban==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else 
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	  
   
   $str1="select sum(jumlah) as jumlah from ".$dbname.".sdm_pjdinasdt
         where notransaksi='".$bar->notransaksi."'";
   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
   $res1->setFetchMode(PDO::FETCH_OBJ);
   $usage=0;
   while($bar1=$res1->fetch())
   {
   	 $usage=$bar1->jumlah;
   }	 	 
	  
	$frm[2].="<tr class=rowcontent>
	  <td align=center>".$no."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggalbuat)."</td>
	  <td>".$bar->tujuan1."</td>
	  <td align=right>".number_format($bar->dibayar,2,'.',',')."</td>
	  <td align=right>".number_format($usage,2,'.',',')."</td>
	  <td>".$stpersetujuan."</td>
	  <td align=center>
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']." (Cost)' onclick=\"previewPJD('".$bar->notransaksi."',event);\"> 
		 <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']." (Task Result Description)' onclick=\"previewPJDUraian('".$bar->notransaksi."',event);\"> 
       ".$add."
	  </td>
	  </tr>";
  }
  $frm[2].="<tr><td colspan=9 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariPJDUraian(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariPJDUraian(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";	   
$frm[2].="</tbody>
	   <tfoot>
	   </tfoot>
	   </table></fieldset>
	 "; 
	 
 
//==================================================	 	 
echo $frm[2];
	 
//drawTab('FRM',$hfrm,$frm,100,900);	  
CLOSE_BOX();
echo close_body();
?>