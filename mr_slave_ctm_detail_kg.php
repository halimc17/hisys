<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

?>
    <link rel=stylesheet type=text/css href=style/generic.css>	
<?

$thn=checkPostGet('thn','');
$blok = checkPostGet('blok', '');
$tipe = checkPostGet('tipe', '');

$nmakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

if($tipe=='excel')
{
    $border="border=1";
}
else
{
    $border="border=0";
}


echo" Print Excel : <img style=cursor:pointer; "
. " onclick=\"parent.lihatdetail('".$blok."','".$thn."','excel',event)\" src=images/excel.jpg  
    title='MS.Excel'>
   ";

            $stream="<table ".$border." class=sortable cellspacing=1 style=width:100%>
             <thead>
                    <tr>
                          <td align=center>".$_SESSION['lang']['tahun']."</td>    
                          <td align=center>Tonase</td>
                          
                        </tr>  
                 </thead>
                 <tbody id=container>"; 
//=================================================
    $str=" select sum(totalkg) as jumlah,left(tanggal,4) as tahun from ".$dbname.".kebun_spb_vw "
            . " where blok='".$blok."' group by left(tanggal,4) ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
        $stream.="<tr class=rowcontent>
            <td align=left>".$bar['tahun']."</td>    
            <td align=right>".number_format($bar['jumlah']/1000,2)."</td>   
             </tr>"; 
        @$gtkg+=$bar['jumlah']/1000;
    }
        $stream.="<tr class=rowcontent>
            <td align=left>".$_SESSION['lang']['total']."</td>
            <td align=right>".number_format($gtkg,2)."</td>";    
          
  
if($tipe=='excel')
{
    //echo $stream;
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="detail_transaksi".$blok.$thn;
    if(strlen($stream)>0)
    {
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
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