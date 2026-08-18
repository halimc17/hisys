<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//$arr="##ptId##unitId##prdIdDr##prdIdSmp";

$param = $_POST;
if (isset($_GET['proses']) != '') {
    if ($_GET['proses'] == 'excel') {
        $param = $_GET;
    } else {
        $param['proses'] = $_GET['proses'];
    }
}
#arrays
$optNmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$optNmbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optKlmpKbrg = makeOption($dbname, 'log_5klbarang', 'kode,kelompok');

$nmTipe = array("0" => "Koreksi", "1" => "Masuk", "2" => "Pengembalian pengeluaran(retur)", "3" => "Penerimaan Mutasi", "5" => "Pengeluaran", "6" => "Pengembalian penerimaan", "7" => "Pengeluaran mutasi");

$whr = $whrd = "";
if ($param['unitId'] != '') {
    $whr.=" and left(kodegudang,4)='" . $param['unitId'] . "'";
}
if (($param['prdIdDr'] != '') || ($param['prdIdSmp'] != '')) {
    if ($param['ptId'] == '') {
        exit("error: " . $_SESSION['lang']['pt'] . " tidak boleh kosong");
    }
    $whrd = "and left(tanggal,7) between '" . $param['prdIdDr'] . "' and '" . $param['prdIdSmp'] . "'";
    $dert = "select TIMESTAMPDIFF(MONTH,'" . $param['prdIdDr'] . "-01','" . $param['prdIdSmp'] . "-01') as difdrt 
           from " . $dbname . ".log_transaksiht where kodept='" . $param['ptId'] . "'";
	$qert=$owlPDO->query($dert) or die(print " Gagal: ".PDOException::getMessage());
	$qert->setFetchMode(PDO::FETCH_ASSOC);
    $rdert = $qert->fetch();
    if (($rdert['difdrt'] >= 0) && ($rdert['difdrt'] <= 6)) {
        $whr.="and left(tanggal,7) between '" . $param['prdIdDr'] . "' and '" . $param['prdIdSmp'] . "'";
    } else {
        exit("error: Periode Salah atau lebih dari 6 bulan");
    }
}
if ($param['proses'] != 'getUnit') {
    #getnoakun dari kelompok barang
    $snoakun = "select distinct noakun,kode from " . $dbname . ".log_5klbarang 
			  where (noakun!='' and noakun is not null) order by kode asc";
	$qnoakun=$owlPDO->query($snoakun) or die(print " Gagal: ".PDOException::getMessage());
	$qnoakun->setFetchMode(PDO::FETCH_ASSOC);
    while ($rnoakun = $qnoakun->fetch()) {
        $lstNoakun[$rnoakun['kode']] = $rnoakun['noakun'];
    }
    #getnojurnal
    $sjurnal = "select distinct nojurnal,noreferensi from " . $dbname . ".keu_jurnalht where kodejurnal like 'INV%' " . $whrd . "";
	$qjurnal=$owlPDO->query($sjurnal) or die(print " Gagal: ".PDOException::getMessage());
	$qjurnal->setFetchMode(PDO::FETCH_ASSOC);
    while ($rjurnal = $qjurnal->fetch()) {
        $nojurnal[$rjurnal['noreferensi']] = $rjurnal['nojurnal'];
    }
    #get jmlh row per klmpk brg
    $sdt3 = "select left(kodebarang,3) as klmpk,count(notransaksi) as jmlh
           from " . $dbname . ".`log_transaksi_vw` 
           where `kodept`='" . $param['ptId'] . "' and post=1 " . $whr . "  and left(kodebarang,1) not in('8','9')
           group by left(kodebarang,3) order by kodebarang,kodegudang asc";
	$qdt3=$owlPDO->query($sdt3) or die(print " Gagal: ".PDOException::getMessage());
	while ($rertklm = $qdt3->fetch()) {
        $jmlhRow[$rertklm['klmpk']] = $rertklm['jmlh'];
    }
    $sdt3 = "select kodebarang as klmpk,count(notransaksi) as jmlh
           from " . $dbname . ".`log_transaksi_vw` 
           where `kodept`='" . $param['ptId'] . "' and post=1 " . $whr . "  and left(kodebarang,1) not in('8','9')
           group by kodebarang order by kodebarang,kodegudang asc";
    $qdt3=$owlPDO->query($sdt3) or die(print " Gagal: ".PDOException::getMessage());
	$qdt3->setFetchMode(PDO::FETCH_ASSOC);
	while ($rertklm = $qdt3->fetch()) {
        $jmlhRowBrg[$rertklm['klmpk']] = $rertklm['jmlh'];
    }
    $bgex = "";
    $brd = 0;
    if ($param['proses'] == 'excel') {
        $bgex = " bgcolor=#DEDEDE align=center";
        $brd = 1;
    }

    $tab = "<table cellpadding=5 cellspacing=1 border=" . $brd . " class=sortable>";
    $tab.="<thead><tr " . $bgex . ">";
    $tab.="<th>" . $_SESSION['lang']['kodebarang'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['namabarang'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['nojurnal'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['noreferensi'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['tipetransaksi'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['rp'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['jumlah'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['satuan'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['kodevhc'] . "</th>";
    $tab.="<th>" . $_SESSION['lang']['kodeblok'] . "</th></tr><tbody>";
    #get data dr log_transaksi
    $sdt = "select left(kodebarang,3) as klmpk,kodebarang,notransaksi,tipetransaksi,`hartot` as uang,jumlah,satuan,kodemesin,kodeblok,kodept
		  from " . $dbname . ".`log_transaksi_vw` 
		  where `kodept`='" . $param['ptId'] . "' and post=1 " . $whr . "  
		  and left(kodebarang,1) not in ('8','9')
		  order by kodebarang,kodegudang asc";
	$qdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
	$qdt->setFetchMode(PDO::FETCH_ASSOC);
	while ($rdt = $qdt->fetch()) {
        if (!isset($klmpkbrg) or $klmpkbrg != substr($rdt['kodebarang'], 0, 3)) {
            $klmpkbrg = substr($rdt['kodebarang'], 0, 3);
            $tab.="<tr class=rowcontent>";
            $tab.="<td>" . $klmpkbrg . "</td>";
            $tab.="<td>" . $optKlmpKbrg[$klmpkbrg] . "</td>";
            $tab.="<td>" . $lstNoakun[$klmpkbrg] . "</td>";
            $tab.="<td>" . $optNmakun[$lstNoakun[$klmpkbrg]] . "</td>";
            $tab.="<td colspan=6>&nbsp;</td>";
            $tab.="</tr>";
            $rowKlmpk = $jmlhRow[$klmpkbrg];
            setIt($subtRps[$klmpkbrg], 0);
            setIt($subtJmlhs[$klmpkbrg], 0);
            $subtRps[$klmpkbrg]+=$rdt['uang'];
            $subtJmlhs[$klmpkbrg]+=$rdt['jumlah'];
            $ad = 1;
        } else {
            setIt($subtRps[$klmpkbrg], 0);
            setIt($subtJmlhs[$klmpkbrg], 0);
            $subtRps[$klmpkbrg]+=$rdt['uang'];
            $subtJmlhs[$klmpkbrg]+=$rdt['jumlah'];
            $ad+=1;
        }
        setIt($nojurnal[$rdt['notransaksi']], '');
        setIt($nmTipe[$rdt['tipetransaksi']], '');
        $tab.="<tr class=rowcontent>";
        $tab.="<td>" . $rdt['kodebarang'] . "</td>";
        $tab.="<td>" . $optNmbarang[$rdt['kodebarang']] . "</td>";
        $tab.="<td>" . $nojurnal[$rdt['notransaksi']] . "</td>";
        $tab.="<td>" . $rdt['notransaksi'] . "</td>";
        $tab.="<td>" . $nmTipe[$rdt['tipetransaksi']] . "</td>";
        $tab.="<td align=right>" . number_format($rdt['uang'], 2) . "</td>";
        $tab.="<td align=right>" . number_format($rdt['jumlah'], 2) . "</td>";
        $tab.="<td>" . $rdt['satuan'] . "</td>";
        $tab.="<td>" . $rdt['kodemesin'] . "</td>";
        $tab.="<td>" . $rdt['kodeblok'] . "</td></tr>";
        if (!isset($kdbrg) or $kdbrg != $rdt['kodebarang']) {
            $aret = 1;
            $kdbrg = $rdt['kodebarang'];
            setIt($subtRp[$kdbrg], 0);
            setIt($subtJmlh[$kdbrg], 0);
            $subtRp[$kdbrg]+=$rdt['uang'];
            $subtJmlh[$kdbrg]+=$rdt['jumlah'];
            $rert = $jmlhRowBrg[$kdbrg];
        } else {
            $aret+=1;
            setIt($subtRp[$kdbrg], 0);
            setIt($subtJmlh[$kdbrg], 0);
            $subtRp[$kdbrg]+=$rdt['uang'];
            $subtJmlh[$kdbrg]+=$rdt['jumlah'];
        }
        if ($rert == $aret) {
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=5 align=right>" . $_SESSION['lang']['subtotal'] . " " . $optNmbarang[$kdbrg] . "</td>";
            $tab.="<td  align=right>" . number_format($subtRp[$kdbrg], 2) . "</td>";
            $tab.="<td  align=right>" . number_format($subtJmlh[$kdbrg], 2) . "</td>";
            $tab.="<td colspan=3>&nbsp;</td>";
            $tab.="</tr>";
        }
        if ($rowKlmpk == $ad) {
            $tab.="<tr bgcolor=orange>";
            $tab.="<td colspan=5 align=right>" . $_SESSION['lang']['subtotal'] . " " . $optKlmpKbrg[$klmpkbrg] . "</td>";
            $tab.="<td  align=right>" . number_format($subtRps[$klmpkbrg], 2) . "</td>";
            $tab.="<td  align=right>" . number_format($subtJmlhs[$klmpkbrg], 2) . "</td>";
            $tab.="<td colspan=3>&nbsp;</td>";
            $tab.="</tr>";
        }
    }
    $tab.="</tbody></table>";
}

switch ($param['proses']) {
    case'getUnit':
        $optUnit2 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $unitDetailAkses = getOrgDetail(2);
        $sunit = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi 
                where induk='" . $param['kdPt'] . "' and kodeorganisasi in (".$unitDetailAkses.") order by namaorganisasi asc";
        $qunit=$owlPDO->query($sunit) or die(print " Gagal: ".PDOException::getMessage());
		$qunit->setFetchMode(PDO::FETCH_ASSOC);
		while ($runit = $qunit->fetch()) {
            $optUnit2.="<option value='" . $runit['kodeorganisasi'] . "'>" . $runit['namaorganisasi'] . "</option>";
        }
        echo $optUnit2;
        break;
    case'preview':
        echo $tab;
        break;
    case'excel':
        $tab.="</table>Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $thisDate = date("YmdHms");
        //$nop_="Laporan_Pembelian";
        $nop_ = "laptransaksiGudang_" . $thisDate;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
        window.location='tempExcel/" . $nop_ . ".xls.gz';
        </script>";
        break;
}
?>
