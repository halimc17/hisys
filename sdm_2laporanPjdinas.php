<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language="javascript" src='js/zTools.js'></script>
<script language=javascript src='js/sdm_2laporanPjdinas.js'></script>
<?
$frm[0]=$frm[1]='';
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2laporanPjdinas').'</span><br>');
$frm[0].="<fieldset style=float:left>
	   <legend>".$_SESSION['lang']['form']."</legend>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input placeholder='".$_SESSION['lang']['all']."' type=text id=txtbabp size=20 class=myinputtext onkeypress=\"key=getKey(event);if(key==13){cariPJD()};\"> &nbsp; 
	  ".$_SESSION['lang']['nama']."
	  <input placeholder='".$_SESSION['lang']['all']."' type=text id=txtnama size=25 class=myinputtext onkeypress=\"key=getKey(event);if(key==13){cariPJD()};\">
	  
	  <button class=mybutton onclick=cariPJD(0)>".$_SESSION['lang']['find']."</button>
	  <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
	  </fieldset>";
echo $frm[0];
CLOSE_BOX();
OPEN_BOX();	  



$countApprove = getCountApproval('PJDINAS','');
$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);

$frm[1].="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <table class=sortable cellspacing=1 border=0>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>".$_SESSION['lang']['notransaksi']."</td>
	  <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
	  <td align=center>".$_SESSION['lang']['tanggalsurat']."</td>
	  <td align=center>".$_SESSION['lang']['tujuan']."</td>";
		for($i=1;$i<=$countApprove;$i++){
			$frm[1].= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
$frm[1].="<td align=center>".$_SESSION['lang']['preview']."</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>";
$limit=20;
$page=0;
$notransaksi=0;
//========================
//ambil jumlah baris dalam tahun ini
if(isset($_POST['tex'])){
	$notransaksi.=$_POST['tex'];
}
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where notransaksi like '%".$notransaksi."%'
		order by jlhbrs desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);		
while($bar=$res->fetch()){
	$jlhbrs=$bar->jlhbrs;
}		
//==================
		 
if(isset($_POST['page'])){
	$page=$_POST['page'];
	if($page<0)
	  $page=0;
}
	 
  
$offset=$page*$limit;





$str="select * from ".$dbname.".sdm_pjdinasht 
	where notransaksi like '%".$notransaksi."%'
	order by tanggalbuat desc,notransaksi desc  limit ".$offset.",20";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=$page*$limit;
echo $frm[1];
$frm[1]="";


while($bar=$res->fetch()){
	$no+=1;

	$namakaryawan='';
	$strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->karyawanid;
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
	while($barx=$resx->fetch()){
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
	$frm[1].="<tr class=rowcontent>
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
				$frm[1].= "<td>".$arrApp['nama']."
					<br />".$arrHsl[$arrApp['status']]."
					<br>".$tngl."
				</td>";
			}else{
				$frm[1].= "<td>&nbsp;</td>";
			}
		}
	  
	  $frm[1].="<td align=center>
		 <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."',event);\"> 
	  </td>
	  </tr>";
}
$frm[1].="<tr><td colspan=10 align=center>
   ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
   <br>
   <button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
   <button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
   </td>
   </tr>";	   
$frm[1].="</tbody>
   <tfoot>
   </tfoot>
   </table>
 </fieldset>";



echo $frm[1];

// $hfrm[0]=$_SESSION['lang']['list'];
 	 
// drawTab('FRM',$hfrm,$frm,100,900);
CLOSE_BOX();
echo close_body('');
?>