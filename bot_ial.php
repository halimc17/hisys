<?php
// include('lib/nangkoelib.php');
// include('lib/zLib.php');
//require_once('config/connection.php');

$dbserverial='8.215.33.83';
$dbnameial  ='owl';
$unameial   ='owlApplication';
$passwdial  ='a@8$4!96kS';

try{
	$owlPDOIAL = new PDO('mysql:host='.$dbserverial.';dbname='.$dbnameial, $unameial, $passwdial, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOIAL->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch (PDOException $e) {
   print " Gagal, could not connect\n";	
   print "Error!: " . $e->getMessage() . "<br/>";
   die();
}

// include('lib/zBot.php');
header('Content-Type: text/plain');

# table update
# user
# tel_perintah
# tel_activity
# tipelogin = AD dan NONAD

$tipelogin='AD'; #untuk server ksp
$tipelogin='NONAD'; 

$tipeserver='local';
$tipeserver='server';

$idbot = "@owlial_bot";
$token = "5446707404:AAHqKjYRzAP-g-XGYLnipkD9uZQn0bbvfkg";	
$urlserver= "https://bgmpa.owl-plantation.com/";



$debug = false;

$content= file_get_contents("php://input");
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
$inline_button = array();

sendApiAction($telegram_id);

#olah pesan yg di kirim oleh user
$param= explode(" ",strtoupper($val['message']['text']));
$text = explode(" ",$val['message']['text']);
$lower= explode(" ",strtolower($val['message']['text']));

#jika di group perintah biasa ada /perintah@usertelegram
#hapus @usertelegram dulu disini
$idcr=strpos($param[0],"@");
if($idcr>0){	
	$param[0] = substr($param[0],0,$idcr);
	$lower[0] = substr($lower[0],0,$idcr);
}

#pakai info alamat server atau tidak
//$msgid = true; 
$server= true;


$tglhi = date("Y-m-d");
$data=array();
$str = "select sum(beratbersih) as kg,count(notransaksi) as rit, kodeorg,millcode,divcode,pengirim,namatransportir,trpcode from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' and kodebarang='40000003' group by millcode,kodeorg,trpcode,divcode";
$res=fetchdataial($str);
foreach($res as $bar){
	if($bar['kodeorg']==''){
		$bar['kodeorg']='EXTERNAL';
		$bar['divcode']=getNamaSupplier_ial($bar['trpcode']);
	}else{			
		$bar['kodeorg']='INTERNAL';
		$bar['divcode']=getNamaOrg_ial($bar['divcode']);
	}
	
	$data[$bar['millcode']][$bar['kodeorg']]+=$bar['kg'];
	$rit[$bar['millcode']][$bar['kodeorg']]+=$bar['rit'];				
	
	$dtmill[$bar['millcode']]=getNamaOrg_ial($bar['millcode']);
	$dtdiv[$bar['divcode']]=$bar['divcode'];
	
	$datadiv[$bar['millcode']][$bar['kodeorg']][$bar['divcode']]+=$bar['kg'];
	$datarit[$bar['millcode']][$bar['kodeorg']][$bar['divcode']]+=$bar['rit'];
}

$s = "select * from ".$dbname.".organisasi where tipe in ('AFDELING')";
$r = fetchdataial($s);
foreach($r as $b){
	$nmrg[$b['kodeorganisasi']]=$b['kodeorganisasi']." - ".$b['namaorganisasi'];
	$iplasma[$b['kodeorganisasi']]=$b['inti'];
}


$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tglhi)));
$tgldepan = date('Y-m-d', strtotime('+1 days', strtotime($tglhi)));
if(count($data)>0){
	$inline_button=array();
	$tab="<b>Laporan Pengiriman / Penerimaan TBS \n</b>Tanggal : <b>".($tglhi)."</b>\nJam : <b>".date("H:i:s")."</b>\n";
	foreach($data as $millcode => $key){
		$no=0;
		$tab.="\n<b>PKS : ".getNamaOrg_ial($millcode)."</b>\n";
		if($group!=''){
			$tab.="<b>Divisi/KUD :</b>\n";
		}
		foreach($key as $kdorg => $kg){
			$no++;
			$nkdorg=explode("#",$kdorg);
			if(strlen($nkdorg[0])==6 and $nkdorg[1]=='' and $iplasma[$nkdorg[0]]=='0'){
				$nkdorg[0]=$nmrg[$nkdorg[0]];
			}
			$tab.="   \n".$no.". ".$nkdorg[0]." : <b>".number_format($kg)."</b> Kg (".$rit[$millcode][$kdorg]." Rit)\n";
			$n=0;
			foreach($dtdiv as $div){
				if($datadiv[$millcode][$kdorg][$div]!=''){
					$n++;
					$tab.="     ".$no.".".$n.". ".$div." : <b>".number_format($datadiv[$millcode][$kdorg][$div])."</b> Kg (".$datarit[$millcode][$kdorg][$div]." Rit)\n";
				}
			}
			
			$stpks[$millcode]['kg']+=$kg;
			$stpks[$millcode]['rit']+=$rit[$millcode][$kdorg];
			$gt['kg']+=$kg;
			$gt['rit']+=$rit[$millcode][$kdorg];
		}
		$tab.="<b>Total ".$millcode." : ".number_format($stpks[$millcode]['kg'])." Kg (".$stpks[$millcode]['rit']." Rit)</b>\n";
	}
	$tab.="<b>\nGrand Total : ".number_format($gt['kg'])." Kg (".$gt['rit']." Rit)</b>\n";
	$tab.="<i>\nSumber Timbangan Pabrik</i>\n";
	
	$message_text.=$tab;
}

send_reply($telegram_id, $msgid, $message_text,$inline_button);


function request_url($method){
	global $token;
	return "https://api.telegram.org/bot" . $token . "/". $method;
}

function send_reply($telegram_id, $msgid, $message_text,$inline_button=array(), $keybrd=array()){
	global $debug;
	global $param;
	global $server;
	
	if(strlen($message_text)>'3500'){
		$message_text=substr($message_text,0,3500);
		$message_text.="\n\n<i>text terlalu panjang dan tidak bisa ditampilkan seluruhnya...</i>\n";
	}
	
	if($param[0]!='/START' and $param[0]!='/MENU'){
		if($server!='' or $server==true){			
			$message_text.="\n<i>Server : http://8.215.33.83/ial</i>";
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

function fetchdataial($query=null, $tipe='ASSOC') {
	# Init
	$result = array();
	global $owlPDOIAL;
	# Arrange to Array
	if($query==null) {
		 echo "Error";
	} else {
		try{
			$str = $owlPDOIAL->query($query);
			if($tipe == 'ASSOC'){
				$str->setFetchMode(PDO::FETCH_ASSOC);
			} else if ($tipe == 'OBJECT') {
				$str->setFetchMode(PDO::FETCH_OBJ);
			}
		} catch (PDOException $e){
			echo " Gagal: ".$e->getMessage(); //return exception
			exit;
		}              
		while($bar=$str->fetch()) {
			$result[] = $bar;
		}
	}

	return $result;
}

function sendApiAction($chatid, $action = 'typing'){
    $method = 'sendChatAction';
    $data = [
        'chat_id' => $chatid,
        'action'  => $action,

    ];
    $result = apiRequest($method, $data);
}

function getNamaSupplier_ial($supplierid,$kolom='namasupplier'){
	global $dbnameial;
    global $owlPDOIAL;
    
	$suppliername='';
    $str="select ".$kolom." from ".$dbnameial.".log_5supplier where supplierid='".$supplierid."'";
	$res=fetchdataial($str);
	$suppliername=$res[0][$kolom];
	
	return $suppliername;    
}
function getNamaOrg_ial($kodeorg,$kolom='namaorganisasi'){
	global $dbnameial;
	global $owlPDOIAL;
	
	$hasil="";
	$str="select ".$kolom." from ".$dbnameial.".organisasi where kodeorganisasi='".$kodeorg."'";
	$res=fetchdataial($str);
	$hasil=$res[0][$kolom];
	if($hasil==""){$hasil=$kodeorg;}
	
	return $hasil;    
}
?>