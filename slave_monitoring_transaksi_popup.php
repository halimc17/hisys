<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>
<?
$theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
  $gen='generic.css';
}else if($theme=='red'){
  $gen='genericRed.css';  
}else{
  $gen='genericGray.css';  
}  
$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$tgl = checkPostGet('tgl', '');
$posting = checkPostGet('posting', '');
$tipe = checkPostGet('tipe', '');

echo"<link rel=stylesheet type=text/css href=style/".$gen.">";
$namaKary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$namaAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');

switch ($proses) {
case 'detail3':
	$stream = "<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['divisi']."</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center>".$_SESSION['lang']['tanggal']."</th>
		<th align=center>".$_SESSION['lang']['hk2']."</th>
		<th align=center>Updateby</th>
		</tr></thead>";

	$no = 0;
	$where = '';
	$cltipe = '';
	if (strlen($unit) == 6) {
		$where = " and b.kodeorg like '".$unit."%'";
	} else {
		$where = " and a.kodeorg = '".$unit."' and b.kodeorg is null";
	}
	if ($tipe != '') {
		$cltipe = " and a.tipetransaksi='".$tipe."'";
	}
	$whr = "lokasitugas like '".$unit."%'";
	$whtp="";
	if($tipe!='PNN'){
		$whtp="and c.karyawanid=b.nikpemel";
		$hknya=" sum(c.jhk) as hk ";
	}else{
		$hknya=" sum(a.jurnal) as hk ";
	}
	
	$str = "select a.*,b.kodeorg as divisi,".$hknya." from ".$dbname.".kebun_aktifitas a
		left join ".$dbname.".kebun_kehadiran_vw c on a.notransaksi=c.notransaksi and a.tipetransaksi!='PNN'
		left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi ".$whtp." 
		where a.tanggal like '".$tgl."' ".$where." ".$cltipe." and a.jurnal='".$posting."' group by a.notransaksi";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$no += 1;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=center>".$unit."</td>";
		$stream.="<td align=center>".$bar['notransaksi']."</td>";
		$stream.="<td align=center>".$bar['tanggal']."</td>";
		$stream.="<td align=right>".number_format($bar['hk'],2)."</td>";
		$stream.="<td align=center>".$namaKary[$bar['updateby']]." - ".$bar['lastupdate']."</td>";
		 @$total += $bar['hk'];
	}
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center colspan=4>TOTAL</td>";
	$stream.="<td align=right>".number_format($total,2)."</td>";
	$stream.="<td align=center></td>";

	$stream.="</table>";
	echo $stream;
break;
case 'detail4':
	$stream = "<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['pabrik']."</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center>".$_SESSION['lang']['tanggal']."</th>
		<th align=center>".$_SESSION['lang']['keterangan']."</th>
		<th align=center>Updateby</th>
		</tr></thead>";
	$kamuspost['0']='<font color=red>Not Posted</font>';
	$kamuspost['1']='Posted';
	$kamuspost['9']='';

	if($tipe=='rwtmsn'){
		$str = "select notransaksi, pabrik, tanggal, kegiatan, updateby, updatetime from ".$dbname.".pabrik_rawatmesinht
			where pabrik = '".$unit."' and tanggal = '".$tgl."' and statPost = '".$posting."' order by tanggal, notransaksi ";
	}
	if($tipe=='pengol'){
		$str = "select nopengolahan as notransaksi, kodeorg as pabrik, tanggal, concat(jammulai,' - ',jamselesai) as kegiatan, createby as updateby, updatetime from ".$dbname.".pabrik_pengolahan
			where kodeorg = '".$unit."' and tanggal = '".$tgl."' and posting = '".$posting."' order by tanggal, notransaksi ";
	}
	if($tipe=='maskel'){
		$posting='9'; // tidak ada posting
		$str = "select notransaksi as notransaksi, kodeorg as pabrik, tanggal, concat(kodetangki,' - ',FORMAT(kuantitas+kernelquantity,0)) as kegiatan, createby as updateby, updatetime from ".$dbname.".pabrik_masukkeluartangki
			where kodeorg = '".$unit."' and tanggal = '".$tgl."' order by tanggal, notransaksi ";
	}
	if($tipe=='produk'){
		$posting='9'; // tidak ada posting
		$str = "select tanggal as notransaksi, kodeorg as pabrik, tanggal, concat('TBS Olah : ',FORMAT(tbsdiolah,0)) as kegiatan, createby as updateby, updatetime from ".$dbname.".pabrik_produksi
			where kodeorg = '".$unit."' and tanggal = '".$tgl."' order by tanggal, notransaksi ";
	}
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$no += 1;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=center>".$unit."</td>";
		$stream.="<td align=center>".$bar['notransaksi']."</td>";
		$stream.="<td align=center>".$bar['tanggal']."</td>";
		$stream.="<td align=left>".$bar['kegiatan']."</td>";
		$stream.="<td align=center>".$namaKary[$bar['updateby']]." - ".$bar['updatetime']."</td>";
		 // @$total += $bar['hk'];
	}
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center colspan=4>TOTAL ".$kamuspost[$posting]."</td>";
	$stream.="<td align=right>".number_format($no)."</td>";
	$stream.="<td align=center></td>";

	$stream.="</table>";
	echo $stream;
break;
case'keu':
	$nmstat = array('0'=>'not posting','1'=>'posting','9'=>'proses','3'=>'tolak');
	$stream="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center width=50px>".$_SESSION['lang']['noakun']."</th>
		<th align=center>".$_SESSION['lang']['tipe']."</th>
		<th align=center>".$_SESSION['lang']['keterangan']."</th>
		<th align=center>".$_SESSION['lang']['status']."</th>
		<th align=center>".$_SESSION['lang']['jumlah']."</th>
		<th align=center>Updateby</th>
		</tr></thead>";
	$where='';
	if($posting==0){
		$where=" and posting in ('0','9')";
	}else{
		$where=" and posting in ('1','3')";
	}
	$str = "select * from ".$dbname.".keu_kasbankht where tanggalinput = '".$tgl."' and kodeorg='".$unit."' ".$where." and pembayaran=0";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no='0';
	while ($bar = $res->fetch()) {
		$no += 1;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=center>".$bar['notransaksi']."</td>";
		$stream.="<td align=left>".$namaAkun[$bar['noakun']]."</td>";
		$stream.="<td align=center>".$bar['tipetransaksi']."</td>";
		$stream.="<td align=left>".$bar['keterangan']."</td>";
		$stream.="<td align=left>".$nmstat[$bar['posting']]."</td>";
		$stream.="<td align=right>".number_format($bar['jumlah'])."</td>";
		$stream.="<td align=center>".$namaKary[$bar['userid']]."<br>".$bar['lastupdate']."</td>";
		 @$total += $bar['jumlah'];
	}
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center colspan=6>TOTAL</td>";
	$stream.="<td align=right>".number_format($total)."</td>";
	$stream.="<td align=center></td>";
	$stream.="</table>";
	echo $stream;
break;
case'log':
	$stream="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center width=80px>".$_SESSION['lang']['tanggal']." Transaksi</th>
		<th align=center width=80px>".$_SESSION['lang']['tanggal']." Dibuat</th>
		<th align=center width=80px>".$_SESSION['lang']['tanggal']." Posting</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center>".$_SESSION['lang']['tipe']."</th>
		<th align=center>".$_SESSION['lang']['keterangan']."</th>
		<th align=center>".$_SESSION['lang']['kodebarang']."</th>
		<th align=center>".$_SESSION['lang']['satuan']."</th>
		<th align=center>".$_SESSION['lang']['jumlah']."</th>
		<th align=center>Updateby</th>
		<th align=center width=40px>Tgl Transaksi s/d Dibuat</th>
		<th align=center width=30px>Tgl Transaksi s/d Posting</th>
		<th align=center width=30px>Tgl Dibuat s/d Posting</th>

		</tr></thead>";
	$nmtrans=array('0'=>'Pabrikasi', '1'=>'Masuk','2'=>'Pengembalian pengeluaran(retur)', '3'=>'penerimaan mutasi','4'=>'Koreksi','5'=>'Pengeluaran','6'=>'Pengembalian penerimaan','7'=>'pengeluaran mutasi');
	
	$strr = " select distinct tanggalentry, noreferensi from ".$dbname.".keu_jurnalht ";
	$res = $owlPDO->query($strr)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) 
	{
		$tglpost[$bar['noreferensi']]=$bar['tanggalentry'];
	}

	$str = "select * from ".$dbname.".log_transaksi_vw where tanggal = '".$tgl."' and kodegudang='".$unit."' and post='".$posting."'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no='0';
	while ($bar = $res->fetch()) {
		$no += 1;

		$jmlHari1 = (strtotime(tanggalnormal($bar['waktutransaksi'])) - strtotime($bar['tanggal'])) / (60 * 60 * 24);
		$jmlHari2 = (strtotime($tglpost[$bar['notransaksi']]) - strtotime($bar['tanggal'])) / (60 * 60 * 24);
		$jmlHari3 = (strtotime($tglpost[$bar['notransaksi']]) - strtotime(tanggalnormal($bar['waktutransaksi'])) ) / (60 * 60 * 24);

		if ($jmlHari1 <= 0){
			$warna1="style='text-align:center;color:black'";
			$jmlHari1 = 0;
		}else{
			$warna1="style='text-align:center;color:red'";
		}

		if ($jmlHari2 <= 0){
			$warna2="style='text-align:center;color:black'";
			$jmlHari2 = 0;
		}else{
			$warna2="style='text-align:center;color:red'";
		}

		if ($jmlHari3 <= 0){
			$warna3="style='text-align:center;color:black'";
			$jmlHari3 = 0;
		}else{
			$warna3="style='text-align:center;color:red'";
		}

		$nmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
		$stream.="<td align=center>".tanggalnormal($bar['waktutransaksi'])."</td>";
		$stream.="<td align=center>".tanggalnormal($tglpost[$bar['notransaksi']])."</td>";
		$stream.="<td align=center>".$bar['notransaksi']."</td>";
		$stream.="<td align=center>".$nmtrans[$bar['tipetransaksi']]."</td>";
		$stream.="<td align=left>".$bar['keterangan']."</td>";
		$stream.="<td align=left>".$bar['kodebarang']." - ".$nmbarang[$bar['kodebarang']]."</td>";
		$stream.="<td align=left>".$bar['satuan']."</td>";
		$stream.="<td align=right>".number_format($bar['jumlah'])."</td>";
		$stream.="<td align=center>".@$namaKary[$bar['updateby']]."</td>";
		$stream.="<td ".$warna1." >".$jmlHari1."</td>";
		$stream.="<td ".$warna2." >".$jmlHari2."</td>";
		$stream.="<td ".$warna3." >".$jmlHari3."</td>";
	}
	$stream.="</table>";
	echo $stream;
break;
case'service':
	$stream="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center>".$_SESSION['lang']['kodevhc']."</th>
		<th align=center>".$_SESSION['lang']['keterangan']."</th>
		<th align=center>".$_SESSION['lang']['tipeperbaikan']."</th>
		<th align=center>".$_SESSION['lang']['jumlah']."</th>
		<th align=center>Updateby</th>
		</tr></thead>";

	$str = "select * from ".$dbname.".vhc_penggantianht where tanggal = '".$tgl."' and kodeorg='".$unit."' and posting='".$posting."'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no='0';
	while ($bar = $res->fetch()) {
		$no += 1;
		$nmbarang=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar['kodevhc']."'");
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=center>".$bar['notransaksi']."</td>";
		$stream.="<td align=left>".$bar['kodevhc']." - ".$nmbarang[$bar['kodevhc']]."</td>";
		$stream.="<td align=left>".$bar['kerusakan']."</td>";
		$stream.="<td align=left>".$bar['tipeperbaikan']."</td>";
		$stream.="<td align=right>".number_format($bar['downtime'])."</td>";
		$stream.="<td align=center>".@$namaKary[$bar['updateby']]."</td>";
	}
	$stream.="</table>";
	echo $stream;
break;
case'pek':
	$stream="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center>".$_SESSION['lang']['kodevhc']."</th>
		<th align=center>Updateby</th>
		</tr></thead>";
	$str = "select * from ".$dbname.".vhc_runht where tanggal = '".$tgl."' and kodeorg='".$unit."' and posting='".$posting."'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no='0';
	while ($bar = $res->fetch()) {
		$no += 1;
		$nmbarang=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',"kodevhc='".$bar['kodevhc']."'");
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=center>".$bar['notransaksi']."</td>";
		$stream.="<td align=left>".$bar['kodevhc']." - ".$nmbarang[$bar['kodevhc']]."</td>";
		$stream.="<td align=center>".@$namaKary[$bar['updateby']]."</td>";
	}
	$stream.="</table>";
	echo $stream;
break;
case'spl':
	$stream="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center>".$_SESSION['lang']['nourut']." BKM</th>
		<th align=center>Updateby</th>
		</tr></thead>";
	$str = "select * from ".$dbname.".vhc_spl_aktifitas where tanggal = '".$tgl."' and kodeorg='".$unit."' and jurnal='".$posting."'";
	if(count(fetchData($str))>0){
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='0';
		while ($bar = $res->fetch()) {
			$no += 1;
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td align=center>".$bar['notransaksi']."</td>";
			$stream.="<td align=center>".$bar['nobkm']."</td>";
			$stream.="<td align=center>".@$namaKary[$bar['updateby']]."</td>";
		}
	}else{
		$stream.="<tr class=rowcontent><td colspan=4 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}
	$stream.="</table>";
	echo $stream;
break;
case'abs':
	$stream="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['nama']."</th>
		<th align=center>".$_SESSION['lang']['jabatan']."</th>
		<th align=center>".$_SESSION['lang']['absensi']."</th>
		<th align=center>".$_SESSION['lang']['hk2']."</th>
		<th align=center>".$_SESSION['lang']['upah']."</th>
		<th align=center>".$_SESSION['lang']['premi']."</th>
		<th align=center>".$_SESSION['lang']['keterangan']."</th>
		</tr></thead>";
	$str = "select * from ".$dbname.".sdm_absensidt_vw where tanggal = '".$tgl."' and kodeorg='".$unit."'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no='0';
	while ($bar = $res->fetch()) {
		$jab=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$bar['karyawanid']."'");
		
		$no += 1;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left>".$namaKary[$bar['karyawanid']]."</td>";
		$stream.="<td align=left>".$nmjab[$jab[$bar['karyawanid']]]."</td>";
		$stream.="<td align=center>".$bar['absensi']."</td>";
		$stream.="<td align=center>".$bar['nilaihk']."</td>";
		$stream.="<td align=right>".number_format($bar['umr'])."</td>";
		$stream.="<td align=right>".number_format($bar['premi']+$bar['insentif']+$bar['insentiflibur'])."</td>";
		$stream.="<td align=left>".$bar['penjelasan']."</td>";
	}
	$stream.="</table>";
	echo $stream;
break;
case'lbr':
	$stream="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['nama']."</th>
		<th align=center>".$_SESSION['lang']['jabatan']."</th>
		<th align=center>".$_SESSION['lang']['tipe']."</th>
		<th align=center>".$_SESSION['lang']['jam']."</th>
		<th align=center>".$_SESSION['lang']['mulai']."</th>
		<th align=center>".$_SESSION['lang']['selesai']."</th>
		<th align=center>".$_SESSION['lang']['jumlah']."</th>
		<th align=center>".$_SESSION['lang']['keterangan']."</th>
		</tr></thead>";
		
	$arrTipe=array('0'=>'Hari Kerja','1'=>'Hari Minggu','2'=>'Hari Libur','3'=>'Hari Raya');
	$str = "select * from ".$dbname.".sdm_lemburdt where tanggal = '".$tgl."' and kodeorg='".$unit."'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no='0';
	while ($bar = $res->fetch()) {
		$jab=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$bar['karyawanid']."'");
		$no += 1;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left>".$namaKary[$bar['karyawanid']]."</td>";
		$stream.="<td align=left>".$nmjab[$jab[$bar['karyawanid']]]."</td>";
		$stream.="<td align=center>".$arrTipe[$bar['tipelembur']]."</td>";
		$stream.="<td align=center>".$bar['jamaktual']."</td>";
		$stream.="<td align=center>".$bar['jammulai']."</td>";
		$stream.="<td align=center>".$bar['jamselesai']."</td>";
		$stream.="<td align=right>".number_format($bar['uangkelebihanjam'])."</td>";
		$stream.="<td align=left>".$bar['ket']."</td>";
	}
	$stream.="</table>";
	echo $stream;
break;
case'finger':	
	$tab="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['tipekaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['shift']."</th>
				<th rowspan='3'>".$_SESSION['lang']['sumber']."</th>
				<th colspan='5'>Tanggal</th>
				<th rowspan='3'>".$_SESSION['lang']['penjelasan']."</th>
				<th rowspan='3'>SDM Absensi</th>
			</tr>
			<tr class=rowheader style='text-align:center;font-weight:bold'>";
			$tab.="<th colspan=5 >".$param['tanggal']."</th>";
			$tab.="</tr>";
			$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			$tab.="<th>In</th>";
			$tab.="<th>Out</th>";
			$tab.="<th>In</th>";
			$tab.="<th>Out</th>";
			$tab.="<th>Abs</th>";
			$tab.="</tr>";
			$tab.="</thead>
			<tbody>";
			
			$nmsumber=array('manual'=>'BA Absensi','upload'=>'Fingerprint');
			
			$where=" and kodeorg like '".$unit."%'";
			$where.=" and tanggal='".$tgl."'";
			
			$str = "select * from ".$dbname.".sdm_absensidt where 1=1 ".$where."";
			$res = fetchdata($str);
			foreach($res as $bar){
				$sdmabsensi[$bar['idfp']]="&#10003;";
			}			

			$str= "select * from ".$dbname.".sdm_5mastershift where status='1' order by id";
			$res= fetchdata($str);
			foreach($res as $val){
				$arrnamashift[$val['shift']]=$val['namashift'];
			}
			 $text= getNamaOrg($unit,'tipe');

			if($text=='KEBUN'){
				$where=" and kodeorg='".$unit."'";
			}else{
				$where=" and subbagian='".$unit."'";
			}

			$where.=" and tanggalabsen='".$tgl."'";
			//$where.=" and sumber='upload'";
			$str = "select * from ".$dbname.".upload_absensi where 1=1 ".$where."";
			$res = fetchdata($str);
			foreach($res as $bar){
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".getKary($bar['karyawanid'],'nik')."</td>";
				$tab.="<td align=left>".getKary($bar['karyawanid'])."</td>";
				$tab.="<td>".getNamaJabatan(getKary($bar['karyawanid'],'kodejabatan'))."</td>";
				$tab.="<td align=left>".getNamaTipeKary(getKary($bar['karyawanid'],'tipekaryawan'))."</td>";
				$tab.="<td align=left>".$bar['shift']." - ".$arrnamashift[$bar['namashift']]."</td>";
				$tab.="<td align=center>".$nmsumber[$bar['sumber']]."</td>";
				$tab.="<td align=center width=75px>".waktunormal($bar['jam'])."</td>";
				if($bar['jam2']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam2'])."</td>";
				}
				if($bar['jam3']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam3'])."</td>";
				}
				if($bar['jam4']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam4'])."</td>";
				}
				
				$tab.="<td align=center>".$bar['absensi']."</td>";
				if($bar['penjelasan']!=''){					
					$tab.="<td align=left>".$bar['penjelasan']."<br><font style=font-size:9px;font-style:italic>".getKary($bar['updatedby'])." ".waktunormal($bar['updatedtime'])."</font></td>";
				}else{
					$tab.="<td align=center></td>";					
				}
				if($sdmabsensi[$bar['id']]!=''){					
					$tab.="<td align=center style=background-color:green;>".$sdmabsensi[$bar['id']]."</td>";
				}else{
					$tab.="<td align=center>x</td>";
				}
			}
	echo $tab;
break;
case'REKAPPNN':
	$tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center rowspan='2' style=width:75px>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center colspan='2'>" . $_SESSION['lang']['luas'] . "</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hk']."</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "<br>" . $_SESSION['lang']['panen'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['bjr'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</th>
            <th align=center colspan='2'>" . $_SESSION['lang']['afkir'] . "</th>
            
        
        </tr>
        <tr>
            
            
            <th align=center style=width:75px>" . $_SESSION['lang']['luasareaproduktif'] . "</th>
            <th align=center>" . $_SESSION['lang']['panen'] . "</th>
            <th align=center>" . $_SESSION['lang']['jjg'] . "</th>
            <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
            </tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekappnn_vw where divisi like '" . $unit . "%' and tanggal='" . $tgl . "' and posting='".$posting."'";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['blok'] . "</td>";
            $tab.="<td align=right>" . $bar['tahuntanam'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luasproduksi'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['tenagakerja'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgpanen']) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['bjr'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['kgkebun']) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgafkir']) . "</td>";
            $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
            @$tluasplan+=$bar['luasproduksi'];
            @$tluaspanen+=$bar['luaspanen'];
            @$ttk+=$bar['tenagakerja'];
            @$tjjgpnn+=$bar['jjgpanen'];
            @$tjjgafkir+=$bar['jjgafkir'];
            @$tkgkebun+=$bar['kgkebun'];
        }
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=right colspan=3><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluasplan, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluaspanen, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($ttk, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgpnn) . "</td>";
        $tab.="<td align=right><b>".@number_format($tkgkebun/$tjjgpnn,2)."</td>";
        $tab.="<td align=right><b>" . @number_format($tkgkebun) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgafkir) . "</td>";
        $tab.="<td></td>";
        $tab.="</tr>";
        $tab.="</table>";
	echo $tab;
break;
case'spb':
	$stream="<table cellpadding=5 cellspacing=1 class=sortable style=width:100%>
		<thead>
		<tr class=rowheader>
			<th align=center colspan=4>SPB KEBUN</th>
			<th align=center colspan=10>SPB PKS</th>
		</tr>
		<tr class=rowheader>
			<th align=center>".$_SESSION['lang']['nourut']."</th>
			<th align=center>".$_SESSION['lang']['nospb']."</th>
			<th align=center>Jjg</th>
			<th align=center>Kg WB</th>
		
			<th align=center>".$_SESSION['lang']['nourut']."</th>
			<th align=center>".$_SESSION['lang']['nospb']."</th>
			<th align=center>No Ticket</th>
			<th align=center>No Kend</th>
			<th align=center>Supir</th>
			<th align=center>".$_SESSION['lang']['pabrik']."</th>
			<th align=center>Jam Masuk</th>
			<th align=center>Jam Keluar</th>
			<th align=center>Jjg</th>
			<th align=center>Kg</th>
		</tr>
		
		</thead>";
	$str = "select * from ".$dbname.".pabrik_timbangan where tanggal like '".$tgl."%' and nospb like '%".$unit."%' and kodebarang='40000003'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$nopks='';
	while ($bar = $res->fetch()) {
		$nopks++;
		$nospbx[$bar['nospb']]=$bar['nospb'];
		$nospbpks[$bar['nospb']]=$bar['nospb'];
		$notransaksi[$bar['nospb']]=$bar['notransaksi'];
		$nokendaraan[$bar['nospb']]=$bar['nokendaraan'];
		$supir[$bar['nospb']]=$bar['supir'];
		$millcode[$bar['nospb']]=$bar['millcode'];
		$jammasuk[$bar['nospb']]=$bar['jammasuk'];
		$jamkeluar[$bar['nospb']]=$bar['jamkeluar'];
		$jjg[$bar['nospb']]=$bar['jumlahtandan1'];
		$kgpks[$bar['nospb']]=$bar['beratbersih'];
	}
	
	$str = "select * from ".$dbname.".kebun_spb_vw where tanggal like '".$tgl."%' and divisi like '%".$unit."%'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		@$nospbx[$bar['nospb']]=$bar['nospb'];
		@$nospbkebun[$bar['nospb']]=$bar['nospb'];
		@$jjgspb[$bar['nospb']]+=$bar['jjg'];
		@$kgspb[$bar['nospb']]+=$bar['kgwb'];
	}

	$nokbn=$nopks='';
	foreach ($nospbx as $nospb => $val){
		if(@$nospbkebun[$nospb]!=''){
			$nokbn+=1;
		}
		if(@$nospbpks[$nospb]!=''){
			$nopks+=1;
		}
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".($nospbkebun[$nospb]==''?'':$nokbn)."</td>";
		$stream.="<td align=center>".@$nospbkebun[$nospb]."</td>";
		$stream.="<td align=right>".@number_format($jjgspb[$nospb])."</td>";
		$stream.="<td align=right>".@number_format($kgspb[$nospb])."</td>";
		
		$stream.="<td align=center>".($nospbpks[$nospb]==''?'':$nopks)."</td>";
		$stream.="<td align=center>".@$nospbpks[$nospb]."</td>";
		$stream.="<td align=center>".@$notransaksi[$nospb]."</td>";
		$stream.="<td align=left>".@$nokendaraan[$nospb]."</td>";
		$stream.="<td align=left>".@$supir[$nospb]."</td>";
		$milext=makeOption($dbname,'kebun_spbht','nospb,penerimatbs',"nospb='".$nospbpks[$nospb]."'");
		if($millcode[$nospb]=='EXTM'){
			$milextnm=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
			$mill = $milextnm[$milext[$millcode[$nospb]]];
		}else{
			$mill = $millcode[$nospb];
		}
		$stream.="<td align=center>".$mill."</td>";
		$stream.="<td align=center>".@$jammasuk[$nospb]."</td>";
		$stream.="<td align=center>".@$jamkeluar[$nospb]."</td>";
		$stream.="<td align=right>".@number_format($jjg[$nospb])."</td>";
		$stream.="<td align=right>".@number_format($kgpks[$nospb])."</td>";
		
		$ttljjgkbn+=$jjgspb[$nospb];
		$ttlkgkbn+=$kgspb[$nospb];
		$ttljjgpks+=$jjg[$nospb];
		$ttlkgpks+=$kgpks[$nospb];
	}
		
		$stream.="</tr><tr class=rowcontent>";
		$stream.="<td align=center colspan=2>T O T A L</td>";
		$stream.="<td align=right>".number_format($ttljjgkbn)."</td>";
		$stream.="<td align=right>".number_format($ttlkgkbn)."</td>";
		$stream.="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
		$stream.="<td align=right>".number_format($ttljjgpks)."</td>";
		$stream.="<td align=right>".number_format($ttlkgpks)."</td>";
	$stream.="</table>";
	echo $stream;

break;
}
?>