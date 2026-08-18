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
		
		$gettglawal=$periode."-01";
		$gettglakhir=tglakhir($periode);
		$bulan=tanggalbulan($periode);
		$exptglakhir=explode('-',$gettglakhir);
		$tglawal='01';
		$tglakhir=$exptglakhir[2];

		$rangetgl = rangeTanggalarr($gettglawal,$gettglakhir);
		
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

        $str="select * from ".$dbname.".kebun_curahhujan where 1=1 and tanggal like '%".$periode."%'  ".$wh." order by tanggal";
		$res=fetchdata($str);
		foreach($res as $val){
            $pagi[$val['kodeorg']][$val['tanggal']]  = $val['pagi'];
            $siang[$val['kodeorg']][$val['tanggal']] = $val['siang'];
            $sore[$val['kodeorg']][$val['tanggal']]  = $val['sore'];
        }

		$colspn=count($kodedivisi)*4;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['tanggal']."</th>
				<th colspan='".$colspn."'>PERIODE ".$periode."</th>";
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
		foreach($rangetgl as $tgl){
			$tab.="<tr class='rowcontent'>";
			    $tab.="<td align=center width=50px>". substr($tgl,8,2)."</td>";
                foreach($kodedivisi as $divisi){	
                    $tab.="<td align=center width=50px>".number_format($pagi[$divisi][$tgl],2)."</td>";
                    $tab.="<td align=center width=50px>".number_format($siang[$divisi][$tgl],2)."</td>";
                    $tab.="<td align=center width=50px>".number_format($sore[$divisi][$tgl],2)."</td>";
                    $tab.="<td align=center width=50px>".number_format($pagi[$divisi][$tgl]+$siang[$divisi][$tgl]+$sore[$divisi][$tgl],2)."</td>";

                    $ttpagi[$divisi]  += $pagi[$divisi][$tgl];
                    $ttsiang[$divisi] += $siang[$divisi][$tgl];
                    $ttsore[$divisi]  += $sore[$divisi][$tgl];
                    $tttotal[$divisi] += $pagi[$divisi][$tgl]+$siang[$divisi][$tgl]+$sore[$divisi][$tgl];

                    if($pagi[$divisi][$tgl] != '' || $pagi[$divisi][$tgl] != 0 || $siang[$divisi][$tgl] != '' || $siang[$divisi][$tgl] != 0 || $sore[$divisi][$tgl] != '' || $sore[$divisi][$tgl] != 0 ){
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