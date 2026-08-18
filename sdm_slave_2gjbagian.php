<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$per=checkPostGet('per','');
$unit=checkPostGet('unit','');
$bag=checkPostGet('bag','');
$gaji=checkPostGet('gaji','');



if($per=='' || $unit==''){
	exit("Warning : Periode atau unit masih kosong");
}


	if($unit==''){
		exit("Unit tidak boleh kosong");
	}
	if($per==''){
		exit("Periode tidak boleh kosong");
	}
	
	$where=''; 
	$where1=''; 
	$wherev='';
	if(strlen($bag)=='4'){
		$where.=" and a.subbagian = ''";
		$where1.=" and subbagian = ''";
		$wherev.=" and a.subbagian = ''";
	} else if(strlen($bag)>'4'){
		$where.=" and a.subbagian like '".$bag."%'";
		$where1.=" and subbagian like '".$bag."%'";
		$wherev.=" and a.subbagian = '".$bag."'";
	}
	
	$wgaji='';
	$wgaji1='';
	if($gaji!=''){
		$wgaji.=" and a.sistemgaji = '".$gaji."'";
		$wgaji1.=" and c.sistemgaji = '".$gaji."'";
	}
	
	# ambil dari setup parameter komponen BPJS Plus tidak di tampilkan
	$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRBPJSPLUS' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();	
	$arrbpjs=explode(',',$bar['nilai']);
	foreach($arrbpjs as $key){
		$arrpen[$key]=$key;
	}

	//komponen penambah	
	$str="select * from ".$dbname.".sdm_gaji_vw a left join sdm_ho_component b on a.idkomponen=b.id
		  where a.lokasitugas='".$unit."' and a.periodegaji='".$per."' ".$wherev." and plus='1' and a.idkomponen not in ('".implode("','",$arrpen)."') order by idkomponen asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$idplus[$bar['idkomponen']]=$bar['idkomponen'];
		@$idplusname[$bar['idkomponen']]=$bar['name'];
	}
	//komponen pengurang
	$str="select * from ".$dbname.".sdm_gaji_vw a left join sdm_ho_component b on a.idkomponen=b.id
		  where a.lokasitugas='".$unit."' and a.periodegaji='".$per."' ".$wherev." and plus='0' and a.idkomponen not in ('".implode("','",$arrpen)."') order by idkomponen asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$idmin[$bar['idkomponen']]=$bar['idkomponen'];
		@$idminname[$bar['idkomponen']]=$bar['name'];
	}
	@$colplus=count($idplus)+2;
	@$colmin=count($idmin)+1;

#buat trap data kosong
@$trap=count($idplus);
	
if($trap<1){
	exit("Warning: Unit ".$unit." Belum melakukan proses gaji");
}
	
if($proses=='excel'){
	$border="border=1";
}else{
	$border="border=0";
}

	$stream.="<table cellspacing='1' cellpadding='5' ".$border." class='sortable'>";
	$stream.="<thead><tr>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>";
	$stream.="<th align=center rowspan=2>Jumlah<br>Karyawan</th>";
	$stream.="<th align=center rowspan=2>Jumlah<br>HK</th>";
	$stream.="<th align=center rowspan=2>Total<br>Jam<br>Lembur</th>";
	$stream.="<th align=center rowspan=2>Total<br>Natura<br>Kg</th>";
	$stream.="<th align=center colspan='".$colplus."'>Upah & Tunjangan</th>";
	$stream.="<th align=center colspan='".$colmin."'>Potongan</th>";
	$stream.="<th align=center rowspan=2>Total<br>Gaji Bersih</th>";
	$stream.="</tr>";
	$stream.="<tr>";
	
	foreach ($idplus as $listidplus => $id) {
	$stream.="<th align=center width=50px>".$idplusname[$id]."</th>";
	}
	$stream.="<th align=center width=50px>".$_SESSION['lang']['natura']."</th>";
	$stream.="<th align=center width=50px>Total</th>";
	
	foreach ($idmin as $listidmin => $idm) {
	$stream.="<th align=center width=50px>".$idminname[$idm]."</th>";
	}
	$stream.="<th align=center width=50px>Total</th>";
	$stream.="</tr>";
	$stream.="</thead>";
	
	$nmbag=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	//ambil gaji dan komponen lainnya
	$str="select * from ".$dbname.".sdm_gaji_vw a
		 where a.kodeorg='".$unit."' and a.periodegaji='".$per."' ".$where." ".$wgaji." ";
		 
	// if($_SESSION['standard']['username']=='tim.owl3'){
		// echo $str;
	// }		
		 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$kdbagian[$bar['subbagian']]=$bar['subbagian'];
		$karid[$bar['karyawanid']]=$bar['karyawanid'];
		$jumkarawal[$bar['subbagian']][$bar['karyawanid']]=1;
		@$jumgj[$bar['subbagian']][$bar['idkomponen']]+=$bar['jumlah'];
		@$jumgjkot[$bar['subbagian']]+=$bar['jumlah'];
		@$jhk[$bar['subbagian']]+=$bar['hk'];
	}
	$dakarbulanan=0;
	$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$per."' "; 
	$res = fetchdata($str);
	if(count($res)>0){ 
		$dakarbulanan=1;
	}
	$dakarbulanan=1;
	//ambil Natura
	if($dakarbulanan ==0){
		$str="select a.*, c.sistemgaji from ".$dbname.".sdm_catu a
			left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
			where a.kodeorg='".$unit."' and a.periodegaji='".$per."' ".$where." ".$wgaji1." and tanggalkeluar='0000-00-00'";
	}else{
		$str="select a.*, c.sistemgaji from ".$dbname.".sdm_catu a
			left join ".$dbname.".datakaryawan_hist c on a.karyawanid=c.karyawanid and approval_status='8' and version_type='B' and c.periodegaji='".$per."'
			where a.kodeorg='".$unit."' and approval_status='8' and version_type='B' and a.periodegaji='".$per."' ".$where." ".$wgaji1." and tanggalkeluar='0000-00-00'";
	}
	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$kdbagian[$bar['subbagian']]=$bar['subbagian'];
		@$catukg[$bar['subbagian']]+=$bar['totalcatu'];
		@$caturp[$bar['subbagian']]+=$bar['jumlahrupiah'];
	}
	
	// if($jumkarawal<=0){
		// exit('Data Kosong');
	// }
	foreach($kdbagian as $bagian){
		foreach($karid as $kar){
			@$jumkar[$bagian]+=$jumkarawal[$bagian][$kar];
		}
	}

	//ambil jam lembur
	if($dakarbulanan ==0){
		$str="select a.kodeorg,a.tanggal,a.karyawanid,a.jamaktual,c.subbagian 
		from ".$dbname.".sdm_lemburdt a left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
		where a.kodeorg like '".$unit."%' and tanggal like '".$per."%' ".$where1." ".$wgaji1."";
	}else{
		$str="select a.kodeorg,a.tanggal,a.karyawanid,a.jamaktual,c.subbagian 
		from ".$dbname.".sdm_lemburdt a left join ".$dbname.".datakaryawan_hist c on a.karyawanid=c.karyawanid and approval_status='8' and version_type='B' and c.periodegaji='".$per."'
		where a.kodeorg like '".$unit."%' and approval_status='8' and version_type='B' and tanggal like '".$per."%' ".$where1." ".$wgaji1."";
	}

	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$kdbagian[$bar['subbagian']]=$bar['subbagian'];
		@$jamlembur[$bar['subbagian']]+=$bar['jamaktual'];	
	}
	
	
	array_multisort($kdbagian,SORT_ASC);
	
	foreach($kdbagian as $bagian){
	if($bagian==''){
		$kdbagian=$unit;
	}else{
		$kdbagian=$bagian;
	}
		$no+=1;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";		
		$stream.="<td align=left>".$kdbagian." - ".$nmbag[$kdbagian]."</td>";
		$stream.="<td align=right>".$jumkar[$bagian]."</td>";
		$stream.="<td align=right>".@number_format($jhk[$bagian],2)."</td>";
		$stream.="<td align=right>".@number_format($jamlembur[$bagian],2)."</td>";
		$stream.="<td align=right>".@number_format($catukg[$bagian],2)."</td>";
	
	foreach ($idplus as $listidplus => $id) {
		$stream.="<td align=right>".@number_format($jumgj[$bagian][$id])."</td>";
		@$ttplus[$bagian]+=$jumgj[$bagian][$id];
		@$tjumgj[$id]+=$jumgj[$bagian][$id];
	}
		$stream.="<td align=right>".@number_format($caturp[$bagian])."</td>";
		$stream.="<td align=right>".@number_format($ttplus[$bagian]+$caturp[$bagian])."</td>";
		@$tcaturp+=$caturp[$bagian];
		
	foreach ($idmin as $listidmin => $idm) {
		$stream.="<td align=right>".@number_format($jumgj[$bagian][$idm])."</td>";
		@$ttmin[$bagian]+=$jumgj[$bagian][$idm];
		@$tjumgjm[$idm]+=$jumgj[$bagian][$idm];
	}
		$stream.="<td align=right>".@number_format($ttmin[$bagian])."</td>";
		@$gtbersih[$bagian]=(($ttplus[$bagian]+$caturp[$bagian])-$ttmin[$bagian]);
		$stream.="<td align=right>".@number_format($gtbersih[$bagian])."</td>";
		$stream.="</tr>";
		
		
		@$tjumkar+=$jumkar[$bagian];
		@$tjhk+=$jhk[$bagian];
		@$tjamlembur+=$jamlembur[$bagian];
		@$tcatukg+=$catukg[$bagian];

	}
	
	
	#total
	$stream.="<tr class=rowcontent>";
		$stream.="<td align=center colspan=2>Total</td>";		
		$stream.="<td align=right>".$tjumkar."</td>";
		$stream.="<td align=right>".@number_format($tjhk,2)."</td>";
		$stream.="<td align=right>".@number_format($tjamlembur,2)."</td>";
		$stream.="<td align=right>".@number_format($tcatukg,2)."</td>";
	foreach ($idplus as $listidplus => $id) {
		$stream.="<td align=right>".@number_format($tjumgj[$id])."</td>";
		@$ttjumgj+=$tjumgj[$id];
	}	
		$stream.="<td align=right>".@number_format($tcaturp)."</td>";
		$stream.="<td align=right>".@number_format($ttjumgj+$tcaturp)."</td>";
	foreach ($idmin as $listidmin => $idm) {
		$stream.="<td align=right>".@number_format($tjumgjm[$idm])."</td>";
		@$ttjumgjm+=$tjumgjm[$idm];
	}
		$stream.="<td align=right>".@number_format($ttjumgjm)."</td>";
		@$gt=(($ttjumgj+$tcaturp)-$ttjumgjm);
		$stream.="<td align=right>".@number_format($gt)."</td>";
		$stream.="</tr>";
		$stream.="</tbody></table>";
	

#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
######HTML
	case 'preview':
		echo $stream;
    break;

######EXCEL	
	case 'excel':
		//$stream.="Print Time : ".date('Ymd, H:i:s')."<br>By : ".$_SESSION['empl']['name'];	
		$stream.="Note : Jumlah Karyawan adalah Jumlah karyawan yang Hadir / Bekerja";	
		$tglSkrg=date("Ymd");
		$nop_="rekap_gaji_per_divisi_".$tglSkrg;
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					@unlink('tempExcel/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
		break;
}

?>