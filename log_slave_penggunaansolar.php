<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses  = checkPostGet('proses', '');
$kdorg   = checkPostGet('kdorg', '');
$prd     = checkPostGet('prd', '');
$tipe    = checkPostGet('tipe', '');
$tipex    = checkPostGet('tipe', '');
$prdsd    = checkPostGet('prdsd', '');
$gudang    = checkPostGet('gudang', '');
$tipetran    = checkPostGet('tipetran', '');
$kegiatan    = checkPostGet('kegiatan', '');
$nopol    = checkPostGet('nopol', '');

$masuk=array(
	'1'=>'Dari PO',
	'2'=>'Retur',
	'3'=>'Mutasi',
	'4'=>'Adjust'
);
$keluar=array(
	'5'=>'Pakai',
	'6'=>'Retur',
	'7'=>'Mutasi',
	'8'=>'Adjust'
);

$tipetrans['masuk']=$masuk;
$tipetrans['keluar']=$keluar;

switch($tipe){
	case'detail2':
		ini_set('display_errors',0);
		error_reporting(0);



		$where='';
		$where.=" and kodegudang='".$gudang."'";
		if($kegiatan!=''){			
			$where.=" and kodekegiatan='".$kegiatan."'";
		}
		if($nopol!=''){			
			$where.=" and kodemesin='".$nopol."'";
		}
		$where.=" and tipetransaksi	='".$tipetran."'";
		
		#khusus solar
		$where.=" and kodebarang='351010003'";
		
		$tab.= "<label>Gudang: ".getNamaOrg($gudang)."</label><br>";
		$tab.= "<label>Periode: ".$prd." s/d ".$prdsd."</label><br>";
		$tab.= "<label>Tipe: ".($keluar[$tipetran]?"Keluar ".$keluar[$tipetran]:"Masuk ".$masuk[$tipetran])."</label></br>";
		if($kegiatan!=''){			
			$tab.= "<label>Alokasi: ".getNamaKeg($kegiatan)."</label><br>";
		}
		if($nopol!=''){			
			if(getNopol($nopol,'nopol')!=''){
				$tab.="<label>Nopol: ".getNopol($nopol)." / ".getNopol($nopol,'nopol')."</label>";							
			}else{
				$tab.="<label>Nopol: ".getNopol($nopol)."</label>";	
			}
		}
		
		
		$str = "select * from " . $dbname . ".vhc_kegiatan"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$namakeg[$bar['kodekegiatan']]=$bar['namakegiatan'];
		}
		
		if ($proses == 'excel') {
			$tab.="<table>";
		} else {
			$tab.= "<table>";
		}
		$tab.="
			<tr>
				<td align=center style=background-color:gray>Transaksi Gudang</td>
				<td align=center style=background-color:gray>Rekap Keg Traksi</td>
			</tr>
			<tr>
				<td align=center style=vertical-align:top>
			";
			

			if ($proses == 'excel') {
				$tab.="<table class=sortable cellspacing=1 border=1>";
			} else {
				$tab.= "<table class=sortable cellpadding=5 cellspacing=1>";
			}
			$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center>".$_SESSION['lang']['notransaksi']."</th>
					<th align=center>".$_SESSION['lang']['tanggal']."</th>
					<th align=center>".$_SESSION['lang']['nopo']."</th>
					<th align=center>".$_SESSION['lang']['supplier']."</th>
					<th align=center>".$_SESSION['lang']['tujuan']."/".$_SESSION['lang']['sumber']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['kegiatan']."</th>
					";
				$tab.="</tr>";
			$tab.="</thead>
			<tbody>";
			
			$totalltr=[];
			$str = "select * from " . $dbname . ".log_transaksi_vw a  where 1=1 ".$where." and substr(tanggal,1,7) between '".$prd."' and '".$prdsd."' and post='1' order by tanggal desc";
			$res = fetchdata($str);
			foreach($res as $bar){
				$no++;
				$tab.="</tr>";
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['notransaksi']."</td>";
				$tab.="<td>".$bar['tanggal']."</td>";
				$tab.="<td>".$bar['nopo']."</td>";
				if(getNamaSupplier($bar['idsupplier'])!=''){					
					$tab.="<td>".getNamaSupplier($bar['idsupplier'])."</td>";
				}else{					
					$tab.="<td>".getNamaSupplier($bar['kodeblok'])."</td>";
				}
				if(getNamaOrg($bar['gudangx'])!=''){					
					$tab.="<td>".getNamaOrg($bar['gudangx'])."</td>";
				}else{
					$tab.="<td>".getNamaOrg($bar['kodeblok'])."</td>";
				}
				$tab.="<td align=right>".hidezerodecimal($bar['jumlah'],2)."</td>";
				$tab.="<td></td>";
				$tab.="</tr>";
				$ttl+=$bar['jumlah'];
				
				$totalltr[substr($bar['tanggal'],0,7)]+=$bar['jumlah'];
			}
			$tab.="<tr class=rowcontent style=background-color:#efeff2;font-weight:bold>";
			$tab.="<td colspan=6 align=center>TOTAL</td>";
			$tab.="<td align=right>".hidezerodecimal($ttl,2)."</td>";
			$tab.="<td></td>";
			$tab.="</tr>";
			
			$tab.="</tbody></table>";
			
			
		$tab.="</td>";
		$tab.="<td align=center style=vertical-align:top>";
		
		
			if ($proses == 'excel') {
				$tab.="<table class=sortable cellspacing=1 border=1>";
			} else {
				$tab.= "<table class=sortable cellpadding=5 cellspacing=1>";
			}
			$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center>".$_SESSION['lang']['periode']."</th>
					<th align=center>Liter</th>
					<th align=center>Jlh HM or KM</th>
					<th align=center>LTR/HM</th>
					<th align=center>KM/LTR</th>
					";
				$tab.="</tr>";
			$tab.="</thead>
			<tbody>";
			
			$no=0;
			$sql = "select sum(jumlah) as jumlah, substr(tanggal,1,7) as prd from " . $dbname . ".vhc_rundt_vw a  where substr(tanggal,1,7) between '".$prd."' and '".$prdsd."' and kodevhc='".$nopol."' group by substr(tanggal,1,7) order by substr(tanggal,1,7) desc";
			$req = fetchdata($sql);
			foreach($req as $bar){
				$no++;
				$tab.="</tr>";
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['prd']."</td>";
				$tab.="<td align=right>".hidezerodecimal($totalltr[$bar['prd']],2)."</td>";
				$tab.="<td align=right>".hidezerodecimal($bar['jumlah'],2)."</td>";
				$tab.="<td align=right>".hidezerodecimal(@$totalltr[$bar['prd']]/@$bar['jumlah'],2)."</td>";
				$tab.="<td align=right>".hidezerodecimal(fixnan(@$bar['jumlah']/@$totalltr[$bar['prd']]),2)."</td>";
				$tab.="</tr>";
				$ttlhm+=$bar['jumlah'];
				$ttllt+=$totalltr[$bar['prd']];
			}
			
			$tab.="<tr class=rowcontent style=background-color:#efeff2;font-weight:bold>";
			$tab.="<td colspan=2 align=center>TOTAL</td>";
			$tab.="<td align=right>".hidezerodecimal($ttllt,2)."</td>";
			$tab.="<td align=right>".hidezerodecimal($ttlhm,2)."</td>";
			$tab.="<td align=right>".hidezerodecimal($ttllt/$ttlhm,2)."</td>";
			$tab.="<td align=right>".hidezerodecimal($ttlhm/$ttllt,2)."</td>";
			$tab.="</tr>";
			
			$tab.="</tbody></table>";
		
		$tab.="</td>";
		$tab.="</tr>";
		$tab.="</table>";
	break;
	case'detail1':
		$where='';
		$where.=" and kodegudang='".$gudang."'";
		#khusus solar
		$where.=" and kodebarang='351010003'";
		
		$str = "select * from " . $dbname . ".log_5saldobulanan a  where 1=1 ".$where." and periode='".$prd."'"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$sawal+=$bar['saldoawalqty'];
		}
		
		
		$str = "select * from " . $dbname . ".log_transaksi_vw a  where 1=1 ".$where." and substr(tanggal,1,7) between '".$prd."' and '".$prdsd."' and post='1' order by kodemesin asc,kodekegiatan asc, tipetransaksi asc, tanggal asc"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['tipetransaksi']<='4'){
				$keluarmasuk='masuk';
			}else{
				$keluarmasuk='keluar';
			}
			$dt[$bar['kodekegiatan']][$bar['kodemesin']]=$bar['kodemesin'];
			$data[$bar['kodekegiatan']][$bar['kodemesin']][$keluarmasuk][$bar['tipetransaksi']]+=$bar['jumlah'];
		}
		

		$tab.= "<label>Gudang: ".getNamaOrg($gudang)."</label><br>";
		$tab.= "<label>Periode: ".$prd." s/d ".$prdsd."</label>";
		if ($proses == 'excel') {
			$tab.="<table class=sortable cellspacing=1 border=1>";
		} else {
			$tab.= "<table class=sortable cellpadding=5 cellspacing=1>";
		}

		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan='2'>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan='2'>".$_SESSION['lang']['alokasi']."</th>
					<th align=center rowspan='2'>".$_SESSION['lang']['nopol']."</th>
					";
					foreach($tipetrans as $keluarmasuk => $val1){				
						$tab.="<th align=center colspan='".(count($masuk)+1)."'>".$keluarmasuk."</th>";			
						foreach($val1 as $tptrans => $namatrans){							
						}
					}
				$tab.="</tr>";
				$tab.="<tr class=rowheader>";
					foreach($tipetrans as $keluarmasuk => $val1){				
						foreach($val1 as $tptrans => $namatrans){							
							$tab.="<th align=center>".$namatrans."</th>";			
						}
						$tab.="<th align=center>TOTAL</th>";			
					}
				$tab.="</tr>";
			$tab.="</thead>
			<tbody>";
			$u=$i="";
			
			$tab.="<tr class=rowcontent style=background-color:#efeff2;font-weight:bold>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td>".$_SESSION['lang']['saldoawal']."</td>";
			$tab.="<td colspan=10 align=right>".hidezerodecimal($sawal,2)."</td>";
			$tab.="</tr>";
			
			foreach($dt as $kegiatan => $val1){
				foreach($val1 as $nopol){
					$no++;
					$u=$kegiatan;					
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					if($u!=$i){
						$tab.="<td>".getNamaKeg($kegiatan)."</td>";
					}else{
						$tab.="<td></td>";
					}
					if(getNopol($nopol,'nopol')!=''){
						$tab.="<td>".getNopol($nopol)." / ".getNopol($nopol,'nopol')."</td>";							
					}else{
						$tab.="<td>".getNopol($nopol)."</td>";	
					}
					foreach($tipetrans as $keluarmasuk => $val1){				
						foreach($val1 as $tptrans => $namatrans){
							$click="onclick=getdetail2('detail2','".$prd."','".$prdsd."','".$gudang."','".$kegiatan."','".$nopol."','".$tptrans."')";
							$tab.="<td ".$click." align=right style=color:blue;cursor:pointer;>".hidezerodecimal($data[$kegiatan][$nopol][$keluarmasuk][$tptrans],2)."</td>";
							
							$ttlperkeluarmasuk[$kegiatan][$nopol][$keluarmasuk]+=$data[$kegiatan][$nopol][$keluarmasuk][$tptrans];
							$grandttl[$keluarmasuk][$tptrans]+=$data[$kegiatan][$nopol][$keluarmasuk][$tptrans];
						}
						$tab.="<td align=right>".hidezerodecimal($ttlperkeluarmasuk[$kegiatan][$nopol][$keluarmasuk],2)."</td>";						
					}
					$i=$u;
				}
			}
			$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#efeff2>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td>TOTAL</td>";
			foreach($tipetrans as $keluarmasuk => $val1){				
				foreach($val1 as $tptrans => $namatrans){
					$tab.="<td align=right>".hidezerodecimal($grandttl[$keluarmasuk][$tptrans],2)."</td>";		
					$gttlkm[$keluarmasuk]+=$grandttl[$keluarmasuk][$tptrans];
					
					if($keluarmasuk=='keluar'){
						$akhir+=$grandttl[$keluarmasuk][$tptrans]*(-1);
					}else{
						$akhir+=$grandttl[$keluarmasuk][$tptrans];
					}
				}
				$tab.="<td align=right>".hidezerodecimal($gttlkm[$keluarmasuk],2)."</td>";
			}	
			$tab.="</tr>";
			
			$tab.="<tr class=rowcontent style=background-color:#efeff2;font-weight:bold>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td>".$_SESSION['lang']['saldoakhir']."</td>";
			$tab.="<td align=right colspan=10>".hidezerodecimal($sawal+$akhir,2)."</td>";
			$tab.="</tr>";
		$tab.="</tbody></table>";
	break;
	default:
		$where='';
		if($kdorg!=''){
			$where.=" and substr(kodegudang,1,4)='".$kdorg."'";
		}
		#khusus solar
		$where.=" and kodebarang='351010003'";
		
		$str = "select * from " . $dbname . ".log_5saldobulanan a  where 1=1 ".$where." and periode='".$prd."'"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$sawal[$bar['kodeorg']][$bar['kodegudang']]+=$bar['saldoawalqty'];
		}
		
		
		$str = "select * from " . $dbname . ".log_transaksi_vw a  where 1=1 ".$where." and substr(tanggal,1,7) between '".$prd."' and '".$prdsd."' and post='1' order by tipetransaksi, tanggal, kodept,kodegudang"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['tipetransaksi']<='4'){
				$keluarmasuk='masuk';
			}else{
				$keluarmasuk='keluar';
			}
			$dt[$bar['kodept']][$bar['kodegudang']]=$bar['kodegudang'];
			$data[$bar['kodept']][$bar['kodegudang']][$keluarmasuk][$bar['tipetransaksi']]+=$bar['jumlah'];
		}
		
		

		if ($proses == 'excel') {
			$tab.="<table class=sortable cellspacing=1 border=1>";
		} else {
			$tab.= "<table class=sortable cellpadding=5 cellspacing=1>";
		}

		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan='2'>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan='2'>".$_SESSION['lang']['pt']."</th>
					<th align=center rowspan='2'>".$_SESSION['lang']['gudang']."</th>
					<th align=center rowspan='2'>".$_SESSION['lang']['saldoawal']."</th>
					";
					foreach($tipetrans as $keluarmasuk => $val1){				
						$tab.="<th align=center colspan='".(count($masuk)+1)."'>".$keluarmasuk."</th>";			
						foreach($val1 as $tptrans => $namatrans){							
						}
					}
				$tab.="<th align=center rowspan='2'>".$_SESSION['lang']['saldoakhir']."</th>";
				$tab.="</tr>";
				$tab.="<tr class=rowheader>";
					foreach($tipetrans as $keluarmasuk => $val1){				
						foreach($val1 as $tptrans => $namatrans){							
							$tab.="<th align=center>".$namatrans."</th>";			
						}
						$tab.="<th align=center>TOTAL</th>";			
					}
				$tab.="</tr>";
			$tab.="</thead>
			<tbody>";
			$u=$i="";
			foreach($dt as $kodept => $val1){
				foreach($val1 as $gudang){
					$u=$kodept;					
					$tab.="<tr class=rowcontent>";
					if($u!=$i){
						$no++;
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td>".getNamaOrg($kodept)."</td>";
					}else{
						$tab.="<td></td>";
						$tab.="<td></td>";
					}
					$tab.="<td>".getNamaOrg($gudang)."</td>";
					
					$tab.="<td align=right>".hidezerodecimal($sawal[$kodept][$gudang],2)."</td>";
					$ttlsawalperpt[$kodept]+=$sawal[$kodept][$gudang];
					$gtsawal+=$sawal[$kodept][$gudang];
					foreach($tipetrans as $keluarmasuk => $val1){				
						foreach($val1 as $tptrans => $namatrans){
							$click="onclick=getdetail('detail1','".$prd."','".$prdsd."','".$gudang."','".$tptrans."')";
							$tab.="<td ".$click." align=right style=color:blue;cursor:pointer;>".hidezerodecimal($data[$kodept][$gudang][$keluarmasuk][$tptrans],2)."</td>";
							
							$ttlperkeluarmasuk[$kodept][$gudang][$keluarmasuk]+=$data[$kodept][$gudang][$keluarmasuk][$tptrans];
							$ttlperpt[$kodept][$keluarmasuk][$tptrans]+=$data[$kodept][$gudang][$keluarmasuk][$tptrans];
							$ttlperptkm[$kodept][$keluarmasuk]+=$data[$kodept][$gudang][$keluarmasuk][$tptrans];
							$gtotal[$keluarmasuk][$tptrans]+=$data[$kodept][$gudang][$keluarmasuk][$tptrans];

							if($keluarmasuk=='keluar'){
								$akhir[$kodept][$gudang]+=$data[$kodept][$gudang][$keluarmasuk][$tptrans]*(-1);
							}else{
								$akhir[$kodept][$gudang]+=$data[$kodept][$gudang][$keluarmasuk][$tptrans];
							}
						}
						$tab.="<td align=right>".hidezerodecimal($ttlperkeluarmasuk[$kodept][$gudang][$keluarmasuk],2)."</td>";						
					}
					$saldoakhir[$kodept][$gudang]=$sawal[$kodept][$gudang]+$akhir[$kodept][$gudang];
					$tab.="<td align=right>".hidezerodecimal($saldoakhir[$kodept][$gudang],2)."</td>";
					$tab.="</tr>";
					$i=$u;
					$saldoakhirpt[$kodept]+=$saldoakhir[$kodept][$gudang];
				}
				$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#efeff2>";
				$tab.="<td></td>";
				$tab.="<td colspan=2>SUB TOTAL ".getNamaOrg($kodept)."</td>";
				$tab.="<td align=right>".hidezerodecimal($ttlsawalperpt[$kodept],2)."</td>";
				foreach($tipetrans as $keluarmasuk => $val1){				
					foreach($val1 as $tptrans => $namatrans){
						$tab.="<td align=right>".hidezerodecimal($ttlperpt[$kodept][$keluarmasuk][$tptrans],2)."</td>";						
					}
					$tab.="<td align=right>".hidezerodecimal($ttlperptkm[$kodept][$keluarmasuk],2)."</td>";
				}	
				$tab.="<td align=right>".hidezerodecimal($saldoakhirpt[$kodept],2)."</td>";
				$tab.="</tr>";
			}
			
			$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#dadae3>";
			$tab.="<td></td>";
			$tab.="<td colspan=2>GRAND TOTAL</td>";
			$tab.="<td align=right>".hidezerodecimal($gtsawal,2)."</td>";
			foreach($tipetrans as $keluarmasuk => $val1){				
				foreach($val1 as $tptrans => $namatrans){
					$tab.="<td align=right>".hidezerodecimal($gtotal[$keluarmasuk][$tptrans],2)."</td>";						
				}
				$tab.="<td align=right>".hidezerodecimal($ttlperptkm[$kodept][$keluarmasuk],2)."</td>";
			}	
			$tab.="<td align=right>".hidezerodecimal($saldoakhirpt[$kodept],2)."</td>";
			$tab.="</tr>";
			
		$tab.="</tbody></table>";
	
	break;
}

switch ($proses) {
######PREVIEW
    case 'preview':
		$tab.="<br><br>";
		echo $tab;
    break;

######EXCEL	
    case 'excel':
    	if ($tipe == 'summary' || $tipe == 'detail') {
    		$print = $html;
    	} else {
    		$print = $tab;
    	}
        $nop_ = $tipe;
        if (strlen($print) > 0) {
			$print.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $print)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e="";
	}else if(is_infinite($e)){
		$e="";
	}else{
		$e=$e;
	}
	$n = hidezerodecimal($e,$i);
	if($n==0 or $n==''){
		$n='';
	}
	
	return $n;
}



?>