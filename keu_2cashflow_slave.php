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

// echo $pt._.$regional._.$unit;exit();
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
while($bar=$res->fetch())
{
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    // $dzArr[$bar->nourut]['tampil']=$bar->variableoutput;    
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
$res = fetchData($str);
foreach($res as $bar){
	$jumlahdaftar[$bar['nourut']]=$bar['jumlah'];
	$dzArr[$bar['nourut']]['jumlahakun']=$bar['jumlah'];
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
			@$daftarakun[$bar->nourut].=$bar->noakun.',';
			@$dzArr[$bar->nourut]['noakun'].=$bar->noakun.',';
		}
	}
	$nouruttemp=$bar->nourut;
}

$stream="";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' or length(kodeorganisasi)='3' ";
$res=fetchData($str);
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
				$stream.="<th align=center  width='20px;'><b>".numToMonth(substr($per,5,2),'I','long')."</th>";  
			}
			$stream.="<th align=center  width='100px;'><b>YTD<br>Actual</b></th>";
			$stream.="<th align=center><b>YTD<br>Budget</b></th>";
			$stream.="<th align=center><b>Variance</b></th>";
			$stream.="<th align=center><b>%</b></th>";
		$stream.="</tr>";
	$stream.="</thead>";
}else{
		$stream.="<table class=sortable border=1 cellspacing=1><tbody>";
		$stream.="<tr class=rowheader>";
			$stream.="<td  bgcolor=#BDBDBD align=center width='300px;'><b>".$_SESSION['lang']['keterangan']."</td>";
			foreach($arrperiode as $per){
				 $stream.="<td align=center  bgcolor=#BDBDBD><b>".numToMonth(substr($per,5,2),'I','long')."</td>";  
			}
			$stream.="<td  bgcolor=#BDBDBD align=center  width='100px;'><b>YTD<br>Actual</b></td>";
			$stream.="<td  bgcolor=#BDBDBD align=center><b>YTD<br>Budget</b></td>";
			$stream.="<td  bgcolor=#BDBDBD  align=center><b>Variance</b></td>";
			$stream.="<td  bgcolor=#BDBDBD  align=center><b>%</b></td>";
        $stream.="</tr>";
}





if(!empty($dzArr))foreach($dzArr as $data){
   
   $addbetween='';
	if((@$data['jumlahakun']>0 or @$data['jumlahakun']!='') and $data['tipe']=='Detail'){
		$addbetween=" and noaruskas in (".$data['noakun'].")";
		$addbetweenbudget=" and aruskas in (".$data['noakun'].")";
		$str="select jumlah,substr(tanggal,1,7) as periode,tipetransaksi,kurs from ".$dbname.".keu_kasbankdtht_vw where  1=1 and pembayaran=1 ".$addbetween."  and tanggal like '".$periode."%'  ".$where."";
		// $str="select sum(jumlah) as jumlah,substr(tanggal,1,7) as periode,tipetransaksi,kurs from ".$dbname.".keu_kasbankdtht_vw where  1=1 and pembayaran=1 ".$addbetween."  and tanggal like '".$periode."%'  ".$where." group by tipetransaksi,periode,kurs";
		$res = fetchData($str);
		foreach($res as $bar){
			$bar['jumlah']=$bar['jumlah']*$bar['kurs'];
			if($bar['tipetransaksi']=='K'){
				$bar['jumlah']=$bar['jumlah']*-1;
			}
			@$dzArr[$data['nourut']][$bar['periode']]+=$bar['jumlah'];
			@$dzArr[$data['nourut']]['totaldetail']+=$bar['jumlah'];
		}
		
		$str="select sum(rupiah) as rupiah from ".$dbname.".bgt_budget where 1=1 ".$addbetweenbudget."  and tahunbudget='".$periode."'  ".$where."";	
		$res=fetchData($str);
		foreach($res as $bar){
		#= sementara budget di 0kan dlu
			@$dzArr[$data['nourut']]['budget']+=$bar['rupiah'];
		}
	} 
}



#saldo awal
foreach($arrperiode as $per){
	$exper=explode('-',$per);
	$str="select sum(awal".$exper[1].") as salwal from ".$dbname.".keu_saldobulanan where 
	1=1 and periode='".str_replace('-','',$per)."'  ".$where." and noakun in (select noakun from ".$dbname.".keu_5akun where kasbank=1)";
	// echo $str.___;
	// echo $str;exit();
	$res = fetchData($str);
	foreach($res as $bar){
		@$dzArr['18999999'][$per]=$bar['salwal'];
	}
}

# ADJUSTMEN
$str = "select sum(jumlah) as jumlah,code,periode from ".$dbname.".keu_adjustmentlaporankeuangan where 1=1 ".$whadj." and jenis='".$kodelaporan."' group by jumlah,code,periode";
$res = fetchData($str);
foreach($res as $bar){
	@$dzArr[$bar['code']][$bar['periode']]+=$bar['jumlah'];
}


#= buat total disini
if(!empty($dzArr))foreach($dzArr as $data){
	if($data['tipe']=='Total'){
		#= explode data
		$arrdata=explode(',',$data['noakundisplay']);
		$dzArr[$data['nourut']]['budget']=0;
		foreach($arrdata as $key){
			foreach($arrperiode as $per){
				
				//16999999
				// if($data['nourut']=='16999999'){
					// $dzArr[$key][$per]=$dzArr[$key][$per]*-1;
				// }
				
				@$dzArr[$data['nourut']][$per]+=$dzArr[$key][$per]; 	
				@$dzArr[$data['nourut']]['totaldetail']+=$dzArr[$key][$per]; 	
			}
			@$dzArr[$data['nourut']]['budget']+=$dzArr[$key]['budget']; 	
		}
		
	}
}



// echo"<pre>";
// print_r($dzArr);
// echo"</pre>";
// exit();


// echo"<pre>";
// print_r($dzArr);
// echo"</pre>";


// echo"<pre>";
// print_r($dzArr['14699999']);
// echo"</pre>";

// echo"<pre>";
// print_r($dzArr['14999999']);
// echo"</pre>";


#ambil format mesinlaporan==========
if(!empty($dzArr))foreach($dzArr as $data){
	if($data['tampil']=='1'){
		 switch($data['tipe']){
				
			case'Header':
				$stream.="<tr class=rowcontent>
					<td colspan=17><b>".$data['keterangan']."</b></td>
				</tr>"; 
			break;
			
			case'Total':
				$stream.="<tr class=rowcontent>";
					$stream.="<td nowrap><b>".$data['keterangan']."</b></td>";
					foreach($arrperiode as $per){
						$detailSubTotal = (hidezerodecimal($data[$per],2) > 0 ? hidezerodecimal($data[$per],2) : hidezerodecimal(abs($data[$per]),2));
						$stream.="<td align=right title='Click untuk melihat detail'  style=cursor:pointer; onclick=\"detailsubtotal('".$data['nourut']."','".$per."','".$pt."','".$regional."','".$unit."','html','event');\">".$detailSubTotal."</td>";
					}
					
					$totalDetail = (hidezerodecimal($data['totaldetail'],2) > 0 ? hidezerodecimal($data['totaldetail'],2) : hidezerodecimal(abs($data['totaldetail']),2));
					$budget = (hidezerodecimal($data['budget'],2) > 0 ? hidezerodecimal($data['budget'],2) : hidezerodecimal(abs($data['budget']),2));
					$subTotal = (hidezerodecimal($data['totaldetail']-$data['budget'],2) > 0 ? hidezerodecimal($data['totaldetail']-$data['budget'],2) : hidezerodecimal(abs($data['totaldetail']-$data['budget']),2));

					$stream.="<td align=right>".$totalDetail."</td>";
					$stream.="<td align=right>".$budget."</td>";
					$stream.="<td align=right>".$subTotal."</td>";

				if(@$data['budget']>0){
					$totalDetailBudget = (hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2) > 0 ? hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2) : hidezerodecimal(abs(($data['totaldetail']-$data['budget'])/$data['budget']*100),2));
					$stream.="<td align=right>".$totalDetailBudget."</td>";
				}else{
					$stream.="<td align=right>".hidezerodecimal(0,2)."</td>";
				}
				$stream.="</tr>";
				$stream.="<tr class=rowcontent><td colspan=17>&nbsp;</td></tr>"; 
			break;
			
			case'Detail':
				$stream.="<tr class=rowcontent>";				
					$stream.="<td >&nbsp;&nbsp;&nbsp; ".$data['keterangan']."</td>";
						foreach($arrperiode as $per){
							$dataDetail = (hidezerodecimal($data[$per],2) > 0 ? hidezerodecimal($data[$per],2) : hidezerodecimal(abs($data[$per]),2));
							 $stream.="<td align=right title='Click untuk melihat detail' style=cursor:pointer; onclick=\"detail('".$data['nourut']."','".$per."','".$pt."','".$regional."','".$unit."','html','event');\">".$dataDetail."</td>";
						}
						
						$totalDetail = (hidezerodecimal(@$data['totaldetail'],2) > 0 ? hidezerodecimal(@$data['totaldetail'],2) : hidezerodecimal(abs(@$data['totaldetail']),2));
						$budget = (hidezerodecimal(@$data['budget'],2) > 0 ? hidezerodecimal(@$data['budget'],2) : hidezerodecimal(abs(@$data['budget']),2));
						$subTotal = (hidezerodecimal(@$data['totaldetail']-$data['budget'],2) > 0 ? hidezerodecimal(@$data['totaldetail']-$data['budget'],2) : hidezerodecimal(abs(@$data['totaldetail']-$data['budget']),2));

						$stream.="<td align=right>".$totalDetail."</td>";
						$stream.="<td align=right>".$budget."</td>";
						$stream.="<td align=right>".$subTotal."</td>";
						if(@$data['budget']>0){
							$dtDetailBudget = (hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2) > 0 ? hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2) : hidezerodecimal(abs(($data['totaldetail']-$data['budget'])/$data['budget']*100),2));
							$stream.="<td align=right>".$dtDetailBudget."</td>";
						}else{
							$stream.="<td align=right>".hidezerodecimal(0,2)."</td>";
						}
				$stream.="</tr>"; 
			break;
		 }
	}
}

$stream.= "</tbody></tfoot></tfoot></table>";











$streamdetail="";

$streamdetail.="NOTES CASHFLOW REPORT - TREND<br>";
$streamdetail.="YEAR ".$periode."<br>";
$streamdetail.="(Rp.)<br>";
if($pt==''){
	$streamdetail.="<br>";
} else if($regional==''){
	$unittampil 	= $nmorg[$pt];
	$streamdetail.="".$unittampil."<br>";
} else if($unit==''){
	$unittampil 	= 'Seluruh Unit Regional '.$regional.' '.$nmorg[$pt];
	$streamdetail.="".$unittampil."<br>";
}else{
	$unittampil = $unit;
	$streamdetail.="".$unittampil." - ".$nmorg[$unit]."<br>";
}
if($tipe=='html'){
	$streamdetail.="<table class=sortable border=0 cellspacing=1>";
    $streamdetail.="<thead>";
        $streamdetail.="<tr class=rowheader>";
			$streamdetail.="<td align=center width='300px;'><b>".$_SESSION['lang']['keterangan']."</td>";
			foreach($arrperiode as $per){
				 $streamdetail.="<td align=center  width='100px;'><b>".numToMonth(substr($per,5,2),'I','long')."</td>";  
			}
			$streamdetail.="<td align=center  width='100px;'><b>YTD<br>Actual</b></td>";
			$streamdetail.="<td align=center><b>YTD<br>Budget</b></td>";
			$streamdetail.="<td align=center><b>Variance</b></td>";
			$streamdetail.="<td align=center><b>%</b></td>";
		$streamdetail.="</tr>";
	$streamdetail.="</thead>";
}else{
		$streamdetail.="<table class=sortable border=1 cellspacing=1><tbody>";
		$streamdetail.="<tr class=rowheader>";
			$streamdetail.="<td  bgcolor=#BDBDBD align=center width='300px;'><b>".$_SESSION['lang']['keterangan']."</td>";
			foreach($arrperiode as $per){
				 $streamdetail.="<td align=center  bgcolor=#BDBDBD><b>".numToMonth(substr($per,5,2),'I','long')."</td>";  
			}
			$streamdetail.="<td  bgcolor=#BDBDBD align=center  width='100px;'><b>YTD<br>Actual</b></td>";
			$streamdetail.="<td  bgcolor=#BDBDBD align=center><b>YTD<br>Budget</b></td>";
			$streamdetail.="<td  bgcolor=#BDBDBD  align=center><b>Variance</b></td>";
			$streamdetail.="<td  bgcolor=#BDBDBD  align=center><b>%</b></td>";
        $streamdetail.="</tr>";
}






#ambil format mesinlaporan==========

if(!empty($dzArr))foreach($dzArr as $data){
	
	if($data['detail']=='1'){
		 switch($data['tipe']){
				
			case'Header':
			  $streamdetail.="<tr class=rowcontent>
					<td colspan=17><b>".$data['keterangan']."</b></td>
				</tr>"; 
			break;
			
			case'Total':
					$streamdetail.="<tr class=rowcontent>";
						$streamdetail.="<td><b>".$data['keterangan']."</b></td>";
						foreach($arrperiode as $per){
							$streamdetail.="<td align=right width='200px;'>".hidezerodecimal($data[$per],2)."</td>";
						}
						$streamdetail.="<td align=right width='200px;'>".hidezerodecimal($data['totaldetail'],2)."</td>";
						$streamdetail.="<td align=right width='200px;'>".hidezerodecimal($data['budget'],2)."</td>";
					$streamdetail.="<td align=right width='200px;'>".hidezerodecimal($data['totaldetail']-$data['budget'],2)."</td>";
					if(@$data['budget']>0){
						$streamdetail.="<td align=right width='200px;'>".hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2)."</td>";
					}else{
						$streamdetail.="<td align=right width='200px;'>".hidezerodecimal(0,2)."</td>";
					}
					$streamdetail.="</tr>";
					$streamdetail.="<tr class=rowcontent><td colspan=17>&nbsp;</td></tr>"; 
			break;
			
			case'Detail':
				$streamdetail.="<tr class=rowcontent>";				
				$streamdetail.="<td >&nbsp;&nbsp;&nbsp; ".$data['keterangan']."</td>";
					foreach($arrperiode as $per){
						 $streamdetail.="<td align=right width='200px;'>".hidezerodecimal(@$data[$per],2)."</td>";
					}
					$streamdetail.="<td align=right width='200px;'>".hidezerodecimal(@$data['totaldetail'],2)."</td>";
					$streamdetail.="<td align=right width='200px;'>".hidezerodecimal(@$data['budget'],2)."</td>";
					$streamdetail.="<td align=right width='200px;'>".hidezerodecimal(@$data['totaldetail']-$data['budget'],2)."</td>";
					if(@$data['budget']>0){
						$streamdetail.="<td align=right width='200px;'>".hidezerodecimal(($data['totaldetail']-$data['budget'])/$data['budget']*100,2)."</td>";
					}else{
						$streamdetail.="<td align=right width='200px;'>".hidezerodecimal(0,2)."</td>";
					}
				$streamdetail.="</tr>"; 
			break;
		 }
	}
}

$streamdetail.= "</tbody></tfoot></tfoot></table>";



















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