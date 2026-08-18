<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$tgl1 = tanggalsystem(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystem(checkPostGet('tgl2', ''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdUnit', '');
$divisi = checkPostGet('divisi', '');
$tanggal = tanggalsystemn(checkPostGet('tgl1', ''));
$periode = substr($tanggal,0,7);
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


$str = "select * from " . $dbname . ".kebun_5dendapanen where kodeorg='".$kdorg."'";
$resdenda=fetchdata($str);
$span = count($resdenda);

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}
$stream.="
    <thead>
        <tr class=rowheader>

            <th align=center  rowspan='3'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center  rowspan='3'>" . $_SESSION['lang']['divisi'] . "</th>
            <th align=center  rowspan='3'>" . $_SESSION['lang']['nik2'] . "</th>
            <th align=center  rowspan='3'>" . $_SESSION['lang']['namakaryawan'] . "</th>
            <th align=center  rowspan='3'>" . $_SESSION['lang']['status'] . "</th>
            <th align=center  colspan='11'>" . $_SESSION['lang']['panen'] . "</th>
            <th align=center  colspan=".(($span*2)+1).">" . $_SESSION['lang']['denda'] . "</th>

        </tr>";
$stream.="<tr>
			<th align=center rowspan='2'>" . $_SESSION['lang']['luas'] . "</th>
			<th align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "</th>
			<th align=center rowspan='2'>" . $_SESSION['lang']['kgwb'] . "</th>
			<th align=center rowspan='2'>" . $_SESSION['lang']['hk2'] . "</th>
			<th align=center rowspan='2'>" . $_SESSION['lang']['upah'] . "</th>
			<th align=center rowspan='2' width=70px>" . $_SESSION['lang']['premibasis'] . "</th>
			<th align=center rowspan='2' width=70px>" . $_SESSION['lang']['premlebihbasis'] . "</th>
			<th align=center rowspan='2' width=70px>" . $_SESSION['lang']['jumlahpremi'] . "</th>
			<th align=center rowspan='2'>" . $_SESSION['lang']['denda'] . "<br>Rp</th>
			<th align=center rowspan='2'>" . $_SESSION['lang']['premi'] . " " . $_SESSION['lang']['dibayar'] . " Rp</th>
			<th align=center rowspan='2'>" . $_SESSION['lang']['upah'] . " + " . $_SESSION['lang']['premi'] . " Rp</th>
			";
$title='';
foreach ($resdenda as $listdenda => $bardenda) {
        $title=" title=\"".$bardenda['deskripsi']."\"";
		$stream.="<th align=center colspan='2' ".$title."><b>".$bardenda['kodedenda']."</b><font size='1'><br>Rp.".number_format($bardenda['denda'])."<br>".$bardenda['jenisdenda']."</font></th>";
}
		$stream.="<th align=center rowspan=2>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['denda']."<br>Rp</th>";

$stream.="</tr>";
$stream.="<tr>";
foreach ($resdenda as $listdenda => $bardenda) {
        $stream.="<th align=center>".$_SESSION['lang']['fisik']."</th>";
        $stream.="<th align=center>Rp</th>";
}

$stream.="</tr>";

$stream.="</thead>
 <tbody>";
######################################
############# prepare data ###########
######################################

#kebun_prestasi_vs_hk
$str = "select * from " . $dbname . ".kebun_prestasi_vs_hk where "
        . "lokasitugas='" . $kdorg . "' and subbagian like '".$divisi."%' and tanggal between '".$tgl1."' and '".$tgl2."' order by subbagian asc, namakaryawan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kdkary[$bar['karyawanid']] = $bar['karyawanid'];
	$kddivisi[$bar['subbagian']] = $bar['subbagian'];
    $listkary[$bar['subbagian']][$bar['karyawanid']] = $bar['karyawanid'];
    $namakary[$bar['subbagian']][$bar['karyawanid']] = $bar['namakaryawan'];
    @$janjang[$bar['subbagian']][$bar['karyawanid']]+= $bar['hasilkerja'];
    #@$kg[$bar['subbagian']][$bar['karyawanid']]+= $bar['hasilkerjakg'];
    @$upah[$bar['subbagian']][$bar['karyawanid']]+= $bar['tupah'];
    @$luaspanen[$bar['subbagian']][$bar['karyawanid']]+= $bar['luaspanen'];
    @$hk[$bar['subbagian']][$bar['karyawanid']]+= $bar['hkpanenperhari'];
    @$premisb[$bar['subbagian']][$bar['karyawanid']]+= $bar['upahpremi'];
    @$premilb[$bar['subbagian']][$bar['karyawanid']]+= $bar['upahpremilebihbasis'];
    @$tpremi[$bar['subbagian']][$bar['karyawanid']]+= $bar['tpremi'];
    @$rpenalty[$bar['subbagian']][$bar['karyawanid']]+= $bar['rupiahpenalty'];

}

#kebun_3premipemanen
$str = "select * from " . $dbname . ".kebun_3premipemanen where "
        . "kodeorg='" . $kdorg . "' and divisi like '".$divisi."%' and periode like '".$periode."%'"; //exit('error'.$str);
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$kg[$bar['divisi']][$bar['karyawanid']]+= $bar['kgwb'];
}


#kebun_prestasi
$str = "select a.*,substr(a.notransaksi,0,8) as tanggal,b.karyawanid, b.lokasitugas, b.subbagian, b.nik, b.namakaryawan from " . $dbname . ".kebun_prestasi a
		left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid
		where b.lokasitugas='" . $kdorg . "' and b.subbagian like '".$divisi."%' and substr(a.notransaksi,1,8) between '".$tgl1."' and '".$tgl2."'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['A']+= $bar['penalti1'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['S']+= $bar['penalti2'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['M1']+= $bar['penalti3'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['M2']+= $bar['penalti4'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['M3']+= $bar['penalti5'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['GL']+= $bar['penalti6'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['PB']+= $bar['penalti7'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['TP']+= $bar['penalti8'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['BT']+= $bar['penalti9'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['PS']+= $bar['penalti10'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['X1']+= $bar['penalti11'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['X2']+= $bar['penalti12'];
		@$penalti[$bar['subbagian']][$bar['karyawanid']]['X3']+= $bar['penalti13'];
	}

$where="lokasitugas='".$kdorg."'";
$nikkary=makeOption($dbname,'datakaryawan','karyawanid,nik',$where);
$kodetipe=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',$where);
$namatipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

@$jumdiv = count($kdkary);
if ($jumdiv > 0) {
    array_multisort($kddivisi, SORT_ASC);
    array_multisort($listkary, SORT_ASC);
} else {
    exit("error : Data kosong");
}

foreach ($kddivisi as $divisi) {
	foreach ($kdkary as $kary) {
	$listkary[$divisi][$kary]= isset($listkary[$divisi][$kary]) ? $listkary[$divisi][$kary] : '';
		if ($listkary[$divisi][$kary] != '') {
			$no+=1;$color="";
			$stream.="<tr class=rowcontent style=cursor:pointer; title='clickdetail' onclick=level1('".$listkary[$divisi][$kary]."','".$tgl1."','".$tgl2."','".$divisi."','html','DetailBlok',event)>
				<td align=center>".$no."</td>
				<td align=center>".$divisi."</td>
				<td align=center>".$nikkary[$listkary[$divisi][$kary]]."</td>
				<td >".$namakary[$divisi][$kary]."</td>
				<td >".$namatipe[$kodetipe[$listkary[$divisi][$kary]]]."</td>
				<td align=right>".@number_format($luaspanen[$divisi][$kary],2)."</td>
				<td align=right>".@number_format($janjang[$divisi][$kary])."</td>
				<td align=right>".@number_format($kg[$divisi][$kary])."</td>
				<td align=right>".@number_format($hk[$divisi][$kary],2)."</td>
				<td align=right>".@number_format($upah[$divisi][$kary])."</td>
				<td align=right>".@number_format($premisb[$divisi][$kary])."</td>
				<td align=right>".@number_format($premilb[$divisi][$kary]+$rpenalty[$divisi][$kary])."</td>
				<td align=right>".@number_format($tpremi[$divisi][$kary]+$rpenalty[$divisi][$kary])."</td>
				<td align=right>".@number_format($rpenalty[$divisi][$kary])."</td>
				<td align=right>".@number_format($tpremi[$divisi][$kary])."</td>
				<td align=right>".@number_format(($upah[$divisi][$kary]+$tpremi[$divisi][$kary]))."</td>
				";
				@$tluaspanen+=$luaspanen[$divisi][$kary];
				@$tjanjang+=$janjang[$divisi][$kary];
				@$tkg+=$kg[$divisi][$kary];
				@$thk+=$hk[$divisi][$kary];
				@$tupah+=$upah[$divisi][$kary];
				@$tpremisb+=$premisb[$divisi][$kary];
				@$tpremilb+=$premilb[$divisi][$kary]+$rpenalty[$divisi][$kary];
				@$ttpremi+=$tpremi[$divisi][$kary]+$rpenalty[$divisi][$kary];
				@$trpenalty+=$rpenalty[$divisi][$kary];

			foreach ($resdenda as $listdenda => $bardenda) {
			$stream.="
				  <td align=right>".@$penalti[$divisi][$kary][$bardenda['kodedenda']]."</td>
				  <td align=right>".@number_format($penalti[$divisi][$kary][$bardenda['kodedenda']]*$bardenda['denda'])."</td>
				  ";

				  @$totaldendafis[$divisi][$bardenda['kodedenda']]+=$penalti[$divisi][$kary][$bardenda['kodedenda']];
				  @$totaldendarp[$divisi][$bardenda['kodedenda']]+=$penalti[$divisi][$kary][$bardenda['kodedenda']]*$bardenda['denda'];
				  @$totaldendarpperkary[$divisi][$kary]+=$penalti[$divisi][$kary][$bardenda['kodedenda']]*$bardenda['denda'];
			}
			if($rpenalty[$divisi][$kary]!=$totaldendarpperkary[$divisi][$kary]){
				$color=" color=red";
			}
			$stream.="
				  <td align=right><font ".$color.">".@number_format($totaldendarpperkary[$divisi][$kary])."</font></td>
					";
			@$gtotaldendarpperkary+=$totaldendarpperkary[$divisi][$kary];
		}
	}
}
$stream.="
        <tr bgcolor=#F5F5DC>
            <td align=center colspan=5>" . $_SESSION['lang']['grnd_total'] . "</td>
            <td align=right>".@number_format($tluaspanen,2)."</td>
            <td align=right>".@number_format($tjanjang)."</td>
            <td align=right>".@number_format($tkg)."</td>
            <td align=right>".@number_format($thk)."</td>
            <td align=right>".@number_format($tupah)."</td>
            <td align=right>".@number_format($tpremisb)."</td>
            <td align=right>".@number_format($tpremilb)."</td>
            <td align=right>".@number_format($ttpremi)."</td>
            <td align=right>".@number_format($trpenalty)."</td>
            <td align=right>".@number_format($ttpremi-$trpenalty)."</td>
            <td align=right>".@number_format(($tupah+$ttpremi)-$trpenalty)."</td>
		";
	foreach ($resdenda as $listdenda => $bardenda) {
		$stream.="
		  <td align=right>".$totaldendafis[$divisi][$bardenda['kodedenda']]."</td>
		  <td align=right>".@number_format($totaldendarp[$divisi][$bardenda['kodedenda']])."</td>
		  ";

	}
$stream.="
		  <td align=right>".@number_format($gtotaldendarpperkary)."</td>
		  ";

$stream.="</tr>";

$stream.="
 </tbody>
     </table>";

switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;

######EXCEL
    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = getMenu('kebun_2potongbuahdetail')." ".$kdorg;
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