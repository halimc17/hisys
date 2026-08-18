<?php

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
//##kdkegiatan##ket##satuan##nilsngtbaik##nilbaik##nilckp##nilkrg##method
$kodeorg = $_POST['kodeorg'];
$periode = $_POST['periode'];

#ambil kodePT:
$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . substr($kodeorg, 0, 4) . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$kodept = '';
while ($bar = $res->fetch()) {
    $kodept = $bar->induk;
}
if ($kodept == '') {
    exit(' Error: Org code is missing');
}
#ambil periode akunting
$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi where left(kodeorg,4)= '" . $kodeorg . "' and periode='" . $periode . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$mulai = '';
$sampai = '';
while ($bar = $res->fetch()) {
    $mulai = $bar->tanggalmulai;
    $sampai = $bar->tanggalsampai;
}
if ($mulai == '' or $sampai == '') {
    exit(" Error: periode akuntansi unit " . $kodeorg . " belum terdaftar");
} else {
#ambil transaksi material
    $bkmMat=$log = Array();
    $str = "select * from " . $dbname . ".kebun_pakai_material_detail_vw
              where tanggal>='" . $mulai . "' and tanggal<='" . $sampai . "' and jurnal=1 and kodeorg like '" . $kodeorg . "%'";
			  // echo $str;
	/*
	    $str = "select a.*,b.jurnal,b.tanggal,c.kodekegiatan from " . $dbname . ".kebun_pakaimaterial a 
			left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
             left join " . $dbname . ".kebun_prestasi c on a.notransaksi=c.notransaksi
              where b.tanggal>='" . $mulai . "' and b.tanggal<='" . $sampai . "' and b.jurnal=1 and b.kodeorg='" . $kodeorg . "'";
	*/		  
			  // echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
    while ($bar = $res->fetch()) {
		$bkmMat[] = $bar;
        $bkmLast[] = $bar;
    }
	$namabrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$bar['kodebarang']);
	
     #ambil transaksi gudang
    $str = "select notransaksireferensi,kodebarang from " . $dbname . ".log_transaksi_vw where 
			kodegudang like '" . $kodeorg . "%' and tanggal>='" . $mulai . "' and tanggal<='" . $sampai . "'
              and tipetransaksi=5 and notransaksireferensi is not null and notransaksireferensi!='' order by kodegudang";
			  // echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
    while ($bar = $res->fetch()) {
        $log[] = $bar;
    }
	
    if ((isset($log) ? count($log) : 0) > 0) {
        foreach ($log as $key => $val) {
            foreach ($bkmMat as $key1 => $val1) {
                if (($val1['notransaksi'] == $val['notransaksireferensi']) and ($val1['kodebarang']==$val['kodebarang'])) {
                    unset($bkmLast[$key1]);
                }
            }
        }
    }
	
    #material BKMyg tidak ada di log_transasi
    if ((isset($bkmLast) ? count($bkmLast) : 0) == 0) {
        echo "Transaction clear, All BKM material on period " . $periode . " has been listed on inventory";
    } else {
        if (isset($_POST['preview'])) {
            echo "Below transaction not registered on inventory:<br>";
            echo"<table class=sortable border=0 cellspacing=1>
				 <thead>
				  <tr class=rowheader>
					 <td>" . $_SESSION['lang']['notransaksi'] . "</td>
					 <td>" . $_SESSION['lang']['tanggal'] . "</td>
					 <td>" . $_SESSION['lang']['gudang'] . "</td>
					 <td>" . $_SESSION['lang']['kodebarang'] . "</td>
					 <td>" . $_SESSION['lang']['namabarang'] . "</td>
					 <td>" . $_SESSION['lang']['jumlah'] . "</td>
					  <td>" . $_SESSION['lang']['kodeblok'] . "</td>   
				   </tr></thead><tbody>";
            foreach ($bkmLast as $key => $val) {
                echo "<tr class=rowcontent>
						 <td>" . $val['notransaksi'] . "</td>
						 <td>" . $val['tanggal'] . "</td>
						 <td>" . $val['kodegudang'] . "</td>
						 <td>" . $val['kodebarang'] . "</td>
						 <td>" . $namabrg[$val['kodebarang']] . "</td>
						 <td align=right>" . $val['kwantitas'] . "</td>
						  <td>" . $val['kodeorg'] . "</td>   
					</tr>";
            }
            echo "</tbody><tfoot></table></table>";
        } else {
            #create transaction                              
            $nam = array();
            foreach ($bkmLast as $key => $val) {
                if (empty($nam[$val['kodegudang']])) {
                    $sTgl = "select tanggalsampai,tanggalmulai  from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $val['kodegudang'] . "' and periode='" . $periode . "'";
					$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
					$qTgl->setFetchMode(PDO::FETCH_ASSOC);
                    $rTgl = $qTgl->fetch();
                    $str = "select max(notransaksi) as notransaksi from " . $dbname . ".log_transaksiht where tipetransaksi=5 and kodegudang='" . $val['kodegudang'] . "' and tanggal between '" . $rTgl['tanggalmulai'] . "' and '" . $rTgl['tanggalsampai'] . "' and notransaksireferensi!='' order by notransaksi desc limit 1";
                    $qstr=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$qstr->setFetchMode(PDO::FETCH_ASSOC);
					$rstr = $qstr->fetch();
                    $nam[$val['kodegudang']] = substr($rstr['notransaksi'], 7, 4);
                }
                $nam[$val['kodegudang']] = intval($nam[$val['kodegudang']]) + 1;

                $num = str_pad($nam[$val['kodegudang']], 4, "0", STR_PAD_LEFT);
                $num = str_replace("-", "", $periode) . "M" . $num . "-GI-" . $val['kodegudang'];
                #ambil satuan
                $satuan = '';
                $str = "select satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $val['kodebarang'] . "'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
                while ($bar = $res->fetch()) {
                    $satuan = $bar->satuan;
                }


                #create header
                $dataMat['header'][] = array(
                    'tipetransaksi' => '5',
                    'notransaksi' => $num,
                    'tanggal' => $val['tanggal'],
                    'kodept' => $kodept,
                    'untukpt' => $kodept,
                    'nopo' => '',
                    'nosj' => '',
                    'keterangan' => 'Material BKM ',
                    'statusjurnal' => '1',
                    'kodegudang' => $val['kodegudang'],
                    'user' => $_SESSION['standard']['userid'],
                    'namapenerima' => '0',
                    'mengetahui' => $_SESSION['standard']['userid'],
                    'idsupplier' => '',
                    'nofaktur' => '',
                    'post' => '1',
                    'postedby' => $_SESSION['standard']['userid'],
                    'untukunit' => substr($val['kodeorg'], 0, 4),
                    'subunit' => '',
                    'notransaksireferensi' => $val['notransaksi'],
                    'gudangx' => '',
					'persetujuan1'=>0,
                    'hasilpersetujuan1'=>0,
                    'tanggalpersetujuan1'=>'0000-00-00',
                    'persetujuan2'=>0,
                    'hasilpersetujuan2'=>0,
                    'tanggalpersetujuan2'=>'0000-00-00',
					'namafile' => '',
					'departemen' => '',
					'karyawanid' =>0,
                    'lastupdate' => date('Y-m-d H:i:s'),
					'norequest' => '',
					'driver' => '',
					'hpdriver' => '',
					'nopol' => '',
					'jeniskendaraan' => '',
					'expeditor' => ''
                );
                #detail log_transaksidt 
                $dataMat['detail'][] = array(
                    'notransaksi' => $num,
                    'nopp' => '',
                    'kodebarang' => $val['kodebarang'],
                    'satuan' => $satuan,
                    'jumlah' => $val['kwantitas'],
                    'jumlahlalu' => 0,
                    'hargasatuan' => '0',
                    'ongkir' => '0',
                    'kodeblok' => $val['kodeorg'],
                    'waktutransaksi' => date('Y-m-d H:i:s'),
                    'updateby' => $_SESSION['standard']['userid'],
                    'kodekegiatan' => $val['kodekegiatan'],
                    'kodemesin' => '',
                    'statussaldo' => 1,
                    'hargarata' => $val['hargasatuan'],
                    'nopo' => '',
                    'kodesegment' => '0000000001',
                    'catatan' => NULL,
                    'namafile' => NULL,
                    'kmhm' => 0,
                    'kodedptrmn' => '',
                    'statusblok' => NULL
                );
                $num = "";
            }

            $errorX = '';
            foreach ($dataMat['header'] as $key => $dataX) {
                $queryD = insertQuery($dbname, 'log_transaksiht', $dataX);
				try{
					$owlPDO->exec($queryD); 
				}catch(PDOException $e){
					$errorX = " Error insert header material :" . $queryD . ":" . $e->getMessage() . "\n";
				}
            }
            if ($errorX != '') {
                #rollback material
                foreach ($dataMat['header'] as $key => $dataX) {
                    $queryD = " delete from " . $dbname . ".log_transaksiht where notransaksi='" . $dataX['notransaksi'] . "'";
                    try{
						$owlPDO->exec($queryD); 
					}catch(PDOException $e){
						
					}
                }
                echo $errorX;
            } else {
                #insert detail
                $errorY = '';
                foreach ($dataMat['detail'] as $key => $dataY) {
                    $queryD = insertQuery($dbname, 'log_transaksidt', $dataY);
                    try{
						$owlPDO->exec($queryD); 
					}catch(PDOException $e){
						$errorY = " Error insert detail material :" . $queryD . ":" . $e->getMessage() . "\n";
					}
                }
                if ($errorY != '') {#rollback header only
                    foreach ($dataMat['header'] as $key => $dataX) {
                        $queryD = " delete from " . $dbname . ".log_transaksiht where notransaksi='" . $dataX['notransaksi'] . "'";
						try{
							$owlPDO->exec($queryD); 
						}catch(PDOException $e){
							
						}
                    }
                    echo $errorY;
                }
            }
        }
    }
}
?>
