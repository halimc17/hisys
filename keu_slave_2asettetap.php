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

function asetTetapSqlList($arr)
{
    $out = array();
    foreach($arr as $val){
        $out[] = "'".str_replace("'", "''", $val)."'";
    }
    return implode(',', $out);
}

function asetTetapOrgScope($owlPDO, $dbname, $root)
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

function asetTetapMoney($nilai, $decimal=false)
{
    if(abs($nilai)<0.005){
        return '-';
    }
    return number_format($nilai, $decimal ? 2 : 0);
}

function asetTetapRoman($num)
{
    $map = array(
        'M'=>1000,'CM'=>900,'D'=>500,'CD'=>400,'C'=>100,'XC'=>90,
        'L'=>50,'XL'=>40,'X'=>10,'IX'=>9,'V'=>5,'IV'=>4,'I'=>1
    );
    $out = '';
    foreach($map as $roman=>$value){
        while($num >= $value){
            $out .= $roman;
            $num -= $value;
        }
    }
    return $out;
}

if($unit==''){
    $orgScope = asetTetapOrgScope($owlPDO, $dbname, $pt);
    $unitx = $pt;
}else{
    $orgScope = array($unit);
    $unitx = $unit;
}
$whereOrgList = asetTetapSqlList($orgScope);

$namaOrg = $unitx;
$str = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$unitx."'";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $namaOrg = $bar['namaorganisasi'];
}

$str = "select a.kodeasset, a.kodeorg, a.tipeasset, a.tanggalperolehan, a.namasset,
        a.hargaperolehan, a.jlhblnpenyusutan, a.bulanan, a.tanggaldisposal,
        t.namatipe, t.noakun as akunsusut
    from ".$dbname.".sdm_daftarasset a
    left join ".$dbname.".sdm_5tipeasset t on t.kodetipe=a.tipeasset
    where a.kodeorg in(".$whereOrgList.")
        and a.tanggalperolehan<='".$akhirBulan."'
        and (a.tanggaldisposal='0000-00-00' or a.tanggaldisposal>='".$awalBulan."')
        and a.tipeasset not in ('TM','T1')
        and (t.namatipe is null or t.namatipe not like '%Tanaman Menghasilkan%')
        and (a.leasing=1 or (a.namasset not like '%leasing%' and (t.namatipe is null or t.namatipe not like '%leasing%')))
    order by t.namatipe, a.tipeasset, a.tanggalperolehan, a.kodeasset";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$groups = array();
$assetToGroup = array();
$assetRows = array();
$assetCodes = array();
while($bar=$res->fetch()){
    $tipeAsset = trim($bar['tipeasset']);
    $namaTipe = trim($bar['namatipe'])!='' ? trim($bar['namatipe']) : $tipeAsset;
    if(!isset($groups[$tipeAsset])){
        $groups[$tipeAsset] = array('nama'=>$namaTipe, 'rows'=>array());
    }

    $tanggal = $bar['tanggalperolehan'];
    $disposal = $bar['tanggaldisposal'];
    $harga = floatval($bar['hargaperolehan']);
    $fisikAwal = ($tanggal<$awalBulan && ($disposal=='0000-00-00' || $disposal>=$awalBulan)) ? 1 : 0;
    $fisikTambah = ($tanggal>=$awalBulan && $tanggal<=$akhirBulan) ? 1 : 0;
    $fisikKurang = ($disposal!='0000-00-00' && $disposal>=$awalBulan && $disposal<=$akhirBulan) ? 1 : 0;
    $fisikAkhir = ($tanggal<=$akhirBulan && ($disposal=='0000-00-00' || $disposal>$akhirBulan)) ? 1 : 0;

    $row = array(
        'kodeasset'=>$bar['kodeasset'],
        'uraian'=>$bar['namasset'],
        'tahun'=>($tanggal!='0000-00-00' ? substr($tanggal,0,4) : ''),
        'manfaat'=>ceil(floatval($bar['jlhblnpenyusutan'])/12),
        'satuan'=>'unit',
        'fisik_awal'=>$fisikAwal,
        'awal'=>($tanggal<$awalBulan ? $harga : 0),
        'fisik_db'=>$fisikTambah,
        'db'=>($tanggal>=$awalBulan && $tanggal<=$akhirBulan ? $harga : 0),
        'kr'=>($fisikKurang ? $harga : 0),
        'fisik_akhir'=>$fisikAkhir,
        'akhir'=>($tanggal<=$akhirBulan && ($disposal=='0000-00-00' || $disposal>$akhirBulan) ? $harga : 0),
        'kelompok'=>$namaTipe,
        'rate'=>floatval($bar['jlhblnpenyusutan'])>0 ? round(12/floatval($bar['jlhblnpenyusutan']), 6) : '',
        'susut_awal'=>0,
        'susut'=>0,
        'susut_akhir'=>0,
        'nilai_buku'=>0,
        'akunsusut'=>$bar['akunsusut']
    );
    $groups[$tipeAsset]['rows'][] = $row;
    $assetToGroup[$bar['kodeasset']] = array($tipeAsset, count($groups[$tipeAsset]['rows'])-1);
    $assetRows[$bar['kodeasset']] = $row;
    $assetCodes[] = $bar['kodeasset'];
}

if(!empty($assetCodes)){
    $chunks = array_chunk($assetCodes, 500);
    foreach($chunks as $chunk){
        $str = "select d.kodeasset, d.noakun,
                sum(case when d.tanggal<'".$awalBulan."' then d.jumlah else 0 end) as susut_awal,
                sum(case when d.tanggal>='".$awalBulan."' and d.tanggal<='".$akhirBulan."' then d.jumlah else 0 end) as susut,
                sum(case when d.tanggal<='".$akhirBulan."' then d.jumlah else 0 end) as susut_akhir
            from ".$dbname.".keu_jurnaldt d force index(kodeasset)
            where d.tanggal<='".$akhirBulan."' and d.kodeasset in(".asetTetapSqlList($chunk).")
            group by d.kodeasset, d.noakun";
        $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if(!isset($assetToGroup[$bar['kodeasset']])) continue;
            list($grp, $idx) = $assetToGroup[$bar['kodeasset']];
            $akunSusut = trim($groups[$grp]['rows'][$idx]['akunsusut']);
            if($akunSusut=='' || $bar['noakun']!=$akunSusut) continue;
            $groups[$grp]['rows'][$idx]['susut_awal'] += floatval($bar['susut_awal']);
            $groups[$grp]['rows'][$idx]['susut'] += floatval($bar['susut']);
            $groups[$grp]['rows'][$idx]['susut_akhir'] += floatval($bar['susut_akhir']);
        }
    }
}

foreach($groups as $grp=>$group){
    foreach($groups[$grp]['rows'] as $idx=>$row){
        $groups[$grp]['rows'][$idx]['nilai_buku'] =
            $groups[$grp]['rows'][$idx]['akhir'] - $groups[$grp]['rows'][$idx]['susut_akhir'];
    }
}

$border = ($tipe=='excel') ? 1 : 0;
$stream = "<style>
    .asetTetap{border-collapse:collapse;font-family:Arial;font-size:11px;min-width:1600px}
    .asetTetap td{border:1px solid #444;padding:3px 4px;height:20px}
    .asetTetap .center{text-align:center;font-weight:bold}
    .asetTetap .head{font-weight:bold;text-align:center;vertical-align:middle;background:#275370;color:#fff}
    .asetTetap .right{text-align:right;white-space:nowrap}
    .asetTetap .title{font-weight:bold;background:#d9e8fb}
    .asetTetap .subtotal td{font-weight:bold}
    .asetTetap .noborder td{border:0}
</style>";
$stream .= "<table class='asetTetap' border='".$border."' cellspacing=0 cellpadding=3>";
$stream .= "<tr class='noborder'><td colspan=20 class=center>".strtoupper($namaOrg)."</td></tr>";
$stream .= "<tr class='noborder'><td colspan=20 class=center>DAFTAR AKTIVA KEBUN</td></tr>";
$stream .= "<tr class='noborder'><td colspan=20 class=center>PER ".$tanggalCaption."</td></tr>";
$stream .= "<tr class='noborder'><td colspan=20>&nbsp;</td></tr>";
$stream .= "<tr>
    <td rowspan=3 class=head style='width:45px'>NO</td>
    <td rowspan=3 class=head style='width:45px'>No. Asset</td>
    <td rowspan=3 class=head style='width:260px'>URAIAN</td>
    <td rowspan=3 class=head style='width:90px'>TAHUN PEROLEHAN</td>
    <td rowspan=3 class=head style='width:110px'>Manfaat Ekonomis</td>
    <td rowspan=3 class=head style='width:70px'>SATUAN</td>
    <td colspan=8 class=head>NILAI PEROLEHAN</td>
    <td rowspan=3 class=head style='width:130px'>Kelompok Aktiva</td>
    <td rowspan=3 class=head style='width:70px'>RATE %</td>
    <td colspan=4 class=head>MUTASI PENYUSUTAN</td>
    <td rowspan=3 class=head style='width:120px'>NILAI BUKU</td>
</tr>";
$stream .= "<tr>
    <td class=head>FISIK</td>
    <td class=head>PEROLEHAN AWAL</td>
    <td class=head>FISIK</td>
    <td colspan=2 class=head>PENAMBAHAN/PENGURANGAN</td>
    <td class=head>FISIK</td>
    <td class=head>PEROLEHAN AKHIR</td>
    <td class=head></td>
    <td class=head>PENYUSUTAN AWAL</td>
    <td colspan=2 class=head>PENYUSUTAN</td>
    <td class=head>PENYUSUTAN AKHIR</td>
</tr>";
$stream .= "<tr>
    <td class=head></td><td class=head></td><td class=head></td>
    <td class=head>Db</td><td class=head>Kr</td>
    <td class=head></td><td class=head></td><td class=head></td>
    <td class=head></td><td class=head></td><td class=head></td><td class=head></td>
</tr>";

$roman = 1;
$grand = array('fisik_awal'=>0,'awal'=>0,'fisik_db'=>0,'db'=>0,'kr'=>0,'fisik_akhir'=>0,'akhir'=>0,'susut_awal'=>0,'susut'=>0,'susut_akhir'=>0,'nilai_buku'=>0);
foreach($groups as $grp=>$group){
    if(empty($group['rows'])) continue;
    $stream .= "<tr class=title><td class=center>".asetTetapRoman($roman++)."</td><td colspan=19>".htmlspecialchars($group['nama'], ENT_QUOTES)."</td></tr>";
    $no = 1;
    $sub = array('fisik_awal'=>0,'awal'=>0,'fisik_db'=>0,'db'=>0,'kr'=>0,'fisik_akhir'=>0,'akhir'=>0,'susut_awal'=>0,'susut'=>0,'susut_akhir'=>0,'nilai_buku'=>0);
    foreach($group['rows'] as $row){
        foreach($sub as $key=>$val){
            $sub[$key] += $row[$key];
            $grand[$key] += $row[$key];
        }
        $stream .= "<tr>
            <td class=center>".$no++."</td>
            <td class=center>".$row['kodeasset']."</td>
            <td>".htmlspecialchars($row['uraian'], ENT_QUOTES)."</td>
            <td class=center>".$row['tahun']."</td>
            <td class=center>".($row['manfaat']>0 ? $row['manfaat'] : '')."</td>
            <td class=center>".$row['satuan']."</td>
            <td class=right>".asetTetapMoney($row['fisik_awal'])."</td>
            <td class=right>".asetTetapMoney($row['awal'])."</td>
            <td class=right>".asetTetapMoney($row['fisik_db'])."</td>
            <td class=right>".asetTetapMoney($row['db'])."</td>
            <td class=right>".asetTetapMoney($row['kr'])."</td>
            <td class=right>".asetTetapMoney($row['fisik_akhir'])."</td>
            <td class=right>".asetTetapMoney($row['akhir'])."</td>
            <td></td>
            <td>".htmlspecialchars($row['kelompok'], ENT_QUOTES)."</td>
            <td class=center>".($row['rate']!=='' ? number_format($row['rate']*100, 2).'%' : '')."</td>
            <td class=right>".asetTetapMoney($row['susut_awal'])."</td>
            <td class=right>".asetTetapMoney($row['susut'], true)."</td>
            <td></td>
            <td class=right>".asetTetapMoney($row['susut_akhir'])."</td>
            <td class=right>".asetTetapMoney($row['nilai_buku'])."</td>
        </tr>";
    }
    $stream .= "<tr class=subtotal>
        <td></td><td>Sub Jumlah</td><td></td><td></td><td></td>
        <td class=right>".asetTetapMoney($sub['fisik_awal'])."</td>
        <td class=right>".asetTetapMoney($sub['awal'])."</td>
        <td class=right>".asetTetapMoney($sub['fisik_db'])."</td>
        <td class=right>".asetTetapMoney($sub['db'])."</td>
        <td class=right>".asetTetapMoney($sub['kr'])."</td>
        <td class=right>".asetTetapMoney($sub['fisik_akhir'])."</td>
        <td class=right>".asetTetapMoney($sub['akhir'])."</td>
        <td></td><td></td><td></td>
        <td class=right>".asetTetapMoney($sub['susut_awal'])."</td>
        <td class=right>".asetTetapMoney($sub['susut'], true)."</td>
        <td></td>
        <td class=right>".asetTetapMoney($sub['susut_akhir'])."</td>
        <td class=right>".asetTetapMoney($sub['nilai_buku'])."</td>
    </tr>";
}

$stream .= "<tr class=subtotal>
    <td></td><td>TOTAL</td><td></td><td></td><td></td>
    <td class=right>".asetTetapMoney($grand['fisik_awal'])."</td>
    <td class=right>".asetTetapMoney($grand['awal'])."</td>
    <td class=right>".asetTetapMoney($grand['fisik_db'])."</td>
    <td class=right>".asetTetapMoney($grand['db'])."</td>
    <td class=right>".asetTetapMoney($grand['kr'])."</td>
    <td class=right>".asetTetapMoney($grand['fisik_akhir'])."</td>
    <td class=right>".asetTetapMoney($grand['akhir'])."</td>
    <td></td><td></td><td></td>
    <td class=right>".asetTetapMoney($grand['susut_awal'])."</td>
    <td class=right>".asetTetapMoney($grand['susut'], true)."</td>
    <td></td>
    <td class=right>".asetTetapMoney($grand['susut_akhir'])."</td>
    <td class=right>".asetTetapMoney($grand['nilai_buku'])."</td>
</tr>";
$stream .= "</table>";

if($tipe=='excel'){
    $stream .= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop = "AsetTetap-".$pt."_".$unit."_".$periode."___".date("YmdHis").".xls";
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
