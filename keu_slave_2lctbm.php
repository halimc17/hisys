<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');

if($pt=='' || $periode==''){
    exit('PT dan periode wajib diisi');
}

$periodeDb = str_replace('-', '', $periode);
$bulan = substr($periodeDb, 4, 2);
$tahun = substr($periodeDb, 0, 4);
$awalBulan = $tahun.'-'.$bulan.'-01';
$akhirBulan = date('Y-m-t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun)));
$tanggalCaption = date('t', strtotime($akhirBulan)).' '.strtoupper(numToMonth($bulan, 'I', 'long')).' '.$tahun;

function lcTbmSqlList($arr)
{
    $out = array();
    foreach($arr as $val){
        $out[] = "'".str_replace("'", "''", $val)."'";
    }
    return implode(',', $out);
}

function lcTbmOrgScope($owlPDO, $dbname, $root)
{
    $scope = array($root);
    $queue = array($root);
    while(!empty($queue)){
        $parent = array_shift($queue);
        $str = "select kodeorganisasi from ".$dbname.".organisasi where induk='".str_replace("'", "''", $parent)."'";
        $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if(!in_array($bar['kodeorganisasi'], $scope)){
                $scope[] = $bar['kodeorganisasi'];
                $queue[] = $bar['kodeorganisasi'];
            }
        }
    }
    return $scope;
}

function lcTbmMoney($nilai)
{
    if(abs($nilai)<0.005){
        return '-';
    }
    return number_format($nilai, 0);
}

function lcTbmSetup($owlPDO, $dbname, $kelompok)
{
    $data = array('akun'=>array(), 'kegiatan'=>array(), 'pairs'=>array(), 'label'=>array());
    $kelompok = str_replace("'", "''", $kelompok);
    $str = "select k.noakun, k.kodekegiatan, k.namakegiatan, a.namaakun
        from ".$dbname.".setup_kegiatan k
        left join ".$dbname.".keu_5akun a on a.noakun=k.noakun
        where k.kelompok='".$kelompok."' and k.status=1 and k.noakun<>'' and k.kodekegiatan<>''
        order by k.noakun, k.kodekegiatan";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $data['akun'][$bar['noakun']] = $bar['noakun'];
        $data['kegiatan'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
        $data['pairs'][$bar['kodekegiatan'].'|'.$bar['noakun']] = $bar['noakun'];
        if(!isset($data['label'][$bar['noakun']])){
            $nama = trim($bar['namaakun'])!='' ? trim($bar['namaakun']) : trim($bar['namakegiatan']);
            $data['label'][$bar['noakun']] = $nama;
        }
    }
    return $data;
}

if($unit==''){
    $orgScope = lcTbmOrgScope($owlPDO, $dbname, $pt);
    $unitx = $pt;
}else{
    $orgScope = array($unit);
    $unitx = $unit;
}
$whereOrgList = lcTbmSqlList($orgScope);

$namaOrg = $unitx;
$str = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$unitx."'";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $namaOrg = $bar['namaorganisasi'];
}

$tbSetup = lcTbmSetup($owlPDO, $dbname, 'TB');
$tbRows = array();
foreach($tbSetup['akun'] as $akun){
    $tbRows[$akun] = array('awal'=>0, 'mutasi'=>0, 'akhir'=>0);
}
if(!empty($tbSetup['kegiatan']) && !empty($tbSetup['akun'])){
    $str = "select d.kodekegiatan, d.noakun, d.tanggal, d.jumlah
        from ".$dbname.".keu_jurnaldt d force index(kodekegiatan)
        where d.tanggal<='".$akhirBulan."' and d.kodeorg in(".$whereOrgList.")
            and d.kodekegiatan in(".lcTbmSqlList($tbSetup['kegiatan']).")
            and d.noakun in(".lcTbmSqlList($tbSetup['akun']).")";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        if(!isset($tbSetup['pairs'][$bar['kodekegiatan'].'|'.$bar['noakun']])) continue;
        $akun = $tbSetup['pairs'][$bar['kodekegiatan'].'|'.$bar['noakun']];
        $jumlah = floatval($bar['jumlah']);
        if($bar['tanggal']<$awalBulan){
            $tbRows[$akun]['awal'] += $jumlah;
        }
        if($bar['tanggal']>=$awalBulan && $bar['tanggal']<=$akhirBulan){
            $tbRows[$akun]['mutasi'] += $jumlah;
        }
        $tbRows[$akun]['akhir'] += $jumlah;
    }
}

$str = "select count(*) as jumlah from ".$dbname.".setup_blok_tahunan where tahun='".$periodeDb."'";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$useBlokTahunan = false;
while($bar=$res->fetch()){
    $useBlokTahunan = intval($bar['jumlah'])>0;
}
if($useBlokTahunan){
    $joinBlok = "left join ".$dbname.".setup_blok_tahunan bt on bt.kodeorg=d.kodeblok and bt.tahun='".$periodeDb."'
        left join ".$dbname.".setup_blok bs on bs.kodeorg=d.kodeblok";
    $tahunTanamExpr = "coalesce(bt.tahuntanam, bs.tahuntanam)";
}else{
    $joinBlok = "left join ".$dbname.".setup_blok bs on bs.kodeorg=d.kodeblok";
    $tahunTanamExpr = "bs.tahuntanam";
}

$tbmSetup = lcTbmSetup($owlPDO, $dbname, 'TBM');
$tbmRows = array();
if(!empty($tbmSetup['kegiatan']) && !empty($tbmSetup['akun'])){
    $str = "select d.kodekegiatan, d.noakun, d.tanggal, d.jumlah, ".$tahunTanamExpr." as tahuntanam
        from ".$dbname.".keu_jurnaldt d force index(kodekegiatan)
        ".$joinBlok."
        where d.tanggal<='".$akhirBulan."' and d.kodeorg in(".$whereOrgList.")
            and d.kodeblok<>'' and ".$tahunTanamExpr." is not null and ".$tahunTanamExpr.">0
            and d.kodekegiatan in(".lcTbmSqlList($tbmSetup['kegiatan']).")
            and d.noakun in(".lcTbmSqlList($tbmSetup['akun']).")";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        if(!isset($tbmSetup['pairs'][$bar['kodekegiatan'].'|'.$bar['noakun']])) continue;
        $tahunTanam = intval($bar['tahuntanam']);
        if($tahunTanam<=0) continue;
        $akun = $tbmSetup['pairs'][$bar['kodekegiatan'].'|'.$bar['noakun']];
        if(!isset($tbmRows[$tahunTanam])){
            $tbmRows[$tahunTanam] = array();
            foreach($tbmSetup['akun'] as $akunSetup){
                $tbmRows[$tahunTanam][$akunSetup] = array('awal'=>0, 'mutasi'=>0, 'akhir'=>0);
            }
        }
        $jumlah = floatval($bar['jumlah']);
        if($bar['tanggal']<$awalBulan){
            $tbmRows[$tahunTanam][$akun]['awal'] += $jumlah;
        }
        if($bar['tanggal']>=$awalBulan && $bar['tanggal']<=$akhirBulan){
            $tbmRows[$tahunTanam][$akun]['mutasi'] += $jumlah;
        }
        $tbmRows[$tahunTanam][$akun]['akhir'] += $jumlah;
    }
}
ksort($tbmRows);

$border = ($tipe=='excel') ? 1 : 0;
$stream = "<style>
    .lcTbm{border-collapse:collapse;font-family:Arial;font-size:12px;min-width:760px}
    .lcTbm td{border:1px solid #222;padding:4px;height:22px}
    .lcTbm .center{text-align:center;font-weight:bold}
    .lcTbm .head{font-weight:bold;text-align:center;vertical-align:middle}
    .lcTbm .right{text-align:right;white-space:nowrap}
    .lcTbm .section td{font-weight:bold}
    .lcTbm .total td{font-weight:bold;background:#92d050}
    .lcTbm .grand td{font-weight:bold;background:#ffff00}
    .lcTbm .noborder td{border:0}
</style>";
$stream .= "<table class='lcTbm' border='".$border."' cellspacing=0 cellpadding=3>";
$stream .= "<tr class='noborder'><td colspan=5 class=center>".strtoupper($namaOrg)."</td></tr>";
$stream .= "<tr class='noborder'><td colspan=5 class=center>LAPORAN LC & TBM</td></tr>";
$stream .= "<tr class='noborder'><td colspan=5 class=center>PER ".$tanggalCaption."</td></tr>";
$stream .= "<tr class='noborder'><td colspan=5>&nbsp;</td></tr>";
$stream .= "<tr>
    <td rowspan=2 class=head style='width:45px'>NO</td>
    <td rowspan=2 class=head style='width:420px'>URAIAN</td>
    <td colspan=3 class=head>NILAI PEROLEHAN</td>
</tr>";
$stream .= "<tr>
    <td class=head style='width:130px'>PEROLEHAN AWAL</td>
    <td class=head style='width:130px'>PENAMBAHAN/PENGURANGAN</td>
    <td class=head style='width:130px'>PEROLEHAN AKHIR</td>
</tr>";

$grand = array('awal'=>0, 'mutasi'=>0, 'akhir'=>0);
$stream .= "<tr class=section><td class=center>1</td><td>Land Clearing</td><td></td><td></td><td></td></tr>";
$totalLc = array('awal'=>0, 'mutasi'=>0, 'akhir'=>0);
foreach($tbRows as $akun=>$val){
    foreach($totalLc as $key=>$dummy){
        $totalLc[$key] += $val[$key];
        $grand[$key] += $val[$key];
    }
    $label = isset($tbSetup['label'][$akun]) ? $tbSetup['label'][$akun] : $akun;
    $stream .= "<tr>
        <td></td>
        <td>Land Clearing - ".htmlspecialchars($label, ENT_QUOTES)." <span style='color:#777'>(".$akun.")</span></td>
        <td class=right>".lcTbmMoney($val['awal'])."</td>
        <td class=right>".lcTbmMoney($val['mutasi'])."</td>
        <td class=right>".lcTbmMoney($val['akhir'])."</td>
    </tr>";
}
$stream .= "<tr class=total><td></td><td>Total Land Clearing</td>
    <td class=right>".lcTbmMoney($totalLc['awal'])."</td>
    <td class=right>".lcTbmMoney($totalLc['mutasi'])."</td>
    <td class=right>".lcTbmMoney($totalLc['akhir'])."</td></tr>";

$no = 2;
foreach($tbmRows as $tahunTanam=>$akunRows){
    $stream .= "<tr><td colspan=5>&nbsp;</td></tr>";
    $stream .= "<tr class=section><td class=center>".$no++."</td><td>Tanaman Belum Menghasilkan (TBM) ".$tahunTanam."</td><td></td><td></td><td></td></tr>";
    $totalTbm = array('awal'=>0, 'mutasi'=>0, 'akhir'=>0);
    foreach($akunRows as $akun=>$val){
        foreach($totalTbm as $key=>$dummy){
            $totalTbm[$key] += $val[$key];
            $grand[$key] += $val[$key];
        }
        $label = isset($tbmSetup['label'][$akun]) ? $tbmSetup['label'][$akun] : $akun;
        $stream .= "<tr>
            <td></td>
            <td>Tanaman Belum Menghasilkan TT. ".$tahunTanam." - ".htmlspecialchars($label, ENT_QUOTES)." <span style='color:#777'>(".$akun.")</span></td>
            <td class=right>".lcTbmMoney($val['awal'])."</td>
            <td class=right>".lcTbmMoney($val['mutasi'])."</td>
            <td class=right>".lcTbmMoney($val['akhir'])."</td>
        </tr>";
    }
    $stream .= "<tr class=total><td></td><td>Total Tanaman Belum Menghasilkan (TBM) ".$tahunTanam."</td>
        <td class=right>".lcTbmMoney($totalTbm['awal'])."</td>
        <td class=right>".lcTbmMoney($totalTbm['mutasi'])."</td>
        <td class=right>".lcTbmMoney($totalTbm['akhir'])."</td></tr>";
}
$stream .= "<tr><td colspan=5>&nbsp;</td></tr>";
$stream .= "<tr class=grand><td></td><td>Total</td>
    <td class=right>".lcTbmMoney($grand['awal'])."</td>
    <td class=right>".lcTbmMoney($grand['mutasi'])."</td>
    <td class=right>".lcTbmMoney($grand['akhir'])."</td></tr>";
$stream .= "</table>";

if($tipe=='excel'){
    $stream .= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop = "LcTbm-".$pt."_".$unit."_".$periode."___".date("YmdHis").".xls";
    if ($handle = opendir('tempExcel')) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.html") {
                @unlink('tempExcel/'.$file);
            }
        }
        closedir($handle);
    }
    $handle=fopen("tempExcel/".$nop,'w');
    if(!fwrite($handle,$stream)){
        echo 'Can not convert to excel format';
        exit;
    }
    fclose($handle);
    echo "tempExcel/".$nop;
}else{
    echo $stream;
}
?>
