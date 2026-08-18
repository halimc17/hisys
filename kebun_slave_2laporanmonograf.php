<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';
require_once('lib/fpdf.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$method=checkPostGet('method','');
$pt=checkPostGet('pt','');
$periode=checkPostGet('periode','');
$idKebun=checkPostGet('idKebun','');
$afdeling=checkPostGet('afdeling','');
$idlaporan=checkPostGet('idlaporan','');
$tipelaporan=checkPostGet('tipelaporan','');
$jenis=checkPostGet('jenis','');

switch ($method) {
    case 'getUnitKebun':
        $optKebun="<option value=''>".$_SESSION['lang']['all']."</option>";
        $whrpt = "";
        if ($pt != "") {
            $whrpt = " and induk='".$pt."'";
        }
        $sKebun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' ".$whrpt." and kodeorganisasi in (".getOrgDetail(24).")";
        $qKebun=$owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
        $qKebun->setFetchMode(PDO::FETCH_ASSOC);
        while($rKebun=$qKebun->fetch()){
            $optKebun.="<option value='".$rKebun['kodeorganisasi']."'>".$rKebun['kodeorganisasi']." - ".$rKebun['namaorganisasi']."</option>";
        }
        
        echo $optKebun;
    break;

    case 'getDivisiKebun':
        $optAfdeling="<option value=''>".$_SESSION['lang']['all']."</option>";
        $whrpt = "";
        if ($idKebun != "") {
            $whrpt = " and induk='".$idKebun."'";
        }
        $sKebun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe IN ('AFDELING','BIBITAN') and length(kodeorganisasi)=6 ".$whrpt."
        and kodeorganisasi IN (".getOrgDetail(20).")";
        $qKebun=$owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
        $qKebun->setFetchMode(PDO::FETCH_ASSOC);
        while($rKebun=$qKebun->fetch()){
            $optAfdeling.="<option value='".$rKebun['kodeorganisasi']."'>".$rKebun['kodeorganisasi']." - ".$rKebun['namaorganisasi']."</option>";
        }
        
        echo $optAfdeling;
    break;

    case 'preview':
        if ($pt == '' && $idKebun=='' && $afdeling == '') {
            exit("Warning: Perusahaan Masih Kosong !");
        }
        if ($idlaporan == '') {
            exit("Warning: Nama Laporan Masih Kosong !");
        }
        if ($periode == '') {
            exit("Warning: Periode Masih Kosong !");
        }

        $arrStBlok = array();
        $dataBlok=$dataKegiatan=array();
        $dataHsl=$dataPres=$dataGet=array();
        $total=$totalgetrealpokok=$totaljmlmtrx=array();

        $arrNamaLaporan = makeOption($dbname,"kebun_5getpokokreport","idlaporan,namalaporan");
        $arrTipeLaporan = array('' => 'Seluruh Jenis', 'bkm' => 'BKM Rawat', 'spk' => 'Borongan SPK');

        $border="";
        $styleth="";
        if ($jenis == 'html') {
            $border = "border=0";  
            $styleth="style='position:sticky;top:0;z-index:1;'";
        } else {
            $border = "border=1";  
        }

        // Ambil Kegiatan dan ispokok
        $sgtp = "SELECT a.idlaporan,a.namalaporan, b.kodekegiatan, c.satuan, c.kelompok, b.ispokok 
        FROM $dbname.`kebun_5getpokokreport` a
        JOIN $dbname.`kebun_5getpokokreport_dt` b ON a.`idlaporan` = b.`idlaporan`
        JOIN $dbname.`setup_kegiatan` c ON b.kodekegiatan = c.kodekegiatan
        WHERE a.`idlaporan`='".$idlaporan."' ORDER BY ispokok DESC";
        $rgtp = fetchData($sgtp);
        $jmldtpkk = count($rgtp);
        foreach ($rgtp as $vltp) {
            $listKegiatan[$vltp['kelompok']][$vltp['satuan']] = $vltp['satuan'];
        }

        // Ambil Kelompok Status Blok
        $sKlmpk = "SELECT c.kelompok
        FROM $dbname.`kebun_5getpokokreport_dt` b
        JOIN $dbname.`setup_kegiatan` c ON b.kodekegiatan = c.kodekegiatan
        WHERE b.`idlaporan`='".$idlaporan."' GROUP BY c.kelompok, BINARY c.satuan;";
        $rKlmpk = fetchData($sKlmpk);
        foreach ($rKlmpk as $vlkp) {
            $arrStBlok[$vlkp['kelompok']] = $vlkp['kelompok'];
        }

        // Ambil Data Kegiatan Yang Disetup kebun_5getpokokreport_dt
        $sKgtn = "SELECT a.* FROM $dbname.kebun_5getpokokreport_dt a
        JOIN $dbname.setup_kegiatan b ON a.kodekegiatan = b.kodekegiatan
        WHERE b.kelompok IN ('".implode("','",$arrStBlok)."') and a.idlaporan='".$idlaporan."'";
        $rKgtn = fetchData($sKgtn);
        foreach ($rKgtn as $kgtn) {
            $lstkgtn[$kgtn['kodekegiatan']] = $kgtn['kodekegiatan'];
        }

        // Ambil Kelompok Status Blok
        $counterdt=0;
        $sKlmpk = "SELECT c.kelompok, c.satuan, b.ispokok
        FROM $dbname.`kebun_5getpokokreport_dt` b
        JOIN $dbname.`setup_kegiatan` c ON b.kodekegiatan = c.kodekegiatan
        WHERE b.`idlaporan`='".$idlaporan."'
        GROUP BY c.kelompok, BINARY c.satuan;";
        $rKlmpk = fetchData($sKlmpk);
        foreach ($rKlmpk as $vlkp) {
            $dtKlmpk[$vlkp['kelompok']] = $vlkp['satuan'];
            if ($vlkp['satuan'] != 'POKOK') {
                $ispokok[$vlkp['kelompok']][$vlkp['satuan']] = $vlkp['ispokok'];
            } else {
                $ispokok[$vlkp['kelompok']][$vlkp['satuan']] = 0;
            }
            // $jmldtKlmpk[$vlkp['kelompok']] = count($dtKlmpk);
            $jmldtKlmpk[$vlkp['kelompok']] += 1;
            $counterdt +=1;
        }

        // Ambil data kegiatan di setup Laporan Kegiatan Per Pokok
        $sKgp = "SELECT a.`kodekegiatan`, b.`kelompok`, a.`ispokok` 
        FROM $dbname.`kebun_5getpokokreport_dt` a 
        JOIN $dbname.`setup_kegiatan` b on a.kodekegiatan = b.kodekegiatan
        WHERE a.`idlaporan`='".$idlaporan."'";
        $rKgp = fetchData($sKgp);
        foreach ($rKgp as $kgp) {
            $kegpkk[$kgp['kelompok']][$kgp['kodekegiatan']] = $kgp['kodekegiatan'];
            $jmlkegpkk[$kgp['kelompok']] = count($kegpkk[$kgp['kelompok']]);
        }

        $whrMt = "";
        $whrBlkx = "";
        if ($pt != "" && $idKebun == "") {
            $whrMt = " AND LEFT(a.kodeorg, 6) IN (
                SELECT kodeorganisasi
                from $dbname.organisasi
                where induk in (
                    select kodeorganisasi
                    from $dbname.organisasi
                    where induk = '".$pt."'
                )
            ) AND LEFT(a.kodeorg,6) IN (".getOrgDetail(20).")";
            
            $whrBlkx = " AND LEFT(indukblok, 6) IN (
                    SELECT kodeorganisasi
                    from $dbname.organisasi
                    where induk in (
                        select kodeorganisasi
                        from $dbname.organisasi
                        where induk='".$pt."'
                    )
                ) AND LEFT(indukblok,6) IN (".getOrgDetail(20).")";
        }
        
        if ($idKebun != "" && $afdeling == '') {
            $whrMt = " AND a.kodeorg like '".$idKebun."%' AND LEFT(a.kodeorg,6) IN (".getOrgDetail(20).")";
            $whrBlkx = " and indukblok like '".$idKebun."%' and LEFT(indukblok,6) IN (".getOrgDetail(20).")";
        }
        
        if ($afdeling != "") {
            $whrMt = " AND a.kodeorg like '".$afdeling."%'";
            $whrBlkx = " and indukblok like '".$afdeling."%'";
        }
        
        $jmlmtrlx = $lstbrgx = $lstmtrlx = $jmldtmtrx = array();
        $countermtr = 0;

        // Jika Tipe Laporan Seluruhnya atau BKM Rawat, Maka Munculkan Preview Laporan Materialnya
        if ($tipelaporan == '' || $tipelaporan == 'bkm') {
            // Ambil Data Material Yang Dipakai di periode saat ini berdasarkan kegiatan dan bulan
            $sMtrl = "SELECT a.notransaksi,a.kodeorg,a.kodebarang,a.kwantitas,a.kwantitasha,
            a.kodekegiatan,c.satuan,a.tanggal,b.tipetransaksi 
            FROM $dbname.kebun_pakai_material_vw a
            JOIN $dbname.kebun_aktifitas b ON a.notransaksi = b.notransaksi
            JOIN $dbname.setup_kegiatan c on a.kodekegiatan = c.kodekegiatan
            WHERE b.tipetransaksi IN ('".implode("','",$arrStBlok)."') 
            AND a.kodekegiatan IN ('".implode("','",$lstkgtn)."')
            AND a.tanggal like '".$periode."%' ".$whrMt."";
            $rMtrl = fetchData($sMtrl);
            foreach ($rMtrl as $mtrl) {
                // $jmlmtrlx[substr($mtrl['kodeorg'],0,6)][$mtrl['tipetransaksi']][$mtrl['satuan']][substr($mtrl['tanggal'],5,2)][$mtrl['kodeorg']][$mtrl['kodebarang']] += $mtrl['kwantitas'];
                $jmlmtrlx[substr($mtrl['kodeorg'],0,6)][$mtrl['tipetransaksi']][$mtrl['kodeorg']][$mtrl['satuan']][substr($mtrl['tanggal'],5,2)][$mtrl['kodebarang']] += $mtrl['kwantitas'];
            }
    
            // Ambil Kode Barang (Material) di periode saat ini berdasarkan kegiatan dan bulan
            $sBrgx = "SELECT a.kodebarang FROM $dbname.kebun_pakai_material_vw a
            JOIN $dbname.kebun_aktifitas b ON a.notransaksi = b.notransaksi
            WHERE b.tipetransaksi IN ('".implode("','",$arrStBlok)."') 
            AND a.kodekegiatan IN ('".implode("','",$lstkgtn)."')
            AND a.tanggal like '".$periode."%' ".$whrMt."
            group by kodebarang";
            $rBrgx = fetchData($sBrgx);
            foreach ($rBrgx as $brgx) {
                $lstbrgx[$brgx['kodebarang']] = $brgx['kodebarang'];
            }
    
            // Get Jumlah Material berdasarkan setup_kegiatan
            $sNorm = "SELECT b.satuan,b.kelompok,a.kodebarang 
            FROM $dbname.setup_kegiatannorma a
            JOIN $dbname.setup_kegiatan b ON a.kodekegiatan = b.kodekegiatan
            WHERE b.kelompok IN ('".implode("','",$arrStBlok)."') AND a.kodebarang IN ('".implode("','",$lstbrgx)."')
            AND a.kodekegiatan IN ('".implode("','",$lstkgtn)."')
            GROUP BY b.kelompok,BINARY b.satuan,a.kodebarang";
            $rNorm = fetchData($sNorm);
            foreach ($rNorm as $norm) {
                $lstmtrlx[$norm['kelompok']][$norm['satuan']][$norm['kodebarang']] = $norm['kodebarang'];
                $jmldtmtrx[$norm['kelompok']] += 1;
                $countermtr += 1;
            }
        }

        // Get Jumlah pokok dan luas planted area
        $jmlsetpkk=$jmlsetluas= array();
        $sBlkx = "SELECT indukblok,statusblok,SUM(jumlahpokok) as jmlpokok,SUM(luasareaproduktif) AS luasareaproduktif
            FROM $dbname.`setup_blok` WHERE 1=1 ".$whrBlkx."
            group by indukblok,statusblok";
        $rBlkx = fetchData($sBlkx);
        foreach ($rBlkx as $val) {
            $jmlsetpkk[$val['statusblok']][$val['indukblok']] = $val['jmlpokok'];
            $jmlsetluas[$val['statusblok']][$val['indukblok']] = $val['luasareaproduktif'];
        }


        $tab="";

        if ($jenis == 'excel') {
            if ($idKebun == '') {
                $namaunit = $pt;
            } elseif ($afdeling == '' && $idKebun == '') {
                $namaunit = $pt;
            } elseif ($afdeling == '' && $idKebun != '') {
                $namaunit = $idKebun;
            } else {
                $namaunit = $afdeling;
            }

            $tab .= "<table ".$border." class=sortable cellpadding=5 cellspacing=1 style='width:100%'>";
                $tab .= "<tr>";
                    $tab .= "<td colspan='6' style='font-weight:bold;font-size:14px;'>".$namaunit." - ".getNamaOrg($namaunit)."</td>";
                $tab .= "</tr>";
                $tab .= "<tr>";
                    $tab .= "<td colspan='6' style='font-weight:bold;font-size:14px;'>".$arrNamaLaporan[$idlaporan]." - ".$arrTipeLaporan[$tipelaporan]."</td>";
                $tab .= "</tr>";
            $tab .= "</table>";

            $tab.="<table></table><table></table>";
        }

        $tab.="<table ".$border." class=sortable cellpadding=5 cellspacing=1 style='width:100%'>";
            $tab .= "<thead>";
                $tab .= "<tr class=rowheader>";
                $tab .= "<th ".$styleth." rowspan=3>".$_SESSION['lang']['nourut']."</th>";
                $tab .= "<th ".$styleth." rowspan=3>".$_SESSION['lang']['divisi']."</th>";
                $tab .= "<th ".$styleth." rowspan=3>".$_SESSION['lang']['blok']."</th>";
                // Get Status Blok for to cover the value of the planted area and the number of trees
                foreach ($arrStBlok as $sb) {
                    $tab .= "<th ".$styleth." rowspan=2 colspan=3>".$sb."</th>";
                }

                ##cek ispokok
                $countispokok = $countsb = 0;
                $countispokok2 = array();
                foreach ($arrStBlok as $sb) {
                    $countsb += 1;
                    foreach ($listKegiatan[$sb] as $lkgtn) {
                        if ($ispokok[$sb][$lkgtn] != 0) {
                            $countispokok += 1;
                            $countispokok2[$sb] += 1;
                        }
                    }
                }

                // Loop untuk menampilkan header per bulan
                for ($i = 1; $i <= 12; $i++) {
                    // $tab .= "<th ".$styleth." colspan=".((count($arrStBlok)*2)+2).">".numToMonth($i, 'I', 'long')."</th>";
                    $tab .= "<th ".$styleth." colspan=".(($counterdt+$countermtr)+$countispokok).">".numToMonth($i, 'I', 'long')."</th>";
                }
                $tab .= "</tr>";
                
                $tab .= "<tr class=rowheader>";
                for ($i = 1; $i <= 12; $i++) {
                    // Loop untuk menampilkan header per tipe blok (TM, TBM, TB, dan Total)
                    foreach ($arrStBlok as $sb) {
                        $tab .= "<th ".$styleth." colspan=".(($jmldtKlmpk[$sb]+$jmldtmtrx[$sb])+$countispokok2[$sb]).">".$sb."</th>";
                    }
                }
                $tab .= "</tr>";
                $tab .= "<tr class=rowheader>";
                 // Get Luas planted area and number of trees from setup_blok
                foreach ($arrStBlok as $sb) {
                    $tab .= "<th ".$styleth.">".$_SESSION['lang']['luasareaproduktif']."</th>";
                    $tab .= "<th ".$styleth.">".$_SESSION['lang']['jumlahpokok']."<br>Di Master Blok</th>";
                    $tab .= "<th ".$styleth.">SPH</th>";
                }
                for ($i = 1; $i <= 12; $i++) {
                    // Loop untuk menampilkan header per tipe blok (TM, TBM, TB, dan Total)
                    foreach ($arrStBlok as $sb) {
                        // $tab .= "<th ".$styleth.">PKK</th>";
                        foreach ($listKegiatan[$sb] as $lkgtn) {
                            $tab .= "<th ".$styleth.">Satuan Hasil Kerja<br>(".$lkgtn.")</th>";
                            if ($ispokok[$sb][$lkgtn] != 0) {
                                $tab .= "<th ".$styleth.">POKOK</th>";
                            }
                            foreach ($lstmtrlx[$sb][$lkgtn] as $mtrx) {
                                $tab .= "<th ".$styleth.">".getNamaBrg($mtrx)."<br>(".getNamaBrg($mtrx,'satuan').")</th>";
                            }
                        }
                    }
                }   
                $tab .= "</tr>";
            $tab .= "</thead>";
        
            $where="";
            $where2="";
            $wherespk="";
            // Filter data berdasarkan PT, kebun atau afdeling
            if ($pt != '' && $idKebun == '') {
                $where2 = " AND LEFT(a.kodeorg,6) IN (
                    SELECT kodeorganisasi
                    from ".$dbname.".organisasi
                    where induk in (
                        select kodeorganisasi
                        from ".$dbname.".organisasi
                        where induk = '".$pt."'
                    )
                ) AND LEFT(a.kodeorg,6) IN (".getOrgDetail(20).")";
                
                $where = " AND LEFT(indukblok, 6) IN (
                    SELECT kodeorganisasi
                    from $dbname.organisasi
                    where induk in (
                        select kodeorganisasi
                        from $dbname.organisasi
                        where induk='".$pt."'
                    )
                ) AND LEFT(indukblok,6) IN (".getOrgDetail(20).")";
                
                $wherespk = " AND LEFT(a.kodeblok, 6) IN (
                    SELECT kodeorganisasi
                    from $dbname.organisasi
                    where induk in (
                        select kodeorganisasi
                        from $dbname.organisasi
                        where induk='".$pt."'
                    )
                ) AND LEFT(a.kodeblok,6) IN (".getOrgDetail(20).")";
            }  
            if ($idKebun != '' && $afdeling == '') {
                $wherespk = " and a.kodeblok like '".$idKebun."%' and LEFT(a.kodeblok,6) IN (".getOrgDetail(20).")";
                $where2 = " and a.kodeorg like '".$idKebun."%' and LEFT(a.kodeorg,6) IN (".getOrgDetail(20).")";
                $where = " and indukblok like '".$idKebun."%' and LEFT(indukblok,6) IN (".getOrgDetail(20).")";
            }
            if($afdeling != '') {
                $wherespk = " and a.kodeblok like '".$afdeling."%'";
                $where2 = " and a.kodeorg like '".$afdeling."%'";
                $where = " and indukblok like '".$afdeling."%'";
            }
        
            // Query untuk mengambil data perawatan
            // Jika Tipe laporan seluruhanya atau bkm rawat maka tampilkan data prestasinya
            if ($tipelaporan == '' || $tipelaporan == 'bkm') {
                $sPres = "SELECT b.satuan, LEFT(a.kodeorg, 6) as divisi, a.kodeorg, tipetransaksi, periode, hasilkerja 
                    FROM $dbname.kebun_perawatan_vw a
                    JOIN $dbname.setup_kegiatan b on a.kodekegiatan = b.kodekegiatan
                    WHERE a.tipetransaksi IN ('".implode("','",$arrStBlok)."')
                    AND a.kodekegiatan IN ('".implode("','",$lstkgtn)."')
                    AND periode like '".$periode."%' ".$where2."
                    ORDER BY b.satuan ASC,LEFT(a.kodeorg,6) ASC,a.kodeorg ASC, a.periode ASC";
                $rPres = fetchData($sPres);
            
                // Memproses hasil query
                foreach ($rPres as $val) {
                    $divisi = $val['divisi'];
                    $tipetransaksi = $val['tipetransaksi'];
                    $satuankegiatan = $val['satuan'];
                    $bulan = substr($val['periode'], 5, 2);
                    $kodeorg = $val['kodeorg'];
                    $hasilkerja = $val['hasilkerja'];
                
                    // Simpan data hasil kerja berdasarkan divisi, tipe transaksi, kegiatan, dan bulan
                    $dataPres[$divisi][$tipetransaksi][$kodeorg][$satuankegiatan][$bulan] += $hasilkerja;
                }
            }
            // Jika Tipe laporan seluruh atau borongan spk maka tampilkan data prestasinya
            if ($tipelaporan == '' || $tipelaporan == 'spk') {
                $sPres = "SELECT b.satuan, LEFT(a.kodeblok, 6) as divisi, a.kodeblok AS kodeorg, b.kelompok as tipetransaksi,
                    LEFT(a.tanggal,7) AS periode, hasilkerjarealisasi AS hasilkerja
                    FROM $dbname.log_baspkdt a
                    JOIN $dbname.setup_kegiatan b ON a.kodekegiatan = b.kodekegiatan
                    WHERE b.kelompok IN ('".implode("','",$arrStBlok)."')
                    AND a.kodekegiatan IN ('".implode("','",$lstkgtn)."')
                    AND a.tanggal like '".$periode."%' ".$wherespk."
                    ORDER BY b.satuan ASC,LEFT(a.kodeblok,6) ASC,a.kodeblok ASC, LEFT(a.tanggal,7) ASC";
                $rPres = fetchData($sPres);
            
                // Memproses hasil query
                foreach ($rPres as $val) {
                    $divisi = $val['divisi'];
                    $tipetransaksi = $val['tipetransaksi'];
                    $satuankegiatan = $val['satuan'];
                    $bulan = substr($val['periode'], 5, 2);
                    $kodeorg = $val['kodeorg'];
                    $hasilkerja = $val['hasilkerja'];
                
                    // Simpan data hasil kerja berdasarkan divisi, tipe transaksi, kegiatan, dan bulan
                    $dataPres[$divisi][$tipetransaksi][$kodeorg][$satuankegiatan][$bulan] += $hasilkerja;
                }
            }


            // Query setup_blok_tahunan
            $sBlok = "SELECT indukblok, SUM(jumlahpokok) as jmlpokok,SUM(luasareaproduktif) AS luasareaproduktif,statusblok,tahun
            FROM $dbname.`setup_blok_tahunan` WHERE 1=1 ".$where." and tahun like '".$periode."%'
            group by indukblok,statusblok,tahun";
            $rBlok = fetchData($sBlok);
            foreach ($rBlok as $val) {
                $jmlpokok[$val['statusblok']][$val['indukblok']][substr($val['tahun'],4,2)] = $val['jmlpokok'];
                $luasblokst[$val['statusblok']][$val['indukblok']][substr($val['tahun'],4,2)] = $val['luasareaproduktif'];
            }

            // Query setup_blok
            $sBlok = "SELECT indukblok, SUM(jumlahpokok) as jmlpokok,SUM(luasareaproduktif) AS luasareaproduktif,statusblok
            FROM $dbname.`setup_blok` WHERE 1=1 ".$where."
            group by indukblok,statusblok";
            $rBlok = fetchData($sBlok);
            foreach ($rBlok as $val) {
                $jmlpokok2[$val['statusblok']][$val['indukblok']] = $val['jmlpokok'];
                $luasblokst2[$val['statusblok']][$val['indukblok']] = $val['luasareaproduktif'];
            }

            // Get Blok Group By Indukblok
            $sBlok = "SELECT indukblok,statusblok
            FROM $dbname.`setup_blok_tahunan` WHERE 1=1 ".$where." and tahun like '".$periode."%'
            group by indukblok";
            $rBlok = fetchData($sBlok);
            foreach ($rBlok as $val) {
                $dataGet[substr($val['indukblok'],0,6)][$val['statusblok']][$val['indukblok']] = $val['indukblok'];
            }

            // echo "<pre>";
            // print_r($dataPres);
            // echo "</pre>";
            // exit();

            $totalsetpkk=$totalsetluas= array();
            $tab .= "<tbody>";
                $nourut = 1;
                if (count($dataPres) > 0) {
                    // Memproses hasil query dengan $dataGet untuk divisi, tipetransaksi, dan kodeorg
                    foreach ($dataGet as $divisi => $transaksiData) {
                        foreach ($transaksiData as $tipetransaksi => $blokData) {
                            foreach ($blokData as $blok => $kodeorg) {
                                // Baris untuk menampilkan hasil kerja per blok
                                $tab .= "<tr class=rowcontent>";
                                $tab .= "<td>".$nourut."</td>";
                                $tab .= "<td>".$divisi." (".getNamaOrg($divisi).")</td>";
                                $tab .= "<td>".$blok." (".getIndukBlok($blok).")</td>";
                                
                                foreach ($arrStBlok as $sb) {
                                    $tab .= "<td>".number_format($jmlsetluas[$sb][$kodeorg],2)."</td>";
                                    $tab .= "<td>".number_format($jmlsetpkk[$sb][$kodeorg],2)."</td>";
                                    
                                    $sph = fixnan($jmlsetpkk[$sb][$kodeorg] / $jmlsetluas[$sb][$kodeorg]);

                                    $tab .= "<td>".number_format($sph,0)."</td>";

                                    $totalsetpkk[$sb]   +=  $jmlsetpkk[$sb][$kodeorg];
                                    $totalsetluas[$sb]  +=  $jmlsetluas[$sb][$kodeorg];
                                }

                                // Looping untuk setiap bulan dan status blok
                                for ($i = 1; $i <= 12; $i++) {
                                    $bln = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    foreach ($arrStBlok as $sb) {
                                        foreach ($listKegiatan[$sb] as $lkgtn) {
                                            // Ambil hasil kerja dari $dataPres dengan urutan yang baru
                                            $hasil = isset($dataPres[$divisi][$sb][$kodeorg][$lkgtn][$bln]) ? $dataPres[$divisi][$sb][$kodeorg][$lkgtn][$bln] : 0;
                                            $tab .= "<td>".number_format($hasil, 2)."</td>";
                                            $total[$sb][$bln] += $hasil;
                                            
                                            // Jika ada perhitungan real pokok
                                            if ($ispokok[$sb][$lkgtn] != 0) {
                                                // Ambil data pokok dari setup_blok_tahunan atau setup_blok sesuai kondisinya
                                                if (!empty($jmlpokok[$sb][$blok][$bln])) {
                                                    $jmlsphpokok[$sb][$blok][$bln] = fixnan($jmlpokok[$sb][$blok][$bln] / $luasblokst[$sb][$blok][$bln]);
                                                    $getrealpokok[$sb][$blok][$bln] = ($hasil * $jmlsphpokok[$sb][$blok][$bln]);
                                                    $tab .= "<td>".number_format($getrealpokok[$sb][$blok][$bln], 2)."</td>";
                                                    $totalgetrealpokok[$sb][$bln] += $getrealpokok[$sb][$blok][$bln];
                                                } else {
                                                    $jmlsphpokok2[$sb][$blok][$bln] = fixnan($jmlpokok2[$sb][$blok] / $luasblokst2[$sb][$blok]);
                                                    $getrealpokok2[$sb][$blok][$bln] = ($hasil * $jmlsphpokok2[$sb][$blok][$bln]);
                                                    $tab .= "<td>".number_format($getrealpokok2[$sb][$blok][$bln], 2)."</td>";
                                                    $totalgetrealpokok[$sb][$bln] += $getrealpokok2[$sb][$blok][$bln];
                                                }
                                            }
                                            // Tambahkan data material, jika ada
                                            foreach ($lstmtrlx[$sb][$lkgtn] as $mtrx) {
                                                $tab .= "<td>".number_format($jmlmtrlx[$divisi][$sb][$blok][$lkgtn][$bln][$mtrx], 2)."</td>";
                                                $totaljmlmtrx[$sb][$lkgtn][$bln][$mtrx] += $jmlmtrlx[$divisi][$sb][$blok][$lkgtn][$bln][$mtrx];
                                            }
                                        }
                                    }
                                }
                                $tab .= "</tr>";
                                $nourut++;
                            }
                        }
                    }
                    $tab.= "<tr class=rowcontent style='text-align:center;font-weight:bold;background-color:#cad9e3;'>";
                        $tab.="<td colspan=3>".$_SESSION['lang']['total']."</td>";
                        foreach ($arrStBlok as $sb) {
                            $tab.="<td>".number_format($totalsetluas[$sb],2)."</td>";
                            $tab.="<td>".number_format($totalsetpkk[$sb],2)."</td>";
                            $tab.="<td></td>";
                        }
                        for ($i=1; $i <= 12 ; $i++) {
                            $bln = str_pad($i, 2, '0', STR_PAD_LEFT);
                            foreach ($arrStBlok as $sb => $lkgtn) {
                                foreach ($listKegiatan[$sb] as $lkgtn) {
                                    $tab.="<td>".number_format($total[$sb][$bln],2)."</td>";
                                    if ($ispokok[$sb][$lkgtn] != 0) {
                                        $tab.="<td>".number_format($totalgetrealpokok[$sb][$bln],2)."</td>";
                                    }
                                    foreach ($lstmtrlx[$sb][$lkgtn] as $mtrx) {
                                        $tab.="<td>".number_format($totaljmlmtrx[$sb][$lkgtn][$bln][$mtrx],2)."</td>";
                                    }
                                }
                            }
                        }
                    $tab.= "</tr>";
                } else {
                    $tab .= "<tr class=rowcontent>";
                    $tab.= "<td colspan='".(3+($countsb*3)+((($counterdt+$countermtr)+$countispokok)*12))."' style='text-align:center;font-weight:bold;color:red;'>".$_SESSION['lang']['errdatanotexist']."</td>";
                    $tab .= "</tr>";
                }
                $tab .= "</tbody>";
        $tab.="</table>";

        if ($jenis == 'html') {
            echo $tab;
        } else {
            $nop = "Laporan_Monograf_".$arrNamaLaporan[$idlaporan]."_".$arrTipeLaporan[$tipelaporan].".xls";
            $xls = new HtmlExcel();
            $xls->setCss($css);
            $xls->addSheet("Laporan Monograf", $tab);
            $xls->headers($nop);
            echo $xls->buildFile();
        }
    break;
}

?>