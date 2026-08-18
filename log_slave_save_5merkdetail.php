<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$idmerk = checkPostGet('idmerk','');
$idmerk_det = checkPostGet('idmerk_det','');
$kodebarang_det = checkPostGet('kodebarang_det','');
$method = checkPostGet('method','');

switch($method){
	
		case 'insert':
		//cek apakah barang sudah ada ??
		$str = "select count(kodebarang) as kodebarang from ".$dbname.".log_5merkbarangdt where idmerk='".$idmerk_det."' and kodebarang='".$kodebarang_det."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$countitem = $bar['kodebarang'];
		
		if($countitem >= 1)
		{
			exit("Warning : Barang sudah pernah terdaftar sebelumnya.");
		}
		else
		{
			$input = "insert into " . $dbname . ".log_5merkbarangdt (id,idmerk,kodebarang,createby,createtime,updateby,updatetime)
					values ('','".$idmerk_det."','".$kodebarang_det."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
			try{
			  $owlPDO->exec($input); 
			}catch(PDOException $e){
			  echo " Gagal," . addslashes($e->getMessage());
			}
		}
	
	break;

    case 'delete':
        $input = "delete from " . $dbname . ".log_5merkbarangdt where idmerk='".$idmerk_det."' and kodebarang='".$kodebarang_det."'";
        try{
		$owlPDO->exec($input); 
		}catch(PDOException $e){
		  echo " Gagal," . addslashes($e->getMessage());
		}
            
	break;

	case'loadData':
    echo"<table class=sortable cellpadding=1 cellspacing=1 border=0>
		 <thead>
			<tr class=rowheader>
			 <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
			 <td align=center>ID " . $_SESSION['lang']['merk'] . "</td>
			 <td align=center>" . $_SESSION['lang']['merk'] . "</td>
			 <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
			 <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
			 <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
			 <td align=center>" . $_SESSION['lang']['action'] . "</td>
			</tr>
		</thead>
		<tbody>";

		$nor=0;
		$namabrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
		$namamerk = makeOption($dbname, 'log_5merkbaranght', 'idmerk,merk');
		$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		
		$input = "select * from " . $dbname . ".log_5merkbarangdt where idmerk = '".$idmerk."' order by createtime desc";
		$n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
		while ($d = $n->fetch()) {
			$no+=1;
			echo "<tr class=rowcontent>";
			echo "<td align=center>" . $no . "</td>";
			echo "<td align=center>" . $d['idmerk'] . "</td>";
			echo "<td align=left>" . $namamerk[$d['idmerk']] . "</td>";
			echo "<td align=left>" . $d['kodebarang'] . "</td>";
			echo "<td align=left>" . $namabrg[$d['kodebarang']] . "</td>";
			echo "<td align=left>" . $nmKar[$d['updateby']] . "</td>";
			echo "<td align=center>
				<img src=images/skyblue/delete.png class=resicon  caption='Delete' onclick=\"del('" . $d['idmerk'] . "',". "'" . $d['kodebarang'] . "');\">
				</td>";

			echo "</tr>"; 
		 }
			
		echo"</tbody></table>";
		break;
		default:
		break;
	
}

?>