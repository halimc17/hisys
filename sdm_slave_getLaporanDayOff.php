<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
// init dompdf 
require('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$kodeorg=checkPostGet('kodeorg','');
$periode=checkPostGet('periode','');
$karyawan=checkPostGet('karyawan','');
$notransaksi=checkPostGet('notransaksi','');
$method=checkPostGet('method','');
$arrstat=array('0'=>'Tahap Persetujuan','1'=>'Disetujui','2'=>'Ditolak');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Get Jenis Approval
$qJnsApproval = selectQuery($dbname, 'approval', '*', "notransaksi = '".$notransaksi."'");
$resJnsApproval = fetchData($qJnsApproval);

$jnsapp = $resJnsApproval[0]['jenispersetujuan'];

switch($method){
	case 'preview':
		$whr='';
		if($kodeorg!=''){
			$whr=" and a.notransaksi like '%".$kodeorg."%'";
		}
		$hariini = date("Y-m-d");
		$str1="select a.*,b.namakaryawan,b.tanggalmasuk, b.nik
	       from ".$dbname.".sdm_dayoff_dt_vw a
		   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	       where 1=1 ".$whr." 
		   and a.tanggaldayoff like '%".$periode."%' 
		   and a.karyawanid like '%".$karyawan."%'
		   and b.tipekaryawan in(0,1,2,3,7,8,9,12)
		   and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') order by b.namakaryawan, a.tanggalberlakusampai desc";
		//echo $str1;
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res1);
		
		if($numrows <= 0){
			echo $_SESSION['lang']['datanotfound'];
		}else{
			

			echo"<table class=sortable cellspacing=1 border=0 cellpadding=5>
				 <thead>
				 <tr class=rowheader>
					<th>No</th>
					<th align=center width=50px>".$_SESSION['lang']['kodeorganisasi']."</th>		 
					<th align=center>".$_SESSION['lang']['nik2']."</th>
					<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
					<th align=center>".$_SESSION['lang']['tanggalmasuk']."</th>			
					<th align=center>".$_SESSION['lang']['periode']."</th>			
					<th align=center>Tanggal Day Off</th>
					<th align=center>Berlaku Sampai</th>
					<th align=center width=50px>".$_SESSION['lang']['hakcuti']."</th>
					<th align=center width=50px>".$_SESSION['lang']['diambil']."</th>
					<th align=center width=50px>Akan Diambil/Tahap Persetujuan</th>
					<th align=center width=50px>".$_SESSION['lang']['sisa']."</th>
					<th align=center width=50px>Notransaksi Cuti</th>
					<th align=center>Status</th>
					</tr>
				 </thead>
				 <tbody id=container>"; 
			$no=0;	 
			while($bar1=$res1->fetch())
			{

				$sCek = "select * from " . $dbname . ".sdm_ijin where notransaksi='".$bar1->notransaksicuti."' ";
				$resCek=fetchData($sCek);
				$jmlCek=count($resCek);

				$sCek2 = "select * from " . $dbname . ".sdm_ijinnonstaff where notransaksi='".$bar1->notransaksicuti."' ";
				$resCek2=fetchData($sCek2);

				$no+=1;
				$arrnotrans=explode('/', $bar1->notransaksi);

				echo"<tr class=rowcontent id=baris".$no.">
						   <td align=center>".$no."</td>
						   <td align=center>".$arrnotrans[3]."</td>
						   <td align=center>".$bar1->nik."</td>
						   <td>".$bar1->namakaryawan."</td>
						   <td align=center>".tanggalnormal($bar1->tanggalmasuk)."</td>
						   <td align=center>".$periode."</td>				   
						   <td align=center onclick=\"viewpdf('".$bar1->notransaksi."')\" style='color:blue;cursor:pointer' title='Klik untuk lihat pdf' >".tanggalnormal($bar1->tanggaldayoff)."</td>
						   <td align=center>".tanggalnormal($bar1->tanggalberlakusampai)."</td>
						   <td align=center>".$bar1->jumlahharidayoff."</td>
						   <td align=center>".$bar1->diambil."</td>
						   <td align=center>".$bar1->akandiambil."</td>
						   <td align=center>".($bar1->jumlahharidayoff-$bar1->diambil-$bar1->akandiambil)."</td>";
						   if($jmlCek>0)
						   {
						   echo "<td align=center onclick=\"previewPdfstaff('" . tanggalnormal($resCek[0]['tanggal']) . "','" . $bar1->karyawanid . "',event)\"  style='color:blue;cursor:pointer' title='Klik untuk lihat pdf' >".$bar1->notransaksicuti."</td>";	
						   }
						   else
						   {
						   echo "<td align=center onclick=\"previewPdfnonstaff('" . tanggalnormal($resCek2[0]['tanggal']) . "','" . $bar1->karyawanid . "',event)\"  style='color:blue;cursor:pointer' title='Klik untuk lihat pdf' >".$bar1->notransaksicuti."</td>";	
						   }
						   echo "<td align=center>".$arrstat[$bar1->status]."</td>
					</tr>	   
						   ";
			}	 
			echo"	 
				 </tbody>
				 <tfoot>
				 </tfoot>
				 </table>";
		}
	break;
	
	case 'loadkaryawan':
		$hariini = date("Y-m-d");
		$optkaryawan="";
		$whr='';
		if($kodeorg!=''){
			$whr=" and lokasitugas='".$kodeorg."'";
		}
		$str="select nik,namakaryawan, karyawanid from ".$dbname.".datakaryawan where tipekaryawan in(0,1,2,3,7,8) ".$whr." and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') order by namakaryawan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$optkaryawan.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($bar=$res->fetch())
		{
			$optkaryawan.="<option value='".$bar->karyawanid."'>".$bar->nik." - ".$bar->namakaryawan."</option>";
		}
		echo $optkaryawan;
	break;

	case 'viewpdf':
		# get necessary data from database

			// helper mendapatkan nama hari dari tanggal
			function namaHari($tanggal) {
				$day = date('D', strtotime($tanggal));
				$dayList = array(
					'Sun' => 'Minggu',
					'Mon' => 'Senin',
					'Tue' => 'Selasa',
					'Wed' => 'Rabu',
					'Thu' => 'Kamis',
					'Fri' => 'Jumat',
					'Sat' => 'Sabtu'
				);
				return $dayList[$day];
			}

			# data dayoff karyawan
			$sDayoff = selectQuery($dbname, 'sdm_dayoff', '*', "notransaksi='".$notransaksi."'");
			$rDayoff = fetchData($sDayoff);
			$karyawanid = $rDayoff[0]['karyawanid'];
			$tanggalPengajuan = $rDayoff[0]['tanggalpengajuan'];
			$explodeTanggalPengajuan = explode('-', $tanggalPengajuan);
			$tahunPengajuan = $explodeTanggalPengajuan[0];
			$bulanPengajuan = $explodeTanggalPengajuan[1];
			$tanggalPengajuan = $explodeTanggalPengajuan[2];
			$newTanggalPengajuan = $tanggalPengajuan.' '.numToMonth(intval($bulanPengajuan), 'I', 'long')." ".$tahunPengajuan;
			$tanggalMulai = $rDayoff[0]['tanggalmulai'];
			$explodeTanggalMulai = explode('-', $tanggalMulai);
			$tahunMulai = $explodeTanggalMulai[0];
			$bulanMulai = $explodeTanggalMulai[1];
			$hariMulai = $explodeTanggalMulai[2];
			$namaHariMulai = namaHari($tanggalMulai);
			$newTanggalMulai = $namaHariMulai.', '.$hariMulai.' '.numToMonth(intval($bulanMulai), 'I', 'long')." ".$tahunMulai;
			$tanggalSampai = $rDayoff[0]['tanggalsampai'];
			$explodeTanggalSampai = explode('-', $tanggalSampai);
			$tahunSampai = $explodeTanggalSampai[0];
			$bulanSampai = $explodeTanggalSampai[1];
			$hariSampai = $explodeTanggalSampai[2];
			$namaHariSampai = namaHari($tanggalSampai);
			$newTanggalSampai = $namaHariSampai.', '.$hariSampai." ".numToMonth(intval($bulanSampai), 'I', 'long')." ".$tahunSampai;
			$tanggalKerja = $rDayoff[0]['tanggalkerja'];
			$explodeTanggalKerja = explode('-', $tanggalKerja);
			$tahunKerja = $explodeTanggalKerja[0];
			$bulanKerja = $explodeTanggalKerja[1];
			$hariKerja = $explodeTanggalKerja[2];
			$namaHariKerja = namaHari($tanggalKerja);
			$newTanggalKerja = $namaHariKerja.', '.$hariKerja.' '.numToMonth(intval($bulanKerja), 'I', 'long')." ".$tahunKerja;
			$tanggalCreated = $rDayoff[0]['createdat'];
			$explodeTanggalCreated = explode('-', $tanggalCreated);
			$tahunCreated = $explodeTanggalCreated[0];
			$bulanCreated = $explodeTanggalCreated[1];
			$tanggalCreated = substr($explodeTanggalCreated[2], 0, 2);
			$jamCreated = substr($explodeTanggalCreated[2], 2, 9);
			if ($tanggalCreated == '00') {
				$newTanggalCreated = '-';
			} else {
				$newTanggalCreated = $tanggalCreated.' '.numToMonth(intval($bulanCreated), 'I', 'long')." ".$tahunCreated. ' '.$jamCreated;		
			}
			$jumlahHari = $rDayoff[0]['jumlahharidayoff'];
			$keterangan = $rDayoff[0]['keterangan'];
			$status = $rDayoff[0]['status'];

			# datakaryawan dayoff
			$sKaryawan = selectQuery($dbname, 'datakaryawan', '*', "karyawanid='".$karyawanid."'");
			$rKaryawan = fetchData($sKaryawan);
			$namaKaryawan = $rKaryawan[0]['namakaryawan'];
			$nik = $rKaryawan[0]['nik'];
			
			# golongan karyawan
			$golongan = $rKaryawan[0]['kodegolongan'];
			$sGolongan = selectQuery($dbname, 'sdm_5golongan', '*', "kodegolongan='".$golongan."'");
			$rGolongan = fetchData($sGolongan);
			$golonganKaryawan = $rGolongan[0]['namagolongan'];

			# departemen karyawan
			$departemen = $rKaryawan[0]['bagian'];
			$sDepartemen = selectQuery($dbname, 'sdm_5departemen', '*', "kode='".$departemen."'");
			$rDepartemen = fetchData($sDepartemen);
			$departemenKaryawan = $rDepartemen[0]['nama'];

			$unit = $rKaryawan[0]['lokasitugas'];

			# pt karyawan
			$kodept = $rKaryawan[0]['kodeorganisasi'];
			$arrHead = setheadreport('',$kodept);
			$namapt = $arrHead['nama'];
			$alamatpt = $arrHead['alamat'];

			# get url logo from local or prod
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
				$protocol = "https://";
			} 
			else {
				$protocol = "http://";
			}
			$url = $_SERVER['HTTP_HOST'];
			$self = $_SERVER['PHP_SELF'];
			$Explodeself = explode("/",$self);			
			$logo = $protocol.$url.'/'.$Explodeself[1].'/'.$arrHead['logo'];

			# get nama karyawan createdby
			$sCreatedBy = selectQuery($dbname, 'datakaryawan', '*', "karyawanid='".$rDayoff[0]['createdby']."'");
			$rCreatedBy = fetchData($sCreatedBy);
			if (count($rCreatedBy) > 0) {
				$createdBy = $rCreatedBy[0]['namakaryawan'];
			} else {
				$createdBy = "-";
			}
			# render pdf 
			$dokumen = '<!DOCTYPE html>';
			$dokumen .= '<html lang="en">';
	
			$dokumen .= '<head>';
			$dokumen .= '<meta charset="UTF-8">';
			$dokumen .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
			$dokumen .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
			$dokumen .= '<title>Day Off Staff</title>';
	
			$dokumen .= '<style>';
			$dokumen .= '*, *:before, *:after {-webkit-box-sizing:border-box; -moz-box-sizing:border-box;box-sizing:border-box;}';
			$dokumen .= 'body {font-size: 11px;font-family: "Garamond", sans-serif;}';
			$dokumen .= '.text-center {text-align: center;}';
			$dokumen .= '.text-right {text-align: right;}';
			$dokumen .= '.header {max-width: 100%;max-height: 100%;font-size: 1.5em;}';
			$dokumen .= '.header-content {margin-left: 10px; margin-bottom: 20px;}';
			$dokumen .= 'h2 {margin-top: 0;text-transform: uppercase;margin-bottom: 5px;}';
			$dokumen .= '.logo {float: left;margin-bottom: 5px;max-width: 80px;}';
			$dokumen .= '.title {width: 100%;}';
			$dokumen .= '.title-content {padding: 5px;margin: 0 auto;text-align: center;width: 100%;}';
			$dokumen .= 'ul {list-style-type: upper-alpha;margin-top: 10px;}';
			$dokumen .= 'ul li {display: list-item;text-align: -webkit-match-parent;font-size: 18px;font-weight: bold;color: #333;}';
			$dokumen .= 'ul li .content {font-size: 16px;font-weight: normal;margin-top: 1em;margin-bottom: 1em;}';
			$dokumen .= '.table-noborder {margin: 0 auto;width: 100%;border-collapse: collapse;margin-top: 10px;}';
			$dokumen .= '.table-noborder td {padding: 3px;}';
			$dokumen .= '.table {border-collapse: collapse;width: 60%;margin-top: 10px;font-size: 18px;}';
			$dokumen .= '.table th {padding: 8px 8px;border: 1px solid #000000;}';
			$dokumen .= '.table td {padding: 3px 3px;border: 1px solid #000000;}';
			$dokumen .= '.penutup {margin-top: 30px;font-size: 1em;}';
			$dokumen .= '</style>';
			
			$dokumen .= '</head>';
			$dokumen .= '<body>';

			$dokumen .= "<body class='A4'>";
			$dokumen .= '<section class="sheet padding-10mm">';
	
			$dokumen .= '<header class="header">';
            $dokumen .= '<img src="'.$logo.'" alt="Logo" class="logo" />';
            $dokumen .= '<div class="header-content">';
			$dokumen .= '<h2>'.$namapt.'</h2>';
			$dokumen .= '<div>'.$alamatpt.'</div>';
            $dokumen .= '</div>';
        	$dokumen .= '</header>';

			$dokumen .= '<br>';
			$dokumen .= '<hr />';

			$dokumen .= '<header class="title">';
			$dokumen .= '<div class="title-content">';
			$dokumen .= '<h2>SURAT PERINTAH BEKERJA PADA HARI LIBUR</h2>';
			$dokumen .= '</div>';
			$dokumen .= '<div class="text-right" style="font-size: 1.5em;">Tanggal Surat : '.$newTanggalPengajuan.'</div>';
			$dokumen .= '</header>';

			$dokumen .= '<ul>';
            $dokumen .= '<li>Diperintahkan Kepada,<br>';
			$dokumen .= '<div class="content">';
			$dokumen .= '<table class="table-noborder">';
			$dokumen .= '<tr>';
			$dokumen .= '<td style="width: 255px">Nama Karyawan</td>';
			$dokumen .= '<td style="width: 20px">:</td>';
			$dokumen .= '<td>'.$namaKaryawan.'</td>';
			$dokumen .= '</tr>';
			$dokumen .= '<tr>';
			$dokumen .= '<td>NIK OWL</td>';
			$dokumen .= '<td>:</td>';
			$dokumen .= '<td>'.$nik.'</td>';
			$dokumen .= '</tr>';
			$dokumen .= '<tr>';
			$dokumen .= '<td>Golongan</td>';
			$dokumen .= '<td>:</td>';
			$dokumen .= '<td>'.$golonganKaryawan.'</td>';
			$dokumen .= '</tr>';
			$dokumen .= '<tr>';
			$dokumen .= '<td>Departemen</td>';
			$dokumen .= '<td>:</td>';
			$dokumen .= '<td>'.$departemenKaryawan.'</td>';
			$dokumen .= '</tr>';
			$dokumen .= '<tr>';
			$dokumen .= '<td>Unit Kerja</td>';
			$dokumen .= '<td>:</td>';
			$dokumen .= '<td>'.$unit.'</td>';
			$dokumen .= '</tr>';
			$dokumen .= '</table>';
			$dokumen .= '</div>';
            $dokumen .= '</li>';
            $dokumen .= '<li>Untuk Melaksanakan Pekerjaan Pada Hari Libur<br>';
			$dokumen .= '<div class="content">';
			$dokumen .= '<table class="table-noborder">';
			$dokumen .= '<tr>';
			$dokumen .= '<td style="width: 255px">Hari, Tanggal Masuk Hari Libur</td>';
			$dokumen .= '<td style="width: 20px">:</td>';
			$dokumen .= '<td>'.$newTanggalMulai.'</td>';
			$dokumen .= '</tr>';
			$dokumen .= '<tr>';
			$dokumen .= '<td>Jumlah Day Off  didapatkan</td>';
			$dokumen .= '<td>:</td>';
			$dokumen .= '<td>'.$jumlahHari.' <span style="margin-left:15px">(Hari)</span></td>';
			$dokumen .= '</tr>';
			$dokumen .= '<tr>';
			$dokumen .= '<td>Keterangan/ Alasan Pemberian Surat Perintah bekerja pada Hari Libur</td>';
			$dokumen .= '<td>:</td>';
			$dokumen .= '<td>'.$keterangan.'</td>';
			$dokumen .= '</tr>';
			$dokumen .= '</table>';

			$dokumen .= '<div class="penutup">Demikian surat perintah bekerja pada hari libur ini dibuat, untuk dipergunakan sebagaimana mestinya</div>';
			$dokumen .= '</div>';

            $dokumen .= '</li>';
        	$dokumen .= '</ul>';

			$dokumen .= '<hr style="margin-top:15px;" />';

			$dokumen .= '<table class="table-noborder" style="margin-top: 20px;font-size: 18px">';
			$dokumen .= '<tr>';
			$dokumen .= '<td>Dibuat Oleh:</td>';
			$dokumen .= '</tr>';
			$dokumen .= '<tr>';
			$dokumen .= '<td>'.$createdBy.', '.$newTanggalCreated.'</td>';
			$dokumen .= '</tr>';
			$dokumen .= '</table>';
			$dokumen .= '<table class="table">';

			// $countApp = getCountApproval($jnsapp, $unit, $departemen);
			$sCountApp = selectQuery($dbname, "approval", "count(distinct level) as count", "notransaksi='".$notransaksi."'");
			$rCountApp = fetchData($sCountApp);
			$countApp = $rCountApp[0]['count'];
			for($i=1; $i<=$countApp; $i++) {
				$sApproval = selectQuery($dbname, 'setup_approval', '*', "jenispersetujuan='".$jnsapp."' and level='".$i."' and kodeunit='".$unit."'");
				$rApproval = fetchData($sApproval);
				$tipeapp = @$rApproval[0]['tipe'];
				$departemenapp = @$rApproval[0]['departemen'];
				$tipekaryawanapp = @$rApproval[0]['tipekaryawan'];
				$jabatanapp = @$rApproval[0]['jabatan'];
				$level=@$rApproval[0]['level'];

				@$arrDetail = detailApprove($i,$notransaksi,$jnsapp);
				// echo "<pre>"; print_r($arrDetail); exit;
				// echo "<pre>"; print_r("Level : ".$i."No Transaksi : ".$notransaksi."Jenis Approval : ".$jnsapp); exit;
				if($tipeapp=='1' && $arrDetail['status']!=''){
					if($arrDetail['status']!='1'){
						if($departemenapp!=''){
							$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							$arrDetail['nama'] = $opttipe[$departemenapp];
						}
						
						if($tipekaryawanapp!=''){
							$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						}
						
						if($jabatanapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							$arrDetail['nama'] = $opttipe[$jabatanapp];
						}
					}
				}
				$tanggalApproval = isset($arrDetail['tanggal']) ? $arrDetail['tanggal'] : '-';
				$explodeTanggalApproval = explode("-", $tanggalApproval);
				$tahunApproval = $explodeTanggalApproval[0];
				$bulanApproval = $explodeTanggalApproval[1];
				$tanggalApproval = substr($explodeTanggalApproval[2], 0, 2);
				$jamApproval = substr($explodeTanggalApproval[2], 3, 8);
				$tt = $tanggalApproval.' '.numToMonth(intval($bulanApproval), 'I', 'long')." ".$tahunApproval. ' '.$jamApproval;
				$namaApproval = $arrDetail['nama'];
				$newTanggalApproval = $tt;

				// check if approval level is empty or not 
				if($arrDetail['status']==''){
					$newTanggalApproval = '-';
					$namaApproval = '-';
				} 

				$dokumen .= '<tr>';
				$dokumen .= '<td style="width:250px">Disetujui Oleh:<br />'.$namaApproval.', '.$newTanggalApproval.'</td>';
				$dokumen .= '<td class="text-center" style="width:40px">'.(($arrDetail['status']=='9'||$arrDetail['status']=='')?"":$arrDetail['namastatus']).'</td>';
				$dokumen .= '<td style="width:360px">'.$arrDetail['komentar'].'</td>';
				$dokumen .= '</tr>';
				

				if($level==1){
					$approve1=$arrDetail['karyawanid'];
				}elseif($level==2){
					$approve2=$arrDetail['karyawanid'];
				}elseif($level==3){
					$approve3=$arrDetail['karyawanid'];
				}
				if($arrDetail['status']==1){
					$stat=1;
				}
			}

			$dokumen .= '</table>';
			
			$dokumen .= '</section>';
			$dokumen .= '</body>';

			$dompdf = new Dompdf();
			$options = $dompdf->getOptions();
			$options->set(array('isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true));
			$dompdf->loadHtml($dokumen);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->setOptions($options);
			$dompdf->render();
			$dompdf->stream('Laporan Dayoff.pdf', array('Attachment' => 0));
		break;
	
	default:
	break;
}
?>