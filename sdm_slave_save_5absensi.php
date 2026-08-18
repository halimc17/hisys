<?php

require_once('master_validation.php');
require_once('config/connection.php');

$kode = $_POST['kode'];
$keterangan = $_POST['keterangan'];
$jumlahhk = $_POST['jumlahhk'];
$group = $_POST['grup'];
$status = $_POST['status'];
$potongan = $_POST['potongan'];
$pengali = $_POST['pengali'];
$validasiDokumen = $_POST['validasiDokumen'];
$method = $_POST['method'];
$arrayStat = array('1'=> 'Aktif', '0'=> 'Tidak Aktif');
$arraypot = array('1'=> 'Ada Potongan', '0'=> 'Tidak Ada Potongan');
$arrayValDok = array('1'=> 'Aktif', '0'=> 'Tidak Aktif');

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".sdm_5absensi set keterangan='" . $keterangan . "',
	       kelompok=" . $group . ",nilaihk='" . $jumlahhk . "',status=" . $status . ",potongan=" . $potongan . ",pengali='" . $pengali . "' ,validasidokumen='" . $validasiDokumen . "'  
	       where kodeabsen='" . $kode . "'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".sdm_5absensi 
	      (kodeabsen,keterangan,kelompok,nilaihk,status,pengali,potongan,validasidokumen)
	      values('" . $kode . "','" . $keterangan . "'," . $group . "," . $jumlahhk . "," . $status . "," . $pengali . "," . $potongan . "," . $validasiDokumen . ")";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".sdm_5absensi
	where kodeabsen='" . $kode . "'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    default:
        break;
}

$str1 = "select *,
	     case kelompok when 1 then '" . $_SESSION['lang']['dibayar'] . "'
		 when 0 then '" . $_SESSION['lang']['tidakdibayar'] . "'
		 end as ketgroup 
	     from " . $dbname . ".sdm_5absensi order by kodeabsen";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch()) {
	echo"<tr class=rowcontent>
			   <td align=center>" . $bar1->kodeabsen . "</td>
			   <td>" . $bar1->keterangan . "</td>
			   <td>" . $bar1->ketgroup . "</td>
			   <td>" . $bar1->nilaihk . "</td>
			   <td>" . $bar1->pengali . "</td>
			   <td>" . $arrayStat[$bar1->status] . "</td>
			   <td>" . $arraypot[$bar1->potongan] . "</td>
			   <td>" . $arrayValDok[$bar1->validasidokumen] . "</td>
			   <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->kodeabsen . "','" . $bar1->keterangan . "','" . $bar1->kelompok . "','" . $bar1->nilaihk . "','" . $bar1->pengali . "','" . $bar1->status . "','" . $bar1->potongan . "','" . $bar1->validasidokumen . "');\"></td></tr>";
}
?>
