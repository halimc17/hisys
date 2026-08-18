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
$karyawanid=checkPostGet('karyawanid','');
$kodeunit=checkPostGet('kodeunit','');


$jambayar=checkPostGet('jambayar','');
$jampotong=checkPostGet('jampotong','');
$persenpotong=checkPostGet('persenpotong','');
$tglurut=checkPostGet('tglurut','');
if(strlen($tglurut)<2){
	$tglurut='0'.$tglurut;
}
$tgl=$per.'-'.$tglurut;

// echo $method;exit();
$stream="";

$jumlahhari=checkPostGet('jumlahhari','');
$potongan=checkPostGet('potongan','');
$cutiawal=checkPostGet('cutiawal','');
$potongancuti=checkPostGet('potongancuti','');
$pengali=checkPostGet('pengali','');
$sisacuti=checkPostGet('sisacuti','');










switch($method){
	
	
	case'simpan':
	
		#= delete
		$str = " delete from ".$dbname.".`sdm_rekapabsenho` where karyawanid='".$karyawanid."' and tanggal='".$tgl."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	
		$str = " INSERT INTO ".$dbname.".`sdm_rekapabsenho` 
			(`karyawanid`, `kodept`, `kodeunit`, `periode`,
			`tanggal`,`jambayar`,`jampotong`,`persenpotong`)
			values ('".$karyawanid."','".$pt."','".$kodeunit."','".$per."',
			'".$tgl."','".$jambayar."','".$jampotong."','".floatval($persenpotong)."')";
			 ///exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}

	
	break;
	
	
	case'preview':
		#= ambil tanggal periode gaji
		$str="SELECT tanggalmulai,tanggalsampai FROM ".$dbname.".sdm_5periodegaji where periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			
		$tgl1=$bar['tanggalmulai'];
		$tgl2=$bar['tanggalsampai'];
		#= ambil karyawan yg lokasi kanwil dan holding yang terdaftar di uang makan
		$str="select a.*,b.lokasitugas,b.kodeorganisasi,b.namakaryawan,b.nik from ".$dbname.".sdm_5gajipokokho a 
				left join ".$dbname.".datakaryawan b
				on a.karyawanid=b.karyawanid 
				where tahun = '".substr($per,0,4)."' and idkomponen='1' and kodeorganisasi='".$pt."' and
				(b.tanggalkeluar = '0000-00-00' or b.tanggalkeluar>= '" . $tgl1 . "')
				order by namakaryawan asc";
	    // echo $str;
	    // exit('warning');
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
			$namakaryawan[$bar['karyawanid']]=$bar['namakaryawan'];
			$nik[$bar['karyawanid']]=$bar['nik'];
			$jumlah[$bar['karyawanid']]=$bar['jumlah'];
			$lokasitugas[$bar['karyawanid']]=$bar['lokasitugas'];
			$lstData[$bar['karyawanid']]=$bar['lokasitugas'];
		}
		
		
			
			// $tgl1='2018-06-01';
			// $tgl2='2018-06-10';
		
		$dttgl=rangeTanggalarr($tgl1,$tgl2);
		$cdttgl=count($dttgl);
		
		
		#= ambil kerja dinas
		#= KARYAWAN DINAS
		$str="SELECT a.karyawanid, a.tanggalperjalanan, a.tanggalkembali, a.tujuan1, a.tujuan2, a.tujuan3,a.kodeorg FROM ".$dbname.".sdm_pjdinasht a
		WHERE (a.tanggalperjalanan between '".$tgl1."' and '".$tgl2."') or (a.tanggalkembali between  '".$tgl1."' and '".$tgl2."')
			order by a.tanggalperjalanan, a.tanggalkembali";
		/*
		str="SELECT a.karyawanid, a.tanggalperjalanan, a.tanggalkembali, a.tujuan1, a.tujuan2, a.tujuan3,a.kodeorg FROM ".$dbname.".sdm_pjdinasht a
		WHERE a.tanggalperjalanan <= '".$tgl2."' and a.tanggalkembali >= '".$tgl1."' 
				and statuspersetujuan='1' and statushrd='1'
			order by a.tanggalperjalanan, a.tanggalkembali";
		*/	
			// echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
				$dinaspergi[$bar['karyawanid']]=$bar['tanggalperjalanan'];
				$dinaspulang[$bar['karyawanid']]=$bar['tanggalkembali'];
				
		}
		
				// exit("Error:a");
		
		#= ambil jam kerja
		$str="SELECT * FROM ".$dbname.".sdm_5jamkerja";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jamkerja[$bar['kodeunit']][$bar['hari']]=$bar['jamkerja'];
			$jamistirahat[$bar['kodeunit']][$bar['hari']]=$bar['jamistirahat'];
			#$jamkerjaefektif[$bar['kodeunit']][$bar['hari']]=$bar['jamkerja']-$bar['jamistirahat'];
			$jammasukkerja[$bar['kodeunit']][$bar['hari']]=$bar['jammasuk'];
			$jamkeluarkerja[$bar['kodeunit']][$bar['hari']]=$bar['jamkeluar'];	
		}
		
		foreach($lstData as $key => $valUnit){
			foreach ($dttgl as $key2) {
				$sTipe="select * from ".$dbname.".organisasi where kodeorganisasi='".$valUnit."'";
				$rTipe=fetchData($sTipe);	
				if(($rTipe[0]['tipe']!='HOLDING')&&($rTipe[0]['tipe']!='KANWIL')){
					$hari=date('D', strtotime($key2));
					$sHrLbr="select * from ".$dbname.".sdm_5harilibur where tanggal='".$key2."' and (kebun='".$valUnit."' or kebun='GLOBAL') and keterangan='libur'";
					$rHrLbr=fetchData($sHrLbr);
					if(($hari!='Sun')||(count($rHrLbr)!=0)){
						$jammasuk[$key][$key2]=$jammasukkerja[$valUnit][$hari];
						$jamkeluar[$key][$key2]=$jamkeluarkerja[$valUnit][$hari];

						$jammasukhitung=$jammasuk[$key][$key2];
						$jamkeluarhitung=$jamkeluar[$key][$key2];

						#=rumus
						$jmmsk=substr($jammasukhitung,0,2);
							$mnmsk=substr($jammasukhitung,3,2);
						$jmsel=substr($jamkeluarhitung,0,2);
							$mnsel=substr($jamkeluarhitung,3,2);
						#= jam masuk yg diperhitungkan
						$htgselisih=selisihjam($jmmsk,$mnmsk,$jmsel,$mnsel);
						$selisihjam[$key][$key2]=$htgselisih;
					}else{
						$jammasuk[$key][$key2]="00:00";
						$jamkeluar[$key][$key2]="00:00";
						$selisihjam[$key][$key2]=0;
					}
					
				}
			}
		}

		
		
		// $jammasukbayar='08:30';
		// $jamkeluarbayar='17:30';
		
		
		// tanggalperjalanan
		// tanggalkembali
		
		
		
		// echo"<pre>";
		// print_r($pergi);
		// echo"</pre>";
		
		
		#= ambil absen karyawan
		$str="SELECT * FROM ".$dbname.".sdm_absensidt where tanggal like '".$per."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$sTipe="select * from ".$dbname.".organisasi where kodeorganisasi='".substr($bar['kodeorg'],0,4)."'";
			$rTipe=fetchData($sTipe);
			if(($rTipe[0]['tipe']=='HOLDING')||($rTipe[0]['tipe']=='KANWIL')){
				$optTj=makeOption($dbname,'sdm_5absensi','kodeabsen,kelompok',"kodeabsen='".$bar['absensi']."'");
				$kelompokabsensi[$bar['karyawanid']][$bar['tanggal']]=$optTj[$bar['absensi']];
				$absensi[$bar['karyawanid']][$bar['tanggal']]=$bar['absensi'];
				
				#= buat show
				$jammasukaktual=substr($bar['jam'],0,5);
				$jamkeluaraktual=substr($bar['jamPlg'],0,5);
				$jammasuk[$bar['karyawanid']][$bar['tanggal']]=$jammasukaktual;
				$jamkeluar[$bar['karyawanid']][$bar['tanggal']]=$jamkeluaraktual;
				
				#= hitung jam yg dibayar
				#= jika dia terlambat maka jam bayarnya mulai dari jam masuknya
				#= untuk jam pulang, jika pulang lebih awal maka itu sebagai jam pulang bayarnya
				#= contoh kasus | HO | jam masuk dr kantor : 08:30, jam pulang 17:30
				#= karyawan 1 : masuk : 08:00 pulang 19:00 => maka jam dibayarnya masuk 08:30 pulang 17:30
				#= karyawan 2 : masuuk 09:00 pulang 14:00 => maka jam dibayarnya 09:00 pulang 14:00
				$hari=date('D', strtotime($bar['tanggal']));
				$jammasukbayar=$jammasukkerja[$bar['kodeorg']][$hari];
				$jamkeluarbayar=$jamkeluarkerja[$bar['kodeorg']][$hari];
				
				if($jammasukaktual>$jammasukbayar){
					$jammasukhitung=$jammasukaktual;
				}else{
					$jammasukhitung=$jammasukbayar;
				}
				
				if($jamkeluaraktual>$jamkeluarbayar){ 
					$jamkeluarhitung=$jamkeluarbayar;
				}else{
					$jamkeluarhitung=$jamkeluaraktual; 
				}
				
				#= jam masuk hitung
				$jmmasuk[$bar['karyawanid']][$bar['tanggal']]=$jammasukhitung;
				$jmkeluar[$bar['karyawanid']][$bar['tanggal']]=$jamkeluarhitung;
				
				#=rumus
				$jmmsk=substr($jammasukhitung,0,2);
					$mnmsk=substr($jammasukhitung,3,2);
				$jmsel=substr($jamkeluarhitung,0,2);
					$mnsel=substr($jamkeluarhitung,3,2);
				#= jam masuk yg diperhitungkan
				$htgselisih=selisihjam($jmmsk,$mnmsk,$jmsel,$mnsel);
				$selisihjam[$bar['karyawanid']][$bar['tanggal']]=$htgselisih;
			}
		} 
		
		
		#= simpan jumlah hari
		$stream.= "<input hidden type=text id=jumhari class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:55px;\" value='".$cdttgl."'>";
		
		#= print data
		$stream.= "<table class=sortable cellspacing=1>";
		$stream.="<thead class=rowheader>
			<tr class=rowheader>";	
			
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>";  
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namakaryawan']."</td>";  
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nik']."</td>";  
		$stream.="<td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['unit']."</td>";  
		$stream.="<td colspan=".($cdttgl*5)." bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tanggal']."</td>";  
		// $stream.="<td colspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['potongan']."</td>";  

		$stream.="</tr>";  
		
		$stream.="<tr class=rowheader>";	
		foreach($dttgl as $tgl){
			// $stream.="<td>".tglnmbln($tgl,'I','short')."</td>"; 
			$hari=date('D', strtotime($tgl));
			$stream.="<td colspan=5 align=center>".intval(substr($tgl,8,2))." ( ".$hari." )</td>";
		}
		// $stream.="<td bgcolor=#CCCCCC align=center rowspan=2>".$_SESSION['lang']['hari']."</td>";  
		// $stream.="<td bgcolor=#CCCCCC align=center rowspan=2>".$_SESSION['lang']['rupiah']."</td>";  
			   
		$stream.="</tr>";
		
		$stream.="<tr class=rowheader>";	
		foreach($dttgl as $tgl){
			// $stream.="<td>".tglnmbln($tgl,'I','short')."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['waktu']."<br>".$_SESSION['lang']['absensi']."</td>";
			// $stream.="<td>Jam<br>Kerja</td>";
			$stream.="<td align=center>".$_SESSION['lang']['waktu']."<br>".$_SESSION['lang']['dibayar']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['jam']."<br>".$_SESSION['lang']['dibayar']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['jam']."<br>".$_SESSION['lang']['potong']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['jam']."<br>".$_SESSION['lang']['potong']."</td>";
		}
		$stream.="</tr>";
		
		
		$stream.="</thead>";
		
		if(isset($karyawanid))
			foreach ($karyawanid as $kar){
				
				@$no+=1;
				$stream.="<tr class=rowcontent id=row".$no.">";
				$stream.="<td>".@$no."</td>";
				$stream.="<td hidden id=karyawanid".$no.">".$kar."</td>";
				$stream.="<td>".$namakaryawan[$kar]."</td>";
				// $stream.="<td>".$nik[$kar]." ".$kar."</td>";
				$stream.="<td>".$nik[$kar]."</td>";
				$stream.="<td id=kodeunit".$no.">".$lokasitugas[$kar]."</td>";
				//".$tunjangan[$kar][$tgl]."
				foreach($dttgl as $tgl){
					
					/*
					$jammasuk[$kar][$tgl]='';
					$jamkeluar[$kar][$tgl]='';
					$jammasukkerja[$lokasitugas[$kar]][$hari]='';
					$jamkeluarkerja[$lokasitugas[$kar]][$hari]='';
					$jmmasuk[$kar][$tgl]='';
					$jmkeluar[$kar][$tgl]='';
					$jamkerjabayar[$kar][$tgl]='';
					$jamkerjapotong[$kar][$tgl]='';
					$persentasejamkerja[$kar][$tgl]='';
					*/
				
					$hari=date('D', strtotime($tgl));
					
					#= jam dibayar dan jam tidak dibayar
					@$jamkerjabayar[$kar][$tgl]=$selisihjam[$kar][$tgl]-$jamistirahat[$lokasitugas[$kar]][$hari];
					if($selisihjam[$kar][$tgl]==''){
						$jamkerjabayar[$kar][$tgl]=0;
					}
					@$jamkerjapotong[$kar][$tgl]=$jamkerja[$lokasitugas[$kar]][$hari]-$jamkerjabayar[$kar][$tgl];
					$persentasejamkerja[$kar][$tgl]=0;
					if($jamkerjapotong[$kar][$tgl]>0){
						@$persentasejamkerja[$kar][$tgl]=$jamkerjapotong[$kar][$tgl]/$jamkerja[$lokasitugas[$kar]][$hari];
					}
					
					
					#= cek dinas
					if($dinaspergi[$kar]!=''){
						if(($dinaspergi[$kar]<=$tgl)&&($dinaspulang[$kar]>=$tgl)){
							if($jammasukkerja[$lokasitugas[$kar]][$hari]!=''){
								$jammasuk[$kar][$tgl]='Dinas';
								$jamkeluar[$kar][$tgl]='';
								$jmmasuk[$kar][$tgl]='Dinas';
								$jmkeluar[$kar][$tgl]='';
								$jamkerjabayar[$kar][$tgl]=$jamkerja[$lokasitugas[$kar]][$hari];
								$jamkerjapotong[$kar][$tgl]=0;
								$persentasejamkerja[$kar][$tgl]=0;
							}
						}
					}
					
					#= jika mangkir
					if($kelompokabsensi[$kar][$tgl]=='0'){
						$jammasuk[$kar][$tgl]=$absensi[$kar][$tgl];
						$jamkeluar[$kar][$tgl]='';
						$jmmasuk[$kar][$tgl]=$absensi[$kar][$tgl];
						$jmkeluar[$kar][$tgl]='';
						$jamkerjabayar[$kar][$tgl]=0;
						$jamkerjapotong[$kar][$tgl]=$jamkerja[$lokasitugas[$kar]][$hari];
						$persentasejamkerja[$kar][$tgl]=1;
					}
					
					 
				
					$stream.="<td align=center id=waktuabsen".$no."#".intval(substr($tgl,8,2)).">".$jammasuk[$kar][$tgl]."<br>".$jamkeluar[$kar][$tgl]."</td>";
					// $stream.="<td align=right>".$jammasukkerja[$lokasitugas[$kar]][$hari]."<br>".$jamkeluarkerja[$lokasitugas[$kar]][$hari]."</td>";
					$stream.="<td align=center id=waktubayar".$no."#".intval(substr($tgl,8,2)).">".$jmmasuk[$kar][$tgl]."<br>".$jmkeluar[$kar][$tgl]."</td>";
					
					$stream.="<td align=right id=jambayar".$no."#".intval(substr($tgl,8,2)).">".$jamkerjabayar[$kar][$tgl]."</td>";
					$stream.="<td align=right id=jampotong".$no."#".intval(substr($tgl,8,2)).">".$jamkerjapotong[$kar][$tgl]."</td>";
					$stream.="<td align=right id=persenpotong".$no."#".intval(substr($tgl,8,2)).">".$persentasejamkerja[$kar][$tgl]."</td>";
					@$ttunjangan[$kar]+=$tunjangan[$kar][$tgl];
				}		
				// $stream.="<td align=right id=hrjumlah".$no.">".$ttunjangan[$kar]."</td>";		
				// $stream.="<td align=right id=rpjumlah".$no.">".number_format($ttunjangan[$kar]*$jumlah[$kar])."</td>";			
				$stream.="</tr>";  				
			}	
			$stream.="<button class=mybutton onclick=saveall(".$no.");>".$_SESSION['lang']['proses']."</button><br><br>";
		$stream.="</tbody></table>";

		echo $stream;
	
	break;
	
	
	######################################################################################################################################
	######################################################################################################################################
	######################################################################################################################################
	######################################################################################################################################
	######################################################################################################################################
	
	case'previewv':
	
		#= ambil karyawan yg lokasi kanwil dan holding yang terdaftar di uang makan
		$str="select a.*,b.namakaryawan,b.nik from ".$dbname.".sdm_rekapabsenho a 
				left join ".$dbname.".datakaryawan b
				on a.karyawanid=b.karyawanid 
				where periode = '".$per."' and kodeorganisasi='".$pt."'
				order by namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
			$namakaryawan[$bar['karyawanid']]=$bar['namakaryawan'];
			$nik[$bar['karyawanid']]=$bar['nik'];
			$unit[$bar['karyawanid']]=$bar['kodeunit'];
			@$tpotong[$bar['karyawanid']]+=$bar['persenpotong'];
		}
		
		#= ambil sisa cuti
		$str="select a.*,b.namakaryawan,b.nik from ".$dbname.".sdm_cutiht a 
				left join ".$dbname.".datakaryawan b
				on a.karyawanid=b.karyawanid 
				where periodecuti = '".substr($per,0,4)."' and kodeorganisasi='".$pt."'
				order by namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$cuti[$bar['karyawanid']]=$bar['sisa'];
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
		
		#= simpan jumlah hari
		$stream.= "<input hidden type=text id=jumhari class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:55px;\" value='".$cdttgl."'>";
		
		#= print data	
		$stream.= "<table class=sortable cellspacing=1>";
		$stream.="<thead class=rowheader><tr class=rowheader>";	
			
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>";  
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namakaryawan']."</td>";  
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nik']."</td>";  
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['unit']."</td>";  
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['hari']."</td>";  
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['potongan']."</td>";  
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['cuti']." ".$_SESSION['lang']['awal']."</td>";  
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['potongan']." - ".$_SESSION['lang']['cuti']."</td>";  
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['pengali']."</td>"; 
			$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['sisa']." ".$_SESSION['lang']['cuti']."</td>"; 

		$stream.="</tr>";  
		$stream.="</thead>";
		if(!empty($karyawanid)){
			foreach($karyawanid as $kar){
				@$no+=1;
					$stream.="<tr class=rowcontent id=row".$no.">";
					$stream.="<td align=center>".$no."</td>";	
					$stream.="<td hidden id=karyawanid".$no.">".$kar."</td>";
					$stream.="<td>".$namakaryawan[$kar]."</td>";	
					$stream.="<td>".$nik[$kar]."</td>";	
					$stream.="<td id=kodeunit".$no.">".$unit[$kar]."</td>";	
					$stream.="<td id=jumlahhari".$no.">".$cdttgl."</td>";	
					$stream.="<td id=potongan".$no.">".$tpotong[$kar]."</td>";	
					$stream.="<td id=cutiawal".$no.">".$cuti[$kar]."</td>";
						$potongcuti[$kar]=$cuti[$kar]-$tpotong[$kar];
					$stream.="<td id=potongancuti".$no.">".$potongcuti[$kar]."</td>";	
					if($potongcuti[$kar]<0){
						$pengali[$kar]=($cdttgl-($potongcuti[$kar]*-1))/$cdttgl;
						$sisacuti[$kar]=0;
					}else{
						$pengali[$kar]=1;
						$sisacuti[$kar]=$potongcuti[$kar];
					}
					$stream.="<td id=pengali".$no.">".$pengali[$kar]."</td>";	
					$stream.="<td id=sisacuti".$no.">".$sisacuti[$kar]."</td>";	
				$stream.="</tr>";  
			}
			$stream.="<button class=mybutton onclick=saveallv(".$no.");>".$_SESSION['lang']['proses']."</button><br><br>";
		}else{
			$stream.="<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['kosong']."</td></tr>";
		}
		
		echo $stream;
		
	break;
	
	
	
	
	case'simpanv':
	
		#= delete
		$str = " delete from ".$dbname.".`sdm_rekapabsenhobulanan` where karyawanid='".$karyawanid."' and periode='".$per."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	
		$str = " INSERT INTO ".$dbname.".`sdm_rekapabsenhobulanan` 
			(`karyawanid`, `kodept`, `kodeunit`, `periode`,`jumlahhari`,
			`potongan`,`cutiawal`,`potongancuti`,`pengali`,`sisacuti`)
			values ('".$karyawanid."','".$pt."','".$kodeunit."','".$per."','".$jumlahhari."',
			'".$potongan."','".floatval($cutiawal)."','".$potongancuti."','".$pengali."','".$sisacuti."')";
			// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}

	
	break;
	

	
	
	default:
	break;
}



?>