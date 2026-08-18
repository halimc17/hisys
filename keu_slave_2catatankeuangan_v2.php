<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');
$kodelaporan = 'CATATAN KEUANGAN';

if($pt=='' || $periode==''){
    exit('PT dan periode wajib diisi');
}

$periodeDb = str_replace('-', '', $periode);
$bulan = substr($periodeDb, 4, 2);
$tahun = substr($periodeDb, 0, 4);
$akhirBulan = date('t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun)));
$kolomSaldo = 'awal'.$bulan.'+debet'.$bulan.'-kredit'.$bulan;
$tanggalCaption = $akhirBulan.' '.strtoupper(numToMonth($bulan, 'I', 'long')).' '.$tahun;

function catatanKeuSqlList($arr)
{
    $out = array();
    foreach($arr as $val){
        $out[] = "'".str_replace("'", "''", $val)."'";
    }
    return implode(',', $out);
}

function catatanKeuOrgScope($owlPDO, $dbname, $root)
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

if($unit==''){
    $orgScope = catatanKeuOrgScope($owlPDO, $dbname, $pt);
    $whereOrg = "kodeorg in(".catatanKeuSqlList($orgScope).")";
    $whereProjectOrg = "p.kodeorg in(".catatanKeuSqlList($orgScope).")";
    $unitx = $pt;
}else{
    $orgScope = array($unit);
    $whereOrg = "kodeorg='".$unit."'";
    $whereProjectOrg = "p.kodeorg='".$unit."'";
    $unitx = $unit;
}

$namaOrg = $unitx;
$str = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$unitx."'";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $namaOrg = $bar['namaorganisasi'];
}

$rows = array();
$listurut = array();
$str = "select nourut, tipe, noakundisplay, keterangandisplay, keterangandisplay1, posisi, tipeunit, keydata
    from ".$dbname.".keu_5mesinlaporandt
    where namalaporan='".$kodelaporan."' and tampil=1
    order by nourut asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $ket = ($_SESSION['language']=='EN' && trim($bar['keterangandisplay1'])!='')
        ? $bar['keterangandisplay1'] : $bar['keterangandisplay'];
    $rows[$bar['nourut']] = array(
        'nourut'=>$bar['nourut'],
        'tipe'=>$bar['tipe'],
        'keterangan'=>$ket,
        'anak'=>$bar['noakundisplay'],
        'posisi'=>($bar['posisi']=='' ? 1 : floatval($bar['posisi'])),
        'tipeunit'=>trim($bar['tipeunit']),
        'keydata'=>trim($bar['keydata']),
        'nilai'=>0
    );
    $listurut[] = $bar['nourut'];
}

if(empty($rows)){
    exit("Setup mesin laporan '".$kodelaporan."' belum tersedia");
}

$allowedUnitAccounts = array();
if($unit==''){
    $str = "select distinct au.noakun
        from ".$dbname.".keu_5akununit au
        where au.kodeunit in(".catatanKeuSqlList($orgScope).")";
}else{
    $str = "select distinct noakun
        from ".$dbname.".keu_5akununit
        where kodeunit='".$unit."'";
}
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $allowedUnitAccounts[$bar['noakun']] = true;
}

$akunToUrut = array();
$str = "select nourut, noakun
    from ".$dbname.".keu_5mesinlaporandt_akun
    where namalaporan='".$kodelaporan."'
    order by nourut asc, noakun asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $akunToUrut[$bar['noakun']][] = $bar['nourut'];
}

$str = "select noakun, sum(".$kolomSaldo.") as jumlah
    from ".$dbname.".keu_saldobulanan
    where periode='".$periodeDb."' and ".$whereOrg."
    group by noakun";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    if(!isset($akunToUrut[$bar['noakun']])) continue;
    foreach($akunToUrut[$bar['noakun']] as $urut){
        if(in_array(strtoupper($rows[$urut]['tipeunit']), array('PREV_YEAR','YTD_PREV_MONTH','MONTH_CURRENT'))){
            continue;
        }
        if(strtoupper($rows[$urut]['tipeunit'])=='COA_UNIT' && !isset($allowedUnitAccounts[$bar['noakun']])){
            continue;
        }
        $rows[$urut]['nilai'] += $bar['jumlah'] * $rows[$urut]['posisi'];
    }
}

$specialExpr = array();
$prevYearPeriod = ($tahun-1).'12';
$specialExpr['PREV_YEAR'] = array('periode'=>$prevYearPeriod, 'expr'=>'awal12+debet12-kredit12');
if(intval($bulan)>1){
    $arrPrevMonth = array();
    for($i=1;$i<intval($bulan);$i++){
        $bln = str_pad($i, 2, '0', STR_PAD_LEFT);
        $arrPrevMonth[] = 'debet'.$bln.'-kredit'.$bln;
    }
    $specialExpr['YTD_PREV_MONTH'] = array('periode'=>$periodeDb, 'expr'=>implode('+', $arrPrevMonth));
}else{
    $specialExpr['YTD_PREV_MONTH'] = array('periode'=>$periodeDb, 'expr'=>'0');
}
$specialExpr['MONTH_CURRENT'] = array('periode'=>$periodeDb, 'expr'=>'debet'.$bulan.'-kredit'.$bulan);

foreach($specialExpr as $mode=>$cfg){
    if($cfg['expr']=='0') continue;
    $str = "select noakun, sum(".$cfg['expr'].") as jumlah
        from ".$dbname.".keu_saldobulanan
        where periode='".$cfg['periode']."' and ".$whereOrg."
        group by noakun";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        if(!isset($akunToUrut[$bar['noakun']])) continue;
        foreach($akunToUrut[$bar['noakun']] as $urut){
            if(strtoupper($rows[$urut]['tipeunit'])!=$mode) continue;
            $rows[$urut]['nilai'] += $bar['jumlah'] * $rows[$urut]['posisi'];
        }
    }
}

$cipItems = array();
$cipTotal = 0;
$str = "select p.kode, p.nama, p.tipe, coalesce(tdetail.akunak, tumum.akunak) as akunak,
        group_concat(distinct d.noakun order by d.noakun separator ', ') as akunhutang,
        sum(d.jumlah) as saldo
    from ".$dbname.".project p
    left join ".$dbname.".sdm_5tipeasset tdetail on tdetail.kodetipe=substr(p.kode,4,2)
    left join ".$dbname.".sdm_5tipeasset tumum on tumum.kodetipe=left(p.kode,2)
    join ".$dbname.".keu_jurnaldt d on d.kodeasset=p.kode
    where d.tanggal<='".$tahun."-".$bulan."-".$akhirBulan."'
        and ".$whereProjectOrg."
        and left(d.noakun,1)='2'
        and (coalesce(tdetail.akunak, tumum.akunak) is null or coalesce(tdetail.akunak, tumum.akunak)='' or exists(
            select 1 from ".$dbname.".keu_jurnaldt dx
            where dx.kodeasset=p.kode and dx.noakun=coalesce(tdetail.akunak, tumum.akunak)
                and dx.tanggal<='".$tahun."-".$bulan."-".$akhirBulan."'
        ))
    group by p.kode, p.nama, p.tipe, coalesce(tdetail.akunak, tumum.akunak)
    having abs(saldo)>0
    order by p.kode";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $nilai = $bar['saldo'] * -1;
    $cipTotal += $nilai;
    $cipItems[] = array(
        'kode'=>$bar['kode'],
        'nama'=>$bar['nama'],
        'akunak'=>$bar['akunak'],
        'akunhutang'=>$bar['akunhutang'],
        'nilai'=>$nilai
    );
}
foreach($rows as $urut=>$row){
    if(strtoupper($row['tipeunit'])=='CIP_SECTION'){
        $rows[$urut]['nilai'] = $cipTotal;
    }
}

foreach($listurut as $urut){
    if(trim($rows[$urut]['anak'])=='') continue;
    $children = explode(',', $rows[$urut]['anak']);
    foreach($children as $child){
        $child = trim($child);
        if($child=='') continue;
        $sign = 1;
        if(substr($child,0,1)=='-'){
            $sign = -1;
            $child = substr($child,1);
        }
        if(isset($rows[$child])){
            $rows[$urut]['nilai'] += $rows[$child]['nilai'] * $sign;
        }
    }
}

$border = ($tipe=='excel') ? 1 : 0;
$stream = "<style>
    .catatanKeuV2{border-collapse:collapse;font-family:Arial;font-size:12px;min-width:760px}
    .catatanKeuV2 td{border:1px solid #c8c8c8;padding:3px 5px;height:18px}
    .catatanKeuV2 .center{text-align:center;font-weight:bold}
    .catatanKeuV2 .noteNo{width:45px;text-align:center;font-weight:bold}
    .catatanKeuV2 .title{font-weight:bold}
    .catatanKeuV2 .desc{font-style:italic;color:#333}
    .catatanKeuV2 .amount{text-align:right;white-space:nowrap;width:150px}
    .catatanKeuV2 .total td{font-weight:bold}
    .catatanKeuV2 .total .amount{border-top:2px solid #000}
</style>";
$stream .= "<table class='catatanKeuV2' border='".$border."' cellspacing=0 cellpadding=3>";
$stream .= "<tr><td colspan=4 class=center>".strtoupper($namaOrg)."</td></tr>";
$stream .= "<tr><td colspan=4 class=center>CATATAN ATAS LAPORAN KEUANGAN</td></tr>";
$stream .= "<tr><td colspan=4 class=center>PER ".$tanggalCaption."</td></tr>";
$stream .= "<tr><td colspan=4>&nbsp;</td></tr>";

foreach($listurut as $urut){
    $row = $rows[$urut];
    $ket = htmlspecialchars($row['keterangan'], ENT_QUOTES);
    $nilai = number_format($row['nilai']);
    $tipeunit = strtoupper($row['tipeunit']);

    if($row['tipe']=='Header'){
        if($tipeunit=='NOTE'){
            $stream .= "<tr><td></td><td colspan=3 class=desc>".$ket."</td></tr>";
        }else if($tipeunit=='SUB'){
            $stream .= "<tr><td></td><td></td><td class=title>".$ket."</td><td></td></tr>";
        }else if($tipeunit=='SUBTOTAL'){
            $stream .= "<tr><td></td><td colspan=2 class=title>".$ket."</td><td class=amount>".$nilai."</td></tr>";
        }else{
            $stream .= "<tr><td class=noteNo>".intval($urut)."</td><td colspan=2 class=title>".$ket."</td><td class=amount>".$nilai."</td></tr>";
            if($tipeunit=='CIP_SECTION'){
                foreach($cipItems as $item){
                    $stream .= "<tr><td></td><td></td><td>".htmlspecialchars($item['kode'].' - '.$item['nama'], ENT_QUOTES)."</td><td class=amount>".number_format($item['nilai'])."</td></tr>";
                }
            }
        }
    }else if($row['tipe']=='Detail'){
        if($tipeunit=='CIP'){
            continue;
        }
        $stream .= "<tr><td></td><td></td><td>".$ket."</td><td class=amount>".$nilai."</td></tr>";
    }else if($row['tipe']=='Total'){
        if(trim($row['keterangan'])=='-'){
            continue;
        }
        $stream .= "<tr class=total><td></td><td colspan=2>".$ket."</td><td class=amount>".$nilai."</td></tr>";
        $stream .= "<tr><td colspan=4>&nbsp;</td></tr>";
    }
}

$stream .= "</table>";

if($tipe=='excel'){
    $stream .= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop = "CatatanKeuangan-".$pt."_".$unit."_".$periode."___".date("YmdHis").".xls";
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
