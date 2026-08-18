<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php

$sup = checkPostGet('sup','');
$bank = checkPostGet('bank','');
$rek = checkPostGet('rek','');
$an = checkPostGet('an','');
$method = checkPostGet('method','');
 $nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan'); 
?>

<?php

switch ($method) {


    case 'insert':
        $i = "insert into " . $dbname . ".log_5rekbank (supplierid,bank,rekening,an,updateby)
            values ('" . $sup . "','" . $bank . "','" . $rek . "','" . $an . "','" . $_SESSION['standard']['userid'] . "')";
		try{
			$owlPDO->exec($i); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'update':
        $i = "update " . $dbname . ".log_5rekbank set rekening='" . $rek . "',"
                . " updateby='" . $_SESSION['standard']['userid'] . "',an='" . $an . "'
             where supplierid='" . $sup . "' and bank='".$bank."'";
        try{
			$owlPDO->exec($i); 
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
				 <td align=center>" . $_SESSION['lang']['supplier'] . "</td>
				 <td align=center>" . $_SESSION['lang']['bank'] . "</td>
				 <td align=center>Rekening</td>
				 <td align=center>Atas Nama</td>
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

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5rekbank"; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
        $i = "select * from " . $dbname . ".log_5rekbank  limit " . $offset . "," . $limit . "";
		$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {
           
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . $nmsup[$d['supplierid']]. "</td>";
            echo "<td align=left>" . $d['bank'] . "</td>";
            echo "<td align=left>" . $d['rekening'] . "</td>";
			 echo "<td align=left>" . $d['an'] . "</td>";
            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['supplierid'] . "','" . $d['bank'] . "',"
            . "'" . $d['rekening'] . "','" . $d['an'] . "');\">
                            </td>";
            echo "</tr>"; //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['kode']."');\">
        }
        /* echo"
          <tr class=rowheader><td colspan=18 align=center>
          ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
          <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
          <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
          </td>
          </tr>"; */
        echo"</tbody></table>";
        break;

    case 'delete':
        $i = "delete from " . $dbname . ".kebun_5dendapengawas where kode='" . $kode . "'";
        try{
			$owlPDO->exec($i); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    default:
}
?>
