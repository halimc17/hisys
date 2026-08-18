<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
if($_GET['telid']!=''){
	require_once('master_validation_tel.php');
}else{
	require_once('master_validation.php');
}

$method           = checkPostGet('method','');
$pages            = checkPostGet('page','');
$sccari           = checkPostGet('sccari','');
$idmenu           = checkPostGet('idmenu','');
$idhelp           = checkPostGet('idhelp','');
$tentang          = checkPostGet('tentang','');
$penjelasan       = checkPostGet('penjelasan','');
$action           = checkPostGet('action','');
$linkurl          = checkPostGet('linkurl','');
$namafile         = checkPostGet('filename','');
$jenis            = checkPostGet('jenis','');
$tindaklanjut     = checkPostGet('tindaklanjut','');
$jumlahapproval   = checkPostGet('jumlahapproval','');
$userinput        = checkPostGet('userinput','');
$kodeorg          = checkPostGet('kodeorg','');
$path             = "help/upload/";

$namafile    = checkPostGet('namafile','');
$valid_ext   = array("pdf","doc","docx","jpg","png","jpeg","xls","xlsx","zip","rar");
$enum_fields = ['0'=>'Perubahan','1'=>'Saran','2'=>'Bugs','3'=>'Pengecekan','4'=>'Support','5'=>'Unposting'];

if(!empty($_GET)){$param=$_GET;}else{$param=$_POST;}

$str = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
$res = fetchdata($str);
if(count($res)>0){
	$admin=true;
}else{
	$admin=false;
}

// echo"<script language=JavaScript1.2 src=ckeditor/ckeditor.js?ver=1.1></script>";

switch ($method){
	case'getdetailuser':
		$nmrole = makeOption($dbname,'admin_rolemenuht','id,name');
		$dtrole=array();
		$w = "select * from ".$dbname.".auth_role where namauser  ='".$param['username']."'";
		$res = fetchdata($w);
		foreach($res as $bar){
			$no++;
			$rolekary[$no.". ".$nmrole[$bar['idrole']]]=$no.". ".$nmrole[$bar['idrole']];
			$dtrole[$bar['idrole']]=$bar['idrole'];
		}
		
		$w = "select * from ".$dbname.".user where namauser  ='".$param['username']."'";
		$res = fetchdata($w);
		
		
		$tab.="<label><b><i>Informasi</i></b></label><br/>";
		if(getKary($res[0]['karyawanid'],'photo')!=''){
			$namafile= "photokaryawan/".getKary($res[0]['karyawanid'],'photo');
			if (file_exists($namafile)) {
				$tab.="<br>";
				$tab.="
					<div>
						<img style='height:150px;width:150px;border: 2px solid rgb(255, 255, 255);border-radius: 10px;' src='".$namafile."'>
					</div>";
			}else{
				// $tab.="<hr>";
				$tab.="<br>";
				$tab.="
					<div>
						<img style='height:150px;width:auto;border: 2px solid rgb(255, 255, 255);border-radius: 10px;' src='images/user.png'>
					</div>";
			}
		}
		$tab.="
			<table border=0 cellpadding=3 cellspacing=1 style=font-size:10px;text-align:left;width:100%;>
			<tr>
				<td colspan=3 align=center style=background-color:#c6dff2d9;><b><i>Data Pengguna</i></b></td>
			</tr>
			<tr>
				<td>User</td><td>:</td><td>".$param['username']."</td>
			</tr>
			<tr>
				<td>Lokasi</td><td>:</td><td>".$res[0]['kodeorg']." - ".getNamaOrg($res[0]['kodeorg'])."</td>
			</tr>
			<tr>
				<td>Admin</td><td>:</td><td>".(!$admin?'No':'Yes')."</td>
			</tr>
			<tr>
				<td colspan=3 align=center style=background-color:#c6dff2d9;><b><i>Data Karyawan</i></b></td>
			</tr>
			<tr>
				<td>NIK</td><td>:</td><td>".getKary($res[0]['karyawanid'],'nik')."</td>
			</tr>
			<tr>
				<td>Nama</td><td>:</td><td>".getKary($res[0]['karyawanid'],'namakaryawan')."</td>
			</tr>
			<tr>
				<td>Jabatan</td><td>:</td><td>".getNamaJabatan(getKary($res[0]['karyawanid'],'kodejabatan'))." (".getKary($res[0]['karyawanid'],'kodejabatan').")</td>
			</tr>
			<tr>
				<td>Lokasi</td><td>:</td><td>".getKary($res[0]['karyawanid'],'lokasitugas')." - ".getNamaOrg(getKary($res[0]['karyawanid'],'lokasitugas'))."</td>
			</tr>
			";
			if(getKary($res[0]['karyawanid'],'subbagian')!=''){
				$tab.="<tr>
					<td>Divisi</td><td>:</td><td>".getKary($res[0]['karyawanid'],'subbagian')." - ".getNamaOrg(getKary($res[0]['karyawanid'],'subbagian'))."</td>
				</tr>";
			}
		$tab.="<tr>
				<td>Dept</td><td>:</td><td>".getNamaDept(getKary($res[0]['karyawanid'],'bagian'))." (".getKary($res[0]['karyawanid'],'bagian').")</td>
			</tr>
			<tr>
				<td valign=top>Role</td><td valign=top>:</td>
				<td valign=top>";
				foreach($dtrole as $role){
					$n++;
					$tab.="<div onclick=setMapUserMenuDet('".$role."') style=color:blue;cursor:pointer;>".$n.". ".$nmrole[$role]."</div>";
				}
				//<td valign=top onclick=setMapUserMenuDet('".$param['username']."') style=color:blue;cursor:pointer;>".implode("<br>",$rolekary)."</td>
			$tab.="</td>";
			$tab.="</tr>";
			
			$tanggalabsen=date('Y-m-d');
			
			$w = "select scan_date from ".$dbname.".att_pegawai a left join ".$dbname.".att_log b on a.sn=b.sn and a.pin=b.pin where karyawan  ='".$res[0]['karyawanid']."' and scan_date like '".$tanggalabsen."%' order by scan_date asc limit 1";
			$res = fetchdata($w);
			if(count($res)>0){
				foreach($res as $val){
					$n = "select masuk, toleransi from ".$dbname.".sdm_5shiftanggota a left join ".$dbname.".sdm_5shift b on a.idshift=b.id where karyawanid ='".$res[0]['karyawanid']."' order by tanggal asc LIMIT 1";
					$s = fetchdata($n);
					$color="";
					if(!empty($s)){						
						$masuk = $s[0]['masuk'];
						$toleransi = $s[0]['toleransi'];
						$date = tanggalnormal($tanggalabsen)." ".$masuk.":00";
						$maxmasuk = tambahmenitshift($date,$toleransi);
						if(strtotime($val['scan_date'])>strtotime($maxmasuk)){
							$diff      = (strtotime($maxmasuk)-strtotime($val['scan_date']));
							$hari      = floor($diff/(60*60*24));
							$jam       = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
							$menit     = floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
							$color="style=color:red; title='Terlambat'";
						}
						
						$tab.="<tr>";
						$tab.="<td valign=top>Jam Masuk</td><td valign=top>:</td>";
						$tab.="<td valign=top>".substr($maxmasuk,-8)."</td>";
						$tab.="</tr>";
					}
					$tab.="<tr><td valign=top>Jam Finger</td><td valign=top>:</td>";
					$tab.="<td valign=top ".$color.">".substr($val['scan_date'],-8)."</td>";
					$tab.="</tr>";
				}
			}
			
		$tab.="<tr>
				<td colspan=3 align=center style=background-color:#c6dff2d9;><b><i>Account Periode</i></b></td>
			</tr>";
		$str = "select periode from ".$dbname.".setup_periodeakuntansi where kodeorg ='".$_SESSION['empl']['lokasitugas']."' and tutupbuku='0' order by periode asc limit 1";
		$res = fetchdata($str);	
		$tab.="<tr>
				<td>Aktif</td><td>:</td><td>".$res[0]['periode']."</td>
			</tr>";	
			
		$tab.="</table>";
			// echo $tgl = date('Y-m-d')." 00:00:00";
			// echo tambahmenitshift();
			// $date = date('d-m-Y')." 07:00:00";
			
			// echo tambahmenitshift($date,15);
			// echo"<br>";
			// $datex = date_create(date('d-m-Y H:i:s'));
			// echo tambahmenit(16);
		echo $tab;	
	break;
	case'readhelppopup':
		if($param['sumber']=='help'){			
			$w = "select * from ".$dbname.".owlhelp where menuid ='".$idmenu."' and status='A' order by updatetime desc";
			$d = fetchdata($w);
			if(count($d)>0){			
				$q = "select * from ".$dbname.".owlhelp_read where idmenu='".$idmenu."' and karyawanid='".$_SESSION['standard']['userid']."' and jenis='".$param['sumber']."'";
				$n = fetchdata($q);
				if(count($n)>0){
					$str = "update ".$dbname.".owlhelp_read set jumlah='".count($d)."', lastdate='".date("Y-m-d H:i:s")."' where idmenu='".$idmenu."' and karyawanid='".$_SESSION['standard']['userid']."' and jenis='".$param['sumber']."'";
					try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
				}else{
					$data = array(
						'idmenu'    => $idmenu,
						'jumlah'    => count($d),
						'jenis'     => $param['sumber'],
						'karyawanid'=> $_SESSION['standard']['userid'],
						'lastdate'  => date("Y-m-d H:i:s")
					);
					$str = insertQuery($dbname,'owlhelp_read',$data,array_keys($data));
					try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
				}
			}
		}
		if($param['sumber']=='tiket'){
			$q = "select * from ".$dbname.".owlhelp_read where idmenu='".$idmenu."' and karyawanid='".$_SESSION['standard']['userid']."' and jenis='".$param['sumber']."'";
			$n = fetchdata($q);
			if(count($n)>0){
				$str = "update ".$dbname.".owlhelp_read set jumlah='".strtotime(date("Y-m-d H:i:s"))."', lastdate='".date("Y-m-d H:i:s")."' where idmenu='".$idmenu."' and karyawanid='".$_SESSION['standard']['userid']."' and jenis='".$param['sumber']."'";
				try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
			}else{
				$data = array(
					'idmenu'    => $idmenu,
					'jumlah'    => strtotime(date("Y-m-d H:i:s")),
					'jenis'     => $param['sumber'],
					'karyawanid'=> $_SESSION['standard']['userid'],
					'lastdate'  => date("Y-m-d H:i:s")
				);
				$str = insertQuery($dbname,'owlhelp_read',$data,array_keys($data));
				try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
			}
			lastUpdateHelpPopup($idmenu);
		}
	
		
	break;
	case'gethelppopup':
		$str="SELECT f.* FROM (SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)FROM (SELECT @id := ".$param['idmenu']." ) tmp1 JOIN ".$dbname.".menu ON @id <> 0) tmp2 JOIN ".$dbname.".menu f ON tmp2._id = f.Id order by action,parent";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['parent']==0){
				$modul         = $bar['caption'];
				$modulid       = $bar['id'];
				$bar['caption']= ucwords(strtolower($bar['caption']));
			}
			
			$menu[$bar['caption']]=$bar['caption'];
		}
		$frm[0].="<fieldset style=display:none><legend>Find</legend>
					<table border=0>
					<tr>
						<td>ID</td><td>:</td><td><input style=width:50px class=myinputtext id=idcarihelppopup onkeyup=showhelppopup('".$param['idmenu']."');></td>
						<td>Tentang</td><td>:</td><td><input class=myinputtext id=tentangcarihelppopup onkeyup=showhelppopup('".$param['idmenu']."');></td>
						<td>Penjelasan</td><td>:</td><td><input class=myinputtext id=penjelasancarihelppopup onkeyup=showhelppopup('".$param['idmenu']."');></td>
						<td><button class=mybutton onclick=showhelppopup('".$param['idmenu']."');>Preview</button></td>
						<td><button class=mybutton onclick=cancelhelppopup('".$param['idmenu']."');>Cancel</button></td>";
						
						// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL' or $admin==true){
							// $frm[0].="<td style=width:100%;text-align:center;><button style=background-color:#7FFF00;font-weight:bold;color:blue class=mybutton onclick=tambahhelppopup('".$param['idmenu']."');>Tambah</button></td>";
						// }	
			$frm[0].="</tr>
				</table>
				</fieldset>";
		$frm[0].="<button style=background-color:transparent;font-weight:normal;color:blue;float:left; class=mybutton onclick=reporthelppopup('".$param['idmenu']."');>Report / Add Ticket</button>";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL' or $admin==true){
			$frm[0].="<button style=background-color:transparent;font-weight:normal;color:blue;float:right; class=mybutton onclick=tambahhelppopup('".$param['idmenu']."');>Add Help</button>";
		}	
		
		$str = "select * from ".$dbname.".owlhelp where menuid='".$param['idmenu']."' and createdby='".$_SESSION['standard']['userid']."'";
		$res = fetchdata($str);
		$buat = count($res);
		
		#<fieldset><legend>Result</legend>
		$frm[0].="
				<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center' colspan=3>Penjelasan</th>";
					//<th align='center'>Penjelasan</th>";
					if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL' or $admin==true or $buat>0){
						$frm[0].="<th align='center' width=30px colspan=2>Action</th>";
					}else{						
						//$frm[0].="<th align='center' width=30px >Action</th>";
					}
			$frm[0].="</tr>
				</thead>
				<tbody id=containerhelppopup>";
		$frm[0].="</tbody>";
		$frm[0].="</table>"; #</fieldset>
		
		echo $frm[0]."####".implode(" - ",$menu);			
	break;
	case'reporthelppopup':
		$arrHsl = array("0"=>"Belum diajukan","9"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		$arrStat = array("0"=>"Open","1"=>"Close","3"=>$_SESSION['lang']['ditolak']);
		
		$frm[0]="<button style=background-color:transparent;font-weight:normal;color:blue;float:left; class=mybutton onclick=tambahreporthelppopup('".$param['idmenu']."');>Add New</button><div style='text-align:center;width:100%;font-weight:bold;'>Daftar Ticket</div>";
		
		$admin=false;
		$query = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($query);
		if(!empty($res)){			
			$admin=true;
		}
		
		
		$str = "select * from ".$dbname.".owlhelp_ticket_dt where idht in (select id from ".$dbname.".owlhelp_ticket where info_menu='".$param['idmenu']."') order by iddt asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$detail[$bar['idht']]=$bar['idht'];
			
			$lastreply[$bar['idht']]=$bar['date'];
			$lastreplyby[$bar['idht']]=$bar['username'];
		}
		
		$sudahbaca=[];
		$str = "select * from ".$dbname.".owlhelp_read where jenis='tiket' and karyawanid='".$_SESSION['standard']['userid']."' and idmenu in (select id from ".$dbname.".owlhelp_ticket where info_menu='".$param['idmenu']."')";
		$res = fetchdata($str);
		foreach($res as $bar){
			$sudahbaca[$bar['idmenu']]=$bar['idmenu'];
		}
		
		$str = "select * from ".$dbname.".owlhelp_ticket where info_menu='".$param['idmenu']."' order by status asc, id desc, date desc";
		$res = fetchdata($str);
		$buat = count($res);
		$frm[0].="
				<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center'>ID</th>
					<th align='center'>Tanggal</th>
					<th align='center'>Subject</th>
					<th align='center'>Jenis</th>
					<th align='center'>From</th>
					<th align='center'>Last Reply</th>
					<th align='center'>Status</th>
					<th align='center' style='width:30px' colspan=2>Action</th>";
		$frm[0].="</tr></thead><tbody>";
		
		$st2="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#e74c3c;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";

		$st0="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#f1c40f;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";

		$st1="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#3498db;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";
		
		foreach($res as $bar){
			$optjenis="";
			foreach($enum_fields as $key => $value){
				$disabeled="";
				if($bar['category_ticket']=='5' and $key!='5'){
					$disabeled="disabled";
				}
				if($key==$bar['category_ticket']){
					$optjenis.="<option value=".$key." ".$disabeled." selected>".$value."</option>";
				}else{				
					$optjenis.="<option value=".$key." ".$disabeled.">".$value."</option>";
				}
			}
			$new="";
			if($bar['lastupdateby']!=$_SESSION['standard']['userid'] and $bar['status']=='0'){
				$new="<br><span class='badge badge-danger badge-smaller' style='vertical-align: text-top;cursor:pointer;font-size:9px;' title='New Update.'>New</span>";
			}elseif($bar['status']=='1' and empty($sudahbaca[$bar['id']])){
				$new="<br><span class='badge badge-danger badge-smaller' style='vertical-align: text-top;cursor:pointer;font-size:9px;' title='New Update.'>New</span>";
			}
			
			$no++;
			$frm[0].="<tr class=rowcontent>";
			$frm[0].="<td align=center>".$no."</td>";
			$frm[0].="<td align=center>#".$bar['id']."</td>";
			$frm[0].="<td align=center nowrap>".$bar['date']."</td>";
			$frm[0].="<td>".$bar['subject']."</td>";
			$frm[0].="<td align=center>";
				if($bar['category_ticket']=='2'){
					$frm[0].="<span ".$st2.">".$enum_fields[$bar['category_ticket']]."</span>";
				}elseif($bar['category_ticket']=='0'){
					$frm[0].="<span ".$st0.">".$enum_fields[$bar['category_ticket']]."</span>";
				}else{
					$frm[0].="<span ".$st1.">".$enum_fields[$bar['category_ticket']]."</span>";
				}
				if($admin==true and $bar['status']=='0' and ($bar['persetujuan']=='1' or $bar['persetujuan']=='0')){
					$frm[0].="<div style='clear:both;margin-top:2px;'></div><select class='select2 help' id=jenishelppopup_".$no." onchange=\"gantijenishelppopup('".$bar['id']."','".$param['idmenu']."',this.value)\">".$optjenis."</select>";
				}
			$frm[0].="</td>";
			// $frm[0].="<td>".$enum_fields[$bar['category_ticket']]."</td>";
			$frm[0].="<td align=center>".$bar['username']."</td>";
			// $frm[0].="<td align=center>".$arrHsl[$bar['persetujuan']]."</td>";
			$frm[0].="<td align=center nowrap><font color=blue>".$lastreplyby[$bar['id']]."</font><br>".str_replace(" ","<br>",$lastreply[$bar['id']])."</td>";
			// $frm[0].="<td align=center>".$arrStat[$bar['status']]."</td>";
			$frm[0].="<td align=center nowrap>";
				if($bar['persetujuan']=='0' and $bar['status']=='0'){
					if($bar['category_ticket']=='5'){
						$post="Ajukan";
					}else{
						$post="Post";
					}
					$frm[0].="<button style=\"background-color:red;color:white;border-color:white;\" title='Post ?' class=mybutton onclick=getticketsupportajukan(".$bar['id'].",".$bar['info_menu'].")>".$post."</button>";
				}elseif($bar['status']=='0' and $bar['persetujuan']=='9'){
					$frm[0].="<button style=\"background-color:#084a04;color:#f1c40f;border-color:#f1c40f;\" title='Wait' onclick=gethistoriapproval('".$bar['id']."','event','UNPOST') class=mybutton>Waiting Approval</button>";
				}elseif($bar['status']=='0'){
					if($lastreply[$bar['id']]!=''){						
						$labelbtn="In Progress";
						$stylebtn="style=\"background-color:#f1c40f;color:white;border-color:#f1c40f;\"";
					}elseif($lastreply[$bar['id']]==''){
						$labelbtn="Waiting";
						$stylebtn="style=\"background-color:#084a04;color:#f1c40f;border-color:#f1c40f;\"";
					}else{
						$labelbtn=$arrStat[$bar['status']];
						$stylebtn="style=\"background-color:#f1c40f;color:white;border-color:#f1c40f;\"";
					}
					$frm[0].="<button ".$stylebtn." title='Click untuk menutup ticket' class=mybutton onclick=getticketsupportclose(".$bar['id'].",".$bar['info_menu'].")>".$labelbtn."</button>";
				}elseif($bar['status']=='1'){
					$frm[0].="<button style=\"background-color:#26a69a;color:white;border-color:#26a69a;\" class=mybutton>".$arrStat[$bar['status']]."</button>";
				}elseif($bar['status']=='3'){
					$frm[0].="<button style=\"background-color:#e74c3c;color:white;border-color:#e74c3c;\"  onclick=gethistoriapproval('".$bar['id']."','event','UNPOST') class=mybutton>".$arrStat[$bar['status']]."</button>";
				}else{
					$frm[0].="<button style=\"background-color:#26a69a;color:white;border-color:#26a69a;\" class=mybutton>".$arrStat[$bar['status']]."</button>";
				}
			$frm[0].="</td>";
			if($bar['persetujuan']=='0'){
				if($detail[$bar['id']]!=''){
					$frm[0].="<td align=center width=20px></td>";
				
					$frm[0].="<td onclick=openConvhelppopup('".$bar['id']."') align=center><img title='Click to open conversations' class='zImgBtn' src='images/chat1.png' onclick=openConvhelppopup('".$bar['id']."')>".$new."</td>";
				}else{					
					$frm[0].="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"tambahreporthelppopup('".$bar['info_menu']."','".$bar['id']."','edit');\" ></td>";
				
					$frm[0].="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delreporthelppopup('".$bar['info_menu']."','".$bar['id']."','delete');\" ></td>";
				}
			}else{				
				if($detail[$bar['id']]!=''){
					$frm[0].="<td onclick=openConvhelppopup('".$bar['id']."') align=center colspan=2 width=20px><img title='Click to open conversations' class='zImgBtn' src='images/chat1.png' onclick=openConvhelppopup('".$bar['id']."')>".$new."</td>";
				}else{				
					$frm[0].="<td onclick=openConvhelppopup('".$bar['id']."') align=center colspan=2 width=20px><img title='Click to open conversations' class='zImgBtn' src='images/chat0.png' onclick=openConvhelppopup('".$bar['id']."')>".$new."</td>";
				}
			}
		}
		
		$frm[0].="</tbody>";
		$frm[0].="</table>";
		
		echo $frm[0];
	break;
	case'getticketsupportajukan':
		getOtorisasiHelpPopup($param);
		
		$str = "select * from ".$dbname.".owlhelp_ticket where id='".$param['idhelp']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$subject=$bar['subject'];
			$username=$bar['username'];
			$category_ticket=$bar['category_ticket'];
			$kodeorg=$bar['kodeorg'];
		}
		
		$str = "select * from ".$dbname.".user where namauser='".$username."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$karyawanid=$bar['karyawanid'];
		}
		if(!empty($kodeorg)){
			$lokasitugas=$kodeorg;
		}else{			
			$lokasitugas=getKary($karyawanid,'lokasitugas');
		}
		$bagian=getKary($karyawanid,'bagian');
		
		$kosong="";
		// $str = "select * from ".$dbname.".setup_approval where jenispersetujuan='UNPOST' and kodeunit='".$lokasitugas."' and departemen='".$bagian."'  order by level asc";
		// $res = fetchdata($str);
		// if(empty($res)){
			// $str = "select * from ".$dbname.".setup_approval where jenispersetujuan='UNPOST' and kodeunit='".$lokasitugas."' and departemen='' order by level asc";
			// $res = fetchdata($str);
			// if(empty($res)){
				// $kosong="Persetujuan belum dilakukan setup.";
			// }
		// }

		$tab="<table width=100%>
			<tr>
				<td>ID</td>
				<td>:</td>
				<td>#".$param['idhelp']."</td>
			</tr>
			<tr>
				<td>Menu</td>
				<td>:</td>
				<td>".getNamaMenu2($param['idmenu'])."</td>
			</tr>
			<tr>
				<td>Subject</td>
				<td>:</td>
				<td>".$subject."</td>
			</tr>
			<tr>
				<td>From</td>
				<td>:</td>
				<td nowrap>".getKary($karyawanid)." - ".getKary($karyawanid,'lokasitugas')." <br>".getDepartemen($karyawanid,'bagian')."</td>
			</tr>";
		$tab.="<tr>
				<td>Approval</td>
				<td>:</td>
				<td>".$kosong."</td>
			</tr>
			";
			
			$countApp = getCountApproval('UNPOST',$lokasitugas,$bagian);
			if(empty($countApp)){
				$kosong="Persetujuan belum dilakukan setup.";
			}
			for($i=1;$i<=$countApp;$i++){
				$arrList = listApprove($i,'UNPOST',$lokasitugas,$bagian);
				
				$optpersetujuan="";$nomor="0";
				foreach($arrList as $key=>$val){
					$nomor++;
					if(count($arrList)>1 and $nomor==1){			
						$optpersetujuan.="<option value='' selected>Pilih Data</option>";
					}
					$optpersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
				}
				$tab.="<tr><td align=right>Level</td><td>".$i."</td><td><select style=width:200px id=approvaltucketsupport_".$i." >".$optpersetujuan."</select></td></tr>";
			}	

		$tab.="<tr><td align=center colspan=3>&nbsp;</td></tr>";
		if(empty($countApp)){
			$tab.="<tr><td align=center colspan=3>".$kosong."</td></tr>";
		}else{
			$tab.="<tr><td align=center colspan=3><button title='Ajukan ???' class='mybutton' onclick=\"getticketsupportajukansimpan('".$param['idhelp']."','".$param['idmenu']."','".$countApp."')\">Simpan</button></td></tr>";
		}
		$tab.="</table>";
		
		if($category_ticket=='5'){			
			echo $tab;
		}else{
			$data = array(
				'persetujuan'  => '1'
			);
			$where = "id='".$param['idhelp']."'";
			$str = updateQuery($dbname,'owlhelp_ticket',$data,$where);
			$owlPDO->exec($str);
		}
	break;
	case'gantijenishelppopup':
		$str = "select * from ".$dbname.".owlhelp_ticket where id='".$param['idhelp']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$subject=$bar['subject'];
			$persetujuan=$bar['persetujuan'];
			$category_ticket=$bar['category_ticket'];
			$idmenu=$bar['info_menu'];
		}
		
		if($param['jenisbaru'] !=2){				
			$str = "select * from ".$dbname.".owlhelp_ticket where info_menu='".$idmenu."' and category_ticket!='2' and persetujuan='1' and status='0' and pic_client='".$_SESSION['standard']['userid']."'";
			$res = fetchdata($str);
			if(count($res)>=2){
				exit("Error: Masih ada ticket yang terbuka / aktif untuk menu ini sebanyak <b>".count($res)."</b> ticket, silahkan tolak ticket ini.");
			}
		}
		
		
		$change=true;
		$persetujuanbaru=1;
		if($category_ticket==0){
			$change=false;				
			if($persetujuan==1){
				$change=true;				
				$persetujuanbaru=0;
			}
			if($persetujuan==0){
				$change=true;
				$persetujuanbaru=0;
			}
		}
		if($param['jenisbaru']!='0'){
			$persetujuanbaru=1;			
		}else{
			$persetujuanbaru=0;
		}
		
		$data = array(
			'category_ticket'=> $param['jenisbaru'],
			'persetujuan'  => $persetujuanbaru
		);
		$where = "id='".$param['idhelp']."'";
		$str = updateQuery($dbname,'owlhelp_ticket',$data,$where);
		if($change==true){				
			$owlPDO->exec($str);
			
			$str = "select max(iddt) as id from ".$dbname.".owlhelp_ticket_dt";
			$res = fetchdata($str);
			if($res[0]['id']>0){
				$iddt=$res[0]['id']+1;
			}else{
				$iddt=1;
			}
			
			$st2="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#e74c3c;color:#ffffff;border-radius:1rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";

			$st0="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#f1c40f;color:#ffffff;border-radius:1rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";

			$st1="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#3498db;color:#ffffff;border-radius:1rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";

			if($category_ticket=='2'){
				$awal="<span ".$st2.">".$enum_fields[$category_ticket]."</span>";				
			}elseif($category_ticket=='0' or $category_ticket=='5'){
				$awal="<span ".$st0.">".$enum_fields[$category_ticket]."</span>";
			}else{
				$awal="<span ".$st1.">".$enum_fields[$category_ticket]."</span>";
			}
			
			if($param['jenisbaru']=='2'){
				$akhir="<span ".$st2.">".$enum_fields[$param['jenisbaru']]."</span>";
			}elseif($param['jenisbaru']=='0' or $param['jenisbaru']=='5'){
				$akhir="<span ".$st0.">".$enum_fields[$param['jenisbaru']]."</span>";
			}else{
				$akhir="<span ".$st1.">".$enum_fields[$param['jenisbaru']]."</span>";
			}
			
			
			
			$desc = "<ul>Jenis telah dilakukan penyesuaian : <br><br><li>1. Username : <b>".$_SESSION['standard']['username']."</b></li><br><li>2. Tanggal : <b>".date("Y-m-d H:i:s")."</b></li><br><li>3. Jenis awal : ".$awal."</li><br><li>4. Jenis akhir : ".$akhir."</li></ul>";
			
			$data = array(
				'iddt'         => $iddt,
				'date'         => date("Y-m-d H:i:s"),
				'username'     => $_SESSION['standard']['username'],
				'description'  => $desc,
				'idht'         => $param['idhelp']
			);
			$str = insertQuery($dbname,'owlhelp_ticket_dt',$data,array_keys($data));
			$owlPDO->exec($str);
			
			lastUpdateHelpPopup($param['idhelp']);
		}else{
			exit("Gagal");
		}
			
	break;
	case'getticketsupportajukansimpan':
		try {
			$owlPDO->beginTransaction();
		
			$str = "update " . $dbname . ".owlhelp_ticket set persetujuan=9 where id='".$param['idhelp']."'";
			$owlPDO->exec($str);
			
			for($i=1;$i<=$param['jumlahapproval'];$i++){
				if($param['persetujuan'][$i]==''){
					throw new PDOException("Persetujuan ".$i." masih kosong.");
				}
				
				$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
					   values('".$param['idhelp']."','UNPOST','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";	
				$owlPDO->exec($str);
			}
			
			
			$owlPDO->commit();
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning: " . addslashes($e->getMessage());
		}
		lastUpdateHelpPopup($param['idhelp']);
	break;
	
	case'getticketsupportclose':
		$admin=false;
		$listticket=false;
		$query = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($query);
		if(!empty($res)){			
			$admin=true;
			$listticket=true;
		}
		
		$str = "select * from ".$dbname.".owlhelp_ticket where id='".$param['idhelp']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['username']==$_SESSION['standard']['username']){
				$listticket=true;
			}
		}
		$str = "select * from ".$dbname.".owlhelp_ticket_dt where idht='".$param['idhelp']."' order by iddt asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['pictindaklajut']==$_SESSION['standard']['userid']){
				$listticket=true;
			}
			if($bar['username']==$_SESSION['standard']['username']){
				$listticket=true;
			}
		}	
		
		if($listticket==true){			
			$data = array(
				'solveddate'=> date("Y-m-d H:i:s"),
				'solvedby'  => $_SESSION['standard']['userid'],
				'status'    => '1'
			);
			$where = "id='".$param['idhelp']."'";
			$str = updateQuery($dbname,'owlhelp_ticket',$data,$where);
			//exit("error".print_r($param));
			$owlPDO->exec($str);
			
			lastUpdateHelpPopup($param['idhelp']);
		}else{
			exit("Warning: Anda tidak memiliki otorisasi untuk menutup ticket ini.");
		}
		
		
	break;
	
	case'getticketsupporttolak':
		$admin=false;
		$listticket=false;
		$query = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($query);
		if(!empty($res)){			
			$admin=true;
			$listticket=true;
		}
		if($admin==true){
			try {
			$owlPDO->beginTransaction();
				$str = "select max(iddt) as id from ".$dbname.".owlhelp_ticket_dt";
				$res = fetchdata($str);
				if($res[0]['id']>0){
					$iddt=$res[0]['id']+1;
				}else{
					$iddt=1;
				}
				
				$tambahan="Ticket ini telah ditolak oleh : <b>".$_SESSION['standard']['username']."</b>, dengan penjelasan sebagai berikut :<br>";
				
				
				$data = array(
					'iddt'            => $iddt,
					'date'            => date("Y-m-d H:i:s"),
					'username'        => $_SESSION['standard']['username'],
					'description'     => $tambahan.$penjelasan,
					'pictindaklajut'  => $tindaklanjut,
					'idht'            => $idhelp
				);
				$str = insertQuery($dbname,'owlhelp_ticket_dt',$data,array_keys($data));
				$owlPDO->exec($str);
				
				
				$countfiles = @count($_FILES['file']['name']);
				if($countfiles>5){
					throw new PDOException("Jumlah maksimal hanya 5 file.");
				}
				if($countfiles>0){
					for($i=0;$i < $countfiles;$i++){
						$filesize+=$_FILES['file']['size'][$i];
					}
					if($filesize>250000000){
						throw new PDOException("File size terlalu besar (".formatBytes($filesize).").");
					}
					
					$path="fileupload/owlreport/".$idhelp."/";
					if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					$tempfile="";
					for($i=0;$i < $countfiles;$i++){
						$file_tmpname= file_get_contents($_FILES['file']['tmp_name'][$i]);
						$filename    = $_FILES['file']['name'][$i];
						
						$file_extension = pathinfo($path.$filename, PATHINFO_EXTENSION);
						$file_extension = strtolower($file_extension);
						
						if($_FILES['file']['error'][$i]==0){
							$filename = $idhelp."_".$iddt."_".$filename;
							$tempfile.=$filename."|";
							
							if(file_exists($path.$filename)) {
								unlink($path.$filename);
							}
							if(in_array($file_extension,$valid_ext)){
								file_put_contents($path.$filename,$file_tmpname);
							}else{
								throw new PDOException("File tidak diizinkan.");
							}
						}
					}
					
					
					$data = array(
						'file'    => $tempfile
					);
					$where = "idht='".$idhelp."' and iddt='".$iddt."'";
					$str = updateQuery($dbname,'owlhelp_ticket_dt',$data,$where);
					$owlPDO->exec($str);
					
				}
				
				
				$data = array(
					'solveddate'=> date("Y-m-d H:i:s"),
					'solvedby'  => $_SESSION['standard']['userid'],
					'status'    => '3'
				);
				$where = "id='".$idhelp."'";
				$str = updateQuery($dbname,'owlhelp_ticket',$data,$where);
				// exit("error".print_r($str));
				$owlPDO->exec($str);
				
				
				#execute
				$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
			
			if($tindaklanjut!=''){
				$str = "select * from ".$dbname.".owlhelp_ticket where 1=1 and id= ".$idhelp."";
				$res = fetchdata($str);
				$info_menu = $res[0]['info_menu'];
				$subject = $res[0]['subject'];
				$description = nl2br($res[0]['description']);
				
				$textx="<html>
					<head>
						<body>
							Dengan Hormat,
							<br>
							<br>
							Ada ticket support yang perlu bapak/ibu tindak lanjuti, sebagai berikut :
							<br>
							<br>
							<b>Nomor :</b> #".$idhelp."<br>
							<b>Path Menu :</b> ".getNamaMenu2($info_menu)."<br>
							<b>Judul :</b> ".$subject."<br>
							<b>Penjelasan :</b> ".$description."<br>
							<b>Balasan dari :</b> ".$_SESSION['standard']['username']." ".date("Y-m-d H:i:s")."<br>
							<i>".$penjelasan."</i><br><br>
							Demikian disampaikan, terima kasih.<br>
							Salam,
							
							<br><br><br>
							<i>Pesan ini dikirim otomatis, untuk membalas silahkan buka https://owl.ksp-agro.com kemudian menu : My Account - Ticket Report atau masuk ke menu : ".getNamaMenu2($info_menu)." kemudian click <b>Help</b> kemudian click <b>Report/Add Ticket</b></i>
						</body>
					</head>
			   </html>
				";
				
				$to = getUserEmail($tindaklanjut);
				$subjectx="[Notifikasi]Ticket Support Nomor  #".$idhelp."";
				if(isset($to)){
					$kirim = kirimEmail($to, '', $subjectx, $textx);
				}
			}
			
			lastUpdateHelpPopup($idhelp);
				
		}else{
			exit("Warning: Anda tidak memiliki otorisasi untuk menutup ticket ini.");
		}
	break;
	
	case'openConvhelppopup':
		$admin=false;
		$query = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($query);
		if(!empty($res)){			
			$admin=true;
		}
		
		$listticket=false;
		$str = "select * from ".$dbname.".owlhelp_ticket where id='".$param['idhelp']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['username']==$_SESSION['standard']['username']){
				$listticket=true;
			}
			
			$tab="<fieldset style=background-color:#c8e6f7;>";
			$tab.="<legend style=background-color:#92d6fc><i>Report By: ".$bar['username']." at: ".$bar['date']."</i></legend>";
			$tab.="<br><label><b><i>Subject: ".$bar['subject']."</i></b></label>";
			$tab.="<br><label><b><i>Jenis: ".$enum_fields[$bar['category_ticket']]."</i></b></label>";
			if($bar['userinput']!=''){				
				$tab.="<br><label><b><i>User Input: ".$bar['userinput']."</i></b></label>";
				$tab.="<br><label><b><i>Kode Org: ".getNamaOrg($bar['kodeorg'])."</i></b></label>";
			}
			$tab.="<br><br>".nl2br($bar['description']);
			
			if($bar['file']!= null){
				$path="fileupload/owlreport/".$param['idhelp']."/";
				$tab.="<br><br>Attachment :<br>";
				
				$fileT=explode("|",$bar['file']);
				foreach($fileT as $filename){
					if($filename!=''){						
						$tab.="<a href='".$path.$filename."' download target=blank><span style='color:blue;cursor:pointer' title=\"Klik untuk download\">".$filename."</span></a><br>";
					}
				}
			}
			
			$tab.="<br><br></fieldset>";
			
			$status=$bar['status'];
			$category_ticket=$bar['category_ticket'];
			$persetujuan=$bar['persetujuan'];
			$solveddate=$bar['solveddate'];
			$solvedby=$bar['solvedby'];
			$info_menu=$bar['info_menu'];
		}
	
		
		$adadetail=false;
		$str = "select * from ".$dbname.".owlhelp_ticket_dt where idht='".$param['idhelp']."' order by iddt asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$adadetail=true;
			if($bar['pictindaklajut']==$_SESSION['standard']['userid']){
				$listticket=true;
			}
			if($bar['username']==$_SESSION['standard']['username']){
				$listticket=true;
			}
			
			$no++;
			$i=$no%2;
			
			$tomboldel="";
			$selisih = (((strtotime (date('Y-m-d H:i:s')) - strtotime ($bar['date'])))/(60));
			if($selisih<1 and $bar['username']==$_SESSION['standard']['username']){
				$tomboldel="<td align=right nowrap width=30px><div name=labeleditdeletepopup[]><img src='images/application/application_edit.png' style=\"height:10px;width:10px;vertical-align:center;cursor:pointer;display:none;\" title='Edit' onclick=editdtpopup('".$bar['idht']."','".$bar['iddt']."')>";
				$tomboldel.="&nbsp;&nbsp;<img src='images/application/application_delete.png' style=\"height:10px;width:10px;vertical-align:center;cursor:pointer;\" title='Delete' onclick=deldtpopup('".$bar['idht']."','".$bar['iddt']."')></div></td><td style=min-width:40px align=right><div style=color:red; name=timerhelppopup[]></div></td>";
			}
			
			if($i==0){
				$tab.="<fieldset style=background-color:#c8e6f7;>";
				$tab.="<legend style=background-color:#92d6fc;height:10px;><table><td><i>Reply By: ".$bar['username']." at: ".$bar['date']."</i> </td>".$tomboldel."</table></legend>";
			}else{
				$tab.="<fieldset style=background-color:#d1ffeb;>";
				$tab.="<legend style=background-color:#92fccd;height:10px;><table><td><i>Reply By: ".$bar['username']." at: ".$bar['date']."</i> </td>".$tomboldel."</table></legend>";
			}
			if($bar['pictindaklajut']!=''){				
				$tab.="<br><label><b><i>@ ".getKary($bar['pictindaklajut'])."</i></b></label></br></br>";
				$usertindaklanjut=$bar['pictindaklajut'];
			}else{
				$usertindaklanjut="";				
			}
			
			$tab.="<br>".nl2br($bar['description']);
			
			
			if($bar['file']!= null){
				$path="fileupload/owlreport/".$param['idhelp']."/";
				$tab.="<br><br>Attachment :<br>";
				
				$fileT=explode("|",$bar['file']);
				foreach($fileT as $filename){
					if($filename!=''){						
						$tab.="<a href='".$path.$filename."' download target=blank><span style='color:blue;cursor:pointer' title=\"Klik untuk download\">".$filename."</span></a><br>";
					}
				}
			}
			$tab.="<br><br></fieldset>";
		}
		
		$optper="<option value=''>&nbsp;</option>";
		$whereKary= " and tipekaryawan in (0) and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date('Y-m-d')."')";
		$str = "select karyawanid,nik, namakaryawan, kodejabatan from ".$dbname.".datakaryawan where 1=1 ".$whereKary." order by kodejabatan, namakaryawan asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$d=$bar['kodejabatan'];
			if($d!=$n){			
				$optper.="<optgroup label='".getNamaJabatan($d)."'>";
			}
			$optper.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']."</option>";
			$n=$d;
			if($d!=$n){			
				$optper.="</optgroup>";
			}
		}
		
		$valpenjelasanhelppopup2=$tmblsimpan="";
		if($usertindaklanjut!="" and $_SESSION['standard']['userid']!=$usertindaklanjut){
			$valpenjelasanhelppopup2="Anda diizinkan untuk membalas ticket ini setelah ada tindak lanjut dari : ".getKary($usertindaklanjut)."";
			$tmblsimpan="display:none;";
		}
		if($admin==false){
			if($listticket==false){
				$tmblsimpan="display:none;";
				$status=2;
			}else{
				$valpenjelasanhelppopup2=$tmblsimpan="";				
			}
		}else{
			$valpenjelasanhelppopup2=$tmblsimpan="";							
		}
		
		if($persetujuan!='1'){
			$status=2;					
		}
		
		if($status==0){
			$st0="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#d1e3fa;color:green;font-weight:normal;font-size:12px;border-radius:0.3rem;text-align:center;height: 30px;padding-left:10px;padding-right:10px;padding-top:5px;padding-bottom:2px;float:left;\"";
			
			$tab.="<fieldset style=background-color:#defae8;>";
			$tab.="<legend style=background-color:#faec23><i>Reply Form</i></legend>";
			$tab.="
				<table border=0 cellpadding=1 cellspacing=1 style=width:100%>
					<tr>
						<td width=200px>Penjelasan :</td>
						<td nowrap style=vertical-align:bottom;>
							<div><span ".$st0.">Agar seluruh fitur dapat berjalan dengan baik disarankan menggunakan tampilan full screen, click icon </span>
							<span id='cke_787' class='cke_toolbar' aria-labelledby='cke_787_label' role='toolbar'><span id='cke_787_label' class='cke_voice_label'>Tools</span><span class='cke_toolbar_start'></span><span class='cke_toolgroup' role='presentation'><a id='cke_788' class='cke_button cke_button__maximize cke_button_off' title='Maximize' tabindex='-1' hidefocus='true' role='button' aria-labelledby='cke_788_label' aria-describedby='cke_788_description' aria-haspopup='false' aria-disabled='false'><span class='cke_button_icon cke_button__maximize_icon' style='background-image:url('http://localhost/ksp/ckeditor/plugins/icons.png?t=JB9C');background-position:0 -672px;background-size:auto;'>&nbsp;</span><span id='cke_788_label' class='cke_button_label cke_button__maximize_label' aria-hidden='false'>Maximize</span><span id='cke_788_description' class='cke_button_label' aria-hidden='false'></span></a></span><span class='cke_toolbar_end'></span></span>
							
							<fieldset style=float:left><i>@ <select  style=width:250px id=tindaklanjut class='select2 help'>".$optper."</select> <img src=images/application/application_delete.png style=\"height:10px;width:10px;vertical-align:center;cursor:pointer;\" title='Hapus Data' onclick=\"deletetindaklanjut()\"></i></fieldset>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan=2><textarea rows=10 placeholder=required class=ckeditor id=penjelasanhelppopup2 name=penjelasanhelppopup2 type='text' onkeypress='return tanpa_kutip(event)' style='width:98%;'>".$valpenjelasanhelppopup2."</textarea></td>
					</tr>
					<tr>
						<td colspan=2>Attachment :</td>
					</tr>
					<tr>
						<td nowrap colspan=2>File : <input id=fileshelppopup2 name=fileshelppopup2[] type=file multiple></td>
					</tr>
					<tr>
						<td colspan=2>&nbsp;</td>
					</tr>
					<tr>
						<td align=center colspan=2>";
							if($admin==true and $adadetail==false){
								$tab.="<button onclick=getticketsupporttolak('".$param['idhelp']."'); style='height:30px;background-color:#e74c3c;color:white;border-color:#e74c3c;".$tmblsimpan."' class=mybutton name=preview id=preview>Tolak</button>";								
							}else{								
								$tab.="<button title='Click untuk menutup ticket' style='height:30px;background-color:green;color:white;border-color:#e74c3c;".$tmblsimpan."' class=mybutton onclick=getticketsupportclose(".$param['idhelp'].",".$info_menu.")>Close Ticket</button>";
							}
							$tab.="<button onclick=simpanreporthelppopup2('".$param['idhelp']."'); style='height:30px;".$tmblsimpan."' class=mybutton name=preview id=preview>Simpan</button>";
					$tab.="</td>
					</tr>
				</table>
			";
			$tab.="</fieldset>";
		}elseif($status==1){
			$tab.="<fieldset style=background-color:#cff7cd;>";
			$tab.="<legend style=background-color:green><i>Closed / Solved</i></legend>";
			$tab.="<br><label>Ticket telah ditutup pada :</label>";
			$tab.="<br><br><label>Tanggal : <b>".$solveddate."</b></label>";
			$tab.="<br><br><label>Oleh : <b>".getKary($solvedby)."</b></label>";
			$tab.="</fieldset>";
		}
		
		echo $tab;
	break;
	case'showhelppopup':
		$wh="";
		$str = "select * from ".$dbname.".owlhelp where menuid='".$param['idmenu']."' and createdby='".$_SESSION['standard']['userid']."'";
		$res = fetchdata($str);
		$buat = count($res);
		
		if($param['jenis']=='x'){
			$tab.="
				<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center' colspan=3>Penjelasan</th>";
					if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL' or $admin==true or $buat>0){
						$tab.="<th align='center' width=30px colspan=2>Action</th>";
					}else{						
						//$frm[0].="<th align='center' width=30px >Action</th>";
					}
			$tab.="</tr>
				</thead>
				<tbody>";
		}
	
		if($param['idhelp']!=''){
			$wh.=" and id like '%".$param['idhelp']."%'";
		}
		if($param['tentang']!=''){
			$wh.=" and judul like '%".$param['tentang']."%'";
		}
		if($param['penjelasan']!=''){
			$wh.=" and penjelasan like '%".$param['penjelasan']."%'";
		}
		$str="select * from ".$dbname.".owlhelp where menuid='".$param['idmenu']."' ".$wh." order by id desc";
		$res = fetchdata($str);
		if(count($res)>0){			
			foreach($res as $bar){
				$no++;
				$tab.="<tr class=rowcontent style='cursor:pointer;font-size:14px'>";
				$tab.="<td valign=top align=center style=font-weight:bold;background-color:#21ffaa;>".$no."</td>";
				
				$tab.="<td valign=top align=left style=color:blue;cursor:pointer;width:50px;background-color:#21ffaa; title='Click' onclick=jumpHelp('/help_".$bar['id']."',this)>/help_".$bar['id']."</td>";
				$tab.="<td valign=top align=left title='Click' onclick=jumpHelp('/help_".$bar['id']."',this) style=font-weight:bold;background-color:#21ffaa;>".nl2br($bar['judul'])."</td>";
				if($bar['namafile']!=''){
					$tab.="<td width=80px valign=top align=center style=background-color:#21ffaa;><a href='".$bar['namafile']."' download target=blank style='color:blue;cursor:pointer' title=\"Klik untuk download\n".$bar['namafile']."\">download</a></td>";
				}else{
					$tab.="<td width=20px style=background-color:#21ffaa;></td>";					
				}
				if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL' or $admin==true or $buat>0){
					if($bar['createdby']==$_SESSION['standard']['userid'] or $admin==true){
						$tab.="<td width=20px valign=top align=center style=font-weight:bold;background-color:#21ffaa;><img class=zImgBtn src=images/application/application_edit.png onclick=\"tambahhelppopup('".$bar['menuid']."','".$bar['id']."','edit');\" title='Edit'></td>";
						
						$tab.="<td width=20px valign=top align=center style=font-weight:bold;background-color:#21ffaa;><img class=zImgBtn src=images/application/application_delete.png onclick=\"delhelppopup('".$bar['id']."','".$bar['menuid']."');\" title='Delete'></td>";
					}else{
						$tab.="<td width=20px style=font-weight:bold;background-color:#21ffaa;></td>";
						$tab.="<td width=20px style=font-weight:bold;background-color:#21ffaa;></td>";
					}
				}
				$tab.="</tr>";
				$tab.="<tr class=rowcontent>";
				$tab.="<td valign=top align=left colspan=6 style='font-size:14px;background-color:rgb(212, 236, 255);'>".nl2br($bar['penjelasan'])."</td>";
				$tab.="</tr>";
				
				$tab.="</tr>";
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=6 style=background-color:#c4ffe9;>&nbsp;</td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=6>Data tidak ditemukan</td>";
			$tab.="</tr>";
		}
		if($param['jenis']=='x'){
			$tab.="</tbody>";
			$tab.="</table>";
		}
		echo $tab;
	break;
	case'jumpHelp':
		$arrid = explode("_",$param['idhelp']);
		$str = "select * from ".$dbname.".owlhelp where id='".$arrid[1]."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$nmmenu = makeOption($dbname,'menu','id,caption');
			foreach($res as $bar){
				$sql = "SELECT f.* FROM (SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)FROM (SELECT @id := ".$bar['menuid']." ) tmp1 JOIN ".$dbname.".menu ON @id <> 0) tmp2 JOIN ".$dbname.".menu f ON tmp2._id = f.Id order by action,parent";
				$req = fetchdata($sql);
				foreach($req as $val){
					$menu[$val['caption']]=strtoupper(strtolower($val['caption']));
				}
				
				/* $tab="<table border=0>";
				$tab.="<tr><td width=80px><b>ID</b></td><td>: <i>/help_".$bar['id']."</i></td></tr>";
				$tab.="<tr><td><b>Modul </td><td>: <b>".strtoupper(strtolower($bar['modul']))."</b></td></tr>";
				$tab.="<tr><td><b>Menu </td><td>: <b><i>".strtoupper(strtolower($nmmenu[$bar['menuid']]))."</i></b></td></tr>";
				$tab.="<tr><td><b>Alamat </td><td>: <i>".implode(" - ",$menu)."</i></td></tr>";
				
				$tab.="<tr><td><b>Tentang</b></td><td>:</td></tr><tr><td colspan=2><i>&nbsp;&nbsp;".nl2br($bar['judul'])."</i></td></tr><tr><td colspan=2>&nbsp;</td></tr>";
				if($bar['penjelasan']!=''){					
					$tab.="<tr><td><b>Penjelasan</b></td><td>:</td></tr><tr><td colspan=2>".nl2br($bar['penjelasan'])."</td></tr>";
				}
				if($bar['namafile']!=''){
					$tab.="<tr><td colspan=2>&nbsp;</td></tr><tr><td colspan=2>Attachment :</td></tr>";
					$tab.="<tr><td colspan=2><a href='".$bar['namafile']."' download target=blank><span style='color:blue;cursor:pointer' title=\"Klik untuk download\">".$bar['namafile']."</span></a></td></tr>";
				}
				$tab.="</table>"; */
				
				if($namafile!=''){
					$tab.="<div style=\"font-family:sans-serif;font-size:10px;\">";
				}
				$tab.="ID : <b>/help_".$bar['id']."</b><br>";
				$tab.="Modul : <b>".strtoupper(strtolower($bar['modul']))."</b><br>";
				//$tab.="Menu : <b>".strtoupper(strtolower($nmmenu[$bar['menuid']]))."</b></b><br>";
				$tab.="Menu : <b>".implode(" - ",$menu)."</b><br>";
				$tab.="Tentang : <b>".nl2br($bar['judul'])."</b><br>";
				if($bar['penjelasan']!=''){					
					$tab.="<br><b>Penjelasan :</b><br>".nl2br($bar['penjelasan']);
				}
				if($bar['namafile']!=''){
					$tab.="<br>Attachment :<br>";
					$tab.="<a href='".$bar['namafile']."' download target=blank><span style='color:blue;cursor:pointer' title=\"Klik untuk download\">".$bar['namafile']."</span></a>";
				}
				if($namafile!=''){					
					$tab.="</div>";
				}
			}
			
			if($namafile!=''){
				$dompdf = new Dompdf();
				$dompdf->load_html($tab);
				$dompdf->setPaper('A4', 'potrait');
				$dompdf->render();
				$canvas = $dompdf->get_canvas();
				if (file_exists($namafile)){
					unlink($namafile);
				}
				file_put_contents($namafile, $dompdf->output());
			}else{
				echo $tab;
			}
		}
		
	break;
	
	case'tambahhelppopup':
		if($param['action']=='edit'){
			$str = "select * from ".$dbname.".owlhelp where id='".$param['idhelp']."'";
			$res = fetchdata($str);
			foreach($res as $bar){				
				$tentang=htmlentities($bar['judul']);
				$penjelasan=htmlentities($bar['penjelasan']);
			}
		}
		
		//<td colspan=2><input id=tentanghelppopup placeholder=required class=myinputtext style=width:95%;height:30px value=\"".$tentang."\"></td>
		
		echo"
			<table border=0 style=width:100%><td align=center>
				<table border=0 cellpadding=1 cellspacing=1 style=width:90%>
					<tr>
						<td>Tentang :</td><td style=cursor:pointer;color:blue;float:right;><button style=background-color:transparent;font-weight:bold;color:blue; class=mybutton onclick=getinfohelppopup();>Petunjuk ?</button>&nbsp;&nbsp;</td>
						<input id=idmenutambahhelppopup style=display:none value=".$param['idmenu'].">
						<input id=idhelptambahhelppopup style=display:none value=".$param['idhelp'].">
					</tr>
					<tr>
						<td colspan=2><textarea rows=1 placeholder=required maxlength=400 id=tentanghelppopup type='text' onkeypress='return tanpa_kutip(event)' style='width:98%;'>".$tentang."</textarea></td>
						
					</tr>
					<tr>
						<td colspan=2>Penjelasan :</td>
					</tr>
					<tr>
						<td colspan=2><textarea rows=23 placeholder=required class=ckeditor id=penjelasanhelppopup type='text' onkeypress='return tanpa_kutip(event)' style='width:98%;'>".$penjelasan."</textarea></td>
					</tr>
					<tr>
						<td colspan=2>Attachment <i>(pilih salah satu file atau link)</i> :</td>
					</tr>
					<tr>
						<td style=width:50%><fieldset><table border=0 width=100%><td width=60px>File :</td><td><input id=fileshelppopup name=fileshelppopup[] type=file></td></table></fieldset></td>
						<td style=width:50%><fieldset><table border=0 width=100%><td width=60px>Link (url) :</td><td><input id=linkhelppopup style=width:98% class=myinputtext></td></table></fieldset></td>
					</tr>
					<tr>
						<td colspan=2>&nbsp;</td>
					</tr>
					<tr>
						<td align=center colspan=2>
							<button onclick=simpanaddhelppopup('".$param['action']."'); style=width:200px;height:30px class=mybutton name=preview id=preview>Simpan</button>
						</td>
					</tr>
				</table>
			</td></table>
		";
	
	break;
	case'getpathmenu':
		echo getNamaMenu2($param['idmenu']);
	break;
	case'tambahreporthelppopup':
		
		$tentang=$penjelasan="";
		$jenis="5";
		if($param['action']=='edit'){
			$str = "select * from ".$dbname.".owlhelp_ticket where id='".$param['idhelp']."'";
			$res = fetchdata($str);
			foreach($res as $bar){				
				$tentang=htmlentities($bar['subject']);
				$penjelasan=htmlentities($bar['description']);
				$jenis=$bar['category_ticket'];
				$userinput=$bar['userinput'];
				$kodeorginput=$bar['kodeorg'];
			}
		}
		
		// echo"<pre>";
		// print_r($param);
		// $table = 'owlhelp_ticket';
		// $field = 'category_ticket';
		// $query = $owlPDO->query(" SHOW FULL COLUMNS FROM `".$dbname."`.`".$table."` LIKE '".$field."' ");
		// $query->setFetchMode(PDO::FETCH_NUM);
		// $row = $query->fetch();
		// #extract the values
		// $enum_fields = array();
		// $arrfiled = explode(";",$row[8]);
		// foreach($arrfiled as $i) {
			// $e = explode("=>",$i);
			// $enum_fields[trim($e[0])] = trim($e[1]);
		// }
		
		$st0="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:transparent;color:green;font-weight:normal;font-size:10px;border-radius:0.3rem;text-align:center;height: 30px;padding-left:10px;padding-right:10px;padding-top:8px;padding-bottom:1px;float:left;\"";
		
		$st[2]="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#e74c3c;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";
		
		$st[5]=$st[0]="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#f1c40f;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";
		
		$st[1]="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#3498db;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";

		$str = "SELECT * from ".$dbname.".menu where id= '".$param['idmenu']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$action=$bar['action'];
		}
		
		echo"
			<table border=0 style=width:100%><td align=center>
				<table border=0 cellpadding=1 cellspacing=1 style=width:90%>
					<tr>
						<td>Subject :</td><td>Jenis :</td><td style=cursor:pointer;color:blue;float:right;><button style=background-color:transparent;font-weight:bold;color:blue; class=mybutton onclick=getinfohelppopup();>Petunjuk ?</button>&nbsp;&nbsp;</td>
						<input id=idmenutambahhelppopup style=display:none value=".$param['idmenu'].">
						<input id=idmenutambahhelppopupaction style=display:none value=".$action.">
						<input id=idmenutambahhelppopupawal style=display:none value=".$param['idmenu'].">
						<input id=idhelptambahhelppopup style=display:none value=".$param['idhelp'].">
					</tr>
					<tr>
						<td><input placeholder=required class=myinputtext maxlength=400 id=tentanghelppopup onkeypress='return tanpa_kutip(event)' style='padding-left:15px;width:95%;height:30px' value=".$tentang."></td>
						
						<td style=width:60% colspan=2><div style='border: 0.5px solid #000;border-radius:5px;min-height:33px;vertical-align:text-bottom;width:99%'><table><tr><td></td></tr><tr>";
							foreach($enum_fields as $key => $value){
								if($st[$key]==''){
									$st[$key]=$st[1];
								}
								if($jenis==$key){									
									echo"<td style=text-align:center;><input type=radio name=radio[] onclick=showposthelppoup(this); id=jenis".$key." value=".$key." checked><label ".$st[$key].">".$value."</label></td>";
								}else{
									echo"<td style=text-align:center;><input type=radio name=radio[] onclick=showposthelppoup(this); id=jenis".$key." value=".$key."><label ".$st[$key].">".$value."</label></td>";
								}
							}
					
					echo"</tr></table></div></td>
					</tr>";
					
					$optmenu="<option value='".$param['idmenu']."'>".$_SESSION['lang']['pilihdata']."</option>";
					$hidden="style=display:none";$adaselect2='n';
					if($action=='user_tiketreport'){
						if($admin==false){						
							$whr=" and namauser='".$_SESSION['standard']['username']."'";		
						}
						
						$str = "select * from ".$dbname.".menu where 1=1 and (action!='' and action!='null') and hide='0' and id in (select menuid from ".$dbname.".auth where 1=1 ".$whr.") order by parent asc";
						$res = fetchdata($str);
						foreach($res as $bar){				
							$filemenu[$bar['id']]=$bar['action'];
							
							$d=getNamaMenu2($bar['id'],'modul');
							if($d!=$n){			
								$optmenu.="<optgroup label='".$d."'>";
							}
							$optmenu.="<option value='".$bar['id']."'>".$bar['caption']."</option>";
							$n=$d;
							if($d!=$n){			
								$optmenu.="</optgroup>";
							}
						}
						$hidden="";$adaselect2='y';
					}
					
					$optuser="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
					$str = "select * from ".$dbname.".user where 1=1 and status='1' order by namauser asc";
					$res = fetchdata($str);
					foreach($res as $bar){
						if($userinput==$bar['namauser']){
							$optuser.="<option value='".$bar['namauser']."' selected>".getKary($bar['karyawanid'])." (".$bar['namauser'].")</option>";
						}else{							
							$optuser.="<option value='".$bar['namauser']."'>".getKary($bar['karyawanid'])." (".$bar['namauser'].")</option>";
						}
						
						if($bar['namauser']==$_SESSION['standard']['username']){
							$kodeorguser=$bar['kodeorg'];
						}
					}
					
					if($kodeorginput!=''){
						$kodeorguser=$kodeorginput;
					}
					
					foreach(getOrgDetail(11) as $key => $val){
						$d=getNamaOrg($key,'induk');
						if($d!=$n){			
							$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
						}
						if($kodeorguser==$key){							
							$optorg.="<option value=".$key." selected>".$key." - ".$val."</option>";
						}else{
							$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
						}
						$n=$d;
						if($d!=$n){			
							$optorg.="</optgroup>";
						}
					}

					$adaselect2='y';
					$shownm=" style=display:none";
					if($jenis=='5'){
						$shownm="";
					}
					echo"<input hidden id=adaselect2 value=".$adaselect2.">";
					echo"<tr ".$hidden.">
						<td nowrap>Menu :</td>
						<td nowrap>Path Menu :</td>
					</tr>
					<tr ".$hidden.">
						<td nowrap><select class='select2 help' style=float:right;width:98%; onchange=getpathmenu(this.value); >".$optmenu."</select></td>
						<td style=width:60% colspan=2 id=pathmenuhelppopup></td>
					</tr>
					<tr id=bariskraniinput1 ".$shownm.">
						<td>Nama User Input Transaksi :</td>
						<td>Kode Organisasi :</td>
					</tr>
					<tr id=bariskraniinput2 ".$shownm.">
						<td><select id=namauserinputtransaksi class='select2 help' style=width:98%;>".$optuser."</select></td>
						<td><select id=namaorginputtransaksi class='select2 help'>".$optorg."</select></td>
					</tr>
					";
					echo"<tr>
						<td nowrap style=padding-right:8px;>Penjelasan :</td>
						<td colspan=2 nowrap><span ".$st0.">Agar seluruh fitur dapat berjalan dengan baik disarankan menggunakan tampilan full screen, click icon </span>
							<span id='cke_787' class='cke_toolbar' aria-labelledby='cke_787_label' role='toolbar'><span id='cke_787_label' class='cke_voice_label'>Tools</span><span class='cke_toolbar_start'></span><span class='cke_toolgroup' role='presentation'><a id='cke_788' class='cke_button cke_button__maximize cke_button_off' title='Maximize' tabindex='-1' hidefocus='true' role='button' aria-labelledby='cke_788_label' aria-describedby='cke_788_description' aria-haspopup='false' aria-disabled='false'><span class='cke_button_icon cke_button__maximize_icon' style='background-image:url('http://localhost/ksp/ckeditor/plugins/icons.png?t=JB9C');background-position:0 -672px;background-size:auto;'>&nbsp;</span><span id='cke_788_label' class='cke_button_label cke_button__maximize_label' aria-hidden='false'>Maximize</span><span id='cke_788_description' class='cke_button_label' aria-hidden='false'></span></a></span><span class='cke_toolbar_end'></span></span>
						</td>
					</tr>
					<tr>
						<td colspan=3><textarea rows=23 class=ckeditor placeholder=required id=penjelasanhelppopup name=penjelasanhelppopup type='text' onkeypress='return tanpa_kutip(event)' style='width:98%;'>".$penjelasan."</textarea></td>
					</tr>
					<tr>
						<td colspan=3>Attachment :</td>
					</tr>
					<tr>
						<td nowrap colspan=3>File : <input id=fileshelppopup name=fileshelppopup[] type=file multiple></td>
					</tr>
					<tr>
						<td colspan=3>";
						
						if($bar['file']!= null){
							$path="fileupload/owlreport/".$param['idhelp']."/";
							echo"<table class='sortable' cellspacing='1' cellpadding=3 border='0'>
								<thead>
								<tr class=rowheader>
									<th align='center' width=30px>No.</th>
									<th align='center'>Nama File</th>
									<th align='center'>Action</th>
								</tr>
							</thead>
							<tbody>";
							
							$fileT=explode("|",$bar['file']);
							foreach($fileT as $filename){
								if($filename!=''){
									$no++;
									echo"<tr class=rowcontent>";
									echo"<td align=center>".$no."</td>";
									echo"<td>".$filename."</td>";
									echo"<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delfilereporthelppopup('".$param['idmenu']."','".$bar['id']."','".$filename."');\" ></td>";
									echo"</tr>";
								}
							}
							
							echo"</tbody>";
							echo"</table>";
						}
						echo"</td>
					</tr>
					<tr>
						<td colspan=3>&nbsp;</td>
					</tr>
					<tr>
						<td align=center colspan=3>
							<button onclick=simpanreporthelppopup('".$param['action']."'); style=width:200px;height:30px class=mybutton name=preview id=preview>Simpan</button>
						</td>
					</tr>
				</table>
			</td></table>
		";
	
	break;
	case'getinfohelppopup':
		$str="select * from ".$dbname.".owlhelp where modul=''";
		$res = fetchdata($str);
		foreach($res as $bar){
			echo nl2br($bar['penjelasan'])."<br>";
		}
	break;
	case'delhelppopup':
		$str = "select * from ".$dbname.".owlhelp where id='".$idhelp."'";
		$res = fetchdata($str);
		$file = $res[0]['namafile'];
		
		$str="delete from ".$dbname.".owlhelp where id='".$idhelp."'";
		try{$owlPDO->exec($str);
			if(file_exists($file)){
				unlink($file);
			}
		}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
	break;
	case'deldtpopup':
		$str = "select * from ".$dbname.".owlhelp_ticket_dt where iddt>='".$param['iddt']."' and idht='".$param['idht']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['iddt']>$param['iddt']){
				exit("Error : Tidak bisa dihapus.");
			}
			if($bar['iddt']=$param['iddt']){
				$tempfile=$bar['file'];
			}
			$date=$bar['date'];
		}
		
		$selisih = (((strtotime (date('Y-m-d H:i:s')) - strtotime ($date)))/(60));
		if($selisih>1){
			exit("Error : Tidak bisa dihapus (Expired).");
		}
		
		$path="fileupload/owlreport/".$param['idht']."/";
		
		$fileT = explode("|",$tempfile);
		foreach($fileT as $filename){
			if($filename!=''){				
				$file = $path.$filename;
				if(file_exists($file)){
					unlink($file);
				}
			}
		}
		
		$str="delete from ".$dbname.".owlhelp_ticket_dt where iddt='".$param['iddt']."' and idht='".$param['idht']."'";
		try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
	break;
	
	case'delreporthelppopup':
		getOtorisasiHelpPopup($param);
		$path="fileupload/owlreport/".$param['idhelp']."/";
		
		$str = "select * from ".$dbname.".owlhelp_ticket where id='".$param['idhelp']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$tempfile=$bar['file'];
		}
		$filesave="";
		if($tempfile!=null){			
			$fileT = explode("|",$tempfile);
			foreach($fileT as $filename){
				$file = $path.$filename;
				if(file_exists($file)){
					@unlink($file);
				}
			}
		}
		
		// echo $file."<br>";
		// exit("error");
		$str="delete from ".$dbname.".owlhelp_ticket where id='".$param['idhelp']."'";
		try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
		
	break;
	case'delfilereporthelppopup':
		$path="fileupload/owlreport/".$param['id']."/";
	
		
		$str = "select * from ".$dbname.".owlhelp_ticket where id='".$param['id']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$tempfile=$bar['file'];
		}
		$filesave="";
		if($tempfile!=null){			
			$fileT = explode("|",$tempfile);
			foreach($fileT as $filename){
				if($filename==$param['filename']){
					$file = $path.$filename;
					if(file_exists($file)){
						unlink($file);
					}
				}else{
					$filesave.=$filename."|";
				}
			}
			$data = array(
				'file'    => $filesave
			);
			$where = "id='".$param['id']."'";
			$str = updateQuery($dbname,'owlhelp_ticket',$data,$where);
			$owlPDO->exec($str);
		}
		
	break;
	
	case'simpanreporthelppopup':
		try {
		$owlPDO->beginTransaction();
			$persetujuan=0;
			if($jenis!=0){
				//$persetujuan='1';
			}
			
			if($jenis !=2){				
				$str = "select * from ".$dbname.".owlhelp_ticket where info_menu='".$idmenu."' and category_ticket!='2' and persetujuan='1' and status='0' and pic_client='".$_SESSION['standard']['userid']."'";
				$res = fetchdata($str);
				if(count($res)>=2){
					throw new PDOException("Masih ada ticket yang terbuka / aktif untuk menu ini sebanyak <b>".count($res)."</b> ticket, anda tidak diizinkan untuk menambah ticket baru.");
				}
			}
			
			if($userinput=='' and $jenis=='5'){
				throw new PDOException("Nama user pembuat transaksi harus diisi.");
			}
			
			if($action!='edit'){
				
				$str = "select max(id) as id from ".$dbname.".owlhelp_ticket";
				$res = fetchdata($str);
				if($res[0]['id']>0){
					$idhelp=$res[0]['id']+1;
				}else{
					$idhelp=1;
				}
				
				$data = array(
					'id'              => $idhelp,
					'info_menu'       => $idmenu,
					'subject'         => $tentang,
					'description'     => $penjelasan,
					'date'            => date("Y-m-d H:i:s"),
					'username'        => $_SESSION['standard']['username'],
					'persetujuan'     => $persetujuan,
					'userinput'       => $userinput,
					'kodeorg'         => $kodeorg,
					'status'          => '0',
					'category_ticket' => $jenis,
					'pic_client'      => $_SESSION['standard']['userid']
				);
				$str = insertQuery($dbname,'owlhelp_ticket',$data,array_keys($data));
				$owlPDO->exec($str);
			}else{
				$data = array(
					'info_menu'       => $idmenu,
					'subject'         => $tentang,
					'description'     => $penjelasan,
					'date'            => date("Y-m-d H:i:s"),
					'username'        => $_SESSION['standard']['username'],
					'persetujuan'     => $persetujuan,
					'userinput'       => $userinput,
					'kodeorg'         => $kodeorg,
					'status'          => '0',
					'category_ticket' => $jenis,
					'pic_client'      => $_SESSION['standard']['userid']
				);
				
				$where = "id='".$idhelp."'";
				$str = updateQuery($dbname,'owlhelp_ticket',$data,$where);
				$owlPDO->exec($str);
			}
			
			$countfiles = @count($_FILES['file']['name']);
			if($countfiles>5){
				throw new PDOException("Jumlah maksimal hanya 5 file.");
			}
			if($countfiles>0){
				for($i=0;$i < $countfiles;$i++){
					$filesize+=$_FILES['file']['size'][$i];
				}
				if($filesize>250000000){
					throw new PDOException("File size terlalu besar (".formatBytes($filesize).").");
				}
				
				$path="fileupload/owlreport/".$idhelp."/";
				if (!file_exists($path)) {
					mkdir($path, 0777, true);
				}	
				$tempfile="";
				for($i=0;$i < $countfiles;$i++){
					$file_tmpname= file_get_contents($_FILES['file']['tmp_name'][$i]);
					$filename    = $_FILES['file']['name'][$i];
					
					$file_extension = pathinfo($path.$filename, PATHINFO_EXTENSION);
					$file_extension = strtolower($file_extension);
					
					if($_FILES['file']['error'][$i]==0){
						$filename = $idhelp."_".$filename;
						$tempfile.=$filename."|";
						
						if(file_exists($path.$filename)) {
							unlink($path.$filename);
						}
						if(in_array($file_extension,$valid_ext)){
							file_put_contents($path.$filename,$file_tmpname);
						}else{
							throw new PDOException("File tidak diizinkan.");
						}
					}
				}
				
				$data = array(
					'file'    => $tempfile
				);
				$where = "id='".$idhelp."'";
				$str = updateQuery($dbname,'owlhelp_ticket',$data,$where);
				$owlPDO->exec($str);
			}
			
			
			## CREATE NOTIFICATION
			$str = "select * from ".$dbname.".setup_notification_dt where kodejenis='TICKET'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$isi = "Jenis : ".$enum_fields[$jenis]."<br>Dari : ".$_SESSION['standard']['username']."<br> Tentang : ".$tentang;
				$str = "insert into ".$dbname.".list_notification (kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('".$idhelp."','TICKET','".$isi."','".$bar['karyawanid']."','0','0','".date('Y-m-d H:i:s')."')";
				$owlPDO->exec($str);
			}
			
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
		lastUpdateHelpPopup($idhelp);
		//exit("error".$idhelp);
	
	break;
	case'simpanreporthelppopup2':
		try {
		$owlPDO->beginTransaction();
			
			$str = "select max(iddt) as id from ".$dbname.".owlhelp_ticket_dt";
			$res = fetchdata($str);
			if($res[0]['id']>0){
				$iddt=$res[0]['id']+1;
			}else{
				$iddt=1;
			}
			
			$data = array(
				'iddt'            => $iddt,
				'date'            => date("Y-m-d H:i:s"),
				'username'        => $_SESSION['standard']['username'],
				'description'     => $penjelasan,
				'pictindaklajut'  => $tindaklanjut,
				'idht'            => $idhelp
			);
			$str = insertQuery($dbname,'owlhelp_ticket_dt',$data,array_keys($data));
			$owlPDO->exec($str);
			
			
			$countfiles = @count($_FILES['file']['name']);
			if($countfiles>5){
				throw new PDOException("Jumlah maksimal hanya 5 file.");
			}
			if($countfiles>0){
				for($i=0;$i < $countfiles;$i++){
					$filesize+=$_FILES['file']['size'][$i];
				}
				if($filesize>250000000){
					throw new PDOException("File size terlalu besar (".formatBytes($filesize).").");
				}
				
				$path="fileupload/owlreport/".$idhelp."/";
				if (!file_exists($path)) {
					mkdir($path, 0777, true);
				}
				$tempfile="";
				for($i=0;$i < $countfiles;$i++){
					$file_tmpname= file_get_contents($_FILES['file']['tmp_name'][$i]);
					$filename    = $_FILES['file']['name'][$i];
					
					$file_extension = pathinfo($path.$filename, PATHINFO_EXTENSION);
					$file_extension = strtolower($file_extension);
					
					if($_FILES['file']['error'][$i]==0){
						$filename = $idhelp."_".$iddt."_".$filename;
						$tempfile.=$filename."|";
						
						if(file_exists($path.$filename)) {
							unlink($path.$filename);
						}
						if(in_array($file_extension,$valid_ext)){
							file_put_contents($path.$filename,$file_tmpname);
						}else{
							throw new PDOException("File tidak diizinkan.");
						}
					}
				}
				
				
				$data = array(
					'file'    => $tempfile
				);
				$where = "idht='".$idhelp."' and iddt='".$iddt."'";
				$str = updateQuery($dbname,'owlhelp_ticket_dt',$data,$where);
				$owlPDO->exec($str);
				
			}
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
		if($tindaklanjut!=''){
			$str = "select * from ".$dbname.".owlhelp_ticket where 1=1 and id= ".$idhelp."";
			$res = fetchdata($str);
			$info_menu = $res[0]['info_menu'];
			$subject = $res[0]['subject'];
			$description = nl2br($res[0]['description']);
			
			$textx="<html>
				<head>
					<body>
						Dengan Hormat,
						<br>
						<br>
						Ada ticket support yang perlu bapak/ibu tindak lanjuti, sebagai berikut :
						<br>
						<br>
						<b>Nomor :</b> #".$idhelp."<br>
						<b>Path Menu :</b> ".getNamaMenu2($info_menu)."<br>
						<b>Judul :</b> ".$subject."<br>
						<b>Penjelasan :</b> ".$description."<br>
						<b>Balasan dari :</b> ".$_SESSION['standard']['username']." ".date("Y-m-d H:i:s")."<br>
						<i>".$penjelasan."</i><br><br>
						Demikian disampaikan, terima kasih.<br>
						Salam,
						
						<br><br><br>
						<i>Pesan ini dikirim otomatis, untuk membalas silahkan buka https://owl.ksp-agro.com kemudian menu : My Account - Ticket Report atau masuk ke menu : ".getNamaMenu2($info_menu)." kemudian click <b>Help</b> kemudian click <b>Report/Add Ticket</b></i>
					</body>
				</head>
		   </html>
			";
			
			
			$to = getUserEmail($tindaklanjut);
			$subjectx="[Notifikasi]Ticket Support Nomor  #".$idhelp."";
			if(isset($to)){
				$kirim = kirimEmail($to, '', $subjectx, $textx);
			}
			
			$telegram = getTelegram($tindaklanjut);
			
			if($telegram!=''){
				$textx="
					Dengan Hormat,
					\n
					\n
					Ada ticket support yang perlu bapak/ibu tindak lanjuti, sebagai berikut :
					\n
					\n
					<b>Nomor :</b> #".$idhelp."\n
					<b>Path Menu :</b> ".getNamaMenu2($info_menu)."\n
					<b>Judul :</b> ".$subject."\n
					<b>Penjelasan :</b> ".$description."\n
					<b>Balasan dari :</b> ".$_SESSION['standard']['username']." ".date("Y-m-d H:i:s")."\n
					<i>".$penjelasan."</i>\n\n
					Demikian disampaikan, terima kasih.\n
					Salam,
					\n\n\n
					<i>Pesan ini dikirim otomatis, untuk membalas silahkan buka https://owl.ksp-agro.com kemudian menu : My Account - Ticket Report atau masuk ke menu : ".getNamaMenu2($info_menu)." kemudian click <b>Help</b> kemudian click <b>Report/Add Ticket</b></i>";
				
				kirimtelegram($telegram,$textx);
			}
		}
		
		
		lastUpdateHelpPopup($idhelp);
		
		//exit("error".$idhelp);
	break;
	case'simpanaddhelppopup':
		try {
		$owlPDO->beginTransaction();
			
			$str="SELECT f.* FROM (SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)FROM (SELECT @id := ".$idmenu." ) tmp1 JOIN ".$dbname.".menu ON @id <> 0) tmp2 JOIN ".$dbname.".menu f ON tmp2._id = f.Id order by action,parent";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['parent']==0){
					$modul  =$bar['caption'];
					$modulid=$bar['id'];
				}
				$menu[$bar['caption']]=strtoupper(strtolower($bar['caption']));
			}
			
			if($linkurl!=''){				
				if (filter_var($linkurl, FILTER_VALIDATE_URL)){
				} else {
					throw new PDOException('url not valid.');
				}
			}

			if($action=='edit'){
				$id=$idhelp;
				$add="";
				if($linkurl!=''){
					$add="namafile = '".$linkurl."',";
				}
				$str="update ".$dbname.".owlhelp set ".$add." judul='".$tentang."', penjelasan='".$penjelasan."' , updateby='".$_SESSION['standard']['userid']."' where id='".$id."'";
				$owlPDO->exec($str);
			}else{				
				$str = "select max(id) as id from ".$dbname.".owlhelp";
				$res = fetchdata($str);
				if(count($res)==0){
					$id=1;
				}else{
					$id=$res[0]['id']+1;
				}
				
				
				$data = array(
						'id'         => $id,
						'menuid'     => $idmenu,
						'modulid'    => $modulid,
						'modul'      => $modul,
						'bahasa'     => "ID",
						'judul'      => $tentang,
						'penjelasan' => $penjelasan,
						'namafile'   => $linkurl,
						'createdby'  => $_SESSION['standard']['userid'],
						'createdtime'=> date("Y-m-d H:i:s"),
						'updateby'   => $_SESSION['standard']['userid']
				);
				$str = insertQuery($dbname,'owlhelp',$data,array_keys($data));
				$owlPDO->exec($str);
			}
			
			
			$data = $_POST;
			if($data['fileupload']!=''){
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $id.$filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
						
						$str="update ".$dbname.".owlhelp set namafile='".$path.$filename."', updateby='".$_SESSION['standard']['userid']."' where id='".$id."'";
							
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						if(file_exists($path.$filename)) {
							unlink($path.$filename);
						}
						file_put_contents($path.$filename,$file_tmpname);
						
					}else{
						throw new PDOException('Format file tidak diizinkan.');
					}
				}
			}
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
	break;
	case'loaddata':
		$tab="";
        $limit = 20;
        $page = 0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=@($page*$limit);
		$no=@(($page*$limit));
		$colspan=4;
		
		$where="";
		if($sccari!=''){
			$where.=" and modul in (select id from ".$dbname.".menu where id like '%".$sccari."%' or caption like '%".$sccari."%' or caption2 like '%".$sccari."%' or caption3 like '%".$sccari."%') or judul like'%".$sccari."%'";
		}
		

        $str = "select id from ".$dbname.".owlhelp where 1=1 ".$where." group by modul, judul";
		$res=fetchdata($str);
		$jlhbrs = count($res);
				
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$no = 0;
			$optbahasa=makeOption($dbname,'namabahasa','code,name');
			$str = "select * from ".$dbname.".owlhelp where 1=1 ".$where." group by modul, judul order by modul asc, judul asc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				$optmodul=makeOption($dbname,'menu','id,caption',"id='".$val['modul']."'");
				
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$optmodul[$val['modul']]."</td>";
				$tab.="<td>".$val['judul']."</td>";
				
				$tab.="<td>";
				$strx="select * from ".$dbname.".owlhelp where modul='".$val['modul']."' and judul='".$val['judul']."' order by bahasa asc";
				$resx=fetchdata($strx);
				$nox=0;
				foreach($resx as $valx){
					$nox++;
					if($nox==1){
						// $tab.="<a href='help/upload/".$valx['namafile']."' download>".$optbahasa[$valx['bahasa']]."</a>";
						$tab.="<label onclick=\"viewpdf('".$valx['modul']."','".$optbahasa[$val['modul']]."','".$valx['judul']."','".$valx['bahasa']."',event)\" style='color:blue;cursor:pointer' title='Klik untuk menampilkan PDF'>".$optbahasa[$valx['bahasa']]."</label>";
					}else{
						// $tab.="<br><a href='help/upload/".$valx['namafile']."' download>".$optbahasa[$valx['bahasa']]."</a>";
						$tab.="<br><label onclick=\"viewpdf('".$valx['modul']."','".$optbahasa[$val['modul']]."','".$valx['judul']."','".$valx['bahasa']."',event)\" style='color:blue;cursor:pointer' title='Klik untuk menampilkan PDF'>".$optbahasa[$valx['bahasa']]."</label>";
					}
				}
				$tab.="</td>";
				$tab.="</tr>";
			}
			
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loadData','getPage');
			$tab.="</table>";
		}
		
		echo $tab;
	break;
	
	case'viewpdf':
		$modul = checkPostGet('modul','');
		$judul = checkPostGet('judul','');
		$bahasa = checkPostGet('bahasa','');
		
		$str="select * from ".$dbname.".owlhelp where modul='".$modul."' and judul='".$judul."' and bahasa='".$bahasa."'";
		$res=fetchdata($str);
		
		echo "<embed src='help/upload/".$res[0]['namafile']."' width='800px' height='450px' />";
	break;
}

function formatBytes($size, $precision = 2) {
    $base 		= log($size, 1024);
    $suffixes 	= array('B', 'KB', 'MB', 'GB', 'TB');   

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}

function getNamaMenu2($idmenu,$tipe=''){
	include('lib/zLib.php');
	global $dbname;
	global $owlPDO;
	
	$menu=[];
	$str="SELECT f.* FROM (SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)FROM (SELECT @id := ".$idmenu." ) tmp1 JOIN ".$dbname.".menu ON @id <> 0) tmp2 JOIN ".$dbname.".menu f ON tmp2._id = f.Id order by action,parent";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['parent']==0){
			$modul  =$bar['caption'];
			$modulid=$bar['id'];
		}
		$menu[$bar['caption']]=strtoupper(strtolower($bar['caption']));
	}
	if($tipe=='modul'){
		return $modul;
	}else{		
		return implode(" - ",$menu);
	}
}

function lastUpdateHelpPopup($idhelp){
	include('lib/zLib.php');
	global $dbname;
	global $owlPDO;
	
	$data = array(
		'lastupdateby'  => $_SESSION['standard']['userid'],
		'lastupdate'  => date("Y-m-d H:i:s")
	);
	$where = "id='".$idhelp."'";
	$str = updateQuery($dbname,'owlhelp_ticket',$data,$where);
	try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
}

function getOtorisasiHelpPopup($param){
	global $param;
	include('lib/zLib.php');
	global $dbname;
	global $owlPDO;
	
	$admin=false;
	$listticket=false;
	$query = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
	$res = fetchdata($query);
	if(!empty($res)){			
		$admin=true;
		$listticket=true;
	}
	
	$str = "select * from ".$dbname.".owlhelp_ticket where id='".$param['idhelp']."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['username']==$_SESSION['standard']['username']){
			$listticket=true;
		}
	}
	$str = "select * from ".$dbname.".owlhelp_ticket_dt where idht='".$param['idhelp']."' order by iddt asc";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['pictindaklajut']==$_SESSION['standard']['userid']){
			$listticket=true;
		}
		if($bar['username']==$_SESSION['standard']['username']){
			$listticket=true;
		}
	}	
	
	if($listticket==true){			
		return true;
	}else{
		return exit("Warning: Anda tidak memiliki otorisasi.");
	}
}

function tambahmenitshift($tgl,$jlhmenit){
	$date = date_create($tgl);
	date_add($date, date_interval_create_from_date_string($jlhmenit.' minutes'));
	return date_format($date, 'Y-m-d H:i:s');
}

function getNamaMenu3($idmenu){
	include('lib/zLib.php');
	global $dbname;
	global $owlPDO;
	
	$menu='';
	$str="SELECT * from ".$dbname.".menu where id= '".$idmenu."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$menu=strtoupper(strtolower($bar['caption']));
	}
	return $menu;
}
?>