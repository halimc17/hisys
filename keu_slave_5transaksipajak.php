<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$id=checkPostGet('id','');
$jenis=checkPostGet('jenis','');
$method=checkPostGet('method','');
$status = checkPostGet('status','');
$arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif");


switch ($method) {
    case 'insert':

        $query="select count(id) as nomorurut from ".$dbname.".keu_5transaksipajak";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }
        $id=addZero($awal,2);

        $str="insert into ".$barbname.".keu_5transaksipajak (id,jenis,status,createdby,updateby) values ('".$id."','".$jenis."','".$status."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal,".addslashes($e->getMessage());
		}
        break;

    case 'update':
        $str="update ".$barbname.".keu_5transaksipajak set updateby='".$_SESSION['standard']['userid']."',jenis='".$jenis."',status='".$status."'
             where id='".$id."'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal,".addslashes($e->getMessage());
		}
            
        break;

    case'loadData':
        echo"
            <div id=container>
            <table class=sortable cellspacing=1 border=0>
            <thead><tr class=rowheader>
			 	<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['jenistransaksi']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
                <td align=center>".$_SESSION['lang']['action']."</td>
			 </tr></thead>
            <tbody>";

        $limit=10;
        $page=0;
        if (isset($_POST['page'])) {
            $page=$_POST['page'];
            if ($page < 0)
                $page=0;
        }
        $offset=$page * $limit;
        $maxdisplay=($page * $limit);

        $str="select count(*) as jmlhrow from ".$barbname.".keu_5transaksipajak";
        $bar=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$bar->setFetchMode(PDO::FETCH_OBJ);
		while($res=$bar->fetch()) {
            $jlhbrs=$res->jmlhrow;
        }
		
        $str="select * from ".$barbname.".keu_5transaksipajak  limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $no=$maxdisplay;
        while($bar=$res->fetch()){
            $nmKar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>".$no."</td>";
            echo "<td align=left>".$bar['jenis']."</td>";
            echo "<td align=left>".(isset($nmKar[$bar['updateby']]) ? $nmKar[$bar['updateby']] : '')."</td>";
            echo "<td align=center>
                  <img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('".$bar['id']."','".$bar['jenis']."','".$bar['status']."');\">
                 </td>";
            echo "</tr>";
        }
        echo"</tbody></table>";
        break;

    case 'delete':
        $str="delete from ".$barbname.".kebun_5dendapengawas where id='".$id."'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal,".addslashes($e->getMessage());
		}
        break;

    default:
}
?>
