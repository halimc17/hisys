<?php
// require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$tab = '';
$method = checkPostGet('method', '');
$sn 	= checkPostGet('sn', '');
$kode 	= checkPostGet('kode', '');
$ch 	= checkPostGet('ch', '');
$datetime 	= checkPostGet('datetime', '');
$explodedatetime = explode(" ", $datetime);
$tanggal = $explodedatetime[0];
$waktu = $explodedatetime[1];

switch ($method) {
    case 'intch':
        $response = [];
		
        try {

            // Insert data ke kebun_tempcurahhujan dulu
            $data = [$sn, $kode, $ch, $datetime];
            $cols = ['sn', 'kode', 'ch', 'datetime'];
            $insertQuery = insertQuery($dbname, 'kebun_tempcurahhujan', $data, $cols);
            try {
                $owlPDO->exec($insertQuery);
            } catch (PDOException $e) {
                echo 'Error: '.$e->getMessage();
                die();
            }
            // Get data yang di post terus convert agar bisa di insert ke kebun_curahhujan
            $curahhujan = [];
            $jam_from_waktu = substr($waktu, 0, 2);
            if ($jam_from_waktu >= 8 && $jam_from_waktu <= 14) {
                $curahhujan[$tanggal]['pagi'] += $ch;
            } else if ($jam_from_waktu >= 14 && $jam_from_waktu <= 20) {
                $curahhujan[$tanggal]['siang'] += $ch;
            } else if ($jam_from_waktu >= 20 && $jam_from_waktu <= 2) {
                $curahhujan[$tanggal]['sore'] += $ch;
            } else {
                $curahhujan[$tanggal]['malam'] += $ch;
            }

            // Insert array curahhujan ke dalam table kebun_curahhujan
            foreach ($curahhujan as $keys => $values) {
                $pagi = $values['pagi'] ? $values['pagi'] : 0;
                $siang = $values['siang'] ? $values['siang'] : 0;
                $sore = $values['sore'] ? $values['sore'] : 0;
                $malam = $values['malam'] ? $values['malam'] : 0;

                // Cek jika data curahhujan sudah ada 
                $qCurahhujan = selectQuery($dbname, 'kebun_curahhujan', "*", "kodeorg = '".$kode."' AND tanggal = '".substr($datetime,0,10)."' AND flag = 'OMRO'");
                $resCurahhujan = fetchData($qCurahhujan);
                $ttlch = $resCurahhujan[0]['pagi'] + $resCurahhujan[0]['siang'] + $resCurahhujan[0]['sore'] +  $resCurahhujan[0]['malam'] + $pagi + $siang + $sore + $malam;
                if ($ttlch <= 0.4) {
                    $catatan = "Berawan";
                } else if ($ttlch <= 20 && $ttlch >= 0.5) {
                    $catatan = "Hujan Ringan";
                } else if ($ttlch <= 50 && $ttlch >= 20.1) {
                    $catatan = "Hujan Sedang";
                } else if ($ttlch <= 100 && $ttlch >= 50.1) {
                    $catatan = "Hujan Lebat";
                } else if ($ttlch <= 150 && $ttlch >= 100.1) {
                    $catatan = "Hujan Sangat Lebat";
                } else {
                    $catatan = "Hujan Ekstrem";
                }
                
                if ($resCurahhujan[0]['kodeorg'] == $kode && $resCurahhujan[0]['tanggal'] == $tanggal) {
                    $pagi = $resCurahhujan[0]['pagi'] + $pagi ? $resCurahhujan[0]['pagi'] + $pagi : 0;
                    $siang = $resCurahhujan[0]['siang'] + $siang ? $resCurahhujan[0]['siang'] + $siang : 0;
                    $sore = $resCurahhujan[0]['sore'] + $sore ? $resCurahhujan[0]['sore'] + $sore : 0;
                    $malam = $resCurahhujan[0]['malam'] + $malam ? $resCurahhujan[0]['malam'] + $malam : 0; 
                    $data = ["pagi" => $pagi, "siang" => $siang, "sore" => $sore, "malam" => $malam, "catatan" => $catatan];
                    $updateQuery = updateQuery($dbname, 'kebun_curahhujan', $data, "kodeorg = '".$kode."' AND tanggal = '".$tanggal."'");
                    try {
                        $owlPDO->exec($updateQuery);
                    } catch (PDOException $e) {
                        echo "Error: ".$e->getMessage();
                    }
                } else {

                    $cols = ["kodeorg", "tanggal", "pagi", "siang", "sore", "malam", "catatan", "flag"];
                    $data = [$kode,$keys,$pagi,$siang,$sore,$malam, $catatan, "OMRO"];
                    $insertQuery = insertQuery($dbname, 'kebun_curahhujan', $data, $cols);
                    try {
                        $owlPDO->exec($insertQuery);
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
                }
           }

           $response['status'] = '200';
           $response['error'] = false;
           $response['message'] = "Data inserted successfully.";

        } catch (PDOException $e) {
            $response['status'] = '500';
            $response['error'] = true;
            $response['message'] = $e->getMessage();
        }

        echo json_encode($response);

    break;
	default:
		// code...
    break;
}


?>