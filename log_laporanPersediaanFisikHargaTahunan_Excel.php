<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt = $_GET['pt'];
$gudang = $_GET['gudang'];
$periode = $_GET['periode'];
$stream = '';
//nyari barang
//nyari barang
if ($gudang == '') {
    $str = "select a.kodebarang, b.satuan, b.namabarang from " . $dbname . ".log_5saldobulanan a
    left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang
    where a.kodeorg='" . $pt . "' 
    and a.periode like '" . $periode . "%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    order by a.kodebarang";
} else {
    $str = "select a.kodebarang, b.satuan, b.namabarang from " . $dbname . ".log_5saldobulanan a
    left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang
    where a.kodeorg='" . $pt . "' and kodegudang = '" . $gudang . "'
    and a.periode like '" . $periode . "%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    order by a.kodebarang";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $arrBarang[$bar->kodebarang] = $bar->kodebarang;
    $kamussatuan[$bar->kodebarang] = $bar->satuan;
    $kamusnamabarang[$bar->kodebarang] = $bar->namabarang;
}
$sAwal="select distinct right(periode,2) as prd from ".$dbname.".setup_periodeakuntansi where periode like '".$periode."%' 
        and kodeorg in (select distinct kodegudang from ".$dbname.".log_5saldobulanan where kodeorg='".$pt."') order by periode asc";
$rAwal=fetchData($sAwal);
$prdbln=$rAwal[0]['prd'];
//nyari saldoawal
    if ($gudang == '') {
    $str = "
    select a.kodebarang,sum(a.saldoawalqty) as saldoawalqty,sum(a.nilaisaldoawal) as nilaisaldoawal from
    (select kodebarang ,kodegudang,min(periode),saldoawalqty , nilaisaldoawal  from " . $dbname . ".log_5saldobulanan
    where kodeorg='" . $pt . "' 
    and periode like '%" . $periode ."%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    group by kodebarang ,kodegudang order by kodebarang) a group by a.kodebarang order by a.kodebarang";
} else {
    $str = " select a.kodebarang,sum(a.saldoawalqty) as saldoawalqty,sum(a.nilaisaldoawal) as nilaisaldoawal from
    (select kodebarang ,kodegudang,min(periode),saldoawalqty , nilaisaldoawal  from " . $dbname . ".log_5saldobulanan
    where kodeorg='" . $pt . "' and kodegudang = '" . $gudang . "'
    and periode like '%" . $periode ."%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    group by kodebarang ,kodegudang order by kodebarang) a group by a.kodebarang order by a.kodebarang";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $arrAwal[$bar->kodebarang]['saldoawalqty'] = $bar->saldoawalqty;
    @$arrAwal[$bar->kodebarang]['hargaratasaldoawal'] = $bar->nilaisaldoawal / $bar->saldoawalqty;
    $arrAwal[$bar->kodebarang]['nilaisaldoawal'] = $bar->nilaisaldoawal;
}


//nyari tahun berjalan
if ($gudang == '') {
    $str = "select kodebarang, sum(qtymasuk) as qtymasuk, sum(qtykeluar) as qtykeluar, sum(qtymasukxharga) as qtymasukxharga, sum(qtykeluarxharga) as qtykeluarxharga 
    from " . $dbname . ".log_5saldobulanan
    where kodeorg='" . $pt . "' 
    and periode like '" . $periode . "%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    group by kodebarang
    order by kodebarang";
} else {
    $str = "select kodebarang, sum(qtymasuk) as qtymasuk, sum(qtykeluar) as qtykeluar, sum(qtymasukxharga) as qtymasukxharga, sum(qtykeluarxharga) as qtykeluarxharga 
    from " . $dbname . ".log_5saldobulanan 
    where kodeorg='" . $pt . "' and kodegudang = '" . $gudang . "'
    and periode like '" . $periode . "%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    group by kodebarang
    order by kodebarang";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $arrAwal[$bar->kodebarang]['qtymasuk'] = $bar->qtymasuk;
    $arrAwal[$bar->kodebarang]['qtykeluar'] = $bar->qtykeluar;
    $arrAwal[$bar->kodebarang]['qtymasukxharga'] = $bar->qtymasukxharga;
    $arrAwal[$bar->kodebarang]['qtykeluarxharga'] = $bar->qtykeluarxharga;
}

$stream.=$_SESSION['lang']['persediaanfisikharga'] . ": " . $pt . " " . $gudang . " : " . $periode . "<br>    
        <table border=1>
                <tr>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >No.</td>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['periode'] . "</td>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kodebarang'] . "</td>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['namabarang'] . "</td>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['satuan'] . "</td>
                  <td colspan=3 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['saldoawal'] . "</td>
                  <td colspan=3 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['masuk'] . "</td>
                  <td colspan=3 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['keluar'] . "</td>
                  <td colspan=3 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['saldo'] . "</td>
                </tr>
                <tr>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kuantitas'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['hargasatuan'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['totalharga'] . "</td>	   
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kuantitas'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['hargasatuan'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['totalharga'] . "</td>	   
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kuantitas'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['hargasatuan'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['totalharga'] . "</td>	   
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kuantitas'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['hargasatuan'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['totalharga'] . "</td>	   
                </tr>";
$no = 0;
$totalSaldoAwal = 0;
$totalMasuk = 0;
$totalKeluar = 0;
$totalSaldo = 0;
if (!empty($arrBarang))
    foreach ($arrBarang as $barang) {
        $no+=1;
        @$hargamasuk = $arrAwal[$barang]['qtymasukxharga'] / $arrAwal[$barang]['qtymasuk'];
        @$hargakeluar = $arrAwal[$barang]['qtykeluarxharga'] / $arrAwal[$barang]['qtykeluar'];
        @$salakqty = $arrAwal[$barang]['saldoawalqty'] + $arrAwal[$barang]['qtymasuk'] - $arrAwal[$barang]['qtykeluar'];
        @$salakrp = $arrAwal[$barang]['nilaisaldoawal'] + $arrAwal[$barang]['qtymasukxharga'] - $arrAwal[$barang]['qtykeluarxharga'];
        @$salakhar = $salakrp / $salakqty;
        if (!isset($arrAwal[$barang]['saldoawalqty']))
            $arrAwal[$barang]['saldoawalqty'] = 0;
        if (!isset($arrAwal[$barang]['hargaratasaldoawal']))
            $arrAwal[$barang]['hargaratasaldoawal'] = 0;
        if (!isset($arrAwal[$barang]['nilaisaldoawal']))
            $arrAwal[$barang]['nilaisaldoawal'] = 0;
        $stream.="<tr class=rowcontent>
        <td>" . $no . "</td>
        <td>" . $periode . "</td>
        <td>" . $barang . "</td>
        <td>" . $kamusnamabarang[$barang] . "</td>
        <td>" . $kamussatuan[$barang] . "</td>
        <td align=right class=firsttd>" . number_format($arrAwal[$barang]['saldoawalqty'], 2) . "</td>
        <td align=right>" . number_format($arrAwal[$barang]['hargaratasaldoawal'], 2) . "</td>
        <td align=right>" . number_format($arrAwal[$barang]['nilaisaldoawal'], 2) . "</td>
        <td align=right class=firsttd>" . number_format($arrAwal[$barang]['qtymasuk'], 2) . "</td>
        <td align=right>" . number_format($hargamasuk, 2) . "</td>
        <td align=right>" . number_format($arrAwal[$barang]['qtymasukxharga'], 2) . "</td>
        <td align=right class=firsttd>" . number_format($arrAwal[$barang]['qtykeluar'], 2) . "</td>
        <td align=right>" . number_format($hargakeluar, 2) . "</td>
        <td align=right>" . number_format($arrAwal[$barang]['qtykeluarxharga'], 2) . "</td>
        <td align=right class=firsttd>" . number_format($salakqty, 2) . "</td>
        <td align=right>" . number_format($salakhar, 2) . "</td>
        <td align=right>" . number_format($salakrp, 2) . "</td>
    </tr>";

        $totalSaldoAwal += $arrAwal[$barang]['nilaisaldoawal'];
        $totalMasuk += $arrAwal[$barang]['qtymasukxharga'];
        $totalKeluar += $arrAwal[$barang]['qtykeluarxharga'];
        $totalSaldo += $salakrp;
    }
$stream.="<tr class=rowcontent>
			<td colspan=5 style='text-align:center; font-weight:bold;'>" . strtoupper($_SESSION['lang']['total']) . "</td>
			<td></td>
			<td></td>
			<td style='text-align:right; font-weight:bold;'>" . number_format($totalSaldoAwal, 2) . "</td>
			<td></td>
			<td></td>
			<td style='text-align:right; font-weight:bold;'>" . number_format($totalMasuk, 2) . "</td>
			<td></td>
			<td></td>
			<td style='text-align:right; font-weight:bold;'>" . number_format($totalKeluar, 2) . "</td>
			<td></td>
			<td></td>
			<td style='text-align:right; font-weight:bold;'>" . number_format($totalSaldo, 2) . "</td>
		</tr>";
if (empty($arrBarang)) {
    echo"No data.";
    exit;
}
$stream.= "</table>";
$stream.="</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];


$nop_ = "MaterialBalanceWPrice";
if (strlen($stream) > 0) {
    if ($handle = opendir('tempExcel')) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.html") {
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
?>