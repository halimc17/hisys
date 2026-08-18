<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=	!empty($_POST['proses'])? $_POST['proses']:$_GET['proses'];
@$pt=		!empty($_POST['pt'])? $_POST['pt']:$_GET['pt'];
@$unit=		!empty($_POST['unit'])? $_POST['unit']:$_GET['unit'];
@$periode1=	!empty($_POST['periode1'])? $_POST['periode1']:$_GET['periode1'];
@$periode2=	!empty($_POST['periode2'])? $_POST['periode2']:$_GET['periode2'];
@$tipe=	    !empty($_POST['tipe'])? $_POST['tipe']:$_GET['tipe'];
@$tipewkt=   !empty($_POST['tipewkt'])? $_POST['tipewkt']:$_GET['tipewkt'];
@$notransaksi=   !empty($_POST['notransaksi'])? $_POST['notransaksi']:$_GET['notransaksi'];


if(($proses=='preview')or($proses=='excel')){
    if(($periode1=='')){
            echo"Error: period is obligatory."; exit;
    }
   /* if($periode1>$periode2){
            echo"Error: First period must smaller than the secon period."; exit;
    }*/

    if ($periode1!='') {
        if ($periode2=='') {
        $period=" and tanggalmulai like '%".$periode1."%'";
        }
    }

    if ($periode1!='') {
        if ($periode2!='') {
         @$period.=" and tanggalmulai like '%".$periode1."%' and tanggalselesai like '%".$periode2."%'";
        }
    }



}

$theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
  $men='menu.css';
  $gen='generic.css';
}else if($theme=='red'){
  $men='menuRed.css';
  $gen='genericRed.css';  
}else{
  $men='menuGray.css';
  $gen='genericGray.css';  
}
echo"
<link rel=stylesheet type='text/css' href='style/".$gen."'>
";
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

case 'detail':
    $stream="<div style=overflow:auto; height:300px;>";
    $stream.="<table cellspacing='1' border=1 class='sortable'>
                <thead>
                    <tr class=rowheader>
                        <td align=center id=no>No.</td>
                        <td align=center id=tgl>".$_SESSION['lang']['nojurnal']."</td>
                        <td align=center id=noref>".$_SESSION['lang']['tanggal']."</td>
                        <td align=center id=ket>".$_SESSION['lang']['noakun']."</td>    
                        <td align=center id=ket>".$_SESSION['lang']['ket']."</td>    
                        <td align=center id=ket>".$_SESSION['lang']['jumlah']."</td> 
                    </tr>
                </thead>
                <tbody>";

    $s_transaksi = "select * from keu_jurnaldt_vw where noreferensi='".$notransaksi."' and noakun like '%8212%' order by noreferensi ";
                    
    $no=$jumlah=0;
    $res=$owlPDO->query($s_transaksi) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($r_transaksi=$res->fetch())
    {
        $no++;
        $stream.="<tr class=rowcontent >
                    <td align=center >".$no."</td>
                    <td align=right >".$r_transaksi['nojurnal']."</td>
                    <td align=right >".$r_transaksi['tanggal']."</td>
                    <td align=right >".$r_transaksi['noakun']."</td>
                    <td align=left >".$r_transaksi['keterangan']."</td>
                    <td align=right >".number_format($r_transaksi['jumlah'],2)."</td>
                </tr>";
        @$tot+=$r_transaksi['jumlah'];
                
    }

    $stream.="<tr><td colspan=5 align=center><b>".$_SESSION['lang']['jumlah']."</b></td>
            <td><b>".number_format(@$tot,2)."</b></td>
            </tr>";
    $stream.="</tbody></table>";
    echo $stream;
    
break;
}
if($proses=='excel'){
    $bg=" bgcolor=#DEDEDE";
    $brdr=1;
}
else{ 
    $bg="";
    $brdr=0;
}

if ($tipe!='') {
   $whr=" and jenistipe='".$tipe."'";
}
if ($tipewkt!='') {
    $whr.=" and tipewaktu='".$tipewkt."'";
}



if($proses=='excel'){
    $bgcoloraja="bgcolor=#DEDEDE ";
    $brdr=1;
    $stream="
    <table border=0>
    <tr><td align=center colspan=5><b>Laporan Transaksi Berulang</b></td></tr>
    <tr>
        <td align=left>".$_SESSION['lang']['namapt']."</td>
        <td>:".$unit."</td>
    </tr>
    </table>";
}


$stream="<div style=overflow:auto; height:300px;>";
$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
$stream.="Laporan Transaksi Berulang<br>";
$stream.="".$unit." - ".$nmorg[$unit]."<br>";
$stream.="Periode ".$periode1." s/d ".$periode2."<br><br>";
$stream.="<table cellspacing='1' border='".$brdr."' class='sortable'>
<thead>
<tr class=rowheader>
<td align=center id=no>No.</td>
<td align=center id=tgl>".$_SESSION['lang']['notransaksi']."</td>
<td align=center id=noref>".$_SESSION['lang']['kodeorganisasi']."</td>
<td align=center id=ket>".$_SESSION['lang']['tipe']." Transaksi</td>    
<td align=center id=ket>".$_SESSION['lang']['tipe']." Waktu</td>    
<td align=center id=kolom>Asignment</td>
<td align=center id=kolom>No akun Bank</td>
<td align=center id=kolom>Akun Debet</td>
<td align=center id=kolom>Akun Kredit</td>
<td align=center id=kolom>Tanggal Mulai</td>
<td align=center id=kolom>Tanggal Selesai</td>
<td align=center id=kolom>Jumlah</td>
<td align=center id=kolom>Jumlah Jurnal</td>
<td align=center id=kolom>Selisih</td>
</tr></thead>
<tbody>";

$optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjns=makeOption($dbname,'keu_5jenistagihan','kode,namajenis');
$optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$optno=makeOption($dbname,'keu_5akunbank','noakun,namabank');
$optNmBank=makeOption($dbname,'keu_5daftarbank','kodebank,namabank');

// $s_transaksi = "select a.notransaksi,a.kodeorg,a.jenistipe,a.tipewaktu,a.supplierid,a.noakun as noakun,a.noakun_debet,a.noakun_kredit,a.tanggalmulai,a.tanggalselesai,a.harga_barang,sum(jumlah) as totaljurnal from ".$dbname.".keu_transaksi_rutin a left join ".$dbname.".keu_jurnaldt b on a.notransaksi=b.noreferensi
//                 where a.kodeorg='".$unit."' ".$period." ".$whr." and b.noakun like '%8212%' group by a.notransaksi";

$s_transaksi = "select a.notransaksi,a.kodeorg,a.jenistipe,a.tipewaktu,a.supplierid,a.noakun as noakun,a.noakun_debet,a.noakun_kredit,a.tanggalmulai,a.tanggalselesai,a.harga_barang,sum(jumlah) as totaljurnal from ".$dbname.".keu_transaksi_rutin a left join ".$dbname.".keu_jurnaldt b on a.notransaksi=b.noreferensi
                where a.kodeorg='".$unit."' ".$period." ".$whr." and b.jumlah > 0 group by a.notransaksi";

$no=$jumlah=0;
$res=$owlPDO->query($s_transaksi) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($r_transaksi=$res->fetch())
{
    $no++;
    $stream.="<tr class=rowcontent tyle='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('".$r_transaksi['notransaksi']."',event);\">
                  <td align=center >".$no."</td>
                  <td align=right >".$r_transaksi['notransaksi']."</td>
                  <td align=left >".$optpt[$r_transaksi['kodeorg']]."</td>
                  <td align=left >".$optjns[$r_transaksi['jenistipe']]."</td>
                  <td align=left >".$r_transaksi['tipewaktu']."</td>
                  <td align=left >".$r_transaksi['supplierid']." (".$optsup[$r_transaksi['supplierid']].")</td>
                  <td align=left >".$r_transaksi['noakun']." (".$optNmBank[$optno[$r_transaksi['noakun']]].")</td>
                  <td align=right >".$r_transaksi['noakun_debet']."</td>
                  <td align=right >".$r_transaksi['noakun_kredit']."</td>
                  <td align=right >".$r_transaksi['tanggalmulai']."</td>
                  <td align=right >".$r_transaksi['tanggalselesai']."</td>
                  <td align=right >".number_format($r_transaksi['harga_barang'],2)."</td>
                  <td align=right >".number_format($r_transaksi['totaljurnal'],2)."</td>
                  <td align=right >".number_format($r_transaksi['totaljurnal']-$r_transaksi['harga_barang'],2)."</td>
              </tr>";

              @$totjum+=$r_transaksi['harga_barang'];
              @$totjumjur+=$r_transaksi['totaljurnal'];
              @$totselisih+=$r_transaksi['totaljurnal']-$r_transaksi['harga_barang'];
}


$stream.="<tr><td colspan=11 align=center><b>".$_SESSION['lang']['jumlah']."</b></td>
          <td><b>".number_format(@$totjum,2)."</b></td>
          <td><b>".number_format(@$totjumjur,2)."</b></td>
          <td><b>".number_format(@$totselisih,2)."</b></td>
          </tr>";
$stream.="</tbody></table>";

switch($proses){
case 'preview':
    echo $stream;
break;
case 'excel':   
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
    $dte=date("YmdHis");
    $nop_="transasksi_berulang";
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