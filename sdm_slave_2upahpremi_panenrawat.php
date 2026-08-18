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
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and tipe = 'AFDELING' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;
	
	case'preview':

		
		$tab="";
		
		$gettglawal=$periode."-01";
		$gettglakhir=tglakhir($periode);
		$bulan=tanggalbulan($periode);
		$exptglakhir=explode('-',$gettglakhir);
		$tglawal='01';
		$tglakhir=$exptglakhir[2];

		$rangetgl = rangeTanggalarr($gettglawal,$gettglakhir);
		
		$where="";
		if($subunit=='all'){

            $afdeling = '';
            $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and tipe = 'AFDELING' order by kodeorganisasi";
            $res=fetchdata($str);
            foreach($res as $val){
                if($afdeling==''){
                    $afdeling="'".$val['kodeorganisasi']."'";
                }else{
                    $afdeling.=",'".$val['kodeorganisasi']."'";
                }
            }

			$where.="and subbagian in (".$afdeling.")";

		}else{
			$where.=" and subbagian='".$subunit."'";
		}

        if($tipekaryawan!='all'){
			$where.=" and tipekaryawan='".$tipekaryawan."'";
        }


		$where.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$gettglakhir."')";
		$where.= " and tanggalmasuk<='".$gettglakhir."'";
	
		$dakarbulanan=0;
        $str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."' ";
        $res = fetchdata($str);
        if(count($res)>0){ 
            $dakarbulanan=1;
        }

        if($dakarbulanan == 1){
            $str = "select karyawanid,nik,namakaryawan,kodejabatan,subbagian from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."' ".$where." order by namakaryawan asc ";
            $res = fetchdata($str);
		    $arrkary=$res;
        }else{
            $str="select karyawanid,nik,namakaryawan,kodejabatan,subbagian from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." order by namakaryawan asc";
            $res=fetchdata($str);
            $arrkary=$res;
        }

        ## Rawat
        $arrpremi_upah_rawat=array();
        $str = "select * from ".$dbname.".kebun_kehadiran_vw where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res = fetchdata($str);
        foreach($res as $val){
            $arrpremi_upah_rawat[$val['karyawanid']][$val['tanggal']] = $val['umr'] + $val['insentif'] ;
        }

        ## Panen
        $arrpremi_upah_panen=array();
        $str = "select * from ".$dbname.".kebun_prestasi_vs_hk where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res = fetchdata($str);
        foreach($res as $val){
            $arrpremi_upah_panen[$val['karyawanid']][$val['tanggal']] = $val['tupah'] + $val['tpremi'] - $val['upahpenalty']  ;
        }
		

		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$colspn=$tglakhir*2;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['subunit']."</th>
				<th colspan='".$colspn."'>Periode ".$periode."</th>
				<th rowspan='3'>Total Rawat</th>
				<th rowspan='3'>Total Panen</th>
				<th rowspan='3'>Total</th>
			</tr>";
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$tab.="<th colspan=2>".addZero($i,2)."</th>";
			}
		$tab.="</tr>";
		$tab.="<tr>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$tab.="<th>Rawat</th>";
				$tab.="<th>Panen</th>";
			}
		$tab.="</tr>";
		$tab.="</thead><tbody>";
		$no=0;$ttlabs=$ttpremi=[];
		foreach($arrkary as $val){
            $no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td align='center'>".$val['nik']."</td>
					<td style='text-transform: uppercase;'>".$val['namakaryawan']."</td>
					<td>".getNamaJabatan($val['kodejabatan'])."</td>
					<td>".getNamaOrg($val['subbagian'],'namaorganisasi')."</td>";

					foreach($rangetgl as $tgl){				
						$tab.="<td align='right' style='cursor:pointer;color:blue;' onclick=detailrawat('".$val['karyawanid']."','".$tgl."')>".number_format($arrpremi_upah_rawat[$val['karyawanid']][$tgl],0)."</td>";
						$tab.="<td align='right' style='cursor:pointer;color:blue;' onclick=detailpanen('".$val['karyawanid']."','".$tgl."')>".number_format($arrpremi_upah_panen[$val['karyawanid']][$tgl],0)."</td>";

                        $ttpremi_rawat[$val['karyawanid']]+=$arrpremi_upah_rawat[$val['karyawanid']][$tgl];
                        $gtpremi_rawat[$tgl]+=$arrpremi_upah_rawat[$val['karyawanid']][$tgl];
                        $ggtpremi_rawat+=$arrpremi_upah_rawat[$val['karyawanid']][$tgl];

                        $ttpremi_panen[$val['karyawanid']]+=$arrpremi_upah_panen[$val['karyawanid']][$tgl];
                        $gtpremi_panen[$tgl]+=$arrpremi_upah_panen[$val['karyawanid']][$tgl];
                        $ggtpremi_panen+=$arrpremi_upah_panen[$val['karyawanid']][$tgl];
                    }
                    
                    $tab.="<td align='right'>".number_format($ttpremi_rawat[$val['karyawanid']],0)."</td>";
                    $tab.="<td align='right'>".number_format($ttpremi_panen[$val['karyawanid']],0)."</td>";
                    $tab.="<td align='right'>".number_format($ttpremi_panen[$val['karyawanid']] + $ttpremi_rawat[$val['karyawanid']] ,0)."</td>";
                }
                $tab.="</tr>";

				$tab.="<tr class='rowcontent'>";
					$tab.="<td align='center' colspan=5><b>TOTAL PREMI</b></td>";
					foreach($rangetgl as $tgl){			
						$tab.="<td align='center'><b>".number_format($gtpremi_rawat[$tgl],0)."</b></td>";
						$tab.="<td align='center'><b>".number_format($gtpremi_panen[$tgl],0)."</b></td>";
					}
                    $tab.="<td align='center'><b>".number_format($ggtpremi_rawat,0)."</b></td>";
                    $tab.="<td align='center'><b>".number_format($ggtpremi_panen,0)."</b></td>";
                    $tab.="<td align='center'><b>".number_format($ggtpremi_panen + $ggtpremi_rawat ,0)."</b></td>";
                $tab.="</tr>";
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_UpahPremi_RawatPanen_".$unit."_".$periode;
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

    case'detailrawat':

        ## Ambil dari kebun_kehadiran_vw
		## Perawatan
        $str1 = "select * from ".$dbname.".kebun_kehadiran_vw where tanggal = '".$tanggal."' and karyawanid = '".$karyawanid."' order by tanggal asc";
		$res1 = fetchdata($str1);
        foreach($res1 as $val){
            $datakar[$val['karyawanid']] = $val['karyawanid'];
            $notransaksi[$val['karyawanid']] = $val['notransaksi'];
            $tgl[$val['karyawanid']] = $val['tanggal'];
            $kegiatan[$val['karyawanid']] = $val['kodekegiatan'];
            $jhk[$val['karyawanid']] = $val['jhk'];
            $umr[$val['karyawanid']] = $val['umr'];
            $premi[$val['karyawanid']] = $val['insentif'];
        }

        $tab="";
        $tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader style='text-align:center;font-weight:bold;'>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['nourut']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['notransaksi']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['namakaryawan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['tanggal']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['kegiatan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['jhk']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['umr']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['premi']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['total']."</th>";
		$tab.="</tr>";
		$tab.="</thead><tbody>";

		$no=0;
		foreach($datakar as $karid ){
            $no++;
            $tab.="<tr class='rowcontent'>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=center>".$notransaksi[$karid]."</td>";
                $tab.="<td align=center>".getNamaKaryawan($karid)."</td>";
                $tab.="<td align=center>".$tgl[$karid]."</td>";
                $tab.="<td align=center>".$kegiatan[$karid]." - ".getNamaKeg($kegiatan[$karid],'namakegiatan')."</td>";
                $tab.="<td align=center>".$jhk[$karid]."</td>";
                $tab.="<td align=center>".number_format($umr[$karid],0)."</td>";
                $tab.="<td align=center>".number_format($premi[$karid],0)."</td>";
                $tab.="<td align=center>".number_format($premi[$karid]+$umr[$karid],0)."</td>";
		}
		$tab.="</tr>";

		echo $tab;

        
    break;
	case'detailpanen':

        ## Ambil dari kebun_kehadiran_vw
		## Panen
        $str1 = "select * from ".$dbname.".kebun_prestasi_vs_hk where tanggal = '".$tanggal."' and karyawanid = '".$karyawanid."' order by tanggal asc";
		$res1 = fetchdata($str1);
        foreach($res1 as $val){
            $datakar[$val['karyawanid']]= $val['karyawanid'];
            $notransaksi[$val['karyawanid']]= $val['notransaksi'];
            $tgl[$val['karyawanid']]= $val['tanggal'];
            $upahkerja[$val['karyawanid']]= $val['upahkerja'];  
            $premi[$val['karyawanid']]= $val['tpremi'];  
            $penalty[$val['karyawanid']]= $val['upahpenalty'];  
        }

        
        $tab="";
        $tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader style='text-align:center;font-weight:bold;'>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['nourut']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['notransaksi']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['namakaryawan']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['tanggal']." Panen</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['upah']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['premi']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['penalti']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['total']."</th>
			
			";
           
		$tab.="</tr>";
		$tab.="</thead><tbody>";

		$no=0;
		foreach($datakar as $karid){
            $no++;
            $tab.="<tr class='rowcontent'>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=center>".$notransaksi[$karid]."</td>";
                $tab.="<td align=center>".getNamaKaryawan($karid)."</td>";
                $tab.="<td align=center>".$tgl[$karid]."</td>";
                $tab.="<td align=center>".number_format($upahkerja[$karid])."</td>";
                $tab.="<td align=center>".number_format($premi[$karid])."</td>";
                $tab.="<td align=center>".number_format($penalty[$karid])."</td>";
                $tab.="<td align=center>".number_format($upahkerja[$karid] + $premi[$karid] - $penalty[$karid] )."</td>";                
		}
		$tab.="</tr>";

		echo $tab;
    break;
}


?>