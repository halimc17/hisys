<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');

$spt = checkPostGet('spt','');
$sunit = checkPostGet('sunit','');
$sstt = checkPostGet('sstt','');

$kode = checkPostGet('kode','');
$pt = checkPostGet('pt','');
$unit = checkPostGet('unit','');
$keterangan = checkPostGet('keterangan','');
$jam10 = checkPostGet('jam10','');
$mnt10 = checkPostGet('mnt10','');
$jam11 = checkPostGet('jam11','');
$mnt11 = checkPostGet('mnt11','');
$jam20 = checkPostGet('jam20','');
$mnt20 = checkPostGet('mnt20','');
$jam21 = checkPostGet('jam21','');
$mnt21 = checkPostGet('mnt21','');
$jam30 = checkPostGet('jam30','');
$mnt30 = checkPostGet('mnt30','');
$jam31 = checkPostGet('jam31','');
$mnt31 = checkPostGet('mnt31','');
$jam40 = checkPostGet('jam40','');
$mnt40 = checkPostGet('mnt40','');
$jam41 = checkPostGet('jam41','');
$mnt41 = checkPostGet('mnt41','');
$stt = checkPostGet('stt','');


switch ($method){
	case'getunit':
		$optunit="";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($unit==$val['kodeorganisasi']){
				$optunit.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";				
			}else{
				$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";				
			}
		}
		echo $optunit;
	break;
	
	case'getunitsearch':
		$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($spt!=''){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$spt."'";
			$res=fetchdata($str);
			foreach($res as $val){
				$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";				
			}
		}
		echo $optunit;
	break;
	
	case 'insert':
		if($keterangan==""){
			exit("Gagal : Keterangan harus diisi");
		}
	
		## BEGIN CREATE KODE ATTEDENCE ###
		$str = "select kode from ".$dbname.".sdm_5stdwaktuabsen where unit = '".$unit."' order by kode asc limit 1";
		$res=fetchdata($str);
		if(count($res) > 0){
			$expkode = substr($res[0]['kode'],4,2);
			$kode = $unit."".addZero(($expkode+1),2);
		}else{
			$kode = $unit."01";
		}
		
		$str = "insert into ".$dbname.".sdm_5stdwaktuabsen values ('".$kode."','".$pt."','".$unit."','".$keterangan."','".$jam10."','".$mnt10."','".$jam11."','".$mnt11."','".$jam20."','".$mnt20."','".$jam21."','".$mnt21."','".$jam30."','".$mnt30."','".$jam31."','".$mnt31."','".$jam40."','".$mnt40."','".$jam41."','".$mnt41."','".$stt."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

    case 'update':
		if($keterangan==""){
			exit("Gagal : Keterangan harus diisi");
		}
		
		$str = "update ".$dbname.".sdm_5stdwaktuabsen set keterangan='".$keterangan."', jammasuk='".$jam10."', menitmasuk='".$mnt10."', tjammasuk='".$jam11."', tmenitmasuk='".$mnt11."', jamistdari='".$jam20."', menitistdari='".$mnt20."', tjamistdari='".$jam21."', tmenitistdari='".$mnt21."', jamistsampai='".$jam30."', menitistsampai='".$mnt30."', tjamistsampai='".$jam31."', tmenitistsampai='".$mnt31."', jamkeluar='".$jam40."', menitkeluar='".$mnt40."', tjamkeluar='".$jam41."', tmenitkeluar='".$mnt41."', status='".$stt."', updatedby='".$_SESSION['standard']['userid']."', updatedtime='".date('Y-m-d H:i:s')."' where kode = '".$kode."'";
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
		$tab.="<table class=sortable cellpadding=3 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td rowspan=2 align=center>".$_SESSION['lang']['nourut']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['kode']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['pt']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['unit']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</td>
				<td colspan=2 align=center>".$_SESSION['lang']['jammasuk']."</td>
				<td colspan=2 align=center>".$_SESSION['lang']['jamistirahatdari']."</td>
				<td colspan=2 align=center>".$_SESSION['lang']['jamistirahatsampai']."</td>
				<td colspan=2 align=center>".$_SESSION['lang']['jamkeluar']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['status']."</td>
				<td rowspan=2 align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['waktu']."</td>
				<td align=center>".$_SESSION['lang']['toleransi']."</td>
				<td align=center>".$_SESSION['lang']['waktu']."</td>
				<td align=center>".$_SESSION['lang']['toleransi']."</td>
				<td align=center>".$_SESSION['lang']['waktu']."</td>
				<td align=center>".$_SESSION['lang']['toleransi']."</td>
				<td align=center>".$_SESSION['lang']['waktu']."</td>
				<td align=center>".$_SESSION['lang']['toleransi']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$where="";
		if($spt!=''){
			$where.=" and pt='".$spt."'";
		}
		if($sunit!=''){
			$where.=" and unit='".$sunit."'";
		}
		if($sstt!=''){
			$where.=" and status='".$sstt."'";
		}
		$str = "select * from ".$dbname.".sdm_5stdwaktuabsen where 1=1 ".$where." order by status asc, kode asc";
		$res = fetchdata($str);
		if(count($res) > 0){
			foreach ($res as $val){
				$no++;
				$toleransi = ($val['tjammasuk']>0?hidezerodecimal($val['jammasuk']).' Jam, ':'')."".hidezerodecimal($val['tmenitmasuk'],0)." Menit";
				$toleransi2 = ($val['tjamistdari']>0?hidezerodecimal($val['tjamistdari']).' Jam, ':'')."".hidezerodecimal($val['tmenitistdari'],0)." Menit";
				$toleransi3 = ($val['tjamistsampai']>0?hidezerodecimal($val['tjamistsampai']).' Jam, ':'')."".hidezerodecimal($val['tmenitistsampai'],0)." Menit";
				$toleransi4 = ($val['tjamkeluar']>0?hidezerodecimal($val['tjamkeluar']).' Jam, ':'')."".hidezerodecimal($val['tmenitkeluar'],0)." Menit";
				$status = ($val['status']=='A'?'Aktif':'Non-Aktif');
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=right>".$no."</td>";
				$tab.="<td align=center>".$val['kode']."</td>";
				$tab.="<td align=center>".$val['pt']."</td>";
				$tab.="<td align=center>".$val['unit']."</td>";
				$tab.="<td align=left>".$val['keterangan']."</td>";
				$tab.="<td align=center>".$val['jammasuk'].":".$val['menitmasuk']."</td>";
				$tab.="<td align=center>".$toleransi."</td>";
				$tab.="<td align=center>".$val['jamistdari'].":".$val['menitistdari']."</td>";
				$tab.="<td align=center>".$toleransi2."</td>";
				$tab.="<td align=center>".$val['jamistsampai'].":".$val['menitistsampai']."</td>";
				$tab.="<td align=center>".$toleransi3."</td>";
				$tab.="<td align=center>".$val['jamkeluar'].":".$val['menitkeluar']."</td>";
				$tab.="<td align=center>".$toleransi4."</td>";
				$tab.="<td align=center>".$status."</td>";
				$tab.="<td align=center>
					<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$val['kode']."','".$val['pt']."','".$val['unit']."','".$val['keterangan']."','".$val['jammasuk']."','".$val['menitmasuk']."','".$val['tjammasuk']."','".$val['tmenitmasuk']."','".$val['jamistdari']."','".$val['menitistdari']."','".$val['tjamistdari']."','".$val['tmenitistdari']."','".$val['jamistsampai']."','".$val['menitistsampai']."','".$val['tjamistsampai']."','".$val['tmenitistsampai']."','".$val['jamkeluar']."','".$val['menitkeluar']."','".$val['tjamkeluar']."','".$val['tmenitkeluar']."','".$val['status']."');\">
				</td>";

				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class=rowcontent><td colspan=15 align='center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}
		$tab.="</tbody>
		</table>";
		
		echo $tab;
	break;

    default:
}
?>
