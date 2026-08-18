<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$method   =checkPostGet('method','');
$kegpr    =checkPostGet('kegpr','');
$unitpr   =checkPostGet('unitpr','');
$basispr  =checkPostGet('basispr','');
$premilbpr=checkPostGet('premilbpr','');

switch($method){
	case'savepr':
		$str="insert into ".$dbname.".kebun_5premibkm (`unit`,`kodekegiatan`,`basis`,`premilebihbasis`,`updateby`)
		values ('".$unitpr."','".$kegpr."','".$basispr."','".$premilbpr."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;
	case'delpr':
		$str="delete from ".$dbname.".kebun_5premibkm where unit='".$unitpr."' and kodekegiatan='".$kegpr."' ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}			
	break;
	case'loadpr':
		echo"
			<table border=0 cellpadding=5 cellspacing=1 class=sortable>
				<thead>
					<tr class=rowheader>
					<th align=center>No</th>
					<th align=center>".$_SESSION['lang']['unit']."</th>
					<th align=center>Jumlah Basis</th>
					<th align=center>Premi Lebih Basis</th>
					<th align=center>".$_SESSION['lang']['action']."</th></tr></thead>";
			$str="select * from ".$dbname.".kebun_5premibkm where kodekegiatan='".$kegpr."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$no++;
				echo"
					<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td >".getNamaOrg($bar['unit'])."</td>
					<td align=right>".$bar['basis']."</td>
					<td align=right>".$bar['premilebihbasis']."</td>
					<td align=center width=25px><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delpr('".$bar['unit']."','".$bar['kodekegiatan']."');\" ></td></tr>
				";
			}
			echo"</table>";
	break;
}



?>