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
$shiftkar=checkPostGet('shiftkar','');


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
			$wheres_fp.="";
		}else if($subunit==''){
			$where.=" and subbagian=''";
			$wheres_fp.=" and subbagian=''";
		}else{
			$where.=" and subbagian='".$subunit."'";
			$where_fp.=" and subbagian='".$subunit."'";
		}

		$where.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$gettglakhir."')";
		$where.= " and tanggalmasuk<='".$gettglakhir."'";
	
		$str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." order by namakaryawan asc";
		$res=fetchdata($str);
		$arrkary=$res;
		

        $str = "select * from ".$dbname.".sdm_5shift where kodeorg = '".$unit."'";
        $res = fetchdata($str);
        if(count($res)==0){
            exit("errorcode : Master shift untuk kode organisasi ".$unit." belum ada.");
        }

        foreach($res as $val){
        
            $jamshiftmasuk[$val['id']]    = $val['masuk'];
            $jamshiftoutist[$val['id']]   = $val['keluar_ist'];
            $jamshiftinist[$val['id']]    = $val['masuk_ist'];
            $jamshiftpulang[$val['id']]   = $val['keluar'];
            $jamshifttoleransi[$val['id']]= $val['toleransi'];
            $jamshiftbatasawal[$val['id']]= $val['batas_awal'];
            $jamshifttipe_shift[$val['id']]= $val['tipe_shift'];
        }
		
        $jamshift = array();
        $str = "select * from ".$dbname.".sdm_5shiftanggota where kodeorg = '".$unit."' ".$where_fp." and tanggal between '".$gettglawal."' and '".tglbesok($gettglakhir)."' order by tanggal";
        $res = fetchdata($str);
        foreach($res as $val){
            $jamshift[$val['karyawanid']][$val['tanggal']]['namashift']= $val['namashift'];
            $jamshift[$val['karyawanid']][$val['tanggal']]['ke']       = $val['shift'];
            $jamshift[$val['karyawanid']][$val['tanggal']]['idshift']  = $val['idshift'];
            $jamshift[$val['karyawanid']][$val['tanggal']]['masuk']    = $jamshiftmasuk[$val['idshift']];
            $jamshift[$val['karyawanid']][$val['tanggal']]['outist']   = $jamshiftoutist[$val['idshift']];
            $jamshift[$val['karyawanid']][$val['tanggal']]['inist']    = $jamshiftinist[$val['idshift']];
            $jamshift[$val['karyawanid']][$val['tanggal']]['pulang']   = $jamshiftpulang[$val['idshift']];
            $jamshift[$val['karyawanid']][$val['tanggal']]['toleransi']= $jamshifttoleransi[$val['idshift']];
            $jamshift[$val['karyawanid']][$val['tanggal']]['batasawal']= $jamshiftbatasawal[$val['idshift']];
            $jamshift[$val['karyawanid']][$val['tanggal']]['tipe_shift']     = $jamshifttipe_shift[$val['idshift']];
        }
		
	
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$colspn=$tglakhir*6;

		if($tipeprint!='html'){
			$tab="<fieldset style=float:left>
				<legend>Note</legend>
				<table  cellpadding=5 cellspacing=1 border=0 class=sortable>
					<tr>
						<td style='width:20px;background:blue'>&nbsp;</td>
						<td colspan=2>Jam Finger Valid</td>
					</tr>
					<tr>
						<td style='width:20px;background:red'>&nbsp;</td>
						<td colspan=2>Jam Finger Tidak Valid (Jam tidak sesuai dengan shift kerja)</td>
					</tr>
					<tr>
						<td style='width:20px;background:green'>&nbsp;</td>
						<td colspan=2>Jam Finger Valid (Jam finger dari BA-ABSENSI)</td>
					</tr>
					<tr>
					</tr>
				</table>
				</div>
			</fieldset>";
		}
		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th colspan='".$colspn."'>".$bulan."</th>
			</tr>";
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$tab.="<th colspan=6>".addZero($i,2)."</th>";
			}
		$tab.="</tr>";
		$tab.="<tr>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$tab.="<th>Nama Shift</th>";
				$tab.="<th>Jam Masuk</th>";
				$tab.="<th>Ist Mas</th>";
				$tab.="<th>Ist Kel</th>";
				$tab.="<th>Jam Pulang</th>";
				$tab.="<th>Batas Awal</th>";
			}
		$tab.="</tr>";
		$tab.="</thead><tbody>";

        $optnamashfit=makeOption($dbname, 'sdm_5mastershift','shift,namashift');


		$no=0;$ttlabs=[];
		foreach($arrkary as $val){
			$no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td align='center'>".$val['nik']."</td>
					<td>".$val['namakaryawan']."</td>
					<td>".getJabatanKaryawan($val['karyawanid'])."</td>";

					foreach($rangetgl as $tgl){				
                        
                        if($jamshift[$val['karyawanid']][$tgl]['namashift'] == ''){
                            $tab.="<td align='center' bg colspan=5 ".$style." >Minggu/Tidak ada shift</td>";

                        }else{
                            $tab.="<td align='center' ".$style." >[".$jamshift[$val['karyawanid']][$tgl]['ke']."] <br> ".$optnamashfit[$jamshift[$val['karyawanid']][$tgl]['namashift']]."</td>";
                            $tab.="<td align='center' ".$style." >".$jamshift[$val['karyawanid']][$tgl]['masuk']."</td>";
                            $tab.="<td align='center' ".$style." >".$jamshift[$val['karyawanid']][$tgl]['outist']."</td>";
                            $tab.="<td align='center' ".$style." >".$jamshift[$val['karyawanid']][$tgl]['inist']."</td>";
                            $tab.="<td align='center' ".$style." >".$jamshift[$val['karyawanid']][$tgl]['pulang']."</td>";
                            $tab.="<td align='center' ".$style." >".$jamshift[$val['karyawanid']][$tgl]['batasawal']."</td>";
                        }
						
						
					}
		}
		
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_ShiftKaryawan_".$unit."_".$periode;
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
}


?>