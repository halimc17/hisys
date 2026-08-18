<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = $_GET['proses'];
$param = $_POST;

// Get Kegiatan
$qParam = "SELECT nilai FROM $dbname.setup_parameterappl a
	LEFT JOIN ".$dbname.".setup_kegiatan b ON a.nilai = b.kodekegiatan
	WHERE a.kodeaplikasi in ('SS','TN') and b.satuan = 'POKOK'";
$resParam = fetchData($qParam);
$optKeg = array();
foreach($resParam as $row) {
	$optKeg[] = $row['nilai'];
}
$optIP = array('I'=>'Inti','P'=>'Plasma');

// Get Data
$qPres = "SELECT SUM(hasilkerja) as bibit,
		a.kodeorg,a.kodekegiatan,f.namakegiatan,b.intiplasma,e.hargasatuan,f.noakun
	FROM ".$dbname.".kebun_prestasi a
	LEFT JOIN ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg
	LEFT JOIN ".$dbname.".kebun_aktifitas c on a.notransaksi = c.notransaksi
	LEFT JOIN ".$dbname.".bgt_regional_assignment d on substr(a.kodeorg,1,4) = d.kodeunit
	LEFT JOIN ".$dbname.".kebun_5hargabibit e on d.regional = e.regional and b.intiplasma = e.status and e.periode = substr(c.tanggal,1,7)
	LEFT JOIN ".$dbname.".setup_kegiatan f on a.kodekegiatan = f.kodekegiatan
	WHERE c.tanggal like '".$param['periode']."%' and a.kodeorg like '".$param['kebun']."%'
	and a.kodekegiatan in ('".implode("','",$optKeg)."')
	GROUP BY a.kodeorg, a.kodekegiatan";
$resPres = fetchData($qPres);

switch($proses) {
	case 'preview':
		// Table
		
		$tab = "<button id='processBtn' class=mybutton onclick='process()'>".
		$_SESSION['lang']['proses']."</button>";
		$tab .= "<input id='kebunHid' type=hidden value='".$param['kebun']."'>";
		$tab .= "<input id='periodeHid' type=hidden value='".$param['periode']."'>";
		$tab .= "<input id='sumberHid' type=hidden value='".$param['sumber']."'>";
		$tab .= "<table class=sortable border=0 cellspacing=1 cellpadding=2>";
		$tab .= "<thead><tr class=rowheader>";
		$tab .= "<td>".$_SESSION['lang']['kodeblok']."</td>";
		$tab .= "<td>".$_SESSION['lang']['kegiatan']."</td>";
		$tab .= "<td>".$_SESSION['lang']['intiplasma']."</td>";
		$tab .= "<td>".$_SESSION['lang']['jumlah']."</td>";
		$tab .= "<td>".$_SESSION['lang']['hargasatuan']."</td>";
		$tab .= "<td>".$_SESSION['lang']['totalharga']."</td>";
		$tab .= "</tr></thead><tbody>";
		foreach($resPres as $row) {
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td>".$row['kodeorg']."</td>";
			$tab .= "<td>".$row['namakegiatan']."</td>";
			$tab .= "<td>".$optIP[$row['intiplasma']]."</td>";
			$tab .= "<td align=right>".$row['bibit']."</td>";
			$tab .= "<td align=right>".number_format($row['hargasatuan'],2)."</td>";
			$tab .= "<td align=right>".number_format($row['hargasatuan'] * $row['bibit'],2)."</td>";
			$tab .= "</tr>";
		}
		$tab .= "</tbody></table>";
		
		echo "<fieldset style=float:left><legend>Preview</legend>".$tab."</fieldset>";
		break;
	case 'proses':
		include_once('lib/zJournal.php');
		// Var
		$kodeJurnal = 'ALB';
		$tmpPeriod = explode('-',$param['periode']);
		$tgl = cal_days_in_month(CAL_GREGORIAN,$tmpPeriod[1],$tmpPeriod[0]);
		$tglFull = $param['periode'].'-'.$tgl;
		$unitSumber = substr($param['sumber'],0,4);
		
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
		
		// Init Class
		$zJ = new zJournal();
		
		// Get Parameter Jurnal
		$paramJ = $zJ->getParam($_SESSION['org']['induk'],'ALK',$kodeJurnal);
		
		/**
		 * Validasi
		 */
		// Cek apakah Unit yang sama
		$sameUnit = false;
		if($param['unit']==$unitSumber) $sameUnit = true;
		
		// Cek apakah PT yang sama
		$sameComp = false;
		$qPT = selectQuery($dbname,'organisasi','kodeorganisasi,induk',
						   "kodeorganisasi in ('".$param['kebun']."','".
						   $unitSumber."')");
		$resPT = fetchData($qPT);
		$optPT = array();
		foreach($resPT as $row) {
			$optPT[$row['kodeorganisasi']] = $row['induk'];
		}
		if(count($resPT)==2 and $resPT[0]['induk']==$resPT[1]['induk']) {
			$sameComp = true;
		}
		
		/**
		 * R/K
		 */
		if(!$sameUnit) { // Jika unit tidak sama, maka terbentuk 2 jurnal
			// Jenis Caco
			$jenisCaco = ($sameComp)? 'intra': 'inter';
			
			// Akun Caco
			$qCaco = selectQuery($dbname,'keu_5caco','kodeorg,akunpiutang,akunhutang',
								 "kodeorg in ('".$param['kebun']."','".$unitSumber."')
								 and jenis='".$jenisCaco."'");
			$resCaco = fetchData($qCaco);
			
			$optCaco = array();
			foreach($resCaco as $row) {

			    if ($row['akunhutang']=='' || $row['akunpiutang']=='') {
			        exit("Warning : Account intraco or interco not available for ".$row['kodeorg'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
			    }

				if($row['kodeorg']==$param['kebun']) {
					$optCaco[$row['kodeorg']] = $row['akunhutang'];
				} else {
					$optCaco[$row['kodeorg']] = $row['akunpiutang'];
				}
			}
		}
		
		/**
		 * Prepare Jurnal
		 */
		// Get Counter Jurnal
		$counterJ = $zJ->getCounter($optPT[$param['kebun']],$kodeJurnal);
		
		// Nomor Jurnal
		$nojurnal = $zJ->genNoJournal($tglFull,$param['kebun'],$kodeJurnal,$counterJ);
		
		// Data Jurnal
		$dataRes = array();
		$noRef = 'ALB/'.$param['kebun'].'/'.$param['periode'];
		$dataRes['header'] = array(
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>$kodeJurnal,
			'tanggal'=>$tglFull,
			'tanggalentry'=>date('Ymd'),
			'posting'=>'0',
			'totaldebet'=>'0',
			'totalkredit'=>'0',
			'amountkoreksi'=>'0',
			'noreferensi'=>$noRef,
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);
		
		$dataRes['detail'] = array();
		$noUrut = 1;
		$total = 0;
		foreach($resPres as $row) {
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tglFull,
				'nourut'=>$noUrut,
				'noakun'=>$row['noakun'],
				'keterangan'=>'Alokasi Biaya Bibit '.$param['kebun'].' untuk kegiatan '.$row['namakegiatan'],
				'jumlah'=>$row['hargasatuan'] * $row['bibit'],
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$param['kebun'],
				'kodekegiatan'=>$row['kodekegiatan'],
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>$row['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			$total += $row['hargasatuan'] * $row['bibit'];
		}
		$dataRes['header']['totaldebet'] = $total;
		$dataRes['header']['totalkredit'] = $total;
		
		if($sameUnit) { // Unit yang sama pengurangan biaya masih 1 jurnal
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tglFull,
				'nourut'=>$noUrut,
				'noakun'=>$paramJ['noakunkredit'],
				'keterangan'=>'Alokasi Biaya Bibit '.$param['kebun'].' untuk kegiatan '.$row['namakegiatan'],
				'jumlah'=>$total * (-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$param['kebun'],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
		} else { // Unit berbeda, pengurangan biaya jurnal di sumber bibit
			// R/K
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tglFull,
				'nourut'=>$noUrut,
				'noakun'=>$optCaco[$unitSumber],
				'keterangan'=>'Alokasi Biaya Bibit '.$param['kebun'].' untuk kegiatan '.$row['namakegiatan'],
				'jumlah'=>$total * (-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$param['kebun'],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			
			
			// Jurnal kedua
			// Get Counter Jurnal
			$counterJ1 = $zJ->getCounter($optPT[$unitSumber],$kodeJurnal);
			
			// Nomor Jurnal
			$nojurnal1 = $zJ->genNoJournal($tglFull,$unitSumber,$kodeJurnal,$counterJ1);
			
			$dataRes1 = array();
			
			// R/K Header
			$dataRes1['header'] = array(
				'nojurnal'=>$nojurnal1,
				'kodejurnal'=>$kodeJurnal,
				'tanggal'=>$tglFull,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>$total,
				'totalkredit'=>$total,
				'amountkoreksi'=>'0',
				'noreferensi'=>$noRef,
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'
			);
			
			// R/K Detail
			$dataRes1['detail'] = array();
			$dataRes1['detail'][] = array(
				'nojurnal'=>$nojurnal1,
				'tanggal'=>$tglFull,
				'nourut'=>1,
				'noakun'=>$optCaco[$param['kebun']],
				'keterangan'=>'Alokasi Biaya Bibit '.$param['kebun'].' untuk kegiatan '.$row['namakegiatan'],
				'jumlah'=>$total,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unitSumber,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			
			// Biaya
			$dataRes1['detail'][] = array(
				'nojurnal'=>$nojurnal1,
				'tanggal'=>$tglFull,
				'nourut'=>2,
				'noakun'=>$paramJ['noakunkredit'],
				'keterangan'=>'Alokasi Biaya Bibit '.$param['kebun'].' untuk kegiatan '.$row['namakegiatan'],
				'jumlah'=>$total * (-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unitSumber,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
		}
		
		/**
		 * Proses Penjurnalan
		 */
		// Delete Jurnal dulu
		if($sameUnit) {
			$whereDel = "nojurnal='".$nojurnal."'";
		} else {
			$whereDel = "nojurnal in ('".$nojurnal."','".$nojurnal1."')";
		}
		$delJournal = deleteQuery($dbname,'keu_jurnalht',$whereDel);
		try{
			$owlPDO->exec($delJournal); 
			$zJ->doJournal($optPT[$param['kebun']],$kodeJurnal,$dataRes,$counterJ,"Alokasi Biaya Bibit");
			$zJ->doJournal($optPT[$unitSumber],$kodeJurnal,$dataRes1,$counterJ1,"Alokasi Biaya Bibit");
		}catch (PDOException $e){
			exit("Hapus Jurnal Error: ".$e->getMessage());
			die();
		}
		
		// Result
		$res = array('msg' => 'Proses Jurnal berhasil');
		echo json_encode($res);
		break;
}