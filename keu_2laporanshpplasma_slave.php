<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');

use Dompdf\Dompdf;


# INSIALISASI
$stream = '';
$entitas = '';
$rupiahplasma = array();

$proses = checkPostGet('proses', '');
$param = $_POST;
if (count($param) == 0) {
    $param = $_GET;
}

#==============#
# MAKE OPTION
#==============#
$nmkegiatan = makeOption($dbname, "setup_kegiatan", "kodekegiatan,namakegiatan");
$nmakun = makeOption($dbname, "keu_5akun", "noakun,namaakun");
$nikkaryawan = makeOption($dbname, "datakaryawan", "karyawanid,nik");
$nmkaryawan = makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan");
$lokasitugaskaryawan = makeOption($dbname, "datakaryawan", "karyawanid,lokasitugas");
$nmorganisasi = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi");
$nmbarang = makeOption($dbname, "log_5masterbarang", "kodebarang,namabarang");

# Nama KUD
$kud = makeOption($dbname, "kebun_5namakud", "afdeling,kodesupplier");
$kudsupplier = makeOption($dbname, "log_5supplier", "supplierid,namasupplier");


switch ($proses) {
    case 'preview':

        # Get
        if ($param['kodeunit'] == '') {
            $arrunit = getOrgDetail(2);
        } else {
            $arrunit = "'" . $param['kodeunit'] . "'";
        }

        $sqlOrgPlasma = "SELECT kodeorganisasi, inti, tipe FROM " . $dbname . ".organisasi WHERE kodeorganisasi IN (" . $arrunit . ") AND induk='" . $param['kodept'] . "'";
        $res = fetchData($sqlOrgPlasma, "OBJECT");

        foreach ($res as $val):
            if ($val->inti == 0) {
                $unitplasma[$val->kodeorganisasi] = $val->kodeorganisasi;
            } else {
                if ($val->tipe != 'HOLDING') {
                    $unitinti[$val->kodeorganisasi] = $val->kodeorganisasi;
                }
            }
        endforeach;

        $jumlahPlasmaOrg = count($unitplasma);
        # End Get

        # Cek Filter
        $where = "";
        if ($param['kodeunit'] != '') {
            $where .= " AND kodeorg='" . $param['kodeunit'] . "'";
        } else {
            $where .= " AND kodeorg IN ('" . implode("','", $unitplasma) . "')";
        }

        $where .= " AND periode='" . $param['periode'] . "'";

        # ================================================================ #
        # GET DATA
        # ================================================================ #
        // $sql = "SELECT kodeorg as unitplasma, SUM(jumlah) as rupiahplasma FROM ".$dbname.".keu_jurnaldt_vw WHERE 5=5 AND noakun like '213%' and substr(noakun,1,5) < '21302' AND nik <> ''".$where." GROUP BY unitplasma";
        // $res = fetchData($sql,"OBJECT");

        // foreach($res as $val):
        //     # ILMU BARU
        //     // $rupiahplasma[$val->unitplasma] = [$val->rupiahplasma];
        //     # END

        //     $dataplasma[$val->unitplasma] = $val->unitplasma;
        //     $rupiahplasma[$val->unitplasma]['PPPE'] = abs($val->rupiahplasma);
        // endforeach;

        // # Hitung Plasma
        // $jumlahPlasma = count($dataplasma);

        if ($param['tipelaporan'] == '3') { # Tipe Bulanan
            $sql = "SELECT kodeorg as unitplasma, SUM(jumlah) as rupiahplasma FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND kodejurnal IN ('M0','M9') AND nik='' AND kodekegiatan <> '' AND kodeblok IN (SELECT kodeorg FROM setup_blok WHERE 5=5 AND STATUSBLOK IN ('TB','TBM')) " . $where . " GROUP BY unitplasma";
        } else if ($param['tipelaporan'] == '2') { # Tipe Bulanan & Investasi
            $sql = "SELECT kodeorg as unitplasma, SUM(jumlah) as rupiahplasma FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND kodejurnal IN ('M0','M9') AND nik='' AND kodekegiatan <> '' AND kodeblok IN (SELECT kodeorg FROM setup_blok WHERE 5=5 AND STATUSBLOK IN ('TB','TBM','TM')) " . $where . " GROUP BY unitplasma";
        } else {
            $sql = "SELECT kodeorg as unitplasma, SUM(jumlah) as rupiahplasma FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND noakun like '213%' and substr(noakun,1,5) < '21302' AND nik <> ''" . $where . " GROUP BY unitplasma";
        }

        $res = fetchData($sql, "OBJECT");

        foreach ($res as $val):
            # ILMU BARU
            // $rupiahplasma[$val->unitplasma] = [$val->rupiahplasma];
            # END

            $dataplasma[$val->unitplasma] = $val->unitplasma;
            $rupiahplasma[$val->unitplasma]['PPPE'] = abs($val->rupiahplasma);
        endforeach;

        # Hitung Plasma
        $jumlahPlasma = count($dataplasma);

        # GET RUPIAH PEMAKAIAN BKM
        if ($param['tipelaporan'] == '3') {
            $sql = "SELECT kodeorg as unitplasma, noreferensi, noakun, kodekegiatan, kodebarang, SUM(jumlah) as rupiahpemakaianplasma FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND kodejurnal IN ('INVK1') and (noreferensi LIKE '%TBM%' or noreferensi LIKE '%TB%') and kodekegiatan='' and kodebarang <> '' GROUP BY unitplasma";
        } else if ($param['tipelaporan'] == '2') {
            $sql = "SELECT kodeorg as unitplasma, noreferensi, noakun, kodekegiatan, kodebarang, SUM(jumlah) as rupiahpemakaianplasma FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND kodejurnal IN ('INVK1') and (noreferensi LIKE '%TM%') and kodekegiatan='' and kodebarang <> '' GROUP BY unitplasma";
        } else {
            $sql = "SELECT kodeorg as unitplasma, noreferensi, noakun, kodekegiatan, kodebarang, SUM(jumlah) as rupiahpemakaianplasma FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND kodejurnal IN ('INVK1') and (noreferensi LIKE '%TBM%' or noreferensi LIKE '%TM%' or noreferensi LIKE '%TB%') and kodekegiatan='' and kodebarang <> '' GROUP BY unitplasma";
        }

        $res = fetchData($sql, "OBJECT");

        foreach ($res as $val):
            $rupiahplasma[$val->unitplasma]['PPPE'] += abs($val->rupiahpemakaianplasma);
        endforeach;
        // echo $sql;
        // echo "<pre>";
        // print_r($rupiahplasma);

        # ================================================================ #
        # END GET DATA
        # ================================================================ #

        // echo "<pre>";
        // print_r($rupiahplasma);

        if ($param['tipe'] == 'excel') {
            $entitas = "border=1";
        } else {
            $entitas = "border='0' width='100%' cellpadding='2' cellspacing='1'";
        }

        $stream .= "<table class='sortable' " . $entitas . ">";

        $stream .= "<thead>";
        $stream .= "<tr class=rowheader>";
        $stream .= "<th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>";
        $stream .= "<th align=center rowspan=2>Unit</th>";
        $stream .= "<th colspan='" . $jumlahPlasmaOrg . "'>Unit Plasma</th>";
        $stream .= "</tr>";

        $stream .= "<tr>";
        foreach ($unitplasma as $up => $val):
            $stream .= "<th>[" . $up . "] <br/> " . $kudsupplier[$kud[$up]] . "</th>";
        endforeach;
        $stream .= "</tr>";
        $stream .= "</thead>";

        # CONTENT #
        $stream .= "<tbody>";
        $no = 0;
        foreach ($unitinti as $int => $valint):
            $no++;
            $stream .= "<tr class=rowcontent>";
            $stream .= "<td align=center>" . $no . "</td>";
            $stream .= "<td align=left>[" . $int . "] " . $nmorganisasi[$int] . "</td>";
            foreach ($unitplasma as $up => $val):

                if ($rupiahplasma[$up][$int] > 0) {
                    $entitasdetail = "onclick=detaildata('" . $int . "','" . $up . "','" . $param['tipelaporan'] . "'); style='cursor:pointer;'";
                    // $entitasdetail = "onclick=detailportal('".$int."','".$up."'); style='cursor:pointer;'";
                } else {
                    $entitasdetail = '';
                }

                $stream .= "<td align=right " . $entitasdetail . ">" . hidezerodecimal($rupiahplasma[$up][$int], 2) . "</td>";
            endforeach;
            $stream .= "</tr>";
        endforeach;

        # PLASMA #
        // foreach($unitplasma as $up => $valint):
        //     $no++;
        //     $stream .= "<tr class=rowcontent>";
        //         $stream .= "<td align=center>".$no."</td>";
        //         $stream .= "<td align=left>[".$up."] ".$nmorganisasi[$up]."</td>";
        //         foreach($unitplasma as $up => $val):

        //             if($rupiahplasmax[$up][$up] > 0) {
        //                 // $entitasdetail = "onclick=detaildata('".$up."','".$up."'); style='cursor:pointer;'";
        //                 $entitasdetail = "onclick=detailportal('".$up."','".$up."'); style='cursor:pointer;'";
        //             } else {
        //                 $entitasdetail = '';
        //             }

        //             $stream .= "<td align=right ".$entitasdetail.">".hidezerodecimal(@$rupiahplasmax[$up][$up],2)."</td>";
        //         endforeach;
        //     $stream .= "</tr>";
        // endforeach;

        $stream .= "<tbody>";

        $stream .= "</table>";

        echo $stream;
        break;

    case 'detailportal':

        $stream .= "<style>";
        $stream .= "table tr td { margin: 0px 10px!important; }";
        $stream .= "</style>";

        if ($param['tipe'] == 'excel') {
            $entitas = "border=1";
        } else {
            $entitas = "border='0' width='100%' cellpadding='5' cellspacing='1' style='padding: 5px 8px;'";
        }

        $stream .= "<table " . $entitas . ">";
        $stream .= "<tbody>";
        $stream .= "<tr>";
        $stream .= "<td class=dataPortal onclick=\"deleteht('" . @$bar['noinvoice'] . "');\">";
        $stream .= "<div style='/*background:#74f797;*/height:30vh;cursor:pointer;text-align:center;font-weight:800;border: 1px solid #000;'><!--<img src=images/skyblue/delete.png class=zImgBtn  title='Delete'>-->";
        $stream .= "<table width='100%' style='height:100%;/*background:#74f797;*/'>";
        $stream .= "<tbody>";
        $stream .= "<tr>";
        $stream .= "<td>Detail Per Kegiatan</td>";
        $stream .= "</tr>";
        $stream .= "<tr>";
        $stream .= "<td><img src=images/efill.png style='width:80px;height:100px;' class=zImgBtn  title='Delete'></td>";
        $stream .= "</tr>";
        $stream .= "<tr>";
        $stream .= "<td style='color:transparent;'>Detail Per Kegiatan</td>";
        $stream .= "</tr>";
        $stream .= "</tbody>";
        $stream .= "</table>";
        $stream .= "</div>";
        $stream .= "</td>";

        $stream .= "<td class=dataPortal onclick=\"deleteht('" . @$bar['noinvoice'] . "');\">";
        $stream .= "<div style='/*background:#8574f7;*/height:30vh;cursor:pointer;text-align:center;font-weight:800;border: 1px solid #000;'>";
        $stream .= "<table width='100%' style='height:100%;/*background:#8574f7;*/'>";
        $stream .= "<tbody>";
        $stream .= "<tr>";
        $stream .= "<td>Hutang Investasi</td>";
        $stream .= "</tr>";
        $stream .= "<tr>";
        $stream .= "<td><img src=images/archive.png style='width:80px;height:100px;' class=zImgBtn  title='Delete'></td>";
        $stream .= "</tr>";
        $stream .= "<tr>";
        $stream .= "<td style='color:transparent;'>Detail Per Kegiatan</td>";
        $stream .= "</tr>";
        $stream .= "</tbody>";
        $stream .= "</table>";
        $stream .= "</div>";
        $stream .= "</td>";

        $stream .= "<td class=dataPortal onclick=\"deleteht('" . @$bar['noinvoice'] . "');\">";
        $stream .= "<div style='/*background:#fa5fb7;*/height:30vh;cursor:pointer;text-align:center;font-weight:800;border: 1px solid #000;'>";
        $stream .= "<table width='100%' style='height:100%;/*background:#fa5fb7;*/'>";
        $stream .= "<tbody>";
        $stream .= "<tr>";
        $stream .= "<td>Rekap</td>";
        $stream .= "</tr>";
        $stream .= "<tr>";
        $stream .= "<td><img src=images/book_icon.gif style='width:80px;height:100px;' class=zImgBtn  title='Delete'></td>";
        $stream .= "</tr>";
        $stream .= "<tr>";
        $stream .= "<td style='color:transparent;'>Detail Per Kegiatan</td>";
        $stream .= "</tr>";
        $stream .= "</tbody>";
        $stream .= "</table>";
        $stream .= "</div>";
        $stream .= "</td>";
        $stream .= "</tr>";
        $stream .= "</tbody>";
        $stream .= "</table>";

        echo $stream;
        break;

    case 'detaildata':

        # ================================================================ #
        # GET DATA
        # ================================================================ #

        # ============================================= #
        # GET STATUS BLOK
        # ============================================= #
        $sqlStatusBlok = "SELECT * FROM " . $dbname . ".setup_blok WHERE LEFT(kodeorg,4)='" . $param['unitplasma'] . "'";
        $res = fetchData($sqlStatusBlok, "OBJECT");

        foreach ($res as $val):
            $statusblok[$val->kodeorg] = $val->statusblok;
            $tahuntanam[$val->kodeorg] = $val->tahuntanam;
        endforeach;

        $where .= " AND kodeorg='" . $param['unitplasma'] . "'";


        if ($param['tipelaporan'] == '3') { # Tipe Bulanan
            $sql = "SELECT * FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND kodejurnal IN ('M0','M9') AND nik='' AND kodekegiatan <> '' AND kodeblok IN (SELECT kodeorg FROM setup_blok WHERE 5=5 AND STATUSBLOK IN ('TB','TBM'))" . $where . " ORDER BY kodekegiatan ASC";
        } else if ($param['tipelaporan'] == '2') {
            $sql = "SELECT * FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND kodejurnal IN ('M0','M9') AND nik='' AND kodekegiatan <> '' AND kodeblok IN (SELECT kodeorg FROM setup_blok WHERE 5=5 AND STATUSBLOK IN ('TB','TBM','TM'))" . $where . " ORDER BY kodekegiatan ASC";
        } else {
            $sql = "SELECT * FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 AND noakun like '213%' and substr(noakun,1,5) < '21302' AND nik <> ''" . $where . " ORDER BY kodekegiatan ASC";
        }

        $res = fetchData($sql, "OBJECT");

        foreach ($res as $val):
            $dataplasma[$val->kodeorg][$statusblok[$val->kodeblok]][$val->kodeblok][$val->kodekegiatan][$val->noreferensi][$val->nojurnal][$val->nourut] = $val->kodekegiatan;
            $rupiahplasma[$val->kodeorg][$statusblok[$val->kodeblok]][$val->kodeblok][$val->kodekegiatan][$val->noreferensi][$val->nojurnal][$val->nourut] = abs($val->jumlah);
            $noakun[$val->kodeorg][$statusblok[$val->kodeblok]][$val->kodeblok][$val->kodekegiatan][$val->noreferensi][$val->nojurnal][$val->nourut] = $val->noakun;
            $karyawanid[$val->kodeorg][$statusblok[$val->kodeblok]][$val->kodeblok][$val->kodekegiatan][$val->noreferensi][$val->nojurnal][$val->nourut] = $val->nik;

            # Biaya Kiriman
            $biayablok[$val->kodeorg][$statusblok[$val->kodeblok]] = $val->kodeasset;
        endforeach;

        # GET RUPIAH PEMAKAIAN BKM
        if ($param['tipelaporan'] == '3') {
            $sql = "SELECT * FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 " . $where . " AND kodejurnal IN ('INVK1') and (noreferensi LIKE '%TBM%' or noreferensi LIKE '%TB%')";
        } else if ($param['tipelaporan'] == '2') {
            $sql = "SELECT * FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 " . $where . " AND kodejurnal IN ('INVK1') and (noreferensi LIKE '%TM%')";
        } else {
            $sql = "SELECT * FROM " . $dbname . ".keu_jurnaldt_vw WHERE 5=5 " . $where . " AND kodejurnal IN ('INVK1') and (noreferensi LIKE '%TBM%' or noreferensi LIKE '%TM%' or noreferensi LIKE '%TB%')";
        }
        $res = fetchData($sql, "OBJECT");

        foreach ($res as $val):

            // if($val->debet > 0) {
            //     $datapemakaianplasma[$val->kodeorg]['PEMAKAIAN BARANG']['DEBET'] = $val->kodebarang;
            //     $rupiahpemakaianplasmax[$val->kodeorg]['PEMAKAIAN BARANG']['DEBET'] = $val->jumlah;
            // }

            if ($val->kredit > 0) {
                $datapemakaianplasma[$val->kodeorg]['PEMAKAIAN BARANG'][$val->kodebarang][$val->nourut][$val->noreferensi]['KREDIT'] = $val->kodekegiatan;
                $rupiahpemakaianplasmax[$val->kodeorg]['PEMAKAIAN BARANG'][$val->kodebarang][$val->nourut][$val->noreferensi]['KREDIT'] += abs($val->jumlah);
                $noakunpemakaian[$val->kodeorg]['PEMAKAIAN BARANG'][$val->kodebarang][$val->nourut][$val->noreferensi]['KREDIT'] = $val->noakun;
                $norefpemakaian[$val->kodeorg]['PEMAKAIAN BARANG'][$val->kodebarang][$val->nourut][$val->noreferensi]['KREDIT'] = $val->noreferensi;
                $kdbrgpemakaian[$val->kodeorg]['PEMAKAIAN BARANG'][$val->kodebarang][$val->nourut][$val->noreferensi]['KREDIT'] = $val->kodebarang;
            }


        endforeach;

        // echo $sql;
        // echo "<pre>";
        // print_r($rupiahpemakaianplasmax);

        // echo "<pre>";
        // print_r($rupiahplasma['BKPE']['TM']);

        # Sort Buat Array Kosong
        # Array Kosong adalah kiriman Biaya Plasma A Hutang ke Plasma B
        ksort($dataplasma[$param['unitplasma']]);

        # Hitung Plasma
        $jumlahPlasma = count($dataplasma);

        # ================================================================ #
        # END GET DATA
        # ================================================================ #

        if ($param['tipe'] == 'excel') {
            $entitas = "border=1";
        } else {
            $entitas = "border='0' width='100%' cellpadding='5' cellspacing='1'";
        }

        $stream .= "<table class='sortable' " . $entitas . ">";
        $stream .= "<thead>";
        $stream .= "<tr class=rowheader>";
        $stream .= "<th>" . $_SESSION['lang']['nourut'] . "</th>";
        $stream .= "<th>" . $_SESSION['lang']['noakun'] . "</th>";
        $stream .= "<th>" . $_SESSION['lang']['kodekegiatan'] . "</th>";
        $stream .= "<th>" . $_SESSION['lang']['namabarang'] . "</th>";
        $stream .= "<th>Rupiah</th>";
        $stream .= "<th>" . $_SESSION['lang']['namakaryawan'] . "</th>";
        $stream .= "<th>" . $_SESSION['lang']['lokasitugas'] . " Karyawan</th>";
        $stream .= "<th>Noreferensi</th>";
        $stream .= "<th>Kode Blok</th>";
        $stream .= "<th>" . $_SESSION['lang']['tahuntanam'] . "</th>";
        $stream .= "</tr>";
        $stream .= "</thead>";

        # CONTENT #
        $stream .= "<tbody>";
        $nodetail = 0;
        foreach ($dataplasma as $up => $val):
            foreach ($val as $stblok => $valk):
                if ($stblok == '') {
                    $stream .= "<tr class=rowcontent style='background-color:#66fab8!important;'>";
                    $stream .= "<td align=left colspan=10><b>BIAYA DARI " . substr($biayablok[$up][$stblok], 0, 4) . "</b></td>";
                    $stream .= "</tr>";
                } else {
                    $stream .= "<tr class=rowcontent style='background-color:#66fab8!important;'>";
                    $stream .= "<td align=center colspan=10><b>" . $stblok . "</b></td>";
                    $stream .= "</tr>";
                }
                foreach ($valk as $blok => $valnb):
                    foreach ($valnb as $kdkg => $valn):
                        foreach ($valn as $noref => $valb):
                            foreach ($valb as $nojur => $valno):
                                foreach ($valno as $nourut => $valnor):
                                    $nodetail++;
                                    $stream .= "<tr class=rowcontent>";
                                    $stream .= "<td align=center>" . $nodetail . "</td>";
                                    $stream .= "<td align=left>" . $noakun[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut] . " - " . $nmakun[$noakun[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut]] . "</td>";
                                    $stream .= "<td align=left>" . $kdkg . " - " . $nmkegiatan[$kdkg] . "</td>";
                                    $stream .= "<td align=left></td>";
                                    $stream .= "<td align=right>" . hidezerodecimal($rupiahplasma[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut], 2) . "</td>";
                                    $stream .= "<td align=left>" . ($nikkaryawan[$karyawanid[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut]] != '' ? "[" . $nikkaryawan[$karyawanid[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut]] . "]" : '') . " " . $nmkaryawan[$karyawanid[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut]] . "</td>";
                                    $stream .= "<td align=left>" . ($lokasitugaskaryawan[$karyawanid[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut]] != '' ? "[" . $lokasitugaskaryawan[$karyawanid[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut]] . "] " : '') . " " . $nmorganisasi[$lokasitugaskaryawan[$karyawanid[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut]]] . "</td>";
                                    $stream .= "<td align=center>" . $noref . "</td>";
                                    $stream .= "<td align=center>" . $blok . "</td>";
                                    $stream .= "<td align=center>" . $tahuntanam[$blok] . "</td>";
                                    $stream .= "</tr>";


                                    # SUBTOTAL PER STATUS BLOK
                                    $subtotalstblok[$stblok] += $rupiahplasma[$up][$stblok][$blok][$kdkg][$noref][$nojur][$nourut];
                                endforeach;
                            endforeach;
                        endforeach;
                    endforeach;
                endforeach;
                $stream .= "<tr class=rowcontent style='background-color:#66fab8!important;'>";
                $stream .= "<td align=center colspan=4><b>SUBTOTAL " . $stblok . "</b></td>";
                $stream .= "<td align=right><b>" . hidezerodecimal($subtotalstblok[$stblok], 2) . "</b></td>";
                $stream .= "<td align=center></td>";
                $stream .= "<td align=center></td>";
                $stream .= "<td align=center></td>";
                $stream .= "<td align=center></td>";
                $stream .= "<td align=center></td>";
                $stream .= "</tr>";

                $grandtotal += $subtotalstblok[$stblok];
            endforeach;
        endforeach;


        #================================#
        # PEMAKAIAN
        #================================#
        $nodetail = 0;
        foreach ($datapemakaianplasma as $up => $val) {
            foreach ($val as $head => $valh) {
                $stream .= "<tr class=rowcontent style='background-color:#4287f5!important;'>";
                $stream .= "<td align=center colspan=10><b>" . $head . "</b></td>";
                $stream .= "</tr>";

                foreach ($valh as $kdbrg => $valkd) {
                    foreach ($valkd as $nourutkd => $valnokd) {
                        foreach ($valnokd as $noref => $valnoref) {
                            foreach ($valnoref as $posisi => $valx) {
                                $nodetail++;
                                $stream .= "<tr class=rowcontent>";
                                $stream .= "<td align=center>" . $nodetail . "</td>";
                                $stream .= "<td align=left>" . $noakunpemakaian[$up][$head][$kdbrg][$nourutkd][$noref][$posisi] . " - " . $nmakun[$noakunpemakaian[$up][$head][$kdbrg][$nourutkd][$noref][$posisi]] . "</td>";
                                $stream .= "<td align=left>" . $kdkg . " - " . $nmkegiatan[$kdkg] . "</td>";
                                $stream .= "<td align=left>" . $kdbrgpemakaian[$up][$head][$kdbrg][$nourutkd][$noref][$posisi] . " - " . $nmbarang[$kdbrgpemakaian[$up][$head][$kdbrg][$nourutkd][$noref][$posisi]] . "</td>";
                                $stream .= "<td align=right>" . hidezerodecimal($rupiahpemakaianplasmax[$up][$head][$kdbrg][$nourutkd][$noref][$posisi], 2) . "</td>";
                                $stream .= "<td align=center></td>";
                                $stream .= "<td align=center></td>";
                                $stream .= "<td align=center>" . $norefpemakaian[$up][$head][$kdbrg][$nourutkd][$noref][$posisi] . "</td>";
                                $stream .= "<td align=center></td>";
                                $stream .= "</tr>";

                                # SUBTOTAL
                                $subtotalstblok[$head] += $rupiahpemakaianplasmax[$up][$head][$kdbrg][$nourutkd][$noref][$posisi];
                            }
                        }
                    }
                }
            }
            $stream .= "<tr class=rowcontent style='background-color:#4287f5!important;'>";
            $stream .= "<td align=center colspan=4><b>SUBTOTAL " . $head . "</b></td>";
            $stream .= "<td align=right><b>" . hidezerodecimal($subtotalstblok[$head], 2) . "</b></td>";
            $stream .= "<td align=center></td>";
            $stream .= "<td align=center></td>";
            $stream .= "<td align=center></td>";
            $stream .= "<td align=center></td>";
            $stream .= "<td align=center></td>";
            $stream .= "</tr>";

            $grandtotal += $subtotalstblok[$head];
        }


        #==================#
        # GRAND TOTAL
        #==================#
        $stream .= "<tr class=rowcontent style='background-color:#66fab8!important;'>";
        $stream .= "<td align=center colspan=4><b>GRANDTOTAL</b></td>";
        $stream .= "<td align=right><b>" . hidezerodecimal($grandtotal, 2) . "</b></td>";
        $stream .= "<td align=center></td>";
        $stream .= "<td align=center></td>";
        $stream .= "<td align=center></td>";
        $stream .= "<td align=center></td>";
        $stream .= "</tr>";


        $stream .= "<tbody>";
        $stream .= "</table>";

        echo $stream;
        break;
}
