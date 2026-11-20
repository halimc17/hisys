<?php
# CABANG PPP dari vhc_slave_updateFlag.php (Proses Akhir Bulan > 8. Alokasi Traksi dan Bengkel: tanda alokasi per periode). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai vhc_slave_updateFlag.php. Perubahan khusus PPP tandai "#PPP".
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$param = $_POST;
#PPP: periode divalidasi sebelum dipakai di query
if (!isset($param['periode']) || !is_string($param['periode']) || !preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $param['periode'])) {
    exit('Warning : Parameter periode tidak valid.');
}
$periode=$param['periode'];
$kodetraksi=addslashes($_SESSION['empl']['lokasitugas']);
$str="insert into ".$dbname.".vhc_flag_alokasi(kodetraksi,periode) values('".$kodetraksi."','".$periode."')";
try{$owlPDO->exec($str); }
catch (PDOException $e) {
    //ignore
}
?>