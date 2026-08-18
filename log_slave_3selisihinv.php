<?php

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
//##kdkegiatan##ket##satuan##nilsngtbaik##nilbaik##nilckp##nilkrg##method
$kodeorg = $_POST['kodeorg'];
$periode = $_POST['periode'];
$method = $_POST['method'];


switch ($method) {
    case 'load':
    #ambil data Mutasi
    $str = "select * from " . $dbname . ".log_transaksi_vw where kodegudang ='" . $kodeorg . "' and tanggal like '%".$periode."%' and tipetransaksi=7 and statusjurnal=1";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $kodept = '';
    while ($bar = $res->fetch()) {
        $notransaksi[$bar->notransaksi]=$bar->notransaksi;
        $arrdata[$bar->notransaksi][$bar->kodebarang]=$bar->hargarata;
        $arrhargarata[$bar->notransaksi][$bar->kodebarang]=$bar->hargarata;
    
    }

    #ambil data Terima Mutasi
    $str = "select * from " . $dbname . ".log_transaksi_vw where notransaksireferensi in ('".implode("','",$notransaksi)."') ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $kodept = '';
    while ($bar = $res->fetch()) {
        $arrnoterima[$bar->notransaksireferensi][$bar->kodebarang]=$bar->notransaksi;
        $arrbrgterima[$bar->notransaksireferensi][$bar->kodebarang]=$bar->kodebarang;
        $arrhargaterima[$bar->notransaksireferensi][$bar->kodebarang]=$bar->hargasatuan;

    }


    $tab.="<table class='sortable' cellspacing='1' border='0' width=100%>
        <thead>
        <tr class=rowheader>
        <td align='center' rowspan=2>".$_SESSION['lang']['nourut']."</td>
        <td align='center' colspan=3>Mutasi</td>
        <td align='center' colspan=3>Terima Mutasi</td>
        </tr>
        <tr class=rowheader>
        <td align='center'>".$_SESSION['lang']['notransaksi']."</td>
        <td align='center'>".$_SESSION['lang']['namabarang']."</td>
        <td align='center'>".$_SESSION['lang']['harga']." Rata</td>
        <td align='center'>".$_SESSION['lang']['notransaksi']."</td>
        <td align='center'>".$_SESSION['lang']['namabarang']."</td>
        <td align='center'>".$_SESSION['lang']['harga']." Satuan</td>
        </tr>
        </thead>
        <tbody>";


    $nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
    foreach ($arrdata as $notrs => $v) {
        foreach ($v as $kdbrg => $v2) {
            if ($arrhargarata[$notrs][$kdbrg]!=$arrhargaterima[$notrs][$kdbrg]) {
               $no+=1;
               $tab.="<tr class=rowcontent>";
               $tab.="<td align='center'>".$no."</td>";
               $tab.="<td align='left'>".$notrs."</td>";
               $tab.="<td align='left'>".$nmbrg[$kdbrg]."</td>";
               $tab.="<td align='right'>".$arrhargarata[$notrs][$kdbrg]."</td>";
               $tab.="<td align='left'>".$arrnoterima[$notrs][$kdbrg]."</td>";
               $tab.="<td align='left'>".$nmbrg[$arrbrgterima[$notrs][$kdbrg]]."</td>";
               $tab.="<td align='right'>".$arrhargaterima[$notrs][$kdbrg]."</td>";
               $tab.="</tr>";
            }
          
        }
       
    }

    echo $tab;
    break;

}



?>
