<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
	
$method = checkPostGet('method', '');
$id=checkPostGet('id','');
$id_kec=checkPostGet('kecamatan','');
$kecamatan=checkPostGet('kecamatan','');
$desa=checkPostGet('desa','');
//echo $provinsi;
	
switch($method){	
	case 'update':
        $str = "update " . $dbname . ".desa set desa='" . $desa . "'
	       where id_kec='" . $id_kec . "' and iddes='". $id ."'";
	       //exit($str);
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
		
    case 'insert':
			
        $str = "insert into " . $dbname . ".desa (desa,id_kec)
	      values('" . $desa . "','" . $id_kec . "')";
        //exit('error'.$str);
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
		
	case 'deletefield':
		$str="select * from ".$dbname.".lgl_csr where kodekecamatan='".$id_kec."' and kodedesa='".$id."'";
		$res=fetchdata($str);
		
		if(count($res) > 0){
			exit("Gagal : Item ini sudah/pernah dilakukan transaksi.");
		}else{
			$str = "delete from ".$dbname.".desa where iddes='".$id."' and id_kec='".$id_kec."'";
			try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
        break;
		
	default:
	break;	
}
//echo $_SESSION['lang']['list'];

echo "<div id=container>";
$str1 = "select * from " . $dbname . ".desa a left join " . $dbname . ".kecamatan b on a.id_kec=b.idkec
order by a.id_kec";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 cellpadding=3 border=0>
	     <thead>
		 <tr class=rowheader>
		 <td style='width:100px;' align=center>" . $_SESSION['lang']['kecamatan'] ."</td>
		 <td style='width:100px;' align=center>" . $_SESSION['lang']['desa'] . "</td>
		<td style='width:10px;'>aksi</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
		<td align=left>" . $bar1->kecamatan . "</td>
		<td align=left>" . $bar1->desa . "</td>
		<td align=center>
			<img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"fillField('" . $bar1->id_kec . "','" . $bar1->iddes . "','" . $bar1->idkec . "','" . $bar1->desa . "');\">&nbsp;
			<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deletefield('".$bar1->iddes."','".$bar1->idkec."');\">
		</td>
		</tr>";
}
echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
		 
echo "</div>";
CLOSE_BOX();
?>