<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi = checkPostGet('notransaksi','');
$tanggal = tanggalsystem(checkPostGet('tanggal',''));
$kmawal = checkPostGet('kmawal','');
$kmakhir = checkPostGet('kmakhir','');
$jlhbbm = checkPostGet('jlhbbm','');
$method = checkPostGet('method','');
$totalharga = checkPostGet('totalharga','');
// exit("error : ".$str);
if ($method == 'delete') {
    $tanggal = $_POST['tanggal'];
    $str = "delete from " . $dbname . ".sdm_penggantiantransportdt where notransaksi='" . $notransaksi . "' and tanggal='" . $tanggal . "'";

    //ambil nilai solar yang diambil 
    $stru = "select hargatotal from  " . $dbname . ".sdm_penggantiantransportdt where notransaksi='" . $notransaksi . "' and tanggal='" . $tanggal . "'";
    $resf = $owlPDO->query($stru) or die(print " Gagal: " . PDOException::getMessage());
    $resf->setFetchMode(PDO::FETCH_OBJ);
    while ($baru = $resf->fetch()) {
        $totalharga = $baru->hargatotal;
    }

    //create string untuk meubah nilai claim pada header
    $str1 = "update  " . $dbname . ".sdm_penggantiantransport set totalklaim=(totalklaim-" . $totalharga . ") where notransaksi='" . $notransaksi . "'";
} 
else if ($method == 'insert') {
	
	$cek="SELECT karyawanid,periode FROM " . $dbname . ".sdm_penggantiantransport where notransaksi='" . $notransaksi . "' ";
	$rescek = $owlPDO->query($cek) or die(print " Gagal: " . PDOException::getMessage());
    $rescek->setFetchMode(PDO::FETCH_OBJ);
    while ($baru = $rescek->fetch()) {
        $karyawanid = $baru->karyawanid;
		$periode = $baru->periode;
    }
	//exit("error :".$karyawanid);
	$cekjml="SELECT sum(jlhbbm) as jlhbbm  FROM sdm_penggantiantransport a, sdm_penggantiantransportdt b
		where a.notransaksi=b.notransaksi and a.periode='".$periode."'
		and a.periode=substr(b.tanggal,1,7) and a.karyawanid='".$karyawanid."' ";
	
	$rescek = $owlPDO->query($cekjml) or die(print " Gagal: " . PDOException::getMessage());
    $rescek->setFetchMode(PDO::FETCH_OBJ);
    while ($baru = $rescek->fetch()) {
        $jlhbbm1 = $baru->jlhbbm;		
    }
	
	$cekpl="SELECT plafond FROM sdm_5plafond_bbm a, datakaryawan b where b.karyawanid='".$karyawanid."' and 				
		a.kodejabatan=b.kodejabatan and a.tahun_berlaku=substr('".$periode."',1,4)";
	$rescek = $owlPDO->query($cekpl) or die(print " Gagal: " . PDOException::getMessage());
    $rescek->setFetchMode(PDO::FETCH_OBJ);
    while ($baru = $rescek->fetch()) {
        $plafond = $baru->plafond;		
    }
	//exit("error :".$cekpl);
	
	$cekhk="SELECT karyawanid FROM sdm_absensidt where karyawanid='".$karyawanid."' and 				
		tanggal = DATE_FORMAT('".$tanggal."', '%Y-%m-%d') and substr(tanggal,1,7) ='".$periode."' and absensi='H'";
	$reshk = $owlPDO->query($cekhk) or die(print " Gagal: " . PDOException::getMessage());
    $reshk->setFetchMode(PDO::FETCH_OBJ);
    while ($baruhk = $reshk->fetch()) {
        $absen = $baruhk->karyawanid;		
    }
	
	//exit("error :".$plafond);
	if($plafond==''||$plafond<0){
		exit("error : Silahkan Set Up Pelafon BBM !");
	}
	// else if($absen=='' || $absen<1){
		// #exit("error : Tidak ada Absensi di tanggal ".$tanggal);
	// }	
	else if(($jlhbbm1+$jlhbbm)<=$plafond){		
	
		$str = "insert into " . $dbname . ".sdm_penggantiantransportdt (`notransaksi`,`tanggal`,`jlhbbm`,`hargatotal`,`kmawal`,`kmakhir`)
			  values('" . $notransaksi . "'," . $tanggal . "," . $jlhbbm . "," . $totalharga . "," . $kmawal . "," . $kmakhir . ")";
		$str1 = "update  " . $dbname . ".sdm_penggantiantransport set totalklaim=(totalklaim+" . $totalharga . ") where notransaksi='" . $notransaksi . "'";
	}
	else{ exit("Error : Sudah melebihi plafond yang diberikan !"); }
} 
else if ($method == 'update') {
	
	$cek="SELECT karyawanid,periode FROM " . $dbname . ".sdm_penggantiantransport where notransaksi='" . $notransaksi . "' ";
	$rescek = $owlPDO->query($cek) or die(print " Gagal: " . PDOException::getMessage());
    $rescek->setFetchMode(PDO::FETCH_OBJ);
    while ($baru = $rescek->fetch()) {
        $karyawanid = $baru->karyawanid;
		$periode = $baru->periode;
    }
	$cekjml="SELECT sum(jlhbbm) as jlhbbm  FROM sdm_penggantiantransport a, sdm_penggantiantransportdt b
		where a.notransaksi=b.notransaksi and a.periode='".$periode."'
		and a.periode=substr(b.tanggal,1,7) and a.karyawanid='".$karyawanid."' ";
	
	$rescek = $owlPDO->query($cekjml) or die(print " Gagal: " . PDOException::getMessage());
    $rescek->setFetchMode(PDO::FETCH_OBJ);
    while ($baru = $rescek->fetch()) {
        $jlhbbm1 = $baru->jlhbbm;		
    }
	
	$cekpl="SELECT plafond FROM sdm_5plafond_bbm a, datakaryawan b where  				
		a.kodejabatan=b.kodejabatan and a.tahun_berlaku=substr('".$periode."',1,4)";
	$rescek = $owlPDO->query($cekpl) or die(print " Gagal: " . PDOException::getMessage());
    $rescek->setFetchMode(PDO::FETCH_OBJ);
    while ($baru = $rescek->fetch()) {
        $plafond = $baru->plafond;	
	
	$cekhk="SELECT karyawanid FROM sdm_absensidt where karyawanid='".$karyawanid."' and 				
		tanggal = DATE_FORMAT('".$tanggal."', '%Y-%m-%d') and substr(tanggal,1,7) ='".$periode."' and absensi='H'";
	$reshk = $owlPDO->query($cekhk) or die(print " Gagal: " . PDOException::getMessage());
    $reshk->setFetchMode(PDO::FETCH_OBJ);
    while ($baruhk = $reshk->fetch()) {
        $absen = $baruhk->karyawanid;		
    }
	
    }
	//exit("error :".$plafond);
	if($plafond==''){
		exit("Silahkan Set Up Pelafon BBM !");
	}
	else if($absen=='' || $absen<1){
		exit("error : Tidak ada Absensi di tanggal ".$tanggal);
	}
	else if(($jlhbbm1+$jlhbbm)<=$plafond){		
	
		// $str = "insert into " . $dbname . ".sdm_penggantiantransportdt (`notransaksi`,`tanggal`,`jlhbbm`,`hargatotal`,`kmawal`,`kmakhir`)
			  // values('" . $notransaksi . "'," . $tanggal . "," . $jlhbbm . "," . $totalharga . "," . $kmawal . "," . $kmakhir . ")";
		$str1 = "update  " . $dbname . ".sdm_penggantiantransport set totalklaim=(totalklaim+" . $totalharga . ") where notransaksi='" . $notransaksi . "'";
	}
	else{ exit("Error : Sudah melebihi plafond yang diberikan !"); }
}

else {
	//exit("error : ccccccccccccc");
    $str = "";
    $str1 = $str;
}

try {
	if ($str != '') {
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {}
		if ($str1 != '') {
			try {
				$owlPDO->exec($str1);
			} catch (PDOException $e) {
				//jika tidak berhasil maka BBM dihapus dan harus input ulang
				$strx = "delete from ".$dbname.".sdm_penggantian transportdt where notransaksi='".$notransaksi."'";
				try {
					$owlPDO->exec($strx);
					echo $str1;
					echo "Error: Inconsistence calculation on Detail transaction, please re-input again";
					exit(0);
				} catch (PDOException $e) {
					echo " Gagal ".addslashes($e->getMessage());
					die();
				}
			}
		}
	}
	
	//jika berhasil semua
	$str = "select * from " . $dbname . ".sdm_penggantiantransportdt where notransaksi='" . $notransaksi . "'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no = 0;
    $tkuantitas = 0;
    $tharga = 0;
    while ($bar = $res->fetch()) {
		$no+=1;
		echo"<tr class=rowcontent>
			<td align=center>" . $no . "</td>
			<td>" . tanggalnormal($bar->tanggal) . "</td>
			<td align=right>" . number_format($bar->kmawal, 2, '.', ',') . "</td>
			<td align=right>" . number_format($bar->kmakhir, 2, '.', ',') . "</td>
			<td align=right>" . number_format($bar->jlhbbm, 2, '.', ',') . "</td>
			<td align=right id='x" . $no . "'>" . number_format($bar->hargatotal, 2, '.', ',') . "</td>
			<td align=center>
				<img src='images/application/application_delete.png' class=resicon onclick=\"deleteSolar('" . $bar->notransaksi . "','" . $bar->tanggal . "','x" . $no . "');\">
				
			</td>
		</tr>";
		$tkuantitas+= $bar->jlhbbm;
		$tharga+= $bar->hargatotal;
	}
	echo"<tr class=rowcontent>
		<td></td>
		<td></td>
		<td></td>
		<td>" . $_SESSION['lang']['total'] . "</td>
		<td align=right>" . number_format($tkuantitas, 2, '.', ',') . "</td>
		<td align=right>" . number_format($tharga, 2, '.', ',') . "</td>
		<td>-</td>
	</tr>";
} catch (PDOException $e) {
    echo " Gagal " . addslashes($e->getMessage());
	die();
}
?>