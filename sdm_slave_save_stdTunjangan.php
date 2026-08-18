<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodejabatan = checkPostGet('kodejabatan','');
$lokasi = checkPostGet('lokasi','');
$tjjabatan = checkPostGet('tjjabatan','');
$tjkota = checkPostGet('tjkota','');
$tjtransport = checkPostGet('tjtransport','');
$tjmakan = checkPostGet('tjmakan','');
$tjsdaerah = checkPostGet('tjsdaerah','');
$tjmahal = checkPostGet('tjmahal','');
$tjpembantu = checkPostGet('tjpembantu','');

//del first
$str="delete from ".$dbname.".sdm_5stdtunjangan where jabatan=".$kodejabatan." and penempatan='".$lokasi."'";
try{
	$owlPDO->exec($str); 
}
catch (PDOException $e){
	
}
//insert
$str="insert into ".$dbname.".sdm_5stdtunjangan (
      jabatan, penempatan, tjjabatan, tjkota, 
      tjtransport, tjmakan, tjsdaerah, tjmahal, 
      tjpembantu)
      values(
      ".$kodejabatan.",
      '".$lokasi."',
      ".$tjjabatan.",
      ".$tjkota.",
      ".$tjtransport.",
      ".$tjmakan.",
      ".$tjsdaerah.",
      ".$tjmahal.",
      ".$tjpembantu.");";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo "Error ".addslashes($e->getMessage()); 
		exit();
	}
  
// default, display content
$str="select a.*,b.namajabatan from ".$dbname.".sdm_5stdtunjangan a left join ".$dbname.".sdm_5jabatan b on a.jabatan=b.kodejabatan order by penempatan,jabatan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar=$res->fetch())
{
    $no+=1;
    echo "<tr class=rowcontent>
          <td>".$no."</td>
          <td>".$bar->namajabatan."</td>
          <td>".$bar->penempatan."</td>
          <td>".$bar->tjjabatan."</td>
          <td>".$bar->tjkota."</td>
          <td>".$bar->tjtransport."</td>
          <td>".$bar->tjmakan."</td>
          <td>".$bar->tjsdaerah."</td>
          <td>".$bar->tjmahal."</td>
          <td>".$bar->tjpembantu."</td>
          <td><img class='resicon' onclick=\"fillField('".$bar->jabatan."','".$bar->penempatan."','".$bar->tjjabatan."','".$bar->tjkota."','".$bar->tjtransport."','".$bar->tjmakan."','".$bar->tjsdaerah."','".$bar->tjmahal."','".$bar->tjpembantu."');\" title='Edit' src='images/application/application_edit.png'></td>
          </tr>";
}   
  
?>