<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses          = checkPostGet('proses', '');
$txtFind         = checkPostGet('txtfind', '');
$absnId          = explode("###", checkPostGet('absnId', ''));
$tgl             = isset($absnId[1]) ? tanggalsystem($absnId[1]) : '';
$kdOrg           = $absnId[0];
$krywnId         = checkPostGet('krywnId', '');
$shifTid         = checkPostGet('shifTid', '');
$asbensiId       = checkPostGet('asbensiId', '');
$Jam             = checkPostGet('Jam', '');
$Jam2            = checkPostGet('Jam2', '');
$Jam3            = checkPostGet('Jam3', '');
$Jam4            = checkPostGet('Jam4', '');
$ket             = checkPostGet('ket', '');
$periode         = checkPostGet('period', '');
$jmlHk           = checkPostGet('jmlHk', '0');
$idOrg           = substr($_SESSION['empl']['lokasitugas'], 0, 4);
$catu            = checkPostGet('catu', '');
$insentif        = checkPostGet('insentif', '');
$insentiflibur   = checkPostGet('insentiflibur', '');
$penaltykehadiran= checkPostGet('dendakehadiran', '');
$kdorgxxx        = checkPostGet('kdorg', '');
$noakun          = checkPostGet('noakun', '');
$tglxxx          = tanggalsystem(checkPostGet('tgl', ''));

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}


$periodeAkutansi = @$_SESSION['org']['period']['tahun'] . "-" . @$_SESSION['org']['period']['bulan'];
if($periodeAkutansi=='-'){
	exit("Warning : Periode akuntansi belum ada !");
}

$kdJbtn  = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$tipeKary= makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan');
$nmkarya = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optorg  = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$tipeorg =makeOption($dbname,'organisasi','kodeorganisasi,tipe');


##buat cek proses gaji
$iPer = "select * from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and "
        . " sudahproses=0 order by periode asc limit 1";
$nPer = $owlPDO->query($iPer) or die(print " Gagal: " . PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
$dPer = $nPer->fetch();

$perGjSkrg = $dPer['periode'];
if($asbensiId!=''){
    #update hk jika user input hk=1 tapi tipe absen untuk hk=0, otomatis terisi 0
    $whrd="kodeabsen='".$asbensiId."'";
    $hkDt=makeOption($dbname,'sdm_5absensi','kodeabsen,nilaihk',$whrd);

    $whrGjDt="karyawanid='".$krywnId."' and tahun='".$periode."' and idkomponen=1";
    $sGaji="select (sum(jumlah)/25) as gaji from ".$dbname.".sdm_5gajipokok where ".$whrGjDt."";
    $rGaji=fetchData($sGaji);

    if($hkDt[$asbensiId]==0){
        $jmlHk=$hkDt[$asbensiId];
        $umr=0;
    }

	$str = "select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$krywnId."'";
	$bar=fetchData($str);
	if($bar[0]['tipekaryawan']==4 && ($asbensiId=='HL' || $asbensiId=='MG' || $asbensiId=='LN' || $asbensiId=='L' )){
		$jmlHk=0;
	}

    $umr=$rGaji[0]['gaji']*$jmlHk;

    if($jmlHk>1){
        exit('warning: HK Tidak boleh lebih dari 1');
    }
}

switch ($proses) {
	case'getKegiatan':

		$optKegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sjnskrj="select * from ".$dbname.".setup_kegiatan where noakun = '".$noakun."' and status ='1' and kelompok = 'KNT'  order by kodekegiatan asc";
		$res=fetchdata($sjnskrj);
		foreach($res as $rjnskrj){

			$s="";
			if($param['kodekegiatan'] == $rjnskrj['kodekegiatan']){
				$s="selected";
			}
			$optKegiatan.="<option value=".$rjnskrj['kodekegiatan']." ".$s.">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";

		}

		if ($noakun[0] == '4') {
			$flag = 1;
		} else {
			$flag = 0;
		}

		echo $optKegiatan."###".$flag;
	break;

	case'getKehadiran':
		$optAbsen="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".sdm_5absensi where status='1' and absensidt=1 order by nilaihk desc";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['nilaihk']>0){				
				$d="DIBAYAR";
			}else{
				$d="TIDAK DIBAYAR";
			}
			if($d!=$n){			
				$optAbsen.="<optgroup label=\"".$d."\">";
			}
			$optAbsen.="<option value=".$bar['kodeabsen'].">".strtoupper($bar['keterangan'])."</option>";
			$n=$d;
			if($d!=$n){
				$optAbsen.="</optgroup>";
			}
		}
		
		echo $optAbsen;
	break;

    case'cariOrg':
        //echo"warning:masuk";
        $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where namaorganisasi like '%" . $txtFind . "%' or kodeorganisasi like '%" . $txtFind . "%' "; //echo "warning:".$str;exit();
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
            echo"<fieldset>
				<legend>Result</legend>
				<div style=\"overflow:auto; height:300px;\" >
				<table class=data cellspacing=1 cellpadding=2  border=0>
					<thead>
					<tr class=rowheader>
						<td class=firsttd>
						No.
						</td>
						<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
						<td align=center>" . $_SESSION['lang']['namaorganisasi'] . "</td>
					</tr>
					</thead>
					<tbody>";
            $no = 0;
            while ($bar = $res->fetch()) {
                $no+=1;
                echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setOrg('" . $bar->kodeorganisasi . "','" . $bar->namaorganisasi . "')\" title='Click' >
					<td class=firsttd>" . $no . "</td>
					<td>" . $bar->kodeorganisasi . "</td>
					<td>" . $bar->namaorganisasi . "</td>
				</tr>";
            }
            echo "</tbody>
				<tfoot>
				</tfoot>
			</table></div></fieldset>";
        break;
		
    case'cariOrg2':
        $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where namaorganisasi like '%" . $txtFind . "%' or kodeorganisasi like '%" . $txtFind . "%' "; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        
		echo"<fieldset>
			<legend>Result</legend>
			<div style=\"overflow:auto; height:300px;\" >
			<table class=data cellspacing=1 cellpadding=2  border=0>
				<thead>
				<tr class=rowheader>
					<td class=firsttd>No.</td>
					<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td align=center>" . $_SESSION['lang']['namaorganisasi'] . "</td>
				</tr>
				</thead>
				<tbody>";
		$no = 0;
		while ($bar = $res->fetch()) {
			$no+=1;
			echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setOrg2('" . $bar->kodeorganisasi . "','" . $bar->namaorganisasi . "')\" title='Click' >
				<td class=firsttd>" . $no . "</td>
				<td>" . $bar->kodeorganisasi . "</td>
				<td>" . $bar->namaorganisasi . "</td>
			</tr>";
		}
		echo "</tbody>
			<tfoot>
			</tfoot>
		</table></div></fieldset>";
        break;
    case'cekData':
        if ($kdOrg == '') {
            exit("error: Unit code must filled");
        }
		
		if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){			
			if((substr($param['noakun'],0,1)!='7' and substr($param['noakun'],0,1)!='8') and substr($param['noakun'],0,1)!='4') {
				exit("Warning : Alokasi wajib diisi.\nNama Karyawan : ".$nmkarya[$krywnId]."\nNomor Akun : ".$param['noakun']."\nAlokasi : ".$param['alokasi']);
			}
			
			if((substr($param['noakun'],0,1)=='7' or substr($param['noakun'],0,1)=='8') and $param['alokasi']!=''){
				exit("Warning : Kosongkan Alokasi.\nNama Karyawan : ".$nmkarya[$krywnId]."\nNomor Akun : ".$param['noakun']."\nAlokasi : ".$param['alokasi']);
			}
		}		
		
		#validasi maksimal HK BHL
		cekmaxnilaihk($krywnId,tanggalsystemn(tanggalnormal($tgl)),$jmlHk,'0','new',$exit='0');
		
		#query pengecekan apakah FP aktif / tidak
		$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".substr($kdOrg,0,4)."' and tanggal<='".tanggalsystemn(tanggalnormal($tgl))."'";
		$res = fetchData($str);
		$statusfp    = $res[0]['status'];//1 aktif,0 tidak
		$tipevalidasi= $res[0]['tipevalidasi'];
		$detailexp   = explode(",",$res[0]['detailvalidasi']);
		foreach($detailexp as $vald){
			$detval[$vald]=$vald;
		}
		$arrUpload = array();
		if($statusfp==1){
			if($asbensiId=='H'){
				$arrUpload[]['nik'] = $krywnId;
				validasifp($tipevalidasi,$detval,'SDM',$arrUpload,tanggalsystemn(tanggalnormal($tgl)),'1');
				
				/* $str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$krywnId."' and tanggalabsen='".$tgl."' limit 1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				if($krywnId != $bar['karyawanid']){
					$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$krywnId."'");
					$optNik = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$krywnId."'");
					echo "Warning : Absen fingerprint untuk karyawan dg NIK : <br>".$optNik[$krywnId]." = ".$optNamaKaryawan[$krywnId]."<br>belum ada.<br>Silahkan lakukan proses melalui menu : SDM - Proses - Fingerprint.";
					exit;
				} */		
			}
		}
		
		#Cek apakah di sdm_5absensi upload file bersifat Mandatory atau tidak
		$pecahtanggalFile = explode("-",tanggalsystem($absnId[1]));
		$tanggalFilenya = $pecahtanggalFile[0].$pecahtanggalFile[1].$pecahtanggalFile[2];
		$notransaksiUploadFile = $tanggalFilenya.$krywnId;
		$where = "notransaksi='".$notransaksiUploadFile."' ";
		$qCekListFileUpload = selectQuery($dbname, "listfileupload", "*", $where);
		$resValidasiDokumen = fetchData($qCekListFileUpload);

		
		if(count($resValidasiDokumen) <= 0) {
			// exit("Warning : Data belum di upload");

			# Convert kodenya ke nama kehadiran
			$where = "kodeabsen='".$asbensiId."' and validasidokumen = 1 ";
			$conKehadiran = makeOption($dbname,"sdm_5absensi","kodeabsen,keterangan",$where);

			# Buat html
			$bgHtmlnya = "style=color:red;font-weight:bold;";
			$bgHtmlnya2 = "style=color:red;font-size:12px;margin-top:20px;";
			$teksHtml2 = "<p ".$bgHtmlnya2.">Noted : Untuk mengatur Validasi Dokumen bisa di lihat di Menu (SETUP > ABSENSI)</p>";

			# Query cek apakah Validasi dari setup Absensi Aktif atau Tidak Aktif
			$qCekUploadDokumen = selectQuery($dbname, "sdm_5absensi", "*", $where);
			$resValidasiDokumen = fetchData($qCekUploadDokumen);
			if(count($resValidasiDokumen) > 0) {
				exit("Warning : File Upload Dokumen Kosong, Jenis Absensi : <span ".$bgHtmlnya.">".$conKehadiran[$asbensiId]."</span>, Validasi Dokumennya Aktif <br/> ".$teksHtml2." ");
			}
		}	

		// exit('warning');

		// $where = "kodeabsen='".$asbensiId."' and validasidokumen = 1 ";
		// $qCekUploadDokumen = selectQuery($dbname, "sdm_5absensi", "*", $where);
		// $resValidasiDokumen = fetchData($qCekUploadDokumen);
		// if(count($resValidasiDokumen) > 0) {
		// 	exit('Warning : File Upload Dokumen Wajib Diisi Jenis Absen ini !!!');
		// }
		
		#Ambil UMR
		//$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kdOrg."'");
		// if($tipeorg[$kdOrg]=='HOLDING'){
		// 	$tablename='sdm_5gajipokokho';
		// }else{
		// 	$tablename='sdm_5gajipokok';
		// }

		$tablename='sdm_5gajipokok';

		$str = "select * from ".$dbname.".".$tablename." where karyawanid='".$krywnId."' and tahun='".$periode."' and idkomponen='1'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$jumlahumr+=$bar['jumlah'];
		
		$tipekaryawan_val = getKary($krywnId,'tipekaryawan');
		if($tipekaryawan_val != '0'){
			if($jumlahumr==''){
				exit("Warning : Gaji Pokok untuk periode ".$periode." belum ada !");
			}
		}
		
		
        $sCek = "select DISTINCT tanggalmulai,tanggalsampai,periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and sudahproses=0 and tanggalmulai<='" . $tgl . "' and tanggalsampai>='" . $tgl . "'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_OBJ);
		$rCek=owlBaris($qCek);
        if ($rCek > 0) {
			
			// Kebun Aktifitas Cek tidak bisa menginput ketika sudah di input
			$str = "select count(*) as datakbm from ".$dbname.".kebun_aktifitas where tanggal='".$tgl."' and 
			(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$datakbm=$bar['datakbm'];

			$strData = "select * from ".$dbname.".kebun_aktifitas where tanggal='".$tgl."' and 
			(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
			$resData=$owlPDO->query($strData) or die(print " Gagal: ".PDOException::getMessage());
			$resData->setFetchMode(PDO::FETCH_ASSOC);
			$barDt=$resData->fetch();


			// exit("warning".print_r($barDt));
			// exit("warning".print_r($jmlHk));
			if($datakbm > 0 and $jmlHk > 0) {
				exit("Warning : Data sudah terdaftar di BKM, dengan<b> No Transaksi : ".$barDt['notransaksi'])."</b>";
			}

			// CEK DATA PANEN SPB

			$str = "select count(*) as datapnn from ".$dbname.".kebun_prestasi_vw where tanggal='".$tgl."' and 
			(nikmandor='".$krywnId."' or karyawanid='".$krywnId."' or kerani='".$krywnId."')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$datapnn=$bar['datapnn'];

			$strData = "select * from ".$dbname.".kebun_prestasi_vw where tanggal='".$tgl."' and 
			(nikmandor='".$krywnId."' or karyawanid='".$krywnId."' or kerani='".$krywnId."')";
			$resData=$owlPDO->query($strData) or die(print " Gagal: ".PDOException::getMessage());
			$resData->setFetchMode(PDO::FETCH_ASSOC);
			$barDtpnn=$resData->fetch();


			// exit("warning".print_r($barDt));
			// exit("warning".print_r($jmlHk));
			if($datapnn > 0 and $jmlHk > 0) {
				exit("Warning : Data sudah terdaftar di Kegiatan Panen, dengan<b> No Transaksi : ".$barDtpnn['notransaksi'])."</b>";
			}

			
            $sCek = "select kodeorg,tanggal from " . $dbname . ".sdm_absensiht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$qCek->setFetchMode(PDO::FETCH_OBJ);
			$rCek=owlBaris($qCek);
            #cek premi
            #jika status di datakaryawawan apakah menerima premi dan insentif 
            #dan apakah sudah melebhi dari setup premi, jika lebih atau sama dengan maka premi=0
 
            

            if ($rCek < 1) {
                $sIns = "insert into " . $dbname . ".sdm_absensiht (`kodeorg`,`tanggal`,`periode`,`updateby`) values
					('" . $kdOrg . "','" . $tgl . "','" . $periode . "','" . $_SESSION['standard']['userid'] . "')"; //echo"warning:".$sIns;
				try{
					$owlPDO->exec($sIns); 
					if ($_POST['premi'] == '') {
                        $_POST['premi'] = 0;
                    }
                    if ($_POST['insentif'] == '') {
                        $_POST['insentif'] = 0;
                    }
                    if ($_POST['premidt'] == '') {
                        $_POST['premi'] = 0;
                    }
					
					$sdtCek = "select distinct * from " . $dbname . ".kebun_kehadiran_vw where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
					$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
					$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
					$rDtCek=owlBaris($qDtCek);
                    $rSource = $qDtCek->fetch();
                    if ($rDtCek > 0 and $jmlHk>0) {
                        exit("error: Employee registered on transaction : " . $rSource['notransaksi']);
                    }
					
					$sdtCek = "select distinct * from " . $dbname . ".kebun_prestasi_vw 
                                 where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
					$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
					$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
					$rDtCek=owlBaris($qDtCek);
					$rSource = $qDtCek->fetch();
					if ($rDtCek > 0 and $jmlHk>0) {
						exit("error: Employee registered on transaction : " . $rSource['notransaksi']);
					}
					
					#cek apakah sudah pernah di-input
					$str = "select count(*) as jumdata from ".$dbname.".sdm_absensidt_vw where tanggal='" . $tgl . "' and karyawanid='".$krywnId."'  ";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
					$jumdata=$bar['jumdata'];
					
					if($jumdata>0 and $jmlHk>0){
						exit("Warning : Sudah ada data");
					}

					
					#cek kebun aktifitas, jika hk>0 maka err,
					#absen sudah ada dari db tsb, bik khl/kht,
					#diperboolehkan input premi saja dari absensi dengan hk 0
					
					$str = "select count(*) as jumbkm from ".$dbname.".kebun_aktifitas where tanggal='" . $tgl . "' and
							(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
					$jumbkm=$bar['jumbkm'];
					
					if($jumbkm>0 and $jmlHk>0){
						exit("Warning : Sudah terdaftar di BKM (sebagai mandor/mandor1/kerani)");
					}
					
					$tipekary=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',"karyawanid='".$krywnId."'");
					$str = "select sum(upah) as jumvhc from ".$dbname.".vhc_runhk_vw where tanggal='" . $tgl . "' and idkaryawan='".$krywnId."'";
					$res=fetchdata($str);
					$jumvhc=$res[0]['jumvhc'];
					if($tipekary[$krywnId]!='4'){
						if(count($res)>0 and $jmlHk>0){
							if($_SESSION['standard']['username']=='tim.owl3') {
								echo "<pre>";
								print_r($res);
								print_r($jmlHk);
							}

							exit("Warning : Sudah terdaftar ditraksi pekerjaan sebagai operator x1");
						}
					}else{						
						if($jumvhc>0 and $jmlHk>0){
							if($_SESSION['standard']['username']=='tim.owl3') {
								echo "<pre>";
								print_r($jumvhc);
								print_r($jmlHk);
							}

							exit("Warning : Sudah terdaftar ditraksi pekerjaan sebagai operator x2");
						}
					}

					#penguncian hari minggu hanya boleh masuk MG dan hari libur HL ngunci ke tanggal dan setup hari libur
					
					$day = date('D', strtotime($tgl));
					if($day=='Sun'){
						if($asbensiId!='MG' and $tipekary[$krywnId]!='4'){
							#exit("Warning: Absen hanya boleh 'hari minggu' dan HK harus 0, nilai hk di-isikan di premi");
						}
					}	
					
                    $sDetIns = "insert into " . $dbname . ".sdm_absensidt (`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`, `jamistirahatdari`, `jamistirahatsampai`,`penjelasan`,`catu`,`penaltykehadiran`,`premi`,`insentif`,`hk`,`umr`, `insentiflibur`,`noakun`,`alokasi`,`kegiatan`) 
					 values ('" . $kdOrg . "','" . $tgl . "','" . $krywnId . "','" . $shifTid . "','" . $asbensiId . "','" . $Jam . "','" . $Jam2 . "','" . $Jam3 . "','" . $Jam4 . "','" . $ket . "'," . $catu . "," . $penaltykehadiran . "," . $_POST['premidt'] . "," . $_POST['insentif'] . ",'".$jmlHk."','".$umr."','".$insentiflibur."','".$param['noakun']."','".$param['alokasi']."','".$param['kodekegiatan']."')";
					try{
						$owlPDO->exec($sDetIns); 
					}catch (PDOException $e){
						echo "DB Error : " . $e->getMessage();
						die();
					}
				}catch (PDOException $e){
					echo "DB Error : " . $e->getMessage();
					die();
				}
            } else {
				
					//exit("Error:$umr");
					if ($_POST['premi'] == '') {
						$_POST['premi'] = 0;
					}
					if ($_POST['insentif'] == '') {
						$_POST['insentif'] = 0;
					}
					if ($_POST['premidt'] == '') {
						$_POST['premidt'] = 0;
					}

					$sdtCek = "select distinct * from " . $dbname . ".kebun_kehadiran_vw where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
					$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
					$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
					$rDtCek=owlBaris($qDtCek);
					$rSource = $qDtCek->fetch();
					if ($rDtCek > 0 and $jmlHk>0) {
						exit("Warning: Employee registered on transaction : " . $rSource['notransaksi']);
					}
					
					$sdtCek = "select distinct * from " . $dbname . ".kebun_prestasi_vw 
									 where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
					$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
					$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
					$rDtCek=owlBaris($qDtCek);
					$rSource = $qDtCek->fetch();
					if ($rDtCek > 0 and $jmlHk>0) {
						exit("Warning: Employee registered on transaction : " . $rSource['notransaksi']);
					}
                
					#cek apakah sudah pernah di-input
					$str = "select count(*) as jumdata from ".$dbname.".sdm_absensidt_vw where tanggal='" . $tgl . "' and karyawanid='".$krywnId."'  ";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
					$jumdata=$bar['jumdata'];
					
					if($jumdata>0 and $jmlHk>0){
						exit("Warning:Sudah ada data");
					}
					
					$tipekary=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',"karyawanid='".$krywnId."'");
					# Cek baru
					$str = "select * from ".$dbname.".vhc_runhk_vw where tanggal='" . $tgl . "' and idkaryawan='".$krywnId."'";
					$resnew=fetchdata($str);

					#cek absensi dari vhc
					$str = "select sum(upah) as jumvhc from ".$dbname.".vhc_runhk_vw where tanggal='" . $tgl . "' and idkaryawan='".$krywnId."'";
					$res=fetchdata($str);
					$jumvhc=$res[0]['jumvhc'];
					if($tipekary[$krywnId]!='4'){
						if(count($resnew)>0 and $jmlHk>0){
							if($_SESSION['standard']['username']=='tim.owl3') {
								echo "<pre>";
								print_r($res);
								print_r($jmlHk);
							}

							exit("Warning : Sudah terdaftar ditraksi pekerjaan sebagai operator. x3");
						}
					}else{						
						if($jumvhc>0 and $jmlHk>0){
							if($_SESSION['standard']['username']=='tim.owl3') {
								echo "<pre>";
								print_r($jumvhc);
								print_r($jmlHk);
							}

							exit("Warning : Sudah terdaftar ditraksi pekerjaan sebagai operator x4");
						}
					}
				
					
					#cek kebun aktifitas, jika hk>0 maka err,
					#absen sudah ada dari db tsb, bik khl/kht,
					#diperboolehkan input premi saja dari absensi dengan hk 0
					$jumbkm=0;
					$nobkm='';
					$str = "select * from ".$dbname.".kebun_aktifitas where tanggal='" . $tgl . "' and
							(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						$jumbkm+=1;
						$nobkm.=$bar['notransaksi'].', ';
					}
						
					if($jumbkm>0 and $jmlHk>0){
						exit("Warning:Sudah terdaftar dibkm (sebagai mandor/mandor1/kerani) \n No.BKM : ".$nobkm." ");
					}
		
					#penguncian hari minggu hanya boleh masuk MG dan hari libur HL ngunci ke tanggal dan setup hari libur
					$day = date('D', strtotime($tgl));
					if($day=='Sun'){
						if($asbensiId!='MG' and $tipekary[$krywnId]!='4'){
							#exit("Warning:Absen hanya boleh 'hari minggu' dan HK harus 0, nilai hk di-isikan di premi");
							#ksbw ada masuk minggu kalau disini MG transportnya gak masuk
						}
					}

					
					$sDetIns = "insert into " . $dbname . ".sdm_absensidt
					(`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`,`jamistirahatdari`,`jamistirahatsampai`, `penjelasan`,`catu`,`penaltykehadiran`,`premi`,`insentif`,`hk`,`umr`,`insentiflibur`,`noakun`,`alokasi`,`kegiatan`) 
					values ('" . $kdOrg . "','" . $tgl . "','" . $krywnId . "','" . $shifTid . "','" . $asbensiId . "','" . $Jam . "','" . $Jam2 . "','" . $Jam3 . "','" . $Jam4 . "','" . $ket . "'," . $catu . "," . $penaltykehadiran . "," . $_POST['premidt'] . "," . $_POST['insentif'] . ",'".$jmlHk."','".$umr."','".$insentiflibur."','".$param['noakun']."','".$param['alokasi']."','".$param['kodekegiatan']."')";

					try{
					$owlPDO->exec($sDetIns); 
				}catch (PDOException $e){
					echo "DB Error : " . $e->getMessage();
					die();
				}
            }
        } else {
            echo"warning:Date out of payment period";
            exit();
        }
        break;
		
    case'loadNewData':
        echo"
                <table class=sortable border=0 cellspacing=1 cellpadding=5 style=min-width:50%>
                <thead>
                <tr class=rowheader style=height:30px>
                <th align=center>No.</th>
                <th align=center width=50px>" . $_SESSION['lang']['kodeorganisasi'] . "</th>
                <th align=center>" . $_SESSION['lang']['namaorganisasi'] . "</th>
                <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
                <th align=center>" . $_SESSION['lang']['periode'] . "</th>
                <th align=center>" . $_SESSION['lang']['updateby'] . "</th>
                <th align=center colspan=4>Action</th>
                </tr>
                </thead>
                <tbody>
                ";
        $limit = 15;
        $page = 0;
		
        if (isset($_POST['page'])) {	
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
		$maxdisplay=($page*$limit);
		$no=$maxdisplay;
		
		$wh="";
		if($kdorgxxx!=''){
			$wh.="and kodeorg='".$kdorgxxx."'";
		}
		
		if($tglxxx!='--' and $tglxxx!=''){
			$wh.="and tanggal='".$tglxxx."'";
		}

		$nourut = 0;
		$optNewOrg = "";
		foreach(getOrgDetail(29) as $key => $value){
			if($nourut==0){
				$optNewOrg .= "'".$key."'";
			}else{
				$optNewOrg .= ",'".$key."'";
			}
			$nourut++;
		}

		foreach(getOrgDetail(1) as $key => $value){
			if($nourut==0){
				$optNewOrg .= "'".$key."'";
			}else{
				$optNewOrg .= ",'".$key."'";
			}
			$nourut++;
		}

		foreach(getOrgDetail(21) as $key => $value){
			if($nourut==0){
				$optNewOrg .= "'".$key."'";
			}else{
				$optNewOrg .= ",'".$key."'";
			}
			$nourut++;
		}

		$wh.="and kodeorg in (".$optNewOrg.")";
		#cek apakah ada detail yg abnomral, nilaihk=1, umr=0
		// $sData="select * from ".$dbname.".sdm_absensidt_vw where left(kodeorg,4)='".$idOrg."' and norefrensi='' and nobkm='' and nilaihk<>0 and umr=0";
		// $rData=fetchData($sData);
		// if(count($rData)!=0){
		// 	foreach($rData as $baris=>$val){
		// 		$sGapok="select (jumlah/25) as upah from ".$dbname.".sdm_5gajipokok where 
		// 				 idkomponen=1 and tahun='".$periode."'  and karyawanid='".$val['karyawanid']."'";
		// 		$rGapok=fetchData($sGapok);
		// 		$umrdt=$val['nilaihk']*$rGapok[0]['upah'];

		// 		$supdate="update ".$dbname.".sdm_absensidt set umr='".$umrdt."' 
		// 				  where karyawanid='".$val['karyawanid']."' and tanggal='".$val['tanggal']."' and absensi='".$val['absensi']."'";
		// 		$owlPDO->exec($supdate);
		// 	}
		// }

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_absensiht where 1=1 ".$wh." order by `tanggal` desc";
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }


        $slvhc = "select * from " . $dbname . ".sdm_absensiht where 1=1 ".$wh." order by `tanggal` desc, kodeorg asc limit " . $offset . "," . $limit . "";
		$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
        while ($rlvhc = $qlvhc->fetch()) {
            $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $rlvhc['kodeorg'] . "'";
			$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
			$qOrg->setFetchMode(PDO::FETCH_ASSOC);
            $rOrg = $qOrg->fetch();
            $sGp = "select DISTINCT sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . substr($rlvhc['kodeorg'], 0,4) . "' and `periode`='" . $rlvhc['periode'] . "'";
			$qGp=$owlPDO->query($sGp) or die(print " Gagal: ".PDOException::getMessage());
			$qGp->setFetchMode(PDO::FETCH_ASSOC);
            $rGp = $qGp->fetch();

			$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$rlvhc['updateby']."'");
			
            $no+=1;
            echo"
                <tr class=rowcontent style=min-height:33px>
                <td align=center>" . $no . "</td>
                <td>" . $rlvhc['kodeorg'] . "</td>
                <td>" . $optorg[$rlvhc['kodeorg']] . "</td>
                <td align=center>" . tanggalnormal($rlvhc['tanggal']) . "</td>
                <td align=center>" . substr(tanggalnormal($rlvhc['periode']), 1, 7) . "</td>
                 <td>" . $optNamaKaryawan[$rlvhc['updateby']] . "</td>";
				if ($rGp['sudahproses'] == 0){
					echo"<td width=30px align=center>";
					echo"<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['periode'] . "');\">";
					echo"</td>";
					echo"<td width=30px align=center>";
					echo"<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "');\" >";
					echo"</td>";
					echo"<td width=30px align=center>";
					echo"<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_absensiht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_absensiPdf',event)\">";
					echo"</td>";
					// echo"<td width=30px align=center>";
					// echo"<img src=images/uploader/dwnld8.png class=zImgBtn  title='Download' onclick=\"showuploadperkaryawan('" . $rlvhc['kodeorg'] . "','" . $rlvhc['tanggal'] . "')\">";
					// echo"</td>";
				}else{
					echo"<td width=30px align=center></td>";
					echo"<td width=30px align=center></td>";
					echo"<td width=30px align=center>";
					echo"<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_absensiht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_absensiPdf',event)\">";
					echo"</td>";
					// echo"<td width=30px align=center>";
					// echo"<img src=images/uploader/dwnld8.png class=zImgBtn  title='Download' onclick=\"showuploadperkaryawan('" . $rlvhc['kodeorg'] . "','" . $rlvhc['tanggal'] . "')\">";
					// echo"</td>";
				}
            echo"</td>
                </tr>
                ";
        }
        echo"
                <tr class=rowheader><td colspan=9 align=center>
                " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
        echo"</tbody></table>";
        break;
		
    case'delData':
	
	
		#cek apakah ada detail / tidak
		$str = "select count(*) as jumdata from " . $dbname . ".sdm_absensidt where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
			$jumdata=$bar['jumdata'];
			
			if($jumdata>0){
				exit("Warning : Harap Hapus data detail terlebih dahulu");
			}
			
        $sCek = "select posting from " . $dbname . ".sdm_absensiht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
        $rCek = $qCek->fetch();
        if ($rCek['posting'] == '1') {
            echo"warning:Already Post This Data";
            exit();
        }
        $scek = "select distinct * from " . $dbname . "";
        $sDel = "delete from " . $dbname . ".sdm_absensiht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'"; // 
		try{
			$owlPDO->exec($sDel); 
			$sDelDetail = "delete from " . $dbname . ".sdm_absensidt where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
			try{
				$owlPDO->exec($sDelDetail); 
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
        break;
		
    case'cekHeader':
        $abs = explode("###", $_POST['absnId']);
        if ($abs[0] == '') {
            exit("error: Unit code must filled");
        }
        $sCek = "select DISTINCT tanggalmulai,tanggalsampai,periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and periode='" . $periode . "' and sudahproses=0 and tanggalmulai<='" . $tgl . "' and tanggalsampai>='" . $tgl . "'";
        $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
		
		
		if ($rCek < 1) {
			echo"warning : Tanggal diluar periode aktip gaji";
            exit();
        }
		
		$sCek = "select kodeorg,tanggal from " . $dbname . ".sdm_absensiht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
        if ($rCek > 0) {
            echo"warning : This date and Organization Name already exist";
            exit();
        }


        $str = "select * from " . $dbname . ".setup_periodeakuntansi where periode='" . $periode . "' and
                kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and tutupbuku=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($res);
        if ($numrows > 0)
            $aktif = true;
        else
            $aktif = false;
        if ($aktif == true) {
            exit("Error : Accounting period has been closed");
        }
        break;
		
    case'cariAbsn':
        echo"
                <div style=overflow:auto; height:350px;>
                <table cellspacing=1 border=0 class=sortable>
                <thead>
                <tr class=rowheader>
                <td align=center>No.</td>
                <td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
                <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                <td align=center>" . $_SESSION['lang']['periode'] . "</td>
				 <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
                <td align=center>Action</td>
                </tr>
                </thead>
                <tbody>
                ";
		
        $where = "";
        if ($kdOrg != '') {
            $where.=" and kodeorg='" . $kdOrg . "'";
        }
        if ($tgl != '') {
            $bln = explode("-", $absnId[1]);

            $where.=" and tanggal='" . $bln[2] . "-" . $bln[1] . "-" . $bln[0] . "'";
        }

        $sCek = "select * from " . $dbname . ".sdm_absensiht where substr(kodeorg,1,4)='" . $_SESSION['empl']['lokasitugas'] . "' " . $where . "";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
        if ($rCek > 0) {
            $slvhc = "select * from " . $dbname . ".sdm_absensiht where substr(kodeorg,1,4)='" . $_SESSION['empl']['lokasitugas'] . "' " . $where . "  order by `tanggal` desc ";
			$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
			$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
            while ($rlvhc = $qlvhc->fetch()) {
                $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $rlvhc['kodeorg'] . "'";
				$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
				$qOrg->setFetchMode(PDO::FETCH_ASSOC);
                $rOrg = $qOrg->fetch();
                $sGp = "select DISTINCT sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $rlvhc['kodeorg'] . "' and `periode`='" . $rlvhc['periode'] . "'";
                $qGp=$owlPDO->query($sGp) or die(print " Gagal: ".PDOException::getMessage());
				$qGp->setFetchMode(PDO::FETCH_ASSOC);
				$rGp = $qGp->fetch();
                $no+=1;
				
					$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$rlvhc['updateby']."'");
				
                echo"
                <tr class=rowcontent>
                <td align=center>" . $no . "</td>
				 <td>" . $rlvhc['kodeorg'] . " -> " . $optorg[$rlvhc['kodeorg']] . "</td>
                <td>" . tanggalnormal($rlvhc['tanggal']) . "</td>
                <td>" . substr(tanggalnormal($rlvhc['periode']), 1, 7) . "</td>
                 <td>" . $optNamaKaryawan[$rlvhc['updateby']] . "</td>
                <td>";
               if ($rGp['sudahproses'] == 0){
				echo"<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['periode'] . "');\">
                <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "');\" >	
                <img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_absensiht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_absensiPdf',event)\">";
               }else{
				   	echo"<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_absensiht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_absensiPdf',event)\">";           
			   }
                echo"</td>
                </tr>
                ";
				
				/*
				 if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
                    echo"<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['periode'] . "');\">
                    <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "');\" >	
                    <img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_absensiht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_absensiPdf',event)\">";
                }
                 else if ($rGp['sudahproses'] == 0 and $rlvhc['updateby']==$_SESSION['standard']['userid']) {
                    echo"<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['periode'] . "');\">
                <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "');\" >	
                <img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_absensiht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_absensiPdf',event)\">";
                } else {
                    echo"<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_absensiht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_absensiPdf',event)\">";
                }
                echo"</td>
                </tr>
                ";
				*/
				
            }

            echo"</tbody></table></div>";
        } else {
            echo"<tr class=rowcontent><td colspan=5 align=center>Not Found</td></tr></tbody></table></div>";
        }
        break;
    case'updateData':
        if ($_POST['premi'] == '') {
            $_POST['premi'] = 0;
        }

        if ($_POST['insentif'] == '') {
            $_POST['insentif'] = 0;
        }
        
		if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){		
			if((substr($param['noakun'],0,1)!='7' and substr($param['noakun'],0,1)!='8') and substr($param['noakun'],0,1)!='4') {
				exit("Warning : Alokasi wajib diisi.\nNama Karyawan : ".$nmkarya[$krywnId]."\nNomor Akun : ".$param['noakun']."\nAlokasi : ".$param['alokasi']);
			}
			
			if((substr($param['noakun'],0,1)=='7' or substr($param['noakun'],0,1)=='8') and $param['alokasi']!=''){
				exit("Warning : Kosongkan Alokasi.\nNama Karyawan : ".$nmkarya[$krywnId]."\nNomor Akun : ".$param['noakun']."\nAlokasi : ".$param['alokasi']);
			}
		}
		
        if ($kdOrg == '') {
            exit("error:Unit code must filled");
        }      
        $sdtCek = "select distinct * from " . $dbname . ".kebun_kehadiran_vw 
                                 where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
		$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
		$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
		$rDtCek=owlBaris($qDtCek);
        $rSource = $qDtCek->fetch();

		## Dikomen karena bisa input premi dari absensi nya
        // if ($rDtCek > 0 and $jmlHk>0) {
        //     exit("error: Employee registered on transaction : " . $rSource['notransaksi']);
        // }
		
		
		#Cek apakah di sdm_5absensi upload file bersifat Mandatory atau tidak
		$pecahtanggalFile = explode("-",$tgl);
		$tanggalFilenya = $pecahtanggalFile[0].$pecahtanggalFile[1].$pecahtanggalFile[2];
		$notransaksiUploadFile = $tanggalFilenya.$krywnId;
		$where = "notransaksi='".$notransaksiUploadFile."' ";
		$qCekListFileUpload = selectQuery($dbname, "listfileupload", "*", $where);
		$resValidasiDokumen = fetchData($qCekListFileUpload);
				
		if(count($resValidasiDokumen) <= 0) {
			// exit("Warning : Data belum di upload");

			# Convert kodenya ke nama kehadiran
			$where = "kodeabsen='".$asbensiId."' and validasidokumen = 1 ";
			$conKehadiran = makeOption($dbname,"sdm_5absensi","kodeabsen,keterangan",$where);

			# Buat html
			$bgHtmlnya = "style=color:red;font-weight:bold;";
			$bgHtmlnya2 = "style=color:red;font-size:12px;margin-top:20px;";
			$teksHtml2 = "<p ".$bgHtmlnya2.">Noted : Untuk mengatur Validasi Dokumen bisa di lihat di Menu (SETUP > ABSENSI)</p>";

			# Query cek apakah Validasi dari setup Absensi Aktif atau Tidak Aktif
			$qCekUploadDokumen = selectQuery($dbname, "sdm_5absensi", "*", $where);
			$resValidasiDokumen = fetchData($qCekUploadDokumen);
			if(count($resValidasiDokumen) > 0) {
				exit("Warning : File Upload Dokumen Kosong, Jenis Absensi : <span ".$bgHtmlnya.">".$conKehadiran[$asbensiId]."</span>, Validasi Dokumennya Aktif <br/> ".$teksHtml2." ");
			}
		}
		
		
		
		#validasi maksimal HK BHL
		$str = "select sum(hk) as hk from ".$dbname.".sdm_absensidt where kodeorg='".$kdOrg."' and tanggal='".$tgl."' and karyawanid='".$krywnId."'";
		$res = fetchdata($str);
		$hkold = $res[0]['hk'];

		cekmaxnilaihk($krywnId,tanggalsystemn(tanggalnormal($tgl)),$jmlHk,$hkold,'edit',$exit='0');
		
				
		$jumbkm=0;
		$nobkm='';
		$str = "select * from ".$dbname.".kebun_aktifitas where tanggal='" . $tgl . "' and
				(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jumbkm+=1;
			$nobkm.=$bar['notransaksi'].', ';
		}
			
		if($jumbkm>0 and $jmlHk>0){
			exit("Warning:Sudah terdaftar dibkm (sebagai mandor/mandor1/kerani) \n No.BKM : ".$nobkm." ");
		}
		
		
		$sdtCek = "select distinct * from " . $dbname . ".kebun_prestasi_vw 
		where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
		$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
		$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
		$rDtCek=owlBaris($qDtCek);
        $rSource = $qDtCek->fetch();
        if ($rDtCek > 0 and $jmlHk>0) {
            exit("error: Employee registered on transaction : " . $rSource['notransaksi']);
        }
		
        $sUpd = "update " . $dbname . ".sdm_absensidt set shift='" . $shifTid . "',absensi='" . $asbensiId . "',jam='" . $Jam . "', jamPlg='" . $Jam2 . "',jamistirahatdari='" . $Jam3 . "',jamistirahatsampai='" . $Jam4 . "',penjelasan='" . $ket . "', catu=" . $catu . ",penaltykehadiran=" . $penaltykehadiran . " ,`premi` ='" . $_POST['premidt'] . "',`insentif`='$insentif',`hk`='".$jmlHk."',`umr`='".$umr."',`insentiflibur`='".$insentiflibur."', noakun='".$param['noakun']."', alokasi='".$param['alokasi']."', kegiatan='".$param['kodekegiatan']."'
		where kodeorg='" . $kdOrg . "' and tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
        try{
			$owlPDO->exec($sUpd); 
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
        break;
		
    case'delDetail':
        $dh = "idkaryawan='" . $krywnId . "' and tanggal='" . $tgl . "'";
        $optKar = makeOption($dbname, 'vhc_runhk', 'idkaryawan,upah', $dh);
        $optnotran = makeOption($dbname, 'vhc_runhk', 'idkaryawan,notransaksi', $dh);
        if ($optKar[$krywnId] != '') {
            //exit("error: Tidak dapat menghapus data, karena ada absensi dari traksi " . $optnotran[$krywnId] . " ");
        }
        $sDelDetail = "delete from " . $dbname . ".sdm_absensidt where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "' and karyawanid='" . $krywnId . "'";
		try{
			$owlPDO->exec($sDelDetail); 
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
        break;
		
    case'getPremi':
        $insentif = 0;
        $premi = 0;
        $premitetap = 0;
        $where = "karyawanid='" . $_POST['karyId'] . "'";
        $statPremi = makeOption($dbname, 'datakaryawan', 'karyawanid,statpremi', $where);
        if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
            exit();
        }
        if ($statPremi[$_POST['karyId']] == 0) {
            //exit("error:".$_POST['absnId']);
            $tgl = explode("-", $_POST['tglDt']);
            $periode = $tgl[2] . "-" . $tgl[1];
            $isi = $tgl[2] . "-" . $tgl[1] . "-" . $tgl[0];


            if ($_POST['jamPlg'] == '00:00') {
                $_POST['jmMulai'] = "00:00";
            }
            $jm1 = explode(":", $_POST['jmMulai']);
            $jm2 = explode(":", $_POST['jamPlg']);

            $dtTmbh = 0;
            if ($jm2 < $jm1) {
                $dtTmbh = 1;
            }
            $qwe = date('D', strtotime($isi));
            //exit("error: ".$qwe);
            $wktmsk = mktime(intval($jm1[0]), intval($jm1[1]), 0, intval(substr($_POST['tglDt'], 3, 2)), intval(substr($_POST['tglDt'], 0, 2)), substr($_POST['tglDt'], 6, 4));
            $wktplg = mktime(intval($jm2[0]), intval($jm2[1]), 0, intval(substr($_POST['tglDt'], 3, 2)), intval(substr($_POST['tglDt'], 0, 2) + $dtTmbh), substr($_POST['tglDt'], 6, 4));
            $slsihwaktu = $wktplg - $wktmsk;
            $sisa = $slsihwaktu % 86400;
            $jumlah_jam = floor($sisa / 3600);
            if (($_POST['absnId'] == 'H') || ($_POST['absnId'] == 'AS')) {

                $spremi = "select distinct premitetap from " . $dbname . ".sdm_5premitetap 
                                             where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' 
                                             and kodejabatan='" . $kdJbtn[$_POST['karyId']] . "'";
				$qpremi=$owlPDO->query($spremi) or die(print " Gagal: ".PDOException::getMessage());
				$qpremi->setFetchMode(PDO::FETCH_ASSOC);
                $rpremi = $qpremi->fetch();

                //exit("error:".$jumlah_jam);
                $scek = "select distinct sum(premi) as premi from " . $dbname . ".sdm_absensidt 
                                           where karyawanid='" . $_POST['karyId'] . "' and tanggal like '" . $periode . "%'";
                $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
				$qcek->setFetchMode(PDO::FETCH_ASSOC);
				$rcek = $qcek->fetch();
                if ($qwe == 'Sat') {
                    $premi = 0;
                    if ($jumlah_jam >= 5) {
                        @$premi = $rpremi['premitetap'] / 25;
                        // }
                    } else {
                        $premi = 0;
                    }
                } else {
                    if ($jumlah_jam >= 7) {
                        @$premi = $rpremi['premitetap'] / 25;
                    } else {
                        $premi = 0;
                    }
                }
            }

            if (($_POST['absnId'] == 'HL') || ($_POST['absnId'] == 'L') || ($_POST['absnId'] == 'MG')) {

                $premi = 0;
                $spremi = "select distinct insentif from " . $dbname . ".sdm_5insentif 
                                             where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' 
                                             and tipekaryawan='" . $tipeKary[$_POST['karyId']] . "'";
                $qpremi=$owlPDO->query($spremi) or die(print " Gagal: ".PDOException::getMessage());
				$qpremi->setFetchMode(PDO::FETCH_ASSOC);
				$rpremi = $qpremi->fetch();
                if ($jumlah_jam >= 3) {
                    $insentif = $rpremi['insentif'];
                } elseif (($jumlah_jam < 3) || ($jumlah_jam > 1)) {
                    @$insentif = $rpremi['insentif'] / 2;
                }
                //exit("error:masuk".$insentif);
            }
            $premitetap = $premi + $insentif;
            echo $premitetap . "####" . $insentif . "####" . $premi;
        }
        break;
		case 'showuploadperkaryawan':
			$tab = "";
			$tab .= "<fieldset>";
				$tab .= "<table width=100% border=0 cellpadding=5 cellspacing=0 class=sortable>";
					$tab .= "<tr>";
						$tab .= "<th>No</th>";
						$tab .= "<th>Nama Karyawan</th>";
						$tab .= "<th>Kehadiran</th>";
						$tab .= "<th>Jenis Kegiatan</th>";
						$tab .= "<th>Alokasi</th>";
						$tab .= "<th>Hari Kerja</th>";
						$tab .= "<th>Premi</th>";
						$tab .= "<th>Extra Fooding</th>";
						$tab .= "<th>Denda Harian</th>";
						$tab .= "<th>Keterangan</th>";
						$tab .= "<th>File Type</th>";
						$tab .= "<th>Nama File</th>";
						$tab .= "<th>Action</th>";
					$tab .= "</tr>";

			// Ambil data filenya 
			// $sFile = selectQuery($dbname, "listfileupload", "*", "kriteriaefil='ABSEN' and status='1'");
			// $resFile = fetchData($sFile);

			// foreach($resFile as $brs=>$result) {
			// 	$filenya[$result['notransaksi']][$brs] = $result['namafile'];
			// 	$icon[$result['notransaksi']][$brs]=seticonfile($result['formaticon']);
			// }
			
			// echo "<pre>";
			// print_r($icon);
			// echo "</pre>";

			// Query Tampilkan Karyawan
			$sqlKaryawan = "SELECT * from ".$dbname.".sdm_absensidt WHERE kodeorg='".$param['kodeorgnya']."' and tanggal='".$param['tanggal']."'";
			$res = $owlPDO->query($sqlKaryawan) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			// exit("warning".print_r($res));
			$noKar=0;
			while($resKaryawanDt = $res->fetch()) {
				$nmkaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$resKaryawanDt->karyawanid."'");
				$nmakun = makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$resKaryawanDt->noakun."'");
				$pecahtanggal = explode("-",$resKaryawanDt->tanggal);
				$tanggalnya = $pecahtanggal[0].$pecahtanggal[1].$pecahtanggal[2];
				$notransaksi = $tanggalnya.$resKaryawanDt->karyawanid;
				// echo "<pre>";
				// print_r($filenya[$notransaksi]);
				// echo "</pre>";

				$sFile = selectQuery($dbname, "listfileupload", "*", "notransaksi='".$notransaksi."' and kriteriaefil='ABSEN' and status='1'");
				$resFile = fetchData($sFile);

				foreach($resFile as $key => $val) {
					$pathDownload = "fileupload/dtkaryawanabsen/";
					$noKar+=1;
					$tab .= "<tr class=rowcontent>";
						$tab .= "<td align=right>".$noKar."</td>";
						$tab .= "<td align=left>".$nmkaryawan[$resKaryawanDt->karyawanid]."</td>";
						$tab .= "<td align=center>".$resKaryawanDt->absensi."</td>";
						$tab .= "<td align=left>".$nmakun[$resKaryawanDt->noakun]."</td>";
						$tab .= "<td align=left>".$resKaryawanDt->alokasi."</td>";
						$tab .= "<td align=right>".$resKaryawanDt->hk."</td>";
						$tab .= "<td align=right>".$resKaryawanDt->premi."</td>";
						$tab .= "<td align=right>".$resKaryawanDt->tunjangan."</td>";
						$tab .= "<td align=right>".$resKaryawanDt->penaltykehadiran."</td>";
						$tab .= "<td align=left>".$resKaryawanDt->penjelasan."</td>";
						
						$icon=seticonfile($val['formaticon']);
						$tab.="<td style='text-align:center'>
								<a href='".$pathDownload.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
							</td>";
						$tab .= "<td align=left>".$val['namafile']."</td>";
						$tab .= "<td align=center><a href='".$pathDownload.$val['namafile']."'><img src=images/uploader/dwnld8.png class=zImgBtn  title='Download'></a></td>";
					
					$tab .= "</tr>";
				}
			}
			
				$tab .= "</table>";
			$tab .= "</fieldset>";
	
			echo $tab;
		break;

		# NOTE :
		# Case Ini menjalankan ketika submit file melakukan onclick / simpan data langsung ke kedua data
		# untuk onclicknya bisa search di file sdm_slave_absensi_detail.php dengan kata kunci "onclick=\"submitfile('".$param['tanggal'].$param['karyawanid']."'); addDetailUploadAll();\""
		# Jadi kenapa di bikin case baru ini, kenapa tidak pakai case 'cekData' => di khususkan / di bedakan 2 function untuk insert datanya
		# CekData => ketika simpan di tombol floppy disk, akan ada pengecekan apakah jenis kehadiran wajib melakukan input files atau tidak
		# CekDataUploadAll => ketika terbuka popup alertify, dan memilih filenya dan lakukan submit maka otomatis akan melakukan insert dengan kriteria, kuncian yang sama dengan case CekData
		# dan melakukan insert ke 2 tabel sekaligus yaitu File Uploadnya dan Absensi Dtnya

		case 'cekDataUploadAll':
			if ($kdOrg == '') {
				exit("error: Unit code must filled");
			}
			
			if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){			
				if((substr($param['noakun'],0,1)!='7' and substr($param['noakun'],0,1)!='8') and substr($param['noakun'],0,1)!='4') {
					exit("Warning : Alokasi wajib diisi.\nNama Karyawan : ".$nmkarya[$krywnId]."\nNomor Akun : ".$param['noakun']."\nAlokasi : ".$param['alokasi']);
				}
				
				if((substr($param['noakun'],0,1)=='7' or substr($param['noakun'],0,1)=='8') and $param['alokasi']!=''){
					exit("Warning : Kosongkan Alokasi.\nNama Karyawan : ".$nmkarya[$krywnId]."\nNomor Akun : ".$param['noakun']."\nAlokasi : ".$param['alokasi']);
				}
			}		
			
			#query pengecekan apakah FP aktif / tidak
			$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".substr($kdOrg,0,4)."' and tanggal<='".tanggalsystemn(tanggalnormal($tgl))."'";
			$res = fetchData($str);
			$statusfp    = $res[0]['status'];//1 aktif,0 tidak
			$tipevalidasi= $res[0]['tipevalidasi'];
			$detailexp   = explode(",",$res[0]['detailvalidasi']);
			foreach($detailexp as $vald){
				$detval[$vald]=$vald;
			}
			$arrUpload = array();
			if($statusfp==1){
				if($asbensiId=='H'){
					$arrUpload[]['nik'] = $krywnId;
					validasifp($tipevalidasi,$detval,'SDM',$arrUpload,tanggalsystemn(tanggalnormal($tgl)),'1');
					
					/* $str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$krywnId."' and tanggalabsen='".$tgl."' limit 1";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
					if($krywnId != $bar['karyawanid']){
						$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$krywnId."'");
						$optNik = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$krywnId."'");
						echo "Warning : Absen fingerprint untuk karyawan dg NIK : <br>".$optNik[$krywnId]." = ".$optNamaKaryawan[$krywnId]."<br>belum ada.<br>Silahkan lakukan proses melalui menu : SDM - Proses - Fingerprint.";
						exit;
					} */		
				}
			}
			
			#Cek apakah di sdm_5absensi upload file bersifat Mandatory atau tidak
			$pecahtanggalFile = explode("-",tanggalsystem($absnId[1]));
			$tanggalFilenya = $pecahtanggalFile[0].$pecahtanggalFile[1].$pecahtanggalFile[2];
			$notransaksiUploadFile = $tanggalFilenya.$krywnId;
			$where = "notransaksi='".$notransaksiUploadFile."' ";
			$qCekListFileUpload = selectQuery($dbname, "listfileupload", "*", $where);
			$resValidasiDokumen = fetchData($qCekListFileUpload);
	
			
			// if(count($resValidasiDokumen) <= 0) {
			// 	// exit("Warning : Data belum di upload");
	
			// 	# Convert kodenya ke nama kehadiran
			// 	$where = "kodeabsen='".$asbensiId."' and validasidokumen = 1 ";
			// 	$conKehadiran = makeOption($dbname,"sdm_5absensi","kodeabsen,keterangan",$where);
	
			// 	# Buat html
			// 	$bgHtmlnya = "style=color:red;font-weight:bold;";
			// 	$bgHtmlnya2 = "style=color:red;font-size:12px;margin-top:20px;";
			// 	$teksHtml2 = "<p ".$bgHtmlnya2.">Noted : Untuk mengatur Validasi Dokumen bisa di lihat di Menu (SETUP > ABSENSI)</p>";
	
			// 	# Query cek apakah Validasi dari setup Absensi Aktif atau Tidak Aktif
			// 	$qCekUploadDokumen = selectQuery($dbname, "sdm_5absensi", "*", $where);
			// 	$resValidasiDokumen = fetchData($qCekUploadDokumen);
			// 	if(count($resValidasiDokumen) > 0) {
			// 		exit("Warning : File Upload Dokumen Kosong, Jenis Absensi : <span ".$bgHtmlnya.">".$conKehadiran[$asbensiId]."</span>, Validasi Dokumennya Aktif <br/> ".$teksHtml2." ");
			// 	}
			// }	
	
			// exit('warning');
	
			// $where = "kodeabsen='".$asbensiId."' and validasidokumen = 1 ";
			// $qCekUploadDokumen = selectQuery($dbname, "sdm_5absensi", "*", $where);
			// $resValidasiDokumen = fetchData($qCekUploadDokumen);
			// if(count($resValidasiDokumen) > 0) {
			// 	exit('Warning : File Upload Dokumen Wajib Diisi Jenis Absen ini !!!');
			// }
			
			#Ambil UMR
			//$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kdOrg."'");
			// if($tipeorg[$kdOrg]=='HOLDING'){
			// 	$tablename='sdm_5gajipokokho';
			// }else{
			// 	$tablename='sdm_5gajipokok';
			// }

			$tablename='sdm_5gajipokok';
	
			$str = "select * from ".$dbname.".".$tablename." where karyawanid='".$krywnId."' and tahun='".$periode."' and idkomponen='1'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$jumlahumr+=$bar['jumlah'];

			$tipekaryawan_val = getKary($krywnId,'tipekaryawan');
			if($tipekaryawan_val != 0){
				if($jumlahumr==''){
					exit("Warning : Gaji Pokok untuk tahun ".$periode." belum ada !");
				}
			}
			
			$sCek = "select DISTINCT tanggalmulai,tanggalsampai,periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and sudahproses=0 and tanggalmulai<='" . $tgl . "' and tanggalsampai>='" . $tgl . "'";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$qCek->setFetchMode(PDO::FETCH_OBJ);
			$rCek=owlBaris($qCek);
			if ($rCek > 0) {
				
				// Kebun Aktifitas Cek tidak bisa menginput ketika sudah di input
				$str = "select count(*) as datakbm from ".$dbname.".kebun_aktifitas where tanggal='".$tgl."' and 
				(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				$datakbm=$bar['datakbm'];
	
				$strData = "select * from ".$dbname.".kebun_aktifitas where tanggal='".$tgl."' and 
				(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
				$resData=$owlPDO->query($strData) or die(print " Gagal: ".PDOException::getMessage());
				$resData->setFetchMode(PDO::FETCH_ASSOC);
				$barDt=$resData->fetch();
	
	
				// exit("warning".print_r($barDt));
				// exit("warning".print_r($jmlHk));
				if($datakbm > 0 and $jmlHk > 0) {
					exit("Warning : Data sudah terdaftar di BKM, dengan<b> No Transaksi : ".$barDt['notransaksi'])."</b>";
				}
	
				// CEK DATA PANEN SPB
	
				$str = "select count(*) as datapnn from ".$dbname.".kebun_prestasi_vw where tanggal='".$tgl."' and 
				(nikmandor='".$krywnId."' or karyawanid='".$krywnId."' or kerani='".$krywnId."')";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				$datapnn=$bar['datapnn'];
	
				$strData = "select * from ".$dbname.".kebun_prestasi_vw where tanggal='".$tgl."' and 
				(nikmandor='".$krywnId."' or karyawanid='".$krywnId."' or kerani='".$krywnId."')";
				$resData=$owlPDO->query($strData) or die(print " Gagal: ".PDOException::getMessage());
				$resData->setFetchMode(PDO::FETCH_ASSOC);
				$barDtpnn=$resData->fetch();
	
	
				// exit("warning".print_r($barDt));
				// exit("warning".print_r($jmlHk));
				if($datapnn > 0 and $jmlHk > 0) {
					exit("Warning : Data sudah terdaftar di Kegiatan Panen, dengan<b> No Transaksi : ".$barDtpnn['notransaksi'])."</b>";
				}
	
				
				$sCek = "select kodeorg,tanggal from " . $dbname . ".sdm_absensiht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
				$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
				$qCek->setFetchMode(PDO::FETCH_OBJ);
				$rCek=owlBaris($qCek);
				#cek premi
				#jika status di datakaryawawan apakah menerima premi dan insentif 
				#dan apakah sudah melebhi dari setup premi, jika lebih atau sama dengan maka premi=0
	 
				
	
				if ($rCek < 1) {
					$sIns = "insert into " . $dbname . ".sdm_absensiht (`kodeorg`,`tanggal`,`periode`,`updateby`) values
						('" . $kdOrg . "','" . $tgl . "','" . $periode . "','" . $_SESSION['standard']['userid'] . "')"; //echo"warning:".$sIns;
					try{
						$owlPDO->exec($sIns); 
						if ($_POST['premi'] == '') {
							$_POST['premi'] = 0;
						}
						if ($_POST['insentif'] == '') {
							$_POST['insentif'] = 0;
						}
						if ($_POST['premidt'] == '') {
							$_POST['premi'] = 0;
						}
						
						$sdtCek = "select distinct * from " . $dbname . ".kebun_kehadiran_vw where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
						$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
						$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
						$rDtCek=owlBaris($qDtCek);
						$rSource = $qDtCek->fetch();
						if ($rDtCek > 0 and $jmlHk>0) {
							exit("error: Employee registered on transaction : " . $rSource['notransaksi']);
						}
						
						$sdtCek = "select distinct * from " . $dbname . ".kebun_prestasi_vw 
									 where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
						$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
						$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
						$rDtCek=owlBaris($qDtCek);
						$rSource = $qDtCek->fetch();
						if ($rDtCek > 0 and $jmlHk>0) {
							exit("error: Employee registered on transaction : " . $rSource['notransaksi']);
						}
						
						#cek apakah sudah pernah di-input
						$str = "select count(*) as jumdata from ".$dbname.".sdm_absensidt_vw where tanggal='" . $tgl . "' and karyawanid='".$krywnId."'  ";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar=$res->fetch();
						$jumdata=$bar['jumdata'];
						
						if($jumdata>0 and $jmlHk>0){
							exit("Warning : Sudah ada data");
						}
	
						
						#cek kebun aktifitas, jika hk>0 maka err,
						#absen sudah ada dari db tsb, bik khl/kht,
						#diperboolehkan input premi saja dari absensi dengan hk 0
						
						$str = "select count(*) as jumbkm from ".$dbname.".kebun_aktifitas where tanggal='" . $tgl . "' and
								(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar=$res->fetch();
						$jumbkm=$bar['jumbkm'];
						
						if($jumbkm>0 and $jmlHk>0){
							exit("Warning : Sudah terdaftar di BKM (sebagai mandor/mandor1/kerani)");
						}
						
						$tipekary=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',"karyawanid='".$krywnId."'");
						# Cek baru
						$str = "select * from ".$dbname.".vhc_runhk_vw where tanggal='" . $tgl . "' and idkaryawan='".$krywnId."'";
						$resnew=fetchdata($str);

						$str = "select sum(upah) as jumvhc from ".$dbname.".vhc_runhk_vw where tanggal='" . $tgl . "' and idkaryawan='".$krywnId."'";
						$res=fetchdata($str);
						$jumvhc=$res[0]['jumvhc'];
						if($tipekary[$krywnId]!='4'){
							if(count($resnew)>0 and $jmlHk>0){
								if($_SESSION['standard']['username']=='tim.owl3') {
									echo "<pre>";
									print_r($res);
									print_r($jmlHk);
								}

								exit("Warning : Sudah terdaftar ditraksi pekerjaan sebagai operator x6");
							}
						}else{						
							if($jumvhc>0 and $jmlHk>0){
								if($_SESSION['standard']['username']=='tim.owl3') {
									echo "<pre>";
									print_r($jumvhc);
									print_r($jmlHk);
								}

								exit("Warning : Sudah terdaftar ditraksi pekerjaan sebagai operator x7");
							}
						}
	
						#penguncian hari minggu hanya boleh masuk MG dan hari libur HL ngunci ke tanggal dan setup hari libur
						
						$day = date('D', strtotime($tgl));
						if($day=='Sun'){
							if($asbensiId!='MG' and $tipekary[$krywnId]!='4'){
								#exit("Warning: Absen hanya boleh 'hari minggu' dan HK harus 0, nilai hk di-isikan di premi");
							}
						}					
	
						$sDetIns = "insert into " . $dbname . ".sdm_absensidt (`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`, `jamistirahatdari`, `jamistirahatsampai`,`penjelasan`,`catu`,`penaltykehadiran`,`premi`,`insentif`,`hk`,`umr`, `insentiflibur`,`noakun`,`alokasi`,`kegiatan`) 
						 values ('" . $kdOrg . "','" . $tgl . "','" . $krywnId . "','" . $shifTid . "','" . $asbensiId . "','" . $Jam . "','" . $Jam2 . "','" . $Jam3 . "','" . $Jam4 . "','" . $ket . "'," . $catu . "," . $penaltykehadiran . "," . $_POST['premidt'] . "," . $_POST['insentif'] . ",'".$jmlHk."','".$umr."','".$insentiflibur."','".$param['noakun']."','".$param['alokasi']."','".$param['kodekegiatan']."')";
						try{
							$owlPDO->exec($sDetIns); 
						}catch (PDOException $e){
							echo "DB Error : " . $e->getMessage();
							die();
						}
					}catch (PDOException $e){
						echo "DB Error : " . $e->getMessage();
						die();
					}
				} else {
					
						//exit("Error:$umr");
						if ($_POST['premi'] == '') {
							$_POST['premi'] = 0;
						}
						if ($_POST['insentif'] == '') {
							$_POST['insentif'] = 0;
						}
						if ($_POST['premidt'] == '') {
							$_POST['premidt'] = 0;
						}
	
						$sdtCek = "select distinct * from " . $dbname . ".kebun_kehadiran_vw where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
						$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
						$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
						$rDtCek=owlBaris($qDtCek);
						$rSource = $qDtCek->fetch();
						if ($rDtCek > 0 and $jmlHk>0) {
							exit("Warning: Employee registered on transaction : " . $rSource['notransaksi']);
						}
						
						$sdtCek = "select distinct * from " . $dbname . ".kebun_prestasi_vw 
										 where tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
						$qDtCek=$owlPDO->query($sdtCek) or die(print " Gagal: ".PDOException::getMessage());
						$qDtCek->setFetchMode(PDO::FETCH_ASSOC);
						$rDtCek=owlBaris($qDtCek);
						$rSource = $qDtCek->fetch();
						if ($rDtCek > 0 and $jmlHk>0) {
							exit("Warning: Employee registered on transaction : " . $rSource['notransaksi']);
						}
					
						#cek apakah sudah pernah di-input
						$str = "select count(*) as jumdata from ".$dbname.".sdm_absensidt_vw where tanggal='" . $tgl . "' and karyawanid='".$krywnId."'  ";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar=$res->fetch();
						$jumdata=$bar['jumdata'];
						
						if($jumdata>0 and $jmlHk>0){
							exit("Warning:Sudah ada data");
						}
						
						$tipekary=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',"karyawanid='".$krywnId."'");
						# Cek baru
						$str = "select * from ".$dbname.".vhc_runhk_vw where tanggal='" . $tgl . "' and idkaryawan='".$krywnId."'";
						$resnew=fetchdata($str);

						#cek absensi dari vhc
						$str = "select sum(upah) as jumvhc from ".$dbname.".vhc_runhk_vw where tanggal='" . $tgl . "' and idkaryawan='".$krywnId."'";
						$res=fetchdata($str);
						$jumvhc=$res[0]['jumvhc'];
						if($tipekary[$krywnId]!='4'){
							if(count($resnew)>0 and $jmlHk>0){
								exit("Warning : Sudah terdaftar ditraksi pekerjaan sebagai operator.");
							}
						}else{						
							if($jumvhc>0 and $jmlHk>0){
								exit("Warning : Sudah terdaftar ditraksi pekerjaan sebagai operator");
							}
						}
					
						
						#cek kebun aktifitas, jika hk>0 maka err,
						#absen sudah ada dari db tsb, bik khl/kht,
						#diperboolehkan input premi saja dari absensi dengan hk 0
						$jumbkm=0;
						$nobkm='';
						$str = "select * from ".$dbname.".kebun_aktifitas where tanggal='" . $tgl . "' and
								(nikmandor='".$krywnId."' or nikmandor1='".$krywnId."' or keranimuat='".$krywnId."')";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						while($bar=$res->fetch()){
							$jumbkm+=1;
							$nobkm.=$bar['notransaksi'].', ';
						}
							
						if($jumbkm>0 and $jmlHk>0){
							exit("Warning:Sudah terdaftar dibkm (sebagai mandor/mandor1/kerani) \n No.BKM : ".$nobkm." ");
						}
			
						#penguncian hari minggu hanya boleh masuk MG dan hari libur HL ngunci ke tanggal dan setup hari libur
						$day = date('D', strtotime($tgl));
						if($day=='Sun'){
							if($asbensiId!='MG' and $tipekary[$krywnId]!='4'){
								#exit("Warning:Absen hanya boleh 'hari minggu' dan HK harus 0, nilai hk di-isikan di premi");
								#ksbw ada masuk minggu kalau disini MG transportnya gak masuk
							}
						}
				
				//exit("Error:$umr");
						$sDetIns = "insert into " . $dbname . ".sdm_absensidt
						(`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`,`jamistirahatdari`,`jamistirahatsampai`, `penjelasan`,`catu`,`penaltykehadiran`,`premi`,`insentif`,`hk`,`umr`,`insentiflibur`,`noakun`,`alokasi`,`kegiatan`) 
						values ('" . $kdOrg . "','" . $tgl . "','" . $krywnId . "','" . $shifTid . "','" . $asbensiId . "','" . $Jam . "','" . $Jam2 . "','" . $Jam3 . "','" . $Jam4 . "','" . $ket . "'," . $catu . "," . $penaltykehadiran . "," . $_POST['premidt'] . "," . $_POST['insentif'] . ",'".$jmlHk."','".$umr."','".$insentiflibur."','".$param['noakun']."','".$param['alokasi']."','".$param['kodekegiatan']."')";
					//exit('warning'.$sDetIns); 
					try{
						$owlPDO->exec($sDetIns); 
					}catch (PDOException $e){
						echo "DB Error : " . $e->getMessage();
						die();
					}
				}
			} else {
				echo"warning:Date out of payment period";
				exit();
			}
			break;
}
?>