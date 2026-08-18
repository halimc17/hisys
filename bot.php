<?php
include('lib/nangkoelib.php');
include('lib/zLib.php');
require_once('config/connection.php');
include('lib/zBot.php');
header('Content-Type: text/plain');

# table update
# user
# tel_perintah
# tel_activity
# tipelogin = AD dan NONAD

$tipelogin='NONAD'; 
$tipelogin='AD'; #untuk server ksp

if($tipelogin=='AD'){
	$idbot    = "@owlksp_robot";
	$token    = "1348581495:AAHD9WS9wQw0tyMq0-OdGzyJCNAT6KsAdyQ"; 
	$urlserver= "https://owl.ksp-agro.com/";
}else{
	$idbot    = "@owlnotifbot";
	$token    = "1624052900:AAGAxZ7fWKOhM-6SKtMEJG7Lm0M0Cht6ZrY";
	$urlserver= "http://localhost/ksp/";
}

#didalam case jangan gunakan $val pada foreach($res as $val) karena $val sudah dipake, 

$debug = false;
$val=array();
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


set_time_limit(0);
ignore_user_abort(true);
sendApiAction($telegram_id);


#ambil data user owl
$user       = getDetailUser($val,$update);
$lokasitugas= $user['lokasitugas'];
$userowl    = $user['userowl'];
$karidowl   = $user['karidowl'];
$idpengirim = $user['idpengirim'];
$tipeorg    = $user['tipeorg'];
$kodept     = $user['kodept'];
$namakary   = $user['namakary'];
$subbagian  = $user['subbagian'];
$kodejabatan= $user['kodejabatan'];

#cuma memastikan kalau idtelegram sudah ada di table user
#jika belum terdaftar maka :
# - private = tidak bisa kirim perintah
# - group = tidak bisa kirim perintah hanya readonly
cekstatustel($val,$idpengirim);


#cek akses perintah bisa di jalankan di group atau tidak
#cek akses perintah memerlukan level admin atau tidak
cekakses($val,$userowl);


$formreg = "\n<b>REG</b> spasi <b>USER_OWL</b> spasi <b>PASS_OWL</b>\n<i>contoh : reg user.owl 123456</i>\n";
$formunreg = "\n<b>UNREG</b> spasi <b>USER_OWL</b> spasi <b>PASS_OWL</b>\n<i>contoh : unreg user.owl 123456</i>\n";

#olah pesan yg di kirim oleh user
$param= explode(" ",strtoupper($val['message']['text']));
$text = explode(" ",$val['message']['text']);
$lower= explode(" ",strtolower($val['message']['text']));

#ini khusus buat help
$tx = explode("_",strtoupper($val['message']['text']));
if($tx[0]=='/HELP'){
	$spasi= $param;
	$param= explode("_",strtoupper($val['message']['text']));
	$text = explode("_",$val['message']['text']);
	$lower= explode("_",strtolower($val['message']['text']));
	$param[8]=$spasi[1]; #/help_id read ==> /help_12 read
	$param[9]='sc';
}

#jika di group perintah biasa ada /perintah@usertelegram
#hapus @usertelegram dulu disini
$idcr=strpos($param[0],"@");
if($idcr>0){	
	$param[0] = substr($param[0],0,$idcr);
	$lower[0] = substr($lower[0],0,$idcr);
}

#pakai info alamat server atau tidak
#$msgid = false; 
$server= false;
$server= true;
		
switch($param[0]){
	case '/PENC':
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>(1)</b><i> spasi </i><b>(2)</b>
				<b>(1) = REGION</b> atau <b>KEBUN</b> atau <b>DIVISI</b>
							atau <b>KSPAGRO</b> (untuk menampilkan seluruh data region)
				<b>(2) = thn-bln</b>
				
				Contoh :
				1. ".strtoupper($lower[0])." KSPAGRO ".periodelalu(date('Y-m'))."
				2. ".strtoupper($lower[0])." SEKADAU ".periodelalu(date('Y-m'))."
				3. ".strtoupper($lower[0])." SD1E ".periodelalu(date('Y-m'))."
				4. ".strtoupper($lower[0])." SD1E01 ".periodelalu(date('Y-m'))."
				";
			break;
			default:
				$server = false;
				
				$detaillaporan = 'LAP0000004';
				$str = "select * from ".$dbname.".bi_5warnalaporan where idlap = '".$detaillaporan."' order by nilaiawal desc, nilaiakhir desc";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$arrWarna = array(); $nomor=0;
				while($bar = $res->fetch()){
					$nomor++;
					
					$arrWarna[numbertohuruf($nomor)]['opawal'] = $bar['opawal'];
					$arrWarna[numbertohuruf($nomor)]['awal'] = $bar['nilaiawal'];
					$arrWarna[numbertohuruf($nomor)]['opakhir'] = $bar['opakhir'];
					$arrWarna[numbertohuruf($nomor)]['akhir'] = $bar['nilaiakhir'];
					
					$kelasprod[numbertohuruf($nomor)]=$bar['keterangan'];
				}
				
				$periode = $param[2];
				if($periode==''){			
					$periode = periodelalu(date('Y-m'));
				}
				$tempPer = explode("-",$periode);
				$tahun   = $tempPer[0];
				$bulan   = $tempPer[1];
				
				
				$sql = "select * from ".$dbname.".bgt_regional_assignment";
				$req = fetchdata($sql);
				foreach($req as $bar){
					if(getNamaOrg(substr($bar['kodeunit'],0,4),'tipe')=='KEBUN'){		
						$region[$bar['kodeunit']]=$bar['subregional'];
						$listregion[$bar['subregional']]=$bar['subregional'];
						$listkebun[$bar['kodeunit']]=$bar['kodeunit'];
					}
				}
				
				$regionuser  = $region[$lokasitugas];
				if(getNamaOrg($param[1],'tipe')=='KEBUN'){
					$kodeorg=$param[1];
					$where=" and kodeorg like '".$kodeorg."%'";
					$jeniskirman = 'kebun';
					
					$kiriman = $param[1];
				}elseif(getNamaOrg($param[1],'tipe')=='AFDELING'){
					$kodeorg=$param[1];
					$where=" and kodeorg like '".$kodeorg."%'";
					$jeniskirman = 'divisi';
					$kiriman = $param[1];
				}elseif(substr($param[1],0,4)=='BLOK'){
					$jeniskirman = 'blok';
					$kiriman = substr($param[1],4,6);
					$where=" and kodeorg like '".$kiriman."%'";
					
				}elseif($listregion[$param[1]]!=''){
					$jeniskirman = 'region';
					$kiriman = $param[1];
				}else{
					if($param[1]==''){
						if($region[$lokasitugas]==''){
							$jeniskirman = 'kspagro';					
						}else{
							$kiriman = $region[$lokasitugas];					
							$jeniskirman = 'region';
						}
					}else{				
						$jeniskirman = 'kspagro';					
						$kiriman = $param[1];
					}
				}
				
				
				
				$sql = "select * from ".$dbname.".setup_blok where statusblok='TM' and luasareaproduktif>'0' ".$where."";
				$req = fetchdata($sql);
				foreach($req as $bar){
					if(getNamaOrg(substr($bar['kodeorg'],0,4),'inti')=='1'){		
						$listblok[$bar['kodeorg']]=$bar['kodeorg'];
						$luas[$bar['kodeorg']]=$bar['luasareaproduktif'];
						$luaskebun[substr($bar['kodeorg'],0,4)]+=$bar['luasareaproduktif'];
						$luasdivisi[substr($bar['kodeorg'],0,6)]+=$bar['luasareaproduktif'];
						$luasreg[$region[substr($bar['kodeorg'],0,4)]]+=$bar['luasareaproduktif'];
						$gtha+=$bar['luasareaproduktif'];
						
						$listkebunperreg[$region[substr($bar['kodeorg'],0,4)]][substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
						$listdivisiperkbn[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)]=substr($bar['kodeorg'],0,6);
						
					}
				}

				$prod=[];
				$sql = "select blok, sum(kgwb) as kgwb, sum(jjg) as jjg from ".$dbname.".kebun_spb_vw where tanggal like '".$periode."%' and posting='1' group by blok";
				$req = fetchdata($sql);
				foreach($req as $bar){
					$prod[$bar['blok']]+=$bar['kgwb'];
				}

				$kgsdbi="(";
				$jjgsdbi="(";
				for($i=1;$i<=intval($bulan);$i++){
					if(intval($bulan)==$i){
						$kgsdbi.="kg".addZero($i,2);
						$jjgsdbi.="jjg".addZero($i,2);
						$kgbi="kg".addZero($i,2)." as kgbi,";
						$jjgbi="jjg".addZero($i,2)." as jjgbi,";
					}else{
						$kgsdbi.="kg".addZero($i,2)."+";
						$jjgsdbi.="jjg".addZero($i,2)."+";
					}
				}
				$kgsdbi.=") as kgsdbi,";
				$jjgsdbi.=") as jjgsdbi,";

				$str = "SELECT ".$jjgbi." ".$kgbi." ".$kgsdbi." ".$jjgsdbi." kodeunit,totalkg,totaljjg, substr(kodeblok,1,6) as divisi, kodeblok as blok from " . $dbname . ".bgt_produksi_kebun where tahunbudget='".$tahun."'";
				$res = fetchdata($str);
				$bgtkgbi = $bgtkgsdbi = $jjgbi = $jjgsdbi = [];
				foreach($res as $bar){
					$bgtkgbi[$bar['blok']]+=$bar['kgbi'];
					$bgtkgsdbi[$bar['blok']]+=$bar['kgsdbi'];
				}

				$pencprod=[];
				$bloktidakpanen=$luastidakpanen=[];
				foreach($listblok as $blok){
					$reg = $region[substr($blok,0,4)];
					$kbn = substr($blok,0,4);
					$div = substr($blok,0,6);
					
					if(empty($prod[$blok])){
						$bloktidakpanen[$reg][$kbn][$div]+=1;
						$luastidakpanen[$reg][$kbn][$div]+=$luas[$blok];
					}
					
					$jumlahblok[$reg][$kbn][$div]+=$luas[$blok];
					$jumlahblokkbn[$reg][$kbn]+=$luas[$blok];
					$jumlahblokreg[$reg]+=$luas[$blok];
					$jumlahblokall+=$luas[$blok];
								
					@$pencprod[$blok]+=$prod[$blok]/$bgtkgbi[$blok]*100;
					foreach($arrWarna as $key => $row){
						if(my_operator($pencprod[$blok],$row['awal'],$row['opawal']) && my_operator($pencprod[$blok],$row['akhir'],$row['opakhir'])){
							$listpencprd[$key][$blok]=$pencprod[$blok];
							$blokpencprd[$reg][$kbn][$div][$key]+=1;
							$blokpenc[$reg][$kbn][$div]=$div;
							$luasblokpenc[$reg][$kbn][$div][$key]+=$luas[$blok];
							$kgpencprd[$key][$blok]['act']+=$prod[$blok];
							$kgpencprd[$key][$blok]['bgt']+=$bgtkgbi[$blok];
						}	
					}	
				}
				
				
				// send_reply($telegram_id, $msgid, json_encode($kgpencprd),$inline_button);
				ksort($listpencprd);
				
				$tab="<b>Pencapaian produksi berdasarkan kategori</b>\n";
				$tab.="Periode : <b>".$periode."</b>\n";
				$tab.="<b>Penjelasan :</b>\n";
				foreach($kelasprod as $kelas => $namakelas){
					$tab.="<i>Kategori <b>".$kelas."</b> : ".ucfirst(strtolower($namakelas))."</i>\n";
				}
				foreach($blokpenc as $regional => $v1){
					$noreg="";
					$tampilregion[$regional].="\n===================";
					$tampilregion[$regional].="\nRegional : <b>".$regional."</b>";
					$tampilregion[$regional].="\n===================\n";
					foreach($v1 as $kebun => $v2){
						$tampilkebun[$kebun].="\n<b>".getNamaOrg($kebun)."</b>\n";
						$nomor="";
						foreach($v2 as $divisi){
							$tampildivisi[$divisi].="\n<b>".getNamaOrg($kebun)."</b>\n";
							$tampildivisi[$divisi].="<b>".getTab2(5).getNamaOrg($divisi)."</b>\n";
							$no=0;
							foreach($kelasprod as $kelas => $namakelas){
								if($blokpencprd[$regional][$kebun][$divisi][$kelas]!=''){
									$no++;
									$persen=$luasblokpenc[$regional][$kebun][$divisi][$kelas]/$jumlahblok[$regional][$kebun][$divisi]*100;
									$tampildivisi[$divisi].=getTab2(10).$no.". <b>".$kelas."</b> = <b>".$blokpencprd[$regional][$kebun][$divisi][$kelas]."</b> Blok, <b>".$luasblokpenc[$regional][$kebun][$divisi][$kelas]."</b> Ha, <b>".hidezerodecimal($persen,2)."</b> %\n";
									
									$kebunpencprd[$regional][$kebun][$kelas]+=$blokpencprd[$regional][$kebun][$divisi][$kelas];
									$kebunluasprd[$regional][$kebun][$kelas]+=$luasblokpenc[$regional][$kebun][$divisi][$kelas];
									
									$regpencprd[$regional][$kelas]+=$blokpencprd[$regional][$kebun][$divisi][$kelas];
									$regluasprd[$regional][$kelas]+=$luasblokpenc[$regional][$kebun][$divisi][$kelas];
									
									$allpencprd[$kelas]+=$blokpencprd[$regional][$kebun][$divisi][$kelas];
									$allluasprd[$kelas]+=$luasblokpenc[$regional][$kebun][$divisi][$kelas];
								}
							}
						}
						foreach($kelasprod as $kelas => $namakelas){
							if($kebunpencprd[$regional][$kebun][$kelas]!=''){
								$nomor++;
								@$persen=$kebunluasprd[$regional][$kebun][$kelas]/$jumlahblokkbn[$regional][$kebun]*100;
								$tampilkebun[$kebun].=getTab2(5).$nomor.". <b>".$kelas."</b> = <b>".$kebunpencprd[$regional][$kebun][$kelas]."</b> Blok, <b>".$kebunluasprd[$regional][$kebun][$kelas]."</b> Ha, <b>".hidezerodecimal($persen,2)."</b> %\n";
							}
						}
					}
					
					foreach($kelasprod as $kelas => $namakelas){
						if($regpencprd[$regional][$kelas]!=''){
							$noreg++;
							@$persen=$regluasprd[$regional][$kelas]/$jumlahblokreg[$regional]*100;
							$tampilregion[$regional].=getTab2(5).$noreg.". <b>".$kelas."</b> = <b>".$regpencprd[$regional][$kelas]."</b> Blok, <b>".$regluasprd[$regional][$kelas]."</b> Ha, <b>".hidezerodecimal($persen,2)."</b> %\n";
						}
					}
				}

				$tampilall.="\n===================";
				$tampilall.="\n<b>KSP - AGRO</b>";
				$tampilall.="\n===================\n";
				foreach($kelasprod as $kelas => $namakelas){
					if($allpencprd[$kelas]!=''){
					
						$noall++;
						@$persen=$allluasprd[$kelas]/$jumlahblokall*100;
						$tampilall.=getTab2(5).$noall.". <b>".$kelas."</b> = <b>".$allpencprd[$kelas]."</b> Blok, <b>".$allluasprd[$kelas]."</b> Ha, <b>".hidezerodecimal($persen,2)."</b> %\n";
					}
				}
				
				
				
				$inline_button=[];
				switch($jeniskirman){
					case'blok':	
						$tampilanperblok[$kiriman].="\n".getTab2(15)."Actual (Ton) / ";
						$tampilanperblok[$kiriman].="Budget (Ton) = %\n";
						$tampilanperblok[$kiriman].="<b>".getNamaOrg($kiriman)."</b>\n";
						foreach($listpencprd as $kelas => $val1){
							$tampilanperblok[$kiriman].="<b>Kategori : ".getTab2(2).$kelas."</b>\n";
							$noblok="";
							foreach($val1 as $blok => $persen){
								if(substr($blok,0,6)==$kiriman){							
									$noblok++;
									$persen=$kgpencprd[$kelas][$blok]['act']/$kgpencprd[$kelas][$blok]['bgt']*100;
									$tampilanperblok[$kiriman].=getTab2(5).$noblok.". ".(getNamaOrg($blok)==$blok?substr($blok,6,4):getNamaOrg($blok))." = ".hidezerodecimal($kgpencprd[$kelas][$blok]['act']/1000,2)." <b>/</b> ".hidezerodecimal($kgpencprd[$kelas][$blok]['bgt']/1000,2)." <b>=</b> ".hidezerodecimal($persen,2)." %\n";
								}
								// $tampilanperblok[$kiriman].=getTab2(10).$noblok."\n";
							}
						}
						$message_text=$tab.$tampilanperblok[$kiriman];
					break;	
					case'divisi':	
						# lokasi kebun
						# asst divisi
						$message_text=$tab.$tampildivisi[$kiriman];
						$inline_button[][] = array(
							"text"=>"DETAIL BLOK ".getNamaOrg($kiriman),"callback_data"=>"/PENC blok".$kiriman." ".$periode
						);
						echo str_replace("\n","<br>",$message_text);
					break;
					case'kebun':
						# estate manager
						$message_text=$tab.$tampilkebun[$kiriman];
						$e=0; $jlh=count($listdivisiperkbn[$kiriman]);
						foreach($listdivisiperkbn[$kiriman] as $divisi){
							if($e==round($jlh/2)){$e=0;}
							$inline_button[$e][] = array(
								"text"=>getNamaOrg($divisi),"callback_data"=>"/PENC ".$divisi." ".$periode
							);
							$e++;
						}
						echo str_replace("\n","<br>",$message_text);
					break;
					case'region':
						# gm kebun
						$message_text=$tab.$tampilregion[$kiriman];
						$e=0; $jlh=count($listkebunperreg[$kiriman]);
						foreach($listkebunperreg[$kiriman] as $kebun){	
							if($e==round($jlh/2)){$e=0;}
							$inline_button[$e][] = array(
								"text"=>getNamaOrg($kebun),"callback_data"=>"/PENC ".$kebun." ".$periode
							);

							$e++;
						}
						
						echo str_replace("\n","<br>",$message_text);
					break;
					case'kspagro':
						# PC
						$message_text=$tab.$tampilall;
						$e=0; $jlh=count($listregion);
						foreach($listregion as $regional){	
							if($e==round($jlh/2)){$e=0;}
							$inline_button[$e][] = array(
								"text"=>$regional,"callback_data"=>"/PENC ".$regional." ".$periode
							);

							$e++;
							$message_text.=$tampilregion[$regional];
						}
						echo str_replace("\n","<br>",$message_text);
					break;
				}
				
				$inline_button[][] = array(
					"text"=>'Help',"callback_data"=>"/PENC info"
				);
			
			break;
		}
		
		$message_text.$jeniskirman;
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
		
	break;
	case '/OPENAI':
		$message_text = getOpenAI(substr($val['message']['text'],8,9999));
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case '/NNP':
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>(1)</b>
				<b>(1) = thn-bln-tgl</b>
				Contoh :
				1. ".$lower[0]." ".date("Y-m-d");
			break;
			default:
				$tglhi = tglkemarin(date("Y-m-d"));
				$artgl = explode("-",$tglhi);
				$tgldr = date("Y-".$artgl[1]."-01");
	
				if($param[1]!=''){
					$wh=$group="";
					$s = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param[1]."'";
					$r = fetchdata($s);
					foreach($r as $b){
						$nmorg=$b['kodeorganisasi'];
					}
					if($nmorg!=''){
						$wh.=" and kodeorg like '".substr($param[1],0,4)."%'";
						if($param[2]!=''){					
							$tglhi = $param[2];	
							$artgl = explode("-",$tglhi);
							$tgldr = date("Y-".$artgl[1]."-01");
						}
					}else{
						$tglhi = $param[1];
						$artgl = explode("-",$tglhi);
						$tgldr = date("Y-".$artgl[1]."-01");
					}
				}else{				
					$tglhi = $tglhi;
					$artgl = explode("-",$tglhi);
					$tgldr = date("Y-".$artgl[1]."-01");
				}
				
				$createtime=$data=array();
				$str=" select distinct kodeorg,  max(tanggal) as tanggal from ".$dbname.".pabrik_masukkeluartangki where 1=1 and tanggal between '".$tgldr."' and '".$tglhi."' ".$wh." group by kodeorg order by tanggal";
				$res = fetchdata($str);
				if(count($res)>0){
					foreach($res as $bar){
						$dtmill[$bar['kodeorg']]=$bar['kodeorg'];
						$sql=" select * from ".$dbname.".pabrik_masukkeluartangki where 1=1 and tanggal = '".$bar['tanggal']."' and kodeorg='".$bar['kodeorg']."' order by kuantitas desc";
						$req = fetchdata($sql);
						foreach($req as $key){
							if($key['kuantitas']>0){
								$data[$bar['kodeorg']][$bar['tanggal']]['cpo'][$key['kodetangki']]=$key['kuantitas'];
							}
							if($key['kernelquantity']>0){
								$data[$bar['kodeorg']][$bar['tanggal']]['ker'][$key['kodetangki']]=$key['kernelquantity'];
							}
							$createtime[$key['createtime']]=$key['createtime'];
						}
					}
					
					$tab="\n<b><u>LAPORAN STOK CPO & PK (SOUNDING)</u></b>\n";
					foreach($data as $mill => $v1){
						$tab.="\nPKS : <b>".$mill."</b> - ";
						foreach($v1 as $tanggal => $v2){
							$tab.="Tanggal : <b>".tanggalnormal($tanggal)."</b>\n";
							$no=0;
							$ttl=array();
							foreach($v2 as $jenis => $v3){
								$no++;
								$tab.="<b>".$no.". ".strtoupper($jenis)."</b>\n";
								foreach($v3 as $tangki => $jumlah){
									$nmtangki = makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan',"kodetangki='".$tangki."' and kodeorg='".$mill."'");
									$tab.=" - ".ucwords(strtolower($nmtangki[$tangki]))." = ".number_format($jumlah)." Kg\n";
									$ttl[$jenis]+=$jumlah;
									$gttl[$jenis]+=$jumlah;
								}
								$tab.="<b>    TOTAL ".strtoupper($jenis)." = ".number_format($ttl[$jenis])." Kg</b>\n";
							}
						}
					}
					
					$tab.="<b>\nGrand Total</b>";
					$tab.="<b>\n    CPO = ".number_format($gttl['cpo'])." Kg</b>";
					$tab.="<b>\n    KER = ".number_format($gttl['ker'])." Kg</b>";
					

					$tab.="<i>\n\nSumber:\n1. Pabrik - Transaksi - Stok CPO dan PK</i>\n";
					$jlh=count($dtmill);
					if($jlh>1){						
						$e=0; 
						foreach($dtmill as $divisi){
							if($e==round($jlh/3)){$e=0;}
							$inline_button[$e][]= array(
										"text"=>$divisi,"callback_data"=>$param[0]." ".$divisi." ".$tglhi
										);
							$e++;
						}
					}
					$inline_button[] = array(array("text"=>"Help","callback_data"=>$param[0]." info"));
				}else{
					$tab.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".strtolower($param[1])." ".$tglhi."\n";
					$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
				}
				
				$message_text.=$tab;
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	
	break;
	case '/CHART':
			$message_text="Form Login, Isikan Username :";
			
			$inline_button = array(array(array("text"=>"PASS","callback_data"=>"/CHART PASS")));
			$force_reply=array('force_reply'=>true,'input_field_placeholder'=>'input_field_placeholder','selective'=>true);
			
			$msgid=false; $server=false;
			force_reply($telegram_id, $msgid, $message_text, $force_reply);
			// send_reply($telegram_id, $msgid, $message_text,$inline_button);
		// try{
		// }catch($e) {			
			// $message_text="Kesalahan ".$e;
			// send_reply($telegram_id, $msgid, $message_text,$inline_button);
		// }
	break;
	case '/INLINE':
		if($param[1]=='GANTI'){
			$message_id= $val['message']['message_id'];
			$chatid    = $val['message']['chat']['id'];
			$inkeyboard = [
                [
                    ['text' => 'UPDATE', 'callback_data' => '/INLINE UPDATE 1'],
                    ['text' => 'Update 221', 'callback_data' => 'data update 2'],
                ],
                [
                    ['text' => 'keyboard on21', 'callback_data' => '!keyboard'],
                    ['text' => 'keyboard inline21', 'callback_data' => '!inline'],
                ],
                [
                    ['text' => 'keyboard off21', 'callback_data' => '!hide'],
                ],
            ];
			
			$message_text="Tidak ditemukan, silahkan coba dengan keyword lainnya";
			editMessageText($telegram_id, $msgid, $message_text, $inkeyboard, true);
		}elseif($param[1]=='UPDATE'){
			$message_text="Tidak ditemukan, silahkan coba dengan keyword lainnya";
			$inkeyboard = array(array(array("text"=>"BUKAN","callback_data"=>"/INLINE BUKAN")));
			
			editMessageText($telegram_id, $msgid, $message_text, $inkeyboard, true);
		}else{
			$message_text="Click";
			$inline_button = array(array(array("text"=>"GANTI","callback_data"=>"/INLINE GANTI")));
			send_reply($telegram_id, $msgid, $message_text,$inline_button);
		}	
	break;
	case'/HELP':
		$cari = trim(substr($val['message']['text'],5,9999));
		$cari = str_replace(" ","%",$cari);
		if($param[1]!='' and $param[9]=='sc' and $param[8]!='READ'){
			$nmmenu = makeOption($dbname,'menu','id,caption');
			$str = "select * from ".$dbname.".owlhelp where 1=1 and id = '".$param[1]."'";
			$res = fetchdata($str);
			if(count($res)>0){	
				foreach($res as $bar){				
					if(strripos($bar['penjelasan'],"<")!='' or strripos($bar['judul'],"<")!=''){
						$namafile= "imgbot/pdf/help_".$bar['id'].".pdf";
						$param   = "par=owlApp&idhelp=/help_".$param[1]."&telid=".$telegram_id."&method=jumpHelp&namafile=".$namafile;
						$tujuan  = "help_slave_show.php?".$param;
						
						$url     = $urlserver.$tujuan;     
						$opts    = array('http'=>array('header'=> "User-Agent:MyAgent/1.0\r\n")); 
						$context = stream_context_create($opts);
						$html    = file_get_contents($url,false,$context);
						$html    = htmlspecialchars($html);
						if (!file_exists($namafile)) {
							$message_text.="\n<b>Data tidak ditemukan.</b>\n";
							//send_reply($telegram_id, $msgid, $message_text,$inline_button);
						}else{
							$message_text="\n<b>Silahkan buka file terlampir.</b>\n";
							$document=$namafile;
							//sendDocument($telegram_id,$namafile);
						}
					}else{
						if($bar['namafile']!=''){
							$message_text="<b>ID</b> : /help_".$bar['id']."\n";
							$message_text.="<b>Modul</b> : <b><i>".$nmmenu[$bar['modulid']]."</i></b>\n";
							$message_text.="<b>Menu</b> : <b><i>".$nmmenu[$bar['menuid']]."</i></b>\n\n";
							$message_text.="<b>Tentang</b> : \n".$bar['judul']."\n\n";
							if($bar['penjelasan']!=''){
								$message_text.="<b>Penjelasan</b> : \n".$bar['penjelasan']."\n";
							}
							if(file_exists($bar['namafile'])){
								$document=$bar['namafile'];
								#sendDocument($telegram_id,$document);
							}else{
								$nu++;
								if($nu==1){$message_text.="Silahkan click tombol dibawah";}
								//$inline_button[] = array(array("text"=>nl2br($bar['ket']),"url"=>$bar['url']));
								$message_text.=$bar['namafile'];
							}
						}else{
							$message_text="<b>ID</b> : /help_".$bar['id']."\n";
							$message_text.="<b>Modul</b> : <b><i>".$nmmenu[$bar['modulid']]."</i></b>\n";
							$message_text.="<b>Menu</b> : <b><i>".$nmmenu[$bar['menuid']]."</i></b>\n\n";
							$message_text.="<b>Tentang</b> : \n<i>".$bar['judul']."</i>\n\n";
							$message_text.="<b>Penjelasan</b> : \n".$bar['penjelasan']."\n";					
						}
					}
				}
			}else{
				$message_text="Tidak ditemukan, silahkan coba dengan keyword lainnya";
			}
		}elseif($param[1]!='' and $param[8]=='READ'){
			$idmn  = explode(" ",$param[1]);
			$nmmenu= makeOption($dbname,'menu','id,caption');
			$idmu  = makeOption($dbname,'owlhelp','id,menuid',"id='".$idmn[0]."'");
			
			$str = "select * from ".$dbname.".owlhelp_read where 1=1 and idmenu = '".$idmu[$idmn[0]]."' order by lastdate asc";
			$res = fetchdata($str);
			if(count($res)>0){
				$message_text.="<b>Menu</b> : <b><i>".$nmmenu[$res[0]['idmenu']]."</i></b>\n";
				$message_text.="<b>Nama</b> :\n";
				foreach($res as $bar){
					$no++;
					$nmkary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
					$message_text.=$no.". ".$nmkary[$bar['karyawanid']]."; ".$bar['lastdate']."\n";
				}
			}else{
				$message_text="Data tidak ditemukan.";
			}
		}elseif($param[1]!=''){
			if($param[1]=='MODUL'){				
				$str = "select * from ".$dbname.".owlhelp where 1=1 and modulid = '".$param[2]."' order by modul asc, menuid asc";
			}else{
				$str = "select * from ".$dbname.".owlhelp where 1=1 and judul like '%".$cari."%' order by modul asc, menuid asc";
			}
			$res = fetchdata($str);
			if(count($res)>0){			
				$nmmenu = makeOption($dbname,'menu','id,caption');
				foreach($res as $bar){
					$d=$bar['modul'];
					$e=$bar['menuid'];
					if($d!=$n){						
						$message_text.="\n<b>Modul : ".$d."</b>\n";
					}
					if($e!=$r){						
						$message_text.="\n<b><u>Menu : ".$nmmenu[$e]."</u></b>\n";
					}
					$message_text.="/help_".$bar['id']." - ".$bar['judul']."\n";
					$n=$d;
					$r=$e;
				}		
			}else{
				$message_text.="Tidak ditemukan, silahkan coba dengan keyword lainnya (*).";
			}			
			
		}if(strtolower(trim($telegram_msg))=='/help'){
			$server=false;
			$message_text="Ketik /help spasi keyword pencarian\nContoh : /help <b>kas bank</b>\n\n";
			$str = "select distinct modulid, modul from ".$dbname.".owlhelp where modul!='' order by modul asc, id asc";
			$res = fetchdata($str);
			$jlh = count($res);
			if($jlh>0){						
				$message_text.="Atau click tombol dibawah ini.";
				$e=0; 
				foreach($res as $bar){
					if($e==round($jlh/2)){$e=0;}
					$inline_button[$e][]= array(
								"text"=>$bar['modul'],"callback_data"=>$param[0]." MODUL ".$bar['modulid']
								);
					$e++;
				}
			}
			
		}
		$message_text.=$param[0]."=".$param[1]."=".$param[2]."=".$param[3]."=".$param[8]."=".$param[9];
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
		if($document!=''){
			sendDocument($telegram_id,$document);
		}
	break;
	
	case'/LTBS':
		$filepdf = "imgbot/laporanpenerimaantbs.pdf";
		if(file_exists($filepdf)){
			$message_text="Terlampir kami kirimkan Laporan Penerimaan TBS\n";
			send_reply($telegram_id, $msgid, $message_text);
			sendDocument($telegram_id,$filepdf);
		}else{
			$message_text="Data tidak ditemukan.";
			send_reply($telegram_id, $msgid, $message_text);
		}
	break;
	case'/SOUNDING':
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>(1)</b><i> spasi </i><b>(2)</b>
				<b>(1) = Kosong / Mill</b>
				<b>(2) = Kosong / thn-bln-tgl</b>
				Contoh :
				1. ".$lower[0]."
				2. ".$lower[0]." SDKM
				3. ".$lower[0]." SDKM ".date("Y-m-d");
			break;
			default:
				$tglhi = tglkemarin(date("Y-m-d"));
				$artgl = explode("-",$tglhi);
				$tgldr = date("Y-".$artgl[1]."-01");
	
				if($param[1]!=''){
					$wh=$group="";
					$s = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param[1]."'";
					$r = fetchdata($s);
					foreach($r as $b){
						$nmorg=$b['kodeorganisasi'];
					}
					if($nmorg!=''){
						$wh.=" and kodeorg like '".substr($param[1],0,4)."%'";
						if($param[2]!=''){					
							$tglhi = $param[2];	
							$artgl = explode("-",$tglhi);
							$tgldr = date("Y-".$artgl[1]."-01");
						}
					}else{
						$tglhi = $param[1];
						$artgl = explode("-",$tglhi);
						$tgldr = date("Y-".$artgl[1]."-01");
					}
				}else{				
					$tglhi = $tglhi;
					$artgl = explode("-",$tglhi);
					$tgldr = date("Y-".$artgl[1]."-01");
				}
				
				$createtime=$data=array();
				$str=" select distinct kodeorg,  max(tanggal) as tanggal from ".$dbname.".pabrik_masukkeluartangki where 1=1 and tanggal between '".$tgldr."' and '".$tglhi."' ".$wh." group by kodeorg order by tanggal";
				$res = fetchdata($str);
				if(count($res)>0){
					foreach($res as $bar){
						$dtmill[$bar['kodeorg']]=$bar['kodeorg'];
						$sql=" select * from ".$dbname.".pabrik_masukkeluartangki where 1=1 and tanggal = '".$bar['tanggal']."' and kodeorg='".$bar['kodeorg']."' order by kuantitas desc";
						$req = fetchdata($sql);
						foreach($req as $key){
							if($key['kuantitas']>0){
								$data[$bar['kodeorg']][$bar['tanggal']]['cpo'][$key['kodetangki']]=$key['kuantitas'];
							}
							if($key['kernelquantity']>0){
								$data[$bar['kodeorg']][$bar['tanggal']]['ker'][$key['kodetangki']]=$key['kernelquantity'];
							}
							$createtime[$key['createtime']]=$key['createtime'];
						}
					}
					
					$tab="\n<b><u>LAPORAN STOK CPO & PK (SOUNDING)</u></b>\n";
					foreach($data as $mill => $v1){
						$tab.="\nPKS : <b>".$mill."</b> - ";
						foreach($v1 as $tanggal => $v2){
							$tab.="Tanggal : <b>".tanggalnormal($tanggal)."</b>\n";
							$no=0;
							$ttl=array();
							foreach($v2 as $jenis => $v3){
								$no++;
								$tab.="<b>".$no.". ".strtoupper($jenis)."</b>\n";
								foreach($v3 as $tangki => $jumlah){
									$nmtangki = makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan',"kodetangki='".$tangki."' and kodeorg='".$mill."'");
									$tab.=" - ".ucwords(strtolower($nmtangki[$tangki]))." = ".number_format($jumlah)." Kg\n";
									$ttl[$jenis]+=$jumlah;
									$gttl[$jenis]+=$jumlah;
								}
								$tab.="<b>    TOTAL ".strtoupper($jenis)." = ".number_format($ttl[$jenis])." Kg</b>\n";
							}
						}
					}
					
					$tab.="<b>\nGrand Total</b>";
					$tab.="<b>\n    CPO = ".number_format($gttl['cpo'])." Kg</b>";
					$tab.="<b>\n    KER = ".number_format($gttl['ker'])." Kg</b>";
					

					$tab.="<i>\n\nSumber:\n1. Pabrik - Transaksi - Stok CPO dan PK</i>\n";
					$jlh=count($dtmill);
					if($jlh>1){						
						$e=0; 
						foreach($dtmill as $divisi){
							if($e==round($jlh/3)){$e=0;}
							$inline_button[$e][]= array(
										"text"=>$divisi,"callback_data"=>$param[0]." ".$divisi." ".$tglhi
										);
							$e++;
						}
					}
					$inline_button[] = array(array("text"=>"Help","callback_data"=>$param[0]." info"));
				}else{
					$tab.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".strtolower($param[1])." ".$tglhi."\n";
					$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
				}
				
				$message_text.=$tab;
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/PRDMILL':
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>(1)</b><i> spasi </i><b>(2)</b>
				<b>(1) = Kosong / Mill</b>
				<b>(2) = Kosong / thn-bln-tgl</b>
				Contoh :
				1. ".$lower[0]."
				2. ".$lower[0]." SDKM
				3. ".$lower[0]." SDKM ".date("Y-m-d");
			break;
			default:
				$tglhi = tglkemarin(date("Y-m-d"));
				$artgl = explode("-",$tglhi);
				$tgldr = date("Y-".$artgl[1]."-01");
	
				if($param[1]!=''){
					$wh=$group="";
					$s = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param[1]."'";
					$r = fetchdata($s);
					foreach($r as $b){
						$nmorg=$b['kodeorganisasi'];
					}
					
					if($nmorg!=''){
						$wh.=" and kodeorg like '".substr($param[1],0,4)."%'";
						if($param[2]!=''){					
							$tglhi = $param[2];	
							$artgl = explode("-",$tglhi);
							$tgldr = date("Y-".$artgl[1]."-01");
						}
					}else{
						$tglhi = $param[1];
						$artgl = explode("-",$tglhi);
						$tgldr = date("Y-".$artgl[1]."-01");
					}
				}else{				
					$tglhi = $tglhi;
					$artgl = explode("-",$tglhi);
					$tgldr = date("Y-".$artgl[1]."-01");
				}
				
				$str=" select distinct kodeorg,  max(tanggal) as tanggal from ".$dbname.".pabrik_produksi where 1=1 and tanggal between '".$tgldr."' and '".$tglhi."' ".$wh." group by kodeorg order by tanggal";
				$ren = fetchdata($str);
				if(count($ren)>0){
					foreach($ren as $ban){
						$dtmill[$ban['kodeorg']]=$ban['kodeorg'];
						
						$str=" select * from ".$dbname.".pabrik_produksi  where 1=1 and tanggal between '".$tgldr."' and  '".$ban['tanggal']."' and kodeorg='".$ban['kodeorg']."'";
						$res = fetchdata($str);
						foreach($res as $bar){
							$data[$bar['kodeorg']]=$bar['kodeorg'];
							$tglmill[$bar['kodeorg']]=$ban['tanggal'];
							if($bar['tanggal']==$ban['tanggal']){
								$tbsm[$bar['kodeorg']]['hi']+=$bar['tbsmasuk'];
								$tbso[$bar['kodeorg']]['hi']+=$bar['tbsdiolah'];
								$cpo[$bar['kodeorg']]['hi']+=$bar['oer'];
								$ffa[$bar['kodeorg']]['hi']=$bar['ffa'];
								$air[$bar['kodeorg']]['hi']=$bar['kadarair'];
								$kot[$bar['kodeorg']]['hi']=$bar['kadarkotoran'];
								
								$pk[$bar['kodeorg']]['hi']+=$bar['oerpk'];
								$airpk[$bar['kodeorg']]['hi']=$bar['kadarairpk'];
								$kotpk[$bar['kodeorg']]['hi']=$bar['kadarkotoranpk'];
							}
							$tbsm[$bar['kodeorg']]['shi']+=$bar['tbsmasuk'];
							$tbso[$bar['kodeorg']]['shi']+=$bar['tbsdiolah'];
							$cpo[$bar['kodeorg']]['shi']+=$bar['oer'];
							$ffa[$bar['kodeorg']]['shi']=$bar['ffa'];
							$air[$bar['kodeorg']]['shi']=$bar['kadarair'];
							$kot[$bar['kodeorg']]['shi']=$bar['kadarkotoran'];
							
							$pk[$bar['kodeorg']]['shi']+=$bar['oerpk'];
							$airpk[$bar['kodeorg']]['shi']=$bar['kadarairpk'];
							$kotpk[$bar['kodeorg']]['shi']=$bar['kadarkotoranpk'];
							
							$createtime[$bar['createtime']]=$bar['createtime'];
						}
					}
					$tab="<b>Laporan Produksi PKS (CPO dan PK)</b>\n";
					foreach($data as $millcode){
					$tab.="\n<b>PKS : ".$millcode."</b> - Tanggal : <b>".tanggalnormal($tglmill[$millcode])."</b>\n";
					$tab.="   <b>1. TBS</b> :
					   ".getTab("Masuk <i>(t)</i> hi",20).": ".number_format($tbsm[$millcode]['hi']/1000,2)."; sdhi: ".number_format($tbsm[$millcode]['shi']/1000,2)."
					   ".getTab("Olah <i>(t)</i> hi",22).": ".number_format($tbso[$millcode]['hi']/1000,2)."; sdhi: ".number_format($tbso[$millcode]['shi']/1000,2)."\n";
					$tab.="   <b>2. CPO</b> :
					   ".getTab("Jlh <i>(t)</i> hi",25).": ".number_format($cpo[$millcode]['hi']/1000,2)."; sdhi: ".number_format($cpo[$millcode]['shi']/1000,2)."
					   ".getTab("Oer <i>(%)</i> hi",21).": ".number_format(bagi($cpo[$millcode]['hi'],$tbso[$millcode]['hi'])*100,2)."; sdhi: ".number_format(bagi($cpo[$millcode]['shi'],$tbso[$millcode]['shi'])*100,2)."
					   ".getTab("Ffa <i>(%)</i> hi",23).": ".number_format($ffa[$millcode]['hi'],2)."; sdhi: ".number_format($ffa[$millcode]['shi'],2)."
					   ".getTab("Dirt <i>(%)</i> hi",22).": ".number_format($kot[$millcode]['hi'],2)."; sdhi: ".number_format($kot[$millcode]['shi'],2)."
					   ".getTab("Moist <i>(%)</i> hi",20).": ".number_format($air[$millcode]['hi'],2)."; sdhi: ".number_format($air[$millcode]['shi'],2)."\n";
					$tab.="   <b>3. KERNEL</b> :
					   ".getTab("Jlh <i>(t)</i> hi",26).": ".number_format($pk[$millcode]['hi']/1000,2)."; sdhi: ".number_format($pk[$millcode]['shi']/1000,2)."
					   ".getTab("Ker <i>(%)</i> hi",22).": ".number_format(bagi($pk[$millcode]['hi'],$tbso[$millcode]['hi'])*100,2)."; sdhi: ".number_format(bagi($pk[$millcode]['shi'],$tbso[$millcode]['shi'])*100,2)."
					   ".getTab("Dirt <i>(%)</i> hi",22).": ".number_format($kotpk[$millcode]['hi'],2)."; sdhi: ".number_format($kotpk[$millcode]['shi'],2)."
					   ".getTab("Moist <i>(%)</i> hi",20).": ".number_format($airpk[$millcode]['hi'],2)."; sdhi: ".number_format($airpk[$millcode]['shi'],2)."\n";   
					}
					
					$tab.="<i>\nSumber:\n1. Pabrik - Transaksi - Produksi Harian</i>\n";
					
					
					$jlh=count($dtmill);
					if($jlh>1){						
						$e=0; 
						foreach($dtmill as $divisi){
							if($e==round($jlh/3)){$e=0;}
							$inline_button[$e][]= array(
										"text"=>$divisi,"callback_data"=>$param[0]." ".$divisi." ".$tglhi
										);
							$e++;
						}
					}
					$inline_button[] = array(array("text"=>"Help","callback_data"=>$param[0]." info"));
				}else{
					$tab.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".strtolower($param[1])." ".$tglhi."\n";
					$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
				}
				
				$message_text.=$tab;
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/GRAPH':
		$folder = "imgbot/";
		if (!file_exists($folder)) {
			mkdir($folder, 0777, true);
		}
		switch($param[1]){
			default:
				$inline_button=array(
					array(array(
							"text"=>"Daily Production (Region)",
							"callback_data"=>"/GRAPH REGION")),
					array(array(
							"text"=>"Daily Production (Estate)",
							"callback_data"=>"/GRAPH KEBUN"))
						);
			break;
			case'DELETE':
				// if($folder!=''){					
					// $files = glob($folder.'*'); 
					// foreach($files as $file) {
						// if(is_file($file)) 
							// unlink($file);
					// }
				// }
			break;
			case'KEBUN':
				$str = "select * from ".$dbname.".organisasi where inti='1' and tipe='KEBUN'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$arrunit[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
				}
				$e=0; $jlh=count($arrunit);
				if($jlh>0){
					foreach($arrunit as $bar){
						if($e==round($jlh/2)){$e=0;}
						$inline_button[$e][]= array(
									"text"=>$bar,"callback_data"=>$param[0]." PRD ".$bar
								);
						$e++;
					}
				}
			break;
			case'REGION':
				$inline_button=
					array(
						array(
							array(
								"text"=>"Sekadau",
								"callback_data"=>"/GRAPH PRD SEKADAU"
							),
							array(
								"text"=>"Bonti",
								"callback_data"=>"/GRAPH PRD BONTI"
							)
						),
						array(
							array(
								"text"=>"Kapuas",
								"callback_data"=>"/GRAPH PRD KAPUAS"
							),
							array(
								"text"=>"Kalteng",
								"callback_data"=>"/GRAPH PRD KALTENG"
							)
						),
						array(
							array(
								"text"=>"KSP Group",
								"callback_data"=>"/GRAPH PRD KSP-GROUP"
							)
						)
					);
			break;
			case'PRD':
				$filepdf=$folder."Daily_Prod_Report_".$param[2].".pdf";
				if (!file_exists($filepdf)) {
					$param   = "unit=".$param[2];
					$tujuan  = "bot_graph.php?".$param;
					
					$url     = $urlserver.$tujuan;     
					$opts    = array('http'=>array('header'=> "User-Agent:MyAgent/1.0\r\n")); 
					$context = stream_context_create($opts);
					$html    = file_get_contents($url,false,$context);
					$html    = htmlspecialchars($html);
					
					sendDocument($telegram_id,$filepdf);
					//$message_text.="\n<b>Data tidak ditemukan.</b>\n";
				}else{					
					sendDocument($telegram_id,$filepdf);
				}
				
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/NOTIF':
		switch($param[1]){
			case'YAKINDAFTAR':
				$str = "select * from ".$dbname.".setup_notification_ht where status='1'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$nama[$bar['kodejenis']]=$bar['namajenis'];
				}
				
				$str = "select * from ".$dbname.".setup_notification_dt where karyawanid='".$karidowl."' and kodejenis='".$param[2]."'";
				$res = fetchdata($str);
				if(count($res)>0){
					$e="#UNREG"; $n="Berhenti (Ya)";
					$message_text.="\n<b>Anda telah terdaftar, ingin berhenti ???</b>\n";
				}else{
					$e="#DAFTAR"; $n="Mendaftar (Ya)";
					$message_text.="\n<b>Ingin mendapatkan notifikasi ???</b>\n";
				}
				
				$message_text.="<b>".$param[2]." - ".$nama[$param[2]]."</b>\n";
				$inline_button=array(array(array(
							"text"=>$n,
							"callback_data"=>"/NOTIF ".$e." ".$param[2]
						),
						array(
							"text"=>"Batalkan",
							"callback_data"=>"/NOTIF"
						)
					));
			break;
			case'#DAFTAR':
				$str="insert into ".$dbname.".setup_notification_dt (kodejenis,karyawanid,tipe,telegram,status)
				values('".$param[2]."','".$karidowl."','0','1','".date("Y-m-d H:i:s")."')";
				try{$owlPDO->exec($str);
				$message_text.="\n<b>Pendaftaran Berhasil...</b>\n";
				$inline_button=array(array(array(
							"text"=>"Back",
							"callback_data"=>"/NOTIF"
						)));
				}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
			break;
			case'#UNREG':
				$query = "delete from `".$dbname."`.`setup_notification_dt` where kodejenis = '".$param[2]."' and karyawanid='".$karidowl."'";
				try{$owlPDO->exec($query);
				$message_text.="\n<b>Berhenti langganan Berhasil...</b>\n";
				$inline_button=array(array(array(
							"text"=>"Back",
							"callback_data"=>"/NOTIF"
						)));
				}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
			break;
			default:
				$rekomended=array('APPROVAL','ATBS');
				$str = "select * from ".$dbname.".setup_notification_ht where status='1' and kodejenis!='HTB' order by id asc";
				$res = fetchdata($str);
				$message_text.="<b>MENDAFTAR UNTUK MENDAPATKAN NOTIFIKASI :</b>\n";
				$message_text.="\n* = Recommended\n";
				foreach($res as $bar){
					$rek="";
					if(in_array($bar['kodejenis'],$rekomended)){
						$rek=" *";
					}
					$no++;
					$inline_button[][]=array(
							"text"=>$no.". ".$bar['namajenis']." ".$rek,
							"callback_data"=>"/NOTIF YAKINDAFTAR ".$bar['kodejenis']
					);
				}
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/SARAN':
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik :\n".$lower[0]." <i>spasi</i> <b>isi saran anda</b>\n";
			break;
			case'#LIHAT':
				$message_text.="\n<b>Daftar saran yng masuk :</b>\n";
				$str = "select * from ".$dbname.".tel_activity where text='/SARAN' and register ='REG' and full_text not like '/saran info%' and full_text not like '/saran #lihat%' and length(full_text)>6";
				$res = fetchdata($str);
				foreach($res as $bar){
					$no++;
					$message_text.=$no.". ".$bar['username']." => ".$bar['waktu']." => ".$bar['full_text']."\n\n";
				}
			break;
			default:
				if($param[1]!=''){
					$message_text.="Terima kasih saran dan masukkan anda telah kami terima.\n";
				}else{				
					$message_text.="Silahkan ketik saran dan masukkan anda dengan format :\n ".$lower[0]." <i>spasi</i> <b>isi saran anda</b>\n";
				}
				$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/APPROVAL':
		$str = "select * from ".$dbname.".setup_jenisapproval";
		$res = fetchdata($str);
		foreach($res as $bar){
			$namaapp[$bar['jenis']]=$bar['nama'];
		}
		switch($param[1]){
			case'DETAIL':
				$str = "select * from ".$dbname.".approval where karyawanid='".$karidowl."' and status='0' and jenispersetujuan='".$param[2]."'";
				$res = fetchdata($str);
				if(count($res)>0){
					$message_text.="Jenis Approval / Persetujuan :\n<b>".$namaapp[$param[2]]."</b>\n";
					$message_text.="\n<b>Click untuk melihat detail transaksi.</b>";
					foreach($res as $bar){
						$no++;
						#$message_text.=$no.". ".$bar['notransaksi']."\n";
						$inline_button[][]=array(
								"text"=>$no.". ".$bar['notransaksi'],
								"callback_data"=>"/APPROVAL TRANS ".$param[2]." ".$bar['notransaksi']
						);
					}
				}else{
					$message_text.="\n<b>Data tidak ditemukan.</b>\n";
				}
			break;
			case'TRANS':
				$nmapprov=makeOption($dbname,'setup_jenisapproval','jenis,nama');
				$folder = "imgbot/pdf/";
				$namafile=str_replace("/","",$param[3]);
				$namafile=str_replace("-","",$namafile);
				$filepdf =$folder.$param[2]."_".$namafile.".pdf";
				#$param[0] = /APPROVAL
				#$param[1] = TRANS
				#$param[2] = Jenis Approval
				#$param[3] = Notransaksi
				if($param[2]=='MB'){
					$str = "select * from ".$dbname.".log_5masterbarang where kodebarang='".$param[3]."'";
					$res = fetchdata($str);
					foreach($res as $bar){						
						$nmkel=makeOption($dbname,'log_5klbarang','kode,kelompok',"kode='".$bar['kelompokbarang']."'");
						
						$message_text.="Kelompok Barang : <b>".$nmkel[$bar['kelompokbarang']]."</b>\n";
						$message_text.="Kode Barang : <b>".$bar['kodebarang']."</b>\n";
						$message_text.="Nama Barang : <b>".$bar['namabarang']."</b>\n";
						$message_text.="Satuan Barang : <b>".$bar['satuan']."</b>\n";
					}
				}elseif(!file_exists($filepdf)){
					$message_text.="\n<b>Data tidak ditemukan.</b>\n";
					$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,ip,karyawanid,type,text,full_text)
					values('".$user['userowl']."','REG','".$idpengirim."','".$_SERVER['PHP_SELF']."','','".$user['karidowl']."','private','/SARAN','/SARAN pdf approval jenis ".$param[2]." belum ada, sumber ".$param[0]." ".$param[1]." ".$param[2]." ".$param[3]."')";
					try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}else{
					sendDocument($telegram_id,$filepdf);
					
					// $msgid =false;
					// $server=false;
					// $message_text="<b>Silahkan click tombol dibawah untuk menindak lanjuti</b>";
					// $inline_button[]=array(
										// array(
												// "text"=>"Approve",
												// "callback_data"=>"/APPROVAL #ACTION APPROVE ".$param[2]." ".$param[3]
											// ),
										// array(
												// "text"=>"Reject",
												// "callback_data"=>"/APPROVAL #ACTION REJECT ".$param[2]." ".$param[3]
											// )
									// );
					
				}
			break;
			case'#ACTION':
				#$param[0] = /APPROVAL
				#$param[1] = #ACTION
				#$param[2] = APPROVE or REJECT
				#$param[3] = Jenis Approval
				#$param[4] = Notransaksi
				switch($param[2]){
					case'APPROVE':
						switch($param[3]){
							case'PJDSTF':
							case'PJDNSTF':
							
							break;
						}
					break;
					case'REJECT':
					break;
				}

				$message_text="<b>Mohon maaf action saat ini belum tersedia.</b>";
			break;
			default:			
				$str = "select count(notransaksi) as jlh, jenispersetujuan from ".$dbname.".approval where karyawanid='".$karidowl."' and status='0' group by jenispersetujuan";
				$res = fetchdata($str);
				if(count($res)>0){					
					$message_text.="\n<b>Click untuk melihat detail</b>\n";
					foreach($res as $bar){
						$inline_button[][]=array(
								"text"=>$namaapp[$bar['jenispersetujuan']]." (".$bar['jlh'].")",
								"callback_data"=>"/APPROVAL DETAIL ".$bar['jenispersetujuan']
						);
					}
					$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
				}else{
					$message_text.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$lower[1]." ".$lower[2]." ".$lower[3]."\n";
					$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
				}
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/TEST':
		#versi vertical (row)
		$inline_button = array(
							array(
								array("text"=>"Help","callback_data"=>$lower[0]." info")
							),
							array(
								array("text"=>"Google url","url"=>"http://google.com")
							),
							array(
								array("text"=>"tbs info","callback_data"=>'/tbs info')
							),
							array(
								array("text"=>"tbs info test tes","callback_data"=>'/tbs info'),
								array("text"=>"tbs info test tes","callback_data"=>'/tbs info'),
								array("text"=>"tbs info test tes","callback_data"=>'/tbs info'),
								array("text"=>"tbs info test tes","callback_data"=>'/tbs info')
							)
							
						);
		
		
		#versi horisontal (kolom)
		// $inline_button = array(
							// array(
								// array(
									// "text"=>"Google url",
									// "url"=>"http://google.com"
								// ),
								// array(
									// "text"=>"tbs info",
									// "callback_data"=>'/tbs info'
								// )
							// )
						// );
		send_reply($telegram_id, $msgid, $message_text,$inline_button);				
	break;
	case'SQL':
		switch($param[1]){
			case'INFO':
				$message_text.="<b>sql</b> <i>select namakaryawan,nik from datakaryawan where lokasitugas='SD1E'</i>\n";
				$message_text.="<b>sql</b> <i>select namakaryawan as <b>nama</b>, nik from datakaryawan where lokasitugas='SD1E'</i>\n";
			break;
			case'SELECT':
				$post=$val['message']['text'];

				$sel=strpos($post,"select");
				$frm=strpos($post,"from");
				$tab = substr($post,$frm);
				$tbl = explode(" ",$tab);
				$table = $tbl[1];



				$slct  = substr($post,$sel+6,$frm-($sel+6));
				$select= explode(",",$slct);
				$dicari= " as ";
				
				#jaga2 kalau ada alias
				foreach($select as $key => $colom){	
					if(preg_match("/$dicari/i", $colom)){
						$len=strlen($colom);
						$pss=strpos($colom,$dicari);
						$select[$key]=trim(substr($colom,$pss+4,$len-($pss+4)));
					}else{
						$select[$key]=trim($colom);
					}
				}
				if(trim($select[0])=='*'){					
					$select = getKolomName($table,'array');
				}
				
				$query = substr($post,4,strlen($post)-4)." limit 50;";
				$res = fetchdata($query);
				if(count($res)>0){
					foreach($select as $colom){						
						$message_text.="<b>".trim($colom)."; </b>";
					}
					$message_text.="\n";
					foreach($res as $bar){
						foreach($select as $colom){						
							$message_text.=" ".$bar[trim($colom)]." ;";
						}
						$message_text.="\n";
						$message_text.="\n";
					}
					$message_text.="\nlimit 50";
				}else{
					$message_text.="Data tidak ditemukan.";				
				}
			break;
			default;
				$message_text="Perintah tidak tersedia.";				
			break;
		}
		$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
		
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/BKMPOST':
		switch($param[1]){
			case'YAKINPOSTPNN':
				$aksesorg=orgDetail($userowl);
				$s = "select * from ".$dbname.".kebun_aktifitas where notransaksi='".$param[2]."' and tipetransaksi='PNN'";
				$r = fetchdata($s);
				if($aksesorg[substr($r[0]['kodeorg'],0,4)]==''){
					$message_text.="\nAnda tidak memiliki otorisasi ke ".substr($r[0]['kodeorg'],0,4)."\n";
				}else{							
					$message_text.="\n\n<b>Posting data ".$param[2]." ???</b>\n";
					$inline_button=array(array(
						array(
							"text" => "Anda Yakin ??? (YA)",
							"callback_data"  =>"/BKMPOST POSTPNN ".$param[2]),
						array(
							"text" => "Batal",
							"callback_data"  =>"/BKM ".$r[0]['kodeorg']." ".$r[0]['tanggal'])
						)
					);
				}
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, true);
			break;
			case'POSTPNN':
				$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".$param[2]."'");
				$dataH = fetchData($queryH);

				$queryD = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".$param[2]."'");
				$dataD = fetchData($queryD);
				
				if(count($dataH)==0) {
					$message_text.="\nNomor transaksi salah.\n";
				}elseif(count($dataD)==0) {
					$message_text.="\nDetail transaksi tidak ada.\n";
				}elseif($dataH[0]['jurnal']=='1'){
					$message_text.="\nTransaksi sudah pernah di Posting.\n";
					$inline_button=array(array(
						array(
							"text" => "Kembali ke List BKM",
							"callback_data"  =>"/BKM ".substr($dataD[0]['kodeorg'],0,6)." ".$dataH[0]['tanggal']." # # EDITMESSAGE")
						)	
					);

				}else{					
					$strupd=" update ".$dbname.".kebun_aktifitas set jurnal='1' where notransaksi='".$param[2]."' and tipetransaksi='PNN'";
					try{
						$owlPDO->exec($strupd);
						$message_text.="\nPosting Sukses.\n";
					
						$inline_button=array(array(
							array(
								"text" => "Kembali ke List BKM",
								"callback_data"  =>"/BKM ".substr($dataD[0]['kodeorg'],0,6)." ".$dataH[0]['tanggal']." # # EDITMESSAGE")
							)	
						);
					
					}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
			break;
			case'POST':
				$str = "select * from ".$dbname.".setup_posting where kodeaplikasi='rawatkebun' and jabatan='".$kodejabatan."'";
				$res = fetchdata($str);
				if(count($res)>0){					
					$s = "select * from ".$dbname.".kebun_aktifitas where notransaksi='".$param[2]."' and tipetransaksi!='PNN'";
					$r = fetchdata($s);
					if(count($r)>0){
						$aksesorg=orgDetail($userowl);
						if($aksesorg[substr($r[0]['kodeorg'],0,4)]==''){
							$message_text.="\nAnda tidak memiliki otorisasi ke ".substr($r[0]['kodeorg'],0,4)."\n";
						}else{							
							$q = "select substr(kodeorg,1,6) as divisi from ".$dbname.".kebun_prestasi where notransaksi='".$param[2]."'";
							$n = fetchdata($q);
							
							$t  = "select sum(umr+premi+hk) as jlh from ".$dbname.".sdm_absensidt where norefrensi='".$param[2]."' and nobkm='".$r[0]['nobkm']."'"; 
							$p  = fetchdata($t);
							$ttl= $resn[0]['jlh'];
							
							$message_text.="\nPosting data ".$param[2]." ?\n";
							if(count($n)==0 and $ttl>0){
								$param        = "par=owlApp&notransaksi=".$r[0]['notransaksi']."&telid=".$telegram_id."&method=postingabsensi";
								$tujuan       = "kebun_slave_bkm.php?".$param;
								$url          = $urlserver.$tujuan;
							}else{
								$param        = "par=owlApp&notransaksi=".$r[0]['notransaksi']."&telid=".$telegram_id;
								$tujuan       = "kebun_slave_operasional_postingx.php?".$param;
								$url          = $urlserver.$tujuan;
							}
							$opts         = array('http'=>array('header'=> "User-Agent:MyAgent/1.0\r\n")); 
							$context      = stream_context_create($opts);
							$html         = file_get_contents($url,false,$context);
							//$html       = htmlspecialchars($html);								
							
							$message_text.="\n<b>".$html."</b>\n";
						}
					}else{				
						$message_text.="\nNomor transaksi salah.\n";
					}
				}else{
					$message_text.="\nAnda tidak memiliki otorisasi untuk Posting.\n";
				}
				
				$inline_button=array(array(
						array(
							"text" => "Kembali ke List BKM",
							"callback_data"  =>"/BKM ".substr($n[0]['divisi'],0,6)." ".$r[0]['tanggal']." # # EDITMESSAGE")
						)
					);
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
				//send_reply($telegram_id, $msgid, $message_text,$inline_button);
			break;
			case'YAKINPOST':
				$aksesorg=orgDetail($userowl);
				$s = "select * from ".$dbname.".kebun_aktifitas where notransaksi='".$param[2]."' and tipetransaksi!='PNN'";
				$r = fetchdata($s);
				if($aksesorg[substr($r[0]['kodeorg'],0,4)]==''){
					$message_text.="\nAnda tidak memiliki otorisasi ke ".substr($r[0]['kodeorg'],0,4)."\n";
				}else{							
					$message_text.="\n\n<b>Posting data ".$param[2]." ???</b>\n";
					$inline_button=array(array(
						array(
							"text" => "Anda Yakin ??? (YA)",
							"callback_data"  =>"/BKMPOST POST ".$param[2]),
						array(
							"text" => "Batal",
							"callback_data"  =>"/BKM ".$r[0]['kodeorg']." ".$r[0]['tanggal'])
						)
					);
				}
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, true);
			break;
			case'INFO':
				$message_text.="\nKetik :\n".$lower[0]." <i>spasi</i> <b>(1)</b> <i>spasi</i> <b>(2)</b>
				<b>(1)</b> = <b>Kosong / Tgl / Unit / Div</b>
				<b>(2)</b> = <b>Kosong / Tgl</b>
				Contoh :
				1. ".$lower[0]."
				2. ".$lower[0]." ".date("Y-m-d")."
				3. ".$lower[0]." ".strtolower($lokasitugas)."
				4. ".$lower[0]." ".strtolower($lokasitugas)."01 ".date("Y-m-d")."\n
				";
				send_reply($telegram_id, $msgid, $message_text,$inline_button);
			break;
			default:
				if($tipeorg!='KEBUN'){
					$server=false;
					$message_text="Hanya untuk tipe organisasi <b>KEBUN</b>, sementara tipe organisasi anda adalah <b>".$tipeorg."</b> silahkan pindah ke <b>KEBUN</b> terlebih dahulu.";
					//send_reply($telegram_id, $msgid, $message_text,$inline_button);
				}else{					
					if($param[1]!=''){
						$s = "select * from ".$dbname.".organisasi where kodeorganisasi='".substr($param[1],0,4)."'";
						$r = fetchdata($s);
						foreach($r as $b){
							$nmorg=$b['kodeorganisasi'];
						}
						if($nmorg!=''){
							$aksesorg=orgDetail($userowl);
							if($aksesorg[substr(strtoupper($param[1]),0,4)]==''){
								$message_text.="\nAnda tidak memiliki otorisasi ke ".substr(strtoupper($param[1]),0,4)."\n";
								$wh.=" and a.kodeorg = '".$lokasitugas."'"; $lokunit=$lokasitugas;
							}else{						
								$wh.=" and a.kodeorg = '".substr($param[1],0,4)."'"; $lokunit=substr($param[1],0,4);
							}
							if(strlen($param[1])=='6'){
								$wh.=" and substr(b.kodeorg,1,6) = '".$param[1]."'"; $lokunit=$param[1];
							}
							
							$info=substr($param[1],0,4);
							if($param[2]!=''){					
								if(strlen($param[2])!='10'){
									$message_text.="\ninfo : Format tanggal salah, format yg benar yyyy-mm-dd, contoh ".date("Y-m-d")."\n\n";
									$tglhi = date("Y-m-d");
								}else{								
									$tglhi = $param[2];
								}
							}else{					
								$tglhi = date("Y-m-d");
							}
						}else{
							$wh.=" and a.kodeorg = '".$lokasitugas."'";  $lokunit=$lokasitugas;
							$info=$lokasitugas;
							if(strlen($param[1])!='10'){
								$message_text.="\ninfo : Format tanggal salah, format yg benar yyyy-mm-dd, contoh ".date("Y-m-d")."\n\n";
								$tglhi = date("Y-m-d");
							}else{								
								$tglhi = $param[1];
							}
						}
					}else{				
						$wh.=" and a.kodeorg = '".$lokasitugas."'";  $lokunit=$lokasitugas;
						$info=$lokasitugas;
						$tglhi = date("Y-m-d");
					}
					
					$str = "SELECT a.*, a.tipetransaksi as tipetransaksi, substr(b.kodeorg,1,6) as divisi, sum(b.hasilkerja) as jjg, sum(b.jumlahhk) as hk, sum(b.upahpremi) as premi FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1  and tanggal = '".$tglhi."' ".$wh." group by a.notransaksi order by divisi asc,a.tipetransaksi asc, a.notransaksi asc";
					$data=array(); $allpost=0;
					$res=fetchdata($str);
					foreach($res as $bar){
						if($bar['jurnal']==0){						
							$strn = "select sum(umr+premi) as nilai from ".$dbname.".sdm_absensidt where norefrensi='".$bar['notransaksi']."' and nobkm='".$bar['nobkm']."'"; 
							$resn = fetchdata($strn);
							if($bar['divisi']=='' and $resn[0]['nilai']>0){
								$abs="absensi";
							}else{
								$abs="bkm";
							}
							if($bar['divisi']==''){
								$bar['divisi']='Absensi';
							}
							$data[$bar['divisi']][$bar['notransaksi']]=$bar['tipetransaksi'];
						}elseif($bar['jurnal']==1){
							$allpost++;
						}					
					}
					if(count($data)>0){
						$tab.="BKM ".$info." Tanggal : ".$tglhi."\n";
						foreach($data as $divisi => $key){
							foreach($key as $notran => $tipetrans){
								if($tipetrans=='PNN'){
									$tipepost="YAKINPOSTPNN";
								}else{
									$tipepost="YAKINPOST";
								}
								$no++;
								$inline_button[][]=array(
										"text"=>$divisi." - ".$notran,
										"callback_data" => "/BKMPOST ".$tipepost." ".$notran
								);
							}
						}
						$message_text.=$tab;
					}elseif($allpost==count($res) and count($res)>0){
						$message_text.="BKM seluruhnya telah terposting.\n";
					}else{
						$str = "SELECT distinct tanggal FROM " . $dbname . ".kebun_aktifitas a where 1=1 ".$wh." and jurnal='0' order by tanggal asc limit 20";
						$res = fetchdata($str);
						if(count($res)>0){							
							if($subbagian!=''){
								$lokunit=$subbagian;
							}else{
								$lokunit=$lokunit;
							}
							$e=0; 
							foreach($res as $bar){
								if($e==round(count($res)/2)){$e=0;}
								$inline_button[$e][]= array(
											"text"=>$bar['tanggal'],"callback_data"=>$param[0]." ".$lokunit." ".$bar['tanggal']." # # EDITMESSAGE"
											);
								$e++;
							}
							$server=false;
							$message_text="Silahkan pilih tanggal BKM\n";
						}else{
							$message_text="BKM untuk unit ".$lokunit." telah diposting seluruhnya.\n";
						}
					}
				}
				
				if($param[5]=='EDITMESSAGE'){
					editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
				}else{					
					send_reply($telegram_id, $msgid, $message_text,$inline_button);
				}
			break;
		}
	break;
	case'/BKM':
		$s = "select * from ".$dbname.".organisasi";
		$r = fetchdata($s);
		foreach($r as $b){
			$nmorganisasi[$b['kodeorganisasi']]=$b['namaorganisasi'];
		}
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik :\n".$lower[0]." <i>spasi</i> <b>(1)</b> <i>spasi</i> <b>(2)</b>
				<b>(1)</b> = <b>Kosong / Tgl / Unit / Div / det</b>
				<b>(2)</b> = <b>Kosong / Tgl / notransaksi</b>
				Contoh :
				1. ".$lower[0]."
				2. ".$lower[0]." ".date("Y-m-d")."
				3. ".$lower[0]." ".strtolower($lokasitugas)."
				4. ".$lower[0]." ".strtolower($lokasitugas)."01 ".date("Y-m-d")."
				5. ".$lower[0]." det ".date("Ymd")."/".$lokasitugas."/TM/001
				Catatan : <b>notransaksi</b> tidak sama dengan <b>No BKM</b>\n";
				send_reply($telegram_id, $msgid, $message_text,$inline_button);	
			break;
			case'PREMIPNN':
				$str="select * from ".$dbname.".kebun_3premipemanen where divisi='".$param[2]."' and tanggalpanen='".$param[3]."' and mandor='".$param[4]."'";
				$res=fetchdata($str);
				if(count($res)>0){
					$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$param[4]."'");
					$message_text="<b>Upah dan Premi Panen</b>\n";
					$message_text.="Notrans : <b>".$res[0]['notransaksi']."</b>\n";
					$message_text.="Divisi : <b>".$param[2]."</b>\n";
					$message_text.="Tanggal : <b>".$param[3]."</b>\n";
					$message_text.="Posting : <b>".($res[0]['posting']=='1'?'Posted':'Blm Posting')."</b>\n";
					$message_text.="Mandor : <b>".$optnm[$param[4]]."</b>\n";
					$message_text.="----------------------------\n";
					foreach($res as $bar){
						$divisi=$bar['divisi'];
						$data[$bar['karyawanid']][$bar['blok']]=$bar['blok'];
						$jjgpanen[$bar['karyawanid']][$bar['blok']]+=$bar['jjgpanen'];
						$kgwb[$bar['karyawanid']][$bar['blok']]+=$bar['kgwb'];
						$rplb1[$bar['karyawanid']][$bar['blok']]+=$bar['rplb1'];
						$kehadiran[$bar['karyawanid']][$bar['blok']]+=$bar['kehadiran'];
						$kgbrd[$bar['karyawanid']][$bar['blok']]+=$bar['kgbrd'];
						$potbrdkg[$bar['karyawanid']][$bar['blok']]+=$bar['potbrdkg'];
						$rpbrd[$bar['karyawanid']][$bar['blok']]+=$bar['rpbrd'];
						$denda[$bar['karyawanid']][$bar['blok']]+=$bar['denda'];
						
						$ttl['jjg']+=$bar['jjgpanen'];
						$ttl['kg']+=$bar['kgwb'];
						$ttl['rp']+=$bar['rplb1'];
						$ttl['hdr']+=$bar['kehadiran'];
						$ttl['brd']+=$bar['rpbrd'];
						$ttl['pen']+=$bar['denda'];
					}
					
					foreach($data as $kary => $vblok){
						$totalorg[$kary]=0;
						$no++;
						$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kary."'");
						$message_text.="\n<b>".$no.". ".$optnm[$kary]."</b>\n";
						foreach($vblok as $blok){
							$message_text.=$nmorganisasi[$blok].", ";
							$message_text.="Jjg: <b>".hidezerodecimal($jjgpanen[$kary][$blok])."</b>, ";
							$message_text.="Kg: <b>".hidezerodecimal($kgwb[$kary][$blok])."</b>\n";
							$message_text.="Rp: <b>".hidezerodecimal($rplb1[$kary][$blok])."</b>, ";
							$message_text.="Kehadiran: <b>".hidezerodecimal($kehadiran[$kary][$blok])."</b>\n";
							if($kgbrd[$kary][$blok]>0){								
								$message_text.="Brd Kg: <b>".hidezerodecimal($kgbrd[$kary][$blok])."</b>, ";
								$message_text.="Pot Brd Kg: <b>".hidezerodecimal($potbrdkg[$kary][$blok])."</b>\n";
								$message_text.="Brd Rp: <b>".hidezerodecimal($rpbrd[$kary][$blok])."</b>\n";
							}
							if($denda[$kary][$blok]>0){								
								$message_text.="Penalty Rp: <b>".hidezerodecimal($denda[$kary][$blok])."</b>\n";
							}
							$message_text.="Total ".$nmorganisasi[$blok]." : <b>".hidezerodecimal($rplb1[$kary][$blok]+$kehadiran[$kary][$blok])."</b>\n\n";
							$totalorg[$kary]+=$rplb1[$kary][$blok]+$kehadiran[$kary][$blok];
						}
						$message_text.="Total ".$optnm[$kary]." : <b>".hidezerodecimal($totalorg[$kary])."</b>\n";
					}							
					
					
					$message_text.="\n<b>TOTAL</b>\n";
					$message_text.="Jjg: <b>".hidezerodecimal($ttl['jjg'])."</b>\n";
					$message_text.="Kg: <b>".hidezerodecimal($ttl['kg'])."</b>\n";
					$message_text.="Rp: <b>".hidezerodecimal($ttl['rp'])."</b>\n";
					$message_text.="Kehadiran: <b>".hidezerodecimal($ttl['hdr'])."</b>\n";
					if($ttl['brd']>0){								
						$message_text.="Brondol: <b>".hidezerodecimal($ttl['brd'])."</b> Kg\n";
					}
					if($ttl['pen']>0){
						$message_text.="Penalty Rp: <b>".hidezerodecimal($ttl['pen'])."</b>\n";
					}
					$message_text.="Total: <b>".hidezerodecimal($ttl['rp']+$ttl['hdr'])."</b>\n";
					$inline_button = array(array(
											array("text"=>"List BKM","callback_data"=>"/BKM ".$divisi." ".$param[3].""),
											array("text"=>"Help","callback_data"=>$lower[0]." info")
										)
									);
				}else{
					$message_text.="Data tidak ada silahkan proses melalui :\n<b>Menu : Kebun - Proses - Premi Pemanen</b>\n\natau click Help untuk bantuan.\n ".$lower[0]." ".$lower[1]." ".$lower[2]." ".$lower[3]."\n";
					$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
				}
				send_reply($telegram_id, $msgid, $message_text,$inline_button);
			break;
			case'DETPNN':
				if($param[2]!=''){
					$str="select a.nik,b.nikmandor,b.jurnal,a.hasilkerja,a.luaspanen,a.rupiahpenalty,a.brondolan,b.tanggal,
						substr(a.kodeorg,1,6) as divisi, a.kodeorg as blok
						from ".$dbname.".kebun_prestasi a 
						left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
						where a.notransaksi='".$param[2]."' group by a.nik,a.kodeorg order by a.nik asc";
					$res=fetchdata($str);
					if(count($res)>0){
						$post=$res[0]['jurnal'];
						$mdr=$res[0]['nikmandor'];
						$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['nikmandor']."'");
						$tgl=$res[0]['tanggal'];
						$message_text="<b>Prestasi Kerja Panen</b>\n";
						$message_text.="Notrans : <b>".$param[2]."</b>\n";
						$message_text.="Tanggal : <b>".$tgl."</b>\n";
						$message_text.="Posting : <b>".($res[0]['jurnal']=='1'?'Posted':'Blm Posting')."</b>\n";
						$message_text.="Mandor : <b>".$optnm[$res[0]['nikmandor']]."</b>\n";
						$message_text.="----------------------------\n";
						foreach($res as $bar){
							$data[$bar['nik']][$bar['blok']]=$bar['blok'];
							$hasilkerja[$bar['nik']][$bar['blok']]+=$bar['hasilkerja'];
							$luaspanen[$bar['nik']][$bar['blok']]+=$bar['luaspanen'];
							$brondolan[$bar['nik']][$bar['blok']]+=$bar['brondolan'];
							$rupiahpenalty[$bar['nik']][$bar['blok']]+=$bar['rupiahpenalty'];
							$divisi=$bar['divisi'];
							$ttl['jjg']+=$bar['hasilkerja'];
							$ttl['ha']+=$bar['luaspanen'];
							$ttl['brd']+=$bar['brondolan'];
							$ttl['pen']+=$bar['rupiahpenalty'];
						}
						foreach($data as $kary => $vblok){
							$no++;
							$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kary."'");
							$message_text.="\n<b>".$no.". ".$optnm[$kary]."</b>\n";
							foreach($vblok as $blok){								
								$message_text.=$nmorganisasi[$blok].", ";
								$message_text.="Jjg: <b>".hidezerodecimal($hasilkerja[$kary][$blok])."</b>, ";
								$message_text.="Ha: <b>".hidezerodecimal($luaspanen[$kary][$blok],2)."</b>\n";
								if($brondolan[$kary][$blok]>0){								
									$message_text.="Brondol: <b>".hidezerodecimal($brondolan[$kary][$blok])."</b> Kg, ";
								}
								if($rupiahpenalty[$kary][$blok]>0){								
									$message_text.="Penalty Rp: <b>".hidezerodecimal($rupiahpenalty[$kary][$blok])."</b>";
								}
								if($brondolan[$kary][$blok]>0 or $rupiahpenalty[$kary][$blok]>0){									
									$message_text.="\n";
								}
							}
						}
						$message_text.="\n";
						
						$message_text.="<b>TOTAL</b>\n";
						$message_text.="Jjg: <b>".hidezerodecimal($ttl['jjg'])."</b>\n";
						$message_text.="Ha: <b>".hidezerodecimal($ttl['ha'],2)."</b>\n";
						if($ttl['brd']>0){								
							$message_text.="Brondol: <b>".hidezerodecimal($ttl['brd'])."</b> Kg\n";
						}
						if($ttl['pen']>0){
							$message_text.="Penalty Rp: <b>".hidezerodecimal($ttl['pen'])."</b>\n";
						}
						
						$str = "select * from ".$dbname.".setup_posting where kodeaplikasi='rawatkebun' and jabatan='".$kodejabatan."'";
						$res = fetchdata($str);
						if(count($res)>0 and $post=='0'){
							$inline_button = array(array(
													array("text"=>"Upah dan Premi","callback_data"=>"/BKM PREMIPNN ".$divisi." ".$tgl." ".$mdr.""),
													array("text"=>"List BKM","callback_data"=>"/BKM ".$divisi." ".$tgl."")
												),
												array(
													array("text"=>"Posting","callback_data"=>"/BKMPOST YAKINPOSTPNN ".$param[2].""),
													array("text"=>"Help","callback_data"=>$lower[0]." info")
												)
											);
						}else{							
							$inline_button = array(array(
													array("text"=>"Upah dan Premi","callback_data"=>"/BKM PREMIPNN ".$divisi." ".$tgl." ".$mdr.""),
													array("text"=>"List BKM","callback_data"=>"/BKM ".$divisi." ".$tgl."")
												),
												array(array("text"=>"Help","callback_data"=>$lower[0]." info"))
											);
						}
					}else{
						$message_text.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$lower[1]." ".$lower[2]." ".$lower[3]."\n";
						$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
					}
				}else{
					$message_text="Nomor transaksi tidak boleh kosong.\n";
					$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
				}
				// send_reply($telegram_id, $msgid, $message_text,$inline_button);
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
			break;
			case'DET':
				if($param[2]!=''){
					$str="select * from ".$dbname.".setup_kegiatan";
					$res=fetchdata($str);
					foreach($res as $bar){
						$nmkeg[$bar['kodekegiatan']]=$bar['namakegiatan'];
						$nmsat[$bar['kodekegiatan']]=$bar['satuan'];
					}
					
					$str = "select * from ".$dbname.".sdm_absensidt where norefrensi='".$param[2]."'"; 
					$res = fetchdata($str);
					$jlhabs = count($res);
					
					$str="select c.nikmandor,c.jurnal,a.notransaksi,sum(a.insentif) as upahpremi, 
						sum(a.umr) as umr,sum(a.jhk) as jumlahhk,kodekegiatan,
						tanggal,b.kodeorg, sum(b.hasilkerja) as hasilkerja, substr(b.kodeorg,1,6) as divisi 
						from ".$dbname.".kebun_kehadiran a 
						left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi and a.nik=b.nikpemel and a.nourut=b.nourut
						left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi 
						where a.notransaksi='".$param[2]."' group by kodekegiatan, b.kodeorg order by kodekegiatan asc, b.kodeorg asc";
					$res=fetchdata($str);
					if(count($res)>0 or $jlhabs>0){
						$ttl=$totbarang=array();
						
						$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['nikmandor']."'");
						$tgl=$res[0]['tanggal'];
						$message_text="<b>Prestasi Kerja Rawat</b>\n";
						$message_text.="Notrans : <b>".$param[2]."</b>\n";
						$message_text.="Tanggal : <b>".$tgl."</b>\n";
						$message_text.="Posting : <b>".($res[0]['jurnal']=='1'?'Posted':'Blm Posting')."</b>\n";
						$message_text.="Mandor : <b>".$optnm[$res[0]['nikmandor']]."</b>\n";
						$message_text.="----------------------------\n";
						foreach($res as $bar){
							$divisi=$bar['divisi'];
							$ttl['hk']+=$bar['jumlahhk'];
							$ttl['umr']+=$bar['umr'];
							$ttl['pre']+=$bar['upahpremi'];
							
							$message_text.="Keg : ".$nmkeg[$bar['kodekegiatan']]."\n";
							$message_text.="Blok : ".$nmorganisasi[$bar['kodeorg']]."\n";
							$message_text.="Pres : <b>".hidezerodecimal($bar['hasilkerja'],2)."</b> ".$nmsat[$bar['kodekegiatan']]."; ";
							$message_text.="HK : <b>".hidezerodecimal($bar['jumlahhk'],2)."</b>\n";
							$message_text.="Upah : <b>".hidezerodecimal($bar['umr'])."</b>; ";
							$message_text.="Premi : <b>".hidezerodecimal($bar['upahpremi'])."</b>\n";
							
							$sMat="select * from ".$dbname.".kebun_pakaimaterial where notransaksi='".$bar['notransaksi']."' and kodekegiatan='".$bar['kodekegiatan']."' and kodeorg='".$bar['kodeorg']."'";
							$qMat=fetchdata($sMat);
							if(count($qMat)>0){
								$nomat=0;
								$message_text.="Material :\n";
								foreach($qMat as $rMat){
									$nomat++;
									$str="select * from ".$dbname.".log_5masterbarang where kodebarang='".$rMat['kodebarang']."'";
									$res=fetchdata($str);
									foreach($res as $bar){
										$optmat[$bar['kodebarang']]=$bar['namabarang'];
										$nmsat[$bar['kodebarang']]=$bar['satuan'];
									}
									$message_text.=$nomat.". ".$optmat[$rMat['kodebarang']];
									$message_text.=" = <b>".$rMat['kwantitas']."</b> ".$nmsat[$rMat['kodebarang']]."\n";
									
									$totbarang[$rMat['kodebarang']]=$rMat['kodebarang'];
									$totbrng[$rMat['kodebarang']]+=$rMat['kwantitas'];
								}								
							}
							$message_text.="\n";
							
						}
						
						#ini absensi
						$str = "select * from ".$dbname.".sdm_absensidt where norefrensi='".$param[2]."'"; 
						$res = fetchdata($str);
						if(count($res)>0){
							foreach($res as $bar){
								$jhkabs+=$bar['hk'];
								$umrabs+=$bar['umr'];
								$premiabs+=$bar['premi'];
							}
		
							$message_text.="<b>Absensi Umum :</b>\n";
							$message_text.="HK : <b>".hidezerodecimal($jhkabs,2)."</b> ";
							$message_text.="Upah : <b>".hidezerodecimal($umrabs)."</b>; ";
							$message_text.="Premi : <b>".hidezerodecimal($premiabs)."</b>\n";
							$message_text.="\n";
						}
						
						
						$message_text.="<b>TOTAL</b>\n";
						$message_text.="HK: <b>".hidezerodecimal($ttl['hk']+$jhkabs,2)."</b>\n";
						$message_text.="Upah: <b>".hidezerodecimal($ttl['umr']+$umrabs)."</b>;\n";
						$message_text.="Premi: <b>".hidezerodecimal($ttl['pre']+$premiabs)."</b>\n";
						if(count($totbarang)>0){							
							$message_text.="\n<b>MATERIAL</b>\n";
							$no=0;
							foreach($totbarang as $brg){
								$str="select * from ".$dbname.".log_5masterbarang where kodebarang='".$brg."'";
								$res=fetchdata($str);
								foreach($res as $bar){
									$optmat[$bar['kodebarang']]=$bar['namabarang'];
									$nmsat[$bar['kodebarang']]=$bar['satuan'];
								}
								$no++;
								$message_text.=$no.". ".$optmat[$brg].": <b>".$totbrng[$brg]."</b> ".$nmsat[$brg]."\n";							
							}
						}
						
						$inline_button = array(array(
												array("text"=>"Kehadiran","callback_data"=>"/BKM KEHADIRAN ".$param[2].""),
												array("text"=>"List BKM","callback_data"=>"/BKM ".$divisi." ".$tgl."")
											),
											array(array("text"=>"Help","callback_data"=>$lower[0]." info"))
										);
					}else{
						$message_text.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$lower[1]." ".$lower[2]." ".$lower[3]."\n";
						$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
					}
				}else{
					$message_text="Nomor transaksi tidak boleh kosong.\n";
					$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
				}
				//send_reply($telegram_id, $msgid, $message_text,$inline_button);
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
			break;
			case'KEHADIRAN':
				if($param[2]!=''){
					#ini absensi
					$str = "select * from ".$dbname.".sdm_absensidt where norefrensi='".$param[2]."'"; 
					$res = fetchdata($str);
					$jlhabs = count($res);
						
					$str="select * from ".$dbname.".kebun_kehadiran where notransaksi='".$param[2]."' order by nourut";
					$res=fetchdata($str);
					$message_text="<b>Kehadiran</b>\n";
					if(count($res)>0 or $jlhabs>0){
						$s="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$param[2]."'";
						$r=fetchdata($s);
						$tgl=$r[0]['tanggal'];
						$mdr=$r[0]['nikmandor'];
						$post=$r[0]['jurnal'];
						
						$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$mdr."'");
						$s="select substr(kodeorg,1,6) as divisi from ".$dbname.".kebun_prestasi where notransaksi='".$param[2]."'";
						$r=fetchdata($s);
						$divisi=$r[0]['divisi'];
						
						
						$message_text="<b>Kehadiran</b>\n";
						$message_text.="Notrans : <b>".$param[2]."</b>\n";
						$message_text.="Tanggal : <b>".$tgl."</b>\n";
						$message_text.="Posting : <b>".($post=='1'?'Posted':'Blm Posting')."</b>\n";
						$message_text.="Mandor : <b>".$optnm[$mdr]."</b>\n";
						$message_text.="--------------------------\n";
						$message_text.="H : HK, U : Upah, P : Premi\n";
						$message_text.="--------------------------\n";
						foreach($res as $bar){
							$optkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['nik']."'");
							$no++;
							$message_text.=$no.". ".$optkary[$bar['nik']]."\n     => ";
							if($bar['jhk']>'0'){								
								$message_text.="<i>H: <b>".hidezerodecimal($bar['jhk'],2)."</b>,</i> ";
							}
							if($bar['umr']>'0'){								
								$message_text.="<i>U: <b>".hidezerodecimal($bar['umr'])."</b>,</i> ";
							}
							if($bar['insentif']>'0'){								
								$message_text.="<i>P: <b>".hidezerodecimal($bar['insentif'])."</b></i>";
							}
							$message_text.="\n";
							
							$ttl['hk']+=$bar['jhk'];
							$ttl['umr']+=$bar['umr'];
							$ttl['pre']+=$bar['insentif'];
						}
						
						#ini absensi
						$str = "select * from ".$dbname.".sdm_absensidt where norefrensi='".$param[2]."'"; 
						$res = fetchdata($str);
						if(count($res)>0){
							foreach($res as $bar){
								$optkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
								$no++;
								$message_text.=$no.". ".$optkary[$bar['karyawanid']]."\n     => ";
								if($bar['hk']>'0'){								
									$message_text.="<i>H: <b>".hidezerodecimal($bar['hk'],2)."</b>,</i> ";
								}
								if($bar['umr']>'0'){								
									$message_text.="<i>U: <b>".hidezerodecimal($bar['umr'])."</b>,</i> ";
								}
								if($bar['premi']>'0'){								
									$message_text.="<i>P: <b>".hidezerodecimal($bar['premi'])."</b></i>";
								}
								$message_text.="\n";
								
								$ttl['hk']+=$bar['hk'];
								$ttl['umr']+=$bar['umr'];
								$ttl['pre']+=$bar['premi'];
							}
						}
						
						
						$message_text.="\n<b>TOTAL</b>\n";
						$message_text.="H: <b>".hidezerodecimal($ttl['hk'],2)."</b>\n";
						$message_text.="U: <b>".hidezerodecimal($ttl['umr'])."</b>;\n";
						$message_text.="P: <b>".hidezerodecimal($ttl['pre'])."</b>\n";
						
						$str = "select * from ".$dbname.".setup_posting where kodeaplikasi='rawatkebun' and jabatan='".$kodejabatan."'";
						$res = fetchdata($str);
						if(count($res)>0 and $post=='0'){
							$inline_button = array(array(
													array("text"=>"Prestasi","callback_data"=>"/BKM DET ".$param[2].""),
													array("text"=>"List BKM","callback_data"=>"/BKM ".$divisi." ".$tgl.""),
												
												),
												array(
													array("text"=>"Posting","callback_data"=>"/BKMPOST YAKINPOST ".$param[2].""),
													array("text"=>"Help","callback_data"=>$lower[0]." info")
												)	
											);
						}else{
							$inline_button = array(array(
													array("text"=>"Prestasi","callback_data"=>"/BKM DET ".$param[2].""),
													array("text"=>"List BKM","callback_data"=>"/BKM ".$divisi." ".$tgl."")
												),
												array(array("text"=>"Help","callback_data"=>$lower[0]." info"))
											);
						}
					}else{
						$message_text.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$lower[1]." ".$lower[2]." ".$lower[3]."\n";
						$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
					}
				}else{
					$message_text="Nomor transaksi tidak boleh kosong.\n";
					$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
				}
				send_reply($telegram_id, $msgid, $message_text,$inline_button);
			break;
			default:
				$s = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param[1]."'";
				$query=$s;
				$r = fetchdata($s);
				foreach($r as $b){
					$nmorg=$b['kodeorganisasi'];
				}
				$extgl=explode("-",$param[1]);
				if($param[1]==''){
					$param[10]='SENDMESSAGE';
					if($param[2]!=''){
						$tgllalu=$param[2];							
					}else{						
						$tgllalu = date('Y-m-d', strtotime('-1 days', strtotime(date("Y-m-d"))));
					}
					$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tgllalu)));
					if($tipeorg!='KEBUN'){
						$orgdet = orgDetail($userowl);
						$s = "select * from ".$dbname.".organisasi where tipe='KEBUN' and kodeorganisasi in ('".implode("','",$orgdet)."')";
						$r = fetchdata($s);
						$e=0; $jlh=count($r);
						foreach($r as $b){
							if($e==round($jlh/3)){$e=0;}
							$inline_button[$e][]= array(
										"text"=>$b['kodeorganisasi'],"callback_data"=>"/bkm ".$b['kodeorganisasi']." ".$tgllalu
										);
							$e++;
						}
						
						$message_text.="Silahkan pilih BKM untuk Unit mana ?\n";
						$message_text.="Tanggal : ".$tgllalu."\n";
						#ini 2 spasi supaya masuk sini lagi
						$inline_button[] = array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]."  ".$tgllalu2),
												array("text"=>"Help","callback_data"=>$lower[0]." info")
												);
					}else{
						$s = "SELECT distinct a.kodeorg, substr(b.kodeorg,1,6) as divisi FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and tanggal = '".$tgllalu."' and a.kodeorg='".$lokasitugas."' order by divisi asc";
						$r = fetchdata($s);
						if(count($r)>0){
							$message_text.="BKM Unit : <b>".$lokasitugas."</b>\nTanggal : <b>".$tgllalu."</b>\nClick tombol dibawah untuk melihat detail BKM\n";
							$e=0; $jlh=count($r);
							foreach($r as $b){
								$judul=$b['divisi'];
								if($b['divisi']==''){
									$b['divisi']='ABS';
									$judul='ABSEN';
								}
								
								if($e==round($jlh/2)){$e=0;}
								$inline_button[$e][]=array(
											"text"=>$judul,
											"callback_data" => "/BKM ".$b['divisi']." ".$tgllalu." ".$lokasitugas
									);
								$e++;	
							}
							$inline_button[] = array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$lokasitugas." ".$tgllalu2),
												array("text"=>"Help","callback_data"=>$lower[0]." info")
												);
							//$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
						}else{
							$message_text.="BKM Unit ".$lokasitugas.", tanggal ".$tgllalu."\n";
							$message_text.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$lokasitugas." ".$tgllalu."\n";
							
							$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tgllalu)));
							$inline_button = array(array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$lokasitugas." ".$tgllalu2),
												array("text"=>"Help","callback_data"=>$lower[0]." info")
											));							
						}
					}
					if($param[10]=='SENDMESSAGE'){
						send_reply($telegram_id, $msgid, $message_text,$inline_button);						
					}else{						
						editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
					}
				}elseif(checkdate($extgl[1],$extgl[2],$extgl[0]) or strlen($nmorg)<='4' and $param[1]!='ABS'){
					if($param[2]!=''){
						$tgllalu = $param[2];
					}elseif(checkdate($extgl[1],$extgl[2],$extgl[0])){
						$tgllalu = $param[1];
					}else{						
						$tgllalu = date('Y-m-d', strtotime('-1 days', strtotime(date("Y-m-d"))));
					}
					
					if(strlen($nmorg)<='4' and !checkdate($extgl[1],$extgl[2],$extgl[0])){
						$kdorg=$nmorg;
					}else{
						$kdorg=$lokasitugas;
					}
					
					$s = "SELECT distinct a.kodeorg, substr(b.kodeorg,1,6) as divisi FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and tanggal = '".$tgllalu."' and a.kodeorg='".$kdorg."' order by divisi asc";
					$r = fetchdata($s);
					if(count($r)>0){
						$message_text.="BKM Unit : <b>".$kdorg."</b>\nTanggal : <b>".$tgllalu."</b>\nClick tombol dibawah untuk melihat detail\n";
						$e=0; $jlh=count($r);
						foreach($r as $b){
							$judul=$b['divisi'];
							if($b['divisi']==''){
								$b['divisi']='ABS';
								$judul='ABSEN';
							}
							
							if($e==round($jlh/2)){$e=0;}
							$inline_button[$e][]=array(
										"text"=>$judul,
										"callback_data" => "/BKM ".$b['divisi']." ".$tgllalu." ".$kdorg
								);
							$e++;	
						}
						$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tgllalu)));
						$inline_button[] = array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$kdorg." ".$tgllalu2),
												array("text"=>"Help","callback_data"=>$lower[0]." info")
												);
										
						#$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
					}else{
						$message_text.="BKM Unit ".$kdorg.", tanggal ".$tgllalu."\n";
						$message_text.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$kdorg." ".$tgllalu."\n";
						$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tgllalu)));
						$inline_button = array(array(
											array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$kdorg." ".$tgllalu2),
											array("text"=>"Help","callback_data"=>$lower[0]." info")
										));
					}
					editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
				}else{
					$wh=$group=$masuk=$whkdorg="";
					$whkdorg="and kodeorganisasi='".substr($param[1],0,4)."'";
					$s = "select * from ".$dbname.".organisasi where 1=1 ".$whkdorg."";
					$r = fetchdata($s);
					foreach($r as $b){
						$nmorg=$b['kodeorganisasi'];
					}
					$tgllalu = date('Y-m-d', strtotime('-1 days', strtotime(date("Y-m-d"))));
					$tglhi = $tgllalu;
					if($param[2]!=''){					
						if(strlen($param[2])!='10'){
							$message_text.="\ninfo : Format tanggal salah, format yg benar yyyy-mm-dd, contoh ".date("Y-m-d")."\n\n";
							$tglhi = $tgllalu;
						}else{								
							$tglhi = $param[2];
						}
					}else{					
						$tglhi = $tgllalu;
					}
					if($nmorg!=''){
						$aksesorg=orgDetail($userowl);
						if($aksesorg[substr(strtoupper($param[1]),0,4)]==''){
							$message_text.="\nAnda tidak memiliki otorisasi ke ".substr(strtoupper($param[1]),0,4)."\n";
							$wh.=" and a.kodeorg = '".$lokasitugas."'"; $info=$lokasitugas;
						}else{						
							$wh.=" and a.kodeorg = '".substr($param[1],0,4)."'"; $info=substr($param[1],0,4);
						}
						if(strlen($param[1])=='6'){
							$wh.=" and substr(b.kodeorg,1,6) = '".$param[1]."'";
						}
						
					}elseif($param[1]=='ABS'){
						$wh.=" and a.kodeorg = '".substr($param[3],0,4)."'"; $info=substr($param[3],0,4);
						$wh.=" and b.kodeorg is null";
					}else{
						$wh.=" and a.kodeorg = '".$lokasitugas."'"; $info=$lokasitugas;
						if(strlen($param[1])!='10'){
							$message_text.="\ninfo : Format tanggal salah, format yg benar yyyy-mm-dd, contoh ".date("Y-m-d")."\n\n";
							$tglhi = $tgllalu; 
						}else{								
							$tglhi = $param[1];
						}
					}					
					#and a.tipetransaksi not in ('PNN')
					$str = "SELECT a.*, substr(b.kodeorg,1,6) as divisi, sum(b.hasilkerja) as jjg, sum(b.jumlahhk) as hk, sum(b.upahpremi) as premi FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1  and tanggal = '".$tglhi."' ".$wh." group by a.notransaksi order by divisi asc, a.notransaksi asc";
					$res=fetchdata($str);
					if(count($res)>0){						
						$message_text.="<b>Buku Kegiatan Mandor</b>\nKebun : <b>".$info."</b>\nTanggal : <b>".$tglhi."</b>\nP = Posted\nN = Not Posted\nClick untuk melihat detail...\n";
						foreach($res as $bar){
							$s = "SELECT * FROM " . $dbname . ".sdm_absensidt where  norefrensi  = '".$bar['notransaksi']."'";
							$r=fetchdata($s);
							if(count($r)==0 and $bar['divisi']==''){
								$message_text.="\n<b>Detail transaksi tidak ada.</b>\n";
							}else{
								$no++;
								if($bar['divisi']==''){$bar['divisi']="ABSEN";}
								if($bar['jurnal']=='1'){$p="(P)";}else{$p="(N)";}
								if($bar['tipetransaksi']=='PNN' and $bar['noreferensi']==''){
									$inline_button[][]=array(
											"text"=>$no.". ".$bar['divisi']." - ".$bar['notransaksi']." ".$p,
											"callback_data" => "/BKM DETPNN ".$bar['notransaksi']
									);
								}elseif($bar['tipetransaksi']!='PNN'){
									$inline_button[][]=array(
											"text"=>$no.". ".$bar['divisi']." - ".$bar['notransaksi']." ".$p,
											"callback_data" => "/BKM DET ".$bar['notransaksi']
									);
								}								
							}
						}	
						$inline_button[] = array(
												array("text"=>"Back","callback_data"=>$lower[0]." ".$info." ".$tglhi),
												array("text"=>"Help","callback_data"=>$lower[0]." info")
												);
					}else{
						$message_text.="Data BKM untuk unit ".$lokasitugas." tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$lower[1]." ".$lower[2]." ".$lower[3]."\n";
						$inline_button = array(array(array("text"=>"Help","callback_data"=>$lower[0]." info")));
					}
					editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
				}
				//editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
			break;
		}
	break;
	case'/UPAH':
		if(strlen($param[1])!='7'){
			$message_text.="Format periode salah, coba dengan ".$param[0]." ".date("Y-m")."\n";
		}else{			
			$str = "select * from ".$dbname.".sdm_gaji a 
			left join ".$dbname.".sdm_ho_component b on a.idkomponen = b.id
			where karyawanid ='".$karidowl."' and periodegaji='".$param[1]."'
			order by plus desc
			";
			$res=fetchdata($str);
			foreach($res as $bar){
				$data[$bar['plus']][$bar['name']]=$bar['jumlah'];
			}
			if(count($res)>0){
				$tab.="<b>Upah Periode : ".$param[1]."</b>\n";
				foreach($data as $plus => $key){
					if($plus==1){$n="Penambah";}else{$n="Pengurang";}
					$tab.="<b>\n".$n."</b>\n";
					$no=0;
					foreach($key as $comp => $jlh){					
						$no++;
						$tab.=$no.". ".$comp." = ".hidezerodecimal($jlh)."\n";
						$ttl[$plus]+=$jlh;
					}
					$tab.="<b>Total : ".hidezerodecimal($ttl[$plus])."</b>\n";
				}			
				$tab.="\n<b>Gaji Bersih : ".hidezerodecimal($ttl['1']-$ttl['0'])."</b>\n";
				$message_text.=$tab;
			}else{
				$message_text.="Data tidak ditemukan.";
			}
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/KARY':
		$str = "select * from ".$dbname.".datakaryawan where lokasitugas ='".$lokasitugas."' and tanggalkeluar='0000-00-00' limit 50";
		$res=fetchdata($str);
		if(count($res)>0){
			foreach($res as $bar){
				$no++;
				$tab.=$no.". ".$bar['namakaryawan'].", Hp. ".$bar['nohp']."\n";
			}			
			$message_text.=$tab;
		}else{
			$message_text.="Data tidak ditemukan.";
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/WB':
		$wh=$group="";
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>(1)</b><i> spasi </i><b>(2)</b><i> spasi </i><b>(3)</b>
				<b>(1) = cpo atau ker atau tbs atau notiket atau nopol</b>
				<b>(2) = jika (1) = cpo/ker => Mill,
							      jika (1) = tbs => Kebun/Divisi</b>
				<b>(3) = tanggal</b>
				Contoh : 
				1. ".$lower[0]." A069709
				2. ".$lower[0]." tbs sd1e ".date("Y-m-d")."
				3. ".$lower[0]." cpo sdkm ".date("Y-m-d")."
				4. ".$lower[0]." ker sdkm ".date("Y-m-d")."
				5. ".$lower[0]." KB8163JL
				6. ".$lower[0]." KB8163JL ".date("Y-m-d")."
				7. ".$lower[0]." KB8163JL ".date("Y-m")."
				";
			break;
			case'TBS':
				$wh.=" and kodebarang='40000003'";
				if($param[2]==''){
					$param[2]=$lokasitugas;
					$wh.=" and kodeorg like '".substr($param[2],0,4)."%' and kodeorg!=''";
				}else{
					$xkdorg=explode("#",$param[2]);
					if($xkdorg[0]=='EXTN'){
						#$wh.=" and divcode like '".$param[2]."%'";
						$wh.=" and kodeorg = ''";
						$wh.=" and millcode = '".$xkdorg[1]."'";
					}else{						
						$wh.=" and kodeorg like '".substr($param[2],0,4)."%' and kodeorg!=''";
						$wh.=" and divcode like '".$param[2]."%'";
					}
				}
				if($param[3]!=''){
					if(strlen($param[3])!='10'){
						$message_text.="\ninfo : Format tanggal salah, format yg benar yyyy-mm-dd, contoh ".date("Y-m-d")."\n\n";
						$tglhi = date("Y-m-d");
					}else{								
						$tglhi = $param[3];
					}
				}else{					
					$tglhi = date("Y-m-d");
				}
				
				$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tglhi)));
				$tgldepan = date('Y-m-d', strtotime('+1 days', strtotime($tglhi)));				
				$str = "select * from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' ".$wh." order by divcode";
				$res=fetchdata($str);
				if(count($res)>0){
					$message_text.="\nTiket Timbang <b>".$param[1]."</b>\nUnit : ".substr($param[2],0,4)."\nTanggal : ".$tglhi."\n";
					$message_text.="<b>No. Div - Nopol - Supir : Kg</b>\n";
					foreach($res as $bar){
						$no++;
						$inline_button[][] = array("text"=>$no.". ".substr($bar['divcode'],-2)." - ".$bar['nokendaraan']." - ".$bar['supir']." : ".$bar['beratbersih']." Kg","callback_data"=>"/WB ".$bar['notransaksi']."");
					}
					$inline_button[] = array(array("text"=>"Back","callback_data"=>$param[0]." tbs ".$param[2]),array("text"=>"Help","callback_data"=>$lower[0]." info"));
				}else{
					$message_text.="Data tidak ada, click Help untuk bantuan.\n<b>".$lower[0]." ".$lower[1]." ".strtolower($param[2])." ".$tglhi."</b>\n";
					
					$inline_button = array(
									array(
										array("text"=>$tgllalu2,"callback_data"=>$lower[0]." tbs ".$param[2]." ".$tgllalu2),
										array("text"=>$tgldepan,"callback_data"=>$lower[0]." tbs ".$param[2]." ".$tgldepan)
									),
									array(
										array("text"=>"Help","callback_data"=>$lower[0]." info")
									)
								);
				}
			break;
			case'CPO':
				$wh.="and kodebarang='40000001'";
				if($param[2]==''){
					$param[2]=$lokasitugas;
					$wh.=" and millcode like '".substr($param[2],0,4)."%'";
				}else{
					$wh.=" and millcode like '".substr($param[2],0,4)."%'";
				}
				if($param[3]!=''){
					if(strlen($param[3])!='10'){
						$message_text.="\ninfo : Format tanggal salah, format yg benar yyyy-mm-dd, contoh ".date("Y-m-d")."\n\n";
						$tglhi = date("Y-m-d");
					}else{								
						$tglhi = $param[3];
					}
				}else{					
					$tglhi = date("Y-m-d");
				}
				
				$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tglhi)));
				$tgldepan = date('Y-m-d', strtotime('+1 days', strtotime($tglhi)));
				$str = "select * from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' ".$wh." order by notransaksi";
				$res=fetchdata($str);
				$data=$det=array();
				if(count($res)>0){
					$message_text="\nTiket Timbang <b>".$param[1]."</b>\nUnit : ".$param[2]."\nTanggal : ".$tglhi."\n";
					foreach($res as $bar){
						$no++;
						$inline_button[][] = array("text"=>$no.". ".$bar['nokendaraan']." - ".$bar['supir']." : ".$bar['beratbersih']." (Kg)","callback_data"=>"/WB ".$bar['notransaksi']."");
					}
					$inline_button[] = array(array("text"=>"Back","callback_data"=>$param[0]." cpo ".$param[2]),array("text"=>"Help","callback_data"=>$lower[0]." info"));
				}else{
					$message_text.="Data tidak ada, click Help untuk bantuan.\n<b>".$lower[0]." ".$lower[1]." ".strtolower($param[2])." ".$tglhi."</b>\n";
					$inline_button = array(
									array(
										array("text"=>$tgllalu2,"callback_data"=>$lower[0]." cpo ".$param[2]." ".$tgllalu2),
										array("text"=>$tgldepan,"callback_data"=>$lower[0]." cpo ".$param[2]." ".$tgldepan)
									),
									array(
										array("text"=>"Help","callback_data"=>$lower[0]." info")
									)
								);
				}
			break;
			case'KER':
				$wh="and kodebarang='40000002'";
				if($param[2]==''){
					$param[2]=$lokasitugas;
					$wh.=" and millcode like '".substr($param[2],0,4)."%'";
				}else{
					$wh.=" and millcode like '".substr($param[2],0,4)."%'";
				}
				if($param[3]!=''){
					if(strlen($param[3])!='10'){
						$message_text.="\ninfo : Format tanggal salah, format yg benar yyyy-mm-dd, contoh ".date("Y-m-d")."\n\n";
						$tglhi = date("Y-m-d");
					}else{								
						$tglhi = $param[3];
					}
				}else{					
					$tglhi = date("Y-m-d");
				}
				
				$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tglhi)));
				$tgldepan = date('Y-m-d', strtotime('+1 days', strtotime($tglhi)));
				$str = "select * from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' ".$wh." order by pengirim asc, notransaksi asc";
				$res=fetchdata($str);
				if(count($res)>0){
					$message_text.="\nTiket Timbang <b>".$param[1]."</b>\nUnit : ".$param[2]."\nTanggal : ".$tglhi."\n";
					$message_text.="Info : \nT = Penerimaan\nK = Pengiriman\nJ = Penjualan\n";
					foreach($res as $bar){
						$no++;
						if($bar['pengirim']!='' and $bar['pengirim']!=$bar['millcode']){
							#terima
							$inline_button[][] = array("text"=>$no.". (T) ".$bar['nokendaraan']." - ".$bar['supir']." : ".$bar['beratbersih']." (Kg)","callback_data"=>"/WB ".$bar['notransaksi']."");
						}elseif($bar['pengirim']==$bar['millcode']){
							#jual
							$inline_button[][] = array("text"=>$no.". (J) ".$bar['nokendaraan']." - ".$bar['supir']." : ".$bar['beratbersih']." (Kg)","callback_data"=>"/WB ".$bar['notransaksi']."");
						}else{
							#kirim
							$inline_button[][] = array("text"=>$no. ". (K) ".$bar['nokendaraan']." - ".$bar['supir']." : ".$bar['beratbersih']." (Kg)","callback_data"=>"/WB ".$bar['notransaksi']."");
						}
					}
					$inline_button[] = array(array("text"=>"Back","callback_data"=>$param[0]." ker ".$param[2]),array("text"=>"Help","callback_data"=>$lower[0]." info"));
				}else{
					$message_text.="Data tidak ada, click Help untuk bantuan.\n<b>".$lower[0]." ".$lower[1]." ".strtolower($param[2])." ".$tglhi."</b>\n";
					$inline_button = array(
									array(
										array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ker ".$param[2]." ".$tgllalu2),
										array("text"=>$tgldepan,"callback_data"=>$lower[0]." ker ".$param[2]." ".$tgldepan)
									),
									array(
										array("text"=>"Help","callback_data"=>$lower[0]." info")
									)
								);
				}
			break;
			case'OPT':
				if($tipeorg=='PABRIK'){
					$no=0;
					$str = "select * from ".$dbname.".organisasi where induk='".$kodept."' and tipe='KEBUN'";
					$res = fetchdata($str);					
					$message_text.="<b>Silahkan pilih TBS untuk kebun :</b>\n";
					foreach($res as $bar){
						$no++;$n=$no%2;$e=0;
						if($n=='0'){$e++;}
						$inline_button[$e][]= array(
									"text"=>$bar['kodeorganisasi'],"callback_data"=>$param[0]." TBS ".$bar['kodeorganisasi']
									);
					}
					$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
				}else{
					if($param[2]=='PT'){
						#isinya kebun
						$no=0;
						$str = "select * from ".$dbname.".organisasi where induk='".$param[3]."' and tipe='KEBUN'";
						$res = fetchdata($str);					
						$message_text.="<b>Silahkan pilih TBS untuk kebun :</b>\n";
						foreach($res as $bar){
							$no++;$n=$no%2;$e=0;
							if($n=='0'){$e++;}
							$inline_button[$e][]= array(
										"text"=>$bar['kodeorganisasi'],"callback_data"=>$param[0]." TBS ".$bar['kodeorganisasi']
										);
						}
						$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));						
					}elseif($param[2]=='TBS'){
						#list PT
						$no=0;
						$str = "select * from ".$dbname.".organisasi where tipe='PT'";
						$res = fetchdata($str);					
						$message_text.="<b>Silahkan pilih ".$param[2]." untuk PT :</b>\n";
						foreach($res as $bar){
							$no++;$n=$no%2;$e=0;
							if($n=='0'){$e++;}
							$inline_button[$e][]= array(
										"text"=>$bar['kodeorganisasi'],"callback_data"=>$param[0]." OPT PT ".$bar['kodeorganisasi']
										);
						}
						$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
					}else{
						#CPO, PK List PABRIK
						$no=0;
						$str = "select * from ".$dbname.".organisasi where tipe='PABRIK'";
						$res = fetchdata($str);					
						$message_text.="<b>Silahkan pilih ".$param[2]." untuk pabrik :</b>\n";
						foreach($res as $bar){
							$no++;$n=$no%2;$e=0;
							if($n=='0'){$e++;}
							$inline_button[$e][]= array(
										"text"=>$bar['kodeorganisasi'],"callback_data"=>$param[0]." ".$param[2]." ".$bar['kodeorganisasi']
										);
						}
						$inline_button[] = array(array("text"=>"Help","callback_data"=>$lower[0]." info"));
						
					}
				}
			break;
			default:
				#cari by nopol
				if($param[2]!=''){
					$whr=" and tanggal like '".$param[2]."%'";
				}else{
					$whr=" and tanggal like '".date("Y-m-d")."%'";
				}
				
				$sql = "select * from ".$dbname.".pabrik_timbangan where lower(REPLACE(nokendaraan,' ',''))='".strtolower(str_replace(" ","",$param[1]))."' ".$whr."";
				$req=fetchdata($sql);
				$nopol=count($req);
				
				#cari by tiket
				$str = "select * from ".$dbname.".pabrik_timbangan where notransaksi='".$param[1]."'";
				$res=fetchdata($str);
				if(count($res)>0){
					$s = "select * from ".$dbname.".organisasi";
					$r = fetchdata($s);
					foreach($r as $b){
						$nmrg[$b['kodeorganisasi']]=$b['namaorganisasi'];
						$ip[$b['kodeorganisasi']]=$b['inti'];
					}
					foreach($res as $bar){
						if($bar['kodebarang']=='40000004'){
							
							$message_text.="<i>Detail Pengiriman TBK :</i>
								Pabrik : <b>".$nmrg[$bar['millcode']]."</b>
								Tiket : <b>".$bar['notransaksi']."</b>
								Tanggal : <b>".tanggalnormal($bar['tanggal'])."</b>";
							if($bar['kodeorg']!=''){
								$message_text.="
								Unit : <b>".$nmrg[$bar['kodeorg']]."</b>
								Divisi : <b>".$nmrg[$bar['divcode']]."</b>";
							}else{
								$message_text.="
								Transportir : <b>".$bar['namatransportir']."</b>";
							}
							$message_text.="
								Nopol : <b>".$bar['nokendaraan']."</b>
								Driver : <b>".$bar['supir']."</b>
								Jam Masuk : <b>".$bar['jammasuk']."</b> 
								Jam Keluar : <b>".$bar['jamkeluar']."</b>
								Berat Masuk : <b>".hidezerodecimal($bar['beratmasuk'])."</b> Kg
								Berat Keluar : <b>".hidezerodecimal($bar['beratkeluar'])."</b> Kg
								Berat Bersih : <b>".hidezerodecimal($bar['beratbersih'])."</b> Kg
								Potongan : <b>".$bar['kgpotsortasi']."</b> Kg
								Setelah Pot : <b>".hidezerodecimal($bar['beratbersih']-$bar['kgpotsortasi'])."</b> Kg\n\n";
							
							if($bar['divcode']!=''){
								$n=$bar['divcode'];
							}elseif($bar['kodeorg']==''){
								$n="EXTN#".$bar['millcode'];
							}else{
								$n=$bar['kodeorg'];
							}
							
							$inline_button = array(array(
										array("text"=>"Back","callback_data"=>$param[0]." TBS ".$n." ".substr($bar['tanggal'],0,10)),
										array("text"=>"Help","callback_data"=>$param[0]." info")
									)
								);	
						}
						if($bar['kodebarang']=='40000003'){
							
							$message_text.="<i>Detail Pengiriman TBS :</i>
								Pabrik : <b>".$nmrg[$bar['millcode']]."</b>
								Tiket : <b>".$bar['notransaksi']."</b>
								Tanggal : <b>".tanggalnormal($bar['tanggal'])."</b>";
							if($bar['kodeorg']!=''){
								$message_text.="
								Unit : <b>".$nmrg[$bar['kodeorg']]."</b>
								Divisi : <b>".$nmrg[$bar['divcode']]."</b>";
							}else{
								$message_text.="
								Transportir : <b>".$bar['namatransportir']."</b>";
							}
							$message_text.="
								No SPB : <b>".$bar['nospb']."</b>
								Nopol : <b>".$bar['nokendaraan']."</b>
								Driver : <b>".$bar['supir']."</b>
								Jam Masuk : <b>".$bar['jammasuk']."</b> 
								Jam Keluar : <b>".$bar['jamkeluar']."</b>
								Janjang : <b>".$bar['jumlahtandan1']."</b>
								Berat Masuk : <b>".hidezerodecimal($bar['beratmasuk'])."</b> Kg
								Berat Keluar : <b>".hidezerodecimal($bar['beratkeluar'])."</b> Kg
								Berat Bersih : <b>".hidezerodecimal($bar['beratbersih'])."</b> Kg
								BJR : <b>".hidezerodecimal($bar['beratbersih']/$bar['jumlahtandan1'],2)."</b> Kg
								Potongan : <b>".$bar['kgpotsortasi']."</b> Kg
								Setelah Pot : <b>".hidezerodecimal($bar['beratbersih']-$bar['kgpotsortasi'])."</b> Kg\n\n";
							
							if($bar['divcode']!=''){
								$n=$bar['divcode'];
							}elseif($bar['kodeorg']==''){
								$n="EXTN#".$bar['millcode'];
							}else{
								$n=$bar['kodeorg'];
							}
							
							$inline_button = array(array(
										array("text"=>"Back","callback_data"=>$param[0]." TBS ".$n." ".substr($bar['tanggal'],0,10)),
										array("text"=>"Help","callback_data"=>$param[0]." info")
									)
								);	
						}
						if($bar['kodebarang']=='40000002'){
							if($bar['pengirim']!=''){
							$message_text.="<i>Detail Penerimaan KERNEL :</i>
								Bulking : <b>".$nmrg[$bar['millcode']]."</b>
								Pengirim : <b>".$bar['pengirim']."</b>
								No Tiket : <b>".$bar['notransaksi']."</b>
								Tanggal : <b>".tanggalnormal($bar['tanggal'])."</b>
								Referensi : <b>".$bar['norefrensi']."</b>
								No SIPB : <b>".$bar['nosipb']."</b>
								Nopol : <b>".$bar['nokendaraan']."</b>
								Driver : <b>".$bar['supir']."</b>
								Jam Masuk : <b>".$bar['jammasuk']."</b>
								Jam Keluar : <b>".$bar['jamkeluar']."</b>
								Berat Masuk : <b>".hidezerodecimal($bar['beratmasuk'])."</b> Kg
								Berat Keluar : <b>".hidezerodecimal($bar['beratkeluar'])."</b> Kg
								Berat Bersih : <b>".hidezerodecimal($bar['beratbersih'])."</b> Kg\n";
							}else{
							$message_text.="<i>Detail Pengiriman KERNEL :</i>
								Pabrik : <b>".$nmrg[$bar['millcode']]."</b>
								No Tiket : <b>".$bar['notransaksi']."</b>
								Tanggal : <b>".tanggalnormal($bar['tanggal'])."</b>
								No DO : <b>".$bar['nodo']."</b>
								No SIPB : <b>".$bar['nosipb']."</b>
								No Segel : <b>".$bar['nosegel']."</b> <b>(".$bar['jlhsegel'].")</b>
								Transportir : <b>".$bar['namatransportir']."</b>
								Nopol : <b>".$bar['nokendaraan']."</b>
								Driver : <b>".$bar['supir']."</b>
								Jam Masuk : <b>".$bar['jammasuk']."</b>
								Jam Keluar : <b>".$bar['jamkeluar']."</b>
								Berat Masuk : <b>".hidezerodecimal($bar['beratmasuk'])."</b> Kg
								Berat Keluar : <b>".hidezerodecimal($bar['beratkeluar'])."</b> Kg
								Berat Bersih : <b>".hidezerodecimal($bar['beratbersih'])."</b> Kg\n";
							}
							
							$inline_button = array(array(
										array("text"=>"Back","callback_data"=>$param[0]." KER ".$bar['millcode']." ".substr($bar['tanggal'],0,10)),
										array("text"=>"Help","callback_data"=>$param[0]." info")
									)
								);
						}
						if($bar['kodebarang']=='40000001'){
							$message_text.="<i>Detail Pengiriman CPO :</i>
								Pabrik : <b>".$nmrg[$bar['millcode']]."</b>
								No Tiket : <b>".$bar['notransaksi']."</b>
								Tanggal : <b>".tanggalnormal($bar['tanggal'])."</b>
								No DO : <b>".$bar['nodo']."</b>
								No SIPB : <b>".$bar['nosipb']."</b>
								No Segel : <b>".$bar['nosegel']."</b> <b>(".$bar['jlhsegel'].")</b>
								Transportir : <b>".$bar['namatransportir']."</b>
								Nopol : <b>".$bar['nokendaraan']."</b>
								Driver : <b>".$bar['supir']."</b>
								Jam Masuk : <b>".$bar['jammasuk']."</b>
								Jam Keluar : <b>".$bar['jamkeluar']."</b>
								Berat Masuk : <b>".hidezerodecimal($bar['beratmasuk'])."</b> Kg
								Berat Keluar : <b>".hidezerodecimal($bar['beratkeluar'])."</b> Kg
								Berat Bersih : <b>".hidezerodecimal($bar['beratbersih'])."</b> Kg\n";
								
								$inline_button = array(array(
										array("text"=>"Back","callback_data"=>$param[0]." CPO ".$bar['millcode']." ".substr($bar['tanggal'],0,10)),
										array("text"=>"Help","callback_data"=>$param[0]." info")
									)
								);
						}
					}
				}elseif($nopol>0){
					#cari by nopol
					$e=0; $jlh=count($req);
					foreach($req as $val){
						$no++;
						if($e==round($jlh/2)){$e=0;}
						$inline_button[$e][]= array(
									"text"=>$no.". ".$val['notransaksi']." - ".substr($val['tanggal'],0,10)." : ".$val['beratbersih']." Kg","callback_data"=>$param[0]." ".$val['notransaksi']
									);
						$e++;	
					}
					$server=false;
					$message_text="Tiket timbang untuk Nopol <b>".$param[1]."</b> adalah :\n<b><i>No. Tiket - Tanggal : Berat Bersih (Kg)</i></b>";
					
				}elseif($param[1]!=''){
					$message_text.="Data tiket timbang ".$param[1]." tidak ditemukan.";
				}else{
					$server=false;
					$message_text.="<b>Silahkan pilih dengan meng-click tombol dibawah</b>";
					if($tipeorg=='KEBUN'){
						$inline_button = array(array(
												array("text"=>"TBS","callback_data"=>"/WB TBS"),
												array("text"=>"Help","callback_data"=>$param[0]." info")
											)
										);
					}elseif($tipeorg=='PABRIK'){
						$inline_button = array(array(
												array("text"=>"TBS","callback_data"=>"/WB OPT TBS"),
												array("text"=>"CPO","callback_data"=>"/WB CPO"),
												array("text"=>"KER","callback_data"=>"/WB KER")
											),
											array(array("text"=>"Help","callback_data"=>$param[0]." info"))
										);
					}else{
						$inline_button = array(array(
												array("text"=>"TBS","callback_data"=>"/WB OPT TBS"),
												array("text"=>"CPO","callback_data"=>"/WB OPT CPO"),
												array("text"=>"KER","callback_data"=>"/WB OPT KER")
											),
											array(array("text"=>"Help","callback_data"=>$param[0]." info"))
										);
					}
				}
			break;
		}	
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/TBS':
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>(1)</b><i> spasi </i><b>(2)</b>
				<b>(1) = Kosong / Kebun / Divisi</b>
				<b>(2) = Kosong / Thn / thn-bln / thn-bln-tgl</b>
				Contoh :
				1. ".$lower[0]."
				2. ".$lower[0]." ".strtolower($lokasitugas)."
				3. ".$lower[0]." ".strtolower($lokasitugas)." ".date("Y")."
				4. ".$lower[0]." ".strtolower($lokasitugas)." ".date("Y-m")."
				5. ".$lower[0]." ".strtolower($lokasitugas)." ".date("Y-m-d")."
				6. ".$lower[0]." ".strtolower($lokasitugas)."01
				7. ".$lower[0]." ".strtolower($lokasitugas)."01 ".date("Y")."
				8. ".$lower[0]." ".strtolower($lokasitugas)."01 ".date("Y-m")."
				9. ".$lower[0]." ".strtolower($lokasitugas)."01 ".date("Y-m-d")."
				
				Note : Untuk swadaya / external Kebun isi dengan = EXTN#KODEMILL (EXTN#SDKM)
				";
			break;
			default:
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
					}else if($param[1]=='EXTM'){
						$wh=" and millcode = '".$param[1]."'";
						#$group=",kodeorg";
						if($param[2]!=''){							
							$tglhi = $param[2];			
						}
					}else{
						$tglhi = $param[1];			
					}
				}else{				
					$tglhi = date("Y-m-d");
				}
				
				$str = "select sum(beratbersih) as kg,count(notransaksi) as rit, kodeorg,millcode,divcode,pengirim,namatransportir from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' and kodebarang='40000003' ".$wh." group by millcode,kodeorg".$group."";
				//$tab=$str;
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
					}elseif($bar['millcode']=='EXTM'){
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
					$tab.="<b><i>Pengiriman TBS ".$info." Periode : ".$tglhi."</i></b>\n";
					foreach($data as $millcode => $key){
						$no=0;
							$tab.="\n<b>PKS : ".$millcode."</b>\n";
							if($group!=''){
								$tab.="<b>Divisi/KUD :</b>\n";
							}
						foreach($key as $kdorg => $kg){
							$no++;
							$nkdorg=explode("#",$kdorg);
							if(strlen($nkdorg[0])==6 and $nkdorg[1]=='' and $ip[$nkdorg[0]]=='0'){
								$nkdorg[0]=$nmrg[$nkdorg[0]];
							}
							$tab.="   ".$no.". ".$nkdorg[0]." : <b>".hidezerodecimal($kg)."</b> Kg (".$rit[$millcode][$kdorg]." Rit)\n";
							$stpks[$millcode]['kg']+=$kg;
							$stpks[$millcode]['rit']+=$rit[$millcode][$kdorg];
							$gt['kg']+=$kg;
							$gt['rit']+=$rit[$millcode][$kdorg];
						}
						$tab.="<b>Total ".$millcode." : ".hidezerodecimal($stpks[$millcode]['kg'])." Kg (".$stpks[$millcode]['rit']." Rit)</b>\n";
					}
					$tab.="<b>\nGrand Total : ".hidezerodecimal($gt['kg'])." Kg (".$gt['rit']." Rit)</b>\n";
					
					if($tipeorg=='KEBUN'){
						$masuk='1';
						if(strlen($param[1])=='4'){	
							if($subbagian!=''){
								$inline_button = array(
									array(
										array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$subbagian." ".$tgllalu2),
										array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$subbagian." ".$tgldepan)
									),
									array(
										array("text"=>$subbagian,"callback_data"=>$param[0]." ".$subbagian." ".$tglhi),
										array("text"=>"Help","callback_data"=>$lower[0]." info")
									)
								);
							}else{
								$e=0; $jlh=count($dtmill);
								foreach($dtmill as $divisi){
									if($e==round($jlh/3)){$e=0;}
									$inline_button[$e][]= array(
												"text"=>$divisi,"callback_data"=>"/tbs ".$divisi." ".$tglhi
												);
									$e++;
								}
								$inline_button[] = array(
														array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$param[1]." ".$tgllalu2),
														array("text"=>"Help","callback_data"=>$lower[0]." info")
														);
								if(getNamaOrg($param[1],'tipe')=='KEBUN'){									
									$inline_button[][]=array(
											"text"=>"Laporan Produksi TBS (pdf)",
											"callback_data"=>"/prodpdf ".$param[1]." ".$tglhi
									);					
								}						
							}
						}elseif(strlen($param[1])=='6'){
							$inline_button = array(array(
												array("text"=>"Tiket Timbang","callback_data"=>"/wb tbs ".$param[1]." ".$tglhi),
												array("text"=>"Detail Blok","callback_data"=>"/prod ".$param[1]." ".$tglhi)
											)
										);
							$inline_button[][]=array(
									"text"=>"Laporan Produksi TBS (pdf)",
									"callback_data"=>"/prodpdf ".$param[1]." ".$tglhi
							);
						}else{
							$masuk=19;
							$inline_button = array(
									array(
										array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
										array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$tgldepan)
									),
									array(
										array("text"=>$lokasitugas,"callback_data"=>$param[0]." ".$lokasitugas." ".$tglhi),
										array("text"=>"Help","callback_data"=>$lower[0]." info")
									)
								);
						}
					}elseif($tipeorg=='PABRIK'){
						$masuk=2;
						if($param[1]!='' and strlen($param[1])=='4'){
							$e=0; $jlh=count($dtkdorg);
							foreach($dtkdorg as $kdorg){
								if($e==round($jlh/3)){$e=0;}
								$xkdorg=explode("#",$kdorg);
								$inline_button[$e][]= array(
											"text"=>$xkdorg[0],"callback_data"=>$param[0]." ".$kdorg." ".$tglhi
											);
								$e++;
							}
							$inline_button[] = array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$param[1]." ".$tgllalu2),
												array("text"=>"Help","callback_data"=>$lower[0]." info")
												);
						}else if($param[1]!='' and strlen($param[1])=='6'){
							$inline_button = array(array(
												array("text"=>"Tiket Timbang","callback_data"=>"/wb tbs ".$param[1]." ".$tglhi),
												array("text"=>"Detail Blok","callback_data"=>"/prod ".$param[1]." ".$tglhi)
											)
										);	
							$inline_button[][]=array(
											"text"=>"Laporan Produksi TBS (pdf)",
											"callback_data"=>"/prodpdf ".$param[1]." ".$tglhi
									);				
						}else{
							$inline_button = array(
											array(	
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
												array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$tgldepan)
											),
											array(
												array("text"=>$lokasitugas,"callback_data"=>$param[0]." ".$lokasitugas." ".$tglhi),
												array("text"=>"Help","callback_data"=>$lower[0]." info")
											)
										);
						}
					}else{
						$masuk=3;
						if($param[1]=='' or strlen($param[1])=='4' or strlen($param[1])=='10'){
							$e=0; $jlh=count($dtmill);
							foreach($dtmill as $millcode){
								if($e==round($jlh/3)){$e=0;}
								$xkdorg=explode("#",$millcode);
								$inline_button[$e][]= array(
											"text"=>$xkdorg[0], "callback_data"=>$param[0]." ".$millcode." ".$tglhi
											);
								$e++;
							}
							if(strlen($param[1])=='10'){
								#isinya tanggal
								$inline_button[] = array(
													array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
													array("text"=>"Help","callback_data"=>$lower[0]." info")
													);
							}elseif(strlen($param[1])=='4'){
								$inline_button[] = array(
													array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$param[1]." ".$tgllalu2),
													array("text"=>"Help","callback_data"=>$lower[0]." info")
													);
								if(getNamaOrg($param[1],'tipe')=='KEBUN'){									
									$inline_button[][]=array(
											"text"=>"Laporan Produksi TBS (pdf)",
											"callback_data"=>"/prodpdf ".$param[1]." ".$tglhi
										);					
								}
							}else{
								$inline_button[] = array(
													array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
													array("text"=>"Help","callback_data"=>$lower[0]." info")
													);
							}
						}else{
							$inline_button = array(array(
												array("text"=>"Tiket Timbang","callback_data"=>"/wb tbs ".$param[1]." ".$tglhi),
												array("text"=>"Detail Blok","callback_data"=>"/prod ".$param[1]." ".$tglhi)
											)
										);	
							$inline_button[][]=array(
											"text"=>"Laporan Produksi TBS (pdf)",
											"callback_data"=>"/prodpdf ".$param[1]." ".$tglhi
										);					
						}
					}
					
					#bikin suggest
					$str="select * from ".$dbname.".setup_notification_dt where telegram='1' and kodejenis='ATBS' and karyawanid='".$karidowl."'";
					$res=fetchdata($str);
					if(count($res)==0){
						$sql="select substr(waktu,1,10) as waktu from ".$dbname.".tel_activity where telegramid = '".$telegram_id."' and text='/SENDNOTIF' and karyawanid='".$karidowl."' and full_text ='SUGGEST ATBS' order by waktu desc limit 1";
						$req=fetchdata($sql);
						if($req[0]['waktu']<date("Y-m-d")){
							$inline_button[][]=array(
									"text"=>"Berlangganan (Ya)",
									"callback_data"=>"/NOTIF #DAFTAR ATBS"
							);
						}
					}
					
				}else{
					$tab.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".strtolower($param[1])." ".$tglhi."\n";
					$inline_button = array(array(
										array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
										array("text"=>"Help","callback_data"=>$lower[0]." info")
										)
									);
				}
				
				
				
				$message_text.=$tab;
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/PRODPDF':
		$server = false;
		$folder = "imgbot/";
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>kirim</b><i> spasi </i><b>[kebun/divisi]</b><i> spasi </i><b>hi</b>
				
				Contoh :
				1. 	<i>untuk menampilkan data kebun
						SD1E periode tanggal hari ini</i>\n
						<b>".$lower[0]." kirim sd1e hi</b>\n
				2. <i>untuk menampilkan data divisi
						SD1E01 periode tanggal hari ini</i>\n
						<b>".$lower[0]." kirim sd1e01 hi</b>\n
				3. <i>untuk menampilkan data kebun
						SD1E periode bulan ?</i>\n
						<b>/prodpdf kirim [kebun] bln [thn_bln]</b>
						<b>".$lower[0]." kirim sd1e bln ".date("Ym")."</b>\n
				";
				send_reply($telegram_id, $msgid, $message_text,$inline_button);
				
				sendDocument($telegram_id,'help/upload/tel_help_prodpdf.pdf');
			break;
			default:
				$message_text="\nSilahkan pilih dengan meng-click tombol dibawah ini:\n";
				if(strlen($param[1])==4){
					$str = "select * from ".$dbname.".organisasi where kodeorganisasi like '".$param[1]."%' and tipe in ('KEBUN','AFDELING')";
					$res = fetchdata($str);
					$jlh = count($res); $e = 0;
					foreach($res as $bar){
						if($e==round($jlh/2)){$e=0;}
						$inline_button[$e][]=array(
								"text"=>$bar['namaorganisasi'],
								"callback_data"=>"/PRODPDF KIRIM ".$bar['kodeorganisasi']
						);
						$e++;
					}		
					send_reply($telegram_id, $msgid, $message_text,$inline_button);
				}elseif(strlen($param[1])==6){
					$filepdf=$folder.$param[1].".pdf";
					if (file_exists($filepdf)) {
						$message_text="Terlampir file laporan produksi TBS.\n";
						send_reply($telegram_id, $msgid, $message_text,$inline_button);
						sendDocument($telegram_id,$filepdf);
					}else{
						$message_text="File tidak ditemukan.";
						send_reply($telegram_id, $msgid, $message_text,$inline_button);
					}
				}else{
					if($tipeorg=='HOLDING' or getKary($karidowl,'kodegolongan')<='45'){ #GM keatas
						$where="and tipe in ('KEBUN')";
					}elseif($tipeorg=='KANWIL'){
						$where=" and induk = '".$kodept."' and tipe in ('KEBUN')";
					}elseif($tipeorg!='KEBUN'){
						$where=" and induk = '".$kodept."' and tipe in ('KEBUN')";
					}else{
						$where=" and kodeorganisasi like '".$lokasitugas."%' and tipe in ('KEBUN','AFDELING')";
					}
					
					$str = "select * from ".$dbname.".organisasi where 1=1 ".$where." order by inti desc, induk desc, kodeorganisasi";
					$res = fetchdata($str);
					$jlh = count($res); $e = 0;
					foreach($res as $bar){
						if($e==round($jlh/2)){$e=0;}
						$inline_button[$e][]=array(
								"text"=>$bar['namaorganisasi'],
								"callback_data"=>"/PRODPDF KIRIM ".$bar['kodeorganisasi']
						);
						$e++;
					}
					$inline_button[][]=array(
							"text"=>'Help',
							"callback_data"=>"/PRODPDF INFO"
					);					
					send_reply($telegram_id, $msgid, $message_text,$inline_button);
				}
			break;
			case'KIRIM':
				if($param[3]=='HI'){					
					$filepdf=$folder.$param[2].".pdf";
					if (file_exists($filepdf)) {
						$message_text="Terlampir file laporan produksi TBS.\n";
						send_reply($telegram_id, $msgid, $message_text,$inline_button);
						sendDocument($telegram_id,$filepdf);
					}else{
						$message_text="File tidak ditemukan.";
						send_reply($telegram_id, $msgid, $message_text,$inline_button);
					}
				}elseif($param[3]=='BLN'){
					$namafile= $folder.$param[2].$param[4].".pdf";
					$param   = "par=owlApp&periode=".substr($param[4],0,4)."-".substr($param[4],-2)."&kodeorg=".$param[2]."&namafile=".$namafile;
					$tujuan  = "bot_lappenerimaantbs.php?".$param;
					
					$url     = $urlserver.$tujuan;     
					$opts    = array('http'=>array('header'=> "User-Agent:MyAgent/1.0\r\n")); 
					$context = stream_context_create($opts);
					$html    = file_get_contents($url,false,$context);
					$html    = htmlspecialchars($html);
					if (!file_exists($namafile)) {
						$message_text.="\n<b>Data tidak ditemukan.</b>\n";
						send_reply($telegram_id, $msgid, $message_text,$inline_button);
					}else{
						$message_text="\n<b>Silahkan buka file terlampir.</b>\n";
						sendDocument($telegram_id,$namafile);
					}
				}else{
					$message_text="Silahkan pilih data produksi <b>".getNamaOrg($param[2])."</b> untuk hari ini atau produksi periode ?";
					
					$bulan = '05';
					$bulan = date('m');
					$tahun = date('Y');
					
					if(intval($bulan)>1){					
						$range = range(1,intval($bulan)-1);
						$jlh = intval($bulan)-1; $e = 0;
						foreach($range as $bulan){
							if($e==round($jlh/2)){$e=0;}
							$inkeyboard[$e][]=array('text'=>'Periode '.numToMonth($bulan,'I','long')." ".$tahun,'callback_data'=>'/PRODPDF KIRIM '.$param[2].' BLN '.$tahun.addZero($bulan,2));
							$e++;
						}
					}
					$inkeyboard[] = array(
										array('text' =>'Dari 01 s/d '.tanggalbulan(date('Y-m-d')), 'callback_data' => '/PRODPDF KIRIM '.$param[2].' HI')
									);
					editMessageText($telegram_id, $msgid, $message_text, $inkeyboard, true);
				}
			break;
		}
		
	break;
	case'/PROD':
		$str = "select a.notransaksi, b.nospb, b.posting, a.kodeorg, a.millcode, a.divcode, (beratbersih) as kg, jumlahtandan1
		from ".$dbname.".pabrik_timbangan a
		left join ".$dbname.".kebun_spbht b on a.nospb=b.nospb
		where a.tanggal like '".$param[2]."%' and a.kodebarang='40000003'";
		$res=fetchdata($str);
		foreach($res as $bar){
			if(is_null($bar['nospb'])){
				$belumpost[$bar['kodeorg']]+=1;
			}elseif($bar['posting']=='0'){				
				$belumpost[$bar['kodeorg']]+=1;
			}
		}
		
		$str = "select * from ".$dbname.".kebun_spb_vw where tanggal like '".$param[2]."%' and divisi like '".$param[1]."%' order by tahuntanam asc, blok";
		$res = fetchdata($str);
		foreach($res as $bar){
			$data[$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
			$kg[$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
			$jjg[$bar['tahuntanam']][$bar['blok']]+=$bar['jjg'];
		}
		
		if(count($data)>0){
			foreach($data as $tt => $v1){
				$no=0;
				$tab.="Tahun Tanam : <b>".$tt."</b>\n";
				foreach($v1 as $blok){
					$no++;
					$tab.="    ".$no.". ".getNamaOrg($blok)." : <b>".hidezerodecimal($kg[$tt][$blok])."</b> Kg, <b>".hidezerodecimal($jjg[$tt][$blok])."</b> Jjg (BJR ".hidezerodecimal($kg[$tt][$blok]/$jjg[$tt][$blok],2).")\n";
					
					$ttlkg[$tt]+=$kg[$tt][$blok];
					$ttljjg[$tt]+=$jjg[$tt][$blok];
					
					$gtkg+=$kg[$tt][$blok];
					$gtjjg+=$jjg[$tt][$blok];
				}
				$tab.="Total TT ".$tt." : <b>".hidezerodecimal($ttlkg[$tt])."</b> Kg, <b>".hidezerodecimal($ttljjg[$tt])."</b> Jjg (BJR ".hidezerodecimal($ttlkg[$tt]/$ttljjg[$tt],2).")\n\n";
			}
			
			$tab.="Grand Total : <b>".hidezerodecimal($gtkg)."</b> Kg, <b>".hidezerodecimal($gtjjg)."</b> Jjg (BJR ".hidezerodecimal($gtkg/$gtjjg,2).")\n";
			
			$message_text=$tab;
		}else{			
			$message_text="Data produksi tidak ditemukan, <b>harap dipastikan SPB sudah diinput dan diposting.</b> \u{203C}";
		}
		
		if($belumpost[substr($param[1],0,4)]>0){
			$message_text.="\n\nUntuk kebun ".getNamaOrg(substr($param[1],0,4))." terdapat ".$belumpost[substr($param[1],0,4)]." tiket timbang yang belum diinput dan diposting SPB nya. \u{203C}";
		}
		
		
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/CPO':
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>(1)</b><i> spasi </i><b>(2)</b>
				<b>(1) = Kosong / Mill / thn / thn-bln / thn-bln-tgl</b>
				<b>(2) = Kosong / thn / thn-bln / thn-bln-tgl</b>
				Contoh :
				1. ".$lower[0]."
				2. ".$lower[0]." ".date("Y")."
				3. ".$lower[0]." ".date("Y-m")."
				4. ".$lower[0]." ".date("Y-m-d")."
				5. ".$lower[0]." sdkm
				6. ".$lower[0]." sdkm ".date("Y")."
				7. ".$lower[0]." sdkm ".date("Y-m")."
				8. ".$lower[0]." sdkm ".date("Y-m-d")."
				";
			break;
			default:
				if($param[1]!=''){
					$s = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param[1]."'";
					$r = fetchdata($s);
					foreach($r as $b){
						$nmorg=$b['kodeorganisasi'];
					}
					if($nmorg!=''){
						$wh.=" and millcode like '".substr($param[1],0,4)."%'"; $pks=substr($param[1],0,4);
						if($param[2]!=''){					
							$tglhi = $param[2];			
						}else{					
							$tglhi = date("Y-m-d");
						}
					}else{
						$tglhi = $param[1];			
					}
				}else{				
					$str = "select max(substr(tanggal,1,10)) as tanggal from ".$dbname.".pabrik_timbangan where kodebarang='40000001'";
					$res = fetchdata($str);
					$tglhi = $res[0]['tanggal'];
				}
				
				$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tglhi)));
				$tgldepan = date('Y-m-d', strtotime('+1 days', strtotime($tglhi)));
				$str = "select sum(beratbersih) as kg,count(notransaksi) as rit, namatransportir,millcode from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' and kodebarang='40000001' ".$wh." group by namatransportir,millcode";
				$res=fetchdata($str);
				foreach($res as $bar){
					$data[$bar['millcode']][$bar['namatransportir']]+=$bar['kg'];
					$rit[$bar['millcode']][$bar['namatransportir']]+=$bar['rit'];
					$datamill[$bar['millcode']]=$bar['millcode'];					
				}
				if(count($data)>0){				
					$message_text="Pengiriman ".$param[0]." Periode : ".$tglhi."\n";
					foreach($data as $millcode => $key){
						$no=0;
						$message_text.="<b>".$millcode."</b>\n";
						foreach($key as $nodo => $kg){
							$no++;
							$message_text.="   ".$no.". ".$nodo." : ".hidezerodecimal($kg)." Kg (".$rit[$millcode][$nodo]." Rit)\n";
							$stpks[$millcode]['kg']+=$kg;
							$stpks[$millcode]['rit']+=$rit[$millcode][$nodo];
							$gt['kg']+=$kg;
							$gt['rit']+=$rit[$millcode][$nodo];
						}
						$message_text.="Total ".$millcode." : ".hidezerodecimal($stpks[$millcode]['kg'])." Kg (".$stpks[$millcode]['rit']." Rit)\n\n";
					}
					$message_text.="Grand Total : ".hidezerodecimal($gt['kg'])." Kg (".$gt['rit']." Rit)\n";
					
					if(count($datamill)=='1'){$pks=$millcode; $nmill=1;}else{$nmill=count($datamill);}
					$extgl=explode("-",$tglhi);
					if($param[1]=='' or ($nmill>1 and strlen($tglhi)=='10')){
						$e=0; $jlh=count($datamill);
						foreach($datamill as $mill){
							if($e==round($jlh/3)){$e=0;}
							$inline_button[$e][]= array(
										"text"=>$mill,"callback_data"=>$param[0]." ".$mill." ".$tglhi
										);
							$e++;
						}
						
						$inline_button[$e][] = array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2);
						$inline_button[$e][] = array("text"=>"Help","callback_data"=>$lower[0]." info");
					}elseif((checkdate($extgl[1],$extgl[2],$extgl[0]) and $pks!='') or ($nmill==1 and strlen($tglhi)=='10')){
						#jika tanggal dan panjang 10
						#jika pks
						#jika cuma 1 pks
						$inline_button = array(
									array(
										array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
										array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$tgldepan)
									),
									array(
										array("text"=>"Tiket Timbang","callback_data"=>"/wb cpo ".$pks." ".$tglhi),
										array("text"=>"Help","callback_data"=>$lower[0]." info")
									)
								);
					}else{							
						$inline_button = array(array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
												array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$tgldepan)
												),
											array(
												array("text"=>"Help","callback_data"=>$lower[0]." info")
											)
										);
					}
				}else{
					$message_text.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$pks." ".$tglhi."\n";
					$inline_button = array(array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
												array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$tgldepan)
												),
											array(
												array("text"=>"Help","callback_data"=>$lower[0]." info")
											)
										);
				}
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/KER':
		switch($param[1]){
			case'INFO':
				$message_text.="\nKetik : ".$lower[0]."<i> spasi </i><b>(1)</b><i> spasi </i><b>(2)</b>
				<b>(1) = Kosong / Mill / thn / thn-bln / thn-bln-tgl</b>
				<b>(2) = Kosong / thn / thn-bln / thn-bln-tgl</b>
				Contoh :
				1. ".$lower[0]."
				2. ".$lower[0]." ".date("Y")."
				3. ".$lower[0]." ".date("Y-m")."
				4. ".$lower[0]." ".date("Y-m-d")."
				5. ".$lower[0]." sdkm
				6. ".$lower[0]." sdkm ".date("Y")."
				7. ".$lower[0]." sdkm ".date("Y-m")."
				8. ".$lower[0]." sdkm ".date("Y-m-d")."
				";
			break;
			default:
				if($param[1]!=''){
					$s = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param[1]."'";
					$r = fetchdata($s);
					foreach($r as $b){
						$nmorg=$b['kodeorganisasi'];
					}
					if($nmorg!=''){
						$wh.=" and millcode like '".substr($param[1],0,4)."%'"; $pks=substr($param[1],0,4);
						if($param[2]!=''){
							$tglhi = $param[2];			
						}else{					
							$tglhi = date("Y-m-d");
						}
					}else{
						$tglhi = $param[1];			
					}
				}else{				
					$str = "select max(substr(tanggal,1,10)) as tanggal from ".$dbname.".pabrik_timbangan where kodebarang='40000002'";
					$res = fetchdata($str);
					$tglhi = $res[0]['tanggal'];
				}
				$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tglhi)));
				$tgldepan = date('Y-m-d', strtotime('+1 days', strtotime($tglhi)));
				$str = "select pengirim,sum(beratbersih) as kg,count(notransaksi) as rit, namatransportir,millcode from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' and kodebarang='40000002' ".$wh." group by namatransportir,millcode,pengirim";
				$res=fetchdata($str);
				foreach($res as $bar){
					if($bar['pengirim']!='' and $bar['pengirim']!=$bar['millcode']){
						#terima
						$data['terima'][$bar['millcode']][$bar['pengirim']]=$bar['pengirim'];
						$kg['terima'][$bar['millcode']][$bar['pengirim']]+=$bar['kg'];
						$rit['terima'][$bar['millcode']][$bar['pengirim']]+=$bar['rit'];				
					}elseif($bar['pengirim']==$bar['millcode']){
						$data['jual'][$bar['millcode']][$bar['pengirim']]=$bar['pengirim'];
						$kg['jual'][$bar['millcode']][$bar['pengirim']]+=$bar['kg'];
						$rit['jual'][$bar['millcode']][$bar['pengirim']]+=$bar['rit'];				
					}else{
						#kirim
						$data['kirim'][$bar['millcode']][$bar['namatransportir']]=$bar['namatransportir'];
						$kg['kirim'][$bar['millcode']][$bar['namatransportir']]+=$bar['kg'];
						$rit['kirim'][$bar['millcode']][$bar['namatransportir']]+=$bar['rit'];				
					}
					$datamill[$bar['millcode']]=$bar['millcode'];
				}
				if(count($data)>0){				
					$message_text="Pengiriman dan Penerimaan Kernel\nPeriode : ".$tglhi."\n\n";
					foreach($data as $status => $key){
						$message_text.="<b>".strtoupper($status)." :</b>\n";
						foreach($key as $millcode => $key1){
							$no=0;
							$message_text.="   <b>".$millcode."</b>\n";
							foreach($key1 as $nodo){
								$no++;
								$message_text.="   ".$no.". ".$nodo." : ".hidezerodecimal($kg[$status][$millcode][$nodo])." Kg (".$rit[$status][$millcode][$nodo]." Rit)\n";
								$stpks[$status][$millcode]['kg']+=$kg[$status][$millcode][$nodo];
								$stpks[$status][$millcode]['rit']+=$rit[$status][$millcode][$nodo];
								$gt[$status]['kg']+=$kg[$status][$millcode][$nodo];
								$gt[$status]['rit']+=$rit[$status][$millcode][$nodo];
							}
							$message_text.="Total ".$millcode." : ".hidezerodecimal($stpks[$status][$millcode]['kg'])." Kg (".$stpks[$status][$millcode]['rit']." Rit)\n";
						}
						$message_text.="<b>Total ".strtoupper($status)." : ".hidezerodecimal($gt[$status]['kg'])." Kg (".$gt[$status]['rit']." Rit)</b>\n\n";
					}
					
					if(count($datamill)=='1'){$pks=$millcode; $nmill=1;}else{$nmill=count($datamill);}
					$extgl=explode("-",$tglhi);
					if($param[1]=='' or ($nmill>1 and strlen($tglhi)=='10')){
						$e=0; $jlh=count($datamill);
						foreach($datamill as $mill){
							if($e==round($jlh/3)){$e=0;}
							$inline_button[$e][]= array(
										"text"=>$mill,"callback_data"=>$param[0]." ".$mill." ".$tglhi
										);
							$e++;
						}
						
						$inline_button[$e][] = array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2);
						$inline_button[$e][] = array("text"=>"Help","callback_data"=>$lower[0]." info");
					}elseif((checkdate($extgl[1],$extgl[2],$extgl[0]) and $pks!='') or ($nmill==1 and strlen($tglhi)=='10')){
						#jika tanggal dan panjang 10
						#jika pks
						#jika cuma 1 pks
						$inline_button = array(
									array(
										array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
										array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$tgldepan)
									),
									array(
										array("text"=>"Tiket Timbang","callback_data"=>"/wb ker ".$pks." ".$tglhi),
										array("text"=>"Help","callback_data"=>$lower[0]." info")
									)
								);
					}else{							
						$inline_button = array(array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
												array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$tgldepan)
												),
											array(
												array("text"=>"Help","callback_data"=>$lower[0]." info")
											)
										);
					}
				}else{
					$message_text.="Data tidak ada, click Help untuk bantuan.\n ".$lower[0]." ".$pks." ".$tglhi."\n";
					$inline_button = array(array(
												array("text"=>$tgllalu2,"callback_data"=>$lower[0]." ".$tgllalu2),
												array("text"=>$tgldepan,"callback_data"=>$lower[0]." ".$tgldepan)
												),
											array(
												array("text"=>"Help","callback_data"=>$lower[0]." info")
											)
										);
				}
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/MYID':
		switch($param[1]){
			case'#SAVEPINDAH':
				$str="update ".$dbname.".user set kodeorg='".$param[2]."' where karyawanid='".$karidowl."'";	
				try{
					$owlPDO->exec($str);
					$message_text= "<b>Data sudah diupdate</b>\n";	
					$message_text= "<b>Lokasi tugas anda ".$param[2].".</b>\n";	
					$inline_button = array(array(array("text"=>"MyID","callback_data"=>$lower[0]." #")));					
					}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
			break;
			case'#YAKINPINDAH':
				$message_text= "<b>Apakah anda yakin ingin pindah ke ".$param[2]." ???</b>\n";
				$inline_button = array(array(
						array("text"=>"Pindah ke ".$param[2]." (YA)","callback_data"=>$lower[0]." #SAVEPINDAH ".$param[2].""),
						array("text"=>"Batalkan","callback_data"=>$lower[0]." #"))
					);
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);	
			break;
			case'#PINDAH':
				$str = "select * from ".$dbname.".user_orgdetail where namauser='".$userowl."' and kodeorganisasi!='".$lokasitugas."'";
				$res = fetchdata($str);					
				$message_text.="<b>Silahkan pilih ke Unit mana anda akan pindah :</b>\n";
				$e=0; $jlh=count($res);
				if($jlh>0){					
					foreach($res as $bar){
						if($e==round($jlh/4)){$e=0;}
						$inline_button[$e][]= array(
									"text"=>$bar['kodeorganisasi'],"callback_data"=>$param[0]." #YAKINPINDAH ".$bar['kodeorganisasi']
									);
						$e++;
					}
				}else{
					$message_text= "Detail akses anda belum di setting, silahkan hubungi Administrator.\n";
				}
				editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
			break;
			default:
				$str = "select * from ".$dbname.".admin_list where username ='".$userowl."'";
				$res = fetchdata($str);
				$admin="Tidak";
				if(count($res)>0){
					$admin="Ya";
				}
				
				$s = "select * from ".$dbname.".datakaryawan where karyawanid='".$karidowl."'";
				$r=fetchdata($s);

				$str = "select * from ".$dbname.".sdm_5jabatan where kodejabatan='".$r[0]['kodejabatan']."'";
				$jab=fetchdata($str);
				
				$str = "select * from ".$dbname.".sdm_5departemen where kode='".$r[0]['bagian']."'";
				$dep=fetchdata($str);
				
				$message_text= "<b>Data User :</b>\n";
				$message_text.= "Username : <b>".$userowl."</b>\n";
				$message_text.= "Lokasi User : <b>".$lokasitugas."</b>\n";
				$message_text.= "Admin : <b>".$admin."</b>\n";
				#$message_text.= "Telegram ID : <i>".$telegram_id."</i>\n";
				#$message_text.= "Telegram User : <b>".$telegram_uname."</b>\n";
				
				$message_text.= "\n<b>Data Karyawan :</b>\n";
				$message_text.= "NIK : ".$r[0]['nik']."\n";
				$message_text.= "Nama : ".$r[0]['namakaryawan']."\n";
				$message_text.= "Lokasi Tugas : ".$r[0]['lokasitugas']."\n";
				$message_text.= "Jabatan : ".$jab[0]['namajabatan']."\n";
				$message_text.= "Dept : ".$dep[0]['nama']."\n";
				$message_text.= "Tgl Masuk : ".$r[0]['tanggalmasuk']."\n";
				$message_text.= "Alamat : ".$r[0]['alamataktif']."\n";
				$message_text.= "Phone : ".$r[0]['nohp']."\n";
				$message_text.= "e-mail : ".$r[0]['email']."\n";
				$message_text.= "BPJS Kes : ".$r[0]['bpjs']."\n";
				$message_text.= "Jamsostek : ".$r[0]['jms']."\n";
				$message_text.= "Pensiun : ".$r[0]['pensiun']."\n";
				
				
				$str = "select * from ".$dbname.".auth where namauser='".$userowl."' and menuid='737'";
				$res=fetchdata($str);
				if(count($res)>0){			
					$inline_button = array(array(array("text"=>"Pindah Lokasi Tugas ?","callback_data"=>$lower[0]." #PINDAH")));
				}
				if($param[1]==''){					
					send_reply($telegram_id, $msgid, $message_text,$inline_button);
				}else{					
					editMessageText($telegram_id, $msgid, $message_text, $inline_button, false);
				}
			break;
		}
	break;
	case'REG':
		$userowl = trim($text[1]);
		$passowl = trim($text[2]);
		if($userowl=='' or $passowl==''){
			$message_text = "Format salah, format yang benar adalah ".$formreg."";
		}else{
			#cek dulu sudah pernah terdaftar di user lain belum ?
			$str = "select * from ".$dbname.".user   where telegramid='".$telegram_id."' and namauser!='".$userowl."'";
			$res=fetchdata($str);
			if(count($res)>0){
				$message_text = "Telegram <b><i>".$telegram_uname."</i></b> sudah terdaftar untuk user :\n";
				foreach($res as $bar){					
					$message_text .= " <b>".$bar['namauser']."</b>\n";
					$nuser=$bar['namauser'];
				}
				$message_text .= "silahkan unreg terlebih dahulu dengan ketik \n<b>UNREG</b> spasi <b>USER_OWL</b> spasi <b>PASS_OWL</b>\ncontoh : <b>unreg ".$nuser." 123456</b>\n";
			}else{
				switch($tipelogin){
					case'AD':
						#ini jika pake AD
						/* $ldap_hostname = "10.1.1.34";
						$ldap_port = "389";
						$ldap_dn = "cn=admin,dc=ir-group,dc=local";
						$ldap_search = "dc=ir-group,dc=local";
						$ldap_password ="Pa55w0rd";
						
						$uname   =addslashes($userowl);
						$password=addslashes($passowl);
						
						$ldap_con = ldap_connect($ldap_hostname,$ldap_port);
						ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
						ldap_set_option($ldap_con, LDAP_OPT_REFERRALS, 0);
						ldap_bind($ldap_con, $ldap_dn, $ldap_password);
						
						$filter="(samaccountname=".$uname.")";
						$dn=$ldap_search;
						$res = ldap_search($ldap_con, $ldap_search, $filter);
						ldap_sort($ldap_con,$res,"sn");
						$info = ldap_get_entries($ldap_con, $res);
						$first = ldap_first_entry($ldap_con, $res);
						$ldap_Userdn = ldap_get_dn($ldap_con, $first);
						
						$bind = @ldap_bind($ldap_con, $ldap_Userdn, $password); */
						
						# ad nya sering gagal, yang penting ada usernya di table user = masuk
						$bind=true;
						if($bind){
							/* for ($i=0; $i<$info["count"]; $i++){
								if($info['count'] > 1)
									break;
								$nikkar = $info[$i]["description"][0]; 
							}
							@ldap_close($ldap); */
							
							$str="select * from ".$dbname.".user where namauser='".$userowl."'";
							$res=fetchdata($str);
							$count = count($res);
							if($count>0){
								#masuk
								foreach($res as $bar){
									if($bar['telegramid']!='' and $bar['telegramstatus']=='1'){
										if($bar['telegramid']!=$telegram_id){							
											$message_text = "User sudah terdaftar dengan nomor telegram lain.";						
										}elseif($bar['telegramid']==$telegram_id){							
											$message_text = "Anda sudah terdaftar.";
											$message_text .= "\n\nUntuk memulai silahkan click tombol <b>MENU</b> dibawah\n";
											$inline_button = array(array(array("text"=>"Menu","callback_data"=>"/menu")));
											
											/* $message_text.= "\nmulailah dengan mengetikan :\n";
											$msg = listperintah();
											foreach($msg as $key => $bar){
												if($bar['group']=='0'){$e="(Private)";}else{$e='';}
												if($bar['admin']=='0'){				
													$message_text.="<b>".strtolower($bar['id'])."</b> => <i>".$bar['ket']." ".$e."</i>\n";
												}
											} */
										}
									}else{
										$query=" update ".$dbname.".user set telegramid='".$telegram_id."',telegramuser='".$telegram_uname."', telegramstatus='1',first_name='".$first_name."',last_name='".$last_name."' where namauser='".$userowl."'";
										try{$owlPDO->exec($query);
											$query=" update ".$dbname.".user set telegramid='',telegramuser='', telegramstatus='',first_name='',last_name='' where telegramid='".$telegram_id."' and namauser!='".$userowl."'";
											try{$owlPDO->exec($query);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
										}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
										
										$message_text="Register (REG) user <b>".$userowl."</b> BERHASIL\n\nuntuk membatalkan ketik ".$formunreg."";
										$message_text.="\n\nUntuk memulai silahkan click tombol <b>MENU</b> dibawah\n";
										$inline_button = array(array(array("text"=>"Menu","callback_data"=>"/menu")));
										
										/* $msg = listperintah();
										foreach($msg as $key => $bar){
											if($bar['group']=='0'){$e="(Private)";}else{$e='';}
											if($bar['admin']=='0'){				
												$message_text.="<b>".strtolower($bar['id'])."</b> => <i>".$bar['ket']." ".$e."</i>\n";
											}
										} */
									}
								}
							}else{
								#exit
								$message_text = "Anda belum terdaftar di user OWL, silahkan login ke OWL melalui http://owl.ksp-agro.com";
							}
						}else{
							$message_text = "Data user tidak ditemukan, pastikan user dan password yg anda masukkan sudah benar.";
						}
						#batas jika AD
					break;
					case'NONAD':
						#jika bukan AD
						$str = "select * from ".$dbname.".user   where namauser='".$userowl."'  and password=PASSWORD('".$passowl."')";
						$res=fetchdata($str);
						if(count($res)==0){
							$message_text = "Data user tidak ditemukan, pastikan user dan password yg anda masukkan sudah benar.";
						}else{		
							foreach($res as $bar){
								if($bar['telegramid']!='' and $bar['telegramstatus']=='1'){
									if($bar['telegramid']!=$telegram_id){							
										$message_text = "User sudah terdaftar dengan nomor telegram lain.";						
									}elseif($bar['telegramid']==$telegram_id){							
										$message_text = "Anda sudah terdaftar.";
										$message_text .= "\n\nUntuk memulai silahkan click tombol <b>MENU</b> dibawah\n";
										$inline_button = array(array(array("text"=>"Menu","callback_data"=>"/menu")));
										/* $message_text.= "\nmulailah dengan mengetikan :\n";
										$msg = listperintah();
										foreach($msg as $key => $bar){
											if($bar['group']=='0'){$e="(Private)";}else{$e='';}
											if($bar['admin']=='0'){				
												$message_text.="<b>".strtolower($bar['id'])."</b> => <i>".$bar['ket']." ".$e."</i>\n";
											}
										} */
									}
								}else{
									$query=" update ".$dbname.".user set telegramid='".$telegram_id."',telegramuser='".$telegram_uname."', telegramstatus='1',first_name='".$first_name."',last_name='".$last_name."' where namauser='".$userowl."'";
									try{$owlPDO->exec($query);
										$query=" update ".$dbname.".user set telegramid='',telegramuser='', telegramstatus='',first_name='',last_name='' where telegramid='".$telegram_id."' and namauser!='".$userowl."'";
										try{$owlPDO->exec($query);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
									}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
									
									$message_text = "Register (REG) user <b>".$userowl."</b> BERHASIL\n\nuntuk membatalkan ketik ".$formunreg."";
									$message_text .= "\n\nUntuk memulai silahkan click tombol <b>MENU</b> dibawah\n";
									$inline_button = array(array(array("text"=>"Menu","callback_data"=>"/menu")));
									
									/* $message_text .= "/menu - Daftar Menu\n";
									$msg = listperintah();
									foreach($msg as $key => $bar){
										if($bar['group']=='0'){$e="(Private)";}else{$e='';}
										if($bar['admin']=='0'){				
											$message_text.="<b>".strtolower($bar['id'])."</b> => <i>".$bar['ket']." ".$e."</i>\n";
										}
									} */
								}
							}
						}
						#jika bukan AD
					break;
				}	
			} #tutup switch
		}
		
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
		if($param[1]!='' and $param[2]!=''){		
			delpesan($telegram_id, $msgid);
		}
	break;
	case'UNREG':
		$userowl = $text[1];
		$passowl = $text[2];
		if($userowl=='' or $passowl==''){
			$message_text = "Format salah, format yang benar adalah ".$formunreg."";
		}else{
			switch($tipelogin){
				case'AD':
					#ini jika pake AD
					/* $ldap_hostname = "10.1.1.34";
					$ldap_port = "389";
					$ldap_dn = "cn=admin,dc=ir-group,dc=local";
					$ldap_search = "dc=ir-group,dc=local";
					$ldap_password ="Pa55w0rd";
					
					$uname   =addslashes($userowl);
					$password=addslashes($passowl);
					
					$ldap_con = ldap_connect($ldap_hostname,$ldap_port);
					ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option($ldap_con, LDAP_OPT_REFERRALS, 0);
					ldap_bind($ldap_con, $ldap_dn, $ldap_password);
					
					$filter="(samaccountname=".$uname.")";
					$dn=$ldap_search;
					$res = ldap_search($ldap_con, $ldap_search, $filter);
					ldap_sort($ldap_con,$res,"sn");
					$info = ldap_get_entries($ldap_con, $res);
					$first = ldap_first_entry($ldap_con, $res);
					$ldap_Userdn = ldap_get_dn($ldap_con, $first);
					
					$bind = @ldap_bind($ldap_con, $ldap_Userdn, $password); */
					
					$bind=true;
					if($bind){
						/* for ($i=0; $i<$info["count"]; $i++){
							if($info['count'] > 1)
								break;
							$nikkar = $info[$i]["description"][0]; 
						}
						@ldap_close($ldap); */
						
						$str="select * from ".$dbname.".user where namauser='".$userowl."'";
						$res=fetchdata($str);
						$count = count($res);
						if($count>0){
							#masuk
							foreach($res as $bar){
								$query=" update ".$dbname.".user set telegramid='',telegramuser='', telegramstatus='',first_name='',last_name='' where namauser='".$userowl."'";
								try{$owlPDO->exec($query);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
								
								$message_text = "Unregister (UNREG) user <b>".$userowl."</b> BERHASIL\n\nuntuk mendaftar kembali ketik ".$formreg."";
							}
						}else{
							#exit
							$message_text = "Anda belum terdaftar di user OWL, silahkan login ke OWL melalui http://owl.ksp-agro.com";
						}
					}else{
						$message_text = "Data user tidak ditemukan, pastikan user dan password yg anda masukkan sudah benar.";
					}
					#batas jika AD
					
				break;
				case'NONAD':
					#batas non AD
					$str = "select * from ".$dbname.".user   where namauser='".$userowl."'  and password=PASSWORD('".$passowl."')";
					$res=fetchdata($str);
					if(count($res)==0){
						$message_text = "Data user tidak ditemukan, pastikan user dan password yg anda masukkan sudah benar.";
					}else{		
						foreach($res as $bar){
							$query=" update ".$dbname.".user set telegramid='',telegramuser='', telegramstatus='',first_name='',last_name='' where namauser='".$userowl."'";
							try{$owlPDO->exec($query);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
							
							$message_text = "Unregister (UNREG) user <b>".$userowl."</b> BERHASIL\n\nuntuk mendaftar kembali ketik ".$formreg."";
						}
					}
					#batas non AD
				break;
			}
			
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
		if($param[1]!='' and $param[2]!=''){		
			delpesan($telegram_id, $msgid);
		}
	break;
	case'STATUSID':
		if($param[1]=='TELE'){				
			$message_text = "Status telegram anda <b>non aktif</b>\nsilahkan hubungi Administrator.\n";
		}
		if($param[1]=='USER'){
			$message_text = "Status user OWL anda <b>non aktif</b>\nsilahkan hubungi Administrator.\n";
		}
		
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'NOID':
		if($first_name=='' or $last_name==''){
			if($text_ori['message']['from']['username']==''){
				$first_name = $text_ori['message']['from']['first_name'];				
				$last_name = $text_ori['message']['from']['last_name'];				
			}else{
				$first_name = $text_ori['message']['from']['username'];				
			}
		}
		// $first_name = $text_ori['message']['new_chat_participant']['first_name'];
		// $last_name = $text_ori['message']['new_chat_participant']['last_name'];
		
		$message_text.="Selamat datang <b>".$first_name." ".$last_name."</b>,\nsilahkan mendaftar dahulu,\ndengan cara ketik :\n".$formreg."\n\n<i>Note : Kirim hanya melalui pesan Private,\njangan melalui pesan Group, terima kasih.</i>\n";
		
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/START':
		$str = "select * from ".$dbname.".admin_list where username ='".$userowl."'";
		$res = fetchdata($str);
		$admin=false;
		if(count($res)>0){
			$admin=true;
		}
		$message_text = "Please select...\n\n";
		$message_text .= "<b>/menu</b> - Daftar Menu\n";
		$msg = listperintah();
		foreach($msg as $key => $bar){
			if($bar['group']=='0'){$e="(Private)";}else{$e='';}
			if($admin==true){
				if($bar['admin']=='1'){$n="(admin)";}else{$n="";}
				$message_text.="<b>".strtolower($bar['id'])."</b> - <i>".$bar['ket']." ".$e." <b>".$n."</b></i>\n";
			}else{				
				if($bar['admin']=='0'){				
					$message_text.="<b>".strtolower($bar['id'])."</b> - <i>".$bar['ket']." ".$e." <b>".$n."</b></i>\n";
				}
			}
		}
		$inline_button[] = array(array("text"=>"Help","url"=>"https://drive.google.com/drive/folders/16Kns53qsSjim9fK4oGengz8pF5hq4x5k?usp=sharing"));
		
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'/MENU':
		$cpo=$ker="";
		$str = "select max(substr(tanggal,1,10)) as tanggal, kodebarang from ".$dbname.".pabrik_timbangan where kodebarang in ('40000001','40000002') and tanggal like '".date("Y")."%' group by kodebarang limit 2";
		$res=fetchdata($str);
		if(count($res)>0){			
			foreach($res as $bar){
				if($bar['kodebarang']=='40000001'){
					$cpo="/cpo ".$bar['tanggal'];
				}
				if($bar['kodebarang']=='40000002'){
					$ker="/ker ".$bar['tanggal'];
				}
			}
		}
		$message_text .= "Info :\n";
		$message_text .= "<i><b>(P)</b> : Private (Hanya u/ pesan Private / Japri)</i>\n";
	
		$str = "select * from ".$dbname.".admin_list where username ='".$userowl."'";
		$res = fetchdata($str);
		$admin=false;
		if(count($res)>0){
			$admin=true;
			$message_text .= "<i><b>(A)</b> : Admin (Hanya untuk Administrator)</i>\n";
		}
		$msg = listperintah();
		$r=0; $jlh=count($msg);
		foreach($msg as $key => $bar){
			if($ker!=''){if(strtolower($bar['id'])=='/ker'){$bar['id']=$ker;}}
			if($cpo!=''){if(strtolower($bar['id'])=='/cpo'){$bar['id']=$cpo;}}
			
			if($bar['group']=='0'){$e="(P)";}else{$e='';}
			if($admin==true){
				$nomor++;
				if($bar['admin']=='1'){$n="(A)";}else{$n="";}
				#$message_text.="<b>".strtolower($bar['id'])."</b> - <i>".$bar['ket']." ".$e." <b>".$n."</b></i>\n";
				
				if($r==round($jlh/1)){$r=0;}
				$inline_button[$r][]= array(
							"text"=>$nomor.". ".$bar['ket']." ".$e." ".$n,"callback_data"=>strtolower($bar['id'])
							);
				$r++;
			}else{				
				if($bar['admin']=='0'){
					$nomor++;
					#$message_text.="<b>".strtolower($bar['id'])."</b> - <i>".$bar['ket']." ".$e." <b>".$n."</b></i>\n";
					if($r==round($jlh/1)){$r=0;}
					$inline_button[$r][]= array(
								"text"=>$nomor.". ".$bar['ket']." ".$e." ".$n,"callback_data"=>strtolower($bar['id'])
								);
					$r++;
				}
			}
		}
		$inline_button[] = array(array("text"=>"Help","url"=>"https://drive.google.com/drive/folders/16Kns53qsSjim9fK4oGengz8pF5hq4x5k?usp=sharing"));
		
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	case'AKSES':
		switch($param[1]){
			case'GROUP':
				$message_text="Perintah ini tidak bisa diakses lewat group, silahkan kirim pesan secara <b>Private</b>.\n";
			break;
			case'ADMIN':
				$message_text="Perintah ini hanya untuk <b>Administrator</b>.\n";
			break;
			case'NONAKTIF':
				$message_text="Perintah ini non aktif.\n";
			break;
			default:
				$message_text="NOT AUTHORIZED.\n";
			break;
		}
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
	default:
		$server=false;
		$message_text = getOpenAI(substr($val['message']['text'],8,9999));
		send_reply($telegram_id, $msgid, $message_text,$inline_button);
		
		
		// $message_text = "Perintah <b>".$lower[0]."</b> tidak tersedia,\ncoba mulai dengan :\n";
		// $msg = listperintah();
		// foreach($msg as $key => $bar){
			// if($bar['group']=='0'){$e="(Private)";}else{$e='';}
			// if($admin==true){
				// if($bar['admin']=='1'){$n="(admin)";}else{$n="";}
				// $message_text.="<b>".strtolower($bar['id'])."</b> - <i>".$bar['ket']." ".$e." <b>".$n."</b></i>\n";
			// }else{				
				// if($bar['admin']=='0'){				
					// $message_text.="<b>".strtolower($bar['id'])."</b> - <i>".$bar['ket']." ".$e." <b>".$n."</b></i>\n";
				// }
			// }
		// }
		// send_reply($telegram_id, $msgid, $message_text,$inline_button);
	break;
}
	
if($telegram_msg!=''){
	#catat perintah apa saja yg di minta user
	telActivity($val,$text_ori,$message_text,$inline_button);
}

function getOpenAI($prompt){
	$dTemperature = 0.9;
	$iMaxTokens = 150;
	$top_p = 1;
	$frequency_penalty = 0.0;
	$presence_penalty = 0.6;
	$OPENAI_API_KEY = "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";
	$sModel = "text-davinci-003";
	// $prompt = "Siapakah nama presiden indonesia saat ini?";
	$ch = curl_init();
	$headers  = [
		'Accept: application/json',
		'Content-Type: application/json',
		'Authorization: Bearer '.$OPENAI_API_KEY.''
	];
	$postData = [
		'model' => $sModel,
		'prompt' => str_replace('"', '', $prompt),
		'temperature' => $dTemperature,
		'max_tokens' => $iMaxTokens,
		'top_p' => $top_p,
		'frequency_penalty' => $frequency_penalty,
		'presence_penalty' => $presence_penalty,
		'stop' => '[" Human:", " AI:"]',
	];
	 
	 
	curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); 
	 
	$result = curl_exec($ch);
	$decoded_json = json_decode($result, true);
	 
	return($decoded_json['choices'][0]['text']);
}
function getTab2($jumlah){	
	$tab="";
	for($i=1;$i<=$jumlah;$i++){
		$tab.=" ";
	}
	
	return $tab;
}


function my_operator($a, $b, $char) {
	switch($char) {
		case '=': return $a == $b;
		case '<=': return $a <= $b;
		case '>=': return $a >= $b;
		case '<': return $a < $b;
		case '>': return $a > $b;
	}
}

function numbertohuruf($no){
	$range=range("A","Z");
	foreach($range as $n => $huruf){
		if(($n+1)==$no){
			return $huruf;
		}
	}
}
?>