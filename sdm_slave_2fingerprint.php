<?php

error_reporting(0);
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
$jam=checkPostGet('jam','');
$tipeLaporan=checkPostGet('tipeLaporan','');

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
			$wheres_fp.="";
		}else if($subunit==''){
			$where.=" and subbagian=''";
			$wheres_fp.=" and subbagian=''";
		}else{
			$where.=" and subbagian='".$subunit."'";
			$where_fp.=" and subbagian='".$subunit."'";
		}

		if($tipekaryawan=='all'){
			$where_tipekar= "";
		}else{
			$where_tipekar= "and tipekaryawan = '".$tipekaryawan."'";
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
            $str = "select karyawanid,nik,namakaryawan,subbagian,kodejabatan,tipekaryawan from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."' ".$where." ".$where_tipekar." order by namakaryawan asc ";
            $res = fetchdata($str);
		    $arrkary=$res;
        }else{
            $str="select karyawanid,nik,namakaryawan,subbagian,kodejabatan,tipekaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." ".$where_tipekar." order by namakaryawan asc";
            $res=fetchdata($str);
            $arrkary=$res;
        }


			// $arrkar=array();
			// $str="select sn,pin,karyawan from ".$dbname.".att_pegawai where karyawan !='0000000000' and karyawan in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where.") and sn in (select distinct sn from ".$dbname.".att_log where substr(scan_date,1,10) between '".$gettglawal."' and '".tglbesok($gettglakhir)."')";
			// $res=fetchdata($str);
			// foreach($res as $val){
			// 	$arrkar[$val['sn']][$val['karyawan']]=$val['karyawan'];
			// }

        	$kodeabsensi=array();
        	$str = "select a.tanggal,a.karyawanid,a.absensi, b.keterangan from ".$dbname.".sdm_absensidt a
        	left join ".$dbname.".sdm_5absensi b on a.absensi=b.kodeabsen
        	where kodeorg like '%".$unit."%' and absensi!='H' ";
			$res = fetchdata($str);
			foreach($res as $val){
				$kodeabsensi[$val['tanggal']][$val['karyawanid']] = $val['keterangan'];
			}

			$str = "select * from ".$dbname.".sdm_5shift where kodeorg = '".$unit."'";
			$res = fetchdata($str);
			if(count($res)==0){
				exit("errorcode : Master shift untuk kode organisasi ".$unit." belum ada.");
			}

			foreach($res as $val){
				$jamshiftmasuk[$val['id']]    = $val['masuk'];
				$jamshiftoutist[$val['id']]   = $val['keluar_ist'];
				$jamshiftinist[$val['id']]    = $val['masuk_ist'];
				$jamshiftpulang[$val['id']]   = $val['keluar'];
				$jamshifttoleransi[$val['id']]= $val['toleransi'];
				$jamshiftbatasawal[$val['id']]= $val['batas_awal'];
				$jamshifttipe_shift[$val['id']]= $val['tipe_shift'];
			}
		
			$jamshift = array();
			$str = "select * from ".$dbname.".sdm_5shiftanggota where kodeorg = '".$unit."' ".$where_fp." and tanggal between '".$gettglawal."' and '".tglbesok($gettglakhir)."' order by tanggal";
			$res = fetchdata($str);
			foreach($res as $val){
				$jamshift[$val['karyawanid']][$val['tanggal']]['namashift']= $val['namashift'];
				$jamshift[$val['karyawanid']][$val['tanggal']]['ke']       = $val['shift'];
				$jamshift[$val['karyawanid']][$val['tanggal']]['idshift']  = $val['idshift'];
				$jamshift[$val['karyawanid']][$val['tanggal']]['masuk']    = $jamshiftmasuk[$val['idshift']];
				$jamshift[$val['karyawanid']][$val['tanggal']]['outist']   = $jamshiftoutist[$val['idshift']];
				$jamshift[$val['karyawanid']][$val['tanggal']]['inist']    = $jamshiftinist[$val['idshift']];
				$jamshift[$val['karyawanid']][$val['tanggal']]['pulang']   = $jamshiftpulang[$val['idshift']];
				$jamshift[$val['karyawanid']][$val['tanggal']]['toleransi']= $jamshifttoleransi[$val['idshift']];
				$jamshift[$val['karyawanid']][$val['tanggal']]['batasawal']= $jamshiftbatasawal[$val['idshift']];
				$jamshift[$val['karyawanid']][$val['tanggal']]['tipe_shift']     = $jamshifttipe_shift[$val['idshift']];
			}

			$arrfp=array();
			$str = "select * from ".$dbname.".att_log where substr(scan_date,1,10) between '".$gettglawal."' and '".tglbesok($gettglakhir)."' order by scan_date asc";
			$res = fetchdata($str);
			foreach($res as $val){
				$arrfp[substr($val['scan_date'],0,10)][$val['pin']][$val['scan_date']] = $val['scan_date'];		
			}
			

			$jammasukfp = array();
			$jampulangfp = array();
			$validasimasuk = array();
			$validasikeluar = array();

			$jammasukfp_val = array();
			$jampulangfp_val = array();

			foreach ($arrfp as $tanggalfp => $ar1) {
				foreach ($ar1 as $karid => $ar2) {
					foreach ($ar2 as $jamfp => $val) {
						if(!isset($validasimasuk[$karid][$tanggalfp])){
							$validasimasuk[$karid][$tanggalfp]=0;
						}
						if(!isset($validasikeluar[$karid][$tanggalfp])){
							$validasikeluar[$karid][$tanggalfp]=0;
						}

						$shiftmasuk = date_create($tanggalfp.' '.$jamshift[$karid][$tanggalfp]['masuk']);
						date_add($shiftmasuk, date_interval_create_from_date_string("+".$jamshift[$karid][$tanggalfp]['toleransi']." minutes"));
						$shiftmasuk = date_format($shiftmasuk, 'Y-m-d H:i');

						$shiftpulang = date_create($tanggalfp.' '.$jamshift[$karid][$tanggalfp]['pulang']);
						$shiftpulang = date_format($shiftpulang, 'Y-m-d H:i');

						$shiftbatasawal = date_create($tanggalfp.' '.$jamshift[$karid][$tanggalfp]['batasawal']);
						$shiftbatasawal = date_format($shiftbatasawal, 'Y-m-d H:i');

						$shiftbatasawalesoknya = date_create(tglbesok($tanggalfp).' '.$jamshift[$karid][tglbesok($tanggalfp)]['batasawal']);
						$shiftbatasawalesoknya = date_format($shiftbatasawalesoknya, 'Y-m-d H:i');

						if($shiftmasuk > $shiftpulang){
							$shiftpulang = date_create(tglbesok($tanggalfp).' '.$jamshift[$karid][$tanggalfp]['pulang']);
							$shiftpulang = date_format($shiftpulang, 'Y-m-d H:i');
						}

						$jamfp_val = date_create($jamfp);
						$jamfp_val = date_format($jamfp_val, 'Y-m-d H:i');

						## Pengecekan sesuai shfit tidak
						## Jam Masuk
						if($jamfp_val >= $shiftbatasawal and $jamfp_val <= $shiftmasuk){
							$jammasukfp_val[$karid][$tanggalfp] = $jamfp_val;
							$jammasukfp[$karid][$tanggalfp]=$jamfp_val;
							$validasimasuk[$karid][$tanggalfp]=1;
						}

						## Jam Keluar
						if($jamfp_val >= $shiftpulang and $jamfp_val <= $shiftbatasawalesoknya){
							$jampulangfp_val[$karid][$tanggalfp] = $jamfp_val;
							$jampulangfp[$karid][$tanggalfp] = $jamfp_val;
							$validasikeluar[$karid][$tanggalfp]=1;
						}

						foreach ($arrfp[tglbesok($tanggalfp)][$karid] as $jamfpx2 => $valx2){
							$jamfpx2 = date_create($jamfpx2);
							$jamfpx2 = date_format($jamfpx2, 'Y-m-d H:i');
							## Jam Keluar
							if($jamfpx2 >= $shiftpulang and $jamfpx2 <= $shiftbatasawalesoknya){
								$jampulangfp_val[$karid][$tanggalfp] = $jamfpx2;
								$jampulangfp[$karid][$tanggalfp] = $jamfpx2;
								$validasikeluar[$karid][$tanggalfp]=1;
							}
						}
						## End Pengecekan

						// ## Batas Masuk
						// $original_time_pulang = $shiftpulang;
						// $batasMasuk = date('Y-m-d H:i', strtotime('-4 hours', strtotime($original_time_pulang)));


						// if($jammasukfp[$karid][$tanggalfp] <= $batasMasuk){
						// 	$jammasukfp[$karid][$tanggalfp] = $jammasukfp[$karid][$tanggalfp];
						// }else{
						// 	$jampulangfp[$karid][$tanggalfp] = $jammasukfp[$karid][$tanggalfp];
						// }


						// Initialize first and last times for each date and karid
						$firstTime = reset($ar2);
						$lastTime = end($ar2);

						// Format waktu
						$firstTimeFormatted = date_create($firstTime);
						$firstTimeFormatted = date_format($firstTimeFormatted, 'Y-m-d H:i');
						
						$lastTimeFormatted = date_create($lastTime);
						$lastTimeFormatted = date_format($lastTimeFormatted, 'Y-m-d H:i');
				
						// Menyimpan waktu pertama dan terakhir ke array yang sesuai
						if(!isset($jammasukfp[$karid][$tanggalfp])){
							$jammasukfp[$karid][$tanggalfp] = $firstTime;
							$jammasukfp[$karid][$tanggalfp] = $firstTimeFormatted;

						}

						if(!isset($jampulangfp[$karid][$tanggalfp])){
							$jampulangfp[$karid][$tanggalfp] = $lastTime;
							$jampulangfp[$karid][$tanggalfp] = $lastTimeFormatted;

						}
				
						// Assign the first and last times to the respective arrays
						if($jammasukfp[$karid][$tanggalfp] == $jampulangfp[$karid][$tanggalfp]  ){
							$jampulangfp[$karid][$tanggalfp] = '';
						}

					}
				}
			}

		if($tipeLaporan == 1){
			## Ambil dari ba-absensi
			$dtmanual=array();
			$flag=array();
			$str = "select * from ".$dbname.".sdm_ba_absensi where kodeorg='".$unit."' and tanggalabsen between '".$gettglawal."' and '".tglbesok($gettglakhir)."' and posting =1 and statuspersetujuan = 1 ".$where_fp."";
			$res = fetchdata($str);
			foreach($res as $val){
				if(!isset($validasimasuk[$karid][$tanggalfp])){
					$validasimasuk[$karid][$tanggalfp]=0;
				}
				if(!isset($validasikeluar[$karid][$tanggalfp])){
					$validasikeluar[$karid][$tanggalfp]=0;
				}

				$jamBaMasuk = date_create($val['jam']);
				$jamBaMasuk = date_format($jamBaMasuk, 'Y-m-d H:i');

				$jamBaPulang = date_create($val['jam4']);
				$jamBaPulang = date_format($jamBaPulang, 'Y-m-d H:i');

				if($jamBaMasuk != '' or  $jamBaPulang != ''){
					$flag[$val['karyawanid']][$val['tanggalabsen']] = 'BA';
				}

				## Jam Masuk dan Keluar
				if($val['tipeba'] == '1'){
					$jammasukfp[$val['karyawanid']][$val['tanggaljammasuk']] = $jamBaMasuk;
					$jampulangfp[$val['karyawanid']][$val['tanggaljamkeluar']] = $jamBaPulang;
					$validasimasuk[$val['karyawanid']][$val['tanggaljammasuk']]=1;
					$validasikeluar[$val['karyawanid']][$val['tanggaljamkeluar']]=1;
					$flag[$val['karyawanid']][$val['tanggalabsen']] = 'BA1';
				## Jam Masuk
				}elseif($val['tipeba'] == '2'){
					$validasimasuk[$val['karyawanid']][$val['tanggaljammasuk']]=1;
					$flag[$val['karyawanid']][$val['tanggalabsen']] = 'BA2';
					$jammasukfp[$val['karyawanid']][$val['tanggaljammasuk']] = $jamBaMasuk;
				## Jam Keluar
				}elseif($val['tipeba'] == '3'){
					$validasikeluar[$val['karyawanid']][$val['tanggaljamkeluar']]=1;
					$flag[$val['karyawanid']][$val['tanggalabsen']] = 'BA3';
					$jampulangfp[$val['karyawanid']][$val['tanggaljamkeluar']] = $jamBaPulang;
				}
			}
		}
		
		
		
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$colspn=$tglakhir*2;

		if($tipeprint!='html'){
			$tab="<fieldset style=float:left>
				<legend>Note</legend>
				<table  cellpadding=5 cellspacing=1 border=0 class=sortable>
					<tr>
						<td style='width:20px;background:blue'>&nbsp;</td>
						<td colspan=2>Jam Finger Valid</td>
					</tr>
					<tr>
						<td style='width:20px;background:red'>&nbsp;</td>
						<td colspan=2>Jam Finger Tidak Valid (Jam tidak sesuai dengan shift kerja)</td>
					</tr>
					<tr>
						<td style='width:20px;background:green'>&nbsp;</td>
						<td colspan=2>Jam Finger Valid (Jam finger dari BA-ABSENSI)</td>
					</tr>
					<tr>
					</tr>
				</table>
				</div>
			</fieldset>";
		}
		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['tipekaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['divisi']."</th>
				<th colspan='".$colspn."'>".$bulan."</th>
			</tr>";
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			for($i=$tglawal;$i<=$tglakhir;$i++){

				$dayOfWeek = date('w', strtotime($periode."-".$i));
				if ($dayOfWeek == 0) {
					$tab.="<th colspan=2 style='color:red'>".addZero($i,2)."</th>";
				}else{
					$tab.="<th colspan=2>".addZero($i,2)."</th>";
				}
			}
		$tab.="</tr>";
		$tab.="<tr>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$tab.="<th>Jam Awal</th>";
				$tab.="<th>Jam Akhir</th>";
			}
		$tab.="</tr>";
		$tab.="</thead><tbody>";
			
		$no=0;$ttlabs=[];
		foreach($arrkary as $val){

			if($val['subbagian'] == ''){
				$text = "UMUM";
			}else{
				$text = getNamaOrg($val['subbagian']);
			}

			$no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td align='center'>".$val['nik']."</td>
					<td style='text-transform: uppercase;'>".$val['namakaryawan']."</td>
					<td>".getNamaJabatan($val['kodejabatan'])."</td>
					<td align='center'>".getNamaTipeKary($val['tipekaryawan'])."</td>
					<td align='center'>".$text."</td>";

					foreach($rangetgl as $tgl){				
						if(isset($kodeabsensi[$tgl][$val['karyawanid']])){
							$style="style = color:blue;cursor:pointer";
							$tab.="<td align='center' ".$style." >".$kodeabsensi[$tgl][$val['karyawanid']]."</td>";
							$tab.="<td align='center' ".$style." >".$kodeabsensi[$tgl][$val['karyawanid']]."</td>";
						}else{
							if($jamshift[$val['karyawanid']][$tgl]['tipe_shift'] == '1'){
								if($validasimasuk[$val['karyawanid']][$tgl]=='1' and $validasikeluar[$val['karyawanid']][$tgl] =='1'){
									$style="style = color:blue;cursor:pointer";
									$style2="style = color:blue;cursor:pointer";
								}else{
									if($validasimasuk[$val['karyawanid']][$tgl]=='1'){
										$style="style = color:blue;cursor:pointer";
									}else{
										$style="style = color:red;cursor:pointer";

										if($jammasukfp[$val['karyawanid']][$tgl] == '' and $jampulangfp[$val['karyawanid']][$tgl] == '') {
											$style = "style='color:red;cursor:pointer;background-color:yellow;'"; // Tambahkan background color
										}
									}

									if($validasikeluar[$val['karyawanid']][$tgl]=='1'){
										$style2="style = color:blue;cursor:pointer";
									}else{
										$style2="style = color:red;cursor:pointer";

										if($jammasukfp[$val['karyawanid']][$tgl] == '' and $jampulangfp[$val['karyawanid']][$tgl] == '') {
											$style2 = "style='color:red;cursor:pointer;background-color:yellow;'"; // Tambahkan background color
										}
									}
								}
							}else{
								if($validasimasuk[$val['karyawanid']][$tgl]=='1' or $validasikeluar[$val['karyawanid']][$tgl]=='1'){
									$style="style = color:blue;cursor:pointer";
									$style2="style = color:blue;cursor:pointer";
								}else{
									$style="style = color:red;cursor:pointer";
									$style2="style = color:red;cursor:pointer";
								}
							}

							if($flag[$val['karyawanid']][$tgl] == 'BA1'){
								$style="style = color:green";
								$style2="style = color:green";
							}elseif($flag[$val['karyawanid']][$tgl] == 'BA2'){
								$style="style = color:green";
							}elseif($flag[$val['karyawanid']][$tgl] == 'BA3'){
								$style2="style = color:green";
							}

							if($jamshift[$val['karyawanid']][$tgl]['namashift'] == ''){
								$style2 = "style='background-color:cyan;'";
								$style = "style='background-color:cyan;'";
							}
							
							$tab.="<td align='center' ".$style." onclick=\"detail('".$val['karyawanid']."','".$jammasukfp[$val['karyawanid']][$tgl]."')\">".substr($jammasukfp[$val['karyawanid']][$tgl],11,5)."</td>";
							$tab.="<td align='center' ".$style2." onclick=\"detail('".$val['karyawanid']."','".$jampulangfp[$val['karyawanid']][$tgl]."')\">".substr($jampulangfp[$val['karyawanid']][$tgl],11,5)."</td>";

						}
					}
			$tab.="</tr>";

		}
		
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_FingerPrint_".$unit."_".$periode;
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

	case 'detail':

		$tab="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>SN MESIN</th>
				<th>SCAN DATE</th>
				<th>LATITUDE</th>
				<th>LONGITUDE</th>
				<th>LINK GOOGLE MAPS</th>
			</tr>";
		$tab.="</thead><tbody>";
		

		$no=0;
		$str = "select * from ".$dbname.".att_log where pin='".$karyawanid."' and scan_date like '%".$jam."%' order by scan_date asc";
		$res = fetchdata($str);
		if($jam == ''){
			$tab.="<tr class='rowcontent'>";
				$tab.="<td colspan=6 align='center'><b>DATA TIDAK ADA</b></td>";
			$tab.="</tr>";
		}else{
			foreach($res as $val){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td align='center'>".$no."</td>";
				$tab.="<td align='center'>".$val['sn']."</td>";
				$tab.="<td align='center'>".$val['scan_date']."</td>";
				$tab.="<td align='center'>".$val['latitude']."</td>";
				$tab.="<td align='center'>".$val['longitude']."</td>";
				$tab.="<td align='center'><a href=\"https://maps.google.com/?q=".$val['latitude'].",".$val['longitude']."\"  target=\"_blank\">".$val['latitude'].",".$val['longitude']."</a></td>";
			}
			$tab.="</tr>";
		}
		
		echo $tab;
	break;
}


?>