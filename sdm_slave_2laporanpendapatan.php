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
$pendapatan=checkPostGet('pendapatan','');

$tanggal=checkPostGet('tanggal','');
$karyawanid=checkPostGet('karyawanid','');
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

        if($pendapatan != "all"){
            $where = "and idkomponen = '".$pendapatan."'";
        }



		$tab="";

		## pendapatan HT
        $str = "select * from ".$dbname.".sdm_pendapatanlainht where kodeorg='".$unit."' and periodegaji= '".$periode."'  ".$where."";
        $res = fetchdata($str);
        foreach($res as $val){
            $kodeKomponen[$val['idkomponen']] = $val['idkomponen'];
        }

        ## pendapatan DT
        $str = "select * from ".$dbname.".sdm_pendapatanlaindt where kodeorg='".$unit."' and periodegaji= '".$periode."' ".$where."";
        $res = fetchdata($str);
        $arrkary=$res;
        foreach($res as $val){
            $arrdata[$val['karyawanid']] = $val['karyawanid'];
            $jumlahkom[$val['karyawanid']][$val['idkomponen']] = $val['jumlah'];
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
				<th rowspan='2'>".$_SESSION['lang']['karyawanid']."</th>
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
			$nop_="Laporan_Pendapatan_".$unit."_".$periode;
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