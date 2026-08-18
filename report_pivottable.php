<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$method= checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param= $_GET;}

$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmtt  = makeOption($dbname,'setup_blok','kodeorg,tahuntanam');


$datasort= array();

$path  = "imgbot/";
$fname = "Laporan_Pivot_Table_".$_SESSION['standard']['userid'].".pdf"; // name the file
		
switch($method){
	case'simpanassign':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'id'        => $param['id'],
					'karyawanid'=> $param['namauser']
				);
				$cols = array();
				foreach($data as $key=>$row) {
					$cols[] = $key;
				}
				$query = insertQuery($dbname,'pivot_favoritdt',$data,$cols);#exit("error".$query);
				$owlPDO->exec($query);
				
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	case'setformuser':
		$path   = $_SERVER['HTTP_REFERER'];
		$path   = explode('/',$path);
		$rowfile= count($path)-1;
		$file   = $path[$rowfile];
		$file   = str_replace(".php","",$file);
		$idmenu = makeOption($dbname,'menu','action,id');
		
		$iduser = makeOption($dbname,'user','namauser,karyawanid');
		
		
		$optuser="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".auth where menuid='".$idmenu[$file]."'";
		$res = fetchData($str);
		foreach($res as $val){
			$optuser.="<option value=".$iduser[$val['namauser']].">".$val['namauser']."</option>";			
		}

		$tab="<table>";
		$tab.="<tr>
					<td>User Name<td>
					<td>:<input hidden id=idassign value=".$param['id']."><input hidden id=tipeassign value=".$param['tipe']."><td>
					<td><select class='select2' id=namauser style=\"width:200px;\">".$optuser."</select><td>
					<td><button onclick=simpanassign(); class=mybutton>Save</button><td>
				</tr>";
		$tab.="</table>";
		$tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable style=min-width:370px>
			<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>User Name</td>
				<td align=center>Dibuat Oleh</td>
				<td align=center>Action</td>";
		$tab.="</tr>
			</thead>
			<tbody>";
		$str = "select b.jenis, a.karyawanid as user, b.karyawanid as pembuat from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where b.id='".$param['id']."' order by b.id asc";
		$res = fetchdata($str);
		if(count($res)==0){
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=4>".$_SESSION['lang']['datanotfound']."</td>";
			$tab.="</tr>";
		}else{			
			foreach ($res as $bar){
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".getNamaKaryawan($bar['user'])."</td>";
				$tab.="<td align=left>".getNamaKaryawan($bar['pembuat'])."</td>";
				$tab.="<td align=center width=25px colspan=2><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete' onclick=deletefield('".$param['id']."','dt','".$bar['user']."','".$bar['jenis']."')></td>";
				$tab.="</tr>";
			}
			
			$tab.="</tr>";
		}
		
		$tab.="
			</tbody>
		</table>";
	echo $tab; 
	break;
	case'deletefield':
		try{
		$owlPDO->beginTransaction();
			if($param['table']=='ht'){
				$table="pivot_favorit";
			}else{
				$table="pivot_favoritdt";
				if($param['assignto']!=''){
					$where=" and karyawanid='".$param['assignto']."'";
				}else{					
					$where=" and karyawanid='".$_SESSION['standard']['userid']."'";
				}
			}
			
			$str = "delete from ".$dbname.".".$table." where id='".$param['id']."' ".$where.""; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'upload':
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		if(!empty($_POST['data'])){
			$data = $_POST['data'];
			if (file_exists($path.$fname)){
				unlink($path.$fname);
			}
			$file = fopen($path.$fname, 'w'); // open the file path
			fwrite($file, $data); //save data
			fclose($file);
		} else {
			echo "No Data Sent";
		}
	break;
	case'kirimkan':
		$filepdf=$path.$fname;
		
		switch($param['sumber']){
			case'telegram':
				if (!file_exists($filepdf)) {
					exit("Warning : Data tidak ditemukan.");
				}else{
					$message_text="Dear Bapak/Ibu,\n\nTerlampir kami kirimkan data : \n\nLaporan : <b>".$param['namalaporan']."</b>.\n\nLaporan juga dapat diakses melalui :\nurl : <b>owl.ksp-agro.com</b>\nMenu : <b>".ucwords(strtolower($param['namamenu']))."</b>\n\nDemikian disampaikan, terima kasih.\n\nSalam,\n".$_SESSION['empl']['name']."\n".$_SESSION['standard']['username']."";
					
					foreach($param['email'] as $key => $telegram_id){
						send_reply($telegram_id, $message_text);
						sendDocument($telegram_id,$filepdf);
					}
					//unlink($filepdf);
				}
			break;
			case'email':
				if (!file_exists($filepdf)) {
					exit("Warning : Data tidak ditemukan.");
				}
				
				
				### CREATE ZIP
				// $zip = new ZipArchive;
				// if ($zip->open("imgbot/Report.zip", ZipArchive::CREATE) === TRUE){
					// $zip->addFile($filepdf,$fname);
					// $zip->close();
				// }
				
				foreach($param['email'] as $key => $email){
					$subject=$param['namalaporan'];
					$body="Dear Bapak / Ibu,
						<br>
						<br>
						Terlampir kami kirimkan data :<br><br>
						Laporan : <b>".$param['namalaporan']."</b><br><br>
						
						Laporan juga dapat diakses melalui :<br>
						url : <b>owl.ksp-agro.com</b><br>
						Menu : <b>".ucwords(strtolower($param['namamenu']))."</b><br><br>
						
						Demikian disampaikan, terima kasih.<br><br>
						Salam,<br>".$_SESSION['empl']['name']."
						<br>".$_SESSION['standard']['username']."
						";
					
					kirimEmailatt($email,$cc="",$subject,$body,$mailType='text/html',$filepdf);
				}
				//unlink($filepdf);
			break;
		}
	break;
	case'listcari':
		$where="";
		if($param['sumber']=='telegram'){
			$where.=" and karyawanid in (select karyawanid from ".$dbname.".user where telegramid!='' and telegramstatus='1' and status='1')";
		}
		if($param['sumber']=='email'){
			$where.=" and karyawanid in (select karyawanid from ".$dbname.".user where status='1')";
			$where.=" and email like '%@%'";
		}
		if($param['nama']!=''){
			$where.=" and namakaryawan like '%".$param['nama']."%'";
		}
		if($param['lokasi']!=''){
			$where.=" and lokasitugas like '%".$param['lokasi']."%'";
		}
		if($param['jabatan']!=''){
			$where.=" and kodejabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan where namajabatan like '%".$param['jabatan']."%')";
		}
		$nmjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$tab="
			<table id=mytable border=0 cellpadding=5 cellspacing=1 class=sortable width=100%>
				<thead>
					<tr class=rowheader style=text-align:center;height:25px>
						<th>No</th>
						<th>" . $_SESSION['lang']['nama'] . "</th>
						<th>" . $_SESSION['lang']['user'] . "</th>
						<th>" . $_SESSION['lang']['jabatan'] . "</th>
						<th>" . $_SESSION['lang']['lokasitugas'] . "</th>
						<th>" . $param['sumber']. "</th>";
					if($param['sumber']=='telegram'){
						$tab.="<th style='align:center'>User Telegram</th>";
					}	
					$tab.="<th  style='width:30px;align:center'>Action<br>
						<input id=checkall type=checkbox onclick=clickall()>
						</th>
					</tr>
				</thead>
				<tbody>";
				$str="select * from ".$dbname.".datakaryawan where 1=1 ".$where."";
				$res=fetchdata($str);
				foreach($res as $bar){
					$idtel     = makeOption($dbname, 'user', 'karyawanid,telegramid',"karyawanid='".$bar['karyawanid']."'");
					$unametel  = makeOption($dbname, 'user', 'karyawanid,telegramuser',"karyawanid='".$bar['karyawanid']."'");
					$first_name= makeOption($dbname, 'user', 'karyawanid,first_name',"karyawanid='".$bar['karyawanid']."'");
					$last_name = makeOption($dbname, 'user', 'karyawanid,last_name',"karyawanid='".$bar['karyawanid']."'");
					$uname     = makeOption($dbname, 'user', 'karyawanid,namauser',"karyawanid='".$bar['karyawanid']."'");
					if($param['sumber']=='telegram'){
						$bar['email']=$idtel[$bar['karyawanid']];
					}
					if($unametel[$bar['karyawanid']]!=''){
						$utel=$unametel[$bar['karyawanid']];
					}else{
						$utel=$first_name[$bar['karyawanid']]." ".$last_name[$bar['karyawanid']];
					}
					
					$no++;
					$tab.="<tr class=rowcontent style=height:25px>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$bar['namakaryawan']."</td>";
					$tab.="<td>".$uname[$bar['karyawanid']]."</td>";
					$tab.="<td>".$nmjab[$bar['kodejabatan']]."</td>";
					$tab.="<td>".$bar['lokasitugas']."</td>";
					$tab.="<td name=mail[]>".trim($bar['email'])."</td>";
					if($param['sumber']=='telegram'){
						$tab.="<td>".$utel."</td>";
					}
					$tab.="<td align=center><input name=check[] type=checkbox></td>";
					$tab.="</tr>";
				}
				
			$tab.="</tbody>
			</table>
			";
		echo $tab;
	break;
	case'popupkirim':
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	
		$optloc="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select distinct lokasitugas from ".$dbname.".datakaryawan order by lokasitugas asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optloc.="<option value=" . $bar['lokasitugas'] . ">" . $bar['lokasitugas'] . " - ".$nmorg[$bar['lokasitugas']]."</option>";
		}
		if($param['sumber']=='telegram'){
			$btn="<button class=mybutton onclick=infodaftar()>Cara Daftar Telegram</button>";
		}
		$tab="
			<input hidden id=namalaporan value=\"".$param['namalaporan']."\">
			<input hidden id=sumber value=".$param['sumber'].">
			<fieldset style=display:none><legend>Find</legend>
			<table cellspacing=1 border=0>
				<tr class=rowcontent>
					<td>" . $_SESSION['lang']['nama'] . "</td>
					<td>:</td>
					<td><input id=nama onkeyup=listcari(); class=myinputtext></td>
					
					<td>" . $_SESSION['lang']['lokasitugas'] . "</td>
					<td>:</td>
					<td><select style=width:150px onchange=listcari(); id=lokasi>".$optloc."</select></td>
					
					<td>" . $_SESSION['lang']['jabatan'] . "</td>
					<td>:</td>
					<td><input id=jabatan onkeyup=listcari(); class=myinputtext></td>
				</tr>
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=listcari()>Preview</button>".$btn."</td>
				</tr>
				
			</table>
			</fieldset>
			<div style=clear:both></div>
				<div id=listcari><script>listcari()</script></div>
			<table style=align:center;width:100%>
				<td align=center><button class=mybutton id=tombolkirimkan onclick=kirimkan()>Kirim ke ".$param['sumber']."</button></td>
			</table>
			";
		echo $tab;
	break;
}



function sendDocument($telegram_id,$img_dir){
	$idbot = "@owlksp_robot";
	#$token = "1348581495:AAEsK4yzkWGxNvcmIuMxwZYoFdLHOtSOsMw";
	$token = "1348581495:AAHD9WS9wQw0tyMq0-OdGzyJCNAT6KsAdyQ";		
	
	$post_fields = array(
		'chat_id' => $telegram_id,
		'document'=> new CURLFile(realpath($img_dir))
	);
	
	if (!$ch = curl_init()){exit;}
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type:multipart/form-data"
	));
	curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . $token . "/sendDocument"); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($ch);
	curl_close ($ch);
}

function send_reply($telegram_id, $message_text){
	$idbot = "@owlksp_robot";
	#$token = "1348581495:AAEsK4yzkWGxNvcmIuMxwZYoFdLHOtSOsMw";	
	$token = "1348581495:AAHD9WS9wQw0tyMq0-OdGzyJCNAT6KsAdyQ";		
	
	$data = array(
		'chat_id' => $telegram_id,
		'text'  => $message_text,
		'parse_mode'  => "html"
	);
	
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		),
	);
	
	$context  = stream_context_create($options);
	$kirim = "https://api.telegram.org/bot" . $token . "/sendMessage";
	$result = file_get_contents($kirim, false, $context);
}
?>
