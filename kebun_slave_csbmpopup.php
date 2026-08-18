<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$param = array();$param = $_POST;if(count($param)==0){$param = $_GET;	}

$method= checkPostGet('method', '');
$blok  = checkPostGet('blok', '');
$tmtd  = checkPostGet('tmtd', '');
$prd   = checkPostGet('periode', '');
$prod  = checkPostGet('prod', '');


$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $prd;

$periodelalu1       = ($tahun-1)."-01";
$periodelalu2       = ($tahun-1)."-".$bulan;
$periodelalusetahun2= ($tahun-1)."-12";

$arrbd   = explode('-',periodeberikut($prd)); 
$tahundpn= $arrbd[0]; 
$bulandpn= $arrbd[1];
$rangebln= month_inbetween($periode1,$periode2);


switch($method){
	case'ttlbyybgt':
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 width=100%>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['noakun']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['namaakun']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['kegiatan']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['namakegiatan']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['hk2']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['upah']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['material']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['transport']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['lain']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['total']."</th>
				</tr>
			</thead>
			<tbody>";
			
		$str = "select * from " . $dbname . ".keu_5parameterjurnal a  where jurnalid in ('PNN01','PNN02')"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$akunupahpnn[$bar['noakundebet']]=$bar['noakundebet'];
		}
		$akuntranspnn=array('6110103','6110104');
		$lbrpupuk    =array('621010302','621010305','621010308');
		$transpupuk  =array('621010323','621010324');
		$whakun      =" and substr(noakun,1,3) in ('611')";
		$whakunumum  =" and noakun like '7%'";
		
		$e="("; $s="(";
		for($i=1;$i<=intval($bulan);$i++){
			$r="rp".addZero($i,2);$n="fis".addZero($i,2);
			if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
		}
		$e.=")"; $s.=")";

		$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";

		#ini khusus budget kebun
		$str=" select kodebarang,tipebudget,kodebudget,kegiatan,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 and  kodeorg='".$blok."' and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whakun."";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['noakun'],0,3)=='611'){
				if(substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK'){
					if(in_array($bar['noakun'],$akunupahpnn)){
						#khusus HK panen
						$biaya[$bar['kegiatan']]['tkbgt']['tm']+=$bar['bivol'];
						$biaya[$bar['kegiatan']]['tkbgt']['td']+=$bar['sdbivol'];
						$biaya[$bar['kegiatan']]['tkbgt']['thn']+=$bar['jumlah'];
					}
					#LABOUR
					$biaya[$bar['kegiatan']]['bgt_lab']['tm']+=($bar['bi']);
					$biaya[$bar['kegiatan']]['bgt_lab']['td']+=($bar['sdbi']);
					$biaya[$bar['kegiatan']]['bgt_lab']['thn']+=($bar['rupiah']);
				}else if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
					#MATERIAL			
					$biaya[$bar['kegiatan']]['bgt_mat']['tm']+=($bar['bi']);
					$biaya[$bar['kegiatan']]['bgt_mat']['td']+=($bar['sdbi']);
					$biaya[$bar['kegiatan']]['bgt_mat']['thn']+=($bar['rupiah']);
				}else if($bar['kodebudget']=='VHC'){
					#TRANS
					$biaya[$bar['kegiatan']]['bgt_tra']['tm']+=($bar['bi']);
					$biaya[$bar['kegiatan']]['bgt_tra']['td']+=($bar['sdbi']);
					$biaya[$bar['kegiatan']]['bgt_tra']['thn']+=($bar['rupiah']);
				}else{
					#OTHER
					$biaya[$bar['kegiatan']]['bgt_oth']['tm']+=($bar['bi']);
					$biaya[$bar['kegiatan']]['bgt_oth']['td']+=($bar['sdbi']);
					$biaya[$bar['kegiatan']]['bgt_oth']['thn']+=($bar['rupiah']);
				}
				$biaya[$bar['kegiatan']]['ttlbyybgt']['tm']+=($bar['bi']);
				$biaya[$bar['kegiatan']]['ttlbyybgt']['td']+=($bar['sdbi']);
				$biaya[$bar['kegiatan']]['ttlbyybgt']['thn']+=($bar['rupiah']);
				
				$data[$bar['kegiatan']]['td']='td';
				$data[$bar['kegiatan']]['tm']='tm';
				$data[$bar['kegiatan']]['thn']='thn';
			}
		}
		

		$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun');	
		$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');	
		$noakun = makeOption($dbname,'setup_kegiatan','kodekegiatan,noakun');	
		foreach($data as $keg => $v1){
			foreach($v1 as $jenis){
				if($param['tmtd']==$jenis){									
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";	
					$tab.="<td align=center>".$noakun[$keg]."</td>";	
					$tab.="<td align=left>".$optakun[$noakun[$keg]]."</td>";	
					$tab.="<td align=center>".$keg."</td>";	
					$tab.="<td align=left>".$optkeg[$keg]."</td>";	
					$tab.="<td align=right>".number_format($biaya[$keg]['tkbgt'][$jenis])."</td>";	
					$tab.="<td align=right>".number_format($biaya[$keg]['bgt_lab'][$jenis])."</td>";	
					$tab.="<td align=right>".number_format($biaya[$keg]['bgt_mat'][$jenis])."</td>";	
					$tab.="<td align=right>".number_format($biaya[$keg]['bgt_tra'][$jenis])."</td>";	
					$tab.="<td align=right>".number_format($biaya[$keg]['bgt_oth'][$jenis])."</td>";	
					$tab.="<td align=right>".number_format($biaya[$keg]['ttlbyybgt'][$jenis])."</td>";	
					
					$total['tkbgt']+=$biaya[$keg]['tkbgt'][$jenis];
					$total['bgt_lab']+=$biaya[$keg]['bgt_lab'][$jenis];
					$total['bgt_mat']+=$biaya[$keg]['bgt_mat'][$jenis];
					$total['bgt_tra']+=$biaya[$keg]['bgt_tra'][$jenis];
					$total['bgt_oth']+=$biaya[$keg]['bgt_oth'][$jenis];
					$total['ttlbyybgt']+=$biaya[$keg]['ttlbyybgt'][$jenis];
				}
			}
		}
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=5>Total</td>";	
		$tab.="<td align=right>".number_format($total['tkbgt'])."</td>";	
		$tab.="<td align=right>".number_format($total['bgt_lab'])."</td>";	
		$tab.="<td align=right>".number_format($total['bgt_mat'])."</td>";	
		$tab.="<td align=right>".number_format($total['bgt_tra'])."</td>";	
		$tab.="<td align=right>".number_format($total['bgt_oth'])."</td>";	
		$tab.="<td align=right>".number_format($total['ttlbyybgt'])."</td>";	
		$tab.="</tr>";	
		
		echo $tab;
	break;
	case'ttlbyypnn':
	case'byy_tra':
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 width=100%>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['tanggal']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['noakun']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['namaakun']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['kegiatan']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['namakegiatan']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['upah']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['material']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['transport']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['lain']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['total']."</th>
				</tr>
			</thead>
			<tbody>";
			
		$str = "select * from " . $dbname . ".keu_5parameterjurnal a  where jurnalid in ('PNN01','PNN02')"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$akunupahpnn[$bar['noakundebet']]=$bar['noakundebet'];
		}	
			
		$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun');	
		$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');	
		
		$akuntranspnn=array('6110103','6110104');
		$lbrpupuk    =array('621010302','621010305','621010308');
		$transpupuk  =array('621010323','621010324');
		$whakun      =" and substr(noakun,1,3) in ('611','621')";
		$whakunumum  =" and noakun like '7%'";

		# biaya tahun ini
		$str = "select a.*, a.kodekegiatan,kodeorg,a.kodeblok, sum(jumlah) as jumlah, periode,noakun,kodejurnal 
		from " . $dbname . ".keu_jurnaldt_vw a   
		where 1=1 and kodeblok='".$blok."' and periode between '".$periode1."' and  '".$periode2."' ".$whakun."     
		group by periode,noakun,kodejurnal,kodekegiatan order by tanggal desc, noakun asc"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['noakun'],0,3)=='611'){
				#biaya panen
				if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
					#labor
					if($bar['periode']==$prd){
						$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['byy_lab']['tm']+=($bar['jumlah']);
					}
					$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['byy_lab']['td']+=($bar['jumlah']);
				}else if(substr($bar['kodejurnal'],0,3)=='INV'){
					#material
					if($bar['periode']==$prd){
						$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['byy_mat']['tm']+=($bar['jumlah']);
					}
					$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['byy_mat']['td']+=($bar['jumlah']);
				}else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
					#transport
					if($bar['periode']==$prd){
						$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['byy_tra']['tm']+=($bar['jumlah']);
					}
					$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['byy_tra']['td']+=($bar['jumlah']);
				}else{
					#other
					if($bar['periode']==$prd){
						$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['byy_oth']['tm']+=($bar['jumlah']);
					}
					$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['byy_oth']['td']+=($bar['jumlah']);
				}
				
				if($bar['periode']==$prd){
					$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['ttlbyypnn']['tm']+=($bar['jumlah']);
				}
				$biaya[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['ttlbyypnn']['td']+=($bar['jumlah']);
				
				if($param['tmtd']=='tm'){
					if($bar['periode']==$prd){						
						$data[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['tm']='tm';
					}
				}else{					
					$data[$bar['tanggal']][$bar['noakun']][$bar['kodekegiatan']]['td']='td';
				}
				
			}
		}
		foreach($data as $tgl => $v2){
			foreach($v2 as $akun => $v3){
				foreach($v3 as $keg => $v4){
					foreach($v4 as $jenis){
						if($param['tmtd']==$jenis){									
							$no++;
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=center>".$no."</td>";	
							$tab.="<td align=center>".$tgl."</td>";	
							$tab.="<td align=center>".$akun."</td>";	
							$tab.="<td align=left>".$optakun[$akun]."</td>";	
							$tab.="<td align=center>".$keg."</td>";	
							$tab.="<td align=left>".$optkeg[$keg]."</td>";	
							$tab.="<td align=right>".number_format($biaya[$tgl][$akun][$keg]['byy_lab'][$jenis])."</td>";	
							$tab.="<td align=right>".number_format($biaya[$tgl][$akun][$keg]['byy_mat'][$jenis])."</td>";	
							$tab.="<td align=right>".number_format($biaya[$tgl][$akun][$keg]['byy_tra'][$jenis])."</td>";	
							$tab.="<td align=right>".number_format($biaya[$tgl][$akun][$keg]['byy_oth'][$jenis])."</td>";	
							$tab.="<td align=right>".number_format($biaya[$tgl][$akun][$keg]['ttlbyypnn'][$jenis])."</td>";	
							
							$total['byy_lab']+=$biaya[$tgl][$akun][$keg]['byy_lab'][$jenis];
							$total['byy_mat']+=$biaya[$tgl][$akun][$keg]['byy_mat'][$jenis];
							$total['byy_tra']+=$biaya[$tgl][$akun][$keg]['byy_tra'][$jenis];
							$total['byy_oth']+=$biaya[$tgl][$akun][$keg]['byy_oth'][$jenis];
							$total['ttlbyypnn']+=$biaya[$tgl][$akun][$keg]['ttlbyypnn'][$jenis];
						}
					}
				}
			}
		}
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=6>Total</td>";	
		$tab.="<td align=right>".number_format($total['byy_lab'])."</td>";	
		$tab.="<td align=right>".number_format($total['byy_mat'])."</td>";	
		if($method=='byy_tra'){
			$tab.="<td align=right style=background-color:cyan>".number_format($total['byy_tra'])."</td>";	
		}else{
			$tab.="<td align=right>".number_format($total['byy_tra'])."</td>";	
		}
		$tab.="<td align=right>".number_format($total['byy_oth'])."</td>";	
		if($method=='ttlbyypnn'){
			$tab.="<td align=right style=background-color:cyan>".number_format($total['ttlbyypnn'])."</td>";	
		}else{			
			$tab.="<td align=right>".number_format($total['ttlbyypnn'])."</td>";	
		}
		$tab.="</tr>";	
		
		echo $tab;
	break;
	case'prd_smly':
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 width=100%>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['tanggal']." Panen</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['tanggal']." SPB</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['nospb']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['jjg']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['kg']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['brondol']."</th>
				</tr>
			</thead>
			<tbody>";
			
		#ambil prd real
		if($prod=='1'){
			$produksi="(kgwbnetto/1000) as kg,";
		}else{
			$produksi="(kgwb/1000) as kg,";
		}

		#ambil prd real tahun lalu
		$str = "select ".$produksi." substr(tanggal,1,7) as periode, a.* from " . $dbname . ".kebun_spb_vw a  where 1=1 and blok='".$blok."' and substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periodelalu2."' order by tanggal desc, blok"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			if($param['tmtd']=='tm'){				
				if($bar['periode']==$periodelalu2){		
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";	
					$tab.="<td align=center>".$bar['tanggalpanen']."</td>";	
					$tab.="<td align=center>".$bar['tanggal']."</td>";	
					$tab.="<td align=center>".$bar['nospb']."</td>";	
					$tab.="<td align=right>".$bar['jjg']."</td>";	
					$tab.="<td align=right>".number_format($bar['kg'],2)."</td>";	
					$tab.="<td align=right>".$bar['brondolan']."</td>";	
					$tab.="</tr>";
					
					$ttljjg+=$bar['jjg'];
					$ttlkg+=$bar['kg'];
					$ttlbrd+=$bar['brondolan'];
					
				}
			}else{
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";	
				$tab.="<td align=center>".$bar['tanggalpanen']."</td>";	
				$tab.="<td align=center>".$bar['tanggal']."</td>";	
				$tab.="<td align=center>".$bar['nospb']."</td>";	
				$tab.="<td align=right>".$bar['jjg']."</td>";	
				$tab.="<td align=right>".number_format($bar['kg'],2)."</td>";	
				$tab.="<td align=right>".$bar['brondolan']."</td>";	
				$tab.="</tr>";
				
				$ttljjg+=$bar['jjg'];
				$ttlkg+=$bar['kg'];
				$ttlbrd+=$bar['brondolan'];
			}
		}
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=4>Total</td>";	
		$tab.="<td align=right>".$ttljjg."</td>";	
		$tab.="<td align=right>".number_format($ttlkg,2)."</td>";	
		$tab.="<td align=right>".$ttlbrd."</td>";	
		$tab.="</tr>";	
		
		echo $tab;
	break;
	case'prd':
	case'brd':
	case'jjg':
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 width=100%>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['tanggal']." Panen</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['tanggal']." SPB</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['nospb']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['jjg']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['kg']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['brondol']."</th>
				</tr>
			</thead>
			<tbody>";
			
		#ambil prd real
		if($prod=='1'){
			$produksi="(kgwbnetto/1000) as kg,";
		}else{
			$produksi="(kgwb/1000) as kg,";
		}

		$prdton=$prdtontitle=array();
		$str = "select ".$produksi." substr(tanggal,1,7) as periode, a.* from " . $dbname . ".kebun_spb_vw a  where 1=1 and blok='".$blok."' and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' order by tanggal desc, blok"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			if($param['tmtd']=='tm'){				
				if($bar['periode']==$prd){		
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";	
					$tab.="<td align=center>".$bar['tanggalpanen']."</td>";	
					$tab.="<td align=center>".$bar['tanggal']."</td>";	
					$tab.="<td align=center>".$bar['nospb']."</td>";	
					$tab.="<td align=right>".$bar['jjg']."</td>";	
					$tab.="<td align=right>".number_format($bar['kg'],2)."</td>";	
					$tab.="<td align=right>".number_format($bar['brondolan']/1000,2)."</td>";	
					$tab.="</tr>";
					
					$ttljjg+=$bar['jjg'];
					$ttlkg+=$bar['kg'];
					$ttlbrd+=($bar['brondolan']/1000);
					
				}
			}else{
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";	
				$tab.="<td align=center>".$bar['tanggalpanen']."</td>";	
				$tab.="<td align=center>".$bar['tanggal']."</td>";	
				$tab.="<td align=center>".$bar['nospb']."</td>";	
				$tab.="<td align=right>".$bar['jjg']."</td>";	
				$tab.="<td align=right>".number_format($bar['kg'],2)."</td>";	
				$tab.="<td align=right>".number_format($bar['brondolan']/1000,2)."</td>";	
				$tab.="</tr>";
				
				$ttljjg+=$bar['jjg'];
				$ttlkg+=$bar['kg'];
				$ttlbrd+=($bar['brondolan']/1000);
			}
		}
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=4>Total</td>";	
		$tab.="<td align=right>".$ttljjg."</td>";	
		$tab.="<td align=right>".number_format($ttlkg,2)."</td>";	
		$tab.="<td align=right>".number_format($ttlbrd,2)."</td>";	
		$tab.="</tr>";	
		
		echo $tab;
	break;
	case'luaspnn':
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 width=100%>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['tanggal']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['hk2']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['luas']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['jjg']."</th>
				</tr>
			</thead>
			<tbody>";
		#ambil luas panen
		$str = "select a.*, substr(tanggal,1,7) as periode from " . $dbname . ".kebun_rekappnn a  where 1=1 and blok='".$blok."' and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' order by tanggal desc"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			if($param['tmtd']=='tm'){
				if($bar['periode']==$prd){		
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";	
					$tab.="<td align=center>".$bar['tanggal']."</td>";	
					$tab.="<td align=right>".$bar['tenagakerja']."</td>";	
					$tab.="<td align=right>".$bar['luaspanen']."</td>";	
					$tab.="<td align=right>".$bar['jjgpanen']."</td>";	
					$tab.="</tr>";
					
					$ttlhk+=$bar['tenagakerja'];
					$ttlha+=$bar['luaspanen'];
					$ttljjg+=$bar['jjgpanen'];
				}				
			}else{
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";	
				$tab.="<td align=center>".$bar['tanggal']."</td>";	
				$tab.="<td align=right>".$bar['tenagakerja']."</td>";	
				$tab.="<td align=right>".$bar['luaspanen']."</td>";	
				$tab.="<td align=right>".$bar['jjgpanen']."</td>";	
				$tab.="</tr>";
				
				$ttlhk+=$bar['tenagakerja'];
				$ttlha+=$bar['luaspanen'];
				$ttljjg+=$bar['jjgpanen'];
			}
		}
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=2>Total</td>";	
		$tab.="<td align=right>".$ttlhk."</td>";	
		$tab.="<td align=right>".$ttlha."</td>";	
		$tab.="<td align=right>".$ttljjg."</td>";	
		$tab.="</tr>";	
		
		$tab.="</tbody>";	
		$tab.="</table>";	
				
		echo $tab;		
	break;
	case'tkpnn':
		#ambil tk panen
		$str = "select * from " . $dbname . ".kebun_3premipemanen a  where 1=1 and blok='".$blok."' and periode between '".$periode1."' and  '".$periode2."' group by karyawanid, notransaksi, tanggalpanen order by tanggalpanen, notransaksi"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['periode']==$prd){		
				$datatkbi[$bar['notransaksi']][$bar['tanggalpanen']][$bar['karyawanid']]=$bar['karyawanid'];
			}
			$datatk[$bar['notransaksi']][$bar['tanggalpanen']][$bar['karyawanid']]=$bar['karyawanid'];
		}
		
		if($param['tmtd']=='tm'){
			$data=$datatkbi;
		}else{
			$data=$datatk;
		}
		
		
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 width=100%>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['tanggal']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['notransaksi']."</th>
					<th align=center rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				</tr>
			</thead>
			<tbody>";
		foreach($data as $blk => $vtpnn){
			foreach($vtpnn as $tglpnn => $vkar){
				foreach($vkar as $kary){
					$biaya[$blk]['tkpnn']['td']+=1;
					$biaya[$blk]['tkpnn']['tm']+=$datatkbi[$blk][$tglpnn][$kary];
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";	
					$tab.="<td align=center>".$tglpnn."</td>";	
					$tab.="<td align=center>".$blk."</td>";	
					$tab.="<td align=left>".getNamaKaryawan($kary)."</td>";	
					$tab.="</tr>";	
				}
			}
		}	
			
		$tab.="</tbody>";	
		$tab.="</table>";	
				
		echo $tab;		
	break;
}
?>
