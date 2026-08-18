<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=        empty($_POST['pt'])? (isset($_GET['pt'])? $_GET['pt']: '') : $_POST['pt'];
$tampilanId=empty($_POST['tampilanId'])? (isset($_GET['tampilanId'])? $_GET['tampilanId']: '') : $_POST['tampilanId'];
$gudang= 	empty($_POST['gudang'])? (isset($_GET['gudang'])? $_GET['gudang']: '') : $_POST['gudang'];
$periode= 	empty($_POST['periode'])? (isset($_GET['periode'])? $_GET['periode']: '') : $_POST['periode'];
$periode1= 	empty($_POST['periode1'])? (isset($_GET['periode1'])? $_GET['periode1']: '') : $_POST['periode1'];
$proses= 	empty($_POST['proses'])? (isset($_GET['proses'])? $_GET['proses']: '') : $_POST['proses'];


//cek periode dan periode1
if($periode1<$periode) {
	#ditukar
    $z=$periode;
    $periode=$periode1;
    $periode1=$z;
}

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $namapt=strtoupper($bar->namaorganisasi);
}

//ambil list unit
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
      kodeorganisasi in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
$namagudang=$_SESSION['lang']['all'];
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $dtUnit[]=$bar->kodeorganisasi;
        $dtNama[$bar->kodeorganisasi]=$bar->namaorganisasi;
}

//ambil akun laba rugi tahun berjalan:
$CLM='';
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='CLM'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $CLM=$bar->noakundebet;
}

if($_SESSION['language']=='EN'){
    $zz='namaakun1 as namaakun';
}else{
    $zz='namaakun';
}
//ambil semua noakun dari bulan lalu dan bulan ini
$lmperiode=mktime(0,0,0,substr($periode,5,2)-1,4,substr($periode,0,4));
$lmperiode=date('Y-m',$lmperiode);
$str="select distinct noakun,".$zz." from ".$dbname.".keu_5akun where  noakun!='".$CLM."' order by noakun";
$TAB=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if($bar->noakun!='')
    {
    $TAB[$bar->noakun]['noakun']=$bar->noakun;
    $TAB[$bar->noakun]['namaakun']=$bar->namaakun;
//    $TAB[$bar->noakun]['sawal']=0;
//    $TAB[$bar->noakun]['salak']=0;
    }
}
//ambil saldo awal
if($pt!='')
{
    $where =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
    $str="select kodeorg,sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun from ".$dbname.".keu_saldobulanan 
          where periode ='".str_replace("-","",$periode)."'  and  noakun!='".$CLM."' ".$where." group by kodeorg,noakun order by noakun";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        if((@$bar->debet!='0')||(@$bar->kredit!='0'))
        {
        $dt[$bar->noakun]['noakun']=$bar->noakun;
        $dt[$bar->noakun.$bar->kodeorg]['sawal']=$bar->sawal;
        $dt[$bar->noakun.$bar->kodeorg]['salak']=$bar->sawal;
        }
    }

//ambil mutasi-----------------------

if($pt!='')
{
        $str="select kodeorg,sum(debet) as debet,sum(kredit) as kredit,noakun from ".$dbname.".keu_jurnalsum_vw
              where periode>='".$periode."' and periode<='".$periode1."' and kodeorg in (select kodeorganisasi 
              from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)
              and noakun!='".$CLM."' group by kodeorg,noakun"; #tidak sama dengan laba/rugi berjalan
}
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        if(($bar->debet!='0')||($bar->kredit!='0'))
        {
        $dt[$bar->noakun]['noakun']=$bar->noakun;
        $dt[$bar->noakun.$bar->kodeorg]['debet']=$bar->debet;
        $dt[$bar->noakun.$bar->kodeorg]['kredit']=$bar->kredit;
        @$dt[$bar->noakun.$bar->kodeorg]['salak']=$dt[$bar->noakun.$bar->kodeorg]['sawal']+$bar->debet-$bar->kredit;
        }
    } 
    $brd=0;
    $bgcolor=" align=center";
    if($proses=='excel')
    {
        $brd=1;
        $bgcolor=" align=center bgcolor=#DEDEDE";
    }
    $tab="<table cellpaddin=1 cellspacing=1 border=".$brd." class=sortable><thead>";
    $tab.="<tr><td rowspan=2 ".$bgcolor.">".$_SESSION['lang']['noakun']."</td><td rowspan=2 ".$bgcolor.">".$_SESSION['lang']['namaakun']."</td>";
    if ($tampilanId==0) {
       $cols=4;
    }
    elseif ($tampilanId==3) {
       $cols=2;
    }
    else
    {
        $cols=3;
    }
    foreach($dtUnit as $lsUnit)
    {
        $tab.="<td colspan=".$cols." ".$bgcolor.">".$dtNama[$lsUnit]."</td>";
    }
    $tab.="</tr></tr>";
    foreach($dtUnit as $lsUnit)
    {   
        if ($tampilanId==0) {
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['saldoawal']."</td>";
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['debet']."</td>";
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['kredit']."</td>";
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['saldoakhir']."</td>";
        }
        if ($tampilanId==1) {
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['saldoawal']."</td>";
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['debet']."</td>";
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['saldoakhir']."</td>";
        }
        if ($tampilanId==2) {
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['saldoawal']."</td>";
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['kredit']."</td>";
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['saldoakhir']."</td>";
        }
        if ($tampilanId==3) {
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['saldoawal']."</td>";
        $tab.="<td ".$bgcolor.">".$_SESSION['lang']['saldoakhir']."</td>";
        }
       
    }
    $tab.="</tr></thead><tbody>";
    $no=0;


    foreach($TAB as $baris => $data)
    {

    $no+=1;
    $tab.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' >
    <td style='width:80px;'>".$data['noakun']."</td>    
    <td style='width:430px;'>".$data['namaakun']."</td>";
	
	foreach($dtUnit as $lsUnit)
    {
		if(!isset($dt[$data['noakun'].$lsUnit]['sawal'])) $dt[$data['noakun'].$lsUnit]['sawal']=0;
		if(!isset($dt[$data['noakun'].$lsUnit]['debet'])) $dt[$data['noakun'].$lsUnit]['debet']=0;
		if(!isset($dt[$data['noakun'].$lsUnit]['kredit'])) $dt[$data['noakun'].$lsUnit]['kredit']=0;
		if(!isset($dt[$data['noakun'].$lsUnit]['salak'])) $dt[$data['noakun'].$lsUnit]['salak']=0;


        if ($tampilanId==0) {
        $tab.="
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['sawal'],2)."</td>
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['debet'],2)."</td>
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['kredit'],2)."</td>   
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['salak'],2)."</td>";
        }
        if ($tampilanId==1) {
        $tab.="
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['sawal'],2)."</td>
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['debet'],2)."</td>  
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['salak'],2)."</td>";
        }
        if ($tampilanId==2) {
        $tab.="
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['sawal'],2)."</td>
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['kredit'],2)."</td>   
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['salak'],2)."</td>";
        }
        if ($tampilanId==3) {
       $tab.="
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['sawal'],2)."</td> 
        <td align=right onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$lsUnit."',event);\">".number_format($dt[$data['noakun'].$lsUnit]['salak'],2)."</td>";
        }
       
		
		if(!isset($sal_awal[$lsUnit])) $sal_awal[$lsUnit]=0;
		if(!isset($sal_debet[$lsUnit])) $sal_debet[$lsUnit]=0;
		if(!isset($sal_kredit[$lsUnit])) $sal_kredit[$lsUnit]=0;
		if(!isset($sal_salak[$lsUnit])) $sal_salak[$lsUnit]=0;
		$sal_awal[$lsUnit]+=$dt[$data['noakun'].$lsUnit]['sawal'];
		$sal_debet[$lsUnit]+=$dt[$data['noakun'].$lsUnit]['debet'];
		$sal_kredit[$lsUnit]+=$dt[$data['noakun'].$lsUnit]['kredit'];
		$sal_salak[$lsUnit]+=$dt[$data['noakun'].$lsUnit]['salak']; 
    }

    $tab.="</tr>";



    }   
    $tab.="<tr class=rowcontent>
 <td colspan=2 align=center>TOTAL</td>";
     foreach($dtUnit as $lsUnit)
    {

        if ($tampilanId==0) {
        $tab.="<td align=right>".number_format($sal_awal[$lsUnit],2)."</td>
        <td align=right>".number_format($sal_debet[$lsUnit],2)."</td>
        <td align=right>".number_format($sal_kredit[$lsUnit],2)."</td>   
        <td align=right>".number_format($sal_salak[$lsUnit],2)."</td>";
        }
        if ($tampilanId==1) {
        $tab.="<td align=right>".number_format($sal_awal[$lsUnit],2)."</td>
        <td align=right>".number_format($sal_debet[$lsUnit],2)."</td>  
        <td align=right>".number_format($sal_salak[$lsUnit],2)."</td>";
        }
        if ($tampilanId==2) {
        $tab.="<td align=right>".number_format($sal_awal[$lsUnit],2)."</td>
        <td align=right>".number_format($sal_kredit[$lsUnit],2)."</td>   
        <td align=right>".number_format($sal_salak[$lsUnit],2)."</td>";
        }
        if ($tampilanId==3) {
        $tab.="<td align=right>".number_format($sal_awal[$lsUnit],2)."</td>
        <td align=right>".number_format($sal_salak[$lsUnit],2)."</td>";
        }

     
    }
$tab.="</tr>"; 
    $tab.="</tbody></table>";
    switch($proses)
    {
        case'preview':
              echo $tab; 
            break;
        case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
                        $dte=date("Hms");
                        $nop_="neraca_per_unit_".$dte;
                         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                         gzwrite($gztralala, $tab);
                         gzclose($gztralala);
                         echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls.gz';
                            </script>";
            break;
default:
    break;
    }
?>