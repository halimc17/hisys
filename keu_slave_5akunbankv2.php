<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$method=checkPostGet('method','');
$pt=checkPostGet('pt','');
$noakun=checkPostGet('noakun','');
$bank=checkPostGet('bank','');
$rek=checkPostGet('rek','');
$cabang=checkPostGet('cabang','');
$atasnama=checkPostGet('atasnama','');
$matauang=checkPostGet('matauang','');
$swift_code=checkPostGet('swift_code','');
$fungsi=checkPostGet('fungsi',''); $fungsi='Operasional';
$inisialurut=checkPostGet('inisialurut','');
$status=checkPostGet('status','');
$email=checkPostGet('email','');
$noakuncoa=checkPostGet('noakuncoa','');

$unitsch=checkPostGet('unitsch','');
$banksch=checkPostGet('banksch','');
$reksch=checkPostGet('reksch','');

$str = "SELECT * FROM ".$dbname.".keu_5akun where noakun like '111%' and detail=1 and aktif=1";
$res=fetchdata($str);
foreach($res as $bar){
	$namaakun[$bar['noakun']]=$bar['namaakun'];
}

$str = "SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)=4";
$res=fetchdata($str);
foreach($res as $bar){
	$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}


$noakunx= preg_replace("/[^0-9]/","",$rek);
$nmbank=makeOption($dbname,'keu_5daftarbank','kodebank,namabank');
$nmstatus=array('0'=>'Non Active','1'=>'Active');
switch ($method) {
case'nonaktif':
	$str = "update " . $dbname . ".keu_5akunbank set status='".$status."' where pemilik='".$pt."' and noakun='".$noakun."'";
	try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
break;

case'getinisialurut':

	$sql = "SELECT inisial FROM ".$dbname.".keu_5daftarbank where kodebank='".$bank."'"; 
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
		$inisial=$bar['inisial'];
	

	$sql = "SELECT count(*) as jumlah FROM ".$dbname.".keu_5akunbank where namabank='".$bank."'"; 
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	//exit('error'.$bar['jumlah']);
		$nomor=$bar['jumlah']+1;
	
	echo $inisial.$nomor;
break;

case'getbank':
	$optbank= "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sql = "SELECT * FROM ".$dbname.".keu_5daftarbank where status ='1'";
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($data = $res->fetch()) {
		$optbank.="<option value=".$data['kodebank'].">".$data['namabank']."</option>";
	}
	echo $optbank;
break;
case 'insert':
	$str = "insert into ".$dbname.".keu_5akunbank (`pemilik`,`noakun`,`namabank`,`rekening`,`updateby`, `fungsi`,`cabang`,`atasnama`,`matauang`,`swift_code`,`email`,`inisialurut`, `updatetime`,`status`,noakuncoa)
		values ('".$pt."','".$noakunx."','".$bank."','".$rek."','".$_SESSION['standard']['userid']."',
		'".$fungsi."','".$cabang."','".$atasnama."','".$matauang."','".$swift_code."','".$email."','".$inisialurut."',
		'".date('Y-m-d H:i')."','1','".$noakuncoa."')";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'update':
	$str = "update ".$dbname.".keu_5akunbank set namabank='".$bank."', rekening='".$rek."', fungsi='".$fungsi."', cabang='".$cabang."',atasnama='".$atasnama."',matauang='".$matauang."',swift_code='".$swift_code."',updateby='".$_SESSION['standard']['userid']."', email='".$email."',inisialurut='".$inisialurut."',noakuncoa='".$noakuncoa."' where pemilik='".$pt."' and noakun='".$noakun."'";
	// exit('error'.$str);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'loadData':
	echo "<div id=container>
	<table class=sortable cellspacing=1 border=0 style=min-width:100%>
	<thead>
	<tr class=rowheader>
	<td align=center>No</td>
	<td align=center>".$_SESSION['lang']['unit']."</td>
	<td align=center>".$_SESSION['lang']['noakun']."</td>
	<td align=center>".$_SESSION['lang']['namabank']."</td>
	<td align=center>".$_SESSION['lang']['cabang']."</td>
	<td align=center>".$_SESSION['lang']['norek']."</td>
	<td align=center>".$_SESSION['lang']['atasnama']."</td>
	<td align=center>".$_SESSION['lang']['matauang']."</td>
	<td align=center>".$_SESSION['lang']['inisial']." ".$_SESSION['lang']['nourut']."</td>
	<td align=center>".$_SESSION['lang']['status']."</td>
	<td align=center>Action</td>
	</tr>
	</thead>
	<tbody>";
	$where='';
	if($unitsch!=''){
		$where.=" and pemilik='".$unitsch."'";
	}
	if($banksch!=''){
		$where.=" and namabank='".$banksch."'";
	}
	if($reksch!=''){
		$where.=" and rekening like '%".$reksch."%'";
	}
	
	$str = "select * from ".$dbname.".keu_5akunbank where 1=1 ".$where." order by status desc, pemilik asc, namabank asc";
	$res=fetchdata($str);
	foreach($res as $bar){
		$no += 1;
		echo "<tr class=rowcontent>";
		echo "<td align=center>".$no."</td>";
		echo "<td align=left>".$bar['pemilik']." - ".$namaorganisasi[$bar['pemilik']]."</td>";
		// echo "<td align=left>".$bar['noakun']."</td>";
		echo "<td align=left>".$namaakun[$bar['noakuncoa']]."</td>";
		echo "<td align=left>".$nmbank[$bar['namabank']]."</td>";
		echo "<td align=left>".$bar['cabang']."</td>";
		echo "<td align=left>".$bar['rekening']."</td>";
		echo "<td align=left>".$bar['atasnama']."</td>";
		echo "<td align=left>".$bar['matauang']."</td>";
		// echo "<td align=left>".$bar['swift_code']."</td>";
		// echo "<td align=left>".$bar['fungsi']."</td>";
		// echo "<td align=left>".$bar['email']."</td>";
		echo "<td align=left>".$bar['inisialurut']."</td>";
		echo "<td align=left>".$nmstatus[$bar['status']]."</td>";
		echo "<td align=center>";
		if($bar['status']==1){
			echo"<img src=images/application/application_edit.png class=resicon  title='Edit'
			onclick=\"fillField('".$bar['pemilik']."','".$bar['noakun']."','".$bar['namabank']."','".$bar['cabang']."','".$bar['rekening']."','".$bar['atasnama']."','".$bar['matauang']."','".$bar['swift_code']."','".$bar['fungsi']."','".$bar['email']."','".$bar['inisialurut']."','".$bar['noakuncoa']."')\">";
			echo"&nbsp;<img src=images/reject.png class=resicon  title='Deactivate'
			onclick=\"nonaktif('".$bar['pemilik']."','".$bar['noakun']."','0')\">";
		}else{
			echo"<img src=images/approve.png class=resicon  title='Activated'
			onclick=\"nonaktif('".$bar['pemilik']."','".$bar['noakun']."','1')\">";
		}
			
		echo"</td>";
		echo "</tr>";
	}
	break;

default:
}
?>