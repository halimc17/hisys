<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$kdorg=checkPostGet('kdorgv','');
$per1=checkPostGet('per1v','');
$per2=checkPostGet('per2v','');
$kom=checkPostGet('komv','');
$tipekar=checkPostGet('tipekarv','');
$jab=checkPostGet('jabv','');

$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$kenamaorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$komponen="";
if($kom!='')
{
    $komponen=" and idkomponen='".$kom."' ";
}

$sorttk="";
if($tipekar!='')
{
    $sorttk=" and tipekaryawan='".$tipekar."'";
}
else
{
    $sorttk=" and tipekaryawan between '1' and '6' ";
}


$sortjab="";
if($jab!='')
{
    $sortjab=" and kodejabatan='".$jab."' ";
}

##############buat list bulan
$start = new DateTime($per1.'-01');
$end = new DateTime($per2.'-01');
$inc = DateInterval::createFromDateString('first day of next month');
$end->modify('+1 day');
$p = new DatePeriod($start,$inc,$end);  
foreach ($p as $d)
{
    $rangePer[$d->format('Y-m')]=$d->format('Y-m');
}   
##########tutup list bulan


$colspanPer=count($rangePer);



#bentuk list subbagian
$isub="select distinct(subbagian) as subbagian from ".$dbname.".sdm_gaji_vw where kodeorg='".$kdorg."'  order by subbagian asc";
$nsub=$owlPDO->query($isub) or die(print " Gagal: ".PDOException::getMessage());
$nsub->setFetchMode(PDO::FETCH_ASSOC);
while($dsub=$nsub->fetch())
{
    $listsub[$dsub['subbagian']]=$dsub['subbagian'];
   
}

$noKom=$counterKar=$counterGaji="";

#bentuk komponen

$iKom="select distinct (idkomponen) as idkomponen from ".$dbname.".sdm_gaji_vw where 1=1 ".$komponen." order by idkomponen asc ";
$nKom=$owlPDO->query($iKom) or die(print " Gagal: ".PDOException::getMessage());
$nKom->setFetchMode(PDO::FETCH_ASSOC);
while($dKom=$nKom->fetch())
{
    $noKom+=1;
    $idKomponen[$dKom['idkomponen']]=$dKom['idkomponen'];
}

  
##buat array jabatan
$counterjab=$counterGaji=0;
$ijab="select distinct(kodejabatan) as kodejabatan from ".$dbname.".sdm_gaji_vw  where lokasitugas='".$kdorg."' "
        . " ".$sorttk." ".$sortjab." and periodegaji between '".$per1."' and '".$per2."'"
        . " order by kodejabatan asc ";
$njab=$owlPDO->query($ijab) or die(print " Gagal: ".PDOException::getMessage());
$njab->setFetchMode(PDO::FETCH_ASSOC);
while($djab=$njab->fetch())
{
    $counterjab+=1;
    $jabatan[$djab['kodejabatan']]=$djab['kodejabatan'];
}


#ambil gaji dan komponen
$iGaji="select sum(jumlah) as jumlah,kodejabatan,periodegaji,idkomponen,subbagian "
        . " from ".$dbname.".sdm_gaji_vw   where kodeorg='".$kdorg."' "
        . " ".$sorttk." ".$sortjab." and periodegaji between '".$per1."' and '".$per2."' ".$komponen.""
        . " group by subbagian,kodejabatan,periodegaji,idkomponen";
$nGaji=$owlPDO->query($iGaji) or die(print " Gagal: ".PDOException::getMessage());
$nGaji->setFetchMode(PDO::FETCH_ASSOC);
while($dGaji=$nGaji->fetch())
{
    $counterGaji+=1;
    $gaji[$dGaji['subbagian']][$dGaji['kodejabatan']][$dGaji['periodegaji']][$dGaji['idkomponen']]=$dGaji['jumlah'];
}

if($counterGaji<1)
{
    exit("Data Kosong");
}


if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<table class=sortable cellspacing=1>";
}

$stream.="<thead class=rowheader>
    <tr class=rowheader>
       <td rowspan=3 bgcolor=#CCCCCC align=center>No</td>
       <td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jabatan']."</td>
       <td colspan=".$colspanPer*$noKom." bgcolor=#CCCCCC align=center>".$_SESSION['lang']['periode']."</td>
      
    </tr>";// <td rowspan=3 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['subtotal']."</td>
$stream.="<tr class=rowheader>";
foreach($rangePer as $ar => $per)
{
    $stream.="<td colspan=".$noKom." bgcolor=#CCCCCC align=center>".$per."</td>";
}
$stream.="</tr>";


$stream.="<tr class=rowheader>";
for($i=1;$i<=$colspanPer;$i++)
{
    foreach($idKomponen as $idKom)
    {
        $stream.="<td bgcolor=#CCCCCC align=center>".$optNmKomponen[$idKom]."</td>";
    }
}
$stream.="</tr>";
$stream.="</thead>";


$spansubbag=($noKom*count($rangePer))+2;



foreach($listsub as $sub)
{
    if($sub=='')
    {
        $kdsub=$kdorg;
    }
    else
    {
        $kdsub=$sub;
    }
    $stream.="<tr class=rowcontent>";
    $stream.="<td colspan=".$spansubbag."><b>".$kenamaorg[$kdsub]."</b></td>";
    $stream.="</tr>";
    $no=0;
    foreach($jabatan as $jab)
    {
        $no+=1;
        $stream.="<tr class=rowcontent>
        <td align=center>".$no."</td>
        <td align=left>".$optnmjab[$jab]."</td>";
       
        foreach($rangePer as $ar => $per)
        {
            foreach($idKomponen as $idKom)
            {
                setIt($subtot[$sub][$per][$idKom],'');
                setIt($gaji[$sub][$jab][$per][$idKom],'');
                
                $stream.="<td align=right>".number_format((float)$gaji[$sub][$jab][$per][$idKom],2)."</td>";
                $subtot[$sub][$per][$idKom]+=$gaji[$sub][$jab][$per][$idKom];
            }
        }
        $stream.="</tr>";  
    }
    $stream.="<tr class=rowcontent>";
    $stream.="<td colspan=2 align=center>".$_SESSION['lang']['subtotal']."</td>";
    foreach($rangePer as $ar => $per)
    {
        foreach($idKomponen as $idKom)
        {
            setIt($grantot[$per][$idKom],'');
            $stream.="<td align=right>".number_format((float)$subtot[$sub][$per][$idKom],2)."</td>";
            $grantot[$per][$idKom]+=$subtot[$sub][$per][$idKom];
        }
    }
    $stream.="</tr>";    
    
        
}

$stream.="<thead><tr class=rowcontent>";
$stream.="<td colspan=2 align=center>".$_SESSION['lang']['grnd_total']."</td>";

foreach($rangePer as $ar => $per)
{
    foreach($idKomponen as $idKom)
    {
      
          $stream.="<td align=right>".number_format((float)$grantot[$per][$idKom],2)."</td>";
    }
}
//$stream.="<td>".number_format($granTot,2)."</td>";


$stream.="</tr></thead>";
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
        //$nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_sd_".per2;
        $nop_="laporan_total_komponen_gaji_perjabatan".$kdorg;
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