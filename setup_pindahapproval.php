<?
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

try {
$owlPDO->beginTransaction();
	
	$tglhi=date("Y-m-d");
	
	## KHUSUS PR
	$str = "select * from ".$dbname.".setup_slaapproval where status='1' and jenisapproval='PR'";
	$res = fetchdata($str);
	foreach($res as $val){
		#ambil list approval
		$str1 = "select * from ".$dbname.".approval where jenispersetujuan='".$val['jenisapproval']."' and status='0' and karyawanid='".$val['dariuser']."'";
		$res1 = fetchdata($str1);
		foreach($res1 as $bar){
			#ambil tanggal berdasarkan tanggal approval level sebelumnya
			$sql = "select substr(tanggal,1,10) as tanggal from ".$dbname.".approval where jenispersetujuan='".$val['jenisapproval']."' and notransaksi='".$bar['notransaksi']."' and level='".($bar['level']-1)."' limit 1";
			$req = fetchdata($sql);
			if(count($req)>0){				
				$hari = selisitgl($tglhi,$req[0]['tanggal']);
				
				#jika lewat dari tanggal yg sudah di tentukan maka alihkan
				if($hari > $val['hari']){
					##GET REQUESTER
					$str2        = "select requester, unit from ".$dbname.".log_prapoht where nopp='".$bar['notransaksi']."'";
					$res2        = fetchdata($str2);					$requester   = $res2[0]['requester'];
					$koderorg    = $res2[0]['unit'];
					$optdepartmen= makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$requester."'");
					$departemen  = $optdepartmen[$requester];					
					##GET COUNT APPROVAL
					$countApp = getCountApproval($val['jenisapproval'],$koderorg,$departemen);
					
					if($bar['level']<$countApp){
						$level = $bar['level']+1;
						$arrListApp = listApprove($level,$val['jenisapproval'],$koderorg,$departemen);
						foreach($arrListApp as $key => $n){
							if($val['keuser']==$n['karyawanid']){
								$query = "insert into ".$dbname.".approval (notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
								values('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".($bar['level']+1)."','".$val['keuser']."','0','Auto from ".getNamaKaryawan($val['dariuser'])."','','".date("Y-m-d H:i:s")."')";
								$owlPDO->exec($query);

								$query = "update " . $dbname . ".approval set status='1', komentar='Auto approve by system.' where nourut = '".$bar['nourut']."'"; 
								$owlPDO->exec($query);
								
								$query = "insert into ".$dbname.".approval_sla (notransaksi, jenispersetujuan, level, karyawanid, tanggalajukan, karyawanidtujuan, tanggaltujuan, komentar, keterangan, tanggal)
								values('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$req[0]['tanggal']."','".$val['keuser']."','".$tglhi."','".$bar['komentar']."','".$bar['keterangan']."','".date("Y-m-d H:i:s")."')";
								$owlPDO->exec($query);
							}
						}
					}
				}
			}
		}
	}
	
	## KHUSUS PO
	$str = "select * from ".$dbname.".setup_slaapproval where status='1' and jenisapproval='PO'";
	$res = fetchdata($str);
	foreach($res as $val){
		#ambil list approval
		$str1 = "select * from ".$dbname.".approval where jenispersetujuan='".$val['jenisapproval']."' and status='0' and karyawanid='".$val['dariuser']."'";
		$res1 = fetchdata($str1);
		foreach($res1 as $bar){
			#ambil tanggal berdasarkan tanggal approval level sebelumnya
			$sql = "select substr(tanggal,1,10) as tanggal from ".$dbname.".approval where jenispersetujuan='".$val['jenisapproval']."' and notransaksi='".$bar['notransaksi']."' and level='".($bar['level']-1)."' limit 1";
			$req = fetchdata($sql);
			if(count($req)>0){				
				$hari = selisitgl($tglhi,$req[0]['tanggal']);
				#jika lewat dari tanggal yg sudah di tentukan maka alihkan
				if($hari > $val['hari']){
					# ambil level + 1
					$a = "select * from ".$dbname.".approval where jenispersetujuan='".$val['jenisapproval']."' and notransaksi='".$bar['notransaksi']."' and level='".($bar['level']+1)."' limit 1";
					$b = fetchdata($a);
					if(count($b)>0){
						if($val['keuser']==$b[0]['karyawanid'] and $b[0]['status']=='0'){
							$query = "update " . $dbname . ".approval set status='1', komentar='Approve' where nourut = '".$bar['nourut']."'"; 
							$owlPDO->exec($query);
							
							$query = "insert into ".$dbname.".approval_sla (notransaksi, jenispersetujuan, level, karyawanid, tanggalajukan, karyawanidtujuan, tanggaltujuan, komentar, keterangan, tanggal)
							values('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$req[0]['tanggal']."','".$val['keuser']."','".$tglhi."','".$bar['komentar']."','".$bar['keterangan']."','".date("Y-m-d H:i:s")."')";
							$owlPDO->exec($query);
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
	kirimtelegram("1783000758","[pindahapproval] Error from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
	echo "Error, " . addslashes($e->getMessage());
	die();
}
?>