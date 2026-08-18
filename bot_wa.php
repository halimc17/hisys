<?php
include('lib/nangkoelib.php');
include('lib/zLib.php');
require_once('config/connection.php');

//specify instance URL and token
$apiurl = 'https://api.chat-api.com/instance296320/';
$token = '7kflkxg6po0xfeda';

$tipelogin='AD'; #untuk server ksp
$tipelogin='NONAD'; 



//get the JSON body from the instance
$json = file_get_contents('php://input');
$decoded = json_decode($json,true);

//write parsed JSON-body to the file for debugging
ob_start();
var_dump($decoded);
$input = ob_get_contents();
ob_end_clean();
#file_put_contents('input_requests.log',$input.PHP_EOL,FILE_APPEND);

if(isset($decoded['messages'])){
	foreach($decoded['messages'] as $message){
		$param          = explode(' ',strtoupper(trim($message['body'])));
		$text           = explode(' ',trim($message['body']));
		$telegram_msg   = $message['message']['text'];
		$msgid          = $message['id'];
		$chatid         = $message['chatId'];
		$phone          = str_replace('@c.us','',$message['author']);
		$first_name     = $message['senderName'];
		$last_name      = $message['senderName'];
		$telegram_uname = $message['senderName'];
		$type           = $message['type']; #chat image document
		$caption        = $message['caption'];
		$quotedmsgbody  = $message['quotedMsgBody'];
		$quotedmsgid    = $message['quotedMsgId'];
		$quotedmsgtype  = $message['quotedMsgType'];
		$anggotabaru    = "";
		$telegram_group = "";
		#$anggotabaru   = $message['message']['new_chat_participant']['id'];
		#$telegram_group= $message['message']['chat']['type']; #private, group, supergroup
		
		
		
		if(!$message['fromMe']){
			switch(mb_strtoupper($param[0],'UTF-8')){
				// case 'hi':
					// welcome($message['chatId'],false); 
				// break;
				// case 'chatId': 
					// showchatId($message['chatId']); 
				// break;
				// case 'time':
					// waktu($message['chatId']); 
				// break;
				// case 'me':
					// me($message['chatId'],$message['senderName']); 
				// break;
				// case 'file':
					// kirimfile($message['chatId'],$param[1]); 
				// break;
				// case 'ptt':
					// ptt($message['chatId']); 
				// break;
				// case 'geo':
					// geo($message['chatId']); 
				// break;
				// case 'group':
					// group($message['author']); 
				// break;
				case'/TBS':
					if($param[1]!=''){
						$wh=$group="";
						$s = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param[1]."'";
						$r = fetchdata($s);
						foreach($r as $b){
							$nmorg=$b['kodeorganisasi'];
							$tipe=$b['tipe'];
						}
						$xkdorg=explode("#",$param[1]);
						if($nmorg!=''){
							if($tipe=='PABRIK'){
								$wh.=" and millcode like '".substr($param[1],0,4)."%'";
							}else{							
								$wh.=" and kodeorg like '".substr($param[1],0,4)."%'";
								$wh.=" and divcode like '".$param[1]."%'";
								$group=",divcode";
							}
							if($param[2]!=''){					
								$tglhi = $param[2];			
							}else{					
								$tglhi = date("Y-m-d");
							}
							$info=$param[1];
						}else if($xkdorg[0]=='EXTN'){
							$wh =" and kodeorg = ''";
							$wh.=" and divcode = '' and kodecustomer!=''";
							$wh.=" and millcode = '".$xkdorg[1]."'";
							$group=",namatransportir";
							if($param[2]!=''){					
								$tglhi = $param[2];
							}else{					
								$tglhi = date("Y-m-d");
							}
							$info=substr($param[2],0,4);
						}else{
							$tglhi = $param[1];			
						}
					}else{				
						$tglhi = date("Y-m-d");
					}
					$str = "select sum(beratbersih) as kg,count(notransaksi) as rit, kodeorg,millcode,divcode,pengirim,namatransportir from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' and kodebarang='40000003' ".$wh." group by millcode,kodeorg".$group."";
					$res=fetchdata($str);
					foreach($res as $bar){
						if($bar['kodeorg']==''){
							$bar['kodeorg']='EXTN#'.$bar['millcode'];
							$bar['divcode']=$bar['namatransportir'];
						}
						$kdpks[$bar['millcode']]=$bar['millcode'];
						if($group!=''){
							$data[$bar['millcode']][$bar['divcode']]+=$bar['kg'];
							$rit[$bar['millcode']][$bar['divcode']]+=$bar['rit'];				
						}else{
							$data[$bar['millcode']][$bar['kodeorg']]+=$bar['kg'];
							$rit[$bar['millcode']][$bar['kodeorg']]+=$bar['rit'];				
						}
						if($tipe=='PABRIK'){						
							$dtkdorg[$bar['kodeorg']]=$bar['kodeorg'];
							$dtmill[$bar['kodeorg']]=$bar['kodeorg'];
						}elseif($tipe=='KEBUN'){
							$dtmill[$bar['divcode']]=$bar['divcode'];
						}else{
							$dtkdorg[$bar['divcode']]=$bar['divcode'];
							$dtmill[$bar['millcode']]=$bar['millcode'];
						}
					}
					
					$s = "select * from ".$dbname.".organisasi where tipe in ('AFDELING')";
					$r = fetchdata($s);
					foreach($r as $b){
						$nmrg[$b['kodeorganisasi']]=$b['kodeorganisasi']." - ".$b['namaorganisasi'];
						$ip[$b['kodeorganisasi']]=$b['inti'];
					}
					
					
					$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tglhi)));
					$tgldepan = date('Y-m-d', strtotime('+1 days', strtotime($tglhi)));
					if(count($data)>0){
						$tab.="_Pengiriman TBS ".$info." Periode : ".$tglhi."_\n";
						foreach($data as $millcode => $key){
							$no=0;
								$tab.="\n*PKS : ".$millcode."*\n";
								if($group!=''){
									$tab.="Divisi/KUD :\n";
								}
							foreach($key as $kdorg => $kg){
								$no++;
								$nkdorg=explode("#",$kdorg);
								if(strlen($nkdorg[0])==6 and $nkdorg[1]=='' and $ip[$nkdorg[0]]=='0'){
									$nkdorg[0]=$nmrg[$nkdorg[0]];
								}
								$tab.="   ".$no.". ".$nkdorg[0]." : *".hidezerodecimal($kg)."* Kg (".$rit[$millcode][$kdorg]." Rit)\n";
								$stpks[$millcode]['kg']+=$kg;
								$stpks[$millcode]['rit']+=$rit[$millcode][$kdorg];
								$gt['kg']+=$kg;
								$gt['rit']+=$rit[$millcode][$kdorg];
							}
							$tab.="*Total ".$millcode." : ".hidezerodecimal($stpks[$millcode]['kg'])." Kg (".$stpks[$millcode]['rit']." Rit)*\n";
						}
						$tab.="\n*Grand Total : ".hidezerodecimal($gt['kg'])." Kg (".$gt['rit']." Rit)*\n";
					}else{
						$tab="Data tidak ditemukan.";
					}
					$tab.="\n_Server : ".$_SERVER['SERVER_NAME']."_";
					sendMessage($chatid,$tab); 
				break;
				default:
					$tab="Perintah yang tersedia : /TBS";
					$tab.="\n_Server : ".$_SERVER['SERVER_NAME']."_";
					sendMessage($chatid,$tab); 
				break;
			}
		}
	}
}



//this function calls function sendRequest to send a simple message
//@param $chatId [string] [required] - the ID of chat where we send a message
//@param $text [string] [required] - text of the message
function welcome($chatId, $noWelcome = false){
	$welcomeString = ($noWelcome) ? "Incorrect command\n" : "WhatsApp Demo Bot PHP\n";
	sendMessage($chatId,
			$welcomeString.
			"Commands:\n".
			"1. chatId - show ID of the current chat\n".
			"2. time - show server time\n".
			"3. me - show your nickname\n".
			"4. file [format] - get a file. Available formats: doc/gif/jpg/png/pdf/mp3/mp4\n".
			"5. ptt - get a voice message\n".
			"6. geo - get a location\n".
			"7. group - create a group with the bot"
	);
}

//sends Id of the current chat. it is called when the bot gets the command "chatId"
//@param $chatId [string] [required] - the ID of chat where we send a message
function showchatId($chatId){
	sendMessage($chatId,'chatId: '.$chatId);
}

//sends current server time. it is called when the bot gets the command "time"
//@param $chatId [string] [required] - the ID of chat where we send a message
function waktu($chatId){
	sendMessage($chatId,date('d.m.Y H:i:s'));
}

//sends your nickname. it is called when the bot gets the command "me"
//@param $chatId [string] [required] - the ID of chat where we send a message
//@param $name [string] [required] - the "senderName" property of the message
function me($chatId,$name){
	sendMessage($chatId,$name);
}

//sends a file. it is called when the bot gets the command "file"
//@param $chatId [string] [required] - the ID of chat where we send a message
//@param $format [string] [required] - file format, from the params in the message body (text[1], etc)
function kirimfile($chatId,$format){
	$availableFiles = array(
		'doc' => 'document.doc',
		'gif' => 'gifka.gif',
		'jpg' => 'jpgfile.jpg',
		'png' => 'pngfile.png',
		'pdf' => 'presentation.pdf',
		'mp4' => 'video.mp4',
		'mp3' => 'mp3file.mp3'
	);

	if(isset($availableFiles[$format])){
		$data = array(
			'chatId'=>$chatId,
			'body'=>'https://domain.com/PHP/'.$availableFiles[$format],
			'filename'=>$availableFiles[$format],
			'caption'=>'Get your file '.$availableFiles[$format]
		);
		sendRequest('sendFile',$data);
	}
}

//sends a voice message. it is called when the bot gets the command "ptt"
//@param $chatId [string] [required] - the ID of chat where we send a message
function ptt($chatId){
	$data = array(
		'audio'=>'https://domain.com/PHP/ptt.ogg',
		'chatId'=>$chatId
	);
	sendRequest('sendAudio',$data);
}

//sends a location. it is called when the bot gets the command "geo"
//@param $chatId [string] [required] - the ID of chat where we send a message
function geo($chatId){
	$data = array(
		'lat'=>51.51916,
		'lng'=>-0.139214,
		'address'=>'Ваш адрес',
		'chatId'=>$chatId
	);
	sendRequest('sendLocation',$data);
}

//creates a group. it is called when the bot gets the command "group"
//@param chatId [string] [required] - the ID of chat where we send a message
//@param author [string] [required] - "author" property of the message
function group($author){
	$phone = str_replace('@c.us','',$author);
	$data = array(
		'groupName'=>'Group with the bot PHP',
		'phones'=>array($phone),
		'messageText'=>'It is your group. Enjoy'
	);
	sendRequest('group',$data);
}

function sendMessage($chatId, $text){
	$data = array('chatId'=>$chatId,'body'=>$text);
	sendRequest('message',$data);
}

function sendRequest($method,$data){
	global $apiurl;
	global $token;
	
	$url = $apiurl.$method.'?token='.$token;
	if(is_array($data)){ $data = json_encode($data);}
	$options = stream_context_create(['http' => [
		'method'  => 'POST',
		'header'  => 'Content-type: application/json',
		'content' => $data]]
	);
	$response = file_get_contents($url,false,$options);
	#file_put_contents('requests.log',$response.PHP_EOL,FILE_APPEND);
}
	
?>	