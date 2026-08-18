<?php
ini_set('display_errors',0);
error_reporting(0);

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
$method=checkPostGet('method','');
$perusahaan=checkPostGet('perusahaan','');
$periode=checkPostGet('periode','');
$tipe=checkPostGet('tipe','');
switch ($method) {
case 'preview1':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan
	$where = '';
	
	
	
	/*
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];
	if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
		$where=" and (kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' or tipe='PT')";
		$barx=$perusahaan;
	}

	

	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	// exit("error :".$perusahaan);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT') {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 4 and $_SESSION['empl']['tipelokasitugas']=='HOLDING') {
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
		if(strlen($bar['kodeorganisasi']) == 4 and $_SESSION['empl']['tipelokasitugas']!='HOLDING' and $bar['induk']==$perusahaan)
		{
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			
		}
	}
	*/
	
	
	if($perusahaan!=''){
		$whereunit=" and induk='".$perusahaan."'";
	}else{
		$whereunit=" and induk in ('" . implode("','", array_keys(getOrgDetail(3))) . "')";
	}
	
	#= nama organisasi	
	$str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
		where 1=1 ".$whereunit." and (length(kodeorganisasi)='4' or length(kodeorganisasi)='3') order by kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);	
	while ($bar = $res->fetch()) {
		$nmorganisasi[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
	}
	
	
	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi 
		where 1=1 ".$whereunit." and length(kodeorganisasi)='4' order by kodeorganisasi asc";
		 //echo $str;exit();
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	// exit("error :".$perusahaan);
	while ($bar = $res->fetch()) {
		$arrpt[$bar['induk']]['kode'] = $bar['induk'];
		$arrpt[$bar['induk']]['nama'] = $nmorganisasi[$bar['induk']];
		$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
		$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $nmorganisasi[$bar['kodeorganisasi']];
		
	}
	
	
	// echo "<pre>";
	// print_r($arrpt);
	// echo "</pre>";
	// exit();
	
	
	//Get Kas Bank
	$arrItem = array();
	
	$str = "select * from ".$dbname.".keu_kasbankht where tanggalinput like '".$periode."%' and pembayaran=0";
	//exit("error :".$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		
		 @$arrItem[$bar['kodeorg']][substr($bar['tanggalinput'], 8, 2)][$bar['posting']] += 1;
	}
	$tab = "<table class=sortable cellpadding=5 cellspacing=1 border=0>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 3)."'>MONITORING KAS BANK</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	$val=array();	
	
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
		
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 3)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";		
		//exit("error :".$key);	
		if (isset($arrunit[$key]))
			foreach($arrunit[$key]as $key2 => $val2) {
				$tab.="<tr class=rowcontent>
					<td rowspan=2>".$val2['nama']." (".$key2.")</td>
					<td style='text-align:center'>posted</td>";					
				$totnilaiposted = 0;
				foreach($arrhari as $val) {
					 @$nilaiposted = $arrItem[$key2][substr($val, 8, 2)]['1']+$arrItem[$key2][substr($val, 8, 2)]['3'];
					$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key2."','".$val."','keu','1')>".hidezerodecimal($nilaiposted)."</td>";
					$totnilaiposted += $nilaiposted;
					
				}
				$tab.="<td style='text-align:center;'>".hidezerodecimal($totnilaiposted)."</td>
					</tr>
					<tr class=rowcontent>
					<td style='text-align:center;color:red'>not posted</td>";
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					@$nilaiunposted = $arrItem[$key2][substr($val, 8, 2)]['0']+$arrItem[$key2][substr($val, 8, 2)]['9'];
					$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=popup('".$key2."','".$val."','keu','0')>".hidezerodecimal($nilaiunposted)."</td>";
					$totnilaiunposted += $nilaiunposted;
				}
				$tab.="<td style='text-align:center;color:red'>".hidezerodecimal($totnilaiunposted)."</td>
					</tr>";
			}
	}
	$tab.="</tbody>
		</table>";
	echo $tab;
	break;
case 'preview2':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan
	$where = '';
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];
	if(($_SESSION['empl']['tipelokasitugas']!='HOLDING')and($_SESSION['empl']['tipelokasitugas']!='KANWIL')){
		$where=" and (kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' or tipe='PT')";
		$barx=$perusahaan;
	}else{
		$where=" and (kodeorganisasi like '%' or tipe='PT')";
		$barx=$perusahaan;
	}

	
	$arrunit=array();
	$arrpt=array();
	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc"; // echo $str;
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT' and in_array($bar['kodeorganisasi'], array_keys(getOrgDetail(3)))){
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 4) {
			@$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			@$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
		if (strlen($bar['kodeorganisasi']) == 6 and substr($bar['tipe'], 0, 6) == 'GUDANG') {
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
	}
	//Get Transaksi Gudang
	$str = "select * from ".$dbname.".log_transaksi_vw where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem[$bar['kodegudang']][substr($bar['tanggal'], 8, 2)][$bar['post']] += 1;
	}
	$tab = "<table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 4)."'>MONITORING GUDANG</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['gudang']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 4)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		foreach($arrunit[$key]as $key2 => $val2) {
			if (isset($arrgdg[$key2])) {
				$tab.="<tr class=rowcontent>
					<td rowspan='".(count($arrgdg[$key2]) * 2)."' >".$val2['nama']." (".$key2.")</td>";
				$count = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					$count++;
					if ($count == 1) {
						$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
						$tab.="<td style='text-align:center'>posted</td>";
					}
				}
				$totnilaiposted = 0;
				$count = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					$count++;
					if ($count == 1) {
						foreach($arrhari as $val) {
							 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
							$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','log','1')>".$nilaiposted."</td>";
							$totnilaiposted += $nilaiposted;
						}
						$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
						$tab.="</tr>";
						$tab.="<tr class=rowcontent>";
						$tab.="<td style='text-align:center;color:red'>not posted</td>";
						$totnilaiunposted = 0;
						foreach($arrhari as $val) {
							@$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
							$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=popup('".$key3."','".$val."','log','0')>".$nilaiunposted."</td>";
							$totnilaiunposted += $nilaiunposted;
						}
						$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>";
						$tab.="</tr>";
					}
				}
				$count = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					$count++;
					if ($count != 1) {
						$tab.="<tr class=rowcontent>";
						$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
						$tab.="<td style='text-align:center'>posted</td>";
						$totnilaiposted = 0;
						foreach($arrhari as $val) {
							 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
							$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','log','1')>".$nilaiposted."</td>";
							$totnilaiposted += $nilaiposted;
						}
						$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
						$tab.="</tr>";
						$tab.="<tr class=rowcontent>";
						$tab.="<td style='text-align:center;color:red'>not posted</td>";
						$totnilaiunposted = 0;
						foreach($arrhari as $val) {
							 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
							$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=popup('".$key3."','".$val."','log','0')>".$nilaiunposted."</td>";
							$totnilaiunposted += $nilaiunposted;
						}
						$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>";
						$tab.="</tr>";
					}
				}
			}
		}
	}
	$tab.="</tbody>
		</table>";
	echo $tab;
	break;
case 'preview3':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan
	$where = '';
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];
	if(($_SESSION['empl']['tipelokasitugas']!='HOLDING')and($_SESSION['empl']['tipelokasitugas']!='KANWIL')){
		// $where=" and (kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' or tipe='PT')";
		$where=" and (kodeorganisasi like '%' or tipe='PT')";
		$barx=$perusahaan;
	}else{
		$where=" and (kodeorganisasi like '%' or tipe='PT')";
		$barx=$perusahaan;
	}

	
	// $where.=" and inti='1'";

	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT' and in_array($bar['kodeorganisasi'], array_keys(getOrgDetail(3)))) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 4 and $bar['tipe'] == 'KEBUN') {
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			$arrgdg[$bar['kodeorganisasi']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrgdg[$bar['kodeorganisasi']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
		if (strlen($bar['kodeorganisasi']) == 6 and($bar['tipe'] == 'AFDELING' or $bar['tipe'] == 'BIBITAN')) {
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
	}
	//Get Transaksi Kebun
	if ($tipe == '') {
		$cltipe = "1=1";
	} else {
		$cltipe = "a.tipetransaksi='".$tipe."'";
	}
	
	if ($tipe != '') {
		$whtp="";
		if($tipe!='PNN'){
			$whtp="and c.karyawanid=b.nikpemel";
		}
		
		// $str = "select a.*, b.kodeorg as kddiv, sum(c.jhk) as jhk from ".$dbname.".kebun_aktifitas a
		// 	left join ".$dbname.".kebun_kehadiran_vw c on a.notransaksi=c.notransaksi and a.tipetransaksi!='PNN'
		// 	left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi ".$whtp."
		// 	where a.tanggal like '".$periode."%' and ".$cltipe." group by a.notransaksi, b.nik";
		$str = "select a.notransaksi, IF(a.kdblok = '',NULL,a.kdblok) as kddiv, a.kodeorg, a.tanggal,a.tipetransaksi, a.jurnal
			 from ".$dbname.".kebun_prestasi_alltipe_vw a
			where a.tanggal like '".$periode."%' and ".$cltipe." group by a.notransaksi,a.tanggal";
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if (is_null($bar['kddiv'])) {
				 #  @$arrItem[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
				if ($bar['tipetransaksi'] != 'PNN') {
					 @$arrItem[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += count($bar['notransaksi']);
				} else {
					 @$arrItem[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
				}
			} else {
				 #  @$arrItem[substr($bar['kddiv'], 0, 6)][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
				if ($bar['tipetransaksi'] != 'PNN') {
					 @$arrItem[substr($bar['kddiv'], 0, 6)][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += count($bar['notransaksi']);
				} else {
					 @$arrItem[substr($bar['kddiv'], 0, 6)][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
				}
			}
		}
	} else {
		// $str = "select a.*,b.kodeorg as kddiv, sum(c.jhk) as jhk from ".$dbname.".kebun_aktifitas a
		// 	left join ".$dbname.".kebun_kehadiran_vw c on a.notransaksi=c.notransaksi and a.tipetransaksi!='PNN'
		// 	left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi and c.karyawanid=b.nikpemel
		// 	where a.tanggal like '".$periode."%' group by a.notransaksi, b.nik";
		$str = "select a.notransaksi, IF(a.kdblok = '',NULL,a.kdblok) as kddiv, a.kodeorg, a.tanggal,a.tipetransaksi, a.jurnal
			 from ".$dbname.".kebun_prestasi_alltipe_vw a
			where a.tanggal like '".$periode."%' group by a.notransaksi,a.tanggal";
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if (is_null($bar['kddiv'])) {
				 #  @$arrItem[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
				if ($bar['tipetransaksi'] != 'PNN') {
					 @$arrItem[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += count($bar['notransaksi']);
				} else {
					 @$arrItem[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
				}
			} else {
				 #  @$arrItem[substr($bar['kddiv'], 0, 6)][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
				if ($bar['tipetransaksi'] != 'PNN') {
					 @$arrItem[substr($bar['kddiv'], 0, 6)][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += count($bar['notransaksi']);
				} else {
					 @$arrItem[substr($bar['kddiv'], 0, 6)][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
				}
			}
		}
	}
	
	// echo"<pre>";
	// print_r($arrItem);
	// echo"</pre>";
	$tab = "<table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 4)."'>MONITORING ".($tipe == '' ? 'PANEN, TM, TBM' : ($tipe == 'PNN' ? 'PANEN' : strtoupper($tipe)))."</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['divisi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 4)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		if (isset($arrunit[$key]))
			foreach($arrunit[$key]as $key2 => $val2) {
				if (isset($arrgdg[$key2])) {
					$tab.="<tr class=rowcontent>
						<td rowspan='".(count($arrgdg[$key2]) * 2)."'>".$val2['nama']." (".$key2.")</td>";
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count == 1) {
							if (strlen($key3) == 4) {
								$tab.="<td rowspan=2>".$key2." - <font color=red>Transaksi Abnormal</font></td>";
							} else {
								$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
							}
							$tab.="<td style='text-align:center'>posted</td>";
						}
					}
					$totnilaiposted = 0;
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count == 1) {
							foreach($arrhari as $val) {
								 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
								$tab.="<td style='text-align:center;cursor:pointer' onclick=\"detail3('".$key3."','".$val."','".$tipe."','1','detail3','event');\">".$nilaiposted."</td>"; //isi baris pertama posting
								$totnilaiposted += $nilaiposted;
							}
							$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
							$tab.="<td style='text-align:center;color:red'>not posted</td>";
							$totnilaiunposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
								$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=\"detail3('".$key3."','".$val."','".$tipe."','0','detail3','event');\">".$nilaiunposted."</td>"; //isi baris 1 unpost
								$totnilaiunposted += $nilaiunposted;
							}
							$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>";
							$tab.="</tr>";
						}
					}
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count != 1) {
							$tab.="<tr class=rowcontent>";
							if (strlen($key3) == 4) {
								$tab.="<td rowspan=2>".$key2." - <font color=red>Transaksi Abnormal</font></td>";
							} else {
								$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
							}
							$tab.="<td style='text-align:center'>posted</td>";
							$totnilaiposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
								$tab.="<td style='text-align:center;cursor:pointer' onclick=\"detail3('".$key3."','".$val."','".$tipe."','1','detail3','event');\">".$nilaiposted."</td>";
								$totnilaiposted += $nilaiposted;
							}
							$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
							$tab.="<td style='text-align:center;color:red'>not posted</td>";
							$totnilaiunposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
								$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=\"detail3('".$key3."','".$val."','".$tipe."','0','detail3','event');\">".$nilaiunposted."</td>";
								$totnilaiunposted += $nilaiunposted;
							}
							$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>";
							$tab.="</tr>";
						}
					}
				}
			}
	}
	$tab.="</tbody>
		</table>";
	echo $tab;
	break;
case 'preview4':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan
	$where = '';
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];
	if(($_SESSION['empl']['tipelokasitugas']!='HOLDING')and($_SESSION['empl']['tipelokasitugas']!='KANWIL')){
		$where=" and (kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' or tipe='PT')";
		$barx=$perusahaan;
	}else{
		$where=" and (kodeorganisasi like '%' or tipe='PT')";
		$barx=$perusahaan;
	}

	

	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT'and in_array($bar['kodeorganisasi'], array_keys(getOrgDetail(3)))) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 4 && $bar['tipe'] == 'PABRIK') {
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
	}
	//Get Perawatan Mesin
	$str = "select * from ".$dbname.".pabrik_rawatmesinht where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem[$bar['pabrik']][substr($bar['tanggal'], 8, 2)][$bar['statPost']] += 1;
	}
	$tab = "<table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 3)."'>MONITORING PEMELIHARAAN MESIN</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 3)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		if (isset($arrunit[$key])) {
			foreach($arrunit[$key]as $key2 => $val2) {
				$tab.="<tr class=rowcontent>
					<td rowspan=2>".$val2['nama']." (".$key2.")</td>
					<td style='text-align:center'>posted</td>";
				$totnilaiposted = 0;
				foreach($arrhari as $val) {
					//  @$nilaiposted = $arrItem[$key2][substr($val, 8, 2)]['1'];
					// $tab.="<td style='text-align:center'>".$nilaiposted."</td>";
					// $totnilaiposted += $nilaiposted;
					 @$nilaiposted = $arrItem[$key2][substr($val, 8, 2)]['1'];
					$tab.="<td style='text-align:center;cursor:pointer' onclick=\"detail4('".$key2."','".$val."','rwtmsn','1','detail4','event');\">".$nilaiposted."</td>";
					$totnilaiposted += $nilaiposted;					
				}
				$tab.="<td style='text-align:center;'>".$totnilaiposted."</td>
					</tr>
					<tr class=rowcontent>
					<td style='text-align:center;color:red'>not posted</td>";
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					//  @$nilaiunposted = $arrItem[$key2][substr($val, 8, 2)]['0'];
					// $tab.="<td style='text-align:center;color:red'>".$nilaiunposted."</td>";
					// $totnilaiunposted += $nilaiunposted;
					 @$nilaiunposted = $arrItem[$key2][substr($val, 8, 2)]['0'];
					$tab.="<td style='text-align:center;cursor:pointer;color:red' onclick=\"detail4('".$key2."','".$val."','rwtmsn','0','detail4','event');\">".$nilaiunposted."</td>";
					$totnilaiunposted += $nilaiunposted;					

				}
				$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>
					</tr>";
			}
		}
	}
	$tab.="</tbody>
		</table>";
	//Get Pengolahan
	$str = "select * from ".$dbname.".pabrik_pengolahan where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem2[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][$bar['posting']] += 1;
	}
	$tab.="<hr style='border: 0;border-bottom: 1px dashed #ccc;background: #999;'><table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 3)."'>MONITORING PENGOLAHAN</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 3)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		if (isset($arrunit[$key])) {
			foreach($arrunit[$key]as $key2 => $val2) {
				$tab.="<tr class=rowcontent>
					<td rowspan=2>".$val2['nama']." (".$key2.")</td>
					<td style='text-align:center'>posted</td>";
				$totnilaiposted = 0;
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					//  @$nilaiunposted = $arrItem2[$key2][substr($val, 8, 2)]['1'];
					// $tab.="<td style='text-align:center;'>".$nilaiunposted."</td>";
					// $totnilaiunposted += $nilaiunposted;
					 @$nilaiunposted = $arrItem2[$key2][substr($val, 8, 2)]['1'];
					$tab.="<td style='text-align:center;cursor:pointer' onclick=\"detail4('".$key2."','".$val."','pengol','1','detail4','event');\">".$nilaiunposted."</td>";
					$totnilaiunposted += $nilaiunposted;					
				}
				$tab.="<td style='text-align:center;'>".$totnilaiunposted."</td>
					</tr>
					<tr class=rowcontent>";
				$tab.="<td style='text-align:center;color:red'>not posted</td>";
				foreach($arrhari as $val) {
					//  @$nilaiposted = $arrItem2[$key2][substr($val, 8, 2)]['0'];
					// $tab.="<td style='text-align:center'>".$nilaiposted."</td>";
					// $totnilaiposted += $nilaiposted;
					 @$nilaiposted = $arrItem2[$key2][substr($val, 8, 2)]['0'];
					$tab.="<td style='text-align:center;cursor:pointer;color:red' onclick=\"detail4('".$key2."','".$val."','pengol','0','detail4','event');\">".$nilaiposted."</td>";
					$totnilaiposted += $nilaiposted;					
				}
				$tab.="<td style='text-align:center;color:red'>".$totnilaiposted."</td>";
				$tab.="</tr>";
			}
		}
	}
	$tab.="</tbody>
		</table>";
	//Get Stok CPO dan PK
	$str = "select * from ".$dbname.".pabrik_masukkeluartangki where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem4[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][0] += 1;
	}
	$tab.="<hr style='border: 0;border-bottom: 1px dashed #ccc;background: #999;'><table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 3)."'>MONITORING STOK CPO dan KERNEL</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 3)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		if (isset($arrunit[$key])) {
			foreach($arrunit[$key]as $key2 => $val2) {
				$tab.="<tr class=rowcontent>
					<td rowspan=2>".$val2['nama']." (".$key2.")</td>
					<td rowspan=2 style='text-align:center'>posted</td>";
				$totnilaiposted = 0;
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					//  @$nilaiunposted = $arrItem4[$key2][substr($val, 8, 2)]['0'];
					// $tab.="<td rowspan=2 style='text-align:center;'>".$nilaiunposted."</td>";
					// $totnilaiunposted += $nilaiunposted;
					 @$nilaiunposted = $arrItem4[$key2][substr($val, 8, 2)]['0'];
					$tab.="<td style='text-align:center;cursor:pointer' onclick=\"detail4('".$key2."','".$val."','maskel','0','detail4','event');\">".$nilaiunposted."</td>";
					$totnilaiunposted += $nilaiunposted;										
				}
				$tab.="<td rowspan style='text-align:center;'>".$totnilaiunposted."</td>
					</tr>
					<tr class=rowcontent>";
				// $tab.="<td style='text-align:center;color:red'>not posted</td>";
				foreach($arrhari as $val) {
					 @$nilaiposted = $arrItem4[$key2][substr($val, 8, 2)]['1'];
					// $tab.="<td style='text-align:center'>".$nilaiposted."</td>";
					$totnilaiposted += $nilaiposted;
				}
				// $tab.="<td style='text-align:center;color:red'>".$totnilaiposted."</td>";
				$tab.="</tr>";
			}
		}
	}
	$tab.="</tbody>
		</table>";
	//Get Produksi
	$str = "select * from ".$dbname.".pabrik_produksi where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem3[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)][0] += 1;
	}
	$tab.="<hr style='border: 0;border-bottom: 1px dashed #ccc;background: #999;'><table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 3)."'>MONITORING PRODUKSI</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 3)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		if (isset($arrunit[$key])) {
			foreach($arrunit[$key]as $key2 => $val2) {
				$tab.="<tr class=rowcontent>
					<td rowspan=2>".$val2['nama']." (".$key2.")</td>
					<td rowspan=2 style='text-align:center'>posted</td>";
				$totnilaiposted = 0;
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					//  @$nilaiunposted = $arrItem3[$key2][substr($val, 8, 2)]['0'];
					// $tab.="<td rowspan=2 style='text-align:center;'>".$nilaiunposted."</td>";
					// $totnilaiunposted += $nilaiunposted;
					 @$nilaiunposted = $arrItem3[$key2][substr($val, 8, 2)]['0'];
					$tab.="<td style='text-align:center;cursor:pointer' onclick=\"detail4('".$key2."','".$val."','produk','0','detail4','event');\">".$nilaiunposted."</td>";
					$totnilaiunposted += $nilaiunposted;										
				}
				$tab.="<td rowspan=2 style='text-align:center;'>".$totnilaiunposted."</td>
					</tr>
					<tr class=rowcontent>";
				// $tab.="<td style='text-align:center;color:red'>not posted</td>";
				foreach($arrhari as $val) {
					 @$nilaiposted = $arrItem3[$key2][substr($val, 8, 2)]['1'];
					// $tab.="<td style='text-align:center'>".$nilaiposted."</td>";
					$totnilaiposted += $nilaiposted;
				}
				// $tab.="<td style='text-align:center;color:red'>".$totnilaiposted."</td>";
				$tab.="</tr>";
			}
		}
	}
	$tab.="</tbody>
		</table>";
	echo $tab;
	break;
case 'preview5':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan
	$where = '';
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];
	if(($_SESSION['empl']['tipelokasitugas']!='HOLDING')and($_SESSION['empl']['tipelokasitugas']!='KANWIL')){
		$where=" and (kodeorganisasi in ('".implode("','",array_keys(getOrgDetail(10)))."') or kodeorganisasi in ('".implode("','",array_keys(getOrgDetail(18)))."') or kodeorganisasi in ('".implode("','",array_keys(getOrgDetail(29)))."') or tipe='PT')";
		$barx=$perusahaan;
	}else{
		$where=" and (kodeorganisasi like '%' or tipe='PT')";
		$barx=$perusahaan;
	}

	

	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT' and in_array($bar['kodeorganisasi'], array_keys(getOrgDetail(3)))) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 6 and($bar['tipe'] == 'TRAKSI' || $bar['tipe'] == 'WORKSHOP')) {
			$arrunit[getindukPT($bar['induk'])][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[getindukPT($bar['induk'])][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
		if (strlen($bar['kodeorganisasi']) == 6 and($bar['tipe'] == 'TRAKSI' || $bar['tipe'] == 'WORKSHOP')) {
		// if (strlen($bar['kodeorganisasi']) == 6) {
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
	}
	
	//Get Pekerjaan
	$str = "select * from ".$dbname.".vhc_runht where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem2[$bar['kodeorg']."TK"][substr($bar['tanggal'], 8, 2)][$bar['posting']] += 1;
	}
	$tab.="<hr style='border: 0;border-bottom: 1px dashed #ccc;background: #999;'><table class=sortable cellspacing=1 border=0 width=100%>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 3)."'>MONITORING PEKERJAAN</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 3)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		foreach($arrunit[$key]as $key2 => $val2) {
			if(getNamaOrg($key2,'tipe') == 'TRAKSI'){
				$tab.="<tr class=rowcontent>
					<td rowspan=2>".$val2['nama']." (".$key2.")</td>
					<td style='text-align:center'>posted</td>";
				$totnilaiposted = 0;
				foreach($arrhari as $val) {
					 @$nilaiposted = $arrItem2[$key2][substr($val, 8, 2)]['1'];
					$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".substr($key2,0,4)."','".$val."','pek','1')>".$nilaiposted."</td>";
					$totnilaiposted += $nilaiposted;
				}
				$tab.="<td style='text-align:center;'>".$totnilaiposted."</td>
					</tr>
					<tr class=rowcontent>
					<td style='text-align:center;color:red'>not posted</td>";
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					 @$nilaiunposted = $arrItem2[$key2][substr($val, 8, 2)]['0'];
					$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=popup('".substr($key2,0,4)."','".$val."','pek','0')>".$nilaiunposted."</td>";
					$totnilaiunposted += $nilaiunposted;
				}
				$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>
					</tr>";
			}
		}
	}
	$tab.="</tbody>
		</table>";
		
	//Get Service
	$str = "select * from ".$dbname.".vhc_penggantianht where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem[substr($bar['kodeorg'], 0, 6)][substr($bar['tanggal'], 8, 2)][$bar['posting']] += 1;
	}
	$tab.= "<table class=sortable cellpadding=5 cellspacing=1 border=0 width=100%>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 4)."'>MONITORING SERVICE</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['traksi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 4)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		foreach($arrunit[$key]as $key2 => $val2) {
			if (isset($arrgdg[substr($key2,0,4)]) && getNamaOrg($key2,'tipe') == 'WORKSHOP') {
				$tab.="<tr class=rowcontent>
					<td rowspan='".(1 * 2)."'>".$val2['nama']." (".substr($key2,0,4).")</td>";
				$count = 0;
				foreach($arrgdg[substr($key2,0,4)]as $key3 => $val3) {
					$count++;
					if ($count == 1) {
						$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
						$tab.="<td style='text-align:center'>posted</td>";
					}
				}
				$totnilaiposted = 0;
				foreach($arrhari as $val) {
					 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
					$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','service','1')>".$nilaiposted."</td>";
					$totnilaiposted += $nilaiposted;
				}
				$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
				$tab.="</tr>";
				$tab.="<tr class=rowcontent>";
				$tab.="<td style='text-align:center;color:red'>not posted</td>";
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
					$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=popup('".$key3."','".$val."','service','0')>".$nilaiunposted."</td>";
					$totnilaiunposted += $nilaiunposted;
				}
				$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>";
				$tab.="</tr>";
				$count = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					$count++;
					if ($count != 1) {
						$tab.="<tr class=rowcontent>";
						$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
						$tab.="<td style='text-align:center'>posted</td>";
						$totnilaiposted = 0;
						foreach($arrhari as $val) {
							 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
							$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','service','1')>".$nilaiposted."</td>";
							$totnilaiposted += $nilaiposted;
						}
						$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
						$tab.="</tr>";
						$tab.="<tr class=rowcontent>";
						$tab.="<td style='text-align:center;color:red'>not posted</td>";
						$totnilaiunposted = 0;
						foreach($arrhari as $val) {
							 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
							$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=popup('".$key3."','".$val."','service','0')>".$nilaiunposted."</td>";
							$totnilaiunposted += $nilaiunposted;
						}
						$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>";
						$tab.="</tr>";
					}
				}
			}
		}
	}
	$tab.="</tbody>
		</table>";

	//Get Sipil
	$str = "select * from ".$dbname.".vhc_spl_aktifitas where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$kode = substr($bar['kodeorg'], 0, 4); 
		@$arrItem3[$kode][substr($bar['tanggal'], 8, 2)][$bar['jurnal']] += 1;
	}


	$tab.="<hr style='border: 0;border-bottom: 1px dashed #ccc;background: #999;'><table class=sortable cellspacing=1 border=0 width=100%>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 3)."'>MONITORING BKM SIPIL</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	$tempunt = '';
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 3)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		foreach($arrunit[$key]as $key2 => $val2) {
			if(getNamaOrg($key2,'tipe') == 'TRAKSI'){

				if (in_array(getNamaOrg(getindukPT($key2),'tipe'), ['KEBUN','PABRIK'])&& $tempunt != getindukPT($key2)){
					$tab.="<tr class=rowcontent>
						<td rowspan=2>".getNamaOrg(substr($key2,0,4))." (".substr($key2,0,4).")</td>
						<td style='text-align:center'>posted</td>";
					$totnilaiposted = 0;
					foreach($arrhari as $val) {
						@$nilaiposted = $arrItem3[substr($key2,0,4)][substr($val, 8, 2)]['1'];
						$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".substr($key2,0,4)."','".$val."','spl','1')>".$nilaiposted."</td>";
						$totnilaiposted += $nilaiposted;
					}
					$tab.="<td style='text-align:center;'>".$totnilaiposted."</td>
						</tr>
						<tr class=rowcontent>
						<td style='text-align:center;color:red'>not posted</td>";
					$totnilaiunposted = 0;
					foreach($arrhari as $val) {
						// @$nilaiunposted = $arrItem3[$key2][substr($val, 8, 2)]['0'];
						@$nilaiunposted = $arrItem3[substr($key2,0,4)][substr($val, 8, 2)]['0'];
						$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=popup('".substr($key2,0,4)."','".$val."','spl','0')>".$nilaiunposted."</td>";
						$totnilaiunposted += $nilaiunposted;
					}
					$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>
						</tr>";
				}
				$tempunt = getindukPT($key2);
			}
		}
	}
	$tab.="</tbody>
		</table>";
	echo $tab;
	break;
case 'preview6':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan
	$where = '';
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];

	// if(($_SESSION['empl']['tipelokasitugas']!='HOLDING')and($_SESSION['empl']['tipelokasitugas']!='KANWIL')){
	// 	$where=" and (kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' or tipe='PT')";
	// 	$barx=$perusahaan;
	// }else{
		$where=" and (kodeorganisasi like '%' or tipe='PT')";
		$barx=$perusahaan;
	// }

	

	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT' and in_array($bar['kodeorganisasi'], array_keys(getOrgDetail(3)))) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 4) {
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			$arrgdg[$bar['kodeorganisasi']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
		}
		if (strlen($bar['kodeorganisasi']) == 6) {
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
	}
	//Get Absensi
	$str = "select * from ".$dbname.".sdm_absensidt_vw where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)]['0'] += 1;
	}
	$tab = "<table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 4)."'>MONITORING ABSENSI</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['unit']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 4)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		foreach($arrunit[$key]as $key2 => $val2) {
			if (isset($arrgdg[$key2])) {
				$tab.="<tr class=rowcontent>
					<td rowspan='".(count($arrgdg[$key2]) * 2)."'>".$val2['nama']." (".$key2.")</td>";
				$count = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					if ( @$val3['nama'] == '') {
						$val3['nama'] = 'KANTOR';
					}
					$count++;
					if ($count == 1) {
						$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
						$tab.="<td rowspan=2 style='text-align:center'>Posted</td>";
					}
				}
				$count = 0;
				$totnilaiposted = 0;
				$totnilaiunposted = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					$count++;
					if ($count == 1) {
						foreach($arrhari as $val) {
							 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
							$tab.="<td rowspan=2 style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','abs','1')>".$nilaiunposted."</td>";
							$totnilaiunposted += $nilaiunposted;
						}
						$tab.="<td rowspan=2 style='text-align:center'>".$totnilaiunposted."</td>";
						$tab.="</tr>";
						$tab.="<tr class=rowcontent>";
						foreach($arrhari as $val) {
							 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
							$totnilaiposted += $nilaiposted;
						}
						$tab.="</tr>";
					}
				}
				$count = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					$count++;
					if ($count != 1) {
						if ( @$val3['nama'] == '') {
							$val3['nama'] = 'KANTOR';
						}
						$tab.="<tr class=rowcontent>";
						$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
						$tab.="<td rowspan=2 style='text-align:center'>Posted</td>";
						$totnilaiposted = 0;
						$totnilaiunposted = 0;
						foreach($arrhari as $val) {
							 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
							$tab.="<td rowspan=2 style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','abs','1')>".$nilaiunposted."</td>";
							$totnilaiunposted += $nilaiunposted;
						}
						$tab.="<td rowspan=2 style='text-align:center'>".$totnilaiunposted."</td>";
						$tab.="</tr>";
						$tab.="<tr class=rowcontent>";
						foreach($arrhari as $val) {
							 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
							$totnilaiposted += $nilaiposted;
						}
						$tab.="</tr>";
					}
				}
			}
		}
	}
	$tab.="</tbody>
		</table>";
	//Get Lembur
	$str = "select * from ".$dbname.".sdm_lemburdt where tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		 @$arrItem22[$bar['kodeorg']][substr($bar['tanggal'], 8, 2)]['0'] += 1;
	}
	$tab.="<hr style='border: 0;border-bottom: 1px dashed #ccc;background: #999;'><table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 4)."'>MONITORING LEMBUR</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['unit']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 4)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		foreach($arrunit[$key]as $key2 => $val2) {
			if (isset($arrgdg[$key2])) {
				$tab.="<tr class=rowcontent>
					<td rowspan='".(count($arrgdg[$key2]) * 2)."'>".$val2['nama']." (".$key2.")</td>";
				$count = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					$count++;
					if ($count == 1) {
						$tab.="<td rowspan=2>". @$val3['nama']." (".$key3.")</td>";
						$tab.="<td rowspan=2 style='text-align:center'>Posted</td>";
					}
				}
				$totnilaiposted = 0;
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					 @$nilaiunposted = $arrItem22[$key3][substr($val, 8, 2)]['0'];
					$tab.="<td rowspan=2 style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','lbr','1')>".$nilaiunposted."</td>";
					$totnilaiunposted += $nilaiunposted;
				}
				$tab.="<td rowspan=2 style='text-align:center'>".$totnilaiunposted."</td>";
				$tab.="</tr>";
				$tab.="<tr class=rowcontent>";
				foreach($arrhari as $val) {
					 @$nilaiposted = $arrItem22[$key3][substr($val, 8, 2)]['1'];
					$totnilaiposted += $nilaiposted;
				}
				$tab.="</tr>";
				$count = 0;
				foreach($arrgdg[$key2]as $key3 => $val3) {
					$count++;
					if ($count != 1) {
						$tab.="<tr class=rowcontent>";
						$tab.="<td rowspan=2>". @$val3['nama']." (".$key3.")</td>";
						$tab.="<td rowspan=2 style='text-align:center'>Posted</td>";
						$totnilaiposted = 0;
						$totnilaiunposted = 0;
						foreach($arrhari as $val) {
							 @$nilaiunposted = $arrItem22[$key3][substr($val, 8, 2)]['0'];
							$tab.="<td rowspan=2 style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','lbr','1')>".$nilaiunposted."</td>";
							$totnilaiunposted += $nilaiunposted;
						}
						$tab.="<td rowspan=2 style='text-align:center'>".$totnilaiunposted."</td>";
						$tab.="</tr>";
						$tab.="<tr class=rowcontent>";
						foreach($arrhari as $val) {
							@$nilaiposted = $arrItem22[$key3][substr($val, 8, 2)]['1'];
							$totnilaiposted += $nilaiposted;
						}
						$tab.="</tr>";
					}
				}
			}
		}
	}
	$tab.="</tbody>
		</table>";

	## get proses finger
	$str = "select * from ".$dbname.".upload_absensi where tanggalabsen like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {

		if($bar['subbagian'] == ""){
			$kodeorg = $bar['kodeorg'];
		}else{
			$kodeorg = $bar['subbagian'];
		}

    	@$arrItem2[$kodeorg][substr($bar['tanggalabsen'], 8, 2)][$bar['posting']] += 1;
	}
	
	$tab.="<hr style='border: 0;border-bottom: 1px dashed #ccc;background: #999;'><table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 4)."'>MONITORING PROSES FINGER</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['unit']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
		foreach($arrpt as $key => $val) {
			$tab.="<tr class=rowcontent>
				<td colspan='".(count($arrhari) + 4)."'><b>".$val['nama']." (".$key.")</b></td>
				</tr>";
			foreach($arrunit[$key]as $key2 => $val2) {
				if (isset($arrgdg[$key2])) {
					$tab.="<tr class=rowcontent>
						<td rowspan='".(count($arrgdg[$key2]) * 2)."'>".$val2['nama']." (".$key2.")</td>";
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {

						if ( @$val3['nama'] == '') {
							$val3['nama'] = 'KANTOR';
						}

						$count++;
						if ($count == 1) {
							$tab.="<td rowspan=2>". @$val3['nama']." (".$key3.")</td>";
							$tab.="<td rowspan=2 style='text-align:center'>Posted/Unposted</td>";$key3no1=$key3;
						}
					}

					$totnilaiposted = 0;
					$totnilaiunposted = 0;

					foreach($arrhari as $val) {
						$tglHari = substr($val, 8, 2);
						$posted   = isset($arrItem2[$key3no1][$tglHari]['1']) ? $arrItem2[$key3no1][$tglHari]['1'] : 0;
                    	$unposted = isset($arrItem2[$key3no1][$tglHari]['0']) ? $arrItem2[$key3no1][$tglHari]['0'] : 0;

 					// tampilkan posted/unposted dalam satu sel
                    $tab .= "<td style='text-align:center;cursor:pointer' onclick=popup('".$key3no1."','".$val."','finger','1')>
                                <span style='color:green;font-weight:bold'>".$posted."</span> /
                                <span style='color:red;font-weight:bold'>".$unposted."</span>
                             </td>";
						$totnilaiposted += $posted;
                    	$totnilaiunposted += $unposted;
					}

					$tab .= "<td style='text-align:center'>
                            <b><span style='color:green'>".$totnilaiposted."</span> /
                            <span style='color:red'>".$totnilaiunposted."</span></b>
                         </td>";

					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$totnilaiposted = 0;
					$totnilaiunposted = 0;
					foreach($arrhari as $val) {
						$tglHari = substr($val, 8, 2);
						$posted   = isset($arrItem2[$key3][$tglHari]['1']) ? $arrItem2[$key3][$tglHari]['1'] : 0;
                    	$unposted = isset($arrItem2[$key3][$tglHari]['0']) ? $arrItem2[$key3][$tglHari]['0'] : 0;						
						
						$totnilaiposted += $posted;
                    	$totnilaiunposted += $unposted;
					}
					$tab.="</tr>";
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						if ( @$val3['nama'] == '') {
							$val3['nama'] = 'KANTOR';
						}
						
						$count++;
						if ($count != 1) {
							$tab.="<tr class=rowcontent>";
							$tab.="<td rowspan=2>". @$val3['nama']." (".$key3.")</td>";
							$tab.="<td rowspan=2 style='text-align:center'>Posted/Unposted</td>";
							$totnilaiposted = 0;
							$totnilaiunposted = 0;
							foreach($arrhari as $val) {
								$tglHari = substr($val, 8, 2);
								$posted   = isset($arrItem2[$key3][$tglHari]['1']) ? $arrItem2[$key3][$tglHari]['1'] : 0;
                    			$unposted = isset($arrItem2[$key3][$tglHari]['0']) ? $arrItem2[$key3][$tglHari]['0'] : 0;

								 $tab .= "<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','finger','1')>
                                <span style='color:green;font-weight:bold'>".$posted."</span> /
                                <span style='color:red;font-weight:bold'>".$unposted."</span>
                             		</td>";
									$totnilaiposted += $posted;
									$totnilaiunposted += $unposted;
							}
								$tab .= "<td style='text-align:center'>
															<b><span style='color:green'>".$totnilaiposted."</span> /
															<span style='color:red'>".$totnilaiunposted."</span></b>
														</td>";
						 							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
							$totnilaiposted = 0;
							$totnilaiunposted = 0;
							foreach($arrhari as $val) {
								$tglHari = substr($val, 8, 2);
								$posted   = isset($arrItem2[$key3][$tglHari]['1']) ? $arrItem2[$key3][$tglHari]['1'] : 0;
                    			$unposted = isset($arrItem2[$key3][$tglHari]['0']) ? $arrItem2[$key3][$tglHari]['0'] : 0;						
						
								$totnilaiposted += $posted;
                    			$totnilaiunposted += $unposted;
							}
							
							$tab.="</tr>";
						}
					}
				}
			}
		}
		$tab.="</tbody>
			</table>";

	
	echo $tab;
	break;
case 'preview7':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan

	$where = '';
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];
	if(($_SESSION['empl']['tipelokasitugas']!='HOLDING')and($_SESSION['empl']['tipelokasitugas']!='KANWIL')){
		$where=" and (kodeorganisasi in ('".implode("','",array_keys(getOrgDetail(10)))."') or tipe='PT')";
		$barx=$perusahaan;
	}else{
		$where=" and (kodeorganisasi like '%' or tipe='PT')";
		$barx=$perusahaan;
	}

	

	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT' and in_array($bar['kodeorganisasi'], array_keys(getOrgDetail(3)))) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 4 and $bar['tipe'] == 'KEBUN') {
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			//$arrgdg[$bar['kodeorganisasi']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			//$arrgdg[$bar['kodeorganisasi']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
		if (strlen($bar['kodeorganisasi']) == 6 and $bar['tipe'] == 'AFDELING') {
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
	}
	//Get Transaksi Kebun
	$whr = '';
	if ($perusahaan != '') {
		$whr = " and c.alokasi ='".$perusahaan."'";
		//$whr = " and a.kodeorg ='".$perusahaan."'";
	}
	$str = "SELECT a.tanggal, a.kodeorg, a.divcode, a.jumlahtandan1 as jjgpks, a.nospb as spbpks, a.beratbersih as kgpks, b.nospb as spbkbn,
		count(DISTINCT a.nospb) as jlhspbpks, count(DISTINCT b.nospb) as jlhspbkbn, sum(b.jjg) as jjgkbn, sum(b.kgwb) as kgkbn,
		substr(a.kodeorg,1,6), c.alokasi
		FROM ".$dbname.".pabrik_timbangan a
		left join ".$dbname.".kebun_spb_vw b on a.nospb=b.nospb
		left join ".$dbname.".organisasi c on a.kodeorg=c.kodeorganisasi
		where a.kodebarang='40000003'  and a.divcode!='' and a.tanggal like '".$periode."%' ".$whr." group by a.nospb";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if ($tipe == 'inp') {
			 @$arrItem[$bar['divcode']][substr($bar['tanggal'], 8, 2)]['1'] += $bar['jlhspbpks'];
			 @$arrItem[$bar['divcode']][substr($bar['tanggal'], 8, 2)]['0'] += $bar['jlhspbkbn'];
		} else {
			 @$arrItem[$bar['divcode']][substr($bar['tanggal'], 8, 2)]['1'] += number_format($bar['kgpks'] / 1000, 2);
			 @$arrItem[$bar['divcode']][substr($bar['tanggal'], 8, 2)]['0'] += number_format($bar['kgkbn'] / 1000, 2);
		}
	}
	$tab = "<table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 4)."'>MONITORING ".($tipe == 'pos' ? 'POSTING SPB / SPAT' : ($tipe == 'inp' ? 'INPUT SPB / SPAT' : ''))."</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['divisi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 4)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		if (isset($arrunit[$key]))
			foreach($arrunit[$key]as $key2 => $val2) {
				if (isset($arrgdg[$key2])) {
					$tab.="<tr class=rowcontent>
						<td rowspan='".(count($arrgdg[$key2]) * 2)."'>".$val2['nama']." (".$key2.")</td>";
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count == 1) {
							$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
							if ($tipe == 'pos') {
								$tab.="<td style='text-align:center'>ton pks</td>";
							} else {
								$tab.="<td style='text-align:center'>spb pks</td>";
							}
						}
					}
					$totnilaiposted = 0;
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count == 1) {
							foreach($arrhari as $val) {
								 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
								$clr = '';
								if ($nilaiposted == 0) {
									$clr = " color=#D7EBFA";
								}
								$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','spb','pks') title='Tipe = Input, dalam Jlh SPB, Tipe = Posting, Sat dalam Ton'><font ".$clr.">".$nilaiposted."</font></td>";
								$totnilaiposted += $nilaiposted;
							}
							$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
							if ($tipe == 'pos') {
								$tab.="<td style='text-align:center;color:blue'>ton kbn</td>";
							} else {
								$tab.="<td style='text-align:center;color:blue'>spb kbn</td>";
							}
							$totnilaiunposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
								 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
								$color = $clr = '';
								if (($nilaiposted - $nilaiunposted) > 5 && $tipe == 'pos') {
									$color = " color=red";
								}
								elseif(($nilaiposted != $nilaiunposted)) {
									$color = " color=red";
								}
								if ($nilaiposted == 0 || $nilaiunposted == 0) {
									$clr = " color=#D7EBFA";
								}
								$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','spb','kbn') title='Tipe = Input, dalam Jlh SPB, Tipe = Posting, Sat dalam Ton'><font ".$color." ".$clr.">".$nilaiunposted."</font></td>";
								$totnilaiunposted += $nilaiunposted;
							}
							$color = '';
							if (($totnilaiposted - $totnilaiunposted) > 10 && $tipe == 'pos') {
								$color = " color=red";
							}
							elseif(($totnilaiposted != $totnilaiunposted)) {
								$color = " color=red";
							}
							$tab.="<td style='text-align:center;'><font ".$color.">".$totnilaiunposted."</font></td>";
							$tab.="</tr>";
						}
					}
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count != 1) {
							$tab.="<tr class=rowcontent>";
							$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
							if ($tipe == 'pos') {
								$tab.="<td style='text-align:center'>ton pks</td>";
							} else {
								$tab.="<td style='text-align:center'>spb pks</td>";
							}
							$totnilaiposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
								$clr = '';
								if ($nilaiposted == 0) {
									$clr = " color=#D7EBFA";
								}
								$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','spb','pks') title='Tipe = Input, dalam Jlh SPB, Tipe = Posting, Sat dalam Ton'><font ".$clr.">".$nilaiposted."</font></td>";
								$totnilaiposted += $nilaiposted;
							}
							$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
							if ($tipe == 'pos') {
								$tab.="<td style='text-align:center;color:blue'>ton kbn</td>";
							} else {
								$tab.="<td style='text-align:center;color:blue'>spb kbn</td>";
							}
							$totnilaiunposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
								 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
								$color = $clr = '';
								if (($nilaiposted - $nilaiunposted) > 5 && $tipe == 'pos') {
									$color = " color=red";
								}
								elseif(($nilaiposted != $nilaiunposted)) {
									$color = " color=red";
								}
								if ($nilaiposted == 0 || $nilaiunposted == 0) {
									$clr = " color=#D7EBFA";
								}
								$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key3."','".$val."','spb','kbn') title='Tipe = Input, dalam Jlh SPB, Tipe = Posting, Sat dalam Ton'><font ".$color." ".$clr.">".$nilaiunposted."</font></td>";
								$totnilaiunposted += $nilaiunposted;
							}
							$color = '';
							if (($totnilaiposted - $totnilaiunposted) > 10 && $tipe == 'pos') {
								$color = " color=red";
							}
							elseif(($totnilaiposted != $totnilaiunposted)) {
								$color = " color=red";
							}
							$tab.="<td style='text-align:center;'><font ".$color.">".$totnilaiunposted."</font></td>";
							$tab.="</tr>";
						}
					}
				}
			}
	}
	$tab.="</tbody>
		</table>";
	echo $tab;
	break;
case 'preview8':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan
	$where = '';
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];
	if(($_SESSION['empl']['tipelokasitugas']!='HOLDING')and($_SESSION['empl']['tipelokasitugas']!='KANWIL')){
		$where=" and (kodeorganisasi in ('".implode("','",array_keys(getOrgDetail(10)))."') or tipe='PT')";
		$barx=$perusahaan;
	}else{
		$where=" and (kodeorganisasi like '%' or tipe='PT')";
		$barx=$perusahaan;
	}

	//$where.=" and inti='1'";

	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT' and in_array($bar['kodeorganisasi'], array_keys(getOrgDetail(3)))) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 4 and $bar['tipe'] == 'KEBUN') {
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			#$arrgdg[$bar['kodeorganisasi']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			#$arrgdg[$bar['kodeorganisasi']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
		if (strlen($bar['kodeorganisasi']) == 6 and($bar['tipe'] == 'AFDELING' or $bar['tipe'] == 'BIBITAN')) {
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrgdg[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
	}
	//Get Transaksi Rekap Panen
	$str = "select * from ".$dbname.".kebun_rekappnn_vw a
		where a.tanggal like '".$periode."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		@$arrItem[$bar['divisi']][substr($bar['tanggal'], 8, 2)][$bar['posting']] += $bar['jjgpanen'];
		
	}
	
	$tab = "<table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 4)."'>MONITORING REKAP PANEN (JJG)</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['divisi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 4)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";
		if (isset($arrunit[$key]))
			foreach($arrunit[$key]as $key2 => $val2) {
				if (isset($arrgdg[$key2])) {
					$tab.="<tr class=rowcontent>
						<td rowspan='".(count($arrgdg[$key2]) * 2)."'>".$val2['nama']." (".$key2.")</td>";
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count == 1) {
							$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
							$tab.="<td style='text-align:center'>posted</td>";
						}
					}
					$totnilaiposted = 0;
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count == 1) {
							foreach($arrhari as $val) {
								 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
								$tab.="<td style='text-align:center;cursor:pointer' onclick=\"popup('".$key3."','".$val."','REKAPPNN','1');\">".$nilaiposted."</td>"; //isi baris pertama posting
								$totnilaiposted += $nilaiposted;
							}
							$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
							$tab.="<td style='text-align:center;color:red'>not posted</td>";
							$totnilaiunposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
								$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=\"popup('".$key3."','".$val."','REKAPPNN','0');\">".$nilaiunposted."</td>"; //isi baris 1 unpost
								$totnilaiunposted += $nilaiunposted;
							}
							$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>";
							$tab.="</tr>";
						}
					}
					$count = 0;
					foreach($arrgdg[$key2]as $key3 => $val3) {
						$count++;
						if ($count != 1) {
							$tab.="<tr class=rowcontent>";
							$tab.="<td rowspan=2>".$val3['nama']." (".$key3.")</td>";
							$tab.="<td style='text-align:center'>posted</td>";
							$totnilaiposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiposted = $arrItem[$key3][substr($val, 8, 2)]['1'];
								$tab.="<td style='text-align:center;cursor:pointer' onclick=\"popup('".$key3."','".$val."','REKAPPNN','1');\">".$nilaiposted."</td>";
								$totnilaiposted += $nilaiposted;
							}
							$tab.="<td style='text-align:center'>".$totnilaiposted."</td>";
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
							$tab.="<td style='text-align:center;color:red'>not posted</td>";
							$totnilaiunposted = 0;
							foreach($arrhari as $val) {
								 @$nilaiunposted = $arrItem[$key3][substr($val, 8, 2)]['0'];
								$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=\"popup('".$key3."','".$val."','REKAPPNN','0');\">".$nilaiunposted."</td>";
								$totnilaiunposted += $nilaiunposted;
							}
							$tab.="<td style='text-align:center;color:red'>".$totnilaiunposted."</td>";
							$tab.="</tr>";
						}
					}
				}
			}
	}
	$tab.="</tbody>
		</table>";
	echo $tab;
	break;
//Umar
case 'preview9':
	//GET Tanggal Awal dan Tanggal Akhir
	$tglawal = date("".$periode."-01");
	$tglakhir = date('Y-m-t', strtotime($tglawal));
	$arrhari = rangeTanggal($tglawal, $tglakhir);
	//Get Kode Perusahaan
	$where = '';
	
	
	
	/*
	$optPT= makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$perusahaan."'");
	$barx=$optPT[$perusahaan];
	if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
		$where=" and (kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' or tipe='PT')";
		$barx=$perusahaan;
	}

	

	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi where length(kodeorganisasi) <= 6 ".$where." order by tipe, kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	// exit("error :".$perusahaan);
	while ($bar = $res->fetch()) {
		if ($perusahaan == '') {
			if ($bar['tipe'] == 'PT') {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		} else {
			if ($bar['tipe'] == 'PT' and $bar['kodeorganisasi'] == $barx) {
				$arrpt[$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
				$arrpt[$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			}
		}
		if (strlen($bar['kodeorganisasi']) == 4 and $_SESSION['empl']['tipelokasitugas']=='HOLDING') {
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
		}
		if(strlen($bar['kodeorganisasi']) == 4 and $_SESSION['empl']['tipelokasitugas']!='HOLDING' and $bar['induk']==$perusahaan)
		{
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
			$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $bar['namaorganisasi'];
			
		}
	}
	*/
	
	
	if($perusahaan!=''){
		$whereunit=" and induk='".$perusahaan."'";
	}else {
		$whereunit=" and induk in ('" . implode("','", array_keys(getOrgDetail(3))) . "')";
	}
	
	#= nama organisasi	
	$str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
		where 1=1 ".$whereunit." and (length(kodeorganisasi)='4' or length(kodeorganisasi)='3') order by kodeorganisasi asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);	
	while ($bar = $res->fetch()) {
		$nmorganisasi[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
	}
	
	
	$str = "select kodeorganisasi,namaorganisasi,tipe,induk from ".$dbname.".organisasi 
		where 1=1 ".$whereunit." and length(kodeorganisasi)='4' order by kodeorganisasi asc";
		 //echo $str;exit();
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	// exit("error :".$perusahaan);
	while ($bar = $res->fetch()) {
		$arrpt[$bar['induk']]['kode'] = $bar['induk'];
		$arrpt[$bar['induk']]['nama'] = $nmorganisasi[$bar['induk']];
		$arrunit[$bar['induk']][$bar['kodeorganisasi']]['kode'] = $bar['kodeorganisasi'];
		$arrunit[$bar['induk']][$bar['kodeorganisasi']]['nama'] = $nmorganisasi[$bar['kodeorganisasi']];
		
	}
	
	
	// echo "<pre>";
	// print_r($arrpt);
	// echo "</pre>";
	// exit();
	
	
	
	
	
	
	
	
	
	
	//Get Kas Bank
	$arrItem = array();
	
	$str = "select * from ".$dbname.".keu_kasbankht where tanggalinput like '".$periode."%' AND posting = 1";
	//exit("error :".$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		
		 @$arrItem[$bar['kodeorg']][substr($bar['tanggalinput'], 8, 2)][$bar['posting']] += 1;
	}
	$tab = "<table class=sortable cellpadding=5 cellspacing=1 border=0>
		<thead>
		<tr class=rowheader>
		<th style='text-align:center;height:30px;font-weight:bold' colspan='".(count($arrhari) + 3)."'>MONITORING KAS BANK</th>
		</tr>
		<tr class=rowheader>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</th>
		<th style='text-align:center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
		<th style='text-align:center' colspan='".count($arrhari)."'>".$_SESSION['lang']['tanggal']."</th>
		<th style='text-align:center' rowspan=2>Total</th>
		</tr>
		<tr>";
	$val=array();	
	
	foreach($arrhari as $val) {
		$tab.="<th style='text-align:center'>".substr($val, 8, 2)."</th>";
		
	}
	$tab.="</tr>
		</thead>
		<tbody>";
	foreach($arrpt as $key => $val) {
		$tab.="<tr class=rowcontent>
			<td colspan='".(count($arrhari) + 3)."'><b>".$val['nama']." (".$key.")</b></td>
			</tr>";		
		//exit("error :".$key);	
		if (isset($arrunit[$key]))
			foreach($arrunit[$key]as $key2 => $val2) {
				$tab.="<tr class=rowcontent>
					<td rowspan=2>".$val2['nama']." (".$key2.")</td>
					<td style='text-align:center'>posted</td>";					
				$totnilaiposted = 0;
				foreach($arrhari as $val) {
					 @$nilaiposted = $arrItem[$key2][substr($val, 8, 2)]['1']+$arrItem[$key2][substr($val, 8, 2)]['3'];
					$tab.="<td style='text-align:center;cursor:pointer' onclick=popup('".$key2."','".$val."','keu','1')>".hidezerodecimal($nilaiposted)."</td>";
					$totnilaiposted += $nilaiposted;
					
				}
				$tab.="<td style='text-align:center;'>".hidezerodecimal($totnilaiposted)."</td>
					</tr>
					<tr class=rowcontent>
					<td style='text-align:center;color:red'>not posted</td>";
				$totnilaiunposted = 0;
				foreach($arrhari as $val) {
					@$nilaiunposted = $arrItem[$key2][substr($val, 8, 2)]['0']+$arrItem[$key2][substr($val, 8, 2)]['9'];
					$tab.="<td style='text-align:center;color:red;cursor:pointer' onclick=popup('".$key2."','".$val."','keu','0')>".hidezerodecimal($nilaiunposted)."</td>";
					$totnilaiunposted += $nilaiunposted;
				}
				$tab.="<td style='text-align:center;color:red'>".hidezerodecimal($totnilaiunposted)."</td>
					</tr>";
			}
	}
	$tab.="</tbody>
		</table>";
	echo $tab;
	break;
}
?>