<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$proses= checkPostGet('proses', '');
$prd   = checkPostGet('prd', '');
$pt    = checkPostGet('pt', '');
$tipe  = checkPostGet('tipe', '');

$arrbi   = explode('-',$prd); 
$tahun   = $arrbi[0]; 
$bulan   = $arrbi[1];
$periode1= $tahun."-01";
$periode2= $tahun."-12";
$periode2= $prd;

$arrjenis=array(
	'sdmgaji'        =>'SDM - Proses - Gaji (Gaji Bulanan dan Harian)',
	'sdmdet#gjpb'    =>'<b>SDM - Proses - Penggajian Bulanan</b>',
	'sdmdet#gjph'    =>'<b>SDM - Proses - Penggajian Harian</b>',
	'sdmdet#gjbkm'   =>'Kebun - Trans - Buku Kegiatan Mandor',
	'sdmdet#gjpnn'   =>'Kebun - Trans - Kegiatan Panen',
	'sdmdet#gjprepnn'=>'Kebun - Proses - Premi Pemanen',
	'sdmdet#gjpremdr'=>'Kebun - Proses - Premi Kemandoran',
	'sdmdet#gjkeg'   =>'Traksi - Trans - Kegiatan',
	'sdmdet#gjabs'   =>'SDM - Trans - Absensi',
	'sdmdet#gjlbr'   =>'SDM - Trans - Lembur',
	'sdmdet#gjpot'   =>'SDM - Trans - Potongan',
	'sdmdet#gjpln'   =>'SDM - Proses - Pendapatan Lain',
	'sdmdet#gjumt'   =>'SDM - Proses - Extra Fooding, UM, Trans',
	'sdtgjcl'        =>'SDM - Setup - Periode Gaji Unit (Tutup Prd Gaji)',
	'sdtdet#gjclb'   =>'Tutup Gaji Bulanan',
	'sdtdet#gjclh'   =>'Tutup Gaji Harian',
	'sdpph21'        =>'SDM - Proses - Pph21',
	'gudang'         =>'Pengadaan - Proses - Tutup Fisik (Tutup Gudang)',
	'aresta'         =>'Kebun - Proses - Tutup Areal Statement',
	'kasbank'        =>'Keu - Proses - Tutup Kas Bank',
	'keugjbtl'    	 =>'Keu - Proses - Akhir Bulan [<b>1. Gaji Karyawan Tidak Langsung - (RO,Kebun,PKS)</b>]',
	'keugjbtlidletrk'=>'Keu - Proses - Akhir Bulan [<b>2. Gaji Karyawan Tidak Langsung Traksi Idle</b>]',
	'keugjbtltrk'    =>'Keu - Proses - Akhir Bulan [<b>3. Gaji Karyawan Tidak Langsung - (Traksi,Workshop)</b>]',
	'keugjl'         =>'Keu - Proses - Akhir Bulan [<b>4. Gaji Karyawan Belum Teralokasi</b>]',
	'keugjpot'       =>'Keu - Proses - Akhir Bulan [<b>5. Penarikan Potongan Gaji Karyawan</b>]',
	'keudep'         =>'Keu - Proses - Akhir Bulan [<b>6. Depresiasi</b>]',
	'keutrk'         =>'Keu - Proses - Akhir Bulan [<b>7. Alokasi Traksi dan Bengkel</b>]',
	'ohtbm'          =>'Keu - Proses - Jurnal Biaya OH ke TBM',
	'acct'           =>'Keu - Proses - Tutup Periode Acct'
);

if($prd==''){
	exit("Warning : Periode harus diisi.");
}

$wh="";
if($pt!=''){
	$wh.=" and induk='".$pt."'";
}
if($tipe!=''){
	$wh.=" and tipe='".$tipe."'";
}

$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' ".$wh." and induk not in ('LCK','SDP')  order by induk";
$res = fetchdata($str);
foreach($res as $bar){
	$unit[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	$listunit[$bar['induk']]+=1;
	$dtpt[$bar['induk']]=$bar['induk'];
}
if($proses!='excel'){	
	$tab.="<table class=sortable cellspacing=1>";
}else{
	$tab.="<table border=1 class=sortable cellspacing=1>";
}
$tab.="
	<thead>
		<tr class=rowheader>
			<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
			<th align=center rowspan=2>Proses</th>
			";
			foreach($dtpt as $kodept){
				$tab.="<th align=center colspan=".$listunit[$kodept].">".$kodept."</th>";
			}
		
		$tab.="	</tr>";
		$tab.="	<tr class=rowheader>";
			foreach($unit as $kdorg){
				$tab.="<th align=center>".$kdorg."</th>";
			}
		$tab.="	</tr>";
	$tab.="</thead><tbody>";
	
	
	#ambil proses gaji
	$str = "select * from ".$dbname.".sdm_gaji_vw where kodeorg in ('".implode("','",$unit)."') and periodegaji='".$prd."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['idkomponen']=='42' or $bar['idkomponen']=='95'){
			$data[$bar['kodeorg']]['sdpph21']+=1;
		}else{			
			$data[$bar['kodeorg']]['sdmgaji']+=$bar['jumlah'];
		}
		if($bar['sistemgaji']=='Bulanan'){
			$bkm[$bar['kodeorg']]['sdmdet#gjpb']['all']+=$bar['jumlah'];
			$bkm[$bar['kodeorg']]['sdmdet#gjpb']['post']+=$bar['jumlah'];
		}elseif($bar['sistemgaji']=='Harian'){
			$bkm[$bar['kodeorg']]['sdmdet#gjph']['all']+=$bar['jumlah'];
			$bkm[$bar['kodeorg']]['sdmdet#gjph']['post']+=$bar['jumlah'];
		}
	}
	
	
	$str = "select * from ".$dbname.".setup_periodeakuntansi a left join ".$dbname.".keu_setup_watu_tutup b on a.periode=b.periode and a.kodeorg=b.kodeorg where  a.periode='".$prd."' and a.tutupbuku='1'";
	$res = fetchdata($str);
	foreach($res as $bar){
		if(strlen($bar['kodeorg'])==6){			
			$data[substr($bar['kodeorg'],0,4)]['gudang']=$bar['waktu']."<br>".$bar['username'];
		}else{			
			$data[$bar['kodeorg']]['acct']=$bar['waktu']."<br>".$bar['username'];
		}
	}
	
	$str = "select kodeorg, awal".substr(periodeberikut($prd),-2)." as sawal from ".$dbname.".keu_saldobank where periode='".str_replace("-","",periodeberikut($prd))."' and awal".substr(periodeberikut($prd),-2)."!='0'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$data[$bar['kodeorg']]['kasbank']+=$bar['sawal'];
	}
	
	$str = "select kodeorg, awal".substr(periodeberikut($prd),-2)." as sawal from ".$dbname.".keu_saldobulanan where periode='".str_replace("-","",periodeberikut($prd))."' and awal".substr(periodeberikut($prd),-2)."!='0' and noakun like '111%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$data[$bar['kodeorg']]['kasbank']+=$bar['sawal'];
	}
	
	$str="select * from ".$dbname.".keu_jurnalht where kodejurnal in ('KBNB0','KBNB1','KBNB2','KBNB3','KBNB4','KBNB5','KBNL0','KBNL1','KBNL2','KBNL3','PKS01','PKS02','PKS03','PKS04','PKS05','PKS06','PKS07','PKS08','SIPL1','BLK01','BLK02','BLK03','BLK04','BLK05','BLK06','BLK07','BLK08','PBK00','PBK01','PBK02','PBK03','PBK04','PBK05','RNDB0','RNDB1','RNDB2','RNDB3','RNDB4','RNDB5','GJHO0','GJHO1','GJHO2','GJHO3','GJHO4','GJHO5','GJHO6','WSG6','VHCG6','KBNB6','KBNL4','GJHO7','PKS09','PKS10','BLK09','BLK10','RNDB6','PBK07') and tanggal like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrkodeorg=explode("/",$bar['nojurnal']);
		$bar['kodeorg']=$arrkodeorg[1];
		
		$data[$bar['kodeorg']]['keugjbtl']+=$bar['totaldebet'];
	}

	$str="select * from ".$dbname.".keu_jurnalht where kodejurnal in ('VHCG7') and tanggal like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrkodeorg=explode("/",$bar['nojurnal']);
		$bar['kodeorg']=$arrkodeorg[1];
		
		$data[$bar['kodeorg']]['keugjbtlidletrk']+=$bar['totaldebet'];
	}

	$str="select * from ".$dbname.".keu_jurnalht where kodejurnal in ('VHCG0','VHCG1','VHCG2','VHCG3','VHCG4','VHCG5','WSG0','WSG1','WSG2','WSG3','WSG4','WSG5') and tanggal like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrkodeorg=explode("/",$bar['nojurnal']);
		$bar['kodeorg']=$arrkodeorg[1];
		
		$data[$bar['kodeorg']]['keugjbtltrk']+=$bar['totaldebet'];
	}
	
	$str="select * from ".$dbname.".keu_jurnalht where  tanggal like '".$prd."%' and noreferensi='ALK_GAJI_LBR'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrkodeorg=explode("/",$bar['nojurnal']);
		$bar['kodeorg']=$arrkodeorg[1];
		
		$data[$bar['kodeorg']]['keugjl']+=$bar['totaldebet'];
	}
	
	$str="select * from ".$dbname.".keu_jurnalht where  tanggal like '".$prd."%' and noreferensi like 'ALK_POT%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrkodeorg=explode("/",$bar['nojurnal']);
		$bar['kodeorg']=$arrkodeorg[1];
		
		$data[$bar['kodeorg']]['keugjpot']+=$bar['totaldebet'];
	}
	
	$str="select * from ".$dbname.".keu_jurnaldt_vw where  tanggal like '".$prd."%' and (kodejurnal like 'DPH%' or kodejurnal like 'DEP%' or kodejurnal like 'DEB%')";
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrkodeorg=explode("/",$bar['nojurnal']);
		$bar['kodeorg']=$arrkodeorg[1];
		
		$data[$bar['kodeorg']]['keudep']+=$bar['debet'];
	}
	
	$str="select * from ".$dbname.".keu_jurnalht where  tanggal like '".$prd."%' and (noreferensi like 'ALK_MAINTENANCE%' or noreferensi like 'ALK_BY_WS%' or noreferensi like 'ALK_KERJA_AB%')";
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrkodeorg=explode("/",$bar['nojurnal']);
		$bar['kodeorg']=$arrkodeorg[1];
		
		$data[$bar['kodeorg']]['keutrk']+=$bar['totaldebet'];
	}
	
	$str="select sum(debet) as totaldebet,kodeorg from ".$dbname.".keu_jurnaldt_vw where kodejurnal in ('ALKOH') and tanggal like '".$prd."%' group by kodeorg";
	$res = fetchdata($str);
	foreach($res as $bar){
		// $arrkodeorg=explode("/",$bar['nojurnal']);
		// $bar['kodeorg']=$arrkodeorg[1];
		
		$data[$bar['kodeorg']]['ohtbm']+=$bar['totaldebet'];
	}
	
	$str="select * from ".$dbname.".setup_blok_tahunan where tahun='".str_replace("-","",$prd)."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$data[substr($bar['kodeorg'],0,4)]['aresta']+=$bar['luasareaproduktif'];
	}
	
	$str = "select * from ".$dbname.".sdm_5periodegaji where  periode='".$prd."' and sudahproses='1'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$data[$bar['kodeorg']]['sdtgjcl']+=$bar['sudahproses'];
		if($bar['jenisgaji']=='B'){
			$bkm[$bar['kodeorg']]['sdtdet#gjclb']['all']=$bar['sudahproses'];
			$bkm[$bar['kodeorg']]['sdtdet#gjclb']['post']=$bar['sudahproses'];
		}elseif($bar['jenisgaji']=='H'){
			$bkm[$bar['kodeorg']]['sdtdet#gjclh']['all']=$bar['sudahproses'];
			$bkm[$bar['kodeorg']]['sdtdet#gjclh']['post']=$bar['sudahproses'];
		}
		
	}
	
	$str = "select * from ".$dbname.".kebun_aktifitas where  tanggal like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['tipetransaksi']!='PNN'){			
			$bkm[$bar['kodeorg']]['sdmdet#gjbkm']['all']+=1;
			if($bar['jurnal']=='1'){			
				$bkm[$bar['kodeorg']]['sdmdet#gjbkm']['post']+=1;
			}
		}else{
			$bkm[$bar['kodeorg']]['sdmdet#gjpnn']['all']+=1;
			if($bar['jurnal']=='1'){			
				$bkm[$bar['kodeorg']]['sdmdet#gjpnn']['post']+=1;			
			}
		}
	}
	$str = "select * from ".$dbname.".kebun_3premipemanen where  periode like '".$prd."%' group by divisi, periode, tahap";
	$res = fetchdata($str);
	foreach($res as $bar){
		$bkm[$bar['kodeorg']]['sdmdet#gjprepnn']['all']+=1;
		if($bar['posting']=='1'){			
			$bkm[$bar['kodeorg']]['sdmdet#gjprepnn']['post']+=1;
		}
	}
	$str = "select * from ".$dbname.".kebun_premikemandoran where  periode like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$bkm[$bar['kodeorg']]['sdmdet#gjpremdr']['all']+=1;
		$bkm[$bar['kodeorg']]['sdmdet#gjpremdr']['post']+=1;
	}
	$str = "select * from ".$dbname.".sdm_absensidt_vw where  tanggal like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$bkm[$bar['kodeorg']]['sdmdet#gjabs']['all']+=1;
		$bkm[$bar['kodeorg']]['sdmdet#gjabs']['post']+=1;
	}
	$str = "select * from ".$dbname.".vhc_runht where  tanggal like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$bkm[$bar['kodeorg']]['sdmdet#gjkeg']['all']+=1;
		if($bar['posting']=='1'){			
			$bkm[$bar['kodeorg']]['sdmdet#gjkeg']['post']+=1;
		}
	}
	$str = "select * from ".$dbname.".sdm_lemburht where  tanggal like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$bkm[substr($bar['kodeorg'],0,4)]['sdmdet#gjlbr']['all']+=1;
		if($bar['posting']=='1'){			
			$bkm[substr($bar['kodeorg'],0,4)]['sdmdet#gjlbr']['post']+=1;
		}
	}
	$str = "select * from ".$dbname.".sdm_potonganht where  periodegaji like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$bkm[$bar['kodeorg']]['sdmdet#gjpot']['all']+=1;
		if($bar['posting']=='1'){			
			$bkm[$bar['kodeorg']]['sdmdet#gjpot']['post']+=1;
		}
	}
	$str = "select * from ".$dbname.".sdm_uangmakandanextrafooding where  periodegaji like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$bkm[$bar['kodeorg']]['sdmdet#gjumt']['all']+=1;
		if($bar['posting']=='1'){			
			$bkm[$bar['kodeorg']]['sdmdet#gjumt']['post']+=1;
		}
	}
	$str = "select * from ".$dbname.".sdm_pendapatanlainht where  periodegaji  like '".$prd."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$bkm[substr($bar['kodeorg'],0,4)]['sdmdet#gjpln']['all']+=1;
		if($bar['posting']=='1'){			
			$bkm[substr($bar['kodeorg'],0,4)]['sdmdet#gjpln']['post']+=1;
		}
	}
	
	/* 'sdmdetgjbkm' =>'Kebun - Trans - Buku Kegiatan Mandor',
	'sdmdetgjpnn' =>'Kebun - Trans - Kegiatan Panen',
	'sdmdetgjprepnn' =>'Kebun - Proses - Premi Pemanen',
	'sdmdetgjpremdr' =>'Kebun - Proses - Premi Kemandoran',
	'sdmdetgjkeg' =>'Traksi - Trans - Kegiatan',
	'sdmdetgjabs' =>'SDM - Trans - Absensi',
	'sdmdetgjlbr' =>'SDM - Trans - Lembur',
	'sdmdetgjpot' =>'SDM - Trans - Potongan',
	'sdmdetgjpln' =>'SDM - Proses - Pendapatan Lain',
	'sdmdetgjumt' =>'SDM - Proses - Extra Fooding, UM, Trans',
	 */
	 
	// echo"<pre>";
	// print_r($bkm);
	// echo"</pre>"; 
	foreach($arrjenis as $kodejns => $nmjenis){
		$name=substr($kodejns,0,3);
		if(substr($kodejns,3,4)!='det#'){
			$no++;
			$tab.="<tr class=rowcontent style=height:25px;cursor:pointer; onclick=showhide('detail".$name."[]');>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td nowrap>".$nmjenis."</td>";
		}else{
			$tab.="<tr class=rowcontent style=height:25px;display:none; name=detail".$name."[]>";
			$tab.="<td align=center></td>";
			$tab.="<td nowrap style='padding-left:30px;font-style:italic;color:grey;'>* ".$nmjenis."</td>";
		}
		foreach($unit as $kdorg){
			if($bkm[$kdorg][$kodejns]['post']!=''){				
				$data[$kdorg][$kodejns]=numb_format(bagi($bkm[$kdorg][$kodejns]['post'],$bkm[$kdorg][$kodejns]['all'])*100)."%";
			}
			
			
			$hasil=$stl="";
			if($data[$kdorg][$kodejns]!=''){
				if($kodejns=='acct' or $kodejns=='gudang'){
					$hasil=$data[$kdorg][$kodejns]; 
					$stl="style=background-color:green;color:yellow;font-size:10px;font-weight:bold;";
				}elseif(substr($kodejns,3,4)=='det#'){
					$hasil=$data[$kdorg][$kodejns]; 
					$stl="style=background-color:#E0FFFF;color:green;font-size:10px; title='Persen posting terhadap input.';";
				}else{					
					$hasil="Done"; 
					$stl="style=background-color:green;color:yellow;font-weight:bold;";
				}
			}
			
			if($data[$kdorg]['sdtgjcl']=='' and substr($data[$kdorg]['acct'],0,10)!=''){
				if($kodejns=='acct' or $kodejns=='sdtgjcl'){
					#cek ada periode gaji gak
					$sCekPeriodegaji="select * from ".$dbname.".sdm_5periodegaji where periode='".$prd."' and kodeorg='".$kdorg."'";
					$rCekPeriodegaji=fetchData($sCekPeriodegaji);
					if(count($rCekPeriodegaji)!=0){
						$stl="style=background-color:red;color:yellow;font-weight:bold; title='Periode gaji terbuka, ada kemungkinan data gaji berubah, silahkan lakukan proses ulang.'";
					}
				}
			}
			
			$tab.="<td align=center ".$stl.">".$hasil."</td>";
		}
		$tab.="</tr>";
	}


$tab.="</tbody></table>";


switch ($proses) {
    case 'preview':
        echo $tab1.$tab;
	break;

    case 'excel':
        $nop = "monitoringclosing.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet('monitoringclosing', $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
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

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	#$n = number_format($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
function bagi($e,$i){
	if($i!='' and $i!='0'){
		$n=$e/$i;
	}else{
		$n=0;
	}
	return $n;
}
?>
