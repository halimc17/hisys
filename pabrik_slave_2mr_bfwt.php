<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

$nmkode=makeOption($dbname,'pabrik_5mr_bfwt','kode,nama');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='312'");
$satbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kelompokbarang='312'");

$arrtgl=rangeTanggalarr($tgl1,$tgl2);
$spantgl=count($arrtgl);

$str="select * from ".$dbname.".pabrik_5mr_bfwt";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$arrkode[$bar['kode']]=$bar['kode'];
}
$spankode=count($arrkode);

$str="select * from ".$dbname.".pabrik_mr_bfwt where 
		tanggal between '".$tgl1."' and '".$tgl2."' and unit='".$unit."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($tipe=='PA'){
		$arrkode[$bar['kode']]=$bar['kode'];
	}
	$nilai[$bar['tanggal']][$bar['kode']]=$bar['nilai'];
}

$str="select * from ".$dbname.".log_transaksi_vw where untukunit='".$unit."' and 
		kodeblok like '".$unit."14%' and tanggal between '".$tgl1."' and '".$tgl2."' 
		and kodebarang like '312%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$arrkdbrg[$bar['kodebarang']]=$bar['kodebarang'];
	@$jumbrg[$bar['tanggal']][$bar['kodebarang']]+=$bar['jumlah'];
}
$spanbrg=count($arrkdbrg);

$stream="";

//  style=width:300%
$stream.="<table cellspacing=1 class=sortable cellpadding=1 border=0>";
$stream.="<thead>";

$stream.="<tr>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>";
	$stream.="<td align=center colspan='".$spankode."' align=center>".$_SESSION['lang']['kodeparameter']."</td>";
	$stream.="<td align=center colspan='".($spanbrg+1)."' align=center>REGENERASI</td>";
$stream.="</tr>";

$stream.="<tr>";
	foreach($arrkode as $kode){
		$stream.="<td align=center align=center>".$nmkode[$kode]."</td>";
	}
	foreach($arrkdbrg as $brg){
		$stream.="<td align=center align=center>".$nmbrg[$brg]."<br>(".$satbrg[$brg].")</td>";
	}
	$stream.="<td align=center align=center>Volume Air<br>(m3)</td>";
$stream.="</tr>";
$stream.="</thead>";

########################################################################################
#############tampilkan data
########################################################################################

foreach($arrtgl as $tgl){
	$stream.="<tr class=rowcontent>";	
	$stream.="<td align=center>".intval(substr(tanggalnormal($tgl),0,2))."</td>";
	foreach($arrkode as $kode){
		$stream.="<td align=right>".number_format($nilai[$tgl][$kode],2)."</td>";
	}
	foreach($arrkdbrg as $brg){
		$stream.="<td align=center align=right>".number_format($jumbrg[$tgl][$brg],2)."</td>";
	}
	$stream.="<td align=center align=right>".number_format($nilai[$tgl][''],2)."</td>";
	$stream.="</tr>";	
}
$stream.="</table>";
$stream.="<tbody></table>";
switch($proses)
{
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="WATER_TREATMENT_PLANT".$unit;
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }     
        break;	
}
?>