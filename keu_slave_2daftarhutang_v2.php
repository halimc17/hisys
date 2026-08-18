<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');
$kodelaporan = 'DAFTAR HUTANG';

if($pt=='' || $periode==''){
    exit('PT dan periode wajib diisi');
}

$periodeDb = str_replace('-', '', $periode);
$bulan = substr($periodeDb, 4, 2);
$tahun = substr($periodeDb, 0, 4);
$awalBulan = $tahun.'-'.$bulan.'-01';
$akhirBulan = date('Y-m-t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun)));
$tanggalCaption = date('t', strtotime($akhirBulan)).' '.strtoupper(numToMonth($bulan, 'I', 'long')).' '.$tahun;

function dhSqlList($arr)
{
    $out = array();
    foreach($arr as $val){
        $out[] = "'".str_replace("'", "''", $val)."'";
    }
    return implode(',', $out);
}

function dhOrgScope($owlPDO, $dbname, $root)
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

function dhMoney($nilai)
{
    if(abs($nilai)<0.005){
        return '-';
    }
    return number_format($nilai, 0);
}

function dhDate($tgl)
{
    if($tgl=='' || $tgl=='0000-00-00'){
        return '';
    }
    return tanggalnormal($tgl);
}

if($unit==''){
    $orgScope = dhOrgScope($owlPDO, $dbname, $pt);
    $unitx = $pt;
}else{
    $orgScope = array($unit);
    $unitx = $unit;
}
$whereOrgList = dhSqlList($orgScope);

$namaOrg = $unitx;
$str = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".str_replace("'", "''", $unitx)."'";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $namaOrg = $bar['namaorganisasi'];
}

$groups = array();
$details = array();
$detailByGroup = array();
$str = "select nourut, tipe, keterangandisplay, keterangandisplay1, induk, keydata, tampil
    from ".$dbname.".keu_5mesinlaporandt
    where namalaporan='".$kodelaporan."' and tampil=1
    order by nourut asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $ket = ($_SESSION['language']=='EN' && trim($bar['keterangandisplay1'])!='')
        ? $bar['keterangandisplay1'] : $bar['keterangandisplay'];
    if($bar['tipe']=='Header'){
        $groups[$bar['nourut']] = array('nourut'=>$bar['nourut'], 'keterangan'=>$ket);
        $detailByGroup[$bar['nourut']] = array();
    }else{
        $details[$bar['nourut']] = array(
            'nourut'=>$bar['nourut'],
            'induk'=>$bar['induk'],
            'keterangan'=>$ket,
            'akun'=>array()
        );
        if(!isset($detailByGroup[$bar['induk']])){
            $detailByGroup[$bar['induk']] = array();
        }
        $detailByGroup[$bar['induk']][] = $bar['nourut'];
    }
}

if(empty($groups)){
    exit("Setup mesin laporan '".$kodelaporan."' belum tersedia");
}

$akunList = array();
$akunName = array();
$str = "select ma.nourut, ma.noakun, coalesce(a.namaakun, ma.keterangan) as namaakun
    from ".$dbname.".keu_5mesinlaporandt_akun ma
    left join ".$dbname.".keu_5akun a on a.noakun=ma.noakun
    where ma.namalaporan='".$kodelaporan."'
    order by ma.nourut asc, ma.noakun asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    if(isset($details[$bar['nourut']])){
        $details[$bar['nourut']]['akun'][] = $bar['noakun'];
        $akunList[$bar['noakun']] = $bar['noakun'];
        $akunName[$bar['noakun']] = $bar['namaakun'];
    }
}

$tanggalSupplier = array();
if(!empty($akunList)){
    $str = "select h.noakun, h.kodesupplier,
            coalesce(nullif(trim(s.namasupplier),''), h.kodesupplier) as namapihak,
            max(h.tanggal) as tanggal_tagihan,
            max(k.tanggal) as tanggal_bayar
        from ".$dbname.".keu_tagihanht h
        left join ".$dbname.".log_5supplier s on s.supplierid=h.kodesupplier
        left join ".$dbname.".keu_kasbankdtht_vw k on k.keterangan1=h.noinvoice
            and k.kodesupplier=h.kodesupplier
            and k.tanggal<='".$akhirBulan."'
            and k.posting=1
        where h.kodesupplier<>'' and h.noakun in(".dhSqlList($akunList).")
            and h.tanggal<='".$akhirBulan."'
            and (h.kodeorg in(".$whereOrgList.") or h.unit in(".$whereOrgList."))
        group by h.noakun, namapihak";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $tanggal = trim($bar['tanggal_bayar'])!='' ? $bar['tanggal_bayar'] : $bar['tanggal_tagihan'];
        $tanggalSupplier[$bar['noakun']][trim($bar['namapihak'])] = $tanggal;
    }
}

$saldoRows = array();
if(!empty($akunList)){
    $str = "select x.noakun, x.tipepihak, x.kodepihak, x.namapihak,
            max(case when x.jumlah>0 then x.tanggal else null end) as tanggal,
            max(case when x.jumlah>0 then left(x.tanggal,7) else null end) as periode,
            sum(x.jumlah) as saldo
        from (
            select d.noakun, d.tanggal, d.jumlah,
                case
                    when d.kodesupplier is not null and d.kodesupplier<>'' and cast(d.kodesupplier as unsigned)<>0 then 'SUPPLIER'
                    when d.nik is not null and d.nik<>'' and cast(d.nik as unsigned)<>0 then 'KARYAWAN'
                    when d.kodecustomer is not null and d.kodecustomer<>'' and cast(d.kodecustomer as unsigned)<>0 then 'CUSTOMER'
                    else 'LAINNYA'
                end as tipepihak,
                case
                    when d.kodesupplier is not null and d.kodesupplier<>'' and cast(d.kodesupplier as unsigned)<>0 then d.kodesupplier
                    when d.nik is not null and d.nik<>'' and cast(d.nik as unsigned)<>0 then d.nik
                    when d.kodecustomer is not null and d.kodecustomer<>'' and cast(d.kodecustomer as unsigned)<>0 then d.kodecustomer
                    else ''
                end as kodepihak,
                case
                    when d.kodesupplier is not null and d.kodesupplier<>'' and cast(d.kodesupplier as unsigned)<>0 then coalesce(nullif(trim(s.namasupplier),''), d.kodesupplier)
                    when d.nik is not null and d.nik<>'' and cast(d.nik as unsigned)<>0 then coalesce(nullif(trim(k.namakaryawan),''), d.nik)
                    when d.kodecustomer is not null and d.kodecustomer<>'' and cast(d.kodecustomer as unsigned)<>0 then coalesce(nullif(trim(c.namacustomer),''), d.kodecustomer)
                    else 'Tanpa Pihak'
                end as namapihak
            from ".$dbname.".keu_jurnaldt d
            left join ".$dbname.".pmn_4customer c on c.kodecustomer=d.kodecustomer
            left join ".$dbname.".log_5supplier s on s.supplierid=d.kodesupplier
            left join ".$dbname.".datakaryawan k on k.nik=d.nik or lpad(k.karyawanid,10,'0')=d.nik
            where d.tanggal<='".$akhirBulan."' and d.kodeorg in(".$whereOrgList.")
                and d.noakun in(".dhSqlList($akunList).")
        ) x
        group by x.noakun, x.tipepihak, x.namapihak
        having abs(saldo)>0.005
        order by x.noakun, x.tipepihak, x.namapihak";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $nama = trim($bar['namapihak']);
        if($nama=='') $nama = trim($bar['kodepihak']);
        if($nama=='') $nama = isset($akunName[$bar['noakun']]) ? $akunName[$bar['noakun']] : $bar['noakun'];
        if(isset($tanggalSupplier[$bar['noakun']][$nama])){
            $bar['tanggal'] = $tanggalSupplier[$bar['noakun']][$nama];
            $bar['periode'] = substr($bar['tanggal'], 0, 7);
        }

        if(!isset($saldoRows[$bar['noakun']])){
            $saldoRows[$bar['noakun']] = array();
        }
        $saldoRows[$bar['noakun']][] = array(
            'nama'=>$nama,
            'akun'=>$bar['noakun'],
            'periode'=>$bar['periode'],
            'tanggal'=>$bar['tanggal'],
            'saldo'=>floatval($bar['saldo']) * -1
        );
    }
}

$border = ($tipe=='excel') ? 1 : 0;
$stream = "<style>
    .dh{border-collapse:collapse;font-family:Arial;font-size:12px;min-width:900px}
    .dh td{border:1px solid #222;padding:4px;height:22px;vertical-align:middle}
    .dh .center{text-align:center;font-weight:bold}
    .dh .head{font-weight:bold;text-align:center;background:#fff}
    .dh .right{text-align:right;white-space:nowrap}
    .dh .group td{background:#fce4d6;font-weight:bold}
    .dh .subtotal td{font-weight:bold}
    .dh .grand td{font-weight:bold;border-top:2px solid #000;border-bottom:3px double #000}
    .dh .noborder td{border:0}
    .dh .muted{color:#1f4e79}
</style>";
$stream .= "<table class='dh' border='".$border."' cellspacing=0 cellpadding=3>";
$stream .= "<tr class='noborder'><td colspan=6 class=center>".strtoupper($namaOrg)."</td></tr>";
$stream .= "<tr class='noborder'><td colspan=6 class=center>DAFTAR HUTANG</td></tr>";
$stream .= "<tr class='noborder'><td colspan=6 class=center>PER ".$tanggalCaption."</td></tr>";
$stream .= "<tr class='noborder'><td colspan=6>&nbsp;</td></tr>";
$stream .= "<tr>
    <td class=head style='width:45px'>NO</td>
    <td class=head style='width:430px'>URAIAN / RINCIAN</td>
    <td class=head style='width:90px'>AKUN</td>
    <td class=head style='width:90px'>PERIODE</td>
    <td class=head style='width:150px'>TANGGAL TERBIT / PEMBAYARAN</td>
    <td class=head style='width:140px'>SALDO</td>
</tr>";

$grand = 0;
$noGroup = 0;
foreach($groups as $groupUrut=>$group){
    $noGroup++;
    $stream .= "<tr class=group><td class=center>".$noGroup."</td><td colspan=4>".htmlspecialchars($group['keterangan'], ENT_QUOTES)."</td><td></td></tr>";
    $totalGroup = 0;
    $no = 0;
    if(isset($detailByGroup[$groupUrut])){
        foreach($detailByGroup[$groupUrut] as $detailUrut){
            if(!isset($details[$detailUrut])) continue;
            $detail = $details[$detailUrut];
            foreach($detail['akun'] as $akun){
                if(!isset($saldoRows[$akun])) continue;
                foreach($saldoRows[$akun] as $bar){
                    $no++;
                    $totalGroup += $bar['saldo'];
                    $grand += $bar['saldo'];
                    $label = $bar['nama'];
                    $accountLabel = isset($akunName[$akun]) ? $akunName[$akun] : $akun;
                    $stream .= "<tr>
                        <td class=center>".$no."</td>
                        <td><span class=muted>".htmlspecialchars($accountLabel, ENT_QUOTES)."</span> - ".htmlspecialchars($label, ENT_QUOTES)."</td>
                        <td class=center>".$akun."</td>
                        <td class=center>".htmlspecialchars($bar['periode'], ENT_QUOTES)."</td>
                        <td class=center>".dhDate($bar['tanggal'])."</td>
                        <td class=right>".dhMoney($bar['saldo'])."</td>
                    </tr>";
                }
            }
        }
    }
    if($no==0){
        $stream .= "<tr><td></td><td colspan=4>&nbsp;</td><td class=right>-</td></tr>";
    }
    $stream .= "<tr class=subtotal><td></td><td colspan=4>Total ".$group['keterangan']."</td><td class=right>".dhMoney($totalGroup)."</td></tr>";
}
$stream .= "<tr class=grand><td></td><td colspan=4>TOTAL HUTANG</td><td class=right>".dhMoney($grand)."</td></tr>";
$stream .= "</table>";

if($tipe=='excel'){
    $stream .= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop = "DaftarHutang-".$pt."_".$unit."_".$periode."___".date("YmdHis").".xls";
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
