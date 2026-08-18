<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses     = checkPostGet('proses', '');
$kdorg      = checkPostGet('kdorg', '');
$pt         = checkPostGet('pt', '');
$tt         = checkPostGet('tt', '');
$ip         = checkPostGet('ip', '');
$divisi     = checkPostGet('divisi', '');
$prd        = checkPostGet('prd', '');
$tipe       = checkPostGet('tipe', '');
$jenis      = checkPostGet('jenis', '');
$akun       = checkPostGet('akun', '');
$bi         = checkPostGet('bi', '');
$real       = checkPostGet('real', '');

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
if($bi=='bi'){
	$periode1 = $prd;
	$periode2 = $prd;
}else{	
	$periode1 = $tahun."-01";
	$periode2 = $prd;
}

$where='';$where2='';$where_spb=$wh="";$wh2="";$whB="";$wh_spb="";$wh_bgt=$wh_bgtrp='';$whipkebun='';


if($ip!=''){
	$whip=" and intiplasma='".$ip."'";
	if($ip=='I'){
		$inti='1';
	}else{
		$inti='0';
	}
	$whipkebun=" and inti='".$inti."'";
}
$listblokip = [];
if($pt!=''){
	$listkodeorg = [];
	$str = "select * from " . $dbname . ".organisasi where induk='".$pt."' and tipe='KEBUN' ".$whipkebun."";
	$res = fetchdata($str);
	foreach($res as $bar){
		$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
	$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
	$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$where_spb=" and a.kodeorg in ('".implode("','",$listkodeorg)."')";
	$whunit=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
}else{
	$listkodeorg = [];
	#= Update by Abdul
	#= Bikin Bug
	#= Karena laporan pabrik searchnya tipe Kebun
	// $str = "select * from " . $dbname . ".organisasi where tipe='KEBUN' ".$whipkebun."";

	$str = "select * from " . $dbname . ".organisasi where tipe='PABRIK' ".$whipkebun."";
	#= End Abdul
	$res = fetchdata($str);
	foreach($res as $bar){
		$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}

	$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
	$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$where_spb=" and a.kodeorg in ('".implode("','",$listkodeorg)."')";
	$whunit=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
}

if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
	$whunit=" and substr(a.unit,1,4) ='".$kdorg."'";
}

if($ip!=''){
	$wh.=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
	$wh_bgt.=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$wh2.=" and a.intiplasma='".$ip."'";
	$whB.=" and a.intiplasma='".$ip."'";
	$wh_bgtrp.=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
}


if($divisi!=''){
	$wh.=" and a.kodeblok like '".$divisi."%'";
	$whB.=" and a.kodeblok like '".$divisi."%'";
	$wh2.=" and a.kodeorg like '".$divisi."%'";
	$wh_bgt.=" and a.divisi like '".$divisi."%'";
	$wh_bgtrp.=" and a.kodeorg like '".$divisi."%'";
}
if($tt!=''){
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$wh2.=" and a.tahuntanam='".$tt."'";
	$whB.=" and a.thntnm='".$tt."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
}


#=============== mari kita mulai dari sini ===============#
if($tipe=='keg'){
	if(substr($akun,0,1)!='7'){
		if(strlen($akun)<9){
			$whereakun = " and (kodekegiatan like '".$akun."%' or kodekegiatan='')";
		}else{			
			$whereakun = " and kodekegiatan like '".$akun."%'";
		}
		$whereakunbgt = " and kegiatan like '".$akun."%'";
	}
	
	$whereakun .= " and noakun like '".substr($akun,0,7)."%'";
	$whereakunbgt .= " and noakun like '".substr($akun,0,7)."%'";
	
}else{	
	$whereakun = " and noakun like '".$akun."%'";
	$whereakunbgt = " and noakun like '".$akun."%'";
}
$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');

if ($jenis == 'excel') {
    $tab="<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<table class=sortable cellpadding=5 cellspacing=1>";
}

switch ($real) {
    case 'real':
		if ($jenis == 'html') {
			$tab.="<img onclick=\"getdetailexcel('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','','".$akun."','excel','".$bi."','".$real."')\" src=images/excel.jpg class=resicon title='MS.Excel'>";
		}
		$lmto = ['labor','material','transport','other'];
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['nojurnal']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['kodejurnal']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['namajurnal']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['keterangan']."</th>
					<th align=center colspan=".(count($lmto)+1).">".$_SESSION['lang']['jumlah']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['noreferensi']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['nodok']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['nik2']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['nopol']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['barang']."</th>
				</tr>
				<tr class=rowheader>";
					foreach($lmto as $lm){						
						$tab.="<th align=center>".$lm."</th>";
					}
					$tab.="<th align=center>Total</th>";
				$tab.="</tr>
				";
			$tab.="</tr>
			</thead>
		<tbody>";
		
		$str = "select *  from " . $dbname . ".keu_5parameterjurnal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmkelompok[$bar['jurnalid']]=$bar['keterangan'];
		}
		
		
		if(substr($akun,0,1)=='7'){$wh="";}
	
		$noreff=[];
		$str = "select *  from " . $dbname . ".keu_jurnaldt_vw a where 1=1 ".$whereakun." ".$wh." ".$where." and 
		periode between '".$periode1."' and  '".$periode2."'  order by periode asc, noreferensi asc, kodejurnal asc, tanggal asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$noreff[$bar['noreferensi']]=$bar['noreferensi'];
		}
		
		$sql = "select *  from " . $dbname . ".kebun_aktifitas where notransaksi in ('".implode("','",$noreff)."')";
		$req = fetchdata($sql);
		foreach($req as $val){
			if($val['tipetransaksi']!='PNN'){				
				$notransbkm[$val['notransaksi']]='BKM';
			}else{
				$notransbkm[$val['notransaksi']]='PNNJJG';
			}
		}

		$sql = "select distinct notransaksi  from " . $dbname . ".kebun_3premipemanen where blok like '".$param['blok']."%' and notransaksi in ('".implode("','",$noreff)."')";
		$req = fetchdata($sql);
		foreach($req as $val){
			$notransbkm[$val['notransaksi']]='PNNKG';
		}
		$subtotal=[];
		foreach($res as $bar){
			$periode = substr($bar['tanggal'],0,7);
			if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC' and substr($bar['kodejurnal'],0,3)!='SPK' and substr($bar['kodejurnal'],0,3)!='PRJ'){
				#labor
				if(substr($bar['noakun'],0,1)=='7' and (substr($bar['kodejurnal'],0,2)=='KK' or substr($bar['kodejurnal'],0,2)=='KM' or substr($bar['kodejurnal'],0,2)=='BK' or substr($bar['kodejurnal'],0,2)=='BM' or substr($bar['kodejurnal'],0,1)=='M')){					
					$subtotal[$periode]['other']+=$bar['jumlah'];
				}else{
					$subtotal[$periode]['labor']+=$bar['jumlah'];
				}
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$subtotal[$periode]['material']+=$bar['jumlah'];
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
				#transport
				$subtotal[$periode]['transport']+=$bar['jumlah'];
			}else{
				#other
				$subtotal[$periode]['other']+=$bar['jumlah'];
			}
			$subtotalkanan=[];
			// $gtotal=[];
			foreach($lmto as $value){
				$subtotalkanan[$periode]+=$subtotal[$periode][$value];
			}	
		}
		
		foreach($res as $bar){
			$periode = substr($bar['tanggal'],0,7);
			$d = $periode;
			if($d!=$n){			
				$tab.="<tr class=rowcontent style=background-color:#A3E4D7;font-weight:bold;cursor:pointer; title='Click untuk melihat detail' onclick=showbaris('".$d."');>";
				$tab.="<td align=center colspan=5>Sub total biaya periode ".$d."</td>";
				$tab.="<td align=center></td>";
				foreach($lmto as $value){
					$tab.="<td align=right>".number_format($subtotal[$periode][$value])."</td>";
					$ttlrupiahknn[$periode]+=$subtotal[$periode][$value];
				}	
				$tab.="<td align=right>".number_format($ttlrupiahknn[$periode])."</td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="</tr>";
			}
			$no++;
			$tab.="<tr class=rowcontent  name=".$d."[]>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['nojurnal']."</td>";
			$tab.="<td>".$bar['kodejurnal']."</td>";
			$tab.="<td>".$nmkelompok[$bar['kodejurnal']]."</td>";
			$tab.="<td nowrap>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td>".$bar['keterangan']."</td>";
			$rupiah=[];
			if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC' and substr($bar['kodejurnal'],0,3)!='SPK' and substr($bar['kodejurnal'],0,3)!='PRJ'){
				#labor
				if(substr($bar['noakun'],0,1)=='7' and (substr($bar['kodejurnal'],0,2)=='KK' or substr($bar['kodejurnal'],0,2)=='KM' or substr($bar['kodejurnal'],0,2)=='BK' or substr($bar['kodejurnal'],0,2)=='BM' or substr($bar['kodejurnal'],0,1)=='M')){					
					$rupiah['other']+=$bar['jumlah'];
				}else{
					$rupiah['labor']+=$bar['jumlah'];
				}				
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$rupiah['material']+=$bar['jumlah'];
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
				#transport
				$rupiah['transport']+=$bar['jumlah'];
			}else{
				#other
				$rupiah['other']+=$bar['jumlah'];
			}
			$ttlrpknn=0;
			foreach($lmto as $value){
				$tab.="<td align=right>".number_format($rupiah[$value])."</td>";
				$ttlrpknn+=$rupiah[$value];
				$gtotal[$value]+=$rupiah[$value];
			}	
			$tab.="<td align=right>".number_format($ttlrpknn)."</td>";
			
			if($notransbkm[$bar['noreferensi']]=='BKM'){
				$tab.="<td title='click detail BKM' style=cursor:pointer;color:blue; onclick=detailData('".$bar['noreferensi']."','".$bar['kodekegiatan']."','".$bar['kodeblok']."','BKM','html')>".$bar['noreferensi']."</td>";
			}elseif($notransbkm[$bar['noreferensi']]=='PNNKG'){
				$tab.="<td title='click detail Panen' style=cursor:pointer;color:blue; onclick=detailpnnkg('".$bar['noreferensi']."','".$bar['kodekegiatan']."','".$bar['kodeblok']."','".$bar['nik']."','".$bar['nojurnal']."','html')>".$bar['noreferensi']."</td>";
			}elseif($notransbkm[$bar['noreferensi']]=='PNNJJG'){
				$tab.="<td title='click detail Panen' style=cursor:pointer;color:blue; onclick=detailpnnjjg('".$bar['noreferensi']."','".$bar['kodekegiatan']."','".$bar['kodeblok']."','".$bar['nik']."','BKM','html')>".$bar['noreferensi']."</td>";
			}else{
				$clickreff="";
				if($bar['kodejurnal']=='M'){
					$cekkasbank = makeOption($dbname,'keu_kasbankht','notransaksi,notransaksi',"notransaksi='".$bar['noreferensi']."'");
					if($cekkasbank[$bar['noreferensi']]==''){						
						$clickreff=" style=cursor:pointer;color:blue; onclick=clickdetailreff('JM','".$bar['noreferensi']."','".$bar['nojurnal']."');";
					}else{
						$clickreff=" title='Jurnal otomatis (Auto debet / Auto Kredit)'";
					}
				}
				if($bar['kodejurnal']=='KK' or $bar['kodejurnal']=='KM' or $bar['kodejurnal']=='BK' or $bar['kodejurnal']=='BM'){
					$clickreff=" style=cursor:pointer;color:blue; onclick=clickdetailreff('KB','".$bar['noreferensi']."','".$bar['nojurnal']."');";
				}
				if($bar['kodejurnal']=='INVK1'){
					$clickreff=" style=cursor:pointer;color:blue; onclick=clickdetailreff('GI','".$bar['noreferensi']."','".$bar['nojurnal']."');";
				}
				if($bar['kodejurnal']=='PNN19' or $bar['kodejurnal']=='PNN20'){
					if(substr($bar['tanggal'],-2)=='15'){
						$tglmulai=substr($bar['tanggal'],0,7)."-01";
						$tglsampai=$bar['tanggal'];
					}else{
						$tglmulai=substr($bar['tanggal'],0,7)."-16";
						$tglsampai=tglakhir($bar['tanggal']);
					}
					
					$clickreff=" style=cursor:pointer;color:blue; onclick=clickdetailreff('PNN19','".$bar['noreferensi']."','".$bar['nojurnal']."','".$bar['kodeorg']."','".$tglmulai."','".$tglsampai."','".$bar['kodeblok']."');";
				}
				if(substr($bar['kodejurnal'],0,3)=='SPK'){
					$clickreff=" style=cursor:pointer;color:blue; onclick=clickdetailreff('SPK','".$bar['nodok']."','".$bar['nojurnal']."','".$bar['kodeorg']."','".$bar['tanggal']."','".$bar['kodekegiatan']."','".$bar['kodeblok']."');";
				}
				$tab.="<td ".$clickreff.">".$bar['noreferensi']."</td>";
			}
			$tab.="<td>".$bar['nodok']."</td>";
			$tab.="<td>".getKary($bar['nik'])."</td>";
			
			$clickvhc=" title='click detail kendaraan' style=cursor:pointer;color:blue; onclick=detailVhc('".$bar['kodevhc']."','".$bar['kodekegiatan']."','".$bar['kodeblok']."','".$bar['tanggal']."','VHC')";
			$tab.="<td ".$clickvhc.">".$bar['kodevhc']."</td>";
			$tab.="<td>".getNamaBrg($bar['kodebarang'])."</td>";
			$tab.="</tr>";
			
			$n=$d;
		}
		$tab.="<tr class=rowcontent style=background-color:#17A589;font-weight:bold;>";
		$tab.="<td align=center colspan=5>Grand total biaya tahun ".substr($d,0,4)."</td>";
		$tab.="<td align=center></td>";
		foreach($lmto as $value){
			$tab.="<td align=right>".number_format($gtotal[$value])."</td>";
			$gtkanan+=$gtotal[$value];
		}	
		$tab.="<td align=right>".number_format($gtkanan)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		
		
		
		
		
		
		// #khusus kegiatan tanaman
		// echo $str = "select * from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 ".$whereakun." ".$wh." ".$where." and 
		// periode between '".$periode1."' and  '".$periode2."' order by tanggal asc, noakun asc, nojurnal asc"; 
		// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $no=0;
		// while ($bar = $res->fetch()) {
			// $no++;
			// $tab.="<tr class=rowcontent>";
			// $tab.="<td align=center>".$no."</td>";
			// $tab.="<td>".$bar['nojurnal']."</td>";
			// $tab.="<td align=center>".$bar['tanggal']."</td>";
			// $tab.="<td align=center>".$bar['noakun']."</td>";
			// $tab.="<td>".$bar['keterangan']."</td>";
			// $tab.="<td align=right>".@number_format($bar['debet'],0)."</td>";
			// $tab.="<td align=right>".@number_format($bar['kredit'],0)."</td>";
			// $tab.="<td align=right>".@number_format($bar['jumlah'],0)."</td>";
			// $tab.="<td>".$namaOrg[$bar['kodeblok']]."</td>";
			// if(getNopol($bar['kodevhc'])!=''){				
				// $tab.="<td>".$bar['kodevhc']." - ".getNopol($bar['kodevhc'])."</td>";
			// }else{
				// $tab.="<td>".$bar['kodevhc']."</td>";
			// }
			// $tab.="</tr>";
			
			// @$tdebet+=$bar['debet'];
			// @$tkredit+=$bar['kredit'];
			// @$tjumlah+=$bar['jumlah'];
		// }
        
		// $tab.="<tr class=rowcontent>";
		// $tab.="<td align=center colspan=5>TOTAL</td>";
		// $tab.="<td align=right>".@number_format($tdebet,0)."</td>";
		// $tab.="<td align=right>".@number_format($tkredit,0)."</td>";
		// $tab.="<td align=right>".@number_format($tjumlah,0)."</td>";
		// $tab.="<td></td>";
		// $tab.="<td></td>";
		// $tab.="</tr>";
		
	$tab.="</tbody></table>";
	break;
	case'budget':
		if ($jenis == 'html') {
			$tab.="<img onclick=\"getdetailexcel('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','','".$akun."','excel','".$bi."','".$real."')\" src=images/excel.jpg class=resicon title='MS.Excel'>";
		}
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center>".$_SESSION['lang']['kodebudget']."</th>
					<th align=center>".$_SESSION['lang']['noakun']."</th>
					<th align=center>".$_SESSION['lang']['kegiatan']."</th>
					<th align=center>".$_SESSION['lang']['blok']."</th>
					<th align=center>".$_SESSION['lang']['kodevhc']."</th>
					<th align=center>".$_SESSION['lang']['kodebarang']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['fisik']."</th>
					<th align=center>".$_SESSION['lang']['bi']."</th>
					<th align=center>".$_SESSION['lang']['sbi']."</th>
					<th align=center>".$_SESSION['lang']['setahun']."</th>
				</tr>
			</thead>
		 <tbody>";
		
		$optkode=makeOption($dbname,'bgt_kode','kodebudget,nama');
		$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		$optkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		
		$boldbi="";
		$boldsdbi="";
		$boldthn="";
		if($bi=='bi'){
			$boldbi=" style=font-weight:bold";
		}elseif($bi=='sdbi'){
			$boldsdbi=" style=font-weight:bold";
		}elseif($bi=='thn'){
			$boldthn=" style=font-weight:bold";
		}	
			
		$e="("; $s="(";
		for($i=1;$i<=intval($bulan);$i++){
			$r="rp".addZero($i,2);$n="fis".addZero($i,2);
			if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
		}
		$e.=")"; $s.=")";
		if(substr($akun,0,1)=='7'){$wh_bgtrp=" and kodebudget='UMUM'";}
		$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";
		
		$str = " select kegiatan,rp01,rp02,rp03,rp04,rp05,rp06,rp07,rp08,rp09,rp10,rp11,rp12,kodevhc,kodebarang,
		tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol, 
		fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a 
		where 1=1 ".$where." ".$wh_bgtrp."  and tahunbudget = '".$tahun."' ".$whereakunbgt." order by kodebudget, kegiatan, kodeorg";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while ($bar = $res->fetch()) {
			$optbgr=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$optkode[$bar['kodebudget']]."</td>";
			$tab.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
			if($bar['kegiatan']!=''){				
				$tab.="<td align=left>".$bar['kegiatan']." - ".@$optkeg[$bar['kegiatan']]."</td>";
			}else{
				$tab.="<td></td>";
			}
			$tab.="<td>".$namaOrg[$bar['kodeorg']]."</td>";
			$tab.="<td>".$bar['kodevhc']."</td>";
			if($bar['kodebarang']!=''){				
				$tab.="<td>".$bar['kodebarang']." - ".$optbgr[$bar['kodebarang']]."</td>";
			}else{
				$tab.="<td></td>";
			}
			$tab.="<td align=center>".$bar['satuanj']."</td>";
			$tab.="<td align=right>".@number_format($bar['jumlah'],0)."</td>";
			$tab.="<td align=right ".$boldbi.">".@number_format($bar['bi'],0)."</td>";
			$tab.="<td align=right ".$boldsdbi.">".@number_format($bar['sdbi'],0)."</td>";
			$tab.="<td align=right ".$boldthn.">".@number_format($bar['rupiah'],0)."</td>";
			$tab.="</tr>";
			
			@$bgtbi += $bar['bi'];
			@$bgtsdbi += $bar['sdbi'];
			@$bgtthn += $bar['rupiah'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=9>TOTAL</td>";
		$tab.="<td align=right ".$boldbi.">".@number_format($bgtbi,0)."</td>";
		$tab.="<td align=right ".$boldsdbi.">".@number_format($bgtsdbi,0)."</td>";
		$tab.="<td align=right ".$boldthn.">".@number_format($bgtthn,0)."</td>";
		
		$tab.="</tr>";
		
		$tab.="</tbody></table>";
		
	break;
}

switch ($jenis) {
######PREVIEW
    case 'html':
        echo $tab;
        break;

######EXCEL	
    case 'excel':
        $nop_ = "Detail Jurnal";
        if (strlen($tab) > 0) {
			$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
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