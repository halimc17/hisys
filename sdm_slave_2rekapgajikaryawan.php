<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$per=checkPostGet('per','');



$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');


# ambil dari setup parameter komponen BPJS Plus tidak di tampilkan
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRBPJSPLUS' and kodeorg='".$unit."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
$arrbpjs=explode(',',$bar['nilai']);
foreach($arrbpjs as $key){
	$arrpen[$key]=$key;
}

$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$gjthnlu=$bar['nilai'];

$str="select distinct(a.idkomponen),plus,name,id from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b 
		on a.idkomponen=b.id where  kodeorg='".$unit."' and periodegaji='".$per."' and a.idkomponen not in ('".implode("','",$arrpen)."',".$gjthnlu.") ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
if($row<1){
	exit("Warning:Data Kosong");
}
while($bar=$res->fetch()){
	if($bar['plus']==1){
		@$dtkomplus[$bar['id']]=$bar['id'];
	}else{
		@$dtkommin[$bar['id']]=$bar['id'];
	}
	$nmkom[$bar['id']]=$bar['name'];
}


$str="select distinct(a.idkomponen),plus,name,id from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".sdm_ho_component b 
		on a.idkomponen=b.id where  kodeorg='".$unit."' and periodegaji='".periodelalu($per)."' and a.idkomponen  in (".$gjthnlu.") ";
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



$where1='';
if(@strlen($divisi)=='6'){
	$where1.=" and subbagian='".$divisi."'";
} else if(@strlen($divisi)=='4'){
	$where1.=" and subbagian=''";
}

$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,regional');
$regorg=$regional[$unit];


$str="select tipelembur,jamaktual,karyawanid,kodeorg from ".$dbname.".sdm_lemburdt where substr(kodeorg,1,4)='".$unit."' 
		and tanggal like '".periodelalu($per)."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if(strlen($bar['kodeorg'])==4){
		$bar['kodeorg']='';
	}else{
		$bar['kodeorg']=$bar['kodeorg'];
	}
	@$jamlembur[$bar['kodeorg']][$bar['karyawanid']]+=$bar['jamaktual'];
}




#bentuk list karyawan
$str="select * from ".$dbname.".sdm_gaji_vw where kodeorg='".$unit."' and periodegaji='".$per."' and idkomponen not in (".$gjthnlu.") ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
	$dtafd[$bar['subbagian']]=$bar['subbagian'];
	$listidkar[$bar['subbagian']][$bar['karyawanid']]=$bar['karyawanid'];
	$nik[$bar['subbagian']][$bar['karyawanid']]=$bar['nik'];
	$nmkar[$bar['subbagian']][$bar['karyawanid']]=$bar['namakaryawan'];
	@$stpajak[$bar['subbagian']][$bar['karyawanid']]=$bar['statuspajak'];
	$tpkar[$bar['subbagian']][$bar['karyawanid']]=$bar['tipekaryawan'];
	$jabatan[$bar['subbagian']][$bar['karyawanid']]=$bar['kodejabatan'];
	$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
	
	$bank[$bar['subbagian']][$bar['karyawanid']]=$bar['namabank'];
	$rekening[$bar['subbagian']][$bar['karyawanid']]=$bar['norekeningbank'];
	if($bar['idkomponen']==1){
		$hk[$bar['subbagian']][$bar['karyawanid']]=$bar['hk'];
	}
	
}
// echo '1';
// echo"<pre>";
// print_r($rupiah);
// echo"</pre>";
// $rupiah=array();
$str="select * from ".$dbname.".sdm_gaji_vw where kodeorg='".$unit."' and periodegaji='".periodelalu($per)."' and idkomponen  in (".$gjthnlu.") ";
//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
	$dtafd[$bar['subbagian']]=$bar['subbagian'];
	$listidkar[$bar['subbagian']][$bar['karyawanid']]=$bar['karyawanid'];
	$nik[$bar['subbagian']][$bar['karyawanid']]=$bar['nik'];
	$nmkar[$bar['subbagian']][$bar['karyawanid']]=$bar['namakaryawan'];
	@$stpajak[$bar['subbagian']][$bar['karyawanid']]=$bar['statuspajak'];
	$tpkar[$bar['subbagian']][$bar['karyawanid']]=$bar['tipekaryawan'];
	$jabatan[$bar['subbagian']][$bar['karyawanid']]=$bar['kodejabatan'];
	$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
	
	$bank[$bar['subbagian']][$bar['karyawanid']]=$bar['namabank'];
	$rekening[$bar['subbagian']][$bar['karyawanid']]=$bar['norekeningbank'];
	
	
}

// echo '2';
// echo"<pre>";
// print_r($rupiah);
// echo"</pre>";



/*****************************************************************************************************************/

#catu beras
$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan,b.nik,b.namakaryawan,b.statuspajak,b.kodejabatan,b.namabank,b.norekeningbank from ".$dbname.".sdm_catu a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where lokasitugas='".$unit."'  and periodegaji = '".$per."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkomplus['60']='60';
	@$nmkom['60']='Natura';
	@$rupiah[$bar['subbagian']][$bar['karyawanid']]['60']=$bar['jumlahrupiah'];
	@$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
	@$dtafd[$bar['subbagian']]=$bar['subbagian'];
	@$listidkar[$bar['subbagian']][$bar['karyawanid']]=$bar['karyawanid'];
	$nik[$bar['subbagian']][$bar['karyawanid']]=$bar['nik'];
	$nmkar[$bar['subbagian']][$bar['karyawanid']]=$bar['namakaryawan'];
	@$stpajak[$bar['subbagian']][$bar['karyawanid']]=$bar['statuspajak'];
	$tpkar[$bar['subbagian']][$bar['karyawanid']]=$bar['tipekaryawan'];
	$jabatan[$bar['subbagian']][$bar['karyawanid']]=$bar['kodejabatan'];
	$bank[$bar['subbagian']][$bar['karyawanid']]=$bar['namabank'];
	$rekening[$bar['subbagian']][$bar['karyawanid']]=$bar['norekeningbank'];
}


@$tbrskommin=count($dtkommin)+1;
@$tbrskomplus=count($dtkomplus)+1;

/*****************************************************************************************************************/


if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<table class=sortable cellspacing=1>";
}

@array_multisort($dtafd,SORT_ASC);



$stream.="<thead><tr class=rowcontent>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nomor']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['divisi']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nik2']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jabatan']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tipekaryawan']."</td>";
	$stream.="<td align=center rowspan=2>Status Pajak</td>";
	$stream.="<td align=center rowspan=2>NPWP</td>";
	$stream.="<td align=center rowspan='2'>Bank</td>";
	$stream.="<td align=center rowspan='2'>No. Rekening</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['hk']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['lembur']."</td>";
	
	
	$stream.="<td align=center colspan=".$tbrskomplus.">".$_SESSION['lang']['penambah']."</td>";
	$stream.="<td align=center colspan=".$tbrskommin.">".$_SESSION['lang']['pengurang']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']." Terima</td>";
	
$stream.="</tr>";

$stream.="<tr>";
if(isset($dtkomplus)) 
foreach ($dtkomplus as $komplus){
		$stream.="<td align=center>".$nmkom[$komplus]."</td>";
}
$stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
foreach ($dtkommin as $kommin){
		$stream.="<td align=center>".$nmkom[$kommin]."</td>";
}
$stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
$stream.="</tr>";	
$stream.="</thead>";


foreach ($dtafd as $afd){
	if(@$afd==''){
		@$kdafd='Umum';
	}else{
		@$kdafd=$afd;
	}
	foreach ($dtkarid as $karid){
		if(@$listidkar[$afd][$karid]!=''){
			$no++;
			$stspajak=  makeOption($dbname, 'datakaryawan', 'karyawanid,statuspajak',"karyawanid='".$karid."'");
			$nonpwp=  makeOption($dbname, 'datakaryawan', 'karyawanid,npwp',"karyawanid='".$karid."'");
			
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td>".$kdafd."</td>";
			$stream.="<td>".$nik[$afd][$karid]."</td>";
			$stream.="<td>".$nmkar[$afd][$karid]."</td>";
			$stream.="<td>".$optnmjab[$jabatan[$afd][$karid]]."</td>";
			$stream.="<td>".$nmtipekar[$tpkar[$afd][$karid]]."</td>";
			$stream.="<td>".$stspajak[$karid]."</td>";
			if($proses=='excel'){
				$stream.="<td>".$nonpwp[$karid]."</td>";
			}else{
				$stream.="<td>".$nonpwp[$karid]."</td>";
			}
			$stream.="<td>".$bank[$afd][$karid]."</td>";
			$stream.="<td>".$rekening[$afd][$karid]."</td>";
			$stream.="<td align=right>".@number_format($hk[$afd][$karid],2)."</td>";
			$stream.="<td align=right>".@$jamlembur[$afd][$karid]."</td>";
			
		
			
			foreach ($dtkomplus as $komplus){
				$stream.="<td align=right>".@number_format($rupiah[$afd][$karid][$komplus])."</td>";
				@$tkomplus[$afd][$karid]+=$rupiah[$afd][$karid][$komplus];
				@$subtkomplus[$afd][$komplus]+=$rupiah[$afd][$karid][$komplus];
				@$gtkomplus[$komplus]+=$rupiah[$afd][$karid][$komplus];
				if($komplus=='70' || $komplus=='71' || $komplus=='72' || $komplus=='73' || $komplus=='80' )
				{	
				@$tkomplusx[$afd][$karid]+=$rupiah[$afd][$karid][$komplus];
				}
			}
			$stream.="<td align=right>".@number_format($tkomplus[$afd][$karid])."</td>";
			foreach ($dtkommin as $kommin){
				$stream.="<td align=right>".@number_format($rupiah[$afd][$karid][$kommin])."</td>";
				@$tkommin[$afd][$karid]+=$rupiah[$afd][$karid][$kommin];
				@$subtkommin[$afd][$kommin]+=$rupiah[$afd][$karid][$kommin];
				@$gtkommin[$kommin]+=$rupiah[$afd][$karid][$kommin];
			}
			$stream.="<td align=right>".@number_format($tkommin[$afd][$karid])."</td>";
			
			$tnettokar[$afd][$karid]=$tkomplus[$afd][$karid]-$tkommin[$afd][$karid]-$tkomplusx[$afd][$karid];
			$stream.="<td align=right>".@number_format($tnettokar[$afd][$karid])."</td>";
			$stream.="</tr>";
			@$ttlhk[$afd]+=$hk[$afd][$karid];
			@$ttllembur[$afd]+=$jamlembur[$afd][$karid];			
		}
	}
	
	
	
	$stream.="<tr bgcolor=lightgray>";
			
	
	$stream.="<td align=center colspan=10>".$_SESSION['lang']['total']." ".$kdafd."</td>";
	$stream.="<td align=right>".@number_format($ttlhk[$afd],2)."</td>";
	$stream.="<td align=right>".@number_format($ttllembur[$afd],2)."</td>";
	foreach ($dtkomplus as $komplus){
		$stream.="<td align=right>".@number_format($subtkomplus[$afd][$komplus])."</td>";
		@$tsubtkomplus[$afd]+=$subtkomplus[$afd][$komplus];
		
	}
	
	$stream.="<td align=right>".@number_format($tsubtkomplus[$afd])."</td>";
	foreach ($dtkommin as $kommin){
		$stream.="<td align=right>".@number_format($subtkommin[$afd][$kommin])."</td>";
		@$tsubtkommin[$afd]+=$subtkommin[$afd][$kommin];
		
	}
	$stream.="<td align=right>".@number_format($tsubtkommin[$afd])."</td>";
	@$tsubtnetto[$afd]=$tsubtkomplus[$afd]-$tsubtkommin[$afd];

	
	$stream.="<td align=right>".@number_format($tsubtnetto[$afd])."</td>";
	$stream.="</tr>";
	
	
	@$gthk+=$ttlhk[$afd];
	@$gtlembur+=$ttllembur[$afd];			
	
}





$stream.="<tr bgcolor=gray>";
	$stream.="<td align=center colspan=10>".$_SESSION['lang']['total']."</td>";
	$stream.="<td align=right>".@number_format($gthk,2)."</td>";
	$stream.="<td align=right>".@number_format($gtlembur,2)."</td>";
	foreach ($dtkomplus as $komplus){
		$stream.="<td align=right>".@number_format($gtkomplus[$komplus])."</td>";
		@$gtsubtkomplus+=$gtkomplus[$komplus];
		
	}
	$stream.="<td align=right>".@number_format($gtsubtkomplus)."</td>";
	
	foreach ($dtkommin as $kommin){
		$stream.="<td align=right>".@number_format($gtkommin[$kommin])."</td>";	
		@$gtsubtkommin+=$gtkommin[$kommin];
	}
	$stream.="<td align=right>".@number_format($gtsubtkommin)."</td>";
	
	@$gtsubtnetto=$gtsubtkomplus-$gtsubtkommin;
	$stream.="<td align=right>".@number_format($gtsubtnetto)."</td>";
	$stream.="</tr>";

	
	
	
$stream.="<tbody></table>";
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
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        //$nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_sd_".per2;
        $nop_="laporan_rekap_gaji_perkaryawan";
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