<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$tgl1 = tanggalsystem(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystem(checkPostGet('tgl2', ''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdUnit', '');
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

######################################
############# prepare data ###########
######################################

#kebun_rekappnn_vw
#bentuk data blok dari rekap panen
$str = "select *, substr(kodeorg,1,6) as divisi  from " . $dbname . ".setup_blok_tahunan where "
        . "substr(kodeorg,1,4)='" . $kdorg . "' and kodeorg like '".$divisi."%' and tahun >='".substr($tgl1, 0,6)."' and tahun <='".substr($tgl2, 0,6)."'";
#exit('error : '.$str);
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlbaris($res);
if($numrows==0){
    $str = "select *, substr(kodeorg,1,6) as divisi  from " . $dbname . ".setup_blok where "
        . "substr(kodeorg,1,4)='" . $kdorg . "' and kodeorg like '".$divisi."%'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
}
while ($bar = $res->fetch()) {
    $kdblok[$bar['kodeorg']] = $bar['kodeorg'];
	$kddivisi[$bar['divisi']] = $bar['divisi'];
    $tahuntanam[$bar['tahuntanam']] = $bar['tahuntanam'];

    $listtahuntanam[$bar['divisi']][$bar['tahuntanam']] = $bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['kodeorg'];

    $luas[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['luasareaproduktif'];
    $bbt[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['jenisbibit'];
    $pkk[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['jumlahpokok'];
}

#bentuk data blok dari rekap panen
$str = "select * from " . $dbname . ".kebun_rekappnn_vw where "
        . "substr(divisi,1,4)='" . $kdorg . "' and divisi like '".$divisi."%' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$rjjgpnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] += $bar['jjgpanen'];
    @$rhkpnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] += $bar['tenagakerja'];
	@$jjgpnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']][$bar['tanggal']] += $bar['jjgpanen'];
    @$hkpnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']][$bar['tanggal']] += $bar['tenagakerja'];
}

#bentuk data blok dari kegiatan panen
$str = "select *, substr(kodeorg,1,6) as divisi from " . $dbname . ".kebun_prestasi_vw where "
        . "unit='" . $kdorg . "' and kodeorg like '".$divisi."%' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$rjjgkegpnn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] += $bar['hasilkerja'];
    @$rhkkegpnn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] += 0;
    @$jjgkegpnn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']][$bar['tanggal']] += $bar['hasilkerja'];
    @$hkkegpnn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']][$bar['tanggal']] += 0;
}

$str = "select *, substr(blok,1,6) as divisi from " . $dbname . ".kebun_spb_vw where "
        . "kodeorg='" . $kdorg . "' and blok like '".$divisi."%' and tanggalpanen between '" . $tgl1 . "' and '" . $tgl2 . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$rjjgspb[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] += $bar['jjg'];
    @$jjgspb[$bar['divisi']][$bar['tahuntanam']][$bar['blok']][$bar['tanggalpanen']] += $bar['jjg'];
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
            <th align=center  rowspan='4'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center  rowspan='4'>" . $_SESSION['lang']['divisi'] . "</th>
            
            <th align=center rowspan='4'>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center rowspan='4' width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center  rowspan='4'>" . $_SESSION['lang']['luas'] . "</th>
            <th align=center  rowspan='4'>" . $_SESSION['lang']['jenisbibit'] . "</th>    
            <th align=center  rowspan='4' width=50px>Jumlah Pokok</th>        
            <th align=center  rowspan='4'>SPH</th>      
                
            <th align=center colspan=" . ($span*3) . ">" . $_SESSION['lang']['tanggal'] . "</th> 
			<th align=center  rowspan='2' colspan=3>" . $_SESSION['lang']['total'] . "</th>   
        </tr>";
$stream.="<tr>";
foreach ($rangetanggal as $listtanggal => $tgl) {
    $mggu = date('D', strtotime($tgl));
    if ($mggu == 'Sun') {
        $stream.="<th align=center colspan=3><font color=red>" . substr($tgl, 8, 2) . "/" . substr($tgl, 5, 2) . "/" . substr($tgl, 0, 4) . "</font></th>";
    } else {
        $stream.="<th align=center colspan=3>" . substr($tgl, 8, 2) . "/" . substr($tgl, 5, 2) . "/" . substr($tgl, 0, 4) . "</th>";
    }
}

$stream.="</tr>";

$stream.="<tr>";

foreach ($rangetanggal as $listtanggal => $tgl) {
    $mggu = date('D', strtotime($tgl));
    if ($mggu == 'Sun') {
        $stream.="<th align=center colspan=1><font color=red>Rekap Pnn</font></th>";
        $stream.="<th align=center colspan=1><font color=red>Keg Pnn</font></th>";
        $stream.="<th align=center colspan=1><font color=red>SPB</font></th>";
    } else {
        $stream.="<th align=center colspan=1>Rekap Pnn</th>";
        $stream.="<th align=center colspan=1>Keg Pnn</th>";
        $stream.="<th align=center colspan=1>SPB</th>";
    }
}
$stream.="<th align=center colspan=1>Rekap Pnn</th>";
$stream.="<th align=center colspan=1>Keg Pnn</th>";
$stream.="<th align=center colspan=1>SPB</th>";
$stream.="</tr>";

$stream.="<tr>";

foreach ($rangetanggal as $listtanggal => $tgl) {
    $mggu = date('D', strtotime($tgl));
    if ($mggu == 'Sun') {
        //$stream.="<th align=center><font color=red>HK</font></th>";
        $stream.="<th align=center><font color=red>Jjg</font></th>";
		//$stream.="<th align=center><font color=red>HK</font></th>";
        $stream.="<th align=center><font color=red>Jjg</font></th>";
        $stream.="<th align=center><font color=red>Jjg</font></th>";
    } else {
        //$stream.="<th align=center>HK</th>";
        $stream.="<th align=center>Jjg</th>"; 
		//$stream.="<th align=center>HK</th>";
        $stream.="<th align=center>Jjg</th>";
        $stream.="<th align=center>Jjg</th>";
    }
}
//$stream.="<th align=center>HK</th>";
$stream.="<th align=center>Jjg</th>";
//$stream.="<th align=center>HK</th>";
$stream.="<th align=center>Jjg</th>";
$stream.="<th align=center>Jjg</th>";
$stream.="</tr>";
$stream.="</thead>
 <tbody>";



$romawi = array("01"=>"I","02"=>"II","03"=>"III","04"=>"IV","05"=>"V","06"=>"VI","07"=>"VII","08"=>"VIII","09"=>"IX","10"=>"X","11"=>"XI","12"=>"XII","13"=>"XIII","14"=>"XIV","15"=>"XV","16"=>"XVI","17"=>"XVII","18"=>"XVIII","19"=>"XIX","20"=>"XX","A1"=>"Plasma I","A2"=>"Plasma II","A3"=>"Plasma III");


@$jumdiv = count($kddivisi);
if ($jumdiv > 0) {
    array_multisort($kddivisi, SORT_ASC);
    array_multisort($tahuntanam, SORT_ASC);
    array_multisort($kdblok, SORT_ASC);
} else {
    exit("error : Data kosong");
}

foreach ($kddivisi as $divisi) {
    foreach ($tahuntanam as $thntnm) {
        $listtahuntanam[$divisi][$thntnm] = isset($listtahuntanam[$divisi][$thntnm]) ? $listtahuntanam[$divisi][$thntnm] : '';
        if ($listtahuntanam[$divisi][$thntnm] != '') {
            foreach ($kdblok as $blok) {
                $listblok[$divisi][$thntnm][$blok] = isset($listblok[$divisi][$thntnm][$blok]) ? $listblok[$divisi][$thntnm][$blok] : '';
                if ($listblok[$divisi][$thntnm][$blok] != '' and ($luas[$divisi][$thntnm][$blok]>'0')) {
                    $no+=1;
                    $stream.="<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
						<td align=center>" . $namaOrg[$divisi]. "</td>
						<td align=center>" . $namaOrg[$listblok[$divisi][$thntnm][$blok]] . "</td>    
                        <td align=center>" . $listtahuntanam[$divisi][$thntnm] . "</td>
                        <td align=right>" . @number_format($luas[$divisi][$thntnm][$blok],2) . "</td>    
                        <td align=left>" . $bbt[$divisi][$thntnm][$blok] . "</td>    
                        <td align=right>" . @number_format($pkk[$divisi][$thntnm][$blok]) . "</td>    
                        <td align=right>" . @number_format($pkk[$divisi][$thntnm][$blok] / $luas[$divisi][$thntnm][$blok]) . "</td>    
						
                    ";
                    foreach ($rangetanggal as $listtanggal => $tgl) {
						$fkolor='';
						$fkkolor='';
						$fkkolorspb='';
						if(@number_format($hkpnn[$divisi][$thntnm][$blok][$tgl])!=@number_format($hkkegpnn[$divisi][$thntnm][$blok][$tgl])){
							$fkolor=" color=red";
						}
						if(@$jjgpnn[$divisi][$thntnm][$blok][$tgl]!=@$jjgkegpnn[$divisi][$thntnm][$blok][$tgl]){
							$fkkolor=" color=red";
						}
						if(@$jjgpnn[$divisi][$thntnm][$blok][$tgl]!=@$jjgspb[$divisi][$thntnm][$blok][$tgl]){
							$fkkolorspb=" color=red";
						}
						
                        /* $stream.="<td align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail('".$listblok[$divisi][$thntnm][$blok]."','".$tgl."','html','RekapPanen',event)>
						<font ".$fkolor.">".@number_format($hkpnn[$divisi][$thntnm][$blok][$tgl])."</font></td>"; */
                        
						$stream.="<td align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail('".$listblok[$divisi][$thntnm][$blok]."','".$tgl."','html','RekapPanen',event)>
						<font ".$fkkolor.">".@number_format($jjgpnn[$divisi][$thntnm][$blok][$tgl])."</font></td>";
						
						/* $stream.="<td align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail('".$listblok[$divisi][$thntnm][$blok]."','".$tgl."','html','KegiatanPanen',event)>
						<font ".$fkolor.">".@number_format($hkkegpnn[$divisi][$thntnm][$blok][$tgl])."</font></td>"; */
                        
						$stream.="<td align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail('".$listblok[$divisi][$thntnm][$blok]."','".$tgl."','html','KegiatanPanen',event)>
						<font ".$fkkolor.">".@number_format($jjgkegpnn[$divisi][$thntnm][$blok][$tgl])."</font></td>";
						
						$stream.="<td align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail('".$listblok[$divisi][$thntnm][$blok]."','".$tgl."','html','SPBTBS',event)>
						<font ".$fkkolorspb.">".@number_format($jjgspb[$divisi][$thntnm][$blok][$tgl])."</font></td>";
						
					@$sthkpnn[$divisi][$thntnm][$tgl]+=$hkpnn[$divisi][$thntnm][$blok][$tgl];
					@$stjjgpnn[$divisi][$thntnm][$tgl]+=$jjgpnn[$divisi][$thntnm][$blok][$tgl];
					@$sthkkegpnn[$divisi][$thntnm][$tgl]+=$hkkegpnn[$divisi][$thntnm][$blok][$tgl];
					@$stjjgkegpnn[$divisi][$thntnm][$tgl]+=$jjgkegpnn[$divisi][$thntnm][$blok][$tgl];
					@$stjjgpnnspb[$divisi][$thntnm][$tgl]+=$jjgspb[$divisi][$thntnm][$blok][$tgl];
                    
					}

                    @$luastt[$divisi][$thntnm]+=$luas[$divisi][$thntnm][$blok];
                    @$pkktt[$divisi][$thntnm]+=$pkk[$divisi][$thntnm][$blok];
					
					//$stream.="<td align=right>" . @number_format($rhkpnn[$divisi][$thntnm][$blok]) . "</td>";
					$stream.="<td align=right>" . @number_format($rjjgpnn[$divisi][$thntnm][$blok]) . "</td>";
					//$stream.="<td align=right>" . @number_format($rhkkegpnn[$divisi][$thntnm][$blok]) . "</td>";
					$stream.="<td align=right>" . @number_format($rjjgkegpnn[$divisi][$thntnm][$blok]) . "</td>";
					$stream.="<td align=right>" . @number_format($rjjgspb[$divisi][$thntnm][$blok]) . "</td>";
					
					@$strhkpnn[$divisi][$thntnm]+=$rhkpnn[$divisi][$thntnm][$blok];
					@$strjjgpnn[$divisi][$thntnm]+=$rjjgpnn[$divisi][$thntnm][$blok];
					@$strhkkegpnn[$divisi][$thntnm]+=$rhkkegpnn[$divisi][$thntnm][$blok];
					@$strjjgkegpnn[$divisi][$thntnm]+=$rjjgkegpnn[$divisi][$thntnm][$blok];
					@$strjjgkegpnnspb[$divisi][$thntnm]+=$rjjgspb[$divisi][$thntnm][$blok];
					
                }
            }
            $stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=4>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['tahuntanam'] . "  " . $thntnm . "</td>
                    <td align=right>" . @number_format($luastt[$divisi][$thntnm], 2) . "</td>
                    <td></td>
                    <td align=right>" . @number_format($pkktt[$divisi][$thntnm]) . "</td>
                    <td align=right>" . @number_format($pkktt[$divisi][$thntnm] / $luastt[$divisi][$thntnm]) . "</td>
                    ";
			
			
            foreach ($rangetanggal as $listtanggal => $tgl) {
			$fkolor='';
			$fkkolor='';
			$fkkolorspb='';
				if(@number_format($sthkpnn[$divisi][$thntnm][$tgl])!=@number_format($sthkkegpnn[$divisi][$thntnm][$tgl])){
					$fkolor=" color=red";
				}
				if($stjjgpnn[$divisi][$thntnm][$tgl]!=$stjjgkegpnn[$divisi][$thntnm][$tgl]){
					$fkkolor=" color=red";
				}
				if(@$stjjgpnn[$divisi][$thntnm][$tgl]!=@$stjjgpnnspb[$divisi][$thntnm][$tgl]){
					$fkkolorspb=" color=red";
				}
                //$stream.="<td align=right ><font ".$fkolor.">".@number_format($sthkpnn[$divisi][$thntnm][$tgl])."</font></td>";
				$stream.="<td align=right><font ".$fkkolor.">".@number_format($stjjgpnn[$divisi][$thntnm][$tgl])."</font></td>";
				//$stream.="<td align=right><font ".$fkolor.">".@number_format($sthkkegpnn[$divisi][$thntnm][$tgl])."</font></td>";
				$stream.="<td align=right><font ".$fkkolor.">".@number_format($stjjgkegpnn[$divisi][$thntnm][$tgl])."</font></td>";
				$stream.="<td align=right><font ".$fkkolorspb.">".@number_format($stjjgpnnspb[$divisi][$thntnm][$tgl])."</font></td>
                        ";
				
				
					@$divhkpnn[$divisi][$tgl]+=$sthkpnn[$divisi][$thntnm][$tgl];
					@$divjjgpnn[$divisi][$tgl]+=$stjjgpnn[$divisi][$thntnm][$tgl];
					@$divhkkegpnn[$divisi][$tgl]+=$sthkkegpnn[$divisi][$thntnm][$tgl];
					@$divjjgkegpnn[$divisi][$tgl]+=$stjjgkegpnn[$divisi][$thntnm][$tgl];
					@$divjjgspb[$divisi][$tgl]+=$stjjgpnnspb[$divisi][$thntnm][$tgl];
            }
				//$stream.="<td align=right>" . @number_format($strhkpnn[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($strjjgpnn[$divisi][$thntnm]) . "</td>";
				//$stream.="<td align=right>" . @number_format($strhkkegpnn[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($strjjgkegpnn[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($strjjgkegpnnspb[$divisi][$thntnm]) . "</td>";
            $stream.="
                </tr>
                ";
			@$stdrhkpnn[$divisi]+=$strhkpnn[$divisi][$thntnm];
			@$stdrjjgpnn[$divisi]+=$strjjgpnn[$divisi][$thntnm];
			@$stdrhkkegpnn[$divisi]+=$strhkkegpnn[$divisi][$thntnm];
			@$stdrjjgkegpnn[$divisi]+=$strjjgkegpnn[$divisi][$thntnm];
			@$stdrjjgkegpnnspb[$divisi]+=$strjjgkegpnnspb[$divisi][$thntnm];
            @$luasdiv[$divisi]+=$luastt[$divisi][$thntnm];
            @$pkkdiv[$divisi]+=$pkktt[$divisi][$thntnm];
        }
    }
    $stream.="
        <tr bgcolor=skyblue>
            <td align=left colspan=4>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['divisi'] . " " . $divisi . "</td>
            <td align=right>" . @number_format($luasdiv[$divisi], 2) . "</td>
            <td></td>
            <td align=right>" . @number_format($pkkdiv[$divisi]) . "</td>
            <td align=right>" . @number_format($pkkdiv[$divisi] / $luasdiv[$divisi]) . "</td>    
       
    ";

    foreach ($rangetanggal as $listtanggal => $tgl) {
	$fkolor='';
	$fkkolor='';
	$fkkolorspb='';
		if(@number_format($divhkpnn[$divisi][$tgl])!=@number_format($divhkkegpnn[$divisi][$tgl])){
			$fkolor=" color=red ";
		}
		if($divjjgpnn[$divisi][$tgl]!=$divjjgkegpnn[$divisi][$tgl]){
			$fkkolor=" color=red";
		}
		if($divjjgpnn[$divisi][$tgl]!=$divjjgspb[$divisi][$tgl]){
			$fkkolorspb=" color=red";
		}
        #$stream.="<td align=right><font ".$fkolor.">".@number_format($divhkpnn[$divisi][$tgl])."</font></td>";
		$stream.="<td align=right><font ".$fkkolor.">".@number_format($divjjgpnn[$divisi][$tgl])."</font></td>";
		#$stream.="<td align=right><font ".$fkolor.">".@number_format($divhkkegpnn[$divisi][$tgl])."</font></td>";
		$stream.="<td align=right><font ".$fkkolor.">".@number_format($divjjgkegpnn[$divisi][$tgl])."</font></td>";
		$stream.="<td align=right><font ".$fkkolorspb.">".@number_format($divjjgspb[$divisi][$tgl])."</font></td>
            ";
		@$gthkpnn[$tgl]+=$divhkpnn[$divisi][$tgl];
		@$gtjjgpnn[$tgl]+=$divjjgpnn[$divisi][$tgl];
		@$gthkkegpnn[$tgl]+=$divhkkegpnn[$divisi][$tgl];
		@$gtjjgkegpnn[$tgl]+=$divjjgkegpnn[$divisi][$tgl];
		@$gtjjgkegpnnspb[$tgl]+=$divjjgspb[$divisi][$tgl];
    }
	#$stream.="<td align=right>" . @number_format($stdrhkpnn[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stdrjjgpnn[$divisi]) . "</td>";
	#$stream.="<td align=right>" . @number_format($stdrhkkegpnn[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stdrjjgkegpnn[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stdrjjgkegpnnspb[$divisi]) . "</td>";
    $stream.="
                </tr>
                ";
	@$gtstdrhkpnn+=$stdrhkpnn[$divisi];
	@$gtstdrjjgpnn+=$stdrjjgpnn[$divisi];
	@$gtstdrhkkegpnn+=$stdrhkkegpnn[$divisi];
	@$gtstdrjjgkegpnn+=$stdrjjgkegpnn[$divisi];
	@$gtstdrjjgkegpnnspb+=$stdrjjgkegpnnspb[$divisi];
	
    @$gtluas+=$luasdiv[$divisi];
    @$gtpkk+=$pkkdiv[$divisi];
}
$stream.="
        <tr bgcolor=#F5F5DC>
            <td align=left colspan=4>" . $_SESSION['lang']['grnd_total'] . " " . $kdorg . "</td>
            <td align=right>" . @number_format($gtluas, 2) . "</td>
            <td></td>
            <td align=right>" . @number_format($gtpkk) . "</td>
            <td align=right>" . @number_format($gtpkk / $gtluas) . "</td>   
        
    ";

foreach ($rangetanggal as $listtanggal => $tgl) {
$fkolor='';
$fkkolor='';
$fkkolorspb='';
	if(@number_format($gthkpnn[$tgl])!=@number_format($gthkkegpnn[$tgl])){
		$fkolor=" color=red ";
	}
	if($gtjjgpnn[$tgl]!=$gtjjgkegpnn[$tgl]){
		$fkkolor=" color=red";
	}
	if($gtjjgpnn[$tgl]!=$gtjjgkegpnnspb[$tgl]){
		$fkkolorspb=" color=red";
	}
    
    #$stream.="<td align=right><font ".$fkolor.">".@number_format($gthkpnn[$tgl])."</font></td>";
	$stream.="<td align=right><font ".$fkkolor.">".@number_format($gtjjgpnn[$tgl])."</font></td>";
	#$stream.="<td align=right><font ".$fkolor.">".@number_format($gthkkegpnn[$tgl])."</font></td>";
	$stream.="<td align=right><font ".$fkkolor.">".@number_format($gtjjgkegpnn[$tgl])."</font></td>";
	$stream.="<td align=right><font ".$fkkolorspb.">".@number_format($gtjjgkegpnnspb[$tgl])."</font></td>
            ";
}
#$stream.="<td align=right>" . @number_format($gtstdrhkpnn) . "</td>";
$stream.="<td align=right>" . @number_format($gtstdrjjgpnn) . "</td>";
#$stream.="<td align=right>" . @number_format($gtstdrhkkegpnn) . "</td>";
$stream.="<td align=right>" . @number_format($gtstdrjjgkegpnn) . "</td>";
$stream.="<td align=right>" . @number_format($gtstdrjjgkegpnnspb) . "</td>";

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
        $nop_ = "keg_pnn_vs_rekap_pnn_" . $kdorg;
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