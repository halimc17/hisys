<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodegolongan = checkPostGet('kodegolongan', '');
$namagolongan = checkPostGet('namagolongan', '');
$method = checkPostGet('method', '');

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".legal_5pihak set namapihak='" . $namagolongan . "'
	       where kodepihak='" . $kodegolongan . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".legal_5pihak (kodepihak,namapihak)
	      values('" . $kodegolongan . "','" . $namagolongan . "')";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".legal_5pihak 
	where kodepihak='" . $kodegolongan . "'";
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

$str1 = "select * from " . $dbname . ".legal_5pihak order by kodepihak";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
     <thead>
	 <tr class=rowheader>
		<td style='width:150px;'>" . $_SESSION['lang']['kodepihak'] . "</td>
		<td>" . $_SESSION['lang']['namapihak'] . "</td>
		<td  style='width:30px;'>*</td></tr>
	 </thead>
	 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td align=center>" . $bar1->kodepihak . "</td><td>" . $bar1->namapihak . "</td><td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->kodepihak . "','" . $bar1->namapihak . "');\"></td></tr>";
}
echo"	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";
?>
