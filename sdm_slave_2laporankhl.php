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
            $str = "select karyawanid,nik,namakaryawan,kodejabatan from ".$dbname.".datakaryawan_hist where approval_status='8' and tipekaryawan = '4' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."' ".$where." order by namakaryawan asc ";
            $res = fetchdata($str);
		    $arrkary=$res;
        }else{
            $str="select karyawanid,nik,namakaryawan,kodejabatan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and tipekaryawan = '4' ".$where." order by namakaryawan asc";
            $res=fetchdata($str);
            $arrkary=$res;
        }

		$hasilUMR=array();

		## Ambil dari kebun_kehadiran_vw
		## Perawatan
        $str1 = "select * from ".$dbname.".kebun_kehadiran_vw where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res1 = fetchdata($str1);
        foreach($res1 as $val){
            $hasilUMR[$val['karyawanid']][$val['tanggal']] += $val['umr'];
            $hasilPREMI[$val['karyawanid']][$val['tanggal']] += $val['insentif'];
        }

		## Ambil dari kebun_prestasi_vs_hk
		## Panen
		$str2 = "select * from ".$dbname.".kebun_prestasi_vs_hk where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res2 = fetchdata($str2);
        foreach($res2 as $val){
            $hasilUMR[$val['karyawanid']][$val['tanggal']] += $val['upahkerja'];
            $hasilPREMI[$val['karyawanid']][$val['tanggal']] += $val['premi'];
        }

		## Ambil dari vhc_spl_kehadiran_vw 
		## Sipil
		$str3 = "select * from ".$dbname.".vhc_spl_kehadiran_vw where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res3 = fetchdata($str3);
        foreach($res3 as $val){
            $hasilUMR[$val['nik']][$val['tanggal']] += $val['umr'];
            $hasilPREMI[$val['nik']][$val['tanggal']] += $val['premi'];
        }

		## Ambil dari vhc_runhk_vw 
		## Traksi
		$str4 = "select * from ".$dbname.".vhc_runhk_vw where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res4 = fetchdata($str4);
        foreach($res4 as $val){
            $hasilUMR[$val['karyawanid']][$val['tanggal']] += $val['upah'];
            $hasilPREMI[$val['karyawanid']][$val['tanggal']] += $val['premi'];
        }

        ## Start Ambil pejabat kebun dan sipil 
		$str5 = "select * from ".$dbname.".kebun_aktifitas where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res5 = fetchdata($str5);
        foreach($res5 as $val){
            if($val['nikmandor'] != '' and getKary($val['nikmandor'],'tipekaryawan') == '4' and getKary($val['nikmandor'],'lokasitugas') == $unit){
                $hasilUMR[$val['nikmandor']][$val['tanggal']] = getUpahKary($periode,$val['nikmandor']);
            }   

            if($val['nikmandor1'] != '' and getKary($val['nikmandor1'],'tipekaryawan') == '4' and getKary($val['nikmandor1'],'lokasitugas') == $unit){
                $hasilUMR[$val['nikmandor1']][$val['tanggal']] = getUpahKary($periode,$val['nikmandor1']);
            } 

            if($val['nikasisten'] != '' and getKary($val['nikasisten'],'tipekaryawan') == '4' and getKary($val['nikasisten'],'lokasitugas') == $unit){
                $hasilUMR[$val['nikasisten']][$val['tanggal']] = getUpahKary($periode,$val['nikasisten']);
            } 

            if($val['keranimuat'] != '' and getKary($val['keranimuat'],'tipekaryawan') == '4' and getKary($val['keranimuat'],'lokasitugas') == $unit){
                $hasilUMR[$val['keranimuat']][$val['tanggal']] = getUpahKary($periode,$val['keranimuat']);
            } 
        }

		$str6 = "select * from ".$dbname.".vhc_spl_aktifitas where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res6 = fetchdata($str6);
        foreach($res6 as $val){
            if($val['nikmandor'] != '' and getKary($val['nikmandor'],'tipekaryawan') == '4' and getKary($val['nikmandor'],'lokasitugas') == $unit){
                $hasilUMR[$val['nikmandor']][$val['tanggal']] = getUpahKary($periode,$val['nikmandor']);
            }   

            if($val['nikmandor1'] != '' and getKary($val['nikmandor1'],'tipekaryawan') == '4' and getKary($val['nikmandor1'],'lokasitugas') == $unit){
                $hasilUMR[$val['nikmandor1']][$val['tanggal']] = getUpahKary($periode,$val['nikmandor1']);
            } 

            if($val['nikasisten'] != '' and getKary($val['nikasisten'],'tipekaryawan') == '4' and getKary($val['nikasisten'],'lokasitugas') == $unit){
                $hasilUMR[$val['nikasisten']][$val['tanggal']] = getUpahKary($periode,$val['nikasisten']);
            } 

            if($val['keranimuat'] != '' and getKary($val['keranimuat'],'tipekaryawan') == '4' and getKary($val['keranimuat'],'lokasitugas') == $unit){
                $hasilUMR[$val['keranimuat']][$val['tanggal']] = getUpahKary($periode,$val['keranimuat']);
            } 
        }

        ## Ambil dari absensidt
		## Absensidt
        $str7 = "select * from ".$dbname.".sdm_absensidt where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res7 = fetchdata($str7);
        foreach($res7 as $val){
            $hasilUMR[$val['karyawanid']][$val['tanggal']] += $val['umr'];
            $hasilPREMI[$val['karyawanid']][$val['tanggal']] += $val['premi'];
        }

		## BMTBS
        $str8 = "select * from ".$dbname.".kebun_3premibmtbs where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res8 = fetchdata($str8);
        foreach($res8 as $val){
            $hasilUMR[$val['karyawanid']][$val['tanggal']] += $val['rphk'];
            $hasilPREMI[$val['karyawanid']][$val['tanggal']] += $val['premi'];
        }

        ## End Pejabat BKM and Sipil

		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$colspn=$tglakhir*2;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th colspan='".$colspn."'>PERIODE ".$periode."</th>";

		$tab.=" <th rowspan='3'>TOTAL UPAH</th>";
		$tab.=" <th rowspan='3'>TOTAL PREMI</th>";
		$tab.=" <th rowspan='3'>GRAND TOTAL</th>";
		$tab.="</tr>";
            
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
                $dayOfWeek = date('w', strtotime($periode."-".$i));
				if ($dayOfWeek == 0) {
					$tab.="<th colspan=2 style='color:red'>".addZero($i,2)."</th>";
				}else{
					$tab.="<th colspan=2 >".addZero($i,2)."</th>";
				}
			}
		$tab.="</tr>";
		$tab.="<tr>";
			for($i=$tglawal;$i<=$tglakhir;$i++){

				$tab.="<th >UPAH</th>";
				$tab.="<th >PREMI</th>";
			}
		$tab.="</tr>";

		$tab.="</thead><tbody>";
		$no=0;
		foreach($arrkary as $val){
            $no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td>".$val['nik']."</td>
					<td style='text-transform: uppercase;'>".$val['namakaryawan']."</td>
					<td>".getNamaJabatan($val['kodejabatan'])."</td>";

					foreach($rangetgl as $tgl){		

						if($hasilUMR[$val['karyawanid']][$tgl] > getUpahKary($periode,$val['karyawanid'])){
							$Style = "style=color:red;cursor:pointer;min-width:50px !important";
						}else{
							$Style = "style=cursor:pointer;min-width:50px !important";
						}

						$tab.="<td ".$Style." align='center' onclick=\"detail('".$val['karyawanid']."','".$tgl."')\">".number_format($hasilUMR[$val['karyawanid']][$tgl],0)."</td>";
                        $ttl_umr_kar[$val['karyawanid']] += $hasilUMR[$val['karyawanid']][$tgl];
                        $ttl_umr_tgl[$tgl] += $hasilUMR[$val['karyawanid']][$tgl];
                        $gtl_umr_tgl += $hasilUMR[$val['karyawanid']][$tgl];

						$tab.="<td ".$Style." align='center' onclick=\"detail('".$val['karyawanid']."','".$tgl."')\">".number_format($hasilPREMI[$val['karyawanid']][$tgl],0)."</td>";
						$ttl_premi_kar[$val['karyawanid']] += $hasilPREMI[$val['karyawanid']][$tgl];
                        $ttl_premi_tgl[$tgl] += $hasilPREMI[$val['karyawanid']][$tgl];
                        $gtl_premi_tgl += $hasilPREMI[$val['karyawanid']][$tgl];
                    }
                    
                    $tab.="<td align='right' style=min-width:50px !important><b>".number_format($ttl_umr_kar[$val['karyawanid']],0)."</b></td>";
                    $tab.="<td align='right' style=min-width:50px !important><b>".number_format($ttl_premi_kar[$val['karyawanid']],0)."</b></td>";
                    $tab.="<td align='right' style=min-width:50px !important><b>".number_format($ttl_umr_kar[$val['karyawanid']]+$ttl_premi_kar[$val['karyawanid']],0)."</b></td>";
                }
                $tab.="</tr>";

				$tab.="<tr class='rowcontent'>";

					$tab.="<td align='center' colspan=4><b>TOTAL</b></td>";
					foreach($rangetgl as $tgl){			
						$tab.="<td align='center'><b>".number_format($ttl_umr_tgl[$tgl],0)."</b></td>";
						$tab.="<td align='center'><b>".number_format($ttl_premi_tgl[$tgl],0)."</b></td>";
					}

                    $tab.="<td align='center'><b>".number_format($gtl_umr_tgl,0)."</b></td>";
                    $tab.="<td align='center'><b>".number_format($gtl_premi_tgl,0)."</b></td>";
                    $tab.="<td align='center'><b>".number_format($gtl_umr_tgl+$gtl_premi_tgl,0)."</b></td>";

                $tab.="</tr>";
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_Kehadiran_".$unit."_".$periode;
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

	case'detail':

		$hasildetail=array();

		## Ambil dari kebun_kehadiran_vw
		## Perawatan
        $str1 = "select * from ".$dbname.".kebun_kehadiran_vw where tanggal = '".$tanggal."' and karyawanid = '".$karyawanid."' order by tanggal asc";
		$res1 = fetchdata($str1);
        foreach($res1 as $val){
            $hasildetail['BKM RAWAT'][$val['karyawanid']][$val['tanggal']][$val['notransaksi']]= $val['jhk'];
        }
		
		## Ambil dari kebun_prestasi_vs_hk
		## Panen
		$str2 = "select * from ".$dbname.".kebun_prestasi_vs_hk where tanggal = '".$tanggal."' and karyawanid = '".$karyawanid."' order by tanggal asc";
		$res2 = fetchdata($str2);
        foreach($res2 as $val){
            $hasildetail['PANEN'][$val['karyawanid']][$val['tanggal']][$val['notransaksi']]= $val['notransaksi'];
        }

		## Ambil dari vhc_spl_kehadiran_vw 
		## Sipil
		$str3 = "select * from ".$dbname.".vhc_spl_kehadiran_vw where tanggal = '".$tanggal."' and nik = '".$karyawanid."' order by tanggal asc";
		$res3 = fetchdata($str3);
        foreach($res3 as $val){
            $hasildetail['BKM SIPIL'][$val['nik']][$val['tanggal']][$val['notransaksi']]= $val['notransaksi'];
        }

		## Ambil dari vhc_runhk_vw 
		## Traksi
		$str4 = "select * from ".$dbname.".vhc_runhk_vw where tanggal = '".$tanggal."' and idkaryawan = '".$karyawanid."' order by tanggal asc";
		$res4 = fetchdata($str4);
        foreach($res4 as $val){
            $hasildetail['TRAKSI'][$val['idkaryawan']][$val['tanggal']][$val['notransaksi']]= $val['notransaksi'];
        }

		## Start Ambil pejabat kebun dan sipil 
		$str5 = "select * from ".$dbname.".kebun_aktifitas where tanggal = '".$tanggal."' order by tanggal asc";
		$res5 = fetchdata($str5);
        foreach($res5 as $val){
			if($val['nikmandor'] == $karyawanid || $val['nikmandor1'] == $karyawanid || $val['nikasisten'] == $karyawanid || $val['keranimuat'] == $karyawanid ){
				$hasildetail['PEJABAT BKM'][$karyawanid][$val['tanggal']][$val['notransaksi']]= $val['notransaksi'];
			}
        }

		$str6 = "select * from ".$dbname.".vhc_spl_aktifitas where tanggal = '".$tanggal."' order by tanggal asc";
		$res6 = fetchdata($str6);
        foreach($res6 as $val){
            if($val['nikmandor'] == $karyawanid || $val['nikmandor1'] == $karyawanid || $val['nikasisten'] == $karyawanid || $val['keranimuat'] == $karyawanid ){
				$hasildetail['PEJABAT BKM SIPIL'][$karyawanid][$val['tanggal']][$val['notransaksi']]= $val['notransaksi'];
			}
        }
		
		## Ambil dari absensidt
		## Absensidt
        $str7 = "select * from ".$dbname.".sdm_absensidt where tanggal = '".$tanggal."' and karyawanid = '".$karyawanid."'  order by tanggal asc";
        $res7 = fetchdata($str7);
        foreach($res7 as $val){
			$hasildetail['SDM ABSENSI'][$val['karyawanid']][$val['tanggal']][$val['norefrensi']]= $val['norefrensi'];
        }

		## BM TBS
        $str8 = "select * from ".$dbname.".kebun_3premibmtbs where tanggal = '".$tanggal."' and karyawanid = '".$karyawanid."'  order by tanggal asc";
        $res8 = fetchdata($str8);
        foreach($res8 as $val){
			$hasildetail['BM TBS'][$val['karyawanid']][$val['tanggal']][$val['notransaksi']]= $val['norefrensi'];
        }
		

		$tab="";
        $tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader style='text-align:center;font-weight:bold;'>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['nourut']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['sumber']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['namakaryawan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['notransaksi']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['tanggal']."</th>";
		$tab.="</tr>";
		$tab.="</thead><tbody>";

		$no=0;
		foreach($hasildetail as $tipe => $arr1){
			foreach($arr1 as $karid => $arr2){
				foreach($arr2 as $tanggal => $arr3){
					foreach($arr3 as $notrans => $value){
						$no++;
						$tab.="<tr class='rowcontent'>";
							$tab.="<td align=center>".$no."</td>";
							$tab.="<td align=center>".$tipe."</td>";
							$tab.="<td align=center>".getNamaKaryawan($karid)."</td>";
							$tab.="<td align=center>".$notrans."</td>";
							$tab.="<td align=center>".$tanggal."</td>";
					}
				}
			}
		}
		$tab.="</tr>";

		echo $tab;
	break;
}


?>