<?php
#ini_set('display_errors',0);
#error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$param = $_POST;
$method= checkPostGet('method', '');
$param['rupiah']=str_replace(",","",$param['rupiah']);

switch ($method){
	case'preview':
		$param = $_GET;
		$theme=$_SESSION['theme'];
		if($theme=='skyblue' || $theme==''){
		  $gen='generic.css';
		}else if($theme=='red'){
		  $gen='genericRed.css';  
		}else{
		  $gen='genericGray.css';  
		} 
		echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>";

		$str = "select * from " . $dbname . ".sdm_5periodegaji where periode='" . $param['periode'] . "' and kodeorg='".$param['kodeorg']."'"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$tgl1=$bar['tanggalmulai'];
			$tgl2=$bar['tanggalsampai'];
		}
		$rangetgl = rangeTanggal($tgl1,$tgl2);
		$tab="";
		if($param['jenis']=='html'){			
			$tab.="<div class='table-scroll'><table border=0 cellpadding=5 cellspacing=1 class=sortable>";
		}else{
			$tab.="<table border=1 cellpadding=5 cellspacing=1 class=sortable>";			
		}
		$tab.="<thead><tr class=rowheader>";
		$tab.=" <th align=center rowspan=2 width=20px>No</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['nik2']."</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['tipekaryawan']."</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['jabatan']."</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['tipe']."</th>
				<th align=center colspan=".count($rangetgl).">".$_SESSION['lang']['tanggal'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['total'] . "</th>
			</tr>
			<tr class=rowheader>";
			foreach($rangetgl as $tgl){
				$tab.="<th align=center>".substr($tgl,-2)."</th>";
			}
		$tab.="</tr></thead><tbody>";
		
		$no=0;$data=array();
		$str = "select * from " . $dbname . ".sdm_uangmakandanextrafooding a 
		left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid 
		where a.kodeorg='".$param['kodeorg']."' and a.tanggal between '".$tgl1."' and '".$tgl2."' and a.tipekaryawan='".$param['tipekar']."'  and a.idkomponen='".$param['jenisid']."' order by namakaryawan asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$data[$bar['karyawanid']]=$bar['karyawanid'];
			$absen[$bar['karyawanid']][$bar['tanggal']]=$bar['absen'];
			$jam[$bar['karyawanid']][$bar['tanggal']]=$bar['jam'];
			$hari[$bar['karyawanid']][$bar['tanggal']]=$bar['hari'];
			$nik[$bar['karyawanid']]=$bar['nik'];
			$div[$bar['karyawanid']]=$bar['subbagian'];
			$tipe[$bar['karyawanid']]=$bar['tipekaryawan'];
			$jab[$bar['karyawanid']]=$bar['kodejabatan'];
			$nama[$bar['karyawanid']]=$bar['namakaryawan'];
			$rp[$bar['karyawanid']][$bar['tanggal']]=$bar['jumlah'];
		}
		
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi like '".$param['kodeorg']."%'");
		$nmtipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
		$nmjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		foreach($data as $karyid){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center rowspan=4 style=vertical-align:top;>".$no."</td>";
			$tab.="<td rowspan=4 style=vertical-align:top;>".$nik[$karyid]."</td>";
			$tab.="<td rowspan=4 style=vertical-align:top;>".$nama[$karyid]."</td>";
			$tab.="<td rowspan=4 style=vertical-align:top;>".$nmtipe[$tipe[$karyid]]."</td>";
			$tab.="<td rowspan=4 style=vertical-align:top;>".$nmjab[$jab[$karyid]]."</td>";
			$tab.="<td>Hari</td>";
			
			foreach($rangetgl as $tgl){
				$cl="color:white;";
				if(strtolower($hari[$karyid][$tgl])=='sat'){
					$cl="color:yellow;";
				}elseif(strtolower($hari[$karyid][$tgl])=='sun'){
					$cl="color:red;";
				}
				$tab.="<td align=center style=background-color:#275370;".$cl.">".$hari[$karyid][$tgl]."</td>";
			}
			$tab.="<td></td>";
			$tab.="</tr>";
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=left>Absensi</td>";
			foreach($rangetgl as $tgl){
				$tab.="<td align=center>".$absen[$karyid][$tgl]."</td>";
			}	
			$tab.="<td></td>";
			$tab.="</tr>";
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=left>Jam Kerja</td>";
			foreach($rangetgl as $tgl){
				$tab.="<td align=center>".$jam[$karyid][$tgl]."</td>";
			}
			$tab.="<td></td>";			
			$tab.="</tr>";
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=left>Rupiah</td>";
			foreach($rangetgl as $tgl){
				$tab.="<td align=center>" . numb_format($rp[$karyid][$tgl])."</td>";
				$ttlrp[$karyid]+=$rp[$karyid][$tgl];
				$gtlrp[$tgl]+=$rp[$karyid][$tgl];
				$gtlrpall+=$rp[$karyid][$tgl];
			}
			$tab.="<td align=right >".numb_format($ttlrp[$karyid])."</td>";
			$tab.="</tr>";
		}
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=6>T O T A L</td>";
		foreach($rangetgl as $tgl){					
			$tab.="<td align=right>".numb_format($gtlrp[$tgl])."</td>";
		}
		$tab.="<td align=right>".numb_format($gtlrpall)."</td>";
			
		
		$tab.="</tbody>";
		$tab.="</table>";
		if($param['jenis']=='html'){			
			$tab.="</div>";
			echo $tab;
		}else{
			$nop = "excel.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("data", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
	break;
	case'posting':
		try {
		$owlPDO->beginTransaction();
			#insert ke table sdm_pendapatanlain
			$jlhttlrp=0;
			$str = "select idkomponen,karyawanid,sum(jumlah) as jumlah from " . $dbname . ".sdm_uangmakandanextrafooding where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and tipekaryawan='".$param['tipekar']."' and idkomponen='".$param['jenis']."' group by karyawanid, periodegaji, idkomponen";
			$res = fetchdata($str);
			if(count($res)>0){
				foreach($res as $bar){
					#cek dulu di ht
					$strht = "select * from " . $dbname . ".sdm_pendapatanlainht where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and idkomponen='".$bar['idkomponen']."'";
					$resht = fetchdata($strht);
					if(count($resht)==0){
						$data = array();
						$data = array(
							'kodeorg'    => $param['kodeorg'],
							'periodegaji'=> $param['periode'],
							'idkomponen' => $bar['idkomponen'],
							'posting'    => '1',
							'updateby'   => $_SESSION['standard']['userid'],
							'updatetime' => date("Y-m-d H:i:s")
						);
						$cols = array();
						foreach($data as $key=>$row) {
								$cols[] = $key;
						}
						$query = insertQuery($dbname,'sdm_pendapatanlainht',$data,$cols);
						$owlPDO->exec($query);
					}
					
					
					$strdt = "delete from " . $dbname . ".sdm_pendapatanlaindt where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and idkomponen='".$bar['idkomponen']."' and karyawanid='".$bar['karyawanid']."'";
					$owlPDO->exec($strdt);
					
					#insert dt
					$data = array();
					$data = array(
						'kodeorg'    => $param['kodeorg'],
						'periodegaji'=> $param['periode'],
						'karyawanid' => $bar['karyawanid'],
						'idkomponen' => $bar['idkomponen'],
						'jumlah'     => $bar['jumlah'],
						'keterangan' => 'Proses extra fooding',
						'posting'    => '1',
						'updateby'   => $_SESSION['standard']['userid'],
						'updatetime' => date("Y-m-d H:i:s")
					);
					$cols = array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$query = insertQuery($dbname,'sdm_pendapatanlaindt',$data,$cols);
					$owlPDO->exec($query);
				}
			}
			
			$str = "update " . $dbname . ".sdm_uangmakandanextrafooding set posting='1' where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and tipekaryawan='".$param['tipekar']."'  and idkomponen='".$param['jenis']."'";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	
	break;
	case'unposting':
		try {
		$owlPDO->beginTransaction();
			$str = "select * from " . $dbname . ".sdm_5periodegaji where kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."' and sudahproses='1'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Periode gaji sudah ditutup.");
			}
		
			$str = "delete from " . $dbname . ".sdm_pendapatanlaindt where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and idkomponen='".$bar['idkomponen']."' and keterangan='Proses extra fooding'";
			$owlPDO->exec($str);
			
			$str = "update " . $dbname . ".sdm_uangmakandanextrafooding set posting='0' where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and tipekaryawan='".$param['tipekar']."'  and idkomponen='".$param['jenis']."'";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	
	break;
	case'delete':
		$str = "delete from " . $dbname . ".sdm_uangmakandanextrafooding where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and tipekaryawan='".$param['tipekar']."' and idkomponen='".$param['jenis']."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case"insert":
		try {
		$owlPDO->beginTransaction();
			if($param['kodeorg']==''){				
				throw new PDOException("Kode Organisasi tidak boleh kosong.");
			}
			if($param['periode']==''){				
				throw new PDOException("Periode tidak boleh kosong.");
			}
			$str = "select * from " . $dbname . ".sdm_5periodegaji where periode='" . $param['periode'] . "' and kodeorg='".$param['kodeorg']."' and sudahproses='1'"; 
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Periode gaji sudah ditutup.");
			}
		
			$arrtgl = explode("-",$param['tanggal']);
			$date = $arrtgl[2];
			
			if($date=='01'){
				$str = "delete from " . $dbname . ".sdm_uangmakandanextrafooding where kodeorg='".$param['kodeorg']."' and karyawanid='".$param['karyawanid']."' and periodegaji='".$param['periode']."' and idkomponen='".$param['idkomponen']."'";
				$owlPDO->exec($str);
			}
			
			$data = array();
			$data = array(
				'kodeorg'     => $param['kodeorg'],
				'periodegaji' => $param['periode'],
				'tanggal'     => $param['tanggal'],
				'hari'        => $param['namahari'],
				'karyawanid'  => $param['karyawanid'],
				'tipekaryawan'=> $param['tipekar'],
				'idkomponen'  => $param['idkomponen'],
				'absen'       => $param['absen'],
				'jam'         => $param['jamkerja'],
				'jumlah'      => $param['rupiah'],
				'updateby'    => $_SESSION['standard']['userid'],
				'updatetime'  => date("Y-m-d H:i:s")
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			
			$query = insertQuery($dbname,'sdm_uangmakandanextrafooding',$data,$cols);
			$owlPDO->exec($query);
			
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
    case'setharikary':
		$_SESSION['harikary'][$param['karyawanid']]=$param['hari'];
		
		
	break;
    case'simpanheader':
		$dataday=array();
		if($param['tipekar']==''){
			exit("Warning : Tipe karyawan wajib diisi.");
		}
		if($param['jenis']==''){
			exit("Warning : Jenis wajib diisi.");
		}
		
		$kodehari=array('sun'=>'0','mon'=>'1','tue'=>'2','wed'=>'3','thu'=>'4','fri'=>'5','sat'=>'6');
		
		$str = "select * from " . $dbname . ".sdm_uangmakandanextrafooding where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and tipekaryawan='".$param['tipekar']."' and idkomponen='".$param['jenis']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['posting']=='1'){
				exit("Warning : Data sudah diposting.");				
			}
			/* if(substr($bar['tanggal'],-2)=='01'){
				if($_SESSION['harikary'][$bar['karyawanid']]=='' and strtolower(date('D', strtotime($param['periode']."-01")))!=strtolower($bar['hari'])){					
					$_SESSION['harikary'][$bar['karyawanid']]=$kodehari[strtolower($bar['hari'])];
				}
			}
			 */
			$dataday[$bar['karyawanid']][$bar['tanggal']]=$bar['hari'];
		}
		
		$str = "select * from " . $dbname . ".sdm_5periodegaji where periode='" . $param['periode'] . "' and kodeorg='".$param['kodeorg']."'"; #exit('error'.$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$tgl1=$bar['tanggalmulai'];
			$tgl2=$bar['tanggalsampai'];
		}
		$rangetgl = rangeTanggal($tgl1,$tgl2);

        // $tab="<fieldset><legend>List Data</legend>";
		$tab.="<div class='table-scroll' style=height:60vh;clear:both;>
				<table border=0 cellpadding=5 cellspacing=1 class=sortable style=min-width:800px>
			<thead><tr class=rowheader>";
			
		$tab.=" <th align=center rowspan=2 width=20px>No</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['nik2']."</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['tipekaryawan']."</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['jabatan']."</th>
				<th align=center rowspan=2 >".$_SESSION['lang']['tipe']."</th>
				<th align=center colspan=".count($rangetgl).">".$_SESSION['lang']['tanggal'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['total'] . "</th>
				<th align=center colspan=1 rowspan=2>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr class=rowheader>";
			foreach($rangetgl as $tgl){
				$tab.="<th align=center>".substr($tgl,-2)."</th>";
			}
			$tab.="</tr>
			</thead><tbody>";
			
			$opttipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$param['kodeorg']."'");
			if($opttipe[$param['kodeorg']]=='PABRIK' and $param['periode']<'2020-07'){
				exit("Warning : Periode berlaku mulai 07-2020");
			}
			
			$wh="";
			if($param['tipekar']!=''){
				$wh="and b.tipekaryawan='".$param['tipekar']."'";
			}
			
			#kalau di pabrik semua karyawan dapat
			#selebihnya hanya yg terdaftar di sdm_5gajipokok yg dapat
			if($opttipe[$param['kodeorg']]!='PABRIK'){
				$wh.="and a.karyawanid in (select keterangan from " . $dbname . ".sdm_5gajipokok where tahun='" . substr($param['periode'],0,4) . "' and kodeorg='".$param['kodeorg']."' and idkomponen='".$param['jenis']."' and karyawanid ='0000000000')";
			}
			
			$no=0;$data=array();
			$str = "select * from " . $dbname . ".upload_absensi a 
			left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid 
			where a.kodeorg like '".$param['kodeorg']."%' and a.tanggalabsen between '".$tgl1."' and '".$tgl2."' ".$wh." order by namakaryawan asc";
			$res = fetchdata($str);
			foreach($res as $bar){
				$bar['tanggal']=$bar['tanggalabsen'];
				$data[$bar['karyawanid']]=$bar['karyawanid'];
				$absen[$bar['karyawanid']][$bar['tanggal']]=$bar['absensi'];
				$nik[$bar['karyawanid']]=$bar['nik'];
				$div[$bar['karyawanid']]=$bar['subbagian'];
				$tipe[$bar['karyawanid']]=$bar['tipekaryawan'];
				$jab[$bar['karyawanid']]=$bar['kodejabatan'];
				$nama[$bar['karyawanid']]=$bar['namakaryawan'];
				$masuk[$bar['karyawanid']][$bar['tanggal']]=substr($bar['jam'],-8,8);
				$pulang[$bar['karyawanid']][$bar['tanggal']]=substr($bar['jam4'],-8,8);//$bar['jam4'];
				$cektglplg[$bar['karyawanid']][$bar['tanggal']]=substr($bar['jam4'],0,10);
				$ist1[$bar['karyawanid']][$bar['tanggal']]=substr($bar['jam2'],-8,8);//$bar['jam2'];
				$ist2[$bar['karyawanid']][$bar['tanggal']]=substr($bar['jam3'],-8,8);//$bar['jam3'];
			}
			
			$str = "select * from " . $dbname . ".sdm_absensidt a 
			left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid 
			where a.kodeorg like '".$param['kodeorg']."%' and a.tanggal between '".$tgl1."' and '".$tgl2."' ".$wh." order by namakaryawan asc";
			$res = fetchdata($str);
			foreach($res as $bar){
				$data[$bar['karyawanid']]=$bar['karyawanid'];
				$absen[$bar['karyawanid']][$bar['tanggal']]=$bar['absensi'];
				$nik[$bar['karyawanid']]=$bar['nik'];
				$div[$bar['karyawanid']]=$bar['subbagian'];
				$tipe[$bar['karyawanid']]=$bar['tipekaryawan'];
				$jab[$bar['karyawanid']]=$bar['kodejabatan'];
				$nama[$bar['karyawanid']]=$bar['namakaryawan'];
				$masuk[$bar['karyawanid']][$bar['tanggal']]=$bar['jam'];
				$pulang[$bar['karyawanid']][$bar['tanggal']]=$bar['jamPlg'];
				$ist1[$bar['karyawanid']][$bar['tanggal']]=$bar['jamistirahatdari'];
				$ist2[$bar['karyawanid']][$bar['tanggal']]=$bar['jamistirahatsampai'];
			}
			
			// echo "<pre>";
			// print_r($absen);
			
			$idkomp=$param['jenis'];
			$str = "select * from " . $dbname . ".sdm_5gajipokok where tahun='" . substr($param['periode'],0,4) . "' and kodeorg='".$param['kodeorg']."' and idkomponen='".$idkomp."' and karyawanid ='0000000000'"; #exit('error'.$str);
			$res = fetchdata($str);
			if(count($res)==0){exit("Warning : Silahkan tambah rupiah uang makan melalui menu : SDM - Setup - Absensi dan Penggajian - Uang Makan dan Extra Fooding.");}
			foreach($res as $bar){
				$uangmakan1=$bar['jumlah'];
			}
			if(count($data)==0){
				exit("Warning : Data karyawan tidak ada.\n1. Pastikan setup sudah terisi dan,\n2. Pastikan absensi sudah diinput.");
			}
			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi like '".$param['kodeorg']."%'");
			$nmtipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
			$nmjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
			
			
			$tab.="<input hidden id=jumlahtgl value=".count($rangetgl).">";
			$tab.="<input hidden id=idkomponen value=".$idkomp.">";
			//$tab.="<button id=tomboldetail class=mybutton onclick=simpanall('".count($data)."')>" . $_SESSION['lang']['saveall'] . "</button>";

			#$arrhr=array('0'=>'sun','1'=>'mon','2'=>'tue','3'=>'wed','4'=>'thu','5'=>'fri','6'=>'sat');
			$arrhr=range(0,6);
			foreach($data as $karyid){
				$s = "select * from " . $dbname . ".sdm_5gajipokok where tahun='" . substr($param['periode'],0,4) . "' and kodeorg='".$param['kodeorg']."' and idkomponen='".$idkomp."' and karyawanid ='0000000000' and keterangan='".$karyid."'"; #exit('error'.$str);
				$r = fetchdata($s);
				if(count($r)>0){
					$uangmakan=$r[0]['jumlah'];
				}else{
					$uangmakan=$uangmakan1;
				}
				
				$no++;
				$tab.="<tr class=rowcontent id=baris".$no.">";
				$tab.="<td id=karyawanid".$no." hidden rowspan=4>".$karyid."</td>";
				$tab.="<td align=center rowspan=4>".$no."</td>";
				$tab.="<td rowspan=4>".$nik[$karyid]."</td>";
				#$tab.="<td rowspan=4>".$karyid."</td>";
				$tab.="<td rowspan=4>".$nama[$karyid]."</td>";
				$tab.="<td rowspan=4>".$nmtipe[$tipe[$karyid]]."</td>";
				$tab.="<td rowspan=4>".$nmjab[$jab[$karyid]]."</td>";

				$opthari[$karyid]="<option value=''></option>";	
				foreach($arrhr as $key){
					$tgl2 = date('Y-m-d', strtotime('+'.$key.' days', strtotime($param['periode'].'-01')));
					
					if($_SESSION['harikary'][$karyid]!=''){
						if($_SESSION['harikary'][$karyid]==$key){						
							$opthari[$karyid].="<option value=".$key." selected>".date('D', strtotime($tgl2))."</option>";
						}else{						
							$opthari[$karyid].="<option value=".$key." >".date('D', strtotime($tgl2))."</option>";
						}
					}else{
						if(strtolower(date('D', strtotime($tgl2)))==strtolower($dataday[$karyid][$param['periode'].'-01'])){
							$opthari[$karyid].="<option value=".$key." selected>".date('D', strtotime($tgl2))."</option>";
						}else{
							$opthari[$karyid].="<option value=".$key.">".date('D', strtotime($tgl2))."</option>";
						}
					}
				}
				
				$tab.="<td align=center><select id=hari".$no." onchange=\"setharikary('".$karyid."','".$param['periode']."','".$no."');\" style=\"width:50px;\">".$opthari[$karyid]."</select></td>";
				
				$n=0;
				foreach($rangetgl as $tgl){
					$n+=1;
					$cl="color:white;";
					if($_SESSION['harikary'][$karyid]!=''){
						$tgl2 = date('Y-m-d', strtotime('+'.$_SESSION['harikary'][$karyid].' days', strtotime($tgl)));
						$dataday[$karyid][$tgl]=date('D', strtotime($tgl2));
					}else if($dataday[$karyid][$tgl]==''){
						$dataday[$karyid][$tgl]=date('D', strtotime($tgl));
					}else{
						$dataday[$karyid][$tgl]=$dataday[$karyid][$tgl];
					}
					
					if(strtolower($dataday[$karyid][$tgl])=='sat'){
						$cl="color:yellow;";
					}elseif(strtolower($dataday[$karyid][$tgl])=='sun'){
						$cl="color:red;";
					}
					
					$tab.="<td align=center style=background-color:#275370;".$cl." id=namahari_".$no."_".$n.">".$dataday[$karyid][$tgl]."</td>";
				}
				
				$tab.="<td align=left></td>";
				$tab.="<td rowspan=4 align=center><img title=Simpan class=zImgBtn onclick=simpan('".$no."','".$no."','1') src=images/save.png></td>";
				$tab.="</tr>";
				
				
				$tab.="<tr class=rowcontent id=barisa".$no.">";
				$tab.="<td align=left>Absensi</td>";
				$n=0;
				foreach($rangetgl as $tgl){
					$n+=1;
					$tab.="<td align=center id=absen_".$no."_".$n.">" . $absen[$karyid][$tgl]."</td>";
				}
				$tab.="<td align=left></td>";
				
				
				
				$tab.="<tr class=rowcontent id=barisb".$no.">";
				$tab.="<td align=left>Jam Kerja</td>";
				$n=0;
				foreach($rangetgl as $tgl){
					$n+=1;
					$diffx = (strtotime($ist2[$karyid][$tgl])-strtotime($ist1[$karyid][$tgl]));
					$harix = floor($diffx/(60*60*24));
					$jamx  = floor(($diffx-($harix*(60*60*24)))/ (60 * 60));
					$menitx= floor(($diffx-(($harix*(60*60*24))+($jamx*(60*60))))/60);
					$jam=$menit='0';

					$diff = (strtotime($pulang[$karyid][$tgl])-strtotime($masuk[$karyid][$tgl]))-$diffx;
					$hari = floor($diff/(60*60*24));
					$jam  = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
					$menit= floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
					if($pulang[$karyid][$tgl]!='00:00:00'){						
					}
					
					$jamkerja=array();
					if($jam>0 or $menit>0){
						if($cektglplg[$karyid][$tgl]!=$tgl){
							if($jam==0){
								$jam=12;
							}
						}
						$jamkerja[$karyid][$tgl]=$jam.":".$menit;
					}
					
					$tab.="<td align=right ".$color." id=jamkerja_".$no."_".$n.">".$jamkerja[$karyid][$tgl]."</td>";
					$ttlrplbr[$karyid]+=$totaljam[$karyid][$tgl];
					$gtlrplbr[$tgl]+=$totaljam[$karyid][$tgl];
					$gtlrpalllbr+=$totaljam[$karyid][$tgl];
				}
				$tab.="<td align=right >".numb_format($ttlrplbr[$karyid])."</td>";
				$tab.="</tr>";
				
				
				$tab.="<tr class=rowcontent id=barisc".$no.">";
				$tab.="<td align=left>Rupiah</td>";
				$n=0;
				foreach($rangetgl as $tgl){
					$n+=1;
					$diffx=$harix=$jamx=$menitx=0;
					$diffx = (strtotime($ist2[$karyid][$tgl])-strtotime($ist1[$karyid][$tgl]));
					$harix = floor($diffx/(60*60*24));
					$jamx  = floor(($diffx-($harix*(60*60*24)))/ (60 * 60));
					$menitx= floor(($diffx-(($harix*(60*60*24))+($jamx*(60*60))))/60);
					$jam=$menit='0';

					$diff = (strtotime($pulang[$karyid][$tgl])-strtotime($masuk[$karyid][$tgl]))-$diffx;
					$hari = floor($diff/(60*60*24));
					$jam  = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
					$menit= floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);

					$jamkerja=array();
					if($pulang[$karyid][$tgl]!='00:00:00'){		
						if($cektglplg[$karyid][$tgl]!=$tgl){//jika beda tanggal maka dianggap 12
								if($jam==0){
									$jam=12;
								}
						}
						if($jam>0){
							$jamkerja[$karyid][$tgl]=$jam;
						}				
					}else{
						$jamkerja[$karyid][$tgl]=0;
					}
					
					
					
					$day = date('D', strtotime($tgl));					
					$rpum=0; 
					$color="style=background-color:grey;";
					$color="";
					switch ($param['jenis']){
						case'45':
						#uang makan / extra fooding
						if($opttipe[$param['kodeorg']]=='PABRIK'){
							#BERLAKU DI PABRIK
							if(strtolower($dataday[$karyid][$tgl])=='sat'){
								if($absen[$karyid][$tgl]=='H' and $jamkerja[$karyid][$tgl]>='8'){
									$rpum=$uangmakan; $color="";
								}
							}else if(strtolower($dataday[$karyid][$tgl])=='sun'){
								if(($absen[$karyid][$tgl]=='MG' or $absen[$karyid][$tgl]=='H') and $jamkerja[$karyid][$tgl]>='10'){
									$rpum=$uangmakan; $color="";
								}
							}else{
								$harilibur = getjenisharikerja($param['kodeorg'],$tgl);
								if($harilibur=='LIBUR' and $absen[$karyid][$tgl]=='LN' and $jamkerja[$karyid][$tgl]>='10'){
									$rpum=$uangmakan; $color="";
								}
								if($absen[$karyid][$tgl]=='H' and $jamkerja[$karyid][$tgl]>='10'){
									$rpum=$uangmakan; $color="";
								}
							}
							
						}else{
							#BERLAKU SELAIN DI PABRIK
							$kdabsdapat=array('H','IDT','HL');
							if(in_array($absen[$karyid][$tgl],$kdabsdapat)){
								if(strtolower($dataday[$karyid][$tgl])=='sat' and $jamkerja[$karyid][$tgl]>='2'){
									$rpum=$uangmakan; $color="";
								}else if(strtolower($dataday[$karyid][$tgl])!='sat' and $jamkerja[$karyid][$tgl]>='3'){
									$rpum=$uangmakan; $color="";
								}
							}
						}
						break;
						case'69':
						#transport
						if($opttipe[$param['kodeorg']]=='PABRIK'){
							#BERLAKU DI PABRIK
							if($absen[$karyid][$tgl]=='H'){
								$rpum=$uangmakan; $color="";
							}
						}else{
							#BERLAKU SELAIN DI PABRIK
							$kdabsdapat=array('H','IDT','HL');
							if(in_array($absen[$karyid][$tgl],$kdabsdapat)){
								if(strtolower($dataday[$karyid][$tgl])=='sat' and $jamkerja[$karyid][$tgl]>='2'){
									$rpum=$uangmakan; $color="";
								}else if(strtolower($dataday[$karyid][$tgl])!='sat' and $jamkerja[$karyid][$tgl]>='3'){
									$rpum=$uangmakan; $color="";
								}
							}
						}
						break;
					}
					
					
					$tab.="<td id=tanggal_".$no."_".$n." hidden>".$tgl."</td>";
					$tab.="<td align=right ".$color." id=rupiah_".$no."_".$n.">".numb_format($rpum)."</td>";
					$ttlrp[$karyid]+=$rpum;
					$gtlrp[$tgl]+=$rpum;
					$gtlrpall+=$rpum;
				}
				$tab.="<td align=right >".numb_format($ttlrp[$karyid])."</td>";
				$tab.="</tr>";
			}
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=6>T O T A L</td>";
			foreach($rangetgl as $tgl){					
				$tab.="<td align=right>".numb_format($gtlrp[$tgl])."</td>";
			}
			$tab.="<td align=right>".numb_format($gtlrpall)."</td>";
			$tab.="<td align=right></td>";
			
		$tab.="</table>";
		$tab.="</div>";
		$tab.="<div style=clear:both><button id=tomboldetail class=mybutton onclick=simpanall('".count($data)."')>" . $_SESSION['lang']['saveall'] . "</button></div>";
		echo $tab;
		
	break;
	

    case'loaddata':
		$_SESSION['harikary']=array();
		
		$where="";
		$lstUnit=getOrgDetail(1);
		$dtMul=0;
		$listOrg="";
		foreach($lstUnit as $row=>$isiDt){
			if(substr($row,0,5)=='Pilih'){
				continue;
			}
			if($dtMul==0){
				$listOrg="'".$row."'";
				$dtMul=1;
			}else{
				$listOrg.=",'".$row."'";
			}
		}


		$where.=" and a.kodeorg in (".$listOrg.")";
		
        if ($param['kodeorg'] != '') {
            $where.=" and a.kodeorg = '".$param['kodeorg']."' ";
        }
        if ($param['periode'] != '') {
            $where.=" and a.periodegaji = '".$param['periode']."' ";
        }
		if ($param['tipekar'] != '') {
            $where.=" and a.tipekaryawan = '".$param['tipekar']."' ";
        }
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
		$tab = "";
        $no = $maxdisplay;

        $sql = "select * from " . $dbname . ".sdm_uangmakandanextrafooding a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 " . $where . " group by kodeorg,periodegaji,a.tipekaryawan,idkomponen";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
		
        $str = "select a.tipekaryawan,kodeorg,periodegaji,a.updateby,posting,idkomponen,sum(jumlah) as jumlah from " . $dbname . ".sdm_uangmakandanextrafooding a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 " . $where . " group by kodeorg,periodegaji,a.tipekaryawan,idkomponen order by periodegaji desc limit " . $offset . "," . $limit . ""; 
		#exit('error'.$str);
        $res = fetchdata($str);
		$no=0;
		$nmjenis=makeOption($dbname,'sdm_ho_component','id,name');
		$nmtipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
        foreach($res as $bar){
			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi = '".$bar['kodeorg']."'");
			
            $no+=1;
            $tab.="<tr class=rowcontent style=height:23px; id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['kodeorg'] . " - ".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align=center>" . $bar['periodegaji'] . "</td>";
            $tab.="<td align=left>" . $nmtipe[$bar['tipekaryawan']] . "</td>";
            $tab.="<td align=left>" . $nmjenis[$bar['idkomponen']] . "</td>";
            $tab.="<td align=right>" . numb_format($bar['jumlah']) . "</td>";
            $tab.="<td align=center>" . @$nmkar[$bar['updateby']] . "</td>";
            
			$isi="";
            if ($bar['posting']==0) {
                $isi.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                    onclick=\"edit('".$bar['kodeorg']."','".$bar['periodegaji']."','".$bar['tipekaryawan']."','".$bar['idkomponen']."');\" ></td>";
					
                $isi.="<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                    onclick=\"del('".$bar['kodeorg']."','".$bar['periodegaji']."','".$bar['tipekaryawan']."','".$bar['idkomponen']."');\" ></td>";

				$isi.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['kodeorg']."','".$bar['periodegaji']."','".$bar['tipekaryawan']."','".$bar['idkomponen']."');\" ></td>";
            }else{				
				$isi.="<td style=width:20px></td><td style=width:20px></td>";
				$isi.="<td align=center style=width:20px><img src=images/icons/04/16/04.png class=zImgBtn class=zImgBtn height='30'  title='Unposting' onclick=\"unposting('".$bar['kodeorg']."','".$bar['periodegaji']."','".$bar['tipekaryawan']."','".$bar['idkomponen']."');\" ></td>";
			}
			
            $isi.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['kodeorg']."','".$bar['periodegaji']."','".$bar['tipekaryawan']."','".$bar['idkomponen']."','html');\" ></td>";
            $isi.="<td align=center style=width:20px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['kodeorg']."','".$bar['periodegaji']."','".$bar['tipekaryawan']."','".$bar['idkomponen']."','excel');\" ></td>";

            $tab.=$isi;

            $tab.="</tr>";
        }
		
		$footd = createpaging($jlhbrs,$limit,$page,12,'loaddata','getPage');
        echo $tab . "####" . $footd;

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