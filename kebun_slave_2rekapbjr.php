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


switch($method){
	case'preview':
        
        ## Ambil dari kebun_spbdt_detail
        $str1 = "select * from ".$dbname.".kebun_spbdt_detail where tanggalpanen like '%".$periode."%' and blok like '%".$unit."%' order by blok";
		$res1 = fetchdata($str1);
        foreach($res1 as $val){
            $indukblok[$val['blok']] = $val['blok'];
            $totalKG[$val['blok']] += $val['totalkg'];
            $jjg[$val['blok']] += $val['jjg'];
        }

    


        $tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:70%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['divisi']."</th>
				<th>".$_SESSION['lang']['blok']."</th>
				<th>".$_SESSION['lang']['tahuntanam']."</th>
				<th>JJG</th>
				<th>KG</th>
				<th>BJR</th>";
        $tab.="</thead><tbody>";

        $no=0;
        $gtJg=0;
        $gtKg=0;
		foreach($indukblok as $bar => $val ){
            $no++;
			$tab.="<tr class='rowcontent'>";
            $tab.="<td align='center'>".$no."</td>";
            $tab.="<td align='center'>".getNamaOrg(substr($bar,0,6))."</td>";
            $tab.="<td align='center'>".getNamaOrg($bar)."</td>";
            $tab.="<td align='center'>".getBlok($bar,'tahuntanam')."</td>";
            $tab.="<td align='right'>".number_format($jjg[$bar])."</td>";
            $tab.="<td align='right'>".number_format($totalKG[$bar])."</td>";
            $tab.="<td align='right'>".number_format(fixnan($totalKG[$bar]/$jjg[$bar]),2)."</td>";
            $gtJg+=$jjg[$bar];
            $gtKg+=$totalKG[$bar];
        }

        $tab.="<tr class='rowcontent'>";
            $tab.="<td align='center' colspan=4 ><b>TOTAL</b></td>";
            $tab.="<td align='center' ><b>".number_format($gtJg,2)."</b></td>";
            $tab.="<td align='center' ><b>".number_format($gtKg,2)."</b></td>";
            $tab.="<td align='center'></td>";


        $tab.="<tr>";


		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_RekapBJR_".$unit."_".$periode;
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