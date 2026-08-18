<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;




$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');


if($periode==''){
	exit("Warning:Periode Masih Kosong");
}


// echo $pt._.$regional._.$unit;exit();
// if($regional=='' && $unit=='' && $pt==''){
	// $where='';
// } else if($regional=='' && $unit=='' && $pt!=''){
    // $where="  and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
// } else if($regional!='' && $unit=='') {
    // $where="  and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            // . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
// } else {
    // $where="  and kodeorg='".$unit."'";
// }


#= ambil daftar pt
$str="select * from ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrkodept[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
}

#= ambil daftar noakun
$str="select * from ".$dbname.".keu_5jenistagihan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrkdjenistagihan[$bar['kode']]=$bar['kode'];
	$nmjenistagihan[$bar['kode']]=$bar['namajenis'];
	$tipejurnal[$bar['kode']]=$bar['jurnal'];
}



$str="select * from ".$dbname.".log_5klbarang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrkdjenistagihan[$bar['kode']]=$bar['kode'];
	$nmjenistagihan[$bar['kode']]=$bar['kelompok'];
	// $tipejurnal[$bar['kode']]=$bar['jurnal'];
}



#= ambil daftar noakun
$str="select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1!='' and tipetransaksi='K'";
// echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrnotagihan[$bar['keterangan1']]=$bar['keterangan1'];
}
	

$str="select * from ".$dbname.".keu_tagihanht where noinvoice in ('".implode("','",$arrnotagihan)."')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	// $arrnotagihan[$bar['keterangan1']]=$bar['keterangan1'];
	if($tipejurnal[$bar['tipeinvoice']]==0){
		@$nilaidpp[$bar['tipeinvoice']][$bar['kodeorg']]=$bar['nilaiinvoice'];
	}
}


$str="select a.*,b.tipeinvoice,b.nilaiinvoice,b.kodeorg from ".$dbname.".keu_tagihandt a 
		left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
		where a.noinvoice in ('".implode("','",$arrnotagihan)."')";
		// echo $str;exit();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	// $arrnotagihan[$bar['keterangan1']]=$bar['keterangan1'];
	if($bar['noakun']=='1170111'){
		@$nilaippn[$bar['tipeinvoice']][$bar['kodeorg']]+=$bar['nilaiinvoice'];
	}
	if($tipejurnal[$bar['tipeinvoice']]==1 and substr($bar['noakun'],0,1)>3){
		@$nilaidpp[$bar['tipeinvoice']][$bar['kodeorg']]+=$bar['nilai'];
	}
}


// echo"<pre>";
// print_r($nilaidpp);
// echo"</pre>";
// exit();
$stream="";


	$stream.="<table class=sortable border=0 cellspacing=1>";
    $stream.="<thead>";
        $stream.="<tr class=rowheader>";
			$stream.="<td align=center></td>";
			foreach($arrkodept as $kodept){
				 $stream.="<td align=center colspan=3><b>".$kodept."</td>";  
			}
		$stream.="</tr>";
		$stream.="<tr class=rowheader>";
			$stream.="<td align=center></td>";
			foreach($arrkodept as $kodept){
				 $stream.="<td align=center><b>DPP</b></td>";  
				 $stream.="<td align=center><b>".$_SESSION['lang']['ppn']."</b></td>";  
				 $stream.="<td align=center><b>".$_SESSION['lang']['total']."</b></td>";  
			}
		$stream.="</tr>";
	$stream.="</thead>";
	
	foreach($arrkdjenistagihan as $kdjenistagihan){
		$stream.="<tr class=rowcontent>";
			// $stream.="<td align=left>".$kdjenistagihan." ".$nmjenistagihan[$kdjenistagihan]."</td>";
			$stream.="<td align=left>".$nmjenistagihan[$kdjenistagihan]."</td>";
			foreach($arrkodept as $kodept){
				@$nilaitotal[$kdjenistagihan][$kodept]=$nilaidpp[$kdjenistagihan][$kodept]+$nilaippn[$kdjenistagihan][$kodept];
				$stream.="<td align=right>".@number_format($nilaidpp[$kdjenistagihan][$kodept])."</td>";  
				$stream.="<td align=right>".@number_format($nilaippn[$kdjenistagihan][$kodept])."</td>";  
				$stream.="<td align=right>".@number_format($nilaitotal[$kdjenistagihan][$kodept])."</td>";  
				@$tnilaidpp[$kodept]+=$nilaidpp[$kdjenistagihan][$kodept];
				@$tnilaippn[$kodept]+=$nilaippn[$kdjenistagihan][$kodept];
				@$tnilaitotal[$kodept]+=$nilaitotal[$kdjenistagihan][$kodept];
			}
		$stream.="</tr>";
 	}

	$stream.="<tr class=rowcontent>";
		// $stream.="<td align=left>".$kdjenistagihan." ".$nmjenistagihan[$kdjenistagihan]."</td>";
		$stream.="<td align=left><b>".$_SESSION['lang']['total']."</b></td>";
		foreach($arrkodept as $kodept){
			// $tnilaitotal[$kodept]=$tnilaidpp[$kodept]+$tnilaippn[$kodept];
			$stream.="<td align=right><b>".number_format($tnilaidpp[$kodept])."</td>";  
			$stream.="<td align=right><b>".number_format($tnilaippn[$kodept])."</td>";  
			$stream.="<td align=right><b>".number_format($tnilaitotal[$kodept])."</td>";  
		}
	$stream.="</tr>";

$stream.= "</tbody></tfoot></tfoot></table>";




if($tipe=='excel'){
	$nop_="realisasi_AP_".$pt."_".$periode;
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
		  echo "<script language=javascript>
				parent.window.alert('Can't convert to excel format');
				</script>";
		   exit;
		 }
		 else
		 {
		  echo "<script language=javascript>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
		 }
		fclose($handle);
	}
} else if ($tipe=='pdf') {
	$dompdf = new Dompdf();
	$dompdf->loadHtml($stream);
	$dompdf->setPaper('A4', 'landscape');
	$dompdf->render();
	$dompdf->stream("Neraca",array("Attachment"=>0));
} else {
	echo $stream;
}


?>