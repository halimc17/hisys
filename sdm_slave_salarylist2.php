<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');


$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}



$str = "select *  from " . $dbname . ".organisasi";
$res = fetchdata($str);
foreach($res as $bar){
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
	$tporg[$bar['kodeorganisasi']]=$bar['tipe'];
	$nmpt[$bar['kodeorganisasi']]=$bar['induk'];
}

$str = "select *  from " . $dbname . ".sdm_ho_component";
$res = fetchdata($str);
foreach($res as $bar){
	$idcomp[$bar['id']]=$bar['name'];
	$plus[$bar['id']]=$bar['plus'];
}

$str = "select *  from " . $dbname . ".bgt_regional_assignment";
$res = fetchdata($str);
foreach($res as $bar){
	$nmreg[$bar['kodeunit']]=$bar['regional'];
}

$str = "select *  from " . $dbname . ".sdm_5jabatan";
$res = fetchdata($str);
foreach($res as $bar){
	$nmjab[$bar['kodejabatan']]=$bar['namajabatan'];
}
$str = "select *  from " . $dbname . ".sdm_5tipekaryawan";
$res = fetchdata($str);
foreach($res as $bar){
	$tipekar[$bar['id']]=$bar['tipe'];
}
$str = "select *  from " . $dbname . ".sdm_5departemen";
$res = fetchdata($str);
foreach($res as $bar){
	$nmdept[$bar['kode']]=$bar['nama'];
}
$str = "select *  from " . $dbname . ".sdm_5golongan";
$res = fetchdata($str);
foreach($res as $bar){
	$nmgol[$bar['kodegolongan']]=$bar['namagolongan'];
}

$datasort= array();
		
switch($method){
	case'sumber':
		$tab="<fieldset><legend>Info</legend><div>";
		switch($param['jenis']){
			case'payroll':
				$tab.="Sumber data :<li>Data gaji dengan beberapa komponen diambil dari bulan lalu.</li>";
				$tab.="<table class='sortable' cellspacing='1' cellpadding='1' border='0'>";
				$tab.="<tr class=rowcontent><td>";
				$tab.="Data diluar komponen payroll karyawan (tidak ditampilkan) :";
				
				$str   = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='KOMGJEXSLP'";
				$res   = fetchdata($str);
				$arrx  = explode(',', $res[0]['nilai']);
				for ($i=0; $i < count($arrx); $i++) { 
					$tab.="<li>".$idcomp[$arrx[$i]]."</li>";
				}
				$tab.="</td><td>";
				$tab.="Data diambil dari bulan lalu :";
				$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
				$res = fetchdata($str);
				$arrx  = explode(',', $res[0]['nilai']);
				for ($i=0; $i < count($arrx); $i++) { 
					$tab.="<li>".$idcomp[str_replace("'","",$arrx[$i])]."</li>";
				}
				$tab.="</td></tr>";
				$tab.="</table>";
				
			break;
			case'aktual':
				$tab.="Sumber data :<li>Data gaji aktual bulan ini.</li>
						<li>Jika ada data yg tidak muncul agar dilakukan Proses Penggajian ulang.</li>
						<li>Untuk menghitung jumlah TK pilih <b>Count Unique Values : KaryID</b></li>
						";
			break;
		}
		$tab.="</div></fieldset>";
	break;
	case'aktual':
		$wh="";$whr="";
		if($param['tipekaryawan']!=''){
			$whr.=" and tipekaryawan='".$param['tipekaryawan']."'";	
		}else{				
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$whr=" and tipekaryawan in ('4')";
			}
		}
		if($param['kodeorg']!=''){
			$whr.=" and lokasitugas='".$param['kodeorg']."'";	
		}else{
			if($param['jenis']=='data'){				
				exit("Warning: Kode organisasi harus diisi.");
			}
		}
		$datae[]=array('Periode','PT','Unit','Tipe Org','Divisi','KaryID','NIK','Nama Karyawan','Jabatan','Tipe Kary','Status Pajak','NPWP','Bank','Rekening','Pemilik','Sistem Gaji','Tanggal Masuk','Tanggal Keluar','Agama','J/K','Dept','Golongan','Status Kary','KPP NPWP','BPJS TK','BPJS Kes','JP','Jenis Komponen','Komponen','Jumlah');
			
		$numb=array(26);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row     = array("Unit","Divisi");
		$col     = array("Jenis Komponen","Komponen");
		$value   = array('Jumlah');
		$datasort= array("Gaji Pokok","Premi","Lembur","Insentif Tetap","Uang Transport","Extra Fooding");
		
		$rangebulan = month_inbetween($param['periode'],$param['periode2']);
		$nourut = 0;
		foreach($rangebulan as $bulan){
			$str = "select * from " . $dbname . ".sdm_gaji where 1=1 ".$wh." and periodegaji = '".$bulan."'";
			$res = fetchdata($str);
			foreach($res as $val){
				$sql = "select * from ".$dbname.".datakaryawan_hist where 1=1 and karyawanid='".$val['karyawanid']."' and periodegaji = '".$bulan."' and version_type='B' ".$whr."";
				$req = fetchdata($sql);
				if(count($req)==0){
					$sql = "select * from ".$dbname.".datakaryawan where 1=1 and karyawanid='".$val['karyawanid']."' ".$whr."";
					$req = fetchdata($sql);
				}
				foreach($req as $bar){
					$datakary[$bar['karyawanid']]=$bar['karyawanid'];
					$dt[$bar['karyawanid']][$bulan]['nama']=$bar['namakaryawan'];
					$dt[$bar['karyawanid']][$bulan]['nik']=$bar['nik'];
					$dt[$bar['karyawanid']][$bulan]['jk']=$bar['jeniskelamin'];
					$dt[$bar['karyawanid']][$bulan]['agama']=$bar['agama'];
					$dt[$bar['karyawanid']][$bulan]['norek']=$bar['norekeningbank'];
					$dt[$bar['karyawanid']][$bulan]['bank']=$bar['namabank'];
					$dt[$bar['karyawanid']][$bulan]['pemilik']=$bar['pemilikrekening'];
					$dt[$bar['karyawanid']][$bulan]['sistemgaji']=$bar['sistemgaji'];
					$dt[$bar['karyawanid']][$bulan]['tanggalmasuk']=$bar['tanggalmasuk'];
					$dt[$bar['karyawanid']][$bulan]['tanggalkeluar']=$bar['tanggalkeluar'];
					$dt[$bar['karyawanid']][$bulan]['tipekaryawan']=$bar['tipekaryawan'];
					$dt[$bar['karyawanid']][$bulan]['statuspajak']=$bar['statuspajak'];
					$dt[$bar['karyawanid']][$bulan]['npwp']=$bar['npwp'];
					$dt[$bar['karyawanid']][$bulan]['pt']=$bar['kodeorganisasi'];
					$dt[$bar['karyawanid']][$bulan]['dept']=$bar['bagian'];
					$dt[$bar['karyawanid']][$bulan]['kodejabatan']=$bar['kodejabatan'];
					$dt[$bar['karyawanid']][$bulan]['kodegolongan']=$bar['kodegolongan'];
					$dt[$bar['karyawanid']][$bulan]['lokasitugas']=$bar['lokasitugas'];
					$dt[$bar['karyawanid']][$bulan]['alokasi']=$bar['alokasi'];
					$dt[$bar['karyawanid']][$bulan]['subbagian']=$bar['subbagian'];
					$dt[$bar['karyawanid']][$bulan]['statuskaryawan']=$bar['statuskaryawan'];
					$dt[$bar['karyawanid']][$bulan]['kppnpwp']=$bar['kppnpwp'];
					$dt[$bar['karyawanid']][$bulan]['bpjstk']=$bar['jms'];
					$dt[$bar['karyawanid']][$bulan]['bpjskes']=$bar['bpjs'];
					$dt[$bar['karyawanid']][$bulan]['pensiun']=$bar['pensiun'];
				}
				
				if($plus[$val['idkomponen']]=='0'){
					$val['jumlah']=$val['jumlah']*(-1);
					$nmcom="Pengurang";
				}else{
					$nmcom="Penambah";
					$idkomp[$val['idkomponen']]=$val['idkomponen'];
				}
				if($dt[$val['karyawanid']][$val['periodegaji']]['subbagian']==''){
					$divisi="UMUM";
				}else{
					$divisi=$nmorg[$dt[$val['karyawanid']][$val['periodegaji']]['subbagian']];
				}
				if($datakary[$val['karyawanid']]!=''){					
					$data[]=array(
						$val['periodegaji'],
						$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
						$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
						$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
						$divisi,
						$val['karyawanid'],
						$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
						$dt[$val['karyawanid']][$val['periodegaji']]['nama'],
						$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
						$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
						$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
						$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
						$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
						$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
						$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
						$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
						$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
						$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
						$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
						$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
						$nmdept[$dt[$val['karyawanid']][$val['periodegaji']]['dept']],
						$nmgol[$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan']],
						$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
						$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
						$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
						$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
						$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
						$nmcom,
						$idcomp[$val['idkomponen']],
						$val['jumlah']
					);
				}
			}
		}
		
	array_multisort($idkomp,SORT_ASC);
	foreach($idkomp as $komponen){
		$datasort[]=$idcomp[$komponen];
	}
		
	break;
	case'payroll':
		$wh="";$whr="";$whtp="";
		
		if($param['tipekaryawan']!=''){
			$whr.=" and tipekaryawan='".$param['tipekaryawan']."'";	
			$whtp.=" and tipekaryawan='".$param['tipekaryawan']."'";	
		}else{				
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$whr.=" and tipekaryawan in ('4')";
				$whtp.=" and tipekaryawan in ('4')";
			}
		}
		
		if($param['kodeorg']!=''){
			$whr.=" and lokasitugas='".$param['kodeorg']."'";	
		}else{
			if($param['jenis']=='data'){				
				exit("Warning: Kode organisasi harus diisi.");
			}
		}
		
		$datae[]=array('Periode','PT','Unit','Tipe Org','Divisi','KaryID','NIK','Nama Karyawan','KTP','Jabatan','Tipe Kary','Status Pajak','NPWP','Bank','Rekening','Pemilik','Sistem Gaji','Tanggal Masuk','Tanggal Keluar','Agama','J/K','Dept','Golongan','Status Kary','KPP NPWP','BPJS TK','BPJS Kes','JP','Jenis Komponen','Komponen','Jumlah');
		
		$numb=array(26);
		
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("PT","Unit","Divisi","NIK","Nama Karyawan","Jabatan","Golongan","Tipe Kary","Status Pajak","KTP","Bank","Rekening","Pemilik","Tanggal Masuk","Tanggal Keluar");
		$col = array("Jenis Komponen","Komponen");
		$value = array('Jumlah');
		
		
		#= komponen yang tidak termasuk di slip gaji
		$str   = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='KOMGJEXSLP'";
		$res   = fetchdata($str);
		$exslp = $res[0]['nilai'];
		$exslip= array();
		$arrx  = explode(',', $res[0]['nilai']);
		for ($i=0; $i < count($arrx); $i++) { 
			$exslip[$arrx[$i]]=$arrx[$i];
		}


		$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
		$res = fetchdata($str);
		$gjthnlu=$res[0]['nilai'];

		$rangebulan = month_inbetween($param['periode'],$param['periode']);
		$nourut = 0;
		foreach($rangebulan as $bulan){
			$str = "select * from ".$dbname.".datakaryawan_hist where 1=1 and periodegaji = '".$bulan."' ".$whr." and version_type='B'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$karyblnini[$bar['karyawanid']]=$bar['karyawanid'];
				if($nourut==0){
					$nikkary.= "'".$bar['karyawanid']."'";
				}else{
					$nikkary.= ",'".$bar['karyawanid']."'";
				}
				$nourut++;
				$dt[$bar['karyawanid']][$bar['periodegaji']]['nama']=$bar['namakaryawan'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['ktp']=$bar['noktp'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['nik']=$bar['nik'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['jk']=$bar['jeniskelamin'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['agama']=$bar['agama'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['norek']=$bar['norekeningbank'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['bank']=$bar['namabank'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['pemilik']=$bar['pemilikrekening'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['sistemgaji']=$bar['sistemgaji'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalmasuk']=$bar['tanggalmasuk'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalkeluar']=$bar['tanggalkeluar'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['tipekaryawan']=$bar['tipekaryawan'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['statuspajak']=$bar['statuspajak'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['npwp']=$bar['npwp'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['pt']=$bar['kodeorganisasi'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['dept']=$nmdept[$bar['bagian']];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['kodejabatan']=$bar['kodejabatan'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['kodegolongan']=$nmgol[$bar['kodegolongan']];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['lokasitugas']=$bar['lokasitugas'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['alokasi']=$bar['alokasi'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['subbagian']=$bar['subbagian'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['statuskaryawan']=$bar['statuskaryawan'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['kppnpwp']=$bar['kppnpwp'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['bpjstk']=$bar['jms'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['bpjskes']=$bar['bpjs'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['pensiun']=$bar['pensiun'];
			}
			
			$str = "select * from " . $dbname . ".sdm_gaji_vw where 1=1 and periodegaji = '".$bulan."' and tipekaryawan='4' ".$whr."";
			
			$res = fetchdata($str);
			foreach($res as $val){
				if($dt[$val['karyawanid']][$val['periodegaji']]['subbagian']==''){
					$divisi="UMUM";
				}else{
					$divisi=$nmorg[$dt[$val['karyawanid']][$val['periodegaji']]['subbagian']];
				}
				if($plus[$val['idkomponen']]=='0'){
					$val['jumlah']=$val['jumlah']*(-1);
					$nmcom="Pengurang";
				}else{
					$nmcom="Penambah";
					$idkomp[$val['idkomponen']]=$val['idkomponen'];
				}
				$data[]=array(
					$val['periodegaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$divisi,
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					($dt[$val['karyawanid']][$val['periodegaji']]['nama']==""?$val['karyawanid']:$dt[$val['karyawanid']][$val['periodegaji']]['nama']),
					$dt[$val['karyawanid']][$val['periodegaji']]['ktp'],
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['dept'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
					$nmcom,
					$idcomp[$val['idkomponen']],
					$val['jumlah']
				);
			}
			
			$whkar=" and karyawanid in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 and periodegaji = '".$bulan."' ".$whr." and version_type='B')";
			$str = "select * from " . $dbname . ".sdm_gaji_vw where 1=1 and tipekaryawan!='4' ".$whtp." and periodegaji = '".$bulan."' and idkomponen not in (".$gjthnlu.") and idkomponen not in(".$exslp.") ".$whkar."";
			$res = fetchdata($str);
			foreach($res as $val){
				if($dt[$val['karyawanid']][$val['periodegaji']]['subbagian']==''){
					$divisi="UMUM";
				}else{
					$divisi=$nmorg[$dt[$val['karyawanid']][$val['periodegaji']]['subbagian']];
				}
				if($plus[$val['idkomponen']]=='0'){
					$val['jumlah']=$val['jumlah']*(-1);
					$nmcom="Pengurang";
				}else{
					$nmcom="Penambah";
					$idkomp[$val['idkomponen']]=$val['idkomponen'];
				}
				$val['periodegaji']=$bulan;
				$data[]=array(
					$val['periodegaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$divisi,
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					($dt[$val['karyawanid']][$val['periodegaji']]['nama']==""?$val['karyawanid']:$dt[$val['karyawanid']][$val['periodegaji']]['nama']),
					$dt[$val['karyawanid']][$val['periodegaji']]['ktp'],
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['dept'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
					$nmcom,
					$idcomp[$val['idkomponen']],
					$val['jumlah']
				);
			}
			#komponen bulan lalu
			#$str = "select * from " . $dbname . ".sdm_gaji_vw where periodegaji = '".periodelalu($bulan)."' and idkomponen in (".$gjthnlu.") and idkomponen not in(".$exslp.") and karyawanid in (select karyawanid from " . $dbname . ".sdm_gaji_vw where 1=1 and tipekaryawan!='4' ".$whtp." and periodegaji = '".$bulan."' and idkomponen not in (".$gjthnlu.") and idkomponen not in(".$exslp.")) ".$whkar."";
			
			$str = "select * from " . $dbname . ".sdm_gaji_vw where periodegaji = '".periodelalu($bulan)."' and idkomponen in (".$gjthnlu.") and idkomponen not in(".$exslp.") and tipekaryawan!='4' ".$whr."";#exit("error".$str);
			$res = fetchdata($str);
			foreach($res as $val){
				if($karyblnini[$val['karyawanid']]==''){					
					$sql = "select * from ".$dbname.".datakaryawan_hist where 1=1 and periodegaji = '".periodelalu($bulan)."' and karyawanid='".$val['karyawanid']."' and version_type='B'";
					$req = fetchdata($sql);
					foreach($req as $bar){
						$dt[$bar['karyawanid']][$bulan]['nama']=$bar['namakaryawan'];
						$dt[$bar['karyawanid']][$bulan]['ktp']=$bar['noktp'];
						$dt[$bar['karyawanid']][$bulan]['nik']=$bar['nik'];
						$dt[$bar['karyawanid']][$bulan]['jk']=$bar['jeniskelamin'];
						$dt[$bar['karyawanid']][$bulan]['agama']=$bar['agama'];
						$dt[$bar['karyawanid']][$bulan]['norek']=$bar['norekeningbank'];
						$dt[$bar['karyawanid']][$bulan]['bank']=$bar['namabank'];
						$dt[$bar['karyawanid']][$bulan]['pemilik']=$bar['pemilikrekening'];
						$dt[$bar['karyawanid']][$bulan]['sistemgaji']=$bar['sistemgaji'];
						$dt[$bar['karyawanid']][$bulan]['tanggalmasuk']=$bar['tanggalmasuk'];
						$dt[$bar['karyawanid']][$bulan]['tanggalkeluar']=$bar['tanggalkeluar'];
						$dt[$bar['karyawanid']][$bulan]['tipekaryawan']=$bar['tipekaryawan'];
						$dt[$bar['karyawanid']][$bulan]['statuspajak']=$bar['statuspajak'];
						$dt[$bar['karyawanid']][$bulan]['npwp']=$bar['npwp'];
						$dt[$bar['karyawanid']][$bulan]['pt']=$bar['kodeorganisasi'];
						$dt[$bar['karyawanid']][$bulan]['dept']=$nmdept[$bar['bagian']];
						$dt[$bar['karyawanid']][$bulan]['kodejabatan']=$bar['kodejabatan'];
						$dt[$bar['karyawanid']][$bulan]['kodegolongan']=$nmgol[$bar['kodegolongan']];
						$dt[$bar['karyawanid']][$bulan]['lokasitugas']=$bar['lokasitugas'];
						$dt[$bar['karyawanid']][$bulan]['alokasi']=$bar['alokasi'];
						$dt[$bar['karyawanid']][$bulan]['subbagian']=$bar['subbagian'];
						$dt[$bar['karyawanid']][$bulan]['statuskaryawan']=$bar['statuskaryawan'];
						$dt[$bar['karyawanid']][$bulan]['kppnpwp']=$bar['kppnpwp'];
						$dt[$bar['karyawanid']][$bulan]['bpjstk']=$bar['jms'];
						$dt[$bar['karyawanid']][$bulan]['bpjskes']=$bar['bpjs'];
						$dt[$bar['karyawanid']][$bulan]['pensiun']=$bar['pensiun'];
					}
				}
				
				
				if($dt[$val['karyawanid']][$val['periodegaji']]['subbagian']==''){
					$divisi="UMUM";
				}else{
					$divisi=$nmorg[$dt[$val['karyawanid']][$val['periodegaji']]['subbagian']];
				}
				if($plus[$val['idkomponen']]=='0'){
					$val['jumlah']=$val['jumlah']*(-1);
					$nmcom="Pengurang";
				}else{
					$nmcom="Penambah";
					$idkomp[$val['idkomponen']]=$val['idkomponen'];
				}
				$val['periodegaji']=$bulan;
				$data[]=array(
					$val['periodegaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$divisi,
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					($dt[$val['karyawanid']][$val['periodegaji']]['nama']==""?$val['karyawanid']:$dt[$val['karyawanid']][$val['periodegaji']]['nama']),
					$dt[$val['karyawanid']][$val['periodegaji']]['ktp'],
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['dept'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
					$nmcom,
					$idcomp[$val['idkomponen']],
					$val['jumlah']
				);
			}	
		}
		
		array_multisort($idkomp,SORT_ASC);
		foreach($idkomp as $komponen){
			$datasort[]=$idcomp[$komponen];
		}
	break;
}

// echo"<pre>";
// print_r($rangebulan);
// echo"</pre>";
// exit("error");

if($param['jenis']=='data'){
	$tab="<table id=pvtTable cellpadding=1 cellspacing=1 border=0 class='sortable' width='100%' data-scroll-x='true' scroll-collapse='false'>
		<thead>
			<tr>";
			foreach($datae as $key => $var){
				foreach($var as $val){
					if($key==0){
						$tab.="<th>".$val."</th>";
						$jlhcolhead++;
					}
				}
			}
		
		$tab.="</tr>
			</thead><tbody>";
		$tab.="</tbody><tfoot>
			<tr>";
			foreach($datae as $key => $var){
				foreach($var as $val){
					if($key==0){					
						$tab.="<th>".$val."</th>";
					}
				}
			}
		
	$tab.="</tr></tfoot>";	
	$tab.="</table>";
	$tab.="<fieldset style=float:left;><legend>Show/Hide</legend><div>";
		$e=0;
		foreach($datae as $key => $var){
			foreach($var as $val){
				if($key==0){
					if($jlhcolhead>8){						
						$tab.="<button class=\"dt-button\" data-column=".$e.">".substr($val,0,4)."...</button>";
					}else{
						$tab.="<button class=\"dt-button\" data-column=".$e.">".$val."</button>";
					}
					$e++;
				}
			}
		}
	$tab.="</div></fieldset>";
	
	echo $tab."####".json_encode($data)."####".json_encode($numb);
}elseif($method=='sumber'){
	echo $tab;
}else{	
	echo json_encode($data)."####".json_encode($row)."####".json_encode($col)."####".json_encode($value)."####".json_encode($datasort);
}

?>
