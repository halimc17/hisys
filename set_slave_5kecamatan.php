<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
	
$method = checkPostGet('method', '');
$id=checkPostGet('id','');
$id_kab=checkPostGet('kabupaten','');
$kecamatan=checkPostGet('kecamatan','');
$kabupaten=checkPostGet('kabupaten','');
//echo $provinsi;
	
switch($method){	
	case 'update':

		$str1 = "select * from " . $dbname . ".kecamatan where idkec!='".$id."' and id_kab='".$id_kab."' and kecamatan='".$kecamatan."'";
			//exit("Error : ".$str1);
		$res1=fetchData($str1);
		$jlh=count($res1);

		if($jlh==0){
	        $str = "update " . $dbname . ".kecamatan set kecamatan='" . $kecamatan . "'
		       where idkec='" . $id . "' and id_kab='".$id_kab."'";

		    
	        try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}

		}
	   else
	   	{
	   		exit('Error : Data tidak boleh sama , kecamatan '.$kecamatan.' sudah ada');
	   	}
        break;	

    case 'insert':
		$str1 = "select * from " . $dbname . ".kecamatan where id_kab='".$id_kab."' and kecamatan='".$kecamatan."'";
			//exit("Error : ".$str1);
		$res1=fetchData($str1);
		$jlh=count($res1);

		if($jlh==0){
	        $str = "insert into " . $dbname . ".kecamatan (kecamatan,id_kab)
		      values('" . $kecamatan . "','" . $id_kab . "')";
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
	   		exit('Error : Data tidak boleh sama , kecamatan '.$kecamatan.' sudah ada');
	   	}
        break;
	default:
	break;	
}
echo "<div id=container>";
$str1 = "select * from " . $dbname . ".kecamatan a left join " . $dbname . ".kabupaten b on a.id_kab=b.id";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		 <td align=center>" . $_SESSION['lang']['kabupaten'] ."</td>
		 <td align=center>" . $_SESSION['lang']['kecamatan'] . "</td>
		<td style='width:30px;'>aksi</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
		<td align=left>" . $bar1->kabupaten . "</td>
		<td align=left>" . $bar1->kecamatan . "</td>
			<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->id . "','" . $bar1->idkec . "','" . $bar1->id . "','" . $bar1->kecamatan . "');\"></td>
		</tr>";
}

echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
		 
echo "</div>";

?>