<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php

$parameter = checkPostGet('parameter','');
$ket = checkPostGet('ket','');
$method = checkPostGet('method','');

?>

<?php

switch ($method) {


    case 'insert':
        $str = "insert into " . $dbname . ".pabrik_5mr_roa_parameter (parameter,nama,updateby)
            values ('" . $parameter . "','" . $ket . "','" . $_SESSION['standard']['userid'] . "')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'update':
        $str = "update " . $dbname . ".pabrik_5mr_roa_parameter set updateby='" . $_SESSION['standard']['userid'] . "',
			nama='" . $ket . "' where parameter='" . $parameter . "'";
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
                                 <td align=center>" . $_SESSION['lang']['kodeparameter'] . "</td>
				 <td align=center>" . $_SESSION['lang']['nama'] . "</td>
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

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pabrik_5mr_roa_parameter"; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		//  limit " . $offset . "," . $limit . "
        $i = "select * from " . $dbname . ".pabrik_5mr_roa_parameter";
		$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . $d['parameter'] . "</td>";
            echo "<td align=left>" . $d['nama'] . "</td>";
            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            //echo "<td align=left>".$d['updatetime']."</td>";
            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['parameter'] . "',"
            . "'" . $d['nama'] . "');\">
                            </td>";
            echo "</tr>"; //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['parameter']."');\">
        }
        
        echo"</tbody></table>";
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".a where parameter='" . $parameter . "'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    default:
}
?>
