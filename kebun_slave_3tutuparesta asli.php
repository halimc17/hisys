<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_POST['proses'];
$kdOrg=$_POST['kdOrg'];
$periode=$_POST['Tahun'];
$tmpPeriod = explode('-',$periode);
$currTahun = $tmpPeriod[0];
$currBulan = $tmpPeriod[1];
$tahun1 = ($currBulan==12)? $currTahun+1: $currTahun;

$periodeBefore = "";
if($currBulan=='01') {
	$periodeBefore .= ($currTahun-1).'-12';
} else {
	$periodeBefore .= $currTahun."-".str_pad($currBulan-1,2,'0',STR_PAD_LEFT);
}

$pNow = str_replace('-','',$periode);
$pBef = str_replace('-','',$periodeBefore);

switch($proses)
{
//load data
case'getData':
    echo"<fieldset>
    <legend>".$_SESSION['lang']['list']." ".$kdOrg."</legend>";
    if($_SESSION['language']=='EN'){
        echo"Important:";
        echo"</br>1. If there is no data in the last year it will use the data from Setup Blocks";
        echo"</br>2. Setup Data Block will be updated according to the data Period ".$periode;
        echo"</br>3. Please make sure Insertion Plants and New Planting has been recorded to the system, it will automatically affect new areastatement";
        echo"</br>4. If the process in smaller period of year, repeat the process for the next year until current year (active period)";
        echo"</br>5. Process button will appear at the bottom";

    }else{
        echo"Catatan:";
        echo"</br>1. Bila data tahun lalu belum ada maka akan digunakan data Setup Blok";
        echo"</br>2. Data Setup Blok akan diupdate sesuai dengan data Periode ".$periode;
        echo"</br>3. Hindari melakukan proses tanpa historis transaksi tanam/pokok mati yang jelas, karena data Setup Blok akan diupdate juga";
        echo"</br>4. Bila melakukan proses pada tahun yang lebih kecil, ulangi proses untuk tahun selanjutnya hingga tahun aktif";
        echo"</br>5. Tombol Proses akan muncul di paling bawah jika data jumlah pokok SETUP BLOK sudah sesuai dengan data hasil perhitungan";
    }
    echo"<table cellspacing=1 border=0 class=sortable>
    <thead>
    <tr>
    <td align=center rowspan=2>".$_SESSION['lang']['kodeorg']."</td>
    <td align=center rowspan=2>".$_SESSION['lang']['tahuntanam']."</td>
    <td align=center rowspan=2>".$_SESSION['lang']['kelaspohon']."</td>
    <td align=center colspan=4>".$_SESSION['lang']['periode']." ".$periodeBefore."</td>
    <td align=center colspan=2>".$_SESSION['lang']['Mutasi1']." ".$periode."</td>
    <td align=center colspan=4>".$_SESSION['lang']['periode']." ".$periode."</td>
    </tr>
    <tr>
    <td align=center>".$_SESSION['lang']['luasareaproduktif']."</td>
    <td align=center>".$_SESSION['lang']['luasareanonproduktif']."</td>
    <td align=center>".$_SESSION['lang']['pokok']."</td>
    <td align=center>".$_SESSION['lang']['statusblok']."</td>
    <td align=center>".$_SESSION['lang']['pokok']."+</td>
    <td align=center>".$_SESSION['lang']['pokok']."-</td>
    <td align=center>".$_SESSION['lang']['luasareaproduktif']."</td>
    <td align=center>".$_SESSION['lang']['luasareanonproduktif']."</td>
    <td align=center>".$_SESSION['lang']['pokok']."</td>
    <td align=center>".$_SESSION['lang']['statusblok']."</td>
    </tr>
    </thead>

    <tbody>";

    // ambil data periode lalu, untuk antisipasi blok baru ambil dari setup_blok
    $sCek="select * from ".$dbname.".setup_blok where kodeorg like '".$kdOrg."%'
        and statusblok in ('TB', 'TBM', 'TM') and tahuntanam > 0 and (luasareaproduktif+luasareanonproduktif) > 0
        order by kodeorg";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    while($rCek=$qCek->fetch())
    {
        $listkodeorg[$rCek['kodeorg']]=$rCek['kodeorg'];
        $tahuntanam[$rCek['kodeorg']]=$rCek['tahuntanam'];    
        $kelaspohon[$rCek['kodeorg']]=$rCek['kelaspohon'];    
        $luasareaproduktif[$rCek['kodeorg']]=$rCek['luasareaproduktif'];    
        $luasareanonproduktif[$rCek['kodeorg']]=$rCek['luasareanonproduktif'];    
        $jumlahpokok[$rCek['kodeorg']]=$rCek['jumlahpokok'];    
        $statusblok[$rCek['kodeorg']]=$rCek['statusblok'];    
        $jumlahpokokcek[$rCek['kodeorg']]=$rCek['jumlahpokok'];    
    }

    // ambil data periode lalu, kalo ada data tahun lalu, timpa yang pool data yang dari setup_blok
    $sCek="select * from ".$dbname.".setup_blok_tahunan where kodeorg like '".$kdOrg."%' and tahun = '".$pBef."' 
        and statusblok in ('TB', 'TBM', 'TM') and (luasareaproduktif+luasareanonproduktif) > 0
        order by kodeorg";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    while($rCek=$qCek->fetch())
    {
        $listkodeorg[$rCek['kodeorg']]=$rCek['kodeorg'];
        $tahuntanam[$rCek['kodeorg']]=$rCek['tahuntanam'];    
        $kelaspohon[$rCek['kodeorg']]=$rCek['kelaspohon'];    
        $jumlahpokok[$rCek['kodeorg']]=$rCek['jumlahpokok'];    
        $statusblok[$rCek['kodeorg']]=$rCek['statusblok'];    
        $jumlahpokoklalu[$rCek['kodeorg']]=$rCek['jumlahpokok'];    
    }

    // ambil data mutasi tambah
    $sCek="select kodeorg, sum(hasilkerja) as tambah from ".$dbname.".kebun_perawatan_vw where kodeorg like '".$kdOrg."%' and tanggal like '".$periode."%'
        and jurnal = '1' and kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'TN')
        group by kodeorg order by kodeorg";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    while($rCek=$qCek->fetch())
    {
        $tambah[$rCek['kodeorg']]=$rCek['tambah'];    
    }

    // ambil data mutasi kurang
    $sCek="select blok, sum(pokokmati) as kurang from ".$dbname.".kebun_rencanasisip where blok like '".$kdOrg."%' and periode like '".$periode."%'
        and posting = '1'
        group by blok order by blok";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    while($rCek=$qCek->fetch())
    {
        $kurang[$rCek['blok']]=$rCek['kurang'];    
    }
	
	$masihadasalah = false;
    if(!empty($listkodeorg))foreach($listkodeorg as $daftar){
		setIt($luasareaproduktif[$daftar],0);
		setIt($luasareanonproduktif[$daftar],0);
		setIt($tambah[$daftar],0);
		setIt($kurang[$daftar],0);
		setIt($jumlahpokokcek[$daftar],0);
		setIt($jumlahpokoklalu[$daftar],0);
		$luasareaproduktif1[$daftar]=$luasareaproduktif[$daftar];
        $luasareanonproduktif1[$daftar]=$luasareanonproduktif[$daftar];
        $jumlahpokok1[$daftar]=$jumlahpokok[$daftar]+$tambah[$daftar]-$kurang[$daftar];
        if(number_format($jumlahpokoklalu[$daftar])==0){ // blok baru, ambil langsung dari setup blok... by pass itung2an
            $jumlahpokok1[$daftar]=$jumlahpokokcek[$daftar];
        }
        if($tahun1-$tahuntanam[$daftar]>=3){ // TM, umur tahun depan >= 3
            $statusblok1[$daftar]='TM';
        }else if($tahun1==$tahuntanam[$daftar]){ // TB // umur tahun depan = tahun... tak mungkin terjadi?
            $statusblok1[$daftar]='TB';        
        }else{
             $statusblok1[$daftar]='TBM';  
        }
        if($jumlahpokok1[$daftar]!=$jumlahpokokcek[$daftar]){
            $masihadasalah=true;
            $warna=" bgcolor=pink";
        }else{
            $warna="";
        }
        echo"<tr class=rowcontent>
        <td align=center>".$daftar."</td>
        <td align=center>".$tahuntanam[$daftar]."</td>
        <td align=center>".$kelaspohon[$daftar]."</td>
        <td align=right>".number_format($luasareaproduktif[$daftar],2)."</td>
        <td align=right>".number_format($luasareanonproduktif[$daftar],2)."</td>
        <td align=right>".number_format($jumlahpokok[$daftar])."</td>
        <td align=center>".$statusblok[$daftar]."</td>
        <td align=center title=\"Details\" style=\"cursor: pointer\" onclick=\"viewData('".$daftar."###".$periode."','".$_SESSION['lang']['detail']." ".$daftar."','<div id=container></div>',event)\";>".number_format($tambah[$daftar])."</td>
        <td align=center title=\"Details\" style=\"cursor: pointer\" onclick=\"viewData('".$daftar."###".$periode."','".$_SESSION['lang']['detail']." ".$daftar."','<div id=container></div>',event)\";>".number_format($kurang[$daftar])."</td>
        <td align=right>".number_format($luasareaproduktif1[$daftar],2)."</td>
        <td align=right>".number_format($luasareanonproduktif1[$daftar],2)."</td>
        <td align=right".$warna." title=\"Data Setup Blok\" style=\"cursor: pointer\" onclick=\"alert('Data Setup Blok: ".$jumlahpokokcek[$daftar].". Data Perhitungan: ".$jumlahpokok1[$daftar]."')\";>".number_format($jumlahpokok1[$daftar])."</td>
        <td align=center>".$statusblok1[$daftar]."</td>
        </tr>";    
    }
    echo"</tbody>
    <table>";
    if(!$masihadasalah){
        echo"</br><button class=mybutton id='process' onclick='processData()'>".$_SESSION['lang']['proses']."</button>";        
    }
    echo"</fieldset>";
break;

case'processData':    
    // ambil data periode lalu, untuk antisipasi blok baru ambil dari setup_blok
    $sCek="select * from ".$dbname.".setup_blok where kodeorg like '".$kdOrg."%'
        and statusblok in ('TB', 'TBM', 'TM') and (luasareaproduktif+luasareanonproduktif) > 0
        order by kodeorg";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    while($rCek=$qCek->fetch())
    {
        $listkodeorg[$rCek['kodeorg']]=$rCek['kodeorg'];
        $tahuntanam[$rCek['kodeorg']]=$rCek['tahuntanam'];    
        $kelaspohon[$rCek['kodeorg']]=$rCek['kelaspohon'];    
        $luasareaproduktif[$rCek['kodeorg']]=$rCek['luasareaproduktif'];    
        $luasareanonproduktif[$rCek['kodeorg']]=$rCek['luasareanonproduktif'];    
        $jumlahpokok[$rCek['kodeorg']]=$rCek['jumlahpokok'];    
        $statusblok[$rCek['kodeorg']]=$rCek['statusblok'];  

        // ambil semua data setup_blok
        $tahunmulaipanen[$rCek['kodeorg']]=$rCek['tahunmulaipanen'];  
        $bulanmulaipanen[$rCek['kodeorg']]=$rCek['bulanmulaipanen'];  
        $kodetanah[$rCek['kodeorg']]=$rCek['kodetanah'];  
        $klasifikasitanah[$rCek['kodeorg']]=$rCek['klasifikasitanah'];  
        $topografi[$rCek['kodeorg']]=$rCek['topografi'];  
        $jenisbibit[$rCek['kodeorg']]=$rCek['jenisbibit'];  
        $tanggalpengakuan[$rCek['kodeorg']]=$rCek['tanggalpengakuan'];  
        $intiplasma[$rCek['kodeorg']]=$rCek['intiplasma'];  
        $basiskg[$rCek['kodeorg']]=$rCek['basiskg'];  
        $periodetm[$rCek['kodeorg']]=$rCek['periodetm'];  
        $cadangan[$rCek['kodeorg']]=$rCek['cadangan'];  
        $okupasi[$rCek['kodeorg']]=$rCek['okupasi'];  
        $rendahan[$rCek['kodeorg']]=$rCek['rendahan'];  
        $sungai[$rCek['kodeorg']]=$rCek['sungai'];  
        $rumah[$rCek['kodeorg']]=$rCek['rumah'];  
        $kantor[$rCek['kodeorg']]=$rCek['kantor'];  
        $pabrik[$rCek['kodeorg']]=$rCek['pabrik'];  
        $jalan[$rCek['kodeorg']]=$rCek['jalan'];  
        $kolam[$rCek['kodeorg']]=$rCek['kolam'];  
        $umum[$rCek['kodeorg']]=$rCek['umum'];  
        $lc[$rCek['kodeorg']]=$rCek['lc'];     
    }

    // ambil data periode lalu, kalo ada data tahun lalu, timpa pool data dari setup_blok
    $sCek="select * from ".$dbname.".setup_blok_tahunan where kodeorg like '".$kdOrg."%' and tahun = '".$pBef."' 
        and statusblok in ('TB', 'TBM', 'TM') and (luasareaproduktif+luasareanonproduktif) > 0
        order by kodeorg";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    while($rCek=$qCek->fetch())
    {
        $listkodeorg[$rCek['kodeorg']]=$rCek['kodeorg'];
        $tahuntanam[$rCek['kodeorg']]=$rCek['tahuntanam'];    
        $kelaspohon[$rCek['kodeorg']]=$rCek['kelaspohon'];    
//        $luasareaproduktif[$rCek['kodeorg']]=$rCek['luasareaproduktif'];
//        $luasareanonproduktif[$rCek['kodeorg']]=$rCek['luasareanonproduktif'];    
        $jumlahpokok[$rCek['kodeorg']]=$rCek['jumlahpokok'];    
        $statusblok[$rCek['kodeorg']]=$rCek['statusblok'];   

        // ambil semua data setup_blok
        $tahunmulaipanen[$rCek['kodeorg']]=$rCek['tahunmulaipanen'];  
        $bulanmulaipanen[$rCek['kodeorg']]=$rCek['bulanmulaipanen'];  
        $kodetanah[$rCek['kodeorg']]=$rCek['kodetanah'];  
        $klasifikasitanah[$rCek['kodeorg']]=$rCek['klasifikasitanah'];  
        $topografi[$rCek['kodeorg']]=$rCek['topografi'];  
        $jenisbibit[$rCek['kodeorg']]=$rCek['jenisbibit'];  
//        $tanggaltransaksi[$rCek['kodeorg']]=$rCek['tanggaltransaksi'];  
        $tanggalpengakuan[$rCek['kodeorg']]=$rCek['tanggalpengakuan'];  
        $intiplasma[$rCek['kodeorg']]=$rCek['intiplasma'];  
        $basiskg[$rCek['kodeorg']]=$rCek['basiskg'];  
        $periodetm[$rCek['kodeorg']]=$rCek['periodetm'];  
        $cadangan[$rCek['kodeorg']]=$rCek['cadangan'];  
        $okupasi[$rCek['kodeorg']]=$rCek['okupasi'];  
        $rendahan[$rCek['kodeorg']]=$rCek['rendahan'];  
        $sungai[$rCek['kodeorg']]=$rCek['sungai'];  
        $rumah[$rCek['kodeorg']]=$rCek['rumah'];  
        $kantor[$rCek['kodeorg']]=$rCek['kantor'];  
        $pabrik[$rCek['kodeorg']]=$rCek['pabrik'];  
        $jalan[$rCek['kodeorg']]=$rCek['jalan'];  
        $kolam[$rCek['kodeorg']]=$rCek['kolam'];  
        $umum[$rCek['kodeorg']]=$rCek['umum'];  
        $lc[$rCek['kodeorg']]=$rCek['lc'];         
    }

    // // ambil data mutasi tambah
    // $sCek="select kodeorg, sum(hasilkerja) as tambah from ".$dbname.".kebun_perawatan_vw where kodeorg like '".$kdOrg."%' and tanggal like '".$periode."%'
        // and jurnal = '1' and kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'TN')
        // group by kodeorg order by kodeorg";
	// $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	// $qCek->setFetchMode(PDO::FETCH_ASSOC);
    // while($rCek=$qCek->fetch())
    // {
        // $tambah[$rCek['kodeorg']]=$rCek['tambah'];    
    // }

    // // ambil data mutasi kurang
    // $sCek="select blok, sum(pokokmati) as kurang from ".$dbname.".kebun_rencanasisip where blok like '".$kdOrg."%' and periode like '".$periode."%'
        // and posting = '1'
        // group by blok order by blok";
	// $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	// $qCek->setFetchMode(PDO::FETCH_ASSOC);
    // while($rCek=$qCek->fetch())
    // {
        // $kurang[$rCek['blok']]=$rCek['kurang'];    
    // }

    // hapus bila sudah ada data
    $sUpd="DELETE FROM ".$dbname.".setup_blok_tahunan WHERE `kodeorg` like '".$kdOrg."%' and tahun = '".$periode."'";
	try{
		$owlPDO->exec($sUpd); 
	}catch (PDOException $e){
		echo "DB Error : ".$e->getMessage();
        exit();
	}
	
    if(!empty($listkodeorg))foreach($listkodeorg as $daftar){
        $luasareaproduktif1[$daftar]=$luasareaproduktif[$daftar];
        $luasareanonproduktif1[$daftar]=$luasareanonproduktif[$daftar];
        $jumlahpokok1[$daftar]=$jumlahpokok[$daftar];
        if($tahun1-$tahuntanam[$daftar]>=3){ // TM, umur tahun depan >= 3
            $statusblok1[$daftar]='TM';
        }else if($tahun1==$tahuntanam[$daftar]){ // TB // umur tahun depan = tahun... tak mungkin terjadi?
            $statusblok1[$daftar]='TB';
        }else{
            $statusblok1[$daftar]='TBM';
        }

        // inject ke tahun ini
        $sIns="INSERT INTO ".$dbname.".setup_blok_tahunan (`kodeorg`, `tahun`, `tahuntanam`, `kelaspohon`, `luasareaproduktif`, `luasareanonproduktif`, 
            `jumlahpokok`, `statusblok`, `tahunmulaipanen`, `bulanmulaipanen`, 
            `kodetanah`, `klasifikasitanah`, `topografi`, `jenisbibit`, 
            `tanggaltransaksi`, `tanggalpengakuan`, `intiplasma`, `basiskg`, 
            `periodetm`, `cadangan`, `okupasi`, `rendahan`, `sungai`, 
            `rumah`, `kantor`, `pabrik`, `jalan`, `kolam`, `umum`, `lc`) 
            VALUES ('".$daftar."', '".$pNow."', '".$tahuntanam[$daftar]."', '".$kelaspohon[$daftar]."', '".$luasareaproduktif1[$daftar]."', '".$luasareanonproduktif1[$daftar]."', 
                '".$jumlahpokok1[$daftar]."', '".$statusblok[$daftar]."', '".$tahunmulaipanen[$daftar]."', '".$bulanmulaipanen[$daftar]."', 
                '".$kodetanah[$daftar]."', '".$klasifikasitanah[$daftar]."', '".$topografi[$daftar]."', '".$jenisbibit[$daftar]."', 
                '".$currTahun."-".$currBulan."-".cal_days_in_month(CAL_GREGORIAN,$currBulan,$currTahun)."', '".$tanggalpengakuan[$daftar]."', '".$intiplasma[$daftar]."', '".$basiskg[$daftar]."', 
                '".$periodetm[$daftar]."', '".$cadangan[$daftar]."', '".$okupasi[$daftar]."', '".$rendahan[$daftar]."', '".$sungai[$daftar]."', 
                '".$rumah[$daftar]."', '".$kantor[$daftar]."', '".$pabrik[$daftar]."', '".$jalan[$daftar]."', '".$kolam[$daftar]."', '".$umum[$daftar]."', '".$lc[$daftar]."')";
		try{
			$owlPDO->exec($sIns); 
			// berhasil insert, lanjut update setup_blok     
            $sUpd="UPDATE ".$dbname.".`setup_blok` SET 
                `luasareaproduktif` = '".$luasareaproduktif1[$daftar]."',
                `luasareanonproduktif` = '".$luasareanonproduktif1[$daftar]."',
                `tanggaltransaksi` = '".$currTahun."-".$currBulan."-".
					cal_days_in_month(CAL_GREGORIAN,$currBulan,$currTahun)."',
                `jumlahpokok` = '".$jumlahpokok1[$daftar]."',
                `statusblok` = '".$statusblok1[$daftar]."' 
                WHERE `setup_blok`.`kodeorg` = '".$daftar."'";echo $sUpd;
			try{
				$owlPDO->exec($sUpd); 
			}catch (PDOException $e){
				echo "DB Error : Silakan hubungi IT.\n".$e->getMessage();	 
                exit();
			}
		}catch (PDOException $e){
			//updatenya disinni
            $updt="update ".$dbname.".setup_blok_tahunan set `kodeorg`='".$daftar."', `tahun`='".$pNow."',
                `tahuntanam`='".$tahuntanam[$daftar]."', `kelaspohon`='".$kelaspohon[$daftar]."', `luasareaproduktif`='".$luasareaproduktif1[$daftar]."',
                `luasareanonproduktif`='".$luasareanonproduktif1[$daftar]."',`jumlahpokok`='".$jumlahpokok1[$daftar]."', `statusblok`='".$statusblok[$daftar]."',
                `tahunmulaipanen`='".$tahunmulaipanen[$daftar]."', `bulanmulaipanen`='".$bulanmulaipanen[$daftar]."', 
            `kodetanah`='".$kodetanah[$daftar]."', `klasifikasitanah`='".$klasifikasitanah[$daftar]."', `topografi`='".$topografi[$daftar]."', `jenisbibit`='".$tanggalpengakuan[$daftar]."', 
            `tanggaltransaksi`='".$currTahun."-".$currBulan."-".cal_days_in_month(CAL_GREGORIAN,$currBulan,$currTahun)."', `tanggalpengakuan`='".$tanggalpengakuan[$daftar]."', 
            `intiplasma`='".$intiplasma[$daftar]."', `basiskg`='".$basiskg[$daftar]."',`periodetm`='".$periodetm[$daftar]."', `cadangan`='".$cadangan[$daftar]."',
            `okupasi`='".$okupasi[$daftar]."', `rendahan`='".$rendahan[$daftar]."', `sungai`='".$sungai[$daftar]."', 
            `rumah`='".$rumah[$daftar]."', `kantor`='".$kantor[$daftar]."', `pabrik`='".$pabrik[$daftar]."',
            `jalan`='".$jalan[$daftar]."', `kolam`='".$kolam[$daftar]."', `umum`='".$umum[$daftar]."', `lc`='".$lc[$daftar]."' 
            where `kodeorg`='".$daftar."' and `tahun`='".$pNow."' ";
            try{
				$owlPDO->exec($updt); 
			}catch (PDOException $e){
				echo "DB Error : Silakan hubungi IT.\n".$e->getMessage();	 
                exit();
			}
			

		}
    }
	break;

case'ShowData':
    function baliktanggal($lama){
        $qwe=explode('-',$lama);
        $balikin=$qwe[2]."-".$qwe[1]."-".$qwe[0];
        return $balikin;
    }
    echo"<link rel=stylesheet type=text/css href=\"style/zTable.css\">";
    echo"<fieldset><legend>".$_SESSION['lang']['Mutasi1']."+ ".$periode."</legend><table cellspacing=1 border=0>
    <thead>
    <tr>
    <td align=center>".$_SESSION['lang']['notransaksi']."</td>
    <td align=center>".$_SESSION['lang']['tanggal']."</td>
    <td align=center>".$_SESSION['lang']['namakegiatan']."</td>
    <td align=center>".$_SESSION['lang']['jumlah']."</td>
    </tr>
    </thead>
    <tbody>";
    
    // kamus kode kegiatan
    $sCek="select * from ".$dbname.".setup_kegiatan
        where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'TN')";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    while($rCek=$qCek->fetch())
    {
        $kamuskegiatan[$rCek['kodekegiatan']]=$rCek['namakegiatan'];
    }
                    
    // ambil data mutasi tambah
    $sCek="select * from ".$dbname.".kebun_perawatan_vw 
        where kodeorg like '".$kdOrg."%' and tanggal like '".$periode."%'
        and jurnal = '1' and kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'TN')
        order by tanggal, notransaksi";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
	$totaltambah=0;
    while($rCek=$qCek->fetch())
    {
        echo"<tr class=rowcontent>";
        echo"<td>".$rCek['notransaksi']."</td>";
        echo"<td>".baliktanggal($rCek['tanggal'])."</td>";
        echo"<td>".$kamuskegiatan[$rCek['kodekegiatan']]."</td>";
        echo"<td align=right>".number_format($rCek['hasilkerja'])."</td>";
        echo"</tr>";
        $totaltambah+=$rCek['hasilkerja'];    
    }
    echo"<tr class=rowcontent>";
    echo"<td colspan=3>".$_SESSION['lang']['total']."+</td>";
    echo"<td align=right>".number_format($totaltambah)."</td>";
    echo"</tr>";
                    
    echo"</tbody></table></fieldset>";    
    
    echo"<fieldset><legend>".$_SESSION['lang']['Mutasi1']."- ".$periode."</legend><table cellspacing=1 border=0>
    <thead>
    <tr>
    <td align=center>".$_SESSION['lang']['periode']."</td>
    <td align=center>".$_SESSION['lang']['rencanasisip']."</td>
    <td align=center>".$_SESSION['lang']['keterangan']."</td>
    <td align=center>".$_SESSION['lang']['pokokmati']."</td>
    </tr>
    </thead>
    <tbody>";
    
    // ambil data mutasi kurang
    $sCek="select * from ".$dbname.".kebun_rencanasisip
        where blok like '".$kdOrg."%' and periode like '".$periode."%'
        and posting = '1'
        order by periode";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
    $totalkurang=0;
    while($rCek=$qCek->fetch())
    {
        echo"<tr class=rowcontent>";
        echo"<td>".$rCek['periode']."</td>";
        echo"<td align=right>".number_format($rCek['rencanasisip'])."</td>";
        echo"<td>".$rCek['keterangan']."</td>";
        echo"<td align=right>".number_format($rCek['pokokmati'])."</td>";
        echo"</tr>";
        $totalkurang+=$rCek['pokokmati'];    
    }
    echo"<tr class=rowcontent>";
    echo"<td colspan=3>".$_SESSION['lang']['total']."-</td>";
    echo"<td align=right>".number_format($totalkurang)."</td>";
    echo"</tr>";
                    
    echo"</tbody></table></fieldset>";        
	break;

default:
	break;
}
?>