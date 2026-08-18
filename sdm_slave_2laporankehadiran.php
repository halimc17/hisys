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
            $str = "select karyawanid,nik,namakaryawan,kodejabatan,tipekaryawan,subbagian from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."' ".$where." order by namakaryawan asc ";
            $res = fetchdata($str);
		    $arrkary=$res;
        }else{
            $str="select karyawanid,nik,namakaryawan,kodejabatan,tipekaryawan,subbagian from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." order by namakaryawan asc";
            $res=fetchdata($str);
            $arrkary=$res;
        }

		$hasilAbsn=array();

		## Ambil dari kebun_kehadiran_vw
		## Perawatan
        $str1 = "select * from ".$dbname.".kebun_kehadiran_vw where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res1 = fetchdata($str1);
        foreach($res1 as $val){
            $hasilAbsn[$val['karyawanid']][$val['tanggal']] = $val['absensi'];
        }

		## Ambil dari kebun_prestasi_vs_hk
		## Panen
		$str2 = "select * from ".$dbname.".kebun_prestasi_vs_hk where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res2 = fetchdata($str2);
        foreach($res2 as $val){
            $hasilAbsn[$val['karyawanid']][$val['tanggal']] = 'H';
        }

		## Ambil dari vhc_spl_kehadiran_vw 
		## Sipil
		$str3 = "select * from ".$dbname.".vhc_spl_kehadiran_vw where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res3 = fetchdata($str3);
        foreach($res3 as $val){
            $hasilAbsn[$val['nik']][$val['tanggal']] = 'H';
        }

		## Ambil dari vhc_runhk_vw 
		## Traksi
		$str4 = "select * from ".$dbname.".vhc_runhk_vw where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res4 = fetchdata($str4);
        foreach($res4 as $val){
            $hasilAbsn[$val['idkaryawan']][$val['tanggal']] = 'H';
        }

        ## BONGKAR MUAT
		$str8 = "select * from ".$dbname.".kebun_spbbm 
		where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res8 = fetchdata($str8);
        foreach($res8 as $val){
            $hasilAbsn[$val['karyawanid']][$val['tanggal']] = 'H';
        }

		## Start Ambil pejabat kebun dan sipil 
		$str5 = "select * from ".$dbname.".kebun_aktifitas where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res5 = fetchdata($str5);
        foreach($res5 as $val){
            $hasilAbsn[$val['nikmandor']][$val['tanggal']]  = 'H';
            $hasilAbsn[$val['nikmandor1']][$val['tanggal']] = 'H';
            $hasilAbsn[$val['nikasisten']][$val['tanggal']] = 'H';
            $hasilAbsn[$val['keranimuat']][$val['tanggal']] = 'H';
        }

		$str6 = "select * from ".$dbname.".vhc_spl_aktifitas where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
		$res6 = fetchdata($str6);
        foreach($res6 as $val){
            $hasilAbsn[$val['nikmandor']][$val['tanggal']]  = 'H';
            $hasilAbsn[$val['nikmandor1']][$val['tanggal']] = 'H';
            $hasilAbsn[$val['nikasisten']][$val['tanggal']] = 'H';
            $hasilAbsn[$val['keranimuat']][$val['tanggal']] = 'H';
        }
		## End Ambil pejabat kebun dan sipil

		## Ambil dari absensidt
		## Absensidt
        $str7 = "select * from ".$dbname.".sdm_absensidt where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res7 = fetchdata($str7);
        foreach($res7 as $val){
            $hasilAbsn[$val['karyawanid']][$val['tanggal']] = $val['absensi'];
            $kodeAbsen[$val['absensi']] = $val['absensi'];
        }

		## Cek Finger
		$str8 = "select * from ".$dbname.".upload_absensi where tanggalabsen between '".$gettglawal."' and '".$gettglakhir."' order by tanggalabsen asc";
		$res8 = fetchdata($str8);
        foreach($res8 as $val){
            $hasilFinger[$val['karyawanid']][$val['tanggalabsen']] = $val['absensi'];
        }

		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}



		$colspn=$tglakhir;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='2'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='2'>".$_SESSION['lang']['subbagian']."</th>
				<th rowspan='2'>".$_SESSION['lang']['tipekaryawan']."</th>
				<th rowspan='2'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='2'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='2'>".$_SESSION['lang']['jabatan']."</th>
				<th colspan='".$colspn."'>PERIODE ".$periode."</th>";

		foreach($kodeAbsen as $absen){	
			$tab.=" <th rowspan='2'>".$absen."</th>";
		}

		$tab.=" <th rowspan='2'>TOTAL</th>";
		$tab.="</tr>";
            
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
                $dayOfWeek = date('w', strtotime($periode."-".$i));
				if ($dayOfWeek == 0) {
					$tab.="<th style='color:red'>".addZero($i,2)."</th>";
				}else{
					$tab.="<th>".addZero($i,2)."</th>";
				}
			}
		$tab.="</tr>";
		$tab.="</thead><tbody>";
		$no=0;
		foreach($arrkary as $val){
            $no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td>".$val['subbagian']."</td>
					<td>".$opttipe[$val['tipekaryawan']]."</td>
					<td>".$val['nik']."</td>
					<td style='text-transform: uppercase;'>".$val['namakaryawan']."</td>
					<td>".getNamaJabatan($val['kodejabatan'])."</td>";

					foreach($rangetgl as $tgl){				
						if($hasilAbsn[$val['karyawanid']][$tgl] != 'H'){
							$Style = "style='color:red;cursor:pointer;'";
						}else{
								$Style = "style='cursor:pointer;'";
						}

						if($hasilAbsn[$val['karyawanid']][$tgl] == '' and $hasilFinger[$val['karyawanid']][$tgl]  == '' ){
							$Style = "style='cursor:pointer; background-color:yellow;'";
						}

						if($hasilAbsn[$val['karyawanid']][$tgl] == '' and $hasilFinger[$val['karyawanid']][$tgl]  != '' ){
							$Style = "style='cursor:pointer; background-color:red;'";
						}
						
						$tab .= "<td ".$Style." align='center' onclick=\"detail('".$val['karyawanid']."','".$tgl."')\">".$hasilAbsn[$val['karyawanid']][$tgl]."</td>";

						if($hasilAbsn[$val['karyawanid']][$tgl] != ''){
							$ttl_abs[$val['karyawanid']] += 1;
							$gtl_abs[$tgl]+= 1;
						}
                    }
					
					$ttkodeAbsen = [];

					// Inisialisasi array ttkodeAbsen untuk setiap karyawan dan kode absen
					foreach ($hasilAbsn as $karid => $arr1) {
						foreach ($kodeAbsen as $absen) {
							$ttkodeAbsen[$karid][$absen] = 0;
						}
					}

					// Hitung jumlah kode absen
					foreach ($kodeAbsen as $absen) {
						foreach ($hasilAbsn as $karid => $arr1) {    
							foreach ($arr1 as $tgl => $kodeabsen) {    
								if ($absen == $kodeabsen) {
									$ttkodeAbsen[$karid][$absen] += 1;
								}
							}
						}
					}
					
					foreach ($kodeAbsen as $absen) {
						$tab .= "<td style='text-transform: uppercase;' align='center'>" . $ttkodeAbsen[$val['karyawanid']][$absen] . "</td>";

						if($ttkodeAbsen[$val['karyawanid']][$absen] != ''){
							$ttl_kode[$absen]+= $ttkodeAbsen[$val['karyawanid']][$absen];
							$gtl_kode+= $ttkodeAbsen[$val['karyawanid']][$absen];
						}
					}
                    
                    $tab.="<td align='center'><b>".number_format($ttl_abs[$val['karyawanid']],0)."</b></td>";
                }
                $tab.="</tr>";

				$tab.="<tr class='rowcontent'>";

					$tab.="<td align='center' colspan=6><b>TOTAL</b></td>";
					foreach($rangetgl as $tgl){			
						$tab.="<td align='center'><b>".number_format($gtl_abs[$tgl],0)."</b></td>";
					}
					
					foreach ($kodeAbsen as $absen) {
						$tab.="<td align='center'><b>".number_format($ttl_kode[$absen],0)."</b></td>";
					}

                    $tab.="<td align='center'><b>".number_format($gtl_kode,0)."</b></td>";

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
		$str8 = "select * from ".$dbname.".kebun_spbbm 
		where tanggal  = '".$tanggal."' and karyawanid = '".$karyawanid."' order by tanggal asc";
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