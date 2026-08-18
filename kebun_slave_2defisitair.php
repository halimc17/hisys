<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$tipeprint=checkPostGet('tipeprint','');

$unit=checkPostGet('unit','');
$subunit=checkPostGet('subunit','');
$periode=checkPostGet('periode','');

$unit2=checkPostGet('unit2','');
$subunit2=checkPostGet('subunit2','');
$periode2=checkPostGet('periode2','');

switch($method){
	case'getsubunit':
		$optSubUnit="<option value='all'>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and tipe = 'AFDELING' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;

	case'getsubunit2':
		$optSubUnit="<option value='all'>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit2."' and tipe = 'AFDELING' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;
	
	case'preview':
		$tab="";

		$months = [
			"Januari", "Februari", "Maret", "April", "Mei", "Juni",
			"Juli", "Agustus", "September", "Oktober", "November", "Desember"
		];
        
		
		$where="";
		$wh="";
		if($subunit=='all'){
			$where.="";
            $wh.=" and kodeorg like '".$unit."%'";
		}else{
			$where.=" and kodeorganisasi ='".$subunit."'";
            $wh.=" and kodeorg ='".$subunit."'";
		}



        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and  tipe = 'AFDELING' ".$where." order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
            $kodedivisi[$val['kodeorganisasi']] = $val['kodeorganisasi'];
        }

        $str="select distinct kodeorg, substr(tanggal, 1,7) as periode, 
		sum(pagi + siang + sore + malam) as jml , SUM(CASE 
		WHEN pagi != 0 OR siang != 0 OR sore != 0 OR malam != 0 THEN 1 
		ELSE 0 END) AS HH  from ".$dbname.".kebun_curahhujan where 1=1 and tanggal like '%".$periode."%'  ".$wh." group by kodeorg, periode";
		$res=fetchdata($str);
		foreach($res as $val){
            $mmCurahhujan[$val['kodeorg']][$val['periode']]  = $val['jml'];
            $HH[$val['kodeorg']][$val['periode']]  = $val['HH'];
        }

		$str="select distinct kodeorg, periode,mm from ".$dbname.".kebun_defisitair where 1=1 and periode like '%".$periode."%'  ".$wh." group by kodeorg, periode";
		$res=fetchdata($str);
		foreach($res as $val){
            $MM[$val['kodeorg']][$val['periode']]  = $val['mm'];
        }

		$colspn=count($kodedivisi)*4;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th >".$_SESSION['lang']['divisi']."</th>
				<th >".$_SESSION['lang']['bulan']."</th>
				<th >MM</th>
				<th >HH</th>
				<th >MM/HH</th>
				<th >Cadangan Bulan Ini</th>
				<th >Evaporasi</th>
				<th >Balance</th>
				<th >Cadangan Akhir</th>
				<th >Drainase</th>
				<th >Defisit</th>";
		$tab.="</tr>";
		$tab.="</thead><tbody>";

			

				foreach($kodedivisi as $divisi){
					$tab.="<tr class='rowcontent'>";
						$tab.="<td align=center rowspan=13 width=50px>". getNamaOrg($divisi)."</td>";
					$tab.="</tr>"; 
					

					for ($i = 1; $i <= 12; $i++) {
						$tab.="<tr class='rowcontent'>";
							$tab.="<td align=center width=50px>". $months[$i - 1]."</td>";

							$formattedNumber = str_pad($i, 2, "0", STR_PAD_LEFT);

							$tab.="<td align=right width=50px>".number_format($mmCurahhujan[$divisi][$periode."-".$formattedNumber],2)."</td>";
							$tab.="<td align=right width=50px>".number_format($HH[$divisi][$periode."-".$formattedNumber],2)."</td>";
							$tab.="<td align=right width=50px>".number_format(fixnan($mmCurahhujan[$divisi][$periode."-".$formattedNumber]/$HH[$divisi][$periode."-".$formattedNumber]),2)."</td>";
							$tab.="<td align=right width=50px>".number_format($MM[$divisi][$periode."-".$formattedNumber],2)."</td>";

							##Evaporasi
							if($HH[$divisi][$periode."-".$formattedNumber] <= 9){
								$evaporasi[$divisi][$periode."-".$formattedNumber] = 150;
							}else{
								$evaporasi[$divisi][$periode."-".$formattedNumber] = 120;
							}

							$tab.="<td align=right width=50px>".number_format($evaporasi[$divisi][$periode."-".$formattedNumber],2)."</td>";
							
							## Balance
							$balance[$divisi][$periode."-".$formattedNumber] = $mmCurahhujan[$divisi][$periode."-".$formattedNumber] + $MM[$divisi][$periode."-".$formattedNumber] - $evaporasi[$divisi][$periode."-".$formattedNumber];
							$tab.="<td align=right width=50px>".number_format($balance[$divisi][$periode."-".$formattedNumber],2)."</td>";
							
							
							## Cadangan Akhir
							if($balance[$divisi][$periode."-".$formattedNumber] >= 200){
								$cadanganAkhir[$divisi][$periode."-".$formattedNumber] = 200;
							}elseif($balance[$divisi][$periode."-".$formattedNumber] < 200){
								$cadanganAkhir[$divisi][$periode."-".$formattedNumber] = $balance[$divisi][$periode."-".$formattedNumber];
							}elseif($balance[$divisi][$periode."-".$formattedNumber] < 0){
								$cadanganAkhir[$divisi][$periode."-".$formattedNumber] = $balance[$divisi][$periode."-".$formattedNumber];
							}
							$tab.="<td align=right width=50px>".number_format($cadanganAkhir[$divisi][$periode."-".$formattedNumber],2)."</td>";
							
							## Darinase
							$drainase[$divisi][$periode."-".$formattedNumber] = $balance[$divisi][$periode."-".$formattedNumber] - $cadanganAkhir[$divisi][$periode."-".$formattedNumber];
							$tab.="<td align=right width=50px>".number_format($drainase[$divisi][$periode."-".$formattedNumber],2)."</td>";

							## Defisit
							if($balance[$divisi][$periode."-".$formattedNumber] >= 0){
								$defisit[$divisi][$periode."-".$formattedNumber] = 0;
							}else{
								$defisit[$divisi][$periode."-".$formattedNumber] = $balance[$divisi][$periode."-".$formattedNumber] ;
							}
							$tab.="<td align=right width=50px>".number_format($defisit[$divisi][$periode."-".$formattedNumber],2)."</td>";
						$tab.="</tr>"; 
					}
				}

		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_CurahHujan_".$unit."_".$periode;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$tab))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		}
	break;

	case'preview2':
		$tab="";
		
		$where="";
		$wh="";
		if($subunit2=='all'){
			$where.="";
            $wh.=" and kodeorg like '".$unit2."%'";
		}else{
			$where.=" and kodeorganisasi ='".$subunit2."'";
            $wh.=" and kodeorg ='".$subunit2."'";
		}

        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit2."' and  tipe = 'AFDELING' ".$where." order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
            $kodedivisi[$val['kodeorganisasi']] = $val['kodeorganisasi'];
        }

        $str="select *,MONTH(tanggal) AS bulan  from ".$dbname.".kebun_curahhujan where 1=1 and tanggal like '%".$periode2."%' ".$wh." order by tanggal";
		$res=fetchdata($str);
		foreach($res as $val){
            $pagi[$val['kodeorg']][$val['bulan']]  += $val['pagi'];
            $siang[$val['kodeorg']][$val['bulan']] += $val['siang'];
            $sore[$val['kodeorg']][$val['bulan']]  += $val['sore'];
        }

		$colspn=count($kodedivisi)*4;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['bulan']."</th>
				<th colspan='".$colspn."'>PERIODE ".$periode2."</th>";
		$tab.="</tr>";
        
        $tab.="<tr class=rowheader style='text-align:center ;font-weight:bold'>";
        foreach($kodedivisi as $divisi){	
            $tab.=" <th colspan = 4>".getNamaOrg($divisi)."</th>";
		}
        $tab.="</tr>";

        $tab.="<tr class=rowheader style='text-align:center ;font-weight:bold'>";
        foreach($kodedivisi as $divisi){	
            $tab.=" <th>PAGI</th>";
            $tab.=" <th>SIANG</th>";
            $tab.=" <th>MALAM</th>";
            $tab.=" <th>TOTAL</th>";
		}
        $tab.="</tr>";

		$tab.="</thead><tbody>";
		for ($bulan = 1; $bulan <= 12; $bulan++) {
			$tab.="<tr class='rowcontent'>";
			$tab.="<td align=center>".numToMonth($bulan,'I','long')."</td>";
                foreach($kodedivisi as $divisi){	
                    $tab.="<td align=center width=50px>".number_format($pagi[$divisi][$bulan],2)."</td>";
                    $tab.="<td align=center width=50px>".number_format($siang[$divisi][$bulan],2)."</td>";
                    $tab.="<td align=center width=50px>".number_format($sore[$divisi][$bulan],2)."</td>";
                    $tab.="<td align=center width=50px>".number_format($pagi[$divisi][$bulan]+$siang[$divisi][$bulan]+$sore[$divisi][$bulan],2)."</td>";

                    $ttpagi[$divisi]  += $pagi[$divisi][$bulan];
                    $ttsiang[$divisi] += $siang[$divisi][$bulan];
                    $ttsore[$divisi]  += $sore[$divisi][$bulan];
                    $tttotal[$divisi] += $pagi[$divisi][$bulan]+$siang[$divisi][$bulan]+$sore[$divisi][$bulan];

                    if($pagi[$divisi][$bulan] != '' || $pagi[$divisi][$bulan] != 0 || $siang[$divisi][$bulan] != '' || $siang[$divisi][$bulan] != 0 || $sore[$divisi][$bulan] != '' || $sore[$divisi][$bulan] != 0 ){
                        $jmlahh[$divisi]  += 1;
                    }
                }
			$tab.="</tr>"; 
        }

        $tab.="<tr class='rowcontent' style='background-color:cyan;'>";
            $tab.="<td align=center width=50px><b>Jumlah (mm)</b></td>";
            foreach($kodedivisi as $divisi){	
                $tab.="<td align=center width=50px><b>".number_format($ttpagi[$divisi],2)."</b></td>";
                $tab.="<td align=center width=50px><b>".number_format($ttsiang[$divisi],2)."</b></td>";
                $tab.="<td align=center width=50px><b>".number_format($ttsore[$divisi],2)."</b></td>";
                $tab.="<td align=center width=50px><b>".number_format($tttotal[$divisi],2)."</b></td>";
            }
        $tab.="</tr>"; 
        $tab.="<tr class='rowcontent' style='background-color:yellow;'>";
			$tab.="<td align=center width=50px><b>Jumlah hari hujan (hh)</b></td>";
                foreach($kodedivisi as $divisi){	
                    $tab.="<td align=center colspan=3 width=50px></b></td>";
                    $tab.="<td align=center  width=50px><b>".number_format($jmlahh[$divisi])."</b></td>";
                }
        $tab.="</tr>";
		$tab.="</tr>"; 
        $tab.="<tr class='rowcontent' style='background-color:orange;'>";
		$tab.="<td align=center width=50px><b>Jumlah (mm/hh)</b></td>";
		foreach($kodedivisi as $divisi){	
                    $tab.="<td align=center colspan=3 width=50px></b></td>";
                    $tab.="<td align=center  width=50px><b>".number_format(fixnan($tttotal[$divisi]/$jmlahh[$divisi]),2)."</b></td>";
                }
        $tab.="</tr>";  



		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_CurahHujan_".$unit."_".$periode;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$tab))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		}
	break;

	
}


?>