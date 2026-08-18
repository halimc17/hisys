<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses   = checkPostGet('proses', '');
$kdorg    = checkPostGet('kdorg', '');
$pt       = checkPostGet('pt', '');
$tt       = checkPostGet('tt', '');
$keg      = checkPostGet('keg', '');
$klp      = checkPostGet('klp', '');
$divisi   = checkPostGet('divisi', '');
$prd      = checkPostGet('prd', '');
$tipe     = checkPostGet('tipe', '');
$tampil   = checkPostGet('tampil', '');
$jenis    = checkPostGet('jenis', '');
$bulanini = checkPostGet('bulanini', '');
$ip		  = checkPostGet('ip', '');
$blok	  = checkPostGet('blok', '');

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}
$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $prd;


switch ($proses) {
    case 'preview':
    case 'excel':
	
	// if($pt==''){exit("warning : Kode PT harus di pilih.");}

	$where='';$where2='';$where_spb=$whip='';
	
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
		$str = "select * from " . $dbnamerpt . ".organisasi where induk='".$pt."' and tipe='KEBUN' ".$whipkebun."";
		$res = fetchData($str);
		foreach($res as $bar){
			$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		}
		$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
		$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
		$where_spb=" and a.kodeorg in ('".implode("','",$listkodeorg)."')";
		$whunit=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
	}else{
		$listkodeorg = [];
		$str = "select * from " . $dbnamerpt . ".organisasi where tipe='KEBUN' ".$whipkebun."";
		$res = fetchData($str);
		foreach($res as $bar){
			$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		}

		$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
		$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
		$where_spb=" and a.kodeorg in ('".implode("','",$listkodeorg)."')";
		$whunit=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
	}
	
	$str = "select kodeorg from " . $dbnamerpt . ".setup_blok where 1=1 ".$whip." and substr(kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
	$res = fetchData($str);
	foreach($res as $bar){
		$listblokip[substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
		$listblokip[substr($bar['kodeorg'],0,6)]=substr($bar['kodeorg'],0,6);
		$listblokip[$bar['kodeorg']]=$bar['kodeorg'];
	}
	$listblokbgtip = [];
	$str = "select kodeblok from " . $dbnamerpt . ".bgt_blok where 1=1 ".$whip." and tahunbudget='".$tahun."' and substr(kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$res = fetchData($str);
	foreach($res as $bar){
		$listblokbgtip[substr($bar['kodeblok'],0,4)]=substr($bar['kodeblok'],0,4);
		$listblokbgtip[substr($bar['kodeblok'],0,6)]=substr($bar['kodeblok'],0,6);
		$listblokbgtip[$bar['kodeblok']]=$bar['kodeblok'];
	}
	
	if($ip!=''){
		//$wh.=" and a.kodeblok in ('".implode("','",$listblokip)."')";
		// $whpres.=" and a.kodeorg in ('".implode("','",$listblokip)."')";
		// $whpnn.=" and a.blok in ('".implode("','",$listblokip)."')";
		// $wh_bgt.=" and a.kodeblok in ('".implode("','",$listblokbgtip)."')";
		$wh2.=" and a.intiplasma='".$ip."'";
		$whB.=" and a.intiplasma='".$ip."'";
		// $wh_spb.=" and b.blok in ('".implode("','",$listblokip)."')";
		// $wh_bgtrp.=" and a.kodeorg in ('".implode("','",$listblokbgtip)."')";
	}
	if($tt!=''){
		$listblokip = [];
		$str = "select kodeorg from " . $dbnamerpt . ".setup_blok where 1=1 ".$whip." and substr(kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
		$res = fetchData($str);
		foreach($res as $bar){
			$listblokip[substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
			$listblokip[substr($bar['kodeorg'],0,6)]=substr($bar['kodeorg'],0,6);
			$listblokip[$bar['kodeorg']]=$bar['kodeorg'];
		}
		$listblokbgtip = [];
		$str = "select kodeblok from " . $dbnamerpt . ".bgt_blok where 1=1 ".$whip." and substr(kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
		$res = fetchData($str);
		foreach($res as $bar){
			$listblokbgtip[substr($bar['kodeblok'],0,4)]=substr($bar['kodeblok'],0,4);
			$listblokbgtip[substr($bar['kodeblok'],0,6)]=substr($bar['kodeblok'],0,6);
			$listblokbgtip[$bar['kodeblok']]=$bar['kodeblok'];
		}
		
		
		// $wh.=" and a.kodeblok in (select kodeorg from ".$dbnamerpt.".setup_blok where tahuntanam='".$tt."')";
		// $whpres.=" and a.kodeorg in (select kodeorg from ".$dbnamerpt.".setup_blok where tahuntanam='".$tt."')";
		// $whpnn.=" and a.blok in (select kodeorg from ".$dbnamerpt.".setup_blok where tahuntanam='".$tt."')";
		// $wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbnamerpt.".bgt_blok where thntnm='".$tt."')";
		$wh2.=" and a.tahuntanam='".$tt."'";
		$whB.=" and a.thntnm='".$tt."'";
		// $wh_spb.=" and b.blok in (select kodeorg from ".$dbnamerpt.".setup_blok where tahuntanam='".$tt."')";
		// $wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbnamerpt.".bgt_blok where thntnm='".$tt."')";
	}
	
	if($kdorg!=''){
		$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
		$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
		$where_spb=" and a.kodeorg ='".$kdorg."'";
	}

	$wh="";$wh_spb="";$wh_bgt=$wh_bgtrp=$whin='';
	if($divisi!=''){
		$wh.=" and a.kodeblok like '".$divisi."%'";
		$whpres.=" and a.kodeorg like '".$divisi."%'";
		$whpnn.=" and a.blok like '".$divisi."%'";
		$whB.=" and a.kodeblok like '".$divisi."%'";
		$wh2.=" and a.kodeorg like '".$divisi."%'";
		$wh_spb.=" and b.blok like '".$divisi."%'";
		$wh_bgt.=" and a.divisi like '".$divisi."%'";
		$wh_bgtrp.=" and a.kodeorg like '".$divisi."%'";
		if($tt!='')
		$whin.=" and a.kodeblok in (select kodeorg from ".$dbnamerpt.".setup_blok where tahuntanam ='".$tt."' and kodeblok like '".$divisi."%')";
	}
	if($blok!=''){
		$wh.=" and a.kodeblok like '".$blok."%'";
		$whpres.=" and a.kodeorg like '".$blok."%'";
		$whpnn.=" and a.blok like '".$blok."%'";
		$whB.=" and a.kodeblok like '".$blok."%'";
		$wh2.=" and a.kodeorg like '".$blok."%'";
		$wh_spb.=" and b.blok like '".$blok."%'";
		$wh_bgt.=" and a.divisi like '".$blok."%'";
		$wh_bgtrp.=" and a.kodeorg like '".$blok."%'";
		if($tt!='')
		$whin.=" and a.kodeblok in (select kodeorg from ".$dbnamerpt.".setup_blok where tahuntanam ='".$tt."' and kodeblok like '".$blok."%')";
	}
	

	#=============== mari kita mulai dari sini ===============#
	#ambil luas bgt
	$str = "select * from " . $dbnamerpt . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahun."' and statusblok='TM' order by kodeblok, substr(kodeblok,1,6), thntnm"; //exit("error".$str);
	$res=fetchData($str);
	foreach($res as $bar){
		$listkebun[substr($bar['kodeblok'],0,4)]=substr($bar['kodeblok'],0,4);
		$luas[$bar['kodeblok']]=$bar['hathnini'];
		$pokok[$bar['kodeblok']]=$bar['pokokthnini'];
		
		$listblok[substr($bar['kodeblok'],0,4)][substr($bar['kodeblok'],0,6)][$bar['kodeblok']]=$bar['kodeblok'];
	}
	

	#ambil luas realisasi
	$str = "select kodeorg, luasareaproduktif, jumlahpokok from " . $dbnamerpt . ".setup_blok_tahunan a  where 1=1 ".$wh2." ".$where." and tahun='".$tahun.$bulan."'  and statusblok='TM' order by kodeorg, substr(kodeorg,1,6), tahuntanam"; 
	if(count(fetchData($str))==0){
		$str = "select kodeorg, luasareaproduktif, jumlahpokok from " . $dbnamerpt . ".setup_blok a  where 1=1 ".$wh2." ".$where." and statusblok='TM' order by kodeorg, substr(kodeorg,1,6), tahuntanam"; 
	}
	$res=fetchData($str);
	foreach($res as $bar){
		@$luas[$bar['kodeorg']]=$bar['luasareaproduktif'];
		@$pokok[$bar['kodeorg']]=$bar['jumlahpokok'];
		$listkebun[substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
		
		$listblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['kodeorg']]=$bar['kodeorg'];
	}

	
	#ambil prd real
	$whprd="and substr(a.tanggal,1,7) between '".$periode1."' and  '".$periode2."'";
	$str = "select blok, sum(kgwb) as kgwb, substr(tanggal,1,7) as periode from " . $dbnamerpt . ".kebun_spbdt b left join " . $dbnamerpt . ".kebun_spbht a on a.nospb=b.nospb where 1=1 ".$where_spb." ".$whprd." group by blok, periode"; 
	$res = fetchData($str);
	foreach($res as $bar){
		if($listblokip[$bar['blok']]!=''){			
			if($bar['periode']==$periode2){		
				$prdrealbi[$bar['blok']] += $bar['kgwb'];
			}
			$prdrealsdbi[$bar['blok']] += $bar['kgwb'];
		}
	}
	
	#ambil prd bgt
	$e="(";
	for($i=1;$i<=intval($bulan);$i++){
		$r="kg".addZero($i,2);
		if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
	}
	$e.=")";

	$str=" select kodeunit,divisi,thntnm,kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbnamerpt.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." ".$whB." and tahunbudget = '".$tahun."'";
	$res=fetchData($str);
	foreach($res as $bar){
		if($listblokbgtip[$bar['kodeblok']]!=''){		
			$prdbgtbi[$bar['kodeblok']] += $bar['bi'];
			$prdbgtsdbi[$bar['kodeblok']] += $bar['sdbi'];
			$prdbgtthn[$bar['kodeblok']] += $bar['kgsetahun'];
		}
	}
	if($keg!='' and $keg!='null'){
		$arrkeg = explode(",",$keg);
		foreach($arrkeg as $kdkeg){
			$kodekeg[$kdkeg]=$kdkeg;
		}
		$wherekeg=" and kodekegiatan in ('".implode("','",$kodekeg)."')";
	}
	
	$listkeg=$listkegbi=$listakunbi=[];
	#khusus kegiatan tanaman
	switch($jenis){
		case'fisik':
			if($klp!='611'){				
				#pemel
				$str = "select a.kodeorg as kodeblok, kodekegiatan, sum(hasilkerja) as jumlah,substr(kodekegiatan,1,5) as jobgroup, substr(a.kodeorg,1,6) as divisi,substr(kodekegiatan,1,7) as noakun, substr(tanggal,1,7) as periode, b.kodeorg from " . $dbnamerpt . ".kebun_prestasi a 
				left join " . $dbnamerpt . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where 1=1 and substr(kodekegiatan,1,3) in ('".$klp."') ".$wherekeg." ".$whpres." ".$where." and tipetransaksi!='PNN' 
				and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' and b.jurnal='1'
				group by kodekegiatan, substr(tanggal,1,7), a.kodeorg order by a.kodeorg";
				$res = fetchData($str);
				foreach($res as $bar){
					if($listblokip[$bar['kodeblok']]!=''){	
						if($bar['periode']==$periode2){
							$realbyybi[$bar['kodeblok']][$bar['kodekegiatan']] += $bar['jumlah'];
							if($bar['kodekegiatan']!=''){		
								$listkegbi[$bar['kodekegiatan']]=$bar['kodekegiatan'];
								$listakunbi[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
							}
						}
						$realbyysdbi[$bar['kodeblok']][$bar['kodekegiatan']] += $bar['jumlah'];
						if($bar['kodekegiatan']!=''){		
							$listkeg[$bar['kodekegiatan']]=$bar['kodekegiatan'];
							$listakun[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
						}			
						$listblok[substr($bar['kodeblok'],0,4)][substr($bar['kodeblok'],0,6)][$bar['kodeblok']]=$bar['kodeblok'];
					}
				}
			}
			
			if($klp=='611'){				
				#panen jjg
				$kodeJurnal  = 'PNN01';
				$queryParam  = selectQuery($dbnamerpt,'keu_5parameterjurnal','noakunkredit,noakundebet'," jurnalid='".$kodeJurnal."'");
				$resParam    = fetchData($queryParam);
				$akundebet   = $resParam[0]['noakundebet'];
				$kodekegiatan= $akundebet."01";     
				
				# untuk premi kutib brondol
				$kodeJurnalpremibrd  = 'PNN03';
				$queryParampremibrd  = selectQuery($dbnamerpt,'keu_5parameterjurnal','noakunkredit,noakundebet'," jurnalid='".$kodeJurnalpremibrd."'");
				$resParampremibrd    = fetchData($queryParampremibrd);
				$akundebetpremibrd   = $resParampremibrd[0]['noakundebet'];
				$kodekegiatanpremibrd= $akundebetpremibrd."06";  

				$str = "select a.kodeorg as kodeblok, sum(hasilkerja) as jumlah, sum(brondolan) as brondolan, substr(a.kodeorg,1,6) as divisi, substr(tanggal,1,7) as periode, b.kodeorg from " . $dbnamerpt . ".kebun_prestasi a 
				left join " . $dbnamerpt . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where 1=1 ".$whpres." ".$where." and tipetransaksi='PNN' and tipe='JJG' 
				and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' and b.jurnal='1'
				group by kodekegiatan, substr(tanggal,1,7), a.kodeorg order by a.kodeorg";
				$res = fetchData($str);
				foreach($res as $bar){
					$bar['kodekegiatan'] = $kodekegiatan;
					if($listblokip[$bar['kodeblok']]!=''){						
						if($bar['periode']==$periode2){
							$realbyybi[$bar['kodeblok']][$bar['kodekegiatan']] += $bar['jumlah'];
							$realbyybi[$bar['kodeblok']][$kodekegiatanpremibrd] += $bar['brondolan'];
							if($bar['kodekegiatan']!=''){		
								$listkegbi[$bar['kodekegiatan']]=$bar['kodekegiatan'];
								$listakunbi[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
							}
						}
						$realbyysdbi[$bar['kodeblok']][$bar['kodekegiatan']] += $bar['jumlah'];
						$realbyysdbi[$bar['kodeblok']][$kodekegiatanpremibrd] += $bar['brondolan'];
						if($bar['kodekegiatan']!=''){		
							$listkeg[$bar['kodekegiatan']]=$bar['kodekegiatan'];
							$listakun[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
						}			
						$listblok[substr($bar['kodeblok'],0,4)][substr($bar['kodeblok'],0,6)][$bar['kodeblok']]=$bar['kodeblok'];
					}
				}
				
				$kodeJurnal = 'PNN02';
				$queryParam = selectQuery($dbnamerpt,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='".$kodeJurnal."'");
				$resParam = fetchData($queryParam);
				$kegpanen = $resParam[0]['noakundebet']."02";
				
				#panen kg
				$str = "select a.blok as kodeblok, sum(kgwb) as jumlah, divisi, periode, kodeorg from " . $dbnamerpt . ".kebun_3premipemanen a 
				where 1=1 ".$whpnn." ".$where." and periode between '".$periode1."' and  '".$periode2."' and posting='1'
				group by periode, a.blok order by a.blok";
				$res = fetchData($str);
				foreach($res as $bar){
					$bar['kodekegiatan'] = $kegpanen;
					if($listblokip[$bar['kodeblok']]!=''){						
						if($bar['periode']==$periode2){
							$realbyybi[$bar['kodeblok']][$bar['kodekegiatan']] += round($bar['jumlah']);
							if($bar['kodekegiatan']!=''){		
								$listkegbi[$bar['kodekegiatan']]=$bar['kodekegiatan'];
								$listakunbi[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
							}
						}
						$realbyysdbi[$bar['kodeblok']][$bar['kodekegiatan']] += round($bar['jumlah']);
						if($bar['kodekegiatan']!=''){		
							$listkeg[$bar['kodekegiatan']]=$bar['kodekegiatan'];
							$listakun[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
						}			
						$listblok[substr($bar['kodeblok'],0,4)][substr($bar['kodeblok'],0,6)][$bar['kodeblok']]=$bar['kodeblok'];
					}
				}
			}
			
			#bapp
			$str = "select kodeblok, kodekegiatan, sum(hasilkerjarealisasi) as jumlah,substr(kodekegiatan,1,5) as jobgroup, substr(a.kodeblok,1,6) as divisi,substr(kodekegiatan,1,7) as noakun, substr(tanggal,1,7) as periode, substr(a.kodeblok,1,4) as kodeorg from " . $dbnamerpt . ".log_baspk a 
			where 1=1 and substr(kodekegiatan,1,3) in ('".$klp."') ".$wherekeg." ".$wh." ".$where2."
			and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' and a.statusjurnal='1'
			group by kodekegiatan, substr(tanggal,1,7), a.kodeblok order by a.kodeblok";
			$res = fetchData($str);
			foreach($res as $bar){
				if($listblokip[$bar['kodeblok']]!=''){					
					if($bar['periode']==$periode2){
						$realbyybi[$bar['kodeblok']][$bar['kodekegiatan']] += $bar['jumlah'];
						if($bar['kodekegiatan']!=''){		
							$listkegbi[$bar['kodekegiatan']]=$bar['kodekegiatan'];
							$listakunbi[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
						}
					}
					$realbyysdbi[$bar['kodeblok']][$bar['kodekegiatan']] += $bar['jumlah'];
					if($bar['kodekegiatan']!=''){		
						$listkeg[$bar['kodekegiatan']]=$bar['kodekegiatan'];
						$listakun[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
					}				
					$listblok[substr($bar['kodeblok'],0,4)][substr($bar['kodeblok'],0,6)][$bar['kodeblok']]=$bar['kodeblok'];
				}
			}
			
			
			$numberformat='2';
		break;
		default:
			$numberformat='0';
		
			$str = "select kodeblok, kodekegiatan, sum(jumlah) as jumlah,substr(noakun,1,5) as jobgroup, substr(kodeblok,1,6) as divisi,noakun,periode,kodeorg  
			from " . $dbnamerpt . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('".$klp."') ".$wherekeg." ".$whin." ".$where." and periode between '".$periode1."' and  '".$periode2."' 
			group by kodekegiatan, periode, kodeblok order by kodeblok";
			// exit("error".$str);	
			$res = fetchData($str);
			foreach($res as $bar){
				if($listblokip[$bar['kodeblok']]!=''){					
					if($bar['periode']==$periode2){
						$realbyybi[$bar['kodeblok']][$bar['kodekegiatan']] += $bar['jumlah'];
						if($bar['kodekegiatan']!=''){		
							$listkegbi[$bar['kodekegiatan']]=$bar['kodekegiatan'];
							$listakunbi[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
						}
					}
					$realbyysdbi[$bar['kodeblok']][$bar['kodekegiatan']] += $bar['jumlah'];
					if($bar['kodekegiatan']!=''){		
						$listkeg[$bar['kodekegiatan']]=$bar['kodekegiatan'];
						$listakun[getNamaKeg($bar['kodekegiatan'],'noakun')][$bar['kodekegiatan']]=$bar['kodekegiatan'];
					}
					
					$listblok[substr($bar['kodeblok'],0,4)][substr($bar['kodeblok'],0,6)][$bar['kodeblok']]=$bar['kodeblok'];
				}
			}
		
		break;
	}
	
	array_multisort($listblok,SORT_ASC,SORT_STRING);
	ksort($listkeg);
	$listakun=[];
	foreach($listkeg as $keg){
		$listakun[getNamaKeg($keg,'noakun')][$keg]=$keg;
	}
	
	ksort($listkegbi);
	$listakunbi=[];
	foreach($listkegbi as $keg){
		$listakunbi[getNamaKeg($keg,'noakun')][$keg]=$keg;
	}
	
	
	
	ksort($listkebun);
	if ($proses == 'excel') {
		// $arrtipe=array("group"=>"Per Job Group","code"=>"Per Job Code");
		// $nmorg=makeOption($dbnamerpt,'organisasi','kodeorganisasi,namaorganisasi');
		// if($kdorg!=''){$xkdorg=$nmorg[$kdorg];}else{$xkdorg=$_SESSION['lang']['all'];}
		// if($divisi!=''){$xdivisi=$nmorg[$divisi];}else{$xdivisi=$_SESSION['lang']['all'];}
		// if($tt!=''){$xtt=$tt;}else{$xtt=$_SESSION['lang']['all'];}
		
		
		// $tab="<table class=sortable cellspacing=1 width=100% >";
		// $tab.="<tr><td align=center colspan=15>HARVESTING VS UPKEEP</td>";
		// $tab.="<tr><td align=center colspan=15>" . $_SESSION['lang']['pt'] . " : ".$nmorg[$pt].";&nbsp;";
		// $tab.="" . $_SESSION['lang']['unit'] . " : ".$xkdorg."</td></tr>";
		// $tab.="<tr><td align=center colspan=15>" . $_SESSION['lang']['divisi'] . " : ".$xdivisi.";&nbsp;";
		// $tab.="" . $_SESSION['lang']['tahuntanam'] . " : ".$xtt."</td></tr>";
		// $tab.="" . $_SESSION['lang']['periode'] . " : ".$prd."</td></tr>";
		$tab.="<table class=sortable cellspacing=1 border=1>";
	} else {
		$tab.="<div class='menu'>
			<div id='btninscmnt' class='menu-item'>Insert Comment</div>
			<div id='btnshowcmn' class='menu-item'>Show Comment</div>
			<div id='btnreloadframe' class='menu-item'>Reload Frame</div>
		</div>";
		$tab .="<table class=sortable cellpadding=5 cellspacing=1>";
	}
	
	$hidebi="";
	if($bulanini=='hide'){
		$hidebi=" hidden";
	}
	
	$tab.="
		<thead>
			<tr class=rowheader>
				<th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th align=center rowspan='3'>".$_SESSION['lang']['kebun']."</th>
				<th align=center rowspan='3'>".$_SESSION['lang']['divisi']."</th>
				<th align=center rowspan='3' width=40px>".$_SESSION['lang']['tahuntanam']."</th>
				<th align=center rowspan='3'>".$_SESSION['lang']['blok']."</th>
				<th align=center rowspan='3'>".$_SESSION['lang']['luas']."</th>
				<th align=center rowspan='3'>".$_SESSION['lang']['pokok']."</th>
				<th align=center rowspan='3'>".$_SESSION['lang']['sph']."</th>
				<th align=center ".$hidebi." colspan='".(6+COUNT($listkegbi))."'>".$_SESSION['lang']['bulanini']."</th>
				<th align=center colspan='".(6+COUNT($listkeg))."'>".$_SESSION['lang']['sdbulanini']."</th>
				<th align=center colspan='5'>".$_SESSION['lang']['tahunanggaran']."</th>
			</tr>
			<tr>
				<th ".$hidebi." align=center colspan=3>Produksi (Kg)</th>
				<th ".$hidebi." align=center colspan=2>Yield (Ton/Ha)</th>";
				foreach($listakunbi as $noakun => $val1){					
					$colspan=0;
					foreach($val1 as $kegiatan){
						$colspan++;
					}
					$tab.="<th ".$hidebi." colspan=".$colspan." align=center style=max-width:80px>".ucwords(strtolower(getNamaAkun($noakun)))."</th>";
				}
			$tab.=" <th ".$hidebi." align=center rowspan=2>Total</th>";
			$tab.=" <th align=center colspan=3>Produksi (Kg)</th>
					<th align=center colspan=2>Yield (Ton/Ha)</th>
				";
				
				foreach($listakun as $noakun => $val1){					
					$colspan=0;
					foreach($val1 as $kegiatan){
						$colspan++;
					}
					$tab.="<th colspan=".$colspan." align=center style=max-width:80px>".ucwords(strtolower(getNamaAkun($noakun)))."</th>";
				}
			$tab.="<th align=center rowspan=2>Total</th>";
			$tab.=" <th align=center colspan=3>Produksi (Kg)</th>
					<th align=center colspan=2>Yield (Ton/Ha)</th>";
			$tab.="
			</tr>
			<tr>
				<th ".$hidebi." align=center>Budget</th>  
				<th ".$hidebi." align=center>Actual</th>
				<th ".$hidebi." align=center>% Act</th>  
				<th ".$hidebi." align=center>Budget</th>  
				<th ".$hidebi." align=center>Actual</th>";
				foreach($listakunbi as $noakun => $val1){
					foreach($val1 as $kegiatan){
						if($jenis=='fisik'){
							$satuan="<br>(".getNamaKeg($kegiatan,'satuan').")";
						}else{
							$satuan="<br>(Rp)";
						}
						$tab.="<th ".$hidebi." align=center style=font-size:9px;max-width:80px>".ucwords(strtolower(getNamaKeg($kegiatan)))."<br><i>".$kegiatan."</i>".$satuan."</th>";
					}
				}
				
			$tab.="<th align=center>Budget</th>  
				<th align=center>Actual</th>
				<th align=center>% Act</th>  
				<th align=center>Budget</th>  
				<th align=center>Actual</th>";
				foreach($listakun as $noakun => $val1){
					foreach($val1 as $kegiatan){
						if($jenis=='fisik'){
							$satuan="<br>(".getNamaKeg($kegiatan,'satuan').")";
						}else{
							$satuan="<br>(Rp)";
						}
						$tab.="<th align=center style=font-size:9px;max-width:80px>".ucwords(strtolower(getNamaKeg($kegiatan)))."<br><i>".$kegiatan."</i>".$satuan."</th>";
					}
				}
			$tab.="<th align=center>Budget</th>  
				<th align=center>Actual</th>
				<th align=center>% Act</th>  
				<th align=center>Budget</th>  
				<th align=center>Actual</th>";
		$tab.="</tr>
		</thead>
	 <tbody>";
	// usort($listakun, function($a, $b) {
		// return $a['order'] <=> $b['order'];
	// });
	
	$showcomment=[];
	$str = "select * from ".$dbname.".kebun_2commentreport a where 1=1 ".$whunit." and periode <= '".$prd."' and periode like '".$tahun."%' and act='real' ";
	$res = fetchdata($str);
	foreach($res as $bar){
		$showcomment[$bar['unit']][$bar['kegiatan']][$bar['bi']][]=array('id'=>$bar['id'],'user'=>$bar['createdby'],'comment'=>substr($bar['comment'],0,40));
	}

	
	// echo $tab;
	// exit();
		$no=0;$realbyysdbiTemp=[];
		foreach($listkebun as $estate){		
			foreach($listblok[$estate] as $divisi => $v){
				foreach($v as $blok){
					foreach($listkeg as $kegiatan){
						$realbyysdbiTemp[$blok]+=$realbyysdbi[$blok][$kegiatan];
					}
				}
			}
		}
		// echo "<pre>";
		// print_r($showcomment);
		// echo "</pre>";
		
		foreach($listkebun as $estate){		
			foreach($listblok[$estate] as $divisi => $v){
				foreach($v as $blok){
					// if($realbyysdbiTemp[$blok]=='0'){
						// $realbyysdbiTemp[$blok]='';
					// }
					
					switch($tampil){
						case'1':#Produksi Capai Target
							$case=$prdrealsdbi[$blok]>=$prdbgtsdbi[$blok];
						break;
						case'2':#Produksi Tidak Capai Target
							$case=$prdrealsdbi[$blok]<$prdbgtsdbi[$blok];
						break;
						case'3':#Tidak ada Produksi ada Biaya
							$case=($prdrealsdbi[$blok]<='0' and $realbyysdbiTemp[$blok]>'0');
						break;
						case'4':#Tidak ada Produksi ada Budget Produksi
							$case=($prdrealsdbi[$blok]<='0' and $prdbgtsdbi[$blok]>'0');
						break;
						default:						
							$case=$blok!='';
						break;
					}
					if($case){					
						$no++;
						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td>".substr($divisi,0,4)."</td>";
						$tab.="<td>".$divisi."</td>";
						$tab.="<td align=center>".getBlok($blok,'tahuntanam')."</td>";
						$tab.="<td align=center>".getNamaOrg($blok)."</td>";
						$tab.="<td align=right>".hidezerodecimal($luas[$blok],2)."</td>";
						$tab.="<td align=right>".hidezerodecimal($pokok[$blok])."</td>";
						$tab.="<td align=right>".@hidezerodecimal(@$pokok[$blok]/@$luas[$blok],2)."</td>";
						#bulan ini
						$tab.="<td ".$hidebi." align=right>".hidezerodecimal($prdbgtbi[$blok])."</td>";
						$tab.="<td ".$hidebi." align=right>".hidezerodecimal($prdrealbi[$blok])."</td>";
						$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$prdrealbi[$blok]/@$prdbgtbi[$blok]*100,2)."</td>";
						$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$prdbgtbi[$blok]/@$luas[$blok]/1000,2)."</td>";
						$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$prdrealbi[$blok]/@$luas[$blok]/1000,2)."</td>";
						foreach($listakunbi as $noakun => $val1){
							foreach($val1 as $kegiatan){
								
								$click=$adacomment=""; $flag=0;
								$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
								if(!empty($showcomment[$blok][$kegiatan]['bi'])){
									$adacomment="class=has_sign"; $flag='1';
									$title=" title='".getKary($showcomment[$blok][$kegiatan]['bi'][0]['user'])."\n".$showcomment[$blok][$kegiatan]['bi'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
								}
								
								$click="style=cursor:pointer;color:blue; onclick=getDetail('".$kegiatan."','".$blok."','".$prd."','bi')";
								$click.=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$kegiatan."','".$blok."','".$prd."','bi','real')\"";
								
								
								$tab.="<td ".$hidebi." align=right ".$click.">".hidezerodecimal($realbyybi[$blok][$kegiatan],$numberformat)."</td>";
								$strealbyybi[$blok]+=$realbyybi[$blok][$kegiatan];
								$strealbyybidiv[$divisi][$kegiatan]+=$realbyybi[$blok][$kegiatan];
								$sterealbyybidiv[$estate][$kegiatan]+=$realbyybi[$blok][$kegiatan];
								
								$gtrealbyybidiv[$kegiatan]+=$realbyybi[$blok][$kegiatan];
								$gterealbyybidiv[$kegiatan]+=$realbyybi[$blok][$kegiatan];
							}
						}
						$color="";
						if($strealbyybi[$blok]>0 and $prdrealbi[$blok]<='0'){
							$color="style=background-color:orange;";
						}
						$tab.="<td ".$hidebi." ".$color." align=right>".hidezerodecimal($strealbyybi[$blok],$numberformat)."</td>";
						
						#subtotal perdivisi
						$stluas[$divisi]+=$luas[$blok];
						$stpokok[$divisi]+=$pokok[$blok];
						$stprdbgtbi[$divisi]+=$prdbgtbi[$blok];
						$stprdrealbi[$divisi]+=$prdrealbi[$blok];
						# st estate
						$steluas[$estate]+=$luas[$blok];
						$stepokok[$estate]+=$pokok[$blok];
						$steprdbgtbi[$estate]+=$prdbgtbi[$blok];
						$steprdrealbi[$estate]+=$prdrealbi[$blok];
						
						# gt
						$gteluas+=$luas[$blok];
						$gtepokok+=$pokok[$blok];
						$gteprdbgtbi+=$prdbgtbi[$blok];
						$gteprdrealbi+=$prdrealbi[$blok];
						
						#sampa dengan bulan ini
						$tab.="<td align=right>".hidezerodecimal($prdbgtsdbi[$blok])."</td>";
						$tab.="<td align=right>".hidezerodecimal($prdrealsdbi[$blok])."</td>";
						$tab.="<td align=right>".@hidezerodecimal(@$prdrealsdbi[$blok]/@$prdbgtsdbi[$blok]*100,2)."</td>";
						$tab.="<td align=right>".@hidezerodecimal(@$prdbgtsdbi[$blok]/@$luas[$blok]/1000,2)."</td>";
						$tab.="<td align=right>".@hidezerodecimal(@$prdrealsdbi[$blok]/@$luas[$blok]/1000,2)."</td>";
						foreach($listakun as $noakun => $val1){
							foreach($val1 as $kegiatan){
								
								$click=$adacomment=""; $flag=0;
								$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
								if(!empty($showcomment[$blok][$kegiatan]['sdbi'])){
									$adacomment="class=has_sign"; $flag='1';
									$title=" title='".getKary($showcomment[$blok][$kegiatan]['sdbi'][0]['user'])."\n".$showcomment[$blok][$kegiatan]['sdbi'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
								}
								
								$click="style=cursor:pointer;color:blue; onclick=getDetail('".$kegiatan."','".$blok."','".$prd."','sdbi')";
								$click.=$title."  ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$kegiatan."','".$blok."','".$prd."','sdbi','real')\"";
								
								
								$tab.="<td align=right ".$click.">".hidezerodecimal($realbyysdbi[$blok][$kegiatan],$numberformat)."</td>";
								$strealbyysdbi[$blok]+=$realbyysdbi[$blok][$kegiatan];
								$strealbyysdbidiv[$divisi][$kegiatan]+=$realbyysdbi[$blok][$kegiatan];
								$sterealbyysdbidiv[$estate][$kegiatan]+=$realbyysdbi[$blok][$kegiatan];
								
								$gtrealbyysdbidiv[$kegiatan]+=$realbyysdbi[$blok][$kegiatan];
								$gterealbyysdbidiv[$kegiatan]+=$realbyysdbi[$blok][$kegiatan];
							}	
						}
						$color="";
						if($strealbyysdbi[$blok]>0 and $prdrealsdbi[$blok]<='0'){
							$color="style=background-color:orange;";
						}
						$tab.="<td ".$color." align=right>".hidezerodecimal($strealbyysdbi[$blok],$numberformat)."</td>";
						# sub total sdbi
						$stprdbgtsdbi[$divisi]+=$prdbgtsdbi[$blok];
						$stprdrealsdbi[$divisi]+=$prdrealsdbi[$blok];
						# sub total sdbi estatet
						$steprdbgtsdbi[$estate]+=$prdbgtsdbi[$blok];
						$steprdrealsdbi[$estate]+=$prdrealsdbi[$blok];
						
						# grand total sdbi
						$gteprdbgtsdbi+=$prdbgtsdbi[$blok];
						$gteprdrealsdbi+=$prdrealsdbi[$blok];
						
						#tahunan
						$tab.="<td align=right>".hidezerodecimal($prdbgtthn[$blok])."</td>";
						$tab.="<td align=right>".hidezerodecimal($prdrealsdbi[$blok])."</td>";
						$tab.="<td align=right>".@hidezerodecimal(@$prdrealsdbi[$blok]/@$prdbgtthn[$blok]*100,2)."</td>";
						$tab.="<td align=right>".@hidezerodecimal(@$prdbgtthn[$blok]/@$luas[$blok]/1000,2)."</td>";
						$tab.="<td align=right>".@hidezerodecimal(@$prdrealsdbi[$blok]/@$luas[$blok]/1000,2)."</td>";
						$tab.="</tr>";
						
						#sub total tahun
						$stprdbgtthn[$divisi]+=$prdbgtthn[$blok];
						$steprdbgtthn[$estate]+=$prdbgtthn[$blok];
						
						#grand total tahun
						$gtprdbgtthn+=$prdbgtthn[$blok];
						$gteprdbgtthn+=$prdbgtthn[$blok];
						
						$listdivisi[$divisi]=$divisi;
					} #tutup if case
				}
				if($listdivisi[$divisi]!=''){				
					$tab.="<tr class=rowcontent style=background-color:#dcfcf2>";
					$tab.="<td colspan=2></td>";
					$tab.="<td colspan=3>Sub Total ".getNamaOrg($divisi)."</td>";
					$tab.="<td align=right>".hidezerodecimal($stluas[$divisi],2)."</td>";
					$tab.="<td align=right>".hidezerodecimal($stpokok[$divisi])."</td>";
					$tab.="<td align=right>".@hidezerodecimal(@$stpokok[$divisi]/@$stluas[$divisi],2)."</td>";	
					#bi
					$tab.="<td ".$hidebi." align=right>".hidezerodecimal($stprdbgtbi[$divisi])."</td>";
					$tab.="<td ".$hidebi." align=right>".hidezerodecimal($stprdrealbi[$divisi])."</td>";
					$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$stprdrealbi[$divisi]/@$stprdbgtbi[$divisi]*100,2)."</td>";
					$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$stprdbgtbi[$divisi]/@$stluas[$divisi]/1000,2)."</td>";
					$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$stprdrealbi[$divisi]/@$stluas[$divisi]/1000,2)."</td>";
					foreach($listakunbi as $noakun => $val1){
						foreach($val1 as $kegiatan){
							$tab.="<td ".$hidebi." align=right>".hidezerodecimal($strealbyybidiv[$divisi][$kegiatan],$numberformat)."</td>";
							$strealbyybidiv_k[$divisi]+=$strealbyybidiv[$divisi][$kegiatan];
						}
					}
					$color="";
					if($strealbyybidiv_k[$divisi]>0 and $stprdrealbi[$divisi]<='0'){
						$color="style=background-color:orange;";
					}
					$tab.="<td ".$hidebi." ".$color." align=right>".hidezerodecimal($strealbyybidiv_k[$divisi],$numberformat)."</td>";
					#sdbi
					$tab.="<td align=right>".hidezerodecimal($stprdbgtsdbi[$divisi])."</td>";
					$tab.="<td align=right>".hidezerodecimal($stprdrealsdbi[$divisi])."</td>";
					$tab.="<td align=right>".@hidezerodecimal(@$stprdrealsdbi[$divisi]/@$stprdbgtsdbi[$divisi]*100,2)."</td>";
					$tab.="<td align=right>".@hidezerodecimal(@$stprdbgtsdbi[$divisi]/@$stluas[$divisi]/1000,2)."</td>";
					$tab.="<td align=right>".@hidezerodecimal(@$stprdrealsdbi[$divisi]/@$stluas[$divisi]/1000,2)."</td>";
					foreach($listakun as $noakun => $val1){
						foreach($val1 as $kegiatan){
							$tab.="<td align=right>".hidezerodecimal($strealbyysdbidiv[$divisi][$kegiatan],$numberformat)."</td>";
							$strealbyysdbidiv_k[$divisi]+=$strealbyysdbidiv[$divisi][$kegiatan];
						}	
					}
					$color="";
					if($strealbyysdbidiv_k[$divisi]>0 and $stprdrealsdbi[$divisi]<='0'){
						$color="style=background-color:orange;";
					}
					$tab.="<td ".$color." align=right>".hidezerodecimal($strealbyysdbidiv_k[$divisi],$numberformat)."</td>";
					
					#tahun
					$tab.="<td align=right>".hidezerodecimal($stprdbgtthn[$divisi])."</td>";
					$tab.="<td align=right>".hidezerodecimal($stprdrealsdbi[$divisi])."</td>";
					$tab.="<td align=right>".@hidezerodecimal(@$stprdrealsdbi[$divisi]/@$stprdbgtthn[$divisi]*100,2)."</td>";
					$tab.="<td align=right>".@hidezerodecimal(@$stprdbgtthn[$divisi]/@$stluas[$divisi]/1000,2)."</td>";
					$tab.="<td align=right>".@hidezerodecimal(@$stprdrealsdbi[$divisi]/@$stluas[$divisi]/1000,2)."</td>";
					
					$daftarkebun[substr($divisi,0,4)]=substr($divisi,0,4);
					$tab.="</tr>";
				}
			}
			if($daftarkebun[$estate]!=''){				
				$tab.="<tr class=rowcontent style=background-color:#9afcde>";
				$tab.="<td></td>";
				$tab.="<td colspan=4>Sub Total ".getNamaOrg($estate)."</td>";
				$tab.="<td align=right>".hidezerodecimal($steluas[$estate],2)."</td>";
				$tab.="<td align=right>".hidezerodecimal($stepokok[$estate])."</td>";
				$tab.="<td align=right>".@hidezerodecimal(@$stepokok[$estate]/@$steluas[$estate],2)."</td>";	
				#bi
				$tab.="<td ".$hidebi." align=right>".hidezerodecimal($steprdbgtbi[$estate])."</td>";
				$tab.="<td ".$hidebi." align=right>".hidezerodecimal($steprdrealbi[$estate])."</td>";
				$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$steprdrealbi[$estate]/@$steprdbgtbi[$estate]*100,2)."</td>";
				$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$steprdbgtbi[$estate]/@$steluas[$estate]/1000,2)."</td>";
				$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$steprdrealbi[$estate]/@$steluas[$estate]/1000,2)."</td>";
				foreach($listakunbi as $noakun => $val1){
					foreach($val1 as $kegiatan){
						$tab.="<td ".$hidebi." align=right>".hidezerodecimal($sterealbyybidiv[$estate][$kegiatan],$numberformat)."</td>";
						$sterealbyybidiv_k[$estate]+=$sterealbyybidiv[$estate][$kegiatan];
					}
				}
				$color="";
				if($sterealbyybidiv_k[$estate]>0 and $steprdrealbi[$estate]<='0'){
					$color="style=background-color:orange;";
				}
				$tab.="<td ".$hidebi." ".$color." align=right>".hidezerodecimal($sterealbyybidiv_k[$estate],$numberformat)."</td>";
				#sdbi
				$tab.="<td align=right>".hidezerodecimal($steprdbgtsdbi[$estate])."</td>";
				$tab.="<td align=right>".hidezerodecimal($steprdrealsdbi[$estate])."</td>";
				$tab.="<td align=right>".@hidezerodecimal(@$steprdrealsdbi[$estate]/@$steprdbgtsdbi[$estate]*100,2)."</td>";
				$tab.="<td align=right>".@hidezerodecimal(@$steprdbgtsdbi[$estate]/@$steluas[$estate]/1000,2)."</td>";
				$tab.="<td align=right>".@hidezerodecimal(@$steprdrealsdbi[$estate]/@$steluas[$estate]/1000,2)."</td>";
				foreach($listakun as $noakun => $val1){
					foreach($val1 as $kegiatan){
						$tab.="<td align=right>".hidezerodecimal($sterealbyysdbidiv[$estate][$kegiatan],$numberformat)."</td>";
						$sterealbyysdbidiv_k[$estate]+=$sterealbyysdbidiv[$estate][$kegiatan];
					}
				}
				$color="";
				if($sterealbyysdbidiv_k[$estate]>0 and $steprdrealsdbi[$estate]<='0'){
					$color="style=background-color:orange;";
				}
				$tab.="<td ".$color." align=right>".hidezerodecimal($sterealbyysdbidiv_k[$estate],$numberformat)."</td>";
				
				#tahun
				$tab.="<td align=right>".hidezerodecimal($steprdbgtthn[$estate])."</td>";
				$tab.="<td align=right>".hidezerodecimal($steprdrealsdbi[$estate])."</td>";
				$tab.="<td align=right>".@hidezerodecimal(@$steprdrealsdbi[$estate]/@$steprdbgtthn[$estate]*100,2)."</td>";
				$tab.="<td align=right>".@hidezerodecimal(@$steprdbgtthn[$estate]/@$steluas[$estate]/1000,2)."</td>";
				$tab.="<td align=right>".@hidezerodecimal(@$steprdrealsdbi[$estate]/@$steluas[$estate]/1000,2)."</td>";
				$tab.="</tr>";	
			}
		}
	$tab.="<tr class=rowcontent style=background-color:#17A589>";
	$tab.="<td colspan=5>GRAND TOTAL</td>";
	$tab.="<td align=right>".hidezerodecimal($gteluas,2)."</td>";
	$tab.="<td align=right>".hidezerodecimal($gtepokok)."</td>";
	$tab.="<td align=right>".@hidezerodecimal(@$gtepokok/@$gteluas,2)."</td>";	
	#bi
	$tab.="<td ".$hidebi." align=right>".hidezerodecimal($gteprdbgtbi)."</td>";
	$tab.="<td ".$hidebi." align=right>".hidezerodecimal($gteprdrealbi)."</td>";
	$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$gteprdrealbi/@$gteprdbgtbi*100,2)."</td>";
	$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$gteprdbgtbi/@$gteluas/1000,2)."</td>";
	$tab.="<td ".$hidebi." align=right>".@hidezerodecimal(@$gteprdrealbi/@$gteluas/1000,2)."</td>";
	foreach($listakunbi as $noakun => $val1){
		foreach($val1 as $kegiatan){
			$tab.="<td ".$hidebi." align=right>".hidezerodecimal($gterealbyybidiv[$kegiatan],$numberformat)."</td>";
			$gterealbyybidiv_k+=$gterealbyybidiv[$kegiatan];
		}
	}
	$color="";
	if($gterealbyybidiv_k>0 and $gteprdrealbi<='0'){
		$color="style=background-color:orange;";
	}
	$tab.="<td ".$hidebi." ".$color." align=right>".hidezerodecimal($gterealbyybidiv_k,$numberformat)."</td>";
	#sdbi
	$tab.="<td align=right>".hidezerodecimal($gteprdbgtsdbi)."</td>";
	$tab.="<td align=right>".hidezerodecimal($gteprdrealsdbi)."</td>";
	$tab.="<td align=right>".@hidezerodecimal(@$gteprdrealsdbi/@$gteprdbgtsdbi*100,2)."</td>";
	$tab.="<td align=right>".@hidezerodecimal(@$gteprdbgtsdbi/@$gteluas/1000,2)."</td>";
	$tab.="<td align=right>".@hidezerodecimal(@$gteprdrealsdbi/@$gteluas/1000,2)."</td>";
	foreach($listakun as $noakun => $val1){
		foreach($val1 as $kegiatan){
			$tab.="<td align=right>".hidezerodecimal($gterealbyysdbidiv[$kegiatan],$numberformat)."</td>";
			$gterealbyysdbidiv_k+=$gterealbyysdbidiv[$kegiatan];
		}
	}
	$color="";
	if($gterealbyysdbidiv_k>0 and $gteprdrealsdbi<='0'){
		$color="style=background-color:orange;";
	}
	$tab.="<td ".$color." align=right>".hidezerodecimal($gterealbyysdbidiv_k,$numberformat)."</td>";
	
	#tahun
	$tab.="<td align=right>".hidezerodecimal($gteprdbgtthn)."</td>";
	$tab.="<td align=right>".hidezerodecimal($gteprdrealsdbi)."</td>";
	$tab.="<td align=right>".@hidezerodecimal(@$gteprdrealsdbi/@$gteprdbgtthn*100,2)."</td>";
	$tab.="<td align=right>".@hidezerodecimal(@$gteprdbgtthn/@$gteluas/1000,2)."</td>";
	$tab.="<td align=right>".@hidezerodecimal(@$gteprdrealsdbi/@$gteluas/1000,2)."</td>";
	$tab.="</tr>";		
		
	$tab.="</tbody></table>";

	if($proses=='preview'){
		echo $tab;
	}elseif($proses=='excel'){
		$nop = "prdvsbyy.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("prdvsbyy", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
	}
	break;
	case'getdetail':
		
		$lmto = ['labor','material','transport','other'];
	
		$tab = "<table class=sortable cellpadding=5 cellspacing=1>";
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
		
		$str = "select *  from " . $dbnamerpt . ".keu_5parameterjurnal";
		$res = fetchData($str);
		foreach($res as $bar){
			$nmkelompok[$bar['jurnalid']]=$bar['keterangan'];
		}
		if($tipe=='bi'){
			$where=" and periode = '".$prd."'";
		}else{
			$where=" and periode between '".$periode1."' and  '".$periode2."'";
		}
		if($param['sumber']=='kebun_consolbyyproduksi'){
			if(strlen($param['kegiatan'])>7){
				$where.=" and noakun like '".substr($param['kegiatan'],0,7)."%' and kodekegiatan like '".$param['kegiatan']."%' and kodeorg = '".$param['blok']."'";
			}else{				
				$where.=" and noakun like '".$param['kegiatan']."%' and kodeorg = '".$param['blok']."'";
			}
		}else{
			$where.=" and noakun like '".substr($param['kegiatan'],0,3)."%' and kodekegiatan='".$param['kegiatan']."' and kodeblok='".$param['blok']."'";
		}
		
		
		$noreff=[];
		$str = "select *  from " . $dbnamerpt . ".keu_jurnaldt_vw a  where 1=1 ".$where." order by periode asc, noreferensi asc, kodejurnal asc, tanggal asc";
		$res = fetchData($str);
		foreach($res as $bar){
			$noreff[$bar['noreferensi']]=$bar['noreferensi'];
		}
		
		$sql = "select *  from " . $dbnamerpt . ".kebun_aktifitas where notransaksi in ('".implode("','",$noreff)."')";
		$req = fetchData($sql);
		foreach($req as $val){
			if($val['tipetransaksi']!='PNN'){				
				$notransbkm[$val['notransaksi']]='BKM';
			}else{
				$notransbkm[$val['notransaksi']]='PNNJJG';
			}
		}

		$sql = "select distinct notransaksi  from " . $dbnamerpt . ".kebun_3premipemanen where blok like '".$param['blok']."%' and notransaksi in ('".implode("','",$noreff)."')";
		$req = fetchData($sql);
		foreach($req as $val){
			$notransbkm[$val['notransaksi']]='PNNKG';
		}
		// EXIT("error".$str);
		$subtotal=[];
		foreach($res as $bar){
			$periode = substr($bar['tanggal'],0,7);
			if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC' and substr($bar['kodejurnal'],0,3)!='SPK'){
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
		
		// echo"<pre>";
		// print_r($gtotal);
		// echo"</pre>";
		
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
			$tab.="<tr class=rowcontent style=display:none; name=".$d."[]>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['nojurnal']."</td>";
			$tab.="<td>".$bar['kodejurnal']."</td>";
			$tab.="<td>".$nmkelompok[$bar['kodejurnal']]."</td>";
			$tab.="<td nowrap>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td>".$bar['keterangan']."</td>";
			$rupiah=[];
			if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC' and substr($bar['kodejurnal'],0,3)!='SPK'){
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
		
		echo $tab;
	break;
	case'detailpnnkg':
		
		$listkary=array();
		$basisk=$rplb=$rpbr=$rptop=$arrtop=$jlhtop=array();
		$listkary=$hk=$jjgpanen=$kgwb=$basiskg=$kglb1=$rplb1=$kglb2=$rplb2=$kgbrd=$rpbrd=$denda=$kehadiran=array();
		$thk=$tjjgpanen=$tkgwb=$tbasiskg=$tkglb1=$trplb1=$tkglb2=$trplb2=$tkgbrd=$trpbrd=$tdenda=$tkehadiran=$ttotal=array();


		# ambil data
		$str="select * from ".$dbname.".kebun_3premipemanen where notransaksi='".$param['notransaksi']."' limit 1"; #and periode='".$prd."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$blok  = $bar['divisi'];
			$divisi= $bar['divisi'];
			$prd   = $bar['periode'];
		}
		
		#ambil basis wb
		$str="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$divisi."' and tahun='".$prd."'";
		$res = fetchData($str);
		foreach($res as $bar){
			$basiskg[$bar['tahuntanam']][$bar['topografi']]=$bar['basis'];
			$rplb1[$bar['tahuntanam']][$bar['topografi']]=$bar['premilebihbasis'];
			$rpbrd[$bar['tahuntanam']][$bar['topografi']]=$bar['premibrondolan'];
			$rptopo[$bar['tahuntanam']][$bar['topografi']]=$bar['premitopografi'];
			$arrtopo[$bar['tahuntanam']][$bar['topografi']]=$bar['topografi'];
			$jlhtopo[$bar['topografi']]=$bar['topografi'];
		}

		$stream='<label>Panen TBS (Kg)</label>';
		if ($tipe == 'excel') {
			$stream.="<table class=sortable cellspacing=1 border=1>";
		} else 	{
			$stream.="<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
		}
		
		$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.="<td align=center rowspan=2>No</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['mandor']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kerani']."</td>";
			$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahuntanam']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nik2']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
			$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['topografi']."</td>";
			//$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['hk']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>";
			$stream.="<td align=center rowspan=2>Total Kg</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['basic']." Kg</td>";
			$stream.="<td align=center colspan=2>".$_SESSION['lang']['premi']."</td>";
			$stream.="<td align=center colspan=2>".$_SESSION['lang']['brondol']."</td>";
			$stream.="<td align=center rowspan=2>Kehadiran</td>";
			$stream.="<td align=center rowspan=2>Tambahan</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['denda']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>";
		$stream.="</tr>";
		$stream.="<tr>";
			$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
		$stream.="</tr>";
		$stream.="</thead><tbody>";
		
		# ambil data
		$str="select a.*, b.namakaryawan from ".$dbname.".kebun_3premipemanen a left join datakaryawan b 
		on a.karyawanid=b.karyawanid where a.notransaksi ='".$param['notransaksi']."' and a.karyawanid='".$param['karyawanid']."' and blok='".$param['blok']."' and jurnal='".$param['nojurnal']."' order by a.mandor asc, a.tahuntanam asc, b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$listkary[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]=$bar['status'];
			@$hk[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['hk'];
			@$jjgpanen[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['jjgpanen'];
			@$kgwb[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kgwb'];
			@$basiskg[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['basiskg'];
			@$kglb1[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kglb1'];
			@$rplb1[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rplb1'];
			@$kglb2[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kglb2'];
			@$rplb2[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rplb2'];
			@$kgbrd[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kgbrd'];
			@$rpbrd[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rpbrd'];
			@$denda[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['denda'];
			@$kehadiran[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kehadiran'];
			@$pretambahan[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['tambahan'];
		}
		$no='';
		$optTopografi =makeOption($dbname,'setup_topografi','topografi,keterangan');
		foreach($listkary as $mdr => $val){
			foreach($val as $krn => $key){
				foreach($key as $tt => $key1){
					foreach($key1 as $kary => $key2){
						foreach($key2 as $status){
							$no++;
							$stream.="<tr class=rowcontent>";
							$stream.="<td align=right>".$no."</td>";
							$stream.="<td align=left>".@getNamaKaryawan($mdr)."</td>";
							$stream.="<td align=left>".@getNamaKaryawan($krn)."</td>";
							$stream.="<td align=center>".$tt."</td>";
							$stream.="<td align=center>".getKary($kary,'nik')."</td>";
							$stream.="<td align=left>".@getNamaKaryawan($kary)."</td>";
							$stream.="<td align=left>".$optTopografi[$status]."</td>";
							//$stream.="<td align=right>".@hidezerodecimal($hk[$mdr][$krn][$tt][$kary][$status],1)."</td>";
							$stream.="<td align=right>".@hidezerodecimal($jjgpanen[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($kgwb[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($basiskg[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($kglb1[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($rplb1[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($kgbrd[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($rpbrd[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($kehadiran[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($pretambahan[$mdr][$krn][$tt][$kary][$status])."</td>";
							$stream.="<td align=right>".@hidezerodecimal($denda[$mdr][$krn][$tt][$kary][$status])."</td>";
							$total=(($rplb1[$mdr][$krn][$tt][$kary][$status]+$rplb2[$mdr][$krn][$tt][$kary][$status]+$rpbrd[$mdr][$krn][$tt][$kary][$status] +$kehadiran[$mdr][$krn][$tt][$kary][$status]+$pretambahan[$mdr][$krn][$tt][$kary][$status])-$denda[$mdr][$krn][$tt][$kary][$status]);
							$stream.="<td align=right>".@hidezerodecimal($total)."</td>";
							$stream.="</tr>";
							@$thk[$mdr]+=$hk[$mdr][$krn][$tt][$kary][$status];
							@$tjjgpanen[$mdr]+=$jjgpanen[$mdr][$krn][$tt][$kary][$status];
							@$tkgwb[$mdr]+=$kgwb[$mdr][$krn][$tt][$kary][$status];
							@$tbasiskg[$mdr]+=$basiskg[$mdr][$krn][$tt][$kary][$status];
							@$tkglb1[$mdr]+=$kglb1[$mdr][$krn][$tt][$kary][$status];
							@$trplb1[$mdr]+=$rplb1[$mdr][$krn][$tt][$kary][$status];
							@$tkglb2[$mdr]+=$kglb2[$mdr][$krn][$tt][$kary][$status];
							@$trplb2[$mdr]+=$rplb2[$mdr][$krn][$tt][$kary][$status];
							@$tkgbrd[$mdr]+=$kgbrd[$mdr][$krn][$tt][$kary][$status];
							@$trpbrd[$mdr]+=$rpbrd[$mdr][$krn][$tt][$kary][$status];
							@$tdenda[$mdr]+=$denda[$mdr][$krn][$tt][$kary][$status];
							@$tkehadiran[$mdr]+=$kehadiran[$mdr][$krn][$tt][$kary][$status];
							@$ttambahan[$mdr]+=$pretambahan[$mdr][$krn][$tt][$kary][$status];
							@$ttotal[$mdr]+=$total;
						}
					}
				}
			}		
				$stream.="<tr class=rowcontent>";
				$stream.="<td colspan=7 bgcolor='cyan'>Sub Total Kemandoran ".getNamaKaryawan($mdr)."</td>";
				//$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($thk[$mdr],1)."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tjjgpanen[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgwb[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tbasiskg[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkglb1[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trplb1[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgbrd[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trpbrd[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkehadiran[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($ttambahan[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tdenda[$mdr])."</td>";
				$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($ttotal[$mdr])."</td>";
				$stream.="</tr>";
				@$gthk+=$thk[$mdr];
				@$gtjjgpanen+=$tjjgpanen[$mdr];
				@$gtkgwb+=$tkgwb[$mdr];
				@$gtbasiskg+=$tbasiskg[$mdr];
				@$gtkglb1+=$tkglb1[$mdr];
				@$gtrplb1+=$trplb1[$mdr];
				@$gtkglb2+=$tkglb2[$mdr];
				@$gtrplb2+=$trplb2[$mdr];
				@$gtkgbrd+=$tkgbrd[$mdr];
				@$gtrpbrd+=$trpbrd[$mdr];
				@$gtdenda+=$tdenda[$mdr];
				@$gtkehadiran+=$tkehadiran[$mdr];
				@$gttambahan+=$ttambahan[$mdr];
				@$gttotal+=$ttotal[$mdr];
		}	
		$stream.="<tr class=rowcontent>";
		$stream.="<td bgcolor='cyan' colspan=7>Grand Total</td>";
		//$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gthk)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtjjgpanen)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkgwb)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtbasiskg)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkglb1)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtrplb1)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkgbrd)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtrpbrd)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkehadiran)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gttambahan)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtdenda)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gttotal)."</td>";
		$stream.="</tr>";
		$stream.="</tbody></table>";
		
		echo $stream;
		
		// echo $tab;
	break;
	case'detailvhc':
		$tab.="<label>Nopol / Detail : ".getNopol($param['kodevhc'])."</label><br><br>";
		$tab.="<table cellspacing=1 cellpadding=5 border=0 class=sortable>
			<thead>
        	<tr class=rowheader>
				<th align=center>No.</th>
				<th align=center>".$_SESSION['lang']['notransaksi']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']." </th>
				<th align=center>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</th>
				<th align=center>".$_SESSION['lang']['keterangan']."</th>
				<th align=center>HM/KM</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['vhc_berat_muatan']."</th>
				<th align=center>".$_SESSION['lang']['jumlahrit']."</th>
				<th align=center>".$_SESSION['lang']['alokasibiaya']."</th>
				<th align=center>".$_SESSION['lang']['operator']." / ".$_SESSION['lang']['sopir']."</th>    
            </tr>
        </thead>
        <tbody>";
		
		$str = "select *  from " . $dbnamerpt . ".vhc_kegiatan";
		$res = fetchData($str);
		foreach($res as $bar){
			$optnmkeg[$bar['kodekegiatan']]=$bar['namakegiatan'];
		}
		
		$optposisi=['0'=>'Operator','1'=>'Helper','2'=>'Sopir'];
		
		$str = "select a.*,b.satuan  from " . $dbnamerpt . ".vhc_rundt_vw a  
		left join ".$dbnamerpt.".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
		left join ".$dbnamerpt.".setup_kegiatan c on b.setupkegiatan=c.kodekegiatan
		where 1=1 and (c.kodekegiatan like '".substr($param['kegiatan'],0,3)."%' or b.kodekegiatan like '".substr($param['kegiatan'],0,3)."%') 
		and (c.kodekegiatan like '".$param['kegiatan']."%' or b.kodekegiatan like '".$param['kegiatan']."%' )
		and alokasibiaya like '".$param['blok']."%' and kodevhc='".$param['kodevhc']."' and tanggal like '".substr($param['tanggal'],0,7)."%' order by notransaksi asc";
		$res = fetchData($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['notransaksi']."</td>";
			$tab.="<td nowrap>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td>".$optnmkeg[$bar['jenispekerjaan']]."</td>";
			$tab.="<td>".$bar['keterangan']."</td>";
			$tab.="<td align=right>".number_format($bar['jumlah'],2)."</td>";
			$tab.="<td>".$bar['satuan']."</td>";
			$tab.="<td align=right>".number_format($bar['beratmuatan'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['jumlahrit'],2)."</td>";
			$tab.="<td>".getNamaOrg($bar['alokasibiaya'])."</td>";
			
			$tab.="<td>";
				$tab.="<table>";
				$str = "select *  from ".$dbnamerpt.".vhc_runhk where notransaksi='".$bar['notransaksi']."'";
				$res = fetchData($str);
				$nomor='';
				foreach($res as $bar){
					$nomor++;
					$tab.="<tr>";
					$tab.="<td>".$nomor.". </td>";
					$tab.="<td>".getKary($bar['idkaryawan'])."</td>";
					$tab.="<td>".$optposisi[$bar['posisi']]."</td>";
					
					$tab.="</tr>";
				}
				$tab.="</table>";
			$tab.="</td>";
			
		}
		
		echo $tab;
	break;
	case'getdetailprdvsact':
		
		$kdorg    = checkPostGet('kodeorg', '');
		$regional = checkPostGet('regional', '');
		$periode  = checkPostGet('prd', '');
		$prd      = checkPostGet('prd', '');
		
		$arrbi    = explode('-',$prd); 
		$tahun    = $arrbi[0]; 
		$bulan    = $arrbi[1];
		$periode1 = $tahun."-01";
		$periode2 = $prd;
		$periode2 = $tahun."-12";


		if($regional!=''){
			$whreg="and subregional='".$regional."'";
		}
		
		$listkodeorg = [];
		$datakodeorg = [];

		$str="select * from ".$dbname.".bgt_regional_assignment where 1=1 ".$whreg."";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(getNamaOrg($bar['kodeunit'],'tipe')=='KEBUN' or getNamaOrg($bar['kodeunit'],'tipe')=='PABRIK'){		
				$datakodeorg[$bar['subregional']][$bar['kodeunit']]=$bar['kodeunit'];
				$listkodeorg[$bar['kodeunit']]=$bar['kodeunit'];
				$getregion[$bar['kodeunit']]=$bar['subregional'];
				$listreg[$bar['subregional']]=$bar['subregional'];		
			}
			
			if(getNamaOrg($bar['kodeunit'],'tipe')=='PABRIK'){
				$listpabrik[$bar['subregional']][$bar['kodeunit']]=$bar['kodeunit'];
			}
		}

		$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
		$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
		$whhk=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
		$whB=" and substr(a.millcode,1,4) in ('".implode("','",$listkodeorg)."')";
		if($kdorg!=''){
			$where.=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
			$where2.=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
			$whhk.=" and substr(a.unit,1,4) ='".$kdorg."'";
			$whB=" and substr(a.millcode,1,4) ='".$kdorg."'";
		}

		$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)<=4";
		$res = fetchdata($str);
		foreach($res as $bar){
			$listip[$bar['kodeorganisasi']]=$bar['inti'];		
		}

		$tab="<label>Periode : ".$param['prd']."</label><br><br>";
		$tab.="<table cellspacing=1 cellpadding=5 border=0 class=sortable>
			<thead>
        	<tr class=rowheader>
				<th align=center>No.</th>
				<th align=center>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['budget']." </th>
				<th align=center>".$_SESSION['lang']['aktual']."</th>    
            </tr>
        </thead>
        <tbody>";
		
		$str = " select * from ".$dbnamerpt.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." and tahunbudget = '".$tahun."'";
		$res = fetchData($str);
		foreach($res as $bar){
			$per = substr($periode,-2);
			if($listip[substr($bar['kodeblok'],0,4)]=='1'){			
				$data['ffbi'][$getregion[substr($bar['kodeblok'],0,4)]][substr($bar['kodeblok'],0,4)]['bgt'] += $bar['kg'.$per]/1000;
				$data['ffbttl'][$getregion[substr($bar['kodeblok'],0,4)]]['bgt'] += $bar['kg'.$per]/1000;
			}
			if($listip[substr($bar['kodeblok'],0,4)]=='0'){			
				$data['ffbp'][$getregion[substr($bar['kodeblok'],0,4)]][substr($bar['kodeblok'],0,4)]['bgt'] += $bar['kg'.$per]/1000;
				$data['ffbttl'][$getregion[substr($bar['kodeblok'],0,4)]]['bgt'] += $bar['kg'.$per]/1000;
			}
		}
		
		$str = " select * from ".$dbnamerpt.".bgt_produksi_pks_vw a where 1=1 ".$whB." and tahunbudget = '".$tahun."'";
		$res = fetchData($str);
		foreach($res as $bar){
			$per = substr($periode,-2);
			if($bar['kodeunit']=='tbsexternal'){		
				$data['ffbswa'][$getregion[substr($bar['millcode'],0,4)]][$bar['kodesupplier']]['bgt'] += $bar['olah'.$per]/1000;
				$data['ffbttl'][$getregion[substr($bar['kodeblok'],0,4)]]['bgt'] += $bar['kg'.$per]/1000;
			}
			$data['cpo'][$getregion[substr($bar['millcode'],0,4)]][substr($bar['millcode'],0,4)]['bgt'] += $bar['kgcpo'.$per]/1000;
			$data['pk'][$getregion[substr($bar['millcode'],0,4)]][substr($bar['millcode'],0,4)]['bgt'] += $bar['kgker'.$per]/1000;
			$data['pp'][$getregion[substr($bar['millcode'],0,4)]][substr($bar['millcode'],0,4)]['bgt'] += ($bar['kgcpo'.$per]+$bar['kgker'.$per])/1000;
		}
		
		$str = "select sum(beratbersih) as kg,count(notransaksi) as rit, kodeorg,millcode,divcode,pengirim,namatransportir,substr(tanggal,1,7) as periode from ".$dbnamerpt.".pabrik_timbangan a where tanggal like '".$periode."%' and kodebarang='40000003' ".$whB." group by millcode,kodeorg, substr(tanggal,1,7)";
		$res=fetchData($str);
		foreach($res as $bar){
			if($listip[substr($bar['kodeorg'],0,4)]=='1'){			
				$data['ffbi'][$getregion[substr($bar['kodeorg'],0,4)]][substr($bar['kodeorg'],0,4)]['act'] += $bar['kg']/1000;
			}elseif($listip[substr($bar['kodeorg'],0,4)]=='0'){	
				$data['ffbp'][$getregion[substr($bar['kodeorg'],0,4)]][substr($bar['kodeorg'],0,4)]['act'] += $bar['kg']/1000;
			}else{
				$data['ffbswa'][$getregion[substr($bar['kodeorg'],0,4)]][substr($bar['kodeorg'],0,4)]['act'] += $bar['kg']/1000;
			}
			$data['ffbttl'][$getregion[substr($bar['kodeorg'],0,4)]]['act'] += $bar['kg']/1000;
		}

		$str = "select sum(tbsmasuk) as tbsmasuk, sum(tbsdiolah) as tbsdiolah, sum(oer) as cpo,sum(oerpk) as pk, kodeorg,substr(tanggal,1,7) as periode from ".$dbnamerpt.".pabrik_produksi a where tanggal like '".$periode."%' ".$where." group by kodeorg, substr(tanggal,1,7)";
		$res = fetchData($str);
		foreach($res as $bar){
			$data['cpo'][$getregion[substr($bar['kodeorg'],0,4)]][substr($bar['kodeorg'],0,4)]['act'] += $bar['cpo']/1000;
			$data['pk'][$getregion[substr($bar['kodeorg'],0,4)]][substr($bar['kodeorg'],0,4)]['act'] += $bar['pk']/1000;
		}
		
		foreach($listpabrik as $reg => $val1){
			foreach($val1 as $mill){
				$data['oer'][$reg][$mill]['bgt']=$data['cpo'][$reg][$mill]['bgt']/$data['ffbttl'][$reg]['bgt']*100;
			}
		}
		
		foreach($data[$param['code']] as $reg => $val1){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=4>".$reg."</td>";
			$no=0;
			foreach($val1 as $kodeorg => $jenis){
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				if(getNamaSupplier($kodeorg)!=''){					
					$tab.="<td>".getNamaSupplier($kodeorg)."</td>";
				}else{					
					$tab.="<td>".$kodeorg." - ".getNamaOrg($kodeorg)."</td>";
				}
				
				$tab.="<td align=right>".numb_format($jenis['bgt'],2)."</td>";
				$tab.="<td align=right>".numb_format($jenis['act'],2)."</td>";
				$tab.="</tr>";
				
				$total[$reg]['bgt']+=$jenis['bgt'];
				$total[$reg]['act']+=$jenis['act'];
				$gt['bgt']+=$jenis['bgt'];
				$gt['act']+=$jenis['act'];
			}	
			$tab.="<tr class=rowcontent style=font-weight:bold;>";
			$tab.="<td colspan=2>TOTAL ".$reg."</td>";
			$tab.="<td align=right>".numb_format($total[$reg]['bgt'],2)."</td>";
			$tab.="<td align=right>".numb_format($total[$reg]['act'],2)."</td>";
			$tab.="</tr>";
		}	
		
		$tab.="<tr class=rowcontent style=font-weight:bold;>";
		$tab.="<td colspan=2>GRAND TOTAL</td>";
		$tab.="<td align=right>".numb_format($gt['bgt'],2)."</td>";
		$tab.="<td align=right>".numb_format($gt['act'],2)."</td>";
		$tab.="</tr>";
		
		// echo"<pre>";
		// print_r($data);
		// echo"</pre>";

		
		echo $tab;
	break;
	case'getBlokBig':
		$str = "select distinct indukblok from " . $dbname . ".setup_blok where status='A' and statusblok='TM' and kodeorg like '".$divisi."%' and tahuntanam ='".$tt."' order by indukblok asc ";
		#exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optBlok.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$optBlok.="<option value='" . $bar['indukblok'] . "'>" . $bar['indukblok'] . " - ".getIndukBlok($bar['indukblok'])."</option>";
		}
	echo $optBlok;
	break;
}
function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
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