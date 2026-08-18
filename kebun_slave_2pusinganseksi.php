<?php

require_once('master_validation.php');
require_once('lib/zLib.php');
$tgl1 = tanggalsystem(checkPostGet('tgl1seksi', ''));
$tgl2 = tanggalsystem(checkPostGet('tgl2seksi', ''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorgseksi', '');

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
#kebun_rekappnn_vw
#bentuk data blok dari rekap panen
$str = "select a.*,b.seksi from " . $dbname . ".kebun_pusingan_vw a left join ".$dbname.".kebun_5seksipanen b on a.blok=b.blok where "
        . " unit='" . $kdorg . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' order by seksi asc";
		// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kdblok[$bar['blok']] = $bar['blok'];
    $kddivisi[$bar['divisi']] = $bar['divisi'];
  
    $listblok[$bar['divisi']][$bar['blok']] = $bar['blok'];

    $angka[$bar['divisi']][$bar['blok']][$bar['tanggal']] = $bar['angka'];
    $ket[$bar['divisi']][$bar['blok']][$bar['tanggal']] = $bar['keterangan'];

    $luas[$bar['divisi']][$bar['blok']] = $bar['luasareaproduktif'];
    $bbt[$bar['divisi']][$bar['blok']] = $bar['jenisbibit'];
    $pkk[$bar['divisi']][$bar['blok']] = $bar['jumlahpokok'];
}

//ambil seksi panen
$str = "select * from " . $dbname . ".kebun_5seksipanen where ". " divisi like '" . $kdorg . "%'";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $seksi[$bar['divisi']][$bar['blok']] = $bar['seksi'];
}

##cari max
$str = "select max(angka) as angka,divisi,tahuntanam,tanggal from " . $dbname . ".kebun_pusingan_vw where "
        . " unit='" . $kdorg . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' "
        . " group by divisi,tahuntanam,tanggal";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $angkatt[$bar['divisi']][$bar['tanggal']] = $bar['angka'];
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


//[$divisi][$tgl]

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
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
        $stream.="<th align=center><font color=red>" . substr($tgl, 8, 2) . "/" . substr($tgl, 5, 2) . "</font></th>";
    } else {
        $stream.="<th align=center>" . substr($tgl, 8, 2) . "/" . substr($tgl, 5, 2) . "</th>";
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
    // array_multisort($kdblok, SORT_ASC);
} else {
    exit("error : Data kosong");
}

foreach ($kddivisi as $divisi) {
            foreach ($kdblok as $blok) {
                $listblok[$divisi][$blok] = isset($listblok[$divisi][$blok]) ? $listblok[$divisi][$blok] : '';
                if ($listblok[$divisi][$blok] != '') {
                    $no+=1;
                    $stream.="<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
						<td align=center>" . $romawi[substr($divisi, 4, 2)] . "</td>
                        <td align=center>" . $seksi[$divisi][$blok] . "</td>   
						<td align=center>" . $listblok[$divisi][$blok] . "</td>    
                        <td align=center>" . $a[$divisi] . "</td>
                        <td align=right>" . @number_format($luas[$divisi][$blok],2) . "</td>    
                        <td align=left>" . $bbt[$divisi][$blok] . "</td>    
                        <td align=right>" . @number_format($pkk[$divisi][$blok]) . "</td>    
                        <td align=right>" . @number_format($pkk[$divisi][$blok] / $luas[$divisi][$blok]) . "</td>    
                    ";
                    foreach ($rangetanggal as $listtanggal => $tgl) {
                        $ket[$divisi][$blok][$tgl] = isset($ket[$divisi][$blok][$tgl]) ? $ket[$divisi][$blok][$tgl] : '';
                        $angka[$divisi][$blok][$tgl] = isset($angka[$divisi][$blok][$tgl]) ? $angka[$divisi][$blok][$tgl] : '';
                        if ($ket[$divisi][$blok][$tgl] == 'P' && $angka[$divisi][$blok][$tgl] == '1') {
                            $bgcolor = "bgcolor=#067f02";
                        } else if ($ket[$divisi][$blok][$tgl] == 'P' && $angka[$divisi][$blok][$tgl] > '1') {
                            $bgcolor = "bgcolor=red";
                        } else {
                            $bgcolor = "";
                        }
                        $angka[$divisi][$blok][$tgl] = isset($angka[$divisi][$blok][$tgl]) ? $angka[$divisi][$blok][$tgl] : '';
                        $stream.="
							<td align=center " . $bgcolor . ">" . $angka[$divisi][$blok][$tgl] . "</td>
							";
                    }

                    @$luastt[$divisi]+=$luas[$divisi][$blok];
                    @$pkktt[$divisi]+=$pkk[$divisi][$blok];
                }
            }
           
            @$luasdiv[$divisi]+=$luastt[$divisi];
            @$pkkdiv[$divisi]+=$pkktt[$divisi];
       
   
    $stream.="
        <tr bgcolor=#009999>
            <td align=left colspan=5>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['divisi'] . " " . $divisi . "</td>
            <td align=right>" . @number_format($luasdiv[$divisi], 2) . "</td>
            <td></td>
            <td align=right>" . @number_format($pkkdiv[$divisi]) . "</td>
            <td align=right>" . @number_format($pkkdiv[$divisi] / $luasdiv[$divisi]) . "</td>    
       
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
            <td align=right>" . @number_format($gtluas, 2) . "</td>
            <td></td>
            <td align=right>" . @number_format($gtpkk) . "</td>
            <td align=right>" . @number_format($gtpkk / $gtluas) . "</td>   
        
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
        $nop_ = "pusingan_panen_" . $kdorg;
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