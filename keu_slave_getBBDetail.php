<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

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
$noakun= checkPostGet('noakun','');   
$periode= checkPostGet('periode','');   
$periode1= checkPostGet('periode1','');   
$lmperiode= checkPostGet('lmperiode','');   
$pt= checkPostGet('pt','');   
$gudang= checkPostGet('gudang','');   
$regional= checkPostGet('regional','');   
$revisi= checkPostGet('revisi','');   

$nmSup=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
	
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

if($regional != ''){
	@$whrRegional = " and a.kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'))";
	@$whrRegionaldatakaryawan = " and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'))";
}



#= buat query nama karyawan
$str="select * from  ".$dbname.".datakaryawan where 
karyawanid in (select nik from ".$dbname.".keu_jurnaldt_vw 
where periode>='".$periode."' and periode<='".$periode1."' and 
noakun='".$noakun."' and revisi <= '".$revisi."' ".@$whrRegionaldatakaryawan.")";
// echo $str;
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar= $res->fetch()){
	$namakaryawan[$bar->karyawanid]=$bar->namakaryawan;
	$nikkaryawan[$bar->karyawanid]=$bar->nik;
}






//ambil mutasi-----------------------
if($gudang=='' and $pt=='')
{
    $str="select a.kodesupplier,a.nojurnal,a.jumlah,a.keterangan,a.tanggal,a.noreferensi,a.kodevhc,a.nik,a.kodeblok,b.namapenerima,c.namakaryawan 
        from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".log_transaksiht b on a.noreferensi = b.notransaksi
        left join ".$dbname.".datakaryawan c on b.namapenerima = c.karyawanid
        where a.periode>='".$periode."' and a.periode<='".$periode1."' and a.noakun='".$noakun."' and a.revisi <= '".$revisi."' ".@$whrRegional." order by a.tanggal asc";
}
else if($gudang=='' and $pt!='')
{
    $str="select a.kodesupplier,a.nojurnal,a.jumlah,a.keterangan,a.tanggal,a.noreferensi,a.kodevhc,a.nik,a.kodeblok,b.namapenerima,c.namakaryawan
        from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".log_transaksiht b on a.noreferensi = b.notransaksi
        left join ".$dbname.".datakaryawan c on b.namapenerima = c.karyawanid
        where a.periode>='".$periode."' and a.periode<='".$periode1."' and a.kodeorg in(select kodeorganisasi 
        from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)
        and a.noakun='".$noakun."' and a.revisi <= '".$revisi."' ".@$whrRegional." order by a.tanggal asc";
}
else
{
    $str="select a.kodesupplier,a.nojurnal,a.jumlah,a.keterangan,a.tanggal,a.noreferensi,a.kodevhc,a.nik,a.kodeblok,b.namapenerima,c.namakaryawan
        from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".log_transaksiht b on a.noreferensi = b.notransaksi
        left join ".$dbname.".datakaryawan c on b.namapenerima = c.karyawanid
        where a.periode>='".$periode."' and a.periode<='".$periode1."' and a.kodeorg ='".$gudang."'
        and a.noakun='".$noakun."' and a.revisi <= '".$revisi."' ".@$whrRegional." order by a.tanggal asc";   
}   

//=================================================
echo"
     <img onclick=\"parent.detailKeExcel(event,'keu_slave_getBBDetail.php?type=excel&noakun=".$noakun."&periode=".$periode."&periode1=".$periode1."&lmperiode=".$lmperiode."&pt=".$pt."&gudang=".$gudang."&revisi=".$revisi."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
     ";
if(isset($_GET['type']) and $_GET['type']=='excel')$border=1; else $border=0;
$stream="<table class=sortable cellpadding=5 border=".$border." cellspacing=1>
    <thead>
    <tr class=rowcontent>
        <td align=center>No</td>
        <td align=center>".$_SESSION['lang']['nojurnal']."</td>
        <td align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=center>".$_SESSION['lang']['noakun']."</td>
        <td align=center>".$_SESSION['lang']['keterangan']."</td>
        <td align=center>".$_SESSION['lang']['debet']."</td>
        <td align=center>".$_SESSION['lang']['kredit']."</td>
        <td align=center>".$_SESSION['lang']['karyawan']."</td>
        <td align=center>".$_SESSION['lang']['karyawan']."<br>(Penerima Gudang)</td>
        <td align=center>".$_SESSION['lang']['supplier']."</td>
        <td align=center>".$_SESSION['lang']['mesin']."</td>
        <td align=center>".$_SESSION['lang']['blok']."</td>
    </tr>
    </thead>
    <tbody>";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$tdebet=0;
$tkredit=0;
while($bar= $res->fetch())
{
    $no+=1;
    $debet=0;
    $kredit=0;
    if($bar->jumlah>0)
         $debet= $bar->jumlah;
    else
         $kredit= $bar->jumlah*-1;

    $noref=$bar->noreferensi;
    if(trim($noref)=='')$noref=$bar->nojurnal;
if(isset($_GET['type']) and $_GET['type']=='excel')$tampiltanggal=$bar->tanggal; else $tampiltanggal=tanggalnormal($bar->tanggal);
    $penerima=$bar->namakaryawan;
    if(substr($bar->noreferensi,11,2)=='-G'){ // kalo transaksi gudang
        $penerima=$bar->namapenerima;
        if(substr($bar->namapenerima,0,3)=='000')$penerima=$bar->namakaryawan;
    }
    $stream.="<tr class=rowcontent>
           <td align=center>".$no."</td>
           <td>".$bar->nojurnal."</td>               
           <td>".$noref."</td>               
           <td>".$tampiltanggal."</td>    
           <td>".$noakun."</td>    
           <td>".$bar->keterangan."</td>
           <td align=right>".number_format($debet,2)."</td>
           <td align=right>".number_format($kredit,2)."</td>  
           <td>".$nikkaryawan[$bar->nik]." - ".$namakaryawan[$bar->nik]."</td>
           <td>".$penerima."</td>
		   <td>".@$nmSup[$bar->kodesupplier]."</td>
           <td>".$bar->kodevhc."</td>
           <td>".$bar->kodeblok."</td>  
        </tr>";
    $tdebet+=$debet;
    $tkredit+=$kredit;    
} 
$stream.="<tr class=rowcontent>
    <td colspan=6>TOTAL</td>
    <td align=right>".number_format($tdebet,2)."</td>
    <td align=right>".number_format($tkredit,2)."</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>";  
$stream.="</tbody><tfoot></tfoot></table>";
if(isset($_GET['type']) and $_GET['type']=='excel')
{
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="Detail_jurnal_".$_GET['gudang']."_".$_GET['periode'];
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
}
else
{
   echo $stream;
}    
       
?>