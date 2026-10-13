<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');
    include_once('lib/HtmlExcel.php');
    $method         = checkPostGet('method', '');
    $param          =$_POST;if(count($param)==0){$param = $_GET;}
    $nmprjct        = makeOption($dbname,'project','kode,nama');
    $path           = "fileupload/PHP/";
    $emodul         = 'PHP';
    $maksUpload     = 5 * 1024 * 1024; #batas ukuran file upload (byte)
    $formatUpload   = array('.jpeg', '.jpg', '.png', '.pdf', '.xls', '.xlsx', '.doc', '.docx');

    #akses unit sesuai detail unit user (sama seperti halaman lain); unit ada di nomor transaksi (NNN/PHP/UNIT/TAHUN)
    $unitAkses  = getOrgDetail(1);
    $statusEdit = array('0', '3'); #belum diajukan atau koreksi/reconfirm; selain itu tidak boleh diubah

    #format parameter dicek sebelum dipakai di query
    $polaParam = array(
        'notransaksi'       => '/^[A-Za-z0-9\/_\-]{1,40}$/',
        'supplierid'        => '/^[A-Za-z0-9_\-]{1,15}$/',
        'nama'              => '/^[A-Za-z0-9_\-\.]{1,40}$/',
        'kodeorgsch'        => '/^[A-Za-z0-9]{1,6}$/',
        'tanggalsch'        => '/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/',
        'statussch'         => '/^[0-9]$/',
        'jenisupload'       => '/^[0-9]$/',
        'kepada'            => '/^[0-9#]{1,200}$/',
        'page'              => '/^[0-9]{1,6}$/'
    );
    foreach ($polaParam as $kunciParam => $polaX) {
        if (isset($param[$kunciParam]) && $param[$kunciParam] !== '' && (!is_string($param[$kunciParam]) || !preg_match($polaX, $param[$kunciParam]))) {
            exit("Warning : Parameter " . $kunciParam . " tidak valid.");
        }
    }

    #nama project; kode yang tidak ada di master project ditampilkan sebagai tidak ditemukan
    function namaProjectPH($kode)
    {
        global $nmprjct;
        return isset($nmprjct[$kode]) && $nmprjct[$kode] !== '' ? $nmprjct[$kode] : '(project tidak ditemukan)';
    }

    function unitNotransaksi($notransaksi)
    {
        $bagian = explode('/', $notransaksi);
        return isset($bagian[2]) ? $bagian[2] : '';
    }

    #unit pada nomor transaksi harus termasuk unit yang boleh diakses user
    function cekUnitPH($notransaksi)
    {
        global $unitAkses;
        if (!isset($unitAkses[unitNotransaksi($notransaksi)])) {
            exit("Warning : Unit " . unitNotransaksi($notransaksi) . " tidak sesuai hak akses Anda.");
        }
    }

    #baris perbandingan harga (satu baris per kontraktor) + cek akses unit; $harusEdit = hanya boleh jika belum diajukan/koreksi
    function barisPH($notransaksi, $harusEdit = false)
    {
        global $owlPDO, $dbname, $statusEdit;
        $stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_penawaranharga where notransaksi=? order by nourut");
        $stmt->execute(array($notransaksi));
        $baris = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($baris) == 0) {
            exit("Warning : Data perbandingan harga tidak ditemukan.");
        }
        cekUnitPH($notransaksi);
        if ($harusEdit && !in_array($baris[0]['statuspersetujuan'], $statusEdit)) {
            exit("Warning : Perbandingan harga ini sudah diajukan atau disetujui, tidak dapat diubah.");
        }
        return $baris;
    }

    #nilai angka dari klien: kosong dianggap 0, harus angka tidak negatif
    function nilaiPH($nilai, $label, $maks = null)
    {
        if ($nilai === null || $nilai === '') {
            return 0;
        }
        $nilai = is_string($nilai) ? str_replace(',', '', $nilai) : $nilai;
        if (!is_numeric($nilai) || $nilai < 0 || ($maks !== null && $nilai > $maks)) {
            exit("Warning : Nilai " . $label . " tidak valid.");
        }
        return doubleval($nilai);
    }

    #filter daftar perbandingan harga (dipakai daftar, Excel, dan PDF supaya isinya sama); hanya unit yang boleh diakses user
    function wherePH($param)
    {
        global $owlPDO;
        $kolomUnit = "substring_index(substring_index(h.notransaksi,'/',3),'/',-1)";
        $where = " and " . $kolomUnit . " in (" . getOrgDetail(2) . ")";
        if (isset($param['notransaksisch']) && $param['notransaksisch'] !== '') {
            $where .= " and h.notransaksi like " . $owlPDO->quote('%' . $param['notransaksisch'] . '%');
        }
        if (isset($param['tanggalsch']) && $param['tanggalsch'] !== '') {
            $where .= " and h.tanggal=" . $owlPDO->quote(tanggaldb($param['tanggalsch']));
        }
        if (isset($param['kodeorgsch']) && $param['kodeorgsch'] !== '') {
            $where .= " and " . $kolomUnit . "=" . $owlPDO->quote($param['kodeorgsch']);
        }
        if (isset($param['statussch']) && $param['statussch'] !== '') {
            $where .= " and h.statuspersetujuan=" . $owlPDO->quote($param['statussch']);
        }
        return $where;
    }

    #teks status tanpa tag HTML
    function labelStatusPH($status)
    {
        global $arrstatus;
        return isset($arrstatus[$status]) ? strip_tags($arrstatus[$status]) : '';
    }

    #keterangan filter yang aktif untuk kop tarikan
    function infoFilterPH($param)
    {
        global $unitAkses;
        $info = array();
        $unit = (isset($param['kodeorgsch']) && $param['kodeorgsch'] !== '') ? $param['kodeorgsch'] : '';
        $info[] = 'Unit: ' . ($unit != '' ? $unit . (isset($unitAkses[$unit]) ? ' - ' . $unitAkses[$unit] : '') : 'Semua');
        $info[] = 'Status: ' . ((isset($param['statussch']) && $param['statussch'] !== '') ? labelStatusPH($param['statussch']) : 'Semua');
        if (isset($param['tanggalsch']) && $param['tanggalsch'] !== '') {
            $info[] = 'Tanggal: ' . $param['tanggalsch'];
        }
        if (isset($param['notransaksisch']) && $param['notransaksisch'] !== '') {
            $info[] = 'No Transaksi: ' . $param['notransaksisch'];
        }
        return implode(' | ', $info);
    }

    #seluruh perbandingan (satu baris per nomor transaksi) untuk tarikan Excel/PDF
    function dataListPH($param)
    {
        global $dbname;
        return fetchdata("SELECT h.*, (SELECT COUNT(*) FROM " . $dbname . ".lgl_penawaranharga x WHERE x.notransaksi=h.notransaksi) AS jmlkontraktor
                    FROM " . $dbname . ".lgl_penawaranharga h
                    INNER JOIN (SELECT h.notransaksi, MIN(h.nourut) AS mn, MAX(h.createdtime) AS dibuat FROM " . $dbname . ".lgl_penawaranharga h WHERE 1=1 " . wherePH($param) . "
                                GROUP BY h.notransaksi) p ON p.notransaksi=h.notransaksi AND p.mn=h.nourut
                    ORDER BY p.dibuat DESC, h.notransaksi DESC");
    }

    #data satu perbandingan untuk tarikan: header, baris per kontraktor (rumus sama dengan layar), pemenang terpilih, approval
    function dataPH($notransaksi)
    {
        global $owlPDO, $dbname, $nmprjct;
        $rows = barisPH($notransaksi);
        $hdr  = $rows[0];
        $stmt = $owlPDO->prepare("SELECT * FROM " . $dbname . ".lgl_penawaranhargadt WHERE notransaksi=?");
        $stmt->execute(array($notransaksi));
        $dt = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $dt[intval($d['nourut'])] = $d;
        }
        $luas = floatval($hdr['luas']);
        $tax  = floatval($hdr['tax']);
        $rat  = floatval($hdr['rupiah']);
        $baris = array();
        $pemenang = null;
        $rekomendasi = null;
        foreach ($rows as $r) {
            $d = isset($dt[intval($r['nourut'])]) ? $dt[intval($r['nourut'])] : array('rpsatpenawaran' => 0, 'rppenawaran' => 0, 'rpsatnegosiasi' => 0, 'rpnegosiasi' => 0, 'flag' => '0');
            $off  = floatval($d['rppenawaran']);
            $nego = floatval($d['rpnegosiasi']);
            $pajakNego = floatval($d['rpsatnegosiasi']) * $luas * $tax / 100;
            $fixNego   = $nego - $pajakNego;
            $item = array(
                'nourut'     => $r['nourut'],
                'supplierid' => $r['supplierid'],
                'nama'       => getNamaSupplier($r['supplierid']),
                'rpsatoff'   => floatval($d['rpsatpenawaran']),
                'off'        => $off,
                'rpsatnego'  => floatval($d['rpsatnegosiasi']),
                'nego'       => $nego,
                'varian'     => $off - $nego,
                'pajak'      => $pajakNego,
                'fixrpsat'   => $luas > 0 ? $fixNego / $luas : 0,
                'fix'        => $fixNego,
                'terpilih'   => ($d['flag'] == '1')
            );
            $baris[] = $item;
            //pemenang = kontraktor yang sudah dipilih penyetuju (flag detail = 1)
            if ($item['terpilih'] && $pemenang === null) {
                $pemenang = $item;
            }
            //rekomendasi = negosiasi terendah di atas 0 (kontraktor tanpa harga negosiasi tidak dipertimbangkan)
            if ($nego > 0 && ($rekomendasi === null || $nego < $rekomendasi['nego'])) {
                $rekomendasi = $item;
            }
        }
        $stmt = $owlPDO->prepare("SELECT level, karyawanid, status, tanggal FROM " . $dbname . ".approval WHERE notransaksi=? ORDER BY level");
        $stmt->execute(array($notransaksi));
        $approval = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
            $tglApp = (isset($a['tanggal']) && $a['tanggal'] != '0000-00-00 00:00:00' && $a['tanggal'] != '' && $a['status'] != '0') ? date('d-m-Y H:i', strtotime($a['tanggal'])) : '';
            $approval[] = array('level' => $a['level'], 'nama' => getNamaKaryawan($a['karyawanid']), 'status' => labelStatusPH($a['status']), 'kode' => $a['status'], 'tanggal' => $tglApp);
        }
        $rek = null;
        if ($rekomendasi !== null) {
            $pajakRek = $rekomendasi['nego'] * $tax / 100;
            $rek = array('nama' => $rekomendasi['nama'], 'rpsat' => $rekomendasi['rpsatnego'], 'nominal' => $rekomendasi['nego'], 'varrp' => $rekomendasi['nego'] - $rat, 'varpersen' => $rat > 0 ? $rekomendasi['nego'] / $rat * 100 : 0, 'pajak' => $pajakRek, 'setelahpajak' => $rekomendasi['nego'] - $pajakRek);
        }
        return array('hdr' => $hdr, 'unit' => unitNotransaksi($notransaksi), 'projek' => $hdr['keterangan'] . ' - ' . namaProjectPH($hdr['keterangan']), 'baris' => $baris, 'rek' => $rek, 'pemenang' => $pemenang, 'approval' => $approval, 'status' => labelStatusPH($hdr['statuspersetujuan']));
    }
    switch ($method) {
        case 'loadData':
            $tab        = "";
            $footer     = "";
            $limit      = 10;
            $colspan    = 13;
            $page       = isset($param['page']) ? intval($param['page']) : 0;
            $offset     = $page * $limit;
            $no         = $offset;
            $where      = wherePH($param);
            $res        = fetchdata("SELECT COUNT(DISTINCT h.notransaksi) AS jml FROM " . $dbname . ".lgl_penawaranharga h WHERE 1=1 " . $where);
            $jlhbrs     = intval($res[0]['jml']);
            $totrows    = ceil($jlhbrs / $limit);
            if ($totrows == 0) {
                $totrows = 1;
            }

            $isiRow = '';
            for ($er = 1; $er <= $totrows; $er++) {
                $sel    = ($page == $er - 1) ? 'selected' : '';
                $isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
            }

            $frompage   = $offset + 1;
            $topage     = (($page + 1) * $limit > $jlhbrs) ? $jlhbrs : ($page + 1) * $limit;

            if ($jlhbrs < 1) {
                $tab .= "<tr class=rowcontent>
                            <td style='text-align:center' colspan=" . $colspan . ">" . $_SESSION['lang']['errdatanotexist'] . "</td>
                        </tr>";
            } else {
                #satu baris per nomor transaksi (baris kontraktor pertama), terbaru di atas
                $iList  = "SELECT h.*, (SELECT MAX(x.nourut) FROM " . $dbname . ".lgl_penawaranharga x WHERE x.notransaksi=h.notransaksi) AS maxnourut
                            FROM " . $dbname . ".lgl_penawaranharga h
                            INNER JOIN (SELECT h.notransaksi, MIN(h.nourut) AS mn, MAX(h.createdtime) AS dibuat FROM " . $dbname . ".lgl_penawaranharga h WHERE 1=1 " . $where . "
                                        GROUP BY h.notransaksi ORDER BY dibuat DESC, h.notransaksi DESC LIMIT " . $offset . "," . $limit . ") p
                                ON p.notransaksi=h.notransaksi AND p.mn=h.nourut
                            ORDER BY p.dibuat DESC, h.notransaksi DESC";
                $hasil  = fetchdata($iList);

                foreach ($hasil as $dList) {
                    $no += 1;
                    $tab .= "<tr class=rowcontent>";
                    $tab .= " <td align=center style='width:5px'>" . $no . "</td>";
                    $tab .= " <td align=center>" . $dList['notransaksi'] . "</td>";
                    $tab .= " <td align=center>" . tanggalnormal($dList['tanggal']) . "</td>";
                    $tab .= " <td align=left>" . $dList['keterangan'] . " - " . namaProjectPH($dList['keterangan']) . "</td>";
                    $tab .= " <td align=right>" . number_format($dList['rupiah']) . "</td>";
                    $warnaStatus = array('1' => '#a5d6a7', '2' => '#ef9a9a', '3' => '#ffcc80', '9' => '#fff59d');
                    $tab .= " <td align=center style=\"cursor:pointer;background-color:" . (isset($warnaStatus[$dList['statuspersetujuan']]) ? $warnaStatus[$dList['statuspersetujuan']] : '') . "\" onclick=\"gethistoriapproval('" . $dList['notransaksi'] . "')\">" . $arrstatus[$dList['statuspersetujuan']] . "</td>";
                    if (in_array($dList['statuspersetujuan'], $statusEdit)) {
                        $tab .= " <td align=center>
                                    <img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"fillField('" . $dList['notransaksi'] . "','" . tanggalnormal($dList['tanggal']) . "','" . $dList['keterangan'] . "');\">
                                </td>";
                        $tab .= " <td align=center>
                                    <img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"del('" . $dList['notransaksi'] . "','" . $page . "');\">
                                </td>";
                        $tab .= " <td align=center>
                                    <img src=images/nxbtn.png class=zImgBtn title='Input Perbandingan harga' onclick=\"bukaharga('" . $dList['notransaksi'] . "','" . $dList['maxnourut'] . "');\">
                                </td>";
                    } else {
                        $tab .= "<td align=center></td>";
                        $tab .= "<td align=center></td>";
                        $tab .= "<td align=center></td>";
                    }
                    $tab .= "<td align=center>
                                <img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('" . $dList['notransaksi'] . "','" . $dList['maxnourut'] . "');\">
                            </td>";
                    $tab .= "<td align=center width=20px><img src=images/upload-2-xxl.png class=zImgBtn title='Upload' onclick=\"showupload('" . $dList['notransaksi'] . "','1');\"></td>";
                    $tab .= "<td align=center><img src=images/excel.jpg class=zImgBtn title='Excel' onclick=\"exportPH('" . $dList['notransaksi'] . "','excel');\"></td>";
                    $tab .= "<td align=center><img src=images/pdf.jpg class=zImgBtn title='PDF' onclick=\"exportPH('" . $dList['notransaksi'] . "','pdf');\"></td>";
                    $tab .= "</tr>";
                }
                $footer .= "<tr>
                                <td colspan=" . $colspan . " align=center>
                                    " . $frompage . " to " . $topage . " Of " . $jlhbrs . "
                                </td>
                            </tr>";
                $footer .= "<tr>
                                <td colspan=" . $colspan . " align=center>";
                if ($page != 0) {
                    $footer .= "<button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
                }
                $footer .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
                if (($page + 1) != $totrows) {
                    $footer .= "<button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
                }
                $footer .= "</td>
                            </tr>";
            }
            echo $tab . "####" . $footer;
        break;
        case 'saveheader':
            $supplierid = isset($param['supplierid']) ? $param['supplierid'] : '';
            if ($supplierid == '') {
                exit("Warning : Harap memilih kontraktor.");
            }
            #kontraktor harus aktif dan bertipe KONTRAKTOR
            $stmt = $owlPDO->prepare("SELECT a.supplierid FROM " . $dbname . ".log_5supkelompok a LEFT JOIN " . $dbname . ".log_5supplier b ON a.supplierid=b.supplierid WHERE a.tipe='KONTRAKTOR' AND b.status='1' AND a.supplierid=?");
            $stmt->execute(array($supplierid));
            if (!$stmt->fetch()) {
                exit("Warning : Kontraktor tidak valid.");
            }

            if ($param['notransaksi'] == '') {
                //Perbandingan baru: tanggal dan project dari form
                $tanggaldb = tanggaldb($param['tanggal']);
                if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $tanggaldb)) {
                    exit("Warning : Harap mengisi tanggal.");
                }
                $stmt = $owlPDO->prepare("SELECT kode FROM " . $dbname . ".project WHERE kode=? AND posting='0' AND pekerjaan='External'");
                $stmt->execute(array(isset($param['nama']) ? $param['nama'] : ''));
                if (!$stmt->fetch()) {
                    exit("Warning : Harap mengisi nama project yang valid.");
                }

                //Bentuk Kode: NNN/PHP/UNIT/TAHUN, unit = lokasi tugas user
                $unit   = $_SESSION['empl']['lokasitugas'];
                cekUnitPH('000/PHP/' . $unit . '/0000');
                $format = "/PHP/" . $unit . "/" . substr($tanggaldb, 0, 4);
                $stmt   = $owlPDO->prepare("SELECT notransaksi FROM " . $dbname . ".lgl_penawaranharga WHERE notransaksi LIKE ? ORDER BY CAST(SUBSTRING_INDEX(notransaksi,'/',1) AS UNSIGNED) DESC LIMIT 1");
                $stmt->execute(array('%' . $format));
                $bar    = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($bar) {
                    $bagianNo = explode('/', $bar['notransaksi']);
                    $counter  = addZero(intval($bagianNo[0]) + 1, 3);
                } else {
                    $counter  = "001";
                }
                $notransnew = $counter . $format;
                $nourut     = 1;
                $nilaiBaru  = array('tanggal' => $tanggaldb, 'keterangan' => $param['nama'], 'status' => '0', 'luas' => 0, 'rpsat' => 0, 'rupiah' => 0, 'tax' => 0);
            } else {
                //Tambah kontraktor ke perbandingan yang ada: hanya bila masih boleh diubah, data header ikut yang tersimpan
                $baris      = barisPH($param['notransaksi'], true);
                $notransnew = $param['notransaksi'];
                $nourut     = intval($baris[count($baris) - 1]['nourut']) + 1;
                $nilaiBaru  = array('tanggal' => $baris[0]['tanggal'], 'keterangan' => $baris[0]['keterangan'], 'status' => $baris[0]['statuspersetujuan'], 'luas' => $baris[0]['luas'], 'rpsat' => $baris[0]['rpsat'], 'rupiah' => $baris[0]['rupiah'], 'tax' => $baris[0]['tax']);
            }

            $stmt = $owlPDO->prepare("SELECT notransaksi FROM " . $dbname . ".lgl_penawaranharga WHERE notransaksi=? AND supplierid=?");
            $stmt->execute(array($notransnew, $supplierid));
            if ($stmt->fetch()) {
                exit("Warningsystem : Kode Assignment " . getNamaSupplier($supplierid) . " sudah ada di notransaksi " . $notransnew);
            }

            try {
                $stmt = $owlPDO->prepare("INSERT INTO " . $dbname . ".lgl_penawaranharga
                            (`notransaksi`,`nourut`,`tanggal`,`supplierid`,`keterangan`,`luas`,`rpsat`,`rupiah`,`tax`,`statuspersetujuan`,`createdby`,`createdtime`) VALUES
                            (?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute(array($notransnew, $nourut, $nilaiBaru['tanggal'], $supplierid, $nilaiBaru['keterangan'], $nilaiBaru['luas'], $nilaiBaru['rpsat'], $nilaiBaru['rupiah'], $nilaiBaru['tax'], $nilaiBaru['status'], $_SESSION['standard']['userid'], date('Y-m-d H:i:s')));
                echo trim($notransnew);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        break;
        case 'opendetail':
            $t = "";
            cekUnitPH($param['notransaksi']);
            $stmt = $owlPDO->prepare("SELECT * FROM " . $dbname . ".lgl_penawaranharga WHERE notransaksi=? ORDER BY nourut");
            $stmt->execute(array($param['notransaksi']));
            $rel = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rel) > 0) {
                $bolehHapus = in_array($rel[0]['statuspersetujuan'], $statusEdit);
                foreach ($rel as $v) {
                    $t .= "<tr class=rowcontent>";
                    $t .= "<td align=center>" . $v['nourut'] . "</td>";
                    $t .= "<td align=center>" . $v['notransaksi'] . "</td>";
                    $t .= "<td align=center>" . $v['keterangan'] . " - " . namaProjectPH($v['keterangan']) . "</td>";
                    $t .= "<td align=center>" . getNamaSupplier($v['supplierid']) . "</td>";
                    $t .= "<td align=center>";
                    if ($bolehHapus) {
                        $t .= "<img src=images/application/application_delete.png class=zImgBtn title='Hapus Kontraktor " . getNamaSupplier($v['supplierid']) . "' onclick=\"hapus('" . $v['notransaksi'] . "','" . $v['supplierid'] . "','" . getNamaSupplier($v['supplierid']) . "');\">";
                    }
                    $t .= "</td>";
                    $t .= "</tr>";
                }
                echo $t . "#" . count($rel);
            } else {
                $t .= "<tr class=rowcontent><td align=center colspan=5>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
                echo $t;
            }
        break;
        case 'bukaharga':
            barisPH($param['notransaksi'], true);
            $str=selectQuery($dbname,'lgl_penawaranharga','*',"notransaksi='".$param['notransaksi']."'");
            $rst=fetchData($str);
            
            $str2=selectQuery($dbname,'lgl_penawaranhargadt','*',"notransaksi='".$param['notransaksi']."'");
            $rst2=fetchData($str2);
            foreach ($rst2 as $v2) {
                $data[$v2['nourut']]=$v2;
            }

            $tab = "";
            $tab.="
            <table class=sortable cellspacing=1 border=0 width=100%>
                <thead>
                    <tr class=rowheader>
                        <th align=center rowspan=3 style='width:15%'>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</th>
                        <th align=center rowspan=3 style='width:2%'>Tax</th>
                        <th align=center rowspan=2 colspan=3 style='width:14%'>RAT</th>";
                        foreach ($rst as $v) {
                            $tab.=" <th align=center rowspan=1 colspan=5><b style='font-size:13px'>" . getNamaSupplier($v['supplierid']) . "</b></th>";
                        }
                    // $tab.=" <th align=center rowspan=1 colspan=4>Summary</th>";
                    $tab.=" 
                    </tr>
                    <tr class=rowheader>";
                        for ($i=1; $i <= count($rst) ; $i++) { 
                            $tab.="
                            <th align=center colspan=2>Penawaran</th>
                            <th align=center colspan=2>Negosiasi</th>
                            <th align=center rowspan=2>Var -RP</th>";
                        }
                    $tab.="
                    </tr>
                    <tr class=rowheader>
                        <th>RP / Sat</th>
                        <th style='width:1%'>Fisik</th>
                        <th>Nominal</th>";
                        for ($i=1; $i <= count($rst)*2 ; $i++) { 
                            $tab.=" <th>RP / Sat</th>
                            <th>Nominal</th>";
                        }
                    // $tab.=" <th>RP / Sat</th>
                    //         <th>Nominal</th>
                    //         <th>Var RP</th>
                    //         <th>Var (%)</th>
                    //     </tr>";
                $tab.="
                </thead>
                <tbody>";
                    $tab.="
                    <tr class=rowcontent>
                        <td align=center>".$rst[0]['keterangan']." - ".namaProjectPH($rst[0]['keterangan'])."</td>
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsat');hitungrat()\" maxlength=12 id=rpsat class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['rpsat'] == '0' ? '' : $rst[0]['rpsat'])."'>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('luas');hitungrat()\"  maxlength=7 id=luas class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['luas'] == '0' ? '' : $rst[0]['luas'])."'>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=rupiah class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['rupiah'] == '0' ? '' : $rst[0]['rupiah'])."' disabled>
                        </td>";
                    foreach ($rst as $v) {
                        @$max++;
                        $tab.="
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatoff_".$v['nourut']."');hitungoff('".$v['nourut']."','".getNamaSupplier($v['supplierid'])."');hitungtax('".count($rst)."')\" maxlength=12 id=rpsatoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".$data[$v['nourut']]['rpsatpenawaran']."'>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=rupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".$data[$v['nourut']]['rppenawaran']."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatnego_".$v['nourut']."');hitungnego('".$v['nourut']."','".getNamaSupplier($v['supplierid'])."');hitungtax('".count($rst)."')\" maxlength=12 id=rpsatnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".$data[$v['nourut']]['rpsatnegosiasi']."'>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=rupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".$data[$v['nourut']]['rpnegosiasi']."' disabled>
                        </td>
                        <td id=varrp_".$v['nourut']." align=right></td>";
                    }
                $tab.="
                </tr>";
                $tab.="
                <tr class=rowcontent>
                    <td align=center>".$_SESSION['lang']['pajak']."</td>
                    <td align=center>
                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('tax')\" maxlength=4 onblur=\"hitungtax('".$max."')\" id=tax class=myinputtextnumber style=\"width:85%;\" value='".$rst[0]['tax']."'>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>";
                    foreach ($rst as $v) {
                        $tab.="
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('taxrupiahoff_".$v['nourut']."')\" maxlength=12 id=taxrupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('taxrupiahnego_".$v['nourut']."')\" maxlength=12 id=taxrupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td></td>";
                    }
                $tab.=" </tr>";
                $tab.="
                <tr class=rowcontent>
                    <td align=center><b>Harga setelah pajak</b></td>
                    <td align=center></td>
                    <td></td>
                    <td></td>
                    <td></td>";
                    foreach ($rst as $v) {
                        $tab.="
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrpsatoff_".$v['nourut']."');\" maxlength=12 id=fixrpsatoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrupiahoff_".$v['nourut']."')\" maxlength=12 id=fixrupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrpsatnego_".$v['nourut']."')\" maxlength=12 id=fixrpsatnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrupiahnego_".$v['nourut']."')\" maxlength=12 id=fixrupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td id=fixvarrp_".$v['nourut']." align=right></td>";
                    }
                $tab.=" </tr>";
                $tab.=" <tr><td align=center colspan=40>
                            <input type=hidden id=methoddt value='simpandt'>
                            <input type=hidden id=notransaksidt value=''>
                            <button class=mybutton onclick=\"simpandetail('".$max."')\" align=center>".$_SESSION['lang']['save']."</button>
                            </td>
                        </tr> ";
                $tab.="
                </tbody>
            </table>";
            echo $tab;
        break;
        case 'html':
            barisPH($param['notransaksi']);
            $str=selectQuery($dbname,'lgl_penawaranharga','*',"notransaksi='".$param['notransaksi']."'");
            $rst=fetchData($str);
            
            $str2=selectQuery($dbname,'lgl_penawaranhargadt','*',"notransaksi='".$param['notransaksi']."'");
            $rst2=fetchData($str2);
            foreach ($rst2 as $v2) {
                $data[$v2['nourut']]=$v2;
            }

            $datarpmin=0; $datarpnegomin=0;
            #rekomendasi = harga negosiasi terendah; kontraktor tanpa harga negosiasi (0) tidak ikut dipertimbangkan
            $str3=selectQuery($dbname,'lgl_penawaranhargadt','rpnegosiasi,nourut,supplierid,rpsatnegosiasi',"notransaksi='".$param['notransaksi']."' and rpnegosiasi>0",'rpnegosiasi','','1');
            $rst3=fetchData($str3);
            foreach ($rst3 as $v3) {
                $datarpmin=$v3['rpnegosiasi'];
                $datarpnegomin=$v3['rpsatnegosiasi'];
            }
            $tab = "";
            $tab.="
            <table class=sortable cellspacing=1 border=0 width=100%>
                <thead>
                    <tr class=rowheader>
                        <th align=center rowspan=3 style='width:15%'>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</th>
                        <th align=center rowspan=3 style='width:2%'>Tax</th>
                        <th align=center rowspan=2 colspan=3 style='width:14%'>RAT</th>";
                        foreach ($rst as $v) {
                            $tab.=" <th align=center rowspan=1 colspan=5><b style='font-size:13px'>" . getNamaSupplier($v['supplierid']) . "</b></th>";
                        }
                    $tab.=" <th align=center rowspan=2 colspan=4>Rekomendasi Pemenang</th>";
                    $tab.=" 
                    </tr>
                    <tr class=rowheader>";
                        for ($i=1; $i <= count($rst) ; $i++) { 
                            $tab.="
                            <th align=center colspan=2>Penawaran</th>
                            <th align=center colspan=2>Negosiasi</th>
                            <th align=center rowspan=2>Var -RP</th>";
                        }
                    $tab.="
                    </tr>
                    <tr class=rowheader>
                        <th>RP / Sat</th>
                        <th style='width:1%'>Fisik</th>
                        <th>Nominal</th>";
                        for ($i=1; $i <= count($rst)*2 ; $i++) { 
                            $tab.=" <th>RP / Sat</th>
                            <th>Nominal</th>";
                        }
                    $tab.=" <th>RP / Sat</th>
                            <th>Nominal</th>
                            <th>Var RP</th>
                            <th>Var (%)</th>
                        </tr>";
                $tab.="
                </thead>
                <tbody>";
                    $tab.="
                    <tr class=rowcontent>
                        <td align=center>".$rst[0]['keterangan']." - ".namaProjectPH($rst[0]['keterangan'])."</td>
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsat');hitungrat()\" maxlength=12 id=prpsat class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['rpsat'] == '0' ? '' : number_format($rst[0]['rpsat']))."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('luas');hitungrat()\"  maxlength=7 id=pluas class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['luas'] == '0' ? '' : $rst[0]['luas'])."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiah class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['rupiah'] == '0' ? '' : number_format($rst[0]['rupiah']))."' disabled>
                        </td>";
                    foreach ($rst as $v) {
                        @$max++;
                        $tab.="
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatoff_".$v['nourut']."');hitungoff('".$v['nourut']."','".getNamaSupplier($v['supplierid'])."');hitungtax('".count($rst)."')\" maxlength=12 id=prpsatoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".number_format($data[$v['nourut']]['rpsatpenawaran'])."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".number_format($data[$v['nourut']]['rppenawaran'])."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatnego_".$v['nourut']."');hitungnego('".$v['nourut']."','".getNamaSupplier($v['supplierid'])."');hitungtax('".count($rst)."')\" maxlength=12 id=prpsatnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".number_format($data[$v['nourut']]['rpsatnegosiasi'])."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".number_format($data[$v['nourut']]['rpnegosiasi'])."' disabled>
                        </td>
                        <td id=pvarrp_".$v['nourut']." align=right></td>";
                    }
                    $tab.="<td align=right>".number_format($datarpnegomin)."</td>";
                    $tab.="<td align=right>".number_format($datarpmin)."</td>";
                    $tab.="<td align=right>".number_format(($datarpmin - $rst[0]['rupiah']))."</td>";
                    $tab.="<td align=right>".number_format(($rst[0]['rupiah'] > 0 ? $datarpmin/$rst[0]['rupiah']*100 : 0))."</td>";
                $tab.="
                </tr>";
                $tab.="
                <tr class=rowcontent>
                    <td align=center>".$_SESSION['lang']['pajak']."</td>
                    <td align=center>
                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('tax')\" maxlength=3 onblur=\"hitungtax('".$max."')\" id=ptax class=myinputtextnumber style=\"width:85%;\" value='".($rst[0]['tax'] == 0 ? '' : number_format($rst[0]['tax']))."' disabled>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>";
                    foreach ($rst as $v) {
                        $tab.="
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('taxrupiahoff_".$v['nourut']."')\" maxlength=12 id=ptaxrupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('taxrupiahnego_".$v['nourut']."')\" maxlength=12 id=ptaxrupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td></td>";
                    }
                    $tab.="<td align=right></td>";
                    $tab.="<td align=right>".number_format($datarpmin*$rst[0]['tax']/100)."</td>";
                    $tab.="<td align=right></td>";
                    $tab.="<td align=right></td>";
                $tab.=" </tr>";
                $tab.="
                <tr class=rowcontent>
                    <td align=center><b>Harga setelah pajak</b></td>
                    <td align=center></td>
                    <td></td>
                    <td></td>
                    <td></td>";
                    foreach ($rst as $v) {
                        $tab.="
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrpsatoff_".$v['nourut']."');\" maxlength=12 id=pfixrpsatoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrupiahoff_".$v['nourut']."')\" maxlength=12 id=pfixrupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrpsatnego_".$v['nourut']."')\" maxlength=12 id=pfixrpsatnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrupiahnego_".$v['nourut']."')\" maxlength=12 id=pfixrupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td id=pfixvarrp_".$v['nourut']." align=right></td>";
                    }
                    $tab.="<td align=right>".number_format($rst[0]['luas'] > 0 ? ($datarpmin-($datarpmin*$rst[0]['tax']/100))/$rst[0]['luas'] : 0)."</td>";
                    $tab.="<td align=right>".number_format($datarpmin-($datarpmin*$rst[0]['tax']/100))."</td>";
                    $tab.="<td align=right>".number_format(($datarpmin - $rst[0]['rupiah'])-($datarpmin*$rst[0]['tax']/100))."</td>";
                    $tab.="<td align=right>".number_format($rst[0]['rupiah'] > 0 ? (($datarpmin-($datarpmin*$rst[0]['tax']/100))/$rst[0]['rupiah']*100) : 0)."</td>";
                $tab.="
                </tr>";
                $tab.="
                </tbody>
            </table><br>";
            
            $jenispersetujuanx 	= 'PHP';
			$pecahnotrans		= explode('/', $param['notransaksi']);
			$kodeorg 			= $pecahnotrans[2];
			$nmapprval        	= makeOption($dbname,'setup_jenisapproval','jenis,nama');
            
            if($rst[0]['statuspersetujuan'] == 0 || $rst[0]['statuspersetujuan'] == 3){
                if(count($rst2)>0){
                    $tab.="<table cellspacing=1 border=0 class=sortable cellpadding=5 align=center>";
                    $tab.="<tr class=rowcontent><td align=center colspan=3>";
                    $tab.="<h3 style='padding-left:40px'><b>Persetujuan ".$nmapprval[$jenispersetujuanx]."<br></b></h3>";
                    $tab.="<h4 style='padding-left:40px'><b> Unit : ".$kodeorg." - ".getNamaOrg($kodeorg)."</b></h4>";
                    $tab.="</td></tr>";
                    $tab.="<tr class=rowcontent >";
                        $tab.="<td>No Pengajuan</td>";
                        $tab.="<td width=5px>:</td>";
                        $tab.="<td id=notran_aju>".$param['notransaksi']."</td>";
                    $tab.="</tr>";
        
                    ##CEK PER DEPARTEMEN
                    $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg."' and jenispersetujuan='".$jenispersetujuanx."' and departemen='".$departemen."'";
                    $res=fetchdata($str);
                    $perdepartemen=$res[0]['kodeunit'];
                    $where="";
                    if($perdepartemen>0){
                        $where.=" and departemen='".$departemen."'";
                    }else{
                        $where.=" and departemen=''";
                    }
        
                    ## APPROVAL DINAMIS SESUAI SETUP##
                    $optKryx	=array();
                    $optKrylevel=array();
        
                    $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuanx."' and kodeunit='".$kodeorg."' and karyawaniduser='' ".$where." ";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while($bar=$res->fetch()){
                        $whr		=" karyawanid='".$bar['karyawanid']."'";
                        $optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                        
                        $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                        $optKrylevel[$bar['level']]=$bar['level'];
        
                    }
        
                    
                    $jumlahlevel=count($optKrylevel);
                    if($jumlahlevel>0)
                    {
                        for ($i=1; $i <= $jumlahlevel; $i++) { 
                            $optKry='';
                            foreach ($optKryx[$i] as $key2 => $val) {
                                $optKry.=$val;
                            }
                            $tab .= "<tr class=rowcontent>
                                <td>Approval ke-".$i."</td>
                                <td width=5px>:</td>
                                <td><select id=kepada".$i." style='width:250px;'>".$optKry."</select></td>
                            </tr>";					
                        }
                    }
                    else
                    {			
                        $jumlahlevel=1;
                        $tab .= "<tr class=rowcontent>
                            <td>Approval ke-1</td>
                            <td width=5px>:</td>
                            <td><select id=kepada1 style='width:250px;'></select></td>
                        </tr>";
                    }
                        $tab .= "<tr class=rowcontent>
                            <td hidden><input id=jenispersetujuanx style=display:none value=".$jenispersetujuanx."></td><td><input id=numrow style=display:none value=".$jumlahlevel."></td>
                            <td align=left></td>
                            <td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
                        </tr>
                    </table>";
                }

            }else{
              
                $str=selectQuery($dbname,'setup_approval','*',"jenispersetujuan='".$jenispersetujuanx."' and kodeunit='".$kodeorg."'");
                $ree=fetchData($str);
                $tab.="<table cellspacing=1 border=0 class=sortable cellpadding=5 align=center>";
                $tab.="<tr class=rowheader>";
                $tab.="<td align=center>Notransaksi</td>";
                foreach ($ree as $v) {
                    $tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$v['level']."</td>";
                }
                $tab.="</tr>";
                $str=selectQuery($dbname,'approval','*',"jenispersetujuan='".$jenispersetujuanx."' and notransaksi='".$param['notransaksi']."'");
                $rei=fetchData($str);
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center style='height:60px'>".$param['notransaksi']."</td>";
                foreach ($rei as $v) {
                    $tab.="<td align=center style='height:60px'>".getNamaKaryawan($v['karyawanid'])."<br>(".($v['status'] == 1 ? $arrstatus[$v['status']] : $arrstatus[$rst[0]['statuspersetujuan']]).")</td>";
                }
                $tab.="</tr>";
                $tab.="</table>";
                $sql=selectQuery($dbname,'lgl_penawaranhargadt','supplierid',"notransaksi='".$param['notransaksi']."' and flag='1'");
                $tab.="<br><table class=sortable cellspacing=1 cellpadding=5 align=center>
                        <tr class=rowheader><td>Pemenang Perbandingan Project</td></tr>
                        <tr class=rowcontent><td align=center>".getNamaSupplier(fetchData($sql)[0]['supplierid'])."</td></tr></table>";
            }

			echo $tab;
		break;
        case 'simpandt':
            $rows = barisPH($param['notransaksi'], true);
            $jumlahKontraktor = count($rows);
            if (!isset($param['max']) || intval($param['max']) != $jumlahKontraktor) {
                exit("Warning : Jumlah kontraktor tidak sesuai dengan data tersimpan, buka ulang halaman perbandingan harga.");
            }
            #nourut 1..n harus lengkap karena angka dikirim per nourut
            $supplierPerUrut = array();
            foreach ($rows as $barisSup) {
                $supplierPerUrut[intval($barisSup['nourut'])] = $barisSup['supplierid'];
            }
            for ($i = 1; $i <= $jumlahKontraktor; $i++) {
                if (!isset($supplierPerUrut[$i])) {
                    exit("Warning : Urutan kontraktor tidak berurutan, hapus lalu tambahkan ulang kontraktor yang bermasalah.");
                }
            }

            //semua nilai dicek sebelum ada yang ditulis; nominal dihitung ulang di server (Rp/Sat x luas), nilai nominal dari klien diabaikan
            $luas   = nilaiPH(isset($param['luas']) ? $param['luas'] : '', 'luas');
            if ($luas <= 0) {
                exit("Warning : Luas (fisik) harus diisi dan lebih besar dari 0.");
            }
            $rpsat  = nilaiPH(isset($param['rpsat']) ? $param['rpsat'] : '', 'Rp/Sat RAT');
            if ($rpsat <= 0) {
                exit("Warning : Rp/Sat RAT harus diisi dan lebih besar dari 0.");
            }
            $rupiah = round($rpsat * $luas, 2);
            $tax    = nilaiPH(isset($param['tax']) ? $param['tax'] : '', 'pajak', 100);
            $dataDt = array();
            for ($i = 1; $i <= $jumlahKontraktor; $i++) {
                $namaSup    = getNamaSupplier($supplierPerUrut[$i]);
                $rpsatOff   = nilaiPH(isset($param['rpsatoff_' . $i]) ? $param['rpsatoff_' . $i] : '', 'Rp/Sat penawaran ' . $namaSup);
                if ($rpsatOff <= 0) {
                    exit("Warning : Rp/Sat penawaran " . $namaSup . " harus diisi dan lebih besar dari 0.");
                }
                $rpsatNego  = nilaiPH(isset($param['rpsatnego_' . $i]) ? $param['rpsatnego_' . $i] : '', 'Rp/Sat negosiasi ' . $namaSup);
                if ($rpsatNego <= 0) {
                    //belum ada negosiasi: harga tetap sebesar penawaran (supaya tidak dianggap termurah karena bernilai 0)
                    $rpsatNego = $rpsatOff;
                }
                $dataDt[$i] = array($rpsatOff, round($rpsatOff * $luas, 2), $rpsatNego, round($rpsatNego * $luas, 2));
            }

            try {
                $owlPDO->beginTransaction();
                $owlPDO->prepare("DELETE FROM " . $dbname . ".lgl_penawaranhargadt WHERE notransaksi=?")->execute(array($param['notransaksi']));
                $upd = $owlPDO->prepare("UPDATE " . $dbname . ".lgl_penawaranharga SET luas=?, rpsat=?, rupiah=?, tax=?, updatedby=? WHERE notransaksi=? AND nourut=? AND supplierid=?");
                $ins = $owlPDO->prepare("INSERT INTO " . $dbname . ".lgl_penawaranhargadt (`notransaksi`,`nourut`,`supplierid`,`rpsatpenawaran`,`rppenawaran`,`rpsatnegosiasi`,`rpnegosiasi`) VALUES (?,?,?,?,?,?,?)");
                for ($i = 1; $i <= $jumlahKontraktor; $i++) {
                    $upd->execute(array($luas, $rpsat, $rupiah, $tax, $_SESSION['standard']['userid'], $param['notransaksi'], $i, $supplierPerUrut[$i]));
                    $ins->execute(array($param['notransaksi'], $i, $supplierPerUrut[$i], $dataDt[$i][0], $dataDt[$i][1], $dataDt[$i][2], $dataDt[$i][3]));
                }
                $owlPDO->commit();
            } catch (PDOException $e) {
                $owlPDO->rollBack();
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        break;
        case 'del':
            //hanya yang belum diajukan atau koreksi; approval dan file ikut dibersihkan
            barisPH($param['notransaksi'], true);
            $stmt = $owlPDO->prepare("SELECT namafile FROM " . $dbname . ".listfileupload WHERE notransaksi=?");
            $stmt->execute(array($param['notransaksi']));
            $fileHapus = $stmt->fetchAll(PDO::FETCH_COLUMN);
            try {
                $owlPDO->beginTransaction();
                $owlPDO->prepare("DELETE FROM " . $dbname . ".approval WHERE notransaksi=?")->execute(array($param['notransaksi']));
                $owlPDO->prepare("DELETE FROM " . $dbname . ".listfileupload WHERE notransaksi=?")->execute(array($param['notransaksi']));
                $owlPDO->prepare("DELETE FROM " . $dbname . ".lgl_penawaranharga WHERE notransaksi=?")->execute(array($param['notransaksi']));
                $owlPDO->commit();
            } catch (PDOException $e) {
                $owlPDO->rollBack();
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
            foreach ($fileHapus as $namaFile) {
                if (is_file($path . basename($namaFile))) {
                    unlink($path . basename($namaFile));
                }
            }
        break;
        case 'delete':
            //hapus satu kontraktor; detail harga ikut terhapus (relasi), sisa kontraktor dirapikan nourut-nya 1..n
            $rows = barisPH($param['notransaksi'], true);
            $ada = false;
            foreach ($rows as $barisSup) {
                if ($barisSup['supplierid'] == $param['supplierid']) {
                    $ada = true;
                }
            }
            if (!$ada) {
                exit("Warning : Kontraktor tidak ditemukan pada perbandingan harga ini.");
            }
            try {
                $owlPDO->beginTransaction();
                $owlPDO->prepare("DELETE FROM " . $dbname . ".lgl_penawaranhargadt WHERE notransaksi=?")->execute(array($param['notransaksi']));
                $owlPDO->prepare("DELETE FROM " . $dbname . ".lgl_penawaranharga WHERE notransaksi=? AND supplierid=?")->execute(array($param['notransaksi'], $param['supplierid']));
                $urut = 0;
                $ubah = $owlPDO->prepare("UPDATE " . $dbname . ".lgl_penawaranharga SET nourut=? WHERE notransaksi=? AND supplierid=?");
                foreach ($rows as $barisSup) {
                    if ($barisSup['supplierid'] == $param['supplierid']) {
                        continue;
                    }
                    $urut++;
                    if (intval($barisSup['nourut']) != $urut) {
                        $ubah->execute(array($urut, $param['notransaksi'], $barisSup['supplierid']));
                    }
                }
                $owlPDO->commit();
            } catch (PDOException $e) {
                $owlPDO->rollBack();
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        break;
        case 'ajukan':
            //hanya yang belum diajukan atau koreksi; jenis persetujuan tetap PHP dan penyetuju harus sesuai setup approval unit
            $rows = barisPH($param['notransaksi'], true);
            $jenispersetujuanx = 'PHP';
            $unitPH = unitNotransaksi($param['notransaksi']);
            $kepada = isset($param['kepada']) ? $param['kepada'] : '';
            if ($kepada == '') {
                exit("Warning : Isikan nama penyetuju.");
            }
            $stmt = $owlPDO->prepare("SELECT COUNT(*) FROM " . $dbname . ".lgl_penawaranhargadt WHERE notransaksi=?");
            $stmt->execute(array($param['notransaksi']));
            if (intval($stmt->fetchColumn()) < count($rows)) {
                exit("Warning : Detail perbandingan harga belum disimpan lengkap.");
            }

            $stmt = $owlPDO->prepare("SELECT level, karyawanid FROM " . $dbname . ".setup_approval WHERE jenispersetujuan=? AND kodeunit=?");
            $stmt->execute(array($jenispersetujuanx, $unitPH));
            $penyetujuSah = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $barisApp) {
                $penyetujuSah[intval($barisApp['level'])][intval($barisApp['karyawanid'])] = 1;
            }
            $arrkepada = explode('###', $kepada);
            if (count($arrkepada) != count($penyetujuSah)) {
                exit("Warning : Jumlah penyetuju tidak sesuai dengan setup approval unit " . $unitPH . ".");
            }
            for ($i = 0; $i < count($arrkepada); $i++) {
                if (!preg_match('/^[0-9]{1,10}$/', $arrkepada[$i]) || !isset($penyetujuSah[$i + 1][intval($arrkepada[$i])])) {
                    exit("Warning : Penyetuju approval ke-" . ($i + 1) . " tidak valid, pilih dari daftar penyetuju.");
                }
            }

            try {
                $owlPDO->beginTransaction();
                //update flag menjadi 9 (proses persetujuan)
                $owlPDO->prepare("UPDATE " . $dbname . ".lgl_penawaranharga SET statuspersetujuan='9' WHERE notransaksi=?")->execute(array($param['notransaksi']));
                //hapus approval sebelumnya (koreksi), lalu isi ulang sesuai penyetuju terpilih
                $owlPDO->prepare("DELETE FROM " . $dbname . ".approval WHERE notransaksi=?")->execute(array($param['notransaksi']));
                $ins = $owlPDO->prepare("INSERT INTO " . $dbname . ".approval (`nourut`,`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,`status`,`komentar`,`keterangan`,`tanggal`) VALUES ('',?,?,?,?,'0','','','')");
                for ($i = 0; $i < count($arrkepada); $i++) {
                    $ins->execute(array($param['notransaksi'], $jenispersetujuanx, $i + 1, $arrkepada[$i]));
                }
                $owlPDO->commit();
            } catch (PDOException $e) {
                $owlPDO->rollBack();
                echo "Error, " . addslashes($e->getMessage());
            }
        break;
        case 'showupload':
            barisPH($param['notransaksi']);
            $optkriteria = "";
            foreach (getmodulefil($emodul) as $key => $val) {
                $optkriteria .= "<option value='" . $key . "'>" . htmlspecialchars($val['kriteria']) . "</option>";
            }

            $tab="";
            $tab.="<fieldset><legend>Upload</legend>
            <table border=0 >
                <tr>
                    <td>Kriteria</td>
                    <td>:</td>
                    <td>
                        <select id='kriteriaefil'>". $optkriteria."</select>
                    </td>
                </tr>
                <tr>
                    <td>Filename</td>
                    <td></td>
                    <td>
                        <input type='file' name='upload' id='upload' >
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>Format " . implode(', ', $formatUpload) . ", maksimal " . ($maksUpload / 1048576) . " MB</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>
                        <button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."','".$param['jenisupload']."')\">Submit</button>
                    </td>
                </tr>
            </table>
            </fieldset>
                <p />";

            $tab.="<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>
                <table class='sortable' cellspacing='1' border='0' width=100%>
                    <thead>
                    <tr class=rowheader>
                        <td align='center' width=50px>No.</td>
                        <td align='center' width=50px>File Type</td>
                        <td align='center'>Kriteria</td>
                        <td align='center'>Filename</td>
                        <td align='center' width=50px>Action</td>
                    </tr>
                    </thead>
                    <tbody id='listfiles'>
                    </tbody>
                </table>
            </fieldset> ";

            echo $tab;
        break;
        case 'submitfile':
            //melebihi post_max_size: PHP mengosongkan seluruh $_POST dan $_FILES
            if (empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
                exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
            }
            if (!isset($param['notransaksi']) || $param['notransaksi'] == '') {
                exit("Warning : Silahkan isikan detail transaksi terlebih dahulu.");
            }
            barisPH($param['notransaksi']);
            #cek data
            $stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_penawaranhargadt where notransaksi=?");
            $stmt->execute(array($param['notransaksi']));
            if ($stmt->fetchColumn() == 0) {
                exit('Warning : Silahkan isikan dan save detail transaksi terlebih dahulu.');
            }
            $kriteriaefil = isset($param['kriteriaefil']) ? $param['kriteriaefil'] : 'others';
            $arrkriteria = getmodulefil($emodul);
            if (!is_string($kriteriaefil) || !isset($arrkriteria[$kriteriaefil])) {
                exit("Warning : Kriteria file tidak valid.");
            }
            $stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".listfileupload where notransaksi=? and status='1'");
            $stmt->execute(array($param['notransaksi']));
            if ($stmt->fetchColumn() >= 10) {
                exit("Warning : Limit upload hanya 10 file.");
            }
            if (!isset($_FILES['file']) || !is_array($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) {
                exit("Warning : Silahkan pilih file yang akan diupload.");
            }
            $errUpload = $_FILES['file']['error'];
            if ($errUpload == UPLOAD_ERR_INI_SIZE || $errUpload == UPLOAD_ERR_FORM_SIZE) {
                exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
            }
            if ($errUpload != UPLOAD_ERR_OK) {
                exit("Warning : Upload file gagal (kode " . $errUpload . "), silahkan coba lagi.");
            }
            if ($_FILES['file']['size'] > $maksUpload) {
                exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
            }
            $filetype = strtolower('.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if (!in_array($filetype, $formatUpload)) {
                exit("Warning : Format file upload harus " . implode(', ', $formatUpload) . ".");
            }
            if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                exit("Warning : Upload file tidak valid.");
            }
            //nama file unik, tidak bergantung nama asli dari user
            $failnem = "Perbandingan_Harga_Project_" . date('Ymd_His') . "_" . substr(md5(uniqid('', true)), 0, 8) . $filetype;
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $path . $failnem)) {
                exit("Gagal, file tidak dapat disimpan.");
            }
            try {
                $owlPDO->prepare("insert into " . $dbname . ".listfileupload (notransaksi,namafile,formaticon,kriteriaefil,status,createdby,createdtime) values (?,?,?,?,'1',?,?)")
                    ->execute(array($param['notransaksi'], $failnem, $filetype, $kriteriaefil, $_SESSION['standard']['userid'], date('Y-m-d H:i:s')));
            } catch (PDOException $e) {
                unlink($path . $failnem);
                echo " Gagal," . addslashes($e->getMessage());
            }
        break;
        case 'loadfiles':
            barisPH($param['notransaksi']);
            $no = 0;
            $tab = "";
            $arrkriteria = getmodulefil($emodul);
            $stmt = $owlPDO->prepare("select * from " . $dbname . ".listfileupload where notransaksi=? and status='1' order by id");
            $stmt->execute(array($param['notransaksi']));
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(empty($res)){
                $tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }else{
                foreach($res as $key=>$val){
                    $no++;
                    $tab.="<tr class=rowcontent>
                            <td style='text-align:center'>".$no."</td>";
                    $icon=seticonfile($val['formaticon']);
                    $nfile = htmlspecialchars($val['namafile']);
                    $namaKriteria = isset($arrkriteria[$val['kriteriaefil']]) ? $arrkriteria[$val['kriteriaefil']]['kriteria'] : 'Others';
                    $tab.="<td style='text-align:center'>
                            <a href='".$path.$nfile."' download><img src=".$icon." class=zImgBtn></a>
                        </td>";
                    $tab.="<td style='text-align:left'>".htmlspecialchars($namaKriteria)."</td>
                    <td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$nfile."')\">".$nfile."</td>
                        <td align=center>
                            <a href='".$path.$nfile."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a>&nbsp";
                    $tab.="<img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$nfile."','".$param['jenisupload']."');\" >";
                    $tab.="</td>
                        </tr>";
                }
            }
            echo $tab;
        break;
        case 'viewfile':
            //hanya file yang tercatat pada perbandingan harga yang boleh diakses user
            $namafile = basename(isset($param['namafile']) ? $param['namafile'] : '');
            $stmt = $owlPDO->prepare("SELECT notransaksi FROM " . $dbname . ".listfileupload WHERE namafile=? AND status='1' LIMIT 1");
            $stmt->execute(array($namafile));
            $barisFile = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$barisFile) {
                exit("Warning : File tidak ditemukan.");
            }
            barisPH($barisFile['notransaksi']);
            $tab = "<div align=center>";
            $tab .= "<img src='" . $path . $namafile . "' style='width:95%;height:80%;'></div>";
            echo $tab;
        break;
        case 'deletefile':
            //hanya file yang tercatat pada perbandingan harga ini yang boleh dihapus
            barisPH($param['notransaksi']);
            $namafile = basename(isset($param['namafile']) ? $param['namafile'] : '');
            $stmt = $owlPDO->prepare("SELECT namafile FROM " . $dbname . ".listfileupload WHERE notransaksi=? AND namafile=?");
            $stmt->execute(array($param['notransaksi'], $namafile));
            if (!$stmt->fetch()) {
                exit("Warning : File tidak ditemukan.");
            }
            try {
                $owlPDO->prepare("DELETE FROM " . $dbname . ".listfileupload WHERE notransaksi=? AND namafile=?")->execute(array($param['notransaksi'], $namafile));
                if (is_file($path . $namafile)) {
                    unlink($path . $namafile);
                }
            } catch (PDOException $e) {
                echo " Gagal," . addslashes($e->getMessage());
            }
        break;
        case 'excellist':
            $rows = dataListPH($param);
            $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
            $kolom = 8;
            $stream = "<table>
                <tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
                <tr><td colspan=" . $kolom . "><b>PERBANDINGAN HARGA PROJECT</b></td></tr>
                <tr><td colspan=" . $kolom . ">" . htmlspecialchars(infoFilterPH($param)) . "</td></tr>
                <tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
                <tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
                </table>";
            $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
                <thead><tr>
                    <th align=center bgcolor=#CCCCCC>No</th>
                    <th align=center bgcolor=#CCCCCC>No Transaksi</th>
                    <th align=center bgcolor=#CCCCCC>Tanggal</th>
                    <th align=center bgcolor=#CCCCCC>Unit</th>
                    <th align=center bgcolor=#CCCCCC>Project</th>
                    <th align=center bgcolor=#CCCCCC>Jumlah Kontraktor</th>
                    <th align=center bgcolor=#CCCCCC>RAT (Rp)</th>
                    <th align=center bgcolor=#CCCCCC>Status</th>
                </tr></thead><tbody>";
            $no = 0;
            foreach ($rows as $r) {
                $no++;
                $stream .= "<tr>
                    <td align=center>" . $no . "</td>
                    <td>" . $r['notransaksi'] . "</td>
                    <td align=center>" . tanggalnormal($r['tanggal']) . "</td>
                    <td align=center>" . unitNotransaksi($r['notransaksi']) . "</td>
                    <td>" . htmlspecialchars($r['keterangan'] . " - " . namaProjectPH($r['keterangan'])) . "</td>
                    <td align=center>" . $r['jmlkontraktor'] . "</td>
                    <td align=right>" . round($r['rupiah']) . "</td>
                    <td>" . labelStatusPH($r['statuspersetujuan']) . "</td>
                </tr>";
            }
            if ($no == 0) {
                $stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
            }
            $stream .= "</tbody></table>";

            $xls = new HtmlExcel();
            $xls->addSheet("Perbandingan Harga", $stream);
            $xls->headers("Perbandingan_Harga_" . date('YmdHis') . ".xls");
            echo $xls->buildFile();
            exit;

        case 'pdflist':
            require_once('lib/fpdf.php');
            $rows = dataListPH($param);
            $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
            $infoFilter = infoFilterPH($param);

            #teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
            function phFit($pdf, $txt, $w, $f = 7)
            {
                $txt = utf8_decode($txt);
                $pdf->SetFont('Arial', '', $f);
                if ($pdf->GetStringWidth($txt) <= $w - 2) {
                    return $txt;
                }
                while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
                    $txt = substr($txt, 0, -1);
                }
                return rtrim($txt) . '...';
            }

            class PDFListPH extends FPDF
            {
                public $colW = array();

                function Header()
                {
                    global $hdpt, $infoFilter;
                    $width = $this->w - $this->lMargin - $this->rMargin;
                    if (file_exists($hdpt['logo'])) {
                        $this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
                    }
                    $this->SetFont('Arial', 'B', 11);
                    $this->SetXY($this->lMargin + 22, $this->tMargin + 2);
                    $this->Cell(160, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
                    $this->SetY($this->tMargin + 15);
                    $this->SetFont('Arial', 'B', 13);
                    $this->Cell($width, 7, 'PERBANDINGAN HARGA PROJECT', 0, 1, 'C');
                    $this->SetFont('Arial', '', 8);
                    $this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
                    $this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
                    $this->Ln(1);
                    $this->SetFillColor(220, 220, 220);
                    $this->SetFont('Arial', 'B', 8);
                    $judul = array('No', 'No Transaksi', 'Tanggal', 'Unit', 'Project', 'Kontraktor', 'RAT (Rp)', 'Status');
                    foreach ($judul as $i => $j) {
                        $this->Cell($this->colW[$i], 6, $j, 1, 0, 'C', true);
                    }
                    $this->Ln();
                }

                function Footer()
                {
                    $this->SetY(-12);
                    $this->SetFont('Arial', 'I', 7);
                    $this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
                }
            }

            $pdf = new PDFListPH('L', 'mm', 'A4');
            $pdf->AliasNbPages();
            $pdf->SetAutoPageBreak(false, 16);
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $colFixed = array(8, 40, 22, 16, 100, 22, 40); #semua kolom kecuali Status
            $pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
            $pdf->AddPage();
            $h = 6;
            $no = 0;
            foreach ($rows as $r) {
                $no++;
                if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                    $pdf->AddPage();
                }
                $c = $pdf->colW;
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell($c[0], $h, $no, 1, 0, 'C');
                $pdf->Cell($c[1], $h, $r['notransaksi'], 1, 0, 'L');
                $pdf->Cell($c[2], $h, tanggalnormal($r['tanggal']), 1, 0, 'C');
                $pdf->Cell($c[3], $h, unitNotransaksi($r['notransaksi']), 1, 0, 'C');
                $pdf->Cell($c[4], $h, phFit($pdf, $r['keterangan'] . ' - ' . namaProjectPH($r['keterangan']), $c[4]), 1, 0, 'L');
                $pdf->Cell($c[5], $h, $r['jmlkontraktor'], 1, 0, 'C');
                $pdf->Cell($c[6], $h, number_format($r['rupiah']), 1, 0, 'R');
                $pdf->Cell($c[7], $h, phFit($pdf, labelStatusPH($r['statuspersetujuan']), $c[7]), 1, 1, 'L');
            }
            if ($no == 0) {
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
            }
            $pdf->Output();
            exit;

        case 'excelPH':
            $d = dataPH(isset($param['notransaksi']) ? $param['notransaksi'] : '');
            $hdr = $d['hdr'];
            $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
            $kolom = 10;
            $info = array(
                'No Transaksi' => $hdr['notransaksi'],
                'Tanggal' => tanggalnormal($hdr['tanggal']),
                'Unit' => $d['unit'],
                'Project' => $d['projek'],
                'Status' => $d['status'],
                'RAT' => number_format($hdr['rpsat']) . ' x ' . floatval($hdr['luas']) . ' = ' . number_format($hdr['rupiah']),
                'Pajak (%)' => floatval($hdr['tax'])
            );
            $stream = "<table>
                <tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
                <tr><td colspan=" . $kolom . "><b>PERBANDINGAN HARGA PROJECT</b></td></tr>
                <tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
                <tr><td colspan=" . $kolom . ">&nbsp;</td></tr>";
            foreach ($info as $label => $nilai) {
                $stream .= "<tr><td colspan=2><b>" . $label . "</b></td><td colspan=" . ($kolom - 2) . ">" . htmlspecialchars($nilai) . "</td></tr>";
            }
            $stream .= "<tr><td colspan=" . $kolom . ">&nbsp;</td></tr></table>";
            $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
                <thead><tr>
                    <th align=center bgcolor=#CCCCCC>No</th>
                    <th align=center bgcolor=#CCCCCC>Kontraktor</th>
                    <th align=center bgcolor=#CCCCCC>Rp/Sat Penawaran</th>
                    <th align=center bgcolor=#CCCCCC>Nominal Penawaran</th>
                    <th align=center bgcolor=#CCCCCC>Rp/Sat Negosiasi</th>
                    <th align=center bgcolor=#CCCCCC>Nominal Negosiasi</th>
                    <th align=center bgcolor=#CCCCCC>Selisih (Penawaran - Negosiasi)</th>
                    <th align=center bgcolor=#CCCCCC>Pajak Negosiasi (Rp)</th>
                    <th align=center bgcolor=#CCCCCC>Rp/Sat Setelah Pajak</th>
                    <th align=center bgcolor=#CCCCCC>Nominal Setelah Pajak</th>
                </tr></thead><tbody>";
            $no = 0;
            foreach ($d['baris'] as $r) {
                $no++;
                $stream .= "<tr>
                    <td align=center>" . $no . "</td>
                    <td>" . htmlspecialchars($r['nama']) . ($r['terpilih'] ? " (Pemenang)" : "") . "</td>
                    <td align=right>" . round($r['rpsatoff']) . "</td>
                    <td align=right>" . round($r['off']) . "</td>
                    <td align=right>" . round($r['rpsatnego']) . "</td>
                    <td align=right>" . round($r['nego']) . "</td>
                    <td align=right>" . round($r['varian']) . "</td>
                    <td align=right>" . round($r['pajak']) . "</td>
                    <td align=right>" . round($r['fixrpsat']) . "</td>
                    <td align=right>" . round($r['fix']) . "</td>
                </tr>";
            }
            $stream .= "</tbody></table><br />";
            if ($d['rek'] !== null) {
                $rek = $d['rek'];
                $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
                    <tr><td colspan=2 bgcolor=#CCCCCC><b>Rekomendasi (negosiasi terendah)</b></td><td colspan=4>" . htmlspecialchars($rek['nama']) . "</td></tr>
                    <tr><td colspan=2>Rp/Sat</td><td colspan=4 align=right>" . round($rek['rpsat']) . "</td></tr>
                    <tr><td colspan=2>Nominal</td><td colspan=4 align=right>" . round($rek['nominal']) . "</td></tr>
                    <tr><td colspan=2>Selisih terhadap RAT (Rp)</td><td colspan=4 align=right>" . round($rek['varrp']) . "</td></tr>
                    <tr><td colspan=2>Persentase terhadap RAT (%)</td><td colspan=4 align=right>" . round($rek['varpersen'], 2) . "</td></tr>
                    <tr><td colspan=2>Pajak (Rp)</td><td colspan=4 align=right>" . round($rek['pajak']) . "</td></tr>
                    <tr><td colspan=2>Nominal setelah pajak</td><td colspan=4 align=right>" . round($rek['setelahpajak']) . "</td></tr>
                </table><br />";
            }
            $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
                <tr><td bgcolor=#CCCCCC><b>Pemenang</b></td><td colspan=3>" . ($d['pemenang'] !== null ? htmlspecialchars($d['pemenang']['nama']) : 'Belum ditetapkan') . "</td></tr>
            </table><br />";
            if (count($d['approval']) > 0) {
                $jml = count($d['approval']);
                $stream .= "<table cellspacing=1 cellpadding=3 border=1><tr>";
                foreach ($d['approval'] as $a) {
                    $stream .= "<td colspan=2 align=center bgcolor=#CCCCCC><b>Approval ke-" . $a['level'] . "</b></td>";
                }
                $stream .= "</tr><tr>";
                foreach ($d['approval'] as $a) {
                    if ($a['kode'] == '1') {
                        $teks = "<font color=#008000><b>APPROVED</b></font><br>Disetujui secara digital";
                    } elseif ($a['kode'] == '2') {
                        $teks = "<font color=#CC0000><b>DITOLAK</b></font>";
                    } elseif ($a['kode'] == '3') {
                        $teks = "<font color=#CC6600><b>KOREKSI</b></font>";
                    } else {
                        $teks = "<font color=#777777><b>MENUNGGU</b></font>";
                    }
                    $stream .= "<td colspan=2 align=center height=50>" . $teks . "</td>";
                }
                $stream .= "</tr><tr>";
                foreach ($d['approval'] as $a) {
                    $stream .= "<td colspan=2 align=center>" . htmlspecialchars($a['nama']) . "</td>";
                }
                $stream .= "</tr><tr>";
                foreach ($d['approval'] as $a) {
                    $stream .= "<td colspan=2 align=center>" . $a['tanggal'] . "</td>";
                }
                $stream .= "</tr></table>";
            }
            $xls = new HtmlExcel();
            $xls->addSheet("Perbandingan Harga", $stream);
            $xls->headers("Perbandingan_Harga_" . str_replace('/', '-', $hdr['notransaksi']) . ".xls");
            echo $xls->buildFile();
            exit;

        case 'pdfPH':
            require_once('lib/fpdf.php');
            $d = dataPH(isset($param['notransaksi']) ? $param['notransaksi'] : '');
            $hdr = $d['hdr'];
            $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);

            #teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
            function phFit($pdf, $txt, $w, $f = 8)
            {
                $txt = utf8_decode($txt);
                $pdf->SetFont('Arial', '', $f);
                if ($pdf->GetStringWidth($txt) <= $w - 2) {
                    return $txt;
                }
                while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
                    $txt = substr($txt, 0, -1);
                }
                return rtrim($txt) . '...';
            }

            class PDFDetailPH extends FPDF
            {
                public $colW = array();

                function Header()
                {
                    global $hdpt;
                    $width = $this->w - $this->lMargin - $this->rMargin;
                    if (file_exists($hdpt['logo'])) {
                        $this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
                    }
                    $this->SetFont('Arial', 'B', 11);
                    $this->SetXY($this->lMargin + 22, $this->tMargin + 2);
                    $this->Cell(160, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
                    $this->SetY($this->tMargin + 15);
                    $this->SetFont('Arial', 'B', 13);
                    $this->Cell($width, 7, 'PERBANDINGAN HARGA PROJECT', 0, 1, 'C');
                    $this->SetFont('Arial', '', 8);
                    $this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
                    $this->Ln(2);
                }

                function Footer()
                {
                    $this->SetY(-12);
                    $this->SetFont('Arial', 'I', 7);
                    $this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
                }
            }

            function phJudulTabel($pdf)
            {
                $pdf->SetFillColor(220, 220, 220);
                $pdf->SetFont('Arial', 'B', 7);
                $judul = array('No', 'Kontraktor', 'Rp/Sat Penawaran', 'Nominal Penawaran', 'Rp/Sat Negosiasi', 'Nominal Negosiasi', 'Selisih', 'Pajak (Rp)', 'Rp/Sat Setelah Pajak', 'Nominal Setelah Pajak');
                foreach ($judul as $i => $j) {
                    $pdf->Cell($pdf->colW[$i], 7, $j, 1, 0, 'C', true);
                }
                $pdf->Ln();
            }

            $pdf = new PDFDetailPH('L', 'mm', 'A4');
            $pdf->AliasNbPages();
            $pdf->SetAutoPageBreak(false, 16);
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $pdf->colW = array(8, 62, 24, 28, 24, 28, 24, 22, 26, 31);
            $pdf->AddPage();

            #info: dua kolom label : nilai
            $kiri = array(
                'No Transaksi' => $hdr['notransaksi'],
                'Tanggal' => tanggalnormal($hdr['tanggal']),
                'Unit' => $d['unit'],
                'Project' => $d['projek']
            );
            $kanan = array(
                'Status' => $d['status'],
                'RAT' => number_format($hdr['rpsat']) . ' x ' . floatval($hdr['luas']) . ' = ' . number_format($hdr['rupiah']),
                'Pajak (%)' => floatval($hdr['tax']),
                'Jumlah Kontraktor' => count($d['baris'])
            );
            $kunciKiri = array_keys($kiri);
            $kunciKanan = array_keys($kanan);
            $wLabel = 32;
            $wNilai = $width / 2 - $wLabel;
            for ($i = 0; $i < count($kunciKiri); $i++) {
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($wLabel, 5.5, $kunciKiri[$i], 0, 0, 'L');
                $pdf->Cell($wNilai, 5.5, ': ' . phFit($pdf, $kiri[$kunciKiri[$i]], $wNilai, 8), 0, 0, 'L');
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($wLabel, 5.5, $kunciKanan[$i], 0, 0, 'L');
                $pdf->Cell($wNilai, 5.5, ': ' . phFit($pdf, (string) $kanan[$kunciKanan[$i]], $wNilai, 8), 0, 1, 'L');
            }
            $pdf->Ln(3);

            phJudulTabel($pdf);
            $h = 6;
            $no = 0;
            foreach ($d['baris'] as $r) {
                $no++;
                if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                    $pdf->AddPage();
                    phJudulTabel($pdf);
                }
                $c = $pdf->colW;
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell($c[0], $h, $no, 1, 0, 'C');
                $pdf->Cell($c[1], $h, phFit($pdf, $r['nama'] . ($r['terpilih'] ? ' (Pemenang)' : ''), $c[1], 7), 1, 0, 'L');
                $pdf->Cell($c[2], $h, number_format($r['rpsatoff']), 1, 0, 'R');
                $pdf->Cell($c[3], $h, number_format($r['off']), 1, 0, 'R');
                $pdf->Cell($c[4], $h, number_format($r['rpsatnego']), 1, 0, 'R');
                $pdf->Cell($c[5], $h, number_format($r['nego']), 1, 0, 'R');
                $pdf->Cell($c[6], $h, number_format($r['varian']), 1, 0, 'R');
                $pdf->Cell($c[7], $h, number_format($r['pajak']), 1, 0, 'R');
                $pdf->Cell($c[8], $h, number_format($r['fixrpsat']), 1, 0, 'R');
                $pdf->Cell($c[9], $h, number_format($r['fix']), 1, 1, 'R');
            }
            if ($no == 0) {
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
            }

            #pemenang dan approval
            $pdf->Ln(4);
            if ($d['rek'] !== null) {
                $rek = $d['rek'];
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(60, 6, 'Rekomendasi (negosiasi terendah)', 0, 0, 'L');
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(0, 6, ': ' . utf8_decode($rek['nama']) . ' | Rp/Sat ' . number_format($rek['rpsat']) . ' | Nominal ' . number_format($rek['nominal']) . ' | Selisih thd RAT ' . number_format($rek['varrp']) . ' (' . number_format($rek['varpersen'], 2) . '%) | Setelah pajak ' . number_format($rek['setelahpajak']), 0, 1, 'L');
            }
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(60, 6, 'Pemenang', 0, 0, 'L');
            $pdf->Cell(0, 6, ': ' . utf8_decode($d['pemenang'] !== null ? $d['pemenang']['nama'] : 'Belum ditetapkan'), 0, 1, 'L');
            if (count($d['approval']) > 0) {
                //kotak approval digital: satu kotak per level, tertulis APPROVED + nama + waktu bila sudah disetujui
                $lebarKotak = 62;
                $tinggiKotak = 32;
                $jarak = 6;
                $perBaris = max(1, intval(floor(($width + $jarak) / ($lebarKotak + $jarak))));
                if ($pdf->GetY() + $tinggiKotak + 12 > $pdf->h - $pdf->bMargin) {
                    $pdf->AddPage();
                }
                $pdf->Ln(2);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(0, 6, 'Persetujuan', 0, 1, 'L');
                $x0 = $pdf->GetX();
                $y = $pdf->GetY();
                $n = 0;
                foreach ($d['approval'] as $a) {
                    if ($n > 0 && $n % $perBaris == 0) {
                        $y += $tinggiKotak + 4;
                        if ($y + $tinggiKotak > $pdf->h - $pdf->bMargin) {
                            $pdf->AddPage();
                            $y = $pdf->GetY();
                        }
                    }
                    $x = $x0 + ($n % $perBaris) * ($lebarKotak + $jarak);
                    if ($a['kode'] == '1') {
                        $warna = array(0, 128, 0);
                        $teks = 'APPROVED';
                    } elseif ($a['kode'] == '2') {
                        $warna = array(200, 0, 0);
                        $teks = 'DITOLAK';
                    } elseif ($a['kode'] == '3') {
                        $warna = array(204, 102, 0);
                        $teks = 'KOREKSI';
                    } else {
                        $warna = array(120, 120, 120);
                        $teks = 'MENUNGGU';
                    }
                    $pdf->SetDrawColor($warna[0], $warna[1], $warna[2]);
                    $pdf->SetLineWidth(0.4);
                    $pdf->Rect($x, $y, $lebarKotak, $tinggiKotak);
                    $pdf->SetFillColor(235, 235, 235);
                    $pdf->Rect($x, $y, $lebarKotak, 6, 'FD');
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($lebarKotak, 6, 'Approval ke-' . $a['level'], 0, 0, 'C');
                    $pdf->SetTextColor($warna[0], $warna[1], $warna[2]);
                    $pdf->SetFont('Arial', 'B', 13);
                    $pdf->SetXY($x, $y + 8);
                    $pdf->Cell($lebarKotak, 7, $teks, 0, 0, 'C');
                    $pdf->SetFont('Arial', 'I', 7);
                    $pdf->SetXY($x, $y + 15);
                    $pdf->Cell($lebarKotak, 4, $a['kode'] == '1' ? 'Disetujui secara digital' : '', 0, 0, 'C');
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetXY($x, $y + 20);
                    $pdf->Cell($lebarKotak, 5, phFit($pdf, $a['nama'], $lebarKotak, 8), 0, 0, 'C');
                    $pdf->SetFont('Arial', '', 7);
                    $pdf->SetXY($x, $y + 25);
                    $pdf->Cell($lebarKotak, 4, $a['tanggal'], 0, 0, 'C');
                    $n++;
                }
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetLineWidth(0.2);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetY($y + $tinggiKotak + 2);
            }
            $pdf->Output();
            exit;
        default:
    }
?>