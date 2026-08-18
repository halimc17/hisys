<?
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$tglskrg = date('Y-m-d');
$tglskrg2 = date('Y-m-d H:i:s');

$strx="select * from ".$dbname.".setup_notification_ht where status='1'";
$resx=fetchdata($strx);
foreach($resx as $keyx=>$valx){
	## Tanggal Jatuh Tempo STNK
	if($valx['kodejenis']=='TJTS'){
		## HIDE NOTIFICATION ##
		$str = "select * from ".$dbname.".list_notification where kodenotification='".$valx['kodejenis']."'";
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			$tglakhirnotif = substr($val['tanggal'],0,10);
			
			$str2="select * from ".$dbname.".vhc_5master where kodevhc='".$val['kodetransaksi']."'";
			$res2=fetchdata($str2);
			if(count($res2) > 0){
				$kodevhc = $res2[0]['kodevhc'];
				$tglakhirstnk = $res2[0]['tglakhirstnk'];
				
				$selisihnotif = remenderselisihhari($tglakhirnotif,$tglakhirstnk);
				
				if($selisihnotif > 30){
					$strq="update ".$dbname.".list_notification set shownotif='1' where id='".$val['id']."'";
					try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
				}
			}
		}
		
		## INSERT NOTIFICATION ##
		$str="select * from ".$dbname.".vhc_5master where tglakhirstnk!='0000-00-00' and status='1'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$tglakhirstnk = $val['tglakhirstnk'];
			$selisih = remenderselisihhari($tglskrg,$tglakhirstnk);
			
			if($selisih <= 30 and $selisih >= 0){
				$detail = $valx['namajenis']." untuk kendaraan dengan kode ".$val['kodevhc']." dan plat no ".$val['nopol']." adalah ".tanggalnormal($tglakhirstnk);
				
				$str2 = "select * from ".$dbname.".list_notification where detail='".$detail."'";
				$res2 = fetchdata($str2);
				$countnotif = count($res2);
				
				if($countnotif > 0){
					
				}else{
					$str2="select * from ".$dbname.".setup_notification_dt where kodejenis='".$valx['kodejenis']."'";
					$res2=fetchdata($str2);
					foreach($res2 as $key2=>$val2){
						$strq="insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ('','".$val['kodevhc']."','".$valx['kodejenis']."','".$detail."','".$val2['karyawanid']."','".$val2['kodedepartement']."','".$val2['kodetipekaryawan']."','".$val2['kodejabatan']."','0','0','".$tglakhirstnk."')";
						try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
					}
				}
			}
		}
	}
	

	if($valx['kodejenis']=='TJTAP'){
		$str = "select * from ".$dbname.".setup_notification_dt where kodejenis='".$valx['kodejenis']."'";
		$notifDt = fetchdata($str);
		

		
		$str2="select ifnull(b.keterangan1,'active') as closed,ifnull(c.kodetransaksi ,'new') as notif,c.id as notifid,a.* from ".$dbname.".keu_tagihanht a 
		left join ".$dbname.".keu_kasbankdt b on b.keterangan1 = a.noinvoice
		left join ".$dbname.".list_notification c on c.kodetransaksi = a.noinvoice
		where c.shownotif = '0' or c.kodetransaksi is null";
		//and a.jatuhtempo='".$min1Month ."'";
		$res2=fetchdata($str2);
		$tagihan = array();
		if(count($res2) > 0){
			foreach($res2 as $v){
				if($v['notif'] == 'new'){
					## INSERT NOTIFICATION ##
					$d = array();
					$d['showdate'] = $min1Month = date("Y-m-d",strtotime("-1 Month",strtotime($v['jatuhtempo'])));;
					$d['noinvoice'] = $v['noinvoice'];
					$d['detail'] = $valx['namajenis']." untuk no invoice ".$v['noinvoice']." adalah ".tanggalnormal($v['jatuhtempo']);
					$tagihan[] = $d;
				}elseif($v['closed'] != "active"){
					## CLOSE NOTIFICATION ##
					$closeQ="update ".$dbname.".list_notification set shownotif='1' where id='".$v['notifid']."'";
					try{$owlPDO->exec($closeQ);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
				}
			}
			$strq = array();
			if(count($tagihan) > 0){
				//data insert tagihan notif
				foreach($notifDt as $tujuan){
					foreach($tagihan as $vd){
						$strq[]="('','".$vd['noinvoice']."','".$valx['kodejenis']."','".$vd['detail']."','".$tujuan['karyawanid']."','".$tujuan['kodedepartement']."','".$tujuan['kodetipekaryawan']."','".$tujuan['kodejabatan']."','0','0','".$vd['showdate']."')";
					}
				}
				//Exec Batch
				if(count($strq)> 0){
					$strqJoin = implode(",",$strq);
					$strExec = "insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ";
					$strExec .= $strqJoin.";";
					try{$owlPDO->exec($strExec);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
				}
				
			}
		}
		
		
		
	}
	if($valx['kodejenis']=='TKIR'){
		## HIDE NOTIFICATION ##
		$str = "select * from ".$dbname.".list_notification where kodenotification='".$valx['kodejenis']."'";
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			$tglakhirnotif = substr($val['tanggal'],0,10);
			
			$str2="select * from ".$dbname.".vhc_5master where kodevhc='".$val['kodetransaksi']."'";
			$res2=fetchdata($str2);
			if(count($res2) > 0 ){
				$kodevhc = $res2[0]['kodevhc'];
				$tglakhirkir = $res2[0]['tglakhirkir'];
				
				$selisihnotif = remenderselisihhari($tglakhirnotif,$tglakhirkir);
				
				if($selisihnotif > 30){
					$strq="update ".$dbname.".list_notification set shownotif='1' where id='".$val['id']."'";
					try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
				}
			}
		}
		
		## INSERT NOTIFICATION ##
		$str="select * from ".$dbname.".vhc_5master where tglakhirkir!='0000-00-00' and status='1'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$tglakhirkir = $val['tglakhirkir'];
			$selisih = remenderselisihhari($tglskrg,$tglakhirkir);
			
			if($selisih <= 30 and $selisih >= 0){
				$detail = $valx['namajenis']." untuk kendaraan dengan kode ".$val['kodevhc']." dan plat no ".$val['nopol']." adalah ".tanggalnormal($tglakhirkir);
				
				$str2 = "select * from ".$dbname.".list_notification where detail='".$detail."'";
				$res2 = fetchdata($str2);
				$countnotif = count($res2);
				
				if($countnotif > 0){
					
				}else{
					$str2="select * from ".$dbname.".setup_notification_dt where kodejenis='".$valx['kodejenis']."'";
					$res2=fetchdata($str2);
					foreach($res2 as $key2=>$val2){
						$strq="insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ('','".$val['kodevhc']."','".$valx['kodejenis']."','".$detail."','".$val2['karyawanid']."','".$val2['kodedepartement']."','".$val2['kodetipekaryawan']."','".$val2['kodejabatan']."','0','0','".$tglakhirkir."')";
						try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
					}
				}
			}
		}
	}
	
	if($valx['kodejenis']=='TLSG'){
		## HIDE NOTIFICATION ##
		$str = "select * from ".$dbname.".list_notification where kodenotification='".$valx['kodejenis']."'";
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			$tglakhirnotif = substr($val['tanggal'],0,10);
			
			$str2="select * from ".$dbname.".vhc_5master where kodevhc='".$val['kodetransaksi']."'";
			$res2=fetchdata($str2);
			$kodevhc = $res2[0]['kodevhc'];
			$tglakhirleasing = $res2[0]['tglakhirleasing'];
			
			$selisihnotif = remenderselisihhari($tglakhirnotif,$tglakhirleasing);
			
			if($selisihnotif > 7){
				$strq="update ".$dbname.".list_notification set shownotif='1' where id='".$val['id']."'";
				try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
			}
		}
		
		## INSERT NOTIFICATION ##
		$str="select * from ".$dbname.".vhc_5master where tglakhirleasing!='0000-00-00' and status='1'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$tglakhirleasing = $val['tglakhirleasing'];
			$selisih = remenderselisihhari($tglskrg,$tglakhirleasing);
			
			if($selisih <= 7 and $selisih >= 0){
				$detail = $valx['namajenis']." untuk kendaraan dengan kode ".$val['kodevhc']." dan plat no ".$val['nopol']." adalah ".tanggalnormal($tglakhirleasing);
				
				$str2 = "select * from ".$dbname.".list_notification where detail='".$detail."'";
				$res2 = fetchdata($str2);
				$countnotif = count($res2);
				
				if($countnotif > 0){
					
				}else{
					$str2="select * from ".$dbname.".setup_notification_dt where kodejenis='".$valx['kodejenis']."'";
					$res2=fetchdata($str2);
					foreach($res2 as $key2=>$val2){
						$strq="insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ('','".$val['kodevhc']."','".$valx['kodejenis']."','".$detail."','".$val2['karyawanid']."','".$val2['kodedepartement']."','".$val2['kodetipekaryawan']."','".$val2['kodejabatan']."','0','0','".$tglakhirleasing."')";
						try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
					}
				}
			}
		}
	}
	
	if($valx['kodejenis']=='TPLSG'){
		## HIDE NOTIFICATION ##
		$str = "select * from ".$dbname.".list_notification where kodenotification='".$valx['kodejenis']."'";
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			$tglakhirnotif = substr($val['tanggal'],0,10);
			
			$str2="select * from ".$dbname.".keu_leasingdt where notransaksi='".$val['kodetransaksi']."' and statuskasbank='0'";
			$res2=fetchdata($str2);
			if(count($res2) > 0 ){
				$kodevhc = $res2[0]['kodevhc'];
				$tglakhirleasing = $res2[0]['tgl_transaksi'];
				
				$selisihnotif = remenderselisihhari($tglakhirnotif,$tglakhirleasing);
				
				if($selisihnotif > 7){
					$strq="update ".$dbname.".list_notification set shownotif='1' where id='".$val['id']."'";
					try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
				}
			}
		}
		
		## INSERT NOTIFICATION ##
		$str="select * from ".$dbname.".keu_leasingdt where tgl_transaksi!='0000-00-00' and statuskasbank='0'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$tglakhirleasing = $val['tgl_transaksi'];
			$selisih = remenderselisihhari($tglskrg,$tglakhirleasing);
			
			if($selisih <= 3 and $selisih >= 0){
				$detail = $valx['namajenis']." untuk leasing dengan kode ".$val['notransaksi']." dan tenor ke- ".$val['tenor_ke']." adalah ".tanggalnormal($tglakhirleasing);
				
				$str2 = "select * from ".$dbname.".list_notification where detail='".$detail."'";
				$res2 = fetchdata($str2);
				$countnotif = count($res2);
				
				if($countnotif > 0){
					
				}else{
					$str2="select * from ".$dbname.".setup_notification_dt where kodejenis='".$valx['kodejenis']."'";
					$res2=fetchdata($str2);
					foreach($res2 as $key2=>$val2){
						$strq="insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ('','".$val['notransaksi']."','".$valx['kodejenis']."','".$detail."','".$val2['karyawanid']."','".$val2['kodedepartement']."','".$val2['kodetipekaryawan']."','".$val2['kodejabatan']."','0','0','".$tglakhirleasing."')";
						try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
					}
				}
			}
		}
	}

	if($valx['kodejenis']=='TLOG'){
		## HIDE NOTIFICATION ##
		$str = "select * from ".$dbname.".list_notification where kodenotification='".$valx['kodejenis']."'";
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			$str2="select *,max(tanggal) as tanggal from ".$dbname.".vhc_runht where tanggal!='0000-00-00' and kodevhc='".$val['kodetransaksi']."'";
			$res2=fetchdata($str2);
			$kodevhc = $res2[0]['kodevhc'];
			$tglakhirtrk = $res2[0]['tanggal'];
			
			$selisihnotif = remenderselisihhari($tglakhirtrk,$tglskrg);
			
			if($selisihnotif <= 7){
				$strq="update ".$dbname.".list_notification set shownotif='1' where id='".$val['id']."'";
				try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
			}
		}
		
		## INSERT NOTIFICATION ##
		$str="select * from ".$dbname.".vhc_5master where status='1'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$str2="select *,max(tanggal) as tanggal from ".$dbname.".vhc_runht where tanggal!='0000-00-00' and kodevhc='".$val['kodevhc']."'";
			$res2=fetchdata($str2);
			$tglakhirtrk = $res2[0]['tanggal'];
			
			$selisih = remenderselisihhari($tglakhirtrk,$tglskrg);
			
			if($selisih > 7){
				$detail = $valx['namajenis']." terakhir untuk kendaraan dengan kode ".$val['kodevhc']." dan plat no ".$val['nopol']." ".($tglakhirtrk==''?'tidak ada disistem':'adalah '.tanggalnormal($tglakhirtrk));
				
				$str2 = "select * from ".$dbname.".list_notification where detail='".$detail."'";
				$res2 = fetchdata($str2);
				$countnotif = count($res2);
				
				if($countnotif > 0){
					
				}else{
					$str2="select * from ".$dbname.".setup_notification_dt where kodejenis='".$valx['kodejenis']."'";
					$res2=fetchdata($str2);
					foreach($res2 as $key2=>$val2){
						$strq="insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ('','".$val['kodevhc']."','".$valx['kodejenis']."','".$detail."','".$val2['karyawanid']."','".$val2['kodedepartement']."','".$val2['kodetipekaryawan']."','".$val2['kodejabatan']."','0','0','".$tglakhirtrk."')";
						try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}
					}
				}
				
				// echo $val['kodevhc']."__".$res2[0]['tanggal']."__".$selisih."<br>";				
			}
		}
	}
	
	## HARGA TERAKHIR BARANG ##
	if($valx['kodejenis']=='HTB'){
		$arrkar = picnotification($valx['kodejenis']);
		$str="select * from ".$dbname.".log_5hargaterakhir where status='1' order by kodebarang asc, unit asc";		
		$res=fetchdata($str);
		foreach($res as $val){
			$tglhargatrk = $val['tanggal'];
			$selisitgl = selisitgl($tglskrg,$tglhargatrk);
			$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			$detail = $valx['namajenis']." untuk material/barang dengan kode ".$val['kodebarang	']." dengan nama : ".$optnmbarang[$val['kodebarang']]." di unit ".$val['unit']." sudah mencapai batas waktu lebih dari 90 Hari, Silahkan update harga terakhir material/barang\nHarga terakhir : ".tanggalnormal($tglhargatrk);
			if($selisitgl > 90){
				foreach($arrkar as $valkar){
					$strq="insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('','".$val['kodebarang']."','".$valx['kodejenis']."','".$detail."','".$valkar."','0','0','".$tglskrg2."')";
					try{$owlPDO->exec($strq);}catch(PDOException $e){continue;}
				}
			}
		}
	}
	
	## STOK MINIMUM BARANG ##
	if($valx['kodejenis']=='STOKMIN'){
		$arrkar = picnotification($valx['kodejenis']);
		$str="select gudang,kodebarang,stok from ".$dbname.".log_5minimunstok where stok > 0";
		$res=fetchdata($str);
		foreach($res as $val){
			$strx="select sum(saldoakhirqty) as saldoakhirqty from ".$dbname.".log_5saldobulanan where kodebarang='".$val['kodebarang']."' and kodegudang='".$val['gudang']."' order by periode desc limit 1";
			$resx=fetchdata($strx);
			$saldogudang = $resx[0]['saldoakhirqty'];
			if($saldogudang==''){
				$saldogudang=0;
			}
			if($saldogudang <= $val['stok']){
				$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
				$detail = $valx['namajenis']." untuk material/barang dengan kode ".$val['kodebarang	']." dengan nama : ".$optnmbarang[$val['kodebarang']]." di Gudang ".$val['gudang']." sudah mencapai batas stok minimum; stok gudang : ".$saldogudang.", Silahkan lakuakan Purchase Request untuk barang tersebut";
				foreach($arrkar as $valkar){
					$loktugas = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$valkar."'");
					if(substr($val['gudang'],0,4)==$loktugas[$valkar]){
						$strq="insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('','".$val['kodebarang']."','".$valx['kodejenis']."','".$detail."','".$valkar."','0','0','".$tglskrg2."')";
						try{$owlPDO->exec($strq);}catch(PDOException $e){continue;}
					}
				}
			}
		}
	}


	if($valx['kodejenis']=='NDTK'){
		$textx="<html>
		<head>
		<body>
		Dengan Hormat,
		<br>
		<br>
		Pada hari ini, tanggal ".date('d-m-Y')." system memberitahukan notifikasi karyawan kontrak, percobaan dan pensiun kepada bapak/ibu.
		<br>
		<br>
		<table class=sortable border=1 cellspacing=1> 
		<thead><tr class=rowheader>
		<td>No</td>
		<td>NIK</td>
		<td>Nama Karyawan</td>
		<td>Unit</td>
		<td>Tanggal</td>
		<td>Deskripsi</td></tr></thead><tbody>
		";
		$percobaanx=array();
		$date1 = new DateTime($tglskrg);
		$date1->modify('+1 month');
		$str="select * from ".$dbname.".datakaryawan where tanggalkeluar='".$date1->format('Y-m-d')."' and statuskaryawan='Percobaan' and tipekaryawan in ('1','0') order by namakaryawan";
		//echo $str;
		$res=fetchdata($str);
		$no=0;
		foreach($res as $key=>$val){
			$no++;
			//$tanggalkeluar = $val['tanggalkeluar'];
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['lokasitugas']."'");
			@$percobaanx[$val['lokasitugas']]['percobaan']+=1;
			$textx.="<tr class=rowcontent><td>" . $no . "</td><td>" . $val['nik'] . "</td><td>" . $val['namakaryawan'] . "</td><td>" . $nmorg[$val['lokasitugas']] . "</td><td>" . tanggalnormal($date1->format('Y-m-d')) . "</td><td>Percobaan</td></tr>";
		}
		$str="select * from ".$dbname.".datakaryawan where tanggalkeluar='".$date1->format('Y-m-d')."' and statuskaryawan='Kontrak' and tipekaryawan in ('1','0') order by namakaryawan";
		//echo $str;
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$no++;
			//$tanggalkeluar = $val['tanggalkeluar'];
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['lokasitugas']."'");
			@$percobaanx[$val['lokasitugas']]['kontrak']+=1;
			$textx.="<tr class=rowcontent><td>" . $no . "</td><td>" . $val['nik'] . "</td><td>" . $val['namakaryawan'] . "</td><td>" . $nmorg[$val['lokasitugas']] . "</td><td>" . tanggalnormal($date1->format('Y-m-d')) . "</td><td>Kontrak</td></tr>";		
		}
		$str="select * from ".$dbname.".datakaryawan where 1=1 and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".date('Y-m-d')."') and month(tanggallahir) = month(curdate()) and day(tanggallahir)= day(curdate()) and year(curdate())-year(tanggallahir)>57 and tipekaryawan in ('1','0') order by namakaryawan";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$no++;
			//$tanggalkeluar = $val['tanggalkeluar'];
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['lokasitugas']."'");
			@$percobaanx[$val['lokasitugas']]['pensiun']+=1;
			$textx.="<tr class=rowcontent><td>" . $no . "</td><td>" . $val['nik'] . "</td><td>" . $val['namakaryawan'] . "</td><td>" . $nmorg[$val['lokasitugas']] . "</td><td>" . tanggalnormal($date1->format('Y-m-d')) . "</td><td>Pensiun</td></tr>";
		}
		$textx.="</tbody><tfoot></tfoot></table>
		<br>
		<br>
		Regards,
		<br>
		OWL-Plantation
		</body>
		 </head>
	   </html>";
		$detail ='';
		foreach ($percobaanx as $keyzx => $valzx) {
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$keyzx."'");
			$detail .= " Jumlah ".$percobaanx[$keyzx]['percobaan']." karyawan percobaan di unit ".$nmorg[$keyzx]." akan berakhir pada tanggal ".tanggalnormal($date1->format('Y-m-d'));
		}

		$str2 = "select * from ".$dbname.".list_notification where detail='".$detail."'";
		$res2 = fetchdata($str2);
		$countnotif = count($res2);
				
		if($countnotif > 0){
					
		}else{
			$str2="select * from ".$dbname.".setup_notification_dt where kodejenis='".$valx['kodejenis']."'";
			$res2=fetchdata($str2);
			foreach($res2 as $key2=>$val2){
				$strq="insert into ".$dbname.".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,kodedepartement,kodetipekaryawan,kodejabatan,readnotif,shownotif,tanggal) values ('','Notifkaryawan','".$valx['kodejenis']."','".$detail."','".$val2['karyawanid']."','".$val2['kodedepartement']."','".$val2['kodetipekaryawan']."','".$val2['kodejabatan']."','0','0','".$date1->format('Y-m-d')."')";
				try{$owlPDO->exec($strq);}catch(PDOException $e){echo "Error, ".addslashes($e->getMessage());}

				$namaorganisasi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");

			$to = getUserEmail($val2['karyawanid']);
			//$namapengaju = getNamaKaryawan($_SESSION['standard']['userid']);
			$subject="[Notifikasi]Datakaryawan Kontrak, Percobaan dan Pensiun ";

				if(isset($to))
					$kirim = kirimEmail($to, '', $subject, $textx);

			}
		}
	}

}

function remenderselisihhari($tgl1,$tgl2){
	//format tangal Y-m-d // 2015-12-31
	$selisih = (((strtotime ($tgl2) - strtotime ($tgl1)))/(60*60*24));
	return $selisih;
}
?>