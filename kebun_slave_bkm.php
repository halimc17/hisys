<?php
#ini_set('display_errors',0);
#error_reporting(0);
if($_GET['telid']!=''){
	require_once('master_validation_tel.php');
}else{
	require_once('master_validation.php');
}
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
include_once('lib/formTable.php');
include("lib/mharvest/getContentAPI.php");
$getApi = new getContentAPI;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}
$path              = "fileupload/bkm/";
$method            = checkPostGet('method', '');
$tipe              = checkPostGet('tipe', '');
$notrandetsch      = checkPostGet('notrandetsch', '');
$namakary          = checkPostGet('namakary', '');
$blok              = checkPostGet('blok', '');
$kegiatan          = checkPostGet('kegiatan', '');
$notransaksi       = checkPostGet('notransaksi', '');
$tgl               = checkPostGet('tgl', '');
$kodeorg           = checkPostGet('kodeorg', '');
$filterdivisi      = checkPostGet('filterdivisi', '');
$showpermandor     = checkPostGet('showpermandor', '');
$mandor            = checkPostGet('mandor', '');
$mandor1           = checkPostGet('mandor1', '');
$asst              = checkPostGet('asst', '');
$kerani            = checkPostGet('kerani', '');
$kontanan          = checkPostGet('kontanan', '');
$nobkm             = checkPostGet('nobkm', '');
$blok              = checkPostGet('blok', '');
$karyawanid        = checkPostGet('karyawanid', '');
$kegiatan          = checkPostGet('kegiatan', '');
$kodegudang        = checkPostGet('kodegudang', '');
$mandorsrc         = checkPostGet('mandorsrc', '');
$nobkmsch          = checkPostGet('nobkmsch', '');
$mode              = checkPostGet('mode', '');
$kodeabsen         = checkPostGet('kodeabsen', '');
$filterunit        = checkPostGet('filterunit', '');
$bahasa            = checkPostGet('bahasa', '');
$verifikasisch     = checkPostGet('verifikasisch', '');

$prestasi          = checkPostGet('prestasi', '');
@$prestasi         = str_replace(",","",$prestasi);
@$param['qtymat']  = str_replace(",","",$param['qtymat']);
@$param['prestasi']= str_replace(",","",$param['prestasi']);
@$param['jhk']     = str_replace(",","",$param['jhk']);
@$param['upah']    = str_replace(",","",$param['upah']);
@$param['premi']   = str_replace(",","",$param['premi']);

$statusblok        = $_SESSION['tmp']['kebun']['tipeTrans'];
$stsawal           = checkPostGet('stsawal', '');

$divsch            = checkPostGet('divsch', '');
$tglmulai          = tanggalsystemn(checkPostGet('tglmulai', ''));
$tglselesai        = tanggalsystemn(checkPostGet('tglselesai', ''));
$notransaksisch    = checkPostGet('notransaksisch', '');
$postingsrc        = checkPostGet('postingsrc', '');
$periodesch        = checkPostGet('periodesch', '');
$txtcari           = checkPostGet('txtcari', '');
$kodeorgkary       = checkPostGet('kodeorgkary', '');
#======================= cek admin =========================
$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
$wh='';
if(count($adm)==0){
	$wh= " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}
#======================= cek admin =========================

#===================== Kode Keg ==========================
	$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".setup_kegiatan
			where 1=1 and kelompok in ('BBT','TBM','TM','PNN') and status='1' order by kodekegiatan asc, namakegiatan asc";
	$res=fetchdata($str);
	foreach($res as $bar){
		$optKeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
	}
	
	$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where noakun in (select akunak from ".$dbname.".sdm_5tipeasset) order by noakun,kelompok,namakegiatan";
	$res=fetchdata($str);
	foreach($res as $bar){
		$optKeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
	}
	
#===================== Kode Keg ==========================
	
	
$jab = getPostingJabatan('rawatkebun');	
$tmpTgl = explode('-',$tgl);	
$namaAbsensi = makeOption($dbname,"sdm_5absensi","kodeabsen,keterangan");

switch ($method) {
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
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN','TRAKSI') and kodeorganisasi in ('".implode("','",$divisiasal)."') and kodeorganisasi like '".$filterunit."%'";
		$resx=fetchdata($str);
		foreach($resx as $res){
			$optUnit.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
		}
		
		// exit("error");
		echo $optUnit;
	break;
	case'getprdcari':
		// $optprd = "<option value=''></option>";
		// $wh="";
		// if($_SESSION['empl']['subbagian']!=''){
			// $wh=" and divisi like '".$_SESSION['empl']['subbagian']."%'";
		// }
		// $str="select DISTINCT (substr(tanggal,1,7)) as prd from ".$dbname.".kebun_aktifitas where kodeorg = '".$_SESSION['empl']['lokasitugas']."' ".$wh." order by prd desc";
		// $res = fetchData($str);
		// foreach($res as $key => $val){
			// $data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
		// }
		// $no=0;
		// foreach($data as $thn => $vprd){
			// $optprd.="<option value=".$thn." ".$n.">".$thn."</option>";			
			// foreach($vprd as $prd){
				// $no+=1;$n="";
				// if($no==1){
					// $n="selected";
				// }
				// $optprd.="<option value=".$prd." ".$n.">".$prd."</option>";			
			// }
		// }
		// for($x=0;$x<25;$x++){
			// $dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
			// $optprd.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
		// }
		// echo $optprd;
	break;
	case'simpanheader':
		#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
		$sekarang=  tanggalsystem($tgl);
		if($sekarang<$_SESSION['org']['period']['start']){
			exit("Validation Error : Date out of range");
        }
		$tgl=tanggalsystemn($tgl);
		
		#cek status awal
		stsawal($param);
		
		##cek apakah sudah diinput di detail BKM belum
		$str1 = "select * from " . $dbname . ".kebun_kehadiran_vw where 
		( karyawanid = '".$mandor."' or karyawanid = '".$mandor1."' or karyawanid = '".$kerani."') 
		and tanggal = '".$tgl."' and (jhk > '0' or umr > '0')";
		
		$wherenamaKary= "( karyawanid = '".$mandor."' or karyawanid = '".$mandor1."' or karyawanid = '".$kerani."')";
		$namaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$wherenamaKary);
		$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res);
		if($numrows>0){
			echo "HK Mandor / Mandor 1 / Kerani tidak boleh di input pada detail BKM.<br><br>Karyawan tersebut dibawah ini sudah terdaftar pada transaksi :<br>";
			$no=0;
			while($bar=$res->fetch()){
			   $no+=1;
				echo $no.". ".$namaKary[$bar->karyawanid]." => ".$bar->notransaksi." => ".tanggalnormal($bar->tanggal)."<br>"; 
			}
			exit('Warning: silahkan kosongkan HK pada transaksi tersebut.');
		}
		
		#$whabsensi=" and absensi not in ('H')";
		$str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$tgl."' ".$whabsensi." and karyawanid in ('".$mandor."')";
		if(count(fetchData($str))>0){
			exit("Warning : Mandor sudah pernah diinput di menu Absensi.");
		}
		$str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$tgl."' ".$whabsensi." and karyawanid in ('".$mandor1."')";
		if(count(fetchData($str))>0){
			exit("Warning : Mandor 1 sudah pernah diinput di menu Absensi.");
		}
		$str = "select * from ".$dbname.".sdm_absensidt where tanggal='".$tgl."' ".$whabsensi." and karyawanid in ('".$kerani."')";
		if(count(fetchData($str))>0){
			exit("Warning : Kerani sudah pernah diinput di menu Absensi.");
		}
		
		#=== insert header ===
        if ($mode=='edit') {
			#validasi maksimal HK BHL
			cekmaxnilaihk($mandor,tanggalsystemn($param['tgl']),'1','1','edit',$exit='0');
			cekmaxnilaihk($mandor1,tanggalsystemn($param['tgl']),'1','1','edit',$exit='0');
			cekmaxnilaihk($kerani,tanggalsystemn($param['tgl']),'1','1','edit',$exit='0');
			cekmaxnilaihk($asst,tanggalsystemn($param['tgl']),'1','1','edit',$exit='0');
			
            $str = "update " . $dbname . ".kebun_aktifitas set `nikmandor`='".$mandor."', `nikmandor1`='".$mandor1."',`keranimuat`='".$kerani."',`nikasisten`='".$asst."', divisi='".$param['divisi']."', kontanan='".$kontanan."'
			where `notransaksi`='".$notransaksi."' and `nobkm`='".$nobkm."'"; #exit("error".$str);
			// exit("Warning: ".$str);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        }else{
			#===== buat nomor BKM =====
			$data = $_POST;
			# Data Capture & Reform			
			$data['tgl'] = tanggalsystem($data['tgl']);
			
			#validasi maksimal HK BHL
			cekmaxnilaihk($mandor,tanggalsystemn($param['tgl']),'1','0','new',$exit='0');
			cekmaxnilaihk($mandor1,tanggalsystemn($param['tgl']),'1','0','new',$exit='0');
			cekmaxnilaihk($kerani,tanggalsystemn($param['tgl']),'1','0','new',$exit='0');
			cekmaxnilaihk($asst,tanggalsystemn($param['tgl']),'1','0','new',$exit='0');
			
			#=== Generate No Transaksi
			# Get Existing Data
			$fWhere = "tanggal='".$data['tgl']."' and kodeorg='".$data['kodeorg']."' and tipetransaksi!='PNN'";
			$fQuery = selectQuery($dbname,'kebun_aktifitas','nobkm',$fWhere);
			$tmpNo = fetchData($fQuery);
			
			# Generate No Transaksi
			if(count($tmpNo)==0) {
				$notransaksi = $data['tgl']."/".$data['kodeorg']."/BKM/001";
			} else {
				# Get Max No Urut
				$maxNo = 1;
				foreach($tmpNo as $row) {
					$tmpRow = explode('/',$row['nobkm']);
					$noUrut = (int)$tmpRow[3];
					if($noUrut>$maxNo){
						$maxNo = $noUrut;
					}
				}
				$currNo = addZero($maxNo+1,3);
				$notransaksi = $data['tgl']."/".$data['kodeorg']."/BKM/".$currNo;
			}
			#=== End buat nomor transaksi ===
			if($statusblok==''){
				exit("Warning : Tipe transaksi masih kosong.");
			}
			$str = "insert into " . $dbname . ".kebun_aktifitas (`notransaksi`, `tipetransaksi`, `tanggal`, `nobkm`, `kodeorg`,`divisi`, `nikmandor`, `nikmandor1`, `nikasisten`, `keranimuat`, `jurnal`, `nospk`, `kontanan`, `updateby`)
			values ('".$notransaksi."','".$statusblok."','".$tgl."','" . $notransaksi . "','" . $kodeorg . "','".$param['divisi']."','".$mandor."','".$mandor1."','".$asst."','".$kerani."','0',null,'".$kontanan."','" . $_SESSION['standard']['userid'] . "')"; #exit("error".$str);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
			echo $notransaksi;
		}	
		
		
	break;
    case'detail':
        OPEN_BOX();
		#==== Form Judul Detail ====
		
		$param['tgl']=tanggalsystemn($param['tgl']);
		# Divisi
		$optDivisi='';
		$optPt=$optUnit='';
		$optDivisi.="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$optDivisiabs.="<option value=''>" . $_SESSION['lang']['all'] . "</option>";

		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') and kodeorganisasi like '".$kodeorg."%'";
		$resstr = fetchdata($str);
        foreach($resstr as $res){
			if($param['divisi']==$res['kodeorganisasi']){
				$optDivisi.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
				$optDivisiabs.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}else{
				$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
				$optDivisiabs.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}
		}

	if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' ";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}
	}

	if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}
	}

	if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' ";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}
	}


	## AMBIL TRAKSI
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where  tipe = 'TRAKSI' and induk in (".$dataunitx.")";
	$resstr = fetchdata($str);
	foreach($resstr as $res){
		if($param['divisi']==$res['kodeorganisasi']){
			$optDivisi.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			$optDivisiabs.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
		}else{
			$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			$optDivisiabs.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
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
		
		$str="select * from ".$dbname.".kebun_5asistensi where kodeorgtujuan ='".$param['kodeorg']."' and divisitujuan='".$param['divisi']."' and tanggal<='".$param['tgl']."' and tanggalsampai>='".$param['tgl']."' and posting='1' and (tipetrans='BKM' or tipetrans='')";
		$resx=fetchdata($str);
		foreach($resx as $res){
			$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['kodeorgasal']."'");
			//$optUnit.="<option value=".$res['kodeorgasal'].">".$res['kodeorgasal']." - ".$nmorg[$res['kodeorgasal']]."</option>";
			$dtunit[$res['kodeorgasal']]=$res['kodeorgasal'];
			
			
			$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['divisiasal']."'");
			$optDivisi.="<option value=".$res['divisiasal'].">".$res['divisiasal']." - ".$nmorg[$res['divisiasal']]."</option>";
			$optDivisiabs.="<option value=".$res['divisiasal'].">".$res['divisiasal']." - ".$nmorg[$res['divisiasal']]."</option>";
		}

		$optDivisi.="<option value='PROJECT'>PROJECT - PROJECT</option>";
		
		if(count($resx)>0){
			$dis="";
		}else{
			$dis="disabled";
		}
		
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
		
		#=== TAB PRESTASI DAN KEHADIRAN ===
        $frm[0]="<table><td valign=top>
			<fieldset style=float:left><legend>Filter</legend>
				<table height=25px>
					<td hidden>" . $_SESSION['lang']['pt'] . "</td>
					<td hidden><select style=\"width:50px;\" title=\"Untuk menampilkan data karyawan dari PT lain.\" onchange=\"getunit();\" id=filterpt>".$optPt."</select></td>
					
					<td style=display:none>" . $_SESSION['lang']['unit'] . "</td>
					<td style=display:none><select style=\"width:150px;\" title=\"Untuk assistensi dari unit lain silahkan daftarkan terlebih dahulu melalui menu Kebun - Setup - Assistensi.\" onchange=\"getdivisi();\" id=filterunit ".$dis.">".$optUnit."</select></td>
					
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td><select style=\"width:150px;\"  title=\"Untuk menampilkan data karyawan dari divisi lain.\" onchange=\"getdatadivisi(this.value)\" id=filterdivisi>".$optDivisi."</select></td>
					
					<td>&nbsp;</td>
					<td><input type=checkbox onchange=\"getdatamandor()\" id=showpermandor></td>
					<td>Per Mandor</td>
				</table>
			</fieldset>
			</td><td valign=top>
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
				<legend>Info</legend>
				<table height=25px><td><font color=red><b>* </font>".$_SESSION['lang']['notifobligatory']."</b></td></table>
			</fieldset>
			</td>
			</table>
			<fieldset>
			<legend>" . $_SESSION['lang']['detail'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$frm[0].="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']."</th>
				<th align=center ".$rows."  colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['blok'] . "</th>
				<th align=center ".$rows."  colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['kegiatan']." <input type=checkbox onclick=changelabel(this); id=bahasa title='ID / EN' class='zImgBtn' style='position:relative;top:3px;left:3px;'><span id=labelbahasa>ID</span></th>
				<th align=center ".$rows." width=30px>".$_SESSION['lang']['luas'] . "</th>
				<th align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</th>
				<th align=center colspan=2 width=50px>".$_SESSION['lang']['jumlah']."</th>
				<th align=center colspan=3 >".$_SESSION['lang']['premi']."</th>
				<th align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr>
				<th align=center width=45px>".$_SESSION['lang']['satuan'] . "</th>
				<th align=center width=50px><font color=red><b>*</font></b>".$_SESSION['lang']['jumlah'] . "</th>
				<th align=center width=35px><font color=red><b>* </font></b>HK</th>
				<th align=center width=50px><font color=red><b>* </font></b>".$_SESSION['lang']['upah']."</th>
				<th align=center width=50px>".$_SESSION['lang']['basic']."</th>
				<th align=center width=50px>".$_SESSION['lang']['rpsat']."</th>
				<th align=center width=50px>".$_SESSION['lang']['lebihbasis']."</th>
			</tr>
			<tr id=copy style=display:none>
				<th></th>
				<th colspan=2></th>
				<th colspan=2 align=center valign=center>Copy <input type=checkbox title='Aktif / Non Aktif' id=copyblok class='zImgBtn' style='position:relative;top:3px;left:3px;'></th>
				<th colspan=2 align=center valign=center>Copy <input type=checkbox title='Aktif / Non Aktif' id=copykeg class='zImgBtn' style='position:relative;top:3px;left:3px;'></th>
				<th></th>
				<th></th>
				<th align=center><input type=checkbox id=copypres title='Aktif / Non Aktif' class='zImgBtn' style='position:relative;top:3px;left:3px;'></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th colspan=2></th>
				
			</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		#=== Isi input detail ===
		$frm[0].="<tbody id=inputdetail>
				<script>inputdetail()</script>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input detail ===	
		$frm[0].="<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['notransaksi'] . "</td><td>:</td>
							<td><input tipe=text style=width:125px onchange=loaddatadetail() class=myinputtext id=notrandetsch></td>
							
							<td>" . $_SESSION['lang']['namakaryawan'] . "</td><td>:</td>
							<td><input tipe=text class=myinputtext onchange=loaddatadetail() id=namakarydetsch></td>
							
							<td>" . $_SESSION['lang']['blok'] . "</td><td>:</td>
							<td><input tipe=text style=width:100px onchange=loaddatadetail() class=myinputtext id=blokdetsch></td>
							
							<td>" . $_SESSION['lang']['kegiatan'] . "</td><td>:</td>
							<td><input tipe=text class=myinputtext onchange=loaddatadetail() id=kegdetsch></td>
							
							<td><button class=mybutton onclick=loaddatadetail()>" . $_SESSION['lang']['preview'] . "</button></td>
							<td><button class=mybutton onclick=loaddatadetailxls('','dt')>" . $_SESSION['lang']['excel'] . "</button></td>
							<td><button class=mybutton onclick=cancelcari()>" . $_SESSION['lang']['cancel'] . "</button></td>
						</tr>
					
					</table>
				</fieldset>
				<div style=clear:both></div>
				";
        $frm[0].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
				<div style=clear:both></div>
			<div id=loaddatadetail>
				<script>loaddatadetail()</script>
			</div></fieldset>";
			
		#=== TAB MATERIAL ===
		$frm[1]="<table><td valign=top>
			<fieldset style=float:left>
				<legend>Info</legend>
					<table height=25px><td><font color=red><b>* </font>".$_SESSION['lang']['notifobligatory']."</b></td>
					<td>&nbsp;||&nbsp;</td><td>Hanya kegiatan yang sudah di daftarkan materialnya melalui menu <b>Setup - Kegiatan</b> yang di munculkan dan pastikan material memiliki saldo yang cukup.</td>
					</table>
			</fieldset>
			</td></table>
			<fieldset>
			<legend>" . $_SESSION['lang']['material'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$frm[1].="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['notransaksi'] . "</th>
				<th align=center width=55px ".$rows.">".$_SESSION['lang']['kodekegiatan'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['blok'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['gudang']."</th>
				<th align=center ".$rows." colspan=2>".$_SESSION['lang']['namabarang']."</th>
				<th align=center colspan=2 width=50px>".$_SESSION['lang']['material']."</th>
				<th align=center ".$rows." colspan=2>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr>
				<th align=center width=35px>".$_SESSION['lang']['satuan']."</th>
				<th align=center width=50px><font color=red><b>* </font></b>".$_SESSION['lang']['jumlah']."</th>
				
				
			</tr>
			</thead>";
		#==== Form Judul material ====
		
		#=== Isi input material ===
		$frm[1].="<tbody id=inputdetailmaterial>
				<script>inputdetailmaterial()</script>
				</tbody></table></fieldset>";
		
		#=== List data tersimpan input material ===	
        $frm[1].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['material'] . "</legend>
			<div id=loaddatadetailmaterial>
				<script>loaddatadetailmaterial()</script>
			</div></fieldset>";
		
		//$optDivisiabs="<option value=".$param['divisi'].">".$param['divisi']." - ".getNamaOrg($param['divisi'])."</option>";
		#=== TAB ABSENSI ===
		$frm[2]="<table border=0><td valign=top>
			<fieldset style=float:left;height:70px; >
				<legend>Info</legend>
					<table height=25px border=0>
						<tr><td>" . $_SESSION['lang']['divisi'] . "</td>
						<td><select style=\"width:150px;\" title=\"Untuk menampilkan data karyawan dari divisi lain.\" onchange=\"getdataabs(this.value)\" id=filterdivisiabsensi>".$optDivisiabs."</select></td></tr>
						
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
			<table border=0 cellpadding=1 cellspacing=1 class=sortable>
			<thead><tr class=rowheader style=height:25px>";
		
		## HIDE INPUT PREMI KEGIATAN RAWAT (PERMINTAAN PALMA SESUAI TIKET SUPPORT)
		$str = "select nilai,kodeorg from ".$dbname.".setup_parameterappl where kodeparameter='HDPREMI' and kodeorg = '".getindukPT($_SESSION['empl']['lokasitugas'])."'"; 
		$res = fetchdata($str);
		$get_jabatan = explode(',', $res[0]['nilai']);

		$hidden_tombol = 'hidden';
		if($res[0]['kodeorg'] == getindukPT($_SESSION['empl']['lokasitugas'])){
			if (in_array($_SESSION['empl']['kodejabatan'],$get_jabatan)) {
				$hidden_tombol = '';
			}
		}else{
			$hidden_tombol = '';
		}


		$rows="rowspan=1";	
		$frm[2].="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']."</th>
				<th align=center ".$rows.">".$_SESSION['lang']['akun'] . "</th>
				<th align=center ".$rows.">Absensi</th>
				<th align=center ".$rows.">".$_SESSION['lang']['hk2'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['upah'] . " Rp</th>
				<th ".$hidden_tombol." align=center ".$rows.">".$_SESSION['lang']['premi'] . "</th>
				<th align=center ".$rows."><font color=red><b>* </font></b> ".$_SESSION['lang']['keterangan'] . "</th>
				<th align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</th>
			</tr>
			</thead>";
		#==== Form Judul Absensi ====
		
		#=== Isi input Absensi ===
		$optabs = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
		$str="select * from ".$dbname.".sdm_5absensi where status='1' AND absensidt='1'";
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
		// $wh=" and substr(noakun,1,3) in ('711')";
		$wh=" and noakun like '7%'";
		$wh.=" and substr(noakun,1,3) not in ('719','715')";
		$sjnskrj="select * from ".$dbname.".keu_5akun where length(noakun)='7' and namaakun not like '%NON AKTIF%' ".$wh." and aktif='1' order by noakun asc";
		// exit("error".$sjnskrj);
		$optJnsKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=fetchdata($sjnskrj);
		$n="";
		$z=0;
		foreach($res as $rjnskrj){
			$d=substr($rjnskrj['noakun'],0,5);
			if($d!=$n){
				$z++;
				if ($z > 1) {
					$optJnsKerja.="</optgroup>";
				}
				$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
				$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
			}
			$e="";
			if($rjnskrj['noakun']==$akun){
				$e="selected";
			}
			$optJnsKerja.="<option value=".$rjnskrj['noakun']." ".$e.">".$rjnskrj['noakun']." - ".$rjnskrj['namaakun']."</option>";
			$n=$d;
		}
		
		$optKary = getoptkary($param);
		

		
				// <td><input type=text style=\"width:75px;\" id=premiabsen class=myinputtextnumber onkeyup=\"z.numberFormat('premiabsen',2)\" nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
		$frm[2].="<tbody id=inputdetailabsensi>
				<tr class=rowcontent>
				<td id=no align=center>1</td>
				
				
				<td colspan=2><select style=width:200px id=karyawanidabsensi onchange=getumrabsensi(); class=select2>".$optKary."</select></td>
				<!--<td width=20px>
				<img id='karyawanidabsensi' onclick=z.elSearch('karyawanidabsensi',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>-->

				<td><select class=select2 style=width:180px onmousemove=hapuswarna(this.id); id=noakunabsensi>".$optJnsKerja."</select></td>
				<td><select  class=select2 style=width:90px onchange=getnilaihk(); onmousemove=hapuswarna(this.id); id=kodeabsen>".$optabs."</select></td>
				
				<td>
					<input onkeyup=getumrabsensi(); disabled id=jhkabsen class=myinputtextnumber value=".$isihk." onkeypress=\"return angka_doang(event);\" style=\"width:35px;\">
					<input type=hidden value=".$isihk." id='nilaihkabs' disabled>
				</td>
				
				<td><input id=upahabsen onkeyup=gethk(this.id,'jhkabsen','karyawanidabsensi',''); disabled class=myinputtextnumber style=\"width:75px;\"></td>
				<td ".$hidden_tombol." ><input type=text style=\"width:75px;\" id=premiabsen class=myinputtextnumber onkeyup=\"z.numberFormat('premiabsen',2)\" nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
				<td><input id=keteranganabsen class=myinputtext style=\"width:200px;\"></td>
				
				
				
				<td  align=center width=20px><input type=hidden id=method value='insert'>
					<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"saveabsensi()\" src='images/save.png'/></td>
				<td  align=center width=20px>
					<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"clearabsensi()\" src='images/clear.png'/>
				</td>
			</tr><tr>
				<td colspan=9></td>
				<td  align=center width=20px>
					<input hidden id=methodabsensi value='insertabsensi'>
					<input hidden id=kodeorgabsensi>
					<img title='Refresh List Data' class=zImgBtn onclick=\"loaddataabsensi()\" src='images/refresh2.png'/></td>
				<td  align=center width=20px>
					<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
				</td>
			</tr>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input Absensi ===	
        $frm[2].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " Absensi</legend>
			<div id=loaddataabsensi></div></fieldset>";
			
		$hfrm[0]=$_SESSION['lang']['prestasi']." & ".$_SESSION['lang']['absensi'];
		$hfrm[1]=$_SESSION['lang']['material'];
		$hfrm[2]='Absensi';

		# draw tab, jangan ganti parameter pertama, krn dipakai di javascript
		drawTab('FRM',$hfrm,$frm,175,'100%');

        CLOSE_BOX();
		
	break;
	case'loaddataabsensi':
		$tab.="<table border=0 cellpadding=2 cellspacing=1 class=sortable style=min-width:800px>
			<thead><tr class=rowheader style=height:25px>";
			
		$rows="rowspan=1";	
		$tab.="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center ".$rows.">".$_SESSION['lang']['noakun'] . "</th>
				<th align=center width=100px ".$rows.">Absensi</th>
				<th align=center width=40px ".$rows.">".$_SESSION['lang']['hk2'] . "</th>
				<th align=center width=100px ".$rows.">".$_SESSION['lang']['upah'] . " Rp</th>
				<th align=center width=100px ".$rows.">".$_SESSION['lang']['premi'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['keterangan'] . "</th>
				<th align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</th>
			</tr>
			</thead><tbody>";
		
		$thk=$tumr=$tpremi=$ttltk=0;
		$str = "select * from ".$dbname.".sdm_absensidt where 1=1 and (norefrensi='".$notransaksi."' or nobkm='".$nobkm."') and nobkm='".$nobkm."' and tanggal='".tanggalsystemn($tgl)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while($bar=$res->fetch()){
			$no+=1;
			$optnik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$bar['karyawanid']."'");
			
			if($optnik[$bar['karyawanid']]!=''){
				$bar['nik']=$optnik[$bar['karyawanid']]." - ";
			}
			if(getKary($bar['karyawanid'],'subbagian')!=''){
				$bar['subbagian']=getKary($bar['karyawanid'],'subbagian')." - ";
			}
			
			$tab.="<tr class=rowcontent  style=height:25px id=rowabslist_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>" . $bar['nik'].$bar['subbagian'].getNamaKaryawan($bar['karyawanid']). "</td>";

			$tab.="<td align=left>".$bar['noakun']." - ".getNamaAkun($bar['noakun'])."</td>";
			$tab.="<td align=center>".$namaAbsensi[$bar['absensi']]."</td>";
			$tab.="<td align=right>".@numb_format($bar['nilaihk'],2)."</td>";
			// if(getKary($bar['karyawanid'],'tipekaryawan')!='4'){
			// 	$tab.="<td align=right>*******</td>";
			// }else{				
			// 	$tab.="<td align=right>".@numb_format($bar['umr'],2)."</td>";
			// }
			$tab.="<td align=right>".@numb_format($bar['umr'],2)."</td>";
			$tab.="<td align=right>".@numb_format($bar['premi'],2)."</td>";
			$tab.="<td>".$bar['penjelasan']."</td>";
			
			$tab.="<td align=center  width=20px>
				<img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"editabsensi('".$bar['karyawanid']."','".$bar['absensi']."','".$bar['nilaihk']."','".$bar['umr']."','".$bar['premi']."','".$bar['penjelasan']."','".$bar['kodeorg']."','".$bar['noakun']."');\">
				</td>
				
				<td align=center  width=20px>
				<img title='".$_SESSION['lang']['delete']."' class=zImgBtn onclick=\"delabsen('".$notransaksi."','".$tgl."','".$bar['kodeorg']."','".$bar['karyawanid']."')\" src='images/skyblue/delete.png'/>
				
				
			</td>";
			$tab.="</tr>";
			
			@$thk+=$bar['nilaihk'];
			if(getKary($bar['karyawanid'],'tipekaryawan')!='4'){				
			}else{
				@$tumr+=$bar['umr'];
			}
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
		$tab.="</tr>";
		
		$ttltk=$no;
		
		#absensi dari BKM
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=10 bgcolor=#2C3E50></td>";
		$tab.="</tr>";
		$thkb=$tumrb=$tpremib=0;
		$str = "select a.nobkm, a.nikpemel, sum(c.jhk) as jhk, sum(c.umr) as umr, sum(c.insentif) as insentif, c.absensi
			from " . $dbname . ".kebun_prestasi a 
			left join " . $dbname . ".kebun_kehadiran c on a.nikpemel=c.nik and a.notransaksi=c.notransaksi and a.nourut=c.nourut
			where a.nobkm='" . $nobkm . "' group by a.nikpemel"; #exit('error'.$str);
		$res = fetchdata($str);
		$no=0;
		foreach($res as $bar){
			$no++;
			$optnik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$bar['nikpemel']."'");
			$optsbg=makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$bar['nikpemel']."'");
			
			if($optnik[$bar['nikpemel']]!=''){
				$bar['nik']=$optnik[$bar['nikpemel']]." - ";
			}
			if($optsbg[$bar['nikpemel']]!=''){
				$bar['subbagian']=$optsbg[$bar['nikpemel']]." - ";
			}
			
			$tab.="<tr class=rowcontent style=color:gray;height:25px; title=\"Untuk melakukan edit atau delete silahkan buka di tab Prestasi dan Kehadiran\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left colspan=2>" . $bar['nik'].@$bar['subbagian'].getNamaKaryawan($bar['nikpemel']) . "</td>";
			$tab.="<td align=center>".$bar['absensi']."</td>";
			$tab.="<td align=right>".numb_format($bar['jhk'],2)."</td>";
			$tab.="<td align=right>".numb_format($bar['umr'])."</td>";
			$tab.="<td align=right>".numb_format($bar['insentif'])."</td>";
			$tab.="<td></td>";	
			$tab.="<td></td>";	
			$tab.="<td></td>";
			
			@$thkb+=$bar['jhk'];
			@$tumrb+=$bar['umr'];
			@$tpremib+=$bar['insentif'];
		}
		#sub total bkm
		$tab.="<tr class=rowcontent style=background-color:#AED6F1;font-weight:bold;height:25px>";
		$tab.="<td align=center>".numb_format($no)."</td>";
		$tab.="<td align=center colspan=3>Sub Total (BKM)</td>";
		$tab.="<td align=right>".numb_format($thkb,2)."</td>";
		$tab.="<td align=right>".numb_format($tumrb)."</td>";
		$tab.="<td align=right>".numb_format($tpremib)."</td>";
		$tab.="<td></td>";
		$tab.="<td width=20px></td>";
		$tab.="<td width=20px></td>";
		$tab.="</tr>";
		
		$ttltk+=$no;
		
		#Grand Total
		$tab.="<tr class=rowcontent style=background-color:#A3E4D7;font-weight:bold;height:25px>";
		$tab.="<td align=center>".numb_format($ttltk)."</td>";
		$tab.="<td align=center colspan=3>Grand Total (Absensi + BKM)</td>";
		$tab.="<td align=right>".numb_format($thkb+$thk,2)."</td>";
		$tab.="<td align=right>".numb_format($tumrb+$tumr)."</td>";
		$tab.="<td align=right>".numb_format($tpremib+$tpremi)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		
		
		if($_SESSION['empl']['subbagian']==''){
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
		}
		
		$str = "select * from ".$dbname.".sdm_absensidt where tanggal='".tanggalsystemn($param['tgl'])."' and norefrensi='' and nobkm='' ".$whr.""; #exit('error'.$str);
		$res = fetchdata($str);
		if(count($res)>0){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=10 bgcolor=#2C3E50></td>";
			$tab.="</tr>";
			$tab.="<tr class=rowcontent style=height:25px>";
			$tab.="<td colspan=10 bgcolor=#E7E7FF style=font-weight:bold>Informasi karyawan yang di absen melalui menu SDM - Transaksi - Absensi</td>";
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
				$tab.="<tr class=rowcontent style=background-color:#E7E7FF;color:gray;height:25px;>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".$nikx."".$divx."".getNamaKaryawan($bar['karyawanid'])."</td>";
				$tab.="<td align=left>".$bar['noakun']." - ".getNamaAkun($bar['noakun'])."</td>";
				$tab.="<td align=center>".$bar['absensi']."</td>";
				$tab.="<td align=right>".numb_format($bar['hk'],2)."</td>";
				
				if(getKary($bar['karyawanid'],'tipekaryawan')!='4'){
					$tab.="<td align=right>*******</td>";
				}else{				
					$tab.="<td align=right>".@numb_format($bar['umr'],2)."</td>";
					@$tumr+=$bar['umr'];
				}
				
				$tab.="<td align=right>".numb_format($bar['premi'],2)."</td>";
				$tab.="<td align=left>".$bar['penjelasan']."</td>";
				$tab.="<td width=20px></td>";
				$tab.="<td width=20px></td>";
				$tab.="</tr>";
				
				@$thk+=$bar['hk'];
				@$tpremi+=$bar['premi'];
			}
			$tab.="<tr class=rowcontent style=background-color:#AED6F1;font-weight:bold;height:25px>";
			$tab.="<td align=center>".numb_format($no)."</td>";
			$tab.="<td align=center colspan=3>Sub Total SDM Absensi</td>";
			$tab.="<td align=right>".numb_format($thk,2)."</td>";
			$tab.="<td align=right>".numb_format($tumr)."</td>";
			$tab.="<td align=right>".numb_format($tpremi)."</td>";
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
			
			
			cekPrestasi($param);
			
			#validasi maksimal HK KHL
			if (getKary($param['karyawanid'],'tipekaryawan') == 4) {
				cekmaxnilaihk($param['karyawanid'],tanggalsystemn($param['tgl']),'1','0','new',$exit='0');
			}
			
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
			
			# Validasi penginputan
			cekPrestasi($param);
			
			#validasi maksimal HK BHL
			cekmaxnilaihk($param['karyawanid'],tanggalsystemn($param['tgl']),'1','1','edit',$exit='0');
			
			# ==========================================================================================
			$data = array(
				'absensi'   =>$param['kodeabsen'],
				'hk'        =>$param['jhk'],
				'noakun'    =>$param['noakun'],
				'umr'       =>$param['upah'],
				'premi'     =>$param['premi'],
				'penjelasan'=>$param['keterangan']
			);
			$where = "(norefrensi='".$param['notransaksi']."' or nobkm='".$param['nobkm']."') and tanggal='".tanggalsystemn($param['tgl'])."' and karyawanid='".$param['karyawanid']."' and kodeorg='".$param['kodeorgabsensi']."'";
			
			# Update sdm_absensidt
			$query = updateQuery($dbname,'sdm_absensidt',$data,$where);;
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
	
	case 'caribarang':
		$tab="";
		$no=0;
		$tab.="<table class=sortable cellpadding=5 cellspacing=1 border=0 style=width:100%>
			<thead>
			<tr class=rowheader>
				<th rowspan=2 align=center>No</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['namabarang']."</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['satuan']."</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['saldo']." Gudang</th>
				<th rowspan=2 align=center width=50px>Pemakaian Blm Post</th>
				<th rowspan=2 align=center width=50px>".$_SESSION['lang']['saldo']." Akhir Gudang</th>
				<th rowspan=1 colspan=3 align=center width=50px>Hitung stok transaksi</th>
			</tr>
			<tr>
				<th rowspan=1 align=center width=50px>Masuk</th>
				<th rowspan=1 align=center width=50px>Keluar</th>
				<th rowspan=1 align=center width=50px>Masuk - Keluar</th>
			</tr>
			</thead>
			<tbody>";
		$arrprd = explode("-",tanggalsystemn($param['tgl']));
		$periode = $arrprd[0]."-".$arrprd[1];
		
		$str="select a.kodebarang,a.namabarang,a.satuan,b.saldoakhirqty as saldoqty,b.saldoawalqty 
		from ".$dbname.".log_5masterbarang a 
		left join ".$dbname.".log_5saldobulanan b on a.kodebarang=b.kodebarang 
		where (a.namabarang like '%".$txtcari."%' or a.kodebarang like '%".$txtcari."%') and b.kodegudang='".$kodegudang."' and b.saldoakhirqty >'0' and a.kodebarang in (select kodebarang from ".$dbname.".setup_kegiatannorma where kodekegiatan='".$kegiatan."') and b.periode='".$periode."'";
		$res=fetchData($str);
		if(count($res)>0){			
			foreach($res as $val){
				$s="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where kodebarang='".$val['kodebarang']."' and kodegudang='".$kodegudang."' and tanggal like '".$periode."%' and post='0' and tipetransaksi>4";
				$r=fetchData($s);
				$logblmpost = $r[0]['jumlah'];
				
				$queryM="select sum(kwantitas) as jlh from ".$dbname.".kebun_pakai_material_vw where kodegudang='".$kodegudang."' and kodebarang='".$val['kodebarang']."' and tanggal like '".$periode."%' and notransaksi in (select notransaksi from ".$dbname.".kebun_aktifitas where jurnal='0' and kodeorg='".substr($kodegudang,0,4)."')";
				$dataM = fetchData($queryM);
				$pakaibkm = $dataM[0]['jlh'];
				$pakaiblmpost = $pakaibkm+$logblmpost;
				
				
				$i="";
				if(($val['saldoqty']-$pakaiblmpost)<0){
					$i = "style=color:red;font-weight:bold;";
				}


				// bandingkan saldo masuk dan keluar
				#ambil data keluar
				$s_0="select sum(jumlah) as jumlah from " . $dbname . ".log_transaksi_vw where kodegudang='" . $kodegudang . "' and tanggal like '".$periode."%' and kodebarang='".$val['kodebarang']."'
				and tipetransaksi>4 and statussaldo=1 group by kodebarang";
				$r_0=fetchData($s_0);
				$jumlah_transaksikeluar = $r_0[0]['jumlah'];
				
				#ambil data masuk
				$s_1="select sum(jumlah) as jumlah from " . $dbname . ".log_transaksi_vw where kodegudang='" . $kodegudang . "' and tanggal like '".$periode."%' and kodebarang='".$val['kodebarang']."'
				and tipetransaksi<5 and statussaldo=1 group by kodebarang";
				$r_1=fetchData($s_1);
				$jumlah_transaksimasuk = $r_1[0]['jumlah'];

				$total_SALDOMASUK = $val['saldoawalqty'] + $jumlah_transaksimasuk;
				$total_SALDOkeluar = $jumlah_transaksikeluar + $pakaiblmpost;

				if($total_SALDOMASUK <= $total_SALDOkeluar){
					$tr_cek = "<tr class=rowcontent style='background-color:orange'>";
				}else{
					$tr_cek = "<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"loadField('".$val['kodebarang']."','".$val['namabarang']."','".$val['satuan']."');\">";
				}


				
				
				$no+=1;
				$tab.="
				".$tr_cek."
					<td align=center>".$no."</td>
					<td align=center>".$val['kodebarang']."</td>
					<td>".$val['namabarang']."</td>
					<td>".$val['satuan']."</td>
					<td align=right>".number_format($val['saldoqty'],3)."</td>
					<td align=right>".number_format($pakaiblmpost,3)."</td>
					<td align=right ".$i.">".number_format($val['saldoqty']-$pakaiblmpost,3)."</td>
					<td align=right style='background-color:yellow'>".number_format($total_SALDOMASUK,3)."</td>
					<td align=right style='background-color:yellow'>".number_format($total_SALDOkeluar,3)."</td>
					<td align=right style='background-color:yellow'>".number_format($total_SALDOMASUK-$total_SALDOkeluar,3)."</td>
				</tr>";	
			}
		}else{
			$tab.="<tr class=rowcontent style='cursor:pointer;'>
				<td align=left colspan=7>Jika daftar barang tidak muncul :<br>1. Daftarkan terlebih dahulu kode barang melalui Setup - Kegiatan (silahkan hubungi Administrator)<br>2. Mutasikan barang dari Gudang Central ke Gudang Divisi.<br>3. Posting mutasi barang dari Gudang Central.<br>4. Lakukan penerimaan mutasi di Gudang Divisi.<br>5. Posting penerimaan mutasi barang di Gudang Divisi.</td>
			</tr>";			
		}
		$tab.="</table>";
		
		echo $tab;
	break;
	
	case'inputdetailmaterial':
		$str = "select sum(hasilkerja) as hasilkerja, kodekegiatan, kodeorg,notransaksi from ".$dbname.".kebun_prestasi where 1=1 and notransaksi in (select notransaksi from ".$dbname.".kebun_aktifitas where nobkm='".$notransaksi."') and kodekegiatan in (select kodekegiatan from ".$dbname.".setup_kegiatannorma) group by kodekegiatan, kodeorg order by kodekegiatan asc, kodeorg asc"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while($bar=$res->fetch()){
			$strv = "select * from ".$dbname.".kebun_5gudangtransaksi where afdeling='".substr($bar['kodeorg'],0,6)."' and status='1'";
			$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
			$resv->setFetchMode(PDO::FETCH_ASSOC);
			$barv=$resv->fetch();
		
			$queryM = "select * from ".$dbname.".kebun_pakaimaterial where 1=1 and notransaksi='".$bar['notransaksi']."' and kodekegiatan='".$bar['kodekegiatan']."' and kodeorg='".$bar['kodeorg']."'";
			$dataM = fetchData($queryM);
			$i="";
			if(count($dataM)==0){
				$i = "style=color:red; title=\"Material belum diinput\"";
			}
			
			$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$bar['kodekegiatan']."'");
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kodekegiatan']."'");
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$barv['kodegudang']."'");
			$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok',"indukblok='".$bar['kodeorg']."'");
			
			$no+=1;
			$tab.="<tr class=rowcontent ".$i." id=rowmat_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td id=notran".$no.">".$bar['notransaksi']."</td>";
			$tab.="<td id=kegiatanmat".$no.">".$bar['kodekegiatan']."</td>";
			$tab.="<td>".$nmkeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td id=blokmat".$no." hidden>".$bar['kodeorg']."</td>";
			$tab.="<td>".$nminduk[$bar['kodeorg']]."</td>";
			$tab.="<td id=pres".$no." style=display:none>".$bar['hasilkerja']."</td>";
			$tab.="<td id=kodegudang".$no." style=display:none>".$barv['kodegudang']."</td>";
			$tab.="<td>".$barv['kodegudang']." - ".$nmorg[$barv['kodegudang']]."</td>";
			$tab.="<td>
					<input type=text id=kodemat".$no." class=myinputtext style='width:60px;' onclick=\"searchmat('".$no."','Find',event);\" onmousemove=hapuswarna(this.id); readonly></td>
					<td>
					<input type=text id=namamat".$no." class=myinputtext style='width:150px;' onclick=\"searchmat('".$no."','Find',event);\" onmousemove=hapuswarna(this.id); readonly></td>";
			$tab.="<td><input id=satmat".$no." class=myinputtext disabled style=\"width:35px;\"></td>";
			$tab.="<td><input id=qtymat".$no." onclick=\"hapuswarna(this.id);\" onkeyup=\"z.numberFormat('qtymat".$no."',2);\"  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
			$tab.="<td  align=center width=20px>
				<img title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=\"savematerial(".$no.")\" src='images/save.png'/></td>
				<td  align=center width=20px>
				<img title='".$_SESSION['lang']['clear']."' class=zImgBtn onclick=\"clearmaterial(".$no.")\" src='images/clear.png'/>
			</td>";
			$tab.="</tr>";
		}
		$tab.="<tr>
			<td colspan=10></td>
			<td  align=center width=20px>
				<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetailmaterial()\" src='images/refresh2.png'/></td>
			<td  align=center width=20px>
				<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
			</td>
        </tr>";
		
		echo $tab;
	break;
	
	case'loaddatadetailmaterial':
		$tab.="<table border=0 cellpadding=2 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
		$tab.="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['notransaksi'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['kodekegiatan'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['blok'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['gudang']."</th>
				<th align=center ".$rows.">".$_SESSION['lang']['namabarang']."</th>
				<th align=center colspan=2 width=50px>".$_SESSION['lang']['material']."</th>
				<th align=center ".$rows." width=35px>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr>
				<th align=center width=35px>".$_SESSION['lang']['satuan']."</th>
				<th align=center width=50px>".$_SESSION['lang']['jumlah']."</th>
				
				
			</tr>
			</thead><tbody>";
		$datamat=$kdgdng=$jlhmat=$datakdmat=array();
		$str = "select * from ".$dbname.".kebun_pakaimaterial where 1=1 and notransaksi in (select notransaksi from ".$dbname.".kebun_aktifitas where nobkm='".$notransaksi."') order by kodekegiatan, kodeorg";
		$res=fetchdata($str);
		foreach($res as $bar){
			$datamat[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]=$bar['kodebarang'];
			$kdgdng[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]=$bar['kodegudang'];
			@$jlhmat[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]+=$bar['kwantitas'];
			$notrans[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']]=$bar['notransaksi'];
			$datakdmat[$bar['kodebarang']]=$bar['kodebarang'];
		}

		if(count($datamat)==0){
			$tab.="<tr class=rowcontent><td colspan=12 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		
		$no=0;
		foreach($datamat as $kdkeg => $valkdorg){
			foreach($valkdorg as $kdorg => $valbrg){
				foreach($valbrg as $kdbrg => $kodebrg){
					$no+=1;
					$nmsatbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kdbrg."'");
					$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
					$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kdkeg."'");
					$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$kdkeg."'");
					$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kdgdng[$kdkeg][$kdorg][$kdbrg]."'");
					$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok',"indukblok='".$kdorg."'");
					
					$strv = "select sum(hasilkerja) as hasilkerja from ".$dbname.".kebun_prestasi where notransaksi='".$notrans[$kdkeg][$kdorg][$kdbrg]."' and kodekegiatan='".$kdkeg."' and kodeorg='".$kdorg."'"; //exit('error'.$strv);
					$barv=fetchdata($strv);
					
					$tab.="<tr class=rowcontent id=rowmatlist_".$no." style=height:25px>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$notrans[$kdkeg][$kdorg][$kdbrg]."</td>";
					$tab.="<td>".$kdkeg."</td>";
					$tab.="<td>".$nmkeg[$kdkeg]."</td>";
					$tab.="<td>".$nminduk[$kdorg]."</td>";
					$tab.="<td>".$kdgdng[$kdkeg][$kdorg][$kdbrg]." - ".$nmorg[$kdgdng[$kdkeg][$kdorg][$kdbrg]]."</td>";
					$tab.="<td>".$kdbrg." - ".$nmbrg[$kdbrg]."</td>";
					$tab.="<td align=center>".$nmsatbrg[$kdbrg]."</td>";
					$tab.="<td align=right>".@numb_format($jlhmat[$kdkeg][$kdorg][$kdbrg],2)."</td>";
					$tab.="<input type=hidden id=method value='insertmaterial'>";
					
					
					@$ttljlhmat[$kdbrg]+=$jlhmat[$kdkeg][$kdorg][$kdbrg];
					
						#cek apakah sudah di posting	
						$optpost=makeOption($dbname,'kebun_aktifitas','notransaksi,jurnal',"notransaksi='".$notrans[$kdkeg][$kdorg][$kdbrg]."'");
						if($optpost[$notrans[$kdkeg][$kdorg][$kdbrg]]==0){
							$tab.="<td align=center>
								<img title='".$_SESSION['lang']['delete']."' class=zImgBtn onclick=\"delmaterial('".$notrans[$kdkeg][$kdorg][$kdbrg]."','".$kdkeg."','".$kdorg."','".$kdbrg."')\" src='images/skyblue/delete.png'/>
							</td>";					
						}else{
							$tab.="<td align=center>Posted</td>";
						}
						
					$tab.="</tr>";
				}
					// $tab.="<tr class=rowcontent style=background-color:#2C3E50>";
					// $tab.="<td colspan=12></td>";
					// $tab.="</tr>";
			}
		}
		
		$no=0;
		foreach($datakdmat as $kodemat){
			$nmsatbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kodemat."'");
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodemat."'");
					
			$no++;
			$tab.="<tr class=rowcontent style=background-color:#A3E4D7;height:25px>";
			if($no==1){				
				$tab.="<td align=center rowspan=".count($datakdmat)." colspan=6><b>REKAPITULASI</b></td>";
			}
			$tab.="<td>".$kodemat." - ".$nmbrg[$kodemat]."</td>";
			$tab.="<td align=center>".$nmsatbrg[$kodemat]."</td>";
			$tab.="<td align=right>".@numb_format($ttljlhmat[$kodemat],3)."</td>";
			$tab.="<td></td>";
			$tab.="</tr>";
		}
		
		$tab.="</tbody></table>";
		echo $tab;
	break;
	case'inputdetail':
		echo"<tr class=rowcontent>";
		echo"<td id=no align=center>1</td>";

		/* 
			echo"<td>";
		?>
			<form >
			<input type="text" list="datakar" name="datakar"> 
			<datalist id="datakar"><?php echo $optKary; ?></datalist>
			</form>
		<?php
			echo"</td>";
		*/	
		$optKary = getoptkary($param);
		$optBlok = getoptblok($param);
		echo"<td colspan=2><select style=width:205px;align:right; onchange=getDataDetail() id=karyawanid class='select2'>".$optKary."</select></td>
			<!--<td width=20px>
			<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>-->

			<td colspan=2><select style=width:150px onchange=getDataDetail('','changekeg') id=blok class='select2'>".$optBlok."</select></td>
			<!--
			<td width=20px>
			<img id='blok' onclick=z.elSearch('blok',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>-->
			
			<td colspan=2><select style=width:180px onchange=getDataDetail() id=kegiatan class='select2'>".@$optKeg."</select></td>
			<!--<td width=20px>
			<img id='kegiatan' onclick=z.elSearch('kegiatan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>-->
			
			<td><input id=luas disabled class=myinputtextnumber style=\"width:35px;\"></td>
			<td><input id=satuan class=myinputtext disabled style=\"width:40px;align:center\"></td>

			<td><input id=prestasi onkeyup=\"getDataDetail();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber  onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
			
			<td><input id=jhk title=\"Double click untuk mengisi HK menjadi 1\" onkeyup=\"getumr('','i');\" ondblclick=\"getumr('','d');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:35px;\"></td>
			
			<td><input id=upah onkeyup=gethk(this.id,'jhk','karyawanid',''); nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:60px;\"></td>

			<td><input id=basis disabled onkeyup=\"z.numberFormat('basis',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
			<td><input id=rpsat disabled onkeyup=\"z.numberFormat('rpsat',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
			
			<td><input id=premi disabled onkeyup=\"z.numberFormat('premi',2)\"  class=myinputtextnumber style=\"width:60px;\"></td>
			
			
			<td align=center width=20px><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
			</td>
			<td  align=center width=20px>	
				<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail('x')\" src='images/clear.png'/>
			</td>
        </tr><tr>
			<td id=pfot colspan=15></td>
			<td  align=center  width=20px>
			<input id=jlhbrs style=display:none>
			<img title='Refresh List Data' class=zImgBtn onclick=\"cancelcari()\" src='images/refresh2.png'/>
			</td>
			<td  align=center width=20px>
			<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
			</td>
        </tr>";
	break;
	case'getdata':	
		$optKary = getoptkary($param);
		$optBlok = getoptblok($param);
		echo $optKary."######".$optBlok;
	break;
	case'getdatamandor':
	$optBlok = getoptblok($param);
	$whereKary="";
	$whereKary= " and tipekaryawan in (2,3,4,5,6) and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
	$str = "select a.karyawanid,b.namakaryawan,b.nik, b.subbagian from ".$dbname.".kebun_5mandor a
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where statusaktif='1' and mandorid='".$mandor."' ".$whereKary." order by a.nourut asc"; #exit("error".$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$tab='';
	$no='';
	while($bar=$res->fetch()){
		$no++;
		if($bar['nik']!=''){
			$bar['nik']=$bar['nik']." - ";
		}
		
		// <td><input id=premi".$no." disabled onkeyup=\"z.numberFormat('premi".$no."',2)\"  class=myinputtextnumber style=\"width:60px;\"></td>
		$tab.="<tr class=rowcontent id=row".$no.">";
		$tab.="	<td align=center>".$no."</td>
		<td style=display:none><input id=karyawanid".$no." value=".$bar['karyawanid']."></td>
		<td colspan=2 id=kary".$no.">".$bar['nik'].$bar['namakaryawan']."</td>
		
		<td style=width:95px><select style=width:95px onchange=\"getDataDetailAllAll(".$no.",'changekeg');copyblok(".$no.")\" id=blok".$no.">".$optBlok."</select></td><td width=20px>
		<img id='blok".$no."' onclick=z.elSearch('blok".$no."',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
		
		<td><select style=width:150px onchange=\"getDataDetailAllAll(".$no.");copykegiatan(".$no.")\" id=kegiatan".$no.">".@$optKeg."</select></td><td width=20px>
		<img id='kegiatan".$no."' onclick=z.elSearch('kegiatan".$no."',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
		
		<td><input id=luas".$no." disabled class=myinputtextnumber style=\"width:35px;\"></td>
		<td><input id=satuan".$no." class=myinputtext disabled style=\"width:40px;align:center\"></td>

		<td><input id=prestasi".$no."  onblur=\"getDataDetailAllAll(".$no.");copypres(".$no.")\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
		
		<td><input id=jhk".$no." title=\"Double click untuk mengisi HK menjadi 1\" onkeyup=\"getumr(".$no.",'i');\" ondblclick=\"getumr(".$no.",'d');\"   nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:35px;\"></td>
		
		<td><input id=upah".$no." onkeyup=gethk(this.id,'jhk".$no."','karyawanid".$no."','".$no."'); nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:60px;\"></td>

		<td><input id=basis".$no." disabled onkeyup=\"z.numberFormat('basis".$no."',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
		<td><input id=rpsat".$no." disabled onkeyup=\"z.numberFormat('rpsat".$no."',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
			
		<td><input id=premi".$no." disabled onkeyup=\"numberFormat2('premi".$no."',0)\"  class=myinputtextnumber style=\"width:60px;\"></td>

		<td align=center width=20px><input type=hidden id=method value='insert'>
			<img title='" . $_SESSION['lang']['save']."' class=zImgBtn onclick=\"savedetail(".$no.")\" src='images/save.png'/>
		</td>
			<td  align=center width=20px>
			<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail(".$no.")\" src='images/clear.png'/>
		</td>
	</tr>";
	}
	$tab.="<tr>
		<td id=pfot colspan=14>
		<td align=right center width=20px>
		<input id=jlhbrs  style=display:none value=".$no.">
		<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetail()\" src='images/refresh2.png'/>&nbsp;
		</td>
			<td  align=center width=20px>
		<img title='" . $_SESSION['lang']['saveall']."' class=zImgBtn onclick=\"saveAll(".$no.")\" src='images/save.png'/>
		</td>
			<td  align=center width=20px>
		<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
		</td>
	</tr>";
	echo $tab."######".$no;
	break;
	
	case'loaddatadetail':
	
	$rows="rowspan=2";
	$border="border=0";
	if($tipe=='excel'){$border="border=1";}
	
	$tab="<table id=tabledt cellpadding=3 cellspacing=1 ".$border." class=sortable style=min-width:960px>
			<thead><tr class=rowheader>
			<th align=center ".$rows." width=25px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['notransaksi'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center ".$rows.">".$_SESSION['lang']['blok'] . "</th>
				<th align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</th>
				<th align=center ".$rows." width=30px>".$_SESSION['lang']['luas'] . "</th>
				<th align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</th>
				<th align=center colspan=2 width=50px>".$_SESSION['lang']['jumlah']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['premi']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['total']."</th>
				<th align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr>
				<th align=center width=45px>".$_SESSION['lang']['satuan'] . "</th>
				<th align=center width=45px>".$_SESSION['lang']['jumlah'] . "</th>
				<th align=center width=35px>HK</th>
				<th align=center>".$_SESSION['lang']['upah']."</th>
			</tr>
		</thead>";
		
        $no = 0;
		$where = "";
		$wherexz = "";
		if($notrandetsch!=''){
			$where.=" and a.notransaksi like '%".$notrandetsch."%'";
		}
		if($namakary!=''){
			$where.=" and (a.nikpemel like '%".$namakary."%' or a.nikpemel in (select karyawanid from ".$dbname.".datakaryawan where karyawanid like '%".$namakary."%' or nik like '%".$namakary."%' or namakaryawan like '%".$namakary."%'))";
			$wherexz.=" and (karyawanid like '%".$namakary."%' or nik like '%".$namakary."%' or namakaryawan like '%".$namakary."%')";
		}
		if($blok!=''){
			$where.=" and a.kodeorg like '%".$blok."%'";
		}
		if($kegiatan!=''){
			$where.=" and (a.kodekegiatan like '%".$kegiatan."%' or a.kodekegiatan in (select kodekegiatan from ".$dbname.".setup_kegiatan where namakegiatan like '%".$kegiatan."%'))";
		}

		$jlhcolspan=13;

		$str = "select * from ".$dbname.".setup_kegiatan"; 
		$res=fetchdata($str);
		foreach($res as $bar){
			$nmsat[$bar['kodekegiatan']]=$bar['satuan'];
			$nmkeg[$bar['kodekegiatan']]=$bar['namakegiatan'];
		}
		
		// $str = "select * from ".$dbname.".organisasi where kodeorganisasi like '".$param['kodeorg']."%'"; 
		$str = "select * from ".$dbname.".organisasi where indukblok like '".$param['kodeorg']."%'"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			// $nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
			$nmorg[$bar['indukblok']]=$bar['namaindukblok'];
		}
		
		$str = "select * from ".$dbname.".project where kodeorg like '".$param['kodeorg']."%'"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmorg[$bar['kode']]=$bar['nama'];
			$luasblok[$bar['kode']]=$bar['jumlah'];
		}

		// $str = "select a.kodekegiatan, a.pilihanluas, b.kodeorg, c.statusblok, c.luasareaproduktif, c.luasareanonproduktif, c.luasbloking, c.lc
		// from ".$dbname.".setup_kegiatan a
		// JOIN ".$dbname.".kebun_prestasi b on a.kodekegiatan = b.kodekegiatan
		// JOIN ".$dbname.".setup_blok c on b.kodeorg = c.indukblok and IF (a.kelompok = 'PNN', 'TM', a.kelompok) = c.statusblok
		// where b.nobkm='".$param['nobkm']."'";

		$str = "select a.kodekegiatan, a.pilihanluas, b.kodeorg, a.kelompok
		from ".$dbname.".setup_kegiatan a 
		JOIN ".$dbname.".kebun_prestasi b on a.kodekegiatan = b.kodekegiatan 
		where b.nobkm='".$param['nobkm']."'
		group by b.kodeorg, b.kodekegiatan";
		$reskeg=fetchdata($str);
		foreach($reskeg as $barkeg){
			$pilihanluas[$barkeg['kodekegiatan']] = $barkeg['pilihanluas'];
			$bloklist[] = $barkeg['kodeorg'];
			$stbloklist[] = $barkeg['kelompok'];

			// $str = "select * from ".$dbname.".setup_blok where kodeorg like '".$param['kodeorg']."%'"; 
			$str = "select indukblok, statusblok, SUM(luasareaproduktif) AS luasareaproduktif, 
			SUM(luasareanonproduktif) AS luasareanonproduktif, SUM(luasbloking) AS luasbloking, SUM(lc) AS lc
			from ".$dbname.".setup_blok WHERE indukblok in ('".implode("','",$bloklist)."') and statusblok IN ('".implode("','",$stbloklist)."') AND status='A'
			group by indukblok,statusblok"; 
			$res = fetchdata($str);
			foreach($res as $valkeg){
				if ($pilihanluas[$barkeg['kodekegiatan']] == 0) {
					// exit("Warning: 0");
					$luasblok2[$valkeg['indukblok']][$valkeg['statusblok']]=($valkeg['luasareaproduktif'] + $valkeg['luasareanonproduktif']);
					$luasblok3[$valkeg['indukblok']][$valkeg['statusblok']]=($valkeg['luasareaproduktif'] + $valkeg['luasareanonproduktif']);
				} elseif ($pilihanluas[$barkeg['kodekegiatan']] == 1) {
					// exit("Warning: 1");
					$luasblok2[$valkeg['indukblok']][$valkeg['statusblok']]+=($valkeg['luasbloking']);
					$luasblok3[$valkeg['indukblok']][$valkeg['statusblok']]+=($valkeg['luasbloking']);
				} elseif ($pilihanluas[$barkeg['kodekegiatan']] == 2) {
					// exit("Warning: 2");
					$luasblok2[$valkeg['indukblok']][$valkeg['statusblok']]+=($valkeg['lc']);
					$luasblok3[$valkeg['indukblok']][$valkeg['statusblok']]+=($valkeg['lc']);
				} else {
					// exit("Warning: 3");
					$luasblok2[$valkeg['indukblok']][$valkeg['statusblok']]+=($valkeg['luasareaproduktif']);
					$luasblok3[$valkeg['indukblok']][$valkeg['statusblok']]+=($valkeg['luasareaproduktif']);
				}
			}
		}

		$stsblok2 = array();
		$strx = "SELECT b.statusblok FROM $dbname.kebun_prestasi a LEFT JOIN $dbname.setup_blok b
		ON a.kodeorg = b.indukblok WHERE a.nobkm = '".$nobkm."' AND b.status='A' group by b.statusblok";
		$resx = fetchData($strx);
		foreach ($resx as $valx) {
			$stsblok2[].= $valx['statusblok'];
		}

		$str = "select a.*,d.kelompok,c.jhk, c.umr, c.insentif from " . $dbname . ".kebun_prestasi a 
		left join " . $dbname . ".kebun_kehadiran c on a.nikpemel=c.nik and a.notransaksi=c.notransaksi and a.nourut=c.nourut
		left join " . $dbname . ".setup_kegiatan d on a.kodekegiatan=d.kodekegiatan 
		where a.nobkm='" . $nobkm . "' ".$where." order by a.notransaksi asc, a.kodekegiatan asc";
		// exit('error '.$str);		
        $res=fetchdata($str);
        $row=count($res);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=".$jlhcolspan." style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach ($res as $bar) {
				if(getHistKary($bar['nikpemel'],substr($bar['notransaksi'],0,4)."-".substr($bar['notransaksi'],4,2),'namakaryawan')!=''){
					$bar['namakaryawan']= getHistKary($bar['nikpemel'],substr($bar['notransaksi'],0,4)."-".substr($bar['notransaksi'],4,2),'namakaryawan');
					$bar['subbagian']   = getHistKary($bar['nikpemel'],substr($bar['notransaksi'],0,4)."-".substr($bar['notransaksi'],4,2),'subbagian');
					$bar['nik2']        = getHistKary($bar['nikpemel'],substr($bar['notransaksi'],0,4)."-".substr($bar['notransaksi'],4,2),'nik');
				}else{
					$bar['namakaryawan']= getKary($bar['nikpemel'],'namakaryawan');
					$bar['subbagian']   = getKary($bar['nikpemel'],'subbagian');
					$bar['nik2']        = getKary($bar['nikpemel'],'nik');
				}
				
				#cek apakah sudah di posting	
				$optpost=makeOption($dbname,'kebun_aktifitas','notransaksi,jurnal',"notransaksi='".$bar['notransaksi']."'");
				
				$bgcolor=$title=$asstensi=$infomat='';
				$bgc="style=height:25px;";
				$strx = "select count(nikpemel) as jmlkary, nikpemel from " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."' and nikpemel='".$bar['nikpemel']."' group by nikpemel";
				$barx = fetchdata($strx)[0];
				if(($bar['nikpemel']==$barx['nikpemel']) and ($barx['jmlkary']>1)){
					$bgcolor="style=color:#06BA10;cursor:pointer;";
					$bgcolor.=" title = 'Karyawan Bekerja lebih dari 1 blok atau lebih dari 1 kegiatan dalam sehari.'";
				}
				if(substr($bar['kodeorg'],0,6)!=$bar['subbagian']){
					$asstensi="style=color:blue;cursor:pointer;";
					$asstensi.=" title =\"Karyawan melakukan asistensi / lokasi tugas karyawan berbeda dengan lokasi kerjanya.\nLokasi Tugas Karyawan : ".$bar['subbagian']."\nLokasi Bekerja Karyawan : ".substr($bar['kodeorg'],0,6)."\"";
				}
				
				if($bar['nik2']!=''){
					$bar['nik2']=$bar['nik2']." - ";
				}
				if($bar['subbagian']!=''){
					$bar['subbagian']=$bar['subbagian']." - ";
				}
				if($optpost[$bar['notransaksi']]==1){
					$bgc="style=background-color:#33FF35;height:25px;";
				}
				
				$cekmat="select * from " . $dbname . ".kebun_pakaimaterial where notransaksi='".$bar['notransaksi']."' and kodeorg='".$bar['kodeorg']."' and kodekegiatan='".$bar['kodekegiatan']."'";
				$resmat = fetchdata($cekmat);
				
				$cekset="select * from " . $dbname . ".setup_kegiatannorma where kodekegiatan='".$bar['kodekegiatan']."'";				
				$resset = fetchdata($cekset);
				
				if(count($resset)>0 and count($resmat)==0){
					$infomat="style=color:red;cursor:pointer; onclick=pindahtab('tabFRM1','1');";
					$infomat.=" title = 'Pekerjaan ini harus menggunakan material, silahkan input pada tab Material.'";
				}
				
				$kodeproject = substr($bar['kodeorg'],0,2);
				$luasclr="";
				if ($kodeproject == "AK") {
					if(strtolower($nmsat[$bar['kodekegiatan']])=='ha' and $bar['hasilkerja']>$luasblok[$bar['kodeorg']]){
						$luasclr="style=background-color:red; title='Luas hasil kerja melebihi luasan total blok.'";
					}
				} else {
					if(strtolower($nmsat[$bar['kodekegiatan']])=='ha' and $bar['hasilkerja']>$luasblok2[$bar['kodeorg']][$bar['kelompok']]){
						$luasclr="style=background-color:red; title='Luas hasil kerja melebihi luasan total blok.'";
					}
				}
				
				
				$no+=1;
				$align=" align=right ";
				$nn=" style=display:none ";
				$tab.="<tr class=rowcontent ".$bgc.">";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['notransaksi']."','notran')>" . $bar['notransaksi']."</td>";
				$tab.="<td align=left ".$asstensi." style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['nikpemel']."','namakary')>" . $bar['nik2'].$bar['subbagian'].$bar['namakaryawan'] . "</td>";
				$tab.="<td align=center ".$bgcolor." style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['kodeorg']."','blok')>" . $nmorg[$bar['kodeorg']]. "</td>";
				$tab.="<td align=left ".$infomat." style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['kodekegiatan']."','kegiatan')>".$bar['kodekegiatan']." - " . $nmkeg[$bar['kodekegiatan']]. "</td>";
				if ($kodeproject == "AK") {
					$tab.="<td align=right>" . @numb_format($luasblok[$bar['kodeorg']],2) . "</td>";
				} else {
					if ($bar['kelompok'] == "PNN") {
						$tab.="<td align=right>" . @numb_format($luasblok2[$bar['kodeorg']]["TM"],2) . "</td>";
					} else {
						$tab.="<td align=right>" . @numb_format($luasblok2[$bar['kodeorg']][$bar['kelompok']],2) . "</td>";
					}
				}
				
				$tab.="<td align=center>" . $nmsat[$bar['kodekegiatan']] . "</td>";
				$tab.="<td align=right ".$luasclr.">" . @numb_format($bar['hasilkerja'],2) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['jhk'],2) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['umr'],2) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['insentif'],2) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['umr']+$bar['insentif'],2) . "</td>";
				
				@$tjhk+=$bar['jhk'];
				@$tumr+=$bar['umr'];
				@$tinsentif+=$bar['insentif'];
				if($optpost[$bar['notransaksi']]==0){
					$tab.="<td align=center width=20px>";
					if($tipe!='excel'){
						if ($kodeproject == "AK") {
							$tab.="<img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
							onclick=\"editdetail('".$bar['notransaksi']."','".$bar['nikpemel']."','".$bar['kodekegiatan']."','".$bar['kodeorg']."','".$luasblok[$bar['kodeorg']]."','".$nmsat[$bar['kodekegiatan']]."','".$bar['hasilkerja']."','".$bar['jhk']."','".$bar['umr']."','".$bar['insentif']."','".$no."','".getNamaKaryawan($bar['nikpemel'])."','".$nmorg[$bar['kodeorg']]. "');\" >";	
						} else {
							$tab.="<img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
							onclick=\"editdetail('".$bar['notransaksi']."','".$bar['nikpemel']."','".$bar['kodekegiatan']."','".$bar['kodeorg']."','".$luasblok2[$bar['kodeorg']][$bar['kelompok']]."','".$nmsat[$bar['kodekegiatan']]."','".$bar['hasilkerja']."','".$bar['jhk']."','".$bar['umr']."','".$bar['insentif']."','".$no."','".getNamaKaryawan($bar['nikpemel'])."','".$nmorg[$bar['kodeorg']]. "');\" >";
						}
						
						$tab.="</td>";	
						$tab.="<td align=center width=20px>";	
						$tab.="<img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
						onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['nikpemel'] . "','" . $bar['kodeorg'] . "','".$bar['kodekegiatan']."');\" >";
					}
					$tab.="</td>";
				}else{
					$tab.="<td align=center colspan=2>Posted</td>";
				}
				#untuk subtotal dibawah
				$data[$bar['kodeorg']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
				$getstblok[$bar['kodeorg']][$bar['kodekegiatan']]=$bar['kelompok'];
				@$preskerja[$bar['kodeorg']][$bar['kodekegiatan']]+=$bar['hasilkerja'];
				@$subjhl[$bar['kodeorg']][$bar['kodekegiatan']]+=$bar['jhk'];
				@$subumr[$bar['kodeorg']][$bar['kodekegiatan']]+=$bar['umr'];
				@$supahpre[$bar['kodeorg']][$bar['kodekegiatan']]+=$bar['insentif'];
			}
			
			$rowsp=0;
			foreach($data as $kdblok => $valkeg){
				foreach($valkeg as $kdkeg => $kegiatan){
					$rowsp+=1;
				}
			}
			// $tab.="<tr class=rowcontent>";
			// $tab.="<td colspan=".$jlhcolspan." bgcolor=#2C3E50></td>";
			// $tab.="</tr>";
			$nosub=0;
			foreach($data as $kdblok => $valkeg){
				foreach($valkeg as $kdkeg => $kegiatan){
					$gtblokx = $getstblok[$kdblok][$kdkeg];

					$nosub+=1;
					$tab.="<tr class=rowcontent style=background-color:#AED6F1;height:25px;>";
					if($nosub==1){
						$tab.="<td colspan=3 rowspan=".$rowsp." align=center><b>REKAPITULASI</b></td>";						
					}
					$luasclr="";
					if ($kodeproject == "AK") {
						if(strtolower($nmsat[$kdkeg])=='ha' and number_format($preskerja[$kdblok][$kdkeg],2)>number_format($luasblok[$kdblok],2)){
							$luasclr="style=background-color:red; title='Luas hasil kerja melebihi luasan total blok.'";
							// exit("warning".strtolower($nmsat[$kdkeg]).'xx'.number_format($preskerja[$kdblok][$kdkeg],2).' | '.number_format($luasblok[$kdblok],2));
						}
					} else {
						if(strtolower($nmsat[$kdkeg])=='ha' and number_format($preskerja[$kdblok][$kdkeg],2)>number_format($luasblok3[$kdblok][$gtblokx],2)){
							$luasclr="style=background-color:red; title='Luas hasil kerja melebihi luasan total blok.'";
						}
					}
					
					$tab.="<td align=center style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$kdblok."','blok')>".$nmorg[$kdblok]."</td>";
					$tab.="<td align=left  style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$kdkeg."','kegiatan')>".$kdkeg." - " . $nmkeg[$kdkeg]. "</td>";
					if ($kodeproject == "AK") {
						$tab.="<td align=right>" . @numb_format($luasblok[$kdblok],2) . "</td>";
					} else {
						if ($bar['kelompok'] == "PNN") {
							$gtblokx = "TM";
							$tab.="<td align=right>" . @numb_format($luasblok3[$kdblok][$gtblokx],2) . "</td>";
						} else {
							$tab.="<td align=right>" . @numb_format($luasblok3[$kdblok][$gtblokx],2) . "</td>";
						}
					}
					
					$tab.="<td align=center>" . $nmsat[$kdkeg] . "</td>";
					$tab.="<td align=right ".$luasclr.">" . @numb_format($preskerja[$kdblok][$kdkeg],2) . "</td>";
					$tab.="<td align=right>" . @numb_format($subjhl[$kdblok][$kdkeg],2) . "</td>";
					$tab.="<td align=right>" . @numb_format($subumr[$kdblok][$kdkeg]) . "</td>";
					$tab.="<td align=right>" . @numb_format($supahpre[$kdblok][$kdkeg]) . "</td>";
					$tab.="<td align=right>" . @numb_format($supahpre[$kdblok][$kdkeg]+$subumr[$kdblok][$kdkeg]) . "</td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=right></td>";
					$tab.="</tr>";					
				}
			}
			// $tab.="<tr class=rowcontent>";
			// $tab.="<td colspan=".$jlhcolspan." bgcolor=#2C3E50></td>";
			// $tab.="</tr>";
			
			
			$tab.="<tr class=rowcontent style=background-color:#A3E4D7;height:25px>";
			$tab.="<td colspan=3 align=center><input value=".$no." style=display:none id=jlhbrsdt><b>GRAND TOTAL</b></td>
					<td colspan=5 align=center></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@numb_format($tjhk,2)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@numb_format($tumr)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@numb_format($tinsentif)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@numb_format($tumr+$tinsentif)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right></td>";
			$tab.="</tr>";
		}
        $tab.="</tr>";
        $tab.="</table>";

		if($tipe=='excel'){	
			$nop = "detail_bkm.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("detail_bkm", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;
		}
	break;
	
	case'getDataDetail':
	#========================== Setup Kegiatan ============================
		$tahun=substr(tanggalsystemn($tgl),0,4);
		
		$str = "select * from ".$dbname.".setup_kegiatan where kodekegiatan='".$kegiatan."'"; 
		$res=fetchdata($str);
		foreach($res as $bar){
			$stspremi=$bar['premi'];
			$satkegiatan=$bar['satuan'];
			$kelompok = $bar['kelompok'];
			$pilihanluas = $bar['pilihanluas'];
		}
		
	#========================== Setup Kegiatan ============================
	#========================== Premi Kegiatan ============================
		$str = "select * from ".$dbname.".kebun_5premibkm where kodekegiatan='".$kegiatan."' and unit='".$kodeorg."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$basis=$bar['basis'];
			$rppremilebihbasis=$bar['premilebihbasis'];
			
			$premibasis=$premilebihbasis=0;
			if($prestasi>=$basis){
				$premibasis=$bar['premibasis'];
			}
			if((floatval($prestasi)-floatval($basis))>0){
				$premilebihbasis=($prestasi-$basis)*$rppremilebihbasis;
			}
		}
		
	#========================== Premi Kegiatan ============================
	#============================== Tipe Kary =============================
		$str = "select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$tipeKary=$bar['tipekaryawan'];
		}
	#============================== Tipe Kary =============================
	#=============================== Get Ha ===============================
		$whrk = "";
		if ($kegiatan != "") {
			if ($kelompok == 'PNN') {
				$whrk .= " and statusblok='TM'";
				$whrk .= " AND status='A'";
			} else {
				$whrk .= " and statusblok in (select kelompok from ".$dbname.".setup_kegiatan where kodekegiatan='".$kegiatan."')";
				$whrk .= " AND status='A'";
			}
		}

		$stsblok = array();
		// $str = "select * from ".$dbname.".setup_blok where kodeorg='".$blok."'"; 
		if ($pilihanluas == 0) {
			$str = "select sum(luasareaproduktif + luasareanonproduktif) as luasareaproduktif,statusblok from ".$dbname.".setup_blok where indukblok='".$blok."' ".$whrk." group by indukblok,statusblok"; 
		} elseif ($pilihanluas == 1) {
			$str = "select sum(luasbloking) as luasareaproduktif,statusblok from ".$dbname.".setup_blok where indukblok='".$blok."' ".$whrk." group by indukblok,statusblok"; 
		} elseif ($pilihanluas == 2) {
			$str = "select sum(lc) as luasareaproduktif,statusblok from ".$dbname.".setup_blok where indukblok='".$blok."' ".$whrk." group by indukblok,statusblok"; 
		} else {
			$str = "select sum(luasareaproduktif) as luasareaproduktif,statusblok from ".$dbname.".setup_blok where indukblok='".$blok."' ".$whrk." group by indukblok,statusblok"; 
		}
		// exit("warning ".$str);
		$res=fetchdata($str);
		foreach($res as $bar){
			if ($kegiatan != "") {
				$luasblok=$bar['luasareaproduktif'];
			} else {
				$luasblok="";
			}

			$stsblok[].=$bar['statusblok'];
		}
	#=============================== Get Ha ===============================
	#====================== Ambil Daftar Kegiatan =========================
		$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$n = "";
		$z = 0;
		if($param['filterdivisi']=='PROJECT'){
			if(substr($blok,0,2)=='AK'){
				$tipeasset= substr($blok,3,2);
				$str = "select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where noakun in (select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".$tipeasset."') order by noakun,kelompok,namakegiatan";
				$res = fetchdata($str);
				foreach($res as $bar){
					$d=substr($bar['kodekegiatan'],0,5);
					if($d!=$n){
						$z++;
						if ($z > 1) {
							$optKeg.="</optgroup>";
						}
						$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
						$optKeg.="<optgroup label='".$nmorg[$d]."'>";
					}
					$optKeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
					$n=$d;
				}
			}
		}else{
			if ($stsblok[0] != "BBT") {
				$str = "select a.* from ".$dbname.".setup_kegiatan a left join ".$dbname.".keu_5akun b on a.noakun=b.noakun where 1=1 and a.kelompok IN ('".implode("','",$stsblok)."','PNN') and a.status='1' and b.namaakun not like '%NON AKTI%' and a.namakegiatan not like '%NON AKTI%' order by a.kodekegiatan asc, a.namakegiatan asc";
			} else {
				$str = "select a.* from ".$dbname.".setup_kegiatan a left join ".$dbname.".keu_5akun b on a.noakun=b.noakun where 1=1 and a.kelompok IN ('".implode("','",$stsblok)."') and a.status='1' and b.namaakun not like '%NON AKTI%' and a.namakegiatan not like '%NON AKTI%' order by a.kodekegiatan asc, a.namakegiatan asc";
			}
			// echo $str;
			$res = fetchdata($str);
			$n = "";
			$z = 0;
			foreach($res as $bar){
				$d=substr($bar['kodekegiatan'],0,7);
				if($d!=$n){
					$z++;
					if ($z > 1) {
						$optKeg.="</optgroup>";
					}
					$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
					$optKeg.="<optgroup label='".$nmorg[$d]."'>";
				}
				$optKeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
				$n=$d;
			}
		}
		#====================== Ambil Daftar Kegiatan =========================
		
		#=============================== Get HK Header ===============================
		$jumtrans2 = 0;
		#cek mandor
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor='".$karyawanid."' and tanggal='".tanggaldb($tgl)."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$jumtrans2+=$bar['jumkar'];
			
		#cek mandor1
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor1='".$karyawanid."' and tanggal='".tanggaldb($tgl)."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$jumtrans2+=$bar['jumkar'];
			
		#cek kerani
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where keranimuat='".$karyawanid."' and tanggal='".tanggaldb($tgl)."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$jumtrans2+=$bar['jumkar'];
			
		#cek nikasisten
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikasisten='".$karyawanid."' and tanggal='".tanggaldb($tgl)."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$jumtrans2+=$bar['jumkar'];

		if ($jumtrans2 > 0) {
			$hkhead = 1;	
		} else {
			$hkhead = 0;
		}

		#=============================== Get HK Header ===============================
		
		// echo"<pre>";
		// print_r($param);
		// exit("error");

	echo $stspremi."######".$basis."######".$premibasis."######".$premilebihbasis."######".$tipeKary."######".$luasblok."######".$satkegiatan."######".$rppremilebihbasis."######".$optKeg."######".$hkhead;
	break;
	
	case'getumr':
	#=============================== Get UMR ==============================
		$tahun=substr(tanggalsystemn($tgl),0,7);
		$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$karyawanid."' and tahun='".$tahun."' and idkomponen in ('1')";#exit("Warning :".$str);
		// exit("Warning: ".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$Umr=$res->fetch();
			$umrHarian=$Umr['nilai']/25;
			// $umrHarianbasis=$Umr['nilai']/25;
		
		if($umrHarian==0){
			exit("Warning : Gaji Pokok Karyawan belum ada.");
		}
		// $umrHarian = $umrHarian * $param['jhk'];
		
		// exit("error ".$umrHarian);

		echo $umrHarian."####".getKary($karyawanid,'tipekaryawan');
	break;
	
	case'getnilaihk':
		$str = "select * from ".$dbname.".sdm_5absensi where kodeabsen='".$kodeabsen."'";
		$res=fetchData($str);
		
		echo @$res[0]['nilaihk'];
	break;
	
	case'delmaterial':
		$str = "delete from " . $dbname . ".kebun_pakaimaterial where notransaksi='".$notransaksi."' and kodeorg='".$param['blok']."' and kodebarang='".$param['kodebarang']."' and kodekegiatan='".$param['kegiatan']."'";
		try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
	break;
	case'insertmaterial':
	try {
	$owlPDO->beginTransaction();
	
		if($param['qtymat']==''){
			$param['qtymat']='0';
		}
		$ttlkeluar = $logblmpost = $bkmblmpost = $saldogudang = 0;
		
		$param['qtymat'] =  round($param['qtymat'],2);
		
		
		$arrprd = explode("-",tanggalsystemn($param['tgl']));
		$periode = $arrprd[0]."-".$arrprd[1];
		
		# Ambil saldo gudang
		$str="select saldoakhirqty as saldoqty from ".$dbname.".log_5saldobulanan where kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."' and periode='".$periode."'";
		$res=fetchData($str);
		$saldogudang = $res[0]['saldoqty'];
		
		#ambil transaksi belum posting di BKM
		$str="select sum(kwantitas) as kwantitas from ".$dbname.".kebun_pakaimaterial a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.kodebarang='".$param['kodebarang']."' and a.kodegudang='".$param['kodegudang']."' and b.jurnal='0'";
		$res=fetchData($str);
		$bkmblmpost = $res[0]['kwantitas'];
		
		#ambil transaksi belum posting di gudang (siapa tau ada, ambil yang keluar saja yang masuk biarkan saja)
		$str="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."' and post='0'  and tipetransaksi>4";
		$res=fetchData($str);
		$logblmpost = $res[0]['jumlah'];
		
		$ttlkeluar = floatval($bkmblmpost)+floatval($logblmpost)+floatval($param['qtymat']);
		
		if($saldogudang<0){
			throw new PDOException("Saldo barang salah mohon hubungi administrator.");
		}
		
	
		if(floatval(number_format($ttlkeluar),5) > floatval(number_format($saldogudang),5)){
			throw new PDOException("Saldo barang tidak cukup, sisa saldo : ".$saldogudang."\nPemakaian lalu belum posting : ".($bkmblmpost+$logblmpost)."\nTransaksi saat ini : ".$param['qtymat']."\nTotal Keluar : ".$ttlkeluar."\nSelisih : ".number_format($saldogudang-$ttlkeluar,5));
		}

		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['notransaksi']!='' and $param['kegiatan']!='' and $param['blok']!='' and $param['kodebarang']!='' and $param['qtymat']!='0' and $param['kodegudang']!=''){
			
			#cek apakah sudah di posting	
			$optpost=makeOption($dbname,'kebun_aktifitas','notransaksi,jurnal',"notransaksi='".$notransaksi."'");
			if($optpost[$notransaksi]==1){
				$ststransblok = explode("/",$notransaksi);
				throw new PDOException("Transaksi dengan nomor ".$notransaksi." (status blok : ".$ststransblok[2].") sudah diposting, silahkan buat Transaksi dengan No BKM yang baru untuk menambahkan.");
			}
			
			# Hapus dulu data yang lama
			$str = "delete from " . $dbname . ".kebun_pakaimaterial where notransaksi='".$notransaksi."' and kodeorg='".$param['blok']."' and kodebarang='".$param['kodebarang']."' and kodekegiatan='".$param['kegiatan']."'";
			$owlPDO->exec($str);
			
			# ambil harga rata2 barang
			$str = "select hargarata from ".$dbname.".log_5saldobulanan where kodegudang='".$param['kodegudang']."' and kodebarang='".$param['kodebarang']."'  and periode='".$periode."' order by periode desc limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$harga=$res->fetch();
			$hargaratabarang=$harga['hargarata'];
			
			$cols = array(
						'notransaksi','kodekegiatan','kodeorg','kodebarang','kwantitas','kodegudang','kwantitasha','hargasatuan',
					);
			$data = array($param['notransaksi'],$param['kegiatan'],$param['blok'],$param['kodebarang'],$param['qtymat'],$param['kodegudang'],$param['prestasi'],$hargaratabarang
			);
			
			# Insert
			$query = insertQuery($dbname,'kebun_pakaimaterial',$data,$cols);
			$owlPDO->exec($query);
		}
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
		
    case'insert':
	try {
	$owlPDO->beginTransaction();
		$param['tgl'] = tanggalsystem($param['tgl']);
	
		if($param['prestasi']==''){
			$param['prestasi']='0';
		}
		if($param['jhk']==''){
			$param['jhk']='0';
		}
		if($param['upah']==''){
			$param['upah']='0';
		}
		if($param['premi']==''){
			$param['premi']='0';
		}
		
		if($param['upah']=='0' and $param['jhk']!='0'){
			throw new PDOException("Nilai HK dan Upah tidak sesuai.");
		}
		if($param['upah']!='0' and $param['jhk']=='0'){
			throw new PDOException("Nilai HK dan Upah tidak sesuai.");
		}
			
		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['notransaksi']!='' and $param['karyawanid']!='' and $param['kegiatan']!='' and $param['blok']!='' and $param['prestasi']!='0' and (($param['jhk']!='0' and $param['upah']!='0') or $param['premi']!='0')){
			$optstskel = makeOption($dbname,"setup_kegiatan","kodekegiatan,kelompok","kodekegiatan='".$param['kegiatan']."'");
			# Cari status blok
			if ($optstskel[$param['kegiatan']] == "PNN") {
				$param['tipetransaksi'] = "TM";
			} else {
				$optstsblok = makeOption($dbname,'setup_blok','indukblok,statusblok',"indukblok='".$param['blok']."' and statusblok IN (select kelompok from ".$dbname.".setup_kegiatan where kodekegiatan='".$param['kegiatan']."') AND status='A'");
				#===== buat nomor transaksi =====
				# Data Capture & Reform
				if($param['filterdivisi']=='PROJECT'){
					$param['tipetransaksi'] = "PRJ";
				}else{				
					$param['tipetransaksi'] = $optstsblok[$param['blok']];
				}
			}
			
			$upahharian = 0;
			$tahun = substr($param['tgl'],0,4);
			$bulan = substr($param['tgl'],4,2);
			$prdgj = $tahun."-".$bulan;
			$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$param['karyawanid']."' and tahun='".$prdgj."' and idkomponen in ('1')"; #exit("Warning :".$str);
			$res = fetchdata($str);
			$upahharian = $res[0]['nilai']/25;
			$upahkary = $upahharian * $param['jhk'];
			if(round($upahkary-$param['upah'],0)!=0){
				throw new PDOException("Nilai HK dan Upah tidak sesuai.");
			}
			
			#=== Generate No Transaksi
			# Get Existing Data
			$fWhere = "tanggal='".$param['tgl']."' and kodeorg='".$param['kodeorg'].
				"' and tipetransaksi='".$param['tipetransaksi']."'";
			$fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
			$tmpNo = fetchData($fQuery);
			
			# Generate No Transaksi
			if(count($tmpNo)==0) {
				$notransaksi = $param['tgl']."/".$param['kodeorg']."/".$param['tipetransaksi']."/001";
			} else {
				# Get Max No Urut
				$maxNo = 1;
				foreach($tmpNo as $row) {
				$tmpRow = explode('/',$row['notransaksi']);
				$noUrut = (int)$tmpRow[3];
				if($noUrut>$maxNo)
					$maxNo = $noUrut;
				}
				$currNo = addZero($maxNo+1,3);
				$notransaksi = $param['tgl']."/".$param['kodeorg']."/".$param['tipetransaksi']."/".$currNo;
			}
			#=== End buat nomor transaksi ===
			#==== Update nomor transaksi ====
			
			# Cek sudah ada atau belum ???
			$str = "select * from " . $dbname . ".kebun_aktifitas where nobkm='".$param['nobkm']."'";
			$resp=count(fetchData($str));
			
			# Cek aktifitas sudah ada atau belum ???
			$str = "select * from " . $dbname . ".kebun_aktifitas where nobkm='".$param['nobkm']."' and tipetransaksi='BKM'";
			$resa=count(fetchData($str));
			
			# Cek aktifitas sudah ada atau belum ???
			$str = "select * from " . $dbname . ".kebun_aktifitas where nobkm='".$param['nobkm']."' and tipetransaksi='".$param['tipetransaksi']."'";
			$rexx = fetchData($str); 
			$resx=count($rexx);
		
			#exit("error masuk asdfghjk".$resp."____".$resa."+++++++".$resx);
			if($resp==0 and $resa>0){
				# pertama kali input
				$str = "update " . $dbname . ".kebun_aktifitas set `notransaksi`='".$notransaksi."', `tipetransaksi`='".$param['tipetransaksi']."' where `nobkm`='".$param['nobkm']."' and tipetransaksi='BKM'"; #exit("error".$str);
				$owlPDO->exec($str);
				
				# update norefrensi absensi
				$str = "update " . $dbname . ".sdm_absensidt set `norefrensi`='".$notransaksi."' where `norefrensi`='".$param['nobkm']."' and `nobkm`='".$param['nobkm']."'"; #exit("error".$str);
				$owlPDO->exec($str);
				
				$param['notransaksi']=$notransaksi;
				
				
			}elseif($resp>0 and $resx==0){
				# input selanjutnya namun tipe berbeda
				$str = "insert into " . $dbname . ".kebun_aktifitas (`notransaksi`, `tipetransaksi`, `tanggal`, `nobkm`, `kodeorg`, `nikmandor`, `nikmandor1`, `nikasisten`, `keranimuat`, `jurnal`, `nospk`, `kontanan`, `updateby`, `divisi`)
				values ('".$notransaksi."','".$param['tipetransaksi']."','".$param['tgl']."','" . $param['nobkm'] . "','" . $kodeorg . "','".$mandor."','".$mandor1."','".$asst."','".$kerani."','0',null,'".$param['kontanan']."','" . $_SESSION['standard']['userid'] . "','".$param['divisi']."')";
				$owlPDO->exec($str);
				
				$param['notransaksi']=$notransaksi;
			}else{
				$str = "update " . $dbname . ".kebun_aktifitas set `nikmandor`='".$mandor."', `nikmandor1`='".$mandor1."',`keranimuat`='".$kerani."',`nikasisten`='".$asst."', `kontanan`='".$param['kontanan']."'
				where `nobkm`='".$param['nobkm']."'"; #exit("error".$str);
				$owlPDO->exec($str);
				
				$param['notransaksi']=$rexx[0]['notransaksi'];
			}
			
			#cek apakah sudah di posting	
			$optpost=makeOption($dbname,'kebun_aktifitas','notransaksi,jurnal',"notransaksi='".$param['notransaksi']."'");
			if($optpost[$param['notransaksi']]==1){
				$ststransblok = explode("/",$param['notransaksi']);
				throw new PDOException("Transaksi dengan nomor ".$param['notransaksi']." (status blok : ".$ststransblok[2].") sudah diposting, silahkan buat Transaksi dengan No BKM yang baru untuk menambahkan.");
			}
			
			# Validasi penginputan
			cekPrestasi($param);
			
			#validasi penginputan
			validasiInput($kodeorg,substr($param['blok'],0,6),'BKM',tanggalsystemn(tanggalnormal($param['tgl'])),$exit='0');
			
			#validasi maksimal HK KHL
			if (getKary($param['karyawanid'],'tipekaryawan') == 4) {
				cekmaxnilaihk($param['karyawanid'],tanggalsystemn(tanggalnormal($param['tgl'])),'1','0','new',$exit='0');
			}

			// Validasi Maksimal HK untuk Karyawan Non Staff Bulanan
			$arrkarybln = array("1","2","3","5");
			if (in_array(getKary($param['karyawanid'],'tipekaryawan'),$arrkarybln)) {
				cekmaxhkBln($param['karyawanid'],tanggalsystemx(tanggalnormal($param['tgl'])),'1','0','new',$exit='0');
				
				$arrtgl  = explode("-",$param['tgl']);
				$thnx     = $arrtgl[0];
				$blnx     = $arrtgl[1];
				$periodex = $thnx."-".$blnx;
				$jmlhkbln = 0;
				$totalhkx = 0;

				// Cek Di Validasi Apakah Jumlah HK di kebun_prestasi BKM melebihi 25 HK dalam periode
				$sPrestasi = selectQuery($dbname,"kebun_prestasi","sum(jumlahhk) AS jumlahhk","nikpemel='".$param['karyawanid']."' AND notransaksi like '".str_replace("-","",$periode)."%'");
				$rPrestasi = fetchdata($sPrestasi);
				$jmlhkbln += $rPrestasi[0]['jumlahhk'];

				// Cek Juga Apakah Karyawan yang kemungkinan sebagai Mandor dapat HK
				$sMandor = selectQuery($dbname,"kebun_aktifitas","*","nikmandor='".$param['karyawanid']."' and tanggal like '".$periode."%'");
				$rMandor = fetchData($sMandor);
				$jmlhkbln += count($rMandor);

				// Cek Juga Apakah Karyawan yang kemungkinan sebagai Mandor1 dapat HK
				$sMandor1 = selectQuery($dbname,"kebun_aktifitas","*","nikmandor1='".$param['karyawanid']."' and tanggal like '".$periode."%'");
				$rMandor1 = fetchData($sMandor1);
				$jmlhkbln += count($rMandor1);

				// Cek Juga Apakah Karyawan yang kemungkinan sebagai asisten dapat HK
				$sAsisten = selectQuery($dbname,"kebun_aktifitas","*","nikasisten='".$param['karyawanid']."' and tanggal like '".$periode."%'");
				$rAsisten = fetchData($sAsisten);
				$jmlhkbln += count($rAsisten);

				// Cek Apakah Karyawan yang kemungkinan dapat HK di kegitan traksi
				$sVhc = selectQuery($dbname,"vhc_runhk","*","idkaryawan='".$param['karyawanid']."' and tanggal like '".$periode."%'");
				$rVhc = fetchdata($sVhc);
				$jmlhkbln += count($rVhc);

				// Cek Kelompok kode absensi yang kelompok = 1
				$str = "select * from ".$dbname.".sdm_5absensi where status	='1'";
				$res = fetchData($str);
				foreach($res as $val){
					$kodeabsensi[$val['kodeabsen']]=$val['kelompok'];
				}
				// Cek Apakah Ada Absensi Umum atas nama karyawan tersebut
				$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyawanid']."' and tanggal like '".$periode."%'";
				$res = fetchData($str);
				foreach($res as $val){
					if($kodeabsensi[$val['absensi']]=='1'){	
						$jmlhkbln += $val['hk'];			
					}
				}

				$totalhkx = (floatval($jmlhkbln) + floatval($param['jhk']));
			}
			
			# Buat nomor urut
			$sql = "select max(nourut) as nourut from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."' limit 1"; 
			$res=fetchData($sql);

			# ==========================================================================================
			if($param['filterdivisi']!='PROJECT'){				
				$optstskeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok',"kodekegiatan='".$param['kegiatan']."'");
				if ($optstskeg[$param['kegiatan']] != "PNN") {
					if($optstsblok[$param['blok']]!=$optstskeg[$param['kegiatan']]){
						throw new PDOException("Kode kegiatan salah, kode kegiatan : ".$optstskeg[$param['kegiatan']]." sementara blok : ".$optstsblok[$param['blok']]."");
					}
				}
			}
			# ==========================================================================================
			// Jika Tipe Karyawan nya KHT,KBT, atau Nonstaff
			if (in_array(getKary($param['karyawanid'],'tipekaryawan'),$arrkarybln)) {
				// Jika Total HK nya lebih dari 25 maka upah jangan diisi, diganti ke premi
				if ($totalhkx > 25) {
					$param['premi'] = $premi['upah'] + $premi['premi'];
					$cols = array(
								'nobkm','notransaksi','nourut','nik','nikpemel','kodekegiatan','kodeorg','hasilkerja','jumlahhk','tahuntanam','upahpremi'
							);
					$data = array(
								$param['nobkm'],$param['notransaksi'],($res[0]['nourut']+1),'-',$param['karyawanid'],$param['kegiatan'],$param['blok'],$param['prestasi'],$param['jhk'],'0',$param['premi']
							);
				} else {
					// Jika belum melebihi 25 HK maka seperti biasa upah dimasukkan
					$cols = array(
								'nobkm','notransaksi','nourut','nik','nikpemel','kodekegiatan','kodeorg','hasilkerja','jumlahhk','tahuntanam','upahpremi'
							);
					$data = array(
								$param['nobkm'],$param['notransaksi'],($res[0]['nourut']+1),'-',$param['karyawanid'],$param['kegiatan'],$param['blok'],$param['prestasi'],$param['jhk'],'0',$param['premi']
							);
				}
			} else {
				// Jika Bukan tipe karyawan 1,2,3 seperti biasa
				$cols = array(
							'nobkm','notransaksi','nourut','nik','nikpemel','kodekegiatan','kodeorg','hasilkerja','jumlahhk','tahuntanam','upahpremi'
						);
				$data = array(
							$param['nobkm'],$param['notransaksi'],($res[0]['nourut']+1),'-',$param['karyawanid'],$param['kegiatan'],$param['blok'],$param['prestasi'],$param['jhk'],'0',$param['premi']
						);
				// exit("Warning: Masuk Ke tipe KHL");
			}
			
			# Insert kebun_prestasi
			$query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
			#exit("error".$query);
			$owlPDO->exec($query);
			# ==========================================================================================
			// Jika Tipe Karyawan nya KHT,KBT, atau Nonstaff
			if (in_array(getKary($param['karyawanid'],'tipekaryawan'),$arrkarybln)){
				// Jika Total HK nya lebih dari 25 maka upah jangan diisi, diganti ke premi
				if ($totalhkx > 25) {
					$param['premi'] = $premi['upah'] + $premi['premi'];

					$cols = array(
							'notransaksi','nourut','nik','absensi','jhk','umr','insentif','hasilkerja'
						);
					$data = array(
						$param['notransaksi'],($res[0]['nourut']+1),$param['karyawanid'],'H',$param['jhk'],'0',$param['premi'],$param['prestasi']
					);
				} else {
					// Jika belum melebihi 25 HK maka seperti biasa upah dimasukkan
					$cols = array(
						'notransaksi','nourut','nik','absensi','jhk','umr','insentif','hasilkerja'
					);
					$data = array(
						$param['notransaksi'],($res[0]['nourut']+1),$param['karyawanid'],'H',$param['jhk'],$param['upah'],$param['premi'],$param['prestasi']
					);
				}
			} else {
				// Jika Bukan tipe karyawan 1,2,3 seperti biasa
				$cols = array(
							'notransaksi','nourut','nik','absensi','jhk','umr','insentif','hasilkerja'
						);
				$data = array(
							$param['notransaksi'],($res[0]['nourut']+1),$param['karyawanid'],'H',$param['jhk'],$param['upah'],$param['premi'],$param['prestasi']
						);
			}
			# Insert kebun_kehadiran
			$query = insertQuery($dbname,'kebun_kehadiran',$data,$cols);
			$owlPDO->exec($query);
			# ==========================================================================================
		
		}
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	
	echo $param['notransaksi'];
	break;
	case'update':
	try {
	$owlPDO->beginTransaction();
	
		if($param['prestasi']==''){
			$param['prestasi']='0';
		}
		if($param['jhk']==''){
			$param['jhk']='0';
		}
		if($param['upah']==''){
			$param['upah']='0';
		}
		if($param['premi']==''){
			$param['premi']='0';
		}
		
		if($param['upah']=='0' and $param['jhk']!='0'){
			throw new PDOException("Nilai HK dan Upah tidak sesuai.");
		}
		if($param['upah']!='0' and $param['jhk']=='0'){
			throw new PDOException("Nilai HK dan Upah tidak sesuai.");
		}
		
		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['notransaksi']!='' and $param['karyawanid']!='' and $param['kegiatan']!='' and $param['blok']!='' and $param['prestasi']!='0' and (($param['jhk']!='0' and $param['upah']!='0') or $param['premi']!='0')){
			
			$upahharian = 0;
			$tahun = substr(tanggalsystemn($param['tgl']),0,4);
			$bulan = substr(tanggalsystemn($param['tgl']),5,2);
			$prdgj = $tahun."-".$bulan;
			
			$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$param['karyawanid']."' and tahun='".$prdgj."' and idkomponen in ('1')"; #exit("Warning :".$str);
			$res = fetchdata($str);
			$upahharian = $res[0]['nilai']/25;
			$upahkary = $upahharian * $param['jhk'];
			
			if(round($upahkary-$param['upah'],0)!=0){
				throw new PDOException("Nilai HK dan Upah tidak sesuai.");
			}
			
			# Ambil nomor urut kary
			$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and kodekegiatan='".$param['kegiatan']."'";
			$res=fetchData($str);
			$nourut=$res[0]['nourut'];
					
			# Validasi penginputan
			cekPrestasi($param);
			
			#validasi maksimal HK BHL
			cekmaxnilaihk($param['karyawanid'],tanggalsystemn($param['tgl']),'1','1','edit',$exit='0');

			// Validasi Maksimal HK untuk Karyawan Non Staff Bulanan
			$arrkarybln = array("1","2","3","5");
			if (in_array(getKary($param['karyawanid'],'tipekaryawan'),$arrkarybln)) {
				cekmaxhkBln($param['karyawanid'],tanggalsystemx($param['tgl']),'1','0','new',$exit='0');
				
				$arrtgl  = explode("-",$param['tgl']);
				$thnx     = $arrtgl[2];
				$blnx     = $arrtgl[1];
				$periodex = $thnx."-".$blnx;
				$jmlhkbln = 0;
				$totalhkx = 0;

				// Cek Di Validasi Apakah Jumlah HK di kebun_prestasi BKM melebihi 25 HK dalam periode
				$sPrestasi = selectQuery($dbname,"kebun_prestasi","sum(jumlahhk) AS jumlahhk","nikpemel='".$param['karyawanid']."' AND notransaksi like '".str_replace("-","",$periodex)."%'");
				$rPrestasi = fetchdata($sPrestasi);
				$jmlhkbln += $rPrestasi[0]['jumlahhk'];

				// Cek Juga Apakah Karyawan yang kemungkinan sebagai Mandor dapat HK
				$sMandor = selectQuery($dbname,"kebun_aktifitas","*","nikmandor='".$param['karyawanid']."' and tanggal like '".$periodex."%'");
				$rMandor = fetchData($sMandor);
				$jmlhkbln += count($rMandor);

				// Cek Juga Apakah Karyawan yang kemungkinan sebagai Mandor1 dapat HK
				$sMandor1 = selectQuery($dbname,"kebun_aktifitas","*","nikmandor1='".$param['karyawanid']."' and tanggal like '".$periodex."%'");
				$rMandor1 = fetchData($sMandor1);
				$jmlhkbln += count($rMandor1);

				// Cek Juga Apakah Karyawan yang kemungkinan sebagai asisten dapat HK
				$sAsisten = selectQuery($dbname,"kebun_aktifitas","*","nikasisten='".$param['karyawanid']."' and tanggal like '".$periodex."%'");
				$rAsisten = fetchData($sAsisten);
				$jmlhkbln += count($rAsisten);

				// Cek Apakah Karyawan yang kemungkinan dapat HK di kegitan traksi
				$sVhc = selectQuery($dbname,"vhc_runhk","*","idkaryawan='".$param['karyawanid']."' and tanggal like '".$periodex."%'");
				$rVhc = fetchdata($sVhc);
				$jmlhkbln += count($rVhc);

				// Cek Kelompok kode absensi yang kelompok = 1
				$str = "select * from ".$dbname.".sdm_5absensi where status	='1'";
				$res = fetchData($str);
				foreach($res as $val){
					$kodeabsensi[$val['kodeabsen']]=$val['kelompok'];
				}
				// Cek Apakah Ada Absensi Umum atas nama karyawan tersebut
				$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyawanid']."' and tanggal like '".$periodex."%'";
				$res = fetchData($str);
				foreach($res as $val){
					if($kodeabsensi[$val['absensi']]=='1'){	
						$jmlhkbln += $val['hk'];			
					}
				}

				$totalhkx = (floatval($jmlhkbln) + floatval($param['jhk']));
			}

			# ==========================================================================================
			// Jika Tipe Karyawan nya KHT,KBT, atau Nonstaff
			if (in_array(getKary($param['karyawanid'],'tipekaryawan'),$arrkarybln)){
				// Jika Total HK nya lebih dari 25 maka upah jangan diisi, diganti ke premi
				if ($totalhkx > 25){
					$data = array(
						'hasilkerja'=>$param['prestasi'],
						'jumlahhk'=>$param['jhk'],
						'upahpremi'=>($param['premi'] + $param['upah'])
					);
				} else {
					// Jika belum melebihi 25 HK maka seperti biasa upah dimasukkan
					$data = array(
						'hasilkerja'=>$param['prestasi'],
						'jumlahhk'=>$param['jhk'],
						'upahpremi'=>$param['premi']
					);
				}
			} else {
				// Jika Bukan tipe karyawan 1,2,3 seperti biasa
				$data = array(
					'hasilkerja'=>$param['prestasi'],
					'jumlahhk'=>$param['jhk'],
					'upahpremi'=>$param['premi']
				);
			}
			$where = "notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and kodekegiatan='".$param['kegiatan']."'";
			
			# Update kebun_prestasi
			$query = updateQuery($dbname,'kebun_prestasi',$data,$where);
			$owlPDO->exec($query);
			# ==========================================================================================
			// Jika Tipe Karyawan nya KHT,KBT, atau Nonstaff
			if (in_array(getKary($param['karyawanid'],'tipekaryawan'),$arrkarybln)) {
				// Jika Total HK nya lebih dari 25 maka upah jangan diisi, diganti ke premi
				if ($totalhkx > 25) {
					$data = array(
						'hasilkerja'=>$param['prestasi'],
						'jhk'=>$param['jhk'],
						'umr'=>'0',
						'insentif'=>($param['premi'] + $param['upah'])
					);
				} else {
					// Jika belum melebihi 25 HK maka seperti biasa upah dimasukkan
					$data = array(
						'hasilkerja'=>$param['prestasi'],
						'jhk'=>$param['jhk'],
						'umr'=>$param['upah'],
						'insentif'=>$param['premi']
					);
				}
			} else {
				// Jika Bukan tipe karyawan 1,2,3 seperti biasa
				$data = array(
							'hasilkerja'=>$param['prestasi'],
							'jhk'=>$param['jhk'],
							'umr'=>$param['upah'],
							'insentif'=>$param['premi']
						);
			}
			$where = "notransaksi='".$param['notransaksi']."' and nik='".$param['karyawanid']."' and nourut='".$nourut."'";
			
			# Update kebun_kehadiran
			$query = updateQuery($dbname,'kebun_kehadiran',$data,$where);
			$owlPDO->exec($query);
			# ==========================================================================================
		}
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
    case'delete':
        try {
		$owlPDO->beginTransaction();
		
		$str="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$notransaksi."'";
		$bar=fetchData($str);
		$tgl=$bar[0]['tanggal'];
		$kodeorg=$bar[0]['kodeorg'];
		$nobkm=$bar[0]['nobkm'];
		
		$str="select * from ".$dbname.".kebun_aktifitas where nobkm='".$nobkm."'";
		$res=fetchData($str);
		if(count($res)>1){
			if (!empty($notransaksi) && !empty($nobkm)) {
				$str = "delete from " . $dbname . ".sdm_absensidt where (norefrensi='".$notransaksi."' AND norefrensi != '') and (nobkm='".$nobkm."' AND nobkm != '')"; 
				$owlPDO->exec($str);
			}
		}else{
			if (!empty($nobkm)) {
				$str = "delete from " . $dbname . ".sdm_absensidt where nobkm='".$nobkm."' and nobkm != ''";
				$owlPDO->exec($str);
			}
		}
		
		
		
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
        
		$str = "SELECT * FROM " . $dbname . ".listfileupload where notransaksi ='".$param['notransaksi']."' and kriteriaefil='BKM'";
		$res = fetchData($str);
		if(count($res)>0){
			foreach($res as $bar){
				$str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$bar['namafile']."'";
				$owlPDO->exec($str);
				$pathx = $path.$bar['namafile'];
				#unlink ditiadakan
				#unlink($pathx);
			}
		}
		
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			

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
    case'deletedetail':
		#cek dulu di tab material udah di simpan belum, jika sudah harus di hapus juga
		$str = "SELECT * FROM " . $dbname . ".kebun_pakaimaterial where notransaksi ='".$notransaksi."' and kodeorg='" . $blok . "' and kodekegiatan='".$kegiatan."'";
		$res=fetchData($str);
		if(count($res)>0){
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kegiatan."'");
			exit("Error : Material untuk kegiatan : ".$nmkeg[$kegiatan]."<br>Blok : ".$blok." sudah pernah di input, silahkan hapus terlebih dahulu melalui tab Material");
		}
		# Ambil nomor urut kary
		$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi ='".$notransaksi."' and kodeorg='" . $blok . "' and nikpemel='" . $karyawanid . "' and kodekegiatan='".$kegiatan."'";
		$res=fetchData($str);
		$nourut=$res[0]['nourut'];
			
		#hapus kehadiran
		$str = "delete from " . $dbname . ".kebun_kehadiran where notransaksi ='".$notransaksi."' and nik='" . $karyawanid . "' and nourut='".$nourut."'";
		try {$owlPDO->exec($str);
			#hapus prestasi	
			$str = "delete from " . $dbname . ".kebun_prestasi where notransaksi ='".$notransaksi."' and kodeorg='" . $blok . "' and nikpemel='" . $karyawanid . "' and kodekegiatan='".$kegiatan."'";
			try {$owlPDO->exec($str);
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
		} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
    break;
	
	case'loaddataexcel':
		#validasi
		stsawal($param);
		$kodorg=array();
        $where=$wh3="";
		
		/* 
		$where.= "and a.kodeorg in (".getOrgDetail(24).")";
		$wh3.= "and a.kodeorg in (".getOrgDetail(24).")";
		$whsdm= "and substr(kodeorg,1,4) in (".getOrgDetail(24).")";
		*/
		
		$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABKRN'"; 
		$res = fetchdata($str);
		@$arrjab = explode(',', $res[0]['nilai']);
		
		#karena list data di filter berdasarkan pembuat terkadang user mencari nomor transaksi yg di input oleh user lain tidak muncul, solusinya munculkan semua transaksi pada saat pencarian walau bukan ybs pembuatnya namun tidak bisa di edit.
		$subbg='isi';
		if($_SESSION['empl']['subbagian']==''){
			$subbg='kosong';
		}

		// if(in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
		// 	// $where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and (a.divisi like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."')"; 
		// 	$where.=" and (a.kodeorg IN (".getOrgDetail(2).") and a.divisi IN (".getOrgDetail(26)."))"; 
		// 	$wh3.= "and a.divisi IN (".getOrgDetail(26).")";
		// }elseif($_SESSION['empl']['subbagian']==''){
		// 	$where.= " and (a.kodeorg = '".$_SESSION['empl']['lokasitugas']."')";
		// 	$wh3.= "and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		// 	$whsdm.=" and substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."'";
		// }else{
		// 	$where.=" and (a.divisi like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."')"; 
		// 	$wh3.= "and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		// 	$whsdm.=" and substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."'";
		// 	# or b.kodeorg is null
		// }

		$where.=" and (a.kodeorg IN (".getOrgDetail(2).") and a.divisi IN (".getOrgDetail(26)."))"; 
		$wh3.= "and a.divisi IN (".getOrgDetail(26).")";
		
		// echo $where;exit();
		
		// $wh3.= "and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		// $whsdm.=" and substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."'";
		// $where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		
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
            $wh3.=" and a.tanggal like '" . $periodesch . "%' ";
        }
		if ($nobkmsch != '') {
            $where.=" and a.nobkm like '%".$nobkmsch."%' ";
        }
		if ($mandorsrc != '' and $mandorsrc != 'blank') {
            $where.=" and a.nikmandor in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".$mandorsrc."%')";
			
        } else if($mandorsrc == 'blank'){
            $where.=" and a.nikmandor = '' ";
		}
		
		$wh="('PNN')";
		

		$fileup=array();
        $sql = "select distinct(notransaksi) as notransaksi from " . $dbname . ".listfileupload where 1=1 and notransaksi like '%".$_SESSION['empl']['lokasitugas']."%' and notransaksi like '".str_replace("-","",$periodesch)."%' and kriteriaefil='BKM'";
        $res = fetchdata($sql);
		foreach ($res as $bar) {
			$fileup[$bar['notransaksi']]=$bar['notransaksi'];
		}
		
		$sql = "select count(distinct a.notransaksi) as notr from " . $dbname . ".kebun_aktifitas a where 1=1 and a.tipetransaksi not in ".$wh." " . $where . "";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=22 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
		
		$ttl=array();
		$strn = "select karyawanid, norefrensi, nobkm, kodeorg,sum(umr) as umr, sum(premi) as premi, sum(hk) as hk from ".$dbname.".sdm_absensidt where norefrensi!='' and nobkm!='' ".$whsdm." group by norefrensi, nobkm, karyawanid"; 
		$resn = fetchdata($strn);
		foreach ($resn as $bar) {
			if(getKary($bar['karyawanid'],'tipekaryawan')==4){				
				$umrab[$bar['norefrensi']][$bar['nobkm']]+=$bar['umr'];
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['umr']+$bar['premi'];
			}else{				
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['premi'];
			}
			// premi bkm jangan pake $resn[0]['premi']
			$hkab[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk'];
			$premab[$bar['norefrensi']][$bar['nobkm']]+=$bar['premi'];
		}
		
		$strx = "select sum(umr) as umr, sum(jhk) as jhk, sum(insentif) as insentif, notransaksi from ".$dbname.".kebun_kehadiran where notransaksi in (select notransaksi from " . $dbname . ".kebun_aktifitas a where 1=1 and a.tipetransaksi not in ".$wh." ".$wh3.") group by notransaksi"; 
		$resn = fetchdata($strx);
		foreach ($resn as $bar) {
			$umr[$bar['notransaksi']]=$bar['umr'];
			$hkp[$bar['notransaksi']]=$bar['jhk'];
			$premip[$bar['notransaksi']]=$bar['insentif'];
			$ttlrp2[$bar['notransaksi']]=$bar['umr']+$bar['jhk']+$bar['insentif'];
		}
		
		/* $str = "select * from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."'"; 
		$resn = fetchdata($str);
		foreach ($resn as $bar) {
			$subbagian[$bar['karyawanid']]=$bar['subbagian'];
			$namakarya[$bar['karyawanid']]=$bar['namakaryawan'];
		} */
		
		$notrx=1;
        //$str = "SELECT a.*, substr(b.kodeorg,1,6) as divisipres FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi not in ".$wh." " . $where . " group by a.notransaksi order by a.nobkm desc, a.notransaksi desc limit " . $offset . "," . $limit . ""; 
		
		
		
		$tab.="<table class='sortable' cellspacing='1' cellpadding='5' border='1' width=100%>
			<thead>
				<tr class=rowheader>
					<th align=center width=40px>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center >No BKM</th>
					<th align=center >" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center >" . $_SESSION['lang']['sumber'] . "</th>
					<th align=center >" . $_SESSION['lang']['noreferensi'] . "</th>
					<th align=center >" . $_SESSION['lang']['organisasi'] . "</th>
					<th align=center >" . $_SESSION['lang']['divisi'] . "</th>
					<th align=center >" . $_SESSION['lang']['hari'] . "</th>
					<th align=center width=100px>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center >" . $_SESSION['lang']['jhk'] . "</th>
					<th align=center >" . $_SESSION['lang']['upah'] . "</th>
					<th align=center >" . $_SESSION['lang']['premi'] . "</th>
					<th align=center >" . $_SESSION['lang']['mandor'] . "</th>
					<th align=center >" . $_SESSION['lang']['mandor'] . " 1</th>
					<th align=center >" . $_SESSION['lang']['kerani'] . "</th>
					<th align=center >" . $_SESSION['lang']['nikasisten'] . "</th>
					<th align=center >" . $_SESSION['lang']['updateby'] . "</th>
			</thead>";
		
		
        
		$str = "SELECT * FROM " . $dbname . ".kebun_aktifitas a where 1=1 and a.tipetransaksi not in ".$wh." " . $where . " group by a.notransaksi order by a.nobkm desc, a.notransaksi desc"; 
		// echo $str;
		// exit('error'.$str);
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $isi=$a=$xx=$cl=$abs='';
            $no+=1;
			$n=explode("/",$bar['nobkm']);
			
			if($bar['divisi']==''){
				$bar['divisi']=$bar['divisipres'];
			}
			
			//if($bar['divisipres']=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]==0){
			if($ttlrp2[$bar['notransaksi']]=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]==0){
				$cl=" style=background-color:#ff2929; title=\"Data detail belum ada.\"";
			//}elseif($bar['divisipres']=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]>0){
			}elseif($ttlrp2[$bar['notransaksi']]=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]>0){
				$cl=" style=background-color:yellow; title=\"Data hanya absensi.\"";
				$abs="absensi";
			}
			
			$hari=$c="";
			$hari = date('D', strtotime($bar['tanggal']));
			if($hari=='Sun'){
				$c="style=\"color:red\"";
			}
			if($hari=='Fri'){
				$c="style=\"color:blue\"";
			}
			
			$a=$a1=$b=$b1=$d=$d1="";
			if(getSubbagian($bar['nikmandor'])!=$bar['divisi']){
				$a="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor'])."</i></b></font>";				
				$a1="title=\"Karyawan asistensi\"";				
			}
			if(getSubbagian($bar['nikmandor1'])!=$bar['divisi']){
				$b="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor1'])."</i></b></font>";				
				$b1="title=\"Karyawan asistensi\"";				
			}
			if(getSubbagian($bar['keranimuat'])!=$bar['divisi']){
				$d="<br><font size=1px color=blue><b><i>".getSubbagian($bar['keranimuat'])."</i></b></font>";				
				$d1="title=\"Karyawan asistensi\"";				
			}
			
            $tab.="<tr class=rowcontent ".$xx." ".$cl." id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['nobkm'] . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
			if($bar['noreferensi']!='' and $bar['deviceid']!=''){
				$tab.="<td align=center>Mobile</td>";				
			}elseif($bar['noreferensi']=='' and is_null($bar['deviceid'])){
				$tab.="<td align=center>Web</td>";
			}elseif($bar['noreferensi']!='' and is_null($bar['deviceid'])){
				$tab.="<td align=center>Otomatis</td>";
			}else{
				$tab.="<td align=center>Other</td>";
			}
            $tab.="<td align=center>" . $bar['noreferensi'] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
            $tab.="<td align=center>" . $bar['divisi'] . "</td>";
            $tab.="<td align=center ".$c.">" . hari($bar['tanggal'],'ID') . "</td>";
            $tab.="<td align=center ".$c.">" . tanggalnormal($bar['tanggal']) . "</td>";
            // $tab.="<td align=center>" . @numb_format($bar['hk']+$resn[0]['hk'],2) . "</td>";
            // $tab.="<td align=right>" . @numb_format($resx[0]['umr']+$resn[0]['umr']) . "</td>";
            // $tab.="<td align=right>" . @numb_format($bar['premi']+$resn[0]['premi'],2) . "</td>";
            $tab.="<td align=center>" . @numb_format($hkp[$bar['notransaksi']]+$hkab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
            $tab.="<td align=right>" . @numb_format($umr[$bar['notransaksi']]+$umrab[$bar['notransaksi']][$bar['nobkm']]) . "</td>";
            $tab.="<td align=right>" . @numb_format($premip[$bar['notransaksi']]+$premab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
			//$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$where);
            $tab.="<td align=center ".$a1.">" . getKary($bar['nikmandor']). "".$a."</td>";
            $tab.="<td align=center ".$b1.">" . getKary($bar['nikmandor1']) . "".$b."</td>";
            $tab.="<td align=center ".$d1.">" . getKary($bar['keranimuat']) . "".$d."</td>";
            $tab.="<td align=center>" . getKary($bar['nikasisten']). "</td>";
            $tab.="<td align=center>" . getKary($bar['updateby']). "</td>";
            

            $tab.="</tr>";
        }
		
		  $tab.="<table>";

		$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("YmdHis");
		$nop_="listbkm__".$tglSkrg;
		if(strlen($tab)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					@unlink('tempExcel/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$tab))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}  

	break;

    case'loaddata':
		#validasi
		stsawal($param);
		$kodorg=array();
        $where=$wh3="";
		
		/* 
		$where.= "and a.kodeorg in (".getOrgDetail(24).")";
		$wh3.= "and a.kodeorg in (".getOrgDetail(24).")";
		$whsdm= "and substr(kodeorg,1,4) in (".getOrgDetail(24).")";
		*/
		
		$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABKRN'"; 
		$res = fetchdata($str);
		@$arrjab = explode(',', $res[0]['nilai']);
		
		#karena list data di filter berdasarkan pembuat terkadang user mencari nomor transaksi yg di input oleh user lain tidak muncul, solusinya munculkan semua transaksi pada saat pencarian walau bukan ybs pembuatnya namun tidak bisa di edit.
		$subbg='isi';
		if($_SESSION['empl']['subbagian']==''){
			$subbg='kosong';
		}
		
		// if(in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
		// 	// $where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and (a.divisi like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."')"; 
		// 	$where.=" and (a.kodeorg IN (".getOrgDetail(2).") and a.divisi IN (".getOrgDetail(26)."))"; 
		// 	$wh3.= "and a.divisi IN (".getOrgDetail(26).")";
		// }elseif($_SESSION['empl']['subbagian']=='' ){
		// 	$where.= " and (a.kodeorg = '".$_SESSION['empl']['lokasitugas']."')";
		// 	$wh3.= "and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		// 	$whsdm.=" and substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."'";
		// }else{
		// 	// $where.=" and (a.divisi like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."')"; 
		// 	// $where.= " and (a.kodeorg = '".$_SESSION['empl']['lokasitugas']."')";
		// 	// $wh3.= "and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		// 	// $whsdm.=" and substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."'";
		// }

		$where.=" and (a.kodeorg IN (".getOrgDetail(2).") and a.divisi IN (".getOrgDetail(26)."))"; 
		$wh3.= "and a.divisi IN (".getOrgDetail(26).")";
		
		// echo $where;exit();
		
		// $wh3.= "and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		// $whsdm.=" and substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."'";
		// $where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		
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
            $wh3.=" and a.tanggal like '" . $periodesch . "%' ";
        }
		if ($nobkmsch != '') {
            $where.=" and a.nobkm like '%".$nobkmsch."%' ";
        }

		if ($mandorsrc != '' and $mandorsrc != 'blank') {
            $where.=" and a.nikmandor in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".$mandorsrc."%')";
			
        } else if($mandorsrc == 'blank'){
            $where.=" and a.nikmandor = '' ";
		}
		
		$wh="('PNN')";
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
		$tab = "";
        $no = $maxdisplay;
		
		$fileup=array();
        $sql = "select distinct(notransaksi) as notransaksi from " . $dbname . ".listfileupload where 1=1 and notransaksi like '%".$_SESSION['empl']['lokasitugas']."%' and notransaksi like '".str_replace("-","",$periodesch)."%' and kriteriaefil='BKM'";
        $res = fetchdata($sql);
		foreach ($res as $bar) {	
			$fileup[$bar['notransaksi']]=$bar['notransaksi'];
		}

		// GET URI FOR PRODUCTION
		$expri = explode("/",$_SERVER['REQUEST_URI']);

		$svr=parse_url($_SERVER['HTTP_REFERER']);

		$pat=array();
		$pat=explode('/',$svr['path']);
		$arr = array_filter($pat, function($value) {
    		return !is_null($value) && $value !== '';
		});		
		
		$data=[];
		foreach ($arr as $key => $value) {
			if (!strpos($value, ".php")) {
				$data[]=$value;
			}
		}
		$urlocal=$_SERVER['HTTP_ORIGIN'].'/'.implode("/",$data);

		$nok = 0;
		$options = array(
			'client_id' => 'USERSYSTEM',
			'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
			'username' => $_SESSION['standard']['username']
		);
		/** GET API KEY */
		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
			// Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
				$urlInit = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
			}else{
				$urlInit = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
			}
		}else{
			// Jika Server local / localhost maka munculkan URL localhost
			$urlInit = $urlocal."mobile/index.php/api/access_token/api_key";
		}
		$getApi->init($urlInit,$options);

		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
			// Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
				$url = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Mrawat/getheadererp/load';
			}else{
				$url = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Mrawat/getheadererp/load';
			}
		}else{
			// Jika Server local / localhost maka munculkan URL localhost
			$url = $urlocal.'mobile/index.php/api/module/Mrawat/getheadererp/load';
		}
		
		$dataParam = array(
			'dateFrom' => $tglmulai != '--' ? $tglmulai : "",
			'dateTo' =>   $tglselesai != '--' ? $tglselesai : "",
			'notransaksi' => "",
			'divisi' => $divsch ? $divsch : "",
			'periode' => $periodesch ? $periodesch : "",
			'mandor' => $mandorsrc ? $mandorsrc : "",
			'kodeorg' => getOrgDetail(28),
		);
		$data = $getApi->post($url,$dataParam);
		$countbkmmobile = count($data->response['result']['data']); 
		foreach ($data->response['result']['data'] as $key => $val) {
			$nok++;
			$tab.="<tr class=rowcontent style='background-color:#4cdf26;'>";
            $tab.="<td align=center>".$nok."</td>";
            $tab.="<td align=center></td>";
            $tab.="<td align=center>".$val['notransaksi']."</td>";
            $tab.="<td align=center>Mobile</td>";
			$tab.="<td align=center></td>";
            $tab.="<td align=center>".$val['kodeorg']."</td>";
            $tab.="<td align=center>".$val['divisi']."</td>";
            $tab.="<td align=center>".hari($val['tanggal'],"ID")."</td>";
            $tab.="<td align=center>".$val['tanggal']."</td>";
			$tab.="<td align=center></td>";
			$tab.="<td align=center></td>";
			$tab.="<td align=center></td>";
            $tab.="<td align=center>".getNamaKaryawan($val['nikmandor'])."</td>";
			$tab.="<td align=center>".getNamaKaryawan($val['nikmandor1'])."</td>";
			$tab.="<td align=center>".getNamaKaryawan($val['kerani'])."</td>";
			$tab.="<td align=center>".getNamaKaryawan($val['nikasisten'])."</td>";
			$tab.="<td align=center></td>";
			$tab.="<td align=center></td>";
			$tab.="<td align=center></td>";
			if ($val['updateby'] != "") {
				$tab.="<td align=center>".getNamaKaryawan($val['updateby'])."</td>";
			} else {
				$tab.="<td align=center>".getNamaKaryawan($val['createby'])."</td>";
			}
			
			$tab.="<td colspan=7 align=center>
			<img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30' title='Preview Data' onclick=\"previewData('".$val['notransaksi']."','BKM')\">
			</td>";
			$tab.="</tr>";
		}

		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
			// Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
				$url2 = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Kehadiranumum/getHeader/send';
			}else{
				$url2 = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Kehadiranumum/getHeader/send';
			}
		}else{
			// Jika Server local / localhost maka munculkan URL localhost
			$url2 = $urlocal.'mobile/index.php/api/module/Kehadiranumum/getHeader/send';
		}
		$dataParam2 = array(
			'dateFrom' => $tglmulai != '--' ? $tglmulai : "",
			'dateTo' =>   $tglselesai != '--' ? $tglselesai : "",
			'notransaksi' => "",
			'divisi' => $divsch ? $divsch : "",
			'periode' => $periodesch ? $periodesch : "",
			'mandor' => $mandorsrc ? $mandorsrc : "",
			'kodeorg' => getOrgDetail(28),
		);
		$data2 = $getApi->post($url2,$dataParam2);
		// echo "<pre>";
		// print_r($data2->response['result']['data']);
		// echo "</pre>";
		$countbkmmobile += count($data2->response['result']['data']);
		foreach ($data2->response['result']['data'] as $key => $val) {
			if (!empty($val['noreferensi'])) {
				$nok++;
				$tab.="<tr class=rowcontent style='background-color:#4cdf26;'>";
				$tab.="<td align=center>".$nok."</td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center>".$val['notransaksi']."</td>";
				$tab.="<td align=center>Mobile</td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center>".$val['kodeorg']."</td>";
				$tab.="<td align=center>".$val['divisi']."</td>";
				$tab.="<td align=center>".hari($val['tanggal'],"ID")."</td>";
				$tab.="<td align=center>".$val['tanggal']."</td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center>".getNamaKaryawan($val['nikmandor'])."</td>";
				$tab.="<td align=center>".getNamaKaryawan($val['nikmandor1'])."</td>";
				$tab.="<td align=center>".getNamaKaryawan($val['kerani'])."</td>";
				$tab.="<td align=center>".getNamaKaryawan($val['nikasisten'])."</td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center></td>";
				$tab.="<td align=center></td>";
				if ($val['updateby'] != "") {
					$tab.="<td align=center>".getNamaKaryawan($val['updateby'])."</td>";
				} else {
					$tab.="<td align=center>".getNamaKaryawan($val['createby'])."</td>";
				}
				
				$tab.="<td colspan=7 align=center>
				<img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30' title='Preview Data' onclick=\"previewData('".$val['notransaksi']."','BKM')\">
				</td>";
				$tab.="</tr>";
			}
		}

		$sql = "select count(distinct a.notransaksi) as notr from " . $dbname . ".kebun_aktifitas a where 1=1 and a.tipetransaksi not in ".$wh." " . $where . "";
		$res = fetchdata($sql);
        $jlhbrs = ($res[0]['notr'] + $countbkmmobile);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=22 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
		
		$ttl=$coutabs=array();
		$strn = "select karyawanid, norefrensi, nobkm, kodeorg,sum(umr) as umr, sum(premi) as premi, sum(hk) as hk from ".$dbname.".sdm_absensidt where norefrensi!='' and nobkm!='' ".$whsdm." group by norefrensi, nobkm, karyawanid"; 
		$resn = fetchdata($strn);
		foreach ($resn as $bar) {
			if(getKary($bar['karyawanid'],'tipekaryawan')==4){				
				$umrab[$bar['norefrensi']][$bar['nobkm']]+=$bar['umr'];
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['umr']+$bar['premi'];
				$countabs[$bar['norefrensi']][$bar['nobkm']]=count($bar['karyawanid']);
			}else{				
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['premi'];
				$countabs[$bar['norefrensi']][$bar['nobkm']]=count($bar['karyawanid']);
			}
			// premi bkm jangan pake $resn[0]['premi']
			$hkab[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk'];
			$premab[$bar['norefrensi']][$bar['nobkm']]+=$bar['premi'];
		}
		
		$strx = "select sum(umr) as umr, sum(jhk) as jhk, sum(insentif) as insentif, notransaksi from ".$dbname.".kebun_kehadiran where notransaksi like '".str_replace("-","",$periodesch)."%' group by notransaksi"; 
		$resn = fetchdata($strx);
		foreach ($resn as $bar) {
			$umr[$bar['notransaksi']]=$bar['umr'];
			$hkp[$bar['notransaksi']]=$bar['jhk'];
			$premip[$bar['notransaksi']]=$bar['insentif'];
			$ttlrp2[$bar['notransaksi']]=$bar['umr']+$bar['jhk']+$bar['insentif'];
		}

		$strnotif = "SELECT notransaksi, verifiedby, updatetime FROM ".$dbname.".kebun_verifikasibkm where 
		notransaksi like '".str_replace("-","",$periodesch)."%' order by nourut asc";
		$resnotif = fetchdata($strnotif);
		foreach ($resnotif as $bar) {
			$waktuverif[$bar['notransaksi']][$bar['updatetime']] = $bar['updatetime'];
			$verified[$bar['notransaksi']][$bar['verifiedby']] = $bar['verifiedby'];
		}
		
		$notrx=1; 
		if($verifikasisch != ''){
			if($verifikasisch == '1'){
				$str = "SELECT a.* 
				FROM " . $dbname . ".kebun_aktifitas a 
				LEFT JOIN " . $dbname . ".kebun_verifikasibkm b 
				ON a.notransaksi = b.notransaksi and tipetransaksi != 'BKM'
				WHERE 1=1 and statusverifikasi = 1
				AND a.tipetransaksi NOT IN " . $wh . " " . $where . " 
				GROUP BY a.notransaksi 
				ORDER BY a.nobkm DESC, a.notransaksi DESC 
				LIMIT " . $offset . "," . $limit;
			}else{
				$str = "SELECT a.* 
				FROM " . $dbname . ".kebun_aktifitas a 
				LEFT JOIN " . $dbname . ".kebun_verifikasibkm b 
				ON a.notransaksi = b.notransaksi 
				WHERE b.notransaksi IS NULL and tipetransaksi != 'BKM'
				AND a.tipetransaksi NOT IN " . $wh . " " . $where . " 
				GROUP BY a.notransaksi 
				ORDER BY a.nobkm DESC, a.notransaksi DESC 
				LIMIT " . $offset . "," . $limit;
			}
		}else{
			$str = "SELECT * FROM " . $dbname . ".kebun_aktifitas a where 1=1 and a.tipetransaksi not in ".$wh." " . $where . " group by a.notransaksi order by a.nobkm desc, a.notransaksi desc limit " . $offset . "," . $limit . ""; 
		}

        $res = fetchdata($str);
        foreach ($res as $bar) {

			$isi=$a=$xx=$cl=$abs='';
            $no+=1;
			$n=explode("/",$bar['nobkm']);
			
			if($bar['divisi']==''){
				$bar['divisi']=$bar['divisipres'];
			}

			if($ttlrp2[$bar['notransaksi']]=='' and $countabs[$bar['notransaksi']][$bar['nobkm']]==0){
				$cl=" style=background-color:#ff2929; title=\"Data detail belum ada.\"";
			}elseif($ttlrp2[$bar['notransaksi']]=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]>=0){
				$cl=" style=background-color:yellow; title=\"Data hanya absensi.\"";
				$abs="absensi";
			}

			if ($bar['tipetransaksi'] == "PRJ") {
				$abs = "project";
			}
			
			$hari=$c="";
			$hari = date('D', strtotime($bar['tanggal']));
			if($hari=='Sun'){
				$c="style=\"color:red\"";
			}
			if($hari=='Fri'){
				$c="style=\"color:blue\"";
			}
			
			$a=$a1=$b=$b1=$d=$d1="";
			if(getSubbagian($bar['nikmandor'])!=$bar['divisi']){
				$a="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor'])."</i></b></font>";				
				$a1="title=\"Karyawan asistensi\"";				
			}
			if(getSubbagian($bar['nikmandor1'])!=$bar['divisi']){
				$b="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor1'])."</i></b></font>";				
				$b1="title=\"Karyawan asistensi\"";				
			}
			if(getSubbagian($bar['keranimuat'])!=$bar['divisi']){
				$d="<br><font size=1px color=blue><b><i>".getSubbagian($bar['keranimuat'])."</i></b></font>";				
				$d1="title=\"Karyawan asistensi\"";				
			}
			
            $tab.="<tr class=rowcontent ".$xx." ".$cl." id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['nobkm'] . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";

            if($bar['noreferensi']!='' and $bar['deviceid']!=''){
				$tab.="<td align=center>Mobile</td>";				
			}elseif($bar['noreferensi']=='' and is_null($bar['deviceid'])){
				$tab.="<td align=center>Web</td>";
			}elseif($bar['noreferensi']!='' and is_null($bar['deviceid'])){
				$tab.="<td align=center>Otomatis</td>";
			}else{
				$tab.="<td align=center>Other</td>";
			}

            $tab.="<td align=center>" . $bar['noreferensi'] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
            $tab.="<td align=center>" . $bar['divisi'] . "</td>";
            $tab.="<td align=center ".$c.">" . hari($bar['tanggal'],'ID') . "</td>";
            $tab.="<td align=center ".$c.">" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td align=center>" . @numb_format($hkp[$bar['notransaksi']]+$hkab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
            $tab.="<td align=right>" . @numb_format($umr[$bar['notransaksi']]+$umrab[$bar['notransaksi']][$bar['nobkm']]) . "</td>";
            $tab.="<td align=right>" . @numb_format($premip[$bar['notransaksi']]+$premab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
            $tab.="<td align=center ".$a1.">" . getKary($bar['nikmandor']). "".$a."</td>";
            $tab.="<td align=center ".$b1.">" . getKary($bar['nikmandor1']) . "".$b."</td>";
            $tab.="<td align=center ".$d1.">" . getKary($bar['keranimuat']) . "".$d."</td>";
            $tab.="<td align=center>" . getKary($bar['nikasisten']). "</td>";
            $tab.="<td align=center>" . $bar['kontanan']. "</td>";
			$tab.="<td align=center>";
				foreach ($verified[$bar['notransaksi']] as $v) 
				{
					@$nokr++;
					if($nokr == 1){
						$tab.=getNamaKaryawan($v);
					}else{
						$tab.="<br><br>".getNamaKaryawan($v);
					}
				}
			$tab.="</td>";

			$tab.="<td align=center>";
				foreach ($waktuverif[$bar['notransaksi']] as $wv) {
					@$notkr++;
					if ($notkr == 1) {
						$tab.=$wv;	
					} else {
						$tab.="<br><br>".$wv;
					}
				}
			$tab.="</td>";

            $tab.="<td align=center>" . getKary($bar['updateby']). "</td>";
            if ($bar['jurnal'] == 0) {
				if($subbg=='isi' and $bar['updateby']!=$_SESSION['standard']['userid'] and !in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
					$isi.="<td width=20px></td><td width=20px></td>";
				}else{					
					$isi.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".$bar['notransaksi']."','".$bar['kontanan']."','".tanggalnormal($bar['tanggal'])."','".$bar['kodeorg']."','".$bar['nobkm']."','".$bar['nikmandor']."','".$bar['nikmandor1']."','".$bar['nikasisten']."','".$bar['keranimuat']."','".$no."');\" ></td>";
					$isi.="<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['notransaksi']."','".$no."');\" ></td>";
				}
				if ($abs == "absensi" || $abs == "project") {
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$isi.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['notransaksi']."','".$no."','".$abs."');\" ></td>";
					} else {
						$isi.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting'></td>";
					}
					
				} else {
					$isi.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"viewPostingData('".$bar['notransaksi']."','".$no."','event','".$statusblok."','html');\" ></td>";
				}
            }elseif ($bar['jurnal'] == 1) {
				$kdpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorg']."'");
				
				$isi.="<td style=width:20px></td><td style=width:20px></td>";
				$isi.="<td align=center style=width:20px><img src=images/skyblue/posted.png class=zImgBtn class=zImgBtn height='30'  title='Posted\nClick untuk melihat Jurnal' onclick=getjurnal('".$kdpt[$bar['kodeorg']]."','".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".tanggalnormal($bar['tanggal'])."')></td>";
			}else{
				$isi.="<td width=20px></td><td width=20px></td><td width=20px></td>";
			}
			$style=" style=width:20px; title=\"File pendukung belum diupload\"";
			if($fileup[$bar['notransaksi']]!=''){
				$style=" style=width:20px;background-color:#68edaf; title=\"File pendukung sudah diupload\"";
			}
			$isi.="<td align=center ".$style."><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$bar['notransaksi']."');\" ></td>";
			
            $isi.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailPDF('".$bar['notransaksi']."','".$no."','event','".$statusblok."');\" ></td>";
            $isi.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','".$statusblok."','html');\" ></td>";
            $isi.="<td align=center style=width:20px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailDataExcel('".$bar['notransaksi']."','".$no."','event','".$statusblok."','excel');\" ></td>";

            $tab.=$isi;

            $tab.="</tr>";
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
        $footd.="<tr><td colspan=27 align=center>";
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
        $footd.="</td></tr>";

        echo $tab . "####" . $footd;

	break;
	case'edithead':
		$str="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		foreach($res as $bar){
			if($bar['divisi']==''){
				$sql="select substr(kodeorg,1,6) as divisi from ".$dbname.".kebun_prestasi where notransaksi='".$notransaksi."'";
				$req=fetchData($sql);
				foreach($req as $val){
					$bar['divisi']=$val['divisi'];
				}
			}
			
			$param['nikmandor'] = $bar['nikmandor'];
			$param['nikmandor1']= $bar['nikmandor1'];
			$param['asst']      = $bar['nikasisten'];
			$param['kerani']    = $bar['keranimuat'];
			$param['divisi']    = $bar['divisi'];
			$param['jenis']     = $bar['jenis'];
			$param['kodeorg']   = $bar['kodeorg'];
			$param['tgl']       = tanggalnormal($bar['tanggal']);
			
			echo $bar['notransaksi']."####".tanggalnormal($bar['tanggal'])."####".$bar['kodeorg']."####".$bar['nobkm']."####".$bar['nikmandor']."####".$bar['nikmandor1']."####".$bar['nikasisten']."####".$bar['keranimuat']."####".$bar['divisi']."####".$bar['jenis'];
			
		}
		
		echo "####".getNamaOrg($bar['kodeorg']);
		echo "####".getNamaOrg($bar['divisi']);
		echo "####".getmandor($param);
		
	break;
	case 'showupload':
		$tab="";
		$tab.="
		<table border=0 >
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td id='notranupload'>". $param['notransaksi']."</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='status'></p>
					<p id='loaded_n_total'></p>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."')\">Submit</button>
				</td>
			</tr>
		</table>
		";
		$str = "select * from ".$dbname.".kebun_aktifitas where notransaksi='".$notransaksi."'";
		$res = fetchData($str);
		if($res[0]['jurnal']==1){
			$tab="<b>List File Upload<br></b>";
		}
		$tab.="
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=30px colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		";

		echo $tab;
	break;
	
	case 'submitfile':
		try {
		$owlPDO->beginTransaction();
		$data = $_POST;
		if(count($data)==0){
			$data = $_GET;			
		}
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				//$filename = "BKM".date("YmdHis");
				#cek duplikasi nama file
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."'";
				$res=fetchData($str);
				if(count($res)>0){
					throw new PDOException("Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('".$param['notransaksi']."','".$filename."','".$filetype."','BKM','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					$owlPDO->exec($str);
					file_put_contents($path.$filename,$file_tmpname);
				}else{
					throw new PDOException("Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
				if (!file_exists($path.$filename)) {
					throw new PDOException("Upload file gagal.");
				}
			}
		}else{
			throw new PDOException("Upload file gagal.");
		}
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();
	}
	break;
	case 'loadfiles':
		$str= "select * from ".$dbname.".kebun_aktifitas where notransaksi = '".$param['notransaksi']."'";
		$res= fetchData($str);
		$jurnal = $res[0]['jurnal'];
		
		$no = 0;
		$tab= "";
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
		$res= fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
				if($jurnal==0){					
					$tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
					
					$tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" ></td>";
				}else{
					$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				}
				$tab.="</tr>";
			}
		}
		echo $tab;
	break;
	case 'deletefile':
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			#sementara tidak boleh ada unlink
			//unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'viewfile':
		$tab="";
		$str= "select * from ".$dbname.".listfileupload where id = '".$param['idfile']."'";
		$res= fetchData($str);
		if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}
		
		if($res[0]['formaticon']=='.pdf'){
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:100%;height:97%;' type='application/pdf'>";
		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."'>";
		}
		
		echo $tab;
	break;	
	case'postingabsensi':
	try {
		$owlPDO->beginTransaction();
		
		$queryH= selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".$param['notransaksi']."'");
		$dataH = fetchData($queryH);
		$kodeorg= $dataH[0]['kodeorg'];
		$tanggal= $dataH[0]['tanggal'];
		$nobkm  = $dataH[0]['nobkm'];
		
		// $str = "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and kriteriaefil='BKM'";
		// $res = fetchData($str);
		// if(count($res)==0){
		// 	throw new PDOException("Silahkan upload file pendukung terlebih dahulu sebelum melakukan posting.");
		// }
		
		$arrUpload = array();
		if(!empty($dataH[0]['nikmandor'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor'];
		if(!empty($dataH[0]['nikmandor1'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor1'];
		// if(!empty($dataH[0]['nikasisten'])) $arrUpload[]['nik'] = $dataH[0]['nikasisten'];
		if(!empty($dataH[0]['keranimuat'])) $arrUpload[]['nik'] = $dataH[0]['keranimuat'];
		
		$str = "select * from ".$dbname.".sdm_absensidt where norefrensi = '".$param['notransaksi']."' and nobkm='".$nobkm."'";
		$res = fetchData($str);
		foreach($res as $row){
			$arrUpload[]['nik'] = $row['karyawanid'];
		}
		
		
		#query pengecekan apakah FP aktif / tidak
		$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".$kodeorg."' and tanggal<='".$tanggal."'";
		$res = fetchData($str);
		$statusfp    = $res[0]['status'];//1 aktif,0 tidak
		$tipevalidasi= $res[0]['tipevalidasi'];
		$detailexp   = explode(",",$res[0]['detailvalidasi']);
		foreach($detailexp as $vald){
			$detval[$vald]=$vald;
		}

		if($statusfp==1){
			validasifpfull($tipevalidasi,$detval,'BKM',$arrUpload,$tanggal,'1');
		} else {
			if ($statusfp == '') {
				exit("Warning: Aktivasi Fingerprint belum ada<br>
						Silakan setup di menu SDM > SETUP > Aktivasi Fingerprint"
				);
			}
		}

		$strupd=" update ".$dbname.".kebun_aktifitas set jurnal='1' where notransaksi='".$param['notransaksi']."'";
		$owlPDO->exec($strupd);			
		
		
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		
	break;

	// case 'insertDetails':
	// 	// Cek dulu datanya ada atau gak kebun_prestasi_detail 
	// 	$cekPres = selectQuery($dbname,"kebun_prestasi_detail","*","notransaksi='".$param['notransaksi']."'");
	// 	$rcPres = fetchData($cekPres);
	// 	$countPres = count($rcPres);
	// 	// Jika ada di delete dulu datanya
	// 	if ($countPres > 0) {
	// 		$delPres = deleteQuery($dbname,"kebun_prestasi_detail","notransaksi='".$param['notransaksi']."'");
	// 		try {
	// 			$owlPDO->exec($delPres);
	// 		} catch (PDOException $e) {
	// 			print " Gagal  !: " . $e->getMessage() . "\n"; die();
	// 		}
	// 	}

	// 	// Cek dulu datanya ada atau gak kebun_pakaimaterial_detail
	// 	$cekMatr = selectQuery($dbname,"kebun_pakaimaterial_detail","*","notransaksi='".$param['notransaksi']."'");
	// 	$rcMatr = fetchData($cekMatr);
	// 	$countMatr = count($rcMatr);
	// 	// Jika ada di delete dulu datanya
	// 	if ($countMatr > 0) {
	// 		$delMatr = deleteQuery($dbname,"kebun_pakaimaterial_detail","notransaksi='".$param['notransaksi']."'");
	// 		try {
	// 			$owlPDO->exec($delMatr);
	// 		} catch (PDOException $e) {
	// 			print " Gagal  !: " . $e->getMessage() . "\n"; die();
	// 		}
	// 	}

	// 	// Cek dulu datanya ada atau gak kebun_kehadiran_detail
	// 	$cekHadir = selectQuery($dbname,"kebun_kehadiran_detail","*","notransaksi='".$param['notransaksi']."'");
	// 	$rcHadir = fetchData($cekHadir);
	// 	$countHadir = count($rcHadir);
	// 	// Jika ada di delete dulu datanya
	// 	if ($countHadir > 0) {
	// 		$delHadir = deleteQuery($dbname,"kebun_kehadiran_detail","notransaksi='".$param['notransaksi']."'");
	// 		try {
	// 			$owlPDO->exec($delHadir);
	// 		} catch (PDOException $e) {
	// 			print " Gagal  !: " . $e->getMessage() . "\n"; die();
	// 		}
	// 	}

	// 	try {
	// 		$owlPDO->beginTransaction();

	// 		foreach ($param['notrans_pres'] as $key => $notrans) {
	// 			$dtPres = array(
	// 				'notransaksi'	=> $notrans,
	// 				'nobkm'			=> $param['nobkm_pres'][$key],
	// 				'nourut'		=> $param['nourut_pres'][$key],
	// 				'nikpemel'		=> $param['nik_pres'][$key],
	// 				'kodekegiatan'	=> $param['keg_pres'][$key],
	// 				'indukblok'		=> $param['indukblok_pres'][$key],
	// 				'kodeorg'		=> $param['blokkecil_pres'][$key],
	// 				'hasilkerja'	=> $param['hasilkerja_pres'][$key],
	// 				'jumlahhk'		=> $param['hk_pres'][$key],
	// 				'kodesegment'	=> "0000000001",
	// 				"flag"			=> "0",
	// 			);
				
	// 			$cols = array();
	// 			foreach($dtPres as $key=>$row) {
	// 					$cols[] = $key;
	// 			}
				
	// 			$qInsPres = insertQuery($dbname,"kebun_prestasi_detail",$dtPres,$cols);
	// 			$owlPDO->exec($qInsPres);
	// 		}
			
	// 		foreach ($param['notrans_matr'] as $key => $notransm) {
	// 			$dtMatr = array(
	// 				'notransaksi'	=> $notransm,
	// 				'indukblok'		=> $param['indukblok_matr'][$key],
	// 				'kodekegiatan'	=> $param['keg_matr'][$key],
	// 				'kodeorg'		=> $param['blokkecil_matr'][$key],
	// 				'kodebarang'	=> $param['kodebarang_matr'][$key],
	// 				'kwantitas'		=> $param['jmlbrg_matr'][$key],
	// 				'kwantitasha'	=> $param['jmlha_matr'][$key],
	// 				'kodegudang'	=> $param['kodegudang_matr'][$key],
	// 			);

	// 			$cols = array();
	// 			foreach($dtMatr as $key=>$row) {
	// 					$cols[] = $key;
	// 			}

	// 			$qInsMatr = insertQuery($dbname,"kebun_pakaimaterial_detail",$dtMatr,$cols);
	// 			$owlPDO->exec($qInsMatr);
	// 		}

	// 		foreach ($param['notrans_pres'] as $key => $notrans_hadir) {
	// 			$dtHadir = array(
	// 				'notransaksi'	=> $notrans_hadir,
	// 				'nik'			=> $param['nik_pres'][$key],
	// 				'nourut'		=> $param['nourut_pres'][$key],
	// 				'jhk'			=> $param['hk_pres'][$key],
	// 				'umr'			=> $param['umr_pres'][$key],
	// 				'insentif'		=> $param['premi_pres'][$key],
	// 				'hasilkerja'	=> $param['hasilkerja_pres'][$key],
	// 			);
				
	// 			$cols = array();
	// 			foreach($dtHadir as $key=>$row) {
	// 					$cols[] = $key;
	// 			}
				
	// 			$qInsHadir = insertQuery($dbname,"kebun_kehadiran_detail",$dtHadir,$cols);
	// 			$owlPDO->exec($qInsHadir);
	// 		}

	// 		$owlPDO->commit();
	// 	} catch (PDOException $e) {
	// 		$owlPDO->rollback();
	// 		echo "Error, " . addslashes($e->getMessage());die();
	// 	}
	// break;

	case 'validasiVerifikasi':
		#cek apakah 1 no transaksi terdiri dari beberapa keg dan blok
		$str = "SELECT * FROM " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."'"; #exit('error'.$str);
		$ceckD = fetchData($str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$dataX[$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']]=$bar['kodeorg'];
		}


		## Untuk validasi FP agar muncul semua karyawannya
		
		$queryFP = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".$param['notransaksi']."'");
		$dataFP = fetchData($queryFP);


		# Absensi
		$queryAbsFP = "SELECT a.jhk,a.umr,a.insentif,a.penalty,a.nik FROM " . $dbname . ".kebun_kehadiran a 
		left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi and a.nik=b.nikpemel and a.nourut=b.nourut 
		where a.notransaksi='".$param['notransaksi']."' ";
		$dataAbsFP = fetchData($queryAbsFP);


		#=== Cek if Upload Absensi ===
		$arrUploadFP = array();
		if(!empty($dataFP[0]['nikmandor'])) $arrUploadFP[]['nik'] = $dataFP[0]['nikmandor'];
		if(!empty($dataFP[0]['nikmandor1'])) $arrUploadFP[]['nik'] = $dataFP[0]['nikmandor1'];
		// if(!empty($dataFP[0]['nikasisten'])) $arrUploadFP[]['nik'] = $dataFP[0]['nikasisten'];
		if(!empty($dataFP[0]['keranimuat'])) $arrUploadFP[]['nik'] = $dataFP[0]['keranimuat'];
		foreach($dataAbsFP as $row){
			$arrUploadFP[]['nik'] = $row['nik'];
		}


		#query pengecekan apakah FP aktif / tidak
		$statusfp ='0'; $tipevalidasi = "";
		$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".$dataFP[0]['kodeorg']."' and tanggal <= '".$dataFP[0]['tanggal']."'";
		$res = fetchData($str);
		$statusfp    = $res[0]['status'];//1 aktif,0 tidak
		$tipevalidasi= $res[0]['tipevalidasi'];
		$detailexp   = explode(",",$res[0]['detailvalidasi']);
		foreach($detailexp as $vald){
			$detval[$vald]=$vald;
		}
		
		if($statusfp==1){
			validasifpfull($tipevalidasi,$detval,'BKM',$arrUploadFP,$dataFP[0]['tanggal'],'1');
		} else {
			if ($statusfp == '') {
				exit("Warning: Aktivasi Fingerprint belum ada<br>
						Silakan setup di menu SDM > SETUP > Aktivasi Fingerprint"
				);
			}
		}

		## End validasi FP Full



		$errCekPnn = "";
		$errCekMtrl="";
		$noCekMtrl=0;
		foreach($dataX as $notranX => $valKegX){
			foreach($valKegX as $kegiatanX => $valBlokX){
				foreach($valBlokX as $blokX){
					# Header
					$qBlok = selectQuery($dbname,'setup_blok','statusblok',"kodeorg='".$blokX."'");
					$resBlok = fetchData($qBlok);

					$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".$param['notransaksi']."'");
					$dataH = fetchData($queryH);

					# Prestasi
					$queryD="select notransaksi,kodekegiatan,kodeorg,tahuntanam, sum(hasilkerja) as hasilkerja, sum(jumlahhk) as jumlahhk, sum(upahkerja) as upahkerja, 
					sum(upahpenalty) as upahpenalty, sum(upahpremi) as upahpremi, sum(upahpremilebihbasis) as upahpremilebihbasis, sum(premibasis) as premibasis, 
					sum(umr) as umr, sum(rupiahpenalty) as rupiahpenalty, kodesegment from ".$dbname.".kebun_prestasi 
					where notransaksi='".$notranX."' and kodekegiatan='".$kegiatanX."' and kodeorg='".$blokX."' group by notransaksi, kodekegiatan, kodeorg";
					$dataD = fetchData($queryD);

					
					@$arrkegx[$resBlok[0]['statusblok']][$blokX][$kegiatanX]+=$dataD[0]['hasilkerja'];
					# Absensi
					$queryAbs = "SELECT a.jhk,a.umr,a.insentif,a.penalty,a.nik FROM " . $dbname . ".kebun_kehadiran a 
					left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi and a.nik=b.nikpemel and a.nourut=b.nourut 
					where a.notransaksi='".$notranX."' and b.kodekegiatan='".$kegiatanX."' and b.kodeorg='".$blokX."'";
					$dataAbs = fetchData($queryAbs);

					#=== Cek if posted ===
					$error0 = "";
					if($dataH[0]['jurnal']==1) {
						$error0 .= $_SESSION['lang']['errisposted'];
					}
					if($error0!='') {
						exit("Warning: ".$error0);
					}

					#=== Cek if data not exist ===
					$error1 = "";
					if(count($dataH)==0) {
						$error1 .= $_SESSION['lang']['errheadernotexist']."\n";
					}
					if(count($dataD)==0) {
						$error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
					}
					if($error1!='') {
						exit("Warning: ".$error1);
					}

					#=== Hitung Cost dari Absensi (Perawatan) ===
					$costRawat=$costRawatPre=$penaltyRawat=$totalHk = 0;
					$costRawatNik=$costRawatPreNik=$penaltyRawatNik=$totalHkNik = array();
					if(!empty($dataAbs)) {
						$noCekPnn = 0;
						foreach($dataAbs as $row) {
							$costRawat += $row['umr'];
							$costRawatPre += $row['insentif'];
							$penaltyRawat += $row['penalty'];
							$totalHk += $row['jhk'];
							
							@$costRawatNik[$row['nik']] += $row['umr'];
							@$costRawatPreNik[$row['nik']] += $row['insentif'];
							@$penaltyRawatNik[$row['nik']] += $row['penalty'];
							@$totalHkNik[$row['nik']] += $row['jhk'];

							//  Cek Perawatan
							//  Jika karyawan ada pekerjaan panen, maka HK tidak boleh diinput
							$qAbs = selectQuery($dbname,'kebun_prestasi_vs_hk','SUM(hasilkerja) as jjg',
							"karyawanid='".$row['nik']."' and tanggal='".$dataH[0]['tanggal']."'");
							$resAbs = fetchData($qAbs);
							//  Hasil Jjg
							$cekPanen = $resAbs[0]['jjg'];
							
							// Jika Jjg ada dan ada HK di bkm rawat maka munculkan validasi
							if(floatval($cekPanen) > 0 and $totalHkNik[$row['nik']] > 0) {
								$noCekPnn++;
								$errCekPnn .= "".$noCekPnn.". ".getKary($row['nik'],'nik')." - ".getNamaKaryawan($row['nik'])."<br>";
							}
						}
					}
					if ($noCekPnn > 0) {
						exit("Warning List Karyawan:<br>". $errCekPnn ."<br>Sudah terdaftar di kegiatan panen, silahkan kosongkan Jumlah HK untuk melanjutkan.");
					}

					#=== Cek if HK belum sama ===
					$totalHk=round($totalHk,2);                             // diround hingga 2 desimal
					$dataD[0]['jumlahhk']=round($dataD[0]['jumlahhk'],2);   // diround hingga 2 desimal
					$qwe=$totalHk-$dataD[0]['jumlahhk'];
					if($totalHk!=$dataD[0]['jumlahhk']) {
						// throw new PDOException("HK Prestasi belum teralokasi dengan lengkap ".$qwe."");
						exit("Warning: HK Prestasi belum teralokasi dengan lengkap ".$qwe."xxx".$totalHk."xx".$dataD[0]['jumlahhk']."");
					}

					foreach ($dataD as $details) {
						#=== cek apakah di setup ada materialnya ===
						# Ambil data dari  kebun_pakaimaterial
						$queryM = selectQuery($dbname,'kebun_pakaimaterial',"*","notransaksi='".$notranX."' and kodekegiatan='".$details['kodekegiatan']."' and kodeorg='".$details['kodeorg']."'");
						$dataM = fetchData($queryM);
	
						# Cek data di master kegiatan
						$queryK = selectQuery($dbname,'setup_kegiatannorma',"*","kodekegiatan='".$details['kodekegiatan']."'");
						$dataK = fetchData($queryK);
						if(empty($dataM) and !empty($dataK)){
							$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$details['kodekegiatan']."'");
							// exit("Warning: Kegiatan ".$nmkeg[$details['kodekegiatan']].", blok ".$details['kodeorg']." harus menggunakan material.");
							$noCekMtrl++;
							$errCekMtrl .= "".$noCekMtrl.". ".$details['kodekegiatan']." (".$nmkeg[$details['kodekegiatan']]."), blok ".$details['kodeorg']." (".getIndukBlok($details['kodeorg']).")<br>";
						}
					}
					
					if ($noCekMtrl > 0) {
						exit("Warning List Kegiatan:<br>".$errCekMtrl."<br>harus menggunakan material.");
					}

					#=== Cek if Upload Absensi ===
					$countUpload = 0;
					$countUpload = "";
					$arrUpload = array();
					if(!empty($dataH[0]['nikmandor'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor'];
					if(!empty($dataH[0]['nikmandor1'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor1'];
					// if(!empty($dataH[0]['nikasisten'])) $arrUpload[]['nik'] = $dataH[0]['nikasisten'];
					if(!empty($dataH[0]['keranimuat'])) $arrUpload[]['nik'] = $dataH[0]['keranimuat'];
					foreach($dataAbs as $row){
						$arrUpload[]['nik'] = $row['nik'];
					}


					#query pengecekan apakah FP aktif / tidak
					$statusfp ='0'; $tipevalidasi = "";
					$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".$dataH[0]['kodeorg']."' and tanggal <= '".$dataH[0]['tanggal']."'";
					$res = fetchData($str);
					$statusfp    = $res[0]['status'];//1 aktif,0 tidak
					$tipevalidasi= $res[0]['tipevalidasi'];
					$detailexp   = explode(",",$res[0]['detailvalidasi']);
					foreach($detailexp as $vald){
						$detval[$vald]=$vald;
					}
					
					if($statusfp==1){
						validasifpfull($tipevalidasi,$detval,'BKM',$arrUpload,$dataH[0]['tanggal'],'1');
					} else {
						if ($statusfp == '') {
							exit("Warning: Aktivasi Fingerprint belum ada<br>
									Silakan setup di menu SDM > SETUP > Aktivasi Fingerprint"
							);
						}
					}
				}
			}
		}

		#cek apakah 1 no transaksi terdiri dari beberapa keg dan blok
		$str3 = "SELECT * FROM " . $dbname . ".kebun_pakaimaterial where notransaksi='".$param['notransaksi']."'"; #exit('error'.$str3);
		$res3 = $owlPDO->query($str3) or die(print " Gagal: " . PDOException::getMessage());
		$res3->setFetchMode(PDO::FETCH_ASSOC);
		$adamat=0;
		while ($bar3 = $res3->fetch()) {
			$dataXX[$bar3['notransaksi']][$bar3['kodekegiatan']][$bar3['kodeorg']][$bar3['kodebarang']]=$bar3['kodebarang'];
			@$adamat+=1;
		}
		if($adamat!=''){
			foreach($dataXX as $notranXX => $valKegXX){
				foreach($valKegXX as $kegiatanXX => $valBlokXX){
					foreach($valBlokXX as $blokXX => $valBrgXX){
						foreach($valBrgXX as $barangXX){
							$brg=Array();
							$gud=Array();
							$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
							left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
							where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!='' 
							and a.kodekegiatan='".$kegiatanXX."' and a.kodeorg='".$blokXX."' and a.kodebarang='".$barangXX."'";
							$resa=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$resa->setFetchMode(PDO::FETCH_OBJ);
							#ambil saldo dan harga rata
							while($barf=$resa->fetch()){
								#ambil periode akuntansi masing-masing gudang
								$strd="select periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$barf->kodegudang."' and tutupbuku=0";
								$resd=$owlPDO->query($strd) or die(print " Gagal: ".PDOException::getMessage());
								$resd->setFetchMode(PDO::FETCH_OBJ);
								while($bard=$resd->fetch()){
									$periode[$barf->kodegudang]=$bard->periode;
								}
								
								$stru="select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where kodegudang='".$barf->kodegudang."' and kodebarang='".$barf->kodebarang."' and periode='".$periode[$barf->kodegudang]."'"; 
								// exit('warning:'.$stru);
								$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
								$resu->setFetchMode(PDO::FETCH_OBJ);
								$saldo[$barf->kodegudang][$barf->kodebarang]=0;
								$harga[$barf->kodegudang][$barf->kodebarang]=0;
								$kodegudangxz[$blokXX]=$barf->kodegudang;
								while($baru=$resu->fetch()){
									$saldo[$barf->kodegudang][$barf->kodebarang]=$baru->saldoakhirqty;
									// $harga[$barf->kodegudang][$barf->kodebarang]=$baru->hargarata;
									$xkeluar[$barf->kodegudang][$barf->kodebarang]=$baru->qtykeluarxharga;
									$qtykeluar[$barf->kodegudang][$barf->kodebarang]=$baru->qtykeluar;
									$nilaisaldoakhir[$barf->kodegudang][$barf->kodebarang]=$baru->nilaisaldoakhir;
									// ROUND2021
									$harga[$barf->kodegudang][$barf->kodebarang]=$baru->nilaisaldoakhir/$baru->saldoakhirqty;
								}
							}

							$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
							left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
							where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!='' 
							and a.kodekegiatan='".$kegiatanXX."' and a.kodeorg='".$blokXX."'";
							$res = fetchdata($str);
							foreach($res as $bar){
								$brg[$bar['kodegudang']][$bar['kodebarang']]=$bar['kodebarang'];
								$gud[$bar['kodegudang']]=$bar['kodegudang'];     
							}

							#periksa apakah saldo mencukupi:
							$str="select a.notransaksi, a.kodebarang, a.kodegudang, sum(a.kwantitas) as kwantitas,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
							left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
							where a.notransaksi='".$notranXX."' and a.kodegudang='".$kodegudangxz[$blokXX]."' and a.kodebarang='".$barangXX."' group by a.kodebarang, a.notransaksi,a.kodegudang 
							";
							$errsaldo='';
							$resku=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$resku->setFetchMode(PDO::FETCH_OBJ);
							while($barf=$resku->fetch()){
								# cek saldo apakah cukup, di round desimal 2 untuk ngatasin mysql sum problem 0.88 => 0.8800000000000001 
								$jumlah[$barf->kodegudang][$barf->kodebarang]=round($barf->kwantitas,5);
							}
							
							$errSal='';
							foreach($gud as $keygud=>$valgud){
								foreach($brg[$valgud] as $keybrg=>$valbrg){
									$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$valbrg."'");
									#pastikan tidak ada minus diantara kita
									if($saldo[$valgud][$valbrg]-$jumlah[$valgud][$valbrg]<0){
										// exit("Warning: Barang:".$valbrg."<br>Saldo:".$saldo[$valgud][$valbrg]."<br>Jumlah:".$jumlah[$valgud][$valbrg]);
										exit("Warning: Nilai kuantitas saldo ".$valbrg." (". getNamaBrg($valbrg) .") di ".$valgud." (".getNamaOrg($valgud).") tidak mencukupi.<br>
											Saldo Gudang:".$saldo[$valgud][$valbrg]."<br>Jumlah Barang:".$jumlah[$valgud][$valbrg]
										);
									}
									
									$xsisa=round($nilaisaldoakhir[$valgud][$valbrg]-$rpmatperbarang[$valgud][$valbrg]);
									if($xsisa<0){
										exit("Warning: ".$xsisa." Nilai rupiah saldo tidak mencukupi.\nRupiah saldo : ".number_format($nilaisaldoakhir[$valgud][$valbrg])."\nRupiah pakai : ".number_format($rpmatperbarang[$valgud][$valbrg])."");
									}
								}
							}
						}
					}
				}
			}
		}
	break;

	case 'detailVerifikasi':
		$theme = $_SESSION['theme'];
    	if ($theme == 'skyblue' || $theme == '') {
    		$gen = 'generic.css';
    	} else if ($theme == 'red') {
    		$gen = 'genericRed.css';
    	} else {
    		$gen = 'genericGray.css';
    	}

		$tab = "";
    	$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
		
		$tab .= "<b style='font-size:14px;margin-left:5px;'>DETAIL VERIFIKASI</b> <br><br>";
		$tab.="
    		<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100%>
    		<thead>";
			$tab.="<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center>".$_SESSION['lang']['namakegiatan']."</td>
				<td align=center>".$_SESSION['lang']['hasilkerja2']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
    		</tr>";
			$tab.="</thead>";

			$tab.="<tbody>";
			$no = 0;
			
			$optstats = "";
			$arrstats = array("1" => "Berhasil", "0" => "Tidak Berhasil");
			foreach ($arrstats as $key => $val) {
				$optstats .= "<option value=".$key.">".$val."</option>";
			}

			// Query Datanya
			$str = "SELECT notransaksi, SUM(hasilkerja) AS hasilkerja, kodekegiatan FROM $dbname.kebun_prestasi
					WHERE notransaksi='".$notransaksi."' GROUP BY kodekegiatan";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$bar['notransaksi']."</td>";
				$tab.="<td hidden id='kegiatandt_".$no."'>".$bar['kodekegiatan']."</td>";
				$tab.="<td>".getNamaKeg($bar['kodekegiatan'])."</td>";
				$tab.="<td align=right style='width:70px;'>
					<input type=hidden id='oldHasilKerja_".$no."' value='".$bar['hasilkerja']."'>
					<input style='width:70px;' type=text class=myinputtextnumber id='hasilkerja_".$no."' value='".$bar['hasilkerja']."' disabled>
				</td>";
				$tab.="<td align=center>
					<select style=\"width:100px;\" onchange=\"getSuksesDt('".$no."');\" id='optsukses".$no."'>".$optstats."</select>				
				</td>";
				$tab.="</tr>";
			}
			$tab.="</tbody>";
			$tab.="</table>";
			
			$tab.="<input type=hidden id='getRowDt' value='".$no."'>";
			
			$tab.="<br>";
			$tab.="<button class=mybutton onclick=\"insertVerifikasi('".$notransaksi."','".$no."')\" id='simpanverif'>".$_SESSION['lang']['save']."</button>";
		echo $tab;
	break;

	case 'insertVerifikasi':
		$cekVerifikasi = selectQuery($dbname,"kebun_verifikasibkm","nourut","notransaksi='".$param["notransaksi"]."'","nourut desc limit 1");
		$resVerifikasi = fetchData($cekVerifikasi);
		$countVerifikasi = $resVerifikasi[0]['nourut'];

		if ($countVerifikasi == 0) {
			$nourutver = 1;
		} else {
			$nourutver = $countVerifikasi;
		}
		
		if ($countVerifikasi > 0) {
			$nourutver += 1;
		}

		$insVer = array(
			'notransaksi'		=> $param['notransaksi'],
			'nourut'			=> $nourutver,
			'statusverifikasi'	=> '1',
			'verifiedby'		=> $_SESSION['standard']['userid'],
			'updatetime'		=> date("Y-m-d H:i:s")
		);

		$cols = array();
		foreach ($insVer as $key => $row) {
			$cols[] = $key;
		}
		$qInsVer = insertQuery($dbname,"kebun_verifikasibkm",$insVer,$cols);

		try {
			$owlPDO->exec($qInsVer);
		} catch (PDOException $e) {
			print " Error  !: " . $e->getMessage() . "\n"; die();
		}
		
		try {
			$sCekKgt = selectQuery($dbname,"kebun_statuskegiatan","*","notransaksi='".$notransaksi."'");
			$rCekKgt = fetchData($sCekKgt);
			if (count($rCekKgt) > 0) {
				$sDel = deleteQuery($dbname,"kebun_statuskegiatan","notransaksi='".$notransaksi."'");
				$owlPDO->exec($sDel);
			}
			
			for ($getRow=1; $getRow <= $param['row'];) { 
				// echo $param['row'];
				$insDtKg = array(
					'notransaksi'	=> $notransaksi,
					'kodekegiatan'	=> $param['kodekegiatan_'.$getRow],
					'hasilkerja'	=> $param['hasilkerja_'.$getRow],
					'status'		=> $param['status_'.$getRow]
				);
				$cols = array();
				foreach ($insDtKg as $key => $row) {
					$cols[] = $key;
				}
				$qInsVerDt = insertQuery($dbname,"kebun_statuskegiatan",$insDtKg,$cols);
				$owlPDO->exec($qInsVerDt);
				
				$getRow++;
			}
		} catch (PDOException $e) {
			print " Error  !: " . $e->getMessage() . "\n"; die();
		}
	break;
	
}

function cekPrestasi($param) {
	global $dbname;
	global $owlPDO;
		
	$tgl=explode('/',$param['notransaksi']);
	$tgl=$tgl[0];
	$thn=substr($tgl,0,4);
	$bln=substr($tgl,4,2);
	
	$periode = $thn."-".$bln;
	
	$tanggal=tanggalsystemn(checkPostGet('tgl', ''));
	
	#============== Validasi SESSION Status ==========+=========
	stsawal($param);
	#============ End Validasi SESSION Status ==================
	
	
	#cek HK perhari maksimal 1
	# Ambil nomor urut kary
	$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodeorg='".@$param['blok']."' and kodekegiatan='".@$param['kegiatan']."'";
	$res=fetchData($str);
	@$nourut=$res[0]['nourut'];
			
	if($param['method']=='insert'){
		$str = "select a.notransaksi,jhk from ".$dbname.".kebun_kehadiran a
				left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where nik='".$param['karyawanid']."' and tanggal='".$tgl."'";
	} else if($param['method']=='update'){
		$str = "select a.notransaksi,jhk from ".$dbname.".kebun_kehadiran a
				left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where nik='".$param['karyawanid']."' and tanggal='".$tgl."' and nourut!='".$nourut."'";
	}else{
		$str = "select a.notransaksi,jhk from ".$dbname.".kebun_kehadiran a
				left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where nik='".$param['karyawanid']."' and tanggal='".$tgl."'";
	}
	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$jlhhk=0;
		$trans='';
		while($bar=$res->fetch()){ 
			$jlhhk+=$bar['jhk'];
			$trans.="No => ".$bar['notransaksi']." => ".$bar['jhk']." HK<br>";
		}
		
		if(floatval($param['jhk'])+$jlhhk>1){
			throw new PDOException("Jumlah HK karyawan lebih dari 1, HK yang sudah tersimpan sebesar = ".$jlhhk." HK<br><br> ".$trans."");
		}		
			
	$jumtrans = 0;
	#cek mandor
	$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		
	#cek mandor1
	$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor1='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		
	#cek kerani
	$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where keranimuat='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		
	#cek nikasisten
	$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikasisten='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		
	if(@$jumtrans>0 && $param['isHkHead'] == 1){
		// throw new PDOException("Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani");
		throw new PDOException("Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani Atau Jumlah HK sudah 1");
	}

	# Cek Perawatan
	# Jika sudah ada HK di perawatan 
	# Jika karyawan ada pekerjaan panen dan perawatan, namun ada JJG maka munculkan validasi
	# Jika karyawan ada pekerjaan panen dan perawatan, namun tidak ada JJG maka lewati validasi
	$qAbs = selectQuery($dbname,'kebun_prestasi_vs_hk','SUM(hasilkerja) as jjg',
								"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$cekPanen = $resAbs[0]['jjg'];
	
	if(floatval($cekPanen) > 0 and $param['jhk']>'0') {
		throw new PDOException("Karyawan sudah terdaftar di kegiatan panen, silahkan kosongkan Jumlah HK untuk melanjutkan.");
	}
	
	#cek di vhc - kegiatan traksi
	$qAbs = selectQuery($dbname,'vhc_runhk','sum(upah) as jhk',
			"idkaryawan='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhkvhc = $resAbs[0]['jhk'];
	
	if(floatval($jmlhkvhc)!='0') {
		throw new PDOException("Karyawan sudah terdaftar di kegiatan traksi");
	}
	
	#cek di SDM
	if($param['method']=='updateabsensi'){
		$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."' and nobkm!='".$param['nobkm']."' ";
		$res = fetchData($str);
		if(count($res)>'0') {
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM dengan nomor transaksi : ".$res[0]['norefrensi'].".");
		}
	}else{
		$qAbs = selectQuery($dbname,'sdm_absensidt_vw','sum(nilaihk) as jhk',
				"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
		$resAbs = fetchData($qAbs);
		$jmlhksdm = $resAbs[0]['jhk'];
		if(floatval($jmlhksdm)!='0' and $param['jhk']>'0') {
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM.");
		}
	}
	
	$luasttlblok = 0;
	$pokokttlblok = 0;
	
	$pilluas = makeOption($dbname,"setup_kegiatan","kodekegiatan,pilihanluas");

	$str = "select * from " . $dbname . ".setup_blok where indukblok='".$param['blok']."' and statusblok in (select kelompok from ".$dbname.".setup_kegiatan where kodekegiatan='".$param['kegiatan']."') AND status='A'"; 
	$res = fetchData($str);
	foreach($res as $val){
		if ($pilluas[$param['kegiatan']] == 0) {
			$luasttlblok += ($val['luasareaproduktif']+$val['luasareanonproduktif']);
		} elseif ($pilluas[$param['kegiatan']] == 1) {
			$luasttlblok += ($val['luasbloking']);
		} elseif ($pilluas[$param['kegiatan']] == 2) {
			$luasttlblok +=($val['lc']);
		} else {
			$luasttlblok +=($val['luasareaproduktif']);
		}

		$pokokttlblok+=$val['jumlahpokok'];
	}

	$satsetup = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$param['kegiatan']."'");
	
	$hasilkerja=0;$notrhasil="";
	$hasilkerjaedit=0;
	if($param['method']=='update'){
		$str = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_prestasi where kodeorg='".$param['blok']."' and notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodekegiatan='".$param['kegiatan']."'"; 
		$res = fetchData($str);
		foreach($res as $val){
			$hasilkerjaedit=number_format($val['hasilkerja'],2);
		}
	}
	
	$notrhasil.="\nTransaksi saat ini = ".@number_format($param['prestasi'],2)." ".$satsetup[$param['kegiatan']]."\n";
	$str = "select sum(hasilkerja) as hasilkerja,notransaksi,substr(notransaksi,1,8) from " . $dbname . ".kebun_prestasi where kodeorg='".$param['blok']."' and substr(notransaksi,1,8) between '".seminggulalu($tgl)."' and '".$tgl."' and kodekegiatan='".$param['kegiatan']."'  group by notransaksi  order by notransaksi desc"; 
	$res = fetchData($str);
	foreach($res as $val){
		$hasilkerja+=$val['hasilkerja'];
		if($val['notransaksi']==$param['notransaksi']){
			$notrhasil.=$val['notransaksi']." = ".number_format($val['hasilkerja']-$hasilkerjaedit,2)." ".$satsetup[$param['kegiatan']]."\n";
		}else{			
			$notrhasil.=$val['notransaksi']." = ".number_format($val['hasilkerja'],2)." ".$satsetup[$param['kegiatan']]."\n";
		}
	}

	if($param['kegiatan']!='621010302' and $param['kegiatan']!='126010802' and $param['kegiatan']!='126090701'){		
		if(strtolower($satsetup[$param['kegiatan']])=='ha'){
			$a=number_format(($hasilkerja+$param['prestasi'])-$hasilkerjaedit,2);
			$a=str_replace(",","",$a);
			$b=number_format($luasttlblok,2);
			$b=str_replace(",","",$b);
			if($a>$b){
				throw new PDOException("Luas dikerjakan sudah melebihi luas blok,<br>Luas blok : ".$b." HA<br>Luas dikerjakan : ".$a." HA<br>".$notrhasil."");
			}
		}elseif(strtolower($satsetup[$param['kegiatan']])=='pokok' or strtolower($satsetup[$param['kegiatan']])=='pkk' and substr($param['kegiatan'],0,3) !='126'){
			$a=number_format(($hasilkerja+$param['prestasi'])-$hasilkerjaedit,2);
			$a=str_replace(",","",$a);
			$b=number_format($pokokttlblok,2);
			$b=str_replace(",","",$b);
			// if($a>$b){
			// 	throw new PDOException("Pokok dikerjakan sudah melebihi jumlah pokok blok,<br>Pokok blok : ".$b." PKK<br>Pokok dikerjakan : ".$a." PKK<br>".$notrhasil."");
			// }
		}
	}
}

function cekmaxhkBln($karyawanid,$tgl,$hknew,$hkold,$mode='new',$exit='1') {
	global $dbname;
	global $owlPDO;
	
	#exit
	# 0 = throw new PDOException("exit");
	# 1 = exit('Error');
	if($hkold==''){$hkold='0';}
	
	$arrtgl  = explode("-",$tgl);
	$thn     = $arrtgl[0];
	$bln     = $arrtgl[1];
	$periode = $thn."-".$bln;
	$param['kodeorg'] = getKary($karyawanid,'lokasitugas');
	$param['karyawanid'] = $karyawanid;
	
	#HK BHL tidak boleh jumlahnya >20 hari kerja
	$totalhkbi=$maxhk= 0; $result=""; $hkpres=[];
	
	#ambil unit dari parameter	
	// $str = "select * from " . $dbname . ".kebun_5maxhkkarykhl where kodeorg='".$param['kodeorg']."' and status='1' and tanggalberlaku<='".$tgl."'"; 
	// $res = fetchData($str);
	// $maxhk = $res[0]['nilai'];
	// $jenis = $res[0]['jenis'];
	// $exclude = explode(",",$res[0]['excludejabatan']);
	// $kecuali = [];;
	// foreach($exclude as $excl){
	// 	$kecuali[$excl]=$excl;
	// }
	
	$maxhk = 25;
	$jenis = "hadir";
	
	$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$param['karyawanid']."' and tahun='".$periode."' and idkomponen='1'"; 
	$Umr = fetchdata($str);
	$gapok = $Umr[0]['nilai']/25;

	$arrkarybln = array("1","2","3","5");
	if($maxhk>0 and in_array(getKary($param['karyawanid'],'tipekaryawan'),$arrkarybln)){
		#cek di kebun prestasi [pemel]
		$str = "select * from " . $dbname . ".kebun_prestasi where nikpemel='".$param['karyawanid']."' and notransaksi like '".str_replace("-","",$periode)."%' group by substr(notransaksi,1,8)";
		$res = fetchData($str);
		foreach($res as $val){
			$hkpres['BKMRAWAT']['hk']+=$val['jumlahhk'];
			$hkpres['BKMRAWAT']['hadir']+=1;
		}
		
		
		#cek mandor
		$str = "select * from ".$dbname.".kebun_aktifitas where nikmandor='".$param['karyawanid']."' and tanggal like '".$periode."%' group by tanggal";
		$res = fetchData($str);
		$hkpres['MANDOR']['hk']+=count($res);
		$hkpres['MANDOR']['hadir']+=count($res);
		#cek mandor1
		$str = "select * from ".$dbname.".kebun_aktifitas where nikmandor1='".$param['karyawanid']."' and tanggal like '".$periode."%' group by tanggal";
		$res = fetchData($str);
		$hkpres['MANDOR1']['hk']+=count($res);
		$hkpres['MANDOR1']['hadir']+=count($res);
		
		#cek kerani
		$str = "select * from ".$dbname.".kebun_aktifitas where keranimuat='".$param['karyawanid']."' and tanggal like '".$periode."%' group by tanggal";
		$res = fetchData($str);
		$hkpres['KERANI']['hk']+=count($res);
		$hkpres['KERANI']['hadir']+=count($res);
		
		#cek nikasisten
		$str = "select * from ".$dbname.".kebun_aktifitas where nikasisten='".$param['karyawanid']."' and tanggal like '".$periode."%' group by tanggal";
		$res = fetchData($str);
		$hkpres['KERANI']['hk']+=count($res);
		$hkpres['KERANI']['hadir']+=count($res);
		
		#cek di vhc - kegiatan traksi
		$str = "select * from ".$dbname.".vhc_runhk where idkaryawan='".$param['karyawanid']."' and tanggal like '".$periode."%' group by tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			if($bar['upah']>0){				
				$hkpres['TRAKSI']['hk']+=$bar['upah']/$gapok;
			}
			$hkpres['TRAKSI']['hadir']+=1;
		}
		
		$str = "select * from ".$dbname.".sdm_5absensi where status	='1'";
		$res = fetchData($str);
		foreach($res as $val){
			$kodeabsensi[$val['kodeabsen']]=$val['kelompok'];
		}
		
		#cek sdm
		$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyawanid']."' and tanggal like '".$periode."%'";
		$res = fetchData($str);
		foreach($res as $val){
			$hkpres['SDM']['hk']+=$val['hk'];
			if($kodeabsensi[$val['absensi']]=='1'){				
				$hkpres['SDM']['hadir']+=1;
			}
		}
		$rincian="";
		foreach($hkpres as $key => $val1){
			foreach($val1 as $jns => $value){
				if($jenis==$jns){
					if($value>0){
						$nomor++;
						$rincian.=$nomor.". ".strtolower($key).": ".$value."; <br>";
						$totalhkbi+=$value;
					}
				}
			}
		}

		#jika kary KHL
		if($mode=='edit'){
			$totalhk = (floatval($totalhkbi)+floatval($hknew))-floatval($hkold);
		}else{
			$totalhk = (floatval($totalhkbi)+floatval($hknew));
		}
		
		if(floatval($totalhk)>floatval($maxhk)){
			if($jenis=='hk'){
				$kehadiran="HK";
				$hari="HK";
			}
			if($jenis=='hadir'){
				$kehadiran="kehadiran";
				$hari="Hari";
			}
			
			$result = "Jumlah ".$kehadiran." selama periode ".$periode." telah melebihi batas maksimal ".$kehadiran." dalam sebulan, sebagai berikut :\n\n<br>Jumlah maksimal : ".$maxhk." ".$hari." / Bulan.\n<br>Jumlah ".$kehadiran." bulan ini : ".($totalhkbi)." ".$hari." \n<br>".trim($rincian)." ";
			if($exit=='0'){				
				throw new PDOException($result);
			}else{
				exit("Error, ".$result);
			}
		}
	}
}

function stsawal($param) {
	global $dbname;
	global $owlPDO;
	$statusblok='BKM';
	
	if($param['stsawal']!=$statusblok){
		exit("Error : SESSION anda sudah habis, silahkan reload frame atau buka ulang menu ini untuk melanjutkan.");
	}
}

function seminggulalu($tgl){
	#membuat tanggal kemarin dari parameter kiriman
	#$tgl format : 2015-12-25;
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime('- 6 day',strtotime($tgl));
	$newdate = date('Ymd', $newdate);
	return $newdate;
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
	
	// echo"<pre>";
	// print_r($param);
	// exit("error");
	
	if($param['kodeorg']==''){
		$param['kodeorg']=$_SESSION['empl']['lokasitugas'];
	}
	$start = makeOption($dbname, 'setup_periodeakuntansi', 'kodeorg,tanggalmulai',"kodeorg='".$param['kodeorg']."'");
	$optdivisi="";
	if($param['sumber']=='kebun'){			
		# Divisi
		$wh=" and induk in ('".$param['kodeorg']."')";
		$optdivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') ".$wh." order by induk, kodeorganisasi";
		$res = fetchData($str);
		$n = "";
		$z = 0;
		foreach($res as $key => $val){
			$d=$val['induk'];
			if($d!=$n){
				$z++;
				if ($z > 1) {
					$optdivisi.="</optgroup>";
				}
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optdivisi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optdivisi.="<option value=".$val['kodeorganisasi']." ".$s.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
			$n=$d;
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
	$str="select * from ".$dbname.".kebun_5asistensi where kodeorgtujuan ='".$param['kodeorg']."' and divisitujuan='".$param['divisi']."' and tanggal<='".tanggalsystemn($param['tgl'])."' and tanggalsampai>='".tanggalsystemn($param['tgl'])."' and posting='1' and (tipetrans='BKM' or tipetrans='')";
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

	$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg ='".$param['kodeorg']."' and tipe='BKM'";
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

	if(getindukPT($_SESSION['empl']['lokasitugas']) == 'PPP'){
		$tipeOrg = makeOption($dbname,"organisasi","kodeorganisasi,inti");
		if($tipeOrg[$_SESSION['empl']['lokasitugas']] == '0') {
			if($mdr1!=''){
				$getAllPlamaPerPt = makeOption($dbname,"organisasi","kodeorganisasi,induk");

				$sqlPlasma = selectQuery($dbname,"organisasi","kodeorganisasi","induk='".$getAllPlamaPerPt[$_SESSION['empl']['lokasitugas']]."' and tipe='KEBUN'");
				$resPlasma = fetchData($sqlPlasma);

				foreach($resPlasma as $v) {
					$datakaryawanSupervisiPlasma[$v['kodeorganisasi']] = $v['kodeorganisasi'];
				}

				if(count($dt)>0){
				}elseif(count($resx)>0){
					if($param['divisi']!=''){
						//$whereKary.=" and (a.subbagian='".$param['divisi']."' or a.subbagian in ('".implode("','",$divisiasal)."'))";
						$whereKary.=" and (a.lokasitugas in ('".implode("','",$datakaryawanSupervisiPlasma)."'))";
					}
				}else{
					if($param['divisi']!=''){
						//$whereKary.=" and a.subbagian='".$param['divisi']."'";
					}
					$whereKary.=" and a.lokasitugas in ('".implode("','",$datakaryawanSupervisiPlasma)."')";
				}
			}
		} else {
			$whereKary.=" and a.lokasitugas in ('".implode("','",$kdorg)."')";
			if (!$asst && !$mdr1) {
				$whereKary.=" and a.subbagian!=''";
			}
			if(count($dt)>0){
				if (!$asst) {
					$whereKary.=" and (a.subbagian='".$param['divisi']."' or a.karyawanid in ('".implode("','",$dt)."'))";
				}
			}elseif(count($resx)>0){
				if($param['divisi']!=''){
					//$whereKary.=" and (a.subbagian='".$param['divisi']."' or a.subbagian in ('".implode("','",$divisiasal)."'))";
					$whereKary.=" and (a.lokasitugas='".$param['kodeorg']."' or a.subbagian in ('".implode("','",$divisiasal)."'))";
				}
			}else{
				if($param['divisi']!=''){
					//$whereKary.=" and a.subbagian='".$param['divisi']."'";
				}
				$whereKary.=" and a.lokasitugas='".$param['kodeorg']."'";
			}
		}

	## UNTUK GROUP DMA
	}else{
		## Gini dulu urgent di kebun/ kalau sempat nanti diganti parameter aplikasi
		if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
		}
	}

	$whereKarykerani.=$whereKary;

	
	# Mandor
	$d=$n="";
	if($mdr!=''){
		$whr=" and a.kodejabatan in (".$mdr.")";
	}else{
		$whr=" and b.namajabatan like '%mandor%' and b.namajabatan not like '%mandor%1%'";
	}
	
	$qMandor = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by a.lokasitugas, b.namajabatan,a.namakaryawan asc";
	$res=fetchdata($qMandor);
	$n = $w = "";
	$z = 0;
	foreach($res as $row){
		$dkary="";
		if($row['subbagian']!=$param['divisi']){
			$dkary=" [ ".$row['subbagian']." ]";
		}
		$q=getNamaOrg($row['lokasitugas']);
		if($q!=$w){
			$z++;
			if ($z > 1) {
				$optMandor.="</optgroup>";
			}
			$optMandor.="<optgroup label='".$q."'>";
		}
		
		$d=$row['namajabatan'];
		if($d!=$n){
			$z++;
			if ($z > 1) {
				$optMandor.="</optgroup>";
			}
			$optMandor.="<optgroup label='".$d."'>";
		}
		
		if($param['nikmandor']==$row['karyawanid']){
			$optMandor.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
		}else{			
			$optMandor.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
		}

		$n=$d;
		$w=$q;
	}


	# Mandor 1
	if($mdr1!=''){
		$whr=" and a.kodejabatan in (".$mdr1.")";
	}else{
		// $whr=" and b.namajabatan like '%mandor%I%' ";
		$whr=" and a.kodejabatan = '6' ";
	}
	$qMandor1 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by a.lokasitugas,  b.namajabatan,a.namakaryawan asc";
	$n = $w = "";
	$z = 0;
	$res=fetchdata($qMandor1);
	foreach($res as $row){
		$dkary="";
		if($row['subbagian']!=$param['divisi']){
			$dkary=" [ ".$row['subbagian']." ]";
		}
		$q=getNamaOrg($row['lokasitugas']);
		if($q!=$w){
			$z++;
			if ($z > 1) {
				$optMandor1.="</optgroup>";
			}
			$optMandor1.="<optgroup label='".$q."'>";
		}
		
		$d=$row['namajabatan'];
		if($d!=$n){
			$z++;
			if ($z > 1) {
				$optMandor1.="</optgroup>";
			}
			$optMandor1.="<optgroup label='".$d."'>";
		}
		
		if($param['nikmandor1']==$row['karyawanid']){
			$optMandor1.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
		}else{			
			$optMandor1.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
		}	
		$n=$d;
		$w=$q;
	}

	# Asst
	$whr=" and a.kodejabatan in (".$asst.")";
	if($asst!=''){
		$whr=" and a.kodejabatan in (".$asst.")";
	}else{
		$whr=" and a.kodejabatan in ('4','5')";
	}
	$qAsst = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by a.lokasitugas, b.namajabatan,a.namakaryawan asc";
	// exit("Warning: ".$qAsst);
	$n = $w = "";
	$z = 0;
	$res=fetchdata($qAsst);
	foreach($res as $row){
		if($row['subbagian']!=''){
			$row['subbagian']=$row['subbagian'];
		}else{
			$row['subbagian']=$row['lokasitugas'];
		}
		$q=getNamaOrg($row['lokasitugas']);
		if($q!=$w){
			$z++;
			if ($z > 1) {
				$optAsst.="</optgroup>";
			}
			$optAsst.="<optgroup label='".$q."'>";
		}
		$d=$row['namajabatan'];
		if($d!=$n){
			$z++;
			if ($z > 1) {
				$optAsst.="</optgroup>";
			}
			$optAsst.="<optgroup label='".$d."'>";
		}
		$optAsst.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]</option>";
		$n=$d;
		$w=$q;
	}

	# Kerani
	if($krn!=''){
		$whr=" and a.kodejabatan in (".$krn.")";
	}else{
		$whr=" and (b.namajabatan like '%krani%panen%' or "." b.namajabatan like '%kerani%panen%' or b.namajabatan like '%harves%clerk%') and (b.namajabatan not like '%account%' and b.namajabatan not like '%akunt%' and b.namajabatan not like '%Store%' and b.namajabatan not like '%gudang%' and b.namajabatan not like '%civil%') and a.lokasitugas not like '%M' ";
	}
	$qKerani = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKarykerani." order by a.lokasitugas, b.namajabatan,  a.namakaryawan asc";
		$n = $w = "";
		$z = 0;
	$res=fetchdata($qKerani);
	foreach($res as $row){
		if($row['subbagian']!=''){
			$row['subbagian']=$row['subbagian'];
		}else{
			$row['subbagian']=$row['lokasitugas'];
		}
		$q=getNamaOrg($row['lokasitugas']);
		if($q!=$w){
			$z++;
			if ($z > 1) {
				$optKerani.="</optgroup>";
			}
			$optKerani.="<optgroup label='".$q."'>";
		}
		
		$d=$row['namajabatan'];
		if($d!=$n){
			$z++;
			if ($z > 1) {
				$optKerani.="</optgroup>";
			}
			$optKerani.="<optgroup label='".$d."'>";
		}
		if($param['kerani']==$row['karyawanid']){
			$optKerani.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]</option>";
		}else{			
			$optKerani.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]</option>";
		}
		$n=$d;
		$w=$q;
	}
	
	
	return $optdivisi."####".$optMandor."####".$optMandor1."####".$optKerani."####".$optAsst;
}

function getoptkary($param){
	global $dbname;
	global $conn;
	global $owlPDO;
	global $param;
	global $filterunit;
	global $kodeorg;
	global $kodeorgkary;
	global $method;
	global $filterdivisi;
	global $tgl;
	
	
	#============== KHT, KHL dan Kontrak =======================
	$whereKary="";
	if($kodeorgkary==''){$kodeorgkary=$kodeorg;}
	if($method=='detail'){
		$filterdivisi=$param['divisi'];
		$whereKary.=" and tipekaryawan in ('1','2','3','5','4','6','7')";
	}else{
		$whereKary.=" and tipekaryawan in ('1','2','3','4','5','6','7')";
	}
	if($filterdivisi=='PROJECT'){$filterdivisi=$param['divisi'];}

	if($filterdivisi!=''){
		
		$unitsendiri= substr($param['divisi'],0,4);
		$unitlawan  = substr($filterdivisi,0,4);	
		
		$dt=array();
		$str="select * from ".$dbname.".kebun_5asistensi where kodeorgtujuan ='".$param['kodeorg']."' and divisitujuan='".$param['divisi']."'
		and tanggal<='".tanggalsystemn($param['tgl'])."' and tanggalsampai>='".tanggalsystemn($param['tgl'])."' and posting='1' 
		and (tipetrans='BKM' or tipetrans='')";
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
			// if(count($dt)>0 and $filterdivisi!=$param['divisi']){			
			// 	$whereKary.=" and karyawanid in ('".implode("','",$dt)."')";
			// }elseif(count($resx)>0 and $filterdivisi!=$param['divisi']){			
				$whereKary.=" and subbagian in ('".implode("','",$divisiasal)."')";
				// $whereKary.=" and subbagian in ('".implode("','",$divisiasal)."') and tipekaryawan in ('2','3','4','6')";
			// }else{
				// $whereKary.=" and tipekaryawan in ('2','3','4','6')";
			// }
		}else{			
			$whereKary.=" and subbagian = '".$filterdivisi."'";
			// $whereKary.=" and tipekaryawan in ('2','3','4','6')";
		}
		
	}else{

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' ";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}
			}

			if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}
			}

			if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' ";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}
			}

		if($filterunit!=''){
			$whereKary.= " and lokasitugas='".$filterunit."' and (subbagian in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN','TRAKSI')) OR subbagian='')";
		}else{		
				
			$whereKary.= " and lokasitugas in (".$dataunitx.") and (subbagian in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN','TRAKSI')) OR subbagian='')";
		}
	}
	// echo"<pre>";
	// print_r($unitsendiri);
	// print_r($unitlawan);
	
	
	//$whereKary.= " and subbagian in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and tipe in  ('AFDELING','BIBITAN'))";
	$whereKary.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
	$whereKary.= " and tanggalmasuk<='".tanggalsystemn($tgl)."'";
	
	$optKary="<option value=''>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']."</option>";

	$strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist a 
	left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
	where 1=1 ".$whereKary."  and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."'"; 
	$resdkar = fetchdata($strdkar);
	if(count($resdkar)>0){ 
		$str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.subbagian from ".$dbname.".datakaryawan_hist a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." and approval_status='8' and version_type='B'  and periodegaji='".substr($tgl, 0,6)."' order by a.subbagian, a.namakaryawan asc";
	}else{
		$str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.subbagian, tipekaryawan from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." order by a.subbagian, a.namakaryawan asc";
	}
	
	// echo $str;
	$res=fetchdata($str);
	$d='';
	$n='';
	foreach($res as $bar){
		$d=$bar['subbagian'];

		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optKary.="<optgroup label='".$nmorg[$d]."'>";
		}

		$optKary.="<option value=".$bar['karyawanid']."> [".$bar['nik']."] - ".$bar['namakaryawan']."</option>";
		if($d!=$n){			
			$n=$d;
			$optKary.="</optgroup>";
		}
	}
	#============== KHT, KHL dan Kontrak ======================
	
	// echo "<pre>";
	// print_r($str);
	// exit("error");
	
	return $optKary;
}	

function getoptblok($param){
	global $dbname;
	global $conn;
	global $owlPDO;
	global $param;
	global $kodeorg;
	
	$filterdivisi = $param['filterdivisi'];

	$getNamaIndukBlok = makeOption($dbname,"organisasi","indukblok,namaindukblok");
	
	$whereBlok=$whererpnn='';
	$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	if($filterdivisi=='PROJECT'){
		$str = "select kode, nama from ".$dbname.".project where kodeorg='".$kodeorg."'and posting='0' and substr(kode,4,2)='BG' order by substr(kode,4,2) asc";
		$res = fetchdata($str);
		$n="";
		$z=0;
		foreach($res as $val){
			$d = substr($val['kode'],3,2);
			if($d!=$n){
				$z++;
				if ($z > 1) {
					$optBlok.="</optgroup>";
				}	
				$nmorg = makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,namatipe',"kodetipe='".$d."'");
				$optBlok.="<optgroup label='".$nmorg[$d]."'>";
			}
			
			if(substr($val['kode'],0,2)=='AK' or substr($val['kode'],0,2)=='PB'){
				if($param['blok']==$val['kode']){
					$optBlok.="<option value='".$val['kode']."' selected>".$val['kode']." - ".$val['nama']."</option>";
				}else{
					$optBlok.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
				}
			}else{
				if($param['blok']==$val['kode']){
					$optBlok.="<option value='".$val['kode']."' selected>".$val['kode']." - ".$val['nama']."</option>";
				}else{
					$optBlok.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
				}
			}
			$n=$d;
		}
		
	}else{
		// if($filterdivisi!=''){
			// if(substr($filterdivisi,0,4)!=$kodeorg){
				// $whereBlok.= " and substr(a.kodeorganisasi,1,6) = '".$param['divisi']."'";
				// $whereBlok.=" ";
			// }else{			
			// 	$whereBlok.=" and substr(a.kodeorganisasi,1,6) = '".$filterdivisi."'";
			// 	$whereBlok.=" and b.statusblok in ('TB','TBM','TM','BBT')";
			// }
		// }else{
		// 	$whereBlok.= " and substr(a.kodeorganisasi,1,4) ='".$kodeorg."'";
		// 	$whereBlok.= " and b.statusblok in ('TB','TBM','TM','BBT')";
		// }

		if($filterdivisi!=''){
			if(substr($filterdivisi,0,4)!=$kodeorg){
				$whereBlok.= " and substr(a.kodeorganisasi,1,4) ='".$kodeorg."'";
			}else{			
				$whereBlok.=" and substr(a.kodeorganisasi,1,6) = '".$filterdivisi."'";
			}
		}else{
				$whereBlok.= " and substr(a.kodeorganisasi,1,4) ='".$kodeorg."'";
		}
		
		// $str = "select * from ".$dbname.".organisasi a  left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg where a.tipe in('BLOK','BIBITAN') ".$whereBlok." and b.luasareaproduktif>0 order by substr(a.kodeorganisasi,1,6), b.tahuntanam, a.kodeorganisasi asc";
		$str = "select * from ".$dbname.".organisasi a  left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg where a.tipe in('BLOK','BIBITAN') ".$whereBlok." and b.luasareaproduktif>=0 and b.statusblok in ('TB','TBM','TM','BBT') group by a.indukblok order by substr(a.kodeorganisasi,1,6), a.kodeorganisasi, b.tahuntanam asc";
		$res=fetchdata($str);
		$m="";
		$z=0;
		foreach($res as $bar){
			$a=substr($bar['kodeorganisasi'],0,6);
			if($a!=$m){
				$z++;
				if ($z > 1) {
					$optBlok.="</optgroup>";
				}
				$optBlok.="<optgroup label='".getNamaOrg($a)."'>";
			}
			// $d=$bar['tahuntanam'];
			// if($d!=$n){			
				// $optBlok.="<optgroup label='Tahun Tanam ".$d."'>";
			// }
			// $optBlok.="<option value=".$bar['kodeorganisasi'].">".getNamaOrg($bar['kodeorganisasi'])." - ".$bar['statusblok']."</option>";
			$optBlok.="<option value='".$bar['indukblok']."'>".$getNamaIndukBlok[$bar['indukblok']]."</option>";
			// $n=$d;
			// if($d!=$n){			
				// $optBlok.="</optgroup>";
			// }
			$m=$a;
		}
	}
	
	// echo"<pre>";
	// print_r($filterdivisi);
	// exit("error".$str);
	
	return $optBlok;	
}
?>	