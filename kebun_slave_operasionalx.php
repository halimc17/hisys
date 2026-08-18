<?php
#ini_set('display_errors',0);
#error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$param = $_POST;
$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$tgl = checkPostGet('tgl', '');
$kodeorg = checkPostGet('kodeorg', '');
$filterdivisi = checkPostGet('filterdivisi', '');
$showpermandor = checkPostGet('showpermandor', '');
$mandor = checkPostGet('mandor', '');
$mandor1 = checkPostGet('mandor1', '');
$asst = checkPostGet('asst', '');
$kerani = checkPostGet('kerani', '');
$nobkm = checkPostGet('nobkm', '');
$blok = checkPostGet('blok', '');
$karyawanid = checkPostGet('karyawanid', '');
$kegiatan = checkPostGet('kegiatan', '');
$kodegudang = checkPostGet('kodegudang', '');
$mandorsrc = checkPostGet('mandorsrc', '');
$nobkmsch = checkPostGet('nobkmsch', '');
$mode = checkPostGet('mode', '');

$prestasi = checkPostGet('prestasi', '');
@$prestasi =str_replace(",","",$prestasi);
@$param['qtymat'] =str_replace(",","",$param['qtymat']);
@$param['prestasi'] =str_replace(",","",$param['prestasi']);
@$param['jhk'] =str_replace(",","",$param['jhk']);
@$param['upah'] =str_replace(",","",$param['upah']);
@$param['premi'] =str_replace(",","",$param['premi']);

$statusblok = $_SESSION['tmp']['kebun']['tipeTrans'];
$stsawal = checkPostGet('stsawal', '');

$divsch = checkPostGet('divsch', '');
$tglmulai = tanggalsystemn(checkPostGet('tglmulai', ''));
$tglselesai = tanggalsystemn(checkPostGet('tglselesai', ''));
$notransaksisch = checkPostGet('notransaksisch', '');
$postingsrc = checkPostGet('postingsrc', '');
$periodesch = checkPostGet('periodesch', '');
$txtcari = checkPostGet('txtcari', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');		
$luasblok=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif');		

#======================= cek admin =========================
$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
$wh='';
if(count($adm)==0){
	$wh= " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}
#============== KHT, KHL dan Kontrak ======================
	if($filterdivisi!=''){
		@$whereKary=" and subbagian = '".$filterdivisi."'";
	}else{
		@$whereKary.= " and lokasitugas='".$kodeorg."'";
	}
	$whereKary.= " and tipekaryawan in (2,3,4,6) and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
	$optKary="<option value=''>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']."</option>";
	$str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.subbagian from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." order by a.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		if($bar['nik']!=''){
			$bar['nik']=" - ".$bar['nik'];
		}
		if($bar['subbagian']!=''){
			$bar['subbagian']=" - ".$bar['subbagian'];
		}
		
		$optKary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan'].$bar['nik'].$bar['subbagian']."</option>";
	}
#============== KHT, KHL dan Kontrak ======================
#===================== Kode Blok ==========================
	if($filterdivisi!=''){
		@$whereBlok=" and substr(a.kodeorganisasi,1,6) = '".$filterdivisi."'";
		@$whereBlok.=" and b.statusblok = '".$statusblok."'";
	}else{
		@$whereBlok.= " and substr(a.kodeorganisasi,1,4) ='".$kodeorg."'";
		@$whereBlok.= " and b.statusblok = '".$statusblok."'";
	}
	$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".organisasi a 
			left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
			where a.tipe in('BLOK','BIBITAN') ".$whereBlok." order by a.kodeorganisasi asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optBlok.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
	}
	#exit("error".$str);
#===================== Kode Blok ==========================
#======================= Kegiatan =========================
	$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".setup_kegiatan
			where 1=1 and kelompok='".$statusblok."' and status='1' order by kodekegiatan asc, namakegiatan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optKeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
	}
#======================= Kegiatan =========================

$jab = getPostingJabatan('rawatkebun');	
$tmpTgl = explode('-',$tgl);	


switch ($method) {
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
			echo "HK Mandor / Mandor 1 / Kerani tidak boleh di input pada detail BKM.\n\nKaryawan tersebut dibawah ini sudah terdaftar pada transaksi :\n";//
			$no=0;
			while($bar=$res->fetch()){
			   $no+=1;
				echo $no.". ".$namaKary[$bar->karyawanid]." => ".$bar->notransaksi." => ".tanggalnormal($bar->tanggal)."\n"; 
			}
			exit('Warning: silahkan kosongkan HK pada transaksi tersebut.');
		}
		#=== insert header ===
        if ($mode=='edit') {
            $str = "update " . $dbname . ".kebun_aktifitas set `nobkm`='".$nobkm."', `nikmandor`='".$mandor."', `nikmandor1`='".$mandor1."',`keranimuat`='".$kerani."',`nikasisten`='".$asst."' where `notransaksi`='".$notransaksi."'"; #exit("error".$str);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        } else {
			#===== buat nomor transaksi =====
			$data = $_POST;
			# Data Capture & Reform
			$data['tipetransaksi'] = $stsawal;
			$data['tgl'] = tanggalsystem($data['tgl']);
			
			#=== Generate No Transaksi
			# Get Existing Data
			$fWhere = "tanggal='".$data['tgl']."' and kodeorg='".$data['kodeorg'].
				"' and tipetransaksi='".$data['tipetransaksi']."'";
			$fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
			$tmpNo = fetchData($fQuery);
			
			# Generate No Transaksi
			if(count($tmpNo)==0) {
				$notransaksi = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/001";
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
				$notransaksi = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/".$currNo;
			}
			#=== End buat nomor transaksi ===
			$str = "insert into " . $dbname . ".kebun_aktifitas (`notransaksi`, `tipetransaksi`, `tanggal`, `nobkm`, `kodeorg`, `nikmandor`, `nikmandor1`, `nikasisten`, `keranimuat`, `jurnal`, `nospk`, `updateby`)
			values ('".$notransaksi."','".$statusblok."','".$tgl."','" . $nobkm . "','" . $kodeorg . "','".$mandor."','".$mandor1."','".$asst."','".$kerani."','0',null,'" . $_SESSION['standard']['userid'] . "')";
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
			$whereX=" and tipe = 'BIBITAN' ";
		}else{
			$whereX=" and tipe != 'BIBITAN' ";
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
					<img id='hidebtn' onclick=\"hideheader()\" title='Full Screen' class='resicon' src='images/full-screen.png' >
					<img id='unhidebtn' onclick=\"unhideheader()\" title='Exit Full Screen' class='resicon' style=display:none src='images/exit_full_screen.png' >
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
			<table border=0 cellpadding=1 cellspacing=1 class=sortable style='min-width:1010px'>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$frm[0].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows." colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['namakaryawan']." - ".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']."</td>
				<td align=center ".$rows."  colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows."  colspan=2><font color=red><b>* </font></b>".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows." width=30px>".$_SESSION['lang']['luas'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['jumlah']."</td>
				<td align=center colspan=3 >".$_SESSION['lang']['premi']."</td>
				<td align=center width=30px ".$rows.">" . $_SESSION['lang']['action'] . "</td>
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
				<td colspan=2 align=center valign=center>Copy <input type=checkbox title='Aktif / Non Aktif' id=copykeg class='resicon' style='position:relative;top:3px;left:3px;'></td>
				<td colspan=2 align=center valign=center>Copy <input type=checkbox title='Aktif / Non Aktif' id=copyblok class='resicon' style='position:relative;top:3px;left:3px;'></td>
				<td></td>
				<td></td>
				<td align=center><input type=checkbox id=copypres title='Aktif / Non Aktif' class='resicon' style='position:relative;top:3px;left:3px;'></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		#=== Isi input detail ===
		$frm[0].="<tbody id=inputdetail>
				<script>inputdetail()</script>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input detail ===	
        $frm[0].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
			<div id=loaddatadetail>
				<script>loaddatadetail()</script>
			</div></fieldset>";
			
		#=== TAB MATERIAL ===
		$frm[1]="<table><td valign=top>
			<fieldset style=float:left>
				<legend>Info</legend>
					<table height=25px><td><font color=red><b>* </font>".$_SESSION['lang']['notifobligatory']."</b></td>
					<td>&nbsp;||&nbsp;</td><td>Hanya kegiatan yang sudah di daftarkan materialnya melalui menu <b>Setup - Kegiatan</b> yang di munculkan.</td>
					</table>
			</fieldset>
			</td></table>
			<fieldset>
			<legend>" . $_SESSION['lang']['material'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable style='min-width:980px'>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$frm[1].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kodekegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['gudang']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['namabarang']."</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['material']."</td>
				<td align=center ".$rows.">" . $_SESSION['lang']['action'] . "</td>
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
			
		$hfrm[0]=$_SESSION['lang']['prestasi']." & ".$_SESSION['lang']['absensi'];
		$hfrm[1]=$_SESSION['lang']['material'];

		# draw tab, jangan ganti parameter pertama, krn dipakai di javascript
		drawTab('FRM',$hfrm,$frm,175,'100%');

        CLOSE_BOX();
		
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
				<td align=center>".$_SESSION['lang']['saldo']."</td>
			</tr>
			</thead>
			<tbody>";
			
		$str="select a.kodebarang,a.namabarang,a.satuan,b.saldoqty
			  from ".$dbname.".log_5masterbarang a 
			  left join ".$dbname.".log_5masterbarangdt b on a.kodebarang=b.kodebarang 
			  where (a.namabarang like '%".$txtcari."%' or a.kodebarang like '%".$txtcari."%') and b.kodegudang='".$kodegudang."' and b.saldoqty >'0' and a.kodebarang in (select kodebarang from ".$dbname.".setup_kegiatannorma where kodekegiatan='".$kegiatan."')";
		$res=fetchData($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"loadField('".$val['kodebarang']."','".$val['namabarang']."','".$val['satuan']."');\">
				<td align=center>".$no."</td>
				<td align=center>".$val['kodebarang']."</td>
				<td>".$val['namabarang']."</td>
				<td>".$val['satuan']."</td>
				<td align=right>".@hidezerodecimal($val['saldoqty'],2)."</td>
			</tr>";	
		}
		$tab.="</table>";
		
		echo $tab;
	break;
	
	case'inputdetailmaterial':

		$str = "select sum(hasilkerja) as hasilkerja, kodekegiatan, kodeorg from ".$dbname.".kebun_prestasi where 1=1 and notransaksi='".$notransaksi."' and kodekegiatan in (select kodekegiatan from ".$dbname.".setup_kegiatannorma) group by kodekegiatan, kodeorg order by kodekegiatan asc, kodeorg asc"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$strv = "select * from ".$dbname.".kebun_5gudangtransaksi where afdeling='".substr($bar['kodeorg'],0,6)."' and status='1'";
			$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
			$resv->setFetchMode(PDO::FETCH_ASSOC);
			$barv=$resv->fetch();
		
			$no+=1;
			$tab.="<tr class=rowcontent id=rowmat_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td id=kegiatanmat".$no.">".$bar['kodekegiatan']."</td>";
			$tab.="<td>".$nmkeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td id=blokmat".$no.">".$bar['kodeorg']."</td>";
			$tab.="<td align=center>".$nmsat[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=right>".@hidezerodecimal($bar['hasilkerja'],2)."</td>";
			$tab.="<td id=pres".$no." style=display:none>".$bar['hasilkerja']."</td>";
			$tab.="<td id=kodegudang".$no." style=display:none>".$barv['kodegudang']."</td>";
			$tab.="<td>".$barv['kodegudang']." - ".$nmorg[$barv['kodegudang']]."</td>";
			$tab.="<td>
					<input type=text id=kodemat".$no." class=myinputtext style='width:60px;' onclick=\"searchmat('".$no."','Find',event);\" readonly>
					<input type=text id=namamat".$no." class=myinputtext style='width:150px;' onclick=\"searchmat('".$no."','Find',event);\" readonly></td>";
			$tab.="<td><input id=satmat".$no." class=myinputtext disabled style=\"width:35px;\"></td>";
			$tab.="<td><input id=qtymat".$no." onkeyup=\"z.numberFormat('qtymat".$no."',3);\"  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
			$tab.="<td align=center>
				<img title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=\"savematerial(".$no.")\" src='images/save.png'/>
				<img title='".$_SESSION['lang']['clear']."' class=resicon onclick=\"clearmaterial(".$no.")\" src='images/clear.png'/>
			</td>";
			$tab.="</tr>";
		}
	echo $tab;
	break;
	
	case'loaddatadetailmaterial':
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='min-width:980px'>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
		$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kodekegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['gudang']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['namabarang']."</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['material']."</td>
				<td align=center ".$rows." width=30px>" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>".$_SESSION['lang']['satuan'] . "</td>
				<td align=center width=45px>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center width=35px>".$_SESSION['lang']['satuan']."</td>
				<td align=center width=50px>".$_SESSION['lang']['jumlah']."</td>
				
				
			</tr>
			</thead><tbody>";
				
		$str = "select * from ".$dbname.".kebun_pakaimaterial where 1=1 and notransaksi='".$notransaksi."' order by kodekegiatan, kodeorg";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$no+=1;
			$nmsatbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$bar['kodebarang']."'");
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$strv = "select sum(hasilkerja) as hasilkerja from ".$dbname.".kebun_prestasi where notransaksi='".$notransaksi."' and kodekegiatan='".$bar['kodekegiatan']."' and kodeorg='".$bar['kodeorg']."'"; //exit('error'.$strv);
			$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
			$resv->setFetchMode(PDO::FETCH_ASSOC);
			$barv=$resv->fetch();
			
			$tab.="<tr class=rowcontent id=rowmatlist_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['kodekegiatan']."</td>";
			$tab.="<td>".$nmkeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td>".$bar['kodeorg']."</td>";
			$tab.="<td align=center>".$nmsat[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=right>".@hidezerodecimal($barv['hasilkerja'],2)."</td>";
			$tab.="<td>".$bar['kodegudang']." - ".$nmorg[$bar['kodegudang']]."</td>";
			$tab.="<td min-width=230px>".$bar['kodebarang']." - ".$nmbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=center>".$nmsatbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=right>".@hidezerodecimal($bar['kwantitas'],2)."</td>";
			$tab.="<td align=center><input type=hidden id=method value='insertmaterial'>
				<img title='".$_SESSION['lang']['delete']."' class=zImgBtn onclick=\"delmaterial('".$notransaksi."','".$bar['kodekegiatan']."','".$bar['kodeorg']."','".$bar['kodebarang']."')\" src='images/skyblue/delete.png'/>
			</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody></table>";
	echo $tab;
	break;
	case'inputdetail':
	echo"<tr class=rowcontent>";
	echo"	<td id=no align=center>1</td>
			<td style=width:255px><select style=width:255px onchange=getDataDetail() id=karyawanid>".$optKary."</select></td><td width=20px>
			<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
			
			<td style=width:195px><select style=width:195px onchange=getDataDetail() id=kegiatan>".$optKeg."</select></td><td width=20px>
			<img id='kegiatan' onclick=z.elSearch('kegiatan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
			
			<td style=width:95px><select style=width:95px onchange=getDataDetail() id=blok>".$optBlok."</select></td><td width=20px>
			<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
			<td><input id=luas disabled class=myinputtextnumber style=\"width:35px;\"></td>
			<td><input id=satuan class=myinputtext disabled style=\"width:40px;align:center\"></td>

			<td><input id=prestasi onkeyup=\"z.numberFormat('prestasi',2);getDataDetail();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
			
			<td><input id=jhk onkeyup=\"z.numberFormat('jhk',2);getumr();\"  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:35px;\"></td>
			
			<td><input id=upah disabled class=myinputtextnumber style=\"width:60px;\"></td>

			<td><input id=basis disabled onkeyup=\"z.numberFormat('basis',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
			<td><input id=rpsat disabled onkeyup=\"z.numberFormat('rpsat',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
			
			<td><input id=premi disabled onkeyup=\"z.numberFormat('premi',2)\"  class=myinputtextnumber style=\"width:60px;\"></td>
			
			
			<td align=center><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
				<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetailall()\" src='images/clear.png'/>
			</td>
        </tr><tr>
			<td id=pfot colspan=14></td>
			<td  align=right colspan=2>
			<input id=jlhbrs style=display:none>
			<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetail()\" src='images/refresh2.png'/>
			<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
			</td>
        </tr>";
	break;
	case'getdata':	
		echo $optKary."######".$optBlok;
	break;
	case'getdatamandor':
	@$whereKary.= " and tipekaryawan in (2,3,4,6) and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
	$str = "select a.karyawanid,b.namakaryawan,b.nik, b.subbagian from ".$dbname.".kebun_5mandor a
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where statusaktif='1' and mandorid='".$mandor."' ".$whereKary." order by a.nourut asc"; #exit("error".$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$tab='';
	$no='';
	while($bar=$res->fetch()){
		$no++;
		$tab.="<tr class=rowcontent id=row".$no.">";
		$tab.="	<td align=center>".$no."</td>
		<td style=display:none><input id=karyawanid".$no." value=".$bar['karyawanid']."></td>
		<td colspan=2 id=kary".$no.">".$bar['nik']." - ".$bar['namakaryawan']."</td>
		
		<td style=width:195px><select style=width:195px onchange=\"getDataDetailAllAll(".$no.");copykegiatan(".$no.")\" id=kegiatan".$no.">".$optKeg."</select></td><td width=20px>
		<img id='kegiatan".$no."' onclick=z.elSearch('kegiatan".$no."',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
		
		<td style=width:95px><select style=width:95px onchange=\"getDataDetailAllAll(".$no.");copyblok(".$no.")\" id=blok".$no.">".$optBlok."</select></td><td width=20px>
		<img id='blok".$no."' onclick=z.elSearch('blok".$no."',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
		<td><input id=luas".$no." disabled class=myinputtextnumber style=\"width:35px;\"></td>
		<td><input id=satuan".$no." class=myinputtext disabled style=\"width:40px;align:center\"></td>

		<td><input id=prestasi".$no." onkeyup=\"z.numberFormat('prestasi".$no."',2);\" onblur=\"getDataDetailAllAll(".$no.");copypres(".$no.")\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
		
		<td><input id=jhk".$no." onkeyup=\"z.numberFormat('jhk".$no."',2);\" onblur=\"getumr(".$no.")\"  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:35px;\"></td>
		
		<td><input id=upah".$no." disabled class=myinputtextnumber style=\"width:60px;\"></td>
		
		<td><input id=basis".$no." disabled onkeyup=\"z.numberFormat('basis".$no."',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
		<td><input id=rpsat".$no." disabled onkeyup=\"z.numberFormat('rpsat".$no."',2)\"  class=myinputtextnumber style=\"width:50px;\"></td>
			
		<td><input id=premi".$no." disabled onkeyup=\"z.numberFormat('premi".$no."',2)\"  class=myinputtextnumber style=\"width:60px;\"></td>

		<td align=center><input type=hidden id=method value='insert'>
					
			<img title='" . $_SESSION['lang']['save']."' class=zImgBtn onclick=\"savedetail(".$no.")\" src='images/save.png'/>
			<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail(".$no.")\" src='images/clear.png'/>
		</td>
	</tr>";
	}
	$tab.="<tr>
		<td id=pfot colspan=14>
		<td colspan=2 align=right>
		<input id=jlhbrs  style=display:none value=".$no.">
		<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetail()\" src='images/refresh2.png'/>
		<img title='" . $_SESSION['lang']['saveall']."' class=zImgBtn onclick=\"saveAll(".$no.")\" src='images/save.png'/>
		<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
		</td>
	</tr>";
	echo $tab."######".$no;
	break;
	
	case'loaddatadetail':
	
	$rows="rowspan=2";	
	$tab="<table id=tabledt cellpadding=1 cellspacing=1 border=0 class=sortable >
			<thead><tr class=rowheader>
			<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows." width=30px>".$_SESSION['lang']['luas'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['jumlah']."</td>
				<td align=center ".$rows." >".$_SESSION['lang']['premi']."</td>
				<td align=center width=30px ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>".$_SESSION['lang']['satuan'] . "</td>
				<td align=center width=45px>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center width=35px>HK</td>
				<td align=center width=50px>".$_SESSION['lang']['upah']."</td>
			</tr>
		</thead>";
		
        $no = 0;
        $str = "select a.*,b.namakaryawan,b.nik as nik2, b.subbagian, c.jhk, c.umr, c.insentif
				from " . $dbname . ".kebun_prestasi a 
				left join " . $dbname . ".datakaryawan b on a.nikpemel=b.karyawanid  
				left join " . $dbname . ".kebun_kehadiran c on a.nikpemel=c.nik and a.notransaksi=c.notransaksi and a.nourut=c.nourut
				where a.notransaksi='" . $notransaksi . "' order by b.namakaryawan asc";// exit('error'.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=11 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$bgcolor=$title='';
				$strx = "select count(nikpemel) as jmlkary, nikpemel from " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."' and nikpemel='".$bar['nik']."' group by nik";
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$barx = $resx->fetch();
				if(($bar['nikpemel']==$barx['nikpemel']) and ($barx['jmlkary']>1)){
					$bgcolor="style=background-color:orange;";
					$title=" title = 'Karyawan Bekerja lebih dari 1 blok atau 1 kegiatan !'";
				}
				if($bar['nik2']!=''){
					$bar['nik2']=$bar['nik2']." - ";
				}
				if($bar['subbagian']!=''){
					$bar['subbagian']=$bar['subbagian']." - ";
				}
				$no+=1;
				$align=" align=right ";
				$nn=" style=display:none ";
				$tab.="<tr class=rowcontent ".$bgcolor."".$title.">";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>" . $bar['nik2'].$bar['subbagian'].$bar['namakaryawan'] . "</td>";
				$tab.="<td align=left>".$bar['kodekegiatan']." - " . $nmkeg[$bar['kodekegiatan']]. "</td>";
				$tab.="<td align=center>" . $bar['kodeorg']. "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($luasblok[$bar['kodeorg']],2) . "</td>";
				$tab.="<td align=center>" . $nmsat[$bar['kodekegiatan']] . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['hasilkerja'],2) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['jhk'],2) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['umr']) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['insentif']) . "</td>";
				
				@$tjhk+=$bar['jhk'];
				@$tumr+=$bar['umr'];
				@$tinsentif+=$bar['insentif'];
				
			$tab.="<td align=center>";
			$tab.="<img src=images/application/application_edit.png class=resicon  title='Edit' 
					onclick=\"editdetail('".$bar['notransaksi']."','".$bar['nikpemel']."','".$bar['kodekegiatan']."','".$bar['kodeorg']."','".$luasblok[$bar['kodeorg']]."','".$nmsat[$bar['kodekegiatan']]."','".$bar['hasilkerja']."','".$bar['jhk']."','".$bar['umr']."','".$bar['insentif']."','".$no."');\" >
					
					<img src=images/application/application_delete.png class=resicon  title='Delete' 
					onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['nikpemel'] . "','" . $bar['kodeorg'] . "','".$bar['kodekegiatan']."');\" >
					
					</td>";
			}
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan= 7 bgcolor=cyan align=center>
				   <input value=".$no." style=display:none id=jlhbrsdt><b>TOTAL</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@hidezerodecimal($tjhk,2)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@hidezerodecimal($tumr)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@hidezerodecimal($tinsentif)."</b></td>";
			$tab.="<td bgcolor=cyan align=right></td>";
			$tab.="</tr>";
		}
        $tab.="</tr>";
        $tab.="</table>";


        echo $tab;
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
			if(($prestasi-$basis)>0){
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
	#=============================== Get Ha ===============================

	echo $stspremi."######".$basis."######".$premibasis."######".$premilebihbasis."######".$tipeKary."######".$luasblok."######".$satkegiatan."######".$rppremilebihbasis;
	break;
	
	case'getumr':
	#=============================== Get UMR ==============================
		$tahun=substr(tanggalsystemn($tgl),0,4);
		$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$karyawanid."' and tahun=".$tahun." and idkomponen in ('1')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$Umr=$res->fetch();
			$umrHarian=$Umr['nilai']/25;
		
	#=============================== Get UMR ==============================

	echo $umrHarian;
	break;
	
	case'delmaterial':
		$str = "delete from " . $dbname . ".kebun_pakaimaterial where notransaksi='".$notransaksi."' and kodeorg='".$param['blok']."' and kodebarang='".$param['kodebarang']."' and kodekegiatan='".$param['kegiatan']."'";
		try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
	break;
	case'insertmaterial':
		if($param['qtymat']==''){
			$param['qtymat']='0';
		}
		$ttlkeluar = $logblmpost = $bkmblmpost = $saldogudang = 0 ;
		
		# Ambil saldo gudang
		$str="select saldoqty from ".$dbname.".log_5masterbarangdt where kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."'";
		$res=fetchData($str);
		$saldogudang = $res[0]['saldoqty'];
		
		#ambil transaksi belum posting di BKM
		$str="select sum(kwantitas) as kwantitas from ".$dbname.".kebun_pakaimaterial a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.kodebarang='".$param['kodebarang']."' and a.kodegudang='".$param['kodegudang']."' and b.jurnal='0'";
		$res=fetchData($str);
		$bkmblmpost = $res[0]['kwantitas'];
		
		#ambil transaksi belum posting di gudang (siapa tau ada, ambil yang keluar saja yang masuk biarkan saja)
		$str="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where kodebarang='".$param['kodebarang']."' and kodegudang='".$param['kodegudang']."' and post='0'";
		$res=fetchData($str);
		$logblmpost = $res[0]['jumlah'];
		
		$ttlkeluar = $bkmblmpost+$logblmpost+$param['qtymat'];
		
		if($ttlkeluar>$saldogudang){
			exit("Error : Saldo barang tidak cukup, sisa saldo : ".$saldogudang."\nPemakaian lalu belum posting : ".($bkmblmpost+$logblmpost)."\nTransaksi saat ini : ".$param['qtymat']."\nTotal Keluar : ".$ttlkeluar);
		}

		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['notransaksi']!='' and $param['kegiatan']!='' and $param['blok']!='' and $param['kodebarang']!='' and $param['qtymat']!='0' and $param['kodegudang']!=''){
		
			# Hapus dulu data yang lama
			$str = "delete from " . $dbname . ".kebun_pakaimaterial where notransaksi='".$notransaksi."' and kodeorg='".$param['blok']."' and kodebarang='".$param['kodebarang']."' and kodekegiatan='".$param['kegiatan']."'";
			try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
			
			# ambil harga rata2 barang
			$str = "select hargarata from ".$dbname.".log_5saldobulanan where kodegudang='".$param['kodegudang']."' and kodebarang='".$param['kodebarang']."' order by periode desc limit 1";
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
			try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }

		}
	break;
		
    case'insert':
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
		
		# Cek sudah ada atau belum ???
		$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and kodekegiatan='".$param['kegiatan']."'";
		$res=fetchData($str);
		if(count($res)>0){
			exit("Error : Data sudah ada !");
		}
			
			# Validasi penginputan
			cekPrestasi($param);
			
			# Buat nomor urut
			$sql = "select max(nourut) as nourut from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."' limit 1"; 
			$res=fetchData($sql);
			
			# ==========================================================================================
			$cols = array(
						'notransaksi','nourut','nik','nikpemel','kodekegiatan','kodeorg','hasilkerja','jumlahhk','tahuntanam','upahpremi'
					);
			$data = array(
						$param['notransaksi'],($res[0]['nourut']+1),'-',$param['karyawanid'],$param['kegiatan'],$param['blok'],$param['prestasi'],$param['jhk'],'0',$param['premi']
					);

			# Insert kebun_prestasi
			$query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
			try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
			# ==========================================================================================
			
			$cols = array(
						'notransaksi','nourut','nik','absensi','jhk','umr','insentif','hasilkerja'
					);
			$data = array(
						$param['notransaksi'],($res[0]['nourut']+1),$param['karyawanid'],'H',$param['jhk'],$param['upah'],$param['premi'],$param['prestasi']
					);

			# Insert kebun_kehadiran
			$query = insertQuery($dbname,'kebun_kehadiran',$data,$cols);
			try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
			# ==========================================================================================
		}
	break;
	case'update':
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
			try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
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
			try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
			# ==========================================================================================
		}
	break;
	
    case'delete':

        $str = "delete from " . $dbname . ".kebun_aktifitas where notransaksi='".$notransaksi."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;

    case'deletedetail':
		#cek dulu di tab material udah di simpan belum, jika sudah harus di hapus juga
		$str = "SELECT * FROM " . $dbname . ".kebun_pakaimaterial where notransaksi ='".$notransaksi."' and kodeorg='" . $blok . "' and kodekegiatan='".$kegiatan."'";
		$res=fetchData($str);
		if(count($res)>0){
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
		
        $where="";
		$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
        $adm = fetchData($str);
		if(count($adm)>0){
			$where.= "";
		}elseif($_SESSION['empl']['subbagian']==''){
			$where.= " and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		#}else if($_SESSION['empl']['subbagian']!='' and $_SESSION['empl']['tipekaryawan']==0){
		}else if($_SESSION['empl']['subbagian']!=''){
			$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.updateby ='".$_SESSION['standard']['userid']."'"; 
		}else {
			$where.= " and b.kodeorg like '".$_SESSION['empl']['subbagian']."%'";
		}
		
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
		if ($nobkmsch != '') {
            $where.=" and a.nobkm like '%".$nobkmsch."%' ";
        }
		if ($mandorsrc != '' and $mandorsrc != 'blank') {
            $where.=" and a.nikmandor like '%".$mandorsrc."%' ";
        } else if($mandorsrc == 'blank'){
            $where.=" and a.nikmandor = '' ";
		}
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
		$tab = "";
        $no = $maxdisplay;

        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi='".$statusblok."' " . $where . " group by a.notransaksi order by a.notransaksi desc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=19 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
        $str = "SELECT a.*, substr(b.kodeorg,1,6) as divisi, sum(b.hasilkerja) as jjg, sum(b.jumlahhk) as hk, sum(b.upahpremi) as premi FROM " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 and a.tipetransaksi='".$statusblok."' " . $where . " group by a.notransaksi order by a.notransaksi desc limit " . $offset . "," . $limit . ""; #exit('error'.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;
			$a=$xx='';
			$a=$no%2;
			if($a==0){
				$xx.=" style=background-color:#F5EEF8";
			}
            $tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td align=center>" . $bar['nobkm'] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
            $tab.="<td align=center>" . $bar['divisi'] . "</td>";
            $tab.="<td align=center>" . $bar['tanggal'] . "</td>";
            $tab.="<td align=center>" . @hidezerodecimal($bar['hk'],2) . "</td>";
            $tab.="<td align=right>" . @hidezerodecimal($bar['premi'],2) . "</td>";
            $tab.="<td align=center>" . @$nmkar[$bar['nikmandor']] . "</td>";
            $tab.="<td align=center>" . @$nmkar[$bar['nikmandor1']] . "</td>";
            $tab.="<td align=center>" . @$nmkar[$bar['keranimuat']] . "</td>";
            $tab.="<td align=center>" . @$nmkar[$bar['nikasisten']] . "</td>";
            $tab.="<td align=center>" . @$nmkar[$bar['updateby']] . "</td>";
            

            if ($bar['jurnal'] == 0) {
                $isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                    onclick=\"edit('".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".$bar['kodeorg']."','".$bar['nobkm']."','".$bar['nikmandor']."','".$bar['nikmandor1']."','".$bar['nikasisten']."','".$bar['keranimuat']."','".$no."');\" ></td>";
					
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('".$bar['notransaksi']."','".$no."');\" ></td>";

				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$isi.="<td align=center><img src=images/skyblue/posting.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['notransaksi']."','".$no."');\" ></td>";
				} else {
					$isi.="<td align=center><img src=images/skyblue/posting.png class=resicon class=zImgBtn height='30'  title='Posting'></td>";
				}
            }
			if ($bar['jurnal'] == 1) {
				$isi.="<td></td><td></td>";
				$isi.="<td align=center><img src=images/skyblue/posted.png class=resicon class=zImgBtn height='30'  title='Posted'></td>";
			}
			
            $isi.="<td align=center><img src=images/skyblue/pdf.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailPDF('".$bar['notransaksi']."','".$no."','event','".$statusblok."');\" ></td>";
            $isi.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','".$statusblok."','html');\" ></td>";
            $isi.="<td align=center><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','".$statusblok."','excel');\" ></td>";

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
                     <tr><td colspan=19 align=center>";

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
		
	/* case'getnotransaksi':
		$data = $_POST;
		# Data Capture & Reform
		$data['tipetransaksi'] = $statusblok;
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
			$noUrut = (int)$tmpRow[3];
			if($noUrut>$maxNo)
				$maxNo = $noUrut;
			}
			$currNo = addZero($maxNo+1,3);
			echo $data['notransaksi'] = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/".$currNo;
		}	
    break; */
}

function cekPrestasi($param) {
	global $dbname;
	global $owlPDO;
		
	$tgl=explode('/',$param['notransaksi']);
	$tgl=$tgl[0];
	
	#============== Validasi SESSION Status ==========+=========
	stsawal($param);
	#============ End Validasi SESSION Status ==================
	
	#cek HK perhari maksimal 1
	# Ambil nomor urut kary
	$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodeorg='".$param['blok']."' and kodekegiatan='".$param['kegiatan']."'";
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
	}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$jlhhk='';
		while($bar=$res->fetch()){ 
			$jlhhk+=$bar['jhk'];
			$trans.="No => ".$bar['notransaksi']." => ".$bar['jhk']." HK\n";
		}
		if(($param['jhk']+$jlhhk)>1){
			echo "Jumlah HK karyawan lebih dari 1, HK yang sudah tersimpan sebesar = ".$jlhhk." HK\n\n";
			echo $trans;
			exit("Error");
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
		
	if(@$jumtrans>0){
		exit("Warning : Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani");
	}

	# Cek Perawatan
	# Jika sudah ada di perawatan tidak bisa input panen
	# Jika karyawan ada pekerjaan panen dan perawatan, maka harus malekukan input panen terlebih dahulu
	$qAbs = selectQuery($dbname,'kebun_prestasi_vs_hk','karyawanid,sum(hkpanenperhari) as jhk',
								"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jhkpanen = $resAbs[0]['jhk'];
	
	if(intval($jhkpanen)!='0') {
		exit("Warning : Karyawan sudah terdaftar di kegiatan panen, silahkan kosongkan Jumlah HK untuk melanjutkan.");
	}
	
	#cek di vhc - kegiatan traksi
	$qAbs = selectQuery($dbname,'vhc_runhk','sum(upah) as jhk',
			"idkaryawan='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhkvhc = $resAbs[0]['jhk'];
	
	if(intval($jmlhkvhc)!='0') {
		exit("Warning : Karyawan sudah terdaftar di kegiatan traksi");
	}
	
	#cek di SDM
	$qAbs = selectQuery($dbname,'sdm_absensidt_vw','sum(nilaihk) as jhk',
			"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhksdm = $resAbs[0]['jhk'];
	
	if(intval($jmlhksdm)!='0') {
		exit("Warning : Karyawan sudah terdaftar di absensi SDM.");
	}
}


function stsawal($param) {
	global $dbname;
	global $owlPDO;
	
	$statusblok = $_SESSION['tmp']['kebun']['tipeTrans'];
	switch($statusblok) {
    case "TB":
	$title = "Land Clearing";
	break;
    case "BBT":
	$title = $_SESSION['lang']['pembibitan'];
	break;
    case "TBM":
	$title = "UPKEEP-".$_SESSION['lang']['tbm'];
	break;
    case "TM":
	$title = "UPKEEP-".$_SESSION['lang']['tm'];
	break;
	default:
	echo "Error : Planting type undefined, please reload frame";
	exit;
	break;
	}

	if($param['stsawal']!=$statusblok){
		exit("Error : SESSION anda sudah habis, silahkan reload frame atau buka ulang menu ini untuk melanjutkan.");
	}
}
?>	