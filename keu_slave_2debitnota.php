<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=	!empty($_POST['proses'])? $_POST['proses']:$_GET['proses'];
$pt=		!empty($_POST['pt'])? $_POST['pt']:$_GET['pt'];
$unit=		!empty($_POST['unit'])? $_POST['unit']:$_GET['unit'];
$tanggal=	!empty($_POST['tanggal'])? $_POST['tanggal']:$_GET['tanggal'];
$sd=		!empty($_POST['sd'])? $_POST['sd']:$_GET['sd'];
$tanggal=tanggalsystem($tanggal); 
$tgldari=substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2);
$sd=tanggalsystem($sd); 
$tglsd=substr($sd,0,4).'-'.substr($sd,4,2).'-'.substr($sd,6,2);
if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if(($tanggal=='')or($sd=='')){
            echo"Error: Date is obligatory."; exit;
    }
    if($tgldari>$tglsd){
            echo"Error: First date must smaller than the secon date."; exit;
    }
}
switch($proses){
case 'load_unit_kpd':
    $opt_unit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $s_unit="select * from ".$dbname.".organisasi where induk='".$pt."' order by kodeorganisasi asc";
    $res=$owlPDO->query($s_unit) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($r_unit=$res->fetch())
    {
        $opt_unit.="<option value='".$r_unit['kodeorganisasi']."'>".$r_unit['namaorganisasi']."</option>";  
        
    }
    echo $opt_unit;
	exit();	
break;
case 'load_kpd':
    $opt_kepada="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $s_kepada="select * from ".$dbname.".organisasi 
               where length(kodeorganisasi)=4 and kodeorganisasi != '".$unit."' 
               order by namaorganisasi asc";
    $res=$owlPDO->query($s_kepada) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($r_kepada=$res->fetch())
    {
        $opt_kepada.="<option value='".$r_kepada['kodeorganisasi']."'>".$r_kepada['namaorganisasi']."</option>";  
        
    }
    echo $opt_kepada;
	exit();
break;
}
# Ambil akun r/k tujuan

$no=0;


$bgcoloraja="bgcolor=#DEDEDE ";
$brdr=1;
$stream="
<table border=0>
<tr><td align=left colspan=11><b>Laporan debet nota</b></td></tr>
<tr>
<td align=left>".$_SESSION['lang']['namapt']."</td>
<td>:".$pt."</td>
</tr>
<tr>
<td align=left>".$_SESSION['lang']['unitkerja']."</td>
        <td colspan=4>:".$unit."</td>
</tr>
<tr>
<td align=left>".$_SESSION['lang']['periode']."</td>
<td colspan=4>:".substr($tanggal,6,2).'-'.substr($tanggal,4,2).'-'.substr($tanggal,0,4)." 
s/d ".substr($sd,6,2).'-'.substr($sd,4,2).'-'.substr($sd,0,4)."</td>
</tr><tr><td colspan=5></td></tr>
</table>";

$stream.="<div style=overflow:auto; height:300px;>";
$stream.="<table cellpading=1 cellspacing=1 border=0 class=sortable  style='float:left;'>";
$stream.="<thead>";
$stream.="
<table cellspacing='1' border='".$brdr."' class='sortable'>
<thead>
<tr class=rowheader>
<td align=center>Tanggal</td>
<td align=center>Jenis</td> 
<td align=center>Nomor Nota Debet</td> 
<td align=center>Supplier</td>
<td align=center>Invoice</td>
<td align=center>Nomor Akun</td>  
<td align=center>Nama Akun</td>   
<td align=center>Debet</td> 
<td align=center>Kredit</td> 
</tr></thead>
<tbody>";



// echo $stream;
// exit();
$no=$jumlah=$zerox=$zerox2=0;
$tanggaljenis='';
$notadebetx='';
$debet=0;
$kredit=0;
$s_transaksi="select * from ".$dbname.".keu_notadebet_ht  
               where kodeorg='".$pt."' and unit='".$unit."' and (tanggal >= '".$tanggal."' and tanggal <= '".$sd."') 
               order by tanggal,tipeinvoice,notadebet,kodesupplier,noinvoice_referensi asc";
$resx=$owlPDO->query($s_transaksi) or die(print " Gagal: ".PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_ASSOC);
while($bax=$resx->fetch())
{
    //echo $no+=1;
        if($tanggaljenis=='')
        {
            $tanggaljenis=$bax['tanggal'].$bax['tipeinvoice'];
            $zerox=1;
            $debet=0;
            $kredit=0;
        }
        else
        {
            if($tanggaljenis==$bax['tanggal'].$bax['tipeinvoice'])
            {
                $zerox=0;
            }
            else
            {
                $tanggaljenis=$bax['tanggal'].$bax['tipeinvoice'];
                $zerox=1;
                $debet=0;
                $kredit=0;
            }
        }

        if($notadebetx=='')
        {
            $notadebetx=$bax['notadebet'];
            $zerox2=1;
        }
        else
        {
            if($notadebetx==$bax['notadebet'])
            {
                $zerox2=0;
            }
            else
            {
                $notadebetx=$bax['notadebet'];
                $zerox2=1;
            }
        }
        // $no=1;
        // $str="select * from ".$dbname.".keu_notadebet_ht where notadebet='".$notadebet."'";
        // $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_ASSOC);
        // $bax=$res->fetch();


        //get unit tagihan
        $sqlht=$owlPDO->query("select unit from ".$dbname.".keu_tagihanht where noinvoice='".$bax['noinvoice_referensi']."'");
        $sqlht->setFetchMode(PDO::FETCH_ASSOC);
        $tght=$sqlht->fetch();
        $unittagihan=$tght['unit'];

        //check apakah unit tagihan = unit nota debet
        //jika tidak sama muncul jurnal R/K
        //echo $bax['notadebet'].' : '.$bax['noinvoice_referensi'].' : '.$bax['unit'].'=='.$unittagihan.'<br>';
        if ($bax['unit']!=$unittagihan and $unittagihan!='')
        {
            $strcaco="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$bax['unit']."' and jenis='intra'";
            $rescaco=fetchData($strcaco);
            $akunrkpiutang=$rescaco[0]['akunpiutang'];
    
            $strcaco2="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".$unittagihan."' and jenis='intra'";
            $rescaco2=fetchData($strcaco2);
            $akunrkhutang=$rescaco2[0]['akunhutang'];
        }

        $whrno="noakun='".$bax['noakun']."'";
        $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);
        $whrtipe="kode='".$bax['tipeinvoice']."'";
        $opttipe=makeOption($dbname,'keu_5jenistagihan','kode,namajenis',$whrtipe);
        $whrsupp="supplierid='".$bax['kodesupplier']."'";
        $optsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsupp);
        $stream.="<tr class=rowcontent>";
        if($zerox==1)
        {
            $stream.="<td style='text-align:center;'><b>".$bax['tanggal']."</b></td>";
            $stream.="<td style='text-align:center;'><b>".$opttipe[$bax['tipeinvoice']]."</b></td>";
            $zerox=0;
        }
        else
        {
            $stream.="<td style='text-align:center;'></td>";
            $stream.="<td style='text-align:center;'></td>";
        }
        if($zerox2==1)
        {
            $stream.="<td style='text-align:center;'><b>".$bax['notadebet']."</b></td>";
            $stream.="<td style='text-align:center;'><b>".$optsupp[$bax['kodesupplier']]."</b></td>";
            $stream.="<td style='text-align:center;'><b>".$bax['noinvoice_referensi']."</b></td>";
            $zerox2=0;
        }
        else
        {
            $stream.="<td style='text-align:center;'></td>";
            $stream.="<td style='text-align:center;'></td>";
            $stream.="<td style='text-align:center;'></td>";
        }        
        $stream.="<td>".$bax['noakun']."</td>
        <td>".$optnmakun[$bax['noakun']]."</td>
        <td align=right>".number_format($bax['nilaiinvoice'],2)."</td>
        <td align=right>".number_format(0,2)."</td>";
        $debet=$bax['nilaiinvoice'];
        $stream.="</tr>";

        if ($bax['unit']!=$unittagihan and $unittagihan!=''){
            $whrno="noakun='".$akunrkpiutang."'";
            $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);
            $whrtipe="kode='".$bax['tipeinvoice']."'";
            $opttipe=makeOption($dbname,'keu_5jenistagihan','kode,namajenis',$whrtipe);
            $whrsupp="supplierid='".$bax['kodesupplier']."'";
            $optsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsupp);
            $stream.="<tr class=rowcontent>";
            if($zerox==1)
            {
                $stream.="<td style='text-align:center;'><b>".$bax['tanggal']."</b></td>";
                $stream.="<td style='text-align:center;'><b>".$opttipe[$bax['tipeinvoice']]."</b></td>";
                $zerox=0;
            }
            else
            {
                $stream.="<td style='text-align:center;'></td>";
                $stream.="<td style='text-align:center;'></td>";
            }
            if($zerox2==1)
            {
                $stream.="<td style='text-align:center;'><b>".$bax['notadebet']."</b></td>";
                $stream.="<td style='text-align:center;'><b>".$optsupp[$bax['kodesupplier']]."</b></td>";
                $stream.="<td style='text-align:center;'><b>".$bax['noinvoice_referensi']."</b></td>";
                $zerox2=0;
            }
            else
            {
                $stream.="<td style='text-align:center;'></td>";
                $stream.="<td style='text-align:center;'></td>";
                $stream.="<td style='text-align:center;'></td>";
            }          
            $stream.="<td>".$akunrkpiutang."</td>
            <td>".$optnmakun[$akunrkpiutang]."</td>
            <td align=right>".number_format(0,2)."</td>
            <td align=right>".number_format($bax['nilaiinvoice'],2)."</td>";
            $debet=$bax['nilaiinvoice'];
            $stream.="</tr>";
           // $no+=1;

            $whrno="noakun='".$akunrkhutang."'";
            $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);
            $whrtipe="kode='".$bax['tipeinvoice']."'";
            $opttipe=makeOption($dbname,'keu_5jenistagihan','kode,namajenis',$whrtipe);
            $whrsupp="supplierid='".$bax['kodesupplier']."'";
            $optsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsupp);
            $stream.="<tr class=rowcontent>";
            if($zerox==1)
            {
                $stream.="<td style='text-align:center;'><b>".$bax['tanggal']."</b></td>";
                $stream.="<td style='text-align:center;'><b>".$opttipe[$bax['tipeinvoice']]."</b></td>";
                $zerox=0;
            }
            else
            {
                $stream.="<td style='text-align:center;'></td>";
                $stream.="<td style='text-align:center;'></td>";
            }

            if($zerox2==1)
            {
                $stream.="<td style='text-align:center;'><b>".$bax['notadebet']."</b></td>";
                $stream.="<td style='text-align:center;'><b>".$optsupp[$bax['kodesupplier']]."</b></td>";
                $stream.="<td style='text-align:center;'><b>".$bax['noinvoice_referensi']."</b></td>";
                $zerox2=0;
            }
            else
            {
                $stream.="<td style='text-align:center;'></td>";
                $stream.="<td style='text-align:center;'></td>";
                $stream.="<td style='text-align:center;'></td>";
            } 

            $stream.="<td>".$akunrkhutang."</td>
            <td>".$optnmakun[$akunrkhutang]."</td>
            <td align=right>".number_format($bax['nilaiinvoice'],2)."</td>
            <td align=right>".number_format(0,2)."</td>";
            $debet=$bax['nilaiinvoice'];
            $stream.="</tr>";
        }

        $str="select * from ".$dbname.".keu_notadebet_dt where notadebet='".$bax['notadebet']."'";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($baxz=$res->fetch()) {
            $whrno="noakun='".$baxz['noakun']."'";
            $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);

        $whrtipe="kode='".$bax['tipeinvoice']."'";
        $opttipe=makeOption($dbname,'keu_5jenistagihan','kode,namajenis',$whrtipe);
        $whrsupp="supplierid='".$bax['kodesupplier']."'";
        $optsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsupp);
        $stream.="<tr class=rowcontent>";
        if($zerox==1)
        {
            $stream.="<td style='text-align:center;'><b>".$bax['tanggal']."</b></td>";
            $stream.="<td style='text-align:center;'><b>".$opttipe[$bax['tipeinvoice']]."</b></td>";
            $zerox=0;
        }
        else
        {
            $stream.="<td style='text-align:center;'></td>";
            $stream.="<td style='text-align:center;'></td>";
        }
        if($zerox2==1)
        {
            $stream.="<td style='text-align:center;'><b>".$bax['notadebet']."</b></td>";
                $stream.="<td style='text-align:center;'><b>".$optsupp[$bax['kodesupplier']]."</b></td>";
            $stream.="<td style='text-align:center;'><b>".$bax['noinvoice_referensi']."</b></td>";
            $zerox2=0;
        }
        else
        {
            $stream.="<td style='text-align:center;'></td>";
            $stream.="<td style='text-align:center;'></td>";
            $stream.="<td style='text-align:center;'></td>";
        }          
        $stream.="<td>".$baxz['noakun']."</td>
                <td>".$optnmakun[$baxz['noakun']]."</td>
                <td align=right>".number_format(0,2)."</td>
                <td align=right>".number_format($baxz['nilai'],2)."</td>";
                $kredit+=$baxz['nilai'];
        $stream.="</tr>";
            }
        $stream.="<tr class=rowcontent>";
        $stream.="<td colspan=7>".$_SESSION['lang']['subtotal']."</td>";
        $stream.="<td align=right>".number_format($debet,2)."</td>";
        $stream.="<td align=right>".number_format($kredit,2)."</td>";
        $stream.="</tr>";

    
}
$stream.="</tbody>";
$stream.="</table>";
$stream.="</tbody></table>";
//echo $stream;
//exit();
switch($proses){
case 'preview':
    echo $stream;
break;
case 'excel':   
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
    $dte=date("YmdHis");
    $nop_="LaporanDebetNota_".$dte;
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

default:
break;	
}
?>