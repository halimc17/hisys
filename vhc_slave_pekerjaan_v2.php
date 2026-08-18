<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$param          = $_POST;
if (count($param) == 0) {
    $param = $_GET;
}

$param['jns_kerja'] = trim($param['jns_kerja']);

$lokasikerja    = $_SESSION['empl']['lokasitugas'];
$user_entry     = $_SESSION['standard']['userid'];
$proses         = checkPostGet('proses', '');
if (isset($param['tglpekerjaan'])) {
    $tgl_kerja  = tanggalsystem($param['tglpekerjaan']);
}



$optkelvhc      = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,kelompokvhc');
$optpt          = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$nmpek          = makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,namakegiatan');
$arrJab         = array("0" => "Operator", "1" => "Helper", "2" => "Driver");

if (isset($param['no_trans'])) {
    $optKdVhc       = makeOption($dbname, 'vhc_runht', 'notransaksi,kodevhc,kodeorg', "notransaksi = '" . $param['no_trans'] . "'");
    $sKode = selectQuery($dbname, 'vhc_runht', '*', "notransaksi='" . $param['no_trans'] . "'");
    @$rKode = fetchData($sKode)[0];
}

switch ($proses) {

    case 'getsubunit':
        $optSubUnit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $param['kodeorg'] . "' and tipe= 'TRAKSI' order by kodeorganisasi";
        $res = fetchdata($str);
        foreach ($res as $val) {
            $optSubUnit .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
        }

        echo $optSubUnit;
        break;

    case 'getjenisvhc':
        $optjns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

        // Ambil nama jenis kendaraan
        $nmtp = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');

        // Ambil data jenis kendaraan dari master
        $str = selectQuery($dbname, 'vhc_5master', 'jenisvhc', "kodetraksi='" . $param['kodetraksi'] . "'", '', true);
        $res = fetchData($str);

        // Ambil mapping jenisvhc ke kelompokvhc
        $klpvhc = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,kelompokvhc');

        // Definisi label optgroup
        $arrjenis = array(
            'AB' => array('id' => 'Alat Berat', 'en' => 'Heavy Equipment'),
            'KD' => array('id' => 'Kendaraan', 'en' => 'Vehicle'),
            'MS' => array('id' => 'Mesin', 'en' => 'Machinery')
        );

        $n = ''; // Kelompok sebelumnya
        foreach ($res as $bar) {
            $jenis = $bar['jenisvhc'];
            $kelompok = isset($klpvhc[$jenis]) ? $klpvhc[$jenis] : '';

            // Jika kelompok berubah, tutup optgroup sebelumnya dan buka baru
            if ($kelompok != $n) {
                if ($n != '') {
                    $optjns .= "</optgroup>";
                }

                if (isset($arrjenis[$kelompok])) {
                    $label = ($_SESSION['language'] != 'EN') ? $arrjenis[$kelompok]['id'] : $arrjenis[$kelompok]['en'];
                    $optjns .= "<optgroup label='" . $label . "'>";
                }

                $n = $kelompok;
            }

            // Tambah opsi jenis
            $optjns .= "<option value='" . $jenis . "'>" . $nmtp[$jenis] . "</option>";
        }

        // Tutup optgroup terakhir jika perlu
        if ($n != '') {
            $optjns .= "</optgroup>";
        }

        ## MANDOR
        $optMandor = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sbrg = "select * from " . $dbname . ".vhc_5mandortraksi where kodetraksi = '" . $param['kodetraksi'] . "'";
        $rbrg = fetchData($sbrg);
        foreach ($rbrg as $val) {
            $optMandor .= "<option value=" . $val['karyawanid'] . ">[" . getNik($val['karyawanid']) . "] - " . getNamaKaryawan($val['karyawanid']) . "</option>";
        }

        echo $optjns . "###" . $optMandor;
        break;
    case 'getKodeVhc':

        $optkodekendaraan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $kdVhc = '';
        if (isset($param['no_trans']) == '') {
            $sql = selectQuery($dbname, 'vhc_5master', 'kodevhc,kodetraksi,nopol,detailvhc', "jenisvhc='" . $param['jenisvhc'] . "' and kodetraksi like '%" . $param['kodetraksi'] . "%' and status=1");
        } else {
            $sVhc = selectQuery($dbname, 'vhc_runht', 'jenisvhc,kodevhc', "notransaksi='" . $param['no_trans'] . "'");
            $rVhc = fetchData($sVhc)[0];
            $kdVhc = $rVhc['kodevhc'];
            $sql = selectQuery($dbname, 'vhc_5master', 'kodevhc,kodetraksi,nopol,detailvhc', "jenisvhc='" . $rVhc['jenisvhc'] . "' ");
        }
        $bar = fetchData($sql);
        foreach ($bar as $res) {
            if ($kdVhc == $res['kodevhc']) {
                $sel = "selected";
            } else {
                $sel = "";
            }
            $optkodekendaraan .= "<option value='" . $res['kodevhc'] . "' $sel>" . $res['kodevhc'] . " " . ($res['nopol'] != '' ? "- " . $res['nopol'] : '') . " - " . $res['kodetraksi'] . " " . ($res['detailvhc'] != '' ? "- " . $res['detailvhc'] : '') . "</option>";
        }

        echo $optkodekendaraan;
        break;
    case 'getkodekegiatan':
        $optkodvhc = makeOption($dbname, 'vhc_5master', 'kodevhc,kelompokvhc');
        $kelvhc = $optkodvhc[$param['kodevhc']];
        $n = '';
        $sjnskrj = "select * from " . $dbname . ".vhc_kegiatan where tipe ='traksi' and LEFT(kodekegiatan, 3) != '111' and (kelompokvhc='" . $kelvhc . "' or kelompokvhc='GLOBAL') and (jenisvhc='" . $param['jenisvhc'] . "' or jenisvhc='GLOBAL') and status='1' order by noakun asc";
        $optJnsKerja = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $res = $owlPDO->query($sjnskrj) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $d = '';
        while ($rjnskrj = $res->fetch()) {
            $d = substr($rjnskrj['kodekegiatan'], 0, 5);
            if ($d != $n) {
                $nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $d . "'");
                $optJnsKerja .= "<optgroup label='" . $nmorg[$d] . "'>";
            }
            $optJnsKerja .= "<option value=" . $rjnskrj['kodekegiatan'] . ">" . $rjnskrj['kodekegiatan'] . " - " . $rjnskrj['namakegiatan'] . "</option>";
            if ($d != $n) {
                $n = $d;
                $optJnsKerja .= "</optgroup>";
            }
        }

        $sql = selectQuery($dbname, 'vhc_rundt', 'jenispekerjaan', "notransaksi='" . $param['no_trans'] . "'");
        $res = fetchData($sql)[0];

        echo $optJnsKerja . "###" . $res['jenispekerjaan'];
        break;
    case 'getData':
        $sql = "select * from " . $dbname . ".vhc_runht where notransaksi='" . $param['no_trans'] . "'";
        $res1 = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        $res = $res1->fetch();

        $sql = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "'";
        $res1 = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        $resdt = $res1->fetch();

        $sSpk = "select tanggal from " . $dbname . ".log_spkht where notransaksi='" . $res['notransaksi'] . "'";
        $res1 = $owlPDO->query($sSpk) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        $resPk = $res1->fetch();
        $thn = substr($resPk['tanggal'], 0, 4);
        $optKntrk = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sSpk2 = "select notransaksi from " . $dbname . ".log_spkht where kodeorg='" . $lokasikerja . "' and posting<>'0' and tanggal like '%" . $thn . "%'";
        $res1 = $owlPDO->query($sSpk2) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        while ($rSpk = $res1->fetch()) {
            $optKntrk .= "<option value=" . $rSpk['notransaksi'] . " " . ($rSpk['notransaksi'] == $param['no_trans'] ? 'selected' : '') . ">" . $rSpk['notransaksi'] . "</option>";
        }


        ## Ambil operator
        $optKary = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $skary = "select distinct(a.karyawanid),a.nama,b.nik,a.jabatan from " . $dbname . ".vhc_5operator a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.aktif='1' and a.vhc = '" . $res['kodevhc'] . "'and a.jabatan in ('0','2') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . $res['tanggal'] . "')  order by a.jabatan,a.nama"; //echo $skary;
        $res1 = $owlPDO->query($skary) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        while ($rkary = $res1->fetch()) {
            $optKary .= "<option value=" . $rkary['karyawanid'] . ">" . $rkary['nama'] . "&nbsp; [" . $arrJab[$rkary['jabatan']] . "] &nbsp;[" . $rkary['nik'] . "]</option>";
        }

        ## Ambil helper
        $optKary_Help = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $skary = "select distinct(a.karyawanid),a.nama,b.nik,a.jabatan from " . $dbname . ".vhc_5operator a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.aktif='1' and a.vhc = '" . $res['kodevhc'] . "'and a.jabatan in ('1') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . $res['tanggal'] . "')  order by a.jabatan,a.nama"; //echo $skary;
        $res1 = $owlPDO->query($skary) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        while ($rkary = $res1->fetch()) {
            $optKary_Help .= "<option value=" . $rkary['karyawanid'] . ">" . $rkary['nama'] . "&nbsp; [" . $arrJab[$rkary['jabatan']] . "] &nbsp;[" . $rkary['nik'] . "]</option>";
        }

        #= Mandor
        // $optMandor="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sbrg = "select * from " . $dbname . ".vhc_5mandortraksi where karyawanid = '" . $res['mandor'] . "'";
        $rbrg = fetchData($sbrg);
        foreach ($rbrg as $val) {
            $optMandor .= "<option value=" . $val['karyawanid'] . ">[" . getNik($val['karyawanid']) . "] - " . getNamaKaryawan($val['karyawanid']) . "</option>";
        }

        //cari traksi
        $sTraksi = "select distinct kodetraksi from " . $dbname . ".vhc_5master where kodevhc='" . $res['kodevhc'] . "'";
        $res1 = $owlPDO->query($sTraksi) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        $rTraksi = $res1->fetch();

        $sbrg = "select * from " . $dbname . ".vhc_5jenisvhc where jenisvhc = '" . $res['jenisvhc'] . "'";
        $rbrg = fetchData($sbrg);
        foreach ($rbrg as $val) {
            $optJnsVhc .= "<option value=" . $val['jenisvhc'] . ">" . $val['jenisvhc'] . " - " . $val['namajenisvhc'] . "</option>";
        }

        echo $res['notransaksi'] . "####" . $res['kodeorg'] . "####" . $rTraksi['kodetraksi'] . "####" . $optJnsVhc . "####" . tanggalnormal($res['tanggal']) . "####" . $optKary . "####" . $res['jenisbbm'] . "####" . $res['jlhbbm'] . "####" . $res['kontanan'] . "####" . $res['kodevhc'] . "####" . $optMandor . "####" . $optKary_Help;

        break;
    case 'getKmAkhir':
        // Get Data
        $qKm = selectQuery($dbname, 'vhc_kmhm_track', '*', "kodevhc='" . $_POST['kodevhc'] . "'");
        $resKm = fetchData($qKm);
        if (empty($resKm))
            echo 0;
        else
            echo $resKm[0]['kmhmakhir'];
        break;
    case 'getPremi':

        ## Cek config premi pakek table yang mana
        $sconfig = "select * from " . $dbname . ".vhc_5configpremi where pt = '" . getindukPT($param['kodeorg']) . "' and aktif = 1";
        $res = $owlPDO->query($sconfig) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $rConfig = $res->fetch();

        if ($rConfig['setuppremi'] == '2') {
            echo hitungpremi_table2($param);
        } else {
            echo hitungpremi($param);
        }

        break;
    case 'getSatuan':
        $strSat = selectQuery($dbname, 'vhc_kegiatan', 'satuan', "kodekegiatan='{$param['jns_kerja']}'");
        $resSat = fetchData($strSat)[0];

        #== Ambil lokasi 
        $optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sOrg   = selectQuery($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "length(kodeorganisasi)='4' and tipe in ('PABRIK','KEBUN','HOLDING')");
        $rOrg   = fetchData($sOrg);
        foreach ($rOrg as $row => $lsDt) {
            // if(substr($lsDt['kodeorganisasi'],0,4)==$_SESSION['empl']['lokasitugas']){
            if (substr($lsDt['kodeorganisasi'], 0, 4) == $param['kdkbn']) {
                $optOrg .= "<option value='" . $lsDt['kodeorganisasi'] . "' selected>" . $lsDt['kodeorganisasi'] . " - " . $lsDt['namaorganisasi'] . "</option>";
            } else {
                $optOrg .= "<option value='" . $lsDt['kodeorganisasi'] . "'>" . $lsDt['kodeorganisasi'] . " - " . $lsDt['namaorganisasi'] . "</option>";
            }
        }

        echo $resSat['satuan'] . "####" . $optOrg;
        break;
    case 'getdept':
        $kepalapek = substr($param['jns_kerja'], 0, 1);
        if ($kepalapek == 7 || $kepalapek == 8) {
            $sdept = "select * from " . $dbname . ".sdm_5departemen where aktif ='1' order by nama asc";
            $optdept = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
            $res = $owlPDO->query($sdept) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($rdept = $res->fetch()) {
                $optdept .= "<option value=" . $rdept['kode'] . ">" . $rdept['kode'] . " - " . $rdept['nama'] . "</option>";
            }
        } else {
            $optdept = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        }

        echo $optdept;
        break;
    case 'getBlok':
        #== Ambil kelompok dari kegiatan traksi
        $sAlokasi = selectQuery($dbname, 'vhc_kegiatan', 'kelompok', "kodekegiatan='{$param['jns_kerja']}' and tipe='traksi'");
        $rAlokasi = fetchData($sAlokasi)[0];

        #== Ambil statusblok berdasarkan kelompok kegiatan traksi
        if ($rAlokasi['kelompok'] == 'PNN') {
            $statusblok = " and statusblok = 'TM'";
        } else if ($rAlokasi['kelompok'] == 'LC') {
            $statusblok = " and statusblok IN ('LC','TBM')";
        } else {
            $statusblok = " and statusblok = '" . $rAlokasi['kelompok'] . "'";
        }

        $optBlok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        if ($rAlokasi['kelompok'] == 'MIL') {
            $sBlok = selectQuery($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "induk like '%{$param['lokasi_kerja']}%' and tipe='STATION'", "tipe desc,kodeorganisasi");
        } elseif ($rAlokasi['kelompok'] == 'EXT') {
            $sBlok = selectQuery($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi,indukblok,namaindukblok', "induk like '%{$param['lokasi_kerja']}%' and (tipe='BLOK' OR tipe='BIBITAN') and kodeorganisasi in 
                (select distinct kodeorg from $dbname.setup_blok where left(indukblok,6)='{$param['lokasi_kerja']}' and luasareaproduktif>0 and statusblok='TM' and status='A') group by indukblok", 'tipe desc,kodeorganisasi');
        } elseif ($rAlokasi['kelompok'] == 'INF') {
            $sBlok = selectQuery($dbname, 'project', 'kode as kodeorganisasi,nama as namaorganisasi', "kodeorg like '%{$param['lokasi_kerja']}%' and posting='0'", 'nama,tanggalmulai desc');
        } else {
            if (getNamaKeg($param['jns_kerja'], 'pilihanluas') == 1) {
                $whrpiluas = "(luasbloking-lc) >0";
            } elseif (getNamaKeg($param['jns_kerja'], 'pilihanluas') == 2) {
                $whrpiluas = "(lc) >0";
            } else {
                $whrpiluas = "(luasareaproduktif) >0";
            }
            $sBlok = selectQuery($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi,indukblok,namaindukblok', "induk like '%{$param['lokasi_kerja']}%' and (tipe='BLOK' OR tipe='BIBITAN') and kodeorganisasi in 
                (select distinct kodeorg from $dbname.setup_blok where left(indukblok,4)='{$param['lokasi_kerja']}' and " . $whrpiluas . " $statusblok and status='A') group by indukblok", 'tipe desc,kodeorganisasi');
        }
        $n = '';
        $resblk = fetchData($sBlok);
        $d = '';
        foreach ($resblk as $rBlok) {
            if ($rAlokasi['kelompok'] != 'INF') {
                $d = substr($rBlok['kodeorganisasi'], 0, 6);
                if ($d != $n) {
                    $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi,indukblok,namaindukblok', "kodeorganisasi='" . $d . "'");
                    $optBlok .= "<optgroup label='" . $nmorg[$d] . "'>";
                }

                if (isset($param['blok']) != "") {
                    if ($rBlok['indukblok'] == isset($param['blok'])) {
                        $optBlok .= "<option value=" . $rBlok['indukblok'] . " selected>" . $rBlok['namaindukblok'] . "</option>";
                    } else {
                        $optBlok .= "<option value=" . $rBlok['indukblok'] . ">" . $rBlok['namaindukblok'] . "</option>";
                    }
                } else {
                    $optBlok .= "<option value=" . $rBlok['indukblok'] . ">" . $rBlok['namaindukblok'] . "</option>";
                }
                if ($d != $n) {
                    $n = $d;
                    $optBlok .= "</optgroup>";
                }
            } else {
                if (isset($param['blok']) != "") {
                    if ($rBlok['indukblok'] == isset($param['blok'])) {
                        $optBlok .= "<option value=" . $rBlok['indukblok'] . " selected>" . $rBlok['namaindukblok'] . "</option>";
                    } else {
                        $optBlok .= "<option value=" . $rBlok['indukblok'] . ">" . $rBlok['namaindukblok'] . "</option>";
                    }
                } else {
                    $optBlok .= "<option value=" . $rBlok['indukblok'] . ">" . $rBlok['namaindukblok'] . "</option>";
                }
            }
        }

        #khusus Project:
        // $str="select kode,nama from  ".$dbname.".project where kodeorg='".$param['lokasi_kerja']."' and posting=0";
        // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_OBJ);
        // $optBlok.="<optgroup label='PROJECT'>";
        // while($bar=$res->fetch()){
        //     $optBlok.="<option value=".$bar->kode.">".$bar->nama." [".$bar->kode."]</option>";
        // }
        // $optBlok.="</optgroup>";

        echo $optBlok . "###" . isset($param['blok']);
        break;
    case 'getUmr':
        if ($_POST['tahun'] != '')
            $tahun = $_POST['tahun'];
        else {
            $tahun = date('Y');
        }

        $tgl_kerja        = tanggalsystemn($param['tglpekerjaan']);
        $err              = '';
        $dan              = '';
        $errhelp          = '';
        $errall           = '0';
        $umr              = 0;
        $umrhelp          = 0;
        $umrhelp2         = 0;
        $umrhelp3         = 0;
        $tipekaryawan     = 'kosong';
        $tipekaryawanhelp = 'kosong';
        $tipekaryawanhelp2 = 'kosong';
        $tipekaryawanhelp3 = 'kosong';


        ## OPERATOR/DRIVER
        if ($param['kode_karyawan'] != '') {
            #cek nilai umr
            $str = selectQuery($dbname, 'sdm_5gajipokok', 'jumlah', "karyawanid='{$param['kode_karyawan']}' and tahun='" . substr($tgl_kerja, 0, 7) . "' and idkomponen='1'");
            $res = fetchData($str)[0];
            $gaji = $res['jumlah'] / 25;
            $umr = $gaji;

            if ($umr == '') {
                exit("Error : Gaji Pokok untuk periode " . substr($tgl_kerja, 0, 7) . " belum ada !");
            }

            $str = " select * from " . $dbname . ".datakaryawan where 1=1 and karyawanid='" . $param['kode_karyawan'] . "'";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                $tipekaryawan = $bar['tipekaryawan'];
            }

            #== cek data apakah sudah ada HK karyawan tersebut di hari yang sama
            $n = 0;
            $qry = selectQuery($dbname, 'vhc_runhk_vw', 'upah,premi,notransaksi', "idkaryawan='" . $param['kode_karyawan'] . "' and tanggal='" . $tgl_kerja . "'");
            $rst = fetchData($qry);
            foreach ($rst as $v) {
                $n++;
            }

            if ($rKode['kontanan'] == 'KONTAN') {
                $str = selectQuery($dbname, 'vhc_5premikegiatan', 'upahkontanan', "kodekegiatan='{$param['jns_kerja']}' and vhc='" . $rKode['jenisvhc'] . "' and jenishari='libur' and statuspremi='1'");
                $rese = fetchData($str)[0];
                $umr = $rese['upahkontanan'];
            }

            #== bagi rata umrnya
            $sql = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and operator='" . $param['kode_karyawan'] . "'";
            $hsl = fetchData($sql);
            $jumkary = count($hsl);
            if (count($rst) > 0 && ($rst[0]['upah'] == 0 || $rst[0]['premi'] == 0)) {
                $umr = ($umr);
            } else {
                $umr = ($umr / ($jumkary + 1));
            }

            #== cek transaksi yang pertama kali ke insert 
            $string = "select distinct notransaksi from " . $dbname . ".vhc_rundt where operator='" . $param['kode_karyawan'] . "' ";
            $result = fetchdata($string);
            $notransada = [];
            foreach ($result as $val) {
                array_push($notransada, $val['notransaksi']);
            }

            if (isset($rst[0]['upah']) == 0 && count($rst) == 0 && $n == 1) {
                $umr = $umr;
            }

            if (isset($rst[0]['upah']) > 0 && count($rst) > 0 && $n > 0) {
                // if(in_array($param['no_trans'],$notransada)){
                $err = 'Operator';
                $umr = 0;
                // }
            }
            if ($param['proses_pekerjaan'] == 'update_kerja') {
                $umr = $param['uphOprt'];
            }
        }


        ## HELPER 1
        if ($param['kode_helper'] != '') {

            #UMR HELPER 1
            $str = selectQuery($dbname, 'sdm_5gajipokok', 'jumlah', "karyawanid='{$param['kode_helper']}' and tahun='" . substr($tgl_kerja, 0, 7) . "' and idkomponen='1'");
            $res = fetchData($str)[0];
            $gaji = $res['jumlah'] / 25;
            $umrhelp = $gaji;

            if ($umrhelp == '') {
                exit("Error : Gaji Pokok untuk periode " . substr($tgl_kerja, 0, 7) . " belum ada !");
            }

            #== cek data apakah sudah ada HK karyawan tersebut di hari yang sama
            $n = 0;
            $qry = selectQuery($dbname, 'vhc_runhk_vw', 'upah,premi,notransaksi', "idkaryawan='" . $param['kode_helper'] . "' and tanggal='" . $tgl_kerja . "'");
            $rst = fetchData($qry);
            foreach ($rst as $v) {
                $n++;
            }

            #== bagi rata umrnya
            $sql = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and operator='" . $param['kode_helper'] . "'";
            $hsl = fetchData($sql);
            $jumkary = count($hsl);
            if (count($rst) > 0 && ($rst[0]['upah'] == 0 || $rst[0]['premi'] == 0)) {
                $umrhelp = ($umrhelp);
            } else {
                $umrhelp = ($umrhelp / ($jumkary + 1));
            }

            #== cek transaksi yang pertama kali ke insert 
            $string = "select distinct notransaksi from " . $dbname . ".vhc_rundt where operator='" . $param['kode_helper'] . "' ";
            $result = fetchdata($string);
            $notransada = [];
            foreach ($result as $val) {
                array_push($notransada, $val['notransaksi']);
            }

            if (isset($rst[0]['upah']) == 0 && count($rst) == 0 && $n == 1) {
                $umrhelp = $umrhelp;
            }

            if (isset($rst[0]['upah']) > 0 && count($rst) > 0 && $n > 0) {
                $errhelp = 'Helper';
                $umrhelp = 0;
            }

            if ($param['proses_pekerjaan'] == 'update_kerja') {
                $umrhelp = $param['uphHelp'];
            }
        }

        ## HELPER 2
        if ($param['kode_helper2'] != '') {

            #UMR HELPER 2
            $str = selectQuery($dbname, 'sdm_5gajipokok', 'jumlah', "karyawanid='{$param['kode_helper2']}' and tahun='" . substr($tgl_kerja, 0, 7) . "' and idkomponen='1'");
            $res = fetchData($str)[0];
            $gaji = $res['jumlah'] / 25;
            $umrhelp2 = $gaji;

            if ($umrhelp2 == '') {
                exit("Error : Gaji Pokok untuk periode " . substr($tgl_kerja, 0, 7) . " belum ada !");
            }

            #== cek data apakah sudah ada HK karyawan tersebut di hari yang sama
            $n = 0;
            $qry = selectQuery($dbname, 'vhc_runhk_vw', 'upah,premi,notransaksi', "idkaryawan='" . $param['kode_helper2'] . "' and tanggal='" . $tgl_kerja . "'");
            $rst = fetchData($qry);
            foreach ($rst as $v) {
                $n++;
            }

            #== bagi rata umrnya
            $sql = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and operator='" . $param['kode_helper2'] . "'";
            $hsl = fetchData($sql);
            $jumkary = count($hsl);
            if (count($rst) > 0 && ($rst[0]['upah'] == 0 || $rst[0]['premi'] == 0)) {
                $umrhelp2 = ($umrhelp2);
            } else {
                $umrhelp2 = ($umrhelp2 / ($jumkary + 1));
            }

            #== cek transaksi yang pertama kali ke insert 
            $string = "select distinct notransaksi from " . $dbname . ".vhc_rundt where operator='" . $param['kode_helper2'] . "' ";
            $result = fetchdata($string);
            $notransada = [];
            foreach ($result as $val) {
                array_push($notransada, $val['notransaksi']);
            }

            if (isset($rst[0]['upah']) == 0 && count($rst) == 0 && $n == 1) {
                $umrhelp2 = $umrhelp2;
            }

            if (isset($rst[0]['upah']) > 0 && count($rst) > 0 && $n > 0) {
                $errhelp = 'Helper2';
                $umrhelp2 = 0;
            }

            if ($param['proses_pekerjaan'] == 'update_kerja') {
                $umrhelp2 = $param['uphHelp2'];
            }
        }

        if ($err != '' || $errhelp != '') {
            if ($err != '' && $errhelp != '') {
                $dan = ' dan';
            }
            $errall = "Upah " . $err . "" . @$dan . " " . $errhelp . " sudah terdaftar ditransaksi lain pada tangal yang sama, kolom upah akan terisi 0";
        }

        #= Cek dihari biasa libur atau minggu
        $strhr   = "SELECT * FROM " . $dbname . ".sdm_5harilibur where tanggal = '" . $tgl_kerja . "' and (kebun='GLOBAL' or kebun='" . substr($param['no_trans'], 0, 4) . "')";
        $hslhr   = fetchdata($strhr);
        $ketlibur = isset($hslhr[0]['keterangan']);
        if ($ketlibur == '') {
            $ketlibur = 'kerja';
        }

        if ($ketlibur == 'cuti bersama') {
            $ketlibur = 'libur';
        }

        $sql = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and jenispekerjaan='" . $param['jns_kerja'] . "' and (operator='" . $param['kode_helper2'] . "' or helper='" . $param['kode_helper'] . "' or helper2='" . $param['kode_helper2'] . "' or helper3='" . $param['kode_helper2'] . "')";
        $hsl = fetchData($sql);
        $jlhkaryperpekerjaan = count($hsl);

        // if($libur==true){
        //     @$umr=0;
        //     @$umrhelp=0;
        // }else{
        //     @$umr=$umr;
        //     @$umrhelp=$umrhelp;
        // }

        ## Ambil config premi
        $sconfig = "select * from " . $dbname . ".vhc_5configpremi where pt = '" . getindukPT($param['kodeorg']) . "' and aktif = 1";
        $res = $owlPDO->query($sconfig) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $rConfig = $res->fetch();

        $setupPremi = $rConfig['setuppremi'];


        echo $umr . "####" . $umrhelp . "####" . $umrhelp2 . "####" . $umrhelp3 . "####" . $tipekaryawan . "####" . $tipekaryawanhelp . "####" . $tipekaryawanhelp2 . "####" . $tipekaryawanhelp3 . "####" . $errall . "####" . $ketlibur . "####" . $jlhkaryperpekerjaan . "####" . $setupPremi;

        break;
    case 'cariTransaksi':
        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $where = "";
        if ($param['tgl_cari'] != '') {
            $txtTgl = tanggalsystem($param['tgl_cari']);
            $txt_tgl_a = substr($txtTgl, 0, 4);
            $txt_tgl_b = substr($txtTgl, 4, 2);
            $txt_tgl_c = substr($txtTgl, 6, 2);
            $txtTgl = $txt_tgl_a . "-" . $txt_tgl_b . "-" . $txt_tgl_c;
            if ($param['tgl_carisd'] != '') {
                $where .= " AND tanggal BETWEEN " . $txtTgl . " AND " . $param['tgl_carisd'] . " ";
            } else {
                $where .= " and tanggal='" . $txtTgl . "'";
            }
        }
        if ($param['txtCari'] != '') {
            $where .= " and notransaksi like '%" . trim($param['txtCari']) . "%'";
        }
        if ($param['kodevhc_cari'] != '') {
            $where .= " and kodevhc like '%" . trim($param['kodevhc_cari']) . "%'";
        }
        if ($param['kontanan_cari'] != '') {
            $where .= " and kontanan = '" . $param['kontanan_cari'] . "'";
        }

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".vhc_runht where substr(kodeorg,1,4) regexp '" . str_replace(',', '|', str_replace("'", "", getOrgDetail(2))) . "' " . $where . " order by tanggal desc";
        $sql = "select * from " . $dbname . ".vhc_runht where  substr(kodeorg,1,4) regexp '" . str_replace(',', '|', str_replace("'", "", getOrgDetail(2))) . "' " . $where . " order by tanggal desc limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $res->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        $res7 = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res7->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $res7->fetch()) {
            $sSpk = "select tanggal from " . $dbname . ".log_spkht where notransaksi='" . $res['notransaksi'] . "'";
            $res1 = $owlPDO->query($sSpk) or die(print " Gagal: " . PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $rSpk = $res1->fetch();
            $thn = substr($rSpk['tanggal'], 0, 4);

            $sbrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $res['jenisbbm'] . "'";
            $res1 = $owlPDO->query($sbrg) or die(print " Gagal: " . PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $rbrg = $res1->fetch();
            $rbrg['namabarang'];
            $no += 1;

            $optnopol = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol', "kodevhc='" . $res['kodevhc'] . "'");
            $optdet = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc', "kodevhc='" . $res['kodevhc'] . "'");
            $optjns = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc', "jenisvhc='" . $res['jenisvhc'] . "'");

            echo "
                <tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=center>" . $res['notransaksi'] . "</td>
                <td align=center>" . $res['jenisvhc'] . " - " . $optjns[$res['jenisvhc']] . "</td>
                <td align=center>" . $res['kodevhc'] . "</td>
                <td align=center>" . $optnopol[$res['kodevhc']] . "</td>
                <td align=left>" . $optdet[$res['kodevhc']] . "</td>
                <td align=center>" . tanggalnormal($res['tanggal']) . "</td>
                <td align=center>" . $rbrg['namabarang'] . "</td>
                <td align=center>" . $res['jlhbbm'] . "</td>
                ";
            if ($res['posting'] == 1) {
                echo "<td width=25px></td><td width=25px></td><td align=center width=25px><img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','" . $res['notransaksi'] . "," . $res['kodevhc'] . "','','vhc_slave_pekerjaanPrint',event);\"></td>";
            } else {
                echo "
                        <td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                        onclick=\"fillField('" . $res['notransaksi'] . "','" . $thn . "');\"></td>
                        <td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delHead('" . $res['notransaksi'] . "','" . $param['page'] . "');\" >	</td>
                        <td align=center width=25px><img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','" . $res['notransaksi'] . "," . $res['kodevhc'] . "','','vhc_slave_pekerjaanPrint',event);\">
                        </td>";
            }
        }
        break;
    case 'loaddata':
        $limit      = 15;
        $colspan    = 21;
        $page       = 0;
        $tgl_carisd = tanggalsystemn(checkPostGet('tgl_carisd', ''));
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $where = "";
        if ($param['tgl_cari'] != '') {
            $txtTgl = tanggalsystem($param['tgl_cari']);
            $txt_tgl_a = substr($txtTgl, 0, 4);
            $txt_tgl_b = substr($txtTgl, 4, 2);
            $txt_tgl_c = substr($txtTgl, 6, 2);
            $txtTgl = $txt_tgl_a . "-" . $txt_tgl_b . "-" . $txt_tgl_c;
            if ($tgl_carisd != '--') {
                $where .= " AND tanggal BETWEEN '" . $txtTgl . "' AND '" . $tgl_carisd . "' ";
            } else {
                $where .= " and tanggal='" . $txtTgl . "'";
            }
        }
        if ($param['txtCari'] != '') {
            $where .= " and notransaksi like '%" . trim($param['txtCari']) . "%'";
        }
        if ($param['kodevhc_cari'] != '') {
            $where .= " and kodevhc like '%" . trim($param['kodevhc_cari']) . "%'";
        }
        if ($param['kontanan_cari'] != '%') {
            $where .= " and kontanan = '" . $param['kontanan_cari'] . "'";
        }

        $query  = "select count(*) as jmlhrow from " . $dbname . ".vhc_runht where substr(kodeorg,1,4) regexp '" . str_replace(',', '|', str_replace("'", "", getOrgDetail(2))) . "' " . $where . " order by tanggal desc,posting asc"; // echo $ql2;
        $result = fetchData($query);
        foreach ($result as $val) {
            $jlhbrs = $val['jmlhrow'];
        }

        $sql = "select * from " . $dbname . ".vhc_runht where substr(kodeorg,1,4) regexp '" . str_replace(',', '|', str_replace("'", "", getOrgDetail(2))) . "' " . $where . " order by tanggal desc,posting asc limit " . $offset . "," . $limit . "";
        $hsl = fetchData($sql);
        if (count($hsl) > 0) {
            $no = 0;
            $no = $maxdisplay;
            $bar = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
            $bar->setFetchMode(PDO::FETCH_ASSOC);
            while ($res = $bar->fetch()) {
                $sSpk = "select tanggal from " . $dbname . ".log_spkht where notransaksi='" . $res['notransaksi'] . "'";
                $res1 = $owlPDO->query($sSpk) or die(print " Gagal: " . PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $rSpk = $res1->fetch();
                $thn = substr($rSpk['tanggal'], 0, 4);

                $sbrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $res['jenisbbm'] . "'";
                $res1 = $owlPDO->query($sbrg) or die(print " Gagal: " . PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $rbrg = $res1->fetch();

                $svhc = "select detailvhc,nopol from " . $dbname . ".vhc_5master where kodevhc='" . $res['kodevhc'] . "'";
                $res2 = $owlPDO->query($svhc) or die(print " Gagal: " . PDOException::getMessage());
                $res2->setFetchMode(PDO::FETCH_ASSOC);
                $rvhc = $res2->fetch();

                $sdtx = "SELECT SQL_CALC_FOUND_ROWS `notransaksi`, GROUP_CONCAT(`operator`) as aroperator, GROUP_CONCAT(`helper`) as arhelper, GROUP_CONCAT(`helper2`) as arhelper2, GROUP_CONCAT(`helper3`) as arhelper3
                           FROM " . $dbname . ".vhc_rundt
                           WHERE notransaksi='" . $res['notransaksi'] . "'
                           GROUP BY `notransaksi`";
                $resdtx = $owlPDO->query($sdtx) or die(print " Gagal: " . PDOException::getMessage());
                $resdtx->setFetchMode(PDO::FETCH_ASSOC);
                $rdtx = $resdtx->fetch();

                $nmjenis = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc', "jenisvhc='" . $res['jenisvhc'] . "'");
                $no += 1;

                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=center>" . $no . "</td>";
                $tab .= "<td align=center>" . $res['notransaksi'] . "</td>";
                $tab .= "<td align=center>" . $res['jenisvhc'] . " - " . $nmjenis[$res['jenisvhc']] . "</td>";
                $tab .= "<td align=center>" . $res['kodevhc'] . "</td>";
                $tab .= "<td align=center>" . $rvhc['nopol'] . "</td>";
                $tab .= "<td align=center>" . $rvhc['detailvhc'] . "</td>";
                $tab .= "<td align=center>" . getNamaKaryawan($res['mandor']) . "</td>";
                $datasupirarr = explode(',', $rdtx['aroperator']);
                $datasupir = array();
                $tab .= "<td align=center>";
                for ($i = 0; $i < count($datasupirarr); $i++) {
                    if ($datasupirarr[$i] != '0000000000') {
                        if (!isset($datasupir[$datasupirarr[$i]])) {
                            $tab .= getNamaKaryawan($datasupirarr[$i]) . "</br>";
                            $datasupir[$datasupirarr[$i]] = 1;
                        }
                    }
                }
                $tab .= "</td>";
                $datasupirarr = explode(',', $rdtx['arhelper']);
                $datasupir = array();
                $tab .= "<td align=center>";
                for ($i = 0; $i < count($datasupirarr); $i++) {
                    if ($datasupirarr[$i] != '0000000000') {
                        if (!isset($datasupir[$datasupirarr[$i]])) {
                            $tab .= getNamaKaryawan($datasupirarr[$i]) . "</br>";
                            $datasupir[$datasupirarr[$i]] = 1;
                        }
                    }
                }
                $tab .= "</td>";
                $datasupirarr = explode(',', $rdtx['arhelper2']);
                $datasupir = array();
                $tab .= "<td align=center>";
                for ($i = 0; $i < count($datasupirarr); $i++) {
                    if ($datasupirarr[$i] != '0000000000') {
                        if (!isset($datasupir[$datasupirarr[$i]])) {
                            $tab .= getNamaKaryawan($datasupirarr[$i]) . "</br>";
                            $datasupir[$datasupirarr[$i]] = 1;
                        }
                    }
                }
                $tab .= "</td>";
                $datasupirarr = explode(',', $rdtx['arhelper3']);
                $datasupir = array();
                $tab .= "<td align=center>";
                for ($i = 0; $i < count($datasupirarr); $i++) {
                    if ($datasupirarr[$i] != '0000000000') {
                        if (!isset($datasupir[$datasupirarr[$i]])) {
                            $tab .= getNamaKaryawan($datasupirarr[$i]) . "</br>";
                            $datasupir[$datasupirarr[$i]] = 1;
                        }
                    }
                }
                $tab .= "</td>";
                $tab .= "<td align=center>" . tanggalnormal($res['tanggal']) . "</td>";
                $tab .= "<td align=center>" . $rbrg['namabarang'] . "</td>";
                $tab .= "<td align=center>" . $res['jlhbbm'] . "</td>";
                $tab .= "<td align=center>" . ($res['kontanan'] != '' ? 'YA' : 'TIDAK') . "</td>";
                $tab .= "<td align=center>" . getNamaKaryawan($res['updateby']) . "</td>";
                $tab .= "<td align=center>" . $res['createdtime'] . "</td>";
                if ($res['posting'] == 1) {
                    $tab .= "<td></td>";
                    $tab .= "<td></td>";
                    // if(in_array($_SESSION['empl']['jabatan'],$personPosting,true)){
                    // $icon="images/icons/04/16/04.png";
                    // $title="Unposting";
                    // }else {
                    $icon = "images/icons/04/16/02.png";
                    $title = "Posted";
                    // }
                    $tab .= "<td  align=center width=25px><img src=" . $icon . " class=zImgBtn height='30'  title='" . $title . "'></td>";
                    $tab .= " <td align=center>
                                    <img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','" . $res['notransaksi'] . "," . $res['kodevhc'] . "','','vhc_slave_pekerjaanPrint',event);\">
                                </td>";
                } else {
                    $tab .= " <td align=center>
                                    <img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $res['notransaksi'] . "','" . $thn . "');\">
                                </td>";
                    $tab .= " <td align=center>
                                    <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delHead('" . $res['notransaksi'] . "');\" >
                                </td>";
                    $tab .= " <td align=center width=25px><img src=images/skyblue/posting.png class=zImgBtn height='30'  title='Posting' onclick=\"postingdata('" . $res['notransaksi'] . "','" . $res['kodevhc'] . "','" . $res['tanggal'] . "','" . $page . "');\"></td>";
                    $tab .= " <td align=center>
                                    <img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','" . $res['notransaksi'] . "," . $res['kodevhc'] . "','','vhc_slave_pekerjaanPrint',event);\">
                                </td>";
                }
            }
        } else {
            $tab .= "<tr class=rowcontent><td colspan=" . $colspan . " align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
        }
        $totrows = ceil($jlhbrs / $limit);
        $footd = createpaging($jlhbrs, $limit, $page, $colspan, 'loaddata', 'getPage');
        echo $tab . "##" . $footd;
        break;
    case 'loaddetail':
        $ttlrit         = 0;
        $ttlprestasi    = 0;
        $ttlkmhm        = 0;
        $ttluphoprt     = 0;
        $ttlprmioprt    = 0;
        $ttluphhelp     = 0;
        $ttlprmihelp    = 0;
        $ttluphhelp2    = 0;
        $ttlprmihelp2   = 0;
        $ttluphhelp3    = 0;
        $ttlprmihelp3   = 0;
        $ttldenda       = 0;

        #- Ambil Pt Untuk hidden kolom helper
        $str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='TRDH' ";
        $res = fetchdata($str);
        @$arrpt = explode(',', $res[0]['nilai']);

        $hiddenHelper = '';
        if (in_array($_SESSION['empl']['kodeorganisasi'], $arrpt)) {
            $hiddenHelper = "hidden";
        } else {
            $hiddenHelper = '';
        }

        $sql = "select a.*,b.namasegment from " . $dbname . ".vhc_rundt a left join " . $dbname . ".keu_5segment b on a.kodesegment=b.kodesegment where substring(notransaksi,1,4)='" . $rKode['kodeorg'] . "' and notransaksi='" . $param['no_trans'] . "' order by kmhmawal asc"; // echo $sql;
        $no = 0;
        $hsl = fetchData($sql);
        if (count($hsl) > 0) {
            foreach ($hsl as $res) {
                $nmpek = makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan = '" . $res['jenispekerjaan'] . "'");
                $kdkndraan = makeOption($dbname, 'vhc_runht', 'notransaksi,kodevhc', "notransaksi = '" . $res['notransaksi'] . "'");
                $tglnya = makeOption($dbname, 'vhc_runht', 'notransaksi,tanggal', "notransaksi = '" . $res['notransaksi'] . "'");

                #= Cek dihari biasa libur atau minggu
                $strhr   = "SELECT * FROM " . $dbname . ".sdm_5harilibur where tanggal = '" . $tglnya[$res['notransaksi']] . "' and (kebun='GLOBAL' or kebun='" . substr($res['alokasibiaya'], 0, 4) . "')";
                $hslhr   = fetchdata($strhr);
                $ketlibur = isset($hslhr[0]['keterangan']);
                if ($ketlibur == '') {
                    $ketlibur   = 'kerja';
                }

                if ($ketlibur == 'cuti bersama') {
                    $ketlibur   = 'libur';
                }

                #= cek apakah sekarang hari libur atau bukan 
                if ($ketlibur == 'libur') {
                    $whrhari = " and jenishari='libur'";
                } else {
                    $whrhari = " and jenishari='kerja'";
                }

                $string = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and (operator='" . $res['operator'] . "' or helper='" . $res['operator'] . "' or helper2='" . $res['operator'] . "' or helper3='" . $res['operator'] . "')";
                $result = fetchData($string);

                $string1 = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and (operator='" . $res['helper'] . "' or helper='" . $res['helper'] . "' or helper2='" . $res['helper'] . "' or helper3='" . $res['helper'] . "')";
                $result1 = fetchData($string1);

                $string2 = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and (operator='" . $res['helper2'] . "' or helper='" . $res['helper2'] . "' or helper2='" . $res['helper2'] . "' or helper3='" . $res['helper2'] . "')";
                $result2 = fetchData($string2);

                $string3 = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and (operator='" . $res['helper3'] . "' or helper='" . $res['helper3'] . "' or helper2='" . $res['helper3'] . "' or helper3='" . $res['helper3'] . "')";
                $result3 = fetchData($string3);


                #=upah sopir/operator
                $sql = "select upah from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $res['operator'] . "'";
                $rst = fetchData($sql);

                #=upah helper
                $sql1 = "select upah from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $res['helper'] . "'";
                $rst1 = fetchData($sql1);

                #=upah helper2
                $sql2 = "select upah from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $res['helper2'] . "'";
                $rst2 = fetchData($sql2);

                #=upah helper3
                $sql3 = "select upah from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $res['helper3'] . "'";
                $rst3 = fetchData($sql3);

                $premi_operator              = $res['premiopt'];
                $premi_operator_tambahan     = $res['premitambahanopt'];
                $premi_premihelp    = $res['premihelp'];
                $premi_premihelp2   = $res['premihelp2'];
                $premi_premihelp3   = $res['premihelp3'];

                $upahnya        = ($rst[0]['upah'] / count($result));



                $upahnyahelp    = ($rst1[0]['upah'] / count($result1));
                $upahnyahelp2   = ($rst2[0]['upah'] / count($result2));
                $upahnyahelp3   = ($rst3[0]['upah'] / count($result3));

                if (strlen($res['alokasibiaya']) == 4) {
                    $orgnya = " and unit='" . substr($res['alokasibiaya'], 0, 4) . "'";
                } else {
                    $orgnya = " and divisi='" . substr($res['alokasibiaya'], 0, 6) . "'";
                }

                $strx   = "SELECT * FROM " . $dbname . ".vhc_5premikegiatan where kodekegiatan='" . $res['jenispekerjaan'] . "' " . $orgnya . " " . $whrhari . " and statuspremi='1'";
                $resx   = fetchdata($strx);
                $no += 1;
                echo "
                    <tr class=rowcontent>
                    <td align=center>" . $no . "</td>
                    <td align=center>" . $res['notransaksi'] . "</td>
                    <td>" . $res['jenispekerjaan'] . "-" . $nmpek[$res['jenispekerjaan']] . "</td>
                    <td>" . (strlen($res['alokasibiaya']) > 6 ? getIndukBlok($res['alokasibiaya']) : getNamaOrg($res['alokasibiaya'])) . "</td>
                    <td style='display:none'>" . $res['namasegment'] . "</td>
                    <td align=right>" . number_format($res['jumlahrit'], 2) . "</td>
                    <td align=right>" . number_format($res['beratmuatan'], 2) . "</td>
                    <td align=right>" . number_format($res['kmhmawal'], 2) . "</td>
                    <td align=right>" . number_format($res['kmhmakhir'], 2) . "</td>
                    <td align=right>" . number_format($res['kmhmakhir'] - $res['kmhmawal'], 2) . "</td>
                    <td align=center>" . $res['satuan'] . "</td>
                    <td align=center>" . getNamaKaryawan($res['operator']) . "</td>
                    <td align=right>" . number_format($upahnya, 2) . "</td>
                    <td align=right>" . number_format($premi_operator, 2) . "</td>
                    <td align=right " . $hiddenHelper . ">" . number_format($premi_operator_tambahan, 2) . "</td>
                    <td align=right>" . number_format($res['denda']) . "</td>
                    <td align=center >" . getNamaKaryawan($res['helper']) . "</td>
                    <td align=right >" . number_format($upahnyahelp, 2) . "</td>
                    <td align=right >" . number_format($premi_premihelp, 2) . "</td>
                    <td align=center " . $hiddenHelper . ">" . getNamaKaryawan($res['helper2']) . "</td>
                    <td align=right " . $hiddenHelper . ">" . number_format($upahnyahelp2, 2) . "</td>
                    <td align=right " . $hiddenHelper . ">" . number_format($premi_premihelp2, 2) . "</td>
                    <td align=center hidden>" . getNamaKaryawan($res['helper3']) . "</td>
                    <td align=right hidden>" . number_format($upahnyahelp3, 2) . "</td>
                    <td align=right hidden>" . number_format($premi_premihelp3, 2) . "</td>
                    <td align=right style='display:none'>" . number_format($res['biaya'], 2) . "</td>
                    <td>" . $res['keterangan'] . "</td>";

                $ttlrit += $res['jumlahrit'];
                $ttlprestasi += $res['beratmuatan'];
                $ttlkmhm += ($res['kmhmakhir'] - $res['kmhmawal']);
                $ttluphoprt += $upahnya;
                // $ttlprmioprt += $res['premiopt'];
                $ttlprmioprt += $premi_operator;
                $ttlprmioprt_tambahan += $premi_operator_tambahan;
                $ttluphhelp += $upahnyahelp;
                // $ttlprmihelp += $res['premihelp'];
                $ttlprmihelp += $premi_premihelp;
                $ttluphhelp2 += $upahnyahelp2;
                // $ttlprmihelp2 += $res['premihelp2'];
                $ttlprmihelp2 += $premi_premihelp2;
                $ttluphhelp3 += $upahnyahelp3;
                // $ttlprmihelp3 += $res['premihelp3'];
                $ttlprmihelp3 += $premi_premihelp3;
                $ttldenda += $res['denda'];

                echo "<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                    onclick=\"fillFieldKrj('" . $res['jenispekerjaan'] . "','" . $res['alokasibiaya'] . "','" . $res['beratmuatan'] . "','" . $res['jumlahrit'] . "','" . $res['keterangan'] . "','" . $res['biaya'] . "','" . $res['kmhmawal'] . "','" . $res['kmhmakhir'] . "','" . $res['satuan'] . "','" . $res['kodesegment'] . "','" . $res['namasegment'] . "','" . $res['kodedept'] . "','" . $res['operator'] . "','" . $res['premiopt'] . "','" . $res['helper'] . "','" . $res['helper2'] . "','" . $res['helper3'] . "','" . $res['premihelp'] . "','" . $res['premihelp2'] . "','" . $res['premihelp3'] . "','" . $upahnya . "','" . $upahnyahelp . "','" . $upahnyahelp2 . "','" . $upahnyahelp3 . "','" . $res['denda'] . "','" . isset($resx[0]['basis']) . "','" . $res['checklembur'] . "','" . $resx[0]['premilebihbasis'] . "','" . $res['premitambahanopt'] . "');\"></td>

                    <td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delDataKrj('" . $res['notransaksi'] . "','" . $res['jenispekerjaan'] . "','" . $res['alokasibiaya'] . "','" . $res['kodesegment'] . "','" . $res['beratmuatan'] . "','" . $res['operator'] . "','" . $res['helper'] . "','" . $res['helper2'] . "','" . $res['helper3'] . "','" . $kdkndraan[$res['notransaksi']] . "','" . $res['kmhmawal'] . "');\" >	
                    </td>
                    </tr>
                    ";
            }
            echo "<tr class=rowcontent style='background-color:#CCC;font-weight:bold;'>
                        <td align=center colspan=4>" . $_SESSION['lang']['total'] . "</td>
                        <td align=right>" . number_format($ttlrit, 2) . "</td>
                        <td align=right>" . number_format($ttlprestasi, 2) . "</td>
                        <td align=right colspan=3>" . number_format($ttlkmhm, 2) . "</td>
                        <td align=right></td>
                        <td align=right></td>
                        <td align=right>" . number_format($ttluphoprt, 2) . "</td>
                        <td align=right>" . number_format($ttlprmioprt, 2) . "</td>
                        <td align=right " . $hiddenHelper . ">" . number_format($ttlprmioprt_tambahan, 2) . "</td>
                        <td align=right>" . number_format($ttldenda, 2) . "</td>
                        <td align=right ></td>
                        <td align=right >" . number_format($ttluphhelp, 2) . "</td>
                        <td align=right >" . number_format($ttlprmihelp, 2) . "</td>
                        <td align=right " . $hiddenHelper . "></td>
                        <td align=right " . $hiddenHelper . ">" . number_format($ttluphhelp2, 2) . "</td>
                        <td align=right " . $hiddenHelper . ">" . number_format($ttlprmihelp2, 2) . "</td>
                        <td align=right hidden></td>
                        <td align=right hidden>" . number_format($ttluphhelp3, 2) . "</td>
                        <td align=right hidden>" . number_format($ttlprmihelp3, 2) . "</td>
                        <td align=right colspan=3></td>
                    </tr>";
        } else {
            echo "<tr class=rowcontent><td colspan=25 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
        }
        break;
    case 'insert_header':
        #== Create No transaksi
        $thn = substr($tgl_kerja, 0, 4);
        $bln = substr($tgl_kerja, 4, 2);
        $periode = $thn . "-" . $bln;

        $notransaksi = $param['kodeorg'] . "/RUN/" . $thn . "/" . $bln . "/";
        $sql = selectQuery($dbname, "vhc_runht", "notransaksi", "notransaksi like '%" . $notransaksi . "%'", "notransaksi desc", "", "1");
        $hsl = fetchData($sql)[0];
        if (!isset($hsl['notransaksi'])) {
            $awal = 1;
        } else {
            $awal = substr($hsl['notransaksi'], -4, 4);
            $awal = intval($awal);

            $cekbln = substr($hsl['notransaksi'], -7, 2);
            $cekthn = substr($hsl['notransaksi'], -12, 4);
            if ($thn != $cekthn) {
                $awal = 1;
            } else {
                $awal++;
            }
        }
        $counter = addZero($awal, 4);

        #== Cek periode gajinya
        cekperiodegaji($param['kodeorg'], $thn . "-" . $bln);
        $notrans_new = $param['kodeorg'] . "/RUN/" . $thn . "/" . $bln . "/" . $counter;

        if ($notrans_new == '') {
            exit("Warningsystem :Notransaksi Tidak Boleh Kosong");
        }
        //ending create notransaksi

        #== mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
        if ($tgl_kerja < $_SESSION['org']['period']['start']) {
            echo "Warningsystem : Periode akutansi bulan " . numToMonth(intval($bln), 'I', 'long') . " sudah ditutup.";
            break;
        }

        #== Validasi EOD
        // validasiInput($param['kodeorg'],'','TRK',tanggalsystemn(tanggalnormal($tgl_kerja)),$exit='1');

        #== Cek Periode akuntansi
        $str = selectQuery($dbname, "setup_periodeakuntansi", "*", "periode='$periode' and kodeorg='{$param['kodeorg']}' and tutupbuku='1'");
        $numrows = count(fetchData($str));
        if ($numrows > 0) {
            exit("Warningsystem :Periode sudah tutup buku");
        }

        #== Cek apakah sudah kendaraan atau alat kerja dihari yang sama
        $str = selectQuery($dbname, 'vhc_runht', '*', "kodevhc='{$param['kodevhc']}' and tanggal='$tgl_kerja'");
        if (count(fetchdata($str)) > 0) {
            exit("Warningsystem : Kendaraan " . $param['kodevhc'] . " - " . getVhc($param['kodevhc'], 'detailvhc') . " pada tanggal " . tanggalnormal($tgl_kerja) . " sudah diinput pada notransaksi " . fetchData($str)[0]['notransaksi'] . "\nsilahkan cari di list data dan lakukan Edit !");
        }

        #== Cek notransaksi baru sudah ada belum
        $sql = selectQuery($dbname, 'vhc_runht', 'notransaksi', "notransaksi='$notrans_new'");
        $numrows = count(fetchData($sql));
        if ($numrows < 1) {
            // Cek apakah kodevhc sudah ada di tanggal > tanggal input
            $qCek = selectQuery($dbname, 'vhc_runht', 'max(tanggal) as tgl', "kodevhc = '" . $param['kodevhc'] . "' and tanggal > '" . $tgl_kerja . "'");
            $resCek = fetchData($qCek);
            if (!empty($resCek[0]['tgl'])) {
                /*	exit("Warningsystem: Kendaraan sudah ada transaksi di tanggal yang lebih besar."."\nTanggal transaksi terakhir ".tanggalnormal($resCek[0]['tgl']));*/
            }

            $createdtime = date('Y-m-d H:i:s');
            /* Insert Header*/
            $data = array(
                'notransaksi' => $notrans_new,
                'kodeorg' => $param['kodeorg'],
                'jenisvhc' => $param['jenisvhc'],
                'kodevhc' => $param['kodevhc'],
                'tanggal' => $tgl_kerja,
                'jenisbbm' => $param['jenisbbm'],
                'jlhbbm' => $param['jmlh_bbm'],
                'kontanan' => $param['kontanan'],
                'mandor' => $param['mandor'],
                'updateby' => $user_entry,
                'createdtime' => $createdtime
            );

            try {
                $sql = insertQuery($dbname, 'vhc_runht', $data, array_keys($data));
                $owlPDO->exec($sql);

                $optkode = makeOption($dbname, 'vhc_5master', 'kodevhc,kelompokvhc');
                $kelvhc = $optkode[$param['kodevhc']];
                $n = '';
                #== Ambil kegiatan traksi
                $optJnsKerja = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
                $sjnskrj = selectQuery($dbname, 'vhc_kegiatan', '*', "tipe='traksi' and LEFT(kodekegiatan, 3) != '111' and (kelompokvhc='" . $kelvhc . "' or kelompokvhc='GLOBAL') and (jenisvhc='" . $param['jenisvhc'] . "' or jenisvhc='GLOBAL') AND status='1'", "noakun");
                $rjnskrj = fetchData($sjnskrj);
                $d = '';
                foreach ($rjnskrj as $jnskrj) {
                    $d = substr($jnskrj['kodekegiatan'], 0, 5);
                    if ($d != $n) {
                        $nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $d . "'");
                        $optJnsKerja .= "<optgroup label='" . $nmorg[$d] . "'>";
                    }

                    $optJnsKerja .= "<option value=" . $jnskrj['kodekegiatan'] . ">" . $jnskrj['kodekegiatan'] . " - " . $jnskrj['namakegiatan'] . "</option>";

                    if ($d != $n) {
                        $n = $d;
                        $optJnsKerja .= "</optgroup>";
                    }
                }

                // #== Ambil Operator
                // $optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                // $skary  ="SELECT DISTINCT (a.karyawanid),a.nama,b.nik,a.jabatan FROM $dbname.vhc_5operator a 
                // LEFT JOIN $dbname.datakaryawan b ON a.karyawanid=b.karyawanid 
                // WHERE a.aktif='1' AND a.vhc = '{$param['kodevhc']}' AND  ORDER BY a.jabatan ";
                // $rkary  =fetchData($skary);
                // foreach ($rkary as $v) {
                //     $optKary.="<option value=".$v['karyawanid'].">".$v['nama']."&nbsp; [".$arrJab[$v['jabatan']]."] &nbsp;[".$v['nik']."]</option>";
                // }

                ## Ambil operator
                $optKary = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
                $skary = "select distinct(a.karyawanid),a.nama,b.nik,a.jabatan from " . $dbname . ".vhc_5operator a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.aktif='1' and a.vhc = '" . $param['kodevhc'] . "'and a.jabatan in ('0','2') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . $tgl_kerja . "')  order by a.jabatan,a.nama";

                $res1 = $owlPDO->query($skary) or die(print " Gagal: " . PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                while ($rkary = $res1->fetch()) {
                    $optKary .= "<option value=" . $rkary['karyawanid'] . ">" . $rkary['nama'] . "&nbsp; [" . $arrJab[$rkary['jabatan']] . "] &nbsp;[" . $rkary['nik'] . "]</option>";
                }

                ## Ambil Helper
                $optKary_Helper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
                $skary = "select distinct(a.karyawanid),a.nama,b.nik,a.jabatan from " . $dbname . ".vhc_5operator a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.aktif='1' and a.vhc = '" . $param['kodevhc'] . "'and a.jabatan in ('1') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . $tgl_kerja . "')  order by a.jabatan,a.nama";
                $res1 = $owlPDO->query($skary) or die(print " Gagal: " . PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                while ($rkary = $res1->fetch()) {
                    $optKary_Helper .= "<option value=" . $rkary['karyawanid'] . ">" . $rkary['nama'] . "&nbsp; [" . $arrJab[$rkary['jabatan']] . "] &nbsp;[" . $rkary['nik'] . "]</option>";
                }


                #== Ambil kmhmakhir dari kendaraan dipilih.
                $sKm = selectQuery($dbname, 'vhc_kmhmakhir_vw', 'kmhmakhir', "kodevhc='{$param['kodevhc']}'", '', true);

                echo fetchData($sKm)[0]['kmhmakhir'] . "####" . $notrans_new . "####" . $optJnsKerja . "####" . $optKary . "####" . $optKary_Helper;
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        } else {
            echo "Warningsystem : Nomor transaksi sudah ada !";
            exit();
        }
        break;
    case 'update_head':
        if (($tgl_kerja == '') || ($param['jmlh_bbm'] == '')) {
            echo "Warningsystem:Please Complete The Form";
            exit();
        }

        // Cek apakah kodevhc sudah ada di tanggal > tanggal input
        $qCek = selectQuery(
            $dbname,
            'vhc_runht',
            'max(tanggal) as tgl',
            "kodevhc = '" . $param['kodevhc'] . "' and tanggal > '" . $tgl_kerja . "'"
        );
        $resCek = fetchData($qCek);
        $editOnly = false;
        if (!empty($resCek[0]['tgl'])) $editOnly = true;

        @$jumlah = $param['kmhm_akhir'] - $param['kmhm_awal'];
        $sql = "update " . $dbname . ".vhc_runht set jenisvhc='" . $param['jenisvhc'] . "',kodevhc='" . $param['kodevhc'] . "',tanggal='" . $tgl_kerja . "',jenisbbm='" . $param['jenisbbm'] . "',jlhbbm='" . $param['jmlh_bbm'] . "' 
                    where notransaksi='" . $param['no_trans'] . "'";
        try {
            $owlPDO->exec($sql);

            $optkode = makeOption($dbname, 'vhc_5master', 'kodevhc,kelompokvhc');
            $kelvhc = $optkode[$param['kodevhc']];

            $sjnskrj = "select * from " . $dbname . ".vhc_kegiatan where
                        tipe ='traksi' and LEFT(kodekegiatan, 3) != '111' and (kelompokvhc='" . $kelvhc . "' or kelompokvhc='GLOBAL') 
                        and (jenisvhc='" . $param['jenisvhc'] . "' or jenisvhc='GLOBAL') AND status='1' order by noakun asc";
            $optJnsKerja = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
            $res = $owlPDO->query($sjnskrj) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $d = '';
            while ($rjnskrj = $res->fetch()) {
                $d = substr($rjnskrj['kodekegiatan'], 0, 5);
                if ($d != $n) {
                    $nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $d . "'");
                    $optJnsKerja .= "<optgroup label='" . $nmorg[$d] . "'>";
                }
                $optJnsKerja .= "<option value=" . $rjnskrj['kodekegiatan'] . ">" . $rjnskrj['kodekegiatan'] . " - " . $rjnskrj['namakegiatan'] . "</option>";
                if ($d != $n) {
                    $n = $d;
                    $optJnsKerja .= "</optgroup>";
                }
            }


            $sKm = "select distinct kmhmakhir from " . $dbname . ".vhc_kmhmakhir_vw where kodevhc='" . $param['kodevhc'] . "'";
            $res = $owlPDO->query($sKm) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $rKm = $res->fetch();
            if ($editOnly) {
                $nol = 0;
                echo $nol . "####" . $optJnsKerja;
            } else {
                echo $rKm['kmhmakhir'] . "####" . $optJnsKerja;
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'deleteHead':
        $sdel = "delete from " . $dbname . ".vhc_runht where notransaksi='" . $param['no_trans'] . "'"; //echo "Warningsystem:".$sdel;
        try {
            $owlPDO->exec($sdel);
            $sdel2 = "delete from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "'";
            try {
                $owlPDO->exec($sdel2);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
            $sdel3 = "delete from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "'";
            try {
                $owlPDO->exec($sdel3);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
            updateKmHm($optKdVhc[$param['no_trans']]);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'insert_pekerjaan':
        #= cek headernya sudah ada belum
        $qHead = selectQuery($dbname, 'vhc_runht', '*', "notransaksi = '" . $param['no_trans'] . "'");
        $rCekHt = fetchData($qHead);
        if (count($rCekHt) < 1) {
            echo "Warningsystem: Header harus diinput terlebih dahulu.";
            exit();
        }

        #= perhitungan jumlah kmhm
        $jumlah = $param['kmhm_akhir'] - $param['kmhm_awal'];

        #= ambil tipe kode unit
        $dTip = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $param['lokasi_kerja'] . "'");

        #= ambil Jenis kegiatan
        $sAlokasi = "select count(b.kelompok) as countkelompok from " . $dbname . ".vhc_kegiatan a 
                        left join " . $dbname . ".setup_kegiatan b on a.noakun = b.noakun and b.kelompok in ('BBT', 'PNN', 'LC', 'TBM', 'TM') 
                        where a.kodekegiatan='" . $param['jns_kerja'] . "'";
        $rAlokasi = fetchData($sAlokasi)[0];

        if ($param['no_trans'] == '') {
            echo "Warningsystem: Notransksi tidak boleh kosong";
            exit();
        }
        if ($param['jns_kerja'] == '') {
            echo "Warningsystem: Jenis pekerjaan harus dipilih";
            exit();
        }
        if ($param['lokasi_kerja'] == '') {
            echo "Warningsystem: Lokasi harus dipilih";
            exit();
        }
        if ($rAlokasi['countkelompok'] != 0) {
            if ($param['blok'] == '') {
                echo "Warningsystem : Blok harus dipilih.";
                exit();
            }
        }
        if ($param['jmlh_rit'] == '' || $param['jmlh_rit'] == '0') {
            echo "Warningsystem: Jumlah rit harus diisi";
            exit();
        }
        if ($param['brt_muatan'] == '' || $param['brt_muatan'] == '0') {
            echo "Warningsystem: Prestasi harus diisi";
            exit();
        }
        if ($param['kmhm_awal'] >= $param['kmhm_akhir']) {
            echo "Warningsystem:" . $_SESSION['lang']['vhc_kmhm_awal'] . " harus lebih kecil dari " . $_SESSION['lang']['vhc_kmhm_akhir'] . "";
            exit();
        }

        if ($param['blok'] != '') {
            if ($dTip[$param['lokasi_kerja']] == 'KEBUN') {
                if (strlen($param['blok']) < 9) {
                    exit("Warningsystem: Blok diperlukan");
                }
            }
            $param['lokasi_kerja'] = $param['blok'];
        }

        if ($param['biaya'] == '') {
            $biaya = 0;
        }

        #= Cek nilai umr
        $str = selectQuery($dbname, 'sdm_5gajipokok', '*', "karyawanid='" . $param['kode_karyawan'] . "' and tahun='" . substr(tanggaldb($param['tglpekerjaan']), 0, 7) . "' and idkomponen='1'");
        $bar = fetchdata($str)[0];
        $gajipokok = $bar['jumlah'] / 25;

        if ($param['uphOprt'] > $gajipokok) {
            if ($rCekHt[0]['kontanan'] != 'KONTAN') {
                exit("Warningsystem: Nilah upah lebih besar dari nilai UMR / Hari, maksimal nilai upah = Rp. " . $gajipokok . "");
            }
        }

        #= cek sudah ada data detail yang sama belum
        $sql = selectQuery($dbname, 'vhc_rundt', '*', "notransaksi='" . $param['no_trans'] . "' and jenispekerjaan='" . $param['jns_kerja'] . "' and alokasibiaya='" . $param['blok'] . "' and kmhmawal='" . $param['kmhm_awal'] . "' and operator='{$param['kode_karyawan']}'");
        $hsl = fetchdata($sql);
        if (count($hsl) > 0) {
            exit("Warningsystem : Data dengan Notransaksi = " . $param['no_trans'] . " dan Jenis pekerjaan = " . $param['jns_kerja'] . " dan Blok = " . $param['blok'] . " dan KMHM Awal = " . $param['kmhm_awal'] . "  dan operator = {$param['kode_karyawan']} sudah tersedia ");
            die();
            echo "salah";
        } else { // jika data detail belum ada
            if (($param['kode_karyawan'] != '' && $param['kode_helper'] != '') && ($param['kode_karyawan'] == $param['kode_helper'] || $param['kode_karyawan'] == $param['kode_helper2'] || $param['kode_karyawan'] == $param['kode_helper3'])) { //validasi hanya 1 jabatan di 1 hari
                exit("Warningsystem : Operator dan Helper tidak boleh sama dalam hari yang sama!.");
            } else {
                $select = selectQuery($dbname, 'vhc_rundt', '*', "notransaksi='" . $param['no_trans'] . "' and jenispekerjaan='" . $param['jns_kerja'] . "' and alokasibiaya='" . $param['lokasi_kerja'] . "' and beratmuatan='" . $param['brt_muatan'] . "' and kmhmawal='" . $param['kmhm_awal'] . "' and operator='{$param['kode_karyawan']}'");
                $hasil  = fetchdata($select);
                if (count($hasil) > 0) { //kalau udah ada
                    exit("Warningsystem : Data sudah tersedia dengan rincian :\nnotransaksi =" . $param['no_trans'] . "\nJenis Pekerjaan =" . $nmpek[$param['jns_kerja']] . "\nAlokasi Biaya / Blok =" . getNamaOrg($param['lokasi_kerja']) . "\nBerat muatan =" . number_format($param['brt_muatan']) . "\nKmHm Awal =" . $param['kmhm_awal']);
                } else { // eksekusi kalo belum ada
                    $data = array(
                        'notransaksi'   => $param['no_trans'],
                        'jenispekerjaan' => $param['jns_kerja'],
                        'alokasibiaya'  => $param['lokasi_kerja'],
                        'beratmuatan'   => $param['brt_muatan'],
                        'jumlahrit'     => $param['jmlh_rit'],
                        'keterangan'    => $param['ket'],
                        'biaya'         => $biaya,
                        'kmhmawal'      => $param['kmhm_awal'],
                        'kmhmakhir'     => $param['kmhm_akhir'],
                        'jumlah'        => $jumlah,
                        'satuan'        => $param['stn'],
                        'kodesegment'   => $param['kodesegment'],
                        'operator'      => $param['kode_karyawan'],
                        'helper'        => $param['kode_helper'],
                        'helper2'       => $param['kode_helper2'],
                        'helper3'       => $param['kode_helper3'],
                        'denda'         => $param['pnltyOprt'],
                        'checklembur'   => $param['checklembur']
                    );

                    if (($param['kode_helper'] == $param['kode_karyawan']) && ($param['kode_helper'] != '' && $param['kode_karyawan'] != '')) {
                        exit("warning : Helper dan Operator tidak boleh orang yang sama!");
                    }

                    $sins = insertQuery($dbname, 'vhc_rundt', $data, array_keys($data));
                    try {
                        $owlPDO->exec($sins);
                        updateKmHm($optKdVhc[$param['no_trans']]);
                        $sKm = selectQuery($dbname, 'vhc_kmhmakhir_vw', 'kmhmakhir', "kodevhc='" . $optKdVhc[$param['no_trans']] . "'", '', true);
                        $rKm = fetchData($sKm)[0];
                        echo intval($rKm['kmhmakhir']);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }
            }
        }
        break;
    case 'update_kerja':
        $sAlokasi = "select count(b.kelompok) as countkelompok from " . $dbname . ".vhc_kegiatan a 
                                    left join " . $dbname . ".setup_kegiatan b 
                                    on a.noakun = b.noakun and b.kelompok in ('BBT', 'PNN', 'TB', 'TBM', 'TM') 
                                    where a.kodekegiatan='" . $param['jns_kerja'] . "'";
        $res = $owlPDO->query($sAlokasi) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $rAlokasi = $res->fetch();

        if (($param['brt_muatan'] == '') || ($param['jmlh_rit'] == '')) {
            echo "Warningsystem: Harap lengkapi form inputan !";
            exit();
        }

        if ($rAlokasi['countkelompok'] != 0) {
            if ($param['blok'] == '') {
                echo "Warningsystem : Blok harus dipilih.";
                exit();
            }
        }

        if ($param['blok'] != '') {
            $param['lokasi_kerja'] = $param['blok'];
            $where .= " and alokasibiaya='" . $param['lokasi_kerja'] . "'";
        } else {
            if ($param['old_lokkerja'] != $param['lokasi_kerja']) {
                $where .= " and alokasibiaya='" . $param['old_lokkerja'] . "'";
            } else {
                $where .= " and alokasibiaya='" . $param['lokasi_kerja'] . "'";
            }
        }
        if ($param['old_jnskerja'] != '') {
            if ($param['jns_kerja'] != $param['old_jnskerja']) {
                $where .= "  and jenispekerjaan='" . $param['old_jnskerja'] . "'";
            } else {
                $where .= "  and jenispekerjaan='" . $param['jns_kerja'] . "'";
            }
        }
        // $sql ="select * from ".$dbname.".vhc_rundt where notransaksi='".$param['no_trans']."' and jenispekerjaan='".$param['jns_kerja']."' and alokasibiaya='".$param['lokasi_kerja']."' ";
        // $res = fetchData($sql);
        // $brtdidt = [];
        // foreach ($res as $val) {
        //     array_push($brtdidt,$val['beratmuatan']);
        // }
        // if(in_array($param['brt_muatan'],$brtdidt)){
        //     exit("Warningsystem : Berat muatan = ".number_format($param['brt_muatan'])." sudah tersedia di notransaksi = ".$param['no_trans'].", Jenis pekerjaan ".$nmpek[$param['jns_kerja']].", alokasi biaya = ".$param['lokasi_kerja']." ");
        // }

        // if($param['oldbrt_muatan']!=''){
        //     $where.="  and beratmuatan='".$param['oldbrt_muatan']."'";		
        // }

        if (!empty($param['kodesegment'])) {
            $where .= "  and kodesegment='" . $param['oldSegment'] . "'";
        }
        if ($param['kmhm_awal'] >= $param['kmhm_akhir']) {
            echo "Warningsystem: " . $_SESSION['lang']['vhc_kmhm_awal'] . " harus lebih kecil dari " . $_SESSION['lang']['vhc_kmhm_akhir'] . "";
            exit();
        }

        if ($param['jns_kerja'] == '') {
            echo "Warningsystem: Jenis pekerjaan harus dipilih";
            exit();
        }
        if ($param['lokasi_kerja'] == '') {
            echo "Warningsystem: Lokasi harus dipilih";
            exit();
        }

        // Get Prev Data
        $qData  = selectQuery($dbname, 'vhc_rundt', '*', "notransaksi='" . $param['no_trans'] . "' " . $where);
        $resData = fetchData($qData);

        // All Detail in Transaksi
        $qKm    = selectQuery($dbname, 'vhc_rundt', 'max(kmhmakhir) as kmakhir', "notransaksi='" . $param['no_trans'] . "'");
        $resKm  = fetchData($qKm);
        // if($resKm[0]['kmakhir']>$resData[0]['kmhmakhir'] and $param['kmhm_akhir']!=$resData[0]['kmhmakhir']) {
        // if($param['jenisvhc'] != 'CM' || $param['jenisvhc'] != 'FT' || $param['jenisvhc'] != 'TB'){
        // exit("Warningsystem: Transaksi yang bukan terakhir tidak boleh diubah KM / HM Akhir");
        // }
        // }

        // Get Header
        $qHead  = selectQuery($dbname, 'vhc_runht', 'tanggal,kodevhc', "notransaksi = '" . $param['no_trans'] . "'");
        $resHead = fetchData($qHead);
        if (empty($resHead)) exit("Warningsystem: Data Header tidak ada");
        $resHead = $resHead[0];

        // Cek apakah kodevhc sudah ada di tanggal > tanggal input
        $qCek   = selectQuery($dbname, 'vhc_runht', 'max(tanggal) as tgl', "kodevhc = '" . $resHead['kodevhc'] . "' and tanggal > '" . $resHead['tanggal'] . "'");
        $resCek = fetchData($qCek);
        if (!empty($resCek[0]['tgl']) and $param['kmhm_akhir'] != $resData[0]['kmhmakhir']) {
            /*exit("Warningsystem: Kendaraan sudah ada transaksi di tanggal yang lebih besar."."\nPerubahan KM / HM Akhir tidak bisa dilakukan");*/
        }

        $jumlah = $param['kmhm_akhir'] - $param['kmhm_awal'];

        if (($param['kode_helper'] == $param['kode_karyawan']) && ($param['kode_helper'] != '' && $param['kode_karyawan'] != '')) {
            exit("warning : Helper dan Operator tidak boleh orang yang sama!");
        }

        $sup = "update " . $dbname . ".vhc_rundt set beratmuatan='" . $param['brt_muatan'] . "',jumlahrit='" . $param['jmlh_rit'] . "',keterangan='" . $param['ket'] . "',biaya='" . $param['biaya'] . "',kmhmakhir='" . $param['kmhm_akhir'] . "',jumlah='" . $jumlah . "',satuan='" . $param['stn'] . "',kodesegment='" . $param['kodesegment'] . "',denda='" . $param['pnltyOprt'] . "',operator='" . $param['kode_karyawan'] . "',helper='" . $param['kode_helper'] . "',checklembur='" . $param['checklembur'] . "' where notransaksi='" . $param['no_trans'] . "' " . $where . " and kmhmawal='" . $param['kmhm_awal'] . "'";
        try {
            $owlPDO->exec($sup);
            $sKm = "select distinct kmhmakhir from " . $dbname . ".vhc_kmhmakhir_vw where kodevhc='" . $optKdVhc[$param['no_trans']] . "'";
            $res = $owlPDO->query($sKm) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $rKm = $res->fetch();
            updateKmHm($optKdVhc[$param['no_trans']]);
            echo intval($rKm['kmhmakhir']);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'deleteKrj':
        $delKrj = deleteQuery(
            $dbname,
            'vhc_rundt',
            "notransaksi='" . $param['no_trans'] . "' and 
                        jenispekerjaan='" . $param['jns_kerja'] . "' and 
                        alokasibiaya='" . $param['blok'] . "' and 
                        kmhmawal='" . $param['kmhm_awal'] . "' and 
                        kodesegment='" . $param['kodesegment'] . "' and 
                        beratmuatan='" . $param['brt_muatan'] . "'"
        );
        try {
            $owlPDO->exec($delKrj);
            $prmiOprt   = 0;
            $prmiHelp   = 0;
            $prmiHelp2  = 0;
            $prmiHelp3  = 0;

            // $whrt = "";

            // if($param['blok']!=''){
            //     $whrt = " and alokasibiaya='".$param['blok']."'";
            // }else{
            //     $whrt = " and alokasibiaya='".substr($param['Blok'],0,4)."' ";
            // }

            #= update premi di vhcrundt
            // $sup_op="update ".$dbname.".vhc_rundt set premiopt='".@$prmiOprt."' where notransaksi='".$param['no_trans']."' and jenispekerjaan='".$param['jns_kerja']."' and operator='".$param['kode_karyawan']."' ".$whrt."";
            // try{$owlPDO->exec($sup_op); echo"";}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

            // $sup_op1="update ".$dbname.".vhc_rundt set premihelp='".@$prmiHelp."' where notransaksi='".$param['no_trans']."' and jenispekerjaan='".$param['jns_kerja']."' and helper='".$param['kode_helper']."' ".$whrt."";
            // try{$owlPDO->exec($sup_op1); echo"";}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

            // $sup_op2="update ".$dbname.".vhc_rundt set premihelp2='".@$prmiHelp2."' where notransaksi='".$param['no_trans']."' and jenispekerjaan='".$param['jns_kerja']."' and helper2='".$param['kode_helper2']."' ".$whrt."";
            // try{$owlPDO->exec($sup_op2); echo"";}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

            // $sup_op3="update ".$dbname.".vhc_rundt set premihelp3='".@$prmiHelp3."' where notransaksi='".$param['no_trans']."' and jenispekerjaan='".$param['jns_kerja']."' and helper3='".$param['kode_helper3']."' ".$whrt."";
            // try{$owlPDO->exec($sup_op3); echo"";}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

            #= update premi operator di vhcrunhk
            if ($param['kode_karyawan'] != '') {
                $sql = "select * from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_karyawan'] . "'";
                $hsl = fetchData($sql);
                if (count($hsl) > 0) {
                    $qry = "select premiopt,denda from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and operator='" . $param['kode_karyawan'] . "'";
                    $res = fetchData($qry);
                    $premiall = 0;
                    $dendaall = 0;
                    foreach ($res as $val) {
                        @$premiall += $val['premiopt'];
                        @$dendaall += $val['denda'];
                    }

                    $upahdiinputlagi = '';
                    if (count($res) == 1) {
                        $upahdiinputlagi = ",upah='" . $hsl[0]['upah'] . "' ";
                    }
                    $strvhc = "update " . $dbname . ".vhc_runhk set premi='" . round($premiall, 2) . "',penalty='" . $dendaall . "' " . $upahdiinputlagi . " where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_karyawan'] . "'";
                    try {
                        $owlPDO->exec($strvhc);
                    } catch (PDOException $e) {
                        $errorDB .= " Error update Premi dan Upah gagal:" . $e->getMessage() . "\n" . $strvhc;
                    }
                }
            }

            #= update premi helper di vhcrunhk
            if ($param['kode_helper'] != '') {
                $sql = "select * from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper'] . "'";
                $hsl = fetchData($sql);
                if (count($hsl) > 0) {
                    $qry = "select premihelp,denda from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and helper='" . $param['kode_helper'] . "'";
                    $res = fetchData($qry);
                    $premiall = 0;
                    $dendaall = 0;
                    foreach ($res as $val) {
                        @$premiall += $val['premihelp'];
                        @$dendaall += $val['denda'];
                    }

                    $upahdiinputlagi = '';
                    if (count($res) == 1) {
                        $upahdiinputlagi = ",upah='" . $hsl[0]['upah'] . "' ";
                    }
                    $strvhc = "update " . $dbname . ".vhc_runhk set premi='" . round($premiall, 2) . "',penalty='" . $dendaall . "' " . $upahdiinputlagi . " where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper'] . "'";
                    try {
                        $owlPDO->exec($strvhc);
                    } catch (PDOException $e) {
                        $errorDB .= " Error update Premi dan Upah Helper 1 gagal:" . $e->getMessage() . "\n" . $strvhc;
                    }
                }
            }

            #= update premi helper 2 di vhcrunhk
            if ($param['kode_helper2'] != '') {
                $sql = "select * from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper2'] . "'";
                $hsl = fetchData($sql);
                if (count($hsl) > 0) {
                    $qry = "select premihelp2,denda from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "' and helper2='" . $param['kode_helper2'] . "'";
                    $res = fetchData($qry);
                    $premiall = 0;
                    $dendaall = 0;
                    foreach ($res as $val) {
                        @$premiall += $val['premihelp2'];
                        @$dendaall += $val['denda'];
                    }

                    $upahdiinputlagi = '';
                    if (count($res) == 1) {
                        $upahdiinputlagi = ",upah='" . $hsl[0]['upah'] . "' ";
                    }
                    $strvhc = "update " . $dbname . ".vhc_runhk set premi='" . round($premiall, 2) . "',penalty='" . $dendaall . "' " . $upahdiinputlagi . " where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper2'] . "'";
                    try {
                        $owlPDO->exec($strvhc);
                    } catch (PDOException $e) {
                        $errorDB .= " Error update Premi dan Upah Helper 2 gagal:" . $e->getMessage() . "\n" . $strvhc;
                    }
                }
            }

            updateKmHm($optKdVhc[$param['no_trans']]);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;
    case 'load_data_opt':
        $arrPos = array("Operator", "Helper", "Driver");
        $sql = "select * from " . $dbname . ".vhc_runhk where substring(notransaksi,1,4)='" . $rKode['kodeorg'] . "' and notransaksi='" . $param['no_trans'] . "' order by notransaksi desc"; //echo "Warningsystem:".$sql;
        $res3 = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res3->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $res3->fetch()) {
            $skry = "select `namakaryawan` from " . $dbname . ".datakaryawan where karyawanid='" . $res['idkaryawan'] . "'";
            $res4 = $owlPDO->query($skry) or die(print " Gagal: " . PDOException::getMessage());
            $res4->setFetchMode(PDO::FETCH_ASSOC);
            $rkry = $res4->fetch();
            $no += 1;
            echo "
                <tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=center>" . $res['notransaksi'] . "</td>
                <td>" . $rkry['namakaryawan'] . "</td>
                <td>" . $arrPos[$res['posisi']] . "</td>
                <td align=right>" . number_format($res['upah'], 2) . "</td>
                <td align=right>" . number_format($res['premi'], 2) . "</td>
                <td align=right>" . number_format($res['penalty'], 2) . "</td>
                <td>" . $res['keterangan'] . "</td>
                <td align=center>
                <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $res['notransaksi'] . "','" . $res['idkaryawan'] . "');\" >	
                </td>
                </tr>
                ";
        }
        break;
    case 'insert_operator':
        #= Cek periode gajinya dulu
        $sPeriode   = selectQuery($dbname, 'sdm_5periodegaji', 'periode', "kodeorg='" . substr($rKode['kodeorg'], 0, 4) . "' and periode='" . substr($tgl_kerja, 0, 4) . "-" . substr($tgl_kerja, 4, 2) . "'");
        $rPeriode   = fetchData($sPeriode)[0];
        if ($rPeriode['periode'] == '') {
            echo "Warningsystem: Tanggal transaksi diluar periode atau Periode gaji belum ada.";
            exit();
        }

        #= Cek apakah sudah bekerja di BKM
        // $jlhpjbtbkm =0;
        // if($param['kode_karyawan'] != ''){
        //     $str        =selectQuery($dbname,'kebun_aktifitas','count(*) as jumlah,notransaksi,nobkm',"(nikmandor='".$param['kode_karyawan']."' or nikmandor1='".$param['kode_karyawan']."' or nikasisten='".$param['kode_karyawan']."' or keranimuat='".$param['kode_karyawan']."') and  tanggal='".$tgl_kerja."'");
        //     $bar        =fetchData($str)[0];
        //     $jlhpjbtbkm =$bar['jumlah'];
        //     $notransbkm =$bar['notransaksi'];
        //     $nobkm      =$bar['nobkm'];
        //     if($jlhpjbtbkm>0){
        //         // exit("Warningsystem: Karyawan ditanggal ".tanggalnormal($tgl_kerja)."  sudah terdaftar absensi supervisi di BKM di nomor ".$notransbkm." / ".$nobkm." ");
        //     }
        // }

        #validasi maksimal HK BHL
        cekmaxnilaihk($param['kode_karyawan'], tanggalsystemn(isset($param['tglpekerjaan'])), '1', '0', 'new', $exit = '0');

        #query pengecekan apakah FP aktif / tidak
        $str = "select * from " . $dbname . ".sdm_5aktivasifp where kodeorg='" . substr($rKode['kodeorg'], 0, 4) . "' and tanggal<='" . tanggalsystemn($param['tglpekerjaan']) . "'";
        $res = fetchData($str);

        $statusfp    = $res[0]['status']; //1 aktif,0 tidak
        $tipevalidasi = $res[0]['tipevalidasi'];
        $detailexp   = explode(",", $res[0]['detailvalidasi']);
        foreach ($detailexp as $vald) {
            $detval[$vald] = $vald;
        }

        $arrUpload = array();
        if ($statusfp == 1) {
            $arrUpload[]['nik'] = $param['kode_karyawan'];
            // validasifp($tipevalidasi,$detval,'TRK',$arrUpload,tanggalsystemn($param['tglpekerjaan']),'1');
        }

        #= Cek absensi umum
        $str        = selectQuery($dbname, 'sdm_absensidt', '*', "karyawanid='" . $param['kode_karyawan'] . "' and tanggal='" . $tgl_kerja . "'");
        $bar        = isset(fetchData($str)[0]);
        $jumabs     = $bar['umr'];

        if ($jumabs > 0 && $param['uphOprt'] > 0) {
            exit("Warningsystem: Karyawan ditanggal " . tanggalnormal($tgl_kerja) . "  sudah terdaftar di absensi umum, silahkan dihapus dahulu absensi umumnya");
        }

        #= Cek di BKM
        $str        = selectQuery($dbname, 'kebun_kehadiran_vw', 'notransaksi, sum(jhk) as jhk, sum(umr) as umr', "karyawanid='" . $param['kode_karyawan'] . "' and tanggal='" . $tgl_kerja . "'");
        $bar        = fetchdata($str)[0];
        $jmlhkbkm   = $bar['jhk'];
        $jmlumrbkm  = $bar['umr'];
        $notransbkm = $bar['notransaksi'];

        if (($jmlhkbkm > 0 || $jmlumrbkm > 0) && $param['uphOprt'] > 0) {
            exit("Warningsystem: Karyawan sudah terdaftar pada Keg BKM dengan no transaksi " . $notransbkm . "");
        }

        #= Cek di panen BKM
        $str        = selectQuery($dbname, 'kebun_prestasi_vw', 'count(*) as kegpanen, notransaksi', "karyawanid='" . $param['kode_karyawan'] . "' and tanggal='" . $tgl_kerja . "'");
        $bar        = fetchdata($str)[0];
        $jmlhkkegpnn = $bar['kegpanen'];
        $notrkegpnn = $bar['notransaksi'];
        if ($jmlhkkegpnn > 0 && $param['uphOprt'] > 0) {
            exit("Warningsystem: Karyawan sudah terdaftar pada Keg Panen dengan no transaksi " . $notrkegpnn . "");
        }

        #- Cek di supervisi BKM
        $str        = selectQuery($dbname, 'kebun_aktifitas', 'notransaksi', "tanggal='" . $tgl_kerja . "' and (nikmandor='" . $param['kode_karyawan'] . "' or nikmandor1='" . $param['kode_karyawan'] . "' or keranimuat='" . $param['kode_karyawan'] . "')");
        $res        = fetchdata($str);
        if (count($res) > 0 and $param['uphOprt'] > 0) {
            exit("Warningsystem: Karyawan sudah terdaftar pada header BKM dengan nomor : " . $res[0]['notransaksi'] . "");
        }

        #cek jika hari itu sudah ada upah dihari itu
        // $str        = selectQuery($dbname,'vhc_runhk_vw','count(*) as jumkar, notransaksi, premi',"idkaryawan='".$param['kode_karyawan']."' and tanggal='".$tgl_kerja."'");
        // $bar        =fetchdata($str)[0];
        // $jumtrans   =$bar['jumkar'];
        // $notr       =$bar['notransaksi'];
        // $premihk    =$bar['premi'];

        // $day        = date('D', strtotime($tgl_kerja));
        // if($day=='Sun')$libur=true; else $libur=false;

        // kamus hari libur
        // $strorg=selectQuery($dbname,'sdm_5harilibur','keterangan',"tanggal = '".$tgl_kerja."' and (kebun='GLOBAL' or kebun='".substr($param['no_trans'],0,4)."')");
        // $resorg=fetchData($strorg);
        // foreach ($resorg as $roworg) {
        //     if($roworg['keterangan']=='libur')$libur=true;
        //     if($roworg['keterangan']=='cuti bersama')$libur=true;
        //     if($roworg['keterangan']=='masuk')$libur=false;
        // }

        // if($libur==true and $param['uphOprt']>0){
        // exit("Warningsystem:Jika Hari libur/minggu maka nilai upah harus 0, upah ditambahkan ke premi");
        // }

        #======================= cek premi apakah lebih besar dari perhitungan ==================
        $param = $_POST;



        #======================= cek premi apakah lebih besar dari perhitungan ==================
        #= rekalkulasi upah dan premi
        $whrt       = "";
        $qry        = selectQuery($dbname, 'vhc_rundt', '*', "notransaksi='" . $param['no_trans'] . "' and jenispekerjaan='" . $param['jns_kerja'] . "' and operator='" . $param['kode_karyawan'] . "'");
        $hsl        = fetchData($qry);

        $prmiOprt   = $param['prmiOprt'];
        $prmiOprtTambahan   = $param['prmiOprtTambahan'];
        $prmiHelp   = $param['prmiHelp'];
        $prmiHelp2  = $param['prmiHelp2'];

        if ($param['blok'] != '') {
            $whrt = " and alokasibiaya='" . $param['blok'] . "'";
        } else {
            $whrt = " and alokasibiaya='" . $param['lokasikerja'] . "' ";
        }

        $whrt       .= "and kmhmawal='" . $param['kmhm_awal'] . "'";

        if ($param['proses_pekerjaan'] == 'update_kerja' && $param['prmiOprt_old'] == $prmiOprt || $param['jnsstn'] == 'PENGALI') {
            $prmiOprt   = $param['prmiOprt'];
            $prmiOprtTambahan   = $param['prmiOprtTambahan'];
            $prmiHelp   = $param['prmiHelp'];
            $prmiHelp2  = $param['prmiHelp2'];
        }

        try {
            $owlPDO->beginTransaction();

            //Masukan nilai premi
            $sup_op = "UPDATE $dbname.vhc_rundt SET premiopt='" . $prmiOprt . "',premitambahanopt ='" . $prmiOprtTambahan . "',premihelp='" . $prmiHelp . "',premihelp2='" . $prmiHelp2 . "' where notransaksi='" . $param['no_trans'] . "' and jenispekerjaan='" . $param['jns_kerja'] . "' " . $whrt . " and operator='" . $param['kode_karyawan'] . "'";
            $owlPDO->exec($sup_op);

            #cek sudah ada data belum operator
            if ($param['kode_karyawan'] != '') {
                $sql = selectQuery($dbname, 'vhc_runhk', '*', "notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_karyawan'] . "'");
                $hsl = fetchData($sql);
                if (count($hsl) > 0) {
                    $qry = selectQuery($dbname, 'vhc_rundt', 'premiopt,denda,premitambahanopt', "notransaksi='" . $param['no_trans'] . "' and operator='" . $param['kode_karyawan'] . "'");
                    $res = fetchData($qry);
                    foreach ($res as $val) {
                        @$premiall += $val['premiopt'] + $val['premitambahanopt'];
                        @$dendaall += $val['denda'];
                    }

                    $upahdiinputlagi = '';
                    if (count($res) == 1) {
                        $upahdiinputlagi = ",upah='" . $param['uphOprt'] . "' ";
                    }

                    $strvhc = "UPDATE $dbname.vhc_runhk SET premi='" . round($premiall, 2) . "',penalty='$dendaall' $upahdiinputlagi WHERE notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_karyawan'] . "'";
                    $owlPDO->exec($strvhc);
                } else {
                    #insert vhc_runhk
                    $str = "INSERT INTO $dbname.vhc_runhk (`notransaksi`,`idkaryawan`,`posisi`,`tanggal`,`statuskaryawan`,`upah`,`premi`,`penalty`,`keterangan`)
                                VALUES ('" . $param['no_trans'] . "','" . $param['kode_karyawan'] . "','0','" . $tgl_kerja . "','','" . ($jlhpjbtbkm == 0 ? $param['uphOprt'] : 0) . "','" . ($prmiOprt + $prmiOprtTambahan) . "','" . $param['pnltyOprt'] . "','" . $param['ketOprt'] . "')";
                    $owlPDO->exec($str);
                }
            }

            #cek sudah ada data belum helper
            if ($param['kode_helper'] != '') {
                $sql = selectQuery($dbname, 'vhc_runhk', '*', "notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper'] . "'");
                $hsl = fetchData($sql);
                if (count($hsl) > 0) {
                    $qry = selectQuery($dbname, 'vhc_rundt', 'premihelp,denda', "notransaksi='" . $param['no_trans'] . "' and helper ='" . $param['kode_helper'] . "'");
                    $res = fetchData($qry);
                    $premiall = 0;
                    $dendaall = 0;
                    foreach ($res as $val) {
                        @$premiall += $val['premihelp'];
                        @$dendaall += $val['denda'];
                    }

                    $upahdiinputlagi = '';
                    if (count($res) == 1) {
                        $upahdiinputlagi = ",upah='" . $param['uphHelp'] . "' ";
                    }

                    $strvhc = "UPDATE $dbname.vhc_runhk SET premi='" . round($premiall, 2) . "',penalty='$dendaall' $upahdiinputlagi WHERE notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper'] . "'";
                    $owlPDO->exec($strvhc);
                } else {
                    #insert vhc_runhk
                    $str = "INSERT INTO $dbname.vhc_runhk (`notransaksi`,`idkaryawan`,`posisi`,`tanggal`,`statuskaryawan`,`upah`,`premi`,`penalty`,`keterangan`)
                                VALUES ('" . $param['no_trans'] . "','" . $param['kode_helper'] . "','1','" . $tgl_kerja . "','','" . ($jlhpjbtbkm == 0 ? $param['uphHelp'] : 0) . "','" . $prmiHelp . "','" . $param['pnltyOprt'] . "','" . $param['ketOprt'] . "')";
                    $owlPDO->exec($str);
                }
            }

            #cek sudah ada data belum helper 2
            if ($param['kode_helper2'] != '') {
                $sql = selectQuery($dbname, 'vhc_runhk', '*', "notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper2'] . "'");
                $hsl = fetchData($sql);
                if (count($hsl) > 0) {
                    $qry = selectQuery($dbname, 'vhc_rundt', 'premihelp2,denda', "notransaksi='" . $param['no_trans'] . "' and helper2 ='" . $param['kode_helper2'] . "'");
                    $res = fetchData($qry);
                    $premiall = 0;
                    $dendaall = 0;
                    foreach ($res as $val) {
                        @$premiall += $val['premihelp2'];
                        @$dendaall += $val['denda'];
                    }

                    $upahdiinputlagi = '';
                    if (count($res) == 1) {
                        $upahdiinputlagi = ",upah='" . $param['uphHelp2'] . "' ";
                    }

                    $strvhc = "UPDATE $dbname.vhc_runhk SET premi='" . round($premiall, 2) . "',penalty='$dendaall' $upahdiinputlagi WHERE notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper2'] . "'";
                    $owlPDO->exec($strvhc);
                } else {
                    #insert vhc_runhk
                    $str = "INSERT INTO $dbname.vhc_runhk (`notransaksi`,`idkaryawan`,`posisi`,`tanggal`,`statuskaryawan`,`upah`,`premi`,`penalty`,`keterangan`)
                                VALUES ('" . $param['no_trans'] . "','" . $param['kode_helper2'] . "','1','" . $tgl_kerja . "','','" . ($jlhpjbtbkm == 0 ? $param['uphHelp2'] : 0) . "','" . $prmiHelp . "','" . $param['pnltyOprt'] . "','" . $param['ketOprt'] . "')";
                    $owlPDO->exec($str);
                }
            }


            #execute
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;
    case 'delete_opt':

        $svhc = "select * from " . $dbname . ".vhc_rundt where notransaksi='" . $param['no_trans'] . "'";
        $rvhc = fetchData($svhc);
        if (count($rvhc) == 0) {
            $sdel = "delete from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_karyawan'] . "'";
            try {
                $owlPDO->exec($sdel);
                echo "";
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $sdel = "delete from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper'] . "'";
            try {
                $owlPDO->exec($sdel);
                echo "";
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $sdel = "delete from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper2'] . "'";
            try {
                $owlPDO->exec($sdel);
                echo "";
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $sdel = "delete from " . $dbname . ".vhc_runhk where notransaksi='" . $param['no_trans'] . "' and idkaryawan='" . $param['kode_helper3'] . "'";
            try {
                $owlPDO->exec($sdel);
                echo "";
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }

        break;
    case 'postingdata':
        $personPosting      = getPostingJabatan('traksi');
        if (in_array(getKary($user_entry, 'kodejabatan'), $personPosting)) {
            $dt = 0;
            $str = selectQuery($dbname, 'vhc_rundt', 'count(*) as dt', "notransaksi='" . $param['no_trans'] . "'");
            $bar = fetchData($str)[0];
            $dt = $bar['dt'];

            if ($dt < 1) {
                exit("Warningsystem : Data Detail Pekerjaan dengan notransaksi " . $param['no_trans'] . " masih kosong, harap melengkapi detail untuk dapat .
                    memposting data.");
            }

            try {
                $owlPDO->beginTransaction();
                $blokkecil = array();
                #= Proporsikan prestasi,upah,premi
                #== Ambil luasan blok kecil
                $sql = selectQuery($dbname, 'setup_blok', 'luasareaproduktif,kodeorg,indukblok', "status='A' and indukblok in (SELECT alokasibiaya FROM $dbname.vhc_rundt WHERE notransaksi='{$param['no_trans']}')");
                // exit('warning:'.$sql);
                $res = fetchData($sql);
                foreach ($res as $val) {
                    # Bentuk array luas blok
                    $blokkecil[$val['indukblok']][$val['kodeorg']] = $val['kodeorg'];
                    $ttlluasblok[$val['indukblok']] += $val['luasareaproduktif'];
                    $luasblokkecil[$val['indukblok']][$val['kodeorg']] = $val['luasareaproduktif'];
                }
                #== Ambil data detail pekerjaan
                $qry = "SELECT a.*,b.kodeorg,b.indukblok,b.statusblok FROM $dbname.vhc_rundt a LEFT JOIN $dbname.setup_blok b ON a.alokasibiaya=b.indukblok WHERE b.status='A' AND notransaksi='{$param['no_trans']}'";
                $rsx = fetchData($qry);
                foreach ($rsx as $v) {
                    # Bentuk array detail pekerjaan
                    $jenispekerjaan[$v['alokasibiaya']][$v['kodeorg']] = trim($v['jenispekerjaan']);
                    $jumlahrit[$v['alokasibiaya']][$v['kodeorg']] = $v['jumlahrit'];
                    $keterangan[$v['alokasibiaya']][$v['kodeorg']] = $v['keterangan'];
                    $biaya[$v['alokasibiaya']][$v['kodeorg']] = $v['biaya'];
                    $kmhmawal[$v['alokasibiaya']][$v['kodeorg']] = $v['kmhmawal'];
                    $kmhmakhir[$v['alokasibiaya']][$v['kodeorg']] = $v['kmhmakhir'];
                    $jumlah[$v['alokasibiaya']][$v['kodeorg']] = $v['jumlah'];
                    $satuan[$v['alokasibiaya']][$v['kodeorg']] = $v['satuan'];
                    $kodesegment[$v['alokasibiaya']][$v['kodeorg']] = $v['kodesegment'];
                    $operator[$v['alokasibiaya']][$v['kodeorg']] = $v['operator'];
                    $helper[$v['alokasibiaya']][$v['kodeorg']] = $v['helper'];
                    $helper2[$v['alokasibiaya']][$v['kodeorg']] = $v['helper2'];
                    $helper3[$v['alokasibiaya']][$v['kodeorg']] = $v['helper3'];
                    $denda[$v['alokasibiaya']][$v['kodeorg']] = $v['denda'];
                    if (strlen($v['alokasibiaya']) > 6) { //jika alokasi ke blok
                        $karydt[$v['alokasibiaya']][$v['operator']] = $v['operator'];
                        $prestasinya[$v['alokasibiaya']][$v['operator']] = $v['beratmuatan'];
                        $preminya[$v['alokasibiaya']][$v['operator']] = $v['premiopt'];
                        $orangperblok[$v['operator']] += 1;
                    }
                }
                #== Ambil data yg umum saja
                $qry1 = "SELECT * FROM $dbname.vhc_rundt WHERE notransaksi='{$param['no_trans']}' AND alokasibiaya='" . $_SESSION['empl']['lokasitugas'] . "'";
                $rsx1 = fetchData($qry1);
                foreach ($rsx1 as $val1) {
                    //Insert yang umum
                    $data = array(
                        'notransaksi'   => $param['no_trans'],
                        'jenispekerjaan' => trim($val1['jenispekerjaan']),
                        'alokasibiaya'  => $val1['alokasibiaya'],
                        'beratmuatan'   => $val1['beratmuatan'],
                        'jumlahrit'     => $val1['jumlahrit'],
                        'keterangan'    => $val1['keterangan'],
                        'biaya'         => $val1['biaya'],
                        'kmhmawal'      => $val1['kmhmawal'],
                        'kmhmakhir'     => $val1['kmhmakhir'],
                        'jumlah'        => $val1['jumlah'],
                        'satuan'        => $val1['satuan'],
                        'kodesegment'   => $val1['kodesegment'],
                        'operator'      => $val1['operator'],
                        'helper'        => $val1['helper'],
                        'helper2'       => $val1['helper2'],
                        'helper3'       => $val1['helper3'],
                        'denda'         => $val1['denda'],
                        'premiopt'      => $val1['premiopt']
                    );

                    // if(strlen($duk) >6){//selain umum masuk _detail
                    $sins = insertQuery($dbname, 'vhc_rundt_detail', $data, array_keys($data));
                    $owlPDO->exec($sins);
                }

                if (count($blokkecil) > 0) {
                    #== proporsikan ke masing-masing blok kecil
                    foreach ($blokkecil as $indk => $arrkcl) {
                        foreach ($arrkcl as $kcl) {
                            # luas proporsi per blok
                            // $luasproporsi[$indk][$kcl]=$luasblokkecil[$indk][$kcl]/$ttlluasblok[$indk];
                            foreach ($karydt[$indk] as $kry) {
                                # prestasi proporsi per blok
                                $prstsiproporsi[$indk][$kcl] = ($luasblokkecil[$indk][$kcl] / $ttlluasblok[$indk]) * $prestasinya[$indk][$kry];
                                $premiproporsi[$indk][$kcl] = ($luasblokkecil[$indk][$kcl] / $ttlluasblok[$indk]) * $preminya[$indk][$kry];
                            }
                        }
                    }
                    #== input ke tabel detail proporsinya
                    isset($blokkecil);
                    foreach ($blokkecil as $duk => $arrkcl) {
                        foreach ($arrkcl as $cil) {
                            if (substr($jenispekerjaan[$duk][$cil], 0, 3) == '128' and getBlok($cil, 'statusblok') == 'BBT') {

                                $data = array(
                                    'notransaksi'   => $param['no_trans'],
                                    'jenispekerjaan' => trim($jenispekerjaan[$duk][$cil]),
                                    'alokasibiaya'  => $cil,
                                    'beratmuatan'   => $prstsiproporsi[$duk][$cil],
                                    'jumlahrit'     => $jumlahrit[$duk][$cil],
                                    'keterangan'    => $keterangan[$duk][$cil],
                                    'biaya'         => $biaya[$duk][$cil],
                                    'kmhmawal'      => $kmhmawal[$duk][$cil],
                                    'kmhmakhir'     => $kmhmakhir[$duk][$cil],
                                    'jumlah'        => $jumlah[$duk][$cil],
                                    'satuan'        => $satuan[$duk][$cil],
                                    'kodesegment'   => $kodesegment[$duk][$cil],
                                    'operator'      => $operator[$duk][$cil],
                                    'helper'        => $helper[$duk][$cil],
                                    'helper2'       => $helper2[$duk][$cil],
                                    'helper3'       => $helper3[$duk][$cil],
                                    'denda'         => $denda[$duk][$cil],
                                    'premiopt'      => $premiproporsi[$duk][$cil]
                                );

                                // if(strlen($duk) >6){//selain umum masuk _detail
                                $sins = insertQuery($dbname, 'vhc_rundt_detail', $data, array_keys($data));
                                $owlPDO->exec($sins);
                                // }
                            }
                            // exit('warning:'.$jenispekerjaan[$duk][$cil].'__'.getBlok($cil,'statusblok').'__'.$cil);
                            if ((substr($jenispekerjaan[$duk][$cil], 0, 3) == '621' and getBlok($cil, 'statusblok') == 'TM') || (substr($jenispekerjaan[$duk][$cil], 0, 3) == '611' and getBlok($cil, 'statusblok') == 'TM')) {

                                $data = array(
                                    'notransaksi'   => $param['no_trans'],
                                    'jenispekerjaan' => trim($jenispekerjaan[$duk][$cil]),
                                    'alokasibiaya'  => $cil,
                                    'beratmuatan'   => $prstsiproporsi[$duk][$cil],
                                    'jumlahrit'     => $jumlahrit[$duk][$cil],
                                    'keterangan'    => $keterangan[$duk][$cil],
                                    'biaya'         => $biaya[$duk][$cil],
                                    'kmhmawal'      => $kmhmawal[$duk][$cil],
                                    'kmhmakhir'     => $kmhmakhir[$duk][$cil],
                                    'jumlah'        => $jumlah[$duk][$cil],
                                    'satuan'        => $satuan[$duk][$cil],
                                    'kodesegment'   => $kodesegment[$duk][$cil],
                                    'operator'      => $operator[$duk][$cil],
                                    'helper'        => $helper[$duk][$cil],
                                    'helper2'       => $helper2[$duk][$cil],
                                    'helper3'       => $helper3[$duk][$cil],
                                    'denda'         => $denda[$duk][$cil],
                                    'premiopt'      => $premiproporsi[$duk][$cil]
                                );

                                // if(strlen($duk) >6){//selain umum masuk _detail
                                $sins = insertQuery($dbname, 'vhc_rundt_detail', $data, array_keys($data));
                                $owlPDO->exec($sins);
                                // }
                            }
                            if (substr($jenispekerjaan[$duk][$cil], 0, 3) == '126' and (getBlok($cil, 'statusblok') == 'TBM' || getBlok($cil, 'statusblok') == 'TB')) {

                                $data = array(
                                    'notransaksi'   => $param['no_trans'],
                                    'jenispekerjaan' => trim($jenispekerjaan[$duk][$cil]),
                                    'alokasibiaya'  => $cil,
                                    'beratmuatan'   => $prstsiproporsi[$duk][$cil],
                                    'jumlahrit'     => $jumlahrit[$duk][$cil],
                                    'keterangan'    => $keterangan[$duk][$cil],
                                    'biaya'         => $biaya[$duk][$cil],
                                    'kmhmawal'      => $kmhmawal[$duk][$cil],
                                    'kmhmakhir'     => $kmhmakhir[$duk][$cil],
                                    'jumlah'        => $jumlah[$duk][$cil],
                                    'satuan'        => $satuan[$duk][$cil],
                                    'kodesegment'   => $kodesegment[$duk][$cil],
                                    'operator'      => $operator[$duk][$cil],
                                    'helper'        => $helper[$duk][$cil],
                                    'helper2'       => $helper2[$duk][$cil],
                                    'helper3'       => $helper3[$duk][$cil],
                                    'denda'         => $denda[$duk][$cil],
                                    'premiopt'      => $premiproporsi[$duk][$cil]
                                );

                                // if(strlen($duk) >6){//selain umum masuk _detail
                                $sins = insertQuery($dbname, 'vhc_rundt_detail', $data, array_keys($data));
                                $owlPDO->exec($sins);
                                // }
                            }
                        }
                    }
                }
                #= Update Flag Transaksi
                $postedtime = date('Y-m-d H:i:s');
                $dataUpd = array(
                    'posting' => '1',
                    'postingby' => $user_entry,
                    'postedtime' => $postedtime
                );
                $sudPost = updateQuery($dbname, 'vhc_runht', $dataUpd, "notransaksi='" . $param['no_trans'] . "'");
                $owlPDO->exec($sudPost);

                #execute
                $owlPDO->commit();
            } catch (PDOException $e) {
                $owlPDO->rollback();
                echo "Error, " . addslashes($e->getMessage());
                die();
            }
        } else {
            exit(" Gagal : Jabatan anda belum terdaftar untuk posting transaksi\nuntuk mendaftarkan masuk ke menu <a href=\"javascript:do_load('setup_posting')\">SETUP > POSTING</a>");
        }
        break;
    default:
        break;
}
?>

<?php
function updateKmHm($kodevhc)
{
    global $dbname;
    global $owlPDO;
    // Get KM/HM Akhir
    $qKm = selectQuery($dbname, 'vhc_kmhmakhir_vw', '*', "kodevhc='" . $kodevhc . "'");
    $resKm = fetchData($qKm);
    $param['kmhm_akhir'] = (empty($resKm)) ? 0 : $resKm[0]['kmhmakhir'];

    $dataIns = array($kodevhc, $param['kmhm_akhir']);
    $qIns = insertQuery($dbname, 'vhc_kmhm_track', $dataIns);
    try {
        $owlPDO->exec($qIns);
    } catch (PDOException $e) {
        $dataUpd = array('kmhmakhir' => $param['kmhm_akhir']);
        $qUpd = updateQuery($dbname, 'vhc_kmhm_track', $dataUpd, "kodevhc='" . $kodevhc . "'");
        try {
            $owlPDO->exec($qUpd);
        } catch (PDOException $e) {
            print " Update H/KM Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    }
}

function hitungpremiold($param)
{
    global $dbname;
    global $owlPDO;
    global $optkelvhc;
    $totalpremi = '';

    // echo"<pre>";
    // print_r($param);
    // echo"</pre>"; exit('error');

    $tglTrans = tanggalsystem(checkPostGet('tglTrans', ''));
    $kar1 = checkPostGet('kdKry', '');
    $kar2 = checkPostGet('kar', '');
    $jenis = checkPostGet('jenis', '');
    if ($kar1 != '') {
        $kar = $kar1;
    } else {
        $kar = $kar2;
    }
    $proses = checkPostGet('proses', '');
    $pt = checkPostGet('pt', '');
    $posisi = checkPostGet('posisi', '');
    $jenisvhc = checkPostGet('jenisvhc', '');
    $optpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
    $notransaksi = checkPostGet('notransaksi', '');
    if ($notransaksi == '') {
        $notransaksi = checkPostGet('notrans', '');
    }


    #hari minggu
    @$tempUnit = explode('/', $notransaksi);
    $kodetraksi = $tempUnit[0];
    $day = date('D', strtotime($tglTrans));
    if ($day == 'Sun') $libur = true;
    else $libur = false;
    // kamus hari libur
    $strorg = "select * from " . $dbname . ".sdm_5harilibur where tanggal = '" . $tglTrans . "' and 
            (kebun='GLOBAL' or kebun='" . substr($kodetraksi, 0, 4) . "')";
    $queorg = $owlPDO->query($strorg) or die(print " Gagal: " . PDOException::getMessage());
    $queorg->setFetchMode(PDO::FETCH_ASSOC);
    while ($roworg = $queorg->fetch()) {
        if ($roworg['keterangan'] == 'libur') $libur = true;
        if ($roworg['keterangan'] == 'masuk') $libur = false;
    }
    $gapok = 0;
    #gaji pokok dan datakaryawan
    $str = " select a.karyawanid,a.namakaryawan,a.tipekaryawan,b.jumlah from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5gajipokok b
            on a.karyawanid=b.karyawanid where a.karyawanid='" . $kar . "' and b.tahun='" . substr($tglTrans, 0, 7) . "' and b.idkomponen=1";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $bar = $res->fetch();
    if ($libur == true) {
        $gapok = $bar['jumlah'] / 25;
    }

    if ($gapok == 0) {
        //exit("Warningsystem: Gaji Pokok Belum Ada !");
    }

    @$pt = $optpt[substr($kodetraksi, 0, 4)];
    @$vhckel = $optkelvhc[$jenisvhc];

    @$tempUnit = explode('/', $notransaksi);
    @$pt = $optpt[$tempUnit['0']];

    # Perhitungan premi alat berat
    $str = "select sum(a.jumlah) as jumlah,a.notransaksi,b.tanggal,c.basis,c.premibasis,c.premilebihbasis,
                a.satuan,b.kodevhc,b.jenisvhc from " . $dbname . ".vhc_rundt a  
                left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
                left join " . $dbname . ".vhc_5premialatberat c on b.jenisvhc=c.jenisvhc
                where a.notransaksi='" . $notransaksi . "' and c.kodept='" . $pt . "' and c.posisi='" . $posisi . "' and b.jenisvhc='" . $jenisvhc . "'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while ($bar = $res->fetch()) {
        $btshmpremi = $bar['basis'];
        $premilb1 = $bar['premibasis'];
        $premilb2 = $bar['premilebihbasis'];
        $jlhhm = $bar['jumlah'];
    }
    $premiab = '';
    if ($jlhhm > 0 and $jlhhm <= $btshmpremi) {
        $premiab += $jlhhm * $premilb1;
    } else if ($jlhhm > $btshmpremi) {
        $premiab += ($btshmpremi * $premilb1) + (($jlhhm - $btshmpremi) * $premilb2);
    } else {
        $premiab = 0;
    }

    $premikg = '';
    $pres[] = '';
    $str = "select  a.*,b.tanggal,c.basis,c.premibasis,c.premilebihbasis,b.kodevhc,b.jenisvhc 
                from " . $dbname . ".vhc_rundt a
                left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
                left join " . $dbname . ".vhc_5premikegiatan c on a.jenispekerjaan=c.kodekegiatan
                where a.notransaksi='" . $notransaksi . "' and c.kodept='" . $pt . "' and c.posisi='" . $posisi . "'
                and c.vhc='" . $jenisvhc . "'  order by kmhmawal asc";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while ($bar = $res->fetch()) {
        @$keg[$bar['jenispekerjaan']] = $bar['jenispekerjaan'];
        $pres[$bar['jenispekerjaan']] += $bar['beratmuatan'];
        @$basis[$bar['jenispekerjaan']] = $bar['basis'];
        @$rplb[$bar['jenispekerjaan']] = $bar['premilebihbasis'];
        @$hmkm[$bar['jenispekerjaan']] = $bar['jumlah'];
    }


    foreach (@$keg as $jkeg) {
        if (@$pres[$jkeg] > 0 and @$pres[$jkeg] >= @$basis[$jkeg]) {
            @$premikg += $pres[$jkeg] * $rplb[$jkeg];
        }
    }


    if ($vhckel == 'AB') {
        @$totalpremi = $premiab + $premikg;
        @$totalpremidtl = $premiab + $premikg;
    } else {
        @$totalpremi = $premikg;
        @$totalpremidtl = $premikg;
    }

    $str = "select sum(premi) as premi from " . $dbname . ".vhc_runhk where notransaksi='" . $notransaksi . "' and posisi='" . $posisi . "'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while ($bar = $res->fetch()) {
        $cekpremi = $bar['premi'];
    }

    if ($totalpremi - $cekpremi <= 0) {
        $totalpremi = 0;
    } else {
        $totalpremi = $totalpremi - $cekpremi;
    }
    $totalpremi = $totalpremi + $gapok;
    $ttlhmkm = 0;
    $ttlrppres = 0;
    #ini buat nampilkan di detail premi oprt
    if ($jenis == 'detail') {
        $tab = "";
        $no = 0;
        foreach (@$keg as $jkeg) {
            $no++;
            $nmkeg = makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $jkeg . "'");
            $nmsat = makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,satuan', "kodekegiatan='" . $jkeg . "'");
            $tab .= "<tr class=rowcontent  id=tr_$no>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=left>" . $jkeg . " - " . $nmkeg[$jkeg] . "</td>";
            $tab .= "<td align=left>" . $nmsat[$jkeg] . "</td>";
            $tab .= "<td align=right>" . @number_format($pres[$jkeg], 2) . "</td>";
            $tab .= "<td align=right>" . @number_format($hmkm[$jkeg], 2) . "</td>";
            $tab .= "<td align=right>" . @number_format($rplb[$jkeg], 2) . "</td>";
            $tab .= "<td align=right style=background-color:grey></td>";
            $tab .= "<td align=right>" . @number_format($pres[$jkeg] * $rplb[$jkeg]) . "</td>";
            $tab .= "<td align=right style=background-color:grey></td>";
            $tab .= "<td align=right>" . @number_format($pres[$jkeg] * $rplb[$jkeg]) . "</td>";
            $tab .= "</tr>";
            @$ttlhmkm += $hmkm[$jkeg];
            $ttlrppres += $pres[$jkeg] * $rplb[$jkeg];
        }

        if ($libur == true) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=right></td>";
            $tab .= "<td align=left>Gaji Pokok (Hari Libur)</td>";
            $tab .= "<td align=left colspan=7 style=background-color:grey></td>";
            $tab .= "<td align=right>" . @number_format($gapok, 2) . "</td>";
            $tab .= "</tr>";
        }
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=center colspan=3>TOTAL</td>";
        $tab .= "<td align=right style=background-color:grey></td>";
        $tab .= "<td align=right>" . @number_format($ttlhmkm, 2) . "</td>";
        $tab .= "<td align=right style=background-color:grey></td>";
        $tab .= "<td align=right>" . @number_format($premilb2, 2) . "</td>";
        $tab .= "<td align=right>" . @number_format($ttlrppres) . "</td>";
        $tab .= "<td align=right>" . @number_format($ttlhmkm * $premilb2, 2) . "</td>";
        $tab .= "<td align=right>" . @number_format($totalpremidtl + $gapok) . "</td>";

        echo $tab;
    } else {
        return round($totalpremi);
    }
}

function hitungpremi($param)
{
    global $dbname;
    global $tgl_kerja;

    $cekbasis               = 0;
    $hasilnya['preminya']   = 0;
    $hasilnya['premihelp']  = 0;
    $hasilnya['premihelp2'] = 0;
    $hasilnya['premihelp3'] = 0;
    $hasilnya['jnsprmi']    = '';

    #= Cek tipe karyawan
    $hasilnya['tipekaryawan'] = ($param['kode_karyawan'] != '' ? getKary($param['kode_karyawan'], 'tipekaryawan') : '');

    #= Cek apakah tgl dipilih hari libur atau bukan
    $strhr   = selectQuery($dbname, 'sdm_5harilibur', 'keterangan', "tanggal = '" . $tgl_kerja . "' and (kebun='GLOBAL' or kebun='" . $param['lokasi_kerja'] . "')");
    if (count(fetchData($strhr)) > 0) {
        $hslhr   = fetchdata($strhr);
        $hasilnya['ketlibur']   = $hslhr[0]['keterangan'];
        if ($hasilnya['ketlibur'] == '') {
            $hasilnya['ketlibur']   = 'kerja';
        } else if ($hasilnya['ketlibur'] == 'cuti bersama') {
            $hasilnya['ketlibur']   = 'libur';
        } else {
            $hasilnya['ketlibur']   = $hasilnya['ketlibur'];
        }
    } else {
        $hasilnya['ketlibur']   = '';
    }

    if ($hasilnya['ketlibur'] == 'libur') {
        $whrhari = " and jenishari='libur'";
    } else {
        $whrhari = " and jenishari='kerja'";
    }

    if ($param['kode_karyawan'] != '') { //jika operator/supir dipilih   
        #== Ambil setup premi kegiatan operator / supir berdasarkan lokasi kerja 
        $strx   = selectQuery($dbname, 'vhc_5premikegiatan', '*', "kodekegiatan='" . $param['jns_kerja'] . "' and unit='" . $param['lokasi_kerja'] . "' and divisi='" . substr($param['blok'], 0, 6) . "'  and vhc='" . $param['jenisvhc'] . "'  and posisi in ('0','2') " . $whrhari . " and statuspremi='1'");
        $resx   = fetchdata($strx);

        if (count($resx) > 0) { //jika preminya ada
            $hasilnya['jnsprmi']    = $resx[0]['jenisbasis'];

            #== Ambil data prestasi yang sudah tersimpan
            $query  = selectQuery($dbname, 'vhc_rundt', 'SUM(beratmuatan) AS brtaftinput', "notransaksi='" . $param['no_trans'] . "' AND jenispekerjaan='" . $param['jns_kerja'] . "' and operator='" . $param['kode_karyawan'] . "'");
            $hasil  = fetchdata($query);
            $brtsave = $hasil[0]['brtaftinput'];

            switch ($hasilnya['jnsprmi']) {
                case 'KMHM/RIT':
                    # Perhitungan Premi apabila basisnya menggunakan KMHM/RIT
                    $cekbasis = $param['jlhhm'] / $param['brt_muatan'];
                    if (perbandingan($cekbasis, $resx[0]['basis3'], $resx[0]['penanda3']) && $resx[0]['basis3'] != 0) {
                        $hasilnya['preminya'] = (($param['jmlh_rit'] - $resx[0]['pengurangprestasi3'])) * $resx[0]['premibasis3'];
                    } else if (perbandingan($cekbasis, $resx[0]['basis2'], $resx[0]['penanda2']) && $resx[0]['basis2'] != 0) {
                        $hasilnya['preminya'] = (($param['jmlh_rit'] - $resx[0]['pengurangprestasi2'])) * $resx[0]['premibasis2'];
                    } else if (perbandingan($cekbasis, $resx[0]['basis'], $resx[0]['penanda']) && $resx[0]['basis'] != 0) {
                        $hasilnya['preminya'] = (($param['jmlh_rit'])) * $resx[0]['premilebihbasis'];
                    }
                    break;
                case 'KMHM':
                    # Perhitungan Premi apabila basisnya menggunakan KMHM/RIT
                    $cekbasis = $param['jlhhm'];
                    if (perbandingan($cekbasis, $resx[0]['basis3'], $resx[0]['penanda3']) && $resx[0]['basis3'] != 0) {
                        $hasilnya['preminya'] = (($cekbasis - $resx[0]['pengurangprestasi3'])) * $resx[0]['premibasis3'];
                    } else if (perbandingan($cekbasis, $resx[0]['basis2'], $resx[0]['penanda2']) && $resx[0]['basis2'] != 0) {
                        $hasilnya['preminya'] = (($cekbasis - $resx[0]['pengurangprestasi2'])) * $resx[0]['premibasis2'];
                    } else if (perbandingan($cekbasis, $resx[0]['basis'], $resx[0]['penanda']) && $resx[0]['basis'] != 0) {
                        $hasilnya['preminya'] = (($cekbasis)) * $resx[0]['premilebihbasis'];
                    }
                    break;
                case 'RIT':
                    # Perhitungan Premi apabila basisnya menggunakan RIT
                    $cekbasis = $param['jmlh_rit'];
                    if (perbandingan($cekbasis, $resx[0]['basis3'], $resx[0]['penanda3']) && $resx[0]['basis3'] != 0) {
                        $hasilnya['preminya'] = (($cekbasis - $resx[0]['pengurangprestasi3'])) * $resx[0]['premibasis3'];
                    } else if (perbandingan($cekbasis, $resx[0]['basis2'], $resx[0]['penanda2']) && $resx[0]['basis2'] != 0) {
                        $hasilnya['preminya'] = (($cekbasis - $resx[0]['pengurangprestasi2'])) * $resx[0]['premibasis2'];
                    } else if (perbandingan($cekbasis, $resx[0]['basis'], $resx[0]['penanda']) && $resx[0]['basis'] != 0) {
                        $hasilnya['preminya'] = (($cekbasis)) * $resx[0]['premilebihbasis'];
                    }
                    break;
                default:
                    # Perhitungan Premi yang lainnya
                    if (perbandingan($param['brt_muatan'], $resx[0]['basis3'], $resx[0]['penanda3']) && $resx[0]['basis3'] != 0) {
                        $hasilnya['preminya'] = (($param['brt_muatan'] - $resx[0]['pengurangprestasi3'])) * $resx[0]['premibasis3'];
                    } else if (perbandingan($param['brt_muatan'], $resx[0]['basis2'], $resx[0]['penanda2']) && $resx[0]['basis2'] != 0) {
                        $hasilnya['preminya'] = (($param['brt_muatan'] - $resx[0]['pengurangprestasi2'])) * $resx[0]['premibasis2'];
                    } else if (perbandingan($param['brt_muatan'], $resx[0]['basis'], $resx[0]['penanda']) && $resx[0]['basis'] != 0) {
                        $hasilnya['preminya'] = (($param['brt_muatan'] - $resx[0]['pengurangprestasi'])) * $resx[0]['premilebihbasis'];
                    }
                    break;
            }
        }
    }

    #== jika prestasi kurang dari 0 (-) minus setelah dikurang basis maka 0 kan saja
    if ($hasilnya['preminya'] < 0) {
        $hasilnya['preminya'] = 0;
    }

    $nmkegnya       = makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $param['jns_kerja'] . "'");
    $pattern = "/TBS/i";
    $hasilnya['nmkegiatantbs'] = preg_match_all($pattern, $nmkegnya[$param['jns_kerja']]);

    return json_encode($hasilnya);
}

function hitungpremi_table2($param)
{

    global $dbname;
    global $tgl_kerja;

    $cekbasis               = 0;
    $hasilnya['preminya']   = 0;
    $hasilnya['premihelp']  = 0;
    $hasilnya['premihelp2'] = 0;
    $hasilnya['premihelp3'] = 0;
    $hasilnya['jnsprmi']    = '';

    #= Cek tipe karyawan
    $hasilnya['tipekaryawan'] = ($param['kode_karyawan'] != '' ? getKary($param['kode_karyawan'], 'tipekaryawan') : '');

    #= Cek apakah tgl dipilih hari libur atau bukan
    $strhr   = selectQuery($dbname, 'sdm_5harilibur', 'keterangan', "tanggal = '" . $tgl_kerja . "' and (kebun='GLOBAL' or kebun='" . $param['lokasi_kerja'] . "')");
    if (count(fetchData($strhr)) > 0) {
        $hslhr   = fetchdata($strhr);
        $hasilnya['ketlibur']   = $hslhr[0]['keterangan'];

        if ($hasilnya['ketlibur'] == '') {
            $hasilnya['ketlibur']   = 'kerja';
        } else if ($hasilnya['ketlibur'] == 'cuti bersama') {
            $hasilnya['ketlibur']   = 'libur';
        } else {
            $hasilnya['ketlibur']   = $hasilnya['ketlibur'];
        }
    } else {
        $hasilnya['ketlibur']   = '';
    }

    if ($hasilnya['ketlibur'] == 'libur') {
        $whrhari = " and jenishari='libur'";
    } else {
        $whrhari = " and jenishari='kerja'";
    }

    ## INI PREMI OPERATOR/DRIVER
    if ($param['kode_karyawan'] != '') {
        #== Ambil setup premi kegiatan operator / supir berdasarkan lokasi kerja 
        $strx   = selectQuery($dbname, 'vhc_5premikegiatan_v2', '*', "kodekegiatan='" . $param['jns_kerja'] . "' and unit='" . $param['lokasi_kerja'] . "'  and vhc='" . $param['jenisvhc'] . "'  and posisi in ('0','2') " . $whrhari . " and statuspremi='1'");
        $resx   = fetchdata($strx);

        ## Operator
        if (count($resx) > 0) {

            $hasilnya['jnsprmi']    = $resx[0]['jenisbasis'];

            switch ($hasilnya['jnsprmi']) {
                case 'PRESTASI':
                    # Perhitungan Premi apabila basisnya menggunakan prestasi
                    $hasilKerja = $param['brt_muatan'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['preminya'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }
                    break;
                case 'KMHM':
                    # Perhitungan Premi apabila basisnya menggunakan KMHM
                    $hasilKerja = $param['jlhhm'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['preminya'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }

                    break;
                case 'RIT':
                    # Perhitungan Premi apabila basisnya menggunakan RIT
                    $hasilKerja = $param['jmlh_rit'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['preminya'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }
                    break;
                case 'TETAP':
                    $hasilnya['preminya'] = $resx[0]['premilebihbasis'];
                    break;
                default:

                    # 0 kan jika gak ada basis
                    $hasilnya['preminya'] = $resx[0]['premilebihbasis'];
            }
        }
    }

    ## INI PREMI HELPER 1
    if ($param['kode_helper'] != '') {
        #== Ambil setup premi kegiatan helper lokasi kerja 
        $strx   = selectQuery($dbname, 'vhc_5premikegiatan_v2', '*', "kodekegiatan='" . $param['jns_kerja'] . "' and unit='" . $param['lokasi_kerja'] . "'  and vhc='" . $param['jenisvhc'] . "'  and posisi in ('1') " . $whrhari . " and statuspremi='1'");
        $resx   = fetchdata($strx);

        ## Operator
        if (count($resx) > 0) {

            $hasilnya['jnsprmi']    = $resx[0]['jenisbasis'];

            switch ($hasilnya['jnsprmi']) {
                case 'PRESTASI':
                    # Perhitungan Premi apabila basisnya menggunakan prestasi
                    $hasilKerja = $param['brt_muatan'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['premihelp'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }
                    break;
                case 'KMHM':
                    # Perhitungan Premi apabila basisnya menggunakan KMHM
                    $hasilKerja = $param['jlhhm'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['premihelp'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }

                    break;
                case 'RIT':
                    # Perhitungan Premi apabila basisnya menggunakan RIT
                    $hasilKerja = $param['jmlh_rit'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['premihelp'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }
                    break;
                case 'TETAP':
                    $hasilnya['premihelp'] = $resx[0]['premilebihbasis'];
                    break;
                default:

                    # 0 kan jika gak ada basis
                    $hasilnya['premihelp'] = $resx[0]['premilebihbasis'];
            }
        }
    }

    ## INI PREMI HELPER 2
    if ($param['kode_helper2'] != '') {
        #== Ambil setup premi kegiatan helper lokasi kerja 
        $strx   = selectQuery($dbname, 'vhc_5premikegiatan_v2', '*', "kodekegiatan='" . $param['jns_kerja'] . "' and unit='" . $param['lokasi_kerja'] . "'  and vhc='" . $param['jenisvhc'] . "'  and posisi in ('1') " . $whrhari . " and statuspremi='1'");
        $resx   = fetchdata($strx);

        ## Operator
        if (count($resx) > 0) {

            $hasilnya['jnsprmi']    = $resx[0]['jenisbasis'];

            switch ($hasilnya['jnsprmi']) {
                case 'PRESTASI':
                    # Perhitungan Premi apabila basisnya menggunakan prestasi
                    $hasilKerja = $param['brt_muatan'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['premihelp2'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }
                    break;
                case 'KMHM':
                    # Perhitungan Premi apabila basisnya menggunakan KMHM
                    $hasilKerja = $param['jlhhm'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['premihelp2'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }

                    break;
                case 'RIT':
                    # Perhitungan Premi apabila basisnya menggunakan RIT
                    $hasilKerja = $param['jmlh_rit'];

                    if ($hasilKerja > $resx[0]['basis']) {
                        $hasilnya['premihelp2'] = $hasilKerja * $resx[0]['premilebihbasis'];
                    }
                    break;
                case 'TETAP':
                    $hasilnya['premihelp2'] = $resx[0]['premilebihbasis'];
                    break;
                default:

                    # 0 kan jika gak ada basis
                    $hasilnya['premihelp2'] = $resx[0]['premilebihbasis'];
            }
        }
    }

    $nmkegnya = makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $param['jns_kerja'] . "'");
    $pattern  = "/TBS/i";
    $hasilnya['nmkegiatantbs'] = preg_match_all($pattern, $nmkegnya[$param['jns_kerja']]);

    return json_encode($hasilnya);
}

function perbandingan($a, $b, $operator)
{
    switch ($operator) {
        case '>=':
            return $a >= $b;
        case '<=':
            return $a <= $b;
        case '>':
            return $a > $b;
        case '<':
            return $a < $b;
        case '==':
            return $a == $b;
        default:
            return '';
    }
}
