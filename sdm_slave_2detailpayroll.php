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

$arrsource    = explode(",",$param['sumber']);
foreach($arrsource as $val => $isi){
	$source[$isi]=$isi;
}


if ($unit=='#'){
	exit("Error : Unit is required!");
} 
if ($divisi=='#'){
	exit("Error : Divisi is required!");
} 
if($periode=='#'){
	exit("Error : Periode is required");
} 
if($sistemgaji=='#'){
	exit("Error : Sistem gaji is required!");
}



$arrprd=explode("-",$periode);
$tahun=$arrprd[0];
$bulan=$arrprd[1];

switch($proses){
	case'popupsumber':
		
		#= sdm_absensidt_vw
		#= kebun_kehadiran_vw
		#= kebun_prestasi_vs_hk
		#= vhc_runhk_vw
		#= kebun_premikemandoran
		#= vhc_spl_kehadiran_vw
		#= sdm_uangmakandanextrafooding(Extra Fooding, UM)
		#= sdm_pendapatanlaindt
		
		$arrsumber=array(
			'sdm' =>'SDM Absensi',
			'lbr' =>'SDM Lembur',
			'bkm' =>'BKM Rawat',
			'pnn' =>'BKM Panen',
			'vhc' =>'Traksi',
			'mdr' =>'Mandor, Kerani',
			'spl' =>'Sipil',
			'um'  =>'Uang Makan, Extra Fooding',
			'lain'=>'Pendapatan Lain'
		);
	
		$tab="
			<table border=0 cellpadding=1 cellspacing=1 class=sortable width=400px>
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
	case 'preview':
		if(substr($tgl1,0,7)!=$periode){
			exit("Error : Tanggal pertama tidak sesuai periode.");
		}

		if(substr($tgl2,0,7)!=$periode){
			exit("Error : Tanggal kedua tidak sesuai periode.");
		}

		if($tgl1>$tgl2){
			exit("Error : Tanggal pertama lebih besar dari tanggal kedua.");
		}
		

		
		// $sql = "select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where periode='".$periode."' and kodeorg='".$unit."' ";
		// $periode_gaji = fetchData($sql);
		// $tgl1 = $periode_gaji[0]['tanggalmulai'];
		// $tgl2 = $periode_gaji[0]['tanggalsampai'];
		
		$rangetgl = rangeTanggal($tgl1,$tgl2);
		
		
		$orderby = "order by subbagian asc,namakaryawan asc";
		
		$wh="";
		if(strlen($param['divisi'])==4){
			$wh=" and subbagian=''";			
		}
		if(strlen($param['divisi'])==6){
			$wh=" and subbagian='".$param['divisi']."'";			
		}
		$wh.=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tgl1."')";			
		
		
		$str_bulanan = "select * from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas like '%".$unit."%' ".$wh." and tipekaryawan like '%".$tipekar."%' and karyawanid like '%".$karyawanid."%' and periodegaji='".$param['periode']."' ".$orderby.""; 
		
		$str_datakaryawan = "select karyawanid,nik,namakaryawan,subbagian,tipekaryawan from ".$dbname.".datakaryawan where lokasitugas like '%".$unit."%' ".$wh." and tipekaryawan like '%".$tipekar."%' and karyawanid like '%".$karyawanid."%' ".$orderby."";
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
		
		$str = "select * from " . $dbname . ".sdm_5gajipokok where 1=1 and tahun='".$tahun."' and idkomponen='1'";
		$res = fetchData($str);
		foreach ($res as $val) {
			$gaji[$val['karyawanid']]=$val['jumlah']/25;
		}
		
		
		#= sdm_absensidt_vw
		#= kebun_kehadiran_vw
		#= kebun_prestasi_vs_hk
		#= vhc_runhk_vw
		#= kebun_premikemandoran
		#= vhc_spl_kehadiran_vw
		#= sdm_uangmakandanextrafooding(Extra Fooding, UM)
		#= sdm_pendapatanlaindt
		
		$where="";
		$where.=" and kodeorg like '".$param['unit']."%'";
		$where.=" and tanggal like '".$param['periode']."%'";
		
		if($source['sdm']!="" or $param['sumber']==''){			
			$str = "select * from " . $dbname . ".sdm_absensidt_vw where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val){
				$listkary[$val['karyawanid']]=$val['karyawanid'];
				$rupiah[$val['karyawanid']][$val['tanggal']]['absen']=$val['absensi'];
				$rupiah[$val['karyawanid']][$val['tanggal']]['upah']+=$val['umr']-$val['penaltykehadiran'];
				$rupiah[$val['karyawanid']][$val['tanggal']]['premi']+=$val['premi']+$val['insentif']+$val['insentiflibur'];
				
				$rupiahslip[$val['karyawanid']]['absen']=$val['absensi'];
				$rupiahslip[$val['karyawanid']]['upah']+=$val['umr']-$val['penaltykehadiran'];
				$rupiahslip[$val['karyawanid']]['premi']+=$val['premi']+$val['insentif']+$val['insentiflibur'];
			}
		}
		
		if($source['lbr']!="" or $param['sumber']==''){			
			$str = "select * from " . $dbname . ".sdm_lemburdt where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				$listkary[$val['karyawanid']]=$val['karyawanid'];
				$rupiah[$val['karyawanid']][$val['tanggal']]['lembur']+=$val['uangkelebihanjam'];
				$rupiahslip[$val['karyawanid']]['lembur']+=$val['uangkelebihanjam'];
			}
		}
		if($source['bkm']!="" or $param['sumber']==''){			
			$str = "select a.*,b.tanggal,b.kodeorg as kodeorg from " . $dbname . ".kebun_kehadiran a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				$listkary[$val['nik']]=$val['nik'];
				$rupiah[$val['nik']][$val['tanggal']]['absen']=$val['absensi'];
				$rupiah[$val['nik']][$val['tanggal']]['upah']+=$val['umr'];
				$rupiah[$val['nik']][$val['tanggal']]['premi']+=$val['insentif']-$val['penalty'];
				
				$rupiahslip[$val['nik']]['absen']=$val['absensi'];
				$rupiahslip[$val['nik']]['upah']+=$val['umr'];
				$rupiahslip[$val['nik']]['premi']+=$val['insentif']-$val['penalty'];
			}
		}
		
		if($source['pnn']!="" or $param['sumber']==''){			
			$str = "select a.*,b.tanggal,b.kodeorg as kodeorg from " . $dbname . ".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 and tipetransaksi='PNN' and b.kodeorg='".$param['unit']."' and b.tanggal like '".$param['periode']."%'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$listkary[$val['nik']]=$val['nik'];
				$rupiah[$val['nik']][$val['tanggal']]['absen']='H';
				$rupiah[$val['nik']][$val['tanggal']]['upah']+=$val['upahkerja']-$val['upahpenalty'];
				$rupiah[$val['nik']][$val['tanggal']]['premi']+=($val['upahpremi']+$val['upahpremilebihbasis']+$val['premibasis'])-$val['rupiahpenalty'];
				
				$rupiahslip[$val['nik']]['absen']='H';
				$rupiahslip[$val['nik']]['upah']+=$val['upahkerja']-$val['upahpenalty'];
				$rupiahslip[$val['nik']]['premi']+=($val['upahpremi']+$val['upahpremilebihbasis']+$val['premibasis'])-$val['rupiahpenalty'];
			}
		}
		if($source['vhc']!="" or $param['sumber']==''){			
			$str = "select * from " . $dbname . ".vhc_runhk_vw where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				$listkary[$val['idkaryawan']]=$val['idkaryawan'];
				$rupiah[$val['idkaryawan']][$val['tanggal']]['absen']="H";
				$rupiah[$val['idkaryawan']][$val['tanggal']]['upah']+=$val['upah']-$val['penalty'];
				$rupiah[$val['idkaryawan']][$val['tanggal']]['premi']+=$val['premi'];
				
				$rupiahslip[$val['idkaryawan']]['absen']="H";
				$rupiahslip[$val['idkaryawan']]['upah']+=$val['upah']-$val['penalty'];
				$rupiahslip[$val['idkaryawan']]['premi']+=$val['premi'];
			}
		}
		if($source['mdr']!="" or $param['sumber']==''){			
			$str = "select * from " . $dbname . ".kebun_premikemandoran where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				$listkary[$val['karyawanid']]=$val['karyawanid'];
				$rupiah[$val['karyawanid']][$val['tanggal']]['premi']+=$val['premiinput'];
				$rupiahslip[$val['karyawanid']]['premi']+=$val['premiinput'];
			}

			$str = "select * from " . $dbname . ".kebun_aktifitas where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				if($val['nikmandor']!=''){
					$listkary[$val['nikmandor']]=$val['nikmandor'];
					$rupiah[$val['nikmandor']][$val['tanggal']]['absen']="H";
					$rupiahslip[$val['nikmandor']]['absen']="H";
				}
				if($tipekary[$val['nikmandor']]=='4' and $val['nikmandor']!=''){
					$rupiah[$val['nikmandor']][$val['tanggal']]['upah']=$gaji[$val['nikmandor']];
					$rupiahslip[$val['nikmandor']]['upah']=$gaji[$val['nikmandor']];
				}
				if($val['nikmandor1']!=''){
					$listkary[$val['nikmandor1']]=$val['nikmandor1'];
					$rupiah[$val['nikmandor1']][$val['tanggal']]['absen']="H";
					$rupiahslip[$val['nikmandor1']]['absen']="H";
				}
				if($tipekary[$val['nikmandor1']]=='4' and $val['nikmandor1']!=''){
					$rupiah[$val['nikmandor1']][$val['tanggal']]['upah']=$gaji[$val['nikmandor1']];
					$rupiahslip[$val['nikmandor1']]['upah']=$gaji[$val['nikmandor1']];
				}
				if($val['keranimuat']!=''){		
					$listkary[$val['keranimuat']]=$val['keranimuat'];
					$rupiah[$val['keranimuat']][$val['tanggal']]['absen']="H";
					$rupiahslip[$val['keranimuat']]['absen']="H";
				}
				if($tipekary[$val['keranimuat']]=='4' and $val['keranimuat']!=''){
					$rupiah[$val['keranimuat']][$val['tanggal']]['upah']=$gaji[$val['keranimuat']];
					$rupiahslip[$val['keranimuat']]['upah']=$gaji[$val['keranimuat']];
				}
				if($val['nikasisten']!=''){		
					$listkary[$val['nikasisten']]=$val['nikasisten'];
					$rupiah[$val['nikasisten']][$val['tanggal']]['absen']="H";
					$rupiahslip[$val['nikasisten']]['absen']="H";
				}
				if($tipekary[$val['nikasisten']]=='4' and $val['nikasisten']!=''){
					$rupiah[$val['nikasisten']][$val['tanggal']]['upah']=$gaji[$val['nikasisten']];
					$rupiahslip[$val['nikasisten']]['upah']=$gaji[$val['nikasisten']];
				}
			}
		}
		
		
		
		if($source['spl']!="" or $param['sumber']==''){			
			$str = "select * from " . $dbname . ".vhc_spl_kehadiran_vw where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				$listkary[$val['nik']]=$val['nik'];
				$rupiah[$val['nik']][$val['tanggal']]['absen']="H";
				$rupiah[$val['nik']][$val['tanggal']]['upah']+=$val['umr'];
				$rupiah[$val['nik']][$val['tanggal']]['premi']+=$val['premi'];
				
				$rupiahslip[$val['nik']]['absen']="H";
				$rupiahslip[$val['nik']]['upah']+=$val['umr'];
				$rupiahslip[$val['nik']]['premi']+=$val['premi'];
			}
		}
		if($source['um']!="" or $param['sumber']==''){			
			$str = "select * from " . $dbname . ".sdm_uangmakandanextrafooding where 1=1 ".$where."";
			$res = fetchData($str);
			foreach ($res as $val) {
				$listkary[$val['karyawanid']]=$val['karyawanid'];
				$rupiah[$val['karyawanid']][$val['tanggal']]['premi']+=$val['jumlah'];
				$rupiahslip[$val['karyawanid']]['premi']+=$val['jumlah'];
			}
		}
		
		if($source['lain']!="" or $param['sumber']==''){			
			$str = "select * from " . $dbname . ".sdm_pendapatanlaindt where 1=1 and kodeorg='".$param['unit']."' and periodegaji='".$param['periode']."' and keterangan not like '%Proses extra fooding%'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$listkary[$val['karyawanid']]=$val['karyawanid'];
				$rupiah[$val['karyawanid']][$val['periodegaji']."-28"]['premi']+=$val['jumlah'];
				$rupiahslip[$val['karyawanid']]['premi']+=$val['jumlah'];
			}
		}
		
		
		$whgp="";
		$whgp.=" and tahun = '".substr($param['periode'],0,4)."'";
		$str = "select * from " . $dbname . ".sdm_5gajipokok where 1=1 ".$whgp." and idkomponen='1'";
		$res = fetchData($str);
		foreach ($res as $val){
			$gajipokok[$val['karyawanid']]=$val['jumlah']/25;
		}
		
		$wherepot="";
		$wherepot.=" and kodeorg like '".$param['unit']."%'";
		$wherepot.=" and periodegaji like '".$param['periode']."%'";
		$str = "select * from " . $dbname . ".sdm_potongandt where 1=1 ".$wherepot."";
		$res = fetchData($str);
		foreach ($res as $val){
			$listkary[$val['nik']]=$val['nik'];
			$rupiah[$val['nik']][$param['periode']."-28"]['potongan']+=$val['jumlahpotongan']*(-1);
			$rupiahslip[$val['nik']]['potongan']+=$val['jumlahpotongan']*(-1);
		}
		
		
		$arrkomp=array('absen','upah','premi','lembur','potongan');
		
		// echo"<pre>";
			// print_r($event);
			// echo"</pre>";
			// exit("error");
			
		if ($event!='pdf'){
			if ($event=='excel'){
				$tab.= "<table class='sortable' cellspacing='1' border='1'>";
			}else{			
				$tab.= "<table class='sortable' cellspacing='1' border='0'>";
			}
			$tab.="<thead><tr class=rowcontent>";
			$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['nourut']."</th>";
			$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['divisi']."</th>";
			$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['nik']."</th>";
			$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>";
			$tab.="<th align=center  rowspan=2>".$_SESSION['lang']['namakomponen']."</th>";
			$tab.="<th align=center colspan=".count($rangetgl).">".$_SESSION['lang']['tanggal']."</th>";
			$tab.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
			
			$tab.="</tr><tr>";
			foreach($rangetgl as $tgl){
				if(hari($tgl)=='Minggu'){				
					$tab .= "<th style=color:red>".substr($tgl,-2)."<br>".hari($tgl)."</th>";
				}else{
					$tab .= "<th>".substr($tgl,-2)."<br>".hari($tgl)."</th>";
				}
			}
			$tab.="</tr></thead>";
			$tab.="<tbody>";
			
			
			if ($event=='excel'){
				$rowsix="";
				$rowfive="";
			}else{
				$rowsix=" rowspan=6";
				$rowfive=" rowspan=5";
			}	
			
			$adarupiah=array();
			foreach ($dtkary as $kary){
				foreach($arrkomp as $komp){
					foreach($rangetgl as $tgl){
						@$adarupiah[$kary]+=$rupiah[$kary][$tgl][$komp];
						if($tipekary[$kary]!='4' and $rupiah[$kary][$tgl]['absen']!=''){
							@$adarupiah[$kary]+=1;							
						}
					}
				}
			}
			
			
			$ttlkomp=array();
			foreach ($dtkary as $kary){
				if($listkary[$kary]!='' and $adarupiah[$kary]>0){				
					$nourut++;
					$tab.="<tr class=rowcontent>";
					$tab .="<td".$rowsix.">".$nourut."</td>";
					$tab .="<td".$rowsix.">".$subbag[$kary]."</td>";
					$tab .="<td".$rowsix.">".$nikkary[$kary]."</td>";
					$tab .="<td".$rowsix.">".$nmkary[$kary]."</td>";
					if ($event=='excel'){ // kalo excel nama pake sebaris aja
						$tab .="<td></td>";
						foreach($rangetgl as $tgl){
							$tab .="<td></td>";
						}
						$tab .="<td></td></tr>";	
					}	
					$stkary=array();
					$rowkomp=1;
					foreach($arrkomp as $komp){
						if($event=='excel'){
							$tab.="<tr class=rowcontent>";
							$tab .="<td></td><td></td><td></td><td></td>";
							$tab .="<td>".$komp."</td>";
						}else{
							if($rowkomp>1){					
								$tab.="<tr class=rowcontent>";
							}
							$tab .="<td>".$komp."</td>";
						}
						$rowkomp++;
						foreach($rangetgl as $tgl){
							if($tipekary[$kary]!='4'){
								$rupiah[$kary][$tgl]['upah']=0;
							}else{
								$color="";
								if(round($rupiah[$kary][$tgl]['upah'],0)>round($gajipokok[$kary],0)){
									$color="background-color:red; title='Upah melebihi Gapok, Upah: ".$rupiah[$kary][$tgl]['upah'].", Gapok: ".$gajipokok[$kary]."'";
								}
							}
							if($komp=='absen'){
								$a="align=center";
								$isi=$rupiah[$kary][$tgl][$komp];
							}else{
								$a="align=right style=cursor:pointer;".$color." onclick=lihatDetail('".$kary."','".$tgl."')";
								$isi=numb_format($rupiah[$kary][$tgl][$komp],0);
								$ttlkomp[$tgl][$komp]+=$rupiah[$kary][$tgl][$komp];
								$ttlkary[$kary][$komp]+=$rupiah[$kary][$tgl][$komp];
								$stkary[$kary][$tgl]+=$rupiah[$kary][$tgl][$komp];
							}
							$tab .="<td ".$a.">".$isi."</td>";
						}
					
						$tab .="<td ".$a.">".numb_format($ttlkary[$kary][$komp],0)."</td>";
						
						
						$gtkomp[$komp]+=round($ttlkary[$kary][$komp]);
						$tab .="</tr>";
					}
					if($event=='excel'){
						$tab.="<tr class=rowcontent>";
						$tab .="<td></td><td></td><td></td><td></td>";
					}else{
						$tab.="<tr class=rowcontent>";
					}	
					$tab .="<td style=background-color:cyan>sub total</td>";
					$gtkary=array();
					foreach($rangetgl as $tgl){
						$tab .="<td align=right style=background-color:cyan>".numb_format($stkary[$kary][$tgl],0)."</td>";
						$gtkary[$kary]+=$stkary[$kary][$tgl];
					}
					$tab .="<td align=right style=background-color:cyan>".numb_format($gtkary[$kary],0)."</td>";
					$tab .="</tr>";
				}
			} #end foreach $karyawan`
			
			$tab .="<tr class=rowcontent>"; 
			$tab .= "<td ".$rowfive."></td><td ".$rowfive."></td><td ".$rowfive."></td><td".$rowfive." align=center>TOTAL</td>";
			if ($event=='excel'){ // kalo excel nama pake sebaris aja
				$tab .="<td></td>";
				foreach($rangetgl as $tgl){
					$tab .="<td></td>";
				}
				$tab .="<td></td></tr>";	
			}	
			
			$rowkomp=1;
			unset($arrkomp[0]);
			$gtkary=array();
			foreach($arrkomp as $komp){
				if($event=='excel'){
					$tab.="<tr class=rowcontent>";
					$tab .="<td></td><td></td><td></td><td></td>";
					$tab .="<td>".$komp."</td>";
				}else{
					if($rowkomp>1){					
						$tab.="<tr class=rowcontent>";
					}
					$tab .="<td>".$komp."</td>";
				}
				$rowkomp++;
				foreach($rangetgl as $tgl){
					$tab .="<td align=right>".numb_format($ttlkomp[$tgl][$komp],0)."</td>";
					$gtkary[$tgl]+=$ttlkomp[$tgl][$komp];
				}
				$tab .="<td ".$a.">".numb_format($gtkomp[$komp],2)."</td>";
			}
			$tab .="</tr>";
			if($event=='excel'){
				$tab.="<tr class=rowcontent>";
				$tab .="<td></td><td></td><td></td><td></td>";
			}else{
				$tab.="<tr class=rowcontent>";
			}			
			$tab .="<td style=background-color:cyan>Total</td>";
			foreach($rangetgl as $tgl){
				$tab .="<td align=right style=background-color:cyan>".numb_format($gtkary[$tgl],2)."</td>";
				$gttkary+=$gtkary[$tgl];
			}
			$tab .="<td align=right style=background-color:cyan>".numb_format($gttkary,0)."</td>";
			
			$tab .="</tr>";
			$tab.="</tbody></table>";
			
			
			
			if ($event=='html'){
				echo $tab;
			}
			if ($event=='excel'){
				$tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
				$qwe=date("YmdHms");
				$titlelaporan="Laporan detail payroll ".$qwe;
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
		}elseif($event=='pdf'){			
			#untuk slip gaji
			$tab= "<table border=1 width=100% cellspacing=5>";
				foreach ($dtkary as $kary){
					if($listkary[$kary]!=''){
						$no++;
						$i = $no%2;
						if($i==1){					
							$tab.="<tr>";
						}
							$tab.="<td style=text-align:center>";
								$tab.= "<table cellspacing='0' cellpadding=3 border='0' width=100% style=\"font-family:sans-serif;font-size:10px;\">";
									$tab.="<tr><td colspan=3>".getNamaOrg($unit)."</td></tr>";
									$tab.="<tr><td colspan=3></td></tr>";
									$tab.="<tr><td colspan=3 align=center><b>SLIP GAJI</b></td></tr>";
									$tab.="<tr><td colspan=3 align=center>".tglnormal($tgl1)." - ".tglnormal($tgl2)."</td></tr>";
									$tab.="<tr><td width=50%></td><td width=1px></td><td width=50%></td></tr>";
									$tab.="<tr>
												<td>NIK : ".getKary($kary,'nik')."</td>
												<td width=1px></td>
												<td>DIVISI : ".(getKary($kary,'subbagian')!=''?getNamaOrg(getKary($kary,'subbagian')):"UMUM")."</td>
											</tr>";
									$tab.="<tr>
												<td>NAMA : ".$nmkary[$kary]."</td>
												<td width=1px></td>
												<td>JABATAN : ".getNamaJabatan(getKary($kary,'kodejabatan'))."</td>
											</tr>";
									$tab.="<tr><td colspan=3><hr></td></tr>";
									$nomor=0;
									unset($arrkomp[0]); $total=array();
									foreach($arrkomp as $komp){
										if($rupiahslip[$kary][$komp]!=0){											
											$nomor++;
											$tab.="<tr>	
														<td style=padding-left:10px>".$nomor.". ".strtoupper($komp)."</td><td></td>
														<td align=right style=padding-right:30px>".number_format($rupiahslip[$kary][$komp])."</td>
														</tr>";
												$total[$kary]+=$rupiahslip[$kary][$komp];
										}
									}
									$tab.="<tr style=font-weight:bold>	
											<td style=padding-left:20px>TOTAL</td><td></td>
											<td align=right style=padding-right:30px>".number_format($total[$kary])."</td>
											</tr>";
									$tab.="<tr>	<td colspan=3><hr></td></tr>";		
									$tab.="<tr>	
											<td colspan=3 style=font-size:8px;font-style:italic;text-align:center;>(no urut : ".$no.") auto generate from owl.ksp-agro.com</td>
											</tr>";		
								$tab.="</table>";
							$tab.="</td>";
						if($i==0){
							$tab.="</tr>";
						}
					}
				}
			$tab.="</table>";
			// echo $tab;
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$dompdf->stream("Slip Gaji", array("Attachment" => false));
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
		
		case 'filKaryawan':
			$optKaryawan.="<option value=''>".$_SESSION['lang']['all']."</option>";
			
			$sql_databulanan = "select * from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas like '%".$unit."%' ".$wh." and tipekaryawan like '%".$tipekar."%' and karyawanid like '%".$karyawanid."%' and periodegaji='".$param['periode']."'"; 
		
			$sql_datakaryawan = "select karyawanid,nik,namakaryawan,subbagian,tipekaryawan from ".$dbname.".datakaryawan where lokasitugas like '%".$unit."%' ".$wh." and tipekaryawan like '%".$tipekar."%' and karyawanid like '%".$karyawanid."%'";
			
			//exit("");
			
			
			#$sql_databulanan = "select 	karyawanid,namakaryawan from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$_POST['unit']."' and subbagian like '".$param['divisi']."%' and tipekaryawan like '%".$_POST['tipekar']."%' and periodegaji='".$param['periode']."'";
			$querybln = fetchData($sql_databulanan);
			foreach ($querybln as $key=>$val){
				$datakary[$val['karyawanid']]=$val['namakaryawan'];
			}
			#$sql_datakaryawan = "select karyawanid,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$_POST['unit']."' and subbagian like '".$param['divisi']."%' and tipekaryawan like '%".$_POST['tipekar']."%'";
			$querykar = fetchData($sql_datakaryawan);
			foreach ($querykar as $key=>$val){
				$datakary[$val['karyawanid']]=$val['namakaryawan'];
			}
			
			$count = count($querybln);
			$res = ($count>0) ? $querybln : $querykar;
			
			foreach ($datakary as $key=>$val){
				$optKaryawan .= "<option value='".$key."'>".$val."</option>";
			}

			echo $optKaryawan;

		break;

		case 'lihatDetail':
				$str = "select karyawanid,nik,namakaryawan,subbagian,tipekaryawan from ".$dbname.".datakaryawan where karyawanid = '".$param['karyawanid']."'";
				$datakaryawan = fetchData($str);
				foreach($datakaryawan as $bar){
					$dtkary[$bar['karyawanid']]=$bar['karyawanid'];  
					$nmkary[$bar['karyawanid']]=$bar['namakaryawan'];  
					$subbag[$bar['karyawanid']]=$bar['subbagian'];  
					$tipekary[$bar['karyawanid']]=$bar['tipekaryawan'];  
					$tglkeluar[$bar['karyawanid']]=$bar['tanggalkeluar'];  
				}
				
				
				
				$list=array();
				$str = "select * from " . $dbname . ".sdm_5gajipokok where 1=1 and tahun='".substr($param['tanggal'],0,4)."' and idkomponen='1' and karyawanid='".$param['karyawanid']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$gaji[$val['karyawanid']]=$val['jumlah']/25;
				}

				$str = "select * from " . $dbname . ".sdm_absensidt_vw where 1=1 and karyawanid='".$param['karyawanid']."' and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val){
					$rupiah['absensi'][0]['absen']=$val['absensi'];
					$rupiah['absensi'][0]['upah']+=$val['umr']-$val['penaltykehadiran'];
					$rupiah['absensi'][0]['premi']+=$val['premi']+$val['insentif']+$val['insentiflibur'];
					$list['absensi'][0]=0;
				}
				
				$str = "select * from " . $dbname . ".sdm_lemburdt where 1=1 and karyawanid='".$param['karyawanid']."' and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$rupiah['lembur'][0]['lembur']+=$val['uangkelebihanjam'];
					$list['lembur'][0]=0;
				}
				
				$str = "select a.*,b.tanggal,b.kodeorg as kodeorg from " . $dbname . ".kebun_kehadiran a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1  and nik='".$param['karyawanid']."' and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$rupiah['bkmrawat'][$val['notransaksi']]['absen']=$val['absensi'];
					$rupiah['bkmrawat'][$val['notransaksi']]['upah']+=$val['umr'];
					$rupiah['bkmrawat'][$val['notransaksi']]['premi']+=$val['insentif']-$val['penalty'];
					$list['bkmrawat'][$val['notransaksi']]=$val['notransaksi'];
				}
				
				$str = "select a.*,b.tanggal,b.kodeorg as kodeorg from " . $dbname . ".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 and tipetransaksi='PNN' and nik='".$param['karyawanid']."' and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$rupiah['panen'][$val['notransaksi']]['absen']='H';
					$rupiah['panen'][$val['notransaksi']]['upah']+=$val['upahkerja']-$val['upahpenalty'];
					$rupiah['panen'][$val['notransaksi']]['premi']+=($val['upahpremi']+$val['upahpremilebihbasis']+$val['premibasis'])-$val['rupiahpenalty'];
					$list['panen'][$val['notransaksi']]=$val['notransaksi'];
				}
				
				$str = "select * from " . $dbname . ".vhc_runhk_vw where 1=1 and idkaryawan='".$param['karyawanid']."' and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$rupiah['traksi'][$val['notransaksi']]['absen']="H";
					$rupiah['traksi'][$val['notransaksi']]['upah']+=$val['upah']-$val['penalty'];
					$rupiah['traksi'][$val['notransaksi']]['premi']+=$val['premi'];
					$list['traksi'][$val['notransaksi']]=$val['notransaksi'];
				}
				
				$str = "select * from " . $dbname . ".kebun_premikemandoran where 1=1 and karyawanid='".$param['karyawanid']."' and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$rupiah['premimandor'][]['premi']+=$val['premiinput'];
					$list['premimandor'][0]=0;
				}
				
				$gjhead=array();
				$str = "select * from " . $dbname . ".kebun_aktifitas where 1=1 and (nikmandor1='".$param['karyawanid']."' or nikmandor='".$param['karyawanid']."' or keranimuat='".$param['karyawanid']."' or nikasisten='".$param['karyawanid']."') and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					if($val['nikmandor']!=''){
						$rupiah['headbkm'][$val['notransaksi']]['absen']="H";
					}
					if($tipekary[$val['nikmandor']]=='4' and $val['nikmandor']!=''){
						$gjhead[$val['notransaksi']]+=1;
						$uppermd=$gaji[$val['nikmandor']];
					}
					if($val['nikmandor1']!=''){
						$rupiah['headbkm'][$val['notransaksi']]['absen']="H";
					}
					if($tipekary[$val['nikmandor1']]=='4' and $val['nikmandor1']!=''){
						$gjhead[$val['notransaksi']]+=1;
						$uppermd=$gaji[$val['nikmandor1']];
					}
					if($val['keranimuat']!=''){		
						$rupiah['headbkm'][$val['notransaksi']]['absen']="H";
					}
					if($tipekary[$val['keranimuat']]=='4' and $val['keranimuat']!=''){
						$gjhead[$val['notransaksi']]+=1;
						$uppermd=$gaji[$val['keranimuat']];
					}
					if($val['nikasisten']!=''){		
						$rupiah['headbkm'][$val['notransaksi']]['absen']="H";
					}
					if($tipekary[$val['nikasisten']]=='4' and $val['nikasisten']!=''){
						$gjhead[$val['notransaksi']]+=1;
						$uppermd=$gaji[$val['nikasisten']];
					}
					$list['headbkm'][$val['notransaksi']]=$val['notransaksi'];
				}
				
				foreach($gjhead as $notran => $nilai){
						$rupiah['headbkm'][$notran]['upah']+=$uppermd/count($gjhead);
				}
				
				$str = "select * from " . $dbname . ".vhc_spl_kehadiran_vw where 1=1 and nik='".$param['karyawanid']."' and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$rupiah['sipil'][$val['notransaksi']]['absen']="H";
					$rupiah['sipil'][$val['notransaksi']]['upah']+=$val['umr'];
					$rupiah['sipil'][$val['notransaksi']]['premi']+=$val['premi'];
					$list['sipil'][$val['notransaksi']]=$val['notransaksi'];
				}
				
				$str = "select * from " . $dbname . ".sdm_uangmakandanextrafooding where 1=1 and karyawanid='".$param['karyawanid']."' and tanggal='".$param['tanggal']."'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$rupiah['extrafood'][]['premi']+=$val['jumlah'];
					$list['extrafood'][0]=0;
				}
				
				$str = "select * from " . $dbname . ".sdm_pendapatanlaindt where 1=1 and karyawanid='".$param['karyawanid']."' and periodegaji like '".substr($param['tanggal'],0,7)."' and keterangan not like '%Proses extra fooding%'";
				$res = fetchData($str);
				foreach ($res as $val) {
					$rupiah['pendapatanlain'][]['premi']+=$val['jumlah'];
					$list['pendapatanlain'][0]=0;
				}
				
				// echo"<pre>";
				// print_r($rupiah);
				// echo"</pre>";
				
				$wherepot="";
				$wherepot.=" and periodegaji like '".substr($param['tanggal'],0,7)."%'";
				$str = "select * from " . $dbname . ".sdm_potongandt where 1=1 ".$wherepot." and nik ='".$param['karyawanid']."'";
				$res = fetchData($str);
				foreach ($res as $val){
					if($param['tanggal']==substr($param['tanggal'],0,7)."-28"){						
						$rupiah['potongan'][]['potongan']+=$val['jumlahpotongan']*(-1);
						$list['potongan'][0]=0;
					}
				}
				
				$arrkomp=array('absen','upah','premi','lembur','potongan');
				if(count($list)==0){
					echo"Detail tidak tersedia.";exit();
				}
				
				$tab .= "<label>Nama Karyawan : ".$nmkary[$param['karyawanid']]."</label><br>";
				$tab .= "<label>Tanggal : ".$param['tanggal']."</label><br>";
				$tab .= "<table class='sortable' cellspacing='1' cellpadding='5' border='0'>";
				$tab .= "<thead><tr style=text-align:center>";
				$tab .= "<td>".$_SESSION['lang']['nourut']."</td>";
				$tab .= "<td>".$_SESSION['lang']['sumber']."</td>";
				$tab .= "<td>".$_SESSION['lang']['notransaksi']."</td>";
				foreach($arrkomp as $komp){
					$tab .= "<td>".$komp."</td>";					
				}
				$tab .= "</tr>";
				$tab .= "</thead>";
				$tab .= "<tbody>";
				
				foreach($list as $sumber => $key){
					foreach($key as $notran){
						$no++;
						$tab .= "<tr class='rowcontent'>";
						$tab .= "<td align=center>".$no."</td>";
						$tab .= "<td align=left>".$sumber."</td>";
						$tab .= "<td align=left>".$notran."</td>";
						foreach($arrkomp as $komp){
							if($tipekary[$param['karyawanid']]!='4'){
								$rupiah[$sumber][$notran]['upah']=0;
							}
							if($komp=='absen'){
								$a="align=center";
								$isi=$rupiah[$sumber][$notran][$komp];
							}
							else{
								$a="align=right";
								$isi=numb_format($rupiah[$sumber][$notran][$komp]);
							}
							$tab .="<td ".$a.">".$isi."</td>";
						}
					}					
				}
				
				

				$tab .= "</tbody>";
				$tab .= "</table>";
			echo $tab;
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