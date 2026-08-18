<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$unit = checkPostGet('unit', '');
$tglAwal = tanggalsystem(checkPostGet('tglAwal', ''));
$tglAkhir = tanggalsystem(checkPostGet('tglAkhir', ''));
$periode = checkPostGet('periode', '');

$jenisVhc = makeOption($dbname, 'vhc_5master', 'kodevhc,jenisvhc');

$tahunperolehan = makeOption($dbname, 'vhc_5master', 'kodevhc,tahunperolehan');


if($unit=='')
{
    echo"warning : Working unit required";exit();
}
if($tglAwal==''||$tglAkhir==''){
  echo "Warning: date required"; exit;
}

$str="select sum(debet)-sum(kredit) as jumlah,kodevhc,kodeorg from 
    ".$dbname.".keu_jurnaldt_vw where tanggal>='".$tglAwal."' and tanggal<='".$tglAkhir."' 
    and noakun='4110299' and kodeorg='".substr($unit,0,4)."'  group by kodevhc ";   
//echo $str;  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
  $teralokasi[$bar['kodevhc']]=$bar['jumlah']*-1;
}


$kdOrg = substr($unit, 0, 4);
$akunkdari='';
    $akunksampai='';
    $strh="select distinct noakundebet,sampaidebet  from ".$dbname.".keu_5parameterjurnal where  jurnalid='LPVHC'";
    $resh=$owlPDO->query($strh) or die(print " Gagal: ".PDOException::getMessage());
    $resh->setFetchMode(PDO::FETCH_OBJ);
    while($barh=$resh->fetch()){
        $akunkdari=$barh->noakundebet;
        $akunksampai=$barh->sampaidebet;
    }
    if($akunkdari=='' or $akunksampai==''){
        exit("Error: Journal parameter for LPVHC(vehicle cost) not exist");
    }


$str = "select sum(jumlah) as jumlah, kodevhc from ".$dbname.".keu_jurnaldt_vw where  noakun not in (4110299,4110199) and kodeorg='".substr($unit,0,4)."' and tanggal>='".$tglAwal."' and tanggal<='".$tglAkhir."' and (noakun between '".$akunkdari."' and '".$akunksampai."') and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL) group by kodevhc";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = owlBaris($res);

$no = 0;
if ($row < 1) {
    echo"<tr class=rowcontent><td colspan=10>" . $_SESSION['lang']['tidakditemukan'] . "</td></tr>";
} else {
    $stream = $_SESSION['lang']['biayatotalperkendaraan'] . ": " . $unit . " : " . $periode . " (" . tanggalnormal($tglAwal) . " - " . tanggalnormal($tglAkhir) . ")<br>
		<table border=1>
				    <tr>
			  <td bgcolor=#DEDEDE align=center>No.</td>
                          <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['jenisvch'] . "</td>
			  <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
			  <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['tahunperolehan'] . "</td>
			  <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['jumlah'] . "</td>
			  <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['jmljamkerja'] . "</td>  
			  <td bgcolor=#DEDEDE align=center>Price / Unit</td>
			  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['alokasirp']."</td>
			  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['blmAlokasi']."(Rp)</td>			  
					</tr>";
#ambil jumlah jam per kendaraan
    $str1 = "select sum(jumlah) as jumlah,kodevhc from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where tanggal>='".$tglAwal."' and tanggal<='".$tglAkhir."' and b.kodeorg='".substr($unit,0,4)."' group by kodevhc";
    
    $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while ($bar1 = $res1->fetch()) {
        //$jumlahjam[str_replace(" ","",$bar1->kodevhc)]=$bar1->jumlah;
        $jumlahjam[$bar1->kodevhc] = $bar1->jumlah;
    }

    $res->setFetchMode(PDO::FETCH_OBJ);
    $totalbiaya = $totaljam = 0;
    while ($bar = $res->fetch()) {

        $no+=1;
        $total = 0;
        setIt($jumlahjam[$bar->kodevhc], 0);
        if ($jumlahjam[$bar->kodevhc] > 0)
            @$rpunit = $bar->jumlah / $jumlahjam[$bar->kodevhc];
        else
            $rpunit = 0;  //<td align=right>".$periode."</td>              
        $stream.="<tr>
                          <td align=right>" . $no . "</td>
                          <td>" . $jenisVhc[$bar->kodevhc] . "</td>
                          <td>" . $bar->kodevhc . "</td>
                          <td>" . $tahunperolehan[$bar->kodevhc] . "</td>    
                          
                          <td align=right>" . number_format($bar->jumlah) . "</td>
                          <td align=right>" . $jumlahjam[$bar->kodevhc] . "</td> 
                          <td align=right>" . number_format($rpunit) . "</td>
						  <td align=right>".number_format($teralokasi[$bar->kodevhc])."</td>
				          <td align=right>".number_format($bar->jumlah-$teralokasi[$bar->kodevhc])."</td>						  
			</tr>";
        $totalbiaya+=$bar->jumlah;
        $totaljam+=$jumlahjam[str_replace(" ", "", $bar->kodevhc)];
		$alk+=$teralokasi[$bar->kodevhc];
    }
    $stream.="<tr class=rowcontent>";
    $stream.="<td colspan=4 align=center>" . $_SESSION['lang']['total'] . "</td>";
    $stream.="<td align=right>" . number_format($totalbiaya) . "</td>";
    $stream.="<td align=right>" . number_format($totaljam) . "</td>";
    $stream.="<td align=right>".@number_format($totalbiaya/$totaljam,2)."</td>";
    $stream.="<td align=right>".number_format($alk)."</td>";
    $stream.="<td align=right>".number_format($totalbiaya-$alk)."</td>";
    $stream.="</tr>";

    $stream.="</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
}

$nop_ = "BiayaTotalPerKendaraan_" . $unit . "_" . $periode;
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
?>