<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

        $pt=$_GET['pt'];
        $gudang=$_GET['gudang'];
        $periode=$_GET['periode'];
    $stream='';
//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='ALL';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $namapt=strtoupper($bar->namaorganisasi);
}
$whr=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
if($gudang!=''){
        $whr=" and kodeorg='".$gudang."'";
}
$str="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$periode."' ".$whr."";
$currstart='';
$currend='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
$str="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$periodeKmrn."' ".$whr."";
$paststart='';
$pastend='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $paststart=$bar->tanggalmulai;
    $pastend=$bar->tanggalsampai;
}
$tgl="tanggal between '".$currstart."' and '".$currend."'";
$tgl2="tanggal between '".$paststart."' and '".$pastend."'";
$dtArus=array();
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='CASH FLOW DIRECT' order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
        $dtArus[]=$bar;
}
$rpdt=array();
$str1="select sum(debet) as debet,sum(kredit) as kredit,noaruskas,noakun from ".$dbname.".keu_jurnaldt_vw  where ".$tgl." ".$whr." and nojurnal not like '%/M/%'  group by noaruskas";//noakun,
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rstr1=$res->fetch()){
        $rpdbt[$rstr1['noaruskas']]+=$rstr1['debet'];
        $rpkrt[$rstr1['noaruskas']]+=$rstr1['kredit'];
        $dbtperakun[$rstr1['noaruskas']]+=$rstr1['debet'];
        $krtperakun[$rstr1['noaruskas']]+=$rstr1['kredit'];
        $lstAkun[$rstr1['noaruskas']]=$rstr1['noakun'];
}
$str2="select sum(jumlah) as jumlah,noaruskas,noakun from ".$dbname.".keu_jurnaldt  where ".$tgl2." ".$whr." and  nojurnal not like '%/M/%' group by noaruskas";
$res=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rstr1=$res->fetch()){
        $stAwal[$rstr1['noaruskas']]+=$rstr1['jumlah'];
        $stAwalAkn[$rstr1['noaruskas']]+=$rstr1['jumlah'];
        $lstAkun[$rstr1['noaruskas']]=$rstr1['noakun'];
}
$sAkun="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7";
$res=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$res->fetch()){
        $nmAkun[$rAkun['noakun']]=$rAkun['namaakun'];
}

$stream.=$_SESSION['lang']['aruskas'].":<br>
<table border=1>
<tr>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nomor']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoawal']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['debet']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kredit']."</td>
<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoakhir']."</td>
</tr>";
foreach($dtArus as $lstArus){
        if($lstArus['tipe']=='Header'){
                $stream.="<tr>
                          <td colspan=6>".$lstArus['keterangandisplay']."</td>
                        ";
                $stream.="</tr>"; 		
        }
        if($lstArus['tipe']=='Detail'){
                $stream.="<tr class=rowcontent>
                          <td>".$lstArus['nourut']."</td>
                          <td>".$lstArus['keterangandisplay']."</td>
                          <td align=right>".number_format($stAwal[$lstArus['nourut']],2,'.',',')."</td>
                          <td align=right>".number_format($rpdbt[$lstArus['nourut']],2,'.',',')."</td>
                          <td align=right>".number_format($rpkrt[$lstArus['nourut']],2,'.',',')."</td>";
                $endbalance=$stAwal[$lstArus['nourut']]+$rpdbt[$lstArus['nourut']]-$rpkrt[$lstArus['nourut']];
                $stream.="<td align=right>".number_format($endbalance,2,'.',',')."</td>
                        </tr>";
        }

 }
        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
        // echo $stream;
        // exit();
$nop_="ArusKasLangsung";
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
        closedir($handle);
}
?>