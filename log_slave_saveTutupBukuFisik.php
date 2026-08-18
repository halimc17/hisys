<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
//========================
$gudang = $_POST['gudang'];
$user = $_SESSION['standard']['userid'];
$period = $_POST['periode'];
$awal = $_POST['tanggalmulai'];
$akhir = $_POST['tanggalsampai'];

$unit=substr($gudang, 0, 4);


$str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$unit."'";
$res=fetchdata($str);
foreach($res as $bar){
	$tipeorg=$bar['tipe'];
}

//==============
$dtAdd = explode("-", $period);
$bulan = $dtAdd[1];
$x = str_replace("-", "", $period);
$x = str_replace("/", "", $x);
$x = mktime(0, 0, 0, intval(substr($x, 4, 2)) + 1, 15, substr($x, 0, 4));
$prefper = $period;// periode ini pakai prefer
$period = date('Y-m', $x); //periode jadi periode depan 


try {
	$owlPDO->beginTransaction();

#periksa apakah sudah pernah tutup buku pada periode tersebut:
$str = "select distinct(periode)  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $period . "' and kodegudang='" . $gudang . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$numrows=owlBaris($res);
if ($numrows > 0) {
    throw new PDOException($gudang . ' sudah tutup buku pada periode tersebut (' . $prefper . '), mohon hubungi IT');
}


$nobkm = 0;
##cek disini coi untuk transaksi BKM
$iBkm = "select distinct notransaksi from " . $dbname . ".kebun_pakai_material_vw_2 where jurnal=0 and tanggal like '%" . substr($awal, 0, 7) . "%' and"
        . " kodegudang='" . $gudang . "' ";
$nBkm=$owlPDO->query($iBkm) or die(print " Gagal: ".PDOException::getMessage());
$nBkm->setFetchMode(PDO::FETCH_ASSOC);
$adabkm='';
while ($dBkm = $nBkm->fetch()) {
    $nobkm++;
    $adabkm.=$nobkm.". ".$dBkm['notransaksi'] . "<br>";
}


if ($nobkm > 0) {
    throw new PDOException("Ada transaksi bkm yang memakai material belum terposting <br>" . $adabkm . " ");
}

#ambil PT:
$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . substr($gudang, 0, 4) . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$pt = '';
while ($bar = $res->fetch()) {
    $pt = $bar->induk;
}
if ($pt == '') {
    throw new PDOException('Gudang belum memiliki PT');
}

//cel apakah sudah posting semua pada periode tersebut;
$str = "select * from " . $dbname . ".log_transaksi_vw
      where left(kodegudang,4)='" . substr($gudang, 0, 4) . "' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "'
      and (post=0 or statussaldo=0) and hasilpersetujuan1=1 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$jlhNotPost = 0;
while ($bar = $res->fetch()) {
    $jlhNotPost = $bar->tgl;
}

if ($jlhNotPost > 0) {
    throw new PDOException("Masih ada ".$jlhNotPost. " transaksi gudang yang belum " . $_SESSION['lang']['belumposting'] . "");
}
$numrows=owlBaris($res);
$err="";
if($numrows>0){
    $err="Masih ada ".$numrows. " transaksi gudang yang belum di Posting :\n";
    $no=0;
    while($bar=$res->fetch()){
       $no+=1;
        $err.=$no.". No ".$bar->notransaksi." tgl => ".tanggalnormal($bar->tanggal)."\n"; 
    }
    throw new PDOException($err);
}

//cek apakah ada transaksi yang belum disetujui periode tersebut;
$str = "select * from " . $dbname . ".log_transaksi_vw
      where left(kodegudang,4)='" . substr($gudang, 0, 4) . "' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "'
      and (post=0 or statussaldo=0) and hasilpersetujuan1=0 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$jlhNotPost = 0;
while ($bar = $res->fetch()) {
    $jlhNotPost = $bar->tgl;
}

if ($jlhNotPost > 0) {
    throw new PDOException("Masih ada ".$jlhNotPost. " transaksi gudang yang belum " . $_SESSION['lang']['belumposting'] . "");
}

$numrows=owlBaris($res);
$err="";
if($numrows>0){
    $err="Masih ada ".$numrows. " transaksi gudang yang masih dalam proses persetujuan :\n";
    $no=0;
    while($bar=$res->fetch()){
       $no+=1;
        $err.=$no.". No ".$bar->notransaksi." tgl => ".tanggalnormal($bar->tanggal)."\n"; 
    }
    throw new PDOException($err);
}
	
#jika tipe lokasitugas kebun lakukan pengecekan transaksi bkm yang belum di posting
// $tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
// $tipeorg=$tipeorg[substr($gudang, 0, 4)];


if($tipeorg=='KEBUN'){
	$str = "select * from " . $dbname . ".kebun_pakai_material_vw where jurnal=0 and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "' and left(kodegudang,4) = '" . substr($gudang, 0, 4) . "' ";
	$res=fetchData($str);
	$err="";
	if(count($res)>0){
		$no=0;
		$err="Masih ada ".count($res). " transaksi bkm pakai material yang belum di Posting :\n";
		foreach($res as $row=>$bar){
			$no+=1;
			$err.=$no.". No ".$bar['notransaksi']." tgl => ".tanggalnormal($bar['tanggal'])."\n"; 
		}
		throw new PDOException($err);
	}

	// $str = "select * from " . $dbname . ".kebun_aktifitas 
	// 	  where kodeorg='" . substr($gudang, 0, 4) . "' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "'
	// 	  and jurnal=0 and tipetransaksi!='PNN'";
	// $res=fetchData($str);
	// $err="";
	// if(count($res)>0){
	// 	$no=0;
	// 	$err="Masih ada ".count($res). " transaksi bkm yang belum di Posting :\n";
	// 	foreach($res as $row=>$bar){
	// 		$no+=1;
	// 		$err.=$no.". No ".$bar['notransaksi']." tgl => ".tanggalnormal($bar['tanggal'])."\n"; 
	// 	}
	// 	throw new PDOException($err);
	// }
}


/*
 #mutasi antar unit
  $sCekMutasi="select * from ".$dbname.".log_transaksi_vw where left(kodegudang,4)='".substr($gudang, 0, 4)."' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "' and tipetransaksi=7 and left(kodegudang,4)=left(gudangx,4)";
  $rCekMutasi=fetchData($sCekMutasi);
  
  #terima mutasi
  $sCekTrmMutasi="select distinct notransaksireferensi,kodebarang from ".$dbname.".log_transaksi_vw where left(kodegudang,4)='".substr($gudang, 0, 4)."' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "' and tipetransaksi=3 and left(kodegudang,4)=left(gudangx,4)";
  $rCekTrmMutasi=fetchData($sCekTrmMutasi);
  if(count($rCekMutasi)!=count($rCekTrmMutasi)){#bandingkan antara mutasi dengan terima dalam satu unit, harus selesai dalam periode yang sama
    foreach ($rCekMutasi as $key => $val) {
        $lstGudangx[$val['gudangx']]=$val['gudangx'];
    }
    echo "Jalankan Penerimaan Mutasi dalam periode gudang yang sama, Pada Gudang Di Bawah\n";
    foreach ($lstGudangx as $key) {
      $nod+=1;
      echo $nod." : ".$key."\n";
    }
    exit('Warning');
  }
*/ 

#================ cek mutasi
	$str="select sum(jumlah) as jumlah,notransaksi from ".$dbname.".log_transaksi_vw 
	where left(kodegudang,4)='".substr($gudang, 0, 4)."' and tanggal>='" . $awal . "' 
	and tanggal<='" . $akhir . "' and tipetransaksi=7 and left(kodegudang,4)=left(gudangx,4) group by notransaksi";
	$res=fetchData($str);
	foreach($res as $bar){
		$arrnotran[$bar['notransaksi']]=$bar['notransaksi'];
		$jumlahkirim[$bar['notransaksi']]=$bar['jumlah'];
	}

	$str="select sum(jumlah) as jumlah,notransaksireferensi from ".$dbname.".log_transaksi_vw 
	where left(kodegudang,4)='".substr($gudang, 0, 4)."' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "' 
	and tipetransaksi=3 and left(kodegudang,4)=left(gudangx,4) group by notransaksireferensi";
	$res=fetchData($str);
	foreach($res as $bar){
		$arrnotran[$bar['notransaksireferensi']]=$bar['notransaksireferensi'];
		$jumlahterima[$bar['notransaksireferensi']]=$bar['jumlah'];
	}

	$nosalah=0;
	foreach($arrnotran as $notran){
		if($jumlahkirim[$notran] != $jumlahterima[$notran]){
			$nosalah++;
			echo $notran." : Kirim : ".$jumlahkirim[$notran]." ; Terima : ".$jumlahterima[$notran]."\n";
		}
	}

	if(count($jumlahkirim)!=count($jumlahterima)){
		$nosalah=1;//kondisi belum diterimakan mutasinya, kena validasi belum diterimakan mutasinya.
	}

	if($nosalah>0){
		throw new PDOException("Ada transaksi yang belum sesuai saat mutasi barang");
	}
	
	
	
	
	#==============================================================================================================================================
	#= ubah dulu kalau ada saldo <0 langsung ubah menjad 0 termasuk nilai saldoakhirqty
	$str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $prefper. "' and kodegudang='" .$gudang . "' and saldoakhirqty<0";
	// exit("Error:$str");
	$res=fetchdata($str);
	foreach($res as $bar){
		#= update jika ada
		$strup = "update  ".$dbname.".`log_5saldobulanan` set  saldoakhirqty=0,nilaisaldoakhir=0 where kodebarang='".$bar['kodebarang']."' and periode='" . $bar['periode']. "' and kodegudang='" . $bar['kodegudang']. "'";			
		$owlPDO->exec($strup);
	}
	
	#=cek gudang divisi harus 0
	$noerrortransaksi=$nod=0;
	$texterrorsaldodivisi='';
	// fix saldo nolkoma 311010011 KSPE57 0.0000000000036379788070917
	$str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $prefper. "' and   round(saldoakhirqty,5)>0 and kodegudang in (select kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANGTEMP' and induk='" .substr($gudang, 0, 4) . "')";
	$res=fetchdata($str);
	foreach($res as $bar){
		$noerrortransaksi++;
		$texterrorsaldodivisi.=" ".$noerrortransaksi." : ".$bar['kodebarang']." di gudang ".$bar['kodegudang']." sejumlah ".hidezerodecimal($bar['saldoakhirqty'],10)."<br>";
	}
	
	if($noerrortransaksi>0){
		$nod++;
		$textwarn.= "<br />Masih Ada saldo barang digudang divisi<br />";
		$textwarn.=$texterrorsaldodivisi;
		
		throw new PDOException($textwarn);
	}
	#==============================================================================================================================================
	
	#==============================================================================================================================================

	#= cek belum ada penerimaan mutasi yang belum dibuat
	$noerrormutasi=0;
	$str="select * from ".$dbname.".log_transaksi_vw where tipetransaksi=7 and gudangx like '".$gudang."%' and tanggal <='".$akhir."' and (notransaksireferensi='' or notransaksireferensi is null)";
	// echo $str;exit("Error:A");
	$res=fetchdata($str);
	foreach($res as $bar){
		$noerrormutasi++;
		if($bar['statussaldo']==1){
			$texterrormutasi.=" ".$noerrormutasi." : ".$bar['notransaksi']." (".tanggalnormal($bar['tanggal']).") Belum dibuatkan penerimaan mutasi \n ";
		}else{
			$texterrormutasi.=" ".$noerrormutasi." : ".$bar['notransaksi']." (".tanggalnormal($bar['tanggal']).") Transaksi Pengiriman dari ".substr($bar['kodegudang'],0,4)." belum diposting \n ";
		}
	}

	if($noerrormutasi>0){
		echo "<br />Ada Transaksi Mutasi belum diterima kan / Ada Transaksi pengiriman mutasi yang akan dikirim ke gudang ".$gudang." yang belum diposting pengirimannya , list transaksi di bawah ini<br />";
		throw new PDOException($texterrormutasi);
	}
  
  #==============================================================================================================================================
  
  
	
	
#==========================================
  
if ($_SESSION['empl']['tipelokasitugas'] == 'KEBUN') {
    #pengecekan apakah user sudah melakukan intergrity cek atau belum
    // $sCek = "select count(kodebarang) as jmlBrg from " . $dbname . ".kebun_pakai_material_vw_2 where 
    $sCek = "select count(kodebarang) as jmlBrg from " . $dbname . ".kebun_pakai_material_detail_vw where 
         tanggal between '" . $awal . "' and '" . $akhir . "' and kodegudang like '" . substr($gudang, 0, 4) . "%'";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    $rCek = $qCek->fetch();

    $sCek2 = "select count(kodebarang) as jmlBrg from " . $dbname . ".log_transaksi_vw where 
         tanggal between '" . $awal . "' and '" . $akhir . "' and kodegudang like '" . substr($gudang, 0, 4) . "%'
         and notransaksireferensi!='' and tipetransaksi=5";
	$qCek2=$owlPDO->query($sCek2) or die(print " Gagal: ".PDOException::getMessage());
	$qCek2->setFetchMode(PDO::FETCH_ASSOC);
    $rCek2 = $qCek2->fetch();
    if ($rCek['jmlBrg'] != $rCek2['jmlBrg']) {
        throw new PDOException("Silakan jalankan Proses pada menu Pengadaan>Proses>Intergrity Check BKM");
    }
	
}

//=============================
/* * ************************************************************
 * [START] Rekalkulasi stock fisik dan harga ******************
 * ************************************************************ */
#ambil saldo awal
$str = "select a.kodebarang,a.saldoawalqty,a.saldoakhirqty,a.hargarata,a.nilaisaldoawal,b.namabarang,b.satuan,a.qtymasukxharga,a.qtykeluarxharga from " . $dbname . ".log_5saldobulanan a 
              left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang where a.kodegudang='" . $gudang . "' and a.periode='" . $prefper . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $sAkun="select noakun from ".$dbname.".log_5klbarang where kode='".substr($bar->kodebarang,0,3)."'";
    $rAkun=fetchData($sAkun);
    // fix saldo nolkoma 311010011 KSPE57 0.0000000000036379788070917
    $Dt['saldoawalqty'][$bar->kodebarang] = round($bar->saldoawalqty,5);
    $Dt['nilaisaldoawal'][$bar->kodebarang] = $bar->nilaisaldoawal;
    $Dt['saldoakhirqty'][$bar->kodebarang] = $bar->saldoakhirqty;
    $Dt['hargarata'][$bar->kodebarang] = $bar->hargarata;
    $Dt['namabarang'][$bar->kodebarang] = $bar->namabarang;
    $Dt['satuan'][$bar->kodebarang] = $bar->satuan;
}
#ambil data masuk
$str = "select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargasatuan) as rpmasuk from " . $dbname . ".log_transaksi_vw where kodegudang='" . $gudang . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
              and tipetransaksi<5 and statussaldo=1 group by kodebarang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    // sum(0.5;0.2;0.1)=0.7999999999999999? fix pake round,5
    $masuk[$bar->kodebarang] = round($bar->jumlah,5);
    $rpmasuk[$bar->kodebarang]+=$bar->rpmasuk;
}

/*#ambil rupiah per barang per gudang menjadi tambahan rpmasuk    
$sJrn = "select kodebarang,jumlah from " . $dbname . ".keu_jurnaldt where  nojurnal like '%EXP01%' and tanggal between '" . $awal . "' and '" . $akhir . "' and right(noreferensi,6)='" . $gudang . "' and kodebarang!=''";
$qJrn=$owlPDO->query($sJrn) or die(print " Gagal: ".PDOException::getMessage());
$qJrn->setFetchMode(PDO::FETCH_ASSOC);
while ($rJrn = $qJrn->fetch()) {
    $rpmasuk[$rJrn['kodebarang']]+=$rJrn['jumlah'];
}*/

#ambil data keluar
$str = "select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargarata) as rpkeluar from " . $dbname . ".log_transaksi_vw where kodegudang='" . $gudang . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
              and tipetransaksi>4 and statussaldo=1 group by kodebarang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    // sum(0.5;0.2;0.1)=0.7999999999999999? fix pake round,5
    $keluar[$bar->kodebarang] = round($bar->jumlah,5);
    @$rpkeluar[$bar->kodebarang]+=$bar->rpkeluar;
}
#hilangkan blank
$fixdata = Array();
if (!empty($Dt['hargarata'])) {
    foreach ($Dt['hargarata'] as $key => $val) {
        if (!isset($masuk[$key])) {
            $masuk[$key] = 0;
        }
        if (!isset($keluar[$key])) {
            $keluar[$key] = 0;
        }

        $seharusnya = $Dt['saldoawalqty'][$key] + round($masuk[$key],5) - round($keluar[$key],5);
        //if($seharusnya!=$Dt['saldoakhirqty'][$key]){
        if ((@$seharusnya != @$Dt['saldoakhirqty'][$key]) || (@$rpmasuk[$key] != @$Dt['qtymasukxharga'][$key]) || (@$rpkeluar[$key] != @$Dt['qtykeluarxharga'][$key])) {
            $fixdata['saldoawal'][$key] = $Dt['saldoawalqty'][$key];
            $fixdata['saldoakhir'][$key] = $Dt['saldoakhirqty'][$key];
            $fixdata['masuk'][$key] = round($masuk[$key],5);
            $fixdata['keluar'][$key] = round($keluar[$key],5);
            $fixdata['seharusnya'][$key] = round($seharusnya,5);

            $fixdatarp['masuk'][$key] = floatval(@$rpmasuk[$key]) > 0 ? $rpmasuk[$key] : 0;
            $fixdatarp['keluar'][$key] = floatval(@$rpkeluar[$key]) > 0 ? $rpkeluar[$key] : 0;
            $fixdatarp['saldoakhir'][$key] = round($Dt['nilaisaldoawal'][$key] + $fixdatarp['masuk'][$key] - $fixdatarp['keluar'][$key], 4);
            $fixdatarp['hargarata'][$key] = floatval($fixdata['seharusnya'][$key]) > 0 ? $fixdatarp['saldoakhir'][$key] / $fixdata['seharusnya'][$key] : 0;
        }
    }

    if (count($fixdata) > 0) {
        foreach ($fixdata['saldoawal'] as $key => $val) {
            #update log_5saldobulanan
            $str = "update " . $dbname . ".log_5saldobulanan set saldoakhirqty=" . $fixdata['seharusnya'][$key] . ",qtymasuk=" . $fixdata['masuk'][$key] . ",qtykeluar=" . $fixdata['keluar'][$key] . ",
                       hargarata=" . $fixdatarp['hargarata'][$key] . ", qtymasukxharga=" . $fixdatarp['masuk'][$key] . ",qtykeluarxharga=" . $fixdatarp['keluar'][$key] . ",
                       nilaisaldoakhir=" . $fixdatarp['saldoakhir'][$key] . " where kodebarang='" . $key . "' and kodegudang='" . $gudang . "'
                       and periode='" . $prefper . "'";
			$owlPDO->exec($str); 
			
            #update log_5masterbarangdt
            $str = "update " . $dbname . ".log_5masterbarangdt set saldoqty=" . $fixdata['seharusnya'][$key] . " where kodebarang='" . $key . "' and kodegudang='" . $gudang . "'";
			$owlPDO->exec($str); 
        }
    }
}
/* * ************************************************************
 * [END] Rekalkulasi stock fisik dan harga ********************
 * ************************************************************ */

/* * ************************************************************
 * [START] Cek Nilai Material VS Jurnal ***********************
 * ************************************************************ */
// Get Kelompok Barang yang ada Akun



#= pecah RO dan UNIT pakai tipe organisasi
if($tipeorg=='KANWIL'){
	$optKel = makeOption($dbname, 'log_5klbarang', "kode,noakun", "noakun!='' and noakun like '11504%'");
	$listKel = $listAkun = array();
	foreach ($optKel as $kode => $akun) {
		$listKel[] = $kode;
		$listAkun[$akun] = $akun;
	}

	// Get Nilai Material, log_5saldobulanan
	$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
	  FROM " . $dbname . ".log_5saldobulanan 
	  WHERE left(kodebarang,3) in ('" . implode("','", $listKel) . "') and kodegudang like '" . substr($gudang, 0, 4) . "%' and periode='" . $prefper . "' GROUP BY left(kodebarang,3)";
	//echo $qSaldoMat."<p>";
	$resSaldoMat = fetchData($qSaldoMat);
	$optSaldoMat = array();
	foreach ($resSaldoMat as $row) {
		if (!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
			$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
		} else {
			$optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
		}
	}
	$periodeKuangan = $dtAdd[0] . $dtAdd[1];
	// Get Nilai Jurnal, keu_saldobulanan
	$qSaldoJ = "SELECT awal" . $bulan . " as saldoawal,noakun
	  FROM " . $dbname . ".keu_saldobulanan
	  WHERE kodeorg='" . substr($gudang, 0, 4) . "' and periode='" . $periodeKuangan . "'
		and noakun in ('" . implode("','", $listAkun) . "')";
	//echo $qSaldoJ."<p>";
	$resSaldoJ = fetchData($qSaldoJ);
	$optSaldoJ = array();
	foreach ($resSaldoJ as $row) {
		$optSaldoJ[$row['noakun']] = $row['saldoawal'];
	}

	// Get Transaksi Jurnal
	$qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
	  FROM " . $dbname . ".keu_jurnaldt_vw
	  WHERE kodeorg='" . substr($gudang, 0, 4) . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
		and noakun in ('" . implode("','", $listAkun) . "')
	  GROUP BY noakun";
	//echo $qTrans."<p>";
	$resTrans = fetchData($qTrans);
	foreach ($resTrans as $row) {
		if (!isset($optSaldoJ[$row['noakun']]))
			$optSaldoJ[$row['noakun']] = 0;
		$optSaldoJ[$row['noakun']] += $row['saldotrans'];
	}
	
	
}else{
	
	$optKel = makeOption($dbname, 'log_5klbarang', "kode,noakun", "noakun!='' and noakun like '11501%'");
	$listKel = $listAkun = array();
	foreach ($optKel as $kode => $akun) {
		$listKel[] = $kode;
		$listAkun[$akun] = $akun;
	}

	// Get Nilai Material, log_5saldobulanan
	$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
	  FROM " . $dbname . ".log_5saldobulanan 
	  WHERE left(kodebarang,3) in ('" . implode("','", $listKel) . "') and kodegudang like '" . substr($gudang, 0, 4) . "%' and periode='" . $prefper . "' GROUP BY left(kodebarang,3)";
	//echo $qSaldoMat."<p>";
	$resSaldoMat = fetchData($qSaldoMat);
	$optSaldoMat = array();
	foreach ($resSaldoMat as $row) {
		if (!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
			$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
		} else {
			$optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
		}
	}
	$periodeKuangan = $dtAdd[0] . $dtAdd[1];
	// Get Nilai Jurnal, keu_saldobulanan
	$qSaldoJ = "SELECT awal" . $bulan . " as saldoawal,noakun
	  FROM " . $dbname . ".keu_saldobulanan
	  WHERE kodeorg='" . substr($gudang, 0, 4) . "' and periode='" . $periodeKuangan . "'
		and noakun in ('" . implode("','", $listAkun) . "')";
	//echo $qSaldoJ."<p>";
	$resSaldoJ = fetchData($qSaldoJ);
	$optSaldoJ = array();
	foreach ($resSaldoJ as $row) {
		$optSaldoJ[$row['noakun']] = $row['saldoawal'];
	}

	// Get Transaksi Jurnal
	$qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
	  FROM " . $dbname . ".keu_jurnaldt_vw
	  WHERE kodeorg='" . substr($gudang, 0, 4) . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
		and noakun in ('" . implode("','", $listAkun) . "')
	  GROUP BY noakun";
	//echo $qTrans."<p>";
	$resTrans = fetchData($qTrans);
	foreach ($resTrans as $row) {
		if (!isset($optSaldoJ[$row['noakun']]))
			$optSaldoJ[$row['noakun']] = 0;
		$optSaldoJ[$row['noakun']] += $row['saldotrans'];
	}
}



// Cek All Akun
$notBal = "";
foreach ($listAkun as $akun) {
    if (!isset($optSaldoMat[$akun]))
        $optSaldoMat[$akun] = 0;
    if (!isset($optSaldoJ[$akun]))
        $optSaldoJ[$akun] = 0;

    $selisih = abs(abs($optSaldoMat[$akun]) - abs($optSaldoJ[$akun]));
    if ($selisih > 300) {
        $notBal .= $akun . " = " . number_format($selisih) . "___" . abs($optSaldoMat[$akun]) . "____" . abs($optSaldoJ[$akun]) . "\n";
    }
}

// Alert Jika ada yang belum balance
if (!empty($notBal)) {
    echo $notBal;
    throw new PDOException('Silakan Hubungi IT Departement.');
    // exit('warning: Silakan jalankan Proses pada menu Keuangan>Proses>Proses Akhir Bulan, Pilih Intergrity Check Gudang');
    //exit("Warning: Ada jurnal material yang belum balance dengan saldo material\n".$notBal);
}
/* * ************************************************************
 * [END] Cek Nilai Material VS Jurnal *************************
 * ************************************************************ */

$str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where kodeorg='" . $pt . "' and kodegudang='" . $gudang . "' and periode='" . $prefper . "'  and saldoakhirqty<0";
$res=fetchdata($str);
foreach($res as $bar){
	#= update jika ada
	$strup = "update  ".$dbname.".`log_5saldobulanan` set  saldoakhirqty=0,nilaisaldoakhir=0 where kodebarang='".$bar['kodebarang']."' and periode='" . $bar['periode']. "' and kodegudang='" . $bar['kodegudang']. "' and kodeorg='".$bar['kodeorg']."'";			
	$owlPDO->exec($strup);
}


//ambil saldo akhir bulan lalu termasuk rupiah
$str = "select kodebarang,saldoakhirqty,nilaisaldoakhir,hargarata 
            from " . $dbname . ".log_5saldobulanan
            where kodeorg='" . $pt . "' and kodegudang='" . $gudang . "' and periode='" . $prefper . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    //insert new line
    $str1 = "INSERT INTO `" . $dbname . "`.`log_5saldobulanan`
        (`kodeorg`,
        `kodebarang`,
        `saldoakhirqty`,
        `hargarata`,
        `lastuser`,
        `periode`,
        `nilaisaldoakhir`,
        `kodegudang`,
        `qtymasuk`,
        `qtykeluar`,
        `qtymasukxharga`,
        `qtykeluarxharga`,
        `saldoawalqty`,
        `hargaratasaldoawal`,
        `nilaisaldoawal`)
        VALUES(
            '" . $pt . "',
            '" . $bar->kodebarang . "',
            " . $bar->saldoakhirqty . ",
            " . $bar->hargarata . ",
            " . $user . ",
            '" . $period . "',
            " . $bar->nilaisaldoakhir . ",
            '" . $gudang . "',
            0,
            0,
            0,
            0,
            " . $bar->saldoakhirqty . ",
            " . $bar->hargarata . ",
            " . $bar->nilaisaldoakhir . "
        )";
	
	$owlPDO->exec($str1); 
}

//next period is
$nextPeriod = $period;
$tg = mktime(0, 0, 0, substr($akhir, 5, 2), intval(substr($akhir, 8, 2) + 1), intval(substr($prefper, 0, 4)));
$nextAwal = date('Ymd', $tg);
$tg = mktime(0, 0, 0, intval(substr($akhir, 5, 2)) + 1, date('t', $tg), intval(substr($prefper, 0, 4)));
$nextAkhir = date('Ymd', $tg);
//update setup_periodeakuntansi
$str = "update " . $dbname . ".setup_periodeakuntansi set tutupbuku=1
	  where kodeorg='" . $gudang . "' and periode='" . $prefper . "'";

$owlPDO->exec($str);
	
$str = "INSERT INTO `" . $dbname . "`.`setup_periodeakuntansi`
	(`kodeorg`,
	`periode`,
	`tanggalmulai`,
	`tanggalsampai`,
	`tutupbuku`)
	VALUES
	('" . $gudang . "',
		'" . $nextPeriod . "',
		" . $nextAwal . ",
		" . $nextAkhir . ",
		0
		)";
$owlPDO->exec($str); 

$str = "delete from " . $dbname . ".keu_setup_watu_tutup where periode='" . $prefper . "' and kodeorg='" . $gudang . "'";
$owlPDO->exec($str); 

$str = "insert into " . $dbname . ".keu_setup_watu_tutup(kodeorg,periode,username) values(
'" . $gudang . "','" . $prefper . "','" . $_SESSION['standard']['username'] . "')";
$owlPDO->exec($str); 
	
$owlPDO->commit();
} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		
?>