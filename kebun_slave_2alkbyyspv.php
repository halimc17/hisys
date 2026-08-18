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
#if($sts==''){exit("warning : Status harus di pilih !!!");}

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
	$wh.="  kodejurnal like '%KBNL0%'";
	$group=" and jurnalid like 'KBNL0%'";
}elseif($sts=='TBM'){
	$wh.="  kodejurnal like '%KBNL1%'";
	$group=" and jurnalid like 'KBNL1%'";
}elseif($sts=='TM'){
	#$wh.="  kodejurnal like '%KBNL2%'";
	$wh.="  kodejurnal in ('KBNL2','KBNL3')";
	$group=" and jurnalid in ('KBNL2','KBNL3')";
}elseif($sts=='PNN'){
	$wh.="  kodejurnal like '%KBNL3%'";
	$group=" and jurnalid like 'KBNL3%'";
}else{
	$wh.="  kodejurnal like '%KBNL%'";
	$group=" and jurnalid like 'KBNL%'";
}

/* if($klbyy!=''){
	$wh.=" and noakun like '".$klbyy."%'";
}
if($akun!=''){
	$wh.=" and noakun like '".$akun."%'";
}
if($keg!=''){
	$wh.=" and a.kodekegiatan like '".$keg."%'";
} */

$str2="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal  where 1=1 ".$group."";
$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_ASSOC);
while ($bar2 = $res2->fetch()) {
	$noakunin[$bar2['noakundebet']]=$bar2['noakundebet'];
}

$arrdata=array();
#biaya
$str = "select kodeorg,noakun, sum(jumlah) as jumlah, substr(a.kodeblok,1,6) as divisi   
from " . $dbname . ".keu_jurnaldt_vw a   
where 1=1 ".$whereJ." and tanggal between '".$tglawal."' and  '".$tglakhir."' 
and (".$wh."  or noakun in ('".implode("','",$noakunin)."')) and substr(a.noakun,1,3) in ('611','621','128','126')      
group by noakun,divisi order by divisi,noakun"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['divisi']==''){
		$bar['divisi']=$bar['kodeorg'];
	}
	$arrdata[$bar['noakun']]=$bar['noakun'];
	@$rupiah[$bar['noakun']][$bar['divisi']]=$bar['jumlah'];
	$div[$bar['divisi']]=$bar['divisi'];
}



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
            <th align=center rowspan='2'>".$_SESSION['lang']['namaakun']."</th>";
			$tab.="<th align=center colspan=".(count($div)+1).">Rupiah</th>";
	$tab.="</tr>";
	$tab.="<tr>";
		foreach($div as $kddiv){
			if(strlen($kddiv)<6){
				$kdddiv=$kddiv;
			}else{
				$kdddiv=substr($kddiv,-2);
			}
			$tab.="<th align=center>".$kddiv."</th>";
		}
		$tab.="<th align=center>".$_SESSION['lang']['total']."</th>";
	$tab.="</tr>";
	$tab.="</thead>
 <tbody>";
$no=0;
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');

foreach($arrdata as $kdakun){
	$no++;
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>".$no."</td>";
	$tab.="<td align=center>".$kdakun."</td>";
	$tab.="<td>".$nmakun[$kdakun]."</td>";
	foreach($div as $kddiv){
		$tab.="<td align=right>".@hidezerodecimal($rupiah[$kdakun][$kddiv])."</td>";
		@$ttlrp[$kdakun]+=$rupiah[$kdakun][$kddiv];
		@$gtrp[$kddiv]+=$rupiah[$kdakun][$kddiv];
	}
	$tab.="<td align=right>".@hidezerodecimal($ttlrp[$kdakun])."</td>";
}

$tab.="</tr>";
$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
$tab.="<td></td>";
$tab.="<td align=center colspan=2><i>Grand Total</i></td>";
foreach($div as $kddiv){
	$tab.="<td align=right>".@hidezerodecimal($gtrp[$kddiv])."</td>";
	@$ttl+=$gtrp[$kddiv];
}
$tab.="<td align=right>".@hidezerodecimal($ttl)."</td>";

$tab.="</tbody></table>";

	
switch ($proses) {
######PREVIEW
    case 'preview':
		if($pt==''){exit("warning : Kode PT harus di pilih !!!");}
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