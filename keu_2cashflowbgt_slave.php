<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;

$pt       = checkPostGet('pt', '');
$regional = checkPostGet('regional', '');
$unit     = checkPostGet('unit', '');
$periode  = checkPostGet('periode', '');
$tipe     = checkPostGet('tipe', '');


if($periode==''){
	exit("Warning:Periode Masih Kosong");
}

$kodelaporan='CASH FLOW';
$periode1=$periode.'-01';
$periode2=$periode.'-12';
$arrperiode=month_inbetween($periode1,$periode2);

if($regional=='' && $unit=='' && $pt==''){
	$where='';
	$whadj='';
} else if($regional=='' && $unit=='' && $pt!=''){
    $where="  and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
    $whadj="  and left(kodeunit,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
} else if($regional!='' && $unit=='') {
    $where="  and left(kodeorg,4) in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'))"; 
    $whadj="  and left(kodeunit,4) in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'))"; 
} else {
    $where="  and left(kodeorg,4)='".$unit."'";
    $whadj="  and left(kodeunit,4)='".$unit."'";
}


$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;
    }
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    $dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay; #= ini buat total
    $dzArr[$bar->nourut]['rubahoperatr']=$bar->rubahoperatr;
    $dzArr[$bar->nourut]['exception']=$bar->exception;
    $dzArr[$bar->nourut]['exceptiondigit']=$bar->exceptiondigit;
    $dzArr[$bar->nourut]['tampil']=$bar->tampil;
    $dzArr[$bar->nourut]['detail']=$bar->detail;
}



$daftarakun=array();
$nouruttemp='';
#= ambil jumlah
$str="select count(*) as jumlah,nourut from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' group by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$jumlahdaftar[$bar->nourut]=$bar->jumlah;
	$dzArr[$bar->nourut]['jumlahakun']=$bar->jumlah;
}

#= ambil daftar noakun
$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if($nouruttemp==$bar->nourut){
		$no++;	
	}else{
		$no=1;
	}
	
	if($nouruttemp==$bar->nourut){
		if($no<$jumlahdaftar[$bar->nourut]){
			$daftarakun[$bar->nourut].=$bar->noakun.',';
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun.',';
		}else{
			$daftarakun[$bar->nourut].=$bar->noakun;
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun;
		}
	}else{
		if($jumlahdaftar[$bar->nourut]==1){ #= hanya 1 akun saja
			@$daftarakun[$bar->nourut].=$bar->noakun;
			@$dzArr[$bar->nourut]['noakun'].=$bar->noakun;
		} else{
			$daftarakun[$bar->nourut].=$bar->noakun.',';
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun.',';
		}
	}
	$nouruttemp=$bar->nourut;
}

$str="select * from ".$dbname.".keu_5mesinlaporandt_kodejurnal where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['tipe']=='budget'){		
		if($nouruttempbgt==$bar['nourut']){
			$kodejurnalbgt[$bar['nourut']].=",'".trim($bar['kodejurnal'])."'";
		}else{
			$kodejurnalbgt[$bar['nourut']].="'".trim($bar['kodejurnal'])."'";
		}
		$nouruttempbgt=$bar['nourut'];
	}
	if($bar['tipe']=='realisasi'){		
		if($nouruttempact==$bar['nourut']){
			$kodejurnalact[$bar['nourut']].=",'".trim($bar['kodejurnal'])."'";
		}else{
			$kodejurnalact[$bar['nourut']].="'".trim($bar['kodejurnal'])."'";
		}
		$nouruttempact=$bar['nourut'];
	}
}


$stream="";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' or length(kodeorganisasi)='3' ";
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['induk']=='' and $bar['tipe']=='HOLDING'){
		$kodeholding=$bar['kodeorganisasi'];
	}
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

if($tipe!='html'){	
	$stream.=" ".$nmorg[$kodeholding]."<br>";
	$stream.="CASHFLOW TREND REPORT - ".$periode." (Rp.)<br>";
	if($pt==''){
		$streamdetail.="<br>";
	} else if($regional==''){
		$unittampil 	= $nmorg[$pt];
		$stream.="".$unittampil."<br>";
	} else if($unit==''){
		$unittampil 	= 'Seluruh Unit Regional '.$regional.' '.$nmorg[$pt];
		$stream.="".$unittampil."<br>";
	}else{
		$unittampil = $unit;
		$stream.="".$unittampil." - ".$nmorg[$unit]."<br>";
	}
	$stream.="<br>";
}
if($tipe=='html'){
	$stream.="<table class=sortable cellpadding=5 border=0 cellspacing=1>";
    $stream.="<thead>";
        $stream.="<tr class=rowheader>";
			$stream.="<th align=center width='300px;'><b>".$_SESSION['lang']['keterangan']."</th>";
			foreach($arrperiode as $per){
				$stream.="<th align=center width='20px;'><b>".numToMonth(substr($per,5,2),'I','long')."</th>";  
			}			
			$stream.="<th align=center><b>Yth<br>Budget</b></th>";
		$stream.="</tr>";
	$stream.="</thead>";
}else{
	$stream.="<table class=sortable border=1 cellspacing=1><tbody>";
		$stream.="<tr class=rowheader>";
			$stream.="<td  bgcolor=#BDBDBD align=center width='300px;'><b>".$_SESSION['lang']['keterangan']."</td>";
			foreach($arrperiode as $per){
				 $stream.="<td align=center bgcolor=#BDBDBD><b>".numToMonth(substr($per,5,2),'I','long')."</td>";  
			}
			$stream.="<td  bgcolor=#BDBDBD align=center><b>YTD<br>Budget</b></td>";
		$stream.="</tr>";
	$stream.="</thead>";
}


$range=range(1,12);
$sumbgt=$sumbgtk="";
foreach($range as $b){
	if($b<12){		
		$sumbgt.="sum(rp".addZero($b,2).") as rp".addZero($b,2).", ";
		$sumbgtk.="sum(k".addZero($b,2).") as rp".addZero($b,2).", ";
	}else{
		$sumbgt.="sum(rp".addZero($b,2).") as rp".addZero($b,2);
		$sumbgtk.="sum(k".addZero($b,2).") as rp".addZero($b,2);
	}
}

if(!empty($dzArr))foreach($dzArr as $data){
	$addbetween='';
	if((@$data['jumlahakun']>0 or @$data['jumlahakun']!='') and $data['tipe']=='Detail'){
		$addbetweenbudgetkode='';
		$addbetween=" and noaruskas in (".$data['noakun'].")";
		$addbetweenbudget=" and aruskas in (".$data['noakun'].")";
		if($kodejurnalbgt[$data['nourut']]!=''){
			$addbetweenbudgetkode=" and kodebudget in (".$kodejurnalbgt[$data['nourut']].")";			
		}
		
		/* $str="select sum(jumlah) as jumlah,substr(tanggal,1,7) as periode,tipetransaksi,kurs from ".$dbname.".keu_kasbankdtht_vw where 1=1 and pembayaran=1 ".$addbetween."  and tanggal like '".$periode."%'  ".$where." group by substr(tanggal,1,7), tipetransaksi, kurs";	
		$res=fetchdata($str);
		foreach($res as $bar){
			$bar['jumlah']=$bar['jumlah']*$bar['kurs'];
			if($bar['tipetransaksi']=='K'){
				$bar['jumlah']=$bar['jumlah']*-1;
			}
			@$dzArr[$data['nourut']][$bar['periode']]+=$bar['jumlah'];
			@$dzArr[$data['nourut']]['totaldetail']+=$bar['jumlah'];
		} */
		
		$str="select sum(rupiah) as rupiah, ".$sumbgt." from ".$dbname.".bgt_budget_detail where 1=1 ".$addbetweenbudget." ".$addbetweenbudgetkode."  and tahunbudget='".$periode."'  ".$where." and pta='BGT'"; 
		$res=fetchdata($str);
		foreach($res as $bar){
			@$dzArr[$data['nourut']]['budget']+=$bar['rupiah'];
			foreach($range as $b){
				$perbgt=$periode."-".addZero($b,2);
				$bgtbulan[$data['nourut']][$perbgt]+=$bar['rp'.addZero($b,2)];
			}
		}
		// if($data['nourut']=='12101000'){
			// echo $str."<br>"; exit("error");
		// }
		
		$str="select sum(hargatotal) as rupiah,".$sumbgtk." from ".$dbname.".bgt_kapital where 1=1 ".$addbetweenbudget."  and tahunbudget='".$periode."'  ".$whadj." and pta='BGT'";
		$res=fetchdata($str);
		foreach($res as $bar){
			@$dzArr[$data['nourut']]['budget']+=$bar['rupiah'];
			foreach($range as $b){
				$perbgt=$periode."-".addZero($b,2);
				$bgtbulan[$data['nourut']][$perbgt]+=$bar['rp'.addZero($b,2)];
			}
		}
	} 
}


#saldo awal
/* foreach($arrperiode as $per){
	$exper=explode('-',$per);
	$str="select sum(awal".$exper[1].") as salwal from ".$dbname.".keu_saldobulanan where 1=1 and periode='".str_replace('-','',$per)."'  ".$where." and noakun in (select noakun from ".$dbname.".keu_5akun where kasbank=1)";
	$res=fetchdata($str);
	foreach($res as $bar){
		@$dzArr['18999999'][$per]=$bar['salwal'];
	}
} */

/* # ADJUSTMEN
$str = "select * from ".$dbname.".keu_adjustmentlaporankeuangan where 1=1 ".$whadj." and jenis='".$kodelaporan."'";
$res = fetchdata($str);
foreach($res as $bar){
	@$dzArr[$bar['code']][$bar['periode']]+=$bar['jumlah'];
} */


#= buat total disini
if(!empty($dzArr))foreach($dzArr as $data){
	if($data['tipe']=='Total'){
		$arrdata=explode(',',$data['noakundisplay']);
		$dzArr[$data['nourut']]['budget']=0;
		foreach($arrdata as $key){
			foreach($arrperiode as $per){
				@$dzArr[$data['nourut']][$per]+=$dzArr[$key][$per];
				@$dzArr[$data['nourut']]['totaldetail']+=$dzArr[$key][$per];
				
				@$bgtbulanst[$data['nourut']][$per]+=$bgtbulan[$key][$per];
			}
			@$dzArr[$data['nourut']]['budget']+=$dzArr[$key]['budget']; 	
		}
	}
}

// echo"<pre>";
// print_r($bgtbulanst);

// exit("error");

#ambil format mesinlaporan==========
if(!empty($dzArr))foreach($dzArr as $data){
	if($data['tampil']=='1'){
		 switch($data['tipe']){
			case'Header':
				$stream.="<tr class=rowcontent>
					<td colspan=14><b>".$data['keterangan']."</b></td>
				</tr>"; 
			break;
			
			case'Total':
				$stream.="<tr class=rowcontent>";
					$stream.="<td nowrap><b>".$data['keterangan']."</b></td>";
					foreach($arrperiode as $per){
						$detailSubTotalBgt = (hidezerodecimal($bgtbulanst[$data['nourut']][$per],2) > 0 ? hidezerodecimal($bgtbulanst[$data['nourut']][$per],2) : hidezerodecimal(abs($bgtbulanst[$data['nourut']][$per]),2));
						$stream.="<td align=right title='Click untuk melihat detail' style=cursor:pointer; onclick=\"detailsubtotal('".$data['nourut']."','".$per."','".$pt."','".$regional."','".$unit."','html','event','budget');\">".$detailSubTotalBgt."</td>";
					}
					
					$totalDetail = (hidezerodecimal($data['totaldetail'],2) > 0 ? hidezerodecimal($data['totaldetail'],2) : hidezerodecimal(abs($data['totaldetail']),2));
					$budget = (hidezerodecimal($data['budget'],2) > 0 ? hidezerodecimal($data['budget'],2) : hidezerodecimal(abs($data['budget']),2));
					$subTotal = (hidezerodecimal($data['totaldetail']-$data['budget'],2) > 0 ? hidezerodecimal($data['totaldetail']-$data['budget'],2) : hidezerodecimal(abs($data['totaldetail']-$data['budget']),2));

					//$stream.="<td align=right>".$totalDetail."</td>";
					$stream.="<td align=right>".$budget."</td>";
					//$stream.="<td align=right>".$subTotal."</td>";

				// if(@$data['budget']>0){
					// $totalDetailBudget = (hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2) > 0 ? hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2) : hidezerodecimal(abs(($data['totaldetail']-$data['budget'])/$data['budget']*100),2));
					// $stream.="<td align=right>".$totalDetailBudget."</td>";
				// }else{
					// $stream.="<td align=right>".hidezerodecimal(0,2)."</td>";
				// }
				$stream.="</tr>";
				$stream.="<tr class=rowcontent><td colspan=14>&nbsp;</td></tr>"; 
			break;
			
			case'Detail':
				$stream.="<tr class=rowcontent>";				
					$stream.="<td >&nbsp;&nbsp;&nbsp; ".$data['keterangan']."</td>";
						foreach($arrperiode as $per){
							$dataDetailBgt = (hidezerodecimal($bgtbulan[$data['nourut']][$per],2) > 0 ? hidezerodecimal($bgtbulan[$data['nourut']][$per],2) : hidezerodecimal(abs($bgtbulan[$data['nourut']][$per]),2));
							$stream.="<td align=right title='Click untuk melihat detail'  style=cursor:pointer; onclick=\"detail('".$data['nourut']."','".$per."','".$pt."','".$regional."','".$unit."','html','event','budget');\">".$dataDetailBgt."</td>";
						}
						
						$totalDetail = (hidezerodecimal(@$data['totaldetail'],2) > 0 ? hidezerodecimal(@$data['totaldetail'],2) : hidezerodecimal(abs(@$data['totaldetail']),2));
						$budget = (hidezerodecimal(@$data['budget'],2) > 0 ? hidezerodecimal(@$data['budget'],2) : hidezerodecimal(abs(@$data['budget']),2));
						$subTotal = (hidezerodecimal(@$data['totaldetail']-$data['budget'],2) > 0 ? hidezerodecimal(@$data['totaldetail']-$data['budget'],2) : hidezerodecimal(abs(@$data['totaldetail']-$data['budget']),2));

						//$stream.="<td align=right>".$totalDetail."</td>";
						$stream.="<td align=right>".$budget."</td>";
						// $stream.="<td align=right>".$subTotal."</td>";
						// if(@$data['budget']>0){
							// $dtDetailBudget = (hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2) > 0 ? hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2) : hidezerodecimal(abs(($data['totaldetail']-$data['budget'])/$data['budget']*100),2));
							// $stream.="<td align=right>".$dtDetailBudget."</td>";
						// }else{
							// $stream.="<td align=right>".hidezerodecimal(0,2)."</td>";
						// }
				$stream.="</tr>"; 
			break;
		 }
	}
}
$stream.= "</tbody></tfoot></tfoot></table>";


// $streamdetail="";
// $streamdetail.="NOTES CASHFLOW REPORT - TREND<br>";
// $streamdetail.="YEAR ".$periode."<br>";
// $streamdetail.="(Rp.)<br>";
// if($pt==''){
	// $streamdetail.="<br>";
// } else if($regional==''){
	// $unittampil 	= $nmorg[$pt];
	// $streamdetail.="".$unittampil."<br>";
// } else if($unit==''){
	// $unittampil 	= 'Seluruh Unit Regional '.$regional.' '.$nmorg[$pt];
	// $streamdetail.="".$unittampil."<br>";
// }else{
	// $unittampil = $unit;
	// $streamdetail.="".$unittampil." - ".$nmorg[$unit]."<br>";
// }
// if($tipe=='html'){
	// $streamdetail.="<table class=sortable border=0 cellspacing=1>";
    // $streamdetail.="<thead>";
        // $streamdetail.="<tr class=rowheader>";
			// $streamdetail.="<td align=center width='300px;'><b>".$_SESSION['lang']['keterangan']."</td>";
			// foreach($arrperiode as $per){
				 // $streamdetail.="<td align=center  width='100px;'><b>".numToMonth(substr($per,5,2),'I','long')."</td>";  
			// }
			// $streamdetail.="<td align=center  width='100px;'><b>YTD<br>Actual</b></td>";
			// $streamdetail.="<td align=center><b>YTD<br>Budget</b></td>";
			// $streamdetail.="<td align=center><b>Variance</b></td>";
			// $streamdetail.="<td align=center><b>%</b></td>";
		// $streamdetail.="</tr>";
	// $streamdetail.="</thead>";
// }else{
		// $streamdetail.="<table class=sortable border=1 cellspacing=1><tbody>";
		// $streamdetail.="<tr class=rowheader>";
			// $streamdetail.="<td  bgcolor=#BDBDBD align=center width='300px;'><b>".$_SESSION['lang']['keterangan']."</td>";
			// foreach($arrperiode as $per){
				 // $streamdetail.="<td align=center  bgcolor=#BDBDBD><b>".numToMonth(substr($per,5,2),'I','long')."</td>";  
			// }
			// $streamdetail.="<td  bgcolor=#BDBDBD align=center  width='100px;'><b>YTD<br>Actual</b></td>";
			// $streamdetail.="<td  bgcolor=#BDBDBD align=center><b>YTD<br>Budget</b></td>";
			// $streamdetail.="<td  bgcolor=#BDBDBD  align=center><b>Variance</b></td>";
			// $streamdetail.="<td  bgcolor=#BDBDBD  align=center><b>%</b></td>";
        // $streamdetail.="</tr>";
// }






// #ambil format mesinlaporan==========

// if(!empty($dzArr))foreach($dzArr as $data){
	
	// if($data['detail']=='1'){
		 // switch($data['tipe']){
				
			// case'Header':
			  // $streamdetail.="<tr class=rowcontent>
					// <td colspan=17><b>".$data['keterangan']."</b></td>
				// </tr>"; 
			// break;
			
			// case'Total':
					// $streamdetail.="<tr class=rowcontent>";
						// $streamdetail.="<td><b>".$data['keterangan']."</b></td>";
						// foreach($arrperiode as $per){
							// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal($data[$per],2)."</td>";
						// }
						// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal($data['totaldetail'],2)."</td>";
						// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal($data['budget'],2)."</td>";
					// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal($data['totaldetail']-$data['budget'],2)."</td>";
					// if(@$data['budget']>0){
						// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2)."</td>";
					// }else{
						// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(0,2)."</td>";
					// }
					// $streamdetail.="</tr>";
					// $streamdetail.="<tr class=rowcontent><td colspan=17>&nbsp;</td></tr>"; 
			// break;
			
			// case'Detail':
				// $streamdetail.="<tr class=rowcontent>";				
				// $streamdetail.="<td >&nbsp;&nbsp;&nbsp; ".$data['keterangan']."</td>";
					// foreach($arrperiode as $per){
						 // $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(@$data[$per],2)."</td>";
					// }
					// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(@$data['totaldetail'],2)."</td>";
					// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(@$data['budget'],2)."</td>";
					// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(@$data['totaldetail']-$data['budget'],2)."</td>";
					// if(@$data['budget']>0){
						// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2)."</td>";
					// }else{
						// $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(0,2)."</td>";
					// }
				// $streamdetail.="</tr>"; 
			// break;
		 // }
	// }
// }

// $streamdetail.= "</tbody></tfoot></tfoot></table>";



















if($tipe=='excel'){
	if($unittampil==''){
		$unittampil=$nmorg[$kodeholding];
	}
	$nop = "Aruskas-".$unittampil."_".$periode.".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet("Trend_".$periode, $stream);
	$xls->addSheet("Notes_".$periode, $streamdetail);
	$xls->headers($nop);
	echo $xls->buildFile();
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