<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kodejabatan=$_POST['kode'];
$namajabatan=$_POST['nama'];
$namajabatan1=$_POST['nama1'];
$satuan=$_POST['satuan'];
$method=$_POST['method'];

switch($method)
{
case 'update':	
	$str="update ".$dbname.".pabrik_5fraksi set keterangan='".$namajabatan."',keterangan1='".$namajabatan1."',
	      type='".$satuan."' where kode='".$kodejabatan."' and keterangan='".$namajabatan."'";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
        break;
case 'insert':
	$str="insert into ".$dbname.".pabrik_5fraksi (kode,keterangan,keterangan1,type)
	      values('".$kodejabatan."','".$namajabatan."','".$namajabatan1."','".$satuan."')";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
	break;
case 'delete':
	$str="delete from ".$dbname.".pabrik_5fraksi
	where kode='".$kodejabatan."'";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
	break;
default:
   break;					
}
$str1="select * from ".$dbname.".pabrik_5fraksi order by kode";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res1->fetch())
{
    echo"<tr class=rowcontent>
            <td align=center>".$bar1->kode."</td>
            <td>".$bar1->keterangan."</td>
            <td>".$bar1->keterangan1."</td>
            <td align=center>".$bar1->type."</td>
            <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->keterangan."','".$bar1->type."','".$bar1->keterangan1."');\"></td>
        </tr>";
}	 

?>
