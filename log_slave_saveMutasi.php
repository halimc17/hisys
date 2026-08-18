<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
//====================================
//default setting on database 
//1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,
//5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi 
$tipetransaksi=7;
//=============================================

if(isTransactionPeriod()){
try {
$owlPDO->beginTransaction();
	$nodok		  			=	isset($_POST['nodok'])? $_POST['nodok']: '';
	$nosj		  			=	isset($_POST['nosj'])? $_POST['nosj']: '';
	$tanggal	 		 	=	isset($_POST['tanggal'])? tanggalsystem($_POST['tanggal']): '';
	$kodebarang	  			=	isset($_POST['kodebarang'])? $_POST['kodebarang']: '';
	$kegudang	 			=	isset($_POST['kegudang'])? $_POST['kegudang']: '';
	$satuan		  =	isset($_POST['satuan'])? $_POST['satuan']: '';
	$nopo		  =	isset($_POST['nopo'])? $_POST['nopo']: '';
	$qty		  =	isset($_POST['qty'])? $_POST['qty']: 0;
	$gudang		  =	isset($_POST['gudang'])? $_POST['gudang']: '';
	$catatan	  =	isset($_POST['catatan'])? $_POST['catatan']: '';
	$pemilikbarang=	isset($_POST['pemilikbarang'])? $_POST['pemilikbarang']: '';
	$user		  =	$_SESSION['standard']['userid'];
	$periode      = substr(tanggalsystemn(checkPostGet('tanggal', '')),0,7);
	$post         = 0;
	
	$driver		  	= isset($_POST['driver'])? $_POST['driver']: '';
	$hpdriver		= isset($_POST['hpdriver'])? $_POST['hpdriver']: '';
	$nopol		  	= isset($_POST['nopol'])? $_POST['nopol']: '';
	$jeniskendaraan = isset($_POST['jeniskendaraan'])? $_POST['jeniskendaraan']: '';
	$expeditor		= isset($_POST['expeditor'])? $_POST['expeditor']: '';
	$nosuratjalan	= isset($_POST['nosuratjalan'])? $_POST['nosuratjalan']: '';
	
	
	#= khusus mutasi kirim tambahkan pengecekan periode akuntansi
	#= gudang = gudang pengirim
	#= kegudang = gudang penerima
	
	$tutupbukupengirim = '';
	$tutupbukupenerima = '';
	$str=" select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".$gudang."'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$tutupbukupengirim=$bar['tutupbuku'];
	}
	
	$str=" select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".$kegudang."'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$tutupbukupenerima=$bar['tutupbuku'];
	}
	
	$arrtutupbuku=array("0"=>"Open","1"=>"Close",""=>"Periode Belum masuk ".$periode."");
	
	if($tutupbukupengirim!=$tutupbukupenerima){
		exit("Warningsistem:Periode gudang tidak sama antara pengirim dan penerima \n pengirim : ".$gudang." periode ".$periode." ".$arrtutupbuku[$tutupbukupengirim]."\n penerima : ".$kegudang." periode ".$periode." ".$arrtutupbuku[$tutupbukupenerima]." ");
	}
	
	
	//1 cek apakah sudah terekan di header
	//status=0 belum ada apa2
	//status=1 ada header
	//status=2 ada detail dan header
	//status=3 sudah di posting
	//status=4 kode pt penerima barang tidak ada
	//status=5 delete item
	//status=6 display only
	//status=7 sudah ada yang diposting pada tanggal yang lebih besar dengan barang yang sama dan pt yang sama

//======================================================= pengganti line 36-42 by pak ginting mar 5, 2014
	//exit("WARNING-".$nodok);
	$status=0;
	$user1=$_SESSION['standard']['userid'];
	$str="select user from ".$dbname.".log_transaksiht where notransaksi='".$nodok."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	if(owlBaris($res)==1){
		while($bar=$res->fetch()){
			$user1=$bar->user;                  
		}
		if($_SESSION['standard']['userid']==$user1){
			$status=1;
		} else{
			throw new PDOException('This transaction belongs to other user, please reload and start over');
		}
	}
//=======================================================


    if(isset($_POST['delete'])) {
        $status=5;
    }
	
    $str="select * from ".$dbname.".log_transaksiht where notransaksi='".$nodok."'
        and post=1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=owlBaris($res);
    if($numrows>0) {
        $status=3;
    }	
    if($pemilikbarang=='') {
        $status=4;
    }
    if(isset($_POST['displayonly'])) {
        $status=6;
    }

//==================ambil jumlah lalu====================
    $jumlahlalu=0;
    $str="select a.jumlah as jumlah,b.nopo as nopo,a.notransaksi as notransaksi,a.waktutransaksi 
        from ".$dbname.".log_transaksidt a,
             ".$dbname.".log_transaksiht b
        where a.notransaksi=b.notransaksi 
            and a.kodebarang='".$kodebarang."'
			and a.notransaksi<='".$nodok."'
			and tipetransaksi>4
			and b.kodegudang='".$gudang."'
			order by notransaksi desc, waktutransaksi desc limit 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
		$jumlahlalu=$bar->jumlah;
	}
	
    //ambil pemasukan barang yang belum di posting
	$qtynotpostedin=0;
	$str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
        b on a.notransaksi=b.notransaksi where kodept='".$pemilikbarang."' and b.kodebarang='".$kodebarang."' 
		and a.tipetransaksi<5
		and a.kodegudang='".$gudang."'
		and a.post=1 and a.notransaksi!='".$nodok."'			   
		group by kodebarang";
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_OBJ);
	while($bar2=$res2->fetch()){
		$qtynotpostedin=$bar2->jumlah;
	}
	if($qtynotpostedin=='') $qtynotpostedin=0;
	
	//ambil trx yg blm di posting
    //ambil pengeluaran barnag yang belum di posting
	$qtynotposted=0;
	$str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
        b on a.notransaksi=b.notransaksi where kodept='".$pemilikbarang."' and b.kodebarang='".$kodebarang."' 
		and a.tipetransaksi>4
		and a.kodegudang='".$gudang."'
		and a.post=0 and a.notransaksi!='".$nodok."'		   
		group by kodebarang";
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_OBJ);
	while($bar2=$res2->fetch()){
		$qtynotposted=$bar2->jumlah;
	}
	//ambil saldo qty===============================================
	$saldoqty=0;
	$strs="select saldoakhirqty as saldoqty from ".$dbname.".log_5saldobulanan where kodebarang='".$kodebarang."'
        and kodegudang='".$gudang."'
		and periode='".$periode."'"; 
	$ress=$owlPDO->query($strs) or die(print " Gagal: ".PDOException::getMessage());
	$ress->setFetchMode(PDO::FETCH_OBJ);
	while($bars=$ress->fetch()){
        $saldoqty=$bars->saldoqty;
	}
	
	//==================periksa kecukupan saldo
	// echo $qty."__".$qtynotposted."XXXXX".$saldoqty."__".$qtynotpostedin;
	if($status==0 or $status==1) {
		// if(($qty+$qtynotposted)>($saldoqty+$qtynotpostedin)) {
		if(($qty+$qtynotposted)>($saldoqty)) {
			// throw new PDOException($_SESSION['lang']['saldo']." ".$_SESSION['lang']['tidakcukup']." ".$saldoqty."+".$qtynotpostedin."-".$qtynotposted."=".(($saldoqty+$qtynotpostedin)-$qtynotposted).", Qty yang tertera ".$kodebarang.": ".$qty);
			throw new PDOException($_SESSION['lang']['saldo']." ".$_SESSION['lang']['tidakcukup']." ".$saldoqty."-".$qtynotposted."=".(($saldoqty)-$qtynotposted).", Qty yang tertera ".$kodebarang.": ".$qty);
			$status=6;//status ngeles
        }
	} else if($status==2) {
		//status 2 tidak akan perbah dieksekusi
        //ambil jumlah lama dan bandingkan dengan qty kemudian bandingkan dengan saldo
        $jlhlama=0;
        $strt="select jumlah from ".$dbname.".log_transaksidt where notransaksi='".$nodok."'
               and kodebarang='".$kodebarang."' and kodeblok='".$blok."'";
		$rest=$owlPDO->query($strt) or die(print " Gagal: ".PDOException::getMessage());
		$rest->setFetchMode(PDO::FETCH_OBJ);
        while($bart=$rest->fetch()){
			$jlhlama=$bart->jumlah;
        }	
        // if(($saldoqty+$jlhlama+$qtynotpostedin)<($qty+$qtynotposted)){
		if(($saldoqty+$jlhlama)<($qty+$qtynotposted)){
			throw new PDOException($_SESSION['lang']['saldo']." ".$_SESSION['lang']['tidakcukup']);
			$status=6;//status ngeles
        }   
	}


//=============================start input/update
	if($status==0 or (isset($_POST['isNewTrans']) and $_POST['isNewTrans']==1)) {
		//get kode pt penerima barang
		$sKdPt="select distinct induk from ".$dbname.".organisasi where kodeorganisasi='".substr($kegudang,0,4)."'";
		$qKdPt=$owlPDO->query($sKdPt) or die(print " Gagal: ".PDOException::getMessage());
		$qKdPt->setFetchMode(PDO::FETCH_ASSOC);
		$rKdpt=$qKdPt->fetch();
		if($rKdpt['induk']==''){
			throw new PDOException("Kode PT Penerima Kosong");
		}
		
		//menghindari input backdate sedangkan tanggal setelahnya ada transaksi,(permintaan bpk.Ari) 
		$sBrg="SELECT * FROM ".$dbname.".`log_transaksi_vw` WHERE `kodebarang` = '".$kodebarang."' AND `tanggal` > '".$tanggal."' AND `statussaldo` = '1' AND `kodegudang` = '".$gudang."' ORDER BY tanggal";$adabrgbackdate ='';
		foreach (fetchData($sBrg) as $vi) {
			@$nx++;
			$adabrgbackdate.=$nx.". ".$vi['notransaksi']." Tanggal : ".tanggalnormal($vi['tanggal'])."\n";
		}
		if(count(fetchData($sBrg)) > 0){
			throw new PDOException("Sudah terdapat transaksi atas barang ".$kodebarang." - ".getNamaBrg($kodebarang)."\nSetelah Tanggal ".tanggalnormal($tanggal)." \n".$adabrgbackdate."\nSilahkan inputkan setelah atau sama dengan tanggal terakhir diinputkan.");
		}
		
		$str_cp="select * from ".$dbname.".log_transaksiht where notransaksi='".$nodok."'";
		$res_cp=$owlPDO->query($str_cp) or die(print " Gagal: ".PDOException::getMessage());
		$res_cp->setFetchMode(PDO::FETCH_OBJ);
		if(owlBaris($res_cp)<=0){
			$str="insert into ".$dbname.".log_transaksiht (
					`tipetransaksi`,`notransaksi`,
					`tanggal`,`kodept`,`untukpt`,
					`gudangx`,`keterangan`,
					`kodegudang`,`user`,`nosj`,
					`post`,`driver`,`hpdriver`,`nopol`,`jeniskendaraan`,`expeditor`)
				values(".$tipetransaksi.",'".$nodok."',
					   ".$tanggal.",'".$pemilikbarang."','".$rKdpt['induk']."',
						'".$kegudang."','".$catatan."',
						'".$gudang."',".$user.",'".$nosuratjalan."',
						".$post.",'".$driver."','".$hpdriver."','".$nopol."','".$jeniskendaraan."','".$expeditor."'
				)";	
			$owlPDO->exec($str);

		}

		$str="insert into ".$dbname.".log_transaksidt (
		  `notransaksi`,`kodebarang`,
		  `satuan`,`jumlah`,`jumlahlalu`,`nopo`,
		  `updateby`)
		  values('".$nodok."','".$kodebarang."',
		  '".$satuan."',".$qty.",".$jumlahlalu.",'".$nopo."','".$user."')";
		$owlPDO->exec($str); 
    //============================
	//status=1
    } elseif($status==1) {
		$str="select * from ".$dbname.".log_transaksidt where notransaksi='".$nodok."' and kodebarang='".$kodebarang."'";
		$res=fetchdata($str);
		$jumlahtemp = $res[0]['jumlah'];
		if($jumlahtemp==''){
			$jumlahtemp = 0;
		}
		
		if($jumlahtemp==0){
			$str="insert into ".$dbname.".log_transaksidt (
			`notransaksi`,`kodebarang`,
			  `satuan`,`jumlah`,`jumlahlalu`,`nopo`,
			  `updateby`)
			  values('".$nodok."','".$kodebarang."',
			  '".$satuan."',".$qty.",".$jumlahlalu.",'".$nopo."',
			  '".$user."')";
			$owlPDO->exec($str); 
		}else{
			$totqty = $qty+$jumlahtemp;
			// echo $totqty."<br>";
			$str="update ".$dbname.".log_transaksidt set jumlah='".$totqty."', updateby=".$user.", jumlahlalu='".$jumlahlalu."', nopo='".$nopo."' where `notransaksi`='".$nodok."' and `kodebarang`='".$kodebarang."'";
			$owlPDO->exec($str); 
		}
    }
	//============================update detail
	//status=2
    if($status==2) {
        //status ini tidak akan tereksekusi
		$str="update ".$dbname.".log_transaksidt set
			  `jumlah`=".$qty.",
				  `updateby`=".$user.",
				  `nopo`='".$nopo."'
				  where `notransaksi`='".$nodok."'
				  and `kodebarang`='".$kodebarang."'";
		$affected_rows = $owlPDO->exec($str);//insert detail
		if($affected_rows<1){	
			throw new PDOException("update detail on status 2");
		}
    }
	//============================return message
	//status=3
    if($status==3) {	
        throw new PDOException("Data has been posted");
    }
	//============================return message
	//status=4
    if($status==4) {	
		throw new PDOException("Company code of the Recipient is not defined");
    }
	//===========delete ==========================
	//status=5
    if($status==5) { //delete item not header		   	 
		$str="delete from ".$dbname.".log_transaksidt where kodebarang='".$kodebarang."'
			  and notransaksi='".$nodok."'";
		$affected_rows=$owlPDO->exec($str);
        if($affected_rows>0){
			$strSJ = "update ".$dbname.".log_suratjalandt set notransaksireferensi=''
				where notransaksireferensi='".$nodok."' and (kodebarang='".$kodebarang."' or substr(kodebarang,1,2)='PL')";
			$owlPDO->exec($strSJ); 
        }
    }
	
	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	//ambil data untuk ditampilkan
	$strj="select a.* from ".$dbname.".log_transaksidt a 
		   where a.notransaksi='".$nodok."' order by waktutransaksi asc ";	
	$resj=$owlPDO->query($strj) or die(print " Gagal: ".PDOException::getMessage());
	$resj->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
 	while($barj=$resj->fetch()) {
        $no+=1;
        //ambil namabarang
        $namabarangk='';
        $strk="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$barj->kodebarang."'";
		$resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
		$resk->setFetchMode(PDO::FETCH_OBJ);
        while($bark=$resk->fetch()){
            $namabarangk=$bark->namabarang;
        }

        echo"<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$barj->kodebarang."</td>
				<td>".$namabarangk."</td>
				<td align=center>".$barj->satuan."</td>
				<td align=right>".number_format($barj->jumlah,2,'.',',')."</td>
				<td align=center width=25px><img src=images/application/application_edit.png class=resicon  title='edit' onclick=\"editMutasi('".$barj->kodebarang."','".$namabarangk."','".$barj->satuan."','".$barj->jumlah."');\"></td>
				<td align=center width=25px><img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delMutasi('".$nodok."','".$barj->kodebarang."');\">
				</td>
			</tr>";
	}
	if(isset($_POST['isNewTrans']) and $_POST['isNewTrans']==1) {
		echo '#####'.$nodok;
	}
} else {
    echo " Error: Transaction Period missing";
}