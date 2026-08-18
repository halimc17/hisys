<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$kode=$_POST['kode'];
$nama=$_POST['nama'];
$kodepenerimaan=$_POST['kodepenerimaan'];
$method=$_POST['method'];
//$arrEnum=getEnum($dbname,'bgt_tipe','tipe,nama');
switch($method)
{
case 'update':	
	$str="update ".$dbname.".bgt_regional set nama='".$nama."', kodepenerimaankaryawan='".$kodepenerimaan."' 
	       where regional='".$kode."'";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
case 'insert':
    $str="select * from ".$dbname.".bgt_regional  where regional='".$kode."'  limit 0,1";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $sudahada="1";
        $pesan=$bar->regional." - ".$bar->nama;
    }
    if($sudahada=="1"){
        echo " Gagal, data sudah ada: ".$pesan; exit;
    }

    $str="insert into ".$dbname.".bgt_regional (regional,nama,kodepenerimaankaryawan)
	      values('".$kode."','".$nama."','".$kodepenerimaan."')";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
	break;
case 'delete':
	$str="delete from ".$dbname.".bgt_regional  where regional='".$kode."'";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
default:
   break;					
}
$str1="select * from ".$dbname.".bgt_regional order by regional";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res1->fetch())
{
            echo"<tr class=rowcontent><td align=center>".$bar1->regional."</td><td>".$bar1->nama."</td><td>".$bar1->kodepenerimaankaryawan."</td><td><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->regional."','".$bar1->nama."','".$bar1->kodepenerimaankaryawan."');\"></td></tr>";
}	 
?>
