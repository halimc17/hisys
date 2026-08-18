<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$method     = checkPostGet('method', '');

$notrans    = checkPostGet('notrans', '');
$unit       = checkPostGet('unit', '');
$periode    = checkPostGet('periode', '');
$noakun     = checkPostGet('noakun', '');
$minggu     = checkPostGet('minggu', '');

$jumlahfisik = checkPostGet('jmlfisik', '');
$jumlah     = checkPostGet('jumlah', '');
$nominal    = checkPostGet('nominal', '');
$jumlah     = str_replace(",", "", $jumlah);
$nominal    = str_replace(",", "", $nominal);

$mode         = checkPostGet('mode', '');

$notranssch = checkPostGet('notranssch', '');
$unitsch    = checkPostGet('unitsch', '');
$periodesch = checkPostGet('periodesch', '');
$noakunsch  = checkPostGet('noakunsch', '');
$minggusch  = checkPostGet('minggusch', '');

$nmkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$expprd = explode('-', $periode);
$tahun  = $expprd[0];
$bulan  = $expprd[1];

switch ($method) {
    case 'getPeriode':
        $optperiode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

        if ($unit != '') {
            // Get Periode Akuntansi Yang Belum Close
            $sprd = "SELECT DISTINCT periode FROM $dbname.setup_periodeakuntansi WHERE kodeorg='" . $unit . "' AND tutupbuku='0' ORDER BY periode DESC";
            $rprd = fetchData($sprd);
            foreach ($rprd as $val) {
                $optperiode .= "<option value='" . $val['periode'] . "'>" . $val['periode'] . "</option>";
            }

            // Get Noakun
            $optakunsch = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

            $tipeorganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe')[$unit] ?? '';
            $str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbank = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND a.noakun LIKE '11101%' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . $unit . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$tipeorganisasi}' OR a.pemilik IN ('" . $unit . "')))) GROUP BY a.noakun";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                $optakunsch .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
            }
        }

        echo $optperiode . "####" . $optakunsch;
        break;

    case 'getMinggu':
        $optminggu = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

        if ($periode != '') {
            $weeks = getWeeksInPeriod($periode);

            foreach ($weeks as $week) {
                // Ambil hanya angka tanggal dari format Y-m-d
                $tglMulai   = (int) substr($week['start_date'], 8, 2);
                $tglSelesai = (int) substr($week['end_date'], 8, 2);

                $optminggu .= "<option value='" . $week['week'] . "'>"
                    . $week['week']
                    . " (Tanggal " . $tglMulai . " s/d " . $tglSelesai . ")"
                    . "</option>";
            }
        }
        echo $optminggu;
        break;

    case 'posting':
        try {
            $str = "UPDATE $dbname.keu_cashopnameht SET posting='1', postingby='" . $_SESSION['standard']['userid'] . "', postingtime='" . date('Y-m-d H:i:s') . "'
                    WHERE notransaksi='" . $notrans . "'";
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loaddata':
        $where = "";
        if ($notranssch != '') {
            $where .= " and notransaksi like '%" . $karyawansch . "%' ";
        }
        if ($unitsch != '') {
            $where .= " and unit='" . $unitsch . "' ";
        }
        if ($periodesch != '') {
            $where .= " and periode='" . $periodesch . "' ";
        }
        if ($noakunsch != '') {
            $where .= " and noakun='" . $noakunsch . "' ";
        }
        if ($minggusch != '') {
            $where .= " and mingguke='" . $minggusch . "' ";
        }

        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = floatval($page) * $limit;
        $maxdisplay = (floatval($page) * $limit);
        $sql = "select count(*) as jmlhrow from " . $dbname . ".keu_cashopnameht where 1=1 " . $where . " order by notransaksi desc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);

        $no = 0;
        $str = "SELECT * FROM " . $dbname . ".keu_cashopnameht
		where 1=1 " . $where . " 
        order by notransaksi desc limit " . $offset . "," . $limit . "";
        $tab = "";
        $no = $maxdisplay;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        if ($jlhbrs == 0) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center colspan=11>" . $_SESSION['lang']['errdatanotexist'] . "</td>";
            $tab .= "</tr>";
        } else {
            while ($bar = $res->fetch()) {
                $isi = '';
                $no += 1;
                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=center style='width:5%'>" . $no . "</td>";
                $tab .= "<td>" . $bar['notransaksi'] . "</td>";
                $tab .= "<td>" . $bar['unit'] . " - " . getNamaOrg($bar['unit']) . "</td>";
                $tab .= "<td align=center>" . $bar['periode'] . "</td>";
                $tab .= "<td align=center>" . $bar['mingguke'] . "</td>";
                $tab .= "<td align=center>" . $bar['noakun'] . " - " . getNamaAkun($bar['noakun']) . "</td>";
                $tab .= "<td>" . $nmkar[$bar['postingby']] . "</td>";
                if ($bar['posting'] == 0) {
                    $isi .= "<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                        onclick=\"edit('" . $bar['notransaksi'] . "','" . $bar['unit'] . "','" . $bar['periode'] . "','" . $bar['mingguke'] . "','" . $bar['noakun'] . "','edit')\" ></td>";
                    $isi .= "<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                        onclick=\"deleteData('" . $bar['notransaksi'] . "','" . $page . "');\" ></td>";
                    $isi .= "<td align=center><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Posting' 
                        onclick=\"posting('" . $bar['notransaksi'] . "','" . $page . "');\" ></td>";
                } else {
                    $icon = "images/icons/04/16/02.png";
                    $title = "Posted";
                    $unpost = '';

                    $isi .= "<td align=center></td><td align=center></td>";
                    $isi .= "<td align=center><img src=" . $icon . " class=zImgBtn class=zImgBtn height='30'  title='" . $title . "' " . $unpost . " ></td>";
                }
                $isi .= "<td align=center><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' 
                        onclick=\"html('" . $bar['notransaksi'] . "','" . $bar['mingguke'] . "');\" ></td>";
                $tab .= $isi;
                $tab .= "</tr>";
            }
        }

        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = createpaging($jlhbrs, $limit, $page, '11', 'loaddata', 'getPage');
        echo $tab . "####" . $footd;
        break;

    case 'insertHeader':
        try {
            $owlPDO->beginTransaction();

            $countcek = 0;
            if ($mode == 'baru') {
                // Cek Apakah Data Cash Opname sudah diproses posting
                $scek = "SELECT COUNT(*) AS jmlhrow FROM $dbname.keu_cashopnameht WHERE unit='" . $unit . "' AND periode='" . $periode . "' AND noakun='" . $noakun . "' AND mingguke='" . $minggu . "'";
                $rcek = fetchData($scek);
                $countcek = $rcek[0]['jmlhrow'];
            }

            if ($countcek > 0) {
                exit("Warning: Transaksi Baru di unit, periode, nomor akun, dan minggu ke yang terpilih sudah ada / sudah diposting !");
            }

            $prdtr = str_replace("-", "", $periode);

            // Cek Apakah Sudah ada pembentukan transaksi sebelumnya di unit dan periode yang sama
            $scek = "SELECT notransaksi FROM $dbname.keu_cashopnameht
                WHERE unit='" . $unit . "' AND periode='" . $periode . "' ORDER BY notransaksi DESC limit 1";
            $rcek = fetchData($scek);
            $tmpno = $rcek[0]['notransaksi'];

            if (count($tmpno) == 0) {
                $counter = "0001";
                $getnotrans = $prdtr . "-" . "CSP" . "-" . $unit . "-" . $counter;
            } else {
                $tmprow = explode("-", $tmpno);
                $nourut = (int)$tmprow[3];

                $counter = addZero($nourut + 1, 4);

                $getnotrans = $prdtr . "-" . "CSP" . "-" . $unit . "-" . $counter;
            }


            $arrdata = array(
                'notransaksi'   => $getnotrans,
                'unit'          => $unit,
                'periode'       => $periode,
                'noakun'        => $noakun,
                'mingguke'      => $minggu,
                'createby'      => $_SESSION['standard']['userid'],
                'createtime'    => date('Y-m-d H:i:s'),
                'updateby'      => $_SESSION['standard']['userid'],
            );

            $cols = array();
            foreach ($arrdata as $key => $row) {
                $cols[] = $key;
            }

            $inssql = insertQuery($dbname, "keu_cashopnameht", $arrdata, $cols);
            $owlPDO->exec($inssql);

            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }

        echo $getnotrans;
        break;

    case 'deleteHeader':
        try {
            $owlPDO->beginTransaction();

            $delquery = deleteQuery($dbname, "keu_cashopnameht", "notransaksi='" . $notrans . "'");
            $owlPDO->exec($delquery);

            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'detail':
        $optjmlfisik = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        // Get Jumlah Fisik
        $sjfk = "SELECT jumlahfisik FROM $dbname.keu_5jumlahfisik ORDER BY jumlahfisik DESC";
        $rjfk = fetchData($sjfk);
        foreach ($rjfk as $val) {
            $optjmlfisik .= "<option value='" . $val['jumlahfisik'] . "'>" . number_format($val['jumlahfisik'], 2) . "</option>";
        }

        OPEN_BOX();

        echo "<fieldset>";
        echo "<legend>" . $_SESSION['lang']['detail'] . "</legend>";

        echo "<table border='0' cellpadding='3' cellspacing='1' class='sortable'>";
        echo "<thead>";
        echo "<tr class='rowheader'>";
        echo "<th style='text-align:center;'>" . $_SESSION['lang']['uraian'] . "</th>";
        echo "<th style='text-align:center;' colspan='3'>Minggu Ke - " . $minggu . "</th>";
        echo "</tr>";
        echo "<tr class='rowheader'>";
        echo "<th style='text-align:center;'>" . $_SESSION['lang']['jumlah'] . " Fisik</th>";
        echo "<th style='text-align:center;'>" . $_SESSION['lang']['jumlah'] . "</th>";
        echo "<th style='text-align:center;'>Nominal</th>";
        echo "<th style='text-align:center;'>" . $_SESSION['lang']['action'] . "</th>";
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";
        echo "<tr class='rowcontent'>";
        echo "<td align='left'>
                                <select class='select2' style='width:350px;' id='jmlfisik' onchange=\"hitungNominal();\">" . $optjmlfisik . "</select>
                            </td>";
        echo "<td align='left'>
                                <input type='text' class='myinputtextnumber' style='width:350px;' id='jumlah' onkeyup=\"z.numberFormat('jumlah',2);hitungNominal();\">
                            </td>";
        echo "<td align='left'>
                                <input type='text' class='myinputtextnumber' style='width:350px;' id='nominal' value='0.00' disabled>
                            </td>";
        echo "<td align='center'>
                                <img class='zImgBtn' src='images/save.png' title='simpan detail' onclick=\"savedetail('" . $mode . "');\">
                                <input type='hidden' id='metdetail' value='insertDetail'>
                            </td>";
        echo "</tr>";
        echo "</tbody>";
        echo "</table>";
        echo "</fieldset>";

        echo "<fieldset>
                <legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
                <div id=loaddatadetail></div>
            </fieldset>";

        CLOSE_BOX();

        break;

    case 'loaddatadetail':
        // GET Jumlah Hari
        $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        // Hitung angka hari awal dan akhir
        $tglAwal    = ($minggu - 1) * 7 + 1;
        $tglAkhir   = $minggu * 7;
        if ($tglAkhir > $jumlahHari) {
            $tglAkhir = $jumlahHari;
        }

        // Format Ke tanggal SQL (YYYY-MM-DD)
        // Menggunakan sprintf untuk memastikan angka hari selalu 2 digit (misal: 1 jadi 01)
        $tanggalAwal  = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tglAwal);
        $tanggalAkhir = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tglAkhir);

        // GET Saldo Awal Keuangan Bulanan
        $skeu = "SELECT awal$bulan AS saldoawal FROM $dbname.keu_saldobulanan
        WHERE kodeorg='" . $unit . "' AND periode='" . $tahun . "" . $bulan . "' AND noakun='" . $noakun . "'";
        $rkeu = fetchData($skeu);
        $saldoawalkas = $rkeu[0]['saldoawal'];

        // GET Mutasi Kasbank keluar dan masuk
        $skas = "SELECT
        SUM(CASE WHEN tipetransaksi = 'M' THEN jumlah ELSE 0 END) AS jumlahmasuk,
        SUM(CASE WHEN tipetransaksi = 'K' THEN jumlah ELSE 0 END) AS jumlahkeluar
        FROM $dbname.`keu_kasbankht`
        WHERE `kodeorg` = '" . $unit . "' AND `tanggal` between '" . $tanggalAwal . "' AND '" . $tanggalAkhir . "' AND `noakun` = '" . $noakun . "'";
        $rkas = fetchData($skas);
        $jumlahmasukkas  = $rkas[0]['jumlahmasuk'];
        $jumlahkeluarkas = $rkas[0]['jumlahkeluar'];

        $saldoakhirkas   = $saldoawalkas + $jumlahmasukkas - $jumlahkeluarkas;

        $tab = "";
        $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable width='71.5%'>";
        $tab .= "<thead>";
        $tab .= "<tr class='rowheader'>";
        $tab .= "<th style='text-align:center;' rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>";
        $tab .= "<th style='text-align:center;'>" . $_SESSION['lang']['uraian'] . "</th>";
        $tab .= "<th style='text-align:center;' colspan='3'>Minggu Ke - " . $minggu . "</th>";
        $tab .= "</tr>";
        $tab .= "<tr class='rowheader'>";
        $tab .= "<th style='text-align:center;'>" . $_SESSION['lang']['jumlah'] . " Fisik</th>";
        $tab .= "<th style='text-align:center;'>" . $_SESSION['lang']['jumlah'] . "</th>";
        $tab .= "<th style='text-align:center;'>Nominal</th>";
        $tab .= "<th style='text-align:center;'>" . $_SESSION['lang']['action'] . "</th>";
        $tab .= "</tr>";
        $tab .= "</thead>";

        $tab .= "<tbody>";
        $tab .= "<tr class='rowcontent' style='background-color:#F0F3BD'>
                    <td style='text-align:center;font-weight:bold;color:#F45D01' colspan='5'>
                        Total Saldo Akhir Kas Saat ini : " . number_format($saldoakhirkas, 2) . "
                    </td>
                </tr>";

        // Get Data Detail Saat Ini
        $sdet = "SELECT * FROM $dbname.keu_cashopnamedt WHERE notransaksi='" . $notrans . "'";
        $rdet = fetchData($sdet);
        $countdet = count($rdet);
        // Jika ada datanya, maka munculkan data detailnya
        if ($countdet > 0) {
            $no = 0;
            $totalnominal = $totaljumlah = 0;
            foreach ($rdet as $val) {
                $no++;
                $tab .= "<tr class='rowcontent'>";
                $tab .= "<td style='text-align:center;'>" . $no . "</td>";
                $tab .= "<td style='text-align:right;'>" . number_format($val['jumlahfisik'], 2) . "</td>";
                $tab .= "<td style='text-align:right;'>" . number_format($val['jumlah'], 2) . "</td>";
                $tab .= "<td style='text-align:right;'>" . number_format($val['nominal'], 2) . "</td>";
                $tab .= "<td style='text-align:center;'>
                                <img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                                onclick=\"deletedetail('" . $val['notransaksi'] . "','" . $val['jumlahfisik'] . "','" . $val['jumlah'] . "','" . $mode . "');\" >
                            </td>";
                $tab .= "</tr>";

                $totaljumlah    += $val['jumlah'];
                $totalnominal   += $val['nominal'];
            }

            $tab .= "<tr class='rowcontent'>
                        <td style='text-align:center;font-weight:bold;' colspan='2'>" . $_SESSION['lang']['total'] . "</td>
                        <td style='text-align:right;font-weight:bold;'>" . number_format($totaljumlah, 2) . "</td>
                        <td style='text-align:right;font-weight:bold;'>" . number_format($totalnominal, 2) . "</td>
                        <td style='text-align:center;font-weight:bold;'></td>
                    </tr>";
        }
        $tab .= "</tbody>";
        $tab .= "</table>";

        echo $tab;
        break;

    case 'insertDetail':
        try {
            $owlPDO->beginTransaction();

            // Cek Apakah Data Cash Opname sudah diproses posting
            $countcek = 0;
            $scek = "SELECT COUNT(*) AS jmlhrow FROM $dbname.keu_cashopnamedt WHERE notransaksi='" . $notrans . "' 
            AND jumlahfisik='" . $jumlahfisik . "' AND jumlah='" . $jumlah . "'";
            $rcek = fetchData($scek);
            $countcek = $rcek[0]['jmlhrow'];

            if ($countcek > 0) {
                throw new PDOException("Transaksi Detail dengan Jumlah fisik dan jumlah yang diinput sudah ada !");
            }

            if ($jumlahfisik == "") {
                throw new PDOException("Jumlah fisik harus dipilih !");
            }

            if ($jumlah == "" || $jumlah == 0 || $jumlah == 0.00 || $jumlah == null) {
                throw new PDOException("Jumlah harus diinput !");
            }

            // GET Jumlah Hari
            $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
            // Hitung angka hari awal dan akhir
            $tglAwal    = ($minggu - 1) * 7 + 1;
            $tglAkhir   = $minggu * 7;
            if ($tglAkhir > $jumlahHari) {
                $tglAkhir = $jumlahHari;
            }

            // Format Ke tanggal SQL (YYYY-MM-DD)
            // Menggunakan sprintf untuk memastikan angka hari selalu 2 digit (misal: 1 jadi 01)
            $tanggalAwal  = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tglAwal);
            $tanggalAkhir = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tglAkhir);

            // GET Saldo Awal Keuangan Bulanan
            $skeu = "SELECT awal$bulan AS saldoawal FROM $dbname.keu_saldobulanan
            WHERE kodeorg='" . $unit . "' AND periode='" . $tahun . "" . $bulan . "' AND noakun='" . $noakun . "'";
            $rkeu = fetchData($skeu);
            $saldoawalkas = $rkeu[0]['saldoawal'];

            // GET Mutasi Kasbank keluar dan masuk
            $skas = "SELECT
            SUM(CASE WHEN tipetransaksi = 'M' THEN jumlah ELSE 0 END) AS jumlahmasuk,
            SUM(CASE WHEN tipetransaksi = 'K' THEN jumlah ELSE 0 END) AS jumlahkeluar
            FROM $dbname.`keu_kasbankht`
            WHERE `kodeorg` = '" . $unit . "' AND `tanggal` between '" . $tanggalAwal . "' AND '" . $tanggalAkhir . "' AND `noakun` = '" . $noakun . "'";
            $rkas = fetchData($skas);
            $jumlahmasukkas  = $rkas[0]['jumlahmasuk'];
            $jumlahkeluarkas = $rkas[0]['jumlahkeluar'];

            $saldoakhirkas   = $saldoawalkas + $jumlahmasukkas - $jumlahkeluarkas;

            // GET TOTAL TRANSAKSI CASH OPNAME
            $scash = "SELECT SUM(nominal) AS totalnominal FROM $dbname.keu_cashopnamedt
            WHERE notransaksi='" . $notrans . "'";
            $rcash = fetchData($scash);
            $totalnominal = ($rcash[0]['totalnominal'] + $nominal);

            // Jika Total Nominal Cash Opname melebihi saldo akhir kas maka munculkan validasi
            if ($totalnominal > $saldoakhirkas) {
                throw new PDOException("Total Nominal Yang telah diinput saat ini " . number_format($totalnominal, 2) . "<br>melebihi jumlah saldo akhir kas " . number_format($saldoakhirkas, 2));
            }

            $arrdata = array(
                'notransaksi' => $notrans,
                'jumlahfisik' => $jumlahfisik,
                'jumlah'      => $jumlah,
                'nominal'     => $nominal,
                'updateby'    => $_SESSION['standard']['userid'],
                'updatetime'  => date('Y-m-d H:i:s'),
            );

            $cols = array();
            foreach ($arrdata as $key => $row) {
                $cols[] = $key;
            }

            $inssql = insertQuery($dbname, "keu_cashopnamedt", $arrdata, $cols);
            $owlPDO->exec($inssql);

            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'deletedetail':
        try {
            $owlPDO->beginTransaction();

            $delquery = deleteQuery($dbname, "keu_cashopnamedt", "notransaksi='" . $notrans . "' AND jumlahfisik='" . $jumlahfisik . "' AND jumlah='" . $jumlah . "'");
            $owlPDO->exec($delquery);

            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'html':
        $tab = "";
        $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable width='100%'>";
        $tab .= "<thead>";
        $tab .= "<tr class='rowheader'>";
        $tab .= "<th style='text-align:center;' rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>";
        $tab .= "<th style='text-align:center;'>" . $_SESSION['lang']['uraian'] . "</th>";
        $tab .= "<th style='text-align:center;' colspan='2'>Minggu Ke - " . $minggu . "</th>";
        $tab .= "</tr>";
        $tab .= "<tr class='rowheader'>";
        $tab .= "<th style='text-align:center;'>" . $_SESSION['lang']['jumlah'] . " Fisik</th>";
        $tab .= "<th style='text-align:center;'>" . $_SESSION['lang']['jumlah'] . "</th>";
        $tab .= "<th style='text-align:center;'>Nominal</th>";
        $tab .= "</tr>";
        $tab .= "</thead>";

        $tab .= "<tbody>";
        // Get Data Detail Saat Ini
        $sdet = "SELECT * FROM $dbname.keu_cashopnamedt WHERE notransaksi='" . $notrans . "'";
        $rdet = fetchData($sdet);
        $countdet = count($rdet);
        // Jika ada datanya, maka munculkan data detailnya
        if ($countdet > 0) {
            $no = 0;
            $totalnominal = $totaljumlah = 0;
            foreach ($rdet as $val) {
                $no++;
                $tab .= "<tr class='rowcontent'>";
                $tab .= "<td style='text-align:center;'>" . $no . "</td>";
                $tab .= "<td style='text-align:right;'>" . number_format($val['jumlahfisik'], 2) . "</td>";
                $tab .= "<td style='text-align:right;'>" . number_format($val['jumlah'], 2) . "</td>";
                $tab .= "<td style='text-align:right;'>" . number_format($val['nominal'], 2) . "</td>";
                $tab .= "</tr>";

                $totaljumlah    += $val['jumlah'];
                $totalnominal   += $val['nominal'];
            }

            $tab .= "<tr class='rowcontent'>
                        <td style='text-align:center;font-weight:bold;' colspan='2'>" . $_SESSION['lang']['total'] . "</td>
                        <td style='text-align:right;font-weight:bold;'>" . number_format($totaljumlah, 2) . "</td>
                        <td style='text-align:right;font-weight:bold;'>" . number_format($totalnominal, 2) . "</td>
                    </tr>";
        } else {
            $tab .= "<tr class='rowcontent'>
                        <td style='text-align:center;font-weight:bold;' colspan='4'>" . $_SESSION['lang']['errdetailnotexist'] . "</td>
                    </tr>";
        }
        $tab .= "</tbody>";
        $tab .= "</table>";

        echo $tab;
        break;
}

/**
 * Mendapatkan daftar minggu dalam satu bulan berdasarkan periode.
 *
 * @param string $period Format: 'YYYY-MM' (contoh: '2026-03')
 * @return array         Array berisi minggu dengan start_date dan end_date
 * @throws \InvalidArgumentException Jika format periode tidak valid
 */
function getWeeksInPeriod(string $period): array
{
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
        throw new \InvalidArgumentException("Format periode tidak valid: '{$period}'. Gunakan format 'YYYY-MM'.");
    }

    [$year, $month] = array_map('intval', explode('-', $period));

    $totalDays = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
    $weeks     = [];
    $weekIndex = 1;
    $weekStart = null;

    for ($day = 1; $day <= $totalDays; $day++) {
        $date      = \DateTime::createFromFormat('Y-n-j', "{$year}-{$month}-{$day}");
        $dayOfWeek = (int) $date->format('N'); // 1 = Senin, 7 = Minggu

        if ($weekStart === null) {
            $weekStart = $date->format('Y-m-d');
        }

        // Akhir minggu = hari Minggu (N=7) atau hari terakhir bulan
        if ($dayOfWeek === 7 || $day === $totalDays) {
            $weeks[] = [
                'week'       => $weekIndex,
                'start_date' => $weekStart,
                'end_date'   => $date->format('Y-m-d'),
            ];
            $weekIndex++;
            $weekStart = null;
        }
    }

    return $weeks;
}
