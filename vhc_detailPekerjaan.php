<?php
ini_set('display_errors',0);
error_reporting(0);
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$notransaksi_head= isset($_POST['notrans'])?$_POST['notrans']: '';
$notransaksi     = isset($_POST['noOptrans'])?$_POST['noOptrans']: '';
$proses          =  isset($_POST['proses'])? $_POST['proses']: '';
$lokasi          =  $_SESSION['empl']['lokasitugas'];
$jnsPekerjaan    = isset($_POST['jnsPekerjaan'])?$_POST['jnsPekerjaan']: '';
$lokKerja        =  isset($_POST['locationKerja'])?$_POST['locationKerja']: '';
$muatan          =  isset($_POST['muatan'])?$_POST['muatan']: '';
$brtMuatan       = isset($_POST['brtmuatan'])?$_POST['brtmuatan']: '';
$jmlhRit         =  isset($_POST['jmlhRit'])?$_POST['jmlhRit']: '';
$ket             =  isset($_POST['ket'])? $_POST['ket']: '';
$posisi          =  isset($_POST['posisi'])? $_POST['posisi']: '';
$kdKry           =  isset($_POST['kdKry'])? $_POST['kdKry']: '';
$oldjnsPekerjaan = isset($_POST['oldjnsPekerjaan'])?$_POST['oldjnsPekerjaan']: '';
$uphOprt         =  isset($_POST['uphOprt'])?$_POST['uphOprt']: '';
$prmiOprt        = isset($_POST['prmiOprt'])?$_POST['prmiOprt']: '';
$pnltyOprt       = isset($_POST['pnltyOprt'])?$_POST['pnltyOprt']: '';
$ketOprt         = isset($_POST['ketOprt'])?$_POST['ketOprt']: '';
$tglTrans        =  isset($_POST['tglTrans'])?tanggalsystem($_POST['tglTrans']): '';
$thnKntrk        =  isset($_POST['thnKntrk'])?$_POST['thnKntrk']: '';
$noKntrak        = isset($_POST['noKntrak'])?$_POST['noKntrak']: '';
$biaya           =  isset($_POST['biaya'])?$_POST['biaya']: '';
$Blok            =  isset($_POST['Blok'])?$_POST['Blok']: '';
$dept            =  isset($_POST['dept'])?$_POST['dept']: '';
$segment         =  isset($_POST['kodesegment'])?$_POST['kodesegment']: '';
$oldSegment      = isset($_POST['oldSegment'])?$_POST['oldSegment']: '';
$oldBlok         =  isset($_POST['oldBlok'])?$_POST['oldBlok']: '';
$old_lokKerja    = isset($_POST['old_lokKerja'])?$_POST['old_lokKerja']: '';
$beratmuatan     = checkPostGet('beratmuatan','');
$oldbrt_muatan   = checkPostGet('oldbrt_muatan','');

$jnsPekerjaan = trim($jnsPekerjaan);



$jenisvhc= checkPostGet('jenisvhc','');
$kodetraksi= checkPostGet('kodetraksi','');
$kar= checkPostGet('kar','');

$kmhmAwal=	isset($_POST['kmhmAwal'])?$_POST['kmhmAwal']: '';
$kmhmAkhir=	isset($_POST['kmhmAkhir'])?$_POST['kmhmAkhir']: '';
$satuan=	isset($_POST['satuan'])?	$_POST['satuan']: '';

$nmpek = makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
if($notransaksi_head!=''){
	$sKode="select kodeorg from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
	$res=$owlPDO->query($sKode) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$rKode=$res->fetch();
}
$optKdVhc=makeOption($dbname, 'vhc_runht', 'notransaksi,kodevhc',"notransaksi = '".$notransaksi_head."'");

$optkelvhc=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,kelompokvhc');
$optpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');

switch($proses){
	case'getPremi':
		$notransaksi= checkPostGet('notransaksi','');
		$kodetraksi = checkPostGet('kodetraksi','');
		$jenisvhc   = checkPostGet('jenisvhc','');
		$posisi     = checkPostGet('posisi','');
		$karyawanid = checkPostGet('kar','');
		$unit       = substr($kodetraksi,0,4);
		
		$hasil=0;
		if($karyawanid!=''){
			$str="select jenispekerjaan,beratmuatan from ".$dbname.".vhc_rundt where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			foreach($res as $val){
				$strx="select premilebihbasis from ".$dbname.".vhc_5premikegiatan where kodekegiatan='".$val['jenispekerjaan']."' and unit='".$unit."' and vhc='".$jenisvhc."' and posisi='".$posisi."'";
				$resx=fetchdata($strx);
				if(count($resx)>0){
					$hasil+=($val['beratmuatan']*$resx[0]['premilebihbasis']);
				}
			}
		}
		
		echo $hasil;
		#echo hitungpremi($_POST);
	break;
	case'getDetailPremi':
		#echo hitungpremi($_POST);
	break;
        case 'load_data_kerjaan':
        //echo "warning:masuk";	

        $sql="select a.*,b.namasegment from ".$dbname.".vhc_rundt a left join ".$dbname.".keu_5segment b on a.kodesegment=b.kodesegment where substring(notransaksi,1,4)='".$rKode['kodeorg']."' and notransaksi='".$notransaksi_head."' order by kmhmawal asc";// echo $sql;
        $no=0;
        $res1=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);        
        while($res=$res1->fetch()){
        	$nmdept=makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$res['kodedept']."'");
			$no+=1;
			echo"
			<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center>".$res['notransaksi']."</td>
			<td>".$res['jenispekerjaan']."-".$nmpek[$res['jenispekerjaan']]."</td>
			<td>".getNamaOrg($res['alokasibiaya'])."</td>
			<td>".$res['kodedept'].'-'.$nmdept[$res['kodedept']]."</td>
			<td style='display:none'>".$res['namasegment']."</td>
			<td align=right>".number_format($res['jumlahrit'],2)."</td>
			<td align=right>".number_format($res['beratmuatan'],2)."</td>
			<td align=right>".number_format($res['kmhmawal'],2)."</td>
			<td align=right>".number_format($res['kmhmakhir'],2)."</td>
			<td align=right>".number_format($res['kmhmakhir']-$res['kmhmawal'],2)."</td>
			<td align=center>".$res['satuan']."</td>
			<td align=right style='display:none'>".number_format($res['biaya'],2)."</td>
			<td>".$res['keterangan']."</td>
			<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
			onclick=\"fillFieldKrj('".$res['jenispekerjaan']."','".$res['alokasibiaya']."','". $res['beratmuatan']."','". $res['jumlahrit']."','". $res['keterangan']."','". $res['biaya']."','". $res['kmhmawal']."','". $res['kmhmakhir']."','". $res['satuan']."','".$res['kodesegment']."','".$res['namasegment']."','".$res['kodedept']."');\"></td>
			<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delDataKrj('". $res['notransaksi']."','". $res['jenispekerjaan']."','".$res['alokasibiaya']."','".$res['kodesegment']."','".$res['beratmuatan']."');\" >	
			</td>
			</tr>
			";
        }
        break;

        case'insert_pekerjaan':
			#cek tipe kode unit
			$dTip=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$_POST['locationKerja']."'");
			// Get Header
			$qHead = selectQuery($dbname,'vhc_runht','tanggal,kodevhc',"notransaksi = '".$notransaksi_head."'");
			$resHead = fetchData($qHead);
			if(empty($resHead)) exit("Warning: Data Header tidak ada");
			$resHead = $resHead[0];

			// Cek apakah kodevhc sudah ada di tanggal > tanggal input
			/*
			$qCek = selectQuery($dbname,'vhc_runht','max(tanggal) as tgl',"kodevhc = '".$resHead['kodevhc']."' and tanggal > '".$resHead['tanggal']."'");
			$resCek = fetchData($qCek);
			if(!empty($resCek[0]['tgl'])) {
					exit("Warning: Kendaraan sudah ada transaksi di tanggal yang lebih besar.".
							 "\nTanggal transaksi terakhir ".tanggalnormal($resCek[0]['tgl']));
			}
			*/
			//Cek Jenis kegiatan
			$sAlokasi = "select count(b.kelompok) as countkelompok from ".$dbname.".vhc_kegiatan a 
									left join ".$dbname.".setup_kegiatan b 
									on a.noakun = b.noakun and b.kelompok in ('BBT', 'PNN', 'TB', 'TBM', 'TM') 
									where a.kodekegiatan='".$jnsPekerjaan."'";
			$res=$owlPDO->query($sAlokasi) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rAlokasi = $res->fetch();

			if($notransaksi_head=='')
			{
					echo"warning: please confirm heade first";
					exit();
			}
			if($jnsPekerjaan=='')
			{
					echo"warning: Activity required";
					exit();

			}
			if($lokKerja=='')
			{
					echo"warning: Cost allocation (block) required";
					exit();

			}
			if($rAlokasi['countkelompok'] != 0){
					if($Blok == ''){
							echo "warning : Blok harus dipilih.";
							exit();
					}
			}
			
			if($kmhmAwal>=$kmhmAkhir)
			{
							echo"warning:".$_SESSION['lang']['vhc_kmhm_awal']." must lower then ".$_SESSION['lang']['vhc_kmhm_akhir']."";
							exit();
			}
			$jumlah=$kmhmAkhir-$kmhmAwal;
			$sCekHt="select notransaksi from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
			$res=$owlPDO->query($sCekHt) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);                        
			$rCekHt=owlBaris($res);
			if($rCekHt<1)
			{
							echo"warning: Header required";
							exit();
			}

			if($Blok!=''){
					if($dTip[$_POST['locationKerja']]=='KEBUN'){
						if(strlen($Blok)<10){
								exit("Error: Block required");
						}
						   
					}
					$lokKerja=$Blok; 
			}
			
			if(substr($jnsPekerjaan,0,1)=='7' and $dept==''){
				exit("Warning:Nomor akun 7xxx wajib mengisi kolom Dept.");
			}
			if(substr($jnsPekerjaan,0,1)=='8' and $dept==''){
				exit("Warning:Nomor akun 8xxx wajib mengisi kolom Dept.");
			}
			
			if($biaya=='')
					$biaya=0;
			$sins="insert into ".$dbname.".vhc_rundt (`notransaksi`,`jenispekerjaan`,`alokasibiaya`,`beratmuatan`,`jumlahrit`,`keterangan`,`biaya`,`kmhmawal`,
							`kmhmakhir`,`jumlah`,`satuan`,`kodesegment`,`kodedept`) 
							values ('".$notransaksi_head."','".$jnsPekerjaan."','".$lokKerja."','".$brtMuatan."','".$jmlhRit."','".$ket."'
							,'".$biaya."','".$kmhmAwal."','".$kmhmAkhir."','".$jumlah."','".$satuan."','".$segment."','".$dept."')";
			try{$owlPDO->exec($sins); 
					$sKm="select distinct kmhmakhir from ".$dbname.".vhc_kmhmakhir_vw where kodevhc='".$optKdVhc[$notransaksi_head]."'";
					$res=$owlPDO->query($sKm) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$rKm=$res->fetch();
					updateKmHm($optKdVhc[$notransaksi_head]);
					echo intval($rKm['kmhmakhir']);
			}
			catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
            break;

        case'update_kerja':
		$sAlokasi = "select count(b.kelompok) as countkelompok from ".$dbname.".vhc_kegiatan a 
								left join ".$dbname.".setup_kegiatan b 
								on a.noakun = b.noakun and b.kelompok in ('BBT', 'PNN', 'TB', 'TBM', 'TM') 
								where a.kodekegiatan='".$jnsPekerjaan."'";
		$res=$owlPDO->query($sAlokasi) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC); 
		$rAlokasi = $res->fetch();

		if(($brtMuatan=='')||($jmlhRit==''))
		{
						echo"warning:Please Complete The Form";
						exit();
		}
		// exit("error : ".$rAlokasi['countkelompok']);
		if($rAlokasi['countkelompok'] != 0){
				if($Blok == ''){
						echo "warning : Blok harus dipilih.";
						exit();
				}
		}
		if(substr($jnsPekerjaan,0,1)=='7' and $dept==''){
			exit("Warning:Nomor akun 7xxx wajib mengisi kolom Dept.");
		}
		if(substr($jnsPekerjaan,0,1)=='8' and $dept==''){
			exit("Warning:Nomor akun 8xxx wajib mengisi kolom Dept.");
		}
		
		if($Blok!='')
		{
				$lokKerja=$Blok;
				if(!empty($oldBlok) and $lokKerja!=$oldBlok)
				{
								$where.=" and alokasibiaya='".$oldBlok."'";
				}
				else
				{
						if($old_lokKerja!=$lokKerja)
						{
								$where.=" and alokasibiaya='".$old_lokKerja."'";
						} else {
								$where.=" and alokasibiaya='".$lokKerja."'";
						}
				}
		}
		else
		{
						if($old_lokKerja!=$lokKerja)
						{
										$where.=" and alokasibiaya='".$old_lokKerja."'";
						}
						else
						{
										$where.=" and alokasibiaya='".$lokKerja."'";
						}
		}
		if($oldjnsPekerjaan!='')
		{
						if($jnsPekerjaan!=$oldjnsPekerjaan)
						{
										$where.="  and jenispekerjaan='".$oldjnsPekerjaan."'";
						}
						else
						{
										$where.="  and jenispekerjaan='".$jnsPekerjaan."'";
						}
		}
		
		if($oldbrt_muatan!=''){
			$where.="  and beratmuatan='".$oldbrt_muatan."'";		
		}
		
		
		if(!empty($segment)) {
				$where.="  and kodesegment='".$oldSegment."'";
		}
		if($kmhmAwal>=$kmhmAkhir)
		{
						echo"warning:".$_SESSION['lang']['vhc_kmhm_awal']." must lower then ".$_SESSION['lang']['vhc_kmhm_akhir']."";
						exit();
		}
		
		if($jnsPekerjaan==''){
			echo"warning: Activity required";
			exit();
		}
			if($lokKerja=='')
			{
					echo"warning: Cost allocation (block) required";
					exit();

			}
		
		// Get Prev Data
		$qData = selectQuery($dbname,'vhc_rundt','*', "notransaksi='".$notransaksi_head."' ".$where);
		$resData = fetchData($qData);

		// All Detail in Transaksi
		$qKm = selectQuery($dbname,'vhc_rundt','max(kmhmakhir) as kmakhir',"notransaksi='".$notransaksi_head."'");
		$resKm = fetchData($qKm);
		if($resKm[0]['kmakhir']>$resData[0]['kmhmakhir'] and $kmhmAkhir!=$resData[0]['kmhmakhir']) {
				exit("Warning: Transaksi yang bukan terakhir tidak boleh diubah KM / HM Akhir");
		}

		// Get Header
		$qHead = selectQuery($dbname,'vhc_runht','tanggal,kodevhc',"notransaksi = '".$notransaksi_head."'");
		$resHead = fetchData($qHead);
		if(empty($resHead)) exit("Warning: Data Header tidak ada");
		$resHead = $resHead[0];

		// Cek apakah kodevhc sudah ada di tanggal > tanggal input
		$qCek = selectQuery($dbname,'vhc_runht','max(tanggal) as tgl', "kodevhc = '".$resHead['kodevhc']."' and tanggal > '".$resHead['tanggal']."'");
		$resCek = fetchData($qCek);
		if(!empty($resCek[0]['tgl']) and $kmhmAkhir!=$resData[0]['kmhmakhir']) {
				/*exit("Warning: Kendaraan sudah ada transaksi di tanggal yang lebih besar.".
						 "\nPerubahan KM / HM Akhir tidak bisa dilakukan");*/
		}
		//print_r($resKm);exit('error');
		$jumlah=$kmhmAkhir-$kmhmAwal;
		$sup="update ".$dbname.".vhc_rundt set jenispekerjaan='".$jnsPekerjaan."',alokasibiaya='".$lokKerja."',beratmuatan='".$brtMuatan."'
		,jumlahrit='".$jmlhRit."',keterangan='".$ket."',biaya='".$biaya."',kmhmawal='".$kmhmAwal."',kmhmakhir='".$kmhmAkhir."',jumlah='".$jumlah."'
		,satuan='".$satuan."',kodesegment='".$segment."',kodedept='".$dept."' where notransaksi='".$notransaksi_head."' ".$where."";
		try{$owlPDO->exec($sup); 
				$sKm="select distinct kmhmakhir from ".$dbname.".vhc_kmhmakhir_vw where kodevhc='".$optKdVhc[$notransaksi_head]."'";
				$res=$owlPDO->query($sKm) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$rKm=$res->fetch();
				updateKmHm($optKdVhc[$notransaksi_head]);
				echo intval($rKm['kmhmakhir']);
		}
		catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		break;

        case'deleteKrj':
        $delKrj="delete from ".$dbname.".vhc_rundt
                        where notransaksi='".$notransaksi_head."' and
                        jenispekerjaan='".$jnsPekerjaan."' and
                        alokasibiaya='".$Blok."' and
                        kodesegment='".$segment."' and 
						beratmuatan='".$beratmuatan."'";
        try{$owlPDO->exec($delKrj); 
            updateKmHm($optKdVhc[$notransaksi_head]);
        }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }    
        
        break;
    
    
    case'insert_operator':
        $sCekHt="select notransaksi from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
        $res=$owlPDO->query($sCekHt) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);            
        $rCekHt=owlBaris($res);
        if($rCekHt<1){
                echo"warning: Header required";
                exit();
        }

        $sPeriode="select periode from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($rKode['kodeorg'],0,4)."' and periode='".substr($tglTrans,0,4)."-".substr($tglTrans,4,2)."'";# tanggalmulai<".$tglTrans." and tanggalsampai>=".$tglTrans;
        $res=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $rPeriode=$res->fetch();
        if($rPeriode['periode']=='')
        {
			echo"warning: Transaction date out of range\nPeriode gaji belum ada.";
			exit();
        }
		
		
		
		$jumlahpejabatbkm=0;
		$str = "select count(*) as jumlah,notransaksi,nobkm from ".$dbname.".kebun_aktifitas where (nikmandor='".$kdKry."' or nikmandor1='".$kdKry."'  or nikasisten='".$kdKry."'  or keranimuat='".$kdKry."') and  tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlahpejabatbkm=$bar['jumlah'];
			$notransaksibkm=$bar['jumlah'];
			$nobkm=$bar['jumlah'];
		if($jumlah>0){
			exit("Warning:Karyawan ditanggal ".tanggalnormal($tglTrans)."  sudah terdaftar absensi supervisi di BKM di nomor ".$notransaksibkm." / ".$nobkm." ");
		}
		
		
		#validasi maksimal HK BHL
		cekmaxnilaihk($kdKry,tanggalsystemn(tanggalnormal($tglTrans)),'1','0','new',$exit='1');
		
		#query pengecekan apakah FP aktif / tidak
		$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".substr($rKode['kodeorg'],0,4)."' and tanggal<='".tanggalsystemn(tanggalnormal($tglTrans))."'"; #exit("error".$str);
		$res = fetchData($str);
		$statusfp    = $res[0]['status'];//1 aktif,0 tidak
		$tipevalidasi= $res[0]['tipevalidasi'];
		$detailexp   = explode(",",$res[0]['detailvalidasi']);
		foreach($detailexp as $vald){
			$detval[$vald]=$vald;
		}
		$arrUpload = array();
		if($statusfp==1){
			$arrUpload[]['nik'] = $kdKry;
			validasifp($tipevalidasi,$detval,'TRK',$arrUpload,tanggalsystemn(tanggalnormal($tglTrans)),'1');
		}
		
		/*
		$str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$kdKry."' and tanggalabsen='".$tglTrans."' limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($kdKry != $bar['karyawanid']){
			$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$kdKry."'");
			$optNIK = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$kdKry."'");
			
			echo "Warning : Absen fingerprint untuk karyawan dg NIK : \n".$optNIK[$kdKry]." = ".$optNamaKaryawan[$kdKry]."\nbelum diupload.";
			exit;
		}
		*/
		
		$sKd="select lokasitugas,subbagian from ".$dbname.".datakaryawan where karyawanid='".$kdKry."'";
		$res=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);                
		$rKd=$res->fetch();
		$lokasiTugas=$rKd['lokasitugas'];
		if(!is_null($rKd['subbagian'])||$rKd['subbagian']!=0||$rKd['subbagian']!=''){
		   $lokasiTugas=$rKd['subbagian'];
		}

		#cek absensi umum
		$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$kdKry."' and tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumabs=$bar['umr'];
			
		if($jumabs>0 && $uphOprt>0){
			exit("Warning:Karyawan ditanggal ".tanggalnormal($tglTrans)."  sudah terdaftar di absensi umum, silahkan dihapus dahulu absensi umumnya");
		}

		#cek di BKM
		$str = "select notransaksi, sum(jhk) as jhk, sum(umr) as umr from ".$dbname.".kebun_kehadiran_vw where karyawanid='".$kdKry."' and tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jmlhkbkm=$bar['jhk'];
			$jmlumrbkm=$bar['umr'];
			$notransbkm=$bar['notransaksi'];
			
		if(($jmlhkbkm>0||$jmlumrbkm>0) && $uphOprt>0){
			exit("Warning: Karyawan sudah terdaftar pada Keg BKM dengan no transaksi ".$notransbkm."");
		}
		
		#cek nilai umr
		$str = "select * from ".$dbname.".sdm_5gajipokok where karyawanid='".$kdKry."' and tahun='".substr($tglTrans,0,4)."' and idkomponen='1'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$gajipokok=$bar['jumlah']/25;
		
		if($uphOprt>$gajipokok){
			exit("Warning : Nilah upah lebih besar dari nilai UMR / Hari, maksimal nilai upah = Rp. ".$gajipokok."");
		}
		
		#cek di panen
		$str = "select count(*) as kegpanen, notransaksi from ".$dbname.".kebun_prestasi_vw where karyawanid='".$kdKry."' and tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jmlhkkegpnn=$bar['kegpanen'];
			$notranskegpnn=$bar['notransaksi'];
			
		if($jmlhkkegpnn>0 && $uphOprt>0){
			exit("Warning: Karyawan sudah terdaftar pada Keg Panen dengan no transaksi ".$notranskegpnn."");
		}
		
		$str = "select * from ".$dbname.".kebun_aktifitas where tanggal='" . $tglTrans . "' and (nikmandor='".$kdKry."' or nikmandor1='".$kdKry."' or keranimuat='".$kdKry."')";
		$res = fetchdata($str);
		if(count($res)>0 and $uphOprt>0){
			exit("Warning: Karyawan sudah terdaftar pada header BKM dengan nomor : ".$res[0]['notransaksi']."");
		}
		
		#cek jika hari itu sudah ada upah dihari itu
		$str = "select count(*) as jumkar, notransaksi from ".$dbname.".vhc_runhk_vw where idkaryawan='".$kdKry."' and tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans=$bar['jumkar'];
			$notr=$bar['notransaksi'];
			
		if($jumtrans>0 and $uphOprt>0){
			exit("Warning : Upah karyawan sudah terdaftar ditransaksi lain dengan nomor ".$notr.", anda hanya diperbolehkan menginput premi dengan umr 0");
		}			
		
		
		$day = date('D', strtotime($tglTrans));
		if($day=='Sun')$libur=true; else $libur=false;
		// kamus hari libur
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tglTrans."' and (kebun='GLOBAL' or kebun='".substr($notransaksi_head,0,4)."')";
		
		$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
		$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
		while($roworg=$queorg->fetch()){
			if($roworg['keterangan']=='libur')$libur=true;
			if($roworg['keterangan']=='masuk')$libur=false;
		}
		
		if($libur==true and $uphOprt>0){
			exit("Warning:Jika Hari libur/minggu maka nilai upah harus 0, upah ditambahkan ke premi");
		}
		#======================= cek premi apakah lebih besar dari perhitungan ==================
		$param=$_POST;
		#$totalpremi = hitungpremi($param);
		$totalpremi = 10000000;
		
		$str="select sum(premi) as premi from ".$dbname.".vhc_runhk where notransaksi='".$_POST['notrans']."' and posisi='".$posisi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);        
		while($bar=$res->fetch()){
			$cekpremi=$bar['premi'];
		}
			$cekpremi=$cekpremi+$_POST['prmiOprt'];		
		
		if($cekpremi > $totalpremi and $libur==false){
			exit('Error : Total nilai premi yg di input : '.number_format($cekpremi).' lebih besar dari total premi yg seharusnya di dapat : '.number_format($totalpremi));
		}
		
		#======================= cek premi apakah lebih besar dari perhitungan ==================
		
		#insert vhc_runhk
		$str="insert into ".$dbname.".vhc_runhk (`notransaksi`,`idkaryawan`,`posisi`,`tanggal`,`statuskaryawan`,`upah`,`premi`,`penalty`,`keterangan`)
				values ('".$notransaksi_head."','".$kdKry."','".$posisi."','".$tglTrans."','','".$uphOprt."','".$prmiOprt."','".$pnltyOprt."','".$ketOprt."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Gagal Tersimpan:" . $e->getMessage() . "\n".$str;
		}   

		#hapus dahulu jika ada diabsensi umum	
		$str="delete from ".$dbname.".sdm_absensidt where karyawanid='".$kdKry."' and tanggal='".$tglTrans."' ";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			 $errorDB .= " Gagal Menghapus absensi umum :" . $e->getMessage() . "\n".$str;
		}
		
		
		
		$strAbs = "update ".$dbname.".upload_absensi set flag = 1 where karyawanid='".$kdKry."' and tanggalabsen='".$tglTrans."'";
		try{
			$owlPDO->exec($strAbs); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
		}
    break;
        
        case 'update_operator':
		$str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$kdKry."' and tanggalabsen='".tanggalsystem($tglTrans)."' limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($kdKry != $bar['karyawanid']){
			$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$kdKry."'");
			echo "Warning : Absen untuk nik : \n".$kdKry." = ".$optNamaKaryawan[$kdKry]."\nbelum diupload.";
			exit;
		}
		
        if($posisi==1)
        {
				$str="select idkaryawan from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='1'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
				$idkaryawanlama = $bar['idkaryawan'];
			
                $sCek="select count(posisi) as jmlh from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='1'";
                $res=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=$res->fetch();
        }
        elseif($posisi==0)
        {
				$str="select idkaryawan from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='0'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
				$idkaryawanlama = $bar['idkaryawan'];
			
                $sCekSop="select count(posisi) as jmlh from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='0'";
                $res=$owlPDO->query($sCekSop) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $rCekSop=$res->fetch();
        }
        if($rCek['jmlh']>4)
        {
                echo"warning: Can`t complete transaction, Operator maximum limit exeed";
                exit();
        }
        if($rCekSop['jmlh']>1)
        {
                echo"warning: Can`t complete transaction, Operator maximum limit exeed";
                exit();
        }
        $skry="select a.`alokasi`,b.tipe from ".$dbname.".datakaryawan a inner join ".$dbname.".sdm_5tipekaryawan b on 
        a.tipekaryawan=b.id where karyawanid='".$kdKry."'"; 
        $res=$owlPDO->query($skry) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $rkry=$res->fetch();


        $sup_op="update ".$dbname.".vhc_runhk set posisi='".$posisi."',tanggal='".$tglTrans."',statuskaryawan='".$rkry['tipe']."',upah='".$uphOprt."',premi='".$prmiOprt."',penalty='".$pnltyOprt."' where notransaksi='".$notransaksi_head."' and idkaryawan='".$kdKry."'";
        try{$owlPDO->exec($sup_op); echo"";}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		
		$strAbs = "update ".$dbname.".upload_absensi set flag = 0 where karyawanid='".$idkaryawanlama."' and tanggalabsen='".$tglTrans."'";
		try{
			$owlPDO->exec($strAbs); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
		}		
		
		$strAbs = "update ".$dbname.".upload_absensi set flag = 1 where karyawanid='".$kdKry."' and tanggalabsen='".$tglTrans."'";
		try{
			$owlPDO->exec($strAbs); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
		}
        break;
        
        
        case'getUmr':
		
            if($_POST['tahun']!='')
                    $tahun=$_POST['tahun'];
            else {
                    $tahun=date('Y');
            }
			
		
		if($kdKry!=''){
			$sUmr="select sum(jumlah) as jumlah from ".$dbname.".sdm_5gajipokok 
				where karyawanid='".$kdKry."' and tahun=".$tahun."  and idkomponen ='1'";
			$res=$owlPDO->query($sUmr) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rUmr=$res->fetch();
			$umr=$rUmr['jumlah']/25;
			
			if($rUmr['jumlah']==''){
				exit("Error : Gaji Pokok untuk tahun ".$tahun." belum ada !");
			}
			
			$str=" select * from ".$dbname.".datakaryawan where 1=1 and karyawanid='".$kdKry."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$tipekaryawan=$bar['tipekaryawan'];
			}
			
			if($tipekaryawan=='1' or $tipekaryawan=='0'){
				$umr=0;
			}
			
		}
		
			#hari minggu
			$day = date('D', strtotime($tglTrans));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tglTrans."' and 
				(kebun='GLOBAL' or kebun='".substr($kodetraksi,0,4)."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
		
			if($libur==true){
				@$umr=0;
			}else{
				@$umr=$umr;
			}
		
		
		
        echo $umr."####".$tipekaryawan;
        break;

        case'load_data_opt':
        $arrPos=array("Operator","Helper","Sopir");
        $sql="select * from ".$dbname.".vhc_runhk where substring(notransaksi,1,4)='".$rKode['kodeorg']."' and notransaksi='".$notransaksi_head."' order by notransaksi desc"; //echo "warning:".$sql;
        $res3=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $res3->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$res3->fetch())
        {
                $skry="select `namakaryawan` from ".$dbname.".datakaryawan where karyawanid='".$res['idkaryawan']."'";
                $res4=$owlPDO->query($skry) or die(print " Gagal: ".PDOException::getMessage());
                $res4->setFetchMode(PDO::FETCH_ASSOC);
                $rkry=$res4->fetch();
                $no+=1;
                echo"
                <tr class=rowcontent>
                <td align=center>".$no."</td>
                <td align=center>".$res['notransaksi']."</td>
                <td>".$rkry['namakaryawan']."</td>
                <td>".$arrPos[$res['posisi']]."</td>
                <td align=right>".number_format($res['upah'],2)."</td>
                <td align=right>".number_format($res['premi'],2)."</td>
                <td align=right>".number_format($res['penalty'],2)."</td>
                <td>".$res['keterangan']."</td>
                <td align=center>
                <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('". $res['notransaksi']."','". $res['idkaryawan']."');\" >	
                </td>
                </tr>
                ";
        }
        break;
        
        case'getKntrk':
        $optKntrk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sSpk="select notransaksi from ".$dbname.".log_spkht where kodeorg='".$lokasi."' and posting<>'0' and tanggal like '%".$thnKntrk."%'";
        $res3=$owlPDO->query($sSpk) or die(print " Gagal: ".PDOException::getMessage());
        $res3->setFetchMode(PDO::FETCH_ASSOC);
        $rSpk=owlBaris($res3);
        if($rSpk>0)
        {
                while($rSpk=$res3->fetch())
                {
                        $optKntrk.="<option value=".$rSpk['notransaksi']." ".($rSpk['notransaksi']==$noKntrak?'selected':'').">".$rSpk['notransaksi']."</option>";
                }

        }
        else
        {
                $optKntrk="<option value=''></option>";
                //echo $optKntrk;
        }
        echo $optKntrk;
        break;

        case'delete_opt':
            $sTanggal="select distinct tanggal from ".$dbname.".vhc_runht where notransaksi='".$notransaksi."'";
            $res=$owlPDO->query($sTanggal) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);            
            $rTanggal=$res->fetch();
            $delAbsen="delete from ".$dbname.".sdm_absensidt where karyawanid='".$kdKry."' and tanggal='".$rTanggal['tanggal']."'";
            try{$owlPDO->exec($delAbsen); 
                $sdel="delete from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi."' and idkaryawan='".$kdKry."'";
                try{$owlPDO->exec($sdel);echo""; }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }          
            }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			
			$strAbs = "update ".$dbname.".upload_absensi set flag = 0 where karyawanid='".$kdKry."' and tanggalabsen='".$rTanggal['tanggal']."'";
			try{
				$owlPDO->exec($strAbs); 
			}
			catch (PDOException $e) 
			{
				 $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
			}
        break;
        case'getSatuan':
                //enum('','BBT','KNT','MIL','PNN','TB','TBM','TM','WSH')
                $arrTipe=array('BBT'=>'KEBUN','KNT'=>'','MIL'=>'PABRIK','PNN'=>'KEBUN','TB'=>'KEBUN','TBM'=>'KEBUN','TM'=>'KEBUN');
                $strSat="select satuan,kelompok from ".$dbname.".`vhc_kegiatan` where  kodekegiatan='".$jnsPekerjaan."' and tipe='traksi'";
                $res=$owlPDO->query($strSat) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $resSat=$res->fetch();
                $tipeOrg=" and tipe='".$arrTipe[$resSat['kelompok']]."'";
                if($arrTipe[$resSat['kelompok']]==''){
                    $tipeOrg="";
                }
                $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                
				$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
				left join log_5supkelompok b on a.supplierid=b.supplierid
				where a.status=1  order by a.namasupplier asc";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()){
					$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
				}
				
				
				// S201901311
				
				// exit("Error:".$resSat['kelompok']);
				
				if($resSat['kelompok']=='EXT') {
					$sOrg="select * from ".$dbname.".kebun_5namakud where status='1' "; 	
					$rOrg=fetchData($sOrg);			
					foreach($rOrg as $row=>$lsDt){
						$optOrg.="<option value='".$lsDt['afdeling']."'>".$nmsupplier[$lsDt['kodesupplier']]."</option>";					                    
					}					
				}else{
					$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)='4' "; 
					$rOrg=fetchData($sOrg);
					foreach($rOrg as $row=>$lsDt){
						if(substr($lsDt['kodeorganisasi'],0,4)==$_SESSION['empl']['lokasitugas']){
							$optOrg.="<option value='".$lsDt['kodeorganisasi']."' selected>".$lsDt['kodeorganisasi']." - ".$lsDt['namaorganisasi']."</option>";
						}else{						
							$optOrg.="<option value='".$lsDt['kodeorganisasi']."'>".$lsDt['kodeorganisasi']." - ".$lsDt['namaorganisasi']."</option>";
						}	                    
					}					
				}
				// exit("Error:A");
                echo $resSat['satuan']."####".$optOrg;
        break;
        case'getdept':
            $kepalapek= substr($jnsPekerjaan,0,1);
			$lokasi   = checkPostGet('kodeorg', '');
            $unittipe = getNamaOrg(substr($lokasi,0,4),'tipe');
			$kodedept = checkPostGet('kodedept', '');
			
            // if ($kepalapek==7 || $kepalapek==8) {
				$where=" and kode in (select kode from ".$dbname.".sdm_5departemen_detail where unittipe='".$unittipe."')";
				$sdept="select * from ".$dbname.".sdm_5departemen where aktif ='1' ".$where." order by nama asc"; #exit("error".$sdept);
				$optdept="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$res=$owlPDO->query($sdept) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($rdept=$res->fetch()){
					if($kodedept==$rdept['kode']){
						$optdept.="<option value=".$rdept['kode']." selected>".$rdept['kode']." - ".$rdept['nama']."</option>";
					}else{						
						$optdept.="<option value=".$rdept['kode'].">".$rdept['kode']." - ".$rdept['nama']."</option>";
					}

				}
            // }
            // else
            // {
            	// $optdept="<option value=''>&nbsp;</option>";
            // }
			
			echo $optdept;

        break;
        case'getBlok':
			 $sAlokasi = "select kelompok from ".$dbname.".vhc_kegiatan where kodekegiatan='".$jnsPekerjaan."' and tipe='traksi'";
			$res=$owlPDO->query($sAlokasi) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rAlokasi = $res->fetch();        
			//cek tipe
			if($rAlokasi['kelompok']=='PNN'){
				$statusblok = " and statusblok = 'TM'";
			}else if($rAlokasi['kelompok']=='TB'){
				$statusblok = " and statusblok IN ('LC','TB','TBM')";
			}else{
				$statusblok = " and statusblok = '".$rAlokasi['kelompok']."'";
			}
			
			$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			if($rAlokasi['kelompok']=='MIL'){
				$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
				where induk like '%".$lokKerja."%' and tipe='STATION' order by tipe desc, kodeorganisasi asc";
			}elseif($rAlokasi['kelompok']=='EXT'){
				$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
				where induk like '%".$lokKerja."%' and (tipe='BLOK' OR tipe='BIBITAN')
				and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,6)='".$lokKerja."' and luasareaproduktif>0 and statusblok='TM') 
				order by tipe desc, kodeorganisasi asc";
			}else{
				$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
				where induk like '%".$lokKerja."%' and (tipe='BLOK' OR tipe='BIBITAN')
				and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$lokKerja."' and luasareaproduktif>0 ".$statusblok.") 
				order by tipe desc, kodeorganisasi asc";
			}
		
			$res=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($rBlok=$res->fetch()){
				$d=substr($rBlok['kodeorganisasi'],0,6);
				if($d!=$n){			
					$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
					$optBlok.="<optgroup label='".$nmorg[$d]."'>";
				}
				
				if($Blok!=""){
					if($rBlok['kodeorganisasi']==$Blok){						
						$optBlok.="<option value=".$rBlok['kodeorganisasi']." selected>".$rBlok['namaorganisasi']."</option>";
					}else{						
						$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".$rBlok['namaorganisasi']."</option>";
					}
				}else{
					$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".$rBlok['namaorganisasi']."</option>";
				}
				$n=$d;
				if($d!=$n){
					$optBlok.="</optgroup>";
				}
			}
            #khusus Project:
			$str="select kode,nama from  ".$dbname.".project where kodeorg='".$lokKerja."' and posting=0";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
			$optBlok.="<optgroup label='PROJECT'>";
			while($bar=$res->fetch()){
			  $optBlok.="<option value=".$bar->kode.">".$bar->nama." [".$bar->kode."]</option>";
			}
			$optBlok.="</optgroup>";

		// exit("error".$optBlok);
        echo $optBlok;
        break;
        default:
        break;
}

function updateKmHm($kodevhc) {
        global $dbname;
        global $owlPDO;
        // Get KM/HM Akhir
        $qKm = selectQuery($dbname,'vhc_kmhmakhir_vw','*',"kodevhc='".$kodevhc."'");
        $resKm = fetchData($qKm);
        $kmhmAkhir = (empty($resKm))? 0: $resKm[0]['kmhmakhir'];

        $dataIns = array($kodevhc,$kmhmAkhir);
        $qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);
        try{$owlPDO->exec($qIns); }catch (PDOException $e) {
                 $dataUpd = array('kmhmakhir'=>$kmhmAkhir);
                $qUpd = updateQuery($dbname,'vhc_kmhm_track',$dataUpd, "kodevhc='".$kodevhc."'");
                try{$owlPDO->exec($qUpd); }catch (PDOException $e) {print " Update H/KM Gagal  !: " . $e->getMessage() . "\n"; die(); }    
        }
}

function hitungpremi($param){
		global $dbname;
        global $owlPDO;
		$totalpremi='';
		
		// echo"<pre>";
		// print_r($param);
		// echo"</pre>"; exit('error');
		
		$tglTrans= tanggalsystem(checkPostGet('tglTrans',''));
		$kar1= checkPostGet('kdKry','');
		$kar2= checkPostGet('kar','');
		$jenis= checkPostGet('jenis','');
		if($kar1!=''){
			$kar=$kar1;
		}else{
			$kar=$kar2;
		}
		$proses= checkPostGet('proses','');
		$pt= checkPostGet('pt','');
		$posisi= checkPostGet('posisi','');
		$jenisvhc= checkPostGet('jenisvhc','');
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
		$notransaksi= checkPostGet('notransaksi','');
		if($notransaksi==''){
			$notransaksi= checkPostGet('notrans','');
		}
		
		
		#hari minggu
		@$tempUnit=explode('/',$notransaksi);
		$kodetraksi=$tempUnit[0];
		$day = date('D', strtotime($tglTrans));
		if($day=='Sun')$libur=true; else $libur=false;
		// kamus hari libur
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tglTrans."' and 
			(kebun='GLOBAL' or kebun='".substr($kodetraksi,0,4)."')";
		$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
		$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
		while($roworg=$queorg->fetch()){
			if($roworg['keterangan']=='libur')$libur=true;
			if($roworg['keterangan']=='masuk')$libur=false;
		}
		$gapok=0;
		#gaji pokok dan datakaryawan
		$str=" select a.karyawanid,a.namakaryawan,a.tipekaryawan,b.jumlah from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5gajipokok b
			on a.karyawanid=b.karyawanid where a.karyawanid='".$kar."' and b.tahun='".substr($tglTrans,0,4)."' and b.idkomponen=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);        
		$bar=$res->fetch();
			if($libur==true){
				$gapok=$bar['jumlah']/25;
			}
		
		if($gapok==0){
			//exit("Warning : Gaji Pokok Belum Ada !");
		}

		@$pt=$optpt[substr($kodetraksi,0,4)];
		@$vhckel=$optkelvhc[$jenisvhc];
		
		@$tempUnit=explode('/',$notransaksi);
		@$pt=$optpt[$tempUnit['0']];
		
		# Perhitungan premi alat berat
		$str="select sum(a.jumlah) as jumlah,a.notransaksi,b.tanggal,c.basis,c.premibasis,c.premilebihbasis,
				a.satuan,b.kodevhc,b.jenisvhc from ".$dbname.".vhc_rundt a  
				left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
				left join ".$dbname.".vhc_5premialatberat c on b.jenisvhc=c.jenisvhc
				where a.notransaksi='".$notransaksi."' and c.kodept='".$pt."' and c.posisi='".$posisi."' and b.jenisvhc='".$jenisvhc."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);        
		while($bar=$res->fetch()){
			$btshmpremi=$bar['basis'];
			$premilb1=$bar['premibasis'];
			$premilb2=$bar['premilebihbasis'];
			$jlhhm=$bar['jumlah'];
		}
		$premiab='';
		if($jlhhm > 0 and $jlhhm <= $btshmpremi){
			$premiab+=$jlhhm*$premilb1;
		} else if ($jlhhm > $btshmpremi){
			$premiab+=($btshmpremi*$premilb1) + (($jlhhm-$btshmpremi)*$premilb2);
		} else{
			$premiab=0;
		}
		
		$premikg='';
		$str="select  a.*,b.tanggal,c.basis,c.premibasis,c.premilebihbasis,b.kodevhc,b.jenisvhc 
				from ".$dbname.".vhc_rundt a
				left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
				left join ".$dbname.".vhc_5premikegiatan c on a.jenispekerjaan=c.kodekegiatan
				where a.notransaksi='".$notransaksi."' and c.kodept='".$pt."' and c.posisi='".$posisi."'
				and c.vhc='".$jenisvhc."'  order by kmhmawal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);        
		while($bar=$res->fetch()){
			@$keg[$bar['jenispekerjaan']]=$bar['jenispekerjaan'];
			@$pres[$bar['jenispekerjaan']]+=$bar['beratmuatan'];
			@$basis[$bar['jenispekerjaan']]=$bar['basis'];
			@$rplb[$bar['jenispekerjaan']]=$bar['premilebihbasis'];
			@$hmkm[$bar['jenispekerjaan']]=$bar['jumlah'];
		}

		
		foreach(@$keg as $jkeg){
			if( @$pres[$jkeg] > 0 and @$pres[$jkeg] >= @$basis[$jkeg]){
				@$premikg+=$pres[$jkeg]*$rplb[$jkeg];
			}
		}


		if($vhckel=='AB'){
			@$totalpremi=$premiab+$premikg;
			@$totalpremidtl=$premiab+$premikg;
		} else{
			@$totalpremi=$premikg;
			@$totalpremidtl=$premikg;
		}
		
		$str="select sum(premi) as premi from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi."' and posisi='".$posisi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);        
		while($bar=$res->fetch()){
			$cekpremi=$bar['premi'];
		}
		
		if($totalpremi-$cekpremi<=0){
			$totalpremi=0;
		} else {
			$totalpremi=$totalpremi-$cekpremi;
		}
		$totalpremi=$totalpremi+$gapok;
		
		#ini buat nampilkan di detail premi oprt
		if($jenis=='detail'){
			$tab="";$no=0;
			foreach(@$keg as $jkeg){
				$no++;
				$nmkeg=makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$jkeg."'");
				$nmsat=makeOption($dbname,'vhc_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$jkeg."'");
				$tab.="<tr class=rowcontent  id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>".$jkeg." - " . $nmkeg[$jkeg] . "</td>";
				$tab.="<td align=left>" . $nmsat[$jkeg] . "</td>";
				$tab.="<td align=right>" . @number_format($pres[$jkeg],2) . "</td>";
				$tab.="<td align=right>" . @number_format($hmkm[$jkeg],2) . "</td>";
				$tab.="<td align=right>" . @number_format($rplb[$jkeg],2) . "</td>";
				$tab.="<td align=right style=background-color:grey></td>";
				$tab.="<td align=right>" . @number_format($pres[$jkeg]*$rplb[$jkeg]) . "</td>";
				$tab.="<td align=right style=background-color:grey></td>";
				$tab.="<td align=right>" . @number_format($pres[$jkeg]*$rplb[$jkeg]) . "</td>";
				$tab.="</tr>";
				@$ttlhmkm+=$hmkm[$jkeg];
				@$ttlrppres+=$pres[$jkeg]*$rplb[$jkeg];
			}
			
			if($libur==true){
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=right></td>";
				$tab.="<td align=left>Gaji Pokok (Hari Libur)</td>";
				$tab.="<td align=left colspan=7 style=background-color:grey></td>";
				$tab.="<td align=right>".@number_format($gapok,2)."</td>";				
				$tab.="</tr>";
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=3>TOTAL</td>";
			$tab.="<td align=right style=background-color:grey></td>";
			$tab.="<td align=right>".@number_format($ttlhmkm,2)."</td>";
			$tab.="<td align=right style=background-color:grey></td>";
			$tab.="<td align=right>" . @number_format($premilb2,2) . "</td>";
			$tab.="<td align=right>" . @number_format($ttlrppres) . "</td>";
			$tab.="<td align=right>".@number_format($ttlhmkm*$premilb2,2)."</td>";
			$tab.="<td align=right>".@number_format($totalpremidtl+$gapok)."</td>";
			
			echo $tab;
		}else{			
			return round($totalpremi);
		}
		
}