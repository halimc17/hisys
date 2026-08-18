<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
$station =$_GET['station'];
$kdbudget=$_GET['kdbudget'];
$tahun   =$_GET['tahun'];
$bln   =$_GET['bln'];
$fisik ="fis".substr($bln, 2,2);


$str="select a.*,b.namabarang,c.nama from ".$dbname.".bgt_budget_detail a left join 
      ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang left join 
      ".$dbname.".bgt_kode c on a.kodebudget=c.kodebudget
      where a.kodeorg like '".$station."%' and a.kodebudget like '".$kdbudget."%' 
      and a.tahunbudget=".$tahun;

echo"Unit:".$station." Tahun Budget:".$tahun."
     <table class=sortable cellspacing=1 cellpadding=5 border=0>
     <thead>
         <tr class=rowheader>
           <th align=center>".$_SESSION['lang']['nourut']."</th>
           <th align=center>".$_SESSION['lang']['mesin']."</th>
           <th align=center>".$_SESSION['lang']['kodeabs']."</th>
           <th align=center>".$_SESSION['lang']['namabarang']."</th>    
           <th align=center>".$_SESSION['lang']['jumlah']."</th> 
           <th align=center>".$_SESSION['lang']['satuan']."</th>                
           <th align=center>".$_SESSION['lang']['jumlahrp']."</th>     
         </tr>
         </thead>
         <tbody>";
$tj=0;
$no=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{

    $no+=1;
    echo"<tr class=rowcontent>
           <td>".$no."</td>
           <td>".getNamaOrg($bar->kodeorg)."</td>
           <td>".$bar->nama."</td>
           <td>".$bar->namabarang."</td>
           <td align=right>".number_format($bar->$fisik,2,'.',',')."</td>
           <td>".$bar->satuanj."</td>     
           <td align=right>".number_format($bar->$bln,0,'.',',')."</td>   
         </tr>";    
		 
		$total+= $bar->$bln;
		$tj+= $bar->$fisik;
}

echo"<tr class=rowcontent>
           <td colspan=4>TOTAL</td>
		   <td align=right>".number_format($tj,2,'.',',')."</td>   
           <td></td>
		   <td align=right>".number_format($total,0,'.',',')."</td>   
		   ";
echo"</tbody>
		 <tfoot>
		 </tfoot>
		 </table>";