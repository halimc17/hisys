<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');



$kdorg=checkPostGet('kdorg3','');
$per=checkPostGet('per3','');
$proses=checkPostGet('proses','');

$unit=$kdorg;




$akunsort=" and ( (left(noakun,1)='7' and left(noakun,5)!='71502' ) or (left(noakun,2)='63' and left(noakun,3)!='633') or left(noakun,3)='651' or left(noakun,3)='652')";
$namaakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<fieldset style='float:left'><legend>".$_SESSION['lang']['biaya']."</legend><table class=sortable cellspacing=1>";
}

$stream.="<thead class=rowheader>
    <tr class=rowheader>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['station']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['noakun']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namaakun']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jumlah']."</td> 
       <td hidden bgcolor=#CCCCCC align=center>".$_SESSION['lang']['total']."</td>    
       ";
$stream.="</tr>";
$stream.="</thead>";




$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kdorg."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
	
}
$nmorg['FWPM99'].='ALAT BERAT & KENDARAAN';

// $str="select sum(debet) as jumlah,noakun,substr(noakun,1,5) as noakunhead,substr(kodeblok,1,6) as kodeblok,kodeorg from ".$dbname.".keu_jurnaldt_vw "
        // . " where kodeorg='".$kdorg."' and periode='".$per."' ".$akunsort." "
        // . "  and nojurnal not in (select nojurnal from ".$dbname.".keu_jurnaldt_vw where noakun='1210102') and nojurnal not like '%PRSDN%' "
        // . "  group by substr(kodeblok,1,6),noakun  order by noakun";



$str="select sum(jumlah) as jumlah,noakun,substr(kodeblok,1,6) as kodeblok,kodeorg from ".$dbname.".keu_jurnaldt_vw "
        . " where kodeorg='".$kdorg."' and periode='".$per."' ".$akunsort." and nojurnal not like '%PRSDN%' "
        . "  group by substr(kodeblok,1,6),noakun  order by noakun asc";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['kodeblok']==''){
		$bar['kodeblok']=$bar['kodeorg'];
	}
	if(substr($bar['noakun'],0,3)=='634'){
		$bar['kodeblok']='FWPM99';
	}
	if(substr($bar['noakun'],0,1)=='7'){
		$bar['kodeblok']=$bar['kodeorg'];
	}
	$kddiv[$bar['kodeblok']]=$bar['kodeblok'];
	$noakun[$bar['noakun']]=$bar['noakun'];
	$listakun[$bar['kodeblok']][$bar['noakun']]=$bar['noakun'];
	$jumlah[$bar['kodeblok']][$bar['noakun']]+=$bar['jumlah'];
	
}



// echo"<pre>";
// print_r($noakun);
// echo"</pre>";


array_multisort($kddiv,SORT_ASC);

foreach($kddiv as $div){
	$stream.="<tr class=rowcontent>";
	if($div=='FWPM99'){
		$stream.="<td colspan=4><b>".$nmorg[$div]."</b></td>";
	}else{
		$stream.="<td colspan=4><b>".$div." - ".$nmorg[$div]."</b></td>";
	}
	
	$stream.="</tr>";
	foreach($noakun as $akun){
		if($listakun[$div][$akun]!=''){
			$stream.="<tr class=rowcontent>";
			$stream.="<td></td>";
			$stream.="<td>".$akun."</td>";
			$stream.="<td>".$namaakun[$akun]."</td>";
			$stream.="<td align=right>".number_format($jumlah[$div][$akun])."</td>";
			$stream.="</tr>";
			$tjumlah[$div]+=$jumlah[$div][$akun];
		}
	}
	$stream.="<tr class=rowcontent>";
	$stream.="<td colspan=3><b>Total</b></td>";
	$stream.="<td align=right><b>".number_format($tjumlah[$div])."</b></td>";
	$stream.="</tr>";
	$gtjumlah+=$tjumlah[$div];
}
$stream.="<tr class=rowcontent><thead>";
	$stream.="<td colspan=3><b>Grand Total</b></td>";
	$stream.="<td align=right><b>".number_format($gtjumlah)."</b></td>";
	$stream.="</thead></tr>";


$stream.="<tbody></table></fieldset>";


if ($proses == 'excel') {
    $stream .= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream .= "<fieldset style='float:left'><legend>".$_SESSION['lang']['produksi']."</legend><table class=sortable cellspacing=1>";
}

$stream.="<thead>
    <tr class=rowheader>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['komoditi']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['produksi']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['biaya']." / ".$_SESSION['lang']['kg']."</td>
       ";
$stream.="</tr>";
$stream.="</thead>";


$str="select sum(tbsdiolah) as tbs,sum(oer) as cpo,sum(oerpk) as pk from ".$dbname.".pabrik_produksi where kodeorg='".$unit."'  and tanggal like '%".$per."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	$tbs=$bar['tbs'];
	$cpo=$bar['cpo'];
	$pk=$bar['pk'];

	
$stream.="
	<tr class=rowcontent>
       <td align=left>".$_SESSION['lang']['tbs']."</td>
       <td align=right>".@number_format($tbs)."</td>
       <td align=right>".@number_format($gtjumlah/$tbs)."</td>
	</tr>
	<tr class=rowcontent>
       <td align=left>".$_SESSION['lang']['cpo']."</td>
       <td align=right>".@number_format($cpo)."</td>
       <td align=right>".@number_format($gtjumlah/$cpo)."</td>
	</tr>
    <tr class=rowcontent>
       <td align=left>".$_SESSION['lang']['kernel']."</td>
       <td align=right>".@number_format($pk)."</td>
       <td align=right>".@number_format($gtjumlah/$pk)."</td>
	</tr>";


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
        $nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_sd_".per2;
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
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