<?php

require_once('master_validation.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$pt = checkPostGet('pt', '');

$where='';
if($pt!=''){
	$where.=" and b.alokasi ='".$pt."'";	
}

######################################
############# prepare data ###########
######################################

#bentuk data
$str = "select a.*, c.namakegiatan,c.satuan,c.kelompok,c.noakun, b.alokasi  from " . $dbname . ".kebun_5premibkm a
		left join setup_kegiatan c on a.kodekegiatan=c.kodekegiatan 
		left join organisasi b on a.unit=b.kodeorganisasi
		where 1=1 ".$where." and a.unit like '" . $kdorg . "%'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $listpt[$bar['alokasi']] = $bar['alokasi'];
    $unit[$bar['unit']] = $bar['unit'];
    $namakeg[$bar['namakegiatan']] = $bar['namakegiatan'];
    $kodekeg[$bar['kodekegiatan']] = $bar['kodekegiatan'];
	
    $llunit[$bar['alokasi']][$bar['unit']] = $bar['unit'];
    $listkodekegiatan[$bar['alokasi']][$bar['unit']][$bar['kodekegiatan']] = $bar['kodekegiatan'];
    $listnamakegiatan[$bar['alokasi']][$bar['unit']][$bar['kodekegiatan']] = $bar['namakegiatan'];
    $listsatuan[$bar['alokasi']][$bar['unit']][$bar['kodekegiatan']] = $bar['satuan'];
    $basis[$bar['alokasi']][$bar['unit']][$bar['kodekegiatan']] = $bar['basis'];
    $premibasis[$bar['alokasi']][$bar['unit']][$bar['kodekegiatan']] = $bar['premibasis'];
    $premilebihbasis[$bar['alokasi']][$bar['unit']][$bar['kodekegiatan']] = $bar['premilebihbasis'];
    $kelompok[$bar['alokasi']][$bar['unit']][$bar['kodekegiatan']] = $bar['kelompok'];
    $noakun[$bar['alokasi']][$bar['unit']][$bar['kodekegiatan']] = $bar['noakun'];

}


if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}
$stream.="
    <thead>
        <tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>" . $_SESSION['lang']['pt'] . "</td>
            <td align=center>" . $_SESSION['lang']['unit'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['kelompok'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['noakun'] . "</td>
            <td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
            <td align=center>" . $_SESSION['lang']['kodekegiatan'] . "</td>
            <td align=center>" . $_SESSION['lang']['namakegiatan'] . "</td>
            <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center>" . $_SESSION['lang']['basic'] . "</td>
            <td align=center>" . $_SESSION['lang']['premibasis'] . "<br>Rp</td>    
            <td align=center>" . $_SESSION['lang']['premlebihbasis'] . "<br>Rp / Sat</td>    
             
        </tr>";
$stream.="</thead>
 <tbody>";
$namaAkun=makeOption($dbname,'keu_5akun','noakun,namaakun');
@$jumkodekeg = count($kodekeg);
if ($jumkodekeg > 0) {
    array_multisort($listpt, SORT_ASC);
    array_multisort($unit, SORT_ASC);
    array_multisort($kodekeg, SORT_ASC);
} else {
    exit("Warning : Data kosong");
}

foreach($listpt as $lpt){
	foreach($unit as $lunit){
		$llunit[$lpt][$lunit]= isset($llunit[$lpt][$lunit])?$llunit[$lpt][$lunit]:'';
		if($llunit[$lpt][$lunit]!=''){
			foreach($kodekeg as $lkodekeg){
				$listkodekegiatan[$lpt][$lunit][$lkodekeg]= isset($listkodekegiatan[$lpt][$lunit][$lkodekeg])?$listkodekegiatan[$lpt][$lunit][$lkodekeg]:'';
				if($listkodekegiatan[$lpt][$lunit][$lkodekeg]!=''){
					$no+=1;
					$stream.="<tr class=rowcontent>
					<td align=center>" . $no . "</td>
					<td align=center>" . $lpt. "</td>
					<td align=center>" . $lunit. "</td>
					<td align=center>" . $kelompok[$lpt][$lunit][$lkodekeg]. "</td>
					<td align=center>" . $noakun[$lpt][$lunit][$lkodekeg]. "</td>
					<td>" . $namaAkun[$noakun[$lpt][$lunit][$lkodekeg]]. "</td>
					<td align=center>" . $listkodekegiatan[$lpt][$lunit][$lkodekeg]. "</td>
					<td align=left>" . $listnamakegiatan[$lpt][$lunit][$lkodekeg]. "</td>
					<td align=left>" . $listsatuan[$lpt][$lunit][$lkodekeg]. "</td>
					<td align=right>" . number_format($basis[$lpt][$lunit][$lkodekeg],2). "</td>
					<td align=right>" . number_format($premibasis[$lpt][$lunit][$lkodekeg],2). "</td>
					<td align=right>" . number_format($premilebihbasis[$lpt][$lunit][$lkodekeg],2). "</td>
					";
				}
			}
		}
	}
	
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
        $tglSkrg = date("Ymd");
        $nop_ = getMenu('kebun_2daftarpremi')." ". $kdorg;
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