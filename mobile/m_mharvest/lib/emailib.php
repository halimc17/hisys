<?php

$urlerpweb = "<i style='font-size:10pt'><a href='http://owl.ksp-agro.com/'>OWL Plantation System</a> from office network
	<br>
	Or
	<br>
	<a href='http://owl.ksp-agro.com/'>OWL Plantation System</a> from public network.</i>";
	
function notifemailpr($nopp,$status,$user_id){
	global $dbname;
	global $owlPDO;
	global $urlerpweb;
	
	$str="select tipepp,dibuat,requester,tanggal from ".$dbname.".log_prapoht WHERE nopp='".$nopp."'";
	$res=fetchdata($str);
	if($res[0]['tipepp']=='PR'){
		$tipepp = 'Purchase Request';
	}else if($res[0]['tipepp']=='SR'){
		$tipepp = 'Service Request';
	}else if($res[0]['tipepp']=='CP'){
		$tipepp = 'Purchase Request (CAPEX)';
	}
	
	$kariddibuat = $res[0]['dibuat'];
	$dibuat=getNamaKaryawan($kariddibuat);
	$requester=getNamaKaryawan($res[0]['requester']);
	$departemen=getDepartemen($res[0]['requester']);
	$tglpr=$res[0]['tanggal'];
	
	$str="select sum(jumlah*hargasatuan) as total from ".$dbname.".log_prapodt where nopp='".$nopp."'";
	$res=fetchdata($str);
	$total=$res[0]['total'];
	
	$to=getUserEmail($user_id);
	$cc="";
	
	## status 1 = Pengajuan , 2 = Disetujui , 3 = Ditolak
	switch($status){
		case'1':
			$msgdt = $tipepp." dengan No ".$nopp." menunggu approval dari Bapak/Ibu";
			createnotif($nopp,'APR',$msgdt,$user_id,date('Y-m-d H:i:s'));
					
			$subject="[Notifikasi] Persetujuan ".$tipepp." a/n ".$dibuat;
			$body="Dear Bapak/Ibu,
				<br>
				<br>
				Dengan email ini diberitahukan bahwa terdapat ".$tipepp." yang menunggu approval dari Bapak/Ibu. Berikut adalah daftar dari ".$tipepp." tersebut:
				<br>
				<br>
				<table cellspacing=0 cellpadding=1 border=1 class=sortable>
					<thead style='background-color:#CDCDCD;text-align:center'>
					<tr class=rowheader>
						<td>".$tipepp." Number</td>
						<td>Creation Date</td>
						<td>Created By</td>
						<td>Requisitioner</td>
						<td>Department</td>
						<td>Total ".$tipepp." Value</td>
						<td>Currency</td>
					</tr>
					</thead>
					<tbody>";
					
				## LIST PR/SR
				$body.="<tr class=rowcontent style='text-align:center'>
					<td>".$nopp."</td>
					<td>".tglnormal($tglpr)."</td>
					<td>".$dibuat."</td>
					<td>".$requester."</td>
					<td>".$departemen."</td>
					<td>".hidezerodecimal($total,2)."</td>
					<td>IDR</td>
				</tr>";	
					
				$body.="</tbody>
				</table>
				<br>
				<br>
				Untuk menindaklanjuti silahkan klik link berikut:
				<br>
				".$urlerpweb."
				<br>
				<br>
				<br>
				Terimakasih atas perhatiannya.<br>
				Dengan Hormat,<br>
				OWL System<br><br>
				Notes :<br>
				1. Email ini dikirim secara otomatis<br>
				2. Jika ada informasi yang kurang tepat, silahkan menghubungi pembuat ".$tipepp."<br>
				3. Untuk approve ".$tipepp.", silahkan menggunakan menu MY ACCOUNT -> APPROVAL";
		break;
		
		case'2':
			$to=getUserEmail($kariddibuat);
			$lastapprover=getNamaKaryawan($user_id);
			$strx="select karyawanid from ".$dbname.".approval where notransaksi='".$nopp."' and status!='0' and karyawanid!='".$user_id."'";
			$resx=fetchdata($strx);
			$nox=0;
			$msgdt = $tipepp." dengan No ".$nopp." sudah disetujui oleh ".$lastapprover;
			if(count($resx) > 0){
				foreach($resx as $keyx=>$valx){
					$nox++;
					if($nox==1){
						$cc=getUserEmail($valx['karyawanid']);
					}else{
						$cc.=",".getUserEmail($valx['karyawanid']);
					}
					
					createnotif($nopp,'PPR',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
				}
			}
			createnotif($nopp,'PPR',$msgdt,$kariddibuat,date('Y-m-d H:i:s'));
									
			$subject="[Notifikasi] Status ".$tipepp." a/n ".$dibuat;
			$body="Dear Bapak/Ibu,
				<br>
				<br>
				Dengan email ini diberitahukan bahwa status ".$tipepp." Anda sebagai berikut:
				<br>
				<br>
				<table cellspacing=0 cellpadding=1 border=1 class=sortable>
					<thead style='background-color:#CDCDCD;text-align:center'>
					<tr class=rowheader>
						<td>".$tipepp." Number</td>
						<td>Creation Date</td>
						<td>Created By</td>
						<td>Requisitioner</td>
						<td>Department</td>
						<td>Total ".$tipepp." Value</td>
						<td>Currency</td>
						<td>Status</td>
						<td>Last Approver</td>
					</tr>
					</thead>
					<tbody>";
					
				## LIST PR/SR
				$body.="<tr class=rowcontent style='text-align:center'>
					<td>".$nopp."</td>
					<td>".tglnormal($tglpr)."</td>
					<td>".$dibuat."</td>
					<td>".$requester."</td>
					<td>".$departemen."</td>
					<td>".hidezerodecimal($total,2)."</td>
					<td>IDR</td>
					<td>Approved</td>
					<td>".$lastapprover."</td>
				</tr>";	
					
				$body.="</tbody>
				</table>
				<br>
				<br>
				Untuk menindaklanjuti silahkan klik link berikut:
				<br>
				".$urlerpweb."
				<br>
				<br>
				<br>
				Terimakasih atas perhatiannya.<br>
				Dengan Hormat,<br>
				OWL System<br><br>
				Notes :<br>
				1. Email ini dikirim secara otomatis<br>
				2. Jika ada informasi yang kurang tepat, silahkan menghubungi approver ".$tipepp;
		break;
		
		case'3':
			$to=getUserEmail($kariddibuat);
			$lastapprover=getNamaKaryawan($user_id);
			$msgdt = $tipepp." dengan No ".$nopp." ditolak oleh ".$lastapprover;
			
			$strx="select karyawanid from ".$dbname.".approval where notransaksi='".$nopp."' and status!='0' and karyawanid!='".$user_id."'";
			$resx=fetchdata($strx);
			$nox=0;
			if(count($resx) > 0){
				foreach($resx as $keyx=>$valx){
					$nox++;
					if($nox==1){
						$cc=getUserEmail($valx['karyawanid']);
					}else{
						$cc.=",".getUserEmail($valx['karyawanid']);
					}
					
					createnotif($nopp,'TPR',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
				}
			}
			
			createnotif($nopp,'TPR',$msgdt,$kariddibuat,date('Y-m-d H:i:s'));
			
			$subject="[Notifikasi] Status ".$tipepp." a/n ".$dibuat;
			$body="Dear Bapak/Ibu,
				<br>
				<br>
				Dengan email ini diberitahukan bahwa status ".$tipepp." Anda sebagai berikut:
				<br>
				<br>
				<table cellspacing=0 cellpadding=1 border=1 class=sortable>
					<thead style='background-color:#CDCDCD;text-align:center'>
					<tr class=rowheader>
						<td>".$tipepp." Number</td>
						<td>Creation Date</td>
						<td>Created By</td>
						<td>Requisitioner</td>
						<td>Department</td>
						<td>Total ".$tipepp." Value</td>
						<td>Currency</td>
						<td>Status</td>
						<td>Last Approver</td>
					</tr>
					</thead>
					<tbody>";
					
				## LIST PR/SR
				$body.="<tr class=rowcontent style='text-align:center'>
					<td>".$nopp."</td>
					<td>".tglnormal($tglpr)."</td>
					<td>".$dibuat."</td>
					<td>".$requester."</td>
					<td>".$departemen."</td>
					<td>".hidezerodecimal($total,2)."</td>
					<td>IDR</td>
					<td>Rejected</td>
					<td>".$lastapprover."</td>
				</tr>";	
					
				$body.="</tbody>
				</table>
				<br>
				<br>
				Untuk menindaklanjuti silahkan klik link berikut:
				<br>
				".$urlerpweb."
				<br>
				<br>
				<br>
				Terimakasih atas perhatiannya.<br>
				Dengan Hormat,<br>
				OWL System<br><br>
				Notes :<br>
				1. Email ini dikirim secara otomatis<br>
				2. Jika ada informasi yang kurang tepat, silahkan menghubungi approver ".$tipepp;
		break;
	}
	
	$kirim=kirimEmail($to,$cc,$subject,$body);
}

function notifemailpo($nopo,$status,$user_id){
	global $dbname;
	global $owlPDO;
	global $urlerpweb;
	
	$to=getUserEmail($user_id);
	$cc="";
	
	$str="select tanggal,purchaser,kodesupplier from ".$dbname.".log_poht where nopo='".$nopo."'";
	$res=fetchdata($str);
	$tglpo = $res[0]['tanggal'];
	$idpurchaser=$res[0]['purchaser'];
	$purchaser=getNamaKaryawan($res[0]['purchaser']);
	$namasupplier=getNamaSupplier($res[0]['kodesupplier']);
	
	$no=0;
	$str="select nopp from ".$dbname.".log_podt where nopo='".$nopo."' group by nopp";
	$res=fetchdata($str);
	foreach($res as $key=>$val){
		$no++;
		$strx="select tipepp,nopp,dibuat,requester from ".$dbname.".log_prapoht where nopp='".$val['nopp']."'";
		$resx=fetchdata($strx);
		if($no==1){
			if($resx[0]['tipepp']=='PR'){
				$tipepo = 'Purchase Order';
				$tipepp = 'Purchase Request';
			}else if($resx[0]['tipepp']=='SR'){
				$tipepo = 'Service Order';
				$tipepp = 'Service Request';
			}else if($resx[0]['tipepp']=='CP'){
				$tipepo = 'Purchase Order (CAPEX)';
				$tipepp = 'Purchase Request (CAPEX)';
			}
		}
		foreach($resx as $keyx=>$valx){
			$dibuat=getNamaKaryawan($valx['dibuat']);
			$departemen=getDepartemen($valx['requester']);
			$listpodt.="<tr class=rowcontent style='text-align:center'>
				<td>".$nopo."</td>
				<td>".tglnormal($tglpo)."</td>
				<td>".$purchaser."</td>
				<td>".$namasupplier."</td>
				<td>".$valx['nopp']."</td>
				<td>".$dibuat."</td>
				<td>".$departemen."</td>";
			if($status!='1'){
				$lastapprover=getNamaKaryawan($user_id);
				$listpodt.="<td>".($status=='2'?'Approved':'Rejected')."</td>
				<td>".$lastapprover."</td>";
			}				
			$listpodt.="</tr>";
		}
	}
	
	$listpo="<table cellspacing=0 cellpadding=1 border=1 class=sortable>
		<thead style='background-color:#CDCDCD;text-align:center'>
		<tr class=rowheader>
			<td>".$tipepo." Number</td>
			<td>".$tipepo." Creation Date</td>
			<td>".$tipepo." Created By</td>
			<td>Vendor</td>
			<td>".$tipepp." Number</td>
			<td>".$tipepp." Created By</td>
			<td>Requester Department</td>";
	if($status!='1'){
		$listpo.="<td>Status</td>
		<td>Last Approver</td>";
	}			
	$listpo.="</tr>
		</thead>
		<tbody>
		".$listpodt."
		</tbody>
	</table>";
	
	switch($status){
		case'1':
			$msgdt = $tipepo." dengan No ".$nopo." menunggu approval dari Bapak/Ibu";
			createnotif($nopo,'APO',$msgdt,$user_id,date('Y-m-d H:i:s'));
		
			$subject="[Notifikasi] Persetujuan ".$tipepo." a/n ".$purchaser;
			$body="Dear Bapak/Ibu,
				<br>
				<br>
				Dengan email ini diberitahukan bahwa ".$tipepo." untuk ".$tipepp." dibawah ini telah dibuat di sistem OWL. Berikut adalah daftar dari ".$tipepo." tersebut:
				<br>
				<br>
				".$listpo."
				<br>
				<br>
				Untuk menindaklanjuti silahkan klik link berikut:
				<br>
				".$urlerpweb."
				<br>
				<br>
				<br>
				Terimakasih atas perhatiannya.<br>
				Dengan Hormat,<br>
				OWL System<br><br>
				Notes :<br>
				1. Email ini dikirim secara otomatis<br>
				2. Jika ada informasi yang kurang tepat, silahkan menghubungi pembuat ".$tipepo." tsb<br>
				3.  Untuk melihat informasi detail dari ".$tipepo." dapat menggunakan menu MY ACCOUNT -> APPROVAL";
		break;
		
		case'2':
			$to=getUserEmail($idpurchaser);
			
			$strx="select karyawanid from ".$dbname.".approval where notransaksi='".$nopo."' and status!='0' and karyawanid!='".$user_id."'";
			$resx=fetchdata($strx);
			$nox=0;
			$msgdt = $tipepo." dengan No ".$nopo." sudah disetujui oleh ".$lastapprover;
			if(count($resx) > 0){
				foreach($resx as $keyx=>$valx){
					$nox++;
					if($nox==1){
						$cc=getUserEmail($valx['karyawanid']);
					}else{
						$cc.=",".getUserEmail($valx['karyawanid']);
					}
					
					createnotif($nopo,'PPO',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
				}
			}
			createnotif($nopo,'PPO',$msgdt,$idpurchaser,date('Y-m-d H:i:s'));
			
			$subject="Status ".$tipepo." a/n ".$purchaser;
			$body="Dear Bapak/Ibu,
				<br>
				<br>
				Dengan email ini diberitahukan bahwa status ".$tipepo." untuk ".$tipepp." dibawah ini sebagai berikut:
				<br>
				<br>
				".$listpo."
				<br>
				<br>
				Untuk menindaklanjuti silahkan klik link berikut:
				<br>
				".$urlerpweb."
				<br>
				<br>
				<br>
				Terimakasih atas perhatiannya.<br>
				Dengan Hormat,<br>
				OWL System<br><br>
				Notes :<br>
				1. Email ini dikirim secara otomatis<br>
				2. Jika ada informasi yang kurang tepat, silahkan menghubungi approver ".$tipepo." tsb<br>";
		break;
		
		case'3':
			$to=getUserEmail($idpurchaser);
			
			$strx="select karyawanid from ".$dbname.".approval where notransaksi='".$nopo."' and status!='0' and karyawanid!='".$user_id."'";
			$resx=fetchdata($strx);
			$nox=0;
			$msgdt = $tipepo." dengan No ".$nopo." ditolak oleh ".$lastapprover;
			if(count($resx) > 0){
				foreach($resx as $keyx=>$valx){
					$nox++;
					if($nox==1){
						$cc=getUserEmail($valx['karyawanid']);
					}else{
						$cc.=",".getUserEmail($valx['karyawanid']);
					}
					
					createnotif($nopo,'TPO',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
				}
			}
			createnotif($nopo,'TPO',$msgdt,$idpurchaser,date('Y-m-d H:i:s'));
			
			$subject="Status ".$tipepo." a/n ".$purchaser;
			$body="Dear Bapak/Ibu,
				<br>
				<br>
				Dengan email ini diberitahukan bahwa status ".$tipepo." untuk ".$tipepp." dibawah ini sebagai berikut:
				<br>
				<br>
				".$listpo."
				<br>
				<br>
				Untuk menindaklanjuti silahkan klik link berikut:
				<br>
				".$urlerpweb."
				<br>
				<br>
				<br>
				Terimakasih atas perhatiannya.<br>
				Dengan Hormat,<br>
				OWL System<br><br>
				Notes :<br>
				1. Email ini dikirim secara otomatis<br>
				2. Jika ada informasi yang kurang tepat, silahkan menghubungi approver ".$tipepo." tsb<br>";
		break;
	}
	
	$kirim=kirimEmail($to,$cc,$subject,$body);
}

?>