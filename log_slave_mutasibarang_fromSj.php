<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param = $_POST;
if(!isTransactionPeriod())//check if transaction period is normal
{
	echo " Error: Transaction Period missing";
} else {
	// Tipe Transaksi = 7
	$tipetransaksi=7;
	$packinglistId="";
	
	// Get Data Detail
	$qSJ = selectQuery($dbname,'log_suratjalandt',"*","nosj='".$param['nosj']."'");
	$resSJ = fetchData($qSJ);
	
	// Get List of Kode Barang
	$listBarang = '';
	$saldoSJ = array();
	foreach($resSJ as $row) {
		if($row['jenis']=='PL') {
			$qPL = selectQuery($dbname,'log_packingdt',"*","notransaksi='".
							   $row['kodebarang']."'");
			$resPL = fetchData($qPL);
			
			foreach($resPL as $row) {
				if(!empty($listBarang)) $listBarang .= ',';
				$listBarang .= "'".$row['kodebarang']."'";
				
				if(!isset($saldoSJ[$row['kodebarang']]))
					$saldoSJ[$row['kodebarang']]=0;
				$saldoSJ[$row['kodebarang']] += $row['jumlah'];
			}
		} else {
			if(!empty($listBarang)) $listBarang .= ',';
			$listBarang .= "'".$row['kodebarang']."'";
			
			if(!isset($saldoSJ[$row['kodebarang']]))
				$saldoSJ[$row['kodebarang']]=0;
			$saldoSJ[$row['kodebarang']] += $row['jumlah'];
		}
	}
	if(empty($listBarang)) {
		exit('Warning: Surat Jalan '.$param['nosj'].
			 ' tidak memiliki daftar barang');
	}
	
	
	/**
	 * Cek Saldo
	 */
	// Ambil saldo barang aktif, log_5masterbarangdt
	$resSaldo = makeOption($dbname,'log_5masterbarangdt',"kodebarang,saldoqty","kodegudang='".$param['gudang']."' and kodebarang in (".$listBarang.")");
	
	// Ambil transaksi keluar yang belum posting (Potensi pengeluaran barang)
	$qTrans = selectQuery($dbname,'log_transaksi_vw','kodebarang,sum(jumlah) as jumlah',
						  "kodegudang='".$param['gudang']."' and tipetransaksi>4 and kodebarang in (".$listBarang.")")." and statussaldo=0 group by kodebarang";
	$resTrans = fetchData($qTrans);
	foreach($resTrans as $row) {
		$resSaldo[$row['kodebarang']] -= $row['jumlah'];
	}
	
	// List Approved & Not Approved Barang
	$notApp = array();
	foreach($saldoSJ as $barang=>$saldo) {
		if(!isset($resSaldo[$barang]) or $saldo>$resSaldo[$barang]) {
			$notApp[$barang] = isset($resSaldo[$barang])? $resSaldo[$barang]: 0;
			if($notApp[$barang]<0) $notApp[$barang] = 0;
		}
	}
	
	// Insert Header jika belum ada
	/** Jika User pertama kali melakukan insert, maka ambil kembali nomor transaksi */
	$status=0;
	if(isset($_POST['isNewTrans']) and $_POST['isNewTrans']==0) {
		// Get Nomor Transaksi Terakhir
		$str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht
			where tipetransaksi>4 
			and substr(notransaksi,1,6) = '".$_SESSION['gudang'][$param['gudang']]['tahun'].$_SESSION['gudang'][$param['gudang']]['bulan']."'
			and kodegudang='".$param['gudang']."' order by notransaksi desc limit 1";	
		if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
			$str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht
				where tipetransaksi>4
				and substr(notransaksi,1,6) = '".$_SESSION['gudang'][$param['gudang']]['tahun'].$_SESSION['gudang'][$param['gudang']]['bulan']."'
				and kodegudang='".$param['gudang']."' and substr( `notransaksi` , 7, 1 ) not like '%M%'
				order by notransaksi desc limit 1";	
		}
		// Execute Query
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);		
		$num=1;
		while($bar=$res->fetch()) {
			$num=$bar->notransaksi;
			if(!empty($num)) {
				$num=intval(substr($num,6,5))+1;
			}
		}
		$num = str_pad($num, 5, "0", STR_PAD_LEFT);
		$num=$_SESSION['gudang'][$param['gudang']]['tahun'].$_SESSION['gudang'][$param['gudang']]['bulan'].$num."-GI-".$param['gudang'];
		$param['notransaksi'] = $num;
	}else{
		$status=1;
		$scek="select * from ".$dbname.".log_transaksiht where notransaksi='".$param['notransaksi']."'";
		$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
		$rcek=owlBaris($qcek);
		if($rcek==0){
			$status=0;
		}
	}
	
	if($status==0) {
		$sKdPt="select distinct induk from ".$dbname.".organisasi where kodeorganisasi='".substr($param['kegudang'],0,4)."'";
		$qKdPt=$owlPDO->query($sKdPt) or die(print " Gagal: ".PDOException::getMessage());
		$qKdPt->setFetchMode(PDO::FETCH_ASSOC);
		$rKdpt=$qKdPt->fetch();
		if(empty($rKdpt['induk'])) exit("Kode PT Penerima Kosong");
		
		$dataH = array(
			'tipetransaksi' => $tipetransaksi,
			'notransaksi' => $param['notransaksi'],
			'tanggal' => tanggalsystem($param['tanggal']),
			'kodept' => $param['pemilikbarang'],
			'untukpt' => $rKdpt['induk'],
			'keterangan' => $param['catatan'],
			'nosj' => $param['nosj'],
			'kodegudang' => $param['gudang'],
			'user' => $_SESSION['standard']['userid'],
			'post' => 0,
			'gudangx' => $param['kegudang']
		);
		$cols = array();
		foreach($dataH as $key=>$val) {
			$cols[] = $key;
		}
		$qIns = insertQuery($dbname,'log_transaksiht',$dataH,$cols);
		try{
			$owlPDO->exec($qIns); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
    }else{
		$supd="update ".$dbname.".log_transaksiht set keterangan='".$param['catatan']."' where notransaksi='".$param['notransaksi']."'";
		try{
			$owlPDO->exec($supd); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	}
	
	// Masukkan Barang ke transaksidt
	$res = "";$no=0;
	$errorDt = '';
	$errBrg = $jmlhBrg = array();
	$errBrg2 = $jmlhBrg2 = array();//print_r($notApp);exit;
	$jumlahlalu = array();
	foreach($resSJ as $row) {
		//==================ambil jumlah lalu====================
		$str="select a.jumlah as jumlah,b.nopo as nopo,a.notransaksi as notransaksi,a.waktutransaksi 
			from ".$dbname.".log_transaksidt a,
				 ".$dbname.".log_transaksiht b
			where a.notransaksi=b.notransaksi 
				and a.kodebarang='".$row['kodebarang']."'
				and a.notransaksi<='".$param['notransaksi']."'
				and tipetransaksi>4
				and b.kodegudang='".$param['gudang']."'
			order by notransaksi desc, waktutransaksi desc limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()) {
			setIt($jumlahlalu[$row['kodebarang']],0);
			$jumlahlalu[$row['kodebarang']] += $bar->jumlah;
		}
		
		if(substr($row['kodebarang'],0,2)=='PL') {
			// Jika Packing List
			$qPL = selectQuery($dbname,'log_packingdt',"*","notransaksi='".
							   $row['kodebarang']."'");
			$resPL = fetchData($qPL);
			$dataD = array();
			foreach($resPL as $row2) {
				setIt($jumlahlalu[$row2['kodebarang']],0);
				$dataD[] = array(
					'notransaksi' =>$param['notransaksi'],
					'kodebarang' =>$row2['kodebarang'],
					'nopp' => $row2['nopp'],
					'nopo' => $row2['nopo'],
					'satuan' =>$row2['satuanpo'],
					'jumlah' =>$row2['jumlah'],
					'jumlahlalu' =>$jumlahlalu[$row2['kodebarang']],
					'updateby' =>$_SESSION['standard']['userid']
				);
				$jumlahlalu[$row2['kodebarang']] += $row2['jumlah'];
			}
		} else {
			setIt($jumlahlalu[$row['kodebarang']],0);
			// Jika Barang (PO / Material)
			$dataD = array(
				'notransaksi' =>$param['notransaksi'],
				'kodebarang' =>$row['kodebarang'],
				'nopp' => $row['nopp'],
				'nopo' => $row['nopo'],
				'satuan' =>$row['satuanpo'],
				'jumlah' =>$row['jumlah'],
				'jumlahlalu' =>$jumlahlalu[$row['kodebarang']],
				'updateby' =>$_SESSION['standard']['userid']
			);
			$jumlahlalu[$row['kodebarang']] += $row['jumlah'];
		}
		$colD = array('notransaksi','kodebarang','nopp','nopo','satuan',
			'jumlah','jumlahlalu','updateby');
		
		//if(substr($row['kodebarang'],0,2)=='PL') {
		//	$wht="notransaksireferensi!='' and notransaksi='".$row['kodebarang']."'";
		//	$optCek = makeOption($dbname, 'log_packingdt', 'notransaksi,notransaksireferensi',$wht);
		//} else {
			$wht="notransaksireferensi!='' and nosj='".$param['nosj']."' and kodebarang='".
					$row['kodebarang']."' and nopp='".$row['nopp']."' and nopo='".$row['nopo']."'";
			$optCek = makeOption($dbname, 'log_suratjalandt', 'kodebarang,notransaksireferensi',$wht);
		//}
		
		$brgNotApp = '';
		if(empty($optCek[$row['kodebarang']])){ // Validasi sudah pernah dimutasikan
			if(isset($notApp[$row['kodebarang']])) { // Validasi tidak cukup stok
				$errBrg2[$row['kodebarang']]=$row['kodebarang'];
				$jmlhBrg2[$row['kodebarang']]=$row['jumlah'];
			} else {
				$qInsD = insertQuery($dbname,'log_transaksidt',$dataD,$colD);
				try{
					$owlPDO->exec($qInsD);
					
					// Update No Transaksi Referensi
					$data = array('notransaksireferensi'=>$param['notransaksi']);
					if($packinglistId!='') {
                        $qUpd = updateQuery($dbname,'log_packingdt',$data,"notransaksi='".
											$packinglistId."' and kodebarang='".$row['kodebarang'].
											"' and nopp='".$row['nopp']."' and nopo='".$row['nopo']."'");
						try{
							$owlPDO->exec($qUpd);
							$qUpd = updateQuery($dbname,'log_suratjalandt',$data,"nosj='".
                                                $param['nosj']."' and kodebarang='".$packinglistId."'");
							try{
								$owlPDO->exec($qUpd); 
							}catch(PDOException $e){
								print " Gagal  !: " . $e->getMessage() . "\n"; 
								die(); 
							}
						}catch(PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; 
							die(); 
						}
                    } else {
                        $qUpd = updateQuery($dbname,'log_suratjalandt',$data,"nosj='".
											$param['nosj']."' and kodebarang='".$row['kodebarang'].
											"' and nopp='".$row['nopp']."' and nopo='".$row['nopo']."'");
						try{
							$owlPDO->exec($qUpd); 
						}catch(PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; 
							die(); 
						}
					}
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		} else {
			$errBrg[$row['kodebarang']]=$row['kodebarang'];
			$jmlhBrg[$row['kodebarang']]=$row['jumlah'];
		}
	}
	if(!empty($errorDt)) {
		// Rollback
		$qRB = deleteQuery($dbname,'log_transaksidt',"notransaksi='".$param['notransaksi']."'");
		try{
			$owlPDO->exec($qRB);
			
			$qRB2 = deleteQuery($dbname,'log_transaksiht',"notransaksi='".$param['notransaksi']."'");
			try{
				$owlPDO->exec($qRB2); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		exit("Detail Error\n".$errorDt);
	}
	
	//ambil data untuk ditampilkan
	$strj="select a.* from ".$dbname.".log_transaksidt a 
		where a.notransaksi='".$param['notransaksi']."'";
	$resj=$owlPDO->query($strj) or die(print " Gagal: ".PDOException::getMessage());
	$resj->setFetchMode(PDO::FETCH_OBJ);
	$no=0;$tab='';
	while($barj=$resj->fetch()) {
        $no+=1;
        //ambil namabarang
        $namabarangk='';
        $strk="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$barj->kodebarang."'";
		$resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
		$resk->setFetchMode(PDO::FETCH_OBJ);
        while($bark=$resk->fetch())
        {
            $namabarangk=$bark->namabarang;
        }
        $tab.="<tr class=rowcontent>
			<td>".$no."</td>
			<td>".$barj->kodebarang."</td>
			<td>".$namabarangk."</td>
			<td>".$barj->satuan."</td>
			<td align=right>".number_format($barj->jumlah,2,'.',',')."</td>
			<td>&nbsp <img src=images/application/application_edit.png class=resicon  title='edit' onclick=\"editMutasi('".$barj->kodebarang."','".$namabarangk."','".$barj->satuan."','".$barj->jumlah."');\">
			&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delMutasi('".$param['notransaksi']."','".$barj->kodebarang."');\">
			</td>
        </tr>";
	}
	
	// Tampilkan barang dengan saldo yang tidak mencukupi
	if(count($errBrg2)>0){
		$tab.="<tr><td colspan=6>Material yang berwarna merah, tidak memiliki saldo yang cukup</td></tr>";
		foreach($errBrg2 as $lstBrg){
			$no+=1;
			$strk="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$lstBrg."'";
			$resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
			$resk->setFetchMode(PDO::FETCH_OBJ);
			while($bark=$resk->fetch()){
				$namabarangk=$bark->namabarang;
				$satuank = $bark->satuan;
			}
			$tab.="<tr bgcolor=red>
				<td>".$no."</td>
				<td>".$lstBrg."</td>
				<td>".$namabarangk."</td>
				<td>".$satuank."</td>
				<td align=right>".number_format($jmlhBrg2[$lstBrg],2,'.',',')."</td>
				<td>Saldo = ".$notApp[$lstBrg]."</td>
				</tr>";
		}
	}
	
	// Tampilkan barang yang sudah pernah dimutasi
	if(count($errBrg)>0){
		$tab.="<tr><td colspan=6>Material yang berwarna oranye, sudah pernah di mutasikan</td></tr>";
		foreach($errBrg as $lstBrg) {
			$no+=1;
			$strk="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$lstBrg."'";
			$resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
			$resk->setFetchMode(PDO::FETCH_OBJ);
			while($bark=$resk->fetch()){
				$namabarangk=$bark->namabarang;
				$satuank = $bark->satuan;
			}
			echo"<tr bgcolor=orange>
			<td>".$no."</td>
			<td>".$lstBrg."</td>
			<td>".$namabarangk."</td>
			<td>".$satuank."</td>
			<td align=right>".number_format($jmlhBrg[$lstBrg],2,'.',',')."</td>
			<td>&nbsp</td>
			</tr>";
		}
	}
}
echo $tab."#####".$param['notransaksi'];