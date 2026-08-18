<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');
$kodelaporan = 'NERACA V2';

if($pt=='' || $periode==''){
    exit('PT dan periode wajib diisi');
}

$periodeDb = str_replace('-', '', $periode);
$bulan = substr($periodeDb, 4, 2);
$tahun = substr($periodeDb, 0, 4);
$akhirBulan = date('t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun)));
$kolomSaldo = 'awal'.$bulan.'+debet'.$bulan.'-kredit'.$bulan;
$tanggalCaption = $akhirBulan.' '.strtoupper(numToMonth($bulan, 'I', 'long')).' '.$tahun;
$tanggalKolom = $akhirBulan.'-'.date('M-y', mktime(0,0,0,intval($bulan),1,intval($tahun)));

function neracaV2SqlList($arr)
{
    $out = array();
    foreach($arr as $val){
        $out[] = "'".str_replace("'", "''", $val)."'";
    }
    return implode(',', $out);
}

function neracaV2OrgScope($owlPDO, $dbname, $root)
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
    $whereOrg = "kodeorg in(".neracaV2SqlList(neracaV2OrgScope($owlPDO, $dbname, $pt)).")";
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
$str = "select nourut, tipe, noakundari, noakunsampai, noakundisplay, keterangandisplay, keterangandisplay1, posisi, tipeunit
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
        'tipeunit'=>trim($bar['tipeunit']),
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
            }else{
                if($bar['noakun']==$rule || substr($bar['noakun'], 0, strlen($rule))==$rule){
                    $tujuan[$urut] = $urut;
                }
            }
        }
    }
    if(empty($tujuan)) continue;
    foreach($tujuan as $urut){
        if(in_array(strtoupper($rows[$urut]['tipeunit']), array('PREV_YEAR','YTD_PREV_MONTH','MONTH_CURRENT'))){
            continue;
        }
        $posisi = isset($rows[$urut]['posisi']) ? $rows[$urut]['posisi'] : 1;
        $rows[$urut]['nilai'] += $bar['jumlah'] * $posisi;
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

foreach($listurut as $urut){
    if($rows[$urut]['tipe']!='Total') continue;
    $children = explode(',', $rows[$urut]['anak']);
    if(count($children)==2 && is_numeric(trim($children[0])) && is_numeric(trim($children[1]))){
        $awalRange = trim($children[0]);
        $akhirRange = trim($children[1]);
        $children = array();
        foreach($listurut as $urutRange){
            if($urutRange>=$awalRange && $urutRange<=$akhirRange && $urutRange!=$urut && $rows[$urutRange]['tipe']!='Header'){
                $children[] = $urutRange;
            }
        }
    }
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
    .neracaV2{border-collapse:collapse;font-family:Arial;font-size:12px;width:100%}
    .neracaV2 td{border:1px solid #c8c8c8;padding:3px 5px;height:18px}
    .neracaV2 .center{text-align:center;font-weight:bold}
    .neracaV2 .bar td{border-top:2px solid #000;border-bottom:2px solid #000;font-weight:bold;text-align:center}
    .neracaV2 .section{font-weight:bold}
    .neracaV2 .total td{font-weight:bold}
    .neracaV2 .amount{border-top:2px solid #000;text-align:right;white-space:nowrap}
    .neracaV2 .grand .amount{border-top:3px double #000}
    .neracaV2 .note{text-align:center;font-style:italic}
</style>";
$stream .= "<table class='neracaV2' border='".$border."' cellspacing=0 cellpadding=3>";
$stream .= "<tr><td colspan=6 class=center>".strtoupper($namaOrg)."</td></tr>";
$stream .= "<tr><td colspan=6 class=center>N E R A C A</td></tr>";
$stream .= "<tr><td colspan=6 class=center>PER ".$tanggalCaption."</td></tr>";
$stream .= "<tr><td colspan=6>&nbsp;</td></tr>";

foreach($listurut as $urut){
    $row = $rows[$urut];
    $ket = htmlspecialchars($row['keterangan'], ENT_QUOTES);
    $nilai = number_format($row['nilai']);
    $isGrand = (substr($urut, -3)=='999' || substr($urut, -4)=='9999') ? ' grand' : '';

    if($row['tipe']=='Header'){
        if(substr($urut, -3)=='000' || trim($row['keterangan'])=='-'){
            if(trim($row['keterangan'])!='-'){
                $stream .= "<tr class='bar'><td colspan=4 style='width:80%'>".$ket."</td><td class=note>Catatan</td><td>".$tanggalKolom."</td></tr>";
            }else{
                $stream .= "<tr><td colspan=6>&nbsp;</td></tr>";
            }
        }else{
            $stream .= "<tr><td class=section colspan=2>".$ket."</td><td colspan=4></td></tr>";
        }
    }else if($row['tipe']=='Detail'){
        $stream .= "<tr>
            <td style='width:2%'></td>
            <td style='width:23%'></td>
            <td style='width:40%'>".$ket."</td>
            <td style='width:15%'></td>
            <td style='width:5%; text-align:center;'></td>
            <td style='width:15%; text-align:right'>".$nilai."</td>
        </tr>";
    }else if($row['tipe']=='Total'){
        $stream .= "<tr class='total".$isGrand."'>
            <td style='width:2%'></td>
            <td style='width:23%'></td>
            <td style='width:40%'></td>
            <td style='width:15%'>".$ket."</td>
            <td style='width:5%'></td>
            <td class=amount style='width:15%'>".$nilai."</td>
        </tr>";
        $stream .= "<tr><td colspan=6>&nbsp;</td></tr>";
    }
}

$stream .= "</table>";

if($tipe=='excel'){
    $stream .= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop = "NeracaV2-".$pt."_".$unit."_".$periode."___".date("YmdHis").".xls";
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
