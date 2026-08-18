<?php
include('lib/nangkoelib.php');
require_once('lib/zLib.php');

try {
$owlPDO->beginTransaction();
$tab="<table class='sortable' cellspacing='0' cellpadding='5' border='1'>
	<thead>
		<tr class=rowheader>
			<th align=center width=40px>No</th>
			<th align=center >Nopp</th>
			<th align=center >Kodeorg</th>
			<th align=center width=100px>Tanggal</th>
			<th align=center >Req</th>
			<th align=center >Dept</th>";
			$range = range(1,8);
			foreach($range as $level){
				$tab.="<th align=center >".$level."</th>";
			}
			
$tab.="</thead>
	<tbody>";

$str = "select * from ".$dbname.".log_prapoht where `close` = '1' AND (tanggal like '2021%' or tanggal like '2022%')";
$res = fetchdata($str);
foreach($res as $val){
	$no++;
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>".$no."</td>";
	$tab.="<td align=center>".$val['nopp']."</td>";
	$tab.="<td align=center>".$val['unit']."</td>";
	$tab.="<td align=center>".$val['tanggal']."</td>";
	$tab.="<td align=center>".getKary($val['requester'])."</td>";
	$tab.="<td align=center>".getKary($val['requester'],'bagian')."</td>";
	
	$approv=array(); $approvkary="";
	$query="select * from ".$dbname.".approval where notransaksi='".$val['nopp']."' and status='0'";
	$rquery = fetchdata($query);
	foreach($rquery as $bas){
		$approv[$bas['level']]=$bas['karyawanid'];
		$approvkary=$bas['karyawanid'];
	}
	
	$sql="select * from ".$dbname.".setup_approval where jenispersetujuan='PR' and departemen='".getKary($val['requester'],'bagian')."' and kodeunit='".$val['unit']."' and level='1'";
	$req = fetchdata($sql);
	if(count($req)==0){
		$deptxx=false;
	}else{
		$deptxx=true;
	}
	
	$levelkary=0;
	if($deptxx==true){
		$sqlx="select * from ".$dbname.".setup_approval where jenispersetujuan='PR' and departemen='".getKary($val['requester'],'bagian')."' and kodeunit='".$val['unit']."' and karyawanid='".$approvkary."'";
		$req = fetchdata($sqlx);
		$levelkary = $req[0]['level'];
	}else{
		$sqlx="select * from ".$dbname.".setup_approval where jenispersetujuan='PR' and departemen='' and kodeunit='".$val['unit']."' and karyawanid='".$approvkary."'";
		$req = fetchdata($sqlx);
		$levelkary = $req[0]['level'];
	}
	
	
	$newapprov=array();
	$querydel="";
	foreach($range as $level){			
		if($deptxx==true){
			$sql="select * from ".$dbname.".setup_approval where jenispersetujuan='PR' and departemen='".getKary($val['requester'],'bagian')."' and kodeunit='".$val['unit']."' and level='".$level."'";
			$req = fetchdata($sql);
		}else{
			$sql="select * from ".$dbname.".setup_approval where jenispersetujuan='PR' and departemen='' and kodeunit='".$val['unit']."' and level='".$level."'";
			$req = fetchdata($sql);
		}
		foreach($req as $bar){
			$newapprov[$bar['level']]=$bar['karyawanid'];
		}
		if($newapprov[$level]!=$approv[$level] and $approv[$level]!=''){
			$querydel = "delete from " . $dbname . ".approval where notransaksi ='".$val['nopp']."'";
			$owlPDO->exec($querydel);
		}
	}	
	
	$newapprov=array();
	foreach($range as $level){			
		if($deptxx==true){
			$sql="select * from ".$dbname.".setup_approval where jenispersetujuan='PR' and departemen='".getKary($val['requester'],'bagian')."' and kodeunit='".$val['unit']."' and level='".$level."'";
			$req = fetchdata($sql);
		}else{
			$sql="select * from ".$dbname.".setup_approval where jenispersetujuan='PR' and departemen='' and kodeunit='".$val['unit']."' and level='".$level."'";
			$req = fetchdata($sql);
		}
		foreach($req as $bar){
			$newapprov[$bar['level']]=$bar['karyawanid'];
		}
		$color="";
		if($newapprov[$level]!=$approv[$level] and $approv[$level]!=''){
			$color="style=background-color:blue;";
		}
		$dxdxdx="";
		if($querydel!='' and $level<=$levelkary){
			if($level<$levelkary){
				$status='1';
			}else{
				$status='0';
			}
			$data = array();
			$data = array(
				'notransaksi'     => $val['nopp'],
				'jenispersetujuan'=> 'PR',
				'level'           => $level,
				'status'          => $status,
				'karyawanid'      => $newapprov[$level],
				'komentar'        => 'Penyesuaian level approval yang baru'
			);
			$query = insertQuery($dbname,'approval',$data,array_keys($data)); #exit("error".$query);
			$owlPDO->exec($query);
		}
		
		
		$tab.="<td align=center ".$color.">".getKary($newapprov[$level])."<br><b>".getKary($approv[$level])."</b></td>";
	}
	
}

	$owlPDO->commit();
} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
echo $tab;
?>