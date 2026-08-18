<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}
$method             = checkPostGet('method', '');
$notransaksi        = checkPostGet('notransaksi', '');
$namakary           = checkPostGet('namakary', '');
$tt                 = checkPostGet('tt', '');
$tgl                = checkPostGet('tgl', '');
$kodeorg            = checkPostGet('kodeorg', '');
$filterdivisi       = checkPostGet('filterdivisi', '');
$showpermandor      = checkPostGet('showpermandor', '');
$jenispremi         = checkPostGet('jenispremi', '');
$mandor             = checkPostGet('mandor', '');
$mandor1            = checkPostGet('mandor1', '');
$asst               = checkPostGet('asst', '');
$kerani             = checkPostGet('kerani', '');
$nobkm              = checkPostGet('nobkm', '');
$blok               = checkPostGet('blok', '');
$karyawanid         = checkPostGet('karyawanid', '');
$jjgpanen           = checkPostGet('jjgpanen', '');
$mode               = checkPostGet('mode', '');
$sts                = checkPostGet('sts', '');
$kontan             = checkPostGet('kontan', '');
$jlhdenda           = checkPostGet('jlhdenda', '');
$kodeiddenda        = checkPostGet('kodeiddenda', '');
$divsch             = checkPostGet('divsch', '');
$tglmulai           = tanggalsystemn(checkPostGet('tglmulai', ''));
$tglselesai         = tanggalsystemn(checkPostGet('tglselesai', ''));
$notransaksisch     = checkPostGet('notransaksisch', '');
$postingsrc         = checkPostGet('postingsrc', '');
$periodesch         = checkPostGet('periodesch', '');
$kodeabsen          = checkPostGet('kodeabsen', '');
$filterpt           = checkPostGet('filterpt', '');
$filterunit         = checkPostGet('filterunit', '');
$kodeorgkary        = checkPostGet('kodeorgkary', '');
$tipe               = checkPostGet('tipe', '');

@$param['hapanen']  =str_replace(",","",$param['hapanen']);
@$param['jjgpanen'] =str_replace(",","",$param['jjgpanen']);
@$param['brdpanen'] =str_replace(",","",$param['brdpanen']);
@$param['kgpanen']  =str_replace(",","",$param['kgpanen']);
@$param['upah']     =str_replace(",","",$param['upah']);
@$param['basis']    =str_replace(",","",$param['basis']);
@$param['lbasis']   =str_replace(",","",$param['lbasis']);
@$param['denda_rp'] =str_replace(",","",$param['denda_rp']);
@$param['tt']       =str_replace(",","",$param['tt']);
@$param['bjr']      =str_replace(",","",$param['bjr']);
@$param['rpbrondol']=str_replace(",","",$param['rpbrondol']);
@$param['jjgbasis'] =str_replace(",","",$param['jjgbasis']);
@$param['hk']       =str_replace(",","",$param['hk']);
@$param['dendaupah']=str_replace(",","",$param['dendaupah']);
@$param['lbasis2']  =str_replace(",","",$param['lbasis2']);
@$param['lbasis2']  =str_replace(",","",$param['lbasis2']);
@$param['premi']    =str_replace(",","",$param['premi']);
@$param['upahpremi']=str_replace(",","",$param['upahpremi']);
if($param['jjgpanen']==''){$param['jjgpanen']='0';}
if($param['brdpanen']==''){$param['brdpanen']='0';}

if($param['tph']!=''){
	$param['tph'] = $param['blok'].addZero($param['tph'],2);
}

if($param['tphold']!=''){
	$param['tphold'] = $param['blok'].addZero($param['tphold'],2);
}
// echo"<pre>";
// print_r($param);
// echo"</pre>";
// exit("error");

$ndenda=explode("##",$kodeiddenda);
$n = count($ndenda);
if($n>0){
	for($i=0;$i<$n;$i++){
		@$param['penalti'.$ndenda[$i]] =str_replace(",","",$param['penalti'.$ndenda[$i]]);
	}
}

$jab   = getPostingJabatan('panen'); 
$tmpTgl= explode('-',$tgl); 

$dendapanen=array();
$iddendapnn=array();

$str = "select max(id) as max,a.*,b.* from ".$dbname.".kebun_5dendapanen a left join ".$dbname.".kebun_5kodedendapanen b on a.kodedenda=b.kodedenda where 1=1 and a.kodeorg='".$kodeorg."' group by id order by b.id asc";
$res = fetchdata($str);
foreach($res as $bar){
	$iddendapnn[$bar['id']]=$bar['id'];
	$dendapanen[$bar['id']]=$bar['kodedenda'];
	$namadenda[$bar['id']]=$bar['deskripsi'];
	$tp[$bar['id']]= "title=\"".$bar['kodedenda']." => ".$bar['deskripsi']." = (".$bar['denda']." / ".$bar['jenisdenda'].")\"";
	$tplistdata[$bar['id']]= "title=\"".$bar['kodedenda']." => ".$bar['deskripsi']." = (".$bar['denda']." / ".$bar['jenisdenda'].")";
	$harga[$bar['id']] = $bar['denda']; 
	$sat[$bar['id']] = $bar['jenisdenda']; 
	$hp[$bar['id']] = $bar['kodedenda'];
	$maxdenda=$bar['max'];
}

#============== KHT, KHL dan Kontrak ======================
	$whereKary="";
	if($kodeorgkary==''){$kodeorgkary=$kodeorg;}
	if($method=='detail'){$filterdivisi=$param['divisi'];}
	if($filterdivisi!=''){
		$unitsendiri= substr($param['divisi'],0,4);
		$unitlawan  = substr($filterdivisi,0,4);
		
		$dt=array();
		$str="select * from ".$dbname.".kebun_5asistensi where kodeorgtujuan ='".$param['kodeorg']."' and tanggal<='".tanggalsystemn($param['tgl'])."' and tanggalsampai>='".tanggalsystemn($param['tgl'])."' and posting='1' and (tipetrans='PNN' or tipetrans='')";
		$resx=fetchdata($str);
		foreach($resx as $res){
			$divisiasal[$res['divisiasal']]=$res['divisiasal'];

			#asistensi hanya beberapa kary
			$s="select * from ".$dbname.".kebun_5asistensi_dt where id ='".$res['id']."' and karyawanid!='0000000000'";
			$r=fetchdata($s);
			foreach($r as $b){
				$dt[$b['karyawanid']]=$b['karyawanid'];
			}
		}
		if($unitsendiri!=$unitlawan){			
			if(count($dt)>0 and $filterdivisi!=$param['divisi']){			
				$whereKary.=" and karyawanid in ('".implode("','",$dt)."')";
			}elseif(count($resx)>0 and $filterdivisi!=$param['divisi']){			
				$whereKary.=" and subbagian in ('".implode("','",$divisiasal)."') and tipekaryawan in ('1','2','3','4','6')";
			}else{
				$whereKary.=" and tipekaryawan in ('1','2','3','4','6')";
			}
		}else{			
			$whereKary.=" and subbagian = '".$filterdivisi."'";
			$whereKary.=" and tipekaryawan in ('1','2','3','4','6')";
		}
	}else{
		if($filterunit!=''){
			$whereKary.= " and lokasitugas='".$filterunit."' and tipekaryawan in ('1','2','3','4','6')";
		}else{			
			$whereKary.= " and lokasitugas='".$kodeorgkary."' and tipekaryawan in ('1','2','3','4','6')";
		}
	}
	
	// if($method=='getdata'){		
		// echo "<pre>";
		// print_r($whereKary);
		// exit("error");
	// }
	
	$whereKary.=" and subbagian != ''";
	$whereKary.=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".tanggalsystemn($tgl)."')";
	$whereKary.="and tanggalmasuk<='".tanggalsystemn($tgl)."'";
	$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.subbagian from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." order by a.subbagian,a.namakaryawan asc";
	$res = fetchdata($str);
	foreach($res as $bar){
		$d=$bar['subbagian'];
		if($bar['nik']!=''){
			$bar['nik']=" - ".$bar['nik'];
		}
		if($bar['subbagian']!=''){
			$bar['subbagian']=" - ".$bar['subbagian'];
		}
		
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optKary.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		$optKary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan'].$bar['nik']."</option>";
		$n=$d;
		if($d!=$n){			
			$optKary.="</optgroup>";
		}
	}
	if($param['jenis']=='BOR'){
		$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$kodesupp=makeOption($dbname,'log_spkht','notransaksi,koderekanan',"notransaksi='".$param['nospk']."'");
		$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$kodesupp[$param['nospk']]."'");
		if($param['method']=='getdata'){
			
			// echo"<pre>";
			// print_r($param['nospk']);
			// exit("error");
		}
		
		$optKary.="<option value=".$kodesupp[$param['nospk']].">".$namasupp[$kodesupp[$param['nospk']]]."</option>";
	}
	
	
#============== KHT, KHL dan Kontrak ======================

#===================== Kode Blok ==========================
	$whereBlok=$whererpnn='';
	/* $whereBlok.=" and substr(a.kodeorganisasi,1,6) = '".$param['divisi']."'";
	$whereBlok.= " and b.statusblok = 'TM' and substr(a.kodeorganisasi,1,4)='".$kodeorg."'";
	
	$mobile = makeOption($dbname, 'kebun_aktifitas', 'notransaksi,noreferensi',"notransaksi='".$param['notransaksi']."'");
	if($mobile[$param['notransaksi']]==''){		
		$whererpnn.=" and kodeorganisasi in (select blok from ".$dbname.".kebun_rekappnn where tanggal = '".tanggalsystem($tgl)."')";
	}
	 */
	if($filterdivisi!=''){
		if(substr($filterdivisi,0,4)!=$kodeorg){
			$whereBlok.= " and substr(a.kodeorganisasi,1,6) = '".$param['divisi']."'";
		}else{			
			$whereBlok.=" and substr(a.kodeorganisasi,1,6) = '".$filterdivisi."'";
		}
	}else{
		$whereBlok.= " and substr(a.kodeorganisasi,1,4) ='".$kodeorg."'";
	}
	$whereBlok.= " and b.statusblok in ('TM') and substr(a.kodeorganisasi,1,4)='".$kodeorg."'";
	$whererpnn.=" and kodeorganisasi in (select blok from ".$dbname.".kebun_rekappnn where tanggal = '".tanggalsystem($tgl)."')";
	
	$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".organisasi a 
			left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
			where a.tipe='BLOK' ".$whereBlok." ".$whererpnn." order by a.kodeorganisasi asc"; 
	#exit('error'.$str);
	$res = fetchdata($str);$n="";
	foreach($res as $bar){
		$d=$bar['induk'];
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optBlok.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		
		$optnmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorganisasi']."'");
		if($optnmorg[$bar['kodeorganisasi']]==$bar['kodeorganisasi']){
			$blkkry=substr($bar['kodeorganisasi'],6,4);
		}else{
			$blkkry=$optnmorg[$bar['kodeorganisasi']];
		}
		$optBlok.="<option value=".$bar['kodeorganisasi'].">".$blkkry."</option>";
		$n=$d;
		if($d!=$n){			
			$optBlok.="</optgroup>";
		}
	}
#===================== Kode Blok ==========================

$optsesi="<option value='1'>1</option>";
$optsesi.="<option value='2'>2</option>";
$optsesi.="<option value='3'>3</option>";


switch ($method) {
	case'getnospk':
		$param['tgl']=tanggalsystemn($param['tgl']);
		$periode=substr($param['tgl'],0,7);
		
		$namasupp=array();
		$optsupp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sql = "SELECT a.* FROM " . $dbname . ".log_spkht a 
		left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi 
		where a.posting='0' and b.close='0' and b.jenis='PANENTBS' and a.kodeorg='".$param['kodeorg']."' and substr(a.dari,1,7)<='".$periode."' and substr(a.sampai,1,7)>='".$periode."' order by a.notransaksi asc"; #exit("error".$sql);
		$res = fetchdata($sql);
		foreach($res as $bar){
			$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['koderekanan']."'");
			$optsupp.="<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
		}
		
		echo $optsupp;
	break;
	case'getdivmdr':
		echo getmandor($param);
	break;
	case'getunit':
		$optPt=$optUnit=$key='';
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') and induk='".$filterpt."'";
		$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $resstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $resstr->fetch()) {
			$key=$res['kodeorganisasi'];
			$optUnit.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
		}
		
		$optdiv="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING') and kodeorganisasi like '".$key."%'"; #exit("error.$str");
		$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $resstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $resstr->fetch()) {
			$optdiv.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
		}
		
		
		echo $optUnit."####".$optdiv;
	break;
	case'getdivisi':
	
		$str="select * from ".$dbname.".kebun_5asistensi where kodeorgtujuan ='".$param['kodeorg']."' and  tanggal<='".tanggalsystemn($param['tgl'])."' and tanggalsampai>='".tanggalsystemn($param['tgl'])."' and posting='1' and (tipetrans='PNN' or tipetrans='')";
		$resx=fetchdata($str);
		$divisiasal[$param['divisi']]=$param['divisi'];
		foreach($resx as $res){
			$divisiasal[$res['divisiasal']]=$res['divisiasal'];
		}
		
		#$optUnit="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') and kodeorganisasi in ('".implode("','",$divisiasal)."') and kodeorganisasi like '".$filterunit."%'";
		$resx=fetchdata($str);
		foreach($resx as $res){
			$optUnit.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
		}
		
		// exit("error");
		echo $optUnit;
	break;
    case'detail':
	try {
		$owlPDO->beginTransaction();
		
		#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
		$sekarang=  tanggalsystem($tgl);
		if($sekarang<$_SESSION['org']['period']['start']){
			throw new PDOException("Tanggal tidak sesuai dengan periode akuntansi aktif.");
        }
		$tgl=tanggalsystemn($tgl);
		if(count($iddendapnn)<'1'){
			throw new PDOException("Harga denda panen belum ada, silahkan tambahkan melalui menu : Kebun - Setup - Denda Panen");
		}
		
		if($mandor==$mandor1 and ($mandor!='' or $mandor1!='')){
			throw new PDOException("Mandor dan Mandor 1 tidak boleh sama.");
		}elseif($mandor==$kerani and ($mandor!='' or $kerani!='')){
			throw new PDOException("Mandor dan Kerani tidak boleh sama.");
		}elseif($mandor1==$kerani and ($kerani!='' or $mandor1!='')){
			throw new PDOException("Mandor 1 dan Kerani tidak boleh sama.");
		}
		
		if(substr($param['divisi'],0,4)!=$kodeorg){
			throw new PDOException("Divisi dan Kebun tidak sesuai.");
		}
		
		if($param['jenis']=='BOR' and $param['nospk']==''){
			throw new PDOException("Nomor SPK wajib diisi.");
		}
		
		##cek apakah sudah diinput di detail BKM belum
		$str1 = "select * from " . $dbname . ".kebun_kehadiran_vw where 
		( karyawanid = '".$mandor."' or karyawanid = '".$mandor1."' or karyawanid = '".$kerani."') 
		and tanggal = '".$tgl."' and (jhk > '0' or umr > '0')";
		
		$wherenamaKary= "( karyawanid = '".$mandor."' or karyawanid = '".$mandor1."' or karyawanid = '".$kerani."')";
		$namaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$wherenamaKary);
		$numrows=count(fetchdata($str1));
		if($numrows>0){
			echo "HK Mandor / Mandor 1 / Kerani tidak boleh di input pada detail BKM.\n\nKaryawan tersebut dibawah ini sudah terdaftar pada transaksi :\n";//
			$no=0;
			foreach($res as $bar){
			   $no+=1;
				echo $no.". ".$namaKary[$bar['karyawanid']]." => ".$bar['notransaksi']." => ".tanggalnormal($bar['tanggal'])."\n"; 
			}
			throw new PDOException("Silahkan kosongkan HK pada transaksi tersebut.");
		}
		#cek di vhc - kegiatan traksi
		$qAbs = "select sum(upah) as upah,idkaryawan,notransaksi,tanggal from ".$dbname.".vhc_runhk where (idkaryawan = '".$mandor."' or idkaryawan = '".$mandor1."' or idkaryawan = '".$kerani."') and tanggal='".$tgl."' and upah > '0' group by idkaryawan,notransaksi,tanggal"; 
		$resAbs = fetchData($qAbs);
		if(count($resAbs)>'0') {
			$no=0;
			echo "Karyawan dibawah ini sudah terdaftar pada transaksi Traksi - Transaksi - Kegiatan\n";
			foreach($resAbs as $bar){
				$no+=1;
				echo $no.". ".$namaKary[$bar['idkaryawan']]." => ".$bar['notransaksi']." => ".tanggalnormal($bar['tanggal'])."\n"; 
			}
			throw new PDOException();
		}
		#cek sdm absensi
		$str = "select karyawanid,tanggal, sum(umr) as umr from ".$dbname.".sdm_absensidt where (karyawanid = '".$mandor."' or karyawanid = '".$mandor1."' or karyawanid = '".$kerani."') and tanggal='".$tgl."' and umr > '0' group by karyawanid,tanggal";
		#exit("error".$str);
		$res = fetchData($str);
		if(count($res)>'0') {
			$no=0; echo "Karyawan dibawah ini sudah terdaftar pada transaksi SDM - Transaksi - Absensi\n";
			foreach($res as $bar){
				$no+=1;
				echo $no.". ".$namaKary[$bar['karyawanid']]." => ".tanggalnormal($bar['tanggal'])."\n"; 
			}
			throw new PDOException();
		}
		#cek panen
		$str = "select nik,notransaksi, sum(upahkerja) as umr from ".$dbname.".kebun_prestasi where (nik = '".$mandor."' or nik = '".$mandor1."' or nik = '".$kerani."') and notransaksi like '".str_replace("-","",$tgl)."%' and upahkerja > '0' group by nik,notransaksi";
		$res = fetchData($str);
		if(count($res)>'0') {
			$no=0; echo "Karyawan dibawah ini sudah terdaftar pada transaksi Kebun - Transaksi - Kegiatan Panen\n";
			foreach($res as $bar){
				$no+=1;
				echo $no.". ".$namaKary[$bar['nik']]." => ".$bar['notransaksi']."\n"; 
			}
			throw new PDOException();
		}
		
		#=== insert header ===
        $sql = "select * from " . $dbname . ".kebun_aktifitas where "."notransaksi='" . $notransaksi . "'";
        $res = fetchData($sql);
        if (count($res) > 0 and $mode=='edit') {
			cekmaxnilaihk($mandor,$tgl,'1','1','edit',$exit='0');
			cekmaxnilaihk($mandor1,$tgl,'1','1','edit',$exit='0');
			cekmaxnilaihk($kerani,$tgl,'1','1','edit',$exit='0');
			
			
			#=== pastikan 1 mandor 1 notransaksi ===
			/* $str = "select * from ".$dbname.".kebun_aktifitas where nikmandor='".$mandor."' and tanggal='".$tgl."' and nobkm!='".$nobkm."' and tipetransaksi='PNN'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Satu mandor dalam satu hari hanya boleh 1 nomor BKM.");
			} */
			
            $str = "update " . $dbname . ".kebun_aktifitas set `divisi`='".$param['divisi']."', `nobkm`='".$nobkm."', `nikmandor`='".$mandor."', `nikmandor1`='".$mandor1."',`nikasisten`='".$kerani."', jenis='".$param['jenis']."', nospk='".$param['nospk']."' where `notransaksi`='".$notransaksi."'";
			$owlPDO->exec($str);
        } else {
			cekmaxnilaihk($mandor,$tgl,'1','0','new',$exit='0');
			cekmaxnilaihk($mandor1,$tgl,'1','0','new',$exit='0');
			cekmaxnilaihk($kerani,$tgl,'1','0','new',$exit='0');
			
			
			#=== pastikan 1 mandor 1 notransaksi ===
			/* $str = "select * from ".$dbname.".kebun_aktifitas where nikmandor='".$mandor."' and tanggal='".$tgl."' and tipetransaksi='PNN'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Satu mandor dalam satu hari hanya boleh 1 nomor BKM.");
			} */
			
			
			$sql = "select * from " . $dbname . ".kebun_aktifitas where "."notransaksi='" . $notransaksi . "'";
			$res = fetchData($sql);
			if (count($res) > 0) {
				$notrtemp = explode("/",$notransaksi);
				$fWhere = "tanggal='".$notrtemp[0]."' and kodeorg='".$notrtemp[1]."' and tipetransaksi='PNN'";
				$str = "select max(substr(notransaksi,-3)) as notr from " . $dbname . ".kebun_aktifitas where ".$fWhere." limit 1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				
				$trtemp = addZero((intval($bar['notr'])+1),3);
				$notransaksi=str_replace($notrtemp[3],$trtemp,$notransaksi);
			}
			if($notransaksi==''){
				throw new PDOException("Nomor Transaksi tidak boleh kosong.");
			}
			$statuspersetujuan='0';
			if(in_array($_SESSION['empl']['jabatan'],$jab)){
				$statuspersetujuan='1';
			}
			
			$str = "insert into " . $dbname . ".kebun_aktifitas (`notransaksi`, `tipetransaksi`, `tanggal`, `nobkm`, `kodeorg`,`divisi`, `nikmandor`, `nikmandor1`, `nikasisten`, `keranimuat`, `jurnal`, `updateby`,`statuspersetujuan`,`jenis`,`nospk`, `tipe`)
			values ('".$notransaksi."','PNN','".$tgl."','" . $nobkm . "','" . $kodeorg . "','".$param['divisi']."','".$mandor."','".$mandor1."','".$kerani."','','0','" . $_SESSION['standard']['userid'] . "','".$statuspersetujuan."','".$param['jenis']."','".$param['nospk']."','JJG')"; #exit("error".$str);
			$owlPDO->exec($str);
		}
	
	$param['tgl']=$tgl;
	#saveheadertosdmabsensi($param);
		
	#execute
	$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
		#=== insert header ===
        // $tab=OPEN_BOX();
		#==== Form Judul Detail ====
		# Divisi
		
		$optDivisi='';
		$optPt=$optUnit='';
		$optDivisi.="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' and kodeorganisasi like '".$kodeorg."%'";
		$resstr = fetchdata($str);
        foreach($resstr as $res){
			if($param['divisi']==$res['kodeorganisasi']){
				$optDivisi.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}else{
				$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}
		}
		
		
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('PT')";
		$resstr = fetchdata($str);
        foreach($resstr as $res){
			if($_SESSION['empl']['kodeorganisasi']==$res['kodeorganisasi']){
				$optPt.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']."</option>";
			}else{
				$optPt.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']."</option>";
			}
		}
		
		$str="select * from ".$dbname.".kebun_5asistensi where kodeorgtujuan ='".$param['kodeorg']."' and tanggal<='".$param['tgl']."' and tanggalsampai>='".$param['tgl']."' and posting='1' and (tipetrans='PNN' or tipetrans='')";
		$resx=fetchdata($str);
		foreach($resx as $res){
			$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['kodeorgasal']."'");
			//$optUnit.="<option value=".$res['kodeorgasal'].">".$res['kodeorgasal']." - ".$nmorg[$res['kodeorgasal']]."</option>";
			$dtunit[$res['kodeorgasal']]=$res['kodeorgasal'];
			
			
			$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['divisiasal']."'");
			$optDivisi.="<option value=".$res['divisiasal'].">".$res['divisiasal']." - ".$nmorg[$res['divisiasal']]."</option>";
		}
		
		if(count($resx)>0){
			$dis="";
		}else{
			$dis="disabled";
		}
		#semua divisi dimunculkan
		$dis="";
		
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') and kodeorganisasi='".$param['kodeorg']."'";
		$resx=fetchdata($str);
		foreach($resx as $res){
			$dtunit[$res['kodeorganisasi']]=$res['kodeorganisasi'];
			/* if($param['kodeorg']==$res['kodeorganisasi']){
				$optUnit.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}else{
				$optUnit.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			} */
		}
		
		foreach($dtunit as $unit){
			if($param['kodeorg']==$unit){
				$optUnit.="<option value=".$unit." selected>".$unit." - ".getNamaOrg($unit)."</option>";
			}else{
				$optUnit.="<option value=".$unit.">".$unit." - ".getNamaOrg($unit)."</option>";
			}
		}
		
		
		#=================== Jenis dan Hari =======================
		$tanggalx = tanggalsystemn(tanggalnormal($param['tgl']));		
		$jenispremi = getjenisharikerja($kodeorg,$tanggalx);
		if($jenispremi=='LIBUR'){
			$wrna="color:red";$n="";
		}else if($jenispremi=='JUMAT'){
			$wrna="color:blue";$n="hidden";
		}else{
			$wrna="color:''";$n="hidden";
		}
			
		$namahari = strtoupper(@hari($tanggalx,'ID'));
		#=================== Jenis dan Hari =======================
		
		$str="select * from ".$dbname.".kebun_prestasi where notransaksi='".$notransaksi."' and keterangan='KONTAN'";
		$res = fetchdata($str);
		$kontanceck=$ktnceck="";
		$infokontan = $_SESSION['lang']['tidak'];
		if(count($res)>0){
			$kontanceck =" checked=true";
			$infokontan = $_SESSION['lang']['ya'];
			$n="";
		}else{
			$ktnceck=" checked=true";
		}
		
		$dispermdr="";
		if($param['jenis']=='BOR'){
			$dispermdr="disabled";
		}
		
        $frm[0]="<table><td valign=top>
			<fieldset style=float:left;><legend>Filter</legend>
				<table height=25px>
					<td hidden>" . $_SESSION['lang']['pt'] . "</td>
					<td hidden><select style=\"width:50px;\" title=\"Untuk menampilkan data karyawan dari PT lain.\" onchange=\"getunit();\" id=filterpt>".$optPt."</select></td>
					
					<td style=display:none>" . $_SESSION['lang']['unit'] . "</td>
					<td style=display:none><select style=\"width:150px;\" title=\"Untuk assistensi dari unit lain silahkan daftarkan terlebih dahulu melalui menu Kebun - Setup - Assistensi.\" onchange=\"getdivisi();\" id=filterunit ".$dis.">".$optUnit."</select></td>
					
					
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td><select style=\"width:150px;\" title=\"Untuk menampilkan data karyawan dari divisi lain.\" onchange=\"getdata(this.value)\" id=filterdivisi ".$dis.">".$optDivisi."</select></td>
					
					<td>&nbsp;</td>
					<td><input type=checkbox onchange=\"getdatamandor()\" id=showpermandor ".$dispermdr."></td>
					<td>Per Mandor</td>
				</table>
			</fieldset>
			</td>
			<td valign=top >
			<fieldset style=float:left ".$n.">
				<legend>Kontanan</legend>
				<table height=25px width=100%><td align=center>
					<!--
					<input type=checkbox ".$kontanceck." onclick=getkontan('".$jenispremi."') id=kontanxxxxx><span id=info_kontan>".$infokontan."</span>
					-->
					
					<input type=radio ".$kontanceck." onclick=getkontan('".$jenispremi."') name=kont id=kontan value='KONTAN'><span id=info_kontan>Ya</span>
					<input type=radio ".$ktnceck." onclick=getkontan('".$jenispremi."') name=kont id=kontanfalse value='0'><span id=info_kontan>Tidak</span>
					</td></table>
			</fieldset>
			</td>
			<td valign=top>
			<fieldset style=float:left>
				<legend>Screen</legend>
				<table height=25px width=100%><td align=center>
					<img id='hidebtn' onclick=\"hideheader()\" title='Full Screen' class='zImgBtn' src='images/full-screen.png' >
					<img id='unhidebtn' onclick=\"unhideheader()\" title='Exit Full Screen' class='zImgBtn' style=display:none src='images/exit_full_screen.png' >
					</td></table>
			</fieldset>
			</td>
			
			<td valign=top>
			<fieldset style=float:left>
				<legend>Hari</legend>
				<table  height=25px><td>Hari</td><td>=</td><td><b>".@$namahari."</b></td><td>&nbsp;Jenis Premi</td>
				<td>=</td><td id=jenispremi style=\"cursor:pointer;font-weight:bold;".$wrna."\" title=\"Daftarkan hari libur melalui menu : SDM - Setup - Hari Libur !\">".$jenispremi."</td>
				</table>
			</fieldset>
			</td>
			
			
			<td valign=top>
			<fieldset style=float:left>
				<legend>Info</legend>
				<table height=25px><td><font color=red><b>* </font>".$_SESSION['lang']['notifobligatory']."</b></td>
				<td>&nbsp;||&nbsp;</td>
				<td>
					<a href='fileupload/simulasipanen2.xlsx' download>
						<img class='zImgBtn' src='images/modify.png' title=\"Download file Excel simulasi.\" style='position:relative;top:3px;left:3px;'>
					</a>
				</td>
				<td>&nbsp;||</td><td>
					<img class='zImgBtn' onclick=getbasispnn('".$jenispremi."','rekap','') src='images/book_icon.gif' title=\"Preview Basis Panen.\" style='position:relative;top:3px;left:3px;'>
				</td>
				</table>
			</fieldset>
			</td>
			
			</table>
			<fieldset>
			<legend>" . $_SESSION['lang']['detail'] . "</legend>
			
			<table border=0 cellpadding=1 cellspacing=1 class=sortable >
			<thead><tr class=rowheader>";
			$rows="rowspan=2";	
			$frm[0].="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center colspan=4>".$_SESSION['lang']['nomor'] . "</th>
				<th align=center ".$rows." width=30px>".$_SESSION['lang']['tahuntanam'] . "</th>
				<th align=center ".$rows." width=30px>".$_SESSION['lang']['bjr'] . "</th>
				<th align=center colspan=5>".$_SESSION['lang']['hasilkerja2'] . "</th>
				<th align=center colspan=3><font color=red><b>* </font></b>".$_SESSION['lang']['jumlah']."</th>
				<th align=center colspan=5>".$_SESSION['lang']['premilebihbasis']."</th>
				<input id=jumlahkolomdenda value=".count($dendapanen)." style=display:none>
				<th align=center colspan=".count($dendapanen)." id=phead name=inputdenda[] title='Click to Hide' onclick=hidedendav2('inputdenda[]') ><font color=Orange><b>".$_SESSION['lang']['denda']."</b></font></th>
				
				<th align=center ".$rows." title='Click to Unhide' id=pheadrp onclick=hidedendav2('inputdenda[]') style=width:50px;color:Orange;font-weight:bold;>".$_SESSION['lang']['denda']." Rp</th>
				
				<th align=center ".$rows." colspan=3>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr>
				<th align=center colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['blok'] . "</th>
				<th align=center>TPH</th>
				<th align=center>Sesi</th>
				
				<th align=center><font color=red><b>* </font></b>".$_SESSION['lang']['ha'] . "</th>
				<th align=center><font color=red><b>* </font></b>".$_SESSION['lang']['jjg'] . "</th>
				<th align=center>Jjg<br>Basis</th>
				<th align=center>Kg Brd</th>
				<th align=center>".$_SESSION['lang']['kg'] . "</th>
				<th align=center>".$_SESSION['lang']['hk2'] . "</th>
				<th align=center>".$_SESSION['lang']['upah'] . "</th>
				<th align=center>".$_SESSION['lang']['denda'] . "</th>
				<th style=display:none; align=center width=40px rowspan=1 title=\"Hanya untuk karyawan KHL jika HK = 0 dan upah dimasukkan ke dalam premi\">Upah Premi</th>
				<th align=center>".$_SESSION['lang']['basic'] . " 1</th>
				<th align=center>".$_SESSION['lang']['basic'] . " 2</th>
				<th align=center width=40px>".$_SESSION['lang']['lebihbasis'] . " 1</th>
				<th align=center width=40px>".$_SESSION['lang']['lebihbasis'] . " 2</th>
				<th align=center width=40px>Brondol</th>
				<input style=display:none id=kodeiddenda value='".implode("##",$iddendapnn)."'>
				";
				#denda header
				foreach($dendapanen as $iddenda => $kddenda){
					$frm[0].="<th align=center ".$tp[$iddenda]." width=30px style=display:none name=inputdenda[] id=p".$iddenda.">".$kddenda."</th>";
				}
		$frm[0].="</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		#=== Isi input detail ===
		$frm[0].="<tbody id=inputdetail>
				<script>inputdetail()</script>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input detail ===	
        $frm[0].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
			<fieldset style=float:left;><legend>" . $_SESSION['lang']['find'] . "</legend>
				<table>
					<tr>
						<td>" . $_SESSION['lang']['namakaryawan'] . "</td><td>:</td>
						<td><input tipe=text class=myinputtext onchange=loaddatadetail() id=namakarydetsch></td>
						
						<td>" . $_SESSION['lang']['blok'] . "</td><td>:</td>
						<td><input tipe=text style=width:100px onchange=loaddatadetail() class=myinputtext id=blokdetsch></td>
						
						<td>" . $_SESSION['lang']['tahuntanam'] . "</td><td>:</td>
						<td><input tipe=text style=width:50px class=myinputtext onchange=loaddatadetail() id=ttdetsch></td>
						
						<td><button class=mybutton onclick=loaddatadetail()>" . $_SESSION['lang']['preview'] . "</button></td>
						<!--<td><button class=mybutton onclick=loaddatadetailxls('','dt')>" . $_SESSION['lang']['excel'] . "</button></td>-->
						<td><button class=mybutton onclick=cancelcari()>" . $_SESSION['lang']['cancel'] . "</button></td>
					</tr>
				
				</table>
			</fieldset>
			<fieldset style=float:left;><legend>" . $_SESSION['lang']['tampilkan'] . " Per ?</legend>
				<table>
					<tr>
						<td><input type=checkbox id=showdetail onchange=loaddatadetail()></td>
						<td>TPH</td>
						<td><input type=checkbox checked id=showblok onchange=loaddatadetail()></td>
						<td>Blok</td>
						<td><input type=checkbox checked id=showkary onchange=loaddatadetail()></td>
						<td>Kary</td>
						<td><input id=showdenda style=display:none value=''></td>
					</tr>
				</table>
			</fieldset>
			
			<div style=clear:both></div>
				<hr>
			<div style=clear:both></div>		
			<div id=loaddatadetail>
				<script>loaddatadetail()</script>
			</div>
		</fieldset>";
        // $tab.=CLOSE_BOX();
		
		#=== TAB ABSENSI ===
		$frm[1]="<table border=0><td valign=top>
			<fieldset style=float:left;height:70px; >
				<legend>Info</legend>
					<table height=25px border=0>
						<tr><td>" . $_SESSION['lang']['divisi'] . "</td>
						<td><select style=\"width:150px;\" title=\"Untuk menampilkan data karyawan dari divisi lain.\" onchange=\"getdata(this.value)\" id=filterdivisiabsensi ".$dis.">".$optDivisi."</select></td></tr>
						
					</table>
			</fieldset>
			</td>
			<td valign=top>
			<fieldset style=float:left;height:70px;>
				<legend>Info</legend>
					<table height=25px>
						<tr><td colspan=3>Silahkan isi absensi karyawan yang <b>tidak melakukan kegiatan di blok</b>, contoh :</td></tr>
						<tr><td>KHT dan PB</td><td>:</td><td>Sakit, Ijin, Cuti, Mangkir atau karyawan bekerja dan bebannya masuk ke biaya umum</td></tr>
						<tr><td>KHL</td><td>:</td><td><b>Hanya jika karyawan bekerja dan bebannya masuk ke biaya umum</b></td></tr>
					</table>
			</fieldset>
			</td>
			
			
			</table>
			<fieldset>
			<legend>Absensi</legend>
			<table border=0 cellpadding=2 cellspacing=1 class=sortable>
			<thead><tr class=rowheader style=height:25px>";
			
		$rows="rowspan=1";	
		$frm[1].="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']."</th>
				<th align=center ".$rows.">".$_SESSION['lang']['namaakun'] . "</th>
				<th align=center ".$rows.">Absensi</th>
				<th align=center ".$rows.">".$_SESSION['lang']['hk2'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['upah'] . " Rp</th>
				<th align=center ".$rows.">".$_SESSION['lang']['premi'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['keterangan'] . "</th>
				<th align=center ".$rows." colspan=2>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			</thead>";
		#==== Form Judul Absensi ====
		
		#=== Isi input Absensi ===
		$optabs = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
		$str="select * from ".$dbname.".sdm_5absensi where status=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$isihk=0;
		while ($val = $res->fetch()) {
			if('H'==$val['kodeabsen']){
				$optabs.="<option value=".$val['kodeabsen']." selected >".$val['kodeabsen']." - ".$val['keterangan']."</option>";
				$isihk=$val['nilaihk'];
			}else{		
				$optabs.="<option value=".$val['kodeabsen'].">".$val['kodeabsen']." - ".$val['keterangan']."</option>";
			}
		}
		
		$kdjurnal="KBNB0";
		$optakun=makeOption($dbname,'keu_5parameterjurnal','jurnalid,noakundebet',"jurnalid='".$kdjurnal."'");
		$akun=$optakun[$kdjurnal];
		$wh=" and substr(noakun,1,3) in ('711')";
		$wh.=" and substr(noakun,1,3) not in ('719','715')";
		$sjnskrj="select * from ".$dbname.".keu_5akun where length(noakun)='7' and namaakun not like '%NON AKTIF%' ".$wh." and aktif='1' order by noakun asc";
		// exit("error".$sjnskrj);
		$optJnsKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=fetchdata($sjnskrj);
		foreach($res as $rjnskrj){
			$d=substr($rjnskrj['noakun'],0,5);
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
				$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
			}
			$e="";
			if($rjnskrj['noakun']==$akun){
				$e="selected";
			}
			$optJnsKerja.="<option value=".$rjnskrj['noakun']." ".$e.">".$rjnskrj['noakun']." - ".$rjnskrj['namaakun']."</option>";
			$n=$d;
			if($d!=$n){
				$optJnsKerja.="</optgroup>";
			}
		}
		$frm[1].="<tbody id=inputdetailabsensi>
				<tr class=rowcontent>
				<td id=no align=center>1</td>
				<td colspan=2><select class=select2 style=width:200px id=karyawanidabsensi onchange=getumrabsensi();>".$optKary."</select></td>
				<td><select class=select2 style=width:180px onmousemove=hapuswarna(this.id); id=noakunabsensi>".$optJnsKerja."</select></td>
				<td><select class=select2 style=width:90px onchange=getnilaihk(); onmousemove=hapuswarna(this.id); id=kodeabsen>".$optabs."</select>
				</td>
				
				<td><input onkeyup=getumrabsensi(); disabled id=jhkabsen class=myinputtextnumber value=".$isihk." onkeypress=\"return angka_doang(event);\" style=\"width:35px;\"></td>
				
				<td><input id=upahabsen disabled class=myinputtextnumber style=\"width:75px;\"></td>
				<td><input type=text style=\"width:75px;\" id=premiabsen class=myinputtextnumber onkeyup=\"z.numberFormat('premiabsen',2)\" nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
				<td><input id=keteranganabsen class=myinputtext style=\"width:200px;\"></td>
				
				
				
				<td align=center width=20px>
					<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"saveabsensi()\" src='images/save.png'/></td>
				<td align=center width=20px>
					<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"clearabsensi()\" src='images/clear.png'/>
				</td>
			</tr><tr  style=display:none>
				<td colspan=8></td>
				<td  align=center>
					<input hidden id=methodabsensi value='insertabsensi'>
					<input hidden id=kodeorgabsensi>
					<img title='Refresh List Data' class=zImgBtn onclick=\"loaddataabsensi()\" style=vertical-align:center;width:15px;height:15px;cursor:pointer  src='images/refresh-512.png'/></td>
				<td  align=center>
					<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() style=vertical-align:center;width:16px;height:16px;cursor:pointer src=\"images/done.png\"/>
				</td>
			</tr>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input Absensi ===	
        $frm[1].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " Absensi</legend>
			<div id=loaddataabsensi></div></fieldset>";
			
		$hfrm[0]=$_SESSION['lang']['panen'];
		$hfrm[1]='Absensi';

		# draw tab, jangan ganti parameter pertama, krn dipakai di javascript
		drawTab('FRM',$hfrm,$frm,175,'100%');
		
		//echo $notransaksi."####".$tab;	
		
	break;
	case'loaddataabsensi':
		$tab.="<table border=0 cellpadding=2 cellspacing=1 class=sortable style=min-width:795px>
			<thead><tr class=rowheader>";
			
		$rows="rowspan=1";	
		$tab.="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center ".$rows.">".$_SESSION['lang']['namaakun'] . "</th>
				<th align=center ".$rows.">Absensi</th>
				<th align=center width=30px ".$rows.">".$_SESSION['lang']['hk2'] . "</th>
				<th align=center width=70px ".$rows.">".$_SESSION['lang']['upah'] . " Rp</th>
				<th align=center width=70px ".$rows.">".$_SESSION['lang']['premi'] . "</th>
				<th align=center width=70px ".$rows.">".$_SESSION['lang']['denda'] . "</th>
				<th align=center width=70px ".$rows.">Grand Total</th>
				<th align=center ".$rows.">".$_SESSION['lang']['keterangan'] . "</th>
				<th align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</th>
			</tr>
			</thead><tbody>";
		
		$thk=$tumr=$tpremi=$ttltk=0;
		$str = "select * from ".$dbname.".sdm_absensidt_vw where 1=1 and norefrensi='".$notransaksi."' and tanggal='".tanggalsystemn($tgl)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while($bar=$res->fetch()){
			$no+=1;
			$optnik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$bar['karyawanid']."'");
			
			if($optnik[$bar['karyawanid']]!=''){
				$bar['nik']=$optnik[$bar['karyawanid']]." - ";
			}
			if($bar['subbagian']!=''){
				$bar['subbagian']=$bar['subbagian']." - ";
			}
			
			$tab.="<tr class=rowcontent id=rowabslist_".$no." style=height:25px>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>" . $bar['nik'].$bar['subbagian'].getNamaKaryawan($bar['karyawanid']). "</td>";

			$tab.="<td >".getNamaAkun($bar['noakun'])."</td>";
			$tab.="<td align=center>".$bar['absensi']."</td>";
			$tab.="<td align=right>".@numb_format($bar['nilaihk'],2)."</td>";
			$tab.="<td align=right>".@numb_format($bar['umr'],2)."</td>";
			$tab.="<td align=right>".@numb_format($bar['premi'],2)."</td>";
			$tab.="<td align=right></td>";
			$tab.="<td align=right>".@numb_format($bar['premi']+$bar['umr'],2)."</td>";
			$tab.="<td>".$bar['penjelasan']."</td>";
			
			$tab.="<td align=center  width=25px>
				<img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"editabsensi('".$bar['karyawanid']."','".$bar['absensi']."','".$bar['nilaihk']."','".$bar['umr']."','".$bar['premi']."','".$bar['penjelasan']."','".$bar['kodeorg']."','".$bar['noakun']."');\">
				</td>
				
				<td align=center  width=25px>
				<img title='".$_SESSION['lang']['delete']."' class=zImgBtn onclick=\"delabsen('".$notransaksi."','".$tgl."','".$bar['kodeorg']."','".$bar['karyawanid']."')\" src='images/skyblue/delete.png'/>
				
				
			</td>";
			$tab.="</tr>";
			
			@$thk+=$bar['nilaihk'];
			@$tumr+=$bar['umr'];
			@$tpremi+=$bar['premi'];
		}
		#sub total absensi
		$tab.="<tr class=rowcontent style=background-color:#AED6F1;font-weight:bold;height:25px>";
		$tab.="<td align=center>".numb_format($no)."</td>";
		$tab.="<td align=center colspan=3>Sub Total (Absensi)</td>";
		$tab.="<td align=right>".numb_format($thk,2)."</td>";
		$tab.="<td align=right>".numb_format($tumr)."</td>";
		$tab.="<td align=right>".numb_format($tpremi)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		$ttltk=$no;
		
		#absensi dari BKM
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=12 bgcolor=#2C3E50></td>";
		$tab.="</tr>";
		$thkb=$tumrb=$tpremib=$tdenda=0;
		$str = "select a.nobkm, a.nik, sum(a.jumlahhk) as jhk, sum(a.upahkerja) as umr, sum(a.upahpremi+a.premibasis+a.upahpremilebihbasis+a.upahpremilebihbasis2+a.premibrondol) as insentif,sum(a.rupiahpenalty) as denda from " . $dbname . ".kebun_prestasi a where a.notransaksi='" . $notransaksi . "' group by a.nik"; #exit('error'.$str);
		$res = fetchdata($str);
		$no=0;
		foreach($res as $bar){
			$no++;$optsbg=array();
			$optnik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$bar['nik']."'");
			$optsbg=makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$bar['nik']."'");
			
			if($optnik[$bar['nik']]!=''){
				$bar['nik2']=$optnik[$bar['nik']]." - ";
			}
			$bar['subbagian']="";
			if($optsbg[$bar['nik']]!=''){
				$bar['subbagian']=$optsbg[$bar['nik']]." - ";
			}
			if($bar['umr']!=''){
				$bar['absensi']="H";
			}else{
				$bar['absensi']="";
			}
			
			$tab.="<tr class=rowcontent style=color:gray;height:25px title=\"Untuk melakukan edit atau delete silahkan buka di tab Panen\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left colspan=2>" . $bar['nik2'].$bar['subbagian'].getNamaKaryawan($bar['nik']) . "</td>";
			$tab.="<td align=center>".$bar['absensi']."</td>";
			$tab.="<td align=right>".numb_format($bar['jhk'],2)."</td>";
			$tab.="<td align=right>".numb_format($bar['umr'])."</td>";
			$tab.="<td align=right>".numb_format($bar['insentif'])."</td>";
			$tab.="<td align=right>".numb_format($bar['denda'])."</td>";
			$tab.="<td align=right>".numb_format(($bar['umr']+$bar['insentif'])-$bar['denda'])."</td>";
			$tab.="<td></td>";	
			$tab.="<td width=20px></td>";	
			$tab.="<td width=20px></td>";
			
			@$thkb+=$bar['jhk'];
			@$tumrb+=$bar['umr'];
			@$tpremib+=$bar['insentif'];
			@$tdenda+=$bar['denda'];
		}
		#sub total bkm
		$tab.="<tr class=rowcontent style=background-color:#AED6F1;font-weight:bold;height:25px>";
		$tab.="<td align=center>".numb_format($no)."</td>";
		$tab.="<td align=center colspan=3>Sub Total (Panen)</td>";
		$tab.="<td align=right>".numb_format($thkb,2)."</td>";
		$tab.="<td align=right>".numb_format($tumrb)."</td>";
		$tab.="<td align=right>".numb_format($tpremib)."</td>";
		$tab.="<td align=right>".numb_format($tdenda)."</td>";
		$tab.="<td align=right>".numb_format(($tumr+$tpremib)-$tdenda)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		$ttltk+=$no;
		
		#Grand Total
		$tab.="<tr class=rowcontent style=background-color:#A3E4D7;font-weight:bold;height:25px>";
		$tab.="<td align=center>".numb_format($ttltk)."</td>";
		$tab.="<td align=center colspan=3>Grand Total (Absensi + Panen)</td>";
		$tab.="<td align=right>".numb_format($thkb+$thk,2)."</td>";
		$tab.="<td align=right>".numb_format($tumrb+$tumr)."</td>";
		$tab.="<td align=right>".numb_format($tpremib+$tpremi)."</td>";
		$tab.="<td align=right>".numb_format($tdenda)."</td>";
		$tab.="<td align=right>".numb_format(($tumrb+$tumr+$tpremib+$tpremi)-$tdenda)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		
		
		/* if($_SESSION['empl']['subbagian']==''){
			$str = "select substr(kodeorg,1,6) as divisi from ".$dbname.".kebun_prestasi where notransaksi='".$param['notransaksi']."' group by divisi";
			$res = fetchdata($str);
			foreach($res as $bar){
				$div[$bar['divisi']]=$bar['divisi'];
			}
			if(count($res)>0){
				$whr=" and kodeorg in ('".implode("','",$div)."')";
			}else{
				$whr=" and kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and length(kodeorg)=6";
			}
		}else{
			$whr=" and kodeorg = '".$_SESSION['empl']['subbagian']."'";			
		} */
		$whr="";
		$whr=" and kodeorg = '".$param['divisi']."'";
		$str = "select * from ".$dbname.".sdm_absensidt where tanggal='".tanggalsystemn($param['tgl'])."' and norefrensi='' and nobkm='' ".$whr.""; #exit('error'.$str);
		$res = fetchdata($str);
		if(count($res)>0){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=11 bgcolor=#2C3E50></td>";
			$tab.="</tr>";
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=11 bgcolor=#E7E7FF style=font-weight:bold>Informasi karyawan yang di absen melalui menu SDM - Transaksi - Absensi</td>";
			$tab.="</tr>";
			$no=0;$thk=$tumr=$tpremi=0;
			foreach($res as $bar){
				$optnik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$bar['karyawanid']."'");
				$optsbg=makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$bar['karyawanid']."'");
				$nikx=$divx="";
				if($optnik[$bar['karyawanid']]!=''){
					$nikx=$optnik[$bar['karyawanid']]." - ";
				}
				if($optsbg[$bar['karyawanid']]!=''){
					$divx=$optsbg[$bar['karyawanid']]." - ";
				}
				
				$no++;
				$tab.="<tr class=rowcontent style=background-color:#E7E7FF;color:gray;>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".$nikx."".$divx."".getNamaKaryawan($bar['karyawanid'])."</td>";
				$tab.="<td align=left>".getNamaAkun($bar['noakun'])."</td>";
				$tab.="<td align=center>".$bar['absensi']."</td>";
				$tab.="<td align=right>".numb_format($bar['hk'],2)."</td>";
				$tab.="<td align=right>".numb_format($bar['umr'],2)."</td>";
				$tab.="<td align=right>".numb_format($bar['premi'],2)."</td>";
				$tab.="<td></td>";
				$tab.="<td align=left>".$bar['penjelasan']."</td>";
				$tab.="<td width=20px></td>";
				$tab.="<td width=20px></td>";
				$tab.="</tr>";
				
				@$thk+=$bar['hk'];
				@$tumr+=$bar['umr'];
				@$tpremi+=$bar['premi'];
			}
			$tab.="<tr class=rowcontent style=background-color:#AED6F1;font-weight:bold>";
			$tab.="<td align=center>".numb_format($no)."</td>";
			$tab.="<td align=center colspan=3>Sub Total SDM Absensi</td>";
			$tab.="<td align=right>".numb_format($thk,2)."</td>";
			$tab.="<td align=right>".numb_format($tumr)."</td>";
			$tab.="<td align=right>".numb_format($tpremi)."</td>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td width=20px></td>";
			$tab.="<td width=20px></td>";
			$tab.="</tr>";
		}
		
		
		$tab.="</tbody></table>";
	echo $tab;
	break;
	case'insertabsensi':
	try {
	$owlPDO->beginTransaction();
	
		if($param['jhk']==''){
			$param['jhk']='0';
		}
		if($param['upah']==''){
			$param['upah']='0';
		}
		if($param['premi']==''){
			$param['premi']='0';
		}
		if($param['jhk']>0 and $param['upah']==0){
			throw new PDOException("Jika nilai HK > 0 maka nilai upah tidak boleh kosong.");
		}
		
		
		cekPrestasi($param);
		
		#validasi maksimal HK BHL
		cekmaxnilaihk($param['karyawanid'],tanggalsystemn($param['tgl']),'1','0','new',$exit='0');
		
		
		$optdivisi = makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$param['karyawanid']."'");
		if($optdivisi[$param['karyawanid']]!=''){
			$divisi=$optdivisi[$param['karyawanid']];
		}else{
			$divisi=$param['kodeorg'];
		}
			
		# Cek sudah ada atau belum ???
		$str = "select * from " . $dbname . ".sdm_absensiht where tanggal='".tanggalsystemn($param['tgl'])."' and kodeorg='".$divisi."'";
		$res=count(fetchData($str));
		# jika belum ada di ht maka insert dulu
		if($res==0){
			$data = array(
				'tanggal' => tanggalsystemn($param['tgl']),
				'kodeorg' => $divisi,
				'periode' => substr(tanggalsystemn($param['tgl']),0,7),
				'updateby'=> $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}

			# Insert sdm_absensiht
			$query = insertQuery($dbname,'sdm_absensiht',$data,$cols);
			$owlPDO->exec($query);
		}
		
			
			$data = array(
				'kodeorg'   => $divisi,
				'tanggal'   => tanggalsystemn($param['tgl']),
				'karyawanid'=> $param['karyawanid'],
				'noakun'    => $param['noakun'],
				'absensi'   => $param['kodeabsen'],
				'premi'     => $param['premi'],
				'hk'        => $param['jhk'],
				'umr'       => $param['upah'],
				'penjelasan'=> $param['keterangan'],
				'norefrensi'=> $param['notransaksi'],
				'nobkm'     => $param['nobkm']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}

			# Insert sdm_absensidt
			$query = insertQuery($dbname,'sdm_absensidt',$data,$cols);
			$owlPDO->exec($query);
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
	case'updateabsensi':
	try {
	$owlPDO->beginTransaction();

		if($param['jhk']==''){
			$param['jhk']='0';
		}
		if($param['upah']==''){
			$param['upah']='0';
		}
		if($param['premi']==''){
			$param['premi']='0';
		}
		
		if($param['jhk']>0 and $param['upah']==0){
			throw new PDOException("Jika nilai HK > 0 maka nilai upah tidak boleh kosong.");
		}
		
		# Validasi penginputan
		cekPrestasi($param);
		
		#validasi maksimal HK BHL
		cekmaxnilaihk($param['karyawanid'],tanggalsystemn($param['tgl']),'1','1','edit',$exit='0');
		
		# ==========================================================================================
		$data = array(
			'noakun'    =>$param['noakun'],
			'absensi'   =>$param['kodeabsen'],
			'hk'        =>$param['jhk'],
			'umr'       =>$param['upah'],
			'premi'     =>$param['premi'],
			'penjelasan'=>$param['keterangan']
		);
		$where = "norefrensi='".$param['notransaksi']."' and tanggal='".tanggalsystemn($param['tgl'])."' and karyawanid='".$param['karyawanid']."' and kodeorg='".$param['kodeorgabsensi']."'";
		
		# Update sdm_absensidt
		$query = updateQuery($dbname,'sdm_absensidt',$data,$where);
		$owlPDO->exec($query);
		# ==========================================================================================
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
	case'delabsen':
	try {
	$owlPDO->beginTransaction();
	
        $str = "delete from " . $dbname . ".sdm_absensidt where norefrensi='".$notransaksi."' and karyawanid='".$karyawanid."' and tanggal='".tanggalsystemn($tgl)."' and kodeorg='".$kodeorg."'";
		$owlPDO->exec($str);
		
		#cek masih ada tidak detailnya
		$str = "SELECT * FROM " . $dbname . ".sdm_absensidt where tanggal='".tanggalsystemn($tgl)."' and kodeorg='".$kodeorg."'";
		$res=fetchData($str);
		if(count($res)==0){
			#hapus ht nya
			$str = "delete from " . $dbname . ".sdm_absensiht where tanggal='".tanggalsystemn($tgl)."' and kodeorg='".$kodeorg."'";
			$owlPDO->exec($str);
		}
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}

	break;
	case'inputdetail':
	

	echo"<tr class=rowcontent id=row>";
		echo"<td id=no align=center>1
			<input hidden id=sesiold>
			<input hidden id=tphold>
			<input hidden id=noreferensi>
			</td>
			<td colspan=2><select class=select2 style=width:200px onchange=getDataDetail('',this.id) onmousemove=hapuswarna(this.id) id=karyawanid>".$optKary."</select></td>
			
			<td colspan=2><select class=select2 style=width:100px onchange=getDataDetail('',this.id) onmousemove=hapuswarna(this.id) id=blok>".$optBlok."</select></td>
			<td><input id=tph onkeyup=getdatatph(); nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength='2' style=\"width:30px;\"></td>
			<td><select style=width:40px id=sesi>".$optsesi."</select></td>
			
			<td><input id=tt disabled class=myinputtextnumber style=\"width:35px;\"></td>
			<td><input id=bjr disabled class=myinputtextnumber style=\"width:35px;\"></td>
			<td><input id=hapanen onmousemove=hapuswarna(this.id) onkeyup=\"z.numberFormat('hapanen',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>
			<td><input id=jjgpanen onmousemove=hapuswarna(this.id) onkeyup=\"getDataDetail('',this.id)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td><input id=jjgbasis disabled class=myinputtextnumber style=\"width:40px;\"></td>
			<td><input id=brdpanen onkeyup=\"getDataDetail('',this.id)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td><input id=kgpanen disabled class=myinputtextnumber style=\"width:40px;\"></td>
			<td><input id=hk disabled class=myinputtextnumber style=\"width:30px;\"></td>
			<td><input id=upah disabled class=myinputtextnumber style=\"width:40px;\"></td>
			<td><input id=dendaupah disabled class=myinputtextnumber style=\"width:40px;\"></td>
			
			<td style=\"display:none;\" title=\"Hanya untuk karyawan KHL jika HK = 0 dan upah dimasukkan ke dalam premi\"><input onclick=hapuswarna(this.id); id=upahpremi disabled  class=myinputtextnumber style=\"width:40px;\"></td>
			
			<td><input id=basis onkeyup=\"z.numberFormat('basis',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>
			<td><input id=basis2_ onkeyup=\"z.numberFormat('basis2_',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>
			<td><input id=lbasis onkeyup=\"z.numberFormat('lbasis',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>
			<td><input id=lbasis2_ onkeyup=\"z.numberFormat('lbasis2_',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>
			<td><input id=rpbrondol onkeyup=\"z.numberFormat('rpbrondol',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>";
			
			#denda detail input
			foreach($dendapanen as $iddenda => $kddenda){
				echo"<td style=display:none id=pd".$iddenda." name=inputdenda[]><input ".$tp[$iddenda]."  id=penalti".$iddenda." onkeyup=getHitungDenda(0,this) nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
			}
			
			echo"<td><input id=denda_rp disabled nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:45px;\"></td>
			
			<td align=center width=19px>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=modedetail value='new'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
			</td><td align=center width=20px>
				<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail()\" src='images/clear.png'/>
			</td>
			<td align=center width=20px>
				<img title='Preview Basis Panen' class=zImgBtn onclick=\"getbasispnn('".$jenispremi."','detail','')\" src='images/book_icon.gif'/>
			</td>
        </tr><tr>
			<td colspan=23><div id=inforekappnn></div></td>
			<td id=pfot name=inputdenda[] colspan=".count($dendapanen)."></td>
			<input id=jlhbrs style=display:none>
			<td align=center colspan=3 valign=top><button class=mybutton onclick=\"displayList()\">".$_SESSION['lang']['done']."</button></td>
        </tr>
        
		";
	break;
	case'getdatatph':
		$str = "select * from " . $dbname . ".kebun_5tph where kode='".$param['tph']."' and kodeorg='".$param['blok']."'";
		$res = fetchData($str)[0];
		$luas = $res['luas'];
		
		$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."' and nik='".$param['karyawanid']."' and tph='".$param['tph']."' and sesi='".$param['sesi']."'"; #exit("error".$str);
		$jlh = count(fetchData($str));
		
		echo $luas."##".$jlh;
		
	break;
	case'getdata':
		echo $optKary."######".$optBlok;
	break;
	
	case'getdatamandor':
	$whereKary='';
	$whereKary= " and tipekaryawan in (1,2,3,4,6) and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".tanggalsystemn($tgl)."')";
	
	$str = "select a.karyawanid,b.namakaryawan,b.nik, b.subbagian from ".$dbname.".kebun_5mandor a
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where statusaktif='1' and mandorid='".$mandor."' ".$whereKary." order by b.namakaryawan asc";
	$count=fetchData($str);
	$tab='';
	if(count($count)==0){
		$tab.="<tr style=color:blue; class=rowcontent>";
		$tab.="<td align=center id=infokarymandoran colspan=".(count($dendapanen)+23)." style=font-size:15px;>Pastikan kolom <b>Mandor</b> pada Header terisi, dan atau daftarkan terlebih dahulu nama karyawan per kemandoran melalui menu : <b>Kebun - Setup - Mandor</b></td>";
		$tab.="</tr>";
	}else{		
		$tab.="<tr class=rowcontent>";
		$tab.="
				<td colspan=3></td>
				<td colspan=2 align=center valign=center style=font-weight:bold;>Copy <input type='checkbox' title='Aktif / Non Aktif' id='copyblok' class='zImgBtn' style='position:relative;top:3px;left:3px;'></td>
				<td colspan=17></td>
				<td style=display:none name=inputdenda[] id=headceck colspan=".count($dendapanen)."></td>
				<td colspan=1></td>
				<td colspan=3></td>
			";
		$tab.="</tr>";
	}
	
	$no='0';
	$res = fetchdata($str);
	foreach($res as $bar){
		$no++;
		$tab.="<tr class=rowcontent id=row".$no.">";
		$tab.="	<td style=display:none><input hidden id=sesiold".$no.">
					<input hidden id=tphold".$no.">
					<input hidden id=noreferensi".$no."></td>";
		$tab.="	<td align=center>".$no."</td>
		<td style=display:none><input id=karyawanid".$no." value=".$bar['karyawanid']."></td>
		<td id=kary".$no." colspan=2>".$bar['nik']." - ".$bar['namakaryawan']."</td>
		
		<td colspan=2><select style=width:100px onchange=\"getDataDetail(".$no.",this.id)\" id=blok".$no.">".@$optBlok."</select></td>
		
		<td><input id='tph".$no."' onkeyup=getdatatph('".$no."'); nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength='2' style=\"width:30px;\"></td>
		<td><select style=width:40px id='sesi".$no."'>".$optsesi."</select></td>
			
		<td><input id=tt".$no." disabled class=myinputtextnumber style=\"width:35px;\"></td>
		<td><input id=bjr".$no." disabled class=myinputtextnumber style=\"width:35px;\"></td>
		<td><input id=hapanen".$no." onmousemove=hapuswarna(this.id) onkeyup=\"z.numberFormat('hapanen".$no."',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>
		<td><input id=jjgpanen".$no." onmousemove=hapuswarna(this.id) onkeyup=\"getDataDetail(".$no.",this.id)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
		<td><input id=jjgbasis".$no." disabled class=myinputtextnumber style=\"width:40px;\"></td>
			
		<td><input id=brdpanen".$no." onkeyup=\"getDataDetail(".$no.",this.id)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
		<td><input id=kgpanen".$no." disabled class=myinputtextnumber style=\"width:40px;\"></td>
		<td><input id=hk".$no." disabled class=myinputtextnumber style=\"width:30px;\"></td>
		<td><input id=upah".$no." disabled class=myinputtextnumber style=\"width:40px;\"></td>
		<td><input id=dendaupah".$no." disabled class=myinputtextnumber style=\"width:40px;\"></td>
		
		<td style=display:none; title=\"Hanya untuk karyawan KHL jika HK = 0 dan upah dimasukkan ke dalam premi\"><input onclick=hapuswarna(this.id); id=upahpremi".$no." disabled  class=myinputtextnumber style=\"width:40px;\"></td>
		
		<td><input id=basis".$no." onkeyup=\"z.numberFormat('basis".$no."',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>
		<td><input id=basis2_".$no." onkeyup=\"z.numberFormat('basis2_".$no."',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>
		<td><input id=lbasis".$no." onkeyup=\"z.numberFormat('lbasis".$no."',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>
		<td><input id=lbasis2_".$no." onkeyup=\"z.numberFormat('lbasis2_".$no."',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>
		<td><input id=rpbrondol".$no." onkeyup=\"z.numberFormat('rpbrondol".$no."',2)\" disabled class=myinputtextnumber style=\"width:40px;\"></td>";
		
		#denda detail input
		foreach($dendapanen as $iddenda => $kddenda){
			$tab.="<td style=display:none id=pd".$iddenda."".$no." name=inputdenda[]><input ".$tp[$iddenda]." id=penalti".$iddenda."".$no." onkeyup=getHitungDenda(".$no.",this) nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		}
		
		$tab.="<td><input id=denda_rp".$no." disabled nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:45px;\"></td>
				
		<td align=center width=20px><input type=hidden id=method value='insert'>
			<img title='" . $_SESSION['lang']['save']."' class=zImgBtn onclick=\"savedetail(".$no.")\" src='images/save.png'/>
		</td><td align=center width=20px>
			<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail(".$no.")\" src='images/clear.png'/>
		</td><td align=center width=20px>
			<img title='Preview Basis Panen' class=zImgBtn onclick=\"getbasispnn('".$jenispremi."','detail','".$no."')\" src='images/book_icon.gif'/>
		</td>
	</tr>";
	}
	$tab.="<tr>
		
		<td colspan=22><div id=inforekappnnall></div></td>
		<td id=pfot name=inputdenda[] colspan=".count($dendapanen)."><td>
		<td align=center colspan=3 valign=top>
			<input id=jlhbrs  style=display:none value=".$no.">
			<img style=display:none title='" . $_SESSION['lang']['saveall']."' class=zImgBtn onclick=\"saveAll(".$no.")\" src='images/save.png'/>
			
			<button class=mybutton onclick=\"saveAll(".$no.")\">SaveAll</button>
		</td>
		<td style=display:none align=center width=20px>
			<img style=display:none title='Refresh' style=vertical-align:center;width:15px;height:15px;cursor:pointer  onclick=\"cancelcari()\" src='images/refresh-512.png'/>
		</td>
		<td style=display:none align=center width=20px>
			<img style=display:none title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() style=vertical-align:center;width:16px;height:16px;cursor:pointer src=\"images/done.png\"/>
		</td>
	</tr>";
	
	echo $tab."######".$no;
	break;
	
	case'loaddatadetail':
	if($param['showdenda']=='1'){
		$disdenda="";
	}else{
		$disdenda="style=display:none";
	}
	
	$border="border=0";
	if($tipe=='excel'){$border="border=1";}
	$rows="rowspan=2";	
	$tab="<table id=tabledt cellpadding=3 cellspacing=1 ".$border." class=sortable >
			<thead><tr class=rowheader>
			<th align=center ".$rows." width=20px>+/-</th>
			<th align=center ".$rows." width=20px>No</th>
			<th align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</th>
			<th align=center colspan=3>".$_SESSION['lang']['nomor'] . "</th>
			<th align=center ".$rows." width=30px>".$_SESSION['lang']['tahuntanam'] . "</th>
			<th hidden align=center ".$rows." width=30px>Kontanan</th>
			<th align=center ".$rows." width=40px>BJR</th>
			<th align=center colspan=5>".$_SESSION['lang']['hasilkerja2'] . "</th>
			<th align=center colspan=3>".$_SESSION['lang']['jumlah']."</th>
			<th align=center colspan=5>".$_SESSION['lang']['premilebihbasis']."</th>
			<th align=center colspan=".count($dendapanen)." ".$disdenda." id=pheaddt name=listdenda[] title='Click to Hide' onclick=hidedendav2('listdenda[]') ><font color=Orange><b>".$_SESSION['lang']['denda']."</font></b></th>
			<th align=center ".$rows." title='Click to Unhide' id=pheadrpdt onclick=hidedendav2('listdenda[]') style=width:50px;color:Orange;font-weight:bold;>".$_SESSION['lang']['denda']." Rp</th>
			
			<th align=center ".$rows." >Grand Total</th>
			<th align=center ".$rows." colspan=2>" . $_SESSION['lang']['action'] . "</th>
		</tr>
		<tr>
			<th align=center>".$_SESSION['lang']['blok'] . "</th>
			<th align=center>TPH</th>
			<th align=center>Sesi</th>
			
			<th align=center>".$_SESSION['lang']['ha'] . "</th>
			<th align=center>".$_SESSION['lang']['jjg'] . "</th>
			<th align=center>Jjg<br>Basis</th>
			<th align=center>Kg Brd</th>
			<th align=center>".$_SESSION['lang']['kg'] . "</th>
			<th align=center>".$_SESSION['lang']['hk2'] . "</th>
			<th align=center>".$_SESSION['lang']['upah'] . "</th>
			<th align=center>".$_SESSION['lang']['denda'] . "</th>
			<th hidden align=center title=\"Hanya untuk karyawan KHL jika HK = 0 dan upah dimasukkan ke dalam premi\" width=40px>Upah Premi</th>
			<th align=center>".$_SESSION['lang']['basic'] . " 1</th>
			<th align=center>".$_SESSION['lang']['basic'] . " 2</th>
			<th align=center width=40px>".$_SESSION['lang']['lebihbasis'] . " 1</th>
			<th align=center width=40px>".$_SESSION['lang']['lebihbasis'] . " 2</th>
			<th align=center width=40px>Brondol</th>";
			
			#denda header list data
			foreach($dendapanen as $iddenda => $kddenda){
				$tab.="<th align=center ".$tp[$iddenda]." width=30px ".$disdenda." name=listdenda[] id=pdt##".$iddenda.">".$kddenda."</th>";
			}
			
		$tab.="</tr>
		</thead>";
		
        $no = 0;
		$where = "";
		if($namakary!=''){
			$where.=" and (b.karyawanid like '%".$namakary."%' or b.nik like '%".$namakary."%' or b.namakaryawan like '%".$namakary."%')";
		}
		if($blok!=''){
			$where.=" and a.kodeorg like '%".$blok."%'";
		}
		if($tt!=''){
			$where.=" and a.tahuntanam like '%".$tt."%'";
		}
		
		$countkar=$data=array();
		$str = "select a.*,b.namakaryawan,b.nik as nik2, b.subbagian from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid  where a.notransaksi='" . $notransaksi . "' ".$where." order by b.namakaryawan asc";
        $res = fetchdata($str);
		foreach($res as $bar){
			$nmkar[$bar['nik']]=$bar['namakaryawan'];
			$nik2[$bar['nik']]=$bar['nik2'];
			$subbg[$bar['nik']]=$bar['subbagian'];
			$penlty[$bar['nik']]+=$bar['upahpenalty'];
			$ket[$bar['nik']]=$bar['keterangan'];
			$thntnm[$bar['kodeorg']]=$bar['tahuntanam'];
			$bjr[$bar['kodeorg']]=$bar['bjr'];
			
			$data[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]=$bar['noreferensi'];
			$urut[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]=$bar['nourut'];
			$jjg[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['hasilkerja'];
			$ha[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['luaspanen'];
			$brd[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['brondolan'];
			$kg[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['hasilkerjakg'];
			$hk[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['jumlahhk'];
			$upah[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahkerja'];
			$upen[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahpenalty'];
			$upre[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahpremi'];
			$sb[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['premibasis'];
			$sb2[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['premibasis2'];
			$lb1[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahpremilebihbasis'];
			$lb2[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahpremilebihbasis2'];
			$rpbrd[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['premibrondol'];
			$rppen[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['rupiahpenalty'];
			$norma[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['norma'];
			
			$countkar[$bar['nik']][$bar['kodeorg']]=1;
			#$countkar[$bar['nik']][$bar['kodeorg']]=1;
		}
		
		$jmlkary=array();
		foreach($countkar as $nik => $vblok){
			foreach($vblok as $blok => $val){
				$jmlkary[$nik]+=$val;
			}			
		}
		
		if($param['showdetail']=='0'){
			$disdet="style=display:none;height:25px;";
		}else{
			$disdet="style=display:'';height:25px;";
		}
		if($param['showblok']=='0'){
			$disblok="style=background-color:#C9FEFA;display:none;height:25px;";
		}else{
			$disblok="style=background-color:#C9FEFA;height:25px;";
		}
		if($param['showkary']=='0'){
			$diskary="style=background-color:#FDEDEC;display:none;height:25px;";
		}else{
			$diskary="style=background-color:#FDEDEC;height:25px;";
		}
		
		if(count($data)==0){
			$tab.="<tr class=rowcontent>
						<td id=datadetailkosong colspan=22 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>
						<td name=listdenda[] ".$disdenda." colspan=".count($dendapanen)."></td>
						<td colspan=3></td>
					</tr>";
		}else{
			$jlhdet=$nokar=$jlhblok=0;
			foreach($data as $nik => $vblok){
				foreach($vblok as $blok => $vtph){
					$nokar++;
					foreach($vtph as $tph => $vsesi){
						foreach($vsesi as $sesi => $vreff){
							foreach($vreff as $reff){
								$jlhdet++;
								$jlhblok=$nokar+$jlhdet;
							}
						}
					}
				}
			}
			
			$no=$nokar=$nokarnik=0;

			foreach($data as $nik => $vblok){
				$rownik=0;$nokarnik++;
				foreach($vblok as $blok => $vtph){
				$nokar++;$row=0;
					foreach($vtph as $tph => $vsesi){
						foreach($vsesi as $sesi => $vreff){
							foreach($vreff as $reff){
								$row++;$rownik++;$no++;
								$bgcolor=$title=$color=$cp=$doublec="";
								$doublec="style=cursor:pointer; title='Double click untuk filter.'";
								if($jmlkary[$nik]>1){
									$bgcolor="style=color:#06BA10;cursor:pointer;";
									$bgcolor.=" title = 'Karyawan Panen lebih dari 1 blok.'";
								}
								if($subbg[$nik]!=substr($blok,0,6)){
									$color="style=color:blue;cursor:pointer;";
									$color.=" title =\"Karyawan melakukan asistensi / lokasi tugas karyawan berbeda dengan lokasi kerjanya.\nLokasi Tugas Karyawan : ".$subbg[$nik]."\nLokasi Bekerja Karyawan : ".substr($blok,0,6)."\"";
								}
								if($penlty[$nik]){
									$cp="style=color:red; title=\"Untuk karyawan KHT jika tidak sampai 1 HK maka akan ada potongan upah.\"";
								}
								if($nik2[$nik]!=''){$nkkry=$nik2[$nik]." - ";}
								if($subbg[$nik]!=''){$divkry=$subbg[$nik]." - ";}
								$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$blok."'");
								if($nmorg[$blok]==$blok){
									$blkkry=substr($blok,6,4);
								}else{
									$blkkry=$nmorg[$blok];
								}
								
								$tab.="<tr class=rowcontent onclick=getmark(this.id); id=rowdetail".$no." ".$disdet.">";
								$tab.="<td></td>";
								$tab.="<td></td>";
								$tab.="<td align=left ".$color." ".$doublec." ondblclick=cariby('".$nik."','namakary')><font style=font-size:10px;color:gray;>".$nkkry.$divkry.getNamaKaryawan($nik)."</font></td>";
								$tab.="<td align=center ".$bgcolor." ".$doublec." ondblclick=cariby('".$blok."','blok')>".$blkkry."</td>";
								$tab.="<td align=center>".substr($tph,10,10)."</td>";
								$tab.="<td align=center>".$sesi."</td>";
								$tab.="<td align=center ".$bgcolor." ".$doublec." ondblclick=cariby('".$thntnm[$blok]."','tt') ".$bgcolor.">".$thntnm[$blok]."</td>";
								if($ket[$nik]==''){$ket[$nik]="KERJA";}
								$tab.="<td align=center hidden>".$ket[$nik]."</td>";
								$tab.="<td align=right>".@numb_format($bjr[$blok],2) . "</td>";
								$tab.="<td align=right>".@numb_format($ha[$nik][$blok][$tph][$sesi][$reff],2)."</td>";
								$tab.="<td align=right>".@numb_format($jjg[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($norma[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($brd[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($kg[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($hk[$nik][$blok][$tph][$sesi][$reff],2)."</td>";
								$tab.="<td align=right>".@numb_format($upah[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right ".$cp.">".@numb_format($upen[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right hidden>".@numb_format($upre[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($sb[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($sb2[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($lb1[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($lb2[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($rpbrd[$nik][$blok][$tph][$sesi][$reff])."</td>";
								
								@$st_ha[$nik][$blok]+=$ha[$nik][$blok][$tph][$sesi][$reff];
								@$st_jjg[$nik][$blok]+=$jjg[$nik][$blok][$tph][$sesi][$reff];
								@$st_norma[$nik][$blok]+=$norma[$nik][$blok][$tph][$sesi][$reff];
								@$st_brd[$nik][$blok]+=$brd[$nik][$blok][$tph][$sesi][$reff];
								@$st_kg[$nik][$blok]+=$kg[$nik][$blok][$tph][$sesi][$reff];
								@$st_upah[$nik][$blok]+=$upah[$nik][$blok][$tph][$sesi][$reff];
								@$st_hk[$nik][$blok]+=$hk[$nik][$blok][$tph][$sesi][$reff];
								@$st_upen[$nik][$blok]+=$upen[$nik][$blok][$tph][$sesi][$reff];
								@$st_sb[$nik][$blok]+=$sb[$nik][$blok][$tph][$sesi][$reff];
								@$st_sb2[$nik][$blok]+=$sb2[$nik][$blok][$tph][$sesi][$reff];
								@$st_lb1[$nik][$blok]+=$lb1[$nik][$blok][$tph][$sesi][$reff];
								@$st_lb2[$nik][$blok]+=$lb2[$nik][$blok][$tph][$sesi][$reff];
								@$st_rpbrd[$nik][$blok]+=$rpbrd[$nik][$blok][$tph][$sesi][$reff];
								@$st_rppen[$nik][$blok]+=$rppen[$nik][$blok][$tph][$sesi][$reff];
								@$st_upre[$nik][$blok]+=$upre[$nik][$blok][$tph][$sesi][$reff];
								
								@$stn_ha[$nik]+=$ha[$nik][$blok][$tph][$sesi][$reff];
								@$stn_jjg[$nik]+=$jjg[$nik][$blok][$tph][$sesi][$reff];
								@$stn_norma[$nik]+=$norma[$nik][$blok][$tph][$sesi][$reff];
								@$stn_brd[$nik]+=$brd[$nik][$blok][$tph][$sesi][$reff];
								@$stn_kg[$nik]+=$kg[$nik][$blok][$tph][$sesi][$reff];
								@$stn_upah[$nik]+=$upah[$nik][$blok][$tph][$sesi][$reff];
								@$stn_hk[$nik]+=$hk[$nik][$blok][$tph][$sesi][$reff];
								@$stn_upen[$nik]+=$upen[$nik][$blok][$tph][$sesi][$reff];
								@$stn_sb[$nik]+=$sb[$nik][$blok][$tph][$sesi][$reff];
								@$stn_sb2[$nik]+=$sb2[$nik][$blok][$tph][$sesi][$reff];
								@$stn_lb1[$nik]+=$lb1[$nik][$blok][$tph][$sesi][$reff];
								@$stn_lb2[$nik]+=$lb2[$nik][$blok][$tph][$sesi][$reff];
								@$stn_rpbrd[$nik]+=$rpbrd[$nik][$blok][$tph][$sesi][$reff];
								@$stn_rppen[$nik]+=$rppen[$nik][$blok][$tph][$sesi][$reff];
								@$stn_upre[$nik]+=$upre[$nik][$blok][$tph][$sesi][$reff];
								
								@$tluas+=$ha[$nik][$blok][$tph][$sesi][$reff];
								@$tjjg+=$jjg[$nik][$blok][$tph][$sesi][$reff];
								@$tnorma+=$norma[$nik][$blok][$tph][$sesi][$reff];
								@$tbrd+=$brd[$nik][$blok][$tph][$sesi][$reff];
								@$tkg+=$kg[$nik][$blok][$tph][$sesi][$reff];
								@$tupah+=$upah[$nik][$blok][$tph][$sesi][$reff];
								@$thk+=$hk[$nik][$blok][$tph][$sesi][$reff];
								@$tdenda+=$upen[$nik][$blok][$tph][$sesi][$reff];
								@$tpbss+=$sb[$nik][$blok][$tph][$sesi][$reff];
								@$tpbss2+=$sb2[$nik][$blok][$tph][$sesi][$reff];
								@$tplb+=$lb1[$nik][$blok][$tph][$sesi][$reff];
								@$tplb2+=$lb2[$nik][$blok][$tph][$sesi][$reff];
								@$trpbrd+=$rpbrd[$nik][$blok][$tph][$sesi][$reff];
								@$trrp+=$rppen[$nik][$blok][$tph][$sesi][$reff];
								@$tupahpremi+=$upre[$nik][$blok][$tph][$sesi][$reff];
								
								#denda list data 
								$strd = ""; $denda=array();
								$strd = "select * from " . $dbname . ".kebun_mutubuah where notransaksi='".$notransaksi."' and kodeorg='".$blok."' and nik='".$nik."' and tph='".$tph."' and nourut='".$urut[$nik][$blok][$tph][$sesi][$reff]."' and sesi='".$sesi."' and noreferensi='".$reff."'";
								$resd = fetchdata($strd);
								foreach($resd as $bard){
									$denda[$bard['idjenis']]=$bard['nilai'];
								}
								$edit=""; $align=" align=right ";$nn=$disdenda;
								foreach($dendapanen as $iddenda => $kddenda){
									@$tab.="<td ".$align." ".$nn." ".$tplistdata[$iddenda]."\nRp => ".$denda[$iddenda]." x ".$harga[$iddenda]." = ".@numb_format($denda[$iddenda]*$harga[$iddenda])." \" width=30px name=listdenda[] id=pddt##".$iddenda."##".$no.">".@numb_format($denda[$iddenda])."</td>";
									@$ttlp[$iddenda]+=$denda[$iddenda];
									@$edit.="####".$denda[$iddenda];
									
									$st_denda[$iddenda][$nik][$blok]+=$denda[$iddenda];
								}
								$gtperkar[$nik][$blok][$tph][$sesi][$reff]=(($upah[$nik][$blok][$tph][$sesi][$reff]-$upen[$nik][$blok][$tph][$sesi][$reff])+$upre[$nik][$blok][$tph][$sesi][$reff]+$sb[$nik][$blok][$tph][$sesi][$reff]+$lb1[$nik][$blok][$tph][$sesi][$reff]+$lb2[$nik][$blok][$tph][$sesi][$reff]+$rpbrd[$nik][$blok][$tph][$sesi][$reff])-$rppen[$nik][$blok][$tph][$sesi][$reff];
								
								@$st_gtperkar[$nik][$blok]+=$gtperkar[$nik][$blok][$tph][$sesi][$reff];
								@$stn_gtperkar[$nik]+=$gtperkar[$nik][$blok][$tph][$sesi][$reff];
								@$t_gtperkar+=$gtperkar[$nik][$blok][$tph][$sesi][$reff];
								
								$tab.="<td align=right>".@numb_format($rppen[$nik][$blok][$tph][$sesi][$reff])."</td>";
								$tab.="<td align=right>".@numb_format($gtperkar[$nik][$blok][$tph][$sesi][$reff])."</td>";
								
								$tab.="<td align=center width=20px>";
								$namakary=$nmkar[$nik];
								
								$tab.="<img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
								onclick=\"editdetail('".$notransaksi."','".$nik."','".$blok."','".$thntnm[$blok]."','".$ha[$nik][$blok][$tph][$sesi][$reff]."','".$jjg[$nik][$blok][$tph][$sesi][$reff]."','".$brd[$nik][$blok][$tph][$sesi][$reff]."','".$kg[$nik][$blok][$tph][$sesi][$reff]."','".$upah[$nik][$blok][$tph][$sesi][$reff]."','".$upre[$nik][$blok][$tph][$sesi][$reff]."','".$sb[$nik][$blok][$tph][$sesi][$reff]."','".$sb2[$nik][$blok][$tph][$sesi][$reff]."','".$lb1[$nik][$blok][$tph][$sesi][$reff]."','".$lb2[$nik][$blok][$tph][$sesi][$reff]."','".$rpbrd[$nik][$blok][$tph][$sesi][$reff]."','".$rppen[$nik][$blok][$tph][$sesi][$reff]."','".$ket[$nik]."','".$namakary."','".$hk[$nik][$blok][$tph][$sesi][$reff]."','".$no."','".implode("##",$iddendapnn)."','".$edit."','".substr($tph,10,10)."','".$sesi."','".$reff."','".$bjr[$blok]."');\" >";
						
								$tab.="<td align=center width=20px>
								<img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
								onclick=\"deletedetail('".$notransaksi."','".$nik."','".$blok."','".substr($tph,10,10)."','".$sesi."','".$reff."');\" >";
								$tab.="</td>";
								
							}
						}
					}
					$awal=($no-$row)+1;
					$tab.="<tr class=rowcontent ".$disblok." onclick=getmark(this.id); id=groupbyblok".$no.">";
					$tab.="<td align=center style=cursor:pointer;font-weight:bold; id=rowplus".$no." title='Click untuk melihat detail.' onclick=hiderow('".$awal."','".$no."');>+</td>";
					$tab.="<td align=center>".$nokar."</td>";
					$tab.="<td align=left style=font-size:11px;color:#2E86C1; ".$color." ".$doublec." ondblclick=cariby('".$nik."','namakary')>".$nkkry.$divkry.getNamaKaryawan($nik)."</td>";
					
					$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$blok."'");
					if($nmorg[$blok]==$blok){
						$blkkry=substr($blok,6,4);
					}else{
						$blkkry=$nmorg[$blok];
					}
					
					$tab.="<td align=center ".$bgcolor." ".$doublec." ondblclick=cariby('".$blok."','blok')>".$blkkry."</td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td align=center ".$bgcolor." ".$doublec." ondblclick=cariby('".$thntnm[$blok]."','tt')>".$thntnm[$blok]."</td>";
					$tab.="<td align=center hidden>".$ket[$nik]."</td>";
					$tab.="<td align=right>".@numb_format($bjr[$blok],2) . "</td>";
					$tab.="<td align=right>".@numb_format($st_ha[$nik][$blok],2)."</td>";
					$tab.="<td align=right>".@numb_format($st_jjg[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_norma[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_brd[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_kg[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_hk[$nik][$blok],2)."</td>";
					$tab.="<td align=right>".@numb_format($st_upah[$nik][$blok])."</td>";
					$tab.="<td align=right ".$cp.">".@numb_format($st_upen[$nik][$blok])."</td>";
					$tab.="<td align=right hidden>".@numb_format($st_upre[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_sb[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_sb2[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_lb1[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_lb2[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_rpbrd[$nik][$blok])."</td>";
					
					#$align=" align=right ";$nn=" style=display:none ";
					foreach($dendapanen as $iddenda => $kddenda){
						$tab.="<td ".$align." ".$nn." ".$tplistdata[$iddenda]."\nRp => ".$denda[$iddenda]." x ".$harga[$iddenda]." = ".@numb_format($st_denda[$iddenda][$nik][$blok]*$harga[$iddenda])." \" width=30px name=listdenda[] id=pddt##".$iddenda."##".($jlhdet+$nokar).">".@numb_format($st_denda[$iddenda][$nik][$blok])."</td>";
						$stn_denda[$iddenda][$nik]+=$st_denda[$iddenda][$nik][$blok];
						
						$idakhir=($jlhdet+$nokar);
					}
					
					$tab.="<td align=right>".@numb_format($st_rppen[$nik][$blok])."</td>";
					$tab.="<td align=right>".@numb_format($st_gtperkar[$nik][$blok])."</td>";
					
					$tab.="<td align=center style=cursor:pointer;font-weight:bold;width:20px; id=rowplusn".$no." title='Click untuk melihat detail.' onclick=hiderow('".$awal."','".$no."');>+</td>";
					$tab.="<td style=width:20px;text-align:center;>
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete (Satu Blok)' onclick=\"deletedetail('".$notransaksi."','".$nik."','".$blok."','','','','blok');\" ></td>";
				}
				
				$awalnik=($no-$rownik)+1;
				$tab.="<tr class=rowcontent onclick=getmark(this.id); ".$diskary." id=groupbynik".$no.">";
				$tab.="<td align=center style=cursor:pointer;font-weight:bold; id=rowplusnik".$no." title='Click untuk melihat detail.' onclick=hiderow('".$awalnik."','".$no."','nik');>+</td>";
				$tab.="<td align=center>".$nokarnik."</td>";
				$tab.="<td align=left ".$color." ".$doublec." ondblclick=cariby('".$nik."','namakary')>".$nkkry.$divkry.getNamaKaryawan($nik)."</td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td align=center hidden>".$ket[$nik]."</td>";
				$tab.="<td></td>";
				$tab.="<td align=right>".@numb_format($stn_ha[$nik],2)."</td>";
				$tab.="<td align=right>".@numb_format($stn_jjg[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_norma[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_brd[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_kg[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_hk[$nik],2)."</td>";
				$tab.="<td align=right>".@numb_format($stn_upah[$nik])."</td>";
				$tab.="<td align=right ".$cp.">".@numb_format($stn_upen[$nik])."</td>";
				$tab.="<td align=right hidden>".@numb_format($stn_upre[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_sb[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_sb2[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_lb1[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_lb2[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_rpbrd[$nik])."</td>";
				
				foreach($dendapanen as $iddenda => $kddenda){
					$tab.="<td ".$align." ".$nn." ".$tplistdata[$iddenda]."\nRp => ".$denda[$iddenda]." x ".$harga[$iddenda]." = ".@numb_format($stn_denda[$iddenda][$nik]*$harga[$iddenda])." \" width=30px name=listdenda[] id=pddt##".$iddenda."##".($jlhblok+$nokarnik).">".@numb_format($stn_denda[$iddenda][$nik])."</td>";
				}
				$tab.="<td align=right>".@numb_format($stn_rppen[$nik])."</td>";
				$tab.="<td align=right>".@numb_format($stn_gtperkar[$nik])."</td>";

				$tab.="<td align=center style=cursor:pointer;font-weight:bold;width:20px; id=rowplusnnik".$no." title='Click untuk melihat detail.' onclick=hiderow('".$awalnik."','".$no."','nik');>+</td>";
				$tab.="<td style=width:20px;text-align:center;>
				<img src=images/application/application_delete.png class=zImgBtn  title='Delete (Satu Karyawan)' onclick=\"deletedetail('".$notransaksi."','".$nik."','','','','','kary');\" ></td>";
			}
		
	
			
			$tab.="<tr class=rowcontent style=background-color:#A3E4D7;height:25px;>";
			$tab.="<td></td>";
			$tab.="<td colspan=2 align=center>
				   <input value=".($jlhblok+$nokarnik)." style=display:none id=jlhbrsdt><b>GRAND TOTAL</b></td>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td hidden></td>";
			$tab.="<td></td>";
			$tab.="<td align=right>".@numb_format($tluas,2)."</td>";
			$tab.="<td align=right>".@numb_format($tjjg)."</td>";
			$tab.="<td align=right>".@numb_format($norma)."</td>";
			$tab.="<td align=right>".@numb_format($tbrd)."</td>";
			$tab.="<td align=right>".@numb_format($tkg)."</td>";
			$tab.="<td align=right>".@numb_format($thk,2)."</td>";
			$tab.="<td align=right>".@numb_format($tupah)."</td>";
			$tab.="<td align=right ".$cp.">".@numb_format($tdenda)."</td>";
			$tab.="<td align=right hidden>".@numb_format($tupahpremi)."</td>";
			$tab.="<td align=right>".@numb_format($tpbss)."</td>";
			$tab.="<td align=right>".@numb_format($tpbss2)."</td>";
			$tab.="<td align=right>".@numb_format($tplb)."</td>";
			$tab.="<td align=right>".@numb_format($tplb2)."</td>";
			$tab.="<td align=right>".@numb_format($trpbrd)."</td>";
			#ttl denda list data
			foreach($dendapanen as $iddenda => $kddenda){
				$tab.="<td ".$align." ".$nn." ".$tp[$iddenda]." width=30px name=listdenda[] id=tpddt##".$iddenda.">".@numb_format($ttlp[$iddenda])."</td>";
			}
			
			$tab.="<td align=right>".@numb_format($trrp)."</td>";
			$tab.="<td align=right>".@numb_format($t_gtperkar)."</td>";
			$tab.="<td align=right colspan=2></td>";
			$tab.="</tr>";
			
			
			#rekapitulasi
			$str = "select a.notransaksi, a.kodeorg,a.nik,a.nourut,a.tahuntanam,sum(upahpenalty) as upahpenalty,sum(a.hasilkerja) as hasilkerja, sum(a.hasilkerjakg) as kg, sum(a.jumlahhk) as hk, sum(a.norma) as norma, sum(a.upahkerja) as upah, sum(a.premibasis) as bss, sum(a.upahpremilebihbasis) as lbbss,sum(a.upahpremilebihbasis2) as lbbss2, sum(a.brondolan) as brd,sum(a.premibrondol) as rpbrd, sum(a.luaspanen) as ha, sum(a.rupiahpenalty) as rupiahpenalty, sum(a.upahpremi) as upahpremi, sum(a.premibasis2) as bss2  
			from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid  where a.notransaksi='" . $notransaksi . "' ".$where." group by kodeorg order by a.kodeorg asc";
			$row=fetchData($str);
			$nox='0';
			foreach($row as $bar) {
				$nox++;
				$tab.="<tr class=rowcontent style=background-color:#AED6F1;height:25px;>";
				$no+=1;
				$tab.="<td align=center>" . $nox . "</td>";
				if($nox==1){
					$tab.="<td align=center colspan=2><b>REKAPITULASI</b></td>";
				}else{
					$tab.="<td colspan=2></td>";
				}
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				if($nmorg[$bar['kodeorg']]==$bar['kodeorg']){
					$blkkry=substr($bar['kodeorg'],6,4);
				}else{
					$blkkry=$nmorg[$bar['kodeorg']];
				}
				$tab.="<td align=center style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['kodeorg']."','blok')>" . $blkkry. "</td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['tahuntanam']."','tt')>" . $bar['tahuntanam'] . "</td>";
				$tab.="<td align=center hidden></td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=right>" . numb_format($bar['ha'],2) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['hasilkerja']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['norma']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['brd']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['kg']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['hk'],2) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['upah']) . "</td>";
				$tab.="<td align=right ".$cp.">" . numb_format($bar['upahpenalty']) . "</td>";
				$tab.="<td align=right hidden>" . numb_format($bar['upahpremi']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['bss']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['bss2']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['lbbss']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['lbbss2']) . "</td>";
				$tab.="<td align=right>" . numb_format($bar['rpbrd']) . "</td>";
				
				#denda list data
				$strd = ""; $denda=array();
				$strd = "select * from " . $dbname . ".kebun_mutubuah where notransaksi='" . $bar['notransaksi'] . "' and kodeorg='".$bar['kodeorg']."' and nik='".$bar['nik']."' and nourut='".$bar['nourut']."'";
				$resd = fetchdata($strd);
				foreach($resd as $bard){
					$denda[$bard['idjenis']]=$bard['nilai'];
				}
				
				foreach($dendapanen as $iddenda => $kddenda){
					$tab.="<td align=right ".$nn." ".$tp[$iddenda]." width=30px name=listdenda[] id=rtpddt##".$iddenda."##".$nox.">".@numb_format($denda[$iddenda])."</td>";
				}
				
				$tab.="<td align=right>" . numb_format($bar['rupiahpenalty']) . "</td>";
				$tab.="<td align=right>" . numb_format((($bar['upah']-$bar['upahpenalty'])+$bar['upahpremi']+$bar['bss']+$bar['lbbss']+$bar['lbbss2']+$bar['rpbrd'])-$bar['rupiahpenalty']) . "</td>";
				$tab.="<td align=right colspan=2></td>";
			}
			$tab.="<input value=".$nox." style=display:none id=jlhbrsdtrekap>";
			#rekapitulasi end
		}
        $tab.="</tr>";
        $tab.="</table>";

		if($tipe=='excel'){	
			$nop = "detail_panen.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("detail_panen", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;
		}
	break;
	case'edithead':
		$str="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		foreach($res as $bar){
			$param['nikmandor'] = $bar['nikmandor'];
			$param['nikmandor1']= $bar['nikmandor1'];
			$param['kerani']    = $bar['nikasisten'];
			$param['divisi']    = $bar['divisi'];
			$param['kodeorg']   = $bar['kodeorg'];
			$param['tgl']       = tanggalnormal($bar['tanggal']);
			
			echo $bar['notransaksi']."####".tanggalnormal($bar['tanggal'])."####".$bar['kodeorg']."####".$bar['nobkm']."####".$bar['nikmandor']."####".$bar['nikmandor1']."####".$bar['nikasisten']."####".$bar['divisi']."####".$bar['jenis']."####".$bar['nospk'];
		}
		echo "####".getmandor($param);
	break;
	case'getHitungDenda':
	# === Get Denda ===
		$param=$_POST;
		if($param['karyawanid']=='' || $param['blok']=='' || $param['jjgpanen']==''){
			exit('Error : Silahkan isi Karyawan, Blok dan Jjg Panen terlebih dahulu.');
		}
		
		$qDenda = "select * from ".$dbname.".kebun_5dendapanen a left join ".$dbname.".kebun_5kodedendapanen b on a.kodedenda=b.kodedenda where 1=1 and a.kodeorg='".$param['kodeorg']."'";
		$resDenda = fetchData($qDenda);
		$optDenda = array();
		foreach($resDenda as $row) {
			$optDenda[$row['id']] = array(
				'jenis' => $row['jenisdenda'],
				'nilai' => $row['denda']
			);
		}
		
		$denda = array(
				'jjg' => 0,
				'rp' => 0
			);
			
		if(is_array($param['penalti'])){
			foreach($param['penalti'] as $kode=>$val) {
				if(isset($optDenda[$kode])) {
					$denda['rp'] += floatval($val) * floatval($optDenda[$kode]['nilai']);
					
				}
			}
		}
			
		if($denda['rp']<0){
			$denda['rp']=0;
		}

		echo $denda['rp'];
	# === Get Denda ===
	
	break;

	
    case'insert':
		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['karyawanid']!='' and $param['blok']!='' and ($param['hapanen']!='' or $param['hapanen']!='0') and ($param['jjgpanen']>'0' or $param['brdpanen']>'0')){

		try {
			$owlPDO->beginTransaction();
			# ambil tanggal
			$str = "select * from ".$dbname.".kebun_aktifitas where notransaksi='".$notransaksi."'";
			$res = fetchdata($str);
			$tanggal = $res[0]['tanggal'];
			
			
			if($param['sesi']==''){$param['sesi']='1';}
			if($param['tph']!='' or $param['tph']!='0'){
				$str = "select * from ".$dbname.".kebun_5tph  where kode='".$param['tph']."' and status='A'";
				$res = fetchdata($str);
				if(count($res)==0){
					#throw new PDOException("Nomor TPH tidak ada pada master TPH.");
				}	
			}
			
			
			# Hapus dulu data yang lama
			if($param['tphold']!='' or $param['sesiold']!=''){				
				$str = "delete from " . $dbname . ".kebun_prestasi where notransaksi='".$notransaksi."' and nik='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and tph='".$param['tphold']."' and sesi='".$param['sesiold']."' and noreferensi='".$param['noreferensi']."'";
				$owlPDO->exec($str);
			}else{
				$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$notransaksi."' and nik='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and tph='".$param['tphold']."' and sesi='".$param['sesiold']."' and noreferensi='".$param['noreferensi']."'";
				$res = fetchdata($str);
				if(count($res)>0){
					throw new PDOException("Data sudah pernah ada, silahkan cek list tersimpan dibawah.");
				}	
			}
			
			cekmaxnilaihk($param['karyawanid'],$tanggal,'1','0','new',$exit='0');
			
			$str = "select max(nourut) as nourut from ".$dbname.".kebun_prestasi where notransaksi='".$notransaksi."'";
			$res = fetchdata($str);
			if(count($res)==0){
				$nomor=1;
			}else{
				@$nomor=floatval($res[0]['nourut'])+1;
			}
			
			# Validasi penginputan
			cekPrestasi($param);
			
			$data = array(
				'notransaksi'         => $param['notransaksi'],
				'nobkm'               => $param['nobkm'],
				'nik'                 => $param['karyawanid'],
				'nourut'              => $nomor,
				'kodeorg'             => $param['blok'],
				'tph'                 => $param['tph'],
				'sesi'                => $param['sesi'],
				'luaspanen'           => $param['hapanen'],
				'hasilkerja'          => $param['jjgpanen'],
				'brondolan'           => $param['brdpanen'],
				'premibrondol'        => $param['rpbrondol'],
				'hasilkerjakg'        => $param['kgpanen'],
				'norma'               => $param['jjgbasis'],
				'jumlahhk'            => $param['hk'],
				'upahkerja'           => $param['upah'],
				'upahpremi'           => $param['upahpremi'],
				'upahpenalty'         => $param['dendaupah'],
				'premibasis'          => $param['basis'],
				'premibasis2'         => $param['basis2'],
				'upahpremilebihbasis' => $param['lbasis'],
				'upahpremilebihbasis2'=> $param['lbasis2'],
				'rupiahpenalty'       => $param['denda_rp'],
				'tahuntanam'          => $param['tt'],
				'bjr'                 => $param['bjr'],
				'pekerjaanpremi'      => $param['sts'],
				'keterangan'          => $param['kontan'],
				'noreferensi'         => $param['noreferensi'],
				'updateby'            => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			# Insert kebun_prestasi
			$query = insertQuery($dbname,'kebun_prestasi',$data,$cols); 
			// exit("error".$query);
			$owlPDO->exec($query);
			
			#kode denda
			$optkode = makeOption($dbname,'kebun_5kodedendapanen','id,kodedenda');
			
			# Hapus dulu data yang lama
			$str = "delete from " . $dbname . ".kebun_mutubuah where notransaksi='".$notransaksi."' and nik='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and tipedetail='denda' and tph='".$param['tphold']."' and sesi='".$param['sesiold']."' and noreferensi='".$param['noreferensi']."'"; 
			#exit("error".$str);
			$owlPDO->exec($str);
			
			
			$ndenda=explode("##",$kodeiddenda);
			$n = count($ndenda);
			$datamutu = array();
			if($n>0){
				for($i=0;$i<$n;$i++){
					$datamutu = array(
						'notransaksi'=> $param['notransaksi'],
						'kodeorg'    => $param['blok'],
						'tph'        => $param['tph'],
						'nik'        => $param['karyawanid'],
						'tglpanen'   => $tanggal,
						'sesi'       => $param['sesi'],
						'tipedetail' => 'denda',
						'noreferensi'=> $param['noreferensi'],
						'nourut'     => $nomor,
						'idjenis'    => $ndenda[$i],
						'kodedenda'  => @$optkode[$ndenda[$i]],
						'nilai'      => $param['penalti'.$ndenda[$i]],
						'updateby'   => $_SESSION['standard']['userid']
					);
					
					$colsmutu = array();
					foreach($datamutu as $key=>$row) {
							$colsmutu[] = $key;
					}
					
					# Insert kebun_mutubuah
					$query = insertQuery($dbname,'kebun_mutubuah',$datamutu,$colsmutu);
					# jika ada isinya insert jika kosong abaikan
					if($param['penalti'.$ndenda[$i]]!=''){						
						$owlPDO->exec($query);
					}
				}
			}
			if($param['jenis']!='BOR'){				
				hitungpremi($param,'pro');
			}
			
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		}
	break;
    case'delete':
	try {
		$owlPDO->beginTransaction();
		
		$str="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$notransaksi."'";
		$bar=fetchData($str);
		$tgl    =$bar[0]['tanggal'];
		$kodeorg=$bar[0]['kodeorg'];
		$nobkm  =$bar[0]['nobkm'];
		
		// $param['notransaksi']=$bar[0]['notransaksi'];
		// $param['nobkm']      =$bar[0]['nobkm'];
		// $param['kodeorg']    =$bar[0]['kodeorg'];
		// $param['tgl']        =$bar[0]['tanggal'];
		// $param['mandor']     =$bar[0]['nikmandor'];
		// $param['mandor1']    =$bar[0]['nikmandor1'];
		// $param['kerani']     =$bar[0]['keranimuat'];
		
		if($bar['noreferensi']!=''){
			throw new PDOException('Ini adalah transaksi yang terbentuk otomatis pada saat Posting pada proses Premi Pemanen, untuk menghapus silahkan unposting pada transaksi Proses Premi Pemanen.');
		}
		
		$str = "delete from " . $dbname . ".sdm_absensidt where tanggal='".$tgl."' and norefrensi='".$notransaksi."'";
		$owlPDO->exec($str);
		
		#cek masih ada tidak detailnya
		$str = "SELECT * FROM " . $dbname . ".sdm_absensidt where tanggal='".$tgl."' and kodeorg='".$kodeorg."'";
		$res=fetchData($str);
		if(count($res)==0){
			#hapus ht nya
			$str = "delete from " . $dbname . ".sdm_absensiht where tanggal='".$tgl."' and kodeorg='".$kodeorg."'";
			$owlPDO->exec($str);
		}
		
        $str = "delete from " . $dbname . ".kebun_aktifitas where notransaksi='".$notransaksi."'";
        $owlPDO->exec($str);
		#saveheadertosdmabsensi($param);
		
		
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			

	break;

    case'deletedetail':
	try {
		$owlPDO->beginTransaction();
		
		$wh="";
		if($param['sumber']=='kary'){
			$wh.=" and nik='".$karyawanid."'";
		}elseif($param['sumber']=='blok'){
			$wh.=" and nik='".$karyawanid."'";
			$wh.=" and kodeorg='".$blok."'";
		}else{
			$wh.=" and nik='".$karyawanid."'";
			$wh.=" and kodeorg='".$blok."'";
			$wh.=" and tph='".$param['tph']."' and sesi='".$param['sesi']."' and noreferensi='".$param['noreferensi']."'";
		}
		
        $str = "delete from " . $dbname . ".kebun_prestasi where notransaksi ='".$notransaksi."' ".$wh."";
		$owlPDO->exec($str);

		# Hapus dulu denda
		$str = "delete from " . $dbname . ".kebun_mutubuah where notransaksi='".$notransaksi."' and tipedetail='denda' ".$wh."";
		$owlPDO->exec($str);
		
		hitungpremi($param,'pro');
		
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
    break;

    case'loaddata':
		$kodorg=array();
        $where="";
		//$where.= "and a.kodeorg in (".getOrgDetail(2).")";
		$where.= " and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		$whsdm= "and substr(kodeorg,1,4) = '".$_SESSION['empl']['lokasitugas']."'";
		
		$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABKRN'"; 
		$res = fetchdata($str);
		@$arrjab = explode(',', $res[0]['nilai']);
		
		$cari=false;
		$subbg='isi';
		if($_SESSION['empl']['subbagian']==''){
			$subbg='kosong';
		}
		if($_SESSION['empl']['subbagian']=='' and in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
			$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and (b.kodeorg like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."' or b.kodeorg is null)"; 
		}elseif($_SESSION['empl']['subbagian']==''){
			$where.= " and (a.kodeorg = '".$_SESSION['empl']['lokasitugas']."' or b.kodeorg is null)";
		}else{
			$where.=" and (b.kodeorg like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."' or b.kodeorg is null)"; 
		}
		
        if ($kontan != '') {
            $where.=" and b.keterangan like '" . $kontan . "%' ";
        }
		if ($mandor != '') {
            $where.=" and a.nikmandor like '" . $mandor . "%' ";
        }
		
		if ($divsch != '') {
            $where.=" and a.divisi like '" . $divsch . "%' ";
        }
        if (($tglmulai != '') and ($tglmulai != '--')) {
            $where.=" and a.tanggal >='" . $tglmulai . "' ";
			$whsdm.=" and tanggal >='" . $tglmulai . "' ";
        }
		if (($tglselesai != '') and ($tglselesai != '--')) {
            $where.=" and a.tanggal <='" . $tglselesai . "' ";
			$whsdm.=" and tanggal <='" . $tglselesai . "' ";
        }
		if ($notransaksisch != '') {
            $where.=" and a.notransaksi like '%" . $notransaksisch . "%' ";
        }
		if ($postingsrc != '') {
            $where.=" and a.jurnal ='" . $postingsrc . "' ";
        }
		if ($periodesch != '') {
            $where.=" and a.tanggal like '" . $periodesch . "%' ";
			$whsdm.=" and tanggal like '" . $periodesch . "%' ";
        }
		
		#$where.=" and (a.noreferensi is null or a.noreferensi ='')";
		$where.=" and a.tipe = 'JJG'";
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = floatval($page) * floatval($limit);
        $maxdisplay = (floatval($page) * floatval($limit));
        $no = 0;
		$tab = "";
        $no = $maxdisplay;
		
		
		
		$sql = "select count(distinct a.notransaksi) as notr from " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi='PNN' " . $where . " group by a.notransaksi";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=24 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}else{
			$ttl=array();
			$strn = "select norefrensi, nobkm, kodeorg,sum(umr) as umr, sum(premi) as premi, sum(hk) as hk from ".$dbname.".sdm_absensidt where norefrensi!='' and nobkm!='' ".$whsdm." group by norefrensi, nobkm"; 
			$resn = fetchdata($strn);
			foreach ($resn as $bar) {
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['umr']+$bar['premi'];
				// premi bkm jangan pake $resn[0]['premi']
				$hkab[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk'];
				$umrab[$bar['norefrensi']][$bar['nobkm']]+=$bar['umr'];
				$premab[$bar['norefrensi']][$bar['nobkm']]+=$bar['premi'];
			}
			
			$str = "SELECT a.*,sum(upahpenalty) as upahpenalty, sum(b.hasilkerja) as jjg, b.pekerjaanpremi, b.keterangan,sum(b.jumlahhk) as jumlahhk, sum(b.upahkerja) as upahkerja, sum(b.upahpremi) as upahpremi, sum(b.premibasis) as premibasis, sum(b.premibasis2) as premibasis2, sum(b.upahpremilebihbasis) as upahpremilebihbasis, sum(b.upahpremilebihbasis2) as upahpremilebihbasis2, sum(b.premibrondol) as premibrondol, sum(b.rupiahpenalty) as rupiahpenalty  FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi='PNN' " . $where . " group by a.notransaksi order by a.notransaksi desc limit " . $offset . "," . $limit . "";
			$res = fetchdata($str);
			foreach($res as $bar){
				$isi=$cl=$abs=$a=$xx=$ttl='';
				$no+=1;
				$a=$no%2;
				
				$hari=$c="";
				$hari = date('D', strtotime($bar['tanggal']));
				if($hari=='Sun'){$c="style=\"color:red\"";}
				if($hari=='Fri'){$c="style=\"color:blue\"";}
				
				if($bar['jjg']=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]==0){
					$cl=" style=background-color:red; title=\"Data detail belum ada.\"";
				}elseif($bar['jjg']=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]>0){
					$cl=" style=background-color:yellow; title=\"Data hanya absensi.\"";
					$abs="absensi";
				}
				if($bar['keterangan']=='' or $bar['keterangan']=='KERJA'){$bar['keterangan']='KERJA';}
				
				$a=$a1=$b=$b1=$d=$d1="";
				if(getSubbagian($bar['nikmandor'])!=$bar['divisi']){
					$a="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor'])."</i></b></font>";				
					$a1="title=\"Karyawan asistensi\"";				
				}
				if(getSubbagian($bar['nikmandor1'])!=$bar['divisi']){
					$b="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor1'])."</i></b></font>";				
					$b1="title=\"Karyawan asistensi\"";				
				}
				
				if(getSubbagian($bar['nikasisten'])!=$bar['divisi']){
					$d="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikasisten'])."</i></b></font>";				
					$d1="title=\"Karyawan asistensi\"";				
				}
				
				$tab.="<tr ".$cl." height=25px class=rowcontent  id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				//$tab.="<td align=center>" . $bar['nobkm'] . "</td>";
				$tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
				$tab.="<td align=center hidden>" . $bar['nospk'] . "</td>";
				$tab.="<td align=center>" . $bar['noreferensi'] . "</td>";
				$tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
				$tab.="<td align=center>" . $bar['divisi'] . "</td>";
				$tab.="<td align=center hidden ".$xx.">" . $bar['keterangan'] . "</td>";
				$tab.="<td align=center ".$c.">" . hari($bar['tanggal'],'ID') . "</td>";
				$tab.="<td align=center ".$c.">" . tanggalnormal($bar['tanggal']) . "</td>";
				$tab.="<td align=right>" . number_Format($bar['jjg']) . "</td>";
				$tab.="<td align=center>" . @numb_format($bar['jumlahhk']+$hkab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['upahkerja']+$umrab[$bar['notransaksi']][$bar['nobkm']]) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['upahpremi']+$bar['premibasis']+$bar['premibasis2']+$bar['upahpremilebihbasis']+$bar['upahpremilebihbasis2']+$bar['premibrondol']+$premab[$bar['notransaksi']][$bar['nobkm']]) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['rupiahpenalty']) . "</td>";
				$tab.="<td align=center ".$a1.">" . getNamaKaryawan($bar['nikmandor']) . "".$a."</td>";
				$tab.="<td align=center ".$b1.">" . getNamaKaryawan($bar['nikmandor1']) . "".$b."</td>";
				$tab.="<td align=center ".$d1.">" . getNamaKaryawan($bar['nikasisten']) . "".$d."</td>";
				$tab.="<td align=center>" . getNamaKaryawan($bar['updateby']) . "</td>";
				
				if ($bar['jurnal'] == 0) {
					$isi.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
						onclick=\"edit('".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".$bar['kodeorg']."','".$bar['nobkm']."','".$bar['nikmandor']."','".$bar['nikmandor1']."','".$bar['nikasisten']."','".$bar['divisi']."','".$bar['jenis']."','".$bar['nospk']."','".$no."');\" ></td>";					
					$isi.="<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
						onclick=\"del('".$bar['notransaksi']."','".$no."');\" ></td>";
					
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						//$isi.="<td align=center><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['notransaksi']."','".$no."');\" ></td>";
						$isi.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['notransaksi']."','','".$abs."');\" ></td>";
					} else {
						$isi.="<td align=center><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting'></td>";
					}
					
					
				}elseif ($bar['jurnal'] == 1) {
					$kdpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorg']."'");
					
					$isi.="<td style=width:20px></td><td style=width:20px></td>";
					$isi.="<td align=center style=width:20px><img src=images/skyblue/posted.png class=zImgBtn class=zImgBtn height='30'  title='Posted\nClick untuk melihat Jurnal' onclick=getjurnal('".$kdpt[$bar['kodeorg']]."','".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".tanggalnormal($bar['tanggal'])."')></td>";
				}else{
					$isi.="<td style=width:20px></td><td style=width:20px></td><td style=width:20px></td>";
				}
				
				#$isi.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Hitung' onclick=\"proseshitungpremi('".$bar['notransaksi']."');\" ></td>";
				
				$isi.="<td align=center style=width:20px><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$bar['notransaksi']."');\" ></td>";
				
				$isi.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailPDF('".$bar['notransaksi']."','".$no."','event');\" ></td>";
				$isi.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','".$bar['nobkm']."','event','PNN');\" ></td>";
				$isi.="<td align=center style=width:20px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailExcel('".$bar['notransaksi']."','".$no."','".$bar['nobkm']."','event','PNN');\" ></td>";

				$tab.=$isi;

				$tab.="</tr>";
			}
		}

        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr>
                     <tr><td colspan=24 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $footd.="</td>
            </tr>";



        echo $tab . "####" . $footd;

	break;
	case'ajukankeasst':
		$strupd=" update ".$dbname.".kebun_aktifitas set statuspersetujuan='1' where nobkm='".$param['nobkm']."'";
		try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	case'perbaikandata':
		$strupd=" update ".$dbname.".kebun_aktifitas set statuspersetujuan='0' where nobkm='".$param['nobkm']."'";
		try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	case'proseshitungpremi':
		try {
			$owlPDO->beginTransaction();
				
				hitungpremipanen($notransaksi);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}		
	break;
	case'postingabsensi':
		try {
		$owlPDO->beginTransaction();
		
			$queryH= selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".$param['notransaksi']."'");
			$dataH = fetchData($queryH);
			
			$queryD = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".
			$param['notransaksi']."'");
			$dataD = fetchData($queryD);
			
			$error1="";
			if(count($dataH)==0) {
				$error1 .= $_SESSION['lang']['errheadernotexist']."\n";
			}
			if(count($dataD)==0) {
				$error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
			}
			
			if($error1!='') {
				echo "Data Error :\n".$error1;
				exit;
			}
			
			$str = "select * from ".$dbname.".sdm_5periodegaji where kodeorg = '".$dataH[0]['kodeorg']."' and periode='".substr($dataH[0]['tanggal'],0,7)."' and sudahproses='1'";
			$res = fetchData($str);
			if(count($res)>0){
				exit("Warning: Periode gaji sudah ditutup, proses dibatalkan.");
			}
			
			#=== Cek if Upload Absensi ===
			$arrUpload = array();
			if(!empty($dataH[0]['nikmandor'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor'];
			if(!empty($dataH[0]['nikmandor1'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor1'];
			if(!empty($dataH[0]['nikasisten'])) $arrUpload[]['nik'] = $dataH[0]['nikasisten'];
			// if(!empty($dataH[0]['keranimuat'])) $arrUpload[]['nik'] = $dataH[0]['keranimuat'];
			$str = "select * from ".$dbname.".sdm_absensidt where norefrensi = '".$param['notransaksi']."' and nobkm='".$dataH[0]['nobkm']."'";
			$res = fetchData($str);
			foreach($res as $row){
				$arrUpload[]['nik'] = $row['karyawanid'];
			}


			#query pengecekan apakah FP aktif / tidak
			$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".$dataH[0]['kodeorg']."' and tanggal <= '".$dataH[0]['tanggal']."'";
			$res = fetchdata($str);
			$statusfp    = $res[0]['status'];//1 aktif,0 tidak
			$tipevalidasi= $res[0]['tipevalidasi'];
			$detailexp   = explode(",",$res[0]['detailvalidasi']);
			foreach($detailexp as $vald){
				$detval[$vald]=$vald;
			}
			if($statusfp==1){
				validasifp($tipevalidasi,$detval,'PNN',$arrUpload,$dataH[0]['tanggal'],'0');
			}
			// if($statusfp==1){
				// $countUpload=0;
				// foreach($arrUpload as $row){
					// $str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."' limit 1";
					// $bar = fetchdata($str)[0];
					// if($row['nik'] != $bar['karyawanid']){
						// $no++;
						// $optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$row['nik']."'");
						// $nikkary = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$row['nik']."'");
						// $errorUpload .= $no.". ".$nikkary[$row['nik']]." = ".$optNamaKaryawan[$row['nik']]."<br>";
						// $countUpload = $countUpload + 1;
					// }
				// }
				// if($countUpload > 0){
					// exit("Warning: Absen fingerprint untuk karyawan dg NIK : <br>".$errorUpload."belum ada.<br>Silahkan lakukan proses melalui menu : SDM - Proses - Fingerprint.");
				// }
			// }
			
			$str = "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and kriteriaefil='PNN'";
			$res = fetchData($str);
			if(count($res)==0){
				exit("error : Silahkan upload file pendukung terlebih dahulu sebelum melakukan posting.");
			}
			
			$strupd=" update ".$dbname.".kebun_aktifitas set jurnal='1' where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($strupd);
	
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;		
	case'getnotransaksi':
		$data = $_POST;
		# Data Capture & Reform
		$data['tipetransaksi'] = 'PNN';
		$data['tgl'] = tanggalsystem($data['tgl']);
		
		#=== Generate No Transaksi
		# Get Existing Data
		$fWhere = "tanggal='".$data['tgl']."' and kodeorg='".$data['kodeorg'].
			"' and tipetransaksi='".$data['tipetransaksi']."'";
		$fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
		$tmpNo = fetchData($fQuery);
		
		# Generate No Transaksi
		if(count($tmpNo)==0) {
			echo $data['notransaksi'] = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/001";
		} else {
			# Get Max No Urut
			$maxNo = 1;
			$noUrut=0;
			foreach($tmpNo as $row) {
				$tmpRow = explode('/',$row['notransaksi']);
				@$noUrut = (int)$tmpRow[3];
				if($noUrut>$maxNo){
					$maxNo = $noUrut;
				}
			}
			$currNo = addZero($maxNo+1,3);
			echo $data['notransaksi'] = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/".$currNo;
		}
	
    break;
	
	case'getbasispnn':
		$tab='';
		$prd = substr(tanggalsystemn($tgl),0,7);
		
		$strx = "SELECT *  FROM " . $dbname . ".kebun_5basispanen3 where 1=1 and kodeorg='".$kodeorg."' and periode ='".$prd."' and jenispremi='".$param['jenispremi']."' and tahuntanam = '".$bar['tahuntanam']."' order by periode desc limit 1";
		$resx=fetchdata($strx);
		
		//$tab.="<fieldset>";
		$tab.="<table class=sortable cellspacing=1 border=0 cellpadding=5>
                <thead>
					<tr class=rowheader>
                    <th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align=center rowspan=2 width=100px>" . $_SESSION['lang']['periode'] . "<br>(Berlaku)</th>
					<th align=center rowspan=2 width=100px>" . $_SESSION['lang']['jenispremi'] . "</th>";
				$tab.="<th align=center rowspan=2 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>";
				$tab.="<th align=center colspan=2 width=50px>" . $_SESSION['lang']['basic'] . "</th>
					<th align=center rowspan=2 width=50px>Tidak Basis</th>
					<th align=center colspan=2>Premi Siap Basis</th>
					<th align=center colspan=2>" . $_SESSION['lang']['lebihbasis'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['brondol'] . "</th>
				</tr>
				 <tr class=rowheader>";
                $tab.="<th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>
					<th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>
					<th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>
                    
				</tr>
				</thead>
		<tbody>";
		
		$prd = substr(tanggalsystemn($tgl),0,7);
		$no=0;
		
		$where="";
		if($param['tahuntanam']!=''){
			$where="and tahuntanam = '".$param['tahuntanam']."'";
		}
		$str = "SELECT *  FROM " . $dbname . ".kebun_5basispanen3 where 1=1 and kodeorg='".$kodeorg."' and periode<='".$prd."' and jenispremi='".$param['jenispremi']."' ".$where." order by periode desc limit 1";
		$res = fetchdata($str);
		$maxprd=$res[0]['periode'];
		
		$str = "SELECT *  FROM " . $dbname . ".kebun_5basispanen3 where 1=1 and kodeorg='".$kodeorg."' and periode<='".$prd."' and jenispremi='".$param['jenispremi']."' ".$where." order by periode desc";
		$res = fetchdata($str);
	
		
		
		foreach($res as $bar){
			if($maxprd==$bar['periode']){
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				$no+=1;	
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
				$tab.="<td align=center>".$bar['periode']."</td>";
				$tab.="<td>".$bar['jenispremi']."</td>";
				$tab.="<td align=center>".$bar['tahuntanam']."</td>";
				$tab.="<td align=right>".$bar['basis1']."</td>";
				$tab.="<td align=right>".$bar['basis2']."</td>";
				$tab.="<td align=right>".$bar['tidakbasis']."</td>";
				$tab.="<td align=right>".$bar['premibasis1']."</td>";
				$tab.="<td align=right>".$bar['premibasis2']."</td>";
				$tab.="<td align=right>".$bar['premilebihbasis1']."</td>";
				$tab.="<td align=right>".$bar['premilebihbasis2']."</td>";
				$tab.="<td align=right>".$bar['premibrondolan']."</td>";
			}
		}
		
		$tab.="</tr></tbody>";
		$tab.="</table>";
		$tab.="</fieldset>";
		echo $tab;
	break;
	
	case'getDataDetail':
		echo hitungpremi($_POST,'baru');
	break;
	case'getumr':
	#=============================== Get UMR ==============================
		$tahun=substr(tanggalsystemn($tgl),0,4);
		$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$karyawanid."' and tahun=".$tahun." and idkomponen in ('1')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$Umr=$res->fetch();
			$umrHarian=$Umr['nilai']/25;
		
		if($umrHarian==0){
			#exit("Warning : Gaji Pokok Karyawan belum ada.");
		}
	#=============================== Get UMR ==============================
		#$jumlahhksebulan=cekmaxnilaihk($param);
		
		
	echo $umrHarian."####".$jenispremi."####".$jumlahhksebulan; #exit("error");
	break;
	case'getnilaihk':
		$str = "select * from ".$dbname.".sdm_5absensi where kodeabsen='".$kodeabsen."'";
		$res=fetchData($str);
		
		echo @$res[0]['nilaihk'];
	break;
	
}

function cekPrestasi($param) {
	global $dbname;
	global $owlPDO;
	global $jenispremi;
		
	$tgl=explode('/',$param['notransaksi']);
	$tgl=$tgl[0];
	$notrx='';
	
	if($param['hapanen']==''){$param['hapanen']=0;}
	if($param['jjgpanen']==''){$param['jjgpanen']=0;}
	
	
	
	#cek mandor
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikmandor='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi']."\n";
	
	#cek mandor1
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikmandor1='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi']."\n";
	
	#cek kerani
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where keranimuat='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi']."\n";
	
	#cek nikasisten
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikasisten='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi']."\n";
	
		if(@$jumtrans>0){
			throw new PDOException("Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani, notransaksi : \n".trim($notrx)."");
		}
	

	# Cek Perawatan
	# Jika sudah ada di perawatan tidak bisa input panen
	# Jika karyawan ada pekerjaan panen dan perawatan, maka harus malekukan input panen terlebih dahulu
	$qAbs = selectQuery($dbname,'kebun_kehadiran_vw','karyawanid,sum(jhk) as jhk, sum(umr) as umr,notransaksi',
								"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jhkrawat = $resAbs[0]['jhk'];
	$umrrawat = $resAbs[0]['umr'];
	$notr	  = $resAbs[0]['notransaksi'];
	
	if(floatval($jhkrawat)!='0' || floatval($umrrawat)!='0') {
		throw new PDOException("Karyawan sudah terdaftar di kegiatan perawatan, notransaksi : ".$notr."");
	}
	
	#cek di vhc - kegiatan traksi
	$qAbs = selectQuery($dbname,'vhc_runhk','sum(upah) as jhk,notransaksi',
			"idkaryawan='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhkvhc = $resAbs[0]['jhk'];
	$notrtr = $resAbs[0]['notransaksi'];
	
	if(floatval($jmlhkvhc)!='0') {
		throw new PDOException("Karyawan sudah terdaftar di kegiatan traksi, notransaksi : ".$notrtr."");
	}
	
	#cek di SDM
	if($param['method']=='updateabsensi'){
		$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."' and norefrensi!='".$param['notransaksi']."'";
		$res = fetchData($str);
		if(count($res)>'0') {
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM dengan nomor transaksi : ".$param['notransaksi'].".");
		}
	}elseif($param['method']=='insertabsensi'){
		$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."' and absensi != '".$param['kodeabsen']."' group by norefrensi";
		$res = fetchData($str);
		if(count($res)>'0') {
			$noref="";
			foreach($res as $bar){
				$noref.=$bar['norefrensi']."<br>";
			}
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM dengan kode absensi : ".$param['kodeabsen']."<br>".$noref);
		}
		
		$qAbs = selectQuery($dbname,'sdm_absensidt_vw','sum(nilaihk) as jhk', "karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
		$resAbs = fetchData($qAbs);
		$jmlhksdm = $resAbs[0]['jhk'];
		if(floatval($jmlhksdm)!='0' and $param['jhk']>'0') {
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM.");
		}
	}elseif($param['method']!='insertabsensi'){
		
	
		$qAbs = selectQuery($dbname,'sdm_absensidt_vw','sum(nilaihk) as jhk',
				"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
		$resAbs = fetchData($qAbs);
		$jmlhksdm = $resAbs[0]['jhk'];
		
		if(floatval($jmlhksdm)!='0') {
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM, tanggal : ".$tgl."");
		}
		
		# Cek Panen hanya di 1 blok
		$qPnn = selectQuery($dbname,'kebun_prestasi_vw','karyawanid,notransaksi',
				"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."' and notransaksi!='".$param['notransaksi']."'");
		
		$resPnn = fetchData($qPnn);
		if(!empty($resPnn)){
			throw new PDOException("Pemanen dapat memanen diblok berbeda hanya dalam 1 nomor BKM,\nPemanen sudah terdaftar pada transaksi : ".$resPnn[0]['notransaksi']."");
		}
		
		# Cek dan ricek kalau data kosong
		if($param['blok']==''){
			$warning="Blok";
			throw new PDOException("Silakan mengisi ".$warning.".\nsilahkan click tombol Preview untuk melihat data yg tersimpan.");
		}
		if($param['karyawanid']==''){
			$warning="Karyawan";
			throw new PDOException("Silakan mengisi ".$warning.".\nsilahkan click tombol Preview untuk melihat data yg tersimpan.");
		}
		
		if($param['jjgpanen']==0||$param['jjgpanen']=='' and $param['brdpanen']>0){
		}else{			
			if($param['jjgpanen']==0||$param['jjgpanen']==''){
				$warning="Hasil Kerja (Jjg)";
				throw new PDOException("Silakan mengisi ".$warning.".\nsilahkan click tombol Preview untuk melihat data yg tersimpan.");
			}
			
			if($param['hapanen']==0 ||$param['hapanen']==''){
				$warning="Luas Panen(Ha)";
				throw new PDOException("Silakan mengisi ".$warning.".\nsilahkan click tombol Preview untuk melihat data yg tersimpan.");
			}

			if($param['bjr']==0 || $param['bjr']==''){
				$warning="BJR melalui Kebun - Setup - BJR";
				throw new PDOException("Silakan mengisi ".$warning.".\nsilahkan click tombol Preview untuk melihat data yg tersimpan.");
			}
			
			if($param['kgpanen']==0 || $param['kgpanen']==''){
				$warning="Kg Panen";
				throw new PDOException("".$warning." tidak boleh kosong.");
			}
		}
		
		
		
		if($jenispremi!='LIBUR' and $param['upahpremi']=='0'){		
			if($param['upah']==0 || $param['upah']==''){
				$warning="Gaji Pokok Karyawan";
				throw new PDOException("Silakan mengisi ".$warning.".");
			}		
		}
		
		# periksa luas panen hari ini apakah sudah melebihi setup blok
		# cari luas blok
		$query = "SELECT luasareaproduktif FROM ".$dbname.".`setup_blok`
			WHERE `kodeorg` = '".$param['blok']."'";
		$qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$qDetail->setFetchMode(PDO::FETCH_ASSOC);     
		while($rDetail=$qDetail->fetch()){
			$luasbloknya=$rDetail['luasareaproduktif'];
		}
		
		# cari tanggal
		$query = "SELECT distinct tanggal FROM ".$dbname.".`kebun_prestasi_vw`
				  WHERE `notransaksi` = '".$param['notransaksi']."'";
		$tanggalnya = '';
		$qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$qDetail->setFetchMode(PDO::FETCH_ASSOC);     
		while($rDetail=$qDetail->fetch()){
			$tanggalnya=$rDetail['tanggal'];
		}
		if($tanggalnya==''){
			$tanggalnya= $tgl;
		}
		
		$luaspanennya=0;
		# cari luas panen yang sudah diinput ditambah inputan
		$query = "SELECT sum(luaspanen) as luaspanen, sum(hasilkerja) as jjg FROM ".$dbname.".`kebun_prestasi_vw` WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['blok']."' and karyawanid!='".$param['karyawanid']."'";
		$qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$qDetail->setFetchMode(PDO::FETCH_ASSOC);     
		while($rDetail=$qDetail->fetch()){   
			$luaspanennya=$rDetail['luaspanen'];
			$jjgpnnnya=$rDetail['jjg'];
			
		}
		
		$luaspanennya+=$param['hapanen'];
		$jjgpnnnya+=$param['jjgpanen'];
		$selisihx=$luaspanennya-$luasbloknya;
		if($selisihx>0.0001){
			$warning="Total Luas Panen ".$luaspanennya." (Ha), melebihi Luas Blok ".$luasbloknya." (Ha) selisih ".$selisihx;
			throw new PDOException("".$warning.".");
		}

		# cek apakah jumlah luas, jjg tidak boleh lebih dari rekap panen
		# 01. Ambil data dari rekap panen
		$str = "select sum(jjgpanen) as jjgpanen, sum(luaspanen) as hapnn from ".$dbname.".kebun_rekappnn_vw where blok='".$param['blok']."' and tanggal='".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jjgrkppnn=$bar['jjgpanen'];
			$harkppnn=$bar['hapnn'];
		}
		
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		#sumber dari mobile lepas validasi ke rekappnn
		$mobile = makeOption($dbname, 'kebun_aktifitas', 'notransaksi,noreferensi',"notransaksi='".$param['notransaksi']."'");
		if($mobile[$param['notransaksi']]==''){			
			if($luaspanennya>$harkppnn){
				#throw new PDOException('Luas Panen Blok '.$nmorg[$param['blok']].' = '.$luaspanennya.' (Ha), melebihi Luas di Rekap Panen = '.$harkppnn.' (Ha)');
			}
			if($jjgpnnnya>$jjgrkppnn){
				#throw new PDOException('Jumlah Jjg Blok '.$nmorg[$param['blok']].' = '.$jjgpnnnya.', melebihi Jjg di Rekap Panen = '.$jjgrkppnn);
			}
		}
	}	
}

function hitungpremi($param,$mode){
	global $dbname;
	global $conn;
	global $owlPDO;
	global $jenispremi;
	global $param;
	
	switch($mode){
		case'baru':
			#============================= BJR Per Blok ===========================
			$prd  =substr(tanggalsystemn($param['tgl']),0,7);
			$tahun=substr(tanggalsystemn($param['tgl']),0,4);
			
			$bjr=0;
			$str = "select * from ".$dbname.".kebun_5bjr where  kodeorg='".$param['blok']."' and periode <='".$prd."' order by periode desc limit 1"; 
			$bar=fetchdata($str);
				@$bjr=$bar[0]['bjr'];
			
			#============================= BJR Per Blok ===========================
			#============================== Tipe Kary =============================
			$str = "select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$param['karyawanid']."'";
			$tipe=fetchdata($str);
				$tipeKary=$tipe[0]['tipekaryawan'];
				
			#============================== Tipe Kary =============================
			#=============================== Get HL ===============================
			# Jika hari libur maka upah = 0
			# 0 => Staff, 1 => PB, 2 => PKWT, 3 => KHT, 4 => KHL, 5 => Magang, 6 => Kontrak, 7 => Direksi, 8 => Komisaris
				if($jenispremi=='LIBUR'){
					$umrHarian=0;
					$hk=0;
				} else {
					$umrHarian=$umrHarian;
					if($param['jenis']!='BOR'){
						$hk=1;
					}else{
						$umrHarian=0;
						$hk=0;						
					}
				}
			#=============================== Get HL ===============================
			#=============================== Get TT ===============================
				$str = "select * from ".$dbname.".setup_blok where kodeorg='".$param['blok']."'"; 
				$bar=fetchdata($str);
					@$tt=$bar[0]['tahuntanam'];
			#=============================== Get TT ===============================
			#================== buat cek apakah ada di rekappnn ===================
			$jumlah='0';
			$str="select * from ".$dbname.".kebun_rekappnn_vw where "." blok='".$param['blok']."' and tanggal='".tanggalsystemn($param['tgl'])."' and posting=1 ";
			$qDetail=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$qDetail->setFetchMode(PDO::FETCH_ASSOC);
			while($rDetail=$qDetail->fetch()){
				$jumlah+=1;
				$jjgrekappnn+=$rDetail['jjgpanen'];
				$harkppnn+=$rDetail['luaspanen'];
			}
		   
			if($jumlah=='0'){
				$rpnn="x";
			} else {
				$rpnn="y";
			}
				
			#================== buat cek apakah ada di rekappnn ===================
			$query = "SELECT sum(luaspanen) as luaspanen, sum(hasilkerja) as jjg FROM ".$dbname.".`kebun_prestasi_vw` WHERE `tanggal` = '".tanggalsystemn($param['tgl'])."' and `kodeorg` ='".$param['blok']."'";
			$qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
			$qDetail->setFetchMode(PDO::FETCH_ASSOC);     
			while($rDetail=$qDetail->fetch()){   
				$luaspanennya=$rDetail['luaspanen'];
				$jjgpnnnya=$rDetail['jjg'];
			}
			
			#jika sumber dari mobile lepas validasi ke rekappnn
			$mobile = makeOption($dbname, 'kebun_aktifitas', 'notransaksi,noreferensi',"notransaksi='".$param['notransaksi']."'");
			if($mobile[$param['notransaksi']]!=''){		
				$jjgrekappnn=$jjgpnnnya;
				$harkppnn   =$luaspanennya;
				$rpnn       ="y";
			}
			
			#lepas validasi rekappnn
			$rpnn ="y";
			
			
			#cek apakah premi berdasarkan bjr atau tt
			$where="and periode in (SELECT max(periode) as periode FROM " . $dbname . ".kebun_5basispanen3 where 1=1 and kodeorg='".$param['kodeorg']."' and jenispremi='".$jenispremi."' and periode <= '".$prd."' order by periode desc)";
			
			$strz = "SELECT *  FROM " . $dbname . ".kebun_5basispanen3 where 1=1 and kodeorg='".$param['kodeorg']."' ".$where." and jenispremi='".$jenispremi."'";
			$resz = fetchdata($strz);
			
			if(count($resz)>0){
				$tipepremi='tt';
			}else{
				if($param['jenis']!='BOR'){
					exit("Warning : Basis panen belum ada.");
				}
			}
			// exit("error".$strz);
			
			$tab.="Perhitungan basis panen menggunakan <b>Tahun Tanam</b>";
			$opttt = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$param['blok']."'");
			
			$where="and tahuntanam = '".$opttt[$param['blok']]."'";
			$where.="and periode in (SELECT max(periode) as periode FROM " . $dbname . ".kebun_5basispanen3 where 1=1 and kodeorg='".$param['kodeorg']."' and jenispremi='".$jenispremi."' ".$where." and periode <= '".$prd."' order by periode desc)";
			$str = "SELECT *  FROM " . $dbname . ".kebun_5basispanen3 where 1=1 and kodeorg='".$param['kodeorg']."' and jenispremi='".$jenispremi."' ".$where." order by periode desc limit 1";
			$res=fetchdata($str);
			if(count($res)==0 and $param['kodeorg']!='' and $opttt[$param['blok']]!=''){
				if($param['jenis']!='BOR'){
					exit("Warning : Basis untuk tahun tanam  ".$opttt[$param['blok']]." dan jenis premi hari ".$jenispremi." periode ".$prd." belum ada, silahkan tambah melalui menu Kebun - Setup - Basis Panen per Tahun Tanam.");
				}
			}			
			
			// exit("error".$str);
			$umrHarian=0;
			foreach($res as $bar){
				#jika basis 2 kosong maka jadi minus jadi diakali pakai ini
				if($bar['basis2']==0){
					$bar['basis2']='9999999';
				}
				$basis1     =$bar['basis1'];
				$basis2     =$bar['basis2'];
				$premibasis =$bar['premibasis1'];
				$premibasis2=$bar['premibasis2'];
				$lbbss1     =$bar['premilebihbasis1'];
				$lbbss2     =$bar['premilebihbasis2'];
				$rpbrd      =$bar['premibrondolan'];
				$tdkbasis   =$bar['tidakbasis'];
				$umrHarian  =$bar['upahperhk'];
			}
			
			#=============================== Get UMR ==============================
			if($umrHarian==0){				
				$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$param['karyawanid']."' and tahun=".$tahun." and idkomponen in (1)"; 
				$Umr=fetchdata($str);
				$umrHarian=$Umr[0]['nilai']/25;
			}
			if($umrHarian==0){
				if($param['jenis']!='BOR'){
					exit("Warning : Gaji pokok belum ada.");
				}
			}
			#=============================== Get UMR ==============================
			
			if($param['jjgpanen']==""){$param['jjgpanen']=0;}
			if($param['jjgpanen']==0 and $param['brondol']>0){
				###untuk premi kutib brondol###
				$kodeJurnalpremibrd  = 'PNN03';
				$queryParampremibrd  = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"jurnalid='".$kodeJurnalpremibrd."'");
				$resParampremibrd    = fetchData($queryParampremibrd);
				$akundebetpremibrd   = $resParampremibrd[0]['noakundebet'];
				#default kodekegiatan panen/potong buah      
				$kodekegiatanpremibrd= $akundebetpremibrd."03";  
				$cekkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"noakun like '611%'");
				if($cekkeg[$kodekegiatanpremibrd]==''){
					if($param['jenis']!='BOR'){
						exit("Warning : Kode kegiatan untuk kutib brondolan (".$kodekegiatanpremibrd.") tidak terdaftar di menu : Setup - Kegiatan.");
					}
				}
				
				$str = "select * from ".$dbname.".kebun_5premibkm where kodekegiatan='".$kodekegiatanpremibrd."' and unit='".$param['kodeorg']."' and tanggalberlaku <='".tanggalsystemn($param['tgl'])."' order by tanggalberlaku desc limit 1"; #exit('error'.$str);
				$bar=fetchdata($str);
				if(count($bar)==0){
					if($param['jenis']!='BOR'){
						exit("Warning : Basis kegiatan kutip brondolan belum ada, silahkan tambah di menu : Setup - Kegiatan.");
					}
				}else{
					$rppremilebihbasis=$bar[0]['premilebihbasis'];
					$premisb          =$bar[0]['premibasis'];
					$basiskerja       =$bar[0]['basis'];
					$kerja            =$bar[0]['kerja'];
					$jumat            =$bar[0]['jumat'];
					$libur            =$bar[0]['libur'];
					if($kerja==''){$kerja=1;}else{$kerja=$kerja;}
					if($jumat==''){$jumat=1;}else{if($jumat=='5/7'){$jumat=5/7;}else{$jumat=$jumat;}}
					if($libur==''){$libur=1;}else{$libur=$libur;}
					
					if($jenispremi=='JUMAT'){
						$basis=round(@$basiskerja*$jumat,2);
					}elseif($jenispremi=='LIBUR'){
						$basis=@$basiskerja*$libur;
					}else{				
						$basis=@$basiskerja*$kerja;
					}
					
					
					$rplbbss2=$rplbbss1=$upah=$dendaupah=$jlhhk=$potupah=$rpbrondol=0;
					if($param['brondol']<$basis){
						$rplbbss2=$rpsiapbasis=0;
						if($tipeKary=='3'){
							$potupah  = floatval($param['brondol'])/floatval($basis)*$umrHarian;
							$dendaupah= $umrHarian-$potupah;
							$upah     = $umrHarian;    
							$jlhhk    = ($upah-$dendaupah)/$umrHarian;
						}else{					
							# tidak pakai denda upah
							$potupah  = floatval($param['brondol'])/floatval($basis)*$umrHarian;
							$dendaupah= 0;
							$upah     = $potupah;
							$jlhhk    = $upah/$umrHarian;
						}
					}else{
						$upah       = $umrHarian;
						$jlhhk      = $hk;
						$dendaupah  = 0;
						$kglbbrd    = $param['brondol']-$basis;
						$rpsiapbasis= $premisb;
						$rpbrondol  = floatval($kglbbrd)*floatval($rppremilebihbasis);
					}
					$basis1=$basis;
				}
			}else{
				$rplbbss2=$rplbbss1=$upah=$dendaupah=$jlhhk=$potupah=0;
				if($jenispremi=='LIBUR'){
					$rplbbss1 = floatval($param['jjgpanen']) * floatval($lbbss1);
				}else{
					if($param['jjgpanen']<$basis1){
						$rplbbss2=$rpsiapbasis=$rpsiapbasis2=0;
						// if($tipeKary=='3'){
							// #jika pakai denda upah pakai ini
							// if($param['jjgpanen']!='' and $tdkbasis==0){
								// $potupah  = floatval($param['jjgpanen'])/floatval($basis1)*$umrHarian;
								// $dendaupah= $umrHarian-$potupah;
								// $upah     = $umrHarian;    
								// $jlhhk    = ($upah-$dendaupah)/$umrHarian;
							// }elseif($param['jjgpanen']!='' and $tdkbasis!=0){
								// $upah     = $umrHarian;
								// $jlhhk    = 0;
								// $potupah  = $umrHarian;
								// $dendaupah= $umrHarian;
								// $rplbbss2 = floatval($param['jjgpanen']) * floatval($tdkbasis);
							// }
						// }else{					
							# tidak pakai denda upah
							if($param['jjgpanen']!='' and $tdkbasis==0){
								$potupah  = floatval($param['jjgpanen'])/floatval($basis1)*$umrHarian;
								$dendaupah= 0;
								$upah     = $potupah;
								$jlhhk    = $upah/$umrHarian;
							}elseif($param['jjgpanen']!='' and $tdkbasis!=0){
								$potupah  = 0;
								$dendaupah= 0;
								$upah     = 0;
								$jlhhk    = 0;
								$rplbbss2 = floatval($param['jjgpanen']) * floatval($tdkbasis);
							}
						//}
					}else{
						$rpsiapbasis= $premibasis;
						$upah       = $umrHarian;
						$jlhhk      = $hk;
						$dendaupah  =0;
						
						if($param['jjgpanen']>$basis1 and $param['jjgpanen']<=$basis2){
							$jjglbbss1= floatval($param['jjgpanen']) - floatval($basis1);
							$rplbbss1 = floatval($jjglbbss1) * floatval($lbbss1);
						}elseif($param['jjgpanen']>$basis2){
							$jjglbbss1   = floatval($basis2) - floatval($basis1);
							$rplbbss1    = floatval($jjglbbss1) * floatval($lbbss1);
							$jjglbbss2   = floatval($param['jjgpanen']) - floatval($basis2);
							$rplbbss2    = floatval($jjglbbss2) * floatval($lbbss2);
							$rpsiapbasis2= $premibasis2;
						}
					}
				}
				
				$rpbrondol = floatval($param['brondol'])*floatval($rpbrd);
			}
		
		#cek HK jangan sampai lewat dari 20
		#$jumlahhksebulan=cekmaxnilaihk($param);
		
		# ini untuk mode baru
			echo $bjr."######".$upah."######".$tt."######".$rpnn."######".$lbbss1."######".$jlhhk."######".$rpsiapbasis."######".$rplbbss1."######".$rplbbss2."######".$rpbrondol."######".$dendaupah."######".$basis1."######".$jumlahhksebulan."######".$tab."######".$rpsiapbasis2;
		break;
		case 'pro':
			if($param['jjgpanen']==""){$param['jjgpanen']=0;}
			
			if($param['jjgpanen']==0 and $param['brdpanen']>0){
				# ambil tanggal
				$str = "select * from ".$dbname.".kebun_aktifitas where notransaksi='".$param['notransaksi']."'";
				$res = fetchdata($str);
				$tanggal= $res[0]['tanggal'];
				$kodeorg= $res[0]['kodeorg'];
				$prd    = substr($tanggal,0,7);
				$tahun  = substr($tanggal,0,4);
				
				
				$umrHarian=0;
				$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$param['karyawanid']."' and tahun=".$tahun." and idkomponen in (1)"; 
				$Umr=fetchdata($str);
				$umrHarian=$Umr[0]['nilai']/25;
				# Jika hari libur maka upah = 0
				# 0 => Staff, 1 => PB, 2 => PKWT, 3 => KHT, 4 => KHL, 5 => Magang, 6 => Kontrak, 7 => Direksi, 8 => Komisaris
				if($jenispremi=='LIBUR'){
					$umrHarian=0;
					$hk=0;
				} else {
					$umrHarian=$umrHarian;
					$hk=1;
				}

				$str = "select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$param['karyawanid']."'";
				$tipe=fetchdata($str);
				$tipeKary=$tipe[0]['tipekaryawan'];
				
				$persenjjg=array(); $ttlpersenjjg=0;
				# ambil data
				$str="select sum(a.brondolan) as brondolan, b.kodeorg 
				from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
				where a.nik='".$param['karyawanid']."' and b.tanggal='".$tanggal."'"; #exit("error".$str);
				$res = fetchData($str);
				foreach($res as $bar){
					$queryParampremibrd  = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"jurnalid='PNN03'");
					$resParampremibrd    = fetchData($queryParampremibrd);
					$akundebetpremibrd   = $resParampremibrd[0]['noakundebet'];
					$kodekegiatanpremibrd= $akundebetpremibrd."03";  
					
					$str = "select * from ".$dbname.".kebun_5premibkm where kodekegiatan='".$kodekegiatanpremibrd."' and unit='".$bar['kodeorg']."' and tanggalberlaku <='".$tanggal."' order by tanggalberlaku desc limit 1"; #exit('error'.$str);
					$val=fetchdata($str);
					$rppremilebihbasis=$val[0]['premilebihbasis'];
					$premisb          =$val[0]['premibasis'];
					$basiskerja       =$val[0]['basis'];
					$kerja            =$val[0]['kerja'];
					$jumat            =$val[0]['jumat'];
					$libur            =$val[0]['libur'];
					if($kerja==''){$kerja=1;}else{$kerja=$kerja;}
					if($jumat==''){$jumat=1;}else{if($jumat=='5/7'){$jumat=5/7;}else{$jumat=$jumat;}}
					if($libur==''){$libur=1;}else{$libur=$libur;}
					
					if($jenispremi=='JUMAT'){
						$basis=round(@$basiskerja*$jumat,2);
					}elseif($jenispremi=='LIBUR'){
						$basis=@$basiskerja*$libur;
					}else{				
						$basis=@$basiskerja*$kerja;
					}
					
					$totalbrd=$bar['brondolan'];
					
					$rplbbss2=$rplbbss1=$upah=$dendaupah=$jlhhk=$potupah=$rpbrondol=0;
					if($bar['brondolan']<$basis){
						$rplbbss2=$rpsiapbasis=0;
						if($tipeKary=='3'){
							$potupah  = floatval($bar['brondolan'])/floatval($basis)*$umrHarian;
							$dendaupah= $umrHarian-$potupah;
							$upah     = $umrHarian;    
							$jlhhk    = ($upah-$dendaupah)/$umrHarian;
						}else{					
							# tidak pakai denda upah
							$potupah  = floatval($bar['brondolan'])/floatval($basis)*$umrHarian;
							$dendaupah= 0;
							$upah     = $potupah;
							$jlhhk    = $upah/$umrHarian;
						}
					}else{
						$upah       = $umrHarian;
						$jlhhk      = $hk;
						$dendaupah  = 0;
						$kglbbrd    = $bar['brondolan']-$basis;
						$rpsiapbasis= $premisb;
						$rpbrondol  = floatval($kglbbrd)*floatval($rppremilebihbasis);
					}
				}
				
				
				$str="select a.*,b.*,a.kodeorg as blok from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
				where a.nik='".$param['karyawanid']."' and b.tanggal='".$tanggal."'"; #exit("error".$str);
				$res = fetchData($str);
				$jlh = count($res); $no=0;
				foreach($res as $bar){
					$no++;
					if($no!=$jlh){
						$upahpro       = round($bar['brondolan']/$totalbrd*$upah,0); $upahprottl+=$upahpro;
						$dendaupahpro  = round($bar['brondolan']/$totalbrd*$dendaupah,0); $dendaupahprottl+=$dendaupahpro;
						$jlhhkpro      = round($bar['brondolan']/$totalbrd*$jlhhk,2); $jlhhkprottl+=$jlhhkpro;
						$basispro      = round($bar['brondolan']/$totalbrd*$basis,0); $basisprottl+=$basispro;
						$rpsiapbasispro= round($bar['brondolan']/$totalbrd*$rpsiapbasis,0); $rpsiapbasisprottl+=$rpsiapbasispro;
						$rpbrondolpro  = round($bar['brondolan']/$totalbrd*$rpbrondol,0); $rpbrondolprottl+=$rpbrondolpro;
					}else{
						$upahpro       = $upah-$upahprottl;
						$dendaupahpro  = $dendaupah-$dendaupahprottl;
						$jlhhkpro      = $jlhhk-$jlhhkprottl;
						$basispro      = $basis-$basisprottl;
						$rpsiapbasispro= $rpsiapbasis-$rpsiapbasisprottl;
						$rpbrondolpro  = $rpbrondol-$rpbrondolprottl;
					}
					
					if(cekmaxnilaihk($param)!=''){
						$add=",jumlahhk='',upahkerja='',upahpenalty='',upahpremi='".$upahpro."'";
					}else{						
						$add=",jumlahhk='".$jlhhkpro."',upahkerja='".$upahpro."',upahpenalty='".$dendaupahpro."',upahpremi=''";
					}
					$strupd =" update ".$dbname.".kebun_prestasi set premibrondol='".$rpbrondolpro."', norma='".$basispro."',premibasis='".$rpsiapbasispro."' ".$add." where notransaksi='".$param['notransaksi']."' and nik='".$param['karyawanid']."' and kodeorg='".$bar['blok']."' and kodesegment='0000000001' and tph='".$bar['tph']."' and sesi='".$bar['sesi']."' and noreferensi='".$bar['noreferensi']."'";
					$owlPDO->exec($strupd);
				}
			}else{				
				hitungpremipanen($param['notransaksi'],$param['karyawanid']);
			}
		break;
	} 
}

function saveheadertosdmabsensi($param){
	global $dbname;
	global $owlPDO;
	#$param['tgl'] = tanggalnormal($param['tgl']);
	
	$str = "select * from  " . $dbname . ".kebun_aktifitas where tanggal='".($param['tgl'])."' and kodeorg='".$param['kodeorg']."'";
	$res=fetchdata($str);
	$arraynik=array();
	foreach($res as $bar){
		$arraynik[$bar['nikmandor']]=$bar['nikmandor'];
		$arraynik[$bar['nikmandor1']]=$bar['nikmandor1'];
		#jika panen nikasisten == kerani
		if($bar['tipetransaksi']=='PNN'){
			$arraynik[$bar['nikasisten']]=$bar['nikasisten'];		
			$tipetrans[$bar['nikasisten']]=$bar['tipetransaksi'];
		}else{
			$arraynik[$bar['keranimuat']]=$bar['keranimuat'];
			$tipetrans[$bar['keranimuat']]=$bar['tipetransaksi'];
		}
	}
	$str="delete from ".$dbname.".sdm_absensidt where tanggal='".($param['tgl'])."' and alokasibiaya='0' and penjelasan like 'Header BKM ##%'";
	$owlPDO->exec($str); 
	
	foreach($arraynik as $nikkary){
		if($nikkary!=''){
			if($tipetrans[$nikkary]=='PNN'){
				$wh="or nikasisten='".$nikkary."'";
			}else{
				$wh="or keranimuat='".$nikkary."'";
			}
			
			$str = "select * from  " . $dbname . ".kebun_aktifitas where tanggal='".($param['tgl'])."' and (nikmandor='".$nikkary."' or nikmandor1='".$nikkary."' ".$wh.")"; 
			$res=fetchdata($str);
			$nomortrans=$nomorbkm=array();
			foreach($res as $bar){
				$nomorbkm[$bar['nobkm']]        =$bar['nobkm'];
				$nomortrans[$bar['notransaksi']]=$bar['notransaksi'];
			}
			
			#pastikan karyawan ada di datakaryawan
			$str = "select * from  " . $dbname . ".datakaryawan where karyawanid='".$nikkary."'"; 
			$res=fetchdata($str);
			if(count($res)==0){
				throw new PDOException("Karyawan dengan ID . ".$nikkary." tidak ada didaftar karyawan.");
			}
			
			$divkary   = makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid = '".$nikkary."'");
			$loktgskary= makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid = '".$nikkary."'");
			
			if($divkary[$nikkary]==''){
				$divisikary=$loktgskary[$nikkary];
			}else{
				$divisikary=$divkary[$nikkary];
			}
			
			
			
			$str = "select * from " . $dbname . ".sdm_absensiht where tanggal='".($param['tgl'])."' and kodeorg='".$divisikary."'";
			$res=count(fetchData($str));
			# jika belum ada di ht maka insert dulu
			if($res==0){
				$data = array(
					'tanggal' => ($param['tgl']),
					'kodeorg' => $divisikary,
					'periode' => substr(($param['tgl']),0,7),
					'updateby'=> $_SESSION['standard']['userid']
				);
				
				$cols = array();
				foreach($data as $key=>$row) {
						$cols[] = $key;
				}

				# Insert sdm_absensiht
				$query = insertQuery($dbname,'sdm_absensiht',$data,$cols); #exit("error".$query." nik".$nikkary);
				$owlPDO->exec($query);
			}
			
			# ambil gaji pokok
			$arrthn = explode("-",$param['tgl']);
			$tahun=$arrthn[0]; $jlhumr=0;
			$namaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$nikkary."'");
			$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$nikkary."' and tahun=".$tahun." and idkomponen in ('1')";					
			$res=fetchdata($str);
			if(count($res)==0){
				throw new PDOException("Gaji pokok karyawan an. ".$namaKary[$nikkary]." belum ada.");
			}
			$jlhumr = $res[0]['nilai']/25;
					
			#belum ada == insert
			# insert
			$data = array(
				'kodeorg'     => $divisikary,
				'tanggal'     => ($param['tgl']),
				'karyawanid'  => $nikkary,
				'absensi'     => 'H',
				'premi'       => 0,
				'hk'          => 1,
				'umr'         => $jlhumr,
				'penjelasan'  => "Header BKM ##",
				'norefrensi'  => implode(",",$nomortrans),
				'nobkm'       => implode(",",$nomorbkm),
				'alokasibiaya'=> '0'
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}

			# Insert sdm_absensidt
			$query = insertQuery($dbname,'sdm_absensidt',$data,$cols);
			$owlPDO->exec($query);
			
		}
	}
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}

function getmandor($param){
	global $dbname;
	global $conn;
	global $owlPDO;
	global $param;
	
	if($param['kodeorg']==''){
		$param['kodeorg']=$_SESSION['empl']['lokasitugas'];
	}
	$start = makeOption($dbname, 'setup_periodeakuntansi', 'kodeorg,tanggalmulai',"kodeorg='".$param['kodeorg']."'");
	$optdivisi="";
	if($param['sumber']=='kebun'){			
		# Divisi
		$wh=" and induk in ('".$param['kodeorg']."')";
		$optdivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING') ".$wh." order by induk, kodeorganisasi";
		$res = fetchData($str);
		foreach($res as $key => $val){
			$d=$val['induk'];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optdivisi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optdivisi.="<option value=".$val['kodeorganisasi']." ".$s.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
			$n=$d;
			if($d!=$n){
				$optdivisi.="</optgroup>";
			}
		}
		
		if($start[$param['kodeorg']]==''){
			exit("Warning : Periode akutansi untuk kebun ".$param['kodeorg']." belum ada.");
		}
	}
	
	# === Option mandor dan kerani ===
	$optAsst=$optMandor1=$optKerani= "<option value=''>&nbsp;</option>";
	$optMandor="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optMandor="<option value=''>&nbsp;</option>";
	
	$kdorg[$param['kodeorg']]=$param['kodeorg'];
	$whereKary=" and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$start[$param['kodeorg']].")";
	
	
	$dt=array();
	$str="select * from ".$dbname.".kebun_5asistensi where kodeorgtujuan ='".$param['kodeorg']."' and  tanggal<='".tanggalsystemn($param['tgl'])."' and tanggalsampai>='".tanggalsystemn($param['tgl'])."' and posting='1' and (tipetrans='PNN' or tipetrans='')";
	$resx=fetchdata($str);
	foreach($resx as $res){
		$kdorg[$res['kodeorgasal']]=$res['kodeorgasal'];
		$divisiasal[$res['divisiasal']]=$res['divisiasal'];
		
		#asistensi hanya beberapa kary
		$s="select * from ".$dbname.".kebun_5asistensi_dt where id ='".$res['id']."' and karyawanid!='0000000000'";
		$r=fetchdata($s);
		foreach($r as $b){
			$dt[$b['karyawanid']]=$b['karyawanid'];
		}
	}
	$whereKary.=" and a.lokasitugas in ('".implode("','",$kdorg)."')";
	if(count($dt)>0){
		$whereKary.=" and (a.subbagian='".$param['divisi']."' or a.karyawanid in ('".implode("','",$dt)."'))";
	}elseif(count($resx)>0){
		if($param['divisi']!=''){
			$whereKary.=" and (a.subbagian='".$param['divisi']."' or a.subbagian in ('".implode("','",$divisiasal)."'))";
		}
	}else{
		if($param['divisi']!=''){
			$whereKary.=" and a.subbagian='".$param['divisi']."'";
		}
	}
	
	
	$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg ='".$param['kodeorg']."' and tipe='PNN'";
	$res=fetchdata($str);
	foreach($res as $bar){
		if($bar['kolom']=='mandor'){
			$mdr=$bar['jabatan'];
		}
		if($bar['kolom']=='mandor1'){
			$mdr1=$bar['jabatan'];
		}
		if($bar['kolom']=='kerani'){
			$krn=$bar['jabatan'];
		}
		if($bar['kolom']=='asst'){
			$asst=$bar['jabatan'];
		}
	}
	
	# Mandor
	$d=$n="";
	if($mdr!=''){
		$whr=" and a.kodejabatan in (".$mdr.")";
	}else{
		$whr=" and b.namajabatan like '%mandor%' and b.namajabatan not like '%mandor%1%'";
	}
	
	$qMandor = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by a.lokasitugas, b.namajabatan,a.namakaryawan asc";
	// exit("error".$qMandor);
	$res=fetchdata($qMandor);
	foreach($res as $row){
		$dkary="";
		if($row['subbagian']!=$param['divisi']){
			$dkary=" [ ".$row['subbagian']." ]";
		}
		$q=getNamaOrg($row['lokasitugas']);
		if($q!=$w){			
			$optMandor.="<optgroup label='".$q."'>";
		}
		
		$d=$row['namajabatan'];
		if($d!=$n){			
			$optMandor.="<optgroup label='".$d."'>";
		}
		
		if($param['nikmandor']==$row['karyawanid']){
			$optMandor.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
		}else{			
			$optMandor.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
		}
		
		$n=$d;
		if($d!=$n){
			$optMandor.="</optgroup>";
		}
		$w=$q;
		if($q!=$w){
			$optMandor.="</optgroup>";
		}
	}

	# Mandor 1
	if($mdr1!=''){
		$whr=" and a.kodejabatan in (".$mdr1.")";
	}else{
		$whr=" and b.namajabatan like '%mandor%1%' ";
	}
	$qMandor1 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by a.lokasitugas,  b.namajabatan,a.namakaryawan asc";
	$d=$n="";
	$res=fetchdata($qMandor1);
	foreach($res as $row){
		$dkary="";
		if($row['subbagian']!=$param['divisi']){
			$dkary=" [ ".$row['subbagian']." ]";
		}
		$q=getNamaOrg($row['lokasitugas']);
		if($q!=$w){			
			$optMandor1.="<optgroup label='".$q."'>";
		}
		
		$d=$row['namajabatan'];
		if($d!=$n){			
			$optMandor1.="<optgroup label='".$d."'>";
		}
		
		if($param['nikmandor1']==$row['karyawanid']){
			$optMandor1.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
		}else{			
			$optMandor1.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
		}	
		$n=$d;
		if($d!=$n){
			$optMandor1.="</optgroup>";
		}
		$w=$q;
		if($q!=$w){
			$optMandor1.="</optgroup>";
		}
	}

	# Asst
	if($asst!=''){
		$whr=" and a.kodejabatan in (".$asst.")";
	}else{
		$whr=" and (b.namajabatan like '%asst%' or "." b.namajabatan like '%asist%'  or namajabatan like '%assist%') and (namajabatan like '%div%'  or namajabatan like '%afd%' or namajabatan like '%kebun%' or namajabatan like '%rawat%' or namajabatan like '%pemel%' or namajabatan like '%panen%')";
	}
	$qAsst = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." ".$divisix." order by a.lokasitugas, b.namajabatan,a.namakaryawan asc";
	$d=$n="";
	$res=fetchdata($qAsst);
	foreach($res as $row){
		if($row['subbagian']!=''){
			$row['subbagian']=$row['subbagian'];
		}else{
			$row['subbagian']=$row['lokasitugas'];
		}
		$q=getNamaOrg($row['lokasitugas']);
		if($q!=$w){			
			$optAsst.="<optgroup label='".$q."'>";
		}
		$d=$row['namajabatan'];
		if($d!=$n){			
			$optAsst.="<optgroup label='".$d."'>";
		}
		$optAsst.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]</option>";
		$n=$d;
		if($d!=$n){
			$optAsst.="</optgroup>";
		}
		$w=$q;
		if($q!=$w){
			$optAsst.="</optgroup>";
		}
	}

	# Kerani
	if($krn!=''){
		$whr=" and a.kodejabatan in (".$krn.")";
	}else{
		$whr=" and (b.namajabatan like '%krani%panen%' or "." b.namajabatan like '%kerani%panen%' or b.namajabatan like '%harves%clerk%') and (b.namajabatan not like '%account%' and b.namajabatan not like '%akunt%' and b.namajabatan not like '%Store%' and b.namajabatan not like '%gudang%' and b.namajabatan not like '%civil%') and a.lokasitugas not like '%M' ";
	}
	$qKerani = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by a.lokasitugas, b.namajabatan,  a.namakaryawan asc";
	$d=$n="";
	$res=fetchdata($qKerani);
	foreach($res as $row){
		if($row['subbagian']!=''){
			$row['subbagian']=$row['subbagian'];
		}else{
			$row['subbagian']=$row['lokasitugas'];
		}
		$q=getNamaOrg($row['lokasitugas']);
		if($q!=$w){			
			$optKerani.="<optgroup label='".$q."'>";
		}
		
		$d=$row['namajabatan'];
		if($d!=$n){			
			$optKerani.="<optgroup label='".$d."'>";
		}
		if($param['kerani']==$row['karyawanid']){
			$optKerani.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]</option>";
		}else{			
			$optKerani.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]</option>";
		}
		$n=$d;
		if($d!=$n){
			$optKerani.="</optgroup>";
		}
		$w=$q;
		if($q!=$w){
			$optKerani.="</optgroup>";
		}
	}
	
	return $optdivisi."####".$optMandor."####".$optMandor1."####".$optKerani."####".$optAsst;
}

?>	