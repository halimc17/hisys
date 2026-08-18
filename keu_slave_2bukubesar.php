<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$pt = checkPostGet('pt', '');
$gudang = checkPostGet('gudang', '');
$akundari = checkPostGet('akundari', '');
$akunsampai = checkPostGet('akunsampai', '');
$periode = checkPostGet('periode', '');
$periode1 = checkPostGet('periode1', '');
$revisi = checkPostGet('revisi', '');
$regional = checkPostGet('regional', '');
$tampilanId = checkPostGet('tampilanId', '');
$tipelaporan = checkPostGet('tipelaporan', '');


$stream = "";

//cek periode dan periode1
if ($periode1 < $periode) {  #ditukar
    $z = $periode;
    $periode = $periode1;
    $periode1 = $z;
}
$where = '';
if ($akundari != '' and $akunsampai != '') {
    $where .= " and noakun between '" . $akundari . "' and  '" . $akunsampai . "'";
}

$whereakun = '';
if ($akundari != '' and $akunsampai != '') {
    $whereakun .= " and noakun between '" . $akundari . "' and  '" . $akunsampai . "'";
}

//ambil namapt
$str = $owlPDO->query("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'");
$namapt = '';
$str->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $str->fetch()) {
    $namapt = strtoupper($bar->namaorganisasi);
}

//ambil namagudang
$str = $owlPDO->query("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $gudang . "'");
$namagudang = '';
$str->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $str->fetch()) {
    $namagudang = strtoupper($bar->namaorganisasi);
}

//ambil akun laba rugi tahun berjalan:
$CLM = '';
$str = $owlPDO->query("select noakundebet from " . $dbname . ".keu_5parameterjurnal where kodeaplikasi='CLM'");
$str->setFetchMode(PDO::FETCH_OBJ);
while ($bar =  $str->fetch()) {
    $CLM = $bar->noakundebet;
}

//ambil semua noakun dari bulan lalu dan bulan ini
$lmperiode = mktime(0, 0, 0, substr($periode, 5, 2) - 1, 4, substr($periode, 0, 4));
$lmperiode = date('Y-m', $lmperiode);
if ($_SESSION['language'] == 'ID') {
    $str = "select distinct noakun,namaakun from " . $dbname . ".keu_5akun where  noakun!='" . $CLM . "'  " . $where . " order by noakun";
} else {
    $str = "select distinct noakun,namaakun1 as namaakun from " . $dbname . ".keu_5akun where  noakun!='" . $CLM . "' " . $where . " order by noakun";
}
// echo $str;
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
$TAB = array();

while ($bar = $res->fetch()) {
    $TAB[$bar->noakun]['noakun'] = $bar->noakun;
    $TAB[$bar->noakun]['namaakun'] = $bar->namaakun;
    $TAB[$bar->noakun]['sawal'] = 0;
    $TAB[$bar->noakun]['salak'] = 0;
}

$gabungKodeorg = true;
if ($regional == '' && $gudang == '') {
    $where = " and kodeorg in(select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and length(kodeorganisasi)=4)";
} else if ($regional != '' && $gudang == '') {
    $where = " and kodeorg in (select kodeunit from " . $dbname . ".bgt_regional_assignment where regional='" . $regional . "'"
        . " and kodeunit in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "')) ";
} else {
    $where = " and kodeorg ='" . $gudang . "'";
    $gabungKodeorg = false;
}




#disini tambahin kodeorg
$str = "select sum(awal" . substr(str_replace("-", "", $periode), 4, 2) . ") as sawal,noakun,kodeorg from " . $dbname . ".keu_saldobulanan 
      where periode ='" . str_replace("-", "", $periode) . "'  and  noakun!='" . $CLM . "' " . $where . "   group by noakun,kodeorg order by noakun";
// echo $str;
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    if ($gabungKodeorg) {
        @$kode['all'] = 'all';
        $tmpKdOrg = 'all';
    } else {
        @$kode[$bar->kodeorg] = $bar->kodeorg;
        $tmpKdOrg = $bar->kodeorg;
    }
    $TAB[$bar->noakun][$tmpKdOrg]['sawal'] += $bar->sawal;
    $TAB[$bar->noakun][$tmpKdOrg]['salak'] += $bar->sawal;
}





#= akun kas/bank
$str = "select noakun from " . $dbname . ".keu_5akun
    where left(noakun,3) = '111' and detail=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $arrnoakunkb[$bar->noakun] = $bar->noakun;
}

/*
$str=" SELECT 
if(sum(jumlah)>0,sum(jumlah),'0') as debet,
if(sum(jumlah)<0,(sum(jumlah)*-1),'0') as kredit,
noakun,kodeorg
FROM ".$dbname.".`keu_jurnaldt_vw`
WHERE periode>='".$periode."' and periode<='".$periode1."' ".$where." ".$whereakun." 
and noakun!='".$CLM."' and revisi <= '".$revisi."'
 group by noakun,kodeorg,noreferensi,keterangan"; 
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(in_array($bar->noakun,$arrnoakunkb)){
		@$kode[$bar->kodeorg]=$bar->kodeorg;
		@$TAB[$bar->noakun][$bar->kodeorg]['debet']+=$bar->debet;
		@$TAB[$bar->noakun][$bar->kodeorg]['kredit']+=$bar->kredit;
	}
} 
*/

$str = "select sum(debet) as debet,sum(kredit) as kredit, noakun,kodeorg from " . $dbname . ".keu_jurnaldt_vw
    where periode>='" . $periode . "' and periode<='" . $periode1 . "' " . $where . " " . $whereakun . " 
    and noakun!='" . $CLM . "' and revisi <= '" . $revisi . "' group by noakun,kodeorg"; #tidak sama dengan laba/rugi berjalan
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    // if(!in_array($bar->noakun,$arrnoakunkb)){
    if ($gabungKodeorg) {
        @$kode['all'] = 'all';
        $tmpKdOrg = 'all';
    } else {
        @$kode[$bar->kodeorg] = $bar->kodeorg;
        $tmpKdOrg = $bar->kodeorg;
    }
    // @$kode[$bar->kodeorg]=$bar->kodeorg;
    @$TAB[$bar->noakun][$tmpKdOrg]['debet'] += $bar->debet;
    @$TAB[$bar->noakun][$tmpKdOrg]['kredit'] += $bar->kredit;
    // }
}

// foreach ($TAB as $baris => $data) {
//     echo "<pre>" . var_export($data, true) . "</pre>";
// }

// exit('warning! Debugging stop here.');



$no = 0;
$sal_awal = array();
$sal_debet = array();;
$sal_kredit = array();;
$sal_salak = array();;

// echo "<pre>";
// print_r($kode);
// echo "</pre>";
if ($tipelaporan == 'excel') {
    $border = 'border=1';
} else {
    $border = '';
}
$nmorg    = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $gudang . "'");
if ($tipelaporan != 'html') {
    $stream .= "Laporan Neraca<br>";
    if ($gudang == '') {
        $unit     = 'Seluruh Unit';
        $stream .= "" . $unit . "<br>";
    } else {
        $unit = $gudang;
        $stream .= "" . $unit . " - " . $nmorg[$unit] . "<br>";
    }
    $stream .= "Periode " . $periode . " s/d " . $periode1 . "<br><br>";
}

$tmpHeaderUnit = "";
$colspan = 3;

if (!$gabungKodeorg) {
    $tmpHeaderUnit = "<th align=center style='width:50px;'>" . $_SESSION['lang']['unit'] . "</th>";
    $colspan = 4;
}
$stream .= "
        <table class=sortable cellspacing=1 " . $border . ">
            <thead>
                <tr>
                    <th align=center style='width:50px;'>" . $_SESSION['lang']['nomor'] . "</th>
                    <th align=center style='width:80px;'>" . $_SESSION['lang']['noakun'] . "</th>
                    " . $tmpHeaderUnit . "
                    <th align=center style='width:450px;'>" . $_SESSION['lang']['namaakun'] . "</th>
                    <th align=center style='width:130px;'>" . $_SESSION['lang']['saldoawal'] . "</th>
                    <th align=center style='width:130px;'>" . $_SESSION['lang']['debet'] . "</th>
                    <th align=center style='width:130px;'>" . $_SESSION['lang']['kredit'] . "</th>
                    <th align=center style='width:130px;'>" . $_SESSION['lang']['saldoakhir'] . "</th>
                </tr> 
            </thead>
            <tbody>";
if (count($kode) > 0) {
    foreach ($kode as $kdorg) {
        foreach ($TAB as $baris => $data) {
            if ($data['noakun'] != '') {
                // echo "<pre>" . var_export("{$data['noakun']}  ----  {$kdorg} {$data[$kdorg]}", true) . "</pre>";
                if ($tampilanId == 1) {
                    if (($data[$kdorg]['sawal'] == 0) && ($data[$kdorg]['debet'] == 0) && ($data[$kdorg]['kredit'] == 0)) {
                        continue;
                    }
                }
                $no += 1;
                @$data[$kdorg]['salak'] = $data[$kdorg]['sawal'] + $data[$kdorg]['debet'] - $data[$kdorg]['kredit'];

                if ($tipelaporan == 'excel') {
                    $qsawal = $data[$kdorg]['sawal'];
                    $qdebet = isset($data[$kdorg]['debet']) ? $data[$kdorg]['debet'] : 0;
                    $qkredit = isset($data[$kdorg]['kredit']) ? $data[$kdorg]['kredit'] : 0;
                    $qakhir = $data[$kdorg]['salak'];
                } else {
                    $qsawal = number_format($data[$kdorg]['sawal'], 2);
                    $qdebet = number_format(isset($data[$kdorg]['debet']) ? $data[$kdorg]['debet'] : 0, 2);
                    $qkredit = number_format(isset($data[$kdorg]['kredit']) ? $data[$kdorg]['kredit'] : 0, 2);
                    $qakhir = number_format($data[$kdorg]['salak'], 2);
                }

                $tmpDataListUnit = "";
                if (!$gabungKodeorg) {
                    $tmpDataListUnit = "<td style='width:80px;'>" . $kdorg . "</td>";
                }

                $stream .= "<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('" . $data['noakun'] . "','" . $periode . "','" . $periode1 . "','" . $lmperiode . "','" . $pt . "','" . $regional . "','" . $kdorg . "','" . $revisi . "',event);\">
                    <td style='width:50px;' align=center>" . $no . "</td>
                    <td style='width:80px;'>" . $data['noakun'] . "</td>    
                    " . $tmpDataListUnit . "
                    <td style='width:450px;'>" . $data['namaakun'] . "</td>
                    <td align=right style='width:130px;'>" . $qsawal . "</td>
                    <td align=right style='width:130px;'>" . $qdebet . "</td>
                    <td align=right style='width:130px;'>" . $qkredit . "</td>   
                    <td align=right style='width:130px;'>" . $qakhir . "</td>    
                </tr>";
                // } 
                $sal_awal[$kdorg] += $data[$kdorg]['sawal'];
                $sal_debet[$kdorg] += isset($data[$kdorg]['debet']) ? $data[$kdorg]['debet'] : 0;
                $sal_kredit[$kdorg] += isset($data[$kdorg]['kredit']) ? $data[$kdorg]['kredit'] : 0;
                $sal_salak[$kdorg] += $data[$kdorg]['salak'];
                // $sal_awal[$kdorg]+=round($data[$kdorg]['sawal'],2);
                // $qwed=isset($data[$kdorg]['debet'])? $data[$kdorg]['debet']: 0;
                // $qwek=isset($data[$kdorg]['kredit'])? $data[$kdorg]['kredit']: 0;
                // $sal_debet[$kdorg]+=round($qwed,2);
                // $sal_kredit[$kdorg]+=round($qwek,2);
                // $sal_salak[$kdorg]+=round($data[$kdorg]['salak'],2); 
            }
        }
        $stream .= "<tr class=rowcontent>
            <td colspan={$colspan} align=center><b>TOTAL</b></td>
            <td align=right><b>" . number_format($sal_awal[$kdorg], 2) . "</b></td>
            <td align=right><b>" . number_format($sal_debet[$kdorg], 2) . "</b></td>
            <td align=right><b>" . number_format($sal_kredit[$kdorg], 2) . "</b></td>   
            <td align=right><b>" . number_format($sal_salak[$kdorg], 2) . "</b></td> 
        </tr>";
        @$gtsawal += $sal_awal[$kdorg];
        @$gtdb += $sal_debet[$kdorg];
        @$gtkr += $sal_kredit[$kdorg];
        @$gtsalak += $sal_salak[$kdorg];
    }
}
$stream .= "<tr class=rowcontent>
            <td colspan={$colspan} align=center><b>GRAND TOTAL</b></td>
            <td align=right><b>" . number_format($gtsawal, 2) . "</b></td>
            <td align=right><b>" . number_format($gtdb, 2) . "</b></td>
            <td align=right><b>" . number_format($gtkr, 2) . "</b></td>   
            <td align=right><b>" . number_format($gtsalak, 2) . "</b></td> 
        </tr>";
$stream .= "</tbody>
            <tfoot>
            </tfoot>		 
        </table>";

if ($tipelaporan == 'html') {
    echo $stream;
} else {

    $stream .= "Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
    $qwe = date("YmdHms");
    $nop = "NeracaSaldo_" . $gudang . $periode . "rev" . $revisi . "___" . $qwe . ".xls";
    $xls = new HtmlExcel();
    $xls->setCss($css);
    $xls->addSheet("Trend_" . $periode, $stream);
    $xls->addSheet("Notes_" . $periode, $streamdetail);
    $xls->headers($nop);
    echo $xls->buildFile();

    // exit("Error:A");
    // $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    // $qwe=date("YmdHms");
    // $nop_="NeracaSaldo_".$gudang.$periode."rev".$revisi."___".$qwe;
    // if(strlen($stream)>0)
    // {
    // $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
    // gzwrite($gztralala, $stream);
    // gzclose($gztralala);
    // echo "<script language=javascript1.2>
    // window.location='tempExcel/".$nop_.".xls.gz';
    // </script>";
    // }
}
