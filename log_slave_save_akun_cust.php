<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');


$kode = checkPostGet('kode', '');
$kelompok = checkPostGet('kelompok', '');
$noakun = checkPostGet('noakun', '');
$method = checkPostGet('method', '');

switch ($method) {
    case 'delete':
        $strx = "delete from " . $dbname . ". pmn_4klcustomer where kode='" . $kode . "'";

        break;
    case 'update':
        $strx = "update " . $dbname . ". pmn_4klcustomer set kelompok='" . $kelompok . "',noakun='" . $noakun . "',updateby='" . $_SESSION['standard']['userid'] . "' where kode='" . $kode . "'";
        break;
    case 'insert':

        $strx = "insert into " . $dbname . ".pmn_4klcustomer(
					   kode,kelompok,noakun,createby,createtime)
				values('" . $kode . "','" . $kelompok . "','". $noakun . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
        //echo $strx; exit();
        break;
    default:
        break;
}


try {
    $owlPDO->exec($strx);
} catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n";
    die();
}
$no = 0;
$str = "select * from " . $dbname . ".pmn_4klcustomer order by kode desc";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $noakun = $bar->noakun;
    $spr = "select * from  " . $dbname . ".keu_5akun where `noakun`='" . $noakun . "'";
    $rep = $owlPDO->query($spr) or die(print " Gagal: " . PDOException::getMessage());
    $rep->setFetchMode(PDO::FETCH_OBJ);
    $bas = $rep->fetch();
    $no+=1;
    echo"<tr class=rowcontent>
		      <td align=center>" . $no . "</td>
		      <td align=center>" . $bar->kode . "</td>
			  <td>" . $bar->kelompok . "</td>
			  <td align=center>" . $bar->noakun . "</td>
			  <td>" . $bas->namaakun . "</td>
              <td>" . getNamaKaryawan($bar->updateby) . "</td>
			  <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->kode . "','" . $bar->kelompok . "','" . $bar->noakun . "','" . $bas->namaakun . "');\"></td>
			  <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delKlmpkplgn('" . $bar->kode . "','" . $bar->kelompok . "','" . $bar->noakun . "');\"></td>
			 </tr>";
}
?>