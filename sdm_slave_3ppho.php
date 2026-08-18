<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');



$method=checkPostGet('method','');
$pt=checkPostGet('pt','');
$per=checkPostGet('per','');
$thn=substr($per,0,4);
$kom=checkPostGet('kom','');
$karyawanid=checkPostGet('karyawanid','');
$kodeunit=checkPostGet('kodeunit','');
$rpawal=checkPostGet('rpawal','');
$hrjumlah=checkPostGet('hrjumlah','');
$rpjumlah=checkPostGet('rpjumlah','');


$stream="";

		
switch($method){
	
	case'preview':
		#= ambil tanggal periode gaji
		$str="SELECT tanggalmulai,tanggalsampai FROM ".$dbname.".sdm_5periodegaji where periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tgl1=$bar['tanggalmulai'];
			$tgl2=$bar['tanggalsampai'];
		$karyawanid=array();
		
		#= ambil karyawan yg lokasi kanwil dan holding yang terdaftar di uang makan  
		$str="select a.*,b.lokasitugas,b.kodeorganisasi,b.namakaryawan,b.nik from ".$dbname.".sdm_5gajipokokho a 
		left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where tahun = '".substr($per,0,4)."' and idkomponen='".$kom."' and kodeorganisasi='".$pt."'
		and (b.tanggalkeluar = '0000-00-00' or b.tanggalkeluar>= '" . $tgl1 . "')
		order by namakaryawan asc";
		// echo $str;
		// exit('Warning');
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
			$namakaryawan[$bar['karyawanid']]=$bar['namakaryawan'];
			$nik[$bar['karyawanid']]=$bar['nik'];
			$jumlah[$bar['karyawanid']]=$bar['jumlah'];
			$lokasitugas[$bar['karyawanid']]=$bar['lokasitugas'];
		}
		
		
	
		@$jumkardapat=count($karyawanid);
		if($jumkardapat<1){
			exit("Warning:Tidak ada karyawan yang mendapatkan \n Silahkan cek di-setup penggajian ho");
		}
		
		#= ambil jam kerja
		$str="SELECT * FROM ".$dbname.".sdm_5jamkerja";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jamkerja[$bar['kodeunit']][$bar['hari']]=$bar['jamkerja'];
			$jamkerjatunjangan[$bar['kodeunit']][$bar['hari']]=$bar['jamkerja'];
		}	
		
		
		
		$dttgl=rangeTanggalarr($tgl1,$tgl2);
		$cdttgl=count($dttgl);
		

		#= ambil tanggal periode gaji
		// $str="SELECT * FROM ".$dbname.".sdm_absensidt where tanggal like '".$per."%'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// $optTj=makeOption($dbname,'sdm_5absensi','kodeabsen,pengali',"kodeabsen='".$bar['absensi']."'");
			// $tunjangan[$bar['karyawanid']][$bar['tanggal']]=$optTj[$bar['absensi']];
		// } 
		$str="SELECT * FROM ".$dbname.".sdm_rekapabsenho where periode like '".$per."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jambayar[$bar['karyawanid']][$bar['tanggal']]=$bar['jambayar'];
		} 
		
		
		
		
		
		$stream.= "<table class=sortable cellspacing=1>";
		$stream.="<thead class=rowheader>
			<tr class=rowheader>";	
			
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>";  
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namakaryawan']."</td>";  
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nik']."</td>";  
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['unit']."</td>";  
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rupiah']."<br>/<br>hari</td>";  
		$stream.="<td colspan=".($cdttgl*3)." bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tanggal']."</td>";  
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['rupiah']."</td>";  

		$stream.="</tr>";  
		$stream.="<tr class=rowheader>";	
		foreach($dttgl as $tgl){
			$hari=date('D', strtotime($tgl));
			// $stream.="<td>".tglnmbln($tgl,'I','short')."</td>";
			$stream.="<td colspan=3 align=center>".intval(substr($tgl,8,2))."<br>".$hari."</td>";
		}
		// $stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['hari']."</td>";  
		// $stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rupiah']."</td>";  
			   
		$stream.="</tr>";
		
		foreach($dttgl as $tgl){
			// $stream.="<td>".tglnmbln($tgl,'I','short')."</td>";
			$stream.="<td align=center>Jam<br>Absen</td>";
			$stream.="<td align=center>Jam<br>Bayar</td>";
			$stream.="<td align=center>Rp</td>";
		}
		
		$stream.="</thead>";
		
		
	
		
		if(isset($karyawanid))
			foreach ($karyawanid as $kar){
				$sTipe="select * from ".$dbname.".organisasi where kodeorganisasi='".$lokasitugas[$kar]."'";
				$rTipe=fetchData($sTipe);

					if(($rTipe[0]['tipe']!='HOLDING')||($rTipe[0]['tipe']!='KANWIL')){
						if($kom!='84'){
							continue;
						}
					}
						$no+=1;
						$stream.="<tr class=rowcontent id=row".$no.">";
						$stream.="<td>".@$no."</td>";
						$stream.="<td hidden id=karyawanid".$no.">".$kar."</td>";
						$stream.="<td>".$namakaryawan[$kar]."</td>";
						$stream.="<td>".$nik[$kar]." ".$kar."</td>";
						$stream.="<td id=kodeunit".$no.">".$lokasitugas[$kar]."</td>";
						$stream.="<td id=rpawal".$no.">".number_format($jumlah[$kar])."</td>";
						foreach($dttgl as $tgl){
							$hari=date('D', strtotime($tgl));
							$stream.="<td align=right>".$jambayar[$kar][$tgl]."</td>";
							$stream.="<td align=right>".$jamkerjatunjangan[$lokasitugas[$kar]][$hari]."</td>";
							@$rpbayar[$kar][$tgl]=0;
							if($jambayar[$kar][$tgl]>0){
								@$rpbayar[$kar][$tgl]=$jambayar[$kar][$tgl]/$jamkerjatunjangan[$lokasitugas[$kar]][$hari]*$jumlah[$kar];	
							}
							if($hari=='Sun'){
								@$rpbayar[$kar][$tgl]=$jumlah[$kar];		
							}
							
							$stream.="<td align=right>".$rpbayar[$kar][$tgl]."</td>";
							$totalrp[$kar]+=$rpbayar[$kar][$tgl];
						}		
						$stream.="<td hidden align=right id=hrjumlah".$no.">0</td>";		
						$stream.="<td align=right id=rpjumlah".$no.">".number_format($totalrp[$kar],2)."</td>";				
						$stream.="</tr>";  	

			}	
			$stream.="<button class=mybutton onclick=saveall(".$no.");>".$_SESSION['lang']['proses']."</button><br><br>";
		$stream.="</tbody></table>";

		echo $stream;
	
	break;
	
	
	
	
	#= tab tidak tetap
	case'previewv':
		#= ambil karyawan yg lokasi kanwil dan holding yang terdaftar di uang makan
		$str="select a.*,b.lokasitugas,b.kodeorganisasi,b.namakaryawan,b.nik from ".$dbname.".sdm_5gajipokokho a 
		left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where tahun = '".substr($per,0,4)."' and idkomponen='".$kom."'  and kodeorganisasi='".$pt."'
		and (b.tanggalkeluar = '0000-00-00' or b.tanggalkeluar>= '" . date("Y-m-d") . "')
		order by namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
			$namakaryawan[$bar['karyawanid']]=$bar['namakaryawan'];
			$nik[$bar['karyawanid']]=$bar['nik'];
			$jumlah[$bar['karyawanid']]=$bar['jumlah'];
			$lokasitugas[$bar['karyawanid']]=$bar['lokasitugas'];
		}
		
		#= ambil tanggal periode gaji
		$str="SELECT tanggalmulai,tanggalsampai FROM ".$dbname.".sdm_5periodegaji where periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tgl1=$bar['tanggalmulai'];
			$tgl2=$bar['tanggalsampai'];
		
		$dttgl=rangeTanggalarr($tgl1,$tgl2);
		$cdttgl=count($dttgl);
		
		/*
		#= ambil tanggal periode gaji
		$str="SELECT * FROM ".$dbname.".sdm_absensidt where tanggal like '".$per."%' and 
				absensi in (select kodeabsen from ".$dbname.".sdm_5absensi where kelompok=0)";;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			// $optTj=makeOption($dbname,'sdm_5absensi','kodeabsen,pengali',"kodeabsen='".$bar['absensi']."'");
			$tunjangan[$bar['karyawanid']][$bar['tanggal']]=1;
		} 
		*/
		
		#= data absen potongan
		$str="SELECT * FROM ".$dbname.".sdm_rekapabsenho where periode like '".$per."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$persenpotong[$bar['karyawanid']][$bar['tanggal']]=$bar['persenpotong'];
		} 
		
		#= cari sisa cuti
		#= data absen potongan
		$str="SELECT * FROM ".$dbname.".sdm_cutiht where periodecuti like '".$thn."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$cuti[$bar['karyawanid']]=$bar['sisa'];
		} 
		
		$stream.= "<table class=sortable cellspacing=1 >";
		$stream.="<thead><tr class=rowheader>";	
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namakaryawan']."</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nik']."</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['unit']."</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rupiah']."<br>".$_SESSION['lang']['tunjangan']."</td>";  
		// $stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rupiah']."<br>Per<br>Hari</td>";  
		$stream.="<td colspan=".$cdttgl." bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tanggal']."</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['sisa']."<br>".$_SESSION['lang']['cuti']."</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['potongan']."<br>".$_SESSION['lang']['cuti']."</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['sisa']."<br>".$_SESSION['lang']['cuti']."<br>".$_SESSION['lang']['dengan']."<br>".$_SESSION['lang']['potongan']."</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>%</td>";  
		$stream.="<td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rupiah']."</td>";  

		$stream.="</tr>";  
		$stream.="<tr class=rowheader>";	
		foreach($dttgl as $tgl){
			// $stream.="<td>".tglnmbln($tgl,'I','short')."</td>";
			$hari=date('D', strtotime($tgl));
			$stream.="<td align=center>".intval(substr($tgl,8,2))."<br>".$hari."</td>";
		}
		
			   
		$stream.="</tr>";
		$stream.="</thead>";
		
		if(isset($karyawanid))
			foreach ($karyawanid as $kar){
				$no+=1;
				$stream.="<tr class=rowcontent id=row".$no.">";
				$stream.="<td>".@$no."</td>";
				$stream.="<td hidden id=karyawanid".$no.">".$kar."</td>";
				$stream.="<td>".$namakaryawan[$kar]."</td>";
				$stream.="<td>".$nik[$kar]."</td>";
				$stream.="<td id=kodeunit".$no.">".$lokasitugas[$kar]."</td>";
				$stream.="<td id=rpawal".$no.">".number_format($jumlah[$kar])."</td>";
				// $rpawalhari[$kar]=$jumlah[$kar]/$cdttgl;
				// $stream.="<td>".number_format($rpawalhari[$kar])."</td>";
				foreach($dttgl as $tgl){
					if($persenpotong[$kar][$tgl]==0){
						$stream.="<td align=center></td>";
					}else{
						// $stream.="<td align=center>".number_format($persenpotong[$kar][$tgl],2)."</td>";
						$stream.="<td align=center>".$persenpotong[$kar][$tgl]."</td>";
					}
					
					@$tpersenpotong[$kar]+=$persenpotong[$kar][$tgl];
				}		
				#= hari masuk = total hari - hari mangkir
				$stream.="<td align=right>".number_format($cuti[$kar],2)."</td>";
				$stream.="<td align=right id=hrjumlah".$no.">".number_format($tpersenpotong[$kar],2)."</td>";	
				
				$sisacuti[$kar]=$cuti[$kar]-$tpersenpotong[$kar];
				$stream.="<td align=right>".number_format($sisacuti[$kar],2)."</td>";
				
				if($sisacuti[$kar]>0){
					$persentase[$kar]=1;
				}else{
					$persentase[$kar]=(($cdttgl-abs($sisacuti[$kar]))/$cdttgl);
				}
				
				$stream.="<td align=right>".number_format($persentase[$kar],2)."</td>";	
				
				$rpjumlah[$kar]=$persentase[$kar]*$jumlah[$kar];

				$stream.="<td align=right id=rpjumlah".$no.">".number_format($rpjumlah[$kar])."</td>";			
				$stream.="</tr>";  				
			}	
			$stream.="<button class=mybutton onclick=saveallv(".$no.");>".$_SESSION['lang']['proses']."</button><br><br>";
		$stream.="</tbody></table>";

		echo $stream;
	
	break;

	
	case'simpan':
	
		#= delete
		$str="delete from ".$dbname.".sdm_pendapatanho where kodeunit='".$kodeunit."' and kodept='".$pt."'
			and karyawanid='".$karyawanid."' and periode='".$per."' and idkomponen='".$kom."'";
			
				try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		if($rpjumlah>0){
			#= insert jika rupiah dapat > 0
			$str="insert into ".$dbname.".sdm_pendapatanho (`kodept`,`kodeunit`,`periode`,`karyawanid`,`idkomponen`,
					`rpawal`,`hrjumlah`,`rpjumlah`,`updateby`)
					values ('".$pt."','".$kodeunit."','".$per."','".$karyawanid."','".$kom."',
					'".$rpawal."','".$hrjumlah."','".$rpjumlah."','".$_SESSION['standard']['userid']."')";
					// exit("Error:$str");
			try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
	
	break;
	
	default:
	break;
}



?>