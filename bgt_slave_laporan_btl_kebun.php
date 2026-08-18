<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kodeorg=$_POST['kodeorg'];
$thnbudget=$_POST['thnbudget'];
#ambil luas kebun
@$luas=0;
//$str="select sum(hathnini) as luas from ".$dbname.".bgt_areal_per_afd_vw 
//      where tahunbudget=".$thnbudget." and afdeling like '".$kodeorg."%' ";
$str="select sum(hathnini) as luas,thntnm from ".$dbname.".bgt_blok where 
      kodeblok like '".$kodeorg."%' and tahunbudget='".$thnbudget."' and statusblok in ('TBM','TM') group by tahunbudget,kodeblok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $luas+=$bar->luas;
}

echo"
	<table class=sortable cellpadding=5 cellspacing=1 border=0>
	 <thead>
	 <tr class=rowheader>
			   <th align=center>".$_SESSION['lang']['nourut']."</th>
			   <th align=center>".$_SESSION['lang']['noakun']."</th>
			   <th align=center>".$_SESSION['lang']['namaakun']."</th>
			   <th align=center>".$_SESSION['lang']['luas']."</th>
			   <th align=center>".$_SESSION['lang']['jumlahrp']."</th>
			   <th align=center>".$_SESSION['lang']['rpperha']."</th>  
			   <th align=center>01(Rp)</th>
			   <th align=center>02(Rp)</th>
			   <th align=center>03(Rp)</th>
			   <th align=center>04(Rp)</th>
			   <th align=center>05(Rp)</th>
			   <th align=center>06(Rp)</th>
			   <th align=center>07(Rp)</th>
			   <th align=center>08(Rp)</th>
			   <th align=center>09(Rp)</th>
			   <th align=center>10(Rp)</th>
			   <th align=center>11(Rp)</th>
			   <th align=center>12(Rp)</th>
			 </tr>
	 </thead>
";

$str="select a.*, sum(rupiah) as rupiah ,b.namaakun,
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
      where a.kodebudget='UMUM' and tahunbudget=".$thnbudget." and a.kodeorg='".$kodeorg."' group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$rpperha=0;
$tt=$t01=$t02=$t03=$t04=$t05=$t06=$t07=$t08=$t09=$t10=$t11=$t12=0;
while($bar=$res->fetch())
{
    @$rpperha=$bar->rupiah/$luas;
    $no+=1;
    echo"<tr class=rowcontent>
           <td>".$no."</td>
           <td>".$bar->noakun."</td>
           <td>".$bar->namaakun."</td>
           <td align=right>".number_format($luas,2,'.',',')."</td>
           <td align=right>".number_format($bar->rupiah,0,'.',',')."</td>
           <td align=right>".number_format(fixnan($rpperha),0,'.',',')."</td>    
           <td align=right>".number_format($bar->rp01,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp02,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp03,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp04,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp05,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp06,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp07,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp08,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp09,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp10,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp11,0,'.',',')."</td>
           <td align=right>".number_format($bar->rp12,0,'.',',')."</td>
         </tr>";
    $tt+=$bar->rupiah;
    $t01+=$bar->rp01;
    $t02+=$bar->rp02;
    $t03+=$bar->rp03;
    $t04+=$bar->rp04;
    $t05+=$bar->rp05;
    $t06+=$bar->rp06;
    $t07+=$bar->rp07;
    $t08+=$bar->rp08;
    $t09+=$bar->rp09;
    $t10+=$bar->rp10;
    $t11+=$bar->rp11;
    $t12+=$bar->rp12;
    
}
    echo"<tr class=rowcontent>
           <td colspan=4>TOTAL</td>
           <td align=right>".number_format($tt,0,'.',',')."</td>
           <td align=right>".@number_format($tt/$luas,0,'.',',')."</td>    
           <td align=right>".number_format($t01,0,'.',',')."</td>
           <td align=right>".number_format($t02,0,'.',',')."</td>
           <td align=right>".number_format($t03,0,'.',',')."</td>
           <td align=right>".number_format($t04,0,'.',',')."</td>
           <td align=right>".number_format($t05,0,'.',',')."</td>
           <td align=right>".number_format($t06,0,'.',',')."</td>
           <td align=right>".number_format($t07,0,'.',',')."</td>
           <td align=right>".number_format($t08,0,'.',',')."</td>
           <td align=right>".number_format($t09,0,'.',',')."</td>
           <td align=right>".number_format($t10,0,'.',',')."</td>
           <td align=right>".number_format($t11,0,'.',',')."</td>
           <td align=right>".number_format($t12,0,'.',',')."</td>
         </tr>
         <tr class=rowcontent>
           <td colspan=18 style='color:red;'>Luas : ".number_format($luas,0,'.',',')." Ha (Total Planted) TM & TBM</td>
         </tr></table>";
?>