<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
	
$method = checkPostGet('method', '');
$id=checkPostGet('id','');
$id_prov=checkPostGet('provinsi','');
$provinsi=checkPostGet('provinsi','');
$kabupaten=checkPostGet('kabupaten','');
//echo $id_prov;
	
switch($method){	
	case 'update':
		$str1 = "select * from " . $dbname . ".kabupaten where id!='".$id."' and id_prov='".$id_prov."' and kabupaten='".$kabupaten."'";
			//exit("Error : ".$str1);
		$res1=fetchData($str1);
		$jlh=count($res1);

		if($jlh==0){
	        $str = "update " . $dbname . ".kabupaten set kabupaten='" . $kabupaten . "'
		       where id='" . $id . "' and id_prov='".$id_prov."'";
		       //exit($str);
	        try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		else
	   	{
	   		exit('Error : Data tidak boleh sama , kabupaten '.$kabupaten.' sudah ada');
	   	}
        break;	

    case 'insert':
		$str1 = "select * from " . $dbname . ".kabupaten where id_prov='".$id_prov."' and kabupaten='".$kabupaten."'";
			//exit("Error : ".$str1);
		$res1=fetchData($str1);
		$jlh=count($res1);

		if($jlh==0){	
	        $str = "insert into " . $dbname . ".kabupaten (kabupaten,id_prov)
		      values('" . $kabupaten . "','" . $id_prov . "')";
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
	   		exit('Error : Data tidak boleh sama , kabupaten '.$kabupaten.' sudah ada');
	   	}
        break;

	default:
	break;	
}
echo "<div id=container>";
$str1 = "select provinsi,a.id,a.id_prov,kabupaten from " . $dbname . ".kabupaten a left join " . $dbname . ".provinsi b on a.id_prov=b.id";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		 <td style='width:100px;' align=center>" . $_SESSION['lang']['provinsi'] ."</td>
		 <td style='width:100px;' align=center>" . $_SESSION['lang']['kabupaten'] . "</td>
		<td style='width:30px;'>aksi</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
		<td align=left>" . $bar1->provinsi . "</td>
		<td align=left>" . $bar1->kabupaten . "</td>
			<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->id . "','" . $bar1->id_prov . "','" . $bar1->kabupaten . "','" . $bar1->id_prov . "');\"></td>
		</tr>";
}

echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
close_box();
?>