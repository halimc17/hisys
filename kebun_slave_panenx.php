<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$path          = "fileupload/bkm/";
$param         = $_POST;
$method        = checkPostGet('method', '');
$notransaksi   = checkPostGet('notransaksi', '');
$namakary      = checkPostGet('namakary', '');
$tt            = checkPostGet('tt', '');
$tgl           = checkPostGet('tgl', '');
$tgl           = checkPostGet('tgl', '');
$kodeorg       = checkPostGet('kodeorg', '');
$filterdivisi  = checkPostGet('filterdivisi', '');
$showpermandor = checkPostGet('showpermandor', '');
$mandor        = checkPostGet('mandor', '');
$mandor1       = checkPostGet('mandor1', '');
$asst          = checkPostGet('asst', '');
$kerani        = checkPostGet('kerani', '');
$nobkm         = checkPostGet('nobkm', '');
$blok          = checkPostGet('blok', '');
$karyawanid    = checkPostGet('karyawanid', '');
$jjgpanen      = checkPostGet('jjgpanen', '');
$mode          = checkPostGet('mode', '');
$sts           = checkPostGet('sts', '');
$kontan        = checkPostGet('kontan', '');
$jlhdenda      = checkPostGet('jlhdenda', '');

$divsch        = checkPostGet('divsch', '');
$tglmulai      = tanggalsystemn(checkPostGet('tglmulai', ''));
$tglselesai    = tanggalsystemn(checkPostGet('tglselesai', ''));
$notransaksisch= checkPostGet('notransaksisch', '');
$postingsrc    = checkPostGet('postingsrc', '');
$periodesch    = checkPostGet('periodesch', '');

$jab   = getPostingJabatan('panen'); 
$tmpTgl= explode('-',$tgl);

@$param['hapanen']   =str_replace(",","",$param['hapanen']);
@$param['jjgpanen']  =str_replace(",","",$param['jjgpanen']);
@$param['brdpanen']  =str_replace(",","",$param['brdpanen']);
@$param['kgpanen']   =str_replace(",","",$param['kgpanen']);
@$param['upah']      =str_replace(",","",$param['upah']);
@$param['basis']     =str_replace(",","",$param['basis']);
@$param['lbasis']    =str_replace(",","",$param['lbasis']);
@$param['denda_rp']  =str_replace(",","",$param['denda_rp']);
@$param['tt']        =str_replace(",","",$param['tt']);
@$param['bjr']       =str_replace(",","",$param['bjr']);

for($i=1;$i<=$jlhdenda;$i++){
	@$param['penalti'.$i] =str_replace(",","",$param['penalti'.$i]);
}


$dendapanen=array();
$iddendapnn=array();

$str = "select max(id) as max,a.*,b.* from ".$dbname.".kebun_5dendapanen a left join ".$dbname.".kebun_5kodedendapanen b on a.kodedenda=b.kodedenda where 1=1 and a.kodeorg='".$kodeorg."' group by id order by b.id asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$iddendapnn[$bar['id']]=$bar['id'];
	$dendapanen[$bar['id']]=$bar['kodedenda'];
	$namadenda[$bar['id']] =$bar['deskripsi'];
	$tp[$bar['id']]        = "title=\"".$bar['kodedenda']." => ".$bar['deskripsi']." = (".$bar['denda']." / ".$bar['jenisdenda'].")\"";
	$tplistdata[$bar['id']]= "title=\"".$bar['kodedenda']." => ".$bar['deskripsi']." = (".$bar['denda']." / ".$bar['jenisdenda'].")";
	$harga[$bar['id']]     = $bar['denda']; 
	$sat[$bar['id']]       = $bar['jenisdenda']; 
	$hp[$bar['id']]        = $bar['kodedenda'];
	$maxdenda=$bar['max'];
}
			
#============== KHT, KHL dan Kontrak ======================
	$whereKary='';
	if($filterdivisi!=''){
		$unitsendiri= substr($kodeorg,0,4);
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
			if(count($dt)>0 and substr($filterdivisi,0,4)!=$kodeorg){			
				$whereKary.=" and karyawanid in ('".implode("','",$dt)."')";
			}elseif(count($resx)>0 and substr($filterdivisi,0,4)!=$kodeorg){			
				$whereKary.=" and subbagian in ('".implode("','",$divisiasal)."') and tipekaryawan in ('1','2','3','4','6')";
			}else{
				$whereKary.=" and tipekaryawan in ('1','2','3','4','6')";
			}
		}else{			
			$whereKary.=" and subbagian = '".$filterdivisi."'";
			$whereKary.=" and tipekaryawan in ('1','2','3','4','6')";
		}
	}else{
		$whereKary.= " and lokasitugas='".$kodeorg."'";
		$whereKary.=" and subbagian in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and tipe = 'AFDELING')";
	}

	
	$whereKary.= " and tipekaryawan in (1,2,3,4,6) and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
	
	$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.subbagian from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." order by a.subbagian,a.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
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
	}
	#============== KHT, KHL dan Kontrak ======================
	#===================== Kode Blok ==========================
	$whereBlok=$whererpnn='';
	if($filterdivisi!=''){
		if(substr($filterdivisi,0,4)!=$kodeorg){
			//$whereBlok.= " and substr(a.kodeorganisasi,1,6) = '".$param['divisi']."'";
			$whereBlok.= " and substr(a.kodeorganisasi,1,4) ='".$kodeorg."'";
		}else{			
			$whereBlok.=" and substr(a.kodeorganisasi,1,6) = '".$filterdivisi."'";
		}
	}else{
		$whereBlok.= " and substr(a.kodeorganisasi,1,4) ='".$kodeorg."'";
	}
	
	$whereBlok.= " and b.statusblok = 'TM' and substr(a.kodeorganisasi,1,4)='".$kodeorg."'";
	$whererpnn.=" and kodeorganisasi in (select blok from ".$dbname.".kebun_rekappnn where tanggal = '".tanggalsystem($tgl)."')";
	$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".organisasi a 
			left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
			where a.tipe='BLOK' ".$whereBlok." ".$whererpnn." order by substr(a.kodeorganisasi,1,6), a.kodeorganisasi asc"; //exit('error'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$a=substr($bar['kodeorganisasi'],0,6);
		if($a!=$m){			
			$optBlok.="<optgroup label='".getNamaOrg($a)."'>";
		}
		
		$d=$bar['tahuntanam'];
		if($d!=$n){			
			//$optBlok.="<optgroup label='Tahun Tanam ".$d."'>";
		}
		$optBlok.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
		
		
		$n=$d;
		if($d!=$n){			
			//$optBlok.="</optgroup>";
		}
		$m=$a;
		if($a!=$m){			
			$optBlok.="</optgroup>";
		}
	}
	#===================== Kode Blok ==========================
switch ($method) {
    case'detail':
		#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
		$sekarang=  tanggalsystem($tgl);
		if($sekarang<$_SESSION['org']['period']['start']){
			exit("Validation Error : ".$tgl." Date out of range ".$_SESSION['org']['period']['start']);
        }
		$tgl=tanggalsystemn($tgl);
		if(count(@$iddendapnn)<=0){
			exit("Warning : Harga denda panen belum ada, silahkan tambahkan melalui menu : Kebun - Setup - Denda Panen");
		}
		
		if($tgl>='2021-12-01'){
			exit("Warning : Menu ini hanya bisa digunakan untuk transaksi dibawah tanggal 01 Desember 2021.<br>Silahkan menggunakan menu <b>Kegiatan Panen (Kg) SPB</b>");
		}
		
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
        // $sql = "select * from " . $dbname . ".kebun_aktifitas where "."notransaksi='" . $notransaksi . "'";
        // $res = fetchData($sql);
        if ($mode=='edit') {
            $str = "update " . $dbname . ".kebun_aktifitas set `nobkm`='".$nobkm."', `nikmandor`='".$mandor."', `nikmandor1`='".$mandor1."',`nikasisten`='".$kerani."' where `notransaksi`='".$notransaksi."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        } else {
			// $sql = "select * from " . $dbname . ".kebun_aktifitas where "."notransaksi='" . $notransaksi . "'";
			// $res = fetchData($sql);
			// if (count($res) > 0) {
				// $notrtemp = explode("/",$notransaksi);
				// $fWhere = "tanggal='".$notrtemp[0]."' and kodeorg='".$notrtemp[1]."' and tipetransaksi='PNN'";
				// $str = "select max(substr(notransaksi,-3)) as notr from " . $dbname . ".kebun_aktifitas where ".$fWhere." limit 1";
				// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				// $res->setFetchMode(PDO::FETCH_ASSOC);
				// $bar=$res->fetch();
				
				// $trtemp = addZero((intval($bar['notr'])+1),3);
				// $notransaksi=str_replace($notrtemp[3],$trtemp,$notransaksi);
			// }
			// if($notransaksi==''){
				// exit('Warning: Nomor Transaksi tidak boleh kosong.');
			// }
			
			#===== buat nomor BKM =====
			$data = $_POST;
			# Data Capture & Reform			
			$data['tgl'] = tanggalsystem($data['tgl']);
			
			# Get Existing Data
			$fWhere = "tanggal='".$data['tgl']."' and kodeorg='".$data['kodeorg']."' and tipetransaksi='PNN'";
			$fQuery = selectQuery($dbname,'kebun_aktifitas','nobkm',$fWhere);
			$tmpNo = fetchData($fQuery);
			
			# Generate No Transaksi
			if(count($tmpNo)==0) {
				$notransaksi = $data['tgl']."/".$data['kodeorg']."/PNN/001";
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
				$notransaksi = $data['tgl']."/".$data['kodeorg']."/PNN/".$currNo;
			}
			
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
			
			$nobkm = $notransaksi;
			
			if(strlen($notransaksi)<21){
				exit('Warning: Nomor Transaksi tidak boleh kosong.');
			}
			
			
			$str = "insert into " . $dbname . ".kebun_aktifitas (`notransaksi`, `tipetransaksi`, `tanggal`, `nobkm`, `kodeorg`, `nikmandor`, `nikmandor1`, `nikasisten`, `keranimuat`, `jurnal`, `nospk`, `updateby`)
			values ('".$notransaksi."','PNN','".$tgl."','" . $nobkm . "','" . $kodeorg . "','".$mandor."','".$mandor1."','".$kerani."','','0',null,'" . $_SESSION['standard']['userid'] . "')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		#=== insert header ===
        // $tab=OPEN_BOX();
		#==== Form Judul Detail ====
		# Divisi
		$optDivisi='';
		if($_SESSION['empl']['subbagian']!=''){
			#$optDivisi="<option value='".$_SESSION['empl']['subbagian']."'>".$_SESSION['empl']['subbagian']."</option>";
		}
			$optDivisi.="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
			
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' and kodeorganisasi like '".$kodeorg."%'";
		$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $resstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $resstr->fetch()) {
			if($_SESSION['empl']['subbagian']==$res['kodeorganisasi']){
				$optDivisi.="<option value=".$res['kodeorganisasi']." selected>".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}else{
				$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}
		}
		
		$str="select * from ".$dbname.".kebun_5asistensi where kodeorgtujuan ='".$param['kodeorg']."' and tanggal<='".tanggalsystemn($param['tgl'])."' and tanggalsampai>='".tanggalsystemn($param['tgl'])."' and posting='1' and (tipetrans='PNN' or tipetrans='')";
		
		$resx=fetchdata($str);
		foreach($resx as $res){
			$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['kodeorgasal']."'");
			//$optUnit.="<option value=".$res['kodeorgasal'].">".$res['kodeorgasal']." - ".$nmorg[$res['kodeorgasal']]."</option>";
			$dtunit[$res['kodeorgasal']]=$res['kodeorgasal'];
			
			
			$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['divisiasal']."'");
			$optDivisi.="<option value=".$res['divisiasal'].">".$res['divisiasal']." - ".$nmorg[$res['divisiasal']]."</option>";
		}
		
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tgl."' and (kebun='GLOBAL' or kebun='".$kodeorg."')";
		$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
		$queorg->setFetchMode(PDO::FETCH_ASSOC);
		$roworg=$queorg->fetch();
		$day = date('D', strtotime($tgl)); 
			if(@$roworg['keterangan']=='libur'){
				$jenispremi='LIBUR';
			} else if ($day=='Sun'){
				$jenispremi='LIBUR';
			}else{
				$jenispremi='KERJA';
			}
        $tab.="<table><td valign=top>
			<fieldset style=float:left><legend>Filter</legend>
				<table height=25px>
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td><select style=\"width:150px;\"  onchange=\"getdata()\" id=filterdivisi>".$optDivisi."</select></td>
					<td>&nbsp;</td>
					<td><input type=checkbox onchange=\"getdatamandor()\" id=showpermandor></td>
					<td>Per Mandor</td>
				</table>
			</fieldset>
			</td>
			<td valign=top>
			<fieldset style=float:left>
				<legend>Kontanan</legend>
				<table height=25px width=100%><td align=center>
					<input type=checkbox onclick=getkontan('".$jenispremi."') id=kontan><span id=info_kontan>".$_SESSION['lang']['tidak']."</span>
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
				<legend>Info</legend>
				<table height=25px><td><font color=red><b>* </font>".$_SESSION['lang']['notifobligatory']."</b></td>
				<td>&nbsp;||&nbsp;</td><td>Blok yang di munculkan hanya blok yg ada di Menu : <b>Rekap Panen</b></td>
				<td>&nbsp;||</td><td>
					<a href='fileupload/simulasipanen.xlsx' download>
						<img class='zImgBtn' src='images/modify.png' title=\"Download file Excel simulasi !!!\" style='position:relative;top:3px;left:3px;'>
					</a>
				</td>
				<td>&nbsp;||</td><td>
					<img class='zImgBtn' onclick=getbasispnn() src='images/book_icon.gif' title=\"Preview Basis Panen\" style='position:relative;top:3px;left:3px;'>
				</td>
				</table>
			</fieldset>
			</td></table>
			<fieldset>
			<legend>" . $_SESSION['lang']['detail'] . "</legend>
			
			<table border=0 cellpadding=1 cellspacing=1 class=sortable >
			<thead><tr class=rowheader height=25px>";
			$rows="rowspan=2";	
			$tab.="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['blok'] . "</th>
				<th align=center ".$rows." width=30px>".$_SESSION['lang']['tahuntanam'] . "</th>
				<th align=center colspan=4 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</th>
				<th hidden align=center ".$rows." width=50px><font color=red><b>* </font></b>".$_SESSION['lang']['upah']."</th>
				<th hidden align=center colspan=2>".$_SESSION['lang']['premilebihbasis']."</th>
				<input id=jumlahkolomdenda value=".count($dendapanen)." style=display:none>
				<th align=center colspan=".count($dendapanen)." id=phead name=inputdenda[] title='Click to Hide' onclick=hidedendav2('inputdenda[]') ><font color=Orange><b>".$_SESSION['lang']['denda']."</b></font></th>
				
				<th align=center ".$rows." title='Click to Unhide' id=pheadrp onclick=hidedendav2('inputdenda[]') style=width:50px;color:Orange;font-weight:bold;>".$_SESSION['lang']['denda']." Rp</th>
				
				<th align=center width=30px ".$rows." colspan=2>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr class=rowheader height=25px>
				<th align=center width=45px><font color=red><b>* </font></b>".$_SESSION['lang']['ha'] . "</th>
				<th align=center width=45px><font color=red><b>* </font></b>".$_SESSION['lang']['jjg'] . "</th>
				<th align=center width=45px>".$_SESSION['lang']['brondol'] . "</th>
				<th align=center width=45px>".$_SESSION['lang']['kg'] . "</th>
				<th hidden align=center width=45px>".$_SESSION['lang']['basic'] . "</th>
				<th hidden align=center width=45px>".$_SESSION['lang']['lebihbasis'] . "</th>
				<input style=display:none id=kodeiddenda value='".implode("##",$iddendapnn)."'>
				";
				#denda header
				foreach($dendapanen as $iddenda => $kddenda){
					$tab.="<th align=center ".$tp[$iddenda]." width=30px style=display:none name=inputdenda[] id=p".$iddenda.">".$kddenda."</th>";
				}
				
		$tab.="</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		#=== Isi input detail ===
		$tab.="<tbody id=inputdetail>
				<script>inputdetail()</script>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input detail ===	
        $tab.="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
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
						<td><button class=mybutton onclick=cancelcari()>" . $_SESSION['lang']['cancel'] . "</button></td>
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
		echo $notransaksi."####".$tab;	
		
	break;
	case'inputdetail':
	
	
	echo"<tr class=rowcontent id=row>";
		echo"<td id=no align=center>1</td>
			<td colspan=2><select style=min-width:200px onchange=getDataDetail() id=karyawanid class=select2>".$optKary."</select></td>
			<!--<td style=width:20px><img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>-->
			
			<td colspan=2><select style=width:95px onchange=getDataDetail() id=blok class=select2>".$optBlok."</select></td>
			<!--<td style=width:20px><img id='blok' onclick=z.elSearch('blok',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>-->
			<td><input id=tt disabled class=myinputtextnumber style=\"width:35px;\">
				<input id=bjr style=display:none></td>
			<td><input id=hapanen onkeyup=\"z.numberFormat('hapanen',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td><input id=jjgpanen onkeyup=\"getDataDetail()\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:60px;\"></td>
			<td><input id=brdpanen nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:60px;\"></td>
			<td><input id=kgpanen disabled class=myinputtextnumber style=\"width:50px;\"></td>
			<td hidden><input id=upah disabled class=myinputtextnumber style=\"width:50px;\"></td>
			<td hidden><input id=basis onkeyup=\"z.numberFormat('basis',2)\" disabled class=myinputtextnumber style=\"width:50px;\"></td>
			<td hidden><input id=lbasis onkeyup=\"z.numberFormat('lbasis',2)\" disabled class=myinputtextnumber style=\"width:50px;\"></td>";
			
			#denda detail input
			foreach($dendapanen as $iddenda => $kddenda){
				echo"<td style=display:none id=pd".$iddenda." name=inputdenda[]><input ".$tp[$iddenda]." id=penalti".$iddenda." onkeyup=getHitungDenda(0,this) nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
			}
			
			echo"<td><input id=denda_rp disabled nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:45px;\"></td>
			
			<td width=25px align=center><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
			
			</td>
			<td width=25px align=center>
				<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail()\" src='images/clear.png'/>
			</td>
        </tr><tr height=25px>
			<td colspan=10>
			<td id=pfot name=inputdenda[] colspan=".count($dendapanen).">
			<td></td>
			<td align=center width=25px>
			<input id=jlhbrs style=display:none>
			<img title='Refresh' style=vertical-align:center;width:10px;height:10px;cursor:pointer onclick=\"cancelcari()\" src='images/refresh2.png'/>
			</td>
			<td align=center width=25px>
			<img id=done title='" . $_SESSION['lang']['selesai']."' style=vertical-align:center;width:13px;height:13px;cursor:pointer onclick=\"displayList()\" src='images/foldoq.png'/>
			</td>
        </tr>";
	break;
	
	
	case'getdata':
		echo $optKary."######".$optBlok;
	break;
	
	case'getdatamandor':
	$whereKary='';
	$whereKary= " and tipekaryawan in (1,2,3,4,6) and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
	
	$str = "select a.karyawanid,b.namakaryawan,b.nik, b.subbagian from ".$dbname.".kebun_5mandor a
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where statusaktif='1' and mandorid='".$mandor."' ".$whereKary." order by b.namakaryawan asc";
	$count=fetchData($str);
	$tab='';
	if(count($count)==0){
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=23>Pastikan kolom <b>Mandor</b> pada Header terisi, dan atau daftarkan terlebih dahulu nama karyawan per kemandoran melalui menu : <b>Kebun - Setup - Mandor</b></td>";
		$tab.="</tr>";
	}
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no='';
	while($bar=$res->fetch()){
		$no++;
		$tab.="<tr class=rowcontent id=row".$no.">";
		$tab.="	<td align=center>".$no."</td>
		<td style=display:none><input id=karyawanid".$no." value=".$bar['karyawanid']."></td>
		<td id=kary".$no." colspan=2>".$bar['nik']." - ".$bar['namakaryawan']."</td>
		
		<td style=width:95px><select style=width:95px onchange=\"getDataDetail(".$no.")\" id=blok".$no.">".@$optBlok."</select></td>
		<td style=width:20px><img id='blok".$no."' onclick=z.elSearch('blok".$no."',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
		<td><input id=tt".$no." disabled class=myinputtextnumber style=\"width:35px;\">
			<input id=bjr".$no." style=display:none></td>
		<td><input id=hapanen".$no." onkeyup=\"z.numberFormat('hapanen".$no."',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
		<td><input id=jjgpanen".$no." onkeyup=\"getDataDetail(".$no.")\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:60px;\"></td>
		<td><input id=brdpanen".$no." nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:60px;\"></td>
		<td><input id=kgpanen".$no." disabled class=myinputtextnumber style=\"width:50px;\"></td>
		<td hidden><input id=upah".$no." disabled class=myinputtextnumber style=\"width:50px;\"></td>
		<td hidden><input id=basis".$no." onkeyup=\"z.numberFormat('basis".$no."',2)\" disabled class=myinputtextnumber style=\"width:50px;\"></td>
		<td hidden><input id=lbasis".$no." onkeyup=\"z.numberFormat('lbasis".$no."',2)\" disabled class=myinputtextnumber style=\"width:50px;\"></td>";
		
		#denda detail input
		foreach($dendapanen as $iddenda => $kddenda){
			$tab.="<td style=display:none id=pd".$iddenda."".$no."  name=inputdenda[]><input ".$tp[$iddenda]." id=penalti".$iddenda."".$no." onkeyup=getHitungDenda(".$no.",this) nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		}
		
		$tab.="<td><input id=denda_rp".$no." disabled nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:45px;\"></td>
				
		<td align=center width=25px><input type=hidden id=method value='insert'>
			<img title='" . $_SESSION['lang']['save']."' class=zImgBtn onclick=\"savedetail(".$no.")\" src='images/save.png'/>
		</td>
			<td width=25px align=center>	
			<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail(".$no.")\" src='images/clear.png'/>
		</td>
	</tr>";
	}
	$tab.="<tr height=25px>
		
		<td colspan=10>
		<td id=pfot  name=inputdenda[] colspan=".count($dendapanen).">
		<td align=right>
		<input id=jlhbrs  style=display:none value=".$no.">
			<img title='Refresh' style=vertical-align:center;width:10px;height:10px;cursor:pointer onclick=\"cancelcari()\" src='images/refresh2.png'/>
		</td>
			<td width=25px align=center>
			<img title='" . $_SESSION['lang']['saveall']."' class=zImgBtn onclick=\"saveAll(".$no.")\" src='images/save.png'/>
		</td>
			<td width=25px align=center>
			<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
		</td>
	</tr>";
	
	echo $tab."######".$no;
	break;
	
	case'loaddatadetail':
	
	$rows="rowspan=2";	
	$tab="<table id=tabledt cellpadding=5 cellspacing=1 border=0 class=sortable >
			<thead><tr class=rowheader>
			<th align=center ".$rows." width=20px>No</th>
			<th align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</th>
			<th align=center ".$rows.">".$_SESSION['lang']['blok'] . "</th>
			<th align=center ".$rows." width=30px>".$_SESSION['lang']['tahuntanam'] . "</th>
			<th hidden align=center ".$rows." width=30px>Kontanan</th>
			<th align=center colspan=4>".$_SESSION['lang']['hasilkerja2'] . "</th>
			<th hidden align=center ".$rows.">".$_SESSION['lang']['upah']."</th>
			<th hidden align=center colspan=2>".$_SESSION['lang']['premilebihbasis']."</th>
			
			<th align=center colspan=".count($dendapanen)." style=display:none; id=pheaddt name=listdenda[] title='Click to Hide' onclick=hidedendav2('listdenda[]') ><font color=Orange><b>".$_SESSION['lang']['denda']."</font></b></th>
			<th align=center ".$rows." title='Click to Unhide' id=pheadrpdt onclick=hidedendav2('listdenda[]') style=width:50px;color:Orange;font-weight:bold;>".$_SESSION['lang']['denda']." Rp</th>
			
			<th align=center width=30px ".$rows." colspan=2>" . $_SESSION['lang']['action'] . "</th>
		</tr>
		<tr>
			<th align=center>".$_SESSION['lang']['ha'] . "</th>
			<th align=center>".$_SESSION['lang']['jjg'] . "</th>
			<th align=center width=50px>".$_SESSION['lang']['brondol'] . "</th>
			<th align=center width=50px>".$_SESSION['lang']['kg'] . "</th>
			<th hidden align=center>".$_SESSION['lang']['basic'] . "</th>
			<th hidden align=center width=50px>".$_SESSION['lang']['lebihbasis'] . "</th>";
			
			#denda header list data
			foreach($dendapanen as $iddenda => $kddenda){
				$tab.="<th align=center ".$tp[$iddenda]." width=30px style=display:none name=listdenda[] id=pdt##".$iddenda.">".$kddenda."</th>";
			}
			
		$tab.="</tr>
		</thead>";
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
		
        $no = 0;
       $str = "select a.*,b.namakaryawan,b.nik as nik2, b.subbagian from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid  where a.notransaksi='" . $notransaksi . "' ".$where." order by b.namakaryawan asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$bgcolor=$title=$color=$doublec='';
				$doublec="style=cursor:pointer; title='Double click untuk filter.'";
				
				$strx = "select count(nik) as jmlkary, nik from " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."' and nik='".$bar['nik']."' group by nik";
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$barx = $resx->fetch();
				if(($bar['nik']==$barx['nik']) and ($barx['jmlkary']>1)){
					$bgcolor="style=background-color:orange;";
					$title=" title = 'Karyawan Panen lebih dari 1 blok !'";
				}
				if($bar['subbagian']!=substr($bar['kodeorg'],0,6)){
					$color="style=background-color:cyan;cursor:pointer; title='Info : Karyawan berbeda divisi, tapi jika ada Asistensi maka abaikan pesan ini !' ";
				}
				
				if($bar['nik2']!=''){
					$bar['nik2']=$bar['nik2']." - ";
				}
				
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				$no+=1;
				$align=" align=right ";
				$nn=" style=display:none ";
				$tab.="<tr class=rowcontent ".$bgcolor."".$title.">";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left ".$color." ".$doublec." ondblclick=cariby('".$bar['nik']."','namakary')>" . $bar['nik2'] . $bar['subbagian']. " - " . $bar['namakaryawan'] . "</td>";
				$tab.="<td align=center ".$doublec." ondblclick=cariby('".$bar['kodeorg']."','blok')>" . $nmorg[$bar['kodeorg']]. "</td>";
				$tab.="<td align=center ".$doublec." ondblclick=cariby('".$bar['tahuntanam']."','tt') ".$bgcolor.">" . $bar['tahuntanam'] . "</td>";
				$tab.="<td hidden align=center>" . $bar['keterangan'] . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['luaspanen'],2) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['hasilkerja']) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['brondolan']) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['hasilkerjakg']) . "</td>";
				$tab.="<td hidden align=right>" . @hidezerodecimal($bar['upahkerja']) . "</td>";
				$tab.="<td hidden align=right>" . @hidezerodecimal($bar['upahpremi']) . "</td>";
				$tab.="<td hidden align=right>" . @hidezerodecimal($bar['upahpremilebihbasis']) . "</td>";
				
				#denda list data
				$edit="";
				foreach($dendapanen as $iddenda => $kddenda){
					$tab.="<td ".$align." ".$nn." ".$tplistdata[$iddenda]."\nRp => ".$bar['penalti'.$iddenda]." x ".$harga[$iddenda]." = ".@hidezerodecimal($bar['penalti'.$iddenda]*$harga[$iddenda])." \" width=30px name=listdenda[] id=pddt##".$iddenda."##".$no.">".@hidezerodecimal($bar['penalti'.$iddenda])."</td>";

					@$ttlp[$iddenda]+=$bar['penalti'.$iddenda];
					$edit.="####".$bar['penalti'.$iddenda];
				}
				
				$jlhdenda=count($dendapanen);
				$tab.="<td align=right>" . @hidezerodecimal($bar['rupiahpenalty']) . "</td>";
				
				@$tluas+=$bar['luaspanen'];
				@$tjjg+=$bar['hasilkerja'];
				@$tbrd+=$bar['brondolan'];
				@$tkg+=$bar['hasilkerjakg'];
				@$tupah+=$bar['upahkerja'];
				@$tpbss+=$bar['upahpremi'];
				@$tplb+=$bar['upahpremilebihbasis'];
				@$trrp+=$bar['rupiahpenalty'];
				
			$tab.="<td align=center width=25px>";
			$tab.="<img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
					onclick=\"editdetail('".$bar['notransaksi']."','".$bar['nik']."','".$bar['kodeorg']."','".$bar['tahuntanam']."','".$bar['luaspanen']."','".$bar['hasilkerja']."','".$bar['brondolan']."','".$bar['hasilkerjakg']."','".$bar['upahkerja']."','".$bar['upahpremi']."','".$bar['upahpremilebihbasis']."','".$bar['rupiahpenalty']."','".$bar['keterangan']."','".$no."','".$jlhdenda."','".$edit."');\" >
					</td>
					<td align=center width=25px>
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
					onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['nik'] . "','" . $bar['kodeorg'] . "');\" >
					
					</td>";
			}
			
			$tab.="<tr class=rowcontent style=background-color:#A3E4D7>";
			$tab.="<td></td>";
			$tab.="<td colspan=1 align=center>
				   <input value=".$no." style=display:none id=jlhbrsdt><b>GRAND TOTAL</b></td>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@hidezerodecimal($tluas,2)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@number_Format($tjjg)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@number_Format($tbrd)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@number_Format($tkg)."</b></td>";
			$tab.="<td hidden bgcolor=#A3E4D7 align=right><b>".@number_Format($tupah)."</b></td>";
			$tab.="<td hidden bgcolor=#A3E4D7 align=right><b>".@number_Format($tpbss)."</b></td>";
			$tab.="<td hidden bgcolor=#A3E4D7 align=right><b>".@number_Format($tplb)."</b></td>";
			#ttl denda list data
			foreach($dendapanen as $iddenda => $kddenda){
				$tab.="<td bgcolor=#A3E4D7 ".$align." ".$nn." ".$tp[$iddenda]." width=30px name=listdenda[] id=tpddt##".$iddenda."><b>".@hidezerodecimal($ttlp[$iddenda])."</b></td>";
			}
			
			$tab.="<td bgcolor=#A3E4D7 align=right><b>".@number_Format($trrp)."</b></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right></td>";
			$tab.="<td bgcolor=#A3E4D7 align=right></td>";
			$tab.="</tr>";
			
			
			$str = "select a.kodeorg,a.tahuntanam,sum(a.hasilkerja) as hasilkerja, sum(a.hasilkerjakg) as kg, sum(a.jumlahhk) as hk, sum(a.norma) as norma, sum(a.upahkerja) as upah, sum(a.upahpenalty) as penalty, sum(a.upahpremi) as bss, sum(a.upahpremilebihbasis) as lbbss, sum(a.premibasis) as pbss, sum(a.brondolan) as brd, sum(a.luaspanen) as ha ,sum(a.penalti1) as penalti1,sum(a.penalti2) as penalti2,sum(a.penalti3) as penalti3,sum(a.penalti4) as penalti4,sum(a.penalti5) as penalti5,sum(a.penalti6) as penalti6,sum(a.penalti7) as penalti7,sum(a.penalti8) as penalti8,sum(a.penalti9) as penalti9,sum(a.penalti10) as penalti10, sum(a.rupiahpenalty) as rupiahpenalty 
			from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid  where a.notransaksi='" . $notransaksi . "' ".$where." group by kodeorg order by a.kodeorg asc";
			$row=fetchData($str);
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$nox='';
			while ($bar = $res->fetch()) {
				$nox++;
				$nn=" style=display:none ";
				$no+=1;
				$tab.="<tr class=rowcontent style=background-color:#AED6F1>";
				$tab.="<td align=center>" . $nox . "</td>";
				if($nox==1){
					$tab.="<td align=center><b>REKAPITULASI</b></td>";
				}else{
					$tab.="<td></td>";
				}
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				$tab.="<td align=center style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['kodeorg']."','blok')>" . $nmorg[$bar['kodeorg']]. "</td>";
				$tab.="<td align=center style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['tahuntanam']."','tt')>" . $bar['tahuntanam'] . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['ha'],2) . "</td>";
				$tab.="<td align=right>" . number_Format($bar['hasilkerja']) . "</td>";
				$tab.="<td align=right>" . number_Format($bar['brd']) . "</td>";
				$tab.="<td align=right>" . number_Format($bar['kg']) . "</td>";
				$tab.="<td hidden align=right>" . number_Format($bar['upah']) . "</td>";
				$tab.="<td hidden align=right>" . number_Format($bar['bss']) . "</td>";
				$tab.="<td hidden align=right>" . number_Format($bar['lbbss']+$bar['pbss']) . "</td>";
				
				#denda list data
				foreach($dendapanen as $iddenda => $kddenda){
					$tab.="<td align=right ".$nn." ".$tp[$iddenda]." width=30px name=listdenda[] id=rtpddt##".$iddenda."##".$nox.">".@hidezerodecimal($bar['penalti'.$iddenda])."</td>";
				}
				
				$tab.="<td align=right>" . number_Format($bar['rupiahpenalty']) . "</td>";
				$tab.="<td align=right></td>";
				$tab.="<td align=right></td>";

			}
			$tab.="<input value=".$nox." style=display:none id=jlhbrsdtrekap>";
			
		}
        $tab.="</tr>";
        $tab.="</table>";


        echo $tab;
	break;
	
	case'getDataDetail':
	#============================= BJR Per Blok ===========================
		$perLalu=substr(tanggalsystemn($tgl),0,7);
		$tahun=substr(tanggalsystemn($tgl),0,4);
		$str = "select * from ".$dbname.".kebun_5bjr where periode='".$perLalu."' and kodeorg='".$blok."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$bjr=$bar['bjr'];
		
	#============================= BJR Per Blok ===========================
	#=============================== Get UMR ==============================
		$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$karyawanid."' and tahun=".$tahun." and idkomponen in (1)"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$Umr=$res->fetch();
			$umrHarian=$Umr['nilai']/25;
		
	#=============================== Get UMR ==============================
	#============================== Tipe Kary =============================
		$str = "select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$tipe=$res->fetch();
			$tipeKary=$tipe['tipekaryawan'];
		
	#============================== Tipe Kary =============================
	#=============================== Get HL ===============================
	/*
		$tanggalx=tanggalsystemn($tgl);
		$day = date('D', strtotime($tanggalx));
		
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tanggalx."' and (kebun='GLOBAL' or kebun='".$kodeorg."')";
		$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
		$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
		$roworg=$queorg->fetch();
			if($roworg['keterangan']=='libur'){
				$libur=true;
			} else if ($day=='Sun'){
				$libur=true;
			} else if($roworg['keterangan']=='masuk'){
				$libur=false;
			} else {
				$libur=false;
			}
		
	# Jika hari libur maka upah KHT = 0
	# 0 => Staff, 1 => PB, 2 => PKWT, 3 => KHT, 4 => KHL, 5 => Magang, 6 => Kontrak, 7 => Direksi, 8 => Komisaris
		if(($tipeKary=='4' || $tipeKary=='5') and $libur=true){
			$umrHarian=$umrHarian;
		} elseif ($libur=true){
			$umrHarian=0;
		} else {
			$umrHarian=$umrHarian;
		}
	*/
	#=============================== Get HL ===============================
	#=============================== Get TT ===============================
		$str = "select * from ".$dbname.".setup_blok where kodeorg='".$blok."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tt=$bar['tahuntanam'];
	#=============================== Get TT ===============================
	#================== buat cek apakah ada di rekappnn ===================
		$jumlah='0';
		$str="select count(*) as jumlah from ".$dbname.".kebun_rekappnn_vw where "." blok='".$blok."' and tanggal='".tanggalsystemn($tgl)."' and posting=1 ";
		$qDetail=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qDetail->setFetchMode(PDO::FETCH_ASSOC);
		while($rDetail=$qDetail->fetch()){
			$jumlah=$rDetail['jumlah'];  
		}
	   
		if($jumlah=='0'){
			$rpnn="x";
		} else {
			$rpnn="y";
		}
		
	#================== buat cek apakah ada di rekappnn ===================
	
	#===================== ambil rp/kg di blok 
	$rpsatuan=0;
	if($kontan=='KONTAN'){
		$str = "select * from ".$dbname.".kebun_rkh_vw where kodeblok='".$blok."' and 
				kodekegiatan='611010101' and kontan='".$kontan."' and tanggal='".tanggalsystemn($tgl)."' and 
				statuspersetujuan=1"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$rpsatuan=$bar['rpsatuan'];
	}
	#=======================================
		
		#tidak pakai upah, semua karyawan borongan
		$umrHarian=0;
		
		echo $bjr."######".$umrHarian."######".$tt."######".$rpnn."######".$rpsatuan;
		// exit("Error:A");
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
					$denda['rp'] += $val * $optDenda[$kode]['nilai'];
					
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
		if($param['karyawanid']!='' and $param['blok']!='' and ($param['hapanen']!='' or $param['hapanen']!='0') and ($param['jjgpanen']!='' or $param['jjgpanen']!='0')){
			
		try {
			$owlPDO->beginTransaction();
			$arrtgl = makeOption($dbname,'kebun_aktifitas','notransaksi,tanggal',"notransaksi='".$param['notransaksi']."'");
			
			# cek apakah proses premi pemanen sudah dilakukan dan di posting
			$str = "select * from ".$dbname.".kebun_3premipemanen where divisi='".substr($param['blok'],0,6)."' and tanggalpanen='".$arrtgl[$param['notransaksi']]."' and posting='1'";
			$count=fetchData($str);
			if(count($count)>0){
				throw new PDOException("Proses premi pemanen sudah di lakukan dan sudah di posting, jika ingin melanjutkan silahkan unposting Kebun - Proses - Premi Pemanen terlebih dahulu.");
			}
			
			
			# Hapus dulu data yang lama
			$str = "delete from " . $dbname . ".kebun_prestasi where notransaksi='".$notransaksi."' and nik='".$param['karyawanid']."' and kodeorg='".$param['blok']."'";
			$owlPDO->exec($str);
			#try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
			
			# Validasi penginputan
			cekPrestasi($param);
			
			validasiInput(substr($param['blok'],0,4),substr($param['blok'],0,6),'PNN',$arrtgl[$param['notransaksi']],$exit='0');
			
			$data = array(
					'notransaksi'         => $param['notransaksi'],
					'nik'                 => $param['karyawanid'],
					'kodeorg'             => $param['blok'],
					'luaspanen'           => $param['hapanen'],
					'hasilkerja'          => $param['jjgpanen'],
					'brondolan'           => $param['brdpanen'],
					'hasilkerjakg'        => $param['kgpanen'],
					'upahkerja'           => $param['upah'],
					'upahpremi'           => $param['basis'],
					'upahpremilebihbasis' => $param['lbasis'],
					'rupiahpenalty'       => $param['denda_rp'],
					'tahuntanam'          => $param['tt'],
					'bjr'                 => $param['bjr'],
					'pekerjaanpremi'      => $param['sts'],
					'keterangan'          => $param['kontan']
			);
			
			for($i=1;$i<=$jlhdenda;$i++){
				$data['penalti'.$i] = $param['penalti'.$i];
			}
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}

			# Insert
			$query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
			#exit('error :'. $query);
			$owlPDO->exec($query);
			#try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
			
			# Exe jika bukan Kontanan, di KSP tidak di exe karena tidak pakai upah tetapi borongan
			if($param['kontan']!='KONTAN'){
				#proporsiUpah($param);
			}
			
			# Jika Kontanan Maka Nama Mandor Dkk di Header di kosongkan dan mandor bisa di inputkan di detail
			if($param['kontan']=='KONTAN'){
				$str = "update " . $dbname . ".kebun_aktifitas set `nikmandor`='', `nikmandor1`='',`nikasisten`='' where `notransaksi`='".$param['notransaksi']."'";
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		}
	break;
    case'delete':
	try {
		$owlPDO->beginTransaction();
		$str="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['noreferensi']!=''){
			throw new PDOException('Ini adalah transaksi yang terbentuk otomatis pada saat Posting pada proses Premi Pemanen, untuk menghapus silahkan unposting pada transaksi Proses Premi Pemanen.');
		}
		
        $str = "delete from " . $dbname . ".kebun_aktifitas where notransaksi='".$notransaksi."'";
        $owlPDO->exec($str);
		
		$str = "SELECT * FROM " . $dbname . ".listfileupload where notransaksi ='".$param['notransaksi']."' and kriteriaefil='PNN'";
		$res = fetchData($str);
		if(count($res)>0){
			foreach($res as $bar){
				$str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$bar['namafile']."'";
				$owlPDO->exec($str);
				$pathx = $path.$bar['namafile'];
				//unlink($pathx);
			}
		}
		
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			
		// try {
            // $owlPDO->exec($str);
        // } catch (PDOException $e) {
            // print " Gagal  !: " . $e->getMessage() . "\n";
            // die();
        // }

        break;

    case'deletedetail':

        $str = "delete from " . $dbname . ".kebun_prestasi where notransaksi ='".$notransaksi."' and kodeorg='" . $blok . "' and nik='" . $karyawanid . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		# Exe jika bukan Kontanan
		if($param['kontan']!='KONTAN'){
			#proporsiUpah($param);
		}
		
    break;

    case'loaddata':
		$kodorg=array();
        $where="";
		$str="select distinct(a.kodeorganisasi) as kodeorganisasi, b.namaorganisasi, b.alokasi from ".$dbname.".user_orgdetail a left join ".$dbname.".organisasi b on a.kodeorganisasi=b.kodeorganisasi where length(b.kodeorganisasi)=4 and a.namauser='".$_SESSION['standard']['username']."' order by b.kodeorganisasi";
        $res = fetchdata($str);
        foreach ($res as $bar) {
			$kodorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		}
		$kodorg[$_SESSION['empl']['lokasitugas']]=$_SESSION['empl']['lokasitugas'];
		
		if(count($kodorg)>0){
			$where.= "and a.kodeorg in ('".implode("','",$kodorg)."')";
		}
		
		$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABKRN'"; 
		$res = fetchdata($str);
		@$arrjab = explode(',', $res[0]['nilai']);
		
		#karena list data di filter berdasarkan pembuat terkadang user mencari nomor transaksi yg di input oleh user lain tidak muncul, solusinya munculkan semua transaksi pada saat pencarian walau bukan ybs pembuatnya namun tidak bisa di edit.
		
		$cari=false;
		if($_SESSION['empl']['subbagian']=='' and in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
			//$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.updateby ='".$_SESSION['standard']['userid']."'"; 
			
			$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and (b.kodeorg like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."' or b.kodeorg is null)"; 
		}else if($_SESSION['empl']['subbagian']==''){
			// $where.= " and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
			$where.= " and (a.kodeorg = '".$_SESSION['empl']['lokasitugas']."' or b.kodeorg is null)";
		}else {
			$where.=" and (a.kodeorg like '".$_SESSION['empl']['subbagian']."%' or a.updateby ='".$_SESSION['standard']['userid']."' or b.kodeorg is null)"; 
		}
	
		$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
	
	
        if ($divsch != '') {
            $where.=" and b.kodeorg like '" . $divsch . "%' ";
        }
        if (($tglmulai != '') and ($tglmulai != '--')) {
            $where.=" and a.tanggal >='" . $tglmulai . "' ";
        }
		if (($tglselesai != '') and ($tglselesai != '--')) {
            $where.=" and a.tanggal <='" . $tglselesai . "' ";
        }
		if ($notransaksisch != '') {
            $where.=" and a.notransaksi like '%" . $notransaksisch . "%' ";
        }
		if ($postingsrc != '') {
            $where.=" and a.jurnal ='" . $postingsrc . "' ";
        }
		if ($periodesch != '') {
            $where.=" and a.tanggal like '" . $periodesch . "%' ";
        }
		
		$where.=" and a.tipe != 'JJG'";
		
        $limit = 10;
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

		$sql = "select count(distinct a.notransaksi) as notr from " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi='PNN' and ((a.noreferensi !='' and a.deviceid !='') or ((a.noreferensi ='' or a.noreferensi is null) and a.deviceid is null)) " . $where . "";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=22 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
		$sql = "select * from " . $dbname . ".datakaryawan";
        $res = fetchdata($sql);
		foreach($res as $val){
			$divkar[$val['karyawanid']]=$val['subbagian'];
			$nmkar[$val['karyawanid']]=$val['namakaryawan'];
		}
		
        $str = "SELECT a.*, substr(b.kodeorg,1,6) as divisi, sum(b.hasilkerja) as jjg, b.pekerjaanpremi, b.keterangan  FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi='PNN' and ((a.noreferensi !='' and a.deviceid !='') or ((a.noreferensi ='' or a.noreferensi is null) and a.deviceid is null)) " . $where . " group by a.notransaksi order by a.notransaksi desc limit " . $offset . "," . $limit . "";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $isi = $cl = '';
            $no+=1;
			$a=$xx='';
			$a=$no%2;
			if($a==0){
				#$xx.=" style=background-color:#F5EEF8";
			}
			if($bar['keterangan']=='KONTAN'){
				$xx=" style=background-color:#FFFF00 title='Panen Kontanan !!!'";
			}
			if($bar['divisi']==''){
				$cl=" style=background-color:red; title=\"Data detail belum ada !\"";
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
			if($divkar[$bar['nikmandor']]!=$bar['divisi']){
				$a="<br><font size=1px color=blue><b><i>".$divkar[$bar['nikmandor']]."</i></b></font>";				
				$a1="title=\"Karyawan asistensi\"";				
			}
			if($divkar[$bar['nikmandor1']]!=$bar['divisi']){
				$b="<br><font size=1px color=blue><b><i>".$divkar[$bar['nikmandor1']]."</i></b></font>";				
				$b1="title=\"Karyawan asistensi\"";				
			}
			if($divkar[$bar['nikasisten']]!=$bar['divisi']){
				$d="<br><font size=1px color=blue><b><i>".$divkar[$bar['nikasisten']]."</i></b></font>";				
				$d1="title=\"Karyawan asistensi\"";				
			}
			
            $tab.="<tr ".$xx." ".$cl." class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td align=center>" . $bar['nobkm'] . "</td>";
            $tab.="<td align=center>" . $bar['noreferensi'] . "</td>";
            $tab.="<td align=center>" . $bar['deviceid'] . "</td>";
			if($bar['noreferensi']!='' and $bar['deviceid']!=''){
				$tab.="<td align=center>Mobile</td>";				
			}elseif($bar['noreferensi']=='' and is_null($bar['deviceid'])){
				$tab.="<td align=center>Web</td>";
			}elseif($bar['noreferensi']!='' and is_null($bar['deviceid'])){
				$tab.="<td align=center>Otomatis</td>";
			}else{
				$tab.="<td align=center>Other</td>";
			}
			
			
            $tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
            $tab.="<td align=center>" . $bar['divisi'] . "</td>";
            $tab.="<td align=center ".$c.">" . hari($bar['tanggal'],'ID') . "</td>";
            $tab.="<td align=center ".$c.">" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td align=right>" . number_Format($bar['jjg']) . "</td>";
			
            $tab.="<td align=center ".$a1.">" . @$nmkar[$bar['nikmandor']] . "".$a."</td>";
            $tab.="<td align=center ".$b1.">" . @$nmkar[$bar['nikmandor1']] . "".$b."</td>";
            $tab.="<td align=center ".$d1.">" . @$nmkar[$bar['nikasisten']] . "".$d."</td>";
			
            $tab.="<td align=center>" . @$nmkar[$bar['updateby']] . "</td>";
            
            if ($bar['jurnal'] == 0) {
					$isi.="<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
					onclick=\"edit('".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".$bar['kodeorg']."','".$bar['nobkm']."','".$bar['nikmandor']."','".$bar['nikmandor1']."','".$bar['nikasisten']."','".$bar['pekerjaanpremi']."','".$no."');\" ></td>";					
                $isi.="<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                    onclick=\"del('".$bar['notransaksi']."','".$no."');\" ></td>";

				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$isi.="<td align=center><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['notransaksi']."','".$no."');\" ></td>";
				} else {
					$isi.="<td align=center><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting'></td>";
				}
            }elseif ($bar['jurnal'] == 1) {
				$isi.="<td></td><td></td>";
				$isi.="<td align=center><img src=images/skyblue/posted.png class=zImgBtn class=zImgBtn height='30'  title='Posted'></td>";
			}else{
				$isi.="<td></td><td></td><td></td>";
			}
			
			$isi.="<td align=center style=width:20px><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$bar['notransaksi']."');\" ></td>";
			
            $isi.="<td align=center><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailPDF('".$bar['notransaksi']."','".$no."','event');\" ></td>";
            $isi.="<td align=center><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','PNN');\" ></td>";
            $isi.="<td align=center><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailExcel('".$bar['notransaksi']."','".$no."','event');\" ></td>";

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
        $footd.="</tr>
                     <tr><td colspan=22 align=center>";

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
		try {
		$owlPDO->beginTransaction();
		$data= $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				#cek duplikasi nama file
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."'";
				$res=fetchData($str);
				if(count($res)>0){
					throw new PDOException("Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('".$param['notransaksi']."','".$filename."','".$filetype."','PNN','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					$owlPDO->exec($str);
					file_put_contents($path.$filename,$file_tmpname);
				}else{
					throw new PDOException("Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
				if (!file_exists($path.$filename)) {
					throw new PDOException("Upload file gagal.");
				}
			}
		}
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
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
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:950px;height:500px;' type='application/pdf'>";
		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."'>";
		}
		
		echo $tab;
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
			foreach($tmpNo as $row) {
			$tmpRow = explode('/',$row['notransaksi']);
			@$noUrut = (int)$tmpRow[3];
			if($noUrut>$maxNo)
				$maxNo = $noUrut;
			}
			$currNo = addZero($maxNo+1,3);
			echo $data['notransaksi'] = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/".$currNo;
		}
	
    break;
	
	case'getbasispnn':
		$tab='';
		$tab.="<fieldset>";
		$tab.="<table class=sortable cellspacing=1 border=0 width=100%>
                <thead>
					<tr class=rowheader>
						<td align=center rowspan=2>No</td>
						<td align=center rowspan=2>".$_SESSION['lang']['unit']."</td>
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahun']."</td>
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahuntanam']."</td>
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['jenis']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['norma']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['premlebihbasis']." Rp/Kg</td>
						<td align=center rowspan=2>".$_SESSION['lang']['topografi']."</td>
						<td align=center rowspan=2>Premi Kehadiran</td>
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['brondol']." Rp/Kg</td>
					</tr>

				</thead>
		<tbody>";
		
		$optJenis = array(
						'0' => 'Normal',
						'1' => 'Banjir'
					);
		$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
		
		$tahun = substr($notransaksi,0,4);
		$str = "SELECT *  FROM " . $dbname . ".kebun_5basispanen2 where 1=1 and afdeling='".$kodeorg."' and tahun='".$tahun."' order by tahuntanam asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no+=1;	
			$optPT = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi in ('".$bar['afdeling']."')");
			$tab.="<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td>".$bar['afdeling']." - ".$optPT[$bar['afdeling']]."</td>
			<td style='text-align:center'>".$bar['tahun']."</td>
			<td style='text-align:center'>".$bar['tahuntanam']."</td>
			<td style='text-align:center'>".$optJenis[$bar['jenispremi']]."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['basis'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premilebihbasis'],2)."</td>
			<td style='text-align:left'>".($optTopografi[$bar['topografi']])."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premitopografi'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premibrondolan'],2)."</td>";
			
		}
		
		$tab.="</tr></tbody>";
		$tab.="</table>";
		$tab.="</fieldset>";
		echo $tab;
	break;
	case'posting':
		$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".
			$param['notransaksi']."'");
			
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

		$strupd=" update ".$dbname.".kebun_aktifitas set jurnal='1' where notransaksi='".$param['notransaksi']."'";
		try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
}

function cekPrestasi($param) {
	global $dbname;
	global $owlPDO;
		
	$tgl=explode('/',$param['notransaksi']);
	$tgl=$tgl[0];
	$notrx='';
	#cek mandor
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikmandor='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi'];
	#cek mandor1
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikmandor1='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi'];
	#cek kerani
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where keranimuat='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi'];
	#cek nikasisten
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikasisten='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi'];
	
	# Jika Kontanan Maka Nama Mandor Dkk di Header di kosongkan dan mandor bisa di inputkan di detail
	if($param['kontan']!='KONTAN'){
		if(@$jumtrans>0){
			throw new PDOException("Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani, notransaksi : ".$notrx."");
		}
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
	
	if(intval($jhkrawat)!='0' || intval($umrrawat)!='0') {
		// throw new PDOException("Karyawan sudah terdaftar di kegiatan perawatan, notransaksi : ".$notr."");
	}
	
	#cek di vhc - kegiatan traksi
	$qAbs = selectQuery($dbname,'vhc_runhk','sum(upah) as jhk,notransaksi',
			"idkaryawan='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhkvhc = $resAbs[0]['jhk'];
	$notrtr = $resAbs[0]['notransaksi'];
	
	if(intval($jmlhkvhc)!='0') {
		throw new PDOException("Karyawan sudah terdaftar di kegiatan traksi, notransaksi : ".$notrtr."");
	}
	
	#cek di SDM
	$qAbs = selectQuery($dbname,'sdm_absensidt_vw','sum(nilaihk) as jhk',
			"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhksdm = $resAbs[0]['jhk'];
	
	if(intval($jmlhksdm)!='0') {
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
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	if($param['karyawanid']==''){
		$warning="Karyawan";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	if($param['jjgpanen']==0||$param['jjgpanen']==''){
		$warning="Hasil Kerja (Jjg)";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	
	if($param['hapanen']==0 ||$param['hapanen']==''){
		$warning="Luas Panen(Ha)";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	
	if($param['bjr']==0 || $param['bjr']==''){
		$warning="BJR melalui Kebun - Setup - BJR";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	
	if($param['kgpanen']==0 || $param['kgpanen']==''){
		$warning="Kg Panen";
		throw new PDOException("".$warning." tidak boleh kosong !!!");
	}
	
	if($param['kontan']=='KONTAN'){
		if(($param['basis']==0 || $param['basis']=='') and ($param['lbasis']==0 || $param['lbasis']=='')){
			$warning="Premi Karyawan";
			throw new PDOException("Silakan mengisi ".$warning.".");
		}		
	}else{
		if($param['upah']==0 || $param['upah']==''){
			#$warning="Gaji Pokok Karyawan";
			#throw new PDOException("Silakan mengisi ".$warning.".");
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
	
	if(floatval(trim($luaspanennya))>floatval(trim($luasbloknya))){
		$warning="Tota Luas Panen ".$luaspanennya." (Ha), melebihi Luas Blok ".$luasbloknya." (Ha)";
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
	
	// exit("Error:".$a._.$b);
	if(floatval(trim($luaspanennya))>floatval(trim($harkppnn))){
		throw new PDOException('Luas Panen Blok '.$param['blok'].' = '.$luaspanennya.' (Ha), melebihi Luas di Rekap Panen = '.$harkppnn.' (Ha)');
	}
	if(floatval(trim($jjgpnnnya))>floatval(trim($jjgrkppnn))){
		throw new PDOException('Jumlah Jjg Blok '.$param['blok'].' = '.$jjgpnnnya.', melebihi Jjg di Rekap Panen = '.$jjgrkppnn);
	}
}

function proporsiUpah($param) {
        global $dbname;
        global $conn;
        global $owlPDO;
		
        # Get Tahun
		$tmpTgl = explode('/',$param['notransaksi']);
        $tahun = substr($tmpTgl[0],0,4);
		$tgl=$tmpTgl[0];
		
        # Get UMR
        $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai', "karyawanid=".$param['karyawanid']." and tahun=".$tahun." and idkomponen in (1)");
        $Umr = fetchData($qUMR);
        $upahharian=round($Umr[0]['nilai']/25);
		
		
		# Bentuk data
		$str="select sum(luaspanen) as luaspanen,sum(hasilkerja) as hasilkerja,count(*) as jumblok from ".$dbname.".kebun_prestasi_vs_hk where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tluas=$bar['luaspanen'];
			$tjjg=$bar['hasilkerja'];
			$jumblok=$bar['jumblok'];
			@$upahpro=$upahharian/$jumblok;
		
		# Bentuk data
		$str="select * from ".$dbname.".kebun_prestasi_vs_hk where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			# Query update yang baru
			$strupd=" update ".$dbname.".kebun_prestasi set upahkerja='".$upahpro."' where notransaksi='".$param['notransaksi']."' and nik='".$param['karyawanid']."' and kodesegment='0000000001'";
			$owlPDO->exec($strupd);
			#try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
}
?>	