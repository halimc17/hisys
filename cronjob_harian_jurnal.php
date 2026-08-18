<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include('kebun_slave_3pusingan_otomatis.php');
include('setup_pindahapproval.php');

$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

if($param['upload'] == '') {
	$param['upload'] = 'auto'; # Default Auto [Manual, Auto]
}

if($param['upload']=='auto') {
	$persekarang = date("Y-m");
	$periodeberikut = periodeberikut($persekarang);
} else {
	$persekarang = $param['periode'];
	$periodeberikut = periodeberikut($persekarang);
}

// $str = "select * from " . $dbname . ".keu_5kelompokjurnal where periode='" . $persekarang . "' ";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $res->fetch()) {
// 	$str1 = "select count(*) as jumlah from " . $dbname . ".keu_5kelompokjurnal where 
// 		kodeorg	='" . $bar['kodeorg'] . "' and
// 		kodeunit='" . $bar['kodeunit'] . "' and
// 		kodekelompok='" . $bar['kodekelompok'] . "' and
// 		periode='" . $periodeberikut . "'";
// 	$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
// 	$res1->setFetchMode(PDO::FETCH_ASSOC);
// 	$bar1 = $res1->fetch();

// 	if ($bar1['jumlah'] == '0') {
// 		$data = "insert into " . $dbname . ".keu_5kelompokjurnal(kodeorg, kodeunit, kodekelompok, periode, keterangan, nokounter)
// 			   values('" . $bar['kodeorg'] . "','" . $bar['kodeunit'] . "','" . $bar['kodekelompok'] . "','" . $periodeberikut . "','" . $bar['keterangan'] . "','0')";
// 		$owlPDO->exec($data);
// 	}
// }

$query = "INSERT INTO keu_5kelompokjurnal
			(
				kodeorg,
				kodeunit,
				kodekelompok,
				periode,
				keterangan,
				nokounter
			)
			SELECT
				o.induk              AS kodeorg,
				o.kodeorganisasi     AS kodeunit,
				p.jurnalid           AS kodekelompok,
				'{$periodeberikut}'	 AS periode,
				p.keterangan,
				0 AS nokounter
			FROM (
				-- jurnal normal
				SELECT jurnalid, keterangan
				FROM keu_5parameterjurnal
				WHERE jurnalid NOT IN ('PJPD', 'SLE', 'PNB') -- Khusus Palma, karena duplicate

				UNION ALL

				-- jurnal duplicate khusus
				SELECT jurnalid, MAX(keterangan) AS keterangan
				FROM keu_5parameterjurnal
				WHERE jurnalid IN ('PJPD', 'SLE', 'PNB') -- Khusus Palma, karena duplicate
				GROUP BY jurnalid
			) p
			JOIN organisasi o
				ON LENGTH(o.kodeorganisasi) = 4
			LEFT JOIN keu_5kelompokjurnal k
				ON k.kodeorg       = o.induk
			AND k.kodeunit      = o.kodeorganisasi
			AND k.kodekelompok  = p.jurnalid
			AND k.periode       = '{$periodeberikut}'
			WHERE k.kodeorg IS NULL";
$owlPDO->exec($query);