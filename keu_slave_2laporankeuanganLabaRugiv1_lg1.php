<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$pt=$_POST['pt'];
$unit=$_POST['unit']; //kebun
$periode=$_POST['periode'];

$qwe=explode('-',$periode);
$tahun=$periode;
// $bulan=$qwe[1];

// 
$kodelaporan='LABARUGI V2';

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $namapt=strtoupper($bar->namaorganisasi);
}

// ambil urut
$str="select nourut, keterangandisplay, tipe, noakundisplay from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'
    order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$listurut[$bar->nourut]=$bar->nourut;

	$namaurut[$bar->nourut]=$bar->keterangandisplay;
	$tipeurut[$bar->nourut]=$bar->tipe;
	$anakurut[$bar->nourut]=$bar->noakundisplay;
}

// ambil akun
$str="select nourut, noakun from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."'
    order by nourut,noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$keurut[$bar->noakun]=$bar->nourut;
}

// ambil saldo awal
// $str="select periode, noakun, (awal01+awal02+awal03+awal04+awal05+awal06+awal07+awal08+awal09+awal10+awal11+awal12) as awal, kodeorg from ".$dbname.".keu_saldobulanan where periode='".str_replace("-", "", $periode)."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%')";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$perx=substr($bar['periode'],0,4).'-'.substr($bar['periode'],4,2);
// //	$awal[$bar['noakun']]+=$bar['awal'];
// 	if(substr($bar['noakun'],0,5)=='11502'){
// 		$data['2101'][$perx]+=$bar['awal'];
// 		$data['2101']['sd']+=$bar['awal'];		
// 		$data['2103'][$perx]+=$bar['awal'];
// 		$data['2103']['sd']+=$bar['awal'];		
// 	}
// }

// // khusus Opening Stock dan Closing Stock ambil dari noakun 11502 (awal n akhir) ato dari keu_4hpp?
// // ind : ambil dari keu_4hpp

$str="select awal01, noakun from ".$dbname.".keu_saldobulanan where left(periode,4)='".$tahun."01' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and noakun in ('1150201','1150203')";
$res=fetchdata($str);
foreach($res as $bar){
	$perx=$tahun.'-01';
		$data['2101'][$perx]+=$bar['awal01'];
	// $data['2101']['sd']+=$bar['awal01'];
}

// ambil transaksi
$str="select tanggal, noakun, jumlah, kodeorg from ".$dbname.".keu_jurnaldt_vw where left(tanggal,4)='".$tahun."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') order by tanggal asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$kali=1;
	if(substr($bar['noakun'],0,1)=='5'){
		$kali=(-1);
	}

	$perx=substr($bar['tanggal'],0,7);
	$masukkeurut=$keurut[$bar['noakun']];
	// $unit=$bar['tanggal'];

	$data[$masukkeurut][$perx]+=($kali*$bar['jumlah']);

	for ($x = 1; $x <= 12; $x++) {
		$xx = sprintf("%02d", $x);
		$perxxxx=$tahun.'-'.$xx;
		if(($perx<=$perxxxx)and($masukkeurut!=''))
		$datasd[$masukkeurut][$perxxxx]+=($kali*$bar['jumlah']);

		// if($x>1){
		// 	$y=$x-1;
		// 	$yy = sprintf("%02d", $y);
		// 	$peryyyy=$tahun.'-'.$yy;
			if($masukkeurut=='2103'){ // closing stock ambil 
				$data['2103'][$perxxxx]=$datasd['2103'][$perxxxx];
			}
		// }
	} 

	// opening n closing stock
	// if(substr($bar['noakun'],0,5)=='11502'){
	// 	$data['2103'][$perx]+=$bar['jumlah'];
	// 	$data['2103']['sd']+=$bar['jumlah'];
	// }
	// exit("Error:$masukkeurut");
	// if($perx<$periode){
		// $data['2101'][$periode]+=$bar['jumlah'];
		// $data[$masukkeurut][$perx]+=($kali*$bar['jumlah']);
	// }
	
	// if($masukkeurut=='2103'){
	// 	$arrtemp['2101'][$perx]=$data['2103']['sd'];
	// }
	
	// $data['2101'][$periode]=abs($arrtemp['2101'][periodelalu($periode)]);
	// // $data['2101'][$periode]=($data['2103'][periodelalu($periode)]*-1);
	// $data['2103'][$periode]=$data['2103']['sd'];

	
}

// echo"<pre>";
// print_r($data);
// echo"</pre>";

// $str="select sum(rpawal) as jumlah,periode from ".$dbname.".keu_4hpp where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')	and periode in ('".$periode."','".periodeberikut($periode)."') group by periode";
// // echo $str;
// $res=fetchdata($str);
// foreach($res as $bar){
	// if($bar['periode']==$periode){ 
		// $data['2101'][$periode]=$bar['jumlah'];
		// $data['2101']['sd']=$bar['jumlah'];
	// }
	// if($bar['periode']==periodeberikut($periode)){ 
		// $data['2103'][$periode]=$bar['jumlah'];
		// $data['2103']['sd']=$bar['jumlah'];
	// }
// }



// echo "<pre>";
// print_r($datadz);
// echo "</pre>";

// susun total
foreach($listurut as $urut){
	for ($x = 1; $x <= 12; $x++) {
		$xx = sprintf("%02d", $x);
		$perxxxx=$tahun.'-'.$xx;

		if($urut=='3011' || $urut=='4001' || $urut=='4002'){ // sabinus: ini tanda nya harus nya minus pak
			$data[$urut][$perxxxx]=(-1)*$data[$urut][$perxxxx];
			$datasd[$urut][$perxxxx]=(-1)*$datasd[$urut][$perxxxx];
		}
		$qwe=explode(',', $anakurut[$urut]);
		foreach($qwe as $anak){
			if($anak!=''){
				$amin=substr($anak,0,1);
				if($amin=='-'){ // -1234
					$anak2=substr($anak,1,4);
					$data[$urut][$perxxxx]-=$data[$anak2][$perxxxx];
					$datasd[$urut][$perxxxx]-=$datasd[$anak2][$perxxxx];
				}else{ // 1234
					$data[$urut][$perxxxx]+=$data[$anak][$perxxxx];
					$datasd[$urut][$perxxxx]+=$datasd[$anak][$perxxxx];
				}
			}
		}

		if($x==1){
			$datapers[$urut][$perxxxx]=100;
		}else{
			$y=$x-1;
			$yy = sprintf("%02d", $y);
			$peryyyy=$tahun.'-'.$yy;

			if($urut=='2101'){ // opening stock ambil 
				$data['2101'][$perxxxx]=abs($data['2103'][$peryyyy]);
			}

			$datapers[$urut][$perxxxx]=fixnan(($data[$urut][$perxxxx]-$data[$urut][$peryyyy])/$data[$urut][$peryyyy]*100);
		}
	}
}

// echo "<pre>";
// print_r($data['2101']); // Opening Stock
// print_r($data['2103']);
// echo "</pre>";

if($unit==''){
	$unitx=$pt;
}else{
	$unitx=$unit;
}

$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unitx."'");
$stream ="Laporan Keuangan - Laba Rugi<br>";
$stream.="".$unitx." - ".$nmorg[$unitx]."<br>";
$stream.="Periode ".$periode."<br><br>";
$stream.="<table class=sortable border=0 cellspacing=0>
    <thead>
        <tr class=rowheader>
            <td style='width:520px' align=center colspan=3 rowspan=2>Description</td>
            ";
	for ($x = 1; $x <= 12; $x++) {
		$xx = sprintf("%02d", $x);
		$perxxxx=$tahun.'-'.$xx;
		$stream.="<td style='width:120px' align=center rowspan=1 colspan=2>".$perxxxx." </td>";    
	}            
    $stream.="</tr>";
    $stream.="<tr class=rowheader>";
	for ($x = 1; $x <= 12; $x++) {
		$xx = sprintf("%02d", $x);
		$perxxxx=$tahun.'-'.$xx;
		$stream.="<td align=center>Rp.</td>";    
		$stream.="<td align=center>%</td>";
	}	
    $stream.="</tr>";
    $stream.="</thead><tbody>";

if(!empty($listurut))foreach($listurut as $urut){ // level 0
    if($tipeurut[$urut]=='Header'){
        $stream.="<tr class=rowcontent title='".$namaurut[$urut]."' >
            <td colspan=27><b>".$namaurut[$urut]." </b></td>
        </tr>"; 
        $stream.="<tr><td colspan=27><div style=\"display:none;\" id=no_".$urut.">";
        $stream.="</div></td></tr>";
    }else if($tipeurut[$urut]=='Detail'){
        $stream.="<tr class=rowcontent>
            <td style='width:10px'></td>
            <td colspan=2 style='width:510px'>".$namaurut[$urut]." </td>";
			
	for ($x = 1; $x <= 12; $x++) {
		$xx = sprintf("%02d", $x);
		$perxxxx=$tahun.'-'.$xx;
		$stream.="<td style='width:120px' align=right title='Click untuk melihat detail' style=cursor:pointer; onclick=\"getLaporanKeuanganDetailv2_lg1('".$urut."','".$tipeurut[$urut]."','".$perxxxx."')\">".number_format($data[$urut][$perxxxx])."</td>";
		$stream.="<td style='width:120px' align=right title='Click untuk melihat detail' style=cursor:pointer; onclick=\"getLaporanKeuanganDetailv2_lg1('".$urut."','".$tipeurut[$urut]."','".$perxxxx."')\">".number_format($datapers[$urut][$perxxxx],2)."</td>";
	}	
		$stream.="</tr>";

        $stream.="<tr><td colspan=27><div style=\"display:none;\" id=no_".$urut.">";
        $stream.="</div></td></tr>";
    }else if($tipeurut[$urut]=='Total'){
        $stream.="<tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'><b>".$namaurut[$urut]."</b></td>
            ";
	for ($x = 1; $x <= 12; $x++) {
		$xx = sprintf("%02d", $x);
		$perxxxx=$tahun.'-'.$xx;
        $stream.="<td style='width:120px' align=right><b>".number_format($data[$urut][$perxxxx])."</b></td>";                
        $stream.="<td style='width:120px' align=right><b>".number_format($datapers[$urut][$perxxxx],2)."</b></td>";
	}	
        $stream.="</tr>
        <tr class=rowcontent><td colspan=5></td></tr>
        ";
    }
}

$stream.= "</tbody></tfoot></tfoot></table>";
echo $stream;

exit;

/*

$st12="select noakun, namaakun, namaakun1
    from ".$dbname.".keu_5akun where level=5";
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch())
{
    if($_SESSION['language']=='ID'){
        $akun[$ba12->noakun]=$ba12->namaakun;}
    else{
        $akun[$ba12->noakun]=$ba12->namaakun1;
    }
}   

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];
$blnPil=$bulan;

if($bulan=='01' or $bulan=='1'){
  $bulanlalu=12;
 }else{ 
  $bulanlalu=$bulan-1;
} 

if($bulanlalu<10)$bulanlalu='0'.$bulanlalu; // bulan lalu dia digit
if($bulanlalu=='00')$bulanlalu='12';
$periodelalu=$tahun.'-'.$bulanlalu; // periode lalu
if($bulan==1)$periodelalu=$tahunlalu.'-12';

$dzArr=array();
$dzArr2=array();
$nilaiawal=array();
$hargaawal=array();
$fisikawal=array();

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}

$kodelaporan='LK - LABA RUGI V1';

//title table
for ($i = $bulan; $i >= 1; $i--) {
    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
    $t=mktime(0,0,0,$i,15,$tahun);
    $kolom[$ii]=date('M-Y',$t);
}
$t=mktime(0,0,0,$bulan,15,$tahun);
$kolom['sd']='to '.date('M-Y',$t);

//involving units
if($unit=='')$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else $where=" ='".$unit."'";
 
#DATA CPO DAN KERNEL, PRODUKSI,SALDO END SCRIPT#
#NGUMPULIN DATA RUPIAH,AWAL# 
//report format
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'
    order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$noakunsql=" noakun between '".$bar->noakundari."' and '".$bar->noakunsampai."'";
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;}
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    
    // dari total
	
	if($bar->tipe=='Total'){
		$isiRange[$bar->nourut]=$bar->noakundisplay;
		 // if(($dzArr[$bar->nourut]['noakundari']!='')||!is_null(($dzArr[$bar->nourut]['noakundari']!=''))){
			// $tipeTotal='totalnilaiakun';
		// }
		// $qwe=explode(",",$bar->noakundisplay);
		// if(!empty($qwe)){
			// foreach($qwe as $rty){
				// if(trim($rty)!=0){    
					// $emaknya[trim($rty)]=$bar->nourut;
					// $adaemaknya[trim($rty)]=trim($rty);
					
				// }
			// }
		// }  
	}
	
	switch($bar->nourut){#update where untuk biaya tidak langsung
		case'200004':#biaya tidak langsung kebun
		$dtNoakun=array();
		$byOverHead2=array();
		$byOverHead=array();
		// $sDeplesi="select sum(jumlah) as deplesi,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
  //               where left(tanggal,4)='".$tahun."'  and  noakun='7150201' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$pt."') group by left(tanggal,7)";
	 //    $qDeplesi=$owlPDO->query($sDeplesi) or die(print " Gagal: ".PDOException::getMessage());
		// $qDeplesi->setFetchMode(PDO::FETCH_ASSOC);
	 //    while($rDeplesi=$qDeplesi->fetch()){
	 //    	$byDeplesi[$rDeplesi['bln']]=$rDeplesi['deplesi'];
	 //    }
		#akun tanaman tidak disebar merata hanya ke kebun
        $akunTnmn="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TKAKUN'";
        $rakunTnmn=fetchdata($akunTnmn);
	    
		$sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
		$qUnitKbn=$owlPDO->query($sUnitKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qUnitKbn->setFetchMode(PDO::FETCH_ASSOC);
    	while($rowUnitKbn=$qUnitKbn->fetch()){
    		$arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
    	}

	    #total unit pabrik
	    $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
	              where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
		$qUnitPbrk=$owlPDO->query($sUnitPbrk) or die(print " Gagal: ".PDOException::getMessage());
		$qUnitPbrk->setFetchMode(PDO::FETCH_ASSOC);
	    while($rowUnitPbrk=$qUnitPbrk->fetch()){
	    	$arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
	    }
	    for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
	        $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
		}
	    #over head ro dan ho
	    $sOverHead="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
	                where left(tanggal,4)='".$tahun."'  and noakun like '7%' and left(noakun,5)!='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL') and induk='".$pt."')
	                and noakun!='7199999' group by left(tanggal,7)";
		$qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
		$qOverHead->setFetchMode(PDO::FETCH_ASSOC);
	    while($rOverHead=$qOverHead->fetch()){
	    	$byOverHead[$rOverHead['bln']]=$rOverHead['overhead'];
	    }
     	
	    for ($i = 1; $i <= $bulan; $i++) {
	    	if($unit==''){
		    	if($i<10)$ii='0'.$i; else $ii=$i;
		    	@$byOverHead2[$ii]=$byOverHead[$ii]/$rowUnit[$ii];
		    	$dzArr[$bar->nourut][$ii]=($arrRwKbn[$ii]*$byOverHead2[$ii]);
		    	$dzArr[$bar->nourut]['sd']+=($arrRwKbn[$ii]*$byOverHead2[$ii]);
	    	}
	    }
	    
			$nomakun=explode(",",$bar->noakundisplay);
			if($unit==''){
				$where="";
				//$where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in ('KEBUN')) and noakun not in (select noakun from ".$dbname.".keu_5akun where noakun>='".$nomakun[0]."' and noakun<='".$nomakun[1]."')";
				$where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe ='KEBUN') and left(noakun,5)!='71502'";
			}else{
				$whr="kodeorganisasi='".$unit."'";
				$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
				if($optTipe[$unit]!='KEBUN'){
					$where="";
				}else{
					$where=" ='".$unit."'  and left(noakun,5)!='71502'";
				}
			} 
		break;
		case'300003':#biaya tidak langsung pabrik
		$dtNoakun=array();
		$byOverHead2=array();
		$byOverHead2=array();
		$sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
    	$qUnitKbn=$owlPDO->query($sUnitKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qUnitKbn->setFetchMode(PDO::FETCH_ASSOC);
    	while($rowUnitKbn=$qUnitKbn->fetch()){
    		$arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
    	}

	    #total unit pabrik
	    $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
	              where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
		$qUnitPbrk=$owlPDO->query($sUnitPbrk) or die(print " Gagal: ".PDOException::getMessage());
		$qUnitPbrk->setFetchMode(PDO::FETCH_ASSOC);
	    while($rowUnitPbrk=$qUnitPbrk->fetch()){
	    	$arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
	    }
	    for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
	        $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
		}
		 
	    #over head ro dan ho
	    $sOverHead="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
	                where left(tanggal,4)='".$tahun."'  and noakun like '7%' and left(noakun,5)!='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL','TRAKSI') and induk='".$pt."')
	                and noakun!='7199999'  group by left(tanggal,7)";
		$qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
		$qOverHead->setFetchMode(PDO::FETCH_ASSOC);
	    while($rOverHead=$qOverHead->fetch()){
	    	$byOverHead[$rOverHead['bln']]=$rOverHead['overhead'];
	    }
	    for ($i = 1; $i <= $bulan; $i++) {
	    	if($unit==''){
		    	if($i<10)$ii='0'.$i; else $ii=$i;
		    	@$byOverHead2[$ii]=$byOverHead[$ii]/$rowUnit[$ii];
		    	$dzArr[$bar->nourut][$ii]=($arrRwPbrk[$ii]*$byOverHead2[$ii]);
		    	$dzArr[$bar->nourut]['sd']+=($arrRwPbrk[$ii]*$byOverHead2[$ii]);
	    	}
	    }
	     
			if($unit==''){
				$where="";
				$where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')  and left(noakun,5)!='71502'";
			}else{
				$whr="kodeorganisasi='".$unit."'";
				$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
				if($optTipe[$unit]!='PABRIK'){
					$where="";
				}else{
					$where=" ='".$unit."'  and left(noakun,5)!='71502'";
				}
			} 
		break;
		case'200005':#depresiasi kebun+traksi
		$dtNoakun=array();
		$byOverHead=array();
		$byOverHead2=array();
		$sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
    	$qUnitKbn=$owlPDO->query($sUnitKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qUnitKbn->setFetchMode(PDO::FETCH_ASSOC);
    	while($rowUnitKbn=$qUnitKbn->fetch()){
    		$arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
    	}

	    #total unit pabrik
	    $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
	              where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
		$qUnitPbrk=$owlPDO->query($sUnitPbrk) or die(print " Gagal: ".PDOException::getMessage());
		$qUnitPbrk->setFetchMode(PDO::FETCH_ASSOC);
	    while($rowUnitPbrk=$qUnitPbrk->fetch()){
	    	$arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
	    }
	    for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
	        $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
		}
		#akun tanaman tidak disebar merata hanya ke kebun
        $akunTnmn="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TKAKUN'";
        $rakunTnmn=fetchdata($akunTnmn);	

	    #over head ro dan ho
	    $sOverHead="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
	                where left(tanggal,4)='".$tahun."' and noakun not in (".$rakunTnmn[0]['nilai'].")  and left(noakun,5)='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL','TRAKSI') and induk='".$pt."')
	                group by left(tanggal,7)";
		$qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
		$qOverHead->setFetchMode(PDO::FETCH_ASSOC);
	    while($rOverHead=$qOverHead->fetch()){
	    	$byOverHead[$rOverHead['bln']]=$rOverHead['overhead'];
	    }
	    #depresiasi tanaman
        $sOverHead2="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln,noakun  from ".$dbname.".keu_jurnaldt 
                    where left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL') and induk='".$pt."')  
                    and noakun in (".$rakunTnmn[0]['nilai'].") group by left(tanggal,7)";
        $qOverHead2=$owlPDO->query($sOverHead2) or die(print " Gagal: ".PDOException::getMessage());
        $qOverHead2->setFetchMode(PDO::FETCH_ASSOC);
        while($rOverHead2=$qOverHead2->fetch()){ 
            $byOvTnmn[$rOverHead2['bln']]=$rOverHead2['overhead'];
        }
	    for ($i = 1; $i <= $bulan; $i++) {
	    	if($unit==''){
		    	if($i<10)$ii='0'.$i; else $ii=$i;
		    	if($byOverHead[$ii]==''){
		    		$byOverHead[$ii]=0;
		    	}
		    	@$byOverHead2[$ii]=$byOverHead[$ii]/$rowUnit[$ii];
		    	$dzArr[$bar->nourut][$ii]=($arrRwKbn[$ii]*$byOverHead2[$ii])+$byOvTnmn[$ii];
		    	$dzArr[$bar->nourut]['sd']+=($arrRwKbn[$ii]*$byOverHead2[$ii])+$byOvTnmn[$ii];;
	    	}	
	    }
	     
			if($unit==''){
				$where="";
				$where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in  ('KEBUN'))";
			}else{
				$whr="kodeorganisasi='".$unit."'";
				$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
				if(($optTipe[$unit]!='KEBUN')||($optTipe[$unit]!='TRAKSI')){
					$where="";
				}else{
					$where=" ='".$unit."'";
				}
			}
		break;
		case'300004':#depresiasi PABRIK
		$dtNoakun=array();
		$byOverHead=array();
		$byOverHead2=array();
		$sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
    	$qUnitKbn=$owlPDO->query($sUnitKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qUnitKbn->setFetchMode(PDO::FETCH_ASSOC);
    	while($rowUnitKbn=$qUnitKbn->fetch()){
    		$arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
    	}
    	#akun tanaman tidak disebar merata hanya ke kebun
        $akunTnmn="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TKAKUN'";
        $rakunTnmn=fetchdata($akunTnmn);

	    #total unit pabrik
	    $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
	              where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
		$qUnitPbrk=$owlPDO->query($sUnitPbrk) or die(print " Gagal: ".PDOException::getMessage());
		$qUnitPbrk->setFetchMode(PDO::FETCH_ASSOC);
	    while($rowUnitPbrk=$qUnitPbrk->fetch()){
	    	$arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
	    }
	    for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
	        $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
		}
	    #over head ro dan ho
	    $sOverHead="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
	                where left(tanggal,4)='".$tahun."'  and noakun not in (".$rakunTnmn[0]['nilai'].")  and left(noakun,5)='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL','TRAKSI') and induk='".$pt."')
	                group by left(tanggal,7)";
		$qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
		$qOverHead->setFetchMode(PDO::FETCH_ASSOC);
	    while($rOverHead=$qOverHead->fetch()){
	    	$byOverHead[$rOverHead['bln']]=$rOverHead['overhead'];
	    }
	    for ($i = 1; $i <= $bulan; $i++) {
	    	if($unit==''){
	    		if($i<10)$ii='0'.$i; else $ii=$i;
	    		@$byOverHead2[$ii]=$byOverHead[$ii]/$rowUnit[$ii];
	    		$dzArr[$bar->nourut][$ii]=($arrRwPbrk[$ii]*$byOverHead2[$ii]);
	    		$dzArr[$bar->nourut]['sd']+=($arrRwPbrk[$ii]*$byOverHead2[$ii]);
	    	}
	    }

	     
			if($unit==''){
				$where="";
				$where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')";
			}else{
				$whr="kodeorganisasi='".$unit."'";
				$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
				if($optTipe[$unit]!='PABRIK'){
					$where="";
				}else{
					$where=" ='".$unit."'";
				}
			}
		break;
		case'200013':
		case'200011':
			$nomakun=$bar->noakundisplay;
			if($nomakun!=''){
				$noakunsql=" noakun in (".$nomakun.")";
			}
			if($unit==''){
				$where="";
				$where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' ) ";
			}else{
				$whr="kodeorganisasi='".$unit."'";
				$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
					$where=" ='".$unit."' ";
			} 
			 
		break;

		default:
			if($unit=='')$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			else $where=" ='".$unit."'";
		break;
	}
    $whrpeng='';
	if($bar->nourut=='400005'){
		if($bar->noakundisplay!=''){
			$dtAknpeng=explode(",",$bar->noakundisplay);
			$whrpeng=" and left(noakun,".strlen($dtAknpeng[0]).") not in (".$bar->noakundisplay.")";
		}
	}
    
    $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
    $st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal,substr(periode,5,2) as bulan
           from ".$dbname.".keu_saldobulanan where ".$noakunsql." and periode like'".$tahun."%' and kodeorg ".$where." 
           ".$whrpeng." order by noakun,periode ";
    $res12=$owlPDO->query($st12) or die(print " Gagal: "."___".$st12.PDOException::getMessage());
	$res12->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res12->fetch()){
		if($bar->nourut<100006){
			$ba12->jumlah=abs($ba12->jumlah);
			$ba12->awal=abs($ba12->awal);
		}
		
		if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
        $dzArr[$bar->nourut][$ba12->bulan]+=($ba12->jumlah+$ba12->awal);
        $dzArr2[$ba12->noakun][$ba12->bulan]=($ba12->jumlah+$ba12->awal);
		//if(!isset($dzArr[$bar->nourut]['sd'])){ $dzArr[$bar->nourut]['sd']=0;}
		if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
        if(intval($bulan)>=intval($ba12->bulan)){
            @$dzArr[$bar->nourut]['sd']+=($ba12->jumlah+$ba12->awal);
            @$dzArr2[$ba12->noakun]['sd']+=($ba12->jumlah+$ba12->awal);
        }
    	 
    }  
	#NGUMPULIN DATA FISIK CPO,KERNEL DAN TBS AWAL#
	if($bar->nourut==200008){//TBS
			// $sData="select sum(tbsmasuk) as jmlhKg,kodeorg,substr(tanggal,6,2) as bln from ".$dbname.".pabrik_produksi 
			        // where kodeorg ".$where." and tanggal like '".substr($periode,0,4)."%' group by left(tanggal,7),kodeorg";
			$sData="select sum(kgwb) as jmlhKg,kodeorg,substr(tanggal,6,2) as bln from ".$dbname.".kebun_spb_vw where 
					kodeorg ".$where." and tanggal like '".substr($periode,0,4)."%' and intiplasma='I' and posting=1
					group by left(tanggal,7),kodeorg";
			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while($rData=$qData->fetch()){
				$dzArr[$bar->nourut][$rData['bln']]+=$rData['jmlhKg'];
				if(intval($rData['bln'])<=intval(substr($periode,5,2))){
					$dzArr[$bar->nourut]['sd']+=$rData['jmlhKg'];	
				}
			}
	}
	
	#NGUMPULIN DATA FISIK AKHIR#
}
#NGUMPULIN DATA RUPIAH AKHIR#

$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
$stream ="Laporan Keuangan - Laba Rugi<br>";
$stream.="".$unit." - ".$nmorg[$unit]."<br>";
$stream.="Periode ".$periode."<br><br>";
$stream.="<table class=sortable border=0 cellspacing=0>
    <thead>
        <tr class=rowheader>
            <td style='width:520px' align=center colspan=3 rowspan=2>Description</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td style='width:120px' align=center rowspan=2>".$kolom[$ii]." </td>";    
            }
            $stream.="<td style='width:120px' align=center rowspan=2>".$kolom['sd']."</td>
                <td align=center colspan=2>Increase/Decrease</td>    
        </tr>
        <tr class=rowheader>
            <td style='width:120px' align=center>Rupiah</td>
            <td style='width:50px' align=center>%</td>
        </tr>
    </thead><tbody>";
 
$subtotal['sd']=0;
if(!empty($dzArr))foreach($dzArr as $data){ // level 0
	if(@$ba12->bulan=='04'){
			if($bar->nourut=='300020'){
				exit('warning:'.$dzArr[$bar->nourut][$ba12->bulan]);
			}
	}
    if($data['tipe']=='Header')
    {
        $totallagi=0;        
    }
    if($data['tipe']=='Detail'){
        // subtotal
        for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
			if(!isset($subtotal[$ii])) $subtotal[$ii]=0;
            @$subtotal[$ii] += isset($data[$ii])? $data[$ii]: 0;
            @$sbDt[$data['nourut']][$ii]+=isset($data[$ii])? $data[$ii]: 0;
        }
        $subtotal['sd'] += isset($data['sd'])? $data['sd']: 0;
        $totallagi=0; 
    }
	if($totallagi==0){
		$tipeTotal='standar';		
	}
    if($data['tipe']=='Total'){
    	
		#DATA BERDASARKAN TIPE TOTAL
		switch($tipeTotal){
			case'standar':
			if($data['nourut']=='300020'){
    			break;
    		}
				for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
				//if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
                @$dzArr[$data['nourut']][$ii] += isset($subtotal[$ii])? $subtotal[$ii]: 0;
                @$subtotal[$ii]=0;            
				}
				//if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
				@$dzArr[$data['nourut']]['sd'] += isset($subtotal['sd'])? $subtotal['sd']: 0;
			break;
		}

		#DATA PER NOURUT
		$subtotal['sd']=0;
        $totallagi=1;       
		$rangedt=explode(",",$isiRange[$data['nourut']]);
		switch($data['nourut']){
			case '200014':
			#TOTALAN BERDASARKAN RANGE NO URUT 
			$sdt=$rangedt[1]-$rangedt[0];
			$totAwl=$sdt;
			$sdt=$sdt+1;//tambah satu untuk ambil biaya sub total kebun
				for($asa=$sdt;$asa>0;$asa--){
						if($asa==$sdt){
							$ada=200006;//no urut sub total kebun
						}else if($asa==$totAwl){
							$ada=$rangedt[0];
							@$datanya.=$ada;
						}else{
							if($rangedt[1]!=$ada){
								$ada+=1;
								$datanya.=",".$ada;
							}
						}
					for ($i = 1; $i <= $bulan; $i++){
						if($i<10)$ii='0'.$i; else $ii=$i;
						@$dzArr[$data['nourut']][$ii]+=isset($dzArr[$ada][$ii])? $dzArr[$ada][$ii]: 0;
						@$angkanya.=$dzArr[$ada][$ii]."__".$ii."__".$ada."\n";
						//if(!isset($dzArr[$data['nourut']]['sd'])){ $dzArr[$data['nourut']]['sd']=0;}else{
							
						//}
					}
					$dzArr[$data['nourut']]['sd']+=$dzArr[$ada]['sd'];
				}
			break;
			case'300018':
			case'300019':
			case'300020':
				for($i = 1; $i <= $bulan; $i++){
					if($i<10)$ii='0'.$i; else $ii=$i;
					$sAwl="select sum(awal".$ii.") as awal,sum(debet".$ii."-kredit".$ii.") as mutsi from ".$dbname.".keu_saldobulanan where noakun='".$isiRange[$data['nourut']]."' and periode='".$tahun."".$ii."' and kodeorg ".$where." ";
					$qAwl=$owlPDO->query($sAwl) or die(print " Gagal: ".PDOException::getMessage());
					$qAwl->setFetchMode(PDO::FETCH_ASSOC);
					$rAwl=$qAwl->fetch();
					$dzArr[$data['nourut']][$ii]=$rAwl['awal']+$rAwl['mutsi'];
					$dzArr[$data['nourut']]['sd']=$rAwl['awal']+$rAwl['mutsi'];	
				}
			break;
			case '400001':
			#100004=Penjualan,(300007,300011,300012)=total biaya produksi,(300014,300015,300016)=saldo awal,(300018,300019,300020)=saldo akhir
			#PENJUALAN-(TOTAL BIAYA PRODUKSI+SALDO AWAL+PEMBELIAN CPOKER-SALDO AKHIR)*0.9
				for ($i = 1; $i <= $bulan; $i++) {
						if($i<10)$ii='0'.$i; else $ii=$i;
						@$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]-($dzArr[$rangedt[1]][$ii]+$dzArr[$rangedt[2]][$ii]+$dzArr[$rangedt[3]][$ii]+$dzArr[$rangedt[4]][$ii]+$dzArr[$rangedt[5]][$ii]+$dzArr[$rangedt[6]][$ii]-$dzArr[$rangedt[7]][$ii]-$dzArr[$rangedt[8]][$ii]-$dzArr[$rangedt[9]][$ii]))*0.9;
				}
				//exit("warning:".$dzArr[$rangedt[0]][$ii]."___".$dzArr[$rangedt[1]][$ii]."___".$dzArr[$rangedt[2]][$ii]."___".$dzArr[$rangedt[3]][$ii]."___".$dzArr[$rangedt[4]][$ii]."___".$dzArr[$rangedt[5]][$ii]."___".$dzArr[$rangedt[6]][$ii]."___".$dzArr[$rangedt[7]][$ii]."___".$dzArr[$rangedt[8]][$ii]);
			    @$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-($dzArr[$rangedt[1]]['sd']+$dzArr[$rangedt[2]]['sd']+$dzArr[$rangedt[3]]['sd']+$dzArr[$rangedt[4]]['sd']+$dzArr[$rangedt[5]]['sd']+$dzArr[$rangedt[6]]['sd']-$dzArr[$rangedt[7]]['sd']-$dzArr[$rangedt[8]]['sd']-$dzArr[$rangedt[9]]['sd']))*0.9;
				//$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-$dzArr[$rangedt[1]]['sd']);
			break;
			case '400002':
			#100004=Penjualan,(300007,300011,300012)=total biaya produksi,(300014,300015,300016)=saldo awal,(300018,300019,300020)=saldo akhir
			#PENJUALAN-(TOTAL BIAYA PRODUKSI+SALDO AWAL+PEMBELIAN CPOKER-SALDO AKHIR)*0.1
				for ($i = 1; $i <= $bulan; $i++) {
						if($i<10)$ii='0'.$i; else $ii=$i;
						@$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]-($dzArr[$rangedt[1]][$ii]+$dzArr[$rangedt[2]][$ii]+$dzArr[$rangedt[3]][$ii]+$dzArr[$rangedt[4]][$ii]+$dzArr[$rangedt[5]][$ii]+$dzArr[$rangedt[6]][$ii]-$dzArr[$rangedt[7]][$ii]-$dzArr[$rangedt[8]][$ii]-$dzArr[$rangedt[9]][$ii]))*0.1;
				}
				//exit("warning:".$dzArr[$rangedt[0]][$ii]."___".$dzArr[$rangedt[1]][$ii]."___".$dzArr[$rangedt[2]][$ii]."___".$dzArr[$rangedt[3]][$ii]."___".$dzArr[$rangedt[4]][$ii]."___".$dzArr[$rangedt[5]][$ii]."___".$dzArr[$rangedt[6]][$ii]."___".$dzArr[$rangedt[7]][$ii]."___".$dzArr[$rangedt[8]][$ii]);
			    @$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-($dzArr[$rangedt[1]]['sd']+$dzArr[$rangedt[2]]['sd']+$dzArr[$rangedt[3]]['sd']+$dzArr[$rangedt[4]]['sd']+$dzArr[$rangedt[5]]['sd']+$dzArr[$rangedt[6]]['sd']-$dzArr[$rangedt[7]]['sd']-$dzArr[$rangedt[8]]['sd']-$dzArr[$rangedt[9]]['sd']))*0.1;
				//$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-$dzArr[$rangedt[1]]['sd']);
			break;
			case'200009':
				for ($i = 1; $i <= $bulan; $i++) {
						if($i<10)$ii='0'.$i; else $ii=$i;
						@$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]/$dzArr[$rangedt[1]][$ii]);
				}
				@$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']/$dzArr[$rangedt[1]]['sd']);
			break;
			case'300007':
				for ($i = 1; $i <= $bulan; $i++) {
						if($i<10)$ii='0'.$i; else $ii=$i;
						@$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii]);
				}
				$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']+$dzArr[$rangedt[1]]['sd']);
			break;
			 
			case'300014':
				for($i = 1; $i <= $bulan; $i++){
					if($i<10)$ii='0'.$i; else $ii=$i;
					$sAwl="select sum(awal".$ii.") as awal from ".$dbname.".keu_saldobulanan where noakun='".$isiRange[$data['nourut']]."' and periode='".$tahun."".$ii."' and kodeorg ".$where." ";
					$qAwl=$owlPDO->query($sAwl) or die(print " Gagal: ".PDOException::getMessage());
					$qAwl->setFetchMode(PDO::FETCH_ASSOC);
					$rAwl=$qAwl->fetch();
					$dzArr[$data['nourut']][$ii]=$rAwl['awal'];
					if($ii=='01'){
						if($rAwl['awal']!=''){
							$dzArr[$data['nourut']]['sd']=$rAwl['awal'];	
						}
					}
				}
			break;
			case'300015':
				for($i = 1; $i <= $bulan; $i++){
					if($i<10)$ii='0'.$i; else $ii=$i;
					$sAwl="select sum(awal".$ii.") as awal from ".$dbname.".keu_saldobulanan where noakun='".$isiRange[$data['nourut']]."' and periode='".$tahun."".$ii."' and kodeorg ".$where."";
					$qAwl=$owlPDO->query($sAwl) or die(print " Gagal: ".PDOException::getMessage());
					$qAwl->setFetchMode(PDO::FETCH_ASSOC);
					$rAwl=$qAwl->fetch();
					$dzArr[$data['nourut']][$ii]=$rAwl['awal'];
					if($ii=='01'){
						if($rAwl['awal']!=''){
							$dzArr[$data['nourut']]['sd']=$rAwl['awal'];	
						}
					}
					
				}
			break;
			case'300016':
				for($i = 1; $i <= $bulan; $i++){
					if($i<10)$ii='0'.$i; else $ii=$i;
					$sAwl="select sum(awal".$ii.") as awal from ".$dbname.".keu_saldobulanan where noakun='".$isiRange[$data['nourut']]."' and periode='".$tahun."".$ii."' and kodeorg ".$where."";
					$qAwl=$owlPDO->query($sAwl) or die(print " Gagal: ".PDOException::getMessage());
					$qAwl->setFetchMode(PDO::FETCH_ASSOC);
					$rAwl=$qAwl->fetch();
					$dzArr[$data['nourut']][$ii]=$rAwl['awal'];
					if($ii=='01'){
						if($rAwl['awal']!=''){
							$dzArr[$data['nourut']]['sd']=$rAwl['awal'];	
						}
					}	
					
				}
			break;
			
			case'400008':
				for ($i = 1; $i <= $bulan; $i++) {
						if($i<10)$ii='0'.$i; else $ii=$i;
						$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii])-$dzArr[$rangedt[2]][$ii];
						$dzArr[$data['nourut']]['sd']+=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii])-$dzArr[$rangedt[2]][$ii];
				}
			break;
			case'400010':
			#400008,400009
				for ($i = 1; $i <= $bulan; $i++) {
						if($i<10)$ii='0'.$i; else $ii=$i;
						$dzArr[$data['nourut']][$ii]=$dzArr[$rangedt[0]][$ii]-$dzArr[$rangedt[1]][$ii];
				}
				$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-$dzArr[$rangedt[1]]['sd']);
			break;
			case'100006':
				$dzArr[$data['nourut']]['sd'] = 0;
				for ($i = 1; $i <= $bulan; $i++) {
						if($i<10)$ii='0'.$i; else $ii=$i;
						@$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii]+$dzArr[$rangedt[2]][$ii]+$dzArr[$rangedt[3]][$ii])-$dzArr[$rangedt[4]][$ii];
						@$dzArr[$data['nourut']]['sd']+=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii]+$dzArr[$rangedt[2]][$ii]+$dzArr[$rangedt[3]][$ii])-$dzArr[$rangedt[4]][$ii];
				}
			break;
		}
        
    }

}

 
if(!empty($dzArr))foreach($dzArr as $data){ // level 0
    if($data['tipe']=='Header'){
        $stream.="<tr class=rowcontent title='".$data['keterangan']."' >
            <td colspan=".(2+6)."><b>".$data['keterangan']." </b></td>
        </tr>"; 
        $stream.="<tr><td colspan=8><div style=\"display:none;\" id=".$data['nourut'].">";

        $stream.="</div></td></tr>";
    }
    else
    if($data['tipe']=='Detail'){
        @$dataPER=($data[$bulan]-$data[$bulanlalu])/$data[$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent title='Click untuk melihat detail' style=cursor:pointer; onclick=\"getLaporanKeuanganDetailv1('".$data['nourut']."','".$data['tipe']."')\">
            <td style='width:10px'></td>
            <td colspan=2 style='width:510px'>".$data['keterangan']." </td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td style='width:120px' align=right>".number_format(isset($data[$ii])? $data[$ii]: 0,2)."</td>";
            }
			if(!isset($data['sd'])) $data['sd']=0;
			if(!isset($data[$bulan])) $data[$bulan]=0;
			if(!isset($data[$bulanlalu])) $data[$bulanlalu]=0;
			$stream.="<td style='width:120px' align=right>".number_format($data['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($data[$bulan]-$data[$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";          
        $stream.="<tr><td colspan=".(2+6)."><div style=\"display:none;\" id=".$data['nourut'].">";

        $stream.="</div></td></tr>";
    }
    else
    if($data['tipe']=='Total'){
        @$subtotalPER=($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu])/$dzArr[$data['nourut']][$bulanlalu]*100;
        $stream.="<tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'><b>".$data['keterangan']."</b></td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td style='width:120px' align=right><b>".number_format($dzArr[$data['nourut']][$ii],2)."</b></td>";                
            }
            $stream.="<td style='width:120px' align=right><b>".number_format($dzArr[$data['nourut']]['sd'],2)."</b></td>
                <td style='width:120px' align=right><b>".number_format($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu],2)."</b></td>    
            <td style='width:50px' align=right><b>".number_format($subtotalPER,2)."</b></td>    
        </tr>
        <tr class=rowcontent><td colspan=".(2+8)."></td></tr>
        ";
    }        
    if($unit==''){
        $sKd="select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='HOLDING'";
		$qKd=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
		$qKd->setFetchMode(PDO::FETCH_ASSOC);
        $rKd=$qKd->fetch();
        $unit=$rKd['kodeorganisasi'];
        $sCek="select * from ".$dbname.".keu_4rasio where periode='".$periode."' and kodeorg='".$unit."'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
		if($rCek!=0){
        	$bbnpokok=floatval($dzArr['300007'][$bulan]-(($dzArr['300014'][$bulan]+$dzArr['300015'][$bulan]+$dzArr['300016'][$bulan])+($dzArr['300018'][$bulan]+$dzArr['300019'][$bulan]+$dzArr['300020'][$bulan])));
            $sUpdate="update ".$dbname.".keu_4rasio set 
                      pendapatan='".floatval($dzArr['100006'][$bulan])."',
                      lababersih='".floatval(($dzArr['400008'][$bulan]+$dzArr['400009'][$bulan]))."',
                      bebanpokok='".$bbnpokok."',
                      labakotor='".floatval(($dzArr['100006'][$bulan]-$bbnpokok))."',
                      labausaha='".floatval($dzArr['400008'][$bulan])."',
                      beban_keuangan='".floatval($dzArr['400009'][$bulan])."',
                      by_umum='".floatval(($dzArr['200004'][$bulan]+$dzArr['300003'][$bulan]+$dzArr['400005'][$bulan]))."',
                      depresiasi='".floatval(($dzArr['300004'][$bulan]+$dzArr['200005'][$bulan]))."'
                      where periode='".$periode."' and kodeorg='".$unit."'";
        }else{
            $sUpdate="insert into ".$dbname.".keu_4rasio (`kodeorg`,`periode`,`pendapatan`,`lababersih`,`bebanpokok`,`labakotor`,`labausaha`,`beban_keuangan`,`by_umum`,`depresiasi`) values 
                     ('".$unit."','".$periode."','".floatval($dzArr['100009'][$bulan])."','".floatval($lababersih)."','".floatval($rpBaru['212005'][$bulan])."','".floatval(($dzArr['100009'][$bulan]+$rpBaru['212005'][$bulan]))."',
                      '".floatval($dzArr['214999'][$bulan])."','".floatval(($dzArr['215005'][$bulan]+$dzArr['216001'][$bulan]))."',
                      '".floatval(($dzArr['200004'][$bulan]+$dzArr['300003'][$bulan]+$dzArr['400005'][$bulan]))."',
                      '".floatval(($dzArr['300004'][$bulan]+$dzArr['200005'][$bulan]))."')";
        }
		try{
			$owlPDO->exec($sUpdate);
		}catch (PDOException $e){
			exit('warning :'.$sUpdate."___".$e->getMessage());
		}
        $unit="";
    }
}

$stream.= "</tbody></tfoot></tfoot></table>";
echo $stream;
*/
?>