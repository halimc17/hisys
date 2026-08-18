<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');

    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  

  echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 


$tipe=$_GET['tipe'];
$kdpab=$_GET['kdpab'];

if($tipe=='excel')
{
    $border="border=1";
}
else
{
    $border="border=0";
}

$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$namaorganisasi=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

echo" Print Excel : <img style=cursor:pointer; "
. " onclick=\"parent.lihatDetail('".$kdpab."','excel',event)\" src=images/excel.jpg  
    title='MS.Excel'>
   ";

            $stream="<fieldset><legend>Tahapan</legend><table $border class=sortable cellspacing=1>
             <thead>
                    <tr>
                          <td align=center>".$_SESSION['lang']['nourut']."</td>
						<td align=center>Tahapan</td>
						<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>
						<td align=center>".$_SESSION['lang']['tanggalselesai']."</td>
                        </tr>  
                 </thead>
                 <tbody id=container>"; 
//=================================================
            
            
         
            
$no=0;
/*if(count($dat)<1)
{
        echo"<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{*/



$str="select * from ".$dbname.".pabrikasi_5masterdt where kodepabrikasi='".$kdpab."'";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$no+=1;
    $stream.="<tr class=rowcontent>
	<td align=center>".$no."</td>
	<td align=left>".$bar['tahapan']."</td>
	<td align=left>".tanggalnormal($bar['tanggalmulai'])."</td>
	<td align=left>".tanggalnormal($bar['tanggalselesai'])."</td>
	</tr>";
}

                  
$stream.="
     </table></fieldset>";	



$stream.="<fieldset><legend>Biaya</legend><table $border class=sortable cellspacing=1>";

$stream.="
    <thead>
        <tr class=rowheader>
            <td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>".$_SESSION['lang']['jurnal']."</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>".$_SESSION['lang']['noakun']."</td>
			<td align=center>".$_SESSION['lang']['namaakun']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']."</td>
			<td align=center>".$_SESSION['lang']['kodeorg']."</td>
			<td align=center>".$_SESSION['lang']['kegiatan']."</td>
        </tr>   
    </thead>
 <tbody>";


$str="select * from ".$dbname.".keu_jurnaldt_vw where kodeblok='".$kdpab."' and (noakun like '634%' and noakun!='6340199')";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$noby+=1;
    $stream.="<tr class=rowcontent>
	<td align=center>".$noby."</td>
	<td align=left>".$bar['nojurnal']."</td>
	<td align=left>".tanggalnormal($bar['tanggal'])."</td>
	<td align=left>".$bar['noakun']."</td>
	<td align=left>".$nmakun[$bar['noakun']]."</td>
	<td align=right>".number_format($bar['jumlah'])."</td>
	<td align=left>".$bar['kodeorg']."</td>
	<td align=left>".$nmkeg[$bar['kodekegiatan']]."</td>
	</tr>";
}
                  
$stream.="
     </table></fieldset>";	
         



$stream.="<fieldset><legend>Product</legend><table $border class=sortable cellspacing=1>";

$stream.="
    <thead>
        <tr class=rowheader>
            <td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>".$_SESSION['lang']['kodebarang']."</td>
			<td align=center>".$_SESSION['lang']['namabarang']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']."</td>
			<td align=center>".$_SESSION['lang']['hargasatuan']."</td>
        </tr>   
    </thead>
 <tbody>";
 
$str="select * from ".$dbname.".pabrikasi_cutoffdt where kodepabrikasi='".$kdpab."'";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$nopr+=1;
    $stream.="<tr class=rowcontent>
	<td align=center>".$nopr."</td>
	<td align=left>".$bar['kodebarang']."</td>
	<td align=left>".$nmbrg[$bar['kodebarang']]."</td>
	<td align=right>".number_format($bar['jumlah'])."</td>
	<td align=right>".number_format($bar['hargasatuan'])."</td>
	</tr>";
}

                  
$stream.="
     </table></fieldset>";		 
  
if($tipe=='excel')
{
    echo $stream;
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="detail_transaksi".$kodeorg._.$noakun._.$per;
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