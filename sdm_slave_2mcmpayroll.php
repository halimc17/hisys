<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$per=checkPostGet('per','');
$tpKary=checkPostGet('tpKary','');

$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optgol=  makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');

$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$namabank=  makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');

$whrtp="";
if($tpKary!='a'){
	$whrtp=" and tipekaryawan='".$tpKary."'";
}

## Komponen tunjangan tetap
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='KOMTJTETAP'";
$res=fetchdata($str);
$komponentjtetap = $res[0]['nilai'];

## Komponen all bpjs
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRBPJSSLIP'";
$res=fetchdata($str);
$komponentjslip = $res[0]['nilai'];

$str="select distinct(a.idkomponen),plus,name,id from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id in (".$komponentjslip.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkombpjs[$bar['id']]=$bar['id'];
	$nmkom[$bar['id']]=$bar['name'];
}

$str="select distinct(a.idkomponen),plus,name,id from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id in (".$komponentjtetap.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkomtetp[$bar['id']]=$bar['id'];
	$nmkom[$bar['id']]=$bar['name'];
}

$str="select distinct(a.idkomponen),plus,name,id from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id not in (".$komponentjtetap.",".$komponentjslip.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['plus']==1){
		@$dtkomplus[$bar['id']]=$bar['id'];
	}else{
		@$dtkommin[$bar['id']]=$bar['id'];
	}
	$nmkom[$bar['id']]=$bar['name'];
}

## Ambil tarif BPJS JK Karyawan
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRBPJSKER'";
$res=fetchdata($str);
$bpjskar = $res[0]['nilai'];

## RUPIAH BPJS JK KARYAWAN
$str="select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id in (".$bpjskar.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkombpjs_jk_kar[$bar['karyawanid']]+=$bar['rupiah'];
}

## Ambil tarif BPJS JK PERUSAHAAN
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRBPJSPERU'";
$res=fetchdata($str);
$bpjsperusahaan = $res[0]['nilai'];

## RUPIAH BPJS JK PERUSAHAAN
$str="select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id in (".$bpjsperusahaan.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkombpjs_jk_per[$bar['karyawanid']]+=$bar['rupiah'];
}

## Ambil tarif BPJS Kesehatan Karyawan
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRBPJSKES'";
$res=fetchdata($str);
$bpjskes = $res[0]['nilai'];

## RUPIAH BPJS KESEHATAN KARYAWAN
$str="select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id in (".$bpjskes.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkombpjs_kesehatan_kar[$bar['karyawanid']]+=$bar['rupiah'];
}

## Ambil tarif BPJS Kesehatan perusahaan
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRBPJSKESP'";
$res=fetchdata($str);
$bpjskes_perusahaan = $res[0]['nilai'];

## RUPIAH BPJS KESEHATAN PERUSAHAAN
$str="select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id in (".$bpjskes_perusahaan.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkombpjs_kesehatan_per[$bar['karyawanid']]+=$bar['rupiah'];
}

## Ambil tarif BPJS Pensiun Karyawan
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRBPJSPEN'";
$res=fetchdata($str);
$bpjspensiun = $res[0]['nilai'];

## RUPIAH BPJS PENSIUN KARYAWAN
$str="select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id in (".$bpjspensiun.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkombpjs_pensiun_kar[$bar['karyawanid']]+=$bar['rupiah'];
}

## Ambil tarif BPJS Pensiun Karyawan
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRBPJSPENP'";
$res=fetchdata($str);
$bpjspensiun_perusahaan = $res[0]['nilai'];

## RUPIAH BPJS PENSIUN PERUSAHAAN
$str="select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id where id in (".$bpjspensiun_perusahaan.") and kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkombpjs_pensiun_per[$bar['karyawanid']]+=$bar['rupiah'];
}

$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
$tipeOrg=$optTipe[$unit];

## TARIF BPJS KESEHATAN KARYAWAN
$str="select bebankaryawan,jenisbpjs from ".$dbname.".sdm_5bpjs where lokasibpjs = '".$tipeOrg."' and jenisbpjs in(".$bpjskes.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tarifbpjskesehatan_kar +=$bar['bebankaryawan'];
}

## TARIF BPJS KESEHATAN PERUSAHAAN
$str="select bebanperusahaan,jenisbpjs from ".$dbname.".sdm_5bpjs where lokasibpjs = '".$tipeOrg."' and jenisbpjs in(".$bpjskes.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tarifbpjskesehatan_per +=$bar['bebanperusahaan'];
}

## TARIF BPJS TK KARYAWAN
$str="select bebankaryawan,jenisbpjs from ".$dbname.".sdm_5bpjs where lokasibpjs = '".$tipeOrg."' and jenisbpjs in(".$bpjskar.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tarifbpjskar +=$bar['bebankaryawan'];
}

## TARIF BPJS TK PERUSAHAAN
$str="select bebanperusahaan,jenisbpjs from ".$dbname.".sdm_5bpjs where lokasibpjs = '".$tipeOrg."' and jenisbpjs in(".$bpjskar.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tarifbpjsper +=$bar['bebanperusahaan'];
}

## TARIF BPJS JP KARYAWAN
$str="select bebankaryawan,jenisbpjs from ".$dbname.".sdm_5bpjs where lokasibpjs = '".$tipeOrg."' and jenisbpjs in(".$bpjspensiun.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tarifbpjskar_jp +=$bar['bebankaryawan'];
}

## TARIF BPJS TK PERUSAHAAN
$str="select bebanperusahaan,jenisbpjs from ".$dbname.".sdm_5bpjs where lokasibpjs = '".$tipeOrg."' and jenisbpjs in(".$bpjspensiun.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tarifbpjsper_jp +=$bar['bebanperusahaan'];
}

$str="select tipelembur,jamaktual,karyawanid,kodeorg from ".$dbname.".sdm_lemburdt where substr(kodeorg,1,4)='".$unit."' and tanggal like '".$per."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if(strlen($bar['kodeorg'])==4){
		$bar['kodeorg']='';
	}else{
		$bar['kodeorg']=$bar['kodeorg'];
	}

	$bar['kodeorg']=getKary($bar['karyawanid'],'subbagian');
	@$jamlembur[$bar['kodeorg']][$bar['karyawanid']]+=$bar['jamaktual'];
	$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
}

#bentuk list karyawan
$str="select * from ".$dbname.".sdm_gaji_vw a where kodeorg='".$unit."' and periodegaji='".$per."' ".$whrtp." order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtkarid   		[$bar['karyawanid']]=$bar['karyawanid'];
	$listidkar 		[$bar['karyawanid']]=$bar['karyawanid'];
	$nmkar     		[$bar['karyawanid']]=$bar['namakaryawan'];
	$rupiah    		[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
	$bank      		[$bar['karyawanid']]=$bar['namabank'];
	$rekening      	[$bar['karyawanid']]=$bar['norekeningbank'];
}

if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<div class='table-scroll'><table class=sortable cellpadding=7 style='width:100%;' cellspacing=1>";
}

$stream.="<thead><tr class=rowcontent>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
	$stream.="<th></th>";
$stream.="</tr>";	

	$stream.="</thead>";
	$no = 0;
	foreach ($dtkarid as $karid){
		$no++;
		$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td >".$rekening[$karid]."</td>";
			$stream.="<td >".$nmkar[$karid]."</td>";
			$stream.="<td >Batulicin</td>";
			$stream.="<td ></td>";
			$stream.="<td >IDR</td>";

			foreach ($dtkomtetp as $komplus){
				@$ttlTjtetap[$karid] += $rupiah[$karid][$komplus];
				@$gtlTjtetap += $rupiah[$karid][$komplus];
			}

			foreach ($dtkomplus as $komplus){
				@$ttlKomplus[$karid] += $rupiah[$karid][$komplus];
				@$gtlKomplus += $rupiah[$karid][$komplus];
			}

			## TotalBPJS Perusahaan + Peserta
			@$totalbpjs_seluruhnya[$karid] = $dtkombpjs_jk_kar[$karid] + $dtkombpjs_kesehatan_kar[$karid] + $dtkombpjs_pensiun_kar[$karid] + $dtkombpjs_jk_per[$karid] + $dtkombpjs_kesehatan_per[$karid] + $dtkombpjs_pensiun_per[$karid];

			## TotalBPJS Karyawan
			@$totalbpjs_karyawan[$karid] = $dtkombpjs_jk_kar[$karid] + $dtkombpjs_kesehatan_kar[$karid] + $dtkombpjs_pensiun_kar[$karid];

			## TotalBPJS Perusahaan
			@$totalbpjs_perusahaan[$karid] = $dtkombpjs_jk_per[$karid] + $dtkombpjs_kesehatan_per[$karid] + $dtkombpjs_pensiun_per[$karid];

			foreach ($dtkommin as $kommin){
				@$ttlkommin[$karid] += $rupiah[$karid][$kommin];
			}

			## PENGURANG GAJI
			$totalPengurangGaji[$karid] = $ttlkommin[$karid] + $totalbpjs_seluruhnya[$karid];
			$Gtlkommin += $ttlkommin[$karid] + $totalbpjs_seluruhnya[$karid];

			## GAJI BRUTO
			@$gajiBruto[$karid] = $ttlTjtetap[$karid]  + $ttlKomplus[$karid] + $totalbpjs_perusahaan[$karid];
			@$GTgajiBruto += $ttlTjtetap[$karid]  + $ttlKomplus[$karid] + $totalbpjs_perusahaan[$karid];
			
			## GAJI BERSIH
			@$gajiBersih[$karid] = $gajiBruto[$karid] - $totalPengurangGaji[$karid];
			@$GTgajiBersih += $gajiBruto[$karid] - $totalPengurangGaji[$karid];

			$stream.="<td align=right>".number_format($gajiBersih[$karid],0)."</td>";
			$stream.="<td ></td>";
			$stream.="<td ></td>";
			$stream.="<td >IBU</td>";
			$stream.="<td ></td>";
			$stream.="<td >".$namabank[$bank[$karid]]."</td>";
			$stream.="<td >Jakarta</td>";
			$stream.="<td ></td>";
			$stream.="<td ></td>";
			$stream.="<td ></td>";
			$stream.="<td align=center>Y</td>";
			$stream.="<td >".getKary($karid,'email')."</td>";
			$stream.="<td ></td>";
			$stream.="<td ></td>";
			$stream.="<td ></td>";
			$stream.="<td >BEN</td>";
			$stream.="<td >1</td>";
			$stream.="<td >E</td>";
			$stream.="<td ></td>";
			$stream.="<td ></td>";
			$stream.="<td ></td>";
		$stream.="</tr>";	
	}	
$stream.="<tbody></table></div>";
switch($proses){
	case 'getdivisi':
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unit."%' and tipe ='AFDELING' order by kodeorganisasi asc";
		$optdivisi.="<option value=''></option>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}		
			echo $optdivisi;
			break;
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        $tglSkrg=date("Ymd");
        $nop_="MCM PAYROLL KARYAWAN";
        if (strlen($stream) > 0) {
        	if ($handle = opendir('tempExcel')) {
        		while (false !== ($file = readdir($handle))) {
        			if ($file != "." && $file != ".." && $file != "index.html") {
        				 @ unlink('tempExcel/'.$file);
        			}
        		}
        		closedir($handle);
        	}
        	$handle = fopen("tempExcel/".$nop_.".xls", 'w');
        	if (!fwrite($handle, $stream)) {
        		echo "<script language=javascript1.2>
        		parent.window.alert('Can't convert to excel format');
        		</script>";
        		exit;
        	} else {
        		echo "<script language=javascript1.2>
        		window.location='tempExcel/".$nop_.".xls';
        		</script>";
        	}
        	fclose($handle);
        }
        break;
        }
?>