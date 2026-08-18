<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodeid = checkPostGet('kodeid', '');
$namajenis = checkPostGet('namajenis', '');
$status = checkPostGet('status', '');
$proses = checkPostGet('proses', '');

function checkstat($statt)
{
	$retstatt='';
	if($statt==1)
	{
		$retstatt = 'Aktif';
	}
	else
	{
		$retstatt = 'Tidak Aktif';
	}

	return $retstatt;

}

switch ($proses) {
    case 'update':
        $str = "update " . $dbname . ".legal_5masterdraftspk set status='" . $status . "', updateby='".$_SESSION['standard']['userid']."',updatetime='".date("Y-m-d H:i:s")."'
	       where id='" . $kodeid . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".legal_5masterdraftspk (namajenis,status,createdby,createdtime)
	      values('" . $namajenis . "','" . $status . "','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
	      //exit("error :".$str);
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".legal_5masterdraftspk 
	where id='" . $kodeid . "'";
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

$str1 = "select * from " . $dbname . ".legal_5masterdraftspk order by namajenis";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
     <thead>
	 <tr class=rowheader>
		<td style='width:150px;'>Nama Jenis</td>
		<td>" . $_SESSION['lang']['status'] . "</td>
		<td  style='width:30px;'>*</td></tr>
	 </thead>
	 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td align=center>" . $bar1->namajenis . "</td><td>" . checkstat($bar1->status) . "</td><td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->id . "','" . $bar1->namajenis . "','" . $bar1->status . "');\"></td></tr>";
}
echo"	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";
?>
