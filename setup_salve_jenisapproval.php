<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$jenis = checkPostGet('jenis','');
$nama = checkPostGet('nama','');
$status = checkPostGet('status','');
$arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif");

switch ($method) 
{
	case 'insert':
		$str = "select count(jenis) as count from ".$dbname.".setup_jenisapproval where UPPER(jenis) = '".strtoupper($jenis)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		$countitem = $bar['count'];
		
		if($countitem >= 1)
		{
			exit("Warning : Jenis Approval sudah pernah terdaftar sebelumnya.");
		}
		else
		{
			$str = "insert into ".$dbname.".setup_jenisapproval values ('".$jenis."','".$nama."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
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
		$str = "update ".$dbname.".setup_jenisapproval set status='".$status."', nama='".$nama."', updateby='".$_SESSION['standard']['userid']."' where jenis = '".$jenis."'";
		
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
		$tab="<table class=sortable cellpadding=7 cellspacing=1 style='width:100%;' border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['jenispersetujuan']."</td>
				<td align=center>".$_SESSION['lang']['namajenispersetujuan']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['createby']."</td>
				<td align=center>".$_SESSION['lang']['createtime']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center>".$_SESSION['lang']['updatetime']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".setup_jenisapproval order by jenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['jenis']."</td>";
            $tab.="<td>".$bar['nama']."</td>";
            $tab.="<td align=center>".$arrstatus[$bar['status']]."</td>";
			$tab.="<td align=center>".$optNamaKar[$bar['createdby']]."</td>";
			$tab.="<td align=center>".$bar['createdtime']."</td>";
			$tab.="<td align=center>".$optNamaKar[$bar['updateby']]."</td>";
			$tab.="<td align=center>".$bar['updatetime']."</td>";
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['jenis']."','".$bar['nama']."','".$bar['status']."');\">
			</td>";

            $tab.="</tr>";
        }
		
		echo $tab;
	break;

    default:
}
?>
