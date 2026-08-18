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

		
		$tab="";
		
		$gettglawal=$periode."-01";
		$gettglakhir=tglakhir($periode);
		$bulan=tanggalbulan($periode);
		$exptglakhir=explode('-',$gettglakhir);
		$tglawal='01';
		$tglakhir=$exptglakhir[2];

		$rangetgl = rangeTanggalarr($gettglawal,$gettglakhir);
		
		$where="";
		if($subunit=='all'){
			$where.="";
		}else if($subunit==''){
			$where.=" and subbagian=''";
		}else{
			$where.=" and subbagian='".$subunit."'";
		}

        if($tipekaryawan!='all'){
			$where.=" and tipekaryawan='".$tipekaryawan."'";
        }



		$where.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$gettglakhir."')";
		$where.= " and tanggalmasuk<='".$gettglakhir."'";
	
		$dakarbulanan=0;
        $str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."' ";
        $res = fetchdata($str);
        if(count($res)>0){ 
            $dakarbulanan=1;
        }

        if($dakarbulanan == 1){
            $str = "select karyawanid,nik,namakaryawan,kodejabatan from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."' ".$where." order by namakaryawan asc ";
            $res = fetchdata($str);
		    $arrkary=$res;
        }else{
            $str="select karyawanid,nik,namakaryawan,kodejabatan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." order by namakaryawan asc";
            $res=fetchdata($str);
            $arrkary=$res;
        }

        ## Get Premi
        $arrpremi=array();
        $str = "select * from ".$dbname.".vhc_runhk where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res = fetchdata($str);
        foreach($res as $val){
            $arrpremi[$val['idkaryawan']][$val['tanggal']] = $val['premi'];
        }

        ## Get Lembur
        $arrlembur=array();
        $str = "select * from ".$dbname.".sdm_lemburdt where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res = fetchdata($str);
        foreach($res as $val){
            $arrlembur[$val['karyawanid']][$val['tanggal']] = round($val['uangkelebihanjam']);
        }

         ## Get PJD
         $arrpjd=array();
         $str = "select * from ".$dbname.".sdm_pjdinasdt where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
         $res = fetchdata($str);
         foreach($res as $val){
             $arrpjd[$val['piclokasi']][$val['tanggal']] = 1;
         }
		

		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$colspn=$tglakhir*3;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th colspan='".$colspn."'>Periode ".$periode."</th>
				<th rowspan='3'>Total Premi</th>
				<th rowspan='3'>Total Lembur</th>
				<th rowspan='3'>Total</th>
			</tr>";
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$tab.="<th colspan=3>".addZero($i,2)."</th>";
			}
		$tab.="</tr>";
		$tab.="<tr>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
                $tab.="<th>Premi</th>";
				$tab.="<th>Lembur</th>";
				$tab.="<th>PJD</th>";			}
		$tab.="</tr>";
		$tab.="</thead><tbody>";
		$no=0;$ttlabs=$ttpremi=[];
		foreach($arrkary as $val){
            $no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td align='center'>".$val['nik']."</td>
					<td style='text-transform: uppercase;'>".$val['namakaryawan']."</td>
					<td>".getNamaJabatan($val['kodejabatan'])."</td>";

					foreach($rangetgl as $tgl){			

                        $style = ''; // Initialize the style variable
                        if(!empty($arrpremi[$val['karyawanid']][$tgl]) && !empty($arrlembur[$val['karyawanid']][$tgl])) {
                            $style = ' style="color: red;"'; // Set the style to red if both are not empty
                        }

                        $tab .= "<td align='right'{$style}>".number_format($arrpremi[$val['karyawanid']][$tgl], 0)."</td>";
                        $tab .= "<td align='right'{$style}>".number_format($arrlembur[$val['karyawanid']][$tgl], 0)."</td>";

                        if($arrpjd[$val['karyawanid']][$tgl] == 1){
                            $tab .= "<td align='center' style='font-size: 15px;color: blue;'>&#10003;</td>";
                        }else{
                            $tab.="<td align='right'></td>";
                        }

                        $ttpremi[$val['karyawanid']]+=$arrpremi[$val['karyawanid']][$tgl];
                        $gtpremi[$tgl]+=$arrpremi[$val['karyawanid']][$tgl];
                        $ggtpremi+=$arrpremi[$val['karyawanid']][$tgl];

                        $ttlembur[$val['karyawanid']]+=$arrlembur[$val['karyawanid']][$tgl];
                        $gtlembur[$tgl]+=$arrlembur[$val['karyawanid']][$tgl];
                        $ggtlembur+=$arrlembur[$val['karyawanid']][$tgl];
                    }
                    
                    $tab.="<td align='right'>".number_format($ttpremi[$val['karyawanid']],0)."</td>";
                    $tab.="<td align='right'>".number_format($ttlembur[$val['karyawanid']],0)."</td>";
                    $tab.="<td align='right'>".number_format($ttpremi[$val['karyawanid']]+$ttlembur[$val['karyawanid']],0)."</td>";
                }
                $tab.="</tr>";

				$tab.="<tr class='rowcontent'>";
					$tab.="<td align='center' colspan=4><b>TOTAL</b></td>";
					foreach($rangetgl as $tgl){			
						$tab.="<td align='center'><b>".number_format($gtpremi[$tgl],0)."</b></td>";
						$tab.="<td align='center'><b>".number_format($gtlembur[$tgl],0)."</b></td>";
						$tab.="<td align='center'></td>";
					}
                    
                    $tab.="<td align='center'><b>".number_format($ggtpremi,0)."</b></td>";
                    $tab.="<td align='center'><b>".number_format($ggtlembur,0)."</b></td>";
                    $tab.="<td align='center'><b>".number_format($ggtlembur+$ggtpremi,0)."</b></td>";
                $tab.="</tr>";
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_DetailTraksi_".$unit."_".$periode;
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