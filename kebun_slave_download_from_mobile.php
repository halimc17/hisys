<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/zFunction.php');
include("lib/mharvest/getContentAPI.php");
$getApi = new getContentAPI;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$param = $_GET;
if(!empty($_GET)){$param=$_GET;}else{$param=$_POST;}
$proses = $param['proses'];

// GET URI FOR PRODUCTION
$expri = explode("/",$_SERVER['REQUEST_URI']);

$svr=parse_url($_SERVER['HTTP_REFERER']);

$pat=array();
$pat=explode('/',$svr['path']);
$arr = array_filter($pat, function($value) {
    return !is_null($value) && $value !== '';
});

$data=[];
foreach ($arr as $key => $value) {
    if (!strpos($value, ".php")) {
        $data[]=$value;
    }
}
$urlocal=$_SERVER['HTTP_ORIGIN'].'/'.implode("/",$data);

switch ($proses) {
    case 'panen':
        // Cek Dulu Apakah Datanya Sudah ada Di ERP Atau Belum
        $sHead = "SELECT * FROM $dbname.kebun_aktifitas WHERE noreferensi='".$param['notransaksi']."'";
        $rHead = fetchdata($sHead);
        if (count($rHead) > 0) {
            // Jika Ada Data Berdasarkan No referensi munculkan validasi
            exit("Warning: Nomor Transaksi Panen Mobile<br>Di No. Referensi: ". $param['notransaksi'] ." Sudah Ada Di Buku Kegiatan Mandor !
                <br><br>Silakan menghapus data berdasarkan No. Referensi tersebut terlebuh dahulu.");

            // Jika Ada Data Berdasarkan No referensi maka di delete terlebih dahulu
            // if ($param['notransaksi'] != "") {
            //     $qDel = deleteQuery($dbname,"kebun_aktifitas","noreferensi='".$param['notransaksi']."'");
            //     $owlPDO->exec($qDel);
            // }
        }

        /** GET OPTIONS API */
        $options = array(
			'client_id' => 'USERSYSTEM',
			'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
			'username' => $_SESSION['standard']['username']
		);
		/** GET API KEY */
		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
            }else{
                $url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal."mobile/index.php/api/access_token/api_key";
        }
		$getApi->init($url,$options);

		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $urlData = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/mharvest/getDetail/send';
            }else{
                $urlData = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/mharvest/getDetail/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlData = $urlocal.'mobile/index.php/api/module/mharvest/getDetail/send';
        }
		$paramData = array(
			'notransaksi' => $param['notransaksi']
		);
        $getData = $getApi->post($urlData, $paramData);

        // Definisikan variable array
        $iddendapnn=array();
        $dendapanen=array();
        $kodedendaid=array();
        $rupiahdenda=array();
        $rpdendaperkode=array();
        $jmlrpdenda=array();
 
        /* Query untuk get kode denda panen */
        $strDenda = "select max(id) as max,a.*,b.* from ".$dbname.".kebun_5dendapanen a left join ".$dbname.".kebun_5kodedendapanen b on a.kodedenda=b.kodedenda 
        where 1=1 and a.kodeorg='".$getData->response['result']['header']['kodeorg']."' group by id order by b.id asc";
        $resDenda = fetchdata($strDenda);
        foreach ($resDenda as $val) {
            $iddendapnn[$val['id']] = $val['id'];
            $dendapanen[$val['id']] = $val['kodedenda'];
            $kodedendaid[$val['kodedenda']] = $val['id'];
            $rupiahdenda[$val['kodedenda']] = $val['denda'];
        }
        $jlhdenda=count($dendapanen);

        if ($jlhdenda <= 0) {
            exit("Warning: Harga denda panen belum ada, silahkan tambahkan melalui menu : Kebun - Setup - Denda Panen");
        }

        // foreach ($getData->response['result']['kebun_prestasi_mobile'] as $key => $val){
        //     if($val['luaspanen'] == 0 || $val['luaspanen'] == 0.00) {
        //         exit("Warning: Tidak bisa melakukan download data.\nTerdapat Luas Panen Yang Belum Terisi!");
        //     }
        // }

        try {
            $owlPDO->beginTransaction();
            /* ============================================================= HEADER KEBUN_AKTIFTAS PNN ======================================================================== */
            # Get Existing Data
            $fWhere = "tanggal='".$getData->response['result']['header']['tanggal']."' and kodeorg='".$getData->response['result']['header']['kodeorg']."' and tipetransaksi='PNN'";
            $fQuery = selectQuery($dbname,'kebun_aktifitas','nobkm',$fWhere);
            $tmpNo = fetchData($fQuery);
            # Generate No Transaksi
            if(count($tmpNo)==0) {
                $getNotrans = str_replace("-","",$getData->response['result']['header']['tanggal'])."/".$getData->response['result']['header']['kodeorg']."/PNN/001";
            } else {
                # Get Max No Urut
                $maxNo = 1;
                foreach($tmpNo as $row) {
                $tmpRow = explode('/',$row['nobkm']);
                $noUrut = (int)$tmpRow[3];
                if($noUrut>$maxNo)
                    $maxNo = $noUrut;
                }
                $currNo = addZero($maxNo+1,3);
                $getNotrans = str_replace("-","",$getData->response['result']['header']['tanggal'])."/".$getData->response['result']['header']['kodeorg']."/PNN/".$currNo;
            }
            
            $sql = "select * from " . $dbname . ".kebun_aktifitas where "."notransaksi='" . $getNotrans . "'";
            $res = fetchData($sql);
            if (count($res) > 0) {
                $notrtemp = explode("/",$getNotrans);
                $fWhere = "tanggal='".$notrtemp[0]."' and kodeorg='".$notrtemp[1]."' and tipetransaksi='PNN'";
                $str = "select max(substr(notransaksi,-3)) as notr from " . $dbname . ".kebun_aktifitas where ".$fWhere." limit 1";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
                
                $trtemp = addZero((intval($bar['notr'])+1),3);
                $getNotrans=str_replace($notrtemp[3],$trtemp,$getNotrans);
            }
            $nobkm = $getNotrans;
            $arrHead = array(
                'notransaksi'   => $getNotrans, 
                'tipetransaksi' => 'PNN',
                'tanggal'       => $getData->response['result']['header']['tanggal'],
                'nobkm'         => $getNotrans,
                'kodeorg'       => $getData->response['result']['header']['kodeorg'],
                'nikmandor'     => $getData->response['result']['header']['nikmandor'],
                'nikmandor1'    => $getData->response['result']['header']['nikmandor1'],
                'nikasisten'    => $getData->response['result']['header']['kerani'],
                'keranimuat'    => "",
                'jurnal'        => 0,
                'nospk'         => null,
                'noreferensi'   => $getData->response['result']['header']['notransaksi'],
                'updateby'      => $_SESSION['standard']['userid'],
                'divisi'        => $getData->response['result']['header']['divisi'],
                'deviceid'      => $getData->response['result']['header']['deviceid']
            );

            $colsHead = array();
            foreach ($arrHead as $key => $row) {
                $colsHead[] = $key;
            }

            // Execute Insert Header
            $insHead = insertQuery($dbname,"kebun_aktifitas",$arrHead,$colsHead);
            $owlPDO->exec($insHead);
            


            $tanggaltrans = $getData->response['result']['header']['tanggal'];
            $kodeorg = [];

            /* ============================================================= DETAIL KEBUN_PRESTASI PNN ======================================================================== */
            // echo "<pre>";
            // print_r($getData->response['result']['kebun_prestasi_mobile']);
            // echo "</pre>";
            foreach ($getData->response['result']['kebun_prestasi_mobile'] as $key => $val) {
                foreach ($val['mutubuah'] as $key2 => $val2) {
                    $nilaipenalty[$key2][$val['kodeorg']][$val['tph']][$val['sesi']][$val['nik']]  = $val2['nilai'];
                }

                // Get All Kode blok
                $kodeorg[$val['kodeorg']] = $val['kodeorg'];

                // Get Nomor Urut untuk kebun_prestasi dari notransaksi yang terbentuk
                $str = "select max(nourut) as nourut from ".$dbname.".kebun_prestasi where notransaksi='".$getNotrans."'";
                $res = fetchdata($str);
                if(count($res)==0){
                    $nomor=1;
                }else{
                    @$nomor=floatval($res[0]['nourut'])+1;
                }

                $arrDetail = array(
                    'notransaksi'        => $getNotrans,
                    'noreferensi'        => $val['notransaksi'],
                    'nourut'             => $nomor,
                    'nik'                => $val['nik'],
                    'kodeorg'            => $val['kodeorg'],
                    'tph'       		 => $val['tph'],
                    'sesi'       		 => $val['sesi'],
                    'jjgbuahbesar'       => $val['jjngbuahbesar'],
                    'jjgbuahkecil'       => $val['jjgbuahkecil'],
                    'hasilkerja'         => ($val['jjngbuahbesar'] + $val['jjgbuahkecil']),
                    'luaspanen'          => $val['luaspanen'],
                    'brondolan'          => $val['brondolan'],
                    'photo'              => $val['photo'],
                    'photoakhir'         => $val['photo2']
                );

                foreach ($val['mutubuah'] as $key2 => $val2){
                    // Jika ada Kode Denda Di Unit dipilih
                    if (!empty($kodedendaid[$key2])) {
                        // Jika ada nilai penalti
                        if (!empty($val['mutubuah'])) {
                            // Jika pinalti kurang dari sama dengan jumlah denda yang ada di setup kebun_5kodedenda ERP
                            for ($i=1; $i <= $jlhdenda; $i++) {
                                // Get value kolom penalti sesuai dengan id kode denda
                                $arrDetail['penalti'.$kodedendaid[$key2]] = $nilaipenalty[$key2][$val['kodeorg']][$val['tph']][$val['sesi']][$val['nik']];
                                // Perhitungan rupiah denda per kode denda
                                $rpdendaperkode[$key2][$val['kodeorg']][$val['tph']][$val['sesi']][$val['nik']] = ($nilaipenalty[$key2][$val['kodeorg']][$val['tph']][$val['sesi']][$val['nik']] * $rupiahdenda[$key2]);
                            }
                            // Jumlahkan rupiah denda per tph,sesi,nik
                            $jmlrpdenda[$val['kodeorg']][$val['tph']][$val['sesi']][$val['nik']] += $rpdendaperkode[$key2][$val['kodeorg']][$val['tph']][$val['sesi']][$val['nik']];
                            
                            // Get Value jumlah rupiah denda
                            $arrDetail['rupiahpenalty'] = $jmlrpdenda[$val['kodeorg']][$val['tph']][$val['sesi']][$val['nik']];
                        }
                    }
                }
                
                $colsDetail = array();
                foreach ($arrDetail as $key => $row) {
                    $colsDetail[] = $key;
                }
            
                // Execute Insert Detail
                $insDetail = insertQuery($dbname,"kebun_prestasi",$arrDetail,$colsDetail);
                $owlPDO->exec($insDetail);

            }
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Gagal, ".addslashes($e->getMessage());
            die();
        }

        /* ============================================================= REKAP MUTU HANCAK PANEN ======================================================================== */
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        /* 
            if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
                // Jika URI yang array [1] panjang string <= 7, Maka munculkan
                if (strlen($expri[1]) <= 7) {
                    $urlHancakPanen = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/mharvest/rekaphancakpanen/send';
                }else{
                    $urlHancakPanen = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/mharvest/rekaphancakpanen/send';
                }
            }else{
                // Jika Server local / localhost maka munculkan URL localhost
                $urlHancakPanen = $urlocal.'mobile/index.php/api/module/mharvest/rekaphancakpanen/send';
            }
            
            $paramDtHancak = array(
                'tanggal' => $tanggaltrans,
                'blok'    => implode(",",$kodeorg)
            );
            $getDataHancak = $getApi->post($urlHancakPanen, $paramDtHancak);
            // echo "<pre>";
            // print_r($getDataHancak->response['result']['data']);
            // echo "</pre>";
            try {
                foreach ($getDataHancak->response['result']['data'] as $key => $val) {
                    $sCek = selectQuery($dbname,"kebun_rekaphancakpanen","*","kodeorg='".$val['kodeorg']."' AND nik='".$val['nik']."' AND tanggal='".$val['tanggal']."'");
                    $rCek = fetchData($sCek);
                    $countCek = count($rCek);
                    if ($countCek > 0) {
                        $delCek = deleteQuery($dbname,"kebun_rekaphancakpanen","kodeorg='".$val['kodeorg']."' AND nik='".$val['nik']."' AND tanggal='".$val['tanggal']."'");
                        $owlPDO->exec($delCek);
                    }
                    foreach ($val['penalti'] as $key3 => $val3) {
                        $nilaipenalty2[$key3][$val['kodeorg']][$val['nik']][$val['tanggal']] = $val3;
                    }
        
                    $arrRekapHancak = array(
                        'kodeorg'       => $val['kodeorg'],
                        'nik'           => $val['nik'],
                        'tanggal'       => $val['tanggal'],
                        'nikmandor'     => $val['nikmandor'],
                        'hapanen'       => $val['luaspanen'],
                        'posting'       => 1,
                        'postingby'     => $_SESSION['standard']['userid'],
                        'totaljjg'      => ($val['jjgbuahbesar'] + $val['jjgbuahkecil']),
                        'jjgbuahbesar'  => $val['jjgbuahbesar'],
                        'jjgbuahkecil'  => $val['jjgbuahkecil']
                    );
        
                    foreach ($val['penalti'] as $key3 => $val3) {
                        // Jika ada Kode Denda Di Unit dipilih
                        if (!empty($kodedendaid[$key3])) {
                            // Jika ada nilai penalti
                            if (!empty($val['penalti'])) {
                                // Jika pinalti kurang dari sama dengan jumlah denda yang ada di setup kebun_5kodedenda ERP
                                for ($i=1; $i <= $jlhdenda; $i++) {
                                    // Get value kolom penalti sesuai dengan id kode denda
                                    $arrRekapHancak['penalti'.$kodedendaid[$key3]] = $nilaipenalty2[$key3][$val['kodeorg']][$val['nik']][$val['tanggal']];
                                }
                            }
                        }
                    }
        
                    $colsRekapHancak = array();
                    foreach ($arrRekapHancak as $key => $row) {
                        $colsRekapHancak[] = $key;
                    }
        
                    // Execute Insert Rekap Hancak Panen
                    $insRekapHancak = insertQuery($dbname,"kebun_rekaphancakpanen",$arrRekapHancak,$colsRekapHancak);
                    $owlPDO->exec($insRekapHancak);
                }
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        */


        /* ============================================================= UPDATE FLAG KEBUN_AKTIFITAS_MOBILE API ======================================================================== */
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $urlFlag = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/mharvest/postERP/send';
            }else{
                $urlFlag = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/mharvest/postERP/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlFlag = $urlocal.'mobile/index.php/api/module/mharvest/postERP/send';
        }
		$paramFlag = array(
            'notransaksi' => $param['notransaksi'],
			'flag'        => '1',
		);
        
        $dataFlag = $getApi->post($urlFlag, $paramFlag);
        if ($dataFlag->response['error'] == true) {
            exit("Warning: Tidak Berhasil Download Data !");
        }
        echo $dataFlag->response['message'];
        // exit("Warning");
    break;

    case 'rekaphancakpanen':
        /** GET OPTIONS API */
        $options = array(
			'client_id' => 'USERSYSTEM',
			'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
			'username' => $_SESSION['standard']['username']
		);
		/** GET API KEY */
		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
            }else{
                $url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal."mobile/index.php/api/access_token/api_key";
        }
		$getApi->init($url,$options);

        /* ============================================================= CEK DENDA PANEN ======================================================================== */

        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $urlData = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/mharvest/getDetail/send';
            }else{
                $urlData = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/mharvest/getDetail/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlData = $urlocal.'mobile/index.php/api/module/mharvest/getDetail/send';
        }
		$paramData = array(
			'notransaksi' => $param['notransaksi']
		);
        $getData = $getApi->post($urlData, $paramData);

        // Definisikan variable array
        $iddendapnn=array();
        $dendapanen=array();
        $kodedendaid=array();
        $rupiahdenda=array();
        $rpdendaperkode=array();
        $jmlrpdenda=array();
        $kodeorgx = [];
 
        /* Query untuk get kode denda panen */
        $strDenda = "select max(id) as max,a.*,b.* from ".$dbname.".kebun_5dendapanen a left join ".$dbname.".kebun_5kodedendapanen b on a.kodedenda=b.kodedenda 
        where 1=1 and a.kodeorg='".substr($param['kodeorg'],0,4)."' group by id order by b.id asc";
        $resDenda = fetchdata($strDenda);
        foreach ($resDenda as $val) {
            $iddendapnn[$val['id']] = $val['id'];
            $dendapanen[$val['id']] = $val['kodedenda'];
            $kodedendaid[$val['kodedenda']] = $val['id'];
            $rupiahdenda[$val['kodedenda']] = $val['denda'];
        }
        $jlhdenda=count($dendapanen);

        if ($jlhdenda <= 0) {
            exit("Warning : Harga denda panen belum ada, silahkan tambahkan melalui menu : Kebun - Setup - Denda Panen");
        }

        /* ============================================================= KEBUN_REKAPMUTUHANCAKPANEN ======================================================================== */

        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
            if (strlen($expri[1]) <= 7) {
                $urlHancakPanen = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/mharvest/hancakdetails/send';
            }else{
                $urlHancakPanen = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/mharvest/hancakdetails/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlHancakPanen = $urlocal.'mobile/index.php/api/module/mharvest/hancakdetails/send';
        }
        
        $paramDtHancak = array(
            'tanggal'   => $param['tgl2'],
            'nikmandor' => $param['nikmandor'],
            'blok'      => $param['kodeorg'],
            'kodeorg'   => getOrgDetail(28),
        );
        $getDataHancak = $getApi->post($urlHancakPanen, $paramDtHancak);
        
        try {
            foreach ($getDataHancak->response['result']['data'] as $key => $val) {
                $sCek = selectQuery($dbname,"kebun_rekapmutuhancakpanen","*","kodeorg='".$val['kodeorg']."' AND nik='".$val['nik']."' AND tanggal='".$val['tanggal']."' AND nikmandor='".$val['nikmandor']."'");
                $rCek = fetchData($sCek);
                $countCek = count($rCek);
                if ($countCek > 0) {
                    $delCek = deleteQuery($dbname,"kebun_rekapmutuhancakpanen","kodeorg='".$val['kodeorg']."' AND nik='".$val['nik']."' AND tanggal='".$val['tanggal']."' AND nikmandor='".$val['nikmandor']."'");
                    $owlPDO->exec($delCek);
                }
                foreach ($val['penalti'] as $key3 => $val3) {
                    $nilaipenalty2[$key3][$val['kodeorg']][$val['nik']][$val['tanggal']] = $val3;
                }

                /* ================================================== INSERT INTO MUTU HANCAK PANEN (KEBUN_REKAPMUTUHANCAKPANEN) =================================================== */

                $arrRekapMutuHancak = array(
                    'kodeorg'       => $val['kodeorg'],
                    'nik'           => $val['nik'],
                    'tanggal'       => $val['tanggal'],
                    'nikmandor'     => $val['nikmandor'],
                    'posting'       => 0,
                    'postingby'     => "0000000000",
                    'totaljjg'      => ($val['jjgbuahbesar'] + $val['jjgbuahkecil']),
                    'jjgbuahbesar'  => $val['jjgbuahbesar'],
                    'jjgbuahkecil'  => $val['jjgbuahkecil']
                );
    
                foreach ($val['penalti'] as $key3 => $val3) {
                    // Jika ada Kode Denda Di Unit dipilih
                    if (!empty($kodedendaid[$key3])) {
                        // Jika ada nilai penalti
                        if (!empty($val['penalti'])) {
                            // Jika pinalti kurang dari sama dengan jumlah denda yang ada di setup kebun_5kodedenda ERP
                            for ($i=1; $i <= $jlhdenda; $i++) {
                                // Get value kolom penalti sesuai dengan id kode denda
                                $arrRekapMutuHancak['penalti'.$kodedendaid[$key3]] = $nilaipenalty2[$key3][$val['kodeorg']][$val['nik']][$val['tanggal']];
                            }
                        }
                    }
                }
    
                $colsRekapMutuHancak = array();
                foreach ($arrRekapMutuHancak as $key => $row) {
                    $colsRekapMutuHancak[] = $key;
                }
    
                // Execute Insert Rekap Hancak Panen (Mutu Hancak)
                $insMutuHancak = insertQuery($dbname,"kebun_rekapmutuhancakpanen",$arrRekapMutuHancak,$colsRekapMutuHancak);
                $owlPDO->exec($insMutuHancak);

                /* ================================================== INSERT INTO MUTU PHOTO MUTU HANCAK PANEN (KEBUN_REKAPHANCAKPANEN_PHOTO) =================================================== */
                // Get Value From Photo Penalti
                foreach ($val['photo'] as $key5 => $foto) {
                    $photodenda[$key5] = $foto;
                }

                foreach ($val['penalti'] as $key4 => $val4) {
                    // Jika ada Kode Denda Di Unit dipilih
                    if (!empty($kodedendaid[$key4])) {
                        // Jika ada nilai penalti Maka Eksekusi Insert Photo Mutu Hancak
                        if (!empty($val['penalti'])) {
                            $arrRekapPhotoHancak = array(
                                'kodeorg'       => $val['kodeorg'],
                                'nik'           => $val['nik'],
                                'tanggal'       => $val['tanggal'],
                                'nikamandor'     => $val['nikmandor'],
                                'kodedenda'     => $key4,
                                'photo'         => $photodenda[$key4]
                            );

                            $colsRekapPhotoHancak = array();
                            foreach ($arrRekapPhotoHancak as $key => $row) {
                                $colsRekapPhotoHancak[] = $key;
                            }
                
                            // Execute Insert Rekap Hancak Panen (Mutu Hancak)
                            $insMutuHancak = insertQuery($dbname,"kebun_rekaphancakpanen_photo",$arrRekapPhotoHancak,$colsRekapPhotoHancak);
                            $owlPDO->exec($insMutuHancak);
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        // exit("Warning: ");
    break;

    case 'rekaphektarpanen':
        /** GET OPTIONS API */
        $options = array(
			'client_id' => 'USERSYSTEM',
			'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
			'username' => $_SESSION['standard']['username']
		);
		/** GET API KEY */
		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
            }else{
                $url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal."mobile/index.php/api/access_token/api_key";
        }
		$getApi->init($url,$options);

        /* ============================================================= KEBUN_REKAPHANCAKPANEN ======================================================================== */

        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
            if (strlen($expri[1]) <= 7) {
                $urlHancakPanen = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/mluas/erpDetail/send';
            }else{
                $urlHancakPanen = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/mluas/erpDetail/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlHancakPanen = $urlocal.'mobile/index.php/api/module/mluas/erpDetail/send';
        }
        
        $paramDtHancak = array(
            'tanggal'   => $param['tgl2'],
            'periode'   => substr($param['tgl2'],0,7),
            'nikmandor' => $param['nikmandor'],
            'blok'      => $param['kodeorg'],
            'kodeorg'   => getOrgDetail(28),
        );
        $getDataHancak = $getApi->post($urlHancakPanen, $paramDtHancak);
        
        try {
            foreach ($getDataHancak->response['result']['data'] as $key => $val) {
                $sCek = selectQuery($dbname,"kebun_rekaphancakpanen","*","kodeorg='".$val['blok']."' AND nik='".$val['pemanen']."' AND tanggal='".$val['tanggal']."' AND nikmandor='".$val['mandor']."'");
                $rCek = fetchData($sCek);
                $countCek = count($rCek);
                if ($countCek > 0) {
                    $delCek = deleteQuery($dbname,"kebun_rekaphancakpanen","kodeorg='".$val['blok']."' AND nik='".$val['pemanen']."' AND tanggal='".$val['tanggal']."' AND nikmandor='".$val['mandor']."'");
                    $owlPDO->exec($delCek);
                }

                /* ================================================== INSERT INTO HA PANEN (KEBUN_REKAPHANCAKPANEN) =================================================== */
                
                $arrRekapHaPanen = array(
                    'kodeorg'       => $val['blok'],
                    'nik'           => $val['pemanen'],
                    'tanggal'       => $val['tanggal'],
                    'nikmandor'     => $val['mandor'],
                    'hapanen'       => $val['luasaktual'],
                    'posting'       => 0,
                    'postingby'     => "0000000000"
                );
    
                $colsRekapHaPanen = array();
                foreach ($arrRekapHaPanen as $key => $row) {
                    $colsRekapHaPanen[] = $key;
                }
    
                // Execute Insert Rekap Hancak HA Panen
                $insRekapHancak = insertQuery($dbname,"kebun_rekaphancakpanen",$arrRekapHaPanen,$colsRekapHaPanen);
                $owlPDO->exec($insRekapHancak);

                $countTphNik = array();
                // Cek Apakah Data Prestasi Di ERP
                $sPres = "SELECT * FROM $dbname.kebun_prestasi_new_vw WHERE nikmandor='".$val['mandor']."' AND tanggal='".$val['tanggal']."' 
                AND karyawanid='".$val['pemanen']."' AND kodeorg='".$val['blok']."'";
                $rPres = fetchData($sPres);
                $countPres = count($rPres);
                // Jika ada datanya per mandor,tanggal,kodeblok,dan nik pemanen
                if ($countPres > 0) {
                    foreach ($rPres as $prex) {
                        // Hitung Jumlah TPH Per Blok dan Pemanen
                        $tphNik[$prex['karyawanid']][$prex['kodeorg']][$prex['tph']][$prex['sesi']][] = $prex['tph'];
                        $countTphNik = count($tphNik[$prex['karyawanid']][$prex['kodeorg']]);
    
                        // Hitung Jumlah Pemanen Ada Berapa Blok
                        $countNik[$prex['karyawanid']][$prex['kodeorg']][] = $prex['karyawanid'];
                    }

                    foreach ($rPres as $pres) {
                        // Get Nilai Luas Panen Diproporsi
                        // $dividedValue = round($val['luasaktual'] / $countTphNik,2);
                        $dividedValue = floor(fixnan($val['luasaktual'] / $countTphNik)*100)/100;
                        // Rumus Pembagian Luas Panen
                        $luaspnndiprx = (count($countNik[$pres['karyawanid']][$pres['kodeorg']]) == $countTphNik)
                        ? $dividedValue + $val['luasaktual'] - ($dividedValue * $countTphNik)
                        : $dividedValue;

                        // Jika luas panennya kosong, maka jalan eksekusi update luas panen
                        if ($pres['luaspanen'] == 0 || $pres['luaspanen'] == '' || $pres['luaspanen'] == null) {
                            $arrUpdLuasPanen = array(
                                "luaspanen" => $luaspnndiprx
                            );
                            $updLuasPanen = updateQuery($dbname,"kebun_prestasi",$arrUpdLuasPanen,"notransaksi='".$pres['notransaksi']."' AND nik='".$pres['karyawanid']."' AND kodeorg='".$pres['kodeorg']."' AND tph='".$pres['tph']."'");
                            $owlPDO->exec($updLuasPanen);
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        // exit("Warning: ");
    break;

    case 'bkm':
        // Cek Dulu Apakah Datanya Sudah ada Di ERP Atau Belum
        $sHead = "SELECT * FROM $dbname.kebun_aktifitas WHERE noreferensi='".$param['notransaksi']."'";
        $rHead = fetchdata($sHead);
        if (count($rHead) > 0) {
            // Jika Ada Data Berdasarkan No referensi munculkan validasi
            exit("Warning: Nomor Transaksi BKM Mobile<br>Di No. Referensi: ". $param['notransaksi'] ." Sudah Ada Di Buku Kegiatan Mandor !
                <br><br>Silakan menghapus data berdasarkan No. Referensi tersebut terlebuh dahulu.");
            
            // Jika Ada Data Berdasarkan No referensi maka di delete terlebih dahulu
            // if ($param['notransaksi'] != "") {
            //     $qDel = deleteQuery($dbname,"kebun_aktifitas","noreferensi='".$param['notransaksi']."'");
            //     $owlPDO->exec($qDel);
            // }
        }

       /** GET OPTIONS API */
        $options = array(
            'client_id' => 'USERSYSTEM',
            'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
            'username' => $_SESSION['standard']['username']
        );
        /** GET API KEY */
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
            }else{
                $url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal."mobile/index.php/api/access_token/api_key";
        }
        $getApi->init($url,$options);

        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $urlData = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Mrawat/getdetailerp/load';
            }else{
                $urlData = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Mrawat/getdetailerp/load';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlData = $urlocal.'mobile/index.php/api/module/Mrawat/getdetailerp/load';
        }
        $paramData = array(
            'notransaksi' => $param['notransaksi']
        );
        $getData = $getApi->post($urlData, $paramData);
        // echo "<pre>";
        // print_r($getData->response['result']['Prestasi']);
        // print_r($getData->response['result']['Kehadiran']);
        // print_r($getData->response['result']['kehadiranumum']);
        // echo "</pre>";
        // exit("Warning ");


        /* ============================================================= CEK VALIDASI KEBUN_AKTIFITAS BKM ======================================================================== */
        // mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
        $sekarang= str_replace("-","",$getData->response['result']['Header']['tanggal']);
        $sekarang2= $getData->response['result']['Header']['tanggal'];
        if($sekarang<$_SESSION['org']['period']['start']){
            exit("Validation Error : Date out of range");
        }

        // Solusi sementara => Membaca dengan kode kehadiran "H" (Hadir)
        // $whabsensi = " and absensi='H'";
        $whabsensi = "";
        // Cek Apakah Para Mandor, Mandor 1, Kerani sudah ada di absensi umum atau tidak
        $str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$sekarang2."' ".$whabsensi." and karyawanid in ('".$getData->response['result']['Header']['nikmandor']."')";
		if(count(fetchData($str))>0){
			exit("Warning : Mandor sudah pernah diinput di menu Absensi.");
		}
		$str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$sekarang2."' ".$whabsensi." and karyawanid in ('".$getData->response['result']['Header']['nikmandor1']."')";
		if(count(fetchData($str))>0){
			exit("Warning : Mandor 1 sudah pernah diinput di menu Absensi.");
		}
		$str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$sekarang2."' ".$whabsensi." and karyawanid in ('".$getData->response['result']['Header']['kerani']."')";
		if(count(fetchData($str))>0){
			exit("Warning : Kerani sudah pernah diinput di menu Absensi.");
		}

        // GET NO BKM
        # Get Existing Data
        $fWhere = "tanggal='".$getData->response['result']['Header']['tanggal']."' and kodeorg='".$getData->response['result']['Header']['kodeorg']."' and tipetransaksi !='PNN'";
        $fQuery = selectQuery($dbname,'kebun_aktifitas','nobkm',$fWhere);
        $tmpNo = fetchData($fQuery);
        # Generate No Transaksi
        if(count($tmpNo)==0) {
            $nobkm = str_replace("-","",$getData->response['result']['Header']['tanggal'])."/".$getData->response['result']['Header']['kodeorg']."/BKM/001";
        } else {
            # Get Max No Urut
            $maxNo = 1;
            foreach($tmpNo as $row) {
            $tmpRow = explode('/',$row['nobkm']);
            $noUrut = (int)$tmpRow[3];
            if($noUrut>$maxNo)
                $maxNo = $noUrut;
            }
            $currNo = addZero($maxNo+1,3);
            $nobkm = str_replace("-","",$getData->response['result']['Header']['tanggal'])."/".$getData->response['result']['Header']['kodeorg']."/BKM/".$currNo;
        }
        
        // Get Array Of Data Needs
        foreach ($getData->response['result']['Prestasi'] as $key => $val) {
            $arrstblok[$val['statusblok']] = $val['statusblok'];
        }

        /* ============================================================= CEK VALIDASI KEBUN_KEHADIRAN & KEHADIRAN UMUM BKM ======================================================================== */
        $n = 0;
        $nmkaryerr = "";
        foreach ($getData->response['result']['Kehadiran'] as $key => $val) {
            // Cek UMR untuk pekerja yang ada di bkm
            $str = "select karyawanid, sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$val['karyawanid']."' and tahun='".substr($sekarang2,0,7)."' and idkomponen in ('1')";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                $umrHarian = $bar['nilai'] / 25;
                $gjPokok[$val['karyawanid']] = ($bar['nilai'] / 25);
                $karyidgjpkk[$val['karyawanid']] = $bar['karyawanid'];
            }

            if (empty($gjPokok[$val['karyawanid']])) {
                $karyidgjpkk[$val['karyawanid']] = $val['karyawanid'];
                $mError = "Setup Gaji Pokok Belum ada Untuk Karyawan dibawah Berikut:<br>";
                $n++;
                $nmkaryerr .=$n.". ".getNamaKaryawan($karyidgjpkk[$val['karyawanid']])."<br>";
            }
        }

        foreach ($getData->response['result']['kehadiranumum'] as $key => $val) {
            // Cek UMR untuk pekerja yang ada di bkm
            $str = "select karyawanid, sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$val['nik']."' and tahun='".substr($sekarang2,0,7)."' and idkomponen in ('1')";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                $umrHarian = $bar['nilai'] / 25;
                $gjPokok[$val['nik']] = ($bar['nilai'] / 25);
                $karyidgjpkk[$val['nik']] = $bar['karyawanid'];
            }

            if (empty($gjPokok[$val['nik']])) {
                $karyidgjpkk[$val['nik']] = $val['nik'];
                $mError = "Setup Gaji Pokok Belum ada Untuk Karyawan dibawah Berikut:<br>";
                $n++;
                $nmkaryerr .=$n.". ".getNamaKaryawan($karyidgjpkk[$val['nik']])."<br>";
            }
        }

        if($nmkaryerr != ''){
            exit("Warning: ".$mError.$nmkaryerr);
        }

        /* ============================================================= CEK VALIDASI KEBUN_PAKAIMATERIAL BKM ======================================================================== */

        $ttlkeluar = $logblmpost = $bkmblmpost = $saldogudang = $kwantitasmatr = array();
        $periodeMatr = substr($sekarang2,0,7);
        foreach ($getData->response['result']['Material'] as $key => $val) {
            $saldogudang[$val['kodegudang']][$val['kodebarang']] = 0;
            $bkmblmpost[$val['kodegudang']][$val['kodebarang']]  = 0;
            $logblmpost[$val['kodegudang']][$val['kodebarang']]  = 0;
            $ttlkeluar[$val['kodegudang']][$val['kodebarang']]   = 0;
            $kwantitasmatr[$val['kodegudang']][$val['kodebarang']]= 0;

            // Get Kwantitas Barang Per kodegudang dan kodebarang
            $kwantitasmatr[$val['kodegudang']][$val['kodebarang']] = $val['kwantitas'];
            
            # Ambil saldo gudang
            $str="select saldoakhirqty as saldoqty from ".$dbname.".log_5saldobulanan where kodebarang='".$val['kodebarang']."' and kodegudang='".$val['kodegudang']."' and periode='".$periodeMatr."'";
            $res=fetchData($str);
            foreach ($res as $bar) {
                $saldogudang[$val['kodegudang']][$val['kodebarang']] = $bar['saldoqty'];
            }
             
            #ambil transaksi belum posting di BKM
            $str="select sum(kwantitas) as kwantitas from ".$dbname.".kebun_pakaimaterial a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.kodebarang='".$val['kodebarang']."' and a.kodegudang='".$val['kodegudang']."' and b.jurnal='0'";
            $res=fetchData($str);
            foreach ($res as $bar) {
                $bkmblmpost[$val['kodegudang']][$val['kodebarang']] = $bar['kwantitas'];
            }
             
            #ambil transaksi belum posting di gudang (siapa tau ada, ambil yang keluar saja yang masuk biarkan saja)
            $str="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where kodebarang='".$val['kodebarang']."' and kodegudang='".$val['kodegudang']."' and post='0'  and tipetransaksi>4";
            $res=fetchData($str);
            foreach ($res as $bar) {
                $logblmpost[$val['kodegudang']][$val['kodebarang']] = $bar['jumlah'];
            }
             
             $ttlkeluar[$val['kodegudang']][$val['kodebarang']] = floatval($bkmblmpost[$val['kodegudang']][$val['kodebarang']])+floatval($logblmpost[$val['kodegudang']][$val['kodebarang']])+floatval($kwantitasmatr[$val['kodegudang']][$val['kodebarang']]);
             
            if($saldogudang[$val['kodegudang']][$val['kodebarang']]<0){
                exit("Warning: Saldo barang salah mohon hubungi administrator.");
            }
         
            if(floatval(number_format($ttlkeluar[$val['kodegudang']][$val['kodebarang']]),5) > floatval(number_format($saldogudang[$val['kodegudang']][$val['kodebarang']]),5)){
                exit("Warning: Saldo barang tidak cukup, sisa saldo : ".
                    $saldogudang[$val['kodegudang']][$val['kodebarang']]."\nPemakaian lalu belum posting : ".
                    ($bkmblmpost[$val['kodegudang']][$val['kodebarang']]+$logblmpost[$val['kodegudang']][$val['kodebarang']])."
                    \nTransaksi saat ini : ".$kwantitasmatr[$val['kodegudang']][$val['kodebarang']]."\nTotal Keluar : ".$ttlkeluar[$val['kodegudang']][$val['kodebarang']]."
                    \nSelisih : ".number_format($saldogudang[$val['kodegudang']][$val['kodebarang']]-$ttlkeluar[$val['kodegudang']][$val['kodebarang']],5)
                );
            }
        }

        /* ============================================================= END CEK VALIDASI SISTEM ======================================================================== */


        /* ============================================================= HEADER KEBUN_AKTIFTAS BKM ======================================================================== */
        // GET No transaksi
        $getNotrans = array();
        $stb = array();
        foreach ($arrstblok as $bar) {
            if ($bar == "") {
                $bar = "BKM";
            }
            if ($bar == "PNN") {
                $bar = "TM";
            }

            # Get Existing Data
            $fWhere = "tanggal='".$getData->response['result']['Header']['tanggal']."' and kodeorg='".$getData->response['result']['Header']['kodeorg']."' and tipetransaksi='".$bar."'";
            $fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
            $tmpNo = fetchData($fQuery);
            # Generate No Transaksi
            foreach ($getData->response['result']['Prestasi'] as $key => $val) {
                // Cek Kode Kegiatan Masuk Ke TM,TBM,TB, atau BBT
                $sKel = "SELECT kodekegiatan,kelompok FROM $dbname.setup_kegiatan WHERE kodekegiatan='".$val['kodekegiatan']."'";
                $rKel = fetchData($sKel);
                foreach ($rKel as $barc) {
                    $stb[$barc['kodekegiatan']] = $barc['kelompok'];
                }
                if(count($tmpNo)==0) {
                    $getNotrans[$bar] = str_replace("-","",$getData->response['result']['Header']['tanggal'])."/".$getData->response['result']['Header']['kodeorg']."/".$bar."/001";
                } else {
                    # Get Max No Urut
                    $maxNo = 1;
                    foreach($tmpNo as $row) {
                        $tmpRow = explode('/',$row['notransaksi']);
                        $noUrut = (int)$tmpRow[3];
                        if($noUrut>$maxNo){
                            $maxNo = $noUrut;
                        }
                    }
                    $currNo = addZero($maxNo+1,3);
                    $getNotrans[$bar] = str_replace("-","",$getData->response['result']['Header']['tanggal'])."/".$getData->response['result']['Header']['kodeorg']."/".$bar."/".$currNo;
                }
                
                $sql = "select * from " . $dbname . ".kebun_aktifitas where "."notransaksi='" . $getNotrans[$bar] . "'";
                $res = fetchData($sql);
                if (count($res) > 0) {
                    $notrtemp = explode("/",$getNotrans[$bar]);
                    $fWhere = "tanggal='".$notrtemp[0]."' and kodeorg='".$notrtemp[1]."' and tipetransaksi='".$bar."'";
                    $str = "select max(substr(notransaksi,-3)) as notr from " . $dbname . ".kebun_aktifitas where ".$fWhere." limit 1";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar=$res->fetch();
                    
                    $trtemp = addZero((intval($bar['notr'])+1),3);
                    $getNotrans[$bar]=str_replace($notrtemp[3],$trtemp,$getNotrans[$bar]);
                }

                // echo "<pre>";
                // print_r($getNotrans);
                // echo "</pre>";
                
                $arrHead = array(
                    'notransaksi'   => $getNotrans[$bar], 
                    'tipetransaksi' => $bar,
                    'tanggal'       => $getData->response['result']['Header']['tanggal'],
                    'nobkm'         => $nobkm,
                    'kodeorg'       => $getData->response['result']['Header']['kodeorg'],
                    'nikmandor'     => $getData->response['result']['Header']['nikmandor'],
                    'nikmandor1'    => $getData->response['result']['Header']['nikmandor1'],
                    'nikasisten'    => $getData->response['result']['Header']['nikasisten'],
                    'keranimuat'    => "",
                    'jurnal'        => 0,
                    'nospk'         => null,
                    'noreferensi'   => $getData->response['result']['Header']['notransaksi'],
                    'photo'         => $getData->response['result']['Header']['photo'],
                    'photoakhir'    => $getData->response['result']['Header']['photo2'],
                    'updateby'      => $_SESSION['standard']['userid'],
                    'divisi'        => $getData->response['result']['Header']['divisi'],
                    'deviceid'      => $getData->response['result']['Header']['deviceid']
                );
            }
            $colsHead = array();
            foreach ($arrHead as $key => $row) {
                $colsHead[] = $key;
            }
    
            try {
                // Execute Insert Header
                $insHead = insertQuery($dbname,"kebun_aktifitas",$arrHead,$colsHead);
                $owlPDO->exec($insHead);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        
        // echo "<pre>";
        // print_r($getData->response['result']['Prestasi']);
        // echo "</pre>";

        /* ============================================================= DETAIL KEBUN_PRESTASI BKM ======================================================================== */
        try {
            $hasilkerjaha = array();
            foreach ($getData->response['result']['Prestasi'] as $key => $val) {
                // Cek Kode Kegiatan Masuk Ke TM,TBM,TB, atau BBT
                $sKel = "SELECT kodekegiatan,kelompok FROM $dbname.setup_kegiatan WHERE kodekegiatan='".$val['kodekegiatan']."'";
                $rKel = fetchData($sKel);
                foreach ($rKel as $barc) {
                    if ($barc['kelompok'] == "" || $val['kodekegiatan'] == "") {
                        $stb[$barc['kodekegiatan']] = "BKM";
                    } elseif ($barc['kelompok'] == "PNN") {
                        $stb[$barc['kodekegiatan']] = "TM";
                    } else {
                        $stb[$barc['kodekegiatan']] = $barc['kelompok'];
                    }
                }
                
                // Get Hasil Kerja (HA) Per Kegiatan
                $hasilkerjaha[$val['kodekegiatan']] += $val['hasilkerja'];
                // Get Nomor Urut untuk kebun_prestasi dari notransaksi yang terbentuk
                $str = "select max(nourut) as nourut from ".$dbname.".kebun_prestasi where notransaksi='".$getNotrans[$stb[$val['kodekegiatan']]]."' limit 1";
                $res = fetchdata($str);
                if(count($res)==0){
                    $nomorRawat=1;
                }else{
                    $nomorRawat=floatval($res[0]['nourut'])+1;
                }
            
                $arrPrestasi = array(
                    'notransaksi'        => $getNotrans[$stb[$val['kodekegiatan']]],
                    'nobkm'              => $nobkm,
                    'noreferensi'        => $getData->response['result']['Header']['notransaksi'],
                    'nourut'             => $nomorRawat,
                    'nik'                => "-",
                    'nikpemel'           => $val['karyawanid'],
                    'kodekegiatan'       => $val['kodekegiatan'],
                    'kodeorg'            => $val['kodeorg'],
                    'hasilkerja'         => $val['hasilkerja'],
                    'jumlahhk'           => $val['jumlahhk'],
                    'upahpremi'          => $val['hasilkerjapremi'],
                    'photo'              => $val['photo'],
                    'photoakhir'         => $val['photo2'],
                    'tahuntanam'         => 0,
                );

                $colsPrestasi = array();
                foreach ($arrPrestasi as $key => $row) {
                    $colsPrestasi[] = $key;
                }

                // Execute Insert Prestasi
                $insPrestasi = insertQuery($dbname,"kebun_prestasi",$arrPrestasi,$colsPrestasi);
                $owlPDO->exec($insPrestasi);
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        
        /* ============================================================= DETAIL KEBUN_KEHADIRAN BKM ======================================================================== */
        try {
            foreach ($getData->response['result']['Kehadiran'] as $key => $val) {
                // Cek Kode Kegiatan Masuk Ke TM,TBM,TB, atau BBT
                $sKel = "SELECT kodekegiatan,kelompok FROM $dbname.setup_kegiatan WHERE kodekegiatan='".$val['kodekegiatan']."'";
                $rKel = fetchData($sKel);
                foreach ($rKel as $barc) {
                    if ($barc['kelompok'] == "" || $val['kodekegiatan'] == "") {
                        $stbkhdr[$barc['kodekegiatan']] = "BKM";
                    } elseif ($barc['kelompok'] == "PNN") {
                        $stbkhdr[$barc['kodekegiatan']] = "TM";
                    } else {
                        $stbkhdr[$barc['kodekegiatan']] = $barc['kelompok'];
                    }
                }
                // Get Nomor Urut untuk kebun_kehadiran dari notransaksi yang terbentuk
                $str = "select max(nourut) as nourut from ".$dbname.".kebun_kehadiran where notransaksi='".$getNotrans[$stbkhdr[$val['kodekegiatan']]]."' limit 1";
                $res = fetchdata($str);
                if(count($res)==0){
                    $nomorHadir=1;
                }else{
                    $nomorHadir=floatval($res[0]['nourut'])+1;
                }

                $arrKehadiran = array(
                    'notransaksi'        => $getNotrans[$stbkhdr[$val['kodekegiatan']]],
                    'nourut'             => $nomorHadir,
                    'nik'                => $val['karyawanid'],
                    'absensi'            => "H",
                    'jhk'                => $val['jumlahhk'],
                    'umr'                => ($gjPokok[$val['karyawanid']] * $val['jumlahhk']),
                    'insentif'           => $val['hasilkerjapremi'],
                    'penalty'            => 0,
                );

                $colsKehadiran = array();
                foreach ($arrKehadiran as $key => $row) {
                    $colsKehadiran[] = $key;
                }

                // Execute Insert Kehadiran BKM
                $insKehadiran = insertQuery($dbname,"kebun_kehadiran",$arrKehadiran,$colsKehadiran);
                $owlPDO->exec($insKehadiran);
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        /* ============================================================= DETAIL KEBUN_PAKAIMATERIAL BKM ======================================================================== */
        try {
           $periodeMatr = substr($sekarang2,0,7);
            foreach ($getData->response['result']['Material'] as $key => $val) {
                // Cek Kode Kegiatan Masuk Ke TM,TBM,TB, atau BBT
                $sKel = "SELECT kodekegiatan,kelompok FROM $dbname.setup_kegiatan WHERE kodekegiatan='".$val['kodekegiatan']."'";
                $rKel = fetchData($sKel);
                foreach ($rKel as $barc) {
                    if ($barc['kelompok'] == "" || $val['kodekegiatan'] == "") {
                        $stbkhdr[$barc['kodekegiatan']] = "BKM";
                    } elseif ($barc['kelompok'] == "PNN") {
                        $stbkhdr[$barc['kodekegiatan']] = "TM";
                    } else {
                        $stbkhdr[$barc['kodekegiatan']] = $barc['kelompok'];
                    }
                }

                $hargaratabarang[$val['kodegudang']][$val['kodebarang']] = 0;
                // Jika ada datanya Maka di eksekusi, jika tidak maka lewatkan
                if($getData->response['result']['Header']['kodeorg']!='' and $val['kodekegiatan']!='' and $val['kodeorg']!='' and $val['kodebarang']!='' and $val['kwantitas']!='0' and $val['kodegudang']!='') {
                    // Hapus dulu data yang lama
                    $str = "delete from " . $dbname . ".kebun_pakaimaterial where notransaksi='".$getNotrans[$stbkhdr[$val['karyawanid']]]."' and kodeorg='".$val['kodeorg']."' and kodebarang='".$val['kodebarang']."' and kodekegiatan='".$val['kodekegiatan']."'";
                    $owlPDO->exec($str);
                    
                    // ambil harga rata2 barang
                    $str = "select hargarata from ".$dbname.".log_5saldobulanan where kodegudang='".$val['kodegudang']."' and kodebarang='".$val['kodebarang']."'  and periode='".$periodeMatr."' order by periode desc limit 1";
                    $res=fetchdata($str);
                    foreach ($res as $bar) {
                        $hargaratabarang[$val['kodegudang']][$val['kodebarang']] = $bar['hargarata'];
                    }

                    $arrMaterial = array(
                        'notransaksi'     => $getNotrans[$stbkhdr[$val['kodekegiatan']]],
                        'nobkm'           => $getData->response['result']['Header']['notransaksi'],
                        'kodekegiatan'    => $val['kodekegiatan'],
                        'kodeorg'         => $val['kodeorg'],
                        'kodebarang'      => $val['kodebarang'],
                        'kwantitas'       => $val['kwantitas'],
                        'kodegudang'      => $val['kodegudang'],
                        'kwantitasha'     => $hasilkerjaha[$val['kodekegiatan']],
                        'hargasatuan'     => $hargaratabarang[$val['kodegudang']][$val['kodebarang']],
                    );
    
                    $colsMaterial = array();
                    foreach ($arrMaterial as $key => $row) {
                        $colsMaterial[] = $key;
                    }

                    // Execute Insert Material
                    $insMaterial = insertQuery($dbname,"kebun_pakaimaterial",$arrMaterial,$colsMaterial);
                    $owlPDO->exec($insMaterial);
                }
            }

        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        /* ============================================================= DETAIL KEHADIRAN UMUM BKM ======================================================================== */
        try {
            // Jika Ada Datanya Maka Eksekusi, Jika tidak ada maka dilewatkan saja
            if (count($getData->response['result']['kehadiranumum']) > 0) {
                foreach ($getData->response['result']['kehadiranumum'] as $key => $val) {
                    foreach ($arrstblok as $bar) {
                        if ($bar == "") {
                            $bar = "BKM";
                        }
                        if ($bar == "PNN") {
                            $bar = "TM";
                        }

                        // Cek Datanya Apakah sudah ada di sdm_absensiht
                        $str = "select * from " . $dbname . ".sdm_absensiht where tanggal='".$val['tanggal']."' and kodeorg='".$val['divisi']."'";
                        $res=count(fetchData($str));
                        // Jika Header sdm_absensiht belum ada maka eksekusi pembentukan sdm_absensiht
                        if ($res == 0) {
                            $dtabsHt = array(
                                'tanggal' => $val['tanggal'],
                                'kodeorg' => $val['divisi'],
                                'periode' => substr($val['tanggal'],0,7),
                                'updateby'=> $_SESSION['standard']['userid']
                            );
                            
                            $colsHt = array();
                            foreach($dtabsHt as $key=>$row) {
                                    $colsHt[] = $key;
                            }
                
                            # Insert sdm_absensiht
                            $insHt = insertQuery($dbname,'sdm_absensiht',$dtabsHt,$colsHt);
                            $owlPDO->exec($insHt);
                        }

                        if ($val['insentif'] == null || $val['insentif'] == '') {
                            $val['insentif'] = 0;    
                        }

                        $sCekDt = selectQuery($dbname,"sdm_absensidt","*","kodeorg='".$val['divisi']."' AND tanggal='".$val['tanggal']."'AND karyawanid='".$val['nik']."'");
                        $rCekDt = fetchData($sCekDt);
                        if (count($rCekDt) > 0) {
                            $qDel = deleteQuery($dbname,"sdm_absensidt","kodeorg='".$val['divisi']."' AND tanggal='".$val['tanggal']."' AND karyawanid='".$val['nik']."'");
                            $owlPDO->exec($qDel);
                        }
        
                        // Eksekusi sdm_absensidt
                        $dtabsDT = array(
                            'kodeorg'   => $val['divisi'],
                            'tanggal'   => $val['tanggal'],
                            'karyawanid'=> $val['nik'],
                            'noakun'    => $val['noakun'],
                            'absensi'   => $val['absensi'],
                            'premi'     => $val['insentif'],
                            'hk'        => $val['jhk'],
                            'umr'       => ($gjPokok[$val['nik']] * $val['jhk']),
                            'penjelasan'=> $val['keterangan'],
                            'norefrensi'=> $getNotrans[$bar],
                            'nobkm'     => $nobkm
                        );
                        
                        $colsDT = array();
                        foreach($dtabsDT as $key=>$row) {
                                $colsDT[] = $key;
                        }
            
                        # Insert sdm_absensidt
                        $query = insertQuery($dbname,'sdm_absensidt',$dtabsDT,$colsDT);
                        $owlPDO->exec($query);
                    }
                }
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        /* ============================================================= UPDATE FLAG KEBUN_AKTIFITAS_MOBILE API ======================================================================== */
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $urlFlag = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Mrawat/postdataerp/send';
            }else{
                $urlFlag = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Mrawat/postdataerp/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlFlag = $urlocal.'mobile/index.php/api/module/Mrawat/postdataerp/send';
        }
        $paramFlag = array(
            'notransaksi' => $param['notransaksi'],
            'flag'        => '1',
        );
        
        $dataFlag = $getApi->post($urlFlag, $paramFlag);
        if ($dataFlag->response['error'] == true) {
            exit("Warning: Tidak Berhasil Download Data !");
        }
        echo $dataFlag->response['message'];
        // exit("Warning:");
    break;

    case 'kehadiranumum':
        // Cek Dulu Apakah Datanya Sudah ada Di ERP Atau Belum
        $sHead = "SELECT * FROM $dbname.kebun_aktifitas WHERE noreferensi='".$param['notransaksi']."'";
        $rHead = fetchdata($sHead);
        if (count($rHead) > 0) {
            // Jika Ada Data Berdasarkan No referensi munculkan validasi
            exit("Warning: Nomor Transaksi BKM Mobile<br>Di No. Referensi: ". $param['notransaksi'] ." Sudah Ada Di Buku Kegiatan Mandor !
                <br><br>Silakan menghapus data berdasarkan No. Referensi tersebut terlebuh dahulu.");

            // Jika Ada Data Berdasarkan No referensi maka di delete terlebih dahulu
            // if ($param['notransaksi'] != "") {
            //     $qDel = deleteQuery($dbname,"kebun_aktifitas","noreferensi='".$param['notransaksi']."'");
            //     $owlPDO->exec($qDel);
            // }
        }

      /** GET OPTIONS API */
       $options = array(
           'client_id' => 'USERSYSTEM',
           'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
           'username' => $_SESSION['standard']['username']
       );
       /** GET API KEY */
       // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
            }else{
                $url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal."mobile/index.php/api/access_token/api_key";
        }

        $getApi->init($url,$options);

        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $urlData = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Kehadiranumum/getDetail/send';
            }else{
                $urlData = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Kehadiranumum/getDetail/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlData = $urlocal.'mobile/index.php/api/module/Kehadiranumum/getDetail/send';
        }
        $paramData = array(
            'notransaksi' => $param['notransaksi']
        );
        $getData = $getApi->post($urlData, $paramData);

        // echo "<pre>";
        // print_r($getData->response);
        // echo "</pre>";

        /* ============================================================= CEK VALIDASI KEBUN_AKTIFITAS BKM ======================================================================== */
        // mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
        $sekarang= str_replace("-","",$getData->response['result']['header']['tanggal']);
        $sekarang2= $getData->response['result']['header']['tanggal'];
        if($sekarang < $_SESSION['org']['period']['start']){
            exit("Validation Error : Date out of range");
        }

        // Cek Apakah Para Mandor, Mandor 1, Kerani sudah ada di absensi umum atau tidak
        $str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$sekarang2."' and karyawanid in ('".$getData->response['result']['header']['nikmandor']."')";
        if(count(fetchData($str))>0){
            exit("Warning : Mandor sudah pernah diinput di menu Absensi.");
        }
        $str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$sekarang2."' and karyawanid in ('".$getData->response['result']['header']['nikmandor1']."')";
        if(count(fetchData($str))>0){
            exit("Warning : Mandor 1 sudah pernah diinput di menu Absensi.");
        }
        $str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$sekarang2."' and karyawanid in ('".$getData->response['result']['header']['kerani']."')";
        if(count(fetchData($str))>0){
            exit("Warning : Kerani sudah pernah diinput di menu Absensi.");
        }
        
        // Cek UMR untuk pekerja yang ada di bkm
        $sekarang2= $getData->response['result']['header']['tanggal'];
        $n = 0;
        $nmkaryerr = "";
        foreach ($getData->response['result']['sdm_absensi'] as $key => $val) {
            $str = "select karyawanid, sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$val['nik']."' and tahun='".substr($sekarang2,0,7)."' and idkomponen in ('1')";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                $umrHarian = $bar['nilai'] / 25;
                $gjPokok[$val['nik']] = ($bar['nilai'] / 25);
                $karyidgjpkk[$val['nik']] = $bar['karyawanid'];
            }

            if (empty($gjPokok[$val['nik']])) {
                $karyidgjpkk[$val['nik']] = $val['nik'];
                $mError = "Setup Gaji Pokok Belum ada Untuk Karyawan dibawah Berikut:<br>";
                $n++;
                $nmkaryerr .=$n.". ".getNamaKaryawan($karyidgjpkk[$val['nik']])."<br>";
            }
        }
        if($nmkaryerr != ''){
            exit("Warning: ".$mError.$nmkaryerr);
        }
        
        /* ============================================================= END CEK VALIDASI KEBUN_AKTIFITAS BKM ======================================================================== */
        
        
        /* ============================================================= HEADER KEBUN_AKTIFITAS BKM ======================================================================== */
        try {
            // GET NO BKM
            # Get Existing Data
            $fWhere = "tanggal='".$getData->response['result']['header']['tanggal']."' and kodeorg='".$getData->response['result']['header']['kodeorg']."' and tipetransaksi !='PNN'";
            $fQuery = selectQuery($dbname,'kebun_aktifitas','nobkm',$fWhere);
            $tmpNo = fetchData($fQuery);
            # Generate No Transaksi
            if(count($tmpNo)==0) {
                $nobkm = str_replace("-","",$getData->response['result']['header']['tanggal'])."/".$getData->response['result']['header']['kodeorg']."/BKM/001";
            } else {
                # Get Max No Urut
                $maxNo = 1;
                foreach($tmpNo as $row) {
                $tmpRow = explode('/',$row['nobkm']);
                $noUrut = (int)$tmpRow[3];
                if($noUrut>$maxNo)
                    $maxNo = $noUrut;
                }
                $currNo = addZero($maxNo+1,3);
                $nobkm = str_replace("-","",$getData->response['result']['header']['tanggal'])."/".$getData->response['result']['header']['kodeorg']."/BKM/".$currNo;
            }

            // GET NO Transaksaki
            # Get Existing Data
            $fWhere = "tanggal='".$getData->response['result']['header']['tanggal']."' and kodeorg='".$getData->response['result']['header']['kodeorg']."' and tipetransaksi='BKM'";
            $fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
            $tmpNo = fetchData($fQuery);
            # Generate No Transaksi
            if(count($tmpNo)==0) {
                $getNotrans = str_replace("-","",$getData->response['result']['header']['tanggal'])."/".$getData->response['result']['header']['kodeorg']."/BKM/001";
            } else {
                # Get Max No Urut
                $maxNo = 1;
                foreach($tmpNo as $row) {
                $tmpRow = explode('/',$row['notransaksi']);
                $noUrut = (int)$tmpRow[3];
                if($noUrut>$maxNo)
                    $maxNo = $noUrut;
                }
                $currNo = addZero($maxNo+1,3);
                $getNotrans = str_replace("-","",$getData->response['result']['header']['tanggal'])."/".$getData->response['result']['header']['kodeorg']."/BKM/".$currNo;
            }
            
            $sql = "select * from " . $dbname . ".kebun_aktifitas where "."notransaksi='" . $getNotrans . "'";
            $res = fetchData($sql);
            if (count($res) > 0) {
                $notrtemp = explode("/",$getNotrans);
                $fWhere = "tanggal='".$notrtemp[0]."' and kodeorg='".$notrtemp[1]."' and tipetransaksi='BKM'";
                $str = "select max(substr(notransaksi,-3)) as notr from " . $dbname . ".kebun_aktifitas where ".$fWhere." limit 1";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
                
                $trtemp = addZero((intval($bar['notr'])+1),3);
                $getNotrans=str_replace($notrtemp[3],$trtemp,$getNotrans);
            }

            $arrHead = array(
                'notransaksi'   => $getNotrans, 
                'tipetransaksi' => "BKM",
                'tanggal'       => $getData->response['result']['header']['tanggal'],
                'nobkm'         => $nobkm,
                'kodeorg'       => $getData->response['result']['header']['kodeorg'],
                'divisi'        => $getData->response['result']['header']['divisi'],
                'nikmandor'     => $getData->response['result']['header']['nikmandor'],
                'nikmandor1'    => $getData->response['result']['header']['nikmandor1'],
                'nikasisten'    => $getData->response['result']['header']['nikasisten'],
                'keranimuat'    => "",
                'jurnal'        => 0,
                'nospk'         => null,
                'noreferensi'   => $getData->response['result']['header']['notransaksi'],
                'updateby'      => $_SESSION['standard']['userid'],
                'divisi'        => $getData->response['result']['header']['divisi'],
                'deviceid'      => $getData->response['result']['header']['deviceid']
            );
    
            $colsHead = array();
            foreach ($arrHead as $key => $row) {
                $colsHead[] = $key;
            }
            
             // Execute Insert Header
            $insHead = insertQuery($dbname,"kebun_aktifitas",$arrHead,$colsHead);
            $owlPDO->exec($insHead);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }


        /* ============================================================= INSERT SDM_ABSENSIHT & SDM_ABSENSIDT ======================================================================== */
        try {
            foreach ($getData->response['result']['sdm_absensi'] as $key => $val) {
                // Cek Datanya Apakah sudah ada di sdm_absensiht
                $str = "select * from " . $dbname . ".sdm_absensiht where tanggal='".$getData->response['result']['header']['tanggal']."' and kodeorg='".$getData->response['result']['header']['divisi']."'";
                $res=count(fetchData($str));
                // Jika Header sdm_absensiht belum ada maka eksekusi pembentukan sdm_absensiht
                if ($res == 0) {
                    $dtabsHt = array(
                        'tanggal' => $getData->response['result']['header']['tanggal'],
                        'kodeorg' => $getData->response['result']['header']['divisi'],
                        'periode' => substr($getData->response['result']['header']['tanggal'],0,7),
                        'updateby'=> $_SESSION['standard']['userid']
                    );
                    
                    $colsHt = array();
                    foreach($dtabsHt as $key=>$row) {
                            $colsHt[] = $key;
                    }
        
                    # Insert sdm_absensiht
                    $insHt = insertQuery($dbname,'sdm_absensiht',$dtabsHt,$colsHt);
                    $owlPDO->exec($insHt);
                }

                // Cek apakah cutinya di bayar atau tidak
                // $sAbs = selectQuery($dbname,"sdm_5absensi","nilaihk","kodeabsen='".$val['absensi']."'");
                // $rAbs = fetchData($sAbs);
                // $nilaihk = $rAbs[0]['nilaihk'];
                // if ($nilaihk == 0 || $nilaihk == null || $nilaihk == '') {
                    
                // }

                // if (empty($val['jhk'])) {
                //     $gjPokok[$val['nik']] = 0;
                // }

                if ($val['insentif'] == null || $val['insentif'] == '') {
                    $val['insentif'] = 0;    
                }

                $sCekDt = selectQuery($dbname,"sdm_absensidt","*","kodeorg='".$getData->response['result']['header']['divisi']."' AND tanggal='".$getData->response['result']['header']['tanggal']."'AND karyawanid='".$val['nik']."'");
                $rCekDt = fetchData($sCekDt);
                if (count($rCekDt) > 0) {
                    $qDel = deleteQuery($dbname,"sdm_absensidt","kodeorg='".$getData->response['result']['header']['divisi']."' AND tanggal='".$getData->response['result']['header']['tanggal']."' AND karyawanid='".$val['nik']."'");
                    $owlPDO->exec($qDel);
                }

                // Eksekusi sdm_absensidt
                $dtabsDT = array(
                    'kodeorg'   => $getData->response['result']['header']['divisi'],
                    'tanggal'   => $getData->response['result']['header']['tanggal'],
                    'karyawanid'=> $val['nik'],
                    'noakun'    => $val['noakun'],
                    'absensi'   => $val['absensi'],
                    'premi'     => $val['insentif'],
                    'hk'        => $val['jhk'],
                    'umr'       => ($gjPokok[$val['nik']] * $val['jhk']),
                    'penjelasan'=> $val['keterangan'],
                    'norefrensi'=> $getNotrans,
                    'nobkm'     => $nobkm
                );
                
                $colsDT = array();
                foreach($dtabsDT as $key=>$row) {
                        $colsDT[] = $key;
                }
    
                # Insert sdm_absensidt
                $query = insertQuery($dbname,'sdm_absensidt',$dtabsDT,$colsDT);
                $owlPDO->exec($query);
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }


        /* ============================================================= UPDATE FLAG KEBUN_AKTIFITAS_MOBILE API ======================================================================== */
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $urlFlag = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Kehadiranumum/postERP/send';
            }else{
                $urlFlag = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Kehadiranumum/postERP/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $urlFlag = $urlocal.'mobile/index.php/api/module/Kehadiranumum/postERP/send';
        }
        $paramFlag = array(
            'notransaksi' => $param['notransaksi'],
            'flag'        => '1',
        );
        
        $dataFlag = $getApi->post($urlFlag, $paramFlag);
        if ($dataFlag->response['error'] == true) {
            exit("Warning: Tidak Berhasil Download Data !");
        }
        echo $dataFlag->response['message'];
        // exit("Warning");
    break;
}
?>