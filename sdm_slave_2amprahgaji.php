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
$pph21regular  =checkPostGet('pph21regular','');
$pph21irregular=checkPostGet('pph21irregular','');
// $pph21x        =checkPostGet('pph21x','');
// $pph21desemberx=checkPostGet('pph21desemberx','');
$tjpph21x      =checkPostGet('tjpph21x','');
$istjpph21     =checkPostGet('istjpph21','');
$nilaitjpph21  =checkPostGet('nilaitjpph21','');
$baris         =checkPostGet('baris','');

$pph21x= str_replace(',','', checkPostGet('pph21x',''));
$pph21desemberx= str_replace(',','', checkPostGet('pph21desemberx',''));

##Bulan
$bulan12 = substr($per, 5, 2);

switch($proses){
	// case'prosespph':
	// 	try {
	// 	$owlPDO->beginTransaction();
		
	// 	$str="select distinct(sudahproses) from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' and periode='".$per."'";
	// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// 	$res->setFetchMode(PDO::FETCH_ASSOC);
	// 	$bar=$res->fetch();
	// 	$aktifperiodegaji=$bar['sudahproses'];

	// 	if($aktifperiodegaji==0){
	// 		throw new PDOException('Periode gaji '.$per.' belum ditutup.');
	// 	}
		
	// 	$str="select distinct(tutupbuku) from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and periode='".$per."'";
	// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// 	$res->setFetchMode(PDO::FETCH_ASSOC);
	// 	$bar=$res->fetch();
	// 	$aktifperiodeacct=$bar['tutupbuku'];
	// 	if($aktifperiodeacct==1){
	// 		throw new PDOException('Periode akutansi '.$per.' sudah ditutup.');
	// 	}

	// 	if($bulan12 == 12){
	// 		$pph21x  = $pph21desemberx;
	// 	}		
		
	// 	if($baris=='1'){			
	// 		$str = "delete from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='42' and periodegaji='".$per."'";
	// 		$owlPDO->exec($str);

	// 		$str = "delete from " . $dbname . ".sdm_pph21 where kodeorg='" . $unit . "' and periode='".$per."'";
	// 		$owlPDO->exec($str);
	// 	}

	// 	if($pph21x != 0){
	// 		$str = "insert into " . $dbname . ".sdm_gaji (`karyawanid`,`kodeorg`,`idkomponen`,`jumlah`,`pengali`,`hk`,`periodegaji`) values ('".$karyawanid."','".$unit."','42','".$pph21x."','1','0','".$per."') ";
	// 		$owlPDO->exec($str);
	
	// 		$str = "insert into " . $dbname . ".sdm_pph21 (`karyawanid`,`kodeorg`,`tipepph`,`nilai`,`periode`) values ('".$karyawanid."','".$unit."','1','".$pph21x."','".$per."') ";
	// 		$owlPDO->exec($str);
	// 	}
		
	// 	$owlPDO->commit();
	// 	} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		
	// break;
	
    case 'preview':

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
		$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$per."' and statuspajak <> '' "; 
		$res = fetchdata($str);
		if(count($res)>0){ 
			$dakarbulanan=1;
		}

		if($dakarbulanan==1){
			$strawal = "select * from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$per."' and statuspajak <> ''  and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)"; 
		}else{
			$strawal="select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and statuspajak <> '' and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) ";
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


		## Komponen tunjangan tetap
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='KOMTJTETAP'";
		$res=fetchdata($str);
		$komponentjtetap = $res[0]['nilai'];

		## DT Tunjangan tetap
		$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen in (".$komponentjtetap.")  and periodegaji = '".$per."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['plus']==1){
				$dttjtetap[$bar['idkomponen']]=$bar['idkomponen'];	
				$rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
			}
		}

		$count_dttjtetap = count($dttjtetap);

		## DT Komplus
		$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen not in (".$expph.",".$komponentjtetap.") and periodegaji = '".$per."' ";
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

		$count_dtkomplus = count($dtkomplus);
		if($count_dtkomplus == 0){
			if($proses != 'excel') {
				$rowspan_dtkompus = "hidden";
			}
		}

		## BPJS Beben Perusahaan appl
		$str="select nilai from ".$dbname.".setup_parameterappl where 1=1 and kodeparameter = 'HRBPJSPER'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$komponenBPJSper = $bar['nilai'];
		}

		## ambil Id komponennya BPJS perusahaan
		$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen in (".$komponenBPJSper.") and periodegaji = '".$per."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
				$dtbpjsper[$bar['idkomponen']]=$bar['idkomponen'];	
				$rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
		}

		$count_dtbpjsper = count($dtbpjsper);
		if($count_dtbpjsper == 0){
			if($proses != 'excel') {
				$rowspan_dtbpjsper = 'hidden ';
			}
		}

		## BPJS Beben karyawan appl
		$str="select nilai from ".$dbname.".setup_parameterappl where 1=1 and kodeparameter = 'HRBPJSKAR'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$komponenBPJSkar = $bar['nilai'];
		}

		## ambil Id komponennya BPJS karyawan
		$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen in (".$komponenBPJSkar.") and periodegaji = '".$per."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
				$dtbpjskar[$bar['idkomponen']]=$bar['idkomponen'];	
				$rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
		}

		$count_dtbpjskar = count($dtbpjskar);
		if($count_dtbpjskar == 0){
			if($proses != 'excel') {
				$rowspan_dtbpjskar = 'hidden';
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

		$stream.="<thead>";
			$stream.="<tr class=rowcontent>";
				$stream.="<th align=center ".$style_upper." rowspan=3>NO</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3> NIK </th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['namakaryawan']."</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>Golongan</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['statuspajak']."</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['bagian']."</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['jabatan']."</th>";
				$stream.="<th align=center ".$style_upper." colspan=".$count_dttjtetap." >GAJI / UPAH</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>JUMLAH GAJI</th>";
				$stream.="<th align=center ".$style_upper." ".$rowspan_dtkompus." colspan=".$count_dtkomplus.">PREMI/LEMBUR/DLL</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>JUMLAH PREMI/LEMBUR/DLL</th>";

				if($bulan12 == 12){
					$stream.="<th align=center ".$style_upper." ".$rowspan_dtbpjsper." colspan=".$count_dtbpjsper.">BPJS <br>(PERUSAHAAN)</th>";
					$stream.="<th align=center ".$style_upper." ".$rowspan_dtbpjsper." rowspan=3>JUMLAH BPJS <br>(PERUSAHAAN)</th>";

					$stream.="<th align=center ".$style_upper." ".$rowspan_dtbpjskar." colspan=".$count_dtbpjskar.">BPJS <br>(PESERTA)</th>";
					$stream.="<th align=center ".$style_upper." ".$rowspan_dtbpjskar." rowspan=3>JUMLAH BPJS <br>(PESERTA)</th>";
				}

				$stream.="<th align=center ".$style_upper." colspan=3>PERHITUNGAN PPH21 (JAN-NOV)</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>PPH 21 (JAN-NOV)</th>";

				if($bulan12 == 12){

					## BULAN 12 
					$stream.="<th align=center ".$style_upper."  colspan=2>PENGURANG PPH 21 DESEMBER </th>";
					$stream.="<th align=center ".$style_upper."  colspan=2>BRUTO PAJAK</th>";
					
					$stream.="<th align=center ".$style_upper."  rowspan=3>PTKP</th>";
					$stream.="<th align=center ".$style_upper."  rowspan=3>PKP</th>";
					$stream.="<th align=center ".$style_upper."  rowspan=3>TINGKATAN</th>";
					$stream.="<th align=center ".$style_upper."  rowspan=3>PPH 21 (DESEMBER)</th>";
					$stream.="<th align=center ".$style_upper."  rowspan=3>TOTAL PPH 1 TAHUN</th>";
					## END 12

				}
				


			$stream.="</tr>";	

			$stream.="<tr class=rowcontent>";
				foreach ($dttjtetap as $komplus){
					$stream.="<th ".$style_upper." align=center rowspan=2>".$optNmKomponen[$komplus]."</th>";
				}

				foreach ($dtkomplus as $komplus){
					$stream.="<th ".$style_upper." align=center rowspan=2>".$optNmKomponen[$komplus]."</th>";
				}

				if($bulan12 == 12){

					foreach ($dtbpjsper as $komplus){
						$stream.="<th ".$style_upper." align=center rowspan=2>".$optNmKomponen[$komplus]."</th>";
					}
					foreach ($dtbpjskar as $komplus){
						$stream.="<th ".$style_upper." align=center rowspan=2>".$optNmKomponen[$komplus]."</th>";
					}
				}

				$stream.="<th ".$style_upper." align=center rowspan=2>GAJI BRUTO</th>";
				$stream.="<th ".$style_upper." align=center rowspan=2>TER</th>";
				$stream.="<th ".$style_upper." align=center rowspan=2>TARIF EFEKTIF RATA RATA</th>";

				if($bulan12 == 12){

					## BULAN 12
					$stream.="<th ".$style_upper." align=center rowspan=2>PENGURANG PKP</th>";
					$stream.="<th ".$style_upper." align=center rowspan=2> JHT (PESERTA)</th>";
					$stream.="<th ".$style_upper." align=center rowspan=2> SEBULAN </th>";
					$stream.="<th ".$style_upper." align=center rowspan=2> SETAHUN </th>";
					## END 12
				}
			$stream.="</tr>";	
			$stream .= "<tr class=rowcontent>";
			$stream .= "</tr>";

		$stream.="</thead>";

		foreach ($dtkarid as $karid){	
			@$no++;
			$stream.="<tr class=rowcontent id=row".$no.">";
			
			if($tipe != 'excel') {
				$stream.="<td hidden id=karyawanid".$no.">".$karid."</td>";
			}
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$nik[$karid]."</td>";
				$stream.="<td>".$nmkar[$karid]."</td>";
				$stream.="<td align=center>".$nmgolongan[$golongan[$karid]]."</td>";
				$stream.="<td align=center>".$statuspajak[$karid]."</td>";
				$stream.="<td align=center>".$bagian[$karid]."</td>";
				$stream.="<td>".$nmjabatan[$jabatan[$karid]]."</td>";

				foreach ($dttjtetap as $komplus){
					$stream.="<td align=right >".number_format($rupiah[$karid][$komplus],0)."</td>";
					@$ttjtetap[$karid]+=$rupiah[$karid][$komplus];
					@$gttjtetap+=$rupiah[$karid][$komplus];
				}

				$stream.="<td align=right>".number_format($ttjtetap[$karid])."</td>";

				foreach ($dtkomplus as $komplus){
					$stream.="<td align=right >".number_format($rupiah[$karid][$komplus],0)."</td>";
					@$ttdtkompus[$karid]+=$rupiah[$karid][$komplus];
					@$gttdtkompus+=$rupiah[$karid][$komplus];
				}

				$stream.="<td align=right>".number_format($ttdtkompus[$karid])."</td>";

				if($bulan12 == 12){

				foreach ($dtbpjsper as $komplus){
					$stream.="<td align=right >".number_format($rupiah[$karid][$komplus],0)."</td>";
					@$ttdtbpjsper[$karid]+=$rupiah[$karid][$komplus];
					@$gttdtbpjsper+=$rupiah[$karid][$komplus];
				}

				$stream.="<td align=right ".$rowspan_dtbpjsper.">".number_format($ttdtbpjsper[$karid])."</td>";
				
				foreach ($dtbpjskar as $komplus){
					$stream.="<td align=right >".number_format($rupiah[$karid][$komplus],0)."</td>";
					@$ttdtbpjskar[$karid]+=$rupiah[$karid][$komplus];
					@$gttdtbpjskar+=$rupiah[$karid][$komplus];
				}

				$stream.="<td align=right ".$rowspan_dtbpjskar.">".number_format($ttdtbpjskar[$karid])."</td>";
			}

				## Gaji BRUTO
				@$gajiBruto[$karid] = $ttdtkompus[$karid] + $ttjtetap[$karid];
				@$gtgajiBruto = $gttdtkompus + $gttjtetap;
				$stream.="<td align=right >".number_format($gajiBruto[$karid],0)."</td>";

				
				$stream.="<td align=center >".$optKategori[$statuspajak[$karid]]."</td>";

				$str = "select * from ".$dbname.".sdm_5tarifpph21 where status = '".$statuspajak[$karid]."' and kategori = '".$optKategori[$statuspajak[$karid]]."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$tarifData = $res->fetchAll();

				foreach ($tarifData as $bar) {
					if ($gajiBruto[$karid]  >= $bar['minim'] && $gajiBruto[$karid]  <= $bar['maxim']) {
						$tarif_ter[$karid] = $bar['tarif'];
						break; // Keluar dari loop jika sudah menemukan tarif yang sesuai
					}
				}

				$stream.="<td align=center>".number_format($tarif_ter[$karid],2)."</td>";

				## Tarif PPh21
				@$tarifpph21[$karid] = $gajiBruto[$karid] * $tarif_ter[$karid] /100;
				@$gttarifpph21 += $gajiBruto[$karid] * $tarif_ter[$karid] /100;
				$stream.="<td align=right id='pph21x".$no."'>".number_format($tarifpph21[$karid],0)."</td>";

				if($bulan12 == 12){

				## BULAN 12

				## Biaya jabatan
				$biaya_jabatan[$karid] = $ttjtetap[$karid] + $ttdtkompus[$karid] + $komponenBPJSper[$karid] + ($ttjtetap[$karid] * 0.84 / 100) * $persenjabatan /100;

				if($biaya_jabatan[$karid] >= $maxjabatan){
					$biaya_jabatan[$karid] = $maxjabatan;
				}else{
					$biaya_jabatan[$karid] = $ttjtetap[$karid] + $ttdtkompus[$karid] * $persenjabatan /100;
				}

				$gtbiayajabatan += $biaya_jabatan[$karid];
				$stream.="<td align=right >".number_format($biaya_jabatan[$karid],0)."</td>";
				
				## Bpjs Peserta
				$stream.="<td align=right >".number_format($ttdtbpjskar[$karid],0)."</td>";

				## Bruto sebulan
				$bruto_sebulan[$karid]  = $ttjtetap[$karid] + $ttdtkompus[$karid] + $komponenBPJSper[$karid]  + ($ttjtetap[$karid] * 0.84 / 100) - $biaya_jabatan[$karid] - $ttdtbpjskar[$karid] ;
				$gtbrutosebulan +=$bruto_sebulan[$karid] ;
				$stream.="<td align=right >".number_format($bruto_sebulan[$karid],0)."</td>";
				$stream.="<td align=right >".number_format($bruto_sebulan[$karid] * 12,0)."</td>";
				$stream.="<td align=right >".number_format($ptkpnya[$karid],0)."</td>";

				$bruto_setahun[$karid] = $bruto_sebulan[$karid] * 12;

				if($bruto_setahun[$karid] > $ptkpnya[$karid] ){
					$pkp[$karid] = $bruto_setahun[$karid] - $ptkpnya[$karid];
				}else{
					$pkp[$karid] = 0;
				}


				$stream.="<td align=right >".number_format($pkp[$karid],0)."</td>";

				if($pkp[$karid]>0){     
					if($pkp[$karid]<=($pphtarif[0])){
						$percent_pkp[$karid] = $pphpercent[0];
					}else if($pkp[$karid]<=($pphtarif[1])){
						$percent_pkp[$karid] = $pphpercent[1];
					}else if($pkp[$karid]<=($pphtarif[2])){
						$percent_pkp[$karid] = $pphpercent[2];
					}else if($pkp[$karid]<=($pphtarif[3])){
						$percent_pkp[$karid] = $pphpercent[3];
					}else{
						$percent_pkp[$karid] = $pphpercent[4];
					}
				}

					$stream.="<td align=right >".number_format($percent_pkp[$karid],2)."</td>";

					$pph21desember[$karid] = $pkp[$karid] * $percent_pkp[$karid] / 100;
					$gtpph21desember += $pph21desember[$karid];
					$stream.="<td align=right id='pph21desemberx".$no."' >".number_format($pph21desember[$karid],0)."</td>";

					$pph21setahun[$karid] = $pph21desember[$karid] - $tarifpph21[$karid];
					$gtpph21setahun += $pph21desember[$karid] - $tarifpph21[$karid];
					$stream.="<td align=right >".number_format($pph21setahun[$karid],0)."</td>";
				## END 12
			}

			$stream.="</tr>";	
		}

			$stream.="<tr class=rowcontent>";
				$colspanGt1 = $count_dttjtetap +7;
				$colspanGt2 = $count_dtkomplus;
				$colspanGt3 = $count_dtbpjsper;
				$colspanGt4 = $count_dtbpjskar;
				$stream.="<td align=center colspan=".$colspanGt1."><b>TOTAL</b></td>";
				$stream.="<td align=center><b>".number_format($gttjtetap,0)."</b></td>";
				$stream.="<td align=center colspan=".$colspanGt2."></td>";
				$stream.="<td align=center ><b>".number_format($gttdtkompus,0)."</b></td>";

				if($bulan12 == 12){

				$stream.="<td align=center colspan=".$colspanGt3. " ".$rowspan_dtbpjsper."></td>";
				$stream.="<td align=center ".$rowspan_dtbpjsper."><b>".number_format($gttdtbpjsper,0)."</td>";

				$stream.="<td align=center colspan=".$colspanGt4." ".$rowspan_dtbpjskar."></td>";
				$stream.="<td align=center ".$rowspan_dtbpjskar."><b>".number_format($gttdtbpjskar,0)."</td>";
				}
				$stream.="<td align=center ><b>".number_format($gtgajiBruto,0)."</b></td>";
				$stream.="<td align=center colspan=2></td>";
				$stream.="<td align=center ><b>".number_format($gttarifpph21,0)."</td>";

				if($bulan12 == 12){

				## BULAN 12
				$stream.="<td align=center ><b>".number_format($gtbiayajabatan,0)."</td>";
				$stream.="<td align=center ><b>".number_format($gttdtbpjsper,0)."</td>";
				$stream.="<td align=center ><b>".number_format($gtbrutosebulan,0)."</td>";
				$stream.="<td align=center ><b>".number_format($gtbrutosebulan*12,0)."</td>";
				$stream.="<td align=center colspan=3></td>";
				$stream.="<td align=center ><b>".number_format($gtpph21desember,0)."</td>";
				$stream.="<td align=center ><b>".number_format($gtpph21setahun,0)."</td>";
				## END BULAN 12
				}

			$stream.="</tr>";	
		$stream.="<tbody></table></div>";


		if($tipe!='excel') {
			// $stream.="<button class=mybutton onclick=prosespph(".$no.");>".$_SESSION['lang']['proses']."</button>";
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