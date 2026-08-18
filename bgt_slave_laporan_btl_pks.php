<?php
require_once('master_validation.php');
require_once('config/connection.php');

$kodeorg=$_POST['kodeorg'];
$thnbudget=$_POST['thnbudget'];
#ambil produksi pks
$prd=0;
$str="select sum(kgcpo) as cpo,sum(kgkernel) as kernel,sum(kgolah)  as tbs from ".$dbname.".bgt_produksi_pks_vw 
      where tahunbudget=".$thnbudget." and millcode = '".$kodeorg."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $prd=$bar->cpo+$bar->kernel;
    $totTbs=$bar->tbs;
}

$str="select a.*,b.namaakun, sum(rupiah) as rupiah,
	sum(rp01) as rp01,
	sum(rp02) as rp02,
	sum(rp03) as rp03,
	sum(rp04) as rp04,
	sum(rp05) as rp05,
	sum(rp06) as rp06,
	sum(rp07) as rp07,
	sum(rp08) as rp08,
	sum(rp09) as rp09,
	sum(rp10) as rp10,
	sum(rp11) as rp11,
	sum(rp12) as rp12 
from ".$dbname.".bgt_budget_detail a left join
".$dbname.".keu_5akun b on a.noakun=b.noakun
where a.kodebudget='UMUM' and tahunbudget=".$thnbudget." and a.kodeorg='".$kodeorg."' group by noakun order by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$rpperha=0;
$rptbs=0;
$str2="select sum(kgolah) as tbs,sum(kgcpo) as cpo,sum(kgkernel) as kernel from ".$dbname.".bgt_produksi_pks_vw 
      where tahunbudget=".$thnbudget." and millcode = '".$kodeorg."'";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_OBJ);
while($bar2=$res2->fetch())
{
    $tbs=$bar2->tbs;
    $cpo=$bar2->cpo;
    $pk=$bar2->kernel;
    
    $totTbs=$bar2->tbs;
    $prd=$bar2->cpo+$bar2->kernel;
    $totCpo=$bar2->cpo;
    $totKer=$bar2->kernel;
}
$oil=$cpo+$pk;
$stream="<fieldset style=float:left>
<table class=sortable cellspacing=1 border=0 cellpadding=5>
     <thead>
         <tr class=rowheader>
           <th align=center>".$_SESSION['lang']['tbsdiolah']."</th>
           <th align=center>Palm Product</th>
           <th align=center>".$_SESSION['lang']['cpo']."</th>                  
           <th align=center>".$_SESSION['lang']['kernel']."</th> 
         </tr>
         </thead>
         <tbody>
         <tr class=rowcontent>
           <td align=right>".@number_format($totTbs/1000,0,".",",")."</td>
           <td align=right>".@number_format($prd/1000,0,".",",")."</td>
           <td align=right>".@number_format($totCpo/1000,0,".",",")."</td>
           <td align=right>".@number_format($totKer/1000,0,".",",")."</td>    
         </tr>     
     </tbody>
     <tfoot>
     </tfoot>
     </table>
     </fieldset>"; 
$stream.="<div style=clear:both><div>
		 <table class=sortable cellspacing=1 border=0' cellpadding=5>
	     <thead>
		 <tr class=rowheader>
                   <th align=center>".$_SESSION['lang']['nourut']."</th>
                   <th align=center>".$_SESSION['lang']['noakun']."</th>
                   <th align=center>".$_SESSION['lang']['namaakun']."</th>
                   <th align=center>".$_SESSION['lang']['jumlahrp']."</th>
                   <th align=center>".$_SESSION['lang']['rpperkg']."<br>PP</th>
                   <th align=center>".$_SESSION['lang']['rpperkg']."<br>TBS</th>
                   <th align=center>Jan</th>
                   <th align=center>Feb</th>
                   <th align=center>Mar</th>
                   <th align=center>Apr</th>
                   <th align=center>Mei</th>
                   <th align=center>Jun</th>
                   <th align=center>Jul</th>
                   <th align=center>Ags</th>
                   <th align=center>Sep</th>
                   <th align=center>Okt</th>
                   <th align=center>Nop</th>
                   <th align=center>Des</th>
                 </tr>
		 </thead>
		 <tbody>"; 

while($bar=$res->fetch())
{
    $prd=$cpo+$pk;
    @$rpperha=$bar->rupiah/$prd;
    @$rptbs=$bar->rupiah/$totTbs;
    $no+=1;
    $stream.="<tr class=rowcontent>
           <td>".$no."</td>
           <td>".$bar->noakun."</td>
           <td>".$bar->namaakun."</td>
           <td align=right>".@number_format($bar->rupiah,0,'.',',')."</td>
           <td align=right>".@number_format($rpperha,7,'.',',')."</td>  
           <td align=right>".@number_format($rptbs,7,'.',',')."</td> 
           <td align=right>".@number_format($bar->rp01,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp02,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp03,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp04,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp05,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp06,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp07,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp08,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp09,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp10,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp11,0,'.',',')."</td>
           <td align=right>".@number_format($bar->rp12,0,'.',',')."</td>
         </tr>";
    $totRup+=$bar->rupiah;
    $grTotRp+=$rpperha;
    $grTotTbs+=$rptbs;
    $tot[1]+=$bar->rp02;$tot[2]+=$bar->rp02;$tot[3]+=$bar->rp03;
    $tot[4]+=$bar->rp04;$tot[5]+=$bar->rp05;$tot[6]+=$bar->rp06;
    $tot[7]+=$bar->rp07;$tot[8]+=$bar->rp08;$tot[9]+=$bar->rp09;
    $tot[10]+=$bar->rp10;$tot[11]+=$bar->rp11;$tot[12]+=$bar->rp12;
}
$stream.="<tr><td colspan=3>".$_SESSION['lang']['total']."</td>";
$stream.="<td>".@number_format($totRup,0)."</td><td>".@number_format($grTotRp,7)."</td><td>".@number_format($grTotTbs,7)."</td>";
for($rd=1;$rd<=12;$rd++)
{
    $stream.="<td>".@number_format($tot[$rd],0)."</td>";
}
$stream.="</tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo $stream; 
?>