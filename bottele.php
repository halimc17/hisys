<?php
// include('lib/nangkoelib.php');
// include('lib/zLib.php');
// require_once('config/connection.php');

#@owlnotifbot
#$token="1624052900:AAGAxZ7fWKOhM-6SKtMEJG7Lm0M0Cht6ZrY";

#@owlerrorbot
$token = "1545064092:AAHCgJV8P-CyGASfeSC-0bXjtMAZL1tqbxE";

$debug = false;
function request_url($method){
	global $token;
	return "https://api.telegram.org/bot" . $token . "/". $method;
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);
if(!@$update["message"]){
	$val = $update['callback_query'];
}else{
	$val = $update;		
}

$telegram_id   = $val['message']['chat']['id'];
$first_name    = $val['message']['chat']['first_name'];
$telegram_uname= $val['message']['chat']['username'];
$msgid         = $val['message']['message_id'];

$message_text="<a href=https://www.google.com/>Google</a>";
$message_text="<a href=https://www.google.com/>Google</a>";
$message_text="http://localhost/ksp/kebun_slave_operasional_postingx.php?notransaksi=";
$message_text="Ini balasan \n";
$message_text.=$val['message']['text'];


#sendtele($telegram_id,$message_text);
sendtele($telegram_id, $message_text);
//send_reply($telegram_id, $msgid, $message_text);

// function send_reply($telegram_id, $msgid, $message_text){
	// global $debug;
	// $data = array(
		// 'chat_id' => $telegram_id,
		// 'text'  => $message_text,
		// 'parse_mode'  => "html",
		// 'reply_to_message_id' => $msgid
	// );
	
	// $options = array(
		// 'http' => array(
			// 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			// 'method'  => 'POST',
			// 'content' => http_build_query($data),
		// ),
	// );
	// $context  = stream_context_create($options); 
	// $result = file_get_contents(request_url('sendMessage'), false, $context);

	// if ($debug) 
		// print_r($result);
// }

# fungsi kirim ver 1
function sendtele($telegram_id,$message_text){
	global $token;

	$url = "https://api.telegram.org/bot".$token."/sendMessage?&chat_id=".$telegram_id;
	$url = $url."&text=".urlencode($message_text);
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


?>

<?php

// #@owlnotif_bot
// $token="1624052900:AAGAxZ7fWKOhM-6SKtMEJG7Lm0M0Cht6ZrY";

// function request_url($method){
	// global $token;
	// return "https://api.telegram.org/bot" . $token . "/". $method;
// }

// function get_updates($offset) {
    // $url = request_url("getUpdates")."?offset=".$offset;
	// $resp = file_get_contents($url);
	// $result = json_decode($resp, true);
	// if ($result["ok"]==1){
		// return $result["result"];
	// }
	// return array();
// }

// function ambilpesan(){	
	// $update_id  = 0;
	// if (file_exists("last_update_id")) {
		// $update_id = (int)file_get_contents("last_update_id");
	// }
	// $updates = get_updates($update_id);
	// foreach ($updates as $message){
		// $data['text']  = $message['message']['text'];
		// $data['sender']= $message['message']['chat']['id'];
	// }
	// file_put_contents("last_update_id", $update_id + 1);
	// return $data;
// }

// $param = ambilpesan();
// switch($param['text']){
	// case'TBS':
		// $telegram_id = $param['sender'];
		// $message_text = "Ini untuk balasan TBS";		
		// sendtele($telegram_id,$message_text);
	// break;
	// case'CPO':
		// $telegram_id = $param['sender'];
		// $message_text = "Ini untuk balasan CPO";
		// sendtele($telegram_id,$message_text);
	// break;
	// case'KER':
		// $telegram_id = $param['sender'];
		// $message_text = "Ini untuk balasan KER";
		// sendtele($telegram_id,$message_text);
	// break;
	// default:
		// $telegram_id = $param['sender'];
		// $message_text = "Perintah belum terdaftar,\nperintah terdaftar : TBS, CPO, KER";
		// sendtele($telegram_id,$message_text);
	// break;
// }


// function sendtele($telegram_id,$message_text){
	// global $token;

    // $url = "https://api.telegram.org/bot".$token."/sendMessage?&chat_id=".$telegram_id;
    // $url = $url."&text=".urlencode($message_text);
    // $url;
    // $ch = curl_init();
    // $optArray = array(
            // CURLOPT_URL => $url,
            // CURLOPT_RETURNTRANSFER => true
    // );
    // curl_setopt_array($ch, $optArray);
    // $result = curl_exec($ch);
    // curl_close($ch);
// } 

// // echo "<pre>";
// // print_r(ambilpesan());
// // echo "</pre>";


// function send_reply($chatid, $msgid, $text){
    // $data = array(
        // 'chat_id' => $chatid,
        // 'text'  => $text,
        // 'reply_to_message_id' => $msgid
    // );
    // // use key 'http' even if you send the request to https://...
    // $options = array(
    	// 'http' => array(
        	// 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        	// 'method'  => 'POST',
        	// 'content' => http_build_query($data),
    	// ),
    // );
    // $context  = stream_context_create($options);

    // $result = file_get_contents(request_url('sendMessage'), false, $context);
    // print_r($result);
// }

// function create_response(){
	// $text="kirim kirim bro";
	// return "definisi " . $text;
// }


// function process_message($message){
    // $updateid = $message["update_id"];
    // $message_data = $message["message"];
    // if (isset($message_data["text"])) {
    // $chatid = $message_data["chat"]["id"];
        // $message_id = $message_data["message_id"];
        // $text = $message_data["text"];
        // $response = create_response($text);
        // send_reply($chatid, $message_id, $response);
    // }
    // return $updateid;
// }

// function process_one(){
	// $update_id  = 0;
	// if (file_exists("last_update_id")) {
		// $update_id = (int)file_get_contents("last_update_id");
	// }
	// $updates = get_updates($update_id);
	// foreach ($updates as $message){
		// $update_id = process_message($message);
	// }
	// file_put_contents("last_update_id", $update_id + 1);

// }
      
?>

