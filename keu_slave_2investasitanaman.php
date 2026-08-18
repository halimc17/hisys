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

function invTanamanSqlList($arr)
{
    $out = array();
    foreach($arr as $val){
        $out[] = "'".str_replace("'", "''", $val)."'";
    }
    return implode(',', $out);
}

function invTanamanOrgScope($owlPDO, $dbname, $root)
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

function invTanamanMoney($nilai)
{
    if(abs($nilai)<0.005){
        return '-';
    }
    return number_format($nilai, 0);
}

function invTanamanMoneyDecimal($nilai)
{
    if(abs($nilai)<0.005){
        return '-';
    }
    return number_format($nilai, 2);
}

function invTanamanGetKegiatanValue($owlPDO, $dbname, $kelompok, $whereOrg, $awalBulan, $akhirBulan)
{
    $data = array('awal'=>0, 'mutasi'=>0, 'akhir'=>0);
    $kelompok = str_replace("'", "''", $kelompok);
    $setup = invTanamanGetSetupKegiatan($owlPDO, $dbname, $kelompok);
    if(empty($setup['kegiatan']) || empty($setup['akun'])){
        return $data;
    }
    $str = "select d.kodekegiatan, d.noakun, d.tanggal, d.jumlah
        from ".$dbname.".keu_jurnaldt d force index(kodekegiatan)
        where d.tanggal<='".$akhirBulan."' and d.kodeorg in(".$whereOrg.")
            and d.kodekegiatan in(".invTanamanSqlList($setup['kegiatan']).")
            and d.noakun in(".invTanamanSqlList($setup['akun']).")";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        if(!isset($setup['pairs'][$bar['kodekegiatan'].'|'.$bar['noakun']])) continue;
        $jumlah = floatval($bar['jumlah']);
        if($bar['tanggal']<$awalBulan){
            $data['awal'] += $jumlah;
        }
        if($bar['tanggal']>=$awalBulan && $bar['tanggal']<=$akhirBulan){
            $data['mutasi'] += $jumlah;
        }
        $data['akhir'] += $jumlah;
    }
    return $data;
}

function invTanamanGetSetupKegiatan($owlPDO, $dbname, $kelompok)
{
    $data = array('kegiatan'=>array(), 'akun'=>array(), 'pairs'=>array());
    $kelompok = str_replace("'", "''", $kelompok);
    $str = "select kodekegiatan, noakun
        from ".$dbname.".setup_kegiatan
        where kelompok='".$kelompok."' and status=1 and kodekegiatan<>'' and noakun<>''";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $data['kegiatan'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
        $data['akun'][$bar['noakun']] = $bar['noakun'];
        $data['pairs'][$bar['kodekegiatan'].'|'.$bar['noakun']] = true;
    }
    return $data;
}

if($unit==''){
    $orgScope = invTanamanOrgScope($owlPDO, $dbname, $pt);
    $unitx = $pt;
}else{
    $orgScope = array($unit);
    $unitx = $unit;
}
$whereOrgList = invTanamanSqlList($orgScope);

$namaOrg = $unitx;
$str = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$unitx."'";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $namaOrg = $bar['namaorganisasi'];
}

$rows = array();

$bbt = invTanamanGetKegiatanValue($owlPDO, $dbname, 'BBT', $whereOrgList, $awalBulan, $akhirBulan);
$rows[] = array(
    'no'=>'',
    'uraian'=>'Pembibitan',
    'manfaat'=>'',
    'awal'=>$bbt['awal'],
    'mutasi'=>$bbt['mutasi'],
    'akhir'=>$bbt['akhir'],
    'rate'=>'5%',
    'susut_awal'=>0,
    'susut'=>$bbt['akhir']==0 ? 0 : 0,
    'susut_akhir'=>0,
    'nilai_buku'=>$bbt['akhir']
);

$tb = invTanamanGetKegiatanValue($owlPDO, $dbname, 'TB', $whereOrgList, $awalBulan, $akhirBulan);
$rows[] = array(
    'no'=>'',
    'uraian'=>'Land Clearing',
    'manfaat'=>'',
    'awal'=>$tb['awal'],
    'mutasi'=>$tb['mutasi'],
    'akhir'=>$tb['akhir'],
    'rate'=>'5%',
    'susut_awal'=>0,
    'susut'=>0,
    'susut_akhir'=>0,
    'nilai_buku'=>$tb['akhir']
);

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
    
    $strLuas = "select coalesce(bt.tahuntanam, bs.tahuntanam) as tahuntanam, 
                       coalesce(bt.statusblok, bs.statusblok) as statusblok, 
                       sum(coalesce(bt.luasareaproduktif, bs.luasareaproduktif)) as luasan 
                from ".$dbname.".setup_blok bs 
                left join ".$dbname.".setup_blok_tahunan bt on bt.kodeorg=bs.kodeorg and bt.tahun='".$periodeDb."'
                where substr(bs.kodeorg,1,4) in (".$whereOrgList.")
                group by coalesce(bt.tahuntanam, bs.tahuntanam), coalesce(bt.statusblok, bs.statusblok)";
}else{
    $joinBlok = "left join ".$dbname.".setup_blok bs on bs.kodeorg=d.kodeblok";
    $tahunTanamExpr = "bs.tahuntanam";
    
    $strLuas = "select tahuntanam, statusblok, sum(luasareaproduktif) as luasan 
                from ".$dbname.".setup_blok 
                where substr(kodeorg,1,4) in (".$whereOrgList.") 
                group by tahuntanam, statusblok";
}
// exit('warning.'.$strLuas);

$luasanPerTahun = array('TBM'=>array(), 'TM'=>array());
$resLuas = $owlPDO->query($strLuas) or die(print " Gagal: ".PDOException::getMessage());
$resLuas->setFetchMode(PDO::FETCH_ASSOC);
while($barLuas = $resLuas->fetch()){
    $tt = intval($barLuas['tahuntanam']);
    $st = strtoupper(trim($barLuas['statusblok']));
    if($tt > 0 && in_array($st, array('TBM', 'TM'))){
        if(!isset($luasanPerTahun[$st][$tt])) $luasanPerTahun[$st][$tt] = 0;
        $luasanPerTahun[$st][$tt] += floatval($barLuas['luasan']);
    }
}

$tbmRows = array();
$tbmSetup = invTanamanGetSetupKegiatan($owlPDO, $dbname, 'TBM');
if(!empty($tbmSetup['kegiatan']) && !empty($tbmSetup['akun'])){
    $str = "select d.kodekegiatan, d.noakun, d.tanggal, d.jumlah, ".$tahunTanamExpr." as tahuntanam
        from ".$dbname.".keu_jurnaldt d force index(kodekegiatan)
        ".$joinBlok."
        where d.tanggal<='".$akhirBulan."' and d.kodeorg in(".$whereOrgList.")
            and d.kodeblok<>'' and ".$tahunTanamExpr." is not null and ".$tahunTanamExpr.">0
            and d.kodekegiatan in(".invTanamanSqlList($tbmSetup['kegiatan']).")
            and d.noakun in(".invTanamanSqlList($tbmSetup['akun']).")";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        if(!isset($tbmSetup['pairs'][$bar['kodekegiatan'].'|'.$bar['noakun']])) continue;
        $tahunTanam = intval($bar['tahuntanam']);
        if($tahunTanam<=0) continue;
        if(!isset($tbmRows[$tahunTanam])){
            $tbmRows[$tahunTanam] = array('awal'=>0, 'mutasi'=>0, 'akhir'=>0);
        }
        $jumlah = floatval($bar['jumlah']);
        if($bar['tanggal']<$awalBulan){
            $tbmRows[$tahunTanam]['awal'] += $jumlah;
        }
        if($bar['tanggal']>=$awalBulan && $bar['tanggal']<=$akhirBulan){
            $tbmRows[$tahunTanam]['mutasi'] += $jumlah;
        }
        $tbmRows[$tahunTanam]['akhir'] += $jumlah;
    }
}
ksort($tbmRows);
foreach($tbmRows as $tahunTanam=>$bar){
    if(abs($bar['awal'])+abs($bar['mutasi'])+abs($bar['akhir'])<=0) continue;
    $luasanTBM = isset($luasanPerTahun['TBM'][$tahunTanam]) ? $luasanPerTahun['TBM'][$tahunTanam] : 0;
    $rows[] = array(
        'no'=>'auto',
        'uraian'=>'Tahun tanam '.intval($tahunTanam),
        'status_tanaman'=>'Tanaman Belum Menghasilkan',
        'luasan'=>$luasanTBM,
        'manfaat'=>'20',
        'awal'=>floatval($bar['awal']),
        'mutasi'=>floatval($bar['mutasi']),
        'akhir'=>floatval($bar['akhir']),
        'rate'=>'5%',
        'susut_awal'=>0,
        'susut'=>0,
        'susut_akhir'=>0,
        'nilai_buku'=>floatval($bar['akhir'])
    );
}

$str = "select noakun, akunak from ".$dbname.".sdm_5tipeasset where kodetipe='TM' limit 1";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$akunSusutTm = '';
while($bar=$res->fetch()){
    $akunSusutTm = trim($bar['noakun']);
}

$str = "select a.kodeasset, a.kodeorg, a.namasset, a.tanggalperolehan, a.hargaperolehan,
        a.jlhblnpenyusutan, year(a.tanggalperolehan) as tahunasset
    from ".$dbname.".sdm_daftarasset a
    left join ".$dbname.".sdm_5tipeasset t on t.kodetipe=a.tipeasset
    where (a.tipeasset in ('TM','T1') or t.namatipe like '%Tanaman Menghasilkan%')
        and a.kodeorg in(".$whereOrgList.")
        and a.tanggalperolehan<='".$akhirBulan."'
        and (a.leasing=1 or (a.namasset not like '%leasing%' and (t.namatipe is null or t.namatipe not like '%leasing%')))";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$tmAssets = array();
$assetCodes = array();
$tmRawAssets = array();
while($bar=$res->fetch()){
    $tmRawAssets[$bar['kodeasset']] = $bar;
}

$assetTanam = array();
if(!empty($tmRawAssets)){
    $chunks = array_chunk(array_keys($tmRawAssets), 500);
    foreach($chunks as $chunk){
        if($useBlokTahunan){
            $str = "select x.kodeasset, coalesce(bt.tahuntanam, b.tahuntanam) as tahuntanam
                from (
                    select kodeasset, max(kodeblok) as kodeblok
                    from ".$dbname.".keu_jurnaldt force index(kodeasset)
                    where kodeasset in(".invTanamanSqlList($chunk).") and kodeblok<>''
                    group by kodeasset
                ) x
                left join ".$dbname.".setup_blok_tahunan bt on bt.kodeorg=x.kodeblok and bt.tahun='".$periodeDb."'
                left join ".$dbname.".setup_blok b on b.kodeorg=x.kodeblok";
        }else{
            $str = "select x.kodeasset, b.tahuntanam
                from (
                    select kodeasset, max(kodeblok) as kodeblok
                    from ".$dbname.".keu_jurnaldt force index(kodeasset)
                    where kodeasset in(".invTanamanSqlList($chunk).") and kodeblok<>''
                    group by kodeasset
                ) x
                left join ".$dbname.".setup_blok b on b.kodeorg=x.kodeblok";
        }
        $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if(intval($bar['tahuntanam'])>0){
                $assetTanam[$bar['kodeasset']] = intval($bar['tahuntanam']);
            }
        }
    }
}

foreach($tmRawAssets as $bar){
    $tahunTanam = isset($assetTanam[$bar['kodeasset']]) ? $assetTanam[$bar['kodeasset']] : 0;
    if($tahunTanam<=0){
        $tahunTanam = intval($bar['tahunasset']);
    }
    if($tahunTanam<=0 && preg_match('/\bT[TA]\s*([0-9]{4})\b/i', $bar['namasset'], $match)){
        $tahunTanam = intval($match[1]);
    }
    if($tahunTanam<=0) continue;
    if(!isset($tmAssets[$tahunTanam])){
        $tmAssets[$tahunTanam] = array(
            'awal'=>0, 'mutasi'=>0, 'akhir'=>0, 'manfaat'=>0,
            'susut_awal'=>0, 'susut'=>0, 'susut_akhir'=>0
        );
    }
    $harga = floatval($bar['hargaperolehan']);
    if($bar['tanggalperolehan']<$awalBulan){
        $tmAssets[$tahunTanam]['awal'] += $harga;
    }
    if($bar['tanggalperolehan']>=$awalBulan && $bar['tanggalperolehan']<=$akhirBulan){
        $tmAssets[$tahunTanam]['mutasi'] += $harga;
    }
    $tmAssets[$tahunTanam]['akhir'] += $harga;
    $tmAssets[$tahunTanam]['manfaat'] = max($tmAssets[$tahunTanam]['manfaat'], ceil(floatval($bar['jlhblnpenyusutan'])/12));
    $assetCodes[$bar['kodeasset']] = $tahunTanam;
}

if(!empty($assetCodes) && $akunSusutTm!=''){
    $chunks = array_chunk(array_keys($assetCodes), 500);
    foreach($chunks as $chunk){
        $str = "select kodeasset,
                sum(case when tanggal<'".$awalBulan."' then abs(jumlah) else 0 end) as susut_awal,
                sum(case when tanggal>='".$awalBulan."' and tanggal<='".$akhirBulan."' then abs(jumlah) else 0 end) as susut,
                sum(case when tanggal<='".$akhirBulan."' then abs(jumlah) else 0 end) as susut_akhir
            from ".$dbname.".keu_jurnaldt force index(kodeasset)
            where tanggal<='".$akhirBulan."' and noakun='".$akunSusutTm."'
                and kodeasset in(".invTanamanSqlList($chunk).")
            group by kodeasset";
        $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $tahunTanam = $assetCodes[$bar['kodeasset']];
            $tmAssets[$tahunTanam]['susut_awal'] += floatval($bar['susut_awal']);
            $tmAssets[$tahunTanam]['susut'] += floatval($bar['susut']);
            $tmAssets[$tahunTanam]['susut_akhir'] += floatval($bar['susut_akhir']);
        }
    }
}
ksort($tmAssets);
foreach($tmAssets as $tahunTanam=>$val){
    $luasanTM = isset($luasanPerTahun['TM'][$tahunTanam]) ? $luasanPerTahun['TM'][$tahunTanam] : 0;
    $rows[] = array(
        'no'=>'auto',
        'uraian'=>'Tahun tanam '.$tahunTanam,
        'status_tanaman'=>'Tanaman Menghasilkan',
        'luasan'=>$luasanTM,
        'manfaat'=>$val['manfaat']>0 ? $val['manfaat'] : '',
        'awal'=>$val['awal'],
        'mutasi'=>$val['mutasi'],
        'akhir'=>$val['akhir'],
        'rate'=>$val['manfaat']>0 ? number_format((1/$val['manfaat'])*100, 2).'%' : '',
        'susut_awal'=>$val['susut_awal'],
        'susut'=>$val['susut'],
        'susut_akhir'=>$val['susut_akhir'],
        'nilai_buku'=>$val['akhir']-$val['susut_akhir']
    );
}

$no = 1;
$total = array('awal'=>0, 'mutasi'=>0, 'akhir'=>0, 'susut_awal'=>0, 'susut'=>0, 'susut_akhir'=>0, 'nilai_buku'=>0);
foreach($rows as $idx=>$row){
    if($rows[$idx]['no']=='auto'){
        $rows[$idx]['no'] = $no++;
    }
    foreach($total as $key=>$val){
        $total[$key] += $rows[$idx][$key];
    }
}

$border = ($tipe=='excel') ? 1 : 0;
$stream = "<style>
    .invTanaman{border-collapse:collapse;font-family:Arial;font-size:12px;min-width:1220px}
    .invTanaman td{border:1px solid #444;padding:4px;height:22px}
    .invTanaman .center{text-align:center;font-weight:bold}
    .invTanaman .head{font-weight:bold;text-align:center;vertical-align:middle}
    .invTanaman .right{text-align:right;white-space:nowrap}
    .invTanaman .middle{vertical-align:middle}
    .invTanaman .total td{font-weight:bold}
    .invTanaman .noborder td{border:0}
</style>";
$stream .= "<table class='invTanaman' border='".$border."' cellspacing=0 cellpadding=3>";
$stream .= "<tr class='noborder'><td colspan=13 class=center>".strtoupper($namaOrg)."</td></tr>";
$stream .= "<tr class='noborder'><td colspan=13 class=center>INVESTASI TANAMAN</td></tr>";
$stream .= "<tr class='noborder'><td colspan=13 class=center>PER ".$tanggalCaption."</td></tr>";
$stream .= "<tr class='noborder'><td colspan=13>&nbsp;</td></tr>";
$stream .= "<tr>
    <td rowspan=2 class=head style='width:45px'>NO</td>
    <td rowspan=2 class=head style='width:240px'>URAIAN</td>
    <td rowspan=2 class=head style='width:100px'>Status Tanaman</td>
    <td rowspan=2 class=head style='width:100px'>Luasan</td>
    <td rowspan=2 class=head style='width:110px'>Manfaat Ekonomis</td>
    <td colspan=3 class=head>NILAI PEROLEHAN</td>
    <td rowspan=2 class=head style='width:70px'>RATE %</td>
    <td colspan=3 class=head>MUTASI PENYUSUTAN</td>
    <td rowspan=2 class=head style='width:120px'>NILAI BUKU</td>
</tr>";
$stream .= "<tr>
    <td class=head style='width:120px'>PEROLEHAN AWAL</td>
    <td class=head style='width:120px'>PENAMBAHAN / PENGURANGAN</td>
    <td class=head style='width:120px'>PEROLEHAN AKHIR</td>
    <td class=head style='width:120px'>PENYUSUTAN AWAL</td>
    <td class=head style='width:120px'>PENYUSUTAN</td>
    <td class=head style='width:120px'>PENYUSUTAN AKHIR</td>
</tr>";

foreach($rows as $row){
    $stream .= "<tr>
        <td class=center>".$row['no']."</td>
        <td>".htmlspecialchars($row['uraian'], ENT_QUOTES)."</td>
        <td class=center>".$row['status_tanaman']."</td>
        <td class=center>".$row['luasan']."</td>
        <td class=center>".$row['manfaat']."</td>
        <td class=right>".invTanamanMoney($row['awal'])."</td>
        <td class=right>".invTanamanMoney($row['mutasi'])."</td>
        <td class=right>".invTanamanMoney($row['akhir'])."</td>
        <td class=center>".$row['rate']."</td>
        <td class=right>".invTanamanMoney($row['susut_awal'])."</td>
        <td class=right>".invTanamanMoneyDecimal($row['susut'])."</td>
        <td class=right>".invTanamanMoney($row['susut_akhir'])."</td>
        <td class=right>".invTanamanMoney($row['nilai_buku'])."</td>
    </tr>";
}

$stream .= "<tr class=total>
    <td></td>
    <td>Total</td>
    <td></td>
    <td></td>
    <td></td>
    <td class=right>".invTanamanMoney($total['awal'])."</td>
    <td class=right>".invTanamanMoney($total['mutasi'])."</td>
    <td class=right>".invTanamanMoney($total['akhir'])."</td>
    <td></td>
    <td class=right>".invTanamanMoney($total['susut_awal'])."</td>
    <td class=right>".invTanamanMoneyDecimal($total['susut'])."</td>
    <td class=right>".invTanamanMoney($total['susut_akhir'])."</td>
    <td class=right>".invTanamanMoney($total['nilai_buku'])."</td>
</tr>";
$stream .= "</table>";

if($tipe=='excel'){
    $stream .= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop = "InvestasiTanaman-".$pt."_".$unit."_".$periode."___".date("YmdHis").".xls";
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
