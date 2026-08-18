<?php

require_once('master_validation.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$pt = checkPostGet('pt', '');
$jenis = checkPostGet('jenis', '');

if($jenis==''){
	exit("Warning : Jenis harus di pilih.");
}

$where='';
if($pt!=''){
	$where.=" and a.kodept ='".$pt."'";	
}
$arrJab=array("0"=>"Driver","1"=>"Helper","2"=>"Operator");

######################################
############# prepare data ###########
######################################

#bentuk data

if($jenis=='KD'){
	$str = "select * from " . $dbname . ".vhc_5premikegiatan a
			left join vhc_kegiatan c on a.kodekegiatan=c.kodekegiatan 
			where 1=1 ".$where."";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$listpt[$bar['kodept']] = $bar['kodept'];
		$namakeg[$bar['namakegiatan']] = $bar['namakegiatan'];
		$kodekeg[$bar['kodekegiatan']] = $bar['kodekegiatan'];
		$posisi[$bar['posisi']] = $bar['posisi'];
		
		$listkodekegiatan[$bar['kodept']][$bar['kodekegiatan']][$bar['posisi']] = $bar['kodekegiatan'];
		$listnamakegiatan[$bar['kodept']][$bar['kodekegiatan']][$bar['posisi']] = $bar['namakegiatan'];
		$listsatuan[$bar['kodept']][$bar['kodekegiatan']][$bar['posisi']] = $bar['satuan'];
		$basis[$bar['kodept']][$bar['kodekegiatan']][$bar['posisi']] = $bar['basis'];
		$premibasis[$bar['kodept']][$bar['kodekegiatan']][$bar['posisi']] = $bar['premibasis'];
		$premilebihbasis[$bar['kodept']][$bar['kodekegiatan']][$bar['posisi']] = $bar['premilebihbasis'];
		$kelompok[$bar['kodept']][$bar['kodekegiatan']][$bar['posisi']] = $bar['tipe'];
		$noakun[$bar['kodept']][$bar['kodekegiatan']][$bar['posisi']] = $bar['noakun'];

	}
} else if($jenis=='AB'){
	$str2 = "select a.*,c.jenisvhc,c.namajenisvhc,c.kelompokvhc from " . $dbname . ".vhc_5premialatberat a
			left join vhc_5jenisvhc c on a.jenisvhc=c.jenisvhc 
			where 1=1 ".$where."";
	$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar2 = $res2->fetch()) {
		$listpt2[$bar2['kodept']] = $bar2['kodept'];
		$jnsvhc[$bar2['jenisvhc']] = $bar2['jenisvhc'];
		$listjnsvhc[$bar2['kodept']][$bar2['jenisvhc']] = $bar2['jenisvhc'];
		$posisi2[$bar2['posisi']] = $bar2['posisi'];
		$lisposisi[$bar2['kodept']][$bar2['jenisvhc']][$bar2['posisi']] = $bar2['posisi'];
		$listnamavhc[$bar2['kodept']][$bar2['jenisvhc']] = $bar2['namajenisvhc'];
		
		$lbasis[$bar2['kodept']][$bar2['jenisvhc']][$bar2['posisi']] = $bar2['basis'];
		$lpremibasis[$bar2['kodept']][$bar2['jenisvhc']][$bar2['posisi']] = $bar2['premibasis'];
		$lpremilebihbasis[$bar2['kodept']][$bar2['jenisvhc']][$bar2['posisi']] = $bar2['premilebihbasis'];
		
	}
}

#tampil untuk KD
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
            <td align=center width=50px>" . $_SESSION['lang']['kelompok'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['noakun'] . "</td>
            <td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
            <td align=center>" . $_SESSION['lang']['kodekegiatan'] . "</td>
            <td align=center>" . $_SESSION['lang']['namakegiatan'] . "</td>
            <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center>Posisi</td>
            <td align=center>" . $_SESSION['lang']['basic'] . "</td>
            <td align=center>" . $_SESSION['lang']['premibasis'] . "<br>Rp</td>    
            <td align=center>" . $_SESSION['lang']['premlebihbasis'] . "<br>Rp / Sat</td>    
             
        </tr>";
$stream.="</thead>
 <tbody>";
$namaAkun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$datapremikd=0;
$datapremikd=count(@$listpt);

// @$datapremikd = count($kodekeg);
// if ($datapremikd > 0) {
    // array_multisort($listpt, SORT_ASC);
    // array_multisort($unit, SORT_ASC);
    // array_multisort($kodekeg, SORT_ASC);
// } else {
    // exit("Warning : Data kosong");
// }

if($datapremikd>0){
	
	foreach($listpt as $lpt){
		foreach($kodekeg as $lkodekeg){
			foreach($posisi as $pss){
				$listkodekegiatan[$lpt][$lkodekeg][$pss]= isset($listkodekegiatan[$lpt][$lkodekeg][$pss])?$listkodekegiatan[$lpt][$lkodekeg][$pss]:'';
				if($listkodekegiatan[$lpt][$lkodekeg][$pss]!=''){
					$no+=1;
					$stream.="<tr class=rowcontent>
					<td align=center>" . $no . "</td>
					<td align=center>" . $lpt. "</td>
					<td align=center>" . $kelompok[$lpt][$lkodekeg][$pss]. "</td>
					<td align=center>" . $noakun[$lpt][$lkodekeg][$pss]. "</td>
					<td>" . $namaAkun[$noakun[$lpt][$lkodekeg][$pss]]. "</td>
					<td align=center>" . $listkodekegiatan[$lpt][$lkodekeg][$pss]. "</td>
					<td align=left>" . $listnamakegiatan[$lpt][$lkodekeg][$pss]. "</td>
					<td align=left>" . $listsatuan[$lpt][$lkodekeg][$pss]. "</td>
					<td align=left>" . $arrJab[$pss]. "</td>
					<td align=right>" . number_format($basis[$lpt][$lkodekeg][$pss],2). "</td>
					<td align=right>" . number_format($premibasis[$lpt][$lkodekeg][$pss],2). "</td>
					<td align=right>" . number_format($premilebihbasis[$lpt][$lkodekeg][$pss],2). "</td>
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


#tampil untuk AB
if ($proses == 'excel') {
    $tab = "<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<table class=sortable cellspacing=1>";
}
$tab.="
    <thead>
        <tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>" . $_SESSION['lang']['pt'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['jenisvch'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['namajenisvhc'] . "</td>
            <td align=center>" . $_SESSION['lang']['vhc_posisi'] . "</td>
            <td align=center>" . $_SESSION['lang']['basic'] . "<br>HM/KM</td>
            <td align=center>" . $_SESSION['lang']['premibasis'] . "<br>Rp</td>    
            <td align=center>" . $_SESSION['lang']['premlebihbasis'] . "<br>Rp / Sat</td>    
             
        </tr>";
$tab.="</thead>
 <tbody>";
 
$datapremiab=0;
$datapremiab=count(@$listpt2);
if($datapremiab>0){ 
 
$namaAkun=makeOption($dbname,'keu_5akun','noakun,namaakun');

    array_multisort($listpt2, SORT_ASC);
    array_multisort($jnsvhc, SORT_ASC);
    array_multisort($posisi2, SORT_ASC);



foreach($listpt2 as $lpt2){
	foreach($jnsvhc as $ljnsvhc){
		$listjnsvhc[$lpt2][$ljnsvhc]= isset($listjnsvhc[$lpt2][$ljnsvhc])?$listjnsvhc[$lpt2][$ljnsvhc]:'';
		if($listjnsvhc[$lpt2][$ljnsvhc]!=''){
			foreach($posisi2 as $lpos){
				$lisposisi[$lpt2][$ljnsvhc][$lpos]= isset($lisposisi[$lpt2][$ljnsvhc][$lpos])?$lisposisi[$lpt2][$ljnsvhc][$lpos]:'';
				if($lisposisi[$lpt2][$ljnsvhc][$lpos]!=''){
					$no+=1;
					$tab.="<tr class=rowcontent>
					<td align=center>" . $no . "</td>
					<td align=center>" . $lpt2. "</td>
					<td align=center>" . $ljnsvhc. "</td>
					<td >" . $listnamavhc[$lpt2][$ljnsvhc]. "</td>
					<td align=center>" . $arrJab[$lpos]. "</td>
					<td align=right>" . number_format($lbasis[$lpt2][$ljnsvhc][$lpos],2). "</td>
					<td align=right>" . number_format($lpremibasis[$lpt2][$ljnsvhc][$lpos],2). "</td>
					<td align=right>" . number_format($lpremilebihbasis[$lpt2][$ljnsvhc][$lpos],2). "</td>
					";
				}
			}
		}
	}
}
}

$tab.="</tr>";
$tab.="
 </tbody>
     </table>";
	 
switch ($proses) {
######PREVIEW
    case 'preview':
		if($jenis=='KD'){
			echo $stream;
		}else if($jenis=='AB'){
			echo $tab;
		}
        
        break;

######EXCEL	
    case 'excel':
		if($jenis=='KD'){
			$stream=$stream;
		}else if($jenis=='AB'){
			$stream=$tab;
		}
		
        $tglSkrg = date("Ymd");
        $nop_ = getMenu('kebun_2daftarpremivhc')." ". $kdorg;
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