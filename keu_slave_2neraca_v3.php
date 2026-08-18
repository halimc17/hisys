<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');
$tipelaporan = checkPostGet('tipelaporan', '');
$tampilannol = checkPostGet('tampilannol', '');

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];

$newperiode = tglakhirbulan(substr($periode,5,2))." ".numToMonth(substr($periode,5,2),"I","long")." ".substr($periode,0,4);

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
$kodelaporan='NERACA V3';
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

$str="select nourut, noakun from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut asc,noakun asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$keurut[$bar->noakun]=$bar->nourut;
	if($tampilannol=='0'){
		$listakun[$bar->nourut][$bar->noakun]=$bar->noakun;
	}
}

// ambil saldo awal
// $str="select periode, noakun, (awal01+awal02+awal03+awal04+awal05+awal06+awal07+awal08+awal09+awal10+awal11+awal12) as awal, kodeorg from ".$dbname.".keu_saldobulanan where periode='".str_replace("-", "", $periode)."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%')";


$perdata=periodeberikut($periode);
$explodeperdata=explode('-',$perdata);
$bulanperdata=$explodeperdata[1];
$kolomthnini="awal".$bulanperdata;
$perdata=str_replace("-", "",$perdata);	
$str="select periode, noakun, ".$kolomthnini." as data, kodeorg from ".$dbname.".keu_saldobulanan where periode='".$perdata."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%')";
$res=fetchdata($str);
foreach($res as $bar){
	if(substr($bar['noakun'],0,1)!='1'){
		$bar['data']=$bar['data']*-1;
	}
	@$nourut=$keurut[$bar['noakun']];
	@$data[$nourut][$periode]+=$bar['data'];
	@$datadet[$bar['noakun']][$periode]+=$bar['data'];
	
	if($tampilannol=='1'){
		if($bar['data']!='0'){
			$listakun[$nourut][$bar['noakun']]=$bar['noakun'];
		}
	}
	
	
	
}

// echo"<pre>";
// print_r($listnoakun);
foreach($listurut as $urut){
	$qwe=explode(',', $anakurut[$urut]);
	foreach($qwe as $anak){
		if($anak!=''){
			$amin=substr($anak,0,1);
			if($amin=='-'){ // -1234
				@$anak2=substr($anak,1,4);
				@$data[$urut][$periode]-=$data[$anak2][$periode];
				// $data[$urut]['sd']-=$data[$anak2]['sd'];
			}else{ // 1234
				@$data[$urut][$periode]+=$data[$anak][$periode];
				// $data[$urut]['sd']+=$data[$anak]['sd'];
			}
		}
	}
}

// echo "<pre>";
// print_r($keurut);
// print_r($data);
// echo "</pre>";

$stream="";
	
if($tipe=='html'){
	$stream.="<div style='position:fixed;'><table class=sortable border=0 cellspacing=1 cellpadding=5>";
    $stream.="<thead>";
        $stream.="<tr class=rowheader>";
        $stream.="<td width='395px;'></td>";
        $stream.="<td align=center width='200px;'>".$newperiode."</td>";
        $stream.="</tr>";
    $stream.="</thead><tbody></tbody>";
    $stream.="</table>";
	$stream.="</div><br>";
	$stream.="<table class=sortable border=0 cellspacing=1 cellpadding=5><thead><tr><td colspan=7 width='800px;'></td></tr></thead><tbody>";
}else{
	$stream.="<table class=sortable border=0 cellspacing=1 cellpadding=5><tbody>";
	$stream.="<tr class=rowheader>";
        $stream.="<td colspan=5></td>";
        $stream.="<td align=center width='200px;'>".$newperiode."</td>";
	$stream.="</tr>";
}

if($unit==''){
	$unitx=$pt;
}else{
	$unitx=$unit;
}

$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unitx."'");
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$stream ="Laporan Keuangan - Neraca<br>";
$stream.="".$unitx." - ".$nmorg[$unitx]."<br>";
$stream.="Periode ".$newperiode."<br><br>";
if($tipe=='excel'){
	$border='1';
}else{
	$border='0';
}
$stream.="<table class=sortable border=".$border." cellspacing=0 cellpadding=5>
    <thead>
        <tr class=rowheader>
            <td style='width:520px' align=center colspan=3>".$_SESSION['lang']['keterangan']."</td>
            ";
$stream.="<td style='width:120px' align=center colspan=2>S/D ".$newperiode."</td>";    
$stream.="
        </tr>
    </thead><tbody>";

if(!empty($listurut))foreach($listurut as $urut){ // level 0
    if($tipeurut[$urut]=='Header'){
        $stream.="<tr class=rowcontent title='".$namaurut[$urut]."' >
            <td colspan=5><b>".$namaurut[$urut]." </b></td>
        </tr>"; 
        // $stream.="<tr><td colspan=5><div style=\"display:none;\" id=no_".$urut.">";
        // $stream.="</div></td></tr>";
    }else if($tipeurut[$urut]=='Detail'){
        $stream.="<tr class=rowcontent title='Click untuk melihat detail' style=cursor:pointer; onclick=\"lihatDetailNeraca('".$urut."','".$periode."','".$pt."','".$unit."',event);\">
            <td style='width:10px'></td>
            <td colspan=2 style='width:510px'>".$namaurut[$urut]." </td>
            ";
        $stream.="<td style='width:120px' align=right>".number_format($data[$urut][$periode])."</td><td></td>";
		$stream.="</tr>";

        // $stream.="<tr><td colspan=5><div style=\"display:none;\" id=no_".$urut.">";
        // $stream.="</div></td></tr>";
        if($tipelaporan=='detail'){
			
			foreach($listakun[$urut] as $axun){
				// if($urut=='2102')$kali=(-1); else $kali=1; 
				// $datadet[$axun][$periode]=$datadet[$axun][$periode]*$kali;
		        $stream.="<tr class=rowcontent>
		            <td colspan=2></td>
		            <td>".$axun." - ".$nmakun[$axun]." </td>
		            ";
		        $stream.="<td></td><td style='width:120px' align=right>".number_format($datadet[$axun][$periode])."</td>";
				$stream.="</tr>";
			}
		}

    }else if($tipeurut[$urut]=='Total'){
        $stream.="<tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'><b>".$namaurut[$urut]."</b></td>
            ";
        $stream.="<td style='width:120px' align=right><b>".number_format($data[$urut][$periode])."</b></td><td></td>";                
        $stream.="
        </tr>
        <tr class=rowcontent><td colspan=5></td></tr>
        ";
    }
}

$stream.= "</tbody></tfoot></tfoot></table>";
	

$printtime="Print Time:".date('Y-m-d H:i:s')."_By:".$_SESSION['empl']['name'];
if($tipe=='excel'){
	$nop="Neraca-".$pt."_".$unit."_".$periode."___".$printtime.".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet("NERACA", $stream);
	$xls->headers($nop);
	echo $xls->buildFile();
	/*
	$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
	$qwe=date("YmdHms");
	$nop_="Neraca-".$pt."_".$unit."_".$periode."___".$qwe;
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
	*/
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