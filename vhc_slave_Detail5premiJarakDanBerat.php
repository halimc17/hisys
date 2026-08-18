<?php
require_once('master_validation.php');
require_once('config/connection.php');
//include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_POST['pros'];
$keyCodeDtail=$_POST['keyCodeDtail'];
$userOnline=$_SESSION['standard']['userid'];
$posisi=$_POST['posi'];
$prmLbhBasis=$_POST['prmLbhBasis'];
$pinalty=$_POST['pinalty'];
$nomor=$_POST['nmrDetail'];
switch($proses)
{
	case'loadDataDetail':
	$limit=10;
	$page=0;
	if(isset($_POST['page']))
	{
	$page=$_POST['page'];
	if($page<0)
	$page=0;
	}
	$offset=$page*$limit;
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5ratetransport2  where keycode='".$keyCodeDtail."' and nomor='".$nomor."'  order by keycode desc";// echo $ql2;
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}
	$arrPos=array("Sopir","Kondektur");
	$sql="select * from ".$dbname.".kebun_5ratetransport2 where keycode='".$keyCodeDtail."' and nomor='".$nomor."' order by keycode desc limit ".$offset.",".$limit."";
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
	while($res=$query->fetch()){
		$no+=1;
	echo"
	<tr class=\"rowheader\">
		<td>".$no."</td>
		<td>".$res['keycode']."</td>
		<td>".$arrPos[$res['jobposition']]."</td>
		<td>".$res['premilebihbasis']."</td>
		<td>".$res['proporsipenalty']."</td>
		<td><img src=images/application/application_edit.png class=resicon  title='Edit' 
		onclick=\"fillFieldDetail('". $res['keycode']."','". $res['jobposition']."','". $res['premilebihbasis']."','".$res['proporsipenalty']."');\">
		<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDetail('". $res['keycode']."','". $res['jobposition']."');\" >	
		</td>";
		echo"</tr>";
	}
	echo" 
	</tr><tr class=rowheader><td colspan=11 align=center>
	".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	<br />
	<button class=mybutton onclick=cariDet(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	<button class=mybutton onclick=cariDet(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	</td></tr>";
	break;
	
	case'insertDetail':
	if(($keyCodeDtail=='')||($posisi=='')||($prmLbhBasis=='')||($pinalty==''))
	{
		echo"warning:Please Complete Insert The Form";
		exit();
	}
	$thisDay=date("Y-m-d");
	$sIns="insert into ".$dbname.".kebun_5ratetransport2 (keycode, jobposition, nomor, premilebihbasis, proporsipenalty, createdby,createddate ) values 
	('".$keyCodeDtail."','".$posisi."','".$nomor."','".$prmLbhBasis."','".$pinalty."','".$userOnline."','".$thisDay."')";
	//echo"warning:".$sIns;
	try{
		$owlPDO->exec($sIns); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage()."___".$sIns;
		die();
	}
	break;
	case'updateDetail':
	if(($keyCodeDtail=='')||($posisi=='')||($prmLbhBasis=='')||($pinalty==''))
	{
		echo"warning:Please Complete Insert The Form";
		exit();
	}
	$sUpdate="update ".$dbname.".kebun_5ratetransport2 set premilebihbasis='".$prmLbhBasis."', proporsipenalty='".$pinalty."',modifyby='".$userOnline."' where keycode='".$keyCodeDtail."' and jobposition='".$posisi."' and nomor='".$nomor."'";
	try{
		$owlPDO->exec($sUpdate); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage()."___".$sUpdate;
		die();
	}
	break;
	case'delDetail':
	$sDel="delete from ".$dbname.".kebun_5ratetransport2 where keycode='".$keyCodeDtail."' and jobposition='".$posisi."'";
	try{
		$owlPDO->exec($sDel); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage()."___".$sDel;
		die();
	}
	break;
	default:
	break;
}
?>