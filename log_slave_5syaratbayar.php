<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php

$kode = checkPostGet('kode','');
$jenis = checkPostGet('jenis','');
$ket = checkPostGet('ket','');
$method = checkPostGet('method','');
?>

<?php

switch ($method) {


    case 'insert':
        $i = "insert into " . $dbname . ".log_5syaratbayar (kode,jenis,keterangan,updateby)
            values ('" . $kode . "','" . $jenis . "','" . $ket . "','" . $_SESSION['standard']['userid'] . "')";
		try{
			$owlPDO->exec($i); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'update':
        $i = "update " . $dbname . ".log_5syaratbayar set jenis='" . $jenis . "',"
                . " updateby='" . $_SESSION['standard']['userid'] . "',keterangan='" . $ket . "'
             where kode='" . $kode . "'";
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
                                 <td align=center>" . $_SESSION['lang']['kode'] . "</td>
				 <td align=center>" . $_SESSION['lang']['jenis'] . "</td>
				 <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
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

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5syaratbayar"; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
        $i = "select * from " . $dbname . ".log_5syaratbayar  limit " . $offset . "," . $limit . "";
		$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . $d['kode'] . "</td>";
            echo "<td align=left>" . $d['jenis'] . "</td>";
            echo "<td align=left>" . $d['keterangan'] . "</td>";
            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            //echo "<td align=left>".$d['updatetime']."</td>";
            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['kode'] . "','" . $d['jenis'] . "',"
            . "'" . $d['keterangan'] . "');\">
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
