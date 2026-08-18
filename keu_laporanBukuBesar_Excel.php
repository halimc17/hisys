<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');


$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$periode=$_GET['periode'];
$periode1=$_GET['periode1'];
$revisi=$_GET['revisi'];
$regional=$_GET['regional'];

$akundari=$_GET['akundari'];
$akunsampai=$_GET['akunsampai'];
$tampilanId=$_GET['tampilanId'];


//cek periode dan periode1
if($periode1<$periode)
{  #ditukar
    $z=$periode;
    $periode=$periode1;
    $periode1=$z;
}
$where='';
if($akundari!='' and $akunsampai!=''){
	$where.=" and noakun between '".$akundari."' and  '".$akunsampai."'";
}
        
//ambil namapt
$str=$owlPDO->query("select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'");
$namapt='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}

//ambil namagudang
$str=$owlPDO->query("select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."'");
$namagudang='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $namagudang=strtoupper($bar->namaorganisasi);
}

//ambil akun laba rugi tahun berjalan:
$CLM='';
$str=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='CLM'");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $CLM=$bar->noakundebet;
}

//ambil semua noakun dari bulan lalu dan bulan ini
$lmperiode=mktime(0,0,0,substr($periode,5,2)-1,4,substr($periode,0,4));
$lmperiode=date('Y-m',$lmperiode);
if($_SESSION['language']=='ID'){
    $str="select distinct noakun,namaakun from ".$dbname.".keu_5akun where  noakun!='".$CLM."'  ".$where." order by noakun";
}
else{
    $str="select distinct noakun,namaakun1 as namaakun from ".$dbname.".keu_5akun where  noakun!='".$CLM."'  ".$where." order by noakun";
}
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
$TAB=Array();
while($bar=$res->fetch())
{
    $TAB[$bar->noakun]['noakun']=$bar->noakun;
    $TAB[$bar->noakun]['namaakun']=$bar->namaakun;
    $TAB[$bar->noakun]['sawal']=0;
    $TAB[$bar->noakun]['salak']=0;
}


if($regional=='' && $gudang=='')
{
   $where =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
}
else if($regional!='' && $gudang=='')
{
    //$where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."') "; 
    $where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
    
}
else
{
    $where =" and kodeorg ='".$gudang."'";
}

$str=$owlPDO->query("select sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun from ".$dbname.".keu_saldobulanan 
      where periode ='".str_replace("-","",$periode)."' and   noakun!='".$CLM."' ".$where."  group by noakun order by noakun");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $TAB[$bar->noakun]['sawal']=$bar->sawal;
    $TAB[$bar->noakun]['salak']=$bar->sawal;
}

$str=$owlPDO->query("select sum(debet) as debet,sum(kredit) as kredit,noakun from ".$dbname.".keu_jurnaldt_vw
        where periode>='".$periode."' and periode<='".$periode1."' ".$where."
        and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun"); #tidak sama dengan laba/rugi berjalan
//=================================================
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $TAB[$bar->noakun]['debet']=$bar->debet;
    $TAB[$bar->noakun]['kredit']=$bar->kredit;
    $TAB[$bar->noakun]['salak']=$TAB[$bar->noakun]['sawal']+$bar->debet-$bar->kredit;
} 
$no=0;
$stream=strtoupper($_SESSION['lang']['neracasaldo'])." : ".$namapt." ".$namagudang."<br>".$periode." s/d ".$periode1."<table border=1>
    <thead>
    <tr bgcolor='#dedede'>
        <td>".$_SESSION['lang']['nourut']."</td>
        <td>".$_SESSION['lang']['noakun']."</td>
        <td width=60px>".$_SESSION['lang']['namaakun']."</td>
        <td>".$_SESSION['lang']['saldoawal']."</td>
        <td>".$_SESSION['lang']['debet']."</td>
        <td>".$_SESSION['lang']['kredit']."</td>
        <td>".$_SESSION['lang']['saldoakhir']."</td>
    </tr>  
    </thead>
    <tbody id=container>";
$sal_awal=0;
$sal_debet=0;
$sal_kredit=0;
$sal_salak=0;    
foreach($TAB as $baris => $data)
{
 /*   if($data['sawal']==0 && $data['debet']==0 && $data['kredit']==0 && $data['salak']==0)
    {
        
    }
    else
    {*/    
    if($tampilanId==1){
        if(($data['sawal']==0)&&($data['debet']==0)&&($data['kredit']==0)){
            continue;
        }
    }
    $no+=1;
    $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$lmperiode."','".$pt."','".$gudang."',event);\">
            <td>".$no."</td>
            <td>".$data['noakun']."</td>    
            <td>".$data['namaakun']."</td>
            <td align=right>".number_format($data['sawal'],2)."</td>
            <td align=right>".number_format($data['debet'],2)."</td>
            <td align=right>".number_format($data['kredit'],2)."</td>   
            <td align=right>".number_format($data['salak'],2)."</td>    
        </tr>";
   // }   
    $sal_awal+=$data['sawal'];
    $sal_debet+=$data['debet'];
    $sal_kredit+=$data['kredit'];
    $sal_salak+=$data['salak'];
} 

$stream.="<tr class=rowcontent>
        <td colspan=3 align=center>TOTAL</td>
        <td align=right>".number_format($sal_awal,2)."</td>
        <td align=right>".number_format($sal_debet,2)."</td>
        <td align=right>".number_format($sal_kredit,2)."</td>   
        <td align=right>".number_format($sal_salak,2)."</td> 
    </tr>
    </tbody>
    <tfoot>
    </tfoot>		 
    </table>";
$qwe=date("YmdHms");
//exit("Error:$stream");
$nop_="NeracaSaldo_".$gudang.$periode." ".$qwe;
if(strlen($stream)>0)
{
    $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
    gzwrite($gztralala, $stream);
    gzclose($gztralala);
    echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
}    
?>