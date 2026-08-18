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
	case'PDO':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		$countApp = getCountApproval('PDO');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('PDO');
		$str = "select * from ".$dbname.".approval a
			left join ".$dbname.".keu_pdoht b on a.notransaksi = b.nopdo
			where a.jenispersetujuan='PDO' and a.status='0' and a.karyawanid='".$karyawanid."' 
			 group by a.notransaksi, a.level order by a.tanggal asc";
		/*echo $str;
		exit();*/
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$kodeorg = $bar['kodeorg'];
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$kodeorg."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['nopdo']."</td>
				<td align=left>".$bar['periode']."</td>
				<td align=left>".$kodeorg." - ".$optNmOrg[$kodeorg]."</td>
				<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View HTML' onclick=\"detailPDO('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','html','event');\" ></td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['nopdo'], 'PDO',$bar['karyawanid']);
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
					<button class=mybutton onclick=\"getdataPDO('".$bar['nopdo']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakPDO('".$bar['nopdo']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='PDO' and tipe='1' and 
				tipekaryawan='".$_SESSION['empl']['tipekaryawan']."' and kodeunit='".$kodeorg."' and level='".$i."'";
				$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
				$resap->setFetchMode(PDO::FETCH_ASSOC);
				$barap=$resap->fetch();
				$leveldireksi=$barap['level'];

				
				$arrDetail = detailApprove($i, $bar['nopdo'], 'PDO');
				if ($leveldireksi=='') {
					if ($arrDetail['nama'] != '') {
						$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
					} else {
						$tab.="<td style='text-align:center'>-</td>";
					}
				}else{
					$strcount = "select count(level) as jumlahapp from ".$dbname.".approval where jenispersetujuan='PDO' and level='".$i."' and notransaksi='".$bar['notransaksi']."'";
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
	break;
	case'get_form_approval':
		$tab="";
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[4];
		$countApp = getCountApproval('PDO',$koderorg);
		
		$str = "select kodeorg from ".$dbname.".keu_pdoht where `nopdo`='".$notransaksi."'";
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$koderorg=$bar['kodeorg'];
		$countApp = getCountApproval('PDO',$koderorg);

		$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='PDO' and tipe='1' and 
		tipekaryawan='".$_SESSION['empl']['tipekaryawan']."' and kodeunit='".$bar['kodeorg']."' and level='".$kolom."' ";
		$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
		$resap->setFetchMode(PDO::FETCH_ASSOC);
		$barap=$resap->fetch();
		$leveldireksi=$barap['level'];

		for($i=1;$i<=$countApp;$i++){
			$arrDetail = detailApprove($i,$notransaksi,'PDO',$karyawanid);
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
										<button id=Ajukan class=mybutton onclick=nextapprovalPDO('approved') >Approved</button>
									</td>
								</tr>
							</table>
	                    </div>";
					}else{
						$level = $i+1;
						$arrListApp = listApprove($level,'PDO',$koderorg);
						foreach($arrListApp as $key=>$val){
							@$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
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
										<button class=mybutton onclick=nextapprovalPDO() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
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
									<button id=Ajukan class=mybutton onclick=nextapprovalPDO('approved') >Approved</button>
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
	$str = "select * from ".$dbname.".keu_pdoht where `nopdo`='".$notransaksi."'"; #exit('error sasas'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$koderorg=$bar['kodeorg'];
	$countApp = getCountApproval('PDO', $koderorg);

	$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='PDO' and tipe='1' and 
	tipekaryawan='".$_SESSION['empl']['tipekaryawan']."' and kodeunit='".$bar['kodeorg']."' and level='".$kolom."' ";
	$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
	$resap->setFetchMode(PDO::FETCH_ASSOC);
	$barap=$resap->fetch();
	$leveldireksi=$barap['level'];

	if($user_id == 'DIREKSI'){
		$level = $kolom + 1;
		$tglapp=date('y-m-d h:i:s');
		$strkry="select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='7' and bagian='BOD'";
		$reskry=$owlPDO->query($strkry) or die(print " Gagal: ".PDOException::getMessage());
		$reskry->setFetchMode(PDO::FETCH_ASSOC);
		while($barkry=$reskry->fetch()){
			# insert ke table approval
			$strapp = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					 values ('','".$notransaksi."','PDO','".$level."','".$barkry['karyawanid']."','0','','','".$tglapp."')";
			try {
				$owlPDO->exec($strapp);
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					try {$owlPDO->exec($strx);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";die();
			}	
		}
	} elseif ($leveldireksi=='') {
		if ($bar['approval'] == 1) {
			exit("Warning : Sudah di Approved");
		}else if($bar['approval'] == 0) {
			$arrDetail = detailApprove($kolom, $notransaksi, 'PDO');
			$level = $kolom + 1;
			if ($kolom != $countApp) {
				if ($user_id == $arrDetail['karyawanid']) {
					exit("Warning : ".getNamaKaryawan($user_id)." Sudah di gunakan");
				}else if($user_id == $bar['dibuat']) {
					exit("Warning : ".getNamaKaryawan($user_id)." Pembuat Transaksi");
				} else {
					$strx = "insert into ".$dbname.".approval values ('','".$notransaksi."','PDO','".$level."','".$user_id."','0','','','')";
					try {
						$owlPDO->exec($strx);
						$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
						try {$owlPDO->exec($strx);
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
				$strx = "update ".$dbname.".keu_pdoht set approval='1', posting='1' where `nopdo`='".$notransaksi."'";
				try {$owlPDO->exec($strx);
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
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
	}else{
		if ($bar['approval'] == 1) {
			exit("Warning : Sudah di Approved");
		}else if($bar['approval'] == 0) {
			$arrDetail = detailApprove($kolom, $notransaksi, 'PDO',$karyawanid);
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
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
				}
			} else {
				$strx = "update ".$dbname.".keu_pdoht set approval='1',posting='1' where `nopdo`='".$notransaksi."'";
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
		<button class=mybutton onclick=\"inserttolakPDO(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$ardt=0;
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[4];
		$countApp = getCountApproval('PDO',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'PDO');
		$tglskrng=date("Y-m-d H:i:s");
		$str="update ".$dbname.".keu_pdoht set approval='2' where nopdo='".$notransaksi."'" ;
		try{$owlPDO->exec($str); 
			$str="update ".$dbname.".approval set status='3', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."'";
			try{$owlPDO->exec($str);
			
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