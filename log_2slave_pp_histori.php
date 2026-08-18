<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if (isset($_POST['proses'])) {
    $proses = $_POST['proses'];
} else {
    $proses = $_GET['proses'];
}
if ($_SESSION['language'] == 'EN') {
    $zz = 'kelompok1 as kelompok';
} else {
    $zz = 'kelompok';
}

function putertanggal($tanggal) {
    $qwe = explode("-", $tanggal);
    if ($tanggal == '')
        $asd = '';
    else
        $asd = $qwe[2] . "-" . $qwe[1] . "-" . $qwe[0];
    return $asd;
}

$sKlmpk = "select kode," . $zz . " from " . $dbname . ".log_5klbarang order by kode";
$qKlmpk=$owlPDO->query($sKlmpk) or die(print " Gagal: ".PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);
$rKelompok = array();
while ($rKlmpk = $qKlmpk->fetch()) {
    $rKelompok[$rKlmpk['kode']] = $rKlmpk['kelompok'];
}

$klmpkBrg = empty($_POST['klmpkBrg']) ? (isset($_GET['klmpkBrg']) ? $_GET['klmpkBrg'] : '') : $_POST['klmpkBrg'];
$kdUnit = empty($_POST['kdUnit']) ? (isset($_GET['kdUnit']) ? $_GET['kdUnit'] : '') : $_POST['kdUnit'];
$lokasi = empty($_POST['lokasi']) ? (isset($_GET['lokasi']) ? $_GET['lokasi'] : '') : $_POST['lokasi'];
$periode = empty($_POST['periode']) ? (isset($_GET['periode']) ? $_GET['periode'] : '') : putertanggal($_POST['periode']);
$periode2 = empty($_POST['periode2']) ? (isset($_GET['periode2']) ? $_GET['periode2'] : '') : putertanggal($_POST['periode2']);
$statId = empty($_POST['statId']) ? (isset($_GET['statId']) ? $_GET['statId'] : '') : $_POST['statId'];
$purId = empty($_POST['purId']) ? (isset($_GET['purId']) ? $_GET['purId'] : '') : $_POST['purId'];
$nmbarang = empty($_POST['nmbarang']) ? (isset($_GET['nmbarang']) ? $_GET['nmbarang'] : '') : $_POST['nmbarang'];
$keterangan = empty($_POST['keterangan']) ? (isset($_GET['keterangan']) ? $_GET['keterangan'] : '') : $_POST['keterangan'];


//get data kod budget barang
$data = array();
$unitId = $_SESSION['lang']['all'];
$nmPrshn = "Holding";
$purchaser = $_SESSION['lang']['all'];
if ($periode != '') {
    $where = " a.tanggal between '" . $periode . "' and '" . $periode2 . "' ";
    $whereb = " and b.tanggal between '" . $periode . "' and '" . $periode2 . "' ";
} else {
    exit("Error: " . $_SESSION['lang']['periode'] . " tidak boleh kosong" . $periode);
}

if ($statId == '4') {
    exit("Error: " . $_SESSION['lang']['status'] . " tidak boleh kosong");
}

$optNmOrang = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optSatuan = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optFranco = makeOption($dbname, 'setup_franco', 'id_franco,franco_name');
$where2 = "tipetransaksi=1 and substr(tanggal,1,7)>='" . $periode . "'";
$arrTanggal = makeOption($dbname, 'log_transaksiht', 'nopo,tanggal', $where2);
$arrTanggal = makeOption($dbname, 'log_transaksiht', 'nopo,notransaksi', $where2);

if ($kdUnit != '') {
    $where.=" and a.kodeorg='" . $kdUnit . "'";
    $whereb.=" and b.kodeorg='" . $kdUnit . "'";
    $unitId = isset($optNmOrg[$kdUnit]) ? $optNmOrg[$kdUnit] : '';
}
if ($klmpkBrg != "") {
    $where.=" and substr(a.kodebarang,1,3)='" . $klmpkBrg . "'";
    $whereb.=" and substr(a.kodebarang,1,3)='" . $klmpkBrg . "'";
}
if ($lokasi != "") {
    $where.=" and a.lokalpusat='" . $lokasi . "'";
    $whereb.=" and a.lokalpusat='" . $lokasi . "'";
}
if ($purId != '') {
    $where.=" and c.purchaser='" . $purId . "'";
    $whereb.=" and a.purchaser='" . $purId . "'";
    $purchaser = $optNmOrang[$purId];
}
$brdr = 0;
$bgcoloraja = '';

if ($proses == 'excel') {
    //exit("error:".$arrPilMode[$pilMode]."__".$pilMode);
    $bgcoloraja = "bgcolor=#DEDEDE align=center";
    $brdr = 1;
    $tab = "
    <table>
    <tr><td colspan=17 align=left><b><font size=5>" . $_SESSION['lang']['ppLap'] . "</font></b></td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['pt'] . " : " . $unitId . "</td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['periode'] . " : " . $periode . "</td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['kelompokbarang'] . " : " . (isset($rKelompok[$klmpkBrg]) ? $rKelompok[$klmpkBrg] : '') . "</td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['purchaser'] . " : " . $purchaser . "</td></tr>
    </table>";
}
if ($statId == '3') {
     $sListData = "select distinct a.nopp,namabarang,a.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
            c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang,c.kurs, e.keterangan, f.notransaksi 
           from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
               left join " . $dbname . ".log_poht c on a.nopo=c.nopo
               left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
               left join " . $dbname . ".log_transaksiht f on a.nopo=f.nopo
           where " . $where . " and a.nopo in (select nopo from log_transaksiht)
           and namabarang like '%".$nmbarang."%'
           and e.keterangan like '%".$keterangan."%'
            group by a.kodebarang,a.nopo,a.nopp order by substr(a.kodebarang,1,3) asc";
}
elseif ($statId == '2') {
    $sListData = "select distinct a.nopp,namabarang,a.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
            c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang,c.kurs, e.keterangan, f.noinvoice 
           from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
               left join " . $dbname . ".log_poht c on a.nopo=c.nopo
               left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
               left join " . $dbname . ".keu_tagihanht f on a.nopo=f.nopo 
           where " . $where . " and f.noinvoice in (select keterangan1 from keu_kasbankdt)
           and namabarang like '%".$nmbarang."%' 
            and e.keterangan like '%".$keterangan."%'
            group by a.kodebarang,a.nopo,a.nopp order by substr(a.kodebarang,1,3) asc";
}
elseif ($statId == '1') {
    $sListData = "select distinct a.nopp,namabarang,a.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
            c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang,c.kurs, e.keterangan 
           from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
               left join " . $dbname . ".log_poht c on a.nopo=c.nopo
               left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
           where " . $where . " and namabarang like '%".$nmbarang."%'
            and e.keterangan like '%".$keterangan."%' 
           group by a.kodebarang,a.nopo,a.nopp order by substr(a.kodebarang,1,3) asc";
} else {
   $sListData = "select distinct a.nopp,kodebarang,purchaser,b.tanggal as tglpp,tglAlokasi,a.realisasi,a.keterangan from " . $dbname . ".log_prapodt a 
                left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp where (create_po=0 or create_po='') " . $whereb . " and a.status!='3' 
                and a.purchaser='0000000000'
                group by a.kodebarang,nopp  order by kodebarang asc";
 
}
$tab = "<table cellspacing=1 border=" . $brdr . " class=sortable>
        <thead class=rowheader>
        <tr>
        <td align=center " . $bgcoloraja . " rowspan=2>No.</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopp'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PP</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['keterangan'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['purchaser'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['alokasi'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>O.std</td>        
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jmlhDiminta'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['realisasi'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kurs'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['totalharga'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tandatangan'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . " colspan=5 align=center>" . $_SESSION['lang']['pembayaran'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . " colspan=2 align=center>" . $_SESSION['lang']['pengiriman'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . " colspan=2 align=center>" . $_SESSION['lang']['bapb'] . "</td>";
$tab.="</tr>";
// $tab.="<tr><td ".$bgcoloraja.">".$_SESSION['lang']['tipe']."</td>";//manual
//$tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['syaratPem']."</td>";//manual
// $tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['rencana']."</td>";//manual
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</td>"; //tagihan
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>"; //tagihan
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['kasbank'] . "</td>"; //manual
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</td>"; //manual
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['jumlah'] . "</td>"; //manual kasbank
//pengiriman
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['almt_kirim'] . "</td>"; //dari franco tgl kirim di po
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tgl_kirim'] . "</td>"; //dari tgl kirim di po
// $tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['tglsmp']."</td>";//manual
// $tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['satuan']."</td>";//manual
//  $tab.="<td ".$bgcoloraja.">".$_SESSION['lang']['biaya']."</td>";//manual
//bapb
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</td>";
$tab.="<td align=center  " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
//$tab.="<td  ".$bgcoloraja.">Copy</td>";//manual
// $tab.="<td  ".$bgcoloraja.">Original</td>";//manual

$tab.="</tr></thead>
        <tbody>";

$qListData=$owlPDO->query($sListData) or die(print " Gagal: ".PDOException::getMessage());
$qListData->setFetchMode(PDO::FETCH_ASSOC);
$rAdaData=$qListData->rowCount(); //disini error
if ($rAdaData > 0) {
    while ($rListData = $qListData->fetch()) {
        $tglTerima = '';
        $tglEdit = '';
        $tanggalByr = '';
        if (!isset($klmpkBarang) or $klmpkBarang != substr($rListData['kodebarang'], 0, 3)) {
            $brs = 1;
        }
        if ($brs == 1) {
            $no = 0;
            $klmpkBarang = substr($rListData['kodebarang'], 0, 3);
            $tab.="<tr class='rowcontent'>";
            $tab.="<td colspan=3><b>" . $klmpkBarang . "</b></td><td colspan=25><b>" . $rKelompok[$klmpkBarang] . "</b></td>";
            // $tab.="<td colspan=21>&nbsp;</td>";
            $tab.="</tr>";
            $brs = 0;
        }
        $sRealisasi = "select distinct realisasi from " . $dbname . ".log_prapodt where nopp='" . $rListData['nopp'] . "' and kodebarang='" . $rListData['kodebarang'] . "'";
        
        $qRealisai=$owlPDO->query($sRealisasi) or die(print " Gagal: ".PDOException::getMessage());
        $qRealisai->setFetchMode(PDO::FETCH_ASSOC);
        $rRealisasi = $qRealisai->fetch();
        $tanggalData = '';
        if ($statId == '1' or $statId=='2' or $statId='3') {
            if (!empty($rListData['nopo'])) {

                $sTagihan = "select distinct noinvoice,tanggal from " . $dbname . ".keu_tagihanht where nopo='" . $rListData['nopo'] . "'";
                $qTagihan=$owlPDO->query($sTagihan) or die(print " Gagal: ".PDOException::getMessage());
                $qTagihan->setFetchMode(PDO::FETCH_ASSOC);
                $rTagihan = $qTagihan->fetch();
                $tglTerima = isset($rTagihan['tglterima']) ? $rTagihan['tglterima'] : '';
                $bayarnya = '0';
                $nokasbank = '';


                if ($rTagihan['noinvoice'] != '') {
                    $tanggalData = $rTagihan['tanggal'];
                    $tglByr = "select distinct notransaksi from " . $dbname . ".keu_kasbankdt 
                                                         where keterangan1  ='" . $rTagihan['noinvoice'] . "'";
                    $qtglByr=$owlPDO->query($tglByr) or die(print " Gagal: ".PDOException::getMessage());
                    $qtglByr->setFetchMode(PDO::FETCH_ASSOC);
                    $rTglByr = $qtglByr->fetch();
                    //$tanggalByr="00-00-0000";

                    if (!empty($rTglByr['notransaksi'])) {
                        //$rt="select distinct tanggal,jumlah from ".$dbname.".keu_kasbankht where notransaksi='".$rTglByr['notransaksi']."'";
                        $rt = "select tanggal,jumlah,notransaksi from " . $dbname . ".keu_kasbankht where notransaksi='" . $rTglByr['notransaksi'] . "'";
                        $qrt=$owlPDO->query($rt) or die(print " Gagal: ".PDOException::getMessage());
                        $qrt->setFetchMode(PDO::FETCH_ASSOC);
                        $rrt = $qrt->fetch();
                        $bayarnya = 0;
                        if ($rrt['tanggal'] != '' || $rrt['tanggal'] != '0000-00-00' || $rrt['tanggal'] != '00-00-0000') {
                            $tanggalByr = $rrt['tanggal'];
                            $bayarnya = $rrt['jumlah'];
                            $nokasbank = $rrt['notransaksi'];
                        }
                        if ($bayarnya == '') {
                            $bayarnya = 0;
                        }
                    }
                }

                $sTransaksi = "select distinct tanggal,notransaksi from " . $dbname . ".log_transaksiht where nopo='" . $rListData['nopo'] . "'";
                $qTransaksi=$owlPDO->query($sTransaksi) or die(print " Gagal: ".PDOException::getMessage());
                $qTransaksi->setFetchMode(PDO::FETCH_ASSOC);
                $rTransaksi = $qTransaksi->fetch();
                $tglTerima = tanggalnormal($rTransaksi['tanggal']);
            }
        }
        if (!empty($rListData['idFranco'])) {
            $lokasi = $optFranco[$rListData['idFranco']];
            $tglKirim = substr($rListData['tanggalkirim'], 0, 10);
        } else {
            $lokasi = isset($rListData['lokasipengiriman']) ? $rListData['lokasipengiriman'] : '';
            $tglKirim = isset($rListData['tanggalkirim']) ? substr($rListData['tanggalkirim'], 0, 10) : '';
        }

        if (!empty($rListData['tgledit'])) {
            $tglEdit = $rListData['tgledit'];
        }
        if (strlen($tglKirim) < 10) {
            $tglKirim = '';
        }
        if (strlen($tglTerima) < 10) {
            $tglTerima = '';
        }
        $no+=1;
        $hargaBarang = 0;
        if (!empty($rListData['jumlahpesan'])) {
            if ($rListData['matauang'] != 'IDR') {
                $hargaBarang = $rListData['jumlahpesan'] * ($rListData['hargasatuan'] * $rListData['kurs']);
            } else {
                $hargaBarang = $rListData['jumlahpesan'] * $rListData['hargasatuan'];
            }
        }

        $month1 = substr($rListData['tglAlokasi'], 5, 2);
        $date1 = substr($rListData['tglAlokasi'], 8, 2);
        $year1 = substr($rListData['tglAlokasi'], 0, 4);
        
        $mydate1 = date('Y-m-d', strtotime($rListData['tglAlokasi']));
        
        if ($statId == 0) {
            $mydate2 = date('Y-m-d');
            $month2 = date("m");
            $date2 = date("d");
            $year2 = date("Y");
        } elseif ($statId == 1) {
            $mydate2 = date('Y-m-d', strtotime($rListData['tanggal']));
            $month2 = substr($rListData['tanggal'], 5, 2);
            $date2 = substr($rListData['tanggal'], 8, 2);
            $year2 = substr($rListData['tanggal'], 0, 4);
        }
        
        $jmlHari = (strtotime($mydate2) - strtotime($mydate1)) / (60 * 60 * 24);

        if ($year1=='0000' || $rListData['tglAlokasi']=='--') {
            $jmlHari=0;
        }

        

        // $days2 = round($days);
        
        // $jd1 = gregoriantojd($month1, $date1, $year1);
        // $jd2 = gregoriantojd($month2, $date2, $year2);
        // $jmlHari = $jd2 - $jd1;
        $tab.="<tr class='rowcontent'>";
        $tab.="<td>" . $no . "</td>";
        $tab.="<td>" . $rListData['nopp'] . "</td>";
        $tab.="<td>" . tanggalnormal($rListData['tglpp']) . "</td>";
        $tab.="<td>" . $rListData['kodebarang'] . "</td>";
        $tab.="<td>" . $optNmBarang[$rListData['kodebarang']] . "</td>";
        $tab.="<td>" . $optSatuan[$rListData['kodebarang']] . "</td>";

        $tab.="<td>" . $rListData['keterangan'] . "</td>";

        $tab.="<td>" . (!empty($rListData['nopo']) ? $rListData['nopo'] : '') . "</td>";
        $tab.="<td>" . (isset($rListData['tanggal']) ? $rListData['tanggal'] : '') . "</td>";
        $tab.="<td>" . $optNmOrang[$rListData['purchaser']] . "</td>";
        $tab.="<td>" . tanggalnormal($rListData['tglAlokasi']) . "</td>";
        $tab.="<td align=right>" . $jmlHari . "</td>";
        $tab.="<td align=right>" . number_format($rRealisasi['realisasi'], 0) . "</td>";
        $tab.="<td align=right>" . number_format(isset($rListData['jumlahpesan']) ? $rListData['jumlahpesan'] : 0, 0) . "</td>";
        $tab.="<td>" . (isset($rListData['matauang']) ? $rListData['matauang'] : '') . "</td>";
        $tab.="<td align=right>" . (isset($rListData['matauang']) ? $rListData['kurs'] : '') . "</td>";
        $tab.="<td align=right>" . number_format($hargaBarang, 0) . "</td>";
        $tab.="<td>" . (isset($rListData['namasupplier']) ? $rListData['namasupplier'] : '') . "</td>";
        $tab.="<td>" . $tglEdit . "</td>";
        //$tab.="<td>&nbsp;</td>";
        //$tab.="<td>&nbsp;</td>";
        //$tab.="<td>&nbsp;</td>";
        $tab.="<td>" . (isset($rTagihan['noinvoice']) ? $rTagihan['noinvoice'] : '') . "</td>";
        $tab.="<td>" . $tanggalData . "</td>";
        $tab.="<td>" . (isset($nokasbank) ? $nokasbank : '') . "</td>";
        $tab.="<td>" . $tanggalByr . "</td>";

        $tab.="<td>" . number_format(floatval(isset($bayarnya) ? $bayarnya : ''), 2) . "</td>";
        $tab.="<td>" . $lokasi . "</td>";
        $tab.="<td>" . $tglKirim . "</td>";
        //$tab.="<td>&nbsp;</td>";
        //$tab.="<td>&nbsp;</td>";
        //$tab.="<td>&nbsp;</td>";
        $tab.="<td>" . (isset($rTransaksi['notransaksi']) ? $rTransaksi['notransaksi'] : '') . "</td>";
        $tab.="<td>" . $tglTerima . "</td>";
        //$tab.="<td>&nbsp;</td>";
        //$tab.="<td>&nbsp;</td>";
        $tab.="</tr>";
    }
} else {
    $tab.="<tr class=rowcontent><td colspan=27>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
}


$tab.="</tbody></table>";
switch ($proses) {
    case'getKdorg':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sOrg = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $kdPt . "'";
        $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['namaorganisasi'] . "</option>";
        }
        echo $optorg;
        break;
    case'preview':
        echo $tab;
        break;

    case'excel':

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHms");
        $nop_ = "permintaanPembeliaan_" . $purId . "_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";

        break;
    case'getTgl':
        if ($periode != '') {
            $tgl = $periode;
            $tanggal = $tgl[0] . "-" . $tgl[1];
        } elseif ($period != '') {
            $tgl = $period;
            $tanggal = $tgl[0] . "-" . $tgl[1];
        }
        if ($kdUnit == '') {
            $kdUnit = $_SESSION['lang']['lokasitugas'];
        }
        $sTgl = "select distinct tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji where kodeorg='" . substr($kdUnit, 0, 4) . "' and periode='" . $tanggal . "' ";
        $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
        $qTgl->setFetchMode(PDO::FETCH_ASSOC);
        $rTgl = $qTgl->fetch();
        echo tanggalnormal($rTgl['tanggalmulai']) . "###" . tanggalnormal($rTgl['tanggalsampai']);
        break;

    case'pdf':
        $kdPt = $_GET['kdPt'];
        //$arr="##kdPt##kdSup##kdUnit##tglDr##tglSmp";
        $kdSup = $_GET['kdSup'];
        $kdUnit = $_GET['kdUnit'];
        $tglDari = tanggalsystem($_GET['tglDr']);
        $tanggalSampai = tanggalsystem($_GET['tanggalSampai']);
        $lokBeli = $_GET['lokBeli'];
        //echo $tglDari."__".$tanggalSampai;exit();
        if (($tglDari == '') || ($tanggalSampai == '')) {
            echo"warning:Tanggal Dari dan Sampai Tanggal Tidak Boleh Kosong";
            exit();
        } else {
            if ($kdPt != '') {
                $where.=" and a.kodeorg='" . $kdPt . "'";
            }
            if ($kdUnit != '') {
                $where.=" and substring(b.nopp,16,4)='" . $kdUnit . "'";
            }
            if ($kdSup != "") {
                $where.=" and a.kodesupplier='" . $kdSup . "'";
            }
            if (($tglDr != '') || ($tanggalSampai != '')) {
                $where.=" and (a.tanggal between '" . $tglDari . "' and '" . tanggalsystem($_GET['tanggalSampai']) . "')";
            }
            if ($lokBeli != '') {
                $where.=" and lokalpusat='" . $lokBeli . "'";
            }
        }

        class PDF extends FPDF {

            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $kdPt;
                global $kdSup;
                global $kdUnit;
                global $tglDari;
                global $tanggalSampai;
                global $where;
                global $isi;
                global $owlPDO;

                $isi = array();
                if ($kdPt == "") {
                    $pt = 'MHO';
                } else {
                    $pt = $kdPt;
                }
                # Alamat & No Telp
                /*         $query = selectQuery($dbname,'organisasi','namaorganisasi,alamat,telepon',
                  "kodeorganisasi='".$kdPt."'");
                  $orgData = fetchData($query); */
                $sAlmat = "select namaorganisasi,alamat,telepon from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
                $qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
                $qAlamat->setFetchMode(PDO::FETCH_ASSOC);
                $rAlamat = $qAlamat->fetch();

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path = 'images/logo.jpg';
                $this->Image($path, $this->lMargin, $this->tMargin, 0, 55);
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(255, 255, 255);
                $this->SetX(100);
                $this->Cell($width - 100, $height, $rAlamat['namaorganisasi'], 0, 1, 'L');
                $this->SetX(100);
                $this->Cell($width - 100, $height, $rAlamat['alamat'], 0, 1, 'L');
                $this->SetX(100);
                $this->Cell($width - 100, $height, "Tel: " . $rAlamat['telepon'], 0, 1, 'L');
                $this->Line($this->lMargin, $this->tMargin + ($height * 4), $this->lMargin + $width, $this->tMargin + ($height * 4));
                $this->Ln();
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 11);
                $this->Cell($width, $height, $_SESSION['lang']['detPemb'], 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell($width, $height, "Periode : " . $_GET['tglDr'] . " s.d. " . $_GET['tanggalSampai'], 0, 1, 'C');
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 7);
                $this->SetFillColor(220, 220, 220);


                $this->Cell(3 / 100 * $width, $height, 'No', 1, 0, 'C', 1);
                $this->Cell(15 / 100 * $width, $height, $_SESSION['lang']['supplier'], 1, 0, 'C', 1);
                $this->Cell(12 / 100 * $width, $height, $_SESSION['lang']['nopo'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['tanggal'], 1, 0, 'C', 1);
                $this->Cell(22 / 100 * $width, $height, $_SESSION['lang']['namabarang'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['matauang'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
                $this->Cell(10 / 100 * $width, $height, $_SESSION['lang']['total'], 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['tanggal'] . " PP", 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['tanggal'] . " BAPB", 1, 1, 'C', 1);
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }

        }

        $pdf = new PDF('L', 'pt', 'A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 9;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 7);
        $sData = "select a.kodesupplier from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where a.statuspo>1 " . $where . " group by kodesupplier order by a.tanggal asc";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        while ($rData = $qData->fetch()) {
            $isi[] = $rData;
        }
        $totalAll = array();
        foreach ($isi as $test => $dt) {
            $no+=1;

            $i = 0;
            $afdC = false;
            $sNm = "select namasupplier from " . $dbname . ".log_5supplier where supplierid='" . $dt['kodesupplier'] . "'";
            $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
            $qNm->setFetchMode(PDO::FETCH_ASSOC);
            $rNm = $qNm->fetch();
            if ($afdC == false) {
                $pdf->Cell(3 / 100 * $width, $height, $no, 'TLR', 0, 'C', 1);
                $pdf->Cell(15 / 100 * $width, $height, $rNm['namasupplier'], 'TLR', 0, 'C', 1);
            }

            $sList = "select distinct a.tanggal,a.matauang,b.kodebarang,b.satuan,b.nopo,b.jumlahpesan,b.nopp,b.hargasatuan from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where a.kodesupplier='" . $dt['kodesupplier'] . "' and b.nopo!='NULL' and a.tanggal between '" . $tglDari . "' and '" . $tanggalSampai . "'";
            $qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
            $qList->setFetchMode(PDO::FETCH_ASSOC);
            $grandTot = array();

            while ($rList = $qList->fetch()) {
                $limit++;
                $sBrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $rList['kodebarang'] . "'";
                $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                $rBrg = $qBrg->fetch();
                if ($rList['matauang'] != 'IDR') {
                    $sKurs = "select kurs from " . $dbname . ".setup_matauangrate where kode='" . $rList['matauang'] . "' and daritanggal='" . $rList['tanggal'] . "'";
                    $qKurs=$owlPDO->query($sKurs) or die(print " Gagal: ".PDOException::getMessage());
                    $qKurs->setFetchMode(PDO::FETCH_ASSOC);
                    $rKurs = $qKurs->fetch();
                    if ($rKurs != '') {
                        $hrg = $rKurs['kurs'] * $rList['hargasatuan'];
                        $totHrg = $rList['jumlahpesan'] * $hrg;
                    } else {
                        if ($rList['matauang'] == 'USD') {
                            $hrg = $rList['hargasatuan'] * 8850;
                            $totHrg = $rList['jumlahpesan'] * $hrg;
                            $rList['matauang'] = "IDR";
                        } elseif ($rList['matauang'] == 'EUR') {
                            $hrg = $rList['hargasatuan'] * 12643;
                            $totHrg = $rList['jumlahpesan'] * $hrg;
                            $rList['matauang'] = "IDR";
                        } elseif (($rList['matauang'] == '') || ($rList['matauang'] == 'NULL')) {
                            $totHrg = $rList['jumlahpesan'] * $rList['hargasatuan'];
                        }
                    }
                } else {
                    $totHrg = $rList['jumlahpesan'] * $rList['hargasatuan'];
                }
                //$totHrg=$rList['jumlahpesan']*$rList['hargasatuan'];
                $grandTot['total']+=$totHrg;
                if ($rList['nopp'] != "") {
                    $sTgl = "select tanggal from " . $dbname . ".log_prapoht where nopp='" . $rList['nopp'] . "'";
                    $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
                    $qTgl->setFetchMode(PDO::FETCH_ASSOC);
                    $rTgl = $qTgl->fetch();

                    if (($rTgl['tanggal'] != "") || ($rTgl['tanggal'] != "000-00-00")) {
                        $tglPP = tanggalnormal($rTgl['tanggal']);
                    } else {
                        $tglPP = "";
                    }
                } else {
                    $tglPP = "";
                }
                if ($rList['nopo'] != "") {
                    $sTgl2 = "select tanggal from " . $dbname . ".log_transaksiht where nopo='" . $rList['nopo'] . "' and tipetransaksi=1";
                    $qTgl2=$owlPDO->query($sTgl2) or die(print " Gagal: ".PDOException::getMessage());
                    $qTgl2->setFetchMode(PDO::FETCH_ASSOC);
                    $rTgl2 = $qTgl2->fetch();
                    if ($rTgl2['tanggal'] != "") {
                        $tglBapb = tanggalnormal($rTgl2['tanggal']);
                    } else {
                        $tglBapb = "";
                    }
                } else {
                    $tglBapb = "";
                }
                if ($afdC == true) {
                    $i = 0;
                    $pdf->Cell(3 / 100 * $width, $height, '', 'LR', $align[$i], 1);
                    $pdf->Cell(15 / 100 * $width, $height, '', 'LR', $align[$i], 1);
                    //$pdf->Cell($length[$i]/100*$width,$height,'','LR',$align[$i],1);
                    $i++;
                } else {
                    $afdC = true;
                }
                $pdf->Cell(12 / 100 * $width, $height, $rList['nopo'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, tanggalnormal($rList['tanggal']), 1, 0, 'C', 1);
                $pdf->Cell(22 / 100 * $width, $height, $rBrg['namabarang'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['matauang'], 1, 0, 'C', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['jumlahpesan'], 1, 0, 'R', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['satuan'], 1, 0, 'C', 1);
                $pdf->Cell(10 / 100 * $width, $height, number_format($totHrg, 2), 1, 0, 'R', 1);
                $pdf->Cell(7 / 100 * $width, $height, $tglPP, 1, 0, 'C', 1);
                $pdf->Cell(7 / 100 * $width, $height, $tglBapb, 1, 1, 'C', 1);
                //if($limit==46)                
//          {   
//              $limit=0;
//              $pdf->AddPage();
//          }
            }
            $totalAll['totalSemua']+=$grandTot['total'];
            $pdf->Cell(76 / 100 * $width, $height, "Sub Total", 1, 0, 'C', 1);
            $pdf->Cell(10 / 100 * $width, $height, number_format($grandTot['total'], 2), 1, 0, 'R', 1);
            $pdf->Cell(14 / 100 * $width, $height, '', 1, 1, 'R', 1);
        }
        $pdf->Cell(76 / 100 * $width, $height, "Total", 1, 0, 'C', 1);
        $pdf->Cell(10 / 100 * $width, $height, number_format($totalAll['totalSemua'], 2), 1, 0, 'R', 1);
        $pdf->Cell(14 / 100 * $width, $height, '', 1, 1, 'R', 1);
        $pdf->Cell($width, $height, terbilang($totalAll['totalSemua'], 2), 1, 1, 'C', 1);


        $pdf->Output();
        break;


    default:
        break;
}
?>