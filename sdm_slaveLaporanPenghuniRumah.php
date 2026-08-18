<?php
require_once('master_validation.php');
require_once('config/connection.php');

$kodeorg = $_POST['kodeorg'];
$str = " select a.* from " . $dbname . ".sdm_perumahanht a where kodeorg='" . $kodeorg . "'";
$no = 0;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $no+=1;
    $jlh = 0;
    $str1 = "select count(karyawanid) as jlh from " . $dbname . ".sdm_penghunirumah
	        where kodeorg='" . $kodeorg . "' and blok='" . $bar->blok . "'
			and norumah='" . $bar->norumah . "'";
    $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while ($bar1 = $res1->fetch()) {
        $jlh = $bar1->jlh;
    }

    echo"<tr class=rowcontent>
		 <td>" . $no . "</td>
		 <td>" . $kodeorg . "</td>
		 <td>" . $bar->kompleks . "</td>
		 <td>" . $bar->blok . "</td>
		 <td>" . $bar->norumah . "</td>
		 <td>" . $bar->tipe . "</td>
		 <td align=right>" . $jlh . "</td>
		 <td>
		 <img src=images/zoom.png class=resicon onclick=showTenant('" . $kodeorg . "','" . $bar->blok . "','" . $bar->norumah . "',event)>
		 </td>
		 </tr>";
}
?>
