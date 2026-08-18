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
		$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
		$tab="";

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

		$where.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar = '')";
        
        $str="select karyawanid,nik,namakaryawan,kodejabatan,tipekaryawan,subbagian,bpjs,jms,pensiun from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." order by namakaryawan asc";
        $res=fetchdata($str);
        $arrkary=$res;
		
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['nik']."</th>
				<th>".$_SESSION['lang']['namakaryawan']."</th>
				<th>".$_SESSION['lang']['subbagian']."</th>
				<th>".$_SESSION['lang']['tipekaryawan']."</th>
				<th>".$_SESSION['lang']['jabatan']."</th>
				<th>No. BPJS Kesehatan</th>
				<th>No. BPJS Ketenagakerjaan</th>
				<th>No. BPJS Pensiun</th>";

	
	
		$tab.="</thead><tbody>";
		$no=0;
		foreach($arrkary as $val){

            if($val['subbagian'] == ''){
                $div = "KANTOR";
            }else{
                $div= getNamaOrg($val['subbagian']);
            }
            $no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td>".$val['nik']."</td>
					<td style='text-transform: uppercase;'>".$val['namakaryawan']."</td>
					<td>".$div."</td>
					<td>".$opttipe[$val['tipekaryawan']]."</td>
					<td>".getNamaJabatan($val['kodejabatan'])."</td>
					<td align='right'>".$val['bpjs']."</td>
					<td align='right'>".$val['jms']."</td>
					<td align='right'>".$val['pensiun']."</td>";
        }
	
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_DaftarBPJS_".$unit;
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
            $hasildetail['BKM RAWAT'][$val['karyawanid']][$val['tanggal']][$val['notransaksi']]= $val['notransaksi'];
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

        ## Bongkar Muat
		//$str4 = "select * from ".$dbname.".vhc_runhk_vw where tanggal = '".$tanggal."' and idkaryawan = '".$karyawanid."' order by tanggal asc";
		$str8 = "select a.*,b.tanggal from ".$dbname.".kebun_spbbm a
		left join ".$dbname.".kebun_spbht b on a.nospb=b.nospb
		where b.tanggal  = '".$tanggal."' and a.karyawanid = '".$karyawanid."' order by b.tanggal asc";
		$res8 = fetchdata($str8);
        foreach($res8 as $val){
            $hasildetail['BMTBS'][$val['karyawanid']][$val['tanggal']][$val['nospb']]= $val['nospb'];
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