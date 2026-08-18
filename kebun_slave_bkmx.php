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
$nobkm             = checkPostGet('nobkm', '');
$blok              = checkPostGet('blok', '');
$karyawanid        = checkPostGet('karyawanid', '');
$kegiatan          = checkPostGet('kegiatan', '');
$kodegudang        = checkPostGet('kodegudang', '');
$mandorsrc         = checkPostGet('mandorsrc', '');
$nobkmsch          = checkPostGet('nobkmsch', '');
$mode              = checkPostGet('mode', '');
$kodeabsen         = checkPostGet('kodeabsen', '');

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
#======================= cek admin =========================
$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
$wh='';
if(count($adm)==0){
	$wh= " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}
#======================= cek admin =========================
#============== KHT, KHL dan Kontrak =======================
	if($filterdivisi!=''){
		@$whereKary=" and subbagian = '".$filterdivisi."'";
	}else{
		@$whereKary.= " and lokasitugas='".$kodeorg."'";
	}
	
	$whereKary.=" and subbagian in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and tipe = 'AFDELING')";
	
	$whereKary.= " and tipekaryawan in (1,2,3,4,6) and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
	$optKary="<option value=''>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']."</option>";

	$strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist a 
	left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
	where 1=1 ".$whereKary."  and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."'"; 
    $resdkar = fetchdata($strdkar);
    if(count($resdkar)>0){ 
    	$str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.subbagian from ".$dbname.".datakaryawan_hist a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." and approval_status='8' and version_type='B'  and periodegaji='".substr($tgl, 0,6)."' order by a.subbagian, a.namakaryawan asc";
    }else{
    	$str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.subbagian from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." order by a.subbagian, a.namakaryawan asc";
    }
	
	$res=fetchdata($str);
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
			$optKary.="<optgroup label='".$nmorg[$d]."'>";
		}
		$optKary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan'].$bar['nik']."</option>";
		$n=$d;
		if($d!=$n){
			$optKary.="</optgroup>";
		}

	}
#============== KHT, KHL dan Kontrak ======================
#===================== Kode Blok ==========================
	if($filterdivisi!=''){
		@$whereBlok=" and substr(a.kodeorganisasi,1,6) = '".$filterdivisi."'";
		@$whereBlok.=" and b.statusblok in ('TB','TBM','TM','BBT')";
	}else{
		@$whereBlok= " and substr(a.kodeorganisasi,1,4) ='".$kodeorg."'";
		@$whereBlok.= " and b.statusblok in ('TB','TBM','TM','BBT')";
	}
	$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".organisasi a 
			left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
			where a.tipe in('BLOK','BIBITAN') ".$whereBlok." and b.luasareaproduktif>0 order by a.kodeorganisasi asc";
	$res=fetchdata($str);
	foreach($res as $bar){
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorganisasi']."'");
		$optBlok.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$nmorg[$bar['kodeorganisasi']]." - ".$bar['statusblok']."</option>";
	}
	#exit("error".$str);
#===================== Kode Blok ==========================

#===================== Kode Keg ==========================
	$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".setup_kegiatan
			where 1=1 and kelompok in ('BBT','TBM','TM') and status='1' order by kodekegiatan asc, namakegiatan asc";
	$res=fetchdata($str);
	foreach($res as $bar){
		$optKeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
	}
#===================== Kode Keg ==========================
	
	
$jab = getPostingJabatan('rawatkebun');	
$tmpTgl = explode('-',$tgl);	


switch ($method) {
	case'getprdcari':
		$optprd = "<option value=''></option>";
		$wh="";
		if($_SESSION['empl']['subbagian']!=''){
			$wh=" and b.kodeorg like '".$_SESSION['empl']['subbagian']."%'";
		}
		$str="select DISTINCT (substr(a.tanggal,1,7)) as prd from ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi where a.kodeorg = '".$_SESSION['empl']['lokasitugas']."' ".$wh." order by prd desc";
		$res = fetchData($str);
		foreach($res as $key => $val){
			$data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
		}
		$no=0;
		foreach($data as $thn => $vprd){
			$optprd.="<option value=".$thn." ".$n.">".$thn."</option>";			
			foreach($vprd as $prd){
				$no+=1;$n="";
				if($no==1){
					$n="selected";
				}
				$optprd.="<option value=".$prd." ".$n.">".$prd."</option>";			
			}
		}
		echo $optprd;
	break;
	case'simpanheader':
		#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
		$sekarang=  tanggalsystem($tgl);
		if($sekarang<$_SESSION['org']['period']['start']){
			exit("Validation Error : Date out of range");
        }
		$tgl=tanggalsystemn($tgl);
		
		/* #atas permintaan untuk bulan januari jangan di input dulu
		$arrthn = explode("-",$tgl);
		if($arrthn[0]>'2019'){
			exit("Error : Penginputan BKM untuk periode tahun 2020 masih belum di perbolehkan.");
		} */
		
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
			echo "HK Mandor / Mandor 1 / Kerani tidak boleh di input pada detail BKM.\n\nKaryawan tersebut dibawah ini sudah terdaftar pada transaksi :\n";//
			$no=0;
			while($bar=$res->fetch()){
			   $no+=1;
				echo $no.". ".$namaKary[$bar->karyawanid]." => ".$bar->notransaksi." => ".tanggalnormal($bar->tanggal)."\n"; 
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
            $str = "update " . $dbname . ".kebun_aktifitas set `nikmandor`='".$mandor."', `nikmandor1`='".$mandor1."',`keranimuat`='".$kerani."',`nikasisten`='".$asst."' where `notransaksi`='".$notransaksi."' and `nobkm`='".$nobkm."'"; #exit("error".$str);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        }else{
			#===== buat nomor BKM =====
			$data = $_POST;
			# Data Capture & Reform			
			$data['tgl'] = tanggalsystem($data['tgl']);
			
			
			
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
				if($noUrut>$maxNo)
					$maxNo = $noUrut;
				}
				$currNo = addZero($maxNo+1,3);
				$notransaksi = $data['tgl']."/".$data['kodeorg']."/BKM/".$currNo;
			}
			#=== End buat nomor transaksi ===
			if($statusblok==''){
				exit("Warning : Tipe transaksi masih kosong.");
			}
			$str = "insert into " . $dbname . ".kebun_aktifitas (`notransaksi`, `tipetransaksi`, `tanggal`, `nobkm`, `kodeorg`, `nikmandor`, `nikmandor1`, `nikasisten`, `keranimuat`, `jurnal`, `nospk`, `updateby`)
			values ('".$notransaksi."','".$statusblok."','".$tgl."','" . $notransaksi . "','" . $kodeorg . "','".$mandor."','".$mandor1."','".$asst."','".$kerani."','0',null,'" . $_SESSION['standard']['userid'] . "')"; #exit("error".$str);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
			echo $notransaksi;
		}	
		
		
	break;
    case'detail':
        OPEN_BOX();
		#==== Form Judul Detail ====
		# Divisi
		$optDivisi=$whereX='';
		if($_SESSION['empl']['subbagian']!=''){
			#$optDivisi="<option value='".$_SESSION['empl']['subbagian']."'>".$_SESSION['empl']['subbagian']."</option>";
		}
			$optDivisi.="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		if($_SESSION['tmp']['kebun']['tipeTrans']=='BBT'){
			#$whereX=" and tipe = 'BIBITAN' ";
		}else{
			#$whereX=" and tipe != 'BIBITAN' ";
		}
		
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') and kodeorganisasi like '".$kodeorg."%' ".$whereX." ";
		$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $resstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $resstr->fetch()) {
			if($_SESSION['empl']['subbagian']==$res['kodeorganisasi']){
				$optDivisi.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}else{
				$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}
		}
		#=== TAB PRESTASI DAN KEHADIRAN ===
        $frm[0]="<table><td valign=top>
			<fieldset style=float:left><legend>Filter</legend>
				<table height=25px>
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td><select style=\"width:150px;\"  onchange=\"getdata()\" id=filterdivisi>".$optDivisi."</select></td>
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
			$frm[0].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']."</td>
				<td align=center ".$rows."  colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows."  colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows." width=30px>".$_SESSION['lang']['luas'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['jumlah']."</td>
				<td align=center colspan=3 >".$_SESSION['lang']['premi']."</td>
				<td align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>".$_SESSION['lang']['satuan'] . "</td>
				<td align=center width=45px><font color=red><b>* </font></b>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center width=35px><font color=red><b>* </font></b>HK</td>
				<td align=center width=50px><font color=red><b>* </font></b>".$_SESSION['lang']['upah']."</td>
				<td align=center width=50px>".$_SESSION['lang']['basic']."</td>
				<td align=center width=50px>".$_SESSION['lang']['rpsat']."</td>
				<td align=center width=50px>".$_SESSION['lang']['lebihbasis']."</td>
			</tr>
			<tr id=copy style=display:none>
				<td></td>
				<td colspan=2></td>
				<td colspan=2 align=center valign=center>Copy <input type=checkbox title='Aktif / Non Aktif' id=copyblok class='zImgBtn' style='position:relative;top:3px;left:3px;'></td>
				<td colspan=2 align=center valign=center>Copy <input type=checkbox title='Aktif / Non Aktif' id=copykeg class='zImgBtn' style='position:relative;top:3px;left:3px;'></td>
				<td></td>
				<td></td>
				<td align=center><input type=checkbox id=copypres title='Aktif / Non Aktif' class='zImgBtn' style='position:relative;top:3px;left:3px;'></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td colspan=2></td>
				
			</tr>
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
					<hr>
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
			$frm[1].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['notransaksi'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kodekegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['gudang']."</td>
				<td align=center ".$rows." colspan=2>".$_SESSION['lang']['namabarang']."</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['material']."</td>
				<td align=center ".$rows." colspan=2>" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>".$_SESSION['lang']['satuan'] . "</td>
				<td align=center width=45px>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center width=35px>".$_SESSION['lang']['satuan']."</td>
				<td align=center width=50px><font color=red><b>* </font></b>".$_SESSION['lang']['jumlah']."</td>
				
				
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
		
		#=== TAB ABSENSI ===
		$frm[2]="<table border=0><td valign=top>
			<fieldset style=float:left;height:70px; >
				<legend>Info</legend>
					<table height=25px border=0>
						<tr><td>" . $_SESSION['lang']['divisi'] . "</td>
						<td><select style=\"width:150px;\" title=\"Untuk menampilkan data karyawan dari divisi lain.\" onchange=\"getdata(this.value)\" id=filterdivisiabsensi>".$optDivisi."</select></td></tr>
						
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
			<thead><tr class=rowheader>";
			
		$rows="rowspan=1";	
		$frm[2].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']."</td>
				<td align=center ".$rows.">Absensi</td>
				<td align=center ".$rows.">".$_SESSION['lang']['hk2'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['upah'] . " Rp</td>
				<td align=center ".$rows.">".$_SESSION['lang']['premi'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['keterangan'] . "</td>
				<td align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
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
				// <td><input type=text style=\"width:75px;\" id=premiabsen class=myinputtextnumber onkeyup=\"z.numberFormat('premiabsen',2)\" nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
		$frm[2].="<tbody id=inputdetailabsensi>
				<tr class=rowcontent>
				<td id=no align=center>1</td>
				
				
				<td><select style=width:200px id=karyawanidabsensi onchange=getumrabsensi();>".$optKary."</select></td><td width=20px>
				<img id='karyawanidabsensi' onclick=z.elSearch('karyawanidabsensi',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>

				<td><select style=width:90px onchange=getnilaihk(); onmousemove=hapuswarna(this.id); id=kodeabsen>".$optabs."</select>
				</td>
				
				<td><input onkeyup=getumrabsensi(); disabled id=jhkabsen class=myinputtextnumber value=".$isihk." onkeypress=\"return angka_doang(event);\" style=\"width:35px;\"></td>
				
				<td><input id=upahabsen onkeyup=gethk(this.id,'jhkabsen','karyawanidabsensi',''); disabled class=myinputtextnumber style=\"width:75px;\"></td>
				<td><input type=text style=\"width:75px;\" id=premiabsen class=myinputtextnumber onkeyup=\"numberFormat2('premiabsen',0)\" nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
				<td><input id=keteranganabsen class=myinputtext style=\"width:200px;\"></td>
				
				
				
				<td  align=center width=20px><input type=hidden id=method value='insert'>
					<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"saveabsensi()\" src='images/save.png'/></td>
				<td  align=center width=20px>
					<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"clearabsensi()\" src='images/clear.png'/>
				</td>
			</tr><tr>
				<td colspan=8></td>
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
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style=min-width:800px>
			<thead><tr class=rowheader>";
			
		$rows="rowspan=1";	
		$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center width=50px ".$rows.">Absensi</td>
				<td align=center width=40px ".$rows.">".$_SESSION['lang']['hk2'] . "</td>
				<td align=center width=100px ".$rows.">".$_SESSION['lang']['upah'] . " Rp</td>
				<td align=center width=100px ".$rows.">".$_SESSION['lang']['premi'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['keterangan'] . "</td>
				<td align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
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
			
			$tab.="<tr class=rowcontent id=rowabslist_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>" . $bar['nik'].$bar['subbagian'].getNamaKaryawan($bar['karyawanid']). "</td>";

			$tab.="<td align=center>".$bar['absensi']."</td>";
			$tab.="<td align=right>".@numb_format($bar['nilaihk'],2)."</td>";
			$tab.="<td align=right>".@numb_format($bar['umr'],2)."</td>";
			$tab.="<td align=right>".@numb_format($bar['premi'],2)."</td>";
			$tab.="<td>".$bar['penjelasan']."</td>";
			
			$tab.="<td align=center  width=20px>
				<img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"editabsensi('".$bar['karyawanid']."','".$bar['absensi']."','".$bar['nilaihk']."','".$bar['umr']."','".$bar['premi']."','".$bar['penjelasan']."','".$bar['kodeorg']."');\">
				</td>
				
				<td align=center  width=20px>
				<img title='".$_SESSION['lang']['delete']."' class=zImgBtn onclick=\"delabsen('".$notransaksi."','".$tgl."','".$bar['kodeorg']."','".$bar['karyawanid']."')\" src='images/skyblue/delete.png'/>
				
				
			</td>";
			$tab.="</tr>";
			
			@$thk+=$bar['nilaihk'];
			@$tumr+=$bar['umr'];
			@$tpremi+=$bar['premi'];
		}
		#sub total absensi
		$tab.="<tr class=rowcontent style=background-color:#AED6F1;font-weight:bold>";
		$tab.="<td align=center>".numb_format($no)."</td>";
		$tab.="<td align=center colspan=2>Sub Total (Absensi)</td>";
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
		$tab.="<td colspan=9 bgcolor=#2C3E50></td>";
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
			
			$tab.="<tr class=rowcontent style=color:gray; title=\"Untuk melakukan edit atau delete silahkan buka di tab Prestasi dan Kehadiran\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>" . $bar['nik'].@$bar['subbagian'].getNamaKaryawan($bar['nikpemel']) . "</td>";
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
		$tab.="<tr class=rowcontent style=background-color:#AED6F1;font-weight:bold>";
		$tab.="<td align=center>".numb_format($no)."</td>";
		$tab.="<td align=center colspan=2>Sub Total (BKM)</td>";
		$tab.="<td align=right>".numb_format($thkb,2)."</td>";
		$tab.="<td align=right>".numb_format($tumrb)."</td>";
		$tab.="<td align=right>".numb_format($tpremib)."</td>";
		$tab.="<td></td>";
		$tab.="<td width=20px></td>";
		$tab.="<td width=20px></td>";
		$tab.="</tr>";
		
		$ttltk+=$no;
		
		#Grand Total
		$tab.="<tr class=rowcontent style=background-color:#A3E4D7;font-weight:bold>";
		$tab.="<td align=center>".numb_format($ttltk)."</td>";
		$tab.="<td align=center colspan=2>Grand Total (Absensi + BKM)</td>";
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
			$tab.="<td colspan=9 bgcolor=#2C3E50></td>";
			$tab.="</tr>";
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=9 bgcolor=#E7E7FF style=font-weight:bold>Informasi karyawan yang di absen melalui menu SDM - Transaksi - Absensi</td>";
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
				$tab.="<td align=center>".$bar['absensi']."</td>";
				$tab.="<td align=right>".numb_format($bar['hk'],2)."</td>";
				$tab.="<td align=right>".numb_format($bar['umr'],2)."</td>";
				$tab.="<td align=right>".numb_format($bar['premi'],2)."</td>";
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
			$tab.="<td align=center colspan=2>Sub Total SDM Absensi</td>";
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
		
		# ==========================================================================================
		$data = array(
			'absensi'   =>$param['kodeabsen'],
			'hk'        =>$param['jhk'],
			'umr'       =>$param['upah'],
			'premi'     =>$param['premi'],
			'penjelasan'=>$param['keterangan']
		);
		$where = "norefrensi='".$param['notransaksi']."' and nobkm='".$param['nobkm']."' and tanggal='".tanggalsystemn($param['tgl'])."' and karyawanid='".$param['karyawanid']."' and kodeorg='".$param['kodeorgabsensi']."'";
		
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
	
	case 'caribarang':
		$tab="";
		$no=0;
		$tab.="<table class=sortable cellspacing=1 border=0 style=width:100%>
			<thead>
			<tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
				<td align=center width=50px>".$_SESSION['lang']['saldo']." Gudang</td>
				<td align=center width=50px>Pemakaian Blm Post</td>
				<td align=center width=50px>".$_SESSION['lang']['saldo']."</td>
			</tr>
			</thead>
			<tbody>";
		$arrprd = explode("-",tanggalsystemn($param['tgl']));
		$periode = $arrprd[0]."-".$arrprd[1];
		
		$str="select a.kodebarang,a.namabarang,a.satuan,b.saldoakhirqty as saldoqty 
		from ".$dbname.".log_5masterbarang a 
		left join ".$dbname.".log_5saldobulanan b on a.kodebarang=b.kodebarang 
		where (a.namabarang like '%".$txtcari."%' or a.kodebarang like '%".$txtcari."%') and b.kodegudang='".$kodegudang."' and b.saldoakhirqty >'0' and a.kodebarang in (select kodebarang from ".$dbname.".setup_kegiatannorma where kodekegiatan='".$kegiatan."') and b.periode='".$periode."'";
		$res=fetchData($str);
		foreach($res as $val){
			$s="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where kodebarang='".$val['kodebarang']."' and kodegudang='".$kodegudang."' and post='0' and tipetransaksi>4";
			$r=fetchData($s);
			$logblmpost = $r[0]['jumlah'];
			
			$queryM="select sum(kwantitas) as jlh from ".$dbname.".kebun_pakaimaterial where kodegudang='".$kodegudang."' and kodebarang='".$val['kodebarang']."' and notransaksi in (select notransaksi from ".$dbname.".kebun_aktifitas where jurnal='0' and kodeorg='".substr($kodegudang,0,4)."')";
			$dataM = fetchData($queryM);
			$pakaibkm = $dataM[0]['jlh'];
			$pakaiblmpost = $pakaibkm+$logblmpost;
			
			
			$i="";
			if(($val['saldoqty']-$pakaiblmpost)<0){
				$i = "style=color:red;font-weight:bold;";
			}
			
			
			$no+=1;
			$tab.="<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"loadField('".$val['kodebarang']."','".$val['namabarang']."','".$val['satuan']."');\">
				<td align=center>".$no."</td>
				<td align=center>".$val['kodebarang']."</td>
				<td>".$val['namabarang']."</td>
				<td>".$val['satuan']."</td>
				<td align=right>".@numb_format($val['saldoqty'],3)."</td>
				<td align=right>".@numb_format($pakaiblmpost,3)."</td>
				<td align=right ".$i.">".@numb_format($val['saldoqty']-$pakaiblmpost,3)."</td>
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
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."' or kodeorganisasi='".$barv['kodegudang']."'");
			
			$no+=1;
			$tab.="<tr class=rowcontent ".$i." id=rowmat_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td id=notran".$no.">".$bar['notransaksi']."</td>";
			$tab.="<td id=kegiatanmat".$no.">".$bar['kodekegiatan']."</td>";
			$tab.="<td>".$nmkeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td id=blokmat".$no." hidden>".$bar['kodeorg']."</td>";
			$tab.="<td>".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=center>".$nmsat[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=right>".@numb_format($bar['hasilkerja'],2)."</td>";
			$tab.="<td id=pres".$no." style=display:none>".$bar['hasilkerja']."</td>";
			$tab.="<td id=kodegudang".$no." style=display:none>".$barv['kodegudang']."</td>";
			$tab.="<td>".$barv['kodegudang']." - ".$nmorg[$barv['kodegudang']]."</td>";
			$tab.="<td>
					<input type=text id=kodemat".$no." class=myinputtext style='width:60px;' onclick=\"searchmat('".$no."','Find',event);\" onmousemove=hapuswarna(this.id); readonly></td>
					<td>
					<input type=text id=namamat".$no." class=myinputtext style='width:150px;' onclick=\"searchmat('".$no."','Find',event);\" onmousemove=hapuswarna(this.id); readonly></td>";
			$tab.="<td><input id=satmat".$no." class=myinputtext disabled style=\"width:35px;\"></td>";
			$tab.="<td><input id=qtymat".$no." onclick=hapuswarna(this.id); onkeyup=\"z.numberFormat('qtymat".$no."',3);\"  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
			$tab.="<td  align=center width=20px>
				<img title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=\"savematerial(".$no.")\" src='images/save.png'/></td>
				<td  align=center width=20px>
				<img title='".$_SESSION['lang']['clear']."' class=zImgBtn onclick=\"clearmaterial(".$no.")\" src='images/clear.png'/>
			</td>";
			$tab.="</tr>";
		}
		$tab.="<tr>
			<td colspan=12></td>
			<td  align=center width=20px>
				<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetailmaterial()\" src='images/refresh2.png'/></td>
			<td  align=center width=20px>
				<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
			</td>
        </tr>";
		
	echo $tab;
	break;
	
	case'loaddatadetailmaterial':
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
		$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['notransaksi'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kodekegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['gudang']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['namabarang']."</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['material']."</td>
				<td align=center ".$rows." width=35px>" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>".$_SESSION['lang']['satuan'] . "</td>
				<td align=center width=45px>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center width=35px>".$_SESSION['lang']['satuan']."</td>
				<td align=center width=50px>".$_SESSION['lang']['jumlah']."</td>
				
				
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
					$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kdorg."' or kodeorganisasi='".$kdgdng[$kdkeg][$kdorg][$kdbrg]."'");
					$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$kdkeg."'");
					
					$strv = "select sum(hasilkerja) as hasilkerja from ".$dbname.".kebun_prestasi where notransaksi='".$notrans[$kdkeg][$kdorg][$kdbrg]."' and kodekegiatan='".$kdkeg."' and kodeorg='".$kdorg."'"; //exit('error'.$strv);
					$barv=fetchdata($strv);
					
					$tab.="<tr class=rowcontent id=rowmatlist_".$no.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$notrans[$kdkeg][$kdorg][$kdbrg]."</td>";
					$tab.="<td>".$kdkeg."</td>";
					$tab.="<td>".$nmkeg[$kdkeg]."</td>";
					$tab.="<td>".$nmorg[$kdorg]."</td>";
					$tab.="<td align=center>".$nmsat[$kdkeg]."</td>";
					$tab.="<td align=right>".@numb_format($barv[0]['hasilkerja'],2)."</td>";
					$tab.="<td>".$kdgdng[$kdkeg][$kdorg][$kdbrg]." - ".$nmorg[$kdgdng[$kdkeg][$kdorg][$kdbrg]]."</td>";
					$tab.="<td>".$kdbrg." - ".$nmbrg[$kdbrg]."</td>";
					$tab.="<td align=center>".$nmsatbrg[$kdbrg]."</td>";
					$tab.="<td align=right>".@numb_format($jlhmat[$kdkeg][$kdorg][$kdbrg],3)."</td>";
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
					$tab.="<tr class=rowcontent style=background-color:#2C3E50>";
					$tab.="<td colspan=12></td>";
					$tab.="</tr>";
			}
		}
		
		$no=0;
		foreach($datakdmat as $kodemat){
			$nmsatbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kodemat."'");
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodemat."'");
					
			$no++;
			$tab.="<tr class=rowcontent style=background-color:#A3E4D7;>";
			if($no==1){				
				$tab.="<td align=center rowspan=".count($datakdmat)." colspan=8><b>REKAPITULASI</b></td>";
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
			// <td><input id=premi disabled onkeyup=\"z.numberFormat('premi',2)\"  class=myinputtextnumber style=\"width:60px;\"></td>
	echo"<td><select style=width:155px;align:right; onchange=getDataDetail() id=karyawanid >".$optKary."</select></td><td width=20px>
			<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>

			<td style=width:95px><select style=width:95px onchange=getDataDetail('','changekeg') id=blok>".$optBlok."</select></td><td width=20px>
			<img id='blok' onclick=z.elSearch('blok',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
			
			<td><select style=width:150px onchange=getDataDetail() id=kegiatan>".@$optKeg."</select></td><td width=20px>
			<img id='kegiatan' onclick=z.elSearch('kegiatan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
			
			<td><input id=luas disabled class=myinputtextnumber style=\"width:35px;\"></td>
			<td><input id=satuan class=myinputtext disabled style=\"width:40px;align:center\"></td>

			<td><input id=prestasi onkeyup=\"getDataDetail();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber  onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
			
			<td><input id=jhk title=\"Double click untuk mengisi HK menjadi 1\" onkeyup=\"getumr('','i');\" ondblclick=\"getumr('','d');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:35px;\"></td>
			
			<td><input id=upah onkeyup=gethk(this.id,'jhk','karyawanid',''); nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:60px;\"></td>

			<td><input id=basis disabled onkeyup=\"z.numberFormat('basis',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
			<td><input id=rpsat disabled onkeyup=\"z.numberFormat('rpsat',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
			
			<td><input id=premi disabled onkeyup=\"numberFormat('premi',2)\"  class=myinputtextnumber style=\"width:60px;\"></td>
			
			
			<td align=center width=20px><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
			</td>
			<td  align=center width=20px>	
				<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail()\" src='images/clear.png'/>
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
		echo $optKary."######".$optBlok;
	break;
	case'getdatamandor':
	$whereKary="";
	$whereKary= " and tipekaryawan in (2,3,4,6) and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
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
	
	$tab="<table id=tabledt cellpadding=1 cellspacing=1 ".$border." class=sortable style=min-width:960px>
			<thead><tr class=rowheader>
			<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['notransaksi'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows." width=30px>".$_SESSION['lang']['luas'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['jumlah']."</td>
				<td align=center ".$rows." >".$_SESSION['lang']['premi']."</td>
				<td align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>".$_SESSION['lang']['satuan'] . "</td>
				<td align=center width=45px>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center width=35px>HK</td>
				<td align=center>".$_SESSION['lang']['upah']."</td>
			</tr>
		</thead>";
		
        $no = 0;
		$where = "";
		$wherexz = "";
		if($notrandetsch!=''){
			$where.=" and a.notransaksi like '%".$notrandetsch."%'";
		}
		if($namakary!=''){
			$where.=" and (b.karyawanid like '%".$namakary."%' or b.nik like '%".$namakary."%' or b.namakaryawan like '%".$namakary."%')";
			$wherexz.=" and (karyawanid like '%".$namakary."%' or nik like '%".$namakary."%' or namakaryawan like '%".$namakary."%')";
		}
		if($blok!=''){
			$where.=" and a.kodeorg like '%".$blok."%'";
		}
		if($kegiatan!=''){
			$where.=" and (a.kodekegiatan like '%".$kegiatan."%' or a.kodekegiatan in (select kodekegiatan from ".$dbname.".setup_kegiatan where namakegiatan like '%".$kegiatan."%'))";
		}
		$jlhcolspan=13;

		$strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist  where 1=1 ".$wherexz."  and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."'"; 
        $resdkar = fetchdata($strdkar);
        if(count($resdkar)>0)
        { 
        	 $str = "select a.*,b.namakaryawan,b.nik as nik2, b.subbagian, c.jhk, c.umr, c.insentif
				from " . $dbname . ".kebun_prestasi a 
				left join " . $dbname . ".datakaryawan_hist b on a.nikpemel=b.karyawanid  
				left join " . $dbname . ".kebun_kehadiran c on a.nikpemel=c.nik and a.notransaksi=c.notransaksi and a.nourut=c.nourut
				where a.nobkm='" . $nobkm . "' ".$where." and approval_status='8' and version_type='B' and tipekaryawan in ('1','2','3','4') and periodegaji='".substr($tgl, 0,6)."' order by a.notransaksi asc, b.namakaryawan asc, a.kodekegiatan asc";// exit('error'.$str);
        }
        else
        {
        	 $str = "select a.*,b.namakaryawan,b.nik as nik2, b.subbagian, c.jhk, c.umr, c.insentif
				from " . $dbname . ".kebun_prestasi a 
				left join " . $dbname . ".datakaryawan b on a.nikpemel=b.karyawanid  
				left join " . $dbname . ".kebun_kehadiran c on a.nikpemel=c.nik and a.notransaksi=c.notransaksi and a.nourut=c.nourut
				where a.nobkm='" . $nobkm . "' ".$where." order by a.notransaksi asc, b.namakaryawan asc, a.kodekegiatan asc";// exit('error'.$str);
        }
    //     $str = "select a.*,b.namakaryawan,b.nik as nik2, b.subbagian, c.jhk, c.umr, c.insentif
				// from " . $dbname . ".kebun_prestasi a 
				// left join " . $dbname . ".datakaryawan b on a.nikpemel=b.karyawanid  
				// left join " . $dbname . ".kebun_kehadiran c on a.nikpemel=c.nik and a.notransaksi=c.notransaksi and a.nourut=c.nourut
				// where a.nobkm='" . $nobkm . "' ".$where." order by a.notransaksi asc, b.namakaryawan asc, a.kodekegiatan asc";// exit('error'.$str);
        $res=fetchdata($str);
        $row=count($res);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=".$jlhcolspan." style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach ($res as $bar) {
				#cek apakah sudah di posting	
				$optpost=makeOption($dbname,'kebun_aktifitas','notransaksi,jurnal',"notransaksi='".$bar['notransaksi']."'");
				
				$bgcolor=$title=$bgc=$asstensi=$infomat='';
				$strx = "select count(nikpemel) as jmlkary, nikpemel from " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."' and nikpemel='".$bar['nikpemel']."' group by nikpemel";
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$barx = $resx->fetch();
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
					$bgc="style=background-color:#33FF35;";
				}
				
				$cekmat="select * from " . $dbname . ".kebun_pakaimaterial where notransaksi='".$bar['notransaksi']."' and kodeorg='".$bar['kodeorg']."' and kodekegiatan='".$bar['kodekegiatan']."'";
				$resmat = fetchdata($cekmat);
				
				$cekset="select * from " . $dbname . ".setup_kegiatannorma where kodekegiatan='".$bar['kodekegiatan']."'";				
				$resset = fetchdata($cekset);
				
				if(count($resset)>0 and count($resmat)==0){
					$infomat="style=color:red;cursor:pointer; onclick=pindahtab('tabFRM1','1');";
					$infomat.=" title = 'Pekerjaan ini harus menggunakan material, silahkan input pada tab Material.'";
				}
				$luasblok=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$bar['kodeorg']."'");
				$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$bar['kodekegiatan']."'");$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kodekegiatan']."'");
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				
				$luasclr="";
				if(strtolower($nmsat[$bar['kodekegiatan']])=='ha' and $bar['hasilkerja']>$luasblok[$bar['kodeorg']]){
					$luasclr="style=background-color:red; title='Luas hasil kerja melebihi luasan total blok.'";
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
				$tab.="<td align=right>" . @numb_format($luasblok[$bar['kodeorg']],2) . "</td>";
				$tab.="<td align=center>" . $nmsat[$bar['kodekegiatan']] . "</td>";
				$tab.="<td align=right ".$luasclr.">" . @numb_format($bar['hasilkerja'],2) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['jhk'],2) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['umr'],2) . "</td>";
				$tab.="<td align=right>" . @numb_format($bar['insentif'],2) . "</td>";
				
				@$tjhk+=$bar['jhk'];
				@$tumr+=$bar['umr'];
				@$tinsentif+=$bar['insentif'];
				if($optpost[$bar['notransaksi']]==0){
					$tab.="<td align=center width=20px>";
					if($tipe!='excel'){
					$tab.="<img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
						onclick=\"editdetail('".$bar['notransaksi']."','".$bar['nikpemel']."','".$bar['kodekegiatan']."','".$bar['kodeorg']."','".$luasblok[$bar['kodeorg']]."','".$nmsat[$bar['kodekegiatan']]."','".$bar['hasilkerja']."','".$bar['jhk']."','".$bar['umr']."','".$bar['insentif']."','".$no."');\" >";
						$tab.="</td>";	
						$tab.="<td align=center width=20px>";	
						$tab.="<img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
						onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['nikpemel'] . "','" . $bar['kodeorg'] . "','".$bar['kodekegiatan']."');\" >";
					}
					$tab.="</td>";
				}else{
					$tab.="<td align=center>Posted</td>";
				}
				#untuk subtotal dibawah
				$data[$bar['kodeorg']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
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
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=".$jlhcolspan." bgcolor=#2C3E50></td>";
			$tab.="</tr>";
			$nosub=0;
			foreach($data as $kdblok => $valkeg){
				foreach($valkeg as $kdkeg => $kegiatan){
					$nosub+=1;
					$tab.="<tr class=rowcontent style=background-color:#AED6F1>";
					if($nosub==1){
						$tab.="<td colspan=3 rowspan=".$rowsp." align=center><b>REKAPITULASI</b></td>";						
					}
					
					$luasblok=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$kdblok."'");
					$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$kdkeg."'");
					$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kdkeg."'");
					$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kdblok."'");
					
					$luasclr="";
					if(strtolower($nmsat[$kdkeg])=='ha' and $preskerja[$kdblok][$kdkeg]>$luasblok[$kdblok]){
						$luasclr="style=background-color:red; title='Luas hasil kerja melebihi luasan total blok.'";
					}
					
					$tab.="<td align=center style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$kdblok."','blok')>".$nmorg[$kdblok]."</td>";
					$tab.="<td align=left  style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$kdkeg."','kegiatan')>".$kdkeg." - " . $nmkeg[$kdkeg]. "</td>";
					$tab.="<td align=right>" . @numb_format($luasblok[$kdblok],2) . "</td>";
					$tab.="<td align=center>" . $nmsat[$kdkeg] . "</td>";
					$tab.="<td align=right ".$luasclr.">" . @numb_format($preskerja[$kdblok][$kdkeg],2) . "</td>";
					$tab.="<td align=right>" . @numb_format($subjhl[$kdblok][$kdkeg],2) . "</td>";
					$tab.="<td align=right>" . @numb_format($subumr[$kdblok][$kdkeg]) . "</td>";
					$tab.="<td align=right>" . @numb_format($supahpre[$kdblok][$kdkeg]) . "</td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=right></td>";
					$tab.="</tr>";					
				}
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=".$jlhcolspan." bgcolor=#2C3E50></td>";
			$tab.="</tr>";
			
			
			$tab.="<tr class=rowcontent style=background-color:#A3E4D7>";
			$tab.="<td colspan=3 align=center><input value=".$no." style=display:none id=jlhbrsdt><b>GRAND TOTAL</b></td>
					<td colspan=5 align=center></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@numb_format($tjhk,2)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@numb_format($tumr)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@numb_format($tinsentif)."</b></td>";
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
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$stspremi=$bar['premi'];
			$satkegiatan=$bar['satuan'];
		
	#========================== Setup Kegiatan ============================
	#========================== Premi Kegiatan ============================
		$str = "select * from ".$dbname.".kebun_5premibkm where kodekegiatan='".$kegiatan."' and unit='".$kodeorg."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$basis=$bar['basis'];
			$rppremilebihbasis=$bar['premilebihbasis'];
			
			$premibasis=$premilebihbasis=0;
			if($prestasi>=$basis){
				$premibasis=$bar['premibasis'];
			}
			if((floatval($prestasi)-floatval($basis))>0){
				$premilebihbasis=($prestasi-$basis)*$rppremilebihbasis;
			}
		
	#========================== Premi Kegiatan ============================
	#============================== Tipe Kary =============================
		$str = "select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$tipe=$res->fetch();
			$tipeKary=$tipe['tipekaryawan'];
		
	#============================== Tipe Kary =============================
	#=============================== Get Ha ===============================
		$str = "select * from ".$dbname.".setup_blok where kodeorg='".$blok."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$luasblok=$bar['luasareaproduktif'];
			$stsblok=$bar['statusblok'];
	#=============================== Get Ha ===============================
	#====================== Ambil Daftar Kegiatan =========================
		$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".setup_kegiatan
				where 1=1 and kelompok='".$stsblok."' and status='1' order by kodekegiatan asc, namakegiatan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$d=substr($bar['kodekegiatan'],0,5);
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
				$optKeg.="<optgroup label='".$nmorg[$d]."'>";
			}
			$optKeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){
				$optKeg.="</optgroup>";
			}
		}
	#====================== Ambil Daftar Kegiatan =========================

	echo $stspremi."######".$basis."######".$premibasis."######".$premilebihbasis."######".$tipeKary."######".$luasblok."######".$satkegiatan."######".$rppremilebihbasis."######".$optKeg;
	break;
	
	case'getumr':
	#=============================== Get UMR ==============================
		$tahun=substr(tanggalsystemn($tgl),0,4);
		$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$karyawanid."' and tahun=".$tahun." and idkomponen in ('1')";#exit("Warning :".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$Umr=$res->fetch();
			$umrHarian=$Umr['nilai']/25;
		
		if($umrHarian==0){
			#exit("Warning : Gaji Pokok Karyawan belum ada.");
		}
	#=============================== Get UMR ==============================

	echo $umrHarian;
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
		
		$param['qtymat'] =  round($param['qtymat'],3);
		
		
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
		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['notransaksi']!='' and $param['karyawanid']!='' and $param['kegiatan']!='' and $param['blok']!='' and $param['prestasi']!='0' and (($param['jhk']!='0' and $param['upah']!='0') or $param['premi']!='0')){
		
			# Cari status blok
			$optstsblok = makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$param['blok']."'");
			#===== buat nomor transaksi =====
			# Data Capture & Reform
			$param['tipetransaksi'] = $optstsblok[$param['blok']];
			$param['tgl'] = tanggalsystem($param['tgl']);
			
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
			$str = "select * from " . $dbname . ".kebun_prestasi where nobkm='".$param['nobkm']."'";
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
				$str = "insert into " . $dbname . ".kebun_aktifitas (`notransaksi`, `tipetransaksi`, `tanggal`, `nobkm`, `kodeorg`, `nikmandor`, `nikmandor1`, `nikasisten`, `keranimuat`, `jurnal`, `nospk`, `updateby`)
				values ('".$notransaksi."','".$param['tipetransaksi']."','".$param['tgl']."','" . $param['nobkm'] . "','" . $kodeorg . "','".$mandor."','".$mandor1."','".$asst."','".$kerani."','0',null,'" . $_SESSION['standard']['userid'] . "')";
				$owlPDO->exec($str);
				
				$param['notransaksi']=$notransaksi;
			}else{
				$str = "update " . $dbname . ".kebun_aktifitas set `nikmandor`='".$mandor."', `nikmandor1`='".$mandor1."',`keranimuat`='".$kerani."',`nikasisten`='".$asst."' where `nobkm`='".$param['nobkm']."'"; #exit("error".$str);
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
			
			
			
			# Buat nomor urut
			$sql = "select max(nourut) as nourut from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."' limit 1"; 
			$res=fetchData($sql);
			
			# ==========================================================================================
			$optstskeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok',"kodekegiatan='".$param['kegiatan']."'");
			if($optstsblok[$param['blok']]!=$optstskeg[$param['kegiatan']]){
				throw new PDOException("Kode kegiatan salah, kode kegiatan : ".$optstskeg[$param['kegiatan']]." sementara blok : ".$optstsblok[$param['blok']]."");
			}
			# ==========================================================================================
			$cols = array(
						'nobkm','notransaksi','nourut','nik','nikpemel','kodekegiatan','kodeorg','hasilkerja','jumlahhk','tahuntanam','upahpremi'
					);
			$data = array(
						$param['nobkm'],$param['notransaksi'],($res[0]['nourut']+1),'-',$param['karyawanid'],$param['kegiatan'],$param['blok'],$param['prestasi'],$param['jhk'],'0',$param['premi']
					);
			
			# Insert kebun_prestasi
			$query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
			#exit("error".$query);
			$owlPDO->exec($query);
			# ==========================================================================================
			
			$cols = array(
						'notransaksi','nourut','nik','absensi','jhk','umr','insentif','hasilkerja'
					);
			$data = array(
						$param['notransaksi'],($res[0]['nourut']+1),$param['karyawanid'],'H',$param['jhk'],$param['upah'],$param['premi'],$param['prestasi']
					);

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
		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['notransaksi']!='' and $param['karyawanid']!='' and $param['kegiatan']!='' and $param['blok']!='' and $param['prestasi']!='0' and (($param['jhk']!='0' and $param['upah']!='0') or $param['premi']!='0')){
		
			# Ambil nomor urut kary
			$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and kodekegiatan='".$param['kegiatan']."'";
			$res=fetchData($str);
			$nourut=$res[0]['nourut'];
					
			# Validasi penginputan
			cekPrestasi($param);
			
			# ==========================================================================================
			$data = array(
						'hasilkerja'=>$param['prestasi'],
						'jumlahhk'=>$param['jhk'],
						'upahpremi'=>$param['premi']
					);
			$where = "notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and kodekegiatan='".$param['kegiatan']."'";
			
			# Update kebun_prestasi
			$query = updateQuery($dbname,'kebun_prestasi',$data,$where);
			$owlPDO->exec($query);
			# ==========================================================================================
			$data = array(
						'hasilkerja'=>$param['prestasi'],
						'jhk'=>$param['jhk'],
						'umr'=>$param['upah'],
						'insentif'=>$param['premi']
					);
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
			$str = "delete from " . $dbname . ".sdm_absensidt where norefrensi='".$notransaksi."' and nobkm='".$nobkm."'"; 
			$owlPDO->exec($str);
		}else{
			$str = "delete from " . $dbname . ".sdm_absensidt where nobkm='".$nobkm."'";
			$owlPDO->exec($str);
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
				unlink($pathx);
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
			exit("Error : Material untuk kegiatan : ".$nmkeg[$kegiatan]."\nBlok : ".$blok." sudah pernah di input, silahkan hapus terlebih dahulu melalui tab Material");
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

    case'loaddata':
		#validasi
		stsawal($param);
		$kodorg=array();
        $where="";
		// $str="select distinct(a.kodeorganisasi) as kodeorganisasi, b.namaorganisasi, b.alokasi from ".$dbname.".user_orgdetail a left join ".$dbname.".organisasi b on a.kodeorganisasi=b.kodeorganisasi where length(b.kodeorganisasi)=4 and a.namauser='".$_SESSION['standard']['username']."' order by b.kodeorganisasi";
        // $res = fetchdata($str);
        // foreach ($res as $bar) {
			// $kodorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		// }
		$kodorg[$_SESSION['empl']['lokasitugas']]=$_SESSION['empl']['lokasitugas'];
		
		$where.= "and a.kodeorg in (".getOrgDetail(24).")";
		$whsdm= "and substr(kodeorg,1,4) in (".getOrgDetail(24).")";
		
		$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABKRN'"; 
		$res = fetchdata($str);
		@$arrjab = explode(',', $res[0]['nilai']);
		
		#karena list data di filter berdasarkan pembuat terkadang user mencari nomor transaksi yg di input oleh user lain tidak muncul, solusinya munculkan semua transaksi pada saat pencarian walau bukan ybs pembuatnya namun tidak bisa di edit.
		$subbg='isi';
		if($_SESSION['empl']['subbagian']==''){
			$subbg='kosong';
		}
		
		if($_SESSION['empl']['subbagian']=='' and in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
			$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and (a.kodeorg like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."' or a.kodeorg is null)"; 
		}elseif($_SESSION['empl']['subbagian']==''){
			$where.= " and (a.kodeorg = '".$_SESSION['empl']['lokasitugas']."' or a.kodeorg is null)";
		}else{
			$where.=" and (a.kodeorg like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."' or a.kodeorg is null)"; 
		}
		
		$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		$whsdm.=" and substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."'";
		
        if ($divsch != '') {
            $where.=" and a.kodeorg like '" . $divsch . "%' ";
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
		if ($nobkmsch != '') {
            $where.=" and a.nobkm like '%".$nobkmsch."%' ";
        }
		if ($mandorsrc != '' and $mandorsrc != 'blank') {
            $where.=" and a.nikmandor like '%".$mandorsrc."%' ";
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

        #$sql = "select count(distinct a.notransaksi) as notr from " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi not in ".$wh." " . $where . "";
        
		$sql = "select count(distinct a.notransaksi) as notr from " . $dbname . ".kebun_aktifitas a where 1=1 and a.tipetransaksi not in ".$wh." " . $where . "";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=22 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
		
		$ttl=array();
		// $strn = "select norefrensi, nobkm, kodeorg,sum(umr) as umr, sum(premi) as premi, sum(hk) as hk from ".$dbname.".sdm_absensidt where norefrensi!='' and nobkm!='' ".$whsdm." group by norefrensi, nobkm"; 
		// $resn = fetchdata($strn);
		// foreach ($resn as $bar) {
			// $ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['umr']+$bar['premi'];
			// // premi bkm jangan pake $resn[0]['premi']
			// $hkab[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk'];
			// $umrab[$bar['norefrensi']][$bar['nobkm']]+=$bar['umr'];
			// $premab[$bar['norefrensi']][$bar['nobkm']]+=$bar['premi'];
		// }
		
		$notrx=1;
        #$str = "SELECT a.*, substr(b.kodeorg,1,6) as divisi, sum(b.hasilkerja) as jjg, sum(b.jumlahhk) as hk, sum(b.upahpremi) as premi FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi not in ".$wh." " . $where . " group by a.notransaksi order by a.nobkm desc, a.notransaksi desc limit " . $offset . "," . $limit . ""; 
        
		$str = "SELECT * FROM " . $dbname . ".kebun_aktifitas a where 1=1 and a.tipetransaksi not in ".$wh." " . $where . " group by a.notransaksi order by a.nobkm desc, a.notransaksi desc limit " . $offset . "," . $limit . ""; 
		
		
		$in="SELECT distinct notransaksi FROM " . $dbname . ".kebun_aktifitas a where 1=1 and a.tipetransaksi not in ".$wh." " . $where . "";
		$sql="SELECT notransaksi, substr(kodeorg,1,6) as divisi, sum(hasilkerja) as jjg, sum(jumlahhk) as hk, sum(upahpremi) as premi FROM " . $dbname . ".kebun_prestasi where notransaksi in (".$in.") group by notransaksi ";
		$req = fetchdata($sql);
        foreach ($req as $val){
			
		}
		
		#exit('error'.$str);
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $isi=$a=$xx=$cl=$abs='';
            $no+=1;
			$n=explode("/",$bar['nobkm']);
			$a=(($n[0].$n[3])*1)%2;
			if($a==0){
				#$xx.=" style=background-color:#B3F7B3";
			}
			#kehadiran
			$strx = "select sum(umr) as umr from ".$dbname.".kebun_kehadiran where notransaksi='".$bar['notransaksi']."'"; 
			$resx = fetchdata($strx);
			
			// #absensi
			// $strn = "select kodeorg,sum(umr) as umr, sum(premi) as premi, sum(hk) as hk from ".$dbname.".sdm_absensidt where norefrensi='".$bar['notransaksi']."' and nobkm='".$bar['nobkm']."'"; 
			// $resn = fetchdata($strn);
			// $ttl=$resn[0]['hk']+$resn[0]['umr']+$resn[0]['premi'];
			
			
			if($bar['divisi']=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]==0){
				$cl=" style=background-color:red; title=\"Data detail belum ada.\"";
			}elseif($bar['divisi']=='' and $ttl[$bar['notransaksi']][$bar['nobkm']]>0){
				$cl=" style=background-color:yellow; title=\"Data hanya absensi.\"";
				$bar['divisi']=$resn[0]['kodeorg']; $abs="absensi";
			}
			
			$hari=$c="";
			$hari = date('D', strtotime($bar['tanggal']));
			if($hari=='Sun'){
				$c="style=\"color:red\"";
			}
			if($hari=='Fri'){
				$c="style=\"color:blue\"";
			}
			
			$where="karyawanid='".$bar['keranimuat']."' or karyawanid='".$bar['nikmandor']."' or karyawanid='".$bar['nikmandor1']."' or karyawanid='".$bar['nikasisten']."' or karyawanid='".$bar['updateby']."'";
			$divkar = makeOption($dbname, 'datakaryawan', 'karyawanid,subbagian',$where);
			$a=$a1=$b=$b1=$d=$d1="";
			if($divkar[$bar['nikmandor']]!=$bar['divisi']){
				$a="<br><font size=1px color=blue><b><i>".$divkar[$bar['nikmandor']]."</i></b></font>";				
				$a1="title=\"Karyawan asistensi\"";				
			}
			if($divkar[$bar['nikmandor1']]!=$bar['divisi']){
				$b="<br><font size=1px color=blue><b><i>".$divkar[$bar['nikmandor1']]."</i></b></font>";				
				$b1="title=\"Karyawan asistensi\"";				
			}
			if($divkar[$bar['keranimuat']]!=$bar['divisi']){
				$d="<br><font size=1px color=blue><b><i>".$divkar[$bar['keranimuat']]."</i></b></font>";				
				$d1="title=\"Karyawan asistensi\"";				
			}
			
            $tab.="<tr class=rowcontent ".$xx." ".$cl." id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['nobkm'] . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
            $tab.="<td align=center>" . $bar['divisi'] . "</td>";
            $tab.="<td align=center ".$c.">" . hari($bar['tanggal'],'ID') . "</td>";
            $tab.="<td align=center ".$c.">" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td align=center>" . @numb_format($bar['hk']+$hkab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
            $tab.="<td align=right>" . @numb_format($resx[0]['umr']+$umrab[$bar['notransaksi']][$bar['nobkm']]) . "</td>";
            $tab.="<td align=right>" . @numb_format($bar['premi']+$premab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
			$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$where);
            $tab.="<td align=center ".$a1.">" . @$nmkar[$bar['nikmandor']] . "".$a."</td>";
            $tab.="<td align=center ".$b1.">" . @$nmkar[$bar['nikmandor1']] . "".$b."</td>";
            $tab.="<td align=center ".$d1.">" . @$nmkar[$bar['keranimuat']] . "".$d."</td>";
            $tab.="<td align=center>" . @$nmkar[$bar['nikasisten']] . "</td>";
            $tab.="<td align=center>" . @$nmkar[$bar['updateby']] . "</td>";
            

            if ($bar['jurnal'] == 0) {
				if($subbg=='isi' and $bar['updateby']!=$_SESSION['standard']['userid']){
					$isi.="<td width=20px></td><td width=20px></td>";
				}else{					
					$isi.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
						onclick=\"edit('".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".$bar['kodeorg']."','".$bar['nobkm']."','".$bar['nikmandor']."','".$bar['nikmandor1']."','".$bar['nikasisten']."','".$bar['keranimuat']."','".$no."');\" ></td>";
						
					$isi.="<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
						onclick=\"del('".$bar['notransaksi']."','".$no."');\" ></td>";
				}

				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$isi.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['notransaksi']."','".$no."','".$abs."');\" ></td>";
				} else {
					$isi.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting'></td>";
				}
            }elseif ($bar['jurnal'] == 1) {
				$kdpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorg']."'");
				
				$isi.="<td style=width:20px></td><td style=width:20px></td>";
				$isi.="<td align=center style=width:20px><img src=images/skyblue/posted.png class=zImgBtn class=zImgBtn height='30'  title='Posted\nClick untuk melihat Jurnal' onclick=getjurnal('".$kdpt[$bar['kodeorg']]."','".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".tanggalnormal($bar['tanggal'])."')></td>";
			}else{
				$isi.="<td width=20px></td><td width=20px></td><td width=20px></td>";
			}
			
			$isi.="<td align=center style=width:20px><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$bar['notransaksi']."');\" ></td>";
			
            $isi.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailPDF('".$bar['notransaksi']."','".$no."','event','".$statusblok."');\" ></td>";
            $isi.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','".$statusblok."','html');\" ></td>";
            $isi.="<td align=center style=width:20px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','".$statusblok."','excel');\" ></td>";

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
        $footd.="<tr><td colspan=22 align=center>";
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
	case 'showupload':
		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td id='notranupload'>". $param['notransaksi']."</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td></td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>";

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;
	
	case 'submitfile':
		$data= $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				#cek duplikasi nama file
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."'";
				$res=fetchData($str);
				if(count($res)>0){
					exit("Warning : Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('".$param['notransaksi']."','".$filename."','".$filetype."','BKM','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
			}
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
			unlink($pathx);
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
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:950px;height:500px;' type='application/pdf'>";
		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."'>";
		}
		
		echo $tab;
	break;	
	case'postingabsensi':
		$strupd=" update ".$dbname.".kebun_aktifitas set jurnal='1' where notransaksi='".$param['notransaksi']."'";
		try{$owlPDO->exec($strupd);			
			echo "Posting Sukses.";
		}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
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
			$trans.="No => ".$bar['notransaksi']." => ".$bar['jhk']." HK\n";
		}
		
		if(floatval($param['jhk'])+$jlhhk>1){
			throw new PDOException("Jumlah HK karyawan lebih dari 1, HK yang sudah tersimpan sebesar = ".$jlhhk." HK\n\n ".$trans."");
		}		
		
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
		
	if(@$jumtrans>0 ){
		throw new PDOException("Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani");
	}

	# Cek Perawatan
	# Jika sudah ada di perawatan tidak bisa input panen
	# Jika karyawan ada pekerjaan panen dan perawatan, maka harus malekukan input panen terlebih dahulu
	$qAbs = selectQuery($dbname,'kebun_prestasi_vs_hk','karyawanid,sum(hkpanenperhari) as jhk',
								"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jhkpanen = $resAbs[0]['jhk'];
	
	if(floatval($jhkpanen)!='0' and $param['jhk']>'0') {
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
		$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."' and norefrensi!='".$param['notransaksi']."' ";
		$res = fetchData($str);
		if(count($res)>'0') {
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM dengan nomor transaksi : ".$param['notransaksi'].".");
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
	
	
	$str = "select * from " . $dbname . ".setup_blok where kodeorg='".$param['blok']."'"; 
	$res = fetchData($str);
	foreach($res as $val){
		$luasttlblok =$val['luasareaproduktif'];
		$pokokttlblok=$val['jumlahpokok'];
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
	
	$notrhasil.="\nTransaksi saat ini = ".number_format($param['prestasi'],2)." ".$satsetup[$param['kegiatan']]."\n";
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
				throw new PDOException("Luas dikerjakan sudah melebihi luas blok,\nLuas blok : ".$b." HA\nLuas dikerjakan : ".$a." HA\n".$notrhasil."");
			}
		}elseif(strtolower($satsetup[$param['kegiatan']])=='pokok' or strtolower($satsetup[$param['kegiatan']])=='pkk'){
			$a=number_format(($hasilkerja+$param['prestasi'])-$hasilkerjaedit,2);
			$a=str_replace(",","",$a);
			$b=number_format($pokokttlblok,2);
			$b=str_replace(",","",$b);
			if($a>$b){
				throw new PDOException("Pokok dikerjakan sudah melebihi jumlah pokok blok,\nPokok blok : ".$b." PKK\nPokok dikerjakan : ".$a." PKK\n".$notrhasil."");
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
?>	