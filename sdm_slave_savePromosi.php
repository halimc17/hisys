<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$tanggalsk = tanggalsystem(checkPostGet('tanggalsk', ''));
$tanggalberlaku = tanggalsystem(checkPostGet('tanggalberlaku', ''));
$tanggalpen = tanggalsystem(checkPostGet('tanggalpen', ''));

$penandatangan = checkPostGet('penandatangan', '');
$tembusan1 = checkPostGet('tembusan1', '');
$tembusan2 = checkPostGet('tembusan2', '');
$tembusan3 = checkPostGet('tembusan3', '');
$tembusan4 = checkPostGet('tembusan4', '');
$tembusan5 = checkPostGet('tembusan5', '');
$tipetransaksi = checkPostGet('tipetransaksi', '');
$karyawanid = checkPostGet('karyawanid', '');
$oldokasitugas = checkPostGet('oldokasitugas', '');
$oldjabatan = checkPostGet('oldjabatan', '');
$oldtipekaryawan = checkPostGet('oldtipekaryawan', '');
$oldgolongan = checkPostGet('oldgolongan', '');
$olddepartemen = checkPostGet('olddepartemen', '');
$newdepartemen = checkPostGet('newdepartemen', '');
$newlokasitugas = checkPostGet('newlokasitugas', '');
$newjabatan = checkPostGet('newjabatan', '');
$newgolongan = checkPostGet('newgolongan', '');
$oldsubbagian = checkPostGet('oldsubbagian', '');
$newsubbagian = checkPostGet('newsubbagian', '');
$newtipekaryawan = checkPostGet('newtipekaryawan', '');
$tanggungjawab = checkPostGet('tanggungjawab', '');
$method = checkPostGet('method', '');
$atasanbaru = checkPostGet('atasanbaru', '');
if ($atasanbaru == '') {
	$atasanbaru = 0;
}

$noskedit = checkPostGet('nosk', '');
$paragraf1 = checkPostGet('paragraf1', '');
$paragraf2 = checkPostGet('paragraf2', '');
$paragraf3 = checkPostGet('paragraf3', '');
$paragraf4 = checkPostGet('paragraf4', '');
$paragraf5 = checkPostGet('paragraf5', '');
$paragraf6 = checkPostGet('paragraf6', '');
$menimbang = checkPostGet('menimbang', '');
$mengingat = checkPostGet('mengingat', '');
$namajabatan = checkPostGet('namajabatan', '');


$oldkomponen = checkPostGet('oldkomponen', '');
$newkomponen = checkPostGet('newkomponen', '');
$tp = checkPostGet('tp', '');

function ceknonik($nik){
	global $karyawanid;
	global $kodeorganisasi;
	global $lokasitugas;
	global $tanggalmasuk;
	global $dbname;
	global $owlPDO;
	
	$str="select nik from ".$dbname.".datakaryawan where nik = '".$nik."'";
	$res=fetchData($str);
	if(count($res)>0){
		$adanik = 1;  
	}else{
		$adanik = 0;  
	}
	return $adanik;
}

if ($method == 'insert') {
	//get pt
	// $skodept="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' and left(kodeorganisasi,2)='".substr($_SESSION['empl']['lokasitugas'], 0, 2)."'";
	$skodept="select induk from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	//exit('warning :'.$skodept);
	$qkodept=$owlPDO->query($skodept) or die(print "Gagal: ".PDOException::getMessage());
	$qkodept->setFetchMode(PDO::FETCH_OBJ);
	while ($rkodept=$qkodept->fetch()) {
		$kodept=$rkodept->induk;
	}

	if ($tipetransaksi=='Pengangkatan'){
		$tipetrans='PG';	
	}else{
		$tipetrans=strtoupper(substr($tipetransaksi, 0, 2));
	}


	// $potSK = $kodept ."-". $tipetrans . substr($tanggalsk, 0, 4);
	// $str = "select nomorsk from " . $dbname . ".sdm_riwayatjabatan where  nomorsk like '" . $potSK . "%' order by right(nomorsk,5) desc limit 1";
	// $notrx = 0;
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $res->setFetchMode(PDO::FETCH_OBJ);
	// while ($bar = $res->fetch()) {
	// 	$notrx = substr($bar->nomorsk, 12, 5);
	// }

	// $notrx = intval($notrx);
	// $notrx = $notrx + 1;
	// $notrx = str_pad($notrx, 5, "0", STR_PAD_LEFT);
	// $notrx = $potSK . substr($tanggalsk, 4,2). $notrx;

	
	$notrx =$kodept ."-". $tipetrans. substr($tanggalsk, 0, 6).$karyawanid;
	
	$str = "insert into " . $dbname . ".sdm_riwayatjabatan (
	`karyawanid`,`nomorsk`,`tanggalsk`,`tanggalpengajuan`,
	`mulaiberlaku`,`darikodeorg`,
	`darisubbagian`,`kesubbagian`,
	`darikodejabatan`,`daritipe`,`tipesk`,
	`darikodegolongan`,`kekodeorg`,`kekodejabatan`,
	`ketipekaryawan`,`kekodegolongan`,`namadireksi`,`tembusan1`,`tembusan2`,
	`tembusan3`,`tembusan4`,`updateby`,                   
	`tembusan5`,`atasanbaru`,
	`namajabatan`,`pg1`,`pg2`,`pg3`,`pg4`,`pg5`,`pg6`,`menimbang`,`mengingat`,`bagian`,`kebagian`,`tanggungjawab`
	) values(
	" . $karyawanid . ",'" . $notrx . "'," . $tanggalsk . "," . $tanggalpen . ",
	" . $tanggalberlaku . ",'" . $oldokasitugas . "',
	'" . $oldsubbagian . "','" . $newsubbagian . "',
	" . $oldjabatan . "," . $oldtipekaryawan . ",'" . $tipetransaksi . "',
	'" . $oldgolongan . "','" . $newlokasitugas . "'," . $newjabatan . ",
	" . $newtipekaryawan . ",'" . $newgolongan . "','" . $penandatangan . "','" . $tembusan1 . "','" . $tembusan2 . "',
	'" . $tembusan3 . "','" . $tembusan4 . "'," . $_SESSION['standard']['userid'] . ",   
	'" . $tembusan5 . "',
	" . $atasanbaru . ",'" . $namajabatan . "','" . $paragraf1 . "','" . $paragraf2 . "','" . $paragraf3 . "','" . $paragraf4 . "','" . $paragraf5 . "','" . $paragraf6 . "','" . $menimbang . "','" . $mengingat . "',
	'" . $olddepartemen . "','" . $newdepartemen . "' ,'" . $tanggungjawab . "'    
)";


}else if ($method == 'pengajuansk') {
	$notransaksi = checkPostGet('notransaksi','');

	$optPT = makeOption($dbname,'organisasi','kodeorganisasi,induk');
	$optnmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	$optnmkary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
	$optkodjab = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan');
	$optnmjabatan = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
	$optnmbagian = makeOption($dbname,'sdm_5departemen','kode,nama');
	$optnmgolongan = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');

	$strxz = "select * from ".$dbname.".sdm_riwayatjabatan where nomorsk='".$notransaksi."'";
	$dataxz = fetchData($strxz);
	$darisubbagian=$dataxz[0]['darisubbagian'];
	$kesubbagian=$dataxz[0]['kesubbagian'];
	$karyawanid=$dataxz[0]['karyawanid'];
	$darikodejabatan=$dataxz[0]['darikodejabatan'];
	$kekodejabatan=$dataxz[0]['kekodejabatan'];
	$darikodegolongan=$dataxz[0]['darikodegolongan'];
	$kekodegolongan=$dataxz[0]['kekodegolongan'];
	$darikodeorg=$dataxz[0]['darikodeorg'];
	$kekodeorg=$dataxz[0]['kekodeorg'];
	$bagian=$dataxz[0]['bagian'];
	$kebagian=$dataxz[0]['kebagian'];
	$daript=$optnmorg[$optPT[$dataxz[0]['darikodeorg']]];
	$kept=$optnmorg[$optPT[$dataxz[0]['kekodeorg']]];
	$mulaiberlaku=$dataxz[0]['mulaiberlaku'];
	$menimbang=$dataxz[0]['menimbang'];
	$tipesk=$dataxz[0]['tipesk'];
	$nomorsk=$dataxz[0]['nomorsk'];
	$diajukanoleh=$dataxz[0]['updateby'];
	$tanggalaju=$dataxz[0]['tanggalpengajuan'];
	$statuspersetujuan=$dataxz[0]['statuspersetujuan'];

	

	$tipeskeng['Promosi']='Promotion';
	$tipeskeng['Mutasi']='Transfer';
	$tipeskeng['Demosi']='Demotion';

	$tab="<table>";
	$tab.="<tr>
	<td align=center; colspan='3' style='font-size:16pt;'><b>".$daript."</b></td>
	</tr>";
	$tab.="<tr>
	<td align=center; colspan='3' style='font-size:16pt;'><b>Form Pengajuan ".$tipesk."</b></td>
	</tr>";
	$tab.="<tr>
	<td align=center; colspan='3' style='font-size:11pt;'><i>(Employee ".$tipeskeng[$tipesk]." Request)</i></td>
	</tr>";
	$tab.="<tr>
	<td colspan='3' style='background-color: black;'></td>
	</tr>";
	$tab.="<tr>
	<td align=left; colspan='3' style='font-size:12pt;'>Dengan ini kami mohon persetujuan Manajemen mengenai ".$tipesk." Karyawan dengan data sebagai berikut :<i>/ Hereby we would like to request Employee ".$tipeskeng[$tipesk]." Request approval with following data :</i></td>
	</tr>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";
	$tab.="<tr>
	<td align=left style='font-size:12pt;width:250px;'>Nama Karyawan<i>/Employee name</i></td>
	<td align=left style='font-size:12pt;width:2px;'>:</td>
	<td align=left style='font-size:12pt;'>".$optnmkary[$karyawanid]."</td>
	</tr>";
	$tab.="<tr>
	<td align=left style='font-size:12pt;width:250px;'>Jabatan<i>/Position</i></td>
	<td align=left style='font-size:12pt;width:2px;'>:</td>
	<td align=left style='font-size:12pt;'>".$optnmjabatan[$darikodejabatan]."</td>
	</tr>";
	$tab.="<tr>
	<td align=left style='font-size:12pt;width:250px;'>Bagian<i>/Department</i></td>
	<td align=left style='font-size:12pt;width:2px;'>:</td>
	<td align=left style='font-size:12pt;'>".$optnmbagian[$bagian]."</td>
	</tr>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";
	$tab.="<tr>
	<td align=left; colspan='3' style='font-size:12pt;'>Permintaan ".$tipesk." sebagai berikut <i>/ The following ".$tipeskeng[$tipesk]." is requested.</i></td>
	</tr>";
	$tab.="</table>";
	$tab.="<table border=1 cellspacing=0>";
	$tab.="<tr>
	<td align=left style='font-size:12pt;'></td>
	<td align=left style='font-size:12pt;'>Dari (jabatan sekarang) <i>/ From (current job)</i></td>
	<td align=left style='font-size:12pt;'>Ke (jabatan baru) <i>/ To (new position)</i></td>
	</tr>";			
	$tab.="<tr>
	<td align=left style='font-size:12pt;'>Jabatan <i>/ Position</i></td>
	<td align=left style='font-size:12pt;'>".$optnmjabatan[$darikodejabatan]."</td>
	<td align=left style='font-size:12pt;'>".$optnmjabatan[$kekodejabatan]."</td>
	</tr>";				
	$tab.="<tr>
	<td align=left style='font-size:12pt;'>Grade <i>/ Grade</i></td>
	<td align=left style='font-size:12pt;'>".$optnmgolongan[$darikodegolongan]."</td>
	<td align=left style='font-size:12pt;'>".$optnmgolongan[$kekodegolongan]."</td>
	</tr>";			
	$tab.="<tr>
	<td align=left style='font-size:12pt;'>Lokasi kerja <i>/ Location</i></td>
	<td align=left style='font-size:12pt;'>".$optnmorg[$darikodeorg]."</td>
	<td align=left style='font-size:12pt;'>".$optnmorg[$kekodeorg]."</td>
	</tr>";	
	$tab.="<tr>
	<td align=left style='font-size:12pt;'>Sub Bagian <i>/ Division</i></td>
	<td align=left style='font-size:12pt;'>".$darisubbagian."</td>
	<td align=left style='font-size:12pt;'>".$kesubbagian."</td>
	</tr>";	
	$tab.="<tr>
	<td align=left style='font-size:12pt;'>Perusahaan <i>/ Company</i></td>
	<td align=left style='font-size:12pt;'>".$optnmorg[$optPT[$darikodeorg]]."</td>
	<td align=left style='font-size:12pt;'>".$optnmorg[$optPT[$kekodeorg]]."</td>
	</tr>";	
	$tab.="</table>";

	$tab.="<table cellspacing=0>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";
	$tab.="<tr>
	<td align=left style='font-size:12pt;width:200px;'><b>Tanggal efektif <i>/ effective date</i></b></td>
	<td align=left style='font-size:12pt;width:2px;'><b>:</b></td>
	<td align=left style='font-size:12pt;'><b>".tanggalnormal($mulaiberlaku)."</b></td>
	</tr>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";

	$pattern = "/\r\n|\r|\n/";
       $kontendetail=preg_split($pattern, $menimbang);
	$tab.="<tr valign='top'>
	<td align=left style='font-size:12pt;width:200px;'>Alasan ".$tipesk." <i>/ Reason for ".$tipeskeng[$tipesk]." </i></td>
	<td align=left style='font-size:12pt;width:2px;'>:</td>";

	$tab.="<td align=left style='font-size:12pt;width:280px;'> ";
		foreach ($kontendetail as $key => $value){
			$tab.=$value."<br>";
		}
	$tab.="</td>";

	$tab.="</tr>";
	$tab.="<tr>
	<td  colspan='3'></td>
	</tr>";
	$tab.="<tr>
	<td colspan=3 align=left style='font-size:12pt;width:250px;'>Demikian permohonan ".$tipesk." yang telah kami sepakati bersama, sesuai kondisi penjelasan diatas <i>/ Thus the ".$tipeskeng[$tipesk]." request that we have agreed together, according to the description above.</i></td>
	</tr>";
	$tab.="</table>";

	if($tipesk=='Promosi'){
		$jenispersetujuanx='PRM';
	}elseif($tipesk=='Mutasi'){
		$jenispersetujuanx='MTS';
	}elseif($tipesk=='Demosi'){
		$jenispersetujuanx='DMS';
	}else{
		$jenispersetujuanx='';
	}

	$arrstatusapproval=array(0=>'dalam proses',1=>'disetujui',2=>'ditolak');
	$arrstatusx=array(0=>'belum diajukan',9=>'sudah diajukan',1=>'sudah diajukan',2=>'sudah diajukan');
	$strxz = "select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenispersetujuanx."' ";
	$dataxz = fetchData($strxz);
	$countApp = count($dataxz);
	if($countApp>0)
	{
		foreach ($dataxz as $key => $val) {
			$penyetuju[$val['level']]['karyawanid']=$val['karyawanid'];
			$penyetuju[$val['level']]['status']=$val['status'];
			$penyetuju[$val['level']]['komentar']=$val['komentar'];
			$penyetuju[$val['level']]['tanggal']=$val['tanggal'];
		}
	}
	
	$bagix=ceil(($countApp)/2);
	$tipe1=$countApp-$bagix;
	$tipe2=$countApp-$tipe1;

	if($tipe1==0)
	{
		$tipe1=3;
		$tipe2=3;
	}
	else
	{
		$tipe1+=1;
	}

	if ($tipe1<3) {
		$tipe1=3;
	}
	$panpan=$tipe1-1;
	$tab.="<table border=1 cellspacing=0>";
	$tab.="<tr>
	<td colspan='".$panpan."' align=left style='font-size:12pt;'>Tanda Tangan Persetujuan <i>/ Approval Signature</i></td>
	<td align=left style='font-size:12pt;'></td>
	</tr>";			
	$tab.="<tr>
	<td align=left style='font-size:12pt;'>Yang Mengajukan <i>/ Proposed by :</i></td>
	<td colspan='".$panpan."' align=left style='font-size:12pt;'>Atasan langsung  <i>/ Approved by :</i></td>
	</tr>";	
	$tab.="<tr>";
	if ($tipe1<3) {
		$tipe1=3;
	}
	for ($i=1; $i <=$tipe1; $i++) { 
		if($i==1)
		{
		$tab.="<td align=left style='font-size:12pt;background-color: gray;'>".$optnmjabatan[$optkodjab[$diajukanoleh]]."</td>";
		}
		else
		{
			if($countApp==0)
			{
				if($i==2)
				{
				$tab.="<td align=left style='font-size:12pt;background-color: gray;'>General Manager</td>";
				}
				elseif($i==3)
				{
				$tab.="<td align=left style='font-size:12pt;background-color: gray;'>Plantation Controller</td>";
				}
				else
				{}
			}
			else
			{
				$tab.="<td align=left style='font-size:12pt;background-color: gray;'>".$optnmjabatan[$optkodjab[$penyetuju[($i-1)]['karyawanid']]]."</td>";

			}
		}
	}
	$tab.="</tr>";	

	$tab.="<tr>";if ($tipe1<3) {
		$tipe1=3;
	}
	for ($i=1; $i <=$tipe1; $i++) { 
		if($i==1)
		{
			$tab.="<td align=left style='font-size:12pt;height:100px;'>".$arrstatusx[$statuspersetujuan]."</td>";
		}
		else
		{
			if($countApp>0)
			{
			$tab.="<td align=left style='font-size:12pt;height:100px;'>".$arrstatusapproval[$penyetuju[($i-1)]['status']]."</td>";
			}
			else
			{
			$tab.="<td align=left style='font-size:12pt;height:100px;'></td>";
			}
		}
	}
	$tab.="</tr>";	


	$tab.="<tr>";if ($tipe1<3) {
		$tipe1=3;
	}
	for ($i=1; $i <=$tipe1; $i++) { 
		if($i==1)
		{
		$tab.="<td align=left style='font-size:12pt;'>".$optnmkary[$diajukanoleh]."</td>";

		}
		else
		{
			if($countApp>0)
			{
			$tab.="<td align=left style='font-size:12pt;'>".$optnmkary[$penyetuju[($i-1)]['karyawanid']]."</td>";
			}
			else
			{
			$tab.="<td align=left style='font-size:12pt;'></td>";
			}
		}
	}
	$tab.="</tr>";

	$tab.="<tr>";if ($tipe1<3) {
		$tipe1=3;
	}
	for ($i=1; $i <=$tipe1; $i++) { 
		if($i==1)
		{
		$tab.="<td align=left style='font-size:12pt;'>".tanggalnormal($tanggalaju)."</td>";
			
		}
		else
		{
			if($countApp>0)
			{
			$tab.="<td align=left >".tanggalnormal($penyetuju[($i-1)]['tanggal'])."</td>";
			}
			else
			{
			$tab.="<td align=left style='font-size:12pt;'></td>";
			}
		}
	}
	$tab.="</tr>";	


	
	if($tipe2>0)
	{
		$tab.="<tr>";if ($tipe2<3) {
			$tipe2=3;
		}
		for ($i=1; $i <=$tipe2; $i++) { 
			if($countApp==0)
			{
				if($i==1)
				{
				$tab.="<td align=left style='font-size:12pt;background-color: gray;'>HCM - Jakarta</td>";
				}
				elseif($i==2)
				{
				$tab.="<td align=left style='font-size:12pt;background-color: gray;'>Direksi</td>";
				}
				elseif($i==3)
				{
				$tab.="<td align=left style='font-size:12pt;background-color: gray;'>Direksi</td>";
				}
				else
				{}
			}
			else
			{
				$tab.="<td align=left style='font-size:12pt;background-color: gray;'>".$optnmjabatan[$optkodjab[$penyetuju[($i-1+$tipe1)]['karyawanid']]]."</td>";

			}	
		}
		$tab.="</tr>";	

		$tab.="<tr>";if ($tipe2<3) {
			$tipe2=3;
		}
		for ($i=1; $i <=$tipe2; $i++) { 
			if($countApp>($tipe1-1))
			{
			$tab.="<td align=left style='font-size:12pt;height:100px;'>".$arrstatusapproval[$penyetuju[($i-1+$tipe1)]['status']]."</td>";
			}
			else
			{
			$tab.="<td align=left style='font-size:12pt;height:100px;'></td>";
			}
		}
		$tab.="</tr>";	


		$tab.="<tr>";
		if ($tipe<3) {
			$tipe2=3;
		}
		for ($i=1; $i <=$tipe2; $i++) { 
			if($countApp>($tipe1-1))
			{
			$tab.="<td align=left style='font-size:12pt;'>".$optnmkary[$penyetuju[($i-1+$tipe1)]['karyawanid']]."</td>";
			}
			else
			{
			$tab.="<td align=left style='font-size:12pt;'></td>";
			}
		}
		$tab.="</tr>";
		
		$tab.="<tr>";
		if ($tipe2<3) {
			$tipe2=3;
		}
		for ($i=1; $i <=$tipe2; $i++) { 
			if($countApp>($tipe1-1))
			{
			$tab.="<td align=left >".tanggalnormal($penyetuju[($i-1+$tipe1)]['tanggal'])."</td>";
			}
			else
			{
			$tab.="<td align=left  style='font-size:12pt;'></td>";
			}
		}
	

	}
	
	
	$tab.="</table>";
	/*echo $tab;
	exit();*/
	$dompdf = new Dompdf();
	$dompdf->loadHtml($tab);
	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render();
	$dompdf->stream("".$tipesk."_".$optnmkary[$karyawanid]."_".$nomorsk."", array("Attachment" => false)); #mahe


} else if ($method == 'form_ajukan') {
	$nosk = checkPostGet('nosk','');
	$karyawanid = checkPostGet('karyawanid','');
	$kodeorg = checkPostGet('kodeorg','');
	$tipesk = checkPostGet('tipesk','');

		$namakaryawan = '';
	    $strx = "select namakaryawan,lokasitugas,kodegolongan,bagian from " . $dbname . ".datakaryawan where karyawanid=" . $karyawanid;
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_OBJ);
	    while ($barx = $resx->fetch()) {
	        $namakaryawan = $barx->namakaryawan;
	        $lokasitugas = $barx->lokasitugas;
	        $departemen = $barx->bagian;
	        $kodegolongan = $barx->kodegolongan;
	    }

		$str = "select karyawanid from ".$dbname.".sdm_riwayatjabatan where posting='0' and karyawanid='".$karyawanid."' and nomorsk !='".$nosk."' and statuspersetujuan != 2"; 
		$res = fetchdata($str);
		if(count($res)>0)
		{ 
		  exit("Warning : Masih terdapat promosi,demosi,mutasi datakaryawan ini yang belum di posting, silahkan di posting terlebih dahulu");
		}

		if($tipesk=='Promosi'){
			$jenispersetujuanx='PRM';
		}elseif($tipesk=='Mutasi'){
			$jenispersetujuanx='MTS';
		}elseif($tipesk=='Demosi'){
			$jenispersetujuanx='DMS';
		}else{
			$jenispersetujuanx='';
		}

		$tab.="<div>Nama : " . $namakaryawan . "</div>";
		$tab.="<div>Nomor : ".$nosk."</div><br>";
		$tab.="<table cellspacing=1 border=0>
		<tr class=rowcontent hidden>
			<td>" . $namakaryawan . "</td>
			<td width=5px>:</td>
			<td id=notran_aju>".$nosk."</td>
		</tr>";

		//$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$karyawanid."'");
        //$departemen=$optdepartmen[$_SESSION['standard']['userid']];
        
        ##CEK PER DEPARTEMEN
        // $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$_SESSION['empl']['lokasitugas']."' and jenispersetujuan='".$jenispersetujuanx."' and departemen='".$departemen."'";
        $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$lokasitugas."' and jenispersetujuan='".$jenispersetujuanx."' and departemen='".$departemen."'";
        $res=fetchdata($str);
        $perdepartemen=$res[0]['kodeunit'];
        $where="";
        if($perdepartemen>0){
            $where.=" and departemen='".$departemen."'";
        }else{
            $where.=" and departemen=''";
        }

	    $optgol 	= makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');
        $golongan=substr($optgol[$kodegolongan],0,1);
        
        ##CEK PER GOLONGAN
        // $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$_SESSION['empl']['lokasitugas']."' and jenispersetujuan='".$jenispersetujuanx."' and golongan='".$golongan."'";
        $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$lokasitugas."' and jenispersetujuan='".$jenispersetujuanx."' and golongan='".$golongan."'";
		//exit('error : '.$str);
        $res=fetchdata($str);
        $perdepartemen=$res[0]['kodeunit'];
        if($perdepartemen>0){
            $where.=" and golongan='".$golongan."'";
        }else{
            $where.=" and golongan=''";
        }

    ## APPROVAL DINAMIS SESUAI SETUP##    
		$optKryx=array();
		$optKrylevel=array();

	    $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	    $str="select * from ".$dbname.".setup_approval 
	            where jenispersetujuan='".$jenispersetujuanx."' and kodeunit='".$lokasitugas."' and karyawaniduser='".$karyawanid."' ".$where." order by level asc";  
	    $res=fetchData($str);
	    if(count($res) > 0){
	        foreach($res as $key => $bar){
	            $whr		=" karyawanid='".$bar['karyawanid']."'";
	            $optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	           
				$optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
				$optKrylevel[$bar['level']]=$bar['level'];
	        }
	    }else{
	        
	        // $str="select * from ".$dbname.".setup_approval 
	        // where jenispersetujuan='".$jenispersetujuanx."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and karyawaniduser='' ".$where." ";
	        $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuanx."' and kodeunit='".$lokasitugas."' and karyawaniduser='' ".$where." ";
	        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	        $res->setFetchMode(PDO::FETCH_ASSOC);
	        while($bar=$res->fetch()){
	            $whr		=" karyawanid='".$bar['karyawanid']."'";
	            $optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	            
	            $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
	            $optKrylevel[$bar['level']]=$bar['level'];

	        }
	    }

		
		$jumlahlevel=count($optKrylevel);
		if($jumlahlevel>0)
		{
			for ($i=1; $i <= $jumlahlevel; $i++) { 
				$optKry='';
				foreach ($optKryx[$i] as $key2 => $val) {
					$optKry.=$val;
				}
					$tab .= "<tr class=rowcontent>
						<td>Approval ke-".$i."</td>
						<td width=5px>:</td>
						<td><select id=kepada".$i." style='width:200px;'>".$optKry."</select></td>
					</tr>";
			}
		}else{			
			$jumlahlevel=1;
				$tab .= "<tr class=rowcontent>
					<td>Approval ke-1</td>
					<td width=5px>:</td>
					<td><select id=kepada1 style='width:200px;'></select></td>
				</tr>";
		}
				$tab .= "<tr class=rowcontent>
					<td hidden><input id=jenispersetujuanx style=display:none value=".$jenispersetujuanx."></td><td><input id=numrow style=display:none value=".$jumlahlevel."></td>
					<td align=left></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>
				</table>";

        echo $tab;

} else if ($method == 'ajukan') {
	$notransaksi = checkPostGet('notransaksi','');
	$kepada = checkPostGet('kepada','');
	$jenispersetujuanx = checkPostGet('jenispersetujuanx','');

	
		if($kepada==''){
			throw new PDOException('Isikan nama penyetuju.');
		}
		//update flag menjadi 1
        $str2 = "update " . $dbname . ".sdm_riwayatjabatan set statuspersetujuan='9' where nomorsk = '" . $notransaksi . "'";
		//insert ke table approval
		$str='';
		$arrkepada=explode('###', $kepada);
		for ($i=0; $i < (count($arrkepada)); $i++) { 
			$str .= "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
	                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
	            values ('','".$notransaksi."','".$jenispersetujuanx."','".($i+1)."','" . $arrkepada[$i]."','0','','','');";
		} 
		

} else if ($method == 'delete') {
	$nosk = checkPostGet('nosk','');
	$str = "delete from " . $dbname . ".sdm_riwayatjabatan where karyawanid=" . $karyawanid . " and nomorsk='" . $nosk . "'";
	// cek jika ketika delete nosk tersebut memiliki approval 
	$qApproval = selectQuery($dbname, 'approval', '*', "notransaksi = '".$nosk."'");
	$resApproval = fetchData($qApproval);
	if (count($resApproval) > 0) {
		$delApproval = deleteQuery($dbname, 'approval', "notransaksi = '".$nosk."'");
		try {
			$owlPDO->exec($delApproval);
		} catch (PDOException $e) {
			echo "Error: ".$e->getMessage();
		}
	}

} else if ($method == 'update') {
	$str = "update " . $dbname . ".sdm_riwayatjabatan set
	`tanggalsk`=" . $tanggalsk . ",
	`mulaiberlaku`=" . $tanggalberlaku . ",
	`darikodeorg`='" . $oldokasitugas . "',
	`darisubbagian`='" . $oldsubbagian . "',
	`darikodejabatan`=" . $oldjabatan . ",
	`daritipe`=" . $oldtipekaryawan . ",
	`tipesk`='" . $tipetransaksi . "',
	`darikodegolongan`='" . $oldgolongan . "',
	`kekodeorg`='" . $newlokasitugas . "',
	`kesubbagian`='" . $newsubbagian . "',
	`kekodejabatan`=" . $newjabatan . ",
	`ketipekaryawan`=" . $newtipekaryawan . ",
	`kekodegolongan`='" . $newgolongan . "',
	`namadireksi`='" . $penandatangan . "',
	`tembusan1`='" . $tembusan1 . "',
	`tembusan2`='" . $tembusan2 . "',
	`tembusan3`='" . $tembusan3 . "',
	`tembusan4`='" . $tembusan4 . "',
	`updateby`=" . $_SESSION['standard']['userid'] . ",
	`bagian`='" . $olddepartemen . "',
	`kebagian`='" . $newdepartemen . "',
	`tembusan5`='" . $tembusan5 . "',
	`namajabatan`='" . $namajabatan . "',
	`pg1`='" . $paragraf1 . "',
	`pg2`='" . $paragraf2 . "',
	`pg3`='" . $paragraf3 . "',
	`pg4`='" . $paragraf4 . "',
	`pg5`='" . $paragraf5 . "',
	`pg6`='" . $paragraf6 . "',    
	`menimbang`='" . $menimbang . "',    
	`mengingat`='" . $mengingat . "'    
	where `karyawanid`=" . $karyawanid . " and `nomorsk`='" . $noskedit . "'";

	// $dataGajilama = array();
	// $arrold = array();
	// $dataGajibaru = array();
	// $arrnew = array();
	// if (!empty($oldkomponen)) {
	// 	$arrold = explode('###', $oldkomponen);
	// 	$arroldx = array();
	// 	foreach ($arrold as $key => $val) {
	// 		$arroldx = explode('XXX', $val);
	// 		$dataGajilama[] = array(
	// 			'karyawanid' => $karyawanid,
	// 			'nomorsk' => $noskedit,
	// 			'idkomponen' => $arroldx[0],
	// 			'rupiah' => $arroldx[1],
	// 			'status' => 'O',
	// 		);
	// 	}

	// }
	// if (!empty($newkomponen)) {
	// 	$arrnew = explode('###', $newkomponen);
	// 	$arrnewx = array();
	// 	foreach ($arrnew as $key => $val) {
	// 		$arrnewx = explode('XXX', $val);
	// 		$dataGajibaru[] = array(
	// 			'karyawanid' => $karyawanid,
	// 			'nomorsk' => $noskedit,
	// 			'idkomponen' => $arrnewx[0],
	// 			'rupiah' => $arrnewx[1],
	// 			'status' => 'N',
	// 		);
	// 	}
	// }

	
	// $strdel = "delete from " . $dbname . ".sdm_riwayatjabatan_gaji where karyawanid=" . $karyawanid . " and nomorsk='" . $noskedit . "'";
	// try{
	// 	$owlPDO->exec($strdel); 
	// }catch (PDOException $e){
	// 	echo "DB delete Error: " . addslashes($e->getMessage());
	// }
	
	// if (!empty($dataGajilama)) {
	// 	$str2 = insertQuery($dbname, 'sdm_riwayatjabatan_gaji', $dataGajilama);
	// }
	// if (!empty($dataGajibaru)) {
	// 	$str2xy = insertQuery($dbname, 'sdm_riwayatjabatan_gaji', $dataGajibaru);
	// }
	
} else if ($method == 'post') {
	try{
		$owlPDO->beginTransaction();

		$strz = "select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$datakar = fetchData($strz);

		$strz = "select * from ".$dbname.".sdm_riwayatjabatan where nomorsk='".$noskedit."'";
		$datask = fetchData($strz);
		$darikodeorg=$datask[0]['darikodeorg'];
		$kodeorgsblm=$datask[0]['kekodeorg'];


				
		$sql = "select distinct lokasitugas, periodegaji from ".$dbname.".datakaryawan_hist a where lokasitugas = '".$darikodeorg."' and periodegaji='".periodelalu(substr($datask[0]['mulaiberlaku'],0,7))."' and approval_status='8' and version_type='B' group by lokasitugas, periodegaji";
		$req = fetchdata($sql);
		$jumData_Dari = count($req);

		if( $jumData_Dari == 0){
			exit("Warning : Datakaryawan belum diposting unit : ".$darikodeorg."");
		}

		$sql = "select distinct lokasitugas, periodegaji from ".$dbname.".datakaryawan_hist a where lokasitugas = '".$kodeorgsblm."' and periodegaji='".periodelalu(substr($datask[0]['mulaiberlaku'],0,7))."' and approval_status='8' and version_type='B' group by lokasitugas, periodegaji";
		$req = fetchdata($sql);
		$jumData_Sblm = count($req);

		if($jumData_Sblm == 0){
			exit("Warning : Datakaryawan belum diposting unit : ".$kodeorgsblm."");
		}

		// $strz = "select min(periode) as periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$darikodeorg."' and sudahproses='0' ";
		// $strzwr = fetchData($strz);
		$periode= substr($datask[0]['mulaiberlaku'],0,7);
		
		// if(substr($datask[0]['mulaiberlaku'],0,7)!=$periode and getNamaOrg($kodeorgsblm,'tipe')!='HOLDING'){
		// 	throw new PDOException("code [810] : Periode  berlaku : ".substr($datask[0]['mulaiberlaku'],0,7)." dan periode '".$darikodeorg."' min yang masih aktif : ".$periode.", silahkan disesuaikan dengan periode SK berlaku.");
		// }

		$strz = "select * from ".$dbname.".datakaryawan_hist where karyawanid='".$karyawanid."' and periodegaji ='".substr($datask[0]['mulaiberlaku'],0,7)."' and approval_status='9' ";
		$res = fetchData($strz);
		if(count($res) > 0){
			throw new PDOException("Karyawan tersebut dalam tahapan persetujuan atas perubahan datakaryawan");
		}

		$strz = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$karyawanid."' and tanggal like '%".substr($datask[0]['mulaiberlaku'],0,7)."%' ";
		$res = fetchData($strz);
		if(count($res) > 0){
			throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu Absensi , silahkan hapus transaksi tersebut terlebih dahulu");
		}

		$strz = "select * from ".$dbname.".sdm_lemburdt where karyawanid='".$karyawanid."' and tanggal like '%".substr($datask[0]['mulaiberlaku'],0,7)."%' ";
		$res = fetchData($strz);
		if(count($res) > 0){
			throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu Lembur , silahkan hapus transaksi tersebut terlebih dahulu");
		}

		$strz = "select * from ".$dbname.".vhc_runhk where idkaryawan='".$karyawanid."' and tanggal like '%".substr($datask[0]['mulaiberlaku'],0,7)."%' ";
		$res = fetchData($strz);
		if(count($res) > 0){
			throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu Traksi , silahkan hapus transaksi tanggal ".$res[0]['tanggal']." tersebut terlebih dahulu");
		}

		$strz = "select * from ".$dbname.".vhc_penggantiandt_karyawan where karyawanid='".$karyawanid."' and notransaksi like '".str_replace('-', '/', $periode)."%' ";
		$res = fetchData($strz);
		if(count($res) > 0){
			throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu Service , silahkan hapus transaksi tersebut terlebih dahulu");
		}

		$strz = "select * from ".$dbname.".kebun_prestasi_vw where karyawanid='".$karyawanid."' and tanggal like '%".substr($datask[0]['mulaiberlaku'],0,7)."%' ";
		$res = fetchData($strz);
		if(count($res) > 0){
			throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu BKM Panen , silahkan hapus transaksi tersebut terlebih dahulu");
		}

		$strz = "select * from ".$dbname.".kebun_kehadiran_vw where karyawanid='".$karyawanid."' and tanggal like '%".substr($datask[0]['mulaiberlaku'],0,7)."%'";
		$res = fetchData($strz);
		if(count($res) > 0){
			throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu BKM Pemeliharaan , silahkan hapus transaksi tanggal ".$res[0]['tanggal']." tersebut terlebih dahulu");
		}

		$dakarbulanan=0;
		$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$datask[0]['darikodeorg']."' and periodegaji='".$periode."' and karyawanid='".$karyawanid."'"; 
		$res = fetchdata($str);
		if(count($res)>0){ 
		  $dakarbulanan=1;
		}

		if($datask[0]['ketipekaryawan']==4){
			$sistemgaji='Harian';
		}else{
			$sistemgaji='Bulanan';
		}

        $optPT = makeOption($dbname,'organisasi','kodeorganisasi,induk');
		
		## Ambil setup format NIK
		$str="select count(kodept) as jlhitem,tipekaryawan,jumlahcounter, counter, tmk from ".$dbname.".sdm_5formatnik where kodept = '".$optPT[$datask[0]['kekodeorg']]."' and tipekaryawan like '%".$datask[0]['ketipekaryawan']."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);   
		$bar=$res->fetch();
		$cek_data = $bar['jlhitem'];
		$list_tpkar = $bar['tipekaryawan'];
		$jumlahcounter = $bar['jumlahcounter'];
		$counter = $bar['counter'];
		$tmk = $bar['tmk'];

		## Cek sudah disetup kan atau belum
		if($cek_data == '' || $cek_data == 0 || $cek_data < 1 ){
			exit("Warning : Setup format NIK belum ada, silahkan disetupkan terlebih dahulu untuk PT = <b> ".getNamaOrg($optPT[$datask[0]['kekodeorg']])."" );
		}

		$str="select max(substr(nik,- ".$jumlahcounter.")) as noUrut from ".$dbname.".datakaryawan where kodeorganisasi = '".$optPT[$datask[0]['kekodeorg']]."' and lokasitugas='".$datask[0]['kekodeorg']."' and tipekaryawan in (".$list_tpkar.") ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);   
		$bar=$res->fetch();
		$nourut = intval($bar['noUrut']);
		$nourut = $nourut + $counter;
		$nourut = str_pad($nourut, $jumlahcounter, "0", STR_PAD_LEFT);

		$time = strtotime($datask[0]['mulaiberlaku']);
		$firstnik = date('m',$time);
		$sectnik = date('y',$time);

		## Cek pakai TMK tidak
		if($tmk == 1){
			$nik=$optPT[$datask[0]['kekodeorg']].$datask[0]['kekodeorg'].$firstnik.$sectnik.$nourut;
		}else{
			$nik=$optPT[$datask[0]['kekodeorg']].$datask[0]['kekodeorg'].$nourut;
		}	


        if($optPT[$datask[0]['kekodeorg']]==$optPT[$datask[0]['darikodeorg']]){
			ceknonik($nik);
			## Jika dari KHL Ke KHT
			if($datask[0]['ketipekaryawan'] == 3 and $datask[0]['daritipe'] ==  4  and $datask[0]['tipesk'] =='Promosi'){
				$dataUpd = array(
					'nik' => $nik,
					'kodeorganisasi' => $optPT[$datask[0]['kekodeorg']],
					'lokasitugas' => $datask[0]['kekodeorg'],
					'subbagian' => $datask[0]['kesubbagian'],
					'kodejabatan' => $datask[0]['kekodejabatan'],
					'tipekaryawan' => $datask[0]['ketipekaryawan'],
					'kodegolongan' => $datask[0]['kekodegolongan'],
					'bagian' => $datask[0]['kebagian'],
					'tanggalpengangkatan' => $datask[0]['mulaiberlaku'],
					'tanggalsk' => $datask[0]['mulaiberlaku'],
					'sistemgaji' => $sistemgaji
				);
			}else{
				$dataUpd = array(
					'kodeorganisasi' => $optPT[$datask[0]['kekodeorg']],
					'lokasitugas' => $datask[0]['kekodeorg'],
					'subbagian' => $datask[0]['kesubbagian'],
					'kodejabatan' => $datask[0]['kekodejabatan'],
					'tipekaryawan' => $datask[0]['ketipekaryawan'],
					'kodegolongan' => $datask[0]['kekodegolongan'],
					'bagian' => $datask[0]['kebagian'],
					'tanggalsk' => $datask[0]['mulaiberlaku'],
					'sistemgaji' => $sistemgaji
				);
			}
        }else{

			## Jika dari KHL Ke KHT
			if($datask[0]['ketipekaryawan'] == 3 and $datask[0]['daritipe'] ==  4 and $datask[0]['tipesk'] =='Promosi'){
				ceknonik($nik);
				$dataUpd = array(
					'nik' => $nik,
					'kodeorganisasi' => $optPT[$datask[0]['kekodeorg']],
					'lokasitugas' => $datask[0]['kekodeorg'],
					'subbagian' => $datask[0]['kesubbagian'],
					'kodejabatan' => $datask[0]['kekodejabatan'],
					'tipekaryawan' => $datask[0]['ketipekaryawan'],
					'kodegolongan' => $datask[0]['kekodegolongan'],
					'bagian' => $datask[0]['kebagian'],
					'tanggalpengangkatan' => $datask[0]['mulaiberlaku'],
					'tanggalsk' => $datask[0]['mulaiberlaku'],
					'sistemgaji' => $sistemgaji
				);   
			}else{
				$dataUpd = array(
					'kodeorganisasi' => $optPT[$datask[0]['kekodeorg']],
					'lokasitugas' => $datask[0]['kekodeorg'],
					'subbagian' => $datask[0]['kesubbagian'],
					'kodejabatan' => $datask[0]['kekodejabatan'],
					'tipekaryawan' => $datask[0]['ketipekaryawan'],
					'kodegolongan' => $datask[0]['kekodegolongan'],
					'bagian' => $datask[0]['kebagian'],
					'tanggalsk' => $datask[0]['mulaiberlaku'],
					'sistemgaji' => $sistemgaji
				);   
			} 	
        }

		if($dakarbulanan==0){
			$qUpdData = updateQuery($dbname,'datakaryawan',$dataUpd,"karyawanid='".$karyawanid."'");
			$owlPDO->exec($qUpdData);

			// insert history 
			$data = $dataUpd;
				foreach ($dataUpd as $keys => $val) {
					$cols[] = $keys;
				}

				$textchange='';
				foreach ($dataUpd as $field => $val) {
					if($textchange==''){
						$textchange='###'.$field.'###';
					}else{
						$textchange.=$field.'###';
					}
				}

				// penambahan data
				$selectQuery = selectQuery($dbname, 'datakaryawan', '*', "karyawanid='".$karyawanid."'");
				$res = fetchData($selectQuery);
				$newarrData = [
					'tanggallahir' => $res[0]['tanggallahir'],
					'tempatlahir' => $res[0]['tempatlahir'],
					'namakaryawan' => $res[0]['namakaryawan'],
					'namakaryawan2' => $res[0]['namakaryawan2'],
					'nik' => $res[0]['nik'],
					'karyawanid' => $karyawanid,
					'datachange' => $textchange,
					'tanggalmenikah' => $res[0]['tanggalmenikah'],
					'golongandarah' => $res[0]['golongandarah'],
					'levelpendidikan' => $res[0]['levelpendidikan'],
					'alamataktif' => $res[0]['alamataktif'],
					'provinsi' => $res[0]['provinsi'],
					'kota' => $res[0]['kota'],
					'kodepos' => $res[0]['kodepos'],
					'noteleponrumah' => $res[0]['noteleponrumah'],
					'nohp' => $res[0]['nohp'],
					'norekeningbank' => $res[0]['norekeningbank'],
					'namabank' => $res[0]['namabank'],
					'pemilikrekening' => $res[0]['pemilikrekening'],
					'no_keluarga' => $res[0]['no_keluarga'],
					'noktp' => $res[0]['noktp'],
					'tanggalmasuk' => $res[0]['tanggalmasuk'],
					'tanggalpengangkatan' => $res[0]['tanggalpengangkatan'],
					'tanggalkeluar' => $res[0]['tanggalkeluar'],
					'jumlahanak' => $res[0]['jumlahanak'],
					'jumlahtanggungan' => $res[0]['jumlahtanggungan'],
					'statuspajak' => $res[0]['statuspajak'],
					'npwp' => $res[0]['npwp'],
					'bpjs' => $res[0]['bpjs'],
					'lokasipenerimaan' => $res[0]['lokasipenerimaan'],
					'email' => $res[0]['email'],
					'jms' => $res[0]['jms'],
					'updateby' => $_SESSION['standard']['userid'],
					'pensiun' => $res[0]['pensiun'],
					'supbpjs' => $res[0]['supbpjs'],
					'kppnpwp' => $res[0]['kppnpwp'],
					'approval_status' => 1,
					'updatetime' => date('Y-m-d H:i:s'),
					'periodegaji' => $periode,
					'version' => 2,
					'version_type' => 'C',
					'kabupaten' => $res[0]['kabupaten'],
					'kecamatan' => $res[0]['kecamatan'],
					'desa' => $res[0]['desa'],
					'diajukan' => $_SESSION['standard']['userid'],
					'insstatuspajak' => $res[0]['insstatuspajak'],
					'kodecatu' => $res[0]['kodecatu'],
					'subdept' => $res[0]['subdept'],
					'photo' => $res[0]['photo'],
					'emailkantor' => $res[0]['emailkantor'],
					'notelepondarurat' => $res[0]['notelepondarurat'],
					'warganegara' => $res[0]['warganegara'],
					'bulandaftarbpjs' => $res[0]['bulandaftarbpjs'],
					'tmkjamsostek' => $res[0]['tmkjamsostek'],
					'nopaspor' => $res[0]['nopaspor'],
					'nohp2' => $res[0]['nohp2'],
					'nosk' => $noskedit,
				];

				// Hapus elemen 'nik' dan 'tanggalpengangkatan' jika 'tipekaryawan' adalah 3 ke 4 dan tipesk promosi
				if($datask[0]['ketipekaryawan'] == 3 and $datask[0]['daritipe'] ==  4 and $datask[0]['tipesk'] =='Promosi'){
					unset($newarrData['nik']);
					unset($newarrData['tanggalpengangkatan']);
				}

				foreach ($newarrData as $keys => $val) {
					$data[$keys] = $val;
					$cols[] = $keys;
				}
				
		

				$insertQuery = insertQuery($dbname,'datakaryawan_hist',$data,$cols);
				
				$owlPDO->exec($insertQuery);
    	} else {

			$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$datask[0]['darikodeorg']."' and periodegaji='".$periode."' and karyawanid='".$karyawanid."'"; 
			$res = fetchdata($str);
			
			$qUpdData = updateQuery($dbname,'datakaryawan_hist',$dataUpd,"approval_status='8' and version_type='B' and lokasitugas='".$datask[0]['darikodeorg']."' and periodegaji>='".$periode."' and karyawanid='".$karyawanid."'");
			$owlPDO->exec($qUpdData);

			$qUpdData = updateQuery($dbname,'datakaryawan',$dataUpd,"karyawanid='".$karyawanid."'");
			$owlPDO->exec($qUpdData);

			// insert history 
			$data = $dataUpd;
				foreach ($dataUpd as $keys => $val) {
					$cols[] = $keys;
				}

				$textchange='';
				foreach ($dataUpd as $field => $val) {
					if($textchange==''){
						$textchange='###'.$field.'###';
					}else{
						$textchange.=$field.'###';
					}
				}

				// penambahan data
				$selectQuery = selectQuery($dbname, 'datakaryawan', '*', "karyawanid='".$karyawanid."'");
				$res = fetchData($selectQuery);
				$newarrData = [
					'tanggallahir' => $res[0]['tanggallahir'],
					'tempatlahir' => $res[0]['tempatlahir'],
					'namakaryawan' => $res[0]['namakaryawan'],
					'namakaryawan2' => $res[0]['namakaryawan2'],
					'nik' => $res[0]['nik'],
					'karyawanid' => $karyawanid,
					'datachange' => $textchange,
					'tanggalmenikah' => $res[0]['tanggalmenikah'],
					'golongandarah' => $res[0]['golongandarah'],
					'levelpendidikan' => $res[0]['levelpendidikan'],
					'alamataktif' => $res[0]['alamataktif'],
					'provinsi' => $res[0]['provinsi'],
					'kota' => $res[0]['kota'],
					'kodepos' => $res[0]['kodepos'],
					'noteleponrumah' => $res[0]['noteleponrumah'],
					'nohp' => $res[0]['nohp'],
					'norekeningbank' => $res[0]['norekeningbank'],
					'namabank' => $res[0]['namabank'],
					'pemilikrekening' => $res[0]['pemilikrekening'],
					'no_keluarga' => $res[0]['no_keluarga'],
					'noktp' => $res[0]['noktp'],
					'tanggalmasuk' => $res[0]['tanggalmasuk'],
					'tanggalpengangkatan' => $res[0]['tanggalpengangkatan'],
					'tanggalkeluar' => $res[0]['tanggalkeluar'],
					'jumlahanak' => $res[0]['jumlahanak'],
					'jumlahtanggungan' => $res[0]['jumlahtanggungan'],
					'statuspajak' => $res[0]['statuspajak'],
					'npwp' => $res[0]['npwp'],
					'bpjs' => $res[0]['bpjs'],
					'lokasipenerimaan' => $res[0]['lokasipenerimaan'],
					'email' => $res[0]['email'],
					'jms' => $res[0]['jms'],
					'updateby' => $_SESSION['standard']['userid'],
					'pensiun' => $res[0]['pensiun'],
					'supbpjs' => $res[0]['supbpjs'],
					'kppnpwp' => $res[0]['kppnpwp'],
					'approval_status' => 1,
					'updatetime' => date('Y-m-d H:i:s'),
					'periodegaji' => $periode,
					'version' => 2,
					'version_type' => 'C',
					'kabupaten' => $res[0]['kabupaten'],
					'kecamatan' => $res[0]['kecamatan'],
					'desa' => $res[0]['desa'],
					'diajukan' => $_SESSION['standard']['userid'],
					'insstatuspajak' => $res[0]['insstatuspajak'],
					'kodecatu' => $res[0]['kodecatu'],
					'subdept' => $res[0]['subdept'],
					'photo' => $res[0]['photo'],
					'emailkantor' => $res[0]['emailkantor'],
					'notelepondarurat' => $res[0]['notelepondarurat'],
					'warganegara' => $res[0]['warganegara'],
					'bulandaftarbpjs' => $res[0]['bulandaftarbpjs'],
					'tmkjamsostek' => $res[0]['tmkjamsostek'],
					'nopaspor' => $res[0]['nopaspor'],
					'nohp2' => $res[0]['nohp2'],
					'nosk' => $noskedit,
				];

				// Hapus elemen 'nik' dan 'tanggalpengangkatan' jika 'tipekaryawan' adalah 3 ke 4 dan tipesk promosi
				if($datask[0]['ketipekaryawan'] == 3 and $datask[0]['daritipe'] ==  4 and $datask[0]['tipesk'] =='Promosi'){
					unset($newarrData['nik']);
					unset($newarrData['tanggalpengangkatan']);
				}

				foreach ($newarrData as $keys => $val) {
					$data[$keys] = $val;
					$cols[] = $keys;
				}

				$insertQuery = insertQuery($dbname,'datakaryawan_hist',$data,$cols);
				$owlPDO->exec($insertQuery);
    	}

		// Update Status Posting
		$dataUpdPost = array('posting' => 2);
		$updPost = updateQuery($dbname,'sdm_riwayatjabatan',$dataUpdPost,"nomorsk ='".$noskedit."'");
		$owlPDO->exec($updPost);
        
		$owlPDO->commit();
	}catch(PDOException $e){
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
	}
}else if ($method == 'unpost') {
	// try{
	// 	$owlPDO->beginTransaction();

	// 	$strz = "select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
	// 	$datakar = fetchData($strz);

	// 	$strz = "select * from ".$dbname.".sdm_riwayatjabatan where nomorsk='".$noskedit."'";
	// 	$datask = fetchData($strz);
	// 	$kodeorgsblm=$datask[0]['kekodeorg'];

	// 	$strz = "select min(periode) as periode from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($kodeorgsblm, 0,4)."'";
	// 	$strzwr = fetchData($strz);
	// 	$periode=$strzwr[0]['periode'];

	// 	$strz = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$karyawanid."' and tanggal like '".$periode."%' ";

	// 	$res = fetchData($strz);
	// 	if(count($res) > 0){
	// 		throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu Absensi , silahkan hapus transaksi tersebut terlebih dahulu");
	// 	}

	// 	$strz = "select * from ".$dbname.".sdm_lemburdt where karyawanid='".$karyawanid."' and tanggal like '".$periode."%' ";
	// 	$res = fetchData($strz);
	// 	if(count($res) > 0){
	// 		throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu Lembur , silahkan hapus transaksi tersebut terlebih dahulu");
	// 	}

	// 	$strz = "select * from ".$dbname.".vhc_runhk where idkaryawan='".$karyawanid."' and tanggal like '".$periode."%'";
	// 	$res = fetchData($strz);
	// 	if(count($res) > 0){
	// 		throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu Traksi , silahkan hapus transaksi tersebut terlebih dahulu");
	// 	}

	// 	$strz = "select * from ".$dbname.".vhc_penggantiandt_karyawan where karyawanid='".$karyawanid."' and notransaksi like '".str_replace('-', '/', $periode)."%' ";
	// 	$res = fetchData($strz);
	// 	if(count($res) > 0){
	// 		throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu Service , silahkan hapus transaksi tersebut terlebih dahulu");
	// 	}

	// 	$strz = "select * from ".$dbname.".kebun_prestasi where nik='".$karyawanid."' and notransaksi like '".str_replace('-', '', $periode)."%' ";
	// 	$res = fetchData($strz);
	// 	if(count($res) > 0){
	// 		throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu BKM Panen , silahkan hapus transaksi tersebut terlebih dahulu");
	// 	}

	// 	$strz = "select * from ".$dbname.".kebun_prestasi where nikpemel='".$karyawanid."' and notransaksi like '".str_replace('-', '', $periode)."%' ";
	// 	$res = fetchData($strz);
	// 	if(count($res) > 0){
	// 		throw new PDOException("Karyawan tersebut sudah terdaptar pada Menu BKM Pemeliharaan , silahkan hapus transaksi tersebut terlebih dahulu");
	// 	}

    //     $optPT = makeOption($dbname,'organisasi','kodeorganisasi,induk');

	// 	$dataUpd = array(
    //                         'kodeorganisasi' => $optPT[$datask[0]['darikodeorg']],
    //                         'lokasitugas' => $datask[0]['darikodeorg'],
    //                         'kodejabatan' => $datask[0]['darikodejabatan'],
    //                         'tipekaryawan' => $datask[0]['daritipe'],
    //                         'kodegolongan' => $datask[0]['darikodegolongan'],
    //                         'bagian' => $datask[0]['bagian']
    //                     );
    //     $qUpdData = updateQuery($dbname,'datakaryawan',$dataUpd,"karyawanid='".$karyawanid."'");
    //     $owlPDO->exec($qUpdData);
		
	// 	// insert history 
	// 	$data = $dataUpd;
	// 	foreach ($dataUpd as $keys => $val) {
	// 		$cols[] = $keys;
	// 	}

	// 	$textchange='';
	// 	foreach ($dataUpd as $field => $val) {
	// 		if($textchange==''){
	// 			$textchange='###'.$field.'###';
	// 		}else{
	// 			$textchange.=$field.'###';
	// 		}
	// 	}

	// 	// penambahan data
	// 	$selectQuery = selectQuery($dbname, 'datakaryawan', '*', "karyawanid='".$karyawanid."'");
	// 	$res = fetchData($selectQuery);
	// 	$newarrData = [
	// 		'tanggallahir' => $res[0]['tanggallahir'],
	// 		'tempatlahir' => $res[0]['tempatlahir'],
	// 		'namakaryawan' => $res[0]['namakaryawan'],
	// 		'namakaryawan2' => $res[0]['namakaryawan2'],
	// 		'nik' => $res[0]['nik'],
	// 		'karyawanid' => $karyawanid,
	// 		'datachange' => $textchange,
	// 		'tanggalmenikah' => $res[0]['tanggalmenikah'],
	// 		'golongandarah' => $res[0]['golongandarah'],
	// 		'levelpendidikan' => $res[0]['levelpendidikan'],
	// 		'alamataktif' => $res[0]['alamataktif'],
	// 		'provinsi' => $res[0]['provinsi'],
	// 		'kota' => $res[0]['kota'],
	// 		'kodepos' => $res[0]['kodepos'],
	// 		'noteleponrumah' => $res[0]['noteleponrumah'],
	// 		'nohp' => $res[0]['nohp'],
	// 		'norekeningbank' => $res[0]['norekeningbank'],
	// 		'namabank' => $res[0]['namabank'],
	// 		'pemilikrekening' => $res[0]['pemilikrekening'],
	// 		'no_keluarga' => $res[0]['no_keluarga'],
	// 		'noktp' => $res[0]['noktp'],
	// 		'tanggalmasuk' => $res[0]['tanggalmasuk'],
	// 		'tanggalpengangkatan' => $res[0]['tanggalpengangkatan'],
	// 		'tanggalkeluar' => $res[0]['tanggalkeluar'],
	// 		'jumlahanak' => $res[0]['jumlahanak'],
	// 		'jumlahtanggungan' => $res[0]['jumlahtanggungan'],
	// 		'statuspajak' => $res[0]['statuspajak'],
	// 		'npwp' => $res[0]['npwp'],
	// 		'bpjs' => $res[0]['bpjs'],
	// 		'lokasipenerimaan' => $res[0]['lokasipenerimaan'],
	// 		'email' => $res[0]['email'],
	// 		'jms' => $res[0]['jms'],
	// 		'updateby' => $_SESSION['standard']['userid'],
	// 		'pensiun' => $res[0]['pensiun'],
	// 		'supbpjs' => $res[0]['supbpjs'],
	// 		'kppnpwp' => $res[0]['kppnpwp'],
	// 		'approval_status' => 1,
	// 		'updatetime' => date('Y-m-d H:i:s'),
	// 		'periodegaji' => $periode,
	// 		'version' => 2,
	// 		'version_type' => 'C',
	// 		'kabupaten' => $res[0]['kabupaten'],
	// 		'kecamatan' => $res[0]['kecamatan'],
	// 		'desa' => $res[0]['desa'],
	// 		'diajukan' => $_SESSION['standard']['userid'],
	// 		'insstatuspajak' => $res[0]['insstatuspajak'],
	// 		'kodecatu' => $res[0]['kodecatu'],
	// 		'subdept' => $res[0]['subdept'],

	// 	];
	// 	foreach ($newarrData as $keys => $val) {
	// 		$data[$keys] = $val;
	// 		$cols[] = $keys;
	// 	}

	// 	$insertQuery = insertQuery($dbname,'datakaryawan_hist',$data,$cols);
	// 	$owlPDO->exec($insertQuery);
		
	// 	// $dataUpd = array('kodeorg' => $datask[0]['darikodeorg']);
    //     // $qUpdData = updateQuery($dbname,'sdm_cutiht',$dataUpd,"karyawanid='".$karyawanid."'");
    //     // $owlPDO->exec($qUpdData);
		
		
    //     // // Get Old Gaji =
    //     // $qOldGaji = selectQuery($dbname,'sdm_5gajipokok',"karyawanid,idkomponen,jumlah",
    //     //            	"karyawanid = '".$karyawanid."' and tahun='".substr($periode, 0,4))."'";
	// 	// //exit("Error:$qOldGaji");
    //     // $resOldGaji = fetchData($qOldGaji);
    //     // $optOldGaji = array();
    //     // foreach($resOldGaji as $row) {
    //     //                 $optOldGaji[$karyawanid][$row['idkomponen']] = $row['jumlah'];
    //     // }
	// 	// //print_r($optOldGaji);exit("Error:A");

    //     // // Get Gaji
    //     // $qGaji = selectQuery($dbname,'sdm_riwayatjabatan_gaji',"karyawanid,idkomponen,rupiah",
    //     //                                                  "nomorsk ='".$noskedit."' and status='O' ");
    //     // $resGaji = fetchData($qGaji);
    //     // $optGaji = array();
    //     // foreach($resGaji as $row) {
    //     //         $optGaji[$karyawanid][$row['idkomponen']] = $row['rupiah'];
    //     // }

	// 	// $tmpPeriod = explode('-',$periode);
	// 	// if(!empty($optGaji[$karyawanid])){
	// 	// 		foreach($optGaji[$karyawanid] as $komp=>$nilai) {
	//     //         // Query Insert
	//     //         $dataInsGaji = array(
	//     //                 'tahun' => $tmpPeriod[0],
	//     //                 'karyawanid' => $karyawanid,
	//     //                 'idkomponen' => $komp,
	//     //                 'jumlah' => $nilai
	//     //         );
	//     //         $qInsGaji = insertQuery($dbname,'sdm_5gajipokok',$dataInsGaji);

	//     //         // Query Update
	//     //         $dataUpdGaji = array('jumlah' => $nilai);
	//     //         $qUpdGaji = updateQuery($dbname,'sdm_5gajipokok',$dataUpdGaji,
	//     //                                                         "karyawanid='".$karyawanid."' 
	//     //                                                         and idkomponen='".$komp."' 
	//     //                                                         and tahun ='".$tmpPeriod[0]."'");

	            
	//     //     	$owlPDO->exec($qUpdGaji);

	//     //         // History Data Gaji
	//     //         $histGaji[] = array(
	//     //                 'updatetime' => date('Y-m-d H:i:s'),
	//     //                 'updateby' => $_SESSION['standard']['userid'],
	//     //                 'karyawanid' => $karyawanid,
	//     //                 'tahun' => $tmpPeriod[1],
	//     //                 'idkomponen' => $komp,
	//     //                 'jumlahlalu' => $optOldGaji[$karyawanid][$komp],
	//     //                 'jumlah' => $nilai
	//     //         );

	//     //     }
	// 	// }


	// 	// Update Status Posting
	// 	$dataUpdPost = array('posting' => 0);
	// 	$updPost = updateQuery($dbname,'sdm_riwayatjabatan',$dataUpdPost,"nomorsk ='".$noskedit."'");
	// 	$owlPDO->exec($updPost);

	// 	$owlPDO->commit();
	// }catch(PDOException $e){
	// 	$owlPDO->rollback();
	// 	echo "Error, " . addslashes($e->getMessage());
	// }
}
else if ($method == 'getgrade') {
	$strz = "select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
	$datakar = fetchData($strz);
	$kodegolongan=$datakar[0]['kodegolongan'];

	if ($tp=='Promosi') {
		$str="select * from ".$dbname.".sdm_5golongan 
		where kodegolongan < '".$kodegolongan."' and  aktif='1' order by kodegolongan";
	}
	else
	{
		$str="select * from ".$dbname.".sdm_5golongan 
		where kodegolongan > '".$kodegolongan."' and  aktif='1' order by kodegolongan";
	}
	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		
		$optgl.="<option value=".$bar['kodegolongan'].">".$bar['namagolongan']."</option>";

	}

	echo $optgl;

}



if ($method != 'post' and $method != 'unpost')
{

	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo "DB Header Error: " . addslashes($e->getMessage());
	}

	try{
		if(isset($str2)){
			$owlPDO->exec($str2); 
		}
	}catch (PDOException $e){
		echo "DB Gaji1 Error: " . addslashes($e->getMessage());
	}

	try{
		if(isset($str2xy)){
			$owlPDO->exec($str2xy); 
		}
	}catch (PDOException $e){
		echo "DB Gaji2 Error: " . addslashes($e->getMessage());
	}

}

?>