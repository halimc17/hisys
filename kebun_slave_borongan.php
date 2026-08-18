<?php
#ini_set('display_errors',0);
#error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

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
$tt = checkPostGet('tt', '');
$nopengajuan = checkPostGet('nopengajuan', '');
$kepada = checkPostGet('kepada', '');
$tglpengajuan = (checkPostGet('tglpengajuan', ''));
$tipe = checkPostGet('tipe', '');
$jenis = checkPostGet('jenis', '');
$kriteriaefil = checkPostGet('kriteriaefil', '');
$namafile = checkPostGet('namafile', '');

$hargasat = checkPostGet('hargasat', '');
@$hargasat =str_replace(",","",$hargasat);
$prestasi = checkPostGet('prestasi', '');
@$prestasi =str_replace(",","",$prestasi);
@$param['rupiah'] =str_replace(",","",$param['rupiah']);
@$param['prestasi'] =str_replace(",","",$param['prestasi']);
@$param['jhk'] =str_replace(",","",$param['jhk']);
@$param['upah'] =str_replace(",","",$param['upah']);
@$param['premi'] =str_replace(",","",$param['premi']);

$palaborong = checkPostGet('palaborong', '');
$divisi = checkPostGet('divisi', '');
$noborong = checkPostGet('noborong', '');
$statusblok = checkPostGet('statusblok', '');

$divsch = checkPostGet('divsch', '');
$tglsch = tanggalsystemn(checkPostGet('tglsch', ''));
$notransaksisch = checkPostGet('notransaksisch', '');
$postingsrc = checkPostGet('postingsrc', '');
$statussch = checkPostGet('statussch', '');
$kepalaborongansch = checkPostGet('kepalaborongansch', '');
$nomorborongansch = checkPostGet('nomorborongansch', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$nmsat=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');		
$luasblok=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif');		

$stsawal = "BOR";


#============== KHT, KHL dan Kontrak ======================
	if($divisi!=''){
		@$whereKary=" and subbagian = '".$divisi."'";
	}else{
		@$whereKary.= " and lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
	}
	$whereKary.= " and tipekaryawan in (1,3,4,6) and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tgl."')";
	$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.subbagian from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." order by a.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optKary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['subbagian']."</option>";
	}
#============== KHT, KHL dan Kontrak ======================
#===================== Kode Blok ==========================
	@$whereBlok=" and substr(a.kodeorganisasi,1,6) = '".$divisi."'";
	@$whereBlok.=" and b.statusblok = '".$statusblok."'";
	
	$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".organisasi a 
			left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
			where a.tipe in('BLOK') ".$whereBlok." and b.luasareaproduktif>0 order by a.kodeorganisasi asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optBlok.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']."</option>";
	}
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
		
		if($statusblok==''){
			exit("Warning : Status tidak boleh kosong !!!");
		}
		if($divisi==''){
			exit("Warning : Divisi tidak boleh kosong !!!");
		}
		#=== insert header ===
        if ($mode=='edit') {
            $str = "update " . $dbname . ".kebun_aktifitas set `nospk`='".$noborong."',`nobkm`='".$divisi."', `noreferensi`='".$palaborong."', `updateby`='".$_SESSION['standard']['userid']."' where `notransaksi`='".$notransaksi."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        } else {
			#===== buat nomor transaksi =====
			$data = $_POST;
			# Data Capture & Reform
			$data['tipetransaksi'] = $stsawal;
			$data['tgl'] = tanggalsystem($data['tgl']);
			
			#=== Generate No Transaksi
			# Get Existing Data
			$fWhere = "tanggalinput='".$data['tgl']."' and kodeorg='".$data['kodeorg'].
				"' and notransaksi like '%".$data['tipetransaksi']."%'";
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
			$str = "insert into " . $dbname . ".kebun_aktifitas (`notransaksi`, `tipetransaksi`, `tanggalinput`, `nobkm`, `kodeorg`, `nikmandor`, `nikmandor1`, `nikasisten`, `keranimuat`, `jurnal`, `nospk`, `noreferensi`,`updateby`)
			values ('".$notransaksi."','".$statusblok."','".$tgl."','".$divisi."','" . $kodeorg . "','','','','','0','".$noborong."','".$palaborong."','" . $_SESSION['standard']['userid'] . "')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
			echo $notransaksi;
		}
	break;
    case'detail':
        OPEN_BOX();
		#==== Form Judul Detail ====
		#=== TAB PRESTASI ===
		$frm[0]='';
		$frm[1]='';
        
		$frm[0]="
			<fieldset>
			<legend>" . $_SESSION['lang']['detail'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$frm[0].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows." width=120px><font color=red><b>* </font></b>".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows." width=220px><font color=red><b>* </font></b>".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows." width=30px>TT</td>
				<td align=center ".$rows." width=30px>".$_SESSION['lang']['luas'] . "</td>
				<td align=center ".$rows." width=30px>".$_SESSION['lang']['pokok'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center ".$rows." width=50px><font color=red><b>* </font></b>".$_SESSION['lang']['hargasatuan'] . "</td>
				<td align=center width=30px ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>".$_SESSION['lang']['satuan'] . "</td>
				<td align=center width=45px><font color=red><b>* </font></b>".$_SESSION['lang']['jumlah'] . "</td>
			</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		#=== Isi input detail ===
		$frm[0].="<tbody id=inputdetailpres>
					<tr class=rowcontent>
						<td align=center width=10px>#</td>
						<td><select style=width:95px onchange=getdetailblok() id=blok>".$optBlok."</select>
						<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
						
						<td><select style=width:195px onchange=getdetailblok() id=kegiatan>".$optKeg."</select>
							<img id='kegiatan' onclick=z.elSearch('kegiatan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
							</td>
							
						
						<td><input id=tt disabled class=myinputtextnumber style=\"width:35px;\"></td>
						<td><input id=luas disabled class=myinputtextnumber style=\"width:35px;\"></td>
						<td><input id=pkk disabled class=myinputtextnumber style=\"width:35px;\"></td>
						<td><input id=satuan class=myinputtext disabled style=\"width:40px;align:center\"></td>

						<td><input id=prestasi onkeyup=\"z.numberFormat('prestasi',2);\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
						
						<td><input id=hargasat onkeyup=\"z.numberFormat('hargasat',2);\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
						
					<td align=center><input id=methodprestasi value='insertprestasi' style=display:none>
						<img title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=\"saveprestasi(".$no.")\" src='images/save.png'/>
						<img title='".$_SESSION['lang']['clear']."' class=resicon onclick=\"clearprestasi(".$no.")\" src='images/clear.png'/>
					</td>
						
						
					</tr>
				  </tbody></table></fieldset>";
		
		#=== List data tersimpan input detail ===	
        $frm[0].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
			<div id=loaddataprestasi>
				<script>loaddataprestasi()</script>
			</div></fieldset>";
			
		#=== TAB MATERIAL ===
		$frm[1]="
			<fieldset>
			<legend>" . $_SESSION['lang']['absensi'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=1";	
			$frm[1].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['namakaryawan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['prestasi'] . "</td>
				<td align=center ".$rows." width=50px>".$_SESSION['lang']['hargasatuan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['action'] . "</td>
				
			</tr>
			</thead>";
			
			##Cek Harga Satuan
			$str="select outputminimal from ".$dbname.".kebun_prestasi where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			$hargasat=($res[0]['outputminimal']==''?0:$res[0]['outputminimal']);
			$frm[1].="<tbody>
					<tr class=rowcontent>
						<td align=center width=10px>#</td>
						<td><select style=width:250px id=karyawanid>".$optKary."</select>
						<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
						<td><input id=prestasiabs onkeyup=\"z.numberFormat('prestasiabs',2);gettotalabsensi();\" class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style=\"width:50px;\"></td>
						
						<td><input id=hargasatabs value='".$hargasat."' onkeyup=\"z.numberFormat('hargasatabs',2);\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\" disabled></td>
						
						<td><input id=rupiahabs onkeyup=\"z.numberFormat('rupiahabs',2);\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" disabled></td>
						
					<td align=center><input id=methodabsensi value='insertabsensi' style=display:none>
						<img title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=\"saveabsensi(".$no.")\" src='images/save.png'/>
						<img title='".$_SESSION['lang']['clear']."' class=resicon onclick=\"clearabsensi(".$no.")\" src='images/clear.png'/>
					</td>
						
						
					</tr>
				  </tbody></table></fieldset>";
		#==== Form Judul material ====
		$frm[1].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['absensi'] . "</legend>
			<div id=datadetailabsensi>
				<script>loaddatadetailabsensi()</script>
			</div></fieldset>";
	
		$hfrm[0]=$_SESSION['lang']['prestasi'];
		$hfrm[1]=$_SESSION['lang']['absensi'];

		# draw tab, jangan ganti parameter pertama, krn dipakai di javascript
		drawTab('FRM',$hfrm,$frm,175,'100%');

        #CLOSE_BOX();
		
	break;
	case'getdetailblok':
		$str="select * from ".$dbname.".setup_blok where kodeorg = '".$blok."'";
		$res=fetchData($str);
		
		$i="select * from ".$dbname.".setup_kegiatan where kodekegiatan = '".$kegiatan."'";
		$r=fetchData($i);
		
		echo @$res[0]['tahuntanam']."####".@$res[0]['luasareaproduktif']."####".@$res[0]['jumlahpokok']."####".@$r[0]['satuan'];
	break;
	case'insertabsensi':
		if($param['prestasi']==''){
			$param['prestasi']='0';
		}
		
		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['notransaksi']!='' and $param['karyawanid']!='' and $param['rupiah']!='' and $param['prestasi']!='0'){
		
		# Cek sudah ada atau belum ???
		$str = "select * from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."' and nik ='".$param['karyawanid']."'";
		$res=fetchData($str);
		if(count($res)>0){
			exit("Error : Data sudah ada !");
		}
			$i = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."'"; 
			$r=fetchData($i);
			$prestasi = $r[0]['hasilkerja'];
			
			$n = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."'"; 
			$x=fetchData($n);
			$presabs = $x[0]['hasilkerja'];
			
			if(($presabs+$param['prestasi'])>$prestasi){
				exit("Warning : Prestasi / Hasil Kerja lebih besar dari Total Prestasi / Hasil Kerja di Tab Prestasi !!!");
			}
			
			# Buat nomor urut
			$sql = "select max(nourut) as nourut from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."' limit 1"; 
			$res=fetchData($sql);
			$cols = array(
						'notransaksi','nourut','nik','absensi','jhk','umr','insentif','hasilkerja'
					);
			$data = array(
						$param['notransaksi'],($res[0]['nourut']+1),$param['karyawanid'],'H','','',$param['rupiah'],$param['prestasi']
					);

			# Insert kebun_kehadiran
			$query = insertQuery($dbname,'kebun_kehadiran',$data,$cols);
			try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
		}else{
			exit("Warning : No transaksi, Karyawan, Prestasi dan Jumlah tidak boleh kosong !!!");
		}
	break;
	case'updateabsensi':
		$i = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."'"; 
		$r=fetchData($i);
		$prestasi = $r[0]['hasilkerja'];
		
		$n = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."'"; 
		$x=fetchData($n);
		$presabs = $x[0]['hasilkerja'];
		
		$d = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."' and nik='".$param['karyawanid']."'"; 
		$e=fetchData($d);
		$absmin = $e[0]['hasilkerja'];
		
		
		
		if((($presabs+$param['prestasi'])-$absmin)>$prestasi){
			exit("Warning : Prestasi / Hasil Kerja lebih besar dari Total Prestasi / Hasil Kerja di Tab Prestasi !!!");
		}
		$str = "update " . $dbname . ".kebun_kehadiran set `insentif`='".$param['rupiah']."', hasilkerja='".$param['prestasi']."' where `notransaksi`='".$notransaksi."' and nik='".$param['karyawanid']."'";
		// exit("error : ".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'loaddatadetailabsensi':
		$tab='';
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style=min-width:545px>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
		$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['namakaryawan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['prestasi'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['hargasatuan'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center width=30px colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			</thead><tbody>";
				
		$str = "select * from ".$dbname.".kebun_kehadiran where 1=1 and notransaksi='".$notransaksi."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$no+=1;
			$nmnik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$bar['nik']."'");
			$optharga = makeOption($dbname,'kebun_prestasi','notransaksi,outputminimal',"notransaksi='".$notransaksi."'");
			$hargasatuan = $optharga[$notransaksi];
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$nmnik[$bar['nik']]." - ".$nmkar[$bar['nik']]."</td>";
			$tab.="<td align=right>".@number_format($bar['hasilkerja'],2)."</td>";
			$tab.="<td align=right>".@number_format($hargasatuan,2)."</td>";
			$tab.="<td align=right>".@number_format($bar['insentif'],2)."</td>";
			$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                    onclick=\"editabs('".$bar['notransaksi']."','".$bar['nik']."','".$bar['hasilkerja']."','".hidezerodecimal($bar['insentif'],2)."','".$no."');\" ></td>";
					
			$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"delabs('".$bar['notransaksi']."','".$bar['nik']."');\" ></td>";
					
			$tab.="</tr>";
		}
		$tab.="</tbody></table>";
	echo $tab;
	break;
	case'insertprestasi':
		if($param['prestasi']==''){
			$param['prestasi']='0';
		}
		
		if($hargasat==''){
			$hargasat='0';
		}
		
		if($hargasat < 1){
			exit("Gagal, Harga satuan belum diisi atau harus lebih besar dari 0");
		}
		
		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['notransaksi']!='' and $param['kegiatan']!='' and $param['blok']!='' and $param['prestasi']!='0'){
		
		# Cek sudah ada atau belum ???
		$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."'";
		$res=fetchData($str);
		if(count($res)>0){
			exit("Error : Data sudah ada !");
		}
			
			# ==========================================================================================
			$cols = array(
						'notransaksi','nik','kodekegiatan','kodeorg','tahuntanam','hasilkerja','keterangan','outputminimal'
					);
			$data = array(
						$param['notransaksi'],'-',$param['kegiatan'],$param['blok'],$param['tt'],$param['prestasi'],'BORONGAN',$hargasat
					);

			# Insert kebun_prestasi
			$query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
			try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
			# ==========================================================================================
		}else{
			exit("Warning : No transaksi, Blok, Kegiatan dan Jumlah tidak boleh kosong !!!");
		}
	break;
	case'updateprestasi':
		if($hargasat==''){
			$hargasat=0;
		}
		if($hargasat < 1){
			exit("Gagal, Harga satuan belum diisi atau harus lebih besar dari 0");
		}
		$n = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_kehadiran where notransaksi='".$param['notransaksi']."'"; 
		$x=fetchData($n);
		
		if($prestasi<$x[0]['hasilkerja']){
			exit("Warning : Jumlah prestasi di Tab Absensi sudah sebesar ".number_format($x[0]['hasilkerja'])."\nPrestasi di Tab ini tidak boleh kurang dari ".number_format($x[0]['hasilkerja'])." !!!");
		}
		$str = "update " . $dbname . ".kebun_prestasi set `hasilkerja`='".$prestasi."', tahuntanam='".$tt."', outputminimal='".$hargasat."' where `notransaksi`='".$notransaksi."' and kodeorg='".$blok."' and kodekegiatan='".$kegiatan."'";
		try {$owlPDO->exec($str);
			$str="select * from ".$dbname.".kebun_kehadiran where notransaksi='".$param['notransaksi']."'";
			$res=fetchdata($str);
			foreach($res as $val){
				$strx="update ".$dbname.".kebun_kehadiran set insentif='".($hargasat*$val['hasilkerja'])."' where notransaksi='".$val['notransaksi']."' and nik='".$val['nik']."'";
				$owlPDO->exec($strx);
			}
		} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'loaddataprestasi':
		$tab='';
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style=min-width:685px>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
		$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center ".$rows." width=30px>TT</td>
				<td align=center ".$rows." width=30px>".$_SESSION['lang']['luas'] . "</td>
				<td align=center ".$rows." width=30px>".$_SESSION['lang']['pokok'] . "</td>
				<td align=center colspan=2 width=50px>".$_SESSION['lang']['hasilkerja2'] . "</td>
				<td align=center ".$rows." width=50px>".$_SESSION['lang']['hargasatuan'] . "</td>
				<td align=center width=30px colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>".$_SESSION['lang']['satuan'] . "</td>
				<td align=center width=45px>".$_SESSION['lang']['jumlah'] . "</td>
			</tr>
			</thead><tbody>";
				
		$str = "select * from ".$dbname.".kebun_prestasi where 1=1 and notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$no+=1;
			$nmtt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
			$nmpk=makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$bar['kodeorg']."'");
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['kodeorg']."</td>";
			$tab.="<td>".$bar['kodekegiatan']." - ".$nmkeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=center>".$nmtt[$bar['kodeorg']]."</td>";
			$tab.="<td align=center>".$luasblok[$bar['kodeorg']]."</td>";
			$tab.="<td align=center>".$nmpk[$bar['kodeorg']]."</td>";
			$tab.="<td align=center>".$nmsat[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=right>".@number_format($bar['hasilkerja'],2)."</td>";
			$tab.="<td align=right>".@number_format($bar['outputminimal'])."</td>";
			
			$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                    onclick=\"editpres('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['kodekegiatan']."','".$nmtt[$bar['kodeorg']]."','".$luasblok[$bar['kodeorg']]."','".$nmpk[$bar['kodeorg']]."','".$nmsat[$bar['kodekegiatan']]."','".$bar['hasilkerja']."','".$bar['outputminimal']."','".$no."');\" ></td>";
					
			$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"delpres('".$bar['notransaksi']."','".$bar['kodekegiatan']."','".$bar['kodeorg']."','".$no."');\" ></td>";
					
			$tab.="</tr>";
		}
		$tab.="</tbody></table>";
	echo $tab;
	break;
	
	case'delpres':
		#hapus kehadiran
		$str = "delete from " . $dbname . ".kebun_kehadiran where notransaksi ='".$notransaksi."'";
		try {$owlPDO->exec($str);
			#hapus prestasi	
			$str = "delete from " . $dbname . ".kebun_prestasi where notransaksi ='".$notransaksi."' and kodeorg='" . $blok . "'  and kodekegiatan='".$kegiatan."'";
			try {$owlPDO->exec($str);
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
		} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
    break;
	case'delabs':
		#hapus kehadiran
		$str = "delete from " . $dbname . ".kebun_kehadiran where notransaksi ='".$notransaksi."' and nik='".$karyawanid."'";
		try {$owlPDO->exec($str);
		} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'getdata':	
		echo $optKary."######".$optBlok;
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
	case'posting':
		$str = "update " . $dbname . ".kebun_aktifitas set `jurnal`='1' where `notransaksi`='".$notransaksi."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    case'loaddata':
		#validasi
		#stsawal($param);
		$e='';
		$e.="<table class='sortable' cellspacing='1' border='0'>";
		$e.="<thead><tr>";
		$e.="<th align=center>No</th>";
		$e.="<th align=center>No Jurnal</th>";
		$e.="<th align=center>No Voucher</th>";
		$e.="<th align=center>Tanggal</th>";
		$e.="<th align=center>Organisasi</th>";
		$e.="<th align=center>No Akun</th>";
		$e.="<th align=center>Nama Akun</th>";
		$e.="<th align=center>Keterangan</th>";
		$e.="<th align=center>Debet</th>";
		$e.="<th align=center>Kredit</th>";
		$e.="<th align=center>No Referensi</th>";
		$e.="<th align=center>Blok</th>";
		$e.="<th align=center>TT</th>";
		$e.="<th align=center>Rev</th>";
		$e.="</tr></thead><tbody>";
		
        $where="";
		$where.= " and a.notransaksi like '%BOR%'";
		
		if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			if($_SESSION['empl']['subbagian']==''){
				$where.= " and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
			}else if($_SESSION['empl']['subbagian']!='' and $_SESSION['empl']['tipekaryawan']==0){
				$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and b.kodeorg like '".$_SESSION['empl']['subbagian']."%'"; 
			} else {
				$where.= " and a.nobkm like '".$_SESSION['empl']['subbagian']."%'";
			}
		}
		
        if ($divsch != '') {
            $where.=" and a.nobkm like '%" . $divsch . "%' ";
        }
        if (($tglsch != '') and ($tglsch != '--')) {
            $where.=" and a.tanggal like '%" . $tglsch . "%' ";
        }
		if ($notransaksisch != '') {
            $where.=" and a.notransaksi like '%" . $notransaksisch . "%' ";
        }
		if ($postingsrc != '') {
            $where.=" and a.jurnal ='" . $postingsrc . "' ";
        }
		if ($statussch != '') {
            $where.=" and a.tipetransaksi like '%" . $statussch . "%' ";
        }
		if ($kepalaborongansch != '') {
            $where.=" and a.noreferensi like '%".$kepalaborongansch."%' ";
        }
		if ($nomorborongansch != '') {
            $where.=" and a.nospk like '%".$nomorborongansch."%' ";
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

        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_aktifitas a where 1=1 " . $where . " group by a.notransaksi order by a.notransaksi desc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=20 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
        $str = "SELECT * FROM " . $dbname . ".kebun_aktifitas a where 1=1 " . $where . " group by a.notransaksi order by a.notransaksi desc limit " . $offset . "," . $limit . ""; #exit('error'.$str);
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
			if($bar['tanggalinput']=='0000-00-00'){
				$bar['tanggalinput']='';
			}else{
				$bar['tanggalinput']=$bar['tanggalinput'];
			}
            $tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
            $tab.="<td align=center>" . $bar['nobkm'] . "</td>";
            $tab.="<td align=center>" . $bar['tanggalinput'] . "</td>";
            $tab.="<td align=center>" . $bar['nospk'] . "</td>";
            $tab.="<td align=center>" . $bar['noreferensi'] . "</td>";
			
			##Get Kegiatan
			$strx="select b.namakegiatan from ".$dbname.".kebun_prestasi a left join ".$dbname.".setup_kegiatan b on a.kodekegiatan=b.kodekegiatan where a.notransaksi='".$bar['notransaksi']."'";
			$resx=fetchdata($strx);
			$namakegiatan=$resx[0]['namakegiatan'];
            $tab.="<td>".$namakegiatan."</td>";
			
            $tab.="<td align=center>" . @$nmkar[$bar['updateby']] . "</td>";
			$c='';
			if($bar['nopengajuan']!=''){
				$c=" style=cursor:pointer;color:blue; onclick=\"htmlborrekap('".$bar['nopengajuan']."','".$bar['kodeorg']."','".$no."','event','html');\"";
			}
			
            $tab.="<td align=center ".$c.">" . $bar['nopengajuan'] . "</td>";
            $tab.="<td align=center>".(($bar['tglpengajuan']!='0000-00-00')?$bar['tglpengajuan']:"")."</td>";
			
			# approval
			$warna='';
			if($bar['statuspersetujuan']=='3'){
				$warna=" style=background-color:red";
			}
			if($bar['statuspersetujuan']=='0' and $bar['nopengajuan']!=''){
				$arrHsl=array("0"=>"Diperlukan Persetujuan","1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);				
			}else{
				$arrHsl=array("0"=>'',"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);				
			}
            $tab.="<td align=center ".$warna.">" . $arrHsl[$bar['statuspersetujuan']] . "</td>";
			
			$strX = "select * from ".$dbname.".approval where notransaksi='".$bar['nopengajuan']."' and jenispersetujuan='BOR' order by level desc limit 1";
			$resX = $owlPDO->query($strX) or die(print " Gagal: " . PDOException::getMessage());
			$resX->setFetchMode(PDO::FETCH_ASSOC);
			$barX = $resX->fetch();
			if($barX['tanggal']==''|| $barX['tanggal']=='0000-00-00 00:00:00'){
				$tngl='';
			}else{
				$tngl=tanggalnormal($barX['tanggal']);
			}
			$optnmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$barX['karyawanid']."'");
			$countApp = getCountApproval('BOR');
			
			$strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='BOR' and kodeunit='".$bar['kodeorg']."' and level='".$countApp."'";
			$respo=fetchdata($strpo);
			$tipeapp = $respo[0]['tipe'];
			$departemenapp = $respo[0]['departemen'];
			$tipekaryawanapp = $respo[0]['tipekaryawan'];
			$jabatanapp = $respo[0]['jabatan'];
			
			if($countApp==$barX['level'] and $tipeapp=='1'){
				if($departemenapp!=''){
					$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
					$nm = $opttipe[$departemenapp];
				}
				
				if($tipekaryawanapp!=''){
					$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
					$nm = $opttipe[$tipekaryawanapp];
				}
				
				if($jabatanapp!='0'){
					$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
					$nm = $opttipe[$jabatanapp];
				}

				$nama = $nm;
			}else{
				$nama = @$optnmkary[$barX['karyawanid']];
			}
			
			
			$tab.="<td ".$warna.">
						".$nama." ".$tngl."
					<br>".$barX['komentar']."
					</td>";
			# end approval
			
            
			$nmjur=makeOption($dbname,'keu_jurnalht','noreferensi,nojurnal',"noreferensi='".$bar['notransaksi']."'");
			$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorg']."'");
			
            $tab.="<td align=center style=cursor:pointer;color:blue; title=\"Lihat Jurnal\" onclick=getLaporanJurnal('".$nmpt[$bar['kodeorg']]."','".tanggalnormal($bar['tanggal'])."','".$bar['kodeorg']."','".@$nmjur[$bar['notransaksi']]."')>" . @$nmjur[$bar['notransaksi']] . "</td>";
			
			if($bar['tanggal']=='0000-00-00'){
				$bar['tanggal']='';
			}else{
				$bar['tanggal']=$bar['tanggal'];
			}
            $tab.="<td align=center>".$bar['tanggal']."</td>";
			
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$tab.="<td colspan=3></td>";
			}else{
				if ($bar['jurnal'] == 0) {
					if($bar['nopengajuan']=='' or ($bar['nopengajuan']!='' and $bar['statuspersetujuan']==3)){
						$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
						onclick=\"edit('".$bar['notransaksi']."','".tanggalnormal($bar['tanggalinput'])."','".$bar['kodeorg']."','".$bar['nobkm']."','".$bar['nospk']."','".$bar['noreferensi']."','".$bar['tipetransaksi']."','".$no."');\" ></td>";
						
						$isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
						onclick=\"del('".$bar['notransaksi']."','".$no."');\" ></td>";

						$isi.="<td align=center><img src=images/skyblue/submit.jpg class=resicon class=zImgBtn height='30'  title='Ajukan !!!' onclick=\"form_ajukan('".$bar['notransaksi']."','".$bar['nobkm']."','".$bar['nopengajuan']."','".$no."');\" ></td>";
					}elseif($bar['statuspersetujuan']==1){	
						$isi.="<td></td><td></td>";
						$isi.="<td align=center><img src=images/skyblue/posting.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"formpostingData('".$bar['notransaksi']."','".$bar['nopengajuan']."','".$no."');\" ></td>";
						#$isi.="<td align=center><img src=images/skyblue/posting.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['notransaksi']."','".$no."');\" ></td>";
					} else {
						$isi.="<td></td><td></td>";
						$isi.="<td align=center></td>";
					}

				} else {
					$isi.="<td></td><td></td>";
					$isi.="<td align=center><img src=images/skyblue/posted.png class=resicon class=zImgBtn height='30'  title='Posted'></td>";
				}
			}
			
            $isi.="<td align=center><img src=images/skyblue/pdf.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','".$bar['tipetransaksi']."','pdf');\" ></td>";
            $isi.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','".$bar['tipetransaksi']."','html');\" ></td>";
            $isi.="<td align=center><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','".$bar['tipetransaksi']."','excel');\" ></td>";

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
                     <tr><td colspan=21 align=center>";

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

	case'formpostingData':
		$tab="";
		$tab.="<fieldset><legend>No Transaksi</legend>";
		$tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
					<tr class=rowheader>
						<td align=center width=30px>" . $_SESSION['lang']['nourut'] . "</td>
						<td align=center >" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center >" . $_SESSION['lang']['divisi'] . "</td>
						<td align=center >" . $_SESSION['lang']['tanggal'] . "</td>
						<td align=center >Hasil Kerja</td>
						<td align=center >" . $_SESSION['lang']['rupiah'] . "</td>
						<td align=center >" . $_SESSION['lang']['action'] . "</td>
					</tr>
				</thead>
				<tbody>"; 
		
		$ch=" checked";
		$wh=" and a.nopengajuan='".$nopengajuan."' and a.jurnal='0'";
	
		$str = "SELECT a.*, sum(b.insentif) as insentif, sum(b.hasilkerja) as hasilkerjaabs  
				FROM " . $dbname . ".kebun_aktifitas a 
				left join " . $dbname . ".kebun_kehadiran b on a.notransaksi=b.notransaksi 
				where a.notransaksi like '%BOR%' and a.jurnal='0' and (a.statuspersetujuan='1') 
				".$wh."  group by a.notransaksi order by notransaksi desc"; #exit('error'.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $no='';
		while ($bar = $res->fetch()) {
            $no+=1;
			$i='';
			$str = "SELECT sum(hasilkerja) as hasilkerjapre  FROM " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."'"; #exit('error'.$str);
			$e = fetchData($str);
			
			if($bar['hasilkerjaabs']!=$e[0]['hasilkerjapre']){
				$i=" style=background-color:red;cursor:pointer;";
				$i.=" title='Hasil Kerja di Tab Absensi tidak sama dengan Tab Prestasi'";
			}
			$tab.="<tr class=rowcontent id=row".$no.">";
            $tab.="<td align=center>".$no."</td>";	
            $tab.="<td align=center id=notr_".$no.">".$bar['notransaksi']."</td>";	
            $tab.="<td align=center>".$bar['nobkm']."</td>";	
            $tab.="<td align=center>".$bar['tanggalinput']."</td>";	
            $tab.="<td align=right ".$i.">".@number_format($e[0]['hasilkerjapre'])."</td>";	
            $tab.="<td align=right>".number_format($bar['insentif'])."</td>";	
			
			if($bar['hasilkerjaabs']==$e[0]['hasilkerjapre'] and $bar['insentif']>'0'){
				$tab.="<td align=center><input type='checkbox' ".$ch." id=ajukan".$no."></td>";	
			}else{
				$tab.="<td align=center><input type='checkbox' disabled id=ajukan".$no."></td>";	
			}
			
			
			$tab.="</tr>";
		}
		$tab.="</table>";
		$tab.="<hr>";
		$tab.="<table border=0>";
		$tab.="<tr>";
		$tab.="<td>Nomor Pengajuan</td><td>:</td><td id=nopengpost>".$nopengajuan."</td>";
		$tab.="</tr>";
		$tab.="<tr>";
		$tab.="<td>Tanggal</td><td>:</td><td><input type=text class=myinputtext style='width:100px;' id=tglpost onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 /></td>";
		$tab.="</tr>";
		$tab.="<tr>";
		$tab.="<td></td><td></td><td><button class=mybutton id=tombolpost onclick=\"postingall('".$no."');\">" . $_SESSION['lang']['posting'] . "</button></td>";
		$tab.="</tr>";
		$tab.="</table>";
		
		
		echo $tab;
	break;
	case'form_ajukan':
		$_SESSION['bgimage'] = array();
		$tab="";
		$tab.="<fieldset><legend>No Transaksi</legend>";
		$tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
					<tr class=rowheader>
						<td align=center width=40px>" . $_SESSION['lang']['nourut'] . "</td>
						<td align=center >" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center >" . $_SESSION['lang']['divisi'] . "</td>
						<td align=center >" . $_SESSION['lang']['tanggal'] . "</td>
						<td align=center >" . $_SESSION['lang']['kegiatan'] . "</td>
						<td align=center >Hasil Kerja</td>
						<td align=center >" . $_SESSION['lang']['rupiah'] . "</td>
						<td align=center >" . $_SESSION['lang']['action'] . "</td>
					</tr>
				</thead>
				<tbody>"; 
		$wh=$ch='';
		if($nopengajuan!=''){
			$ch=" checked";
			$wh=" and a.nopengajuan='".$nopengajuan."'";
		}else{
			$wh=" and a.nopengajuan=''";
		}
		
		$str = "SELECT a.*, sum(b.insentif) as insentif, sum(b.hasilkerja) as hasilkerjaabs  
				FROM " . $dbname . ".kebun_aktifitas a 
				left join " . $dbname . ".kebun_kehadiran b on a.notransaksi=b.notransaksi 
				where a.notransaksi like '%BOR%' and a.jurnal='0' and (a.statuspersetujuan='0' or a.statuspersetujuan='3') 
				".$wh." and a.nobkm='".$divisi."' group by a.notransaksi order by notransaksi desc"; #exit('error'.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $no='';
		while ($bar = $res->fetch()) {
            $no+=1;
			$i='';
			$str = "SELECT sum(hasilkerja) as hasilkerjapre, kodekegiatan  FROM " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."'"; #exit('error'.$str);
			$e = fetchData($str);
			
			if($bar['hasilkerjaabs']!=$e[0]['hasilkerjapre']){
				$i=" style=background-color:red;cursor:pointer;";
				$i.=" title='Hasil Kerja di Tab Absensi tidak sama dengan Tab Prestasi'";
			}
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";	
            $tab.="<td align=center id=notr_".$no.">".$bar['notransaksi']."</td>";	
            $tab.="<td align=center>".$bar['nobkm']."</td>";	
            $tab.="<td align=center>".$bar['tanggalinput']."</td>";	
			
			##Get Kegiatan
			$optkgtn = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$e[0]['kodekegiatan']."'");
			$tab.="<td>".$optkgtn[$e[0]['kodekegiatan']]."</td>";
			
            $tab.="<td align=right ".$i.">".@number_format($e[0]['hasilkerjapre'])."</td>";	
            $tab.="<td align=right>".number_format($bar['insentif'])."</td>";	
			
			if($bar['hasilkerjaabs']==$e[0]['hasilkerjapre'] and $bar['insentif']>'0'){
				$tab.="<td align=center><input type='checkbox' ".$ch." id=ajukan".$no."></td>";	
			}else{
				$tab.="<td align=center><input type='checkbox' disabled id=ajukan".$no."></td>";	
			}
			
			@$ttl+=$bar['insentif'];
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=6>TOTAL</td>";	
		$tab.="<td align=right>".number_format($ttl)."</td>";	
		$tab.="<td align=right></td>";	
		$tab.="</tr>";
		
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='BOR' and a.level='1' and a.kodeunit='".substr($divisi,0,4)."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
				
		$tab.="</tbody>
			</table>";
		$tab.="</fieldset>";
		
		if($nopengajuan==''){
			$nopengajuan=date("YmdHis");
		}else{
			$nopengajuan=$nopengajuan;
		}
		
		##Upload File
		$arrmodul = getmodulefil($stsawal);
		foreach($arrmodul as $key=>$val){
			@$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}
		$str="select * from ".$dbname.".listfile_kebun_borongan where notransaksi='".$nopengajuan."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			foreach($res as $val){
				$newdata = array(
					'namafile'=>$val['namafile'],
					'filetype'=>$val['filetype'],
					'kriteriaefil'=>$val['kriteriaefil']
				);
				
				array_push($_SESSION['bgimage'],$newdata);
			}
		}
		$tab.="<fieldset><legend>Upload File</legend>";
		$tab.="<table class=sortable cellspacing=1 border=0>
				<thead> 
				<tr>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['kriteria']."</td>
					<td align=center>".$_SESSION['lang']['namafile']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody id=containerupload></tbody>
				<tbody>
				<tr>
					<td></td>
					<td>
						<select id='kriteriaefil'>". $optkriteria."</select>
					</td>
					<td>
						<input type='file' name='upload' id='upload' class=mybutton>
					</td>
					<td style='text-align:center'>
						<img src=images/plus.png class=resicon id='addfile'  title='Add File ' onclick=\"addfile('".$nopengajuan."');\">
					</td>
				</tr>
				</tbody>
			</table>
		</fieldset>"; 
		
		
		$tab.="<fieldset><legend>Ajukan</legend>";
		$tab.="<table>";
		$tab.="<tr><td>Nomor Pengajuan</td><td>:</td><td id=nopengajuan>".$nopengajuan."</td></tr>";
		$tab.="<tr><td>Tanggal Pengajuan</td><td>:</td><td id=tglpengajuan>".date("Y-m-d")."</td></tr>";
		$tab.="<tr><td>Kepada</td><td>:</td><td><select id=kepada style='width:150px;'>".$optKry."</select></td></tr>";
		$tab.="<tr><td></td><td></td><td><button id=tomboldetail class=mybutton onclick=ajukan('".$no."')>" . $_SESSION['lang']['diajukan'] . "</button></td></tr>";
		
		$tab.="</table>";
		$tab.="</fieldset>";
		
		
		echo $tab;
	break;
	case'ajukan':
	try {
		$owlPDO->beginTransaction();
		
		if(isset($_SESSION['bgimage'])){
			foreach($_SESSION['bgimage'] as $key=>$row){
				$str="insert into ".$dbname.".listfile_kebun_borongan values ('','".$nopengajuan."','".$row['namafile']."','".$row['filetype']."','".$row['kriteriaefil']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}
		
		# update flag menjadi 1
        $str = "update " . $dbname . ".kebun_aktifitas set nopengajuan='".$nopengajuan."',statuspersetujuan='0', tglpengajuan='".$tglpengajuan."' where notransaksi in (".$notransaksi.")";
        $owlPDO->exec($str);
		
		# cari dulu apakah sudah pernah di ajukan sebelumnya
		$tglhi = date("Ymd");
		$str="select * from ".$dbname.".approval where jenispersetujuan='BOR' and notransaksi='".$nopengajuan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['notransaksi']!=''){
				# jika ada pindahkan ke table ini
				$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
				$owlPDO->exec($str);
			}
		}
		
		#kemudian setelah di pindah, hapus persetujuan lama
		$str="delete from ".$dbname.".approval where jenispersetujuan='BOR' and notransaksi='".$nopengajuan."'";
		$owlPDO->exec($str);
		
		#insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','".$nopengajuan."','BOR','1','" . $kepada."','0','','','')";
		$owlPDO->exec($str);
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
	case'html':
		$optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		$optSatKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
		
        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $men='menu.css';
          $gen='generic.css';
        }else if($theme=='red'){
          $men='menuRed.css';
          $gen='genericRed.css';  
        }else{
          $men='menuGray.css';
          $gen='genericGray.css';  
        }         
        $tab='';
		$tab.="<fieldset style=min-height:100%><legend>Preview</legend>";
		if($jenis=='html'){
			$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
			$border="border=0 cellspacing=1 cellpadding=1";
		} else {
			$border="border=1 cellspacing=0 cellpadding=1 ";
		}
		
		#approval
		$tab.="<span><b>Approval</b></span>";
		if($jenis=='excel' or $jenis=='pdf'){
			$tab.="<table  border=1 cellspacing=0 cellpadding=1 class=sortable>";
		}else{
			$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
		}
		$nopeng=makeOption($dbname,'kebun_aktifitas','notransaksi,nopengajuan',"notransaksi='".$notransaksi."'");
		$nnorg=makeOption($dbname,'kebun_aktifitas','notransaksi,kodeorg',"notransaksi='".$notransaksi."'");
		
		$countApprove = getCountApproval('BOR',$nnorg[$notransaksi]);
		$str=" select * from ".$dbname.".kebun_aktifitas where  nopengajuan='".$nopeng[$notransaksi]."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$tab.= "<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>
				<td valign=top>".$nmkar[$bar['updateby']]."<br>
					".$bar['lastupdate']."</td>";
					
		for($i=1;$i<=$countApprove;$i++){
			$arrApp = detailApprove($i,$nopeng[$notransaksi],'BOR');
			if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
				$tngl='';
			}else{
				$tngl=tanggalnormal($arrApp['tanggal']);
			}
			$optstatus=array("0"=>"Diperlukan Persetujuan","1"=>"Disetujui","2"=>"Dikoreksi","3"=>"Ditolak","9"=>"Proses Pengajuan");
			if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
				$tab.= "<td valign=top>".$arrApp['nama']."
						<br>".$optstatus[$arrApp['status']]."
						<br>".$tngl."
						<br>".$arrApp['komentar']."
						</td>";
			}else{
				$tab.= "<td>&nbsp;</td>";
			}
		}
		$tab.= "</tbody></table>";
		
		#status tolak
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		
		$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$nopeng[$notransaksi]."' group by keterangan";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				$tab.="<br>";
				if($jenis=='excel' or $jenis=='pdf'){
					$tab.="<table  border=1 cellspacing=0 cellpadding=1 class=sortable>";
				}else{
					$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
				}
				$tab.="<thead>
						<tr style='font-weight:bold'>
							<td colspan='".(1+$val['level'])."'>Return / Tolak - ".$no."</td>
						</tr>
						<tr style='font-weight:bold'>
							<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
							for($i=1;$i<=$val['level'];$i++) {
								$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
							}
						$tab.="</tr>
					</thead>
					<tbody>
						<tr class=rowcontent>
							<td valign=top>".$nmkar[$bar['updateby']]."<br>
											".$bar['lastupdate']."</td>";
							for($i=1;$i<=$val['level'];$i++) {
								$strx="select * from ".$dbname.".approval_return where notransaksi='".$nopeng[$notransaksi]."' and level='".$i."' and keterangan='".$val['keterangan']."'";
								$resx=fetchdata($strx);
								$color='';
								if($resx[0]['status']==3){
									$color=" style=background-color:red ";
								}
								$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
									<br>	
									".$arrHsl[$resx[0]['status']]."
									<br>	
									".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
									<br>	
									".$resx[0]['komentar']."
								</td>";
							}
						$tab.="</tr>
					</tbody>
					</table>";
			}
		}
		$tab.="<br>";
		#end status tolak
		
		$tab.="<table ".$border." class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td><b> ".$notransaksi."</b></td></tr>";
        
        $tab.="</tbody></table>";
        $tab.="<br /><b>Prestasi</b><br />";    
        $tab.="<table ".$border." class=sortable width=100%><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>No</td>";
        $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['namakegiatan']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['hasilkerjad']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['hargasatuan']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['rupiah']."</td>";
        $tab.="</tr></thead><tbody>";
        
		$sPres="select sum(a.insentif) as upahpremi, sum(a.umr) as umr,sum(a.jhk) as jumlahhk,kodekegiatan,
                tanggalinput,b.kodeorg, sum(a.hasilkerja) as hasilkerja, (b.outputminimal) as outputminimal 
				from ".$dbname.".kebun_kehadiran a 
				left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi 
                left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi 
				where a.notransaksi='".$notransaksi."' group by a.notransaksi, kodekegiatan, b.kodeorg order by kodekegiatan asc, b.kodeorg asc"; #exit('error'. $sPres);
        $qPres=$owlPDO->query($sPres) or die(print " Gagal: ".PDOException::getMessage());
		$no=$thk=$tumr=$tpremi=$tpres=0;
        while($rPres=$qPres->fetch()){
			 $no+=1;
             $tab.="<tr class=rowcontent>";
             $tab.="<td align=center>".$no."</td>";
             $tab.="<td>".tanggalnormal($rPres['tanggalinput'])."</td>";
             $tab.="<td>".@$rPres['kodeorg']."</td>";
             $tab.="<td>".@$rPres['kodekegiatan']." - ".@$optKegiatan[$rPres['kodekegiatan']]."</td>";
             $tab.="<td>".@$optSatKegiatan[$rPres['kodekegiatan']]."</td>";
             $tab.="<td align=right>".@hidezerodecimal($rPres['hasilkerja'],2)."</td>";
             $tab.="<td align=right>".@hidezerodecimal($rPres['outputminimal'],0)."</td>";
             $tab.="<td align=right>".@hidezerodecimal($rPres['hasilkerja']*$rPres['outputminimal'],0)."</td>";
             $tab.="</tr>";

			 $tpres+=$rPres['hasilkerja'];
			 $tpremi+=($rPres['hasilkerja']*$rPres['outputminimal']);
		}
			 
			 $tab.="<tr class=rowcontent>";
             $tab.="<td align=center colspan=5>".$_SESSION['lang']['total']."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($tpres,2)."</td>";
			 $tab.="<td  align=right></td>";
             $tab.="<td  align=right>".@hidezerodecimal($tpremi,2)."</td>";
             $tab.="</tr>";
			 
         $tab.="</table>";
         $tab.="<br /><b>Absensi</b><br />";
      
            $tab.="<table  ".$border." class=sortable width=100%><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<td align=center>No</td>";
            $tab.="<td align=center>".$_SESSION['lang']['kegiatan']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['blok']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['nama']."</td>";
			$tab.="<td align=center>".$_SESSION['lang']['hasilkerjad']."</td>";
			$tab.="<td align=center>".$_SESSION['lang']['hargasatuan']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['upahpremi']."</td>";
            $tab.="</tr></thead><tbody>";
			
			$optNamaKary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
			$optNIKary=makeOption($dbname, 'datakaryawan', 'karyawanid,nik');

            $totJhk=$totUmr=$totInsentif=$tothasilkerja=0;
            $sKhdrn="select a.nik, a.absensi, a.insentif, a.umr, jhk, kodekegiatan,tanggal,b.kodeorg,a.hasilkerja 
				from ".$dbname.".kebun_kehadiran a 
				left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi 
                left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi 
				where a.notransaksi='".$notransaksi."' order by kodekegiatan asc, b.kodeorg asc, nik asc"; 
            $qKhdrn=$owlPDO->query($sKhdrn) or die(print " Gagal: ".PDOException::getMessage());
            $qKhdrn->setFetchMode(PDO::FETCH_ASSOC);                       
            @$no='';
			while($rKhdrn=$qKhdrn->fetch()){
			 @$no+=1;
             $tab.="<tr class=rowcontent>";
             $tab.="<td align=center>".$no."</td>";
             $tab.="<td>".@$optKegiatan[$rKhdrn['kodekegiatan']]."</td>";
             $tab.="<td>".$rKhdrn['kodeorg']."</td>";
             $tab.="<td>".@$optNIKary[$rKhdrn['nik']]." - ".@$optNamaKary[$rKhdrn['nik']]."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['hasilkerja'],2)."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['insentif']/$rKhdrn['hasilkerja'],2)."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['insentif'],2)."</td>";
             $tab.="</tr>";
             $totJhk+=$rKhdrn['jhk'];
             $totUmr+=$rKhdrn['umr'];
             $totInsentif+=$rKhdrn['insentif'];
			 $tothasilkerja+=$rKhdrn['hasilkerja'];
            }
             $tab.="<tr class=rowcontent>";
             $tab.="<td align=center colspan=4>".$_SESSION['lang']['total']."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($tothasilkerja,2)."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($totInsentif/$tothasilkerja,2)."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($totInsentif,2)."</td>";
             $tab.="</tr>";
        $tab.="</table><br />";
		
        if($jenis=='html'){
			echo $tab;
		} elseif($jenis=='pdf') {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('Legal', 'landscape');
			$dompdf->render();
			$dompdf->stream("BOR",array("Attachment"=>0));
		}else{	$not=str_replace('/','',$param['notransaksi']);
			$stream = $tab;
			$nop_ = "detail_".$not;
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
								parent.window.alert('Cant convert to excel format');
								</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
				}
				closedir($handle);
			}
		}
		
    break;
	
	case 'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
				{
					if($_FILES['file']['size'] <= 250000)
					{
						$newdata = array(
							'namafile'=>$filename,
							'filetype'=>$filetype,
							'kriteriaefil'=>$kriteriaefil
						);
						
						if($_SESSION['bgimage'] != array())
						{
							foreach($_SESSION['bgimage'] as $key=>$row)
							{
								if($row['namafile'] == $filename)
								{
									exit("Warning : Item ini sudah pernah diinput sebelumnya.");
								}
							}
							array_push($_SESSION['bgimage'],$newdata);
						}else{
							array_push($_SESSION['bgimage'],$newdata);
						}
						if (!file_exists("fileupload/kebun_borongan/")) {
							mkdir("fileupload/kebun_borongan/", 0777, true);
						}
						move_uploaded_file($file_tmpname,"fileupload/kebun_borongan/$filename");
					}
					else
					{
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
			}
		}
	// $tgl = date("YmdHis");
	// $his = date("His");
	// $data = $_POST;
	// if($data['fileupload']!=''){
		// if($_FILES['file']['error']==0){
			// $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			// $filename = $_FILES['file']['name'];
			// $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			// if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
				// /*if($_FILES['file']['size'] <= 250000){*/
					// $str="select * from ".$dbname.".listfile_kebun_borongan where notransaksi = '".$nopengajuan."' and status='1' and namafile='".$filename."'";
					// $res=fetchData($str);
					// if(count($res)>0){exit("Warning : Nama file sudah ada !!!");}
					// $str = "insert into ".$dbname.".listfile_kebun_borongan values ('','".$nopengajuan."','".$filename."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')"; #exit("error".$str);
					// try{
						// $owlPDO->exec($str);
						// if (!file_exists($path)) {
							// mkdir($path, 0777, true);
						// }
						// file_put_contents($path.$filename,$file_tmpname);
					// }
					// catch(PDOException $e){
						// echo " Gagal," . addslashes($e->getMessage());
					// }
				// /*}else{
					// exit("warning : Ukuran file upload maksimal 250kb");
				// }*/
			// }else{
				// exit("Warning : Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
			// }
		// }
	// }
	break;
	
	case'loadfiles':
		$tab="";
		$no=0;
		foreach($_SESSION['bgimage'] as $key=>$row)
		{
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:right'>".$no."</td>";
			$tab.="<td>".$row['kriteriaefil']."</td>";
			$tab.="<td><a href='fileupload/kebun_borongan/".$row['namafile']."' download>".substr($row['namafile'],0,30)."...</a></td>";
			$tab.="<td style='text-align:center'>
				<img title='Delete' class=resicon onclick=\"deletefile('".$row['namafile']."','".$row['kriteriaefil']."')\" src='images/delete_32.png'/
			</td>";
			$tab.="</tr>";
		}
		
		echo $tab;
	break;
	
	case 'deletefile':
		foreach($_SESSION['bgimage'] as $key=>$row){
			if($row['namafile'] == $namafile && $row['kriteriaefil']==$kriteriaefil){
				$path = "fileupload/kebun_borongan/".$namafile;
				unlink($path);
				unset($_SESSION['bgimage'][$key]);
			}
		}
	break;
}

?>	