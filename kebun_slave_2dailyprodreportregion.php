<?php
ini_set('display_errors',0);
error_reporting(0);

require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('jpgraph/jpgraph.php');
require_once('jpgraph/jpgraph_bar.php');
require_once('jpgraph/jpgraph_line.php');
require_once('jpgraph/jpgraph_table.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$proses   = checkPostGet('proses', '');
$kdorg    = checkPostGet('kdorg', '');
$regional = checkPostGet('regional', '');
$tanggal  = tanggalsystemn(checkPostGet('tanggal', ''));
$tipe     = checkPostGet('tipe', '');
$tipex    = checkPostGet('tipe', '');
$bagi     = checkPostGet('bagi', '');

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}

$arrbi         = explode('-',$tanggal); 
$tahun         = $arrbi[0]; 
$bulan         = $arrbi[1];
$periode       = $tahun."-".$bulan;
$tanggaldari   = $tahun."-".$bulan."-01";
$tanggalsampai = $tanggal;

$rangetgl = rangeTanggal($tanggaldari, $tanggalsampai);

if($regional!=''){
	$whreg="and subregional='".$regional."'";
}
if($kdorg!=''){
	$whreg.=" and substr(a.kodeorganisasi,1,4) ='".$kdorg."'";
}

$order="'SD1E','S1PE','SD2E','S2PE','SD3E','S3PE','SD4E','S4PE','BPJE','BPPE','SNPE','SPPE','KSPE','KPPE','KBPE','KPPE','AA1E','A1PE','AA2E','GA1E','BA1E'";

$listkodeorg = [];
$datakodeorg = [];

$str = "select * from ".$dbname.".organisasi a left join ".$dbname.".bgt_regional_assignment b on a.kodeorganisasi=b.kodeunit where 1=1 ".$whreg." and tipe in ('KEBUN','PABRIK') order by FIELD (kodeorganisasi,$order)";
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['inti']=='1'){
		$ip='Inti';
		$kebuninti[$bar['kodeunit']]=$bar['kodeunit'];
	}
	if($bar['inti']=='0'){
		$ip='Plasma';
	}
	
	if($bar['tipe']=='KEBUN'){		
		$datakodeorg[$bar['subregional']][$bar['kodeunit']][$ip]=$ip;
		$listkodeorg[$bar['kodeunit']]=$bar['kodeunit'];
	}
	
	$listunit[$bar['kodeunit']]=$bar['kodeunit'];
	$getregion[$bar['kodeunit']]=$bar['subregional'];
	$listreg[$bar['subregional']]=$bar['subregional'];		
}

foreach($listreg as $reg){
	$datakodeorg[$reg][$reg]['Swadaya']='Swadaya';
	$adaproduksi[$reg]=$reg;
	$listkodeorg[$reg]=$reg;
}

$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listunit)."')";
$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listunit)."')";
$whdiv=" and substr(a.divisi,1,4) in ('".implode("','",$listunit)."')";
$whhk=" and substr(a.unit,1,4) in ('".implode("','",$listunit)."')";
$whB=" and substr(a.millcode,1,4) in ('".implode("','",$listunit)."')";
$whtimb=" and (substr(a.millcode,1,4) in ('".implode("','",$listunit)."') or substr(a.kodeorg,1,4) in ('".implode("','",$listunit)."',''))";

if($kdorg!=''){
	$where.=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2.=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$whdiv.=" and substr(a.divisi,1,4) ='".$kdorg."'";
	$whhk.=" and substr(a.unit,1,4) ='".$kdorg."'";
	$whB=" and substr(a.millcode,1,4) ='".$kdorg."'";
	$whtimb=" and (substr(a.millcode,1,4) ='".$kdorg."' or substr(a.kodeorg,1,4) in ('".$kdorg."',''))";
}

#ambil prd bgt
$kgbi = "kg".addZero($bulan,2);
$jjgbi = "jjg".addZero($bulan,2);
$str = " select kodeunit, sum(".$kgbi.") as kg, sum(".$jjgbi.") as jjg from ".$dbnamerpt.".bgt_produksi_kebun a where 1=1 ".$where2." and tahunbudget = '".$tahun."' group by kodeunit";
$res = fetchdatarpt($str);
foreach($res as $bar){
	if($listkodeorg[substr($bar['kodeunit'],0,4)]!=''){			
		$bgtkg[$bar['kodeunit']] += $bar['kg'];
		$bgtjjg[$bar['kodeunit']] += $bar['jjg'];
	}
	$unit = substr($bar['kodeunit'],0,4);
	$str = " select * from ".$dbnamerpt.".sdm_5harilibur a where kebun='GLOBAL' and keterangan='libur' and tanggal like '".$periode."%' and (kebun='GLOBAL' or kebun='".substr($bar['kodeunit'],0,4)."')";
	$res = fetchdatarpt($str);
	foreach($res as $bar){
		$ketlibur[$unit][$bar['tanggal']]=$bar['keterangan'];
		$liburglobal[$bar['tanggal']]=$bar['catatan'];
		$libur[$unit]+=1;
	}
	
	$adaproduksi[$unit]=$unit;
}

$kgbi = "olah".addZero($bulan,2);
$str = " select millcode, sum(".$kgbi.") as kg from ".$dbnamerpt.".bgt_produksi_pks_vw a where 1=1 ".$whB." and tahunbudget = '".$tahun."' and kodeunit='tbsexternal' group by millcode";
$res = fetchdatarpt($str);
foreach($res as $bar){
	$bgtkg[$getregion[$bar['millcode']]] += $bar['kg'];
	
	$unit = substr($bar['millcode'],0,4);
	$str = " select * from ".$dbnamerpt.".sdm_5harilibur a where kebun='GLOBAL' and keterangan='libur' and tanggal like '".$periode."%' and (kebun='GLOBAL' or kebun='".substr($bar['millcode'],0,4)."')";
	$res = fetchdatarpt($str);
	foreach($res as $bar){
		$ketlibur[$getregion[$unit]][$bar['tanggal']]=$bar['keterangan'];
		$liburglobal[$bar['tanggal']]=$bar['catatan'];
		$libur[$getregion[$unit]]+=1;
	}
	$adaproduksi[$unit]=$unit;
}

$jlhhari = cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
foreach($listkodeorg as $kodeorg){
	foreach($rangetgl as $tgl){
		if($bagi=='hke'){			
			$harikerja[$kodeorg]=$jlhhari-$libur[$kodeorg];
			$kgbgtpanen[$kodeorg]=$bgtkg[$kodeorg]/$harikerja[$kodeorg];
			$jjgbgtpanen[$kodeorg]=$bgtjjg[$kodeorg]/$harikerja[$kodeorg];
			if($ketlibur[$kodeorg][$tgl]==''){			
				$data['panentbs'][$kodeorg][$tgl]['bgt']=$kgbgtpanen[$kodeorg];
				$total['panentbs'][$kodeorg]['bgt']=$bgtkg[$kodeorg];
				
				$data['kirim'][$kodeorg][$tgl]['bgt']=$kgbgtpanen[$kodeorg];
				$total['kirim'][$kodeorg]['bgt']=$bgtkg[$kodeorg];
				
				$data['produksitbs'][$kodeorg][$tgl]['bgt']=$kgbgtpanen[$kodeorg];
				$total['produksitbs'][$kodeorg]['bgt']=$bgtkg[$kodeorg];
				
				$data['panentbsjjg'][$kodeorg][$tgl]['bgt']=$jjgbgtpanen[$kodeorg];
				$total['panentbsjjg'][$kodeorg]['bgt']=$bgtjjg[$kodeorg];
				
				$data['kirimjjg'][$kodeorg][$tgl]['bgt']=$jjgbgtpanen[$kodeorg];
				$total['kirimjjg'][$kodeorg]['bgt']=$bgtjjg[$kodeorg];
			}
		}else{
			$harikerja[$kodeorg]=$jlhhari;
			$kgbgtpanen[$kodeorg]=$bgtkg[$kodeorg]/$harikerja[$kodeorg];
			$jjgbgtpanen[$kodeorg]=$bgtjjg[$kodeorg]/$harikerja[$kodeorg];
			$data['panentbs'][$kodeorg][$tgl]['bgt']=$kgbgtpanen[$kodeorg];
			$total['panentbs'][$kodeorg]['bgt']=$bgtkg[$kodeorg];
			
			$data['kirim'][$kodeorg][$tgl]['bgt']=$kgbgtpanen[$kodeorg];
			$total['kirim'][$kodeorg]['bgt']=$bgtkg[$kodeorg];
			
			$data['produksitbs'][$kodeorg][$tgl]['bgt']=$kgbgtpanen[$kodeorg];
			$total['produksitbs'][$kodeorg]['bgt']=$bgtkg[$kodeorg];
			
			$data['panentbsjjg'][$kodeorg][$tgl]['bgt']=$jjgbgtpanen[$kodeorg];
			$total['panentbsjjg'][$kodeorg]['bgt']=$bgtjjg[$kodeorg];
			
			$data['kirimjjg'][$kodeorg][$tgl]['bgt']=$jjgbgtpanen[$kodeorg];
			$total['kirimjjg'][$kodeorg]['bgt']=$bgtjjg[$kodeorg];
		}
	}
}

$whrekappanen=" and tanggal>='2021-01-01'";

$str = "select sum(tenagakerja) as tenagakerja, sum(jjgpanen) as jjgpanen, sum(kgkebun) as kgkebun, sum(jjgafkir) as jjgafkir, substr(divisi,1,4) as kodeorg, tanggal, blok from ".$dbnamerpt.".kebun_rekappnn_vw a where tanggal like '".$periode."%' and tanggal<='".$tanggalsampai."' ".$whrekappanen." ".$whdiv." group by substr(divisi,1,4), tanggal, blok";
$res = fetchdatarpt($str);
foreach($res as $bar){
	$data['panentbs'][$bar['kodeorg']][$bar['tanggal']]['act'] += $bar['kgkebun'];
	$data['panentbsjjg'][$bar['kodeorg']][$bar['tanggal']]['act'] += $bar['jjgpanen'];
	$data['pemanen'][$bar['kodeorg']][$bar['tanggal']]['act'] += $bar['tenagakerja'];
	$data['panenjjg'][$bar['blok']][$bar['tanggal']]['act'] += $bar['jjgpanen'];
	$data['jjgafkir'][$bar['blok']][$bar['tanggal']]['act'] += $bar['jjgafkir'];
	
	if($bar['tenagakerja']>0){
		$pembagi[$bar['kodeorg']][$bar['tanggal']]['act']=1;
	}
	$total['tenagakerja'][$bar['kodeorg']]['act'] += $bar['tenagakerja'];
	$total['panentbs'][$bar['kodeorg']]['act'] += $bar['kgkebun'];
	$total['panentbsjjg'][$bar['kodeorg']]['act'] += $bar['jjgpanen'];
	
	$listblok[$bar['blok']]=$bar['blok'];
	$adaproduksi[$bar['kodeorg']]=$bar['kodeorg'];
}


$str = "select sum(jjg) as jjg, sum(kgwb) as kgwb, kodeorg, tanggal,blok from ".$dbnamerpt.".kebun_spb_vw a where tanggal like '".$periode."%' and tanggal<='".$tanggalsampai."' ".$where." and posting='1' group by kodeorg, tanggal,blok";
$res = fetchdatarpt($str);
foreach($res as $bar){
	$data['kirimspbkg'][$bar['kodeorg']][$bar['tanggal']]['act'] += $bar['kgwb'];
	$data['kirimjjg'][$bar['kodeorg']][$bar['tanggal']]['act'] += $bar['jjg'];
	$data['kirimspbjjg'][$bar['blok']][$bar['tanggal']]['act'] += $bar['jjg'];
	
	$total['kirimjjg'][$bar['kodeorg']]['act'] += $bar['jjg'];
	
	$listblok[$bar['blok']]=$bar['blok'];
	$adaproduksi[$bar['kodeorg']]=$bar['kodeorg'];
}

$str = "select sum(beratbersih) as kg, sum(kgpotsortasi) as kgpot, kodeorg, millcode, substr(tanggal,1,10) as tanggal from ".$dbnamerpt.".pabrik_timbangan a where tanggal like '".$periode."%' and substr(tanggal,1,10)<='".$tanggalsampai."' ".$whtimb." and kodebarang='40000003' group by millcode,kodeorg, substr(tanggal,1,10)";
$res = fetchdatarpt($str);
foreach($res as $bar){
	if($listkodeorg[substr($bar['kodeorg'],0,4)]!=''){
		$data['kirim'][$bar['kodeorg']][$bar['tanggal']]['act'] += $bar['kg'];
		$data['produksitbs'][$bar['kodeorg']][$bar['tanggal']]['act'] += $bar['kg']-$bar['kgpot'];
		$data['jjgmill'][$bar['kodeorg']][$bar['tanggal']]['act'] += $bar['jumlahtandan1'];

		$total['kirim'][$bar['kodeorg']]['act'] += $bar['kg'];
		$total['produksitbs'][$bar['kodeorg']]['act'] += $bar['kg']-$bar['kgpot'];
		$adaproduksi[$bar['kodeorg']]=$bar['kodeorg'];
		
		
		#graph
		if(!empty($kebuninti[$bar['kodeorg']])){			
			$kggraph[$getregion[$bar['kodeorg']]][$bar['tanggal']]['act']+=$bar['kg'];;
		}
	} 
	if($getregion[$bar['millcode']]!='' and $bar['kodeorg']==''){
		$data['kirim'][$getregion[$bar['millcode']]][$bar['tanggal']]['act'] += $bar['kg'];
		$data['produksitbs'][$getregion[$bar['millcode']]][$bar['tanggal']]['act'] += $bar['kg']-$bar['kgpot'];
		
		$total['kirim'][$getregion[$bar['millcode']]]['act'] += $bar['kg'];
		$total['produksitbs'][$getregion[$bar['millcode']]]['act'] += $bar['kg']-$bar['kgpot'];
	}
}

// echo"<pre>";
// // print_r($datakodeorg);
// print_r($data['kirim']);
// // print_r($subtotal);
// echo"</pre>";
// exit();
	
foreach($listkodeorg as $kodeorg){
	$str = "select sum(jjgpanen) as jjgpanen, sum(jjgafkir) as jjgafkir, substr(divisi,1,4) as kodeorg, blok from ".$dbnamerpt.".kebun_rekappnn_vw a where tanggal<'".$tanggaldari."' and substr(divisi,1,4) = '".$kodeorg."' ".$whrekappanen." group by blok";
	$res = fetchdatarpt($str);
	foreach($res as $bar){
		$jjgpanenbl[$bar['blok']]+=$bar['jjgpanen'];
		$jjgafkirbl[$bar['blok']]+=$bar['jjgafkir'];
	}
	$str = "select sum(jjg) as jjg, sum(kgwb) as kgwb, kodeorg, blok from ".$dbnamerpt.".kebun_spb_vw a where tanggal<'".$tanggaldari."' and kodeorg = '".$kodeorg."' ".$whrekappanen." group by blok";
	$res = fetchdatarpt($str);
	foreach($res as $bar){
		$jjgkirimbl[$bar['blok']]+=$bar['jjg'];
	}
	
	
	$str = "select * from ".$dbnamerpt.".kebun_5bjr a where periode='".$periode."' and substr(kodeorg,1,4) = '".$kodeorg."' order by updatetime asc";
	$res = fetchdatarpt($str);
	foreach($res as $bar){
		$bjr[$bar['kodeorg']]=$bar['bjr'];
	}
}

$tempjjgpnn=$tempjjgkrm=$tempjjgafk=[];
foreach($rangetgl as $tgl){
	foreach($listblok as $blok){			
		$tempjjgpnn[$blok]+=$data['panenjjg'][$blok][$tgl]['act'];
		$tempjjgafk[$blok]+=$data['jjgafkir'][$blok][$tgl]['act'];
		$tempjjgkrm[$blok]+=$data['kirimspbjjg'][$blok][$tgl]['act'];
		$jjgpanensdhi[$blok][$tgl]+=$tempjjgpnn[$blok];
		$jjgafkirsdhi[$blok][$tgl]+=$tempjjgafk[$blok];
		$jjgkirimsdhi[$blok][$tgl]+=$tempjjgkrm[$blok];

		$restant[$blok][$tgl]=(($jjgpanenbl[$blok]+$jjgpanensdhi[$blok][$tgl])-($jjgkirimbl[$blok]+$jjgkirimsdhi[$blok][$tgl]+$jjgafkirsdhi[$blok][$tgl]+$jjgafkirbl[$blok]));
		$data['restantjjg'][substr($blok,0,4)][$tgl]['act'] += $restant[$blok][$tgl];
		$data['restant'][substr($blok,0,4)][$tgl]['act'] += $restant[$blok][$tgl]*$bjr[$blok];
		
		if($tgl==$tanggal){			
			$total['restantjjg'][substr($blok,0,4)]['act'] += $restant[$blok][$tgl];
			$total['restant'][substr($blok,0,4)]['act'] += $restant[$blok][$tgl]*$bjr[$blok];
		}
	}
}

// echo"<pre>";
// print_r($datakodeorg);
// echo"</pre>";

if ($proses == 'excel') {
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<table class=sortable cellpadding=5 cellspacing=1>";
}


$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='2'>Description</th>";
			$tab.="<th align=center colspan=3>To Date This Month</th>";				
			foreach($rangetgl as $tgl){
				if($liburglobal[$tgl]!=''){
					$tab.="<th align=center style=color:red; title='".$liburglobal[$tgl]."' colspan=3>".hari($tgl)." ".tanggalnormal($tgl)."</th>";				
				}else{					
					$tab.="<th align=center colspan=3>".hari($tgl)." ".tanggalnormal($tgl)."</th>";				
				}
			}
        $tab.="</tr>";
        $tab.="<tr class=rowheader>";
			$tab.="<th align=center>Actual</th>";				
			$tab.="<th align=center>Budget</th>";				
			$tab.="<th align=center nowrap>Act %</th>";
			foreach($rangetgl as $tgl){				
				$tab.="<th align=center>Actual</th>";				
				$tab.="<th align=center>Budget</th>";				
				$tab.="<th align=center nowrap>Act %</th>";
			}		
        $tab.="</tr>";
     $tab.="</thead>
	<tbody>";
	
	$arraydata['Inti'] = array(
		'panentbsjjg'   =>'Panen TBS (Jjg)',
		'panentbs'      =>'Panen TBS (Kg)',
		'kirimjjg'      =>'Kirim (Jjg)',
		'kirim'         =>'Kirim (Kg)',
		'restantjjg'    =>'Restant (Jjg)',
		'restant'       =>'Restant (Kg)',
		'produksitbs'   =>'Produksi TBS (Kg)',
		'pemanen'       =>'Pemanen'
	);
	$arraydata['Plasma'] = array(
		'kirim'         =>'Kirim (Kg)',
		'produksitbs'   =>'Produksi TBS (Kg)'
	);
	
	$arraydata['Swadaya'] = array(
		'kirim'         =>'Kirim (Kg)',
		'produksitbs'   =>'Produksi TBS (Kg)'
	);
	
	foreach($datakodeorg as $regional => $val1){
		foreach($val1 as $kodeorg => $val2){
			foreach($val2 as $ip){
				$stotaltk['tenagakerja'][$ip][$regional]['act']+=$total['tenagakerja'][$kodeorg]['act'];
				foreach($rangetgl as $tgl){
					$totalpembagi[$kodeorg]['act']+=$pembagi[$kodeorg][$tgl]['act'];
					$stotalpembagi[$ip][$regional]['act']+=$pembagi[$kodeorg][$tgl]['act'];
				}					
			}
		}
	}

	foreach($datakodeorg as $regional => $val1){
		foreach($val1 as $kodeorg => $val2){
			foreach($val2 as $ip){
				foreach($rangetgl as $tgl){
					@$total['pemanen'][$kodeorg]['act']=fixnan(@$total['tenagakerja'][$kodeorg]['act']/@$totalpembagi[$kodeorg]['act']);
				}					
			}
		}
	}

	foreach($datakodeorg as $regional => $val1){
		$tab.="<tr class=rowcontent style=background-color:#e3dede;font-weight:bold;>";
		$tab.="<td nowrap  colspan=4>".$regional."</td>";
		foreach($rangetgl as $tgl){
			$tab.="<td></td><td></td><td></td>";
		}
		$tab.="</tr>";
		foreach($val1 as $kodeorg => $val2){
			if(!empty($adaproduksi[$kodeorg])){
				foreach($val2 as $ip){
					if($ip=='Inti'){					
						$tab.="<tr class=rowcontent style=background-color:#b3ffc1;font-weight:bold;>";
					}elseif($ip=='Plasma'){
						$tab.="<tr class=rowcontent style=background-color:#a3f7e5;font-weight:bold;>";
					}else{
						$tab.="<tr class=rowcontent style=background-color:#dcb0f5;font-weight:bold;>";
					}
					$tab.="<td nowrap  colspan=4 style=padding-left:5px;>".$ip."</td>";
					foreach($rangetgl as $tgl){
						$tab.="<td></td><td></td><td></td>";
					}
					$tab.="</tr>";
					
					$tab.="<tr class=rowcontent>";
					if(!empty($listreg[$kodeorg])){
						$tab.="<td nowrap style=padding-left:10px;font-weight:bold; colspan=4>SWADAYA</td>";
					}else{					
						$tab.="<td nowrap style=padding-left:10px;font-weight:bold; colspan=4>".$kodeorg." - ".getNamaOrg($kodeorg)."</td>";
					}
					foreach($rangetgl as $tgl){
						$tab.="<td></td><td></td><td></td>";
					}
					$tab.="</tr>";
					foreach($arraydata[$ip] as $judul => $nama){
						
						if(substr($judul,-3)=='jjg'){
							$tab.="<tr class=rowcontent style=font-style:italic;>";					
						}else{
							$tab.="<tr class=rowcontent>";					
						}
						$tab.="<td nowrap style=padding-left:20px;>".$nama."</td>";
						
						@$total[$judul][$kodeorg]['%']=fixnan($total[$judul][$kodeorg]['act']/@$total[$judul][$kodeorg]['bgt']*100);
						
						$tab.="<td align=right>".numb_format($total[$judul][$kodeorg]['act'])."</td>";
						$tab.="<td align=right>".numb_format($total[$judul][$kodeorg]['bgt'])."</td>";
						$tab.="<td align=right>".numb_format($total[$judul][$kodeorg]['%'],2)."</td>";
						
						$stotal[$judul][$ip][$regional]['act']+=$total[$judul][$kodeorg]['act'];
						$stotal[$judul][$ip][$regional]['bgt']+=$total[$judul][$kodeorg]['bgt'];
						
						foreach($rangetgl as $tgl){
							@$data[$judul][$kodeorg][$tgl]['%']=fixnan($data[$judul][$kodeorg][$tgl]['act']/@$data[$judul][$kodeorg][$tgl]['bgt']*100);
							
							if($data['kirimspbkg'][$kodeorg][$tgl]['act']<$data['kirim'][$kodeorg][$tgl]['act'] and ($judul=='kirimjjg' or $judul=='kirim') and $ip=='Inti'){
								$noted['kirim']="title='Ada SPB yang belum diinput.!' style=color:red;";
							}else{
								$noted['kirim']="";
							}
							$tab.="<td align=right ".$noted['kirim'].">".numb_format($data[$judul][$kodeorg][$tgl]['act'])."</td>";
							$tab.="<td align=right>".numb_format($data[$judul][$kodeorg][$tgl]['bgt'])."</td>";
							$tab.="<td align=right>".numb_format($data[$judul][$kodeorg][$tgl]['%'],2)."</td>";
							
							
							$subtotal[$judul][$ip][$regional][$tgl]['act']+=$data[$judul][$kodeorg][$tgl]['act'];
							$subtotal[$judul][$ip][$regional][$tgl]['bgt']+=$data[$judul][$kodeorg][$tgl]['bgt'];
							if($tgl==$tanggal){			
								$stotal['restantjjg'][$ip][$regional]['act'] += $total['restantjjg'][$kodeorg][$tgl]['act'];
								$stotal['restant'][$ip][$regional]['act'] += $total['restant'][$kodeorg][$tgl]['act'];;
							}
						}
						$tab.="</tr>";
					}			
				}
			}
		}
		$tab.="<tr class=rowcontent style=background-color:#e3dede;font-weight:bold;>";
		$tab.="<td align=left colspan=4>TOTAL ".$regional."</td>";
		foreach($rangetgl as $tgl){
			$tab.="<td></td><td></td><td></td>";
		}
		$tab.="</tr>";
		foreach($arraydata  as $ip => $val1){
			if($ip=='Inti'){					
				$tab.="<tr class=rowcontent style=background-color:#b3ffc1;font-weight:bold;>";
			}elseif($ip=='Plasma'){
				$tab.="<tr class=rowcontent style=background-color:#a3f7e5;font-weight:bold;>";
			}else{
				$tab.="<tr class=rowcontent style=background-color:#dcb0f5;font-weight:bold;>";
			}
			$tab.="<td nowrap colspan=4 style=padding-left:10px;>".$ip."</td>";
			foreach($rangetgl as $tgl){
				$tab.="<td></td><td></td><td></td>";
			}
			$tab.="</tr>";
			foreach($val1  as $judul => $nama){				
				$tab.="<tr class=rowcontent>";
				$tab.="<td nowrap style=padding-left:20px;>".$nama."</td>";
				
				@$stotal[$judul][$ip][$regional]['%']=fixnan(@$stotal[$judul][$ip][$regional]['act']/@$stotal[$judul][$ip][$regional]['bgt']*100);
				@$stotal['pemanen'][$ip][$regional]['act']=fixnan(@$stotaltk['tenagakerja'][$ip][$regional]['act']/$stotalpembagi[$ip][$regional]['act']);

				$tab.="<td align=right>".numb_format($stotal[$judul][$ip][$regional]['act'])."</td>";
				$tab.="<td align=right>".numb_format($stotal[$judul][$ip][$regional]['bgt'])."</td>";
				$tab.="<td align=right>".numb_format($stotal[$judul][$ip][$regional]['%'],2)."</td>";
				foreach($rangetgl as $tgl){
					@$subtotal[$judul][$ip][$regional][$tgl]['%']=fixnan(@$subtotal[$judul][$ip][$regional][$tgl]['act']/@$subtotal[$judul][$ip][$regional][$tgl]['bgt']*100);
					
					$tab.="<td align=right>".numb_format($subtotal[$judul][$ip][$regional][$tgl]['act'])."</td>";
					$tab.="<td align=right>".numb_format($subtotal[$judul][$ip][$regional][$tgl]['bgt'])."</td>";
					$tab.="<td align=right>".numb_format($subtotal[$judul][$ip][$regional][$tgl]['%'],2)."</td>";
				}
				$tab.="</tr>";
			}
		}
	}
$tab.="</tbody></table>";


switch ($proses) {
    case 'preview':
		echo $tab;
    break;
    case 'excel':
        $nop_ = 'olah budget vs actual';
		$print = $tab;
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

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}

?>