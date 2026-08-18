<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


// error_reporting(0);
// ini_set('display_errors', "Off");
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$proses    =checkPostGet('proses','');
$unit      =checkPostGet('unit','');
$divisi    =checkPostGet('divisi','');
$periode   =checkPostGet('periode','');
$tgl1      =tanggalsystemn(checkPostGet('tgl1',''));
$tgl2      =tanggalsystemn(checkPostGet('tgl2',''));
$tipekar   =checkPostGet('tipekar','');
$karyawanid=checkPostGet('karyawan','');
$sistemgaji=checkPostGet('sistemgaji','');
$event     =checkPostGet('event','');


if ($unit=='#'){
	exit("Error : Unit is required!");
} 
if ($divisi=='#'){
	exit("Error : Divisi is required!");
} 
if($periode=='#'){
	exit("Error : Periode is required");
} 


$arrprd=explode("-",$periode);
$tahun=$arrprd[0];
$bulan=$arrprd[1];

$str= "select * from ".$dbname.".sdm_5absensi";
$res= fetchdata($str);
foreach($res as $val){
	$nmabsen[$val['kodeabsen']]=$val['keterangan'];
}
$nmabsen['FP']="Fingerprint";

switch($proses){
	case'popupsumber':
		$arrsumber=array(
			'sdm' =>'SDM Absensi',
			'bkm' =>'BKM Rawat',
			'pnn' =>'BKM Panen',
			'vhc' =>'Traksi',
			'mdr' =>'Mandor, Kerani',
			'fp' =>'Fingerprint',
			'ba' =>'BA Absensi'
		);
	
		$tab="
			<table border=0 cellpadding=5 width=100% cellspacing=1 class=sortable>
				<thead>
					<tr class=rowheader style=text-align:center;height:25px>
						<th>No</th>
						<th>" . $_SESSION['lang']['sumber'] . "</th>";
					$tab.="<th  style='width:30px;align:center'>Action<br>
						<input id=checkall type=checkbox onclick=clickall()>
						</th>
					</tr>
				</thead>
				<tbody>";
				
				$kep = explode(",",$param['sumber']);
				foreach($kep as $kpd){
					$kpda[$kpd]=$kpd;
				}
				foreach($arrsumber as $key => $val){
					$no++;
					$tab.="<tr class=rowcontent style=height:25px>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td hidden name=nama[]>".$key."</td>";
					$tab.="<td>".$val."</td>";
					if($kpda[trim($key)]!=''){
						$tab.="<td align=center><input name=check[] type=checkbox checked></td>";
					}else{							
						$tab.="<td align=center><input name=check[] type=checkbox></td>";
					}	
					$tab.="</tr>";
				}
				
			$tab.="<tr class=rowcontent style=height:25px>";
				$tab.="<td align=center colspan=3><button style=width:50px class=mybutton onclick=adddata()>Add</button></td>";
			$tab.="</tr>";
			$tab.="</tbody>
			</table>
			";
		echo $tab;
	break;
	case'html':
		
		$str= "select * from ".$dbname.".sdm_5mastershift where status='1' order by id";
		$res= fetchdata($str);
		foreach($res as $val){
			$arrnamashift[$val['shift']]=$val['namashift'];
		}
		$str= "select * from ".$dbname.".sdm_5tipekaryawan";
		$res= fetchdata($str);
		foreach($res as $val){
			$arrnamatipe[$val['id']]=$val['tipe'];
		}


		$tab="<label>Fingerprint</label>";
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='2'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='2'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='2'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='2'>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan='2'>".$_SESSION['lang']['tipekaryawan']."</th>
				<th rowspan='2'>".$_SESSION['lang']['shift']."</th>
				<th rowspan='2'>".$_SESSION['lang']['sumber']."</th>
				<th rowspan='2'>".$_SESSION['lang']['tanggal']."</th>
				<th colspan='5'>Jam</th>
				<th rowspan='2'>".$_SESSION['lang']['penjelasan']."</th>
				<th rowspan='2'>SDM Absensi</th>
			</tr>
			";
			
			$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			$tab.="<th>In</th>";
			$tab.="<th>Out</th>";
			$tab.="<th>In</th>";
			$tab.="<th>Out</th>";
			$tab.="<th>Abs</th>";
			$tab.="</tr>";
			$tab.="</thead>
			<tbody>";
			
			$nmsumber=array('manual'=>'BA Absensi','upload'=>'Fingerprint');
			
			if($param['karyawanid']!=''){				
				$where=" and karyawanid='".$param['karyawanid']."'";
			}else{
				$where=" and kodeorg like '".$param['unit']."%'";
			}
			$where.=" and tanggal like '".$param['tanggal']."%'";
			
			$str = "select * from ".$dbname.".sdm_absensidt where 1=1 ".$where."";
			$res = fetchdata($str);
			foreach($res as $bar){
				$sdmabsensi[$bar['idfp']]="&#10003;";
			}			
			if($param['karyawanid']!=''){				
				$where=" and karyawanid='".$param['karyawanid']."'";
			}else{
				$where=" and kodeorg='".$param['unit']."'";
				if(strlen($param['divisi'])==4){
					$where.=" and subbagian=''";
				}elseif($param['divisi']==''){
					$where.=" and subbagian like '%'";
				}else{					
					$where.=" and subbagian='".$param['divisi']."'";
				}
				$where.=" and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='".$param['tipekar']."')";				
			}
			$where.=" and tanggalabsen like '".$param['tanggal']."%'";
			$str = "select * from ".$dbname.".upload_absensi where 1=1 ".$where." order by tanggalabsen";
			$res = fetchdata($str);
			foreach($res as $bar){
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".getKary($bar['karyawanid'],'nik')."</td>";
				$tab.="<td align=left>".getKary($bar['karyawanid'])."</td>";
				$tab.="<td>".getNamaJabatan(getKary($bar['karyawanid'],'kodejabatan'))."</td>";
				$tab.="<td align=left>".$arrnamatipe[getKary($bar['karyawanid'],'tipekaryawan')]."</td>";
				$tab.="<td align=left>".$bar['shift']." - ".$arrnamashift[$bar['namashift']]."</td>";
				$tab.="<td align=center>".$nmsumber[$bar['sumber']]."</td>";
				$tab.="<td align=center>".$bar['tanggalabsen']."</td>";
				$tab.="<td align=center width=75px>".waktunormal($bar['jam'])."</td>";
				if($bar['jam2']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam2'])."</td>";
				}
				if($bar['jam3']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam3'])."</td>";
				}
				if($bar['jam4']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam4'])."</td>";
				}
				
				$tab.="<td align=center>".$bar['absensi']."</td>";
				if($bar['penjelasan']!=''){					
					$tab.="<td align=left>".$bar['penjelasan']."<br><font style=font-size:9px;font-style:italic>".getKary($bar['updatedby'])." ".waktunormal($bar['updatedtime'])."</font></td>";
				}else{
					$tab.="<td align=center></td>";					
				}
				if($sdmabsensi[$bar['id']]!=''){					
					$tab.="<td align=center style=background-color:green;>".$sdmabsensi[$bar['id']]."</td>";
				}else{
					$tab.="<td align=center>x</td>";
				}
			}
			
			$tab.="</table>";
			$tab.="<br><label>Transaksi</label>";
			$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
				<thead>
				<tr class=rowheader style='text-align:center;font-weight:bold'>
					<th>".$_SESSION['lang']['nourut']."</th>
					<th>".$_SESSION['lang']['tanggal']."</th>
					<th>".$_SESSION['lang']['sumber']."</th>
					<th>".$_SESSION['lang']['notransaksi']."</th>
				</tr>
				</thead>
			";
			
			
			$data = json_decode($param['json']);
			
			//print_r($data);
			
			foreach($data as $kary => $v1){
				foreach($v1 as $tgl => $v2){
					foreach($v2 as $notr => $sumber){
						if($kary==$param['karyawanid'] and $tgl==$param['tanggal']){							
							$n++;
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=center>".$n."</td>";
							$tab.="<td align=center>".$tgl."</td>";
							$tab.="<td align=left>".$sumber."</td>";
							$tab.="<td align=center>".strtoupper($notr)."</td>";
							$tab.="</tr>";
						}
					}
				}
			}
			if($n==""){
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center colspan=4>Not Found</td>";
				$tab.="</tr>";
			}
				
			$tab.="</table>";

		// Upload Filenya
				
		$tab.="<br/>
		<p><b>Sumber Transaksi Absensi</b></p>
		<input type='hidden' id='nikdetail' value='".$nik."' />
		<input type='hidden' id='tgldetail' value='".$tanggal."' />
		<input type='hidden' id='karyawaniddetail' value='".$karyawanid."' />
		<div style='display: flex; justify-content: space-between;' id='containerdetail'>
			<table cellpadding=5 cellspacing=1 border=0 class=sortable>
				<thead>
				<tr class=rowheader style='text-align:center;font-weight:bold'>
					<th>".$_SESSION['lang']['nourut']."</th>
					<th>Nama Karyawan</th>
					<th>Filename</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody>";

		// Query Tampilkan Karyawan
		$sqlKaryawan = "SELECT * from ".$dbname.".sdm_absensidt WHERE kodeorg='".$param['unit']."' and tanggal='".$param['tanggal']."' and karyawanid='".$param['karyawanid']."'";
		//  exit('warning');
		 $res = $owlPDO->query($sqlKaryawan) or die(print " Gagal: ".PDOException::getMessage());
		 $res->setFetchMode(PDO::FETCH_OBJ);
		 $cekSumberData = fetchData($sqlKaryawan);

		if(count($cekSumberData) <= 0) {
			$stream.="<tr class=rowcontent><td colspan=3>Sumber Absensi Bukan Dari Transaksi Absensi</td></tr>";
		}

		 // exit("warning".print_r($res));
		 $noKar=0;
		 while($resKaryawanDt = $res->fetch()) {
		   $nmkaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$resKaryawanDt->karyawanid."'");
		   $nmakun = makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$resKaryawanDt->noakun."'");
		   $pecahtanggal = explode("-",$resKaryawanDt->tanggal);
		   $tanggalnya = $pecahtanggal[0].$pecahtanggal[1].$pecahtanggal[2];
		   $notransaksix = $tanggalnya.$resKaryawanDt->karyawanid;
		   // echo "<pre>";
		   // print_r($filenya[$notransaksi]);
		   // echo "</pre>";
	   
		 $sFile = selectQuery($dbname, "listfileupload", "*", "notransaksi='".$notransaksix."' and kriteriaefil='ABSEN' and status='1'");
		 $resFile = fetchData($sFile);
		//    exit('warning');
			if(count($resFile) > 0) {
				$no=0;
				foreach($resFile as $key => $val) {
					$pathDownload = "fileupload/dtkaryawanabsen/";
					$no+=1;
					$tab .= "<tr class=rowcontent>";
					  $tab .= "<td align=right>".$no."</td>";
					  $tab .= "<td align=left>".$nmkaryawan[$resKaryawanDt->karyawanid]."</td>";
					  // $tab .= "<td align=center>".$resKaryawanDt->absensi."</td>";
					  // $tab .= "<td align=left>".$nmakun[$resKaryawanDt->noakun]."</td>";
					  // $tab .= "<td align=left>".$resKaryawanDt->alokasi."</td>";
					  // $tab .= "<td align=right>".$resKaryawanDt->hk."</td>";
					  // $tab .= "<td align=right>".$resKaryawanDt->premi."</td>";
					  // $tab .= "<td align=right>".$resKaryawanDt->tunjangan."</td>";
					  // $tab .= "<td align=right>".$resKaryawanDt->penaltykehadiran."</td>";
					  // $tab .= "<td align=left>".$resKaryawanDt->penjelasan."</td>";
					  
					  $icon=seticonfile($val['formaticon']);
					  $tab.="<td style='text-align:center;display:flex;align-item:center;'>
						  <a href='".$pathDownload.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a> <span>".$val['namafile']."</span>
						</td>";
					  // $tab .= "<td align=left>".$val['namafile']."</td>";
					  $tab .= "<td align=center>
									<a href='".$pathDownload.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn width=15px  title='Download'></a>
									<!--<a href='".$pathDownload.$val['namafile']."' preview><img src=images/zoom.png class=zImgBtn width=15px  title='Download'></a>-->
								  </td>";
					
					$tab .= "</tr>";
				}
			} else {
				$tab.="<td style=color:red;><b>Belum Ada Data</b></td>";
			}
		}
				$tab.="</tbody>
			</table>
		</div>";
			
		echo $tab;	
	break;
	case 'preview':
		$arrsource    = explode(",",$param['sumber']);
		foreach($arrsource as $val => $isi){
			$source[$isi]=$isi;
		}

		$orderby = "order by subbagian asc,namakaryawan asc";
		
		$wh="";
		if(strlen($param['divisi'])==4){
			$wh=" and subbagian=''";			
		}
		if(strlen($param['divisi'])==6){
			$wh=" and subbagian='".$param['divisi']."'";			
		}
		
		$rangetgl = rangeTanggal($param['periode']."-01",tglakhir($param['periode']."-01"));
		
		$wh.=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tglakhir($param['periode']."-01")."')";			
		
		
		$str_bulanan = "select * from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas like '%".$unit."%' ".$wh." and tipekaryawan like '%".$tipekar."%' and periodegaji='".$param['periode']."' ".$orderby.""; 
		
		$str_datakaryawan = "select karyawanid,nik,namakaryawan,subbagian,tipekaryawan from ".$dbname.".datakaryawan where lokasitugas like '%".$unit."%' ".$wh." and tipekaryawan like '%".$tipekar."%'  ".$orderby."";
		$count = count(fetchData($str_bulanan));
		
		$str = ($count>0) ? $str_bulanan : $str_datakaryawan;
		$datakaryawan = fetchData($str);
		foreach($datakaryawan as $bar){
			$dtkary[$bar['karyawanid']]=$bar['karyawanid'];  
			$nikkary[$bar['karyawanid']]=$bar['nik'];  
			$nmkary[$bar['karyawanid']]=$bar['namakaryawan'];  
			$subbag[$bar['karyawanid']]=$bar['subbagian'];  
			$tipekary[$bar['karyawanid']]=$bar['tipekaryawan'];  
			$tglkeluar[$bar['karyawanid']]=$bar['tanggalkeluar'];  
		}
		$finger=$title=array();
		if($source['fp']!="" or $param['sumber']==''){	
			$str = "select * from " . $dbname . ".upload_absensi where 1=1 and kodeorg='".$unit."' and tanggalabsen like '".$param['periode']."%' and sumber='upload'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$absen[$val['karyawanid']][$val['tanggalabsen']]=$val['absensi'];
				$finger[$val['karyawanid']][$val['tanggalabsen']]=$val['absensi'];
				$title[$val['karyawanid']][$val['tanggalabsen']]='Fingerprint';
			}
		}
		
		if($source['ba']!="" or $param['sumber']==''){	
			$str = "select * from " . $dbname . ".upload_absensi where 1=1 and kodeorg='".$unit."' and tanggalabsen like '".$param['periode']."%' and sumber='manual'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$absen[$val['karyawanid']][$val['tanggalabsen']]=$val['absensi'];
				$finger[$val['karyawanid']][$val['tanggalabsen']]=$val['absensi'];
				$title[$val['karyawanid']][$val['tanggalabsen']]='BA Absensi';
			}
		}
		
		$where="";
		$where.=" and kodeorg like '".$param['unit']."%'";
		$where.=" and tanggal like '".$param['periode']."%'";
		
		if($source['sdm']!="" or $param['sumber']==''){	
			$str = "select * from " . $dbname . ".sdm_absensidt_vw where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val){
				$absen[$val['karyawanid']][$val['tanggal']]=$val['absensi'];
				$title[$val['karyawanid']][$val['tanggal']]='SDM_Absensi';
				$popup[$val['karyawanid']][$val['tanggal']]['Absensi']='SDM_Absensi';
			}
		}
		
		if($source['bkm']!="" or $param['sumber']==''){	
			$str = "select a.*,b.tanggal,b.kodeorg as kodeorg from " . $dbname . ".kebun_kehadiran a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				$absen[$val['nik']][$val['tanggal']]=$val['absensi'];
				$title[$val['nik']][$val['tanggal']]='BKM_Rawat';
				$popup[$val['nik']][$val['tanggal']][$val['notransaksi']]='BKM_Rawat';
			}
		}
		
		
		if($source['pnn']!="" or $param['sumber']==''){	
			$str = "select a.*,b.tanggal,b.kodeorg as kodeorg from " . $dbname . ".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 and tipetransaksi='PNN' and b.kodeorg='".$param['unit']."' and b.tanggal like '".$param['periode']."%'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$absen[$val['nik']][$val['tanggal']]='H';
				$title[$val['nik']][$val['tanggal']]='BKM_Panen';
				$popup[$val['nik']][$val['tanggal']][$val['notransaksi']]='BKM_Panen';
			}
		}
		if($source['vhc']!="" or $param['sumber']==''){	
			$str = "select * from " . $dbname . ".vhc_runhk_vw where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				$absen[$val['idkaryawan']][$val['tanggal']]='H';
				$title[$val['idkaryawan']][$val['tanggal']]='TRK_Pekerjaan';
				$popup[$val['idkaryawan']][$val['tanggal']][$val['notransaksi']]='TRK_Pekerjaan';
			}
		}
		if($source['mdr']!="" or $param['sumber']==''){	
			$str = "select * from " . $dbname . ".kebun_aktifitas where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				if($val['nikmandor']!=''){
					$absen[$val['nikmandor']][$val['tanggal']]="H";
					$title[$val['nikmandor']][$val['tanggal']]='Header_BKM';
					$popup[$val['nikmandor']][$val['tanggal']][$val['notransaksi']]='Header_BKM';
				}
				if($val['nikmandor1']!=''){
					$absen[$val['nikmandor1']][$val['tanggal']]="H";
					$title[$val['nikmandor1']][$val['tanggal']]='Header_BKM';
					$popup[$val['nikmandor1']][$val['tanggal']][$val['notransaksi']]='Header_BKM';
				}
				if($val['keranimuat']!=''){		
					$absen[$val['keranimuat']][$val['tanggal']]="H";
					$title[$val['keranimuat']][$val['tanggal']]='Header_BKM';
					$popup[$val['keranimuat']][$val['tanggal']][$val['notransaksi']]='Header_BKM';
				}
				if($val['nikasisten']!=''){		
					$absen[$val['nikasisten']][$val['tanggal']]="H";
					$title[$val['nikasisten']][$val['tanggal']]='Header_BKM';
					$popup[$val['nikasisten']][$val['tanggal']][$val['notransaksi']]='Header_BKM';
				}
			}
		}
		
		$jnsabs=array();
		$jnsabs['FP']="FP";
		foreach ($dtkary as $kary){
			foreach($rangetgl as $tgl){
				if($absen[$kary][$tgl]!=""){
					$jnsabs[$absen[$kary][$tgl]]=$absen[$kary][$tgl];
				}
			}
		}
		
		if ($event=='excel'){
			$tab.= "<table class='sortable' cellspacing='1' border='1'>";
		}else{			
			$tab.= "<input hidden id=json value=".json_encode($popup)."><table class='sortable' cellspacing='1' border='0' style=white-space:nowrap; cellpadding=5>";
		}
		$tab.="<thead><tr class=rowcontent>";
		$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['nourut']."</th>";
		$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['divisi']."</th>";
		$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['nik']."</th>";
		$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>";
		$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['jabatan']."</th>";
		$tab.="<th align=center colspan=".count($rangetgl).">".$_SESSION['lang']['tanggal']."</th>";
		$tab.="<th align=center colspan=".count($jnsabs).">".$_SESSION['lang']['absensi']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
		$tab.="</tr><tr>";
		foreach($rangetgl as $tgl){
			if(hari($tgl)=='Minggu'){
				$tab .= "<th style=color:red>".substr($tgl,-2)."<br>".date("D",strtotime($tgl))."</th>";
			}else{
				$tab .= "<th>".substr($tgl,-2)."<br>".date("D",strtotime($tgl))."</th>";
			}
		}
		//sort($jnsabs);
		foreach($jnsabs as $absensi){
			$tab .= "<th align=center>".$absensi."</th>";
		}
		$tab.="</tr></thead>";
		$tab.="<tbody>";
		$gtkary=$gtall=array();
		foreach ($dtkary as $kary){
			$nourut++;
			$tab.="<tr class=rowcontent>";
			$tab .="<td align=center>".$nourut."</td>";
			$tab .="<td>".getNamaOrg($subbag[$kary])."</td>";
			$tab .="<td>".$nikkary[$kary]."</td>";
			$tab .="<td>".$nmkary[$kary]."</td>";
			$tab .="<td>".getNamaJabatan(getKary($kary,'kodejabatan'))."</td>";
			foreach($rangetgl as $tgl){
				$a=$b="";
				if($finger[$kary][$tgl]!='' and $absen[$kary][$tgl]!='H'){
					$a=" title=\"Karyawan melakukan Fingerprint / BA Absensi\" style=cursor:pointer;color:red;font-weight:bold; onclick=lihatDetail('".$kary."','".$tgl."')";
				}elseif($finger[$kary][$tgl]!=''){						
					$a=" title=\"".$title[$kary][$tgl]."\" style=cursor:pointer;color:blue;font-weight:bold; onclick=lihatDetail('".$kary."','".$tgl."')";
				}else{
					$a=" title=\"".$title[$kary][$tgl]."\" onclick=lihatDetail('".$kary."','".$tgl."')";
				}
				if($tipekary[$kary]==1 and $absen[$kary][$tgl]==''){
					$b="style=background-color:#ffaa96;font-weight:bold;cursor:pointer;";
				}
				
				$tab .="<td align=center ".$a." ".$b.">".$absen[$kary][$tgl]."</td>";
				if($absen[$kary][$tgl]!=''){
					$gtkary[$kary]++;
					$gtall[$tgl]++;
					$gt++;
				}
				
				
				foreach($jnsabs as $absensi){
					if($absen[$kary][$tgl]==$absensi){
						$ttlabsn[$kary][$absensi]++;
						$ttlabsnbwhtgl[$absensi][$tgl]++;
						$ttlabsnbawah[$absensi]++;
					}
				}
				if($finger[$kary][$tgl]!=""){
					$ttlabsn[$kary]['FP']++;
					$ttlabsnbawah['FP']++;
					$ttlabsnbwhtgl['FP'][$tgl]++;
				}
			}
			
			foreach($jnsabs as $absensi){
				if($absensi=='FP'){
					$tab .= "<td align=center style=background-color:#80e8de;font-weight:bold;color:blue>".$ttlabsn[$kary][$absensi]."</td>";
				}else{					
					$tab .= "<td align=center>".$ttlabsn[$kary][$absensi]."</td>";
				}
			}
			$a="align=center style=cursor:pointer;background-color:cyan onclick=lihatDetail('".$kary."','".$param['periode']."')";
			$tab .="<td align=right ".$a.">".numb_format($gtkary[$kary])."</td>";
			$tab .="</tr>";
		} #end foreach $karyawan`
		
		
		foreach($jnsabs as $absensi){
			if($absensi=='FP'){
				$tab.="<tr class=rowcontent style=background-color:#80e8de;font-weight:bold;color:blue>";
			}else{					
				$tab.="<tr class=rowcontent style=background-color:#d1d1d1>";
			}
			$tab .= "<td></td>";				
			$tab .= "<td></td>";				
			$tab .= "<td colspan=3>".$absensi." - ".$nmabsen[$absensi]."</td>";
			foreach($rangetgl as $tgl){
				$tab .= "<td align=center>".$ttlabsnbwhtgl[$absensi][$tgl]."</td>";
			}
			
			foreach($jnsabs as $abs){
				if($absensi==$abs){						
					$tab .= "<td align=center>".$ttlabsnbawah[$abs]."</td>";				
				}else{
					$tab .= "<td></td>";						
				}
			}
			$tab .= "<td align=right>".$ttlabsnbawah[$absensi]."</td>";				
			$tab .="</tr>";
		}	
		$tab.="<tr class=rowcontent style=background-color:cyan>";
		$tab.="<td style=background-color:cyan colspan=5 align=center>Total</td>";
			foreach($rangetgl as $tgl){
				if($param['tipekar']==1 and $gtall[$tgl]!=$nourut){
					$a="align=center style=cursor:pointer;background-color:#ffaa96;font-weight:bold; onclick=lihatDetail('','".$tgl."')";
				}else{						
					$a="align=center style=cursor:pointer;background-color:cyan onclick=lihatDetail('','".$tgl."')";
				}
				$tab.="<td ".$a.">".$gtall[$tgl]."</td>";
			}
			foreach($jnsabs as $absensi){
				$tab .= "<td align=center>".$ttlabsnbawah[$absensi]."</td>";
			}
		$tab .="<td align=right style=background-color:cyan>".numb_format($gt)."</td>";			
		$tab .="</tr>";
		$tab.="</tbody></table>";
		
		
		
		if ($event=='html'){
			echo $tab;
		}
		if ($event=='excel'){
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
			$qwe=date("YmdHms");
			$titlelaporan="Laporan_fingerprin_baabsensi ".$qwe;
			if($handle = opendir('tempExcel')){
				while(false !== ($file = readdir($handle))){
					if($file != "." && $file != ".." && $file != "index.html"){
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$titlelaporan.".xls",'w');
			if(!fwrite($handle, $tab)){
				echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
					window.location='tempExcel/".$titlelaporan.".xls';
					</script>";
			}
			closedir($handle); 
		}
	
	break;
	case 'getdivisitipe':
		$optdivisi="<option value='#'>".$_SESSION['lang']['pilihdata']."</option>";
		if(strlen($_SESSION['empl']['subbagian'])==''){
			$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_POST['unit']."%' 
			and tipe in ('AFDELING','BIBITAN','KEBUN','STATION','SIPIL','PABRIK','TRAKSI','WORKSHOP','MAINTENANCE','GUDANG') and length(kodeorganisasi)=6
			order by kodeorganisasi asc";
			$optdivisi.="<option value=''>".$_SESSION['lang']['all']."</option>";
			$optdivisi.="<option value='".$unit."'>".$_SESSION['lang']['kantor']." / ".$_SESSION['lang']['umum']."</option>";
			$res=fetchData($str);
			foreach($res as $bar){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}else{
			$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['subbagian']."' 
			and tipe in ('AFDELING','BIBITAN','KEBUN','STATION','SIPIL','PABRIK','TRAKSI','WORKSHOP','MAINTENANCE') and length(kodeorganisasi)=6
			order by kodeorganisasi asc";				
			$res=fetchData($str);
			foreach($res as $bar){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}
			echo $optdivisi;
		break;
		case 'filPeriode':
			$optPeriode=$tipeKar="<option value='#'>".$_SESSION['lang']['pilihdata']."</option>";
			$unit = $_POST['unit'];
			$sql = "select distinct(periode) from ".$dbname.".sdm_5periodegaji where  kodeorg='".$unit."' order by periode desc limit 13";
			$query = fetchData($sql);
			foreach ($query as $key=>$val){
				$optPeriode .= "<option>".$val['periode']."</option>";
			}

			$sql="select * from ".$dbname.".sdm_5tipekaryawan where aktif='1'";
            $query = fetchData($sql);
            foreach ($query as $key=>$val){
                $tipeKar .= "<option value='".$val['id']."'>".$val['tipe']."</option>";
            }
			
            echo $optPeriode."##".$tipeKar;
		break;
		
}
function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}        
?>