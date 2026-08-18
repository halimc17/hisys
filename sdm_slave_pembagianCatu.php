<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

//periksa apakah sudah ada
$str = "select posting from " . $dbname . ".sdm_catu where kodeorg='" . $_POST['kodeorg'] . "' 
        and periodegaji='" . $_POST['periode'] . "' and posting=1 order by posting desc 
        limit 1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    if ($bar->posting == 1)
        $stat = '1';
    else
        $stat = '';
}

if (!empty($stat)) {
    exit($stat);
}
switch ($_POST['aksi']) {

    case 'display':
        display($_POST['kodeorg'], $_POST['periode'], $_POST['harga'], $dbname, $owlPDO,$_POST['tkar']);
        break;
    case 'simpan':
        display($_POST['kodeorg'], $_POST['periode'], $_POST['harga'], $dbname, $owlPDO,$_POST['tkar']);
        break;
    case 'replace':
        display($_POST['kodeorg'], $_POST['periode'], $_POST['harga'], $dbname, $owlPDO,$_POST['tkar']);
        break;
    case 'posting':
        posting($_POST['kodeorg'], $_POST['periode'], $_POST['jumlah'], $dbname, $owlPDO,$_POST['tkar']);
        break;
		
	case'getrp':
		$harga=0;
		$str = "select harga from " . $dbname . ".sdm_5periodegaji where (harga!='' or harga!=0) 
				and kodeorg='" . $_POST['kodeorg'] . "' and periode='".$_POST['periode']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$bar = $res->fetch();
			 $harga=$bar->harga;
			 echo $harga;
		break;
		
}

function display($kodeorg, $periode, $harga, $dbname, $owlPDO,$tipekar) {
	$where='';	
	$kept= makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
	$kodept=$kept[$kodeorg];	
	$where.=" and tipekaryawan='".$tipekar."'";
	
	
    $tgl1 = '';
    $tgl2 = '';
    $str = "select tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $kodeorg . "'
           and periode='" . $periode . "' and jenisgaji='H'  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $tgl1 = str_replace("-", "", $bar->tanggalmulai);
        $tgl2 = str_replace("-", "", $bar->tanggalsampai);
    }

    if ($tgl1 == '' or $tgl2 == '') {
        exit(" Error: Periode penggajian Harian tidak ditemukan / Daily base payrol period not found");
    } else {
        //ambil kamus karyawan
        $str = "select a.nik,a.karyawanid,a.namakaryawan,a.kodecatu,a.subbagian,b.tipe,c.keterangan,a.tipekaryawan,a.kodejabatan,d.namajabatan,a.tanggalkeluar
                  from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5tipekaryawan b on a.tipekaryawan=b.id                  
                  left join " . $dbname . ".sdm_5catuporsi c on a.kodecatu=c.kode
                  left join " . $dbname . ".sdm_5jabatan d on a.kodejabatan=d.kodejabatan
                  where a.lokasitugas='" . $kodeorg . "'  ".$where." and (a.tanggalkeluar>='" . $_POST['periode'] . "-01' or a.tanggalkeluar='0000-00-00')";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        $kamusKar = Array();
        while ($bar = $res->fetch()) {
			$kamusKar[$bar->karyawanid]['id'] = $bar->karyawanid;
			$kamusKar[$bar->karyawanid]['nik'] = $bar->nik;
			$kamusKar[$bar->karyawanid]['nama'] = $bar->namakaryawan;
			$kamusKar[$bar->karyawanid]['kodecatu'] = $bar->kodecatu;
			$kamusKar[$bar->karyawanid]['subbagian'] = $bar->subbagian;
			$kamusKar[$bar->karyawanid]['tipekaryawan'] = $bar->tipekaryawan;
			$kamusKar[$bar->karyawanid]['namatipe'] = $bar->tipe;
			$kamusKar[$bar->karyawanid]['kelompok'] = $bar->keterangan;
			$kamusKar[$bar->karyawanid]['kode'] = $bar->kodecatu;
			$kamusKar[$bar->karyawanid]['jabatan'] = $bar->namajabatan;
			if(substr($bar->tanggalkeluar,0,7)==$_POST['periode']){
				$jmlhhari=substr($bar->tanggalkeluar,8,2);
				$kamusKar[$bar->karyawanid]['tanggalkeluar'] = $jmlhhari;
			}else{
				$kamusKar[$bar->karyawanid]['tanggalkeluar'] = '';
			}
			
        }
    }
	
	// echo"<pre>";
	// print_r($kamusKar);
	// echo"</pre>";
	
    //ambil subbagian untuk pengurutan perafdeling
    $str = "select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $kodeorg . "' order by kodeorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    $subbagian = Array();
    while ($bar = $res->fetch()) {
        array_push($subbagian, $bar->kodeorganisasi);
    }

	
	//ambil dari absensi.
	/*
    $sAbsn = "select absensi,tanggal,karyawanid,tipekaryawan,lokasitugas from " . $dbname . ".sdm_absensidt_vw 
                        where tanggal between  '" . $tgl1 . "' and '" . $tgl2 . "' and lokasitugas like '" . $kodeorg . "%' and nilaihk>0";
	*/	

	#bentuk absen penambah atau pengurang
	$str="select * from ".$dbname.".sdm_5absensi";			
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$kompengurang[$bar->kodeabsen]=$bar->kelompok;
		
	}
	$arrTambahan=array();#kelompok=1 tapi memotong catu
    $tanggalminggu=array();
    #ambil hari
	#sekarang bentuk pengurang saja untuk absensi
	#karna di DAW perhitungan catu pasti 30 hari
	// $str="select * from ".$dbname.".sdm_absensidt_vw where lokasitugas in 
	// (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
	$str="select * from ".$dbname.".sdm_absensidt_vw where lokasitugas='".$_SESSION['empl']['lokasitugas']."'
	and tanggal between '".$tgl1."' and '".$tgl2."'  order by tanggal asc";					
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    $kehadiran = Array();
    while ($bar = $res->fetch()) {
        // $tgl = str_replace("-", "", $bar->tanggal);   
		// if($bar->tipekaryawan=='1'){
			// $kehadiran[$bar->karyawanid][$tgl]='H';
		// }else { //if ($bar->tipekaryawan=='3'){
			// if($bar->absensi=='MG' || $bar->absensi=='H' || $bar->absensi=='HL'){
				// $kehadiran[$bar->karyawanid][$tgl]='H';
			// }
		// }
		$batasatas=intval(substr($bar->tanggal,-2,2));
	    $batasbawah=intval(substr($tgl2,-2,2));
		//if(@$kompengurang[$bar->absensi]==0){
		#= trap hanya pakai yang absennya M
		if(@$kompengurang[$bar->absensi]==0 and $bar->absensi=='M'){
			@$penguranghk[$bar->karyawanid]+=1;
            $karyawanmangkir[$bar->karyawanid]=$bar->karyawanid;
			
			
            for($tglawal=$batasatas;$tglawal<=$batasbawah;$tglawal++){
                if($tglawal<10){
                    $strTgl=$periode."-0".$tglawal;
                }else{
                    $strTgl=$periode."-".$tglawal;
                }
				
				if($bar->tanggalkeluar!='0000-00-00'){
					if($strTgl>$bar->tanggalkeluar){
						continue;
					}
				}
				
                $hariapa=date('D', strtotime($strTgl));//cari hari minggu
                if($hariapa=='Sun'){
                    $tanggalminggu[$bar->karyawanid][$strTgl]=$strTgl;
                    continue;
                }
            }
			
			
		}else{
			// if(count($arrTambahan[$bar->absensi])==1){
			// 	@$penguranghk[$bar->karyawanid]+=1;
   //          	$karyawanmangkir[$bar->karyawanid]=$bar->karyawanid;
	  //           for($tglawal=$batasatas;$tglawal<=$batasbawah;$tglawal++){
	  //               if($tglawal<10){
	  //                   $strTgl=$periode."-0".$tglawal;
	  //               }else{
	  //                   $strTgl=$periode."-".$tglawal;
	  //               }
	  //               $hariapa=date('D', strtotime($strTgl));//cari hari minggu
	  //               if($hariapa=='Sun'){
	  //                   $tanggalminggu[$bar->karyawanid][$strTgl]=$strTgl;
	  //                   continue;
	  //               }
	  //           }
			// }
		}
    }

    if (count($karyawanmangkir)>0) {
	    foreach ($karyawanmangkir as $listkaryawanmangkir) {
	    	// echo count($tanggalminggu['0000000703']);
	        @$jumlah[$listkaryawanmangkir]=count($tanggalminggu[$listkaryawanmangkir]);
	        // @$penguranghk[$listkaryawanmangkir]+=$jumlah[$listkaryawanmangkir];
	    }
    }
    
	
	/*
	echo"<pre>";
	print_r($penguranghk);
	echo"</pre>";	
	*/

	
	
	
	
	
	// echo"<pre>";

	// print_r($penguranghk);
		// echo"</pre>";


    //ambil dari perawatan
	/*
    $sKehadiran = "select absensi,tanggal,karyawanid from " . $dbname . ".kebun_kehadiran_vw 
                            where tanggal between  '" . $tgl1 . "' and '" . $tgl2 . "' and unit='" . $kodeorg . "' and jhk!=0 ";
	*/
	$str="select a.*,b.subbagian from ".$dbname.".kebun_kehadiran_vw a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where unit in 
		(select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."') 
		and tanggal between '".$tgl1."' and '".$tgl2."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $tgl = str_replace("-", "", $bar->tanggal);
        $kehadiran[$bar->karyawanid][$tgl] = $bar->absensi;
    }
    //ambil Panen
	/*
    $sPrestasi = "select b.tanggal,a.jumlahhk,a.nik from " . $dbname . ".kebun_prestasi a 
					left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
					where b.notransaksi like '%PNN%' and substr(b.kodeorg,1,4)='" . $kodeorg . "' 
					and b.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'";
	*/	
	$str="select * from ".$dbname.".kebun_prestasi_vs_hk  where unit in 
	(select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
	and tanggal between '".$tgl1."' and '".$tgl2."'  ";	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $tgl = str_replace("-", "", $bar->tanggal);
        $kehadiran[$bar->karyawanid][$tgl] = 'H';
    }

	
	
	
	
	
	
	/*
	
	
    // ambil pengawas                        
    $dzstr = "SELECT tanggal,nikmandor FROM " . $dbname . ".kebun_aktifitas a
            left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join " . $dbname . ".datakaryawan c on a.nikmandor=c.karyawanid
            where a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and b.kodeorg like '" . $kodeorg . "%' and c.namakaryawan is not NULL
            union select tanggal,nikmandor1 FROM " . $dbname . ".kebun_aktifitas a 
            left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join " . $dbname . ".datakaryawan c on a.nikmandor1=c.karyawanid
            where a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and b.kodeorg like '" . $kodeorg . "%' and c.namakaryawan is not NULL";
	$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
	$dzres->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $dzres->fetch()) {
        $tgl = str_replace("-", "", $bar->tanggal);
        $kehadiran[$bar->nikmandor][$tgl] = 'H';
    }

    // ambil administrasi                       
    $dzstr = "SELECT tanggal,nikasisten as nikmandor FROM " . $dbname . ".kebun_aktifitas a
            left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join " . $dbname . ".datakaryawan c on a.nikasisten=c.karyawanid
            where a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and b.kodeorg like '" . $kodeorg . "%' and c.namakaryawan is not NULL
            union select tanggal,keranimuat FROM " . $dbname . ".kebun_aktifitas a 
            left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join " . $dbname . ".datakaryawan c on a.keranimuat=c.karyawanid
            where a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and b.kodeorg like '" . $kodeorg . "%' and c.namakaryawan is not NULL";
	$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
	$dzres->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $dzres->fetch()) {
        $tgl = str_replace("-", "", $bar->tanggal);
        $kehadiran[$bar->nikmandor][$tgl] = 'H';
    }
	*/
	
	#bentuk absen  mandor
	$str="select a.nikmandor,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
			left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid where 
			kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."') 
			and tanggal between '".$tgl1."' and '".$tgl2."' and nikmandor!=''  ";
		
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$tgl = str_replace("-", "", $bar['tanggal']);
        $kehadiran[$bar['nikmandor']][$tgl] = 'H';
	}
	
	#mandor 1
	$str="select a.nikmandor1,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
			left join ".$dbname.".datakaryawan b on a.nikmandor1=b.karyawanid where 
			kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
			and tanggal between '".$tgl1."' and '".$tgl2."' and nikmandor1!=''  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$tgl = str_replace("-", "", $bar['tanggal']);
        $kehadiran[$bar['nikmandor1']][$tgl] = 'H';
	}


	//krani
	$str="select a.keranimuat,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
			left join ".$dbname.".datakaryawan b on a.keranimuat=b.karyawanid where 
			kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
			and tanggal between '".$tgl1."' and '".$tgl2."' and keranimuat!=''  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$tgl = str_replace("-", "", $bar['tanggal']);
        $kehadiran[$bar['keranimuat']][$tgl] = 'H';
	}


	//krani panen
	$str="select a.nikasisten,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
			left join ".$dbname.".datakaryawan b on a.nikasisten=b.karyawanid where 
			kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')
			and tanggal between '".$tgl1."' and '".$tgl2."' and nikasisten!=''  ";
			//echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$tgl = str_replace("-", "", $bar['tanggal']);
        $kehadiran[$bar['nikasisten']][$tgl] = 'H';
	}
	
	
	
    // ambil dari traksi
	/*
    $dzstr = "SELECT tanggal,idkaryawan FROM " . $dbname . ".vhc_runhk
            where tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and notransaksi like '" . $kodeorg . "%'";
	*/
	$str="select a.*,b.lokasitugas,b.subbagian,tipekaryawan from ".$dbname.".vhc_runhk_vw a left join ".$dbname.".datakaryawan b
		on a.idkaryawan=b.karyawanid where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')  
		and tanggal between '".$tgl1."' and '".$tgl2."'  ";
    $dzres=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$dzres->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $dzres->fetch()) {
        $tgl = str_replace("-", "", $bar->tanggal);
        $kehadiran[$bar->idkaryawan][$tgl] = 'H';
    }

    //buang hari minggu 
	/*
    $hari = dates_inbetween($tgl1, $tgl2);
    foreach ($hari as $ar => $isi) {
        $qwe = date('D', strtotime($isi));
        $tglini = date('Ymd', strtotime($isi));
        if ($qwe == 'Sun') {
			
            // foreach ($kehadiran as $key => $val) {
                // $sCek = "select distinct catu from " . $dbname . ".sdm_absensidt 
                                   // where karyawanid='" . $key . "' and tanggal='" . $tglini . "'";
				// $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
				// $qCek->setFetchMode(PDO::FETCH_ASSOC);
                // $rCek = $qCek->fetch();
                // if ($rCek['catu'] == 0) {
                    // unset($kehadiran[$key][$tglini]);
                // }
            // }
			
			foreach ($kehadiran as $key => $val) {
				unset($kehadiran[$key][$tglini]);
			}
        }
    }
	*/

	


    //jumlahkan hk masing-masing orang
    $jumlahHK = Array();
    foreach ($kehadiran as $key => $val) {
        $jumlahHK[$key] = count($kehadiran[$key]);
    }
    //ambil jumlah porsi catu masing-masing orang
    $str = "select kelompok, jumlah as porsi from " . $dbname . ".sdm_5catu where kodeorg='" . $kodeorg . "' and tahun=" . substr($periode, 0, 4);
    $porsi = Array();
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
    if ($numrows == 0) {
        if ($_SESSION['language'] == 'ID') {
            exit("Error:Setup->Natura untuk tahun " . substr($periode, 0, 4) . " belum ada, silahkan isi terlebih dahulu");
        } else {
            exit("Error:Setup->Natura for year " . substr($periode, 0, 4) . " not defined, please define first");
        }
    }
    while ($bar = $res->fetch()) {
        $porsi[$bar->kelompok] = $bar->porsi;
    }

	
		
	
    //bentuk rupiah catu masing-masing orang
    $rupiahCatu = Array();
    foreach ($jumlahHK as $key => $val) {
        setIt($jumlahHK[$key], 0);
        setIt($kamusKar[$key]['kode'], '');
        setIt($porsi[$kamusKar[$key]['kode']], 0);
		
		$rupiahCatu[$key] = $jumlahHK[$key] * ($porsi[$kamusKar[$key]['kode']] * $harga);
		
    }
	
	

    if ($_POST['aksi'] == 'display') {
        //print	
        echo"<button class=mybutton onclick=simpanCatu()>" . $_SESSION['lang']['save'] . "</button>
                    <table class=sortable border=0 cellspacing=1>
                    <thead>
                    <tr class=rowheader>
                    <td align=center>No.</td>
                    <td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
                    <td align=center>" . $_SESSION['lang']['subbagian'] . "</td>
                    <td align=center>" . $_SESSION['lang']['periode'] . "</td>
                    <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
                    <td align=center>" . $_SESSION['lang']['tipe'] . "</td>
                    <td align=center>" . $_SESSION['lang']['jabatan'] . "</td>
                    <td align=center>" . $_SESSION['lang']['status'] . "</td>
                    <td align=center>Kg/Hk</td>
                    <td align=center>" . $_SESSION['lang']['jumlah'] . " HK</td>
                    <td align=center>" . $_SESSION['lang']['hargasatuan'] . "</td>
                    <td align=center>" . $_SESSION['lang']['total'] . " (Rp)</td>
                    </tr>
                    </thead>
                    <tbody>";
        $no = 0;
        $ttl = 0;
        foreach($kamusKar as $key =>$val){
            if(!isset($val['nama'])){
                unset($kamusKar[$key]);
            }
        }      
        foreach ($subbagian as $unit => $sub) {
            $SUBTTL = 0;
                $masuk=0;            
            foreach ($kamusKar as $key => $val) {
                setIt($kamusKar[$key]['subbagian'], '');

                if ($kamusKar[$key]['subbagian'] == $sub) {
                    $masuk++;
                    $no+=1;
                    setIt($jumlahHK[$key], 0);
                    setIt($rupiahCatu[$key], 0);
                    setIt($rupiahCatu[$key], 0);
                    setIt($porsi[$kamusKar[$key]['kode']], 0);
					
					#bentuk angka baru
					$exptahun = explode('-',$periode);
					$mytahun = $exptahun[0];
					$mybulan = $exptahun[1];
					
					// $jumlahHK[$key]=30;
					
					#= jika ada karyawan keluar tengah bulan maka ganti jumlahHK saja
					if($kamusKar[$key]['tanggalkeluar']!=''){
						$jumlahHK[$key] = $kamusKar[$key]['tanggalkeluar'];
					}else{
						$jumlahHK[$key] = jumlah_hari($mybulan,$mytahun);
					}
					
					$hkreal[$key]=$jumlahHK[$key]-$penguranghk[$key];
					@$rupiahCatu[$key]=($jumlahHK[$key]-$penguranghk[$key])*$porsi[$kamusKar[$key]['kode']]*$harga;
                    echo "<tr class=rowcontent>
                                <td align=center>" . $no . "</td>
                                <td>" . $kodeorg . "</td>
                                <td>" . $kamusKar[$key]['subbagian'] . "</td>
                                <td>" . $periode . "</td>
                                <td>" . $kamusKar[$key]['id']."-". $kamusKar[$key]['nama'] . "</td>
                                <td>" . $kamusKar[$key]['namatipe'] . "</td>
                                <td>" . $kamusKar[$key]['jabatan'] . "</td>
                                <td align=center>" . $kamusKar[$key]['kode'] . "</td>
                                <td align=center>" . number_format($porsi[$kamusKar[$key]['kode']], 2, '.', ',') . "</td>
                                <td align=right>" . number_format($hkreal[$key], 0, '.', ',') . "</td>
                                <td align=right>" . number_format($harga, 0, '.', ',') . "</td>     
                                <td align=right>" . number_format($rupiahCatu[$key], 0, '.', ',') . "</td></tr>     
                                ";
                    $ttl+=$rupiahCatu[$key];
                    $SUBTTL+=$rupiahCatu[$key];
                }
            }
            if($masuk>0){
            //print subtotal per afdeling    
                echo "<tr class=rowcontent>
                            <td colspan=11>Sub Total " . $sub . "</td>     
                            <td align=right>" . number_format($SUBTTL, 0, '.', ',') . "</td></tr>     
                            ";
            }                
        }
        //khusus karyawan kantor
        $SUBTTL = 0;
        foreach ($kamusKar as $key => $val) {
            if ($kamusKar[$key]['subbagian'] == '' or $kamusKar[$key]['subbagian'] == '0') {
                $no+=1;
                setIt($kamusKar[$key]['nama'], '');
                setIt($kamusKar[$key]['namatipe'], '');
                setIt($kamusKar[$key]['jabatan'], '');
                setIt($kamusKar[$key]['kode'], '');
				
				// $jumlahHK[$key]=30;
				#bentuk angka baru
				$exptahun = explode('-',$periode);
				$mytahun = $exptahun[0];
				$mybulan = $exptahun[1];
				
				if($kamusKar[$key]['tanggalkeluar']!=''){
					$jumlahHK[$key] = $kamusKar[$key]['tanggalkeluar'];
				}else{
					$jumlahHK[$key] = jumlah_hari($mybulan,$mytahun);
				}
				
				// $jumlahHK[$key] = jumlah_hari($mybulan,$mytahun);
				$hkreal[$key]=$jumlahHK[$key]-$penguranghk[$key];
				@$rupiahCatu[$key]=($jumlahHK[$key]-$penguranghk[$key])*$porsi[$kamusKar[$key]['kode']]*$harga;
                echo "<tr class=rowcontent>
					<td align=center>" . $no . "</td>
					<td>" . $kodeorg . "</td>
					<td>" . $kamusKar[$key]['subbagian'] . "</td>
					<td>" . $periode . "</td>
					<td>" . $kamusKar[$key]['id']."-". $kamusKar[$key]['nama'] . "</td>
					<td>" . $kamusKar[$key]['namatipe'] . "</td>
					<td>" . $kamusKar[$key]['jabatan'] . "</td>
					<td align=center>" . $kamusKar[$key]['kode'] . "</td>
					<td align=center>" . number_format(@$porsi[$kamusKar[$key]['kode']], 2, '.', ',') . "</td>
					<td align=right>" . number_format(@$hkreal[$key], 0, '.', ',') . "</td>
					<td align=right>" . number_format($harga, 0, '.', ',') . "</td>     
					<td align=right>" . number_format(@$rupiahCatu[$key], 0, '.', ',') . "</td></tr>";
                @$ttl+=$rupiahCatu[$key];
                @$SUBTTL+=$rupiahCatu[$key];
            }
        }
        //print subtotal per afdeling    
        echo "<tr class=rowcontent>
                            <td colspan=11>Sub Total Kantor / Office</td>     
                            <td align=right>" . number_format($SUBTTL, 0, '.', ',') . "</td></tr>";
        echo "<tr class=rowcontent>
                        <td colspan=11><b>TOTAL</b></td>     
                        <td align=right><b>" . number_format($ttl, 0, '.', ',') . "</b></td></tr>     
                        ";
        echo"</tbody>
                    <tfoot>
                    </tfoot>
                    </table>
                    <button class=mybutton onclick=simpanCatu()>" . $_SESSION['lang']['save'] . "</button>";
					
					
					
					
    } else if ($_POST['aksi'] == 'simpan' or $_POST['aksi'] == 'replace') {
        if ($_POST['aksi'] == 'simpan') {
			
		
			$stat='';
            //periksa dulu apakah sudah ada atau sdah posting
            $str = "select posting from " . $dbname . ".sdm_catu where kodeorg='" . $kodeorg . "' 
                            and periodegaji='" . $periode . "' and tipekaryawan='".$tipekar."'  order by posting desc 
                            limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
                if ($bar->posting == '1')
                    $stat = '1';
                elseif ($bar->posting == '0')
                    $stat = '0';
                else
                    $stat = '';
            }

            if ($stat != '') {
                exit($stat);
            }
			//exit("Error:$stat");
        }
		
		
        $ttl = 0;
        $stsimpan = '';
		$no=0;
		// echo"<pre>";
		// print_r($kamusKar);
		// echo"</pre>";
		// exit("Error:a");
        foreach ($kamusKar as $key => $val) {
			//if tipekar beda skip
			if($kamusKar[$key]['tipekaryawan']==$tipekar){
				// $jumlahHK[$key]=30;
				
				#bentuk angka baru
				$exptahun = explode('-',$periode);
				$mytahun = $exptahun[0];
				$mybulan = $exptahun[1];
				// $jumlahHK[$key] = jumlah_hari($mybulan,$mytahun);
				if($kamusKar[$key]['tanggalkeluar']!=''){
					$jumlahHK[$key] = $kamusKar[$key]['tanggalkeluar'];
				}else{
					$jumlahHK[$key] = jumlah_hari($mybulan,$mytahun);
				}
				
				$hkreal[$key]=$jumlahHK[$key]-$penguranghk[$key];
				$rupiahCatu[$key]=($hkreal[$key])*$porsi[$kamusKar[$key]['kode']]*$harga;
				if (@$rupiahCatu[$key] > 0) {
					if ($no == 0) {
						$stsimpan = "              
								insert into " . $dbname . ".sdm_catu(
								kodeorg, 
								subbagian,
								periodegaji, 
								karyawanid, 
								hargacatu, 
								jumlahhk, 
								catuperhk, 
								totalcatu, 
								jumlahrupiah, 
								posting, 
								updateby,
								tipekaryawan)
								values(
								'" . $kodeorg . "',
								'" . $kamusKar[$key]['subbagian'] . "',    
								'" . $periode . "',
								" . $key . ", 
								" . $harga . ",
								" . $hkreal[$key] . ",   
								" . $porsi[$kamusKar[$key]['kode']] . ",
								" . ($hkreal[$key] * $porsi[$kamusKar[$key]['kode']]) . ", 
								" . $rupiahCatu[$key] . ",
									0,
								" . $_SESSION['standard']['userid'] . ",
								'" . $tipekar . "' 							
								)";
					} else {
						$stsimpan.=",(
								'" . $kodeorg . "',
								'" . $kamusKar[$key]['subbagian'] . "',     
								'" . $periode . "',
								" . $key . ", 
								" . $harga . ",
								" . $hkreal[$key] . ",   
								" . $porsi[$kamusKar[$key]['kode']] . ",
								" . ($hkreal[$key] * $porsi[$kamusKar[$key]['kode']]) . ", 
								" . $rupiahCatu[$key] . ",
									0,
								" . $_SESSION['standard']['userid'] . ",'" . $tipekar . "'    
								)";
					}
					$no+=1;
				}
            }
        }
		
		$str = "delete from " . $dbname . ".sdm_catu where kodeorg='" . $kodeorg . "' and periodegaji='" . $periode . "' and tipekaryawan='".$tipekar."'";
	
		try{
			$owlPDO->exec($str); //hapus dulu yang ada
			if($stsimpan!=''){
				try{
					$owlPDO->exec($stsimpan); 
				}catch (PDOException $e){
					echo " Error: " . $e->getMessage() . $stsimpan;
					die();
				}
			}
		}catch (PDOException $e){
			echo " Error: " . $e->getMessage() . $str;
			die();
		}
    }
}

function posting($kodeorg, $periode, $jumlah, $dbname, $owlPDO,$tipekar) {
	
	// Default Segment
    $defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');
    $tgl1 = '';
    $tgl2 = '';
    $str = "select tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $kodeorg . "'
           and periode='" . $periode . "' and jenisgaji='H'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $tgl1 = str_replace("-", "", $bar->tanggalmulai);
        $tgl2 = str_replace("-", "", $bar->tanggalsampai);
    }

    if ($tgl1 == '' or $tgl2 == '') {
        exit(" Warning: Periode penggajian Harian tidak ditemukan/ Daily base payrol period not found");
    }
    //periksa periode akuntansi
    $str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $kodeorg . "' and tutupbuku=0 order by periode desc";
	$res=fetchData($str);
	$periodeAkun=str_replace("-", "", $res[0]['periode']);
    $periodeGajiCatu=str_replace("-","", $periode);
	//$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    if($periodeGajiCatu<$periodeAkun){
        exit(" Warning: Maaf periode gaji lebih kecil dari periode akuntansi");
    }
	// $numrows=owlBaris($res);
 //    if ($numrows == 0) {
 //        exit(" Warning: Maaf periode akuntansi tidak aktif diperiode ini\nSorry, accounting period is not active on chosen period");
 //    }

    //periksa periode penggajian unit untuk memastikan apakah sudah selesai inputan BKM,KKD,ABSENSI
    $str = "select sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $kodeorg . "' 
             and periode='" . $periode . "' and sudahproses=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=owlBaris($res);
    if ($numrows > 0) {
        exit(" Warning: Periode penggajian belum ditutup untuk periode ini, silahkan tutup dahulu melalui sdm->setup->periode penggajian unit \n Payroll periode has not close in this periode, please make sure for those transaction by confirmation via Hr->Setup->Payroll Periode");
    }

	//periksa tipe organisasi
    $str = "select tipe from " . $dbname . ".organisasi where kodeorganisasi='" . $kodeorg . "'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$tipe = 'KANWIL';
    while ($bar = $res->fetch()) {
        $tipe = $bar->tipe;
    }

    if ($tipe == 'KEBUN') {
        //ambil noakun dari parameter jurnal
        $debet = '';
        $kredit = '';
        $nojurnal = str_replace("-", "", $periode) . "28/" . $kodeorg . "/CT01/001";
        $str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='CT01'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $debet = $bar->noakundebet;
            $kredit = $bar->noakunkredit;
        }
        if ($debet == '' or $kredit == '') {
            exit('Error: Journal parameter for CT01 not defined, contact administrator');
        }
        $kodejurnal = 'CT01';
        //ambil porsi biaya umum
        $byumum = 0;
        $str = "select sum(jumlahrupiah) as byumum from " . $dbname . ".sdm_catu where periodegaji='" . $periode . "' 
                        and kodeorg='" . $kodeorg . "' and subbagian=''";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
            $byumum = $bar->byumum;
        }
        $bytanaman = $jumlah - $byumum;
        //prepare jurnal
        # Prep Header
        $dataRes = Array();
        $dataRes['header'] = array(
            'nojurnal' => $nojurnal,
            'kodejurnal' => $kodejurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'tanggalentry' => date('Ymd'),
            'posting' => '1',
            'totaldebet' => $jumlah,
            'totalkredit' => ($jumlah * -1),
            'amountkoreksi' => '0',
            'noreferensi' => 'CT01',
            'autojurnal' => '1',
            'matauang' => 'IDR',
            'kurs' => '1',
            'revisi' => '0'
        );

        # Data Detail
        $noUrut = 1;
        //jika biaya umum>0
        if ($byumum > 0) {
            # Debet
            $dataRes['detail'][] = array(
                'nojurnal' => $nojurnal,
                'tanggal' => str_replace("-", "", $periode) . "28",
                'nourut' => $noUrut,
                'noakun' => $debet,
                'keterangan' => 'Catu Beras - ' . $periode,
                'jumlah' => $byumum,
                'matauang' => 'IDR',
                'kurs' => '1',
                'kodeorg' => $kodeorg,
                'kodekegiatan' => '',
                'kodeasset' => '',
                'kodebarang' => '',
                'nik' => '',
                'kodecustomer' => '',
                'kodesupplier' => '',
                'noreferensi' => 'CT01',
                'noaruskas' => '',
                'kodevhc' => '',
                'nodok' => '',
                'kodeblok' => '',
                'revisi' => '0',
                'kodesegment' => $defSegment
            );
            $noUrut++;
        }

        //ambil kodeblok dan kegiatan untuk melengkapi sisi debet
        #1 ambil noakun panen
        $akunpanen = '';
        $str = "select  noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='PNN01'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $akunpanen = $bar->noakundebet;
        }
        if ($akunpanen == '') {
            exit(" Error: Account for harvesting not defined in journal parameter PNN01");
        }
        #2 Ambil blok panen
        $sAbsn = "select distinct kodeorg from " . $dbname . ".kebun_prestasi_vw 
				  where tanggal between  '" . $tgl1 . "' and '" . $tgl2 . "' and unit ='" . $kodeorg . "'";
		$respanen1=$owlPDO->query($sAbsn) or die(print " Gagal: ".PDOException::getMessage());
		$jml_baris_pnn=$respanen1->rowCount();
		
		$sAbsn = "select distinct kodeorg from " . $dbname . ".kebun_prestasi_vw 
				  where tanggal between  '" . $tgl1 . "' and '" . $tgl2 . "' and unit ='" . $kodeorg . "'";
        $respanen=$owlPDO->query($sAbsn) or die(print " Gagal: ".PDOException::getMessage());
		$respanen->setFetchMode(PDO::FETCH_OBJ);
		
		#3 ambil noakun dan blok perawatan
        $sAbsn = "select distinct noakun,kodeorg,kodekegiatan from " . $dbname . ".kebun_perawatan_vw 
				  where tanggal between  '" . $tgl1 . "' and '" . $tgl2 . "' and unit ='" . $kodeorg . "'";
		$resrawat1=$owlPDO->query($sAbsn) or die(print " Gagal: ".PDOException::getMessage());
		$jml_baris_rwt=$resrawat1->rowCount();
		
		$sAbsn = "select distinct noakun,kodeorg,kodekegiatan from " . $dbname . ".kebun_perawatan_vw 
				  where tanggal between  '" . $tgl1 . "' and '" . $tgl2 . "' and unit ='" . $kodeorg . "'";
        $resrawat=$owlPDO->query($sAbsn) or die(print " Gagal: ".PDOException::getMessage());
		$resrawat->setFetchMode(PDO::FETCH_OBJ);
		
		$jml_baris=$jml_baris_pnn+$jml_baris_rwt;
		
        #4 dibagi per masing-masing baris     
        if ($jml_baris == 0 and $bytanaman > 0) {
            #jika tidak ada pekerjaan lapangan
            #kembalikan ke biaya umum
            $dataRes['detail'][] = array(
                'nojurnal' => $nojurnal,
                'tanggal' => str_replace("-", "", $periode) . "28",
                'nourut' => $noUrut,
                'noakun' => $debet,
                'keterangan' => 'Catu Beras - ' . $periode,
                'jumlah' => $bytanaman,
                'matauang' => 'IDR',
                'kurs' => '1',
                'kodeorg' => $kodeorg,
                'kodekegiatan' => '',
                'kodeasset' => '',
                'kodebarang' => '',
                'nik' => '',
                'kodecustomer' => '',
                'kodesupplier' => '',
                'noreferensi' => 'CT01',
                'noaruskas' => '',
                'kodevhc' => '',
                'nodok' => '',
                'kodeblok' => '',
                'revisi' => '0',
                'kodesegment' => $defSegment
            );
            $noUrut++;
        } else {
            $biayaperblok = $bytanaman / $jml_baris;
        }
        if ($biayaperblok > 0 and $jml_baris > 0) {
            #5 Bentuk detail jurnal pelengkap disisi debet     
            while ($bar = $respanen->fetch()) {
                # Debet
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => str_replace("-", "", $periode) . "28",
                    'nourut' => $noUrut,
                    'noakun' => $akunpanen,
                    'keterangan' => 'Catu Beras - ' . $periode,
                    'jumlah' => $biayaperblok,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $kodeorg,
                    'kodekegiatan' => $akunpanen . "01",
                    'kodeasset' => '',
                    'kodebarang' => '',
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'CT01',
                    'noaruskas' => '',
                    'kodevhc' => '',
                    'nodok' => '',
                    'kodeblok' => $bar->kodeorg,
                    'revisi' => '0',
                    'kodesegment' => $defSegment
                );
                $noUrut++;
            }

            while ($bar = $resrawat->fetch()) {
                # Debet
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => str_replace("-", "", $periode) . "28",
                    'nourut' => $noUrut,
                    'noakun' => $bar->noakun,
                    'keterangan' => 'Catu Beras - ' . $periode,
                    'jumlah' => $biayaperblok,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $kodeorg,
                    'kodekegiatan' => $bar->kodekegiatan,
                    'kodeasset' => '',
                    'kodebarang' => '',
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'CT01',
                    'noaruskas' => '',
                    'kodevhc' => '',
                    'nodok' => '',
                    'kodeblok' => $bar->kodeorg,
                    'revisi' => '0',
                    'kodesegment' => $defSegment
                );
                $noUrut++;
            }
        }
        # Kredit (Kreditnya cukup satu saja)
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'nourut' => $noUrut,
            'noakun' => $kredit,
            'keterangan' => 'Catu Beras - ' . $periode,
            'jumlah' => -1 * $jumlah,
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $kodeorg,
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'CT01',
            'noaruskas' => '',
            'kodevhc' => '',
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;
    } else if ($tipe == 'TRAKSI') {
        $debet = '';
        $kredit = '';
        $nojurnal = str_replace("-", "", $periode) . "28/" . $kodeorg . "/CT03/001";
        $str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='CT03'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $debet = $bar->noakundebet;
            $kredit = $bar->noakunkredit;
        }
        if ($debet == '' or $kredit == '') {
            exit('Error: Journal parameter for CT03 (Traksi) not defined, contact administrator');
        }
        $kodejurnal = 'CT03';

        #1 Ambil semua kendaraan yang bekerja di bulan ini
        $str = "select distinct kodevhc from " . $dbname . ".vhc_runht where tanggal between  '" . $tgl1 . "' and '" . $tgl2 . "' 
                     and kodeorg ='" . $kodeorg . "'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jml_baris=$res->rowCount();
		
        // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_OBJ);
		// $jml_baris=owlBaris($res);
		//prepare jurnal
        # Prep Header
        $dataRes = Array();
        $dataRes['header'] = array(
            'nojurnal' => $nojurnal,
            'kodejurnal' => $kodejurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'tanggalentry' => date('Ymd'),
            'posting' => '1',
            'totaldebet' => $jumlah,
            'totalkredit' => ($jumlah * -1),
            'amountkoreksi' => '0',
            'noreferensi' => 'CT03',
            'autojurnal' => '1',
            'matauang' => 'IDR',
            'kurs' => '1',
            'revisi' => '0'
        );

        # Data Detail
        $noUrut = 1;
        if ($jml_baris == 0) {//jika tidak ada pekerjaan kendaraan
            # Debet
            $dataRes['detail'][] = array(
                'nojurnal' => $nojurnal,
                'tanggal' => str_replace("-", "", $periode) . "28",
                'nourut' => $noUrut,
                'noakun' => $debet,
                'keterangan' => 'Catu Beras - ' . $periode,
                'jumlah' => $jumlah,
                'matauang' => 'IDR',
                'kurs' => '1',
                'kodeorg' => $kodeorg,
                'kodekegiatan' => '',
                'kodeasset' => '',
                'kodebarang' => '',
                'nik' => '',
                'kodecustomer' => '',
                'kodesupplier' => '',
                'noreferensi' => 'CT03',
                'noaruskas' => '',
                'kodevhc' => '',
                'nodok' => '',
                'kodeblok' => '',
                'revisi' => '0',
                'kodesegment' => $defSegment
            );
            $noUrut++;
        } else {
            $byperkendaraan = $jumlah / $jml_baris;
            while ($bar = $res->fetch()) {
                # Debet
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => str_replace("-", "", $periode) . "28",
                    'nourut' => $noUrut,
                    'noakun' => $debet,
                    'keterangan' => 'Catu Beras - ' . $periode,
                    'jumlah' => $byperkendaraan,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $kodeorg,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => '',
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'CT03',
                    'noaruskas' => '',
                    'kodevhc' => $bar->kodevhc,
                    'nodok' => '',
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => $defSegment
                );
                $noUrut++;
            }
        }
        # Kredit (Kreditnya cukup satu saja)
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'nourut' => $noUrut,
            'noakun' => $kredit,
            'keterangan' => 'Catu Beras - ' . $periode,
            'jumlah' => -1 * $jumlah,
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $kodeorg,
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'CT03',
            'noaruskas' => '',
            'kodevhc' => '',
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;
    } else if ($tipe == 'PABRIK') {
        $debet = '';
        $kredit = '';
        $nojurnal = str_replace("-", "", $periode) . "28/" . $kodeorg . "/CT04/001";
        $str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='CT04'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $debet = $bar->noakundebet;
            $kredit = $bar->noakunkredit;
        }
        if ($debet == '' or $kredit == '') {
            exit('Error: Journal parameter  CT04 (PKS) not defined');
        }
        $kodejurnal = 'CT04';

        //ambil porsi biaya umum
        $byumum = 0;
        $str = "select sum(jumlahrupiah) as byumum from " . $dbname . ".sdm_catu where periodegaji='" . $periode . "' 
                        and kodeorg='" . $kodeorg . "' and subbagian=''";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
            $byumum = $bar->byumum;
        }
        $bystasiun = $jumlah - $byumum;


        #1 Ambil semua statiun yang ada dalam pks
        $str = "select kodeorganisasi from " . $dbname . ".organisasi where tipe='STATION' 
                     and induk ='" . $kodeorg . "'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jml_baris=$res->rowCount();			 
		
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_OBJ);
		$jml_baris=owlBaris($res);
        //prepare jurnal
        # Prep Header
        $dataRes = Array();
        $dataRes['header'] = array(
            'nojurnal' => $nojurnal,
            'kodejurnal' => $kodejurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'tanggalentry' => date('Ymd'),
            'posting' => '1',
            'totaldebet' => $jumlah,
            'totalkredit' => ($jumlah * -1),
            'amountkoreksi' => '0',
            'noreferensi' => 'CT04',
            'autojurnal' => '1',
            'matauang' => 'IDR',
            'kurs' => '1',
            'revisi' => '0'
        );

        # Data Detail
        $noUrut = 1;
        if ($jml_baris == 0) {//jika tidak ada pekerjaan kendaraan
            # Debet
            $dataRes['detail'][] = array(
                'nojurnal' => $nojurnal,
                'tanggal' => str_replace("-", "", $periode) . "28",
                'nourut' => $noUrut,
                'noakun' => $debet,
                'keterangan' => 'Catu Beras - ' . $periode,
                'jumlah' => $jumlah,
                'matauang' => 'IDR',
                'kurs' => '1',
                'kodeorg' => $kodeorg,
                'kodekegiatan' => '',
                'kodeasset' => '',
                'kodebarang' => '',
                'nik' => '',
                'kodecustomer' => '',
                'kodesupplier' => '',
                'noreferensi' => 'CT04',
                'noaruskas' => '',
                'kodevhc' => '',
                'nodok' => '',
                'kodeblok' => '',
                'revisi' => '0',
                'kodesegment' => $defSegment
            );
            $noUrut++;
        } else {
            //biaya umum masuk dulu
            if ($byumum > 0) {
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => str_replace("-", "", $periode) . "28",
                    'nourut' => $noUrut,
                    'noakun' => $debet,
                    'keterangan' => 'Catu Beras - ' . $periode,
                    'jumlah' => $byumum,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $kodeorg,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => '',
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'CT04',
                    'noaruskas' => '',
                    'kodevhc' => '',
                    'nodok' => '',
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => $defSegment
                );
                $noUrut++;
            }
            //bagi biaya station ke setiap station
            $byperstasiun = $bystasiun / $jml_baris;
            while ($bar = $res->fetch()) {
                # Debet
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => str_replace("-", "", $periode) . "28",
                    'nourut' => $noUrut,
                    'noakun' => $debet,
                    'keterangan' => 'Catu Beras - ' . $periode,
                    'jumlah' => $byperstasiun,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $kodeorg,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => '',
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => 'CT04',
                    'noaruskas' => '',
                    'kodevhc' => '',
                    'nodok' => '',
                    'kodeblok' => $bar->kodeorganisasi,
                    'revisi' => '0',
                    'kodesegment' => $defSegment
                );
                $noUrut++;
            }
        }
        # Kredit (Kreditnya cukup satu saja)
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'nourut' => $noUrut,
            'noakun' => $kredit,
            'keterangan' => 'Catu Beras - ' . $periode,
            'jumlah' => -1 * $jumlah,
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $kodeorg,
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'CT04',
            'noaruskas' => '',
            'kodevhc' => '',
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;
    } else {//jika bukan pks,kebun atau traksi maka masuh biaya umum
        $debet = '';
        $kredit = '';
        $nojurnal = str_replace("-", "", $periode) . "28/" . $kodeorg . "/CT01/001";
        $str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='CT01'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $debet = $bar->noakundebet;
            $kredit = $bar->noakunkredit;
        }
        if ($debet == '' or $kredit == '') {
            exit('Error: Journal parameter CT01 (Kebun) not defined');
        }
        $kodejurnal = 'CT01';
        //prepare jurnal
        # Prep Header
        $dataRes = Array();
        $dataRes['header'] = array(
            'nojurnal' => $nojurnal,
            'kodejurnal' => $kodejurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'tanggalentry' => date('Ymd'),
            'posting' => '1',
            'totaldebet' => $jumlah,
            'totalkredit' => ($jumlah * -1),
            'amountkoreksi' => '0',
            'noreferensi' => 'CT01',
            'autojurnal' => '1',
            'matauang' => 'IDR',
            'kurs' => '1',
            'revisi' => '0',
        );

        # Data Detail
        $noUrut = 1;
        # Debet
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'nourut' => $noUrut,
            'noakun' => $debet,
            'keterangan' => 'Catu Beras - ' . $periode,
            'jumlah' => $jumlah,
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $kodeorg,
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'CT01',
            'noaruskas' => '',
            'kodevhc' => '',
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => str_replace("-", "", $periode) . "28",
            'nourut' => $noUrut,
            'noakun' => $kredit,
            'keterangan' => 'Catu Beras - ' . $periode,
            'jumlah' => -1 * $jumlah,
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $kodeorg,
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => 'CT01',
            'noaruskas' => '',
            'kodevhc' => '',
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    #execute
    #========================== Proses Insert dan Update ==========================
    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
    $headErr = '';
    $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	try{
		$owlPDO->exec($insHead); 
	}catch (PDOException $e){
		$headErr .= 'Insert Header Error : ' . $e->getMessage() . "\n";
	}

    if ($headErr == '') {
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
        $detailErr = '';
        foreach ($dataRes['detail'] as $row) {
            $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
            try{
				$owlPDO->exec($insDet); 
			}catch (PDOException $e){
				$detailErr .= "Insert Detail Error : " . $e->getMessage() . "\n";
                break;
			}
        }

        if ($detailErr == '') {
            #update sdm_catu status posting
            $str = "update " . $dbname . ".sdm_catu set posting=1 where kodeorg='" . $kodeorg . "' and periodegaji='" . $periode . "'";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				
			}
        } else {
            echo $detailErr;
            # Rollback, Delete Header
            $RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
            try{
				$owlPDO->exec($RBDet); 
			}catch (PDOException $e){
				echo "Rollback Delete Header Error : " . $e->getMessage();
                exit;
			}
        }
    } else {
        echo $headErr;
        exit;
    }
}

function dates_inbetween($date1, $date2) {

    $day = 60 * 60 * 24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1) / $day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[] = date('Y-m-d', $date1);

    for ($x = 1; $x < $days_diff; $x++) {
        $dates_array[] = date('Y-m-d', ($date1 + ($day * $x)));
    }

    $dates_array[] = date('Y-m-d', $date2);
    if ($date1 == $date2) {
        $dates_array = array();
        $dates_array[] = date('Y-m-d', $date1);
    }
    return $dates_array;
}

function jumlah_hari($bulan = 0, $tahun = '')
{
    if ($bulan < 1 OR $bulan > 12)
    {
  return 0;
    }
    if ( ! is_numeric($tahun) OR strlen($tahun) != 4)
    {
  $tahun = date('Y');
    }
    if ($bulan == 2)
    {
  if ($tahun % 400 == 0 OR ($tahun % 4 == 0 AND $tahun % 100 != 0))
  {
  return 29;
  }
    }
    $jumlah_hari    = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    return $jumlah_hari[$bulan - 1];
}
?>