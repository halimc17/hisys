<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
//kolom QR penyambung
$coLspbHt = "noreferensi";//qr
$coLspbDt = "nospbref";//nospbref
$coLpnnDT = "nopnnref";//nourut

	$proses  = checkPostGet('proses','');//$_POST['proses'];
	$kdOrg 	 = checkPostGet('kdOrg','');
	$tgl 	 = tanggalsystem(checkPostGet('tgl',''));
	$tglQR 	 = checkPostGet('tglQR','');
	$noSpb 	 = checkPostGet('noSpb','');
	$noTransparent 	 = checkPostGet('noTransparent','');
	$noTrans = checkPostGet('noTrans','');
	$qr 	 = checkPostGet('qr','');
	$qrtemp  = checkPostGet('qrtemp','');
	$prestasi= checkPostGet('prestasi','');
	$nomor 	 = checkPostGet('nomor','');
	$nopol	= checkPostGet('nopol','');
	$divisi	 = checkPostGet('divisi','');
	$intvl 	 = strtotime($tgl .' +6 days');
	$intvl 	 = date("Y-m-d", $intvl);

	$intvlQR = strtotime($tglQR .' -6 days');
	$intvlQR = date("Y-m-d", $intvlQR);
	$stream  = '';
	$ChildSPB 	= array();
	$parentSPB 	= array();
	$arrayAnak  = array();
	$anak 	 	= 0;
	$jmlAnak  	= 0;
	$kuduposting = 0;
    switch($proses){
		case 'gantidivisi':
			
			//echo "1<br>";
			$lksi 	= substr($_SESSION['empl']['lokasitugas'],0,4);
			$sKbn 	= "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (kodeorganisasi='".$kdOrg."') or (tipe = 'AFDELING' and kodeorganisasi like '".$kdOrg."%') order by kodeorganisasi ";
			$qKbn 	= $owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
			$qKbn->setFetchMode(PDO::FETCH_ASSOC);
			$optKbn="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rKbn = $qKbn->fetch()) {
				if(strlen($rKbn['kodeorganisasi']) > 4){
					$optKbn .= "<option value=".$rKbn['kodeorganisasi'].">".$rKbn['namaorganisasi']."</option>";
				}
			}

			echo $optKbn;
			break;
		case 'getData':
			$dataREKAP = array();
			if($divisi !=''){
				$whrdiv = "and divisi='".$divisi."'";
			}else{
				$whrdiv = "";
			}
			if(strlen($kdOrg)== 4){
				$whereKodeOrg 			= " and a.kodeorg = '".$kdOrg."' ";
				$unit = $kdOrg;
			}else if(strlen($kdOrg)== 6){
				$whereKodeOrg 			= " and SUBSTRING_INDEX(SUBSTRING_INDEX(a.nospb, '/', 2),'/', -1) = '".$kdOrg."' ";
				$unit = substr($kdOrg,0,4);
			}else{
				exit("Error: Lokasi tidak bisa terdefinisikan.");
			}
			$spbSudahProses = Array();
			$spbOnProgress = Array();
			$noReferensi = Array();
			$sKebun 	= "select a.*,SUBSTRING_INDEX(SUBSTRING_INDEX(a.nospb, '/', 2),'/', -1) as divisi,ifnull(b.noreferensi,'UNPROCESSED') as statusproses,ifnull(b.posting,'x') as posting_result from ".$dbname.".kebun_spbht_mobile a left join ".$dbname.".kebun_spbht b on a.noreferensi = b.noreferensi where a.tanggal = '".$tgl."' ".$whereKodeOrg." ".$whrdiv." and a.syn = '1' and a.tujuan = '0' order by a.tanggal ";
			$qKebun 	= $owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
			$qKebun->setFetchMode(PDO::FETCH_ASSOC);
			$rowKebun 	= owlBaris($qKebun);
			if($rowKebun > 0){
				while ($res = $qKebun->fetch()) {
					$dataREKAP[$res['nospb']]['kebun'] = $res;
					if($res['statusproses'] != 'UNPROCESSED'){
						$spbSudahProses[]=$res['nospb'];
					}
					$spbOnProgress[]=$res['nospb'];//noreferensi
					//$spbOnProgress_mobile[]=$res['noreferensi'];//
					$noReferensi[] = $res;
				}
			}
			$spbPrcssd = array();
			if(count($spbSudahProses) > 0){
				$spbProcessed = " select nospb,SUM(jjg) as jjg,SUM(brondolan) as brondolan from ".$dbname.".kebun_spbdt where nospb in ('".implode("','",array_unique($spbSudahProses))."') group by nospb";
				$qProcessed 	= $owlPDO->query($spbProcessed) or die(print " Gagal: ".PDOException::getMessage());
				$qProcessed->setFetchMode(PDO::FETCH_ASSOC);
				while ($rProcessed = $qProcessed->fetch()) {
					$spbPrcssd[$rProcessed['nospb']]['jjg'] = $rProcessed['jjg'];
					$spbPrcssd[$rProcessed['nospb']]['brondolan'] = $rProcessed['brondolan'];
				}
			}
			$spbOnPrgrss = array();
			$spbProcessed = " select nospb,SUM(jjg) as jjg from ".$dbname.".kebun_spbdt_mobile where nospb in ('".implode("','",array_unique($spbOnProgress))."') group by nospb";
			$qProcessed 	= $owlPDO->query($spbProcessed) or die(print " Gagal: ".PDOException::getMessage());
			$qProcessed->setFetchMode(PDO::FETCH_ASSOC);
			while ($rProcessed = $qProcessed->fetch()) {
				$spbOnPrgrss[$rProcessed['nospb']]['jjgspb'] = $rProcessed['jjg'];
				//$spbOnPrgrss[$rProcessed['nospb']]['brondolan'] = $rProcessed['brondolan'];
			}

			//SECTION2
			$noReferensiPBRK = Array();
			$sPabrik 	= "SELECT a.notransaksi,a.nospb,a.tanggal,a.supir,a.nokendaraan,a.beratbersih as netto,a.jumlahtandan1 as janjang,a.brondolan as brondolan,TRIM(a.identifikasi_qr) as identifikasi
			,ifnull(b.nospb,'UNPROCESSED') as statusproses,ifnull(b.posting,'x') as posting_result
			FROM ".$dbname.".pabrik_timbangan as a
			left join ".$dbname.".kebun_spbht b on a.nospb = b.nospb
			WHERE DATE(a.tanggal) >= '".$tgl."' and DATE(a.tanggal) < '".$intvl."' AND a.identifikasi_qr != '' and a.kodeorg = '".$unit."' ";
			
			$qPabrik 	= $owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
			$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
			$rowPabrik 	= owlBaris($qPabrik);
			if($rowPabrik > 0){
				//date("Ymd", strtotime($res['waktukeluar'])).
				while ($res = $qPabrik->fetch()) {
					//$dataREKAP[$res['qr']]['pabrik'] = $res;
					$dataREKAPPBRK[$res['identifikasi']]['pabrik'] = $res;
					$noReferensiPBRK[] = $res;
				}
			}
			
			$Qrtersedia = array();
			$QRDuplicate = array();
			foreach($noReferensi as $k=>$v){
				$QRFix = $v['noreferensi'];
				
				if (in_array($QRFix, $Qrtersedia)){
					$QRDuplicate[] = $QRFix;
				}
				
				$Qrtersedia[] = $QRFix;
			}
			
			$QrtersediaPBRK = array();
			foreach($noReferensiPBRK as $k=>$v){
				$QrtersediaPBRK[] = trim($v['identifikasi']);
			}
			//check Data benar benar tidak ada kesalahan
			if(count($Qrtersedia) != count(array_unique($Qrtersedia))){
				echo "Berisiko Data QR SPB Double Entry\n";
			}
			if(count($QrtersediaPBRK) != count(array_unique($QrtersediaPBRK))){
				echo "Berisiko Data QR SPB di Pabrik Double Entry\n";
			}
			//menarik yang beririsan
			$QRberIrisan = array_intersect($Qrtersedia, $QrtersediaPBRK);
			
			$qrFalse =[];
			foreach($dataREKAP as $k=>$v){
				if(!in_array($v['kebun']['noreferensi'],$QRberIrisan)){
					$qrFalse[] = $v['kebun'][$coLspbHt];
				}
			}
			$qrFalseOnDt =[];
			if(count($qrFalse) > 0){
				$sKebunSPBDouble = "SELECT nospbref from ".$dbname.".kebun_spbdt_mobile where nospbref IN ('".implode("','",$qrFalse)."')";
				$rDH = fetchdata($sKebunSPBDouble);
				if(count($rDH) > 0){
					foreach($rDH as $v){
						$qrFalseOnDt[] = $v['nospbref'];
					}
				}
			}
			//print_r($qrFalseOnDt);
            $stream = "<fieldset>
						<legend>".$_SESSION['lang']['list']."</legend>
						<table cellspacing=1 border=0 class=sortable width=100%>
							<thead>
								<tr class=rowheader>
									<th align=center colspan='8'>".$_SESSION['lang']['kebun']."</th>
									<th align=center colspan='10' style='background:#ffea32;color:#000;'>".$_SESSION['lang']['pabrik']."</th>
								</tr>
								<tr class=rowheader>
									<th>".@$_SESSION['lang']['nourut']."</th>
									<th>".@$_SESSION['lang']['nospb']." Mobile</th>
									<th>".@$_SESSION['lang']['nopol']."</th>
									<th>".@$_SESSION['lang']['supir']."</th>
									<th>".@$_SESSION['lang']['tglNospb']."</th>
									<th>".@$_SESSION['lang']['jjg']." SPB</th>
									<th>".@$_SESSION['lang']['jjg']."</th>
									<th>".@$_SESSION['lang']['brondolan']."</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['tanggal']."</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['nopol']."</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['supir']."</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['qr']."QR</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['jjg']."</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['brondolan']."</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['berat']."</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['statusproses']."</th>
									<th style='background:#ffea32;color:#000;'>".@$_SESSION['lang']['aksi']."</th>
								</tr>
							</thead>
							<tbody>";
							$no	= 0;
							foreach($dataREKAP as $k=>$v){
								$qrTrue = false;
								$qrMatch = false;
								if(in_array($v['kebun']['noreferensi'],$QRberIrisan)){
									$qrTrue = $v['kebun']['noreferensi'];
									$qrMatch = $v['kebun']['noreferensi'];
								}
								if($qrTrue){
									$no++;
									$status = "";
									if($v['kebun']['statusproses'] == 'UNPROCESSED' and $dataREKAPPBRK[$qrTrue]['pabrik']['statusproses'] == 'UNPROCESSED'){
										$status = "<font color='blue'>Belum diproses</font>";
									}else{
										if($v['kebun']['posting_result'] == "1"){
											$status = "<font color='gray'>POSTED</font> <i class='fa fa-thumbs-o-up'></i>";
										}else if($v['kebun']['posting_result'] == "x"){
											$status = "<font color='blue'>Belum diproses</font>";
										}else if($v['kebun']['posting_result'] == "0"){
											$status = "<font color='green'>Sudah diproses</font>";
										} 
										if($dataREKAPPBRK[$qrTrue]['pabrik']['posting_result'] == "1"){
											$status = "<font color='gray'>POSTED By Manual</font>";
										}else if($dataREKAPPBRK[$qrTrue]['pabrik']['posting_result'] == "0"){
											$status = "<font color='green'>Sudah diproses</font>";
										} 
									}
									$colorMobilTaksama = "background:#f5f5f0;";
									$colorMobilTaksama2 = $colorDuplicate= "";
									$titleMobilTaksama = "";
									if(strtoupper($v['kebun']['nopol'])!= strtoupper($dataREKAPPBRK[$qrTrue]['pabrik']['nokendaraan'])){
										$colorMobilTaksama = "background:#ffa495;";
										$colorMobilTaksama2 = "background:#ffa495;";
										$titleMobilTaksama = "title='".@$_SESSION['lang']['nopol']." tidak sama'";
									}
									if (in_array($qrMatch, $QRDuplicate)){
										$colorDuplicate = "background:#ffa495;' title='Duplicate QR'";
									}
									$stream .= "
										<tr class=rowcontent id=row".$no." style='".$colorDuplicate."'>
											<td align=center>".$no.".</td>
											<td>". $qrMatch."</td>
											<td style='".$colorMobilTaksama2."' ".$titleMobilTaksama.">". $v['kebun']['nopol']."</td>
											<td>". $v['kebun']['kerani']."</td>
											<td align=center>".tanggalnormal($v['kebun']['tanggal'])."</td>
											<td align=center>".number_format(@$spbOnPrgrss[$v['kebun']['nospb']]['jjgspb'])."</td>
											<td align=center>".number_format(@$spbPrcssd[$v['kebun']['nospb']]['jjg'])."</td>
											<td align=center>".number_format(@$spbPrcssd[$v['kebun']['nospb']]['brondolan'])."</td>
											<td style='background:#f5f5f0;color:#000;' align=center>".tanggalnormal($dataREKAPPBRK[$qrTrue]['pabrik']['tanggal'])."</td>
											<td ".$titleMobilTaksama." style='".$colorMobilTaksama."color:#000;' align=center>". $dataREKAPPBRK[$qrTrue]['pabrik']['nokendaraan']."</td>
											<td ".$titleMobilTaksama." style='".$colorMobilTaksama."color:#000;' align=center>". $dataREKAPPBRK[$qrTrue]['pabrik']['supir']."</td>
											<td style='background:#f5f5f0;color:#000;' align=center>".$qrTrue."</td>
											<td style='background:#f5f5f0;color:#000;' align=center>". number_format(intval($dataREKAPPBRK[$qrTrue]['pabrik']['janjang']))."</td>
											<td style='background:#f5f5f0;color:#000;' align=center>". number_format(intval($dataREKAPPBRK[$qrTrue]['pabrik']['brondolan']))."</td>
											<td style='background:#f5f5f0;color:#000;' align=center>". number_format(intval($dataREKAPPBRK[$qrTrue]['pabrik']['netto']))."</td>
											<td style='background:#f5f5f0;color:#000;display:none' id=notran_".$no.">".$dataREKAPPBRK[$qrTrue]['pabrik']['notransaksi']."</td>
											<td style='background:#f5f5f0;color:#000;' align='center' id=nospb_".$no.">".$status."</td>
											<td style='background:#f5f5f0;color:#000;' align=center>";
									//if($v['kebun']['posting_result'] != "1"){	
									if ($colorDuplicate != ""){
										$stream .="Duplicate QR";
									}else{
										$stream .= " <button class=mybutton onclick=\"viewData('".$v['kebun']['nospb']."###".$dataREKAPPBRK[$qrTrue]['pabrik']['notransaksi']."###".$v['kebun']['tanggal']."','".$_SESSION['lang']['detail']."','<fieldset><legend>".$_SESSION['lang']['AmbilKgTimbangan']."</legend><div id=container></div><input type=hidden id=detNospb value=".@$key."></fieldset>',event);\">Detail</button>";
									}
									//}else{
										//$stream .= " <button class=\"mybutton disabled\"><i class='fa fa-lock'></i></button>";
									//}
									$stream .= "</td>
										</tr>";
								}else{
									
									if(isset($v['kebun']) and !in_array($v['kebun'][$coLspbHt],$qrFalseOnDt)){
										$no++;
										$stream .= "
										<tr class=rowcontent id=row".$no.">
											<td align=center>".$no.".</td>";
										$stream .= "<td>".@$v['kebun']['noreferensi']."</td>
											<td>". $v['kebun']['nopol']."</td>
											<td>". $v['kebun']['kerani']."</td>
											<td align=center>". tanggalnormal($v['kebun']['tanggal'])."</td>
											<td align=center>".number_format(@$spbOnPrgrss[$v['kebun']['nospb']]['jjgspb'])."</td>
											<td align=center>".number_format(@$v['kebun']['jjg'])."</td>
											<td align=center>". number_format(@$v['kebun']['brondolan'])."</td>";
										$stream .= "<td align=center style='background:red;color:white;' colspan='10'>". @$v['kebun']['noreferensi']." Tidak ditemukan</td>";
										$stream .= "</tr>";	
										}else{
											/* $stream .= "<td align=center style='background:red;color:white;' colspan='7'>". @$v['pabrik']['qr']." Tidak ditemukan</td>"; */
										}
								}
							}
						$stream .= "</tbody>
								</table>
							</fieldset>";
			
						echo $stream;
	    break;
		case 'ShowData':
			//a.tanggal,a.supir,a.nokendaraan,a.beratbersih as netto,a.jumlahtandan1 as janjang,a.brondolan as brondolan,TRIM(a.identifikasi_qr) as identifikasi
			$sShwData2  = "select a.*,c.tanggal as tanggal,c.beratmasuk,c.beratmasuk,c.beratbersih as netto,c.supir,c.nokendaraan,
			ifnull(b.nospb,'UNPROCESSED') as statusproses,ifnull(b.posting,'0') as posting_result 
			from ".$dbname.".kebun_spbht_mobile a 
			left join ".$dbname.".kebun_spbht b on a.noreferensi = b.noreferensi 
			left join ".$dbname.".pabrik_timbangan c on (a.noreferensi = c.identifikasi_qr or a.noreferensi = c.identifikasi_qr) and c.notransaksi = '".$noTrans."' and a.syn = '1' and c.identifikasi_qr != '' 
			where a.nospb='".$noSpb."' ";
			//echo $sShwData2;$sShwData2['noreferensi']
			$qShwData2 	= $owlPDO->query($sShwData2) or die(print " Gagal: ".PDOException::getMessage());
			$qShwData2->setFetchMode(PDO::FETCH_ASSOC);
			$rShwData2 	= $qShwData2->fetch();
			//$arrStat 	= array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
			//$stat 		= $arrStat[$rShwData2['posting']];
			$status = "";
			if($rShwData2['statusproses'] == 'UNPROCESSED'){
				$status = $rShwData2['statusproses'];
			}else{
				if($rShwData2['posting_result'] == "1"){
					$status = "<font color='gray'>POSTED </font><i class='fa fa-thumbs-o-up'></i>";
				}else if($rShwData2['posting_result'] == "x"){
					$status = "<font color='blue'>Belum diproses</font>";
				}else if($rShwData2['posting_result'] == "0"){
					$status = "<font color='green'>Sudah diproses</font>";
				}
			}
			$tglspb=$rShwData2['tanggal'];
			OPEN_BOX('','<div class="judul"><div class="btnnavbar active refreshpage"><i class="fa fa-refresh"></i>  Proses Pengambilan Data</div></div>');
			echo "
			<br />
			<div class=\"container-col\">
			<div class='row'>
			<div class=\"col-5\">
				<div class=\"notif-frame\">
					<div class=\"title\"><i class='fa fa-file'></i>&nbsp;&nbsp;SPTBS</div>
					<div class=\"body-frame\">
						<table cellspacing=1 border=0 >
						<tr align=left><td>".$_SESSION['lang']['nospb']."</td><td>:</td><td>".$rShwData2['noreferensi']."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['tglNospb']."</td><td>:</td><td>".date("d M Y",strtotime($rShwData2['tanggal']))."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['kodeorg']."</td><td>:</td><td>".$rShwData2['kodeorg']."</td></tr>
						<tr align=left><td>".@$_SESSION['lang']['nokendaraan']."</td><td>:</td><td>".$rShwData2['nopol']."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['supir']."</td><td>:</td><td>".$rShwData2['kerani']."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['tujuan']."</td><td>:</td><td>".$rShwData2['penerimatbs']."</td></tr>
						</table>
						
					</div>
				</div>
			</div>
			<div class=\"col-2\" style='text-align: center;margin-top: 20px;color: #daeaff;'><i class='fa fa-truck' style='font-size: 50px;transform:scaleX(-1);' ></i>
			<div class='clearfix'></div>
			<i class='fa fa-arrow-right' style='font-size: 40px;'></i></div>
			<div class=\"col-5\">
				<div class=\"notif-frame\">
					<div class=\"title\"><i class='fa fa-file'></i>&nbsp;&nbsp;".$_SESSION['lang']['timbangan']."</div>
					<div class=\"body-frame\">
						<table cellspacing=1 border=0 >
						<tr align=left><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td><td>".$noTrans."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['tanggal']."</td><td>:</td><td>".date("d M Y",strtotime($rShwData2['tanggal']))."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['waktu']."</td><td>:</td><td>".date("H:i:s",strtotime($rShwData2['tanggal']))." - ".date("H:i:s",strtotime($rShwData2['tanggal']))."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['nokendaraan']."</td><td>:</td><td>".$rShwData2['nokendaraan']."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['supir']."</td><td>:</td><td>".$rShwData2['supir']."</td></tr>
						<tr align=left><td>".$_SESSION['lang']['netto']."</td><td>:</td><td>".number_format($rShwData2['netto'])." KG</td></tr>
						</table>
					</div>
				</div>
			</div>
			
			</div>
			<div class='clearfix'></div>
			</div>
			<br/>
			";

			$divisi = explode("/",$rShwData2['nospb'])[1];
			$QRreferace = $rShwData2[$coLspbHt];
			$tglBack = date("Y-m-d",strtotime("-6 days",strtotime($rShwData2['tanggal'])));
			$findChild  = "
				select 
					b.noreferensi,
					if(a.nospbref!='',a.nospbref,a.nopnnref) as qr_dt,
					DATE_FORMAT(SUBSTR(if(a.nospbref!='',a.nospbref,a.nopnnref),1,8),'%Y-%m-%d') as tgl_dt,
					if(a.nospbref!='',a.nospbref,concat(a.nopnnref,a.tph,a.nik,a.sesi)) as id,
					b.".$coLspbHt." as parentid, 
					CASE
					  WHEN a.nospbref = '' THEN '1'
					  WHEN a.nospbref != '' THEN '2'
					END as tipeid,b.".$coLspbHt." as qr_spb ,b.tanggal as tanggal,
					a.nospb as nospb,
					SUBSTR(a.blok ,1,4) as estate,b.nopol,b.kerani as supir,b.ffbdocument as ffbdocument,b.penerimatbs
					from ".$dbname.".kebun_spbdt_mobile a
					left join ".$dbname.".kebun_spbht_mobile b on b.nospb= a.nospb
					where b.tanggal >='".$tglBack."' and b.tanggal <='".$rShwData2['tanggal']."' and b.".$coLspbHt." != ''  and b.syn = '1' and b.kodeorg = '".$rShwData2['kodeorg']."' order by tipeid DESC,tanggal ASC";
			// echo $findChild;
			// exit();
			$rFindChild 	  = fetchdata($findChild);
			//echo "<div style=\"display:none;\">".$findChild."</div>";
			$dataHirarki2 = array();
			//$AllidTruck = array($QRreferace);
			$dataHirarki= array();
			$dk = array();
			$dp = array();
			$dp[$QRreferace]=$rShwData2;
			$dp[$QRreferace]['id'] = $QRreferace;
			$dp[$QRreferace]['parentid'] = '0';
			// $dp[$QRreferace]['child'] = array();
			$dp[$QRreferace]['list'] = array();
			$dataParent[]= $QRreferace;
			$tglMin = $rShwData2['tanggal'];
			$qrPNN = array();

			foreach ($rFindChild as $k => $v){
				$id = $v['id'];
				//$id = $v['id'];
				
				if(in_array($v['parentid'],$dataParent)){
					
					//=======================================
					if($v['parentid'] == $QRreferace){
						if($v['nospb'] == $rShwData2['nospb']){
							$rFindChild[$k]['id'] = $id;
							$rFindChild[$k]['divisi'] = $divisi;
							$dataHirarki2[] = $rFindChild[$k];
							
							
							
							if($v['tipeid'] == '2' or $v['tipeid'] == '3'){
								$dataParent[]=$id;
							}else{
								$qrPNN[] = $id;
								$qrPNNData[$id] = $rFindChild[$k];
								if($tglMin < $rFindChild[$k]['tgl_dt']){
									$tglMin = $rFindChild[$k]['tgl_dt'];
								}
								
							}
						
						}
					}else{
						$rFindChild[$k]['id'] = $id;
						$rFindChild[$k]['divisi'] = explode("/",$v['nospb'])[1];
						$dataHirarki2[] = $rFindChild[$k];
						
						if($v['tipeid'] == '2' or $v['tipeid'] == '3'){
							$dataParent[]=$id;
						}else{
							$qrPNN[] = $id;
							$qrPNNData[$id] = $rFindChild[$k];
							if($tglMin < $rFindChild[$k]['tgl_dt']){
								$tglMin = $rFindChild[$k]['tgl_dt'];
							}
							
						}
						
					}
					//get data detail Parent ================
					if($v['tipeid'] == '2' or $v['tipeid'] == '3'){
						$rFindChild[$k]['child'] = array();
						$rFindChild[$k]['list'] = array();
						$dp[$id] = $rFindChild[$k];
					}else if($v['tipeid'] == '1'){
						$dk[$id] = $rFindChild[$k];
					}
					
				}	
			}
			//print_r($qrPNN);
			//exit();
			$color_Random = array('#FF6B81','#AEF2DB','#E0F2AE','#FFC4BD','#E85746','#E7B2F7','#DBF7D5','#F7D5F3','#E8CC68','#F8FA6E','#C2FA6E','#6EFAC2','#333C6B','#878277','#7A0B38','#F2DEAE','#B395BF','#6A0B7A');
    $color_transport = array();
    $profileTruck = array();
    if(count($dataParent)> 0){
        $AllidTruckUniq = array_unique($dataParent);
        $selectProfT = "'0' as rit,a.nospb,a.divisi,a.kodeorg,a.tujuan,a.penerimatbs,a.tanggal,a.flag,a.updateby,a.kerani,trim(a.nopol) as nopol,a.syn,a.createby,a.createtime,a.ffbdocument,a.noreferensi as identifikasi";
        $findALLTRUCK = "
		SELECT a.".$coLspbHt." as id,".$selectProfT." from ".$dbname.".kebun_spbht_mobile as a
        where a.tanggal >='".$tglBack."' and a.tanggal <='".$rShwData2['tanggal']."' and ".$coLspbHt." != '' and a.syn = '1' and a.kodeorg = '".$rShwData2['kodeorg']."' and a.".$coLspbHt." in ('".implode("','",$AllidTruckUniq)."')";
        //echo "<div style=\"display:none;\">".$findALLTRUCK."</div>";
		//echo $findALLTRUCK;
        $rDataTRUCK	  = fetchdata($findALLTRUCK);
        $listORGTruck[] = $rShwData2['penerimatbs'];
		$listNopol = array();
        //ambil data SPB
        foreach ($rDataTRUCK as $v){
            $profileTruck[$v['id']] = $v;
            $listORGTruck[] = $v['kodeorg'];
            if($v['tujuan'] = '0'){
                $listORGTruck[] = $v['penerimatbs'];
            }
            $listORGTruck[] = explode("/",$v['nospb'])[1];
			$listNopol[] = trim($v['tanggal'].$v['nopol']);
        }
		if(count($listNopol) > 0){
			$checkRit =" select ".$coLspbHt." as id,trim(nopol) as nopol from kebun_spbht_mobile where concat(tanggal,trim(nopol)) in ('".implode("','",$listNopol)."') and syn = '1' order by nopol,SUBSTRING_INDEX(noreferensi,'-',1) asc;";
			$rDataRIT = $owlPDO->query($checkRit) or die(print " Gagal: ".PDOException::getMessage());
			$rDataRIT->setFetchMode(PDO::FETCH_ASSOC);
			$truckId = '';
			while ($res = $rDataRIT->fetch()) {
				if($truckId != $res['nopol']){
					$truckId = $res['nopol'];
					$ritNum = 1;
				}else{
					$ritNum++;
				}
				if(isset($profileTruck[$res['id']])){
					$profileTruck[$res['id']]['rit'] = $ritNum;
					
				}
			}
			
			//$rDataRIT = fetchdata($checkRit);
			
			/*if(count($rDataRIT) > 0){
				
				foreach ($rDataRIT as $k =>$v){
					if(isset($profileTruck[$v['id']])){
						$profileTruck[$v['id']]['rit'] = $v['rit'];
						
					}
				}
			}*/
		}
		
        //ambil warna
        foreach ($AllidTruckUniq as $k =>$v){
            $color_transport[$v] = @$color_Random[$k];
        }
        $listORGTruck = array_unique($listORGTruck);
        
    }
    $qrPNN = array_unique($qrPNN);
    //$dataTruck = makehierarchy($dataHirarki2,$dataParent,$QRreferace);
    
    $onclck = "onclick=\"editQR(this,'".$rShwData2['nospb']."','".$rShwData2['tanggal']."','".$rShwData2['nospb']."',event)\"";
    
    ?>
	<label class="switch">
    <section class="management-tree active">
        <div class="mgt-container">
            <div class="mgt-wrapper">
				<?php
				//if(a.nospbref!='',a.nospbref,concat(a.nopnnref,a.tph,a.nik,a.sesi))
				if(count($qrPNN)>0){
					$whereIN = " concat(b.noreferensi,a.tph,a.nik,a.sesi) in ('".implode("','",$qrPNN)."') and b.syn = '1'
					and b.kodeorg = '".$rShwData2['kodeorg']."' and b.tipetransaksi = 'PNN' and b.notransaksi is not null";
					$QRMatch = array();
					//fata panen utama
					$query 	 = "SELECT concat(b.noreferensi,a.tph,a.nik,a.sesi) as identifikasi,a.*,b.tanggal,b.jurnal as posting ,c.namakaryawan,ifnull(d.keterangan,'') as ket_tph FROM ".$dbname.".kebun_prestasi a 
					LEFT JOIN ".$dbname.".kebun_aktifitas b ON b.notransaksi = a.notransaksi and b.syn = '1'
					LEFT JOIN ".$dbname.".datakaryawan c ON a.nik = c.karyawanid 
					LEFT JOIN ".$dbname.".kebun_5tph d ON d.kode = a.tph 
					WHERE ".$whereIN;

					//fata panen mobile app
					$query 	 = "SELECT concat(b.noreferensi,a.tph,a.nik,a.sesi) as identifikasi,a.*,b.tanggal,b.jurnal as posting ,c.namakaryawan,ifnull(d.keterangan,'') as ket_tph FROM ".$dbname.".kebun_prestasi_mobile a 
					LEFT JOIN ".$dbname.".kebun_aktifitas_mobile b ON b.notransaksi = a.notransaksi and b.syn = '1'
					LEFT JOIN ".$dbname.".datakaryawan c ON a.nik = c.karyawanid 
					LEFT JOIN ".$dbname.".kebun_5tph d ON d.kode = a.tph 
					WHERE ".$whereIN;
					//echo $query;
					$rGetDataTrans 	  = fetchdata($query);	
				}
				//echo "<pre>";
                //print_r($rGetDataTrans);
				//echo "</pre>";
				//exit;
					$listORGTruck = array_unique($listORGTruck);
					
					$makeOtp = "SELECT kodeorganisasi,'' as descode1,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi in ('".implode("','",$listORGTruck)."')";
					$sapORG = fetchdata($makeOtp);
					
					if(count($sapORG) > 0){
						unset($listORGTruck);
						foreach($sapORG as $val){
							$listORGTruck[$val['kodeorganisasi']] = $val;
						}
					}
					unset($sapORG);
                                
                        
                        foreach ($profileTruck as $k => $v){
                            if($v['tujuan']=='0'){
                                $profileTruck[$k]['penerimatbs'] = $v['penerimatbs'];//$listORGTruck[$v['penerimatbs']]['descode1'];
                                //$profileTruck[$k]['divisi'] = @$listORGTruck[$v['kodeorg']]['descode1'].".".str_pad(@$listORGTruck[explode("/",$v['nospb'])[1]]['descode1'],2,"0",STR_PAD_LEFT);
                            }elseif($v['tujuan']=='5'){
                                $profileTruck[$k]['penerimatbs'] = $v['penerimatbs'];//@$listORGTruck[$v['kodeorg']]['descode1'].".".str_pad(@$listORGTruck[explode("/",$v['nospb'])[1]]['descode1'],2,"0",STR_PAD_LEFT).".".substr($v['penerimatbs'],-6,6);
                                //$profileTruck[$k]['divisi'] = @$listORGTruck[$v['kodeorg']]['descode1'].".".str_pad(@$listORGTruck[explode("/",$v['nospb'])[1]]['descode1'],2,"0",STR_PAD_LEFT);
                            }
                        }
                        foreach($dk as $k =>$v){
                            if(isset($profileTruck[$dk[$k]['parentid']])){
                                $dk[$k]['nospb_parent'] = $profileTruck[$dk[$k]['parentid']]['nospb'];
                            }else{
                                $dk[$k]['nospb_parent'] = "";
                            }
                        }
                        foreach($dk as $k =>$v){
                            if(isset($dp[$v['parentid']])){
                                //$dp[$v['parentid']]['child'][] = $dk[$k];
                                $dp[$v['parentid']]['list'][] = $k;
                            }
                        }
                        //unset($dk);
                        foreach($dp as $k =>$v){
                            if(isset($profileTruck[$dp[$k]['id']])){
                                $dp[$k]['profile'] = $profileTruck[$dp[$k]['id']];
                            }else{
                                $dp[$k]['profile'] = [];
                            }
                            if(isset($profileTruck[$dp[$k]['parentid']])){
                                $dp[$k]['nospb_parent'] = $profileTruck[$dp[$k]['parentid']]['nospb'];
                            }else{
                                $dp[$k]['nospb_parent'] = "";
                            }
                        }
                            function getHirarkiTruck($dttruck,$idParent,$case,$f='parentid',$c='id'){
                                switch($case){
                                    case'parent':
                                        $data= array();
                                        foreach($dttruck as $k =>$v){
                                            $d = array();
                                            if($dttruck[$k]['id'] == $idParent){
                                                $parentid = $dttruck[$k]['parentid'];
                                                $d = $dttruck[$k]['id'];
                                                unset($dttruck[$k]);
                                                $dataDT = getHirarkiTruck($dttruck,$parentid,'parent');
                                                $data[] = $d;
                                                if(count($dataDT) > 0){
                                                    $data = array_merge($data,$dataDT); 
                                                }
                                                
                                            }
                                        }
                                    break;
                                    case'child':
                                        $data['data'] = array();
                                        $data['list'] = array();
                                        foreach($dttruck as $k =>$v){
                                            $d = array();
                                            if($dttruck[$k][$f] == $idParent){
                                                $d = $dttruck[$k];
                                                //if($f == 'parentid'){
                                                    unset($dttruck[$k]);
                                                //}
                                                $dataDT = getHirarkiTruck($dttruck,$d[$c],'child','parentid');
                                                if(count($dataDT['data'])> 0){
                                                    $d['child'] = $dataDT['data'];
                                                }
                                                if(count($dataDT['list'])> 0){
                                                    if(isset($d['list']) and count($d['list']) > 0){
                                                        $d['list'] = array_merge($d['list'],$dataDT['list']);
                                                    }else{
                                                        $d['list'] = $dataDT['list'];
                                                    }
                                                }
                                                $data['data'][] = $d;
                                                if(isset($data['list']) and count($data['list']) > 0){
                                                    $data['list'] = array_merge($data['list'],$d['list']);
                                                }else{
                                                    $data['list'] = $d['list'];
                                                }
                                            }
                                        }
                                    break;
                                }
                                return $data;
                            }
                            $dpM = getHirarkiTruck($dp,$QRreferace,'child','id');
                            // echo '<pre>';
                            // print_r($dpM);
                            // echo '</pre>';
                            // exit();
                            echo itemChild($dpM['data'],$rGetDataTrans,$rShwData2);
                        
                    ?>
            </div>
        </div>
    </section>
        <i onclick="javascript:this.parentNode.querySelector('.management-tree').classList.toggle('active');" class="fa fa-chevron-down"></i>
    </label>
    	<?
   		 CLOSE_BOX();

		 //$count    = count($dataTruck);
			//$tglMin
			$weekDay = 7;
			$panen = array();
			$spb = array();
			$BatasTglAwal = $rShwData2['tanggal'];
			$BatasTglAkhir = $tglMin; //$intvlQR;
			$divisi = "";
		

		
		if(count($qrPNN)>0){
			$gtjjg 		= 0;
			$gtbrd 		= 0;
			$gtkg 		= 0;
			$gtkgnetto 	= 0;
			$gtkgkbn 	= 0;
			$noAnak 	= 0;
		    ?>
		     <br>
			<?php if($rShwData2['posting_result'] != '1'){ ?>
				<button class="mybutton" <?php echo $onclck; ?> style="display:none;margin-left:10px;"><i class="fa fa-plus"></i> Tambah</button>
				<br>
			<?	
			}
			echo "<br><table id='datapengiriman' cellspacing=1 border=0 class=sortable width=98% style='margin:auto;'>
					<thead style='position:sticky;top:0px;'>
					<tr class=rowheader>
						<th align=center>No</th>
						<th align=center>".$_SESSION['lang']['notransaksi']."</th>
						<th align=center>".$_SESSION['lang']['tanggal']."</th>
						<th align=center>".$_SESSION['lang']['blok']."</th>
						<th align=center>".$_SESSION['lang']['tph']."</th>
						<th align=center>".@$_SESSION['lang']['qrcode']."</th>
						<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
						<th align=center>".$_SESSION['lang']['janjang']."</th>
						<th align=center>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['kebun']."</th>
						<th align=center>".$_SESSION['lang']['brondolan']."</th>
						<th align=center>".$_SESSION['lang']['transport']."</th>
						</tr></thead>
					<tbody>";
					
					//$qrIN = " and a.sesi IN ('".implode("','",$qrPNN)."') ";
					//echo count($rGetDataTrans);
					//exit;
						$blokPanen = array();
						$no = 0;
						$anak = 0;
						$dataBelumLengkap = false;
						$dataSudahterproses = false;
						
						$color= "";
						$AllNik = array();

						$PANEN = array();
					if(count($rGetDataTrans) > 0){
						// $color = 'style="background-color:green;color:white"';
						
						foreach($rGetDataTrans as $k => $bar){
							$PANEN[$bar['identifikasi']] = $bar;
						}
						foreach($PANEN as $k => $bar){
							$color = 'style="background-color:green;color:white;cursor:pointer"';
							if(in_array($k,$qrPNN)){
								//Pembagian KG ke Blok dari data Karyawan
								$AllNik[$bar['nik'].$bar['tanggal']]['nik'] = $bar['nik'];
								$AllNik[$bar['nik'].$bar['tanggal']]['tanggal'] = $bar['tanggal'];
								$no++;
								$QRMatch[] = $k;
								$dataSPB = $qrPNNData[$k];
								//print_r($dataSPB );
								//break;
								if($rShwData2['posting_result'] != '1' and strlen($bar['nourut'])==7){
									$sesi =  substr($bar[$coLpnnDT], 0, 3).addZero(getdescodesap(substr($bar['kodeorg'], 0, 6)), 2).substr($bar[$coLpnnDT], 3, 7);

									$tglBack99 	= date("Y-m-d",strtotime("-6 days",strtotime($bar['tanggal'])));
									//$query99 	= 'SELECT count(b.sesi) AS jumlah FROM '.$dbname.'.kebun_aktifitas AS a LEFT JOIN '.$dbname.'.kebun_prestasi AS b ON b.notransaksi = a.notransaksi WHERE a.tanggal <= "'.$bar['tanggal'].'" AND a.tanggal >= "'.$tglBack99.'" AND b.sesi LIKE "'.substr($bar['sesi'], 0, 3).'%" AND b.sesi LIKE "%'.substr($bar['sesi'], 5, 4).'" and a.tipetransaksi = \'PNN\'';
									$query99 	= 'SELECT count(b.sesi) AS jumlah FROM '.$dbname.'.kebun_aktifitas_mobile AS a LEFT JOIN '.$dbname.'.kebun_prestasi_mobile AS b ON b.notransaksi = a.notransaksi WHERE a.tanggal <= "'.$bar['tanggal'].'" AND a.tanggal >= "'.$tglBack99.'" AND b.sesi LIKE "'.substr($bar['sesi'], 0, 3).'%" AND b.sesi LIKE "%'.substr($bar['sesi'], 5, 4).'" and a.tipetransaksi = \'PNN\'';
									
									$res99 		= fetchdata($query99);
									
									if ($res99[0]['jumlah'] > 1) {
										$color = 'style="background-color:orange;color:black;cursor:pointer" title="QR Panen double, Clik untuk mengganti QR Panen"';
									}
								}
								if($bar['flag'] == '1' and $rShwData2['posting_result'] != '1'){
									$docTerangkut = makeOption($dbname,'kebun_spbdt_mobile','nospb,identifikasi',"nopnnref='".$bar['noreferensi']."' and tanggalpanen = '".$bar['tanggal']."' and sesi = '".$dataSPB['qr_dt']."' and nospb != '".$rShwData2['nospb']."'");
									if(count($docTerangkut)>0){
										$color = 'style="background-color:red;color:black;cursor:pointer" title="QR Panen/Document ini sudah diproses oleh '.implode(",",$docTerangkut).'"';
										$dataSudahterproses = true;
									}
								}
								$namakaryawan=$bar['namakaryawan'];
								if($namakaryawan==''){
									$optbrg=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['nik']."'");
									$namakaryawan="Borongan (".$optbrg[$bar['nik']].")";
								}
								$getTrackQR = getHirarkiTruck($dp,$dk[$k]['parentid'],'parent');
								$trackQR = implode(",",$getTrackQR);
								$jmlTransQr = explode(",",$trackQR);
								$actOnclck = "";
								if($rShwData2['posting_result'] != '1' and strlen($bar['nourut'])==7){
									if(count($jmlTransQr) == 1){
										$actOnclck = $onclck;
									}else{
										//jika dari TPB atau double tidak bisa di delete;
										$actOnclck = "onclick=\"editQR(this,'".@$profileTruck[$dk[$k]['parentid']]['nospb']."','".@$profileTruck[$dk[$k]['parentid']]['tanggal']."','".$rShwData2['nospb']."',event)\"";
									}
								}
								$dott = "";
								foreach($getTrackQR as $idColor){
									$dott .= "&nbsp;<font size='4pt' color='".$color_transport[$idColor]."' >&#11044;</font>";
								}
								echo"<tr class=rowcontent id='row_".$no."' identifikasi='".$k."'>
									<td  align=center>".$no.".</td>
									<td id='trans_".$no."'>".$bar['notransaksi']."</td>
									<td align=center>".date('d F Y',strtotime($bar['tanggal']))."</td>
									<td align=center>".$bar['kodeorg']."</td>
									<td align=center>".substr($bar['ket_tph'],-2)."</td>
									<td id='qr_".$no."' nomor='".$no."' align='center' ".$color." track-qr='".$trackQR."' ".$actOnclck.">".$dataSPB['qr_dt']."</td>
									<td>".$namakaryawan."</td>
									<td align=right>".number_format($bar['hasilkerja'])."</td>
									<td align=right>".number_format($bar['bjr'],2)."</td>
									<td align=right>".number_format($bar['brondolan'],2)."</td>
									<td align=left style='background:#0c314b;'>".$dott."</td>
								</tr>";
								//<td align=center>".@$dataSPB['nopol']."</td>
								@$tjjg[$key['nospb']]+=$bar['hasilkerja'];
								@$tbrd[$key['nospb']]+=$bar['brondolan'];
								@$tkg[$key['nospb']]+=@$bar['kgwb'];
								@$tkgnetto[$key['nospb']]+=@$bar['kgwbnetto'];
								@$tkgkbn[$key['nospb']]+=$bar['bjr']*$bar['hasilkerja'];
								$gtjjg+=$bar['hasilkerja'];
								$gtbrd+=$bar['brondolan'];
								$gtkg+=@$bar['kgwb'];
								$gtkgnetto+=@$bar['kgwbnetto'];
								$gtkgkbn+=$bar['bjr']*$bar['hasilkerja'];
								
								$blokPanen[$bar['kodeorg']]['blok'] = substr($bar['ket_tph'],0,-2);
								$blokPanen[$bar['kodeorg']]['bjr'] = $bar['bjr'];
								@$blokPanen[$bar['kodeorg']]['janjang'] += (int)$bar['hasilkerja'];
								@$blokPanen[$bar['kodeorg']]['brondolan'] += $bar['brondolan'];
								
								$tgl1 = strtotime($tglspb); 
								$tgl2 = strtotime($bar['tanggal']);
								$jarak = $tgl1 - $tgl2;
								$selisih = $jarak / 60 / 60 / 24;
								if($selisih=='0'){
									@$blokPanen[$bar['kodeorg']]['P0']+=(int)$bar['hasilkerja'];
								}
								if($selisih=='1'){
									@$blokPanen[$bar['kodeorg']]['P1']+=(int)$bar['hasilkerja'];
								}
								if($selisih > '1'){
									@$blokPanen[$bar['kodeorg']]['P2']+=(int)$bar['hasilkerja'];
								}
							}else{
								
							}
						}
						$color = 'style="background-color:red;color:white;cursor:pointer"';
						$anak = $no;
						foreach($qrPNN as $v){
							if(!in_array($v,$QRMatch)){
								$no++;
								$dataSPB = $qrPNNData[$v];
								echo"<tr class=rowcontent id='row_".$no."' identifikasi='".$v."' $color>
								<td  align=center >".$no.".</td>
								<td  align=center colspan='4'>Data Tidak Ditemukan</td>
								<td id='qr_".$no."' nomor='".$no."' align=center ".$onclck.">".$dataSPB['qr_dt']."</td>
								<td align=center colspan='7'>Data Tidak Ditemukan</td>
								</tr>";
								$dataBelumLengkap = true;
							}
						}
					}else{
						echo"<tr class=rowcontent id='row_".$no."' ".@$color.">
							<td  align=center colspan='12'>Data Tidak Ditemukan</td>
						</tr>";
						
					}
					echo"</tbody></table><br>";
					
					if($dataBelumLengkap){
						echo "<table cellspacing=1 border=0 class=sortable width=98% style='margin:auto;'>";
						//id untuk mengunci tidak bisa diproses paksa
					}else{
						echo "<table id='datacomplete' cellspacing=1 border=0 class=sortable width=98% style='margin:auto;'>";
					}
					
					echo "	<thead>
								<tr class=rowheader>
									<th align=center>Total ".$_SESSION['lang']['janjang']."</th>
									<th align=center>Total ".$_SESSION['lang']['brondolan']."</th>
								</tr>
							</thead>
							<tbody>
								<tr class=rowcontent>
									<td align=center>".number_format($gtjjg)."</td>
									<td align=center>".number_format($gtbrd,2)."</td>
								</tr>
							</tbody>
						</table>";
					echo "<br>";	
					
					$beratBersih = $rShwData2['netto'];
					$beratMinBrondol = ($rShwData2['netto']-$gtbrd);
					//$gtjjg
					echo "<table  cellspacing=1 border=0 class=sortable width=98% style='margin:auto;'>
								<thead>
								<tr class=rowheader>
									<th align=center>".$_SESSION['lang']['blok']."</th>
									<th align=center>".$_SESSION['lang']['bjr']."</th>
									<th align=center>".$_SESSION['lang']['janjang']."</th>
									<th align=center>P0 (Janjang)</th>
									<th align=center>P1 (Janjang)</th>
									<th align=center><= P2 (Janjang)</th>
									<th align=center>Estimasi (".$_SESSION['lang']['kg'].")</th>
									<th align=center>%</th>
									<th align=center>".$_SESSION['lang']['brondolan']."</th>
									<th align=center>".$_SESSION['lang']['kg']."</th>
									<th align=center>Total ".$_SESSION['lang']['kg']."+".$_SESSION['lang']['brondolan']."</th>
								</tr>
							</thead>
							<tbody>";
					$blokJanjangTerbanyak['blok'] = "";
					$blokJanjangTerbanyak['janjang'] = 0;
					$totalBlok = count($blokPanen);
					$totalEst = 0;
					foreach($blokPanen as $k=>$v){
						if($v["janjang"]>$blokJanjangTerbanyak['janjang']){
							$blokJanjangTerbanyak['blok'] = $k;
							$blokJanjangTerbanyak['janjang'] = $v["janjang"];
						}
						$blokPanen[$k]['estimasikg'] = $v['janjang']*$v['bjr'];
						$totalEst += $blokPanen[$k]['estimasikg'];
					}
					$jumlahHasilpembagian = 0;
					foreach($blokPanen as $k=>$v){
						$pembagianBerat = round(($v['estimasikg']/$totalEst)*$beratMinBrondol);
						$blokPanen[$k]['pesentblok'] =($v['estimasikg']/$totalEst);
						$blokPanen[$k]['kgblok'] = $pembagianBerat;
						$jumlahHasilpembagian += $pembagianBerat;
					}
					$tambahSelisih = 0;
					if($beratMinBrondol != $jumlahHasilpembagian){
						$tambahSelisih = ($beratMinBrondol-$jumlahHasilpembagian);
					}
					$TotJanjangBlk =0;
					$TotBrondolanBlk =0;
					$TotHasilEstBlk =0;
					$TotHasilpembagianBlk =0;
					$TotpersentpembagianBlk =0;
					foreach($blokPanen as $k=>$v){
						if($k == $blokJanjangTerbanyak['blok']){
							$blokPanen[$k]['kgblok'] += $tambahSelisih;
						}
						echo "<tr class=rowcontent>
									<td align=center>".$k."</td>
									<td align=center>".number_format($v["bjr"],2)."</td>
									<td align=center>".$v["janjang"]."</td>
									<td align=center>".(@$v["P0"]==''?'-':$v["P0"])."</td>
									<td align=center>".(@$v["P1"]==''?'-':$v["P1"])."</td>
									<td align=center>".(@$v["P2"]==''?'-':$v["P2"])."</td>
									<td align=center>".number_format($v['estimasikg'],2)."</td>
									<td align=center>".number_format($v['pesentblok']*100,2)."</td>
									<td align=center>".number_format($v["brondolan"],2)."</td>
									<td align=center>".number_format($v['kgblok'],2)."</td>
									<td align=center>".number_format($v['kgblok']+$v["brondolan"],2)."</td>
								</tr>";
						$TotJanjangBlk += $v["janjang"];
						@$TotJanjangP0 += $v["P0"];
						@$TotJanjangP1 += $v["P1"];
						@$TotJanjangP2 += $v["P2"];
						$TotBrondolanBlk += $v["brondolan"];
						$TotHasilEstBlk += $v['estimasikg'];
						$TotHasilpembagianBlk += $v['kgblok'];
						$TotpersentpembagianBlk += $v['pesentblok'];
					}
					echo "<tr class=rowcontent style=\"background-color:#ffea32;\">
									<td align=center colspan='2'>Grand Total</td>
									<td align=center>".$TotJanjangBlk ."</td>
									<td align=center>".((int)@$TotJanjangP0==0?'-':@$TotJanjangP0)."</td>
									<td align=center>".((int)@$TotJanjangP1==0?'-':@$TotJanjangP1)."</td>
									<td align=center>".((int)@$TotJanjangP2==0?'-':@$TotJanjangP2)."</td>
									<td align=center>".number_format($TotHasilEstBlk,2)."</td>
									<td align=center>".number_format($TotpersentpembagianBlk*100,2)."</td>
									<td align=center>".number_format($TotBrondolanBlk,2)."</td>
									<td align=center>".number_format($TotHasilpembagianBlk,2)."</td>
									<td align=center>".number_format($TotHasilpembagianBlk+$TotBrondolanBlk,2)."</td>
								</tr>";
					echo "</tbody>
						</table>";
					echo "<br><center>";
					$justNik = [];
					$justNikKontrak = [];
					foreach($AllNik as $k => $v){
						$justNik = [$v['nik']];
					}
					if(count($justNik) > 0){
						// mencari yang NIK ny adalah kontrak
						$inSupp = "'".implode("','",$justNik)."'";
						$inKontrak = 'SELECT supplierid,namasupplier FROM '.$dbname.'.log_5supplier WHERE supplierid IN ("'.$inSupp.'") ';
						$resKon = fetchdata($inKontrak);
						if(count($resKon)>0){
							foreach($resKon as $k => $v){
								$justNikKontrak = [$v['supplierid']];
							}
						}
					}
					$AllNikKaryawan = $AllNik;
					if(count($justNikKontrak) > 0){
						$AllNikKaryawan = [];
						foreach($AllNik as $k => $v){
							if(!in_array($v['nik'],$justNikKontrak)){
								$AllNikKaryawan[$k] = $v;
							}
						}
					}
					
					foreach($AllNikKaryawan as $k => $v){
						$str9 = 'SELECT a.karyawanid FROM '.$dbname.'.sdm_absensidt AS a LEFT JOIN '.$dbname.'.sdm_absensiht AS b ON b.kodeorg = a.kodeorg AND b.tanggal = a.tanggal WHERE a.karyawanid = "'.$v['nik'].'" AND a.tanggal = "'.$v['tanggal'].'" AND b.posting = "1"';
						$res9 = fetchdata($str9);
						$no9   = count($res9);
	
						if ($bar['posting'] == 0) {
							$kuduposting += 1;
						}
						
					}
					
					if($rShwData2['posting_result'] == '1'){
							echo $status;
							echo "<br>";
					}else{
						//masi dalam tahap perbaikan
						if($dataBelumLengkap){
							//echo "<button class=mybutton onclick=\"alert('Data Panen Belum Lengkap');\">".$_SESSION['lang']['proses']."</button><br>";
						//}else if($kuduposting > 0){
						//	echo "<button class=mybutton onclick=\"alert('Data Pendukung Belum Semuanya Terposting (Panen & Absensi)');\">".$_SESSION['lang']['proses']."</button><br>";
						}else if($dataSudahterproses){
							//echo "<button class=mybutton onclick=\"alert('Data sudah ada yang terproses');\">".$_SESSION['lang']['proses']."</button><br>";
						}else{
							//echo "<button class=mybutton onclick=\"postingSPB('".$rShwData2['noreferensi']."', '".$noTrans."', '".$anak."')\">".$_SESSION['lang']['proses']."</button><br>";
						}
						//echo "<button class=mybutton >".$_SESSION['lang']['proses']."</button><br>";"
					}
					echo "<br>*Note : Satuan yang digunakan KG (Unit of Measurement is KG)</center><br>";
				}
		break;
		case 'moveHeader':
			//$noSpb = noreferensi from mobile
			$query  = "SELECT a.*,ifnull(b.nospb,'UNPROCESSED') as statusproses,ifnull(b.posting,'0') as posting_result 
					FROM ".$dbname.".kebun_spbht_mobile a
					left join ".$dbname.".kebun_spbht b on a.noreferensi = b.noreferensi
					where a.noreferensi='".$noSpb."' and a.syn = '1' ";
			$res 	= fetchdata($query);
			if(count($res)>0){
				foreach ($res as $bar) {
					if(($bar['statusproses'] != "UNPROCESSED" && $bar['posting_result'] == '0') or $bar['statusproses'] == "UNPROCESSED"){
						if($bar['statusproses'] != "UNPROCESSED"){
							$query = "DELETE FROM ".$dbname.".kebun_spbdt WHERE nospb = '".$bar['statusproses']."'";
							try{
								$owlPDO->exec($query);
							}catch(PDOException $e){
								print " Gagal  !: " . $e->getMessage() . "\n"; 
								die(); 
							}
						}
						/*
						$str1 = 'SELECT DISTINCT karyawanid FROM '.$dbname.'.kebun_spbtkbm WHERE nospb = "'.$noSpb.'"';
						$res1 = fetchdata($str1);
						foreach ($res1 as $bar1) {
							$str2 = 'SELECT a.karyawanid FROM '.$dbname.'.sdm_absensidt AS a LEFT JOIN '.$dbname.'.sdm_absensiht AS b ON b.kodeorg = a.kodeorg AND b.tanggal = a.tanggal WHERE (a.karyawanid = "'.$bar1['karyawanid'].'" OR a.karyawanid IS NOT NULL) AND a.tanggal = "'.$bar['tanggal'].'" AND b.posting = "1"';
							$res2 = fetchdata($str2);
							$no2  = count($res2);
							if ($no2 < 1) {
								exit('Warning: Karyawan ('.getkaryawansap($bar1['karyawanid']).') '.getNamaKaryawan($bar1['karyawanid']).' Belum Absen'); 
							}
						}*/
						if($bar['statusproses'] == "UNPROCESSED"){
							$identifyNoSPB = substr($bar['nospb'],7);
							
							$query = "INSERT INTO ".$dbname.".kebun_spbht (nospb,noreferensi,kodeorg,tujuan,penerimatbs,tanggal,kerani,updateby,posting) ";
							$query .= "SELECT 
							concat(LPAD((CAST(SUBSTRING_INDEX(MAX(nospb),'/',1) as SIGNED)+1),7,'0'),'".$identifyNoSPB."'),
							'".$bar['noreferensi']."','".$bar['kodeorg']."','".$bar['tujuan']."','".$bar['penerimatbs']."','".$bar['tanggal']."','".$bar['kerani']."','".$_SESSION['standard']['userid']."','0'
							FROM `kebun_spbht` where nospb like '%".$identifyNoSPB."'";
							try{
								$owlPDO->exec($query);
							}catch(PDOException $e){
								print " Gagal  !: " . $e->getMessage() . "\n"; 
								die(); 
							}
						}
					}else{
						print " Gagal  !: Data Sudah terposting\n"; 
						exit();
					}	
				}
			}else{
				print " Gagal  !: Data tidak ditemukan\n"; 
				exit();
			}
		break;	
		case 'moveDetail':
			$check['param'] = $_POST;
			$result['error'] = false;
			$result['message'] = "";
			$getnoSpb = "";
			$trackqr	 = checkPostGet('trackqr','');
			$query  = "SELECT a.*, b.nikmandor, b.tanggal FROM ".$dbname.".kebun_prestasi a LEFT JOIN ".$dbname.".kebun_aktifitas b ON b.notransaksi = a.notransaksi WHERE a.notransaksi = '".$prestasi."' AND concat(a.noreferensi,a.tph,a.nik,a.sesi) = '".$qr."' and b.tipetransaksi = 'PNN' ";
			$res 	= fetchdata($query);
			//Test data
			if(count($res) == 0){
				$queryMobile  = "SELECT a.*, b.nikmandor, b.tanggal FROM ".$dbname.".kebun_prestasi_mobile a LEFT JOIN ".$dbname.".kebun_aktifitas_mobile b ON b.notransaksi = a.notransaksi WHERE a.notransaksi = '".$prestasi."' AND concat(a.noreferensi,a.tph,a.nik,a.sesi) = '".$qr."' and b.tipetransaksi = 'PNN' ";
				$res 	= fetchdata($queryMobile);
			}
			$check['query'] = $query;
			if(count($res) > 0){
				//Cek data sudah proses atau belom
				if($res[0]['flag'] == '1'){
					unprosesPNN($noSpb);
					$result['error'] = true;
					$result['message'] = " Gagal Proses !: ".$qr." ini sudah pernah diangkut kendaraan lain";
				}
				if($result['error'] == false){
					$queryDtArr = array();
					$query2  = "SELECT nospb FROM ".$dbname.".kebun_spbht WHERE noreferensi = '".$noSpb."' limit 1";
					$res2 	= fetchdata($query2);
					$identifikasi = $noSpb;
					if(count($res2) > 0){
						$getnoSpb = $res2[0]['nospb'];
						$queryDt  = "SELECT jjg,brondolan,bjr FROM ".$dbname.".kebun_spbdt WHERE nospb='".$getnoSpb."' and tanggalpanen= '".$res[0]['tanggal']."' and blok = '".$res[0]['kodeorg']."' limit 1";
						$docTerangkut = fetchdata($queryDt);
						
						if(count($docTerangkut) > 0){
							//update
							$jjg = ($docTerangkut[0]['jjg']+$res[0]['hasilkerja']);
							$brd = ($docTerangkut[0]['brondolan']+$res[0]['brondolan']);
							$kgbjr = ($docTerangkut[0]['bjr']*$jjg);
							$queryDt = "UPDATE ".$dbname.".kebun_spbdt set jjg = '".$jjg."',brondolan='".$brd."' where nospb='".$getnoSpb."' and tanggalpanen= '".$res[0]['tanggal']."' and blok = '".$res[0]['kodeorg']."'";
							$check['query'] = $queryDt;
						}else{
							//insert
							$kgbjr = $res[0]['hasilkerjakg'];
							$queryDt = "INSERT INTO ".$dbname.".kebun_spbdt (nospb,tanggalpanen,blok,jjg,kgwb,kgwbnetto,bjr,brondolan,totalkg,kgbjr) values 
							('".$getnoSpb."','".$res[0]['tanggal']."','".$res[0]['kodeorg']."','".$res[0]['hasilkerja']."','0','0','".$res[0]['bjr']."','".$res[0]['brondolan']."','0','".$kgbjr."')";
							//$check['query'] = $queryDt;
						}
						try{
						
							$owlPDO->exec($queryDt);
						}catch(PDOException $e){
							unprosesPNN($noSpb);
							$result['error'] = true;
							$result['message'] = " Gagal Proses !: " . $queryDt. "\n"; 
						}
					}else{
						$result['error'] = true;
						$result['message'] = " Gagal Proses !: SPTBS ".$noSpb." ini, Header Belum terbentuk, silahkan re-prosess";
					}
					
						if($result['error'] == false){
							$sUpd = "UPDATE ".$dbname.".kebun_prestasi SET flag = '1' WHERE notransaksi = '".$res[0]['notransaksi']."' and concat(noreferensi,tph,nik,sesi) = '".$qr."'";
							$owlPDO->exec($sUpd);
						}else{
							//$sUpd = "UPDATE ".$dbname.".kebun_prestasi SET flag = '0' WHERE notransaksi = '".$bar['notransaksi']."' and concat(noreferensi,tph,nik,sesi) = '".$qr."'";
							if($getnoSpb!= ""){
								unprosesPNN($noSpb);
							}						
						}
						
					}
			}
			if($result['error'] == true){
				print $result['message'];
				die(); 
			}
			echo json_encode($check);
		break;
		case 'PostingData':
			$query2  = "SELECT nospb FROM ".$dbname.".kebun_spbht WHERE noreferensi = '".$noSpb."' limit 1";
			$res2 	= fetchdata($query2);
			if(count($res2) > 0){
				$getnoSpb = $res2[0]['nospb'];
				$sUpd = "UPDATE ".$dbname.".pabrik_timbangan SET status_spb = '1',nospb='".$getnoSpb."' WHERE notransaksi = '".$noTrans."'";
				$owlPDO->exec($sUpd);
				$sUpd = "UPDATE ".$dbname.".kebun_spbht_mobile SET flag = '1' WHERE noreferensi = '".$noSpb."'";
				$owlPDO->exec($sUpd);
			}
		break;
        default:
        break;
    }

    function getChildDataQR($qr, $tanggal){
		global $ChildSPB;
		global $jmlAnak;

		$intvl 	 = strtotime($tanggal .' -6 days');
		$intvl 	 = date("Y-m-d", $intvl);

		$query  = "SELECT a.tanggal, b.nospb, b.nospbref, if(b.nopnnref!='','Normal','Double') as tipe FROM ".$dbname.".kebun_spbht_mobile a INNER JOIN ".$dbname.".kebun_spbdt_mobile b ON a.nospb = b.nospb WHERE a.noreferensi = '".$qr."' AND a.tanggal >= ".$intvl." and a.tanggal <= '".$tanggal."' and a.syn = '1' ORDER BY b.tipe DESC";
		$res 	  = fetchdata($query);
		$count    = count($res);
		$no 	  = 0;
		$temp 	  = array();
		if ($count > 0) {
			foreach ($res as $bar) {
				if ($bar['tipe'] == 'Normal') {
					$ChildSPB[$qr]['nospb'] 				= $bar['nospb'];
					$ChildSPB[$qr]['data'][$no]['qr'] 		= $bar['nospbref'];
					$ChildSPB[$qr]['data'][$no]['tanggal'] 	= $bar['tanggal'];

					$no++;
					$jmlAnak++;
				} else {
					getChildDataQR($bar['nospbref'], $bar['tanggal']);
				}
			}
		} else {
			$ChildSPB[$qr]['nospb'] 				= 'Data Tidak Ditemukan';
			$ChildSPB[$qr]['data'][$no]['qr'] 		= $qr;
			$ChildSPB[$qr]['data'][$no]['tanggal'] 	= $tanggal;
		}
	}

	function getParentDataQR($qr, $tanggal){
		global $dbname;
		global $parentSPB;

		$intvl 	 = strtotime($tanggal .' -6 days');
		$intvl 	 = date("Y-m-d", $intvl);

		$query  = "SELECT b.nospb, b.nospbref, if(b.nopnnref!='','Normal','Double') as tipe FROM ".$dbname.".kebun_spbht_mobile a INNER JOIN ".$dbname.".kebun_spbdt_mobile b ON a.nospb = b.nospb WHERE a.qr = '".$qr."' AND a.tanggal >= '".$intvl."' AND a.tanggal <= '".$tanggal."' and a.syn = '1' ORDER BY tipe DESC";
		$res 	  = fetchdata($query);
		$count    = count($res);
		if ($count > 0) {
			foreach ($res as $bar) {
				if ($bar['tipe'] != 'Normal') {
					$queryInner  = "SELECT a.tanggal, a.nospb, a.noreferensi FROM ".$dbname.".kebun_spbht_mobile a WHERE a.noreferensi = '".$bar['nospbref']."' AND a.tanggal >= '".$intvl."' AND a.tanggal <= '".$tanggal."' and a.syn = '1' ";
					$resInner 	= fetchdata($queryInner);
					$temp = array();
					foreach ($resInner as $barInner) {
						$temp['nospb'] 		= $barInner['nospb'];
						$temp['qr'] 		= $barInner['noreferensi'];
						$temp['tanggal'] 	= $barInner['tanggal'];

						$parentSPB[] 		= $temp;
						getParentDataQR($barInner['noreferensi'], $barInner['tanggal']);
					}
				}
			}
		}

		return $parentSPB;
	}

	function getChildDataTPHQR($qr, $tanggal){
		global $dbname;
		global $ChildSPB;
		global $jmlAnak;

		$intvl 	 = strtotime($tanggal .' -6 days');
		$intvl 	 = date("Y-m-d", $intvl);

		$query  = "SELECT a.tanggal, b.nospb, b.nospbref, if(b.nopnnref!='','Normal','Double') as tipe  FROM ".$dbname.".kebun_spbht_mobile a INNER JOIN ".$dbname.".kebun_spbdt_mobile b ON a.nospb = b.nospb WHERE a.noreferensi = '".$qr."' AND a.tanggal >= ".$intvl." and a.tanggal <= '".$tanggal."' and a.syn = '1' ";
		$res 	  = fetchdata($query);
		$count    = count($res);
		$temp 	  = array();
		if ($count > 0) {
			foreach ($res as $bar) {
				if ($bar['tipe'] == 'Normal') {
					$temp['qr'] 		= $bar['nospbref'];
					$temp['tanggal'] 	= $bar['tanggal'];

					$ChildSPB[] 		= $temp;
				} else {
					getChildDataTPHQR($bar['nospbref'], $bar['tanggal']);
				}
			} 
		}
	}

    function searchForId($id, $array) {
		global $arrayAnak;

	    foreach ($array as $key => $val) {
	       if ($val === $id) {
	           return $key;
	       }
	   }
	   return null;
	}

	function getParentTrans($dataArray,$childID){
		$data['id'] = array();
		$data['nospb'] = "";
		$data['tanggal'] = "";
		for($i=0; $i<count($dataArray); $i++){
			if($dataArray[$i]['id'] == $childID){
				$data['id'][] = $dataArray[$i]['parentid'];
				$data['nospb'] = $dataArray[$i]['nospb'];
				$data['tanggal'] = $dataArray[$i]['tanggal'];
				if(getParentTrans($dataArray,$dataArray[$i]['parentid'])['id']){
					$d = getParentTrans($dataArray,$dataArray[$i]['parentid']);
					$data['id'][] = array_shift($d['id']);
				}
				break;
			}
		}
		return $data;
	}
	function makehierarchy($dataArray,$dataParent,$parentID){
		$data = array();
		for($i=0; $i<count($dataParent); $i++){
			if($dataParent[$i] == $parentID){
				
			}
		}
		for($i=0; $i<count($dataArray); $i++){
			if($dataArray[$i]['parentid'] == $parentID){
				if($dataArray[$i]['tipeid'] == '2' or $dataArray[$i]['tipeid'] == '3'){
					$dataArray[$i]['child'] = makehierarchy($dataArray,$dataArray[$i]['id']);
				}
				$data[] = $dataArray[$i];
				//break;
			}
		}
		return $data;
	}

	function itemChild($item,$prestasi,$nospb_parent,$first=0){
		global $coLpnnDT;
		global $color_transport;
		$html = '';
		if($first != 0){
			$html = '<div class="mgt-item-children">';
		}
		$chld = "";
		if(count($item) > 1){
			$chld = 'many';
		}
		foreach($item as $k=>$v){
			if($first != 0){
			$html .= '<div class="mgt-item-child '.$chld.'">';
			}
			$html .= '<div class="mgt-item">';
			$dataSearch = array();
			if(isset($v['child']) and count($v['child']) > 0){
				$html .= '<div class="mgt-item-parent">';
			}
			if(isset($v['list']) ){
				$dataSearch = $v['list'];
			}
			$totalJJg = 0;
			$totalBRD = 0;
			//print_r($dataSearch);
			$sudahdi_hitung = array();
			if(count($dataSearch) > 0){
				foreach($prestasi as $key => $val){
					if(in_array($val['identifikasi'],$dataSearch) and !in_array($val['identifikasi'],$sudahdi_hitung)){
						$totalJJg += $val['hasilkerja'];
						$totalBRD += $val['brondolan'];
						$sudahdi_hitung[] = $val['identifikasi'];
					};
				}
			}
			$profile = @$v['profile'];
			//print_r($profile);
			$editing = "";
			$html .= '<div id="'.$v['id'].'" class="person">';
			$html .= '<img src="'.@$profile['ffbdocument'].'" alt="">';
			$html .= '<div class="search" onclick="selectDataListDetail(\''.implode(",",$dataSearch).'\');"><i class="fa fa-search"></i></div>';
			if($nospb_parent['posting_result'] != '1'){
				$editing = 'onclick="editQRTrans(\''.@$profile['tanggal'].'\',\''.@$profile['nospb'].'\',\''.@$nospb_parent['nospb'].'\');" ';
				//$html .= '<div class="edit" '.$editing.'><i class="fa fa-pencil"></i></div>';
			}
			$html .= '<p class="name" style="border-left:30px '.@$color_transport[$v['id']].' solid;">';
			$html .= '<i class="fa fa-truck"></i> [Rit ke-'.@$profile['rit'].']<br>';
			$html .= @$profile['nopol'].' / '.@$profile['kerani'].'<br>';
			$html .= '<i class="fa fa-map-marker"></i> ';

			if(isset($profile['penerimatbs']) and $profile['penerimatbs'] != "UNDEFINED"){
				$html .= $profile['divisi'];
			}else{
				$html .= $v['kodeorg'];
			}
			
			$html .=' <i class="fa fa-arrow-right"></i> '.@$profile['penerimatbs'].'<br>';
			$html .= $totalJJg.' Jjg / '.$totalBRD.' Kg <br>';
			$html .= '</p>';
			$html .= '</div>';
			if(isset($v['child']) and count($v['child']) > 0){
				$html .= '</div>';
				$html .= itemChild($v['child'],$prestasi,$nospb_parent,1);
			}
			$html .= '</div>';
			if($first != 0){
				$html .= '</div>';
			}
		}
		if($first != 0){
		$html .= '</div>';
		}
		return $html;
	}
	
	function itemTruck($dataArr,$dataDt,$profile){
		$data = array();
		$res = "";
		foreach($dataDt as $k=>$v){ 
			if(isset($profile[$v['id']]) and ($v['tipeid'] == '2' or $v['tipeid'] == '3')){
				$child1 = findTruck($dataArr,$profile[$v['id']],$v['tipe']);
				if(isset($data[$v['id']])){
					$data[$v['id']]['list'] = array_merge($data[$v['id']]['list'],$child1['list']);
				}else{
					$data[$v['id']] = $child1;
				}
				
				if(isset($v['child']) and count($v['child']) > 0){
					$data[$v['id']]['child'] = itemTruck($dataArr,$v['child'],$profile);
				}
			}else if(!isset($profile[$v['id']]) and ($v['tipeid'] == '2' or $v['tipeid'] == '3')){
				$child1 = MissTruck($dataArr,$v['id'],$v['tipe']);
				
				$data[$v['id']] = $child1;
				if(isset($v['child']) and count($v['child']) > 0){
					$data[$v['id']]['child'] = itemTruck($dataArr,$v['child'],$profile);
				}
			}
		}
		
		return $data;
	}

	function MissTruck($dataArray,$id,$tipe){
		$tipe = $tipe;
		$d = array();
		$d['tipe'] = $tipe;
		for($i=0; $i<count($dataArray); $i++){
			if($dataArray[$i]['id'] == $id){
				$d['id'] = $id;
				$d['nospb_parent'] = $dataArray[$i]['nospb'];
				$d['nospb'] = "UNDEFINED";
				$d['tipe'] = $dataArray[$i]['tipe'];
				$d['divisi'] = $dataArray[$i]['divisi'];
				$d['penerimatbs']= $dataArray[$i]['penerimatbs'];
				$d['tanggal'] = "UNDEFINED";
				$d['ffbdocument'] = "UNDEFINED";
				$d['drop']= "UNDEFINED";
				$d['nopol']= "UNDEFINED";
				$d['supir']= "UNDEFINED";
				$d['list'] = array();
			}
		}
		return $d;
	}
	function findTruck($dataArray,$dTruck,$tipe){
		$tipe = $tipe;
		$d = array();
		$d['tipe'] = $tipe;
		for($i=0; $i<count($dataArray); $i++){
			if($dataArray[$i]['id'] == $dTruck['id']){
				$d['id'] = $dTruck['id'];
				$d['nospb_parent'] = $dataArray[$i]['nospb'];
				$d['nospb'] = $dTruck['nospb'];
				$d['tipe'] = $dataArray[$i]['tipe'];
				$d['divisi'] = $dataArray[$i]['divisi'];
				$d['penerimatbs']= $dataArray[$i]['penerimatbs'];
				$d['tanggal'] = $dTruck['tanggal'];
				$d['ffbdocument'] = $dTruck['ffbdocument']."";
				$d['drop']= $dTruck['penerima'];
				$d['nopol']= $dTruck['nopol'];
				$d['supir']= $dTruck['kerani'];
			}else if($dataArray[$i]['parentid'] == $dTruck['id']){
				if($dataArray[$i]['tipeid'] == '1'){
					$d['list'][] = $dataArray[$i]['id'];
				}
			}
		}
		//$data[] = $d;
		return $d;
	}
	function unprosesPNN($noSpb){
		global $dbname;
		global $owlPDO;
		$query  = "UPDATE ".$dbname.".kebun_spbdt_mobile b 
			left join ".$dbname.".kebun_prestasi c on concat(a.nopnnref,a.tph,a.nik,a.sesi) = concat(c.noreferensi,c.tph,c.nik,c.sesi) 
			set c.flag = '0' where b.noreferensi='".$noSpb."'";
		$owlPDO->exec($query);
		deleteGenerateSPB($noSpb);
		
	}
	function deleteGenerateSPB($noSpb){
		global $dbname;
		global $owlPDO;
		$result = "";
		$query  = "SELECT *
					FROM ".$dbname.".kebun_spbht 
					left join ".$dbname.".kebun_spbht b on a.noreferensi = b.noreferensi
					where noreferensi='".$noSpb."' limit 1 ";
		$res 	= fetchdata($query);
		if(count($res)> 0){
			foreach ($res as $bar) {
				if($bar['statusproses'] != "UNPROCESSED" && $bar['posting_result'] == '0' || $bar['statusproses'] == "UNPROCESSED"){
					$query1 = "DELETE FROM ".$dbname.".kebun_spbdt WHERE nospb = '".$bar['nospb']."';";
					$query1 = "DELETE FROM ".$dbname.".kebun_spbht WHERE nospb = '".$bar['nospb']."';";
					try{
						$owlPDO->exec($query);
						$result = "DONE";
					}catch(PDOException $e){
						$result = " Gagal  !: " . $e->getMessage() . "\n";
						die(); 
					}
				}
			}
		}else{
			$result = "Error : Data SPB tidak ditemukan";
		}
		return $result;
	}
?>