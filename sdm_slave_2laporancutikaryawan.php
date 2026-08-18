<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$tipeprint=checkPostGet('tipeprint','');

$unit=checkPostGet('unit','');
$kodeorgnya=checkPostGet('kodeorgnya','');
$subunit=checkPostGet('subunit','');
$periode=checkPostGet('periode','');
$tipekaryawan=checkPostGet('tipekaryawan','');

$tanggal=checkPostGet('tanggal','');
$nik=checkPostGet('nik','');
$karyawanid=checkPostGet('karyawanid','');

switch($method){
	case'getsubunit':
		$optSubUnit="<option value='all'>".$_SESSION['lang']['all']."</option>";
		$optSubUnit.="<option value=''>".$unit." - Kantor</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;
	
	case'preview':
		$tab="";

		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold;'>
				<th style='text-transform: uppercase;'>".$_SESSION['lang']['nourut']."</th>
				<th style='text-transform: uppercase;'>NIK</th>
				<th style='text-transform: uppercase;'>".$_SESSION['lang']['namakaryawan']."</th>
				<th style='text-transform: uppercase;'>".$_SESSION['lang']['jabatan']."</th>
				<th style='text-transform: uppercase;'>".$_SESSION['lang']['tmk']."</th>
				<th style='text-transform: uppercase;'>".$_SESSION['lang']['periodecuti']."</th>
				<th style='text-transform: uppercase;'>".$_SESSION['lang']['hakcuti']."</th>
				<th style='text-transform: uppercase;'>CUTI TAMBAHAN</th>
				<th style='text-transform: uppercase;'>ADJS CUTI</th>
				<th style='text-transform: uppercase;'>DIAMBIL</th>
				<th style='text-transform: uppercase;'>SISA</th>";
		$tab.="</tr>";
        
		$tab.="</thead><tbody>";
		$no=0;

        ## Ambil dari cuti ht
        $str = "select * from ".$dbname.".sdm_cutiht where periodecuti='".$periode."' and kodeorg = '".$unit."'";
        $res = fetchdata($str);
		foreach($res as $val){
            $no++;
			$tab.="<tr class='rowcontent'>";
			    $tab.="<td align=center>".$no."</td>";
			    $tab.="<td>".getNik($val['karyawanid'])."</td>";
			    $tab.="<td style='text-transform: uppercase;'>".getNamaKaryawan($val['karyawanid'])."</td>";
			    $tab.="<td style='text-transform: uppercase;'>".getJabatanKaryawan($val['karyawanid'])."</td>";
			    $tab.="<td align=center>".getKary($val['karyawanid'],'tanggalmasuk')."</td>";
			    $tab.="<td align=center>".$val['periodecuti']."</td>";
			    $tab.="<td align=center>".$val['hakcuti']."</td>";
			    $tab.="<td align=center>".$val['cutitambahan']."</td>";
			    $tab.="<td style='cursor:pointer;color:blue;' onclick=\"detailadj('".$val['karyawanid']."','".$val['periodecuti']."')\")' align=center>".$val['adjs_hakcuti']."</td>";
			    $tab.="<td style='cursor:pointer;color:blue;' onclick=\"detail('".$val['karyawanid']."','".$val['periodecuti']."')\")' align=center>".$val['diambil']."</td>";
			    $tab.="<td align=center>".$val['sisa']."</td>";
        }
        
        $tab.="</tr>";

		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_CutiKaryawan_".$unit."_".$periode;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$tab))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		}
	break;

    case'detail':
		$tab="";
        $tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader style='text-align:center;font-weight:bold;'>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['nourut']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['notransaksi']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['namakaryawan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['tanggal']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['keperluan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['keterangan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['jumlah']." hari</th>
            <th style='text-transform: uppercase;'>Status Potongan</th>";
    $tab.="</tr>";
    $tab.="</thead><tbody>";

    $no=0;
    ## Ambil dari cuti ht
    $str = "select * from ".$dbname.".sdm_ijin where periodecuti='".$periode."' and karyawanid='".$karyawanid."'";
    $res = fetchdata($str);
    foreach($res as $val){
        $no++;
        $tab.="<tr class='rowcontent'>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$val['notransaksi']."</td>";
            $tab.="<td style='text-transform: uppercase;'>".getNamaKaryawan($val['karyawanid'])."</td>";
            $tab.="<td align=center>".$val['tanggal']."</td>";
            $tab.="<td>".$val['keperluan']."</td>";
            $tab.="<td>".$val['keterangan']."</td>";
            $tab.="<td align=center>".$val['jumlahhari']."</td>";

            if($val['statuspotongan'] == '1'){
                $text = 'POTONG HAK CUTI';
            }else{
                $text = 'TIDAK POTONG HAK CUTI';
            }

            $tab.="<td align=center>".$text."</td>";
    }
    
    $tab.="</tr>";

	echo $tab;
    break;

    case'detailadj':
		$tab="";
        $tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader style='text-align:center;font-weight:bold;'>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['nourut']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['namakaryawan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['periodecuti']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['jumlah']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['keterangan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['createby']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['createtime']."</th>";
    $tab.="</tr>";
    $tab.="</thead><tbody>";

    $no=0;

    ## Ambil dari adjcuti
    $str = "select * from ".$dbname.".sdm_5cutiadjsment where periodecuti='".$periode."' and karyawanid='".$karyawanid."'";
    $res = fetchdata($str);
    foreach($res as $val){
        $no++;
        $tab.="<tr class='rowcontent'>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td style='text-transform: uppercase;'>".getNamaKaryawan($val['karyawanid'])."</td>";
            $tab.="<td align=center>".$val['periodecuti']."</td>";
            $tab.="<td align=center>".$val['adjs_hakcuti']."</td>";
            $tab.="<td align=center>".$val['keterangan']."</td>";
            
            if($val['createdby'] == '0000000000'){
                $text = 'Automatic System';
            }else{
                $text = getNamaKaryawan($val['createdby']);
            }

            $tab.="<td align=center>".$text."</td>";
            $tab.="<td align=center>".$val['createtime']."</td>";
    }
    $tab.="</tr>";


    echo $tab;
    break;

}


?>