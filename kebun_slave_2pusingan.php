<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$tgl1 = tanggalsystem(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystem(checkPostGet('tgl2', ''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$divisi = checkPostGet('divisi', '');

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
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

$whd="";
if($divisi!=''){
	$whd=" and a.divisi like '".$divisi."%'";
}


######################################
############# prepare data ###########
######################################
#kebun_spb_vw
#kebun_rekappnn_vw
#bentuk data blok dari rekap panen
$str = "select a.*,b.seksi from " . $dbname . ".kebun_pusingan_vw a 
left join " . $dbname . ".kebun_5seksipanen b on a.blok=b.blok where "
. " a.unit='" . $kdorg . "' ".$whd." and a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'  and a.luasareaproduktif >'0' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kdblok[$bar['blok']] = $bar['blok'];
    $kddivisi[$bar['divisi']] = $bar['divisi'];
    $tahuntanam[$bar['tahuntanam']] = $bar['tahuntanam'];
    $sksi[$bar['seksi']] = $bar['seksi'];

    $listtahuntanam[$bar['divisi']][$bar['seksi']][$bar['tahuntanam']] = $bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['seksi']][$bar['tahuntanam']][$bar['blok']] = $bar['blok'];

    $angka[$bar['divisi']][$bar['seksi']][$bar['tahuntanam']][$bar['blok']][$bar['tanggal']] = $bar['angka'];
    $ket[$bar['divisi']][$bar['seksi']][$bar['tahuntanam']][$bar['blok']][$bar['tanggal']] = $bar['keterangan'];

    $luas[$bar['divisi']][$bar['seksi']][$bar['tahuntanam']][$bar['blok']] = $bar['luasareaproduktif'];
    $bbt[$bar['divisi']][$bar['seksi']][$bar['tahuntanam']][$bar['blok']] = $bar['jenisbibit'];
    $pkk[$bar['divisi']][$bar['seksi']][$bar['tahuntanam']][$bar['blok']] = $bar['jumlahpokok'];
    $seksii[$bar['divisi']][$bar['seksi']][$bar['tahuntanam']][$bar['blok']] = $bar['seksi'];
}

$hapnn=$jjgpnn=array();
$str = "select * from " . $dbname . ".kebun_rekappnn a  where a.divisi like '" . $kdorg . "%' ".$whd." and a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'";
$res=fetchdata($str);
foreach($res as $bar){
	$hapnn[$bar['blok']][$bar['tanggal']] += $bar['luaspanen'];
	$jjgpnn[$bar['blok']][$bar['tanggal']] += $bar['jjgpanen'];
}





##cari max
$str = "select max(angka) as angka,a.divisi,a.tahuntanam,a.tanggal,b.seksi from " . $dbname . ".kebun_pusingan_vw a left join " . $dbname . ".kebun_5seksipanen b on a.blok=b.blok  where "
        . " a.unit='" . $kdorg . "' and a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' "
        . " group by divisi,seksi,tanggal";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $angkatt[$bar['divisi']][$bar['seksi']][$bar['tanggal']] = $bar['angka'];
}

// echo "<pre>";
// print_r($angkatt);
// echo "</pre>";

$str = "select max(angka) as angka,divisi,tanggal from " . $dbname . ".kebun_pusingan_vw where "
        . " unit='" . $kdorg . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' "
        . " group by divisi,tanggal";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $angkadiv[$bar['divisi']][$bar['tanggal']] = $bar['angka'];
}

$str = "select max(angka) as angka,tanggal from " . $dbname . ".kebun_pusingan_vw where "
        . " unit='" . $kdorg . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' "
        . " group by tanggal";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $angkaunit[$bar['tanggal']] = $bar['angka'];
}


//[$divisi][$thntnm][$tgl]

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1 cellpadding=5>";
}

$span = count($rangetanggal);


$stream.="
    <thead>
        <tr class=rowheader>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['divisi'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['seksi'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['luas'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['jenisbibit'] . "</th>    
            <th align=center  rowspan='2'>Jumlah Pokok</th>        
            <th align=center  rowspan='2'>SPH</th>      
                
            <th align=center colspan=" . $span . ">" . $_SESSION['lang']['tanggal'] . "</th> 
        </tr>";
$stream.="<tr>";
foreach ($rangetanggal as $listtanggal => $tgl) {
    $mggu = date('D', strtotime($tgl));
    if ($mggu == 'Sun') {
        $stream.="<th align=center><font color=red>" . substr($tgl, 8, 2) . "</font></th>";
    } else {
        $stream.="<th align=center>" . substr($tgl, 8, 2). "</th>";
    }
}
$stream.="
        </tr>
    </thead>
 <tbody>";



$romawi = array("01"=>"I","02"=>"II","03"=>"III","04"=>"IV","05"=>"V","06"=>"VI","07"=>"VII","08"=>"VIII","09"=>"IX","10"=>"X","11"=>"XI","12"=>"XII","13"=>"XIII","14"=>"XIV","15"=>"XV","16"=>"XVI","17"=>"XVII","18"=>"XVIII","19"=>"XIX","20"=>"XX","A1"=>"Plasma I","A2"=>"Plasma II","A3"=>"Plasma III");


@$jumdiv = count($kddivisi);
if ($jumdiv > 0) {
    array_multisort($kddivisi, SORT_ASC);
    array_multisort($sksi, SORT_ASC);
    array_multisort($tahuntanam, SORT_ASC);
    array_multisort($kdblok, SORT_ASC);
} else {
    exit("error : Data kosong");
}

foreach ($kddivisi as $divisi) {
   foreach ($sksi as $seksi) {
     $listblok[$divisi][$seksi] = isset($listblok[$divisi][$seksi]) ? $listblok[$divisi][$seksi] : '';
    foreach ($tahuntanam as $thntnm) {
        $listtahuntanam[$divisi][$seksi][$thntnm] = isset($listtahuntanam[$divisi][$seksi][$thntnm]) ? $listtahuntanam[$divisi][$seksi][$thntnm] : '';
        if ($listtahuntanam[$divisi][$seksi][$thntnm] != '') {
            foreach ($kdblok as $blok) {
                $listblok[$divisi][$seksi][$thntnm][$blok] = isset($listblok[$divisi][$seksi][$thntnm][$blok]) ? $listblok[$divisi][$seksi][$thntnm][$blok] : '';
                if ($listblok[$divisi][$seksi][$thntnm][$blok] != '') {
                    $no+=1;
                    $stream.="<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
						<td align=center>" . $romawi[substr($divisi, 4, 2)] . "</td>
                        <td align=center>" . $seksii[$divisi][$seksi][$thntnm][$blok] . "</td>   
						<td align=center>" . $namaOrg[$listblok[$divisi][$seksi][$thntnm][$blok]] . "</td>    
                        <td align=center>" . $listtahuntanam[$divisi][$seksi][$thntnm] . "</td>
                        <td align=right>" . number_format($luas[$divisi][$seksi][$thntnm][$blok],2) . "</td>    
                        <td align=left>" . $bbt[$divisi][$seksi][$thntnm][$blok] . "</td>    
                        <td align=right>" . number_format($pkk[$divisi][$seksi][$thntnm][$blok]) . "</td>    
                        <td align=right>" . number_format($pkk[$divisi][$seksi][$thntnm][$blok] / $luas[$divisi][$seksi][$thntnm][$blok]) . "</td>    
                    ";
                    foreach ($rangetanggal as $listtanggal => $tgl) {
                        $ket[$divisi][$seksi][$thntnm][$blok][$tgl] = isset($ket[$divisi][$seksi][$thntnm][$blok][$tgl]) ? $ket[$divisi][$seksi][$thntnm][$blok][$tgl] : '';
                        $angka[$divisi][$seksi][$thntnm][$blok][$tgl] = isset($angka[$divisi][$seksi][$thntnm][$blok][$tgl]) ? $angka[$divisi][$seksi][$thntnm][$blok][$tgl] : '';
						
                        if ($ket[$divisi][$seksi][$thntnm][$blok][$tgl] == 'P' && $angka[$divisi][$seksi][$thntnm][$blok][$tgl] == '1') {
                            $bgcolor = "style=background-color:#067f02";
                        } else if ($ket[$divisi][$seksi][$thntnm][$blok][$tgl] == 'P' && $angka[$divisi][$seksi][$thntnm][$blok][$tgl] > '0') {
                            $bgcolor = "style=background-color:red";
                        } else {
                            $bgcolor = "";
                        }
                        $angka[$divisi][$seksi][$thntnm][$blok][$tgl] = isset($angka[$divisi][$seksi][$thntnm][$blok][$tgl]) ? $angka[$divisi][$seksi][$thntnm][$blok][$tgl] : '';
						if($hapnn[$blok][$tgl]>0){$hapanen="h:".numb_format($hapnn[$blok][$tgl],2);}else{$hapanen="";}
						if($jjgpnn[$blok][$tgl]>0){$jjgpanen="j:".numb_format($jjgpnn[$blok][$tgl]);}else{$jjgpanen="";}
						
                        $stream.="<td align=center " . $bgcolor . ">" . $angka[$divisi][$seksi][$thntnm][$blok][$tgl] . "
								<br><font style=font-size:9px;>".$hapanen."</font>
								<br><font style=font-size:9px;>".$jjgpanen."</font>
									</td>";
                    }
                    @$luastt[$divisi][$seksi]+=$luas[$divisi][$seksi][$thntnm][$blok];
                    @$pkktt[$divisi][$seksi]+=$pkk[$divisi][$seksi][$thntnm][$blok];
                }
            }
        }
    }
		if ($luastt[$divisi][$seksi]!=0){
			$stream.="
				<tr  style=background-color:#80FFFE>
					<td colspan=5>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['seksi'] . "  " . $seksi . "</td>
					<td align=right>" . number_format($luastt[$divisi][$seksi], 2) . "</td>
					<td></td>
					<td align=right>" . number_format($pkktt[$divisi][$seksi]) . "</td>
					<td align=right>" . number_format($pkktt[$divisi][$seksi] / $luastt[$divisi][$seksi]) . "</td>
					";

			##max per tt
			foreach ($rangetanggal as $listtanggal => $tgl) {
				$angkatt[$divisi][$seksi][$tgl] = isset($angkatt[$divisi][$seksi][$tgl]) ? $angkatt[$divisi][$seksi][$tgl] : '';
				$stream.="<td align=center>" . $angkatt[$divisi][$seksi][$tgl] . "</td>";
			}
			$stream.="</tr>";
			@$luasdiv[$divisi]+=$luastt[$divisi][$seksi];
			@$pkkdiv[$divisi]+=$pkktt[$divisi][$seksi];
		}
   }
    $stream.="
        <tr style=background-color:#009999>
            <td align=left colspan=5>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['divisi'] . " " . $divisi . "</td>
            <td align=right>" . number_format($luasdiv[$divisi], 2) . "</td>
            <td></td>
            <td align=right>" . number_format($pkkdiv[$divisi]) . "</td>
            <td align=right>" . number_format($pkkdiv[$divisi] / $luasdiv[$divisi]) . "</td>    
		";
    ##max per tt
    foreach ($rangetanggal as $listtanggal => $tgl) {
        $angkadiv[$divisi][$tgl] = isset($angkadiv[$divisi][$tgl]) ? $angkadiv[$divisi][$tgl] : '';
        $stream.="<td align=center>" . $angkadiv[$divisi][$tgl] . "</td>";
    }
    $stream.="</tr>";
    @$gtluas+=$luasdiv[$divisi];
    @$gtpkk+=$pkkdiv[$divisi];
}
$stream.="
        <tr style=background-color:#00B366>
            <td align=left colspan=5>" . $_SESSION['lang']['grnd_total'] . " " . $kdorg . "</td>
            <td align=right>" . number_format($gtluas, 2) . "</td>
            <td></td>
            <td align=right>" . number_format($gtpkk) . "</td>
            <td align=right>" . number_format($gtpkk / $gtluas) . "</td>   
        
    ";
##max per tt
foreach ($rangetanggal as $listtanggal => $tgl) {
    $angkaunit[$tgl] = isset($angkaunit[$tgl]) ? $angkaunit[$tgl] : '';
    $stream.="<td align=center>" . $angkaunit[$tgl] . "</td>";
}
$stream.="</tr><thead>";

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
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "laporan_rekap_panen_per_blok" . $kdorg;
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
function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>