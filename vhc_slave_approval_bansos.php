<?php
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	// $validasiGetMobile = explode(" ", isset($_GET['par']));
	if($validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
	$session_id = $_SESSION['standard']['userid'];
}
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$karyawanid=checkPostGet('karyawanid', $session_id);
$method = checkPostGet('method', '');
$proses = checkPostGet('proses', '');
$level = checkPostGet('level', '');
$notransaksi = checkPostGet('notransaksi', '');
$kolom = checkPostGet('kolom', '');
$comment = checkPostGet('comment', '');
$userid = checkPostGet('userid', '');
$tglskrng = date("Y-m-d H:i:s");
$arrstatus = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');
switch ($method) {
case 'getdetail':
	switch ($proses) {
	case'BANSOS':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['kategori']."</td>
			<td align=center>Lokasi Pemesan</td>
			<td align=center>".$_SESSION['lang']['rupiah']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		$countApp = getCountApproval('BANSOS');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('BANSOS');
		$str = "select distinct(a.notransaksi) as notransaksi, a.*,b.kodeorg, b.tanggal,b.kategori,b.lokasipemesan,
			sum(b.rupiah) as rupiah from ".$dbname.".approval a
			left join ".$dbname.".lgl_bansos b on a.notransaksi = b.notransaksi
			where a.jenispersetujuan='BANSOS' and a.status='0' and a.karyawanid='".$karyawanid."' group by notransaksi order by a.tanggal asc, notransaksi asc";
		// exit('error'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$kodeorg = $bar['kodeorg'];
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$kodeorg."'");
			$optNmlokasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['lokasipemesan']."'");
			$optKat = makeOption($dbname, 'lgl_kategoribansos', 'kode,nama', "kode='".$bar['kategori']."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".substr($bar['tanggal'],0,7)."</td>
				<td align=left>".$kodeorg." - ".$optNmOrg[$kodeorg]."</td>
				<td align=left>".$optKat[$bar['kategori']]."</td>
				<td align=left>".$bar['lokasipemesan']." - ".$optNmlokasi[$bar['lokasipemesan']]."</td>
				<td align=left>".@number_Format($bar['rupiah'])."</td>
				<td align=center>
				<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"htmlbansos('".$bar['notransaksi']."','".$kodeorg."','".$bar['tanggal']."','html');\">
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			for ($i = 1; $i <= $countApp; $i++) {
				@$arrDetail = detailApprove($i,$bar['notransaksi'],'BANSOS');
				if($arrDetail['status']=='' || $arrDetail['status']==0){
					$showaction = $showaction + 1;
				}
				if($arrDetail['karyawanid']==$karyawanid){
					$level = $arrDetail['level'];
					break;
				}
				
				
				// $arrDetail = detailApprove($i, $bar['notransaksi'], 'BANSOS',$bar['karyawanid']);
				// if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
					// $level = $arrDetail['level'];
					// $showaction = 1;
					// if ($i >= 2) {
						// $countubahjumlah = 1;
					// }
				// }
			}
			if($showaction!=$level || $level==1){
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatabansos('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakbansos('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2 style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {

				$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='BANSOS' and tipe='1' and 
				tipekaryawan='".$_SESSION['empl']['tipekaryawan']."' and kodeunit='".$kodeorg."' and level='".$i."'";
				$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
				$resap->setFetchMode(PDO::FETCH_ASSOC);
				$barap=$resap->fetch();
				$leveldireksi=$barap['level'];

				$arrDetail = detailApprove($i, $bar['notransaksi'], 'BANSOS');	
				if ($leveldireksi=='') {
					if ($arrDetail['nama'] != '') {
						$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
					} else {
						$arrDetail = detailApprove($i, $bar['notransaksi'], 'BANSOS');
						$tab.="<td style='text-align:center'>-</td>";
					}
				}else{
					$strcount = "select count(level) as jumlahapp from ".$dbname.".approval where jenispersetujuan='BANSOS' and level='".$i."' and notransaksi='".$bar['notransaksi']."'";
					$rescount = $owlPDO->query($strcount)or die(print " Gagal: ".PDOException::getMessage());
					$rescount ->setFetchMode(PDO::FETCH_ASSOC);
					$barcount = $rescount->fetch();

					if ($barcount['jumlahapp']==1) {
						$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
					}else{
						$tab.="<td style='text-align:center'>DIREKSI</td>";
					}
				}				
			}
			$tab.="</tr>";
		}
		$tab.="</tbody>
			<tfoot>
			</tfoot>
			</table>
			</fieldset>";
	break;
	
	case'PP':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['kategori']."</td>
			<td align=center>Lokasi Pemesan</td>
			<td align=center>".$_SESSION['lang']['rupiah']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		$countApp = getCountApproval('PP');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('PP');
		$str = "select distinct(a.notransaksi) as notransaksi, a.*,b.kodeorg, b.tanggal,b.kategori,b.lokasipemesan,
			sum(b.rupiah) as rupiah from ".$dbname.".approval a
			left join ".$dbname.".lgl_bansos b on a.notransaksi = b.notransaksi
			where a.jenispersetujuan='PP' and a.status='0' and a.karyawanid='".$karyawanid."' group by notransaksi order by a.tanggal asc, notransaksi asc";
		// exit('error'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$kodeorg = $bar['kodeorg'];
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$kodeorg."'");
			$optNmlokasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['lokasipemesan']."'");
			$optKat = makeOption($dbname, 'lgl_kategoribansos', 'kode,nama', "kode='".$bar['kategori']."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".substr($bar['tanggal'],0,7)."</td>
				<td align=left>".$kodeorg." - ".$optNmOrg[$kodeorg]."</td>
				<td align=left>".$optKat[$bar['kategori']]."</td>
				<td align=left>".$bar['lokasipemesan']." - ".$optNmlokasi[$bar['lokasipemesan']]."</td>
				<td align=left>".@number_Format($bar['rupiah'])."</td>
				<td align=center>
				<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"htmlbansos('".$bar['notransaksi']."','".$kodeorg."','".$bar['tanggal']."','html');\">
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'PP',$bar['karyawanid']);
				if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
					$level = $arrDetail['level'];
					$showaction = 1;
					if ($i >= 2) {
						$countubahjumlah = 1;
					}
				}
			}
			if ($showaction == 1) {
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatabansos('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakbansos('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=4>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {

				$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='PP' and tipe='1' and 
				tipekaryawan='".$_SESSION['empl']['tipekaryawan']."' and kodeunit='".$kodeorg."' and level='".$i."'";
				$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
				$resap->setFetchMode(PDO::FETCH_ASSOC);
				$barap=$resap->fetch();
				$leveldireksi=$barap['level'];

				$arrDetail = detailApprove($i, $bar['notransaksi'], 'PP');	
				if ($leveldireksi=='') {
					if ($arrDetail['nama'] != '') {
						$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
					} else {
						$arrDetail = detailApprove($i, $bar['notransaksi'], 'PP');
						$tab.="<td style='text-align:center'>-</td>";
					}
				}else{
					$strcount = "select count(level) as jumlahapp from ".$dbname.".approval where jenispersetujuan='PP' and level='".$i."' and notransaksi='".$bar['notransaksi']."'";
					$rescount = $owlPDO->query($strcount)or die(print " Gagal: ".PDOException::getMessage());
					$rescount ->setFetchMode(PDO::FETCH_ASSOC);
					$barcount = $rescount->fetch();

					if ($barcount['jumlahapp']==1) {
						$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
					}else{
						$tab.="<td style='text-align:center'>DIREKSI</td>";
					}
				}				
			}
			$tab.="</tr>";
		}
		$tab.="</tbody>
			<tfoot>
			</tfoot>
			</table>
			</fieldset>";
	break;
	}
break;
	case'get_form_approval':
		$tab="";
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[3];

		// $str = "select kodeorg, jenis from ".$dbname.".lgl_bansos where `notransaksi`='".$notransaksi."'";
		$str = "select kodeorg from ".$dbname.".lgl_bansos where `notransaksi`='".$notransaksi."'";
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$koderorg=$bar['kodeorg'];
		// $jenistrk = ($bar['jenis']=='BANSOS'?'BANSOS':'PP');
		$jenistrk = 'BANSOS';
		$countApp = getCountApproval($jenistrk,$koderorg);
		
		$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='".$jenistrk."' and tipe='1' and 
		tipekaryawan='".$_SESSION['empl']['tipekaryawan']."' and kodeunit='".$bar['kodeorg']."' and level='".$kolom."' ";
		$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
		$resap->setFetchMode(PDO::FETCH_ASSOC);
		$barap=$resap->fetch();
		$leveldireksi=$barap['level'];

		for($i=1;$i<=$countApp;$i++){
			$arrDetail = detailApprove($i,$notransaksi,$jenistrk,$karyawanid);
			if($karyawanid==$arrDetail['karyawanid']){
				if ($leveldireksi=='') {
					if($i == $countApp){
						$tab.="<div id=approve>
							<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<table cellspacing=1 border=0>
								<tr>
									<td colspan=3>Approved</td>
								</tr>
								<tr>
									<td colspan=3><hr></td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['note']."</td>
									<td>:</td>
									<td>
										<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
									</td>
								</tr>
								<tr>
									<td colspan=3 align=center>
										<button id=Ajukan class=mybutton onclick=nextapprovalbansos('approved','".$jenistrk."') >Approved</button>
									</td>
								</tr>
							</table>
	                    </div>";
					}else{
						$level = $i+1;
						$arrListApp = listApprove($level,$jenistrk,$koderorg);
						foreach($arrListApp as $key=>$val){
							$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
						}
						$tab.="<div id=test style=display:block>
	                        <input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<input hidden id=kolom value=".$_POST['kolom']."  />
	                        <table cellspacing=1 border=0>
								<tr>
									<td colspan=3>Submit to the next approval :</td>
								</tr>
								<tr>
									<td colspan=3><hr></td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['namakaryawan']."</td>
									<td>:</td>
									<td valign=top>
										<select id=user_id name=user_id  style=\"width:150px;\">".$optKry."</select>
									</td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['note']."</td>
									<td>:</td>
									<td>
										<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
									</td>
								</tr>
									<td colspan=2></td>
									<td>
										<button class=mybutton onclick=nextapprovalbansos('','".$jenistrk."') title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
									</td>
								</tr>
							</table>
	                        <input type=hidden name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
						</div>";
					}
				}else{
					$tab='';
					$tab.="<div id=approve>
						<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
						<table cellspacing=1 border=0>
							<tr>
								<td colspan=3>Approved</td>
							</tr>
							<tr>
								<td colspan=3><hr></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['note']."</td>
								<td>:</td>
								<td>
									<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<button id=Ajukan class=mybutton onclick=nextapprovalbansos('approved','".$jenistrk."') >Approved</button>
								</td>
							</tr>
						</table>
                    </div>";
				}		
	        }
        }
		echo $tab;
	break;

	case 'insert_nextapproval':
	if($userid==''){
		$user_id = $karyawanid;
	}else{
		$user_id = $userid;
	}

	//exit('Error : '.$userid);
	$temporg = explode("/",$notransaksi);
	$koderorg=$temporg[3];
	$tglskrng = date("Y-m-d H:i:s");
	$str = "select * from ".$dbname.".lgl_bansos where `notransaksi`='".$notransaksi."'"; #exit('error sasas'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$koderorg=$bar['kodeorg'];
	$jenistrk = ($bar['jenis']=='BANSOS'?'BANSOS':'PP');
	$countApp = getCountApproval($jenistrk, $koderorg);

	$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='".$jenistrk."' and tipe='1' and 
	tipekaryawan='".$_SESSION['empl']['tipekaryawan']."' and kodeunit='".$bar['kodeorg']."' and level='".$kolom."' ";
	$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
	$resap->setFetchMode(PDO::FETCH_ASSOC);
	$barap=$resap->fetch();
	$leveldireksi=$barap['level'];


	if($user_id == 'DIREKSI')
	{

			$level = $kolom + 1;
			$tglapp=date('y-m-d h:i:s');
            $strkry="select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='7' and bagian='BOD'";
			$reskry=$owlPDO->query($strkry) or die(print " Gagal: ".PDOException::getMessage());
			$reskry->setFetchMode(PDO::FETCH_ASSOC);
			while($barkry=$reskry->fetch()){
				# insert ke table approval
				$strapp = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
						`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
						 values ('','".$notransaksi."','".$jenistrk."','".$level."','".$barkry['karyawanid']."','0','','','".$tglapp."')";
				try {
					$owlPDO->exec($strapp);
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
						try {$owlPDO->exec($strx);
							#mailCoy($user_id);
							#exit();
						} catch (PDOException $e) {
							print " Gagal  !: ".$e->getMessage()."\n";
							die();
						}
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";die();
				}	
			}
	}
	elseif ($leveldireksi=='') {
		if ($bar['statuspersetujuan'] == 1) {
			exit("Warning : Sudah di Approved");
		}else if($bar['statuspersetujuan'] == 0) {
			$arrDetail = detailApprove($kolom, $notransaksi, $jenistrk);
			$level = $kolom + 1;
			if ($kolom != $countApp) {
				if ($user_id == $arrDetail['karyawanid']) {
					exit("Warning : ".getNamaKaryawan($user_id)." Sudah di gunakan");
				}else if($user_id == $bar['dibuat']) {
					exit("Warning : ".getNamaKaryawan($user_id)." Pembuat Transaksi");
				} else {
					$strx = "insert into ".$dbname.".approval values ('','".$notransaksi."','".$jenistrk."','".$level."','".$user_id."','0','','','')";
					try {
						$owlPDO->exec($strx);
						$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
						try {$owlPDO->exec($strx);
							#mailCoy($user_id);
							#exit();
						} catch (PDOException $e) {
							print " Gagal  !: ".$e->getMessage()."\n";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
				}
			} else {
				$strx = "update ".$dbname.".lgl_bansos set statuspersetujuan='1' where `notransaksi`='".$notransaksi."'";
				try {$owlPDO->exec($strx);
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					try {
						$owlPDO->exec($strx);
						#mailCoy($user_id);
						#break;
						#exit();
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
				} catch (PDOException $e) {
					print " Gagal  !: ".$e->getMessage()."\n";
					die();
				}
			}
		}
	}else{

		if ($bar['statuspersetujuan'] == 1) {
			exit("Warning : Sudah di Approved");
		}else if($bar['statuspersetujuan'] == 0) {
			$arrDetail = detailApprove($kolom, $notransaksi, $jenistrk,$karyawanid);
			$level = $kolom + 1;
			if ($kolom != $countApp) {
				if($user_id == $bar['dibuat']) {
					exit("Warning : ".getNamaKaryawan($user_id)." Pembuat Transaksi");
				} else {

					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					try {
						$owlPDO->exec($strx);

						$strx = "update ".$dbname.".approval set level='".$level."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid!='".$karyawanid."'";
						try {
							$owlPDO->exec($strx);
						} catch (PDOException $e) {
							print " Gagal  !: ".$e->getMessage()."\n";
							die();
						}

						// $strx = "insert into ".$dbname.".approval values ('','".$notransaksi."','BANSOS','".$level."','".$karyawanid."','0','','','')";
						// try {
						// 	$owlPDO->exec($strx);
						// } catch (PDOException $e) {
						// 	print " Gagal  !: ".$e->getMessage()."\n";
						// 	die();
						// }

					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
					
				}
			} else {
				$strx = "update ".$dbname.".lgl_bansos set statuspersetujuan='1' where `notransaksi`='".$notransaksi."'";
				try {
					$owlPDO->exec($strx);

					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					try {
						$owlPDO->exec($strx);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}		

					$strx = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid!='".$karyawanid."' and status='0'";
					try {
						$owlPDO->exec($strx);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}

				} catch (PDOException $e) {
					print " Gagal  !: ".$e->getMessage()."\n";
					die();
				}
			}
		}
	}
	
	break;
	case 'tolak':
		
		$str = "select * from ".$dbname.".lgl_bansos where `notransaksi`='".$_POST['notransaksi']."'"; #exit('error sasas'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$jenistrk = ($bar['jenis']=='BANSOS'?'BANSOS':'PP');
		
		 echo"<div id=rejected_form>
		<input hidden id=notransaksi value=".$_POST['notransaksi']."  />
		<table cellspacing=1 border=0>
		<tr>
		<td colspan=3>
		 Rejection</td></tr>
		<tr>
		<tr><td colspan=3><hr></td></tr>
		<td>".$_SESSION['lang']['note']."</td>
		<td>:</td>
		<td><input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
		</tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=\"inserttolakbansos(".$_POST['kolom'].",'".$jenistrk."')\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$str = "select * from ".$dbname.".lgl_bansos where `notransaksi`='".$notransaksi."'"; #exit('error sasas'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$jenistrk = ($bar['jenis']=='BANSOS'?'BANSOS':'PP');

		$ardt=0;
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[4];
		$countApp = getCountApproval($jenistrk,$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,$jenistrk);
		$tglskrng=date("Y-m-d H:i:s");
		$str="update ".$dbname.".lgl_bansos set statuspersetujuan='3' where notransaksi='".$notransaksi."'" ;
		try{
			$owlPDO->exec($str); 
			
			$str="update ".$dbname.".approval set status='3', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."' ";
			try{
				$owlPDO->exec($str); 

				$strx = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid!='".$karyawanid."' and status='0'";
				try {
					$owlPDO->exec($strx);

					$strx2 = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and level>'".$kolom."' and status='0'";
					try {
						$owlPDO->exec($strx2);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}

				} catch (PDOException $e) {
					print " Gagal  !: ".$e->getMessage()."\n";
					die();
				}

			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
}
?>