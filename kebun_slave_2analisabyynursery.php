<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses   = checkPostGet('proses', '');
$kdorg    = checkPostGet('kdorg', '');
$pt       = checkPostGet('pt', '');
$tt       = checkPostGet('tt', '');
$ip       = checkPostGet('ip', '');
$divisi   = checkPostGet('divisi', '');
$prd      = checkPostGet('prd', '');
$tipe      = checkPostGet('tipe', '');

$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $prd;
if($pt==''){exit("warning : Kode PT harus di pilih.");}

$where='';$where2='';$where_spb='';
if($pt!=''){
	$where=" and substr(a.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where2=" and substr(a.kodeblok,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where_spb=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
}
if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
}

$wh="";$wh2="";$whB="";$wh_spb="";$wh_bgt=$wh_bgtrp='';
if($divisi!=''){
	$wh.=" and a.kodeblok like '".$divisi."%'";
	$whB.=" and a.kodeblok like '".$divisi."%'";
	$wh2.=" and a.kodeorg like '".$divisi."%'";
	$wh_spb.=" and b.blok like '".$divisi."%'";
	$wh_bgt.=" and a.divisi like '".$divisi."%'";
	$wh_bgtrp.=" and a.kodeorg like '".$divisi."%'";
}
if($tt!=''){
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$wh2.=" and a.tahuntanam='".$tt."'";
	$whB.=" and a.thntnm='".$tt."'";
	$wh_spb.=" and b.blok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
}
if($ip!=''){
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
	$wh2.=" and a.intiplasma='".$ip."'";
	$whB.=" and a.intiplasma='".$ip."'";
	$wh_spb.=" and b.blok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
}

#=============== mari kita mulai dari sini ===============#
$whereakun = " and (substr(noakun,1,3) in ('128'))";

$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');

$arrakun=array();
$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun.""; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrkode=array('pml'=>'pml');
	
	if(strlen($bar['noakun'])=='5'){
		$arrakun[$bar['noakun']] = $bar['noakun'];
		$listakun['pml'][$bar['noakun']] = $bar['noakun'];
	}
	if(strlen($bar['noakun'])=='7'){
		$arrakun7[$bar['noakun']] = $bar['noakun'];
		$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
	}
}

if($tipe=='keg'){
	$arrakun7=array();
	$cdarrakun=array();
	$str = "select * from " . $dbname . ".setup_kegiatan  where 1=1 ".$whereakun." and namakegiatan not like '%NON AKTIF%' order by kodekegiatan"; 
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrakun7[$bar['kodekegiatan']] = $bar['kodekegiatan'];
		$cdarrakun['pml'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
	}
	
	$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun." and namaakun not like '%NON AKTIF%'"; 
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if(substr($bar['noakun'],0,1)=='7'){
			if(strlen($bar['noakun'])=='7'){
				$arrakun7[$bar['noakun']] = $bar['noakun'];
				$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
			}
		}
	}
}


#ambil luas realisasi
$str = "select * from " . $dbname . ".setup_blok_tahunan a  where 1=1 ".$wh2." ".$where." and tahun='".$tahun.$bulan."'"; 
if(count(fetchdata($str))==0){
	$str = "select * from " . $dbname . ".setup_blok a  where 1=1 ".$wh2." ".$where.""; 
}
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$luasreal+=$bar['luasareaproduktif'];
}

#ambil luas bgt
$str = "select sum(hathnini) as hathnini from " . $dbname . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahun."'"; 
$bar = fetchdata($str);
$luasbudget=$bar[0]['hathnini'];

#ambil prd real
$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode from " . $dbname . ".kebun_spbdt b
left join " . $dbname . ".kebun_spbht a on a.nospb=b.nospb
where 1=1 ".$wh_spb." ".$where_spb." and substr(a.tanggal,1,7) between '".$periode1."' and  '".$periode2."' group by periode"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['periode']==$periode2){
		@$prdrealbi += $bar['kgwb'];
	}
	@$prdrealsdbi += $bar['kgwb'];
}


#ambil prd bgt
$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kg".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$str=" select kodeunit,divisi,thntnm,kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." ".$whB." ".$wh_bgt." and tahunbudget = '".$tahun."' ".$wh_bgt."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$prdbgtbi += $bar['bi'];
	@$prdbgtsdbi += $bar['sdbi'];
	@$prdbgtthn += $bar['kgsetahun'];
}

#khusus kegiatan tanaman
$str = "select kodekegiatan, sum(jumlah) as jumlah,substr(noakun,1,5) as jobgroup, 
substr(kodeblok,1,6) as divisi,noakun,periode,kodeorg  
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('128') ".$wh." ".$where." and 
periode between '".$periode1."' and  '".$periode2."' 
group by substr(noakun,1,5),noakun,periode,substr(kodeblok,1,6), kodekegiatan"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['kodekegiatan']==''){
		$bar['kodekegiatan']=$bar['noakun'];
	}
	if($tipe=='keg'){		
		$arrakun7[$bar['kodekegiatan']] = $bar['kodekegiatan'];
		$cdarrakun['pml'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
	}
		
	if($bar['periode']==$periode2){
		#jobgroup => akun 5, jobcode => akun
		@$realbi[$bar['jobgroup']] += $bar['jumlah'];
		if($tipe=='keg'){
			@$cdrealbi[$bar['kodekegiatan']] += $bar['jumlah'];
		}else{			
			@$cdrealbi[$bar['noakun']] += $bar['jumlah'];
		}
	}
	@$realsdbi[$bar['jobgroup']] += $bar['jumlah'];
	if($tipe=='keg'){
		@$cdrealsdbi[$bar['kodekegiatan']] += $bar['jumlah'];
	}else{	
		@$cdrealsdbi[$bar['noakun']] += $bar['jumlah'];
	}
	
	
	#noakun
	$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
	$listakun['pml'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
	if($tipe=='keg'){
		$cdarrakun['pml'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
	}else{		
		$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
	}
	
	$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
	if($tipe=='keg'){
		$arrakun7[$bar['kodekegiatan']] = $bar['kodekegiatan'];
	}else{		
		$arrakun7[$bar['noakun']] = $bar['noakun'];
	}
	#end akun
}


$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";

#ini khusus budget kebun
$str=" select kegiatan,substr(noakun,1,5) as jobgroup,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$bgtbi[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']] += $bar['rupiah'];
	
	
	if($tipe=='keg'){
		@$cdbgtbi[$bar['kegiatan']] += $bar['bi'];
		@$cdbgtsdbi[$bar['kegiatan']] += $bar['sdbi'];
		@$cdbgtthn[$bar['kegiatan']] += $bar['rupiah'];
	}else{		
		@$cdbgtbi[$bar['noakun']] += $bar['bi'];
		@$cdbgtsdbi[$bar['noakun']] += $bar['sdbi'];
		@$cdbgtthn[$bar['noakun']] += $bar['rupiah'];
	}
}

#ini khusus budget UMUM
$str=" select substr(noakun,1,5) as jobgroup,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget='UMUM' and tahunbudget = '".$tahun."' ".$whereakun."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$bgtbi[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']] += $bar['rupiah'];
	
	@$cdbgtbi[$bar['noakun']] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']] += $bar['rupiah'];
}

if ($proses == 'excel') {
	$arrtipe=array("group"=>"Per Job Group","code"=>"Per Job Code");
	$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	if($kdorg!=''){$xkdorg=$nmorg[$kdorg];}else{$xkdorg=$_SESSION['lang']['all'];}
	if($divisi!=''){$xdivisi=$nmorg[$divisi];}else{$xdivisi=$_SESSION['lang']['all'];}
	if($tt!=''){$xtt=$tt;}else{$xtt=$_SESSION['lang']['all'];}
	if($ip!=''){$xip=$ip;}else{$xip=$_SESSION['lang']['all'];}
	
	
	$tab="<table class=sortable cellspacing=1 width=100%>";
	$tab.="<tr><td align=center colspan=15>ANALISA BIAYA Nursery (".$arrtipe[$tipe].")</td>";
	$tab.="<tr><td align=center colspan=15>" . $_SESSION['lang']['pt'] . " : ".$nmorg[$pt].";&nbsp;";
	$tab.="" . $_SESSION['lang']['unit'] . " : ".$xkdorg."</td></tr>";
	$tab.="<tr><td align=center colspan=15>" . $_SESSION['lang']['divisi'] . " : ".$xdivisi.";&nbsp;";
	$tab.="" . $_SESSION['lang']['tahuntanam'] . " : ".$xtt."</td></tr>";
	$tab.="<tr><td align=center colspan=15>" . $_SESSION['lang']['intiplasma'] . " : ".$xip.";&nbsp;";
	$tab.="" . $_SESSION['lang']['periode'] . " : ".$prd."</td></tr>";
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<table class=sortable cellpadding=5 cellspacing=1>";
}

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['kode']."</th>
            <th align=center rowspan='2'></th>
            <th align=center rowspan='1' colspan='5'>".$_SESSION['lang']['bulanini']."</th>
            <th align=center rowspan='1' colspan='5'>".$_SESSION['lang']['sdbulanini']."</th>
            <th align=center rowspan='2' colspan='2'>".$_SESSION['lang']['tahunanggaran']."</th>
        </tr>
        <tr>
            <th align=center colspan='2'>".$_SESSION['lang']['realisasi']."</th>  
            <th align=center colspan='2'>".$_SESSION['lang']['budget']."</th>  
            <th align=center rowspan='2'>%</th>
			<th align=center colspan='2'>".$_SESSION['lang']['realisasi']."</th>  
            <th align=center colspan='2'>".$_SESSION['lang']['budget']."</th>  
            <th align=center rowspan='2'>%</th>
        </tr>
        <tr>
            <th align=center>".$_SESSION['lang']['luas']." (Ha)</th>  
            <th align=center colspan='2'>".@nantozero($luasreal,2)."</th>  
            <th align=center colspan='2'>".@nantozero($luasbudget,2)."</th>  
            <th align=center colspan='2'>".@nantozero($luasreal,2)."</th>  
            <th align=center colspan='2'>".@nantozero($luasbudget,2)."</th>  
            <th align=center colspan='2'>".@nantozero($luasbudget,2)."</th>  
        </tr>
    </thead>
 <tbody>";

$nmkode=array('pml'=>'Pemeliharaan','pnn'=>'Panen dan Pengangkutan','umm'=>'Tidak Langsung (Umum)');

#isinya ada 2 jenis
switch ($tipe) {
	case'group':
		$tab.="<tr class=rowcontent style=background-color:#E8DAEF>
            <td></td>  
            <td align=center><i>Code</i></td>  
            <td align=center><i>Activity Group</i></td>  
            <td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
            <td align=center></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
            <td align=center></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
        </tr>";
		foreach($arrkode as $kode){
			$no=0;
			foreach($arrakun as $akun){
				if(@$listakun[$kode][$akun]!=''){					
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						$show=" style=display:none";
					}
					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					$tab.="<td>".$nmakun[$akun]."</td>";
					#===== bi =====
					$detrealbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','bi','real')";
					$detbgtbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','bi','budget')";
					
					$tab.="<td ".$detrealbi." align=right>".@nantozero($realbi[$akun])."</td>";
					if($kode=='pml'){
						@$rprealbisat=$realbi[$akun]/$luasreal;
						@$rpbgtbisat=$bgtbi[$akun]/$luasbudget;
					}else{
						@$rprealbisat=$realbi[$akun]/$prdrealbi;
						@$rpbgtbisat=$bgtbi[$akun]/$prdbgtbi;
					}
					$tab.="<td align=right>".@nantozero($rprealbisat,2)."</td>";
					$tab.="<td ".$detbgtbi." align=right>".@nantozero($bgtbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtbisat,2)."</td>";
					$persenbi=0;
					if($rprealbisat>0){						
						@$persenbi=(($rpbgtbisat-$rprealbisat)/$rpbgtbisat)*100;
					}
					$c="";
					if($persenbi<0){
						$c=" style=color:red;";
					}
					$tab.="<td align=right ".$c.">".@nantozero($persenbi,2)."</td>";
					#===== end bi =====
					#====== sdbi ======
					$detrealsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','sdbi','real')";
					$detbgtsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','sdbi','budget')";
					
					$tab.="<td align=right ".$detrealsdbi.">".@nantozero($realsdbi[$akun])."</td>";
					if($kode=='pml'){
						@$rprealsdbisat=$realsdbi[$akun]/$luasreal;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$luasbudget;
					}else{
						@$rprealsdbisat=$realsdbi[$akun]/$prdrealsdbi;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$prdbgtsdbi;
					}
					$tab.="<td align=right>".@nantozero($rprealsdbisat,2)."</td>";
					$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($bgtsdbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtsdbisat,2)."</td>";
					
					$persensdbi=0;
					if($rprealsdbisat>0){						
						@$persensdbi=(($rpbgtsdbisat-$rprealsdbisat)/$rpbgtsdbisat)*100;
					}
					$c="";
					if($persensdbi<0){
						$c=" style=color:red;";
					}
					$tab.="<td align=right ".$c.">".@nantozero($persensdbi,2)."</td>";
					#===== end sdbi =====
					
					#====== thn ======
					if($kode=='pml'){
						@$rpbgtthnsat=$bgtthn[$akun]/$luasbudget;
					}else{
						@$rpbgtthnsat=$bgtthn[$akun]/$prdbgtthn;
					}
					
					$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','thn','budget')";
					
					$tab.="<td align=right ".$detbgtthn.">".@nantozero($bgtthn[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsat,2)."</td>";
					#===== end thn =====
					$tab.="</tr>";
					
					@$ttlrealbi[$kode]+=$realbi[$akun];
					@$ttlbgtbi[$kode]+=$bgtbi[$akun];
					@$ttlrealsdbi[$kode]+=$realsdbi[$akun];
					@$ttlbgtsdbi[$kode]+=$bgtsdbi[$akun];
					@$ttlbgtthn[$kode]+=$bgtthn[$akun];
					
					@$trealbi+=$realbi[$akun];
					@$tbgtbi+=$bgtbi[$akun];
					@$trealsdbi+=$realsdbi[$akun];
					@$tbgtsdbi+=$bgtsdbi[$akun];
					@$tbgtthn+=$bgtthn[$akun];
				}
			}
			
			# sub total
			if($kode=='umm'){
				$detrealbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','real')";
				$detbgtbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','budget')";
				
				$detrealsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','real')";
				$detbgtsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','budget')";
						
				$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','thn','budget')";
			}else{
				$detrealbi=$detbgtbi=$detrealsdbi=$detbgtsdbi=$detbgtthn="";
			}
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			#===== bi =====
			$tab.="<td align=right ".$detrealbi.">".@nantozero($ttlrealbi[$kode])."</td>";
			if($kode=='pml'){
				@$ttlrprealbisat=$ttlrealbi[$kode]/$luasreal;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$luasbudget;
			}else{
				@$ttlrprealbisat=$ttlrealbi[$kode]/$prdrealbi;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$prdbgtbi;
			}
			$tab.="<td align=right>".@nantozero($ttlrprealbisat,2)."</td>";
			$tab.="<td align=right ".$detbgtbi.">".@nantozero($ttlbgtbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtbisat,2)."</td>";
			@$ttlpersenbi=(($ttlrpbgtbisat-$ttlrprealbisat)/$ttlrpbgtbisat)*100;
			$c="";
			if($ttlpersenbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersenbi,2)."</td>";
			#=== end bi ===
			#====== sdbi ======
			$tab.="<td align=right ".$detrealsdbi.">".@nantozero($ttlrealsdbi[$kode])."</td>";
			if($kode=='pml'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasreal;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudget;
			}else{
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
			}
			$tab.="<td align=right>".@nantozero($ttlrprealsdbisat,2)."</td>";
			$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($ttlbgtsdbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtsdbisat,2)."</td>";
			
			@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
			$c="";
			if($ttlpersensdbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
			#===== end sdbi =====
			#====== thn ======
			if($kode=='pml'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudget;
			}else{
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
			}
			$tab.="<td align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsat,2)."</td>";
			#===== end thn =====
			$tab.="</tr>";
		}
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td align=center colspan=3>GRAND TOTAL</td>";
	
	#===== bi =====
	$tab.="<td align=right>".@nantozero($trealbi)."</td>";
	@$rpperprdrealbi=$trealbi/@$prdrealbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtbi)."</td>";
	@$rpperprdbgtbi=$tbgtbi/$prdbgtbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtbi,2)."</td>";
	@$gtpersenbi=(($rpperprdbgtbi-$rpperprdrealbi)/$rpperprdbgtbi)*100;
	$tab.="<td align=right>".@nantozero($gtpersenbi,2)."</td>";
	#=== end bi ===
	#====== sdbi ======
	$tab.="<td align=right>".@nantozero($trealsdbi)."</td>";
	@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtsdbi)."</td>";
	@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
	@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
	$tab.="<td align=right>".@nantozero($gtpersensdbi,2)."</td>";
	#===== end sdbi =====
	#====== thn ======
	$tab.="<td align=right>".@nantozero($tbgtthn)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
	#===== end thn =====
	
	break;
	
	case'keg':
	case'code':
	//sort($arrakun7);
		$tab.="<tr class=rowcontent style=background-color:#E8DAEF>
            <td></td>  
            <td align=center><i>Code</i></td>  
            <td align=center><i>Activity Group</i></td>  
            <td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
            <td align=center></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
            <td align=center></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Ha</i></td>
        </tr>";
		foreach($arrkode as $kode){
			$no=0;
			foreach($arrakun7 as $akun){
				if(@$cdarrakun[$kode][$akun]!=''){					
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						//$show=" style=display:none";
					}
					if($tipe=='code'){						
						$d=substr($akun,0,5);
					}else{
						if(substr($akun,0,1)=='7'){
							$d=substr($akun,0,5);
						}else{							
							$d=substr($akun,0,7);
						}
					}
					if($d!=$n){
						$tab.="<tr class=rowcontent ".$show." style=background-color:#ccfffd;font-weight:bold;>";
						$tab.="<td></td>";
						$tab.="<td><i>".$d."</i></td>";
						if(getNamaAkun($d)!=''){							
							$tab.="<td><i>".ucwords(strtolower(getNamaAkun($d)))."</i></td>";
						}else{							
							$tab.="<td><i>".ucwords(strtolower(getNamaKeg($d)))."</i></td>";
						}
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="</tr>";
					}
					
					
					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					if($nmakun[$akun]!=''){							
						$tab.="<td>".$nmakun[$akun]."</td>";
					}else{							
						$tab.="<td><i>".getNamaKeg($akun)."</i></td>";
					}
					#===== bi =====
					$detrealbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','bi','real')";
					$detbgtbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','bi','budget')";
					
					$tab.="<td align=right ".$detrealbi.">".@nantozero($cdrealbi[$akun])."</td>";
					if($kode=='pml'){
						@$rprealbisat=$cdrealbi[$akun]/$luasreal;
						@$rpbgtbisat=$cdbgtbi[$akun]/$luasbudget;
					}else{
						@$rprealbisat=$cdrealbi[$akun]/$prdrealbi;
						@$rpbgtbisat=$cdbgtbi[$akun]/$prdbgtbi;
					}
					$tab.="<td align=right>".@nantozero($rprealbisat,2)."</td>";
					$tab.="<td align=right ".$detbgtbi.">".@nantozero($cdbgtbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtbisat,2)."</td>";
					
					$persenbi=0;
					if($rprealbisat>0){						
						@$persenbi=(($rpbgtbisat-$rprealbisat)/$rpbgtbisat)*100;
					}
					$c="";
					if($persenbi<0){
						$c=" style=color:red;";
					}
					$tab.="<td align=right ".$c.">".@nantozero($persenbi,2)."</td>";
					#===== end bi =====
					#====== sdbi ======
					
					$detrealsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','sdbi','real')";
					$detbgtsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','sdbi','budget')";
					
					$tab.="<td align=right ".$detrealsdbi.">".@nantozero($cdrealsdbi[$akun])."</td>";
					if($kode=='pml'){
						@$rprealsdbisat=$cdrealsdbi[$akun]/$luasreal;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$luasbudget;
					}else{
						@$rprealsdbisat=$cdrealsdbi[$akun]/$prdrealsdbi;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$prdbgtsdbi;
					}
					$tab.="<td align=right>".@nantozero($rprealsdbisat,2)."</td>";
					$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($cdbgtsdbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtsdbisat,2)."</td>";
					
					$persensdbi=0;
					if($rprealsdbisat>0){						
						@$persensdbi=(($rpbgtsdbisat-$rprealsdbisat)/$rpbgtsdbisat)*100;
					}
					$c="";
					if($persensdbi<0){
						$c=" style=color:red;";
					}
					$tab.="<td align=right ".$c.">".@nantozero($persensdbi,2)."</td>";
					#===== end sdbi =====
					
					#====== thn ======
					if($kode=='pml'){
						@$rpbgtthnsat=$cdbgtthn[$akun]/$luasbudget;
					}else{
						@$rpbgtthnsat=$cdbgtthn[$akun]/$prdbgtthn;
					}
							
					$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','thn','budget')";
					
					$tab.="<td align=right ".$detbgtthn.">".@nantozero($cdbgtthn[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsat,2)."</td>";
					#===== end thn =====
					$tab.="</tr>";
					
					@$ttlrealbi[$kode]+=$cdrealbi[$akun];
					@$ttlbgtbi[$kode]+=$cdbgtbi[$akun];
					@$ttlrealsdbi[$kode]+=$cdrealsdbi[$akun];
					@$ttlbgtsdbi[$kode]+=$cdbgtsdbi[$akun];
					@$ttlbgtthn[$kode]+=$cdbgtthn[$akun];
					
					@$trealbi+=$cdrealbi[$akun];
					@$tbgtbi+=$cdbgtbi[$akun];
					@$trealsdbi+=$cdrealsdbi[$akun];
					@$tbgtsdbi+=$cdbgtsdbi[$akun];
					@$tbgtthn+=$cdbgtthn[$akun];
				}
				
				$n=$d;
			}
			
			# sub total
			if($kode=='umm'){
				$detrealbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','real')";
				$detbgtbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','budget')";
				
				$detrealsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','real')";
				$detbgtsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','budget')";
						
				$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','thn','budget')";
			}else{
				$detrealbi=$detbgtbi=$detrealsdbi=$detbgtsdbi=$detbgtthn="";
			}
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			#===== bi =====
			$tab.="<td align=right ".$detrealbi.">".@nantozero($ttlrealbi[$kode])."</td>";
			if($kode=='pml'){
				@$ttlrprealbisat=$ttlrealbi[$kode]/$luasreal;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$luasbudget;
			}else{
				@$ttlrprealbisat=$ttlrealbi[$kode]/$prdrealbi;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$prdbgtbi;
			}
			$tab.="<td align=right>".@nantozero($ttlrprealbisat,2)."</td>";
			$tab.="<td align=right ".$detbgtbi.">".@nantozero($ttlbgtbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtbisat,2)."</td>";
			@$ttlpersenbi=(($ttlrpbgtbisat-$ttlrprealbisat)/$ttlrpbgtbisat)*100;
			$c="";
			if($ttlpersenbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersenbi,2)."</td>";
			#=== end bi ===
			#====== sdbi ======
			$tab.="<td align=right ".$detrealsdbi.">".@nantozero($ttlrealsdbi[$kode])."</td>";
			if($kode=='pml'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasreal;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudget;
			}else{
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
			}
			$tab.="<td align=right>".@nantozero($ttlrprealsdbisat,2)."</td>";
			$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($ttlbgtsdbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtsdbisat,2)."</td>";
			
			@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
			$c="";
			if($ttlpersensdbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
			#===== end sdbi =====
			#====== thn ======
			if($kode=='pml'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudget;
			}else{
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
			}
			$tab.="<td align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsat,2)."</td>";
			#===== end thn =====
			$tab.="</tr>";
		}
		
	
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td align=center colspan=3>GRAND TOTAL</td>";
	#===== bi =====
	$tab.="<td align=right>".@nantozero($trealbi)."</td>";
	@$rpperprdrealbi=$trealbi/@$prdrealbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtbi)."</td>";
	@$rpperprdbgtbi=$tbgtbi/$prdbgtbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtbi,2)."</td>";
	@$gtpersenbi=(($rpperprdbgtbi-$rpperprdrealbi)/$rpperprdbgtbi)*100;
	$tab.="<td align=right>".@nantozero($gtpersenbi,2)."</td>";
	#=== end bi ===
	#====== sdbi ======
	$tab.="<td align=right>".@nantozero($trealsdbi)."</td>";
	@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtsdbi)."</td>";
	@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
	@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
	$tab.="<td align=right>".@nantozero($gtpersensdbi,2)."</td>";
	#===== end sdbi =====
	#====== thn ======
	$tab.="<td align=right>".@nantozero($tbgtthn)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
	#===== end thn =====
	
	break;
}
 
$tab.="</tbody></table>";

switch ($proses) {
######PREVIEW
    case 'preview':
    	if ($tipe == 'summary' || $tipe == 'detail') {
    		echo $html;
    	} else {
    		echo $tab;
    	}
    break;

######EXCEL	
    case 'excel':
    	if ($tipe == 'summary' || $tipe == 'detail') {
    		$print = $html;
    	} else {
    		$print = $tab;
    	}
        $nop_ = $tipe;
        if (strlen($print) > 0) {
			$print.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $print)) {
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
		$e="";
	}else if(is_infinite($e)){
		$e="";
	}else{
		$e=$e;
	}
	$n = hidezerodecimal($e,$i);
	if($n==0 or $n==''){
		$n='';
	}
	
	return $n;
}



?>