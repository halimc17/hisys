<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kodegolongan = checkPostGet('kodegolongan', '');
$namagolongan = checkPostGet('namagolongan', '');
$kodekategori = checkPostGet('kodekategori', '');
$method = checkPostGet('method', '');

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".legal_5nama set namaijin='" . $namagolongan . "', kodekategori='" . $kodekategori . "'
	       where kodeijin='" . $kodegolongan . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".legal_5nama (kodeijin,namaijin,kodekategori)
	      values('" . $kodegolongan . "','" . $namagolongan . "','" . $kodekategori . "')";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".legal_5nama 
	where kodeijin='" . $kodegolongan . "'";
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

$optKategori = makeOption($dbname,"legal_5kategoriijin","kodekategori,namakategori");
$str1 = "select * from " . $dbname . ".legal_5nama order by kodeijin";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
     <thead>
	 <tr class=rowheader>
		<td style='width:100px;'>" . $_SESSION['lang']['kode'] . "</td>
		<td>" . $_SESSION['lang']['nama'] . "</td>
		<td>" . $_SESSION['lang']['kategori'] . "</td>
		<td  style='width:30px;'>*</td></tr>
	 </thead>
	 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td align=center>" . $bar1->kodeijin . "</td><td>" . $bar1->namaijin . "</td> <td>" . $optKategori[$bar1->kodekategori] . "</td><td  align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->kodeijin . "','" . $bar1->namaijin . "','" . $bar1->kodekategori . "');\"></td></tr>";
}
echo"	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";
?>
