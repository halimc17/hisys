<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$id = checkPostGet('id','');
$jenis = checkPostGet('jenis','');
$ket = checkPostGet('ket','');
$method = checkPostGet('method','');
$arrstatus=array('1' => 'Disposal','2' => 'Write-off');

switch ($method) {
    case'insert':

        $query="select count(jenis) as nomorurut from ".$dbname.".keu_5jenisdisposalasset where jenis= '".$jenis."'";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
        $nomorurut=$rp['nomorurut'];

        $nomorurut+=1;
        $id=$jenis.$nomorurut;


        $str="insert into ".$dbname.".keu_5jenisdisposalasset (id,jenis,keterangan,updateby)
            values ('".$id."','".$jenis."','".$ket."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;


    case'update':
        $str="update ".$dbname.".keu_5jenisdisposalasset set updateby='".$_SESSION['standard']['userid']."',keterangan='".$ket."'
             where id='".$id."'";
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
			 	<td align=center>".$_SESSION['lang']['nourut']."</td>
                <td align=center>".$_SESSION['lang']['jenis']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
                <td align=center>" . $_SESSION['lang']['action'] . "</td>
			</tr>
		</thead>
		<tbody>";

        $limit=10;
        $page=0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $ql2="select count(*) as jmlhrow from " . $dbname . ".keu_5jenisdisposalasset"; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl=$query2->fetch()) {
            $jlhbrs=$jsl->jmlhrow;
        }
		
        $str="select * from ".$dbname.".keu_5jenisdisposalasset  limit ".$offset.",".$limit."";
		$n=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        $no=$maxdisplay;
        while ($d=$n->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . $arrstatus[$d['jenis']] . "</td>";
            echo "<td align=left>" . $d['keterangan'] . "</td>";
            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            echo "<td align=center>
                 <img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('".$d['id']."','".$d['jenis']."','".$d['keterangan']."');\">
                  </td>";
            echo "</tr>";
        }
        
        echo"</tbody></table>";
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".keu_5jenisdisposalasset where id='" . $id . "'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    default:
}
?>
