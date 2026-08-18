<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=$_GET['proses'];
$proses2=$_POST['proses'];


$unit=$_POST['unit'];
$tgl1=  tanggalsystemn($_POST['tgl1']);
$tgl2=  tanggalsystemn($_POST['tgl2']);



$notran=$_POST['notran'];
$karyawanid=$_POST['karyawanid'];
$kodeorg=$_POST['kodeorg'];
$hasilkerjakg=$_POST['hasilkerjakg'];


//notransaksi	nik	kodekegiatan	kodeorg	tahuntanam	hasilkerja	hasilkerjakg	jumlahhk	norma
 $stream.="<table cellspacing='1' border='0' class='sortable'>";
                $stream.="<thead class=rowheader>
                 <tr>
                    <td align=center>No</td>
                    <td align=center>".$_SESSION['lang']['tipetransaksi']."</td>
                    <td align=center>".$_SESSION['lang']['tanggal']."</td>    
                    <td align=center>".$_SESSION['lang']['notransaksi']."</td>
                    <td align=center>".$_SESSION['lang']['nik']."</td>
                    <td align=center>".$_SESSION['lang']['kodeorg']."</td>
                    <td align=center>".$_SESSION['lang']['tahuntanam']."</td>    
                    <td align=center>".$_SESSION['lang']['hasilkerja']."</td>
                    <td align=center>".$_SESSION['lang']['bjr']."</td>    
                    <td align=center>".$_SESSION['lang']['hasilkerjakg']." Awal</td>
                    <td align=center>".$_SESSION['lang']['hasilkerjakg']." Baru</td>    
                    </tr></thead>
                <tbody>";

$iList="select a.notransaksi,a.nik,a.kodeorg,a.hasilkerja,a.hasilkerjakg,b.tipetransaksi,b.tanggal,a.tahuntanam "
        . " from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b "
        . " on a.notransaksi=b.notransaksi "
        . " where a.kodeorg like '%".$unit."%' and b.tanggal between '".$tgl1."' and '".$tgl2."'"
        . " and b.tipetransaksi='PNN' ";
$nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
$nList->setFetchMode(PDO::FETCH_ASSOC);
while($dList=  $nList->fetch())
{
    
    $iBjr="select bjr from ".$dbname.".kebun_5bjr where kodeorg='".$dList['kodeorg']."' ";
    $nBjr=$owlPDO->query($iBjr) or die(print " Gagal: ".PDOException::getMessage());
    $nBjr->setFetchMode(PDO::FETCH_ASSOC);
     $dBjr=$nBjr->fetch();   
    $no+=1;
    $stream.="<tr class=rowcontent  id=row".$no.">";
    $stream.="
        <td>".$no."</td>
        <td>".$dList['tipetransaksi']."</td>
        <td>".tanggalnormal($dList['tanggal'])."</td>    
        <td  id=notran".$no.">".$dList['notransaksi']."</td>    
        <td  id=karyawanid".$no.">".$dList['nik']."</td>    
        <td  id=kodeorg".$no.">".$dList['kodeorg']."</td>    
        <td>".$dList['tahuntanam']."</td>     
        <td>".$dList['hasilkerja']."</td>    
        <td>".$dBjr['bjr']."</td>
        <td>".$dList['hasilkerjakg']."</td>    
        <td id=hasilkerjakg".$no.">".$dBjr['bjr']*$dList['hasilkerja']."</td>    
        ";
    $stream.="</tr>";	
}

$stream.="<button class=mybutton onclick=saveAll(".$no.");>".$_SESSION['lang']['proses']."</button>";
$stream.="</table>";


switch($proses)
{
    case'preview':
        
        echo $stream;
	
        
    break;
    default:
}


switch($proses2)
{
    
    case'updatedata':
        $iUpdt="update  ".$dbname.".kebun_prestasi set hasilkerjakg='".$hasilkerjakg."' "
            . "where notransaksi='".$notran."' and nik='".$karyawanid."'  and kodeorg='".$kodeorg."'";
        try{$owlPDO->exec($iUpdt); }
        catch (PDOException $e) 
            {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    break;
    
    
    break;
    default;	
		
}
?>