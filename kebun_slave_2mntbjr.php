<?php
//@uhr
require_once('master_validation.php');
require_once('lib/zLib.php');

$prd = checkPostGet('prd','');
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');

if($kdorg=='')
{
    echo"Warning: Unit tidak boleh kosong"; 
    exit;
}
else if ($prd=='')
{
	echo "Warning : Periode tidak boleh kosong";
	exit;
}

$expbln=  explode('-', $prd);
$tahun=$expbln[0];
$bln=$expbln[1];

$blawal=$tahun."-01";

$rangebulan = month_inbetween($blawal, $prd);

$stream="";
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
	@$hide="hidden";
}

$stream.="
    <thead>
        <tr class=rowheader>
			<th rowspan=3 align=center width=20px >".$_SESSION['lang']['nourut']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['divisi']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['tanam']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['luas']." ".$_SESSION['lang']['ha']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['pokok']."</th>
			<th ".@$hide." colspan=2 align=center>".$_SESSION['lang']['budget']."</th>
			<th ".@$hide." colspan=8 align=center>".$_SESSION['lang']['sensus']."</th>
			<th ".@$hide." colspan=".intval($bln+1)." align=center>".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['panen']."</th>
			<th ".@$hide." colspan=".intval($bln+1)." align=center>".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['kirim']."</th>
			<th ".@$hide." colspan=".intval($bln+1)." align=center>".$_SESSION['lang']['kgwb']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['budget']."</th>
			<th colspan=4 align=center>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['sensus']."</th>
			<th colspan=".intval($bln+1)." align=center>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['kebun']."<i><font size=1> (".$_SESSION['lang']['kgwb']." / ".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['panen'].")</font></i></th>
			<th colspan=".intval($bln+1)." align=center>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['pabrik']."<i><font size=1> (".$_SESSION['lang']['kgwb']." / ".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['kirim'].")</font></i></th>
			<th width=60px rowspan=3 align=center>Restant ".$_SESSION['lang']['bi']." <i><font size=1>( ".numToMonth($bln,'E','short')." ".$tahun." )</font></i></th>
			
		</tr>
		<tr>
			<th ".@$hide." rowspan=2 align=center>".$_SESSION['lang']['jjg']."</th>
			<th ".@$hide." rowspan=2 align=center>".$_SESSION['lang']['kg']."</th>
			<th ".@$hide." colspan=4 align=center>".$_SESSION['lang']['jjg']."</th>
			<th ".@$hide." colspan=4 align=center>".$_SESSION['lang']['kg']."</th>";
	foreach ($rangebulan as $listbulan )
	{
		$stream.="<th ".@$hide." rowspan=2 align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</th>";
	}
		$stream.="<th ".@$hide." rowspan=2 align=center>".$_SESSION['lang']['sbi']."</th>";
			
	foreach ($rangebulan as $listbulan )
	{
		$stream.="<th ".@$hide." rowspan=2 align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</th>";
	}
		$stream.="<th ".@$hide." rowspan=2 align=center>".$_SESSION['lang']['sbi']."</th>";
	foreach ($rangebulan as $listbulan )
	{
		$stream.="<th ".@$hide." rowspan=2 align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</th>";
	}
		$stream.="<th ".@$hide." rowspan=2 align=center>".$_SESSION['lang']['sbi']."</th>";
	
	$stream.="
			<th rowspan=2 align=center>SM I</th>
			<th rowspan=2 align=center>SM II</th>
			<th rowspan=2 align=center>".$_SESSION['lang']['sbi']."</th>
			<th rowspan=2 align=center>".$_SESSION['lang']['setahun']."</th>";
			
	foreach ($rangebulan as $listbulan )
	{
		$stream.="<th rowspan=2 align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</th>";
	}
		$stream.="<th rowspan=2 align=center>".$_SESSION['lang']['sbi']."</th>";
		
		
		
	foreach ($rangebulan as $listbulan )
	{
		$stream.="<th rowspan=2 align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</th>";
	}
		$stream.="<th rowspan=2 align=center>".$_SESSION['lang']['sbi']."</th>";
		
		$stream.="</tr>
		<tr>
			<th ".@$hide." align=center>SM I</th>
			<th ".@$hide." align=center>SM II</th>
			<th ".@$hide." align=center>".$_SESSION['lang']['sbi']."</th>
			<th ".@$hide." align=center>".$_SESSION['lang']['setahun']."</th>
			<th ".@$hide." align=center>SM I</th>
			<th ".@$hide." align=center>SM II</th>
			<th ".@$hide." align=center>".$_SESSION['lang']['sbi']."</th>
			<th ".@$hide." align=center>".$_SESSION['lang']['setahun']."</th>

		</tr>";

$stream.="</thead>
		<tbody>";
		#budget hk
		$str="select distinct a.kodeorg, substr(a.kodeorg,1,6) as divisi, a.kodebudget, a.kegiatan, a.JUMLAH as hk, a.satuanj, b.tahuntanam from 
			 ".$dbname.".bgt_budget a left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg where a.kodebudget in('SDM-PHL', 'SDM-KHT') 
			 and a.kodeorg like '".$kdorg."%' and a.kegiatan = '611010101' and a.tahunbudget = '".$tahun."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$hkbgt[$bar['divisi']][$bar['tahuntanam']]+=$bar['hk'];
		}
		
		#budget jjg
		$str="select substr(a.kodeblok,1,6)as divisi, b.tahuntanam, sum(jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12) as jjg from 
			 ".$dbname.".bgt_produksi_kebun a left join ".$dbname.".setup_blok b on a.kodeblok = b.kodeorg where  
			 a.kodeblok like '".$kdorg."%' and a.tahunbudget = '".$tahun."' group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgbgt[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjg'];
		}
		#budget kg
		$str="select divisi, thntnm as tahuntanam, sum(kgsetahun) as kg from 
			 ".$dbname.".bgt_produksi_kbn_kg_vw where kodeblok like '".$kdorg."%' and tahunbudget = '".$tahun."' group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kgbgt[$bar['divisi']][$bar['tahuntanam']]+=$bar['kg'];
		}
		
		#Sensus SMI
		$str="select kodeorg as divisi, tahuntanam, tahun, bulan, semester, jumlah, kgsensus from ".$dbname.".kebun_rencanapanen_vw where kodeblok like '".$kdorg."%' and tahun = '".$tahun."' and semester = 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgsnssm1[$bar['divisi']][$bar['tahuntanam']]+=$bar['jumlah'];
			@$kgsnssm1[$bar['divisi']][$bar['tahuntanam']]+=$bar['kgsensus'];
		}
		
		#Sensus SMII
		$str="select kodeorg as divisi, tahuntanam, tahun, bulan, semester, jumlah, kgsensus from ".$dbname.".kebun_rencanapanen_vw where kodeblok like '".$kdorg."%' and tahun = '".$tahun."' and semester = 2";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgsnssm2[$bar['divisi']][$bar['tahuntanam']]+=$bar['jumlah'];
			@$kgsnssm2[$bar['divisi']][$bar['tahuntanam']]+=$bar['kgsensus'];
		}

		#Sensus SDBI
		$str="select kodeorg as divisi, tahuntanam, tahun, bulan, semester, jumlah, kgsensus from ".$dbname.".kebun_rencanapanen_vw where kodeblok like '".$kdorg."%' and tahun = '".$tahun."' and bulan between 1 and ".intval($bln)."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgsnssd[$bar['divisi']][$bar['tahuntanam']]+=$bar['jumlah'];
			@$kgsnssd[$bar['divisi']][$bar['tahuntanam']]+=$bar['kgsensus'];
		}
		
		#setup blok
		$str="select substr(kodeorg,1,6) as divisi, tahuntanam, sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from 
			 ".$dbname.".setup_blok_tahunan where kodeorg like '".$kdorg."%' and tahun='".str_replace('-', '', $prd)."'group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlbaris($res);
		if($numrows==0){
			#setup blok
		$str="select substr(kodeorg,1,6) as divisi, tahuntanam, sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from 
			 ".$dbname.".setup_blok where kodeorg like '".$kdorg."%' group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		}
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$luasblok[$bar['divisi']][$bar['tahuntanam']]=$bar['luas'];
			$pokokblok[$bar['divisi']][$bar['tahuntanam']]=$bar['pokok'];
		}	
		#rekappnn
		$str="select sum(luaspanen) as luaspanen, sum(jjgpanen) as jjgpanen, sum(tenagakerja) as hk, sum(kgkebun) as kgkebun, tahuntanam, divisi, substr(tanggal,1,7) as prd from 
			 ".$dbname.".kebun_rekappnn_vw where divisi like '".$kdorg."%' and left(tanggal,7) >= '".$tahun."-01' and left(tanggal,7) <= '".$prd."' group by divisi, tahuntanam, prd";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$luaspnn[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['luaspanen'];
			@$luaspnnsbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['luaspanen'];
			$hkbi[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['hk'];
			@$hksbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['hk'];
			$jjgbi[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['jjgpanen'];
			@$jjgsbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjgpanen'];
			$kgkbnbi[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['kgkebun'];
			@$kgkbnsbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['kgkebun'];
		}
		
		#rekappnn dr awal banget
		$str="select sum(luaspanen) as luaspanen, sum(jjgpanen) as jjgpanen, sum(jjgafkir) as jjgafkir, sum(tenagakerja) as hk, sum(kgkebun) as kgkebun, tahuntanam, divisi from 
			 ".$dbname.".kebun_rekappnn_vw where divisi like '".$kdorg."%' and left(tanggal,7) <= '".$prd."' group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgpnndrawal[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjgpanen'];
			@$jjgafkirdrawal[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjgafkir'];	
		}
		
		#spb
		$str="select divisi, tahuntanam, sum(jjg) as jjg, sum(kgwb) as kgwb, substr(tanggal,1,7) as prd from 
			 ".$dbname.".kebun_spb_vw where divisi like '".$kdorg."%' and left(tanggal,7) >= '".$tahun."-01' and left(tanggal,7) <= '".$prd."' group by divisi, tahuntanam, prd";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgkrm[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjg'];
			@$jjgkrmbi[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['jjg'];
			@$kgwbbi[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['kgwb'];
			@$kgwbsbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['kgwb'];
		}
		#SPB dari awal banget 
		$str="select divisi, tahuntanam, sum(jjg) as jjg, sum(kgwb) as kgwb from 
			 ".$dbname.".kebun_spb_vw where divisi like '".$kdorg."%' and left(tanggal,7) <= '".$prd."' group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$jjgkrmdrawal[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjg'];
		}	

array_multisort($kddivisi,SORT_ASC);
array_multisort($tahuntanam,SORT_ASC);

foreach($kddivisi as $divisi)
{
	foreach($tahuntanam as $thntnm)
	{
		if(@$listtahuntanam[$divisi][$thntnm]!=''){
		$no+=1;	
		$stream.="<tr class=rowcontent>
            <td align=center>".$no."</td>
			<td align=center>".$divisi."</td>
			<td align=center>".$thntnm."</td>
			<td align=right>".@number_format($luasblok[$divisi][$thntnm],2)."</td>	
			<td align=right>".@number_format($pokokblok[$divisi][$thntnm])."</td>
			
			<td ".@$hide." align=right>".@number_format($jjgbgt[$divisi][$thntnm],0)."</td>
			<td ".@$hide." align=right>".@number_format($kgbgt[$divisi][$thntnm],0)."</td>
			
			<td ".@$hide." align=right>".@number_format($jjgsnssm1[$divisi][$thntnm],0)."</td>
			<td ".@$hide." align=right>".@number_format($jjgsnssm2[$divisi][$thntnm],0)."</td>
			<td ".@$hide." align=right>".@number_format($jjgsnssd[$divisi][$thntnm],0)."</td>
			<td ".@$hide." align=right>".@number_format($jjgsnssm1[$divisi][$thntnm]+$jjgsnssm2[$divisi][$thntnm],0)."</td>
			<td ".@$hide." align=right>".@number_format($kgsnssm1[$divisi][$thntnm],0)."</td>
			<td ".@$hide." align=right>".@number_format($kgsnssm2[$divisi][$thntnm],0)."</td>
			<td ".@$hide." align=right>".@number_format($kgsnssd[$divisi][$thntnm],0)."</td>
			<td ".@$hide." align=right>".@number_format($kgsnssm1[$divisi][$thntnm]+$kgsnssm2[$divisi][$thntnm],0)."</td>";
		
		foreach($rangebulan as $lstbln){
			$stream.="<td ".@$hide." align=right >".@number_format($jjgbi[$divisi][$thntnm][$lstbln])."</td>";
		}
			$stream.="<td ".@$hide." align=right>".@number_format($jjgsbi[$divisi][$thntnm],0)."</td>";
		foreach($rangebulan as $lstbln){
			$stream.="<td ".@$hide." align=right >".@number_format($jjgkrmbi[$divisi][$thntnm][$lstbln])."</td>";
		}
			$stream.="<td ".@$hide." align=right>".@number_format($jjgkrm[$divisi][$thntnm],0)."</td>";
		foreach($rangebulan as $lstbln){
			$stream.="<td ".@$hide." align=right >".@number_format($kgwbbi[$divisi][$thntnm][$lstbln])."</td>";
		}
			$stream.="<td ".@$hide." align=right>".@number_format($kgwbsbi[$divisi][$thntnm],0)."</td>";	
		
			$stream.="<td align=right>".@number_format($kgbgt[$divisi][$thntnm]/$jjgbgt[$divisi][$thntnm],2)."</td>"; //BJR BGT
			$stream.="<td align=right>".@number_format($kgsnssm1[$divisi][$thntnm]/$jjgsnssm1[$divisi][$thntnm],2)."</td>";
			$stream.="<td align=right>".@number_format($kgsnssm2[$divisi][$thntnm]/$jjgsnssm2[$divisi][$thntnm],2)."</td>";
			$stream.="<td align=right>".@number_format($kgsnssd[$divisi][$thntnm]/$jjgsnssd[$divisi][$thntnm],2)."</td>";
			$stream.="<td align=right>".@number_format(($kgsnssm1[$divisi][$thntnm]+$kgsnssm2[$divisi][$thntnm])/($jjgsnssm1[$divisi][$thntnm]+$jjgsnssm2[$divisi][$thntnm]),2)."</td>";
		foreach($rangebulan as $lstbln){
			$stream.="<td align=right >".@number_format($kgwbbi[$divisi][$thntnm][$lstbln]/$jjgbi[$divisi][$thntnm][$lstbln],2)."</td>";
		}
			$stream.="<td align=right>".@number_format($kgwbsbi[$divisi][$thntnm]/$jjgsbi[$divisi][$thntnm],2)."</td>";
		foreach($rangebulan as $lstbln){
			$stream.="<td align=right >".@number_format($kgwbbi[$divisi][$thntnm][$lstbln]/$jjgkrmbi[$divisi][$thntnm][$lstbln],2)."</td>";
		}
			$stream.="<td align=right>".@number_format($kgwbsbi[$divisi][$thntnm]/$jjgkrm[$divisi][$thntnm],2)."</td>";
			$stream.="<td align=right>".@number_format($jjgpnndrawal[$divisi][$thntnm]-($jjgafkirdrawal[$divisi][$thntnm]+$jjgkrmdrawal[$divisi][$thntnm]))."</td>";
			
			

			
		
		@$ttlluas[$divisi]+=$luasblok[$divisi][$thntnm];
		@$ttlpkk[$divisi]+=$pokokblok[$divisi][$thntnm];
		//Total Budget
		@$ttljjgbgt[$divisi]+=$jjgbgt[$divisi][$thntnm];
		@$ttlkgbgt[$divisi]+=$kgbgt[$divisi][$thntnm];
		//Total Sensus
		@$ttljjgsnssm1[$divisi]+=$jjgsnssm1[$divisi][$thntnm];
		@$ttljjgsnssm2[$divisi]+=$jjgsnssm1[$divisi][$thntnm];
		@$ttljjgsnssd[$divisi]+=$jjgsnssd[$divisi][$thntnm];
		@$ttljjgthn[$divisi]+=$jjgsnssm1[$divisi][$thntnm]+$jjgsnssm2[$divisi][$thntnm];
		@$ttlkgsnssm1[$divisi]+=$kgsnssm1[$divisi][$thntnm];
		@$ttlkgsnssm2[$divisi]+=$kgsnssm2[$divisi][$thntnm];
		@$ttlkgsnssd[$divisi]+=$kgsnssd[$divisi][$thntnm];
		@$ttlkgsnsthn[$divisi]+=$kgsnssm1[$divisi][$thntnm]+$kgsnssm2[$divisi][$thntnm];
		//Total Jjg Panen Bi
		foreach($rangebulan as $lstbln){
			@$ttljjgbi[$divisi][$lstbln]+=$jjgbi[$divisi][$thntnm][$lstbln];
		}
		//Total jjg Panen SDBI
		@$ttljjgsbi[$divisi]+=$jjgsbi[$divisi][$thntnm];
		//Total Jjg kirim Bi
		foreach($rangebulan as $lstbln){
			@$ttljjgkrmbi[$divisi][$lstbln]+=$jjgkrmbi[$divisi][$thntnm][$lstbln];
		}
		//Total jjg kirim SDBI
		@$ttljjgkrmsbi[$divisi]+=$jjgkrm[$divisi][$thntnm];
		//Total Kg kirim Bi
		foreach($rangebulan as $lstbln){
			@$ttlkgbi[$divisi][$lstbln]+=$kgwbbi[$divisi][$thntnm][$lstbln];
		}
		//Total Kg kirim SDBI
		@$ttlkgsbi[$divisi]+=$kgwbsbi[$divisi][$thntnm];	
		@$ttlafkir[$divisi]+=$jjgpnndrawal[$divisi][$thntnm]-($jjgafkirdrawal[$divisi][$thntnm]+$jjgkrmdrawal[$divisi][$thntnm]);
		
		}
	}


$stream.="<tr bgcolor=#00BFFF  style='color:#000000'>
		<td align=left colspan=3 >TOTAL ".$divisi."</td>
		<td align=right >".@number_format($ttlluas[$divisi],2)."</td>
		<td align=right >".@number_format($ttlpkk[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttljjgbgt[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttlkgbgt[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttljjgsnssm1[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttljjgsnssm2[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttljjgsnssd[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttljjgthn[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttlkgsnssm1[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttlkgsnssm2[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttlkgsnssd[$divisi])."</td>
		<td ".@$hide." align=right >".@number_format($ttlkgsnsthn[$divisi])."</td>
		";
	foreach($rangebulan as $lstbln){
		$stream.="<td ".@$hide." align=right >".@number_format($ttljjgbi[$divisi][$lstbln])."</td>";
	}
		$stream.="<td ".@$hide." align=right >".@number_format($ttljjgsbi[$divisi])."</td>";
	foreach($rangebulan as $lstbln){
		$stream.="<td ".@$hide." align=right >".@number_format($ttljjgkrmbi[$divisi][$lstbln])."</td>";
	}
		$stream.="<td ".@$hide." align=right >".@number_format($ttljjgkrmsbi[$divisi])."</td>";
	foreach($rangebulan as $lstbln){
		$stream.="<td ".@$hide." align=right >".@number_format($ttlkgbi[$divisi][$lstbln])."</td>";
	}
		$stream.="<td ".@$hide." align=right >".@number_format($ttlkgsbi[$divisi])."</td>";
		
		$stream.="<td align=right >".@number_format($ttlkgbgt[$divisi]/$ttljjgbgt[$divisi],2)."</td>"; //TTL BJR BGT
		$stream.="<td align=right >".@number_format($ttlkgsnssm1[$divisi]/$ttljjgsnssm1[$divisi],2)."</td>"; 
		$stream.="<td align=right >".@number_format($ttlkgsnssm2[$divisi]/$ttljjgsnssm2[$divisi],2)."</td>"; 
		$stream.="<td align=right >".@number_format($ttlkgsnssd[$divisi]/$ttljjgsnssd[$divisi],2)."</td>"; 
		$stream.="<td align=right >".@number_format($ttlkgsnsthn[$divisi]/$ttljjgthn[$divisi],2)."</td>"; 
	foreach($rangebulan as $lstbln){
		$stream.="<td align=right >".@number_format($ttlkgbi[$divisi][$lstbln]/$ttljjgbi[$divisi][$lstbln],2)."</td>";
	}
		$stream.="<td align=right >".@number_format($ttlkgsbi[$divisi]/$ttljjgsbi[$divisi],2)."</td>";
	foreach($rangebulan as $lstbln){
		$stream.="<td align=right >".@number_format($ttlkgbi[$divisi][$lstbln]/$ttljjgkrmbi[$divisi][$lstbln],2)."</td>";
	}
		$stream.="<td align=right >".@number_format($ttlkgsbi[$divisi]/$ttljjgkrmsbi[$divisi],2)."</td>";
		$stream.="<td align=right >".@number_format($ttlafkir[$divisi])."</td>";
	
	
		
	@$gtluas+=$ttlluas[$divisi];
	@$gtpkk+=$ttlpkk[$divisi];
	//Gt Budget
	@$gtjjgbgt+=$ttljjgbgt[$divisi];
	@$gtkgbgt+=$ttlkgbgt[$divisi];
	//Gt Sensus
	@$gtjjgsnssm1+=$ttljjgsnssm1[$divisi];
	@$gtjjgsnssm2+=$ttljjgsnssm1[$divisi];
	@$gtjjgsnssd+=$ttljjgsnssd[$divisi];
	@$gtjjgsnsthn+=$ttljjgthn[$divisi];
	
	@$gtkgsnssm1+=$ttlkgsnssm1[$divisi];
	@$gtkgsnssm2+=$ttlkgsnssm2[$divisi];
	@$gtkgsnssd+=$ttlkgsnssd[$divisi];
	@$gtkgsnsthn+=$ttlkgsnsthn[$divisi];
	//Total Jjg Panen Bi
	foreach($rangebulan as $lstbln){
		@$gtljjgbi[$lstbln]+=$ttljjgbi[$divisi][$lstbln];
	}
	//Total jjg Panen SDBI
	@$gtljjgsbi+=$ttljjgsbi[$divisi];
	//Total Jjg Kirim Bi
	foreach($rangebulan as $lstbln){
		@$gtljjgkrmbi[$lstbln]+=$ttljjgkrmbi[$divisi][$lstbln];
	}
	//Total jjg Kirim SDBI
	@$gtljjgkrmsbi+=$ttljjgkrmsbi[$divisi];
	//Total Kg Kirim Bi
	foreach($rangebulan as $lstbln){
		@$gtlkgbi[$lstbln]+=$ttlkgbi[$divisi][$lstbln];
	}
	//Total Kg Kirim SDBI
	@$gtlkgsbi+=$ttlkgsbi[$divisi];
	
	@$gtafkir+=$ttlafkir[$divisi];

	

}
$stream.="<tr bgcolor=#1E90FF   style='color:#000000'>
		<td align=left colspan=3 >GRAND TOTAL</td>
		<td align=right >".@number_format($gtluas,2)."</td>
		<td align=right >".@number_format($gtpkk,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtjjgbgt,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtkgbgt,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtjjgsnssm1,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtjjgsnssm2,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtjjgsnssd,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtjjgsnsthn,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtkgsnssm1,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtkgsnssm2,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtkgsnssd,0)."</td>
		<td ".@$hide." align=right >".@number_format($gtkgsnsthn,0)."</td>			
		";
		foreach($rangebulan as $lstbln){
			$stream.="<td ".@$hide." align=right >".@number_format($gtljjgbi[$lstbln])."</td>";
		}
			$stream.="<td ".@$hide." align=right >".@number_format($gtljjgsbi)."</td>";
		foreach($rangebulan as $lstbln){
			$stream.="<td ".@$hide." align=right >".@number_format($gtljjgkrmbi[$lstbln])."</td>";
		}
			$stream.="<td ".@$hide." align=right >".@number_format($gtljjgkrmsbi)."</td>";
		foreach($rangebulan as $lstbln){
			$stream.="<td ".@$hide." align=right >".@number_format($gtlkgbi[$lstbln])."</td>";
		}
			$stream.="<td ".@$hide." align=right >".@number_format($gtlkgsbi)."</td>";
			$stream.="<td align=right >".@number_format($gtkgbgt/$gtjjgbgt,2)."</td>"; //GT BJR BGT
			$stream.="<td align=right >".@number_format($gtkgsnssm1/$gtjjgsnssm1,2)."</td>";
			$stream.="<td align=right >".@number_format($gtkgsnssm2/$gtjjgsnssm2,2)."</td>";
			$stream.="<td align=right >".@number_format($gtkgsnssd/$gtjjgsnssd,2)."</td>";
			$stream.="<td align=right >".@number_format($gtkgsnsthn/$gtjjgsnsthn,2)."</td>";
		foreach($rangebulan as $lstbln){
			$stream.="<td align=right >".@number_format($gtlkgbi[$lstbln]/$gtljjgbi[$lstbln],2)."</td>";
		}
			$stream.="<td align=right >".@number_format($gtlkgsbi/$gtljjgsbi,2)."</td>";
		foreach($rangebulan as $lstbln){
			$stream.="<td align=right >".@number_format($gtlkgbi[$lstbln]/$gtljjgkrmbi[$lstbln],2)."</td>";
		}
			$stream.="<td align=right >".@number_format($gtlkgsbi/$gtljjgkrmsbi,2)."</td>";
			$stream.="<td align=right >".@number_format($gtafkir)."</td>";
	

$stream.="
 </tbody>";

		
switch ($proses) {
    case 'preview':
        echo $stream;
        break;

    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "Monitoring BJR Unit ". $kdorg;
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