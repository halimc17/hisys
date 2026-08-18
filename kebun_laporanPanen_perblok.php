<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
            
$jenis = checkPostGet('jenis', '');
$pt = checkPostGet('pt', '');
$gudang = checkPostGet('gudang', '');
$afdeling = checkPostGet('afdeling', '');
$blok = checkPostGet('blok', '');
$intiplasma = checkPostGet('intiplasma', '');
$tgl1 = checkPostGet('tgl1', '');
$tgl2 = checkPostGet('tgl2', '');


$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$namakar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');
$namaOrgindukblok=makeOption($dbname,'organisasi','indukblok,namaindukblok');

#=========== Ambil KG WB ==============#
$whwb='';
if($gudang!=''){
	$whwb.=" and kodeorg='".$gudang."'";
} else {
	$whwb.=" and kodeorg IN (".getOrgDetail(24).")";
}
if($afdeling!=''){
	$whwb.=" and divisi='".$afdeling."'";
} else {
	$whwb.=" and divisi IN (".getOrgDetail(26).")";
}
if ($blok!="") {
	$whwb.=" and blok='".$blok."'";
}
if($intiplasma!=''){
	$whwb.=" and intiplasma='".$intiplasma."'";
}
$str="select * from ".$dbname.".kebun_spb_vw where tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." ".$whwb."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$kgwb+=$bar['kgwb'];
}


#=========== End Ambil KG WB ==============#

if($gudang==''){
	$where2='';
	if($afdeling!=''){
		$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
	} else {
		$where2 = " and substr(a.kodeorg,1,6) IN (".getOrgDetail(26).")";
	}
	
	if ($blok != '') {
		$where2 .= " and a.kodeorg='".$blok."'";
	}
	$str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(jjgbuahbesar) as jjgbuahbesar,sum(jjgbuahkecil) as jjgbuahkecil,a.karyawanid,
		sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
		sum(a.upahpenalty) as upahpenalty, sum(a.premibasis) as premibasis,
		sum(a.upahpremi) + sum(a.upahpremilebihbasis) as premi,sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk
		,sum(hkpanenperhari) as hkpanenperhari
		from ".$dbname.".kebun_prestasi_vs_hk a
		left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi 
		where c.induk = '".$pt."' and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." ".$where2."
		group by a.tanggal,a.kodeorg,a.karyawanid,a.karyawanid";
}else{
	$where='';
	$where2='';
	
	if($afdeling!=''){
		$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
	} else {
		$where2 = " and substr(a.kodeorg,1,6) IN (".getOrgDetail(26).")";
	}

	if ($blok != '') {
		$where2 .= " and a.kodeorg='".$blok."'";
	}
	$str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(jjgbuahbesar) as jjgbuahbesar,sum(jjgbuahkecil) as jjgbuahkecil,a.karyawanid,
		sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
		sum(a.upahpenalty) as upahpenalty, sum(a.premibasis) as premibasis,
		sum(a.upahpremi) + sum(a.upahpremilebihbasis) as premi,sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk  
		,sum(hkpanenperhari) as hkpanenperhari
		from ".$dbname.".kebun_prestasi_vs_hk a 
		where unit = '".$gudang."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." ".$where." ".$where2."
		group by a.tanggal, a.kodeorg,a.karyawanid";
}	
//=================================================
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$jjgkbn+=$bar['jjg'];
}
$bjrpks=@($kgwb/$jjgkbn);
$tab="<div class='table-scroll'>";
if($jenis=='excel'){
	$tab.="<table class=sortable cellspacing=1 border=1 width=100%>";
}else{
	$tab.="<table class=sortable cellspacing=1 border=0 width=100%>";
}
$tab.="
	 <thead>
		<tr>
			  <th align=center width=50>No.</th>
			  <th align=center width=150>".$_SESSION['lang']['tanggal']."</th>
			  <th align=center width=150>".$_SESSION['lang']['nik']."</th>
			  <th align=center width=150>".$_SESSION['lang']['namakaryawan']."</th>
			  <th align=center>".$_SESSION['lang']['afdeling']."</th>
			  <th align=center>".$_SESSION['lang']['blok']."</th>
			  <th align=center>".$_SESSION['lang']['janjang']." basis besar</th>
			  <th align=center>".$_SESSION['lang']['janjang']." basis kecil</th>
			  <th align=center>".$_SESSION['lang']['hasilkerjad']." (Kg)<br>Kg Kebun</th>    
			  <th align=center>".$_SESSION['lang']['hasilkerjad']." (Kg)<br>Kg PKS</th>    
			  <th align=center>".$_SESSION['lang']['jumlahhk']."</th>
			  <th align=center>".$_SESSION['lang']['upahkerja']."</th>
			  <th align=center>".$_SESSION['lang']['upahpenalty']."</th>
			  <th align=center>".$_SESSION['lang']['premibasis']."</th>
			  <th align=center>".$_SESSION['lang']['premlebihbasis']."</th>
			  <th align=center>".$_SESSION['lang']['rupiahpenalty']."</th>
			</tr>  
		 </thead>";
				 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

$numrows=owlBaris($res);
$no=0;
if($numrows<1){
	$tab.="<tr class=rowcontent><td colspan=15 style='text-align:center'>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}else{
	$totberat=$totUpah=$totUpahpenalty=$totJjg=$totPremi=$totPremibasis=$totHk=$totPenalty=0;
while($bar=$res->fetch()){
	$no+=1;
	$periode=date('Y-m-d H:i:s');
	$tanggal=$bar->tanggal; 
	$kodeorg=$bar->kodeorg;
	#onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\"
	$arr="tanggal##".$tanggal."##kodeorg##".$kodeorg;	  
	$tab.="<tr class=rowcontent style='cursor:pointer;' title='Click' >
		  <td align=center width=20 >".$no."</td>
		  <td align=center>".tanggalnormal($tanggal)."</td>
		  <td align=center>".$nikkar[$bar->karyawanid]."</td>
		  <td align=center>".$namakar[$bar->karyawanid]."</td>
		  <td align=center>".$namaOrg[substr($kodeorg,0,6)]."</td>
		  <td align=center>".$namaOrgindukblok[$kodeorg]."</td>
		  <td align=right>".number_format($bar->jjgbuahbesar,0)."</td>
		  <td align=right>".number_format($bar->jjgbuahkecil,0)."</td>
		  <td align=right>".number_format($bar->berat,2)."</td>    
		  <td align=right>".number_format($bar->jjg * $bjrpks,2)."</td>    
		  <td align=right>".number_format($bar->jumlahhk,2)."</td>
		  <td align=right>".number_format($bar->upah,2)."</td>
		  <td align=right>".number_format($bar->upahpenalty,2)."</td>
		  <td align=right>".number_format($bar->premibasis,2)."</td>
		  <td align=right>".number_format($bar->premi,2)."</td>
		  <td align=right>".number_format($bar->penalty,2)."</td>
		</tr>";
	$totberat+=$bar->berat;
	$totUpah+=$bar->upah;
	$totUpahpenalty+=$bar->upahpenalty;
	$totJjgbs+=$bar->jjgbuahbesar;
	$totJjgkc+=$bar->jjgbuahkecil;
	$totPremi+=$bar->premi;
	$totPremibasis+=$bar->premibasis;
	$totHk+=$bar->hkpanenperhari;
	$totPenalty+=$bar->penalty;
	$ttlkgwb+=$bar->jjg * $bjrpks;
}	
	$arr2="tgl1##".$tgl1."##tgl2##".$tgl2."##gudang##".$gudang."";
	$tab.="<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"zDetailTotal(event,'kebun_slave_2panen.php','".$arr2."');\">
			  <td align=center colspan=6>".$_SESSION['lang']['total']."</td>		 
			  <td align=right>".number_format($totJjgbs,0)."</td>
			  <td align=right>".number_format($totJjgkc,0)."</td>
			  <td align=right>".number_format($totberat,2)."</td>    
			  <td align=right>".number_format($ttlkgwb,2)."</td>    
			  <td align=right>".number_format($totHk,2)."</td>
			  <td align=right>".number_format($totUpah,2)."</td>
			  <td align=right>".number_format($totUpahpenalty,2)."</td>
			  <td align=right>".number_format($totPremibasis,2)."</td>
			  <td align=right>".number_format($totPremi,2)."</td>
			  <td align=right>".number_format($totPenalty,2)."</td>
			   </tr>
			";
}
$tab.="</div>";
if($jenis!='excel'){
	echo $tab;
}else{
 //exit('error'.$jenis);
	
	$stream = $tab;
	$nop_ = "laporan_panen_perblok_" . date('Ymd_His');
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
?>