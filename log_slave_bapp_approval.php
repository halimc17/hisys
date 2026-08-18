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

$method = checkPostGet('method', '');
$proses = checkPostGet('proses', '');
$level = checkPostGet('level', '');
$notransaksi = checkPostGet('notransaksi', '');
$kolom = checkPostGet('kolom', '');
$comment = checkPostGet('comment', '');
$karyawanid = checkPostGet('karyawanid', $session_id);
$userid = checkPostGet('userid', '');
$tglskrng = date("Y-m-d H:i:s");
$arrstatus = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');

switch ($method) {
case 'getdetail':
	case'BAPP':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>No SPK / BAPP</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>".$_SESSION['lang']['unit']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		
		$countApp = getCountApproval('BAPP');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$str = "select a.*,b.*, c.kodeorg as kodeorg from ".$dbname.".approval a
			left join ".$dbname.".log_baspk b on a.notransaksi = b.nopengajuan 
			left join ".$dbname.".log_spkht c on b.notransaksi = c.notransaksi 
			
			where a.jenispersetujuan='BAPP' and a.status='0' and a.karyawanid='".$karyawanid."' group by nopengajuan order by b.nopengajuan asc"; 
		
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$kodeorgspk=makeOption($dbname,'log_spkht','notransaksi,kodeorg',"notransaksi='".$bar['notransaksi']."'");
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['kodeorg']."'");
			$string="select keterangan,tanggal,nopengajuan from ".$dbname.".log_baspk where notransaksi='".$bar['notransaksi']."' and nopengajuan='".$bar['nopengajuan']."' group by keterangan order by tanggal desc limit 1";
			$reesss=fetchdata($string);
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['nopengajuan']."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".$bar['tanggal']."</td>
				<td align=left>".$kodeorgspk[$bar['notransaksi']]."</td>
				<td align=center>
					<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"htmlBAPPX('".$bar['nopengajuan']."','".$bar['notransaksi']."','".$kodeorgspk[$bar['notransaksi']]."','".$bar['tanggal']."','".$bar['termin']."','".$no."','event','html','".$reesss[0]['keterangan']."');\">					
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			$xxx = "";
			for ($i = 1; $i <= $countApp; $i++) {
				$strx="select * from ".$dbname.".approval where notransaksi='".$bar['nopengajuan']."' and level='".$i."'";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					if($valx['karyawanid']==$karyawanid){
						if($valx['status']=='' || $valx['status']==0){
							$showaction = $showaction + 1;
						}
					}
					
					if($valx['karyawanid']==$karyawanid && $valx['status']==0){
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
					<button class=mybutton onclick=\"getdataBAPP('".$bar['nopengajuan']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakBAPP('".$bar['nopengajuan']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['nopengajuan'], 'BAPP');
				
				$strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='BAPP' and kodeunit='".$kodeorgspk[$bar['notransaksi']]."' and level='".$i."'";
				$respo=fetchdata($strpo);
				@$tipeapp = $respo[0]['tipe'];
				@$departemenapp = $respo[0]['departemen'];
				@$tipekaryawanapp = $respo[0]['tipekaryawan'];
				@$jabatanapp = $respo[0]['jabatan'];
				
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
				if($arrDetail['nama']!=''){
					$tab.="<td style='vertical-align:top;text-align:center'>
						<label style='text-align:center;font-weight:bold'>".$arrDetail['nama']."</label><br>
						Status : ".$arrDetail['namastatus']."<br>
						".($arrDetail['komentar']==''?"":"Comment : ".$arrDetail['komentar'])."
					</td>";
				}else{
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
		
		$str="select * from ".$dbname.".log_baspk where nopengajuan='".$notransaksi."'";
		$res=fetchdata($str);
		
		$kodeorgspk=makeOption($dbname,'log_spkht','notransaksi,kodeorg',"notransaksi='".$res[0]['notransaksi']."'");
		
		@$koderorg = $kodeorgspk[$res[0]['notransaksi']];
		$countApp = getCountApproval('BAPP',$koderorg);
		
		
		for($i=1;$i<=$countApp;$i++){
			$strx="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$i."'";
			$resx=fetchdata($strx);
			foreach($resx as $keyx=>$valx){
				if($karyawanid==$valx['karyawanid']){
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
										<button id=Ajukan class=mybutton onclick=nextapprovalBAPP('approved') >Approved</button>
									</td>
								</tr>
							</table>
						</div>";
					}else{
						$level = $i+1;
						$arrListApp = listApprove($level,'BAPP',$koderorg);
						$arrl=array();
						foreach($arrListApp as $key=>$val){
							if (in_array($val['karyawanid'], $arrl, TRUE)){
								
							}else{
								@$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." ".($val['lokasitugas']==''?'':'['.$val['lokasitugas'].']')."</option>";
							} 
							$arrl[] = $val['karyawanid'];
							
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
										<button class=mybutton onclick=nextapprovalBAPP() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
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
	$jenisApp = "BAPP";
	if($userid==''){
		$user_id = $karyawanid;
	}else{
		$user_id = $userid;
	}
	
	$str="select * from ".$dbname.".log_baspk where nopengajuan='".$notransaksi."'";
	$res=fetchdata($str);
	$kodeorgspk=makeOption($dbname,'log_spkht','notransaksi,kodeorg');
	@$koderorg = $kodeorgspk[$res[0]['notransaksi']];
	
	$countApp = getCountApproval('BAPP', $koderorg);
	$tglskrng = date("Y-m-d H:i:s");
	$str = "select * from ".$dbname.".log_baspk where `nopengajuan`='".$notransaksi."'"; //exit('error sasas'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	if ($bar['statuspengajuan'] == 1) {
		exit("Warning : Sudah di Approved");
	}else if($bar['statuspengajuan'] == 9) {
		$arrDetail = detailApprove($kolom, $notransaksi, 'BAPP');
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
					$str = "insert into ".$dbname.".approval values ('','".$notransaksi."','BAPP','".$level."','".$user_id."','0','','','')";
					try {
						$owlPDO->exec($str);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
				}
				
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				try {
					$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
					$owlPDO->exec($str);
				}catch(PDOException $e){print " Gagal  !: ".$e->getMessage()."\n";die();}
			}
		} else {
			$strx = "update ".$dbname.".log_baspk set statuspengajuan='1' where `nopengajuan`='".$notransaksi."'";
			try {
				$owlPDO->exec($strx);
				
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				try {
					$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
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
		<button class=mybutton onclick=\"inserttolakBAPP(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$ardt=0;
		$str="select * from ".$dbname.".log_baspk where nopengajuan='".$notransaksi."'";
		$res=fetchdata($str);
		$kodeorgspk=makeOption($dbname,'log_spkht','notransaksi,kodeorg');
		@$koderorg = $kodeorgspk[$res[0]['notransaksi']];
		
		$countApp = getCountApproval('BAPP',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'BAPP');
		$tglskrng=date("Y-m-d H:i:s");
		$str="update ".$dbname.".log_baspk set statuspengajuan='3' where nopengajuan='".$notransaksi."'" ;
		try{$owlPDO->exec($str); 
			$str="update ".$dbname.".approval set status='3', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."'";
			try{
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
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

function posting($param) {
        global $dbname;
        global $conn;
        global $owlPDO;
		
        # Get Tahun
		$tmpTgl = explode('/',$param['notransaksi']);
        $tahun = substr($tmpTgl[0],0,4);
		$tgl=$tmpTgl[0];
		
        # Get UMR
        $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai', "karyawanid=".$param['karyawanid']." and tahun=".$tahun." and idkomponen in (1)");
        $Umr = fetchData($qUMR);
        $upahharian=round($Umr[0]['nilai']/25);
		
		
		# Bentuk data
		$str="select sum(luaspanen) as luaspanen,sum(hasilkerja) as hasilkerja,count(*) as jumblok from ".$dbname.".kebun_prestasi_vs_hk where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tluas=$bar['luaspanen'];
			$tjjg=$bar['hasilkerja'];
			$jumblok=$bar['jumblok'];
			@$upahpro=$upahharian/$jumblok;
		
		# Bentuk data
		$str="select * from ".$dbname.".kebun_prestasi_vs_hk where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			# Query update yang baru
			$strupd=" update ".$dbname.".kebun_prestasi set upahkerja='".$upahpro."' where notransaksi='".$param['notransaksi']."' and nik='".$param['karyawanid']."' and kodesegment='0000000001'";
			$owlPDO->exec($strupd);
			#try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
}

?>