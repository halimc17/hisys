<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');
$kodelaporan = 'LAPORAN ARUS KAS';

if($pt=='' || $periode==''){
    exit('PT dan periode wajib diisi');
}

$periodeDb = str_replace('-', '', $periode);
$bulan = substr($periodeDb, 4, 2);
$tahun = substr($periodeDb, 0, 4);
$akhirBulan = date('t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun)));
$tanggalCaption = $akhirBulan.' '.strtoupper(numToMonth($bulan, 'I', 'long')).' '.$tahun;
$tanggalKolom = date('d-M-y', mktime(0,0,0,intval($bulan),intval($akhirBulan),intval($tahun)));

function arusKasV2SqlList($arr)
{
    $out = array();
    foreach($arr as $val){
        $out[] = "'".str_replace("'", "''", $val)."'";
    }
    return implode(',', $out);
}

function arusKasV2OrgScope($owlPDO, $dbname, $root)
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

$kolomMutasi = array();
for($i=1;$i<=intval($bulan);$i++){
    $bln = str_pad($i, 2, '0', STR_PAD_LEFT);
    $kolomMutasi[] = "debet".$bln."-kredit".$bln;
}
$kolomMutasi = implode('+', $kolomMutasi);
$kolomAkhir = "awal01+".$kolomMutasi;

if($unit==''){
    $whereOrg = "kodeorg in(".arusKasV2SqlList(arusKasV2OrgScope($owlPDO, $dbname, $pt)).")";
    $unitx = $pt;
}else{
    $whereOrg = "kodeorg='".$unit."'";
    $unitx = $unit;
}

$namaOrg = $unitx;
$str = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$unitx."'";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $namaOrg = $bar['namaorganisasi'];
}

function hitungLabaRugiV2($dbname, $owlPDO, $whereOrg, $periodeDb, $bulan)
{
    $kolomSaldo = array();
    for($i=1;$i<=intval($bulan);$i++){
        $bln = str_pad($i, 2, '0', STR_PAD_LEFT);
        $kolomSaldo[] = "debet".$bln."-kredit".$bln;
    }
    $kolomSaldo = implode('+', $kolomSaldo);
    $rows = array();
    $listurut = array();
    $str = "select nourut, tipe, noakundisplay, posisi
        from ".$dbname.".keu_5mesinlaporandt
        where namalaporan='LABA RUGI V2' and tampil=1
        order by nourut asc";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $rows[$bar['nourut']] = array(
            'tipe'=>$bar['tipe'],
            'anak'=>$bar['noakundisplay'],
            'posisi'=>($bar['posisi']=='' ? 1 : floatval($bar['posisi'])),
            'nilai'=>0
        );
        $listurut[] = $bar['nourut'];
    }
    if(empty($rows)){
        return 0;
    }

    $akunToUrut = array();
    $str = "select nourut, noakun from ".$dbname.".keu_5mesinlaporandt_akun
        where namalaporan='LABA RUGI V2'
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
            $rows[$urut]['nilai'] += $bar['jumlah'] * $rows[$urut]['posisi'];
        }
    }

    foreach($listurut as $urut){
        if($rows[$urut]['tipe']!='Total') continue;
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
    return isset($rows['8999']) ? $rows['8999']['nilai'] : 0;
}

$rows = array();
$listurut = array();
$str = "select nourut, tipe, noakundisplay, keterangandisplay, keterangandisplay1, posisi, tipeunit
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
        'nilai'=>0
    );
    $listurut[] = $bar['nourut'];
}

if(empty($rows)){
    exit("Setup mesin laporan '".$kodelaporan."' belum tersedia");
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

$labaRugiTahunBerjalan = null;
$str = "select noakun, sum(awal01) as saldoawal, sum(".$kolomAkhir.") as saldoakhir, sum(".$kolomMutasi.") as mutasi
    from ".$dbname.".keu_saldobulanan
    where periode='".$periodeDb."' and ".$whereOrg."
    group by noakun";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    if(!isset($akunToUrut[$bar['noakun']])) continue;
    foreach($akunToUrut[$bar['noakun']] as $urut){
        $mode = strtoupper($rows[$urut]['tipeunit']);
        if($mode=='PL'){
            $rows[$urut]['nilai'] += $bar['mutasi'] * $rows[$urut]['posisi'];
        }else if($mode=='BAL'){
            $rows[$urut]['nilai'] += ($bar['saldoakhir'] - $bar['saldoawal']) * $rows[$urut]['posisi'];
        }else if($mode=='CASH_AWAL'){
            $rows[$urut]['nilai'] += $bar['saldoawal'];
        }else if($mode=='CASH_AKHIR'){
            $rows[$urut]['nilai'] += $bar['saldoakhir'];
        }
    }
}

foreach($listurut as $urut){
    if($rows[$urut]['tipe']=='Detail' && strtoupper($rows[$urut]['tipeunit'])=='LR_TOTAL'){
        if($labaRugiTahunBerjalan===null){
            $labaRugiTahunBerjalan = hitungLabaRugiV2($dbname, $owlPDO, $whereOrg, $periodeDb, $bulan);
        }
        $rows[$urut]['nilai'] = $labaRugiTahunBerjalan * $rows[$urut]['posisi'];
    }
}

foreach($listurut as $urut){
    if($rows[$urut]['tipe']!='Total') continue;
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
    .aruskasV2{border-collapse:collapse;font-family:Arial;font-size:12px;min-width:760px}
    .aruskasV2 td{border:1px solid #c8c8c8;padding:3px 5px;height:18px}
    .aruskasV2 .center{text-align:center;font-weight:bold}
    .aruskasV2 .section{font-weight:bold}
    .aruskasV2 .subsection{font-weight:bold;text-decoration:underline}
    .aruskasV2 .detailLabel{padding-left:55px}
    .aruskasV2 .total td{font-weight:bold}
    .aruskasV2 .amount{text-align:right;white-space:nowrap}
    .aruskasV2 .total .amount{border-top:2px solid #000}
    .aruskasV2 .grand .amount{border-top:3px double #000}
    .aruskasV2 .note{text-align:center}
</style>";
$stream .= "<table class='aruskasV2' border='".$border."' cellspacing=0 cellpadding=3>";
$stream .= "<tr><td colspan=5 class=center>".strtoupper($namaOrg)."</td></tr>";
$stream .= "<tr><td colspan=5 class=center>LAPORAN ARUS KAS</td></tr>";
$stream .= "<tr><td colspan=5 class=center>PER ".$tanggalCaption."</td></tr>";
$stream .= "<tr><td colspan=5>&nbsp;</td></tr>";
$stream .= "<tr><td></td><td class=center colspan=2>U r a i a n</td><td></td><td class=center>".$tanggalKolom."<br>(Rp)</td></tr>";

foreach($listurut as $urut){
    $row = $rows[$urut];
    $ket = htmlspecialchars($row['keterangan'], ENT_QUOTES);
    $nilai = number_format($row['nilai']);
    $isGrand = (in_array($urut, array('1499','2999','3999','4999','5101','5102'))) ? ' grand' : '';

    if($row['tipe']=='Header'){
        if(trim($row['keterangan'])=='-'){
            $stream .= "<tr><td colspan=5>&nbsp;</td></tr>";
        }else if(in_array($urut, array('1200','1300'))){
            $stream .= "<tr><td></td><td class=subsection colspan=3>".$ket."</td><td></td></tr>";
        }else{
            $stream .= "<tr><td class=section colspan=4>".$ket."</td><td></td></tr>";
        }
    }else if($row['tipe']=='Detail'){
        $stream .= "<tr>
            <td style='width:45px'></td>
            <td class=detailLabel style='width:380px' colspan=2>".$ket."</td>
            <td style='width:80px'></td>
            <td style='width:160px' class=amount>".$nilai."</td>
        </tr>";
    }else if($row['tipe']=='Total'){
        if(trim($row['keterangan'])=='-'){
            continue;
        }
        $stream .= "<tr class='total".$isGrand."'>
            <td class=section colspan=4>".$ket."</td>
            <td class=amount>".$nilai."</td>
        </tr>";
    }
}

$stream .= "</table>";

if($tipe=='excel'){
    $stream .= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop = "LaporanArusKas-".$pt."_".$unit."_".$periode."___".date("YmdHis").".xls";
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
