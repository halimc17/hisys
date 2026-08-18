\<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodegolongan = checkPostGet('kodegolongan', '');
$namagolongan = checkPostGet('namagolongan', '');
$meliputi = checkPostGet('meliputi', '');
$method = checkPostGet('method', '');

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".pad_5typesurvey set namasurvey='" . $namagolongan . "' ,meliputi='" . $meliputi . "'
	       where kodesurvey='" . $kodegolongan . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".pad_5typesurvey (kodesurvey,namasurvey,meliputi)
	      values('" . $kodegolongan . "','" . $namagolongan . "','" . $meliputi . "')";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".pad_5typesurvey 
	where kodesurvey='" . $kodegolongan . "'";
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

$str1 = "select * from " . $dbname . ".pad_5typesurvey order by kodesurvey";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
     <thead>
	 <tr class=rowheader><td style='width:150px;'>" . $_SESSION['lang']['surveycode'] . "</td><td>" . $_SESSION['lang']['surveytype'] . "</td><td>Meliputi</td><td  style='width:30px;'>*</td></tr>
	 </thead>
	 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td align=center>" . $bar1->kodesurvey . "</td><td>" . $bar1->namasurvey . "</td><td>" . $bar1->meliputi . "</td><td><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->kodesurvey . "','" . $bar1->namasurvey . "','" . $bar1->meliputi . "');\"></td></tr>";
}
echo"	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";
?>
