<?php

error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

$method = checkPostGet('method', '');
$kodeorg = checkPostGet('unit', '');
$tipeprint = checkPostGet('tipeprint', '');

$getPT = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');


$namakar = array();
$premi = array();
$penalty = array();
$gapokbhl = array();
$penaltykehadiran = array();


if ($param['tipekar'] != '') {
	$tpkar = " and b.tipekaryawan='" . $param['tipekar'] . "'";
	$tpkar1 = " and tipekaryawan='" . $param['tipekar'] . "'";
	$tpkar2 = " tipekaryawan='" . $param['tipekar'] . "' and";
} else {
	$tpkar = " and b.tipekaryawan != '0' ";
	$tpkar1 = " and tipekaryawan != '0'";
	$tpkar2 = " tipekaryawan != '0' and";
}

# Get Period Range
$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai', "periode='" . $param['periodegaji'] . "' and kodeorg='" . $param['kodeorg'] . "'");
$resPeriod = fetchData($qPeriod);
@$tanggal1 = $resPeriod[0]['tanggalmulai'];

# Get Period Range
$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai', "periode='" . $param['periodegaji'] . "' and kodeorg='" .
	$param['kodeorg'] . "' and jenisgaji='H'");
$resPeriod = fetchData($qPeriod);
@$tanggal1 = $resPeriod[0]['tanggalmulai'];
@$tanggal2 = $resPeriod[0]['tanggalsampai'];

list($d1, $m1, $y1) = explode('-', $param['tglawal']);
list($d2, $m2, $y2) = explode('-', $param['tglakhir']);

if ($m1 != $m2 || $y1 != $y2) {
	exit("warning: Tanggal awal dan tanggal akhir harus berada pada bulan dan tahun yang sama.");
}

$tanggal1 = "$y1-$m1-$d1";
$tanggal2 = "$y2-$m2-$d2";

## Apakah ada datakaryawan Hist nya gak
$str = "select karyawanid,nik,namakaryawan,nourut from " . $dbname . ".datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji ='" . substr($tanggal1, 0, 7) . "'  and version_type='B'  ";
$res = fetchdata($str);
if (count($res) > 0) {
	$table_hist = "datakaryawan_hist";
	$whereHistA = "and a.periodegaji='" . substr($tanggal1, 0, 7) . "' and a.version_type='B' ";
	$whereHistB = "and b.periodegaji='" . substr($tanggal1, 0, 7) . "' and b.version_type='B' ";
	$whereHistX = "and periodegaji='" . substr($tanggal1, 0, 7) . "' and version_type='B'";
} else {
	$table_hist = "datakaryawan";
	$whereHistA = "";
	$whereHistB = "";
	$whereHistX = "";
}

$query1 = "select a.karyawanid,a.nik,a.subbagian,a.bagian,a.tmkjamsostek,statuspajak,tipekaryawan,namakaryawan,tipekaryawan,kodejabatan,jms,bpjs,pensiun,lokasitugas, a.jumlahtanggungan as jmltanggungan,a.kodecatu as kodecatu,bagian from " . $dbname . "." . $table_hist . " a where " . $tpkar2 . " lokasitugas='" . $param['kodeorg'] . "' and (tanggalkeluar >= '" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi in ('0','1') and ( tanggalmasuk <='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) " . $whereHistA . " group by a.karyawanid order by a.namakaryawan asc";
$absRes = fetchData($query1);


# Error empty karyawan
if (empty($absRes)) {
	exit("Data karyawan tidak ditemukan...");
} else {
	$id = array();
	$idaaaa = '';
	foreach ($absRes as $row => $kar) {
		$id[$kar['karyawanid']][] = $kar['karyawanid'];
		$karydidatakary[$kar['karyawanid']] = $kar['karyawanid'];
		$nikKry[$kar['karyawanid']] = $kar['nik'];
		$statuspajak[$kar['karyawanid']] = $kar['statuspajak'];
		$subbagianKry[$kar['karyawanid']] = $kar['subbagian'];
		$bagianKry[$kar['karyawanid']] = $kar['bagian'];
		$namakar[$kar['karyawanid']] = $kar['namakaryawan'];

		$tipekaryawanx[$kar['karyawanid']] = getNamaTipeKary($kar['tipekaryawan']);
		$kodejabatanx[$kar['karyawanid']] = getNamaJabatan($kar['kodejabatan']);

		$tmkjamsostek[$kar['karyawanid']] = $kar['tmkjamsostek'];

		if ($kar['kodecatu'] != '0') {
			$kodecatu[$kar['karyawanid']] = $kar['kodecatu'];
		}

		if ($idaaaa == '') {
			$idaaaa = "'" . $kar['karyawanid'] . "'";
		} else {
			$idaaaa .= ",'" . $kar['karyawanid'] . "'";
		}

		#bpjstenaga u/ jkk,jkm,jht
		$bpjstenaga[$kar['karyawanid']] = trim($kar['jms']);
		#bpjs pensiun //jp
		$bpjspensiun[$kar['karyawanid']] = trim($kar['pensiun']);
		#bpjskes
		$bpjskes[$kar['karyawanid']] = trim($kar['bpjs']);
		$bpjskestanggungan[$kar['karyawanid']] = $kar['jmltanggungan'] + 1;
		$tipekaryawan[$kar['karyawanid']] = trim($kar['tipekaryawan']);
		$lokasitugas[$kar['karyawanid']] = trim($kar['lokasitugas']);
	}
}

# ambil gaji pokok per hari untuk KHL
$strgjh = "select a.karyawanid,jumlah as umrbulanan,b.tipekaryawan,a.idkomponen from " . $dbname . ".sdm_5gajipokok a left join 
				  " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
				   where a.tahun='" . substr($tanggal1, 0, 7) . "' " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
				   and a.idkomponen in (select id from " . $dbname . ".sdm_ho_component where type='basic' and id not in ('59')) " . $whereHistB . " ";
$resgjh = fetchData($strgjh);
foreach ($resgjh as $idx => $val) {
	if ($val['karyawanid'] == 4) {
		if ($val['idkomponen'] == '1') {
			$gajiperhari[$val['karyawanid']] = $val['umrbulanan'] / 25;
			$umrbulanan[$val['karyawanid']] = $val['umrbulanan'];
		}
	} else {
		$gajiperhari[$val['karyawanid']] = $val['umrbulanan'] / 25;
		$umrbulanan[$val['karyawanid']] += $val['umrbulanan'];
	}
}


$tdkdibayar = array();
$hktdkbyr = array();

#ambil jumlah hk tidak dibayar untuk KHT dan total tidak dibayar
$strgjh = "select  count(*) as jlh,b.karyawanid from " . $dbname . ".sdm_hktdkdibayar_vw a left join 
				  " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
				   where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
				   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'  " . $whereHistB . "   
				   group by a.karyawanid";
$resgjh = fetchData($strgjh);
foreach ($resgjh as $idx => $val) {
	@$tdkdibayar[$val['karyawanid']] += $gajiperhari[$val['karyawanid']] * $val['jlh']; #jumlah tidak dibayar
	@$hktdkbyr[$val['karyawanid']] += $val['jlh'];
	@$lstKary[$val['karyawanid']] = $val['karyawanid'];
}

#ambil hk keseluruhan#
$strup = "select sum(a.umr) as upahabsen,sum(a.premi) as premi,sum(a.insentif) as insentif,sum(a.insentiflibur) as insentiflibur,
		sum(a.penaltykehadiran) as penaltykehadiran,a.karyawanid,sum(a.nilaihk) as hk,b.tipekaryawan
		FROM " . $dbname . ".sdm_absensidt_vw a
		left join " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
        where a.karyawanid in (select karyawanid from datakaryawan where lokasitugas = '" . $param['kodeorg'] . "') 
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' 
		" . $tpkar . " and a.absensi in (select kodeabsen from " . $dbname . ".sdm_5absensi where kelompok=1) " . $whereHistB . " group by a.karyawanid";
$resup = fetchData($strup);
foreach ($resup as $idx => $val) {
	if ($val['tipekaryawan'] == '4') {
		@$gapokbhl[$val['karyawanid']] += $val['upahabsen'];
	}
	@$premi[$val['karyawanid']] += $val['premi'];
	@$penaltykehadiran[$val['karyawanid']] += $val['penaltykehadiran'];
	@$hk[$val['karyawanid']] += $val['hk'];
}

#ambil semua komponen dari sdm_5gajipokok 
$str1 = "select a.*,b.namakaryawan from " . $dbname . ".sdm_5gajipokok a left join " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
            where a.tahun='" . substr($tanggal1, 0, 7) . "' " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
            and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and idkomponen in (select id from " . $dbname . ".sdm_ho_component where plus=1 and type='basic')  " . $addExcp . "  " . $whereHistB . "";
$res1 = fetchData($str1);
foreach ($res1 as $idx => $val) {

	if (@$hk[$val['karyawanid']] == '') {
		@$hk[$val['karyawanid']] = 0;
	}

	#= gapok bhl tidak dari sini
	if ($tipekaryawan[$val['karyawanid']] != '4') {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $val['karyawanid'],
			'idkomponen' => $val['idkomponen'],
			'jumlah' => $val['jumlah'],
			'pengali' => 1,
			'hk' => $hk[$val['karyawanid']]
		);
	}

	if ($val['idkomponen'] != 1 and $tipekaryawan[$val['karyawanid']] == '4') {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $val['karyawanid'],
			'idkomponen' => $val['idkomponen'],
			'jumlah' => $val['jumlah'],
			'pengali' => 1,
			'hk' => $hk[$val['karyawanid']]
		);
	}
}

#3. Get Lembur Data
$query2 = "select a.karyawanid,sum(a.uangkelebihanjam) as lembur from " . $dbname . ".sdm_lemburdt a left join 
				  " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
				   where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') " . $whereHistB . "
				   and a.kodeorg like '" . $param['kodeorg'] . "%' and (tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "') group by a.karyawanid";
$lbrRes = fetchData($query2);
foreach ($lbrRes as $idx => $row) {
	if (isset($id[$row['karyawanid']])) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => 33,
			'jumlah' => $row['lembur'],
			'pengali' => 1,
			'hk' => 0
		);
	} else {
		//abaikan jika tidak terdaftar pada karyawanid  
	}
}

#4. GET POTONGAN
$query3 = "select a.nik as karyawanid,sum(jumlahpotongan) as potongan,tipepotongan from " . $dbname . ".sdm_potongandt a left join 
				" . $dbname . "." . $table_hist . " b on a.nik=b.karyawanid 
                where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00')  
				" . $whereHistB . " and kodeorg='" . $param['kodeorg'] . "' and a.periodegaji='" . substr($tanggal1, 0, 7) . "' group by a.nik,a.tipepotongan";
$potRes = fetchData($query3);
foreach ($potRes as $idx => $row) {
	if (isset($id[$row['karyawanid']])) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => $row['tipepotongan'],
			'jumlah' => $row['potongan'],
			'pengali' => 1,
			'hk' => 0
		);
	} else {
		//abaikan jika tidak terdaftar pada karyawanid  
	}
}



#5. GET ANGSURAN
$query4 = "select lokasitugas,a.karyawanid,jenis,sum(jumlah) as bulanan from " . $dbname . ".sdm_angsuran a 
	left join " . $dbname . ".sdm_angsurandt c on a.notransaksi=c.notransaksi
	left join " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
	where 1=1 and b.lokasitugas='" . $param['kodeorg'] . "' 
	and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and a.status=1  and bulan='" . substr($tanggal1, 0, 7) . "' " . $whereHistB . " group by jenis,karyawanid";
$angRes = fetchData($query4);
foreach ($angRes as $idx => $row) {
	if ($id[$row['karyawanid']][0] == $row['karyawanid']) {
		#add to ready data================================================
		$readyData[] = array(
			'kodeorg' => $row['lokasitugas'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => $row['jenis'],
			'jumlah' => $row['bulanan'],
			'pengali' => 1,
			'hk' => 0
		);
	}
}

#6. GET PERAWATAN NON KONTAN
$query5 = "select sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi,sum(a.jhk) as jumlahhk from " . $dbname . ".kebun_kehadiran_vw a left join 
				" . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
				where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . " )
				and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and a.kontanan !='kontan'
				and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				and a.notransaksi not like '%BOR%' " . $whereHistB . "  group by a.karyawanid";
$premRes = fetchData($query5);
foreach ($premRes as $idx => $val) {
	if ($val['premi'] > 0)
		@$premi[$val['karyawanid']] += $val['premi'];
	#gapok KHL
	if ($tipekaryawan[$val['karyawanid']] == '4') {
		@$gapokbhl[$val['karyawanid']] += $val['gaji'];
	}
	@$hk[$val['karyawanid']] += $val['jumlahhk'];
}

#7. GET PERAWATAN KONTAN
$query5 = "select sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi from " . $dbname . ".kebun_kehadiran_vw a left join 
				" . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
				where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . " )
				and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and a.kontanan ='kontan'
				and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				and a.notransaksi not like '%BOR%' " . $whereHistB . "  group by a.karyawanid";
$premRes = fetchData($query5);
foreach ($premRes as $idx => $val) {
	if ($val['premi'] > 0 or $val['gaji']) {
		@$premikontanan[$val['karyawanid']] += $val['premi'] + $val['gaji'];
		@$potkontanan[$val['karyawanid']] += $val['premi'] + $val['gaji'];
	}
}

#8. GET PANEN NON KONTAN
$query6 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.tpremi) as premi,sum(a.rupiahpenalty) as penalty,sum(hkpanenperhari) as hk ,sum(a.upahpenalty) as upahpenalty
		   from " . $dbname . ".kebun_prestasi_vs_hk a left join 
		   " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
		   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "'  " . $whereHistX . ") 
		   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
		   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' and a.noreferensi like '%PNN%'    
		   and a.keterangan != 'KONTAN' " . $whereHistB . " group by a.tanggal, a.karyawanid";
$premRes1 = fetchData($query6);
foreach ($premRes1 as $idx => $val) {
	@$premi[$val['karyawanid']] += $val['premi'];
	@$penalty[$val['karyawanid']] += $val['penalty'];
	if ($tipekaryawan[$val['karyawanid']] == '4') {
		@$gapokbhl[$val['karyawanid']] += $val['upahkerja'];
	}
	@$hk[$val['karyawanid']] += $val['hk'];
}

#9. GET PANEN KONTAN
$query66 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.tpremi) as premi,sum(a.rupiahpenalty) as penalty,sum(hkpanenperhari) as hk ,sum(a.upahpenalty) as upahpenalty
		   from " . $dbname . ".kebun_prestasi_vs_hk a left join 
		   " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
		   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ")
		   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
		   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' and a.noreferensi like '%PNN%'     
		   and a.keterangan='KONTAN'  " . $whereHistB . " group by a.tanggal, a.karyawanid";
$premRes66 = fetchData($query66);
foreach ($premRes66 as $idx => $val) {
	@$premikontanan[$val['karyawanid']] += $val['premi'] + $val['upahkerja'];
	@$potkontanan[$val['karyawanid']] += $val['premi'] + $val['upahkerja'] + $val['penalty'];
	@$hk[$val['karyawanid']] += $val['hk'];
}

#10. GET TRAKSI NON KONTAN
$query7 = "select sum(a.upah) as upah,a.idkaryawan as karyawanid,sum(a.premi) as premi,sum(a.penalty) as penalty,sum(hk) as hk
				from " . $dbname . ".vhc_runhk_vw a left join 
				" . $dbname . "." . $table_hist . " b on a.idkaryawan=b.karyawanid
				where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ")
				and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and a.kontanan != 'kontan'
				and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' 
				and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' " . $whereHistB . "     
				group by a.idkaryawan";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
	@$premi[$val['karyawanid']] += $val['premi'];
	@$penalty[$val['karyawanid']] += $val['penalty'];
	if ($tipekaryawan[$val['karyawanid']] == '4') {
		@$gapokbhl[$val['karyawanid']] += $val['upah'];
	}
	@$hk[$val['karyawanid']] += $val['hk'];
}

#11. GET TRAKSI KONTAN
$query7 = "select sum(a.upah) as upah,a.idkaryawan as karyawanid,sum(a.premi) as premi,sum(a.penalty) as penalty,sum(hk) as hk
				from " . $dbname . ".vhc_runhk_vw a left join 
				" . $dbname . "." . $table_hist . " b on a.idkaryawan=b.karyawanid 
				where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ")
				and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and a.kontanan = 'kontan'
				and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' 
				and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' " . $whereHistB . "     
				group by a.idkaryawan";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
	@$premikontanan[$val['karyawanid']] += $val['premi'] + $val['upah'];
	@$potkontanan[$val['karyawanid']] += $val['premi'] + $val['upah'];
	@$penalty[$val['karyawanid']] += $val['penalty'];

	@$hk[$val['karyawanid']] += $val['hk'];
}


#6.3.4 Get Premi Kemandoran
// $query8 = "select sum(a.premiinput) as premi,a.karyawanid
// 	  from " . $dbname . ".kebun_premikemandoran a left join 
// 	  " . $dbname . ".".$table_hist." b on a.karyawanid=b.karyawanid 
// 	  where 1=1 ".$tpkar." and b.lokasitugas='" . $param['kodeorg'] . "' 
// 	  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
// 	  and b.karyawanid in (select karyawanid from ".$table_hist." where lokasitugas = '".$param['kodeorg']."' ".$whereHistX.")  
// 	  and a.periode = '" . $param['periodegaji'] . "'     
// 	   and a.kontanan!='KONTAN' ".$whereHistB." group by a.karyawanid";
// $premRes2 = fetchData($query8);
// foreach ($premRes2 as $idx => $val) {
//   $premi[$val['karyawanid']]+=$val['premi'];
// }

// #kontanan
// $query88 = "select sum(a.premiinput) as premi,a.karyawanid
// 		from " . $dbname . ".kebun_premikemandoran a left join 
// 		" . $dbname . ".".$table_hist." b on a.karyawanid=b.karyawanid 
// 		where 1=1 ".$tpkar." and b.lokasitugas='" . $param['kodeorg'] . "' 
// 		and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
// 		and a.periode = '" . $param['periodegaji'] . "'     
// 		and a.kontanan='KONTAN' ".$whereHistB." group by a.karyawanid";
// $premRes28 = fetchData($query88);
// foreach ($premRes28 as $idx => $val) {
//   @$premikontanan[$val['karyawanid']]+=$val['premi'];
//   @$potkontanan[$val['karyawanid']]+=$val['premi'];
// }


#= BMTBS NON KONTAN
$query20 = "select sum(a.rppremi) as premi,sum(a.rphk) as rphk,sum(a.nilai1hk) as nilai1hk,a.karyawanid,sum(a.hk) as jumlahhk
			from " . $dbname . ".kebun_3premibmtbs a left join 
			" . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
			where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ")
			and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
			and a.periode = '" . substr($tanggal1, 0, 7) . "'     
			and a.kontanan!='KONTAN' " . $whereHistB . " group by a.karyawanid";
$res20 = fetchData($query20);
foreach ($res20 as $idx => $val) {
	if ($tipekaryawan[$val['karyawanid']] == '4') {
		@$gapokbhl[$val['karyawanid']] += $val['rphk'];
	}
	if ($val['premi'] > 0) {
		$premi[$val['karyawanid']] += $val['premi'];
	}
	@$hk[$val['karyawanid']] += $val['jumlahhk'];
}

#= premi bmtbs kontan
$query202 = "select sum(a.rppremi) as premi , sum(a.rphk) as rphk,sum(a.nilai1hk) as nilai1hk,a.karyawanid
			from " . $dbname . ".kebun_3premibmtbs a left join 
			" . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
			where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ")
			and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
			and a.periode = '" . substr($tanggal1, 0, 7) . "'     
			and a.kontanan = 'KONTANAN' " . $whereHistB . " group by a.karyawanid";
$res202 = fetchData($query202);
foreach ($res202 as $idx => $val) {
	$premikontanan[$val['karyawanid']] += $val['premi'] + $val['rphk'];
	$potkontanan[$val['karyawanid']] += $val['premi'] + $val['rphk'];
}

#6.3.5 Get Premi dari BKM Sipil Non Kontan
$query7 = "select sum(a.umr) as upah,a.nik as karyawanid,sum(a.premi) as premi,sum(a.jhk) as jhk from " . $dbname . ".vhc_spl_kehadiran_vw a left join 
		" . $dbname . "." . $table_hist . " b on a.nik=b.karyawanid 
		where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") and 
		(b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and a.kontanan != 'KONTAN'  
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' " . $whereHistB . " group by a.nik";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
	if ($val['premi'] > 0) {
		@$premi[$val['karyawanid']] += $val['premi'];
	}

	if ($tipekaryawan[$val['karyawanid']] == '4') {
		@$gapokbhl[$val['karyawanid']] += $val['upah'];
	} else {
		@$penalty[$val['karyawanid']] += $val['nilai1hk'] - $val['rphk'];
	}

	@$hk[$val['karyawanid']] += $val['jhk'];
}

#6.3.5 Get Premi dari BKM Sipil Kontan
$query7 = "select sum(a.umr) as upah,a.nik as karyawanid,sum(a.premi) as premi,sum(a.jhk) as jhk from " . $dbname . ".vhc_spl_kehadiran_vw a left join 
		" . $dbname . "." . $table_hist . " b on a.nik=b.karyawanid 
		where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") and 
		(b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and a.kontanan = 'KONTAN'  
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' " . $whereHistB . " group by a.nik";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
	if ($val['premi'] > 0 or $val['upah'] > 0) {
		@$premikontanan[$val['karyawanid']] += $val['premi'] + $val['upah'];
		@$potkontanan[$val['karyawanid']] += $val['premi'] + $val['upah'];
	}
	@$hk[$val['karyawanid']] += $val['jhk'];
}


$query20xx = "select a.* from " . $dbname . ".sdm_pesangon a left join 
		" . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
		where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' and 
		(b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' " . $whereHistB . "
		and a.posting=1 and a.kodeunit like '" . $param['kodeorg'] . "%'  order by a.tanggal";
//echo $query20xx;
$res20xx = fetchData($query20xx);
foreach ($res20xx as $idx => $val) {
	if ($val['jenispesangon'] == 'Pesangon') {
		$pesangon[$val['karyawanid']] += $val['totalterima'];
	} elseif ($val['jenispesangon'] == 'Kompensasi') {
		$kompensasi[$val['karyawanid']] += $val['totalterima'];
	} elseif ($val['jenispesangon'] == 'Uang Pisah') {
		$uangpisah[$val['karyawanid']] += $val['totalterima'];
	}
}


####################################################################################################################
#TAMBAHAN PEJABAT KEBUN YG KHL

$dtmandor = array();
#### bentuk gapok dari absensi kebun_aktifitas

#bentuk absen  mandor
$str = "select a.nikmandor,a.tanggal,a.kodeorg,b.tipekaryawan,b.lokasitugas from " . $dbname . ".kebun_aktifitas a 
		left join " . $dbname . "." . $table_hist . " b on a.nikmandor=b.karyawanid  where 
		b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") " . $tpkar1 . " and
		tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' and nikmandor!=''  and (nospk='' or nospk is null)  " . $whereHistB . " ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	//bentuk tanggal dulu
	$dtmandor[$bar['nikmandor']] = $bar['nikmandor'];
	$dttgl[$bar['tanggal']] = $bar['tanggal'];
	$counttgl[$bar['nikmandor']][$bar['tanggal']] = 1;
}

#mandor 1
$str = "select a.nikmandor1,a.tanggal,a.kodeorg,b.tipekaryawan,b.lokasitugas from " . $dbname . ".kebun_aktifitas a 
		left join " . $dbname . "." . $table_hist . " b on a.nikmandor1=b.karyawanid  where 
		b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") " . $tpkar1 . " and
		tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' and nikmandor1!='' and (nospk='' or nospk is null) " . $whereHistB . " ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	//bentuk tanggal dulu
	$dtmandor[$bar['nikmandor1']] = $bar['nikmandor1'];
	$dttgl[$bar['tanggal']] = $bar['tanggal'];
	$counttgl[$bar['nikmandor1']][$bar['tanggal']] = 1;
}

#kerani muat
$str = "select a.keranimuat,a.tanggal,a.kodeorg,b.tipekaryawan,b.lokasitugas from " . $dbname . ".kebun_aktifitas a 
		left join " . $dbname . "." . $table_hist . " b on a.keranimuat=b.karyawanid  where 
		b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") " . $tpkar1 . " and
		tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' and keranimuat!='' and (nospk='' or nospk is null)  " . $whereHistB . " ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	//bentuk tanggal dulu
	$dtmandor[$bar['keranimuat']] = $bar['keranimuat'];
	$dttgl[$bar['tanggal']] = $bar['tanggal'];
	$counttgl[$bar['keranimuat']][$bar['tanggal']] = 1;
}

#asisten panen
$str = "select a.nikasisten,a.tanggal,a.kodeorg,b.tipekaryawan,b.lokasitugas from " . $dbname . ".kebun_aktifitas a 
		left join " . $dbname . "." . $table_hist . " b on a.nikasisten=b.karyawanid  where 
		b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") " . $tpkar1 . " and
		tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' and nikasisten!='' and (nospk='' or nospk is null) " . $whereHistB . "  ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	//bentuk tanggal dulu
	$dtmandor[$bar['nikasisten']] = $bar['nikasisten'];
	$dttgl[$bar['tanggal']] = $bar['tanggal'];
	$counttgl[$bar['nikasisten']][$bar['tanggal']] = 1;
}

#### bentuk gapok dari absensi spl_aktifitas
#mandor spl
$str = "select a.nikmandor,a.tanggal,a.kodeorg,b.tipekaryawan,b.lokasitugas from " . $dbname . ".vhc_spl_aktifitas a 
		left join " . $dbname . "." . $table_hist . " b on a.nikmandor=b.karyawanid  where 
		b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") " . $tpkar1 . " and
		tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' and nikmandor!=''  and (nospk='' or nospk is null)  " . $whereHistB . " ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	//bentuk tanggal dulu
	$dtmandor[$bar['nikmandor']] = $bar['nikmandor'];
	$dttgl[$bar['tanggal']] = $bar['tanggal'];
	$counttgl[$bar['nikmandor']][$bar['tanggal']] = 1;
}

#mandor 1 spl
$str = "select a.nikmandor1,a.tanggal,a.kodeorg,b.tipekaryawan,b.lokasitugas from " . $dbname . ".vhc_spl_aktifitas a 
		left join " . $dbname . "." . $table_hist . " b on a.nikmandor1=b.karyawanid  where 
		b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") " . $tpkar1 . " and
		tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' and nikmandor1!='' and (nospk='' or nospk is null) " . $whereHistB . " ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	//bentuk tanggal dulu
	$dtmandor[$bar['nikmandor1']] = $bar['nikmandor1'];
	$dttgl[$bar['tanggal']] = $bar['tanggal'];
	$counttgl[$bar['nikmandor1']][$bar['tanggal']] = 1;
}

#kerani muat spl
$str = "select a.keranimuat,a.tanggal,a.kodeorg,b.tipekaryawan,b.lokasitugas from " . $dbname . ".vhc_spl_aktifitas a 
		left join " . $dbname . "." . $table_hist . " b on a.keranimuat=b.karyawanid  where 
		b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") " . $tpkar1 . " and
		tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' and keranimuat!='' and (nospk='' or nospk is null)  " . $whereHistB . " ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	//bentuk tanggal dulu
	$dtmandor[$bar['keranimuat']] = $bar['keranimuat'];
	$dttgl[$bar['tanggal']] = $bar['tanggal'];
	$counttgl[$bar['keranimuat']][$bar['tanggal']] = 1;
}

#assisten spl
$str = "select a.nikasisten,a.tanggal,a.kodeorg,b.tipekaryawan,b.lokasitugas from " . $dbname . ".vhc_spl_aktifitas a 
		left join " . $dbname . "." . $table_hist . " b on a.nikasisten=b.karyawanid  where 
		b.karyawanid in (select karyawanid from " . $table_hist . " where lokasitugas = '" . $param['kodeorg'] . "' " . $whereHistX . ") " . $tpkar1 . " and
		tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' and nikasisten!='' and (nospk='' or nospk is null) " . $whereHistB . "  ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	//bentuk tanggal dulu
	$dtmandor[$bar['nikasisten']] = $bar['nikasisten'];
	$dttgl[$bar['tanggal']] = $bar['tanggal'];
	$counttgl[$bar['nikasisten']][$bar['tanggal']] = 1;
}

if (isset($dtmandor)) {
	foreach ($dtmandor as $karid) {
		foreach ($dttgl as $tgl) {
			if ($tipekaryawan[$karid] == 4) {
				$gapokbhl[$karid] += $counttgl[$karid][$tgl] * $gajiperhari[$karid];
				$hk[$karid] += $counttgl[$karid][$tgl];
			}
		}
	}
}

## Catu/Tunjangan Keluarga

## Start catu/tunjangan keluarga
$RpNatura = 0;
$sNatura = "select * from " . $dbname . ".sdm_5hargacatukg where unit='" . $param['kodeorg'] . "' and status='1'";
$rNatura = fetchData($sNatura);
if (count($rNatura) == 0) {
	exit('warning: Harga catu per kg belum diinput silahkan input pada setup harga catu per kg');
} else {
	$RpNatura = $rNatura[0]['nilai'];
}

$RpNaturaperkelompok = array();
$PotRpNaturaperkelompok = array();
$sNatura = "select * from " . $dbname . ".sdm_5catu where kodeorg='" . $param['kodeorg'] . "' and tahun='" . substr($tanggal1, 0, 4) . "'";
$rNatura = fetchData($sNatura);
if (count($rNatura) == 0) {
	exit('warning: Kelompok Catu Kg belum disetup, silahkan disetup terlebih dahulu');
} else {
	foreach ($rNatura as $key  => $val) {
		$RpNaturaperkelompok[$val['kelompok']] = ($val['jumlah'] * $RpNatura);
		$PotRpNaturaperkelompok[$val['kelompok']] = (($val['jumlah'] * $RpNatura) / 25);
	}
}

$tjkeluarga = 0;
$dtcatu = count($kodecatu);
if ($dtcatu > 0) {
	foreach ($kodecatu as $bar => $value) {
		$tjkeluarga = $RpNaturaperkelompok[$value];

		## Potongan Natura
		if ($hktdkbyr[$bar] != '') {
			$potNatura = ($PotRpNaturaperkelompok[$value] * $hktdkbyr[$bar]);

			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => substr($tanggal1, 0, 7),
				'karyawanid' => $bar,
				'idkomponen' => 134, //pot. tj keluarga
				'jumlah' => $potNatura,
				'pengali' => 1,
				'hk' => 0
			);
		}

		## Tunjangan Natura
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $bar,
			'idkomponen' => 118, //tj. keluarga
			'jumlah' => $tjkeluarga,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

## End Catu/Tunjangan Keluarga


####################################################################################################################
@$dtkarhktdkbayar = count($lstKary);
if (@$dtkarhktdkbayar > 0) {
	foreach (@$lstKary as $idx => $val) {

		if ($hktdkbyr[$val] == '') {
			@$hktdkbyr[$val] = 0;
		}

		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $val,
			'idkomponen' => 37, //potongan hk
			'jumlah' => $tdkdibayar[$val] + $upahpenalty[$val],
			'pengali' => 1,
			'hk' => $hktdkbyr[$val]
		);
	}
}

#add gapok BHL to ready data
foreach ($gapokbhl as $key => $val) {
	if ($val > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $key,
			'idkomponen' => 1, #kode komponen gapok
			'jumlah' => $val,
			'pengali' => 1,
			'hk' => (is_null($hk[$key]) ? 0 : $hk[$key])
		);
	}
}


foreach ($pesangon as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 89,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

foreach ($kompensasi as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 98,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

foreach ($penambah_gaji as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 141,
			'jumlah' => ($row - $pengurang_gaji[$idx]),
			'pengali' => 1,
			'hk' => 0
		);
	}
}

foreach ($uangpisah as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 97,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

foreach ($premi as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 32,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

if (!empty($premikontanan)) foreach ($premikontanan as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 31,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

if (!empty($potkontanan)) foreach ($potkontanan as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 43,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

foreach ($penalty as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 34,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

// @$cpenaltytraksi=count($penaltytraksi);
// if($cpenaltytraksi>0){
// 	foreach ($penaltytraksi as $idx => $row) {
// 		#add to ready data================================================
// 		if ($row > 0) {
// 			$readyData[] = array(
// 				'kodeorg' => $param['kodeorg'],
// 				'periodegaji' => $param['periodegaji'],
// 				'karyawanid' => $idx,
// 				'idkomponen' => 68,
// 				'jumlah' => $row,
// 				'pengali' => 1,
// 				'hk'=>0);
// 		}
// 	}
// }

foreach ($penaltykehadiran as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $idx,
			'idkomponen' => 41,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

#7. premi   : sdm_premi
$str = "select a.karyawanid,a.premi,a.jenis from " . $dbname . ".sdm_premi a left join 
				  " . $dbname . "." . $table_hist . " b on a.karyawanid=b.karyawanid 
				   where  1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') 
				   and periode='" . substr($tanggal1, 0, 7) . "' " . $whereHistB . " group by a.karyawanid,a.jenis";
$res = fetchData($str);
foreach ($res as $idx => $row) {
	if (isset($id[$row['karyawanid']])) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => $row['jenis'],
			'jumlah' => $row['premi'],
			'pengali' => 1,
			'hk' => 0
		);
	} else {
		//abaikan jika tidak terdaftar pada karyawanid  
	}
}

$rapelgaji = array();
##pendapatan lainnya
$str = "select * from " . $dbname . ".sdm_pendapatanlaindt where kodeorg='" . $param['kodeorg'] . "' and periodegaji='" . substr($tanggal1, 0, 7) . "' "
	. " and karyawanid in (select karyawanid from " . $dbname . "." . $table_hist . " where 1=1 " . $tpkar1 . " and"
	. " (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi=0  and"
	. " (tanggalmasuk<='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)  " . $whereHistX . ") and posting='1'";
$res = fetchData($str);

$tanggalTerakhir = date('Y-m-t', strtotime($tanggal1));

if ($tanggal1 == $tanggalTerakhir || $tanggal2 == $tanggalTerakhir) {

	foreach ($res as $idx => $val) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $val['karyawanid'],
			'idkomponen' => $val['idkomponen'],
			'jumlah' => $val['jumlah'],
			'pengali' => 1,
			'hk' => 0
		);
		if ($val['idkomponen'] == '14') {
			$rapelgaji[$val['karyawanid']] = $val['jumlah'];
		}
	}
}


#######################################################################################################################################
### Star BPJS

$sUmpDaerah = "select distinct jumlah from " . $dbname . ".sdm_5gajipokok where tahun='" . substr($tanggal1, 0, 4) . "' and idkomponen='87' and kodeorg='" . $param['kodeorg'] . "'";
$rUmpDaerah = fetchData($sUmpDaerah);
$umpDaerah = $rUmpDaerah[0]['jumlah'];

if ($umpDaerah == '' or $umpDaerah == 0) {
	exit("Warning UMP Daerah belum disetup (SDM->SETUP->ABSENSI DAN PENGGAJIAN->UMP DAERAH) Tahun : " . substr($tanggal1, 0, 4) . ", Unit : " . $param['kodeorg'] . " ");
}

$optTipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$tipeOrg = $optTipe[$param['kodeorg']];
$bpjsorg = $tipeOrg;
#= parameter aplikasi 

#= kerja
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSKER'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$arrbpjs = explode(',', $bar['nilai']);
foreach ($arrbpjs as $key) {
	$arrker[] = $key;
}

#= kesehatan
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSKES'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$arrbpjs = explode(',', $bar['nilai']);
foreach ($arrbpjs as $key) {
	$arrkes[] = $key;
}


#= pensiun
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSPEN'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$arrbpjs = explode(',', $bar['nilai']);
foreach ($arrbpjs as $key) {
	$arrpen[] = $key;
}



## Tambah Natura
foreach ($readyData as $val) {
	if ($val['idkomponen'] == '118') {
		$natura_karyawan[$val['karyawanid']] = $val['jumlah'];
	}
}

$kdpt = $getPT[$param['kodeorg']];

#= Check apakah ada tipekaryawan yang tidak terdaftar di komponen bpjs
$str = "select * from " . $dbname . ".sdm_5komponenbpjspengali where kodeorg='" . $kdpt . "' and status='1' ";
$resbpjs = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$komponenx = array();
$komponen = array();
foreach ($resbpjs as $rPlbr) {
	$komponenx = explode(',', $rPlbr['komponengaji']);
	foreach ($komponenx as $key => $val) {
		$komponen[$rPlbr['tipekaryawan']][$val] = $val;
	}
}

# Kondisi ambil nilai pengganti selain UMP yang di setupkan di komponen bpjs
if (!empty($komponen)) {
	$nilaiPengganti = array();
	$sbpjs = "select jumlah, karyawanid, idkomponen from " . $dbname . ".sdm_5gajipokok where tahun='" . substr($tanggal1, 0, 7) . "' and kodeorg='" . $param['kodeorg'] . "' and karyawanid in ('" . implode("','", $karydidatakary) . "')";
	$rbpjs = fetchData($sbpjs);
	foreach ($rbpjs as $barbar) {
		if (!empty($komponen[$tipekaryawan[$barbar['karyawanid']]][$barbar['idkomponen']])) {

			$nilaiPengganti[$barbar['karyawanid']] += $barbar['jumlah'];
		}
	}
}


#= Ketenagakerjaan JKK
$str = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='" . $bpjsorg . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {

	#= kerja
	if (in_array($bar['jenisbpjs'], $arrker)) {
		foreach ($umrbulanan as $key => $nilai) {
			if ($bpjstenaga[$key] != '') {

				if ($tipekaryawan[$key] != 4) {
					$nilai = $nilai + $natura_karyawan[$key];
				} else {
					$nilai = $umpDaerah;
				}

				#Kondisi Perhitungan BPJS selain PALMA
				if (!empty($nilaiPengganti[$key])) {
					$nilai = $nilaiPengganti[$key] + $natura_karyawan[$key];
					if ($nilai < $umpDaerah) {
						$nilai = $umpDaerah;
					} else {
						$nilai = $nilai;
					}
				}


				if ($nilai > $bar['maxgaji']) {
					if ($bar['maxgaji'] > 0) {
						$nilai = $bar['maxgaji'];
					} else {
						$nilai = $nilai;
					}
				} else {
					$nilai = $nilai;
				}

				$bebankaryawan = $bar['bebankaryawan'];
				$bebanperusahaan = $bar['bebanperusahaan'];

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => substr($tanggal1, 0, 7),
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjs'],
					'jumlah' => ($bebankaryawan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => substr($tanggal1, 0, 7),
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjsplus'],
					'jumlah' => ($bebanperusahaan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);
			}
		}
	}

	#= kesehatan
	if (in_array($bar['jenisbpjs'], $arrkes)) {
		#= jika diparameter aplikasi diset 0 maka akan ambil dari gapok
		foreach ($umrbulanan as $key => $nilai) {
			if ($bpjskes[$key] != '') {

				if ($tipekaryawan[$key] != 4) {
					$nilai = $nilai + $natura_karyawan[$key];
				} else {
					$nilai = $umpDaerah;
				}
				#Kondisi Perhitungan BPJS selain PALMA

				if (!empty($nilaiPengganti[$key])) {
					$nilai = $nilaiPengganti[$key] + $natura_karyawan[$key];
					if ($nilai < $umpDaerah) {
						$nilai = $umpDaerah;
					} else {
						$nilai = $nilai;
					}
				}


				if ($nilai > $bar['maxgaji']) {
					if ($bar['maxgaji'] > 0) {
						$nilai = $bar['maxgaji'];
					} else {
						$nilai = $nilai;
					}
				} else {
					$nilai = $nilai;
				}

				$bebankaryawan = $bar['bebankaryawan'];
				$bebanperusahaan = $bar['bebanperusahaan'];

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => substr($tanggal1, 0, 7),
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjs'],
					'jumlah' => ($bebankaryawan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => substr($tanggal1, 0, 7),
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjsplus'],
					'jumlah' => ($bebanperusahaan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);
			}
		}
	}

	#= pensiun
	if (in_array($bar['jenisbpjs'], $arrpen)) {
		foreach ($umrbulanan as $key => $nilai) {
			if ($bpjspensiun[$key] != '') {

				if ($tipekaryawan[$key] != 4) {
					$nilai = $nilai + $natura_karyawan[$key];
				} else {
					$nilai = $umpDaerah;
				}

				#Kondisi baca tipe karyawan Perhitungan BPJS selain PALMA
				if (!empty($nilaiPengganti[$key])) {
					$nilai = $nilaiPengganti[$key] + $natura_karyawan[$key];
					if ($nilai < $umpDaerah) {
						$nilai = $umpDaerah;
					} else {
						$nilai = $nilai;
					}
				}


				if ($nilai > $bar['maxgaji']) {
					if ($bar['maxgaji'] > 0) {
						$nilai = $bar['maxgaji'];
					} else {
						$nilai = $nilai;
					}
				} else {
					$nilai = $nilai;
				}

				$bebankaryawan = $bar['bebankaryawan'];
				$bebanperusahaan = $bar['bebanperusahaan'];

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => substr($tanggal1, 0, 7),
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjs'],
					'jumlah' => ($bebankaryawan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => substr($tanggal1, 0, 7),
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjsplus'],
					'jumlah' => ($bebanperusahaan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);
			}
		}
	}
}

### End BPJS
#########################################################################################################################################


$idkomponen_list = [];
foreach ($readyData as $item) {
	$idkomponen_list[] = $item['idkomponen'];
}

$idkomponen_unique = array_unique($idkomponen_list);

// Menyusun string dengan pemisah koma dan tanda kutip
$inkomponen = implode(",", array_map(function ($item) {
	return "'" . $item . "'";
}, $idkomponen_unique));


$strx = "select * FROM " . $dbname . ".sdm_ho_component where id in (" . $inkomponen . ")";
$res = fetchData($strx);
foreach ($res as $bar) {
	if ($bar['plus'] == 1) {

		$idsxc = array('70', '71', '72', '73', '80');
		if (!in_array($bar['id'], $idsxc)) {
			@$dtkomplus[$bar['id']] = $bar['id'];
		}
	} else {
		@$dtkommin[$bar['id']] = $bar['id'];
	}
	$nmkom[$bar['id']] = $bar['name'];
}


$new_jumlah = [];
foreach ($readyData as $item) {
	$karyawanid = $item['karyawanid'];
	$idkomponen = $item['idkomponen'];
	$jumlah = $item['jumlah'];

	if (!isset($new_jumlah[$karyawanid])) {
		$new_jumlah[$karyawanid] = [];
	}

	// BPJS
	$ids = array('3', '61', '67', '44', '81', '70', '71', '72', '73', '80');
	if (in_array($idkomponen, $ids)) {
		$new_jumlah[$karyawanid][$idkomponen] = $jumlah;
	} else {
		$new_jumlah[$karyawanid][$idkomponen] = $jumlah;
	}
}

$colspan_komplus = count($dtkomplus);
$colspan_komin = count($dtkommin);

array_multisort($dtkomplus, SORT_ASC);
array_multisort($dtkommin, SORT_ASC);

$namakomponen = makeOption($dbname, "sdm_ho_component", "id,name");
$list0 = "<table class=sortable border=0 cellspacing=1 cellpadding=5><thead>";
$list0 .= "<tr align=center class=rowheader>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['nomor'] . "</th>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['periodegaji'] . "</th>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['nik'] . "</th>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['namakaryawan'] . "</th>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['statuspajak'] . "</th>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['jabatan'] . "</th>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['tipekaryawan'] . "</th>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['subbagian'] . "</th>";
$list0 .= "<th rowspan=2 >" . $_SESSION['lang']['departemen'] . "</th>";
$list0 .= "<th colspan = " . ($colspan_komplus + 1) . " >" . $_SESSION['lang']['penambah'] . "</th>";
$list0 .= "<th rowspan=2>Total Penambah</th>";
$list0 .= "<th colspan = " . $colspan_komin . " >" . $_SESSION['lang']['pengurang'] . "</th>";
$list0 .= "<th rowspan=2>Total Pengurang</th>";
$list0 .= "<th rowspan=2>Total Gaji</th>";
$list0 .= "</tr>";
$list0 .= "<tr>";
foreach ($dtkomplus as $komplus) {
	$list0 .= "<th align=center >" . $nmkom[$komplus] . "</th>";
}
$list0 .= "<th align=center >Pembulatan Gaji</th>";
foreach ($dtkommin as $kommin) {
	$list0 .= "<th align=center >" . $nmkom[$kommin] . "</th>";
}
$list0 .= "</tr>";
$list0 .= "</thead><tbody>";

## Periksa Gaji Minus
$negatif = false;
$list1 = '';
$listx = "<fieldset style='margin-top: 10px; padding: 10px;'><legend><b>TIDAK DAPAT PROSES GAJI, MASIH ADA GAJI DIBAWAH 0 : </b></legend></fieldset>";
$list2 = '';
$list3 = '';
$no = 0;

if ($readyData < 1) {
	exit("Error:Data Kosong");
}

$strx = "select id as komponen, case plus when 0 then -1 else plus end as pengali,name as nakomp FROM " . $dbname . ".sdm_ho_component";
$comRes = fetchData($strx);
$comp = array();
$nakomp = array();
foreach ($comRes as $idx => $row) {
	$comp[$row['komponen']] = $row['pengali'];
	$nakomp[$row['komponen']] = $row['nakomp'];
}


foreach ($id as $key => $val) {
	$sisa[$val[0]] = 0;

	foreach ($readyData as $dat => $bar) {
		if ($val[0] == $bar['karyawanid']) {
			$idsxc = array('70', '71', '72', '73', '80');
			if (!in_array($bar['idkomponen'], $idsxc)) {
				$sisa[$val[0]] += $bar['jumlah'] * $comp[$bar['idkomponen']];
			}
		} else {
			continue;
		}
	}


	## Jika Gaji Minus Tampilkan yang minus saja
	if ($sisa[$val[0]] < 0) {

		$nmSubbagian = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $subbagianKry[$val[0]] . "'");
		$optNmDep = makeOption($dbname, 'sdm_5departemen', 'kode,nama');

		$tmpSubbagian = '';
		if (isset($nmSubbagian[$subbagianKry[$val[0]]]) && isset($bagianKry[$val[0]])) {
			$tmpSubbagian .= $nmSubbagian[$subbagianKry[$val[0]]] . " [" . $bagianKry[$val[0]] . "]";
		} else if (isset($nmSubbagian[$subbagianKry[$val[0]]])) {
			$tmpSubbagian .= $nmSubbagian[$subbagianKry[$val[0]]];
		} else if (isset($bagianKry[$val[0]])) {
			$tmpSubbagian .= "[{$bagianKry[$val[0]]}]";
		}

		$nox++;
		$list1 .= "<tr class=rowcontent>";
		$list1 .= "<td align=center>" . $nox . "</td>";
		$list1 .= "<td align=center>" . substr($tanggal1, 0, 7) . "</td>";
		$list1 .= "<td>" . $nikKry[$val[0]] . "</td>";
		$list1 .= "<td>" . $namakar[$val[0]] . "</td>";
		$list1 .= "<td>" . $statuspajak[$val[0]] . "</td>";
		$list1 .= "<td>" . $kodejabatanx[$val[0]] . "</td>";
		$list1 .= "<td>" . $tipekaryawanx[$val[0]] . "</td>";
		$list1 .= "<td>{$tmpSubbagian}</td>";
		$list1 .= "<td>" . $optNmDep[$bagianKry[$val[0]]] . "</td>";


		foreach ($dtkomplus as $komplus) {

			$idsxc = array('70', '71', '72', '73', '80');
			if (!in_array($komplus, $idsxc)) {
				$tt_jumkomplusxc[$val[0]] += $new_jumlah[$val[0]][$komplus];
			}
		}


		foreach ($dtkommin as $kommin) {
			$tt_jumkominxc[$val[0]] += $new_jumlah[$val[0]][$kommin];
		}

		$totalgaji[$val[0]] = round(($tt_jumkomplusxc[$val[0]] - $tt_jumkominxc[$val[0]]), 0);
		$dibelakang[$val[0]] = intval(substr(strval($totalgaji[$val[0]]), -2));

		if ($dibelakang[$val[0]] == 0) {
			$penambahdibelakang[$val[0]] = 0;
		} else {
			$penambahdibelakang[$val[0]] = 100 - $dibelakang[$val[0]];
		}

		foreach ($dtkomplus as $komplus) {
			$list1 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$komplus], 0) . "</td>";

			$idsxc = array('70', '71', '72', '73', '80');

			if (!in_array($komplus, $idsxc)) {
				$tt_jumkomplus[$val[0]] += $new_jumlah[$val[0]][$komplus];
			}
		}

		$tt_jumkomplus[$val[0]] += $penambahdibelakang[$val[0]];

		$list1 .= "<td align=right>" . number_format($penambahdibelakang[$val[0]], 0) . "</td>";

		$list1 .= "<td align=right>" . number_format($tt_jumkomplus[$val[0]], 0) . "</td>";

		foreach ($dtkommin as $kommin) {
			$list1 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$kommin], 0) . "</td>";
			$tt_jumkomin[$val[0]] += $new_jumlah[$val[0]][$kommin];
		}

		$list1 .= "<td align=right>" . number_format($tt_jumkomin[$val[0]], 0) . "</td>";

		$ttt_gaji[$val[0]] = $tt_jumkomplus[$val[0]] - $tt_jumkomin[$val[0]];

		$list1 .= "<td style = color:red align=right>" . number_format($ttt_gaji[$val[0]], 0) . "</td>";

		$negatif = false;

		## Jika Gaji Tidak Minus
	} else {

		$optNmDep = makeOption($dbname, 'sdm_5departemen', 'kode,nama');
		$nmSubbagian = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $subbagianKry[$val[0]] . "'");
		$tmpSubbagian = '';
		if (isset($nmSubbagian[$subbagianKry[$val[0]]]) && isset($bagianKry[$val[0]])) {
			$tmpSubbagian .= $nmSubbagian[$subbagianKry[$val[0]]] . " [" . $bagianKry[$val[0]] . "]";
		} else if (isset($nmSubbagian[$subbagianKry[$val[0]]])) {
			$tmpSubbagian .= $nmSubbagian[$subbagianKry[$val[0]]];
		} else if (isset($bagianKry[$val[0]])) {
			$tmpSubbagian .= "[{$bagianKry[$val[0]]}]";
		}

		$no += 1;
		$list2 .= "<tr class=rowcontent>";
		$list2 .= "<td align=center>" . $no . "</td>";
		$list2 .= "<td align=center>" . substr($tanggal1, 0, 7) . "</td>";
		$list2 .= "<td>" . $nikKry[$val[0]] . "</td>";
		$list2 .= "<td>" . $namakar[$val[0]] . "</td>";
		$list2 .= "<td>" . $statuspajak[$val[0]] . "</td>";
		$list2 .= "<td>" . $kodejabatanx[$val[0]] . "</td>";
		$list2 .= "<td>" . $tipekaryawanx[$val[0]] . "</td>";
		$list2 .= "<td>{$tmpSubbagian}</td>";
		$list2 .= "<td>" . $optNmDep[$bagianKry[$val[0]]] . "</td>";




		foreach ($dtkomplus as $komplus) {

			$idsxc = array('70', '71', '72', '73', '80');

			if (!in_array($komplus, $idsxc)) {
				$tt_jumkomplusxc[$val[0]] += $new_jumlah[$val[0]][$komplus];
			}
		}


		foreach ($dtkommin as $kommin) {
			$tt_jumkominxc[$val[0]] += $new_jumlah[$val[0]][$kommin];
		}

		$totalgaji[$val[0]] = round(($tt_jumkomplusxc[$val[0]] - $tt_jumkominxc[$val[0]]), 0);
		$dibelakang[$val[0]] = intval(substr(strval($totalgaji[$val[0]]), -2));
		//echo $totalgaji[$val[0]].'<br>';
		if ($dibelakang[$val[0]] == 0) {
			$penambahdibelakang[$val[0]] = 0;
		} else {
			$penambahdibelakang[$val[0]] = 100 - $dibelakang[$val[0]];
		}

		$readyData[] = array(
			'kodeorg' => $lokasitugas[$val[0]],
			'periodegaji' => substr($tanggal1, 0, 7),
			'karyawanid' => $val[0],
			'idkomponen' => '131',
			'jumlah' => $penambahdibelakang[$val[0]],
			'pengali' => 1,
			'hk' => 0
		);


		foreach ($dtkomplus as $komplus) {
			$list2 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$komplus], 0) . "</td>";
			$idsxc = array('70', '71', '72', '73', '80');
			if (!in_array($komplus, $idsxc)) {
				$tt_jumkomplus[$val[0]] += $new_jumlah[$val[0]][$komplus];
			}
		}

		$tt_jumkomplus[$val[0]] += $penambahdibelakang[$val[0]];
		$list2 .= "<td align=right>" . number_format($penambahdibelakang[$val[0]], 0) . "</td>";

		$list2 .= "<td align=right>" . number_format($tt_jumkomplus[$val[0]], 0) . "</td>";

		foreach ($dtkommin as $kommin) {
			$list2 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$kommin], 0) . "</td>";
			$tt_jumkomin[$val[0]] += $new_jumlah[$val[0]][$kommin];
		}

		$list2 .= "<td align=right>" . number_format($tt_jumkomin[$val[0]], 0) . "</td>";

		$ttt_gaji[$val[0]] = $tt_jumkomplus[$val[0]] - $tt_jumkomin[$val[0]];

		$list2 .= "<td align=right>" . number_format($ttt_gaji[$val[0]], 0) . "</td>";

		$list2 .= "</tr>";
	}
}

$list3 = "</tbody><table>";

switch ($method) {
	case 'preview':
		if ($tipeprint == 'html') {
			if ($negatif) {
				echo $listx . $list0 . $list1 . $list3;
			} else {
				echo $listbutton . $list0 . $list2 . $list3;
			}
		} else {
			$stream = '';
			if ($negatif) {
				$stream .= $listx . $list0 . $list1 . $list3;
			} else {
				$stream .= $listbutton . $list0 . $list2 . $list3;
			}

			$tglSkrg = date("Ymd");
			$nop_ = "ESTIMASI_GAJI";
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
						window.location='tempExcel/" . $nop_ . ".xls';
						</script>";
				}
				fclose($handle);
			}
		}
		break;
	default:
		break;
}
