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
$capital     = checkPostGet('capital', '');
$hasilapp     = checkPostGet('hasilapp', '');

$s = "select * from ".$dbname.".approval a where  a.notransaksi='".$notransaksi."' and a.keterangan!=''";
$r = fetchdata($s)[0];	
if($r['keterangan']=='NONKAPITAL'){		
	$strorg="select substr(kodeorg,1,4) as kodeorg from ".$dbname.".bgt_budget where notransaksi='".$notransaksi."'";
	$resorg=fetchData($strorg);
	if($capital==''){			
		$capital='NONKAPITAL';
	}
}else{
	$strorg="select substr(kodeunit,1,4) as kodeorg from ".$dbname.".bgt_kapital where notransaksi='".$notransaksi."'";
	$resorg=fetchData($strorg);
	if($capital==''){			
		$capital='KAPITAL';
	}
}
$carikoddorg=explode("/",$notransaksi);

if($kodeorg==''){
	$kodeorg=$resorg[0]['kodeorg'];
}
if($kodeorg==''){
	$kodeorg=$carikoddorg[1];
}
// exit('error :'.$kodeorg);
 

$tglskrng    = date("Y-m-d H:i:s");
$arrstatus   = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');

switch ($method) {
case 'getdetail':
	case'PTA':
		$countApp = getCountApproval($proses);
		$tab.="
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['unit']."</td>
			<td align=center>".$_SESSION['lang']['keterangan']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']."</td>
			<td colspan='3' align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		for ($i=1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		
		$s = "select * from ".$dbname.".approval a where  a.jenispersetujuan='".$proses."' and a.status='0' and a.karyawanid='".$karyawanid."' 
			group by a.notransaksi, a.level order by a.tanggal asc";
		$r = fetchdata($s);	
		$no=0;
		foreach($r as $b){
			if($b['keterangan']=='NONKAPITAL'){				
				$str = "select a.*,a.keterangan as ket,b.notransaksi, b.updateby as kary, substr(b.kodeorg,1,4) as kodeorg, b.keterangan, sum(b.rupiah) as rupiah from ".$dbname.".approval a left join ".$dbname.".bgt_budget b on a.notransaksi = b.notransaksi
				where b.notransaksi='".$b['notransaksi']."' and a.jenispersetujuan='".$proses."' and a.status='0' and a.karyawanid='".$karyawanid."' and a.keterangan='".$b['keterangan']."' group by a.notransaksi, a.level order by a.tanggal asc";
			}else if($b['keterangan']=='KAPITAL'){			
				$str = "select a.*,a.keterangan as ket, b.notransaksi, b.updateby as kary, substr(b.kodeunit,1,4) as kodeorg, b.keterangan,sum(b.hargatotal) as rupiah from ".$dbname.".approval a left join ".$dbname.".bgt_kapital b on a.notransaksi = b.notransaksi
				where  b.notransaksi='".$b['notransaksi']."' and a.jenispersetujuan='".$proses."' and a.status='0' and a.karyawanid='".$karyawanid."'  and a.keterangan='".$b['keterangan']."' group by a.notransaksi, a.level order by a.tanggal asc";				
			}else{
				$str = "select a.*,a.keterangan as ket,b.notransaksi, b.updateby as kary, substr(b.kodeorg,1,4) as kodeorg, b.keterangan, sum(b.rupiah) as rupiah from ".$dbname.".approval a left join ".$dbname.".bgt_budget b on a.notransaksi = b.notransaksi
				where b.notransaksi='".$b['notransaksi']."' and a.jenispersetujuan='".$proses."' and a.status='0' and a.karyawanid='".$karyawanid."' and a.keterangan='".$b['keterangan']."' group by a.notransaksi, a.level order by a.tanggal asc";
			}
			
			
			$res = fetchdata($str);
			foreach($res as $bar){
				$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['kary']."'");
				$optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik',"karyawanid='".$bar['kary']."'");
				
				$no++;
				$tab.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=left>".$bar['notransaksi']."</td>
					<td align=left>".$optNmOrg[$bar['kodeorg']]."</td>
					<td align=left>".$bar['keterangan']."</td>
					<td align=right>".number_format($bar['rupiah'])."</td>
					";
				$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailpdfpta('".$bar['notransaksi']."','".$bar['ket']."','event','pdf');\" ></td>";
				
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"detaildatapta('".$bar['notransaksi']."','".$bar['ket']."','event','html');\" ></td>";
				
				$tab.="<td align=center style=width:20px></td>";				
				/*
				$tab.="<td align=center style=width:20px><img src=images/skyblue/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detaildatapta('".$bar['notransaksi']."','".$bar['ket']."','event','excel');\" ></td>";
				*/
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
					$tab.="
						<td style='text-align:center'>
						<button class=mybutton onclick=\"getdatapta('".$bar['notransaksi']."','".$level."','".$proses."','".$bar['kodeorg']."')\">".$_SESSION['lang']['setuju']."</button>
						</td>
						<td style='text-align:center;display:none;'>
						<button class=mybutton onclick=\"reconfirmpta('".$bar['notransaksi']."','".$level."','".$proses."','".$bar['kodeorg']."')\">".$_SESSION['lang']['reconfirm']."</button>
						</td>
						
						<td style='text-align:center'>
						<button class=mybutton onclick=\"tolakpta('".$bar['notransaksi']."','".$level."','".$proses."','".$bar['kodeorg']."')\">".$_SESSION['lang']['tolak']."</button>
						</td>
						";
				} else {
					$tab.="<td colspan='".$col."'>&nbsp;</td>";
				}
				
				for ($i = 1; $i <= $countApp; $i++) {
					$strap="select level from ".$dbname.".setup_approval where jenispersetujuan='".$proses."' and tipe='1' and 
					tipekaryawan='".$_SESSION['empl']['tipekaryawan']."'  and level='".$i."'";
					$resap = fetchdata($strap)[0];
					$leveldireksi=$resap['level'];

					
					$arrDetail = detailApprove($i, $bar['notransaksi'],$proses);
					if ($leveldireksi=='') {
						if ($arrDetail['nama'] != '') {
							$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
						} else {
							$tab.="<td style='text-align:center'>-</td>";
						}
					}else{
						$strcount = "select count(level) as jumlahapp from ".$dbname.".approval where jenispersetujuan='".$proses."' and level='".$i."' and notransaksi='".$bar['notransaksi']."'";
						$barcount = fetchdata($strcount)[0];

						if ($barcount['jumlahapp']==1) {
							$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
						}else{
							$tab.="<td style='text-align:center'>DIREKSI</td>";
						}
					}
				}
				$tab.="</tr>";
			}
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
		if($capital=='NONKAPITAL'){
			$strorg="select sum(rupiah) as rupiah, updateby from ".$dbname.".bgt_budget where notransaksi='".$notransaksi."'";
			$resorg=fetchData($strorg)[0];
			$nilai=$resorg['rupiah'];
			$dept = getKary($resorg['updateby'],'bagian');
		}else{
			$strorg="select sum(hargatotal) as rupiah, updateby from ".$dbname.".bgt_kapital where notransaksi='".$notransaksi."'";
			$resorg=fetchData($strorg)[0];
			$nilai= $resorg['rupiah'];
			$dept = getKary($resorg['updateby'],'bagian');
		}
		
		$countApp = getCountApproval($kodeapproval,$kodeorg, $dept);
		// $qCountApp = selectQuery($dbname, 'approval', 'count(*) as jumlahapproval', "notransaksi = '".$notransaksi."'");
		// $resCountApp = fetchData($qCountApp);
		// $countApp = $resCountApp[0]['jumlahapproval'];

		$optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		for($i=1;$i<=$countApp;$i++){
			$arrDetail = detailApprove($i,$notransaksi,$kodeapproval,$karyawanid);
			if($karyawanid==$arrDetail['karyawanid']){
				// $lastapp = ceklastapproval($i,$kodeorg,$kodeapproval,$nilai);
				if($i == $countApp){
					$tab.="<div id=approve>
						<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$notransaksi."  />
						<input hidden id=kodeapproval value=".$kodeapproval."  />
						<input hidden name=tipepta id=tipepta value=".$capital." >
						<table cellspacing=1 border=0>
							<tr>
								<td>".$_SESSION['lang']['note']."</td>
								<td>:</td>
								<td>
									<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<button id=Ajukan class=mybutton onclick=nextapprovalpta('approved') >Approved</button>
								</td>
							</tr>
						</table>
					</div>";
				}else{
					$str = "select * from ".$dbname.".setup_approval where kodeunit='".$kodeorg."' and jenispersetujuan='".$kodeapproval."' and karyawanid='".$karyawanid."' and level='".$i."'";
					$res = fetchData($str);
					$dept= $res[0]['departemen'];
					
					
					$level = $i+1;
					$arrListApp = listApprove($level,$kodeapproval,$kodeorg, $dept);
					foreach($arrListApp as $key => $val){
						if($val['lokasitugas']!=''){
							@$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
						}else{
							@$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
						}
						
					}
					$tab.="<div id=test style=display:block>
						<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$notransaksi."  />
						<input hidden id=kolom value=".$kolom."  />
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
									<button class=mybutton onclick=nextapprovalpta() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
								</td>
							</tr>
						</table>
						<input type=hidden name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
						<input type=hidden name=tipepta id=tipepta value=".$capital."  />
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
		
		if($capital=='NONKAPITAL'){		
			$strorg="select statuspta,sum(rupiah) as rupiah, updateby from ".$dbname.".bgt_budget where notransaksi='".$notransaksi."'";
			$resorg=fetchData($strorg)[0];
			$nilai=$resorg['rupiah'];
			$dept = getKary($resorg['updateby'],'bagian');
		}else{
			$strorg="select statuspta,sum(hargatotal) as rupiah, updateby from ".$dbname.".bgt_kapital where notransaksi='".$notransaksi."'";
			$resorg=fetchData($strorg)[0];
			$nilai=$resorg['rupiah'];
			$dept = getKary($resorg['updateby'],'bagian');
		}
		
		$statuspengajuan=$resorg['statuspta'];
		
		$countApp = getCountApproval($kodeapproval,$kodeorg,$dept);
		// $qCountApp = selectQuery($dbname, 'approval', 'count(*) as jumlahapproval', "notransaksi = '".$notransaksi."'");
		// $resCountApp = fetchData($qCountApp);
		// $countApp = $resCountApp[0]['jumlahapproval'];
		// echo "<pre>"; print_r($countApp); print_r($kolom); exit;
		
		if ($statuspengajuan == 1) {
			throw new PDOException("Sudah di Approved");
		}else if($statuspengajuan== 9) {
			$arrDetail = detailApprove($kolom, $notransaksi, $kodeapproval);
			$level = $kolom + 1;
			//$lastapp = ceklastapproval($kolom,$kodeorg,$kodeapproval,$nilai);
			// echo "<pre>"; print_r($arrDetail); exit;
			// exit("warning: ".$user_id." ".$arrDetail['karyawanid']);
			if ($kolom != $countApp) {
				if ($user_id == $arrDetail['karyawanid']) {
					throw new PDOException(getNamaKaryawan($user_id)." Sudah di gunakan");
				}else {
					$strx = "insert into ".$dbname.".approval (nourut,notransaksi,jenispersetujuan,level,karyawanid,status,komentar,keterangan,tanggal) values ('','".$notransaksi."','".$kodeapproval."','".$level."','".$user_id."','0','','".$capital."','')";
					$owlPDO->exec($strx);
					
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					$owlPDO->exec($strx);
				}
			} else {
				if($capital=='NONKAPITAL'){					
					$strx = "update ".$dbname.".bgt_budget set statuspta='1', tutup='1' where `notransaksi`='".$notransaksi."'";
				}else{
					$strx = "update ".$dbname.".bgt_kapital set statuspta='1', tutup='1' where `notransaksi`='".$notransaksi."'";
				}
				$owlPDO->exec($strx);
				
				
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
		<tr>";
		if($hasilapp=='3'){
			echo"<td colspan=3>Reconfirm</td></tr>";
			$jud="".$_SESSION['lang']['reconfirm']."";
		}else{			
			echo"<td colspan=3>Rejection</td></tr>";
			$jud="".$_SESSION['lang']['tolak']."";
		}
		
		echo"<tr>
		<tr><td colspan=3><hr></td></tr>
		<td>".$_SESSION['lang']['note']."</td>
		<td>:</td>
		<td><input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
		</tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=\"inserttolakpta('".$_POST['kolom']."','".$hasilapp."')\" >".$jud."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
	try {
	$owlPDO->beginTransaction();
		$tglskrng=date("Y-m-d H:i:s");
		if($hasilapp!='3'){
			$hasilapp='2';
		}
		if($capital=='NONKAPITAL'){			
			$str="update ".$dbname.".bgt_budget set statuspta='".$hasilapp."' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
		}else{
			$str="update ".$dbname.".bgt_kapital set statuspta='".$hasilapp."' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
		}
		
		#update approval
		$str="update ".$dbname.".approval set status='".$hasilapp."', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
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