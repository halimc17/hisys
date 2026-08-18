<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$method= checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param= $_GET;}

$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$datasort= array();
		
switch($method){
	case'kirimkan':
	
	// echo"<pre>";
	// print_r($param['kepada']);
	// echo"</pre>";
	// exit("error");
	// echo count($_FILES['file']['name']);
	try {
	$owlPDO->beginTransaction();
		$tujuan = explode(",",$param['kepada']);
		$message_text="<i>Subject : </i>\n<b>".$param['subject']."</b>\n\n";
		$message_text.="<i>Message : </i>\n".repEnter($param['message'])."\n";
		#$message_text.="\n<i>Salam,</i>\n".$_SESSION['empl']['name']."\n".$_SESSION['standard']['username']."";
		
		if(isset($_SERVER["REMOTE_ADDR"]) ) {
			$ip = $_SERVER["REMOTE_ADDR"];
		}else if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) {
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}else if(isset($_SERVER["HTTP_CLIENT_IP"]) ) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		
		$uname = makeOption($dbname,'user','telegramid,namauser',"telegramid!=''");
		$karid = makeOption($dbname,'user','telegramid,karyawanid',"telegramid!=''");
		
		foreach($tujuan as $telegram_id){
			send_reply($telegram_id, $message_text);
			
			$str = "insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
			values('".$uname[$telegram_id]."','REG','".$telegram_id."','".$_SERVER['PHP_SELF']."','".$karid[$telegram_id]."','private','/SENDMSG','','".$message_text."','".$ip."')";
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
			if($filesize>2500000){
				throw new PDOException("File size terlalu besar (".formatBytes($filesize).").");
			}
			
			$path="imgbot/temp/";
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}		
			for($i=0;$i < $countfiles;$i++){
				$file_tmpname= file_get_contents($_FILES['file']['tmp_name'][$i]);
				$filename    = $_FILES['file']['name'][$i];
				
				$file_extension = pathinfo($path.$filename, PATHINFO_EXTENSION);
				$file_extension = strtolower($file_extension);
				
				$valid_ext = array("pdf","doc","docx","jpg","png","jpeg","xls","xlsx","zip","rar");

				if(in_array($file_extension,$valid_ext)){
					file_put_contents($path.$filename,$file_tmpname);
				}else{
					throw new PDOException("File tidak diizinkan.");
				}
				foreach($tujuan as $telegram_id){
					sendDocument($telegram_id,$path.$filename);
					//unlink($path.$filename);
					
					$str = "insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
					values('".$uname[$telegram_id]."','REG','".$telegram_id."','".$_SERVER['PHP_SELF']."','".$karid[$telegram_id]."','private','/SENDFILE','','".$filename."','".$ip."')";
					$owlPDO->exec($str);
				}
			}
		}
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;
	case'listcari':
		$where="";
		$where.=" and karyawanid in (select karyawanid from ".$dbname.".user where telegramid!='' and telegramstatus='1' and status='1')";
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
			<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>
				<thead>
					<tr class=rowheader style=text-align:center;height:25px>
						<th>No</th>
						<th>" . $_SESSION['lang']['nama'] . "</th>
						<th>" . $_SESSION['lang']['user'] . "</th>
						<th>" . $_SESSION['lang']['jabatan'] . "</th>
						<th>" . $_SESSION['lang']['lokasitugas'] . "</th>
						<th>TelegramID</th>";
					$tab.="<th style='align:center'>User Telegram</th>";
					$tab.="<th  style='width:30px;align:center'>Action<br>
						<input id=checkall type=checkbox onclick=clickall()>
						</th>
					</tr>
				</thead>
				<tbody>";
				
				$kep = explode(",",$param['kepada']);
				foreach($kep as $kpd){
					$kpda[$kpd]=$kpd;
				}
				
				$str="select * from ".$dbname.".datakaryawan where 1=1 ".$where."";
				$res=fetchdata($str);
				foreach($res as $bar){
					$idtel     = makeOption($dbname, 'user', 'karyawanid,telegramid',"karyawanid='".$bar['karyawanid']."'");
					$unametel  = makeOption($dbname, 'user', 'karyawanid,telegramuser',"karyawanid='".$bar['karyawanid']."'");
					$first_name= makeOption($dbname, 'user', 'karyawanid,first_name',"karyawanid='".$bar['karyawanid']."'");
					$last_name = makeOption($dbname, 'user', 'karyawanid,last_name',"karyawanid='".$bar['karyawanid']."'");
					$uname     = makeOption($dbname, 'user', 'karyawanid,namauser',"karyawanid='".$bar['karyawanid']."'");
					$bar['email']=$idtel[$bar['karyawanid']];
					
					if($unametel[$bar['karyawanid']]!=''){
						$utel=$unametel[$bar['karyawanid']];
					}else{
						$utel=$first_name[$bar['karyawanid']]." ".$last_name[$bar['karyawanid']];
					}
					
					$no++;
					$tab.="<tr class=rowcontent style=height:25px>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td name=nama[]>".$bar['namakaryawan']."</td>";
					$tab.="<td>".$uname[$bar['karyawanid']]."</td>";
					$tab.="<td>".$nmjab[$bar['kodejabatan']]."</td>";
					$tab.="<td>".$bar['lokasitugas']."</td>";
					$tab.="<td name=mail[]>".trim($bar['email'])."</td>";
					$tab.="<td>".$utel."</td>";
					if($kpda[trim($bar['email'])]!=''){
						$tab.="<td align=center><input name=check[] type=checkbox checked></td>";
					}else{							
						$tab.="<td align=center><input name=check[] type=checkbox></td>";
					}					
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
			<fieldset><legend>Find</legend>
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
			<fieldset><legend>List</legend>
				<div id=listcari style=overflow:auto;></div>
			</fieldset>
			<table style=align:center;width:100%>
				<td align=right><button style=width:50px class=mybutton onclick=adddata()>Add</button></td>
			</table>
			";
		echo $tab;
	break;
}



function sendDocument($telegram_id,$img_dir){
	$idbot = "@owlksp_robot";
	//$token = "1348581495:AAEsK4yzkWGxNvcmIuMxwZYoFdLHOtSOsMw";
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

function sendtele($telegram_id,$message_text){
	$idbot = "@owlksp_robot";
	//$token = "1348581495:AAEsK4yzkWGxNvcmIuMxwZYoFdLHOtSOsMw";
	$token = "1348581495:AAHD9WS9wQw0tyMq0-OdGzyJCNAT6KsAdyQ";	
	
	$url = "https://api.telegram.org/bot".$token."/sendMessage?&chat_id=".$telegram_id;
	$url = $url."&text=".urlencode($message_text);
	$url = $url."&parse_mode=html";
	#$url = $url."&reply_to_message_id=".$msgid."";
	$url;
	$ch = curl_init();
	$optArray = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true
	);
	curl_setopt_array($ch, $optArray);
	$result = curl_exec($ch);
	curl_close($ch);
} 

function send_reply($telegram_id, $message_text){
	$idbot = "@owlksp_robot";
	//$token = "1348581495:AAEsK4yzkWGxNvcmIuMxwZYoFdLHOtSOsMw";	
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

function formatBytes($size, $precision = 2) {
    $base 		= log($size, 1024);
    $suffixes 	= array('B', 'KB', 'MB', 'GB', 'TB');   

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}

function repEnter($a, $x="\n"){
	$a = nl2br($a);
	$i = explode('<br />',$a);
	$no ='0'; $t='';
	foreach($i as $r => $e){
		$no+=1;
		if($no < count($i)){
			$t.=trim($e).$x;
		}else{
			$t.=trim($e);
		}
	}
	return $t;
}	

?>
