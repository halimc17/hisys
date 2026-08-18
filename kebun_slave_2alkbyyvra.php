<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses        = checkPostGet('proses', '');
$kdorg         = checkPostGet('kdorg', '');
$pt            = checkPostGet('pt', '');
$tt            = checkPostGet('tt', '');
$ip            = checkPostGet('ip', '');
$divisi        = checkPostGet('divisi', '');
$prd           = checkPostGet('prd', '');
$tipe          = checkPostGet('tipe', '');
$tglawal       = tanggalsystemn(checkPostGet('tglawal', ''));
$tglakhir      = tanggalsystemn(checkPostGet('tglakhir', ''));
$klbyy         = checkPostGet('klbyy', '');
$akun          = checkPostGet('akun', '');
$keg           = checkPostGet('keg', '');
$sts           = checkPostGet('sts', '');
$blok           = checkPostGet('blok', '');

if($pt==''){exit("warning : Kode PT harus di pilih !!!");}
if($sts==''){exit("warning : Status harus di pilih !!!");}

$where='';
$whereJ='';
if($pt!=''){
	$where=" and substr(a.alokasibiaya,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
	$whereJ=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
if($kdorg!=''){
	$where=" and a.alokasibiaya like '".$kdorg."%'";
	$whereJ=" and a.kodeorg ='".$kdorg."'";
}
if($divisi!=''){
	$where.=" and a.alokasibiaya like '".$divisi."%'";
	$whereJ.=" and a.kodeblok like '".$divisi."%'";
}
if($blok!=''){
	$where.=" and a.alokasibiaya like '".$blok."%'";
	$whereJ.=" and a.kodeblok like '".$blok."%'";
}

$wh="";
if($sts=='BBT'){
	$wh.=" and substr(noakun,1,3) in ('128')";
}elseif($sts=='TBM'){
	$wh.=" and substr(noakun,1,3) in ('126')";
}elseif($sts=='TM'){
	$wh.=" and substr(noakun,1,3) in ('621','611')";
}elseif($sts=='PNN'){
	$wh.=" and substr(noakun,1,3) in ('611')";
}
if($klbyy!=''){
	$wh.=" and noakun like '".$klbyy."%'";
}
if($akun!=''){
	$wh.=" and noakun like '".$akun."%'";
}
if($keg!=''){
	$wh.=" and noakun like '".$keg."%'";
}

$arrdata=array();
#fisik
$str = "select substr(a.tanggal,1,7) as periode,a.kodevhc,noakun,  
		sum(jumlah) as jumlah, substr(a.alokasibiaya,1,6) as divisi   
from " . $dbname . ".vhc_rundt_vw a  
left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan 
left join " . $dbname . ".vhc_runht c on c.notransaksi=a.notransaksi  
where 1=1 ".$wh." ".$where." and a.tanggal between '".$tglawal."' and  '".$tglakhir."'  and posting='1'  
group by periode,a.kodevhc,noakun,divisi order by divisi,noakun,a.kodevhc"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrdata[$bar['periode']][$bar['noakun']][$bar['kodevhc']]=$bar['kodevhc'];
	@$pres[$bar['periode']][$bar['noakun']][$bar['kodevhc']]['unit'][$bar['divisi']]=$bar['jumlah'];
	$div[$bar['divisi']]=$bar['divisi'];
}

#biaya
$str = "select periode,kodevhc,noakun, sum(jumlah) as jumlah, substr(a.kodeblok,1,6) as divisi   
from " . $dbname . ".keu_jurnaldt_vw a  
where 1=1 ".$wh." ".$whereJ." and tanggal between '".$tglawal."' and  '".$tglakhir."' and kodejurnal like '%VHC%'   
group by periode,kodevhc,noakun,divisi order by divisi,noakun,kodevhc"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrdata[$bar['periode']][$bar['noakun']][$bar['kodevhc']]=$bar['kodevhc'];
	@$rupiah[$bar['periode']][$bar['noakun']][$bar['kodevhc']]['biaya'][$bar['divisi']]=$bar['jumlah'];
	$div[$bar['divisi']]=$bar['divisi'];
}



$arrtipe=array('unit'=>'Qty','biaya'=>'Amount (IDR)');

if(count($arrdata)==0){
	exit("Warning : Data Kosong !!!");
}

if ($proses == 'excel') {
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<table class=sortable cellspacing=1>";
}


$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='2'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['periode']."</th>
            <th align=center rowspan='2' width=50px>".$_SESSION['lang']['noakun']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['namaakun']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['kodevhc']."</th>";

		foreach($arrtipe as $tipe => $valtipe){
			$tab.="<th align=center colspan=".(count($div)+1).">".$valtipe."</th>";
		}
	$tab.="</tr>";
	$tab.="<tr>";
		foreach($arrtipe as $tipe => $valtipe){
			foreach($div as $kddiv){
				if(strlen($kddiv)<6){
					$kdddiv=$kddiv;
				}else{
					$kdddiv=substr($kddiv,-2);
				}
				$tab.="<th align=center>".$kdddiv."</th>";
			}
			$tab.="<th align=center>".$_SESSION['lang']['total']."</th>";
		}
	$tab.="</tr>";
	$tab.="</thead>
 <tbody>";
$no=0;
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');

foreach($arrdata as $period => $valakun){
	foreach($valakun as $kdakun => $valvhc){
		$ttlpres=array();$ttlrp=array();
		foreach($valvhc as $kdvhc){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$period."</td>";
			$tab.="<td>".$kdakun."</td>";
			$tab.="<td>".$nmakun[$kdakun]."</td>";
			$tab.="<td>".$kdvhc."</td>";
			#$tab.="<td>".$kdkeg." - ".$nmkeg[$kdkeg]."</td>";
			foreach($arrtipe as $tipe => $valtipe){
				foreach($div as $kddiv){
					if($tipe=='unit'){
						$tab.="<td align=right>".@hidezerodecimal($pres[$period][$kdakun][$kdvhc][$tipe][$kddiv],2)."</td>";
						@$ttlpres[$period][$kdakun]['unit'][$kddiv]+=$pres[$period][$kdakun][$kdvhc][$tipe][$kddiv];
						@$ttlpresvhc[$period][$kdakun][$kdvhc]['unit']+=$pres[$period][$kdakun][$kdvhc][$tipe][$kddiv];
						@$gtpres['unit'][$kddiv]+=$pres[$period][$kdakun][$kdvhc][$tipe][$kddiv];
					}
					if($tipe=='biaya'){
						$tab.="<td align=right>".@hidezerodecimal($rupiah[$period][$kdakun][$kdvhc][$tipe][$kddiv],2)."</td>";
						@$ttlrp[$period][$kdakun]['biaya'][$kddiv]+=$rupiah[$period][$kdakun][$kdvhc][$tipe][$kddiv];
						@$ttlrpvhc[$period][$kdakun][$kdvhc]['biaya']+=$rupiah[$period][$kdakun][$kdvhc][$tipe][$kddiv];
						@$gtrp['biaya'][$kddiv]+=$rupiah[$period][$kdakun][$kdvhc][$tipe][$kddiv];
					}
				}
				if($tipe=='unit'){
					$tab.="<td align=right>".@hidezerodecimal($ttlpresvhc[$period][$kdakun][$kdvhc]['unit'],2)."</td>";
				}
				if($tipe=='biaya'){
					$tab.="<td align=right>".@hidezerodecimal($ttlrpvhc[$period][$kdakun][$kdvhc]['biaya'],2)."</td>";
				}
			}
		}
		$tab.="</tr>";
		$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td colspan=3><i>Sum ".$nmakun[$kdakun]."</i></td>";
		foreach($arrtipe as $tipe => $valtipe){
			foreach($div as $kddiv){
				if($tipe=='unit'){					
					$tab.="<td align=right>".@hidezerodecimal($ttlpres[$period][$kdakun]['unit'][$kddiv],2)."</td>";
					@$ttlpresakun[$period][$kdakun]['unit']+=$ttlpres[$period][$kdakun]['unit'][$kddiv];
				}
				if($tipe=='biaya'){					
					$tab.="<td align=right>".@hidezerodecimal($ttlrp[$period][$kdakun]['biaya'][$kddiv],2)."</td>";
					@$ttlrpakun[$period][$kdakun]['biaya']+=$ttlrp[$period][$kdakun]['biaya'][$kddiv];
				}
			}
			if($tipe=='unit'){
				$tab.="<td align=right>".@hidezerodecimal($ttlpresakun[$period][$kdakun]['unit'],2)."</td>";
			}
			if($tipe=='biaya'){
				$tab.="<td align=right>".@hidezerodecimal($ttlrpakun[$period][$kdakun]['biaya'],2)."</td>";
			}
		}
	}
}
$tab.="</tr>";
$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
$tab.="<td></td>";
$tab.="<td align=center colspan=4><i>Grand Total</i></td>";
			
foreach($arrtipe as $tipe => $valtipe){
	foreach($div as $kddiv){
		if($tipe=='unit'){
			$tab.="<td align=right>".@hidezerodecimal($gtpres['unit'][$kddiv],2)."</td>";
			@$gtpr['unit']+=$gtpres['unit'][$kddiv];
		}
		if($tipe=='biaya'){
			$tab.="<td align=right>".@hidezerodecimal($gtrp['biaya'][$kddiv],2)."</td>";
			@$gttrp['biaya']+=$gtrp['biaya'][$kddiv];
		}
	}
	if($tipe=='unit'){
		$tab.="<td align=right>".@hidezerodecimal($gtpr['unit'],2)."</td>";
	}
	if($tipe=='biaya'){
		$tab.="<td align=right>".@hidezerodecimal($gttrp['biaya'],2)."</td>";
	}
}





$tab.="</tbody></table>";

	
switch ($proses) {
######PREVIEW
    case 'preview':
        echo $tab;
        break;

######EXCEL	
    case 'excel':
		
        $nop_ = 'excel';
        if (strlen($tab) > 0) {
			$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
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
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}



?>