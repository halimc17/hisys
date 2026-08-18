<?php
include('lib/nangkoelib.php');
include('lib/zLib.php');
require_once('config/connection.php');

# table update
# user
# tel_perintah
# tel_activity
# tipelogin = AD dan NONAD

$tipelogin='AD'; #untuk server ksp
$tipelogin='NONAD'; 

if($tipelogin=='AD'){
	#@owlksp_robot
	$token = "1348581495:AAEsK4yzkWGxNvcmIuMxwZYoFdLHOtSOsMw";	
}else{
	$idbot="@owlnotifbot";
	$token="1624052900:AAGAxZ7fWKOhM-6SKtMEJG7Lm0M0Cht6ZrY";
	
	$idbot="owlglg_robot";
	$token="1848547993:AAFgpfgvBxzMKmaukaaq9MJSONOaUtRZseM";
	
	#@owlerrorbot
	#$token = "1545064092:AAHCgJV8P-CyGASfeSC-0bXjtMAZL1tqbxE";	
}


$debug = false;
function request_url($method){
	global $token;
	return "https://api.telegram.org/bot" . $token . "/". $method;
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);
if(!@$update["message"]){
	$val = $update['callback_query'];
	$val['message']['text'] = $update['callback_query']['data'];
	$text_ori = $update;		
}else{
	$val = $update;		
	$text_ori = $update;		
}

$telegram_msg  = $val['message']['text'];
$msgid         = $val['message']['message_id'];
$telegram_group= $val['message']['chat']['type']; #private, group, supergroup
$telegram_id   = $val['message']['chat']['id'];
$first_name    = $val['message']['chat']['first_name'];
$last_name     = $val['message']['chat']['last_name'];
$telegram_uname= $val['message']['chat']['username'];
$anggotabaru   = $val['message']['new_chat_participant']['id'];


#ambil data user owl
/* $user       = getDetailUser($val,$update);
$lokasitugas= $user['lokasitugas'];
$userowl    = $user['userowl'];
$karidowl   = $user['karidowl'];
$idpengirim = $user['idpengirim'];
$tipeorg    = $user['tipeorg'];
$kodept     = $user['kodept'];
$namakary   = $user['namakary'];
$subbagian  = $user['subbagian'];
$kodejabatan= $user['kodejabatan']; */

#cuma memastikan kalau idtelegram sudah ada di table user
#jika belum terdaftar maka :
# - private = tidak bisa kirim perintah
# - group = tidak bisa kirim perintah hanya readonly
//cekstatustel($val,$idpengirim);


#cek akses perintah bisa di jalankan di group atau tidak
#cek akses perintah memerlukan level admin atau tidak
//cekakses($val,$userowl);


//$formreg = "\n<b>REG</b> spasi <b>USER_OWL</b> spasi <b>PASS_OWL</b>\n<i>contoh : reg user.owl 123456</i>\n";
//$formunreg = "\n<b>UNREG</b> spasi <b>USER_OWL</b> spasi <b>PASS_OWL</b>\n<i>contoh : unreg user.owl 123456</i>\n";

#olah pesan yg di kirim oleh user
#pemisah perintah

$pemisah = " ";
$param= explode($pemisah,strtolower($val['message']['text']));
$text = explode($pemisah,$val['message']['text']);
$lower= explode($pemisah,strtolower($val['message']['text']));

#jika di group perintah biasa ada /perintah@usertelegram
#hapus @usertelegram dulu disini
$idcr=strpos($param[0],"@");
if($idcr>0){	
	$param[0]    = substr($param[0],0,$idcr);
	$lower[0]    = substr($lower[0],0,$idcr);
	$arruser     = explode("@",$param[0]);
	$usertelgroup= $arruser[1];
}

if(count(listperintah($param[0]))>0){
	#jika perintah ada di help_ht maka akan di tampilkan daftar perintah di help_dt
	$str = "select * from ".$dbname.".help_ht where namamodul='".trim($param[0])."'";
	$res = fetchdata($str);
	$idmodul = $res[0]['idmodul'];
	
	$param[1]=$idmodul;
	$param[0]='/level1';
}elseif(count(listperintahdt("",$param[0]))>0){
	#jika perintah ada di help_dt maka akan di tampilkan daftar perintah di help_faq
	$str = "select * from ".$dbname.".help_dt where namasubmenu='".trim($param[0])."'";
	$res = fetchdata($str);
	$idmodul = $res[0]['idsubmodul'];
	
	$param[1]=$idmodul;
	$param[0]='/level2';
}

$slash=substr($param[0],0,1);
if($slash=="/"){	
	if(count(listperintahfaq("","",substr($param[0],1,999999)))>0){
		$param[2]=substr($param[0],1,999999);
		$param[0]='/level4';
		$param[9]='sc';
	}
}

sendApiAction($telegram_id);

switch($param[0]){
	case'/help':
		$cari = trim(substr($val['message']['text'],5,9999));
		$cari = str_replace(" ","%",$cari);
		if($param[1]!=''){
			$str = "select * from ".$dbname.".help_faq where 1=1 and question like '%".$cari."%' order by idsubmodul asc, idfaq asc";
			$res = fetchdata($str);
			if(count($res)>0){			
				foreach($res as $val){
					$message_text.="/".$val['idfaq']." => ".$val['question']."\n";
				}		
			}else{
				$message_text.="Tidak ditemukan, silahkan coba dengan keyword lainnya";
			}			
		}elseif(strtolower(trim($telegram_msg))=='/help'){
			$message_text="Ketik /help spasi keyword pencarian\nContoh : /help kas bank";			
		}
		
	break;
	case'/level1':
		$perintahdt = listperintahdt($param[1]);
		
		$r=0; $jlh=count($perintahdt);
		if($jlh>0){
			$message_text="Silahkan click tombol dibawah";
			foreach($perintahdt as $key => $bar){
				if($r==round($jlh/2)){$r=0;}
				$inline_button[]= array(array(
								"text"=>$bar['ket'],"callback_data"=>strtolower($bar['nama'])
							)
						);
				$r++;
			}			
		}else{
			$message_text="Detail perintah tidak ditemukan.";			
		}
		
	break;
	case'/level2':
		$perintah = listperintahfaq($param[1]);
		
		$jlh=count($perintah);
		if($jlh>0){
			$message_text="Silahkan click tombol dibawah";
			foreach($perintah as $key => $bar){
				$inline_button[] = array(array(
								"text"=>"/".$bar['id']." - ".nl2br($bar['ket']),"callback_data"=>"/level3".$pemisah."".$pemisah.strtolower($bar['id'])
							));
			}			
		}else{
			$nmperintah=makeOption($dbname,'help_dt','idsubmodul,keterangan');
			$message_text="Detail perintah tidak ditemukan.\n";			
			$message_text.="<b>Menu</b> : ".$nmperintah[$param[1]];
		}
		
	break;
	case'/level3':
		#listperintahfaq(induk,nama,id)
		/* $perintah[]=array(
			'id'   => strtolower($val['idfaq']),
			'nama' => strtolower($val['question']),
			'url'  => $val['tujuan'],
			'jawab'=> $val['answer'],
			'ket'  => $val['question']
		); */
		$perintah = listperintahfaq("",$param[1],$param[2]);
		
		$jlh=count($perintah);
		if($jlh>0){
			$nu=$ni=$n0=0;
			foreach($perintah as $key => $bar){
				$nmperintah = makeOption($dbname,'help_dt','idsubmodul,keterangan',"idsubmodul='".$bar['parent']."'");
				if($bar['url']!=''){
					$message_text="<b>ID</b> : /".$bar['id']."\n";
					$message_text.="<b>Menu</b> : ".$nmperintah[$bar['parent']]."\n";
					$message_text.="<b>Tentang</b> : ".$bar['ket']."\n";
					if($bar['jawab']!=''){
						$message_text.="<b>Penjelasan</b> : \n".$bar['jawab']."\n";
					}
					if(file_exists($bar['url'])){
						$document=$bar['url'];
						//sendDocument($telegram_id,$bar['url']);
					}else{
						$nu++;
						if($nu==1){$message_text.="Silahkan click tombol dibawah";}
						//$inline_button[] = array(array("text"=>nl2br($bar['ket']),"url"=>$bar['url']));
						$message_text.=$bar['url'];
					}
				}else{
					$ni++;
					if($ni==1){$message_text="Silahkan click tombol dibawah";}
					// $inline_button[]= array(array(
									// "text"=>"/".$bar['id']." - ".nl2br($bar['ket']),"callback_data"=>"/level4".$pemisah."#".$pemisah.$bar['id']
									// )
								// );
								
					$message_text="<b>ID</b> : /".$bar['id']."\n";
					$message_text.="<b>Menu</b> : ".$nmperintah[$bar['parent']]."\n";
					$message_text.="<b>Tentang</b> : ".$bar['ket']."\n";
					$message_text.="<b>Penjelasan</b> : \n".$bar['jawab']."\n";								
				}
			}			
		}else{
			$message_text.="Detail perintah tidak ditemukan (".$param[1].").";			
		}
		
	break;
	case'/level4':
		$perintah = listperintahfaq("","",$param[2]);
		
		$jlh=count($perintah);
		if($jlh>0){
			foreach($perintah as $key => $bar){
				$nmperintah = makeOption($dbname,'help_dt','idsubmodul,keterangan',"idsubmodul='".$bar['parent']."'");
				if($param[9]=='sc'){
					if($bar['url']!=''){
						$message_text="<b>ID</b> : /".$bar['id']."\n";
						$message_text.="<b>Menu</b> : ".$nmperintah[$bar['parent']]."\n";
						$message_text.="<b>Tentang</b> : ".$bar['ket']."\n";
						if($bar['jawab']!=''){
							$message_text.="<b>Penjelasan</b> : \n".$bar['jawab']."\n";
						}
						if(file_exists($bar['url'])){
							$document=$bar['url'];
							//sendDocument($telegram_id,$bar['url']);
						}else{
							$nu++;
							if($nu==1){$message_text.="Silahkan click tombol dibawah";}
							//$inline_button[] = array(array("text"=>nl2br($bar['ket']),"url"=>$bar['url']));
							$message_text=$bar['url'];
						}
					}else{
						$message_text="<b>ID</b> : /".$bar['id']."\n";
						$message_text.="<b>Menu</b> : ".$nmperintah[$bar['parent']]."\n";
						$message_text.="<b>Tentang</b> : ".$bar['ket']."\n";
						$message_text.="<b>Penjelasan</b> : \n".$bar['jawab']."\n";					
					}
				}else{
					$message_text="<b>ID</b> : /".$bar['id']."\n";
					$message_text.="<b>Menu</b> : ".$nmperintah[$bar['parent']]."\n";
					$message_text.="<b>Tentang</b> : ".$bar['ket']."\n";
					$message_text.="<b>Penjelasan</b> : \n".$bar['jawab']."\n";					
				}
			}			
		}else{
			$message_text.="Jawaban tidak ditemukan.";			
		}
		
	break;
	case'/menu':
	case'/start':
		$message_text = "Daftar Modul :\n";
		$msg = listperintah();
		foreach($msg as $key => $bar){
			$inline_button[] = array(array("text"=>$bar['id']." - ".nl2br($bar['ket']),"callback_data"=>$bar['id']));
		}

	break;
	case'/listmenu':
		$message_text="menu -  Daftar Menu\n";
		$msg = listperintah();
		foreach($msg as $key => $bar){
			$message_text.=str_replace("/","",$bar['id'])." - ".nl2br($bar['ket'])."\n";
		}

	break;
}

if(strlen($message_text)>'3500'){
	$message_text=substr($message_text,0,3500);
	$message_text.="\n\n<i>text terlalu panjang dan tidak bisa ditampilkan seluruhnya...</i>\n";
}

// $idpengirim=$update['callback_query']['from']['id'];
	
if($telegram_msg!=''){
	if($param[0]!='/START' and $param[0]!='/MENU'){
		#$message_text.="\n<i>Server : ".$_SERVER['SERVER_NAME']."</i>";
		if(substr($param[0],0,1)=='/'){			
			#$message_text.="\n<i>Text : ".$telegram_msg."</i>";
		}
	}
	
	if($usertelgroup!=''){
		//$message_text.="\n<i>User : ".$usertelgroup."</i>";
	}
	
	//$msgid="#TIDAKPAKAI";
	//sendtele($telegram_id, $msgid, $message_text);
	send_reply($telegram_id, $msgid, $message_text,$inline_button);
	
	
	if(($param[0]=='REG' or $param[0]=='UNREG') and $param[1]!='' and $param[2]!=''){		
		#delpesan($telegram_id, $msgid);
	}
	
	#catat perintah apa saja yg di minta user
	telActivity($val,$text_ori,$message_text,$inline_button);
}

if($document!=''){
	sendDocument($telegram_id,$document);
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
function send_reply($telegram_id, $msgid, $message_text,$inline_button=array()){
	global $debug;
	if($msgid=="#TIDAKPAKAI"){
		if(count($inline_button)>0){
			$keyboard=array("inline_keyboard"=>$inline_button);
			$data = array(
				'chat_id' => $telegram_id,
				'text'  => $message_text,
				'parse_mode'  => "html",
				'reply_markup' => json_encode($keyboard)
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
	global $update;
	

	if($val['message']['chat']['type']!='private'){
		#kalau di group id nya pakai siapa yg kirim pesan
		if(!@$update["message"]){
			$idpengirim=$update['callback_query']['from']['id'];
			$first_name = $update['callback_query']['from']['first_name'];				
			$last_name = $update['callback_query']['from']['last_name'];				
			$username = $update['callback_query']['from']['username'];
		}else{			
			$idpengirim=$val['message']['from']['id'];
			
			$first_name = $val['message']['from']['first_name'];				
			$last_name = $val['message']['from']['last_name'];				
			$username = $val['message']['from']['username'];
		}
		#$idpengirim=$val['message']['from']['id'];
	}else{
		$idpengirim=$val['message']['chat']['id'];
		
		$first_name = $val['message']['chat']['first_name'];				
		$last_name = $val['message']['chat']['last_name'];				
		$username = $val['message']['chat']['username'];
	}

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
	

	$telegram_uname= $username." ".$first_name.$last_name;
	
	$ft=$text_ori['message']['text'];
	$json=json_encode($text_ori);
	
	$str="insert into ".$dbname.".tel_activity_glg (username,register,telegramid,file,ip,karyawanid,type,text,full_text,msgid,respond,json)
	values('".$telegram_uname."','UNREG','".$idpengirim."','".$_SERVER['PHP_SELF']."','".$ip."','','".$val['message']['chat']['type']."','','".$ft."','".$val['message']['message_id']."','".$message_text.$inline."','".$json."')";
	try{
		$owlPDO->exec($str);
		$query = "delete from `".$dbname."`.`tel_activity_glg` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid='".$idpengirim."'";
		try{$owlPDO->exec($query);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
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
			$idpengirim=$val['message']['from']['id'];
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

function listperintah($induk=''){
	global $dbname;
	global $userowl;
	
	if($induk!=''){
		$wh=" and namamodul='".strtolower($induk)."'";
	}
	$str = "select * from ".$dbname.".help_ht where 1=1 ".$wh." order by idmodul asc";
	$res=fetchdata($str);
	foreach($res as $val){
		$perintah[]=array(
			'id'   => strtolower($val['namamodul']),
			'ket'  => $val['keterangan']
		);
	}		
	return $perintah;
}
function listperintahdt($induk='',$nama=''){
	global $dbname;
	global $userowl;
	$wh="";
	if($induk!=''){
		$wh.=" and idmodul='".strtolower($induk)."'";
	}
	if($nama!=''){
		$wh.=" and namasubmenu='".strtolower($nama)."'";
	}
	
	$perintah=array();
	$str = "select * from ".$dbname.".help_dt where 1=1 ".$wh." order by idsubmodul asc";
	$res = fetchdata($str);
	foreach($res as $val){
		$perintah[]=array(
			'id'  => $val['idsubmodul'],
			'nama'=> strtolower($val['namasubmenu']),
			'ket' => $val['keterangan']
		);
	}		
	return $perintah;
}

function listperintahfaq($induk='',$nama='',$id=''){
	global $dbname;
	global $userowl;
	
	$wh="";
	if($induk!=''){
		$wh.=" and idsubmodul='".strtolower($induk)."'";
	}
	if($nama!=''){
		$wh.=" and question='".strtolower($nama)."'";
	}
	if($id!=''){
		$wh.=" and idfaq='".strtolower($id)."'";
	}
	
	$str = "select * from ".$dbname.".help_faq where 1=1 ".$wh." order by idsubmodul asc, idfaq asc";
	$res = fetchdata($str);
	foreach($res as $val){
		$perintah[]=array(
			'id'    => strtolower($val['idfaq']),
			'nama'  => strtolower($val['question']),
			'url'   => $val['tujuan'],
			'parent'=> $val['idsubmodul'],
			'jawab' => $val['answer'],
			'ket'   => $val['question']
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

function sendApiAction($chatid, $action = 'typing'){
    $method = 'sendChatAction';
    $data = [
        'chat_id' => $chatid,
        'action'  => $action,

    ];
    $result = apiRequest($method, $data);
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
?>