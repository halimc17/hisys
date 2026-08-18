<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$dataorg="'".$_SESSION['empl']['lokasitugas']."'";
if((substr($_SESSION['empl']['lokasitugas'],2,2)=='RO')or(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO')){ // SDHO
    $arrorg[$_SESSION['empl']['lokasitugas']]=$_SESSION['empl']['lokasitugas'];
    if (isset($_SESSION['orgdet'])) {
        foreach($_SESSION['orgdet'] as $key=>$val){
            if($val!=''){   
                $dataorg.= ",'".$_SESSION['orgdet'][$key]."'";       
                $arrorg[$_SESSION['orgdet'][$key]]=$_SESSION['orgdet'][$key];      
            }
        }
    }
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$nama   = checkPostGet('nama', '');
$lokasitugas = checkPostGet("lokasitugas", '');
$jnstransaksi = checkPostGet("jnstransaksi", '');
$blnberlaku = checkPostGet("blnberlaku", '');
$tipekaryawan = checkPostGet("tipekaryawan", '');

//limit/page
$limit = 20;
$page = 0;
//========================
//ambil jumlah baris dalam tahun ini
$notransaksi = "";
if (isset($_POST['tex'])) {
    $notransaksi.=$_POST['tex'];
}


$jab = getPostingJabatan('promosi/mutasi/demosi'); 

$wherexx='';
$allx=1;
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
//     $wherexx='';
//     $allx=1;
// } else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
//     $wherexx='';
//     $allx=1;
// } else {
//     $wherexx='';
//     $allx=1;
// }

if ($nama!='') {
	$wherexx.=" and karyawanid in (select karyawanid from " . $dbname . ".datakaryawan where namakaryawan like '%".$nama."%')";	
}

if ($lokasitugas != '') {
    $wherexx.=" and darikodeorg = '".$lokasitugas."'";
}

if ($jnstransaksi != '') {
    $wherexx.=" and tipesk = '".$jnstransaksi."'";
}

if ($tipekaryawan != '') {
    $wherexx.=" and daritipe IN (".$tipekaryawan.")";
}

if ($blnberlaku != '') {
    $wherexx.=" and mulaiberlaku like '".$blnberlaku."%'";
}

$str = "select count(*) as jlhbrs from " . $dbname . ".sdm_riwayatjabatan where nomorsk like '%" . $notransaksi . "%'
		and darikodeorg in (".getOrgDetail(2).") ".$wherexx."
		order by jlhbrs desc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $jlhbrs = $bar->jlhbrs;
}
//==================

if (isset($_POST['page'])) {
    $page = intval($_POST['page']);
    if ($page < 0)
        $page = 0;
}

$arrtipePersetujuan=array('0'=>'Belum Diajukan','1'=>'Disetujui','2'=>'Ditolak','9'=>'Proses Persetujuan');

$offset = $page * $limit;

$str = "select * from " . $dbname . ".sdm_riwayatjabatan where nomorsk like '%" . $notransaksi . "%'
        and darikodeorg in (".getOrgDetail(2).") ".$wherexx."
		order by updatetime desc limit " . $offset . ",20";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = $page * $limit;
while ($bar = $res->fetch()) {
    $no+=1;

    //===================smbil nama supplier
    $namakaryawan = '';
    $strx = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid=" . $bar->karyawanid;
	
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
    while ($barx = $resx->fetch()) {
        $namakaryawan = $barx->namakaryawan;
    }
    //====================ambil username pembuat
    $namapembuat = '';
    $stry = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid=" . $bar->updateby;
    $resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
	$resy->setFetchMode(PDO::FETCH_OBJ);
	while ($bary = $resy->fetch()) {
        $namapembuat = $bary->namakaryawan;
    }

    if($bar->statuspersetujuan == '1'){
        $sty = "style='color:green'";
    }elseif($bar->statuspersetujuan == '2'){
        $sty = "style='color:red'";
    }else{
        $sty = "";
    }  

    echo"<tr class=rowcontent>
	  <td align=center>" . $no . "</td>
	  <td>" . $bar->nomorsk . "</td>
	  <td>" . $namakaryawan . "</td>
	  <td align=center>" . tanggalnormal($bar->tanggalsk) . "</td>
	  <td align=center>" . tanggalnormal($bar->mulaiberlaku) . "</td>
	  <td align=center>" . $bar->tipesk . "</td>
	  <td align=center ".$sty." style=cursor:pointer;color:blue; title=\"Click untuk melihat detail approval.\" onclick=gethistoriapproval('".$bar->nomorsk."','event')>" . $arrtipePersetujuan[$bar->statuspersetujuan] . "</td>
	  <td align=center>" . $namapembuat . "</td>	
	  <td align=center>" . $bar->updatetime . "</td>";


    // Jika belum posting
    if ($bar->posting == 0) {
        // Jika status belum diajukan atau ditolak
        if ($bar->statuspersetujuan == 0 || $bar->statuspersetujuan == 2) {
            echo"<td align=center>&nbsp;<img src=images/pdf.jpg class=resicon title='" . $_SESSION['lang']['pdf'] . " SK' onclick=\"previewSK('" . $bar->nomorsk . "',event);\"> </td>";
            echo"<td align=center>&nbsp;<img src=images/pdf.jpg class=resicon title='" . $_SESSION['lang']['pdf'] . " Pengajuan' onclick=\"previewSKPengajuan('" . $bar->nomorsk . "',event);\"> </td>";
            echo"<td align=center>&nbsp;<img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"editSK('" . $bar->nomorsk . "','" . $bar->karyawanid . "');\"> </td>";
            echo"<td align=center>&nbsp;<img src=images/application/application_delete.png class=resicon title='delete' onclick=\"delSK('" . $bar->nomorsk . "','" . $bar->karyawanid . "');\"> </td>";
            echo"<td align=center>&nbsp;<img src=images/skyblue/submit.jpg class=zImgBtn height=30 title='Ajukan ???' onclick=\"form_ajukan('" . $bar->nomorsk . "','" . $bar->karyawanid . "','" . $bar->darikodeorg . "','" . $bar->tipesk . "');\"> </td>";

        }
        // Jika sudah disetujui tapi belum posting
        elseif ($bar->statuspersetujuan == 1) {
            echo "<td align=center>&nbsp;<img src=images/hot.png class=resicon title='posting' onclick=\"postSK('" . $bar->nomorsk . "','" . $bar->karyawanid . "');\"> </td>";
            echo "<td align=center>&nbsp;<img src=images/application/application_delete.png class=resicon title='delete' onclick=\"delSK('" . $bar->nomorsk . "','" . $bar->karyawanid . "');\"> </td>";
            echo"<td align=center>&nbsp;<img src=images/pdf.jpg class=resicon title='" . $_SESSION['lang']['pdf'] . " SK' onclick=\"previewSK('" . $bar->nomorsk . "',event);\"> </td>";
            echo"<td align=center>&nbsp;<img src=images/pdf.jpg class=resicon title='" . $_SESSION['lang']['pdf'] . " Pengajuan' onclick=\"previewSKPengajuan('" . $bar->nomorsk . "',event);\"> </td>";

        }
        // Jika proses persetujuan, hanya PDF (sudah ditampilkan di awal)
    } else{
        echo"<td align=center>&nbsp;<img src=images/pdf.jpg class=resicon title='" . $_SESSION['lang']['pdf'] . " SK' onclick=\"previewSK('" . $bar->nomorsk . "',event);\"> </td>";
        echo"<td align=center>&nbsp;<img src=images/pdf.jpg class=resicon title='" . $_SESSION['lang']['pdf'] . " Pengajuan' onclick=\"previewSKPengajuan('" . $bar->nomorsk . "',event);\"> </td>";
        echo"<td align=center>&nbsp; </td>";
        echo"<td align=center>&nbsp; </td>";
        echo"<td align=center>&nbsp; </td>";
    }

    echo "
	  </td></tr>";
}
echo"<tr><td colspan=10 align=center>
       " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "
	   <br>
       <button class=mybutton onclick=cariSK(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
	   <button class=mybutton onclick=cariSK(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
	   </td>
	   </tr>";
?>