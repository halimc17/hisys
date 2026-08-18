<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$pt = checkPostGet('pt', '');
$prd = checkPostGet('prd', '');

	if($prd==''){
		exit("Warning : Periode atau unit masih kosong");
	}

	//komponen penambah	
	$str="select * from ".$dbname.".sdm_5tipekaryawan order by id asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$tipekar[$bar['id']]=$bar['id'];
		$nmtipekar[$bar['id']]=$bar['tipe'];
	}


	
	$nmbag=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	//ambil gaji dan komponen lainnya (komponen penambah)
	$str="select a.*, b.induk
		 from ".$dbname.".sdm_gaji_vw a 
		 left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
		 left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
		 left join ".$dbname.".sdm_ho_component d on a.idkomponen=d.id
		 where a.kodeorg like '%".$kdorg."%' and b.induk like '%".$pt."%' and a.periodegaji='".$prd."' and d.plus='1'  order by b.induk asc, a.kodeorg asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$row.=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$kodept[$bar['induk']]=$bar['induk'];
		@$kodeorg[$bar['kodeorg']]=$bar['kodeorg'];
		@$listkodeorg[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
		@$jmlgaji[$bar['induk']][$bar['kodeorg']][$bar['tipekaryawan']]+=$bar['jumlah'];
		@$jmlhk[$bar['induk']][$bar['kodeorg']][$bar['tipekaryawan']][$bar['idkomponen']]+=$bar['hk'];
		@$jmltkawal[$bar['induk']][$bar['kodeorg']][$bar['tipekaryawan']][$bar['karyawanid']]=1;
	}
	
	//ambil gaji dan komponen lainnya (komponen pengurang)
	$str="select a.*, b.induk
		 from ".$dbname.".sdm_gaji_vw a 
		 left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
		 left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
		 left join ".$dbname.".sdm_ho_component d on a.idkomponen=d.id
		 where a.kodeorg like '%".$kdorg."%' and b.induk like '%".$pt."%' and a.periodegaji='".$prd."' and d.plus='0' order by b.induk asc, a.kodeorg asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$row.=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$listkodeorg[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
		@$jmlgajimin[$bar['induk']][$bar['kodeorg']][$bar['tipekaryawan']]+=$bar['jumlah'];
	}

	//ambil Natura
	$str="select a.*, c.sistemgaji, b.induk from ".$dbname.".sdm_catu a
		left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
		left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
		where a.kodeorg like '%".$kdorg."%' and b.induk like '%".$pt."%' and a.periodegaji='".$prd."'";
	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$row.=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$kodept[$bar['induk']]=$bar['induk'];
		@$kodeorg[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
		@$listkodeorg[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
		@$catukg[$bar['induk']][$bar['kodeorg']][$bar['tipekaryawan']]+=$bar['totalcatu'];
		@$caturp[$bar['induk']][$bar['kodeorg']][$bar['tipekaryawan']]+=$bar['jumlahrupiah'];
	}
	
	if($row<=0){
		exit('Warning : Data tidak ditemukan');
	}
	
	
	if($proses=='excel'){
	$border="border=1";
	}else{
		$border="border=0";
	}

	$stream.="<table cellspacing='1' ".$border." class='sortable'>";
	$stream.="<thead><tr>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['pt']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</td>";
	
	foreach ($tipekar as $listtipekar => $id) {
	$stream.="<td align=center colspan=3>".$nmtipekar[$id]."</td>";
	}
	$stream.="<td align=center colspan=3>".$_SESSION['lang']['total']."</td>";
	$stream.="</tr><tr>";
	foreach ($tipekar as $listtipekar) {
	$stream.="<td align=center>".$_SESSION['lang']['orang']."</td>";
	$stream.="<td align=center width=30px>".$_SESSION['lang']['jhk']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
	}
	$stream.="<td align=center>".$_SESSION['lang']['orang']."</td>";
	$stream.="<td align=center width=30px>".$_SESSION['lang']['jhk']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
	
	$stream.="</tr>";
	$stream.="</thead>";
	
	foreach($listkodeorg as $val => $isiHead){
		foreach ($isiHead as $isidata ){
			@$no+=1;
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".@$no."</td>";	
			$stream.="<td align=left>".@$val."</td>";	
			$stream.="<td align=left>".@$isidata."</td>";
			foreach ($tipekar as $listtipekar) {
				$rupiah=($jmlgaji[$val][$isidata][$listtipekar]+$caturp[$val][$isidata][$listtipekar])-$jmlgajimin[$val][$isidata][$listtipekar];
				$stream.="<td align=right>".@number_format(count($jmltkawal[$val][$isidata][$listtipekar]))."</td>";
				$stream.="<td align=right>".@number_format($jmlhk[$val][$isidata][$listtipekar]['1'],2)."</td>";
				$stream.="<td align=right>".@number_format($rupiah)."</td>";
				
				@$gtjmltkawal[$val][$isidata]+=count($jmltkawal[$val][$isidata][$listtipekar]);
				@$gtjmlhk[$val][$isidata]+=$jmlhk[$val][$isidata][$listtipekar]['1'];
				@$gtjmlgaji[$val][$isidata]+=$rupiah;
				
				@$gtrjlhtk[$listtipekar]+=count($jmltkawal[$val][$isidata][$listtipekar]);
				@$gtrjlhk[$listtipekar]+=$jmlhk[$val][$isidata][$listtipekar]['1'];
				@$gtrjlrp[$listtipekar]+=$rupiah;
			}
		$stream.="<td align=right>".@number_format($gtjmltkawal[$val][$isidata])."</td>";
		$stream.="<td align=right>".@number_format($gtjmlhk[$val][$isidata],2)."</td>";
		$stream.="<td align=right>".@number_format($gtjmlgaji[$val][$isidata])."</td>";
			@$gtktk+=$gtjmltkawal[$val][$isidata];
			@$gtkhk+=$gtjmlhk[$val][$isidata];
			@$gtkrp+=$gtjmlgaji[$val][$isidata];
		}
	}
	
	
	
	#total
	$stream.="<tr class=rowcontent>";
		$stream.="<td align=center colspan=3>Total</td>";		
	foreach ($tipekar as $listtipekar) {
		$stream.="<td align=right>".@number_format($gtrjlhtk[$listtipekar])."</td>";
		$stream.="<td align=right>".@number_format($gtrjlhk[$listtipekar],2)."</td>";
		$stream.="<td align=right>".@number_format($gtrjlrp[$listtipekar])."</td>";
	}
		$stream.="<td align=right>".@number_format($gtktk)."</td>";
		$stream.="<td align=right>".@number_format($gtkhk,2)."</td>";
		$stream.="<td align=right>".@number_format($gtkrp)."</td>";
		$stream.="</tr>";
		$stream.="</tbody></table>";
	

#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses){
######HTML
	case 'preview':
		echo $stream;
    break;

######EXCEL	
	case 'excel':
		$stream.="Note : Jumlah Orang adalah Jumlah karyawan yang Hadir / Bekerja";	
		$tglSkrg=date("Ymd");
		$nop_="rekap_gaji_per_unit_".$tglSkrg;
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