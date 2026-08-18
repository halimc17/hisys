<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$proses  = checkPostGet('proses', '');
$token   = checkPostGet('token', '');
$alamat  = checkPostGet('alamat', '');
$action  = checkPostGet('action', '');
$id      = checkPostGet('id', '');
$username= checkPostGet('username', '');
$param = $_POST;

switch ($proses) {
	case'delete':
		$strx = "delete from ".$dbname.".setup_runningtext where id='".$id."'";
		try {$owlPDO->exec($strx);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	break;
    case 'simpan':
	
	
		if($id!=''){
			$str="update ".$dbname.".setup_runningtext set lokasi='".$param['lokasi']."', text='".$param['text']."', status='".$param['status']."' where id='".$id."'";
			try{
				$owlPDO->exec($str);
				}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
		}else{			
			$str="insert into ".$dbname.".setup_runningtext (lokasi,text,status)
			values('".$param['lokasi']."','".$param['text']."','".$param['status']."')";
			try{
				$owlPDO->exec($str);
				}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
		}
	break;

    case 'loaddata':
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=0>
			<thead>
			<tr class=rowheader>    ";
		$tab.="<td align=center>No</td>";
		$tab.="<td align=center>Lokasi</td>";
		$tab.="<td align=center>Text</td>";
		$tab.="<td align=center>Status</td>";
		$tab.="<td align=center colspan=2>Action</td>";
		$tab.="</tr></thead>";
	
		$arrlok=array("L"=>"Luar (Form Loggin)","D"=>"Dalam (Setelah Loggin)");
	
		$str = "select * from ".$dbname.".setup_runningtext order by id";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $arrlok[$bar['lokasi']] . "</td>";
            $tab.="<td align=left>" . $bar['text'] . "</td>";
			if($bar['status']=='1'){				
				$tab.="<td align=left>Aktif</td>";
			}else{
				$tab.="<td align=left>Non Aktif</td>";
			}
            $tab.="<td align=center width=25px><img src=images/skyblue/edit.png class=zImgBtn onclick=\"showEdit('".$bar['id']."','".$bar['text']."','".$bar['lokasi']."','".$bar['status']."')\" title=Edit></td>";
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
