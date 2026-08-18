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

$vhc = checkPostGet('vhc', '');
$per = checkPostGet('per', '');
$tipe = checkPostGet('tipe', '');

if($tipe=='excel')
{
    $border="border=1";
}
else
{
    $border="border=0";
}


echo" Print Excel : <img style=cursor:pointer; "
. " onclick=\"parent.detail('".$vhc."','".$per."','excel',event)\" src=images/excel.jpg  
    title='MS.Excel'>
   ";

            $stream="<table ".$border." class=sortable cellspacing=1 cellpadding=5 style=width:100%>
             <thead>
                    <tr>
                          <th align=center>".$_SESSION['lang']['nourut']."</th>  
                          <th align=center>".$_SESSION['lang']['notransaksi']."</th> 
                          <th align=center>".$_SESSION['lang']['tanggal']."</th> 
						  <th align=center>".$_SESSION['lang']['downtime']."</th> 
                          <th align=center>".$_SESSION['lang']['keterangan']."</th>  
                          <th align=center>".$_SESSION['lang']['kodebarang']."</th> 
                          <th align=center>".$_SESSION['lang']['namabarang']."</th> 
                          <th align=center>".$_SESSION['lang']['satuan']."</th>     
                          <th align=center>".$_SESSION['lang']['jumlah']."</th>       
                          <th align=center>".$_SESSION['lang']['keterangan']."</th>   
                          <th align=center>".$_SESSION['lang']['nik']."</th>     
                          <th align=center>".$_SESSION['lang']['namakaryawan']."</th>        
                        </tr>  
                 </thead>
                 <tbody id=container>"; 
//=================================================
      
    //$no=0;
    $str="select * from ".$dbname.".vhc_penggantianht where kodevhc='".$vhc."' and tanggal like '".$per."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())	
    {
        $notransaksi[$bar['notransaksi']]=$bar['notransaksi'];
        $tgl[$bar['notransaksi']]=$bar['tanggal'];
        $downtime[$bar['notransaksi']]=$bar['downtime'];
        $kerusakan[$bar['notransaksi']]=$bar['kerusakan'];
        
    }
    
    $str="select a.*,b.tanggal,b.kodevhc,c.namabarang from ".$dbname.".vhc_penggantiandt a "
            . "left join ".$dbname.".vhc_penggantianht b on a.notransaksi=b.notransaksi "
            . "left join ".$dbname.".log_5masterbarang c on a.kodebarang=c.kodebarang "
            . " where b.kodevhc='".$vhc."' and b.tanggal like '".$per."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())	
    {
        $barang[$bar['notransaksi']][]=$bar['kodebarang'];
        $sat[$bar['notransaksi']][]=$bar['satuan'];
        $jum[$bar['notransaksi']][]=$bar['jumlah'];
        $ket[$bar['notransaksi']][]=$bar['keterangan'];
        $namabarang[$bar['notransaksi']][]=$bar['namabarang'];
    }
    
    
    $str="select a.*,b.tanggal,b.kodevhc,c.nik,c.namakaryawan from ".$dbname.".vhc_penggantiandt_karyawan a "
            . "left join ".$dbname.".vhc_penggantianht b on a.notransaksi=b.notransaksi "
            . "left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid "
            . " where b.kodevhc='".$vhc."' and b.tanggal like '".$per."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())	
    {
        $kar[$bar['notransaksi']][]=$bar['karyawanid'];
        $nik[$bar['notransaksi']][]=$bar['nik'];
        $nama[$bar['notransaksi']][]=$bar['namakaryawan'];
    }
    
    if(!empty($notransaksi))
	{
		foreach($notransaksi as $notran)
		{
		   
			
			@$rowspanbarang=count($barang[$notran]);
			@$rowspankar=count($kar[$notran]);

			if($rowspanbarang > $rowspankar)
			{
					$rowspan=$rowspanbarang;
			}
			else
			{
					$rowspan=1;
			}
			
            @$no+=1;
			$stream.="
				<tr class=rowcontent>
					<td align=center valign=top rowspan=".$rowspan.">".$no."</td>   
					<td align=left valign=top rowspan=".$rowspan.">".$notran."</td>   
					<td align=center valign=top rowspan=".$rowspan.">".tanggalnormal($tgl[$notran])."</td>   
					<td align=center valign=top rowspan=".$rowspan.">".$downtime[$notran]."</td>   
					<td align=left valign=top rowspan=".$rowspan.">".$kerusakan[$notran]."</td>";
                    if(empty($barang[$notran]) and empty($kar[$notran]))
                    {
                        $stream.="<td valign=top  rowspan=".$rowspan."></td>";
                        $stream.="<td valign=top  rowspan=".$rowspan."></td>";
                        $stream.="<td valign=top  rowspan=".$rowspan."></td>";
                        $stream.="<td valign=top  rowspan=".$rowspan."></td>";
                        $stream.="<td valign=top  rowspan=".$rowspan."></td>";
                        $stream.="<td valign=top  rowspan=".$rowspan."></td>";
                        $stream.="<td valign=top  rowspan=".$rowspan."></td>";
                    }
                    else
                    {
                        for($i=0;$i<$rowspan;$i++) 
                        {
                        
                            if($i>0)
                            {
                                $stream.="<tr class=rowcontent>";
                            }
                            $stream.="<td valign=top align=left>".@$barang[$notran][$i]."</td>";
                            $stream.="<td valign=top align=left>".@$namabarang[$notran][$i]."</td>";
                            $stream.="<td valign=top align=left>".@$sat[$notran][$i]."</td>";
                            $stream.="<td valign=top align=left>".@$jum[$notran][$i]."</td>";
                            $stream.="<td valign=top align=left>".@$ket[$notran][$i]."</td>";
                            $stream.="<td valign=top align=left>".@$nik[$notran][$i]."</td>";
                            $stream.="<td valign=top align=left>".@$nama[$notran][$i]."</td>";
                            $stream.="</tr>";
                        } 
                    }
			$stream.="</tr>";
		}
	}
          
  
    
    
    
if($tipe=='excel')
{
    echo $stream;
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="detail_transaksi_".$vhc;
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