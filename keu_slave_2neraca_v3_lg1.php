<?php
/*
	LAPORAN INI DIAMBIL DARI FILE === keu_slave_2neraca_v3.php ===
	JIKA ADA PERUBAHAN DI FILE DI ATAS, HARUS DIUPDATE JUGA DI SINI
*/

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');
// $tipelaporan = checkPostGet('tipelaporan', '');

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}
#++++++++++++++++++++++++++++++++++++++++++
$kodelaporan='NERACA V2';
$periodesaldo=str_replace("-", "", $periode);

// echo $pt._.$regional._.$unit;exit();
if($unit==''){
    $where="  kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}else{
    $where="  kodeorg='".$unit."'";
}

$str="select nourut, keterangandisplay, tipe, noakundisplay from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$listurut[$bar->nourut]=$bar->nourut;

	$namaurut[$bar->nourut]=$bar->keterangandisplay;
	$tipeurut[$bar->nourut]=$bar->tipe;
	$anakurut[$bar->nourut]=$bar->noakundisplay;
}

$str="select nourut, noakun from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$keurut[$bar->noakun]=$bar->nourut;

	$listakun[$bar->nourut][$bar->noakun]=$bar->noakun;
	if($bar->nourut=='1112'){ // AR - Related Parties
		$list1112[$bar->noakun]=$bar->noakun;
	}
}
$listakun['2102']=$listakun['1112']; // AP - Related Parties = AR - Related Parties
// echo "<pre>";
// print_r($listakun['1112']);
// print_r($listakun['2102']);
// echo "</pre>";
// kepala urut >1 kali minus

// ambil saldo awal
$str="select periode, noakun, (awal01+awal02+awal03+awal04+awal05+awal06+awal07+awal08+awal09+awal10+awal11+awal12) as awal, kodeorg from ".$dbname.".keu_saldobulanan where left(periode,4) between '".$tahunlalu."' and '".$periode."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') order by periode, kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$perx=substr($bar['periode'],0,4).'-'.substr($bar['periode'],4,2);
	$nourut=$keurut[$bar['noakun']];

	if(substr($nourut,0,1)=='1'){
		$kali=1;
	}else{
		$kali=-1;
	}
	$data[$nourut][$perx]+=($bar['awal']*$kali);
	$datadet[$bar['noakun']][$perx]+=($bar['awal']*$kali);
}

// echo"<pre>";
// print_r($data);
// echo"</pre>";


$listptrel=array('KSP','SDK','BPJ','KBP','SNP','SDP','LCK');
$listrelated['KSP']=array('1210101','1210102','1210103','1210104','1210105','1210106','1210107');
$listrelated['SDK']=array('1210111','1210112','1210113','1210114','1210115','1210116','1210117','1210118');
$listrelated['BPJ']=array('1210121','1210122','1210123','1210124');
$listrelated['KBP']=array('1210131','1210132','1210133');
$listrelated['SNP']=array('1210141','1210142','1210143','1210144');
$listrelated['SDP']=array('1210151','1210152','1210153');
$listrelated['LCK']=array('1210201','1210202','1210203');

// ambil akun restricted fund
// KSP = MANDIRI JKT (IDR) 102-00-0748280-2 ( Restricted Funds ) & MANDIRI JKT (IDR) 102-00-0748279-4  ( Restricted Funds )
// BPJ = MANDIRI JKT (IDR) 102-00-0748316-4 ( Restricted Funds )
// SDK : MANDIRI JKT (IDR) 102-00-0748314-9 ( Restricted Funds )
$str="select periode, norek, (awal01+awal02+awal03+awal04+awal05+awal06+awal07+awal08+awal09+awal10+awal11+awal12) as awal, kodeorg from ".$dbname.".keu_saldobank where left(periode,4) between '".$tahunlalu."' and '".$periode."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and norek in (
		select noakun from ".$dbname.".keu_5akunbank where noakun in ('1020007482802','1020007482794','1020007483164','1020007483149') and pemilik in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%')
	)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$perx=substr($bar['periode'],0,4).'-'.substr($bar['periode'],4,2);
	$nourut='1406'; // Restricted Funds
	if(substr($nourut,0,1)=='1'){
		$kali=1;
	}else{
		$kali=-1;
	}
	$data[$nourut][$perx]+=($bar['awal']*$kali);
	$datadet['1110101_'][$perx]+=($bar['awal']*$kali); // ini gimana

	$nourut='1101'; // Cash and Cash Equivalents
	if(substr($nourut,0,1)=='1'){
		$kali=1;
	}else{
		$kali=-1;
	}
	$data[$nourut][$perx]-=($bar['awal']*$kali);
	$datadet['1110101'][$perx]-=($bar['awal']*$kali);
}
// echo "<pre>";
// print_r($datadet['1110101']);
// echo "</pre>";

// ambil mutasi posted khusus restricted fund
$str="select tipetransaksi, jumlah, tanggal from ".$dbname.".keu_kasbankht where left(tanggal,4) between '".$tahunlalu."' and '".$periode."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and rekening in (
		select noakun from ".$dbname.".keu_5akunbank where noakun in ('1020007482802','1020007482794','1020007483164','1020007483149') and pemilik in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%')
	) and pembayaran = '1' and posting = '1' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$perx=substr($bar['tanggal'],0,7);

	if($bar['tipetransaksi']=='M'){
		$kali=+1;
	}else{
		$kali=-1;
	}
	$data['1406'][$perx]+=($bar['jumlah']*$kali); // Restricted Funds

	$data['1101'][$perx]-=($bar['jumlah']*$kali); // Cash and Cash Equivalents
}	

// // echo "<pre>";
// // print_r($datadet['1110101']);
// // echo "</pre>";

// ambil mutasi
$str="select tanggal, noakun, jumlah, kodeorg from ".$dbname.".keu_jurnaldt_vw where left(tanggal,4) between '".$tahunlalu."' and '".$periode."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$perx=substr($bar['tanggal'],0,7);
	$nourut=$keurut[$bar['noakun']];

	if(substr($nourut,0,1)=='1'){
		$kali=1;
	}else{
		$kali=-1;
	}
	$data[$nourut][$perx]+=($bar['jumlah']*$kali);
	$datadet[$bar['noakun']][$perx]+=($bar['jumlah']*$kali);
}

// susun negatif ato positif per PT
for ($x = 1; $x <= 12; $x++) {
	$xx = sprintf("%02d", $x);
	$perx1=$periode.'-'.$xx;
	$perx2=$tahunlalu.'-'.$xx;

	foreach($listptrel as $ptx){
		foreach($list1112 as $akunrel){
			if (in_array($akunrel, $listrelated[$ptx])){
				$totalrelated[$ptx][$perx1]+=$datadet[$akunrel][$perx1];
				$totalrelated[$ptx][$perx2]+=$datadet[$akunrel][$perx2];
			}	
		}	
	}
	foreach($listptrel as $ptx){
		if($totalrelated[$ptx][$perx1]<0){ // pindah dari 1112 ke 2102
			$data['1112'][$perx1]-=$totalrelated[$ptx][$perx1]; // AR - Related Parties
			$data['2102'][$perx1]+=(-1)*$totalrelated[$ptx][$perx1]; // AP - Related Parties
		} 
	}
	foreach($listptrel as $ptx){
		if($totalrelated[$ptx][$perx2]<0){ // pindah dari 1112 ke 2102
			$data['1112'][$perx2]-=$totalrelated[$ptx][$perx2]; // AR - Related Parties
			$data['2102'][$perx2]+=(-1)*$totalrelated[$ptx][$perx2]; // AP - Related Parties
		} 
	}
}

// // echo "<pre>";
// // print_r($totalrelated);
// // echo "</pre>";

// susun total
for ($x = 1; $x <= 12; $x++) {
	$xx = sprintf("%02d", $x);
	$perx1=$periode.'-'.$xx;
	$perx2=$tahunlalu.'-'.$xx;
	foreach($listurut as $urut){
		// if($urut=='2103'){ // Closing Stock
		// 	$data[$urut][$periode]=(-1)*$data[$urut][$periode];
		// 	$data[$urut]['sd']=(-1)*$data[$urut]['sd'];
		// }

		$qwe=explode(',', $anakurut[$urut]);
		foreach($qwe as $anak){
			if($anak!=''){
				$amin=substr($anak,0,1);
				if($amin=='-'){ // -1234
					$anak2=substr($anak,1,4);
					$data[$urut][$perx1]-=$data[$anak2][$perx1];
					$data[$urut][$perx2]-=$data[$anak2][$perx2];
				}else{ // 1234
					$data[$urut][$perx1]+=$data[$anak][$perx1];
					$data[$urut][$perx2]+=$data[$anak][$perx2];
				}
			}
		}
	}
}	

// echo "<pre>";
// print_r($keurut);
// print_r($data);
// echo "</pre>";

$stream="";
	
// if($tipe=='html'){
// 	$stream.="<div style='position:fixed;'><table class=sortable border=0 cellspacing=1>";
//     $stream.="<thead>";
//         $stream.="<tr class=rowheader>";
//         $stream.="<td width='395px;'></td>";
//         $stream.="<td align=center width='200px;'>".$periode."</td>";
//         $stream.="</tr>";
//     $stream.="</thead><tbody></tbody>";
//     $stream.="</table>";
// 	$stream.="</div><br>";
// 	$stream.="<table class=sortable border=0 cellspacing=1><thead><tr><td colspan=7 width='800px;'></td></tr></thead><tbody>";
// }else{
// 	$stream.="<table class=sortable border=0 cellspacing=1><tbody>";
// 	$stream.="<tr class=rowheader>";
//         $stream.="<td colspan=5></td>";
//         $stream.="<td align=center width='200px;'>".$periode."</td>";
// 	$stream.="</tr>";
// }

if($unit==''){
	$unitx=$pt;
}else{
	$unitx=$unit;
}

$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unitx."'");
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$stream ="Laporan Keuangan - Neraca (Laporan Group 1)<br>";
$stream.="".$unitx." - ".$nmorg[$unitx]."<br>";
$stream.="Periode ".$periode."<br><br>";
if($tipe=='excel'){
	$border='1';
}else{
	$border='0';
}
$stream.="<table class=sortable border=".$border." cellspacing=0>
    <thead>
        <tr class=rowheader>
            <td style='width:520px' align=center colspan=3 rowspan=2>Description</td>";
for ($x = 1; $x <= 12; $x++) {
	$xx = sprintf("%02d", $x);
	$bulan=date('M', strtotime($periode.'-'.$xx.'-01'));
	$stream.="<td style='width:120px' align=center colspan=3>".$bulan."</td>";
}
$stream.="</tr>
        <tr class=rowheader>";
for ($x = 1; $x <= 12; $x++) {
	$xx = sprintf("%02d", $x);
	$stream.="<td style='width:120px' align=center>".$periode."</td>
			<td style='width:120px' align=center>".$tahunlalu."</td>
			<td style='width:120px' align=center>Change</td>";
}             
$stream.="</tr>
    </thead><tbody>";

if(!empty($listurut))foreach($listurut as $urut){ // level 0
    if($tipeurut[$urut]=='Header'){
        $stream.="<tr class=rowcontent title='".$namaurut[$urut]."' >
            <td colspan=3><b>".$namaurut[$urut]." </b></td>";
        for ($x = 1; $x <= 12; $x++) {
        	$stream.="<td></td><td></td><td></td>";
        }    
        $stream.="</tr>"; 
    }else if($tipeurut[$urut]=='Detail'){
        // $stream.="<tr class=rowcontent title='Click untuk melihat detail' style=cursor:pointer; onclick=\"lihatDetailNeraca('".$urut."','".$periode."','".$pt."','".$unit."',event);\">
        //     <td style='width:10px'></td>
        //     <td colspan=2 style='width:510px'>".$namaurut[$urut]." </td>";
        $stream.="<tr class=rowcontent>
            <td style='width:10px'></td>
            <td colspan=2 style='width:510px'>".$namaurut[$urut]." </td>";
        for ($x = 1; $x <= 12; $x++) {
        	$xx = sprintf("%02d", $x);
        	$perx1=$periode.'-'.$xx;
        	$perx2=$tahunlalu.'-'.$xx;
        	$datax[$urut]['rp']=$data[$urut][$perx1]-$data[$urut][$perx2];
        	@$datax[$urut]['persen']=$datax[$urut]['rp']/$data[$urut][$perx2]*100;
        	$stream.="<td style='width:120px' align=right>".number_format($data[$urut][$perx1])."</td>";
        	$stream.="<td style='width:120px' align=right>".number_format($data[$urut][$perx2])."</td>";
        	$stream.="<td style='width:120px' align=right>".number_format($datax[$urut]['persen'])."%</td>";
        }    
        
		$stream.="</tr>";

  //       if($tipelaporan=='detail'){
		// 	if($urut=='1112'){ // AR - Related Parties
		// 		foreach($listptrel as $ptx){
		// 			if($totalrelated[$ptx]<0){ // pindah dari 1112 ke 2102
		// 				foreach($listrelated[$ptx] as $akun){
		// 					unset($listakun[$urut][$akun]);
		// 				}
		// 			}
		// 		}			
		// 	}
		// 	if($urut=='2102'){ // AP - Related Parties
		// 		foreach($listptrel as $ptx){
		// 			if($totalrelated[$ptx]>=0){ // pindah dari 1112 ke 2102
		// 				foreach($listrelated[$ptx] as $akun){
		// 					unset($listakun[$urut][$akun]);
		// 				}
		// 			}
		// 		}			
		// 	}
		// 	foreach($listakun[$urut] as $axun){
		// 		if($urut=='2102')$kali=(-1); else $kali=1; // kalo 2102 AP - Related Parties kaliminnya di sini karena kiriman dari 1112 AR - Related Parties
		// 		$datadet[$axun][$periode]=$datadet[$axun][$periode]*$kali;
		//         $stream.="<tr class=rowcontent>
		//             <td colspan=2></td>
		//             <td>".$axun." - ".$nmakun[$axun]." </td>
		//             ";
		//         $stream.="<td></td><td style='width:120px' align=right>".number_format($datadet[$axun][$periode])."</td>";
		// 		$stream.="</tr>";
		// 	}
		// }

    }else if($tipeurut[$urut]=='Total'){
        $stream.="<tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'><b>".$namaurut[$urut]."</b></td>";
        for ($x = 1; $x <= 12; $x++) {
        	$xx = sprintf("%02d", $x);
        	$perx1=$periode.'-'.$xx;
        	$perx2=$tahunlalu.'-'.$xx;
        	$datax[$urut]['rp']=$data[$urut][$perx1]-$data[$urut][$perx2];
        	@$datax[$urut]['persen']=$datax[$urut]['rp']/$data[$urut][$perx2]*100;
	        $stream.="<td style='width:120px' align=right><b>".number_format($data[$urut][$perx1])."</b></td>";
	       	$stream.="<td style='width:120px' align=right><b>".number_format($data[$urut][$perx2])."</b></td>";
	       	$stream.="<td style='width:120px' align=right><b>".number_format($datax[$urut]['persen'])."%</b></td>";	       	
		}
                
        $stream.="</tr>
        <tr class=rowcontent><td colspan=39></td></tr>
        ";
    }
}

$stream.= "</tbody></tfoot></tfoot></table>";
	

// $stream.= "</tbody></tfoot></tfoot></table>";

if($tipe=='excel'){

	$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
	$qwe=date("YmdHms");
	// $nop_="Neraca_".$pt.$unit.$periode."___".$qwe;
	// if(strlen($stream)>0)
	// {
	// 	 $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
	// 	 gzwrite($gztralala, $stream);
	// 	 gzclose($gztralala);
	// 	 echo "tempExcel/".$nop_.".xls.gz";
	// 	 // echo "<script language=javascript1.2>
	// 		// window.location='tempExcel/".$nop_.".xls.gz';
	// 		// </script>";
	// }

	$nop_="NeracaLG1-".$pt."_".$unit."_".$periode."___".$qwe;
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
		 	echo 'Can not convert to excel format';
		  // echo "<script language=javascript>
				// parent.window.alert('Can't convert to excel format');
				// </script>";
		   exit;
		 }
		 else
		 {
		 	echo "tempExcel/".$nop_.".xls";		 	
		  // echo "<script language=javascript>
				// window.location='tempExcel/".$nop_.".xls';
				// </script>";
		 }
		fclose($handle);
	}
} else if ($tipe=='pdf') {
	// $dompdf = new Dompdf();
	// $dompdf->loadHtml($stream);
	// $dompdf->setPaper('A4', 'landscape');
	// $dompdf->render();
	// $dompdf->stream("Neraca",array("Attachment"=>0));
} else {
	echo $stream;
}


?>