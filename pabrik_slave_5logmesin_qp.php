<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php

$unit = checkPostGet('unit','');
$ht = checkPostGet('ht','');
$st = checkPostGet('st','');
$dt = checkPostGet('dt','');
$nil = checkPostGet('nil','');
$method = checkPostGet('method','');

$nmdata=makeOption($dbname,'pabrik_5logmesin_klasifikasi','kode,nama');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

?>

<?php

switch ($method) {


    case 'insert':
        $str = "insert into " . $dbname . ".pabrik_5logmesin_qp (unit,station,header,detail,nilai,updateby)
            values ('" . $unit . "','".$st."','".$ht."','" . $dt . "','".$nil."','" . $_SESSION['standard']['userid'] . "')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'update':
        $str = "update " . $dbname . ".pabrik_5logmesin_qp set updateby='" . $_SESSION['standard']['userid'] . "',nilai='" . $nil . "'
             where unit='" . $unit . "' and station='" . $st . "' and header='" . $ht . "' and detail='" . $dt . "'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
            
        break;


    case'loadData':
        echo"
            <div id=container>
		<table class=sortable cellspacing=1 border=0>
                    <thead>
			 <tr class=rowheader>
			 	 <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				 <td align=center>" . $_SESSION['lang']['unit'] . "</td>
				 <td align=center>" . $_SESSION['lang']['station'] . "</td>
				 <td align=center>" . $_SESSION['lang']['header'] . "</td>
				 <td align=center>" . $_SESSION['lang']['detail'] . "</td>
				 <td align=center>" . $_SESSION['lang']['nilai'] . "</td>
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

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pabrik_5logmesin_qp"; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
        $str = "select * from " . $dbname . ".pabrik_5logmesin_qp ";// limit " . $offset . "," . $limit . "
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
            echo "<td align=left>" . $nmdata[$bar['header']] . "</td>";
			echo "<td align=left>" . @$nmdata[$bar['detail']] . "</td>";
            echo "<td align=right>" . $bar['nilai'] . "</td>";
            echo "<td align=left>" . (isset($nmKar[$bar['updateby']]) ? $nmKar[$bar['updateby']] : '') . "</td>";
            //echo "<td align=left>".$bar['updatetime']."</td>";
            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $bar['unit'] . "',"
            . "'" . $bar['station'] . "','" . $bar['header'] . "','" . $bar['detail'] . "','" . $bar['nilai'] . "');\">
                            </td>";
            echo "</tr>"; //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kode']."');\">
        }
        
        echo"</tbody></table>";
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".kebun_5dendapengawas where kode='" . $kode . "'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    default:
}
?>
