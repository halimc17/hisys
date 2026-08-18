<?php
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
if($param['periode']==''){
	$param['periode']=date('Y-m');
}
$wh=" and a.scan_date like '".$param['periode']."%'";


$str = "select * from ".$dbname.".att_log a left join ".$dbname.".att_device b on a.sn=b.sn where 1=1 ".$wh."";
$res = fetchdata($str);
foreach($res as $bar){
	$tanggal[substr($bar['scan_date'],0,10)]=substr($bar['scan_date'],0,10);
	//$data[$bar['device_name']]=$bar['device_name'];
	//$snnama[$bar['device_name']]=$bar['sn'];
	
	@$scan[$bar['sn']][substr($bar['scan_date'],0,10)]+=1;
	if($bar['flag']=='1'){		
		@$upload[$bar['sn']][substr($bar['scan_date'],0,10)]+=1;
	}
}


$str = "select * from ".$dbname.".att_device";
$res = fetchdata($str);
foreach($res as $bar){
	$data[$bar['device_name']]=$bar['device_name'];
	$snnama[$bar['device_name']]=$bar['sn'];
	$optnm[$bar['sn']]=$bar['device_name'];
}

######################################
############## SERVER 1 HO ###########
######################################
$dbserverfp='10.1.1.63';
$dbportfp  ='3306';
$dbnamefp  ='fin_pro';
$unamefp   ='uploader';
$passwdfp  ='!0987654321';
$error     ="benar";

try{
	$owlPDOFP = new PDO('mysql:host='.$dbserverfp.';dbname='.$dbnamefp, $unamefp, $passwdfp, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error="salah";
	print " Gagal, ".$dbserverfp." could not connect\n";	
	print "Error!: " . $e->getMessage() . "<br/>";
	die();
}

$svrho=array();
$str = "select * from ".$dbnamefp.".att_log a where 1=1 ".$wh."";
$res = $owlPDOFP->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$svrho[$bar['sn']][substr($bar['scan_date'],0,10)]+=1;
}


######################################
########### SERVER 2 SDKM ############
######################################
$dbserverfp2='10.7.1.4';
$dbportfp2  ='3306';
$dbnamefp2  ='fin_pro';
$unamefp2   ='uploader';
$passwdfp2  ='!0987654321';
$error2     = false;

try{
	$owlPDOFP2 = new PDO('mysql:host='.$dbserverfp2.';dbname='.$dbnamefp2, $unamefp2, $passwdfp2, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP2->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error2=true;
	// print " Gagal, ".$dbserverfp2." could not connect\n";	
	// print "Error!: " . $e->getMessage() . "<br/>";
	// die();
}

$svrsdkm2=array();
$str = "select * from ".$dbnamefp2.".att_log a where 1=1 ".$wh."";
$res = $owlPDOFP2->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$svrsdkm2[$bar['sn']][substr($bar['scan_date'],0,10)]+=1;
}



######################################
########### SERVER 2 SDKM ############
######################################
$dbserverfp5='10.7.1.3';
$dbportfp5  ='3306';
$dbnamefp5  ='fin_pro';
$unamefp5   ='uploader';
$passwdfp5  ='!0987654321';
$error5     = false;

try{
	$owlPDOFP5 = new PDO('mysql:host='.$dbserverfp5.';dbname='.$dbnamefp5, $unamefp5, $passwdfp5, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP5->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error5=true;
	// print " Gagal, ".$dbserverfp5." could not connect\n";	
	// print "Error!: " . $e->getMessage() . "<br/>";
	// die();
}

$svrsdkm3=array();
$str = "select * from ".$dbnamefp5.".att_log a where 1=1 ".$wh."";
$res = $owlPDOFP5->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$svrsdkm3[$bar['sn']][substr($bar['scan_date'],0,10)]+=1;
}


######################################
########### SERVER 3 BPJM ############
######################################
$dbserverfp3='10.7.28.99';
$dbportfp3  ='3306';
$dbnamefp3  ='fin_pro';
$unamefp3   ='uploader';
$passwdfp3  ='!0987654321';
$error3     = false;
try{
	$owlPDOFP3 = new PDO('mysql:host='.$dbserverfp3.';dbname='.$dbnamefp3, $unamefp3, $passwdfp3, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP3->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error3 = true;
	// print " Gagal, ".$dbserverfp3." could not connect\n";	
	// print "Error!: " . $e->getMessage() . "<br/>";
	// die();
}

$svrsdkm4=array();
$str = "select * from ".$dbnamefp3.".att_log a where 1=1 ".$wh."";
$res = $owlPDOFP3->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$svrsdkm4[$bar['sn']][substr($bar['scan_date'],0,10)]+=1;
}


######################################
########### SERVER 4 KSPM ############
######################################
$dbserverfp4='10.7.12.99';
$dbportfp4  ='3306';
$dbnamefp4  ='fin_pro';
$unamefp4   ='uploader';
$passwdfp4  ='!0987654321';
$error4     = false;
try{
	$owlPDOFP4 = new PDO('mysql:host='.$dbserverfp4.';dbname='.$dbnamefp4, $unamefp4, $passwdfp4, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP4->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error4 = true;
	// print " Gagal, ".$dbserverfp3." could not connect\n";	
	// print "Error!: " . $e->getMessage() . "<br/>";
	// die();
}
$svrsdkm5=array();
$str = "select * from ".$dbnamefp4.".att_log a where 1=1 ".$wh."";
$res = $owlPDOFP4->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$svrsdkm5[$bar['sn']][substr($bar['scan_date'],0,10)]+=1;
}



sort($tanggal);
sort($data);
$tab.="Periode : ".$param['periode']."<br>";
$tab.="<table border=1 style=white-space:nowrap; >";
$tab.="<tr>";
$tab.="<td>No</td>";
$tab.="<td>Lokasi</td>";
$tab.="<td>SN</td>";
$tab.="<td>Server</td>";
foreach($tanggal as $tgl){	
	$tab.="<td align=center>".substr($tgl,-2)."</td>";
}
$tab.="<td>Total</td>";
$tab.="</tr>";


foreach($data as $msn){
	$mesin = $snnama[$msn]; #kode sn 
	$no++;
	$tab.="<tr style=vertical-align:top>";
	$tab.="<td>".$no."</td>";
	$tab.="<td>".$msn."</td>";
	$tab.="<td>".$mesin."</td>";
	$tab.="<td>OWL<br>HO(1.63)<br>SDKM(1.4)<br>SDKM(1.3)<br>BPJM(28.99)<br>KSPM(12.99)</td>";
	foreach($tanggal as $tgl){
		$tab.="<td align=center>".@$scan[$mesin][$tgl]."<br>".@$svrho[$mesin][$tgl]."<br>".@$svrsdkm2[$mesin][$tgl]."<br>".@$svrsdkm3[$mesin][$tgl]."<br>".@$svrsdkm4[$mesin][$tgl]."<br>".@$svrsdkm5[$mesin][$tgl]."</td>";
		@$total[$mesin]+=$scan[$mesin][$tgl];
		@$totalu[$mesin]+=$svrho[$mesin][$tgl];
		@$totalu2[$mesin]+=$svrsdkm2[$mesin][$tgl];
		@$totalu3[$mesin]+=$svrsdkm3[$mesin][$tgl];
		@$totalu4[$mesin]+=$svrsdkm4[$mesin][$tgl];
		@$totalu5[$mesin]+=$svrsdkm5[$mesin][$tgl];
		
		@$ttltgl[$tgl]+=$scan[$mesin][$tgl];
		@$ttltglu[$tgl]+=$svrho[$mesin][$tgl];
		@$ttltglu2[$tgl]+=$svrsdkm2[$mesin][$tgl];
		@$ttltglu3[$tgl]+=$svrsdkm3[$mesin][$tgl];
		@$ttltglu4[$tgl]+=$svrsdkm4[$mesin][$tgl];
		@$ttltglu5[$tgl]+=$svrsdkm5[$mesin][$tgl];
		
		@$ttl+=$scan[$mesin][$tgl];
		@$ttlu+=$svrho[$mesin][$tgl];
		@$ttlu2+=$svrsdkm2[$mesin][$tgl];
		@$ttlu3+=$svrsdkm3[$mesin][$tgl];
		@$ttlu4+=$svrsdkm4[$mesin][$tgl];
		@$ttlu5+=$svrsdkm5[$mesin][$tgl];
	}
	$tab.="<td align=center>".@$total[$mesin]."<br>".@$totalu[$mesin]."<br>".@$totalu2[$mesin]."<br>".@$totalu3[$mesin]."<br>".@$totalu4[$mesin]."<br>".@$totalu5[$mesin]."</td>";
	$tab.="</tr>";
}
$tab.="<tr>";
$tab.="<td colspan=3 align=center>TOTAL</td>";
$tab.="<td>OWL<br>FP HO<br>SDKM(1.4)<br>SDKM(1.3)<br>BPJM(28.99)<br>KSPM(12.99)</td>";
foreach($tanggal as $tgl){
	$tab.="<td align=center>".@$ttltgl[$tgl]."<br>".@$ttltglu[$tgl]."<br>".@$ttltglu2[$tgl]."<br>".@$ttltglu3[$tgl]."<br>".@$ttltglu4[$tgl]."<br>".@$ttltglu5[$tgl]."</td>";
}
$tab.="<td align=center>".@$ttl."<br>".@$ttlu."<br>".@$ttlu2."<br>".@$ttlu3."<br>".@$ttlu4."<br>".@$ttlu5."</td>";
$tab.="</tr>";
$tab.="</table>";

if($param['tipe']=='excel'){
	$stream=$tab;;
	@$stream.="Print Time : ".date('H:i:s, d/m/Y');
	$tglSkrg=date("Ymd");
	$nop_="daftar_finger";
	if(strlen($stream)>0){
		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempExcel/'.$file);
				}
			}
			closedir($handle);
		}
		$handle=fopen("tempExcel/".$nop_.".xls",'w');
		if(!fwrite($handle,$stream)){
			echo "<script language=javascript1.2>
			parent.window.alert('Can't convert to excel format');
			</script>";
			exit;
		}else{
			echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls';
			</script>";
		}
		fclose($handle);
	}
	
}else{
	echo $tab;
}
?>