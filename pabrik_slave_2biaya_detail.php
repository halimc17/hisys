<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  

  echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 

// $kodeorg=$_GET['kodeorg'];
// $per=$_GET['per'];
// $noakun=$_GET['noakun'];
// $tipe=$_GET['tipe'];
// $unit=$_GET['unit'];

$kodeorg = checkPostGet('kodeorg','');
$per = checkPostGet('per','');
$noakun = checkPostGet('noakun','');
$tipe = checkPostGet('tipe','');
$unit = checkPostGet('unit','');


// exit("Error:$tipe");

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
. " onclick=\"parent.lihatDetail('".$noakun."','".$kodeorg."','".$per."','".$unit."','excel',event)\" src=images/excel.jpg  
    title='MS.Excel'>
   ";

            $stream="<table $border class=sortable cellspacing=1>
             <thead>
                    <tr>
                          <td align=center width=50>".$_SESSION['lang']['nourut']."</td>
                          <td align=center>".$_SESSION['lang']['tanggal']."</td>    
                          <td align=center>".$_SESSION['lang']['noakun']."</td>
                          <td align=center>".$_SESSION['lang']['namaakun']."</td> 
                          <td align=center>".$_SESSION['lang']['keterangan']."</td> 
                          <td align=center>".$_SESSION['lang']['noreferensi']."</td>  
                          <td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>  
                          <td align=center>".$_SESSION['lang']['namaorganisasi']."</td>     
                          <td align=center>".$_SESSION['lang']['debet']."</td>     
                          <td align=center>".$_SESSION['lang']['kredit']."</td>         
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



	if($kodeorg==''){
		$str="select * from ".$dbname.".keu_jurnaldt_vw where  (kodeblok='' || kodeblok is null) and kodeorg='".$unit."' and periode='".$per."'"
            . " and  noakun='".$noakun."' ";
			
	}
	else if (strlen($kodeorg)==4){
		$str="select * from ".$dbname.".keu_jurnaldt_vw where kodeblok = '".$kodeorg."' and periode='".$per."'"
            . " and  noakun='".$noakun."' ";
			
	}
	else{
		$str="select * from ".$dbname.".keu_jurnaldt_vw where kodeblok like '".$kodeorg."%' and periode='".$per."'"
            . " and  noakun='".$noakun."' ";
			
	}
	
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
        $no+=1;
        $stream.="<tr class=rowcontent>
              <td align=center>".$no."</td>
              <td align=left>".tanggalnormal($bar['tanggal'])."</td>        
              <td align=right>".$bar['noakun']."</td>   
              <td align=left>".$namaakun[$bar['noakun']]."</td>     
              <td align=left>".$bar['keterangan']."</td>     
              <td align=left>".$bar['noreferensi']."</td>         
              <td align=left>".$bar['kodeblok']."</td> 
              <td align=left>".$namaorganisasi[$bar['kodeblok']]."</td>    
              <td align=right>".number_format($bar['debet'],2)."</td>       
              <td align=right>".number_format($bar['kredit'],2)."</td>        
             </tr>"; 
        @$totdb+=$bar['debet'];
        @$totkr+=$bar['kredit'];
    }
    $stream.="<tr class=rowcontent>
                <td colspan=8 align=right>".$_SESSION['lang']['total']."</td>
                <td align=right>".number_format($totdb,2)."</td>    
                <td align=right>".number_format($totkr,2)."</td>        
    ";
          
  
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