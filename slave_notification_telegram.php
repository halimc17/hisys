<?
$tiap15menit=array('14','29','44','59');

if(date("H")>"04"){
	if(in_array(date('i'),$tiap15menit)){	
		include('bot_graph.php');
		include('bot_lappenerimaantbs.php');
	}

	include_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include_once('lib/zFunction.php');


	$tgllalu = date('Y-m-d', strtotime('-30 days', strtotime(date("Y-m-d"))));
	if(isset($_SERVER["REMOTE_ADDR"]) ) {
		$ip = $_SERVER["REMOTE_ADDR"];
	}else if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) {
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}else if(isset($_SERVER["HTTP_CLIENT_IP"]) ) {
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	}

	#kirim notif ke telegram
	$userjam=array();
	$str="select * from ".$dbname.".setup_notification_dt where telegram='1'";
	$res=fetchdata($str);
	foreach($res as $val){
		$cektele[$val['kodejenis']][$val['karyawanid']]=$val['karyawanid'];
		$tanggaldaftar=substr($val['status'],0,10);
		if($val['kodejenis']=='APPROVAL'){				
			$userjam[$val['karyawanid']]=$val['karyawanid'];
		}
	}

	$str = "select * from ".$dbname.".user where telegramid!='' and status='1' and telegramstatus='1'";
	$res=fetchdata($str);
	foreach($res as $val){
		$notelegram[$val['karyawanid']]=$val['telegramid'];
		$innotel[$val['telegramid']]=$val['telegramid'];
	}

	$uname = makeOption($dbname,'user','karyawanid,namauser',"telegramid!=''");
		
	try {
	$owlPDO->beginTransaction();

		$str="select a.*, b.namajenis from ".$dbname.".list_notification a left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis where shownotif='0' and a.sendtelegram='0' and a.karyawanid in (select karyawanid from ".$dbname.".setup_notification_dt where telegram='1') and substr(tanggal,1,10)>'".$tanggaldaftar."' order by a.tanggal desc limit 10";
		$res=fetchdata($str);
		if(count($res)>0){
			foreach($res as $val){
				if($cektele[$val['kodenotification']][$val['karyawanid']]!='' and $notelegram[$val['karyawanid']]!=''){
					$pesan="<b>[Notifikasi]</b>\n\n";
					$pesan.=$val['detail'];
					
					send_reply($notelegram[$val['karyawanid']], $pesan);
					$str="update ".$dbname.".list_notification set sendtelegram='1' where id='".$val['id']."'";
					$owlPDO->exec($str);
					
					$str="update ".$dbname.".setup_notification_dt set lastsend='".date("Y-m-d H:i:s")."' where kodejenis='".$val['kodenotification']."' and karyawanid='".$val['karyawanid']."'";
					$owlPDO->exec($str);
					
					$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
					values('".$uname[$val['karyawanid']]."','REG','".$notelegram[$val['karyawanid']]."','".$_SERVER['PHP_SELF']."','".$val['karyawanid']."','private','/SENDNOTIF','".$val['kodenotification']."','".$pesan."','".$ip."')";
					$owlPDO->exec($str);
					
					$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid = '".$notelegram[$val['karyawanid']]."' and text='/SENDNOTIF'";
					$owlPDO->exec($query);
				}
			}
		}
		
		$data=array();$inline_button=array();$datav2=[];
		if(count($userjam)>0){
			$nmapprov=makeOption($dbname,'setup_jenisapproval','jenis,nama');
			foreach($userjam as $karya){
				$str="select * from ".$dbname.".approval where status='0' and karyawanid ='".$karya."' and (keterangan='' or keterangan='pertanggung') order by tanggal desc limit 10"; #exit("error".$str);
				$res=fetchdata($str);
				if(count($res)>0){
					foreach($res as $val){
						if($cektele['APPROVAL'][$val['karyawanid']]!='' and $notelegram[$val['karyawanid']]!=''){
							$data[$val['karyawanid']][$val['jenispersetujuan']][$val['notransaksi']]=$val['notransaksi'];
							$datav2[$val['karyawanid']][$val['jenispersetujuan']]+=1;
						
							$str="update ".$dbname.".approval set keterangan='1' where nourut='".$val['nourut']."'";
							$owlPDO->exec($str);
						}
					}
					
					foreach ($datav2 as $kary => $v1){
						$pesan="<b>[Approval] Ada permintaan persetujuan sebagai berikut :</b>\n<i>(click untuk melihat notransaksi)</i>\n";
						foreach($v1 as $jnsapprov => $jumlah){
							$inline_button[][]=array(
									"text"=>$nmapprov[$jnsapprov]." (".$jumlah.")",
									"callback_data"=>"/APPROVAL DETAIL ".$jnsapprov
							);
						}
						if($cektele['APPROVAL'][$kary]!='' and $notelegram[$kary]!=''){
							send_reply($notelegram[$kary], $pesan, $inline_button);
							$str="update ".$dbname.".setup_notification_dt set lastsend='".date("Y-m-d H:i:s")."' where kodejenis='APPROVAL' and karyawanid='".$kary."'";
							$owlPDO->exec($str);
							
							$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
							values('".$uname[$kary]."','REG','".$notelegram[$kary]."','".$_SERVER['PHP_SELF']."','".$kary."','private','/SENDNOTIF','APPROVAL','".$pesan."','".$ip."')";
							$owlPDO->exec($str);
							
							$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid = '".$notelegram[$kary]."' and text='/SENDNOTIF'";
							$owlPDO->exec($query);
							
							#bikin suggest
							$str="select * from ".$dbname.".setup_notification_dt where telegram='1' and kodejenis='ATBS' and karyawanid='".$kary."'";
							$res=fetchdata($str);
							if(count($res)==0){
								$sql="select substr(waktu,1,10) as waktu from ".$dbname.".tel_activity where telegramid = '".$notelegram[$kary]."' and text='/SENDNOTIF' and karyawanid='".$kary."' and full_text ='SUGGEST ATBS' order by waktu desc limit 1";
								$req=fetchdata($sql);
								if(date("H")=='10' and $req[0]['waktu']<date("Y-m-d")){
									
									$pesan="Apakah anda ingin mendapatkan notifikasi pengiriman / penerimaan TBS di PKS ???\nsilahkan click tombol dibawah ini untuk berlangganan.";
									$inline_button=array();
									$inline_button[][]=array(
											"text"=>"Berlangganan (Ya)",
											"callback_data"=>"/NOTIF #DAFTAR ATBS"
									);
									send_reply($notelegram[$kary], $pesan, $inline_button);
									
									$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
									values('".$uname[$kary]."','REG','".$notelegram[$kary]."','".$_SERVER['PHP_SELF']."','".$kary."','private','/SENDNOTIF','SUGGEST ATBS','".$pesan."\ninline_button = ".json_encode($inline_button)."','".$ip."')";
									$owlPDO->exec($str);
								}
							}
						}
					}
				}
			}
		}
		

	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback(); 
		send_reply("1783000758","[Notifikasi] (notif dan approval) from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
		// echo "Error, " . addslashes($e->getMessage());
		// die();
	}

	try {
	$owlPDO->beginTransaction();		
		
		#notifikasi pengiriman tbs
		$tglhi = date("Y-m-d");
		$data = array();
		$str = "select sum(beratbersih) as kg,count(notransaksi) as rit, kodeorg,millcode,divcode,pengirim,namatransportir from ".$dbname.".pabrik_timbangan where tanggal like '".$tglhi."%' and kodebarang='40000003' group by millcode,kodeorg";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['kodeorg']==''){
				$bar['kodeorg']='EXTN#'.$bar['millcode'];
				$bar['divcode']=$bar['namatransportir'];
			}
			$data[$bar['millcode']][$bar['kodeorg']]+=$bar['kg'];
			$rit[$bar['millcode']][$bar['kodeorg']]+=$bar['rit'];				
			
			$dtmill[$bar['millcode']]=$bar['millcode'];
		}
		
		$tgllalu2 = date('Y-m-d', strtotime('-1 days', strtotime($tglhi)));
		$tgldepan = date('Y-m-d', strtotime('+1 days', strtotime($tglhi)));
		if(count($data)>0){
			$s = "select * from ".$dbname.".organisasi where tipe in ('AFDELING')";
			$r = fetchdata($s);
			foreach($r as $b){
				$nmrg[$b['kodeorganisasi']]=$b['kodeorganisasi']." - ".$b['namaorganisasi'];
				$iplasma[$b['kodeorganisasi']]=$b['inti'];
			}
			
			$inline_button=array();
			$tab="<b>[Notifikasi] Pengiriman / Penerimaan TBS \n</b>Tanggal : <b>".tanggalnormal($tglhi)."</b>\nJam : <b>".date("H:i:s")."</b>\n";
			foreach($data as $millcode => $key){
				$no=0;
				$tab.="\n<b>PKS : ".$millcode."</b>\n";
				if($group!=''){
					$tab.="<b>Divisi/KUD :</b>\n";
				}
				foreach($key as $kdorg => $kg){
					$no++;
					$nkdorg=explode("#",$kdorg);
					if(strlen($nkdorg[0])==6 and $nkdorg[1]=='' and $iplasma[$nkdorg[0]]=='0'){
						$nkdorg[0]=$nmrg[$nkdorg[0]];
					}
					$tab.="   ".$no.". ".$nkdorg[0]." : <b>".number_format($kg)."</b> Kg (".$rit[$millcode][$kdorg]." Rit)\n";
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
			$e=0; $jlh=count($dtmill);
			foreach($dtmill as $divisi){
				if($e==round($jlh/3)){$e=0;}
				$inline_button[$e][]= array(
							"text"=>$divisi,"callback_data"=>"/tbs ".$divisi." ".$tglhi
							);
				$e++;
			}
			$inline_button[] = array(
									array("text"=>$tgllalu2,"callback_data"=>"/TBS ".$tgllalu2),
									array("text"=>"Help","callback_data"=>"/tbs info")
									);
			
			$kodejenis="ATBS";
			$str="select * from ".$dbname.".setup_notification_dt where telegram='1' and kodejenis='".$kodejenis."' and lastsend < '".date("Y-m-d H:i:s")."' and karyawanid in (select karyawanid from ".$dbname.".user where telegramid!='' and status='1' and telegramstatus='1') order by lastsend asc limit 5";
			$res=fetchdata($str);
			foreach($res as $val){
				if($notelegram[$val['karyawanid']]!=''){
					$diff      = (strtotime(date("Y-m-d H:i:s"))-strtotime($val['lastsend']));
					$hari      = floor($diff/(60*60*24));
					$jam       = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
					$menit     = floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
					
					#kirim setiap 1 jam
					if($hari>0 or $jam>0){
						$sql = "select count(notransaksi) as jumlah from ".$dbname.".pabrik_timbangan where tanggal > '".$val['lastsend']."' and kodebarang='40000003'";
						$rql = fetchdata($sql);
						# range jam atau masih ada pengiriman
						if((date("H")>='06' and date("H")<'21') or $rql[0]['jumlah']>0){
							send_reply($notelegram[$val['karyawanid']], $message_text, $inline_button);
							$str="update ".$dbname.".setup_notification_dt set lastsend='".date("Y-m-d H:i:s")."' where kodejenis='".$kodejenis."' and karyawanid='".$val['karyawanid']."'";
							$owlPDO->exec($str);
							
							$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
							values('".$uname[$val['karyawanid']]."','REG','".$notelegram[$val['karyawanid']]."','".$_SERVER['PHP_SELF']."','".$val['karyawanid']."','private','/SENDNOTIF','".$kodejenis."','".$message_text."\ninline_button = ".json_encode($inline_button)."','".$ip."')";
							$owlPDO->exec($str);
							
							
							$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid = '".$notelegram[$val['karyawanid']]."' and text='/SENDNOTIF'";
							$owlPDO->exec($query);
						}
					}
				}
			}
		}

	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback(); 
		send_reply("1783000758","[Notifikasi] (TBS) from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
		// echo "Error, " . addslashes($e->getMessage());
		// die();
	}

	try {
	$owlPDO->beginTransaction();	
		
		#notifikasi laporan penerimaa tbs pdf
		$filepdf = "imgbot/laporanpenerimaantbs.pdf";
		if(file_exists($filepdf)){
			$inline_button=array();
			$tab="<b>[Notifikasi] Laporan Penerimaan TBS (PDF) \n</b>Tanggal : <b>".tanggalnormal($tglhi)."</b>\nJam : <b>".date("H:i:s")."</b>\n";
			
			$kodejenis="LTBS";
			$str="select * from ".$dbname.".setup_notification_dt where telegram='1' and kodejenis='".$kodejenis."' and lastsend < '".date("Y-m-d H:i:s")."' and karyawanid in (select karyawanid from ".$dbname.".user where telegramid!='' and status='1' and telegramstatus='1') order by lastsend asc limit 1";
			$res=fetchdata($str);
			foreach($res as $val){
				if($notelegram[$val['karyawanid']]!=''){
					$diff      = (strtotime(date("Y-m-d H:i:s"))-strtotime($val['lastsend']));
					$hari      = floor($diff/(60*60*24));
					$jam       = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
					$menit     = floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
					
					#kirim setiap 1 jam
					if($hari>0 or $jam>0){
						$sql = "select count(notransaksi) as jumlah from ".$dbname.".pabrik_timbangan where tanggal > '".$val['lastsend']."' and kodebarang='40000003'";
						$rql = fetchdata($sql);
						# range jam atau masih ada pengiriman
						if((date("H")>='06' and date("H")<'21') or $rql[0]['jumlah']>0){
							$message_text=$tab;
							send_reply($notelegram[$val['karyawanid']], $message_text);
							sendDocument($notelegram[$val['karyawanid']],$filepdf);
							
							$str="update ".$dbname.".setup_notification_dt set lastsend='".date("Y-m-d H:i:s")."' where kodejenis='".$kodejenis."' and karyawanid='".$val['karyawanid']."'";
							$owlPDO->exec($str);
							
							$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
							values('".$uname[$val['karyawanid']]."','REG','".$notelegram[$val['karyawanid']]."','".$_SERVER['PHP_SELF']."','".$val['karyawanid']."','private','/SENDNOTIF','".$kodejenis."','".$message_text."\ninline_button = ".json_encode($inline_button)."','".$ip."')";
							$owlPDO->exec($str);
							
							$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid = '".$notelegram[$val['karyawanid']]."' and text='/SENDNOTIF'";
							$owlPDO->exec($query);
						}
					}
				}
			}
		}

	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback(); 
		send_reply("1783000758","[Notifikasi] (TBS PDF) from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
		// echo "Error, " . addslashes($e->getMessage());
		// die();
	}

	try {
	$owlPDO->beginTransaction();	
		
		#notifikasi pabrik produksi CPO dan PK
		$createtime=$data=array();
		$tglhi = tglkemarin(date("Y-m-d"));
		$artgl = explode("-",$tglhi);
		$tgldr = date("Y-".$artgl[1]."-01");
		
		$str=" select distinct kodeorg,  max(tanggal) as tanggal from ".$dbname.".pabrik_produksi where 1=1 and tanggal between '".$tgldr."' and '".$tglhi."' group by kodeorg order by tanggal";
		$ren = fetchdata($str);
		if(count($ren)>0){
			foreach($ren as $ban){
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
		}

		
		if(count($res)>0){
			$tab="<b>[Notifikasi] Laporan Produksi PKS (CPO dan PK)</b>\nJam : <b>".date("H:i:s")."</b>\n";
			$tab.="\n<b><u>PRODUKSI HARIAN PABRIK</u></b>\n";
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

			$sql = "select * from ".$dbname.".pabrik_produksi a where 1=1 and createtime = '".max($createtime)."' order by tanggal desc limit 1";
			$req = fetchdata($sql);
			if(count($req)>0){			
				$tab.="<i>\nData terakhir diupdate oleh :</i>\n";
				$tab.="<i>".getNamaKaryawan($req[0]['createby']).", ".$req[0]['createtime']."</i>\n";
				$tab.="<i>u/ Trans Unit ".$req[0]['kodeorg'].", tanggal ".$req[0]['tanggal']."</i>\n";
			}
			
			$kodejenis="APRDPKS";
			$str="select * from ".$dbname.".setup_notification_dt where telegram='1' and kodejenis='".$kodejenis."' and lastsend < '".date("Y-m-d H:i:s")."' and karyawanid in (select karyawanid from ".$dbname.".user where telegramid!='' and status='1' and telegramstatus='1') order by lastsend asc limit 5";
			$res=fetchdata($str);
			foreach($res as $val){
				if($notelegram[$val['karyawanid']]!=''){
					$diff      = (strtotime(date("Y-m-d H:i:s"))-strtotime($val['lastsend']));
					$hari      = floor($diff/(60*60*24));
					$jam       = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
					$menit     = floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
					
					#kirim setiap 1 jam
					if($hari>0 or $jam>0){
						$sql = "select count(*) as jumlah from ".$dbname.".pabrik_produksi where createtime > '".$val['lastsend']."'";
						$rql = fetchdata($sql);
						# range jam atau masih ada pengiriman
						if((date("H")>='07' and date("H")<'21') and $rql[0]['jumlah']>0){
							send_reply($notelegram[$val['karyawanid']], $tab);
							$str="update ".$dbname.".setup_notification_dt set lastsend='".date("Y-m-d H:i:s")."' where kodejenis='".$kodejenis."' and karyawanid='".$val['karyawanid']."'";
							$owlPDO->exec($str);
							
							$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
							values('".$uname[$val['karyawanid']]."','REG','".$notelegram[$val['karyawanid']]."','".$_SERVER['PHP_SELF']."','".$val['karyawanid']."','private','/SENDNOTIF','".$kodejenis."','".$tab."','".$ip."')";
							$owlPDO->exec($str);
							
							$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid = '".$notelegram[$val['karyawanid']]."' and text='/SENDNOTIF'";
							$owlPDO->exec($query);
						}
					}
				}
			}
		}
		
		
		
		#stok cpo dan pk
		$createtime=$data=array();
		$str=" select distinct kodeorg,  max(tanggal) as tanggal from ".$dbname.".pabrik_masukkeluartangki where 1=1 and tanggal between '".$tgldr."' and '".$tglhi."' group by kodeorg order by tanggal";
		$res = fetchdata($str);
		if(count($res)>0){
			foreach($res as $bar){
				$sql=" select * from ".$dbname.".pabrik_masukkeluartangki where 1=1 and tanggal = '".$bar['tanggal']."' and kodeorg='".$bar['kodeorg']."' order by kuantitas desc";
				$req = fetchdata($sql);
				foreach($req as $val){
					if($val['kuantitas']>0){
						$data[$bar['kodeorg']][$bar['tanggal']]['cpo'][$val['kodetangki']]=$val['kuantitas'];
					}
					if($val['kernelquantity']>0){
						$data[$bar['kodeorg']][$bar['tanggal']]['ker'][$val['kodetangki']]=$val['kernelquantity'];
					}
					$createtime[$val['createtime']]=$val['createtime'];
				}
			}
			
			$tab="<b>[Notifikasi] Laporan Stok CPO dan PK</b>\nJam : <b>".date("H:i:s")."</b>\n";
			$tab.="\n<b><u>LAPORAN STOK CPO dan PK (SOUNDING)</u></b>\n";
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
			$sql = "select * from ".$dbname.".pabrik_masukkeluartangki a where 1=1 and updatetime = '".max($createtime)."' order by notransaksi desc limit 1";
			$req = fetchdata($sql);
			if(count($req)>0){			
				$tab.="<i>\nData terakhir diupdate oleh :</i>\n";
				if(getNamaKaryawan($req[0]['updateby'])==''){
					$nama=getNamaKaryawan($req[0]['createby']);
				}else{
					$nama=getNamaKaryawan($req[0]['updateby']);
				}
				$tab.="<i>".$nama.", ".$req[0]['updatetime']."</i>\n";
				$tab.="<i>u/ Trans Unit ".$req[0]['kodeorg'].", tangki ".$req[0]['kodetangki'].",\ntanggal ".$req[0]['tanggal']."</i>\n";
			}
			
			$kodejenis="ASTOKPKS";
			$str="select * from ".$dbname.".setup_notification_dt where telegram='1' and kodejenis='".$kodejenis."' and lastsend < '".date("Y-m-d H:i:s")."' and karyawanid in (select karyawanid from ".$dbname.".user where telegramid!='' and status='1' and telegramstatus='1') order by lastsend asc limit 5";
			$res=fetchdata($str);
			foreach($res as $val){
				if($notelegram[$val['karyawanid']]!=''){
					$diff      = (strtotime(date("Y-m-d H:i:s"))-strtotime($val['lastsend']));
					$hari      = floor($diff/(60*60*24));
					$jam       = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
					$menit     = floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
					
					#kirim setiap 1 jam
					if($hari>0 or $jam>0){					
						$sql = "select count(*) as jumlah from ".$dbname.".pabrik_masukkeluartangki where updatetime > '".$val['lastsend']."'";
						$rql = fetchdata($sql);
						# range jam atau masih ada pengiriman
						if((date("H")>='07' and date("H")<'21') and $rql[0]['jumlah']>0){
							send_reply($notelegram[$val['karyawanid']], $tab);
							$str="update ".$dbname.".setup_notification_dt set lastsend='".date("Y-m-d H:i:s")."' where kodejenis='".$kodejenis."' and karyawanid='".$val['karyawanid']."'";
							$owlPDO->exec($str);
							
							$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
							values('".$uname[$val['karyawanid']]."','REG','".$notelegram[$val['karyawanid']]."','".$_SERVER['PHP_SELF']."','".$val['karyawanid']."','private','/SENDNOTIF','".$kodejenis."','".$tab."','".$ip."')";
							$owlPDO->exec($str);

							$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid = '".$notelegram[$val['karyawanid']]."' and text='/SENDNOTIF'";
							$owlPDO->exec($query);
						}
					}
				}
			}
		}

	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback(); 
		send_reply("1783000758","[Notifikasi] (cpo dan pk) from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
		// echo "Error, " . addslashes($e->getMessage());
		// die();
	}

	try {
	$owlPDO->beginTransaction();		
		
		#notifikasi lembur
		$tanggal1 = date("Y-m")."-01";
		$tanggal2 = date("Y-m-d");
		$tahun    = date("Y");
		$day      = strtolower(hari($tanggal2));
		$upto     = 50;
		$kirimhari= array('senin');
		
		$sql = "select sum(jumlah) as jumlah, karyawanid from ".$dbname.".sdm_5gajipokok where tahun = '".$tahun."' and idkomponen='1' group by karyawanid";
		$req = fetchdata($sql);
		foreach($req as $val){
			$gapok[$val['karyawanid']]=$val['jumlah'];
		}
		# hanya lakukan pengecekan setiap hari senin
		if(in_array($day,$kirimhari)){
			

			$data=array();
			$str = "select sum(uangkelebihanjam) as rp, sum(jamaktual) as jam, karyawanid, substr(a.kodeorg,1,4) as kdorg from ".$dbname.".sdm_lemburdt a left join ".$dbname.".sdm_lemburht b on a.kodeorg=b.kodeorg and a.tanggal=b.tanggal where a.tanggal between '".$tanggal1."' and '".$tanggal2."' group by karyawanid, substr(a.kodeorg,1,4) order by rp desc";
			$res = fetchdata($str);
			foreach($res as $bar){
				$persen=($bar['rp']/$gapok[$bar['karyawanid']])*100;
				if($persen>$upto){
					$data[$bar['kdorg']][$bar['karyawanid']]=$bar['karyawanid'];
					$jamsdhi[$bar['karyawanid']]=$bar['jam'];
					$rpsdhi[$bar['karyawanid']]=$bar['rp'];
					$pers[$bar['karyawanid']]=$persen;
				}
				if(getKary($bar['karyawanid'],'tipekaryawan')=='4' and getNamaOrg($bar['kdorg'],'tipe')!='PABRIK'){
					$data[$bar['kdorg']][$bar['karyawanid']]=$bar['karyawanid'];
					$jamsdhi[$bar['karyawanid']]=$bar['jam'];
					$rpsdhi[$bar['karyawanid']]=$bar['rp'];
					$pers[$bar['karyawanid']]=$persen;
				}
				// if(getNamaOrg($bar['kdorg'],'tipe')=='KEBUN'){
					// $data[$bar['kdorg']][$bar['karyawanid']]=$bar['karyawanid'];
					// $jamsdhi[$bar['karyawanid']]=$bar['jam'];
					// $rpsdhi[$bar['karyawanid']]=$bar['rp'];
					// $pers[$bar['karyawanid']]=$persen;
				// }
			}

			if(count($data)>0){
				$tab = "<label>Tanggal : ".tanggalnormal($tanggal1)." s/d ".tanggalnormal($tanggal2)."</label><br>
						<label>Data dibawah ini termasuk data yang belum diposting.</label><br>
					<table cellpadding=1 cellspacing=1 border=1 class=sortable>
					<thead><tr class=rowheader style=font-weight:bold>
					<td align=center>No</td>
					<td align=center>NIK</td>    
					<td align=center>Nama</td>
					<td align=center>Divisi</td>
					<td align=center>Tipe Kary</td>
					<td align=center>Jabatan</td>
					<td align=center>Jam</td>
					<td align=center>Rupiah</td>
					<td align=center>Gapok</td>
					<td align=center>Persen</td>
				</tr>
				</thead>";
				foreach($data as $kodeorg => $v1){
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=10 align=left style=background-color:#91ffe0><b>" . $kodeorg . " - " . getNamaOrg($kodeorg) . "</b></td>";
					$tab.="</tr>";
					foreach($v1 as $kary){
						$no++;			
						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center>" . $no . "</td>";
						$tab.="<td align=left>".getKary($kary,'nik')."</td>";
						$tab.="<td align=left>".getKary($kary)."</td>";
						if(getKary($kary,'subbagian')==''){
							$tab.="<td align=left>UMUM / KANTOR</td>";
						}else{					
							$tab.="<td align=left>".getNamaOrg(getKary($kary,'subbagian'))."</td>";
						}
						$tab.="<td align=left>".getNamaTipeKary(getKary($kary,'tipekaryawan'))."</td>";
						$tab.="<td align=left>".getNamaJabatan(getKary($kary,'kodejabatan'))."</td>";
						$tab.="<td align=right><b>".@number_format($jamsdhi[$kary],2)."</b></td>";
						$tab.="<td align=right><b>".@number_format($rpsdhi[$kary],0)."</b></td>";
						$tab.="<td align=right><b>".@number_format($gapok[$kary],0)."</b></td>";
						$tab.="<td align=right><b>".@number_format($pers[$kary],2)."</b></td>";
						$tab.="</tr>";
						$ttljam[$kodeorg]+=$jamsdhi[$kary];
						$ttlrp[$kodeorg]+=$rpsdhi[$kary];
					}
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=right style=background-color:#e4d9ff colspan=6><b>TOTAL</b></td>";
					$tab.="<td align=right style=background-color:#e4d9ff><b>".@number_format($ttljam[$kodeorg],2)."</b></td>";
					$tab.="<td align=right style=background-color:#e4d9ff><b>".@number_format($ttlrp[$kodeorg],0)."</b></td>";
					$tab.="<td style=background-color:#e4d9ff></td>";
					$tab.="<td style=background-color:#e4d9ff></td>";
					$tab.="</tr>";
				}
				$tab.="</table>";
				$stream = $tab;
				$nop_ = "NotifLembur";
				if (strlen($stream) > 0) {
					if ($handle = opendir('imgbot/temp/')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
								@unlink('imgbot/temp/' . $file);
							}
						}
						closedir($handle);
					}
					$handle = fopen("imgbot/temp/" . $nop_ . ".xls", 'w');
					if (!fwrite($handle, $stream)) {
						echo "<script language=javascript1.2>
									parent.window.alert('Cant convert to excel format');
									</script>";
						exit;
					} else {
						// echo "<script language=javascript1.2>
									// window.location='imgbot/temp/" . $nop_ . ".xls';
									// </script>";
					}
					closedir($handle);
				}
				
				$kodejenis="LBR";
				$nkary=$njab=$ntipe=$ndept=array();
				$str = "select * from ".$dbname.".setup_notification_dt where kodejenis='".$kodejenis."'";
				$res = fetchdata($str);
				foreach($res as $val){
					if($val['karyawanid']!='0000000000'){			
						$nkary[$val['karyawanid']]=$val['karyawanid'];
					}
					if($val['kodejabatan']!=''){						
						$njab[$val['kodejabatan']]=$val['kodejabatan'];
					}
					if($val['kodetipekaryawan']!=''){						
						$ntipe[$val['kodetipekaryawan']]=$val['kodetipekaryawan'];
					}
					if($val['kodedepartement']!=''){			
						$ndept[$val['kodedepartement']]=$val['kodedepartement'];
					}
				}
				
				$where="";
				if(count($nkary)>0){
					$where.=" and karyawanid in ('".implode("','",$nkary)."')";
				}
				if(count($njab)>0){
					if($where!=""){			
						$where.=" or kodejabatan in ('".implode("','",$njab)."')";
					}else{
						$where.=" and kodejabatan in ('".implode("','",$njab)."')";
					}
				}
				if(count($ntipe)>0){
					if($where!=""){
						$where.=" or tipekaryawan in ('".implode("','",$ntipe)."')";
					}else{
						$where.=" and tipekaryawan in ('".implode("','",$ntipe)."')";
					}	
				}
				if(count($ndept)>0){
					if($where!=""){
						$where.=" or bagian in ('".implode("','",$ndept)."')";
					}else{
						$where.=" and bagian in ('".implode("','",$ndept)."')";			
					}	
				}
				
				$filepdf="imgbot/temp/".$nop_.".xls";
				
				$str = "select * from ".$dbname.".user";
				$res = fetchdata($str);
				foreach($res as $bar){
					$username[$bar['karyawanid']]=$bar['namauser'];
				}
				
				$daysKeep  = 30;
				$ztu       = mktime(0,0,0,date('m'),date('d')-$daysKeep,date('Y'));
				$last3month= date('Y-m-d H:i:s',$ztu);

				#delete old log as configured in ini file for days log kept:
				$str = "delete from ".$dbname.".user_activity where file='/owl/slave_notification_telegram.php' and waktu<'".$last3month."' or username=''";
				$owlPDO->exec($str);
				 
				$str = "select * from ".$dbname.".user_activity where file='/owl/slave_notification_telegram.php' and waktu like '".$tanggal2."%' and get='LBR'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$lastsend[$bar['karyawanid']]=$bar['waktu'];
				}
				
				$str = "select * from ".$dbname.".datakaryawan where (1=1 ".$where.") and emailkantor like '%@%'";
				$res = fetchdata($str);
				if(in_array($day,$kirimhari) and count($res)>0){
				#if(count($res)>0){
					foreach($res as $bar){
						$subject="[Notifikasi] Lembur diatas ".$upto."% dari gapok, BHL dan Kebun s/d ".tanggalnormal($tanggal2).".";
						$body="Dear Bapak / Ibu,
							<br>
							<br>
							Terlampir kami kirimkan data lembur karyawan sampai dengan tanggal ".tanggalnormal($tanggal2).".<br><br>
							
							Demikian disampaikan, terima kasih.<br><br>
							Salam,
							<br>
							<br>
							auto generate by owl.ksp-agro.com
							";
						if($lastsend[$bar['karyawanid']]==''){				
							kirimEmailatt($bar['emailkantor'],$cc="",$subject,$body,$mailType='text/html',$filepdf);
							
							$str="insert into ".$dbname.".user_activity (username,file,karyawanid,post,get,ip,compname)
							values('".$username[$bar['karyawanid']]."','/owl/slave_notification_telegram.php','".$bar['karyawanid']."','".$subject."','LBR','".$_SERVER['REMOTE_ADDR']."','')";
							$owlPDO->exec($str);
						}
					}
				}
			}
		}
		#notifikasi lembur
		
		
		#NOTIFIKASI GAJI > 300%
		$tanggal1 = date("Y-m")."-01";
		$tanggal2 = date("Y-m-d");
		$tglkirim = date("Y-m")."-10";
		$tahun    = date("Y");
		$day      = strtolower(hari($tanggal2));
		$upto     = 300;
		
		if($tglkirim == date("Y-m-d")){
			
			$sql = "select distinct kodeorg from ".$dbname.".sdm_5periodegaji";
			$req = fetchdata($sql);
			foreach($req as $val){
				$dtkodeorg[$val['kodeorg']]=$val['kodeorg'];
			}

			$data=array();$datag=[];
			foreach($dtkodeorg as $kodeorg){
				$sql = "select max(periode) as periode from ".$dbname.".sdm_5periodegaji where kodeorg = '".$kodeorg."' and sudahproses='1' order by periode desc limit 1";
				$req = fetchdata($sql);
				foreach($req as $val){
					$periodegj = $val['periode'];
				}
				$str = "select sum(jumlah) as jumlah, karyawanid, periodegaji, plus from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where periodegaji='".$periodegj."' and kodeorg='".$kodeorg."' group by karyawanid, periodegaji, plus order by jumlah desc";
				$res = fetchdata($str);
				foreach($res as $bar){
					if($bar['plus']=='0'){
						$bar['jumlah']=$bar['jumlah']*(-1);
					}
					$datag[$bar['periodegaji']][$bar['karyawanid']]+=$bar['jumlah'];
				}
			}
			foreach($datag as $prdgj => $v1){
				foreach($v1 as $kary => $jumlah){
					$persen=($jumlah/$gapok[$bar['karyawanid']])*100;
					
					if($persen>$upto){
						// $data[$prdgj][$kary]+=$jumlah;
						// $rpsdhi[$prdgj][$kary]+=$jumlah;
						// $pers[$prdgj][$kary]=$persen;
						
						$data[$jumlah]=array(
							'kary'=>$kary,
							'prdgj'=>$prdgj,
							'rpsdhi'=>$jumlah,
							'persen'=>$persen
						);
					}
				}
			}
			
			krsort($data);
			
			if(count($data)>0){
				$tab = "
					<table cellpadding=1 cellspacing=1 border=1 class=sortable>
					<thead><tr class=rowheader style=font-weight:bold>
					<td align=center>No</td>
					<td align=center>Periode</td>
					<td align=center>NIK</td>    
					<td align=center>Nama</td>
					<td align=center>Unit</td>
					<td align=center>Divisi</td>
					<td align=center>Tipe Kary</td>
					<td align=center>Jabatan</td>
					<td align=center>Rupiah</td>
					<td align=center>Gapok</td>
					<td align=center>Persen</td>
				</tr>
				</thead>";
				foreach($data as $key => $val){
					$no++;			
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>" . $no . "</td>";
					$tab.="<td align=left>".$val['prdgj']."</td>";
					$tab.="<td align=left>".getKary($val['kary'],'nik')."</td>";
					$tab.="<td align=left>".getKary($val['kary'])."</td>";
					$tab.="<td align=left>".getNamaOrg(getKary($val['kary'],'lokasitugas'))."</td>";
					if(getKary($val['kary'],'subbagian')==''){
						$tab.="<td align=left>UMUM / KANTOR</td>";
					}else{					
						$tab.="<td align=left>".getNamaOrg(getKary($val['kary'],'subbagian'))."</td>";
					}
					$tab.="<td align=left>".getNamaTipeKary(getKary($val['kary'],'tipekaryawan'))."</td>";
					$tab.="<td align=left>".getNamaJabatan(getKary($val['kary'],'kodejabatan'))."</td>";
					$tab.="<td align=right><b>".@number_format($val['rpsdhi'],0)."</b></td>";
					$tab.="<td align=right><b>".@number_format($gapok[$val['kary']],0)."</b></td>";
					$tab.="<td align=right><b>".@number_format($val['persen'],2)."</b></td>";
					$tab.="</tr>";
				}
				$tab.="</table>";
				$stream = $tab;
				$nop_ = "NotifGaji";
				if (strlen($stream) > 0) {
					if ($handle = opendir('imgbot/temp/')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
								@unlink('imgbot/temp/' . $file);
							}
						}
						closedir($handle);
					}
					$handle = fopen("imgbot/temp/" . $nop_ . ".xls", 'w');
					if (!fwrite($handle, $stream)) {
						echo "<script language=javascript1.2>
									parent.window.alert('Cant convert to excel format');
									</script>";
						exit;
					} else {
						// echo "<script language=javascript1.2>
									// window.location='imgbot/temp/" . $nop_ . ".xls';
									// </script>";
					}
					closedir($handle);
				}
				
				$kodejenis="GAJI";
				$nkary=$njab=$ntipe=$ndept=array();
				$str = "select * from ".$dbname.".setup_notification_dt where kodejenis='".$kodejenis."'";
				$res = fetchdata($str);
				foreach($res as $val){
					if($val['karyawanid']!='0000000000'){			
						$nkary[$val['karyawanid']]=$val['karyawanid'];
					}
					if($val['kodejabatan']!=''){						
						$njab[$val['kodejabatan']]=$val['kodejabatan'];
					}
					if($val['kodetipekaryawan']!=''){						
						$ntipe[$val['kodetipekaryawan']]=$val['kodetipekaryawan'];
					}
					if($val['kodedepartement']!=''){			
						$ndept[$val['kodedepartement']]=$val['kodedepartement'];
					}
				}
				
				$where="";
				if(count($nkary)>0){
					$where.=" and karyawanid in ('".implode("','",$nkary)."')";
				}
				if(count($njab)>0){
					if($where!=""){			
						$where.=" or kodejabatan in ('".implode("','",$njab)."')";
					}else{
						$where.=" and kodejabatan in ('".implode("','",$njab)."')";
					}
				}
				if(count($ntipe)>0){
					if($where!=""){
						$where.=" or tipekaryawan in ('".implode("','",$ntipe)."')";
					}else{
						$where.=" and tipekaryawan in ('".implode("','",$ntipe)."')";
					}	
				}
				if(count($ndept)>0){
					if($where!=""){
						$where.=" or bagian in ('".implode("','",$ndept)."')";
					}else{
						$where.=" and bagian in ('".implode("','",$ndept)."')";			
					}	
				}
				
				$filepdf="imgbot/temp/".$nop_.".xls";
				
				$str = "select * from ".$dbname.".user";
				$res = fetchdata($str);
				foreach($res as $bar){
					$username[$bar['karyawanid']]=$bar['namauser'];
				}
				
				
				$lastsend=array();
				$str = "select * from ".$dbname.".user_activity where file='/owl/slave_notification_telegram.php' and get='GAJI' and waktu like '".$tglkirim."%'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$lastsend[$bar['karyawanid']]=substr($bar['waktu'],0,10);
				}
				$str = "select karyawanid,emailkantor from ".$dbname.".datakaryawan where (1=1 ".$where.") and emailkantor like '%@%'";
				$res = fetchdata($str);
				if(count($res)>0){
				#if(count($res)>0){
					foreach($res as $bar){
						$subject="[Notifikasi] Upah diatas ".$upto."% dari gapok.";
						$body="Dear Bapak / Ibu,
							<br>
							<br>
							Terlampir kami kirimkan data upah karyawan yang jumlah diatas ".$upto."% dari gaji pokok.<br><br>
							
							Demikian disampaikan, terima kasih.<br><br>
							Salam,
							<br>
							<br>
							auto generate by owl.ksp-agro.com
							";
						if($lastsend[$bar['karyawanid']]=="" and $tglkirim == date("Y-m-d")){
							kirimEmailatt($bar['emailkantor'],$cc="",$subject,$body,$mailType='text/html',$filepdf);
							
							$str="insert into ".$dbname.".user_activity (username,file,karyawanid,post,get,ip,compname)
							values('".$username[$bar['karyawanid']]."','/owl/slave_notification_telegram.php','".$bar['karyawanid']."','".$subject."','GAJI','".$_SERVER['REMOTE_ADDR']."','')";
							$owlPDO->exec($str);
						}	
					}
				}
			}	
		}
		#NOTIFIKASI GAJI > 300%	
		
		// #PTA KELAMAAN DELETE
		// #=================================== PINDAHKAN PTA DITOLAK ======================================
		// #NONKAPITAL
		// $data = array();
		// $str = "SELECT * FROM ".$dbname.".bgt_budget where 1=1 and pta='PTA' and statuspta='2'";
		// $res = fetchdata($str);
		// foreach ($res as $bar){
			// $data = array(
				// 'tahunbudget'=> $bar['tahunbudget'],
				// 'kodeorg'    => $bar['kodeorg'],
				// 'dept'       => $bar['dept'],
				// 'kodews'     => $bar['kodews'],
				// 'tipebudget' => $bar['tipebudget'],
				// 'kodebudget' => $bar['kodebudget'],
				// 'kegiatan'   => $bar['kegiatan'],
				// 'noakun'     => $bar['noakun'],
				// 'aruskas'    => $bar['aruskas'],
				// 'volume'     => $bar['volume'],
				// 'satuanv'    => $bar['satuanv'],
				// 'rupiah'     => $bar['rupiah'],
				// 'kodevhc'    => $bar['kodevhc'],
				// 'kodebarang' => $bar['kodebarang'],
				// 'rotasi'     => $bar['rotasi'],
				// 'kunci'      => $bar['kunci'],
				// 'regional'   => $bar['regional'],
				// 'updateby'   => $bar['updateby'],
				// 'lastupdate' => $bar['lastupdate'],
				// 'jumlah'     => $bar['jumlah'],
				// 'satuanj'    => $bar['satuanj'],
				// 'keterangan' => $bar['keterangan'],
				// 'keterangan2'=> $bar['keterangan2'],
				// 'tutup'      => $bar['tutup'],
				// 'pta'        => $bar['pta'],
				// 'notransaksi'=> $bar['notransaksi'],
				// 'statuspta'  => $bar['statuspta'],
				// 'tanggal'    => $bar['tanggal']
			// );
			
			// $query = insertQuery($dbnamerpt,'bgt_budget_hist',$data,array_keys($data));
			// $owlPDO->exec($query);

			// $query = "delete from " . $dbname . ".bgt_budget where pta='PTA' and statuspta='2' and kunci = '".$bar['kunci']."'";
			// $owlPDO->exec($query);
		// }

		// #KAPITAL
		// $data = array();
		// $str = "SELECT * FROM ".$dbname.".bgt_kapital where 1=1 and pta='PTA' and statuspta='2'";
		// $res = fetchdata($str);
		// foreach ($res as $bar){
			// $data = array(
				// 'tahunbudget' => $bar['tahunbudget'],
				// 'kodeunit'    => $bar['kodeunit'],
				// 'jeniskapital'=> $bar['jeniskapital'],
				// 'aruskas'     => $bar['aruskas'],
				// 'kodebarang'  => $bar['kodebarang'],
				// 'keterangan'  => $bar['keterangan'],
				// 'keterangan2' => $bar['keterangan2'],
				// 'jumlah'      => $bar['jumlah'],
				// 'hargasatuan' => $bar['hargasatuan'],
				// 'hargatotal'  => $bar['hargatotal'],
				// 'k01'         => $bar['k01'],
				// 'k02'         => $bar['k02'],
				// 'k03'         => $bar['k03'],
				// 'k04'         => $bar['k04'],
				// 'k05'         => $bar['k05'],
				// 'k06'         => $bar['k06'],
				// 'k07'         => $bar['k07'],
				// 'k08'         => $bar['k08'],
				// 'k09'         => $bar['k09'],
				// 'k10'         => $bar['k10'],
				// 'k11'         => $bar['k11'],
				// 'k12'         => $bar['k12'],
				// 'tutup'       => $bar['tutup'],
				// 'kunci'       => $bar['kunci'],
				// 'updateby'    => $bar['updateby'],
				// 'lastupdate'  => $bar['lastupdate'],
				// 'lokasi'      => $bar['lokasi'],
				// 'pta'         => $bar['pta'],
				// 'notransaksi' => $bar['notransaksi'],
				// 'statuspta'   => $bar['statuspta'],
				// 'tanggal'     => $bar['tanggal']
			// );
			
			// $query = insertQuery($dbnamerpt,'bgt_kapital_hist',$data,array_keys($data));
			// $owlPDO->exec($query);

			// $query = "delete from " . $dbname . ".bgt_kapital where pta='PTA' and statuspta='2' and kunci = '".$bar['kunci']."'";
			// $owlPDO->exec($query);
		// }
		// #=================================================================================================
		
		// #===========================DELETE PTA DITOLAK JIKA 30 HARI TIDAK DIAJUKAN ULANG ==============================
		// $hariini = date("Y-m-d");
		// $filepdf="";
		// $str = "SELECT distinct notransaksi,statuspta, updateby, tanggal FROM ".$dbname.".bgt_budget where 1=1 and pta='PTA' and statuspta='0' group by notransaksi order by tanggal";
		// $res = fetchdata($str);
		// foreach ($res as $bar){			
			// $tgllastapprov = $bar['tanggal'];
			// $jlhhari = selisitgl($hariini,$tgllastapprov);
			// if($jlhhari>60){
				// $sql = "select * from ".$dbname.".datakaryawan where karyawanid='".$bar['updateby']."' and emailkantor like '%@%'";
				// $req = fetchdata($sql);
				// if(count($req)>0){
					// foreach($req as $val){
						// $subject="[Notifikasi] PTA ".$bar['notransaksi']." telah dihapus otomatis.";
						// $body="Dear Bapak / Ibu,
							// <br>
							// <br>
							// Berikut kami sampaikan bahwa PTA dengan nomor ".$bar['notransaksi']." telah dihapus oleh system secara otomatis dikarenakan PTA tersebut tidak diajukan lebih dari dua bulan (>60hari).<br><br>
							
							// Demikian disampaikan, terima kasih.<br><br>
							// Salam,
							// <br>
							// <br>
							// auto generate by owl.ksp-agro.com
							// ";
						// kirimEmailatt($val['emailkantor'],$cc="",$subject,$body,$mailType='text/html',$filepdf);
						
						// $str="insert into ".$dbname.".user_activity (username,file,karyawanid,post,get,ip,compname)
						// values('".$username[$val['karyawanid']]."','/owl/bgt_slave_ptax.php','".$val['karyawanid']."','".$subject."','PTA','".$_SERVER['REMOTE_ADDR']."','')";
						// $owlPDO->exec($str);
					// }
				// }
				
				// $query = "delete from " . $dbname . ".bgt_budget where pta='PTA' and statuspta='0' and notransaksi = '".$bar['notransaksi']."'";
				// try {$owlPDO->exec($query);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			// }
		// }
		
		// $str = "SELECT distinct notransaksi,statuspta, updateby, tanggal FROM ".$dbname.".bgt_kapital where 1=1 and pta='PTA' and statuspta='0' group by notransaksi order by tanggal";
		// $res = fetchdata($str);
		// foreach ($res as $bar){
			// $tgllastapprov = $bar['tanggal'];
			// $jlhhari = selisitgl($hariini,$tgllastapprov);
			// if($jlhhari>60){
				// $sql = "select * from ".$dbname.".datakaryawan where karyawanid='".$bar['updateby']."' and emailkantor like '%@%'";
				// $req = fetchdata($sql);
				// if(count($req)>0){
					// foreach($req as $val){
						// $subject="[Notifikasi] PTA ".$bar['notransaksi']." telah dihapus otomatis.";
						// $body="Dear Bapak / Ibu,
							// <br>
							// <br>
							// Berikut kami sampaikan bahwa PTA dengan nomor ".$bar['notransaksi']." telah dihapus oleh system secara otomatis dikarenakan PTA tersebut tidak diajukan lebih dari dua bulan (>60hari).<br><br>
							
							// Demikian disampaikan, terima kasih.<br><br>
							// Salam,
							// <br>
							// <br>
							// auto generate by owl.ksp-agro.com
							// ";
						// kirimEmailatt($val['emailkantor'],$cc="",$subject,$body,$mailType='text/html',$filepdf);
						
						// $str="insert into ".$dbname.".user_activity (username,file,karyawanid,post,get,ip,compname)
						// values('".$username[$val['karyawanid']]."','/owl/bgt_slave_ptax.php','".$val['karyawanid']."','".$subject."','PTA','".$_SERVER['REMOTE_ADDR']."','')";
						// $owlPDO->exec($str);
					// }
				// }
				// $query = "delete from " . $dbname . ".bgt_kapital where pta='PTA' and statuspta='0' and notransaksi = '".$bar['notransaksi']."'";
				// try {$owlPDO->exec($query);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			// }
		// }
		// #=================================================================================================
	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback(); 
		send_reply("1783000758","[Notifikasi] (lembur dan gaji) from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
		// echo "Error, " . addslashes($e->getMessage());
		// die();
	}

	try {
	$owlPDO->beginTransaction();	
		
		#ada kejadian jurnal PNN19 hilang di bagian kredit, dan belum ketemu apa penyebabnya, solusi insert lagi saja.
		$str = "SELECT * FROM ".$dbname.".keu_jurnal_tidak_balance_vw where (nojurnal like '%PNN19%' or nojurnal like '%PNN20%')";
		$res = fetchdata($str);
		if(count($res)>0){
			foreach($res as $bar){
				$tempkodejurnal = explode("/",$bar['nojurnal']);
				$kodeJurnal = $tempkodejurnal[2];
			
				$queryParam= selectQuery($dbname,'keu_5parameterjurnal','noakunkredit',"kodeaplikasi='KBN' and jurnalid='".$kodeJurnal."'"); 
				$resParam  = fetchData($queryParam);
				
				if($bar['kredit']=='0'){
					$query = selectQuery($dbname,'keu_jurnaldt','max(nourut) as nourut',"nojurnal='".$bar['nojurnal']."'"); 
					$resPar= fetchData($query);
					$noUrut= $resPar[0]['nourut']+1;
					
					$data = array();
					$data = array(
						'nojurnal'    =>$bar['nojurnal'],
						'tanggal'     =>$bar['tanggal'],
						'nourut'      =>$noUrut,
						'noakun'      =>$resParam[0]['noakunkredit'],
						'keterangan'  =>'',
						'jumlah'      =>$bar['debet']*(-1),
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$bar['kodeorg'],
						'kodekegiatan'=>'',
						'kodeasset'   =>'',
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>'',
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>'',
						'revisi'      =>'0',
						'kodesegment' => '0000000001'
					);
					
					$queryH = insertQuery($dbname,'keu_jurnaldt',$data,array_keys($data));
					$owlPDO->exec($queryH);
				}
			}
			send_reply("1783000758","Ada jurnal PNN19 hilang jam : ".date('Y-m-d H:i:s'));
		}
		
		# kalau ini approval HO yang hilang
		
		$sql = "select * from ".$dbname.".approval where jenispersetujuan ='KASBANK' and notransaksi like '%HO%' and level>1 and notransaksi not in (select notransaksi from ".$dbname.".approval where jenispersetujuan ='KASBANK' and notransaksi like '%HO%' and level=1) and status in ('0') order by nourut asc limit 50";
		$req = fetchdata($sql);
		if(count($req)>0){
			foreach($req as $val){
				$level = ($val['level']-1);
				for($i=$level;$i>=1;$i--){
					
					$query = "select * from ".$dbname.".approval where jenispersetujuan ='KASBANK' and notransaksi = '".$val['notransaksi']."' and level='".$i."'";
					$reqqy = fetchdata($query);
					if(count($reqqy)==0){
						$temporg = explode("/",$val['notransaksi']);
						$param['kodeorg'] = $temporg[1];
						$arrList = listApprove($i,'KASBANK',$param['kodeorg']);
						
						$date = tanggalnormal(substr($val['tanggal'],0,10))." ".substr($val['tanggal'],-8);
						$tglapprove = kurangmenit2($date,35);
						
						if($val['status']=='1'){					
							$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
							values('".$val['notransaksi']."','KASBANK','".$i."','".$arrList[0]['karyawanid']."','1','Approved','','".$tglapprove."')";	
							$owlPDO->exec($str);
						}
						if($val['status']=='0'){					
							$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
							values('".$val['notransaksi']."','KASBANK','".$i."','".$arrList[0]['karyawanid']."','0','','','0000-00-00 00:00:00')";	
							$owlPDO->exec($str);
						}
					}
				}
				$nomor++;
				$notrhilang.="\n".$nomor.". ".$val['notransaksi'];
			}
			send_reply("1783000758","[Notifikasi] (Approval KB Hilang) jam ".date("Y-m-d H:i:s").$notrhilang);
		}

		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback(); 
		send_reply("1783000758","[Notifikasi] (pnn19 dan approval kb) from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
		// echo "Error, " . addslashes($e->getMessage());
		// die();
	}

	try {
	$owlPDO->beginTransaction();	
		
		# NOTIFIKASI PENCAPAIAN PRODUKSI DAN BLOK TIDAK TERPANEN
		$periode = periodelalu(date('Y-m'));
		$tempPer = explode("-",$periode);
		$tahun   = $tempPer[0];
		$bulan   = $tempPer[1];

		#kirim setiap tanggal 3
		$kirimtiaptanggal='03';
		if(date('d')==$kirimtiaptanggal){
			
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

			
			$sql = "select * from ".$dbname.".bgt_regional_assignment";
			$req = fetchdata($sql);
			foreach($req as $bar){
				if(getNamaOrg(substr($bar['kodeunit'],0,4),'tipe')=='KEBUN'){		
					$region[$bar['kodeunit']]=$bar['subregional'];
					$listregion[$bar['subregional']]=$bar['subregional'];
					$listkebun[$bar['kodeunit']]=$bar['kodeunit'];
				}
			}

			$sql = "select * from ".$dbname.".setup_blok where statusblok='TM' and luasareaproduktif>'0'";
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
				if($reg!=''){				
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
						}	
					}	
				}
			}

			$tab="";
			if(!empty($bloktidakpanen)){	
				$tab.="\u{203C} <b>[Notifikasi] Jumlah dan luas blok yang tidak terpanen</b> \u{203C}\n";
				$tab.="Periode : <b>".$periode."</b>\n";
				foreach($bloktidakpanen as $regional => $v1){
					$tab.="\n===================";
					$tab.="\nRegional : <b>".$regional."</b>";
					$tab.="\n===================\n";
					foreach($v1 as $kebun => $v2){
						$tab.="\n<b>".getNamaOrg($kebun)."</b>\n"; $no=0;
						foreach($v2 as $divisi => $jumlah){
							$no++;
							$persen=$luastidakpanen[$regional][$kebun][$divisi]/$luasdivisi[$divisi]*100;
							$tab.=$no.". ".getNamaOrg($divisi)." = <b>".$jumlah."</b> Blok, ".$luastidakpanen[$regional][$kebun][$divisi]." Ha, ".hidezerodecimal($persen,2)." %\n";
							
							$subkbun[$kebun]['jlh']+=$jumlah;
							$subkbun[$kebun]['ha']+=$luastidakpanen[$regional][$kebun][$divisi];
							
							$subreg[$regional]['jlh']+=$jumlah;
							$subreg[$regional]['ha']+=$luastidakpanen[$regional][$kebun][$divisi];
							
							$gt['jlh']+=$jumlah;
							$gt['ha']+=$luastidakpanen[$regional][$kebun][$divisi];
						}
						
						$persen=$subkbun[$kebun]['ha']/$luaskebun[$kebun]*100;
						$tab.="<b>SUB TOTAL ".$kebun." = ".$subkbun[$kebun]['jlh']." Blok ".$subkbun[$kebun]['ha']." Ha, ".hidezerodecimal($persen,2)." %</b>\n";
					}
					$persen=$subreg[$regional]['ha']/$luasreg[$regional]*100;
					$tab.="\n<b>SUB TOTAL ".$regional." = ".$subreg[$regional]['jlh']." Blok ".$subreg[$regional]['ha']." Ha, ".hidezerodecimal($persen,2)." %</b>\u{203C}\n";
				}
				$persen=$gt['ha']/$gtha*100;
				$tab.="\n<b>GRAND TOTAL = ".$gt['jlh']." Blok ".$gt['ha']." Ha, ".hidezerodecimal($persen,2)." %</b>\u{203C}\n";

				$message_text=$tab;
				$kodejenis="!PNN";
				$str="select * from ".$dbname.".setup_notification_dt where telegram='1' and kodejenis='".$kodejenis."'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($notelegram[$val['karyawanid']]!=''){			
						$sql = "select count(*) as jumlah from ".$dbname.".tel_activity where telegramid='".$notelegram[$val['karyawanid']]."' and waktu like '".date('Y-m-d')."%' and karyawanid='".$val['karyawanid']."' and text = '/SENDNOTIF' and full_text='[Notifikasi] Jumlah dan luas blok yang tidak terpanen'";
						$rql = fetchdata($sql);
						# range jam
						if((date("H")>='12' and date("H")<'17') and $rql[0]['jumlah']==0){
							$lokasitugas = getKary($val['karyawanid'],'lokasitugas');
							if(getNamaOrg($lokasitugas,'tipe')=='KEBUN'){							
								$inline_button=[];
								send_reply($notelegram[$val['karyawanid']], $message_text,$inline_button);
								
								$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
								values('".$uname[$val['karyawanid']]."','REG','".$notelegram[$val['karyawanid']]."','".$_SERVER['PHP_SELF']."','".$val['karyawanid']."','private','/SENDNOTIF','[Notifikasi] Jumlah dan luas blok yang tidak terpanen','".$message_text."\ninline_button = ".json_encode($inline_button)."','".$ip."')";
								$owlPDO->exec($str);
								
								$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid = '".$notelegram[$val['karyawanid']]."' and text='/SENDNOTIF'";
								$owlPDO->exec($query);
							}
						}
					}
				}
			}
			
			array_multisort($listpencprd,SORT_DESC);

			$tab="<b>[Notifikasi] Pencapaian produksi berdasarkan kategori</b>\n";
			$tab.="Periode : <b>".$periode."</b>\n";
			$tab.="<b>Penjelasan :</b>\n";
			foreach($kelasprod as $kelas => $namakelas){
				$tab.="<i>Kategori <b>".$kelas."</b> : ".$namakelas."</i>\n";
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



			$kodejenis="PENC";
			$str="select * from ".$dbname.".setup_notification_dt where telegram='1' and kodejenis='".$kodejenis."'";
			$res=fetchdata($str);
			foreach($res as $val){
				if($notelegram[$val['karyawanid']]!=''){
					$lokasitugas = getKary($val['karyawanid'],'lokasitugas');
					$golongan    = getKary($val['karyawanid'],'kodegolongan');
					$jabatan     = getKary($val['karyawanid'],'kodejabatan');
					$subbagian   = getKary($val['karyawanid'],'subbagian');
					$regionuser  = $region[$lokasitugas];
					
					#golongan 4F asst kode >= 47
					#golongan 5A mgr kode >=43 sd <=46
					#golongan 6a gm dan pc 39 sd 42
					#golongan 7a BOD 35 38
					
					// $golongan = 43;
					// $lokasitugas = 'SD1E';
					
					if($golongan<100 and $golongan>=47 and getNamaOrg($lokasitugas,'tipe')=='KEBUN'){
						# ASST;
						if($subbagian!=''){				
							$jeniskirman = 'divisi';
						}else{				
							$jeniskirman = 'kebun';
						}
					}elseif($golongan<47 and $golongan>=43 and getNamaOrg($lokasitugas,'tipe')=='KEBUN'){
						# MANAGER
						$jeniskirman = 'kebun';
					}elseif($golongan<43 and $golongan>=39 and getNamaOrg($lokasitugas,'tipe')=='KEBUN'){
						if($jabatan=='128'){ # PC
							$jeniskirman = 'kspagro';
						}else{ # GM
							if($regionuser!=''){					
								$jeniskirman = 'region';
							}else{
								$jeniskirman = 'kspagro';					
							}
						}
					}elseif($golongan<39 and $golongan>=35){
						echo "BOD";
						$jeniskirman = 'kspagro';
					}else{
						#bukan orang kebun
						//$jeniskirman = 'kspagro';
					}
					
					$inline_button=[];
					switch($jeniskirman){
						case'divisi':	
							# lokasi kebun
							# asst divisi
							$message_text=$tab.$tampildivisi[$subbagian];
							echo str_replace("\n","<br>",$message_text);
						break;
						case'kebun':
							# estate manager
							$message_text=$tab.$tampilkebun[$lokasitugas];
							$e=0; $jlh=count($listdivisiperkbn[$lokasitugas]);
							foreach($listdivisiperkbn[$lokasitugas] as $divisi){
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
							$message_text=$tab.$tampilregion[$regionuser];
							$e=0; $jlh=count($listkebunperreg[$regionuser]);
							foreach($listkebunperreg[$regionuser] as $kebun){	
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
					
					
					$sql = "select count(*) as jumlah from ".$dbname.".tel_activity where telegramid='".$notelegram[$val['karyawanid']]."' and waktu like '".date('Y-m-d')."%' and karyawanid='".$val['karyawanid']."' and text = '/SENDNOTIF' and full_text='[Notifikasi] Pencapaian produksi berdasarkan kategori'";
					$rql = fetchdata($sql);
					# range jam
					if((date("H")>='10' and date("H")<'14') and $rql[0]['jumlah']==0){
						send_reply($notelegram[$val['karyawanid']], $message_text,$inline_button);
						
						$str="insert into ".$dbname.".tel_activity (username,register,telegramid,file,karyawanid,type,text,full_text,respond,ip)
						values('".$uname[$val['karyawanid']]."','REG','".$notelegram[$val['karyawanid']]."','".$_SERVER['PHP_SELF']."','".$val['karyawanid']."','private','/SENDNOTIF','[Notifikasi] Pencapaian produksi berdasarkan kategori','".$message_text."\ninline_button = ".json_encode($inline_button)."','".$ip."')";
						$owlPDO->exec($str);
						
						$query = "delete from `".$dbname."`.`tel_activity` where waktu < '".$tgllalu."' and text!='/SARAN' and telegramid = '".$notelegram[$val['karyawanid']]."' and text='/SENDNOTIF'";
						$owlPDO->exec($query);
					}
				}
			}	
		} #tutup if kirim setiap tanggal

		# NOTIFIKASI PENCAPAIAN PRODUKSI DAN BLOK TIDAK TERPANEN
		
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback(); 
		send_reply("1783000758","[Notifikasi] (2) from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
		// echo "Error, " . addslashes($e->getMessage());
		// die();
	}


	if(in_array(date('i'),$tiap15menit)){	
		include('generatepdf.php');
	}
} #tutup if > jam 04



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

function send_reply($telegram_id, $message_text,$inline_button=array()){
	$tipelogin='local';
	$tipelogin='server';
	
	if($tipelogin=='server'){
		$idbot = "@owlksp_robot";
		//$token = "1348581495:AAEsK4yzkWGxNvcmIuMxwZYoFdLHOtSOsMw";	
		$token = "1348581495:AAHD9WS9wQw0tyMq0-OdGzyJCNAT6KsAdyQ";	
	}else{
		$idbot = "@owlnotifbot";
		$token = "1624052900:AAGAxZ7fWKOhM-6SKtMEJG7Lm0M0Cht6ZrY";
	}
	
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
	
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		),
	);
	
	if($telegram_id!='' and $message_text!=''){		
		$context  = stream_context_create($options);
		$kirim = "https://api.telegram.org/bot" . $token . "/sendMessage";
		$result = file_get_contents($kirim, false, $context);
	}
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
function getTab2($jumlah){	
	$tab="";
	for($i=1;$i<=$jumlah;$i++){
		$tab.=" ";
	}
	
	return $tab;
}
function kurangmenit2($tanggal, $jlhmenit, $format='minutes'){
	// $date = date_create(date('d-m-Y H:i:s'));
	$date = date_create($tanggal);
	date_add($date, date_interval_create_from_date_string('-'.$jlhmenit.' '.$format));
	return date_format($date, 'Y-m-d H:i:s');
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