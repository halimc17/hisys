<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$judul = checkPostGet('judul', '');
$afdId = checkPostGet('afdId', '');

$qwe = explode("-", $periode);
$tahun = $qwe[0];
$bulan = $qwe[1];
//exit("Error:".$periode."___".$tahun."___".$bulan);
$optNm = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optThn = makeOption($dbname, 'setup_blok', 'kodeorg,tahuntanam');
$optKeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optKegSat = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
if ($unit == '' || $periode == '') {
    exit("Error:Field required");
}
$addstr = "(";
for ($W = 1; $W <= intval($bulan); $W++) {
    if ($W < 10)
        $jack = "jjg0" . $W;
    else
        $jack = "jjg" . $W;
    if ($W < intval($bulan))
        $addstr.=$jack . "+";
    else
        $addstr.=$jack;
}
$addstr.=")";

$addstr3 = "(";
for ($W = 1; $W <= intval($bulan); $W++) {
    if ($W < 10)
        $jack = "rp0" . $W;
    else
        $jack = "rp" . $W;
    if ($W < intval($bulan))
        $addstr3.=$jack . "+";
    else
        $addstr3.=$jack;
}
$addstr3.=")";

$addstr2 = "(";
for ($W = 1; $W <= intval($bulan); $W++) {
    if ($W < 10)
        $jack = "kg0" . $W;
    else
        $jack = "kg" . $W;
    if ($W < intval($bulan))
        $addstr2.=$jack . "+";
    else
        $addstr2.=$jack;
}
$addstr2.=")";
$optBulan['01'] = $_SESSION['lang']['jan'];
$optBulan['02'] = $_SESSION['lang']['peb'];
$optBulan['03'] = $_SESSION['lang']['mar'];
$optBulan['04'] = $_SESSION['lang']['apr'];
$optBulan['05'] = $_SESSION['lang']['mei'];
$optBulan['06'] = $_SESSION['lang']['jun'];
$optBulan['07'] = $_SESSION['lang']['jul'];
$optBulan['08'] = $_SESSION['lang']['agt'];
$optBulan['09'] = $_SESSION['lang']['sep'];
$optBulan['10'] = $_SESSION['lang']['okt'];
$optBulan['11'] = $_SESSION['lang']['nov'];
$optBulan['12'] = $_SESSION['lang']['dec'];
$bg = "";
$brdr = 0;

#produksi #
$sProd = "select distinct * from " . $dbname . ".kebun_spb_bulanan_vw 
        where blok like '" . $unit . "%' and periode between '" . $tahun . "-01' and '" . $periode . "' 
        order by blok asc,periode desc";
if ($afdId != '') {
    $sProd = "select distinct * from " . $dbname . ".kebun_spb_bulanan_vw 
        where blok like '" . $afdId . "%' and periode between '" . $tahun . "-01' and '" . $periode . "' 
        order by blok asc,periode desc";
}


$qProd = $owlPDO->query($sProd) or die(print " Gagal: " . PDOException::getMessage());
$qProd->setFetchMode(PDO::FETCH_ASSOC);
while ($rProd = $qProd->fetch()) {
    if ($rProd['blok'] != '') {
        if ($periode == $rProd['periode']) {
            @$dtKgBi[$rProd['blok']]+=$rProd['nettotimbangan'];
        }
        @$dtKgSi[$rProd['blok']]+=$rProd['nettotimbangan'];
        $dtKdOrg[$rProd['blok']] = $rProd['blok'];
    }
}
$sJjg = "select distinct sum(hasilkerja) as jjg,left(tanggal,7) as periode,kodeorg from " . $dbname . ".kebun_prestasi_vw 
       where kodeorg like '" . $unit . "%' and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "' 
       group by kodeorg asc,left(tanggal,7) desc order by kodeorg asc";
if ($afdId != '') {
    $sJjg = "select distinct sum(hasilkerja) as jjg,left(tanggal,7) as periode,kodeorg from " . $dbname . ".kebun_prestasi_vw 
       where kodeorg like '" . $afdId . "%' and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "' 
       group by kodeorg asc,left(tanggal,7) desc order by kodeorg asc";
}

$qJjg = $owlPDO->query($sJjg) or die(print " Gagal: " . PDOException::getMessage());
$qJjg->setFetchMode(PDO::FETCH_ASSOC);
while ($rJjg = $qJjg->fetch()) {
    if ($rJjg['kodeorg'] != '') {
        if ($periode == $rJjg['periode']) {
            @$jjgpanen[$rJjg['kodeorg']]+=$rJjg['jjg'];
        }
        @$dtJjgSi[$rJjg['kodeorg']]+=$rJjg['jjg'];
        $dtKdOrg[$rJjg['kodeorg']] = $rJjg['kodeorg'];
    }
}

$sLuas = "select distinct luasareaproduktif,jumlahpokok,kodeorg from " . $dbname . ".kebun_interval_panen_vw where
        kodeorg like '" . $unit . "%' and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "' order by kodeorg asc";
if ($afdId != '') {
    $sLuas = "select distinct luasareaproduktif,jumlahpokok,kodeorg from " . $dbname . ".kebun_interval_panen_vw where
        kodeorg like '" . $afdId . "%' and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "' order by kodeorg asc";
}

$qLuas = $owlPDO->query($sLuas) or die(print " Gagal: " . PDOException::getMessage());
$qLuas->setFetchMode(PDO::FETCH_ASSOC);
while ($rLuas = $qLuas->fetch()) {
    $dtLuas[$rLuas['kodeorg']] = $rLuas['luasareaproduktif'];
    $dtPkk[$rLuas['kodeorg']] = $rLuas['jumlahpokok'];
    $dtKdOrg[$rLuas['kodeorg']] = $rLuas['kodeorg'];
}
$sProdBgt = "select distinct sum" . $addstr2 . " as kgbgt,kgsetahun,kodeblok from " . $dbname . ".bgt_produksi_kbn_kg_vw
           where tahunbudget='" . $tahun . "' and kodeblok like '" . $unit . "%' group by kodeblok order by kodeblok asc";
if ($afdId != '') {
    $sProdBgt = "select distinct sum" . $addstr2 . " as kgbgt,kgsetahun,kodeblok from " . $dbname . ".bgt_produksi_kbn_kg_vw
           where tahunbudget='" . $tahun . "' and kodeblok like '" . $afdId . "%' group by kodeblok order by kodeblok asc";
}

$qProdBgt = $owlPDO->query($sProdBgt) or die(print " Gagal: " . PDOException::getMessage());
$qProdBgt->setFetchMode(PDO::FETCH_ASSOC);
while ($rProdBgt = $qProdBgt->fetch()) {
    $dtKgBgt[$rProdBgt['kodeblok']] = $rProdBgt['kgbgt'];
    $dtKgThnnBgt[$rProdBgt['kodeblok']] = $rProdBgt['kgsetahun'];
    $dtKdOrg[$rProdBgt['kodeblok']] = $rProdBgt['kodeblok'];
}

$sJjg = "select distinct sum" . $addstr . " as jjg,kodeblok from " . $dbname . ".bgt_produksi_kebun
       where kodeblok like '" . $unit . "%' and tahunbudget='" . $tahun . "' group by kodeblok order by kodeblok asc";
if ($afdId != '') {
    $sJjg = "select distinct sum" . $addstr . " as jjg,kodeblok from " . $dbname . ".bgt_produksi_kebun
       where kodeblok like '" . $afdId . "%' and tahunbudget='" . $tahun . "' group by kodeblok order by kodeblok asc";
}

$qJjg = $owlPDO->query($sJjg) or die(print " Gagal: " . PDOException::getMessage());
$qJjg->setFetchMode(PDO::FETCH_ASSOC);
while ($rJjg = $qJjg->fetch()) {
    $dtBgtJjg[$rJjg['kodeblok']] = $rJjg['jjg'];
    $dtKdOrg[$rJjg['kodeblok']] = $rJjg['kodeblok'];
}
#produksi end#
//$bjrSen[$lsBlok]
#bjr sensus#
$strbjr = "select kodeorg,bjr,tahunproduksi from " . $dbname . ".kebun_5bjr
    where tahunproduksi = '" . $tahun . "' and kodeorg like '" . $unit . "%'  order by kodeorg asc";
if ($afdId != '') {
    $strbjr = "select kodeorg,bjr,tahunproduksi from " . $dbname . ".kebun_5bjr
        where tahunproduksi = '" . $tahun . "' and kodeorg like '" . $afdId . "%'  order by kodeorg asc";
}

$query = $owlPDO->query($strbjr) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $bjrSen[$res['kodeorg']] = $res['bjr'];
}

#rotasi#
// data panen sd bulan ini
$panen = "select kodeorg,tanggal,tahuntanam,luasareaproduktif as luas from " . $dbname . ".kebun_interval_panen_vw
    where (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $unit . "%'  order by kodeorg asc,tanggal asc";
if ($afdId != '') {
    $panen = "select kodeorg,tanggal,tahuntanam,luasareaproduktif as luas from " . $dbname . ".kebun_interval_panen_vw
    where (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $afdId . "%' order by kodeorg asc,tanggal asc";
}

$query = $owlPDO->query($panen) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {

    $kodeorgArr[$res['kodeorg']] = $res['kodeorg'];
    $tanggalsdArr[$res['tanggal']] = $res['tanggal'];
    $dzArr[$res['kodeorg']][$res['tanggal']] = 'P';

    //$dtLuas[$adt.$res['tahuntanam']]+=$res['luas'];
}

// susun rotasi
if (!empty($kodeorgArr))
    foreach ($kodeorgArr as $koko) {

        // sd bulan ini
        if (!empty($tanggalsdArr))
            foreach ($tanggalsdArr as $tata) {
                $kemarin = strtotime('-1 day', strtotime($tata));
                $kemarin = date('Y-m-d', $kemarin);
                $bln = substr($tata, 5, 2);
                $dzArr[$koko][$tata] = isset($dzArr[$koko][$tata]) ? $dzArr[$koko][$tata] : '';
                $dzArr[$koko][$kemarin] = isset($dzArr[$koko][$kemarin]) ? $dzArr[$koko][$kemarin] : '';
                if (($dzArr[$koko][$tata] == 'P')and ( $dzArr[$koko][$kemarin] != 'P')) {
                    @$dzRot[$koko]+=1;
                }
            }
    }
$sRotasiBudget = "select distinct rotasi,a.kodeorg,b.thntnm from " . $dbname . ".bgt_budget a left join 
                " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok
                where a.tahunbudget='" . $tahun . "' and a.kodeorg like '" . $unit . "%' and kegiatan=611010101";
if ($afdId != '') {
    $sRotasiBudget = "select distinct rotasi,a.kodeorg,b.thntnm from " . $dbname . ".bgt_budget a left join 
                   " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok
                   where a.tahunbudget='" . $tahun . "' and a.kodeorg like '" . $afdId . "%' and kegiatan=611010101";
}

$qRotasiBudget = $owlPDO->query($sRotasiBudget) or die(print " Gagal: " . PDOException::getMessage());
$qRotasiBudget->setFetchMode(PDO::FETCH_ASSOC);
while ($rRotasiBudget = $qRotasiBudget->fetch()) {
    @$dzRotB[$rRotasiBudget['kodeorg']]+=$rRotasiBudget['rotasi'] / 12;
    @$dzRotBgt[$rRotasiBudget['kodeorg']]+=$dzRotB[$rRotasiBudget['kodeorg']] * (intval($bulan));
}
#rotasi end#

$drt = count($dtKdOrg);
if ($drt == 0) {
    exit("Error:Data Kosong");
}
if ($proses == 'excel') {
    $bg = " bgcolor=#DEDEDE";
    $brdr = 1;
    $tab.="<table border=0>
         <tr>
            <td colspan=8 align=left><font size=3>DATA PANEN TAHUN " . $tahun . "</font></td>
            <td colspan=6 align=right>" . $_SESSION['lang']['bulan'] . " : " . $optBulan[$bulan] . " " . $tahun . "</td>
         </tr> 
         <tr><td colspan=14 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNm[$unit] . " (" . $unit . ")</td></tr>";
    if ($afdId != '') {
        $tab.="<tr><td colspan=14 align=left>" . $_SESSION['lang']['afdeling'] . " : " . $optNm[$afdId] . " (" . $afdId . ")</td></tr>";
    }
    $tab.="</table>";
}



$brdr0;
$bgcoloraja = "";
$preview = isset($preview) ? $preview : '';
if ($preview == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE";
    $brdr = 1;
}
$tab.=$judul;

$tab.="<table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>";
$tab.="<td rowspan=3  align=center " . $bgcoloraja . ">" . $_SESSION['lang']['afdeling'] . "</td>";
$tab.="<td rowspan=3  align=center " . $bgcoloraja . ">" . $_SESSION['lang']['blok'] . "</td>";
$tab.="<td rowspan=3  align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tahuntanam'] . "</td>";
$tab.="<td rowspan=3  align=center " . $bgcoloraja . ">" . $_SESSION['lang']['luas'] . "</td>";
$tab.="<td rowspan=3  align=center " . $bgcoloraja . ">" . $_SESSION['lang']['jumlahpokok'] . "</td>";
$tab.="<td colspan=4  align=center " . $bgcoloraja . ">PRODUKSI  KG TBS PABRIK</td>";
$tab.="<td align=center colspan=3 " . $bgcoloraja . ">PRODUKSI / HA</td>";
$tab.="<td colspan=3 align=center " . $bgcoloraja . ">JUMLAH JJG DIPANEN</td>";
$tab.="<td colspan=4  align=center " . $bgcoloraja . ">BERAT JANJANG RATA-RATA </td>";
$tab.="<td colspan=2  align=center " . $bgcoloraja . ">ROTASI</td></tr>";
$tab.="<tr><td rowspan=2  align=center " . $bgcoloraja . ">BI</td>";
$tab.="<td colspan=2  align=center " . $bgcoloraja . ">S/D B.INI</td>";
$tab.="<td rowspan=2  align=center " . $bgcoloraja . ">ANNUAL BUDGET TAHUNAN</td>";
$tab.="<td rowspan=2  align=center " . $bgcoloraja . ">BI</td>";
$tab.="<td colspan=2  align=center " . $bgcoloraja . ">S/D B.INI</td>";
$tab.="<td rowspan=2  align=center " . $bgcoloraja . ">BI</td>";
$tab.="<td colspan=2  align=center " . $bgcoloraja . ">S/D B.INI</td>";
$tab.="<td align=center colspan=2" . $bgcoloraja . ">" . $_SESSION['lang']['aktual'] . "</td>";
$tab.="<td align=center rowspan=2" . $bgcoloraja . ">Sensus</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">" . $_SESSION['lang']['budget'] . "</td>";
$tab.="<td colspan=2  align=center " . $bgcoloraja . ">S/D B.INI</td></tr>";
$tab.="<tr><td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['aktual'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['budget'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['aktual'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['budget'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['aktual'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['budget'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . ">BI</td>";
$tab.="<td align=center " . $bgcoloraja . ">S/D B.INI</td>";
//    $tab.="<td align=center ".$bgcoloraja."></td>";
//    $tab.="<td align=center ".$bgcoloraja."></td>";
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['aktual'] . "</td>";
$tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['budget'] . "</td></tr></thead><tbody>";


//sort array
array_multisort($dtKdOrg, SORT_ASC);

foreach ($dtKdOrg as $lsBlok) {
    $no+=1;
    $aerd = substr($lsBlok, 0, 6);
    $tab.="<tr class=rowcontent>";
    $tab.="<td>" . $aerd . "</td>";
    $tab.="<td>" . $lsBlok . "</td>";
    $tab.="<td align=right>" . $optThn[$lsBlok] . "</td>";
    $tab.="<td align=right>" . @number_format($dtLuas[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtPkk[$lsBlok], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtKgBi[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtKgSi[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtKgBgt[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtKgThnnBgt[$lsBlok], 2) . "</td>";
    @$kghabi[$lsBlok] = $dtKgBi[$lsBlok] / $dtLuas[$lsBlok];
    @$kgha[$lsBlok] = $dtKgSi[$lsBlok] / $dtLuas[$lsBlok];
    @$kghabgt[$lsBlok] = $dtKgBgt[$lsBlok] / $dtLuas[$lsBlok];
    $tab.="<td align=right>" . @number_format($kghabi[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($kgha[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($kghabgt[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($jjgpanen[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJjgSi[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtBgtJjg[$lsBlok], 2) . "</td>";
    @$bjrbi[$lsBlok] = $dtKgBi[$lsBlok] / $jjgpanen[$lsBlok];
    @$bjrRea[$lsBlok] = $dtKgSi[$lsBlok] / $dtJjgSi[$lsBlok];
    @$bjrBud[$lsBlok] = $dtKgBgt[$lsBlok] / $dtBgtJjg[$lsBlok];
    $tab.="<td align=right>" . @number_format($bjrbi[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($bjrRea[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($bjrSen[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($bjrBud[$lsBlok], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzRot[$lsBlok], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dzRotBgt[$lsBlok], 0) . "</td>";
    $tab.="</tr>";


    @$tdtLuas+=$dtLuas[$lsBlok];
    @$tdtPkk+=$dtPkk[$lsBlok];
    @$tdtKgBi+=$dtKgBi[$lsBlok];
    @$tdtKgSi+=$dtKgSi[$lsBlok];
    @$tdtKgBgt+=$dtKgBgt[$lsBlok];
    @$tdtKgThnnBgt+=$dtKgThnnBgt[$lsBlok];

    @$tkghabi+=$kghabi[$lsBlok];
    @$tkgha+=$kgha[$lsBlok];
    @$tkghabgt +=$kghabgt[$lsBlok];
    @$tjjgpanen+=$jjgpanen[$lsBlok];
    @$tdtJjgSi+=$dtJjgSi[$lsBlok];
    @$tdtBgtJjg+=$dtBgtJjg[$lsBlok];

    @$tbjrSen+=$bjrSen[$lsBlok];
    @$tbjrBud+=$bjrBud[$lsBlok];
}

$tab.="<tr class=rowcontent>";

$tab.="<td colspan=3 align=center>" . $_SESSION['lang']['total'] . "</td>";
$tab.="<td align=right>" . @number_format($tdtLuas, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tdtPkk) . "</td>";
$tab.="<td align=right>" . @number_format($tdtKgBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tdtKgSi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tdtKgBgt, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tdtKgThnnBgt, 2) . "</td>";


$tab.="<td align=right>" . @number_format($tkghabi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tkgha, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tkghabgt, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tjjgpanen, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tdtJjgSi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tdtBgtJjg, 2) . "</td>";


//bjr
@$tbjrbi = $tdtKgBi / $tjjgpanen;
@$tbjrRea = $tdtKgSi / $tdtJjgSi;


@$avgbjrSen = $tbjrSen / $no;
@$avgbjrBud = $tbjrBud / $no;

$tab.="<td align=right>" . @number_format($tbjrbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tbjrRea, 2) . "</td>";
$tab.="<td align=right>" . @number_format($avgbjrSen, 2) . "</td>";
$tab.="<td align=right>" . @number_format($avgbjrBud, 2) . "</td>";
$tab.="<td align=right colspan=2></td>";
//  $tab.="<td align=right>".@number_format(,2)."</td>";
//  $tab.="<td align=right>".@number_format(,2)."</td>";



$tab.="</tr>";

$tab.="</tbody></table>";
//exit("Error:".$tab);
switch ($proses) {
    case'preview':
        if ($unit == '' || $periode == '') {
            exit("Error:Field Tidak Boleh Kosong");
        }
        echo $tab;
        break;

    case'excel':
        if ($unit == '' || $periode == '') {
            exit("Error:Field Tidak Boleh Kosong");
        }

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHis");
        $nop_ = "lbm_produksiperblok_" . $unit . $periode;
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
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