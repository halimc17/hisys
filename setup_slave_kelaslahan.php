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
			$str = "insert into ".$dbname.".setup_kelaslahan values ('".$jenis."','".$nama."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
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
		$str = "update ".$dbname.".setup_kelaslahan set aktif='".$status."', nama='".$nama."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date('Y-m-d H:i:s')."' where kode = '".$jenis."'";
		
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
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".setup_kelaslahan order by kode asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['kode']."</td>";
            $tab.="<td>".$bar['nama']."</td>";
            $tab.="<td align=center>".$arrstatus[$bar['aktif']]."</td>";
			$tab.="<td align=left>".$optNamaKar[$bar['updateby']]."</td>";
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['kode']."','".$bar['nama']."','".$bar['aktif']."');\">
			</td>";

            $tab.="</tr>";
        }
		
		echo $tab;
	break;

    default:
}
?>
