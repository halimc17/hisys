<?php
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('master_validation.php');
require_once('config/connection.php');

$kode = checkPostGet('kode','');
$nama = checkPostGet('nama','');
$nama1 = checkPostGet('nama1','');
$noakun = checkPostGet('noakun','');
$noakungit = checkPostGet('noakungit','');
$status=checkPostGet('status','');
$method = checkPostGet('method','');
$kelbiaya = checkPostGet('kelbiaya','');
$jnsapp = "KL";

date_default_timezone_set("Asia/Bangkok");

switch ($method) {
  case 'delete':
    $strx = "delete from " . $dbname . ".log_5klbarang where kode='" . $kode . "'";
	try{
	  $owlPDO->exec($strx);
	  $strx="delete from ".$dbname.".approval where notransaksi='".$kode."'";
		try{
			$owlPDO->exec($strx);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	}catch (PDOException $e){
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
    break;
  case 'update':
    $strx = "update " . $dbname . ".log_5klbarang set kelompok='" . $nama . "',kelompok1='" . $nama1 . "',
             noakun='" . $noakun . "',noakungit='" . $noakungit . "',kelompokbiaya='" . $kelbiaya . "',status='".$status."' where kode='" . $kode . "'";
	try{
		$owlPDO->exec($strx);
		
		$strx="delete from ".$dbname.".approval where notransaksi='".$kode."'";
		try{
			$owlPDO->exec($strx);
			
			$listpersetujuan=$_POST['persetujuan'];
			foreach($listpersetujuan as $key=>$val)
			{
				// $strx="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','".$status."')";
				$strx="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
				try
				{
					$owlPDO->exec($strx);
				}
				catch (PDOException $e) 
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
				}
			}
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	}catch (PDOException $e){
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
    break;
  case 'insert':
	$strx = "insert into " . $dbname . ".log_5klbarang(kode,kelompok,kelompok1,noakun,noakungit,kelompokbiaya,status)
               values('" . $kode . "','" . $nama . "','" . $nama1 . "','". $noakun . "','". $noakungit . "','" . $kelbiaya . "','".$status."')";
	try{
	  $owlPDO->exec($strx);
		$listpersetujuan=$_POST['persetujuan'];
		foreach($listpersetujuan as $key=>$val)
		{
			$strx="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
			try
			{
				$owlPDO->exec($strx);
			}
			catch (PDOException $e) 
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
	}catch (PDOException $e){
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
    break;
  default:
    break;
}

$str = "select * from " . $dbname . ".log_5klbarang order by kode asc";
$no = 0;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);
while ($bar = $res->fetch()) {
    $no+=1;
    echo"<tr class=rowcontent>
           <td>" . $no . "</td>
           <td>" . $bar->kode . "</td>
          <td>(ID) ".$bar->kelompok."<br>
			(EN) ".$bar->kelompok1."</td>
           <td>".$bar->noakun." - ".getNamaAkun($bar->noakun)."</td>
           <!--<td>".$bar->noakungit." - ".getNamaAkun($bar->noakungit)."</td>-->
		   <td align=center>".($bar->status=='0' ? 'Non-Aktif' : ($bar->status=='3' ? 'Ditolak' : 'Aktif'))."</td>";
		   
			## APPROVAL ##
			@$countApp = getCountApproval($jnsapp);
			for($i=1;$i<=$countApp;$i++)
			{
				@$arrdetail = detailApprove($i,$bar->kode,$jnsapp);
				
				echo"<td align=center>".$arrdetail['nama']."<br>(".($arrdetail['status']=='0'?'Menunggu Keputusan':($arrdetail['status']=='3'?'Ditolak':'Disetujui')).")</td>";
			}
		
		echo"<td align=center>";		
			if($bar->status=='1'){
				echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kode."','".$bar->kelompok."','".$bar->kelompok1."','".$bar->kelompokbiaya."','".$bar->noakun."','".$bar->noakungit."');\">&nbsp;";
			}
			else if($bar->status=='3'){
				echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delKelompok('".$bar->kode."','".$bar->kelompok."');\">";
			}
			else{}
		   
		echo"</td>

          </tr>";
}
?>
