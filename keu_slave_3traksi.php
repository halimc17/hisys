<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$kodeorg  = $param['kodeorg'];
$dataorg = array();
$dtstr = "select * from " . $dbname . ".organisasi where  kodeorganisasi = '" . $kodeorg . "'";
$str = $owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $str->fetch()) {
	$dataorg[$bar->kodeorganisasi] = $bar;
}
$tahunbulan = implode("", explode('-', $param['periode']));
#cek proses gaji tidak langsung sudah diproses atau belum
if (@$dataorg[$kodeorg]->tipe != "HOLDING") {
	$sCek = "select * from " . $dbname . ".keu_jurnalht where 
         nojurnal like '%KBNB%' 
         and tanggal like '" . $_POST['periode'] . "%' and nojurnal like '%" . $kodeorg . "%'";
	$rCek = fetchdata($sCek);
	/*  if(count($rCek)==0){
    exit('warning: Jalankan Proses Gaji Karyawan Tidak Langsung');
  }*/
	#cek ada potongan gak
	$sPot = "select * from " . $dbname . ".sdm_gaji where 
         periodegaji='" . $_POST['periode'] . "' and kodeorg='" . $kodeorg . "'
         and idkomponen in (select id from " . $dbname . ".sdm_ho_component where plus=0)";
	$rPot = fetchdata($sPot);
	if (count($rPot) != 0) {
		$sCek = "select * from " . $dbname . ".keu_jurnalht where 
         nojurnal like '%POT%' and tanggal like '" . $_POST['periode'] . "%' and nojurnal like '%" . $kodeorg . "%'";
		$rCek = fetchdata($sCek);
		if (count($rCek) == 0) {
			exit('warning: Jalankan Proses Penarikan Potongan Gaji');
		}
	}
	$sCek = "select * from " . $dbname . ".keu_jurnalht where 
         nojurnal like '%DEP%' and tanggal like '" . $_POST['periode'] . "%' and nojurnal like '%" . $kodeorg . "%'";
	$rCek = fetchdata($sCek);
	/*if(count($rCek)==0){
	    	exit('warning: Jalankan Proses Depresiasi');
	  }	*/
}
#1. ambil periode akuntansi
$str = "select tanggalmulai,tanggalsampai,periode from " . $dbname . ".setup_periodeakuntansi where 
           kodeorg ='" . $kodeorg . "' and tutupbuku=0 and periode='" . $_POST['periode'] . "' ";
$tgmulai = '';
$tgsampai = '';
$periode = '';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$tgsampai   = $bar->tanggalsampai;
	$tgmulai    = $bar->tanggalmulai;
	$periode      = $bar->periode;
}
if ($tgmulai == '' || $tgsampai == '')
	exit("Error: Accounting period is not registered");


#= validasi apakah sudah tutup traksi atau belum
#= algoritma ambil daftar kendaraan dari  msvhc_by_operator dan vhc_runht

#= array kendaraan yang service dibengkel 
$str = "select distinct(kodevhc) as kodevhc from " . $dbname . ".msvhc_by_operator 
	  where notransaksi like '" . $kodeorg . "%'   and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$arrkdvhc[$bar['kodevhc']] = $bar['kodevhc'];
}

#= array kendaraan yang bekerja 
$str = "select distinct(kodevhc) as kodevhc from " . $dbname . ".vhc_runht where kodevhc in(select kodevhc from " . $dbname . ".vhc_5master where kodetraksi like '" . $kodeorg . "%') and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$arrkdvhc[$bar['kodevhc']] = $bar['kodevhc'];
}

#= bentuk array dari vhc_5masterhist
$str = "select * from " . $dbname . ".vhc_5master_hist where kodevhc in ('" . implode("','", $arrkdvhc) . "') and periode='" . $param['periode'] . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$dtvhchist[$bar['kodevhc']] = $bar['kodevhc'];
}

#= ambil data kode traksi jika tidak terdapat di master hist
$str = "select * from " . $dbname . ".vhc_5master where kodevhc in ('" . implode("','", $arrkdvhc) . "')";
$res = fetchdata($str);
foreach ($res as $bar) {
	$dtvhcmastertraksi[$bar['kodevhc']] = $bar['kodetraksi'];
}

$nohist = 0;
$dterror = '';
foreach ($arrkdvhc as $dtkdvhc) {
	if ($dtvhchist[$dtkdvhc] != $dtkdvhc) {
		$nohist++;
		$dterror .= "Kendaraan " . $dtkdvhc . " belum terdaftar ditutup buku kendaraan, silahkan informasikan/proses closing kendaraan traksi unit : " . substr($dtvhcmastertraksi[$dtkdvhc], 0, 4) . " periode " . $param['periode'] . " di menu : Traksi->Proses->Tutup Buku Kendaraan<br><br>";
	}
}
if ($nohist > 0) {
	echo "<fieldset style=float:left><label style=font-size:14px;color:blue;>";
	echo $dterror;
	echo "</label></fieldset>";
	exit();
}


#ambil kamus traksi
$str = "select kodetraksi,kodevhc from " . $dbname . ".vhc_5master_hist where periode='" . $param['periode'] . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$kamus[$bar->kodevhc] = $bar->kodetraksi;
}

#pastikan semua kegiatan ada noakun pada saat entry
#antisipasi penggantian kegiatan traksi
$str = "select distinct a.notransaksi,a.jenispekerjaan from " . $dbname . ".vhc_rundt a
    left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
    where b.tanggal like '" . $periode . "%' and kodeorg='" . $kodeorg . "'
    and a.jenispekerjaan not in (SELECT kodekegiatan FROM " . $dbname . ".vhc_kegiatan)";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);
if ($numrows > 0) {
	echo "Error : There are Vehicle activity that do not have Acount Number, Please contact administrator\n";
	while ($barf = $res->fetch()) {
		print_r($barf);
	}
	exit();
}

#2. ambil biaya workshop
#kode parameter WS1, ambil semua noakun biaya bengkel
$str = "select noakundebet,sampaidebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='WS1'";
$dariakun = '';
$sampaiakun = '';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$dariakun = $bar->noakundebet;
	$sampaiakun = $bar->sampaidebet;
}
if ($dariakun == '' or $sampaiakun == '')
	exit('Eror: Journalid for WS1 not found');

$str = "select sum(debet-kredit) as jumlah from " . $dbname . ".keu_jurnaldt_vw  
	  where noakun >='" . $dariakun . "' and noakun<='" . $sampaiakun . "' 
	  and kodeorg like '" . $kodeorg . "%' 
	  and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'
	  and (noreferensi not in('ALK_KERJA_AB','ALK_BY_WS','ALK_WS_GYMH','ALK_MAINTENANCE') or noreferensi is NULL)";
$bybengkel = 0;
$strBengkel = $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$bybengkel = $bar->jumlah;
}




#3 periksa apakah sudah posting semua
$str = "select * from " . $dbname . ".msvhc_by_operator where posting=0 
	  and kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist 
	  where kodetraksi like '" . $kodeorg . "%' and periode= '" . $param['periode'] . "')
	  and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);

$str1 = "select * from " . $dbname . ".vhc_runht where posting=0
	   and kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist 
	   where kodetraksi like '" . $kodeorg . "%' and periode= '" . $param['periode'] . "')
	   and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$numrows1 = owlBaris($res1);

if ($numrows > 0 or $numrows1 > 0) {
	//$t="Service:\n";
	echo "There are transactions that have not posted => Service :\n";
	$no = 0;
	while ($bart = $res->fetch()) {
		$no += 1;
		echo $no . ". No " . $bart->notransaksi . " => " . tanggalnormal($bart->tanggal) . "\n";
		//$t.=$bart->notransaksi."\n";
	}
	echo "There are transactions that have not posted => Pekerjaan :\n";
	//$t.="Pekerjaan:\n";
	$no = 0;
	while ($bart = $res1->fetch()) {
		$no += 1;
		echo $no . ". No " . $bart->notransaksi . " => " . tanggalnormal($bart->tanggal) . "\n";
		//$t.=$bart->notransaksi."\n";
	}
	exit("warning");
}


#4 ambil semua kendaraan yang diservice pada periode berjalan
$str = "select sum(downtime) as dt,kodevhc from " . $dbname . ".msvhc_by_operator 
	  where notransaksi like '" . $kodeorg . "%' 
	  and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "' and posting=1
	  group by kodevhc";
$kend = array();
$byrinci = array();
$totaljamservice = 0;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$arrkodevhc[$bar->kodevhc] = $bar->kodevhc;
	$totaljamservice += $bar->dt;
	$kend[$bar->kodevhc] = $bar->dt;
}




#= 4 ambil daftar station pabrik
$str = "select sum(jumlahjamperbaikan) as dt,statasiun as kodevhc
	from " . $dbname . ".pabrik_rawatmesinht where 0=0 and 
	pabrik='" . $kodeorg . "' and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'
	group by statasiun order by statasiun asc";
// echo $str;
// echo $str;exit();
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$totaljamservice += $bar->dt;
	$kend[$bar->kodevhc] = $bar->dt;
}

foreach ($kend as $key => $val) {
	// @$byrinci[$key]=floor(($val/$totaljamservice)*$bybengkel);
	// @$byrinci[$key]=floor(($val/$totaljamservice));
	@$byrinci[$key] = $val;
	// @$byrinci[$key]=$totaljamservice;
	@$tbyrinci += $byrinci[$key];
	$kendaraanpembulatanbengkel = $key;
}
$jumlahpembulatanbengkel[$kendaraanpembulatanbengkel] = round($bybengkel - $tbyrinci, 0);
// echo "<pre>";
// print_r($jumlahpembulatanbengkel);
// print_r($strBengkel);
// exit("Warning");

// echo $sisalokasibengkel;
#=======================================================================================================  
#4.5 ambilnoakun biaya kendaraan
$akunkdari = '';
$akunksampai = '';
$strh = "select distinct noakundebet,sampaidebet  from " . $dbname . ".keu_5parameterjurnal where  jurnalid='LPVHC'";
$res = $owlPDO->query($strh) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($barh = $res->fetch()) {
	$akunkdari = $barh->noakundebet;
	$akunksampai = $barh->sampaidebet;
}
if ($akunkdari == '' or $akunksampai == '') {
	exit("Error: Journal parameter for LPVHC not found");
}
#5 ambil biaya perkendaraan dari data yang sudah dijurnal jurnal 
$str = "select sum(debet-kredit) as jlh,kodevhc from " . $dbname . ".keu_jurnaldt_vw where
	  kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist 
	  where kodetraksi like '" . $kodeorg . "%'  and periode= '" . $param['periode'] . "') 
	  and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "' 
	  and nojurnal like '%" . $kodeorg . "%'
	  and (noakun between '" . $akunkdari . "' and '" . $akunksampai . "')   
	  and (noreferensi not in('ALK_KERJA_AB','ALK_BY_WS','ALK_TRK_GYMH','ALK_WS_GYMH') or noreferensi is NULL) 
	  group by kodevhc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	if (!isset($biayattlkend[$bar->kodevhc])) $biayattlkend[$bar->kodevhc] = 0;
	$biayattlkend[$bar->kodevhc] += $bar->jlh;
}


#mengambil biaya service di bengkel unit lain
#ambil akun biaya bengkel dialokasi:
$str = "select noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='WS2'";
$noakunAlkBengkel = '';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$noakunAlkBengkel = $bar->noakunkredit;
}

if ($noakunAlkBengkel != '') {
	$str = "select sum(jumlah*-1) as service,kodevhc from " . $dbname . ".keu_jurnaldt_vw where
	  kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist 
	  where kodetraksi like '" . $kodeorg . "%'  and periode= '" . $param['periode'] . "') 
	  and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "' 
	  and kodeorg not like '%" . $kodeorg . "%'
	  and noakun= '" . $noakunAlkBengkel . "'  
	  and (noreferensi not in('ALK_KERJA_AB','ALK_TRK_GYMH','ALK_BY_WS','ALK_WS_GYMH')  or noreferensi is NULL) 
	  group by kodevhc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		if (!isset($biayattlkend[$bar->kodevhc])) $biayattlkend[$bar->kodevhc] = 0;
		$biayattlkend[$bar->kodevhc] += $bar->service;
	}
} else {
}



#6 ambil semua jamkerja kendaraan per unit
$str = "select sum(a.jumlah) as jlhjam,kodevhc from " . $dbname . ".vhc_rundt a
	left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
	left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi
	where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
	and jenispekerjaan!=''  and   
	kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist 
	where kodetraksi like '" . $kodeorg . "%'  and periode= '" . $param['periode'] . "')
	group by kodevhc";
$biayaperjam = array();
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$arrkodevhc[$bar->kodevhc] = $bar->kodevhc;
	if (!isset($byrinci[$bar->kodevhc])) {
		$byrinci[$bar->kodevhc] = 0;
	}
	#biaya kendaraan ditambah biaya bengkel yang masuk ke masing-masing kendaraan
	@$biayaperjam[$bar->kodevhc] = floor(($biayattlkend[$bar->kodevhc] + $byrinci[$bar->kodevhc]) / $bar->jlhjam);
	// @$biayaperjam[$bar->kodevhc]=($biayattlkend[$bar->kodevhc]+$byrinci[$bar->kodevhc])/$bar->jlhjam;
	$jamkerja[$bar->kodevhc] = $bar->jlhjam;

	$biayakendaraan[$bar->kodevhc] = $biayattlkend[$bar->kodevhc] + $byrinci[$bar->kodevhc];
	$talokasibiayakendaraan[$bar->kodevhc] = $biayaperjam[$bar->kodevhc] * $bar->jlhjam;
}

// echo"<pre>";
// print_r($arrkodevhc); 
// echo"</pre>"; 


#ambil lokasi kerja 
$str = "select kodevhc,substr(alokasibiaya,1,4) as alkbyy, sum(jumlah) as hm from " . $dbname . ".vhc_rundt a
	left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
	left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi
	where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
	and jenispekerjaan!=''  and   
	kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist 
	where kodetraksi like '" . $kodeorg . "%'  and periode= '" . $param['periode'] . "')
	group by kodevhc,alkbyy";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,kodeorganisasi', "kodeorganisasi='" . $bar['alkbyy'] . "'");
	if ($optorg[$bar['alkbyy']] != '') {
		$datakend['Kegiatan'][$bar['kodevhc']][$bar['alkbyy']] += $bar['hm'];
		if ($bar['alkbyy'] != $kodeorg) {
			$adacaco++;
		}
	}
}

$str = "select a.kodevhc,substr(b.kodetraksi,1,4) as alkbyy, sum(downtime) as hm from " . $dbname . ".vhc_penggantianht a 
left join " . $dbname . ".vhc_5master_hist b on a.kodevhc=b.kodevhc
where a.tanggal>='" . $tgmulai . "' and a.tanggal <='" . $tgsampai . "' and  a.kodeorg like  '" . $kodeorg . "%' and 
b.kodetraksi not like '" . $kodeorg . "%'  and b.periode= '" . $param['periode'] . "'  group by kodevhc,alkbyy";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,kodeorganisasi', "kodeorganisasi='" . $bar['alkbyy'] . "'");
	if ($optorg[$bar['alkbyy']] != '') {
		$datakend['Workshop'][$bar['kodevhc']][$bar['alkbyy']] += $bar['hm'];
		if ($bar['alkbyy'] != $kodeorg) {
			$adacaco++;
		}
	}
}
// echo"<pre>";
// print_r($datakend);
// print_r($kodeorg);
// echo"</pre>";


if ($adacaco > 0) {
	echo "<fieldset style=float:left><label style=font-size:14px;color:blue;>Ada kiriman biaya dari unit " . $kodeorg . " ke unit lain, Harap hubungi KTU masing-masing unit yang terkait<br>dikarenakan adanya pengiriman biaya interco/intraco, sbb :<br></label></fieldset><div style=clear:both></div>";
	echo "
		 <table class=sortable cellpadding=5 cellspacing=1 border=0 style=min-width:700px>
		 <thead>
		   <tr class=rowheader style='text-align:center';>
		   <th>No</th>
		   <th>Jenis</th>
		   <th>Dari " . $kodeorg . " ke Unit</th>
		   <th>KodeVhc</th>
		   <th>Nopol</th>
		   <th>Detail</th>
		   <th>HM/KM</th>
		   <th>Status</th>
		   </tr>
		 </thead>
		 <tbody>";
	$no = 0;
	foreach ($datakend as $jenis => $vkdvhc) {
		foreach ($vkdvhc as $kodevhc => $vunitkirim) {
			foreach ($vunitkirim as $kdunitkirim => $jlhhm) {
				if ($kdunitkirim != $kodeorg) {

					$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and kodeorg='" . substr($kdunitkirim, 0, 4) . "'";
					$tutup = "";
					$res = fetchdata($str);
					if ($res[0]['tutupbuku'] == '1') {
						$tutup = "<font style=color:red>Periode akutansi sudah ditutup.</font>";
						$tutup2 .= $tutup;
					}

					$optorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kdunitkirim . "'");
					$no++;
					echo "<tr class=rowcontent style=cursor:pointer title='Click detail...' onclick=getdetcaco('" . $kodevhc . "','" . $kodeorg . "','" . $kdunitkirim . "','" . $param['periode'] . "','kirim','" . $jenis . "');>";
					echo "<td>" . $no . "</td>";
					echo "<td>" . $jenis . "</td>";
					echo "<td>" . $kdunitkirim . " - " . $optorg[$kdunitkirim] . "</td>";
					echo "<td>" . $kodevhc . "</td>";
					echo "<td>" . getNopol($kodevhc) . "</td>";
					echo "<td>" . getNopol($kodevhc, 'd') . "</td>";
					echo "<td align=right>" . number_format($jlhhm) . "</td>";
					echo "<td>" . $tutup . "</td>";
					echo "</tr>";
				}
			}
		}
	}

	echo "</table><br>";
}

#dari unit lain ke unit anda
$str = "select kodevhc,substr(alokasibiaya,1,4) as alkbyy, sum(jumlah) as hm from " . $dbname . ".vhc_rundt a
	left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
	left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi
	where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
	and jenispekerjaan!=''  and   
	kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist 
	where kodetraksi not like '" . $kodeorg . "%')
	group by kodevhc,alkbyy";
$res = fetchdata($str);
$datakend = array();
foreach ($res as $bar) {
	$kdtrak = makeOption($dbname, 'vhc_5master_hist', 'kodevhc,kodetraksi', "kodevhc='" . $bar['kodevhc'] . "'");
	$optorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,kodeorganisasi', "kodeorganisasi='" . $bar['alkbyy'] . "'");
	if ($optorg[$bar['alkbyy']] != '') {
		if (substr($kdtrak[$bar['kodevhc']], 0, 4) != $kodeorg) {
			if ($bar['alkbyy'] == $kodeorg) {
				$datakend['Kegiatan'][$bar['kodevhc']][substr($kdtrak[$bar['kodevhc']], 0, 4)] += $bar['hm'];
				$terimacaco++;
			}
		}
	}
}

$str = "select a.kodevhc,substr(a.kodeorg,1,4) as alkbyy, sum(downtime) as hm from " . $dbname . ".vhc_penggantianht a 
left join " . $dbname . ".vhc_5master_hist b on a.kodevhc=b.kodevhc
where a.tanggal>='" . $tgmulai . "' and a.tanggal <='" . $tgsampai . "' and  a.kodeorg not like  '" . $kodeorg . "%' and 
b.kodetraksi like '" . $kodeorg . "%' group by kodevhc,alkbyy";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kdtrak = makeOption($dbname, 'vhc_5master_hist', 'kodevhc,kodetraksi', "kodevhc='" . $bar['kodevhc'] . "'");
	$optorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,kodeorganisasi', "kodeorganisasi='" . $bar['alkbyy'] . "'");
	if ($optorg[$bar['alkbyy']] != '') {
		if ($bar['alkbyy'] != $kodeorg) {
			$datakend['Workshop'][$bar['kodevhc']][$bar['alkbyy']] += $bar['hm'];
			$terimacaco++;
		}
	}
}

if ($terimacaco > 0) {
	echo "<fieldset style=float:left><label style=font-size:14px;color:blue;>Ada kiriman biaya dari unit lain ke unit " . $kodeorg . ", sbb :<br></label></fieldset><div style=clear:both></div>";
	echo "
		 <table class=sortable cellpadding=5 cellspacing=1 border=0 style=min-width:700px>
		 <thead>
		   <tr class=rowheader style='text-align:center';>
		   <th>No</th>
		   <th>Jenis</th>
		   <th>Dari Unit Lain Ke " . $kodeorg . "</th>
		   <th>KodeVhc</th>
		   <th>Nopol</th>
		   <th>Detail</th>
		   <th>HM/KM</th>
		   </tr>
		 </thead>
		 <tbody>";
	$no = 0;
	foreach ($datakend as $jenis => $vkdvhc) {
		foreach ($vkdvhc as $kodevhc => $vunitkirim) {
			foreach ($vunitkirim as $kdunitkirim => $jlhhm) {
				if ($kdunitkirim != $kodeorg) {
					$optorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kdunitkirim . "'");
					$no++;
					echo "<tr class=rowcontent style=cursor:pointer title='Click detail...' onclick=getdetcaco('" . $kodevhc . "','" . $kdunitkirim . "','" . $kodeorg . "','" . $param['periode'] . "','terima','" . $jenis . "');>";
					echo "<td>" . $no . "</td>";
					echo "<td>" . $jenis . "</td>";
					echo "<td>" . $kdunitkirim . " - " . $optorg[$kdunitkirim] . "</td>";
					echo "<td>" . $kodevhc . "</td>";
					echo "<td>" . getNopol($kodevhc) . "</td>";
					echo "<td>" . getNopol($kodevhc, 'd') . "</td>";
					echo "<td align=right>" . number_format($jlhhm) . "</td>";
					echo "</tr>";
				}
			}
		}
	}
	echo "</table><br>";
}

// echo"<pre>";
// print_r($datakend);
// print_r($kodeorg);
// echo"</pre>";
if ($tutup2 == '') {
	echo "<button class=mybutton onclick=prosesAlokasi(1) id=btnproses>Process</button><button class=mybutton onclick=exportTableToExcel()>Excel</button>";
} else {
	echo "<fieldset style=float:left><label style=font-size:14px;color:red;>Ada unit penerima alokasi biaya sudah melakukan tutup buku sehingga proses tidak bisa dilanjutkan,<br> silahkan koordinasikan dengan unit tujuan atau anda rubah transaksi alokasi biaya tersebut.</label></fieldset>
	<div style=clear:both></div>
	";
}

echo "<font ><br>Note: If it does not work please reprocess, the old data will be deleted.</font>
	 <table class=sortable cellpadding=5 cellspacing=1 border=0 style=min-width:700px id=mytable>
	 <thead>
	   <tr class=rowheader style='text-align:center';>
	   <th>No</th>
	   <th>Period</th>
	   <th>KodeVhc</th>
	   <th>Nopol</th>
	   <th>Organization</th>
	   <th>Price/Hour</th>
	   <th hidden>Pembulatan</th>
	   <th>Type</th>
	   </tr>
	 </thead>
	 <tbody>";


echo "<pre>";
echo "</pre>";

$no = $nobyws = $nobywsmm = $nobyperjam = 0;
// array_multisort($byrinci,SORT_ASC);
foreach ($byrinci as $key => $val) {
	if ($kamus[$key] != '') {
		$jenis = 'BYWS';
		$nobyws += 1;
		$notampil = $nobyws;
	} else {
		$jenis = 'BYWSMM';
		$nobywsmm += 1;
		$notampil = $nobywsmm;
	}

	$no += 1;
	// if($val>0){
	echo "<tr class=rowcontent id='row" . $no . "'>
		   <td>" . $no . "</td>
		   <td id='periode" . $no . "'>" . $_POST['periode'] . "</td>
		   <td id='kodevhc" . $no . "'>" . $key . "</td>
		   <td>" . getNopol($key) . "</td>
			<td>" . $kamus[$key] . "</td>
		   <td id='jumlah" . $no . "' align=right>" . number_format($val, 0, '.', '') . "</td>    
			<td hidden id='jumlahpembulatan" . $no . "' align=right>" . number_format($jumlahpembulatanbengkel[$key], 10, '.', '') . "</td>    
		    <td hidden id='jumlahbiayakendaraan" . $no . "'></td>
		   <td id='jenis" . $no . "'>" . $jenis . "</td>
		   <td hidden id='nourut" . $no . "' hidden>" . $notampil . "</td>
		   </tr>";
	// }

}

#= buat tampungan
// $no++;
// $notampil++;
// echo"<tr class=rowcontent id='row".$no."'>
// <td>".$no."</td>
// <td id='periode".$no."'>".$_POST['periode']."</td>
// <td id='kodevhc".$no."'>".$kendaraantampung."</td>
// <td>".getNopol($kendaraantampung)."</td>
// <td>".$kamus[$kendaraantampung]."</td>
// <td id='jumlah".$no."' align=right>".number_format($sisaalokasitampung,10,'.','')."</td>    
// <td id='jenis".$no."'>".$jenis."</td>
// <td id='nourut".$no."'>".$notampil."</td>
// </tr>";






// foreach($byrinci as $key =>$val) { 
// $no+=1;
// echo"<tr class=rowcontent id='row".$no."'>
// <td>".$no."</td>
// <td id='periode".$no."'>".$_POST['periode']."</td>
// <td id='kodevhc".$no."'>".$key."</td>
// <td>".$kamus[$key]."</td>
// <td id='jumlah".$no."' align=right>".number_format($val,10,'.','')."</td>    
// <td id='jenis".$no."'>BYWS</td>
// </tr>";
// }


foreach ($biayaperjam as $key => $jlh) {
	$no += 1;
	$nobyperjam += 1;
	$notampil = $nobyperjam;

	echo "<tr class=rowcontent id='row" . $no . "'>
	   <td>" . $no . "</td>
	   <td id='periode" . $no . "'>" . $_POST['periode'] . "</td>
	   <td id='kodevhc" . $no . "'>" . $key . "</td>
	   <td>" . getNopol($key) . "</td>
	   <td>" . $kamus[$key] . "</td>    
	   <td id='jumlah" . $no . "' align=right>" . number_format(fixnan($jlh), 0, '.', '') . "</td>    
	   <td hidden id='jumlahpembulatan" . $no . "' align=right>" . number_format(($biayakendaraan[$key] - $talokasibiayakendaraan[$key] + $jumlahpembulatanbengkel[$key]), 2, '.', '') . "</td>    
	   
	   <td  hidden id='jumlahbiayakendaraan" . $no . "'>" . $biayakendaraan[$key] . "</td>
	   <td hidden>" . $talokasibiayakendaraan[$key] . "</td>
	   
	   
	   <td id='jenis" . $no . "'>ALKJAM</td>
	   <td hidden id='nourut" . $no . "' hidden>" . $notampil . "</td>
	   </tr>";
}


echo "</tbody><tfoot></tfoot></table>";
