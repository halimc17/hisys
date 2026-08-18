<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$namaobat=checkPostGet('namaobat','');
$notransaksi=checkPostGet('notransaksi','');

if(isset($_POST['del']))
{
        $str="delete from ".$dbname.".sdm_pengobatandt where id=".$_POST['id'];
}	
else
{
        $str="insert  into ".$dbname.".sdm_pengobatandt(notransaksi,namaobat,jenis)
              values('".$_POST['notransaksi']."','".$_POST['namaobat']."','".$_POST['jenisobat']."')"; 
}
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

//====================
    $str="select * from ".$dbname.".sdm_pengobatandt  where notransaksi='".$_POST['notransaksi']."'"; 
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar=$res->fetch())
        {
                $no+=1;
                echo"<tr class=rowcontent>
                    <td align=center>".$no."</td>
                        <td>".$bar->notransaksi."</td>
                        <td>".$bar->namaobat."</td>
						 <td>".$bar->jenis."</td> 
                        <td align=center>
                          <img src=images/close.png class=resicon onclick=deleteObat('".$bar->id."','".$bar->notransaksi."')>
                        </td>
                   </tr>";
        }
?>