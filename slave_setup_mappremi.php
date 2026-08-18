<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
//include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$method=$_POST['method'];
$kodeorg=$_POST['kdOrg'];
$tipePremi=$_POST['tipePremi'];
$keyCode=$_POST['kyCode'];
$oldData=$_POST['oldData'];
$oldTipePremi=$_POST['oldTipePremi'];

switch($method)
{
	case'loadNewData':
	$limit=10;
	$page=0;
	if(isset($_POST['page']))
	{
	$page=$_POST['page'];
	if($page<0)
	$page=0;
	}
	$offset=$page*$limit;
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".setup_mappremi  order by kodeorg,keycode desc";// echo $ql2;
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}
	
	$str="select * from ".$dbname.".setup_mappremi order by kodeorg,keycode desc limit ".$offset.",".$limit."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$sPt="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
		$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
		$qPt->setFetchMode(PDO::FETCH_ASSOC);
		$rOrg=$qPt->fetch();
		$no+=1;
		echo"<tr class=rowcontent id='tr_".$no."'>
		<td>".$no."</td>
		<td id='nmorg_".$no."'>".$rOrg['namaorganisasi']."</td>
		<td id='kpsits_".$no."'>".$bar->tipepremi."</td>
		<td id='kpsits_".$no."'>".$bar->keycode."</td>
		<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kodeorg."','".$bar->tipepremi."','".$bar->keycode."');\"><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delCode('".$bar->kodeorg."','".$bar->tipepremi."',,'".$bar->keycode."');\"></td>
		</tr>";
	}	 
	
	echo" 
	</tr><tr class=rowheader><td colspan=5 align=center>
	".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	<br />
	<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	</td></tr>"; 	   	
	
	break;
	case'insert':
	$sCek="select * from ".$dbname.".setup_mappremi where kodeorg='".$kodeorg."' and tipepremi='".$tipePremi."' and keycode='".$keyCode."'";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$rCek=owlBaris($qCek);
	if($rCek<1)
	{
		$sIns="insert into ".$dbname.".setup_mappremi (`kodeorg`,`tipepremi`,`keycode`) values ('".$kodeorg."','".$tipePremi."','".$keyCode."')";
		try{
			$owlPDO->exec($sIns);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}	
	}
	else
	{
		echo"warning:This Data Already Input";
		exit();
	}
	break;
	case'updateData':
	$sUp="update  ".$dbname.".setup_mappremi set keycode='".$keyCode."',tipepremi='".$tipePremi."' where kodeorg='".$kodeorg."' and tipepremi='".$oldTipePremi."' and keycode='".$oldData."' ";
	try{
		$owlPDO->exec($sUp);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
	break;
	case'deleteData':
	$sDel="delete from ".$dbname.".setup_mappremi where kodeorg='".$kodeorg."' and tipepremi='".$tipePremi."' and keycode='".$keyCode."'";
	try{
		$owlPDO->exec($sDel);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
	break;
	default:
	break;
}

?>