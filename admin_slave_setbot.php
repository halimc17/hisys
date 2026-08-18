<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$proses  = checkPostGet('proses', '');
$token   = checkPostGet('token', '');
$alamat  = checkPostGet('alamat', '');
$action  = checkPostGet('action', '');
$id      = checkPostGet('id', '');
$username= checkPostGet('username', '');

switch ($proses) {
	case'delete':
		$strx = "delete from ".$dbname.".tel_token where id='".$id."'";
		try {$owlPDO->exec($strx);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	break;
    case 'simpan':
		if($id!=''){
			$str="update ".$dbname.".tel_token set username='".$username."', token='".$token."', alamat='".$alamat."' where id='".$id."'";
			try{
				$owlPDO->exec($str);
				}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
		}else{			
			$str="insert into ".$dbname.".tel_token (username,token,alamat)
			values('".$username."','".$token."','".$alamat."')";
			try{
				$owlPDO->exec($str);
				}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
		}
	break;

    case 'loaddata':
		$tab.="<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>    ";
		$tab.="<td align=center>No</td>";
		$tab.="<td align=center>Username</td>";
		$tab.="<td align=center>Token</td>";
		$tab.="<td align=center>Alamat (url)</td>";
		$tab.="<td align=center>Status</td>";
		$tab.="<td align=center colspan=3>Webhook</td>";
		$tab.="<td align=center colspan=2>Action</td>";
		$tab.="</tr></thead>";
	
	
	
		$str = "select * from ".$dbname.".tel_token order by username";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['username'] . "</td>";
            $tab.="<td align=left>" . $bar['token']. "</td>";
            $tab.="<td align=left>" . $bar['alamat']. "</td>";
			$tab.="<td align=left>" . $bar['return']. "</td>";
			
			$tab.="<td align=center><button class=mybutton onclick=getWebhook('".$bar['token']."','".$bar['alamat']."','setWebhook')>Set</button></td>";
			$tab.="<td align=center><button class=mybutton onclick=getWebhook('".$bar['token']."','".$bar['alamat']."','deleteWebhook')>Del</button></td>";
			$tab.="<td align=center><button class=mybutton onclick=getWebhook('".$bar['token']."','".$bar['alamat']."','getWebhookInfo')>Info</button></td>";
			
            $tab.="<td align=center width=25px><img src=images/skyblue/edit.png class=zImgBtn onclick=showEdit('".$bar['id']."','".$bar['token']."','".$bar['alamat']."','".$bar['username']."') title=Edit></td>";
            $tab.="<td align=center width=25px><img src=images/skyblue/delete.png class=zImgBtn onclick=del('".$bar['id']."') title=Delete></td>";
			
		}
		$tab.="</tr>";
		$tab.="</table>";
		
		echo $tab;
	break;
	case'getWebhook':
		if($action=='setWebhook'){			
			$url = "https://api.telegram.org/bot".$token."/".$action."?url=".$alamat."&drop_pending_updates=True";
			$content= file_get_contents($url);
			$update = json_decode($content, true);
			$ok         = "ok => ".$update['ok'];
			$result     = "result => ".$update['result'];
			$description= "description => ".$update['description'];
		}
		if($action=='deleteWebhook'){			
			$url = "https://api.telegram.org/bot".$token."/".$action."?url=".$alamat;
			$content= file_get_contents($url);
			$update = json_decode($content, true);
			$ok         = "ok => ".$update['ok'];
			$result     = "result => ".$update['result'];
			$description= "description => ".$update['description'];

		}
		if($action=='getWebhookInfo'){			
			$url = "https://api.telegram.org/bot".$token."/".$action;
			$content= file_get_contents($url);
			$update = json_decode($content, true);
			$ok         = "ok => ".$update['ok'];
			$result     = "result => \nurl: ".$update['result']['url'].",\nip_address:".$update['result']['ip_address'];
		}
		
		
		$str="update ".$dbname.".tel_token set `return`='".str_replace("error","3rr",$content)."' where `token`='".$token."' and `alamat`='".$alamat."'";
		// exit("error".$str);
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	
	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	#$n = number_format($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
function bagi($e,$i){
	if($i!='' and $i!='0'){
		$n=$e/$i;
	}else{
		$n=0;
	}
	return $n;
}
?>
