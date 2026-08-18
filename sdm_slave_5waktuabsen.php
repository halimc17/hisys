<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$karyawanid = checkPostGet('karyawanid','');
$stdabsen = checkPostGet('stdabsen','');

switch ($method) 
{	
	case'preview':
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['nik']."</td>
				<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center>Standard Absensi</td>
				<td align=center>".$_SESSION['lang']['jammasuk']."</td>
				<td align=center>".$_SESSION['lang']['jamistirahatdari']."</td>
				<td align=center>".$_SESSION['lang']['jamistirahatsampai']."</td>
				<td align=center>".$_SESSION['lang']['jamkeluar']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no=0;
		$str = "select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date("Y-m-d") . "') order by namakaryawan asc";
		$res = fetchdata($str);
		foreach ($res as $val){
			$no++;
			$jammasuk = "";
			$jamistirahatdari = "";
			$jamistirahatsampai = "";
			$jamkeluar = "";
			$optWaktuAbsen = makeOption($dbname,'sdm_5waktuabsen','karyawanid,waktuabsen',"karyawanid='".$val['karyawanid']."'");
			$optstdabsen="<option value=''></option>";
			$strx="select * from ".$dbname.".sdm_5stdwaktuabsen where status='A' and unit='".$unit."'";
			$resx=fetchdata($strx);
			foreach($resx as $keyx=>$valx){
				if($valx['kode'] == $optWaktuAbsen[$val['karyawanid']]){
					$optstdabsen.="<option value='".$valx['kode']."' selected>".$valx['keterangan']."</option>";
					$jammasuk = $valx['jammasuk'].":".$valx['menitmasuk'];
					$jamistirahatdari = $valx['jamistdari'].":".$valx['menitistdari'];
					$jamistirahatsampai = $valx['jamistsampai'].":".$valx['menitistsampai'];
					$jamkeluar = $valx['jamkeluar'].":".$valx['menitkeluar'];
				}else{
					$optstdabsen.="<option value='".$valx['kode']."'>".$valx['keterangan']."</option>";
				}
			}
			
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$val['nik']."</td>";
            $tab.="<td align=left>".$val['namakaryawan']."</td>";
            $tab.="<td align=center>
				<select id='stdabsen_".$val['karyawanid']."' onchange=getketerangan('".$val['karyawanid']."')>".$optstdabsen."</select>
			</td>";
            $tab.="<td align=center><label id='jammasuk_".$val['karyawanid']."'>".$jammasuk."</label></td>";
            $tab.="<td align=center><label id='jamistirahatdari_".$val['karyawanid']."'>".$jamistirahatdari."</label></td>";
            $tab.="<td align=center><label id='jamistirahatsampai_".$val['karyawanid']."'>".$jamistirahatsampai."</label></td>";
            $tab.="<td align=center><label id='jamkeluar_".$val['karyawanid']."'>".$jamkeluar."</label></td>";

            $tab.="</tr>";
        }
		
		echo $tab;
	break;
	
	case 'getketerangan':
		$str="select * from ".$dbname.".sdm_5stdwaktuabsen where kode='".$stdabsen."'";
		$res=fetchdata($str);
		if($stdabsen==''){
			$jammasuk = "";
			$jamistirahatdari = "";
			$jamistirahatsampai = "";
			$jamkeluar = "";
		}else{
			$jammasuk = $res[0]['jammasuk'].":".$res[0]['menitmasuk'];
			$jamistirahatdari = $res[0]['jamistdari'].":".$res[0]['menitistdari'];
			$jamistirahatsampai = $res[0]['jamistsampai'].":".$res[0]['menitistsampai'];
			$jamkeluar = $res[0]['jamkeluar'].":".$res[0]['menitkeluar'];
		}
		
		$str="select * from ".$dbname.".sdm_5waktuabsen where karyawanid='".$karyawanid."'";
		$res=fetchdata($str);
		$coundata = count($res);
		
		if($coundata > 0){
			if($stdabsen==''){
				$str="delete from ".$dbname.".sdm_5waktuabsen where karyawanid='".$karyawanid."'";
			}else{
				$str="update ".$dbname.".sdm_5waktuabsen set waktuabsen='".$stdabsen."' where karyawanid='".$karyawanid."'";
			}
		}else{
			$str="insert into ".$dbname.".sdm_5waktuabsen values('".$karyawanid."','".$stdabsen."')";
		}
		try
		{
			$owlPDO->exec($str);
			echo $jammasuk."####".$jamistirahatdari."####".$jamistirahatsampai."####".$jamkeluar;
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
}
?>
