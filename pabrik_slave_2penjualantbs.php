<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt=checkPostGet('pt','');
$date1 = tanggalsystemn(checkPostGet('tanggal1',''));
$date2 = tanggalsystemn(checkPostGet('tanggal2',''));
$proses = checkPostGet('proses', '');
$kodecustomer=array();
$namacustomer=array();
$strcust="
select distinct pabrik_timbangan.kodecustomer, pmn_4customer.namacustomer
FROM ".$dbname.".pabrik_timbangan
left join pmn_4customer on pmn_4customer.kodecustomer = pabrik_timbangan.kodecustomer 
WHERE left(notransaksi,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')
and pabrik_timbangan.kodebarang='40000003'
and pabrik_timbangan.kodecustomer != ''
and left(tanggal,10) between '".$date1."' and '".$date2."'";
$res=$owlPDO->query($strcust) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $namacustomer[$bar['kodecustomer']]=$bar['namacustomer'];
    $kodecustomer[$bar['kodecustomer']]=$bar['kodecustomer'];
}

$strdisbun="
select *
from ".$dbname.".pmn_hargapasar
where pasar = 'Disbun Jambi'
order by tanggal desc limit 1
";
$res=$owlPDO->query($strdisbun) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $harga[$bar['harga']]=$bar['harga'];
}

$strrekappnn="
select tanggal, SUM(kgkebun) as panen
from ".$dbname.".kebun_rekappnn_vw
WHERE left(divisi,4) in (select kodeorganisasi from organisasi where induk='APP' and tipe='KEBUN')
and tanggal between '".$date1."' and '".$date2."'
group by tanggal
";
$res=$owlPDO->query($strrekappnn) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $rekap[$bar['tanggal']]=$bar['panen'];
}

// echo "<pre>";
// print_r($rekap);
// echo "</pre>";

$where='';
if ($pt!=''){
    $where.=" and kodeorganisasi like '".$pt."'";
}
$strpt="select namaorganisasi, kodeorganisasi
from ".$dbname.".organisasi
where 1=1 ".$where." and tipe='PT' order by namaorganisasi asc"; 
$res=$owlPDO->query($strpt) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $namaorganisasi[$bar['namaorganisasi']]=$bar['namaorganisasi'];
}
if ($proses == 'excel') {
    $stream = $_SESSION['lang']['laporan']." ".$_SESSION['lang']['penjualan']." ".$_SESSION['lang']['tbs']." ".$pt." ".$date1." - ".$date2."
    <table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 border=0>";
}
$span=count($namaorganisasi);
$customer=count($namacustomer);
$stream.="
<thead>
<tr class=rowheader style='font-family:tahoma,Arial Narrow;font-size:11px;background-color:#ededed'>
<td align=center rowspan='5'>Tanggal</td>
</tr>
";
$spanpt=($customer*7)+8;
foreach($namaorganisasi as $listorganisasi => $org){
    $stream.="
    <tr style='font-family:tahoma,Arial Narrow;font-size:11px;background-color:#ededed'>
    <td align=center colspan='".$spanpt."'>".$org."</td>";
}
$stream.="<tr style='font-family:tahoma,Arial Narrow;font-size:11px;background-color:#ededed'>";
$spantotal=($customer*7);
$spanpersen=($customer*7)+3;

$spanpenjualan=($customer*7)+6;
for($i=1;$i<=$span;$i++)
{
$stream.="
<td align=center rowspan='2'>Panen (rekapnn)</td>
<td align=center rowspan='2'>Kirim (hasiltimbangext)</td>
<td align=center colspan='".$spanpenjualan."'>Penjualan</td>
";
}
$stream.="
</tr>
<tr style='font-family:tahoma,Arial Narrow;font-size:11px;background-color:#ededed'>";
foreach($kodecustomer as $listcustomer => $cust)
{
$stream.="
<td align=center colspan='7'>".$namacustomer[$cust]."</td>
";
}
for($i=1;$i<=$span;$i++)
{
    
$stream.="
<td align=center>Disbun</td>
<td align=center>Total</td>
<td align=center>Jlh Truk</td>
<td align=center colspan='2'>Realisasi Truk</td>
<td align=center>Jlh Truk</td>";
}
$stream.="
</tr>
<tr style='font-family:tahoma,Arial Narrow;font-size:11px;background-color:#ededed'>";
for($i=1;$i<=$span;$i++)
{
$stream.="
<td align=center>(Kg)</td>
<td align=center>(Kg)</td>
";
}
for($i=1;$i<=$customer;$i++)
{
$stream.="
<td align=center>Netto 1</td>
<td align=center>Netto 2</td>
<td align=center>% Sortase</td>
<td align=center>Harga Jual</td>
<td align=center>Ongkos A.</td>
<td align=center>Harga nett</td>
<td align=center>Stlh Sort.*</td>";
}
for($i=1;$i<=$span;$i++)
{
$stream.="
<td align=center>Jambi</td>
<td align=center>Netto 2</td>
<td align=center>Dibutuhkan</td>
<td align=center>Kebun</td>
<td align=center>Rental</td>
<td align=center>Yg Kurang</td>
";
}
$stream.="</thead>";
$strtransaksi="select left(tanggal,10) as tanggal,kodecustomer,sum(beratmasukpmks) as netto1,
sum(beratbersihpmks) as netto2, keu_pengakuanjual.hargasatuan as hargajual
FROM `pabrik_timbangan`
left join keu_pengakuanjual on substr(keu_pengakuanjual.notransaksi,11,28) = pabrik_timbangan.nokontrak 
and keu_pengakuanjual.tanggalpengakuan = left(pabrik_timbangan.tanggal,10)
WHERE left(pabrik_timbangan.notransaksi,4) in (select kodeorganisasi from organisasi where induk='APP' and tipe='KEBUN') and kodebarang='40000003'
and left(tanggal,10) between '".$date1."' and '".$date2."'
and pabrik_timbangan.kodecustomer != ''
group by left(tanggal,10),kodecustomer;
";
$tglArr=array();
$res=$owlPDO->query($strtransaksi) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$tgldt=substr($bar['tanggal'],0,10);
	$tglArr[$tgldt]=$tgldt;
	$netto1[$tglArr[$tgldt]][$bar['kodecustomer']]=$bar['netto1'];
	$netto2[$tglArr[$tgldt]][$bar['kodecustomer']]=$bar['netto2'];
	$hargajual[$tglArr[$tgldt]][$bar['kodecustomer']]=$bar['hargajual'];
	$kirim[$tglArr[$tgldt]]=$bar['netto2'];
}
$totalrekap=0;
$totalkirim=0;
$netto2total=0;
foreach ($tglArr as $tgl) {
	$totalnetto2=0;
	$sortase='';
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center>".$tgl."</td>";
	$stream.="<td align=right>".@number_format($rekap[$tgl],2)."</td>";
	$stream.="<td align=right>".@number_format($kirim[$tgl],2)."</td>";
	@$totalkirim+=$kirim[$tgl];
	@$totalrekap+=$rekap[$tgl];
	foreach ($kodecustomer as $cust) {
		@$sortase=($netto1[$tgl][$cust]-$netto2[$tgl][$cust])/$netto1[$tgl][$cust];
		@$stlhsortase=($hargajual[$tgl][$cust]*(1-$sortase));
		$stream.="<td align=right>".@number_format($netto1[$tgl][$cust],2)."</td>";
		$stream.="<td align=right>".@number_format($netto2[$tgl][$cust],2)."</td>";
        if(is_nan($sortase)){
            $stream.="<td align=right>0.00</td>";
        }else{
		      $stream.="<td align=right>".@number_format($sortase,2)."%</td>";
        }
		foreach ($harga as $hrg) {
			$stream.="<td align=right>".@number_format($hargajual[$tgl][$cust],2)."</td>";
			$stream.="<td align=right></td>";
			$stream.="<td align=right>".@number_format($hargajual[$tgl][$cust],2)."</td>";
		}
        if(is_nan($stlhsortase)){
		  $stream.="<td align=right>0.00</td>";
        }else{
            $stream.="<td align=right>".@number_format($stlhsortase,2)."</td>";
        }
		@$totalnetto2+=$netto2[$tgl][$cust];
		@$netto2total+=$totalnetto2;
		@$penjualan=$netto2total/$totalkirim;
	}

	$stream.="<td align=right>".@number_format($hrg,2)."</td>";
	$stream.="<td align=right>".@number_format($totalnetto2,2)."</td>";
	$stream.="<td align=center></td>";
	$stream.="<td align=center></td>";
	$stream.="<td align=center></td>";
	$stream.="<td align=center></td>";
}

$stream.="
</tr>
</tfoot>
<tr bgcolor=cyan>
<td align=center>TOTAL</td>
<td align=center>".@number_format($totalrekap,2)."</td>
<td align=center>".@number_format($totalkirim,2)."</td>
<td colspan='".$spantotal."' align=center></td>
<td align=center></td>
<td align=center>".@number_format($netto2total,2)."</td>
<td align=center></td>
<td align=center></td>
<td align=center></td>
<td align=center></td>
</tr>
<tr bgcolor=cyan>
<td align=center>% Penjualan</td>
<td align=center></td>
<td align=center colspan='".$spanpersen."'>".@number_format($penjualan,2)."%</td>
<td align=center></td>
<td align=center></td>
<td align=center></td>
<td align=center></td>
</tr>
</table>";

switch ($proses) {
######PREVIEW
    case 'preview':
    echo $stream;
    break;

######EXCEL 
    case 'excel':
    $tglSkrg = date("Ymd");
    $nop_ = "laporan_rekap_pembayaran_gaji_PHL_" . $pt;
    if (strlen($stream) > 0) {
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                    @unlink('tempExcel/' . $file);
                }
            }
            closedir($handle);
        }
        $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
        if (!fwrite($handle, $stream)) {
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
}

