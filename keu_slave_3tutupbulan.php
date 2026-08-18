<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$tahun = $tmpPeriod[0];
$bulan = $tmpPeriod[1];
$proses = $_GET['proses'];
$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');

$deb=false;

#== cek akun bank tidak boleh ada <0 ===============================================================

$daftarbank=makeOption($dbname,'keu_5akunbank','noakun,namabank');
$namabank=makeOption($dbname,'keu_5daftarbank','kodebank,namabank');

$periodeberikut=periodeberikut($param['periode']);
$tmpPeriodberikut = explode('-',$periodeberikut);
$awalberikut="awal".$tmpPeriodberikut[1];
$str="select ".$awalberikut." as awal,norek FROM ".$dbname.".keu_saldobank where  periode ='".str_replace("-", "", $periodeberikut)."' 
          and kodeorg='".$param['kodeorg']."' and ".$awalberikut."<0 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$dataminus='';
if($numrows>0){
	while($bar=$res->fetch()){
		$dataminus.="Bank/Norek : ".$namabank[$daftarbank[$bar->norek]]." ".$bar->norek."; Jumlah : ".number_format($bar->awal,2)."\n";
	}
}

if($dataminus!=''){
    exit(" Warning:  Masih ada saldo rekening dibawah 0\n".$dataminus."");//
}
#=======================================================================================================

#cek apakah unit dibawah RO tutup buku
if($tipeorg[$param['kodeorg']]=='KANWIL'){
    $sPt="select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
    $rPt=fetchData($sPt);
    $sDt="select kodeorganisasi from ".$dbname.".organisasi where induk='".$rPt[0]['induk']."' and tipe not in ('KANWIL','HOLDING') and namaorganisasi not like '%PLASMA%'";
    $rDt=fetchData($sDt);
    $unitAda=count($rDt);
    $scekAkutansi="select * from ".$dbname.".setup_periodeakuntansi 
                   where periode='".$param['periode']."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$rPt[0]['induk']."' and tipe not in ('KANWIL','HOLDING') and namaorganisasi not like '%PLASMA%') and tutupbuku=1";
    $rCekAkutansi=fetchData($scekAkutansi);
    $unitAkutansi=count($rCekAkutansi);
    if($unitAda!=$unitAkutansi){
        $scekAkutansi="select * from ".$dbname.".setup_periodeakuntansi 
                   where periode='".$param['periode']."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$rPt[0]['induk']."' and tipe not in ('KANWIL','HOLDING') and namaorganisasi not like '%PLASMA%') and tutupbuku=0";
        $rCekAkutansi=fetchData($scekAkutansi);
        $nod=1;
        foreach($rCekAkutansi as $brs=>$val){
            echo $nod.".".$val['kodeorg']."\n";
            $nod+=1; 
        }
        exit('warning: Masih Ada Unit Belum Tutup Buku');
    }
} 
/*
#= cek apakah akun intraco interco sudah balance =======================================================
$str="select sum(jumlah) from ".$dbname.".keu_jurnaldt_vw where 
		noakun in (select akunpiutang from ".$dbname.".keu_5caco) and periode='".$param['periode']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$bar=$res->fetch();
	$jumlahpiutang=number_format(abs($bar->jumlah));
	$jumlahpiutang=str_replace(",","",$jumlahpiutang);

$str="select sum(jumlah) from ".$dbname.".keu_jurnaldt_vw where 
		noakun in (select akunhutang from ".$dbname.".keu_5caco) and periode='".$param['periode']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$bar=$res->fetch();
	$jumlahhutang=number_format(abs($bar->jumlah));
	$jumlahhutang=str_replace(",","",$jumlahhutang);
	
$selisihhutangpiutang=$jumlahhutang-$jumlahpiutang;
// exit("Error:".$selisihhutangpiutang._.$jumlahhutang._.$jumlahpiutang);

if($selisihhutangpiutang>1){
	exit("Warning : Akun Intraco/Interco masih ada selisih, selisih : ".number_format($selisihhutangpiutang)." ");
}
#=======================================================================================================
*/

#================ cek alokasi gaji sudah dilakukan / belum
$where = "AND a.kodeorg = '".$param['kodeorg']."'";
$wh = "AND kodeorg = '".$param['kodeorg']."'";

$getkaryawan = "SELECT a.kodeorg, a.periodegaji, a.karyawanid, b.namakaryawan, b.nik
				FROM ".$dbname.".sdm_gajidetail_vw a
				JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
				WHERE a.periodegaji = '".$param['periode']."' and a.idkomponen!='42' ".$where."
				GROUP BY a.kodeorg, a.periodegaji, a.karyawanid order by b.namakaryawan";
				// echo $getkaryawan;
$reskaryawan = fetchdata($getkaryawan);
$num = 0; $wherekarid = ''; $arrkaryawan = array();
foreach ($reskaryawan as $key => $val) {
	$arrkarya[$val['karyawanid']]=$val['karyawanid'];
	$arrkaryawan[$val['namakaryawan']]['nik'] = $val['nik'];
	$arrkaryawan[$val['namakaryawan']]['lokasitugas'] = $val['kodeorg'];

	if($num == 0) {
		$wherekarid .= "'".$val['karyawanid']."'";
	} else {
		$wherekarid .= ",'".$val['karyawanid']."'";
	}
	$num++;
}

if($wherekarid!=''){	
	## GET JUMLAH GAJI PLUS
	$getjumlahplus = "SELECT sum(jumlah) as jmlh
					 FROM ".$dbname.".sdm_gajidetail_vw
					 WHERE plus = 1 AND periodegaji = '".$param['periode']."' AND karyawanid in (".$wherekarid.")";
	$resjumlahplus = fetchdata($getjumlahplus);
	$arrjmlhplus = 0;
	foreach ($resjumlahplus as $key => $val) {
		$arrjmlhplus += $val['jmlh'];
	}

	## GET JUMLAH GAJI MINUS
	$getjumlahminus = "SELECT sum(jumlah) as jmlh
					 FROM ".$dbname.".sdm_gajidetail_vw
					 WHERE plus = 0 AND periodegaji = '".$param['periode']."' AND karyawanid in (".$wherekarid.") ".$wh."";
	$resjumlahminus = fetchdata($getjumlahminus);
	$arrjmlhminus = 0;
	foreach ($resjumlahminus as $key => $val) {
		$arrjmlhminus += $val['jmlh'];
	}

	## GET TOTAL ALOKASI PLUS
	$getalokplus = "SELECT sum(kredit) as debet
					FROM ".$dbname.".keu_jurnaldt_vw 
					WHERE noakun in (SELECT noakunkredit FROM ".$dbname.".keu_5pengakuanpotongan) AND periode = '".$param['periode']."' AND nik !='' AND nik!='0000000000' ".$wh." 
					AND noreferensi LIKE 'ALK_POT%' and nojurnal not like '%/M/%'";
	$resalokplus = fetchdata($getalokplus);
	$arralokplus = 0;
	foreach ($resalokplus as $key => $val) {
		$arralokplus += $val['debet'];
	}

    $getalokplus = "SELECT sum(debet) as debet
                    FROM ".$dbname.".keu_jurnaldt_vw 
                    WHERE noakun in (SELECT noakunkredit FROM ".$dbname.".keu_5pengakuanpotongan) AND periode = '".$param['periode']."' AND nik !='' AND nik!='0000000000' ".$wh." 
                    AND noreferensi LIKE 'ALK_POT%' and keterangan  like '%Pot. PPh 21%'";
    $resalokplus = fetchdata($getalokplus);
    //$arralokplus = 0;
    foreach ($resalokplus as $key => $val) {
        $arralokplus -= $val['debet'];
    }

	## GET TOTAL ALOKASI MINUS
	$getalokminus = "SELECT sum(kredit) as kredit
					FROM ".$dbname.".keu_jurnaldt_vw 
					WHERE noakun like '213%'  and substr(noakun,1,5) < '21302' AND periode = '".$param['periode']."' AND nik !='' AND nik!='0000000000' ".$wh."  AND jumlah < 0 AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%'";
	$resalokminus = fetchdata($getalokminus);
	$arralokminus = 0;
	foreach ($resalokminus as $key => $val) {
		$arralokminus += $val['kredit'];
	}

    $getalokminus = "SELECT sum(debet) as kredit
                    FROM ".$dbname.".keu_jurnaldt_vw 
                    WHERE noakun like '213%'  and substr(noakun,1,5) < '21302' AND periode = '".$param['periode']."' AND nik !='' AND nik!='0000000000' ".$wh."  AND jumlah > 0 AND keterangan  LIKE '%Tunjangan Pajak%'";
    $resalokminus = fetchdata($getalokminus);
    //$arralokminus = 0;
    foreach ($resalokminus as $key => $val) {
        $arralokminus -= $val['kredit'];
    }

    ## GET TOTAL ALOKASI MINUS
    $getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
                    FROM ".$dbname.".keu_jurnaldt_vw 
                    WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
                    AND periode = '".$param['periode']."'
                    AND nik !='' AND nik!='0000000000' ".$wh." 
                    AND jumlah > 0
                    AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%' 
                    and nojurnal like '%PNN%' 
                    and autojurnal='1'
                    and keterangan like '%potong buah dan premi panen%'";
                    $resalokminus = fetchdata($getalokminus);
    foreach ($resalokminus as $key => $val) {
        $arralokminus += ($val['debet']*-1);
    }

    
    $getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
                                    FROM ".$dbname.".keu_jurnaldt_vw 
                                    WHERE noakun not like '213%'
                                    AND periode = '".$param['periode']."'
                                    AND nik !='' AND nik!='0000000000' ".$wh." 
                                    AND jumlah > 0
                                    AND kodejurnal in ('BM01')
                                    AND autojurnal='1'
                                    AND noaruskas=''
                                    GROUP BY nik";
    $resalokminus = fetchdata($getalokminus);
    foreach ($resalokminus as $key => $val) {
        if(getKaryHist($val['nik'],$param['periode'],'lokasitugas') != $param['kodeorg']) {
            //echo getKaryHist($val['nik'],$periode,'lokasitugas').'!='.$param['kodeorg'].'<br>';
            continue;
        }
        $arralokminus +=$val['debet'];
        

    }

    ## GET TOTAL ALOKASI MINUS
    $getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
                    FROM ".$dbname.".keu_jurnaldt_vw 
                    WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
                    AND periode = '".$param['periode']."'
                    AND nik !='' AND nik!='0000000000' ".$wh." 
                    AND jumlah > 0
                    AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%' 
                    and nojurnal like '%KBNB0%' 
                    and autojurnal='1'";
                    $resalokminus = fetchdata($getalokminus);
    foreach ($resalokminus as $key => $val) {
        $arralokminus += ($val['debet']*-1);
    }   

    ## GET TOTAL ALOKASI MINUS
    $getalokminus = "SELECT nik, sum(debet) as debet, kodeorg
                    FROM ".$dbname.".keu_jurnaldt_vw 
                    WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
                    AND periode = '".$param['periode']."'
                    AND nik !='' AND nik!='0000000000' ".$wh." 
                    AND jumlah > 0
                    AND noreferensi  LIKE 'ALK_GAJI_TERTINGGAL%' 
                    AND nojurnal like '%/M/%'
                    AND autojurnal='1'
                    AND noaruskas=''";
    $resalokminus = fetchdata($getalokminus);
    foreach ($resalokminus as $key => $val) {
        $arralokminus += ($val['debet']*-1);
    }

    ## GET TOTAL ALOKASI MINUS
    $getalokminus = "SELECT nik, sum(kredit) as kredit, kodeorg
                    FROM ".$dbname.".keu_jurnaldt_vw 
                    WHERE noakun like '213%' and substr(noakun,1,5) < '21302'
                    AND periode = '".$param['periode']."'
                    AND nik !='' AND nik!='0000000000' ".$wh." 
                    AND jumlah < 0
                    AND noreferensi NOT LIKE 'ALK_POT%' 
                    AND nojurnal like '%/M/%'
                    AND autojurnal='1'
                    AND noaruskas=''";
    $resalokminus = fetchdata($getalokminus);
    foreach ($resalokminus as $key => $val) {
        $arralokminus += $val['kredit'];
    }   

	$selisihplus = $arrjmlhplus-$arralokminus;
	$selisihminus = $arrjmlhminus-$arralokplus;
	$selisihtotal = $selisihplus-$selisihminus;

	if(abs($selisihtotal)>200 && $deb){
		exit("Warning: Gaji belum terlalokasi seluruhnya ".$selisihtotal.", silahkan cek laporan Keuangan - Laporan - Transaksi Lainnya - Daftar Alokasi Gaji.<br>Untuk mengalokasikan gaji silahkan lakukan proses pada menu Keuangan - Proses - Proses Akhir Bulan");
	}
}	


#================ cek setup_blok_tahunan sudah dilakukan / belum
if($tipeorg[$param['kodeorg']]=='KEBUN'){
    #cek bloknya ada atau gak
    $scekblok="select * from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['kodeorg']."'";
    $rcekblok=fetchData($scekblok);
    if(count($rcekblok)!=0){
        $str="SELECT count(*) as jumlah
        FROM ".$dbname.".`setup_blok_tahunan`
        WHERE `tahun` ='".$tahunbulan."' and 
        substr(kodeorg,1,4)='".$param['kodeorg']."'";
        $res=fetchData($str);
        $jumlah=$res[0]['jumlah'];
        if($jumlah=='0' || $jumlah==''){
            exit("Warning:
                blok tahunan untuk periode ".$param['periode']." belum dilakukan, 
                harap lakukan proses tutup buku areal statement di :
                kebun->proses->tutup aresta");
        }
    }		
}
#=========================================================
/*
1. Pastikan loading dan fee sudah di posting => proses tutup buku
2. Pastikan premi pemanen sudah di posting =>proses gaji
3. Premi mandoran sudah di lakukan (cuma ini gak ada posting nya gimana validasinya) => proses gaji
4. tbs petani => proses tutup buku (pabrik)
*/

#=============== cek transaksi tbs petani

if($tipeorg[$param['kodeorg']]=='PABRIK'){
	$str="SELECT * FROM ".$dbname.".`kebun_tbskud`
	WHERE `tanggal`  like '".$param['periode']."%' and  unit='".$param['kodeorg']."' and posting=0";
	// exit("Error:$str");
	$res=fetchData($str);
	$notbskud=0;
	$data="Ada Transaksi TBS Petani yang harus diposting\n";
	foreach($res as $bar){
		$notbskud++;
		$data.="Notransaksi : ".$bar['notransaksi']." ; ".$bar['tanggal']." ; ".$bar['divisi']."\n";
	}
	if($notbskud>0){
		echo $data;
		exit("Warning:");
	}	
}

#=======================================================================================================
if($tipeorg[$param['kodeorg']]=='KEBUN'){
	$jumlahtranspanen=$jumlahtransrekap=0;
	$str = "select * from ".$dbname.".kebun_aktifitas where kodeorg='".$param['kodeorg']."' and tanggal between '".$currstart."' and '".$currend."' and tipetransaksi='PNN' and deviceid is null and jurnal='1'";
	$res = fetchData($str);
	foreach($res as $bar){
		if($bar['noreferensi']==''){
			$jumlahtranspanen++;
		}
		if($bar['noreferensi']!=''){
			$jumlahtransrekap++;
		}
	}
	if($jumlahtranspanen>0 and $jumlahtransrekap==0){
		echo"Proses rekap premi panen belum dilakukan atau belum diposting.";
		exit("Warning:");
	}
}


#=======================================================================================================



// exit("Error:MASUK");
#============================


$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLM'");
$akunCLM='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunCLM=$bal->noakundebet;
}
//ambil akun laba ditahan
$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLY'");
$akunCLY='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunCLY=$bal->noakundebet;
}

//ambil batas bawah akun laba/rugi
$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='RAT'");
$akunRAT='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunRAT=$bal->noakundebet;
}
if($akunCLM=='' or $akunCLY=='' or $akunRAT=='')
{
    if($_SESSION['language']=='EN'){
        exit('Warning: Annual income account data, account  retained earnings and account limits profits / losses not yet listed on the parameters of the journal');
    }else{
        exit('Warning: data akun laba tahunan, akun laba ditahan dan batas akun laba/rugi belum terdaftar pada parameter jurnal');
    }
}

#periksa apakah sudah diposting semua transaksi kas dan bappp
$str=$owlPDO->query("select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."'");
$currstart='';
$currend='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
    
if($currstart=='' or $currend=='')
{

    exit('Warning: '.$param['kodeorg'].' '.$_SESSION['lang']['periodetidaknormal']);//periodetidaknormal
}
else
{
    
    #periksa apakah ada tagihan yang tipenya jurnal, belum diposting diperiode yang akan ditutup
    $str="select * from ".$dbname.".keu_tagihanht where unit='".$param['kodeorg']."' 
            and tanggal between '".$currstart."' and '".$currend."' and posting=0 and
            tipeinvoice in (select kode from ".$dbname.".keu_5jenistagihan where jurnal=1)";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $row=$res->rowCount();
    $res->setFetchMode(PDO::FETCH_ASSOC);
    if($row>0){
        echo "Ada Tagihan dengan status jurnal belum diposting :\n";
        $no=0;
        while($bar=$res->fetch()){
            $no+=1;
            echo $no.". No ".$bar['noinvoice']."\n"; 
        }
        exit('Warning');
    }
    
    #periksa periode kas kecil sudah tutup atau belum
    $str=$owlPDO->query("select close from ".$dbname.".keu_5kaskecil where periode='".$param['periode']."' and unit='".$param['kodeorg']."' and close=0 ");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($str);
    if($numrows>0){
        echo "Kas Kecil unit ".$param['kodeorg']." pada periode ".$param['periode']." belum tutup. \n";
        exit('Warning');
    }

    #periksa kas
	/*
    $str=$owlPDO->query("select notransaksi,tanggal,jumlah from ".$dbname.".keu_kasbankht where kodeorg='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting<>1");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($str);
    if($numrows>0){
        echo $_SESSION['lang']['cekkasbank']." :\n";
        $no=0;
        while($bar=$str->fetch()){
           $no+=1;
            echo $no.". No : ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlah,0)."\n"; 
        }
        exit('Warning');
    }
	*/

    #periksa bapp
    $str="select notransaksi,tanggal,sum(jumlahrealisasi) as jumlahrealisasi from ".$dbname.".log_baspk where kodeblok like '".$param['kodeorg']."%'
          and tanggal between '".$currstart."' and '".$currend."' and statusjurnal=0 group by notransaksi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo $_SESSION['lang']['cekspk']." :<br>";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlahrealisasi,0)."<br>"; 
        }
        exit('Warning');
    }
    #periksa jurnal tidak balance
    $str="select nojurnal,tanggal,debet,kredit from ".$dbname.".keu_jurnal_tidak_balance_vw where kodeorg = '".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."'
          and nojurnal not like '%/CLSM/%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo $_SESSION['lang']['notifjurnaltidakbalance']." :<br>";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->nojurnal.":".tanggalnormal($bar->tanggal)."->(D)Rp. ".number_format($bar->debet,0).":(K)Rp. ".number_format($bar->kredit,0)."<br>"; 
        }
        exit('Warning');
    }
    
    #periksa gudang
    $str="select notransaksi,tanggal, kodegudang from ".$dbname.".log_transaksiht where post=0 and hasilpersetujuan1!=2 and kodegudang like '".$param['kodeorg']."%'
            and tanggal between '".$currstart."' and '".$currend."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    $stm='';
    if($numrows>0){
        while($bar=$res->fetch()){
             $stm.="Gudang:".$bar->kodegudang."->No.>".$bar->notransaksi."->".$bar->tanggal."<br>";
         }
       echo "Warning: ".$_SESSION['lang']['notifgudang']." <br>".$stm; 
       exit();
    }

    #periksa apakah ada gudang yg belum tutup
    $str="select kodeorg,periode from ".$dbname.".setup_periodeakuntansi where kodeorg like '".$param['kodeorg']."%' and periode='".$param['periode']."' and tutupbuku=0 and char_length(kodeorg)=6 ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    $sgudang="";
    if($numrows>0){
        while($bar=$res->fetch()){
            $sgudang.="- ".$bar->kodeorg."<br>";
        }
       echo "Warning : <br><br>".$_SESSION['lang']['notiftutupgudang']." : <br>".$sgudang; 
       exit();
    }
     
    #Periksa BKM
    $str="select notransaksi,tanggal from ".$dbname.".kebun_aktifitas where kodeorg='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and jurnal=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo $_SESSION['lang']['notifbkm']." :<br>";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."<br>";
        }
        exit('Warning');
    }
   #Periksa TRAKSI
    $str="select notransaksi,tanggal from ".$dbname.".vhc_runht where kodeorg='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo  $_SESSION['lang']['notiftraksi']."  :<br>";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."<br>";
        }
        exit('Warning');
    }  
    #Periksa REKAP PANEN
    $str="select distinct(divisi), tanggal from ".$dbname.".kebun_rekappnn_vw where substr(divisi,1,4)='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo  $_SESSION['lang']['notifrekappnn']."  :<br>";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". Afdeling => ".$bar->divisi." Tanggal => ".tanggalnormal($bar->tanggal)."<br>";
        }
        exit('Warning');
    }
    $tipeLokasitugas=makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$param['kodeorg']."'");
    #Periksa Tutup Aresta
    if($tipeLokasitugas[$param['kodeorg']]=='KEBUN'){
        #cek bloknya ada atau gak
        $scekblok="select * from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['kodeorg']."'";
        $rcekblok=fetchData($scekblok);
        if(count($rcekblok)!=0){
                $str="select * from ".$dbname.".setup_blok_tahunan where kodeorg like '".$param['kodeorg']."%' and tahun = '".$tahunbulan."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $numrows=owlBaris($res);
                if($numrows==0){
                    exit("Warning : Silahkan lakukan Tutup Aresta melalui menu : Kebun - Proses - Tutup Aresta.");
                }
        }
    }
}   

#PERIKSA akun transit yang belum nol=============================
$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$param['kodeorg']."' AND noakun like '4%'";//exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$transit=0;
if($numrows>0){
        while($bar=$res->fetch()){
            $transit=abs($bar->saldo);
        }
}
if($transit>10 && $transit!='')#lebih dari  10 rupiah
{
    exit(" Warning:  ".$_SESSION['lang']['notifakuntransit']." :".number_format($transit));
}


#= tambahan trap dipisah antara bengkel dan traksi 

#= bengkel dlu
$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$param['kodeorg']."' AND noakun like '41101%'";//exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$transit=0;
if($numrows>0){
        while($bar=$res->fetch()){
            $transit=abs($bar->saldo);
        }
}
if($transit>10 && $transit!='')#lebih dari  10 rupiah
{
   exit(" Warning:  ".$_SESSION['lang']['notifakuntransit']." (Workshop) :".number_format($transit));
}

#= transaksi
$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$param['kodeorg']."' AND noakun like '41102%'";//exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$transit=0;
if($numrows>0){
        while($bar=$res->fetch()){
            $transit=abs($bar->saldo);
        }
}
if($transit>10 && $transit!='')#lebih dari  10 rupiah
{
   exit(" Warning:  ".$_SESSION['lang']['notifakuntransit']." (Traksi) :".number_format($transit));
}




#---------------------------------------==================================

/**************************************************************
 * [START] Cek Nilai Material VS Jurnal ***********************
 **************************************************************/
 
if($tipeLokasitugas[$param['kodeorg']]=='KANWIL'){
		 
	// Get Kelompok Barang yang ada Akun
	$optKel = makeOption($dbname,'log_5klbarang',"kode,noakun","noakun!='' and noakun like '11504%'");
	$listKel = $listAkun = array();
	foreach($optKel as $kode=>$akun) {
		$listKel[] =  $kode;
		$listAkun[$akun] =  $akun;
	}

	// Get Nilai Material, log_5saldobulanan
	$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
		FROM ".$dbname.".log_5saldobulanan 
		WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".
			$param['kodeorg']."%' and periode like '".$param['periode']."%'
		GROUP BY left(kodebarang,3)";   
	$resSaldoMat = fetchData($qSaldoMat);
	$optSaldoMat = array();
	foreach($resSaldoMat as $row) {
		if(!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
			$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
		} else {
			$optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
		}
	}
	 

	// Get Nilai Jurnal, keu_saldobulanan
	$qSaldoJ ="SELECT awal".$bulan." as saldoawal,noakun
		FROM ".$dbname.".keu_saldobulanan
		WHERE kodeorg='".$param['kodeorg']."' and periode='".$tahunbulan."'
			and noakun in ('".implode("','",$listAkun)."')";
	$resSaldoJ = fetchData($qSaldoJ);
	$optSaldoJ = array();
	foreach($resSaldoJ as $row) {
		$optSaldoJ[$row['noakun']] = $row['saldoawal'];
	}

	// Get Transaksi Jurnal
	$qTrans ="SELECT sum(debet - kredit) as saldotrans, noakun
		FROM ".$dbname.".keu_jurnaldt_vw
		WHERE kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'
			and noakun in ('".implode("','",$listAkun)."')
		GROUP BY noakun";
	$resTrans = fetchData($qTrans);
	foreach($resTrans as $row) {
		if(!isset($optSaldoJ[$row['noakun']])) 
			$optSaldoJ[$row['noakun']] = 0;
		$optSaldoJ[$row['noakun']] += $row['saldotrans'];
	}
}else{
		 
	// Get Kelompok Barang yang ada Akun
	$optKel = makeOption($dbname,'log_5klbarang',"kode,noakun","noakun!='' and noakun like '11501%'");
	$listKel = $listAkun = array();
	foreach($optKel as $kode=>$akun) {
		$listKel[] =  $kode;
		$listAkun[$akun] =  $akun;
	}

	// Get Nilai Material, log_5saldobulanan
	$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
		FROM ".$dbname.".log_5saldobulanan 
		WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".
			$param['kodeorg']."%' and periode like '".$param['periode']."%'
		GROUP BY left(kodebarang,3)";   
	$resSaldoMat = fetchData($qSaldoMat);
	$optSaldoMat = array();
	foreach($resSaldoMat as $row) {
		if(!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
			$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
		} else {
			$optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
		}
	}
	 

	// Get Nilai Jurnal, keu_saldobulanan
	$qSaldoJ ="SELECT awal".$bulan." as saldoawal,noakun
		FROM ".$dbname.".keu_saldobulanan
		WHERE kodeorg='".$param['kodeorg']."' and periode='".$tahunbulan."'
			and noakun in ('".implode("','",$listAkun)."')";
	$resSaldoJ = fetchData($qSaldoJ);
	$optSaldoJ = array();
	foreach($resSaldoJ as $row) {
		$optSaldoJ[$row['noakun']] = $row['saldoawal'];
	}

	// Get Transaksi Jurnal
	$qTrans ="SELECT sum(debet - kredit) as saldotrans, noakun
		FROM ".$dbname.".keu_jurnaldt_vw
		WHERE kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'
			and noakun in ('".implode("','",$listAkun)."')
		GROUP BY noakun";
	$resTrans = fetchData($qTrans);
	foreach($resTrans as $row) {
		if(!isset($optSaldoJ[$row['noakun']])) 
			$optSaldoJ[$row['noakun']] = 0;
		$optSaldoJ[$row['noakun']] += $row['saldotrans'];
	}
}	


// Cek All Akun
$notBal = "";
foreach($listAkun as $akun) {
    if(!isset($optSaldoMat[$akun])) $optSaldoMat[$akun] = 0;
    if(!isset($optSaldoJ[$akun])) $optSaldoJ[$akun] = 0;
    
    $selisih = abs( abs($optSaldoMat[$akun]) - abs($optSaldoJ[$akun]) );
    if($selisih > 300) {
        $notBal .= $akun." = ".number_format($selisih)."\n";
    }
}

// Alert Jika ada yang belum balance

if ($param['periode']>'2018-02') {
    if(!empty($notBal)) {
        exit("Warning:  ".$_SESSION['lang']['notifmaterial']." \n".$notBal); //
    }
}

/**************************************************************
 * [END] Cek Nilai Material VS Jurnal *************************
 **************************************************************/

if(substr($param['kodeorg'],2,2)!='HO'){
    $str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
              and nojurnal like '%".$param['kodeorg']."/KBN%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){

    }else{
        exit(" Warning : ".$_SESSION['lang']['notifprosesgaji']." ");
    }
    #---------------------------------------==================================
}



// CEK SPB INPUT/POSTING diambil dari KEBUN_SLAVE_PANEN_DETAIL
// cek spb vs tiket
$spbbelumdiinput='';
$query = $owlPDO->query("SELECT a.nospb, b.tanggal
    FROM ".$dbname.".`pabrik_timbangan` a
    LEFT JOIN ".$dbname.".kebun_spbht b ON a.nospb = b.nospb
    WHERE a.`tanggal` LIKE '".$param['periode']."%' and a.`kodeorg` = '".$param['kodeorg']."'
        AND b.`tanggal` is NULL");
$query->setFetchMode(PDO::FETCH_ASSOC);
while($rDetail=$query->fetch()){
    $spbbelumdiinput.=$rDetail['nospb'].', ';
}      
 
// if($spbbelumdiinput!=''){
//     $spbbelumdiinput=substr($spbbelumdiinput,0,-2);
//     exit("Warning :".$_SESSION['lang']['notifspbinput']."  : ".$spbbelumdiinput);
// }

#Periksa SPB vs WB
$str="select sum(kgwb) as kgwb from ".$dbname.".kebun_spb_vw where substr(divisi,1,4)='".$param['kodeorg']."' and tanggal between '".$currstart."' and '".$currend."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$kgspb='';
while($bar=$res->fetch()){
    $kgspb=$bar['kgwb'];
}

$str="select sum(beratbersih) as kgpks from ".$dbname.".pabrik_timbangan where kodeorg='".$param['kodeorg']."' 
and substr(tanggal,1,10) between '".$currstart."' and '".$currend."' and kodebarang='40000003'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$kgpks='';
while($bar=$res->fetch()){
    $kgpks=$bar['kgpks'];
}

$selisih = ($kgpks-$kgspb);
if($selisih>5)#lebih dari  5 kg
{
    //exit("Warning:  ".$_SESSION['lang']['notifspbvspks']." :\nKg SPB/SPATB = ".number_format($kgspb,0)." Kg, WB/PKS = ".number_format($kgpks)." Kg, Selisih = ".number_format($selisih)." Kg");
}

$spbbelumdiposting='';
$query = $owlPDO->query("SELECT nospb, tanggal
    FROM ".$dbname.".`kebun_spb_vw`
    WHERE `tanggal` LIKE '".$param['periode']."%' and `blok` like '".$param['kodeorg']."%'
        and posting = 0
        ");
$query->setFetchMode(PDO::FETCH_ASSOC);
while($rDetail=$query->fetch()){
    $spbbelumdiposting.=$rDetail['nospb'].', ';
}        
if($spbbelumdiposting!=''){
    $spbbelumdiposting=substr($spbbelumdiposting,0,-2);
    // echo "WARNING: ".$_SESSION['lang']['notifspbposting']." : ".$spbbelumdiposting;//
    // exit();
}
//============================================================================== END OF CEK SPB

/**************************************************************
 * [START] Cek Pengakuan Penjualan ****************************
 **************************************************************/
 
if(substr($param['kodeorg'],2,2)=='HO'){
$listTiket="";
$qJual = $owlPDO->query("SELECT notransaksi,a.nokontrak
        FROM ".$dbname.".pabrik_timbangan a
        INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
        WHERE date(a.tanggal) between '".$currstart."' and '".$currend.
            "'and a.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='PABRIK')");
//exit("error:".$qJual);
    $resJual = fetchData($qJual);
    if(!empty($resJual)) {
        $listTiket = '';
        foreach($resJual as $row) {
            $scek2="select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual 
                    where notransaksi='".$row['notransaksi']."'";
                    //exit("error:".$scek2);
            $qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
            $qcek2->setFetchMode(PDO::FETCH_OBJ); 
            $rcek2=owlBaris($qcek2);
            if($rcek2==0){
                $listTiket .= "- ".$row['notransaksi']."\n";    
            }
        }
        if($listTiket!=''){
            exit("Warning:  ".$_SESSION['lang']['notifpengakuanjual']." \n".$listTiket); //   
        }
        
    }
}


/**************************************************************
 * [END] Cek Pengakuan Penjualan ******************************
 **************************************************************/

// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

switch($proses) {
    case 'tutupBuku':

        #==================== Prep Periode ====================================
        # Prep Tahun Bulan untuk periode selanjutnya
        if($tmpPeriod[1]==12) {
            $bulanLanjut = 1;
            $tahunLanjut = $tmpPeriod[0]+1;
        } else {
            $bulanLanjut = $tmpPeriod[1]+1;
            $tahunLanjut = $tmpPeriod[0];
        }
        
        # Prep Hari untuk periode selanjutnya
        $jmlHari = cal_days_in_month(CAL_GREGORIAN,$bulanLanjut,$tahunLanjut);
        $tglAwal = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-01';
        $tglAkhir = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-'.addZero($jmlHari,2);
        #==================== /Prep Periode ===================================
        
        #==================== Prep Jurnal =====================================
        #=== Extract Data ====
        # Get PT
        $pt = getPT($dbname,$param['kodeorg']);
        if($pt==false) {
            $pt = getHolding($dbname,$param['kodeorg']);
        }
        
        # Tanggal dan Kode Jurnal
        $tgl = $tmpPeriod[0].$tmpPeriod[1].
            cal_days_in_month(CAL_GREGORIAN,$tmpPeriod[1],$tmpPeriod[0]);
        $kodejurnal = 'CLSM';
        
        
        #==================== Journal Counter ==================
        $nojurnal = $tgl."/".$param['kodeorg'].
            "/".$kodejurnal."/999";
        #==================== Journal Counter ==================
        
        # Cek apakah tahun sudah ditutup
        $qCek = selectQuery($dbname,'keu_jurnalht','*',
            "nojurnal='".$nojurnal."'");
       // echo "error:".$qCek;
       // exit;
        //exit('warning : '.$qCek);
        $resCek = fetchData($qCek);
        if(!empty($resCek)) {
            $sPeriode=$owlPDO->query("select periode from ".$dbname.".setup_periodeakuntansi 
                       where kodeorg='".$param['kodeorg']."' order by periode desc limit 1");
            $sPeriode->setFetchMode(PDO::FETCH_ASSOC);
            $rPeriode=$sPeriode->fetch();
            if($rPeriode['periode']==$param['periode']){
                $sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
                try{
                    $owlPDO->exec($sDel); 
                }catch(PDOException $e){
                    echo "DB Error : " . $e->getMessage();
                    die();
                }
            }else{
                echo ' warning : '.$_SESSION['lang']['notifperiode'];//
                exit();    
            }
        }
        
        
        
        $query =  "select count(*) as x from ".$dbname.".keu_jurnaldt_vw where 
                   tanggal between '".$currstart."' and '".$currend."' and kodeorg='".$param['kodeorg']."'";
//         exit("error: ".$query);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($res);
        // if($numrows==0) {
        //     echo 'Warning : '.$_SESSION['lang']['datanotfound'];//
        //     exit;
        // }
        
        # Get Sum dari Jurnal
        $query = selectQuery($dbname,'keu_jurnaldt_vw','kodeorg as kodeorg,sum(jumlah) as jumlah',
            "kodeorg='".$param['kodeorg']."' and tanggal between '".$currstart."' and '".$currend."'
             and noakun>='".$akunRAT."'").
            "group by kodeorg";
        $data = fetchData($query);

        
        # Get Akun
        #+++++++++++++++++++++++++
        //tambahan ginting
        $noakun=$akunCLM;//akun laba tahun berjalan
        #++++++++++++++++++++++++++
        if($data[0]['jumlah']>0) {
            # Rugi
            $debetH=$data[0]['jumlah'];
            $kreditH=0;
        } else {
            # Laba
            $debetH=0;
            $kreditH=$data[0]['jumlah'];            
        }
        
        # Prep Header
        $dataRes['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>$kodejurnal,
            'tanggal'=>$tgl,
            'tanggalentry'=>date('Ymd'),
            'posting'=>'0',
            'totaldebet'=>floatval($debetH),
            'totalkredit'=>floatval($kreditH),
            'amountkoreksi'=>'0',
            'noreferensi'=>'TUTUP/'.$param['kodeorg'].'/'.$tahunbulan,
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'
        );
        
        # Data Detail
        $noUrut = 1;
        
        # Debet
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tgl,
            'nourut'=>$noUrut,
            'noakun'=>$noakun,
            'keterangan'=>'Tutup Bulan '.$tahunbulan.' Unit '.$param['kodeorg'],
            'jumlah'=>floatval($data[0]['jumlah']),
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$param['kodeorg'],
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>'',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>'',
            'revisi'=>'0',
            'kodesegment'=>$defSegment
        );
        $noUrut++;
//exit("Error:A");

       #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
        $headErr = '';
        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
        try{$owlPDO->exec($insHead); }
        catch (PDOException $e) {
            $headErr .= "Insert Header Error sini clsm : " . $e->getMessage() . "\n".$insHead; 
        }
        
        if($headErr=='') {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                try{$owlPDO->exec($insDet); }
                catch (PDOException $e) {
                    $detailErr .= "Insert Detail Error : " . $e->getMessage() . "\n".$insDet; 
                    break;
                }     
            }
            
            if($detailErr=='') {
                
                /**
                 * Cek Nilai Akumulasi Penyusutan (Dari Daftar Asset) dengan Nilai Akumulasi Penyusutan pada Saldo Bulanan
                 */
                // Ambil Nilai Akumulasi Penyusutan
                if($_SESSION['language']=='EN'){
                    $zz="b.namatipe1 as namatipe";
                } else {
                    $zz="b.namatipe";
                }
                $totalsudahsusut=0;
				$selisihsusut=0;
                $rinci = array();
                $str=$owlPDO->query("select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan, a.hargaperolehan ,".$zz.",left(a.tanggaldisposal,7) as periodenonaktif 
                      from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
                      on a.tipeasset=b.kodetipe    
                      where a.kodeorg='".$param['kodeorg']."' 
                      and status=1  and a.awalpenyusutan <= '".$param['periode']."' and persendecline=0");
                
                $arrAsset = array();
                $str->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$str->fetch()){
                    $x=mktime(0,0,0,  intval(substr($bar->awalpenyusutan,5,2)+($bar->jlhblnpenyusutan)),15,substr($bar->awalpenyusutan,0,4));
                    $maxperiod=date('Y-m',$x);
                    if($bar->periodenonaktif!="0000-00"){
                        if($param['periode']>=$bar->periodenonaktif){
                            continue;
                         }  
                    }
					
					$totalsudahsusut=0;
					$selisihsusut=0;
					if($param['periode']<$maxperiod){
						$totalsudahsusut=$bar->bulanan*$bar->jlhblnpenyusutan;
						$selisihsusut=$bar->hargaperolehan-$totalsudahsusut;
					}
					 
                    if($param['periode']!=periodelalu($maxperiod)){
                        $selisihsusut=0;
                    }

                    if($param['periode']<$maxperiod) {
                       if(!isset($arrAsset[$bar->tipeasset]['nilai'])) $arrAsset[$bar->tipeasset]['nilai']=0;
                       $arrAsset[$bar->tipeasset]['nilai']+=$bar->bulanan+$selisihsusut;
                    }

                    //
                    $arrAsset[$bar->tipeasset]['nama']=$bar->namatipe;
					if($tipeorg[$param['kodeorg']]=='HOLDING'){
						$arrAsset[$bar->tipeasset]['kode']='DPH'.substr($bar->tipeasset,0,2);
					}elseif($tipeorg[$param['kodeorg']]=='KANWIL'){
						$arrAsset[$bar->tipeasset]['kode']='DPH'.substr($bar->tipeasset,0,2);
                    }else{
						$arrAsset[$bar->tipeasset]['kode']='DEP'.substr($bar->tipeasset,0,2);
					}
                }
                
                //Ambil double declining
     //            $str=$owlPDO->query("select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,
					//  a.persendecline,a.hargaperolehan,".$zz." 
     //                 from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
     //                 on a.tipeasset=b.kodetipe    
     //                 where a.kodeorg='".$param['kodeorg']."' 
     //                 and status=1 and a.awalpenyusutan <= '".$param['periode']."' and a.persendecline>'0'");
     //            $str->setFetchMode(PDO::FETCH_OBJ);
     //            while($bar=$str->fetch()){
     //                $thnawal=substr($bar->awalpenyusutan,0,4);
     //                $blnawal=substr($bar->awalpenyusutan,5,2);
     //                $total=($thnawal*12)+$blnawal;
                
     //                $thnNow=substr($param['periode'],0,4);
     //                $blnNow=substr($param['periode'],5,2);
                    
     //                $totalBulanAwal = 12-$blnawal+1;
     //                $totalTahun = $thnNow-$thnawal-1;
                    
     //                $totalNow=($thnNow*12)+$blnNow+1;
     //                $selisih=$totalNow-$total;
     //                $out=0;
     //                $akumNow = $sekarang = 0;
                    
     //                // Depresiasi s/d akhir tahun
     //                $before = $sekarang = $bar->hargaperolehan;
     //                if($totalTahun>-1) {
     //                    $akumNow += $totalBulanAwal/12 * $bar->persendecline/100 * $sekarang;
     //                }
     //                $sekarang -= $akumNow;
                    
     //                // Depresiasi per Tahun
     //                if($totalTahun>0) {
     //                    for($i=0;$i<$totalTahun;$i++) {
     //                        $before = $sekarang;
     //                        $akumNow += $sekarang*$bar->persendecline/100;
     //                        $sekarang -= $sekarang*$bar->persendecline/100;
     //                    }
     //                }
                    
     //                // Depresiasi per Bulan
     //                $out = $sekarang*($bar->persendecline/100)/12;
     //                if($bar->jlhblnpenyusutan<$selisih) {
     //                    if($totalTahun>-1) {
     //                        $out = $sekarang - ($bulanNow*$sekarang);
     //                    } else {
     //                        $out = $sekarang - (($bulanNow-$bulanawal+1)*$sekarang);
     //                    }
     //                }
                    
     //                if(isset($arrAsset[$bar->tipeasset]['nilai'])) {
     //                    $arrAsset[$bar->tipeasset]['nilai']+=$out;
     //                } else {
     //                    $arrAsset[$bar->tipeasset]['nilai']=$out;
     //                }
     //                $arrAsset[$bar->tipeasset]['nama']=$bar->namatipe;
					
					// if($tipeorg[$param['kodeorg']]=='HOLDING'){
					// 	$arrAsset[$bar->tipeasset]['kode']='DPH'.substr($bar->tipeasset,0,2);
					// }elseif($tipeorg[$param['kodeorg']]=='KANWIL'){
     //                    $arrAsset[$bar->tipeasset]['kode']='DPH'.substr($bar->tipeasset,0,2);
     //                }else{
					// 	$arrAsset[$bar->tipeasset]['kode']='DEP'.substr($bar->tipeasset,0,2);
					// }
     //            }
                
                $poolAsset = array();
                foreach($arrAsset as $row) {
                    if($row['nilai']>0){
                        $poolAsset[$row['kode']] = $row['nilai'];
                    }
                }
                
                // Get List Akun dari Parameter Jurnal = 'DEP'
				
				$optDep = makeOption($dbname,'keu_5parameterjurnal',"jurnalid,noakunkredit","kodeaplikasi in ('DEP','DPH') ");
			
                
                // Get Jurnal
                foreach($poolAsset as $kode=>$nilai) {
                    // No Jurnal
                    //$konter ='001';
                    $tanggal=$param['periode']."-28";
                    # Transform No Jurnal dari No Transaksi
                    //$nojurnal = str_replace("-","",$tanggal)."/".substr($param['kodeorg'],0,4)."/".$kode."/".$konter;
               
                    $qJurnal = selectQuery($dbname,'keu_jurnaldt',"sum(jumlah) as jumlah",
                                           "tanggal = '".$tanggal."' and kodeorg='".$param['kodeorg']."' and nojurnal like '%".$kode."%' and noakun='".$optDep[$kode]."'");
                    //echo $qJurnal.'<br>';
										        // exit("Error:".$qJurnal);
                    $resJurnal = fetchData($qJurnal);
                    if ($param['periode']>'2018-02') {
                        if(empty($resJurnal)) {
                            exit("Warning: ".$kode." : ".$_SESSION['lang']['notifdepresiasi']);//
                        } else {
                            if($resJurnal[0]['jumlah']+round($nilai,2)>0.05) {
                                exit("Warning: ".$kode." : ".$_SESSION['lang']['notifdepresiasi']." Jurnal: ".$resJurnal[0]['jumlah'].", Daftar Asset: ".$nilai);
                            }
                        }
                    }
                }
                
                #==================== /Prep Jurnal ====================================
                createSaldoAwal($param['periode'],$tahunLanjut.'-'.addZero($bulanLanjut,2),$param['kodeorg']);
                
                #========================== Proses Insert dan Update ==========================
                
                # Header and Detail inserted
                # Update Status Tutup Buku
                $queryUpd = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>1),
                    "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                try{$owlPDO->exec($queryUpd); }catch (PDOException $e) {print " Gagal  Update!: " . $e->getMessage() . "\n"; die(); }
                    # Insert periode baru
                    $dataIns = array(
                        'kodeorg'=>$param['kodeorg'],
                        'periode'=>$tahunLanjut.'-'.addZero($bulanLanjut,2),
                        'tanggalmulai'=>$tglAwal,
                        'tanggalsampai'=>$tglAkhir,
                        'tutupbuku'=>0
                    );
                    $queryIns = insertQuery($dbname,'setup_periodeakuntansi',$dataIns);
                    echo '1';
                    $test=false;
                    try{$test=$owlPDO->exec($queryIns); }
                    catch (PDOException $e) {
                        print " Gagal  Update!: " . $e->getMessage() . "\n"; 
                         $queryRB = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>0),
                            "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                         try{$owlPDO->exec($queryRB); }
                         catch (PDOException $e) {
                             print " Error Rollback Update  !: " . $e->getMessage() . "\n"; 
                             die();                            
                         }                   
                    }
                    if($test){#berhasil
                            //update history tutup buku
                            $str="delete from ".$dbname.".keu_setup_watu_tutup where periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."'";
                            $owlPDO->exec($str);
                            $str="insert into ".$dbname.".keu_setup_watu_tutup(kodeorg,periode,username) values(
                                  '".$param['kodeorg']."','".$param['periode']."','".$_SESSION['standard']['username']."')";
                            $owlPDO->exec($str);                             
                        }                    
            } else {
                echo $detailErr;
                # Rollback, Delete Header
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                try{$owlPDO->exec($RBDet); }catch (PDOException $e) {print " Rollback Delete Header Error  !: " . $e->getMessage() . "\n"; die(); }                
            }
        } else {
            echo $headErr;
            exit;
        }

        
        break;
    default:
}

function createSaldoAwal($dariperiode,$keperiode,$kodeorg)
{
    global $conn;
    global $dbname;
    global $akunRAT;
    global $akunCLM;
    global $akunCLY;
    global $owlPDO;
    $sawal=Array();
    $mtdebet=Array();
    $mtkredit=Array();
    $salak=Array();
    #ambil saldoawal bulan berjalan
    $str="select awal".substr($dariperiode,5,2).",noakun from ".$dbname.".keu_saldobulanan
          where periode='".str_replace("-", "", $dariperiode)."' and kodeorg='".$kodeorg."'";
          
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_NUM);
    while($bar=$res->fetch()){
        $sawal[$bar[1]]=$bar[0];
        $mtdebet[$bar[1]]=0;
        $mtkredit[$bar[1]]=0;
        $salak[$bar[1]]=$bar[0];
    }
    //exit("Error:asd");
    
    #ambil transaksi transaksi bln berjalan
    $str="select debet,kredit,noakun from ".$dbname.".keu_jurnalsum_vw 
          where periode='".$dariperiode."' and kodeorg='".$kodeorg."'";
    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        setIt($sawal[$bar->noakun],0);
        $mtdebet[$bar->noakun]=$bar->debet;
        $mtkredit[$bar->noakun]=$bar->kredit;
        $salak[$bar->noakun]=$mtdebet[$bar->noakun]+$sawal[$bar->noakun]-$mtkredit[$bar->noakun];
    }
    #ambil semu nomor akun
    $str="select noakun from ".$dbname.".keu_5akun where length(noakun)=7";
    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $temp='';
    while($bar=$res->fetch()){
        #create string update current
       
        if($sawal[$bar->noakun]!=''){
         #jika sudah ada di database maka update
            if($mtdebet[$bar->noakun]=='')
                $mtdebet[$bar->noakun]=0;
           if($mtkredit[$bar->noakun]=='')
                $mtkredit[$bar->noakun]=0;
           
           $temp="update ".$dbname.".keu_saldobulanan 
                set debet".substr($dariperiode,5,2)."=".$mtdebet[$bar->noakun].",
                kredit".substr($dariperiode,5,2)."=".$mtkredit[$bar->noakun]."
                where periode='".str_replace("-", "", $dariperiode)."'
                and kodeorg='".$kodeorg."' and noakun='".$bar->noakun."';";
          try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error update mutasi bulanan!: " . $e->getMessage() . "\n"; die(); }   
        }
        else
        {
           #jika belum ada maka insert
         if(isset($sawal[$bar->noakun]) and ($sawal[$bar->noakun]!='' or $mtdebet[$bar->noakun]!='' or  $mtkredit[$bar->noakun]!='')){
            if($mtdebet[$bar->noakun]=='')
                $mtdebet[$bar->noakun]=0;
           if($mtkredit[$bar->noakun]=='')
                $mtkredit[$bar->noakun]=0;
           $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                  awal".substr($dariperiode,5,2).",debet".substr($dariperiode,5,2).",
                  kredit".substr($dariperiode,5,2).")values('". 
                   $kodeorg."','".str_replace("-", "", $dariperiode)."','".$bar->noakun."',0,".
                   $mtdebet[$bar->noakun].",".$mtkredit[$bar->noakun].");";
           try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error insert mutasi bulanan!: " . $e->getMessage() . "\n".$temp; die(); }  
         }
        }   
    } 
    #delete saldo awal bulan selanjutnya;
    $str="delete from ".$dbname.".keu_saldobulanan where periode='".str_replace("-", "", $keperiode)."'
          and kodeorg='".$kodeorg."';";
    try{
        $owlPDO->exec($str);
        
        $saldoditahan=0;
        foreach($salak as $key=>$val){
            if($salak[$key]!=''){
              
                $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                      awal".substr($keperiode,5,2).")values('". 
                       $kodeorg."','".str_replace("-", "", $keperiode)."','".$key."',".$salak[$key].")";
               if(substr($keperiode,5,2)!='01')#jika bukan awal tahun
               {      
                   try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error insert saldo awal!: " . $e->getMessage() . "\n".$temp; die(); }   
               }
               else #jika bulan 12
               {                     
                   if($key<$akunRAT){#jika awal tahun maka hanya akan membawa aktiva saja ke bulan selanjutnya
                #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++                       
                   #deteksi jika saldo ditahan
                   #sudah mengakomodasi tutup akhir tahun    
                    if($key==$akunCLY)
                        $saldoditahan+=$salak[$key];
                    else{                    
                            if($key==$akunCLM){
                                $saldoditahan+=$salak[$key];#tampung laba tahun berjalan ke laba ditahan
                                $salak[$key]=0;
                            }
                            $temp1="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                                  awal".substr($keperiode,5,2).")values('". 
                                   $kodeorg."','".str_replace("-", "", $keperiode)."','".$key."',".$salak[$key].")";

                       #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++                       
                           try{$owlPDO->exec($temp1); }catch (PDOException $e) {print " Error insert saldo awal!: " . $e->getMessage() . "\n".$temp1; die(); } 
                    }                   
                  }
               }
            }   
        }
      //masukkan saldo laba ditahan
     if(substr($keperiode,5,2)=='01'){//hanya pada bulan 12                           
        $temp2="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
          awal".substr($keperiode,5,2).")values
           ('".$kodeorg."','".str_replace("-", "", $keperiode)."','".$akunCLY."',".$saldoditahan.")";
       try{$owlPDO->exec($temp2); }catch (PDOException $e) {print " Error insert laba ditahan pada saldo awal!: " . $e->getMessage() . "\n"; die(); }
     }
    }catch (PDOException $e) {
        print " Error insert mutasi bulanan!: " . $e->getMessage() . "\n".$temp2; die(); 
    }   
}  
?>