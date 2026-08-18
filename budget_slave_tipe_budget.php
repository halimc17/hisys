<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$kode=$_POST['kode'];
$nama=$_POST['nama'];
$method=$_POST['method'];
$arrEnum=getEnum($dbname,'bgt_tipe','tipe,nama');
switch($method)
{
case 'update':	
	$str="update ".$dbname.".bgt_tipe set nama='".$nama."' where tipe='".$kode."'";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
case 'insert':
                $str="select * from ".$dbname.".bgt_tipe  where tipe='".$kode."'  limit 0,1";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch())
                {
                    $sudahada="1";
                    $pesan=$bar->tipe." - ".$bar->nama;
                }
                if($sudahada=="1"){
                    echo " Gagal, data sudah ada: ".$pesan; exit;
                }

                $str="insert into ".$dbname.".bgt_tipe (tipe,nama)
                      values('".$kode."','".$nama."')";
                try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
                break;
case 'delete':
        $str="delete from ".$dbname.".bgt_tipe where tipe='".$kode."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
    default:
       break;					
}
$str1="select * from ".$dbname.".bgt_tipe order by tipe";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res1->fetch())
{
            echo"<tr class=rowcontent><td align=center>".$bar1->tipe."</td><td>".$bar1->nama."</td><td><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->tipe."','".$bar1->nama."');\"></td></tr>";
}	 

?>
