<?php

require_once('master_validation.php');
require_once('lib/zLib.php');
$tgl1 = tanggalsystem(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystem(checkPostGet('tgl2', ''));
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

######################################
############# prepare data ###########
######################################
#kebun_spb_vw
#kebun_rekappnn
#bentuk data blok dari rekap panen
$str = "select * from " . $dbname . ".kebun_pusingan_vw where "
        . " unit='" . $kdorg . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kdblok[$bar['blok']] = $bar['blok'];
    $kddivisi[$bar['divisi']] = $bar['divisi'];
    $tahuntanam[$bar['tahuntanam']] = $bar['tahuntanam'];

    $listtahuntanam[$bar['divisi']][$bar['tahuntanam']] = $bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['blok'];

    $angka[$bar['divisi']][$bar['tahuntanam']][$bar['blok']][$bar['tanggal']] = $bar['angka'];
    $ket[$bar['divisi']][$bar['tahuntanam']][$bar['blok']][$bar['tanggal']] = $bar['keterangan'];

    $luas[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['luasareaproduktif'];
    $bbt[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['jenisbibit'];
    $pkk[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['jumlahpokok'];
}

//ambil seksi panen
$str = "select * from " . $dbname . ".kebun_5seksipanen where ". " divisi like '" . $kdorg . "%'";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $seksi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['seksi'];
}

##cari max
$str = "select max(angka) as angka,divisi,tahuntanam,tanggal from " . $dbname . ".kebun_pusingan_vw where "
        . " unit='" . $kdorg . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' "
        . " group by divisi,tahuntanam,tanggal";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $angkatt[$bar['divisi']][$bar['tahuntanam']][$bar['tanggal']] = $bar['angka'];
}

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
    $stream = "<table class=sortable cellspacing=1>";
}

$span = count($rangetanggal);


$stream.="
    <thead>
        <tr class=rowheader>
            <td align=center  rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center  rowspan='2'>" . $_SESSION['lang']['divisi'] . "</td>
            <td align=center  rowspan='2'>" . $_SESSION['lang']['seksi'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['blok'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center  rowspan='2'>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center  rowspan='2'>" . $_SESSION['lang']['jenisbibit'] . "</td>    
            <td align=center  rowspan='2'>Jumlah Pokok</td>        
            <td align=center  rowspan='2'>SPH</td>      
                
            <td align=center colspan=" . $span . ">" . $_SESSION['lang']['tanggal'] . "</td> 
        </tr>";
$stream.="<tr>";
foreach ($rangetanggal as $listtanggal => $tgl) {
    $mggu = date('D', strtotime($tgl));
    if ($mggu == 'Sun') {
        $stream.="<td align=center><font color=red>" . substr($tgl, 8, 2) . "/" . substr($tgl, 5, 2) . "</font></td>";
    } else {
        $stream.="<td align=center>" . substr($tgl, 8, 2) . "/" . substr($tgl, 5, 2) . "</td>";
    }
}
$stream.="
        </tr>
    </thead>
 <tbody>";



$romawi = array("1"=>"I","2"=>"II","3"=>"III","4"=>"IV","5"=>"V","6"=>"VI","7"=>"VII","8"=>"VIII","9"=>"IX","10"=>"X","11"=>"XI","12"=>"XII","13"=>"XIII","14"=>"XIV","15"=>"XV","16"=>"XVI","17"=>"XVII","18"=>"XVIII","19"=>"XIX","20"=>"XX","A1"=>"Ip","A2"=>"IIp","A3"=>"IIIp");


@$jumdiv = count($kddivisi);
if ($jumdiv > 0) {
    // array_multisort($kddivisi, SORT_ASC);
    array_multisort($seksi, SORT_ASC);
    // array_multisort($tahuntanam, SORT_ASC);
    // array_multisort($kdblok, SORT_ASC);
} else {
    exit("error:Data kosong");
}

foreach ($kddivisi as $divisi) {

    foreach ($tahuntanam as $thntnm) {
        $listtahuntanam[$divisi][$thntnm] = isset($listtahuntanam[$divisi][$thntnm]) ? $listtahuntanam[$divisi][$thntnm] : '';
        if ($listtahuntanam[$divisi][$thntnm] != '') {
            foreach ($kdblok as $blok) {
                $listblok[$divisi][$thntnm][$blok] = isset($listblok[$divisi][$thntnm][$blok]) ? $listblok[$divisi][$thntnm][$blok] : '';
                if ($listblok[$divisi][$thntnm][$blok] != '') {
                    $no+=1;
                    $stream.="<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
						<td align=center>" . $romawi[intval(substr($divisi, 5, 1))] . "</td>
                        <td align=center>" . $seksi[$divisi][$thntnm][$blok] . "</td>   
						<td align=center>" . $listblok[$divisi][$thntnm][$blok] . "</td>    
                        <td align=center>" . $listtahuntanam[$divisi][$thntnm] . "</td>
                        <td align=right>" . number_format($luas[$divisi][$thntnm][$blok],2) . "</td>    
                        <td align=left>" . $bbt[$divisi][$thntnm][$blok] . "</td>    
                        <td align=right>" . number_format($pkk[$divisi][$thntnm][$blok]) . "</td>    
                        <td align=right>" . number_format($pkk[$divisi][$thntnm][$blok] / $luas[$divisi][$thntnm][$blok]) . "</td>    
                    ";
                    foreach ($rangetanggal as $listtanggal => $tgl) {
                        $ket[$divisi][$thntnm][$blok][$tgl] = isset($ket[$divisi][$thntnm][$blok][$tgl]) ? $ket[$divisi][$thntnm][$blok][$tgl] : '';
                        $angka[$divisi][$thntnm][$blok][$tgl] = isset($angka[$divisi][$thntnm][$blok][$tgl]) ? $angka[$divisi][$thntnm][$blok][$tgl] : '';
                        if ($ket[$divisi][$thntnm][$blok][$tgl] == 'P' && $angka[$divisi][$thntnm][$blok][$tgl] == '1') {
                            $bgcolor = "bgcolor=blue";
                        } else if ($ket[$divisi][$thntnm][$blok][$tgl] == 'P' && $angka[$divisi][$thntnm][$blok][$tgl] > '1') {
                            $bgcolor = "bgcolor=red";
                        } else {
                            $bgcolor = "";
                        }
                        $angka[$divisi][$thntnm][$blok][$tgl] = isset($angka[$divisi][$thntnm][$blok][$tgl]) ? $angka[$divisi][$thntnm][$blok][$tgl] : '';
                        $stream.="
                                    <td align=center " . $bgcolor . ">" . $angka[$divisi][$thntnm][$blok][$tgl] . "</td>
                                ";
                    }

                    @$luastt[$divisi][$thntnm]+=$luas[$divisi][$thntnm][$blok];
                    @$pkktt[$divisi][$thntnm]+=$pkk[$divisi][$thntnm][$blok];
                }
            }
            $stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=5>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['tahuntanam'] . "  " . $thntnm . "</td>
                    <td align=right>" . number_format($luastt[$divisi][$thntnm], 2) . "</td>
                    <td></td>
                    <td align=right>" . number_format($pkktt[$divisi][$thntnm]) . "</td>
                    <td align=right>" . number_format($pkktt[$divisi][$thntnm] / $luastt[$divisi][$thntnm]) . "</td>
                    ";

            ##max per tt
            foreach ($rangetanggal as $listtanggal => $tgl) {
                $angkatt[$divisi][$thntnm][$tgl] = isset($angkatt[$divisi][$thntnm][$tgl]) ? $angkatt[$divisi][$thntnm][$tgl] : '';
                $stream.="
                        <td align=center>" . $angkatt[$divisi][$thntnm][$tgl] . "</td>
                        ";
            }
            $stream.="
                </tr>
                ";
            @$luasdiv[$divisi]+=$luastt[$divisi][$thntnm];
            @$pkkdiv[$divisi]+=$pkktt[$divisi][$thntnm];
        }
    }
    $stream.="
        <tr bgcolor=#009999>
            <td align=left colspan=5>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['divisi'] . " " . $divisi . "</td>
            <td align=right>" . number_format($luasdiv[$divisi], 2) . "</td>
            <td></td>
            <td align=right>" . number_format($pkkdiv[$divisi]) . "</td>
            <td align=right>" . number_format($pkkdiv[$divisi] / $luasdiv[$divisi]) . "</td>    
       
    ";
    ##max per tt
    foreach ($rangetanggal as $listtanggal => $tgl) {
        $angkadiv[$divisi][$tgl] = isset($angkadiv[$divisi][$tgl]) ? $angkadiv[$divisi][$tgl] : '';
        $stream.="
                <td align=center>" . $angkadiv[$divisi][$tgl] . "</td>
                ";
    }
    $stream.="
                </tr>
                ";
    @$gtluas+=$luasdiv[$divisi];
    @$gtpkk+=$pkkdiv[$divisi];
}
$stream.="
        <tr bgcolor=#00B366>
            <td align=left colspan=5>" . $_SESSION['lang']['grnd_total'] . " " . $kdorg . "</td>
            <td align=right>" . number_format($gtluas, 2) . "</td>
            <td></td>
            <td align=right>" . number_format($gtpkk) . "</td>
            <td align=right>" . number_format($gtpkk / $gtluas) . "</td>   
        
    ";
##max per tt
foreach ($rangetanggal as $listtanggal => $tgl) {
    $angkaunit[$tgl] = isset($angkaunit[$tgl]) ? $angkaunit[$tgl] : '';
    $stream.="
            <td align=center>" . $angkaunit[$tgl] . "</td>
            ";
}
$stream.="
            </tr><thead>
            ";

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
?>