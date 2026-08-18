<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$pt = checkPostGet('pt', '');
$regional = checkPostGet('regional', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$periodepembanding = checkPostGet('periodepembanding', '');
$periode1 = checkPostGet('periode1', '');
$revisi = checkPostGet('revisi', '');
$gudang = checkPostGet('gudang', '');
$tipe = checkPostGet('tipe', '');

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$bulan=$qwe[1];

$qwe=explode('-',$periodepembanding);
$tahunpembanding=$qwe[0];
$bulanpembanding=$qwe[1];

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=fetchData($str);
$bar=$res[0];
$namapt=strtoupper($bar['namaorganisasi']);
#++++++++++++++++++++++++++++++++++++++++++


$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');



$kodelaporan='LABA RUGI';
$periodesaldo=str_replace("-", "", $periode);
$periodesaldopembanding=str_replace("-", "", $periodepembanding);


#lalu


$kolomPRF="awal".$bulan;
$kolompembanding="awal".$bulanpembanding;

#captionsekarang============================
$t=mktime(0,0,0,substr($periodesaldo,4,2),15,substr($periodesaldo,0,4));
$captionCUR=numToMonth(substr($periodesaldo,4,2),"I","long")."<br>".substr($periodesaldo,0,4);

$t=mktime(0,0,0,substr($periodesaldopembanding,4,2),15,substr($periodesaldopembanding,0,4));
$captionpembanding=numToMonth(substr($periodesaldopembanding,4,2),"I","long")."<br>".substr($periodesaldopembanding,0,4);


if($regional=='' && $unit==''){
    $where=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
} else if($regional!='' && $unit=='') {
    $where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
    
} else {
    $where=" and kodeorg='".$unit."'";
}


// echo $where;
// $where=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";

$totalRealisasiSd=array();
$totalRealisasiSdpembanding=array();
// $stream.="<table class=sortable border=0 cellspacing=1>";
    // $stream.="<thead>";
        // $stream.="<tr>";
        // $stream.="<td align=center colspan=2  >Keterangan</td>";
        // $stream.="<td align=center  >".$captionCUR."</td>  ";  
        // $stream.="<td align=center >YTD<br>".$tahun."</td>";
		  // $stream.="<td align=center  >".$captionpembanding."</td>  ";  
        // $stream.="<td align=center >YTD<br>".$tahunpembanding."</td>";
// $stream.="</tr>";




	
if($tipe=='html'){
	$stream.="<div style='position:fixed;'><table class=sortable border=0 cellspacing=1 width='1100px;'>";
    $stream.="<thead>";
        $stream.="<tr class=rowheader>";
        $stream.="<td align=center width='415px;'><b>KETERANGAN</td>";
        $stream.="<td align=center width='150px;'><b>".$captionCUR."</td>  ";  
        $stream.="<td align=center width='150px;'><b>YTD<br>".$tahun."</td>";
		  $stream.="<td align=center width='150px;'><b>".$captionpembanding."</td>  ";  
        $stream.="<td align=center width='150px;'><b>YTD<br>".$tahunpembanding."</td>";
        $stream.="</tr>";
   
	  $stream.="</thead><tbody></tbody>";
    $stream.="</table>";
	$stream.="</div><br>";
	$stream.="<table class=sortable border=0 cellspacing=1  width='1100px;'><thead></thead><tbody>";
	
}else{
		$stream.="<table class=sortable border=0 cellspacing=1><tbody>";
	$stream.="<tr class=rowheader>";
    
              $stream.="<td align=center><b>KETERANGAN</td>";
        $stream.="<td align=center><b>".$captionCUR."</td>  ";  
        $stream.="<td align=center><b>YTD<br>".$tahun."</td>";
		  $stream.="<td align=center><b>".$captionpembanding."</td>  ";  
        $stream.="<td align=center><b>YTD<br>".$tahunpembanding."</td>";
	$stream.="</tr>";
}

// echo $kolomPRF._.$kolompembanding;

$stream.="</thead><tbody>";
$dzArr=array();
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
$res=fetchData($str);
foreach($res as $row=>$bar){
    $dzArr[$bar['nourut']]['nourut']=$bar['nourut'];
    $dzArr[$bar['nourut']]['tampil']=$bar['variableoutput'];    
    $dzArr[$bar['nourut']]['tipe']=$bar['tipe'];
    $dzArr[$bar['nourut']]['nourutTotal']=$bar['nourut_total'];
    $dzArr[$bar['nourut']]['total']=$bar['noakundisplay'];
    $dzArr[$bar['nourut']]['sum']=$bar['rubahoperatr'];//jika 1=Totalan,2=total
    if($_SESSION['language']=='ID'){
        $dzArr[$bar['nourut']]['keterangan']=$bar['keterangandisplay'];
    }
    else{
        $dzArr[$bar['nourut']]['keterangan']=$bar['keterangandisplay1'];
    }
    $dzArr[$bar['nourut']]['noakundari']=$bar['noakundari'];
    $dzArr[$bar['nourut']]['noakunsampai']=$bar['noakunsampai'];
}
if($revisi!=0){
    $addRev=" and revisi<='".$revisi."'";
}


#========================================= bentuk daftar akun

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
			$daftarakun[$bar->nourut].=$bar->noakun;
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun;
		} else{
			$daftarakun[$bar->nourut].=$bar->noakun.',';
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun.',';
		}
	}
	$nouruttemp=$bar->nourut;
}


#======================================== tutup daftar akun


// echo"<pre>";
// print_r($dzArr);
// echo"</pre>";

// exit();
// exit("Error:A");

if(!empty($dzArr)){
    foreach($dzArr as $row=>$data){
       
		$addbetween='';
		if(($data['jumlahakun']>0 or $data['jumlahakun']!='') and $data['tipe']=='Detail'){
			$addbetween=" and noakun in (".$data['noakun'].")";
	
			#query realisasi data aja
			$whrpeng='';
			$semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
			
			
			$res12=array();
			$st12="select noakun,(".$semuakolomdb.") as jumlah, ".$kolomPRF." as awal,substr(periode,5,2) as bulan
				   from ".$dbname.".keu_saldobulanan where periode='".$periodesaldo."' ".$addbetween." ".$where." 
				   order by noakun,periode "; 	  
			// exit('warning : '.$st12);
			$res12=fetchData($st12);
			if(!empty($res12)){
				foreach($res12 as $row=>$ba12){
						if(($data['nourut']>'1100')&&($data['nourut']<='1200')){
						$dtRupiah[$data['nourut']][$ba12['bulan']]+=($ba12['jumlah'])*-1;
						$dtRupiah[$data['nourut']]['sd']+=($ba12['jumlah']+$ba12['awal'])*-1;
					}else{
						$dtRupiah[$data['nourut']][$ba12['bulan']]+=($ba12['jumlah']);
						$dtRupiah[$data['nourut']]['sd']+=($ba12['jumlah']+$ba12['awal']);    
					}
					
				}
			}
			
			
			#= periode pembanding
			$res12=array();
			$st12="select noakun,(".$semuakolomdb.") as jumlah, ".$kolompembanding." as awal,substr(periode,5,2) as bulan
				   from ".$dbname.".keu_saldobulanan where periode='".$periodesaldopembanding."' ".$addbetween." ".$where." 
				   order by noakun,periode "; 	  
				   // echo $st12;
			$res12=fetchData($st12);
			if(!empty($res12)){
				foreach($res12 as $row=>$ba12){
						if(($data['nourut']>'1100')&&($data['nourut']<='1200')){
						$dtRupiahpembanding[$data['nourut']][$ba12['bulan']]+=($ba12['jumlah'])*-1;
						$dtRupiahpembanding[$data['nourut']]['sd']+=($ba12['jumlah']+$ba12['awal'])*-1;
					}else{
						$dtRupiahpembanding[$data['nourut']][$ba12['bulan']]+=($ba12['jumlah']);
						$dtRupiahpembanding[$data['nourut']]['sd']+=($ba12['jumlah']+$ba12['awal']);    
					}
					
				}
			}
			
			
		}
    }
}



#ambil nilai akumulasi 
foreach($dzArr as $row=>$data){
	if($data['tipe']=='Total'){
		$isitotal=explode(",",$data['total']);    

		if($isitotal!=''){
			switch ($data['nourut']) {
				case '1999':#LABA (RUGI) KOTOR =(Total Penjualan-Total Harga Pokok Penjualan )
					$dtRupiah[$data['nourut']][$bulan]=$dtRupiah[$isitotal[0]][$bulan]-$dtRupiah[$isitotal[1]][$bulan];
					$totalRealisasiSd[$data['nourut']]['sd']=$totalRealisasiSd[$isitotal[0]]['sd']-$totalRealisasiSd[$isitotal[1]]['sd'];
					
					$dtRupiahpembanding[$data['nourut']][$bulanpembanding]=$dtRupiahpembanding[$isitotal[0]][$bulanpembanding]-$dtRupiahpembanding[$isitotal[1]][$bulanpembanding];
					$totalRealisasiSdpembanding[$data['nourut']]['sd']=$totalRealisasiSdpembanding[$isitotal[0]]['sd']-$totalRealisasiSdpembanding[$isitotal[1]]['sd'];

				
				break;
				
				case'3999':#LABA (RUGI) OPERASI=(LABA (RUGI) KOTOR-Total Biaya Penjualan-Total Biaya Operasi)
					$dtRupiah[$data['nourut']][$bulan]=$dtRupiah[$isitotal[0]][$bulan]-$dtRupiah[$isitotal[1]][$bulan]-$dtRupiah[$isitotal[2]][$bulan];
					$totalRealisasiSd[$data['nourut']]['sd']=$totalRealisasiSd[$isitotal[0]]['sd']-$totalRealisasiSd[$isitotal[1]]['sd']-$totalRealisasiSd[$isitotal[2]]['sd'];
					
					$dtRupiahpembanding[$data['nourut']][$bulanpembanding]=$dtRupiahpembanding[$isitotal[0]][$bulanpembanding]-$dtRupiahpembanding[$isitotal[1]][$bulanpembanding]-$dtRupiahpembanding[$isitotal[2]][$bulanpembanding];
					$totalRealisasiSdpembanding[$data['nourut']]['sd']=$totalRealisasiSdpembanding[$isitotal[0]]['sd']-$totalRealisasiSdpembanding[$isitotal[1]]['sd']-$totalRealisasiSdpembanding[$isitotal[2]]['sd'];
					
				break;
				
				case'5999':#LABA (RUGI) SEBELUM PAJAK=(LABA (RUGI) OPERASI+Total Pendapatan (Biaya) Lainnya)
					$dtRupiah[$data['nourut']][$bulan]=$dtRupiah[$isitotal[0]][$bulan]-$dtRupiah[$isitotal[1]][$bulan];
					$totalRealisasiSd[$data['nourut']]['sd']=$totalRealisasiSd[$isitotal[0]]['sd']-$totalRealisasiSd[$isitotal[1]]['sd'];
					
					$dtRupiahpembanding[$data['nourut']][$bulanpembanding]=$dtRupiahpembanding[$isitotal[0]][$bulanpembanding]-$dtRupiahpembanding[$isitotal[1]][$bulanpembanding];
					$totalRealisasiSdpembanding[$data['nourut']]['sd']=$totalRealisasiSdpembanding[$isitotal[0]]['sd']-$totalRealisasiSdpembanding[$isitotal[1]]['sd'];
				break;
				
				default;
					// unset($dtRupiah[$data['nourut']][$bulan]);
					for($awal=0;$awal<count($isitotal);$awal++){
						$dtRupiah[$data['nourut']][$bulan]+=$dtRupiah[$isitotal[$awal]][$bulan];
						$totalRealisasiSd[$data['nourut']]['sd']+=$dtRupiah[$isitotal[$awal]]['sd'];
					}
					
					for($awal=0;$awal<count($isitotal);$awal++){
						$dtRupiahpembanding[$data['nourut']][$bulanpembanding]+=$dtRupiahpembanding[$isitotal[$awal]][$bulanpembanding];
						$totalRealisasiSdpembanding[$data['nourut']]['sd']+=$dtRupiahpembanding[$isitotal[$awal]]['sd'];
					}
				break;
			}
			
		}
	}
    
}





#display data
if(!empty($dzArr)){
    foreach($dzArr as $row=>$data){
        switch($data['tipe']){
            case'Header':
				$stream.="<tr class=rowcontent><td colspan=5>&nbsp;</td></tr>";     
                $stream.="</tr>";
            $stream.="<tr class=rowcontent title='".$data['keterangan']."' ><td colspan=5><b>".$data['keterangan']." </b></td></tr>";         
            break;
            case'Total':
				$stream.="<tr class=rowcontent><td></td>
						<td colspan=4 align=center>----------------------------------------------------------------------------------------------------</td>
					</tr>";     
                $stream.="<tr class=rowcontent title='".$data['keterangan']."' >";       
                $stream.="<td><b>".$data['keterangan']."</b></td>";   
                $stream.="<td align=right><b>".number_format($dtRupiah[$data['nourut']][$bulan],2)."</td>";
                $stream.="<td align=right><b>".number_format($totalRealisasiSd[$data['nourut']]['sd'],2)."</td>"; 
				 $stream.="<td align=right><b>".number_format($dtRupiahpembanding[$data['nourut']][$bulanpembanding],2)."</td>";
                $stream.="<td align=right><b>".number_format($totalRealisasiSdpembanding[$data['nourut']]['sd'],2)."</td>"; 
				
				
            break;
            case'Detail':
			 $stream.="
						<tr class=rowcontent title='Click untuk melihat detail' 
						onclick=\"lihatDetaillaporanlr('','','".$periode."','',
						'".$pt."','".$unit."',event,'".$data['nourut']."','".$kodelaporan."','".$periodepembanding."','".$regional."');\">
                ";
				// $stream.="<tr class=rowcontent title='".$data['keterangan']."' >";
           
                          $stream.="<td width='415px;'><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$data['keterangan']."</td>"; 
                $stream.="<td align=right width='150px;'><b>".number_format($dtRupiah[$data['nourut']][$bulan],2)."</td>";
                $stream.="<td align=right width='150px;'><b>".number_format($dtRupiah[$data['nourut']]['sd'],2)."</td>";
				$stream.="<td align=right width='150px;'><b>".number_format($dtRupiahpembanding[$data['nourut']][$bulanpembanding],2)."</td>";
                $stream.="<td align=right width='150px;'><b>".number_format($dtRupiahpembanding[$data['nourut']]['sd'],2)."</td>";
                $stream.="</tr>";
				
				
				$arrnoakun=array();
				$jumlah=array();
				$jumlahperiodelalu=array();
				$jumlahdaftar=0;
				$nodetail=1;
				$addbetweendetail=0;
				$daftarakundetail='';
					
				#= ambil jumlah
				$str="select count(*) as jumlah from ".$dbname.".keu_5mesinlaporandt_akun where 
					namalaporan='".$kodelaporan."' and nourut='".$data['nourut']."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);;
				while($bar=$res->fetch()){
					$jumlahdaftar=$bar['jumlah'];	
				}
				
				#= ambil daftar noakun
				$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."'  and nourut='".$data['nourut']."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					if($nodetail<$jumlahdaftar){
						$daftarakundetail.=$bar['noakun'].',';
					}else{
						$daftarakundetail.=$bar['noakun'];
					}
					$nodetail++;
					$arrnoakun[$bar['noakun']]=$bar['noakun'];
				}
				
				$addbetweendetail='';
				if($jumlahdaftar>0){
				   $addbetweendetail=" and noakun in (".$daftarakundetail.")";
				} 
				
				
				if($jumlahdaftar>0){
					$semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';			
					$str="select noakun,(".$semuakolomdb.") as jumlah, ".$kolomPRF." as awal
								   from ".$dbname.".keu_saldobulanan where periode='".$periodesaldo."' ".$addbetweendetail." ".$where." 
								   order by noakun";
								   
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						if(($data['nourut']>'1100')&&($data['nourut']<='1200')){
							$bar['jumlah']=$bar['jumlah']*-1;
							$bar['awal']=$bar['awal']*-1;
						}
						@$jumlahbi[$bar['noakun']]+=$bar['jumlah'];
						@$jumlah[$bar['noakun']]+=$bar['jumlah']+$bar['awal'];
					}
					
					$str="select noakun,(".$semuakolomdb.") as jumlah, ".$kolompembanding." as awal
								   from ".$dbname.".keu_saldobulanan where periode='".$periodesaldopembanding."' ".$addbetweendetail." ".$where." 
								   order by noakun";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						if(($data['nourut']>'1100')&&($data['nourut']<='1200')){
							$bar['jumlah']=$bar['jumlah']*-1;
							$bar['awal']=$bar['awal']*-1;
						}
						@$jumlahbipembanding[$bar['noakun']]+=$bar['jumlah'];
						@$jumlahpembanding[$bar['noakun']]+=$bar['jumlah']+$bar['awal'];
					}	
				}
				
					foreach($arrnoakun as $noakun){
						if(@$jumlahbi[$noakun]!=0 || @$jumlah[$noakun]!=0 || @$jumlahbipembanding[$noakun]!=0|| @$jumlahpembanding[$noakun]!=0){
							@$no+=1;
							$stream.="
								<tr class=rowcontent>
								<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$nmakun[$noakun]."</td>
									<td align=right>".number_format($jumlahbi[$noakun],2)."</td>
									<td align=right>".number_format($jumlah[$noakun],2)."</td>
									<td align=right>".number_format($jumlahbipembanding[$noakun],2)."</td>
									<td align=right>".number_format($jumlahpembanding[$noakun],2)."</td>
								</tr>";
						}
				}
				
            break;
        }
    }
}

$stream.= "</tbody></table>";



if($tipe=='excel'){
	$nop_="LabaRugi-".$pt."_".$periodesaldo;
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