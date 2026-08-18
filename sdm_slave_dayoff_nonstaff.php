<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
include_once('lib/formTable.php');

$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi','');
$karyawanid = checkPostGet('karyawanid','');
$tanggalpengajuan = tanggalsystem(checkPostGet('tglpengajuan',''));
$tglAwal = tanggalsystem(checkPostGet('tglAwal',''));
// $tglAwal = explode("-", checkPostGet('tglAwal', '00-00-0000'));
$tglEnd = tanggalsystem(checkPostGet('tglEnd',''));
// $tglEnd = explode("-", checkPostGet('tgl$tglEnd', '00-00-0000'));
$tanggalkerja = tanggalsystem(checkPostGet('tanggalkerja',''));
$keterangan = checkPostGet('keterangan','');
$jmldayoff = checkPostGet('jmldayoff','');
$pengganti = checkPostGet('pengganti','');
$persetujuan1 = checkPostGet('persetujuan1','');
$persetujuan2 = checkPostGet('persetujuan2','');
$persetujuan3 = checkPostGet('persetujuan3','');
$tglpengajuansch = tanggalsystemn(checkPostGet('tglpengajuansch',''));
$tgldarisch = tanggalsystemn(checkPostGet('tgldarisch',''));
$tglsampaisch = tanggalsystemn(checkPostGet('tglsampaisch',''));
$crjmldayoff = checkPostGet('crjmldayoff','');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if($tglpengajuansch == '--'){
	$tglpengajuansch = '';
}
if($tgldarisch == '--'){
	$tgldarisch = '';
}
if($tglsampaisch == '--'){
	$tglsampaisch = '';
}

$jnsapp = "DOFNS";

$tanggalskrg = date('Y-m-d H:i:s');

// init dompdf 
require('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

	switch ($method) {	
		
		case 'loadData':
			//Where 1=1, if $tglpengajuansch dll tdk dijalankan. Muncul semua list data
	        $where = "a.notransaksi like '%NONSTAFF%' ";
	        $footd = "";

	        if ($tglpengajuansch != '') {
	            $where.=" and a.tanggalpengajuan like '%" . $tglpengajuansch . "%'";
	        }
	        if ($tgldarisch != '') {
	            $where.=" and a.tanggalmulai='".$tgldarisch."'";
	        }
	        if ($tglsampaisch != '') {
	            $where.=" and a.tanggalsampai='".$tglsampaisch."'";
	        }	 
	        if ($crjmldayoff != '') {
	            $where.=" and a.jumlahharidayoff='".$crjmldayoff."'";
	        }
			if ($notransaksi != '') {
	            $where.=" and a.notransaksi='". $notransaksi . "'";
	        }
			$where.=" and right(a.notransaksi,4) in (".getOrgDetail(2).")";
		
	        $limit=20;
	        $page=0;
	        $jlhbrs=0;
	        if(isset($_POST['page'])){
	            $page=$_POST['page'];
	            if($page<0)
	            $page=0;
	        }
	        $offset=$page*$limit;
	        $maxdisplay=($page*$limit);
			$totrows=$offset/$limit+1;

			if($totrows==0){
				$totrows=1;
			}
			$isiRow='';
			for($er=1;$er<=$totrows;$er++){
				$sel = ($page==$er-1)? 'selected': '';
				$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
			}
			$str="select a.*, b.tanggalberlakusampai from ".$dbname.".sdm_dayoff AS a LEFT JOIN ".$dbname.".sdm_dayoff_dt AS b ON a.notransaksi = b.notransaksi where ".$where." order by a.createdat DESC, a.tanggalpengajuan DESC, a.tanggalmulai DESC, a.tanggalsampai DESC, a.tanggalkerja DESC limit ".$offset.",".$limit."";
			// echo $str;
			// exit('error');
	        $res=fetchdata($str);
	        $jlhbrs=count($res);
	        if($jlhbrs==0){
	            $tab.="<tr class=rowcontent>";
	            $tab.="<td colspan=12 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";
	            $tab.="</tr>";
			}
			else{

	            $no=$maxdisplay;
				$tab="";
				//di else ini muncul semua list data krn $jlhbrs >0
	            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOExceunition::getMessage());
	            $res->setFetchMode(PDO::FETCH_OBJ);
	            while($bar=$res->fetch()){
	            	$stat=0;
	            	$arrnotrans=explode('/', $bar->notransaksi);
	                $nmkaryawan 	= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar->karyawanid."'");

					# Get data karyawan
					$sKaryawan = selectQuery($dbname,'datakaryawan','*',"karyawanid='".$bar->karyawanid."'");
					$rKaryawan = fetchData($sKaryawan);
					$departemen = $rKaryawan[0]['bagian'];
					$golongan = substr($rKaryawan[0]['kodegolongan'], 0, 1);
					$unit = $rKaryawan[0]['lokasitugas'];

	                $no+=1;
					$class_truncate = strlen($bar->keterangan) > 45 ? 'class="truncate-keterangan"': '';

	                $tab.="<tr class=rowcontent>
	                    <td style='text-align:center;'>".$no."</td>
	                    <td>".$bar->notransaksi."</td>
	                    <td>".$nmkaryawan[$bar->karyawanid]."</td>
	                    <td style='min-width:70px;text-align:center;'>".tanggalnormal($bar->tanggalpengajuan)."</td>
	                    <td style='min-width:70px;text-align:center;'>".tanggalnormal($bar->tanggalmulai)."</td>
	                    <td style='min-width:70px;text-align:center;'>".tanggalnormal($bar->tanggalberlakusampai)."</td>
	                    <td align=center>".$bar->jumlahharidayoff."</td>
						<td><p style='padding:.5rem 1rem;' ".$class_truncate." data-text='".$bar->keterangan."'>".$bar->keterangan."</p></td>";
						$countApp = getCountApproval($jnsapp);
						// var_dump($countApp); exit;
						for($i=1;$i<=$countApp;$i++){
							@$arrDetail = detailApprove($i,$bar->notransaksi,$jnsapp);
							if($arrDetail['status']==1){
								$stat=1;
							}
							$tab.="<td align=center>".$arrDetail['nama']."<br>".$arrDetail['namastatus']."</td>";
						}

	                    if ($bar->status==0 and $stat==0){
							$tab.="<td align=center>
									<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdt('".$bar->notransaksi."','".$bar->karyawanid."','".tanggalnormal($bar->tanggalpengajuan)."','".tanggalnormal($bar->tanggalmulai)."','".$bar->jumlahharidayoff."', `".$bar->keterangan."`)\">
									</td>

	                               <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteht('" . $bar->notransaksi. "');\" ></td>
								   
								   <td align=center><img src=images/pdf.jpg class=resicon caption='PDF' title='Duty Staff".$bar->notransaksi."' onclick=\"viewpdf('".$bar->notransaksi."');\"></td>";       
	                    }
	                    else
	                    {
	                    	$tab.="<td align=center colspan=3><img src=images/pdf.jpg class=resicon caption='PDF' title='Duty Staff".$bar->notransaksi."' onclick=\"viewpdf('".$bar->notransaksi."');\"></td>";
	                    }
	                    
	                $tab.="</tr>";
				}
				$footd.="
					<tr><td colspan=14 align=center>
					<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
					<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
					</td>
					</tr>";
	        }
	        echo $tab."####".$footd;
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
			$dompdf->stream('Dayoff_Nonstaff.pdf', array('Attachment' => 0));
		break;
		
		case 'getjmldayoff':
			// if($tglEnd < $tglAwal){
			// 	exit('warning: Tanggal Sampai tidak boleh lebih kecil dari tanggal Mulai ');
			// }
			$whr=" and (kebun='GLOBAL' or kebun='".$_SESSION['empl']['lokasitugas']."')";
			if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
				$whr=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$_SESSION['empl']['lokasitugas']."')";
			}
			$hariawal=$tglAwal;
			// $hariakhir=$tglEnd;
			$dt1 = strtotime($hariawal);
			// $dt2 = strtotime($hariakhir);

			// $jumlahhari = selisitgl($hariakhir,$hariawal)+1;
			$jumlahhari = selisitgl($hariawal,$hariawal)+1;
			$n=$jumlahhari;

			$tglcuti='';
			$no="";
			for ($i=0; $i < $n ; $i++) { 
			#cek apakah tanggal termasuk hari libur
			$tglcuti= date("Ymd",strtotime("+".$i." Day",strtotime($tglAwal)));	
			$str="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tglcuti."'".$whr;
			$res=fetchData($str);
			$jmlhbaris=count($res);
			if ($jmlhbaris>0) {
				$jumlahhari=$jumlahhari-1;
			}
				$no++;	
		}

			$jarakwaktu=strtotime($tglAwal)-strtotime($tglAwal);
			// echo number_format($jarakwaktu/60/60/24+1)-$jumlahhari;
			echo '1';
		break;

		case 'insertht':
		        if ($tanggalpengajuan=='' || $tglAwal=='') {
		            exit('warning : All field may not empty.');
				}
				
				$str="select notransaksi from ".$dbname.".sdm_dayoff_dt_vw where karyawanid='".$karyawanid."' and status!='2' 
				and tanggaldayoff ='".$tglAwal."' order by notransaksi desc";
				// echo $str;
				// exit('error');
				$bar = fetchData($str);
				if(count($bar)>0){
					exit('Warning : untuk tanggal day off yang dipilih sudah ada dalam tahap pengajuan atau telah disetujui , silahkan pilih tanggal lain');
				}

				$notransaksi=generatenotrans();
					$createdat = date('Y-m-d H:i:s');

					$str = "insert into ".$dbname.".sdm_dayoff (notransaksi,karyawanid,tanggalpengajuan,tanggalmulai,jumlahharidayoff,keterangan,status, createdby, createdat) values ('".$notransaksi."','".$karyawanid."','".$tanggalpengajuan."','".$tglAwal."','".$jmldayoff."','".$keterangan."','0', '".$_SESSION['standard']['userid']."', '".$createdat."')";
					// echo $str;
					// exit('error');
					try{
						$owlPDO->exec($str); 

						$berlakusampai = date('Y-m-d', strtotime(tanggalsystemn(tanggalnormal($tglAwal)). ' + 2 month'));
						// echo "<pre>"; print_r(tanggalsystemn(tanggalnormal($tglAwal))); exit;
						$strdt = "insert into ".$dbname.".sdm_dayoff_dt (notransaksi,karyawanid,tanggaldayoff,tanggalberlakusampai,jumlahharidayoff) values ('".$notransaksi."','".$karyawanid."','".$tglAwal."','".$berlakusampai."','1')";
							
						try {
							$owlPDO->exec($strdt); 
						} catch(PDOExceunition $e){
							echo " Gagal," . addslashes($e->getMessage());
						}

					}catch(PDOExceunition $e){
						echo " Gagal," . addslashes($e->getMessage());
					}

					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jnsapp."' ";
					$owlPDO->exec($str);
					
					$listpersetujuan=$_POST['persetujuan'];
					foreach($listpersetujuan as $key=>$val)
						{

							$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jnsapp."' and level='".$key."'";
							$res=fetchData($str);
							$tipeapp = $res[0]['tipe'];
							$departemenapp = $res[0]['departemen'];
							$tipekaryawanapp = $res[0]['tipekaryawan'];
							$jabatanapp = $res[0]['jabatan'];
							
							if($tipeapp=='1'){
								if($departemenapp!=''){
									$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
									$res=fetchdata($str);
									foreach($res as $keyx=>$valx){
										$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
										$owlPDO->exec($str);
									}
								}
								if($tipekaryawanapp!=''){
									$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
									$res=fetchdata($str);
									foreach($res as $keyx=>$valx){
										$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
										$owlPDO->exec($str);
									}
								}
								if($jabatanapp!='0'){
									$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
									$res=fetchdata($str);
									foreach($res as $keyx=>$valx){
										$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
										$owlPDO->exec($str);
									}
								}
							}else{
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
								try
								{
									$owlPDO->exec($str);
								}
								catch (PDOException $e) 
								{
									print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
								}
							}
						}
		break;

	    case'deleteht':

	         $strdt = "delete from ".$dbname.".sdm_dayoff where notransaksi='".$notransaksi."'";
	        try {
	            $owlPDO->exec($strdt);
				
				$strdelapp="delete from ".$dbname.".approval where jenispersetujuan='DOFNS' and notransaksi='".$notransaksi."'";
				try {
					$owlPDO->exec($strdelapp);

				} catch (PDOExceunition $e) {
			        print " Gagal: " . $e->getMessage() . "\n";
			        die();
			    }
				
	        } catch (PDOExceunition $e) {
	            print " Gagal: " . $e->getMessage() . "\n";
	            die();
	        }
	    break;

	    case 'updateht':

	    	$strdt = "delete from ".$dbname.".sdm_dayoff_dt where notransaksi='".$notransaksi."'";
		        try {
		            $owlPDO->exec($strdt);
		        } catch (PDOExceunition $e) {
		            print " Gagal: " . $e->getMessage() . "\n";
		            die();
		        }

	    	$str="select notransaksi from ".$dbname.".sdm_dayoff_dt_vw where karyawanid='".$karyawanid."' and status!='2' 
				and tanggaldayoff ='".$tglAwal."' order by notransaksi desc";
				// echo $str;
				// exit('error');
				$bar = fetchData($str);
				if(count($bar)>0){
					exit('Warning : untuk tanggal day off yang dipilih sudah ada dalam tahap pengajuan atau telah disetujui , silahkan pilih tanggal lain');
				}

	        $strht = "update ".$dbname.".sdm_dayoff set tanggalpengajuan='".$tanggalpengajuan."',tanggalmulai='".$tglAwal."',jumlahharidayoff='".$jmldayoff."', keterangan='".$keterangan."' where notransaksi='".$notransaksi."'";
		
	        try{
				$owlPDO->exec($strht); 

				$berlakusampai = date('Y-m-d', strtotime(tanggalsystemn(tanggalnormal($tglAwal)). ' + 2 month'));
				// echo "<pre>"; print_r(tanggalsystemn(tanggalnormal($tglAwal))); exit;
				$strdt = "insert into ".$dbname.".sdm_dayoff_dt (notransaksi,karyawanid,tanggaldayoff,tanggalberlakusampai,jumlahharidayoff) values ('".$notransaksi."','".$karyawanid."','".$tglAwal."','".$berlakusampai."','1')";
					
				try {
					$owlPDO->exec($strdt); 
				} catch(PDOExceunition $e){
					echo " Gagal," . addslashes($e->getMessage());
				}

			}catch(PDOExceunition $e){
				echo " Gagal," . addslashes($e->getMessage());
			}

	    break;	    
	    
		default:
		break;
	}

	function generatenotrans(){
		global $dbname;
		global $owlPDO;
		global $tanggalpengajuan;

		$tempno=substr($tanggalpengajuan,0,6)."/"."NONSTAFF"."/".$_SESSION['empl']['lokasitugas'];
		$str="select notransaksi from ".$dbname.".sdm_dayoff where notransaksi like '%".$tempno."' order by notransaksi desc limit 1";
		$res=fetchdata($str);
		$notrans = substr($res[0]['notransaksi'],0,5);

		if($notrans==''){
			$notransaksi="00001/".$tempno;	
		}else{
			$notransaksi=addZero(($notrans+1),5)."/".$tempno;
		}

		return $notransaksi;
	}

?>