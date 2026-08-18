<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');
$kodelaporan = 'LABA RUGI V2';

if($pt=='' || $periode==''){
    exit('PT dan periode wajib diisi');
}

$periodeDb = str_replace('-', '', $periode);
$bulan = substr($periodeDb, 4, 2);
$tahun = substr($periodeDb, 0, 4);
$tanggalCaption = strtoupper(numToMonth($bulan, 'I', 'long')).' '.$tahun;
$tanggalKolom = date('M-y', mktime(0,0,0,intval($bulan),1,intval($tahun)));

function labaRugiV2SqlList($arr)
{
    $out = array();
    foreach($arr as $val){
        $out[] = "'".str_replace("'", "''", $val)."'";
    }
    return implode(',', $out);
}

function labaRugiV2OrgScope($owlPDO, $dbname, $root)
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

$kolomSaldo = array();
for($i=1;$i<=intval($bulan);$i++){
    $bln = str_pad($i, 2, '0', STR_PAD_LEFT);
    $kolomSaldo[] = "debet".$bln."-kredit".$bln;
}
$kolomSaldo = implode('+', $kolomSaldo);

if($unit==''){
    $whereOrg = "kodeorg in(".labaRugiV2SqlList(labaRugiV2OrgScope($owlPDO, $dbname, $pt)).")";
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

$rows = array();
$listurut = array();
$str = "select nourut, tipe, noakundari, noakunsampai, noakundisplay, keterangandisplay, keterangandisplay1, posisi
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
        'noakundari'=>$bar['noakundari'],
        'noakunsampai'=>$bar['noakunsampai'],
        'anak'=>$bar['noakundisplay'],
        'posisi'=>($bar['posisi']=='' ? 1 : floatval($bar['posisi'])),
        'nilai'=>0
    );
    $listurut[] = $bar['nourut'];
}

if(empty($rows)){
    exit("Setup mesin laporan '".$kodelaporan."' belum tersedia");
}

$akunToUrut = array();
$akunRules = array();
$str = "select nourut, noakun
    from ".$dbname.".keu_5mesinlaporandt_akun
    where namalaporan='".$kodelaporan."'
    order by nourut asc, noakun asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $akunToUrut[$bar['noakun']][] = $bar['nourut'];
}

foreach($rows as $urut=>$row){
    if($row['tipe']!='Detail') continue;
    if(trim($row['noakundari'])!='' && trim($row['noakunsampai'])!=''){
        $akunRules[$urut][] = trim($row['noakundari']).'..'.trim($row['noakunsampai']);
    }
}

$str = "select noakun, sum(".$kolomSaldo.") as jumlah
    from ".$dbname.".keu_saldobulanan
    where periode='".$periodeDb."' and ".$whereOrg."
    group by noakun";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $tujuan = array();
    if(isset($akunToUrut[$bar['noakun']])){
        foreach($akunToUrut[$bar['noakun']] as $urut){
            $tujuan[$urut] = $urut;
        }
    }
    foreach($akunRules as $urut=>$rules){
        foreach($rules as $rule){
            if(strpos($rule, '..')!==false){
                $batas = explode('..', $rule);
                if($bar['noakun']>=$batas[0] && $bar['noakun']<=$batas[1]){
                    $tujuan[$urut] = $urut;
                }
            }
        }
    }
    if(empty($tujuan)) continue;
    foreach($tujuan as $urut){
        $posisi = isset($rows[$urut]['posisi']) ? $rows[$urut]['posisi'] : 1;
        $rows[$urut]['nilai'] += $bar['jumlah'] * $posisi;
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
    .labarugiV2{border-collapse:collapse;font-family:Arial;font-size:12px;min-width:760px}
    .labarugiV2 td{border:1px solid #c8c8c8;padding:3px 5px;height:18px}
    .labarugiV2 .center{text-align:center;font-weight:bold}
    .labarugiV2 .section{font-weight:bold}
    .labarugiV2 .detailLabel{padding-left:60px}
    .labarugiV2 .total td{font-weight:bold}
    .labarugiV2 .amount{text-align:right;white-space:nowrap}
    .labarugiV2 .total .amount{border-top:2px solid #000}
    .labarugiV2 .grand .amount{border-top:3px double #000}
    .labarugiV2 .note{text-align:center;font-style:italic}
</style>";
$stream .= "<table class='labarugiV2' border='".$border."' cellspacing=0 cellpadding=3>";
$stream .= "<tr><td colspan=5 class=center>".strtoupper($namaOrg)."</td></tr>";
$stream .= "<tr><td colspan=5 class=center>LAPORAN LABA RUGI</td></tr>";
$stream .= "<tr><td colspan=5 class=center>".$tanggalCaption."</td></tr>";
$stream .= "<tr><td colspan=5>&nbsp;</td></tr>";
$stream .= "<tr><td colspan=3></td><td class=note>Catatan</td><td class=center>".$tanggalKolom."</td></tr>";

foreach($listurut as $urut){
    $row = $rows[$urut];
    $ket = htmlspecialchars($row['keterangan'], ENT_QUOTES);
    $nilai = number_format($row['nilai']);
    $isGrand = (in_array($urut, array('3999','5999','7999','8999'))) ? ' grand' : '';

    if($row['tipe']=='Header'){
        if(trim($row['keterangan'])=='-'){
            $stream .= "<tr><td colspan=5>&nbsp;</td></tr>";
        }else{
            $stream .= "<tr><td class=section colspan=3>".$ket."</td><td></td><td></td></tr>";
        }
    }else if($row['tipe']=='Detail'){
        $stream .= "<tr>
            <td style='width:45px'></td>
            <td class=detailLabel style='width:360px' colspan=2>".$ket."</td>
            <td style='width:95px'></td>
            <td style='width:150px' class=amount>".$nilai."</td>
        </tr>";
    }else if($row['tipe']=='Total'){
        if(trim($row['keterangan'])=='-'){
            continue;
        }
        $stream .= "<tr class='total".$isGrand."'>
            <td class=section colspan=3>".$ket."</td>
            <td></td>
            <td class=amount>".$nilai."</td>
        </tr>";
        if(in_array($urut, array('3999','5999','6999','7999','8999'))){
            $stream .= "<tr><td colspan=5>&nbsp;</td></tr>";
        }
    }
}

$stream .= "</table>";

if($tipe=='excel'){
    $stream .= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop = "LabaRugiV2-".$pt."_".$unit."_".$periode."___".date("YmdHis").".xls";
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
