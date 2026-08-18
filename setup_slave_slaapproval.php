<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$proses  = checkPostGet('proses', '');
$token   = checkPostGet('token', '');
$alamat  = checkPostGet('alamat', '');
$action  = checkPostGet('action', '');
$id      = checkPostGet('id', '');
$username= checkPostGet('username', '');
$param   = $_POST;

switch ($proses) {
	case'delete':
		$strx = "delete from ".$dbname.".setup_slaapproval where id='".$id."'";
		try {$owlPDO->exec($strx);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	break;
    case 'simpan':
		if($id!=''){
			$str="update ".$dbname.".setup_slaapproval set jenisapproval='".$param['jenis']."',dariuser='".$param['dariuser']."', keuser='".$param['keuser']."', status='".$param['status']."',hari='".$param['hari']."' where id='".$id."'";
			try{
				$owlPDO->exec($str);
				}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
		}else{			
			$str="insert into ".$dbname.".setup_slaapproval (jenisapproval, dariuser, keuser, hari, status, updateby)
			values('".$param['jenis']."','".$param['dariuser']."','".$param['keuser']."','".$param['hari']."','".$param['status']."','".$_SESSION['standard']['userid']."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
		}
	break;

    case 'loaddata':
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=0>
			<thead>
			<tr class=rowheader>    ";
		$tab.="<th align=center>No</th>";
		$tab.="<th align=center>Jenis Approval</th>";
		$tab.="<th align=center>Dari User</th>";
		$tab.="<th align=center>Ke User</th>";
		$tab.="<th align=center>Outstanding<br>(Hari)</th>";
		$tab.="<th align=center>Status</th>";
		$tab.="<th align=center colspan=2>Action</th>";
		$tab.="</tr></thead>";
		
		$arrlok = makeOption($dbname,'setup_jenisapproval','jenis,nama');
		
		$str = "select * from ".$dbname.".setup_slaapproval order by id";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $arrlok[$bar['jenisapproval']] . "</td>";
            $tab.="<td align=left>" . getNamaKaryawan($bar['dariuser']) . "</td>";
            $tab.="<td align=left>" . getNamaKaryawan($bar['keuser']) . "</td>";
			$tab.="<td align=center>".$bar['hari']."</td>";
			if($bar['status']=='1'){				
				$tab.="<td align=left>Aktif</td>";
			}else{
				$tab.="<td align=left>Non Aktif</td>";
			}
            $tab.="<td align=center width=25px><img src=images/skyblue/edit.png class=zImgBtn onclick=\"showEdit('".$bar['id']."','".$bar['jenisapproval']."','".$bar['dariuser']."','".$bar['keuser']."','".$bar['hari']."','".$bar['status']."')\" title=Edit></td>";
            $tab.="<td align=center width=25px><img src=images/skyblue/delete.png class=zImgBtn onclick=del('".$bar['id']."') title=Delete></td>";
			
		}
		$tab.="</tr>";
		$tab.="</table>";
		
		echo $tab;
	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	#$n = number_format($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
function bagi($e,$i){
	if($i!='' and $i!='0'){
		$n=$e/$i;
	}else{
		$n=0;
	}
	return $n;
}
?>
