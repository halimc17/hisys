<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


$method = checkPostGet('method','');
$param=$_POST;
if($_GET['method']!=''){
    $param=$_GET;
}
 

switch ($method) {


    case 'insert':
        $str = "insert into ".$dbname .".sdm_5plafond_bbm (kodeunit,kodejabatan,plafond,tahun_berlaku,createdby,createtime)
            values ('" . $param['unit'] . "','" . $param['jbtanId'] . "','" . $param['plafond'] . "','".$param['tahun']."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
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
				 <td align=center>" . $_SESSION['lang']['jabatan'] . "</td>
				 <td align=center>" . $_SESSION['lang']['tahun'] . "</td>
				 <td align=center>" . $_SESSION['lang']['plafon'] . "</td>
				 <td align=center>" . $_SESSION['lang']['dibuatoleh'] . "</td>
                 <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
                 <td align=center>" . $_SESSION['lang']['action'] . "</td>
			 </tr>
		</thead>
		<tbody>";

        $optJabatan=makeOption($dbname,"sdm_5jabatan","kodejabatan,namajabatan");
        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $whr=" where kodeunit='".$_SESSION['empl']['lokasitugas']."'";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
            $whr="where kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where char_length(kodeorganisasi)=4 and tipe<>'HOLDING')";
        }else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
            $whr="where kodeunit in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
        }

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_5plafond_bbm ".$whr.""; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		//  limit " . $offset . "," . $limit . "
        if($jlhbrs==0){
            echo"<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
        }else{
            $str = "select * from " . $dbname . ".sdm_5plafond_bbm ".$whr." limit " . $offset . "," . $limit . "";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $no = $maxdisplay;
            while ($bar = $res->fetch()) {

                $nmKarPembuat = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['createdby']."'");
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
                $no+=1;
                echo "<tr class=rowcontent>";
                echo "<td align=center>" . $no . "</td>";
                echo "<td align=left>" . $bar['kodeunit'] . "</td>";
                echo "<td align=left>" . $optJabatan[$bar['kodejabatan']] . "</td>";
                echo "<td align=left>" . $bar['tahun_berlaku'] . "</td>";
                echo "<td align=right>" . $bar['plafond'] . "</td>";
                echo "<td align=left>" . (isset($nmKarPembuat[$bar['createdby']]) ? $nmKarPembuat[$bar['createdby']] : '') . "</td>";
                echo "<td align=left>" . (isset($nmKar[$bar['updateby']]) ? $nmKar[$bar['updateby']] : '') . "</td>";
                echo "<td align=center>
                               <img src=images/application/application_delete.png class=resicon  caption='Delete' 
                               onclick=\"del('".$bar['kodeunit']."','".$bar['kodejabatan']."','".$bar['tahun_berlaku']."');\">
                                </td>";
                echo "</tr>"; //
            }
        }
        echo "<tr class=rowheader><td colspan=8 align=center>" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
    <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
    <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
    </td>
    </tr> </tbody> </table>";
        break;

    case 'delete':
        $str = "delete from ".$dbname.".sdm_5plafond_bbm where kodeunit='".$param['unit']."' and kodejabatan='".$param['jbtanId']."' and tahun_berlaku='".$param['tahun']."' ";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    default:
}
?>
