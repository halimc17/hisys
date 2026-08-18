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
	case'RKH':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>".$_SESSION['lang']['unit']."</td>
			<td align=center>".$_SESSION['lang']['divisi']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		
		$countApp = getCountApproval('RKH');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('RKH');
		$str = "select * from ".$dbname.".approval a
			left join ".$dbname.".kebun_rkhht b on a.notransaksi = b.notransaksi
			where a.jenispersetujuan='RKH' and a.status='0' and a.karyawanid='".$_SESSION['standard']['userid']."' order by a.tanggal asc";
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi like '".substr($bar['kodeorg'],0,4)."%' ");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".$bar['tanggal']."</td>
				<td align=left>".substr($bar['divisi'],0,4)." - ".$optNmOrg[substr($bar['divisi'],0,4)]."</td>
				<td align=left>".$bar['divisi']." - ".$optNmOrg[$bar['divisi']]."</td>
				<td align=center>
					<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"viewdata('event','kebun_slave_rkh.php','".$bar['notransaksi']."');\">					
				</td>";//<br><a href='kebun_rkbx.php'>Revisi Data</a>
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			$xxx = "";
			for ($i = 1; $i <= $countApp; $i++) {
				
				$strx="select * from ".$dbname.".approval where notransaksi='".$bar['notransaksi']."' and level='".$i."'";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					if($valx['karyawanid']==$_SESSION['standard']['userid']){
						if($valx['status']=='' || $valx['status']==0)
						{
							$showaction = $showaction + 1;
						}
					}
					
					if($valx['karyawanid']==$_SESSION['standard']['userid'] && $valx['status']==0)
					{
						$level = $valx['level'];
						$xxx = "conte";
						break;
					}
				}
				
				if($xxx=="conte"){
					break;
				}
			}
			
			if ($showaction!=$level || $level==1) {
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatarkh('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakrkh('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'RKH');
				
				$strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='RKH' and kodeunit='".$kodeorg."' and level='".$i."'";
				$respo=fetchdata($strpo);
				$tipeapp = $respo[0]['tipe'];
				$departemenapp = $respo[0]['departemen'];
				$tipekaryawanapp = $respo[0]['tipekaryawan'];
				$jabatanapp = $respo[0]['jabatan'];
				
				if($tipeapp=='1'){
					if($arrDetail['komentar']==''){
						if($departemenapp!=''){
							$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							$arrDetail['nama'] = $opttipe[$departemenapp];
						}
						
						if($tipekaryawanapp!=''){
							$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						}
						
						if($jabatanapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							$arrDetail['nama'] = $opttipe[$jabatanapp];
						}
					}
				}
				
				if($arrDetail['nama']!='')
				{
					$tab.="<td style='vertical-align:top;text-align:center'>
						<label style='text-align:center;font-weight:bold'>".$arrDetail['nama']."</label><br>
						Status : ".$arrDetail['namastatus']."<br>
						".($arrDetail['komentar']==''?"":"Comment : ".$arrDetail['komentar'])."
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>-</td>";
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
		
		$str="select * from ".$dbname.".kebun_rkhht where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$koderorg = $res[0]['koderorg'];
		$countApp = getCountApproval('RKH',$koderorg);
		for($i=1;$i<=$countApp;$i++){
			// $arrDetail = detailApprove($i,$notransaksi,'RKH');
			
			$strx="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$i."'";
			$resx=fetchdata($strx);
			foreach($resx as $keyx=>$valx){
				if($_SESSION['standard']['userid']==$valx['karyawanid']){
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
										<button id=Ajukan class=mybutton onclick=nextapprovalrkh('approved') >Approved</button>
									</td>
								</tr>
							</table>
						</div>";
					}else{
						$level = $i+1;
						$arrListApp = listApprove($level,'RKH',$koderorg);
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
										<button class=mybutton onclick=nextapprovalrkh() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
									</td>
								</tr>
							</table>
							<input type=hidden name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
						</div>";
					}
				}				
			}
        }
		echo $tab;
	break;
	case 'insert_nextapproval':
	$jenisApp = "RKH";
	if($userid==''){
		$user_id = $_SESSION['standard']['userid'];
	}else{
		$user_id = $userid;
	}
	
	$str="select * from ".$dbname.".kebun_rkhht where notransaksi='".$notransaksi."'";
	$res=fetchdata($str);
	$koderorg = $res[0]['kodeorg'];
	$koderorg = substr($res[0]['divisi'],0,4);
	$countApp = getCountApproval('RKH', $koderorg);
	$tglskrng = date("Y-m-d H:i:s");
	$str = "select * from ".$dbname.".kebun_rkhht where `notransaksi`='".$notransaksi."'"; #exit('error sasas'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	if ($bar['statuspersetujuan'] == 1) {
		exit("Warning : Sudah di Approved");
	}else if($bar['statuspersetujuan'] == 0) {
		
		$arrDetail = detailApprove($kolom, $notransaksi, 'RKH');
		$level = $kolom + 1;
		
		if ($kolom != $countApp) {
			if ($user_id == $arrDetail['karyawanid']) {
				exit("Warning : ".getNamaKaryawan($user_id)." Sudah di gunakan");
			}else if($user_id == $bar['dibuat']) {
				exit("Warning : ".getNamaKaryawan($user_id)." Pembuat Transaksi");
			} else {
				$str="select * from ".$dbname.".setup_approval where 
						jenispersetujuan='".$jenisApp."' and level='".$level."' and kodeunit='".$koderorg."'";
				$res=fetchData($str);
				$tipeapp = $res[0]['tipe'];
				$departemenapp = $res[0]['departemen'];
				$tipekaryawanapp = $res[0]['tipekaryawan'];
				$jabatanapp = $res[0]['jabatan'];
				
				if($tipeapp=='1'){
					if($departemenapp!=''){
						$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
							$owlPDO->exec($str);
						}
					}
					if($tipekaryawanapp!=''){
						$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
							$owlPDO->exec($str);
						}
					}
					if($jabatanapp!='0'){
						$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
							$owlPDO->exec($str);
						}
					}
				}else{
					$str = "insert into ".$dbname.".approval values ('','".$notransaksi."','RKH','".$level."','".$user_id."','0','','','')";
					try {
						$owlPDO->exec($str);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
				}
				
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$_SESSION['standard']['userid']."'";
				try {
					$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$_SESSION['standard']['userid']."' and level='".$kolom."'";
					$owlPDO->exec($str);
				}catch(PDOException $e){print " Gagal  !: ".$e->getMessage()."\n";die();}
			}
		} else {
			$strx = "update ".$dbname.".kebun_rkhht set statuspersetujuan='1' where `notransaksi`='".$notransaksi."'";
			try {
				$owlPDO->exec($strx);
				
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$_SESSION['standard']['userid']."'";
				try {
					$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$_SESSION['standard']['userid']."' and level='".$kolom."'";
					$owlPDO->exec($str);
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
		<button class=mybutton onclick=\"inserttolakrkh(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$ardt=0;
		$temporg = explode("/",$notransaksi);
		$koderorg=substr($temporg[1],0,4);
		$countApp = getCountApproval('RKH',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'RKH');
		$tglskrng=date("Y-m-d H:i:s");
		$str="update ".$dbname.".kebun_rkhht set statuspersetujuan='3' where notransaksi='".$notransaksi."'" ;
		try{$owlPDO->exec($str); 
			$str="update ".$dbname.".approval set status='3', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."'";
			try{
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$_SESSION['standard']['userid']."' and level='".$kolom."'";
				$owlPDO->exec($str);
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