<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/sdm_verPertanggungjawabanPJD.js'></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['verifikasi']).'</span>');

$frm[1] = "<fieldset>
     <legend>" . $_SESSION['lang']['form'] . "</legend>
	 <fieldset>
     <legend>" . $_SESSION['lang']['find'] . "</legend>
	 <table>
		<tr>
		   <td>" . $_SESSION['lang']['notransaksi'] . "</td><td>:</td>
		   <td><input type=text class=myinputtext id=notransaksi disabled value=''>
		   </td>		   
		</tr>	
	 </table>
	 </fieldset>
	 <fieldset>
	    <legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
		<table class=sortable cellspacing=1>
		<thead>
		 <tr>
		    <td align=center>No.</td>
			<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
			<td align=center>" . $_SESSION['lang']['jenisbiaya'] . "</td>
			<td align=center>" . $_SESSION['lang']['detail'] . "</td>
			<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
			<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
			<td align=center>" . $_SESSION['lang']['disetujui'] . "</td>
			<td align=center>" . $_SESSION['lang']['sumber'] . "</td>
			
		</tr>	
		 </thead>	
		 <tbody id=innercontainer>
		 </tbody>
		 <tfoot>
		 </tfoot>
		</table>
		<button class=mybutton onclick=selesai()>" . $_SESSION['lang']['done'] . "</button>
		<button class=mybutton onclick=batalkan()>" . $_SESSION['lang']['cancel'] . "</button>
	 </fieldset>
	 </fieldset>
	 ";
//=====================================
$frm[0] = "
	   
	  <fieldset style=float:left><legend>" . $_SESSION['lang']['find'] . "</legend>
	  " . $_SESSION['lang']['cari_transaksi'] . "
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" >
	  <button class=mybutton onclick=cariPJD(0)>" . $_SESSION['lang']['find'] . "</button>
	  </fieldset><br><br><br><br>
	  <fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
	  <table class=sortable cellspacing=1 border=0 width=100%>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
	  <td align=center>" . $_SESSION['lang']['karyawan'] . "</td>
	  <td align=center>" . $_SESSION['lang']['tanggalsurat'] . "</td>
	  <td align=center>" . $_SESSION['lang']['tujuan'] . "</td>
	  <td align=center>" . $_SESSION['lang']['uangmuka'] . "</td>
	  <td align=center>" . $_SESSION['lang']['uangmuka'] . " Realisasi</td>
	  <td align=center>Disetujui HRD</td>	  
	  <td align=center>" . $_SESSION['lang']['approval_status'] . "</td>	  
	  <td align=center>" . $_SESSION['lang']['action'] . "</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>";
$limit = 20;
$page = 0;



//========================
//ambil jumlah baris dalam tahun ini
$notransaksi = "";
if (isset($_POST['tex'])) {
    $notransaksi.=" and notransaksi like '%" . $_POST['tex'] . "%' ";
}
$str = "select count(*) as jlhbrs from " . $dbname . ".sdm_pjdinasht 
        where
		kodeorg='" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "'
		and statuspersetujuan=1 and namatamu=''
		" . $notransaksi . "
		order by jlhbrs desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $jlhbrs = $bar->jlhbrs;
}
//==================

if (isset($_POST['page'])) {
    $page = $_POST['page'];
    if ($page < 0)
        $page = 0;
}

$offset = $page * $limit;


$str = "select * from " . $dbname . ".sdm_pjdinasht 
        where
        kodeorg ='" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "'
		and statuspersetujuan=1 and namatamu=''
		" . $notransaksi . "
		order by tanggalbuat desc  limit " . $offset . ",20";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = $page * $limit;
while ($bar = $res->fetch()) {
	//Get advance payment from cash bank
    $uangmuka=0;
    $strupd = "select sum(a.jumlah) as uangmuka , b.nopo from ".$dbname.".keu_kasbankdt a  
    left join ".$dbname.".keu_tagihanht b on a.keterangan1= b.noinvoice
    where b.nopo ='".$bar->notransaksi."' and b.tipeinvoice = 'upd' ";
    $resupd=$owlPDO->query($strupd) or die(print " Gagal: ".PDOException::getMessage());
    $resupd->setFetchMode(PDO::FETCH_OBJ);
    while ($barupd = $resupd->fetch()) {
        $uangmuka = $barupd->uangmuka;
    }

    $no+=1;

    $namakaryawan = '';
    $strx = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid=" . $bar->karyawanid;
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
    while ($barx = $resx->fetch()) {
        $namakaryawan = $barx->namakaryawan;
    }
    $add = '';
    if ($bar->statuspertanggungjawaban == 0) {
        $add.="&nbsp <img src=images/application/application_edit.png class=resicon  title='FollowUp' onclick=\"editPPJD('" . $bar->notransaksi . "');\">
         ";
    }
    if ($bar->statuspertanggungjawaban == 2)
        $stpersetujuan = $_SESSION['lang']['ditolak'];
    else if ($bar->statuspertanggungjawaban == 1)
        $stpersetujuan = $_SESSION['lang']['disetujui'];
    else
        $stpersetujuan = $_SESSION['lang']['wait_approve'];

    $str1 = "select sum(jumlahhrd) as jumlah from " . $dbname . ".sdm_pjdinasdt
         where notransaksi='" . $bar->notransaksi . "' and sumber=1";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
    
    $usage = 0;
    while ($bar1 = $res1->fetch()) {
        $usage = $bar1->jumlah;
    }

    $frm[0].="<tr class=rowcontent>
	  <td align=center>" . $no . "</td>
	  <td>" . $bar->notransaksi . "</td>
	  <td>" . $namakaryawan . "</td>
	  <td>" . tanggalnormal($bar->tanggalbuat) . "</td>
	  <td>" . $bar->tujuan1 . "</td>
	  <td align=right>" . number_format($bar->uangmuka, 2, '.', ',') . "</td>
	  <td align=right>" . number_format($uangmuka, 2, '.', ',') . "</td>
	  <td align=right>" . number_format($usage, 2, '.', ',') . "</td>
	  <td>" . $stpersetujuan . "</td>
	  <td align=center>
	     <img src=images/pdf.jpg class=resicon  title='" . $_SESSION['lang']['pdf'] . "' onclick=\"previewPJD('" . $bar->notransaksi . "',event);\"> 
       " . $add . "
	  </td>
	  </tr>";
}
$frm[0].="<tr><td colspan=9 align=center>
       " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "
	   <br>
       <button class=mybutton onclick=cariPJD(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
	   <button class=mybutton onclick=cariPJD(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
	   </td>
	   </tr>";
$frm[0].="</tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>";
//==================================================	 	 
$hfrm[1] = $_SESSION['lang']['form'];
$hfrm[0] = $_SESSION['lang']['list'];

drawTab('FRM', $hfrm, $frm, 100, 900);
CLOSE_BOX();
echo close_body();
?>