<?
// ini_set('display_errors',0);
// error_reporting(0);
session_start();
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	if($validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
	$session_id = $_SESSION['standard']['userid'];
}else{
	if(!empty($_POST['namafile']) || !empty($_GET['namafile'])){		
		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	}
}


require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
if(count($_POST)>0){$param=$_POST;}else{$param=$_GET;}

$jab = getPostingJabatan('datakaryawan');
$arrstatusapp=array('0'=>'Belum Diajukan','9'=>'Tahap Pesetujuan','1'=>'Disetujui','2'=>'Ditolak');
$arrpersetujuan=array('0'=>'Belum Diproses','9'=>'Tahap Pesetujuan','1'=>'Disetujui','2'=>'Ditolak','8'=>'Posted','7'=>'Not Posted');
$arrtipeversion=array('C'=>'Update Data','N'=>'Data Baru','B'=>'Proses Bulanan');
$arralokasibiaya=array('0'=>'Unit','1'=>'Umum');
$namakab['LOKAL']="LOKAL";


$str = "select *  from " . $dbname . ".keu_5daftarbank";
$res = fetchdata($str);
foreach($res as $bar){
	$nmbank[$bar['kodebank']]=$bar['namabank'];
}

if($param['method']=='history'){
	$karyawanid=$param['karyawanid'];
    $str="select a.namaorganisasi, b.statuskaryawan from ".$dbname.".datakaryawan_hist b left join ".$dbname.".organisasi a on b.kodeorganisasi=a.kodeorganisasi where b.karyawanid='".$karyawanid."' and b.nourut='".$param['nourut']."' ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $namapt=$bar->namaorganisasi;
        $statuskaryawan=$bar->statuskaryawan;
    }

	$str="SHOW COLUMNS FROM ".$dbname.".datakaryawan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$arrdtc=array();
	while($bar=$res->fetch()){
		$arrdtc[$bar->Field]='#EDEDED';
	}

	//nama negara
	$str="select * from ".$dbname.".datakaryawan_hist where karyawanid='".$karyawanid."' and nourut='".$_POST['nourut']."'";
	$res=fetchdata($str);
	$country=readCountry("./config/country.lst");
	for($x=0;$x<count($country);$x++){
		if($res[0]['warganegara']==$country[$x][2]){
			$vcountry=$country[$x][0];
		}
	}


	//nama provinsi
	$str="select * from ".$dbname.".datakaryawan_hist where karyawanid='".$karyawanid."' and nourut='".$_POST['nourut']."'";
	$res=fetchdata($str);
	$country=readCountry("./config/provinsi.lst");
	for($x=0;$x<count($country);$x++){
		if($res[0]['provinsi']==$country[$x][1]){
			$vcprovince=$country[$x][0];
		}
	}
	
	$namaprov=makeOption($dbname,'provinsi','id,provinsi');
	$namakab=makeOption($dbname,'kabupaten','id,kabupaten');
	$namakec=makeOption($dbname,'kecamatan','idkec,kecamatan',"idkec='".$res[0]['kecamatan']."'");
	$namades=makeOption($dbname,'desa','iddes,desa',"iddes='".$res[0]['desa']."'");
	
	if($vcprovince==''){
		$vcprovince=$namaprov[$res[0]['provinsi']];
	}

	$nmsuku=makeOption($dbname,'sdm_5suku','idsuku,namasuku');
	$str="select *,
		case jeniskelamin when 'L' then 'Laki-Laki'
		else  'Wanita'
		end as jk
		from ".$dbname.".datakaryawan_hist where karyawanid='".$karyawanid ."' and nourut='".$_POST['nourut']."' limit 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$defaulsrc='images/user.png';


	$tab="<div><table class=standard cellspacing=1 width=100% bgcolor=#A3D988 cellpadding=5>";
	$optkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
	while($bar=$res->fetch()){
		$arr1x=explode('###', $bar->datachange);
		foreach ($arr1x as $key => $val) {
			if($val!=''){
				$arrdtc[$val]='red';
			}
		}

	  
		$tab.="<b>*Perubahan data ditandai dengan warna merah</b>";
	  
		//get pendidikan
		$pendidikan='';
		$str1="select kelompok from ".$dbname.".sdm_5pendidikan where levelpendidikan=".$bar->levelpendidikan;
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while($bar1=$res1->fetch()){
			$pendidikan=$bar1->kelompok;
		}
		
		//Tipe karyawan
		$tipekaryawan='';
		$str2="select * from ".$dbname.".sdm_5tipekaryawan where id=".$bar->tipekaryawan;   
		$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
		$res2->setFetchMode(PDO::FETCH_OBJ);
		while($bar2=$res2->fetch()){
			$tipekaryawan=$bar2->tipe;
		} 

		//jabatan
		$jabatan='';
		$str3="select * from ".$dbname.".sdm_5jabatan where kodejabatan=".$bar->kodejabatan." and namajabatan not like '%available' order by kodejabatan";
		$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
		$res3->setFetchMode(PDO::FETCH_OBJ);
		while($bar3=$res3->fetch()){
			$jabatan=$bar3->namajabatan;
		} 
		$jabatanku=$bar->kodejabatan;
		if($bar->statusakad=='0'){
			$txtAkad='-';
		}else if($bar->statusakad=='1'){
			$txtAkad='akad';
		}else{
			$txtAkad='non-akad';
		}
		
		if($bar->tipekaryawan=='4'){
			$jenispersetujuanx='DTK1';
		}elseif($bar->tipekaryawan=='0'){
			$jenispersetujuanx='DTK3';
		}else{
			$jenispersetujuanx='DTK2';
		}

		if($bar->approval_status!='B'){
			$strapp="select * from ".$dbname.".approval where notransaksi='".$bar->nourut."' and jenispersetujuan='".$jenispersetujuanx."' order by level asc ";   
			$textapp='';
			$resapp=$owlPDO->query($strapp) or die(print " Gagal: ".PDOException::getMessage());
			$jlhlevel=$resapp->rowCount();
			$resapp->setFetchMode(PDO::FETCH_ASSOC);
			if($jlhlevel>0){
				$nox=0;
				while($barapp=$resapp->fetch()){
				$nox+=1;
				$textapp.="
				<tr class=rowcontent>
					<td align=right>Status Persetujuan</td>
					<td align=left style=background-color:#EDEDED;><b>".$arrstatusapp[$bar->approval_status]."</b></td>
					<td align=right>Diajukan Oleh</td>
					<td align=left style=background-color:#EDEDED;><b>".$optkary[$bar->diajukan]."</b></td>
				</tr>
				<tr class=rowcontent>
					<td align=right>Approval Ke-".$nox."</td>
					<td align=left style=background-color:#EDEDED;>".$optkary[$barapp['karyawanid']]."</td>
					<td align=left style=background-color:#EDEDED;>".$arrpersetujuan[$barapp['status']]."</td>
					<td align=left style=background-color:#EDEDED;>".$barapp['komentar']."</td>
				</tr>";
				}

			}else{
			  $textapp.="
				<tr class=rowcontent>
					<td align=right>Status Persetujuan</td>
					<td align=left style=background-color:#EDEDED;><b>".$arrstatusapp[$bar->approval_status]."</b></td>
					<td align=right>Diajukan Oleh</td>
					<td align=left style=background-color:#EDEDED;><b>".$optkary[$bar->diajukan]."</b></td>
				</tr>
				<tr class=rowcontent>
					<td colspan=4 align=center>Approval Data Not Found</td>
				</tr>";
			}
		}else{
			$textapp.="
			<tr class=rowcontent>
				<td align=right>Status</td>
				<td align=left style=background-color:#EDEDED;><b>".$arrstatusapp[$bar->approval_status]."</b></td>
				<td align=right>Diposting Oleh</td>
				<td align=left style=background-color:#EDEDED;><b>".$optkary[$bar->diajukan]."</b></td>
			</tr>
			<tr class=rowcontent>
				<td colspan=4 align=center></td>
			</tr>";
		}



		$date1 = $bar->tanggalmasuk;
		$date2 = date('Y-m-d');
		$diff  = abs(strtotime($date2) - strtotime($date1));             
		$years = floor($diff / (365*60*60*24)); 
		$months= floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 
		$days  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));         
		$lamaKerja=" ".$years." Tahun ".$months." Bulan ".$days." Hari ";
		$arrlokasitugas=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

		$optKetStatusPajak= makeOption($dbname,'sdm_5statuspajak','inisial,nama',"inisial='".$bar->insstatuspajak."'");
		$nmGolongan       = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$bar->kodegolongan."'");
		$nmDepartemen     = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar->subdept."'");
		$nmLevelKaryawan     = makeOption($dbname,'sdm_5levelkaryawan','kode,nama',"kode='".$bar->levelkaryawan."'");

		$nmBagian         = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar->bagian."'");
		@$suku=$nmsuku[$bar->suku];
		
		if($bar->photo!=''){
			$foto="photokaryawan/".$bar->photo;
		}else{
			$foto=$defaulsrc;
		}
		$tab.="
		<tr>
			<td colspan=4 align=center>
			   <img src='".$foto."' style='height:150px;'>
			</td>
		</tr>
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>VERSION</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>Version</td>
			<td align=left style=background-color:#EDEDED;><b>".$bar->version."</b></td>

			<td align=right >Version Type</td>
			<td align=left style=background-color:#EDEDED;><b>".$arrtipeversion[$bar->version_type]."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right >Periode Gaji</td>
			<td align=left style=background-color:#EDEDED;><b>".$bar->periodegaji."</b></td>

			<td align=right >Update Time</td>
			<td align=left style=background-color:#EDEDED;><b>".tanggalnormal($bar->updatetime)."</b></td>
		</tr>
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>PERSETUJUAN</td>
		</tr>
		".$textapp."
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>DATA PRIBADI</td>
		</tr>
		<tr class=rowcontent>
			<td align=right >".$_SESSION['lang']['nik']."</td><td align=left bgcolor=".$arrdtc['nik']."><b>".$bar->nik."</b></td>
			<td align=right>".$_SESSION['lang']['namakaryawan']."</td><td align=left bgcolor=".$arrdtc['namakaryawan']."><b>".$bar->namakaryawan."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['tempatlahir']."</td><td align=left bgcolor=".$arrdtc['tempatlahir']."><b>".$bar->tempatlahir."</b></td>
			<td align=right>".$_SESSION['lang']['tanggallahir']."</td><td align=left bgcolor=".$arrdtc['tanggallahir']."><b>".tanggalnormal($bar->tanggallahir)."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['jeniskelamin']."</td><td align=left bgcolor=".$arrdtc['jeniskelamin']."><b>".$bar->jk."</b></td>
			<td align=right>".$_SESSION['lang']['warganegara']."</td><td align=left bgcolor=".$arrdtc['warganegara']."><b>".$vcountry."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['noktp']."</td><td align=left bgcolor=".$arrdtc['noktp']."><b>".$bar->noktp."</b></td>
			<td align=right>".$_SESSION['lang']['passport']."</td><td align=left bgcolor=".$arrdtc['no_keluarga']."><b>".$bar->no_keluarga."</b></td>
			
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['province']."</td><td align=left bgcolor=".$arrdtc['provinsi']."><b>".$vcprovince."</b></td>
			<td align=right>".$_SESSION['lang']['kabupaten']."/".$_SESSION['lang']['kota']."</td><td align=left bgcolor=".$arrdtc['kabupaten']."><b>".$namakab[$bar->kabupaten]."</b></td>
		</tr>	
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['kecamatan']."</td><td align=left bgcolor=".$arrdtc['kecamatan']."><b>".$namakec[$bar->kecamatan]."</b></td>
			<td align=right>".$_SESSION['lang']['desa']."/".$_SESSION['lang']['kelurahan']."</td><td align=left bgcolor=".$arrdtc['desa']."><b>".$namades[$bar->desa]."</b></td>
		</tr>	
		<tr class=rowcontent>
			<td align=right valign=top>Alamat KTP</td><td align=left bgcolor=".$arrdtc['alamataktif']." valign=top><b>".$bar->alamataktif."</b></td>
			<td align=right>".$_SESSION['lang']['kodepos']."</td><td align=left bgcolor=".$arrdtc['kodepos']."><b>".$bar->kodepos."</b></td>
		</tr>
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>LOKASI KERJA DAN STATUS KARYAWAN</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['kodeorganisasi']."</td><td align=left bgcolor=".$arrdtc['kodeorganisasi']."><b>".$bar->kodeorganisasi."</b></td>
			<td align=right>".$_SESSION['lang']['lokasitugas']."</td><td align=left bgcolor=".$arrdtc['lokasitugas']."><b>".$bar->lokasitugas." - ".$arrlokasitugas[$bar->lokasitugas]."</b></td>
		</tr>                               
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['divisi']."</td><td align=left bgcolor=".$arrdtc['subbagian']."><b>".$bar->subbagian."</b></td>
			
			<td align=right>".$_SESSION['lang']['departemen']."</td><td align=left bgcolor=".$arrdtc['bagian']."><b>".$nmBagian[$bar->bagian]."</b></td>        
		</tr>
		<tr class=rowcontent>
			<td align=right>Level ".$_SESSION['lang']['karyawan']."</td><td align=left bgcolor=".$arrdtc['levelkaryawan']."><b>".$nmLevelKaryawan[$bar->levelkaryawan]."</b></td>
			<td align=right>".$_SESSION['lang']['functionname']."</td><td align=left bgcolor=".$arrdtc['kodejabatan']."><b>".$jabatan."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['tipekaryawan']."</td><td align=left bgcolor=".$arrdtc['tipekaryawan']."><b>".$tipekaryawan."</b></td>
			<td align=right>".$_SESSION['lang']['kodegolongan']."</td><td align=left bgcolor=".$arrdtc['kodegolongan']."><b>".$nmGolongan[$bar->kodegolongan]."</b></td>
			
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['status']." ".$_SESSION['lang']['karyawan']."</td><td align=left bgcolor=".$arrdtc['statuskaryawan']."><b>".$statuskaryawan."</b></td>    
			<td align=right>".$_SESSION['lang']['tanggalmasuk']."</td><td align=left bgcolor=".$arrdtc['tanggalmasuk']."><b>".tanggalnormal($bar->tanggalmasuk)."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['tanggalpengangkatan']." Pertama</td><td align=left bgcolor=".$arrdtc['tanggalpengangkatan']."><b>".tanggalnormal($bar->tanggalpengangkatan)."</b></td>
			<td align=right>".$_SESSION['lang']['tanggalkeluar']."</td><td align=left bgcolor=".$arrdtc['tanggalkeluar']."><b>".tanggalnormal($bar->tanggalkeluar)."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>Periode Gaji Terakhir</td><td align=left bgcolor=".$arrdtc['periodeakhirgaji']."><b>".$bar->periodeakhirgaji."</b></td>
			<td align=right>Masa Kerja</td><td align=left bgcolor=EDEDED><b>".$lamaKerja."<b></td>
			
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['sistemgaji']."</td><td align=left bgcolor=".$arrdtc['sistemgaji']."><b>".$bar->sistemgaji."</b></td>
			<td align=right>".$_SESSION['lang']['statuspajak']."</td><td align=left bgcolor=".$arrdtc['insstatuspajak']."><b>".$optKetStatusPajak[$bar->insstatuspajak]."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['lokasipenerimaan']."</td><td align=left bgcolor=".$arrdtc['lokasipenerimaan']."><b>".$namaprov[$bar->lokasipenerimaan]."</b></td>
			<td align=right>".$_SESSION['lang']['alokasibiaya']."</td><td align=left bgcolor=".$arrdtc['alokasi']."><b>".$arralokasibiaya[$bar->alokasi]."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['tanggalpengangkatan']." Non Staff</td><td align=left bgcolor=".$arrdtc['tanggalpengangkatannonstaff']."><b>".tanggalnormal($bar->tanggalpengangkatannonstaff)."</b></td>
			<td align=right></td><td></td>
		</tr>  
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>DETAIL KARYAWAN</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['telp']."</td><td align=left bgcolor=".$arrdtc['noteleponrumah']."><b>".$bar->noteleponrumah."</b></td>
			<td align=right>".$_SESSION['lang']['nohp']." (1)</td><td align=left bgcolor=".$arrdtc['nohp']."><b>".$bar->nohp."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['nohp']." (2)</td><td align=left bgcolor=".$arrdtc['nohp2']."><b>".$bar->nohp2."</b></td>
			<td align=right>".$_SESSION['lang']['notelepondarurat']."</td><td align=left bgcolor=".$arrdtc['notelepondarurat']."><b>".$bar->notelepondarurat."</b></td>
		</tr> 
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['agama']."</td><td align=left bgcolor=".$arrdtc['agama']."><b>".$bar->agama."</b></td>
			<td align=right>".$_SESSION['lang']['suku']."</td><td align=left bgcolor=".$arrdtc['suku']."><b>".$suku."</b></td>    
		</tr>
		
		
		<tr class=rowcontent>	
			<td align=right>".$_SESSION['lang']['statusperkawinan']."</td><td align=left bgcolor=".$arrdtc['statusperkawinan']."><b>".$bar->statusperkawinan."</b></td>
			<td align=right>".$_SESSION['lang']['tanggalmenikah']."</td><td align=left bgcolor=".$arrdtc['tanggalmenikah']."><b>".tanggalnormal($bar->tanggalmenikah)."</b></td>
		</tr>    
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['jumlahanak']."</td><td align=left bgcolor=".$arrdtc['jumlahanak']."><b>".$bar->jumlahanak."</b></td>
			<td align=right>".$_SESSION['lang']['levelpendidikan']."</td><td align=left bgcolor=".$arrdtc['levelpendidikan']."><b>".$pendidikan."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right >Nomor SIM</td><td align=left bgcolor=".$arrdtc['sim']."><b>".$bar->sim."</b></td>
			<td align=right>".$_SESSION['lang']['golongandarah']."</td><td align=left bgcolor=".$arrdtc['golongandarah']."><b>".$bar->golongandarah."</b></td>
		</tr>          
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['npwp']."</td><td align=left bgcolor=".$arrdtc['npwp']."><b>".$bar->npwp."</b></td>
			<td align=right>KPP Perusahaan</td><td align=left bgcolor=".$arrdtc['kppnpwp']."><b>".$bar->kppnpwp."</b></td>
		</tr> 
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['email']." Pribadi</td><td align=left bgcolor=".$arrdtc['email']."><b>".$bar->email."</b></td>
			<td align=right>".$_SESSION['lang']['email']." ".$_SESSION['lang']['kantor']."</td><td align=left bgcolor=".$arrdtc['emailkantor']."><b>".$bar->emailkantor."</b></td>
		</tr> 
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>REKENING</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['namabank']."</td><td align=left bgcolor=".$arrdtc['namabank']."><b>".$nmbank[$bar->namabank]."</b></td>
			<td align=right>".$_SESSION['lang']['norekeningbank']."</td><td align=left bgcolor=".$arrdtc['norekeningbank']."><b>".$bar->norekeningbank."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>A/N Rekening</td><td align=left bgcolor=".$arrdtc['pemilikrekening']."><b>".$bar->pemilikrekening."</b></td>
			<td align=right></td><td align=left bgcolor=".$arrdtc['pemilikrekening']."></td>
		</tr>
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>BPJS</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['jms']."</td><td align=left bgcolor=".$arrdtc['jms']."><b>".$bar->jms."</b></td>
			<td align=right>".$_SESSION['lang']['bpjs']." (".$_SESSION['lang']['kesehatan'].")</td><td align=left bgcolor=".$arrdtc['bpjs']."><b>".$bar->bpjs."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['bpjs']." Pensiun</td><td align=left bgcolor=".$arrdtc['pensiun']."><b>".$bar->pensiun."</b></td>
			<td align=right>".$_SESSION['lang']['jumlahtanggunganbpjs']."</td><td align=left bgcolor=".$arrdtc['jumlahtanggungan']."><b>".$bar->jumlahtanggungan."</b></td>
		</tr>";
	}
	   
	$tab.="</table>";
	$tab.="</div>";	 

	if($param['tampilan']=='PDF'){
		$dompdf = new Dompdf();
		$dompdf->load_html($tab);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$canvas = $dompdf->get_canvas();

		$filepdf=$param['namafile'];
		if (file_exists($filepdf)){
			unlink($filepdf);
		}
		file_put_contents($filepdf, $dompdf->output());
	}else{			
		echo $tab;
	}	 
}else{
	
#non history	
$karyawanid=$param['karyawanid'];

$str="select a.namaorganisasi, b.statuskaryawan from ".$dbname.".datakaryawan b left join ".$dbname.".organisasi a 
          on b.kodeorganisasi=a.kodeorganisasi where b.karyawanid=".$karyawanid;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $namapt=$bar->namaorganisasi;
    $statuskaryawan=$bar->statuskaryawan;
}

//nama negara
$str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
$res=fetchdata($str);
$country=readCountry("./config/country.lst");
for($x=0;$x<count($country);$x++){
	if($res[0]['warganegara']==$country[$x][2]){
		$vcountry=$country[$x][0];
	}
}


//nama provinsi
$str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
$res=fetchdata($str);
$country=readCountry("./config/provinsi.lst");
for($x=0;$x<count($country);$x++){
	if($res[0]['provinsi']==$country[$x][1]){
		$vcprovince=$country[$x][0];
	}
}

$namaprov=makeOption($dbname,'provinsi','id,provinsi');
$namakab=makeOption($dbname,'kabupaten','id,kabupaten');
$namakec=makeOption($dbname,'kecamatan','idkec,kecamatan',"idkec='".$res[0]['kecamatan']."'");
$namades=makeOption($dbname,'desa','iddes,desa',"iddes='".$res[0]['desa']."'");
$namakab['LOKAL']="LOKAL";

if($vcprovince==''){
	$vcprovince=$namaprov[$res[0]['provinsi']];
}

$str="SHOW COLUMNS FROM ".$dbname.".datakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$arrdtc=array();
while($bar=$res->fetch()){
	$arrdtc[$bar->Field]='#EDEDED';
}

$nmsuku=makeOption($dbname,'sdm_5suku','idsuku,namasuku');

$str="select *,
      case jeniskelamin when 'L' then 'Laki-Laki'
          else  'Wanita'
          end as jk
          from ".$dbname.".datakaryawan where karyawanid=".$karyawanid ." limit 1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$defaulsrc='images/user.png';

if($param['tampilan']=='PDF'){	
	$tab="<div>
		 <fieldset><legend>".$_SESSION['lang']['datapribadi']."</legend>
		 <table class=standard cellspacing=1 border=0 width=100% cellpadding=5 style=text-align:center>";
}else{
	$tab="<div>
		 <fieldset><legend>".$_SESSION['lang']['datapribadi']."</legend>
		 <table class=standard cellspacing=1 width=100% bgcolor=#A3D988 cellpadding=5 style=text-align:center>";
}
while($bar=$res->fetch()){
        $optKetStatusPajak    = makeOption($dbname,'sdm_5statuspajak','inisial,nama',"inisial='".$bar->insstatuspajak."'");
        $nmGolongan           = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$bar->kodegolongan."'");
        $nmDepartemen         = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar->subdept."'");
        $nmBagian             = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar->bagian."'");
		$nmLevelKaryawan     = makeOption($dbname,'sdm_5levelkaryawan','kode,nama',"kode='".$bar->levelkaryawan."'");

        @$suku                = $nmsuku[$bar->suku];
  
        //get pendidikan
        $pendidikan='';
        $str1="select kelompok from ".$dbname.".sdm_5pendidikan where levelpendidikan=".$bar->levelpendidikan;
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch())
        {$pendidikan=$bar1->kelompok;}

        //Tipe karyawan
        $tipekaryawan='';
        $str2="select * from ".$dbname.".sdm_5tipekaryawan where id=".$bar->tipekaryawan;   
        $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
        $res2->setFetchMode(PDO::FETCH_OBJ);
        while($bar2=$res2->fetch())
        {$tipekaryawan=$bar2->tipe;}

        //jabatan
        $jabatan='';
        $str3="select * from ".$dbname.".sdm_5jabatan where kodejabatan=".$bar->kodejabatan." and namajabatan not like '%available' order by kodejabatan";
        $res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
        $res3->setFetchMode(PDO::FETCH_OBJ);
        while($bar3=$res3->fetch())
        {$jabatan=$bar3->namajabatan;} 
        $jabatanku=$bar->kodejabatan;
            if($bar->statusakad=='0'){
                    $txtAkad='-';
            }else if($bar->statusakad=='1'){
                    $txtAkad='akad';
            }else{
                    $txtAkad='non-akad';
            }
                
        $date1=$bar->tanggalmasuk;
        $date2=date('Y-m-d');
        $diff = abs(strtotime($date2) - strtotime($date1));             
        $years = floor($diff / (365*60*60*24)); 
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));         
        $lamaKerja=" ".$years." Tahun ".$months." Bulan ".$days." Hari ";
        $arrlokasitugas=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		
		if($bar->photo!=''){
			$foto="photokaryawan/".$bar->photo;
		}else{
			$foto=$defaulsrc;
		}

        $tab.="
		<tr>
			<td colspan=4 align=center>
			   <img src='".$foto."' style='height:150px;'>
			</td>
		</tr>
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>DATA PRIBADI</td>
		</tr>
		<tr class=rowcontent>
			<td align=right >".$_SESSION['lang']['nik']."</td><td align=left bgcolor=".$arrdtc['nik']."><b>".$bar->nik."</b></td>
			<td align=right>".$_SESSION['lang']['namakaryawan']."</td><td align=left bgcolor=".$arrdtc['namakaryawan']."><b>".$bar->namakaryawan."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['tempatlahir']."</td><td align=left bgcolor=".$arrdtc['tempatlahir']."><b>".$bar->tempatlahir."</b></td>
			<td align=right>".$_SESSION['lang']['tanggallahir']."</td><td align=left bgcolor=".$arrdtc['tanggallahir']."><b>".tanggalnormal($bar->tanggallahir)."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['jeniskelamin']."</td><td align=left bgcolor=".$arrdtc['jeniskelamin']."><b>".$bar->jk."</b></td>
			<td align=right>".$_SESSION['lang']['warganegara']."</td><td align=left bgcolor=".$arrdtc['warganegara']."><b>".$vcountry."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['noktp']."</td><td align=left bgcolor=".$arrdtc['noktp']."><b>".$bar->noktp."</b></td>
			<td align=right>".$_SESSION['lang']['passport']."</td><td align=left bgcolor=".$arrdtc['no_keluarga']."><b>".$bar->no_keluarga."</b></td>
			
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['province']."</td><td align=left bgcolor=".$arrdtc['provinsi']."><b>".$vcprovince."</b></td>
			<td align=right>".$_SESSION['lang']['kabupaten']."/".$_SESSION['lang']['kota']."</td><td align=left bgcolor=".$arrdtc['kabupaten']."><b>".$namakab[$bar->kabupaten]."</b></td>
		</tr>	
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['kecamatan']."</td><td align=left bgcolor=".$arrdtc['kecamatan']."><b>".$namakec[$bar->kecamatan]."</b></td>
			<td align=right>".$_SESSION['lang']['desa']."/".$_SESSION['lang']['kelurahan']."</td><td align=left bgcolor=".$arrdtc['desa']."><b>".$namades[$bar->desa]."</b></td>
		</tr>	
		<tr class=rowcontent>
			<td align=right valign=top>Alamat KTP</td><td align=left bgcolor=".$arrdtc['alamataktif']." valign=top><b>".$bar->alamataktif."</b></td>
			<td align=right>".$_SESSION['lang']['kodepos']."</td><td align=left bgcolor=".$arrdtc['kodepos']."><b>".$bar->kodepos."</b></td>
		</tr>
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>LOKASI KERJA DAN STATUS KARYAWAN</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['kodeorganisasi']."</td><td align=left bgcolor=".$arrdtc['kodeorganisasi']."><b>".$bar->kodeorganisasi."</b></td>
			<td align=right>".$_SESSION['lang']['lokasitugas']."</td><td align=left bgcolor=".$arrdtc['lokasitugas']."><b>".$bar->lokasitugas." - ".$arrlokasitugas[$bar->lokasitugas]."</b></td>
		</tr>                               
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['divisi']."</td><td align=left bgcolor=".$arrdtc['subbagian']."><b>".$bar->subbagian."</b></td>
			
			<td align=right>".$_SESSION['lang']['departemen']."</td><td align=left bgcolor=".$arrdtc['bagian']."><b>".$nmBagian[$bar->bagian]."</b></td>        
		</tr>
		<tr class=rowcontent>
			<td align=right>Level ".$_SESSION['lang']['karyawan']."</td><td align=left bgcolor=".$arrdtc['levelkaryawan']."><b>".$nmLevelKaryawan[$bar->levelkaryawan]."</b></td>
			<td align=right>".$_SESSION['lang']['functionname']."</td><td align=left bgcolor=".$arrdtc['kodejabatan']."><b>".$jabatan."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['tipekaryawan']."</td><td align=left bgcolor=".$arrdtc['tipekaryawan']."><b>".$tipekaryawan."</b></td>
			<td align=right>".$_SESSION['lang']['kodegolongan']."</td><td align=left bgcolor=".$arrdtc['kodegolongan']."><b>".$nmGolongan[$bar->kodegolongan]."</b></td>
			
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['status']." ".$_SESSION['lang']['karyawan']."</td><td align=left bgcolor=".$arrdtc['statuskaryawan']."><b>".$statuskaryawan."</b></td>    
			<td align=right>".$_SESSION['lang']['tanggalmasuk']."</td><td align=left bgcolor=".$arrdtc['tanggalmasuk']."><b>".tanggalnormal($bar->tanggalmasuk)."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['tanggalpengangkatan']." Pertama</td><td align=left bgcolor=".$arrdtc['tanggalpengangkatan']."><b>".tanggalnormal($bar->tanggalpengangkatan)."</b></td>
			<td align=right>".$_SESSION['lang']['tanggalkeluar']."</td><td align=left bgcolor=".$arrdtc['tanggalkeluar']."><b>".tanggalnormal($bar->tanggalkeluar)."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>Periode Gaji Terakhir</td><td align=left bgcolor=".$arrdtc['periodeakhirgaji']."><b>".$bar->periodeakhirgaji."</b></td>
			<td align=right>Masa Kerja</td><td align=left bgcolor=EDEDED><b>".$lamaKerja."</b></td>
			
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['sistemgaji']."</td><td align=left bgcolor=".$arrdtc['sistemgaji']."><b>".$bar->sistemgaji."</b></td>
			<td align=right>".$_SESSION['lang']['statuspajak']."</td><td align=left bgcolor=".$arrdtc['insstatuspajak']."><b>".$optKetStatusPajak[$bar->insstatuspajak]."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['lokasipenerimaan']."</td><td align=left bgcolor=".$arrdtc['lokasipenerimaan']."><b>".$namaprov[$bar->lokasipenerimaan]."</b></td>
			<td align=right>".$_SESSION['lang']['alokasibiaya']."</td><td align=left bgcolor=".$arrdtc['alokasi']."><b>".$arralokasibiaya[$bar->alokasi]."</b></td>
		</tr>

		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['tanggalpengangkatan']." Non Staff</td><td align=left bgcolor=".$arrdtc['tanggalpengangkatannonstaff']."><b>".tanggalnormal($bar->tanggalpengangkatannonstaff)."</b></td>
			<td align=right></td><td></td>
		</tr>
		
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>DETAIL KARYAWAN</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['telp']."</td><td align=left bgcolor=".$arrdtc['noteleponrumah']."><b>".$bar->noteleponrumah."</b></td>
			<td align=right>".$_SESSION['lang']['nohp']." (1)</td><td align=left bgcolor=".$arrdtc['nohp']."><b>".$bar->nohp."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['nohp']." (2)</td><td align=left bgcolor=".$arrdtc['nohp2']."><b>".$bar->nohp2."</b></td>
			<td align=right>".$_SESSION['lang']['notelepondarurat']."</td><td align=left bgcolor=".$arrdtc['notelepondarurat']."><b>".$bar->notelepondarurat."</b></td>
		</tr> 
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['agama']."</td><td align=left bgcolor=".$arrdtc['agama']."><b>".$bar->agama."</b></td>
			<td align=right>".$_SESSION['lang']['suku']."</td><td align=left bgcolor=".$arrdtc['suku']."><b>".$suku."</b></td>    
		</tr>
		
		
		<tr class=rowcontent>	
			<td align=right>".$_SESSION['lang']['statusperkawinan']."</td><td align=left bgcolor=".$arrdtc['statusperkawinan']."><b>".$bar->statusperkawinan."</b></td>
			<td align=right>".$_SESSION['lang']['tanggalmenikah']."</td><td align=left bgcolor=".$arrdtc['tanggalmenikah']."><b>".tanggalnormal($bar->tanggalmenikah)."</b></td>
		</tr>    
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['jumlahanak']."</td><td align=left bgcolor=".$arrdtc['jumlahanak']."><b>".$bar->jumlahanak."</b></td>
			<td align=right>".$_SESSION['lang']['levelpendidikan']."</td><td align=left bgcolor=".$arrdtc['levelpendidikan']."><b>".$pendidikan."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right >Nomor SIM</td><td align=left bgcolor=".$arrdtc['sim']."><b>".$bar->sim."</b></td>
			<td align=right>".$_SESSION['lang']['golongandarah']."</td><td align=left bgcolor=".$arrdtc['golongandarah']."><b>".$bar->golongandarah."</b></td>
		</tr>          
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['npwp']."</td><td align=left bgcolor=".$arrdtc['npwp']."><b>".$bar->npwp."</b></td>
			<td align=right>KPP Perusahaan</td><td align=left bgcolor=".$arrdtc['kppnpwp']."><b>".$bar->kppnpwp."</b></td>
		</tr> 
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['email']." Pribadi</td><td align=left bgcolor=".$arrdtc['email']."><b>".$bar->email."</b></td>
			<td align=right>".$_SESSION['lang']['email']." ".$_SESSION['lang']['kantor']."</td><td align=left bgcolor=".$arrdtc['emailkantor']."><b>".$bar->emailkantor."</b></td>
		</tr> 
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>REKENING</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['namabank']."</td><td align=left bgcolor=".$arrdtc['namabank']."><b>".$nmbank[$bar->namabank]."</b></td>
			<td align=right>".$_SESSION['lang']['norekeningbank']."</td><td align=left bgcolor=".$arrdtc['norekeningbank']."><b>".$bar->norekeningbank."</b></td>
		</tr>  
		<tr class=rowcontent>
			<td align=right>A/N Rekening</td><td align=left bgcolor=".$arrdtc['pemilikrekening']."><b>".$bar->pemilikrekening."</b></td>
			<td align=right></td><td align=left bgcolor=".$arrdtc['pemilikrekening']."></td>
		</tr>
		<tr class=rowcontent>	
			<td colspan=4 style=background-color:#adffff;text-align:center;font-style:italic;font-weight:bold;>BPJS</td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['jms']."</td><td align=left bgcolor=".$arrdtc['jms']."><b>".$bar->jms."</b></td>
			<td align=right>".$_SESSION['lang']['bpjs']." (".$_SESSION['lang']['kesehatan'].")</td><td align=left bgcolor=".$arrdtc['bpjs']."><b>".$bar->bpjs."</b></td>
		</tr>
		<tr class=rowcontent>
			<td align=right>".$_SESSION['lang']['bpjs']." Pensiun</td><td align=left bgcolor=".$arrdtc['pensiun']."><b>".$bar->pensiun."</b></td>
			<td align=right>".$_SESSION['lang']['jumlahtanggunganbpjs']."</td><td align=left bgcolor=".$arrdtc['jumlahtanggungan']."><b>".$bar->jumlahtanggungan."</b></td>
		</tr>
                ";

}
	$tab.="</table>
     </fieldset>
         <fieldset><legend>".$_SESSION['lang']['pengalamankerja']."</legend>
         <table class=sortable cellspacing=1 cellpadding=5 width=100% cellpadding=2>
			<thead>
			<tr class=rowheader  bgcolor=#e6e6e6>
			  <th>No.</th>
			  <th>".$_SESSION['lang']['orgname']."</th>
			  <th>".$_SESSION['lang']['bidangusaha']."</th>
			  <th>".$_SESSION['lang']['bulanmasuk']."</th>
			  <th>".$_SESSION['lang']['bulankeluar']."</th>
			  <th>".$_SESSION['lang']['jabatanterakhir']."</th>
			  <th>".$_SESSION['lang']['bagian']."</th>
			  <th>".$_SESSION['lang']['masakerja']."</th>
			  <th>".$_SESSION['lang']['alamat']."</th>  
			</tr>  
			</thead>
	";
	$str="select *,right(bulanmasuk,4) as masup,left(bulanmasuk,2) as busup from ".$dbname.".sdm_karyawancv where karyawanid=".$karyawanid." order by masup,busup";
	$res3=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res3->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	$mskerja=0;
	while($bar=$res3->fetch()){
		$no+=1;  
		$msk=mktime(0,0,0,substr(str_replace("-","",$bar->bulanmasuk),0,2),1,substr($bar->bulanmasuk,3,4)); 
		$klr=mktime(0,0,0,substr(str_replace("-","",$bar->bulankeluar),0,2),1,substr($bar->bulankeluar,3,4)); 
		$dateDiff = $klr - $msk;
		$mskerja = floor($dateDiff/(60*60*24))/365; 

		$tab.="<tr class=rowcontent>
		  <td>".$no."</td>
		  <td>".$bar->namaperusahaan."</td>
		  <td>".$bar->bidangusaha."</td>
		  <td>".$bar->bulanmasuk."</td>
		  <td>".$bar->bulankeluar."</td>
		  <td>".$bar->jabatan."</td>
		  <td>".$bar->bagian."</td>
		  <td>".number_format($mskerja,2,',','.')." Yrs.</td>
		  <td>".$bar->alamatperusahaan."</td> 
		</tr>";   
	}   
$tab.="</table>
    </fieldset>
	<fieldset><legend>".$_SESSION['lang']['pendidikan']."</legend>
	<table class=sortable  cellspacing=1 width=100% cellpadding=5 style=text-align:center>
	<thead>
	<tr class=rowheader bgcolor=#e6e6e6 >
	<th>No.</th>
	<th>".$_SESSION['lang']['edulevel']."</th>        
	<th>".$_SESSION['lang']['namasekolah']."</th>
	<th>".$_SESSION['lang']['kota']."</th>        
	<th>".$_SESSION['lang']['jurusan']."</th>       
	<th>".$_SESSION['lang']['tahunlulus']."</th>
	<th>".$_SESSION['lang']['gelar']."</th>
	<th>".$_SESSION['lang']['nilai']."</th>
	<th>".$_SESSION['lang']['keterangan']."</th>  
	</tr>
	</thead>
	";
	$str="select a.*,b.kelompok from ".$dbname.".sdm_karyawanpendidikan a,".$dbname.".sdm_5pendidikan b
	where a.karyawanid=".$karyawanid."  and a.levelpendidikan=b.levelpendidikan order by a.levelpendidikan desc";
	$res4=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res4->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$res4->fetch()){
	$no+=1;  
	$tab.="    <tr class=rowcontent>
		  <td>".$no."</td>
		  <td>".$bar->kelompok."</td>       
		  <td>".$bar->namasekolah."</td>
		  <td>".$bar->kota."</td>       
		  <td>".$bar->spesialisasi."</td>       
		  <td>".$bar->tahunlulus."</td>
		  <td>".$bar->gelar."</td>
		  <td>".$bar->nilai."</td>
		  <td>".$bar->keterangan."</td>
		</tr>";   
	}   
$tab.="</table>
         </fieldset>
         <fieldset><legend>".$_SESSION['lang']['traininginternal']."</legend>
         <table class=sortable cellspacing=1 width=100% cellpadding=5 style=text-align:center>
		 <thead>
         <tr class=rowheader bgcolor=#e6e6e6>
          <td>No.</td>
          <td>".$_SESSION['lang']['jeniskursus']."</td>       
          <td>".$_SESSION['lang']['legend']."</td>
          <td>".$_SESSION['lang']['penyelenggara']."</td>       
          <td>".$_SESSION['lang']['tanggalmasuk']."</td>        
          <td>".$_SESSION['lang']['tanggalkeluar']."</td>
          <td>".$_SESSION['lang']['sertifikat']."</td>
         </tr>
		</thead> 
     ";
   
	$arrjenistraining=makeOption($dbname,'sdm_5jenistraining','kodetraining,jenistraining');
	$str="select a.*,case a.sertifikat when 0 then 'N' else 'Y' end as bersertifikat, b.jenistraining as jnsTraining 
	from ".$dbname.".sdm_karyawantraining a  left join ".$dbname.".sdm_5jenistraining b  on a.jenistraining = b.kodetraining 
	where a.karyawanid=".$karyawanid."  order by a.tanggalmulai desc";  
	$res5=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res5->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$res5->fetch()){
		$no+=1;  
		$tab.="    <tr class=rowcontent>
		<td class=firsttd>".$no."</td>
		<td>".$arrjenistraining[$bar->jenistraining]."</td>       
		<td>".$bar->judultraining."</td>
		<td>".$bar->penyelenggara."</td>        
		<td>".tanggalnormal($bar->tanggalmulai)."</td>        
		<td>".tanggalnormal($bar->tanggalselesai)."</td>
		<td>".$bar->bersertifikat."</td>
		</tr>";   
	}   
$tab.="</table>
     </fieldset>
		<fieldset><legend>".$_SESSION['lang']['keluarga']."</legend>
		<table class=sortable cellspacing=1 width=100% cellpadding=5 style=text-align:center>
			<thead>
			<tr class=rowheader bgcolor=#e6e6e6 >
				<th>No.</th>
				<th>".$_SESSION['lang']['nama']."</th>        
				<th>".$_SESSION['lang']['jeniskelamin']."</th>
				<th>".$_SESSION['lang']['hubungan']."</th>        
				<th>".$_SESSION['lang']['tanggallahir']."</th>        
				<th>".$_SESSION['lang']['statusperkawinan']."</th>
				<th>".$_SESSION['lang']['umur']."</th>
				<th>".$_SESSION['lang']['pendidikan']."</th>
				<th>".$_SESSION['lang']['pekerjaan']."</th>
				<th>".$_SESSION['lang']['telp']."</th>
				<th>".$_SESSION['lang']['email']."</th>
				<th>".$_SESSION['lang']['tanggungan']."</th>
				<th>".$_SESSION['lang']['emplasment']."</th>
			</tr>
			</thead>
		";
		$str="select a.*,case a.tanggungan when 0 then 'N' else 'Y' end as tanggungan1,case a.emplasment when 0 then 'N' else 'Y' end as emplasment1, 
		b.kelompok,COALESCE(ROUND(DATEDIFF('".date('Y-m-d')."',a.tanggallahir)/365.25,1),0) as umur from ".$dbname.".sdm_karyawankeluarga a,".$dbname.".sdm_5pendidikan b where a.karyawanid=".$karyawanid." and a.levelpendidikan=b.levelpendidikan order by hubungankeluarga"; 
		$res6=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res6->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res6->fetch()){
			$val=$bar->hubungankeluarga;
			if($_SESSION['language']=='EN'){
				switch($bar->hubungankeluarga){
					case'Pasangan':
					  $val='Couple';
					  break;
					case'Anak':
					  $val='Child';
					  break;
					case'Ibu':
					  $val='Mother';
					  break;
					case'Bapak':
					  $val='Father';
					  break;
					case'Adik':
					  $val='Younger brother/sister';
					  break;        
					case'Kakak':
					  $val='Older brother/sister';
					  break;      
					case'Ibu Mertua':
					  $val='Monther-in-law';
					  break;   
					case'Bapak Mertua':
					  $val='Father-in-law';
					  break;   
					case'Sepupu':
					  $val='Cousin';
					  break;  
					case'Ponakan':
					  $val='Nephew';
					  break;                                
					default:
					  $val='Foster child';
					  break;                         
				}
			}
			$gal=$bar->status;
			if($_SESSION['language']=='EN' && $bar->status=='Kawin')
				$gal='Married';
			if($_SESSION['language']=='EN' && ($bar->status=='Bujang' or $bar->status=='Lajang'))
				$gal='Single';            

			$no+=1;  
			$tab.=" <tr class=rowcontent>
				<td>".$no."</td>
				<td>".$bar->nama."</td>       
				<td>".$bar->jeniskelamin."</td>
				<td>".$val."</td>       
				<td>".$bar->tempatlahir.",".tanggalnormal($bar->tanggallahir)."</td>        
				<td>".$gal."</td>
				<td>".$bar->umur." Yrs</td>
				<td>".$bar->kelompok."</td>
				<td>".$bar->pekerjaan."</td>
				<td>".$bar->telp."</td>
				<td>".$bar->email."</td>
				<td>".$bar->tanggungan1."</td>
				<td>".$bar->emplasment1."</td>
				</tr>";   
		}   
$tab.="</table>
     </fieldset>
         <fieldset><legend>".$_SESSION['lang']['alamat']."</legend>
         <table class=sortable cellspacing=1 width=100% cellpadding=5 style=text-align:center>
		 <thead>
         <tr class=rowheader bgcolor=#e6e6e6 >
          <th>No.</th>
          <th>".$_SESSION['lang']['alamat']."</th>        
          <th>".$_SESSION['lang']['kota']."</th>
          <th>".$_SESSION['lang']['province']."</th>        
          <th>".$_SESSION['lang']['kodepos']."</th>       
          <th>".$_SESSION['lang']['emplasmen']."</th>
          <th>".$_SESSION['lang']['status']."</th>
         </tr>
		 </thead>
         ";
		$str="select *,case aktif when 1 then 'Yes' when 0 then 'No' end as status from ".$dbname.".sdm_karyawanalamat where karyawanid=".$karyawanid." order by nomor desc";
		$res7=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res7->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res7->fetch()){
			$no+=1;  
			$tab.=" <tr class=rowcontent>
			  <td class=firsttd>".$no."</td>
			  <td>".$bar->alamat."</td>       
			  <td>".$bar->kota."</td>
			  <td>".$bar->provinsi."</td>       
			  <td>".$bar->kodepos."</td>        
			  <td>".$bar->emplasemen."</td>
			  <td>".$bar->status."</td>
			</tr>";   
		}   
                 
if($_SESSION['language']=='EN'){
	$gg='History of reprimands';
}else{
	$gg='Riwayat Teguran dan SP';
}
        
$tab.="</table>
         </fieldset>
         <fieldset><legend>".$gg."</legend>
         <table class=sortable cellspacing=1 width=100% cellpadding=5 style=text-align:center>
         <thead>
		 <tr class=rowheader bgcolor=#e6e6e6 >
          <th>No.</th>
          <th>".$_SESSION['lang']['jenissp']."</th>       
          <th>".$_SESSION['lang']['tanggalsurat']."</th>
          <th>".$_SESSION['lang']['masaberlaku']." (".$_SESSION['lang']['bulan'].")</th>        
          <th>".$_SESSION['lang']['pelanggaran']."</th>       
          <th>".$_SESSION['lang']['penandatangan']."</th>
          <th>".$_SESSION['lang']['functionname']."</th>
         </tr>
		 </thead>
         ";
		$str="select * from ".$dbname.".sdm_suratperingatan where karyawanid=".$karyawanid." order by tanggal desc";
		$res8=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res8->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res8->fetch()){
			$no+=1;  
			$tab.=" <tr class=rowcontent>
			  <td class=firsttd>".$bar->nomor."</td>
			  <td>".$bar->jenissp."</td>        
			  <td>".tanggalnormal($bar->tanggal)."</td>
			  <td align=right>".$bar->masaberlaku."</td>        
			  <td>".$bar->pelanggaran."</td>        
			  <td>".$bar->penandatangan."</td>
			  <td>".$bar->jabatan."</td>
			</tr>";   
		}   

		$str="select * from ".$dbname.".sdm_5jabatan";
		$res9=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res9->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res9->fetch()){
		$kamusjabatan[$bar->kodejabatan]=$bar->namajabatan;
		}
if($_SESSION['language']=='EN'){
	$gg='Promotion history';
}else{
	$gg='Riwayat Mutasi/Promosi/Demosi';
}                 
$tab.="</table>
	 </fieldset>
	 <fieldset><legend>".$gg."</legend>
	 <table class=sortable cellspacing=1 width=100% cellpadding=5 style=text-align:center>
	 <thead>
	 <tr class=rowheader bgcolor=#e6e6e6 >
	  <th>No.</th>
	  <th>".$_SESSION['lang']['tipetransaksi']."</th>
	  <th>".$_SESSION['lang']['tanggalsurat']."</th>
	  <th>".$_SESSION['lang']['tanggalberlaku']."</th>        
	  <th>".$_SESSION['lang']['dari']."</th>
	  <th>".$_SESSION['lang']['ke']."</th>    
	  <th>".$_SESSION['lang']['penandatangan']."</th>
	 </tr>
	 </thead>
	 ";
	 $str="select * from ".$dbname.".sdm_riwayatjabatan where karyawanid=".$karyawanid." order by tanggalsk desc";
	$resw=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$resw->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$resw->fetch()){
		$no+=1;  
		$tab.=" <tr class=rowcontent>
		<td class=firsttd>".$bar->nomorsk."</td>
		<td>".$bar->tipesk."</td>       
		<td>".tanggalnormal($bar->tanggalsk)."</td>
		<td>".tanggalnormal($bar->mulaiberlaku)."</td>";
		if($bar->tipesk=='Mutasi'){
			$tab.="<td>".$bar->darikodeorg." (".$kamusjabatan[$bar->darikodejabatan].")</td>
					<td>".$bar->kekodeorg." (".$kamusjabatan[$bar->kekodejabatan].")</td>";                                              
		}elseif($bar->tipesk=='Promosi'){
			$tab.="<td>".$kamusjabatan[$bar->darikodejabatan]." (".$bar->darikodegolongan.")</td>       
				<td>".$kamusjabatan[$bar->kekodejabatan]." (".$bar->kekodegolongan.") </td>";                                             
		}elseif($bar->tipesk=='Demosi'){
			$tab.="<td>".$kamusjabatan[$bar->darikodejabatan]." (".$bar->darikodegolongan.")</td>       
				<td>".$kamusjabatan[$bar->kekodejabatan]." (".$bar->kekodegolongan.") </td>";                                             
		}
		$tab.="<td>".$bar->namadireksi."</td>
		</tr>";   
	}   
$tab.="</table>
         </fieldset>";
$sJabat="select * from ".$dbname.".sdm_5matriktraining where 1";
$resd=$owlPDO->query($sJabat) or die(print " Gagal: ".PDOException::getMessage());
$resd->setFetchMode(PDO::FETCH_ASSOC);
while($rJabat=$resd->fetch()){
    $kamusKategori[$rJabat['matrixid']]=$rJabat['kategori'];
    $kamusTopik[$rJabat['matrixid']]=$rJabat['topik'];
}

if($_SESSION['language']=='EN'){
	$gg='Training provided By '.$namapt;
}else{             
	$gg='Training di '.$namapt;
}
        
$tab.="<fieldset><legend>".$gg."</legend>
         <table class=sortable cellspacing=1 width=100% cellpadding=5 style=text-align:center>
		 <thead>
         <tr class=rowheader bgcolor=#e6e6e6 >
          <th>".$_SESSION['lang']['kategori']."</th>
          <th>".$_SESSION['lang']['topik']."</th>
          <th>".$_SESSION['lang']['tanggalmulai']."</th>        
          <th>".$_SESSION['lang']['tanggalsampai']."</th>       
          <th>".$_SESSION['lang']['catatan']."</th>       
         </tr>
		 </thead>
         ";
    $str="select * from ".$dbname.".sdm_matriktraining where karyawanid = '".$karyawanid."'";
    $rest=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $rest->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$rest->fetch()){
		$no+=1;  
		$tab.=" <tr class=rowcontent>
			  <td class=firsttd>".$kamusKategori[$bar->matrikxid]."</td>
			  <td>".$kamusTopik[$bar->matrikxid]."</td>       
			  <td>".tanggalnormal($bar->tanggaltraining)."</td>
			  <td>".tanggalnormal($bar->sampaitanggal)."</td>
			  <td>".$bar->catatan."</td>
			</tr>";   
	}   
$tab.="</table>
         </fieldset>";
if($_SESSION['language']=='EN'){
	$gg='Standard Training';
}else{           
	$gg='Standard training yang harus diikuti';
}
        
$tab.="<fieldset><legend>".$gg."</legend>
         <table class=sortable cellspacing=1 width=100% cellpadding=5 style=text-align:center>
		 <thead>
         <tr class=rowheader bgcolor=#e6e6e6>
          <th>".$_SESSION['lang']['jabatan']."</th>
          <th>".$_SESSION['lang']['kategori']."</th>
          <th>".$_SESSION['lang']['topik']."</th>       
         </tr>
		 </thead>
         ";
$str="select * from ".$dbname.".sdm_5matriktraining where kodejabatan = '".$jabatanku."'";
$rest1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$rest1->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar=$rest1->fetch()){
	$no+=1;  
	$tab.=" <tr class=rowcontent>
		  <td class=firsttd>".$kamusjabatan[$bar->kodejabatan]."</td>
		  <td>".$bar->kategori."</td>       
		  <td>".$bar->topik."</td>
	</tr>";   
}   
$tab.="</table>
         </fieldset>";
		 
$sJabat="select * from ".$dbname.".log_5supplier where 1";
$rest1=$owlPDO->query($sJabat) or die(print " Gagal: ".PDOException::getMessage());
$rest1->setFetchMode(PDO::FETCH_ASSOC);
while($rJabat=$rest1->fetch()){
    $kamusSup[$rJabat['supplierid']]=$rJabat['namasupplier'];
}
if($_SESSION['language']=='EN'){
	$gg='Additional Training';
}else{           
	$gg='Additional training yang sudah diikuti';
}
        
$tab.="<fieldset><legend>".$gg."</legend>
         <table class=sortable cellspacing=1 width=100% cellpadding=5 style=text-align:center>
         <thead>
		 <tr class=rowheader bgcolor=#e6e6e6 >
          <th>".$_SESSION['lang']['namatraining']."</th>
          <th>".$_SESSION['lang']['penyelenggara']."</th>
          <th>".$_SESSION['lang']['tanggalmulai']."</th>        
          <th>".$_SESSION['lang']['tanggalsampai']."</th>       
         </tr>
		</thead> 
         ";
    $str="select * from ".$dbname.".sdm_5training where karyawanid = '".$karyawanid."' and sthrd = '1'";
    $rest1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $rest1->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$rest1->fetch()){
	$no+=1;  
	$tab.=" 
		<tr class=rowcontent>
			<td class=firsttd>".$bar->namatraining."</td>
			<td>".$kamusSup[$bar->penyelenggara]."</td>       
			<td>".tanggalnormal($bar->tglmulai)."</td>
			<td>".tanggalnormal($bar->tglselesai)."</td>
		</tr>";   
	}   
$tab.="</table>
         </fieldset>";
$tab.="</div>";

$frm1[0].=$tab;
####################### history datakaryawan ###########

$frm1[1]="<div><span id=cap1></span><span id=cap2></span>
         <table class=sortable border=0 cellspacing=1 cellpadding=5>
         <thead>
           <tr class=rowheader>
             <td align=center>No.</td>
                 <td align=center>".$_SESSION['lang']['nik']."</td>
                 <td align=center>".$_SESSION['lang']['nama']."</td>
                 <td align=center>".$_SESSION['lang']['functionname']."</td>
                 <td align=center>".$_SESSION['lang']['kodegolongan']."</td>
                 <td align=center>".$_SESSION['lang']['lokasitugas']."</td>
                 <td align=center>".$_SESSION['lang']['pt']."</td>
                 <td align=center>".$_SESSION['lang']['noktp']."</td>
                 <td align=center>".$_SESSION['lang']['pendidikan']."</td>
                 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['statuspajak'])."</td>
                 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['statusperkawinan'])."</td>
                 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['jumlahanak'])."</td>
                 <td align=center>".$_SESSION['lang']['tanggalmasuk']."</td>
                 <td align=center>".$_SESSION['lang']['tanggalkeluar']."</td>
                 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['tipekaryawan'])."</td>
                 <td align=center>".$_SESSION['lang']['status']." ".$_SESSION['lang']['karyawan']."</td>
                 <td align=center>Status Approval</td>
                 <td align=center>Tanggal Perubahan</td>
                 <td align=center>Periode Gaji</td>
                 <td align=center>Tipe Perubahan</td>
                 <td align=center>Version</td>
                 <td colspan=4 align=center>Action</td>
           </tr>
         </thead>
         <tbody>";
         #jabatan yang tampil di kebun dan pabrik, tipe parameter nya global tanpa kunci kode org##
        // $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRJBTNUNIT' ";
        // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_ASSOC);
        // $bar=$res->fetch();
        // $hrjbtnunit=$bar['nilai'];

        //Numrows perpage==20;
        // $getrows=20;
        //default query
        // $page = checkPostGet('page',1);
        // $maxdisplay=($page*$getrows-20);


        $txtsearch='';
        $orgsearch='';  
        $tipesearch='';
        $statussearch='';
        $noktp='';


      
       
        //make sure user can only access allowed data   
        // $listOrg=ambilLokasiTugasDanTurunannya('list',$_SESSION['empl']['lokasitugas']);
        // $list=str_replace("|","','",$listOrg);
        // $list="'".$list."'";

        
        //a.tipekaryawan!=0 orang yang tidak di pusat tidak dapat melihat data orang permanent
        $str="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan_hist a, 
              ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
            a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
            and d.id=a.tipekaryawan and karyawanid='".$karyawanid."' order by periodegaji desc";    

        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($res);
        if($numrows<1){
            $frm1[1].= "<tr class=rowcontent>
                    <td colspan=22 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
                    </tr>";
        }else{
            $no=0;
            while($bar=$res->fetch()){
				//get pendidikan terakhir
				$str1="select a.kelompok from ".$dbname.".sdm_5pendidikan a
					   where a.levelpendidikan=".$bar->levelpendidikan; 
				$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				$pendidikan="";
				while($barpendidikan=$res1->fetch()){
					$pendidikan=$barpendidikan->kelompok;
				}    
				$no+=1;
				if($bar->tanggalkeluar == '0000-00-00'){
					$valueTglKeluar = '-';
				}else{
					$valueTglKeluar = tanggalnormal($bar->tanggalkeluar);
				}

				if($bar->tipe == '4'){
					$jnx="DTK1";
				}else if($bar->tipe == '1'){
					$jnx="DTK2";
				}else{
					$jnx="DTK3";
				}

				$frm1[1].= "<tr class=rowcontent>
				 <td align=center>".$no."</td>
                 <td>".$bar->nik."</td>
                 <td>".$bar->namakaryawan."</td>
                 <td>".$bar->namajabatan."</td>
                 <td>".$bar->namagolongan."</td>
                 <td align=center>".$bar->lokasitugas."</td>
                 <td align=center>".$bar->kodeorganisasi."</td>
                 <td>".$bar->noktp."</td>
                 <td>".$pendidikan."</td>
                 <td>".$bar->insstatuspajak."</td>
                 <td>".$bar->statusperkawinan."</td>
                 <td align=right >".$bar->jumlahanak."</td>
                 <td align=center>".tanggalnormal($bar->tanggalmasuk)."</td>
                 <td align=center>".$valueTglKeluar."</td>
                 <td align=center>".$bar->tipe."</td>
                 <td align=center>".$bar->statuskaryawan."</td>
                 <td align=center >".$arrstatusapp[$bar->approval_status]."</td>
                 <td align=center>".tanggalnormal($bar->updatetime)."</td>
                 <td align=center>".$bar->periodegaji."</td>
                 <td align=center>".$arrtipeversion[$bar->version_type]."</td>
                 <td align=center>".$bar->version."</td>";
				 $isi=0;
                 if($bar->approval_status=='0' or $bar->approval_status=='2' ){
					$dakarbulanan=0;
                    $strdatabul = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$bar->lokasitugas."' and periodegaji='".$bar->periodegaji."'"; 
                    $resdatabul = fetchdata($strdatabul);
                    if(count($resdatabul)>0){
						
					}else{
						if( $bar->approval_status=='0'){
							$frm1[1].="<td align=center width=25px>";
							$frm1[1].="
							<img src='images/skyblue/submit.jpg' class='zImgBtn' height='30' title='Ajukan ???' onclick=\"form_ajukan_dtk('".$bar->nourut."','".$bar->tipekaryawan."','".$bar->lokasitugas."','".$bar->namakaryawan."','".$bar->karyawanid."','".$bar->periodegaji."','1');\">";
							$frm1[1].="</td>";
						}else{
							$frm1[1].="<td align=center width=25px>";
							$frm1[1].="";
						}
                    }
                }else{
					$frm1[1].="<td align=center width=25px>";
					$frm1[1].="</td>";
				}
                if( $bar->approval_status=='0'){
                    $dakarbulanan=0;
                    $strdatabul = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$bar->lokasitugas."' and periodegaji='".$bar->periodegaji."'"; 
                    $resdatabul = fetchdata($strdatabul);
                    if(count($resdatabul)>0){ 
						$frm1[1].="<td align=center width=25px>";
						$frm1[1].="</td>";
                    }else{
						$frm1[1].="<td align=center width=25px>";
						$frm1[1].="<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editKaryawanhist('".$bar->nourut."','".$bar->karyawanid."','".$bar->namakaryawan."');\">";
						$frm1[1].="</td>";
						$isi++;
                    }
				}
				if($bar->approval_status=='7' and $bar->version_type=='B'){
					$frm1[1].="<td align=center width=25px>";
					$frm1[1].="<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editKaryawanhist('".$bar->nourut."','".$bar->karyawanid."','".$bar->namakaryawan."');\">";
					$frm1[1].="</td>";
					$isi++;
				}
				if($bar->approval_status=='7' and $bar->version_type=='B' and in_array($_SESSION['empl']['jabatan'],$jab)){
					$frm1[1].="<td align=center width=25px>";
					$frm1[1].="<img src=images/skyblue/posting.png class=resicon  height='30' title='posting' onclick=\"postingdata('".$bar->nourut."','".$bar->karyawanid."','".$bar->namakaryawan."');\">";
					$frm1[1].="</td>";
					$isi++;
				}else{
					$frm1[1].="<td align=center width=25px>";
					$frm1[1].="</td>";
				}
				
				if($bar->approval_status=='8' and $bar->version_type=='B' and in_array($_SESSION['empl']['jabatan'],$jab)){
					$frm1[1].="<td align=center width=25px>";
					$frm1[1].="<img src=images/icons/04/16/04.png height='30' class=resicon  title='unposting' onclick=\"unpostingdata('".$bar->nourut."','".$bar->karyawanid."','".$bar->namakaryawan."');\">";
					$frm1[1].="</td>";
					$isi++;
                }
				
				if($isi==0){
					$frm1[1].="<td align=center width=25px>";
					$frm1[1].="</td>";
				}
				$frm1[1].="<td align=center width=25px>";
				$frm1[1].="<img src=images/zoom.png class=resicon  title='".$_SESSION['lang']['view']."' onclick=\"previewKaryawanhist('".$bar->nourut."','".$bar->karyawanid."','".$bar->namakaryawan."',event);\">
                </td>
                </tr>";
            }
        }
         $frm1[1].="</tbody>
         <tfoot>
         </tfoot>    
         
    </table>
     </div>";
	
	if($param['tampilan']=='PDF'){
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A3', 'landscape');
		$dompdf->render();
		$dompdf->stream($param['namakaryawan'],array("Attachment"=>0));
		
	}else{			
		$nfrm[0]='Data Karyawan';
		$nfrm[1]='History Perubahan';
		drawTab('FRM1',$nfrm,$frm1,180,"100%");
	}	 
	
}
?>