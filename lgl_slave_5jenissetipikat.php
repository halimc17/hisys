<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodegolongan = checkPostGet('kodegolongan', '');
$namagolongan = checkPostGet('namagolongan', '');
$method = checkPostGet('method', '');

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".lgl_5jenissertipikat set nama='" . $namagolongan . "'
	       where kode='" . $kodegolongan . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".lgl_5jenissertipikat (kode,nama)
	      values('" . $kodegolongan . "','" . $namagolongan . "')";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".lgl_5jenissertipikat 
	where kode='" . $kodegolongan . "'";
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

$str1 = "select * from " . $dbname . ".lgl_5jenissertipikat order by kode";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
     <thead>
	 <tr class=rowheader>
		<td style='width:100px;'>" . $_SESSION['lang']['kode'] . "</td>
		<td>" . $_SESSION['lang']['nama'] . "</td>
		<td  style='width:30px;'>*</td></tr>
	 </thead>
	 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td align=center>" . $bar1->kode . "</td><td>" . $bar1->nama . "</td><td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->kode . "','" . $bar1->nama . "');\"></td></tr>";
}
echo"	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";
?>
