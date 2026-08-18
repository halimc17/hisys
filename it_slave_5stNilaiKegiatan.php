<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
//##kdkegiatan##ket##satuan##nilsngtbaik##nilbaik##nilckp##nilkrg##method
$kdkegiatan = checkPostGet('kdkegiatan','');
$ket = checkPostGet('ket','');
$satuan = checkPostGet('satuan','');
$nilsngtbaik = checkPostGet('nilsngtbaik','');
$nilbaik = checkPostGet('nilbaik','');
$nilckp = checkPostGet('nilckp','');
$nilkrg = checkPostGet('nilkrg','');
$method = checkPostGet('method','');

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".it_standard set keterangan='" . $ket . "',
satuan='" . $satuan . "',sangatbaik='" . $nilsngtbaik . "',baik='" . $nilbaik . "',cukup='" . $nilckp . "',kurang='" . $nilkrg . "'
where kodekegiatan='" . $kdkegiatan . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".it_standard (kodekegiatan,keterangan,satuan,sangatbaik,baik,cukup,kurang)
values('" . $kdkegiatan . "','" . $ket . "','" . $satuan . "','" . $nilsngtbaik . "','" . $nilbaik . "','" . $nilckp . "','" . $nilkrg . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".it_standard
where kodekegiatan='" . $kdkegiatan . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
    case'loadData':
        $str1 = "select * from " . $dbname . ".it_standard order by kodekegiatan asc";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
        echo"<table class=sortable cellspacing=1 border=0 style='width:800px;'>
			<thead>
				<tr class=rowheader>
				<td style='width:150px;'>" . $_SESSION['lang']['kodekegiatan'] . "</td>
				<td>" . $_SESSION['lang']['keterangan'] . "</td>
				<td>" . $_SESSION['lang']['satuan'] . "</td>
				<td>Sangat Baik</td>
				<td>Baik</td>
				<td>Cukup</td>
				<td>Kurang</td>
				<td style='width:70px;'>*</td></tr>
			</thead>
			<tbody>";
		while ($bar1 = $res1->fetch()) {
			echo"<tr class=rowcontent>
				<td align=center>" . $bar1->kodekegiatan . "</td>
				<td>" . $bar1->keterangan . "</td><td>" . $bar1->satuan . "</td>
				<td>" . $bar1->sangatbaik . "</td>
				<td>" . $bar1->baik . "</td>
				<td>" . $bar1->cukup . "</td>
				<td>" . $bar1->kurang . "</td>
				<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar1->kodekegiatan . "','" . $bar1->keterangan . "','" . $bar1->satuan . "','" . $bar1->sangatbaik . "','" . $bar1->baik . "','" . $bar1->cukup . "','" . $bar1->kurang . "');\"> 
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPendidikan('" . $bar1->kodekegiatan . "');\"></td></tr>";
		}
		echo"</tbody>
			<tfoot></tfoot>
		</table>";
        break;
    default:
        break;
}
?>
