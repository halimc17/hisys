<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

include_once('lib/zPosting.php');
include_once('lib/zJournal.php');


$method =checkPostGet('method','');
$pt     =checkPostGet('pt','');
$unit   =checkPostGet('unit','');
$per    =checkPostGet('per','');
$tipe   =checkPostGet('tipe','');

$tmpPeriod = explode('-',$per);
$tahunbulan= implode("",$tmpPeriod);
$tahun     = $tmpPeriod[0];
$bulan     = $tmpPeriod[1];

$karyawanid=checkPostGet('karyawanid','');

$nmakun    =makeOption($dbname,'keu_5akun','noakun,namaakun');
$optpt     =makeOption($dbname,'organisasi','kodeorganisasi,induk');
$pt        =$optpt[$unit];


#= jurnal 1
$akundb=checkPostGet('akundb','');
$akunkr=checkPostGet('akunkr','');
$rpdb  =checkPostGet('rpdb','');
$rpkr  =checkPostGet('rpkr','');

$akundb2=checkPostGet('akundb2','');
$akunkr2=checkPostGet('akunkr2','');
$rpdb2  =checkPostGet('rpdb2','');
$rpkr2  =checkPostGet('rpkr2','');

$akundb3=checkPostGet('akundb3','');
$akunkr3=checkPostGet('akunkr3','');
$rpdb3  =checkPostGet('rpdb3','');
$rpkr3  =checkPostGet('rpkr3','');
	
$param=$_POST;

# Laporan
$namalaporan = "ALOKASI GC TM UNIT";
$kodeJurnal = "OHTM";

$stream="";
switch($method){
	case'previewori':
		#= ambil saldo awal
		$stream.="<button class=mybutton onclick=savehpp()>".$_SESSION['lang']['proses']."</button><br><br>";
	
		#= cek setup_blok_tahunan sudah dilakukan / belum
		$str="SELECT count(*) as jumlah FROM ".$dbname.".`setup_blok_tahunan` WHERE   `tahun` ='".str_replace('-','',$per)."' and  substr(kodeorg,1,4)='".$unit."' and statusblok in ('TBM') group by statusblok";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
		$jumlahsetupblok=$bar['jumlah'];
			
		if($jumlahsetupblok=='0' || $jumlahsetupblok==''){
			exit("Warning: Unit ini tidak memiliki blok tbm atau
			blok tahunan untuk periode ".$per." belum terdaftar, harap lakukan proses tutup buku areal statement di : kebun->proses->tutup aresta.");
		}				
		
		#= luas tbm
		$str="SELECT sum(luasareaproduktif) as luas,statusblok FROM ".$dbname.".`setup_blok_tahunan` WHERE   `tahun` ='".str_replace('-','',$per)."' and substr(kodeorg,1,4)='".$unit."' group by statusblok";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			if($bar['statusblok']=='TM'){
				@$luasb1+=$bar['luas'];
			}
			if($bar['statusblok']=='TBM'){
				@$luasb2+=$bar['luas'];
			}
			@$tluas+=$bar['luas'];
		}

		$rpa = 0; $noakundet=$biayadet=[];
		$ttlalktbm = 0;
		$luasb3=$luasb1+$luasb2;
		

		$str = "select sum(jumlah) as jumlah, noakun from ".$dbname.".keu_jurnaldt_vw where periode='".$per."' and  kodeorg='".$unit."' and substr(noakun,1,3) in ('711','721','732') and nojurnal not like '%HPP%' group by noakun";
		$res = fetchdata($str);
		foreach($res as $bar){
			$rpa+=$bar['jumlah'];
			$noakundet[$bar['noakun']]=$bar['noakun'];
			//$biayadet[$bar['noakun']]+=$bar['jumlah'];
			$biayadet[$bar['noakun']]+=round($luasb2/$luasb3*$bar['jumlah']);
			$ttlalktbm+=round($luasb2/$luasb3*$bar['jumlah']);

		}
	
		
		//$rpb2=round($luasb2/$luasb3*$rpa);
		$rpb2=$ttlalktbm;
		$rpb1=$rpa-$rpb2;
		$rpb3=$rpb1+$rpb2;
		
		$akundebet['1']='1260191';
		$akunkredit['1']='7120198';
		$akundebet['2']='1260193';
		$akunkredit['2']='7150198';
		$akundebet['3']='1260194';
		$akunkredit['3']='7150398';
		
		
		
		$stream.= "<table class=sortable cellspacing=1 cellpadding=5>";
		$stream.="<thead>";
		
		$stream.="<tr class=rowheader>";	
		$stream.="<th bgcolor=#CCCCCC align=center colspan=2>".$_SESSION['lang']['keterangan']."</th>";  
		$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['luas']."</th>";  
		$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rp']."</th>";  
		$stream.="<th align=center></th>";  
		$stream.="<th align=center></th>";  				
		$stream.="</tr>"; 
		$stream.="</thead>";

		## ALOKASI DIPECAH MENJADI 3 SESUAI MEETING TANGGAL 12 SEPT 2022
		## ALOKASI KHUSUS GC

		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>A</b></td>";  
		$stream.="<td align=left><b>Jenis Biaya</b></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>UMUM (GC)</td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=right>".number_format($rpa,2)."</td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left colspan=6>&nbsp;</td>";  
		$stream.="</tr>"; 
	
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>B</b></td>";  
		$stream.="<td align=left><b>Alokasi Biaya Umum</b></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>TM</td>";  
		$stream.="<td align=right>".number_format($luasb1,2)."</td>";
		$stream.="<td align=right>".number_format($rpb1,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;2</td>";  
		$stream.="<td align=left>TBM</td>";  
		$stream.="<td align=right>".number_format($luasb2,2)."</td>";
		$stream.="<td align=right>".number_format($rpb2,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
	
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left></td>";  
		$stream.="<td align=left>".$_SESSION['lang']['total']."</td>";  
		$stream.="<td align=right>".number_format($luasb3,2)."</td>";
		$stream.="<td align=right>".number_format($rpb3,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$rpdbb1a=$rpa4;
		$rpdbb1b=0;
		$rpdbb1c=0;
		$rpdbb1d=0;
		
		$rpkrb1a=0;
		$rpkrb1b=$rpa1;
		$rpkrb1c=$rpa2;
		$rpkrb1d=$rpa3;
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left colspan=6>&nbsp;</td>";  
		$stream.="</tr>"; 
		
		
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>C</b></td>";  
		$stream.="<td align=left><b>Jurnal</b></td>";  
		$stream.="<td align=center>".$_SESSION['lang']['noakun']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['namaakun']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['debet']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['kredit']."</td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>Alokasi Biaya Umum ke Neraca</td>";  
		$stream.="<td align=center id=akundb1>".$akundebet['1']."</td>";  
		$stream.="<td align=left>".$nmakun[$akundebet['1']]."</td>";  
		$stream.="<td align=right id=rpdb1>".number_format($rpb2,2)."</td>";
		$stream.="<td align=right></td>";
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left></td>";  
		$stream.="<td align=left></td>";  
		$stream.="<td align=center id=akunkr1>".$akunkredit['1']."</td>";  
		$stream.="<td align=left>".$nmakun[$akunkredit['1']]."</td>";  
		$stream.="<td align=right></td>";
		$stream.="<td align=right id=rpkr1>".number_format($rpb2,2)."</td>";
		$stream.="</tr>"; 
		
		
		#Biar gak error di JS
		$stream.="<tr class=rowcontent style=display:none>";	
		$stream.="<td name=akundet[]></td>";  
		$stream.="<td name=rupiahdet[]></td>";
		$stream.="<td name=keterangan[]></td>";
		$stream.="</tr>"; 
		
		if($param['jenis']=='1'){
			$stream.="<tr class=rowcontent>";	
			$stream.="<td colspan=6 style=background-color:#bbbffc><b>Breakdown Detail</b></td>";  
			$stream.="</tr>"; 
			sort($noakundet);
			foreach($noakundet as $noakun){
				$stream.="<tr class=rowcontent>";	
				$stream.="<td align=left></td>";  
				$stream.="<td align=left name=keterangan[]>DETAIL ".$nmakun[$akunkredit['1']]."</td>";  
				$stream.="<td align=center name=akundet[]>".$noakun."</td>";  
				$stream.="<td align=left>".$nmakun[$noakun]."</td>";  
				$stream.="<td align=right></td>";
				$stream.="<td align=right name=rupiahdet[]>".number_format($biayadet[$noakun])."</td>";
				$stream.="</tr>"; 
			}
		}
		
		
		
		## =========================================================== ##
		## ALOKASI DEPRESIASI
		$rpa = 0; $noakundet=$biayadet=[];
		$ttlalktbm = 0;
		$luasb3=$luasb1+$luasb2;
		
		$str = "select sum(jumlah) as jumlah, noakun from ".$dbname.".keu_jurnaldt_vw where periode='".$per."' and  kodeorg='".$unit."' and (noakun like '71501%' and noakun!='7150198') and nojurnal not like '%HPP%' group by noakun";
		$res = fetchdata($str);
        foreach($res as $bar){
			$rpa+=$bar['jumlah'];
			$noakundet[$bar['noakun']]=$bar['noakun'];
			//$biayadet[$bar['noakun']]+=$bar['jumlah'];
			
			$biayadet[$bar['noakun']]+=round($luasb2/$luasb3*$bar['jumlah']);
			$ttlalktbm+=round($luasb2/$luasb3*$bar['jumlah']);
		}
		
		//$rpb2=round($luasb2/$luasb3*$rpa);
		$rpb2=$ttlalktbm;
		$rpb1=$rpa-$rpb2;
		$rpb3=$rpb1+$rpb2;
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td colspan=6 style=background-color:cyan>&nbsp;</td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>A</b></td>";  
		$stream.="<td align=left><b>Jenis Biaya</b></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>DEPRESIASI</td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=right>".number_format($rpa,2)."</td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left colspan=6>&nbsp;</td>";  
		$stream.="</tr>"; 
	
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>B</b></td>";  
		$stream.="<td align=left><b>Alokasi Biaya Umum</b></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>TM</td>";  
		$stream.="<td align=right>".number_format($luasb1,2)."</td>";
		$stream.="<td align=right>".number_format($rpb1,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;2</td>";  
		$stream.="<td align=left>TBM</td>";  
		$stream.="<td align=right>".number_format($luasb2,2)."</td>";
		$stream.="<td align=right>".number_format($rpb2,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
	
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left></td>";  
		$stream.="<td align=left>".$_SESSION['lang']['total']."</td>";  
		$stream.="<td align=right>".number_format($luasb3,2)."</td>";
		$stream.="<td align=right>".number_format($rpb3,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$rpdbb1a=$rpa4;
		$rpdbb1b=0;
		$rpdbb1c=0;
		$rpdbb1d=0;
		
		$rpkrb1a=0;
		$rpkrb1b=$rpa1;
		$rpkrb1c=$rpa2;
		$rpkrb1d=$rpa3;
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left colspan=6>&nbsp;</td>";  
		$stream.="</tr>"; 
		
		
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>C</b></td>";  
		$stream.="<td align=left><b>Jurnal</b></td>";  
		$stream.="<td align=center>".$_SESSION['lang']['noakun']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['namaakun']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['debet']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['kredit']."</td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>Alokasi Biaya Umum ke Neraca</td>";  
		$stream.="<td align=center id=akundb2>".$akundebet['2']."</td>";  
		$stream.="<td align=left>".$nmakun[$akundebet['2']]."</td>";  
		$stream.="<td align=right id=rpdb2>".number_format($rpb2,2)."</td>";
		$stream.="<td align=right></td>";
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left></td>";  
		$stream.="<td align=left></td>";  
		$stream.="<td align=center id=akunkr2>".$akunkredit['2']."</td>";  
		$stream.="<td align=left>".$nmakun[$akunkredit['2']]."</td>";  
		$stream.="<td align=right></td>";
		$stream.="<td align=right id=rpkr2>".number_format($rpb2,2)."</td>";
		$stream.="</tr>"; 
		
		if($param['jenis']=='1'){
			$stream.="<tr class=rowcontent>";	
			$stream.="<td colspan=6 style=background-color:#bbbffc><b>Breakdown Detail</b></td>";  
			$stream.="</tr>"; 
			sort($noakundet);
			foreach($noakundet as $noakun){
				$stream.="<tr class=rowcontent>";	
				$stream.="<td align=left></td>";  
				$stream.="<td align=left name=keterangan[]>DETAIL ".$nmakun[$akunkredit['2']]."</td>";  
				$stream.="<td align=center name=akundet[]>".$noakun."</td>";  
				$stream.="<td align=left>".$nmakun[$noakun]."</td>";  
				$stream.="<td align=right></td>";
				$stream.="<td align=right name=rupiahdet[]>".number_format($biayadet[$noakun])."</td>";
				$stream.="</tr>"; 
			}
		}
		## =========================================================== ##
		## ALOKASI AMORTASI
		
		$rpa = 0; $noakundet=$biayadet=[];
		$ttlalktbm = 0;
		$luasb3=$luasb1+$luasb2;
		
		$str = "select sum(jumlah) as jumlah, noakun from ".$dbname.".keu_jurnaldt_vw where periode='".$per."' and  kodeorg='".$unit."' and (noakun like '71503%' and noakun!='7150398') and nojurnal not like '%HPP%' group by noakun";
		$res = fetchdata($str);
        foreach($res as $bar){
			$rpa+=$bar['jumlah'];
			$noakundet[$bar['noakun']]=$bar['noakun'];
			//$biayadet[$bar['noakun']]+=$bar['jumlah'];
			
			$biayadet[$bar['noakun']]+=round($luasb2/$luasb3*$bar['jumlah']);
			$ttlalktbm+=round($luasb2/$luasb3*$bar['jumlah']);
		}
		
		//$rpb2=round($luasb2/$luasb3*$rpa);
		$rpb2=$ttlalktbm;
		$rpb1=$rpa-$rpb2;
		$rpb3=$rpb1+$rpb2;
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td colspan=6 style=background-color:cyan>&nbsp;</td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>A</b></td>";  
		$stream.="<td align=left><b>Jenis Biaya</b></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>AMORTASI</td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=right>".number_format($rpa,2)."</td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left colspan=6>&nbsp;</td>";  
		$stream.="</tr>"; 
	
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>B</b></td>";  
		$stream.="<td align=left><b>Alokasi Biaya Umum</b></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		
		
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>TM</td>";  
		$stream.="<td align=right>".number_format($luasb1,2)."</td>";
		$stream.="<td align=right>".number_format($rpb1,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;2</td>";  
		$stream.="<td align=left>TBM</td>";  
		$stream.="<td align=right>".number_format($luasb2,2)."</td>";
		$stream.="<td align=right>".number_format($rpb2,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
	
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left></td>";  
		$stream.="<td align=left>".$_SESSION['lang']['total']."</td>";  
		$stream.="<td align=right>".number_format($luasb3,2)."</td>";
		$stream.="<td align=right>".number_format($rpb3,2)."</td>";
		$stream.="<td align=center></td>";  
		$stream.="<td align=center></td>";  
		$stream.="</tr>"; 
		
		$rpdbb1a=$rpa4;
		$rpdbb1b=0;
		$rpdbb1c=0;
		$rpdbb1d=0;
		
		$rpkrb1a=0;
		$rpkrb1b=$rpa1;
		$rpkrb1c=$rpa2;
		$rpkrb1d=$rpa3;
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left colspan=6>&nbsp;</td>";  
		$stream.="</tr>"; 
		
		
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left><b>C</b></td>";  
		$stream.="<td align=left><b>Jurnal</b></td>";  
		$stream.="<td align=center>".$_SESSION['lang']['noakun']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['namaakun']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['debet']."</td>";  
		$stream.="<td align=center>".$_SESSION['lang']['kredit']."</td>";  
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
		$stream.="<td align=left>Alokasi Biaya Umum ke Neraca</td>";  
		$stream.="<td align=center id=akundb3>".$akundebet['3']."</td>";  
		$stream.="<td align=left>".$nmakun[$akundebet['3']]."</td>";  
		$stream.="<td align=right id=rpdb3>".number_format($rpb2,2)."</td>";
		$stream.="<td align=right></td>";
		$stream.="</tr>"; 
		
		$stream.="<tr class=rowcontent>";	
		$stream.="<td align=left></td>";  
		$stream.="<td align=left></td>";  
		$stream.="<td align=center id=akunkr3>".$akunkredit['3']."</td>";  
		$stream.="<td align=left>".$nmakun[$akunkredit['3']]."</td>";  
		$stream.="<td align=right></td>";
		$stream.="<td align=right id=rpkr3>".number_format($rpb2,2)."</td>";
		$stream.="</tr>"; 
		
		if($param['jenis']=='1'){
			$stream.="<tr class=rowcontent>";	
			$stream.="<td colspan=6 style=background-color:#bbbffc><b>Breakdown Detail</b></td>";  
			$stream.="</tr>"; 
			sort($noakundet);
			foreach($noakundet as $noakun){
				$stream.="<tr class=rowcontent>";	
				$stream.="<td align=left></td>";  
				$stream.="<td align=left name=keterangan[]>DETAIL ".$nmakun[$akunkredit['3']]."</td>";  
				$stream.="<td align=center name=akundet[]>".$noakun."</td>";  
				$stream.="<td align=left>".$nmakun[$noakun]."</td>";  
				$stream.="<td align=right></td>";
				$stream.="<td align=right name=rupiahdet[]>".number_format($biayadet[$noakun])."</td>";
				$stream.="</tr>"; 
			}
		}
		## =========================================================== ##
		
		$stream.="</table>";  
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="laporan_jurnal_oh_tbm_".$unit."_".$per;
			if(strlen($stream)>0){
                if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
								@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
                } else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
                }
                fclose($handle);
			}     
		} else {
			echo $stream;
		}
	
	break;
	case'preview':
		#= ambil saldo awal
		$stream.="<button class=mybutton onclick=savehpp()>".$_SESSION['lang']['proses']."</button><br><br>";
	
		#= cek setup_blok_tahunan sudah dilakukan / belum
		$str="SELECT count(*) as jumlah FROM ".$dbname.".`setup_blok_tahunan` WHERE   `tahun` ='".str_replace('-','',$per)."' and  substr(kodeorg,1,4)='".$unit."' and statusblok in ('TBM') group by statusblok";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
		$jumlahsetupblok=$bar['jumlah'];
			
		if($jumlahsetupblok=='0' || $jumlahsetupblok==''){
			exit("Warning: Unit ini tidak memiliki blok tbm atau
			blok tahunan untuk periode ".$per." belum terdaftar, harap lakukan proses tutup buku areal statement di : kebun->proses->tutup aresta.");
		}				
		
		#= luas tbm
		$str="SELECT sum(luasareaproduktif) as luas,statusblok FROM ".$dbname.".`setup_blok_tahunan` WHERE `tahun` ='".str_replace('-','',$per)."' and substr(kodeorg,1,4)='".$unit."' group by statusblok";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			if($bar['statusblok']=='TM'){
				@$luasb1+=$bar['luas'];
			}
			if($bar['statusblok']=='TBM'){
				@$luasb2+=$bar['luas'];
			}
			@$tluas+=$bar['luas'];
		}

		#= Alokasi
		$listdata = 0;
		$sql="SELECT * FROM {$dbname}.keu_5prosesalokasidt WHERE namalaporan='".$namalaporan."' AND tipe='Detail'";
		$res=fetchData($sql,"OBJECT");
		foreach($res as $v) {	
			$arrlistjudul[$v->nourut] = $v->nourut;
			$namajudul[$v->nourut] = $v->keterangandisplay;
			$akunalokasi[$v->nourut] = $v->noakun;
			$akundebet[$v->nourut] = $v->noakundebet;
			$akunkredit[$v->nourut] = $v->noakunkredit;
			$listdata++;
		}

		$rpa = 0; $noakundet=$biayadet=[];
		$ttlalktbm = 0;
		$luasb3=$luasb1+$luasb2;
		
		foreach($arrlistjudul as $lsjudul) {
			
			# AKUN
			$arrnoakun = array();
			$str="select * from ".$dbname.".keu_5prosesalokasidt_akun where namalaporan='".$namalaporan."' and nourut='".$lsjudul."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$arrnoakun[$bar['noakun']]=$bar['noakun'];
			}

			# TBM
			$str = "select sum(jumlah) as jumlah, noakun from ".$dbname.".keu_jurnaldt_vw where periode='".$per."' and  kodeorg='".$unit."' and noakun in ('".implode("','",$arrnoakun)."') group by noakun";
			$res = fetchdata($str);
			foreach($res as $bar){
				$rpa+=$bar['jumlah'];
				$noakundet[$bar['noakun']]=$bar['noakun'];
				//$biayadet[$bar['noakun']]+=$bar['jumlah'];
				$biayadet[$bar['noakun']]+=round($luasb2/$luasb3*$bar['jumlah']);
				$ttlalktbm+=round($luasb2/$luasb3*$bar['jumlah']);

				@$nilaijurnal[$lsjudul]+=$bar['jumlah']/$listdata;
				@$proporsinilaijurnaltbm[$lsjudul]=round($luasb2/$luasb3*$nilaijurnal[$lsjudul]);
				@$proporsinilaijurnaltm[$lsjudul]=round($luasb1/$luasb3*$nilaijurnal[$lsjudul]);
			}
			@$tnilaijurnal+=$bar['jumlah']/$listdata;

		}

		$stream.= "<table class=sortable cellspacing=1 cellpadding=5>";
		$stream.="<thead>";
		
		$stream.="<tr class=rowheader>";	
		$stream.="<th bgcolor=#CCCCCC align=center colspan=2>".$_SESSION['lang']['keterangan']."</th>";  
		$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['luas']."</th>";  
		$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rp']."</th>";  
		$stream.="<th align=center>Debet</th>";  
		$stream.="<th align=center>Kredit</th>";  				
		$stream.="</tr>"; 
		$stream.="</thead>";

		## COBA VERSI FOREACH
		$nomor=0;
		foreach($arrlistjudul as $lsjudul):
			$nomor++;

			$stream.="<tr class=rowcontent>";
			$stream.="<td align=left><b>A</b></td>";  
			$stream.="<td align=left><b>Jenis Biaya</b></td>";  
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>"; 	
			$stream.="</tr>";

			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left>&nbsp;&nbsp;{$nomor}</td>";  
			$stream.="<td align=left>".$namajudul[$lsjudul]."</td>";  
			$stream.="<td align=center></td>";  
			$stream.="<td align=right>".number_format($nilaijurnal[$lsjudul],2)."</td>";  
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="</tr>"; 

			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left colspan=6>&nbsp;</td>";  
			$stream.="</tr>"; 

			#===========================#
			# LUAS
			#===========================#
			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left><b>B</b></td>";  
			$stream.="<td align=left><b>Alokasi Biaya Umum</b></td>";  
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="</tr>"; 
			
			
			
			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
			$stream.="<td align=left>TM</td>";  
			$stream.="<td align=right>".number_format($luasb1,2)."</td>";
			$stream.="<td align=right>".number_format($proporsinilaijurnaltm[$lsjudul],2)."</td>";
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="</tr>"; 
			
			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left>&nbsp;&nbsp;2</td>";  
			$stream.="<td align=left>TBM</td>";  
			$stream.="<td align=right>".number_format($luasb2,2)."</td>";
			$stream.="<td align=right>".number_format($proporsinilaijurnaltbm[$lsjudul],2)."</td>";
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="</tr>"; 
		
			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left></td>";  
			$stream.="<td align=left>".$_SESSION['lang']['total']."</td>";  
			$stream.="<td align=right>".number_format($luasb3,2)."</td>";
			$stream.="<td align=right>".number_format($nilaijurnal[$lsjudul],2)."</td>";
			$stream.="<td align=center></td>";  
			$stream.="<td align=center></td>";  
			$stream.="</tr>"; 
			#===========================#
			# END LUAS
			#===========================#
			
			#===========================#
			# JURNAL
			#===========================#
			$rpdbb1a=$rpa4;
			$rpdbb1b=0;
			$rpdbb1c=0;
			$rpdbb1d=0;
			
			$rpkrb1a=0;
			$rpkrb1b=$rpa1;
			$rpkrb1c=$rpa2;
			$rpkrb1d=$rpa3;
			
			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left colspan=6>&nbsp;</td>";  
			$stream.="</tr>"; 
			
			
			
			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left><b>C</b></td>";  
			$stream.="<td align=left><b>Jurnal</b></td>";  
			$stream.="<td align=center>".$_SESSION['lang']['noakun']."</td>";  
			$stream.="<td align=center>".$_SESSION['lang']['namaakun']."</td>";  
			$stream.="<td align=center>".$_SESSION['lang']['debet']."</td>";  
			$stream.="<td align=center>".$_SESSION['lang']['kredit']."</td>";  
			$stream.="</tr>"; 
			
			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left>&nbsp;&nbsp;1</td>";  
			$stream.="<td align=left>".$namajudul[$lsjudul]."</td>";  
			$stream.="<td align=center id=akundb1>".$akundebet[$lsjudul]."</td>";  
			$stream.="<td align=left>".$nmakun[$akundebet[$lsjudul]]."</td>";  
			$stream.="<td align=right id=rpdb1>".number_format($proporsinilaijurnaltm[$lsjudul],2)."</td>";
			$stream.="<td align=right></td>";
			$stream.="</tr>"; 
			
			$stream.="<tr class=rowcontent>";	
			$stream.="<td align=left></td>";  
			$stream.="<td align=left></td>";  
			$stream.="<td align=center id=akunkr1>".$akunkredit[$lsjudul]."</td>";  
			$stream.="<td align=left>".$nmakun[$akunkredit[$lsjudul]]."</td>";  
			$stream.="<td align=right></td>";
			$stream.="<td align=right id=rpkr1>".number_format($proporsinilaijurnaltm[$lsjudul],2)."</td>";
			$stream.="</tr>"; 
			
			
			#Biar gak error di JS
			$stream.="<tr class=rowcontent style=display:none>";	
			$stream.="<td name=akundet[]></td>";  
			$stream.="<td name=rupiahdet[]></td>";
			$stream.="<td name=keterangan[]></td>";
			$stream.="</tr>"; 

			#===========================#
			# END JURNAL
			#===========================#
						
			#===========================#
			# DETAIL VIEW
			#===========================#

			$stream.="<tr class=rowcontent>";	
			$stream.="<td colspan=6 style=background-color:#bbbffc><b>Breakdown Detail</b></td>";  
			$stream.="</tr>"; 
			sort($noakundet);
			foreach($noakundet as $noakun){
				$stream.="<tr class=rowcontent>";	
				$stream.="<td align=left></td>";  
				$stream.="<td align=left name=keterangan[]>DETAIL ".$nmakun[$akunkredit[$lsjudul]]."</td>";  
				$stream.="<td align=center name=akundet[]>".$noakun."</td>";  
				$stream.="<td align=left>".$nmakun[$noakun]."</td>";  
				$stream.="<td align=right></td>";
				$stream.="<td align=right name=rupiahdet[]>".number_format($biayadet[$noakun])."</td>";
				$stream.="</tr>"; 
			}

		endforeach;

		## END


		## =========================================================== ##
		
		$stream.="</table>";  
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="laporan_jurnal_oh_tbm_".$unit."_".$per;
			if(strlen($stream)>0){
                if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
								@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
                } else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
                }
                fclose($handle);
			}     
		} else {
			echo $stream;
		}
	break;

	case'savehpp':
		
		try {
		$owlPDO->beginTransaction();
	
		#= cek sudah tutup buku / belum
		$str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$per."' and kodeorg='".$unit."'";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
		$tutupbuku=$bar['tutupbuku'];
		if($tutupbuku==1){
			throw new PDOException("Periode ini sudah ditutup.");
		}
		
		$lastDay = cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
		$nojurnal = $tahunbulan.$lastDay.'/'.$unit.'/ALKOH/001';
		$kodeJurnal = 'ALKOH';
		$tanggalJurnal = $per.'-'.$lastDay;
		$noUrut = 1;
		$noRef = $kodeJurnal.'/'.$unit.'/'.$tahunbulan;
		
		 // Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
			
		$str = deleteQuery($dbname,'keu_jurnalht',"nojurnal = '".$nojurnal."'");
		$owlPDO->exec($str);
		
			

			
		// Prepare Data Header
		$dataResHPP['header'] = array(
			'nojurnal'=>$nojurnal, 
			'kodejurnal'=>$kodeJurnal,
			'tanggal'=>$tanggalJurnal, 
			'tanggalentry'=>date('Ymd'),
			'posting'=>'0',
			'totaldebet'=>'0', 
			'totalkredit'=>'0',
			'amountkoreksi'=>'0',
			'noreferensi'=>$noRef,
			'autojurnal'=>'1',
			'matauang'=>'IDR', 
			'kurs'=>'1',
			'revisi'=>'0'
		);
		
		// Prepare Data Detail
		$dataResHPP['detail'] = array();
		
		
		#= Alokasi HPP TBS Ke Persediaan
		#= debet diganti perblok
		$noblok=$tluas=0;
		$str="SELECT * FROM ".$dbname.".`setup_blok_tahunan` WHERE `tahun` ='".str_replace('-','',$per)."' and  substr(kodeorg,1,4)='".$unit."' and statusblok in ('TBM') and luasareaproduktif>0";
		$res=fetchdata($str);
		foreach($res as $bar){
			$tluas+=$bar['luasareaproduktif'];
			$noblok++;
		}
		
		$totalperblok = $totalperblok2 = $totalperblok3 = 0;
		$str="SELECT * FROM ".$dbname.".`setup_blok_tahunan` WHERE `tahun` ='".str_replace('-','',$per)."' and  substr(kodeorg,1,4)='".$unit."' and statusblok in ('TBM') and luasareaproduktif>0";
		$res=fetchdata($str);
		foreach($res as $bar){
			$rpperblok=floor($rpdb/$tluas*$bar['luasareaproduktif']);
			$dataResHPP['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggalJurnal,
				'nourut'=>$noUrut,
				'noakun'=>$akundb,
				'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
				'jumlah'=>$rpperblok,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>$bar['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			
			$totalperblok+=$rpperblok;
			
			$rpperblok2=floor($rpdb2/$tluas*$bar['luasareaproduktif']);
			$dataResHPP['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggalJurnal,
				'nourut'=>$noUrut,
				'noakun'=>$akundb2,
				'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
				'jumlah'=>$rpperblok2,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>$bar['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			
			$totalperblok2+=$rpperblok2;
			
			$rpperblok3=floor($rpdb3/$tluas*$bar['luasareaproduktif']);
			$dataResHPP['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggalJurnal,
				'nourut'=>$noUrut,
				'noakun'=>$akundb3,
				'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
				'jumlah'=>$rpperblok3,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>$bar['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			
			$totalperblok3+=$rpperblok3;
		}
		
		$selisih=$selisih2=$selisih3=0;
		$selisih=$rpdb-$totalperblok;
		$selisih2=$rpdb2-$totalperblok2;
		$selisih3=$rpdb3-$totalperblok3;
		
		
		if($noUrut>$noblok){
			$dataResHPP['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggalJurnal,
				'nourut'=>$noUrut,
				'noakun'=>$akundb,
				'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
				'jumlah'=>$selisih,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>$bar['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			
			$dataResHPP['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggalJurnal,
				'nourut'=>$noUrut,
				'noakun'=>$akundb2,
				'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
				'jumlah'=>$selisih2,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>$bar['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			
			$dataResHPP['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggalJurnal,
				'nourut'=>$noUrut,
				'noakun'=>$akundb3,
				'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
				'jumlah'=>$selisih3,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noRef,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$noRef,
				'kodeblok'=>$bar['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
		}
		
		
		$dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 
			'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 
			'noakun'=>$akunkr,
            'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
            'jumlah'=>$rpkr*(-1),
            'matauang'=>'IDR', 
			'kurs'=>'1',
            'kodeorg'=>$unit, 
			'kodekegiatan'=>'',
            'kodeasset'=>'', 
			'kodebarang'=>'',
            'nik'=>'', 
			'kodecustomer'=>'', 
			'kodesupplier'=>'',
            'noreferensi'=>$noRef, 
			'noaruskas'=>'',
            'kodevhc'=>'', 
			'nodok'=>$noRef, 
			'kodeblok'=>'',
            'revisi'=>'0', 
			'kodesegment' => $defSegment
        );
		$noUrut++;
		
		$dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 
			'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 
			'noakun'=>$akunkr2,
            'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
            'jumlah'=>$rpkr2*(-1),
            'matauang'=>'IDR', 
			'kurs'=>'1',
            'kodeorg'=>$unit, 
			'kodekegiatan'=>'',
            'kodeasset'=>'', 
			'kodebarang'=>'',
            'nik'=>'', 
			'kodecustomer'=>'', 
			'kodesupplier'=>'',
            'noreferensi'=>$noRef, 
			'noaruskas'=>'',
            'kodevhc'=>'', 
			'nodok'=>$noRef, 
			'kodeblok'=>'',
            'revisi'=>'0', 
			'kodesegment' => $defSegment
        );
		$noUrut++;
		
		$dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 
			'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 
			'noakun'=>$akunkr3,
            'keterangan'=>'Alokasi Biaya OH ke TBM '.$pt.' '.$per,
            'jumlah'=>$rpkr3*(-1),
            'matauang'=>'IDR', 
			'kurs'=>'1',
            'kodeorg'=>$unit, 
			'kodekegiatan'=>'',
            'kodeasset'=>'', 
			'kodebarang'=>'',
            'nik'=>'', 
			'kodecustomer'=>'', 
			'kodesupplier'=>'',
            'noreferensi'=>$noRef, 
			'noaruskas'=>'',
            'kodevhc'=>'', 
			'nodok'=>$noRef, 
			'kodeblok'=>'',
            'revisi'=>'0', 
			'kodesegment' => $defSegment
        );
		$noUrut++;
		
		
		
		$queryH = insertQuery($dbname,'keu_jurnalht',$dataResHPP['header']);
		$owlPDO->exec($queryH);
		
		foreach($dataResHPP['detail'] as $key=>$dataDet) {
			$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
			$owlPDO->exec($queryD);
		}
		
		// echo"<pre>";
		// print_r($param);
		// exit("error");
		
		## PERMINTAAN BU LENNY UNTUK LAPORAN MPI
		$str = "delete from " . $dbname . ".keu_jurnaldetailbyyoh where 1=1 and kodeorg='".$unit."' and periode='".$param['per']."'";
		$owlPDO->exec($str);
		
		foreach($param['akundetail'] as $key => $noakun){
			if($noakun!=''){
				$data = array(
					'kodeorg'=>$unit, 
					'periode'=>$param['per'],
					'noakun'=>$noakun,
					'keterangan'=>$param['ketdetail'][$key],
					'jumlah'=>str_replace(",","",$param['rupiahdetail'][$key])*(-1)
				);
				$query = insertQuery($dbname,'keu_jurnaldetailbyyoh',$data,array_keys($data));
				$owlPDO->exec($query);
			}
		}
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
	break;
	
	default:
	break;
}



?>