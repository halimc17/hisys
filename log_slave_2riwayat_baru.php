<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

//status PP masih harus dikaji ulang
$jnsapp = "PR";
$nmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$satBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');

$stPp = array("0" => "Proses Persetujuan", "1" => "sudah selesai di sisi user", "2" => "PP sudah bisa di PO", "3" => "Ditolak");
$stPo = array("0" => "Belum Selesai", "1" => "Sudah selesai dan diajukan", "2" => "Purchase Order (PO) sudah dapat di kirim (persetujuan selesai)","3" => "Barang sudah diterima");

$proses = isset($_POST['proses']) ? $_POST['proses'] : $_GET['proses'];
$nopp = isset($_POST['nopp']) ? $_POST['nopp'] : $_GET['nopp'];
$tgl = isset($_POST['tgl']) ? tanggalsystem($_POST['tgl']) : tanggalsystem($_GET['tgl']);
$per1 = isset($_POST['per1']) ? $_POST['per1'] : $_GET['per1'];
$per2 = isset($_POST['per2']) ? $_POST['per2'] : $_GET['per2'];
$lok = isset($_POST['lok']) ? $_POST['lok'] : $_GET['lok'];
$stat = isset($_POST['stat']) ? $_POST['stat'] : $_GET['stat'];
$sup = isset($_POST['sup']) ? $_POST['sup'] : $_GET['sup'];
$nama = isset($_POST['nama']) ? $_POST['nama'] : $_GET['nama'];
$psj = isset($_POST['psj']) ? $_POST['psj'] : $_GET['psj'];
$previewdata = isset($_POST['previewdata']) ? $_POST['previewdata'] : $_GET['previewdata'];
$statuspo = isset($_POST['statuspo']) ? $_POST['statuspo'] : $_GET['statuspo'];
$dept = isset($_POST['dept']) ? $_POST['dept'] : $_GET['dept'];

if ($proses == 'excel' || $proses == 'pdf') {
    $nopp = $_GET['nopp'];
    $tgl = tanggalsystem($_GET['tgl']);
    $per1 = $_GET['per1'];
    $per2 = $_GET['per2'];
    $lok = $_GET['lok'];
    $stat = $_GET['stat'];
    $sup = $_GET['sup'];
    $nama = $_GET['nama'];
    $psj = $_GET['psj'];
}

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
	$jlhcolspan = 11;
} else {
    $stream = "<table class=sortable cellpadding=5 cellspacing=1>";
	$jlhcolspan = 12;
}

$stream.="<thead class=rowheader>
	<tr class=rowheader>
		<th rowspan=2 bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['nourut'] . "</th>
		<th colspan=".$jlhcolspan." bgcolor=#CCCCCC align=center>PR/SR</th>
        <th colspan=4 bgcolor=#CCCCCC align=center>DPH</th>
		<th colspan=4 bgcolor=#CCCCCC align=center>RPH</th>
		<th colspan=6 bgcolor=#CCCCCC align=center>PO/SO</th>
		<th colspan=5 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['gudang']."</th>
        <th colspan=4 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['invoice']."</th>
		<th rowspan=2 bgcolor=#CCCCCC align=center>Total Ostd</th>
		<th rowspan=2 bgcolor=#CCCCCC align=center>Ostd</th>
	</tr>		
	
	<tr class=rowheader>
		<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['nopp'] . "</th> 
		<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['tanggal'] . "</th>
		<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['kodebarang'] . "</th>    
		<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['namabarang'] . "</th>
		<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['jumlah'] . "</th>
		<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['satuan'] . "</th>
		<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan'] . "</th>
		<th bgcolor=#CCCCCC  align=center>Tanggal Approval</th>  
		<th bgcolor=#CCCCCC  align=center>Selisih Tanggal</th>  
		<th bgcolor=#CCCCCC  align=center>Requster</th>   
		<th bgcolor=#CCCCCC  align=center>Ostd Purchaser</th>   
		";
			
if ($proses != 'excel'){
    $stream.= "<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['chat'] . "</th>   ";
} 

$stream.="<th bgcolor=#CCCCCC  align=center>Nomor</th>
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['tanggal'] . "</th>
	<th bgcolor=#CCCCCC  align=center>Purchaser</th>
	<th bgcolor=#CCCCCC  align=center>Ostd</th>  
	<th bgcolor=#CCCCCC  align=center>Nomor</th>
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['tanggal'] . "</th>
	<th bgcolor=#CCCCCC  align=center>Verificator</th>
	<th bgcolor=#CCCCCC  align=center>Ostd</th>
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['nopo'] . "</th>     
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['tgl_po'] . "</th> 
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['jumlah'] . " PO</th> 
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['namasupplier'] . "</th>     
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['status'] . " PO</th>
	<th bgcolor=#CCCCCC  align=center>Tanggal Approval</th> 
	<th bgcolor=#CCCCCC  align=center>Ostd</th>
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['rapbNo'] . "</th>
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['tanggal'] . "</th>
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['jumlah'] . " Diterima</th>
	<th bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['belumterima'] . "</th>
	<th bgcolor=#CCCCCC  align=center>Ostd</th>";
	
	$stream.= "<th bgcolor=#CCCCCC align=center>no invoice</th>
		<th bgcolor=#CCCCCC align=center>nilai invoice</th>
		<th bgcolor=#CCCCCC align=center>tanggal</th>";
$stream.="</tr></thead>";

## lokasitugas lock
/*if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
	
}else if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
    $where.=" and a.nopp not like 'HO%' ";
}else{
	$where.=" and a.kodeorg='" . $_SESSION['empl']['kodeorganisasi'] . "' and a.nopp not like 'HO%' ";
}*/

if ($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') {
    $where.=" and a.nopp not like 'HO%' ";
}

## nopp
if ($nopp != '') {
    $where.=" and a.nopp like '%" . $nopp . "%' ";
}

## tanggal pp
if ($tgl != '') {
    $where.="and a.tanggal='" . $tgl . "' ";
}

## lokasi pembelian
if ($lok != '') {
    // $where.="and a.lokalpusat='" . $lok . "'";
    $where.="and a.kodeorg='" . $lok . "'";
}

## nama supplier
if ($sup != '') {
    $where.="and b.namasupplier like '%" . $sup . "%' ";
}

## status po
if ($statuspo != '') {

	if($previewdata==1){
		exit('warning : Jika status po terisi, close PO tidak boleh di centang.');
	}

	$arrstatuspo=explode('#', $statuspo);
    $where.="and d.statuspo='".$arrstatuspo[0]."'";
}

## stat pp
if ($stat != '') {
    if ($stat == '1') {
        $where.=" and a.close='1' and a.status!='3'";
    }
    if ($stat == '2') {
        $where.=" and a.close='2' and a.purchaser='0000000000' and a.create_po='' and ditolakoleh=0";
    }
    if ($stat == '3') {
        $where.="and  a.create_po!='' and b.nopo is not null";
    }
    if ($stat == '4') {
        $where.="and (a.create_po='' or a.create_po='0') and a.purchaser!='0000000000' and a.close='2' and b.nopo is null ";
    }
    if ($stat == '5') {
        $where.="and a.status='3' and (a.close='2' or a.close='1') ";
    }
}

if ($nama != ''){
    $where.=" and a.kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where namabarang like '%".$nama."%')";
}

if ($psj != ''){
	$where.="and (a.persetujuan1='".$psj."' || a.persetujuan2='".$psj."' || a.persetujuan3='".$psj."' || a.persetujuan4='".$psj."' || a.persetujuan5='".$psj."')";
}

$str="select sum(jumlah) as jumlah,nopp,kodebarang from ".$dbname.".log_transaksi_vw where nopp in (select nopp from ".$dbname.".log_prapo_vw where  tanggal between '".tanggalsystemn($per1)."' and '".tanggalsystemn($per2)."') group by nopp,kodebarang ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$totalterima[$bar['nopp']][$bar['kodebarang']]=$bar['jumlah'];
}

if($dept!=''){
	$where.=" and a.requester in (select karyawanid from " . $dbname . ".datakaryawan where bagian='".$dept."')";
}

$tempnopp=$tempkdbrg='';
$str = "select a.requester, a.tipepp,c.hasilpersetujuan1,d.closed,d.keteranganclose,d.keterangan,a.kodeorg,"
		. " a.nopp,a.tanggal as tanggalpp,a.purchaser,a.kodebarang,a.jumlah as jumlahpp,a.kodevhc,a.status,"
        . " a.close,a.create_po,a.lokalpusat,b.nopo as nopo,b.tanggal as tanggalpo,"
        . "b.jumlahpesan as jumlahpo,b.kodesupplier,b.kodesupplier,b.namasupplier,"
        . " b.statuspo,c.notransaksi,c.tanggal as tanggalba,c.jumlah as jumpengud "
        . " from " . $dbname . ".log_prapo_vw a left join " . $dbname . ".log_po_vw b on a.nopp=b.nopp and a.kodebarang=b.kodebarang"
        . " left join " . $dbname . ".log_transaksi_vw c on b.nopo=c.nopo and b.nopp=c.nopp and b.kodebarang=c.kodebarang "
        . " left join " . $dbname . ".log_poht d on b.nopo=d.nopo where  a.tanggal between '".tanggalsystemn($per1)."' and '".tanggalsystemn($per2)."' ".$where." order by a.nopp desc,a.tanggal desc ";
// echo $str;
		#and c.hasilpersetujuan1!='2'
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){

	##untuk filter data tutup po
	if($bar['closed']=='1' && $bar['statuspo']!=3){
		if (!is_null($bar['keteranganclose']) || !is_null($bar['keterangan'])) {
			if($previewdata==0){
				continue;
			}
		}else{
			if($previewdata==1){
				continue;
			}
		}
	}else if($bar['closed']=='1' && $bar['statuspo']==3){
		if($previewdata==1){
			continue;
		}
	}else{
		if($previewdata==1){
			continue;
		}
	}

	##untuk filter status po 2 SR dan PR
	if ($bar['tipepp']=='SR' && $bar['statuspo']==2) {
		if ($statuspo=='2#PO') {
			continue;
		}
	}

	if ($bar['tipepp']=='PR' && $bar['statuspo']==2) {
		if ($statuspo=='2#SO') {
			continue;
		}
	}

	$no++;
	$stream.="<tr class=rowcontent>";
	
	## Get Unit PR/SR
	$exppr = explode('/',$bar['nopp']);
	$kodeunit = $exppr[4];
	
	## Chat PR/SR
	$strChat = "select * from ".$dbname.".log_pp_chat where kodebarang='".$bar['kodebarang']."' and nopp='".$bar['nopp']."'";
    $resChat=fetchdata($strChat);
	if (count($resChat) > 0) {
        $ingChat = "<img src='images/chat1.png' onclick=\"loadPPChat('" . $bar['nopp'] . "','" . $bar['kodebarang'] . "',event);\" class=resicon>";
    } else {
        $ingChat = "<img src='images/chat0.png'  onclick=\"loadPPChat('" . $bar['nopp'] . "','" . $bar['kodebarang'] . "',event);\" class=resicon>";
    }
	
	## Detail Data DPH
	$strrph="select * from ".$dbname.".log_permintaanhargadt left join log_perintaanhargaht on log_perintaanhargaht.nomor = log_permintaanhargadt.nomor and log_perintaanhargaht.nourut = log_permintaanhargadt.nourut where log_permintaanhargadt.nopp='".$bar['nopp']."' and log_permintaanhargadt.kodebarang='".$bar['kodebarang']."' group by log_permintaanhargadt.nomor";
	$resrph=fetchdata($strrph);
	
	## Detail Data Invoice
	$strinvoice="select * from ".$dbname.".keu_tagihanht where nopo = '".$bar['nopo']."' and nopo!=''";
	$resinvoice=fetchdata($strinvoice);

	## PR/SR
	$stream.="<td align=center>".$no."</td>
		<td style='text-align:center;color:blue;cursor:pointer' onclick=\"previewDetail2('".$bar['nopp']."','".$bar['kodebarang']."',event)\">".$bar['nopp']."</td>
		<td style='min-width:75px;text-align:center'>".tanggalnormal($bar['tanggalpp'])."</td>
		<td>".$bar['kodebarang']."</td>
		<td>".@$nmBrg[$bar['kodebarang']]."</td>
		<td align=right>".@number_format($bar['jumlahpp'])."</td>
		<td>".@$satBrg[$bar['kodebarang']]."</td>";
	
	$sttstj = "";	
	$outpr = "";
	$arrpr = array();
	## Status Persetujuan PR/SR
	if($bar['close']=='0'){
		$sttstj = "Proses Create PR/SR";
	}else if($bar['close']=='2'){
		$sttstj = "Disetujui";
	}else if($bar['close']=='3'){
		$sttstj = "Ditolak";
	}else{
		## Get Count Approval PR/SR
		$countApp = getCountApproval($jnsapp,$kodeunit);
		for($i=1;$i<=$countApp;$i++){
			$arrDetail = detailApprove($i,$bar['nopp'],$jnsapp);
			if($i=='1' && $arrDetail['status']=='0'){
				$sttstj = "Menunggu ".$_SESSION['lang']['persetujuan'].$i;
				continue;
			}
			if($arrDetail['status']=='2'){
				$sttstj = "Ditolak";
				continue;
			}
			if($countApp == $i && $arrDetail['status']=='1'){
				$sttstj = "Disetujui";
				$start = strtotime($bar['tanggalpp']); 
				$end = strtotime($arrDetail['tanggal']); 
				$hasil = abs($end - $start);
				$outpr = floor($hasil/(60*60*24));
				$arrpr[$bar['nopp']][$bar['kodebarang']]['tanggalpr'] = $arrDetail['tanggal'];
				continue;
			}
			if($arrDetail['status']=='1'){
				$sttstj = "Menunggu ".$_SESSION['lang']['persetujuan'].($i+1);
			}
		}
	}
	
	$strapv="select * from ".$dbname.".approval where notransaksi='".$bar['nopp']."' order by level desc limit 1";
	$resapv=fetchdata($strapv);
	foreach($resapv as $barapv){
		$tglapvpr=$barapv['tanggal'];
	}
	
	#= tanggal terakhir approval
	

	$stream.="<td align=center>".$sttstj."</td>
	
	<td align=center>".tanggalnormal(substr($tglapvpr,0,10))."</td>
	<td align=center>".selisihari($bar['tanggalpp'],substr($tglapvpr,0,10))."</td>
	
		<td style='text-align:center'>".getKary($bar['requester'])."</td>
		<td style='text-align:center'>".$outpr."</td>";
	
	if ($proses != 'excel') {
		$stream.="<td style='text-align:center;'>".$ingChat."</td>";
	}
	
	$norph=0;
	$stream.="<td><table>";
	foreach ($resrph as $keyrph => $valrph) {
		$norph++;
		$stream.="<tr>
			<td>".$norph.".</td>
			<td style='text-align:center;color:blue;cursor:pointer' onclick=\"previewlink('".$valrph['nomor']."', '', 'Detail Riwayat Perbandingan Harga' ,event)\">".$valrph['nomor']."</td>
		</tr>";
	}
	$stream.="</table></td>";
	
	$stream.="<td><table>";
	foreach ($resrph as $keyrph => $valrph) {
		$stream.="<tr>
			<td style='min-width:75px;text-align:center'>".tanggalnormal($valrph['tanggal'])."</td>
		</tr>";
	}
	$stream.="</table></td>";
	
	$stream.="<td><table>";
	foreach ($resrph as $keyrph => $valrph) {
		$optkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$valrph['purchaser']."'");
		$stream.="<tr>
			<td style='min-width:150px;text-align:center'>".$optkar[$valrph['purchaser']]."</td>
		</tr>";
	}
	$stream.="</table></td>";
	
	$outrph = "";
	$stream.="<td style='text-align:center'><table>";
	foreach ($resrph as $keyrph => $valrph) {
		if(isset($arrpr[$bar['nopp']][$bar['kodebarang']]['tanggalpr'])){
			$start = strtotime($arrpr[$bar['nopp']][$bar['kodebarang']]['tanggalpr']); 
			$end = strtotime($valrph['tanggal']); 
			$hasil = abs($end - $start);
			$outrph = floor($hasil/(60*60*24));
			$stream.="<tr>
				<td style='text-align:center'>".$outrph."</td>
			</tr>";
		}else{
			$stream.="<tr>
				<td></td>
			</tr>";
		}
	}
	$stream.="</table></td>";
	$outdph = "";
	$strdph = "select * from ".$dbname.".log_permintaanhargadt where nopp='".$bar['nopp']."' and kodebarang='".$bar['kodebarang']."' and norph!=''";
	$resdph = fetchdata($strdph);
	if(count($resdph) > 0){
		$optkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$resdph[0]['verificator']."'");
		$opttgl = makeOption($dbname,'log_perintaanhargaht','nomor,tanggal',"nomor='".$resdph[0]['nomor']."' and nourut='".$resdph[0]['nourut']."'");
		$optsup = makeOption($dbname,'log_perintaanhargaht','nomor,supplierid',"nomor='".$resdph[0]['nomor']."' and nourut='".$resdph[0]['nourut']."'");
		$start = strtotime($opttgl[$resdph[0]['nomor']]); 
		$end = strtotime($resdph[0]['tanggalverifikasi']); 
		$hasil = abs($end - $start);
		$outdph = floor($hasil/(60*60*24));
		$stream.="<td style='text-align:center;color:blue;cursor:pointer' onclick=\"previewlinkpemenang('".$resdph[0]['norph']."', '".$optsup[$resdph[0]['nomor']]."', 'Detail Pemenang Perbandingan Harga' ,event)\">".$resdph[0]['norph']."</td>
		<td style='min-width:75px;text-align:center'>".tanggalnormal($resdph[0]['tanggalverifikasi'])."</td>
		<td>".$optkar[$resdph[0]['verificator']]."</td>
		<td style='text-align:center'>".$outdph."</td>";
	}else{
		$stream.="<td></td>
		<td></td>
		<td></td>
		<td></td>";
	}
	
	## PO/SO
	## Get Unit PO/SO
	$outpo = "";
	$exppr = explode('/',$bar['nopo']);
	$kodeunit = $exppr[4];
	$countApp = getCountApproval("PO",$kodeunit);
	for($i=1;$i<=$countApp;$i++){
		$arrDetail = detailApprove($i,$bar['nopo'],"PO");
		if($countApp == $i && $arrDetail['status']=='1'){
			$start = strtotime($bar['tanggalpo']); 
			$end = strtotime($arrDetail['tanggal']); 
			$hasil = abs($end - $start);
			$outpo = floor($hasil/(60*60*24));
			continue;
		}
	}

	if($bar['closed']=='1'){
		if(strpos($bar['keteranganclose'], ",tanggal tutup : ")){
			$status = "Become Out Standing";
		}
		if(strpos($bar['keterangan'], ",tanggal tutup : ")){

			$status = "Cancel";
		}

		if ($bar['statuspo']==3 || $bar['statuspo']==2|| $bar['statuspo']==1) {
			$status=@$stPo[$bar['statuspo']];
		}
	}
	else
	{
		$status=@$stPo[$bar['statuspo']];
		
		if ($bar['tipepp']!='SR' && $bar['statuspo']==3) {
			/*if ($bar['jumpengud']<$bar['jumlahpo']) {
				$status='Barang sudah diterima.';
			}else if ($bar['jumpengud']==$bar['jumlahpo']) {
				$status=@$stPo[$bar['statuspo']];
			}*/
			$status=@$stPo[$bar['statuspo']];
		}

		if ($bar['tipepp']=='SR' && $bar['statuspo']==2) {
			$status='Service Order (SO) tidak ada pengiriman (persetujuan selesai)';
		}
	}

	$strapv="select * from ".$dbname.".approval where notransaksi='".$bar['nopo']."' order by level desc limit 1";
	$resapv=fetchdata($strapv);
	foreach($resapv as $barapv){
		$tglapvpO=$barapv['tanggal'];
	}
	#= tanggal terakhir approval
	
	$stream.="<td>".$bar['nopo']."</td>
		<td style='min-width:75px;text-align:center'>".($bar['tanggalpo']==''?'':tanggalnormal($bar['tanggalpo']))."</td>
		<td align=right>".number_format($bar['jumlahpo'])."</td>
		<td>".$bar['namasupplier']."</td>
		<td>".$status."</td>
		<td style='text-align:center'>".tanggalnormal(substr($tglapvpO,0,10))."</td>
		<td style='text-align:center'>".$outpo."</td>";
		
	## Penerimaan Gudang
	$bgcolor=$title='';
	$nowdate=date('Y-m-d');
	if ($bar['notransaksi']!='') {
		if($bar['hasilpersetujuan1']=='2'){
			$bgcolor='bgcolor=red';
			$title="title='Transaksi Gudang ditolak'";
		} else {
			$start = strtotime($bar['tanggalpo']); 
			$end = strtotime($bar['tanggalba']); 
			$hasil = abs($end - $start);
			$outba = floor($hasil/(60*60*24));
			@$totalterimaDt[$bar['nopo']][$bar['kodebarang']]+=$bar['jumpengud'];
		}
	} else {
		$strba="select a.notransaksi,a.jumlah,b.tanggal from ".$dbname.".log_penerimaanpodt a left join ".$dbname.".log_penerimaanpoht b on a.notransaksi=b.notransaksi where a.nopo='".$bar['nopo']."' and a.kodebarang='".$bar['kodebarang']."'";
		$resba=fetchdata($strba);
		$bar['notransaksi']=$resba[0]['notransaksi'];
		$bar['tanggalba']=$resba[0]['tanggal'];
		$totalterimaDt[$bar['nopo']][$bar['kodebarang']]=$resba[0]['jumlah'];
		$start = strtotime($bar['tanggalpo']); 
		$end = strtotime($bar['tanggalba']); 
		$hasil = abs($end - $start);
		$outba = floor($hasil/(60*60*24));
		
		if($bar['notransaksi']==''){
			$strba="select noba,tanggal from ".$dbname.".log_baservis where noso='".$bar['nopo']."'";
			$resba=fetchdata($strba);
			$bar['notransaksi']=$resba[0]['noba'];
			$bar['tanggalba']=$resba[0]['tanggal'];
			$totalterimaDt[$bar['nopo']][$bar['kodebarang']]=$bar['jumlahpo'];
			$start = strtotime($bar['tanggalpo']); 
			$end = strtotime($bar['tanggalba']); 
			$hasil = abs($end - $start);
			$outba = floor($hasil/(60*60*24));
		}
	}
	
	$stream.="<td ".$bgcolor." ".$title.">".$bar['notransaksi']."</td>
		<td style='min-width:75px;text-align:center' ".$bgcolor." ".$title.">".($bar['tanggalba']==''?'':tanggalnormal($bar['tanggalba']))."</td>
		<td align=right ".$bgcolor." ".$title.">".@number_format($totalterimaDt[$bar['nopo']][$bar['kodebarang']])."</td>";
	$stream.="<td align=right ".$bgcolor." ".$title.">".@number_format(($bar['jumlahpo']-$totalterimaDt[$bar['nopo']][$bar['kodebarang']]))."</td>";
	$stream.="<td align=right ".$bgcolor." ".$title.">".$outba."</td>";
			
	## Tagihan
	$nowdate=date('Y-m-d');
	if($resinvoice[0]['noinvoice'] != ''){
		$start = strtotime($bar['tanggalpp']); 
		$start1 = strtotime($bar['tanggalba']); 
		$end = strtotime($resinvoice[0]['tanggal']); 
		$end1 = strtotime($nowdate); 
		$hasil = abs($end - $start);
		$hasil1 = abs($end1 - $start1);
		$invoicedate = floor($hasil/(60*60*24));
		$outinv=floor($hasil1/(60*60*24));
		
		$stream.="<td>".$resinvoice[0]['noinvoice']."</td>";
		$stream.="<td style='text-align:right'>".number_format($resinvoice[0]['nilaiinvoice'])."</td>";
		$stream.="<td style='min-width:75px;text-align:center'>".tanggalnormal($resinvoice[0]['tanggal'])."</td>";
		$stream.="<td style='text-align:center'>".$outinv."</td>";   
		$stream.="<td style='text-align:center'>".$invoicedate."</td>";  
	}
	else
	{
		$stream.="<td>".$resinvoice[0]['noinvoice']."</td>";
		$stream.="<td>".$resinvoice[0]['nilaiinvoice']."</td>";
		$stream.="<td>".$resinvoice[0]['tanggal']."</td>";
		$stream.="<td style='text-align:center'></td>";    
		$stream.="<td></td>";
	}
	$stream.="</tr>";
	
    /*## buat tanggal
    if (!is_null($bar['tanggalpo']) || $bar['tanggalpo'] != '') {
        $tglA = substr($bar['tanggalpo'], 0, 4); //po
        $tglB = substr($bar['tanggalpo'], 5, 2);
        $tglC = substr($bar['tanggalpo'], 8, 2);
        $tgl2 = $tglA . $tglB . $tglC;
        $tGl1 = substr($bar['tglp4'], 0, 4);
        $tGl2 = substr($bar['tglp4'], 5, 2);
        $tGl3 = substr($bar['tglp4'], 8, 2);
		
		$tgl2 = $tglA . $tglB . $tglC;
        $tgl1 = $tGl1 . $tGl2 . $tGl3;
        $stat = 1;
        $nopo = $bar['nopo'];

    } else {
        $tGl1 = substr($bar['tglp4'], 0, 4);
        $tGl2 = substr($bar['tglp4'], 5, 2);
        $tGl3 = substr($bar['tglp4'], 8, 2);
		
        $tgl1 = $tGl1 . $tGl2 . $tGl3;
        $tgl2 = date('Y-m-d');
        $stat = 0;
        $nopo = "Blm PO";
    }



    $starttime = strtotime($tgl1); //time();// tanggal sekarang
    $endtime = strtotime($tgl2); //tanggal pembuatan dokumen
    $timediffSecond = abs($endtime - $starttime);
    $base_year = min($tGl1, $tglA);
    $diff = mktime(0, 0, intval($timediffSecond), 1, 1, intval($base_year));
    $jmlHari = date("j", $diff) - 1;
    //tutup tanggal
    //periksa chat==================================
    $strChat = "select * from " . $dbname . ".log_pp_chat where "
            . " kodebarang='" . $bar['kodebarang'] . "' and nopp='" . $bar['nopp'] . "'";
    $resChat=$owlPDO->query($strChat) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=owlBaris($resChat);
	if ($numrows > 0) {
        $ingChat = "<img src='images/chat1.png' onclick=\"loadPPChat('" . $bar['nopp'] . "','" . $bar['kodebarang'] . "',event);\" class=resicon>";
    } else {
        $ingChat = "<img src='images/chat0.png'  onclick=\"loadPPChat('" . $bar['nopp'] . "','" . $bar['kodebarang'] . "',event);\" class=resicon>";
    }

    //status pp
    @$no+=1;
	
	
	if($tempnopp==$bar['nopp'] and $tempkdbrg==$bar['kodebarang']){
		$bar['nopp']='';
		$bar['tanggalpp']='';
		$bar['kodebarang']='';
		$bar['jumlahpp']='';
		$bar['close']='';
		$jmlHari='';
		$ingChat='';
	}


 
    
	## mengambil detail rph
	$strrph="select * from ".$dbname.".log_permintaanhargadt left join log_perintaanhargaht on log_perintaanhargaht.nomor = log_permintaanhargadt.nomor where log_permintaanhargadt.nopp='".$bar['nopp']."' and log_permintaanhargadt.kodebarang='".$bar['kodebarang']."' group by log_permintaanhargadt.nomor";
	$resrph=fetchdata($strrph);

	## mengambil detail invoice
	$strinvoice="select * from ".$dbname.".keu_tagihanht where nopo = '".$bar['nopo']."'";
	$resinvoice=fetchdata($strinvoice);

	
	if (!isset($stPo[$bar['statuspo']])){
		$stPo[$bar['statuspo']] = "Belum PO";
	}
	
	$stream.="<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td>".$bar['nopp']."</td>";
		
	if($bar['tanggalpp']==''){
		$stream.="<td></td>";
	}else{
		$stream.="<td style='min-width:75px;text-align:center'>".tanggalnormal($bar['tanggalpp'])."</td>";
	}
	
	$stream.="<td>".$bar['kodebarang']."</td>
		<td>".@$nmBrg[$bar['kodebarang']]."</td>
		<td align=right>".@number_format($bar['jumlahpp'])."</td>
		<td>".@$satBrg[$bar['kodebarang']]."</td>";
	
	if (($bar['close'] == 2 || $bar['close'] == 1) && $bar['status'] == 3){
		$stream.="<td style='text-align:center'>Ditolak</td>";
	}else{
		$stream.="<td>" . @$stPp[$bar['status']] . "</td>";
	}
	
	$stream.="<td align=right>".$jmlHari."</td>";
	
	if ($proses != 'excel') {
		$stream.="<td style='text-align:center;'>".$ingChat."</td>";
	}

	$start = strtotime($resrph[0]['tanggal']); 
	$end = strtotime($bar['tanggalpp']); 
	$hasil = abs($end - $start);
	$jmlhdate = floor($hasil/(60*60*24));

	$norph=0;
	$stream.="<td><table>";
	foreach ($resrph as $keyrph => $valrph) {
		$norph++;
		$stream.="<tr>
			<td>".$norph.".</td>
			<td>".$valrph['nomor']."</td>
		</tr>";
	}

	$stream.="</table></td>";
	
	$stream.="<td>".tanggalnormal($valrph['tanggal'])."</td>"; 
	$stream.="<td align=right>".$jmlhdate."</td>"; 


	$stream.="<td>" . $bar['nopo'] . "</td>";
	$stream.="<td>" . $bar['tanggalpo'] . "</td>";
	$stream.="<td align=right>" . number_format($bar['jumlahpo']) . "</td>";
	$stream.="<td>" . $bar['namasupplier'] . "</td>";
	$stream.="<td>" . @$stPo[$bar['statuspo']] . "</td>";
	$stream.="<td>" . $bar['notransaksi'] . "</td>";
	$stream.="<td>" . $bar['tanggalba'] . "</td>";
	$stream.="<td align=right>".@number_format($bar['jumpengud'])."</td>";
	$totalterimaDt[$bar['nopo']]+=$bar['jumpengud'];
	$stream.="<td align=right>".@number_format(($bar['jumlahpo']-$totalterimaDt[$bar['nopo']]))."</td>";

	$start = strtotime($resinvoice[0]['tanggal']); 
	$end = strtotime($bar['tanggalpp']); 
	$hasil = abs($end - $start);
	$invoicedate = floor($hasil/(60*60*24));

	if($resinvoice[0]['noinvoice'] != ''){
		$stream.="<td>".$resinvoice[0]['noinvoice']."</td>";
		$stream.="<td>".$resinvoice[0]['nilaiinvoice']."</td>";
		$stream.="<td>".tanggalnormal($resinvoice[0]['tanggal'])."</td>";
		$stream.="<td>".$invoicedate."</td>";   
	}else{
		$stream.="<td>".$resinvoice[0]['noinvoice']."</td>";
		$stream.="<td>".$resinvoice[0]['nilaiinvoice']."</td>";
		$stream.="<td>".$resinvoice[0]['tanggal']."</td>";
		$stream.="<td></td>";   
	}

	if ($proses != 'excel') {
		 $stream.="<td align=center>";
		 if($stat == '1') {
		  $stream.="<img onclick=\"previewTrace('".$bar['nopp']."','".$bar['kodebarang']."','Trace PP',event);\" title=\"Trace PP\" class=\"resicon\" src=\"images/tool.png\">";
		 }
		 $stream.="<img onclick=\"previewDetail('".$bar['nopp']."','Detail PP',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\">
							<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_prapoht','" . $bar['nopp'] . "','','log_slave_print_log_pp',event);\"></td>";
	 } 
	$stream.="</tr>";
	
	$tempnopp=$bar['nopp'];
	$tempkdbrg=$bar['kodebarang'];*/
}

$stream.="</table>";
$stream.="<tbody></table>";

	switch ($proses) {
		######PREVIEW
		case 'preview':
			echo $stream;
		break;

		######EXCEL	
		case 'excel':
			//$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
			$tglSkrg = date("Ymd");
			$nop_ = "Laporan_riwayat_PP";
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
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/" . $nop_ . ".xls';
					</script>";
				}
				fclose($handle);
			}
		break;

		default;
	}
?>