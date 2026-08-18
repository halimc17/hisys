<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporanHariKerja').'</span><br>');
CLOSE_BOX();
OPEN_BOX();
echo"<div style='overflow:auto;height:550px;'>
<table class=sortable cellspacing=1 border=0 cellpadding=5>
     <thead>
	  <tr class=rowheader>
	   <th align=center rowspan=2>No</th>
	   <th align=center width=50px rowspan=2>".$_SESSION['lang']['unit']."</th>
	   <th align=center width=50px rowspan=2>".$_SESSION['lang']['budgetyear']."</th>
	   <th align=center width=50px rowspan=2>Jumlah hari kerja Tahun (HK)</th>
	   <th align=center width=50px rowspan=2>Jumlah hari setahun (HKS)</th>
	   <th align=center width=50px colspan=4>Hari libur (HL)</th>
	   <th align=center width=50px rowspan=2>Hari Absensi (HA)</th>
	   <th align=center width=50px rowspan=2>Cuti tahunan (ct)</th>
	   <th align=center width=50px colspan=5>Cuti sakit/ijin (sim)</th>
	   <th align=center width=50px rowspan=2>Jumlah hari kerja efektif (HKE)</th>
	   <th align=center width=50px rowspan=2>Prosentase hari kerja efektif (% HKE)</th>
	   
	  </tr>
	  <tr class=rowheader style=align:center>
	   <th align=center width=50px>Hari Minggu</th>
	   <th align=center width=50px>Hari Libur</th>
	   <th align=center width=50px>Hari Libur Minggu</th>
	   <th align=center width=50px>Total</th>
	   <th align=center width=50px>S1/S2</th>
	   <th align=center width=50px>H1/H2</th>
	   <th align=center width=50px>P1/P3</th>
	   <th align=center width=50px>Mangkir</th>
	   <th align=center width=50px>Total</th>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 
		$str="select * from ".$dbname.".bgt_hk  order by tahunbudget desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$thrlb=$bar['hrminggu']+$bar['hrlibur']-$bar['hrliburminggu'];
			$thke=$bar['harisetahun']-$thrlb;
			$tsim=$bar['s1s2']+$bar['h1h2']+$bar['p1p3']+$bar['mangkir'];
			$tothke=$thke-($bar['jlhcuti']+$tsim);
			$persen=$tothke/$bar['harisetahun']*100;
			
			$no+=1;	
			echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center>".$bar['unit']."</td>
			<td align=center>".$bar['tahunbudget']."</td>
			<td align=right>".$thke."</td>
			<td align=right>".$bar['harisetahun']."</td>
			<td align=right>".$bar['hrminggu']."</td>
			<td align=right>".$bar['hrlibur']."</td>
			<td align=right>".$bar['hrliburminggu']."</td>
			<td align=right>".$thrlb."</td>
			<td align=right>".($bar['jlhcuti']+$tsim)."</td>
			<td align=right>".$bar['jlhcuti']."</td>
			<td align=right>".$bar['s1s2']."</td>
			<td align=right>".$bar['h1h2']."</td>
			<td align=right>".$bar['p1p3']."</td>
			<td align=right>".$bar['mangkir']."</td>
			<td align=right>".$tsim."</td>
			<td align=right>".$tothke."</td>
			<td align=right>".number_format($persen,2)."</td>
			
			</tr>";	
		}   
echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table></div>";   
CLOSE_BOX();
echo close_body();
?>