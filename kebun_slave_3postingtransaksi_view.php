<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses']))
{
	$proses=$_POST['proses'];
}
else
{
	$proses=$_GET['proses'];
}

$optNmKar=makeOption($dbname, 'datakaryawan','karyawanid,namakaryawan');
$optNmOrg=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');

$_POST['kdOrg']==''?$kdOrg=$_GET['kdOrg']:$kdOrg=$_POST['kdOrg'];
$_POST['thnId']==''?$thnId=$_GET['thnId']:$thnId=$_POST['thnId'];
@$_POST['kdProj']==''?@$kdProj=$_GET['kdProj']:@$kdProj=$_POST['kdProj'];
$_POST['tipe']==''?$tipe=$_GET['tipe']:$tipe=$_POST['tipe'];
$divisi=checkPostGet('divisi','');
$unitId=$_SESSION['lang']['all'];
$dktlmpk=$_SESSION['lang']['all'];

$_POST['tanggal1']==''?$tanggal1=$_GET['tanggal1']:$tanggal1=$_POST['tanggal1'];
$_POST['tanggal2']==''?$tanggal2=$_GET['tanggal2']:$tanggal2=$_POST['tanggal2'];

function putertanggal($tanggal)
{
    $qwe=explode('-',$tanggal);
    return $qwe[2].'-'.$qwe[1].'-'.$qwe[0];
} 

$tangsys1=putertanggal($tanggal1);
$tangsys2=putertanggal($tanggal2);

$wheretang=" b.tanggal like '%%' ";
if($tanggal1!=''){
    $wheretang=" b.tanggal = '".$tangsys1."' ";
    if($tanggal2!=''){
        $wheretang=" b.tanggal between '".$tangsys1."' and '".$tangsys2."' ";
    }
}
if($tanggal2!=''){
    $wheretang=" b.tanggal = '".$tangsys2."' ";
    if($tanggal1!=''){
        $wheretang=" b.tanggal between '".$tangsys1."' and '".$tangsys2."' ";
    }
}
$whdiv='';
if($divisi!=''){
	$whdiv=" and c.kodeorg like '".$divisi."%'";
}

if($proses=='preview'||$proses=='excel')
{


$brdr=0;
$bgcoloraja=$whre='';
 if(@$_POST['tipeTrk']!='')
        {
            $whre=" and tipetransaksi='". $_POST['tipeTrk']."'";
        }
        $str="select distinct b.notransaksi,b.tanggal, b.nobkm, substr(c.kodeorg,1,6) as divisi from ".$dbname.".kebun_aktifitas b 
			   left join ".$dbname.".kebun_prestasi c on b.notransaksi=c.notransaksi
               where substr(b.tanggal,1,7)='".$thnId."' and b.kodeorg='".$kdOrg."' and b.jurnal=0 ".$whdiv." and b.notransaksi like '%".$tipe."%'
               and ".$wheretang."
               ".$whre."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$rowdt=$res->rowCount();
        $tab.="<button class=mybutton onclick=postingDat(".$rowdt.")  id=revTmbl>Cek Data</button>";
		//$tab.="<button class=mybutton id=btnexcel style=display:none onclick=excel('excel')>Excel</button>";
		$tab.="<table cellspacing=1 border=".$brdr." class=sortable>
		<thead class=rowheader>
		<tr>
        <td ".$bgcoloraja." align=center>No.</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td ".$bgcoloraja." align=center>No BKM</td>
        <td ".$bgcoloraja." align=center>Divisi</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</td>       
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['status']."</td>       
        </tr>";
        $tab.="</tr></thead><tbody>";
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
           @$nor+=1;
           $tab.="<tr class=rowcontent id=rowDt_".$nor."><td align=center>".$nor."</td>";
           $tab.="<td id=notransaksi_".$nor.">".$bar['notransaksi']."</td>";
           $tab.="<td>".$bar['nobkm']."</td>";
           $tab.="<td>".$bar['divisi']."</td>";
           $tab.="<td>".$bar['tanggal']."</td>";
           $tab.="<td id=status_".$nor."></td>";
        }
        $tab.="</tbody></table>";
		
}
        
switch($proses){ 
	case'preview':
	echo $tab;
	break;
        case'getPeriode': 
            $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select distinct left(tanggal,7) as periode from ".$dbname.".kebun_aktifitas where 
			kodeorg='".$_POST['kdOrg']."' and jurnal=0";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);				 
			while ($bar = $res->fetch()) {	
               $optPeriode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
            }
            echo $optPeriode;
        break;
	default:
	break;
}
?>