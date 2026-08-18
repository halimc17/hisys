<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$proses   = checkPostGet('proses', '');
$divisi   = checkPostGet('divisi', '');
$periode  = checkPostGet('periode', '');
$klpakun  = checkPostGet('kdklpakun', '');
$akun     = checkPostGet('kdakun', '');
$kegiatan = checkPostGet('keg', '');
$kdorg    = checkPostGet('kdorg', '');

$periode1 = substr($periode,0,4)."-01";

$arrsdi   =explode('-',$periode); 
$tahun    =$arrsdi[0]; 
$bulan    =$arrsdi[1];

if ($kdorg == '') {
    echo"Warning: Unit tidak boleh kosong";
    exit;
}

if (($periode == '')) {
    echo"Warning: Periode tidak boleh kosong";
    exit;
}

if ($proses == 'excel') {
    $tab = "<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<table class=sortable cellspacing=1>";
}

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan=2>" . $_SESSION['lang']['uraian'] . "</th>
            <th align=center rowspan=2>" . $_SESSION['lang']['satuan'] . "</th>
            <th align=center colspan=2>" . $_SESSION['lang']['aktual'] . "</th>
            <th align=center colspan=2>" . $_SESSION['lang']['budget'] . "</th>
            <th align=center rowspan=2>" . $_SESSION['lang']['budget'] . "<br>" . $_SESSION['lang']['setahun'] . "</th>
        </tr>    
        <tr class=rowheader>
            <th align=center>Bi</th>
            <th align=center>s/d Bi</th>
            <th align=center>Bi</th>
            <th align=center>s/d Bi</th>
        ";

$tab.="</tr></thead><tbody>";

$wh=$whx=$whj="";
if($divisi!=''){
	$wh.=" and a.kodeorg like '".$divisi."%'";
	$whx.=" and a.kodeblok like '".$divisi."%'";
	$whj.=" and a.kodeblok like '".$divisi."%'";
}
if($kdorg!=''){
	$wh.=" and a.kodeorg like '".$kdorg."%'";
	$whx.=" and a.kodeblok like '".$kdorg."%'";
	$whj.=" and a.kodeorg like '".$kdorg."%'";
}
$str = "select * from " . $dbname . ".setup_blok a where 1=1 ".$wh."";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$luastotal+= $bar['luasareaproduktif'];
}

$str = "select * from " . $dbname . ".bgt_blok a where 1=1 ".$whx." and tahunbudget='".$tahun."'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$luasbgt+= $bar['hathnini'];
}



$whr=$whrb=$whrj="";
if($kegiatan!=''){
	$whr=" and a.kodekegiatan like '".$kegiatan."%'";
	$whrb=" and a.kegiatan like '".$kegiatan."%'";
	$whrj=" and a.kodekegiatan like '".$kegiatan."%'";
}else if($akun!=''){
	$whr=" and a.kodekegiatan like '".$akun."%'";
	$whrb=" and a.noakun like '".$akun."%'";
	$whrj=" and a.noakun like '".$akun."%'";
}elseif($klpakun!=''){
	$whr=" and a.kodekegiatan like '".$klpakun."%'";
	$whrb=" and a.noakun like '".$klpakun."%'";
	$whrj=" and a.noakun like '".$klpakun."%'";
}else{
	exit("Error : Kelompok Biaya Wajib diisi !!!");
}

#kegiatan dan prestasi kerja
$str = "select sum(hasilkerja) as hasilkerja,kodekegiatan,substr(b.tanggal,1,7) as periode  from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 ".$wh." ".$whr." and substr(b.tanggal,1,7) between '".$periode1."' and  '".$periode."' and a.notransaksi not like '%/BOR/%' group by kodekegiatan, periode";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$arrkeg[$bar['kodekegiatan']] = $bar['kodekegiatan'];
	if($bar['periode']==$periode){
		@$hasilkerjabi[$bar['kodekegiatan']] += $bar['hasilkerja'];
	}
	@$hasilkerjasdbi[$bar['kodekegiatan']] += $bar['hasilkerja'];
}

#Borongan Sendiri
$str = "select sum(hasilkerja) as hasilkerja,kodekegiatan,substr(b.tanggal,1,7) as periode  from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 ".$wh." ".$whr." and substr(b.tanggal,1,7) between '".$periode1."' and  '".$periode."' and a.notransaksi like '%/BOR/%' group by kodekegiatan, periode";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$arrkegspk[$bar['kodekegiatan']]['bor'] = $bar['kodekegiatan'];
	if($bar['periode']==$periode){
		@$hasilkerjabispk[$bar['kodekegiatan']]['bor'] += $bar['hasilkerja'];
	}
	@$hasilkerjasdbispk[$bar['kodekegiatan']]['bor'] += $bar['hasilkerja'];
}

#SPK
$str = "select sum(hasilkerjarealisasi) as hasilkerja,kodekegiatan,substr(a.tanggal,1,7) as periode  from " . $dbname . ".log_baspk a where 1=1 ".$whx." ".$whr." and substr(a.tanggal,1,7) between '".$periode1."' and  '".$periode."' and statusjurnal='1' group by kodekegiatan, periode";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$arrkegspk[$bar['kodekegiatan']]['spk'] = $bar['kodekegiatan'];
	if($bar['periode']==$periode){
		@$hasilkerjabispk[$bar['kodekegiatan']]['spk'] += $bar['hasilkerja'];
	}
	@$hasilkerjasdbispk[$bar['kodekegiatan']]['spk'] += $bar['hasilkerja'];
}


$e="("; $s="(";
for($i=01;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";
$kodetipekary=array('SDM-KBL'=>'1','SDM-KHT'=>'1','SDM-KNT'=>'1','SDM-PHL'=>'1','SDM-PRE'=>'100','SDM-PREBRD'=>'100','SDM-PREHDR'=>'100','SDM-PREKRN'=>'200','SDM-PRELB'=>'100','SDM-PRELBR'=>'100','SDM-PREMDR'=>'200','SDM-PREMDR1'=>'200','SDM-PRESPV'=>'200','KONTRAK'=>'101');

$str=" select tipebudget,kodebudget,kodevhc,kodebarang,kegiatan,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$wh." and tahunbudget = '".$tahun."' ".$whrb."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$arrkeg[$bar['kegiatan']] = $bar['kegiatan'];
	#setahun
	@$bgtrpthn[$bar['kegiatan']] += $bar['rupiah'];
	
	if($bar['kodebudget']=='SDM-PREKRN' or $bar['kodebudget']=='SDM-PREMDR' or $bar['kodebudget']=='SDM-PREMDR1' or $bar['kodebudget']=='SDM-PRESPV' or $bar['kodebudget']=='SUPERVISI'){
		#budget supervisi
		@$arrdataspv[$bar['kegiatan']]= $bar['kegiatan'];
		@$bgtspvbi[$bar['kegiatan']]+= $bar['bi'];
		@$bgtspvsbi[$bar['kegiatan']]+= $bar['sdbi'];
		@$bgtspvthn[$bar['kegiatan']]+= $bar['rupiah'];
	}
		
	if(substr($bar['kodebudget'],0,3)=='SDM'){
		$lstdatahk[$kodetipekary[$bar['kodebudget']]] = $kodetipekary[$bar['kodebudget']];
		#setahun
		@$bgtbivolthn[$bar['kegiatan']] += $bar['fsetahun'];
		if(substr($bar['kodebudget'],4,3)=='KBL' or substr($bar['kodebudget'],4,3)=='KHT' or substr($bar['kodebudget'],4,3)=='KNT' or substr($bar['kodebudget'],4,3)=='PHL'){
			$arrdatahk[$kodetipekary[$bar['kodebudget']]] = array('hk'=>'HK','umr'=>'Rp','premi'=>'Rp');
			@$bgthkthn[$kodetipekary[$bar['kodebudget']]]['hk'] += $bar['fsetahun'];
			@$bgthkthn[$kodetipekary[$bar['kodebudget']]]['umr'] += $bar['rupiah'];
			@$bgthkbi[$kodetipekary[$bar['kodebudget']]]['hk'] += $bar['bivol'];
			@$bgthkbi[$kodetipekary[$bar['kodebudget']]]['umr'] += $bar['bi'];
			@$bgthksdbi[$kodetipekary[$bar['kodebudget']]]['hk'] += $bar['sdbivol'];
			@$bgthksdbi[$kodetipekary[$bar['kodebudget']]]['umr'] += $bar['sdbi'];
		}elseif($bar['kodebudget']!='SDM-PREKRN' and $bar['kodebudget']!='SDM-PREMDR' and $bar['kodebudget']!='SDM-PREMDR1' and $bar['kodebudget']!='SDM-PRESPV' and $bar['kodebudget']!='SUPERVISI'){
			$arrdatahk[$kodetipekary[$bar['kodebudget']]] = array('premi'=>'Rp');
			
			@$bgthkthn[$kodetipekary[$bar['kodebudget']]]['premi'] += $bar['rupiah'];
			@$bgthkbi[$kodetipekary[$bar['kodebudget']]]['premi'] += $bar['bi'];
			@$bgthksdbi[$kodetipekary[$bar['kodebudget']]]['premi'] += $bar['sdbi'];
		}
		
		#bi
		@$bgtbivol[$bar['kegiatan']] += $bar['bivol'];
		@$bgtrpbi[$bar['kegiatan']] += $bar['bi'];
		#sdbi
		@$bgtsdbivol[$bar['kegiatan']] += $bar['sdbivol'];
		@$bgtrpsdbi[$bar['kegiatan']] += $bar['sdbi'];
	}
	
	#kontak
	if($bar['kodebudget']=='KONTRAK'){
		$arrdatahk[$kodetipekary[$bar['kodebudget']]] = array('spk'=>'SPK','bor'=>'Bor');
		@$bgthkthn[$kodetipekary[$bar['kodebudget']]]['spk'] += $bar['rupiah'];
		@$bgthkbi[$kodetipekary[$bar['kodebudget']]]['spk'] += $bar['bi'];
		@$bgthksdbi[$kodetipekary[$bar['kodebudget']]]['spk'] += $bar['sdbi'];		
		
		@$arrkegspk[$bar['kegiatan']]['spk'] = $bar['kegiatan'];
		@$arrkegspk[$bar['kegiatan']]['bor'] = $bar['kegiatan'];
		#fisik
		@$bgtbivolthnspk[$bar['kegiatan']]['spk'] += $bar['fsetahun'];
		#bi
		@$bgtbivolspk[$bar['kegiatan']]['spk'] += $bar['bivol'];
		#sdbi
		@$bgtsdbivolspk[$bar['kegiatan']]['spk'] += $bar['sdbivol'];
	}
	
	#material
	if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
		#$arrdatamat[$bar['kodebarang']]= array('kwantitas','harga','total');
		$arrdatamat[$bar['kodebarang']]= array('kwantitas');
		@$databgtmatbi[$bar['kodebarang']]['kwantitas'] += $bar['bivol'];
		@$databgtmatsdbi[$bar['kodebarang']]['kwantitas']+= $bar['sdbivol'];
		@$databgtmatthn[$bar['kodebarang']]['kwantitas'] += $bar['fsetahun'];
		@$ttlbgtmatbi+= $bar['bi'];
		@$ttlbgtmatsdbi+= $bar['sdbi'];
		@$ttlbgtmatthn+= $bar['rupiah'];
	}
	
	#material
	if($bar['kodebudget']=='VHC'){
		@$arrdatavhc[$bar['kodevhc']]= $bar['kodevhc'];
		
		@$databgtvhcbi[$bar['kodevhc']] += $bar['bi'];
		@$databgtvhcsdbi[$bar['kodevhc']]+= $bar['sdbi'];		
		@$databgtvhcthn[$bar['kodevhc']]+= $bar['rupiah'];		
	}
}




#ini untuk ambil volume hasil kerja atau prestasi kerja atau jumlah HA
$str=" select * from ".$dbname.".bgt_budget_detail a where 1=1 ".$wh." and tahunbudget = '".$tahun."' ".$whrb." and (kodebudget like 'SDM%' or kodebudget like 'KONTRAK%') group by kegiatan, kodeorg,kodebudget";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['kodebudget']!='KONTRAK'){
		@$bgtvolthn[$bar['kegiatan']]+=$bar['volume'];
	}
	if($bar['kodebudget']=='KONTRAK'){
		@$bgtvolthnspk[$bar['kegiatan']]['spk']+=$bar['volume'];
	}
}

$tab.="<tr class=rowcontent>";
#$tab.="<td valign=top align=center></td>";
$tab.="<td valign=top align=left>Luas Kebun</td>";
$tab.="<td valign=top align=center>Ha</td>";
$tab.="<td valign=top align=right>" . @number_format($luastotal,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($luastotal,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($luasbgt,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($luasbgt,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($luasbgt,2) . "</td>";
$tab.= "</tr>";

// echo"<pre>";
// print_r($arrkegspk);
// echo"</pre>";

$colspan=7;
if(count($arrkeg)>0){
$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF>";
$tab.="<td valign=top align=left colspan=".$colspan.">Hasil Kerja</td>";
$tab.= "</tr>";

$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#FEE3DA>";
$tab.="<td valign=top align=center>Harian / Kary Sendiri</td>";
$tab.="<td></td><td></td><td></td><td></td><td></td><td></td>";
$tab.="</tr>";
	foreach(@$arrkeg as $kdkeg){
		$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kdkeg."'");
		$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$kdkeg."'");
		
		$tab.="<tr class=rowcontent>";
		#$tab.="<td valign=top align=center></td>";
		$tab.="<td valign=top align=left>" . $nmkeg[$kdkeg] . "</td>";
		$tab.="<td valign=top align=center>" . $nmsat[$kdkeg] . "</td>";

		$tab.="<td valign=top align=right>" . @number_format($hasilkerjabi[$kdkeg],2) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($hasilkerjasdbi[$kdkeg],2) . "</td>";
		
		@$bgtfisikbi[$kdkeg]=($bgtbivol[$kdkeg]/$bgtbivolthn[$kdkeg])*$bgtvolthn[$kdkeg];
		@$bgtfisiksdbi[$kdkeg]=($bgtsdbivol[$kdkeg]/$bgtbivolthn[$kdkeg])*$bgtvolthn[$kdkeg];
		
		$tab.="<td valign=top align=right>" . @number_format($bgtfisikbi[$kdkeg],2) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($bgtfisiksdbi[$kdkeg],2) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($bgtvolthn[$kdkeg],2) . "</td>";
		$tab.= "</tr>";
		
		@$ttlhabi+=$hasilkerjabi[$kdkeg];
		@$ttlhasdbi+=$hasilkerjasdbi[$kdkeg];
		@$ttlbgthabi+=$bgtfisikbi[$kdkeg];
		@$ttlbgthasdbi+=$bgtfisiksdbi[$kdkeg];
		@$ttlbgthathn+=$bgtvolthn[$kdkeg];
	}
}
if(count($arrkegspk)>0){
$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#FEE3DA>";
$tab.="<td valign=top align=center>Kontrak / SPK</td>";
$tab.="<td></td><td></td><td></td><td></td><td></td><td></td>";
$tab.="</tr>";
		
	foreach($arrkegspk as $kdkeg => $arrtpspk){
		foreach($arrtpspk as $tpspk => $arkdkeg){
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kdkeg."'");
			$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$kdkeg."'");
			
			$tab.="<tr class=rowcontent>";
			#$tab.="<td valign=top align=center></td>";
			$tab.="<td valign=top align=left>" . $nmkeg[$kdkeg] . "</td>";
			$tab.="<td valign=top align=center>" .strtoupper($tpspk) . "</td>";

			$tab.="<td valign=top align=right>" . @number_format($hasilkerjabispk[$kdkeg][$tpspk],2) . "</td>";
			$tab.="<td valign=top align=right>" . @number_format($hasilkerjasdbispk[$kdkeg][$tpspk],2) . "</td>";
			
			@$bgtfisikbispk[$kdkeg][$tpspk]=($bgtbivolspk[$kdkeg][$tpspk]/$bgtbivolthnspk[$kdkeg][$tpspk])*$bgtvolthnspk[$kdkeg][$tpspk];
			@$bgtfisiksdbispk[$kdkeg][$tpspk]=($bgtsdbivolspk[$kdkeg][$tpspk]/$bgtbivolthnspk[$kdkeg][$tpspk])*$bgtvolthnspk[$kdkeg][$tpspk];
			
			$tab.="<td valign=top align=right>" . @number_format($bgtvolthnspk[$kdkeg][$tpspk],2) . "</td>";
			$tab.="<td valign=top align=right>" . @number_format($bgtfisiksdbispk[$kdkeg][$tpspk],2) . "</td>";
			$tab.="<td valign=top align=right>" . @number_format($bgtvolthnspk[$kdkeg][$tpspk],2) . "</td>";
			$tab.= "</tr>";
			
			@$ttlhabi+=$hasilkerjabispk[$kdkeg][$tpspk];
			@$ttlhasdbi+=$hasilkerjasdbispk[$kdkeg][$tpspk];
			@$ttlbgthabi+=$bgtfisikbispk[$kdkeg][$tpspk];
			@$ttlbgthasdbi+=$bgtfisiksdbispk[$kdkeg][$tpspk];
			@$ttlbgthathn+=$bgtvolthnspk[$kdkeg][$tpspk];
			
		}
	}
}

$tab.="<tr class=rowcontent  style=font-weight:bold;background-color:#D5F5E3>";
#$tab.="<td valign=top align=center></td>";
$tab.="<td valign=top align=left>Total</td>";
$tab.="<td valign=top align=center>" . $nmsat[$kdkeg] . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlhabi,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlhasdbi,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlbgthabi,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlbgthasdbi,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlbgthathn,2) . "</td>";
$tab.= "</tr>";


$tab.="<tr class=rowcontent>";
#$tab.="<td valign=top align=center></td>";
$tab.="<td valign=top align=left>Rotasi</td>";
$tab.="<td valign=top align=center>Kali</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlhabi/$luastotal,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlhasdbi/$luastotal,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlbgthabi/$luasbgt,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlbgthasdbi/$luasbgt,2) . "</td>";
$tab.="<td valign=top align=right>" . @number_format($ttlbgthathn/$luasbgt,2) . "</td>";
$tab.= "</tr>";



$datahkbi=array();
$datahksdbi=array();

#total hk, tidak bisa digabung diatas karena perlu status karyawan
$str = "select sum(jhk) as jhk,sum(insentif) as insentif,sum(umr) as umr,kodekegiatan,substr(a.tanggal,1,7) as periode,kodetipekaryawan,tipekaryawan  from " . $dbname . ".kebun_kehadiran_vw a where 1=1 ".$wh." ".$whr." and substr(a.tanggal,1,7) between '".$periode1."' and  '".$periode."' and a.notransaksi not like '%/BOR/%'  
group by kodekegiatan, periode";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrdatahk['1']= array('hk'=>'HK','umr'=>'Rp','premi'=>'Rp');
	
	if($bar['periode']==$periode){
		@$datahkbi['1']['hk']+= $bar['jhk'];
		if(substr($klpakun,0,3)!='611'){
			@$datahkbi['1']['premi'] += $bar['insentif'];
		}
		#@$datahkbi[$bar['kodetipekaryawan']]['umr'] += $bar['umr'];
	}
	@$datahksdbi['1']['hk']+= $bar['jhk'];
	if(substr($klpakun,0,3)!='611'){		
		@$datahksdbi['1']['premi'] += $bar['insentif'];
	}
	#@$datahksdbi[$bar['kodetipekaryawan']]['umr'] += $bar['umr'];
}

#ambil dari Jurnal
$str = "select sum(jumlah) as jumlah, periode,kodejurnal      
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('611','621') ".$whj." ".$whrj." and 
periode between '".$periode1."' and  '".$periode."' and a.noreferensi not like '%/BOR/%'  
group by periode,kodejurnal"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrdatahk['1']= array('hk'=>'HK','umr'=>'Rp','premi'=>'Rp');
	$arrdatahk['101']= array('spk'=>'SPK','bor'=>'Bor');
	$arrdatahk['300']= array('hk'=>'HK','umr'=>'Rp','premi'=>'Rp');

	if($bar['kodejurnal']=='M0' or substr($bar['kodejurnal'],0,2)=='CT'){
		if($bar['periode']==$periode){
			@$datahkbi['1']['umr'] += $bar['jumlah'];
		}
		@$datahksdbi['1']['umr'] += $bar['jumlah'];		
	}
	if($bar['kodejurnal']=='PNN01' or $bar['kodejurnal']=='PNN02' or $bar['kodejurnal']=='BM01'){ 
	#digabung dulu nanti dibawah dikurangkan
		if($bar['periode']==$periode){
			@$datahkbi['1']['umr'] += $bar['jumlah'];
		}
		@$datahksdbi['1']['umr'] += $bar['jumlah'];		
	}
	if($bar['kodejurnal']=='PNN02' or $bar['kodejurnal']=='BM01'){
		if($bar['periode']==$periode){
			@$datahkbi['1']['premi'] += $bar['jumlah'];
		}
		@$datahksdbi['1']['premi'] += $bar['jumlah'];		
	}
	if(substr($bar['kodejurnal'],0,3)=='SPK'){
		if($bar['periode']==$periode){
			@$datahkbi['101']['spk'] += $bar['jumlah'];
		}
		@$datahksdbi['101']['spk'] += $bar['jumlah'];		
	}
	if($bar['kodejurnal']=='M' or $bar['kodejurnal']=='NOTAD' or $bar['kodejurnal']=='NOTAK' 
	or $bar['kodejurnal']=='HPP' or $bar['kodejurnal']=='TGH01' or $bar['kodejurnal']=='HPP'  
	or $bar['kodejurnal']=='BK' or $bar['kodejurnal']=='KK' or substr($bar['kodejurnal'],0,3)=='PRJ'  
	or $bar['kodejurnal']=='POT'){
		if($bar['periode']==$periode){
			@$datahkbi['300']['umr'] += $bar['jumlah'];
		}
		@$datahksdbi['300']['umr'] += $bar['jumlah'];		
	}
}

#Khusus borongan sendiri
$str = "select sum(jumlah) as jumlah, periode,kodejurnal      
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('611','621') ".$whj." ".$whrj." and 
periode between '".$periode1."' and  '".$periode."' and a.noreferensi like '%/BOR/%'  
group by periode,kodejurnal"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrdatahk['101']= array('spk'=>'SPK','bor'=>'Bor');
	if($bar['kodejurnal']=='M0'){
		if($bar['periode']==$periode){
			@$datahkbi['101']['bor'] += $bar['jumlah'];
		}
		@$datahksdbi['101']['bor'] += $bar['jumlah'];		
	}
}

// echo"<pre>";
// print_r($datahkbi);
// echo"</pre>";

#HK
$arrurai=array('hk'=>'HK','umr'=>'Upah','premi'=>'Premi','spk'=>'SPK','bor'=>'Borongan Sendiri');

$nmkdkary['1']='KBL,KHT,KHL,CATU';
$nmkdkary['100']='Premi Budget';
$nmkdkary['101']='Kontrak';
$nmkdkary['200']='Supervisi'; #sudah dipisahkan di kolom sendiri
$nmkdkary['300']='Biaya Lainnya'; #<i>(Kode Jurnal : M,NOTAD,NOTAK,HPP,TGH01 dll)</i>';
if(count($arrdatahk)>0){
$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF>";
$tab.="<td valign=top align=left colspan=".$colspan.">Tenaga Kerja</td>";
$tab.= "</tr>";
	foreach($arrdatahk as $kdhk => $valtipe){
		if(@$lstdatahk[$kdhk]!='200'){ #200 isinya harusnya kosong, kalau tidak kosong berarti salah bangsat
			$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#CECCCE>";
			$tab.="<td valign=top align=left colspan=".$colspan.">".$nmkdkary[$kdhk]."</td>";
			$tab.= "</tr>";
			foreach($valtipe as $tipe => $sat){
				$tab.="<tr class=rowcontent>";
				#$tab.="<td valign=top align=left>" . $tipe . "</td>";
				$tab.="<td valign=top align=left>" . $arrurai[$tipe] . "</td>";
				$tab.="<td valign=top align=center>".$sat."</td>";
				if($tipe=='umr'){
					@$datahkbi[$kdhk]['umr']=$datahkbi[$kdhk]['umr']-$datahkbi[$kdhk]['premi'];
					@$datahksdbi[$kdhk]['umr']=$datahksdbi[$kdhk]['umr']-$datahksdbi[$kdhk]['premi'];
				}
				$tab.="<td valign=top align=right>" . @number_format($datahkbi[$kdhk][$tipe],2) . "</td>";
				$tab.="<td valign=top align=right>" . @number_format($datahksdbi[$kdhk][$tipe],2) . "</td>";				
				$tab.="<td valign=top align=right>" . @number_format($bgthkbi[$kdhk][$tipe],2) . "</td>";
				$tab.="<td valign=top align=right>" . @number_format($bgthksdbi[$kdhk][$tipe],2) . "</td>";
				$tab.="<td valign=top align=right>" . @number_format($bgthkthn[$kdhk][$tipe],2) . "</td>";
				$tab.= "</tr>";
				
				if($tipe=='premi' or $tipe=='spk' or $tipe=='bor'){
					@$ttlpremibi+=$datahkbi[$kdhk][$tipe];
					@$ttlpremisdbi+=$datahksdbi[$kdhk][$tipe];
					
					@$ttlbgtpremibi+=$bgthkbi[$kdhk][$tipe];
					@$ttlbgtpremisdbi+=$bgthksdbi[$kdhk][$tipe];
					@$ttlbgtpremithn+=$bgthkthn[$kdhk][$tipe];
				}elseif($tipe=='umr'){
					@$ttlumrbi+=$datahkbi[$kdhk][$tipe];
					@$ttlumrsdbi+=$datahksdbi[$kdhk][$tipe];
					
					@$ttlbgtumrbi+=$bgthkbi[$kdhk][$tipe];
					@$ttlbgtumrsdbi+=$bgthksdbi[$kdhk][$tipe];
					@$ttlbgtumrthn+=$bgthkthn[$kdhk][$tipe];
				}if($tipe=='hk'){
					@$ttlhkbi+=$datahkbi[$kdhk][$tipe];
					@$ttlhksdbi+=$datahksdbi[$kdhk][$tipe];
					@$ttlbgthkbi+=$bgthkbi[$kdhk][$tipe];
					@$ttlbgthksdbi+=$bgthksdbi[$kdhk][$tipe];
					@$ttlbgthkthn+=$bgthkthn[$kdhk][$tipe];
				}
			}
		}
	}
	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#CECCCE>";
	$tab.="<td valign=top align=left colspan=".$colspan.">TOTAL</td>";
	$tab.= "</tr>";
		
	$tab.="<tr class=rowcontent>";
	#$tab.="<td valign=top align=left></td>";
	$tab.="<td valign=top align=left>HK</td>";
	$tab.="<td valign=top align=center>HK</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlhkbi,2)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlhksdbi,2)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgthkbi,2)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgthksdbi,2)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgthkthn,2)."</td>";
	$tab.= "</tr>";

	$tab.="<tr class=rowcontent>";
	#$tab.="<td valign=top align=left></td>";
	$tab.="<td valign=top align=left></td>";
	$tab.="<td valign=top align=center>HK / Ha</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlhkbi/$ttlhabi,2)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlhksdbi/$ttlhasdbi,2)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgthkbi/$ttlbgthabi,2)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgthksdbi/$ttlbgthasdbi,2)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgthkthn/$ttlbgthathn,2)."</td>";
	$tab.= "</tr>";


	$tab.="<tr class=rowcontent>";
	#$tab.="<td valign=top align=left></td>";
	$tab.="<td valign=top align=left>Upah</td>";
	$tab.="<td valign=top align=center>Rp</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlumrbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlumrsdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtumrbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtumrsdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtumrthn)."</td>";
	$tab.= "</tr>";

	$tab.="<tr class=rowcontent>";
	#$tab.="<td valign=top align=left></td>";
	$tab.="<td valign=top align=left>Premi</td>";
	$tab.="<td valign=top align=center>Rp</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlpremibi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlpremisdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtpremibi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtpremisdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtpremithn)."</td>";
	$tab.= "</tr>";

		
	
	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#D5F5E3>";
	#$tab.="<td valign=top align=left></td>";
	$tab.="<td valign=top align=left>Upah + Premi</td>";
	$tab.="<td valign=top align=center>Rp</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlumrbi+$ttlpremibi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlumrsdbi+$ttlpremisdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtumrbi+$ttlbgtpremibi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtumrsdbi+$ttlbgtpremisdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtumrthn+$ttlbgtpremithn)."</td>";
	$tab.= "</tr>";
	
} #tutup if hk

#material
$whr="";
if($kegiatan!=''){
	$whr=" and b.kodekegiatan like '".$kegiatan."%'";
}else if($akun!=''){
	$whr=" and b.kodekegiatan like '".$akun."%'";
}elseif($klpakun!=''){
	$whr=" and b.kodekegiatan like '".$klpakun."%'";
}else{
	exit("Error : Kelompok Biaya Wajib diisi !!!");
}
$str = "select sum(kwantitas) as kwantitas,hargasatuan,b.kodekegiatan,substr(c.tanggal,1,7) as periode,kodebarang 
from " . $dbname . ".kebun_pakaimaterial a 
left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi and a.kodeorg=b.kodeorg 
left join " . $dbname . ".kebun_aktifitas c on a.notransaksi=c.notransaksi 
where 1=1 ".$wh." ".$whr." and substr(c.tanggal,1,7) between '".$periode1."' and  '".$periode."%' group by b.kodekegiatan, periode, kodebarang";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	#@$arrdatamat[$bar['kodebarang']]= array('kwantitas','harga','total');
	@$arrdatamat[$bar['kodebarang']]= array('kwantitas');
	
	if($bar['periode']==$periode){
		@$datamatbi[$bar['kodebarang']]['kwantitas'] += $bar['kwantitas'];
		@$datamatbi[$bar['kodebarang']]['harga'] = $bar['hargasatuan'];
	}
	@$datamatsdbi[$bar['kodebarang']]['kwantitas']+= $bar['kwantitas'];
	@$datamatsdbi[$bar['kodebarang']]['harga']= $bar['hargasatuan'];
}

$whr="";
if($kegiatan!=''){
	$whr=" and kodekegiatan like '".$kegiatan."%' and noakun like '".$akun."%'";
}else if($akun!=''){
	$whr=" and noakun like '".$akun."%' and noakun like '".$klpakun."%'";
}elseif($klpakun!=''){
	$whr=" and noakun like '".$klpakun."%'";
}else{
	exit("Error : Kelompok Biaya Wajib diisi !!!");
}
$str = "select sum(jumlah) as jumlah,kodekegiatan, periode   
from " . $dbname . ".keu_jurnaldt_vw a
where 1=1 ".$wh." ".$whr." and periode between '".$periode1."' and  '".$periode."%' and kodejurnal like 'INV%' group by kodekegiatan, periode, kodevhc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['periode']==$periode){
		@$ttlmatbi+= $bar['jumlah'];
	}
	@$ttlmatsdbi+= $bar['jumlah'];
}

#Mat
if(count(@$arrdatamat)>0){
$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF>";
$tab.="<td valign=top align=left colspan=".$colspan.">Material / Bahan</td>";
$tab.= "</tr>";
	foreach($arrdatamat as $kdbarang => $valtipe){
		foreach($valtipe as $tipe){
			$nmsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kdbarang."'");
			$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbarang."'");
			$tab.="<tr class=rowcontent>";
			#$tab.="<td valign=top align=left>" . $tipe . "</td>";
			if($tipe=='kwantitas'){
				$tab.="<td valign=top align=left>" . $nmbrg[$kdbarang] . "</td>";
				$tab.="<td valign=top align=center>".$nmsat[$kdbarang]."</td>";
			}else{
				$tab.="<td valign=top align=left>" . strtoupper($tipe) . "</td>";
				$tab.="<td valign=top align=center>Rp</td>";
			}
			
			$tab.="<td valign=top align=right>" . @number_format($datamatbi[$kdbarang][$tipe],2) . "</td>";
			$tab.="<td valign=top align=right>" . @number_format($datamatsdbi[$kdbarang][$tipe],2) . "</td>";
			$tab.="<td valign=top align=right>" . @number_format($databgtmatbi[$kdbarang][$tipe],2) . "</td>";
			$tab.="<td valign=top align=right>" . @number_format($databgtmatsdbi[$kdbarang][$tipe],2) . "</td>";
			$tab.="<td valign=top align=right>" . @number_format($databgtmatthn[$kdbarang][$tipe],2) . "</td>";
			
			$tab.= "</tr>";
		}
	}

	$tab.="<tr class=rowcontent  style=font-weight:bold;background-color:#D5F5E3>";
	#$tab.="<td valign=top align=left></td>";
	$tab.="<td valign=top align=left>Total Biaya Material</td>";
	$tab.="<td valign=top align=center>Rp</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlmatbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlmatsdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtmatbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtmatsdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtmatthn)."</td>";
	$tab.= "</tr>";
} #tutup if mat


#kendaraan
$whr="";
if($kegiatan!=''){
	$whr=" and kodekegiatan like '".$kegiatan."%' and noakun like '".$akun."%'";
}else if($akun!=''){
	$whr=" and noakun like '".$akun."%' and noakun like '".$klpakun."%'";
}elseif($klpakun!=''){
	$whr=" and noakun like '".$klpakun."%'";
}else{
	exit("Error : Kelompok Biaya Wajib diisi !!!");
}
$str = "select sum(jumlah) as jumlah,kodekegiatan, periode,kodevhc  
from " . $dbname . ".keu_jurnaldt_vw a
where 1=1 ".$wh." ".$whr." and periode between '".$periode1."' and  '".$periode."%' and kodejurnal like 'VHC%' group by kodekegiatan, periode, kodevhc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$arrdatavhc[$bar['kodevhc']]= $bar['kodevhc'];
	
	if($bar['periode']==$periode){
		@$datavhcbi[$bar['kodevhc']] += $bar['jumlah'];
	}
	@$datavhcsdbi[$bar['kodevhc']]+= $bar['jumlah'];
}

if(count(@$arrdatavhc)>0){
$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF>";
$tab.="<td valign=top align=left colspan=".$colspan.">Kendaraan</td>";
$tab.= "</tr>";

	foreach($arrdatavhc as $kdvhc => $kodevhc){
		$nmnopol = makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$kdvhc."'");
		if($nmnopol[$kdvhc]!=''){
			$nopol=" - ".$nmnopol[$kdvhc];
		}else{$nopol="";}
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td valign=top align=left>" . $kdvhc . "".$nopol."</td>";
		$tab.="<td valign=top align=center>Rp</td>";
		$tab.="<td valign=top align=right>" . @number_format($datavhcbi[$kdvhc]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($datavhcsdbi[$kdvhc]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($databgtvhcbi[$kdvhc]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($databgtvhcsdbi[$kdvhc]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($databgtvhcthn[$kdvhc]) . "</td>";
		$tab.= "</tr>";
		
		@$ttlvhcbi+=$datavhcbi[$kdvhc];
		@$ttlvhcsdbi+=$datavhcsdbi[$kdvhc];
		@$ttlbgtvhcbi+=$databgtvhcbi[$kdvhc];
		@$ttlbgtvhcsdbi+=$databgtvhcsdbi[$kdvhc];
		@$ttlbgtvhcthn+=$databgtvhcthn[$kdvhc];
	}

	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#D5F5E3>";
	$tab.="<td valign=top align=left>Total Biaya Kendaraan</td>";
	$tab.="<td valign=top align=center>Rp</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlvhcbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlvhcsdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtvhcbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtvhcsdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtvhcthn)."</td>";
	$tab.= "</tr>";
} #tutup if kend

#supervisi
$whr="";
if($kegiatan!=''){
	$whr=" and kodekegiatan like '".$kegiatan."%' and noakun like '".$akun."%'";
}else if($akun!=''){
	$whr=" and noakun like '".$akun."%' and noakun like '".$klpakun."%'";
}elseif($klpakun!=''){
	$whr=" and noakun like '".$klpakun."%'";
}else{
	exit("Error : Kelompok Biaya Wajib diisi !!!");
}

$str = "select sum(jumlah) as jumlah,kodekegiatan,noakun, periode   
from " . $dbname . ".keu_jurnaldt_vw a
where 1=1 ".$wh." ".$whr." and periode between '".$periode1."' and  '".$periode."%' and kodejurnal in ('KBNL0','KBNL1','KBNL2','KBNL3') group by kodekegiatan, periode";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$arrdataspv[$bar['kodekegiatan']]= $bar['kodekegiatan'];
	@$nakun[$bar['kodekegiatan']]= $bar['noakun'];
	
	if($bar['periode']==$periode){
		@$dataspvbi[$bar['kodekegiatan']] += $bar['jumlah'];
	}
	@$dataspvsdbi[$bar['kodekegiatan']]+= $bar['jumlah'];
}
if(count(@$arrdataspv)>0){
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
array_multisort($arrdataspv,SORT_ASC);
$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF>";
$tab.="<td valign=top align=left colspan=".$colspan.">Supervisi</td>";
$tab.= "</tr>";

	foreach($arrdataspv as $kodekeg => $kdkeg){
		$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kdkeg."'");
		
		$tab.="<tr class=rowcontent>";
		if($nmkeg[$kdkeg]!=''){
			$tab.="<td valign=top align=left>".$kdkeg." - " . $nmkeg[$kdkeg] . "</td>";
		}else{
			$tab.="<td valign=top align=left>".$nakun[$kdkeg]." - " . $nmakun[$nakun[$kdkeg]] . "</td>";
		}
		$tab.="<td valign=top align=center>Rp</td>";
		$tab.="<td valign=top align=right>" . @number_format($dataspvbi[$kdkeg]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($dataspvsdbi[$kdkeg]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($bgtspvbi[$kdkeg]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($bgtspvsbi[$kdkeg]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($bgtspvthn[$kdkeg]) . "</td>";
		$tab.= "</tr>";
		
		@$ttlspvbi+=$dataspvbi[$kdkeg];
		@$ttlspvsdbi+=$dataspvsdbi[$kdkeg];
		@$ttlbgtspvbi+=$bgtspvbi[$kdkeg];
		@$ttlbgtspvsdbi+=$bgtspvsbi[$kdkeg];
		@$ttlbgtspvthn+=$bgtspvthn[$kdkeg];
	}

	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#D5F5E3>";
	$tab.="<td valign=top align=left>Total Biaya Supervisi</td>";
	$tab.="<td valign=top align=center>Rp</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlspvbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlspvsdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtspvbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtspvsdbi)."</td>";
	$tab.="<td valign=top align=right>".@number_format($ttlbgtspvthn)."</td>";
	$tab.= "</tr>";
} #tutup if supervisi

$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF>";
$tab.="<td valign=top align=left colspan=".$colspan.">Grand Total</td>";
$tab.= "</tr>";


@$gtbi=$ttlumrbi+$ttlpremibi+$ttlmatbi+$ttlvhcbi+$ttlspvbi;
@$gtsdbi=$ttlumrsdbi+$ttlpremisdbi+$ttlmatsdbi+$ttlvhcsdbi+$ttlspvsdbi;
@$gtbgtbi=$ttlbgtumrbi+$ttlbgtpremibi+$ttlbgtmatbi+$ttlbgtvhcbi+$ttlbgtspvbi;
@$gtbgtsdbi=$ttlbgtumrsdbi+$ttlbgtpremisdbi+$ttlbgtmatsdbi+$ttlbgtvhcsdbi+$ttlbgtspvsdbi;
@$gtbgtthn=$ttlbgtumrthn+$ttlbgtpremithn+$ttlbgtmatthn+$ttlbgtvhcthn+$ttlbgtspvthn;

$tab.="<tr class=rowcontent>";
$tab.="<td valign=top align=left><b>Grand Total</b></td>";
$tab.="<td valign=top align=center><b>Rp</b></td>";
$tab.="<td valign=top align=right><b>".@number_format($gtbi)."</b></td>";
$tab.="<td valign=top align=right><b>".@number_format($gtsdbi)."</b></td>";
$tab.="<td valign=top align=right><b>".@number_format($gtbgtbi)."</b></td>";
$tab.="<td valign=top align=right><b>".@number_format($gtbgtsdbi)."</b></td>";
$tab.="<td valign=top align=right><b>".@number_format($gtbgtthn)."</b></td>";
$tab.= "</tr>";

$tab.="<tr class=rowcontent>";
$tab.="<td valign=top align=left></td>";
$tab.="<td valign=top align=center>Rp / Ha</td>";
$tab.="<td valign=top align=right>".@number_format($gtbi/$luastotal,2)."</td>";
$tab.="<td valign=top align=right>".@number_format($gtsdbi/$luastotal,2)."</td>";
$tab.="<td valign=top align=right>".@number_format($gtbgtbi/$luasbgt,2)."</td>";
$tab.="<td valign=top align=right>".@number_format($gtbgtsdbi/$luasbgt,2)."</td>";
$tab.="<td valign=top align=right>".@number_format($gtbgtthn/$luasbgt,2)."</td>";
$tab.= "</tr>";

$tab.="</tbody></table>";

switch ($proses) {
######PREVIEW
    case 'preview':
        echo $tab;
        break;

######EXCEL	
    case 'excel':
        $tempnm = explode("/",$_SERVER['PHP_SELF']);
		$nop_ = substr($tempnm[2],0,strripos($tempnm[2],'.'));
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
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