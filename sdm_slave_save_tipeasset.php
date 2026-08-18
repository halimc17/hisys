<?
require_once('master_validation.php');
require_once('config/connection.php');

$kodetipe=$_POST['kodetipe'];
$kodetipelama=$_POST['kodetipelama'];
$namatipe=$_POST['namatipe'];
$namatipe1=$_POST['namatipe1'];
$noakun=$_POST['noakun'];
$noakunak=$_POST['noakunak'];
$tppenyusutan=$_POST['tppenyusutan'];
$method=$_POST['method'];

switch($method)
{
case 'update':	
        $str="update ".$dbname.".sdm_5tipeasset set namatipe='".$namatipe."',namatipe1='".$namatipe1."'
               ,noakun='".$noakun."',akunak='".$noakunak."',metodepenyusutan='".$tppenyusutan."',kodetipelama='".$kodetipelama."'
               where kodetipe='".$kodetipe."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
case 'insert':
        $str="insert into ".$dbname.".sdm_5tipeasset (kodetipe,kodetipelama,namatipe,namatipe1,noakun,akunak,metodepenyusutan)
              values('".$kodetipe."','".$kodetipelama."','".$namatipe."','".$namatipe1."','".$noakun."','".$noakunak."','".$tppenyusutan."')";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
        break;
case 'delete':
        $str="delete from ".$dbname.".sdm_5tipeasset 
        where kodetipe='".$kodetipe."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
default:
   break;					
}
$stru="select noakun,namaakun from ".$dbname.".keu_5akun";
$res=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namaakun[$bar->noakun]=$bar->namaakun;
}    // <td>".$bar1->noakun." - ".@$namaakun[$bar1->noakun]."</td>
$str1="select * from ".$dbname.".sdm_5tipeasset
                   order by namatipe";
				   
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res->fetch())
{
                echo"<tr class=rowcontent>
						 <td align=center>".$bar1->kodetipe."</td>
             <td align=center>".$bar1->kodetipelama."</td>
                         <td>".$bar1->namatipe."</td>
						 <td>".$bar1->namatipe1."</td>
						 <td>".$bar1->akunak." - ".@$namaakun[$bar1->akunak]."</td>
                         <td>".ucfirst($bar1->metodepenyusutan)."</td>
                         <td  align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodetipe."','".$bar1->namatipe."','".$bar1->namatipe1."','".$bar1->noakun."','".$bar1->akunak."','".$bar1->metodepenyusutan."','".$bar1->kodetipelama."');\"></td></tr>";
}	 
?>
