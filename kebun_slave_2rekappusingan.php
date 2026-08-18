<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

@$tgl2 = tanggalsystemn(checkPostGet('tgl2',''));
@$proses = checkPostGet('proses', '');
@$kdorg = checkPostGet('kdorg', '');

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
if($kdorg==''){
    echo"Warning: Unit tidak boleh kosong"; 
    exit;
}else if ($tgl2=='--'){
	echo "Warning : Tanggal tidak boleh kosong";
	exit;
}

######################################
############# prepare data ###########
######################################
$stream="";
 
	
############################
###### REKAP PSG (HA)#######
############################
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<fieldset><legend>".$_SESSION['lang']['pusingan']."</legend>";
    $stream.="<fieldset style='float:left;'><legend>".$_SESSION['lang']['rekap']." ".$_SESSION['lang']['luas']." (Ha) ".$_SESSION['lang']['tanggal']." ".tanggalnormal($tgl2)."</legend><table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>
				<tr>
				<th rowspan=2 align=center width=20px >".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 align=center width=55px >".$_SESSION['lang']['divisi']."</th>
				<th colspan=4 align=center>".$_SESSION['lang']['pusingan']." (Ha)</th>
				<th rowspan=2 align=center width=80px >".$_SESSION['lang']['total']."</th>
				</tr>
				<tr>
				<th align=center width=70px ><= 7 ".$_SESSION['lang']['hari']."</th>
				<th align=center width=70px >8 - 10 ".$_SESSION['lang']['hari']."</th>
				<th align=center width=70px >11 - 15 ".$_SESSION['lang']['hari']."</th>
				<th align=center width=70px >> 15 ".$_SESSION['lang']['hari']."</th>
				</tr>
        </tr>";
		
$stream.="</thead>
		<tbody>";
		$no=0;
		$str="select divisi, tanggal, angka, sum(luasareaproduktif) as luas from ".$dbname.".kebun_pusingan_vw where "
			   . " unit='".$kdorg."' and tanggal = '".$tgl2."' group by angka, divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$angka[$bar['divisi']][$bar['tanggal']]=$bar['angka'];
			
			if ($bar['angka'] <=7){
				@$luas7[$bar['divisi']][$bar['tanggal']]+=$bar['luas'];
			}	
			else if ($bar['angka'] >7 and $bar['angka'] <= 10){
				@$luas10[$bar['divisi']][$bar['tanggal']]+=$bar['luas'];
			}
			else if ($bar['angka'] >10 and $bar['angka'] <= 15){
				@$luas15[$bar['divisi']][$bar['tanggal']]+=$bar['luas'];
			}
			else {
				@$luas16[$bar['divisi']][$bar['tanggal']]+=$bar['luas'];
			}		

				@$luasttl[$bar['divisi']][$bar['tanggal']]+=$bar['luas'];
			
		}	
			
				// echo "<pre>";
				// print_r($luas7);
				// echo "</pre>";
				
		$str="select distinct(substr(kodeorg,1,6)) as divisi from ".$dbname.".setup_blok where "." kodeorg like '".$kdorg."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
		$no+=1;
		$stream.="<tr class=rowcontent>
            <td align=center>".$no."</td>
			<td align=center>".$namaOrg[$bar['divisi']]."</td>
			<td align=right>".@$luas7[$bar['divisi']][$tgl2]."</td>
			<td align=right>".@$luas10[$bar['divisi']][$tgl2]."</td>
			<td align=right>".@$luas15[$bar['divisi']][$tgl2]."</td>
			<td align=right>".@$luas16[$bar['divisi']][$tgl2]."</td>
			<td align=right>".@$luasttl[$bar['divisi']][$tgl2]."</td>			
			</tr>";
			
			@$ttl7+=$luas7[$bar['divisi']][$tgl2];
			@$ttl10+=$luas10[$bar['divisi']][$tgl2];
			@$ttl15+=$luas15[$bar['divisi']][$tgl2];
			@$ttl16+=$luas16[$bar['divisi']][$tgl2];
			@$gtttl+=$luasttl[$bar['divisi']][$tgl2];
			
			
		}
		
		$stream.="<tr bgcolor=#0033CC  style='color:#FFFFFF'>
			<td align=center colspan=2 ><b>TOTAL</b></td>
			<td align=right ><b>".@number_format($ttl7,2)."</b></td>
			<td align=right ><b>".@number_format($ttl10,2)."</b></td>
			<td align=right ><b>".@number_format($ttl15,2)."</b></td>
			<td align=right ><b>".@number_format($ttl16,2)."</b></td>
			<td align=right ><b>".@number_format($gtttl,2)."</b></td>
			
			</tr>
	</table></fieldset>";
	
##############################
###### REKAP PSG (count)######
##############################
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<fieldset style='float:left;' ><legend>".$_SESSION['lang']['rekap']." ".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['blok']." ".$_SESSION['lang']['tanggal']." ".tanggalnormal($tgl2)."</legend><table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>
				<tr>
				<th rowspan=2 align=center width=20px >".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 align=center width=55px >".$_SESSION['lang']['divisi']."</th>
				<th colspan=4 align=center>".$_SESSION['lang']['pusingan']." (Count Blok)</th>
				<th rowspan=2 align=center width=80px >".$_SESSION['lang']['total']."</th>
				</tr>
				<tr>
				<th align=center width=70px >< 7 ".$_SESSION['lang']['hari']."</th>
				<th align=center width=70px >8 - 10 ".$_SESSION['lang']['hari']."</th>
				<th align=center width=70px >11 - 15 ".$_SESSION['lang']['hari']."</th>
				<th align=center width=70px >> 15 ".$_SESSION['lang']['hari']."</th>
				</tr>
        </tr>";
		
$stream.="</thead>
		<tbody>";
		
		$str="select divisi, tanggal, angka, count(luasareaproduktif) as jlh from ".$dbname.".kebun_pusingan_vw where "
			   . " unit='".$kdorg."' and tanggal = '".$tgl2."' group by divisi, angka";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$angka[$bar['divisi']][$bar['tanggal']]=$bar['angka'];
			
			if ($bar['angka'] <=7){
				@$jlh7[$bar['divisi']][$bar['tanggal']]+=$bar['jlh'];
			}	
			else if ($bar['angka'] >7 and $bar['angka'] <= 10){
				@$jlh10[$bar['divisi']][$bar['tanggal']]+=$bar['jlh'];
			}
			else if ($bar['angka'] >10 and $bar['angka'] <= 15){
				@$jlh15[$bar['divisi']][$bar['tanggal']]+=$bar['jlh'];
			}
			else {
				@$jlh16[$bar['divisi']][$bar['tanggal']]+=$bar['jlh'];
			}		

			@$jlhttl[$bar['divisi']][$bar['tanggal']]+=$bar['jlh'];
			
		}	

		$no=0;
		$str="select distinct(substr(kodeorg,1,6)) as divisi from ".$dbname.".setup_blok where "." kodeorg like '".$kdorg."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
		$no+=1;
		$stream.="<tr class=rowcontent>
            <td align=center>".$no."</td>
			<td align=center>".$namaOrg[$bar['divisi']]."</td>
			<td align=center>".@$jlh7[$bar['divisi']][$tgl2]."</td>
			<td align=center>".@$jlh10[$bar['divisi']][$tgl2]."</td>
			<td align=center>".@$jlh15[$bar['divisi']][$tgl2]."</td>
			<td align=center>".@$jlh16[$bar['divisi']][$tgl2]."</td>
			<td align=center>".@$jlhttl[$bar['divisi']][$tgl2]."</td>			
			</tr>";
			
			@$ttljlh7+=$jlh7[$bar['divisi']][$tgl2];
			@$ttljlh10+=$jlh10[$bar['divisi']][$tgl2];
			@$ttljlh15+=$jlh15[$bar['divisi']][$tgl2];
			@$ttljlh16+=$jlh16[$bar['divisi']][$tgl2];
			@$gtjlhttl+=$jlhttl[$bar['divisi']][$tgl2];
			
			
		}
		
		$stream.="<tr bgcolor=#0033CC  style='color:#FFFFFF'>
			<td align=center colspan=2 ><b>TOTAL</b></td>
			<td align=center ><b>".@number_format($ttljlh7)."</b></td>
			<td align=center ><b>".@number_format($ttljlh10)."</b></td>
			<td align=center ><b>".@number_format($ttljlh15)."</b></td>
			<td align=center ><b>".@number_format($ttljlh16)."</b></td>
			<td align=center ><b>".@number_format($gtjlhttl)."</b></td>
			
			</tr>
	</table></fieldset>";

########################################
###### MONITORING OUTPUT PANEN HI ######
########################################
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
	$stream.="</fieldset>";
    $stream.="<fieldset><legend>Monitoring ".$_SESSION['lang']['hasil']." ".$_SESSION['lang']['panen']." ".$_SESSION['lang']['tanggal']." ".tanggalnormal($tgl2)."</legend><table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>
				<tr>
				<th rowspan=2 align=center width=20px >".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 align=center width=70px >".$_SESSION['lang']['divisi']."</th>
				<th colspan=4 align=center>".$_SESSION['lang']['panen']."</th>
				<th colspan=2 align=center>".$_SESSION['lang']['kirim']."</th>
				<th rowspan=2 align=center width=55px>Afkir</th>
				<th colspan=2 align=center>".$_SESSION['lang']['sisa']."(Restan)</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['bjr']."</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['jjg']."/".$_SESSION['lang']['jhk']."</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['kg']."/".$_SESSION['lang']['jhk']."</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['ha']."/".$_SESSION['lang']['jhk']."</th>
				</tr>
				<tr>
				<th align=center width=70px >".$_SESSION['lang']['ha']."</th>
				<th align=center width=70px >".$_SESSION['lang']['jhk']."</th>
				<th align=center width=60px >".$_SESSION['lang']['jjg']."</th>
				<th align=center width=70px >".$_SESSION['lang']['kg']."</th>
				<th align=center width=60px >".$_SESSION['lang']['jjg']."</th>
				<th align=center width=70px >".$_SESSION['lang']['kg']."</th>
				<th align=center width=70px >".$_SESSION['lang']['jjg']."</th>
				<th align=center width=70px >".$_SESSION['lang']['kg']."</th>
				
				</tr>
        </tr>";
		
$stream.="</thead>
		<tbody>";
		## Ambil komponen panen
		$str="select divisi, tanggal, sum(luaspanen) as luas, sum(tenagakerja) as tenagakerja, 
		sum(jjgpanen) as jjgpanen, sum(kgkebun) as kgkebun, sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn_vw where "
			   . " divisi like'".$kdorg."%' and tanggal = '".$tgl2."' group by divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$luas[$bar['divisi']][$bar['tanggal']]+=$bar['luas'];
			// @$hk[$bar['divisi']][$bar['tanggal']]+=$bar['tenagakerja'];
			@$jjgpnn[$bar['divisi']][$bar['tanggal']]+=$bar['jjgpanen'];
			@$kgpnn[$bar['divisi']][$bar['tanggal']]+=$bar['kgkebun'];
			@$jjgafkir[$bar['divisi']][$bar['tanggal']]+=$bar['jjgafkir'];			
		}

		$str="select divisi,sum(hkbuahbesar)+sum(hkbuahkecil) as hk,tanggalpanen as tanggal from ".$dbname.".kebun_3premipemanen where divisi like'".$kdorg."%' and tanggalpanen = '".$tgl2."' group by divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
		
			@$hk[$bar['divisi']][$bar['tanggal']]+=$bar['hk'];		
		}
		
		## Ambil komponen kirim
		$str="select divisi, sum(jjg) as jjg, sum(kgwb) as kg, tanggal from ".$dbname.".kebun_spb_detail_vw where "
			   . " divisi like'".$kdorg."%' and tanggal = '".$tgl2."' group by divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$jjgkrm[$bar['divisi']][$bar['tanggal']]+=$bar['jjg'];
			@$kgkrm[$bar['divisi']][$bar['tanggal']]+=$bar['kg'];
		}
		
		## Ambil jjg panen dari awal buat restan
		$str="select divisi, tanggal, sum(luaspanen) as luas, sum(jjgpanen) as jjgpanen, sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn_vw where "
			   . " divisi like'".$kdorg."%' and tanggal <= '".$tgl2."' group by divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$jjgpnnawal[$bar['divisi']]+=$bar['jjgpanen'];
			@$jjgafkirawal[$bar['divisi']]+=$bar['jjgafkir'];			
		}

		## Ambil jjg kirim buat hitung restan
		$str="select divisi, sum(jjg) as jjg, sum(kgwb) as kg, tanggal from ".$dbname.".kebun_spb_detail_vw where "
			   . " divisi like'".$kdorg."%' and tanggal <= '".$tgl2."' group by divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$divisi[$bar['divisi']]+=$bar['divisi'];
			@$jjgkrmawal[$bar['divisi']]+=$bar['jjg'];
			@$kgkrmawal[$bar['divisi']]+=$bar['kg'];
		}
		
		$no=0;
		$str="select distinct(substr(kodeorg,1,6)) as divisi from ".$dbname.".setup_blok where "." kodeorg like '".$kdorg."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
		$no+=1;
		$stream.="<tr class=rowcontent>
            <td align=center>".$no."</td>
			<td align=center>".$namaOrg[$bar['divisi']]."</td>
			<td align=right>".@nantozero($luas[$bar['divisi']][$tgl2],2)."</td>
			<td align=right>".@nantozero($hk[$bar['divisi']][$tgl2],2)."</td>
			<td align=right>".@nantozero($jjgpnn[$bar['divisi']][$tgl2],0)."</td>
			<td align=right>".@nantozero($kgpnn[$bar['divisi']][$tgl2],0)."</td>
			<td align=right>".@nantozero($jjgkrm[$bar['divisi']][$tgl2],0)."</td>
			<td align=right>".@nantozero($kgkrm[$bar['divisi']][$tgl2],0)."</td>
			<td align=right>".@nantozero($jjgafkir[$bar['divisi']][$tgl2],0)."</td>

			
			<td align=right>".@nantozero((($jjgpnnawal[$bar['divisi']]-$jjgafkirawal[$bar['divisi']])-$jjgkrmawal[$bar['divisi']]),0)."</td>";
			
			
			$kgrestant=@((($jjgpnnawal[$bar['divisi']]-$jjgafkirawal[$bar['divisi']])-$jjgkrmawal[$bar['divisi']])*(($kgkrmawal[$bar['divisi']]/$jjgkrmawal[$bar['divisi']])));
			if(is_nan($kgrestant)){
				$kgrestant=0;
			}else{
				$kgrestant=$kgrestant;
			}
			
		$stream.="<td align=right>".number_format($kgrestant)."</td>
			<td align=right>".@nantozero(($kgkrm[$bar['divisi']][$tgl2]/$jjgkrm[$bar['divisi']][$tgl2]),2)."</td>
			<td align=right>".@nantozero(($jjgpnn[$bar['divisi']][$tgl2]/$hk[$bar['divisi']][$tgl2]))."</td>
			<td align=right>".@nantozero(($kgpnn[$bar['divisi']][$tgl2]/$hk[$bar['divisi']][$tgl2]))."</td>
			<td align=right>".@nantozero(($luas[$bar['divisi']][$tgl2]/$hk[$bar['divisi']][$tgl2]),2)."</td>
			</tr>";
			
			@$ttlluas+=$luas[$bar['divisi']][$tgl2];
			@$ttlhk+=$hk[$bar['divisi']][$tgl2];
			@$ttljjgpnn+=$jjgpnn[$bar['divisi']][$tgl2];
			@$ttlkgpnn+=$kgpnn[$bar['divisi']][$tgl2];
			@$ttljjgkrm+=$jjgkrm[$bar['divisi']][$tgl2];
			@$ttlkgkrm+=$kgkrm[$bar['divisi']][$tgl2];
			@$ttljjgafkir+=$jjgafkir[$bar['divisi']][$tgl2];
			@$ttlrestantjjg+=((($jjgpnnawal[$bar['divisi']]-$jjgafkirawal[$bar['divisi']])-$jjgkrmawal[$bar['divisi']]));
			@$ttlrestantkg+=$kgrestant;
		}
		
		$stream.="<tr bgcolor=#0033CC  style='color:#FFFFFF'>
			<td align=center colspan=2 ><b>TOTAL</b></td>
			<td align=right ><b>".@nantozero($ttlluas,2)."</b></td>
			<td align=right ><b>".@nantozero($ttlhk,2)."</b></td>
			<td align=right ><b>".@nantozero($ttljjgpnn)."</b></td>
			<td align=right ><b>".@nantozero($ttlkgpnn)."</b></td>
			<td align=right ><b>".@nantozero($ttljjgkrm)."</b></td>
			<td align=right ><b>".@nantozero($ttlkgkrm)."</b></td>
			<td align=right ><b>".@nantozero($ttljjgafkir)."</b></td>
			<td align=right ><b>".@nantozero($ttlrestantjjg)."</b></td>
			<td align=right ><b>".@number_format($ttlrestantkg)."</b></td>
			<td align=right ><b>".@nantozero($ttlkgkrm/$ttljjgkrm,2)."</b></td>
			<td align=right ><b>".@nantozero($ttljjgpnn/$ttlhk,0)."</b></td>
			<td align=right ><b>".@nantozero($ttlkgpnn/$ttlhk,0)."</b></td>
			<td align=right ><b>".@nantozero($ttlluas/$ttlhk,2)."</b></td>
			
			</tr>
	</table></fieldset>";
	
###########################################
###### MONITORING OUTPUT PANEN SD HI ######
###########################################
$tgl1 = substr($tgl2,0,8);
		$tgl1 = $tgl1.'01';
		
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<fieldset ><legend>Monitoring ".$_SESSION['lang']['hasil']." ".$_SESSION['lang']['panen']." ".$_SESSION['lang']['dari']." ".$_SESSION['lang']['tanggal']." ".tanggalnormal($tgl1)." ".$_SESSION['lang']['sampai']." ".$_SESSION['lang']['tanggal']." ".tanggalnormal($tgl2)."</legend><table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>
				<tr>
				<th rowspan=2 align=center width=20px >".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 align=center width=70px >".$_SESSION['lang']['divisi']."</th>
				<th colspan=4 align=center>".$_SESSION['lang']['panen']."</th>
				<th colspan=2 align=center>".$_SESSION['lang']['kirim']."</th>
				<th rowspan=2 align=center width=55px>Afkir</th>
				<th colspan=2 align=center>".$_SESSION['lang']['sisa']."(Restan)</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['bjr']."</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['jjg']."/".$_SESSION['lang']['jhk']."</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['kg']."/".$_SESSION['lang']['jhk']."</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['ha']."/".$_SESSION['lang']['jhk']."</th>
				</tr>
				<tr>
				<th align=center width=70px >".$_SESSION['lang']['ha']."</th>
				<th align=center width=70px >".$_SESSION['lang']['jhk']."</th>
				<th align=center width=60px >".$_SESSION['lang']['jjg']."</th>
				<th align=center width=70px >".$_SESSION['lang']['kg']."</th>
				<th align=center width=60px >".$_SESSION['lang']['jjg']."</th>
				<th align=center width=70px >".$_SESSION['lang']['kg']."</th>
				<th align=center width=70px >".$_SESSION['lang']['jjg']."</th>
				<th align=center width=70px >".$_SESSION['lang']['kg']."</th>
				
				</tr>
        </tr>";
		
$stream.="</thead>
		<tbody>";
		## Ambil komponen panen
		$str="select divisi, tanggal, sum(luaspanen) as luas, sum(tenagakerja) as tenagakerja, 
		sum(jjgpanen) as jjgpanen, sum(kgkebun) as kgkebun, sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn_vw where "
			   . " divisi like'".$kdorg."%' and tanggal between '".$tgl1."' and '".$tgl2."' group by divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$luassd[$bar['divisi']]+=$bar['luas'];
			@$hksd[$bar['divisi']]+=$bar['tenagakerja'];
			@$jjgpnnsd[$bar['divisi']]+=$bar['jjgpanen'];
			@$kgpnnsd[$bar['divisi']]+=$bar['kgkebun'];
			@$jjgafkirsd[$bar['divisi']]+=$bar['jjgafkir'];			
		}
		
		
		
		## Ambil komponen kirim
		$str="select divisi, sum(jjg) as jjg, sum(kgwb) as kg, tanggal from ".$dbname.".kebun_spb_vw where "
			   . " divisi like'".$kdorg."%' and tanggal between '".$tgl1."' and '".$tgl2."' group by divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$jjgkrmsd[$bar['divisi']]+=$bar['jjg'];
			@$kgkrmsd[$bar['divisi']]+=$bar['kg'];
		}
	
		$no=0;
		$str="select distinct(substr(kodeorg,1,6)) as divisi from ".$dbname.".setup_blok where "." kodeorg like '".$kdorg."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
		$no+=1;
		$stream.="<tr class=rowcontent>
            <td align=center>".@$no."</td>
			<td align=center>".$namaOrg[@$bar[divisi]]."</td>
			<td align=right>".@nantozero($luassd[$bar['divisi']],2)."</td>
			<td align=right>".@nantozero($hksd[$bar['divisi']],2)."</td>
			<td align=right>".@nantozero($jjgpnnsd[$bar['divisi']],0)."</td>
			<td align=right>".@nantozero($kgpnnsd[$bar['divisi']],0)."</td>
			<td align=right>".@nantozero($jjgkrmsd[$bar['divisi']],0)."</td>
			<td align=right>".@nantozero($kgkrmsd[$bar['divisi']],0)."</td>
			<td align=right>".@nantozero($jjgafkirsd[$bar['divisi']],0)."</td>		
			<td align=right>".@nantozero((($jjgpnnawal[$bar['divisi']]-$jjgafkirawal[$bar['divisi']])-$jjgkrmawal[$bar['divisi']]),0)."</td>";
			
			$kgrestantsd=@((($jjgpnnawal[$bar['divisi']]-$jjgafkirawal[$bar['divisi']])-$jjgkrmawal[$bar['divisi']])*(($kgkrmawal[$bar['divisi']]/$jjgkrmawal[$bar['divisi']])));
			if(is_nan($kgrestantsd)){
				$kgrestantsd=0;
			}else{
				$kgrestantsd=$kgrestantsd;
			}
			
		$stream.="<td align=right>".@number_format($kgrestantsd,0)."</td>
			<td align=right>".@nantozero(($kgkrmsd[$bar['divisi']]/$jjgkrmsd[$bar['divisi']]),2)."</td>
			<td align=right>".@nantozero(($jjgpnnsd[$bar['divisi']]/$hksd[$bar['divisi']]))."</td>
			<td align=right>".@nantozero(($kgpnnsd[$bar['divisi']]/$hksd[$bar['divisi']]))."</td>
			<td align=right>".@nantozero(($luassd[$bar['divisi']]/$hksd[$bar['divisi']]),2)."</td>
			</tr>";
			
			@$ttlluassd+=$luassd[$bar['divisi']];
			@$ttlhksd+=$hksd[$bar['divisi']];
			@$ttljjgpnnsd+=$jjgpnnsd[$bar['divisi']];
			@$ttlkgpnnsd+=$kgpnnsd[$bar['divisi']];
			@$ttljjgkrmsd+=$jjgkrmsd[$bar['divisi']];
			@$ttlkgkrmsd+=$kgkrmsd[$bar['divisi']];
			@$ttljjgafkirsd+=$jjgafkirsd[$bar['divisi']];
			@$ttlrestantjjgsd+=((($jjgpnnawal[$bar['divisi']]-$jjgafkirawal[$bar['divisi']])-$jjgkrmawal[$bar['divisi']]));
			@$ttlrestantkgsd+=($kgrestantsd);
		}
		
		$stream.="<tr bgcolor=#0033CC  style='color:#FFFFFF'>
			<td align=center colspan=2 ><b>TOTAL</b></td>
			<td align=right ><b>".@nantozero($ttlluassd,2)."</b></td>
			<td align=right ><b>".@nantozero($ttlhksd,2)."</b></td>
			<td align=right ><b>".@nantozero($ttljjgpnnsd)."</b></td>
			<td align=right ><b>".@nantozero($ttlkgpnnsd)."</b></td>
			<td align=right ><b>".@nantozero($ttljjgkrmsd)."</b></td>
			<td align=right ><b>".@nantozero($ttlkgkrmsd)."</b></td>
			<td align=right ><b>".@nantozero($ttljjgafkirsd)."</b></td>
			<td align=right ><b>".@nantozero($ttlrestantjjgsd)."</b></td>
			<td align=right ><b>".@number_format($ttlrestantkgsd)."</b></td>
			<td align=right ><b>".@nantozero($ttlkgkrmsd/$ttljjgkrmsd,2)."</b></td>
			<td align=right ><b>".@nantozero($ttljjgpnnsd/$ttlhksd,0)."</b></td>
			<td align=right ><b>".@nantozero($ttlkgpnnsd/$ttlhksd,0)."</b></td>
			<td align=right ><b>".@nantozero($ttlluassd/$ttlhksd,2)."</b></td>
			
			</tr>
	</table></fieldset>";

	
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<fieldset ><legend>".$_SESSION['lang']['detail']." ".$_SESSION['lang']['pusingan']." ".$_SESSION['lang']['tanggal']." ".tanggalnormal($tgl2)."</legend><table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>

				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['divisi']."</th>
				<th align=center>".$_SESSION['lang']['blok']."</th>
				<th align=center width=60px >".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['tanam']."</th>
				<th align=center>".$_SESSION['lang']['luas']." (Ha)</th>
				<th align=center>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['pokok']."</th>
				<th align=center>SPH</th>
				<th align=center>".$_SESSION['lang']['pusingan']."</th>
				</tr>
        </tr>";
		
$stream.="</thead>
		<tbody>";
		$no=0;
		$str="select a.*, b.* from ".$dbname.".setup_blok_tahunan a left join ".$dbname.".kebun_pusingan_vw b on
		a.kodeorg = b.blok where "." a.kodeorg like '".$kdorg."%' and b.tanggal = '".$tgl2."' and a.tahun='".substr(str_replace('-', '', $tgl2),0,6)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlbaris($res);
		if($numrows==0)
		{

		$str="select a.*, b.* from ".$dbname.".setup_blok a left join ".$dbname.".kebun_pusingan_vw b on
		a.kodeorg = b.blok where "." a.kodeorg like '".$kdorg."%' and b.tanggal = '".$tgl2."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		}

		//exit('error : '.$str);
		while($bar=$res->fetch())
		{
		if (@$bar[angka]=='1' and @$bar[keterangan]=='P')
			{
				@$bgcolor="bgcolor=blue";
			}
			else if (@$bar[angka]>'1' and @$bar[keterangan]=='P')
			{
				@$bgcolor="bgcolor=red";
			}
			else
			{
				$bgcolor="";
			}
				
		@$no+=1;
		$stream.="<tr class=rowcontent>
            <td align=center>".@$no."</td>
			<td align=center>".$namaOrg[@substr($bar[kodeorg],0,6)]."</td>
			<td align=center>".$namaOrg[@$bar[kodeorg]]."</td>
			<td align=center>".@$bar[tahuntanam]."</td>
			<td align=right>".@nantozero($bar[luasareaproduktif],2)."</td>
			<td align=right>".@nantozero($bar[jumlahpokok],0)."</td>
			<td align=right>".@nantozero($bar[jumlahpokok]/$bar[luasareaproduktif],2)."</td>
			<td align=center ".@$bgcolor.">".@$bar[angka]."</td>
		    </tr>";
					
			@$gtluas+=$bar[luasareaproduktif];
			@$gtpokok+=$bar[jumlahpokok];
			

		}
		
		$stream.="<tr bgcolor=#0033CC  style='color:#FFFFFF'>
			<td align=center colspan=4 ><b>TOTAL</b></td>
			<td align=right ><b>".@nantozero($gtluas,2)."</b></td>
			<td align=right ><b>".@nantozero($gtpokok)."</b></td>
			<td align=right ><b>".@nantozero($gtpokok/$gtluas,2)."</b></td>
			<td align=right ><b></b></td>
			</tr>
	</table></fieldset>";

	
$stream.="
 </tbody>";

		
switch ($proses) {
    case 'preview':
	// exit ("error:masuk");
        echo $stream;
        break;

    case 'excel':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "Rekap Pusingan Tgl " . $tgl2. " unit ". $kdorg;
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

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}

?>