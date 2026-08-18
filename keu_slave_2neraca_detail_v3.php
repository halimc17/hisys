<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$nourut = checkPostGet('nourut', '');
$periode = checkPostGet('periode', '');
$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
// $kodelaporan = checkPostGet('kodelaporan', '');
$method = checkPostGet('method', '');

$nourutawal=$nourut;


// echo "qwe".$nourut." ".$periode." ".$pt." ".$unit." ".$method;

$whr="and kodeorg='".$unit."'";
if($unit==''){
	$whr="and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$kodelaporan='NERACA V3';

switch($method){
	case'html':
	
	$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
	$stream.="
		<table cellpading=1 cellspacing=1 ".$border." class=sortable>		
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td> 
				<td align=center>".$_SESSION['lang']['noakun']."</td> 
				<td align=center>".$_SESSION['lang']['namaakun']."</td> 
				<td align=center>".$_SESSION['lang']['jumlah']."</td> 
			</tr>
		</thead>";
	
	$str="select nourut, noakun from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$nourut."' and namalaporan='".$kodelaporan."' order by noakun";
	$res=fetchdata($str);
	foreach($res as $bar){
		$listakun[$bar['noakun']]=$bar['noakun'];
	}	

	

	// ambil saldo awal
	// $str="select periode, noakun, (awal01+awal02+awal03+awal04+awal05+awal06+awal07+awal08+awal09+awal10+awal11+awal12) as awal, kodeorg from ".$dbname.".keu_saldobulanan where periode='".str_replace("-", "", $periode)."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and noakun in (
			// select noakun from ".$dbname.".keu_5mesinlaporandt_akun where nourut = '".$nourut."' and namalaporan = '".$kodelaporan."'
		// )";
		
	
	$perdata=periodeberikut($periode);
	$explodeperdata=explode('-',$perdata);
	$bulanperdata=$explodeperdata[1];
	$kolomthnini="awal".$bulanperdata;
	$perdata=str_replace("-", "",$perdata);	
	$str="select periode, noakun, ".$kolomthnini." as data, kodeorg from ".$dbname.".keu_saldobulanan where periode='".$perdata."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and noakun in (select noakun from ".$dbname.".keu_5mesinlaporandt_akun where nourut = '".$nourut."' and namalaporan = '".$kodelaporan."')";
	// echo $str;
	$res=fetchdata($str);
	foreach($res as $bar){
		// $perx=substr($bar['periode'],0,4).'-'.substr($bar['periode'],4,2);
		// $nourut=$keurut[$bar['noakun']];

		if(substr($nourut,0,1)=='1'){
			$kali=1;
		}else{
			$kali=-1;
		}
		$data[$bar['noakun']][$periode]+=($bar['data']*$kali);
		$datadet[$bar['noakun']][$periode]+=($bar['data']*$kali);
	}
	foreach($listakun as $akun){
		// // if($data[$akun][$periode]==0)continue;
		@$no+=1;
		// if($nourutawal=='2102'){ // kalo 2102 kalimin AP - Related Parties
		// 	$kali=(-1);
		// }else{
		// 	$kali=1;
		// }
		// $data[$akun][$periode]=$data[$akun][$periode]*$kali;
		$stream.="
			<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$akun."</td>
				<td>".$nmakun[$akun]."</td>
				<td align=right>".number_format($data[$akun][$periode])."</td>
			</tr>
		";
		 $tjumlah+=$data[$akun][$periode];
	}
	$stream.="<tr class=rowcontent>
				<td align=center colspan=3>Total</td>
				<td align=right>".number_format($tjumlah)."</td>
			</tr></table>";
	echo $stream;
	
	break;
}












?>