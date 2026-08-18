<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$karyawanid=$_POST['karid'];
$tahun=substr($_POST['tanggal'],6,4);
//exit('Error : '.$tahun);
$optGaji = makeOption($dbname,"sdm_ho_component","id,name");
$str="select * from ".$dbname.".sdm_5gajipokokho where karyawanid='".$karyawanid ."' and tahun='".$tahun."' order by idkomponen asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows==0)
{
	$str="select * from ".$dbname.".sdm_5gajipokok where karyawanid=".$karyawanid ." order by idkomponen asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
}
//echo $str;
$jab='';
while($bar=$res->fetch())
{
	$jab.="<tr>";
	$jab.="<td hidden id='oldid_".$bar->idkomponen."'>".$bar->idkomponen."</td><td hidden id='oldkomponen_".$bar->idkomponen."'>".$optGaji[$bar->idkomponen]."</td><td hidden> : </td>
		<td hidden><input id=oldkomponenjml_".$bar->idkomponen." type=text class=myinputtextnumber  value=0 size=15 maxlength=15 onkeypress=\"return angka_doang(event);\" onblur=change_number(this)> </td>";
	$jab.="</tr>";
}

echo $jab;
?>