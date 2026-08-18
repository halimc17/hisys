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

if($kdOrg==''){
	exit("Error: Kebun wajib di pilih");
}

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
case'getPrd':
		$optPerList="";
		$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='".$kdOrg."' and tutupbuku='0'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optPerList.="<option value='" . $bar['periode'] . "'>" . $bar['periode'] . "</option>";
		}
		
	echo $optPerList;
break;
	
//load data
case'getData':
    // echo"<fieldset>
    // <legend>".$_SESSION['lang']['list']." ".$kdOrg."</legend>";
    /*if($_SESSION['language']=='EN'){
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
    }*/
    echo"<table cellspacing=1 cellpadding=5 border=0 class=sortable>
    <thead>
    <tr>
    <th align=center rowspan=2>No</th>
    <th align=center rowspan=2>".$_SESSION['lang']['namaorganisasi']."</th>
    <th align=center rowspan=2>".$_SESSION['lang']['kodeorg']."</th>
    <th align=center rowspan=2>".$_SESSION['lang']['tahuntanam']."</th>
    
    <th align=center colspan=4>".$_SESSION['lang']['periode']." ".$periodeBefore."</th>
    <th align=center colspan=4>".$_SESSION['lang']['periode']." ".$periode."</th>
    </tr>
    <tr>
    <th align=center>".$_SESSION['lang']['luasareaproduktif']."</th>
    <th align=center>".$_SESSION['lang']['luasareanonproduktif']."</th>
    <th align=center>".$_SESSION['lang']['pokok']."</th>
    <th align=center>".$_SESSION['lang']['statusblok']."</th>
    <th align=center>".$_SESSION['lang']['luasareaproduktif']."</th>
    <th align=center>".$_SESSION['lang']['luasareanonproduktif']."</th>
    <th align=center>".$_SESSION['lang']['pokok']."</th>
    <th align=center>".$_SESSION['lang']['statusblok']."</th>
    </tr>
    </thead>

    <tbody>";
	$jumlahpokokcek=$jumlahpokoklalu=array();
    $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='BLOK' and kodeorganisasi like '".$kdOrg."%' ");
	// ambil data periode lalu, kalo ada data tahun lalu, timpa yang pool data yang dari setup_blok
    $sCek="select * from ".$dbname.".setup_blok_tahunan where kodeorg like '".$kdOrg."%' and tahun = '".$pBef."' 
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
        $luasareaproduktiflalu[$rCek['kodeorg']]=$rCek['luasareaproduktif'];    
        $luasareanonproduktiflalu[$rCek['kodeorg']]=$rCek['luasareanonproduktif'];    
    }
	
    // ambil data periode lalu, untuk antisipasi blok baru ambil dari setup_blok
    $sCek="select * from ".$dbname.".setup_blok where kodeorg like '".$kdOrg."%'
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

   $no='0';
    if(!empty($listkodeorg))foreach($listkodeorg as $daftar){
		setIt($luasareaproduktif[$daftar],0);
		setIt($luasareanonproduktif[$daftar],0);
		setIt($jumlahpokokcek[$daftar],0);
		setIt($jumlahpokoklalu[$daftar],0);
        if($jumlahpokokcek[$daftar]!=$jumlahpokoklalu[$daftar]){
            $warna=" bgcolor=pink";
        }else{
            $warna="";
        }
		$no+=1;
        echo"<tr class=rowcontent>
        <td align=center>".$no."</td>
        <td align=center>".$nmorg[$daftar]."</td>
        <td align=center>".$daftar."</td>
        <td align=center>".$tahuntanam[$daftar]."</td>
        
        <td align=right>".number_format($luasareaproduktiflalu[$daftar],2)."</td>
        <td align=right>".number_format($luasareanonproduktiflalu[$daftar],2)."</td>
        <td align=right>".number_format($jumlahpokoklalu[$daftar])."</td>
        <td align=center>".$statusblok[$daftar]."</td>        
        <td align=right".$warna." >".number_format($luasareaproduktif[$daftar],2)."</td>
        <td align=right".$warna." >".number_format($luasareanonproduktif[$daftar],2)."</td>
        <td align=right".$warna." >".number_format($jumlahpokok[$daftar])."</td>
        <td align=center>".$statusblok[$daftar]."</td>
        </tr>";    
    }
    echo"</tbody>
    <table>";
	echo"</br><button class=mybutton id='process' onclick='processData()'>".$_SESSION['lang']['proses']."</button>";        
    // echo"</fieldset>";
break;

case'processData':    
	
	$sUpd="DELETE FROM ".$dbname.".setup_blok_tahunan WHERE `kodeorg` like '".$kdOrg."%' and tahun = '".$pNow."'";
	try{$owlPDO->exec($sUpd); }catch (PDOException $e){echo "DB Error : ".$e->getMessage();exit();}
	
	$str = "select * from ".$dbname.".setup_blok where kodeorg like '".$kdOrg."%'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi = '".$bar['kodeorg']."' ");
		$sql="insert into ".$dbname.".setup_blok_tahunan 
		(`namaorg`,
		`kodeorg`,
		`indukblok`,
		`tahun`,
		`tahuntanam`,
		`kelaspohon`,
		`buahkecil`,
		`luasareaproduktif`,
		`luasareanonproduktif`,
		`jumlahpokok`,
		`statusblok`,
		`tahunmulaipanen`,
		`bulanmulaipanen`,
		`kodetanah`,
		`klasifikasitanah`,
		`topografi`,
		`jenisbibit`,
		`tanggaltransaksi`,
		`tanggalpengakuan`,
		`intiplasma`,
		`basiskg`,
		`periodetm`,
		`cadangan`,
		`arealberbatu`,
		`konservasi`,
		`enclave`,
		`okupasi`,
		`rendahan`,
		`sungai`,
		`rumah`,
		`kantor`,
		`pabrik`,
		`jalan`,
		`kolam`,
		`umum`,
		`lc`,
        `luasbloking`,
		`status`) 
		value(
		'".$nmorg[$bar['kodeorg']]."',
		'".$bar['kodeorg']."',
		'".$bar['indukblok']."',
		'".$pNow."',
		'".$bar['tahuntanam']."',
		'".$bar['kelaspohon']."',
		'".$bar['buahkecil']."',
		'".$bar['luasareaproduktif']."',
		'".$bar['luasareanonproduktif']."',
		'".$bar['jumlahpokok']."',
		'".$bar['statusblok']."',
		'".$bar['tahunmulaipanen']."',
		'".$bar['bulanmulaipanen']."',
		'".$bar['kodetanah']."',
		'".$bar['klasifikasitanah']."',
		'".$bar['topografi']."',
		'".$bar['jenisbibit']."',
		'".$bar['tanggaltransaksi']."',
		'".$bar['tanggalpengakuan']."',
		'".$bar['intiplasma']."',
		'".$bar['basiskg']."',
		'".$bar['periodetm']."',
		'".$bar['cadangan']."',
		'".$bar['arealberbatu']."',
		'".$bar['konservasi']."',
		'".$bar['enclave']."',
		'".$bar['okupasi']."',
		'".$bar['rendahan']."',
		'".$bar['sungai']."',
		'".$bar['rumah']."',
		'".$bar['kantor']."',
		'".$bar['pabrik']."',
		'".$bar['jalan']."',
		'".$bar['kolam']."',
		'".$bar['umum']."',
		'".$bar['lc']."',
		'".$bar['luasbloking']."',
		'".$bar['status']."'
		)";
		
		try {$owlPDO->exec($sql);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	}
	/* 
    $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='BLOK' and kodeorganisasi like '".$kdOrg."%' ");
    // ambil data periode lalu, kalo ada data tahun lalu, timpa pool data dari setup_blok
    $sCek="select * from ".$dbname.".setup_blok_tahunan where kodeorg like '".$kdOrg."%' and tahun = '".$pBef."' 
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
	
	// ambil data periode lalu, untuk antisipasi blok baru ambil dari setup_blok
    $sCek="select * from ".$dbname.".setup_blok where kodeorg like '".$kdOrg."%'
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

        // inject ke tahun ini
        $sIns="INSERT INTO ".$dbname.".setup_blok_tahunan (`namaorg`,`kodeorg`, `tahun`, `tahuntanam`, `kelaspohon`, `luasareaproduktif`, `luasareanonproduktif`, 
            `jumlahpokok`, `statusblok`, `tahunmulaipanen`, `bulanmulaipanen`, 
            `kodetanah`, `klasifikasitanah`, `topografi`, `jenisbibit`, 
            `tanggaltransaksi`, `tanggalpengakuan`, `intiplasma`, `basiskg`, 
            `periodetm`, `cadangan`, `okupasi`, `rendahan`, `sungai`, 
            `rumah`, `kantor`, `pabrik`, `jalan`, `kolam`, `umum`, `lc`) 
            VALUES ('".$nmorg[$daftar]."','".$daftar."', '".$pNow."', '".$tahuntanam[$daftar]."', '".$kelaspohon[$daftar]."', '".$luasareaproduktif1[$daftar]."', '".$luasareanonproduktif1[$daftar]."', 
                '".$jumlahpokok1[$daftar]."', '".$statusblok[$daftar]."', '".$tahunmulaipanen[$daftar]."', '".$bulanmulaipanen[$daftar]."', 
                '".$kodetanah[$daftar]."', '".$klasifikasitanah[$daftar]."', '".$topografi[$daftar]."', '".$jenisbibit[$daftar]."', 
                '".$currTahun."-".$currBulan."-".cal_days_in_month(CAL_GREGORIAN,$currBulan,$currTahun)."', '".$tanggalpengakuan[$daftar]."', '".$intiplasma[$daftar]."', '".$basiskg[$daftar]."', 
                '".$periodetm[$daftar]."', '".$cadangan[$daftar]."', '".$okupasi[$daftar]."', '".$rendahan[$daftar]."', '".$sungai[$daftar]."', 
                '".$rumah[$daftar]."', '".$kantor[$daftar]."', '".$pabrik[$daftar]."', '".$jalan[$daftar]."', '".$kolam[$daftar]."', '".$umum[$daftar]."', '".$lc[$daftar]."')";
		try{
			$owlPDO->exec($sIns);
			
		}catch (PDOException $e){
			//updatenya disinni
            $updt="update ".$dbname.".setup_blok_tahunan set `namaorg`='".$nmorg[$daftar]."',`kodeorg`='".$daftar."', `tahun`='".$pNow."',
                `tahuntanam`='".$tahuntanam[$daftar]."', `kelaspohon`='".$kelaspohon[$daftar]."', `luasareaproduktif`='".$luasareaproduktif1[$daftar]."',
                `luasareanonproduktif`='".$luasareanonproduktif1[$daftar]."',`jumlahpokok`='".$jumlahpokok1[$daftar]."', `statusblok`='".$statusblok[$daftar]."',
                `tahunmulaipanen`='".$tahunmulaipanen[$daftar]."', `bulanmulaipanen`='".$bulanmulaipanen[$daftar]."', 
            `kodetanah`='".$kodetanah[$daftar]."', `klasifikasitanah`='".$klasifikasitanah[$daftar]."', `topografi`='".$topografi[$daftar]."', `jenisbibit`='".$jenisbibit[$daftar]."', 
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
    } */
	break;

}
?>