<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php

$unit = checkPostGet('unit','');
$station = checkPostGet('station','');
$kodebarang = checkPostGet('kodebarang','');
$method = checkPostGet('method','');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
?>

<?php

switch ($method) {

	case'getstation':
		$optstation="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and tipe='STATION'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optstation.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
		}
		echo $optstation;
	break;

    case 'insert':
        $str = "insert into " . $dbname . ".pabrik_5criticalparts (unit,station,kodebarang,updateby)
            values ('" . $unit . "','" . $station . "','" . $kodebarang . "','" . $_SESSION['standard']['userid'] . "')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

   
    case'loaddata':
        echo"
            <div id=container>
		<table class=sortable cellspacing=1 border=0>
                    <thead>
			 <tr class=rowheader>
			 	 <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                                 <td align=center>" . $_SESSION['lang']['unit'] . "</td>
				 <td align=center>" . $_SESSION['lang']['station'] . "</td>
				 <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
				 <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
				 <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
                                 <td align=center>" . $_SESSION['lang']['action'] . "</td>
			 </tr>
		</thead>
		<tbody>";


        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pabrik_5criticalparts"; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		//  limit " . $offset . "," . $limit . "
        $str = "select * from " . $dbname . ".pabrik_5criticalparts";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($bar = $res->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . $nmorg[$bar['unit']] . "</td>";
            echo "<td align=left>" . $nmorg[$bar['station']] . "</td>";
            echo "<td align=left>" . $bar['kodebarang'] . "</td>";
            echo "<td align=left>" . $nmbrg[$bar['kodebarang']] . "</td>";
            echo "<td align=left>" . (isset($nmKar[$bar['updateby']]) ? $nmKar[$bar['updateby']] : '') . "</td>";
     
            echo "<td align=center>
                           <img src=images/application/application_delete.png class=resicon  caption='Delete' 
						   onclick=\"del('".$bar['unit']."','".$bar['station']."','".$bar['kodebarang']."');\">
                            </td>";
            echo "</tr>"; //
        }
        
        echo"</tbody></table>";
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".pabrik_5criticalparts where unit='" . $unit . "' and station='".$station."' and kodebarang='".$kodebarang."' ";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    default:
}
?>
