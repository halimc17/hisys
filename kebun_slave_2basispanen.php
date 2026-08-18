<?php

require_once('master_validation.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$tgl1 = checkPostGet('tgl1', '');
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdUnit', '');
$divisi = checkPostGet('divisi', '');

$rangetanggal = $tgl1;

if ($kdorg == '') {
    echo"Warning: Unit tidak boleh kosong";
    exit;
}

if ($tgl1 == '') {
    echo"Warning: Periode tidak boleh kosong";
    exit;
}
$where='';
$where1='';
if($divisi!=''){
	$where.=" and divisi='".$divisi."'";	
	$where1.=" and a.kodeorg like '".$divisi."%'";	
} else {
	$where.=" and kodeorg='".$kdorg."'";	
	$where1.=" and a.kodeorg like '".$kdorg."%'";	
}

######################################
############# prepare data ###########
######################################


#bentuk data blok
$str = "select *, substr(kodeorg,1,6) as divisi  from " . $dbname . ".setup_blok where "
        . "substr(kodeorg,1,4)='" . $kdorg . "' and kodeorg like '".$divisi."%'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kdblok[$bar['kodeorg']] = $bar['kodeorg'];
	$kddivisi[$bar['divisi']] = $bar['divisi'];
    $tahuntanam[$bar['tahuntanam']] = $bar['tahuntanam'];

    $listtahuntanam[$bar['divisi']][$bar['tahuntanam']] = $bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['kodeorg'];

    $luas[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['luasareaproduktif'];
    $bbt[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['jenisbibit'];
    $pkk[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['jumlahpokok'];
    $topo[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['topografi'];
    $status[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']] = $bar['statusblok'];
}

	$tgl = explode('-',$tgl1);	
	if($tgl[1]==11 || $tgl[1]==12 || $tgl[1]==1 ){
		if($tgl[1]==1){
			$tgl = ($tgl[0]-1)."-11";
		}else{
			$tgl = $tgl[0]."-11";
		}
	} else if($tgl[1]==2 || $tgl[1]==3 || $tgl[1]==4 ){
		if($tgl[1]==1){
			$tgl = ($tgl[0]-1)."-02";
		}else{
			$tgl = $tgl[0]."-02";
		}
	} else if($tgl[1]==5 || $tgl[1]==6 || $tgl[1]==7 ){
		if($tgl[1]==1){
			$tgl = ($tgl[0]-1)."-05";
		}else{
			$tgl = $tgl[0]."-05";
		}
	} else if($tgl[1]==8 || $tgl[1]==9 || $tgl[1]==10 ){
		if($tgl[1]==1){
			$tgl = ($tgl[0]-1)."-08";
		}else{
			$tgl = $tgl[0]."-08";
		}
	}
	$tgl = explode('-',$tgl);
	if($tgl[1]==1){
		$tglbjr = ($tgl[0]-1)."-12";
	}else{
		$tglbjr = $tgl[0]."-".addZero(($tgl[1]-1),2);
	}
	$tgl = explode('-',$tglbjr);
	if($tgl[1]==1){
		$tglbjr1 = ($tgl[0]-1)."-12";
	}else{
		$tglbjr1 = $tgl[0]."-".addZero(($tgl[1]-1),2);
	}
	$tgl = explode('-',$tglbjr1);
	if($tgl[1]==1){
		$tglbjr2 = ($tgl[0]-1)."-12";
	}else{
		$tglbjr2 = $tgl[0]."-".addZero(($tgl[1]-1),2);
	}
	#bjr 1
	$str = "select divisi, blok, tahuntanam, sum(kgwb) as kgwb, sum(jjg) as jjg, (sum(kgwb)/sum(jjg)) as bjr from ".$dbname.".kebun_spb_vw where 1=1 ".$where." and tanggal like '".$tglbjr."%' group by blok";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		 $kg1[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['kgwb'];
		 $jjg1[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['jjg'];
		 $bjr1[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['bjr'];
	}
	#bjr 2
	$str = "select divisi, blok, tahuntanam, sum(kgwb) as kgwb, sum(jjg) as jjg, (sum(kgwb)/sum(jjg)) as bjr from ".$dbname.".kebun_spb_vw where 1=1 ".$where." and tanggal like '".$tglbjr1."%' group by blok";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		 $kg2[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['kgwb'];
		 $jjg2[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['jjg'];
		 $bjr2[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['bjr'];
	}
	#bjr 3
	$str = "select divisi, blok, tahuntanam, sum(kgwb) as kgwb, sum(jjg) as jjg, (sum(kgwb)/sum(jjg)) as bjr from ".$dbname.".kebun_spb_vw where 1=1 ".$where." and tanggal like '".$tglbjr2."%' group by blok";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		 $kg3[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['kgwb'];
		 $jjg3[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['jjg'];
		 $bjr3[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['bjr'];
	}
	
	#bjr sd
	$str = "select divisi, blok, tahuntanam, sum(kgwb) as kgwb, sum(jjg) as jjg, (sum(kgwb)/sum(jjg)) as bjr from ".$dbname.".kebun_spb_vw where 1=1 ".$where." and (tanggal like '".$tglbjr."%' or tanggal like '".$tglbjr1."%' or tanggal like '".$tglbjr2."%') group by blok";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		 $kgsd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['kgwb'];
		 $jjgsd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['jjg'];
		 $bjrsd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['bjr'];
	}
	
	#bjr setup
	$str = "select a.*, substr(a.kodeorg,1,6) as divisi, a.kodeorg as blok, b.tahuntanam from ".$dbname.".kebun_5bjr a left join ".$dbname.".setup_blok b on a.kodeorg=b.kodeorg where a.periode = '".$tglbjr2."' ".$where1."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		 $bjrsetup[$bar['divisi']][$bar['tahuntanam']][$bar['blok']] = $bar['bjr'];
	}

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}
$stream.="
    <thead>
        <tr class=rowheader>
            <td align=center  rowspan='4'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center  rowspan='4'>" . $_SESSION['lang']['divisi'] . "</td>
            <td align=center rowspan='4'>" . $_SESSION['lang']['blok'] . "</td>
            <td align=center rowspan='4' width=50px>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center rowspan='4' width=50px>" . $_SESSION['lang']['topografi'] . "</td>
            <td align=center rowspan='4' width=50px>" . $_SESSION['lang']['status'] . "</td>
            <td align=center  rowspan='4'>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center  rowspan='4'>" . $_SESSION['lang']['jenisbibit'] . "</td>    
            <td align=center  rowspan='4' width=50px>Jumlah Pokok</td>        
            <td align=center  rowspan='4'>SPH</td>      
            <td align=center colspan=17>" . $_SESSION['lang']['panen'] . " " . $_SESSION['lang']['bulan'] . " : ".$tgl1."</td> 
        </tr>";
$stream.="<tr><td align=center colspan=12>SPB</td>
			  <td align=center rowspan=3>Setup<br>".$_SESSION['lang']['bjr']."</td>
			  <td align=center rowspan=2 colspan=4>".$_SESSION['lang']['basic']." ".$_SESSION['lang']['panen']."</td></tr>";
$stream.="<tr>
		  <td align=center colspan=3>".$tglbjr2."</td>
		  <td align=center colspan=3>".$tglbjr1."</td>
		  <td align=center colspan=3>".$tglbjr."</td>
		  <td align=center colspan=3>".$_SESSION['lang']['rerata']."</td>
		  </tr>";
$stream.="<tr>";
$stream.="<td align=center>Jjg</td>";
$stream.="<td align=center>Kg</td>";
$stream.="<td align=center>BJR</td>";
$stream.="<td align=center>Jjg</td>";
$stream.="<td align=center>Kg</td>";
$stream.="<td align=center>BJR</td>";
$stream.="<td align=center>Jjg</td>";
$stream.="<td align=center>Kg</td>";
$stream.="<td align=center>BJR</td>";

$stream.="<td align=center>Jjg</td>";
$stream.="<td align=center>Kg</td>";
$stream.="<td align=center>BJR</td>";


$stream.="<td align=center>Basis Jjg</td>"; 
$stream.="<td align=center>Premi SB</td>";
$stream.="<td align=center>Premi LB<br>(Rp/Jjg)</td>";
$stream.="<td align=center>Basis Ha</td>";

$stream.="</tr>";
$stream.="</thead>
 <tbody>";
$topografi=makeOption($dbname,'setup_topografi','topografi,keterangan');
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
                if ($listblok[$divisi][$thntnm][$blok] != '') {
                    $no+=1;
                    $stream.="<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
						<td align=center>" . $divisi. "</td>
						<td align=center>" . $listblok[$divisi][$thntnm][$blok] . "</td>    
                        <td align=center>" . $listtahuntanam[$divisi][$thntnm] . "</td>
                        <td align=center>" . $topografi[$topo[$divisi][$thntnm][$blok]] . "</td>
                        <td align=center>" . $status[$divisi][$thntnm][$blok] . "</td>
                        <td align=right>" . @number_format($luas[$divisi][$thntnm][$blok],2) . "</td>    
                        <td align=left>" . $bbt[$divisi][$thntnm][$blok] . "</td>    
                        <td align=right>" . @number_format($pkk[$divisi][$thntnm][$blok]) . "</td>    
                        <td align=right>" . @number_format($pkk[$divisi][$thntnm][$blok] / $luas[$divisi][$thntnm][$blok]) . "</td>";
                        $stream.="<td align=right>".@number_format($jjg3[$divisi][$thntnm][$blok])."</td>";
                        $stream.="<td align=right>".@number_format($kg3[$divisi][$thntnm][$blok])."</td>";
                        $stream.="<td align=right>".@number_format($bjr3[$divisi][$thntnm][$blok],2)."</td>";
						
						$stream.="<td align=right>".@number_format($jjg2[$divisi][$thntnm][$blok])."</td>";
                        $stream.="<td align=right>".@number_format($kg2[$divisi][$thntnm][$blok])."</td>";
                        $stream.="<td align=right>".@number_format($bjr2[$divisi][$thntnm][$blok],2)."</td>";
						
						$stream.="<td align=right>".@number_format($jjg1[$divisi][$thntnm][$blok])."</td>";
                        $stream.="<td align=right>".@number_format($kg1[$divisi][$thntnm][$blok])."</td>";
                        $stream.="<td align=right>".@number_format($bjr1[$divisi][$thntnm][$blok],2)."</td>";
						
						$stream.="<td align=right>".@number_format($jjgsd[$divisi][$thntnm][$blok])."</td>";
                        $stream.="<td align=right>".@number_format($kgsd[$divisi][$thntnm][$blok])."</td>";
			
				$optPt = makeOption($dbname,'organisasi','kodeorganisasi,alokasi',"kodeorganisasi='".$divisi."'");
						
						$bgb='';
						if($bjrsetup[$divisi][$thntnm][$blok]>$bjrsd[$divisi][$thntnm][$blok]){
							$bgb="color=blue";	
						} else if($bjrsetup[$divisi][$thntnm][$blok]<$bjrsd[$divisi][$thntnm][$blok]){
							$bgb="color=red";	
						}
						
						
						$stream.="<td align=right>".@number_format($bjrsd[$divisi][$thntnm][$blok],2)."</td>";
						$stream.="<td align=right><font ".$bgb."><b>".@number_format($bjrsetup[$divisi][$thntnm][$blok],2)."</b></font></td>";
						
						$strv = "select * from ".$dbname.".kebun_5basispanen2 where (bjrdari <= '".@number_format($bjrsetup[$divisi][$thntnm][$blok],2)."' and bjrsampai >= '".@number_format($bjrsetup[$divisi][$thntnm][$blok],2)."') and topografi = '".$topo[$divisi][$thntnm][$blok]."' and afdeling = '".$optPt[$divisi]."'";
						$resv=fetchdata($strv);
						if(count($resv)==0){
							$stream.="<td></td>";
							$stream.="<td></td>";
							$stream.="<td></td>";
							$stream.="<td></td>";
						}else{
							foreach($resv as $bar => $barv){
							$stream.="<td align=right>".$barv['basis']."</td>";	
							$stream.="<td align=right>".@number_format($barv['premibasis'])."</td>";
							$stream.="<td align=right>".@number_format($barv['premilebihbasis'])."</td>";
							$stream.="<td align=right>".$barv['luastopografi']."</td>";
							}
							
						}
						
							// $resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
							// $resv->setFetchMode(PDO::FETCH_ASSOC);
							// while($barv=$resv->fetch())
								// {
									// $stream.="<td align=right>".$barv['basis']."</td>";
									// $stream.="<td align=right>".@number_format($barv['premibasis'])."</td>";
									// $stream.="<td align=right>".@number_format($barv['premilebihbasis'])."</td>";
									// $stream.="<td align=right>".$barv['luastopografi']."</td>";
								// }
							
					
					@$ttluas[$divisi][$thntnm]+=$luas[$divisi][$thntnm][$blok];
					@$ttpkk[$divisi][$thntnm]+=$pkk[$divisi][$thntnm][$blok];
					@$sttjjg3[$divisi][$thntnm]+=$jjg3[$divisi][$thntnm][$blok];
					@$sttkg3[$divisi][$thntnm]+=$kg3[$divisi][$thntnm][$blok];
					@$sttjjg2[$divisi][$thntnm]+=$jjg2[$divisi][$thntnm][$blok];
					@$sttkg2[$divisi][$thntnm]+=$kg2[$divisi][$thntnm][$blok];
					@$sttjjg1[$divisi][$thntnm]+=$jjg1[$divisi][$thntnm][$blok];
					@$sttkg1[$divisi][$thntnm]+=$kg1[$divisi][$thntnm][$blok];
					@$sttjjgsd[$divisi][$thntnm]+=$jjgsd[$divisi][$thntnm][$blok];
					@$sttkgsd[$divisi][$thntnm]+=$kgsd[$divisi][$thntnm][$blok];
                }
            }
            $stream.="
                <tr  bgcolor=#80FFFE>
                <td colspan=6>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['tahuntanam'] . "  " . $thntnm . "</td>
				<td align=right>" . @number_format($ttluas[$divisi][$thntnm],2) . "</td>
				<td></td>
				<td align=right>" . @number_format($ttpkk[$divisi][$thntnm]) . "</td>
				<td align=right>" . @number_format($ttpkk[$divisi][$thntnm] / $ttluas[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttjjg3[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttkg3[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttkg3[$divisi][$thntnm]/$sttjjg3[$divisi][$thntnm],2) . "</td>";
				$stream.="<td align=right>" . @number_format($sttjjg2[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttkg2[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttkg2[$divisi][$thntnm]/$sttjjg2[$divisi][$thntnm],2) . "</td>";
				$stream.="<td align=right>" . @number_format($sttjjg1[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttkg1[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttkg1[$divisi][$thntnm]/$sttjjg1[$divisi][$thntnm],2) . "</td>";
				$stream.="<td align=right>" . @number_format($sttjjgsd[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttkgsd[$divisi][$thntnm]) . "</td>";
				$stream.="<td align=right>" . @number_format($sttkgsd[$divisi][$thntnm]/$sttjjgsd[$divisi][$thntnm],2) . "</td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				
            $stream.="</tr>";
			@$luasdiv[$divisi]+=$ttluas[$divisi][$thntnm];
			@$pkkdiv[$divisi]+=$ttpkk[$divisi][$thntnm];
			@$stjjg3[$divisi]+=$sttjjg3[$divisi][$thntnm];
			@$stkg3[$divisi]+=$sttkg3[$divisi][$thntnm];
			@$stjjg2[$divisi]+=$sttjjg2[$divisi][$thntnm];
			@$stkg2[$divisi]+=$sttkg2[$divisi][$thntnm];
			@$stjjg1[$divisi]+=$sttjjg1[$divisi][$thntnm];
			@$stkg1[$divisi]+=$sttkg1[$divisi][$thntnm];
			@$stjjgsd[$divisi]+=$sttjjgsd[$divisi][$thntnm];
			@$stkgsd[$divisi]+=$sttkgsd[$divisi][$thntnm];
		}
    }
    $stream.="
        <tr bgcolor=skyblue>
            <td align=left colspan=6>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['divisi'] . " " . $divisi . "</td>
            <td align=right>" . @number_format($luasdiv[$divisi], 2) . "</td>
            <td></td>
            <td align=right>" . @number_format($pkkdiv[$divisi]) . "</td>
            <td align=right>" . @number_format($pkkdiv[$divisi] / $luasdiv[$divisi]) . "</td>";
			
	$stream.="<td align=right>" . @number_format($stjjg3[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stkg3[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stkg3[$divisi]/$stjjg3[$divisi],2) . "</td>";
	$stream.="<td align=right>" . @number_format($stjjg2[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stkg2[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stkg2[$divisi]/$stjjg2[$divisi],2) . "</td>";
	$stream.="<td align=right>" . @number_format($stjjg1[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stkg1[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stkg1[$divisi]/$stjjg1[$divisi],2) . "</td>";
	$stream.="<td align=right>" . @number_format($stjjgsd[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stkgsd[$divisi]) . "</td>";
	$stream.="<td align=right>" . @number_format($stkgsd[$divisi]/$stjjgsd[$divisi],2) . "</td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
	$stream.="<td></td>";
    $stream.="</tr>";
	
	@$gtluas+=$luasdiv[$divisi];
	@$gtpkk+=$pkkdiv[$divisi];
	@$gjjg3+=$stjjg3[$divisi];
	@$gkg3+=$stkg3[$divisi];
	@$gjjg2+=$stjjg2[$divisi];
	@$gkg2+=$stkg2[$divisi];
	@$gjjg1+=$stjjg1[$divisi];
	@$gkg1+=$stkg1[$divisi];
	@$gjjgsd+=$stjjgsd[$divisi];
	@$gkgsd+=$stkgsd[$divisi];
}
$stream.="
        <tr bgcolor=#F5F5DC>
            <td align=left colspan=6>" . $_SESSION['lang']['grnd_total'] . " " . $kdorg . "</td>
            <td align=right>" . @number_format($gtluas, 2) . "</td>
            <td></td>
            <td align=right>" . @number_format($gtpkk) . "</td>
            <td align=right>" . @number_format($gtpkk / $gtluas) . "</td>";
$stream.="<td align=right>" . @number_format($gjjg3) . "</td>";
$stream.="<td align=right>" . @number_format($gkg3) . "</td>";
$stream.="<td align=right>" . @number_format($gkg3/$gjjg3,2) . "</td>";
$stream.="<td align=right>" . @number_format($gjjg2) . "</td>";
$stream.="<td align=right>" . @number_format($gkg2) . "</td>";
$stream.="<td align=right>" . @number_format($gkg2/$gjjg2,2) . "</td>";
$stream.="<td align=right>" . @number_format($gjjg1) . "</td>";
$stream.="<td align=right>" . @number_format($gkg1) . "</td>";
$stream.="<td align=right>" . @number_format($gkg1/$gjjg1,2) . "</td>";
$stream.="<td align=right>" . @number_format($gjjgsd) . "</td>";
$stream.="<td align=right>" . @number_format($gkgsd) . "</td>";
$stream.="<td align=right>" . @number_format($gkgsd/$gjjgsd,2) . "</td>";
$stream.="<td></td>";
$stream.="<td></td>";
$stream.="<td></td>";
$stream.="<td></td>";
$stream.="<td></td>";
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
        $nop_ = "basis_pnn_" . $kdorg;
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