<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

try {
    $owlPDO->beginTransaction();

    $param = $_POST;

    $dataorg = array();
    $dtstr = "select * from " . $dbname . ".organisasi where  kodeorganisasi = '" . $param['kodeorg'] . "'";
    $str = $owlPDO->query($dtstr);
    $str->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $str->fetch()) {
        $dataorg[$bar->kodeorganisasi] = $bar;
    }
    $tanggal = $param['periode'] . "-28";

    $strht = "select induk from " . $dbname . ".organisasi where  kodeorganisasi='" . $param['kodeorg'] . "'";
    $resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
    $resht->setFetchMode(PDO::FETCH_ASSOC);
    $barht = $resht->fetch();
    $orgpt = $barht['induk'];

    // exit("Error:".$param['kodeorg']._.$param['kodeorg']);
    $param['kodeorg'] = trim($param['kodeorg']);
    #parameter
    #periode
    #kodevhc
    #jumlah
    #jenis (ALK_BY_WS atau ALK_KERJA_AB)

    // Default Segment
    $defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');


    if ($param['jenis'] == 'BYWS') {
        #=================================================================================
        #hapus dulu alokasi  untuk kendaraan yang sama pada periode yang sama jika sudah pernah di proses:

        #= ws unit sendiri
        $str = "select distinct a. nojurnal from " . $dbname . ".keu_jurnaldt a 
					  left join " . $dbname . ".keu_jurnalht b on a.nojurnal=b.nojurnal
			  where a.noreferensi='ALK_BY_WS' 
					  and (b.amountkoreksi='0' or b.amountkoreksi is null)
			  and a.kodevhc='" . $param['kodevhc'] . "' and a.tanggal='" . $tanggal . "' and kodeorg='" . $param['kodeorg'] . "'";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $bar->nojurnal . "'";
            $owlPDO->exec($str);
        }

        #= kiriman untuk unit lain
        $str = "select distinct a. nojurnal from " . $dbname . ".keu_jurnaldt a 
					  left join " . $dbname . ".keu_jurnalht b on a.nojurnal=b.nojurnal
			  where a.noreferensi='ALK_BY_WS' 
					  and (b.amountkoreksi='" . $param['kodeorg'] . "')
			  and a.kodevhc='" . $param['kodevhc'] . "' and a.tanggal='" . $tanggal . "'";
        // exit("Error:".$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $bar->nojurnal . "'";
            $owlPDO->exec($str);
        }

        /*
	$str="select distinct a. nojurnal from ".$dbname.".keu_jurnaldt a 
					  left join ".$dbname.".keu_jurnalht b on a.nojurnal=b.nojurnal
			  where a.noreferensi='ALK_BY_WS' 
					  and (b.amountkoreksi='0' or b.amountkoreksi is null 
					  or b.amountkoreksi='".$param['kodeorg']."')
			  and a.kodevhc='".$param['kodevhc']."' and a.tanggal='".$tanggal."'";
		  
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$bar->nojurnal."'";
		$owlPDO->exec($str);
	}
	*/
        #============================================================================
        prosesByBengkel();


        /*
	if($param['nourut']=='1'){
		$str="delete from ".$dbname.".keu_jurnalht where noreferensi='ALK_BY_WS' and nojurnal like '%".$param['kodeorg']."%' and tanggal='".$tanggal."' ";
		$owlPDO->exec($str);
	}
	prosesByBengkel();
*/
    } else if ($param['jenis'] == 'ALKJAM') {
        #=================================================================================
        #hapus dulu alokasi  untuk kendaraan yang sama pada periode yang sama jika sudah pernah di proses:

        $str = "select distinct nojurnal from " . $dbname . ".keu_jurnaldt where noreferensi='ALK_KERJA_AB'
			  and kodevhc='" . $param['kodevhc'] . "' and tanggal='" . $tanggal . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $bar->nojurnal . "'";
            $owlPDO->exec($str);
        }
        #============================================================================
        prosesAlokasi();

        /*
	if($param['nourut']=='1'){
		$str="delete from ".$dbname.".keu_jurnalht where noreferensi='ALK_KERJA_AB' and nojurnal like '%".$param['kodeorg']."%' and tanggal='".$tanggal."' ";
		$owlPDO->exec($str);
	}
	prosesAlokasi();  
	*/
    } else if ($param['jenis'] == 'BYWSMM') {
        #hapus dulu alokasi ke station

        $str = "select distinct a. nojurnal from " . $dbname . ".keu_jurnaldt a 
					  left join " . $dbname . ".keu_jurnalht b on a.nojurnal=b.nojurnal
			  where a.noreferensi='ALK_MAINTENANCE' 
					  and (b.amountkoreksi='0' or b.amountkoreksi is null 
					  or b.amountkoreksi='" . $param['kodeorg'] . "')
			  and a.kodeblok='" . $param['kodevhc'] . "' and a.tanggal='" . $tanggal . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $bar->nojurnal . "'";
            $owlPDO->exec($str);
        }
        #============================================================================
        prosesmaintenance();

        /*
	if($param['nourut']=='1'){
		$str="delete from ".$dbname.".keu_jurnalht where noreferensi='ALK_MAINTENANCE' and nojurnal like '%".$param['kodeorg']."%' and tanggal='".$tanggal."' ";
		$owlPDO->exec($str);
	}
	prosesmaintenance();
	*/
    } else {
        throw new PDOException("TIDAK ADA PROSES ALOKASI");
    }


    #execute
    $owlPDO->commit();
} catch (PDOException $e) {
    $owlPDO->rollback();
    echo "Error, " . addslashes($e->getMessage());
    die();
}

function prosesmaintenance()
{
    global $conn;
    global $tanggal;
    global $param;
    global $dbname;
    global $defSegment;
    global $owlPDO;
    global $orgpt;

    $kodeJurnal = 'PKS99';

    $str = "select * from " . $dbname . ".keu_5parameterjurnal where 
				kodeaplikasi='PKS' and jurnalid='" . $kodeJurnal . "'";
    // exit("Error:$str");
    $res = $owlPDO->query($str);
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $bar = $res->fetch();
    $akundebet = $bar['noakundebet'];
    $akunkredit = $bar['noakunkredit'];

    if ($akundebet == '' || $akunkredit == '') {
        throw new PDOException("No.Akun pada parameterjurnal belum ada untuk PKS99");
    }


    #======================== Nomor Jurnal =============================
    # Get Journal Counter
    $queryJ = selectQuery(
        $dbname,
        'keu_5kelompokjurnal',
        'nokounter',
        "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "' "
    );
    $tmpKonter = fetchData($queryJ);
    $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

    # Transform No Jurnal dari No Transaksi
    $nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter;

    $param['jumlah'] = $param['jumlah'] + $param['jumlahpembulatan'];

    # Prep Header
    $dataRes['header'] = array(
        'nojurnal' => $nojurnal,
        'kodejurnal' => $kodeJurnal,
        'tanggal' => $tanggal,
        'tanggalentry' => date('Ymd'),
        'posting' => 1,
        'totaldebet' => $param['jumlah'],
        'totalkredit' => -1 * $param['jumlah'],
        'amountkoreksi' => '0',
        'noreferensi' => 'ALK_MAINTENANCE',
        'autojurnal' => '1',
        'matauang' => 'IDR',
        'kurs' => '1',
        'revisi' => '0'
    );


    # Data Detail
    $noUrut = 1;

    # Debet
    $dataRes['detail'][] = array(
        'nojurnal' => $nojurnal,
        'tanggal' => $tanggal,
        'nourut' => $noUrut,
        'noakun' => $akundebet,
        'keterangan' => 'Alokasi  maintenance Periode:' . $param['periode'] . ' Station:' . $param['kodevhc'] . ' ',
        'jumlah' => $param['jumlah'],
        'matauang' => 'IDR',
        'kurs' => '1',
        'kodeorg' => $param['kodeorg'],
        'kodekegiatan' => '',
        'kodeasset' => '',
        'kodebarang' => '',
        'nik' => '',
        'kodecustomer' => '',
        'kodesupplier' => '',
        'noreferensi' => 'ALK_MAINTENANCE',
        'noaruskas' => '',
        'kodevhc' => '',
        'nodok' => '',
        'kodeblok' => $param['kodevhc'],
        'revisi' => '0',
        'kodesegment' => $defSegment
    );
    $noUrut++;


    # Kredit
    $dataRes['detail'][] = array(
        'nojurnal' => $nojurnal,
        'tanggal' => $tanggal,
        'nourut' => $noUrut,
        'noakun' => $akunkredit,
        'keterangan' => 'Alokasi maintenance Periode:' . $param['periode'] . ' Station:' . $param['kodevhc'] . ' ',
        'jumlah' => $param['jumlah'] * -1,
        'matauang' => 'IDR',
        'kurs' => '1',
        'kodeorg' => $param['kodeorg'],
        'kodekegiatan' => '',
        'kodeasset' => '',
        'kodebarang' => '',
        'nik' => '',
        'kodecustomer' => '',
        'kodesupplier' => '',
        'noreferensi' => 'ALK_MAINTENANCE',
        'noaruskas' => '',
        'kodevhc' => '',
        'nodok' => '',
        'kodeblok' => $param['kodevhc'],
        'revisi' => '0',
        'kodesegment' => $defSegment
    );
    $noUrut++;

    $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
    $owlPDO->exec($insHead);


    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
    foreach ($dataRes['detail'] as $row) {
        $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
        $owlPDO->exec($insDet);
    }

    # Header and Detail inserted
    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
    $updJurnal = updateQuery(
        $dbname,
        'keu_5kelompokjurnal',
        array('nokounter' => $konter),
        "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
    );
    $owlPDO->exec($updJurnal);
}




function prosesByBengkel()
{
    global $conn;
    global $tanggal;
    global $param;
    global $dbname;
    global $defSegment;
    global $owlPDO;
    global $orgpt;

    #output pada jurnal kolom noreferensi ALK_BY_WS  
    $group = 'WS2';
    $str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal  where jurnalid='" . $group . "' limit 1";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    if ($numrows < 1) {
        throw new PDOException("No.Akun pada parameterjurnal belum ada untuk WS2");
    } else {
        $akundebet = '';
        $akunkredit = '';
        $bar = $res->fetch();
        $akundebet = $bar->noakundebet;
        $akunkredit = $bar->noakunkredit;
    }

    #periksa apakah kendaraan dalam satu unit dengan workshop
    #periode
    #kodevhc
    #jumlah

    #ambil kode traksi  kendaraan bersangkutan
    $status = 'A'; #default adalah 1 unit kerja
    $str = " select kodetraksi from " . $dbname . ".vhc_5master_hist where kodevhc='" . $param['kodevhc'] . "' and periode='" . $param['periode'] . "'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $kodetrk = $bar->kodetraksi;
    }
    if ($param['kodeorg'] != substr($kodetrk, 0, 4)) {
        $status = 'B'; #beda unit kerja dalam satu PT
    }
    #periksa apakah 1 PT
    $str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . substr($kodetrk, 0, 4) . "'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $ptkend = $bar->induk;
    }
    if ($orgpt != substr($ptkend, 0, 3)) {
        $status = 'C'; #beda unit kerja beda PT
    }


    #= jumlahkan dengan pembulatan untuk tampungan

    $param['jumlah'] = $param['jumlah'] + $param['jumlahpembulatan'];


    if ($status == 'A') { #satu unit kerja
        #proses data
        $kodeJurnal = $group;
        #======================== Nomor Jurnal =============================
        # Get Journal Counter 
        $queryJ = selectQuery(
            $dbname,
            'keu_5kelompokjurnal',
            'nokounter',
            "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
        );
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

        # Transform No Jurnal dari No Transaksi
        $nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter;

        #======================== /Nomor Jurnal ============================
        # Prep Header
        $dataRes['header'] = array(
            'nojurnal' => $nojurnal,
            'kodejurnal' => $kodeJurnal,
            'tanggal' => $tanggal,
            'tanggalentry' => date('Ymd'),
            'posting' => 1,
            'totaldebet' => $param['jumlah'],
            'totalkredit' => -1 * $param['jumlah'],
            'amountkoreksi' => 0,
            'noreferensi' => 'ALK_BY_WS',
            'autojurnal' => '1',
            'matauang' => 'IDR',
            'kurs' => '1',
            'revisi' => '0'
        );
        # Data Detail
        $noUrut = 1;

        # Debet
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => $tanggal,
            'nourut' => $noUrut,
            'noakun' => $akundebet,
            'keterangan' => 'Biaya Bengkel/Reprasi ' . $param['kodevhc'],
            'jumlah' => $param['jumlah'],
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $param['kodeorg'],
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'ALK_BY_WS',
            'noaruskas' => '',
            'kodevhc' => $param['kodevhc'],
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;

        # Kredit
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => $tanggal,
            'nourut' => $noUrut,
            'noakun' => $akunkredit,
            'keterangan' => 'Alokasi biaya bengkel ke ' . $param['kodevhc'],
            'jumlah' => -1 * $param['jumlah'],
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $param['kodeorg'],
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'ALK_BY_WS',
            'noaruskas' => '',
            'kodevhc' => $param['kodevhc'],
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;

        $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
        $owlPDO->exec($insHead);

        foreach ($dataRes['detail'] as $row) {
            $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
            $owlPDO->exec($insDet);
        }
        # Header and Detail inserted
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
        $updJurnal = updateQuery(
            $dbname,
            'keu_5kelompokjurnal',
            array('nokounter' => $konter),
            "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
        );
        $owlPDO->exec($updJurnal);
    } else {
        #jika tidak dalam satu unit kerja maka akan ada hubungan RK
        #Periksa apakah unit tujuan sudah tutup buku:
        $str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' 
					   and kodeorg='" . substr($kodetrk, 0, 4) . "'";
        $close = '0';
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $close = $bar->tutupbuku;
        }
        if ($close == '1') {
            throw new PDOException("Unit " . substr($kodetrk, 0, 4) . ' has been closed');
        }
        #ambil akun pengguna:
        $str = "select akunpiutang,jenis from " . $dbname . ".keu_5caco where kodeorg='" . substr($kodetrk, 0, 4) . "'";
        $intraco = '';
        $interco = '';
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            if ($bar->jenis == 'intra')
                $intraco = $bar->akunpiutang;
            else
                $interco = $bar->akunpiutang;
        }

        if ($intraco == '' || $interco == '') {
            throw new PDOException("Account intraco or interco not available for " . substr($kodetrk, 0, 4) . ". Please setting on menu Finance > setup > COA for Intra/Interco.");
        }

        #ambil akun pemilik workshop
        $str = "select akunhutang,jenis from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "'";
        $intraco1 = '';
        $interco1 = '';
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            if ($bar->jenis == 'intra')
                $intraco1 = $bar->akunhutang;
            else
                $interco1 = $bar->akunhutang;
        }

        if ($intraco == '' || $interco == '') {
            throw new PDOException("Account intraco or interco not available for " . $param['kodeorg'] . ". Please setting on menu Finance > setup > COA for Intra/Interco.");
        }

        if ($status == 'C') {
            $akunspl = $interco1;
            $inter = $interco;
        }
        if ($status == 'B') {
            $akunspl = $intraco1;
            $inter = $intraco;
        }
        if ($akunspl == '' or $inter == '') {
            throw new PDOException("Account number for working unit not defined on Parameterjurnal");
        }


        #proses jurnal sisi pemilik
        $kodeJurnal = $group;
        #======================== Nomor Jurnal =============================
        # Get Journal Counter
        $queryJ = selectQuery(
            $dbname,
            'keu_5kelompokjurnal',
            'nokounter',
            "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and 
					kodeunit='" . trim($param['kodeorg']) . "' 
					and periode='" . $param['periode'] . "'  "
        );
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

        # Transform No Jurnal dari No Transaksi
        $nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter;
        $noroll = $nojurnal;
        #======================== /Nomor Jurnal ============================
        # Prep Header
        $dataRes['header'] = array(
            'nojurnal' => $nojurnal,
            'kodejurnal' => $kodeJurnal,
            'tanggal' => $tanggal,
            'tanggalentry' => date('Ymd'),
            'posting' => 1,
            'totaldebet' => $param['jumlah'],
            'totalkredit' => -1 * $param['jumlah'],
            'amountkoreksi' => $param['kodeorg'],
            'noreferensi' => 'ALK_BY_WS',
            'autojurnal' => '1',
            'matauang' => 'IDR',
            'kurs' => '1',
            'revisi' => '0'
        );
        # Data Detail
        $noUrut = 1;

        # Debet
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => $tanggal,
            'nourut' => $noUrut,
            'noakun' => $inter,
            'keterangan' => 'Biaya Bengkel/Reprasi ' . $param['kodevhc'] . '-' . $kodetrk,
            'jumlah' => $param['jumlah'],
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $param['kodeorg'],
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'ALK_BY_WS',
            'noaruskas' => '',
            'kodevhc' => '',
            'nodok' => '',
            'kodeblok' => $param['kodevhc'],
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;

        # Kredit
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => $tanggal,
            'nourut' => $noUrut,
            'noakun' => $akunkredit,
            'keterangan' => 'Alokasi biaya bengkel ke ' . $param['kodevhc'] . '-' . $kodetrk,
            'jumlah' => -1 * $param['jumlah'],
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $param['kodeorg'],
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'ALK_BY_WS',
            'noaruskas' => '',
            'kodevhc' => $param['kodevhc'],
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;

        $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
        $owlPDO->exec($insHead);

        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
        foreach ($dataRes['detail'] as $row) {
            $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
            $owlPDO->exec($insDet);
        }
        # Header and Detail inserted
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
        $updJurnal = updateQuery(
            $dbname,
            'keu_5kelompokjurnal',
            array('nokounter' => $konter),
            "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
        );
        $owlPDO->exec($updJurnal);


        #===============================================
        #Proses jurnal sisi pengguna
        unset($dataRes['detail']); #kosongkan detail
        #proses data
        $kodeJurnal = $group;
        #======================== Nomor Jurnal =============================
        # Get Journal Counter
        $queryJ = selectQuery(
            $dbname,
            'keu_5kelompokjurnal',
            'nokounter',
            "kodeorg='" . substr($ptkend, 0, 3) . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . trim(substr($kodetrk, 0, 4)) . "' and periode='" . $param['periode'] . "' "
        );
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

        # Transform No Jurnal dari No Transaksi
        $nojurnal = str_replace("-", "", $tanggal) . "/" . substr($kodetrk, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
        #======================== /Nomor Jurnal ============================
        # Prep Header
        $dataRes['header'] = array(
            'nojurnal' => $nojurnal,
            'kodejurnal' => $kodeJurnal,
            'tanggal' => $tanggal,
            'tanggalentry' => date('Ymd'),
            'posting' => 1,
            'totaldebet' => $param['jumlah'],
            'totalkredit' => -1 * $param['jumlah'],
            'amountkoreksi' => $param['kodeorg'],
            'noreferensi' => 'ALK_BY_WS',
            'autojurnal' => '1',
            'matauang' => 'IDR',
            'kurs' => '1',
            'revisi' => '0'
        );
        # Data Detail
        $noUrut = 1;

        # Debet
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => $tanggal,
            'nourut' => $noUrut,
            'noakun' => $akundebet,
            'keterangan' => 'Biaya Bengkel/Reprasi ' . $param['kodevhc'],
            'jumlah' => $param['jumlah'],
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => substr($kodetrk, 0, 4),
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'ALK_BY_WS',
            'noaruskas' => '',
            'kodevhc' => $param['kodevhc'],
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;

        # Kredit
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => $tanggal,
            'nourut' => $noUrut,
            'noakun' => $akunspl,
            'keterangan' => 'Alokasi biaya bengkel ke ' . $param['kodevhc'],
            'jumlah' => -1 * $param['jumlah'],
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => substr($kodetrk, 0, 4),
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'ALK_BY_WS',
            'noaruskas' => '',
            'kodevhc' => $param['kodevhc'],
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;

        $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
        $owlPDO->exec($insHead);

        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
        foreach ($dataRes['detail'] as $row) {
            $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
            $owlPDO->exec($insDet);
        }
        # Header and Detail inserted
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
        $updJurnal = updateQuery(
            $dbname,
            'keu_5kelompokjurnal',
            array('nokounter' => $konter),
            "kodeorg='" . substr($ptkend, 0, 3) . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . trim(substr($kodetrk, 0, 4)) . "' and periode='" . $param['periode'] . "'  "
        );
        $owlPDO->exec($updJurnal);
    }
}
function prosesAlokasi()
{
    global $conn;
    global $tanggal;
    global $param;
    global $dbname;
    global $defSegment;
    global $owlPDO;
    global $orgpt;

    $arrUnit = array();

    #1 ambil periode akuntansi
    $str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi where 
          kodeorg ='" . $param['kodeorg'] . "' and tutupbuku=0 and periode='" . $param['periode'] . "'";
    $tgmulai = '';
    $tgsampai = '';
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    if ($numrows < 1) {
        throw new PDOException("Tidak ada periode akuntansi untuk induk " . $param['kodeorg']);
    }
    while ($bar = $res->fetch()) {
        $tgsampai   = $bar->tanggalsampai;
        $tgmulai    = $bar->tanggalmulai;
    }
    if ($tgmulai == '' || $tgsampai == '')
        throw new PDOException("Periode akuntasi tidak terdaftar");

    #2 output pada jurnal kolom noreferensi ALK_KERJA_AB  

    $group = 'VHC1';
    #ambil akun alokasi
    $str = "select noakundebet from " . $dbname . ".keu_5parameterjurnal
          where jurnalid='" . $group . "' limit 1";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    if ($numrows < 1)
        throw new PDOException("No.Akun pada parameterjurnal belum ada untuk VHC1");
    else {
        $bar = $res->fetch();
        $akunalok = $bar->noakundebet;
    }

    $strh = "select distinct noakundebet,sampaidebet  from " . $dbname . ".keu_5parameterjurnal where  jurnalid='LPVHC'";
    $resh = $owlPDO->query($strh) or die(print " Gagal: " . PDOException::getMessage());
    $resh->setFetchMode(PDO::FETCH_OBJ);
    while ($barh = $resh->fetch()) {
        $akunkdari = $barh->noakundebet;
        $akunksampai = $barh->sampaidebet;
    }

    $str = "select sum(jumlah) as jumlah, kodevhc from " . $dbname . ".keu_jurnaldt_vw where  noakun not in (4110299,4110199)  and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "' and (noakun between '" . $akunkdari . "' and '" . $akunksampai . "') and (noreferensi not in ('ALK_KERJA_AB','ALK_TRK_GYMH') or noreferensi is NULL) and kodevhc='" . $param['kodevhc'] . "' ";

    $res = fetchdata($str);
    foreach ($res as $bar) {
        $param['jumlahbiayakendaraan'] = $bar['jumlah'];
    }

    #3 ambil semua lokasi kegiatan
    $str = "select sum(a.jumlah) as jlh,a.alokasibiaya,b.noakun,b.setupkegiatan from " . $dbname . ".vhc_rundt_detail a
            left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
            left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
            where c.kodevhc='" . $param['kodevhc'] . "'
            and c.tanggal>='" . $tgmulai . "' and c.tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
            and jenispekerjaan!=''    
            group by jenispekerjaan,noakun,alokasibiaya,kodesegment order by tanggal asc";

    // exit("Error:".$str);

    $lokasi = array();
    $biaya = array();
    $jam  = array();
    $akun = array();
    $kodeasset = array();
    $segment = array();
    $ttl = 0;
    $no = 0;
    $counttemp = 0;
    $tempjamx = 0;

    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $tempjamx += $bar->jlh;
        $counttemp++;
    }



    if ($param['jumlahbiayakendaraan'] != 0) {
        $tempjumlah = $param['jumlahbiayakendaraan'];

        // exit("Error:".$param['jumlahpembulatan']);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {

            #= insert pembulatan di baris 1
            $no++;
            // if($no>1){
            // $param['jumlahpembulatan']=0;
            // }


            #kusus jika project
            if (substr($bar->alokasibiaya, 0, 2) == 'AK' or substr($bar->alokasibiaya, 0, 2) == 'PB') {
                if (substr($bar->alokasibiaya, 0, 2) == 'AK') {
                    #ambil akun aktiva dalam konstruksi
                    // alokasi ke AK-BG98000008, kalo 3,3 dapetnya BG9... sementara setupnya cuman ada BG. jadi ganti 3,2
                    $tipeasset = substr($bar->alokasibiaya, 3, 2);
                    $tipeasset =  str_replace("0", "", $tipeasset);
                    $str1 = "select akunak from " . $dbname . ".sdm_5tipeasset where kodetipe='" . $tipeasset . "'";
                    $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                    $res1->setFetchMode(PDO::FETCH_OBJ);
                    $numrows1 = owlBaris($res1);
                    if ($numrows1 < 1) {
                        throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " beum disetting dari keuangan->setup->tipeasset");
                    } else {
                        while ($bar1 = $res1->fetch()) {
                            if ($bar1->akunak == '')
                                throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " belum disetting dari keuangan->setup->tipeasset");
                            else
                                $akun[] = $bar1->akunak;
                        }
                    }
                    $kodeasset[] = $bar->alokasibiaya;
                }
                #jika pabrikasi
                if (substr($bar->alokasibiaya, 0, 2) == 'PB') {
                    $sData = "select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='PBR3'";
                    $rData = fetchData($sData);
                    if (count($rData) == 0) {
                        throw new PDOException("Akun untuk alokasi traksi pabrikasi belum di setting pada keuangan->setup->parameter jurnal");
                    } else {
                        $akun[] = $rData[0]['noakundebet'];
                        $kodeasset[] = '';
                    }
                }
                $lokasi[] = $bar->alokasibiaya;
                $jam[] = $bar->jlh;
                if ($no == $counttemp or ($tempjumlah - floor(($bar->jlh / $tempjamx) * $param['jumlahbiayakendaraan'])) <= 0) {
                    $biaya[] = $tempjumlah;
                    $totalalokasi += $tempjumlah;
                    $tempjumlah = $tempjumlah - $tempjumlah;
                } else {
                    $biaya[] = floor(($bar->jlh / $tempjamx) * $param['jumlahbiayakendaraan']);
                    $totalalokasi += floor(($bar->jlh / $tempjamx) * $param['jumlahbiayakendaraan']);
                    $tempjumlah = $tempjumlah - floor(($bar->jlh / $tempjamx) * $param['jumlahbiayakendaraan']);
                }
                // if($param['kodevhc']=='PPPEDT0004'){
                //     echo ' jlh :'.$bar->jlh.' tempjam: '.$tempjamx.' jumlahbiaya: '.$param['jumlahbiayakendaraan'].' totalalokasi : '.$totalalokasi.' tempjumlah : '.$tempjumlah.'<br>';
                //     echo ' jlh :'.$bar->jlh.' tempjam: '.$tempjamx.' jumlahbiaya: '.$param['jumlahbiayakendaraan'].' totalalokasi : '.$totalalokasi.' tempjumlah : '.$tempjumlah.'<br>';
                // }
                $kegiatan[] = '';
                $segment[] = $bar->kodesegment;
            } else {
                $lokasi[] = $bar->alokasibiaya;
                $akun[]  = $bar->noakun;
                $jam[] = $bar->jlh;
                if ($no == $counttemp or ($tempjumlah - floor(($bar->jlh / $tempjamx) * $param['jumlahbiayakendaraan'])) <= 0) {
                    $biaya[] = $tempjumlah;
                    $totalalokasi += $tempjumlah;
                    $tempjumlah = $tempjumlah - $tempjumlah;
                } else {
                    $biaya[] = floor(($bar->jlh / $tempjamx) * $param['jumlahbiayakendaraan']);
                    $totalalokasi += floor(($bar->jlh / $tempjamx) * $param['jumlahbiayakendaraan']);
                    $tempjumlah = $tempjumlah - floor(($bar->jlh / $tempjamx) * $param['jumlahbiayakendaraan']);
                }

                // if($param['kodevhc']=='PPPEDT0004'){
                //     echo ' jlh :'.$bar->jlh.' tempjam: '.$tempjamx.' jumlahbiaya: '.$param['jumlahbiayakendaraan'].' totalalokasi : '.$totalalokasi.' tempjumlah : '.$tempjumlah.'<br>';
                //     echo ' jlh :'.$bar->jlh.' tempjam: '.$tempjamx.' jumlahbiaya: '.$param['jumlahbiayakendaraan'].' totalalokasi : '.$totalalokasi.' tempjumlah : '.$tempjumlah.'<br>';
                // }
                // $kegiatan[]=$bar->noakun."01";
                $kegiatan[] = $bar->setupkegiatan;
                $kodeasset[] = '';
                $segment[] = $bar->kodesegment;


                $templokasi = $bar->alokasibiaya;
                $tempakun = $bar->noakun;
                $tempjam = '1';
                $tempkegiatan = $bar->setupkegiatan;
                $tempbiaya = 0;
                $tempkodeasset = 0;
                $tempsegment = $bar->kodesegment;
            }
        }
    }
    // if($param['kodevhc']=='PPPEDT0004'){
    //     exit("Error:");
    // }


    // if ($param['jumlahbiayakendaraan'] != '0') {
    //     $lokasi[] = $templokasi;
    //     $akun[]  = $tempakun;
    //     $jam[] = $tempjam;
    //     $biaya[] = $param['jumlahbiayakendaraan'];
    //     // 
    //     // exit("Error:".$param['jumlahbiayakendaraan']._.$totalalokasi);
    //     $kegiatan[] = $tempkegiatan;
    //     $kodeasset[] = $tempkodeasset;
    //     $segment[] = $tempsegment;
    // }


    foreach ($biaya as $key => $nilai) {
        #periksa unit 
        $dataRes['header'] = array();
        $dataRes['detail'] = array();
        $intern = true;

        $pengguna = substr($lokasi[$key], 0, 4);
        if (substr($lokasi[$key], 0, 2) == 'AK' or substr($lokasi[$key], 0, 2) == 'PB') {
            if (substr($lokasi[$key], 0, 2) == 'AK') {
                #khusus project
                $str = "select kodeorg from " . $dbname . ".project where kode='" . $lokasi[$key] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while ($bar = $res->fetch()) {
                    $pengguna = $bar->kodeorg;
                    //$lokasi[$key]=$lokasi[$key];
                }
            }
            if (substr($lokasi[$key], 0, 2) == 'PB') {
                #khusus project
                $str = "select kodeorg from " . $dbname . ".pabrikasi_5masterht where kodepabrikasi='" . $lokasi[$key] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while ($bar = $res->fetch()) {
                    $pengguna = $bar->kodeorg;
                    //$lokasi[$key]=$lokasi[$key];
                }
            }
        }

        #ambil piutang ke pengguna
        $str = "select akunpiutang,jenis from " . $dbname . ".keu_5caco where kodeorg='" . $pengguna . "'";
        $intraco = '';
        $interco = '';
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            if ($bar->jenis == 'intra')
                $intraco = $bar->akunpiutang;
            else
                $interco = $bar->akunpiutang;
        }

        $supplierrxx = '';
        $str = "select * from " . $dbname . ".kebun_5namakud where status='1' and afdeling='" . substr($lokasi[$key], 0, 6) . "'";
        $res = fetchData($str);
        foreach ($res as $row => $lsDt) {
            $supplierrxx = $lsDt['kodesupplier'];
        }
        if ($supplierrxx == '') {
            $strx = "select * from " . $dbname . ".kebun_5namakud where status='1' and kodesupplier='" . substr($lokasi[$key], 0, 6) . "'";
            $resx = fetchData($strx);
            foreach ($resx as $rowx => $lsDtx) {
                $supplierrxx = $lsDtx['kodesupplier'];
                $pengguna = $lsDtx['kodeunit'];
            }
        }
        /*
			if ($intraco=='' || $interco==''){
				if($supplierrxx==''){
					throw new PDOException("EN : KUD code ".$lokasi[$key]." not register, please register KUD in Kebun->Setup->Nama Kud \n IND : KUD kode ".$lokasi[$key]."  belum didaftarkan, silahkan daftarkan KUD di Kebun->Setup->Nama Kud");	
				}else{
					throw new PDOException("EN : Account intraco or interco not available for ".$pengguna.". Please setting on menu Finance->setup->COA for Intra/Interco \n IN : Akun intraco datau interco belum didaftarkan ".$pengguna.". Silahkan hubungi Accounting untuk mendaftarkan akun interco dan intraco.");
				}
			}
			*/


        if ($intraco == '' || $interco == '') {
            if ($supplierrxx == '') {
                throw new PDOException("Jika Unit " . $lokasi[$key] . " adalah KUD maka daftarkan dahulu di KUD di Kebun->Setup->Nama Kud \n Jika unit bukan KUD maka daftarkan COA intra/interco Keuangan->setup->akun inter/intraco");
            }
        }



        #++++++++++++++++++++++++++++++++++++++
        $akunpekerjaan = $akun[$key];
        #++++++++++++++++++++++++++++++++++++++++
        $ptpengguna = '';
        $str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $pengguna . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $ptpengguna = $bar->induk;
        }

        $ptGudang = '';
        $str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $param['kodeorg'] . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $ptGudang = $bar->induk;
        }


        if ($supplierrxx != '') {
            $ptpengguna = $ptGudang;
            $pengguna = $param['kodeorg'];
            #echo $lokasi[$key]."xxx".$ptpengguna."xxx".$ptGudang."xxx".$supplierrxx."xxx".$pengguna;
            #exit('error');
        }
        #jika pt tidak sama maka pakai akun interco
        $akunpengguna = '';
        if ($ptGudang != $ptpengguna) {
            #ambil akun interco
            $intern = false;
            $str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='inter'";
            $akunpengguna = '';
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
                $akunpengguna = $bar->akunhutang;
            }
            $akunsendiri = $interco;
            if ($akunpengguna == '')
                throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
        } else if ($pengguna != $param['kodeorg']) { #jika satu pt beda kebun
            #ambil akun intraco
            $intern = false;
            $str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='intra'";
            $akunpengguna = '';
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
                $akunpengguna = $bar->akunhutang;
            }
            $akunsendiri = $intraco;
            if ($akunpengguna == '')
                throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
        } else {
            $intern = true;
        }

        if ($intern) {
            #proses data
            $kodeJurnal = $group;
            #======================== Nomor Jurnal =============================
            # Get Journal Counter
            $queryJ = selectQuery(
                $dbname,
                'keu_5kelompokjurnal',
                'nokounter',
                "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
            );
            $tmpKonter = fetchData($queryJ);
            $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);


            #= cek konter dari jurnal
            $str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                $konterjurnal = ($bar['konter'] + 1);
            }

            if ($konterjurnal > $konter) {
                $konter = $konterjurnal;
            }


            # Transform No Jurnal dari No Transaksi
            $nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
            #======================== /Nomor Jurnal ============================
            # Prep Header
            $dataRes['header'] = array(
                'nojurnal' => $nojurnal,
                'kodejurnal' => $kodeJurnal,
                'tanggal' => $tanggal,
                'tanggalentry' => date('Ymd'),
                'posting' => 1,
                'totaldebet' => $biaya[$key],
                'totalkredit' => -1 * $biaya[$key],
                'amountkoreksi' => '0',
                'noreferensi' => 'ALK_KERJA_AB',
                'autojurnal' => '1',
                'matauang' => 'IDR',
                'kurs' => '1',
                'revisi' => '0'
            );
            # Data Detail
            $noUrut = 1;
            # Debet
            $dataRes['detail'][] = array(
                'nojurnal' => $nojurnal,
                'tanggal' => $tanggal,
                'nourut' => $noUrut,
                'noakun' => $akunpekerjaan,
                'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
                'jumlah' => $biaya[$key],
                'matauang' => 'IDR',
                'kurs' => '1',
                'kodeorg' => $param['kodeorg'],
                'kodekegiatan' => $kegiatan[$key],
                'kodeasset' => $kodeasset[$key],
                'kodebarang' => '',
                'nik' => 0,
                'kodecustomer' => '',
                'kodesupplier' => $supplierrxx,
                'noreferensi' => 'ALK_KERJA_AB',
                'noaruskas' => '',
                'kodevhc' => $param['kodevhc'],
                'nodok' => '',
                'kodeblok' => $lokasi[$key],
                'revisi' => '0',
                'kodesegment' => $segment[$key]
            );
            $noUrut++;

            # Kredit
            $dataRes['detail'][] = array(
                'nojurnal' => $nojurnal,
                'tanggal' => $tanggal,
                'nourut' => $noUrut,
                'noakun' => $akunalok,
                'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
                'jumlah' => -1 * $biaya[$key],
                'matauang' => 'IDR',
                'kurs' => '1',
                'kodeorg' => $param['kodeorg'],
                'kodekegiatan' => $kegiatan[$key],
                'kodeasset' => '',
                'kodebarang' => '',
                'nik' => '0',
                'kodecustomer' => '',
                'kodesupplier' => '',
                'noreferensi' => 'ALK_KERJA_AB',
                'noaruskas' => '',
                'kodevhc' => $param['kodevhc'],
                'nodok' => '',
                'kodeblok' => $lokasi[$key],
                'revisi' => '0',
                'kodesegment' => $segment[$key]
            );
            $noUrut++;
            $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
            $owlPDO->exec($insHead);

            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            foreach ($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                $owlPDO->exec($insDet);
            }
            # Header and Detail inserted
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
            $updJurnal = updateQuery(
                $dbname,
                'keu_5kelompokjurnal',
                array('nokounter' => $konter),
                "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
            );
            $owlPDO->exec($updJurnal);
        } else {
            # Data Detail
            $noUrut = 1;
            #proses data
            $kodeJurnal = $group;
            #======================== Nomor Jurnal =============================
            # Get Journal Counter
            $queryJ = selectQuery(
                $dbname,
                'keu_5kelompokjurnal',
                'nokounter',
                "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
            );
            $tmpKonter = fetchData($queryJ);
            $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

            #= cek konter dari jurnal
            $str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                $konterjurnal = ($bar['konter'] + 1);
            }

            if ($konterjurnal > $konter) {
                $konter = $konterjurnal;
            }



            # Transform No Jurnal dari No Transaksi
            $nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter;
            $str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and tutupbuku = '1' 
            and kodeorg='" . $pengguna . "'";
            $rstr = fetchData($str);
            $arrUnit = array();
            if (count($rstr) == '0') { //klo blm tutup buku
                #======================== /Nomor Jurnal ============================
                # Prep Header
                $dataRes['header'] = array(
                    'nojurnal' => $nojurnal,
                    'kodejurnal' => $kodeJurnal,
                    'tanggal' => $tanggal,
                    'tanggalentry' => date('Ymd'),
                    'posting' => 1,
                    'totaldebet' => $biaya[$key],
                    'totalkredit' => -1 * $biaya[$key],
                    'amountkoreksi' => '0',
                    'noreferensi' => 'ALK_KERJA_AB',
                    'autojurnal' => '1',
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'revisi' => '0'
                );
                # Debet
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $akunsendiri,
                    'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
                    'jumlah' => $biaya[$key],
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $param['kodeorg'],
                    'kodekegiatan' => $kegiatan[$key],
                    'kodeasset' => '',
                    'kodebarang' => '',
                    'nik' => '0',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'ALK_KERJA_AB',
                    'noaruskas' => '',
                    'kodevhc' => $param['kodevhc'],
                    'nodok' => '',
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => $defSegment
                );
                $noUrut++;

                # Kredit
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $akunalok,
                    'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
                    'jumlah' => -1 * $biaya[$key],
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $param['kodeorg'],
                    'kodekegiatan' => $kegiatan[$key],
                    'kodeasset' => '',
                    'kodebarang' => '',
                    'nik' => '0',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'ALK_KERJA_AB',
                    'noaruskas' => '',
                    'kodevhc' => $param['kodevhc'],
                    'nodok' => '',
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => $defSegment
                );

                $noUrut++;
                $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                $owlPDO->exec($insHead);

                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                foreach ($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                    $owlPDO->exec($insDet);
                }
                # Header and Detail inserted
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                $updJurnal = updateQuery(
                    $dbname,
                    'keu_5kelompokjurnal',
                    array('nokounter' => $konter),
                    "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
                );
                $owlPDO->exec($updJurnal);

                #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
                #sisi Pengguna
                $kodeJurnal = $group;
                #ambil periodeaktif pengguna
                $tgmulaid = $tanggal;


                #======================== Nomor Jurnal =============================
                # Get Journal Counter
                $queryJ = selectQuery(
                    $dbname,
                    'keu_5kelompokjurnal',
                    'nokounter',
                    "kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
                );
                $tmpKonter = fetchData($queryJ);
                $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

                #= cek konter dari jurnal
                $str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($pengguna) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $konterjurnal = ($bar['konter'] + 1);
                }

                if ($konterjurnal > $konter) {
                    $konter = $konterjurnal;
                }



                # Transform No Jurnal dari No Transaksi
                $nojurnal = str_replace("-", "", $tgmulaid) . "/" . trim($pengguna) . "/" . $kodeJurnal . "/" . $konter;
                #======================== /Nomor Jurnal ============================
                # Prep Header
                unset($dataRes['header']); //ganti header   
                $dataRes['header'] = array(
                    'nojurnal' => $nojurnal,
                    'kodejurnal' => $kodeJurnal,
                    'tanggal' => $tgmulaid,
                    'tanggalentry' => date('Ymd'),
                    'posting' => 1,
                    'totaldebet' => $biaya[$key],
                    'totalkredit' => -1 * $biaya[$key],
                    'amountkoreksi' => '0',
                    'noreferensi' => 'ALK_KERJA_AB',
                    'autojurnal' => '1',
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'revisi' => '0'
                );

                # Debet 1
                $noUrut = 1;
                unset($dataRes['detail']); //ganti header 
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tgmulaid,
                    'nourut' => $noUrut,
                    'noakun' => $akunpekerjaan,
                    'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
                    'jumlah' => $biaya[$key],
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $pengguna,
                    'kodekegiatan' => $kegiatan[$key],
                    'kodeasset' => $kodeasset[$key],
                    'kodebarang' => '',
                    'nik' => '0',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'ALK_KERJA_AB',
                    'noaruskas' => '',
                    'kodevhc' => $param['kodevhc'],
                    'nodok' => '',
                    'kodeblok' => $lokasi[$key],
                    'revisi' => '0',
                    'kodesegment' => $segment[$key]
                );
                $noUrut++;

                # Kredit 1
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tgmulaid,
                    'nourut' => $noUrut,
                    'noakun' => $akunpengguna,
                    'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
                    'jumlah' => -1 * $biaya[$key],
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $pengguna,
                    'kodekegiatan' => $kegiatan[$key],
                    'kodeasset' => '',
                    'kodebarang' => '',
                    'nik' => '0',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'ALK_KERJA_AB',
                    'noaruskas' => '',
                    'kodevhc' => $param['kodevhc'],
                    'nodok' => '',
                    'kodeblok' => $lokasi[$key],
                    'revisi' => '0',
                    'kodesegment' => $segment[$key]
                );
                $noUrut++;

                $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                $owlPDO->exec($insHead);

                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                foreach ($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                    $owlPDO->exec($insDet);
                }
                # Header and Detail inserted
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                $updJurnal = updateQuery(
                    $dbname,
                    'keu_5kelompokjurnal',
                    array('nokounter' => $konter),
                    "kodeorg='" . $ptpengguna .
                        "' and kodekelompok='" . $kodeJurnal . "'   and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
                );
                $owlPDO->exec($updJurnal);

                //tutup 
            } else {
                $arrUnit[$pengguna] = $pengguna;
            }
        }
    }
    if (count($arrUnit) != 0) {
        $tttttt = "Ada Unit Tidak Menerima Biaya Karna Sudah Tutup Buku : ";
        foreach ($arrUnit as $unitddd) {
            $tttttt .= $unitddd . "\n";
        }
        throw new PDOException($tttttt);
    }
}
