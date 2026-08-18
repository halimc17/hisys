<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
//include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_POST['proses'];
$keyCode=$_POST['keycode'];
$nmr=$_POST['nomor'];
$jmlhBasis=$_POST['jmlhBasis'];
$jmlhTrip=$_POST['jmlhTrip'];
$detPremi=$_POST['detPremi'];
$tpeKerja=$_POST['tipeKerja'];
$userOnline=$_SESSION['standard']['userid'];
$posisi=$_POST['posisi'];
switch($proses)
{
	case'loadData':
	$arrPos=array("Sopir","Kondektur");
	$limit=10;
	$page=0;
	if(isset($_POST['page']))
	{
	$page=$_POST['page'];
	if($page<0)
	$page=0;
	}
	$offset=$page*$limit;
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5ratetransport where jarakdari=''  order by nomor desc";// echo $ql2;
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}
	
	$sql="select keycode,nomor,tipeangkutan,jumlahtrip,rate,jobposition from ".$dbname.".kebun_5ratetransport where jarakdari='' order by nomor desc limit ".$offset.",".$limit."";
	
	//echo"warning:test".$sql;
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
	while($res=$query->fetch()){

		$no+=1;
		echo"
		<tr class=rowcontent>
		<td>".$no."</td>
		<td align=center>".$res['keycode']."</td>
		<td align=center>".$res['nomor']."</td>
		<td align=center>".$res['tipeangkutan']."</td>
		<td align=center>".$res['jumlahtrip']."</td>
		<td align=center>".$res['rate']."</td>
		<td align=center>".$arrPos[$res['jobposition']]."</td>
		<td><img src=images/application/application_edit.png class=resicon  title='Edit' 
		onclick=\"fillField('". $res['keycode']."','". $res['nomor']."','". $res['tipeangkutan']."','". $res['jobposition']."','". $res['jumlahtrip']."','". $res['rate']."');\">
		<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delHead('". $res['keycode']."','". $res['nomor']."');\" >	
		</td>"	;
	}
	echo" </tr><tr class=rowheader><td colspan=11 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
				<br />
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>";
	break;
	case'insert_header':
	if(($keyCode=='')||($nmr=='')||($tpeKerja=='')||($jmlhTrip==''))
	{
		echo"warning:Please Complete The Form";
		exit();
	}
	$sCek="select keycode from ".$dbname.".kebun_5ratetransport where keycode='".$keyCode."' and tipeangkutan='".$tpeKerja."' and jobposition='".$posisi."'";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
	$rCek=owlBaris($qCek);
	if($rCek<1){
		$thisDay=date("Y-m-d");
		$sIns="insert into ".$dbname.".kebun_5ratetransport 
		(keycode, nomor, tipeangkutan,  jumlahtrip, rate, createdby,createddate,jobposition) values 
		('".$keyCode."','".$nmr."','".$tpeKerja."','".$jmlhTrip."','".$detPremi."','".$userOnline."','".$thisDay."','".$posisi."')";
		//echo "warning:".$sIns;
		try{
			$owlPDO->exec($sIns); 
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage()."___".$sIns;
			die();
		}
		
	}
	else
	{
		echo"warning:This Keycode and Number Already Input, Please Try Other Number";
		exit();
	}
	break;
	
	case'updateHeader':
	//echo"warning:masuk";
	if(($keyCode=='')||($nmr=='')||($tpeKerja=='')||($jmlhTrip==''))
	{
		
		echo"warning:Please Complete The Form";
		print"<pre>";
		print_r($_POST);
		print"</pre>";
		exit();
	}
	$sUpd="update ".$dbname.".kebun_5ratetransport set tipeangkutan='".$tpeKerja."', jumlahtrip='".$jmlhTrip."' 
	,rate='".$detPremi."',modifyby='".$userOnline."',jobposition='".$posisi."' where keycode='".$keyCode."' and nomor='".$nmr."'";
	//echo "warning:".$sUpd;
	try{
		$owlPDO->exec($sUpd); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage()."___".$sUpd;
		die();
	}
	break;
	
	case'delData':
	
	$sDel="delete from ".$dbname.".kebun_5ratetransport where keycode='".$keyCode."' and nomor='".$nmr."'";
	//echo"warning:".$sDel;
	try{
		$owlPDO->exec($sDel); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage()."___".$sDel;
		die();
	}
	break;
	case'cekNmr':
	$sCek="select nomor from ".$dbname.".kebun_5ratetransport where keycode='".$keyCode."' order by nomor desc limit 0,1"; //echo "warning".$sCek;
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
	$rCek=$qCek->fetch();
	$nilaiAwal=intval($rCek['nomor']);
	if($nilaiAwal==0)
	{
		$nilaiAwal+=1;
	}
	else
	{
		$nilaiAwal+=1;
	}
	echo $nilaiAwal;
	break;
	default:
	break;
}

?>