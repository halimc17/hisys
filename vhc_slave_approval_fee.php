<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');


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
	case'FEE':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['keterangan']."</td>
			<td align=center>".$_SESSION['lang']['rupiah']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		$countApp = getCountApproval('FEE');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('FEE');
		$str = "select distinct(a.notransaksi) as notransaksi, a.*,b.kodeorg,b.keterangan,
			sum(b.rupiah) as rupiah from ".$dbname.".approval a
			left join ".$dbname.".lgl_pengajuanfee b on a.notransaksi = b.notransaksi
			where a.jenispersetujuan='FEE' and a.status='0' and a.karyawanid='".$_SESSION['standard']['userid']."' group by notransaksi order by a.tanggal asc, notransaksi asc";
		//exit('error'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$kodeorg = $bar['kodeorg'];
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$kodeorg."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".substr($bar['tanggal'],0,7)."</td>
				<td align=left>".$kodeorg." - ".$optNmOrg[$kodeorg]."</td>
				<td align=left>".$bar['keterangan']."</td>
				<td align=left>".@number_Format($bar['rupiah'])."</td>
				<td align=center>
				<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"htmlfee('".$bar['notransaksi']."','".$kodeorg."','".$bar['tanggal']."','html');\">
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'FEE');
				if ($arrDetail['karyawanid'] == $_SESSION['standard']['userid'] && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
					$level = $arrDetail['level'];
					$showaction = 1;
					if ($i >= 2) {
						$countubahjumlah = 1;
					}
				}
			}
			if ($showaction == 1) {
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatafee('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakfee('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=4>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'FEE');
				if ($arrDetail['nama'] != '') {
					$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
				} else {
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
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[4];
		$countApp = getCountApproval('FEE',$koderorg);
		for($i=1;$i<=$countApp;$i++){
			$arrDetail = detailApprove($i,$notransaksi,'FEE');
			if($_SESSION['standard']['userid']==$arrDetail['karyawanid']){
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
									<button id=Ajukan class=mybutton onclick=nextapprovalfee('approved') >Approved</button>
								</td>
							</tr>
						</table>
                    </div>";
				}else{
					$level = $i+1;
					$arrListApp = listApprove($level,'FEE',$koderorg);
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
									<button class=mybutton onclick=nextapprovalfee() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
								</td>
							</tr>
						</table>
                        <input type=hidden name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
					</div>";
				}
            }
        }
		echo $tab;
	break;
	case 'insert_nextapproval':
	if($userid==''){
		$user_id = $_SESSION['standard']['userid'];
	}else{
		$user_id = $userid;
	}
	
	$temporg = explode("/",$notransaksi);
	$koderorg=$temporg[4];
	$countApp = getCountApproval('FEE', $koderorg);
	$tglskrng = date("Y-m-d H:i:s");
	$str = "select * from ".$dbname.".lgl_pengajuanfee where `notransaksi`='".$notransaksi."'"; #exit('error sasas'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	if ($bar['statuspersetujuan'] == 1) {
		exit("Warning : Sudah di Approved");
	}else if($bar['statuspersetujuan'] == 0) {
		$arrDetail = detailApprove($kolom, $notransaksi, 'FEE');
		$level = $kolom + 1;
		if ($kolom != $countApp) {
			if ($user_id == $arrDetail['karyawanid']) {
				exit("Warning : ".getNamaKaryawan($user_id)." Sudah di gunakan");
			}else if($user_id == $bar['dibuat']) {
				exit("Warning : ".getNamaKaryawan($user_id)." Pembuat Transaksi");
			} else {
				$strx = "insert into ".$dbname.".approval values ('','".$notransaksi."','FEE','".$level."','".$user_id."','0','','','')";
				try {
					$owlPDO->exec($strx);
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$_SESSION['standard']['userid']."'";
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
						$strz = "select * from ".$dbname.".organisasi where kodeorganisasi='".$bar['kodeorg']."'";
						$resz = $owlPDO->query($strz)or die(print " Gagal: ".PDOException::getMessage());
						$resz->setFetchMode(PDO::FETCH_ASSOC);
						$barz = $resz->fetch();

						$stru = "select * from ".$dbname.".lgl_pengajuanfee where notransaksi='".$notransaksi."'";
						$resu = $owlPDO->query($stru)or die(print " Gagal: ".PDOException::getMessage());
						$resu->setFetchMode(PDO::FETCH_ASSOC);
						while ($baru = $resu->fetch()){
						$nox+=1;
			            $noinvoice=date('Ymdhis')+$nox;
		                $keterangan=$baru['deskripsi'];

		                $insht[]="insert into ".$dbname.".keu_tagihanht(noinvoice, tipeinvoice, tanggal, nopo, kodesupplier, nilaiinvoice, keterangan, keterangan2, noakun, matauang, kurs, posting, kodeorg, unit, updateby, postingby) values 
		                ('".$noinvoice."','','".date('Y-m-d')."','".$notransaksi."','".$baru['createby']."','".$baru['rupiah']."','','".$keterangan."','','IDR','1','1','".$barz['induk']."','".$barz['kodeorganisasi']."','".$baru['createby']."','".$baru['createby']."')";
		                //exit($strpj);
		            	$ins[]="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset) values 
		                      ('".$noinvoice."','".$baru['noakun']."','".$baru['rupiah']."','','')";
		                
		            }

		            /*print_r($insht);
		            exit();*/
		                
		                foreach ($insht as $key => $val) {
		                	 try {
		                    $owlPDO->exec($val);
		                   
			                } catch (PDOException $e) {
			                    print " Gagal: " . $e->getMessage() . "\n";
			                    die();
			                }
		                }
		               

		                foreach ($ins as $key => $val) {
		                	try{
		                        $owlPDO->exec($val);
		                    } catch (PDOException $e) {
		                        print " Gagal: " . $e->getMessage() . "\n";
		                        die();
		                    }
		                }

			$strx = "update ".$dbname.".lgl_pengajuanfee set statuspersetujuan='1' where `notransaksi`='".$notransaksi."'";
			try {$owlPDO->exec($strx);
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$_SESSION['standard']['userid']."'";
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
		<button class=mybutton onclick=\"inserttolakfee(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$ardt=0;
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[4];
		$countApp = getCountApproval('FEE',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'FEE');
		$tglskrng=date("Y-m-d H:i:s");
		$str="update ".$dbname.".lgl_pengajuanfee set statuspersetujuan='3' where notransaksi='".$notransaksi."'" ;
		try{$owlPDO->exec($str); 
			$str="update ".$dbname.".approval set status='3', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."'";
			try{$owlPDO->exec($str); 
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