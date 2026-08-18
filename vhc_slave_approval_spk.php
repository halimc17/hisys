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

$strz = "select * from ".$dbname.".approval a left join ".$dbname.".lgl_pengajuanspkht b on a.notransaksi = b.notransaksi
		where a.jenispersetujuan='SPK' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc";
$resz = $owlPDO->query($strz)or die(print " Gagal: ".PDOException::getMessage());
$resz->setFetchMode(PDO::FETCH_ASSOC);

while ($barz = $resz->fetch()) {	 
	$notrans=$barz['notransaksi'];
}  
$strAp = "select max(level) as level, karyawanid from ".$dbname.".project_approval a left join ".$dbname.".lgl_pengajuanspkht b on a.kode = b.divisi  where b.notransaksi='".$notrans."' ";
$res=$owlPDO->query($strAp) or die(print " Gagal: ".PDOException::getMessage()); 
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$lvlapr=$bar['level'];  
$karyapr=$bar['karyawanid'];
  
switch ($method) {
case 'getdetail':
	case'SPK':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['lokasi']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		if ($lvlapr!=0) {
			$countApp=$lvlapr;
		}else { 
			$countApp = getCountApproval('SPK');
		}
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		// $countApp = getCountApproval('SPK');
		$str = "select * from ".$dbname.".approval a
			left join ".$dbname.".lgl_pengajuanspkht b on a.notransaksi = b.notransaksi
			where a.jenispersetujuan='SPK' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc";
		//exit('error'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			if($bar['kategori']=='PUSAT'){
				$strx="select kodeorganisasi from ".$dbname.".organisasi where induk='".$bar['pt']."' and tipe='HOLDING'";
				$resx=fetchdata($strx);
				$kodeorg = $resx[0]['kodeorganisasi'];
			}else{
				$kodeorg = $bar['unit'];				
			} 
			 
			
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$kodeorg."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".substr($bar['tanggal'],0,7)."</td>
				<td align=left>".$kodeorg." - ".$optNmOrg[$kodeorg]."</td>
				<td align=left>".$bar['kategori']."</td>
				<td align=center>
				<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"htmlspk('".$bar['notransaksi']."','html');\">
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			$xxx = "";
			for ($i = 1; $i <= $countApp; $i++) {
				// $arrDetail = detailApprove($i, $bar['notransaksi'], 'SPK');
				// if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
					// $level = $arrDetail['level'];
					// $showaction = 1;
					// if ($i >= 2) {
						// $countubahjumlah = 1;
					// }
				// }
				$strx="select * from ".$dbname.".approval where notransaksi='".$bar['notransaksi']."' and level='".$i."'";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					if($valx['karyawanid']==$karyawanid){
						if($valx['status']=='' || $valx['status']==0)
						{
							$showaction = $showaction + 1;
						}
					}
					
					if($valx['karyawanid']==$karyawanid && $valx['status']==0)
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
					<button class=mybutton onclick=\"getdataspk('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakspkxxx('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= 4; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'SPK');
				
				$strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='SPK' and kodeunit='".$kodeorg."' and level='".$i."'";
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
		$temporg = explode("/",$notransaksi);
		$str="select kategori,pt,unit from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$kategori = $res[0]['kategori'];
		if($kategori=='PUSAT'){
			$strx="select kodeorganisasi from ".$dbname.".organisasi where induk='".$res[0]['pt']."' and tipe='HOLDING'";
			$resx=fetchdata($strx);
			$koderorg = $resx[0]['kodeorganisasi'];
		}else{
			$koderorg = $res[0]['unit'];
		}

		$strAp = "select * from ".$dbname.".project_approval a left join ".$dbname.".lgl_pengajuanspkht b on a.kode = b.divisi  where b.notransaksi='".$notransaksi."' ";
		$barAp=fetchData($strAp);  
		@$ceklagi=count($barAp);
		
		if ($ceklagi!=0) {
			$countApp=$ceklagi;
		}else{ 
			$countApp = getCountApproval('SPK',$koderorg);
		}
		for($i=1;$i<=$countApp;$i++){
			// $arrDetail = detailApprove($i,$notransaksi,'SPK');
			
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
										<button id=Ajukan class=mybutton onclick=nextapprovalspk('approved') >Approved</button>
									</td>
								</tr>
							</table>
						</div>";
					}else{
						
						$level = $i+1;
						$nmkar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
						$lktugas=makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');

						#ambil total
						$stri="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
						$resi=fetchData($stri);
						
						if ($ceklagi>0) {
							$str="SELECT * FROM project_approval a left join lgl_pengajuanspkht b ON a.kode=b.divisi where b.notransaksi='".$notransaksi."' 
							and level ='".$level."' ";
							$arrListApp=fetchData($str);
								foreach($arrListApp as $key=>$val){ 
									@$optKry.="<option value='".$val['karyawanid']."'>".$nmkar[$val['karyawanid']]." - [".$lktugas[$val['karyawanid']]."]</option>"; 
								}   
						}else { 
							// $arrListApp = listApprove($level,'SPK',$koderorg);
							$str="select distinct a.karyawanid,a.nilaidari,a.nilaisampai,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a
							left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where
							a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='SPK' and a.level='".$level."' and a.kodeunit='".$koderorg."'  order by b.namakaryawan asc";
							$arrListApp=fetchData($str);
							foreach($arrListApp as $val){
								if($val['nilaidari']=='0' && $val['nilaisampai']=='0'){
									@$optKry.="<option value='".$val['karyawanid']."'>".$nmkar[$val['karyawanid']]." - [".$val['lokasitugas']."]</option>";
								}else{
									if($val['nilaidari'] < $resi[0]['total'] && ($resi[0]['total'] < $val['nilaisampai'])){
										@$optKry.="<option value='".$val['karyawanid']."'>".$nmkar[$val['karyawanid']]." - [".$val['lokasitugas']."]</option>";
									}
								}
								
							}
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
										<button class=mybutton onclick=nextapprovalspk() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
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
		
		$jenisApp = "SPK";
		if($userid==''){
			$user_id = $karyawanid;
		}else{
			$user_id = $userid;
		}
		#KONDISI KOLOM JIKA APPROVAL PROJECT 
		$strmax="SELECT max(level) as level FROM project_approval a left join lgl_pengajuanspkht b ON a.kode=b.divisi where b.notransaksi='".$notransaksi."' ";
		$barmax=fetchdata($strmax); 
		@$maxapr=$barmax[0]['level']; 
		
		#KONDISI KOLOM JIKA APPROVAL PROJECT 
		$str="SELECT * FROM project_approval a left join lgl_pengajuanspkht b ON a.kode=b.divisi where b.notransaksi='".$notransaksi."' and karyawanid ='".$karyawanid."' ";  
		$bar=fetchdata($str); 
		@$lvlapr=$bar[0]['level'];
		@$cekaprv=count($lvlapr);

		$str="select kategori,pt,unit from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$kategori = $res[0]['kategori'];
		if($kategori=='PUSAT'){
			$strx="select kodeorganisasi from ".$dbname.".organisasi where induk='".$res[0]['pt']."' and tipe='HOLDING'";
			$resx=fetchdata($strx);
			$koderorg = $resx[0]['kodeorganisasi'];
		}else{
			$koderorg = $res[0]['unit'];
		}
		 
		if ($cekaprv>0) {
			$countApp=$maxapr; 
		}else { 
			$countApp = getCountApproval('SPK', $koderorg); 
		}
		$tglskrng = date("Y-m-d H:i:s");
		$str = "select * from ".$dbname.".lgl_pengajuanspkht where `notransaksi`='".$notransaksi."'"; #<=== SEBELUM
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch(); 
		if ($bar['statuspersetujuan'] == 1) {
			throw new PDOException("Sudah di Approved");
		}else if($bar['statuspersetujuan'] == 0) {
			$arrDetail = detailApprove($kolom, $notransaksi, 'SPK');
			$level = $kolom + 1;
			
			
			if ($kolom != $countApp) {
				// exit('error, masuk bos'.$kolom);
				if ($user_id == $arrDetail['karyawanid']) {
					throw new PDOException(getNamaKaryawan($user_id)." Sudah di gunakan");
				}else if($user_id == $bar['dibuat']) {
					throw new PDOException(getNamaKaryawan($user_id)." Pembuat Transaksi");
				} else {
					$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$level."' and kodeunit='".$koderorg."'";
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
						$str = "insert into ".$dbname.".approval values ('','".$notransaksi."','SPK','".$level."','".$user_id."','0','','','')";
						$owlPDO->exec($str);
					}
					
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
					$owlPDO->exec($str);
				}

			} else {
				
				$strx = "update ".$dbname.".lgl_pengajuanspkht set statuspersetujuan='1' where `notransaksi`='".$notransaksi."'";
				$owlPDO->exec($strx);
				
				# ========================= end insert ke table spk ================================
				
				$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
				$res=fetchData($str);
				$kodeorg = $res[0]['unit'];
				$jenis = $res[0]['jenis'];
				$tanggal = date('Y-m-d');
				$nospk = $notransaksi;
				
				#ANGKUTTBS detailnya nanti diisi pada saat proses di menu Rekap Angkutan TBS
				$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
				$resx=fetchData($str);
				if(count($resx)==0 and ($jenis!='ANGKUTTBS' && $jenis != 'SEWA.HM')){
					throw new PDOException("Detail Kegiatan Pengajuan SPK Tidak Ada.");
				}
				
				
				if(substr($res[0]['divisi'],0,3)=='AK-'){
					$divisi = 'PROJECT';
				}else{
					$divisi = $res[0]['divisi'];
				}
				$koderekanan = $res[0]['koderekanan'];
				$keterangan = $res[0]['project'];
				$dari = $res[0]['tanggaldari'];
				$sampai = $res[0]['tanggalsampai'];
				
				$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."'";
				$res=fetchData($str);
				$nilaikontrak = 0;
				$arrpajak = array();
				foreach($res as $key => $val){
					if($val['tipe']=='rupiah'){
						$nilaikontrak = $nilaikontrak + $val['nilai'];
					}
					if($val['tipe']=='pajak'){
						$arrpajak[$val['nourut']] = $val['nilai']; 
					}
				}
				
				### HEADER ###
				$str="insert into ".$dbname.".log_spkht (kodeorg,notransaksi,tanggal,divisi,koderekanan,posting,nilaikontrak,keterangan,dari,sampai,matauang,nopengajuan) values ('".$kodeorg."','".$nospk."','".$tanggal."','".$divisi."','".$koderekanan."','0','".$nilaikontrak."','".$keterangan."','".$dari."','".$sampai."','IDR','".$notransaksi."')";
				$owlPDO->exec($str);
				
				### TAX ###
				foreach($arrpajak as $key=>$val){
					$nilaipajak = $val;
					$str="insert into ".$dbname.".log_spk_tax (kodeorg,notransaksi,noakun,nilai) values ('".$kodeorg."','".$nospk."','".$key."','".$nilaipajak."')";
					$owlPDO->exec($str);
				}
	 			
				### DETAIL ###
				$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
				$res=fetchData($str);
				foreach($res as $key=>$val){
					$str="insert into ".$dbname.".log_spkdt (notransaksi,kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp,rupiahpersatuan) values ('".$nospk."','".$val['subunit']."','".$val['kegiatan']."','".$val['hk']."','".$val['volume']."','".$val['satuan']."','".$val['total']."','".($val['total']/$val['volume'])."')";
					$owlPDO->exec($str);
				}
			
				# ========================= end insert ke table spk ================================

				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);

				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
				$owlPDO->exec($str);
					
			}
		}
		
			#EXECUTE
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
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
		<button class=mybutton onclick=\"inserttolakspkxxx(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$ardt=0;
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[4];
		$countApp = getCountApproval('SPK',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'SPK');
		$tglskrng=date("Y-m-d H:i:s");
		$str="update ".$dbname.".lgl_pengajuanspkht set statuspersetujuan='3' where notransaksi='".$notransaksi."'"; #exit("error".$str);
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
?>