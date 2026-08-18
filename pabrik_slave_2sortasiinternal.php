<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$intex=checkPostGet('intex','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

if($tgl1=='' || $tgl2=='' || $tgl1=='--' || $tgl2=='--'){
	echo"Error: Tanggal tidak boleh kosong"; 
	exit;
}

if($tgl1>$tgl2){
	echo"Error: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
	exit;
}

$dttgl=rangeTanggalarr($tgl1,$tgl2);

$pt=makeOption($dbname,'organisasi','kodeorganisasi,induk');

if($intex==3){
	$whr="";
} else if ($intex==1) { //internal	
	$whr.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$unit]."') ";
} else if($intex==2){
	$whr.=" and kodeorg not in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$unit]."') ";
}
	
	
$stream='';
if ($proses == 'excel') {
	$stream.="<table class=sortable cellspacing=1 border=1>";
} else 	{
	$stream.="<div class='table-scroll'><table class=sortable cellspacing=1 width=110%>";
}

$stream.="<thead>
<tr>
	<th rowspan=3 align=center>".$_SESSION['lang']['divisi']."</th>
	<th rowspan=3 align=center>".$_SESSION['lang']['ticket']."</th>
	<th rowspan=3 align=center style='min-width:80px'>Tanggal</th>
	<th colspan=3 align=center>TBS</th>
	<th colspan=28 align=center>KRETERIA GRADING</th>
	<th colspan=4 rowspan=2 align=center>TOTAL GRADING</th>
</tr>
<tr>
	<th align=center>Masuk</th>
	<th rowspan=2 align=center>Jum. JJ</th>
	<th rowspan=2 align=center>BJR</th>
	<th colspan=4 align=center>F00 (Buah Sangat Mentah)</th>
	<th colspan=4 align=center>F0 (Buah Mentah)</th>
	<th colspan=4 align=center>F5 (Buah Lewat Matang)</th>
	<th colspan=4 align=center>F6 (Tandan Kosong)</th>
	<th colspan=4 align=center>Gagang Panjang</th>
	<th colspan=4 align=center>Basah Pasir (<3 Kg)</th>
	<th colspan=4 align=center>Sampah Air</th>
</tr>
<tr>
	<th align=center>(Kg)</th>";

	for ($i=1; $i < 8; $i++) { 
		$stream.="
		<th align=center>(Kg)</th>
	    <th align=center>(%)</th>

	    <th align=center>(Kg) Proporsi</th>
	    <th align=center>(%) Proporsi</th>";
	}

$stream.="    
<th align=center>(Kg)</th>
<th align=center>(%)</th>

<th align=center>(Kg) Proporsi</th>
<th align=center>(%) Proporsi</th>
</tr>
</thead>
";

#tbs masuk
$str="select * from ".$dbname.".pabrik_timbangan_vw where kodebarang='40000003' and millcode='".$unit."' and tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg!='' ".$whr." order by tanggal asc";
// exit('error');
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$kgpot=array();
while($bar=$res->fetch()){
	$arrKebun[$bar['kodeorg']] = $bar['kodeorg'];
	$arrDivisi[$bar['kodeorg']][$bar['divisi']] = $bar['divisi'];
	$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['tanggal'] = $bar['tanggal'];
	$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['tbsmasuk'] = $bar['beratbersih'];
	$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['jmljjg'] = $bar['jjg'];
	$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['bjr'] = $bar['bjr'];
	
	$kgpot[$bar['kodeorg']][$bar['divisi']] += $bar['kgpotsortasi'];
	$gtkgpot += $bar['kgpotsortasi'];

	$strx="select * from ".$dbname.".pabrik_sortasi where notiket='".$bar['notiket']."'";
	$resx=fetchdata($strx);
	foreach($resx as $keyx=>$valx){
		if($valx['kodefraksi']=='mentah'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mentahkg'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mentahper'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='mengkal'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mengkalkg'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mengkalper'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='restan'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['restankg'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['restanper'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='buahbusuk'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['busukkg'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['busukper'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='panjang'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['panjangkg'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['panjangper'] = $valx['persen'];
		}
	
		if($valx['kodefraksi']=='sampah'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['sampahkg'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['sampahper'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='mutu'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mutukg'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mutuper'] = $valx['persen'];
		}
	}

	##sortasi proporsi
	$strx="select * from ".$dbname.".pabrik_sortasi_proporsi where notiket='".$bar['notiket']."'";
	$resx=fetchdata($strx);
	foreach($resx as $keyx=>$valx){
		if($valx['kodefraksi']=='mentah'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mentahkgprop'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mentahperprop'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='mengkal'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mengkalkgprop'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mengkalperprop'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='restan'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['restankgprop'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['restanperprop'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='buahbusuk'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['busukkgprop'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['busukperprop'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='panjang'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['panjangkgprop'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['panjangperprop'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='sampah'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['sampahkgprop'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['sampahperprop'] = $valx['persen'];
		}
		if($valx['kodefraksi']=='mutu'){
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mutukgprop'] = $valx['kg'];
			$arrList[$bar['kodeorg']][$bar['divisi']][$bar['notiket']]['mutuperprop'] = $valx['persen'];
		}
	}
}

$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$grdtbsmasuk = $grdjmljjg = $grdmentahkg = $grdmengkalkg = $grdrestankg = $grdbusukkg = $grdpanjangkg = $grdsampahkg = $grdmutukg = $grdgradkg = 0;

$grdtbsmasukprop = $grdmentahkgprop = $grdmengkalkgprop = $grdrestankgprop = $grdbusukkgprop = $grdpanjangkgprop = $grdsampahkgprop = $grdmutukgprop = $grdgradkgprop = 0;
foreach($arrKebun as $val){
	$stream.="<tr class=rowcontent>
		<td colspan='38' style='font-weight:bold'>".$optNmOrg[$val]."</td>";
	$stream.="</tr>";
	asort($arrDivisi[$val]);
	foreach($arrDivisi[$val] as $val2){
		$stream.="<tr class=rowcontent>
			<td colspan='38' style='font-weight:bold'>".$optNmOrg[$val2]."</td>";
		$stream.="</tr>";
		$ttltbsmasuk = $ttljmljjg = $ttlmentahkg = $ttlmengkalkg = $ttlrestankg = $ttlbusukkg = $ttlpanjangkg = $ttlsampahkg = $ttlmutukg = $ttlgradkg = 0;

		$ttltbsmasukprop = $ttlmentahkgprop = $ttlmengkalkgprop = $ttlrestankgprop = $ttlbusukkgprop = $ttlpanjangkgprop = $ttlsampahkgprop = $ttlmutukgprop = $ttlgradkgprop = 0;

		foreach($arrList[$val][$val2] as $key3=>$val3){
			$totalgradkg = $val3['mentahkg'] + $val3['mengkalkg'] + $val3['restankg'] + $val3['busukkg'] + $val3['panjangkg'] + $val3['sampahkg'] + $val3['mutukg'];
			$totalgradper = $val3['mentahper'] + $val3['mengkalper'] + $val3['restanper'] + $val3['busukper'] + $val3['panjangper'] + $val3['sampahper'] + $val3['mutuper'];

			$totalgradkgprop = $val3['mentahkgprop'] + $val3['mengkalkgprop'] + $val3['restankgprop'] + $val3['busukkgprop'] + $val3['panjangkgprop'] +  $val3['sampahkgprop'] + $val3['mutukg'];
			$totalgradperprop = $val3['mentahperprop'] + $val3['mengkalperprop'] + $val3['restanperprop'] + $val3['busukperprop'] + $val3['panjangperprop'] + $val3['sampahperprop'] + $val3['mutuper'];
			
			$color="";
			$cekfile = makeOption($dbname,'listfileupload','notransaksi,namafile',"notransaksi ='".$key3."'");
			if($cekfile[$key3]!=''){
				$color="style=background-color:cyan;";
			}
			$stream.="<tr class=rowcontent>
				<td align=center ".$color."><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$key3."','SORTINT');\" ></td>
				<td align='center'>".$key3."</td>
				<td align='center'>".tanggalnormal($val3['tanggal'])."</td>
				<td align='right'>".hidezerodecimal($val3['tbsmasuk'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['jmljjg'],0)."</td>
				<td align='right'>".$val3['bjr']."</td>

				<td align='right'>".hidezerodecimal($val3['mentahkg'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['mentahper'],2)."</td>
				<td align='right'>".hidezerodecimal($val3['mentahkgprop'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['mentahperprop'],2)."</td>

				<td align='right'>".hidezerodecimal($val3['mengkalkg'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['mengkalper'],2)."</td>
				<td align='right'>".hidezerodecimal($val3['mengkalkgprop'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['mengkalperprop'],2)."</td>
				
				<td align='right'>".hidezerodecimal($val3['restankg'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['restanper'],2)."</td>
				<td align='right'>".hidezerodecimal($val3['restankgprop'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['restanperprop'],2)."</td>

				<td align='right'>".hidezerodecimal($val3['busukkg'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['busukper'],2)."</td>
				<td align='right'>".hidezerodecimal($val3['busukkgprop'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['busukperprop'],2)."</td>

				<td align='right'>".hidezerodecimal($val3['panjangkg'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['panjangper'],2)."</td>
				<td align='right'>".hidezerodecimal($val3['panjangkgprop'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['panjangperprop'],2)."</td>

				<td align='right'>".hidezerodecimal($val3['sampahkg'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['sampahper'],2)."</td>
				<td align='right'>".hidezerodecimal($val3['sampahkgprop'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['sampahperprop'],2)."</td>

				<td align='right'>".hidezerodecimal($val3['mutukg'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['mutuper'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['mutukgprop'],0)."</td>
				<td align='right'>".hidezerodecimal($val3['mutuperprop'],0)."</td>

				<td align='right'>".hidezerodecimal($totalgradkg,0)."</td>
				<td align='right'>".hidezerodecimal($totalgradper,2)."</td>
				<td align='right'>".hidezerodecimal($totalgradkgprop,0)."</td>
				<td align='right'>".hidezerodecimal($totalgradperprop,2)."</td>
				";
			$stream.="</tr>";
			
			$ttltbsmasuk = $ttltbsmasuk + $val3['tbsmasuk'];
			$ttljmljjg = $ttljmljjg + $val3['jmljjg'];
			
			$ttlmentahkg = $ttlmentahkg + $val3['mentahkg'];
			$ttlmengkalkg = $ttlmengkalkg + $val3['mengkalkg'];
			$ttlrestankg = $ttlrestankg + $val3['restankg'];
			$ttlbusukkg = $ttlbusukkg + $val3['busukkg'];
			$ttlpanjangkg = $ttlpanjangkg + $val3['panjangkg'];
			$ttlsampahkg = $ttlsampahkg + $val3['sampahkg'];
			$ttlmutukg = $ttlmutukg + $val3['mutukg'];
			$ttlgradkg = $ttlgradkg + $totalgradkg;

			#ttl sortasi proporsi
			$ttlmentahkgprop = $ttlmentahkgprop + $val3['mentahkgprop'];
			$ttlmengkalkgprop = $ttlmengkalkgprop + $val3['mengkalkgprop'];
			$ttlrestankgprop = $ttlrestankgprop + $val3['restankgprop'];
			$ttlbusukkgprop = $ttlbusukkgprop + $val3['busukkgprop'];
			$ttlpanjangkgprop = $ttlpanjangkgprop + $val3['panjangkgprop'];
			$ttlsampahkgprop = $ttlsampahkgprop + $val3['sampahkgprop'];
			$ttlmutukgprop = $ttlmutukgprop + $val3['mutukgprop'];
			$ttlgradkgprop = $ttlgradkgprop + $totalgradkgprop;
			
			$grdtbsmasuk = $grdtbsmasuk + $val3['tbsmasuk'];
			$grdjmljjg = $grdjmljjg + $val3['jmljjg'];
			
			$grdmentahkg = $grdmentahkg + $val3['mentahkg'];
			$grdmengkalkg = $grdmengkalkg + $val3['mengkalkg'];
			$grdrestankg = $grdrestankg + $val3['restankg'];
			$grdbusukkg = $grdbusukkg + $val3['busukkg'];
			$grdpanjangkg = $grdpanjangkg + $val3['panjangkg'];
			$grdsampahkg = $grdsampahkg + $val3['sampahkg'];
			$grdmutukg = $grdmutukg + $val3['mutukg'];
			$grdgradkg = $grdgradkg + $totalgradkg;

			#grnd sortasi proporsi
			$grdmentahkgprop = $grdmentahkgprop + $val3['mentahkgprop'];
			$grdmengkalkgprop = $grdmengkalkgprop + $val3['mengkalkgprop'];
			$grdrestankgprop = $grdrestankgprop + $val3['restankgprop'];
			$grdbusukkgprop = $grdbusukkgprop + $val3['busukkgprop'];
			$grdpanjangkgprop = $grdpanjangkgprop + $val3['panjangkgprop'];
			$grdsampahkgprop = $grdsampahkgprop + $val3['sampahkgprop'];
			$grdmutukgprop = $grdmutukgprop + $val3['mutukgprop'];
			$grdgradkgprop = $grdgradkgprop + $totalgradkgprop;
		}
		$stream.="<tr class='rowcontent' style='font-weight:bold'>
			<td colspan=3 style='text-align:right;'>Total ".$optNmOrg[$val2]."</td>
			<td style='text-align:right'>".hidezerodecimal($ttltbsmasuk,0)."</td>
			<td style='text-align:right'>".hidezerodecimal($ttljmljjg,0)."</td>
			<td style='text-align:right'></td>
			";

			$stream.="
			<td style='text-align:right'>".hidezerodecimal($ttlmentahkg,0)."</td>
			<td style='text-align:right'></td>
			<td style='text-align:right'>".hidezerodecimal($ttlmentahkgprop,0)."</td>
			<td style='text-align:right'></td>

			<td style='text-align:right'>".hidezerodecimal($ttlmengkalkg,0)."</td>
			<td style='text-align:right'></td>
			<td style='text-align:right'>".hidezerodecimal($ttlmengkalkgprop,0)."</td>
			<td style='text-align:right'></td>

			<td style='text-align:right'>".hidezerodecimal($ttlrestankg,0)."</td>
			<td style='text-align:right'></td>
			<td style='text-align:right'>".hidezerodecimal($ttlrestankgprop,0)."</td>
			<td style='text-align:right'></td>

			<td style='text-align:right'>".hidezerodecimal($ttlbusukkg,0)."</td>
			<td style='text-align:right'></td>
			<td style='text-align:right'>".hidezerodecimal($ttlbusukkgprop,0)."</td>
			<td style='text-align:right'></td>

			<td style='text-align:right'>".hidezerodecimal($ttlpanjangkg,0)."</td>
			<td style='text-align:right'></td>
			<td style='text-align:right'>".hidezerodecimal($ttlpanjangkgprop,0)."</td>
			<td style='text-align:right'></td>

			<td style='text-align:right'>".hidezerodecimal($ttlsampahkg,0)."</td>
			<td style='text-align:right'></td>
			<td style='text-align:right'>".hidezerodecimal($ttlsampahkgprop,0)."</td>
			<td style='text-align:right'></td>


			<td style='text-align:right'>".hidezerodecimal($ttlmutukg,0)."</td>
			<td style='text-align:right'></td>
			<td style='text-align:right'>".hidezerodecimal($ttlmutukgprop,0)."</td>
			<td style='text-align:right'></td>


			<td style='text-align:right'>".hidezerodecimal($kgpot[$val][$val2],0)."</td>
			<td style='text-align:right'></td>
			<td style='text-align:right'>".hidezerodecimal($ttlgradkgprop,0)."</td>
			<td style='text-align:right'></td>
		</tr>";
	}
}
$stream.="<tr class='rowcontent' style='font-weight:bold'>
	<td colspan=3 style='text-align:right;'>Grand Total</td>
	<td style='text-align:right'>".hidezerodecimal($grdtbsmasuk,0)."</td>
	<td style='text-align:right'>".hidezerodecimal($grdjmljjg,0)."</td>
	<td style='text-align:right'></td>

	<td style='text-align:right'>".hidezerodecimal($grdmentahkg,0)."</td>
	<td style='text-align:right'></td>
	<td style='text-align:right'>".hidezerodecimal($grdmentahkgprop,0)."</td>
	<td style='text-align:right'></td>

	<td style='text-align:right'>".hidezerodecimal($grdmengkalkg,0)."</td>
	<td style='text-align:right'></td>
	<td style='text-align:right'>".hidezerodecimal($grdmengkalkgprop,0)."</td>
	<td style='text-align:right'></td>

	<td style='text-align:right'>".hidezerodecimal($grdrestankg,0)."</td>
	<td style='text-align:right'></td>
	<td style='text-align:right'>".hidezerodecimal($grdrestankgprop,0)."</td>
	<td style='text-align:right'></td>

	<td style='text-align:right'>".hidezerodecimal($grdbusukkg,0)."</td>
	<td style='text-align:right'></td>
	<td style='text-align:right'>".hidezerodecimal($grdbusukkgprop,0)."</td>
	<td style='text-align:right'></td>

	<td style='text-align:right'>".hidezerodecimal($grdpanjangkg,0)."</td>
	<td style='text-align:right'></td>
	<td style='text-align:right'>".hidezerodecimal($grdpanjangkgprop,0)."</td>
	<td style='text-align:right'></td>

	<td style='text-align:right'>".hidezerodecimal($grdsampahkg,0)."</td>
	<td style='text-align:right'></td>
	<td style='text-align:right'>".hidezerodecimal($grdsampahkgprop,0)."</td>
	<td style='text-align:right'></td>

	<td style='text-align:right'>".hidezerodecimal($grdmutukg,0)."</td>
	<td style='text-align:right'></td>
	<td style='text-align:right'>".hidezerodecimal($grdmutukgprop,0)."</td>
	<td style='text-align:right'></td>

	<td style='text-align:right'>".hidezerodecimal($gtkgpot,0)."</td>
	<td style='text-align:right'></td>
	<td style='text-align:right'>".hidezerodecimal($grdgradkgprop,0)."</td>
	<td style='text-align:right'></td>
</tr>";
$stream.="</tbody></table></div><br>";
		
switch($proses)
{
    
    case'preview':
         echo $stream;
	break;
    
    ######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_sortasi_internal";
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempFile')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempFile/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempFile/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempFile/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
		break;
                
	default:
}



?>