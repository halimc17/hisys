<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
include_once('lib/HtmlExcel.php');
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
		$str="select kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$unit."' and tipe IN ('TRAKSI','WORKSHOP') order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
            if($val['tipe'] == 'TRAKSI'){
                $sel ='selected';
            }else{
                $sel ='';
            }
			$optSubUnit.="<option value='".$val['kodeorganisasi']."' ".$sel.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;
	
	case'preview':

		
		$tab="";
		if($tipeprint!='html'){
            $tab.="<h3><b>Laporan Premi Traksi</b></h3>";
            $tab.="<h5><i>Periode ".numToMonth(substr($periode,5,2),'I','long')." ".substr($periode,0,4)."</i></h5>";
        }
		$gettglawal=$periode."-01";
		$gettglakhir=tglakhir($periode);
		$bulan=tanggalbulan($periode);
		$exptglakhir=explode('-',$gettglakhir);
		$tglawal='01';
		$tglakhir=$exptglakhir[2];

		$rangetgl = rangeTanggalarr($gettglawal,$gettglakhir);
		
		$where="";
		if($subunit=='all'){
			$where.="";
		}else if($subunit==''){
			$where.=" and subbagian=''";
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
            $str = "select karyawanid,nik,namakaryawan,kodejabatan from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."' ".$where." order by namakaryawan asc ";
            $res = fetchdata($str);
		    $arrkary=$res;
        }else{
            $str="select karyawanid,nik,namakaryawan,kodejabatan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." order by namakaryawan asc";
            $res=fetchdata($str);
            $arrkary=$res;
        }

        $arrpremi=array();
        $str = "select * from ".$dbname.".vhc_runhk where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res = fetchdata($str);
        foreach($res as $val){
            $arrpremi[$val['idkaryawan']][$val['tanggal']]['pekerjaan'] += $val['premi'];
        }
        $str = "select * from ".$dbname.".vhc_spl_kehadiran_vw where tanggal between '".$gettglawal."' and '".$gettglakhir."' order by tanggal asc";
        $res = fetchdata($str);
        foreach($res as $val){
            $arrpremi[$val['nik']][$val['tanggal']]['sipil'] += $val['premi'];
        }
		

		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$colspn=$tglakhir;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th colspan='".($colspn*2)."'>Periode ".numToMonth(substr($periode,5,2),'I','long')." ".substr($periode,0,4)."</th>
				<th rowspan='3'>Total Premi</th>
			</tr>";
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$tab.="<th colspan=2>".addZero($i,2)."</th>";
			}
		$tab.="</tr>";
		$tab.="<tr>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$tab.="<th>Pekerjaan</th>";
				$tab.="<th>Sipil</th>";
			}
		$tab.="</tr>";
		$tab.="</thead><tbody>";
		$no=0;$ttlabs=$ttpremi=[];
		foreach($arrkary as $val){
            $no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td align='left'>".$val['nik']."</td>
					<td style='text-transform: uppercase;'>".$val['namakaryawan']."</td>
					<td>".getNamaJabatan($val['kodejabatan'])."</td>";

					foreach($rangetgl as $tgl){				
						$tab.="<td align='right'>".number_format($arrpremi[$val['karyawanid']][$tgl]['pekerjaan'],0)."</td>";
						$tab.="<td align='right'>".number_format($arrpremi[$val['karyawanid']][$tgl]['sipil'],0)."</td>";
                        $ttpremi[$val['karyawanid']]+=($arrpremi[$val['karyawanid']][$tgl]['pekerjaan']+$arrpremi[$val['karyawanid']][$tgl]['sipil']);
                        $gtpremi[$tgl]['pekerjaan']+=$arrpremi[$val['karyawanid']][$tgl]['pekerjaan'];
                        $gtpremi[$tgl]['sipil']+=$arrpremi[$val['karyawanid']][$tgl]['sipil'];
                        $ggtpremi+=($arrpremi[$val['karyawanid']][$tgl]['pekerjaan']+$arrpremi[$val['karyawanid']][$tgl]['sipil']);
                    }
                    $tab.="<td align='right'><b>".number_format($ttpremi[$val['karyawanid']],0)."</b></td>";
                }
                $tab.="</tr>";

				$tab.="<tr class='rowcontent'>";
					$tab.="<td align='center' colspan=4><b>TOTAL PREMI</b></td>";
					foreach($rangetgl as $tgl){			
						$tab.="<td align='right'><b>".number_format($gtpremi[$tgl]['pekerjaan'],0)."</b></td>";
						$tab.="<td align='right'><b>".number_format($gtpremi[$tgl]['sipil'],0)."</b></td>";
					}
                    $tab.="<td align='right'><b>".number_format($ggtpremi,0)."</b></td>";
                $tab.="</tr>";
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_PremiTraksi_".$unit."_".$periode.".xls";
			if(strlen($tab)>0){
                $xls = new HtmlExcel();
                $xls->setCss($css);
                $xls->addSheet("Laporan_PremiTraksi_", $tab);
                $xls->headers($nop_);
                echo $xls->buildFile();
			}
		}
	break;
}


?>