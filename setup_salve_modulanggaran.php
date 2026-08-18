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
		$str = "select count(kode) as count from ".$dbname.".setup_modulanggaran where UPPER(modul) = '".strtoupper($jenis)."'";
		$res=fetchdata($res);
		$countitem = $res[0]['count'];
		
		if($countitem >= 1){
			exit("Warning : Modul sudah pernah terdaftar sebelumnya.");
		}else{
			$str = "insert into ".$dbname.".setup_modulanggaran values ('".$jenis."','".$nama."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
			try{
				$owlPDO->exec($str);
			}catch(PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
	break;

    case 'update':
		$str = "update ".$dbname.".setup_modulanggaran set status='".$status."', modul='".$nama."', updateby='".$_SESSION['standard']['userid']."' where kode = '".$jenis."'";
		
        try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

    case'loaddata':
		$tab="<table class=sortable cellpadding=3 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['modul']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".setup_modulanggaran order by modul asc";
		$res=fetchdata($str);
        foreach($res as $val){
			$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$val['kode']."</td>";
            $tab.="<td>".$val['modul']."</td>";
            $tab.="<td align=center>".$arrstatus[$val['status']]."</td>";
			$tab.="<td align=left>".$optNamaKar[$val['updateby']]."</td>";
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$val['kode']."','".$val['modul']."','".$val['status']."');\">
			</td>";

            $tab.="</tr>";
        }
		
		echo $tab;
	break;

    default:
}
?>
