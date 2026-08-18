<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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
$prd           = checkPostGet('prd', '');
$prd2           = checkPostGet('prd2', '');


$tglawal=$prd."-01";
$tglakhir=tglakhir($prd2);


if($pt==''){exit("warning : Kode PT harus di pilih !!!");}
if($sts==''){exit("warning : Status harus di pilih !!!");}

$where='';
$wherepnn='';
if($pt!=''){
	$where=" and b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
	$wherepnn=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
if($kdorg!=''){
	$where=" and b.kodeorg ='".$kdorg."'";
	$wherepnn=" and a.kodeorg ='".$kdorg."'";
}
if($divisi!=''){
	$where.=" and a.kodeorg like '".$divisi."%'";
	$wherepnn.=" and a.divisi like '".$divisi."%'";
}

$wh="";
if($sts=='BBT'){
	$wh.=" and substr(kodekegiatan,1,3) in ('128')";
}elseif($sts=='TBM'){
	$wh.=" and substr(kodekegiatan,1,3) in ('126')";
}elseif($sts=='TM'){
	$wh.=" and substr(kodekegiatan,1,3) in ('621','611')";
}elseif($sts=='PNN'){
	$wh.=" and substr(kodekegiatan,1,3) in ('611')";
}
if($klbyy!=''){
	$wh.=" and kodekegiatan like '".$klbyy."%'";
}
if($akun!=''){
	$wh.=" and kodekegiatan like '".$akun."%'";
}
if($keg!=''){
	$wh.=" and kodekegiatan like '".$keg."%'";
}

$arrdata=array();
#khusus tbm, tm dan bbt
$str = "select substr(kodekegiatan,1,3) as status,substr(kodekegiatan,1,5) as klbiaya, 
		substr(kodekegiatan,1,7) as noakun,kodekegiatan,sum(jumlahhk) as hk_b,  
		sum(hasilkerja) as unit_b, substr(a.kodeorg,1,6) as divisi,b.kodeorg  
from " . $dbname . ".kebun_prestasi a  
left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
where 1=1 ".$wh." ".$where." and tanggal between '".$tglawal."' and  '".$tglakhir."' 
and a.notransaksi not like '%PNN%' 
group by substr(kodekegiatan,1,3),substr(kodekegiatan,1,5),substr(kodekegiatan,1,7), 
kodekegiatan,substr(a.kodeorg,1,6) order by divisi,kodekegiatan";
// exit('warning: '.$str); 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrdata[$bar['klbiaya']][$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	@$hk[$bar['klbiaya']][$bar['noakun']][$bar['kodekegiatan']]['hk_b'][$bar['divisi']]=$bar['hk_b'];
	@$pres[$bar['klbiaya']][$bar['noakun']][$bar['kodekegiatan']]['unit_b'][$bar['divisi']]=$bar['unit_b'];
	$div[$bar['divisi']]=$bar['divisi'];
	
}


if($sts=='TM'){
	#khusus panen
	$str = "select kodeorg,divisi,
			sum(hkbuahbesar) as hk_b, sum(hkbuahkecil) as hk_k,
			sum(jjgbuahbesar) as unit_b, sum(jjgbuahkecil) as unit_k 
		from " . $dbname . ".kebun_3premipemanen a  
		where 1=1 ".$wherepnn." and periode ='".substr($tglawal,0,7)."' group by divisi order by divisi"; 
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$arrdata['61101']['6110101']['611010101']='611010101';
		@$hk['61101']['6110101']['611010101']['hk_b'][$bar['divisi']]=$bar['hk_b'];
		@$hk['61101']['6110101']['611010101']['hk_k'][$bar['divisi']]=$bar['hk_k'];
		@$pres['61101']['6110101']['611010101']['unit_b'][$bar['divisi']]=$bar['unit_b'];
		@$pres['61101']['6110101']['611010101']['unit_k'][$bar['divisi']]=$bar['unit_k'];
		$div[$bar['divisi']]=$bar['divisi'];
	}

	#khusus bm tbs
	$str = "select kodeorg,divisi,substr(kegiatan,1,3) as status,substr(kegiatan,1,5) as klbiaya, 
			substr(kegiatan,1,7) as noakun,kegiatan,sum(hk) as hk_b, sum(kgwb) as unit_b from " . $dbname . ".kebun_3premibmtbs a  
	where 1=1 ".$wherepnn." and periode ='".substr($tglawal,0,7)."' group by divisi,kegiatan order by divisi"; 
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$arrdata[$bar['klbiaya']][$bar['noakun']][$bar['kegiatan']]=$bar['kegiatan'];
		@$hk[$bar['klbiaya']][$bar['noakun']][$bar['kegiatan']]['hk_b'][$bar['divisi']]=$bar['hk_b'];
		@$pres[$bar['klbiaya']][$bar['noakun']][$bar['kegiatan']]['unit_b'][$bar['divisi']]=$bar['unit_b'];
		$div[$bar['divisi']]=$bar['divisi'];
	}
}

$arrtipe=array(
	'hk_b'=>'HK Besar',
	'hk_k'=>'HK Kecil',
	'unit_b'=>'Unit Besar',
	'unit_k'=>'Unit Kecil',
	'uph'=>'Unit/HK'
);

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
            <th align=center rowspan='2'>".$_SESSION['lang']['namaakun']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['kegiatan']."</th>";

		foreach($arrtipe as $tipe => $valtipe){
			$tab.="<th align=center colspan=".(count($div)+1).">".$valtipe."</th>";
		}
	$tab.="</tr>";
	$tab.="<tr>";
		foreach($arrtipe as $tipe => $valtipe){
			foreach($div as $kddiv){
				$tab.="<th align=center>".substr($kddiv,-2)."</th>";
			}
			$tab.="<th align=center>".$_SESSION['lang']['total']."</th>";
		}
	$tab.="</tr>";
	$tab.="</thead>
 <tbody>";
$no=0;
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$ttlhk=array();
$ttlpres=array();
foreach($arrdata as $klbiaya => $valakun){
	foreach($valakun as $kdakun => $valkeg){
		foreach($valkeg as $kdkeg){
		$ttlrhk=array();
		$ttlrpres=array();
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$kdakun." - ".$nmakun[$kdakun]."</td>";
			$tab.="<td>".$nmkeg[$kdkeg]."</td>";
			#$tab.="<td>".$kdkeg." - ".$nmkeg[$kdkeg]."</td>";
			foreach($arrtipe as $tipe => $valtipe){
				foreach($div as $kddiv){
					$v_hk = @$hk[$klbiaya][$kdakun][$kdkeg]['hk_b'][$kddiv] + @$hk[$klbiaya][$kdakun][$kdkeg]['hk_k'][$kddiv];
					$v_unit = @$pres[$klbiaya][$kdakun][$kdkeg]['unit_b'][$kddiv] + @$pres[$klbiaya][$kdakun][$kdkeg]['unit_k'][$kddiv];
					@$uperhk = ($v_hk > 0) ? $v_unit / $v_hk : 0;
					
					if($tipe=='hk_b' || $tipe=='hk_k'){
						$tab.="<td align=right>".@hidezerodecimal($hk[$klbiaya][$kdakun][$kdkeg][$tipe][$kddiv],2)."</td>";
						@$ttlhk[$tipe][$kddiv]+=$hk[$klbiaya][$kdakun][$kdkeg][$tipe][$kddiv];
						@$ttlrhk[$tipe]+=$hk[$klbiaya][$kdakun][$kdkeg][$tipe][$kddiv];
					}
					if($tipe=='unit_b' || $tipe=='unit_k'){
						$tab.="<td align=right>".@hidezerodecimal($pres[$klbiaya][$kdakun][$kdkeg][$tipe][$kddiv],2)."</td>";
						@$ttlpres[$tipe][$kddiv]+=$pres[$klbiaya][$kdakun][$kdkeg][$tipe][$kddiv];
						@$ttlrpres[$tipe]+=$pres[$klbiaya][$kdakun][$kdkeg][$tipe][$kddiv];
					}
					if($tipe=='uph'){
						$tab.="<td align=right>".@hidezerodecimal($uperhk,2)."</td>";
					}
				}
				if($tipe=='hk_b' || $tipe=='hk_k'){
					$tab.="<td align=right style=background-color:#DCFEDA>".@hidezerodecimal($ttlrhk[$tipe],2)."</td>";
				}
				if($tipe=='unit_b' || $tipe=='unit_k'){
					$tab.="<td align=right style=background-color:#DCFEDA>".@hidezerodecimal($ttlrpres[$tipe])."</td>";
				}
				
				$vr_hk = @$ttlrhk['hk_b'] + @$ttlrhk['hk_k'];
				$vr_unit = @$ttlrpres['unit_b'] + @$ttlrpres['unit_k'];
				@$uperhkr = ($vr_hk > 0) ? $vr_unit / $vr_hk : 0;
				if($tipe=='uph'){
					$tab.="<td align=right style=background-color:#DCFEDA>".@hidezerodecimal($uperhkr,2)."</td>";
				}
			}
		}
	}
}
$tab.="</tr>";
$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
$tab.="<td align=center colspan=3>T O T A L</td>";
$gttlrhk=array();
$gttlrpres=array();

foreach($arrtipe as $tipe => $valtipe){
	foreach($div as $kddiv){
		if($tipe=='hk_b' || $tipe=='hk_k'){
			$tab.="<td align=right>".@hidezerodecimal($ttlhk[$tipe][$kddiv],2)."</td>";
			@$gttlrhk[$tipe]+=$ttlhk[$tipe][$kddiv];
		}
		if($tipe=='unit_b' || $tipe=='unit_k'){
			$tab.="<td align=right>".@hidezerodecimal($ttlpres[$tipe][$kddiv],2)."</td>";
			@$gttlrpres[$tipe]+=$ttlpres[$tipe][$kddiv];
		}
		
		$vt_hk = @$ttlhk['hk_b'][$kddiv] + @$ttlhk['hk_k'][$kddiv];
		$vt_unit = @$ttlpres['unit_b'][$kddiv] + @$ttlpres['unit_k'][$kddiv];
		@$ttluperhk = ($vt_hk > 0) ? $vt_unit / $vt_hk : 0;
		if($tipe=='uph'){
			$tab.="<td align=right>".@hidezerodecimal($ttluperhk,2)."</td>";
		}
	}
	if($tipe=='hk_b' || $tipe=='hk_k'){
		$tab.="<td align=right style=background-color:#DCFEDA>".@hidezerodecimal($gttlrhk[$tipe],2)."</td>";
	}
	if($tipe=='unit_b' || $tipe=='unit_k'){
		$tab.="<td align=right style=background-color:#DCFEDA>".@hidezerodecimal($gttlrpres[$tipe])."</td>";
	}
	
	$vgr_hk = @$gttlrhk['hk_b'] + @$gttlrhk['hk_k'];
	$vgr_unit = @$gttlrpres['unit_b'] + @$gttlrpres['unit_k'];
	@$guperhkr = ($vgr_hk > 0) ? $vgr_unit / $vgr_hk : 0;
	if($tipe=='uph'){
		$tab.="<td align=right style=background-color:#DCFEDA>".@hidezerodecimal($guperhkr,2)."</td>";
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
        $nop_ = strtolower(getMenu('kebun_2alkpakaihk'));
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