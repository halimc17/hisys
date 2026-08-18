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
$pt = checkPostGet('pt', '');
$pt2 = checkPostGet('pt2', '');
$periode = checkPostGet('periode', '');
$intiplasma = checkPostGet('intiplasma', '');

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
	
	case 'preview':
        $tab = "";
        $bulan = tanggalbulan($periode); 
        $expP = explode("-", $periode);
        $thnShort = substr($expP[0], 2, 2);
        $namaBulanShort = $bulan; 

        $gettglawal = $periode . "-01";
        $gettglakhir = tglakhir($periode);
        $rangetgl = rangeTanggalarr($gettglawal, $gettglakhir);
        
        $wh = "";
        $join = "";
		if($pt != ""){
            $pt_arr = explode(',', $pt);
            $pt_wh = [];
            foreach($pt_arr as $p) {
                if($p != '') $pt_wh[] = "a.kodeorg like '".$p."%'";
            }
            if(!empty($pt_wh)) {
                $wh .= " and (".implode(' OR ', $pt_wh).")";
            }
		}

        if($intiplasma != ""){
            $join = " left join ".$dbname.".organisasi b on a.kodeorg = b.kodeorganisasi";
            $wh .= " and b.inti = '".$intiplasma."'";
        }
        
        $strH = "select a.* from " . $dbname . ".kebun_curahhujan a ".$join." where a.tanggal like '" . $periode . "%' " . $wh . " order by a.tanggal";
        $resH = fetchdata($strH);
        
        $dataH = [];
        foreach ($resH as $val) {
            $tgl = $val['tanggal'];
            $dataH[$tgl]['p_mm'] += $val['pagi'];
            $dataH[$tgl]['s_mm'] += $val['siang'];
            $dataH[$tgl]['m_mm'] += $val['sore'];
            
            $p_time = explode(' ', $val['jampagi'] ?? '00:00:00')[1] ?? ($val['jampagi'] ?? '00:00:00');
            $p_t = explode(':', $p_time);
            $dataH[$tgl]['p_sec'] += (int)($p_t[0]*3600) + (int)(($p_t[1] ?? 0)*60) + (int)($p_t[2] ?? 0);
            
            $s_time = explode(' ', $val['jamsiang'] ?? '00:00:00')[1] ?? ($val['jamsiang'] ?? '00:00:00');
            $s_t = explode(':', $s_time);
            $dataH[$tgl]['s_sec'] += (int)($s_t[0]*3600) + (int)(($s_t[1] ?? 0)*60) + (int)($s_t[2] ?? 0);
            
            $m_time = explode(' ', $val['jamsore'] ?? '00:00:00')[1] ?? ($val['jamsore'] ?? '00:00:00');
            $m_t = explode(':', $m_time);
            $dataH[$tgl]['m_sec'] += (int)($m_t[0]*3600) + (int)(($m_t[1] ?? 0)*60) + (int)($m_t[2] ?? 0);
            
            $dataH[$tgl]['count'] = ($dataH[$tgl]['count'] ?? 0) + 1;
        }

        $tab = "<table cellpadding=5 cellspacing=1 border=1 class=sortable style='border-collapse:collapse; width:100%'>
                <thead>
                    <tr class=rowheader style='text-align:center; font-weight:bold; background-color:#7dffe2;'>
                        <th colspan=10>".strtoupper($bulan)." </th>
                    </tr>
                    <tr class=rowheader style='text-align:center; font-weight:bold; background-color:#7dffe2;'>
                        <th rowspan=3>Tanggal</th>
                        <th colspan=2>PAGI</th><th colspan=2>SORE</th><th colspan=2>MALAM</th><th colspan=3>TOTAL</th>
                    </tr>
                    <tr class=rowheader style='text-align:center; font-weight:bold; background-color:#7dffe2;'>
                        <th colspan=2>06.00 - 12.00</th><th colspan=2>12.00 - 18.00</th><th colspan=2>18.00 - 06.00</th>
                        <th rowspan=2>Jam</th><th rowspan=2>HH</th><th rowspan=2>CH (mm)</th>
                    </tr>
                    <tr class=rowheader style='text-align:center; font-weight:bold; background-color:#7dffe2;'>
                        <th>Jml Jam</th>
						<th>mm</th>

						<th>Jml Jam</th>
						<th>mm</th>
						
						<th>Jml Jam</th>
						<th>mm</th>
                    </tr>
                </thead><tbody>";

        $gt_p = 0; $gt_s = 0; $gt_m = 0; $gt_ch = 0; $gt_hh = 0;
        $tot_p_sec = 0; $tot_s_sec = 0; $tot_m_sec = 0; 

        foreach ($rangetgl as $tgl) {
            $d = $dataH[$tgl] ?? null;
            $cnt = $d['count'] ?? 1;
            
            // Average mm across entries for this date
            $p_mm = ($d['p_mm'] ?? 0) / $cnt;
            $s_mm = ($d['s_mm'] ?? 0) / $cnt;
            $m_mm = ($d['m_mm'] ?? 0) / $cnt;
            $total_mm = $p_mm + $s_mm + $m_mm;
            
            // Average seconds across entries for this date
            $p_sec = ($d['p_sec'] ?? 0) / $cnt;
            $s_sec = ($d['s_sec'] ?? 0) / $cnt;
            $m_sec = ($d['m_sec'] ?? 0) / $cnt;
            
            $p_jam = floor($p_sec/3600) . ":" . str_pad(floor(($p_sec%3600)/60), 2, "0", STR_PAD_LEFT) . ":" . str_pad(($p_sec%60), 2, "0", STR_PAD_LEFT);
            $s_jam = floor($s_sec/3600) . ":" . str_pad(floor(($s_sec%3600)/60), 2, "0", STR_PAD_LEFT) . ":" . str_pad(($s_sec%60), 2, "0", STR_PAD_LEFT);
            $m_jam = floor($m_sec/3600) . ":" . str_pad(floor(($m_sec%3600)/60), 2, "0", STR_PAD_LEFT) . ":" . str_pad(($m_sec%60), 2, "0", STR_PAD_LEFT);

            $day_tot_sec = $p_sec + $s_sec + $m_sec;
            $display_jam_hari = floor($day_tot_sec/3600) . ":" . str_pad(floor(($day_tot_sec%3600)/60), 2, "0", STR_PAD_LEFT) . ":" . str_pad(($day_tot_sec%60), 2, "0", STR_PAD_LEFT);

            $bg = ($total_mm == 0) ? "style='background-color:yellow;'" : "";
            $tglDisplay = substr($tgl, 8, 2) . " -" . $namaBulanShort;
            $hh = ($total_mm > 0) ? "1.00" : "0.00";
            $hh_mm = $total_mm;

            $tab .= "<tr class='rowcontent' ".$bg.">
                     <td align=center>".$tglDisplay."</td>
                     <td align=center>".$p_jam."</td><td align=center>".number_format($p_mm, 1)."</td>
                     <td align=center>".$s_jam."</td><td align=center>".number_format($s_mm, 1)."</td>
                     <td align=center>".$m_jam."</td><td align=center>".number_format($m_mm, 1)."</td>
                     <td align=center>".$display_jam_hari."</td><td align=center>".$hh."</td>
                     <td align=center>".number_format($hh_mm, 2)."</td>
                     </tr>";
            
            $gt_p += $p_mm; $gt_s += $s_mm; $gt_m += $m_mm; $gt_ch += $hh_mm;
            $tot_p_sec += $p_sec; $tot_s_sec += $s_sec; $tot_m_sec += $m_sec;
            if($hh_mm > 0) $gt_hh += 1;
        }

        $grand_tot_sec = $tot_p_sec + $tot_s_sec + $tot_m_sec;

        $tab .= "<tr class='rowcontent' style='font-weight:bold; background-color:#7dffe2;'>
                 <td align=center>Jumlah</td>
                 <td align=center>".floor($tot_p_sec/3600).":".str_pad(floor(($tot_p_sec%3600)/60), 2, "0", STR_PAD_LEFT).":".str_pad(($tot_p_sec%60), 2, "0", STR_PAD_LEFT)."</td>
                 <td align=center>".number_format($gt_p, 2)."</td>
                 <td align=center>".floor($tot_s_sec/3600).":".str_pad(floor(($tot_s_sec%3600)/60), 2, "0", STR_PAD_LEFT).":".str_pad(($tot_s_sec%60), 2, "0", STR_PAD_LEFT)."</td>
                 <td align=center>".number_format($gt_s, 2)."</td>
                 <td align=center>".floor($tot_m_sec/3600).":".str_pad(floor(($tot_m_sec%3600)/60), 2, "0", STR_PAD_LEFT).":".str_pad(($tot_m_sec%60), 2, "0", STR_PAD_LEFT)."</td>
                 <td align=center>".number_format($gt_m, 2)."</td>
                 <td align=center>".floor($grand_tot_sec/3600).":".str_pad(floor(($grand_tot_sec%3600)/60), 2, "0", STR_PAD_LEFT).":".str_pad(($grand_tot_sec%60), 2, "0", STR_PAD_LEFT)."</td>
                 <td align=center>".number_format($gt_hh, 2)."</td>
                 <td align=center>".number_format($gt_ch, 2)."</td>
                 </tr>";

        $tab .= "<tr class='rowcontent' style='font-weight:bold; background-color:#e2ffdb;'>
                 <td align=center>Presentase</td>
                 <td align=center>".($grand_tot_sec > 0 ? number_format(($tot_p_sec/$grand_tot_sec)*100, 2) : "0.00")." %</td>
                 <td align=center>".($gt_ch > 0 ? number_format(($gt_p/$gt_ch)*100, 2) : "0.00")." %</td>
                 <td align=center>".($grand_tot_sec > 0 ? number_format(($tot_s_sec/$grand_tot_sec)*100, 2) : "0.00")." %</td>
                 <td align=center>".($gt_ch > 0 ? number_format(($gt_s/$gt_ch)*100, 2) : "0.00")." %</td>
                 <td align=center>".($grand_tot_sec > 0 ? number_format(($tot_m_sec/$grand_tot_sec)*100, 2) : "0.00")." %</td>
                 <td align=center>".($gt_ch > 0 ? number_format(($gt_m/$gt_ch)*100, 2) : "0.00")." %</td>
                 <td align=center>100 %</td>
				 <td align=center style='font-size:10px'>Rata2/Hari</td><td align=center>".number_format($gt_ch/count($rangetgl), 2)."</td>
                 </tr></tbody></table>";

        if ($pt != "") {
            $pt_arr_disp1 = explode(',', $pt);
            $pt_names1 = [];
            foreach($pt_arr_disp1 as $p_disp1) {
                $pt_names1[] = getNamaOrg($p_disp1);
            }
			$judulPT1 = implode(', ', $pt_names1);
		} else {
			$judulPT1 = "Data Curah Hujan Seluruh PT";
		}

        $tab = "<h3>CURAH HUJAN HARIAN ".$judulPT1."</h3>
                <p><b>Bulan : ".$bulan."</b></p>".$tab;

        if ($tipeprint == 'html') {
            echo $tab;
        } else {
            $nop = "Curah_Hujan_Harian_" . $periode . ".xls";
            include_once('lib/HtmlExcel.php');
            $xls = new HtmlExcel();
            $xls->addSheet("Harian", $tab);
            $xls->headers($nop);
            echo $xls->buildFile();
        }
    break;

	case 'preview2':
        $tab = "";
        $bulanSekarang = tanggalbulan(date('Y-m')); 
        $tahunSekarang = date('Y');

        $wh = "";
        $join = "";
		if($pt2 != ""){
            $pt_arr2 = explode(',', $pt2);
            $pt_wh2 = [];
            foreach($pt_arr2 as $p) {
                if($p != '') $pt_wh2[] = "a.kodeorg like '".$p."%'";
            }
            if(!empty($pt_wh2)) {
                $wh .= " and (".implode(' OR ', $pt_wh2).")";
            }
		}

        if($intiplasma != ""){
            $join = " left join ".$dbname.".organisasi b on a.kodeorg = b.kodeorganisasi";
            $wh .= " and b.inti = '".$intiplasma."'";
        }
        
        $strMin = "SELECT MIN(YEAR(a.tanggal)) as thn_awal FROM ".$dbname.".kebun_curahhujan a
                   ".$join." where 1=1 ".$wh." ";
        // exit('error:' . $strMin);
		$resMin = fetchData($strMin);
        $tahunAwal = (!empty($resMin[0]['thn_awal'])) ? $resMin[0]['thn_awal'] : $tahunSekarang;

        $listTahun = [];
        for ($t = $tahunAwal; $t <= $tahunSekarang; $t++) {
            $listTahun[] = $t;
        }

        $str = "SELECT thn, bln, SUM(total_per_hari) as total_mm, COUNT(CASE WHEN total_per_hari > 0 THEN 1 END) as jml_hh
				FROM ( SELECT 
					YEAR(a.tanggal) as thn, 
					MONTH(a.tanggal) as bln, 
					a.tanggal,
					SUM(a.pagi+a.siang+a.sore) as total_per_hari
				FROM ".$dbname.".kebun_curahhujan a
					".$join."
                    where 1=1 ".$wh."
					GROUP BY a.tanggal
				) as per_tanggal
				GROUP BY thn, bln";
		// exit("error:".$str);
        
        $res = fetchData($str);
        $dataTahun = [];
        foreach ($res as $val) {
            $dataTahun[$val['thn']][$val['bln']] = [
                'mm' => $val['total_mm'],
                'hh' => $val['jml_hh']
            ];
        }

		if ($pt2 != "") {
            $pt_arr_disp2 = explode(',', $pt2);
            $pt_names2 = [];
            foreach($pt_arr_disp2 as $p_disp2) {
                $pt_names2[] = getNamaOrg($p_disp2);
            }
			$judulPT2 = implode(', ', $pt_names2);
		} else {
			$judulPT2 = "Data Curah Hujan Seluruh PT";
		}

        $tab .= " 
                <h3> CURAH HUJAN BULANAN: ".$judulPT2."</h3>
                <p><b>Bulan : ".$bulanSekarang."</b></p>
                <table cellpadding=5 cellspacing=1 border=1 class=sortable style='border-collapse:collapse; width:100%'>
                <thead>
                    <tr class=rowheader style='text-align:center; font-weight:bold; background-color:#7dffe2;'>
                        <th rowspan='2' bgcolor='#fff'>BULAN</th>";
        
        foreach ($listTahun as $thn) {
            $tab .= "<th colspan='2'>".$thn."</th>";
        }
        $tab .= "<th colspan='2' bgcolor='#99ff99'>RATA-RATA</th></tr>";

        $tab .= "<tr class=rowheader style='text-align:center; font-weight:bold; background-color:#7dffe2;'>";
        foreach ($listTahun as $thn) {
            $tab .= "<th>m.m</th><th>days</th>";
        }
        $tab .= "<th bgcolor='#99ff99'>m.m</th><th bgcolor='#99ff99'>days</th></tr>";
        $tab .= "</thead><tbody>";

        $gt_tahun = []; 
        for ($m = 1; $m <= 12; $m++) {
            $tab .= "<tr class='rowcontent'>
                     <td style='font-weight:bold;'>".strtoupper(numToMonth($m, 'I', 'long'))."</td>";
            
            $rowMm = 0; $rowHh = 0;
            foreach ($listTahun as $thn) {
                $valMm = isset($dataTahun[$thn][$m]['mm']) ? $dataTahun[$thn][$m]['mm'] : 0;
                $valHh = isset($dataTahun[$thn][$m]['hh']) ? $dataTahun[$thn][$m]['hh'] : 0;
                
                $tab .= "<td align=center>".number_format($valMm, 2)."</td>";
                $tab .= "<td align=center>".number_format($valHh, 0)."</td>";
                
                $rowMm += $valMm;
                $rowHh += $valHh;

                $gt_tahun[$thn]['mm'] += $valMm;
                $gt_tahun[$thn]['hh'] += $valHh;
            }
         
            $avgMm = $rowMm / count($listTahun);
            $avgHh = $rowHh / count($listTahun);
            
            $tab .= "<td align=center bgcolor='#e2ffdb'><b>".number_format($avgMm, 2)."</b></td>";
            $tab .= "<td align=center bgcolor='#e2ffdb'><b>".number_format($avgHh, 2)."</b></td>";
            $tab .= "</tr>";
        }

        $tab .= "<tr class='rowcontent' style='font-weight:bold; background-color:#7dffe2;'>
                 <td align=center>TOTAL</td>";
        
        $allTotalMm = 0; $allTotalHh = 0;
        foreach ($listTahun as $thn) {
            $tab .= "<td align=center>".number_format($gt_tahun[$thn]['mm'], 2)."</td>";
            $tab .= "<td align=center>".number_format($gt_tahun[$thn]['hh'], 0)."</td>";
            $allTotalMm += $gt_tahun[$thn]['mm'];
            $allTotalHh += $gt_tahun[$thn]['hh'];
        }
        
        $avgAllMm = $allTotalMm / count($listTahun);
        $avgAllHh = $allTotalHh / count($listTahun);
        
        $tab .= "<td align=center bgcolor='#99ff99'>".number_format($avgAllMm, 2)."</td>";
        $tab .= "<td align=center bgcolor='#99ff99'>".number_format($avgAllHh, 2)."</td>";
        $tab .= "</tr></tbody></table>";

        if ($tipeprint == 'html') {
            echo $tab;
        } else {
            $nop = "Curah_Hujan_Bulanan.xls";
            include_once('lib/HtmlExcel.php');
            $xls = new HtmlExcel();
            $xls->addSheet("Bulanan", $tab);
            $xls->headers($nop);
            echo $xls->buildFile();
        }

		$labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agt", "Sep", "Okt", "Nov", "Des"];
		$datasets = [];
		$colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#f680ff', '#ff8000', '#8000ff', '#00ff80', '#0000ff', '#ff0000'];
		
		foreach ($listTahun as $key => $thn) {
			$dataMm = [];
			for ($m = 1; $m <= 12; $m++) {
				$dataMm[] = (float)($dataTahun[$thn][$m]['mm'] ?? 0);
			}
			$datasets[] = [
				'label' => 'Thn ' . $thn,
				'data' => $dataMm,
				'borderColor' => $colors[$key % count($colors)],
				'backgroundColor' => 'transparent',
				'borderWidth' => ($thn == $tahunSekarang) ? 4 : 2,
				'tension' => 0.2
			];
		}
		
		echo "<input type='hidden' id='dataGrafik' value='".json_encode($datasets)."'>";
		echo "<input type='hidden' id='labelGrafik' value='".json_encode($labels)."'>";
    break;
}

?>