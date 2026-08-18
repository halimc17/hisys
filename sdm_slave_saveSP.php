<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$jenissp = checkPostGet('jenissp', '');
$karyawanid = checkPostGet('karyawanid', '');
$masaberlaku = checkPostGet('masaberlaku', '');
$tanggalsp = tanggalsystem(checkPostGet('tanggalsp', ''));

$paragraf1 = checkPostGet('paragraf1', '');
$paragraf2 = checkPostGet('paragraf2', '');
$paragraf3 = checkPostGet('paragraf3', '');
$paragraf4 = checkPostGet('paragraf4', '');

$pelanggaran = checkPostGet('pelanggaran', '');
$penandatangan = checkPostGet('penandatangan', '');
$jabatan = checkPostGet('jabatan', '');
$tembusan1 = checkPostGet('tembusan1', '');
$tembusan2 = checkPostGet('tembusan2', '');
$tembusan3 = checkPostGet('tembusan3', '');
$tembusan4 = checkPostGet('tembusan4', '');
$method = checkPostGet('method', '');
$kodeorg = substr($_SESSION['empl']['lokasitugas'], 0, 4);
$verifikasi = checkPostGet('verifikasi', '');
$dibuat = checkPostGet('dibuat', '');
$jabatan1 = checkPostGet('jabatan1', '');
$jabatan2 = checkPostGet('jabatan2', '');

$menimbang = checkPostGet('menimbang', '');
$mengingat = checkPostGet('mengingat', '');
$mendengar = checkPostGet('mendengar', '');

$t = mktime(0, 0, 0, substr($tanggalsp, 4, 2) + $masaberlaku, substr($tanggalsp, 6, 2), substr($tanggalsp, 0, 4));
$sampai = date('Ymd', $t);

if ($method == 'selectsp') {
    if ($jenissp == 'SP1') {
		echo readTextFile('config/sp_format/sp_paragraf1_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf2_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf3_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf4_SP1.lst') ;
    } else if ($jenissp == 'SP2') {
        echo readTextFile('config/sp_format/sp_paragraf1_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf2_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf3_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf4_SP1.lst') ;
    } else if ($jenissp == 'SP3') {
        echo readTextFile('config/sp_format/sp_paragraf1_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf2_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf3_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf4_SP1.lst') ;
    } else if ($jenissp == 'ST') {
        echo readTextFile('config/sp_format/sp_paragraf1_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf2_SP1.lst') . "###" . readTextFile('config/sp_format/st_paragraf3.lst') . "###" . readTextFile('config/sp_format/sp_paragraf4_SP1.lst') ;
    } else if ($jenissp == 'PHK') {
        echo readTextFile('config/sp_format/sp_paragraf1_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf2_SP1.lst') . "###" . readTextFile('config/sp_format/sp_paragraf2_SP1.lst') . "###" . readTextFile('config/sp_format/phk_paragraf4.lst') ;
    }
} else {

    if ($method == 'insert') {

       
        if ($jenissp == 'SP1') {
            $js = 'SP.I';
        } else if ($jenissp == 'SP2') {
            $js = 'SP.II';
        } else if ($jenissp == 'SP3') {
            $js = 'SP.III';
        } else if($js ='ST'){
            $js = 'ST';
        } else if($js ='PHK'){
            $js = 'PHK';
        }

        //get pt
        $skodept="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' and left(kodeorganisasi,2)='".substr($_SESSION['empl']['lokasitugas'], 0, 2)."'";
        $qkodept=$owlPDO->query($skodept) or die(print "Gagal: ".PDOException::getMessage());
        $qkodept->setFetchMode(PDO::FETCH_OBJ);
        $rkodept=$qkodept->fetch();
        $kodept=$rkodept->kodeorganisasi;

        $bulan = substr($tanggalsp, 4, 2);
        $tahun = substr($tanggalsp, 0, 4);
        $arrayRomawi = array("I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII");
        $resultRomawi = $arrayRomawi[(int) $bulan - 1];
        $ql = "select `nomor` from " . $dbname . ".`sdm_suratperingatan` where year(tanggal)='" . $tahun . "'";
        $qr = $owlPDO->query($ql) or die(print " Gagal: " . PDOException::getMessage());
        $ql2 = "select `nomor` from " . $dbname . ".`sdm_suratperingatan` where jenissp='" . $jenissp . "' and year(tanggal)='" . $tahun . "'";
        $qr2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());

        $countNoGlobal = owlBaris($qr);
        $countNo = owlBaris($qr2);

        $noSP =$kodept ."/". addZero($countNoGlobal + 1, 3) . "/" . $js . "-" . addZero($countNo + 1, 3) . "/" . $resultRomawi . "/" . $tahun;
       
        
            $data = array(
                'nomor'      => $noSP,
                'jenissp'    => $jenissp,
                'karyawanid' => $karyawanid,
                'pelanggaran'=> $pelanggaran,
                'tanggal'    => $tanggalsp,
                'masaberlaku'=> $masaberlaku,
                'sampai'     => $sampai,
                'tembusan1'  => $tembusan1,
                'tembusan2'  => $tembusan2,
                'tembusan3'  => $tembusan3,
                'tembusan4'  => $tembusan4,
                'kodeorg'    => $kodeorg,
                'penandatangan' => $penandatangan,
                'jabatan'       => $jabatan,
                'updateby'      => $_SESSION['standard']['userid'],
                'paragraf1'     => $paragraf1,
                'paragraf2'     => $paragraf2,
                'paragraf3'     => $paragraf3,
                'paragraf4'     => $paragraf4,
                'verifikasi'    => $verifikasi,
                'dibuat'        => $dibuat,
                'jabatanverifikasi'    => $jabatan1,
                'jabatandibuat'        => $jabatan2,
                'menimbang'            => "",
                'mengingat'            => "",
                'mendengar'            => ""
            );
            
            $cols = array();
            foreach($data as $key=>$row) {
                $cols[] = $key;
            }

            $query = insertQuery($dbname,'sdm_suratperingatan',$data,$cols);

            try {
                $owlPDO->exec($query);                
            } catch (PDOException $e) {
                echo " Gagal:" . addslashes($e->getMessage());
            }
	 
    } else if ($method == 'delete') {
        $nosp = $_POST['nosp'];
        $strdelete = "delete from " . $dbname . ".sdm_suratperingatan
              where karyawanid=" . $karyawanid . " and nomor='" . $nosp . "'";

        try {
                $owlPDO->exec($strdelete);
            } catch (PDOException $e) {
                echo " Gagal:" . addslashes($e->getMessage());
            }

        $strdeletefile = "delete from " . $dbname . ".listfile_sdm_suratperingatan
              where karyawanid=" . $karyawanid . " and notransaksi='" . $nosp . "'";

         try {
                $owlPDO->exec($strdeletefile);
            } catch (PDOException $e) {
                echo " Gagal:" . addslashes($e->getMessage());
            }
    } else if ($method == 'update') {

        $nosp = $_POST['nosp'];
               
        $data = array(
            'pelanggaran'=> $pelanggaran,
            'tanggal'    => $tanggalsp,
            'masaberlaku'=> $masaberlaku,
            'sampai'     => $sampai,
            'tembusan1'  => $tembusan1,
            'tembusan2'  => $tembusan2,
            'tembusan3'  => $tembusan3,
            'tembusan4'  => $tembusan4,
            'kodeorg'    => $kodeorg,
            'penandatangan' => $penandatangan,
            'jabatan'       => $jabatan,
            'updateby'      => $_SESSION['standard']['userid'],
            'paragraf1'     => $paragraf1,
            'paragraf2'     => $paragraf2,
            'paragraf3'     => $paragraf3,
            'paragraf4'     => $paragraf4,
            'verifikasi'    => $verifikasi,
            'dibuat'        => $dibuat,
            'jabatanverifikasi'    => $jabatan1,
            'jabatandibuat'        => $jabatan2,
            'menimbang'            => "",
            'mengingat'            => "",
            'mendengar'            => ""
        );
        
        $where = "nomor='".$nosp."'";
        $query = updateQuery($dbname,'sdm_suratperingatan',$data,$where);

        try {
            $owlPDO->exec($query);
        } catch (PDOException $e) {
            echo " Gagal:" . addslashes($e->getMessage());
        }
    } else if ($method == 'posting') {
        $nosp = $_POST['nosp'];
        $data = array(
            'postingby' => $_SESSION['standard']['userid'],
            'posting'   => 1
        );
        $where = "nomor='" . $nosp . "'";
        $query = updateQuery($dbname, 'sdm_suratperingatan', $data, $where);

        try {
            $owlPDO->exec($query);
        } catch (PDOException $e) {
            echo " Gagal:" . addslashes($e->getMessage());
        }
    }
}
    ?>