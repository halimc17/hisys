<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$lokasi = checkPostGet('lokasi','');
$inisial = checkPostGet('inisial','');
$status = checkPostGet('status','');
$arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif");

switch ($method) 
{
	case 'insert':
		$str = "select count(id) as count from ".$dbname.".pmn_5lokasikontrak where UPPER(lokasi) = '".strtoupper($lokasi)."' or inisial='".$inisial."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		$countitem = $bar['count'];
		
		if($countitem >= 1)
		{
			exit("Warning : Lokasi atau Inisial sudah pernah terdaftar sebelumnya.");
		}
		else
		{
			$str = "insert into ".$dbname.".pmn_5lokasikontrak values ('','".$lokasi."','".$inisial."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
			try
			{
				$owlPDO->exec($str);
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
	break;

    case 'update':
		$str = "update ".$dbname.".pmn_5lokasikontrak set status='".$status."', updateby='".$_SESSION['standard']['userid']."' where lokasi = '".$lokasi."' and inisial='".$inisial."'";
        try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

    case'loaddata':
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['lokasikontrak']."</td>
				<td align=center>".$_SESSION['lang']['inisial']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".pmn_5lokasikontrak order by lokasi";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['lokasi']."</td>";
            $tab.="<td align=center>".$bar['inisial']."</td>";
            $tab.="<td align=center>".$arrstatus[$bar['status']]."</td>";
			$tab.="<td align=left>".$optNamaKar[$bar['updateby']]."</td>";
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['id']."','".$bar['lokasi']."','".$bar['inisial']."','".$bar['status']."');\">
			</td>";

            $tab.="</tr>";
        }
		
		echo $tab;
	break;

    default:
}
?>
