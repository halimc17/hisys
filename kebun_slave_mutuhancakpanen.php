<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$tgl       = tanggalsystemn(checkPostGet('tgl', ''));
$tgl2       = checkPostGet('tgl2', '');
$method    = checkPostGet('method', '');
$kodeorg      = checkPostGet('kodeorg', '');
$nik    = checkPostGet('nik', '');
$nikpemanen = checkPostGet('nikpemanen', '');
$luaspnn   = checkPostGet('luaspnn', '');
$jjgbuahbesar = checkPostGet('jjgbuahbesar', '');
$jjgbuahkecil = checkPostGet('jjgbuahkecil', '');
$mode         = checkPostGet('mode', '');
$totaljjg = checkPostGet('totaljjg', '');
$karyawansch = checkPostGet('karyawansch', '');
$tglsch    = tanggalsystem(checkPostGet('tglsch', ''));
$nmorg     = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmindk     = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');
$nmkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jab   = getPostingJabatan('mutu_ancak');
if (count($_POST) > 0) {
    $param = $_POST;
} else {
    $param = $_GET;
}

switch ($method) {
    case 'loaddata':
        $where = "";
        if ($karyawansch != '') {
            $where .= " and namamandor like '%" . $karyawansch . "%' ";
        }
        if ($tglsch != '') {
            $where .= " and tanggal='" . $tglsch . "' ";
        }
        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = floatval($page) * $limit;
        $maxdisplay = (floatval($page) * $limit);
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where 1=1 and nikmandor in (select karyawanid from datakaryawan where lokasitugas in (" . getOrgDetail(2) . ") ) " . $where . " order by tanggal desc, namamandor asc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;
        $str = "SELECT nik,tanggal,nikmandor,namamandor,posting,postingby FROM " . $dbname . ".kebun_rekapmutuhancakpanen_vw
		where 1=1 " . $where . " and nikmandor in (select karyawanid from datakaryawan where lokasitugas in (" . getOrgDetail(2) . ") ) group by tanggal,namamandor 
        order by tanggal desc, namamandor asc limit " . $offset . "," . $limit . "";
        $tab = "";
        $no = $maxdisplay;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        if ($jlhbrs == 0) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center colspan=10>" . $_SESSION['lang']['errdatanotexist'] . "</td>";
            $tab .= "</tr>";
        } else {
            while ($bar = $res->fetch()) {
                $isi = '';
                $no += 1;
                $tab .= "<tr class=rowcontent  id=tr_$no>";
                $tab .= "<td align=center style='width:5%'>" . $no . "</td>";
                $tab .= "<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
                // $tab.="<td>" . $bar['kodeorg'] . " - " .  $nmindk[$bar['kodeorg']] . "</td>";
                $tab .= "<td align=center>" . getKary($bar['nikmandor'], 'subbagian') . "</td>";
                $tab .= "<td>" . $bar['namamandor'] . "</td>";
                $tab .= "<td>" . $nmkar[$bar['postingby']] . "</td>";
                if ($bar['posting'] == 0) {
                    $isi .= "<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                        onclick=\"edit('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "', 'edit');\" ></td>";
                    $isi .= "<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                        onclick=\"deleteData('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "','" . $page . "');\" ></td>";
                    $isi .= "<td align=center><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Posting' 
                        onclick=\"posting('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "','" . $page . "');\" ></td>";
                } else {
                    if (in_array($_SESSION['empl']['jabatan'], $jab)) {
                        $icon = "images/icons/04/16/04.png";
                        $title = "Unposting";
                        $unpost = " onclick=\"unposting('" . $bar['nikmandor'] . "','" . $bar['tanggal'] . "','" . $page . "');\" ";
                    } else {
                        $icon = "images/icons/04/16/02.png";
                        $title = "Posted";
                        $unpost = '';
                    }
                    $isi .= "<td align=center></td><td align=center></td>";
                    $isi .= "<td align=center><img src=" . $icon . " class=zImgBtn class=zImgBtn height='30'  title='" . $title . "' " . $unpost . " ></td>";
                }
                $isi .= "<td align=center><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' 
                        onclick=\"html('" . $bar['nikmandor'] . "','" . $bar['tanggal'] . "','" . $bar['kodeorg'] . "');\" ></td>";
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
        $footd = createpaging($jlhbrs, $limit, $page, '10', 'loaddata', 'getPage');
        echo $tab . "####" . $footd;
        break;

    case 'html':
        $tTotJjg = $tluaspanen = $tJjgBesar = $tJjgKecil = 0;
        $totDenda = [];
        // Get Kode Denda Panen
        $sDenda = "SELECT id AS nourut, kodedenda, deskripsi, status FROM " . $dbname . ".kebun_5kodedendapanen order by id asc";
        $rDenda = fetchData($sDenda);
        // Get Jumlah Kode Denda Panen
        $countDenda = count($rDenda);

        $tab = "";
        $tab .= "<label>Daftar Mutu Hancak</label>";
        $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['total'] . " <br> " . $_SESSION['lang']['jjg'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['jjg'] . "</td>
            <td align=center colspan='" . $countDenda . "'>" . $_SESSION['lang']['denda'] . "</td>
        </tr>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<th align=center>Basis Besar</th>";
        $tab .= "<th align=center>Basis Kecil</th>";
        foreach ($rDenda as $dnd) {
            $tab .= "<th align=center title='" . $dnd['deskripsi'] . "'>" . $dnd['kodedenda'] . "</th>";

            $namadenda[$dnd['kodedenda']] = $dnd['deskripsi'];
        }
        $tab .= "</tr>";
        $tab .= "</thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where nikmandor='" . $nik . "' and tanggal='" . $tgl2 . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no += 1;
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=left>" . getNamaKaryawan($bar['nik']) . "</td>";
            $tab .= "<td align=center>" . $nmindk[$bar['kodeorg']] . "</td>";
            $tab .= "<td align=right>" . @number_format($bar['totaljjg']) . "</td>";
            $tab .= "<td align=right>" . @number_format($bar['jjgbuahbesar']) . "</td>";
            $tab .= "<td align=right>" . @number_format($bar['jjgbuahkecil']) . "</td>";
            foreach ($rDenda as $dnd) {
                $tab .= "<td align=right>" . @number_format($bar['penalti' . $dnd['nourut']]) . "</td>";
                $totDenda[$dnd['nourut']] += $bar['penalti' . $dnd['nourut']];
            }
            @$tTotJjg += $bar['totaljjg'];
            @$tJjgBesar += $bar['jjgbuahbesar'];
            @$tJjgKecil += $bar['jjgbuahkecil'];
        }
        $tab .= "</tr>";
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=center colspan=3><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab .= "<td align=right><b>" . @number_format($tTotJjg, 2) . "</td>";
        $tab .= "<td align=right><b>" . @number_format($tJjgBesar, 2) . "</td>";
        $tab .= "<td align=right><b>" . @number_format($tJjgKecil, 2) . "</td>";
        foreach ($rDenda as $dnd) {
            $tab .= "<td align=right><b>" . @number_format($totDenda[$dnd['nourut']]) . "</td>";
        }
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br><br>";

        $sCek = "SELECT * FROM $dbname.kebun_rekaphancakpanen_photo WHERE tanggal='" . $tgl2 . "' AND nikamandor='" . $nik . "'";
        $rCek = fetchData($sCek);
        $countCek = count($rCek);
        if ($countCek > 0) {
            $tab .= "<label>Daftar Photo Mutu Hancak</label>";
            $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
                <thead><tr class=rowheader>
                <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['denda'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['photo'] . "</td>
            </tr>";
            $tab .= "</thead>";
            $no = 0;
            $str = "select * from " . $dbname . ".kebun_rekaphancakpanen_photo where nikamandor='" . $nik . "' and tanggal='" . $tgl2 . "'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                $no += 1;
                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=center>" . $no . "</td>";
                $tab .= "<td align=left>" . getNamaKaryawan($bar['nik']) . "</td>";
                $tab .= "<td align=center>" . $nmindk[$bar['kodeorg']] . "</td>";
                $tab .= "<td align=left>[" . $bar['kodedenda'] . "]  " . $namadenda[$bar['kodedenda']] . "</td>";
                $tab .= "<td align=center>";
                $tab .= "<a href='" . $bar['photo'] . "' class='popup-img'>";
                $tab .= "<img onclick=\"popupimage()\" src='" . $bar['photo'] . "' 
                        alt='" . $dnd['photo'] . "' width=50px height=50px>";
                $tab .= "</a>";
                $tab .= "</td>";
            }
            $tab .= "</tr>";
            $tab .= "</table>";
        }
        echo $tab;
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".kebun_rekapmutuhancakpanen where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'posting':
        $str = "UPDATE $dbname.kebun_rekapmutuhancakpanen SET posting='1', postingby='" . $_SESSION['standard']['userid'] . "' 
                WHERE tanggal='" . $tgl2 . "' AND nikmandor='" . $nik . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loaddatadetail':
        // Get Kode Denda Panen
        $sDenda = "SELECT id, kodedenda, deskripsi, status FROM " . $dbname . ".kebun_5kodedendapanen order by id asc";
        $rDenda = fetchData($sDenda);
        // Get Jumlah Kode Denda Panen
        $countDenda = count($rDenda);

        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:905px>
            <thead>
                <tr class=rowheader>
                <th align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</th>
                <th align=center colspan='2'>" . $_SESSION['lang']['jjg'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jjg'] . "</th>
                <th align=center colspan='" . $countDenda . "'>" . $_SESSION['lang']['denda'] . "</th>
                <th align=center rowspan='2' width=50px>" . $_SESSION['lang']['action'] . "</th>
                </tr>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<th align=center>Basis Besar</th>";
        $tab .= "<th align=center>Basis Kecil</th>";
        foreach ($rDenda as $dnd) {
            $tab .= "<td align=center title='" . $dnd['deskripsi'] . "'>" . $dnd['kodedenda'] . "</td>";
        }
        $tab .= "</tr>";
        $tab .= "";
        $tab .= "</thead>";
        $no = 0;
        // foreach ($rDenda as $dnd) {
        //     $totalpenalti.$dnd['id'] = 0;
        // }
        $totalpenalti1 = $totalpenalti2 = $totalpenalti3 = $totalpenalti4 = $totalpenalti5 = $totalpenalti6 = 0;
        $totalpenalti7 = $totalpenalti8 = $totalpenalti9 = $totalpenalti10 = $totalpenalti11 = $totalpenalti12 = $totalpenalti13 = 0;
        $tluaspanen = $tjjgbesar = $tjjgkecil = $totTtljjg = 0;
        if ($mode == "edit") {
            $str = "select * from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "'";
        } else {
            $str = "select * from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where tanggal='" . $tgl . "' and nikmandor='" . $nik . "'";
        }
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no += 1;
            $tab .= "<tr class=rowcontent>
                    <td id=kodeorgd" . $no . ">" . $bar['kodeorg'] . "</td>
                    <td id=tgld" . $no . ">" . $bar['tanggal'] . "</td>
                    <td>" . getNamaKaryawan($bar['nik']) . " - " . getKary($bar['nik'], 'nik') . "</td>
                    <td hidden id=nikd" . $no . ">" . $bar['nik'] . "</td>
                    <td align=right>" . $bar['jjgbuahbesar'] . "</td>
                    <td align=right>" . $bar['jjgbuahkecil'] . "</td>
                    <td align=right>" . $bar['totaljjg'] . "</td>";
            foreach ($rDenda as $dnd) {
                $tab .= "<td align=right>" . @number_format($bar['penalti' . $dnd['id']]) . "</td>";
            }
            if ($mode != "") {
                $tab .= "<td align=center width=25px>
                            <img title='" . $_SESSION['lang']['delete'] . "' class=zImgBtn onclick=\"deletedetail('" . $no . "','edit')\" src='images/application/application_delete.png'/>
                        </td>";
            } else {
                $tab .= "<td align=center width=25px>
                            <img title='" . $_SESSION['lang']['delete'] . "' class=zImgBtn onclick=\"deletedetail('" . $no . "')\" src='images/application/application_delete.png'/>
                        </td>";
            }

            $tab .= "</tr>";
            $tjjgbesar  += $bar['jjgbuahbesar'];
            $tjjgkecil  += $bar['jjgbuahkecil'];
            $totTtljjg  += $bar['totaljjg'];

            $totalpenalti1 += $bar['penalti1'];
            $totalpenalti2 += $bar['penalti2'];
            $totalpenalti3 += $bar['penalti3'];
            $totalpenalti4 += $bar['penalti4'];
            $totalpenalti5 += $bar['penalti5'];
            $totalpenalti6 += $bar['penalti6'];
            $totalpenalti7 += $bar['penalti7'];
            $totalpenalti8 += $bar['penalti8'];
            $totalpenalti9 += $bar['penalti9'];
            $totalpenalti10 += $bar['penalti10'];
            $totalpenalti11 += $bar['penalti11'];
            $totalpenalti12 += $bar['penalti12'];
            $totalpenalti13 += $bar['penalti13'];
        }
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=center colspan=3> <b>" . $_SESSION['lang']['total'] . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($tjjgbesar, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($tjjgkecil, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totTtljjg, 2) . "</b> </td>";

        $tab .= "<td align=right> <b>" . @number_format($totalpenalti1, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti2, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti3, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti4, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti5, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti6, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti7, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti8, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti9, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti10, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti11, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totalpenalti12, 2) . "</b> </td>";

        $tab .= "<td></td>";
        $tab .= "</tr>";
        $tab .= "</table>";
        echo $tab;
        break;

    case 'detail':
        // Cek Apakah Datanya sudah diposting
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where tanggal='" . $tgl . "' and nikmandor='" . $nik . "' and posting='1'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            // Jika ada data sudah diposting maka tidak bisa melakukan pengisian data
            exit("Error : Data untuk Tanggal: " . tanggalnormal($tgl) . " <br> Dengan Mandor " . getNamaKaryawan($nik) . " sudah di posting");
        }

        // Get Kode Denda Panen
        $sDenda = "SELECT id, kodedenda, deskripsi, status FROM " . $dbname . ".kebun_5kodedendapanen order by id asc";
        $rDenda = fetchData($sDenda);
        // Get Jumlah Kode Denda Panen
        $countDenda = count($rDenda);

        OPEN_BOX();
        echo "
        <fieldset>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table border=0 cellpadding=3 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <th align=center rowspan='2' colspan=2>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</th>
            <th align=center colspan='2'>" . $_SESSION['lang']['jjg'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jjg'] . "</th>
            <th align=center colspan='" . $countDenda . "' style='width:100px;'>" . $_SESSION['lang']['dendapanen'] . "</th>
            <th align=center rowspan='2' width=50px>" . $_SESSION['lang']['action'] . "</th>
        </tr>";
        echo "<tr class=rowheader>";
        echo "<th align=center>Basis Besar</th>";
        echo "<th align=center>Basis Kecil</th>";
        foreach ($rDenda as $dnd) {
            echo "<th align=center title='" . $dnd['deskripsi'] . "'>" . $dnd['kodedenda'] . "</th>";
        }
        echo "</tr>";
        echo "</thead>";

        // Jika Mode Edit
        if ($mode != "edit") {
            $sql = "select kodeorg, tanggal,nikmandor, karyawanid, sum(hasilkerja) as totaljjg, sum(jjgbuahbesar) as jjgbuahbesar, sum(jjgbuahkecil) as jjgbuahkecil,
            SUM(penalti1) AS penalti1, SUM(penalti2) AS penalti2, SUM(penalti3) AS penalti3, SUM(penalti4) AS penalti4,
            SUM(penalti5) AS penalti5, SUM(penalti6) AS penalti6, SUM(penalti7) AS penalti7, SUM(penalti8) AS penalti8,
            SUM(penalti9) AS penalti9, SUM(penalti10) AS penalti10, SUM(penalti11) AS penalti11, SUM(penalti12) AS penalti12, SUM(penalti13) AS penalti13
            from " . $dbname . ".kebun_prestasi_new_vw where tanggal='" . $tgl . "' and nikmandor='" . $nik . "' 
            group by kodeorg,karyawanid order by karyawanid asc";
        } else {
            $sql = "select kodeorg, tanggal,nikmandor, karyawanid, sum(hasilkerja) as totaljjg, sum(jjgbuahbesar) as jjgbuahbesar, sum(jjgbuahkecil) as jjgbuahkecil,
            SUM(penalti1) AS penalti1, SUM(penalti2) AS penalti2, SUM(penalti3) AS penalti3, SUM(penalti4) AS penalti4,
            SUM(penalti5) AS penalti5, SUM(penalti6) AS penalti6, SUM(penalti7) AS penalti7, SUM(penalti8) AS penalti8,
            SUM(penalti9) AS penalti9, SUM(penalti10) AS penalti10, SUM(penalti11) AS penalti11, SUM(penalti12) AS penalti12, SUM(penalti13) AS penalti13
            from " . $dbname . ".kebun_prestasi_new_vw where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "' 
            group by kodeorg,karyawanid order by karyawanid asc";
        }

        $no = 0;
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            // Get Luas Induk Blok
            $sBlok = "SELECT indukblok, SUM(luasareaproduktif) AS luasareaproduktif FROM $dbname.setup_blok WHERE indukblok='" . $bar['kodeorg'] . "' AND (" . date(("Y")) . " - tahuntanam >= 3)";
            $rBlok = fetchData($sBlok);
            foreach ($rBlok as $val) {
                $totluasblok[$val['indukblok']] = $val['luasareaproduktif'];
            }
            $no++;
            // Cek Apakah Ada Data Di Rekap Hancak Panen
            $sqldt = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where kodeorg='" . $bar['kodeorg'] . "' 
            and tanggal='" . $bar['tanggal'] . "' and nik='" . $bar['karyawanid'] . "'";
            $resdt = $owlPDO->query($sqldt) or die(print " Gagal: " . PDOException::getMessage());
            $resdt->setFetchMode(PDO::FETCH_ASSOC);
            $bardt = $resdt->fetch();
            $jlhbrsdt = $bardt['jmlhrow'];
            if ($jlhbrsdt == 0) {
                // Jika Data belum ada di rekap hancak panen maka munculkan
                echo "<tr class=rowcontent>
                    <td colspan=2 id=blokx" . $no . ">" . $bar['kodeorg'] . "</td>
                    <td id=tglx" . $no . ">" . $bar['tanggal'] . "</td>
                    <td>" . getNamaKaryawan($bar['karyawanid']) . " - " . getKary($bar['karyawanid'], 'nik') . "</td>
                    <td hidden id=nikx" . $no . ">" . $bar['karyawanid'] . "</td>
                    <td hidden id=nikmandorx" . $no . ">" . $bar['nikmandor'] . "</td>
                    <td align=right id=jjgbuahbesarx" . $no . ">" . $bar['jjgbuahbesar'] . "</td>
                    <td align=right id=jjgbuahkecilx" . $no . ">" . $bar['jjgbuahkecil'] . "</td>
                    <td align=right id=totaljjgx" . $no . ">" . $bar['totaljjg'] . "</td>";
                foreach ($rDenda as $dnd) {
                    echo "<td>
                            <input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=penaltix" . $dnd['id'] . "_" . $no . " style='width:50px;' value='" . $bar['penalti' . $dnd['id']] . "'>
                        </td>";
                }
                if ($mode != "edit") {
                    echo "<td align=center width=25px> <input type=hidden id=method value='insert'>
                            <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail(" . $no . ")\" src='images/save.png'/>
                        </td>";
                } else {
                    echo "<td align=center width=25px> <input type=hidden id=method value='insert'>
                            <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail(" . $no . ",'edit')\" src='images/save.png'/>
                        </td>";
                }
                echo "</tr>";
            }
        }

        echo "</table>
        <br/>
        <button id=done class=mybutton onclick=cancel()>" . $_SESSION['lang']['selesai'] . "</button>
        </fieldset>";

        echo "
        <fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
        <div id=loaddatadetail>
            <script></script>
         </fieldset>";
        CLOSE_BOX();
        break;

    case 'insert':;
        $arrDataIns = array(
            'kodeorg'       => $kodeorg,
            'nik'           => $nikpemanen,
            'tanggal'       => $tgl2,
            'nikmandor'     => $nik,
            'totaljjg'      => $totaljjg,
            'jjgbuahbesar'  => $jjgbuahbesar,
            'jjgbuahkecil'  => $jjgbuahkecil,
            'posting'       => '0',
            'postingby'     => '0000000000',
            'penalti1'   => $param['penalti1'],
            'penalti2'   => $param['penalti2'],
            'penalti3'   => $param['penalti3'],
            'penalti4'   => $param['penalti4'],
            'penalti5'   => $param['penalti5'],
            'penalti6'   => $param['penalti6'],
            'penalti7'   => $param['penalti7'],
            'penalti8'   => $param['penalti8'],
            'penalti9'   => $param['penalti9'],
            'penalti10'  => $param['penalti10'],
            'penalti11'  => $param['penalti11'],
            'penalti12'  => $param['penalti12'],
            // 'penalti13'  => $param['penalti13']
        );

        $colsInsDt = array();
        foreach ($arrDataIns as $key => $row) {
            $colsInsDt[] = $key;
        }
        $insDetail = insertQuery($dbname, "kebun_rekapmutuhancakpanen", $arrDataIns, $colsInsDt);
        // echo "<pre>";
        // print_r($arrpenalti);
        // echo "</pre>";
        // exit("Warning");
        try {
            $owlPDO->exec($insDetail);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'deletedetail':
        $str = "delete from " . $dbname . ".kebun_rekapmutuhancakpanen where kodeorg='" . $kodeorg . "' and tanggal='" . $tgl2 . "' and nik='" . $nikpemanen . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'unposting':
        $str = "UPDATE $dbname.kebun_rekapmutuhancakpanen SET posting='0', postingby='0' 
                WHERE tanggal='" . $tgl2 . "' AND nikmandor='" . $nik . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
}
