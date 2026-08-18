<?php
require_once('lib/zLib.php');
###setting visudo : www-data ALL=NOPASSWD: ALL
###Data timbangan dapat dari databse
$query = "SELECT * FROM mssystem";
$dataTimbangan = fetchdata($query)[0];
$port = $dataTimbangan['port'];
$baudrate = $dataTimbangan['baudrate'];
$databit = $dataTimbangan['databit'];
$parity = $dataTimbangan['parity'];

switch ($_GET['method']) {
    case'getport': 
        $respon = [
        'status' => true,
        'data' => [
            'port' => $port,
            'baudrate' => $baudrate,
            'databit' => $databit,
            'parity' => $parity
        ]
        ];
    break;
    default:
    $respon =[
        'status' => false,
        'data' => 'Method salah',
    ];
    break;
}
echo json_encode($respon,JSON_PRETTY_PRINT);

?>
