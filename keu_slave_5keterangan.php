<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$notransaksi = checkPostGet('notransaksi','');
$aruskas = checkPostGet('aruskas','');
$keterangan = checkPostGet('keterangan','');
$aktif = checkPostGet('aktif','');
$find_aruskas = checkPostGet('find_aruskas','');
$find_keterangan = checkPostGet('find_keterangan','');
$arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif");
switch ($method) 
{
	case 'insert':
		//cek apakah merk sudah ada ??
		$str = "select count(*) as jumlah from ".$dbname.".keu_5keterangan where noaruskas = '".$aruskas."' and keterangan='".$keterangan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$countitem = $bar['jumlah'];
		if($countitem >= 1)
		{
			exit("Warning : Keterangan sudah pernah terdaftar sebelumnya.");
		}
		else
		{
			$str = "insert into ".$dbname.".keu_5keterangan values ('','".$aruskas."','".$keterangan."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','','".$aktif."')";
			//exit('error'.$str);
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
		$str = "update ".$dbname.".keu_5keterangan set status='".$aktif."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date('Y-m-d H:i')."' where id_ket = '".$notransaksi."'";
        try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'delete':
		$str = "delete from ".$dbname.".pmn_hargabelitbs where notransaksi = '".$notransaksi."'";
		
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
	
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
		$where="";
		if($find_aruskas!=''){ 
			$where.=" and a.noaruskas LIKE  '%".$find_aruskas."%'";
		}
		if($find_keterangan!=''){ 
			$where.=" and UPPER(a.keterangan) LIKE  '%".strtoupper($find_keterangan)."%'";
		}
		
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_5keterangan a
			    left join keu_5aruskas b on a.noaruskas=b.noaruskas
				where 0=0 ".$where.""; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0 min-width=700px>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['noaruskas']."</td>
				<td align=center>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['aruskas']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan');
		$str = "select a.id_ket,a.noaruskas, b.nama_aruskas,a.keterangan,a.status,a.updateby from ".$dbname.".keu_5keterangan a
				left join keu_5aruskas b on a.noaruskas=b.noaruskas
				where 0=0 ".$where." order by a.id_ket desc LIMIT ".$offset.",".$limit."";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$no++;
			$tab.="<tr class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['noaruskas']."</td>";
            $tab.="<td>".$bar['nama_aruskas']."</td>";
            $tab.="<td>".$bar['keterangan']."</td>";
            $tab.="<td>".$arrstatus[$bar['status']]."</td>";
			$tab.="<td align=left>".$optNamaKar[$bar['updateby']]."</td>";
            
			$tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['id_ket']."','".$bar['noaruskas']."','".$bar['keterangan']."','".$bar['status']."');\"></td>";
		    
            $tab.="</tr>";
        }
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0)
		{
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++)
		{
		  $sel = ($page==$er-1)? 'selected': '';
		  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$tab.="<tr><td colspan=7 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;

    default:
}
?>
