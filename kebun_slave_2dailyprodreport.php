<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$param = $_POST;
if(count($param)==0){
	$param = $_GET;	
}

$tipelogin='local';
$tipelogin='server';

if($tipelogin=='server'){
	$idbot = "@owlksp_robot";
	$token = "1348581495:AAEsK4yzkWGxNvcmIuMxwZYoFdLHOtSOsMw";	
}else{
	$idbot = "@owlnotifbot";
	$token = "1624052900:AAGAxZ7fWKOhM-6SKtMEJG7Lm0M0Cht6ZrY";
}


switch($param['method']){
	case'preview':
		$folder = "imgbot/";
		$filepdf=$folder."Daily_Prod_Report_".$param['unit'].".pdf";
		
		switch($param['sumber']){
			case'telegram':
				if (!file_exists($filepdf)) {
					include('bot_graph.php');
				}
				if (!file_exists($filepdf)) {
					exit("Warning : Data tidak ditemukan.");
				}else{
					$message_text="Dear Bapak/Ibu,\n\nTerlampir kami kirimkan data : \nLaporan : Daily Production Report.\nRegion : ".$param['unit']."\nTanggal : ".$param['tanggal']."\n\nDemikian disampaikan, terima kasih.\n\nSalam,\n".$_SESSION['empl']['name']."\n".$_SESSION['standard']['username']."";
					
					foreach($param['email'] as $key => $telegram_id){
						send_reply($telegram_id, $message_text);
						sendDocument($telegram_id,$filepdf);
					}
				}
			break;
			case'email':
				if (!file_exists($filepdf)) {
					include('bot_graph.php');
				}

				foreach($param['email'] as $key => $email){
					$subject="Daily Production Report";
					$body="Dear Bapak/Ibu,
						<br>
						<br>
						Terlampir kami kirimkan data :<br>
						Laporan : Daily Production Report.<br>
						Region : ".$param['unit']."<br>
						Tanggal : ".$param['tanggal']."<br><br>
						
						Demikian disampaikan, terima kasih.<br><br>
						Salam,<br>".$_SESSION['empl']['name']."
						<br>".$_SESSION['standard']['username']."
						";
					
					kirimEmailatt($email,$cc="",$subject,$body,$mailType='text/html',$filepdf);
				}
			break;
			default:
				include('bot_graph.php');

				$tab="<embed src = 'imgbot/Daily_Prod_Report_".$param['unit'].".pdf#page=1&view=fitH,100' width = '100%' height = '100%' type='application/pdf'>";
				echo $tab;
			break;
		}
	break;
	case'kirim':
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	
		$optloc="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select distinct lokasitugas from ".$dbname.".datakaryawan order by lokasitugas asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optloc.="<option value=" . $bar['lokasitugas'] . ">" . $bar['lokasitugas'] . " - ".$nmorg[$bar['lokasitugas']]."</option>";
		}

		$tab="<input hidden id=sumber value=".$param['sumber'].">
			<fieldset style=float:left><legend>Find</legend>
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
					<td><button class=mybutton onclick=listcari()>Preview</button></td>
				</tr>
				
			</table>
			</fieldset>
			<div style=clear:both></div>
			<fieldset><legend>List</legend>
				<div id=listcari style=height:250px;overflow:auto;><script>listcari()</script></div>
			</fieldset>
			<table style=align:center;width:100%>
				<td align=center><button class=mybutton onclick=kirimkan()>Kirim ".$param['sumber']."</button></td>
			</table>
			
			";
		echo $tab;
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
			<table border=0 cellpadding=1 cellspacing=1 class=sortable>
				<thead>
					<tr class=rowheader style=text-align:center;height:25px>
						<th>No</th>
						<th>" . $_SESSION['lang']['nama'] . "</th>
						<th>" . $_SESSION['lang']['user'] . "</th>
						<th>" . $_SESSION['lang']['jabatan'] . "</th>
						<th>" . $_SESSION['lang']['lokasitugas'] . "</th>
						<th>" . $param['sumber']. "</th>
						<th  style='width:30px;align:center'>Action<br>
						<input id=checkall type=checkbox onclick=clickall()>
						</th>
					</tr>
				</thead>
				<tbody>";
				$str="select * from ".$dbname.".datakaryawan where 1=1 ".$where."";
				$res=fetchdata($str);
				foreach($res as $bar){
					$idtel = makeOption($dbname, 'user', 'karyawanid,telegramid',"karyawanid='".$bar['karyawanid']."'");
					$uname = makeOption($dbname, 'user', 'karyawanid,namauser',"karyawanid='".$bar['karyawanid']."'");
					if($param['sumber']=='telegram'){
						$bar['email']=$idtel[$bar['karyawanid']];
					}
					
					$no++;
					$tab.="<tr class=rowcontent style=height:25px>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$bar['namakaryawan']."</td>";
					$tab.="<td>".$uname[$bar['karyawanid']]."</td>";
					$tab.="<td>".$nmjab[$bar['kodejabatan']]."</td>";
					$tab.="<td>".$bar['lokasitugas']."</td>";
					$tab.="<td name=mail[]>".trim($bar['email'])."</td>";
					$tab.="<td align=center><input name=check[] type=checkbox></td>";
					$tab.="</tr>";
				}
				
			$tab.="</tbody>
			</table>
			";
		echo $tab;
	break;
}

function sendDocument($telegram_id,$img_dir){
	global $token;
	
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
	global $token;
	
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