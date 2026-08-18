<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses        =checkPostGet('proses','');
$per           =checkPostGet('per','');
$unit          =checkPostGet('unit','');
$karyawanid    =checkPostGet('karyawanid','');
$tipe          =checkPostGet('tipe','');
$tjpph21x      =checkPostGet('tjpph21x','');
$istjpph21     =checkPostGet('istjpph21','');
$nilaitjpph21  =checkPostGet('nilaitjpph21','');
$baris         =checkPostGet('baris','');

$pph21x= str_replace(',','', checkPostGet('pph21x',''));
$tjpph21x= str_replace(',','', checkPostGet('tjpph21x',''));

switch($proses){
	case'prosespph':
		try {
		$owlPDO->beginTransaction();
		
		$str="select distinct(sudahproses) from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' and periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$aktifperiodegaji=$bar['sudahproses'];

		if($aktifperiodegaji==0){
			throw new PDOException('Periode gaji '.$per.' belum ditutup.');
		}
		
		$str="select distinct(tutupbuku) from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$aktifperiodeacct=$bar['tutupbuku'];
		if($aktifperiodeacct==1){
			throw new PDOException('Periode akutansi '.$per.' sudah ditutup.');
		}

		if($baris=='1'){			
			$str = "delete from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='42' and periodegaji='".$per."'";
			$owlPDO->exec($str);

			$str = "delete from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='153' and periodegaji='".$per."'";
			$owlPDO->exec($str);

			$str = "delete from " . $dbname . ".sdm_pph21 where kodeorg='" . $unit . "' and periode='".$per."'";
			$owlPDO->exec($str);
		}
		if($pph21x != 0){
            $str = "insert into " . $dbname . ".sdm_gaji (`karyawanid`,`kodeorg`,`idkomponen`,`jumlah`,`pengali`,`hk`,`periodegaji`) values ('".$karyawanid."','".$unit."','42','".$pph21x."','1','0','".$per."') ";
			$owlPDO->exec($str);
            
			$str = "insert into " . $dbname . ".sdm_pph21 (`karyawanid`,`kodeorg`,`tipepph`,`nilai`,`periode`) values ('".$karyawanid."','".$unit."','1','".$pph21x."','".$per."') ";
			$owlPDO->exec($str);
		}
        
		if($tjpph21x != 0){
            $str = "insert into " . $dbname . ".sdm_gaji (`karyawanid`,`kodeorg`,`idkomponen`,`jumlah`,`pengali`,`hk`,`periodegaji`) values ('".$karyawanid."','".$unit."','153','".$tjpph21x."','1','0','".$per."') ";
			$owlPDO->exec($str);
		}

		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		
	break;
	
    case 'preview':

		## Cek udah ada proses gaji nya belum
		$sCount = "select count(*) as jmlhrow from ".$dbname.".sdm_gaji where periodegaji = '".$per."' and kodeorg = '".$unit."'";
		$qCount = $owlPDO->query($sCount) or die(print " Gagal: " . PDOException::getMessage());
		$qCount->setFetchMode(PDO::FETCH_OBJ);
		while ($rCount = $qCount->fetch()) {
			$jmlbrs = $rCount->jmlhrow;
		}

		if($jmlbrs == 0){
			exit("Warning : Data gaji tidak ada untuk periode ".$per." di unit ".$unit.", Silahkan proses gaji dan tutup periode penggajian...");
		}

		$str="select * from ".$dbname.".sdm_ho_pph21jabatan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$persenjabatan=$bar['persen'];
			@$maxjabatan=$bar['max'];
			
		$str="select id,status,ptkp from ".$dbname.".sdm_5ptkp_pph21";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$ptkpdata[$bar['status']]=$bar['ptkp'];
		} 	

		$str="select level,percent,upto,penambah from ".$dbname.".sdm_ho_pph21_kontribusi order by level";
		$urut=0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$pphtarif[$urut]    =$bar['upto'];
			$pphpenambah[$urut] =$bar['penambah'];
			$pphpercent[$urut]  =$bar['percent'];    
			$urut+=1;  
		}    


		$dakarbulanan=0;
		$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$per."' and statuspajak <> '' and tipekaryawan !=0 "; 
		$res = fetchdata($str);
		if(count($res)>0){ 
			$dakarbulanan=1;
		}

		if($dakarbulanan==1){
			$strawal = "select * from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$per."' and statuspajak <> ''  and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and tipekaryawan !=0"; 
		}else{
			$strawal="select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and statuspajak <> '' and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and tipekaryawan !=0 ";
		}

		$datakosongx=0;
		$dtkomplus=array();
		$dttjtetap=array();

		$dtkomin=array();
		$rupiah=array();

		$expph='';
		#= buat komponen yang tidak masuk perhitungan pph dan komponen netto
		$str="select id from ".$dbname.".sdm_ho_component where pph21='0'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($expph==''){
				$expph="'".$bar['id']."'";
			}else{
				$expph.=",'".$bar['id']."'";
			}
		} 	


		$bulan = substr($per, 5, 2);
		$tahunberjalan=substr($per,0,4);   

		if($bulan == '12'){
			## DT KOMPLUS AND DT KOMMIN
			$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen not in (".$expph.") and (periodegaji <= '".$per."' and periodegaji >= '".$tahunberjalan."-01"."') and kodeorg='".$unit."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					if($bar['plus']==1){
						$dtkomplus[$bar['idkomponen']]=$bar['idkomponen'];	
						$rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
					}else{
						$dtkomin[$bar['idkomponen']]=$bar['idkomponen'];	
						$rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
					}
				}

			## AMBIL PPH 21 JAN - NOV
			$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen = '42' and (periodegaji <= '".$tahunberjalan."-11"."' and periodegaji >= '".$tahunberjalan."-01"."') and kodeorg='".$unit."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$pph21_jan_nov[$bar['karyawanid']]+=$bar['jumlah'];
				}
		}else{
			## DT KOMPLUS AND DT KOMMIN
			$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen not in (".$expph.") and idkomponen != '5' and periodegaji = '".$per."' and kodeorg='".$unit."' ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					if($bar['plus']==1){
						$dtkomplus[$bar['idkomponen']]=$bar['idkomponen'];	
						$rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
					}else{
						$dtkomin[$bar['idkomponen']]=$bar['idkomponen'];	
						$rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
					}
				}
		}
		

		## Make Option
		$nmgolongan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$nmjabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
		$optKategori=  makeOption($dbname, 'sdm_5tarifpph21', 'status,kategori');

		$resawal=$owlPDO->query($strawal) or die(print " Gagal: ".PDOException::getMessage());
		$resawal->setFetchMode(PDO::FETCH_ASSOC);
		while($barawal=$resawal->fetch()){
			$dtkarid[$barawal['karyawanid']]		=$barawal['karyawanid'];
			$nik[$barawal['karyawanid']]			=$barawal['nik'];
			$nmkar[$barawal['karyawanid']]			=$barawal['namakaryawan'];
			$golongan[$barawal['karyawanid']]		=$barawal['kodegolongan'];
			$statuspajak[$barawal['karyawanid']]	=$barawal['statuspajak'];
			$bagian[$barawal['karyawanid']]			=$barawal['bagian'];
			$jabatan[$barawal['karyawanid']]		=$barawal['kodejabatan'];	
			$ptkpnya[$barawal['karyawanid']]		=$ptkpdata[$barawal['statuspajak']];	
		}

		if ($proses == 'excel') {
			$stream.= "<table class=sortable cellspacing=1 border=1>";
		} else {
			$stream.= "<div class='table-scroll'><table class=sortable cellspacing=1 cellpadding=5 style='width:100%;'>";
		}


		$style_upper = "style='text-transform: uppercase;'";
		$count_dtkomplus= count($dtkomplus);
		$count_dtkommin= count($dtkomin);

		$stream.="<thead>";
			$stream.="<tr class=rowcontent>";
				$stream.="<th align=center ".$style_upper." rowspan=3>NO</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3> NIK </th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['namakaryawan']."</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>Golongan</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['statuspajak']."</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['bagian']."</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['jabatan']."</th>";
				$stream.="<th align=center ".$style_upper." colspan=".$count_dtkomplus.">PENDAPATAN</th>";
				
				if($bulan == '12'){
					$stream.="<th align=center ".$style_upper." rowspan=3>GAJI BRUTO</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>TJ PPh 21</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>BIAYA JABATAN</th>";

				}else{
					$stream.="<th align=center ".$style_upper." rowspan=3>TOTAL PENDAPATAN</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>TJ PAJAK</th>";

				}

				if($count_dtkommin > 0){
					$stream.="<th align=center ".$style_upper." colspan=".$count_dtkommin.">PENGURANG</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>TOTAL PENGURANG</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>TOTAL GAJI NETTO</th>";
				}

				if($bulan == '12'){

					$stream.="<th align=center ".$style_upper." rowspan=3>PTKP</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>PKP</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>PKP PEMBULATAN</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>PPH 21 SETAHUN</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>DENDA NO NPWP</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>PPH 21 TERHUTANG</th>";
					$stream.="<th align=center ".$style_upper." colspan=2>PPH 21</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>PPH 21</th>";

				}else{
					$stream.="<th align=center ".$style_upper." colspan=3>PERHITUNGAN PPH21 (JAN-NOV)</th>";
					$stream.="<th align=center ".$style_upper." rowspan=3>PPH 21 (JAN-NOV)</th>";
				}
				
			$stream.="</tr>";	

			$stream.="<tr class=rowcontent>";

				foreach ($dtkomplus as $komplus){
					$stream.="<th ".$style_upper." align=center rowspan=2>".$optNmKomponen[$komplus]."</th>";
				}

				if($count_dtkommin > 0){
					foreach ($dtkomin as $kommin){
						$stream.="<th ".$style_upper." align=center rowspan=2>".$optNmKomponen[$kommin]."</th>";
					}
				}

				if($bulan == '12'){

					$stream.="<th align=center ".$style_upper." rowspan=2>PPH 21 TAHUNAN</th>";
					$stream.="<th align=center ".$style_upper." rowspan=2>PPH 21 JAN-NOV</th>";


				}else{
					$stream.="<th ".$style_upper." align=center rowspan=2>GAJI BRUTO</th>";
					$stream.="<th ".$style_upper." align=center rowspan=2>TER</th>";
					$stream.="<th ".$style_upper." align=center rowspan=2>TARIF EFEKTIF RATA RATA</th>";
				}
				

			$stream.="</tr>";	
			$stream .= "<tr class=rowcontent>";
			$stream .= "</tr>";

		$stream.="</thead>";

		$tunjangan_pph21=array();

		foreach ($dtkarid as $karid){	
			@$no++;
			$stream.="<tr class=rowcontent id=row".$no.">";

			
			## GROSS UP TUNJANGAN PAJAK
			if($bulan == '12'){

				// exit("Warning : IN PROGRESSS ");

				foreach ($dtkomplus as $komplus){
					@$ttdtkompusx[$karid]+=$rupiah[$karid][$komplus];
				}

				foreach ($dtkomin as $kommin){
					@$ttdkomminx[$karid]+=$rupiah[$karid][$kommin];
				}
				
				do{		
					
					if(!isset($tunjangan_pph21[$karid])){
						$tunjangan_pph21[$karid]=0;
					}
	
					if(isset($tarifpph21x[$karid])){
						$tunjangan_pph21[$karid]=$tarifpph21x[$karid];
					}

					## Biaya Jabatan
					@$Biaya_jabatanx[$karid]=($ttdtkompusx[$karid] + $tunjangan_pph21[$karid]) * $persenjabatan/100;   
					if($Biaya_jabatanx[$karid]>($maxjabatan * 12)){   
						$Biaya_jabatanx[$karid]=$maxjabatan * 12;   
					}  	

					## GAJI NETTO
					$GajiNettox[$karid] = ($ttdtkompusx[$karid] + $Biaya_jabatanx[$karid] + $tunjangan_pph21[$karid]) - $ttdkomminx[$karid];	

					// echo "'".$Biaya_jabatanx[$karid]."'";


					$pkpsetahunhitungx[$karid] = $GajiNettox[$karid] - $ptkpnya[$karid];
                    if ($pkpsetahunhitungx[$karid] < 0) {
                        $pkpsetahunhitungx[$karid] = 0;
                    } else {
                        $pkpsetahunhitungx[$karid]= intval(($pkpsetahunhitungx[$karid] / 1000)) * 1000;
                    }					

					// $pph21setahunhitungx[$karid] = 0;
					// if ($pkpsetahunhitungx[$karid] > 0) {
                    //     if ($pkpsetahunhitungx[$karid] <= ($pphtarif[0])) {
                    //         $pph21setahunhitungx[$karid] = $pkpsetahunhitungx[$karid] * $pphpercent[0];
                    //         $persentasehitungx = $persentasehitungx - $pphpercent[0];
                    //     } else if ($pkpsetahunhitungx[$karid] <= ($pphtarif[1])) {
                    //         $pph21setahunhitungx[$karid] = $pphpenambah[1] + ($pkpsetahunhitungx[$karid] - $pphtarif[0]) * $pphpercent[1];
                    //         $persentasehitungx = $persentasehitungx - $pphpercent[1];
                    //     } else if ($pkpsetahunhitungx[$karid] <= ($pphtarif[2])) {
                    //         $pph21setahunhitungx[$karid] = $pphpenambah[2] + ($pkpsetahunhitungx[$karid] - $pphtarif[1]) * $pphpercent[2];
                    //         $persentasehitungx = $persentasehitungx - $pphpercent[2];
                    //     } else if ($pkpsetahunhitungx[$karid] <= ($pphtarif[3])) {
                    //         $pph21setahunhitungx[$karid] = $pphpenambah[3] + ($pkpsetahunhitungx[$karid] - $pphtarif[2]) * $pphpercent[3];
                    //         $persentasehitungx = $persentasehitungx - $pphpercent[3];
                    //     } else {
                    //         $pph21setahunhitungx[$karid] = $pphpenambah[4] + ($pkpsetahunhitungx[$karid] - $pphtarif[3]) * $pphpercent[4];
                    //         $persentasehitungx = $persentasehitungx - $pphpercent[4];
                    //     }
                    // }

					$dendanpwphitungx[$karid] = 0;
                    if ($npwp[$karid] == '' or $npwp[$karid] == '00.000.000.0-000.000') {
                        $dendanpwphitungx[$karid] = round(($pph21setahunhitungx[$karid] * 20 / 100));
                    }

					$pph21terutanghitungx[$karid] = $pph21setahunhitungx[$karid] + $dendanpwphitungx[$karid];
	
					## Tarif PPh21
					$tarifpph21x[$karid] = $pph21terutanghitungx[$karid] - $pph21_jan_nov[$karid];
	
				}while ($tarifpph21x[$karid] != $tunjangan_pph21[$karid]);
				

			}else{
				$str = "select * from ".$dbname.".sdm_5tarifpph21 where status = '".$statuspajak[$karid]."' and kategori = '".$optKategori[$statuspajak[$karid]]."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$tarifData = $res->fetchAll();

				foreach ($dtkomplus as $komplus){
					@$ttdtkompusx[$karid]+=$rupiah[$karid][$komplus];
				}
				
				do{		
					
					if(!isset($tunjangan_pph21[$karid])){
						$tunjangan_pph21[$karid]=0;
					}
	
					if(isset($tarifpph21x[$karid])){
						$tunjangan_pph21[$karid]=$tarifpph21x[$karid];
					}
	
					## Gaji BRUTO
					@$gajiBrutox[$karid] = $ttdtkompusx[$karid] + $tunjangan_pph21[$karid];
	
					foreach ($tarifData as $bar) {
						if ($gajiBrutox[$karid]  >= $bar['minim'] && $gajiBrutox[$karid]  <= $bar['maxim']) {
							$tarif_ter[$karid] = $bar['tarif'];
							break; // Keluar dari loop jika sudah menemukan tarif yang sesuai
						}
					}
	
					## Tarif PPh21
					@$tarifpph21x[$karid] = $gajiBrutox[$karid] * $tarif_ter[$karid] /100;
	
				}while ($tarifpph21x[$karid] != $tunjangan_pph21[$karid]);

			}
			


			if($tipe != 'excel') {
				$stream.="<td hidden id=karyawanid".$no.">".$karid."</td>";
			}
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$nik[$karid]."</td>";
				$stream.="<td>".$nmkar[$karid]." <br> $karid</td>";
				$stream.="<td align=center>".$nmgolongan[$golongan[$karid]]."</td>";
				$stream.="<td align=center>".$statuspajak[$karid]."</td>";
				$stream.="<td align=center>".$bagian[$karid]."</td>";
				$stream.="<td>".$nmjabatan[$jabatan[$karid]]."</td>";

				if($bulan == '12'){

					foreach ($dtkomplus as $komplus){
						$stream.="<td align=right >".number_format($rupiah[$karid][$komplus],0)."</td>";
						@$ttdtkompus[$karid]+=$rupiah[$karid][$komplus];
						@$gttdtkompus+=$rupiah[$karid][$komplus];
					}
	
					$stream.="<td align=right>".number_format($ttdtkompus[$karid])."</td>";

					## TUNJANGAN PAJAK
					$stream.="<td align=right id='tjpph21x".$no."'>".number_format($tunjangan_pph21[$karid],0)."</td>";
					$gtTjpajak += $tunjangan_pph21[$karid];
					
					## Biaya Jabatan
					@$Biaya_jabatan[$karid]=($ttdtkompus[$karid] + $tunjangan_pph21[$karid])*$persenjabatan/100;   
					if($Biaya_jabatan[$karid]>($maxjabatan * 12)){   
						$Biaya_jabatan[$karid]=$maxjabatan * 12;   
					}  

					$stream.="<td align=right>".number_format($Biaya_jabatan[$karid])."</td>";
					$gtbiayajabatan += $Biaya_jabatan[$karid];
					

					if($count_dtkommin > 0){
						foreach ($dtkomin as $kommin){
							$stream.="<td align=right >".number_format($rupiah[$karid][$kommin],0)."</td>";
							@$ttdkommin[$karid]+=$rupiah[$karid][$kommin];
							@$gttdtkommin+=$rupiah[$karid][$kommin];
						}
	
						$stream.="<td align=right >".number_format($ttdkommin[$karid],0)."</td>";
	
						## GAJI NETTO
						$GajiNetto[$karid] = $ttdtkompus[$karid] + $Biaya_jabatan[$karid] - $ttdkommin[$karid];
						$stream.="<td align=right >".number_format($GajiNetto[$karid],0)." </td>";
						$gtnetto += $GajiNetto[$karid];
					}

					$stream.="<td align=right >".number_format($ptkpnya[$karid],0)."</td>";

					$pkpsetahunhitung[$karid] = $GajiNetto[$karid] - $ptkpnya[$karid];
                    if ($pkpsetahunhitung[$karid] < 0) {
                        $pkpsetahunhitung[$karid] = 0;
                    } else {
						$pkpsetahun[$karid]= $pkpsetahunhitung[$karid] ;
                        $pkpsetahunhitung[$karid]= intval(($pkpsetahunhitung[$karid] / 1000)) * 1000;
                    }

					$stream.="<td align=right >".number_format($pkpsetahun[$karid],0)." </td>";
					$stream.="<td align=right >".number_format($pkpsetahunhitung[$karid],0)." </td>";

					$pph21setahunhitung[$karid] = 0;
					if ($pkpsetahunhitung[$karid] > 0) {
                        if ($pkpsetahunhitung[$karid] <= ($pphtarif[0])) {
                            $pph21setahunhitung[$karid] = $pkpsetahunhitung[$karid] * $pphpercent[0];
                            $persentasehitung = $persentasehitung - $pphpercent[0];
                        } else if ($pkpsetahunhitung[$karid] <= ($pphtarif[1])) {
                            $pph21setahunhitung[$karid] = $pphpenambah[1] + ($pkpsetahunhitung[$karid] - $pphtarif[0]) * $pphpercent[1];
                            $persentasehitung = $persentasehitung - $pphpercent[1];
                        } else if ($pkpsetahunhitung[$karid] <= ($pphtarif[2])) {
                            $pph21setahunhitung[$karid] = $pphpenambah[2] + ($pkpsetahunhitung[$karid] - $pphtarif[1]) * $pphpercent[2];
                            $persentasehitung = $persentasehitung - $pphpercent[2];
                        } else if ($pkpsetahunhitung[$karid] <= ($pphtarif[3])) {
                            $pph21setahunhitung[$karid] = $pphpenambah[3] + ($pkpsetahunhitung[$karid] - $pphtarif[2]) * $pphpercent[3];
                            $persentasehitung = $persentasehitung - $pphpercent[3];
                        } else {
                            $pph21setahunhitung[$karid] = $pphpenambah[4] + ($pkpsetahunhitung[$karid] - $pphtarif[3]) * $pphpercent[4];
                            $persentasehitung = $persentasehitung - $pphpercent[4];
                        }
                    }

					$stream.="<td align=right >".number_format($pph21setahunhitung[$karid],2)." </td>";

					$dendanpwphitung[$karid] = 0;
                    if ($npwp[$karid] == '' or $npwp[$karid] == '00.000.000.0-000.000') {
                        $dendanpwphitung[$karid] = round(($pph21setahunhitung[$karid] * 20 / 100));
                    }

					$stream.="<td align=right >".number_format($dendanpwphitung[$karid],2)."</td>";
					
					
	                $pph21terutanghitung[$karid] = $pph21setahunhitung[$karid] + $dendanpwphitung[$karid];
					$stream.="<td align=right >".number_format($pph21terutanghitung[$karid],2)." </td>";
					$gtpph21terutanghitung +=$pph21terutanghitung[$karid];

					$stream.="<td align=right >".number_format($pph21terutanghitung[$karid],2)." </td>";
					$stream.="<td align=right >".number_format($pph21_jan_nov[$karid],2)." </td>";
					$gtpph21_jan_nov += $pph21_jan_nov[$karid];

					##PPh21 
					$tarifpph21[$karid] = $pph21terutanghitung[$karid] - $pph21_jan_nov[$karid];
					$stream.="<td align=right id='pph21x".$no."'>".number_format($tarifpph21[$karid],0)."</td>";
					$gttarifpph21 += $tarifpph21[$karid];

				}else{
					foreach ($dtkomplus as $komplus){
						$stream.="<td align=right >".number_format($rupiah[$karid][$komplus],0)."</td>";
						@$ttdtkompus[$karid]+=$rupiah[$karid][$komplus];
						@$gttdtkompus+=$rupiah[$karid][$komplus];
					}
	
					$stream.="<td align=right>".number_format($ttdtkompus[$karid])."</td>";
	
	
					
					## Gaji BRUTO
					@$gajiBruto[$karid] = $ttdtkompus[$karid] + $tunjangan_pph21[$karid];
					@$gtgajiBruto += $gajiBruto[$karid] ;
					
					foreach ($tarifData as $bar) {
						if ($gajiBruto[$karid]  >= $bar['minim'] && $gajiBruto[$karid]  <= $bar['maxim']) {
							$tarif_ter[$karid] = $bar['tarif'];
							break; // Keluar dari loop jika sudah menemukan tarif yang sesuai
						}
					}
	
					## TUNJANGAN PAJAK
					$stream.="<td align=right id='tjpph21x".$no."'>".number_format($tunjangan_pph21[$karid],0)."</td>";
					$gtTjpajak += $tunjangan_pph21[$karid];
	
	
					if($count_dtkommin > 0){
						foreach ($dtkomin as $kommin){
							$stream.="<td align=right >".number_format($rupiah[$karid][$kommin],0)."</td>";
							@$ttdkommin[$karid]+=$rupiah[$karid][$kommin];
							@$gttdtkommin+=$rupiah[$karid][$kommin];
						}
	
						$stream.="<td align=right >".number_format($ttdkommin[$karid],0)."</td>";
	
						## GAJI NETTO
						$stream.="<td align=right >".number_format($gajiBruto[$karid] - $ttdkommin[$karid],0)."</td>";
						$gtnetto +=$gajiBruto[$karid] - $ttdkommin[$karid];
	
					}
	
					## GAJI BRUTO
					$stream.="<td align=right >".number_format($gajiBruto[$karid],0)."</td>";
	
					## KATEGORI TARIF TER
					$stream.="<td align=center >".$optKategori[$statuspajak[$karid]]."</td>";
	
					## TER NYA
					$stream.="<td align=center>".number_format($tarif_ter[$karid],2)."</td>";
	
					## TARIF PPH21
					@$tarifpph21[$karid] = $gajiBruto[$karid] * $tarif_ter[$karid] /100;
					@$gttarifpph21 += $gajiBruto[$karid] * $tarif_ter[$karid] /100;
					$stream.="<td align=right id='pph21x".$no."'>".number_format($tarifpph21[$karid],0)."</td>";
					$gtTarifpph21 +=$tarifpph21[$karid];
	

				}

			$stream.="</tr>";	
		}

			$stream.="<tr class=rowcontent>";
				

				if($bulan == '12'){

					$colspanplus= $count_dtkomplus +7;
					$colspanpmin= $count_dtkommin;

					$stream.="<td align=center colspan=".$colspanplus."><b>TOTAL</b></td>";
					$stream.="<td align=center><b>".number_format($gttdtkompus)."</b></td>";
					$stream.="<td align=center><b>".number_format($gtbiayajabatan)."</b></td>";
					if($count_dtkommin > 0){
						$stream.="<td align=center colspan=".$colspanpmin."><b></b></td>";

						$stream.="<td align=center><b>".number_format($gttdtkommin)."</b></td>";
						$stream.="<td align=center><b>".number_format($gtnetto)."</b></td>";
					}

					$stream.="<td align=center><b></b></td>";
					$stream.="<td align=center><b></b></td>";
					$stream.="<td align=center><b></b></td>";
					$stream.="<td align=center><b></b></td>";
					$stream.="<td align=center><b></b></td>";
					$stream.="<td align=center><b></b></td>";
					$stream.="<td align=center><b>".number_format($gtpph21terutanghitung)."</b></td>";
					$stream.="<td align=center><b>".number_format($gtpph21_jan_nov)."</b></td>";
					$stream.="<td align=center><b>".number_format($gttarifpph21)."</b></td>";


				}else{

					$colspanplus= $count_dtkomplus +7;
					$colspanpmin= $count_dtkommin;

					$stream.="<td align=center colspan=".$colspanplus."><b>TOTAL</b></td>";
					$stream.="<td align=center><b>".number_format($gttdtkompus)."</b></td>";
					$stream.="<td align=center><b>".number_format($gtTjpajak)."</b></td>";
					if($count_dtkommin > 0){
						$stream.="<td align=center colspan=".$colspanpmin."><b></b></td>";

						$stream.="<td align=center><b>".number_format($gttdtkommin)."</b></td>";
						$stream.="<td align=center><b>".number_format($gtnetto)."</b></td>";
					}
					$stream.="<td align=center><b>".number_format($gtgajiBruto)."</b></td>";
					$stream.="<td align=center><b></b></td>";
					$stream.="<td align=center><b></b></td>";
					$stream.="<td align=center><b>".number_format($gtTarifpph21)."</b></td>";

				}
				

			$stream.="</tr>";	
		$stream.="<tbody></table></div>";


		if($tipe!='excel') {
			$stream.="<button class=mybutton onclick=prosespph(".$no.");>".$_SESSION['lang']['proses']."</button>";
			echo $stream."####".$no;
		}
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="laporan_pph_pasal21";
			if(strlen($stream)>0)
			{
					if ($handle = opendir('tempExcel')) {
							while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
									@unlink('tempExcel/'.$file);
							}
							}	
							closedir($handle);
					}
					$handle=fopen("tempExcel/".$nop_.".xls",'w');
					if(!fwrite($handle,$stream)){
							echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
							exit;
					}else{
							echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls';
							</script>";
					}
					fclose($handle);
			}    
		}
    break;
	
	

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
         
        break;	
}
?>