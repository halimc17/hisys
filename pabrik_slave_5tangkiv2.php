<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg = checkPostGet('kodeorg','');
$kodetangki = checkPostGet('kodetangki','');
$komoditi = checkPostGet('komoditi','');
$kapasitas = checkPostGet('kapasitas','');
$keterangan = checkPostGet('keterangan','');
$keterangan = checkPostGet('keterangan','');
$cycling = checkPostGet('cycling','');
$method = checkPostGet('method','');

switch ($method) {


    case 'insert':
        $str = "insert into " . $dbname . ".pabrik_5tangki (kodeorg,kodetangki,komoditi,kapasitas,keterangan,cycling,createby,createtime)
            values ('".$kodeorg."','".$kodetangki."','".$komoditi."','".$kapasitas."','".$keterangan."','".$cycling."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'update':
        $str = "update ".$dbname.".pabrik_5tangki set kapasitas = '".$kapasitas."',keterangan='".$keterangan."',cycling='".$cycling."',updateby='" . $_SESSION['standard']['userid'] . "' where kodeorg='".$kodeorg."' and kodetangki='".$kodetangki."' ";
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
			 	<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
                <td align=center>".$_SESSION['lang']['kodetangki']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['komoditi']."</td>
                <td align=center>".$_SESSION['lang']['kapasitas']." (Kg)</td>
                <td align=center>".$_SESSION['lang']['cycling']."</td>
                <td align=center>".$_SESSION['lang']['updateby']."</td>
                <td align=center colspan=2>".$_SESSION['lang']['action']."</td>
			 </tr>
		</thead>
		<tbody>";

        $str = "select * from " . $dbname . ".pabrik_5tangki order by kodeorg";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $updateby   = $bar['createby'];
            if($bar['updateby'] == '0000000000'){
                $updateby = $bar['createby'];
            }
            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$updateby."'");
            $nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
            echo "<tr class=rowcontent>";
            echo "<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
            echo "<td align=left>".$bar['kodetangki']."</td>";
            echo "<td align=left>".$bar['keterangan']."</td>";
            echo "<td align=left>".$bar['komoditi']."</td>";
            echo "<td align=right>".number_format($bar['kapasitas'])."</td>";
            echo "<td align=right>".$bar['cycling']."</td>";
            echo "<td align=right>".$nmKar[$updateby]."</td>";
            echo "<td align=center>
            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['kodeorg']."','".$bar['kodetangki']."','".$bar['komoditi']."','".$bar['kapasitas']."','".$bar['keterangan']."','".$bar['cycling']."');\">
            <td align=center>
            <img src=images/delete_32.png class=resicon  caption='Hapus' onclick=\"del('".$bar['kodeorg']."','".$bar['kodetangki']."');\">
            </td>";
            echo "</tr>";
        }
        
        echo"</tbody></table>";
        break;

    case 'delete':
        $str = "delete from ".$dbname.".pabrik_5tangki where kodeorg='".$kodeorg."' and kodetangki='".$kodetangki."'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    default:
}
?>
