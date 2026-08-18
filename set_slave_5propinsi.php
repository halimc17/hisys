<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
	
$method = checkPostGet('method', '');
$id=checkPostGet('id','');
$provinsi=checkPostGet('provinsi','');
 //exit('error'.$method);

switch($method){	
	
case 'insert':
		
		$str1 = "select * from " . $dbname . ".provinsi where provinsi='".$provinsi."'";
			//exit("Error : ".$str1);
		$res1=fetchData($str1);
		$jlh=count($res1);

		if($jlh==0){	
	        $str = "insert into " . $dbname . ".provinsi (provinsi)
		      values('" . $provinsi . "')";
	        //exit('error'.$str);
	        try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		else
	   	{
	   		exit('Error : Data tidak boleh sama , provinsi '.$provinsi.' sudah ada');
	   	}
        break;

case 'update':
		$str1 = "select * from " . $dbname . ".provinsi where id!='".$id."' and provinsi='".$provinsi."'";
			//exit("Error : ".$str1);
		$res1=fetchData($str1);
		$jlh=count($res1);

		if($jlh==0){
        $str = "update " . $dbname . ".provinsi set provinsi='" . $provinsi . "'
	       where id='" . $id . "'";

	        try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		else
	   	{
	   		exit('Error : Data tidak boleh sama , provinsi '.$provinsi.' sudah ada');
	   	}
        break;	
        default:
	break;	


}
echo "<div id=container>";
$str1 = "select * from " . $dbname . ".provinsi order by provinsi";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		 
		 <td align=center>" . $_SESSION['lang']['provinsi'] . "</td>
		<td style='width:10px;' align=center>aksi</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
		
		<td align=left>" . $bar1->provinsi . "</td>
			<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->id . "','" . $bar1->provinsi . "');\"></td>
		</tr>";
}

echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>
		 </div>
		 ";
		 
echo "</div>";
CLOSE_BOX();

?>