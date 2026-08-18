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
$kodeorgnya=checkPostGet('kodeorgnya','');
$subunit=checkPostGet('subunit','');
$periode=checkPostGet('periode','');
$tipekaryawan=checkPostGet('tipekaryawan','');
$potongan=checkPostGet('potongan','');

$tanggal=checkPostGet('tanggal','');
$nik=checkPostGet('nik','');
$karyawanid=checkPostGet('karyawanid','');

switch($method){
	case'getsubunit':
		$optSubUnit="<option value='all'>".$_SESSION['lang']['all']."</option>";
		$optSubUnit.="<option value=''>".$unit." - Kantor</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;
	
	case'preview':

        if($potongan != "all"){
            $where = "and tipepotongan = '".$potongan."'";
        }

		$tab="";

		## Potongan HT
        $str = "select * from ".$dbname.".sdm_potonganht where 1=1 and periodegaji= '".$periode."' and kodeorg= '".$unit."' ".$where."";
        $res = fetchdata($str);
        foreach($res as $val){
            $kodeKomponen[$val['tipepotongan']] = $val['tipepotongan'];
        }

        ## Potongan DT
        $str = "select * from ".$dbname.".sdm_potongandt where 1=1 and periodegaji= '".$periode."' and kodeorg= '".$unit."' ".$where."";
        $res = fetchdata($str);
        $arrkary=$res;
        foreach($res as $val){
            $arrdata[$val['nik']] = $val['nik'];
            $jumlahkom[$val['nik']][$val['tipepotongan']] = $val['jumlahpotongan'];
        }

        $colspn = count($kodeKomponen);
        
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='2'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='2'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='2'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='2'>".$_SESSION['lang']['jabatan']."</th>
				<th colspan='".$colspn."'>PERIODE ".$periode."</th>
				<th rowspan='2'>".$_SESSION['lang']['total']."</th>";
		$tab.="</tr>";
		$tab.="<tr>";
            foreach($kodeKomponen as $bar){	
                $tab.=" <th >".getNamaKomponenGaji($bar)."</th>";
            }
		$tab.="</tr>";
		$tab.="</thead><tbody>";
		$no=0;
            foreach($arrdata as $karid => $value){
                    $no++;
                    $tab.="<tr class='rowcontent'>";
                    $tab.="<td align='center'>".$no."</td>";
                    $tab.="<td >".getNik($karid)."</td>";
                    $tab.="<td >".getNamaKaryawan($karid)."</td>";
                    $tab.="<td >".getJabatanKaryawan($karid)."</td>";

                    foreach($kodeKomponen as $val){
                        $tab.="<td align=right>".number_format($jumlahkom[$karid][$val])."</td>";
                        $ttlkom[$val]+=$jumlahkom[$karid][$val];
                        $ttlkar[$karid] +=$jumlahkom[$karid][$val];
                        $gtttl +=$jumlahkom[$karid][$val];
                    }

                    $tab.="<td align=right>".number_format($ttlkar[$karid])."</td>";
            }
            $tab.="<tr class='rowcontent'>";
                $tab.="<td align=center colspan=4><b>TOTAL</b></td>";
                foreach($kodeKomponen as $val){
                    $tab.="<td align=center><b>".number_format($ttlkom[$val])."</b></td>";
                }
                $tab.="<td align=center><b>".number_format($gtttl)."</b></td>";
            $tab.="</tr>";
        $tab.="</tr>";

		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_Potongan_".$unit."_".$periode;
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