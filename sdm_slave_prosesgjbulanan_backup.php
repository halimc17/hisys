<?php
error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;
$namakar=array();
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}

$method=checkPostGet('method','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));
$kodeorg=checkPostGet('unit','');

if($method=='estgaji'){
	$param['kodeorg']    = $kodeorg;
	$proses              = 'post';
	$param['periodegaji']= substr($tgl1,0,7);
	
	if(substr($tgl1,0,7) != substr($tgl2,0,7)){
		exit("Warning : Tanggal pertama dan tanggal kedua harus dalam bulan yang sama");
	}
}


if($method=='estgaji'){
  $str = "select karyawanid,nik,namakaryawan,nourut from ".$dbname.".datakaryawan_hist where approval_status='9'  and lokasitugas = '".$param['kodeorg']."' and periodegaji ='".$param['periodegaji']."' and tipekaryawan='".$param['tipekar']."' "; 
  $res = fetchdata($str);
  if(count($res)>0){
    $datatmpl="";
    $nodasa=0;
    foreach($res as $brs=>$val){
      $sAkhir="select * from ".$dbname.".approval where notransaksi='".$val['nourut']."'  and status='0'  order by level desc limit 1";
      $rAkhir=fetchData($sAkhir);
      $optnm=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$rAkhir[0]['karyawanid']."'");
      $nmkary=$optnm[$rAkhir[0]['karyawanid']];
      $nodasa+=1;
      $datatmpl.=$nodasa.". NIK :".$val['nik']."-Nama Karyawan : ".$val['namakaryawan']."-Penyetuju Terakhir : ".$nmkary."\n";
    } 
    echo $datatmpl;
    exit("Warning : Masih terdapat perubahan/buat baru datakaryawan pada periode ini yang belum di approved");
  }

  $str = "select karyawanid,nik,namakaryawan,nourut from ".$dbname.".datakaryawan_hist where approval_status='7'  and lokasitugas = '".$param['kodeorg']."' and periodegaji ='".$param['periodegaji']."' and tipekaryawan='".$param['tipekar']."' "; 
  $res = fetchdata($str);
  if(count($res)>0)
  { 
    exit("Warning : Masih terdapat datakaryawan pada periode ini yang belum di posting");
  }

} 
else{
  $str = "select karyawanid,nik,namakaryawan,nourut from ".$dbname.".datakaryawan_hist where approval_status='9'  and lokasitugas = '".$param['kodeorg']."' and periodegaji ='".$param['periodegaji']."' and sistemgaji='Bulanan' "; 
  $res = fetchdata($str);
  if(count($res)>0)
  { 
    $datatmpl="";
    $nodasa=0;
    foreach($res as $brs=>$val){
        $nodasa+=1;
        $sAkhir="select * from ".$dbname.".approval where notransaksi='".$val['nourut']."' and status='0'  order by level desc limit 1";
        $rAkhir=fetchData($sAkhir);
        $optnm=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$rAkhir[0]['karyawanid']."'");
        $nmkary=$optnm[$rAkhir[0]['karyawanid']];
        //$datatmpl.=$nodasa.". NIK :".$val['nik']."-Nama Karyawan : ".$val['namakaryawan']."-Penyetuju Terakhir : ".$nmkary."\n";
		$datatmpl.=$nodasa.". ".$val['nik']." - ".$val['namakaryawan']." - ".$nmkary."<br>";
    } 
    // echo $datatmpl;
    // exit("Warning : Masih terdapat perubahan/buat baru datakaryawan pada periode ini yang belum di approved");
	
	exit("Warning : Masih terdapat perubahan/buat baru datakaryawan pada periode ini yang belum di approved<br>No . NIK - Nama - Penyetuju Terakhir :<br>".$datatmpl."");
  }

  $str = "select karyawanid,nik,namakaryawan,nourut from ".$dbname.".datakaryawan_hist where approval_status='7'  and lokasitugas = '".$param['kodeorg']."' and periodegaji ='".$param['periodegaji']."' and tipekaryawan='".$param['tipekar']."' "; 
  $res = fetchdata($str);
  if(count($res)>0)
  { 
    exit("Warning : Masih terdapat datakaryawan pada periode ini yang belum di posting");
  }

}

#cek tutup atau belum periode gaji
$sCekPeriode="select distinct * from ".$dbname.".sdm_5periodegaji where periode='".$param['periodegaji']."' 
              and kodeorg='".$param['kodeorg']."' and sudahproses=1 and jenisgaji='B'";
$qCekPeriode=$owlPDO->query($sCekPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qCekPeriode->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($qCekPeriode);
if($numrows>0)
    $aktif2=false;
       else
     $aktif2=true;

if (!$aktif2 and $method != 'estgaji'){
    exit("Payroll period has been closed");
}elseif(!$aktif2){
	echo "close"; exit();
}



#ambil datakaryawan
$query = "select * from " . $dbname . ".datakaryawan a where tipekaryawan in(1,2,6) and lokasitugas='" . $param['kodeorg'] . "' and (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi in ('0','1')  group by a.karyawanid";
$res = fetchdata($query);
foreach($res as $val){
	$datakaryawan[$val['karyawanid']]=$val['karyawanid'];
}

if($aktif2==true and $method != 'estgaji'){
	$str = "delete from ".$dbname.".datakaryawan_hist where tipekaryawan in(1,2,6) and alokasi in ('0','1') and approval_status='8' and version_type='B' and lokasitugas='".$param['kodeorg']."' and periodegaji='".$param['periodegaji']."'"; 
	
	try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
}

#ambil data dari hist
$datahist=array();
$str = "select karyawanid from ".$dbname.".datakaryawan_hist where tipekaryawan in(1,2,6) and alokasi in ('0','1') and approval_status='8' and version_type='B' and lokasitugas='".$param['kodeorg']."' and periodegaji='".$param['periodegaji']."'"; 
$res = fetchdata($str); 
$jumlahhist=count($res);
foreach($res as $val){
	$datahist[$val['karyawanid']]=$val['karyawanid'];
}


foreach($datakaryawan as $nik){
	if($datahist[$nik]=='' or count($datahist)==0){
		$dt = "select * from " . $dbname . ".datakaryawan where karyawanid='".$nik."'";
		$rd = fetchdata($dt);
		foreach($rd as $bar){
			$data = array(
				'karyawanid'         =>$bar['karyawanid'],
				'nik'                =>$bar['nik'],
				'namakaryawan'       =>$bar['namakaryawan'],
				'namakaryawan2'      =>$bar['namakaryawan2'],
				'tempatlahir'        =>$bar['tempatlahir'],
				'tanggallahir'       =>$bar['tanggallahir'],
				'warganegara'        =>$bar['warganegara'],
				'jeniskelamin'       =>$bar['jeniskelamin'],
				'statusperkawinan'   =>$bar['statusperkawinan'],
				'tanggalmenikah'     =>$bar['tanggalmenikah'],
				'agama'              =>$bar['agama'],
				'golongandarah'      =>$bar['golongandarah'],
				'levelpendidikan'    =>$bar['levelpendidikan'],
				'alamataktif'        =>$bar['alamataktif'],
				'provinsi'           =>$bar['provinsi'],
				'kota'               =>$bar['kota'],
				'kodepos'            =>$bar['kodepos'],
				'noteleponrumah'     =>$bar['noteleponrumah'],
				'nohp'               =>$bar['nohp'],
				'nohp2'              =>$bar['nohp2'],
				'norekeningbank'     =>$bar['norekeningbank'],
				'namabank'           =>$bar['namabank'],
				'pemilikrekening'    =>$bar['pemilikrekening'],
				'sistemgaji'         =>$bar['sistemgaji'],
				'nopaspor'           =>$bar['nopaspor'],
				'no_keluarga'        =>$bar['no_keluarga'],
				'noktp'              =>$bar['noktp'],
				'notelepondarurat'   =>$bar['notelepondarurat'],
				'tanggalmasuk'       =>$bar['tanggalmasuk'],
				'tanggalpengangkatan'=>$bar['tanggalpengangkatan'],
				'tanggalkeluar'      =>$bar['tanggalkeluar'],
				'tipekaryawan'       =>$bar['tipekaryawan'],
				'jumlahanak'         =>$bar['jumlahanak'],
				'jumlahtanggungan'   =>$bar['jumlahtanggungan'],
				'statuspajak'        =>$bar['statuspajak'],
				'npwp'               =>$bar['npwp'],
				'bpjs'               =>$bar['bpjs'],
				'lokasipenerimaan'   =>$bar['lokasipenerimaan'],
				'kodeorganisasi'     =>$bar['kodeorganisasi'],
				'bagian'             =>$bar['bagian'],
				'kodejabatan'        =>$bar['kodejabatan'],
				'kodegolongan'       =>$bar['kodegolongan'],
				'lokasitugas'        =>$bar['lokasitugas'],
				'photo'              =>$bar['photo'],
				'email'              =>$bar['email'],
				'emailkantor'        =>$bar['emailkantor'],
				'alokasi'            =>$bar['alokasi'],
				'subbagian'          =>$bar['subbagian'],
				'subdept'            =>$bar['subdept'],
				'jms'                =>$bar['jms'],
				'kodecatu'           =>$bar['kodecatu'],
				'statpremi'          =>$bar['statpremi'],
				'statusakad'         =>$bar['statusakad'],
				'suku'               =>$bar['suku'],
				'sim'                =>$bar['sim'],
				'statuskaryawan'     =>$bar['statuskaryawan'],
				'updateby'           =>$bar['updateby'],
				'pensiun'            =>$bar['pensiun'],
				'insstatuspajak'     =>$bar['insstatuspajak'],
				'supbpjs'            =>$bar['supbpjs'],
				'kppnpwp'            =>$bar['kppnpwp'],
				'nosk'               =>$bar['nosk'],
				'tanggalsk'          =>$bar['tanggalsk'],
				'noerf'              =>$bar['noerf'],
				'periodeakhirgaji'   =>$bar['periodeakhirgaji'],
				'tmkjamsostek'       =>$bar['tmkjamsostek'],
				'updatetime'         =>date('Y-m-d'),
				'approval_status'    =>'8',
				'periodegaji'        =>$param['periodegaji'],
				'version_type'       =>'B',
				'datachange'         =>'',
				'version'            =>'1'
			);
			$query = insertQuery($dbname,'datakaryawan_hist',$data,array_keys($data));
			try{$owlPDO->exec($query);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	}
}



$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
$tipeOrg=$optTipe[$param['kodeorg']];
$str="select * from ".$dbname.".bgt_regional_assignment where kodeunit LIKE '".$param['kodeorg']."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
  $regional=$bar->regional;
  $unit=$bar->kodeunit;
}

if($param['periodegaji'] < '2018-05'){
	exit("Warning : Data gaji periode ini telah di upload dan telah sesuai !");
}
	

$resNatura=fetchData($sCekPeriode);
$statNatura=$resNatura[0]['natura'];#cek status natura 1/0, jika 1 maka gajipokok+natura
$arrRpNatura=array();
if($statNatura==1){
  $sNatura="select * from ".$dbname.".sdm_catu where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periodegaji']."' and posting=1";
  $rNatura=fetchData($sNatura);
  if(count($rNatura)==0 and $method != 'estgaji'){
    exit('warning: Catu Belum Terposting/Terinput');
  }else{
      foreach ($rNatura as $key => $val) {
        $arrRpNatura[$val['karyawanid']]=$val['jumlahrupiah'];
      }  
  }
}

#periksa apakah sudah tutup buku
$str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$param['periodegaji']."' and 
      kodeorg='".$param['kodeorg']."' and tutupbuku=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows>0)
   $aktif=false;
else
   $aktif=true;
if (!$aktif and $method != 'estgaji') {
    exit("Accounting perid has been closed");
}elseif(!$aktif){
	echo "close";
}



# Get Period Range
$qPeriod=selectQuery($dbname,'sdm_5periodegaji','tanggalmulai,tanggalsampai', "periode='".$param['periodegaji']."' 
          and kodeorg='".$param['kodeorg']."' and jenisgaji='B'");
$resPeriod = fetchData($qPeriod);
$tanggal1 = $resPeriod[0]['tanggalmulai'];
$tanggal2 = $resPeriod[0]['tanggalsampai'];

if ($method == 'estgaji') {
	$tanggal1 = $tgl1;
	$tanggal2 = $tgl2;

}  

# === hitung selisih hari
$t1     = $tanggal1." 00:00:01"; #awal
$t2     = $tanggal2." 23:59:59"; #sampai
$endd   = strtotime($t2);
$startd = strtotime($t1);
$jlhhari= round(abs($endd-$startd)/60/60/24);

$dakarbulanan=0;
$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$param['kodeorg']."' and periodegaji='".$param['periodegaji']."'"; 
$res = fetchdata($str);
if(count($res)>0){ 
  $dakarbulanan=1;
}


if($dakarbulanan==0){

		#2. Get Karyawan bulanan yang penggajian=bulanan dan alokasi in ('0','1')
		$query1 = "select a.tanggallahir, a.nik,a.karyawanid,a.tmkjamsostek,statuspajak,tipekaryawan,namakaryawan,jms,bpjs,pensiun,lokasitugas, tanggalkeluar, 	a.jumlahtanggungan as jmltanggungan from " . $dbname . ".datakaryawan a where tipekaryawan in(1,2,6) and lokasitugas='" . $param['kodeorg'] . "' and (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and ( tanggalmasuk<='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and ( tanggalsk<='" . $tanggal2 . "' or tanggalsk='0000-00-00' or tanggalsk is null) group by a.karyawanid";
		// echo $query1;
		$absRes = fetchData($query1);
		# Error empty karyawan
		if(empty($absRes)) {
		  echo "Error : There is no prsence(kehadiran) on this period";
		  exit();
		}
	
		  $id=Array();
		  $idaaaa='';
		  $umrbulanansebelumnya=array();
		  foreach($absRes as $row => $kar){
			$karydidatakary[$kar['karyawanid']]=$kar['karyawanid'];
			$id[$kar['karyawanid']][]=$kar['karyawanid'];
			$namakar[$kar['karyawanid']]=$kar['namakaryawan'];
			$nikkary[$kar['karyawanid']]=$kar['nik'];
			$lokasitugas[$kar['karyawanid']]=$kar['lokasitugas'];
			$tglkeluar[$kar['karyawanid']]=$kar['tanggalkeluar'];
			$tmkjamsostek[$kar['karyawanid']]=$kar['tmkjamsostek'];
			//$nojms[$kar['karyawanid']]=trim($kar['jms']);
			//$nobpjs[$kar['karyawanid']]=trim($kar['bpjs']);
			if($idaaaa==''){
				$idaaaa="'".$kar['karyawanid']."'";
			}else{
				$idaaaa.=",'".$kar['karyawanid']."'";
			}
			$umrbulanansebelumnya[$kar['karyawanid']]=0;
			#mengambil no Jamsostek
			#bpjstenaga
			$bpjstenaga[$kar['karyawanid']] = trim($kar['jms']); // JKK JKM JHT
			#bpjs pensiun
			
			$diff= (strtotime($tanggal1)-strtotime($kar['tanggallahir']));
			$umur= floor($diff/(60*60*24*365));
			if($umur>57){
				$bpjspensiun[$kar['karyawanid']] = ""; // JP
			}else{				
				$bpjspensiun[$kar['karyawanid']] = trim($kar['pensiun']); // JP
			}
			#bpjskes
			$bpjskes[$kar['karyawanid']] = trim($kar['bpjs']); // KESEHATAN
			$bpjskestanggungan[$kar['karyawanid']] = $kar['jmltanggungan']+1;
		  }  
		

		#1ambil semua komponen dari gajipokok=====================
		   
		## Parameter aplikasi
		#= ambil komponen yang termasuk di gaji pokok
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRGAPOK'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch(); 
		$arrgapok=explode(',',$bar['nilai']);
		foreach($arrgapok as $key){
		  $arrcomgapok[]=$key;
		}

		$periodelalu=date("Y-m", (strtotime("-1 months",strtotime($tanggal1))));
		$tahunlalu=date("Y", (strtotime("-1 years",strtotime($tanggal1))));

		$sUmpsebelumnya="select * from ".$dbname.".sdm_5gajipokok 
					  where tahun='".$tahunlalu."' and idkomponen='1' and karyawanid in (".$idaaaa.")";
		$rUmpsebelumnya=fetchData($sUmpsebelumnya);
		foreach ($rUmpsebelumnya as $key => $val) {
		$umrbulanansebelumnya[$val['karyawanid']]=$val['jumlah'];
		}

		$sUmpsebelumnya="select * from ".$dbname.".sdm_gaji 
					  where periodegaji='".$periodelalu."' and idkomponen='1' and karyawanid in (".$idaaaa.")";
		$rUmpsebelumnya=fetchData($sUmpsebelumnya);
		foreach ($rUmpsebelumnya as $key => $val) {
			if($val['jumlah']>0)
			{
			$umrbulanansebelumnya[$val['karyawanid']]=$val['jumlah'];
			}
		}

		$tjms=array();   
		$tipekaryawan=array();
		$jumlahhariperbulan=substr($tanggal2,8,2);	   
		$str1 = "select a.*,b.namakaryawan from ".$dbname.".sdm_5gajipokok a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				 where a.tahun=" . substr($tanggal1, 0, 4) . " and b.tipekaryawan in (1,2) and b.lokasitugas='" . $param['kodeorg'] . "' 
				 and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and sistemgaji='Bulanan' and 
					 idkomponen in (select id from " . $dbname . ".sdm_ho_component where type='basic' and id not in ('59'))";		   
		$res1 = fetchData($str1);

		foreach($res1 as $idx => $val){
			$karydigp[$val['karyawanid']]=$val['karyawanid'];
			if($id[$val['karyawanid']][0]==$val['karyawanid']){
					#add to ready data================================================
				
				##Perhitungan proporsi gaji pokok, ketika tanggal keluar terisi dan berada di periode perhitungan
				##Gaji Pokok dihitung sesuai dengan jumlah hari masuk
				$jmlhhari=$jumlahval=$jumlahumrbpjs=0;
				if(in_array($val['idkomponen'],$arrcomgapok)){
					if ($tglkeluar[$val['karyawanid']]!='0000-00-00' && substr($tglkeluar[$val['karyawanid']],0,7)==$param['periodegaji']) {

						$jmlhhari=substr($tglkeluar[$val['karyawanid']],8,2);
						if ($tglkeluar[$val['karyawanid']]<$tanggal2) {
							$jumlahval=($jmlhhari/$jumlahhariperbulan)*$val['jumlah'];
						}else{
							$jumlahval=$val['jumlah'];
						}

						##jika tanggal keluar >= dari tanggal 15, maka bpjs dibayarkan
						##jika tanggal keluar < dari tanggal 15, maka bpjs tidak dibayarkan (0)
						if ($val['idkomponen']==1 ) {
							if ($jmlhhari>=15) {
								$jumlahumrbpjs=$val['jumlah'];
							}
						}
						
					}else{
						$jumlahval=$val['jumlah'];

						if ($val['idkomponen']==1 ) {
							$jumlahumrbpjs=$val['jumlah'];
						}
					}
				}else{
					$jumlahval=$val['jumlah'];
				}
				
					$readyData[] = array(
						'kodeorg'=>$param['kodeorg'],
						'periodegaji'=>$param['periodegaji'],
						'karyawanid'=>$val['karyawanid'],
						'idkomponen'=>$val['idkomponen'],
						'jumlah'=>$jumlahval,
						'pengali' => 1,
						'hk' => 0
					);
			}
			 
			if($val['idkomponen']==1){   
				$umrbulanan[$val['karyawanid']] = $jumlahumrbpjs;
				if($statNatura==1){
				  $umrbulanan[$val['karyawanid']] += $arrRpNatura[$val['karyawanid']];
				}
				
			   // $gajiperhari[$val['karyawanid']]=($val['jumlah']/30);
			   $gajiperhari[$val['karyawanid']]=($val['jumlah']/25);
			} 
		}

		#cek pastikan semua kary memiliki gapok
		$blmadagp="";$ngpblmada=0;
		foreach($karydidatakary as $dtkary){
			if($karydigp[$dtkary]=='0' or $karydigp[$dtkary]==''){
				$ngpblmada+=1;
				$blmadagp.=$ngpblmada.". ".$nikkary[$dtkary]." - ".$namakar[$dtkary]."\n";
			}
		}

		if(!empty($blmadagp)){
			exit("Warning : Ada karyawan belum memiliki gajipokok.\n".$blmadagp."");
		}

		#======================================
		#==== buat potongan hk kbt
		#======================================
		$tdkdibayar = array();
		$hktdkbyr=array();
		$hk=array();

		#ambil jumlah hk tidak dibayar untuk KHT dan total tidak dibayar
		$strgjh = "select  count(*) as jlh,b.karyawanid from " . $dbname . ".sdm_hktdkdibayar_vw a left join 
					  " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
					   where b.tipekaryawan in (1,2) and b.lokasitugas='" . $param['kodeorg'] . "' 
					   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
					   and sistemgaji='Bulanan'
					   group by a.karyawanid";
		///exit('warning:'.$strKrg."____".$strgjh);

		$resgjh = fetchData($strgjh);
		foreach ($resgjh as $idx => $val) {
			@$tdkdibayar[$val['karyawanid']] += $gajiperhari[$val['karyawanid']] * $val['jlh']; #jumlah tidak dibayar
			@$hktdkbyr[$val['karyawanid']] +=$val['jlh'];
			@$lstKary[$val['karyawanid']]=$val['karyawanid'];
		}

		@$dtkarhktdkbayar=count($lstKary);

		if(@$dtkarhktdkbayar>0){
		foreach(@$lstKary as $idx=>$val){
		   if($hktdkbyr[$val]==''){
			   @$hktdkbyr[$val]=0;
		   }
		  $readyData[] = array(
			  'kodeorg' => $param['kodeorg'],
			  'periodegaji' => $param['periodegaji'],
			  'karyawanid' => $val,
			  'idkomponen' => 37, //potongan hk
			  'jumlah' => $tdkdibayar[$val],
			  'pengali' => 1,
			  'hk'=>$hktdkbyr[$val]);
			}
		}   


		#3. Get Lembur Data
		$where2 = " a.kodeorg like '".$param['kodeorg']."%' and (tanggal>='".$tanggal1."' and tanggal<='".$tanggal2."')";
		$query2="select a.karyawanid,sum(a.uangkelebihanjam) as lembur from ".$dbname.".sdm_lemburdt a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				 where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
				 and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				 and sistemgaji='Bulanan' and ".$where2." group by a.karyawanid";
		$lbrRes = fetchData($query2); 
		foreach($lbrRes as $idx=>$row) {  
		  if(isset ($id[$row['karyawanid']]))
		  {
			$readyData[] = array(
			'kodeorg'=>$param['kodeorg'],
			'periodegaji'=>$param['periodegaji'],
			'karyawanid'=>$row['karyawanid'],
			'idkomponen'=>33,   
			'jumlah'=>$row['lembur'],
			'pengali' => 1,
			'hk' => 0);
		  }
		  else
		  {
			//abaikan jika tidak terdaftar pada karyawanid  
		  }   
		}

		#4. Get Potongan Data============================================================
		$where3 = " kodeorg like '%".$param['kodeorg']."%' and a.periodegaji='".$param['periodegaji']."'";
		$query3="select a.nik as karyawanid,sum(jumlahpotongan) as potongan,tipepotongan from ".$dbname.".sdm_potongandt a left join 
				".$dbname.".datakaryawan b on a.nik=b.karyawanid where b.tipekaryawan in('2','1','6') and b.lokasitugas='".$param['kodeorg']."' 
				 and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and sistemgaji='Bulanan'                   
				 and ".$where3." group by a.nik,a.tipepotongan";
		$potRes = fetchData($query3);
		foreach($potRes as $idx=>$row) {  
		  if(isset ($id[$row['karyawanid']]))
		  {
			$readyData[] = array(
			'kodeorg'=>$param['kodeorg'],
			'periodegaji'=>$param['periodegaji'],
			'karyawanid'=>$row['karyawanid'],
			'idkomponen'=>$row['tipepotongan'],   
			'jumlah'=>$row['potongan'],
			'pengali' => 1,
			'hk' => 0);
		  }
		  else
		  {
			//abaikan jika tidak terdaftar pada karyawanid  
		  }   
		}
		$notrgi=array();
		$karygi=array();
		#Potongan Pinjaman BBM
		$where92 = " (a.notransaksi like '%".$param['kodeorg']."%' and a.notransaksi like '".str_replace('-', '', $param['periodegaji'])."%')";
		$str="select a.* FROM ".$dbname.".log_permintaanpicdt a
				left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				left join ".$dbname.".log_transaksidt c on a.notransaksi=c.notransaksi and a.kodebarang=c.kodebarang
				left join ".$dbname.".log_transaksiht d on a.notransaksi=d.notransaksi
				where statussaldo ='1' and d.post='1' and b.tipekaryawan in('2','1','6') and b.lokasitugas='".$param['kodeorg']."' 
				and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and sistemgaji='Bulanan' and ".$where92."";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());		
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$notrgi[$bar['notransaksi']]=$bar['notransaksi'];
			$kdbrgi[$bar['kodebarang']]=$bar['kodebarang'];
			
			$karygi[$bar['notransaksi']][$bar['kodebarang']][$bar['karyawanid']]=$bar['karyawanid'];
			$jlhbrg[$bar['notransaksi']][$bar['kodebarang']]+=$bar['realisasi'];
			$jlhperkary[$bar['notransaksi']][$bar['kodebarang']][$bar['karyawanid']]+=$bar['realisasi'];
		}
		if(count($notrgi)>0){
			$strap = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRPBBM'";
			$resap = fetchData($strap);
			
			$strx = "select * from " . $dbname . ".keu_jurnaldt_vw where kodekegiatan in (".$resap[0]['nilai'].") and kodebarang in ('".implode("','",$kdbrgi)."') and noreferensi in ('".implode("','",$notrgi)."') and debet>0";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
			while ($barx = $resx->fetch()) {
				$jlhrp[$bar['noreferensi']][$bar['kodebarang']]+=$bar['jumlah'];
			}
		}
		if(count($karygi)>0){
			$hargabarang=$potperkarya=0;
			foreach($karygi as $notr => $valkdbrg) { 
				foreach($valkdbrg as $kodebrg => $valkary) { 
					foreach($valkary as $kary => $karyid) { 
						$hargabarang = $jlhrp[$notr][$kodebrg]/$jlhbrg[$notr][$kodebrg];
						$potperkarya = $jlhperkary[$notr][$kodebrg][$kary] * $hargabarang;
						
						if(isset ($id[$kary])){
							$readyData[] = array(
							'kodeorg'=>$param['kodeorg'],
							'periodegaji'=>$param['periodegaji'],
							'karyawanid'=>$kary,
							'idkomponen'=>92,   
							'jumlah'=>$potperkarya,
							'pengali' => 1,
							'hk' => 0);
						}
					}
				}	
			}
		}


		$query4 = "select lokasitugas,a.karyawanid,jenis,sum(jumlah) as bulanan from " . $dbname . ".sdm_angsurandt a left join 
					  " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
					   where 1=1 and b.tipekaryawan in(1,2) and b.lokasitugas='" . $param['kodeorg'] . "' 
					   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					   and a.status=1  and bulan='".$param['periodegaji']."'";
		$angRes = fetchData($query4);
		foreach ($angRes as $idx => $row) {
			if ($id[$row['karyawanid']][0] == $row['karyawanid']) {
			#add to ready data================================================
			$readyData[] = array(
				'kodeorg' => $row['lokasitugas'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $row['karyawanid'],
				'idkomponen' => $row['jenis'],
				'jumlah' => $row['bulanan'],
				'pengali' => 1,
				'hk'=>0);	
			}
		}	
			
			
		#6 Premi dan penalty =======================================================================
		#6.0 periksa posting transaksi
		#posting borongan
		$stru0="select distinct(tanggal) from ".$dbname.".kebun_kehadiran_vw a left join 
			   ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.unit like '".$param['kodeorg']."%' and a.jurnal=0 
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'    
			   and sistemgaji='Bulanan' and a.notransaksi like '%BOR%' order by tanggal";
		$resu0=$owlPDO->query($stru0) or die(print " Gagal: ".PDOException::getMessage());
		$resu0->setFetchMode(PDO::FETCH_OBJ);
		$numrows0=owlBaris($resu0);

		#posting perawatan
		$stru1="select distinct(tanggal) from ".$dbname.".kebun_kehadiran_vw a left join 
			   ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.unit like '".$param['kodeorg']."%' and a.jurnal=0 
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'    
			   and sistemgaji='Bulanan' and a.notransaksi not like '%BOR%' order by tanggal";
		$resu1=$owlPDO->query($stru1) or die(print " Gagal: ".PDOException::getMessage());
		$resu1->setFetchMode(PDO::FETCH_OBJ);
		$numrows1=owlBaris($resu1);

		#posting panen
		$stru2="select distinct(tanggal) from ".$dbname.".kebun_prestasi_vw a left join 
			   ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.unit like '".$param['kodeorg']."%' and a.jurnal=0
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
			   and sistemgaji='Bulanan' order by tanggal";
		$resu2=$owlPDO->query($stru2) or die(print " Gagal: ".PDOException::getMessage());
		$resu2->setFetchMode(PDO::FETCH_OBJ);
		$numrows2=owlBaris($resu2);


		#cek ada trans panen tidak ???
		$stru5 = "select distinct(tanggal) from ".$dbname.".kebun_prestasi_vw a left join 
			   ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.unit like '".$param['kodeorg']."%' 
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
			   and sistemgaji='Bulanan' order by tanggal";
		$res5 = fetchdata($stru5);
		if(count($res5)>0){
			#cek apakah sudah melakukan proses premi panen dan sudah diposting
			$stru6 = "select * from " . $dbname . ".kebun_3premipemanen where periode like '".$param['periodegaji']."%' and kodeorg='" . $param['kodeorg'] . "'";
			$res6 = fetchdata($stru6);
			if($method!='estgaji'){
				if(count($res6)==0){			
					exit("Warning : Silahkan lakukan proses premi panen terlebih dahulu.");
				}else{
					foreach($res6 as $bar6){
						if($bar6['posting']=='0'){
							exit("Warning : Ada Premi panen yang belum diposting.");					
						}
					}
				}
			}
		}


		#posting traksi
		$stru3="select distinct(tanggal) from ".$dbname.".vhc_runhk_vw a left join 
			   ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
			   and posting=0 and sistemgaji='Bulanan' order by tanggal";
		$resu3=$owlPDO->query($stru3) or die(print " Gagal: ".PDOException::getMessage());
		$resu3->setFetchMode(PDO::FETCH_OBJ);
		$numrows3=owlBaris($resu3);

		#posting sdm lembur
		$stru4 = "select * from " . $dbname . ".sdm_lemburht 
				   where tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "'     
				   and posting=0 and kodeorg like '".$param['kodeorg']."%'  order by tanggal";

		$resu4 = $owlPDO->query($stru4) or die(print " Gagal: " . PDOException::getMessage());
		$resu4->setFetchMode(PDO::FETCH_OBJ);
		$numrows4 = owlBaris($resu4);

		#posting sdm pesangon
		$stru99 = "select * from " . $dbname . ".sdm_pesangon 
			   where tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "'     
			   and posting=0 and kodeunit like '".$param['kodeorg']."%'  order by tanggal";

		$resu99 = $owlPDO->query($stru99) or die(print " Gagal: " . PDOException::getMessage());
		$resu99->setFetchMode(PDO::FETCH_OBJ);
		$numrows99 = owlBaris($resu99);

		if($method=='estgaji'){
		  $numrows0=$numrows1=$numrows2=$numrows3=$numrows4=$numrows99=0;
		}

		if($param['kodeorg']!='TPRM'){
		  if($numrows0>0 or $numrows1>0 or $numrows2>0 or $numrows3>0)
		  {
			echo"Masih ada data yang belum di posting/There still unconfirmed transaction:";
			echo"<table class=sortable border=0 cellspacing=1>
				<thead><tr class=rowheader>
				<td>".$_SESSION['lang']['jenis']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				</tr></thead><tbody>";
			while($bar=$resu0->fetch())
			{
			  echo"<tr class=rowcontent><td>Borongan Karyawan Sendiri</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
			}
		  while($bar=$resu1->fetch())
			{
			  echo"<tr class=rowcontent><td>Perawatan Kebun</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
			}
			while($bar=$resu2->fetch())
			{
			  echo"<tr class=rowcontent><td>Panen</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
			}
			while($bar=$resu3->fetch())
			{
			  echo"<tr class=rowcontent><td>Traksi Pekerjaan</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
			}
			echo "</tbody><tfoot></tfoot></table>";
			exit();//keluar dari proses
		  }
		}

		if ($numrows4 > 0 or $numrows99 > 0) {
			echo"Masih ada data yang belum di posting/There still unconfirmed transaction:";
			echo"<table class=sortable border=0 cellspacing=1>
					<thead><tr class=rowheader>
					<td>" . $_SESSION['lang']['jenis'] . "</td>
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					</tr></thead><tbody>";
			while ($bar = $resu4->fetch()) {
				echo"<tr class=rowcontent><td>SDM Lembur</td><td>" . $bar->kodeorg . "</td><td>" . tanggalnormal($bar->tanggal) . "</td></tr>";
			}
			while ($bar = $resu99->fetch()) {
				echo"<tr class=rowcontent><td>SDM Pesangon</td><td>" . $bar->kodeunit . "</td><td>" . tanggalnormal($bar->tanggal) . "</td></tr>";
			}
			echo "</tbody><tfoot></tfoot></table>";
			exit(); //keluar dari proses
		}

		#6.3.1 Get Premi Kegiatan borongan 
		$borongan=array();
		$queryi = "select sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi from " . $dbname . ".kebun_kehadiran_vw a left join 
				  ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				   where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				   and a.unit like '" . $param['kodeorg'] . "%'
				   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				   and sistemgaji='Bulanan' and a.notransaksi like '%BOR%' group by a.karyawanid";
		$premResi = fetchData($queryi);
		foreach ($premResi as $idx => $val) {
		if ($val['premi'] > 0)
			$borongan[$val['karyawanid']]+=$val['premi'];
			$potborongan[$val['karyawanid']]+=$val['premi'];
		}     

		#6.3.1 Get Pesangon 
		$query5xx = "select a.* from " . $dbname . ".sdm_pesangon a left join 
				  ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				   where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				   and a.kodeunit like '" . $param['kodeorg'] . "%'
				   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				   and b.sistemgaji='Bulanan'";
		$pesangonxx = fetchData($query5xx);
		foreach ($pesangonxx as $idx => $val) {
			  if($val['jenispesangon']=='Pesangon')
			  {
			  	$pesangon[$val['karyawanid']]+=$val['totalterima'];
			  }
			  elseif($val['jenispesangon']=='Kompensasi')
			  {
			  	$kompensasi[$val['karyawanid']]+=$val['totalterima'];
			  }
			  elseif($val['jenispesangon']=='Uang Pisah')
			  {
			  	$uangpisah[$val['karyawanid']]+=$val['totalterima'];
			  }
			//$premi[$val['karyawanid']]+=$val['premi'];
		} 

		#6.3.1 Get Premi Kegiatan Perawatan 
		$query5 = "select a.tanggal,sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi from " . $dbname . ".kebun_kehadiran_vw a left join 
				  ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				   where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				   and a.unit like '" . $param['kodeorg'] . "%'
				   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				   and sistemgaji='Bulanan' and a.notransaksi not like '%BOR%' group by a.karyawanid";
		$premRes = fetchData($query5);
		foreach ($premRes as $idx => $val) {
		if ($val['premi'] > 0)
			$premi[$val['karyawanid']]+=$val['premi'];
		}     

		#6.3.2 Get Premi Kegiatan Panen
		$query6 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.tpremi) as premi,sum(a.rupiahpenalty) as penalty,
						sum(hkpanenperhari) as hk ,sum(a.upahpenalty) as upahpenalty from " . $dbname . ".kebun_prestasi_vs_hk a left join 
						".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
					  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					  and a.unit like '" . $param['kodeorg'] . "%' 
					  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
					  and sistemgaji='Bulanan' and a.keterangan!='KONTAN' group by a.tanggal, a.karyawanid";
		$premRes1 = fetchData($query6);
		foreach ($premRes1 as $idx => $val) {
			@$premi[$val['karyawanid']]+=$val['premi'];
			@$penalty[$val['karyawanid']]+=$val['penalty'];
		}

		$query66 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.tpremi) as premi,sum(a.rupiahpenalty) as penalty,
						sum(hkpanenperhari) as hk ,sum(a.upahpenalty) as upahpenalty from " . $dbname . ".kebun_prestasi_vs_hk a left join 
						".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
					  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					  and a.unit like '" . $param['kodeorg'] . "%' 
					  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
					  and sistemgaji='Bulanan' and a.keterangan='KONTAN' group by a.tanggal, a.karyawanid"; 
		$premRes16 = fetchData($query66);
		foreach ($premRes16 as $idx => $val) {
			@$premikontanan[$val['karyawanid']]+=$val['premi'];
			@$penalty[$val['karyawanid']]+=$val['penalty'];
		}

		#6.3.3 Get Premi Transport dan gaji pokok BHL
		$query7 = "select a.tanggal,sum(a.upah) as upah,a.idkaryawan as karyawanid,sum(a.premi) as premi,sum(a.penalty) as penalty,sum(hk) as hk
				  from " . $dbname . ".vhc_runhk_vw a left join " . $dbname . ".datakaryawan b on a.idkaryawan=b.karyawanid
				  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				  and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' 
				  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				  and sistemgaji='Bulanan' group by a.idkaryawan";
		$premRes2 = fetchData($query7);
		foreach ($premRes2 as $idx => $val) {
			@$premi[$val['karyawanid']]+=$val['premi'];
			@$penaltytraksi[$val['karyawanid']]+=$val['penalty'];
			@$hk[$val['karyawanid']]+=$val['hk'];
		}

		#6.3.4 Get Premi Kemandoran
		$query8 ="select sum(a.premiinput) as premi,a.karyawanid from ".$dbname.".kebun_premikemandoran a left join 
					".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
					and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					and a.kodeorg='" . $param['kodeorg'] . "' and a.periode = '" . $param['periodegaji'] . "'     
					and b.sistemgaji='Bulanan' and a.kontanan!='KONTAN' group by a.karyawanid";
		$premRes2 = fetchData($query8);
		foreach ($premRes2 as $idx => $val) {
			$premi[$val['karyawanid']]+=$val['premi'];
		} 

		$query88 ="select sum(a.premiinput) as premi,a.karyawanid from ".$dbname.".kebun_premikemandoran a left join 
					".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
					and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					and a.kodeorg='" . $param['kodeorg'] . "' and a.periode = '" . $param['periodegaji'] . "'     
					and b.sistemgaji='Bulanan' and a.kontanan='KONTAN' group by a.karyawanid";
		$premRes28 = fetchData($query88);
		foreach ($premRes28 as $idx => $val) {
			$premikontanan[$val['karyawanid']]+=$val['premi'];
		} 

		#= premi baru
		$query20 = "select sum(a.rplb) as premi,a.karyawanid
					from " . $dbname . ".kebun_3premibmtbs a left join 
					" . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
					where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
					and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					and a.kodeorg='" . $param['kodeorg'] . "'  
					and a.periode = '" . $param['periodegaji'] . "'     
					and b.sistemgaji='Bulanan' and a.kontanan !='KONTAN' group by a.karyawanid";
		$res20 = fetchData($query20);
		foreach ($res20 as $idx => $val) {
			$premi[$val['karyawanid']]+=$val['premi'];
		}

		$query202 = "select sum(a.rplb) as premi,a.karyawanid
					from " . $dbname . ".kebun_3premibmtbs a left join 
					" . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
					where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
					and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					and a.kodeorg='" . $param['kodeorg'] . "'  
					and a.periode = '" . $param['periodegaji'] . "'     
					and b.sistemgaji='Bulanan' and a.kontanan ='KONTAN' group by a.karyawanid"; 
		$res202 = fetchData($query202);
		foreach ($res202 as $idx => $val) {
			$premikontanan[$val['karyawanid']]+=$val['premi'];
		}

		#6.3.5 Get Premi dari BKM Sipil
		$query7="select a.tanggal, sum(a.umr) as upah,a.nik as karyawanid,sum(a.premi) as premi from ".$dbname.".vhc_spl_kehadiran_vw a left join 
				".$dbname.".datakaryawan b on a.nik=b.karyawanid
				where b.tipekaryawan in (1,2) and b.lokasitugas='".$param['kodeorg']."' and 
				(b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
				and sistemgaji='Bulanan' group by a.nik";
		$premRes2 = fetchData($query7); 
		foreach($premRes2 as $idx => $val){
			 if($val['premi']>0){   
				 @$premi[$val['karyawanid']]+=$val['premi'];
			 }
		}
				
		#premi tetap dari absensi==========================================
		$stkh="select a.tanggal,a.karyawanid,sum(a.premi+a.insentif) as premi from ".$dbname.".sdm_absensidt a 
				 left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					 where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
			   and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.karyawanid";
		$reskh=$owlPDO->query($stkh) or die(print " Gagal: ".PDOException::getMessage());
		$reskh->setFetchMode(PDO::FETCH_OBJ);            
		while($barky=$reskh->fetch()){
			@$premi[$barky->karyawanid]+=$barky->premi;
		}
		#end premi tetap dari absensi========================================== 
		
		#pastikan absen NS penuh selama sebulan
		
		$str = "select a.tanggal from " . $dbname . ".kebun_kehadiran_vw a left join 
		".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
		and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.unit like '" . $param['kodeorg'] . "%'
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		and sistemgaji='Bulanan' and a.notransaksi not like '%BOR%' group by a.karyawanid,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}				   

		$str = "select a.tanggal,a.karyawanid from " . $dbname . ".kebun_prestasi_vs_hk a left join 
		".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
		and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.unit like '" . $param['kodeorg'] . "%' 
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		and sistemgaji='Bulanan' and a.keterangan!='KONTAN' group by a.tanggal, a.karyawanid";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}					  

		$str = "select a.tanggal,a.idkaryawan as karyawanid from " . $dbname . ".vhc_runhk_vw a 
		left join " . $dbname . ".datakaryawan b on a.idkaryawan=b.karyawanid
		where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
		and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' 
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		and sistemgaji='Bulanan' group by a.idkaryawan,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}				  
						  
		$str="select a.tanggal, a.nik as karyawanid from ".$dbname.".vhc_spl_kehadiran_vw a left join 
		".$dbname.".datakaryawan b on a.nik=b.karyawanid
		where b.tipekaryawan in (1,2) and b.lokasitugas='".$param['kodeorg']."' and 
		(b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
		and sistemgaji='Bulanan' group by a.nik,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}				

		$str="select a.tanggal,a.karyawanid from ".$dbname.".sdm_absensidt a 
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.karyawanid,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}
				
		$str="select a.tanggal,a.nikmandor as karyawanid from ".$dbname.".kebun_aktifitas a
		left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.nikmandor,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}
		//exit('WARNING ');
		
		$str="select a.tanggal,a.nikmandor1 as karyawanid from ".$dbname.".kebun_aktifitas a
		left join ".$dbname.".datakaryawan b on a.nikmandor1=b.karyawanid
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.nikmandor1,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}
		
		$str="select a.tanggal,a.keranimuat as karyawanid from ".$dbname.".kebun_aktifitas a
		left join ".$dbname.".datakaryawan b on a.keranimuat=b.karyawanid
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.keranimuat,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}
		
		$str="select a.tanggal,a.nikasisten as karyawanid from ".$dbname.".kebun_aktifitas a
		left join ".$dbname.".datakaryawan b on a.nikasisten=b.karyawanid
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.nikasisten,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}


		#pastikan absen NS penuh selama sebulan
		$blmabsen="";
		foreach($karydidatakary as $karyid){
			if(count($cekabsen[$karyid])<$jlhhari){
				$nx++;
				$blmabsen.=$nx.". ".$namakar[$karyid]." (".count($cekabsen[$karyid]).")<br>";
		    }
		}
		
		if($blmabsen!='' and $param['periodegaji']>='2021-02'){
			exit("Masih ada karyawan Non Staff yang belum memiliki absen sebulan penuh (Jumlah hari kalender ".$jlhhari.")<br><br>No. Nama Karyawan (Jumlah Absen) <br><br>".$blmabsen);
		}
		
		
		foreach($premi as $idx=>$row) { 
		  #add to ready data================================================
		  if($row>0) {
		   $readyData[] = array(
			  'kodeorg'=>$param['kodeorg'],
			  'periodegaji'=>$param['periodegaji'],
			  'karyawanid'=>$idx,
			  'idkomponen'=>32,
			  'jumlah'=>$row,
			  'pengali' => 1,
			  'hk' => 0);
		  }
		}  

	foreach ($pesangon as $idx => $row) {
		#add to ready data================================================
		if ($row > 0) {
			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 89,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
		}
	}

	foreach ($kompensasi as $idx => $row) {
		#add to ready data================================================
		if ($row > 0) {
			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 98,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
		}
	}

	foreach ($uangpisah as $idx => $row) {
		#add to ready data================================================
		if ($row > 0) {
			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 97,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
		}
	}

		if(isset($premikontanan)){
			foreach ($premikontanan as $idx => $row) {
				#add to ready data================================================
				if ($row > 0) {
					#penambah
					$readyData[] = array(
						'kodeorg' => $param['kodeorg'],
						'periodegaji' => $param['periodegaji'],
						'karyawanid' => $idx,
						'idkomponen' => 31,
						'jumlah' => $row,
						'pengali' => 1,
						'hk'=>0);
						
					#di kurangi lagi	
					$readyData[] = array(
						'kodeorg' => $param['kodeorg'],
						'periodegaji' => $param['periodegaji'],
						'karyawanid' => $idx,
						'idkomponen' => 43,
						'jumlah' => $row,
						'pengali' => 1,
						'hk'=>0);
				}
			}
		}
		// echo"<pre>";
		// print_r($readyData);
		// echo"</pre>";
		// count($premikontanan);
					// exit("error");
		if(isset($borongan)){
			foreach($borongan as $idx=>$row) { 
			  #add to ready data================================================
			  if($row>0) {
				$readyData[] = array(
				  'kodeorg'=>$param['kodeorg'],
				  'periodegaji'=>$param['periodegaji'],
				  'karyawanid'=>$idx,
				  'idkomponen'=>30,
				  'jumlah'=>$row,
				  'pengali' => 1,
				  'hk' => 0);
				}
			}    
		}
		if(isset($potborongan)){
			foreach ($potborongan as $idx => $row) {
				#add to ready data================================================
				if ($row > 0) {
					$readyData[] = array(
						'kodeorg' => $param['kodeorg'],
						'periodegaji' => $param['periodegaji'],
						'karyawanid' => $idx,
						'idkomponen' => 48,
						'jumlah' => $row,
						'pengali' => 1,
						'hk'=>0);
				}
			}
		}
		if(!empty($premikontanan))foreach($premikontanan as $idx=>$row) { 
		  #add to ready data================================================
		  if($row>0) {
			$readyData[] = array(
			  'kodeorg'=>$param['kodeorg'],
			  'periodegaji'=>$param['periodegaji'],
			  'karyawanid'=>$idx,
			  'idkomponen'=>43,
			  'jumlah'=>$row,
			  'pengali' => 1,
			  'hk' => 0);
		  }
		}      

		@$cpenalty=count($penalty);
		if($cpenalty>0){
			foreach(@$penalty as $idx=>$row) { 
			  #add to ready data================================================
			  if($row>0) {             
				$readyData[] = array(
				  'kodeorg'=>$param['kodeorg'],
				  'periodegaji'=>$param['periodegaji'],
				  'karyawanid'=>$idx,
				  'idkomponen'=>34,
				  'jumlah'=>$row,
				  'pengali' => 1,
				  'hk' => 0);
			  }
			} 
		}
							 
		#penalty kehadiran dari absensi
		$stkh="select a.karyawanid,sum(a.penaltykehadiran) as penaltykehadiran from ".$dbname.".sdm_absensidt a left join 
			  ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			  where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			  and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
			  and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.karyawanid";
		$reskh=$owlPDO->query($stkh) or die(print " Gagal: ".PDOException::getMessage());
		$reskh->setFetchMode(PDO::FETCH_OBJ);              
		while($barkh=$reskh->fetch()){
		  if($barkh->penaltykehadiran>0)
			 $penaltykehadiran[$barkh->karyawanid]=$barkh->penaltykehadiran;
		}
					
		@$cpenaltykehadiran=count($penaltykehadiran);
		if($cpenaltykehadiran>0){	
		  foreach(@$penaltykehadiran as $idx=>$row) { 
			#add to ready data================================================
			if($row>0) {             
			  $readyData[] = array(
			  'kodeorg'=>$param['kodeorg'],
			  'periodegaji'=>$param['periodegaji'],
			  'karyawanid'=>$idx,
			  'idkomponen'=>41,
			  'jumlah'=>$row,
			  'pengali' => 1,
			  'hk' => 0);
			}
		  } 
		}
			
		#7. premi : sdm_premi
		$str = "select a.karyawanid,a.premi,a.jenis from " . $dbname . ".sdm_premi a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				where  b.tipekaryawan in(1,2,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				and sistemgaji='Bulanan' and periode='" . $param['periodegaji'] . "' group by a.karyawanid,a.jenis";
		$res = fetchData($str);
		foreach ($res as $idx => $row) {
		  if (isset($id[$row['karyawanid']])) {
			  $readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $row['karyawanid'],
				'idkomponen' => $row['jenis'],
				'jumlah' => $row['premi'],
				'pengali' => 1,
				'hk'=>0);
		  } else {
			  //abaikan jika tidak terdaftar pada karyawanid  
		  }
		}
		
		$rapelgaji=array();
		$i="select * from ".$dbname.".sdm_pendapatanlaindt a where left(kodeorg,4)='" . $param['kodeorg'] . "' and a.periodegaji='".$param['periodegaji']."' "
			." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan_hist b where tipekaryawan in (1,2) and"
			." (tanggalkeluar>='".$tanggal1."' or tanggalkeluar='0000-00-00') and alokasi in ('0','1') and sistemgaji='Bulanan' and"
			." (tanggalmasuk<='".$tanggal2."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   ) and posting='1'";        
		$n = fetchData($i);
		foreach($n as $idx => $val){
			$readyData[] = array(
			 'kodeorg'=>$param['kodeorg'],
			 'periodegaji'=>$param['periodegaji'],
			 'karyawanid'=>$val['karyawanid'],
			 'idkomponen'=>$val['idkomponen'],
			 'jumlah'=>$val['jumlah'],
			 'pengali' => 1,
			 'hk' => 0);
			if($val['idkomponen']=='14')
			{
			$rapelgaji[$val['karyawanid']]=$val['jumlah'];  
			}
		}

		############################################################################################################################

		###tambahan indra disini, memasukan bpjs kesehatan (jms) dan bpjs kesehatan
		##algoritma : jika kolom jms dan bpjs di datakaryawan terisi maka akan memotong
		##jika tidak maka di kosongkan
		// if ($tipeOrg == 'PABRIK') {
		// 	$bpjsorg = 'PABRIK';
		// } else {
		// 	$bpjsorg = 'KEBUN';
		// }

		$bpjsorg=$tipeOrg;

		$sUmpDaerah="select distinct jumlah from ".$dbname.".sdm_5gajipokok where tahun='".substr($param['periodegaji'],0,4)."' and idkomponen='87' and kodeorg='".$param['kodeorg']."'";
		$rUmpDaerah=fetchData($sUmpDaerah);
		$umpDaerah=$rUmpDaerah[0]['jumlah'];#bpjs kesehatan


		#= parameter aplikasi 

		#= kerja
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRBPJSKER'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrbpjs=explode(',',$bar['nilai']);
			foreach($arrbpjs as $key){
				$arrker[]=$key;
			}

		#= kesehatan
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRBPJSKES'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrbpjs=explode(',',$bar['nilai']);
			foreach($arrbpjs as $key){
				$arrkes[]=$key;
			}
			
			
		#= pensiun
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRBPJSPEN'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrbpjs=explode(',',$bar['nilai']);
			foreach($arrbpjs as $key){
				$arrpen[]=$key;
			}


		if(count($arrker)==0){
			exit("Warning : Setup Parameter aplikasi untuk kode HRBPJSKER belum ada.");
		}

		if(count($arrkes)==0){
			exit("Warning : Setup Parameter aplikasi untuk kode HRBPJSKES belum ada.");
		}

		if(count($arrpen)==0){
			exit("Warning : Setup Parameter aplikasi untuk kode HRBPJSPEN belum ada.");
		}


		#= Ketenagakerjaan JKK
		$str = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='" . $bpjsorg . "'";
		if(count(fetchdata($str))==0){
			exit("Warning : Setup BPJS belum ada.");
		}
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			#= kerja
			if(in_array($bar['jenisbpjs'],$arrker)){
				foreach ($umrbulanan as $key => $nilai) {
					if ($bpjstenaga[$key] != '') {
				#Untuk JKK,JHT, dan JKM jika gaji pokok dibawah UMP (Daerah) maka menggunakan angka UMP. 
				#Jika lebih menggunakan gaji pokok, berlaku untuk payroll HO&Site
				if($nilai<$umpDaerah){
				  $nilai=$umpDaerah;
				}
				$bebankaryawan=$bar['bebankaryawan'];
				$bebanperusahaan=$bar['bebanperusahaan'];
				if(substr($tmkjamsostek[$key],0,7)==$param['periodegaji'])
				{
					$bebankaryawan=$bar['bebankaryawantpdiskon'];
					$bebanperusahaan=$bar['bebanperusahaantpdiskon'];
				}
						$readyData[] = array(
							'kodeorg' => $lokasitugas[$key],
							'periodegaji' => $param['periodegaji'],
							'karyawanid' => $key,
							'idkomponen' => $bar['jenisbpjs'],
							'jumlah' => ($bebankaryawan / 100 * $nilai),
							'pengali' => 1,
							'hk'=>0);
							
						$readyData[] = array(
							'kodeorg' => $lokasitugas[$key],
							'periodegaji' => $param['periodegaji'],
							'karyawanid' => $key,
							'idkomponen' => $bar['jenisbpjsplus'],
							'jumlah' => ($bebanperusahaan / 100 * $nilai),
							'pengali' => 1,
							'hk'=>0);	
							
					}
				}
			}
			
			#= kesehatan
			if(in_array($bar['jenisbpjs'],$arrkes)){
			#= jika diparameter aplikasi diset 0 maka akan ambil dari gapok
					foreach ($umrbulanan as $key => $nilai) {
						if ($bpjskes[$key] != '') {
				   #Untuk JKK,JHT, dan JKM jika gaji pokok dibawah UMP (Daerah) maka menggunakan angka UMP. 
				   #Jika lebih menggunakan gaji pokok, berlaku untuk payroll HO&Site
					if($nilai<$umpDaerah){
						$nilai=$umpDaerah;
					}
					$bebankaryawan=$bar['bebankaryawan'];
					$bebanperusahaan=$bar['bebanperusahaan'];
					if(substr($tmkjamsostek[$key],0,7)==$param['periodegaji'])
					{
						$bebankaryawan=$bar['bebankaryawantpdiskon'];
						$bebanperusahaan=$bar['bebanperusahaantpdiskon'];
					}

							$readyData[] = array(
								'kodeorg' => $lokasitugas[$key],
								'periodegaji' => $param['periodegaji'],
								'karyawanid' => $key,
								'idkomponen' => $bar['jenisbpjs'],
								'jumlah' => ($bebankaryawan / 100 * $nilai * $bpjskestanggungan[$key]),
								'pengali' => 1,
								'hk'=>0);
						
							$readyData[] = array(
								'kodeorg' => $lokasitugas[$key],
								'periodegaji' => $param['periodegaji'],
								'karyawanid' => $key,
								'idkomponen' => $bar['jenisbpjsplus'],
								'jumlah' => ($bebanperusahaan / 100 * $nilai),
								'pengali' => 1,
								'hk'=>0);
						}
						
					}
				
			}
			
			#= pensiun
			if(in_array($bar['jenisbpjs'],$arrpen)){
				foreach ($umrbulanan as $key => $nilai) {
					if ($bpjspensiun[$key] != '') {
				#Untuk JP jika gaji pokok dibawah UMP (daerah) maka menggunakan UMP. Jika kurang dari Maks gunakan gaji pokok. 
				#Jika lebih dari maks maka gunakan maksimal gapok
				if($nilai<$umpDaerah){
					$nilai=$umpDaerah;
				}
				if($nilai>$bar['maxgaji']){
					$nilai=$bar['maxgaji'];
				}

				$bebankaryawan=$bar['bebankaryawan'];
				$bebanperusahaan=$bar['bebanperusahaan'];
				if(substr($tmkjamsostek[$key],0,7)==$param['periodegaji'])
				{
					$bebankaryawan=$bar['bebankaryawantpdiskon'];
					$bebanperusahaan=$bar['bebanperusahaantpdiskon'];
				}
						$readyData[] = array(
							'kodeorg' => $lokasitugas[$key],
							'periodegaji' => $param['periodegaji'],
							'karyawanid' => $key,
							'idkomponen' => $bar['jenisbpjs'],
							'jumlah' => ($bebankaryawan / 100 * $nilai),
							'pengali' => 1,
							'hk'=>0);
							
						$readyData[] = array(
							'kodeorg' => $lokasitugas[$key],
							'periodegaji' => $param['periodegaji'],
							'karyawanid' => $key,
							'idkomponen' => $bar['jenisbpjsplus'],
							'jumlah' => ($bebanperusahaan / 100 * $nilai),
							'pengali' => 1,
							'hk'=>0);	
					}
				}
			}
		}

		####################################################################################################################

		// echo "<pre>";
		// print_r($readyData);
		// echo "<pre>";
					 
		 //calculate to component
			   $strx="select id as komponen, case plus when 0 then -1 else plus end as pengali,name as nakomp 
					  FROM ".$dbname.".sdm_ho_component";
			   $comRes = fetchData($strx); 
			   $comp=Array();
			   $nakomp=Array();
			   foreach($comRes as $idx=>$row){
				  $comp[$row['komponen']]=$row['pengali'];
				  $nakomp[$row['komponen']]=$row['nakomp'];
			   }       
			   
			   
		   //=tampilan  ============================
				   $listbutton="<button class=mybuttton name=postBtn id=postBtn onclick=post()>Proses</button>"; 
				   $list0 ="<table class=sortable border=0 cellspacing=1>
							 <thead>
							 <tr class=rowheader>";
					$list0 .= "<td>".$_SESSION['lang']['nomor']."</td>";
					$list0 .= "<td>".$_SESSION['lang']['periodegaji']."</td>";
					$list0 .= "<td>".$_SESSION['lang']['karyawanid']."</td>";
					$list0.= "<td>".$_SESSION['lang']['jumlah']."</td>";
		//            $list0.= "<td>PPh21</td>";
					$list0.="</tr></thead><tbody>";
					
		//periksa gaji minus
			$negatif=false; 
			$list1='';
			 $listx = "Masih ada gaji dibawah 0:";    
			$list2='';
			$list3='';
			$no=0;

		   if($readyData<1)
		   {
			   exit("Error:Data Kosong");
		   }
		   
			   foreach($id as $key=>$val){
				   $sisa[$val[0]]=0;
				   foreach($readyData as $dat=>$bar){
					  if($val[0]==$bar['karyawanid'])
					  {
						  $sisa[$val[0]]+=$bar['jumlah']*$comp[$bar['idkomponen']]; 
						  
							
					  }  
					  continue;
				   }
				   
								
				   if($sisa[$val[0]]<0)
				   {
						$list1 .="<tr class=rowcontent>";
						$list1 .= "<td>-</td>";
						$list1 .= "<td>".$param['periodegaji']."</td>";
						$list1 .= "<td>".$val[0]." ".$namakar[$val[0]]."</td>";
						$list1 .= "<td>***</td>";
						#$list1 .= "<td>".number_format($sisa[$val[0]],0,',','.')."</td>";
		//                $list1 .= "<td>".number_format($pph21[$val[0]],0,',','.')."</td>";
						$list1 .= "</tr>";                
						$negatif=true;                
				   } 
				   else
				   {
					   $no+=1; 
						$list2 .="<tr class=rowcontent>";
						$list2 .= "<td>".$no."</td>";
						$list2 .= "<td>".$param['periodegaji']."</td>";
						$list2 .= "<td>".$val[0]." ".$namakar[$val[0]]."</td>";
						$list2 .= "<td>***</td>";
						#$list2 .= "<td align=right>".number_format($sisa[$val[0]],0,',','.')."</td>";
		//                $list2 .= "<td align=right>".number_format($pph21[$val[0]],0,',','.')."</td>";
						$list2 .= "</tr>";  
				   }    
			   }
			#est gaji gak perlu minus2an
			if($method=='estgaji'){
				$negatif = false;
			}
			
		$list3="</tbody><table>";   
}else{
		
		#2. Get Karyawan bulanan yang penggajian=bulanan dan alokasi in ('0','1')
		$query1 = "select a.tanggallahir, a.nik,a.karyawanid,a.tmkjamsostek,statuspajak,tipekaryawan,namakaryawan,jms,bpjs,pensiun, lokasitugas,tanggalkeluar, a.jumlahtanggungan as jmltanggungan from " . $dbname . ".datakaryawan_hist a where tipekaryawan in(1,2,6) and lokasitugas='" . $param['kodeorg'] . "' and (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi in ('0','1') and ( tanggalmasuk<='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and a.periodegaji='".$param['periodegaji']."' and a.version_type='B' group by a.karyawanid";
		// echo $query1;
		$absRes = fetchData($query1);
		# Error empty karyawan
		if(empty($absRes)) {
		  echo "Error : There is no prsence(kehadiran) on this period";
		  exit();
		}
		
		  $id=Array();
		  $idaaaa='';
		  $umrbulanansebelumnya=array();
		  foreach($absRes as $row => $kar)
		  {
		  $karydidatakary[$kar['karyawanid']]=$kar['karyawanid'];
			$id[$kar['karyawanid']][]=$kar['karyawanid'];
			$namakar[$kar['karyawanid']]=$kar['namakaryawan'];
			$nikkary[$kar['karyawanid']]=$kar['nik'];
			$lokasitugas[$kar['karyawanid']]=$kar['lokasitugas'];
			$tglkeluar[$kar['karyawanid']]=$kar['tanggalkeluar'];
			$tmkjamsostek[$kar['karyawanid']]=$kar['tmkjamsostek'];

			if($idaaaa=='')
			{
				$idaaaa="'".$kar['karyawanid']."'";
			}
			else
			{
				$idaaaa.=",'".$kar['karyawanid']."'";
			}
			//$nojms[$kar['karyawanid']]=trim($kar['jms']);
			//$nobpjs[$kar['karyawanid']]=trim($kar['bpjs']);
			$umrbulanansebelumnya[$kar['karyawanid']]=0;
			#mengambil no Jamsostek
			#bpjstenaga
			$bpjstenaga[$kar['karyawanid']] = trim($kar['jms']); // JKK JKM JHT
			#bpjs pensiun
			$diff= (strtotime($tanggal1)-strtotime($kar['tanggallahir']));
			$umur= floor($diff/(60*60*24*365));
			if($umur>57){
				$bpjspensiun[$kar['karyawanid']] = ""; // JP
			}else{				
				$bpjspensiun[$kar['karyawanid']] = trim($kar['pensiun']); // JP
			}
			
			#bpjskes
			$bpjskes[$kar['karyawanid']] = trim($kar['bpjs']); // KESEHATAN
			$bpjskestanggungan[$kar['karyawanid']] = $kar['jmltanggungan']+1;
		  }  


		#1ambil semua komponen dari gajipokok=====================
		   
		// $str1 = "select a.*,b.namakaryawan,b.tipekaryawan from ".$dbname.".sdm_5gajipokok a left join 
		// ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid
		// where a.tahun=".substr($tanggal1,0,4)." and b.tipekaryawan='1' and b.lokasitugas='".$param['kodeorg']."' 
		// and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		// and sistemgaji='Bulanan'";

		## Parameter aplikasi
		#= ambil komponen yang termasuk di gaji pokok
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRGAPOK'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch(); 
		$arrgapok=explode(',',$bar['nilai']);
		foreach($arrgapok as $key){
		  $arrcomgapok[]=$key;
		}


		$periodelalu=date("Y-m", (strtotime("-1 months",strtotime($tanggal1))));
		$tahunlalu=date("Y", (strtotime("-1 years",strtotime($tanggal1))));

		$sUmpsebelumnya="select * from ".$dbname.".sdm_5gajipokok  
					  where tahun='".$tahunlalu."' and idkomponen='1' and karyawanid in (".$idaaaa.") ";
		//echo $sUmpsebelumnya;
		$rUmpsebelumnya=fetchData($sUmpsebelumnya);
		foreach ($rUmpsebelumnya as $key => $val) {
		$umrbulanansebelumnya[$val['karyawanid']]=$val['jumlah'];
		}

		$sUmpsebelumnya="select * from ".$dbname.".sdm_gaji  
					  where periodegaji='".$periodelalu."' and idkomponen='1'and karyawanid in (".$idaaaa.")";
		$rUmpsebelumnya=fetchData($sUmpsebelumnya);
		foreach ($rUmpsebelumnya as $key => $val) {
			if($val['jumlah']>0)
			{
			$umrbulanansebelumnya[$val['karyawanid']]=$val['jumlah'];
			}
		}
		// print_r($umrbulanansebelumnya);
		// exit();
		$tjms=array();   
		$tipekaryawan=array();
		$jumlahhariperbulan=substr($tanggal2,8,2);     
		$str1 = "select a.*,b.namakaryawan from ".$dbname.".sdm_5gajipokok a left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				 where a.tahun=" . substr($tanggal1, 0, 4) . " and b.tipekaryawan in (1,2) and b.lokasitugas='" . $param['kodeorg'] . "' 
				 and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B' and 
				 idkomponen in (select id from " . $dbname . ".sdm_ho_component where type='basic' and id not in ('59'))";       
		$res1 = fetchData($str1);
		foreach($res1 as $idx => $val){
		  $karydigp[$val['karyawanid']]=$val['karyawanid'];
			if($id[$val['karyawanid']][0]==$val['karyawanid']){
				#add to ready data================================================
				
				##Perhitungan proporsi gaji pokok, ketika tanggal keluar terisi dan berada di periode perhitungan
				##Gaji Pokok dihitung sesuai dengan jumlah hari masuk
				$jmlhhari=$jumlahval=$jumlahumrbpjs=0;
				if(in_array($val['idkomponen'],$arrcomgapok)){
					if ($tglkeluar[$val['karyawanid']]!='0000-00-00' && substr($tglkeluar[$val['karyawanid']],0,7)==$param['periodegaji']) {

						$jmlhhari=substr($tglkeluar[$val['karyawanid']],8,2);
						if ($tglkeluar[$val['karyawanid']]<$tanggal2) {
							$jumlahval=($jmlhhari/$jumlahhariperbulan)*$val['jumlah'];
						}else{
							$jumlahval=$val['jumlah'];
						}

						##jika tanggal keluar >= dari tanggal 15, maka bpjs dibayarkan
						##jika tanggal keluar < dari tanggal 15, maka bpjs tidak dibayarkan (0)
						if ($val['idkomponen']==1 ) {
							if ($jmlhhari>=15) {
								$jumlahumrbpjs=$val['jumlah'];
							}
						}
						
					}else{
						$jumlahval=$val['jumlah'];

						if ($val['idkomponen']==1 ) {
							$jumlahumrbpjs=$val['jumlah'];
						}
					}
				}else{
					$jumlahval=$val['jumlah'];
				}
				
				$readyData[] = array(
				  'kodeorg'=>$param['kodeorg'],
				  'periodegaji'=>$param['periodegaji'],
				  'karyawanid'=>$val['karyawanid'],
				  'idkomponen'=>$val['idkomponen'],
				  'jumlah'=>$jumlahval,
				  'pengali' => 1,
				  'hk' => 0
				);
			}
			 
			if($val['idkomponen']==1){   
				$umrbulanan[$val['karyawanid']] = $jumlahumrbpjs;
				if($statNatura==1){
				  $umrbulanan[$val['karyawanid']] += $arrRpNatura[$val['karyawanid']];
				}
				
			   // $gajiperhari[$val['karyawanid']]=($val['jumlah']/30);
			   $gajiperhari[$val['karyawanid']]=($val['jumlah']/25);
			} 
		}

		#cek pastikan semua kary memiliki gapok
		$blmadagp="";$ngpblmada=0;
		foreach($karydidatakary as $dtkary){
		  if($karydigp[$dtkary]=='0' or $karydigp[$dtkary]==''){
			$ngpblmada+=1;
			$blmadagp.=$ngpblmada.". ".$nikkary[$dtkary]." - ".$namakar[$dtkary]."\n";
		  }
		}

		if(!empty($blmadagp)){
		  exit("Warning : Ada karyawan belum memiliki gajipokok.\n".$blmadagp."");
		}

		#======================================
		#==== buat potongan hk kbt
		#======================================
		$tdkdibayar = array();
		$hktdkbyr=array();
		$hk=array();

		#ambil jumlah hk tidak dibayar untuk KHT dan total tidak dibayar
		$strgjh = "select  count(*) as jlh,b.karyawanid from " . $dbname . ".sdm_hktdkdibayar_vw a left join 
					  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
					   where b.tipekaryawan in (1,2) and b.lokasitugas='" . $param['kodeorg'] . "' 
					   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
					   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
					   and sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B' 
					   group by a.karyawanid";
		///exit('warning:'.$strKrg."____".$strgjh);

		$resgjh = fetchData($strgjh);
		foreach ($resgjh as $idx => $val) {
			@$tdkdibayar[$val['karyawanid']] += $gajiperhari[$val['karyawanid']] * $val['jlh']; #jumlah tidak dibayar
			@$hktdkbyr[$val['karyawanid']] +=$val['jlh'];
			@$lstKary[$val['karyawanid']]=$val['karyawanid'];
		}

		@$dtkarhktdkbayar=count($lstKary);

		if(@$dtkarhktdkbayar>0){
		foreach(@$lstKary as $idx=>$val){
		   if($hktdkbyr[$val]==''){
			 @$hktdkbyr[$val]=0;
		   }
		  $readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $val,
			'idkomponen' => 37, //potongan hk
			'jumlah' => $tdkdibayar[$val],
			'pengali' => 1,
			'hk'=>$hktdkbyr[$val]);
		  }
		}   


		#3. Get Lembur Data
		$where2 = " a.kodeorg like '".$param['kodeorg']."%' and (tanggal>='".$tanggal1."' and tanggal<='".$tanggal2."')";
		$query2="select a.karyawanid,sum(a.uangkelebihanjam) as lembur from ".$dbname.".sdm_lemburdt a left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				 where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
				 and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				 and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'  and ".$where2." group by a.karyawanid";
		$lbrRes = fetchData($query2); 
		foreach($lbrRes as $idx=>$row) {  
		  if(isset ($id[$row['karyawanid']])){
			$readyData[] = array(
			'kodeorg'=>$param['kodeorg'],
			'periodegaji'=>$param['periodegaji'],
			'karyawanid'=>$row['karyawanid'],
			'idkomponen'=>33,   
			'jumlah'=>$row['lembur'],
			'pengali' => 1,
			'hk' => 0);
		  }else{
			//abaikan jika tidak terdaftar pada karyawanid  
		  }   
		}

		#4. Get Potongan Data============================================================
		$where3 = " kodeorg like '%".$param['kodeorg']."%' and a.periodegaji='".$param['periodegaji']."'";
		$query3="select a.nik as karyawanid,sum(jumlahpotongan) as potongan,tipepotongan from ".$dbname.".sdm_potongandt a left join 
				".$dbname.".datakaryawan_hist b on a.nik=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B' where b.tipekaryawan in('2','1','6') and b.lokasitugas='".$param['kodeorg']."' 
				 and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'                    
				 and ".$where3." group by a.nik,a.tipepotongan";
		$potRes = fetchData($query3);
		foreach($potRes as $idx=>$row) {  
		  if(isset ($id[$row['karyawanid']])){
			$readyData[] = array(
			'kodeorg'=>$param['kodeorg'],
			'periodegaji'=>$param['periodegaji'],
			'karyawanid'=>$row['karyawanid'],
			'idkomponen'=>$row['tipepotongan'],   
			'jumlah'=>$row['potongan'],
			'pengali' => 1,
			'hk' => 0);
		  }else{
			//abaikan jika tidak terdaftar pada karyawanid  
		  }   
		}
		$notrgi=array();
		$karygi=array();
		#Potongan Pinjaman BBM
		$where92 = " (a.notransaksi like '%".$param['kodeorg']."%' and a.notransaksi like '".str_replace('-', '', $param['periodegaji'])."%')";
		$str="select a.* FROM ".$dbname.".log_permintaanpicdt a
			left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			left join ".$dbname.".log_transaksidt c on a.notransaksi=c.notransaksi and a.kodebarang=c.kodebarang
			left join ".$dbname.".log_transaksiht d on a.notransaksi=d.notransaksi
			where statussaldo ='1' and d.post='1' and b.tipekaryawan in('2','1','6') and b.lokasitugas='".$param['kodeorg']."' 
				and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'  and ".$where92."";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());    
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
		  $notrgi[$bar['notransaksi']]=$bar['notransaksi'];
		  $kdbrgi[$bar['kodebarang']]=$bar['kodebarang'];
		  
		  $karygi[$bar['notransaksi']][$bar['kodebarang']][$bar['karyawanid']]=$bar['karyawanid'];
		  $jlhbrg[$bar['notransaksi']][$bar['kodebarang']]+=$bar['realisasi'];
		  $jlhperkary[$bar['notransaksi']][$bar['kodebarang']][$bar['karyawanid']]+=$bar['realisasi'];
		}
		if(count($notrgi)>0){
		  $strap = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRPBBM'";
		  $resap = fetchData($strap);
		  
		  $strx = "select * from " . $dbname . ".keu_jurnaldt_vw where kodekegiatan in (".$resap[0]['nilai'].") and kodebarang in ('".implode("','",$kdbrgi)."') and noreferensi in ('".implode("','",$notrgi)."') and debet>0";
		  $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		  $resx->setFetchMode(PDO::FETCH_ASSOC);
		  while ($barx = $resx->fetch()) {
			$jlhrp[$bar['noreferensi']][$bar['kodebarang']]+=$bar['jumlah'];
		  }
		}
		if(count($karygi)>0){
		  $hargabarang=$potperkarya=0;
		  foreach($karygi as $notr => $valkdbrg) { 
			foreach($valkdbrg as $kodebrg => $valkary) { 
			  foreach($valkary as $kary => $karyid) { 
				$hargabarang = $jlhrp[$notr][$kodebrg]/$jlhbrg[$notr][$kodebrg];
				$potperkarya = $jlhperkary[$notr][$kodebrg][$kary] * $hargabarang;
				
				if(isset ($id[$kary])){
				  $readyData[] = array(
				  'kodeorg'=>$param['kodeorg'],
				  'periodegaji'=>$param['periodegaji'],
				  'karyawanid'=>$kary,
				  'idkomponen'=>92,   
				  'jumlah'=>$potperkarya,
				  'pengali' => 1,
				  'hk' => 0);
				}
			  }
			} 
		  }
		}

		$query4 = "select lokasitugas,a.karyawanid,jenis,sum(jumlah) as bulanan from " . $dbname . ".sdm_angsurandt a left join 
					  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
					   where 1=1 and b.tipekaryawan in(1,2) and b.lokasitugas='" . $param['kodeorg'] . "' 
					   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'  
					   and a.status=1  and bulan='".$param['periodegaji']."'";
		$angRes = fetchData($query4);
		foreach ($angRes as $idx => $row) {
			if ($id[$row['karyawanid']][0] == $row['karyawanid']) {
		  #add to ready data================================================
		  $readyData[] = array(
			'kodeorg' => $row['lokasitugas'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => $row['jenis'],
			'jumlah' => $row['bulanan'],
			'pengali' => 1,
			'hk'=>0); 
			}
		} 
		  
		  
		#6 Premi dan penalty =======================================================================
		#6.0 periksa posting transaksi
		#posting borongan
		$stru0="select distinct(tanggal) from ".$dbname.".kebun_kehadiran_vw a left join 
			   ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.unit like '".$param['kodeorg']."%' and a.jurnal=0 
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'    
			   and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.notransaksi like '%BOR%' order by tanggal";
		$resu0=$owlPDO->query($stru0) or die(print " Gagal: ".PDOException::getMessage());
		$resu0->setFetchMode(PDO::FETCH_OBJ);
		$numrows0=owlBaris($resu0);

		#posting perawatan
		$stru1="select distinct(tanggal) from ".$dbname.".kebun_kehadiran_vw a left join 
			   ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.unit like '".$param['kodeorg']."%' and a.jurnal=0 
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'    
			   and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.notransaksi not like '%BOR%' order by tanggal";
		$resu1=$owlPDO->query($stru1) or die(print " Gagal: ".PDOException::getMessage());
		$resu1->setFetchMode(PDO::FETCH_OBJ);
		$numrows1=owlBaris($resu1);

		#posting panen
		$stru2="select distinct(tanggal) from ".$dbname.".kebun_prestasi_vw a left join 
			   ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.unit like '".$param['kodeorg']."%' and a.jurnal=0
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
			   and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   order by tanggal";
		$resu2=$owlPDO->query($stru2) or die(print " Gagal: ".PDOException::getMessage());
		$resu2->setFetchMode(PDO::FETCH_OBJ);
		$numrows2=owlBaris($resu2);

		#posting traksi
		$stru3="select distinct(tanggal) from ".$dbname.".vhc_runhk_vw a left join 
			   ".$dbname.".datakaryawan_hist b on a.idkaryawan=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			   where b.tipekaryawan in(1,2,6) and b.lokasitugas='".$param['kodeorg']."' 
			   and  (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
			   and posting=0 and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   order by tanggal";
		$resu3=$owlPDO->query($stru3) or die(print " Gagal: ".PDOException::getMessage());
		$resu3->setFetchMode(PDO::FETCH_OBJ);
		$numrows3=owlBaris($resu3);

		#posting sdm lembur
		$stru4 = "select * from " . $dbname . ".sdm_lemburht 
				   where tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "'     
				   and posting=0 and kodeorg like '".$param['kodeorg']."%'  order by tanggal";

		$resu4 = $owlPDO->query($stru4) or die(print " Gagal: " . PDOException::getMessage());
		$resu4->setFetchMode(PDO::FETCH_OBJ);
		$numrows4 = owlBaris($resu4);

		#posting sdm pesangon
		$stru99 = "select * from " . $dbname . ".sdm_pesangon 
			   where tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "'     
			   and posting=0 and kodeunit like '".$param['kodeorg']."%'  order by tanggal";

		$resu99 = $owlPDO->query($stru99) or die(print " Gagal: " . PDOException::getMessage());
		$resu99->setFetchMode(PDO::FETCH_OBJ);
		$numrows99 = owlBaris($resu99);


		if($method=='estgaji'){
		  $numrows0=$numrows1=$numrows2=$numrows3=$numrows4=$numrows99=0;
		}

		if($param['kodeorg']!='TPRM'){
		  if($numrows0>0 or $numrows1>0 or $numrows2>0 or $numrows3>0)
		  {
			echo"Masih ada data yang belum di posting/There still unconfirmed transaction:";
			echo"<table class=sortable border=0 cellspacing=1>
				<thead><tr class=rowheader>
				<td>".$_SESSION['lang']['jenis']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				</tr></thead><tbody>";
			while($bar=$resu0->fetch())
			{
			  echo"<tr class=rowcontent><td>Borongan Karyawan Sendiri</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
			}
		  while($bar=$resu1->fetch())
			{
			  echo"<tr class=rowcontent><td>Perawatan Kebun</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
			}
			while($bar=$resu2->fetch())
			{
			  echo"<tr class=rowcontent><td>Panen</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
			}
			while($bar=$resu3->fetch())
			{
			  echo"<tr class=rowcontent><td>Traksi Pekerjaan</td><td>".tanggalnormal($bar->tanggal)."</td></tr>";
			}
			echo "</tbody><tfoot></tfoot></table>";
			exit();//keluar dari proses
		  }
		}


		if ($numrows4 > 0 or $numrows99 > 0) {
			echo"Masih ada data yang belum di posting/There still unconfirmed transaction:";
			echo"<table class=sortable border=0 cellspacing=1>
					<thead><tr class=rowheader>
					<td>" . $_SESSION['lang']['jenis'] . "</td>
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					</tr></thead><tbody>";
			while ($bar = $resu4->fetch()) {
				echo"<tr class=rowcontent><td>SDM Lembur</td><td>" . $bar->kodeorg . "</td><td>" . tanggalnormal($bar->tanggal) . "</td></tr>";
			}
			while ($bar = $resu99->fetch()) {
				echo"<tr class=rowcontent><td>SDM Pesangon</td><td>" . $bar->kodeunit . "</td><td>" . tanggalnormal($bar->tanggal) . "</td></tr>";
			}
			echo "</tbody><tfoot></tfoot></table>";
			exit(); //keluar dari proses
		}

		#6.3.1 Get Premi Kegiatan borongan 
		$borongan=array();
		$queryi = "select sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi from " . $dbname . ".kebun_kehadiran_vw a left join 
				  ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				   where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				   and a.unit like '" . $param['kodeorg'] . "%'
				   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				   and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.notransaksi like '%BOR%' group by a.karyawanid";
		$premResi = fetchData($queryi);
		foreach ($premResi as $idx => $val) {
		if ($val['premi'] > 0)
		  $borongan[$val['karyawanid']]+=$val['premi'];
		  $potborongan[$val['karyawanid']]+=$val['premi'];
		}     


		#6.3.1 Get Pesangon 
		$query5xx = "select a.* from " . $dbname . ".sdm_pesangon a left join 
				  ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				   where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				   and a.kodeunit like '" . $param['kodeorg'] . "%'
				   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				   and b.sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B' and posting='1' ";
		//echo $query5xx;
		$pesangonxx = fetchData($query5xx);
		foreach ($pesangonxx as $idx => $val) {
			  if($val['jenispesangon']=='Pesangon')
			  {
			  	$pesangon[$val['karyawanid']]+=$val['totalterima'];
			  }
			  elseif($val['jenispesangon']=='Kompensasi')
			  {
			  	$kompensasi[$val['karyawanid']]+=$val['totalterima'];
			  }
			  elseif($val['jenispesangon']=='Uang Pisah')
			  {
			  	$uangpisah[$val['karyawanid']]+=$val['totalterima'];
			  }
			//$premi[$val['karyawanid']]+=$val['premi'];
		} 

		#6.3.1 Get Premi Kegiatan Perawatan 
		$query5 = "select a.tanggal,sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi from " . $dbname . ".kebun_kehadiran_vw a left join 
				  ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				   where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				   and a.unit like '" . $param['kodeorg'] . "%'
				   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				   and sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'  and a.notransaksi not like '%BOR%' group by a.karyawanid";
		$premRes = fetchData($query5);
		foreach ($premRes as $idx => $val) {
		if ($val['premi'] > 0)
		  $premi[$val['karyawanid']]+=$val['premi'];
		}     

		#6.3.2 Get Premi Kegiatan Panen
		$query6 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.tpremi) as premi,sum(a.rupiahpenalty) as penalty,
				  sum(hkpanenperhari) as hk ,sum(a.upahpenalty) as upahpenalty from " . $dbname . ".kebun_prestasi_vs_hk a left join 
				  ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				  and a.unit like '" . $param['kodeorg'] . "%' 
				  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				  and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.keterangan!='KONTAN' group by a.tanggal, a.karyawanid";
		$premRes1 = fetchData($query6);
		foreach ($premRes1 as $idx => $val) {
		  @$premi[$val['karyawanid']]+=$val['premi'];
		  @$penalty[$val['karyawanid']]+=$val['penalty'];
		}

		$query66 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.tpremi) as premi,sum(a.rupiahpenalty) as penalty,
				  sum(hkpanenperhari) as hk ,sum(a.upahpenalty) as upahpenalty from " . $dbname . ".kebun_prestasi_vs_hk a left join 
				  ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				  and a.unit like '" . $param['kodeorg'] . "%' 
				  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				  and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.keterangan='KONTAN' group by a.tanggal, a.karyawanid"; 
		$premRes16 = fetchData($query66);
		foreach ($premRes16 as $idx => $val) {
		  @$premikontanan[$val['karyawanid']]+=$val['premi'];
		  @$penalty[$val['karyawanid']]+=$val['penalty'];
		}

		#6.3.3 Get Premi Transport dan gaji pokok BHL
		$query7 = "select a.tanggal,sum(a.upah) as upah,a.idkaryawan as karyawanid,sum(a.premi) as premi,sum(a.penalty) as penalty,sum(hk) as hk
				  from " . $dbname . ".vhc_runhk_vw a left join " . $dbname . ".datakaryawan_hist b on a.idkaryawan=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
				  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				  and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' 
				  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				  and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   group by a.idkaryawan";
		$premRes2 = fetchData($query7);
		foreach ($premRes2 as $idx => $val) {
		  @$premi[$val['karyawanid']]+=$val['premi'];
		  @$penaltytraksi[$val['karyawanid']]+=$val['penalty'];
		  @$hk[$val['karyawanid']]+=$val['hk'];
		}

		#6.3.4 Get Premi Kemandoran
		$query8 ="select sum(a.premiinput) as premi,a.karyawanid from ".$dbname.".kebun_premikemandoran a left join 
			  ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
			  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and a.kodeorg='" . $param['kodeorg'] . "' and a.periode = '" . $param['periodegaji'] . "'     
			  and b.sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.kontanan!='KONTAN' group by a.karyawanid";
		$premRes2 = fetchData($query8);
		foreach ($premRes2 as $idx => $val) {
		  $premi[$val['karyawanid']]+=$val['premi'];
		} 

		$query88 ="select sum(a.premiinput) as premi,a.karyawanid from ".$dbname.".kebun_premikemandoran a left join 
			  ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
			  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and a.kodeorg='" . $param['kodeorg'] . "' and a.periode = '" . $param['periodegaji'] . "'     
			  and b.sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.kontanan='KONTAN' group by a.karyawanid";
		$premRes28 = fetchData($query88);
		foreach ($premRes28 as $idx => $val) {
		  $premikontanan[$val['karyawanid']]+=$val['premi'];
		} 

		#= premi baru
		$query20 = "select sum(a.rplb) as premi,a.karyawanid
			  from " . $dbname . ".kebun_3premibmtbs a left join 
			  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
			  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and a.kodeorg='" . $param['kodeorg'] . "'  
			  and a.periode = '" . $param['periodegaji'] . "'     
			  and b.sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.kontanan !='KONTAN' group by a.karyawanid";
		$res20 = fetchData($query20);
		foreach ($res20 as $idx => $val) {
		  $premi[$val['karyawanid']]+=$val['premi'];
		}

		$query202 = "select sum(a.rplb) as premi,a.karyawanid
			  from " . $dbname . ".kebun_3premibmtbs a left join 
			  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			  where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
			  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and a.kodeorg='" . $param['kodeorg'] . "'  
			  and a.periode = '" . $param['periodegaji'] . "'     
			  and b.sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and a.kontanan ='KONTAN' group by a.karyawanid"; 
		$res202 = fetchData($query202);
		foreach ($res202 as $idx => $val) {
		  $premikontanan[$val['karyawanid']]+=$val['premi'];
		}

		#6.3.5 Get Premi dari BKM Sipil
		$query7="select a.tanggal,sum(a.umr) as upah,a.nik as karyawanid,sum(a.premi) as premi from ".$dbname.".vhc_spl_kehadiran_vw a left join 
			".$dbname.".datakaryawan_hist b on a.nik=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			where b.tipekaryawan in (1,2) and b.lokasitugas='".$param['kodeorg']."' and 
			(b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
			and sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'  group by a.nik";
		$premRes2 = fetchData($query7); 
		foreach($premRes2 as $idx => $val){
		   if($val['premi']>0){   
			 @$premi[$val['karyawanid']]+=$val['premi'];
		   }
		}
			
		#premi tetap dari absensi==========================================
		$stkh="select a.tanggal,a.absensi,a.karyawanid,sum(a.premi+a.insentif) as premi from ".$dbname.".sdm_absensidt a 
			   left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			   where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
			   and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'    
			   and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.karyawanid";
		$reskh=$owlPDO->query($stkh) or die(print " Gagal: ".PDOException::getMessage());
		$reskh->setFetchMode(PDO::FETCH_OBJ);            
		while($barky=$reskh->fetch()){
		  @$premi[$barky->karyawanid]+=$barky->premi;
		}
		#end premi tetap dari absensi========================================== 

		#pastikan absen NS penuh selama sebulan
		
		$str = "select a.tanggal,a.karyawanid from " . $dbname . ".kebun_kehadiran_vw a left join 
		".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
		and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.unit like '" . $param['kodeorg'] . "%'
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		and sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'  
		and a.notransaksi not like '%BOR%' group by a.karyawanid,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}		
				
		$str = "select a.tanggal,a.karyawanid from " . $dbname . ".kebun_prestasi_vs_hk a left join 
		".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
		and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.unit like '" . $param['kodeorg'] . "%' 
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   
		and a.keterangan!='KONTAN' group by a.tanggal, a.karyawanid";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}	
		
		$str = "select a.tanggal,a.idkaryawan as karyawanid from " . $dbname . ".vhc_runhk_vw a 
		left join " . $dbname . ".datakaryawan_hist b on a.idkaryawan=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in(1,2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
		and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' 
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		and sistemgaji='Bulanan' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   group by a.idkaryawan,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}			
				
		$str="select a.tanggal, a.nik as karyawanid from ".$dbname.".vhc_spl_kehadiran_vw a left join 
		".$dbname.".datakaryawan_hist b on a.nik=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in (1,2) and b.lokasitugas='".$param['kodeorg']."' and 
		(b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
		and sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'  group by a.nik,a.tanggal";
		$res = fetchData($str); 
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}			
				
		$str="select a.tanggal,a.absensi,a.karyawanid,sum(a.premi+a.insentif) as premi from ".$dbname.".sdm_absensidt a 
		left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'    
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.karyawanid,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}

		$str="select a.tanggal,a.nikmandor as karyawanid from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan_hist b on a.nikmandor=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'    
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by nikmandor,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}
		
		$str="select a.tanggal,a.nikmandor1 as karyawanid from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan_hist b on a.nikmandor1=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'    
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by nikmandor1,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}
		
		$str="select a.tanggal,a.keranimuat as karyawanid from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan_hist b on a.keranimuat=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'    
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by keranimuat,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}
		
		$str="select a.tanggal,a.nikasisten as karyawanid from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan_hist b on a.nikasisten=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
		where b.tipekaryawan in(1,2,6)  and b.lokasitugas='".$param['kodeorg']."' 
		and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
		and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'    
		and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by nikasisten,a.tanggal";
		$res = fetchData($str);
		foreach($res as $bar){
			$cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
		}


		#pastikan absen NS penuh selama sebulan
		$blmabsen="";
		foreach($karydidatakary as $karyid){
			if(count($cekabsen[$karyid])<$jlhhari){
				$nx++;
				$blmabsen.=$nx.". ".$namakar[$karyid]." (".count($cekabsen[$karyid]).")<br>";
		    }
		}
		
		if($blmabsen!=''){
			exit("Masih ada karyawan Non Staff yang belum memiliki absen sebulan penuh (Jumlah hari kalender ".$jlhhari.")<br><br>No. Nama Karyawan (Jumlah Absen) <br><br>".$blmabsen);
		}
			
			
		foreach($premi as $idx=>$row) { 
		  #add to ready data================================================
		  if($row>0) {
		   $readyData[] = array(
			  'kodeorg'=>$param['kodeorg'],
			  'periodegaji'=>$param['periodegaji'],
			  'karyawanid'=>$idx,
			  'idkomponen'=>32,
			  'jumlah'=>$row,
			  'pengali' => 1,
			  'hk' => 0);
		  }
		}    

		foreach ($pesangon as $idx => $row) {
		#add to ready data================================================
		if ($row > 0) {
			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 89,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
		}
	}

	foreach ($kompensasi as $idx => $row) {
		#add to ready data================================================
		if ($row > 0) {
			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 98,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
		}
	}

	foreach ($uangpisah as $idx => $row) {
		#add to ready data================================================
		if ($row > 0) {
			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 97,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
		}
	}

		if(isset($premikontanan)){
		  foreach ($premikontanan as $idx => $row) {
			#add to ready data================================================
			if ($row > 0) {
			  #penambah
			  $readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 31,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
				
			  #di kurangi lagi  
			  $readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 43,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
			}
		  }
		}
		// echo"<pre>";
		// print_r($readyData);
		// echo"</pre>";
		// count($premikontanan);
			  // exit("error");
		if(isset($borongan)){
		  foreach($borongan as $idx=>$row) { 
			#add to ready data================================================
			if($row>0) {
			$readyData[] = array(
			  'kodeorg'=>$param['kodeorg'],
			  'periodegaji'=>$param['periodegaji'],
			  'karyawanid'=>$idx,
			  'idkomponen'=>30,
			  'jumlah'=>$row,
			  'pengali' => 1,
			  'hk' => 0);
			}
		  }    
		}
		if(isset($potborongan)){
		  foreach ($potborongan as $idx => $row) {
			#add to ready data================================================
			if ($row > 0) {
			  $readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 48,
				'jumlah' => $row,
				'pengali' => 1,
				'hk'=>0);
			}
		  }
		}
		if(!empty($premikontanan))foreach($premikontanan as $idx=>$row) { 
		  #add to ready data================================================
		  if($row>0) {
			$readyData[] = array(
			  'kodeorg'=>$param['kodeorg'],
			  'periodegaji'=>$param['periodegaji'],
			  'karyawanid'=>$idx,
			  'idkomponen'=>43,
			  'jumlah'=>$row,
			  'pengali' => 1,
			  'hk' => 0);
		  }
		}      

		@$cpenalty=count($penalty);
		if($cpenalty>0){
			foreach(@$penalty as $idx=>$row) { 
			  #add to ready data================================================
			  if($row>0) {             
				$readyData[] = array(
				  'kodeorg'=>$param['kodeorg'],
				  'periodegaji'=>$param['periodegaji'],
				  'karyawanid'=>$idx,
				  'idkomponen'=>34,
				  'jumlah'=>$row,
				  'pengali' => 1,
				  'hk' => 0);
			  }
			} 
		}
				   
		#penalty kehadiran dari absensi
		$stkh="select a.karyawanid,sum(a.penaltykehadiran) as penaltykehadiran from ".$dbname.".sdm_absensidt a left join 
			  ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
			  where b.tipekaryawan in(1,2,6) and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   and b.lokasitugas='".$param['kodeorg']."' 
			  and (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and a.kodeorg like '".$param['kodeorg']."%' and sistemgaji='Bulanan'  
			  and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."' group by a.karyawanid";
		$reskh=$owlPDO->query($stkh) or die(print " Gagal: ".PDOException::getMessage());
		$reskh->setFetchMode(PDO::FETCH_OBJ);              
		while($barkh=$reskh->fetch()){
		  if($barkh->penaltykehadiran>0)
			 $penaltykehadiran[$barkh->karyawanid]=$barkh->penaltykehadiran;
		}
			  
		@$cpenaltykehadiran=count($penaltykehadiran);
		if($cpenaltykehadiran>0){ 
		  foreach(@$penaltykehadiran as $idx=>$row) { 
			#add to ready data================================================
			if($row>0) {             
			  $readyData[] = array(
			  'kodeorg'=>$param['kodeorg'],
			  'periodegaji'=>$param['periodegaji'],
			  'karyawanid'=>$idx,
			  'idkomponen'=>41,
			  'jumlah'=>$row,
			  'pengali' => 1,
			  'hk' => 0);
			}
		  } 
		}
			
		#7. premi : sdm_premi
		$str = "select a.karyawanid,a.premi,a.jenis from " . $dbname . ".sdm_premi a left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'
				where  b.tipekaryawan in(1,2,6) and b.lokasitugas='" . $param['kodeorg'] . "'  and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'  
				and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				and sistemgaji='Bulanan' and periode='" . $param['periodegaji'] . "' group by a.karyawanid,a.jenis";
		$res = fetchData($str);
		foreach ($res as $idx => $row) {
		  if (isset($id[$row['karyawanid']])) {
			  $readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $row['karyawanid'],
				'idkomponen' => $row['jenis'],
				'jumlah' => $row['premi'],
				'pengali' => 1,
				'hk'=>0);
		  } else {
			  //abaikan jika tidak terdaftar pada karyawanid  
		  }
		}

		$rapelgaji=array();
		$i="select * from ".$dbname.".sdm_pendapatanlaindt a where left(kodeorg,4)='" . $param['kodeorg'] . "' and a.periodegaji='".$param['periodegaji']."' "
			." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan_hist b where tipekaryawan in (1,2) and"
			." (tanggalkeluar>='".$tanggal1."' or tanggalkeluar='0000-00-00') and alokasi in ('0','1') and sistemgaji='Bulanan' and"
			." (tanggalmasuk<='".$tanggal2."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and b.periodegaji='".$param['periodegaji']."' and b.version_type='B'   ) and posting='1'";        
		$n = fetchData($i);
		foreach($n as $idx => $val){
			$readyData[] = array(
			 'kodeorg'=>$param['kodeorg'],
			 'periodegaji'=>$param['periodegaji'],
			 'karyawanid'=>$val['karyawanid'],
			 'idkomponen'=>$val['idkomponen'],
			 'jumlah'=>$val['jumlah'],
			 'pengali' => 1,
			 'hk' => 0);
			if($val['idkomponen']=='14')
			{
			$rapelgaji[$val['karyawanid']]=$val['jumlah'];  
			}
		}


		#######################################################################################################################
		###tambahan indra disini, memasukan bpjs kesehatan (jms) dan bpjs kesehatan
		##algoritma : jika kolom jms dan bpjs di datakaryawan_hist terisi maka akan memotong
		##jika tidak maka di kosongkan
		// if ($tipeOrg == 'PABRIK') {
		// 	$bpjsorg = 'PABRIK';
		// } else {
		// 	$bpjsorg = 'KEBUN';
		// }

		$bpjsorg=$tipeOrg;
		
		$sUmpDaerah="select distinct jumlah from ".$dbname.".sdm_5gajipokok where tahun='".substr($param['periodegaji'],0,4)."' and idkomponen='87' and kodeorg='".$param['kodeorg']."'";
		$rUmpDaerah=fetchData($sUmpDaerah);
		$umpDaerah=$rUmpDaerah[0]['jumlah'];#bpjs kesehatan


		#= parameter aplikasi 

		#= kerja
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSKER'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch(); 
		  $arrbpjs=explode(',',$bar['nilai']);
		  foreach($arrbpjs as $key){
			$arrker[]=$key;
		  }

		#= kesehatan
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSKES'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch(); 
		  $arrbpjs=explode(',',$bar['nilai']);
		  foreach($arrbpjs as $key){
			$arrkes[]=$key;
		  }
		  
		  
		#= pensiun
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSPEN'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch(); 
		  $arrbpjs=explode(',',$bar['nilai']);
		  foreach($arrbpjs as $key){
			$arrpen[]=$key;
		  }


		#= Ketenagakerjaan JKK
		$str = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='" . $bpjsorg . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
		  #= kerja
		  if(in_array($bar['jenisbpjs'],$arrker)){
			foreach ($umrbulanan as $key => $nilai) {
			  if ($bpjstenaga[$key] != '') {
				#Untuk JKK,JHT, dan JKM jika gaji pokok dibawah UMP (Daerah) maka menggunakan angka UMP. 
				#Jika lebih menggunakan gaji pokok, berlaku untuk payroll HO&Site
				if($nilai<$umpDaerah){
				  $nilai=$umpDaerah;
				}

					$bebankaryawan=$bar['bebankaryawan'];
					$bebanperusahaan=$bar['bebanperusahaan'];
					// echo substr($tmkjamsostek[$key],0,7);
					// echo 'xxxx';
					// exit('error');
					if(substr($tmkjamsostek[$key],0,7)==$param['periodegaji'])
					{
						$bebankaryawan=$bar['bebankaryawantpdiskon'];
						$bebanperusahaan=$bar['bebanperusahaantpdiskon'];
					}

				$readyData[] = array(
				  'kodeorg' => $lokasitugas[$key],
				  'periodegaji' => $param['periodegaji'],
				  'karyawanid' => $key,
				  'idkomponen' => $bar['jenisbpjs'],
				  'jumlah' => ($bebankaryawan / 100 * ($nilai+$rapelgaji[$key])),
				  'pengali' => 1,
				  'hk'=>0);
				  
				$readyData[] = array(
				  'kodeorg' => $lokasitugas[$key],
				  'periodegaji' => $param['periodegaji'],
				  'karyawanid' => $key,
				  'idkomponen' => $bar['jenisbpjsplus'],
				  'jumlah' => ($bebanperusahaan / 100 * ($nilai+$rapelgaji[$key])),
				  'pengali' => 1,
				  'hk'=>0); 
				  
			  }
			}
		  }
		  #= kesehatan
		  if(in_array($bar['jenisbpjs'],$arrkes)){
		  #= jika diparameter aplikasi diset 0 maka akan ambil dari gapok
			  foreach ($umrbulanan as $key => $nilai) {
				if ($bpjskes[$key] != '') {
				   #Untuk JKK,JHT, dan JKM jika gaji pokok dibawah UMP (Daerah) maka menggunakan angka UMP. 
				   #Jika lebih menggunakan gaji pokok, berlaku untuk payroll HO&Site
					if($nilai<$umpDaerah){
						$nilai=$umpDaerah;
					}
					$bebankaryawan=$bar['bebankaryawan'];
					$bebanperusahaan=$bar['bebanperusahaan'];
					if(substr($tmkjamsostek[$key],0,7)==$param['periodegaji'])
					{
						$bebankaryawan=$bar['bebankaryawantpdiskon'];
						$bebanperusahaan=$bar['bebanperusahaantpdiskon'];
					}

				  $readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => $param['periodegaji'],
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjs'],
					'jumlah' => ($bebankaryawan / 100 * ($nilai+$rapelgaji[$key]) * $bpjskestanggungan[$key]),
					'pengali' => 1,
					'hk'=>0);
				
				  $readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => $param['periodegaji'],
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjsplus'],
					'jumlah' => ($bebanperusahaan / 100 * ($nilai+$rapelgaji[$key])),
					'pengali' => 1,
					'hk'=>0);
				}
				
			  }
			
		  }
		  
		  #= pensiun
		  if(in_array($bar['jenisbpjs'],$arrpen)){
			foreach ($umrbulanan as $key => $nilai) {
			  if ($bpjspensiun[$key] != '') {
				#Untuk JP jika gaji pokok dibawah UMP (daerah) maka menggunakan UMP. Jika kurang dari Maks gunakan gaji pokok. 
				#Jika lebih dari maks maka gunakan maksimal gapok
				if($nilai<$umpDaerah){
					$nilai=$umpDaerah;
				}
				if($nilai>$bar['maxgaji']){
					$nilai=$bar['maxgaji'];
				}

					$bebankaryawan=$bar['bebankaryawan'];
					$bebanperusahaan=$bar['bebanperusahaan'];
					if(substr($tmkjamsostek[$key],0,7)==$param['periodegaji'])
					{
						$bebankaryawan=$bar['bebankaryawantpdiskon'];
						$bebanperusahaan=$bar['bebanperusahaantpdiskon'];
					}

				$readyData[] = array(
				  'kodeorg' => $lokasitugas[$key],
				  'periodegaji' => $param['periodegaji'],
				  'karyawanid' => $key,
				  'idkomponen' => $bar['jenisbpjs'],
				  'jumlah' => ($bebankaryawan / 100 * ($nilai+$rapelgaji[$key])),
				  'pengali' => 1,
				  'hk'=>0);
				  
				$readyData[] = array(
				  'kodeorg' => $lokasitugas[$key],
				  'periodegaji' => $param['periodegaji'],
				  'karyawanid' => $key,
				  'idkomponen' => $bar['jenisbpjsplus'],
				  'jumlah' => ($bebanperusahaan / 100 * ($nilai+$rapelgaji[$key])),
				  'pengali' => 1,
				  'hk'=>0); 
			  }
			}
		  }
		}

		###################################################################################################################################################
		###################################################################################################################################################
		###################################################################################################################################################

		// echo "<pre>";
		// print_r($readyData);
		// echo "<pre>";
					 
		 //calculate to component
			   $strx="select id as komponen, case plus when 0 then -1 else plus end as pengali,name as nakomp 
					  FROM ".$dbname.".sdm_ho_component";
			   $comRes = fetchData($strx); 
			   $comp=Array();
			   $nakomp=Array();
			   foreach($comRes as $idx=>$row){
				  $comp[$row['komponen']]=$row['pengali'];
				  $nakomp[$row['komponen']]=$row['nakomp'];
			   }       
			   
			   
		   //=tampilan  ============================
				   $listbutton="<button class=mybuttton name=postBtn id=postBtn onclick=post()>Proses</button>"; 
				   $list0 ="<table class=sortable border=0 cellspacing=1>
							 <thead>
							 <tr class=rowheader>";
					$list0 .= "<td>".$_SESSION['lang']['nomor']."</td>";
					$list0 .= "<td>".$_SESSION['lang']['periodegaji']."</td>";
					$list0 .= "<td>".$_SESSION['lang']['karyawanid']."</td>";
					$list0.= "<td>".$_SESSION['lang']['jumlah']."</td>";
		//            $list0.= "<td>PPh21</td>";
					$list0.="</tr></thead><tbody>";
					
		//periksa gaji minus
			$negatif=false; 
			$list1='';
			 $listx = "Masih ada gaji dibawah 0:";    
			$list2='';
			$list3='';
			$no=0;

		   if($readyData<1)
		   {
			   exit("Error:Data Kosong");
		   }
		   
			   foreach($id as $key=>$val){
				   $sisa[$val[0]]=0;
				   foreach($readyData as $dat=>$bar){
					  if($val[0]==$bar['karyawanid'])
					  {
						  $sisa[$val[0]]+=$bar['jumlah']*$comp[$bar['idkomponen']]; 
						  
							
					  }  
					  continue;
				   }
			   
								
				   if($sisa[$val[0]]<0)
				   {
						$list1 .="<tr class=rowcontent>";
						$list1 .= "<td>-</td>";
						$list1 .= "<td>".$param['periodegaji']."</td>";
						$list1 .= "<td>".$val[0]." ".$namakar[$val[0]]."</td>";
						$list1 .= "<td>***</td>";
						#$list1 .= "<td>".number_format($sisa[$val[0]],0,',','.')."</td>";
		//                $list1 .= "<td>".number_format($pph21[$val[0]],0,',','.')."</td>";
						$list1 .= "</tr>";                
						$negatif=true;                
				   } 
				   else
				   {
					   $no+=1; 
						$list2 .="<tr class=rowcontent>";
						$list2 .= "<td>".$no."</td>";
						$list2 .= "<td>".$param['periodegaji']."</td>";
						$list2 .= "<td>".$val[0]." ".$namakar[$val[0]]."</td>";
						$list2 .= "<td>***</td>";
						#$list2 .= "<td align=right>".number_format($sisa[$val[0]],0,',','.')."</td>";
		//                $list2 .= "<td align=right>".number_format($pph21[$val[0]],0,',','.')."</td>";
						$list2 .= "</tr>";  
				   }    
			   }
		  #est gaji gak perlu minus2an
		  if($method=='estgaji'){
			$negatif = false;
		  }
			
		$list3="</tbody><table>";   
}
switch($proses) {
    case 'list':
         if($negatif)
             echo $listx.$list0.$list1.$list3;
         else
             echo $listbutton.$list0.$list2.$list3;
         break;
    case 'post':
	try {
	$owlPDO->beginTransaction();
        # Insert All ready data
        if($dakarbulanan==0){
          $sdel="delete from ".$dbname.".sdm_gaji "
           . " where idkomponen not in ('26','28') "
            . " and periodegaji='".$param['periodegaji']."' and kodeorg='".$param['kodeorg']."' "
           . " and karyawanid in (select distinct karyawanid from ".$dbname.".datakaryawan "
            . " where sistemgaji='Bulanan' and lokasitugas='".$param['kodeorg']."')";
        }
        else
        {

          $sdel="delete from ".$dbname.".sdm_gaji "
           . " where idkomponen not in ('26','28') "
            . " and periodegaji='".$param['periodegaji']."' and kodeorg='".$param['kodeorg']."' "
           . " and karyawanid in (select distinct karyawanid from ".$dbname.".datakaryawan_hist "
            . " where sistemgaji='Bulanan' and lokasitugas='".$param['kodeorg']."' and periodegaji='".$param['periodegaji']."' and version_type='B'  )";
        }
        #delete dulu baru insert
        
        //exit("Error:$sdel");
        $owlPDO->exec($sdel);
		/* try{$owlPDO->exec($sdel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); } */
        
        
         $insError = "";
        foreach($readyData as $row) {
            if($row['jumlah']==0 or $row['jumlah']==''){
                continue;
            }else{
				#= delete lagi kalau tidak terhapus diatas	
				$str="delete from ".$dbname.".sdm_gaji where idkomponen='".$row['idkomponen']."' 
						and karyawanid='".$row['karyawanid']."' and periodegaji='".$row['periodegaji']."' ";
						// exit("Error:$str");
				$owlPDO->exec($str);
				/* try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				} */
				
				
                $queryIns = insertQuery($dbname,'sdm_gaji',$row);
				$owlPDO->exec($queryIns);
                /* try{
                  $test=$owlPDO->exec($queryIns);
                }
                catch (PDOException $e) {
                    try{$owlPDO->exec($queryUpd); }
                    catch (PDOException $e) {
                       echo "DB Update Error : " . $e->getMessage() . "\n"; die(); 
                    } */
				$queryUpd = updateQuery($dbname,'sdm_gaji',$row,
					"kodeorg='".$row['kodeorg'].
					"' and periodegaji='".$row['periodegaji'].
					"' and karyawanid='".$row['karyawanid'].
					"' and idkomponen=".$row['idkomponen']);
				$owlPDO->exec($queryUpd);
                
            }  
        }
		
		
		#= delete 
		
		/* try {
			$owlPDO->exec($sql);
			
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		} */
			
		$qPeriod=selectQuery($dbname,'sdm_5periodegaji','tanggalmulai,tanggalsampai', "periode='".$param['periodegaji']." ' 
          and kodeorg='".$param['kodeorg']."' and jenisgaji='B'");
		$resPeriod = fetchData($qPeriod);
		$tanggal1 = $resPeriod[0]['tanggalmulai'];
		$tanggal2 = $resPeriod[0]['tanggalsampai'];
		
		#= eksekusi datakaryawan site tsb
		$str="select * from ".$dbname.".datakaryawan where lokasitugas='".$param['kodeorg']."' and tanggalmasuk<='".$tanggal2."' and tanggalsk<='".$tanggal2."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

      $dakarbulananx=0;
      $strx = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$param['kodeorg']."' and periodegaji='".$param['periodegaji']."' and karyawanid='".$bar['karyawanid']."' "; 
      $resx = fetchdata($strx);
      if(count($resx)>0)
      { 
        $dakarbulananx=1;
      }

      if($dakarbulananx==0 and $method!='estgaji')
      {
        $strxhist="insert into ".$dbname.".datakaryawan_hist(
          `nik`,`namakaryawan`,`karyawanid`,
          `tempatlahir`,`tanggallahir`,
          `warganegara`,`jeniskelamin`,
          `statusperkawinan`,`tanggalmenikah`,
          `agama`,`golongandarah`,
          `levelpendidikan`,`alamataktif`,
          `provinsi`,`kota`,`kodepos`,
          `noteleponrumah`,`nohp`,`nohp2`,
          `norekeningbank`,`namabank`,
          `sistemgaji`,`no_keluarga`,
          `noktp`,`notelepondarurat`,
          `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
          `tipekaryawan`,`jumlahanak`,
          `jumlahtanggungan`,`statuspajak`,
          `npwp`,`kppnpwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
          `bagian`,`subdept`,`kodejabatan`,`kodegolongan`,`pensiun`,
          `lokasitugas`,`email`,`emailkantor`,`alokasi`,`subbagian`,`jms`,kodecatu,statpremi,suku,statuskaryawan,sim,updateby,insstatuspajak,supbpjs,updatetime,approval_status,periodegaji,version_type,datachange,version,periodeakhirgaji)
        values('".$bar['nik']."','".$bar['namakaryawan']."','".$bar['karyawanid']."',
          '".$bar['tempatlahir']."','".$bar['tanggallahir']."',
          '".$bar['warganegara']."','".$bar['jeniskelamin']."',
          '".$bar['statusperkawinan']."','".$bar['tanggalmenikah']."',
          '".$bar['agama']."','".$bar['golongandarah']."',
          '".$bar['levelpendidikan']."','".$bar['alamataktif']."',
          '".$bar['provinsi']."','".$bar['kota']."','".$bar['kodepos']."',
          '".$bar['noteleponrumah']."','".$bar['nohp']."','".$bar['nohp2']."',
          '".$bar['norekeningbank']."','".$bar['namabank']."',
          '".$bar['sistemgaji']."','".$bar['no_keluarga']."',
          '".$bar['noktp']."','".$bar['notelepondarurat']."',
          '".$bar['tanggalmasuk']."','".$bar['tanggalpengangkatan']."','".$bar['tanggalkeluar']."',
          '".$bar['tipekaryawan']."','".$bar['jumlahanak']."',
          '".$bar['jumlahtanggungan']."','".$bar['statuspajak']."',
          '".$bar['npwp']."','".$bar['kppnpwp']."','".$bar['bpjs']."','".$bar['lokasipenerimaan']."','".$bar['kodeorganisasi']."',
          '".$bar['bagian']."','".$bar['subdept']."','".$bar['kodejabatan']."','".$bar['kodegolongan']."','".$bar['pensiun']."',
          '".$bar['lokasitugas']."','".$bar['email']."','".$bar['emailkantor']."','".$bar['alokasi']."','".$bar['subbagian']."','".$bar['jms']."','".$bar['kodecatu']."','".$bar['statpremi']."','".$bar['suku']."','".$bar['statuskaryawan']."','".$bar['sim']."','".$bar['updateby']."','".$bar['insstatuspajak']."','".$bar['supbpjs']."','".date('Y-m-d')."','8','".$param['periodegaji']."','B','','1','".$bar['periodeakhirgaji']."')";
      $owlPDO->exec($strxhist);

      }
      
        $sqlxa="delete from ".$dbname.".datakaryawan_bulanan where periode='".$param['periodegaji']."' and karyawanid='".$bar['karyawanid']."'";
      $owlPDO->exec($sqlxa);
      
			$sql="insert into ".$dbname.".datakaryawan_bulanan(
			  `karyawanid`,`nik`,`namakaryawan`,
			  `tempatlahir`,`tanggallahir`,
			  `warganegara`,`jeniskelamin`,
			  `statusperkawinan`,`tanggalmenikah`,
			  `agama`,`golongandarah`,
			  `levelpendidikan`,`alamataktif`,
			  `provinsi`,`kota`,`kodepos`,
			  `noteleponrumah`,`nohp`,
			  `norekeningbank`,`namabank`,
			  `sistemgaji`,`no_keluarga`,
			  `noktp`,`notelepondarurat`,
			  `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
			  `tipekaryawan`,`jumlahanak`,
			  `jumlahtanggungan`,`statuspajak`,
			  `npwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
			  `bagian`,`kodejabatan`,`kodegolongan`,`pensiun`,
			  `lokasitugas`,`email`,`alokasi`,`subbagian`,`jms`,kodecatu,statpremi,suku,statuskaryawan,sim,updateby,periode)
			   values('".$bar['karyawanid']."','".$bar['nik']."','".$bar['namakaryawan']."','".$bar['tempatlahir']."','".$bar['tanggallahir']."','".$bar['warganegara']."','".$bar['jeniskelamin']."',
			'".$bar['statusperkawinan']."','".$bar['tanggalmenikah']."','".$bar['agama']."','".$bar['golongandarah']."','".$bar['levelpendidikan']."',
			'".$bar['alamataktif']."','".$bar['provinsi']."','".$bar['kota']."','".$bar['kodepos']."','".$bar['noteleponrumah']."','".$bar['nohp']."',
			'".$bar['norekeningbank']."','".$bar['namabank']."','".$bar['sistemgaji']."','".$bar['no_keluarga']."','".$bar['noktp']."',
			'".$bar['notelepondarurat']."','".$bar['tanggalmasuk']."','".$bar['tanggalpengangkatan']."','".$bar['tanggalkeluar']."','".$bar['tipekaryawan']."',
			'".$bar['jumlahanak']."','".$bar['jumlahtanggungan']."','".$bar['statuspajak']."','".$bar['npwp']."','".$bar['bpjs']."','".$bar['lokasipenerimaan']."',
			'".$bar['kodeorganisasi']."','".$bar['bagian']."','".$bar['kodejabatan']."','".$bar['kodegolongan']."','".$bar['pensiun']."',
			'".$bar['lokasitugas']."','".$bar['email']."','".$bar['alokasi']."','".$bar['subbagian']."','".$bar['jms']."','".$bar['kodecatu']."',
			'".$bar['statpremi']."','".$bar['suku']."','".$bar['statuskaryawan']."','".$bar['sim']."','".$bar['updateby']."','".$param['periodegaji']."')";
			
			$owlPDO->exec($sql);
			/* 
			try {
				$owlPDO->exec($sql);
				
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			} */
		}
		
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, datakaryawan bulanan " . addslashes($e->getMessage());
		die();
	}	
		
		
	break;
    default:
	break;
}
?>