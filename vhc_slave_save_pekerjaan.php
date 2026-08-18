<?php
//error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kode_jns=	isset($_POST['jns_id'])? $_POST['jns_id']: '';
$kodetraksi=isset($_POST['traksi_id'])? $_POST['traksi_id']: '';
$lokasi=	$_SESSION['empl']['lokasitugas'];
$user_entry=$_SESSION['standard']['userid'];
$kode_vhc=	isset($_POST['kode_vhc'])? $_POST['kode_vhc']: '';
$tgl_kerja=	isset($_POST['tglKerja'])? tanggalsystem($_POST['tglKerja']): '';
$kmhmAwal=	isset($_POST['kmhmAwal'])? $_POST['kmhmAwal']: '';
$kmhmAkhir=	isset($_POST['kmhmAkhir'])? $_POST['kmhmAkhir']: '';
$satuan=	isset($_POST['satuan'])? $_POST['satuan']: '';
$jnsBbm=	isset($_POST['jnsBbm'])? $_POST['jnsBbm']: '';
$jumlahBbm=	isset($_POST['jumlah'])? $_POST['jumlah']: '';
$notransaksi_head=	isset($_POST['no_trans'])? $_POST['no_trans']: '';
$kdVhc=		isset($_POST['kdVhc'])? $_POST['kdVhc']: '';
$premiStat=	isset($_POST['premiStat'])? $_POST['premiStat']: '';
$proses=	isset($_POST['proses'])? $_POST['proses']: '';
$noKntrk=	isset($_POST['noKntrk'])? $_POST['noKntrk']: '';
$kdOrg=		isset($_POST['kdOrg'])? $_POST['kdOrg']: '';
$kodeOrg=	isset($_POST['kodeOrg'])? $_POST['kodeOrg']: '';
$txtTgl=	isset($_POST['txtTgl'])? $_POST['txtTgl']: '';
$txtCari=	isset($_POST['txtCari'])? $_POST['txtCari']: '';
$statData=	isset($_POST['statData'])? $_POST['statData']: '';
$kodevhc_cari=	isset($_POST['kodevhc_cari'])? $_POST['kodevhc_cari']: '';
$kde_vhc= checkPostGet('kde_vhc','');
$jnsvhc= checkPostGet('jenis_vhc','');

if(count($_POST)>0){
	$param = $_POST;
}else{
	$param = $_GET;
}
switch($proses){
	case'getkodekend':
		$nmtipe = makeOption($dbname, 'vhc_5jenisvhc','jenisvhc,namajenisvhc');
		$str = "select distinct jenisvhc from " . $dbname . ".vhc_5master where kodetraksi='" . $param['kodetraksi'] . "'"; 
		$res = fetchdata($str);
		$optjns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($res as $bar){
			$optjns.="<option value=".$bar['jenisvhc'].">".$nmtipe[$bar['jenisvhc']]."</option>";
		}
		echo $optjns;
	break;
	case'getKodeVhc':
		$optKdvhc="";
		if($notransaksi_head=='') {
			$sql="select kodevhc,kodetraksi,nopol,detailvhc from ".$dbname.".vhc_5master 
			  where jenisvhc='".$kode_jns."' 
			  and kodetraksi like '%".$kodetraksi."%' and status=1"; //echo "warning:".$sql;
	
		} elseif($notransaksi_head!='') {
			$sVhc="select jenisvhc,kodevhc from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
			$res=$owlPDO->query($sVhc) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rVhc=$res->fetch();
			$kdVhc=$rVhc['kodevhc'];
			$sql="select kodevhc,kodetraksi,nopol,detailvhc from ".$dbname.".vhc_5master where jenisvhc='".$rVhc['jenisvhc']."' ";  //echo "warning:".$sql;
		}

		$bar=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$bar->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$bar->fetch()){
			$optKdvhc.="<option value='".$res['kodevhc']."' ".($res['kodevhc']==$kdVhc?'selected=selected':'').">".$res['kodevhc']." ".($res['nopol']!=''?"- ".$res['nopol']:'')." - ".$res['kodetraksi']." ".($res['detailvhc']!=''?"- ".$res['detailvhc']:'')."</option>";
		}
		
		$optjns = makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');

		$sjvch="select distinct jenisvhc from ".$dbname.".vhc_5master where kodetraksi ='".$kodetraksi."' order by jenisvhc";
		$optJnsvhc='';
		$res=$owlPDO->query($sjvch) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rjvch=$res->fetch()){
			$optJnsvhc.="<option value=".$rjvch['jenisvhc'].">".$rjvch['jenisvhc']."-".$optjns[$rjvch['jenisvhc']]."</option>";
		}
		
		
		
		echo $optKdvhc."####".$optJnsvhc;
		break;

		case'get_no_transaksi':
			$tgl=  date('Ymd');
			$bln = substr($tgl,4,2);
			$thn = substr($tgl,0,4);
			$notransaksi=$kdOrg."/RUN/".date('Y')."/".date('m')."/";
			$ql="select `notransaksi` from ".$dbname.".`vhc_runht` where notransaksi like '%".$notransaksi."%' order by `notransaksi` desc limit 0,1";
			$res=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$rp=$res->fetch();
			if(!isset($rp->notransaksi)) {
					$awal=1;
			} else {
				$awal=substr($rp->notransaksi,-4,4);
				$awal=intval($awal);

				$cekbln=substr($rp->notransaksi,-7,2);
				$cekthn=substr($rp->notransaksi,-12,4);
				if($thn!=$cekthn) {
						$awal=1;
				} else {
						$awal++;
				}
			}
		$counter=addZero($awal,4);
		$notransaksi=$kdOrg."/RUN/".$thn."/".$bln."/".$counter;

		$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$skary="select distinct(a.karyawanid),a.nama,b.nik from ".$dbname.".vhc_5operator a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.aktif='1' and b.lokasitugas='".$kdOrg."' ";//echo $skary;
		$res=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rkary=$res->fetch()){       
			$optKary.="<option value=".$rkary['karyawanid'].">".$rkary['nama']."&nbsp;[".$rkary['nik']."]</option>";
		}
		
		$optjns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$skary="select distinct(a.jenisvhc),b.namajenisvhc from ".$dbname.".vhc_5master a left join ".$dbname.".vhc_5jenisvhc b on a.jenisvhc=b.jenisvhc where a.status='1' and a.kodeorg='".$kdOrg."' ";#echo $skary;
		$res=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rkary=$res->fetch()){       
			$optjns.="<option value=".$rkary['jenisvhc'].">".$rkary['jenisvhc']." - ".$rkary['namajenisvhc']."</option>";
		}
		#exit("error");
		
		if($kdOrg=='')$notransaksi = '';
		echo $notransaksi."####".$optKary."####".$optjns;
	break;

	case'getkodekegiatan':
		
		$optkdvhc=makeOption($dbname,'vhc_5master','kodevhc,kelompokvhc');
		$kelvhc=$optkdvhc[$kde_vhc];
		
		$sjnskrj="select * from ".$dbname.".vhc_kegiatan where tipe ='traksi' and (kelompokvhc='".$kelvhc."' or kelompokvhc='GLOBAL') and (jenisvhc='".$jnsvhc."' or jenisvhc='GLOBAL') order by noakun asc";
		$optJnsKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query($sjnskrj) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rjnskrj=$res->fetch()){
			$d=substr($rjnskrj['kodekegiatan'],0,5);
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
				$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
			}
			$optJnsKerja.="<option value=".$rjnskrj['kodekegiatan'].">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){
				$optJnsKerja.="</optgroup>";
			}
		}
		
		echo $optJnsKerja;
		
	break;
	case'insert_header':
		$thn=substr($tgl_kerja,0,4);
		$bln=substr($tgl_kerja,4,2);
		$periode=$thn."-".$bln;
		if(($tgl_kerja=='')||($jumlahBbm=='')){
			echo"warning : Please Complete The Form";exit();
		}
		#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
		   if($tgl_kerja<$_SESSION['org']['period']['start']){
		   echo "Validation Error : Date out or range\nPeriode akutansi sudah ditutup.";
		   break;                        
		   }
		 #======================================================
		
		validasiInput($kodeOrg,'','TRK',tanggalsystemn(tanggalnormal($tgl_kerja)),$exit='1');
		
		$str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and
		kodeorg='".$kodeOrg."' and tutupbuku=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res);
		if($numrows>0)
		$aktif=true;
		else
		$aktif=false;
		if($aktif==true){
			exit("Error:Periode sudah tutup buku");
		}
		@$jumlah=$kmhmAkhir-$kmhmAwal;
		if($notransaksi_head==''){
			exit("Error:Notransaksi Tidak Boleh Kosong");
		}
		
		$str="select * from ".$dbname.".vhc_runht where kodevhc = '".$kode_vhc."' and tanggal = '".$tgl_kerja."'"; 
		if(count(fetchdata($str))>0){
			exit("Warning : Kendaraan ".$kode_vhc." pada tanggal ".tanggalnormal($tgl_kerja)." sudah diinput, silahkan cari di list data dan lakukan Edit !");
		}
		
		$sqlCek="select notransaksi from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'"; 
		$res=$owlPDO->query($sqlCek) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
	    $numrows=owlBaris($res);
		if($numrows<1){
			// Cek apakah kodevhc sudah ada di tanggal > tanggal input
			$qCek = selectQuery($dbname,'vhc_runht','max(tanggal) as tgl',
													"kodevhc = '".$kode_vhc."' and tanggal > '".$tgl_kerja."'");
			$resCek = fetchData($qCek);
			if(!empty($resCek[0]['tgl'])) {
				/*	exit("Warning: Kendaraan sudah ada transaksi di tanggal yang lebih besar.".
							 "\nTanggal transaksi terakhir ".tanggalnormal($resCek[0]['tgl']));*/
			}
			
			$createdtime=date('Y-m-d H:i:s');
			$sql="insert into ".$dbname.".vhc_runht 
			(`notransaksi`,`kodeorg`,`jenisvhc`,`kodevhc`,`tanggal`,`jenisbbm`,`jlhbbm`,`updateby`,`createdtime`) 
			values ('".$notransaksi_head."','".$kodeOrg."','".$kode_jns."','".$kode_vhc."','".$tgl_kerja."','".$jnsBbm."','".$jumlahBbm."','".$user_entry."','".$createdtime."')";
			try{$owlPDO->exec($sql); 
				
				$optkdvhc=makeOption($dbname,'vhc_5master','kodevhc,kelompokvhc');
				$kelvhc=$optkdvhc[$kode_vhc];
				
				$sjnskrj="select * from ".$dbname.".vhc_kegiatan where tipe ='traksi' and (kelompokvhc='".$kelvhc."' or kelompokvhc='GLOBAL') and (jenisvhc='".$kode_jns."' or jenisvhc='GLOBAL') order by noakun asc";
				$optJnsKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$res=$owlPDO->query($sjnskrj) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($rjnskrj=$res->fetch()){
					$d=substr($rjnskrj['kodekegiatan'],0,5);
					if($d!=$n){			
						$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
						$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
					}
					$optJnsKerja.="<option value=".$rjnskrj['kodekegiatan'].">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
					$n=$d;
					if($d!=$n){
						$optJnsKerja.="</optgroup>";
					}
				}
				
			
			
				$sKm="select distinct kmhmakhir from ".$dbname.".vhc_kmhmakhir_vw where kodevhc='".$kode_vhc."'";
				$res=$owlPDO->query($sKm) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$rKm=$res->fetch();                                     
				echo $rKm['kmhmakhir']."####".$optJnsKerja;                                
			}
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}else{
			echo"Warning :\nTransaction exist / Nomor transaksi sudah ada !";
			exit();
		}
		break;

	case 'update_head':
		if(($tgl_kerja=='')||($jumlahBbm=='')){
			echo"warning:Please Complete The Form";exit();
		}

		// Cek apakah kodevhc sudah ada di tanggal > tanggal input
		$qCek = selectQuery($dbname,'vhc_runht','max(tanggal) as tgl',
												"kodevhc = '".$kode_vhc."' and tanggal > '".$tgl_kerja."'");
		$resCek = fetchData($qCek);
		$editOnly = false;
		if(!empty($resCek[0]['tgl'])) $editOnly = true;

		@$jumlah=$kmhmAkhir-$kmhmAwal;
		$sql="update ".$dbname.".vhc_runht set jenisvhc='".$kode_jns."',kodevhc='".$kode_vhc."',tanggal='".$tgl_kerja."',jenisbbm='".$jnsBbm."',jlhbbm='".$jumlahBbm."' 
				  where notransaksi='".$notransaksi_head."'";
		try{$owlPDO->exec($sql); 
			
			$optkdvhc=makeOption($dbname,'vhc_5master','kodevhc,kelompokvhc');
			$kelvhc=$optkdvhc[$kode_vhc];
			
			$sjnskrj="select * from ".$dbname.".vhc_kegiatan where
					tipe ='traksi' and (kelompokvhc='".$kelvhc."' or kelompokvhc='GLOBAL') 
					and (jenisvhc='".$kode_jns."' or jenisvhc='GLOBAL') order by noakun asc";
					// exit("Error:$sjnskrj");
			$optJnsKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$res=$owlPDO->query($sjnskrj) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($rjnskrj=$res->fetch()){
				$d=substr($rjnskrj['kodekegiatan'],0,5);
				if($d!=$n){			
					$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
					$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
				}
				$optJnsKerja.="<option value=".$rjnskrj['kodekegiatan'].">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
				$n=$d;
				if($d!=$n){
					$optJnsKerja.="</optgroup>";
				}
				
			}
			
			
			$sKm="select distinct kmhmakhir from ".$dbname.".vhc_kmhmakhir_vw where kodevhc='".$kode_vhc."'";
			$res=$owlPDO->query($sKm) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rKm=$res->fetch();
			if($editOnly) {
				$nol=0;
				echo $nol."####".$optJnsKerja;
			} else {
				echo $rKm['kmhmakhir']."####".$optJnsKerja;
			}
		}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
		break;

	case'load_data_header':
	$limit=20;
	$page=0;
	if(isset($_POST['page']))
	{
	$page=$_POST['page'];
	if($page<0)
	$page=0;
	}
	$offset=$page*$limit;
	$maxdisplay=($page*$limit);


	$ql2="select count(*) as jmlhrow from ".$dbname.".vhc_runht where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' order by tanggal desc,posting asc";// echo $ql2;
	$res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$res->fetch())
	{
		$jlhbrs= $jsl->jmlhrow;
	}
	
	$sql="select * from ".$dbname.".vhc_runht where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' order by tanggal desc,posting asc limit ".$offset.",".$limit."";
	$no=0;
	$no=$maxdisplay;
	$bar=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$bar->setFetchMode(PDO::FETCH_ASSOC);
	$data =  array();
	$data['tbody'] = array();
	$data['tfoot'] = array();
	while($res=$bar->fetch()){
		$sSpk="select tanggal from ".$dbname.".log_spkht where notransaksi='".$res['notransaksi']."'";
		$res1=$owlPDO->query($sSpk) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		$rSpk=$res1->fetch();
		$thn=substr($rSpk['tanggal'],0,4);

		$sbrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['jenisbbm']."'";
		$res1=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		$rbrg=$res1->fetch();
		$rbrg['namabarang'];
		$no+=1;
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center>".$res['notransaksi']."</td>";
		$tab.="<td align=center>".$res['jenisvhc']."</td>";
		$tab.="<td align=center>".$res['kodevhc']."</td>";
		$tab.="<td align=center>".tanggalnormal($res['tanggal'])."</td>";
		$tab.="<td align=center>".$rbrg['namabarang']."</td>";
		$tab.=" <td align=center>".$res['jlhbbm']."</td>";
		
		if($res['posting']==1){
			$img = "<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">&nbsp;".$_SESSION['lang']['posting'];
		}else{
		   $img = "<img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
			onclick=\"fillField('". $res['notransaksi']."','".$thn."');\">
			<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delHead('". $res['notransaksi']."');\" >	
			<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">
			";
		}
		$body['img'] = $img;
		$data['tbody'][] = $body;
	}

		$data['tfoot']['page']		= $page;
		$data['tfoot']['limit']		= $limit;
		$data['tfoot']['jlhbrs']	= $jlhbrs;
		$data['tfoot']['pref']		= $_SESSION['lang']['pref'];
		$data['tfoot']['lanjut']	= $_SESSION['lang']['lanjut'];
	
		// create Json - author: Atwal					
		//echo json_encode($data);
		echo $tab;
	break;
	case'cariTransaksi':
	$limit=10;
	$page=0;
	if(isset($_POST['page'])){
		$page=$_POST['page'];
		if($page<0)
		$page=0;
	}
	$offset=$page*$limit;

	$where = "";
	if($_POST['txtTgl']!=''){
		$txtTgl=tanggalsystem($_POST['txtTgl']);
		$txt_tgl_a=substr($txtTgl,0,4);
		$txt_tgl_b=substr($txtTgl,4,2);
		$txt_tgl_c=substr($txtTgl,6,2);
		$txtTgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
		$where.=" and tanggal='".$txtTgl."'";
	}
	if($txtCari!=''){
		$where.=" and notransaksi like '%".trim($txtCari)."%'";
	}
	if($statData!=''){
		$where.=" and posting='".$statData."'";
	}
	if($kodevhc_cari!=''){
		$where.=" and kodevhc like '%".trim($kodevhc_cari)."%'";
	}
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".vhc_runht where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ".$where." order by tanggal desc";
	$sql="select * from ".$dbname.".vhc_runht where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ".$where." order by tanggal desc limit ".$offset.",".$limit."";
	$res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$res->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}
	$res7=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$res7->setFetchMode(PDO::FETCH_ASSOC);
	while($res=$res7->fetch()){
		$sSpk="select tanggal from ".$dbname.".log_spkht where notransaksi='".$res['notransaksi']."'";
		$res1=$owlPDO->query($sSpk) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);                
		$rSpk=$res1->fetch();
		$thn=substr($rSpk['tanggal'],0,4);

		$sbrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['jenisbbm']."'";
		$res1=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);                
		$rbrg=$res1->fetch();                
		$rbrg['namabarang'];
		$no+=1;
		
		$optnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$res['kodevhc']."'");
		$optdet=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',"kodevhc='".$res['kodevhc']."'");
		$optjns=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc',"jenisvhc='".$res['jenisvhc']."'");
		
		echo"
		<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td align=center>".$res['notransaksi']."</td>
		<td align=center>".$res['jenisvhc']." - ".$optjns[$res['jenisvhc']]."</td>
		<td align=center>".$res['kodevhc']."</td>
		<td align=center>".$optnopol[$res['kodevhc']]."</td>
		<td align=left>".$optdet[$res['kodevhc']]."</td>
		<td align=center>".tanggalnormal($res['tanggal'])."</td>
		<td align=center>".$rbrg['namabarang']."</td>
		<td align=center>".$res['jlhbbm']."</td>
		";
		if($res['posting']==1){
			echo"<td width=25px></td><td width=25px></td><td align=center width=25px><img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\"></td>";
		}else{
				echo"
				<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
				onclick=\"fillField('". $res['notransaksi']."','".$thn."');\"></td>
				<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delHead('". $res['notransaksi']."');\" >	</td>
				<td align=center width=25px><img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">
				</td>";
		}

	}
	echo" </tr><tr class=rowheader><td colspan=12 align=center>
			".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
			<br />
			<button class=mybutton onclick=cariData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
			<button class=mybutton onclick=cariData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
			</tr>";
	break;
	case'cari_lokasi':
	$txtcari=$_POST['txtinputan'];
	$str="select a.kodeorganisasi,a.namaorganisasi from ".$dbname.".organisasi a where  a.tipe in ('KEBUN','AFDELING','BLOK') and a.namaorganisasi like '%".$txtcari."%'";
	$res2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res2);	
	if($numrows<1)
	{
			echo"Error: ".$_SESSION['lang']['tidakditemukan'];			
	}
	else
	{
			echo"
			<fieldset>
			<legend>".$_SESSION['lang']['result']."</legend>
			<div style=\"width:450px; height:300px; overflow:auto;\">
			<table class=sortable cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
			<td>No</td>
			<td>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td>".$_SESSION['lang']['namaorganisasi']."</td>
			</tr>
			</thead>
			<tbody>";
			$no=0;	 
			while($bar=$res2->fetch())
			{
			$no+=1;
			echo"<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"throwThisRow('".$bar->kodeorganisasi."','".$bar->namaorganisasi."');\">
			<td>".$no."</td>
			<td>".$bar->kodeorganisasi."</td>
			<td>".$bar->namaorganisasi."</td>
			</tr>";			   	
			}
			echo    "
			</tbody>
			<tfoot></tfoot>
			</table></div></fieldset>";
	}
	break;
	case 'deleteHead':
	$sql="select * from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
			$resTrans = fetchData($sql);

	$sdel="delete from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'"; //echo "warning:".$sdel;
	try{$owlPDO->exec($sdel); 
			$sdel2="delete from ".$dbname.".vhc_rundt where notransaksi='".$notransaksi_head."'";
			try{$owlPDO->exec($sdel2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			$sdel3="delete from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."'";
			try{$owlPDO->exec($sdel3); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			updateKmHm($resTrans[0]['kodevhc']);
	}
	catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	case'getData':
	$sql="select * from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
	$res1=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_ASSOC);     
	$res=$res1->fetch();
	
	$sSpk="select tanggal from ".$dbname.".log_spkht where notransaksi='".$res['notransaksi']."'";
	$res1=$owlPDO->query($sSpk) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_ASSOC);             
	$resPk=$res1->fetch();
	$thn=substr($resPk['tanggal'],0,4);
	$optKntrk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sSpk2="select notransaksi from ".$dbname.".log_spkht where kodeorg='".$lokasi."' and posting<>'0' and tanggal like '%".$thn."%'";
	$res1=$owlPDO->query($sSpk2) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_ASSOC);
	while($rSpk=$res1->fetch()){
			$optKntrk.="<option value=".$rSpk['notransaksi']." ".($rSpk['notransaksi']==$notransaksi_head?'selected':'').">".$rSpk['notransaksi']."</option>";
	}

	// Agar Nama sesuai di datakaryawan
	$nmKaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

	$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$skary="select distinct(a.karyawanid),a.nama,b.nik from ".$dbname.".vhc_5operator a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.aktif='1' and b.lokasitugas='".$res['kodeorg']."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$res['tanggal']."') order by a.nama";//echo $skary;
	$res1=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_ASSOC);
	while($rkary=$res1->fetch()){
			$optKary.="<option value=".$rkary['karyawanid'].">".$nmKaryawan[$rkary['karyawanid']]."&nbsp;[".$rkary['nik']."]</option>";
	}
	  //cari traksi
	   $sTraksi="select distinct kodetraksi from ".$dbname.".vhc_5master where kodevhc='".$res['kodevhc']."'";
		$res1=$owlPDO->query($sTraksi) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);           
	   $rTraksi=$res1->fetch();
			echo $res['notransaksi']."####".$res['jenisvhc']."####".tanggalnormal($res['tanggal'])."####".$res['jenisbbm']."####".$res['jlhbbm']."####".$res['kodeorg']."####".$optKary."####".$rTraksi['kodetraksi'];

	break;
	case 'getKmAkhir':
			// Get Data
			$qKm = selectQuery($dbname,'vhc_kmhm_track','*',"kodevhc='".$_POST['kodevhc']."'");
			$resKm = fetchData($qKm);
			//exit("warning:masuk sini lho".$resKm[0]['kmhmakhir']."___".$qKm);
			if(empty($resKm))
					echo 0;
			else
					echo $resKm[0]['kmhmakhir'];
			break;

default:
	break;
}

function updateKmHm($kodevhc) {
        global $dbname;
        global $owlPDO;
        // Get KM/HM Akhir
        $qKm = selectQuery($dbname,'vhc_kmhmakhir_vw','*',"kodevhc='".$kodevhc."'",'kmhmakhir desc');
        $resKm = fetchData($qKm);
        $kmhmAkhir = (empty($resKm))? 0: $resKm[0]['kmhmakhir'];

        $dataIns = array($kodevhc,$kmhmAkhir);
        $qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);
        try{$owlPDO->exec($qIns); }
        catch (PDOException $e) {
                $dataUpd = array('kmhmakhir'=>$kmhmAkhir);
                $qUpd = updateQuery($dbname,'vhc_kmhm_track',$dataUpd,"kodevhc='".$kodevhc."'");
                try{$owlPDO->exec($qUpd); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }         
        }
}
?>