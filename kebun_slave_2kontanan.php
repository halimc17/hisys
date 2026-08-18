<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$tgl1 = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2', ''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdUnit', '');
$divisi = checkPostGet('divisi', '');
$rangetanggal = rangeTanggal($tgl1, $tgl2);
if ($kdorg == '') {
    echo"Warning: Unit tidak boleh kosong";
    exit;
}
if (($tgl1 == '')or ( $tgl2 == '')) {
    echo"Warning: Tanggal tidak boleh kosong";
    exit;
} else if ($tgl1 > $tgl2) {
    echo"Warning: Tanggal pertama tidak boleh lebih besar dari tanggal kedua";
    exit;
}
# ambi data dari mandor
$str = "select a.*,b.*, b.subbagian as divisi  from " . $dbname . ".kebun_premikemandoran a left join "
		."".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where "
		."a.kodeorg like '%" . $kdorg . "%' and b.subbagian like '".$divisi."%' "
		." and a.kontanan='KONTAN' and a.tanggalkontanan between '" . $tgl1 . "' and '" . $tgl2 . "' order by b.subbagian";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$kddivisi[$bar['divisi']]= $bar['divisi'];
	$karyawan[$bar['karyawanid']] = $bar['karyawanid'];
	$jenis['MDR']='MDR';
	$listjns[$bar['divisi']]['MDR'] = 'MDR';
	
	$listdiv[$bar['divisi']]['MDR'][$bar['karyawanid']] = $bar['divisi'];
	$listkary[$bar['divisi']]['MDR'][$bar['karyawanid']] = $bar['karyawanid'];
	$data[$bar['divisi']]['MDR'][$bar['karyawanid']] = $bar['karyawanid'];
	@$listkg[$bar['divisi']]['MDR'][$bar['karyawanid']] += 0;
	@$listpremi[$bar['divisi']]['MDR'][$bar['karyawanid']] += $bar['premiinput'];
}
# ambi data dari transaksi panen
$str = "select a.*,b.*, substr(a.kodeorg,1,6) as divisi  from " . $dbname . ".kebun_prestasi a left join "
		."".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where "
		."a.notransaksi like '%" . $kdorg . "%' and a.kodeorg like '".$divisi."%' "
		." and a.keterangan='KONTAN' and b.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' order by substr(a.kodeorg,1,6)";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$kddivisi[$bar['divisi']]= $bar['divisi'];
	$karyawan[$bar['nik']] = $bar['nik'];
	$jenis['PNN']='PNN';
	$listjns[$bar['divisi']]['PNN'] = 'PNN';
	
	$listdiv[$bar['divisi']]['PNN'][$bar['nik']] = $bar['divisi'];
	$listkary[$bar['divisi']]['PNN'][$bar['nik']] = $bar['nik'];
	$data[$bar['divisi']]['PNN'][$bar['nik']] = $bar['nik'];
	@$listkg[$bar['divisi']]['PNN'][$bar['nik']] += $bar['hasilkerjakg'];
	@$listpremi[$bar['divisi']]['PNN'][$bar['nik']] += $bar['upahpremilebihbasis'];
	@$listpenalty[$bar['divisi']]['PNN'][$bar['nik']] += $bar['rupiahpenalty'];
}



# ambi data dari BM TBS
$str = "select *  from " . $dbname . ".kebun_3premibmtbs where "
		."kodeorg like '%" . $kdorg . "%' and divisi like '".$divisi."%' "
		." and kontanan='KONTAN' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' order by divisi";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$kddivisi[$bar['divisi']] = $bar['divisi'];
	$karyawan[$bar['karyawanid']] = $bar['karyawanid'];
	$jenis['BM']='BM';
	$listjns[$bar['divisi']]['BM'] = 'BM';
	
	$listdiv[$bar['divisi']]['BM'][$bar['karyawanid']] = $bar['divisi'];
	$listkary[$bar['divisi']]['BM'][$bar['karyawanid']] = $bar['karyawanid'];
	$data[$bar['divisi']]['BM'][$bar['karyawanid']] = $bar['karyawanid'];
	@$listkg[$bar['divisi']]['BM'][$bar['karyawanid']] += $bar['kglb'];
	@$listpremi[$bar['divisi']]['BM'][$bar['karyawanid']] += $bar['rplb'];
}


if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}
$span = count($rangetanggal);
$stream.="
    <thead>
        <tr class=rowheader>
            <th align=center >" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center >" . $_SESSION['lang']['divisi'] . "</th>
            <th align=center >" . $_SESSION['lang']['jenis'] . "</th>
            <th align=center >" . $_SESSION['lang']['nik'] . "</th>
            <th align=center >" . $_SESSION['lang']['namakaryawan'] . "</th>
            <th align=center >" . $_SESSION['lang']['jabatan'] . "</th>
            <th align=center >" . $_SESSION['lang']['namabank'] . "</th>
            <th align=center >" . $_SESSION['lang']['norek'] . "</th>
            
            <th align=center >" . $_SESSION['lang']['hasilkerjakg'] . "</th>
            <th align=center >" . $_SESSION['lang']['harga'] . "</th>
            <th align=center >" . $_SESSION['lang']['upah'] . "</th>
            <th align=center >" . $_SESSION['lang']['penalti'] . "</th>
            <th align=center >Netto</th>
            <th align=center >" . $_SESSION['lang']['keterangan'] . "</th>
        </tr>";
$stream.="</thead><tbody>";

@$jumkary = count($data);
if ($jumkary > 0) {
    array_multisort($kddivisi, SORT_ASC);
    array_multisort($karyawan, SORT_ASC);
} else {
    exit("error : Data kosong");
}

$nmjenis=array('BM'=>'BM TBS','PNN'=>'Pemanen','MDR'=>'Pengawas');
foreach ($data as $divisi => $valjns) {
	foreach ($valjns as $jns => $valkary) {
		foreach ($valkary as $karywn => $kary) {
			
				$no+=1;
				$optnik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$kary."'");
				$optnama=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kary."'");
				$optjab=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$kary."'");
				$optnamajab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$optjab[$kary]."'");
				$optnmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$divisi."'");
				$optnmbank=makeOption($dbname,'datakaryawan','karyawanid,namabank',"karyawanid='".$kary."'");
				$optnorek=makeOption($dbname,'datakaryawan','karyawanid,norekeningbank',"karyawanid='".$kary."'");
				
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>" . $no . "</td>";
				$stream.="<td align=left>".$divisi." - " . $optnmorg[$divisi]. "</td>";
				$stream.="<td align=center>" . $nmjenis[$jns] . "</td>";
				$stream.="<td align=center>" . $optnik[$kary]. "</td>";
				$stream.="<td align=left>" . $optnama[$kary]. "</td>";
				$stream.="<td align=left>" . $optnamajab[$optjab[$kary]]. "</td>";
				$stream.="<td align=left>" . $optnmbank[$kary]. "</td>";
				$stream.="<td align=left>" . $optnorek[$kary]. "</td>";
				$stream.="<td align=right>" . @number_format($listkg[$divisi][$jns][$kary]). "</td>";
				if($listkg[$divisi][$jns][$kary]=='' or $listkg[$divisi][$jns][$kary]==0){
					$stream.="<td align=right>0</td>";
				}else{
					$stream.="<td align=right>".@number_format($listpremi[$divisi][$jns][$kary]/$listkg[$divisi][$jns][$kary]). "</td>";
				}
				$stream.="<td align=right>" . @number_format($listpremi[$divisi][$jns][$kary]). "</td>";
				$stream.="<td align=right>" . @number_format($listpenalty[$divisi][$jns][$kary]). "</td>";
				$stream.="<td align=right>" . @number_format($listpremi[$divisi][$jns][$kary]-$listpenalty[$divisi][$jns][$kary]). "</td>";
				$stream.="<td align=right></td>";
				$stream.="</tr>";
			
				@$ttlkgjns[$divisi][$jns]+=$listkg[$divisi][$jns][$kary];
				@$ttlrpjns[$divisi][$jns]+=$listpremi[$divisi][$jns][$kary];
				@$ttlrpjnspen[$divisi][$jns]+=$listpenalty[$divisi][$jns][$kary];
				
				@$ttlkgdiv[$divisi]+=$listkg[$divisi][$jns][$kary];
				@$ttlrpdiv[$divisi]+=$listpremi[$divisi][$jns][$kary];
				@$ttlrpdivpen[$divisi]+=$listpenalty[$divisi][$jns][$kary];
				
				@$ttlkg+=$listkg[$divisi][$jns][$kary];
				@$ttlrp+=$listpremi[$divisi][$jns][$kary];
				@$ttlrppen+=$listpenalty[$divisi][$jns][$kary];
		}
		$stream.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF>";
		$stream.="<td align=center colspan=3>Sub Total ".$nmjenis[$jns]."</td>";
		$stream.="<td></td>";
		$stream.="<td></td>";
		$stream.="<td></td>";
		$stream.="<td></td>";
		$stream.="<td></td>";
		$stream.="<td align=right>".number_format($ttlkgjns[$divisi][$jns])."</td>";
		if($ttlkgjns[$divisi][$jns]=='' or $ttlkgjns[$divisi][$jns]==0){
			$stream.="<td align=right></td>";
		}else{
			$stream.="<td align=right>".number_format($ttlrpjns[$divisi][$jns]/$ttlkgjns[$divisi][$jns])."</td>";
		}
		$stream.="<td align=right>".number_format($ttlrpjns[$divisi][$jns])."</td>";
		$stream.="<td align=right>".number_format($ttlrpjnspen[$divisi][$jns])."</td>";
		$stream.="<td align=right>".number_format($ttlrpjns[$divisi][$jns]-$ttlrpjnspen[$divisi][$jns])."</td>";
		$stream.="<td></td>";
		
		$stream.="</tr>";
		
    }
	
	$stream.="<tr class=rowcontent style=font-weight:bold;background-color:#85C1E9>";
	$stream.="<td align=center colspan=3>Sub Total ".$divisi."</td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
	$stream.="<td align=right>".number_format($ttlkgdiv[$divisi])."</td>";
	if($ttlkgdiv[$divisi]=='' or $ttlkgdiv[$divisi]==0){
		$stream.="<td align=right></td>";
	}else{
		$stream.="<td align=right>".number_format($ttlrpdiv[$divisi]/$ttlkgdiv[$divisi])."</td>";
	}
	$stream.="<td align=right>".number_format($ttlrpdiv[$divisi])."</td>";
	$stream.="<td align=right>".number_format($ttlrpdivpen[$divisi])."</td>";
	$stream.="<td align=right>".number_format($ttlrpdiv[$divisi]-$ttlrpdivpen[$divisi])."</td>";
	$stream.="<td></td>";
	
	$stream.="</tr>";
}
$stream.="<tr class=rowcontent style=font-weight:bold;background-color:#3498DB>";
$stream.="<td align=center colspan=3>Grand Total</td>";
$stream.="<td></td>";
$stream.="<td></td>";
$stream.="<td></td>";
$stream.="<td></td>";
$stream.="<td></td>";
$stream.="<td align=right>".number_format($ttlkg)."</td>";
if(ttlkg=='' or ttlkg==0){
	$stream.="<td align=right></td>";
}else{
	$stream.="<td align=right>".number_format($ttlrp/$ttlkg)."</td>";
}
$stream.="<td align=right>".number_format($ttlrp)."</td>";
$stream.="<td align=right>".number_format($ttlrppen)."</td>";
$stream.="<td align=right>".number_format($ttlrp-$ttlrppen)."</td>";
$stream.="<td></td>";

$stream.="</tr>";

$stream.="<thead>";
$stream.="</tbody></table>";
switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;
######EXCEL	
    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "kontanan_" . $kdorg;
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