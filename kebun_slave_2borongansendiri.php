<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$tgl1 = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2', ''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');

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

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center>" . $_SESSION['lang']['organisasi'] . "</th>
            <th align=center>" . $_SESSION['lang']['nojurnal'] . "</th>
            <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center>Jlh org</th>
            <th align=center>Total unit</th>
            <th align=center>Total Rp</th>    
            <th align=center>Blok</th>        
            <th align=center>TT</th>      
            <th align=center>Nama Akun</th>      
            <th align=center style=display:none>No Pengajuan</th>      
            <th align=center>No Referensi</th>      
            <th align=center>Dibuat</th>      
        ";

	$countApprove = getCountApproval('BOR',$kdorg);	
	for($i=1;$i<=$countApprove;$i++){
		$stream.= "<th colspan=2 style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</th>";
	}

$stream.="</tr></thead><tbody>";

$str = "select * from " . $dbname . ".keu_jurnaldt_vw  where kodeorg='" . $kdorg . "' and 
		tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'  and noreferensi like '%/BOR/%' and debet>0 order by nojurnal asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $data[$bar['noreferensi']][$bar['nojurnal']][$bar['kodeblok']] = $bar['kodeblok'];
    $tanggal[$bar['noreferensi']][$bar['nojurnal']][$bar['kodeblok']] = $bar['tanggal'];
    $noakun[$bar['noreferensi']][$bar['nojurnal']][$bar['kodeblok']] = $bar['noakun'];
    @$jumlah[$bar['noreferensi']][$bar['nojurnal']][$bar['kodeblok']] += $bar['jumlah'];
}

$str = "select * from " . $dbname . ".kebun_aktifitas  where kodeorg='" . $kdorg . "' and 
		tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'  and notransaksi like '%BOR%' and jurnal='1' 
		and statuspersetujuan='1' and nopengajuan!=''";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $nopengajuan[$bar['notransaksi']] = $bar['nopengajuan'];
    $updateby[$bar['notransaksi']] = $bar['updateby'];
    $lastupdate[$bar['notransaksi']] = $bar['lastupdate'];
	#$tanggal[$bar['nopengajuan']][$bar['notransaksi']]=$bar['tanggal'];
}

$str = "select * from " . $dbname . ".kebun_kehadiran_vw where notransaksi in (select notransaksi from " . $dbname . ".kebun_aktifitas  where kodeorg='" . $kdorg . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'  and notransaksi like '%BOR%' and jurnal='1' and statuspersetujuan='1' and nopengajuan!='')";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$insentif[$bar['notransaksi']][$bar['kodeorg']] += $bar['insentif'];
    @$hk[$bar['notransaksi']][$bar['kodeorg']] += count($bar['karyawanid']);
    @$hasilkerja[$bar['notransaksi']][$bar['kodeorg']] += $bar['hasilkerja'];
}
$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);

$no='';
foreach ($data as $notran => $valjurnal){
	foreach ($valjurnal as $jurnal => $valblok){
		foreach ($valblok as $blok => $kdblok){
			$no++;
			$i='';
			if($jumlah[$notran][$jurnal][$blok]-$insentif[$notran][$blok] !=0){
				$i=" style=background-color:red title=\"Nilai Jurnal vs Nilai Transaksi Tidak Sama !!!\"";
			}
			
			$nmtt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blok."'");
			$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$noakun[$notran][$jurnal][$blok]."'");
			$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$updateby[$notran]."'");
			
			
			$stream.="<tr class=rowcontent>
			<td rowspan='2' valign=top align=center>" . $no . "</td>
			<td rowspan='2' valign=top align=center>" . $kdorg. "</td>
			<td rowspan='2' valign=top align=center>" . $jurnal. "</td>
			<td rowspan='2' valign=top align=center>" . $tanggal[$notran][$jurnal][$blok]. "</td>
			<td rowspan='2' valign=top align=right>" . $hk[$notran][$blok]. "</td>
			<td rowspan='2' valign=top align=right>" . @number_format($hasilkerja[$notran][$blok],2). "</td>
			<td rowspan='2' valign=top align=right ".$i.">" . @number_format($insentif[$notran][$blok]). "</td>
			<td rowspan='2' valign=top align=center>" . $blok. "</td>
			<td rowspan='2' valign=top align=center>" . $nmtt[$blok]. "</td>
			<td rowspan='2' valign=top align=center>" . $nmakun[$noakun[$notran][$jurnal][$blok]]. "</td>
			<td rowspan='2' valign=top align=center style=display:none>" . $nopengajuan[$notran]. "</td>
			<td rowspan='2' valign=top align=center>" . $notran. "</td>
			<td align=center valign=top>" . $nmkary[$updateby[$notran]]. "</td>
			";
			
			@$ttlhk+=$hk[$notran][$blok];
			@$ttlhsl+=$hasilkerja[$notran][$blok];
			@$ttlinst+=$insentif[$notran][$blok];
			
			
			for($i=1;$i<=$countApprove;$i++){
				$arrApp = detailApprove($i,$nopengajuan[$notran],'BOR');
				if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
					$tngl='';
				}else{
					$tngl=waktunormal($arrApp['tanggal']);
				}
				
				if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
					$stream.= "<td valign=top style='text-align:center'>".$arrApp['nama']."
							   <br><b>".$arrHsl[$arrApp['status']]."</b></td>
							   <td valign=top style='text-align:center'>".$tngl."</td>
							";
				}else{
					$stream.= "<td>&nbsp;</td>";
					$stream.= "<td>&nbsp;</td>";
				}
			}
		
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>
						<td align=center>" . $lastupdate[$notran]. "</td>";
			for($i=1;$i<=$countApprove;$i++){
				$arrApp = detailApprove($i,$nopengajuan[$notran],'BOR');
				if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
					$stream.= "<td valign=top style='text-align:center' colspan=2>".$arrApp['komentar']."</td>
							";
				}else{
					$stream.= "<td colspan=2>&nbsp;</td>";
				}
			}
			
			$stream.= "</tr>";
		}
	}
}

$stream.="<tr class=rowcontent>
			<td colspan=4 align=center>T O T A L</td>
			<td align=right>".@number_format($ttlhk,2)."</td>
			<td align=right>".@number_format($ttlhsl,2)."</td>
			<td align=right>".@number_format($ttlinst)."</td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			
			";
			for($i=1;$i<=$countApprove;$i++){
				$stream.= "<td colspan=2 style='text-align:center'></td>";
			}
$stream.= "</tr>";

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
        $tempnm = explode("/",$_SERVER['PHP_SELF']);
		$nop_ = substr($tempnm[2],0,strripos($tempnm[2],'.'));
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