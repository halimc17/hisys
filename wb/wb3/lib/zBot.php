<?php

function sendApiHideKeyboard($chatid, $text){
    $method = 'sendMessage';
    $data = [
        'chat_id'       => $chatid,
        'text'          => $text,
        'parse_mode'    => 'html',
        'reply_markup'  => json_encode(['hide_keyboard' => true]),

    ];

    $result = apiRequest($method, $data);
}

function sendApiSticker($chatid, $sticker, $msg_reply_id = false){
    $method = 'sendSticker';
    $data = [
        'chat_id'  => $chatid,
        'sticker'  => $sticker,
    ];

    if ($msg_reply_id) {
        $data['reply_to_message_id'] = $msg_reply_id;
    }

    $result = apiRequest($method, $data);
}



function request_url($method){
	global $token;
	return "https://api.telegram.org/bot" . $token . "/". $method;
}

function apiRequest($method, $data){
    if (!is_string($method)) {
        error_log("Nama method harus bertipe string!\n");
        return false;
    }

    if (!$data) {
        $data = [];
    } elseif (!is_array($data)) {
        error_log("Data harus bertipe array\n");
        return false;
    }
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents(request_url($method), false, $context);

    return $result;
}

function getApiUpdate($offset){
    $method = 'getUpdates';
    $data['offset'] = $offset;

    $result = apiRequest($method, $data);

    $result = json_decode($result, true);
    if ($result['ok'] == 1) {
        return $result['result'];
    }

    return [];
}

function sendApiAction($chatid, $action = 'typing'){
    $method = 'sendChatAction';
    $data = [
        'chat_id' => $chatid,
        'action'  => $action,

    ];
    $result = apiRequest($method, $data);
}
function sendApiKeyboard($chatid, $text, $keyboard = [], $inline = false){
    $method = 'sendMessage';
    $replyMarkup = [
        'keyboard'        => $keyboard,
        'resize_keyboard' => true,
    ];

    $data = [
        'chat_id'    => $chatid,
        'text'       => $text,
        'parse_mode' => 'Markdown',

    ];

    $inline
    ? $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard])
    : $data['reply_markup'] = json_encode($replyMarkup);

    $result = apiRequest($method, $data);
}

function editMessageText($telegram_id, $msgid, $message_text, $inline_button = [], $inline = false){
    $method = 'editMessageText';
	$keyboard=array("inline_keyboard"=>$inline_button);
	$data = array(
		'chat_id'     => $telegram_id,
		'message_id'  => $msgid,
		'text'        => $message_text,
		'parse_mode'  => "html",
		'reply_markup'=> json_encode($keyboard)
	);
    $result = apiRequest($method, $data);
}
function force_reply($telegram_id, $msgid, $message_text, $force_reply=array()){
	global $debug;
	global $param;
	global $server;
	
	if(strlen($message_text)>'4000'){
		$message_text=substr($message_text,0,4000);
		$message_text.="\n\n<i>text terlalu panjang dan tidak bisa ditampilkan seluruhnya...</i>\n";
	}
	
	if($param[0]!='/START' and $param[0]!='/MENU'){
		if($server!='' or $server==true){			
			$message_text.="\n<i>Server : ".$_SERVER['SERVER_NAME']."</i>";
		}
	}
	
	if($msgid==false){
		$keyboard=array("force_reply"=>$force_reply);
		$data = array(
			'chat_id' => $telegram_id,
			'text'  => $message_text,
			'parse_mode'  => "html",
			'reply_markup' => json_encode($force_reply)
		);
	}else{		
		$keyboard=array("force_reply"=>$force_reply);
		$data = array(
			'chat_id' => $telegram_id,
			'text'  => $message_text,
			'parse_mode'  => "html",
			'reply_markup' => json_encode($force_reply),
			'reply_to_message_id' => $msgid
		);
	}
	
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		),
	);
	
	$context  = stream_context_create($options); 
	$result = file_get_contents(request_url('sendMessage'), false, $context);
	
	if ($debug) 
		print_r($result);
}

# fungsi kirim ver 1
function sendtele($telegram_id,$msgid,$message_text){
	global $token;
	
	$url = "https://api.telegram.org/bot".$token."/sendMessage?&chat_id=".$telegram_id;
	$url = $url."&text=".urlencode($message_text);
	$url = $url."&parse_mode=html";
	$url = $url."&reply_to_message_id=".$msgid."";
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


# fungsi kirim ver 2
function send_reply($telegram_id, $msgid, $message_text,$inline_button=array(), $keybrd=array()){
	global $debug;
	global $param;
	global $server;
	
	if(strlen($message_text)>'4000'){
		$message_text=substr($message_text,0,4000);
		$message_text.="\n\n<i>text terlalu panjang dan tidak bisa ditampilkan seluruhnya...</i>\n";
	}
	
	if($param[0]!='/START' and $param[0]!='/MENU'){
		if($server!='' or $server==true){			
			$message_text.="\n<i>Server : ".$_SERVER['SERVER_NAME']."</i>";
		}
	}
	
	if($msgid==false){
		if(count($inline_button)>0){
			$keyboard=array("inline_keyboard"=>$inline_button);
			$data = array(
				'chat_id' => $telegram_id,
				'text'  => $message_text,
				'parse_mode'  => "html",
				'reply_markup' => json_encode($keyboard)
			);
		}elseif(count($keybrd)>0){
			$keyboard=array("keyboard"=>$keyboardMarkup);
			$data = array(
				'chat_id' => $telegram_id,
				'text'  => $message_text,
				'parse_mode'  => "html",
				'reply_markup' => json_encode($keybrd)
			);
		}else{		
			$data = array(
				'chat_id' => $telegram_id,
				'text'  => $message_text,
				'parse_mode'  => "html"
			);
		}
	}else{		
		if(count($inline_button)>0){
			$keyboard=array("inline_keyboard"=>$inline_button);
			$data = array(
				'chat_id' => $telegram_id,
				'text'  => $message_text,
				'parse_mode'  => "html",
				'reply_markup' => json_encode($keyboard),
				'reply_to_message_id' => $msgid
			);
		}elseif(count($keybrd)>0){
			$keyboard=array("keyboard"=>$keyboardMarkup);
			$data = array(
				'chat_id' => $telegram_id,
				'text'  => $message_text,
				'parse_mode'  => "html",
				'reply_markup' => json_encode($keybrd)
			);
		}else{		
			$data = array(
				'chat_id' => $telegram_id,
				'text'  => $message_text,
				'parse_mode'  => "html",
				'reply_to_message_id' => $msgid
			);
		}
	}
	
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		),
	);
	
	$context  = stream_context_create($options); 
	$result = file_get_contents(request_url('sendMessage'), false, $context);
	
	if ($debug) 
		print_r($result);
}
#kirim pesan v3
function sendApiMsg($chatid, $msg_reply_id = false, $text, $parse_mode = "html", $disablepreview = false){
    $method = 'sendMessage';
    $data = ['chat_id' => $chatid, 'text'  => $text];

    if ($msg_reply_id) {
        $data['reply_to_message_id'] = $msg_reply_id;
    }
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    if ($disablepreview) {
        $data['disable_web_page_preview'] = $disablepreview;
    }

    $result = apiRequest($method, $data);
}

function sendphoto($telegram_id,$img_dir){
	#$img_dir="imgbot/output.png";
	$post_fields = array(
		'chat_id'   => $telegram_id,
		'photo'     => new CURLFile(realpath($img_dir))
	);
	
	if (!$ch = curl_init()){exit;}
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type:multipart/form-data"
	));
	curl_setopt($ch, CURLOPT_URL, request_url('sendPhoto')); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($ch);
	curl_close ($ch);
}
function sendDocument($telegram_id,$img_dir){
	#$img_dir="imgbot/output.png";
	$post_fields = array(
		'chat_id'   => $telegram_id,
		'document'     => new CURLFile(realpath($img_dir))
	);
	
	if (!$ch = curl_init()){exit;}
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type:multipart/form-data"
	));
	curl_setopt($ch, CURLOPT_URL, request_url('sendDocument')); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($ch);
	curl_close ($ch);
}

function delpesan($telegram_id, $msgid){
	global $debug;
	
	$data = array(
		'chat_id' => $telegram_id,
		'message_id' => $msgid
	);
	
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		),
	);
	
	if (!$curld = curl_init()){exit;}
    curl_setopt($curld, CURLOPT_POST, true);
    curl_setopt($curld, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curld, CURLOPT_URL,request_url('deleteMessage'));
    curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curld);
    curl_close ($curld);
	
	
	// $context  = stream_context_create($options); 
	// $result = file_get_contents(request_url('deleteMessage'), false, $context);
	
	if ($debug) 
		print_r($result);
}

function telActivity($val,$text_ori,$message_text,$inline_button){
	global $dbname;
	global $owlPDO;
	global $val;
	global $text_ori;
	global $message_text;
	global $inline_button;
	
	
	$user = getDetailUser($val,$update);
	$idpengirim=$user['idpengirim'];

	$e = explode(" ",$val['message']['text']);
	$idcr=strpos($e[0],"@");
	if($idcr>0){	
		$e[0] = substr($e[0],0,$idcr);
	}
		
	$tgllalu = date('Y-m-d', strtotime('-45 days', strtotime(date("Y-m-d"))));
	
	if(isset($_SERVER["REMOTE_ADDR"]) ) {
        $ip = $_SERVER["REMOTE_ADDR"];
    }else if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }else if(isset($_SERVER["HTTP_CLIENT_IP"]) ) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
	$inline="";
	if(count($inline_button)>0){
		$inline="\ninline_button = ".count($inline_button);
	}
	
	if($user['userowl']!=''){		
		$post = listperintah();
		foreach($post as $key => $bar){
			if((strtoupper($bar['id'])==strtoupper($e[0])) or (strtoupper($e[0])=='REG' or strtoupper($e[0])=='UNREG')){
				if(strtoupper($e[0])=='REG' or strtoupper($e[0])=='UNREG'){
					$ft="";
				}else{
					$ft=$val['message']['text'];
				}
				
				$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,ip,karyawanid,type,text,full_text,msgid,respond,json)
				values('".$user['userowl']."','REG','".$idpengirim."','".$_SERVER['PHP_SELF']."','".$ip."','".$user['karidowl']."','".$val['message']['chat']['type']."','".strtoupper($e[0])."','".$ft."','".$val['message']['message_id']."','".$message_text.$inline."','".json_encode($text_ori)."')";
				try{
					$owlPDO->exec($str);
					$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid='".$idpengirim."'";
					try{$owlPDO->exec($query);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
	}else{
		$telegram_uname= $val['message']['chat']['username'];
		if($val['message']['chat']['type']!='private'){
			#kalau di group id nya pakai siapa yg kirim pesan
			if(!@$update["message"]){
				$first_name = $update['callback_query']['from']['first_name'];				
				$last_name = $update['callback_query']['from']['last_name'];				
				$username = $update['callback_query']['from']['username'];
			}else{			
				$first_name = $val['message']['from']['first_name'];				
				$last_name = $val['message']['from']['last_name'];				
				$username = $val['message']['from']['username'];
			}
			#$idpengirim=$val['message']['from']['id'];
		}else{
			$first_name = $val['message']['chat']['first_name'];				
			$last_name = $val['message']['chat']['last_name'];				
			$username = $val['message']['chat']['username'];
		}
		
		if($telegram_uname==''){
			if($username!=''){				
				$telegram_uname=$username;
			}else{
				$telegram_uname=$first_name." ".$last_name;
			}
		}		
		
		if(strtoupper($e[0])=='REG' or strtoupper($e[0])=='UNREG'){
			$ft="";
		}else{
			$ft=$text_ori['message']['text'];
		}
		$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,ip,karyawanid,type,text,full_text,msgid,respond, json)
		values('".$telegram_uname."','UNREG','".$idpengirim."','".$_SERVER['PHP_SELF']."','".$ip."','','".$val['message']['chat']['type']."','','".$ft."','".$val['message']['message_id']."','".$message_text.$inline."','".json_encode($text_ori)."')";
		try{
			$owlPDO->exec($str);
			$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid='".$idpengirim."'";
			try{$owlPDO->exec($query);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
	}
}



function cekstatustel($val,$idpengirim){
	global $dbname;
	global $owlPDO;
	global $val;
	global $idpengirim;
	
	
	// $user = getDetailUser($val,$update);
	// $idpengirim=$user['idpengirim'];
	
	$str = "select * from ".$dbname.".user where telegramid='".$idpengirim."' and telegramid!=''";
	$res=fetchdata($str);
	$e = explode(" ",$val['message']['text']);
	if(count($res)==0 and trim(strtoupper($e[0]))!='REG' and trim(strtoupper($e[0]))!='/START'){
		return $val['message']['text']='NOID';
	}else{		
		foreach($res as $bar){
			if($bar['telegramstatus']=='0' and trim(strtoupper($e[0]))!='REG' and trim(strtoupper($e[0]))!='/START'){
				return $val['message']['text']="statusid tele ".$bar['telegramstatus']."";
			}
			if($bar['status']=='0' and trim(strtoupper($e[0]))!='REG' and trim(strtoupper($e[0]))!='/START'){
				return $val['message']['text']="statusid user ".$bar['status']."";
			}
		}
	}
}

function cekakses($val,$userowl){
	global $dbname;
	global $owlPDO;
	global $val;
	global $userowl;
	
	$str = "select * from ".$dbname.".admin_list where username ='".$userowl."'";
	$res = fetchdata($str);
	$admin=false;
	if(count($res)>0){
		$admin=true;
	}
	
	$e = explode(" ",$val['message']['text']);
	$idcr=strpos($e[0],"@");
	if($idcr>0){	
		$e[0] = substr($e[0],0,$idcr);
	}
	$str = "select * from ".$dbname.".tel_perintah where idperintah='".trim($e[0])."' and status='A'";
	$res=fetchdata($str);
	if(count($res)>0){
		foreach($res as $bar){
			if($bar['group']=='0' and $val['message']['chat']['type']!='private'){
				return $val['message']['text']="akses group";
			}else if($bar['admin']=='1' and $admin==false){
				return $val['message']['text']="akses admin";
			}
		}
	}elseif((trim(strtoupper($e[0]))=='REG' or trim(strtoupper($e[0]))=='UNREG') and $val['message']['chat']['type']!='private'){
		return $val['message']['text']="akses group";
	}else{
		return $val['message']['text']=$val['message']['text'];
	}
	
}

function getDetailUser($val,$update){
	global $dbname;
	global $owlPDO;
	global $val;
	global $update;
	
	
	if($val['message']['chat']['type']!='private'){
		#kalau di group id nya pakai siapa yg kirim pesan
		if(!@$update["message"]){
			$idpengirim=$update['callback_query']['from']['id'];
		}else{			
			$idpengirim=$update['message']['from']['id'];
		}
		#$idpengirim=$val['message']['from']['id'];
	}else{
		$idpengirim=$val['message']['chat']['id'];
	}
	
	
	$detailuser=array();
	$detailuser['idpengirim'] = $idpengirim;
	$str = "select * from ".$dbname.".user where telegramid='".$idpengirim."' and telegramid!=''";
	$res = fetchdata($str);
	if(count($res)>0){
		$detailuser['lokasitugas']= $res[0]['kodeorg'];
		$detailuser['userowl']    = $res[0]['namauser'];
		$detailuser['karidowl']   = $res[0]['karyawanid'];
		
		$s = "select tipe,induk from ".$dbname.".organisasi where kodeorganisasi='".$res[0]['kodeorg']."'";
		$r = fetchdata($s);
		$detailuser['tipeorg']= $r[0]['tipe'];
		$detailuser['kodept']= $r[0]['induk'];
		
		$n = "select namakaryawan,subbagian,kodejabatan from ".$dbname.".datakaryawan where karyawanid='".$res[0]['karyawanid']."'";
		$e = fetchdata($n);
		$detailuser['namakary']= $e[0]['namakaryawan'];
		$detailuser['subbagian']= $e[0]['subbagian'];
		$detailuser['kodejabatan']= $e[0]['kodejabatan'];
		
	}
	return $detailuser;
}

function listperintah(){
	global $dbname;
	global $userowl;
	
	$str = "select * from ".$dbname.".admin_list where username ='".$userowl."'";
	$res = fetchdata($str);
	$admin=false; 
	$wh=" and admin='0'";
	if(count($res)>0){
		$admin=true; $wh="";
	}
	
	$str = "select * from ".$dbname.".tel_perintah where status='A' and idperintah!='/MENU' ".$wh." order by admin, id asc";
	$res=fetchdata($str);
	foreach($res as $val){
		$perintah[]=array(
			'id'   => strtoupper($val['idperintah']),
			'ket'  => $val['keterangan'],
			'admin'=> $val['admin'],
			'group'=> $val['group']
		);
	}	
	
	return $perintah;
}

function orgDetail($uname){
	global $dbname;
	
	$str = "select * from ".$dbname.".user where namauser='".$uname."'";
	$res=fetchdata($str);
	foreach($res as $val){
		$optOrg[$val['kodeorg']] = $val['kodeorg'];
	}
	
	$str = "select * from ".$dbname.".user_orgdetail where namauser='".$uname."'";
	$res=fetchdata($str);
	foreach($res as $val){
		$optOrg[$val['kodeorganisasi']] = $val['kodeorganisasi'];
	}
	
	return $optOrg;
}

function getKolomName($namatable,$output){
	global $dbname;
	global $owlPDO;
	$option='';$arrReturn=Array();
	
	try { 
		$str=$owlPDO->query("select * from ".$dbname.".".$namatable." limit 1");
		$raw_column_data = $str->fetchAll();
		$jlh_colom=$str->columnCount();
			for($x=0;$x<$jlh_colom;$x++){
				$test=$str->getColumnMeta($x);
				$column_names[] = $test['name'];
				array_push($arrReturn, $test['name']);
				$option.="<option value='".$test['name']."'>".$test['name']."</option>"; 
			} 
	} catch (PDOException $e){
		echo " Gagal: ".$e->getMessage(); //return exception
		return false;
	}         
        
	if($output=='array')
	  return $arrReturn;
	else
	  return $option; 
}

function bagi($e,$i){
	if($i!='' and $i!='0'){
		$n=$e/$i;
	}else{
		$n=0;
	}
	return $n;
}

function getTab($text,$len){
	if(strlen($text)<$len){
		$jlh = $len-strlen($text);$tab="";
		for($i=1;$i<=$jlh;$i++){
			$tab=$tab."\t";
		}
		$return=$text.$tab;
	}else{
		$return=$text;
	}
	return $return;
}

?>