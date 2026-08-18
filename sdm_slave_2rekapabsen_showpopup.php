<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/sdm_2rekapabsen.js"></script>
<?
 $theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
  $gen='generic.css';
}else if($theme=='red'){
  $gen='genericRed.css';  
}else{
  $gen='genericGray.css';  
} 
echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>
";  
$karyawanid=checkPostGet("karyawanid","");
$namakaryawan=checkPostGet("namakaryawan","");
$tanggal=checkPostGet("tanggal","");
$notransaksi=checkPostGet("notransaksi","");

//ilangin __ paling belakang
$namakaryawan=substr($namakaryawan,0,-2);
$notransaksi=substr($notransaksi,0,-2);
//transform __ into spasi
$qwe=explode('__',$notransaksi);
$qwe2=explode('__',$namakaryawan);

// $tipenya = substr($qwe[0],0,3);
$kodeorgnya = substr($qwe[0],8,11);

// exit('warning'.$tipenya);
foreach($qwe2 as $kyu2){
  $namakar=$kyu2.' ';
}
//=================================================
//echo"<fieldset><legend>Print Excel</legend>
//     <img onclick=\"detailExcel(event,'pabrik_slave_2pengolahandetail.php?type=excel&tanggal=".$tanggal."&kodeorg=".$kodeorg."&periode_tahun=".$periode_tahun."&periode_bulan=".$periode_bulan."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
//     </fieldset>"; 
if(checkPostGet("type","")!='excel')$stream="<table class=sortable border=0 cellspacing=1 style=width:100%>"; //else
//$stream="<table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td>Karyawan</td>
          <td>No. Transaksi</td>
          <td align=center>Tanggal</td>";
//		  if($_GET['type']!='excel')$stream.="<td>Browse</td>";
        $stream.="</tr>
      </thead>
      <tbody>";
if($notransaksi==''){
   $stream.="<tr class=rowcontent><td colspan=3>No record</td></tr>";
}else{	  
foreach($qwe as $kyu){
    $stream.="<tr class=rowcontent>";
    $stream.="<td align=left>".$namakar."</td>";
    $stream.="<td align=left>".$kyu."</td>";
    $stream.="<td align=center>".$tanggal."</td>";
    $stream.="</tr>";
	}    
}    

   $stream.="</tbody></table><br/>";

  //  Kolom baru
  $stream.="<b>Sumber Transaksi Absensi</b><table class=sortable border=0 cellspacing=1 style=width:100%>";
  $stream.="
      <thead>
        <tr class=rowcontent>
          <td>Nama Karyawan</td>
          <td>Filename</td>
          <td align=center>Action</td>";
//		  if($_GET['type']!='excel')$stream.="<td>Browse</td>";
        $stream.="</tr>
      </thead>
      <tbody>";
      
  // Query Tampilkan Karyawan
  $sqlKaryawan = "SELECT * from ".$dbname.".sdm_absensidt WHERE kodeorg='".$kodeorgnya."' and tanggal='".tanggalsystem($tanggal)."' and karyawanid='".$karyawanid."'";
  // exit('warning');
  $res = $owlPDO->query($sqlKaryawan) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  $cekSumberData = fetchData($sqlKaryawan);

  if(count($cekSumberData) <= 0) {
    $stream.="<tr class=rowcontent><td colspan=3>Sumber Absensi Bukan Dari Transaksi Absensi</td></tr>";
  }

  // exit("warning".print_r($res));
  $noKar=0;
  while($resKaryawanDt = $res->fetch()) {
    $nmkaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$resKaryawanDt->karyawanid."'");
    $nmakun = makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$resKaryawanDt->noakun."'");
    $pecahtanggal = explode("-",$resKaryawanDt->tanggal);
    $tanggalnya = $pecahtanggal[0].$pecahtanggal[1].$pecahtanggal[2];
    $notransaksix = $tanggalnya.$resKaryawanDt->karyawanid;
    // echo "<pre>";
    // print_r($filenya[$notransaksi]);
    // echo "</pre>";

    $sFile = selectQuery($dbname, "listfileupload", "*", "notransaksi='".$notransaksix."' and kriteriaefil='ABSEN' and status='1'");
    $resFile = fetchData($sFile);
    
if(count($resFile) <= 0){
   $stream.="<tr class=rowcontent><td colspan=3>Belum Ada Data</td></tr>";
}else{	  
  foreach($resFile as $key => $val) {
    $pathDownload = "fileupload/dtkaryawanabsen/";
    $noKar+=1;
    $stream .= "<tr class=rowcontent>";
      // $stream .= "<td align=right>".$noKar."</td>";
      $stream .= "<td align=left>".$nmkaryawan[$resKaryawanDt->karyawanid]."</td>";
      // $stream .= "<td align=center>".$resKaryawanDt->absensi."</td>";
      // $tab .= "<td align=left>".$nmakun[$resKaryawanDt->noakun]."</td>";
      // $tab .= "<td align=left>".$resKaryawanDt->alokasi."</td>";
      // $tab .= "<td align=right>".$resKaryawanDt->hk."</td>";
      // $tab .= "<td align=right>".$resKaryawanDt->premi."</td>";
      // $tab .= "<td align=right>".$resKaryawanDt->tunjangan."</td>";
      // $tab .= "<td align=right>".$resKaryawanDt->penaltykehadiran."</td>";
      // $tab .= "<td align=left>".$resKaryawanDt->penjelasan."</td>";
      
      $icon=seticonfile($val['formaticon']);
      $stream.="<td style='text-align:center;display:flex;align-item:center;'>
          <a href='".$pathDownload.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a> <span>".$val['namafile']."</span>
        </td>";
      // $stream .= "<td align=left>".$val['namafile']."</td>";
      $stream .= "<td align=center>
                    <a href='".$pathDownload.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn width=15px  title='Download'></a>
                    <!--<a href='".$pathDownload.$val['namafile']."' preview><img src=images/zoom.png class=zImgBtn width=15px  title='Download'></a>-->
                  </td>";
    
    $stream .= "</tr>";
  }
}    
  }
   $stream.="</tbody></table>";
//   if($_GET['type']=='excel')
//   {
//$nop_="Detail_pengolahan_".$kodeorg."_".$tanggal;
//        if(strlen($stream)>0)
//        {
//        if ($handle = opendir('tempExcel')) {
//            while (false !== ($file = readdir($handle))) {
//                if ($file != "." && $file != ".." && $file != "index.html") {
//                    @unlink('tempExcel/'.$file);
//                }
//            }	
//           closedir($handle);
//        }
//         $handle=fopen("tempExcel/".$nop_.".xls",'w');
//         if(!fwrite($handle,$stream))
//         {
//          echo "<script language=javascript1.2>
//                parent.window.alert('Can't convert to excel format');
//                </script>";
//           exit;
//         }
//         else
//         {
//          echo "<script language=javascript1.2>
//                window.location='tempExcel/".$nop_.".xls';
//                </script>";
//         }
//        fclose($handle);
//        }       
//   }
//   else
   {
       echo $stream;

       // Query Tampilkan Karyawan
			// $sqlKaryawan = "SELECT * from ".$dbname.".sdm_absensidt WHERE kodeorg='".$param['kodeorgnya']."' and tanggal='".$param['tanggal']."'";
			// $res = $owlPDO->query($sqlKaryawan) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_OBJ);
			// // exit("warning".print_r($res));
			// $noKar=0;
			// while($resKaryawanDt = $res->fetch()) {
			// 	$nmkaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$resKaryawanDt->karyawanid."'");
			// 	$nmakun = makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$resKaryawanDt->noakun."'");
			// 	$pecahtanggal = explode("-",$resKaryawanDt->tanggal);
			// 	$tanggalnya = $pecahtanggal[0].$pecahtanggal[1].$pecahtanggal[2];
			// 	$notransaksi = $tanggalnya.$resKaryawanDt->karyawanid;
			// 	// echo "<pre>";
			// 	// print_r($filenya[$notransaksi]);
			// 	// echo "</pre>";

			// 	$sFile = selectQuery($dbname, "listfileupload", "*", "notransaksi='".$notransaksi."' and kriteriaefil='ABSEN' and status='1'");
			// 	$resFile = fetchData($sFile);

			// 	foreach($resFile as $key => $val) {
			// 		$pathDownload = "fileupload/dtkaryawanabsen/";
			// 		$noKar+=1;
			// 		$tab .= "<tr class=rowcontent>";
			// 			$tab .= "<td align=right>".$noKar."</td>";
			// 			$tab .= "<td align=left>".$nmkaryawan[$resKaryawanDt->karyawanid]."</td>";
			// 			$tab .= "<td align=center>".$resKaryawanDt->absensi."</td>";
			// 			$tab .= "<td align=left>".$nmakun[$resKaryawanDt->noakun]."</td>";
			// 			$tab .= "<td align=left>".$resKaryawanDt->alokasi."</td>";
			// 			$tab .= "<td align=right>".$resKaryawanDt->hk."</td>";
			// 			$tab .= "<td align=right>".$resKaryawanDt->premi."</td>";
			// 			$tab .= "<td align=right>".$resKaryawanDt->tunjangan."</td>";
			// 			$tab .= "<td align=right>".$resKaryawanDt->penaltykehadiran."</td>";
			// 			$tab .= "<td align=left>".$resKaryawanDt->penjelasan."</td>";
						
			// 			$icon=seticonfile($val['formaticon']);
			// 			$tab.="<td style='text-align:center'>
			// 					<a href='".$pathDownload.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
			// 				</td>";
			// 			$tab .= "<td align=left>".$val['namafile']."</td>";
			// 			$tab .= "<td align=center><a href='".$pathDownload.$val['namafile']."'><img src=images/uploader/dwnld8.png class=zImgBtn  title='Download'></a></td>";
					
			// 		$tab .= "</tr>";
			// 	}
			// }
			
			// 	$tab .= "</table>";
			// $tab .= "</fieldset>";
	
			// echo $tab;
   }    
       
?>