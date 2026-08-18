<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$pt=$_POST['pt'];
$unit=$_POST['unit']; //kebun
$periode=$_POST['periode'];

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$bulan=$qwe[1];

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

// ambil transaksi
$str="select tanggal, noakun, jumlah, kodeorg from ".$dbname.".keu_jurnaldt_vw where left(tanggal,4)='".$tahun."' and left(tanggal,7)<='".$periode."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') order by tanggal asc";
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

	if($perx<=$periode){
		// echo "</br>".$perx." ".substr($bar['tanggal'],0,7);
		if($masukkeurut!='')
		$data[$masukkeurut]['sd']+=($kali*$bar['jumlah']);
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
	
	if($masukkeurut=='2103'){
		$arrtemp['2101'][$perx]=$data['2103']['sd'];
	}
	
	$data['2101'][$periode]=abs($arrtemp['2101'][periodelalu($periode)]);
	// $data['2101'][$periode]=($data['2103'][periodelalu($periode)]*-1);
	$data['2103'][$periode]=$data['2103']['sd'];

	
}

// echo"<pre>";
// print_r($data);
// echo"</pre>";

// // khusus Opening Stock dan Closing Stock ambil dari noakun 11502 (awal n akhir) ato dari keu_4hpp?
// // ind : ambil dari keu_4hpp

$str="select awal01, noakun from ".$dbname.".keu_saldobulanan where left(periode,4)='".$tahun."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and noakun in ('1150201','1150203')";
$res=fetchdata($str);
foreach($res as $bar){
	if($periode==$tahun.'-01'){
		$data['2101'][$periode]+=$bar['awal01'];
	}
	// $data['2101']['sd']+=$bar['awal01'];
}

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
	// if($urut=='2103'){ // Closing Stock
	// 	$data[$urut][$periode]=(-1)*$data[$urut][$periode];
	// 	$data[$urut]['sd']=(-1)*$data[$urut]['sd'];
	// }
	if($urut=='3011' || $urut=='4001' || $urut=='4002'){ // sabinus: ini tanda nya harus nya minus pak
		$data[$urut][$periode]=(-1)*$data[$urut][$periode];
		$data[$urut]['sd']=(-1)*$data[$urut]['sd'];
	}
	$qwe=explode(',', $anakurut[$urut]);
	foreach($qwe as $anak){
		if($anak!=''){
			$amin=substr($anak,0,1);
			if($amin=='-'){ // -1234
				$anak2=substr($anak,1,4);
				$data[$urut][$periode]-=$data[$anak2][$periode];
				$data[$urut]['sd']-=$data[$anak2]['sd'];
			}else{ // 1234
				$data[$urut][$periode]+=$data[$anak][$periode];
				$data[$urut]['sd']+=$data[$anak]['sd'];
			}
		}
	}
}

if($unit==''){
	$unitx=$pt;
}else{
	$unitx=$unit;
}

$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unitx."'");
$stream ="Laporan Keuangan - Laba Rugi<br>";
$stream.="".$unitx." - ".$nmorg[$unitx]."<br>";
$stream.="Periode ".$periode."<br><br>";
$stream.="<table class=sortable border=0 cellspacing=0 cellpading=10>
    <thead>
        <tr class=rowheader>
            <td style='width:520px' align=center colspan=3 rowspan=2>Description</td>
            ";
$stream.="<td style='width:120px' align=center rowspan=2>".$periode." </td>";    
$stream.="<td style='width:120px' align=center rowspan=2>SDBI</td>
        </tr>
    </thead><tbody>";

if(!empty($listurut))foreach($listurut as $urut){ // level 0
    if($tipeurut[$urut]=='Header'){
        $stream.="<tr class=rowcontent title='".$namaurut[$urut]."' >
            <td colspan=5><b>".$namaurut[$urut]." </b></td>
        </tr>"; 
        $stream.="<tr><td colspan=5><div style=\"display:none;\" id=no_".$urut.">";
        $stream.="</div></td></tr>";
    }else if($tipeurut[$urut]=='Detail'){
        $stream.="<tr class=rowcontent title='Click untuk melihat detail' style=cursor:pointer; onclick=\"getLaporanKeuanganDetailv2('".$urut."','".$tipeurut[$urut]."')\">
            <td style='width:10px'></td>
            <td colspan=2 style='width:510px'>".$namaurut[$urut]." </td>";
			
		$stream.="<td style='width:120px' align=right>".number_format($data[$urut][$periode])."</td>";
		$stream.="<td style='width:120px' align=right>".number_format($data[$urut]['sd'])."</td>";
	
		$stream.="</tr>";

        $stream.="<tr><td colspan=5><div style=\"display:none;\" id=no_".$urut.">";
        $stream.="</div></td></tr>";
    }else if($tipeurut[$urut]=='Total'){
        $stream.="<tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'><b>".$namaurut[$urut]."</b></td>
            ";
        $stream.="<td style='width:120px' align=right><b>".number_format($data[$urut][$periode])."</b></td>";                
        $stream.="<td style='width:120px' align=right><b>".number_format($data[$urut]['sd'])."</b></td>
        </tr>
        <tr class=rowcontent><td colspan=5></td></tr>
        ";
    }
}

$stream.= "</tbody></tfoot></tfoot></table>";
echo $stream;
?>