<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$periode=checkPostGet('periode','');
$hariminggu=checkPostGet('hariminggu','');
$harilibur=checkPostGet('harilibur','');
$catatan=checkPostGet('catatan','');
$kodept=checkPostGet('kodept','');

#=========================================================================
# HKE per PT: tabel sdm_hke_periode (HKE per periode) dan sdm_hke_komponen (komponen dasar upah harian)
# Setup bisa diisi untuk PT mana pun yang boleh diakses pengguna, tetapi aturannya baru dipakai di transaksi untuk PT yang
# diaktifkan di hkePTDaftar() (lib/nangkoelib.php, saat ini hanya PPP) dan pengaktifannya (aktif + periode mulai) di setup ini. Tabel lama sdm_hk_efektif (laporan LBM) tidak diubah.
#=========================================================================
#PT harus ada di PT yang boleh diakses pengguna; keluar dengan pesan bila tidak
function aksesPT($kodept)
{
	global $dbname, $owlPDO;
	if (!preg_match('/^[A-Za-z0-9]{3}$/', (string)$kodept)) {
		exit("Warning: PT tidak valid.");
	}
	$pt = getOrgDetail(3);
	if (!is_array($pt) || !isset($pt[$kodept])) {
		exit("Warning: Anda tidak punya akses ke PT " . htmlspecialchars($kodept) . ".");
	}
	$stm = $owlPDO->prepare("select 1 from " . $dbname . ".organisasi where kodeorganisasi=? and tipe='PT' limit 1");
	$stm->execute(array($kodept));
	if ($stm->fetchColumn() === false) {
		exit("Warning: " . htmlspecialchars($kodept) . " bukan PT.");
	}
}

function periodeSah($p)
{
	return (bool)preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', (string)$p);
}

function hariBulanPT($p)
{
	return (int)date('t', strtotime($p . '-01'));
}

#payroll PT sudah diproses pada periode itu (exact) atau pada periode yang sama/sesudahnya ($sampaiLebih = true)
function payrollPTSudah($kodept, $periode, $sampaiLebih)
{
	global $dbname, $owlPDO;
	$op = $sampaiLebih ? '>=' : '=';
	$stm = $owlPDO->prepare("select 1 from " . $dbname . ".sdm_gaji where periodegaji" . $op . "? and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk=?) limit 1");
	$stm->execute(array($periode, $kodept));
	return $stm->fetchColumn() !== false;
}

#ada transaksi yang upahnya sudah dihitung dengan aturan HKE PT ini pada periode itu (exact) atau pada periode itu dan sesudahnya ($sampaiLebih = true).
#Yang dicek: absensi karyawan Bulanan PT ini yang sudah punya upah (sdm_absensidt.umr, termasuk absensi dari BKM) dan HK Traksi yang menyimpan upah per hari (vhc_runhk.upahharian).
#Hasil: teks sumber transaksi yang ditemukan (kosong bila tidak ada).
function transaksiHkePTAda($kodept, $periode, $sampaiLebih)
{
	global $dbname, $owlPDO;
	$awal = $periode . '-01';
	#"sampai lebih": dibatasi sampai akhir bulan depan, supaya baris bertanggal rusak jauh di depan (mis. 2046-00-00 dari cuti) tidak mengunci selamanya
	$akhir = $sampaiLebih ? date('Y-m-d', strtotime(date('Y-m-01') . ' +2 month')) : date('Y-m-d', strtotime($awal . ' +1 month'));
	$batas = ' and a.tanggal<?';
	$param = array($awal, $akhir);
	$unit = "select kodeorganisasi from " . $dbname . ".organisasi where induk=?";
	$ada = array();
	try {
		$stm = $owlPDO->prepare("select 1 from " . $dbname . ".sdm_absensidt a join " . $dbname . ".datakaryawan d on d.karyawanid=a.karyawanid
			where a.tanggal>=?" . $batas . " and a.umr>0 and d.sistemgaji='Bulanan' and d.lokasitugas in (" . $unit . ") limit 1");
		$stm->execute(array_merge($param, array($kodept)));
		if ($stm->fetchColumn() !== false) {
			$ada[] = 'absensi/BKM';
		}
	} catch (PDOException $e) {
		exit("Gagal memeriksa transaksi absensi.");
	}
	try {
		$stm = $owlPDO->prepare("select 1 from " . $dbname . ".vhc_runhk a join " . $dbname . ".vhc_runht b on b.notransaksi=a.notransaksi
			where a.tanggal>=?" . $batas . " and a.upahharian>0 and b.kodeorg in (" . $unit . ") limit 1");
		$stm->execute(array_merge($param, array($kodept)));
		if ($stm->fetchColumn() !== false) {
			$ada[] = 'HK Traksi';
		}
	} catch (PDOException $e) {
		exit("Gagal memeriksa transaksi Traksi (kolom vhc_runhk.upahharian belum ada? jalankan sql_hke_ppp_setup.sql).");
	}
	return implode(' dan ', $ada);
}

#transaksi yang upahnya terpengaruh bila komponen dasar upah yang berlaku mulai $mulai diubah, ditambah, atau dihapus.
#Komponen hanya berpengaruh saat HKE PT ini aktif, dan mulai dari periode terlambat antara $mulai dan periode mulai pengaktifan. Hasil: teks sumber transaksi (kosong bila tidak ada).
function transaksiKomponenAda($kodept, $mulai)
{
	$pg = hkePengaturanBaru($kodept);
	if ($pg === null || !$pg['aktif'] || $pg['mulai'] === '') {
		return '';
	}
	return transaksiHkePTAda($kodept, max($mulai, $pg['mulai']), true);
}

#periode data gaji terakhir (YYYY-MM) PT ini di sdm_5gajipokok; kosong bila belum ada
function periodeGajiTerakhir($kodept)
{
	global $dbname, $owlPDO;
	$stm = $owlPDO->prepare("select max(tahun) from " . $dbname . ".sdm_5gajipokok where kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk=?) and idkomponen=1");
	$stm->execute(array($kodept));
	return (string)$stm->fetchColumn();
}

#pengaturan pengaktifan terbaru langsung dari tabel (tanpa cache fungsi hkePengaturan)
function hkePengaturanBaru($kodept)
{
	global $dbname, $owlPDO;
	$stm = $owlPDO->prepare("select aktif,periodemulai from " . $dbname . ".sdm_hke_pengaturan where kodept=?");
	$stm->execute(array($kodept));
	$r = $stm->fetch(PDO::FETCH_ASSOC);
	return $r ? array('aktif' => ((int)$r['aktif'] === 1), 'mulai' => (string)$r['periodemulai']) : null;
}

function namaKomponenBasic()
{
	global $dbname, $owlPDO;
	$o = array();
	foreach ($owlPDO->query("select id,name from " . $dbname . ".sdm_ho_component where type='basic' and plus=1 order by id")->fetchAll(PDO::FETCH_ASSOC) as $r) {
		$o[(int)$r['id']] = trim($r['name']);
	}
	#komponen 118 (catu / tunjangan keluarga natura) bukan komponen basic dan tidak ada di sdm_5gajipokok; boleh ikut dasar upah, nilainya dihitung dari kelompok catu karyawan (getCatuBulanan)
	$o[118] = 'Catu (Tunj. Keluarga Natura)';
	ksort($o);
	return $o;
}

switch($method)
{
    #================= keterangan PT: status pemakaian di transaksi dan komponen yang berlaku bulan ini =================
    case 'info':
        aksesPT($kodept);
        $p = hkePengaturan($kodept);
        $aktif = ($p !== null && $p['aktif']) ? 1 : 0;
        $mulai = ($p !== null) ? $p['mulai'] : '';
        if (!$aktif) {
            $status = 'TIDAK AKTIF';
            $kode = 'off';
        } elseif (!in_array($kodept, hkePTDaftar(), true)) {
            $status = 'AKTIF mulai ' . $mulai . ', belum dipakai di transaksi (saat ini hanya PT PPP)';
            $kode = 'soon';
        } elseif ($mulai > date('Y-m')) {
            $status = 'AKTIF, mulai ' . $mulai . ' (belum berjalan)';
            $kode = 'soon';
        } else {
            $status = 'AKTIF, berjalan sejak ' . $mulai;
            $kode = 'run';
        }
        $nama = namaKomponenBasic();
        $nm = array();
        foreach (getKomponenDasarUpahPPP($kodept, date('Y-m')) as $idk) {
            $nm[] = htmlspecialchars(isset($nama[(int)$idk]) ? $nama[(int)$idk] : ('Komponen ' . $idk));
        }
        echo $status . '|||' . implode(' + ', $nm) . '|||' . $aktif . '|||' . $mulai . '|||' . $kode;
        break;

    #================= komponen dasar upah yang berlaku pada satu periode (ditampilkan di konfirmasi pengaktifan) =================
    case 'komponenPada':
        aksesPT($kodept);
        if (!periodeSah($periode)) {
            exit("Warning : Periode tidak valid.");
        }
        $nama = namaKomponenBasic();
        $nm = array();
        foreach (getKomponenDasarUpahPPP($kodept, $periode) as $idk) {
            $nm[] = isset($nama[(int)$idk]) ? $nama[(int)$idk] : ('Komponen ' . $idk);
        }
        $stm = $owlPDO->prepare("select count(*) from " . $dbname . ".sdm_hke_komponen where kodept=? and periodemulai<=?");
        $stm->execute(array($kodept, $periode));
        echo implode(' + ', $nm) . ((int)$stm->fetchColumn() == 0 ? ' (belum ada pengaturan komponen, memakai bawaan)' : '');
        break;

    #================= pengaktifan: aktif atau tidak dan periode mulai berlaku =================
    case 'aktifSimpan':
        aksesPT($kodept);
        $aktifBaru = checkPostGet('aktif', '');
        $mulaiBaru = checkPostGet('mulai', '');
        if ($aktifBaru !== '0' && $aktifBaru !== '1') {
            exit("Warning : Status tidak valid.");
        }
        $stm = $owlPDO->prepare("select aktif,periodemulai from " . $dbname . ".sdm_hke_pengaturan where kodept=?");
        $stm->execute(array($kodept));
        $lama = $stm->fetch(PDO::FETCH_ASSOC);
        if ($aktifBaru === '1') {
            if (!periodeSah($mulaiBaru)) {
                exit("Warning : Pilih periode mulai berlaku.");
            }
            if (getHKEPPP($kodept, $mulaiBaru) === null) {
                exit("Warning : HKE periode " . $mulaiBaru . " belum diisi. Isi HKE periode mulai berlaku dulu di tab HKE.");
            }
        }
        #perubahan memengaruhi periode paling awal yang aktif (lama atau baru); tidak boleh bila payroll periode itu atau sesudahnya sudah diproses
        $awal = array();
        if ($lama && (int)$lama['aktif'] === 1 && $lama['periodemulai'] !== '') {
            $awal[] = $lama['periodemulai'];
        }
        if ($aktifBaru === '1') {
            $awal[] = $mulaiBaru;
        }
        if (count($awal) > 0) {
            $dari = min($awal);
            if (payrollPTSudah($kodept, $dari, true)) {
                exit("Warning : Payroll PT " . $kodept . " periode " . $dari . " atau sesudahnya sudah diproses, pengaturan tidak bisa diubah. Pilih periode mulai yang belum diproses.");
            }
        }
        #HKE yang sedang berjalan tidak boleh dimatikan atau dipindah periode mulainya bila sudah ada transaksi sejak periode mulai, supaya satu periode tidak campur dua aturan
        if ($lama && (int)$lama['aktif'] === 1 && $lama['periodemulai'] !== '' && ($aktifBaru === '0' || $mulaiBaru !== $lama['periodemulai'])) {
            $trx = transaksiHkePTAda($kodept, min($awal), true);
            if ($trx !== '') {
                exit("Warning : Sudah ada transaksi (" . $trx . ") PT " . $kodept . " sejak periode " . min($awal) . ", pengaturan HKE tidak bisa " . ($aktifBaru === '0' ? "dinonaktifkan" : "dipindah periode mulainya") . ".");
            }
        }
        $stm = $owlPDO->prepare("insert into " . $dbname . ".sdm_hke_pengaturan (kodept,aktif,periodemulai,updateby) values (?,?,?,?)
            on duplicate key update aktif=values(aktif), periodemulai=if(values(aktif)=1, values(periodemulai), periodemulai), updateby=values(updateby)");
        try {
            $stm->execute(array($kodept, (int)$aktifBaru, ($aktifBaru === '1' ? $mulaiBaru : ''), (int)$_SESSION['standard']['userid']));
        } catch (PDOException $e) {
            exit("Gagal menyimpan pengaturan.");
        }
        break;

    #================= HKE: daftar =================
    case 'hkeList':
        aksesPT($kodept);
        $stm = $owlPDO->prepare("select * from " . $dbname . ".sdm_hke_periode where kodept=? order by periode desc");
        $stm->execute(array($kodept));
        $no = 0;
        $out = '';
        foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $b) {
            $no++;
            $kunci = payrollPTSudah($kodept, $b['periode'], false);
            $trxKunci = $kunci ? '' : transaksiHkePTAda($kodept, $b['periode'], false);
            $aksi = $kunci
                ? "<span title='Payroll PT ini periode ini sudah diproses' style='color:#888'>terkunci</span>"
                : ($trxKunci !== ''
                ? "<span title='Periode ini sudah punya transaksi (" . htmlspecialchars($trxKunci, ENT_QUOTES) . ")' style='color:#888'>terkunci</span>"
                : "<img src=images/application/application_edit.png class=resicon title='Ubah' onclick=\"hkeIsi('" . $b['periode'] . "'," . (int)$b['minggu'] . "," . (int)$b['libur'] . ",'" . jsAkses($b['catatan']) . "');\">
                   <img src=images/application/application_delete.png class=resicon title='Hapus' onclick=\"hkeHapus('" . $b['periode'] . "');\">");
            $out .= "<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=center>" . htmlspecialchars($b['periode']) . "</td>
                <td align=right>" . (int)$b['minggu'] . "</td>
                <td align=right>" . (int)$b['libur'] . "</td>
                <td align=right><b>" . (int)$b['hke'] . "</b></td>
                <td>" . htmlspecialchars($b['catatan']) . "</td>
                <td>" . htmlspecialchars(getNamaKaryawan($b['updateby'])) . "<br><small>" . htmlspecialchars($b['updatetime']) . "</small></td>
                <td align=center>" . $aksi . "</td>
            </tr>";
        }
        if ($out == '') {
            $out = "<tr class=rowcontent><td colspan=8 align=center>Belum ada HKE untuk PT " . htmlspecialchars($kodept) . "</td></tr>";
        }
        echo $out;
        break;

    #================= HKE: simpan (tambah atau ubah) =================
    case 'hkeSimpan':
        aksesPT($kodept);
        if (!periodeSah($periode)) {
            exit("Warning : Periode tidak valid.");
        }
        if (!preg_match('/^[0-9]{1,2}$/', (string)$hariminggu) or !preg_match('/^[0-9]{1,2}$/', (string)$harilibur)) {
            exit("Warning : Hari Minggu dan Hari Libur harus berupa angka.");
        }
        $hariminggu = (int)$hariminggu;
        $harilibur = (int)$harilibur;
        $hari = hariBulanPT($periode);
        if ($hariminggu > 6) {
            exit("Warning : Hari Minggu tidak boleh lebih dari 6.");
        }
        $hke = $hari - $hariminggu - $harilibur; #dihitung server, bukan dipercaya dari browser
        if ($hke < 1 or $hke > $hari) {
            exit("Warning : HKE hasil hitung (" . $hke . ") tidak masuk akal untuk bulan dengan " . $hari . " hari.");
        }
        if (strlen($catatan) > 100) {
            exit("Warning : Catatan maksimal 100 karakter.");
        }
        if (payrollPTSudah($kodept, $periode, false)) {
            exit("Warning : Payroll PT " . $kodept . " periode " . $periode . " sudah diproses, HKE tidak bisa diubah.");
        }
        #HKE yang sudah tersimpan tidak boleh diubah bila periode itu sudah punya transaksi (upah transaksi dihitung dengan HKE lama)
        $stm = $owlPDO->prepare("select 1 from " . $dbname . ".sdm_hke_periode where kodept=? and periode=?");
        $stm->execute(array($kodept, $periode));
        if ($stm->fetchColumn() !== false) {
            $trx = transaksiHkePTAda($kodept, $periode, false);
            if ($trx !== '') {
                exit("Warning : Periode " . $periode . " sudah punya transaksi (" . $trx . ") PT " . $kodept . ", HKE tidak bisa diubah.");
            }
        }
        $stm = $owlPDO->prepare("insert into " . $dbname . ".sdm_hke_periode (kodept,periode,minggu,libur,hke,catatan,updateby) values (?,?,?,?,?,?,?)
            on duplicate key update minggu=values(minggu),libur=values(libur),hke=values(hke),catatan=values(catatan),updateby=values(updateby)");
        try {
            $stm->execute(array($kodept, $periode, $hariminggu, $harilibur, $hke, $catatan, (int)$_SESSION['standard']['userid']));
        } catch (PDOException $e) {
            exit("Gagal menyimpan HKE.");
        }
        break;

    case 'hkeHapus':
        aksesPT($kodept);
        if (!periodeSah($periode)) {
            exit("Warning : Periode tidak valid.");
        }
        if (payrollPTSudah($kodept, $periode, false)) {
            exit("Warning : Payroll PT " . $kodept . " periode " . $periode . " sudah diproses, HKE tidak bisa dihapus.");
        }
        #HKE periode yang tercakup pengaktifan dipakai transaksi; menghapusnya membuat input periode itu terblokir
        $pg = hkePengaturanBaru($kodept);
        if ($pg !== null && $pg['aktif'] && $pg['mulai'] !== '' && $periode >= $pg['mulai']) {
            exit("Warning : HKE periode " . $periode . " tercakup pengaktifan (mulai " . $pg['mulai'] . "), tidak bisa dihapus. Ubah nilainya, atau nonaktifkan / pindahkan periode mulai dulu.");
        }
        $trx = transaksiHkePTAda($kodept, $periode, false);
        if ($trx !== '') {
            exit("Warning : Periode " . $periode . " sudah punya transaksi (" . $trx . ") PT " . $kodept . ", HKE tidak bisa dihapus.");
        }
        $stm = $owlPDO->prepare("delete from " . $dbname . ".sdm_hke_periode where kodept=? and periode=?");
        $stm->execute(array($kodept, $periode));
        break;

    #================= HKE: saran dari kalender hari libur per unit =================
    case 'hkeSaran':
        aksesPT($kodept);
        if (!periodeSah($periode)) {
            exit("Warning : Pilih periode dulu.");
        }
        $hari = hariBulanPT($periode);
        $stm = $owlPDO->prepare("select h.kebun, h.tanggal, h.keterangan from " . $dbname . ".sdm_5harilibur h
            join " . $dbname . ".organisasi o on o.kodeorganisasi=h.kebun and o.induk=?
            where h.tanggal like ? order by h.kebun, h.tanggal");
        $stm->execute(array($kodept, $periode . '%'));
        $unit = array();
        foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if ($r['keterangan'] == 'libur') {
                $unit[$r['kebun']]['libur'][$r['tanggal']] = true;
            } else {
                $unit[$r['kebun']]['masuk'][$r['tanggal']] = true;
            }
        }
        #unit dengan hasil sama digabung satu baris: kunci "minggu|libur" => daftar unit
        $grup = array();
        foreach ($unit as $kd => $d) {
            $mgg = 0;
            $lib = 0;
            $lbr = isset($d['libur']) ? array_keys($d['libur']) : array();
            foreach ($lbr as $tgl) {
                if (isset($d['masuk'][$tgl])) {
                    continue;
                }
                if (date('w', strtotime($tgl)) == 0) {
                    $mgg++;
                } else {
                    $lib++;
                }
            }
            $grup[$mgg . '|' . $lib][] = $kd;
        }
        $baris = '';
        foreach ($grup as $kunci => $daftarUnit) {
            list($mgg, $lib) = explode('|', $kunci);
            $baris .= "<tr class=rowcontent>
                <td align=center><b>" . ($hari - $mgg - $lib) . "</b> hari</td>
                <td align=center>" . (int)$mgg . " / " . (int)$lib . "</td>
                <td>" . htmlspecialchars(implode(', ', $daftarUnit)) . "</td>
                <td align=center><button class=mybutton onclick=\"hkeTerap(" . (int)$mgg . "," . (int)$lib . ");\">Pakai</button></td></tr>";
        }
        if ($baris == '') {
            $baris = "<tr class=rowcontent><td colspan=4 align=center>Belum ada hari libur unit PT " . htmlspecialchars($kodept) . " pada periode ini di Setup Hari Libur</td></tr>";
        }
        #transaksi absensi yang sudah tercatat pada periode itu untuk karyawan Bulanan PT ini (tarifnya tidak berubah mengikuti HKE yang baru)
        $stm = $owlPDO->prepare("select count(*) from " . $dbname . ".sdm_absensidt a join " . $dbname . ".datakaryawan d on d.karyawanid=a.karyawanid
            where a.tanggal like ? and a.umr>0 and d.sistemgaji='Bulanan' and d.lokasitugas in (select kodeorganisasi from " . $dbname . ".organisasi where induk=?)");
        $stm->execute(array($periode . '%', $kodept));
        $adaTrans = (int)$stm->fetchColumn();
        echo "<div style='margin:4px 0 8px 0'>Hari dalam bulan: <b>" . $hari . "</b>. Saran dari Setup Hari Libur unit PT " . htmlspecialchars($kodept) . " (Minggu / Libur = hari Minggu libur / hari libur lain):</div>
            <table class='sortable hkeTbl' cellspacing=1 border=0><thead><tr class=rowheader><td align=center style='width:130px'>Saran HKE</td><td align=center style='width:150px'>Minggu / Libur</td><td align=center>Unit</td><td style='width:90px'></td></tr></thead><tbody>" . $baris . "</tbody></table>"
            . ($adaTrans > 0 ? "<div class=hkeWarn>Perhatian: periode ini sudah punya " . $adaTrans . " baris absensi karyawan Bulanan PT ini. Upah yang sudah tersimpan tidak ikut berubah; perlu hitung ulang upah setelah HKE disimpan.</div>" : "");
        break;

    #================= Komponen dasar upah harian: baris centang untuk PT terpilih =================
    case 'kompForm':
        aksesPT($kodept);
        $nama = namaKomponenBasic();
        $pakai = array();
        $perTerakhir = periodeGajiTerakhir($kodept);
        if ($perTerakhir !== '') {
            $stm = $owlPDO->prepare("select g.idkomponen,count(distinct g.karyawanid) as jml from " . $dbname . ".sdm_5gajipokok g join " . $dbname . ".datakaryawan d on d.karyawanid=g.karyawanid where g.tahun=? and d.sistemgaji='Bulanan' and g.kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk=?) group by g.idkomponen");
            $stm->execute(array($perTerakhir, $kodept));
            foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $pakai[(int)$r['idkomponen']] = (int)$r['jml'];
            }
        }
        #jumlah karyawan Bulanan PT ini yang punya kelompok catu
        $stm = $owlPDO->prepare("select count(*) from " . $dbname . ".datakaryawan where sistemgaji='Bulanan' and kodecatu not in ('','0') and (tanggalkeluar is null or tanggalkeluar='0000-00-00' or tanggalkeluar>=curdate()) and lokasitugas in (select kodeorganisasi from " . $dbname . ".organisasi where induk=?)");
        $stm->execute(array($kodept));
        $pakai[118] = (int)$stm->fetchColumn();
        $out = '';
        foreach ($nama as $id => $nm) {
            $jml = isset($pakai[$id]) ? $pakai[$id] : 0;
            $out .= "<tr class=rowcontent>
                <td align=center><input type=checkbox id=kompChk" . $id . " class=kompChk value=" . $id . ($id == 1 ? " checked disabled" : "") . "></td>
                <td><label for=kompChk" . $id . ">" . htmlspecialchars($nm) . " (" . $id . ")" . ($id == 1 ? " <i>selalu ikut</i>" : "") . "</label></td>
                <td align=center>" . ($jml > 0 ? "<a href='#' title='Klik untuk melihat daftar karyawan' style='cursor:pointer;text-decoration:underline' onclick=\"kompKaryawan(" . $id . ",event);return false;\">" . $jml . " karyawan</a>" : "<span style='color:#888'>tidak dipakai</span>") . "</td>
            </tr>";
        }
        echo $out;
        break;

    #================= Daftar karyawan yang memakai satu komponen (klik pada jumlah karyawan) =================
    case 'kompKaryawan':
        aksesPT($kodept);
        $idk = (int)checkPostGet('idkomponen', 0);
        $nama = namaKomponenBasic();
        if (!isset($nama[$idk])) {
            exit("Warning : Komponen tidak valid.");
        }
        $batas = 500;
        if ($idk == 118) {
            #catu: karyawan Bulanan yang punya kelompok catu; nilai = kg kelompok x harga per kg unitnya
            $stm = $owlPDO->prepare("select d.nik, d.namakaryawan, d.lokasitugas, d.kodecatu, c.jumlah as kg, h.nilai as harga from " . $dbname . ".datakaryawan d
                left join " . $dbname . ".sdm_5catu c on c.kodeorg=d.lokasitugas and c.tahun=year(curdate()) and c.kelompok=d.kodecatu
                left join " . $dbname . ".sdm_5hargacatukg h on h.unit=d.lokasitugas and h.status='1'
                where d.sistemgaji='Bulanan' and d.kodecatu not in ('','0') and (d.tanggalkeluar is null or d.tanggalkeluar='0000-00-00' or d.tanggalkeluar>=curdate())
                and d.lokasitugas in (select kodeorganisasi from " . $dbname . ".organisasi where induk=?) order by d.lokasitugas, d.namakaryawan limit " . ($batas + 1));
            $stm->execute(array($kodept));
            $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
            $judul = 'Catu: kelompok, kg, dan nilai per bulan';
            $kepala = "<td>NIK</td><td>Nama</td><td>Unit</td><td>Kelompok</td><td align=right>Kg</td><td align=right>Nilai</td>";
            $baris = '';
            foreach (array_slice($rows, 0, $batas) as $r) {
                $baris .= "<tr class=rowcontent><td>" . htmlspecialchars($r['nik']) . "</td><td>" . htmlspecialchars($r['namakaryawan']) . "</td><td>" . htmlspecialchars($r['lokasitugas']) . "</td><td align=center>" . htmlspecialchars($r['kodecatu']) . "</td><td align=right>" . number_format((float)$r['kg'], 0, ',', '.') . "</td><td align=right>" . number_format((float)$r['kg'] * (float)$r['harga'], 0, ',', '.') . "</td></tr>";
            }
        } else {
            $per = periodeGajiTerakhir($kodept);
            $stm = $owlPDO->prepare("select d.nik, d.namakaryawan, d.lokasitugas, g.jumlah from " . $dbname . ".sdm_5gajipokok g
                join " . $dbname . ".datakaryawan d on d.karyawanid=g.karyawanid
                where g.tahun=? and g.idkomponen=? and d.sistemgaji='Bulanan' and g.kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk=?) order by g.kodeorg, d.namakaryawan limit " . ($batas + 1));
            $stm->execute(array($per, $idk, $kodept));
            $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
            $judul = 'Karyawan Bulanan, periode data ' . htmlspecialchars($per);
            $kepala = "<td>NIK</td><td>Nama</td><td>Unit</td><td align=right>Jumlah</td>";
            $baris = '';
            foreach (array_slice($rows, 0, $batas) as $r) {
                $baris .= "<tr class=rowcontent><td>" . htmlspecialchars($r['nik']) . "</td><td>" . htmlspecialchars($r['namakaryawan']) . "</td><td>" . htmlspecialchars($r['lokasitugas']) . "</td><td align=right>" . number_format((float)$r['jumlah'], 0, ',', '.') . "</td></tr>";
            }
        }
        if ($baris == '') {
            $baris = "<tr class=rowcontent><td colspan=6 align=center>Tidak ada karyawan</td></tr>";
        }
        echo "<div style='padding:6px;max-height:380px;overflow:auto'><div style='margin-bottom:6px'><b>" . htmlspecialchars($nama[$idk]) . "</b> (" . $idk . "). " . $judul . ". " . min(count($rows), $batas) . " karyawan" . (count($rows) > $batas ? " (ditampilkan " . $batas . " pertama)" : "") . "</div>
            <table class=sortable cellspacing=1 border=0 style='width:100%'><thead><tr class=rowheader>" . $kepala . "</tr></thead><tbody>" . $baris . "</tbody></table></div>";
        break;

    #================= Komponen dasar upah harian: daftar pengaturan =================
    case 'kompList':
        aksesPT($kodept);
        $nama = namaKomponenBasic();
        $stm = $owlPDO->prepare("select periodemulai, idkomponen from " . $dbname . ".sdm_hke_komponen where kodept=? order by periodemulai desc, idkomponen");
        $stm->execute(array($kodept));
        $set = array();
        foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $set[$r['periodemulai']][] = $r['idkomponen'];
        }
        $out = '';
        $no = 0;
        foreach ($set as $mulai => $ids) {
            $no++;
            $nm = array();
            foreach ($ids as $id) {
                $nm[] = htmlspecialchars(isset($nama[(int)$id]) ? $nama[(int)$id] : ('Komponen ' . $id));
            }
            $payKunci = payrollPTSudah($kodept, $mulai, true);
            $trxKunci = $payKunci ? '' : transaksiKomponenAda($kodept, $mulai);
            $aksi = $payKunci
                ? "<span title='Payroll PT ini periode ini atau sesudahnya sudah diproses' style='color:#888'>terkunci</span>"
                : ($trxKunci !== ''
                ? "<span title='Sudah ada transaksi (" . htmlspecialchars($trxKunci, ENT_QUOTES) . ") yang memakai pengaturan ini' style='color:#888'>terkunci</span>"
                : "<img src=images/application/application_edit.png class=resicon title='Ubah' onclick=\"kompIsi('" . $mulai . "','" . implode(',', array_map('intval', $ids)) . "');\">
                   <img src=images/application/application_delete.png class=resicon title='Hapus' onclick=\"kompHapus('" . $mulai . "');\">");
            $out .= "<tr class=rowcontent><td align=center>" . $no . "</td><td align=center>" . htmlspecialchars($mulai) . "</td><td>" . implode(' + ', $nm) . "</td><td align=center>" . $aksi . "</td></tr>";
        }
        if ($out == '') {
            $out = "<tr class=rowcontent><td colspan=4 align=center>Belum ada pengaturan: dasar upah harian hanya Gaji Pokok</td></tr>";
        }
        echo $out;
        break;

    case 'kompSimpan':
        aksesPT($kodept);
        $mulai = checkPostGet('mulai', '');
        $komponen = checkPostGet('komponen', '');
        if (!periodeSah($mulai)) {
            exit("Warning : Periode mulai tidak valid.");
        }
        $nama = namaKomponenBasic();
        $ids = array(1 => 1); #gaji pokok selalu ikut
        foreach (explode(',', (string)$komponen) as $k) {
            $k = trim($k);
            if ($k === '') {
                continue;
            }
            if (!preg_match('/^[0-9]{1,6}$/', $k) or !isset($nama[(int)$k])) {
                exit("Warning : Komponen " . htmlspecialchars($k) . " bukan komponen upah (basic).");
            }
            $ids[(int)$k] = (int)$k;
        }
        if (payrollPTSudah($kodept, $mulai, true)) {
            exit("Warning : Payroll PT " . $kodept . " periode " . $mulai . " atau sesudahnya sudah diproses. Pilih periode mulai yang belum diproses.");
        }
        $trx = transaksiKomponenAda($kodept, $mulai);
        if ($trx !== '') {
            exit("Warning : Sudah ada transaksi (" . $trx . ") PT " . $kodept . " yang memakai komponen dasar upah ini, pengaturan tidak bisa diubah.");
        }
        try {
            $owlPDO->beginTransaction();
            $del = $owlPDO->prepare("delete from " . $dbname . ".sdm_hke_komponen where kodept=? and periodemulai=?");
            $del->execute(array($kodept, $mulai));
            $ins = $owlPDO->prepare("insert into " . $dbname . ".sdm_hke_komponen (kodept,periodemulai,idkomponen,updateby) values (?,?,?,?)");
            foreach ($ids as $id) {
                $ins->execute(array($kodept, $mulai, $id, (int)$_SESSION['standard']['userid']));
            }
            $owlPDO->commit();
        } catch (PDOException $e) {
            if ($owlPDO->inTransaction()) {
                $owlPDO->rollBack();
            }
            exit("Gagal menyimpan komponen dasar upah.");
        }
        break;

    case 'kompHapus':
        aksesPT($kodept);
        $mulai = checkPostGet('mulai', '');
        if (!periodeSah($mulai)) {
            exit("Warning : Periode mulai tidak valid.");
        }
        if (payrollPTSudah($kodept, $mulai, true)) {
            exit("Warning : Payroll PT " . $kodept . " periode " . $mulai . " atau sesudahnya sudah diproses, pengaturan tidak bisa dihapus.");
        }
        $trx = transaksiKomponenAda($kodept, $mulai);
        if ($trx !== '') {
            exit("Warning : Sudah ada transaksi (" . $trx . ") PT " . $kodept . " yang memakai komponen dasar upah ini, pengaturan tidak bisa dihapus.");
        }
        $stm = $owlPDO->prepare("delete from " . $dbname . ".sdm_hke_komponen where kodept=? and periodemulai=?");
        $stm->execute(array($kodept, $mulai));
        break;

        default:
        break;
}

#teks untuk argumen string JavaScript di dalam atribut HTML onclick="fn('...')"
function jsAkses($s)
{
	return htmlspecialchars(addcslashes((string)$s, "\\'\"\r\n</"), ENT_QUOTES);
}
?>
