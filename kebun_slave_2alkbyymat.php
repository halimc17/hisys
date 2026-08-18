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
	$where=" and substr(a.kodeblok,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
	$whereJ=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
if($kdorg!=''){
	$where=" and a.kodeblok like '".$kdorg."%'";
	$whereJ=" and a.kodeorg ='".$kdorg."'";
}
if($divisi!=''){
	$where.=" and a.kodeblok like '".$divisi."%'";
	$whereJ.=" and a.kodeblok like '".$divisi."%'";
}
if($blok!=''){
	$where.=" and a.kodeblok like '".$blok."%'";
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
	$wh.=" and a.kodekegiatan like '".$keg."%'";
}

$arrdata=array();
#fisik dan biaya
$str = "select kodebarang,noakun, sum(jumlah) as jumlah,sum(hartot) as hartot, substr(a.kodeblok,1,6) as divisi   
from " . $dbname . ".log_transaksi_vw a 
left join  " . $dbname . ".setup_kegiatan b on a.kodekegiatan=b.kodekegiatan  
where 1=1 ".$wh." ".$where." and tanggal between '".$tglawal."' and  '".$tglakhir."' and post='1'    
group by noakun,kodebarang,divisi order by divisi,noakun,kodebarang"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrdata[$bar['noakun']][$bar['kodebarang']]=$bar['kodebarang'];
	@$pres[$bar['noakun']][$bar['kodebarang']]['unit'][$bar['divisi']]=$bar['jumlah'];
	@$rupiah[$bar['noakun']][$bar['kodebarang']]['biaya'][$bar['divisi']]=$bar['hartot'];
	$div[$bar['divisi']]=$bar['divisi'];
}

/* #biaya
$str = "select a.kodebarang as kdbrngjurnal,b.kodebarang as kdbrngbkm ,noakun, 
		sum(jumlah) as jumlah, substr(a.kodeblok,1,6) as divisi   
from " . $dbname . ".keu_jurnaldt_vw a  
left join  " . $dbname . ".kebun_pakaimaterial b on a.noreferensi=b.notransaksi    
where 1=1 ".$wh." ".$whereJ." and tanggal between '".$tglawal."' and  '".$tglakhir."' and kodejurnal like '%INV%'   
group by a.kodebarang,noakun,divisi order by divisi,noakun,a.kodebarang"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['kdbrngjurnal']!=''){
		$arrdata[$bar['noakun']][$bar['kdbrngjurnal']]=$bar['kdbrngjurnal'];
		@$rupiah[$bar['noakun']][$bar['kdbrngjurnal']]['biaya'][$bar['divisi']]=$bar['jumlah'];
	}else{
		$arrdata[$bar['noakun']][$bar['kdbrngbkm']]=$bar['kdbrngbkm'];
		@$rupiah[$bar['noakun']][$bar['kdbrngbkm']]['biaya'][$bar['divisi']]=$bar['jumlah'];
	}
	$div[$bar['divisi']]=$bar['divisi'];
	
} */

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
            <th align=center rowspan='2' width=50px>".$_SESSION['lang']['noakun']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['namaakun']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['kodebarang']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['namabarang']."</th>";

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

foreach($arrdata as $kdakun => $valbrg){
	foreach($valbrg as $kdbrg){
	$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
		$no++;
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center>".$kdakun."</td>";
		$tab.="<td>".$nmakun[$kdakun]."</td>";
		$tab.="<td align=center>".$kdbrg."</td>";
		$tab.="<td>".$nmbrg[$kdbrg]."</td>";
		foreach($arrtipe as $tipe => $valtipe){
			foreach($div as $kddiv){
				if($tipe=='unit'){
					$tab.="<td align=right>".@hidezerodecimal($pres[$kdakun][$kdbrg][$tipe][$kddiv],2)."</td>";
					@$gtpres['unit'][$kddiv]+=$pres[$kdakun][$kdbrg][$tipe][$kddiv];
					@$ttlpres[$kdakun][$kdbrg]['unit']+=$pres[$kdakun][$kdbrg][$tipe][$kddiv];
				}
				if($tipe=='biaya'){
					$tab.="<td align=right>".@hidezerodecimal($rupiah[$kdakun][$kdbrg][$tipe][$kddiv])."</td>";
					@$gtrp['biaya'][$kddiv]+=$rupiah[$kdakun][$kdbrg][$tipe][$kddiv];
					@$ttlrp[$kdakun][$kdbrg]['biaya']+=$rupiah[$kdakun][$kdbrg][$tipe][$kddiv];
				}
			}
			if($tipe=='unit'){
				$tab.="<td align=right>".@hidezerodecimal($ttlpres[$kdakun][$kdbrg]['unit'],2)."</td>";
			}
			if($tipe=='biaya'){
				$tab.="<td align=right>".@hidezerodecimal($ttlrp[$kdakun][$kdbrg]['biaya'])."</td>";
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
			$tab.="<td align=right></td>";
			@$gtpr['unit']+=$gtpres['unit'][$kddiv];
		}
		if($tipe=='biaya'){
			$tab.="<td align=right>".@hidezerodecimal($gtrp['biaya'][$kddiv])."</td>";
			@$gttrp['biaya']+=$gtrp['biaya'][$kddiv];
		}
	}
	if($tipe=='unit'){
		$tab.="<td align=right></td>";
	}
	if($tipe=='biaya'){
		$tab.="<td align=right>".@hidezerodecimal($gttrp['biaya'])."</td>";
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