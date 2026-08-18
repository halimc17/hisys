<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kodebarang_det = checkPostGet('kodebarang_det','');
$pt = checkPostGet('pt','');
$spesifikasi = checkPostGet('spesifikasi','');
$minstok_det = checkPostGet('minstok_det','');
$method = checkPostGet('method','');

$pic = checkPostGet('pic','');
$kodebarang = checkPostGet('kodebarang','');

switch($method){
	
		case 'insert':
		//cek apakah barang sudah ada ??
		$str = "select count(kodebarang) as kodebarang from ".$dbname.".log_5masterbarang_minstock where kodebarang='".$kodebarang_det."' and kodeunit='".$pt."'";
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
			$input = "insert into " . $dbname . ".log_5masterbarang_minstock (kodebarang,kodeunit,minstok)
					values ('".$kodebarang_det."','".$pt."','".$minstok_det."')";
			try{
			  $owlPDO->exec($input); 
			}catch(PDOException $e){
			  echo " Gagal," . addslashes($e->getMessage());
			}
		}
	
	break;

    case 'delete':
        $input = "delete from " . $dbname . ".log_5masterbarang_minstock where kodebarang='".$kodebarang_det."' and kodeunit='".$pt."'";
        try{
		$owlPDO->exec($input); 
		}catch(PDOException $e){
		  echo " Gagal," . addslashes($e->getMessage());
		}
            
	break;

	case'loadData':
    echo"<table class=sortable cellpadding=5 cellspacing=1 border=0>
		 <thead>
			<tr class=rowheader>
			 <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
			 <th align=center>" . $_SESSION['lang']['pt'] . "</th>
			 <th align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
			 <th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			 <th align=center>" . $_SESSION['lang']['satuan'] . "</th>
			 <th align=center>" . $_SESSION['lang']['minstok'] . "</th>
			 <th align=center>" . $_SESSION['lang']['action'] . "</th>
			</tr>
		</thead>
		<tbody>";

		$nor=0;
		$input = "select a.*, b.namabarang, b.satuan from " . $dbname . ".log_5masterbarang_minstock a left join log_5masterbarang b on a.kodebarang=b.kodebarang where a.kodebarang = '".$kodebarang_det."'";
		$n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
		while ($d = $n->fetch()) {
			$no+=1;
			echo "<tr class=rowcontent>";
			echo "<td align=center>" . $no . "</td>";
			echo "<td align=center>" . $d['kodeunit'] . "</td>";
			echo "<td align=left>" . $d['kodebarang'] . "</td>";
			echo "<td align=left>" . $d['namabarang'] . "</td>";
			echo "<td align=left>" . $d['satuan'] . "</td>";
			echo "<td align=right>" . $d['minstok'] . "</td>";
			echo "<td align=center>
				<img src=images/skyblue/delete.png class=resicon  caption='Delete' onclick=\"del('" . $d['kodeunit'] . "',". "'" . $d['kodebarang'] . "');\">
				</td>";

			echo "</tr>"; 
		 }
			
		echo"</tbody></table>";
	break;
	
	case'deleteimage':
		$str="update ".$dbname.".log_5photobarang set ".$pic."='' where kodebarang='".$kodebarang."'";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'nonaktifbarang':
		$str="update ".$dbname.".log_5masterbarang set inactive='1' where kodebarang='".$kodebarang."'";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
		default:
		break;
	
}

?>