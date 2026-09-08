<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param           = $_POST;
$tmpPeriod       = explode('-',$param['periode']);
$tahunbulan      = implode("",$tmpPeriod);
$tahun           = $tmpPeriod[0];
$bulan           = $tmpPeriod[1];
$proses          = $_GET['proses'];

#=========================================================================
#= DIAGNOSTIK SELISIH AKUN TRANSIT (Workshop 41101% / Traksi 41102%)
#= Dipakai saat selisihnya BESAR / di luar rentang rounding, jadi tidak
#= dibuatkan jurnal otomatis. Grouping per-KARYAWAN (bukan per kode
#= noreferensi) karena satu noreferensi dipakai juga oleh proses alokasi
#= normal (volume besar, saling menutup per karyawan) - kalau digroup per
#= noreferensi saja, proses normal itu ikut nongol sebagai "penyumbang"
#= padahal bukan. Per-karyawan otomatis bersih karena tiap karyawan yang
#= alokasinya sudah benar akan netto ~0 dengan sendirinya.
#=========================================================================
#= Ambil breakdown LENGKAP selisih per-karyawan (semua kontributor, sekecil
#= apapun) - dipakai baik utk cek kelayakan auto-jurnal maupun utk
#= ditampilkan sbg diagnostik, biar query cuma jalan sekali dan totalnya
#= selalu bisa direkonsiliasi persis ke selisih keseluruhan.
function getBreakdownKaryawan($dbname,$kodeorg,$periode,$pola){
    $sSumber = "SELECT a.nik, COALESCE(k.namakaryawan,'') as nama, SUM(a.debet)-SUM(a.kredit) as saldo
        FROM ".$dbname.".keu_jurnaldt_vw a
        LEFT JOIN ".$dbname.".datakaryawan k ON k.karyawanid = CAST(a.nik AS UNSIGNED)
        WHERE a.kodeorg='".$kodeorg."' AND a.periode='".$periode."' AND a.noakun LIKE '".$pola."'
        GROUP BY a.nik, nama
        HAVING ABS(SUM(a.debet)-SUM(a.kredit)) > 0.001
        ORDER BY ABS(SUM(a.debet)-SUM(a.kredit)) DESC";
    return fetchdata($sSumber);
}

#= label yang ditampilkan: karyawan (nama+NIK), karyawan tanpa nama di
#= datakaryawan (NIK saja), atau sumber yang sama sekali tidak terikat
#= karyawan tertentu (nik kosong - misal transaksi gudang/umum)
function labelKaryawan($bar){
    if($bar['nik']==''){ return 'Tidak terikat karyawan tertentu (misal transaksi gudang/umum)'; }
    $nama = trim($bar['nama']);
    return ($nama=='') ? 'NIK '.intval($bar['nik']).' (nama tidak ditemukan)' : $nama.' (NIK '.intval($bar['nik']).')';
}

#= format daftar breakdown: karyawan dengan nilai < $batasKecil ditampilkan
#= satu-satu, sisanya (nilai besar - proses realokasi normal antar
#= kendaraan/karyawan, saling menutup) digabung jadi 1 baris ringkasan.
#= Karena ini partisi LENGKAP dari semua kontributor (tidak ada yang
#= dibuang), totalnya pasti persis sama dengan selisih keseluruhan.
function formatBreakdownList($rSumber,$batasKecil){
    $baris = '';
    $totalKecil = 0;
    $kecilNik = array();
    $besarList = array();
    foreach($rSumber as $bar){
        if(abs($bar['saldo']) < $batasKecil){
            $baris .= "<tr><td style='padding:2px 8px 2px 0;'>".labelKaryawan($bar)."</td><td style='padding:2px 0;text-align:right;'>Rp".number_format($bar['saldo'],2)."</td></tr>";
            $totalKecil += $bar['saldo'];
            $kecilNik[] = $bar['nik'];
        }else{
            $besarList[] = $bar;
        }
    }
    $totalBesar = 0;
    foreach($besarList as $b){ $totalBesar += $b['saldo']; }
    if(count($besarList)>0){
        $baris .= "<tr><td style='padding:2px 8px 2px 0;'>Aktivitas/realokasi normal lainnya (".count($besarList)." karyawan, nilai besar tapi bagian dari proses biasa)</td><td style='padding:2px 0;text-align:right;'>Rp".number_format($totalBesar,2)."</td></tr>";
    }
    if($baris==''){ $baris = "<tr><td colspan=2 style='padding:2px 0;'>(tidak ada rincian)</td></tr>"; }
    return array('html'=>$baris, 'total'=>$totalKecil+$totalBesar, 'kecilNik'=>$kecilNik, 'totalKecil'=>$totalKecil);
}

#= cari tau jurnal/proses (noreferensi) apa yang beneran menghasilkan nilai
#= KECIL itu - dicari langsung dari data, BUKAN diasumsikan/di-hardcode,
#= supaya penjelasannya tetap general (bisa dari proses apapun, tidak harus
#= selalu terkait karyawan tertentu)
function pesanSumberKecil($dbname,$kodeorg,$periode,$pola,$kecilNik){
    if(count($kecilNik)==0){ return ''; }
    $whNik = array();
    foreach($kecilNik as $n){ $whNik[] = "'".addslashes($n)."'"; }
    $sSrc = "SELECT noreferensi, SUM(debet)-SUM(kredit) as saldo
        FROM ".$dbname.".keu_jurnaldt_vw
        WHERE kodeorg='".$kodeorg."' AND periode='".$periode."' AND noakun LIKE '".$pola."' AND nik IN (".implode(',',$whNik).")
        GROUP BY noreferensi
        HAVING ABS(SUM(debet)-SUM(kredit)) > 0.01
        ORDER BY ABS(SUM(debet)-SUM(kredit)) DESC";
    $rSrc = fetchdata($sSrc);
    if(count($rSrc)==0){ return ''; }
    $listSrc = array();
    foreach($rSrc as $s){
        $noref = ($s['noreferensi']=='') ? '(tanpa kode referensi)' : $s['noreferensi'];
        $listSrc[] = $noref." (Rp".number_format($s['saldo'],2).")";
    }
    return "<br><i>Sumber jurnal nilai kecil ini : ".implode(', ',$listSrc)."."
        ." Biasanya muncul karena proses tersebut membagi suatu nilai secara proporsional ke beberapa dimensi (kendaraan/blok/dll) memakai pembulatan ke bawah, dan sisanya tidak terikat ke dimensi manapun sehingga tidak ikut ter-realokasi ke akun transit.</i>";
}

#= tabel header jurnal (Nomor/Tanggal/Kodejurnal/No.Referensi/Keterangan)
#= rapi sejajar label:value, dipakai di popup preview maupun pesan sukses
function pesanHeaderJurnal($nomor,$tanggal,$refJurnal,$keterangan){
    $baris = "<tr><td style='padding:2px 10px 2px 0;color:#666;white-space:nowrap;'>Nomor</td><td style='padding:2px 0;'>".$nomor."</td></tr>"
        ."<tr><td style='padding:2px 10px 2px 0;color:#666;white-space:nowrap;'>Tanggal</td><td style='padding:2px 0;'>".$tanggal."</td></tr>"
        ."<tr><td style='padding:2px 10px 2px 0;color:#666;white-space:nowrap;'>Kodejurnal</td><td style='padding:2px 0;'>M (Jurnal Memorial)</td></tr>"
        ."<tr><td style='padding:2px 10px 2px 0;color:#666;white-space:nowrap;'>No. Referensi</td><td style='padding:2px 0;'>".$refJurnal."</td></tr>"
        ."<tr><td style='padding:2px 10px 2px 0;color:#666;white-space:nowrap;vertical-align:top;'>Keterangan</td><td style='padding:2px 0;'>".$keterangan."</td></tr>";
    return "<table style='border-collapse:collapse;margin:6px 0 10px 0;'>".$baris."</table>";
}

#= breakdown ringkas (tanpa panduan "cara menolkan") - dipakai di kasus yang
#= SUDAH layak jurnal otomatis, biar tetap kelihatan rinciannya dari mana
#= saja dan totalnya PERSIS sama dengan selisih keseluruhan sebelum diklik
function pesanBreakdownSaja($dbname,$kodeorg,$periode,$pola,$rBreakdown,$totalSelisih,$batasKecil){
    $r = formatBreakdownList($rBreakdown,$batasKecil);
    $baris = $r['html']
        ."<tr><td style='padding:4px 8px 2px 0;border-top:1px solid #999;'><b>Total rincian</b></td><td style='padding:4px 0 2px 0;text-align:right;border-top:1px solid #999;'><b>Rp".number_format($r['total'],2)."</b></td></tr>";
    return "<table style='width:100%;border-collapse:collapse;margin:4px 0 8px 0;'>".$baris."</table>"
        .pesanSumberKecil($dbname,$kodeorg,$periode,$pola,$r['kecilNik']);
}

#=========================================================================
#= DIAGNOSTIK SELISIH AKUN TRANSIT (Workshop 41101% / Traksi 41102%)
#= Dipakai saat selisihnya wajib dicek manual (di luar rentang otomatis,
#= atau totalnya sudah di atas Rp200 walau masih layak auto-jurnal).
#= Grouping per-KARYAWAN (bukan per kode noreferensi) karena satu
#= noreferensi dipakai juga oleh proses alokasi normal (volume besar,
#= saling menutup per karyawan) - kalau digroup per noreferensi saja,
#= proses normal itu ikut nongol sebagai "penyumbang" padahal bukan.
#=========================================================================
function pesanDiagnostikTransit($dbname,$kodeorg,$periode,$pola,$label,$totalSelisih,$rSumber,$batasKecil){
    #= kalau ada SATU karyawan yang sendirian sudah = total selisihnya, berarti
    #= dia satu-satunya penyebab asli - karyawan lain di list cuma saling
    #= menutup (proses alokasi normal), jadi rincikan khusus dia saja per akun
    $penyebabTunggal = null;
    foreach($rSumber as $bar){
        if(abs(abs($bar['saldo']) - $totalSelisih) < 1){
            $penyebabTunggal = $bar;
            break;
        }
    }

    $baris = '';
    if($penyebabTunggal!==null){
        $namaTampil = labelKaryawan($penyebabTunggal);
        $baris .= "<tr><td colspan=2 style='padding:2px 0 4px 0;'><b>".$namaTampil."</b> - total Rp".number_format($penyebabTunggal['saldo'])."</td></tr>";

        $sDetail = "SELECT noakun, SUM(debet)-SUM(kredit) as saldo
            FROM ".$dbname.".keu_jurnaldt_vw
            WHERE kodeorg='".$kodeorg."' AND periode='".$periode."' AND noakun LIKE '".$pola."' AND nik".($penyebabTunggal['nik']=='' ? "=''" : "='".$penyebabTunggal['nik']."'")."
            GROUP BY noakun
            HAVING ABS(SUM(debet)-SUM(kredit)) > 1
            ORDER BY ABS(SUM(debet)-SUM(kredit)) DESC";
        $rDetail = fetchdata($sDetail);
        foreach($rDetail as $d){
            $baris .= "<tr><td style='padding:2px 8px 2px 16px;'>".$d['noakun']." - ".getNamaAkun($d['noakun'])."</td><td style='padding:2px 0;text-align:right;'>Rp".number_format($d['saldo'])."</td></tr>";
        }
    }else{
        $baris .= "<tr><td colspan=2 style='padding:2px 0 4px 0;'>Tidak ada satu sumber tunggal yang menjelaskan seluruh selisih - berikut rincian tiap sumber :</td></tr>";
        $r = formatBreakdownList($rSumber,$batasKecil);
        $baris .= $r['html']
            ."<tr><td style='padding:4px 8px 2px 0;border-top:1px solid #999;'><b>Total rincian</b></td><td style='padding:4px 0 2px 0;text-align:right;border-top:1px solid #999;'><b>Rp".number_format($r['total'],2)."</b></td></tr>";
        $pesanSumberKecil = pesanSumberKecil($dbname,$kodeorg,$periode,$pola,$r['kecilNik']);
    }
    if($baris==''){ $baris = "<tr><td colspan=2 style='padding:2px 0;'>(tidak ada rincian)</td></tr>"; }

    return "<br>Penyebab selisih (".$label.") :"
        ."<table style='width:100%;border-collapse:collapse;margin:4px 0 8px 0;'>".$baris."</table>"
        .(isset($pesanSumberKecil) ? $pesanSumberKecil : '')
        ."Cara menolkan : buat jurnal memorial reklasifikasi sisi biaya saja (jurnal asli yang mencatat hutang/kewajiban tidak perlu diubah), Kredit akun biaya di atas sebesar nilai selisihnya, Debet ke akun tujuan yang sesuai dengan jenis biayanya.";
}

#=========================================================================
#= AUTO JURNAL PEMBULATAN (Workshop/Traksi) - khusus PT PPP, selisih kecil
#= (rounding). Membuat jurnal memorial: Debet/Kredit akun Pembulatan
#= (kepala 9) vs akun transit itu sendiri (4110199 Workshop / 4110299
#= Traksi, tidak butuh kodevhc/dimensi lain), sebesar selisihnya, LANGSUNG
#= diposting ke ledger asli (skip approval manual - sudah lewat popup
#= konfirmasi sebelumnya).
#=========================================================================
if($proses=='autoJurnalTransit' || $proses=='previewJurnalAutoTransit'){
    $isPreview = ($proses=='previewJurnalAutoTransit');

    $tipe = $param['tipe']; // 'workshop' atau 'traksi'
    if($tipe=='workshop'){
        $pola = '41101%'; $akunTransit = '4110199'; $label = 'Workshop'; $refJurnal = 'AUTO_PEMBULATAN_WORKSHOP';
    }elseif($tipe=='traksi'){
        $pola = '41102%'; $akunTransit = '4110299'; $label = 'Traksi'; $refJurnal = 'AUTO_PEMBULATAN_TRAKSI';
    }else{
        exit("Warning:Tipe tidak dikenal.");
    }

    #= 1. khusus unit di bawah PT PPP
    $sInduk = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
    $rInduk = fetchdata($sInduk);
    $indukOrg = '';
    foreach($rInduk as $bar){ $indukOrg = $bar['induk']; }
    if($indukOrg != 'PPP'){
        exit("Warning:Fitur ini khusus untuk unit di bawah PT PPP.");
    }

    #= 2. hitung ulang selisih akun transit
    $sTransit = "select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw
            where periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."' AND noakun like '".$pola."'";
    $rTransit = fetchdata($sTransit);
    $saldoTransit = 0;
    foreach($rTransit as $bar){ $saldoTransit = $bar['saldo']; }
    $absTransit = abs($saldoTransit);

    #= 3. hanya untuk selisih kecil: total < Rp200 DAN tiap karyawan < Rp20.
    #= Selisih besar / terkonsentrasi di 1-2 karyawan wajib dicek manual.
    if(!($absTransit>10 && $absTransit<200)){
        exit("Warning:Selisih akun transit ".$label." (".number_format($absTransit).") di luar rentang pembulatan (total harus 10-500), silahkan cek manual, tidak dibuatkan jurnal otomatis.");
    }
    #= batas noise (khusus Traksi) : realokasi normal antar kendaraan bisa
    #= puluhan/ratusan juta per karyawan, itu bukan bagian dari selisih -
    #= jangan ikut dihitung sbg "karyawan individu" saat cek kelayakan
    $batasNoise = ($tipe=='traksi') ? 100000 : PHP_INT_MAX;
    $rBreakdownCek = getBreakdownKaryawan($dbname,$param['kodeorg'],$param['periode'],$pola);
    $maxKaryawanCek = 0;
    foreach($rBreakdownCek as $b){
        if(abs($b['saldo'])<$batasNoise){ $maxKaryawanCek = max($maxKaryawanCek, abs($b['saldo'])); }
    }
    if($maxKaryawanCek>=20){
        exit("Warning:Ada karyawan dengan selisih individu Rp".number_format($maxKaryawanCek)." (>=Rp20), tidak bisa dibuatkan jurnal otomatis, silahkan cek manual.");
    }

    #= 4. cegah dobel kalau tombol diklik berkali-kali untuk periode yang sama
    $sCekDup = "select nojurnal from ".$dbname.".keu_jurnalmemorial
            where kodeorg='".$param['kodeorg']."' and noreferensi='".$refJurnal."'
            and tanggal like '".$param['periode']."%' and posting!=2";
    $rCekDup = fetchdata($sCekDup);
    if(count($rCekDup)>0){
        exit("Warning:Jurnal otomatis untuk periode ini sudah pernah dibuat, nomor : ".$rCekDup[0]['nojurnal'].". Silahkan cek di menu Keuangan - Jurnal Memorial.");
    }

    #= 5. tanggal jurnal = akhir periode aktif (belum tutup buku) untuk unit ybs
    $sPeriodeAkt = "select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."' and tutupbuku=0 order by periode asc limit 1";
    $rPeriodeAkt = fetchdata($sPeriodeAkt);
    if(count($rPeriodeAkt)==0){
        exit("Warning:Periode ".$param['periode']." untuk unit ".$param['kodeorg']." sudah tutup buku atau tidak ditemukan.");
    }
    $tglJurnal = $rPeriodeAkt[0]['tanggalsampai'];

    #= 6. akun lawan = akun transit itu sendiri (tidak perlu breakdown per akun/kendaraan).
    #= Akun Pembulatan ditampung di 9210110 saja (plus maupun minus), tidak
    #= dipecah ke 9110111 lagi - biar semua pembulatan ngumpul di 1 akun.
    $akunPembulatan = '9210110';
    if($saldoTransit>0){
        #= debet>kredit di kepala 4 -> kredit akun transit, debet akun pembulatan
        $jmlTransit     = -$absTransit;
        $jmlPembulatan  = $absTransit;
    }else{
        $jmlTransit     = $absTransit;
        $jmlPembulatan  = -$absTransit;
    }

    #= jangan hardcode "gaji" - sumbernya bisa dari proses alokasi apapun
    #= (gaji, material, dll), lihat pesanSumberKecil() utk sumber sebenarnya
    $keterangan = "Auto-jurnal reklasifikasi sisa pembulatan alokasi ".$label." periode ".$param['periode'];

    $sNamaAkun = "select noakun,namaakun from ".$dbname.".keu_5akun where noakun in ('".$akunPembulatan."','".$akunTransit."')";
    $rNamaAkun = fetchdata($sNamaAkun);
    $namaAkun = array();
    foreach($rNamaAkun as $bar){ $namaAkun[$bar['noakun']] = $bar['namaakun']; }
    $pesanDebet  = ($jmlPembulatan>0) ? $akunPembulatan." - ".$namaAkun[$akunPembulatan] : $akunTransit." - ".$namaAkun[$akunTransit];
    $pesanKredit = ($jmlPembulatan>0) ? $akunTransit." - ".$namaAkun[$akunTransit] : $akunPembulatan." - ".$namaAkun[$akunPembulatan];

    #= sumber selisihnya dipakai breakdown per-karyawan yang SAMA persis dengan
    #= yang ditampilkan di pesan validasi (reuse $rBreakdownCek yang sudah
    #= dihitung di langkah kelayakan #3 di atas) - supaya popup konfirmasi ini
    #= tidak pernah beda isi/angka dengan yang user lihat sebelum klik tombol
    $pesanSumberHtml = pesanBreakdownSaja($dbname,$param['kodeorg'],$param['periode'],$pola,$rBreakdownCek,$absTransit,20);

    $pesanJurnal = "<tr><td style='padding:2px 8px 2px 0;'>Debet</td><td style='padding:2px 8px 2px 0;'>".$pesanDebet."</td><td style='padding:2px 0;text-align:right;white-space:nowrap;'>Rp".number_format($absTransit,2)."</td></tr>"
        ."<tr><td style='padding:2px 8px 2px 0;'>Kredit</td><td style='padding:2px 8px 2px 0;'>".$pesanKredit."</td><td style='padding:2px 0;text-align:right;white-space:nowrap;'>Rp".number_format($absTransit,2)."</td></tr>";

    #= mode preview: cuma tampilkan rincian jurnal yang AKAN dibuat, belum insert apapun.
    #= <style> di sini melebarkan dialog alertify khusus popup ini (default
    #= .ajs-dialog max-width:500px kesempitan utk tabel 3 kolom kita, bikin
    #= kolom "numpuk"/wrap) - tidak menyentuh file CSS alertify yang shared.
    if($isPreview){
        exit("<style>.alertify .ajs-dialog{max-width:900px !important;width:900px !important;}</style>"
            ."<div style='text-align:left;'>"
            ."<b>Sumber selisih akun transit ".$label."</b>"
            .$pesanSumberHtml
            ."<hr>"
            ."<b>Jurnal yang akan dibuat</b> (langsung diposting, tanpa proses approval manual)"
            .pesanHeaderJurnal('(dibuat otomatis saat disimpan)',$tglJurnal,$refJurnal,$keterangan)
            ."<table style='width:100%;border-collapse:collapse;margin:4px 0 10px 0;'>".$pesanJurnal."</table>"
            ."<hr>"
            ."Lanjutkan ?"
            ."</div>");
    }

    try{
        $owlPDO->beginTransaction();

        #= header memorial - pola & counter sama seperti keu_jurnalmemorial_slave.php (case saveht)
        $kodejurnal = 'M';
        $qKonter = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            "kodekelompok='".$kodejurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."'");
        $tmpKonter = fetchData($qKonter);
        $konter = addZero($tmpKonter[0]['nokounter']+1,3);
        $nojurnal = str_replace('-','',$tglJurnal)."/".$param['kodeorg']."/".$kodejurnal."/".$konter;

        $str = "insert into ".$dbname.".keu_jurnalmemorial (nojurnal,kodejurnal,tanggal,tanggalentry,noreferensi,matauang,kurs,createby,createtime,updateby,autojurnal,kodeorg,revisi)
            values
            ('".$nojurnal."','".$kodejurnal."','".$tglJurnal."','".date('Ymd')."','".$refJurnal."',
            'IDR','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."',
            '1','".$param['kodeorg']."','0')";
        $owlPDO->exec($str);

        $str = "update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where
            kodeunit='".$param['kodeorg']."' and kodekelompok='".$kodejurnal."' and periode='".$param['periode']."'";
        $owlPDO->exec($str);

        #= detail memorial - 2 baris: akun Pembulatan vs akun transit Traksi
        $detail = array(
            array('noakun'=>$akunPembulatan, 'jumlah'=>$jmlPembulatan),
            array('noakun'=>$akunTransit,    'jumlah'=>$jmlTransit),
        );
        $nourut = 0;
        foreach($detail as $d){
            $nourut++;
            $str = "insert into ".$dbname.".keu_jurnalmemorialdt
                (nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,nik,noreferensi,revisi,createby,createtime,updateby)
                values
                ('".$nojurnal."','".$tglJurnal."','".$nourut."','".$d['noakun']."','".$keterangan."','".$d['jumlah']."','IDR','1',
                '".$param['kodeorg']."','0','".$refJurnal."','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."')";
            $owlPDO->exec($str);
        }

        #= langsung posting ke ledger asli (skip approval) - reuse pola case 'saveposting'
        #= di keu_jurnalmemorial_slave.php: salin header+detail ke keu_jurnalht/keu_jurnaldt,
        #= baru tandai memorial-nya posting=1
        $str = "insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,tanggal,tanggalentry,noreferensi,matauang,kurs,autojurnal)
            values
            ('".$nojurnal."','".$kodejurnal."','".$tglJurnal."','".date('Ymd')."','".$refJurnal."','IDR','1','1')";
        $owlPDO->exec($str);

        $nourut = 0;
        foreach($detail as $d){
            $nourut++;
            $str = "insert into ".$dbname.".keu_jurnaldt
                (nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,noreferensi,revisi)
                values
                ('".$nojurnal."','".$tglJurnal."','".$nourut."','".$d['noakun']."','".$keterangan."','".$d['jumlah']."','IDR','1',
                '".$param['kodeorg']."','".$refJurnal."','0')";
            $owlPDO->exec($str);
        }

        $str = "update ".$dbname.".keu_jurnalmemorial set posting='1' where nojurnal='".$nojurnal."'";
        $owlPDO->exec($str);

        $owlPDO->commit();

        echo "<style>.alertify .ajs-dialog{max-width:900px !important;width:900px !important;}</style>"
            ."<div style='text-align:left;'>"
            ."<b>Jurnal berhasil dibuat dan sudah diposting</b>"
            .pesanHeaderJurnal($nojurnal,$tglJurnal,$refJurnal,$keterangan)
            ."<table style='width:100%;border-collapse:collapse;margin:4px 0 10px 0;'>".$pesanJurnal."</table>"
            ."<hr>"
            ."Silahkan cek ulang tutup buku."
            ."</div>";
    }catch(PDOException $e){
        $owlPDO->rollback();
        echo "Warning:Gagal membuat jurnal otomatis, ".addslashes($e->getMessage());
    }
    exit;
}

$tipeorg         = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
$daftarbank      = makeOption($dbname,'keu_5akunbank','noakun,namabank');
$namabank        = makeOption($dbname,'keu_5daftarbank','kodebank,namabank');

$periodeberikut  = periodeberikut($param['periode']);
$tmpPeriodberikut= explode('-',$periodeberikut);
$awalberikut     = "awal".$tmpPeriodberikut[1];

$str="select ".$awalberikut." as awal,norek FROM ".$dbname.".keu_saldobank where  periode ='".str_replace("-", "", $periodeberikut)."' and kodeorg='".$param['kodeorg']."' and ".$awalberikut."<0 ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$datasalah=array();
if($numrows>0){
	while($bar=$res->fetch()){
		$datasalah['1'][].="Bank/Norek : ".$namabank[$daftarbank[$bar->norek]]." ".$bar->norek."; Jumlah : ".number_format($bar->awal,2)."<br>";
	}
}


#cek apakah unit dibawah RO tutup buku
if($tipeorg[$param['kodeorg']]=='KANWIL'){
    $sPt="select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
    $rPt=fetchData($sPt);
    $sDt="select kodeorganisasi from ".$dbname.".organisasi where induk='".$rPt[0]['induk']."' and tipe not in ('KANWIL','HOLDING') and namaorganisasi not like '%PLASMA%'";
    $rDt=fetchData($sDt);
    $unitAda=count($rDt);
    $scekAkutansi="select * from ".$dbname.".setup_periodeakuntansi 
                   where periode='".$param['periode']."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$rPt[0]['induk']."' and tipe not in ('KANWIL','HOLDING') and namaorganisasi not like '%PLASMA%') and tutupbuku=1";
    $rCekAkutansi=fetchData($scekAkutansi);
    $unitAkutansi=count($rCekAkutansi);
    if($unitAda!=$unitAkutansi){
        $scekAkutansi="select * from ".$dbname.".setup_periodeakuntansi 
                   where periode='".$param['periode']."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$rPt[0]['induk']."' and tipe not in ('KANWIL','HOLDING') and namaorganisasi not like '%PLASMA%') and tutupbuku=0";
        $rCekAkutansi=fetchData($scekAkutansi);
        $nod=1;
		$datasalah['2'][]="Ada unit dibawah ".$param['kodeorg']." belum tutup<br>";
        foreach($rCekAkutansi as $brs=>$val){
            $datasalah['2'][]=$nod.".".$val['kodeorg']."<br>";
            $nod+=1; 
        }
    }
} 


#================ cek setup_blok_tahunan sudah dilakukan / belum
if($tipeorg[$param['kodeorg']]=='KEBUN'){
    #cek bloknya ada atau gak
    $scekblok="select * from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['kodeorg']."'";
    $rcekblok=fetchData($scekblok);
    if(count($rcekblok)!=0){
        $str="SELECT count(*) as jumlah
        FROM ".$dbname.".`setup_blok_tahunan`
        WHERE `tahun` ='".$tahunbulan."' and 
        substr(kodeorg,1,4)='".$param['kodeorg']."'";
        $res=fetchData($str);
        $jumlah=$res[0]['jumlah'];
        if($jumlah=='0' || $jumlah==''){
            $datasalah['3'][]="Blok tahunan untuk periode ".$param['periode']." belum dilakukan, harap lakukan proses tutup buku areal statement di : kebun->proses->tutup aresta";
        }
    }		
}

if($tipeorg[$param['kodeorg']]=='PABRIK'){
	$str="SELECT * FROM ".$dbname.".`kebun_tbskud`
	WHERE `tanggal`  like '".$param['periode']."%' and  unit='".$param['kodeorg']."' and posting=0";
	// exit("Error:$str");
	$res=fetchData($str);
	$notbskud=0;
	$datasalah['4'][]="Ada Transaksi TBS Petani yang harus diposting<br>";
	foreach($res as $bar){
		$notbskud++;
		$datasalah['4'][]="Notransaksi : ".$bar['notransaksi']." ; ".$bar['tanggal']." ; ".$bar['divisi']."<br>";
	}
}

$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLM'");
$akunCLM='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunCLM=$bal->noakundebet;
}
//ambil akun laba ditahan
$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLY'");
$akunCLY='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunCLY=$bal->noakundebet;
}

//ambil batas bawah akun laba/rugi
$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='RAT'");
$akunRAT='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunRAT=$bal->noakundebet;
}
if($akunCLM=='' or $akunCLY=='' or $akunRAT==''){
	$datasalah['5'][]="data akun laba tahunan, akun laba ditahan dan batas akun laba/rugi belum terdaftar pada parameter jurnal";
}

#periksa apakah sudah diposting semua transaksi kas dan bappp
$str=$owlPDO->query("select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."'");
$currstart='';
$currend='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
    
if($currstart=='' or $currend==''){
    $datasalah['6'][]=$param['kodeorg'].' '.$_SESSION['lang']['periodetidaknormal'];
}else{
    
    #periksa apakah ada tagihan yang tipenya jurnal, belum diposting diperiode yang akan ditutup
    $str="select * from ".$dbname.".keu_tagihanht where unit='".$param['kodeorg']."' 
            and tanggal between '".$currstart."' and '".$currend."' and posting=0 and
            tipeinvoice in (select kode from ".$dbname.".keu_5jenistagihan where jurnal=1)";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $row=$res->rowCount();
    $res->setFetchMode(PDO::FETCH_ASSOC);
    if($row>0){
        $datasalah['7'][]="Ada Tagihan dengan status jurnal belum diposting :<br>";
        $no=0;
        while($bar=$res->fetch()){
            $no+=1;
            $datasalah['7'][]=$no.". No ".$bar['noinvoice']."<br>"; 
        }
    }
    
    #periksa periode kas kecil sudah tutup atau belum
    $str=$owlPDO->query("select close from ".$dbname.".keu_5kaskecil where periode='".$param['periode']."' and unit='".$param['kodeorg']."' and close=0 ");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($str);
    if($numrows>0){
        $datasalah['8'][]="Kas Kecil unit ".$param['kodeorg']." pada periode ".$param['periode']." belum tutup. <br>";
    }

    #periksa bapp
    $str="select notransaksi,tanggal,sum(jumlahrealisasi) as jumlahrealisasi from ".$dbname.".log_baspk where kodeblok like '".$param['kodeorg']."%'
          and tanggal between '".$currstart."' and '".$currend."' and statusjurnal=0 group by notransaksi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        $datasalah['9'][]=$_SESSION['lang']['cekspk']." :<br>";//
        $no=0;
        while($bar=$res->fetch()){
			$no+=1;
            $datasalah['9'][]=$no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlahrealisasi,0)."<br>"; 
        }
    }
    #periksa jurnal tidak balance
    $str="select nojurnal,tanggal,debet,kredit from ".$dbname.".keu_jurnal_tidak_balance_vw where kodeorg = '".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."'
          and nojurnal not like '%/CLSM/%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        $datasalah['10'][]=$_SESSION['lang']['notifjurnaltidakbalance']." :<br>";//
        $no=0;
        while($bar=$res->fetch()){
			$no+=1;
            $datasalah['10'][]=$no.". No ".$bar->nojurnal.":".tanggalnormal($bar->tanggal)."->(D)Rp. ".number_format($bar->debet,0).":(K)Rp. ".number_format($bar->kredit,0)."<br>"; 
        }
    }
    
    #periksa gudang
    $str="select notransaksi,tanggal, kodegudang from ".$dbname.".log_transaksiht where post=0 and hasilpersetujuan1!=2 and kodegudang like '".$param['kodeorg']."%'
            and tanggal between '".$currstart."' and '".$currend."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    $stm='';
    if($numrows>0){
		$datasalah['11'][]=$_SESSION['lang']['notifgudang']." <br>"; 
        while($bar=$res->fetch()){
			$datasalah['11'][]="Gudang:".$bar->kodegudang."->No.>".$bar->notransaksi."->".$bar->tanggal."<br>";
		}
    }

    #periksa apakah ada gudang yg belum tutup
    $str="select kodeorg,periode from ".$dbname.".setup_periodeakuntansi where kodeorg like '".$param['kodeorg']."%' and periode='".$param['periode']."' and tutupbuku=0 and char_length(kodeorg)=6 ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    $sgudang="";
    if($numrows>0){
		$datasalah['12'][]="Gudang belum tutup : <br>"; 
		$no='0';
        while($bar=$res->fetch()){
			$no++;
            $datasalah['12'][]=$no.". ".$bar->kodeorg." - ".getNamaOrg($bar->kodeorg)."<br>";
        }
    }
     
    #Periksa BKM
    $str="select notransaksi,tanggal from ".$dbname.".kebun_aktifitas where kodeorg='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and jurnal=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        $datasalah['13'][]=$_SESSION['lang']['notifbkm']." :<br>";
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            $datasalah['13'][]=$no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."<br>";
        }
    }
   #Periksa TRAKSI
    $str="select notransaksi,tanggal from ".$dbname.".vhc_runht where kodeorg='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        $datasalah['14'][]=$_SESSION['lang']['notiftraksi']."  :<br>";
        $no=0;
        while($bar=$res->fetch()){
			$no+=1;
            $datasalah['14'][]=$no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."<br>";
        }
    }  
    #Periksa REKAP PANEN
    $str="select distinct(divisi), tanggal from ".$dbname.".kebun_rekappnn_vw where substr(divisi,1,4)='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        $datasalah['15'][]=$_SESSION['lang']['notifrekappnn']."  :<br>";
        $no=0;
        while($bar=$res->fetch()){
			$no+=1;
            $datasalah['15'][]=$no.". Afdeling / Divisi => ".$bar->divisi." Tanggal => ".tanggalnormal($bar->tanggal)."<br>";
        }
    }
    $tipeLokasitugas=makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$param['kodeorg']."'");
    #Periksa Tutup Aresta
    if($tipeLokasitugas[$param['kodeorg']]=='KEBUN'){
        #cek bloknya ada atau gak
        $scekblok="select * from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['kodeorg']."'";
        $rcekblok=fetchData($scekblok);
        if(count($rcekblok)!=0){
                $str="select * from ".$dbname.".setup_blok_tahunan where kodeorg like '".$param['kodeorg']."%' and tahun = '".$tahunbulan."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $numrows=owlBaris($res);
                if($numrows==0){
                    $datasalah['16'][]="Silahkan lakukan Tutup Aresta melalui menu : Kebun - Proses - Tutup Aresta.";
                }
        }
    }
}   

#PERIKSA akun transit yang belum nol=============================
$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$param['kodeorg']."' AND noakun like '4%'";//exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$transit=0;
if($numrows>0){
	while($bar=$res->fetch()){
		$transit=abs($bar->saldo);
	}
}
if($transit>10 && $transit!=''){
    $datasalah['17'][]=$_SESSION['lang']['notifakuntransit']." (total gabungan seluruh kelompok akun transit kepala 4) :".number_format($transit)
        ."<br><i>Ini jumlah keseluruhan dari sub-kelompok akun transit (Workshop, Traksi, dll) - rincian dan cara penyelesaian tiap kelompok ada di baris di bawah.</i>";
}

$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$param['kodeorg']."' AND noakun like '41101%'";//exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$transit=0;
if($numrows>0){
	while($bar=$res->fetch()){
		$transit=abs($bar->saldo);  
	}
}
if($transit>10 && $transit!=''){
   $pesanWs = $_SESSION['lang']['notifakuntransit']." (Workshop) :".number_format($transit);

   #= tombol auto-jurnal khusus PT PPP: total < Rp200 DAN tiap karyawan < Rp20
   $sIndukWs = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
   $rIndukWs = fetchdata($sIndukWs);
   $indukWs = '';
   foreach($rIndukWs as $bar){ $indukWs = $bar['induk']; }

   $rBreakdownWs = getBreakdownKaryawan($dbname,$param['kodeorg'],$param['periode'],'41101%');
   $maxKaryawanWs = 0;
   foreach($rBreakdownWs as $b){ $maxKaryawanWs = max($maxKaryawanWs, abs($b['saldo'])); }

   $eligibleWs = ($indukWs=='PPP' && $transit<200 && $maxKaryawanWs<20);

   if($eligibleWs){
       $pesanWs .= "<br>Selisih ini layak jurnal otomatis (total < Rp200 dan tiap karyawan < Rp20) : "
           ."<button type=button class=mybutton onclick=\"buatJurnalAutoTransit('".$param['kodeorg']."','".$param['periode']."','workshop')\">Buat Jurnal Otomatis</button>"
           ."<br>Rincian sumbernya (Workshop) :"
           .pesanBreakdownSaja($dbname,$param['kodeorg'],$param['periode'],'41101%',$rBreakdownWs,$transit,20);
   }else{
       $pesanWs .= "<br><i>Selisih ini di luar rentang otomatis (total harus < Rp200 dan tiap karyawan < Rp20), wajib jurnal manual :</i>";
       $pesanWs .= pesanDiagnostikTransit($dbname,$param['kodeorg'],$param['periode'],'41101%','Workshop',$transit,$rBreakdownWs,20);
   }

   $datasalah['18'][]=$pesanWs;
}

#= transaksi
$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$param['kodeorg']."' AND noakun like '41102%'";//exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$transit=0;
if($numrows>0){
	while($bar=$res->fetch()){
		$transit=abs($bar->saldo);
	}
}
if($transit>10 && $transit!=''){
   $pesanTraksi = $_SESSION['lang']['notifakuntransit']." (Traksi) :".number_format($transit);

   #= tombol auto-jurnal khusus PT PPP: total < Rp200 DAN tiap karyawan < Rp20.
   #= Khusus Traksi, realokasi normal antar kendaraan bisa puluhan/ratusan
   #= juta per karyawan - itu diabaikan saat CEK KELAYAKAN ($batasNoiseTr),
   #= tapi tetap ikut ditampilkan (digabung 1 baris) di breakdown supaya
   #= totalnya tetap PERSIS sama dengan selisih keseluruhan.
   $sIndukTr = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
   $rIndukTr = fetchdata($sIndukTr);
   $indukTr = '';
   foreach($rIndukTr as $bar){ $indukTr = $bar['induk']; }

   $rBreakdownTr = getBreakdownKaryawan($dbname,$param['kodeorg'],$param['periode'],'41102%');
   $batasNoiseTr = 100000;
   $maxKaryawanTr = 0;
   foreach($rBreakdownTr as $b){
       if(abs($b['saldo'])<$batasNoiseTr){ $maxKaryawanTr = max($maxKaryawanTr, abs($b['saldo'])); }
   }

   $eligibleTr = ($indukTr=='PPP' && $transit<200 && $maxKaryawanTr<20);

   if($eligibleTr){
       $pesanTraksi .= "<br>Selisih ini layak jurnal otomatis (total < Rp200 dan tiap karyawan < Rp20) : "
           ."<button type=button class=mybutton onclick=\"buatJurnalAutoTransit('".$param['kodeorg']."','".$param['periode']."','traksi')\">Buat Jurnal Otomatis</button>"
           ."<br>Rincian sumbernya (Traksi) :"
           .pesanBreakdownSaja($dbname,$param['kodeorg'],$param['periode'],'41102%',$rBreakdownTr,$transit,20);
   }else{
       $pesanTraksi .= "<br><i>Selisih ini di luar rentang otomatis (total harus < Rp200 dan tiap karyawan < Rp20), wajib jurnal manual :</i>";
       $pesanTraksi .= pesanDiagnostikTransit($dbname,$param['kodeorg'],$param['periode'],'41102%','Traksi',$transit,$rBreakdownTr,20);
   }

   $datasalah['19'][]=$pesanTraksi;
}

/**************************************************************
 * [START] Cek Nilai Material VS Jurnal ***********************
 **************************************************************/
 
if($tipeorg[$param['kodeorg']]=='KANWIL'){
	
	// Get Kelompok Barang yang ada Akun
	$optKel = makeOption($dbname,'log_5klbarang',"kode,noakun","noakun!='' and noakun like '11504%'");
	$listKel = $listAkun = array();
	foreach($optKel as $kode=>$akun) {
		$listKel[] =  $kode;
		$listAkun[$akun] =  $akun;
	}

	// Get Nilai Material, log_5saldobulanan
	$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
		FROM ".$dbname.".log_5saldobulanan 
		WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".
			$param['kodeorg']."%' and periode like '".$param['periode']."%'
		GROUP BY left(kodebarang,3)";   
	$resSaldoMat = fetchData($qSaldoMat);
	$optSaldoMat = array();
	foreach($resSaldoMat as $row) {
		if(!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
			$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
		} else {
			$optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
		}
	}
	 

	// Get Nilai Jurnal, keu_saldobulanan
	$qSaldoJ ="SELECT awal".$bulan." as saldoawal,noakun
		FROM ".$dbname.".keu_saldobulanan
		WHERE kodeorg='".$param['kodeorg']."' and periode='".$tahunbulan."'
			and noakun in ('".implode("','",$listAkun)."')";
	$resSaldoJ = fetchData($qSaldoJ);
	$optSaldoJ = array();
	foreach($resSaldoJ as $row) {
		$optSaldoJ[$row['noakun']] = $row['saldoawal'];
	}

	// Get Transaksi Jurnal
	$qTrans ="SELECT sum(debet - kredit) as saldotrans, noakun
		FROM ".$dbname.".keu_jurnaldt_vw
		WHERE kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'
			and noakun in ('".implode("','",$listAkun)."')
		GROUP BY noakun";
	$resTrans = fetchData($qTrans);
	foreach($resTrans as $row) {
		if(!isset($optSaldoJ[$row['noakun']])) 
			$optSaldoJ[$row['noakun']] = 0;
		$optSaldoJ[$row['noakun']] += $row['saldotrans'];
	}

}else{
	 
	// Get Kelompok Barang yang ada Akun
	$optKel = makeOption($dbname,'log_5klbarang',"kode,noakun","noakun!='' and noakun like '11501%'");
	$listKel = $listAkun = array();
	foreach($optKel as $kode=>$akun) {
		$listKel[] =  $kode;
		$listAkun[$akun] =  $akun;
	}

	// Get Nilai Material, log_5saldobulanan
	$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
		FROM ".$dbname.".log_5saldobulanan 
		WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".
			$param['kodeorg']."%' and periode like '".$param['periode']."%'
		GROUP BY left(kodebarang,3)";   
	$resSaldoMat = fetchData($qSaldoMat);
	$optSaldoMat = array();
	foreach($resSaldoMat as $row) {
		if(!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
			$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
		} else {
			$optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
		}
	}
	 

	// Get Nilai Jurnal, keu_saldobulanan
	$qSaldoJ ="SELECT awal".$bulan." as saldoawal,noakun
		FROM ".$dbname.".keu_saldobulanan
		WHERE kodeorg='".$param['kodeorg']."' and periode='".$tahunbulan."'
			and noakun in ('".implode("','",$listAkun)."')";
	$resSaldoJ = fetchData($qSaldoJ);
	$optSaldoJ = array();
	foreach($resSaldoJ as $row) {
		$optSaldoJ[$row['noakun']] = $row['saldoawal'];
	}

	// Get Transaksi Jurnal
	$qTrans ="SELECT sum(debet - kredit) as saldotrans, noakun
		FROM ".$dbname.".keu_jurnaldt_vw
		WHERE kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'
			and noakun in ('".implode("','",$listAkun)."')
		GROUP BY noakun";
	$resTrans = fetchData($qTrans);
	foreach($resTrans as $row) {
		if(!isset($optSaldoJ[$row['noakun']])) 
			$optSaldoJ[$row['noakun']] = 0;
		$optSaldoJ[$row['noakun']] += $row['saldotrans'];
	}

}	


// Cek All Akun
$notBal = "";
foreach($listAkun as $akun) {
    if(!isset($optSaldoMat[$akun])) $optSaldoMat[$akun] = 0;
    if(!isset($optSaldoJ[$akun])) $optSaldoJ[$akun] = 0;
    
    $selisih = abs( abs($optSaldoMat[$akun]) - abs($optSaldoJ[$akun]) );
    if($selisih > 300) {
        $notBal .= $akun." - ".getNamaAkun($akun)." = ".number_format($selisih)."<br>";
    }
}

// Alert Jika ada yang belum balance

if ($param['periode']>'2018-02') {
    if(!empty($notBal)) {
        $datasalah['20'][]=$_SESSION['lang']['notifmaterial']." <br>".$notBal;
    }
}

/**************************************************************
 * [END] Cek Nilai Material VS Jurnal *************************
 **************************************************************/

if(substr($param['kodeorg'],2,2)!='HO'){
    $str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
              and nojurnal like '%".$param['kodeorg']."/KBN%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
    }else{
        $datasalah['21'][]=$_SESSION['lang']['notifprosesgaji'];
    }
}

$spbbelumdiinput='';
$query = $owlPDO->query("SELECT a.nospb, b.tanggal
    FROM ".$dbname.".`pabrik_timbangan` a
    LEFT JOIN ".$dbname.".kebun_spbht b ON a.nospb = b.nospb
    WHERE a.`tanggal` LIKE '".$param['periode']."%' and a.`kodeorg` = '".$param['kodeorg']."'
        AND b.`tanggal` is NULL");
$query->setFetchMode(PDO::FETCH_ASSOC);
while($rDetail=$query->fetch()){
    $spbbelumdiinput.=$rDetail['nospb'].', ';
}      
 
// if($spbbelumdiinput!=''){
//     $spbbelumdiinput=substr($spbbelumdiinput,0,-2);
//     exit("Warning :".$_SESSION['lang']['notifspbinput']."  : ".$spbbelumdiinput);
// }

#Periksa SPB vs WB
$str="select sum(kgwb) as kgwb from ".$dbname.".kebun_spb_vw where substr(divisi,1,4)='".$param['kodeorg']."' and tanggal between '".$currstart."' and '".$currend."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$kgspb='';
while($bar=$res->fetch()){
    $kgspb=$bar['kgwb'];
}

$str="select sum(beratbersih) as kgpks from ".$dbname.".pabrik_timbangan where kodeorg='".$param['kodeorg']."' 
and substr(tanggal,1,10) between '".$currstart."' and '".$currend."' and kodebarang='40000003'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$kgpks='';
while($bar=$res->fetch()){
    $kgpks=$bar['kgpks'];
}

$selisih = ($kgpks-$kgspb);
if($selisih>5)#lebih dari  5 kg
{
    //exit("Warning:  ".$_SESSION['lang']['notifspbvspks']." :<br>Kg SPB/SPATB = ".number_format($kgspb,0)." Kg, WB/PKS = ".number_format($kgpks)." Kg, Selisih = ".number_format($selisih)." Kg");
}

$spbbelumdiposting='';
$query = $owlPDO->query("SELECT nospb, tanggal
    FROM ".$dbname.".`kebun_spb_vw`
    WHERE `tanggal` LIKE '".$param['periode']."%' and `blok` like '".$param['kodeorg']."%'
        and posting = 0
        ");
$query->setFetchMode(PDO::FETCH_ASSOC);
while($rDetail=$query->fetch()){
    $spbbelumdiposting.=$rDetail['nospb'].', ';
}        
if($spbbelumdiposting!=''){
    $spbbelumdiposting=substr($spbbelumdiposting,0,-2);
    // echo "WARNING: ".$_SESSION['lang']['notifspbposting']." : ".$spbbelumdiposting;//
    // exit();
}
//============================================================================== END OF CEK SPB

/**************************************************************
 * [START] Cek Pengakuan Penjualan ****************************
 **************************************************************/		
if(substr($param['kodeorg'],2,2)=='HO'){
$listTiket="";
$qJual = "SELECT notransaksi,a.nokontrak
        FROM ".$dbname.".pabrik_timbangan a
        INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
        WHERE date(a.tanggal) between '".$currstart."' and '".$currend.
            "'and a.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='PABRIK')";
    $resJual = fetchData($qJual);
    if(!empty($resJual)) {
        $listTiket = '';
        foreach($resJual as $row) {
            $scek2="select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual 
                    where notransaksi='".$row['notransaksi']."'";
                    //exit("error:".$scek2);
            $qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
            $qcek2->setFetchMode(PDO::FETCH_OBJ); 
            $rcek2=owlBaris($qcek2);
            if($rcek2==0){
                $listTiket .= "- ".$row['notransaksi']."<br>";    
            }
        }
        if($listTiket!=''){
            $datasalah['22'][]=$_SESSION['lang']['notifpengakuanjual']." <br>".$listTiket;
        }
        
    }
}

if($tipeorg[$param['kodeorg']]=='KEBUN'){	
	#cek ada panen atau tidak ?
	$jumlahtranspanen=$jumlahtransrekap=0;
	$str = "select * from ".$dbname.".kebun_aktifitas where kodeorg='".$param['kodeorg']."' and tanggal between '".$currstart."' and '".$currend."' and tipetransaksi='PNN' and deviceid is null and jurnal='1'";
	$res = fetchData($str);
	foreach($res as $bar){
        if($bar['tipe']=='KG'){
            if($bar['noreferensi']==''){
			    $jumlahtranspanen++;
            }
            if($bar['noreferensi']!=''){
                $jumlahtransrekap++;
            }
        }
	}
	if($jumlahtranspanen>0 and $jumlahtransrekap==0){
		$datasalah['23'][]="Proses rekap premi panen belum dilakukan atau belum diposting.";
	}
	
	$data=array();
	$str = "select substr(b.kodeorg,1,6) as divisi, tanggal, count(a.notransaksi) as jumlah 
	from ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
	where a.kodeorg='".$param['kodeorg']."' and a.tanggal between '".$currstart."' and '".$currend."' and a.noreferensi='' and a.tipetransaksi='PNN' and a.tipe='KG' group by substr(b.kodeorg,1,6), tanggal";
    $res = fetchData($str);
	foreach($res as $bar){
		$panen[$bar['divisi']][$bar['tanggal']]=$bar['jumlah'];
		$data[$bar['divisi']][$bar['tanggal']]=$bar['tanggal'];
	}
	$str = "select substr(b.kodeorg,1,6) as divisi, tanggal, count(a.notransaksi) as jumlah 
	from ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
	where a.kodeorg='".$param['kodeorg']."' and a.tanggal between '".$currstart."' and '".$currend."' and a.noreferensi!='' and a.tipetransaksi='PNN' and a.tipe='KG' group by substr(b.kodeorg,1,6), tanggal";
	$res = fetchData($str);
	foreach($res as $bar){
		$premip[$bar['divisi']][$bar['tanggal']]=$bar['jumlah'];
		$data[$bar['divisi']][$bar['tanggal']]=$bar['tanggal'];
	}
	$listsalah="";
	foreach($data as $divisi => $v){
		foreach($v as $tanggal){
			if($panen[$divisi][$tanggal]>0 and $premip[$divisi][$tanggal]<=0){
				$listsalah.="Divisi ".getNamaOrg($divisi).", tanggal ".tanggalnormal($tanggal)."<br>";
			}
		}
	}
	 if($listsalah!=''){
		$datasalah['24'][]="Proses Premi Pemanen belum dilakukan <br>".$listsalah;
	}
}


#================ cek alokasi gaji sudah dilakukan / belum
$where = "AND a.kodeorg = '".$param['kodeorg']."'";
$wh = "AND kodeorg = '".$param['kodeorg']."'";

$getkaryawan = "SELECT a.kodeorg, a.periodegaji, a.karyawanid, b.namakaryawan, b.nik
				FROM ".$dbname.".sdm_gajidetail_vw a
				JOIN ".$dbname.".datakaryawan_hist b ON a.karyawanid = b.karyawanid
				WHERE a.periodegaji = '".$param['periode']."' and b.periodegaji = '".$param['periode']."' 
                and b.version_type = 'B'and a.idkomponen!='42' ".$where."
				GROUP BY a.kodeorg, a.periodegaji, a.karyawanid order by b.namakaryawan";
				// echo $getkaryawan;
$reskaryawan = fetchdata($getkaryawan);
$num = 0; $wherekarid = ''; $arrkaryawan = array();
foreach ($reskaryawan as $key => $val) {
	if($num == 0) {
		$wherekarid .= "'".$val['karyawanid']."'";
	} else {
		$wherekarid .= ",'".$val['karyawanid']."'";
	}
	$num++;
}

if($wherekarid!=''){
	## GET JUMLAH GAJI PLUS
	$getjumlahplus = "SELECT sum(jumlah) as jmlh
					 FROM ".$dbname.".sdm_gajidetail_vw
					 WHERE plus = 1 AND periodegaji = '".$param['periode']."' AND karyawanid in (".$wherekarid.")";
    // if ($_SESSION['standard']['userid'] == '0000000001') {
    //     echo $getjumlahplus."<br/>";
    // }
	$resjumlahplus = fetchdata($getjumlahplus);
	$arrjmlhplus = 0;
	foreach ($resjumlahplus as $key => $val) {
		$arrjmlhplus += $val['jmlh'];
	}

	## GET JUMLAH GAJI MINUS
	$getjumlahminus = "SELECT sum(jumlah) as jmlh
					 FROM ".$dbname.".sdm_gajidetail_vw
					 WHERE plus = 0 AND periodegaji = '".$param['periode']."' AND karyawanid in (".$wherekarid.") ".$wh."";
	$resjumlahminus = fetchdata($getjumlahminus);
	$arrjmlhminus = 0;
	foreach ($resjumlahminus as $key => $val) {
		$arrjmlhminus += $val['jmlh'];
	}
## GET TOTAL ALOKASI PLUS
$getalokplus = "SELECT sum(kredit) as debet
				FROM ".$dbname.".keu_jurnaldt_vw 
				WHERE noakun in (SELECT noakunkredit FROM ".$dbname.".keu_5pengakuanpotongan) AND periode = '".$param['periode']."' 
                AND nik !='' AND nik in (".$wherekarid.")
				AND noreferensi LIKE 'ALK_POT%' and nojurnal not like '%/M/%'";
$resalokplus = fetchdata($getalokplus);
$arralokplus = 0;
foreach ($resalokplus as $key => $val) {
	$arralokplus += $val['debet'];
}   

## GET TOTAL ALOKASI MINUS
$getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
                FROM ".$dbname.".keu_jurnaldt_vw 
                WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
                AND periode = '".$param['periode']."'
                AND nik !=''
                AND nik in (".$wherekarid.")
                AND jumlah > 0
                AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%' 
                and nojurnal like '%PNN%' 
                and autojurnal='1'
                and keterangan like '%potong buah dan premi panen%'";
// if ($_SESSION['standard']['userid'] == '0000000001') {
//     echo $getalokminus."<br/>";
// }
$resalokminus = fetchdata($getalokminus);
foreach ($resalokminus as $key => $val) {
    $arralokminus += ($val['debet']*-1);
}

// $getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
//                                 FROM ".$dbname.".keu_jurnaldt_vw 
//                                 WHERE noakun not like '213%'
//                                 AND periode = '".$param['periode']."'
//                                 AND nik !='' 
//                                 AND nik in (".$wherekarid.")
//                                 AND jumlah > 0
//                                 AND kodejurnal in ('BM01')
//                                 AND autojurnal='1'
//                                 AND noaruskas=''
//                                 GROUP BY nik";
$getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
                                    FROM ".$dbname.".keu_jurnaldt_vw 
                                    WHERE noakun not like '213%'
                                    AND periode = '".$param['periode']."'
                                    AND nik !='' AND nik!='0000000000' ".$wh." 
                                    AND jumlah > 0
                                    AND kodejurnal in ('BM01')
                                    AND autojurnal='1'
                                    AND noaruskas=''
                                    GROUP BY nik";
// if ($_SESSION['standard']['userid'] == '0000000001') {
//     echo $getalokminus."<br/>";
// }
$resalokminus = fetchdata($getalokminus);
foreach ($resalokminus as $key => $val) {
    if(getKaryHist($val['nik'],$param['periode'],'lokasitugas') != $param['kodeorg']) {
        //echo getKaryHist($val['nik'],$periode,'lokasitugas').'!='.$param['kodeorg'].'<br>';
        continue;
    }
    $arralokminus +=$val['debet'];
    

}

## GET TOTAL ALOKASI MINUS
$getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
                FROM ".$dbname.".keu_jurnaldt_vw 
                WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
                AND periode = '".$param['periode']."'
                AND nik !='' 
                AND nik in (".$wherekarid.")
                AND jumlah > 0
                AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%' 
                and nojurnal like '%KBNB0%' 
                and autojurnal='1'";
// if ($_SESSION['standard']['userid'] == '0000000001') {
//     echo $getalokminus."<br/>";
// }
$resalokminus = fetchdata($getalokminus);
foreach ($resalokminus as $key => $val) {
    $arralokminus += ($val['debet']*-1);
}   

## GET TOTAL ALOKASI MINUS
$getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
                FROM ".$dbname.".keu_jurnaldt_vw 
                WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
                AND periode = '".$param['periode']."'
                AND nik !='' 
                AND nik in (".$wherekarid.")
                AND jumlah > 0
                AND noreferensi LIKE 'ALK_GAJI_TERTINGGAL%' 
                AND nojurnal like '%/M/%'
                AND autojurnal='1'
                AND noaruskas=''";
// if ($_SESSION['standard']['userid'] == '0000000001') {
//     echo $getalokminus."<br/>";
// }
$resalokminus = fetchdata($getalokminus);
foreach ($resalokminus as $key => $val) {
    $arralokminus += ($val['debet']*-1);
}

## GET TOTAL ALOKASI MINUS
$getalokminus = "SELECT nik, sum(kredit) as kredit, kodeorg
                FROM ".$dbname.".keu_jurnaldt_vw 
                WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
                AND periode = '".$param['periode']."'
                AND nik !='' 
                AND nik in (".$wherekarid.")
                AND jumlah < 0
                AND noreferensi NOT LIKE 'ALK_POT%' 
                AND nojurnal like '%/M/%'
                AND autojurnal='1'
                AND noaruskas=''";
// if ($_SESSION['standard']['userid'] == '0000000001') {
//     echo $getalokminus."<br/>";
// }
$resalokminus = fetchdata($getalokminus);
foreach ($resalokminus as $key => $val) {
    $arralokminus += $val['kredit'];
}   

## GET TOTAL ALOKASI MINUS
$getalokminus = "SELECT sum(kredit) as kredit
				FROM ".$dbname.".keu_jurnaldt_vw 
				WHERE noakun like '213%' and substr(noakun,1,5) < '21302' 
                AND periode = '".$param['periode']."' 
                AND nik !='' 
                AND nik in (".$wherekarid.")
                AND jumlah < 0 
                AND noreferensi NOT LIKE 'ALK_POT%' 
                and nojurnal not like '%/M/%'";
// if ($_SESSION['standard']['userid'] == '0000000001') {
//     echo $getalokminus."<br/>";
// }
$resalokminus = fetchdata($getalokminus);
// $arralokminus = 0;
foreach ($resalokminus as $key => $val) {
	$arralokminus += $val['kredit'];
}

// if ($_SESSION['standard']['userid'] == '0000000001') {
//     echo $arrjmlhplus.";";
//     echo "<br/>";
//     echo $arralokminus.";";
//     echo "<br/>";
//     echo $arrjmlhminus.";";
//     echo "<br/>";
//     echo $arralokplus.";";
//     echo "<br/>";
// }


$selisihplus = $arrjmlhplus-$arralokminus;
$selisihminus = $arrjmlhminus-$arralokplus;
$selisihtotal = $selisihplus-$selisihminus;

if(abs($selisihtotal)>200){//arahan bang ari samakan nilai toleransi dengan yang ada ditutup buku
	$datasalah['25'][]="Gaji belum terlalokasi seluruhnya terdapat selisih ".$selisihtotal.", silahkan cek laporan Keuangan - Laporan - Transaksi Lainnya - Daftar Alokasi Gaji.<br>Untuk mengalokasikan gaji silahkan lakukan proses pada menu Keuangan - Proses - Proses Akhir Bulan";
}
}else{
	$datasalah['25'][]="Kami tidak menemukan data penggajian, apakah Proses penggajian sudah dilakukan ?<br>Jika belum silahkan lakukan proses : SDM - Proses - Penggajian Bulanan dan Harian.";
}


echo"
	<table cellspacing=1 cellpadding=5 border=0 class=sortable>
		<thead>
			<tr class=rowheader>
				<th align=center>No</th>
				<th align=center>Keterangan</th>
			</tr>
		</thead>
	<tbody>
	";
	
if(!empty($datasalah)){	
	$no=0;
	foreach($datasalah as $urut => $val1){
		$no++;
		foreach($val1 as $key => $keterangan){
			$d=$no;
			echo"<tr class=rowcontent style=vertical-align:top;>";
			if($d!=$n){			
				echo"<td align=center >".$no."</td>";
			}else{
				echo"<td align=center></td>";
			}
			echo"<td align=left >".$keterangan."</td>";
			echo"</tr>";
			$n=$d;
		}
	}
}else{
	echo"<tr class=rowcontent>
		<td align=center colspan=2>Data sudah benar, silahkan lanjut untuk tutup buku.</td>
	";
}
?>