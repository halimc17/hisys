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
$prd           = checkPostGet('prd', '');
$prd2           = checkPostGet('prd2', '');
$blok           = checkPostGet('blok', '');


$tglawal=$prd."-01";
$tglakhir=tglakhir($prd2);


if($pt==''){exit("warning : Kode PT harus di pilih !!!");}
if($sts==''){exit("warning : Status harus di pilih !!!");}

$where='';
$whereJ='';
if($pt!=''){
	$where=" and substr(a.kodeblok,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
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
}
/* elseif($sts=='PNN'){
	$wh.=" and substr(noakun,1,3) in ('611')";
} */
else{
	$wh.=" and substr(noakun,1,3) in ('611','621','126','128')";
}

if($klbyy!=''){
	$wh.=" and noakun like '".$klbyy."%'";
}
if($akun!=''){
	$wh.=" and noakun like '".$akun."%'";
}
if($keg!=''){
	$wh.=" and kodekegiatan like '".$keg."%'";
}

#ini tidak di pakai, dulu kenapa kodejurnal di patok saya juga sudah lupa, jadi gak usah ditanya kenapa ?
#sekarang saya ingat, ini di patok karena yang di ambil cuma Biaya HK dan ini harus sama dengan di lap justifikasi
$whr="and (kodejurnal in ('M0','PNN01','PNN02','CT01','BM01','SPK1','M','NOTAD','NOTAK','HPP','TGH01','BM','BK','KM','KK','POT') 
or kodejurnal like 'PRJ%')";

$arrdata=array();
$str = "select kodeorg,noakun, sum(jumlah) as jumlah, substr(a.kodeblok,1,6) as divisi   
from " . $dbname . ".keu_jurnaldt_vw a   
where 1=1 ".$whereJ." ".$whr." and tanggal between '".$tglawal."' and  '".$tglakhir."' ".$wh."     
group by noakun,divisi order by noakun,divisi"; 
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

$arrtipe=array('hk'=>'HK','unit'=>'Unit','uph'=>'Unit/HK');

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
            <th align=center rowspan='2'>".$_SESSION['lang']['noakun']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['namaakun']."</th>";
			$tab.="<th align=center colspan=".count($div).">Rupiah</th>";
			$tab.="<th align=center rowspan=2>Total</th>";
		
	$tab.="</tr>";
	$tab.="<tr>";
		foreach($div as $kddiv){
			$tab.="<th align=center>".$kddiv."</th>";
		}
	$tab.="</tr>";
	$tab.="</thead>
 <tbody>";
$no=0;
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
foreach($arrdata as $kdakun => $valkeg){
	$no++;
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>".$no."</td>";
	$tab.="<td align=center>".$kdakun."</td>";
	$tab.="<td>".$nmakun[$kdakun]."</td>";
	foreach($div as $kddiv){
		$tab.="<td align=right>".@hidezerodecimal($rupiah[$kdakun][$kddiv])."</td>";
		@$sttl[$kdakun]+=$rupiah[$kdakun][$kddiv];
		@$gttld[$kddiv]+=$rupiah[$kdakun][$kddiv];
		@$gttla+=$rupiah[$kdakun][$kddiv];
	}
	$tab.="<td align=right>".@hidezerodecimal($sttl[$kdakun])."</td>";
}
$tab.="</tr>";
$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
$tab.="<td align=center colspan=3>T O T A L</td>";		
foreach($div as $kddiv){
	$tab.="<td align=right>".@hidezerodecimal($gttld[$kddiv],2)."</td>";
}
$tab.="<td align=right>".@hidezerodecimal($gttla,2)."</td>";
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