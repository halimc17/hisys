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

$whdiv='';
if($divisi!=''){
	$whdiv=" and c.kodeorg like '".$divisi."%'";
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

if($proses=='preview'||$proses=='excel'){
	$brdr=0;
	$bgcoloraja='';
	if(@$_POST['tipeTrk']!=''){
		$whre=" and tipetransaksi='". $_POST['tipeTrk']."'";
	}
   $str="select distinct b.notransaksi,b.tanggal, b.nobkm ,substr(c.kodeorg,1,6) as divisi,tipetransaksi from ".$dbname.".kebun_aktifitas b
			left join ".$dbname.".kebun_prestasi c on b.notransaksi=c.notransaksi
		   where substr(b.tanggal,1,7)='".$thnId."' and b.kodeorg='".$kdOrg."' and b.jurnal=0 ".$whdiv." and b.notransaksi like '%".$tipe."%'
		   and ".$wheretang."
		   ".@$whre."";
	$res = fetchdata($str);	   
	$rowdt=count($res);
	//$tab.="<button class=mybutton onclick=postingDat(".$rowdt.")  id=revTmbl>Posting Data</button>";
	$tab.="<table cellspacing=1 border=".$brdr." class=sortable>
	<thead class=rowheader>
	<tr>
	<th ".$bgcoloraja." align=center>No.</th>
	<th ".$bgcoloraja." align=center>".$_SESSION['lang']['tipe']."</th>
	<th ".$bgcoloraja." align=center>".$_SESSION['lang']['absensi']."</th>
	<th ".$bgcoloraja." align=center>".$_SESSION['lang']['notransaksi']."</th>
	<th ".$bgcoloraja." align=center>No BKM</th>
	<th ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</th>       
	<th ".$bgcoloraja." align=center>".$_SESSION['lang']['divisi']."</th>       
	<th ".$bgcoloraja." align=center>".$_SESSION['lang']['status']."</th>       
	</tr>";
	$tab.="</tr></thead><tbody>";
	$tab.="<tr class=rowcontent><td colspan=8><button class=mybutton onclick=postingDat(".$rowdt.")  id=revTmbl>Posting Data</button></td></tr>";
	
	
	$nor=0;
	foreach($res as $bar){
		$ttl=0; #absensi 
		$strn = "select kodeorg,sum(umr) as umr, sum(premi) as premi, sum(hk) as hk from ".$dbname.".sdm_absensidt where norefrensi='".$bar['notransaksi']."' and nobkm='".$bar['nobkm']."'"; 
		$resn = fetchdata($strn);
		$ttl=$resn[0]['hk']+$resn[0]['umr']+$resn[0]['premi'];
		$abs="";
		if($bar['divisi']=='' and $ttl>0){
			$abs="absensi";
		}
		$nor+=1;
		$tab.="<tr class=rowcontent id=rowDt_".$nor."><td align=center>".$nor."</td>";
		$tab.="<td id=tipe_".$nor.">".$bar['tipetransaksi']."</td>";
		$tab.="<td id=absen_".$nor.">".$abs."</td>";
		$tab.="<td id=notransaksi_".$nor.">".$bar['notransaksi']."</td>";
		$tab.="<td>".$bar['nobkm']."</td>";
		$tab.="<td>".$bar['tanggal']."</td>";
		$tab.="<td>".$bar['divisi']."</td>";
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
			kodeorg='".$_POST['kdOrg']."' and jurnal=0 order by periode desc";
			$res = fetchdata($str);	
			foreach($res as $bar){	
               $optPeriode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
            }
            echo $optPeriode;
        break;
	default:
	break;
}
?>