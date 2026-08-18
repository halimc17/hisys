<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php

$kode = checkPostGet('kode','');
$tipewt = checkPostGet('tipewt','');
$nama = checkPostGet('nama','');
$method = checkPostGet('method','');
$optkd=array("DPWT"=>"Demin Water Treatment","POWT"=>"Plant Operation Water Treatment");

?>

<?php

switch ($method) {
    case 'insert':
        $strCount = "select kode as nourut from " . $dbname . ".pabrik_5mr_bfwt where kd_transaksi='".$tipewt."' order by kode desc limit 1";
        $rData=fetchData($strCount);
        if(intval($rData[0]['nourut'])==0){
            $kode=addZero(1,3);
        }else{
            $kode=addZero((intval($rData[0]['nourut'])+1),3);
        }

        $str = "insert into " . $dbname . ".pabrik_5mr_bfwt (kode,kd_transaksi,nama,updateby)
            values ('".$kode."','" . $tipewt . "','" . $nama . "','" . $_SESSION['standard']['userid'] . "')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
    break;

    case 'update':
        $str = "update " . $dbname . ".pabrik_5mr_bfwt set updateby='" . $_SESSION['standard']['userid'] . "',
			nama='" . $nama . "' where kode='".$kode."' and kd_transaksi='" . $tipewt . "'";
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
			 	 <td align=center>" . $_SESSION['lang']['kode'] . "</td>
                                 <td align=center>" . $_SESSION['lang']['tipewt'] . "</td>
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

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pabrik_5mr_bfwt"; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		//  limit " . $offset . "," . $limit . "
        $i = "select * from " . $dbname . ".pabrik_5mr_bfwt order by kd_transaksi";
		$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $d['kode'] . "</td>";
            echo "<td align=left>" . $d['kd_transaksi']." - ".$optkd[$d['kd_transaksi']] . "</td>";
            echo "<td align=left>" . $d['nama'] . "</td>";
            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            echo "<td align=center>
                     <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit(".$d['kode'].",'".$d['kd_transaksi']."','".$d['nama']."');\">
                  </td>";
            echo "</tr>"; 
        }
        echo"</tbody></table>";
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".a where tipewt='" . $tipewt . "'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    default:
}
?>
