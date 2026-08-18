<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=$_GET['proses'];
$lokasi=$_SESSION['empl']['lokasitugas'];
$kebun=checkPostGet('kebun','');
$divisi=checkPostGet('kdUnit','');
$mandor=checkPostGet('mandor','');
$periode=checkPostGet('periode','');
$jenis=checkPostGet('jenis','');



if ($divisi!='') {
    $where=" and a.kodeorg like '%".$divisi."%' ";
}

if ($mandor!='') {
    $where.=" and a.noreferensi like '".$mandor."%'";
}
function dates_inbetween($date1, $date2){

	$day = 60*60*24;

	$date1 = strtotime($date1);
	$date2 = strtotime($date2);

	$days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

	$dates_array = array();
	$dates_array[] = date('Y-m-d',$date1);

	for($x = 1; $x < $days_diff; $x++){
		$dates_array[] = date('Y-m-d',($date1+($day*$x)));
	}

	$dates_array[] = date('Y-m-d',$date2);
	if($date1==$date2){
		$dates_array = array();
		$dates_array[] = date('Y-m-d',$date1);        
	}
	return $dates_array;
}

$stream="";
if ($proses=='excel' or $proses=='preview'){
    $border=0;
    if($proses=='excel')$border=1;
	$wh="";$i="";
	if($jenis=='posting'){
		$wh=" and b.tanggal like '%".$periode."%' ";
		$i=",b.tanggal as tanggal";
	}else if($jenis=='input'){
		$wh=" and b.tanggalinput like '%".$periode."%' ";
		$i=",b.tanggalinput as tanggal";
	}
	
	
    $str="select a.* ".$i." from ".$dbname.".kebun_kehadiran_vw a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.unit='".$kebun."' ".$where." ".$wh." and a.notransaksi like '%bor%' order by b.tanggal";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
	$no=$jhk=$umr=$insentif=0;
	while($bar=$res->fetch()){
		$karyawanid[$bar->karyawanid]=$bar->karyawanid;
		$kegiatan=$bar->kodekegiatan;
		$tgal[$bar->tanggal]=$bar->tanggal;
		@$hasilkerja[$bar->karyawanid][$bar->tanggal]+=$bar->hasilkerja;
		@$totalrupiah[$bar->karyawanid]+=$bar->insentif;		   
	}
	
	if(count($karyawanid)<1){
		exit("Warning : Data tidak di temukan / kosong !!!");
	}
    $str="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$periode."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
	$no=$jhk=$umr=$insentif=0;
	while($bar=$res->fetch()){
		$tgl1=$bar->tanggalmulai;
		$tgl2=$bar->tanggalsampai;
	}

	$tgal = dates_inbetween($tgl1, $tgl2);
	$optkeg=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan',"kodekegiatan='".$kegiatan."'");
	$stream.="<table cellspacing='1' border=0>
   
    <tr><td>BORONGAN PEKERJAAN</td><td>:</td><td>".$optkeg[$kegiatan]."</td></tr>
    <tr><td>KEBUN/DIVISI</td><td>:</td><td>".$kebun." / ".$divisi."</td></tr>
    <tr><td>Luas</td><td>:</td></tr>
    <tr><td>Jumlah Pokok</td><td>:</td></tr>
    <tr><td>Nama Mandor</td><td>:</td><td>".$mandor."</td></tr>
    <tr><td>Periode</td><td>:</td><td>".$periode."</td></tr>
    </table>";

	$row=count($tgal);       
	$rowtot=count($tgal)+5;       
	$stream.="<table cellspacing='1' border='".$border."' class='sortable'>
			<thead><tr class=rowheader>
        <th rowspan=2>No</th>
        <th rowspan=2>".$_SESSION['lang']['nik2']."</th>
        <th rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>
        <th rowspan=2>".$_SESSION['lang']['rekening']."</th>
        <th rowspan=2>".$_SESSION['lang']['bank']."</th>
        <th colspan=".$row.">".$_SESSION['lang']['tanggal']." / Jumlah Hasil Kerja</th>
        <th rowspan=2>Total Unit</th>
        <th rowspan=2>Rp/Unit</th>
        <th rowspan=2>Total Rp</th>";
        $stream.="</tr>";
         

	foreach($tgal as $tgl){
		$stream.="<td align=center>".substr($tgl,8,2)."</td>";
	}
	
	
	$stream.="</thead>";
	$stream.="<tbody>";
	$no=0;
	foreach ($karyawanid as $kary) {
		$no++;
		$optNmkar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$kary."'");
		$optnik=makeOption($dbname, 'datakaryawan', 'karyawanid,nik',"karyawanid='".$kary."'");
		$optNorek=makeOption($dbname, 'datakaryawan', 'karyawanid,norekeningbank',"karyawanid='".$kary."'");
		$optNmbank=makeOption($dbname, 'datakaryawan', 'karyawanid,namabank',"karyawanid='".$kary."'");

		$stream.="<tr class=rowcontent> 
            <td align=center>".$no."</td>
            <td align=center>".$optnik[$kary]."</td>
            <td>".$optNmkar[$kary]."</td>
            <td>".$optNorek[$kary]."</td>
			<td>".$optNmbank[$kary]."</td>";
		foreach ($tgal as $tgl) {
			$stream.="<td align=right>".@number_format($hasilkerja[$kary][$tgl])."</td>";
			@$totunit[$kary]+=$hasilkerja[$kary][$tgl];
			@$tottgl[$tgl]+=$hasilkerja[$kary][$tgl];
		}
		$stream.="<td align=right>".number_format($totunit[$kary])."</td>
		<td align=right>".number_format($totalrupiah[$kary]/$totunit[$kary])."</td>
		<td align=right>".number_format($totalrupiah[$kary])."</td>";    
		@$grandtotunit+=$totunit[$kary];      
		@$grandtotrpunit+=$totalrupiah[$kary]/$totunit[$kary];      
		@$grandtotrupiah+=$totalrupiah[$kary];      
	}
	$stream.="<tr class=rowcontent>
		<td colspan=5 align=center>Total</td>";
		
		foreach ($tgal as $tgl) {
			$stream.="<td align=right>".@number_format($tottgl[$tgl])."</td>";
		}
		
	$stream.="<td align=right>".number_format($grandtotunit)."</td>
		<td align=right>".number_format($grandtotrpunit)."</td>
		<td align=right>".number_format($grandtotrupiah)."</td>
	   ";  
}  
switch($proses){
    case'preview':
        echo $stream;    
    break;
    case 'excel':
        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("YmdHms");
        $nop_="LaporanBoronganSendiri".$kebun.$mandor."-".$periode;
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $stream);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";            
    break; 

    case'getAfdeling':
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sPrd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
               where induk = '".$kebun."' and tipe='afdeling' order by namaorganisasi asc";

        $qPrd=$owlPDO->query($sPrd) or die(print " Gagal: ".PDOException::getMessage());
        $qPrd->setFetchMode(PDO::FETCH_ASSOC);
        while($rPrd=$qPrd->fetch()){
            $optAfd.="<option value=".$rPrd['kodeorganisasi'].">".$rPrd['namaorganisasi']."</option>";
        }
        
        
        
        echo $optAfd;
    break;  

      case'getPeriode':
                   exit('error'.$optper);
            $optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".kebun_curahhujan where 
                   kodeorg like '".$kdUnit."%' order by tanggal desc";
            $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
            $qTgl->setFetchMode(PDO::FETCH_ASSOC);
            while($rTgl=$qTgl->fetch())
            {
               $optper.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
            }
            echo $optper;
            break; 
}

?>