<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');


$kodeorg1 = checkPostGet('kodeorg1', '');
$kodebarang1 = checkPostGet('kodebarang1', '');
$proses = checkPostGet('proses', '');
$tgl1_1 = tanggalsystemn(checkPostGet('tgl1_1', ''));
$tgl2_1 = tanggalsystemn(checkPostGet('tgl2_1', ''));


if ($kodeorg1 == '') {
    echo"error: Please choose Mill.";
    exit;
}
if ($tgl1_1 == '--' || $tgl2_1 == '--') {
    echo"error: Please choose dates.";
    exit;
}

$str = "select nokontrak, koderekanan from " . $dbname . ".pmn_kontrakjual";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $kontrak[$bar->nokontrak] = $bar->koderekanan;
}
$str = "select kodecustomer,namacustomer from " . $dbname . ".pmn_4customer";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $kustom[$bar->kodecustomer] = $bar->namacustomer;
}
$sBrg = "select namabarang,kodebarang from " . $dbname . ".log_5masterbarang where kodebarang in ('40000001', '40000002')";
$qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rBrg = $qBrg->fetch()) {
    $barang[$rBrg['kodebarang']] = $rBrg['namabarang'];
}

$stream = '';
$border = 0;
if ($proses == 'excel') {
    $border = 1;
    $stream.=$_SESSION['lang']['pabrik'] . " : " . $kodeorg1 . "<br>";
    $stream.=$_SESSION['lang']['komoditi'] . " : " . $barang[$kodebarang1] . "<br>";
    $stream.=$_SESSION['lang']['tanggal'] . " : " . $tgl1_1 . " - " . $tgl2_1 . "<br>";
}
$stream.=" <table class=sortable cellspacing=1 border=" . $border . " width=100%>
    <thead>
        <tr class=rowheader>
            <td align=center>No.</td>
            <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
            <td align=center>" . $_SESSION['lang']['noTiket'] . "</td>
            <td align=center>" . $_SESSION['lang']['customer'] . "</td>
            <td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>
            <td align=center>" . $_SESSION['lang']['nodo'] . "</td>
            <td align=center>" . $_SESSION['lang']['komoditi'] . "</td>
            <td align=center>" . $_SESSION['lang']['jumlah'] . " (kg)</td>
            <td align=center>" . $_SESSION['lang']['kendaraan'] . "</td>
            <td align=center>" . $_SESSION['lang']['supir'] . "</td>
	</tr>
    </thead><tbody>";
$no = 1;
$total = 0;
$optKomoditi = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$sql = "select tanggal,notransaksi,nokontrak,nodo,beratbersih,nokendaraan,supir,kodebarang from " . $dbname . ".pabrik_timbangan where millcode = '" . $kodeorg1 . "' and kodebarang like '%" . $kodebarang1 . "%' and tanggal between '" . $tgl1_1 . " 00:00:00' and '" . $tgl2_1 . " 23:59:59' order by tanggal asc";
$query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$row = owlBaris($query);

if ($row > 0) {
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $kontrak[$res['nokontrak']] = isset($kontrak[$res['nokontrak']]) ? $kontrak[$res['nokontrak']] : '';
        $kustom[$kontrak[$res['nokontrak']]] = isset($kustom[$kontrak[$res['nokontrak']]]) ? $kustom[$kontrak[$res['nokontrak']]] : '';

        $stream.="<tr class=rowcontent>";
        $stream.="<td align=center>" . $no . "</td>";
        if ($proses == 'preview')
            $stream.="<td  align=center>" . tanggalnormal(substr($res['tanggal'], 0, 10)) . "</td>";
        if ($proses == 'excel')
            $stream.="<td>" . substr($res['tanggal'], 0, 10) . "</td>";
        $stream.="<td>" . $res['notransaksi'] . "</td>";
        $stream.="<td>" . $kustom[$kontrak[$res['nokontrak']]] . "</td>";
        $stream.="<td>" . $res['nokontrak'] . "</td>";
        $stream.="<td>" . $res['nodo'] . "</td>";
        $stream.="<td>" . $optKomoditi[$res['kodebarang']] . "</td>";
        $stream.="<td align=right>" . number_format($res['beratbersih'], 0) . "</td>";
        $stream.="<td>" . $res['nokendaraan'] . "</td>";
        $stream.="<td>" . $res['supir'] . "</td>";
        $stream.="</tr>";
        $no+=1;
        $total+=$res['beratbersih'];
    }
    $stream.="<tr class=rowcontent>";
    $stream.="<td align=center colspan=7>Total</td>";
    $stream.="<td align=right>" . number_format($total, 0) . "</td>";
    $stream.="<td align=center colspan=2></td>";
    $stream.="</tr>";
    $no+=1;
}
else {
    $stream.="<tr class=rowcontent align=center><td colspan=9>Not Found</td></tr>";
}
$stream.="</tbody></table>";

switch ($proses) {
    case'preview':
        echo $stream;
        break;
    case'excel':

        $stream.="</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];

        $nop_ = "Penjualan Harian " . $kodeorg1 . " " . $kodebarang1 . " " . $tgl1_1 . "-" . $tgl2_1;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                window.location='tempExcel/" . $nop_ . ".xls';
                </script>";
            }
            fclose($handle);
        }
        break;
    default:
        break;
}
?>