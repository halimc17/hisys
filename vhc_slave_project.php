<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

#Semua isian dari browser (POST/GET) di-escape SATU kali di sini sebelum dipakai di kueri; nilai dari database yang ditampilkan di-escape lewat heAC()/jsAC()
function bersihInputCIP(&$v)
{
    global $owlPDO;
    if (is_array($v)) {
        foreach ($v as $k => $x) {
            bersihInputCIP($x);
            $v[$k] = $x;
        }
    } elseif (is_string($v)) {
        $v = substr($owlPDO->quote($v), 1, -1);
    }
}
bersihInputCIP($_POST);
bersihInputCIP($_GET);

#teks untuk ditampilkan di HTML
function heAC($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES);
}

#teks untuk argumen string JavaScript di dalam atribut HTML onclick="fn('...')"
function jsAC($s)
{
    return htmlspecialchars(addcslashes((string)$s, "\\'\"\r\n</"), ENT_QUOTES);
}

#proyek milik unit pengguna (sama dengan pembatasan List Data); null bila tidak ada atau bukan unit pengguna
function ambilProyekAC($kode)
{
    global $dbname;
    $r = fetchData("select kode,kodeorg,posting from " . $dbname . ".project where kode='" . $kode . "' and kodeorg in (" . getOrgDetail(2) . ")");
    return isset($r[0]) ? $r[0] : null;
}

#filter List Data CIP; dipakai tampilan, Excel, dan PDF. Nilai sudah di-escape oleh bersihInputCIP(), di sini ditambah whitelist
function filterCIP()
{
    $w = "";
    $kodecr = trim((string)checkPostGet('kodecr', ''));
    $namacr = trim((string)checkPostGet('namacr', ''));
    $unitcr = trim((string)checkPostGet('unitcr', ''));
    $jeniscr = trim((string)checkPostGet('jeniscr', ''));
    $asetcr = trim((string)checkPostGet('asetcr', ''));
    $pekerjaancr = trim((string)checkPostGet('pekerjaancr', ''));
    $statuscr = trim((string)checkPostGet('statuscr', ''));
    $tahuncr = trim((string)checkPostGet('tahuncr', ''));
    if ($kodecr != '') {
        $w .= " and a.kode like '%" . $kodecr . "%' ";
    }
    if ($namacr != '') {
        $w .= " and a.nama like '%" . $namacr . "%' ";
    }
    if ($unitcr != '') {
        if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $unitcr)) {
            exit("Warning : Unit tidak valid.");
        }
        $w .= " and a.kodeorg = '" . $unitcr . "' ";
    }
    if ($jeniscr != '') {
        if (!in_array($jeniscr, array('AK', 'PB'), true)) {
            exit("Warning : Jenis tidak valid.");
        }
        $w .= " and a.tipe = '" . $jeniscr . "' ";
    }
    if ($asetcr != '') {
        if (!preg_match('/^[A-Za-z0-9]{2}$/', $asetcr)) {
            exit("Warning : Tipe aset tidak valid.");
        }
        $w .= " and substr(a.kode,4,2) = '" . $asetcr . "' ";
    }
    if ($pekerjaancr != '') {
        if (!in_array($pekerjaancr, array('Internal', 'External'), true)) {
            exit("Warning : Int / Ext tidak valid.");
        }
        $w .= " and a.pekerjaan = '" . $pekerjaancr . "' ";
    }
    if ($statuscr != '') {
        if (!in_array($statuscr, array('0', '1'), true)) {
            exit("Warning : Status tidak valid.");
        }
        $w .= " and a.posting = '" . $statuscr . "' ";
    }
    if ($tahuncr != '') {
        if (!preg_match('/^\d{4}$/', $tahuncr)) {
            exit("Warning : Tahun tidak valid.");
        }
        $w .= " and year(a.tanggalmulai) = '" . $tahuncr . "' ";
    }
    return $w;
}

#keterangan filter aktif untuk judul Excel/PDF
function infoFilterCIP()
{
    global $dbname;
    $o = array();
    $unitcr = trim((string)checkPostGet('unitcr', ''));
    $o[] = 'Unit: ' . ($unitcr != '' ? stripslashes($unitcr) : 'Semua');
    $jeniscr = trim((string)checkPostGet('jeniscr', ''));
    $o[] = 'Jenis: ' . ($jeniscr != '' ? stripslashes($jeniscr) : 'Semua');
    $asetcr = trim((string)checkPostGet('asetcr', ''));
    $o[] = 'Tipe Aset: ' . ($asetcr != '' ? stripslashes($asetcr) : 'Semua');
    $pekerjaancr = trim((string)checkPostGet('pekerjaancr', ''));
    $o[] = 'Int/Ext: ' . ($pekerjaancr != '' ? stripslashes($pekerjaancr) : 'Semua');
    $statuscr = trim((string)checkPostGet('statuscr', ''));
    $o[] = 'Status: ' . ($statuscr === '0' ? 'Belum Diposting' : ($statuscr === '1' ? 'Sudah Diposting' : 'Semua'));
    $tahuncr = trim((string)checkPostGet('tahuncr', ''));
    $o[] = 'Tahun: ' . ($tahuncr != '' ? stripslashes($tahuncr) : 'Semua');
    $kodecr = trim((string)checkPostGet('kodecr', ''));
    if ($kodecr != '') {
        $o[] = 'Kode: ' . stripslashes($kodecr);
    }
    $namacr = trim((string)checkPostGet('namacr', ''));
    if ($namacr != '') {
        $o[] = 'Nama: ' . stripslashes($namacr);
    }
    return implode(' | ', $o);
}

#data List Data CIP untuk tarikan Excel/PDF: proyek milik unit pengguna sesuai filter, paling banyak 3000 baris; nilai dan kode aset diambil sekaligus (bukan per baris)
function dataCIP(&$terpotong)
{
    global $dbname;
    $batas = 3000;
    $rows = fetchData("select a.kode,a.kodeorg,a.tipe,a.nama,a.pekerjaan,a.satuan,a.jumlah,a.tanggalmulai,a.tanggalselesai,a.posting,a.lastupdate,b.namakaryawan
        from " . $dbname . ".project a left join " . $dbname . ".datakaryawan b on a.updateby=b.karyawanid
        where 1=1 " . filterCIP() . " and a.kodeorg in (" . getOrgDetail(2) . ") order by substring(a.kode,-7) desc limit " . ($batas + 1));
    $terpotong = (count($rows) > $batas);
    if ($terpotong) {
        array_pop($rows);
    }
    $nilai = array();
    $aset = array();
    if (count($rows) > 0) {
        $in = array();
        foreach ($rows as $r) {
            $in[] = "'" . addslashes($r['kode']) . "'";
        }
        $daftar = implode(',', $in);
        foreach (fetchData("select kodeasset, sum(jumlah) as biaya from " . $dbname . ".keu_jurnaldt where kodeasset in (" . $daftar . ") and noakun like '12903%' group by kodeasset") as $r) {
            $nilai[$r['kodeasset']] = $r['biaya'];
        }
        foreach (fetchData("select kodeproject, kodeasset from " . $dbname . ".sdm_daftarasset where kodeproject in (" . $daftar . ") order by kodeasset") as $r) {
            if (!isset($aset[$r['kodeproject']])) {
                $aset[$r['kodeproject']] = $r['kodeasset'];
            }
        }
    }
    foreach ($rows as $i => $r) {
        $rows[$i]['nilai'] = isset($nilai[$r['kode']]) ? $nilai[$r['kode']] : 0;
        $rows[$i]['kodeasset'] = isset($aset[$r['kode']]) ? $aset[$r['kode']] : '';
    }
    return $rows;
}

function tglCIP($d)
{
    return ($d == '' || $d == '0000-00-00') ? '' : tanggalnormal($d);
}

#proyek untuk operasi tulis rincian (kegiatan / mesin): milik unit pengguna dan belum diposting; keluar dengan pesan bila tidak
function proyekBolehUbahAC($kode)
{
    $p = ambilProyekAC($kode);
    if ($p === null) {
        exit("Gagal: Proyek tidak ditemukan atau bukan unit Anda.");
    }
    if ($p['posting'] == 1) {
        exit("Gagal: Proyek sudah diposting, rincian tidak bisa diubah.");
    }
    return $p;
}

#data proyek lengkap untuk tarikan Excel per proyek: hanya proyek milik unit pengguna; keluar dengan pesan bila tidak ditemukan
function dataProyekAC($kode)
{
    global $dbname;
    if (ambilProyekAC($kode) === null) {
        exit("Gagal: Proyek tidak ditemukan atau bukan unit Anda.");
    }
    $r = fetchData("select * from " . $dbname . ".project where kode='" . $kode . "'");
    return $r[0];
}

#kop Excel per proyek: nama unit/PT, judul, identitas proyek, dan penarik; $kol = jumlah kolom tabel di bawahnya
function kopExcelProyekAC($dHead, $judul, $kol)
{
    global $dbname, $optNmOrg;
    $ind = fetchData("select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $dHead['kodeorg'] . "'");
    $hd = setheadreport(isset($ind[0]['induk']) ? $ind[0]['induk'] : $dHead['kodeorg'], $dHead['kodeorg']);
    $tglm = ($dHead['tanggalmulai'] == '0000-00-00') ? '-' : tanggalnormal($dHead['tanggalmulai']);
    $tgls = ($dHead['tanggalselesai'] == '0000-00-00') ? '-' : tanggalnormal($dHead['tanggalselesai']);
    $h = "<table>
        <tr><td colspan=" . $kol . "><b>" . heAC($hd['nama']) . "</b></td></tr>
        <tr><td colspan=" . $kol . "><b>" . heAC($judul) . "</b></td></tr>
        <tr><td colspan=" . $kol . ">&nbsp;</td></tr>
        <tr><td colspan=2>Unit Kerja</td><td colspan=" . ($kol - 2) . ">: " . heAC($dHead['kodeorg'] . ' - ' . (isset($optNmOrg[$dHead['kodeorg']]) ? $optNmOrg[$dHead['kodeorg']] : '')) . "</td></tr>
        <tr><td colspan=2>Kode Proyek</td><td colspan=" . ($kol - 2) . ">: " . heAC($dHead['kode']) . "</td></tr>
        <tr><td colspan=2>Nama Proyek</td><td colspan=" . ($kol - 2) . ">: " . heAC($dHead['nama']) . "</td></tr>
        <tr><td colspan=2>Periode Proyek</td><td colspan=" . ($kol - 2) . ">: " . $tglm . " s/d " . $tgls . "</td></tr>
        <tr><td colspan=2>Status</td><td colspan=" . ($kol - 2) . ">: " . ($dHead['posting'] == 1 ? 'Sudah Diposting' : 'Belum Diposting') . "</td></tr>
        <tr><td colspan=" . $kol . ">Ditarik oleh " . heAC($_SESSION['empl']['name']) . " (" . heAC($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
        <tr><td colspan=" . $kol . ">&nbsp;</td></tr></table>";
    return $h;
}

#validasi isian rincian kegiatan (dipakai tambah dan ubah); keluar dengan pesan bila tidak sah
function cekRincianAC($nmKeg, $deskripsi, $satKeg, $volKeg, $bobotKeg)
{
    if (trim((string)$nmKeg) == '') {
        exit("Error:Nama Kegiatan wajib diisi");
    }
    if (strlen((string)$nmKeg) > 500 || strlen((string)$deskripsi) > 500) {
        exit("Error:Nama Kegiatan dan Deskripsi maksimal 500 karakter");
    }
    if (trim((string)$satKeg) == '') {
        exit("Error:Satuan wajib diisi");
    }
    if (!is_numeric($volKeg) || $volKeg <= 0) {
        exit("Error:Volume harus berupa angka lebih dari 0");
    }
    if (!is_numeric($bobotKeg) || $bobotKeg < 0 || $bobotKeg > 100) {
        exit("Error:Bobot harus berupa angka 0 sampai 100");
    }
}

#kode proyek pemilik sebuah baris rincian (project_dt.kegiatan); kosong bila tidak ada
function proyekDariKegiatanAC($kegiatan)
{
    global $dbname;
    $r = fetchData("select kodeproject from " . $dbname . ".project_dt where kegiatan='" . $kegiatan . "'");
    return isset($r[0]['kodeproject']) ? $r[0]['kodeproject'] : '';
}

#selisih hari tanggal selesai - tanggal mulai (yyyy-mm-dd); null bila salah satu tanggal tidak sah
function selisihHariAC($mulai, $selesai)
{
    global $dbname;
    $r = fetchData("select datediff('" . $selesai . "', '" . $mulai . "') as selisih");
    return (isset($r[0]['selisih']) && $r[0]['selisih'] !== null) ? (int)$r[0]['selisih'] : null;
}

$path = "fileupload/cip/";
$method = checkPostGet('method', '');
$kode = checkPostGet('kode', '');
$crcapex = checkPostGet('crcapex', '');

$kodecapex = checkPostGet('kodecapex', '');
$unit = checkPostGet('unit', '');
$aset = checkPostGet('aset', '');
$jenis = checkPostGet('jenis', '');
$jenisbiaya = checkPostGet('jenisbiaya', '');
$nama = checkPostGet('nama', '');
$namaaset = checkPostGet('namaaset', '');
$namacr = checkPostGet('namacr', '');
$unitcr = checkPostGet('unitcr', '');
$kodecr = checkPostGet('kodecr', '');
$tipebg = checkPostGet('tipebg', '');
$pekerjaan = checkPostGet('pekerjaan', '');
$tanggalmulai = tanggalsystem(checkPostGet('tanggalmulai', ''));
$tanggalselesai = tanggalsystem(checkPostGet('tanggalselesai', ''));
$kelompok = checkPostGet('kelompok', '');
$nilai = checkPostGet('nilai', '');
$optLokasi = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$kegiatan = checkPostGet('kegiatan', '');
$nmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$satBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optNmKegBrg = makeOption($dbname, 'project_dt', 'kegiatan,namakegiatan');

$kodeproject = checkPostGet('kodeproject', '');
$kodekegiatan = checkPostGet('kodekegiatan', '');
$kodeBarangForm = checkPostGet('kodeBarangForm', ''); //buat insert
$kodebarang = checkPostGet('kodebarang', ''); //buat delete
$jumlahBarangForm = checkPostGet('jumlahBarangForm', '');

$namaBarangCari = checkPostGet('namaBarangCari', '');

$deskripsi = checkPostGet('deskripsi', '');
$satKeg = checkPostGet('satKeg', '');
$volKeg = checkPostGet('volKeg', '');
$bobotKeg = checkPostGet('bobotKeg', '');
$satuan = checkPostGet('satuan', '');
$jumlah = checkPostGet('jumlah', '');

$posisiasset = checkPostGet('posisiasset', '');
$tipelokasiasset = checkPostGet('tipelokasiasset', '');
$nomesin = checkPostGet('nomesin', '');
$norangka = checkPostGet('norangka', '');
$tipemodel = checkPostGet('tipemodel', '');
$keterangan = checkPostGet('keterangan', '');
$dgnapprvl = checkPostGet('dgnapprvl', '');

$aprv1 = checkPostGet('aprv1', '');
$aprv2 = checkPostGet('aprv2', '');
$aprv3 = checkPostGet('aprv3', '');
$aprv4 = checkPostGet('aprv4', '');

$kryApr1 = checkPostGet('kryApr1', '');
$kryApr2 = checkPostGet('kryApr2', '');
$kryApr3 = checkPostGet('kryApr3', '');
$kryApr4 = checkPostGet('kryApr4', '');

if (count($_POST) > 0) {
    $param = $_POST;
} else {
    $param = $_GET;
}

$sub = checkPostGet('sub', '');

$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

# Posting --> Jabatan
$postJabatan = getPostingJabatan('project');

switch ($method) {
    case 'showupload':
        $tab = "";
        $tab .= "
		<table border=0 >
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td id='notranupload'>" . $param['notransaksi'] . "</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='status'></p>
					<p id='loaded_n_total'></p>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('" . $param['notransaksi'] . "')\">Submit</button>
				</td>
			</tr>
		</table>
		";
        // $str = "select * from ".$dbname.".project where notransaksi='".$notransaksi."'";
        $str = "select a.posting,b.* from " . $dbname . ".project a left join " . $dbname . ".project_dt b ON a.kode=b.kodeproject where b.kegiatan = '" . substr($param['notransaksi'], 12, 7) . "'";
        $res = fetchData($str);
        if ($res[0]['posting'] == 1) {
            $tab = "<b>List File Upload<br></b>";
        }
        $tab .= "
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=30px colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		";

        echo $tab;
        break;

    case 'submitfile':
        try {
            $owlPDO->beginTransaction();
            $data = $_POST;
            if (count($data) == 0) {
                $data = $_GET;
            }

            // Mengecek apakah folder sudah ada
            if (!file_exists($path)) {
                // Membuat folder jika belum ada
                if (mkdir($path, 0777, true)) {
                    // echo "Folder berhasil dibuat: $path";
                } else {
                    echo "<label hidden>Warning</label>Gagal membuat folder.";
                }
            }
            // else {
            // 	exit("<label hidden>Warning</label> Folder sudah ada: $path");
            // }

            $newPermissions = 0755; // Ganti dengan nilai izin yang diinginkan

            // Menggunakan chmod untuk mengatur izin folder
            // chmod($path, $newPermissions);

            if (!preg_match('/^[A-Za-z0-9._\-]{1,40}$/', (string)$param['notransaksi'])) {
                throw new PDOException("Nomor transaksi tidak valid.");
            }
            if ($data['fileupload'] != '') {
                if ($_FILES['file']['error'] == 0) {
                    $filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
                    $filename = $_FILES['file']['name'];
                    $filename = $param['notransaksi'] . "_" . date("YmdHis") . $filetype;
                    $kriteria = "CIP";
                    #cek duplikasi nama file
                    $str = "select * from " . $dbname . ".listfileupload where namafile = '" . $filename . "'";
                    $res = fetchData($str);
                    if (count($res) > 0) {
                        throw new PDOException("Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
                    }
                    $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
                    if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
                        $str = "insert into " . $dbname . ".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('" . $param['notransaksi'] . "','" . $filename . "','" . $filetype . "','" . $kriteria . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
                        $owlPDO->exec($str);
                        file_put_contents($path . $filename, $file_tmpname);
                    } else {
                        throw new PDOException("Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
                    }
                    if (!file_exists($path . $filename)) {
                        throw new PDOException("Upload file gagal.");
                    }
                }
            } else {
                throw new PDOException("Upload file gagal.");
            }
            #execute
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;
    case 'loadfiles':
        // $str= "select * from ".$dbname.".project where kegiatan = '".substr($param['notransaksi'],12,7)."'";
        $str = "select a.posting,b.* from " . $dbname . ".project a left join " . $dbname . ".project_dt b ON a.kode=b.kodeproject where b.kegiatan = '" . substr($param['notransaksi'], 12, 7) . "'";
        $res = fetchData($str);
        $posting = $res[0]['posting'];

        $no = 0;
        $tab = "";
        $str = "select * from " . $dbname . ".listfileupload where notransaksi = '" . $param['notransaksi'] . "' and status='1'";
        $res = fetchData($str);
        if (empty($res)) {
            $tab .= "<tr class=rowcontent><td colspan=5 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
        } else {
            foreach ($res as $key => $val) {
                $no++;
                $tab .= "<tr class=rowcontent>
						<td style='text-align:center'>" . $no . "</td>";
                $icon = seticonfile($val['formaticon']);
                $tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=" . $icon . " class=zImgBtn></a>
					</td>";
                $tab .= "<td style='text-align:left;cursor:pointer' onclick=\"viewfile('" . $val['id'] . "')\">" . $val['namafile'] . "</td>";
                if ($posting == 0) {
                    $tab .= "<td align=center width=30px><a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";

                    $tab .= "<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('" . $val['notransaksi'] . $val['kegiatan'] . "','" . $val['namafile'] . "');\" ></td>";
                } else {
                    $tab .= "<td align=center width=30px colspan=2><a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
                }
                $tab .= "</tr>";
            }
        }
        echo $tab;
        break;
    case 'deletefile':
        $str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' and namafile='" . $param['namafile'] . "'";
        try {
            $owlPDO->exec($str);
            $pathx = $path . $param['namafile'];
            #sementara tidak boleh ada unlink
            //unlink($pathx);
        } catch (PDOException $e) {
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
    case 'viewfile':
        $tab = "";
        $str = "select * from " . $dbname . ".listfileupload where id = '" . $param['idfile'] . "'";
        $res = fetchData($str);
        if ($res[0]['formaticon'] == '.xls' or $res[0]['formaticon'] == '.xlsx' or $res[0]['formaticon'] == '.doc' or $res[0]['formaticon'] == '.docx') {
            exit("Warning: Tidak bisa ditampilkan, silahkan download.");
        }

        if ($res[0]['formaticon'] == '.pdf') {
            $tab .= "<embed src='" . $path . $res[0]['namafile'] . "' style='width:100%;height:97%;' type='application/pdf'>";
        } else {
            $tab .= "<img src='" . $path . $res[0]['namafile'] . "'>";
        }

        echo $tab;
        break;

    case 'viewbiaya':
        $shead = "select * from " . $dbname . ".project
                   where kode='" . $kode . "'";
        $qhead = $owlPDO->query($shead) or die(print " Gagal: " . PDOException::getMessage());
        $qhead->setFetchMode(PDO::FETCH_ASSOC);
        $rhead = $qhead->fetch();
        $tab .= "<link rel=stylesheet type=text/css href=style/generic.css><table cellpadding=5 cellspacing=1 border=0 class=sortable><thead><tr>";

        $tab .= "<th align=center>No.</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['nojurnal'] . "</th>";
        $tab .= "<th align=center>Akun " . $_SESSION['lang']['biaya'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['noreferensi'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['debet'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['kredit'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['kegiatan'] . "</th></tr></thead><tbody>";

        // $sDetail = "select keterangan,noreferensi,nojurnal,tanggal,debet,kredit,noakun,kodevhc
        //  from " . $dbname . ".keu_jurnaldt_vw where kodeasset='" . $kode . "' and noakun like '129%'";
        $sDetail = "select keterangan,noreferensi,nojurnal,tanggal,debet,kredit,noakun,kodevhc
                     from " . $dbname . ".keu_jurnaldt_vw where kodeasset='" . $kode . "' and noakun like '129%'";
        $qDetail = $owlPDO->query($sDetail) or die(print " Gagal: " . PDOException::getMessage());
        $row = owlBaris($qDetail);

        if ($row != 0) {
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);
            $nor = $tdb = $tkr = 0;
            while ($rDetail = $qDetail->fetch()) {
                $nor += 1;

                $svhc = "select jenispekerjaan from " . $dbname . ".vhc_rundt_vw
                   where kodevhc='" . $rDetail['kodevhc'] . "' and alokasibiaya='" . $kode . "'";
                $qvhc = $owlPDO->query($svhc) or die(print " Gagal: " . PDOException::getMessage());
                $qvhc->setFetchMode(PDO::FETCH_ASSOC);
                $rvhc = $qvhc->fetch();

                $whrak = "noakun='" . $rvhc['jenispekerjaan'] . "'";
                $optakun = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', $whrak);
                $nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $rDetail['noakun'] . "'");

                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=center>" . $nor . "</td>";
                $tab .= "<td align=left>" . $rDetail['tanggal'] . "</td>";
                $tab .= "<td align=left>" . $rDetail['nojurnal'] . "</td>";
                $tab .= "<td align=left>" . $rDetail['noakun'] . "</td>";
                $tab .= "<td align=center>" . $nmAkun[$rDetail['noakun']] . "</td>";
                $tab .= "<td align=left>" . $rDetail['keterangan'] . "</td>";
                $tab .= "<td align=left>" . $rDetail['noreferensi'] . "</td>";
                $tab .= "<td align=right>" . number_format($rDetail['debet'], 2) . "</td>";
                $tab .= "<td align=right>" . number_format($rDetail['kredit'], 2) . "</td>";
                $tab .= "<td align=left>" . $rvhc['jenispekerjaan'] . " - " . $optakun[$rvhc['jenispekerjaan']] . "</td></tr>";
                $tdb += $rDetail['debet'];
                $tkr += $rDetail['kredit'];
            }
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td colspan=7 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
            $tab .= "<td align=right><b>" . number_format($tdb, 2) . "</td>";
            $tab .= "<td align=right><b>" . number_format($tkr, 2) . "</td>";
            $tab .= "<td align=right><b></td>";
            $tab .= "</tr>";
        } else {
            $tab .= "<tr class=rowcontent><td colspan=9>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        $tab .= "</tbody></table>";

        // echo '<pre>';
        // print_r($rhead);
        // echo '</pre>';

        $tab .= "<br><br><table cellspacing=1 cellpadding=3 border=0 class='sortable'>
                <thead>
                    <tr class='rowheader'>
                        <th colspan='3'> Data </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['kodecapex'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['kode'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['unitkerja'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['kodeorg'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['posisiasset'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['posisiasset'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['aset'] . "</td>
                        <td>:</td>
                        <td>" . substr($rhead['kode'], 3, 2) . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>Sub " . $_SESSION['lang']['aset'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['subtipe'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>Tipe Lokasi " . $_SESSION['lang']['posisiasset'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['tipelokasi'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['jenisbiaya'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['jenisbiaya'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>No Mesin</td>
                        <td>:</td>
                        <td>" . $rhead['nomesin'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['tipe'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['tipe'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['pekerjaan'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['pekerjaan'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>No Rangka</td>
                        <td>:</td>
                        <td>" . $rhead['norangka'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['nama'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['nama'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['satuan'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['satuan'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['jumlah'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['jumlah'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['posisiasset'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['posisiasset'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['tanggal'] . " Mulai</td>
                        <td>:</td>
                        <td>" . tanggalnormal($rhead['tanggalmulai']) . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['tanggal'] . " Selesai</td>
                        <td>:</td>
                        <td>" . tanggalnormal($rhead['tanggalselesai']) . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>Tipe Model</td>
                        <td>:</td>
                        <td>" . $rhead['tipemodel'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['keterangan'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['keterangan'] . "</td>
                    </tr>
                </tbody>
    </table>";

        echo $tab;
        break;


    case 'viewasset':
        $tab .= "<link rel=stylesheet type=text/css href=style/generic.css>
         <div>
         <table class=sortable  border=0 cellspacing=1>
         <thead>
           <tr class=rowheader>
              <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
              <td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
              <td align=center>" . $_SESSION['lang']['posisiasset'] . "</td>
              <td align=center>" . $_SESSION['lang']['namakelompok'] . "</td>
              <td align=center>" . $_SESSION['lang']['kodeasset'] . "</td>
              <td align=center>" . $_SESSION['lang']['namaaset'] . "</td>
              <td align=center>" . $_SESSION['lang']['tanggalperolehan'] . "</td>
              <td align=center>" . $_SESSION['lang']['status'] . "</td>
              <td align=center>" . $_SESSION['lang']['hargaperolehan'] . "</td>
              <td width=20px align=center>" . $_SESSION['lang']['jumlahbulanpenyusutan'] . "</td>
              <td width=20px align=center>" . $_SESSION['lang']['persendecline'] . "</td>
              <td width=20px align=center>" . $_SESSION['lang']['awalpenyusutan'] . "</td>
              <td align=center>" . $_SESSION['lang']['tanggaldisposal'] . "</td>
              <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
              <td align=center>Leasing</td>
              <td align=center>" . $_SESSION['lang']['project'] . "</td>
            </tr>
          </thead>         
          <tbody id=containeraset>";

        if ($_SESSION['language'] == 'EN') {
            $ads = "b.namatipe1 as namatipe";
        } else {
            $ads = "b.namatipe as namatipe";
        }
        $str = "select a.*," . $ads . ",a.namasset as namassett from " . $dbname . ".sdm_daftarasset a
              left join  " . $dbname . ".sdm_5tipeasset b
              on a.tipeasset=.b.kodetipe where kodeproject='" . $kode . "'";
        // echo $str;

        $no = $offset;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $whr = "id='" . $bar->status . "'";
            $optjns = makeOption($dbname, 'keu_5jenisdisposalasset', 'id,keterangan', $whr);
            $no += 1;
            $tab .= " <tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td width=10 align=center>" . $bar->kodeorg . "</td>
                <td>" . $bar->posisiasset . "</td>
                <td>" . $bar->namatipe . "</td>
                <td width=70 align=center>" . $bar->kodeasset . "</td>
                <td>" . $bar->namassett . "</td>
                <td width=20 align=center>" . tanggalnormal($bar->tanggalperolehan) . "</td>
                <td width=20 align=center>" . $optjns[$bar->status] . "</td>
                <td width=100 align=right>" . number_format($bar->hargaperolehan, 2, '.', ',') . "</td>
                <td width=20 align=right>" . $bar->jlhblnpenyusutan . "</td>
                <td align=right>" . $bar->persendecline . "</td>
                <td align=center>" . ($bar->awalpenyusutan) . "</td>
            
                <td align=center>" . tanggalnormal($bar->tanggaldisposal) . "</td>
                <td>" . $bar->keterangan . "</td>
                <td>" . $kamusleasing[$bar->leasing] . "</td>
                <td>" . $bar->kodeproject . "</td>
            </tr>";
        }
        $tab .= "
                     </tbody>
                     <tfoot>
                     </tfoot>
                     </table>
                     </div>";

        $stream .= "<link rel=stylesheet type=text/css href=style/generic.css><table class='sortable' cellspacing='1' border='0'>
	 			<thead>
					<tr>
						<th align=center >" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center >" . $_SESSION['lang']['nojurnal'] . "</th>
						<th align=center >" . $_SESSION['lang']['kodejurnal'] . "</th>
						<th align=center >" . $_SESSION['lang']['namajurnal'] . "</th>
						<th align=center >" . $_SESSION['lang']['tipe'] . "</th>
						<th align=center >" . $_SESSION['lang']['novoucher'] . "</th>
						<th align=center >" . $_SESSION['lang']['tanggal'] . "</th>
						<th align=center >" . $_SESSION['lang']['unit'] . "</th>
						<th align=center >" . $_SESSION['lang']['noakun'] . "</th>
						<th align=center >" . $_SESSION['lang']['namaakun'] . "</th>
						<th align=center >Tipe Pembayaran</th>
						<th align=center >" . $_SESSION['lang']['keterangan'] . "</th>
						<th align=center >" . $_SESSION['lang']['debet'] . "</th>
						<th align=center >" . $_SESSION['lang']['kredit'] . "</th>
						<th align=center >" . $_SESSION['lang']['noreferensi'] . "</th>    
						<th align=center >" . $_SESSION['lang']['kodeblok'] . "</th>
						<th align=center >" . $_SESSION['lang']['tahuntanam'] . "</th>
						<th align=center >" . $_SESSION['lang']['kodekegiatan'] . "</th>
						<th align=center >" . $_SESSION['lang']['namakegiatan'] . "</th>
						<th align=center >" . $_SESSION['lang']['revisi'] . "</th>
					</tr>  
				</thead>
				<tbody>";
        $tdebet = $tkredit = 0;
        $str = "select a.*,b.namaakun,c.novoucher,c.cgttu from " . $dbname . ".keu_jurnaldt_vw a
				left join " . $dbname . ".keu_5akun b
				on a.noakun=b.noakun
				left join " . $dbname . ".keu_kasbankht c on a.noreferensi=c.notransaksi 
				where a.nodok='" . $kode . "' and a.nojurnal NOT LIKE '%CLSM%' and a.revisi<='0' 
				order by a.nojurnal, a.nourut";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $no += 1;
            $debet = 0;
            $kredit = 0;
            if ($bar->jumlah > 0)
                $debet = $bar->jumlah;
            else
                $kredit = $bar->jumlah * -1;

            $stream .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td>" . $bar->nojurnal . "</td>
						<td>" . $bar->kodejurnal . "</td>
						<td>" . $namajurnal[$bar->kodejurnal] . "</td>";

            $stream .= "<td>" . $nmauto[$bar->autojurnal] . "</td>";


            $stream .= "   <td>" . $bar->novoucher . "</td>
						<td >" . tanggalnormal($bar->tanggal) . "</td>
						<td align=center >" . $bar->kodeorg . "</td>
						<td>" . $bar->noakun . "</td>
						<td>" . $bar->namaakun . "</td>
						<td>" . $bar->cgttu . "</td>
						<td>" . $bar->keterangan . "</td>
						<td align=right  >" . number_format($debet, 2) . "</td>
						<td align=right  >" . number_format($kredit, 2) . "</td>
						<td align=center>" . $bar->noreferensi . "</td>    
						<td align=center>" . $bar->kodeblok . "</td>
						<td align=center>" . (isset($tahuntanam[$bar->kodeblok]) ? $tahuntanam[$bar->kodeblok] : '') . "</td>
						<td align=center>" . $bar->kodekegiatan . "</td>
						<td align=center>" . @$namakegiatan[$bar->kodekegiatan] . "</td>
						<td align=center >" . $bar->revisi . "</td>
						</tr>";
            $tdebet += $debet;
            $tkredit += $kredit;
        }
        $stream .= "<tr class=rowcontent>
					<td align=center colspan=12>Total</td>
					<td align=right >" . number_format($tdebet, 2) . "</td>
					<td align=right >" . number_format($tkredit, 2) . "</td>
					<td align=center colspan=6></td>
					</tr>";
        $stream .= "</tbody>
			<tfoot>
			</tfoot>		 
		</table>";

        $str = "select * from " . $dbname . ".sdm_daftarasset where kodeproject='" . $kode . "'";
        $res = fetchdata($str);
        if (count($res) > 0) {
            echo $tab;
        } else {
            echo $stream;
        }
        break;
    case 'searchnocapex':
        $tab = "<table>
            <tr>
                <td>Cari Capex</td>
                <td>:</td>
                <td>
                    <input type=text id=crcapex size=25 style=width:100px class=myinputtext>
                </td>
                <td>
                    <button onclick=caricapex() class=mybutton>" . $_SESSION['lang']['find'] . "</button>
                </td>
            </tr>
        </table><hr>";

        $tab .= "<div id='listnorequest'><table class=sortable border=0 cellspacing=1 cellpadding=5>
        <thead>
        <tr>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>Kode Capex</td>
            <td align=center>Nama Capex</td>
        </tr>
        </thead>
        <tbody>";

        $str = "select * from " . $dbname . ".spl_capexbangunan where posting='1' and kodeorg in (" . getOrgDetail(2) . ") and kode not in (select kodecapex from " . $dbname . ".project)";
        $res = fetchData($str);
        $no = 0;
        foreach ($res as $key => $val) {
            $expsub = explode('-', $val['kode']);
            $subtipeaset = substr($expsub[1], 0, 2);
            $no++;
            $tab .= "<tr class='rowcontent' style='cursor:pointer;' title='Show Detail' onclick=\"showdetail('" . $val['kode'] . "','" . $val['kodeorg'] . "','" . $subtipeaset . "','" . $val['subtipe'] . "','" . $val['jenis_biaya'] . "','" . $val['tipebg'] . "','" . $val['pekerjaan'] . "','" . $val['nama'] . "','" . tanggalnormal($val['tanggalmulai']) . "','" . tanggalnormal($val['tanggalselesai']) . "')\">";
            $tab .= "<td style='text-align:right'>" . $no . "</td>";
            $tab .= "<td>" . $val['kode'] . "</td>";
            $tab .= "<td>" . $val['nama'] . "</td>";
            $tab .= "</tr>";
        }
        $tab .= "</tbody>
        </table>
        </div>";
        echo $tab;
        break;

    case 'caricapex':
        $tab .= "<table class=sortable border=0 cellspacing=1 cellpadding=5>
        <thead>
        <tr>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>Kode Capex</td>
            <td align=center>Nama Capex</td>
        </tr>
        </thead>
        <tbody>";

        $str = "select * from " . $dbname . ".spl_capexbangunan where posting='1' and kodeorg in (" . getOrgDetail(2) . ") and (kode like '%" . $crcapex . "%' or nama like '%" . $crcapex . "%') and kode not in (select kodecapex from " . $dbname . ".project)";
        $res = fetchData($str);
        $no = 0;
        if (count($res) <= 0) {
            $tab .= "<tr class='rowcontent'>";
            $tab .= "<td colspan=3 style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</td>";
            $tab .= "</tr>";
        } else {
            foreach ($res as $key => $val) {
                $expsub = explode('-', $val['kode']);
                $subtipeaset = substr($expsub[1], 0, 2);
                $no++;
                $tab .= "<tr class='rowcontent' style='cursor:pointer;' title='Show Detail' onclick=\"showdetail('" . $val['kode'] . "','" . $val['kodeorg'] . "','" . $subtipeaset . "','" . $val['subtipe'] . "','" . $val['jenis_biaya'] . "','" . $val['tipebg'] . "','" . $val['pekerjaan'] . "','" . $val['nama'] . "','" . tanggalnormal($val['tanggalmulai']) . "','" . tanggalnormal($val['tanggalselesai']) . "')\">";
                $tab .= "<td style='text-align:right'>" . $no . "</td>";
                $tab .= "<td>" . $val['kode'] . "</td>";
                $tab .= "<td>" . $val['nama'] . "</td>";
                $tab .= "</tr>";
            }
        }
        $tab .= "</tbody>
        </table>";
        echo $tab;
        break;

    case 'getSub':

        $optSub = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $iSub = "select * from " . $dbname . ".sdm_5subtipeasset where kodetipe='" . $aset . "' ";
        $res = $owlPDO->query($iSub) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dSub =  $res->fetch()) {
            if ($_POST['sub'] == $dSub['kodesub']) {
                $select = "selected=selected";
            } else {
                $select = "";
            }
            $optSub .= "<option " . $select . " value='" . $dSub['kodesub'] . "'>" . $dSub['namasub'] . "</option>";
        }



        echo $optSub;



        break;

    case 'getjbiaya':
        $optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $optjb .= "<option value='2' selected=selected>Biaya Tidak Langsung</option>";
        // $optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        // $arjb = getEnum($dbname, 'project', 'jenis_biaya');
        // foreach ($arjb as $kei => $fal) {
        // if ((substr($unit,2,2)=='HO')&&($fal!=3)){
        // continue;
        // }

        // if ((substr($unit,2,2)!='HO')&&($fal==3)){
        // continue;
        // }

        // if ($fal==1){
        // $capt="Biaya Langsung";
        // }
        // if ($fal==2){
        // $capt="Biaya Tidak Langsung";
        // }
        // if ($fal==3){
        // $capt="Operasi";
        // }

        // if($jenisbiaya==$fal)
        // {
        // $optjb.="<option value='" . $kei . "' selected=selected>" . $capt . "</option>";
        // }
        // else{
        // $optjb.="<option value='" . $kei . "'>" . $capt . "</option>";
        // }
        // }
        echo $optjb;

        break;


    case 'update':
        $pjCek = ambilProyekAC($kode);
        if ($pjCek === null) {
            exit("Gagal: Proyek tidak ditemukan atau bukan unit Anda.");
        }
        if ($pjCek['posting'] == 1) {
            exit("Gagal: Proyek sudah diposting, tidak bisa diubah.");
        }
        // exit('error'.$aprv2);
        $str = "update " . $dbname . ".project set nama='" . $nama . "',
          tanggalmulai='" . $tanggalmulai . "',tanggalselesai='" . $tanggalselesai . "',
          updateby='" . $_SESSION['standard']['userid'] . "',subtipe='" . $sub . "',jenis_biaya='" . $jenisbiaya . "', jumlah='" . $jumlah . "',
          posisiasset='" . $posisiasset . "',tipelokasi='" . $tipelokasiasset . "',nomesin='" . $nomesin . "',norangka='" . $norangka . "',
          keterangan='" . $keterangan . "', tipemodel='" . $tipemodel . "',dgnapproval='" . $dgnapprvl . "'
          where kode='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        for ($i = 1; $i <= 4; $i++) {
            $aprv = checkPostGet('aprv' . $i, '');
            $strU = "update " . $dbname . ".project_approval set karyawanid='" . $aprv . "'where kode='" . $kode . "' and level='" . $i . "'";
            try {
                $owlPDO->exec($strU);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        break;

    case 'insert':
        $uOk = fetchData("select kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $unit . "' and kodeorganisasi in (" . getOrgDetail(2) . ")");
        if (count($uOk) == 0) {
            exit("Gagal: Unit tidak valid.");
        }
        // String Kode
        $kode = $jenis . '-' . $aset . $sub;

        // cari nomor terakhir
        $str = "select kode from " . $dbname . ".project where kode like '" . $kode . "%' order by substring(kode, -5) desc  limit 1";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $belakangnya = 0;
        while ($bar = $res->fetch()) {
            $belakangnya = intval(substr($bar->kode, -5));
        }
        $belakangnya += 1;

        $belakangnya = addZero($belakangnya, 10 - strlen($aset . $sub));
        $kode = $jenis . "-" . $aset . $sub . $belakangnya;
        $str = "insert into " . $dbname . ".project (kode, nama, tipe, tipebg,pekerjaan,kodeorg,tanggalmulai,tanggalselesai,updateby,subtipe,jenis_biaya,kodecapex,satuan,jumlah,keterangan,tipemodel,norangka,nomesin,posisiasset,tipelokasi,dgnapproval)
              values('" . $kode . "','" . $nama . "','" . $jenis . "','" . $tipebg . "','" . $pekerjaan . "','" . $unit . "','" . $tanggalmulai . "','" . $tanggalselesai . "'," . $_SESSION['standard']['userid'] . ",'" . $sub . "','" . $jenisbiaya . "','" . $kodecapex . "','" . $satuan . "','" . $jumlah . "','" . $keterangan . "','" . $tipemodel . "','" . $norangka . "','" . $nomesin . "','" . $posisiasset . "','" . $tipelokasiasset . "','" . $dgnapprvl . "')";
        try {
            $owlPDO->exec($str);
            if ($kodecapex != '') {
                $str = "select * from " . $dbname . ".spl_capexbangunandt where kodeproject='" . $kodecapex . "'";
                $res = fetchData($str);
                foreach ($res as $key => $val) {
                    $str2 = "select kegiatan from " . $dbname . ".project_dt order by kegiatan desc limit 1";
                    $res2 = fetchData($str2);
                    $newkode = addZero(($res2[0]['kegiatan'] + 1), 8);

                    $str3 = "insert into " . $dbname . ".project_dt (kegiatan,kodeproject,deskripsi,namakegiatan,tanggalmulai,tanggalselesai,satuan,volume,bobot) values ('" . $newkode . "','" . $kode . "','" . $val['deskripsikegiatan'] . "','" . $val['namakegiatan'] . "','" . $val['tanggalmulai'] . "','" . $val['tanggalselesai'] . "','" . $val['satuan'] . "','" . $val['volume'] . "','" . $val['bobot'] . "')";
                    try {
                        $owlPDO->exec($str3);

                        $str2 = "select * from " . $dbname . ".spl_capexbangunanmaterial where kodeproject='" . $kodecapex . "' and kodekegiatan='" . $val['kegiatan'] . "'";
                        $res2 = fetchData($str2);
                        foreach ($res2 as $key2 => $val2) {
                            $str3 = "insert into " . $dbname . ".project_material (kodeproject,kodekegiatan,kodebarang,jumlah,updateby) values ('" . $kode . "','" . $newkode . "','" . $val2['kodebarang'] . "','" . $val['jumlah'] . "','" . $_SESSION['standard']['userid'] . "')";
                            try {
                                $owlPDO->exec($str3);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        if ($dgnapprvl == '1') {
            for ($i = 1; $i <= 4; $i++) {

                $aprv = checkPostGet('aprv' . $i, '');
                if (trim((string)$aprv) != '') {
                    $stra = "INSERT INTO " . $dbname . ".`project_approval` (`kode`,`level`,`karyawanid`,`createby`)
                    values('" . $kode . "','" . $i . "','" . $aprv . "','" . $_SESSION['standard']['userid'] . "')";
                    try {
                        $owlPDO->exec($stra);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                    // for ($i=1; $i <5 ; $i++) {  

                    ##SIMPAN APPROVAL PROJECT
                    // $strapr="INSERT INTO ".$dbname.".`approval` (`notransaksi`,`jenispersetujuan`,`level`, `karyawanid`)
                    // values('".$kode."','PROJ','".$i."','".$aprv."')";
                    // try{$owlPDO->exec($strapr); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

                    // } 
                }
                // exit('error '.$aprv.$i);
            }
            $str = "update " . $dbname . ".project set statuspersetujuan='9' where kode='" . $kode . "'";
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        break;

    case 'delete':
        $pjCek = ambilProyekAC($kode);
        if ($pjCek === null) {
            exit("Gagal: Proyek tidak ditemukan atau bukan unit Anda.");
        }
        if ($pjCek['posting'] == 1) {
            exit("Gagal: Proyek sudah diposting, tidak bisa dihapus.");
        }
        $sGudang = "select * from " . $dbname . ".log_transaksi_vw where kodeblok = '" . $kode . "'";
        $res = $owlPDO->query($sGudang) or die(print " Gagal: " . PDOException::getMessage());
        $numrows = owlBaris($res);
        $cGudang = $numrows;

        $sKasBank = "select * from " . $dbname . ".keu_kasbankdt where kodeasset = '" . $kode . "'";
        $res = $owlPDO->query($sKasBank) or die(print " Gagal: " . PDOException::getMessage());
        $numrows = owlBaris($res);
        $cKasBank = $numrows;

        $sBaSpk = "select * from " . $dbname . ".log_baspk where kodeblok = '" . $kode . "'";
        $res = $owlPDO->query($sBaSpk) or die(print " Gagal: " . PDOException::getMessage());
        $numrows = owlBaris($res);
        $cBaSpk = $numrows;

        $sJurnal = "select * from " . $dbname . ".keu_jurnaldt_vw where kodeasset = '" . $kode . "'";
        $res = $owlPDO->query($sJurnal) or die(print " Gagal: " . PDOException::getMessage());
        $numrows = owlBaris($res);
        $cJurnal = $numrows;

        if ($cGudang > 0 || $cKasBank > 0 || $cBaSpk > 0 || $cJurnal > 0) {
            exit("Gagal: Item ini tidak dapat dihapus, sudah ada transaksi.");
        }

        $str = "delete from " . $dbname . ".project_material where kodeproject='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $str = "delete from " . $dbname . ".project_dt where kodeproject='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $str = "delete from " . $dbname . ".project_approval where kode='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $str = "delete from " . $dbname . ".project where kode='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loadData':
        // exit('warning : ');
        $where = filterCIP();
        $whrunit="";
        $whrunit = " and kodeorg in (".getOrgDetail(2).")";
        // if ($tglcr != '') {
        //     $where.=" and tanggal='" . $tglcr . "' ";
        // }

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0 or !is_numeric($page))
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $str = "select a.*,b.namakaryawan from " . $dbname . ".project a left join " . $dbname . ".datakaryawan b on a.updateby=b.karyawanid where 1=1 " . $where . $whrunit;
        // exit ('warning : '.$str);
        $res = fetchdata($str);
        $jlhbrs = count($res);
        if ($jlhbrs == 0) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td colspan=13 align=center>" . $_SESSION['lang']['dataempty'] . "</td>";
            $tab .= "</tr>";
        } else {
            $no = $maxdisplay;
            $str = "select a.*,b.namakaryawan,substr(kode,4,2) as aset from " . $dbname . ".project a left join " . $dbname . ".datakaryawan b on a.updateby=b.karyawanid  where 1=1 " . $where . $whrunit . " order by substring(kode, -7) desc limit " . $offset . "," . $limit . "";
            $tab = "";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar1 = $res->fetch()) {
                $kdAst = substr($bar1->kode, 3, 2);
                $iSubAst = "select * from " . $dbname . ".sdm_5subtipeasset where kodetipe='" . $kdAst . "' ";
                $resx = $owlPDO->query($iSubAst) or die(print " Gagal: " . PDOException::getMessage());
                $resx->setFetchMode(PDO::FETCH_ASSOC);
                $dSubAst = $resx->fetch();


                #= cek apakah jadi asset / jadi biaya
                $strdt = "select kodeasset from " . $dbname . ".sdm_daftarasset  where kodeproject='" . jsAC($bar1->kode) . "'";
                $resdt = fetchdata($strdt);
                @$kodeasset = $resdt[0]['kodeasset'];



                $stra = "select max(level) as level from " . $dbname . ".approval where  jenispersetujuan='PROJ' and karyawanid!='0000000000'";
                $resx = $owlPDO->query($stra) or die(print " Gagal: " . PDOException::getMessage());
                $resx->setFetchMode(PDO::FETCH_ASSOC);
                $barx = $resx->fetch();
                $countAppJ = $barx['level'];

                #= cek APPROVAL  




                #= ambil data nilai
                //  $sBiaya = "select distinct sum(jumlah) as biaya from " . $dbname . ".keu_jurnaldt
                //    where kodeasset='" . jsAC($bar1->kode) . "' and noakun like '1299%'";
                $sBiaya = "select distinct sum(jumlah) as biaya from " . $dbname . ".keu_jurnaldt
                   where kodeasset='" . jsAC($bar1->kode) . "' and noakun like '12903%'";
                $qBiaya = $owlPDO->query($sBiaya) or die(print " Gagal: " . PDOException::getMessage());
                $qBiaya->setFetchMode(PDO::FETCH_ASSOC);
                $rBiaya = $qBiaya->fetch();
                $tab .= "<tr class=rowcontent title='Show Detail Data'>
                <td onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . heAC($bar1->kode) . "</td>
                <td onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . heAC($bar1->kodeorg) . "</td>
                <td onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . heAC($bar1->tipe) . "</td>
                <td onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . heAC($bar1->nama) . "</td>
                <td onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . heAC($bar1->pekerjaan) . "</td>
                <td align=center  onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . heAC($bar1->satuan) . "</td>
                <td align=right  onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . @number_format($bar1->jumlah) . "</td>

                <td align=center  onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . tanggalnormal($bar1->tanggalmulai) . "</td>
                <td align=center  onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . tanggalnormal($bar1->tanggalselesai) . "</td>
                 <td  align=right  onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . number_format($rBiaya['biaya'], 2) . "</td>
                <td  onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . heAC($kodeasset) . "</td>
                <td  onclick=\"viewbiaya('" . jsAC($bar1->kode) . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . heAC($bar1->namakaryawan) . "</td>
                ";
                $namasts = array(1 => 'Approved', 0 => 'Waiting', 2 => 'Ditolak');

                // for ($i=1; $i <= 4; $i++) {  
                //     $stra = "select * from ".$dbname.".approval  where notransaksi='".$bar1->kode."' and level='".$i."' ";
                //     $resa=fetchdata($stra);
                //     $cekdata=count($resa);
                //     @$kryApr = $resa[0]['karyawanid'];  
                //     @$stsApr = $resa[0]['status'];  
                //     @$komentarApr = $resa[0]['komentar'];  
                //     @$tglApr = $resa[0]['tanggal'];  
                //     if ($kryApr!=0) { 
                //         $tab.="<td width=100px align=center> <b>".$nmKar[$kryApr]."</b><br/> <br/>".$tglApr."</td>";  
                //     } else {
                //         $tab.="<td width=50px align=center> <b> - </b> </td>"; 
                //     }
                // }  

                // for ($i=1; $i <= 4; $i++) {  
                $strb = "select * from " . $dbname . ".project_approval  where kode='" . jsAC($bar1->kode) . "' and level='1' ";
                $resa = fetchdata($strb);
                @$kryApr1 = $resa[0]['karyawanid'];

                $strb = "select * from " . $dbname . ".project_approval  where kode='" . jsAC($bar1->kode) . "' and level='2' ";
                $resa = fetchdata($strb);
                @$kryApr2 = $resa[0]['karyawanid'];

                $strb = "select * from " . $dbname . ".project_approval  where kode='" . jsAC($bar1->kode) . "' and level='3' ";
                $resa = fetchdata($strb);
                @$kryApr3 = $resa[0]['karyawanid'];

                $strb = "select * from " . $dbname . ".project_approval  where kode='" . jsAC($bar1->kode) . "' and level='4' ";
                $resa = fetchdata($strb);
                @$kryApr4 = $resa[0]['karyawanid'];

                // }   
                // echo $kryApr;
                //if(($bar1->posting==0 and $bar1->updateby==$_SESSION['standard']['userid']) || ($bar1->posting==0 and in_array($_SESSION['empl']['kodejabatan'],$postJabatan))){
                if ($bar1->posting == 0) {
                    $tab .= "<td width=25px align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . jsAC($bar1->kodeorg) . "','" . jsAC($bar1->aset) . "','" . jsAC($bar1->tipe) . "','" . jsAC($bar1->nama) . "','" . tanggalnormal($bar1->tanggalmulai) . "','" . tanggalnormal($bar1->tanggalselesai) . "','update','" . jsAC($bar1->kode) . "','" . jsAC($bar1->subtipe) . "','" . jsAC($bar1->jenis_biaya) . "','" . jsAC($bar1->tipebg) . "','" . jsAC($bar1->pekerjaan) . "','" . jsAC($bar1->kodecapex) . "','" . jsAC(strtoupper($bar1->satuan)) . "','" . jsAC($bar1->jumlah) . "','" . jsAC($bar1->keterangan) . "','" . jsAC($bar1->posisiasset) . "','" . jsAC($bar1->tipelokasi) . "','" . jsAC($bar1->nomesin) . "','" . jsAC($bar1->norangka) . "','" . jsAC($bar1->tipemodel) . "','" . jsAC($bar1->dgnapproval) . "','" . jsAC($bar1->statuspersetujuan) . "','" . jsAC($kryApr1) . "','" . jsAC($kryApr2) . "','" . jsAC($kryApr3) . "','" . jsAC($kryApr4) . "');\"></td>
                    <td width=25px align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"hapus('" . jsAC($bar1->kode) . "');\"></td>";

                    $tab .= "<td width=25px align=center> <img src=images/skyblue/posting.png class=zImgBtn  title='posting data " . heAC($bar1->nama) . "' onclick=\"postIni('" . jsAC($bar1->kode) . "','" . jsAC($bar1->kodeorg) . "','" . jsAC($bar1->jenis_biaya) . "','" . jsAC($bar1->posisiasset) . "');\"></td>";

                    $tab .= "<td width=25px align=center> <img src=images/nxbtn.png class=zImgBtn  title='Detail' onclick=\"detailForm('" . jsAC($bar1->kodeorg) . "','" . jsAC($bar1->aset) . "','" . jsAC($bar1->tipe) . "','" . jsAC($bar1->nama) . "','" . tanggalnormal($bar1->tanggalmulai) . "','" . tanggalnormal($bar1->tanggalselesai) . "','detail','" . jsAC($bar1->kode) . "','" . jsAC($bar1->subtipe) . "','" . jsAC($bar1->jenis_biaya) . "','" . jsAC($bar1->tipebg) . "','" . jsAC($bar1->pekerjaan) . "','" . jsAC($bar1->kodecapex) . "','" . jsAC($bar1->satuan) . "','" . jsAC($bar1->jumlah) . "','" . jsAC($bar1->keterangan) . "','" . jsAC($bar1->posisiasset) . "','" . jsAC($bar1->tipelokasi) . "','" . jsAC($bar1->nomesin) . "','" . jsAC($bar1->norangka) . "','" . jsAC($bar1->tipemodel) . "');\"></td>";
                } else {
                    // $tab.="<td width=25px align=center> <img src=images/zoom.png class=zImgBtn  title='View Detail Asset ".$bar1->nama."' onclick=\"viewasset('".$bar1->kode."');\"></td>";   
                    if ($bar1->posting == 1) {
                        $tab .= "<td width=25px align=center> </td>";
                        $tab .= "<td width=25px align=center><img src=images/zoom.png class=zImgBtn  title='View Detail Asset " . heAC($bar1->nama) . "' onclick=\"viewasset('" . jsAC($bar1->kode) . "');\"></td>";
                        $tab .= "<td width=25px align=center> <img src=images/skyblue/posted.png class=zImgBtn></td>";
                        $tab .= "<td width=25px align=center> </td>";
                    }
                    // $tab."<td width=25px align=center> <img src=images/skyblue/posting.png class=zImgBtn></td>"; 
                }
                // if (($bar1->statuspersetujuan==0)||($bar1->statuspersetujuan==2)) { 

                if ($bar1->dgnapproval == '1') {
                    $tab .= "<td width=25px align=center><img onclick=\"approval('" . jsAC($bar1->kode) . "','" . jsAC($bar1->kodeorg) . "','" . jsAC($bar1->jenis_biaya) . "')\" src=images/approve.png class=zImgBtn title='Approval'></td>";
                } else {
                    $tab .= "<td width=25px align=center> </td>";
                }
                // } else if ($bar1->posting==1){ 
                //     $tab.="<td width=25px align=center>  </td>";
                // }
                $tab .= "
                    <td width=25px align=center>
                        <img onclick=\"masterPDF('project','" . jsAC($bar1->kode) . "," . jsAC($bar1->updateby) . "','','vhc_slave_project_pdf',event);\" title=\"Print\" class=\"zImgBtn\" src=\"images/pdf.jpg\"></td>
					<td width=25px align=center><img onclick=\"excelMaterial(event,'" . jsAC($bar1->kode) . "')\" src=images/excel.jpg class=zImgBtn title='MS.Excel Material'></td>
					<td width=25px align=center><img onclick=\"timeFrame(event,'" . jsAC($bar1->kode) . "')\" src=images/excel.jpg class=zImgBtn title='MS.Excel Time Frame Project'>
            </td></tr>";
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
            $footd = "
                <tr><td colspan=30 align=center>
                <button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
                <button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
        }
        echo $tab . "####" . $footd;
        break;
    case 'excellist':
        include_once('lib/HtmlExcel.php');
        $terpotong = false;
        $data = dataCIP($terpotong);
        $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
        $judulKol = array('No', 'Kode Capex', 'Unit', 'Jenis', 'Nama', 'Int / Ext', 'Satuan', 'Jumlah', 'Tanggal Mulai', 'Sampai Tanggal', 'Nilai', 'Kode Asset', 'Status', 'Last Update', 'Update By');
        $kol = count($judulKol);
        $stream = "<table>
            <tr><td colspan=" . $kol . "><b>" . heAC($hdpt['nama']) . "</b></td></tr>
            <tr><td colspan=" . $kol . "><b>DAFTAR CONSTRUCTION IN PROGRESS (CIP)</b></td></tr>
            <tr><td colspan=" . $kol . ">" . heAC(infoFilterCIP() . ($terpotong ? ' | Catatan: ditampilkan 3000 proyek pertama' : '')) . "</td></tr>
            <tr><td colspan=" . $kol . ">Ditarik oleh " . heAC($_SESSION['empl']['name']) . " (" . heAC($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
            <tr><td colspan=" . $kol . ">&nbsp;</td></tr></table>";
        $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1><thead><tr>";
        foreach ($judulKol as $j) {
            $stream .= "<th align=center bgcolor=#CCCCCC>" . heAC($j) . "</th>";
        }
        $stream .= "</tr></thead><tbody>";
        $no = 0;
        $totNilai = 0;
        foreach ($data as $r) {
            $no++;
            $totNilai += $r['nilai'];
            $stream .= "<tr>
                <td align=center>" . $no . "</td>
                <td>" . heAC($r['kode']) . "</td>
                <td align=center>" . heAC($r['kodeorg']) . "</td>
                <td align=center>" . heAC($r['tipe']) . "</td>
                <td>" . heAC($r['nama']) . "</td>
                <td align=center>" . heAC($r['pekerjaan']) . "</td>
                <td align=center>" . heAC($r['satuan']) . "</td>
                <td align=right>" . round($r['jumlah'], 2) . "</td>
                <td align=center>" . tglCIP($r['tanggalmulai']) . "</td>
                <td align=center>" . tglCIP($r['tanggalselesai']) . "</td>
                <td align=right>" . round($r['nilai'], 2) . "</td>
                <td>" . heAC($r['kodeasset']) . "</td>
                <td align=center>" . ($r['posting'] == 1 ? 'Sudah Diposting' : 'Belum Diposting') . "</td>
                <td align=center>" . heAC($r['lastupdate']) . "</td>
                <td>" . heAC($r['namakaryawan']) . "</td>
            </tr>";
        }
        if ($no == 0) {
            $stream .= "<tr><td colspan=" . $kol . " align=center>Tidak ada data CIP untuk filter ini</td></tr>";
        } else {
            $stream .= "<tr><td colspan=10 align=right><b>TOTAL (" . $no . " proyek)</b></td><td align=right><b>" . round($totNilai, 2) . "</b></td><td colspan=4></td></tr>";
        }
        $stream .= "</tbody></table>";
        $xls = new HtmlExcel();
        $xls->addSheet("DaftarCIP", $stream);
        $xls->headers("DaftarCIP_" . date('YmdHis') . ".xls");
        echo $xls->buildFile();
        exit;
    case 'pdflist':
        require_once('lib/fpdf.php');
        $terpotong = false;
        $data = dataCIP($terpotong);
        $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
        if (!class_exists('PDFDaftarCIP')) {
            class PDFDaftarCIP extends FPDF
            {
                public $colW = array();
                public $judul = array();
                public $kopNama = '';
                public $kopLogo = '';
                public $kopInfo = '';
                public $kopDitarik = '';

                function Header()
                {
                    $lebar = $this->w - $this->lMargin - $this->rMargin;
                    if ($this->kopLogo != '' && file_exists($this->kopLogo)) {
                        $this->Image($this->kopLogo, $this->lMargin, $this->tMargin, 18);
                    }
                    $this->SetFont('Arial', 'B', 11);
                    $this->SetXY($this->lMargin + 22, $this->tMargin + 2);
                    $this->Cell(160, 6, utf8_decode($this->kopNama), 0, 1, 'L');
                    $this->SetY($this->tMargin + 15);
                    $this->SetFont('Arial', 'B', 13);
                    $this->Cell($lebar, 7, 'DAFTAR CONSTRUCTION IN PROGRESS (CIP)', 0, 1, 'C');
                    $this->SetFont('Arial', '', 8);
                    $this->Cell($lebar, 5, utf8_decode($this->kopInfo), 0, 1, 'C');
                    $this->Cell($lebar, 5, utf8_decode($this->kopDitarik), 0, 1, 'C');
                    $this->Ln(1);
                    $this->SetFillColor(220, 220, 220);
                    $this->SetFont('Arial', 'B', 7);
                    foreach ($this->judul as $i => $j) {
                        $this->Cell($this->colW[$i], 8, utf8_decode($j), 1, 0, 'C', true);
                    }
                    $this->Ln();
                }

                function Footer()
                {
                    $this->SetY(-12);
                    $this->SetFont('Arial', 'I', 7);
                    $this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
                }

                function fit($txt, $w, $f = 7)
                {
                    $txt = utf8_decode($txt);
                    $this->SetFont('Arial', '', $f);
                    if ($this->GetStringWidth($txt) <= $w - 2) {
                        return $txt;
                    }
                    while (strlen($txt) > 1 && $this->GetStringWidth($txt . '...') > $w - 2) {
                        $txt = substr($txt, 0, -1);
                    }
                    return rtrim($txt) . '...';
                }
            }
        }
        $pdf = new PDFDaftarCIP('L', 'mm', 'A4');
        $pdf->kopNama = $hdpt['nama'];
        $pdf->kopLogo = isset($hdpt['logo']) ? $hdpt['logo'] : '';
        $pdf->kopInfo = infoFilterCIP() . ($terpotong ? ' | 3000 proyek pertama' : '');
        $pdf->kopDitarik = 'Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s');
        $pdf->judul = array('No', 'Kode Capex', 'Unit', 'Jenis', 'Nama', 'Int / Ext', 'Satuan', 'Jumlah', 'Tgl Mulai', 'Sampai Tgl', 'Nilai', 'Kode Asset', 'Status');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(false, 16);
        $pdf->colW = array(8, 28, 12, 10, 60, 16, 14, 14, 20, 20, 28, 30, 17);
        $pdf->AddPage();
        $h = 6;
        $c = $pdf->colW;
        $no = 0;
        $totNilai = 0;
        foreach ($data as $r) {
            $no++;
            $totNilai += $r['nilai'];
            if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                $pdf->AddPage();
            }
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell($c[0], $h, $no, 1, 0, 'C');
            $pdf->Cell($c[1], $h, $pdf->fit($r['kode'], $c[1]), 1, 0, 'L');
            $pdf->Cell($c[2], $h, $pdf->fit($r['kodeorg'], $c[2]), 1, 0, 'C');
            $pdf->Cell($c[3], $h, $pdf->fit($r['tipe'], $c[3]), 1, 0, 'C');
            $pdf->Cell($c[4], $h, $pdf->fit($r['nama'], $c[4]), 1, 0, 'L');
            $pdf->Cell($c[5], $h, $pdf->fit($r['pekerjaan'], $c[5]), 1, 0, 'C');
            $pdf->Cell($c[6], $h, $pdf->fit($r['satuan'], $c[6]), 1, 0, 'C');
            $pdf->Cell($c[7], $h, number_format($r['jumlah']), 1, 0, 'R');
            $pdf->Cell($c[8], $h, tglCIP($r['tanggalmulai']), 1, 0, 'C');
            $pdf->Cell($c[9], $h, tglCIP($r['tanggalselesai']), 1, 0, 'C');
            $pdf->Cell($c[10], $h, number_format($r['nilai'], 2), 1, 0, 'R');
            $pdf->Cell($c[11], $h, $pdf->fit($r['kodeasset'], $c[11]), 1, 0, 'L');
            $pdf->Cell($c[12], $h, $r['posting'] == 1 ? 'Diposting' : 'Belum', 1, 1, 'C');
        }
        if ($no == 0) {
            $pdf->Cell(array_sum($c), $h, 'Tidak ada data CIP untuk filter ini', 1, 1, 'C');
        } else {
            if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                $pdf->AddPage();
            }
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetFillColor(238, 243, 250);
            $pdf->Cell(array_sum(array_slice($c, 0, 10)), $h, 'TOTAL (' . $no . ' proyek)', 1, 0, 'R', true);
            $pdf->Cell($c[10], $h, number_format($totNilai, 2), 1, 0, 'R', true);
            $pdf->Cell($c[11] + $c[12], $h, '', 1, 1, 'C', true);
        }
        $pdf->Output();
        exit;
    case 'detail':
        $sDet = "select distinct * from " . $dbname . ".project_dt  where kodeproject='" . $kode . "'";
        $resx = $owlPDO->query($sDet) or die(print " Gagal: " . PDOException::getMessage());
        $resx->setFetchMode(PDO::FETCH_ASSOC);
        $numrows = owlBaris($resx);
        $frmdt = "";
        if ($numrows == 0) {
            $frmdt .= "<tr class=rowcontent><td colspan=9>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        } else {
            while ($rDet = $resx->fetch()) {
                $frmdt .= "<tr class=rowcontent><td>" . heAC($rDet['kodeproject']) . "</td>";
                $frmdt .= "<td>" . heAC($rDet['deskripsi']) . "</td>";
                $frmdt .= "<td>" . heAC($rDet['namakegiatan']) . "</td>";
                $frmdt .= "<td>" . heAC($rDet['satuan']) . "</td>";
                $frmdt .= "<td align=right>" . heAC($rDet['volume']) . "</td>";
                $frmdt .= "<td align=right>" . heAC($rDet['bobot']) . "</td>";
                $frmdt .= "<td>" . tanggalnormal($rDet['tanggalmulai']) . "</td>";
                $frmdt .= "<td>" . tanggalnormal($rDet['tanggalselesai']) . "</td>";
                $frmdt .= "<td>
                <img src=images/zoom.png title='" . $_SESSION['lang']['find'] . "' id=tmblCariNoGudang class=zImgBtn onclick=tambahBarang('" . jsAC($rDet['kegiatan']) . "','" . jsAC($rDet['kodeproject']) . "','" . $_SESSION['lang']['find'] . "',event)>
                <img src=images/application/application_edit.png class=zImgBtn name='excapex2' title='Edit' onclick=\"editDet('" . tanggalnormal($rDet['tanggalmulai']) . "','" . tanggalnormal($rDet['tanggalselesai']) . "','updatedet','" . jsAC($rDet['kodeproject']) . "','" . jsAC($rDet['kegiatan']) . "','" . jsAC($rDet['deskripsi']) . "','" . jsAC($rDet['namakegiatan']) . "','" . jsAC($rDet['satuan']) . "','" . jsAC($rDet['volume']) . "','" . jsAC($rDet['bobot']) . "');\">
                <img src=images/application/application_delete.png class=zImgBtn name='excapex3' title='Delete' onclick=\"hapusData('" . jsAC($rDet['kegiatan']) . "');\">

                <img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30' title='Upload' onclick=\"showupload('" . $rDet['kodeproject'] . $rDet['kegiatan'] . "');\"></td>
                </tr>";
            }
        }
        echo $frmdt;

        break;

    case 'detailAK':
        $sDet = "select distinct * from " . $dbname . ".project_dt  where kodeproject='" . $kode . "'";
        $resx = $owlPDO->query($sDet) or die(print " Gagal: " . PDOException::getMessage());
        $resx->setFetchMode(PDO::FETCH_ASSOC);
        $numrows = owlBaris($resx);
        $frmdt = "";
        if ($numrows == 0) {
            $frmdt .= "<tr class=rowcontent><td colspan=9>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        } else {
            while ($rDet =  $resx->fetch()) {
                $frmdt .= "<tr class=rowcontent><td>" . heAC($rDet['kodeproject']) . "</td>";
                $frmdt .= "<td>" . heAC($rDet['no_mesin']) . "</td>";
                $frmdt .= "<td>" . heAC($rDet['no_rangka']) . "</td>";
                $frmdt .= "<td>" . heAC($rDet['tahun_produksi']) . "</td>";
                $frmdt .= "<td>" . heAC($rDet['tahun_prolehan']) . "</td>";
                $frmdt .= "<td>
                <img src=images/application/application_edit.png class=zImgBtn name='excapex2' title='Edit' onclick=\"editDetAK('" . jsAC($rDet['no_mesin']) . "','" . jsAC($rDet['no_rangka']) . "','updatedetAK','" . jsAC($rDet['kodeproject']) . "','" . jsAC($rDet['tahun_produksi']) . "','" . jsAC($rDet['tahun_prolehan']) . "','" . jsAC($rDet['kegiatan']) . "');\">
                <img src=images/application/application_delete.png class=zImgBtn name='excapex3' title='Delete' onclick=\"hapusDataAK('" . jsAC($rDet['kegiatan']) . "');\">
                </td></tr>";
            }
        }
        echo $frmdt;

        break;


    case 'insertDetail':
        proyekBolehUbahAC($kode);
        $tglMul = tanggalsystem(checkPostGet('tglMul', ''));
        $tglakh = tanggalsystem(checkPostGet('tglSmp', ''));

        $selisihTgl = selisihHariAC($tglMul, $tglakh);
        if ($selisihTgl === null) {
            exit("Error:Tanggal Mulai dan Tanggal Selesai wajib diisi");
        }
        if ($selisihTgl < 0) {
            exit("Error:Tanggal Selesai tidak boleh lebih kecil dari Tanggal Mulai");
        }
        cekRincianAC($_POST['nmKeg'], $deskripsi, $satKeg, $volKeg, $bobotKeg);
        $sInser = "insert into " . $dbname . ".project_dt (kodeproject,deskripsi, namakegiatan, tanggalmulai, tanggalselesai,satuan,volume,bobot)
             values ('" . $kode . "','" . $deskripsi . "','" . $_POST['nmKeg'] . "','" . tanggalsystem($_POST['tglMul']) . "','" . tanggalsystem($_POST['tglSmp']) . "','" . $satKeg . "','" . $volKeg . "','" . $bobotKeg . "')";
        //exit($sInser.'Error');
        try {
            $owlPDO->exec($sInser);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'insertDetailAK':
        proyekBolehUbahAC($kode);
        $norangka = checkPostGet('norangka', '');
        $nomesin = checkPostGet('nomesin', '');
        $tahunproduksi = checkPostGet('tahunproduksi', '');
        $tahunprolehan = checkPostGet('tahunprolehan', '');


        $sInser = "insert into " . $dbname . ".project_dt (kodeproject,no_mesin, no_rangka, tahun_produksi, tahun_prolehan)
             values ('" . $kode . "','" . $nomesin . "','" . $norangka . "','" . $tahunproduksi . "','" . $tahunprolehan . "')";
        try {
            $owlPDO->exec($sInser);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'updatedetAK':
        proyekBolehUbahAC(proyekDariKegiatanAC(checkPostGet('kegiatanx', '')));
        $norangka = checkPostGet('norangka', '');
        $nomesin = checkPostGet('nomesin', '');
        $tahunproduksi = checkPostGet('tahunproduksi', '');
        $tahunprolehan = checkPostGet('tahunprolehan', '');
        $kegiatanx = checkPostGet('kegiatanx', '');


        $sUpdate = "update " . $dbname . ".project_dt set no_mesin='" . $nomesin . "', no_rangka='" . $norangka . "',
              tahun_produksi='" . $tahunproduksi . "', tahun_prolehan='" . $tahunprolehan . "' where kegiatan='" . $kegiatanx . "'";
        try {
            $owlPDO->exec($sUpdate);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'updatedet':
        proyekBolehUbahAC(proyekDariKegiatanAC(checkPostGet('index', '')));
        $tglMul = tanggalsystem($_POST['tglMul']);
        $tglakh = tanggalsystem($_POST['tglSmp']);

        $selisihTgl = selisihHariAC($tglMul, $tglakh);
        if ($selisihTgl === null) {
            exit("Error:Tanggal Mulai dan Tanggal Selesai wajib diisi");
        }
        if ($selisihTgl < 0) {
            exit("Error:Tanggal Selesai tidak boleh lebih kecil dari Tanggal Mulai");
        }
        cekRincianAC($_POST['nmKeg'], $deskripsi, $satKeg, $volKeg, $bobotKeg);
        $sUpdate = "update " . $dbname . ".project_dt set deskripsi='" . $deskripsi . "', namakegiatan='" . $_POST['nmKeg'] . "',
              tanggalmulai='" . tanggalsystem($_POST['tglMul']) . "', tanggalselesai='" . tanggalsystem($_POST['tglSmp']) . "',
              satuan='" . $satKeg . "',volume='" . $volKeg . "',bobot='" . $bobotKeg . "' where kegiatan='" . $_POST['index'] . "'";
        try {
            $owlPDO->exec($sUpdate);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;




    case 'postingData':
        try {
            $owlPDO->beginTransaction();

            $pjCek = ambilProyekAC($kode);
            if ($pjCek === null) {
                throw new PDOException("Proyek tidak ditemukan atau bukan unit Anda.");
            }
            if ($pjCek['posting'] == 1) {
                throw new PDOException("Proyek ini sudah diposting.");
            }

            if ($param['alokasi'] == 'biaya' and $param['noakun'] == '') {
                throw new PDOException("Untuk alokasi biaya, nomor akun wajib terisi.");
            }

            #= cek periode akuntansi
            $sCek = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and periode='" . substr(tanggalsystemn($param['tglpost']), 0, 7) . "' and tutupbuku=1";
            $rCek = fetchData($sCek);
            if (count($rCek) == 1) {
                // exit('warning: '.$_SESSION['lang']['unit'].' '.$_SESSION['lang']['tutup']);
                throw new PDOException("Untuk " . $unit . " periode " . substr(tanggalsystemn($param['tglpost']), 0, 7) . " sudah tutup ");
            }


            #= update cek apakah dibuatkan spk
            #= buat perbandingan nilai total sum spkdt dengan sum baspk
            #= jika nilai baspk < spkdt maka exit error

            $str = "SELECT sum(hasilkerjajumlah) as jumspk from " . $dbname . ".log_spkdt where kodeblok='" . $kode . "'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar = $res->fetch();
            @$jumspk = $bar['jumspk'];

            $str = "SELECT sum(hasilkerjarealisasi) as jumba from " . $dbname . ".log_baspk where kodeblok='" . $kode . "' and statusjurnal=1";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar = $res->fetch();
            @$jumba = $bar['jumba'];

            // Parse Kode
            $detailKode = explode('-', $kode);
            $tipe = substr($detailKode[1], 0, 2);
            $sPt = "select induk,tipe from " . $dbname . ".organisasi where kodeorganisasi='" . $unit . "'"; #ambil PT sesuai dengan unit yang dipilih
            $rPt = fetchData($sPt);
            $kodeorg = $rPt[0]['induk'];
            $tipeOrganissi = $rPt[0]['tipe'];

            // Get Parameter Jurnal
            $qParam = selectQuery($dbname, 'keu_5parameterjurnal', "noakundebet, noakunkredit", "kodeaplikasi='PRJ' and jurnalid='PRJ" . $tipe . "'");
            $resParam = fetchData($qParam);
            // Get Header Project
            $qH = selectQuery($dbname, 'project', "*", "kode='" . $kode . "'");
            $resH = fetchData($qH);
            $subtipe = $resH[0]['subtipe'];
            $jenis_biaya = $resH[0]['jenis_biaya'];
            $jumlahasset = $resH[0]['jumlah'];

            require_once('lib/cekakun.php');
            if ($param['noakun'] != '') {
                cekakunkb($param['noakun'], '', '', $param['karyawanid'], '', '', '', '', '', '');
            }

            // echo"<pre>";
            // print_r($param);
            // throw new PDOException("xxxxx");
            // $str="select * from ".$dbname.".project_dt where kodeproject='".$kode."'";
            // // echo $str;exit("Error:A");
            // $res=fetchData($str);
            // $noz = 0;
            // $nomesin=array();
            // $norangka=array();
            // foreach($res as $key=>$val){   
            // if($val['no_mesin']!=''){
            // $noz+=1;
            // @$nomesin[$noz]=trim($val['no_mesin']);
            // @$norangka[$noz]=trim($val['no_rangka']);
            // }
            // }


            // if(@count($nomesin)=='0'){
            // $nomesin='';
            // }
            // if(@count($norangka)=='0'){
            // $norangka='';
            // }

            #cek periode akutansi lalu
            #jika project diposting di atas dari periode akuntasi maka dibuatkan jurnal akumulasinya secara otomatis
            #contoh : asset mobil di unit A seharusnya diakui pada tanggal 2017-08-15, tetapi pada kenyataannya terlupa sehingga baru akan diinput pada periode akuntasi bulan 12
            #maka terbentuk jurnal akumulasi diperiode berjalan dilihat dari bulan awal penyusutannya dikurangi 1 bulan periode aktif
            $sPrdLalu = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and tutupbuku=0 order by periode desc";
            $rPrdLalu = fetchData($sPrdLalu);
            $periodeAktif = $rPrdLalu[0]['periode'];
            $cekTglPost = explode("-", $_POST['tglpost']);
            $periodePost = $cekTglPost[2] . $cekTglPost[1];
            if (empty($resParam)) {
                throw new PDOException("Parameter Jurnal 'PRJ" . $tipe . "' belum ada\nSilahkan hubungi pihak IT");
            }
            if ($tipe != 'TM') {
                // Get Nilai Project
                $qNilai = "SELECT SUM(jumlah) as jumlah FROM " . $dbname . ".keu_jurnaldt
							WHERE kodeasset='" . $kode . "' and noakun='" . $resParam[0]['noakunkredit'] . "' and nojurnal not like '%PRJ%'";
                $resNilai = fetchData($qNilai);


                $qNilai2 = "SELECT SUM(jumlah) as jumlah FROM " . $dbname . ".keu_jurnaldt
							WHERE kodeasset='" . $kode . "' and noakun='" . $resParam[0]['noakundebet'] . "' and nojurnal like '%PRJ%' having sum(jumlah)>0";
                $resNilai2 = fetchData($qNilai2);
                if (count($resNilai2) > 0) {
                    throw new PDOException("Sudah ada pengakuan asset");
                }

                if (empty($resNilai)) {
                    throw new PDOException("Project belum direalisasi. Data tidak dapat di posting");
                } elseif ($resNilai[0]['jumlah'] == 0) {
                    throw new PDOException("Nilai Project tidak ada. Data tidak dapat di posting");
                }
                $nilaitotal = $resNilai[0]['jumlah'];
                if ($jumlahasset > 1) {
                    $nilai = floor($nilaitotal / $jumlahasset);
                    // $nilaimurni = $nilaitotal/$jumlahasset;
                }

                $nilaitotalfloor = $nilai * $jumlahasset;
                $selisih = $nilaitotal - $nilaitotalfloor;

                // echo $nilai._.$nilaitotal._.$nilaitotalfloor._.$selisih;exit("Error:A");

                for ($i = 1; $i <= $jumlahasset; $i++) {

                    if ($i == $jumlahasset) {
                        $nilai = $nilai + $selisih;
                    }

                    // Default Segment
                    $defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

                    /**
                     * Pendaftaran Asset
                     */
                    // Get Jumlah Bulan
                    $qSubTipe = selectQuery($dbname, 'sdm_5subtipeasset', "umurpenyusutan", "kodetipe='" . $tipe . "' and kodesub='" . $subtipe . "' and kodeorg='" . $unit . "'");
                    $resSubTipe = fetchData($qSubTipe);

                    if (empty($resSubTipe)) {
                        throw new PDOException("Sub Tipe " . $subtipe . " dari tipe asset " . $tipe . " belum terdaftar\nSilahkan hubungi IT");
                    }

                    // Kode Asset
                    $kodeAsset = $kodeorg . "-" . $tipe . $subtipe;
                    $qAsset = selectQuery(
                        $dbname,
                        'sdm_daftarasset',
                        "kodeasset",
                        "kodeasset like '" . $kodeAsset . "%' order by kodeasset desc limit 1"
                    );
                    $resAsset = fetchData($qAsset);
                    if (empty($resAsset)) {
                        $counterAsset = 1;
                    } else {
                        $counterAsset = substr($resAsset[0]['kodeasset'], 8, 6) + 1;
                    }
                    $kodeAsset .= str_pad($counterAsset, 6, '0', STR_PAD_LEFT);
                    /*
						0=tgl
						1=bulan
						2=tahun
						*/
                    #======================================================================#
                    # Jika Tgl < 15 maka bulan ini,
                    # Jika Tgl >= 15 maka bulan depan
                    #======================================================================#
                    // if(intval($cekTglPost[0])<15){
                    // 	$prdAwalPenyusutan=$cekTglPost[2]."-".$cekTglPost[1];
                    // }else{
                    // 	if(intval($cekTglPost[1])<10){
                    // 		$blnPnystan="0".(intval($cekTglPost[1])+1);
                    // 	}else{
                    // 		$blnPnystan=(intval($cekTglPost[1])+1);
                    // 	}
                    // 	if($cekTglPost[1]=='12'){
                    // 			$thnPnystan=(intval($cekTglPost[2])+1);
                    // 			$prdAwalPenyusutan=$thnPnystan."-01";
                    // 	}else{
                    // 		$prdAwalPenyusutan=$cekTglPost[2]."-".$blnPnystan;    
                    // 	}
                    // }
                    #=========================================================================#
                    # END
                    #=========================================================================#

                    #==========================================================================================#
                    # Kondisi Di Palma sesuai MOM
                    # Pemelihan menjadi naik asset tanggal 1-31 tetap bulan yang sama untuk penyusutannya
                    #==========================================================================================#
                    $prdAwalPenyusutan = $cekTglPost[2] . "-" . $cekTglPost[1];

                    $bulananpenyusutan = floor($nilai / $resSubTipe[0]['umurpenyusutan']);
                    if ($bulananpenyusutan == '') {
                        exit("Warning:Nilai bulanan salah");
                    }
                    // Data
                    $dataAsset = array(
                        'kodeorg'         => $unit,
                        'kodeasset'       => $kodeAsset,
                        'tipeasset'       => $tipe,
                        'tanggalperolehan' => tanggalsystemn($_POST['tglpost']),
                        'namasset'        => $namaaset,
                        'hargaperolehan'  => $nilai,
                        'jlhblnpenyusutan' => $resSubTipe[0]['umurpenyusutan'],
                        'keterangan'      => $resH[0]['keterangan'],
                        'awalpenyusutan'  => $prdAwalPenyusutan,
                        'user'            => $_SESSION['standard']['userid'],
                        'bulanan'         => $bulananpenyusutan,
                        'penambah'        => 0,
                        'pengurang'       => 0,
                        'nomesin'         => $param['nomesin'],
                        'norangka'        => $param['norangka'],
                        'kodeproject'     => $kode,
                        'subtipe'         => $subtipe,
                        'jenis_biaya'     => '2',
                        'posisiasset'     => $resH[0]['posisiasset'],
                        'tipelokasi'      => $resH[0]['tipelokasi'],
                        'tipemodel'       => $param['tipemodel']
                    );
                    // print_r($dataAsset);
                    // exit("Error:".$dataAsset);
                    $cols = array();
                    foreach ($dataAsset as $key => $row) {
                        $cols[] = $key;
                    }
                    $qIns = insertQuery($dbname, 'sdm_daftarasset', $dataAsset, $cols);
                    if ($param['alokasi'] == 'asset') {
                        #jika alokasi asset insert ke daftar asset, jika biaya abaikan
                        $owlPDO->exec($qIns);
                    }

                    /**
                     * Jurnal
                     */
                    # Get Journal Counter
                    $prd = substr(tanggalsystemn($param['tglpost']), 0, 7);
                    $queryJ = selectQuery(
                        $dbname,
                        'keu_5kelompokjurnal',
                        'nokounter',
                        "kodeunit='" . $param['unit'] . "' and " .
                            "kodekelompok='PRJ" . $tipe . "' and periode='" . $prd . "'"
                    );

                    $tmpKonter = fetchData($queryJ);
                    if (empty($tmpKonter)) {
                        // Jika Kelompok Jurnal belum ada, insert
                        $dataKel = array(
                            'kodeorg'     => $kodeorg,
                            'kodeunit'    => $param['unit'],
                            'kodekelompok' => "PRJ" . $tipe,
                            'periode'     => $prd,
                            'keterangan'  => "Project " . $tipe,
                            'nokounter'   => 0
                        );

                        $qKel = insertQuery($dbname, 'keu_5kelompokjurnal', $dataKel);
                        $owlPDO->exec($qKel);
                        $konter = '001';
                    } else {
                        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
                    }


                    #Prep tgl jurnal
                    #= revisi, jika periode pengakuan >= periode aktif maka prdposting = periode pengakuan
                    #= else, periode posting=periode aktif, dan akan terbentuk jurnal akumulasi

                    $tglpengakuan = tanggalsystemn($_POST['tglpost']);
                    $prdpengakuan = substr($tglpengakuan, 0, 7);
                    if ($prdpengakuan >= $periodeAktif) {
                        $prdposting = $prdpengakuan;
                    } else {
                        $prdposting = $periodeAktif;
                    }
                    $tglJurnal = $prdposting . "-" . substr($_POST['tglpost'], 0, 2);
                    if (intval(substr($_POST['tglpost'], 0, 2)) > 29) {
                        $tglJurnal = tglakhir($prdposting . "-29");
                    }
                    // $tanggal = date('Ymd');
                    $tanggal = $prdposting . substr($tglpengakuan, 7, 3);
                    $tanggal = str_replace('-', '', $tanggal);
                    # Prep No Jurnal
                    $nojurnal = $tanggal . "/" . $unit . "/PRJ" .
                        $tipe . "/" . $konter;

                    if ($param['alokasi'] == 'asset') {
                        $keterangan = "Jurnal aset dari WIP/CIP ke Fix Asset Project Kode:" . $kode . "; Nama:" . $resH[0]['nama'];
                    } elseif ($param['alokasi'] == 'biaya') {
                        $keterangan = "Jurnal Project ke Biaya, Project Kode:" . $kode . "; Nama:" . $resH[0]['nama'];
                        $resParam[0]['noakundebet'] = $param['noakun'];
                    } else {
                        throw new PDOException("Jenis alokasi salah.");
                    }

                    # Prep Header
                    $dataRes['header'] = array(
                        'nojurnal'     => $nojurnal,
                        'kodejurnal'   => 'PRJ' . $tipe,
                        'tanggal'      => $tglJurnal,
                        'tanggalentry' => date('Ymd'),
                        'posting'      => '0',
                        'totaldebet'   => '0',
                        'totalkredit'  => '0',
                        'amountkoreksi' => '0',
                        'noreferensi'  => $kode,
                        'autojurnal'   => '1',
                        'matauang'     => 'IDR',
                        'kurs'         => '1',
                        'revisi'       => '0'
                    );

                    $dataRes['detail'] = array();
                    $dataRes['detail'][0] = array(
                        'nojurnal'    => $nojurnal,
                        'tanggal'     => $tglJurnal,
                        'nourut'      => 1,
                        'noakun'      => $resParam[0]['noakundebet'],
                        'keterangan'  => $keterangan,
                        'jumlah'      => $nilai,
                        'matauang'    => 'IDR',
                        'kurs'        => '1',
                        'kodeorg'     => $unit,
                        'kodekegiatan' => '',
                        'kodeasset'   => $kode,
                        'kodebarang'  => '',
                        'nik'         => $param['karyawanid'],
                        'kodecustomer' => '',
                        'kodesupplier' => '',
                        'noreferensi' => $kode,
                        'noaruskas'   => '',
                        'kodevhc'     => '',
                        'nodok'       => $kode,
                        'kodeblok'    => '',
                        'revisi'      => '0',
                        'kodesegment' => $defSegment
                    );
                    $dataRes['detail'][1] = array(
                        'nojurnal'    => $nojurnal,
                        'tanggal'     => $tglJurnal,
                        'nourut'      => 2,
                        'noakun'      => $resParam[0]['noakunkredit'],
                        'keterangan'  => $keterangan,
                        'jumlah'      => (-1) * $nilai,
                        'matauang'    => 'IDR',
                        'kurs'        => '1',
                        'kodeorg'     => $unit,
                        'kodekegiatan' => '',
                        'kodeasset'   => $kode,
                        'kodebarang'  => '',
                        'nik'         => $param['karyawanid'],
                        'kodecustomer' => '',
                        'kodesupplier' => '',
                        'noreferensi' => $kode,
                        'noaruskas'   => '',
                        'kodevhc'     => '',
                        'nodok'       => $kode,
                        'kodeblok'    => '',
                        'revisi'      => '0',
                        'kodesegment' => $defSegment
                    );
                    #munculkan akumulasi penyusutan jika pengakuannya telat
                    if ($periodeAktif > $prdAwalPenyusutan and $param['alokasi'] == 'asset') {
                        #ambil kode penyusutan
                        if ($tipeOrganissi != 'HOLDING') {
                            $sAkun = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='DEP" . $tipe . "'";
                            $rAkun = fetchData($sAkun);
                        } else {
                            $sAkun = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='DPH" . $tipe . "'";
                            $rAkun = fetchData($sAkun);
                        }
                        $noaAkunDebet = $rAkun[0]['noakundebet'];
                        $noaAkunKredit = $rAkun[0]['noakunkredit'];
                        #jika biaya langsung dan pabrik ambil noaku dari parameter aplikasi
                        if ($tipeOrganissi == 'PABRIK') {
                            if ($_POST['jnsbiaya'] == '1') {
                                $sNoakun = "select * from " . $dbname . ".setup_parameterappl where kodeparameter='" . $tipe . "1'";
                                $rNoakun = fetchData($sNoakun);
                                $noaAkunDebet = $rNoakun[0]['nilai'];
                            }
                        }
                        $selisihBulan = datediff($prdAwalPenyusutan . "-01", $periodeAktif . "-01");
                        $dataRes['detail'][3] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tglJurnal,
                            'nourut' => 3,
                            'noakun' => $noaAkunDebet,
                            'keterangan' => "Akumulasi Penyusutan DEP" . $tipe . " dari bulan  " . $prdAwalPenyusutan . ";Unit :" . $unit,
                            'jumlah' => (($nilai / $resSubTipe[0]['umurpenyusutan']) * $selisihBulan['months']),
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => $kode,
                            'kodebarang' => '',
                            'nik' => $param['karyawanid'],
                            'kodecustomer' => '',
                            'kodesupplier' => '',
                            'noreferensi' => $kode,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $kode,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => $defSegment
                        );
                        $dataRes['detail'][4] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tglJurnal,
                            'nourut' => 4,
                            'noakun' => $noaAkunKredit,
                            'keterangan' => "Akumulasi Penyusutan DEP" . $tipe . " dari bulan  " . $prdAwalPenyusutan . ";Unit :" . $unit,
                            'jumlah' => (-1) * (($nilai / $resSubTipe[0]['umurpenyusutan']) * $selisihBulan['months']),
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => $kode,
                            'kodebarang' => '',
                            'nik' => $param['karyawanid'],
                            'kodecustomer' => '',
                            'kodesupplier' => '',
                            'noreferensi' => $kode,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $kode,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => $defSegment
                        );
                    }

                    $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                    $owlPDO->exec($queryH);
                    foreach ($dataRes['detail'] as $key => $dataDet) {
                        $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                        $owlPDO->exec($queryD);
                    }
                    #update konter nomor jurnal
                    $nokounterbaru = $tmpKonter[0]['nokounter'] + 1;
                    $str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $nokounterbaru . "' "
                        . " where kodeorg='" . $kodeorg . "' and kodeunit='" . $param['unit'] . "' and periode='" . $prd . "' and kodekelompok='PRJ" . $tipe . "' ";
                    $owlPDO->exec($str);
                } #tutup for
            } else {
                throw new PDOException("Parameter Jurnal 'DEP" . $tipe . "' belum ada\n" .
                    "Silahkan hubungi pihak IT");
            }

            $sPost = "update " . $dbname . ".project set updateby='" . $_SESSION['standard']['userid'] . "',posting='1',tanggalposting='" . tanggalsystemn($_POST['tglpost']) . "' where kode='" . $_POST['kode'] . "'";
            $owlPDO->exec($sPost);


            $nokounterbaru = $tmpKonter[0]['nokounter'] + 1;
            $str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $nokounterbaru . "' "
                . " where kodeorg='" . $kodeorg . "' and kodeunit='" . $param['unit'] . "' and periode='" . $prd . "' and kodekelompok='PRJ" . $tipe . "' ";
            $owlPDO->exec($str);

            #execute
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'saveApproval':

        $str = "update " . $dbname . ".project set statuspersetujuan='1' where kode='" . $kodeproject . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    // case 'saveApproval' : 

    //     $sdel="delete from ".$dbname.".approval where notransaksi='".$kodeproject."'";
    //     try{$owlPDO->exec($sdel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); } 

    //     for ($i=1; $i <5 ; $i++) { 

    //         $aprv=checkPostGet('aprv'.$i,'');
    //         if (@$aprv.$i!='')
    //         {
    //             $stra="INSERT INTO ".$dbname.".`approval` (`notransaksi`,`jenispersetujuan`,`level`, `karyawanid`)
    //             values('".$kodeproject."','PROJ','".$i."','".$aprv."')";
    //             try{$owlPDO->exec($stra); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    //         }
    //     }

    //     $str="update ".$dbname.".project set statuspersetujuan='9' where kode='".$kodeproject."'"; 
    //     try{$owlPDO->exec($str); }
    //     catch (PDOException $e) {
    //         print " Gagal  !: " . $e->getMessage() . "\n";
    //         die();
    //     } 
    // break;

    case 'editApproval':

        for ($i = 1; $i <= 4; $i++) {

            $aprv = checkPostGet('aprv' . $i, '');

            $str = "update " . $dbname . ".project_approval set karyawanid='" . $aprv . "' where kode='" . $kodeproject . "' and level='" . $i . "'";
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }


        break;

    case 'timeFrame':
        include_once('lib/HtmlExcel.php');
        $dHead = dataProyekAC($kode);
        if ($dHead['tanggalmulai'] == '0000-00-00' || $dHead['tanggalselesai'] == '0000-00-00' || $dHead['tanggalselesai'] < $dHead['tanggalmulai']) {
            exit("Warning : Tanggal mulai / selesai proyek belum sah, Time Frame tidak bisa dibuat.");
        }
        $arrTgl = rangeTanggal($dHead['tanggalmulai'], $dHead['tanggalselesai']);
        if (count($arrTgl) > 1000) {
            exit("Warning : Rentang proyek lebih dari 1000 hari, Time Frame tidak bisa dibuat.");
        }
        $namaBulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        #kelompok kolom per bulan untuk baris judul
        $grupBulan = array();
        foreach ($arrTgl as $t) {
            $kunci = substr($t, 0, 7);
            if (!isset($grupBulan[$kunci])) {
                $grupBulan[$kunci] = 0;
            }
            $grupBulan[$kunci]++;
        }
        $kolTetap = 4;
        $kol = $kolTetap + count($arrTgl);
        $stream = kopExcelProyekAC($dHead, 'TIME FRAME PROYEK CIP', $kol);
        $stream .= "<table border=1 cellspacing=0 cellpadding=2><thead>
            <tr>
                <th rowspan=2 align=center bgcolor=#CCCCCC>No</th>
                <th rowspan=2 align=center bgcolor=#CCCCCC width=260>Tahapan / Kegiatan</th>
                <th rowspan=2 align=center bgcolor=#CCCCCC width=75>Mulai</th>
                <th rowspan=2 align=center bgcolor=#CCCCCC width=75>Sampai</th>";
        foreach ($grupBulan as $kunci => $jml) {
            $stream .= "<th colspan=" . $jml . " align=center bgcolor=#CCCCCC>" . $namaBulan[(int)substr($kunci, 5, 2)] . " " . substr($kunci, 0, 4) . "</th>";
        }
        $stream .= "</tr><tr>";
        foreach ($arrTgl as $t) {
            $stream .= "<th align=center bgcolor=#CCCCCC width=22>" . (int)substr($t, 8, 2) . "</th>";
        }
        $stream .= "</tr></thead><tbody>";
        $kegs = fetchData("select namakegiatan,deskripsi,tanggalmulai,tanggalselesai from " . $dbname . ".project_dt where kodeproject='" . $kode . "' order by tanggalmulai, kegiatan");
        $no = 0;
        foreach ($kegs as $kg) {
            $no++;
            $namaKeg = trim($kg['namakegiatan']) != '' ? $kg['namakegiatan'] : $kg['deskripsi'];
            $sah = ($kg['tanggalmulai'] != '0000-00-00' && $kg['tanggalselesai'] != '0000-00-00');
            $stream .= "<tr>
                <td align=center>" . $no . "</td>
                <td>" . heAC($namaKeg) . "</td>
                <td align=center>" . ($kg['tanggalmulai'] == '0000-00-00' ? '-' : tanggalnormal($kg['tanggalmulai'])) . "</td>
                <td align=center>" . ($kg['tanggalselesai'] == '0000-00-00' ? '-' : tanggalnormal($kg['tanggalselesai'])) . "</td>";
            foreach ($arrTgl as $t) {
                $aktif = ($sah && $t >= $kg['tanggalmulai'] && $t <= $kg['tanggalselesai']);
                $stream .= $aktif ? "<td bgcolor=#1F4E79>&nbsp;</td>" : "<td>&nbsp;</td>";
            }
            $stream .= "</tr>";
        }
        if ($no == 0) {
            $stream .= "<tr><td colspan=" . $kol . " align=center>Belum ada rincian kegiatan untuk proyek ini</td></tr>";
        }
        $stream .= "</tbody></table>";
        $xls = new HtmlExcel();
        $xls->addSheet("TimeFrame", $stream);
        $xls->headers("TimeFrame_" . $dHead['kode'] . "_" . date('YmdHis') . ".xls");
        echo $xls->buildFile();
        exit;

    case 'hpsDetailAK':
        proyekBolehUbahAC(proyekDariKegiatanAC(checkPostGet('index', '')));
        $sdel = "delete from " . $dbname . ".project_dt where kegiatan='" . $_POST['index'] . "'";
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'excelMaterial':
        include_once('lib/HtmlExcel.php');
        $dHead = dataProyekAC($kode);
        $listKdBrg = array();
        $jmlRab = array();
        $jmlPakai = array();
        foreach (fetchData("select kodebarang, sum(jumlah) as jml from " . $dbname . ".project_material where kodeproject='" . $kode . "' group by kodebarang") as $r) {
            $listKdBrg[$r['kodebarang']] = $r['kodebarang'];
            $jmlRab[$r['kodebarang']] = $r['jml'];
        }
        foreach (fetchData("select kodebarang, sum(jumlah) as jml from " . $dbname . ".log_transaksi_vw where kodeblok='" . $kode . "' and post='1' group by kodebarang") as $r) {
            $listKdBrg[$r['kodebarang']] = $r['kodebarang'];
            $jmlPakai[$r['kodebarang']] = $r['jml'];
        }
        ksort($listKdBrg);
        $kol = 7;
        $stream = kopExcelProyekAC($dHead, 'MATERIAL USAGE PROYEK CIP', $kol);
        $stream .= "<table border=1 cellspacing=0 cellpadding=3><thead><tr>
            <th align=center bgcolor=#CCCCCC width=40>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center bgcolor=#CCCCCC width=90>" . $_SESSION['lang']['kodebarang'] . "</th>
            <th align=center bgcolor=#CCCCCC width=320>" . $_SESSION['lang']['namabarang'] . "</th>
            <th align=center bgcolor=#CCCCCC width=60>" . $_SESSION['lang']['satuan'] . "</th>
            <th align=center bgcolor=#CCCCCC width=110>" . $_SESSION['lang']['penggunaan'] . " " . $_SESSION['lang']['project'] . "</th>
            <th align=center bgcolor=#CCCCCC width=110>" . $_SESSION['lang']['jumlahkeluargudang'] . "</th>
            <th align=center bgcolor=#CCCCCC width=90>" . $_SESSION['lang']['selisih'] . "</th>
        </tr></thead><tbody>";
        $no = 0;
        foreach ($listKdBrg as $kdBarang) {
            $no++;
            $rab = isset($jmlRab[$kdBarang]) ? $jmlRab[$kdBarang] : 0;
            $pakai = isset($jmlPakai[$kdBarang]) ? $jmlPakai[$kdBarang] : 0;
            $stream .= "<tr>
                <td align=center>" . $no . "</td>
                <td>" . heAC($kdBarang) . "</td>
                <td>" . heAC(isset($nmBrg[$kdBarang]) ? $nmBrg[$kdBarang] : '') . "</td>
                <td align=center>" . heAC(isset($satBrg[$kdBarang]) ? $satBrg[$kdBarang] : '') . "</td>
                <td align=right>" . round($rab, 2) . "</td>
                <td align=right>" . round($pakai, 2) . "</td>
                <td align=right>" . round($rab - $pakai, 2) . "</td>
            </tr>";
        }
        if ($no == 0) {
            $stream .= "<tr><td colspan=" . $kol . " align=center>Belum ada material pada proyek ini</td></tr>";
        }
        $stream .= "</tbody></table>";
        $xls = new HtmlExcel();
        $xls->addSheet("MaterialUsage", $stream);
        $xls->headers("MaterialUsage_" . $dHead['kode'] . "_" . date('YmdHis') . ".xls");
        echo $xls->buildFile();
        exit;

    case 'hpsDetail':
        $kodePjHps = proyekDariKegiatanAC(checkPostGet('index', ''));
        proyekBolehUbahAC($kodePjHps);
        $owlPDO->exec("delete from " . $dbname . ".project_material where kodeproject='" . $kodePjHps . "' and kodekegiatan='" . checkPostGet('index', '') . "'");
        $sdel = "delete from " . $dbname . ".project_dt where kegiatan='" . $_POST['index'] . "'";
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'saveFormBarang':
        $i = "INSERT INTO " . $dbname . ".`project_material` (`kodeproject`, `kodekegiatan`, `kodebarang`, `jumlah`, `updateby`)
                    values('" . $kodeproject . "','" . $kodekegiatan . "','" . $kodeBarangForm . "','" . $jumlahBarangForm . "','" . $_SESSION['standard']['userid'] . "')";
        try {
            $owlPDO->exec($i);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;


    case 'deleteMaterial':
        //exit("Error:hahaha");
        $i = "DELETE FROM " . $dbname . ".`project_material` WHERE `kodeproject` = '" . $kodeproject . "' AND `kodekegiatan` = '" . $kegiatan . "' AND `kodebarang`= '" . $kodebarang . "'";
        try {
            $owlPDO->exec($i);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'getListBarang':
        $str = "select kodecapex from " . $dbname . ".project where kode='" . $kodeproject . "'";
        $res = fetchData($str);
        $kodecapex = $res[0]['kodecapex'];
        if ($kodecapex == '') {
            $showhide = '';
        } else {
            $showhide = 'none';
        }
        echo "
                    <fieldset style='display:" . $showhide . "'>
                    <legend>" . $_SESSION['lang']['form'] . "</legend>
                            <fieldset style='float:left;display:" . $showhide . "' >
                                    <legend>" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "</legend>
                                            <table cellspacing=1 border=0 class=data>

                                                    <tr>
                                                            <td colspan=2>" . $_SESSION['lang']['namabarang'] . "</td>

                                                            <td colspan=5>:
                                                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                                                    <button class=mybutton onclick=cariListBarang('" . $kegiatan . "','" . $kodeproject . "')>" . $_SESSION['lang']['find'] . "</button>
                                                            <td>
                                                    <tr>
                                                    </table>

                                                    <table id=listCariBarang >
                                                    <thead>
                                                    <tr class=rowheader>
                                                            <td>No</td>
                                                            <td>" . $_SESSION['lang']['kodebarang'] . "</td>
                                                            <td>" . $_SESSION['lang']['namabarang'] . "</td>
                                                            <td>" . $_SESSION['lang']['satuan'] . "</td>
                                                    </tr></thead>";

        if ($namaBarangCari == '') {
        } else {
            $i = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang where namabarang like '%" . $namaBarangCari . "%'";
            $resw = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
            $resw->setFetchMode(PDO::FETCH_ASSOC);
            while ($d = $resw->fetch()) {
                $no += 1;
                echo "
                                                    <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('" . $d['kodebarang'] . "','" . $nmBrg[$d['kodebarang']] . "','" . $satBrg[$d['kodebarang']] . "');\">
                                                            <td>" . $no . "</td>
                                                            <td>" . $d['kodebarang'] . "</td>
                                                            <td>" . $nmBrg[$d['kodebarang']] . "</td>
                                                            <td>" . $satBrg[$d['kodebarang']] . "</td>
                                                    </tr>";
            }
        }
        echo "</table>
                                    </fieldset>


                                    <fieldset style='display:" . $showhide . "'>
                                    <legend>" . $_SESSION['lang']['form'] . "</legend>
                                            <table cellspacing=1 border=0>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['project'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=kodeproject disabled value='" . $kodeproject . "' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['kodekegiatan'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=kodekegiatan disabled value='" . $kegiatan . "' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['kodebarang'] . "</td>
                                                            <td>:</td>
                                                            <td>
                                                                    <input type=text id=kodeBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                                            </td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['namabarang'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=namaBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['satuan'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=satuanBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['jumlah'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=jumlahBarangForm class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>

                                                    <tr>
                                                            <td>
                                                                    <button class=mybutton onclick=saveFormBarang('" . $kegiatan . "','" . $kodeproject . "','" . $_SESSION['lang']['find'] . "',event)>" . $_SESSION['lang']['save'] . "</button>
                                                                    <button class=mybutton onclick=cancelFormBarang('" . $kegiatan . "','" . $kodeproject . "','" . $_SESSION['lang']['find'] . "',event)>" . $_SESSION['lang']['delete'] . "</button>
                                                                    <button class=mybutton onclick=closeDialog()>" . $_SESSION['lang']['selesai'] . "</button>
                                                            </td>
                                                    </tr>
                                            </table>
                                    </fieldset>
                            </fieldset>

            <fieldset>
            <legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
            <table cellspacing=1 border=0 class=data>
            <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>" . $_SESSION['lang']['project'] . "</td>
                            <td>" . $_SESSION['lang']['namakegiatan'] . "</td>
                            <td>" . $_SESSION['lang']['kodebarang'] . "</td>
                            <td>" . $_SESSION['lang']['namabarang'] . "</td>
                            <td>" . $_SESSION['lang']['jumlah'] . "</td>
                            <td>" . $_SESSION['lang']['satuan'] . "</td>
                            <td>" . $_SESSION['lang']['dibuat'] . "</td>
                            <td>" . $_SESSION['lang']['action'] . "</td>
                    </tr>
            </thead>
            </tbody>";

        $i = "select * from " . $dbname . ".project_material where kodekegiatan='" . $kegiatan . "'";
        $res1 = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        $noData = 0;
        while ($d = $res1->fetch()) {
            $noData += 1;
            echo "
                    <tr class=rowcontent>
                            <td>" . $noData . "</td>
                            <td>" . $d['kodeproject'] . "</td>
                            <td>" . $optNmKegBrg[$d['kodekegiatan']] . "</td>
                            <td>" . $d['kodebarang'] . "</td>
                            <td>" . $nmBrg[$d['kodebarang']] . "</td>
                            <td align=right>" . $d['jumlah'] . "</td>
                            <td>" . $satBrg[$d['kodebarang']] . "</td>
                            <td>" . $nmKar[$d['updateby']] . "</td>

                            <td>
                                    <img src=images/application/application_delete.png class=zImgBtn style='display:" . $showhide . "' caption='Delete'
                                    onclick=\"delMaterial('" . $d['kodeproject'] . "','" . $d['kodekegiatan'] . "','" . $d['kodebarang'] . "');\">
                            </td>
                    </tr>";
        }
        echo "</table></fieldset>";

        break;
    case 'showformApproval':

        // $str="SELECT a.*,b.* FROM ".$dbname.".datakaryawan a LEFT JOIN ".$dbname.".sdm_5jabatan b ON a.kodejabatan=b.kodejabatan where a.lokasitugas in(select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional=subregional ) 
        // and b.namajabatan like '%manager%' and tanggalkeluar='0000-00-00' ";
        $str = "SELECT karyawanid,namakaryawan from " . $dbname . ".datakaryawan where tipekaryawan='0' and (tanggalkeluar!= '0000-00-00' or tanggalkeluar < " . date('Y-m-d') . ") and statuskaryawan!='kontrak' and statuskaryawan!='keluar' and namakaryawan not like 'ADMINISTRATOR%' ";
        $optPersetujuan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($rstr = $res->fetch()) {
            $optPersetujuan .= "<option value=" . $rstr['karyawanid'] . ">" . $rstr['namakaryawan'] . "</option>";
        }

        $str = "select * from " . $dbname . ".project where kode='" . $_POST['kodeproject'] . "'";
        $res = fetchData($str);
        $stspersetujuan = $res[0]['statuspersetujuan'];

        // if ($res[0]['statuspersetujuan']=='0') {
        // $tab="<table>";
        // $tab.="<tr>
        //         <td>".$_SESSION['lang']['kode']."</td>
        //         <td>:</td>
        //         <td><input type=text id=kode onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:150px;\" value='".$_POST['kodeproject']."' disabled=disabled /></td>
        //     </tr>";

        // for ($i=1; $i <=4 ; $i++) {  
        //     $tab.="<tr>
        //     <td>".$_SESSION['lang']['persetujuan']." ".$i."</td>
        //     <td>:</td>
        //     <td><select id=aprv1 style=\"width:155px;\">" . $optPersetujuan . "</select></td>
        //     </tr>";
        // }
        // // <td><img id='aprv".$i." onclick=z.elSearch('aprv1',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td> 


        // $tab.="<tr>
        //         <td colspan=2>&nbsp;</td>
        //         <td><button class=mybutton onclick=editApproval('".$kodeproject."','aprv1')>".$_SESSION['lang']['edit']."</button></td>
        //     </tr>
        //     <tr>
        //         <td colspan=3><hr></td>
        //     </tr>

        // </table>";
        // }else{
        $tab .= "<h4>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan'] . "</h4>";
        $tab .= "<table class=sortable cellspacing=1 border=0>";
        $tab .= "<thead><tr class=rowheader>";
        $tab .= "<th>" . $_SESSION['lang']['nourut'] . "</th>";
        $tab .= "<th>" . $_SESSION['lang']['namakaryawan'] . "</th>";
        // $tab.="<th>".$_SESSION['lang']['status']."</th>";
        // $tab.="<th>Komentar</th>";
        $tab .= "</tr></thead><tbody>";
        $arrkomen = array('0' => 'Menunggu Persetujuan', '1' => $_SESSION['lang']['disetujui'], '2' => $_SESSION['lang']['ditolak']);
        $str = "select * from " . $dbname . ".project_approval where kode='" . $_POST['kodeproject'] . "'";
        $res = fetchData($str);
        foreach ($res as $bar) {
            @$no++;
            $nmorang = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['karyawanid'] . "'");
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=left>" . @$nmorang[$bar['karyawanid']] . "</td>";
            // $tab.="<td align=center>".$arrkomen[$bar['status']]."</td>";
            // $tab.="<td align=center>".$bar['komentar']."</td>";
            $tab .= "</tr>";
        }
        if ($stspersetujuan != 1) {
            $tab .= "<tr>
              
                <td><button class=mybutton onclick=saveApproval('" . $kodeproject . "')>" . $_SESSION['lang']['save'] . " Approval</button></td>
                </tr>";
        }
        $tab .= "</table>";
        // }

        echo $tab;
        break;

    case 'showform':
        proyekBolehUbahAC(checkPostGet('kodeproject', ''));
        $res = array('asset' => 'Naikkan Menjadi Asset', 'biaya' => 'Alokasi Sebagai Biaya');
        $optjenis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        foreach ($res as $key => $val) {
            $optjenis .= "<option value=" . $key . ">" . $val . "</option>";
        }
        $opttipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $param['kodeorg'] . "'");

        $wh = "";
        if ($opttipe[$param['kodeorg']] == 'KEBUN' or $opttipe[$param['kodeorg']] == 'PABRIK') {
            $wh = " and (substr(noakun,1,1) in ('7')";
        } elseif ($opttipe[$param['kodeorg']] == 'BULKING') {
            $wh = " and (substr(noakun,1,2) in ('81')";
        } else {
            $wh = " and (substr(noakun,1,2) in ('82')";
        }


        $wh .= " or substr(noakun,1,5) in ('12911') or substr(noakun,1,5) in ('12902') or substr(noakun,1,5) in ('12101') or noakun in ('1160103')) and detail=1 and length(noakun)='7'";

        #PABRIK => %63%, 7%
        #KANWIL => 82%
        #RND => 82%
        #TC => 82%
        #BULKING => 81%

        // $str="select * from ".$dbname.".project where kode='".$_POST['kodeproject']."'";
        // $res=fetchData($str);
        // if ($res[0]['dgnapproval']=='1') { 
        //     if ($res[0]['statuspersetujuan']!='1') {  
        //         exit("<b>Maaf, Kode Project ini masih belum diajukan atau dalam tahap pengajuan </b>"); 
        //     }
        // }
        $sjnskrj = "select * from " . $dbname . ".keu_5akun where length(noakun) in ('7','8') and namaakun not like '%NON AKTIF%' " . $wh . " order by noakun asc";
        $optJnsKerja = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $res = $owlPDO->query($sjnskrj) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($rjnskrj = $res->fetch()) {
            $d = substr($rjnskrj['noakun'], 0, 5);
            if ($d != $n) {
                $nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $d . "'");
                $optJnsKerja .= "<optgroup label='" . $nmorg[$d] . "'>";
            }
            $optJnsKerja .= "<option value=" . $rjnskrj['noakun'] . ">" . $rjnskrj['noakun'] . " - " . $rjnskrj['namaakun'] . "</option>";
            $n = $d;
            if ($d != $n) {
                $optJnsKerja .= "</optgroup>";
            }
        }


        $whereKary = "";
        $whereKary = " and tipekaryawan in ('0','1','4') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date('Y-m-d') . "')";
        $str = "select * from " . $dbname . ".datakaryawan where lokasitugas='" . $param['kodeorg'] . "' " . $whereKary . " order by namakaryawan asc";
        $res = fetchData($str);
        $optkary = "<option value=''>&nbsp;</option>";
        foreach ($res as $val) {
            $optkary .= "<option value=" . $val['karyawanid'] . ">" . $val['nik'] . " - " . $val['namakaryawan'] . "</option>";
        }


        #= ambil nama default
        $str = "select * from " . $dbname . ".project where kode='" . $_POST['kodeproject'] . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $namaaset = $bar['nama'];
            $norangka = $bar['norangka'];
            $nomesin = $bar['nomesin'];
            $posisiasset = $bar['posisiasset'];
            $tipelokasi = $bar['tipelokasi'];
            $tipemodel = $bar['tipemodel'];
        }


        #akun yang dipakai bila dinaikkan menjadi aset: dari Parameter Jurnal PRJ + kode tipe project (sama dengan yang dipakai saat posting)
        $dkPost = explode('-', (string)$_POST['kodeproject']);
        $tipePost = substr(isset($dkPost[1]) ? $dkPost[1] : '', 0, 2);
        $rPrm = fetchData("select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where kodeaplikasi='PRJ' and jurnalid='PRJ" . $tipePost . "'");
        if (isset($rPrm[0])) {
            $nmAkunPrm = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun in ('" . $rPrm[0]['noakundebet'] . "','" . $rPrm[0]['noakunkredit'] . "')");
            $barisAkunAset = "<tr id=rowakunasset1 style=display:none>
                <td class=lbl>Akun Aset (Debet)</td>
                <td class=sep>:</td>
                <td><b>" . heAC($rPrm[0]['noakundebet'] . " - " . (isset($nmAkunPrm[$rPrm[0]['noakundebet']]) ? $nmAkunPrm[$rPrm[0]['noakundebet']] : '')) . "</b></td>
            </tr>
            <tr id=rowakunasset2 style=display:none>
                <td class=lbl>Akun CIP (Kredit)</td>
                <td class=sep>:</td>
                <td><b>" . heAC($rPrm[0]['noakunkredit'] . " - " . (isset($nmAkunPrm[$rPrm[0]['noakunkredit']]) ? $nmAkunPrm[$rPrm[0]['noakunkredit']] : '')) . "</b></td>
            </tr>";
        } else {
            $barisAkunAset = "<tr id=rowakunasset1 style=display:none>
                <td class=lbl>Akun Aset</td>
                <td class=sep>:</td>
                <td style='color:#b42318'>Parameter Jurnal PRJ" . heAC($tipePost) . " belum ada, posting aset tidak bisa dilakukan. Hubungi pihak IT.</td>
            </tr>
            <tr id=rowakunasset2 style=display:none><td colspan=3></td></tr>";
        }

        $tglPosting = date('d-m-Y');
        $fw = "style='width:100%;box-sizing:border-box;'";
        $kodePost = heAC($_POST['kodeproject']);
        $tab = "<style>
            #tbPostCIP {width:100%;max-width:760px;border-collapse:collapse}
            #tbPostCIP td {padding:3px 4px;vertical-align:middle}
            #tbPostCIP td.lbl {width:150px;white-space:nowrap}
            #tbPostCIP td.sep {width:12px;text-align:center}
            #tbPostCIP tr.judul td {font-weight:bold;padding-top:12px;border-bottom:1px solid #9bb3d0}
            #tbPostCIP .select2-container {width:100% !important}
            #infoPostCIP {max-width:760px;margin-top:12px;padding:7px 10px;background:#f4f8fd;border:1px solid #c5d5e8;font-size:12px}
        </style>";
        $tab .= "<table id=tbPostCIP>
            <tr class=judul><td colspan=3>Data Aset</td></tr>
            <tr>
                <td class=lbl>" . $_SESSION['lang']['kode'] . "</td><td class=sep>:</td>
                <td><input type=text id=kodeposting class=myinputtext " . $fw . " value='" . $kodePost . "' disabled=disabled /></td>
            </tr>
            <tr>
                <td class=lbl>" . $_SESSION['lang']['nama'] . "</td><td class=sep>:</td>
                <td><input type=text id=namaasetposting onkeypress=\"return tanpa_kutip(event);\" class=myinputtext " . $fw . " value='" . heAC($namaaset) . "' /></td>
            </tr>
            <tr>
                <td class=lbl>" . $_SESSION['lang']['norangka'] . "</td><td class=sep>:</td>
                <td><input type=text id=norangkaposting onkeypress=\"return tanpa_kutip(event);\" class=myinputtext " . $fw . " value='" . heAC($norangka) . "' /></td>
            </tr>
            <tr>
                <td class=lbl>" . $_SESSION['lang']['nomesin'] . "</td><td class=sep>:</td>
                <td><input type=text id=nomesinposting onkeypress=\"return tanpa_kutip(event);\" class=myinputtext " . $fw . " value='" . heAC($nomesin) . "' /></td>
            </tr>
            <tr>
                <td class=lbl>" . $_SESSION['lang']['tipemodel'] . "</td><td class=sep>:</td>
                <td><input type=text id=tipemodelposting onkeypress=\"return tanpa_kutip(event);\" class=myinputtext " . $fw . " value='" . heAC($tipemodel) . "' /></td>
            </tr>
            <tr class=judul><td colspan=3>Posting</td></tr>
            <tr>
                <td class=lbl>" . $_SESSION['lang']['tanggal'] . " <span style='color:#c0392b'>*</span></td><td class=sep>:</td>
                <td><input type=text class=myinputtext readonly id=tglpost onmousemove=setCalendar(this.id) onkeypress=return false; size=10 maxlength=10 style='width:110px;' value='" . $tglPosting . "'/></td>
            </tr>
            <tr>
                <td class=lbl>" . $_SESSION['lang']['alokasi'] . " <span style='color:#c0392b'>*</span></td><td class=sep>:</td>
                <td><select id=alokasi class=select2 onchange=showhide(this.value); style='width:100%;'>" . $optjenis . "</select></td>
            </tr>
            " . $barisAkunAset . "
            <tr id=rowakun style=display:none>
                <td class=lbl>" . $_SESSION['lang']['noakun'] . " <span style='color:#c0392b'>*</span></td><td class=sep>:</td>
                <td><select id=noakun class=select2 style='width:100%;'>" . $optJnsKerja . "</select></td>
            </tr>
            <tr id=rowkary style=display:none>
                <td class=lbl>" . $_SESSION['lang']['namakaryawan'] . "</td><td class=sep>:</td>
                <td><select id=karyawanid class=select2 style='width:100%;'>" . $optkary . "</select></td>
            </tr>
            <tr>
                <td colspan=2>&nbsp;</td>
                <td style='padding-top:8px;'><button class=mybutton onclick=\"savePosting('" . jsAC($_POST['kodeproject']) . "','" . jsAC($_POST['kodeorg']) . "','" . jsAC($_POST['kodeorg']) . "')\">" . $_SESSION['lang']['save'] . "</button></td>
            </tr>
        </table>
        <div id=infoPostCIP>Pemilihan naik menjadi asset, Tanggal 1 s/d 30 maka periode penyusutan masuk ke bulan yg sama.</div>";

        echo $tab;
        break;

    case 'changetipelokasi':
        $orgarr = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $_POST['posisiasset'] . "'");
        $optSub = "<option value=''>" . $_SESSION['empl']['pilihdata'] . "</option>";
        $tipelokasi = $orgarr[$_POST['posisiasset']];

        $optSub = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $iSub = "select * from " . $dbname . ".keu_5tipelokasiasset where tipelokasi='" . $tipelokasi . "' ";
        $res = $owlPDO->query($iSub) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dSub = $res->fetch()) {
            if ($_POST['lokasi'] == $dSub['kodelokasi']) {
                $select = "selected=selected";
            } else {
                $select = "";
            }
            $optSub .= "<option " . $select . " value='" . $dSub['kodelokasi'] . "'>" . $dSub['namalokasi'] . "</option>";
        }
        echo $optSub;
        break;

    default:
        break;
}
