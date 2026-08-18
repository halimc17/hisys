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

$karyawanid	 = checkPostGet('karyawanid', $session_id);
$method      = checkPostGet('method', '');
$proses      = checkPostGet('proses', '');
$level       = checkPostGet('level', '');
$notransaksi = checkPostGet('notransaksi', '');
$kolom       = checkPostGet('kolom', '');
$comment     = checkPostGet('comment', '');
$userid      = checkPostGet('userid', '');
$jenis       = checkPostGet('jenis', '');
$kodeapproval= checkPostGet('kodeapproval', '');
$kodeorg     = checkPostGet('kodeorg', '');

if($kodeorg==''){
	$strorg="select kodeorg from sdm_pjdinasht where notransaksi='".$notransaksi."'";
	$resorg=fetchData($strorg);
	$kodeorg=$resorg[0]['kodeorg'];
}
 
#$path = "fileupload/lgl_anggarandasar/";

$tglskrng    = date("Y-m-d H:i:s");
$arrstatus   = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');

// echo"<pre>";
// print_r($_POST);
// print_r($_GET);
// echo"</pre>";
// exit("error");

switch ($method) {
case 'getdetail':
	case'PJDSTF':
	case'PJDNSTF':
	case'PJDMGR':
	case'PJDPC':
	case'PJDGM':
	case'PJDBOD':
		/* $strorg="select kodeorg from sdm_pjdinasht where notransaksi='".$notransaksi."'";
		$resorg=fetchData($strorg);
		$kodeorg=$resorg[0]['kodeorg'];	 */
		#kita cari dulu ini pengajuan atau pertangung jawaban
		$countApp = getCountApproval($proses);
		/* $str = "select a.*,b.notransaksi, b.karyawanid as kary, b.kodeorg, b.keterangan from ".$dbname.".approval a
			left join ".$dbname.".sdm_pjdinasht b on a.notransaksi = b.notransaksi
			where  a.jenispersetujuan='".$proses."' and a.status='0' and a.karyawanid='".$karyawanid."' 
			and a.level='".$countApp."' and a.keterangan='pertanggung' group by a.notransaksi, a.level order by a.tanggal asc";
		$res = fetchdata($str);
		$pertanggung=count($res);
		if($pertanggung>0){
			$countApp = getCountApproval($proses,$kodeorg);
			$col=3;
		}else{			
			$countApp = (getCountApproval($proses,$kodeorg)-1);
		} */
		$col=3;
		
	
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['nik2']."</td>
			<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
			<td align=center>".$_SESSION['lang']['unit']."</td>
			<td align=center>".$_SESSION['lang']['keterangan']."</td>
			<td colspan='2' align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='".$col."' align='center'>Verification</td>";
		for ($i=1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$str = "select a.*,b.notransaksi, b.karyawanid as kary, b.kodeorg, b.keterangan from ".$dbname.".approval a
			left join ".$dbname.".sdm_pjdinasht b on a.notransaksi = b.notransaksi
			where  a.jenispersetujuan='".$proses."' and a.status='0' and a.karyawanid='".$karyawanid."' 
			group by a.notransaksi, a.level order by a.tanggal asc";
		
		$res = fetchdata($str);
		foreach($res as $bar){
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['kary']."'");
			$optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik',"karyawanid='".$bar['kary']."'");
			
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".$optnik[$bar['kary']]."</td>
				<td align=left>".$nmkar[$bar['kary']]."</td>
				<td align=left>".$optNmOrg[$bar['kodeorg']]."</td>
				<td align=left>".$bar['keterangan']."</td>
				";
			$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailpdfpjdinas('".$bar['notransaksi']."','event','pdf');\" ></td>";
            
			$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"detaildatapjdinas('".$bar['notransaksi']."','event','html');\" ></td>";
				
			$showaction = 0;
			$level = 1;
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'],$proses,$bar['karyawanid']);
				if ($arrDetail['karyawanid']==$karyawanid && ($arrDetail['status']=='' || $arrDetail['status']==0)){
					$level = $arrDetail['level'];
					$showaction = 1;
				}
			}
			
			if ($showaction == 1) {
				$str = "select * from ".$dbname.".approval a where  a.nourut='".$bar['nourut']."' and a.keterangan='pertanggung'";
				$res = fetchdata($str);
				$ptj = count($res);
				if($ptj>0){					
					$tab.="<td style='text-align:center'>
						<button class=mybutton title=\"Click disini untuk melakukan verifikasi\"><a href=\"javascript:do_load('sdm_verifikasiptjpjdx')\" >Verifikasi</a></button>
						</td>";
				}else{
					$tab.="<td style='text-align:center'></td>";
				}
					
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdataperjalanandinas('".$bar['notransaksi']."','".$level."','".$proses."','".$bar['kodeorg']."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakperjalanandinas('".$bar['notransaksi']."','".$level."','".$proses."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan='".$col."'>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='".$proses."' and tipe='1' and 
				tipekaryawan='".$_SESSION['empl']['tipekaryawan']."'  and level='".$i."'";
				$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
				$resap->setFetchMode(PDO::FETCH_ASSOC);
				$barap=$resap->fetch();
				$leveldireksi=$barap['level'];

				
				$arrDetail = detailApprove($i, $bar['notransaksi'],$proses);
				if ($leveldireksi=='') {
					if ($arrDetail['nama'] != '') {
						$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
					} else {
						$tab.="<td style='text-align:center'>-</td>";
					}
				}else{
					$strcount = "select count(level) as jumlahapp from ".$dbname.".approval where jenispersetujuan='".$proses."' and level='".$i."' and notransaksi='".$bar['notransaksi']."'";
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
		#kita cari dulu ini pengajuan atau pertangung jawaban
		$str="select karyawanid from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$karydinas=$res[0]['karyawanid'];

		$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$karydinas."'");
		$departemen=$optdepartmen[$karydinas];

		$kodegol=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$karydinas."'");
		$optgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');
		
		#cek sendiri untuk approvalnya ==================================================================================================
		$kodeorg=makeOption($dbname,'sdm_pjdinasht','notransaksi,kodeorg',"notransaksi='".$notransaksi."'");
		
		
		##CEK PER DEPARTEMEN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg[$notransaksi]."' and jenispersetujuan='".$kodeapproval."' and departemen='".$departemen."' and level ='1'";
		$res=fetchdata($str);
		$perdepartemen=$res[0]['kodeunit'];
		$wheredept="";
		if($perdepartemen>0 and $kodeapproval!='PJDNSTF'){
			$wheredept.=" and a.departemen='".$departemen."'";
		}else{
			$wheredept.=" and a.departemen=''";
		}

		$kodegol=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$karydinas."'");
		$optgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');

		##CEK PER GOLONGAN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg[$notransaksi]."' and jenispersetujuan='".$kodeapproval."' and golongan='".substr($optgol[$kodegol[$karydinas]],0,1)."'  and level='1'";
		$res=fetchdata($str);
		$pergolongan=$res[0]['kodeunit'];
		#$where="";
		if($pergolongan>0 and $kodeapproval!='PJDNSTF'){
			$wheregol.=" and a.golongan='".substr($optgol[$kodegol[$karydinas]],0,1)."' ";
		}else{
			$wheregol.=" and a.golongan=''";
		}
		
		#ambil level
		$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."' and a.karyawaniduser='".$karydinas."' ".$wheredept." ".$wheregol."";
		$res=fetchData($str);
		if(is_null($res[0]['level'])){
			$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser='' ".$wheredept." ".$wheregol."";
			$res=fetchData($str);
			if(is_null($res[0]['level'])){
				$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser='' ".$wheredept."";
				$res=fetchData($str);
				if(is_null($res[0]['level'])){
					$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.departemen='".$departemen."'";
					$res=fetchData($str);
					if(is_null($res[0]['level'])){						
						$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
						where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.departemen=''";
						$res=fetchData($str);
					}
				}
			}
		}
		
		//echo $str;
		#level terakhir pastikan HRD
		foreach($res as $bar){
			$levelapproval=$bar['level'];
		}
		$kodeorg=$kodeorg[$notransaksi];
		#=====================================================================================================
		$str = "select a.*,b.notransaksi, b.karyawanid as kary, b.kodeorg, b.keterangan from ".$dbname.".approval a
		left join ".$dbname.".sdm_pjdinasht b on a.notransaksi = b.notransaksi
		where b.notransaksi='".$notransaksi."' and a.jenispersetujuan='".$kodeapproval."' and a.status='0' and a.karyawanid='".$karyawanid."' and a.keterangan='pertanggung' group by a.notransaksi, a.level order by a.tanggal asc";
		$res = fetchdata($str);
		$levelmax=$res[0]['level'];
		$pertanggung=count($res); 
		if($pertanggung>0){
			// if($kodeapproval!='PJDNSTF'){				
				// $countApp = getCountApproval($kodeapproval,$kodeorg,$departemen,substr($optgol[$kodegol[$karydinas]],0,1),$karydinas);
			// }else{
				// $countApp = getCountApproval($kodeapproval,$kodeorg,'','',$karydinas);
			// }
			$countApp = $levelapproval;
			$n=$countApp;
		}else{			
			// if($kodeapproval!='PJDNSTF'){				
				// $countApp = (getCountApproval($kodeapproval,$kodeorg,$departemen,substr($optgol[$kodegol[$karydinas]],0,1),$karydinas)-1);
			// }else{
				// $countApp = (getCountApproval($kodeapproval,$kodeorg,'','',$karydinas)-1);
			// }
			$countApp = $levelapproval-1;
			$n=1;
		}
		
	// echo"<pre>";
	
	// print_r($countApp);
	// exit("error");
		
		$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='".$kodeapproval."' and tipe='1' and 
		tipekaryawan='".$_SESSION['empl']['tipekaryawan']."'  and level='".$kolom."' ";
		$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
		$resap->setFetchMode(PDO::FETCH_ASSOC);
		$barap=$resap->fetch();
		$leveldireksi=$barap['level'];

		for($i=$n;$i<=$countApp;$i++){
			$arrDetail = detailApprove($i,$notransaksi,$kodeapproval,$karyawanid);
			if($karyawanid==$arrDetail['karyawanid']){
				if ($leveldireksi=='') {
					if($i == $countApp){
						#cari noreff uang muka
						$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2='umpjd#".$notransaksi."' and nik='".$karyawanid."'";
						$resa = fetchdata($stra);
						$umdibayarkan=0;
						foreach($resa as $bara){				
							$umdibayarkan+=$bara['jumlah'];
						}

						$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' order by jenisbiaya asc";
						$res=fetchdata($str);
						$verhrd=$klaimkary=$sdhrealpt=$umminta=0;$data=$dataisi=$rangetgl=array();
						foreach($res as $bar){
							if($bar['sumber']=='0'){
								$umminta+=$bar['jumlah'];
							}
							if($bar['tanggungan']=='0' and $bar['sumber']=='1'){
								$sdhrealpt+=$bar['jumlah'];
							}
							if($bar['tanggungan']=='1' and $bar['sumber']=='1'){
								$klaimkary+=$bar['jumlah'];
							}
							if($bar['tanggungan']=='1' and $bar['statusverifikasihrd']=='1'  and $bar['sumber']=='1'){
								$verhrd+=$bar['jumlahhrd'];
							}
						}
						$tab.="<div id=approve>
							<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<input hidden id=kodeapproval value=".$kodeapproval."  />
							<table cellspacing=1 border=0>
								<tr>
									<td>UM di Minta</td>
									<td>:</td>
									<td>".number_format($umminta)."</td>
								</tr>
								<tr>
									<td>UM di Bayar</td>
									<td>:</td>
									<td>".number_format($umdibayarkan)."</td>
								</tr>
								<tr>
									<td>Claim di ajukan</td>
									<td>:</td>
									<td>".number_format($klaimkary)."</td>
								</tr>
								<tr>
									<td>Verifikasi HCM / HR</td>
									<td>:</td>
									<td>".number_format($verhrd)."</td>
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
										<button id=Ajukan class=mybutton onclick=nextapprovalperjalanandinas('approved') >Approved</button>
									</td>
								</tr>
							</table>
	                    </div>";
					}else{
						$level = $i+1;
						$str="select karyawanid from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
						$res=fetchdata($str);
						$karydinas=$res[0]['karyawanid'];
						
						$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$karydinas."'");
						$departemen=$optdepartmen[$karydinas];
						
						/* ##CEK PER DEPARTEMEN
						$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg."' and jenispersetujuan='".$kodeapproval."' and departemen='".$departemen."' and level='".$level."'";
						$res=fetchdata($str);
						$perdepartemen=$res[0]['kodeunit'];
						$where="";
						// if($perdepartemen>0 and $kodeapproval!='PJDNSTF'){
						if($perdepartemen>0){
							$where.=" and a.departemen='".$departemen."'";
						}else{
							$where.=" and a.departemen=''";
						}


						$kodegol=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$karydinas."'");
						$optgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');
						##CEK PER GOLONGAN
						$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg."' and jenispersetujuan='".$kodeapproval."' and golongan='".substr($optgol[$kodegol[$karydinas]],0,1)."' and level='".$level."'";
						$res=fetchdata($str);
						$pergolongan=$res[0]['kodeunit'];
						// if($pergolongan>0 and $kodeapproval!='PJDNSTF'){
						if($pergolongan>0){
							$where.=" and a.golongan='".substr($optgol[$kodegol[$karydinas]],0,1)."'";
						}else{
							$where.=" and a.golongan=''";
						} */
						
						/* $arrListApp = listApprove($level,$kodeapproval,$kodeorg,$departemen);
						foreach($arrListApp as $key=>$val){
							if($val['lokasitugas']!=''){
								@$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
							}else{
								@$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
							}
						}
						*/
						
						$str="select * from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg."' and a.karyawaniduser='".$karydinas."' ".$wheredept." ".$wheregol." and level='".$level."' ";
						$res=fetchData($str);
						if(count($res)==0){
							$str="select * from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
							where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg."'  and a.karyawaniduser='' ".$wheredept." ".$wheregol." and level='".$level."'";
							$res=fetchData($str);
							if(count($res)==0){
								$str="select * from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
								where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg."'  and a.karyawaniduser='' ".$wheredept." and level='".$level."'";
								$res=fetchData($str);
								if(count($res)==0){
									$str="select * from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
									where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg."'  and a.karyawaniduser='' and level='".$level."'";
									$res=fetchData($str);
								}
							}
						}
						
						
						// $str="select * from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
						// where jenispersetujuan='".$kodeapproval."' and a.level='".$level."' and kodeunit='".$kodeorg."' and a.karyawaniduser='".$karydinas."' ".$where."";
						// $arrListApp=fetchData($str);
						// if(count($arrListApp)==0){
							// $str="select * from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
							// where jenispersetujuan='".$kodeapproval."' and a.level='".$level."' and kodeunit='".$kodeorg."' and a.karyawaniduser='' ".$where."";
							// $arrListApp=fetchData($str);
						// }
						// echo $str;
						foreach($res as $key=>$val){
							if($val['lokasitugas']!=''){
								@$optKry.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";
							}else{
								@$optKry.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']."</option>";
							}
						}
						// if($_SESSION['standard']['username']=='tim.owl3'){
							// echo $str;
						// }
						
						$tab.="<div id=test style=display:block>
	                        <input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<input hidden id=kolom value=".$_POST['kolom']."  />
							<input hidden id=kodeapproval value=".$kodeapproval."  />
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
										<button class=mybutton onclick=nextapprovalperjalanandinas() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
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
						<input hidden id=kodeapproval value=".$kodeapproval."  />
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
									<button id=Ajukan class=mybutton onclick=nextapprovalperjalanandinas('approved') >Approved</button>
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
	try {
	$owlPDO->beginTransaction();
	
	if($userid==''){
		$user_id = $karyawanid;
	}else{
		$user_id = $userid;
	}
	
	#kita cari dulu ini pengajuan atau pertangung jawaban
	$str="select karyawanid from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$karydinas=$res[0]['karyawanid'];

		$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$karydinas."'");
		$departemen=$optdepartmen[$karydinas];

		$kodegol=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$karydinas."'");
		$optgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');

		#cek sendiri untuk approvalnya ==================================================================================================
		$kodeorg=makeOption($dbname,'sdm_pjdinasht','notransaksi,kodeorg',"notransaksi='".$notransaksi."'");
		
		
		##CEK PER DEPARTEMEN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg[$notransaksi]."' and jenispersetujuan='".$kodeapproval."' and departemen='".$departemen."' and level ='1'";
		$res=fetchdata($str);
		$perdepartemen=$res[0]['kodeunit'];
		$wheredept="";
		if($perdepartemen>0 and $kodeapproval!='PJDNSTF'){
			$wheredept.=" and a.departemen='".$departemen."'";
		}else{
			$wheredept.=" and a.departemen=''";
		}

		$kodegol=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$karydinas."'");
		$optgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');

		##CEK PER GOLONGAN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg[$notransaksi]."' and jenispersetujuan='".$kodeapproval."' and golongan='".substr($optgol[$kodegol[$karydinas]],0,1)."'  and level='1'";
		$res=fetchdata($str);
		$pergolongan=$res[0]['kodeunit'];
		#$where="";
		if($pergolongan>0 and $kodeapproval!='PJDNSTF'){
			$wheregol.=" and a.golongan='".substr($optgol[$kodegol[$karydinas]],0,1)."' ";
		}else{
			$wheregol.=" and a.golongan=''";
		}
		
		#ambil level
		$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."' and a.karyawaniduser='".$karydinas."' ".$wheredept." ".$wheregol."";
		$res=fetchData($str);
		if(is_null($res[0]['level'])){
			$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser='' ".$wheredept." ".$wheregol."";
			$res=fetchData($str);
			if(is_null($res[0]['level'])){
				$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser='' ".$wheredept."";
				$res=fetchData($str);
				if(is_null($res[0]['level'])){
					$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser=''";
					$res=fetchData($str);
				}
			}
		}
		
		
		#level terakhir pastikan HRD
		foreach($res as $bar){
			$levelapproval=$bar['level'];
		}
		
	
	$countApp = getCountApproval($kodeapproval,$kodeorg,$departemen,substr($optgol[$kodegol[$karydinas]],0,1),$karydinas);
	//$countApp = getCountApproval($kodeapproval,$kodeorg);
	$str = "select a.*,b.notransaksi, b.karyawanid as kary, b.kodeorg, b.keterangan from ".$dbname.".approval a
	left join ".$dbname.".sdm_pjdinasht b on a.notransaksi = b.notransaksi
	where b.notransaksi='".$notransaksi."' and a.jenispersetujuan='".$kodeapproval."' and a.status='0' and a.karyawanid='".$karyawanid."' 
	and a.keterangan='pertanggung' group by a.notransaksi, a.level order by a.tanggal asc";
	$res = fetchdata($str);
	$levelmax=$res[0]['level'];
	
	$pertanggung=count($res); 
	if($pertanggung>0){
		#pastikan ada verifikasi
		$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' order by jenisbiaya asc";
		$res=fetchdata($str);
			$verhrd=$klaimkary=$sdhrealpt=$umminta=0;$data=$dataisi=$rangetgl=array();
		if(count($res)>0){
			foreach($res as $bar){
				if($bar['tanggungan']=='1' and $bar['sumber']=='1'){
					$klaimkary+=$bar['jumlah'];
				}
				if($bar['tanggungan']=='1' and $bar['statusverifikasihrd']=='1'  and $bar['sumber']=='1'){
					$verhrd+=$bar['jumlahhrd'];
				}
			}
			if($klaimkary>0 and $verhrd==0){
				throw new PDOException("Mohon lakukan verifikasi terlebih dahulu dengan meng-click tombol Verifikasi.");
			}

		}else{
			$strx = "update ".$dbname.".sdm_pjdinasdt set statusverifikasihrd='1' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($strx);
		}
		
		#cek dulu sudah di lakukan verifikasi atau belum
		$str = "select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and statusverifikasihrd='1'";
		$res = fetchdata($str);
		if(count($res)==0){
			throw new PDOException("Mohon lakukan verifikasi terlebih dahulu dengan meng-click tombol Verifikasi.");
		}
		
		//$countApp = getCountApproval($kodeapproval,$kodeorg,$departemen,substr($optgol[$kodegol[$karydinas]],0,1),$karydinas);
		$countApp = $levelmax;
				
		$str = "select * from ".$dbname.".sdm_pjdinasht where `notransaksi`='".$notransaksi."'"; #exit('error dsadsasas'.$str);
		$res = fetchdata($str);
		$statuspengajuan = $res[0]['statusrealisasi'];
		$updateby = $res[0]['createdby'];
	}else{
		//$countApp = (getCountApproval($kodeapproval,$kodeorg,$departemen,substr($optgol[$kodegol[$karydinas]],0,1),$karydinas)-1);
		$countApp = $levelapproval-1;
		
		$str = "select * from ".$dbname.".sdm_pjdinasht where `notransaksi`='".$notransaksi."'"; #exit('error sasas'.$str);
		$res = fetchdata($str);
		$statuspengajuan = $res[0]['statuspengajuan'];
		$updateby = $res[0]['createdby'];
	}
	
	// echo"<pre>";
	// print_r($kolom);
	// print_r($countApp);
	// exit("error");
	
	if ($statuspengajuan == 1) {
		throw new PDOException("Sudah di Approved");
	}else if($statuspengajuan== 9) {
		$arrDetail = detailApprove($kolom, $notransaksi, $kodeapproval);
		$level = $kolom + 1;
		if ($kolom != $countApp) {
			if ($user_id == $arrDetail['karyawanid']) {
				throw new PDOException(getNamaKaryawan($user_id)." Sudah di gunakan");
			}else if($user_id == $updateby) {
				throw new PDOException(getNamaKaryawan($user_id)." Pembuat Transaksi");
			} else {
				$strx = "insert into ".$dbname.".approval values ('','".$notransaksi."','".$kodeapproval."','".$level."','".$user_id."','0','','','')";
				$owlPDO->exec($strx);
				
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);
			}
		} else {
			if($pertanggung>0){
				$strx = "update ".$dbname.".sdm_pjdinasht set statusrealisasi='1' where `notransaksi`='".$notransaksi."'";
				$owlPDO->exec($strx);
			}else{					
				$strx = "update ".$dbname.".sdm_pjdinasht set statuspengajuan='1' where `notransaksi`='".$notransaksi."'";
				$owlPDO->exec($strx);
				
				
				#jika ada pengajuan UM buatkan notifikasinya
				$strum="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber='0' order by tanggal asc";
				$resum=fetchdata($strum);
				$jumlahum=0;
				foreach($resum as $barum){
					$jumlahum+=$barum['jumlah'];
				}
				
				if($jumlahum>0){
					$ket = makeOption($dbname, 'sdm_pjdinasht', 'notransaksi,keterangan',"notransaksi='".$notransaksi."'");
					$idkar = makeOption($dbname, 'sdm_pjdinasht', 'notransaksi,karyawanid',"notransaksi='".$notransaksi."'");
					$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$idkar[$notransaksi]."'");
					$tgldinas = makeOption($dbname, 'sdm_pjdinasht', 'notransaksi,tgldinasdarireal',"notransaksi='".$notransaksi."'");
					
					
					#ambil datakaryawan KTU, Kasir, Finance, Accounting
					$wh="";
					$wh=" and (kodejabatan in ('21','24') or bagian in ('FNC','ACT'))";
					
					$strn="select * from ".$dbname.".datakaryawan where lokasitugas='".$kodeorg."' ".$wh."";
					$resn=fetchdata($strn);
					if(count($resn)>0){							
						foreach($resn as $barn){
							$msgdt = "Ada permintaan UM Perjalanan Dinas nomor ".$notransaksi.", atas nama ".$nmkar[$idkar[$notransaksi]].", sebesar Rp. ".number_format($jumlahum).", tanggal dinas ".tanggalnormal($tgldinas[$notransaksi]).", keterangan ".$ket[$notransaksi]."";
							
							createnotif($notransaksi,'PJDS',$msgdt,$barn['karyawanid'],date('Y-m-d H:i:s'));
						}
					}
				}
			}
			
			$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
			$owlPDO->exec($strx);
		}
	}
	
		
		#exit("error");
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;
	
	case 'tolak':
		 echo"<div id=rejected_form>
		<input hidden id=notransaksi value=".$_POST['notransaksi']."  />
		<input hidden id=kodeapproval value=".$kodeapproval."  />
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
		<button class=mybutton onclick=\"inserttolakperjalanandinas(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
	try {
	$owlPDO->beginTransaction();
		$tglskrng=date("Y-m-d H:i:s");
		
		
		#kita cari dulu ini pengajuan atau pertangung jawaban
		$kodeorg=makeOption($dbname,'sdm_pjdinasht','notransaksi,kodeorg',"notransaksi='".$notransaksi."'");
		$countApp = getCountApproval($kodeapproval,$kodeorg[$notransaksi]);
		$str = "select a.*,b.notransaksi, b.karyawanid as kary, b.kodeorg, b.keterangan from ".$dbname.".approval a
			left join ".$dbname.".sdm_pjdinasht b on a.notransaksi = b.notransaksi
			where b.notransaksi='".$notransaksi."' and a.jenispersetujuan='".$kodeapproval."' and a.status='0' and a.karyawanid='".$karyawanid."' 
			and a.keterangan='pertanggung' group by a.notransaksi, a.level order by a.tanggal asc";
		$res = fetchdata($str);
		$pertanggung=count($res);
		if($pertanggung>0){
			#update transaksi
			$str="update ".$dbname.".sdm_pjdinasht set statusrealisasi='2' where notransaksi='".$notransaksi."'" ;
			$owlPDO->exec($str); 
		}else{			
			#update transaksi
			$str="update ".$dbname.".sdm_pjdinasht set statuspengajuan='2' where notransaksi='".$notransaksi."'" ;
			$owlPDO->exec($str); 
		}
		
		#update approval
		$str="update ".$dbname.".approval set status='2', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
		$owlPDO->exec($str);
		
		#hapus kalau ada user lain
		$strx = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid!='".$karyawanid."' and status='0'";
		$owlPDO->exec($strx);
		
		#hapus level selanjutnya
		$strx2 = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and level>'".$kolom."' and status='0'";
		$owlPDO->exec($strx2);
	
	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
	break;
	
}
?>