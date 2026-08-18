<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdUnit', '');
$periode = checkPostGet('periode', '');
$divisi = checkPostGet('divisi', '');
if ($kdorg == '') {
    echo"Warning: Unit tidak boleh kosong";
    exit;
}
if (($periode == '')) {
    echo"Warning: Periode tidak boleh kosong !!!";
    exit;
}

$where='';
$whereX='';
if($divisi!=''){
	$where=" and a.divisi='".$divisi."'";
	$whereX=" and a.kodeorg like '".$divisi."%'";
}else{
	$where=" and a.divisi like'".$kdorg."%'";
	$whereX=" and a.kodeorg like '".$kdorg."%'";
}


$str = "select * from " . $dbname . ".kebun_rkbdt a 
			left join " . $dbname . ".kebun_rkbht b on a.norkb=b.norkb 
			where a.periode='".$periode."' and a.tipetransaksi='PEMEL' and b.statuspersetujuan='1' ".$where." order by a.divisi asc, a.kodekegiatan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$kegiatan[$bar['divisi']][$bar['kodekegiatan']] = $bar['kodekegiatan'];
	@$hkrkb[$bar['divisi']][$bar['kodekegiatan']] += ($bar['KBL']+$bar['KHT']+$bar['KHL']);
}


$str = "select sum(jumlahhk) as jumlahhk, a.kodekegiatan,substr(a.kodeorg,1,6) as divisi from " . $dbname . ".kebun_prestasi a 
		left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
		where b.tanggal like '".$periode."%' and b.kodeorg='".$kdorg."' ".$whereX." and a.notransaksi not like '%PNN%' group by a.kodekegiatan, substr(a.kodeorg,1,6) order by substr(a.kodeorg,1,6) asc, a.kodekegiatan asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$kegiatan[$bar['divisi']][$bar['kodekegiatan']] = $bar['kodekegiatan'];
	@$hkreal[$bar['divisi']][$bar['kodekegiatan']] += $bar['jumlahhk'];
}			

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}
$stream.="
    <thead>
        <tr class=rowheader>
            <th align=center >" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center >" . $_SESSION['lang']['divisi'] . "</th>
            <th align=center >" . $_SESSION['lang']['kodekegiatan'] . "</th>
            <th align=center >" . $_SESSION['lang']['namakegiatan'] . "</th>
            <th align=center >" . $_SESSION['lang']['periode'] . "</th>
            <th align=center >HK Realisasi</th>
            <th align=center >HK RKB</th>
            
            <th align=center >Sisa HK Tersedia</th>
            <th align=center >% HK Aktual</th>
        </tr>";
$stream.="</thead><tbody>";
@$jumkeg = count($kegiatan);
if ($jumkeg == 0) {
    exit("error : Data kosong");
}
$no='';
foreach($kegiatan as $div => $valkeg){
	foreach($valkeg as $keg => $kodekeg){
		$no+=1;
		$optkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kodekeg."'");
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>" . $no . "</td>";
		$stream.="<td align=center>" . $div . "</td>";
		$stream.="<td align=left>" . $kodekeg . "</td>";
		$stream.="<td align=left>" . $optkeg[$kodekeg] . "</td>";
		$stream.="<td align=center>" . $periode . "</td>";
		$stream.="<td align=right>" . @number_format($hkreal[$div][$kodekeg],2) . "</td>";
		$stream.="<td align=right>" . @number_format($hkrkb[$div][$kodekeg],2) . "</td>";
		$stream.="<td align=right>" . @number_format($hkrkb[$div][$kodekeg]-$hkreal[$div][$kodekeg],2) . "</td>";
		$stream.="<td align=right>" . @number_format(($hkreal[$div][$kodekeg]/$hkrkb[$div][$kodekeg])*100,2) . "</td>";
		@$ttlhkreal[$div]+=$hkreal[$div][$kodekeg];
		@$ttlhkrkb[$div]+=$hkrkb[$div][$kodekeg];
	}
	$stream.="<tr class=rowcontent style=background-color:cyan>";
	$stream.="<td align=center colspan=5>TOTAL DIVISI " . $div . "</td>";
	$stream.="<td align=right>" . @number_format($ttlhkreal[$div],2) . "</td>";
	$stream.="<td align=right>" . @number_format($ttlhkrkb[$div],2) . "</td>";
	$stream.="<td align=right>" . @number_format($ttlhkrkb[$div]-$ttlhkreal[$div],2) . "</td>";
	$stream.="<td align=right>" . @number_format(($ttlhkreal[$div]/$ttlhkrkb[$div])*100,2) . "</td>";
	@$gtreal+=$ttlhkreal[$div];
	@$gtrkb+=$ttlhkrkb[$div];
}

$stream.="<tr class=rowcontent style=background-color:cyan>";
$stream.="<td align=center colspan=5>TOTAL</td>";
$stream.="<td align=right>" . @number_format($gtreal,2) . "</td>";
$stream.="<td align=right>" . @number_format($gtrkb,2) . "</td>";
$stream.="<td align=right>" . @number_format($gtrkb-$gtreal,2) . "</td>";
$stream.="<td align=right>" . @number_format(($gtreal/$gtrkb)*100,2) . "</td>";

$stream.="</tr><thead>";
$stream.="</tbody></table>";
switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;
######EXCEL	
    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "realvarkb_" . $kdorg;
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
?>