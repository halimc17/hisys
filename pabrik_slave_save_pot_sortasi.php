<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$str="delete from ".$dbname.".pabrik_5pot_fraksi where kodefraksi='".$_POST['kode']."'";
try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
$str="insert into ".$dbname.".pabrik_5pot_fraksi (kodefraksi,potongan,createby,createtime)
      values('".$_POST['kode']."',".$_POST['potongan'].",'" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."');";


try{
    $owlPDO->exec($str); 
    $str1="select a.*,b.keterangan, b.keterangan1 from ".$dbname.".pabrik_5pot_fraksi a LEFT JOIN
		".$dbname.".pabrik_5fraksi2 b ON a.kodefraksi = b.kode
		order by a.kodefraksi";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch()){
            $updateby   = $bar1->createby;
            if($bar1->updateby == '0000000000'){
                $updateby = $bar1->createby;
            }
            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$updateby."'");
            echo"<tr class=rowcontent><td align=center>".$bar1->kodefraksi."</td>
                    <td>".$bar1->keterangan."</td>
                    <td>".$bar1->keterangan1."</td>
                    <td align=right>".$bar1->potongan."</td>
                    <td align=center>".$nmKar[$updateby]."</td>
                    <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodefraksi."','".$bar1->potongan."');\"></td></tr>";
	}
    
}
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}

?>