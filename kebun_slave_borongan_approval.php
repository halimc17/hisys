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
$pt = checkPostGet('pt', '');
$kodeorg = checkPostGet('kodeorg', '');
$periode = checkPostGet('periode', '');
$jenis = checkPostGet('jenis', '');

$tglskrng = date("Y-m-d H:i:s");
$arrstatus = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');
switch ($method) {
case 'getdetail':
	case'BOR':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['unit']."</td>
			<td align=center>".$_SESSION['lang']['divisi']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		
		$countApp = getCountApproval('BOR');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "length(kodeorganisasi)<=6");
		$countApp = getCountApproval('BOR');
		$str = "select * from ".$dbname.".approval a
			left join ".$dbname.".kebun_aktifitas b on a.notransaksi = b.nopengajuan
			where a.jenispersetujuan='BOR' and a.status='0' and a.karyawanid='".$karyawanid."' group by b.nopengajuan order by a.tanggal asc";
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nmpt=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='".$bar['kodeorg']."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['nopengajuan']."</td>
				<td align=left>".$bar['kodeorg']." - ".$optNmOrg[$bar['kodeorg']]."</td>
				<td align=left>".$bar['nobkm']." - ".$optNmOrg[$bar['nobkm']]."</td>
				<td align=center>					
		<img src=images/zoom.png class=resicon height='30' title='Preview Rekap' onclick=\"htmlborrekap('".$bar['nopengajuan']."','".$bar['kodeorg']."','".$no."','event','html');\">					
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
					<button class=mybutton onclick=\"getdatabor('".$bar['nopengajuan']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakbor('".$bar['nopengajuan']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['nopengajuan'], 'BOR');
				if($kodeorg==''){
					$kodeorg=$bar['kodeorg'];
				}else{
					$kodeorg=$kodeorg;
				}
				$strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='BOR' and kodeunit='".$kodeorg."' and level='".$i."'";
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
					
					if($arrDetail['nama']!=''){
						$tab.="<td style='vertical-align:top;text-align:center'>
							<label style='text-align:center;font-weight:bold'>".$arrDetail['nama']."</label><br>
							Status : ".$arrDetail['namastatus']."<br>
							".($arrDetail['komentar']==''?"":"Comment : ".$arrDetail['komentar'])."
						</td>";
					}else{
						$tab.="<td style='text-align:center'>-</td>";
					}
					
					// if ($arrDetail['nama'] != '') {
						// $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
					// } else {
						// $tab.="<td style='text-align:center'>-</td>";
					// }
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
		$str="select * from ".$dbname.".kebun_aktifitas where nopengajuan='".$notransaksi."'";
		$res=fetchdata($str);
		$koderorg = $res[0]['kodeorg'];
		$countApp = getCountApproval('BOR',$koderorg);
		for($i=1;$i<=$countApp;$i++){
			// $arrDetail = detailApprove($i,$notransaksi,'BOR');
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
										<button id=Ajukan class=mybutton onclick=nextapprovalbor('approved') >Approved</button>
									</td>
								</tr>
							</table>
						</div>";
					}else{
						$level = $i+1;
						$arrListApp = listApprove($level,'BOR',$koderorg);
						$optKry='';
						foreach($arrListApp as $key=>$val){
							$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
						}
						$tab.="<div id=test style=display:block>
							<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<input hidden id=kolom value=".$_POST['kolom']."  />
							<table cellspacing=1 border=0 style=width:100%>
								<tr>
									<td colspan=3>Submit to the next approval :</td>
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
										<button class=mybutton onclick=nextapprovalbor() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
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
	try {
		$owlPDO->beginTransaction();
	
	$jenisApp = "BOR";
	if($userid==''){
		$user_id = $karyawanid;
	}else{
		$user_id = $userid;
	}
	$str="select * from ".$dbname.".kebun_aktifitas where nopengajuan='".$notransaksi."'";
	$res=fetchdata($str);
	$koderorg = $res[0]['kodeorg'];
	
	$countApp = getCountApproval('BOR', $koderorg);
	$tglskrng = date("Y-m-d H:i:s");
	$str = "select * from ".$dbname.".kebun_aktifitas where `nopengajuan`='".$notransaksi."'"; #exit('error sasas'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	if ($bar['statuspersetujuan'] == 1) {
		exit("Warning : Sudah di Approved");
	}else if($bar['statuspersetujuan'] == 0) {
		$arrDetail = detailApprove($kolom, $notransaksi, 'BOR');
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
					$str = "insert into ".$dbname.".approval values ('','".$notransaksi."','BOR','".$level."','".$user_id."','0','','','')";
					$owlPDO->exec($str);
				}
				
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
					$owlPDO->exec($str);
			}
		} else {
			$strx = "update ".$dbname.".kebun_aktifitas set statuspersetujuan='1' where `nopengajuan`='".$notransaksi."'";
			$owlPDO->exec($strx);
				
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
					$owlPDO->exec($str);
		}
	}
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
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
		
		<td>".$_SESSION['lang']['note']."</td>
		<td>:</td>
		<td><input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
		</tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=\"inserttolakbor(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$ardt=0;
		$str="select * from ".$dbname.".kebun_aktifitas where nopengajuan='".$notransaksi."'";
		$res=fetchdata($str);
		$koderorg = $res[0]['kodeorg'];
		$countApp = getCountApproval('BOR',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'BOR');
		$tglskrng=date("Y-m-d H:i:s");
		$str="update ".$dbname.".kebun_aktifitas set statuspersetujuan='3' where nopengajuan='".$notransaksi."'" ;
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
	case'rekap':	
	#approval
	$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
	$tab.="<span><b>Approval</b></span>";
	if($jenis=='excel' or $jenis=='pdf'){
		$tab.="<table  border=1 cellspacing=0 cellpadding=1 class=sortable>";
	}else{
		$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
	}
	$nopeng=makeOption($dbname,'kebun_aktifitas','notransaksi,nopengajuan',"notransaksi='".$notransaksi."'");
	$nnorg=makeOption($dbname,'kebun_aktifitas','notransaksi,kodeorg',"nopengajuan='".$notransaksi."'");
	
	$countApprove = getCountApproval('BOR',$nnorg[$notransaksi]);
	$str=" select * from ".$dbname.".kebun_aktifitas where  nopengajuan='".$notransaksi."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$tab.= "<thead>
			<tr style='font-weight:bold'>
				<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
			for($i=1;$i<=$countApprove;$i++){
				$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
			}
				
	$tab.= "</tr></thead><tbody>";
	$tab.= "<tr class=rowcontent>
			<td valign=top>".$nmkar[$bar['updateby']]."<br>
				".$bar['lastupdate']."</td>";
				
	for($i=1;$i<=$countApprove;$i++){
		$arrApp = detailApprove($i,$notransaksi,'BOR');
		if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
			$tngl='';
		}else{
			$tngl=tanggalnormal($arrApp['tanggal']);
		}
		$optstatus=array("0"=>"Diperlukan Persetujuan","1"=>"Disetujui","2"=>"Dikoreksi","3"=>"Ditolak","9"=>"Proses Pengajuan");
		if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
			$tab.= "<td valign=top>".$arrApp['nama']."
					<br>".$optstatus[$arrApp['status']]."
					<br>".$tngl."
					<br>".$arrApp['komentar']."
					</td>";
		}else{
			$tab.= "<td>&nbsp;</td>";
		}
	}
	$tab.= "</tbody></table>";
	
	#status tolak
	$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
	
	$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
	$res=fetchdata($str);
	$row=count($res);
	if($row>0){
		$no=0;
		foreach($res as $key=>$val){
			$no++;
			$tab.="<br>";
			if($jenis=='excel' or $jenis=='pdf'){
				$tab.="<table  border=1 cellspacing=0 cellpadding=1 class=sortable>";
			}else{
				$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
			}
			$tab.="<thead>
					<tr style='font-weight:bold'>
						<td colspan='".(1+$val['level'])."'>Return / Tolak - ".$no."</td>
					</tr>
					<tr style='font-weight:bold'>
						<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
						for($i=1;$i<=$val['level'];$i++) {
							$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
						}
					$tab.="</tr>
				</thead>
				<tbody>
					<tr class=rowcontent>
						<td valign=top>".$nmkar[$bar['updateby']]."<br>
										".$bar['lastupdate']."</td>";
						for($i=1;$i<=$val['level'];$i++) {
							$strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
							$resx=fetchdata($strx);
							$color='';
							if($resx[0]['status']==3){
								$color=" style=background-color:red ";
							}
							$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
								<br>	
								".$arrHsl[$resx[0]['status']]."
								<br>	
								".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
								<br>	
								".$resx[0]['komentar']."
							</td>";
						}
					$tab.="</tr>
				</tbody>
				</table>";
		}
	}
	$tab.="<br>";
	#end status tolak
	
	if($proses=='preview'){
		$tab.="<fieldset><legend>List File Upload</legend>";
		$tab.="<table class=sortable cellspacing=1 border=0>
				<thead> 
				<tr>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['kriteria']."</td>
					<td align=center>".$_SESSION['lang']['namafile']."</td>
				</tr>
				</thead>
				<tbody>";
		
		$str="select * from ".$dbname.".listfile_kebun_borongan where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$no=0;
		if(count($res) > 0){
			foreach($res as $val){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$val['kriteriaefil']."</td>";
				$tab.="<td><a href='fileupload/kebun_borongan/".$val['namafile']."' download>".$val['namafile']."</a></td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'><td style='text-align:center' colspan=3>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}
				
			$tab.="</tbody>
			</table>
		</fieldset>
		<br>"; 
		
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
	}else{
		$tab.="<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
	}
	$tab.="<thead><tr class=rowheader>";
		$rows="rowspan=1";	
		$tab.="<th align=center ".$rows." width=20px>No</th>
			<th align=center ".$rows.">".$_SESSION['lang']['notransaksi'] . "</th>
			<th align=center ".$rows.">".$_SESSION['lang']['tanggal'] . "</th>
			<th align=center ".$rows.">".$_SESSION['lang']['blok'] . "</th>
			<th align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</th>
			<th align=center ".$rows.">".$_SESSION['lang']['satuan'] . "</th>
			<th align=center ".$rows.">".$_SESSION['lang']['hasilkerjad'] . "</th>
			<th align=center ".$rows.">".$_SESSION['lang']['rupiah'] . "</th>
		</tr>
		</thead>";
		$optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		$optSatKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
		$str="select * from ".$dbname.".kebun_aktifitas where nopengajuan='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$no=$thk=$tumr=$tpremi=$tpres=0;
        while($bar=$res->fetch()){	
			$sPres="select a.notransaksi, sum(a.insentif) as upahpremi, sum(a.umr) as umr,sum(a.jhk) as jumlahhk,kodekegiatan,
					tanggalinput,b.kodeorg, sum(a.hasilkerja) as hasilkerja 
					from ".$dbname.".kebun_kehadiran a 
					left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi 
					left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi 
					where a.notransaksi='".$bar['notransaksi']."' group by a.notransaksi, kodekegiatan, b.kodeorg order by kodekegiatan asc, b.kodeorg asc"; #exit('error'. $sPres);
			$qPres=$owlPDO->query($sPres) or die(print " Gagal: ".PDOException::getMessage());
			while($rPres=$qPres->fetch()){
				 $no+=1;
				 $tab.="<tr class=rowcontent style=cursor:pointer;color:blue; onclick=detailData('".$rPres['notransaksi']."','".$no."','event','','html')>";
				 $tab.="<td align=center>".$no."</td>";
				 $tab.="<td>".@$rPres['notransaksi']."</td>";
				 $tab.="<td>".tanggalnormal($rPres['tanggalinput'])."</td>";
				 $tab.="<td>".@$rPres['kodeorg']."</td>";
				 $tab.="<td>".@$rPres['kodekegiatan']." - ".@$optKegiatan[$rPres['kodekegiatan']]."</td>";
				 $tab.="<td>".@$optSatKegiatan[$rPres['kodekegiatan']]."</td>";
				 $tab.="<td align=right>".@hidezerodecimal($rPres['hasilkerja'],2)."</td>";
				 $tab.="<td align=right>".@hidezerodecimal($rPres['upahpremi'],0)."</td>";
				 $tab.="</tr>";

				 $tpremi+=$rPres['upahpremi'];
				 $tpres+=$rPres['hasilkerja'];
			}
		}
			 
			 $tab.="<tr class=rowcontent>";
             $tab.="<td align=center colspan=6>".$_SESSION['lang']['total']."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($tpres,2)."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($tpremi,2)."</td>";
             $tab.="</tr>";
			 
         $tab.="</table>";
	echo $tab;
	break;
	
}
?>