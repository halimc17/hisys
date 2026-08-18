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
$periode=checkPostGet('periode','');
$jenis=checkPostGet('jenis','');


$tipe=checkPostGet('tipe','');
$periode2=checkPostGet('periode2','');

switch($method){
	case'preview':
		$tab="";				
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$str = "select * from ".$dbname.".setup_blok where  kodeorg like '".$unit."%' ";
		$res = fetchdata($str);
        foreach($res as $val){
			if($jenis == 'Kecil'){
				$Luas[$val['kodeorg']] += $val['luasareaproduktif'];
				$Pokok[$val['kodeorg']] += $val['jumlahpokok'];
				$tahunTanam[$val['kodeorg']] = $val['tahuntanam'];
			}else{
				$Luas[$val['indukblok']] += $val['luasareaproduktif'];
				$Pokok[$val['indukblok']] += $val['jumlahpokok'];
			}
		}

        $str = "select *,MONTH(tanggalpanen) AS bulan from ".$dbname.".kebun_spbdt_detail where date_format(tanggalpanen,'%Y') = '".$periode."' and indukblok like '".$unit."%' order by tanggalpanen asc ";
		$res = fetchdata($str);
        foreach($res as $val){
			if($jenis == 'Kecil'){
				$blok[substr($val['blok'],0,6)][$val['blok']] = $val['blok'];
							
				$jjg[$val['blok']][$val['bulan']] += $val['jjg'];
				$kgwb[$val['blok']][$val['bulan']] += $val['kgwb'];
				$kgwbnetto[$val['blok']][$val['bulan']] += $val['kgwbnetto'];
			}else{
				$blok[substr($val['indukblok'],0,6)][$val['indukblok']] = $val['indukblok'];

				$jjg[$val['indukblok']][$val['bulan']] += $val['jjg'];
				$kgwb[$val['indukblok']][$val['bulan']] += $val['kgwb'];
				$kgwbnetto[$val['indukblok']][$val['bulan']] += $val['kgwbnetto'];
			}
        }

		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='3'> DIV </th>
				<th rowspan='3'> TT </th>
				<th rowspan='3'> BLOK </th>
				<th rowspan='3'> LUAS (HA) </th>
				<th rowspan='3'> JUMLAH (POKOK) </th>
				<th rowspan='3'> SPH </th>
				<th colspan='60'>Periode ".$periode."</th>";
		$tab.="</tr>";
            
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $tab.="<th colspan='5'>".numToMonth($bulan,'I','long')."</th>";
            }
		$tab.="</tr>";
        $tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $tab.="<th>TDN</th>";
                $tab.="<th>KG BRUTO</th>";
                $tab.="<th>KG NETTO</th>";
                $tab.="<th>BJR</th>";
                $tab.="<th>KG/HA</th>";
            }
        $tab.="</tr>";
		$tab.="</thead><tbody>";


		ksort($blok);

        foreach($blok as $divisi => $arr1){
			foreach($arr1 as $blk => $val){
				$tab.="<tr class='rowcontent'>";
					$tab.="<td>".getNamaOrg($divisi)."</td>";
					$tab.="<td align=center>".$tahunTanam[$val]."</td>";

					if($jenis == 'Kecil'){
						$tab.="<td align=center>".$val." ( ".getNamaOrg($val)." )</td>";
					}else{
						$tab.="<td align=center>".$val." ( ".getIndukBlok($val)." )</td>";
					}
					
					$tab.="<td align=center>".number_format($Luas[$val],2)."</td>";
					$tab.="<td align=center>".number_format($Pokok[$val])."</td>";
					$tab.="<td align=center>".number_format($Pokok[$val]/$Luas[$val],2)."</td>";

					$ttluas[$divisi] += $Luas[$val];
					$ttpokok[$divisi] += $Pokok[$val];

					for ($bulan = 1; $bulan <= 12; $bulan++) {
						$tab.="<td align=center>".number_format($jjg[$val][$bulan],2)."</td>";
						$tab.="<td align=center>".number_format($kgwb[$val][$bulan],2)."</td>";
						$tab.="<td align=center>".number_format($kgwbnetto[$val][$bulan],2)."</td>";
						$tab.="<td align=center>".number_format(fixnan($kgwb[$val][$bulan]/$jjg[$val][$bulan]),2)."</td>";
						$tab.="<td align=center>".number_format(fixnan($kgwb[$val][$bulan]/$Luas[$val]),2)."</td>";

						$ttljjg[$divisi][$bulan] += $jjg[$val][$bulan];
						$ttlkgwb[$divisi][$bulan] += $kgwb[$val][$bulan];
						$ttlkgwbnetto[$divisi][$bulan] += $kgwbnetto[$val][$bulan];

						// $ttlbjr[$divisi][$bulan] += $kgwb[$val][$bulan]/$jjg[$val][$bulan];
						// $ttlkgha[$divisi][$bulan] += $kgwb[$val][$bulan]/$Luas[$val];
					}
				$tab.="</tr>";
			}
			$tab.="<tr class='rowcontent' style='background-color:cyan;'>";
				$tab.="<td align=center colspan =3><b> TOTAL ".getNamaOrg($divisi)." </b></td>";
				$tab.="<td align=center><b>".number_format($ttluas[$divisi],2)."</b></td>";
				$tab.="<td align=center><b>".number_format($ttpokok[$divisi],2)."</b></td>";
				$tab.="<td align=center><b>".number_format($ttpokok[$divisi]/$ttluas[$divisi],2)."</b></td>";

				$gtluas += $ttluas[$divisi];
				$gtpokok += $ttpokok[$divisi];

				for ($bulan = 1; $bulan <= 12; $bulan++) {
					$tab.="<td align=center><b>".number_format($ttljjg[$divisi][$bulan],2)."</b></td>";
					$tab.="<td align=center><b>".number_format($ttlkgwb[$divisi][$bulan],2)."</b></td>";
					$tab.="<td align=center><b>".number_format($ttlkgwbnetto[$divisi][$bulan],2)."</b></td>";

					$tab.="<td align=center><b>".number_format(fixnan($ttlkgwb[$divisi][$bulan]/$ttljjg[$divisi][$bulan]),2)."</b></td>";
					$tab.="<td align=center><b>".number_format(fixnan($ttlkgwb[$divisi][$bulan]/$ttluas[$divisi]),2)."</b></td>";

					$gtjjg[$bulan] += $ttljjg[$divisi][$bulan];
					$gtkgwb[$bulan] += $ttlkgwb[$divisi][$bulan];
					$gtkgwbnetto[$bulan] += $ttlkgwbnetto[$divisi][$bulan];

					// $gtbjr[$bulan] += $ttlbjr[$divisi][$bulan];
					// $gtkgha[$bulan] += $ttlkgha[$divisi][$bulan];
				}
			$tab.="</tr>";

			
		}

		$tab.="<tr class='rowcontent' style='background-color:orange;'>";
			$tab.="<td align=center colspan =3><b> GRAND TOTAL </b></td>";
			$tab.="<td align=center><b>".number_format($gtluas,2)."</b></td>";
			$tab.="<td align=center><b>".number_format($gtpokok,2)."</b></td>";
			$tab.="<td align=center><b>".number_format($gtpokok/$gtluas,2)."</b></td>";

			for ($bulan = 1; $bulan <= 12; $bulan++) {
				$tab.="<td align=center><b>".number_format($gtjjg[$bulan],2)."</b></td>";
				$tab.="<td align=center><b>".number_format($gtkgwb[$bulan],2)."</b></td>";
				$tab.="<td align=center><b>".number_format($gtkgwbnetto[$bulan],2)."</b></td>";

				$tab.="<td align=center><b>".number_format(fixnan($gtkgwb[$bulan]/$gtjjg[$bulan]),2)."</b></td>";
				$tab.="<td align=center><b>".number_format(fixnan($gtkgwb[$bulan]/$gtluas),2)."</b></td>";
			}

		$tab.="</tr>";

		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Sebaran_produksi".$unit."_".$periode;
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
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}
		
		$wh= "";
		if($tipe != 'Seluruhnya'){
			if($tipe == 'Inti'){
				$wh= "and intiplasma = 'I' ";
				
			}else{
				$wh= "and intiplasma = 'P' ";
			}
		}

		$wherex .= "and substr(b.blok,1,4) in (".getOrgDetail(2).")";
		
		$str = "select b.*,a.tahuntanam,a.intiplasma,MONTH(b.tanggalpanen) AS bulan 
				from ".$dbname.".setup_blok a 
				left join ".$dbname.".kebun_spbdt_detail b on a.kodeorg=b.blok 
				where date_format(b.tanggalpanen,'%Y') = '".$periode."' and a.tahuntanam != 0 ".$wh." ".$wherex."
				order by a.tahuntanam, a.intiplasma asc";
		$res = fetchdata($str);
        foreach($res as $val){
				$tahunTanam[$val['intiplasma']][substr($val['blok'],0,6)][$val['tahuntanam']]= $val['tahuntanam'];

				$jjg[$val['intiplasma']][substr($val['blok'],0,6)][$val['tahuntanam']][$val['bulan']] += $val['jjg'];
				$kgwb[$val['intiplasma']][substr($val['blok'],0,6)][$val['tahuntanam']][$val['bulan']] += $val['kgwb'];
				$kgwbnetto[$val['intiplasma']][substr($val['blok'],0,6)][$val['tahuntanam']][$val['bulan']] += $val['kgwbnetto'];
		}


		$str = "select * from ".$dbname.".setup_blok ";
		$res = fetchdata($str);
        foreach($res as $val){
			$Luas[$val['intiplasma']][substr($val['kodeorg'],0,6)][$val['tahuntanam']]+= $val['luasareaproduktif'];
			$Pokok[$val['intiplasma']][substr($val['kodeorg'],0,6)][$val['tahuntanam']]+= $val['jumlahpokok'];
		}

		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='3'> DIV </th>
				<th rowspan='3'> TT </th>
				<th rowspan='3'> LUAS (HA) </th>
				<th rowspan='3'> JUMLAH (POKOK) </th>
				<th rowspan='3'> SPH </th>
				<th colspan='60'>Periode ".$periode."</th>";
		$tab.="</tr>";
            
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $tab.="<th colspan='5'>".numToMonth($bulan,'I','long')."</th>";
            }
		$tab.="</tr>";
        $tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $tab.="<th>TDN</th>";
                $tab.="<th>KG BRUTO</th>";
                $tab.="<th>KG NETTO</th>";
                $tab.="<th>BJR</th>";
                $tab.="<th>KG/HA</th>";
            }
        $tab.="</tr>";
		$tab.="</thead><tbody>";

		ksort($tahunTanam);


		foreach($tahunTanam as $ip => $arr1){
			foreach($arr1 as $divisi => $arr2){
				foreach($arr2 as $thtanam => $value){
					$tab.="<tr class='rowcontent'>";
						$tab.="<td align=center>".getNamaOrg($divisi)."</td>";
						$tab.="<td align=center>".$thtanam."</td>";
						$tab.="<td align=center>".number_format($Luas[$ip][$divisi][$thtanam],2)."</td>";
						$tab.="<td align=center>".number_format($Pokok[$ip][$divisi][$thtanam])."</td>";
						$tab.="<td align=center>".number_format($Pokok[$ip][$divisi][$thtanam]/$Luas[$ip][$divisi][$thtanam],2)."</td>";

						$ttluas[$ip][$divisi] += $Luas[$ip][$divisi][$thtanam];
						$ttpokok[$ip][$divisi] += $Pokok[$ip][$divisi][$thtanam];
						
						for ($bulan = 1; $bulan <= 12; $bulan++) {
							$tab.="<td align=center>".number_format($jjg[$ip][$divisi][$thtanam][$bulan],2)."</td>";
							$tab.="<td align=center>".number_format($kgwb[$ip][$divisi][$thtanam][$bulan],2)."</td>";
							$tab.="<td align=center>".number_format($kgwbnetto[$ip][$divisi][$thtanam][$bulan],2)."</td>";
							$tab.="<td align=center>".number_format(fixnan($kgwb[$ip][$divisi][$thtanam][$bulan]/$jjg[$ip][$divisi][$thtanam][$bulan]),2)."</td>";
							$tab.="<td align=center>".number_format(fixnan($kgwb[$ip][$divisi][$thtanam][$bulan]/$Luas[$ip][$divisi][$thtanam]),2)."</td>";

							$ttljjg[$ip][$divisi][$bulan] += $jjg[$ip][$divisi][$thtanam][$bulan];
							$ttlkgwb[$ip][$divisi][$bulan] += $kgwb[$ip][$divisi][$thtanam][$bulan];
							$ttlkgwbnetto[$ip][$divisi][$bulan] += $kgwbnetto[$ip][$divisi][$thtanam][$bulan];

							// $ttlbjr[$ip][$divisi][$bulan] += $kgwb[$ip][$divisi][$thtanam][$bulan]/$jjg[$ip][$divisi][$thtanam][$bulan];
							// $ttlkgha[$ip][$divisi][$bulan] += $kgwb[$ip][$divisi][$thtanam][$bulan]/$Luas[$ip][$divisi][$thtanam];
						}
					$tab.="</tr>";
				}

							$tab.="<tr class='rowcontent' style='background-color:cyan;'>";
							$tab.="<td align=center colspan =2><b> TOTAL ".getNamaOrg($divisi)." </b></td>";
							$tab.="<td align=center><b>".number_format($ttluas[$ip][$divisi],2)."</b></td>";
							$tab.="<td align=center><b>".number_format($ttpokok[$ip][$divisi],2)."</b></td>";
							$tab.="<td align=center><b>".number_format($ttpokok[$ip][$divisi]/$ttluas[$ip][$divisi],2)."</b></td>";

							$gtluas[$ip] += $ttluas[$ip][$divisi];
							$gtpokok[$ip] += $ttpokok[$ip][$divisi];

							for ($bulan = 1; $bulan <= 12; $bulan++) {
								$tab.="<td align=center><b>".number_format($ttljjg[$ip][$divisi][$bulan],2)."</b></td>";
								$tab.="<td align=center><b>".number_format($ttlkgwb[$ip][$divisi][$bulan],2)."</b></td>";
								$tab.="<td align=center><b>".number_format($ttlkgwbnetto[$ip][$divisi][$bulan],2)."</b></td>";

								$tab.="<td align=center><b>".number_format(fixnan($ttlkgwb[$ip][$divisi][$bulan]/$ttljjg[$ip][$divisi][$bulan]),2)."</b></td>";
								$tab.="<td align=center><b>".number_format(fixnan($ttlkgwb[$ip][$divisi][$bulan]/$ttluas[$ip][$divisi]),2)."</b></td>";
								
								$gtjjg[$ip][$bulan] += $ttljjg[$ip][$divisi][$bulan];
								$gtkgwb[$ip][$bulan] += $ttlkgwb[$ip][$divisi][$bulan];
								$gtkgwbnetto[$ip][$bulan] += $ttlkgwbnetto[$ip][$divisi][$bulan];

								// $gtbjr[$ip][$bulan] += $ttlbjr[$ip][$divisi][$bulan];
								// $gtkgha[$ip][$bulan] += $ttlkgha[$ip][$divisi][$bulan];

							}
						$tab.="</tr>";
					}

					if($ip == "I"){
						$tipe = "INTI";
					}else{
						$tipe = "PLASMA";
					}

					$tab.="<tr class='rowcontent' style='background-color:orange;'>";
						$tab.="<td align=center colspan =2><b>TOTAL (".$tipe.") </b></td>";
						$tab.="<td align=center><b>".number_format($gtluas[$ip],2)."</b></td>";
						$tab.="<td align=center><b>".number_format($gtpokok[$ip],2)."</b></td>";
						$tab.="<td align=center><b>".number_format($gtpokok[$ip]/$gtluas[$ip],2)."</b></td>";

						for ($bulan = 1; $bulan <= 12; $bulan++) {
							$tab.="<td align=center><b>".number_format($gtjjg[$ip][$bulan],2)."</b></td>";
							$tab.="<td align=center><b>".number_format($gtkgwb[$ip][$bulan],2)."</b></td>";
							$tab.="<td align=center><b>".number_format($gtkgwbnetto[$ip][$bulan],2)."</b></td>";

							$tab.="<td align=center><b>".number_format(fixnan($gtkgwb[$ip][$bulan]/$gtjjg[$ip][$bulan]),2)."</b></td>";
							$tab.="<td align=center><b>".number_format(fixnan($gtkgwb[$ip][$bulan]/$gtluas[$ip]),2)."</b></td>";
						}
					$tab.="</tr>";
			}
				
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Sebaran_produksi".$unit."_".$periode;
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