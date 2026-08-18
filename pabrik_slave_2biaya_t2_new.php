<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$kdorg=checkPostGet('kdorgt','');
$tgl=tanggalsystemn(checkPostGet('tglt',''));
$per=substr($tgl,0,7);
// $per=checkPostGet('pert','');
$proses=checkPostGet('proses','');
$expblnbgt=  explode('-', $per);
$blnbgt=$expblnbgt[1];
$kodelaporan='ANALISA BIAYA PABRIK';
// echo"<pre>";

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1 width=100%>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}


@$lastday = date('t', strtotime($periode));	
$tglawal=substr($per,0,4).'-01-01';
// $tglakhir=$per.'-'.$lastday;

if($kdorg==''){
    $kdorgx ="kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING'))";
} elseif ($kdorg==''){
    $kdorgx="kodeorganisasi ='".$kdorg."'";
}else {
    $kdorgx="induk ='".$kdorg."'";
}

if($kdorg==''){
    $kdorgy ="kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING'))";
    $kdorgyy ="millcode in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING'))";
} else {
    $kdorgy="kodeorg ='".$kdorg."'";
    $kdorgyy="millcode ='".$kdorg."'";
} 

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmurut=makeOption($dbname,'keu_5mesinlaporandt','nourut,keterangandisplay');

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where  
(".$kdorgx." or ".$kdorgx.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//$station[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	$nmdivisi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}
// @$nmdivisi[''].=$_SESSION['lang']['lain'];


##ambil produksi
// $str=" select * from ".$dbname.".pabrik_produksi where tanggal like '".substr($per,0,4)."%' and kodeorg='".$kdorg."' ";
$str=" select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglawal."' and '".$tgl."' and ".$kdorgy." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//hi
	if($bar['tanggal']==$tgl){
		$tbshi=$bar['tbsdiolah'];
		$cpohi=$bar['oer'];
		$kerhi=$bar['oerpk'];
		$palmhi=$bar['oer']+$bar['oerpk'];
	}
	
	//bi
	if(substr($bar['tanggal'],0,7)==$per){
		@$tbsbi+=$bar['tbsdiolah'];
		@$cpobi+=$bar['oer'];
		@$kerbi+=$bar['oerpk'];
		@$palmbi+=$bar['oer']+$bar['oerpk'];
	}
	//ti
	@$tbsti+=$bar['tbsdiolah'];
	@$cpoti+=$bar['oer'];
	@$kerti+=$bar['oerpk'];
	@$palmti+=$bar['oer']+$bar['oerpk'];
}


$addstrtbs="(";
$addstrcpo="(";
$addstrker="(";
$addstrbgt="(";
for($i=1;$i<=intval($blnbgt);$i++){
    if($i<10){
        $isitbs="olah0".$i;
		$isicpo="kgcpo0".$i;
		$isiker="kgker0".$i;
		$isibgt="rp0".$i;
		
    }
    else{
        $isitbs="olah".$i;
		$isicpo="kgcpo".$i;
		$isiker="kgker".$i;
		$isibgt="rp".$i;
    }
    if($i<intval($blnbgt)){
        $addstrtbs.=$isitbs."+";
		$addstrcpo.=$isicpo."+";
		$addstrker.=$isiker."+";
		$addstrbgt.=$isibgt."+";
    }
    else{
        $addstrtbs.=$isitbs;
		$addstrcpo.=$isicpo;
		$addstrker.=$isiker;
		$addstrbgt.=$isibgt;
    }
}
$addstrtbs.=")";
$addstrcpo.=")";
$addstrker.=")";
$addstrbgt.=")";

##bgt produksi
$str=" select olah".$blnbgt." as tbsbi,".$addstrtbs." as tbssdbi,kgolah as tbsthn,
			kgcpo".$blnbgt." as cpobi,".$addstrcpo." as cposdbi,kgcpo as cpothn,
			kgker".$blnbgt." as kerbi,".$addstrker." as kersdbi,kgkernel as kerthn
		from ".$dbname.".bgt_produksi_pks_vw where tahunbudget = '".substr($per,0,4)."' and ".$kdorgyy." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$bgttbsbi+=$bar['tbsbi'];
	@$bgttbssdbi+=$bar['tbssdbi'];
	@$bgttbsthn+=$bar['tbsthn'];
		@$bgtcpobi+=$bar['cpobi'];
		@$bgtcposdbi+=$bar['cposdbi'];
		@$bgtcpothn+=$bar['cpothn'];
	@$bgtkerbi+=$bar['kerbi'];
	@$bgtkersdbi+=$bar['kersdbi'];
	@$bgtkerthn+=$bar['kerthn'];
		@$bgtpalmbi+=$bar['kerbi']+$bar['cpobi'];
		@$bgtpalmsdbi+=$bar['kersdbi']+$bar['cposdbi'];
		@$bgtpalmthn+=$bar['kerthn']+$bar['cpothn'];	
}
								

$stream.="<thead>
    <tr class=rowheader>
       <td align=center rowspan=2></td>
       <td align=center rowspan=2 colspan=3></td>
       <td align=center rowspan=2 colspan=3>TODAY</td>
       <td align=center colspan=6>MONTH TODATE</td>
	   <td align=center colspan=6>YEAR TODATE</td>
	   <td align=center colspan=3 rowspan=2>".$_SESSION['lang']['budget']." ".$_SESSION['lang']['tahun']."</td>
	 </tr>
	 <tr>
		<td align=center colspan=3>".$_SESSION['lang']['realisasi']."</td>
		<td align=center colspan=3>".$_SESSION['lang']['budget']."</td>
		<td align=center colspan=3>".$_SESSION['lang']['realisasi']."</td>
		<td align=center colspan=3>".$_SESSION['lang']['budget']."</td>
	 </tr>";
$stream.="</thead>";

$stream.="
	<tr class=rowcontent>
		<td align=center rowspan=6></td>
		<td align=left rowspan=4 valign=top>".$_SESSION['lang']['produksi']."</td>
		<td align=left>".$_SESSION['lang']['tbs']."</td>
		<td align=left>(".$_SESSION['lang']['kg'].")</td>  
		<td align=right colspan=3>".@number_format($tbshi)."</td> 
		<td align=right colspan=3>".@number_format($tbsbi)."</td> 
		<td align=right colspan=3>".@number_format($bgttbsbi)."</td> 
		<td align=right colspan=3>".@number_format($tbsti)."</td> 
		<td align=right colspan=3>".@number_format($bgttbssdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgttbsthn)."</td> 
    </tr>
	 <tr class=rowcontent>
		<td align=left>".$_SESSION['lang']['cpo']."</td>
		<td align=left>(".$_SESSION['lang']['kg'].")</td>  
		<td align=right colspan=3>".@number_format($cpohi)."</td> 
		<td align=right colspan=3>".@number_format($cpobi)."</td> 
		<td align=right colspan=3>".@number_format($bgtcpobi)."</td> 
		<td align=right colspan=3>".@number_format($cpoti)."</td> 
		<td align=right colspan=3>".@number_format($bgtcposdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtcpothn)."</td> 
    </tr>
	 <tr class=rowcontent>
		<td align=left>".$_SESSION['lang']['kernel']."</td>
		<td align=left>(".$_SESSION['lang']['kg'].")</td> 
		<td align=right colspan=3>".@number_format($kerhi)."</td> 
		<td align=right colspan=3>".@number_format($kerbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtkerbi)."</td> 
		<td align=right colspan=3>".@number_format($kerti)."</td> 
		<td align=right colspan=3>".@number_format($bgtkersdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtkerthn)."</td> 
    </tr>
	
	<tr class=rowcontent>
		<td align=left>".$_SESSION['lang']['palm']."</td>
		<td align=left>(".$_SESSION['lang']['kg'].")</td> 
		<td align=right colspan=3>".@number_format($palmhi)."</td> 
		<td align=right colspan=3>".@number_format($palmbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtpalmbi)."</td> 
		<td align=right colspan=3>".@number_format($palmti)."</td> 
		<td align=right colspan=3>".@number_format($bgtpalmsdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtpalmthn)."</td> 				
    </tr>
	<tr class=rowcontent>
		<td align=center rowspan=2 colspan=3></td>
				<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
    </tr>
	<tr class=rowcontent>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
    </tr>
	";


##ambil sumber akun
$str="select * from ".$dbname.".keu_5mesinlaporandt where 
		namalaporan='".$kodelaporan."' order by nourut asc";//  and tipe='detail'
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	// if($bar['tipe']=='Header'){
		// $arrht[$bar['nourut']]=$bar['nourut'];
	// }
	if($bar['tipe']=='Detail'){
		$arrdt[$bar['nourut']]=$bar['nourut'];
		// $akun1[$bar['nourut']]=$bar['noakundari'];
		// $akun2[$bar['nourut']]=$bar['noakunsampai'];
		// $akunin[$bar['nourut']]=$bar['noakundisplay'];
		// $akundigitex[$bar['nourut']]=$bar['exceptiondigit'];
		// $akunex[$bar['nourut']]=$bar['exception'];
		// $tipedt[$bar['nourut']]=$bar['rubahoperatr'];
	}
	$nmurut[$bar['nourut']]=$bar['keterangandisplay'];
}





$akunin=array();
$jumlahakun=array();
$nouruttemp='';
#= ambil jumlah
$str="select count(*) as jumlah,nourut from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' group by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$jumlahakun[$bar->nourut]=$bar->jumlah;
}



#= ambil daftar noakun
$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	// if(@$jumlahakun[$nourut]>0){
		if($nouruttemp==$bar->nourut){
			$no++;	
		}else{
			$no=1;
		}
		
		if($nouruttemp==$bar->nourut){
			if($no<$jumlahakun[$bar->nourut]){
				@$akunin[$bar->nourut].=$bar->noakun.',';
			}else{
				@$akunin[$bar->nourut].=$bar->noakun;
			}
		}else{
			if($jumlahakun[$bar->nourut]==1){ #= hanya 1 akun saja
				@$akunin[$bar->nourut].=$bar->noakun;
			} else{
				@$akunin[$bar->nourut].=$bar->noakun.',';
			}
		}
		$nouruttemp=$bar->nourut;
	// }
}



// echo"<pre>";
// print_r($jumlahakun);
// echo"</pre>";
// exit("Error:asda");



// $where='';
foreach($arrdt as $nourut){

	$where='';
	if(@$jumlahakun[$nourut]>0){
		$where=" and noakun in (".$akunin[$nourut].") ";	
	
	echo $str="select jumlah,substr(kodeblok,1,6) as station,tanggal,noakun from ".$dbname.".keu_jurnaldt_vw where 
			kodeorg='".$kdorgyy."' and tanggal between '".$tglawal."' and '".$tgl."' 
			and nojurnal not like '%PRSDN%' ".$where."";
			// if($nourut=='15'){
				
			// exit("Error:".$str._.$nourut);
			// }
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		if($bar['station']==''){
			@$bar['station']=$bar['kodeorg'];
		}
		@$dtafd[$bar['station']]=$bar['station'];
		@$dtakun[$bar['noakun']]=$bar['noakun'];
		@$listnourut[$bar['station']][$nourut]=$bar['station'];
		@$listakun[$bar['station']][$nourut][$bar['noakun']]=$bar['station'];
		if($bar['tanggal']==$tgl){
			@$realhi[$bar['station']][$nourut][$bar['noakun']]+=$bar['jumlah'];
			@$sturutrealhi[$bar['station']][$nourut]+=$bar['jumlah'];
			@$stafdrealhi[$bar['station']]+=$bar['jumlah'];
			@$gtrealhi+=$bar['jumlah'];
		}
		if(substr($bar['tanggal'],0,7)==$per){
			@$realbi[$bar['station']][$nourut][$bar['noakun']]+=$bar['jumlah'];
			@$sturutrealbi[$bar['station']][$nourut]+=$bar['jumlah'];
			@$stafdrealbi[$bar['station']]+=$bar['jumlah'];
			@$gtrealbi+=$bar['jumlah'];
		}
		@$realti[$bar['station']][$nourut][$bar['noakun']]+=$bar['jumlah'];
		@$strealti[$bar['station']]+=$bar['jumlah'];
		@$gtrealti+=$bar['jumlah'];
	}
	
	
	
	$str="select noakun,substr(kodeorg,1,6) as station,rp".$blnbgt." as bi,".$addstrbgt." as sdbi,rupiah as thn
				from ".$dbname.".bgt_budget_detail where tahunbudget = '".substr($per,0,4)."' and kodeorg like '".$kdorg."%' 
				".$where."  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		
			@$dtafd[$bar['station']]=$bar['station'];
			@$dtakun[$bar['noakun']]=$bar['noakun'];
			@$listnourut[$bar['station']][$nourut]=$bar['station'];
			@$listakun[$bar['station']][$nourut][$bar['noakun']]=$bar['station'];
		
			@$bgtbi[$bar['station']][$nourut][$bar['noakun']]+=$bar['bi'];
			@$bgtsdbi[$bar['station']][$nourut][$bar['noakun']]+=$bar['sdbi'];
			@$bgtthn[$bar['station']][$nourut][$bar['noakun']]+=$bar['thn'];
			
			@$stafdbgtbi[$bar['station']]+=$bar['bi'];
			@$stafdbgtsdbi[$bar['station']]+=$bar['sdbi'];
			@$stafdbgtthn[$bar['station']]+=$bar['thn'];
	}
	}
	
	
}

array_multisort($dtafd,SORT_ASC);	

foreach($dtafd as $afd){
	$stream.="<tr class=rowcontent>";
		$stream.="<td><b>".$afd."</td>";
		$stream.="<td colspan=3><b>".@$nmdivisi[$afd]."</td>";
		
		$stream.="<td align=right><b>".@number_format($stafdrealhi[$afd],2)."</td>";
		$stream.="<td align=right><b>".@number_format($stafdrealhi[$afd]/$palmhi,2)."</td>";
		$stream.="<td align=right><b>".@number_format($stafdrealhi[$afd]/$tbshi,2)."</td>";
		
		$stream.="<td align=right><b>".@number_format($stafdrealbi[$afd],2)."</td>";
		$stream.="<td align=right><b>".@number_format($stafdrealbi[$afd]/$palmbi,2)."</td>";
		$stream.="<td align=right><b>".@number_format($stafdrealbi[$afd]/$tbsbi,2)."</td>";
		
	
		$stream.="<td align=right><b>".@number_format($stafdbgtbi[$afd],2)."</td>";
		$stream.="<td align=right><b>".@number_format(@$stafdbgtbi[$afd]/$bgtpalmbi,2)."</td>";
		$stream.="<td align=right><b>".@number_format(@$stafdbgtbi[$afd]/$bgttbsbi,2)."</td>";
		
		
		
		$stream.="<td align=right><b>".@number_format($strealti[$afd],2)."</td>";
		$stream.="<td align=right><b>".@number_format($strealti[$afd]/$palmti,2)."</td>";
		$stream.="<td align=right><b>".@number_format($strealti[$afd]/$tbsti,2)."</td>";
		
			$stream.="<td align=right><b>".@number_format($stafdbgtsdbi[$afd],2)."</td>";
			$stream.="<td align=right><b>".@number_format($stafdbgtsdbi[$afd]/$bgtpalmsdbi,2)."</td>";
			$stream.="<td align=right><b>".@number_format($stafdbgtsdbi[$afd]/$bgttbssdbi,2)."</td>";
			
			$stream.="<td align=right><b>".@number_format($stafdbgtthn[$afd],2)."</td>";
			$stream.="<td align=right><b>".@number_format($stafdbgtthn[$afd]/$bgtpalmthn,2)."</td>";
			$stream.="<td align=right><b>".@number_format($stafdbgtthn[$afd]/$bgttbsthn,2)."</td>";
					
		
		// $stream.="<td colspan=18></td>";
	$stream.="</tr>";	
	foreach($arrdt as $nourut){
		if(@$listnourut[$afd][$nourut]!=''){
				$stream.="<tr class=rowcontent>";
					// $stream.="<td>".$nourut."</td>";
					$stream.="<td></td>";
					$stream.="<td colspan=3><b>".$nmurut[$nourut]."</td>";
					
					$stream.="<td colspan=18></td>";
				$stream.="</tr>";	
			foreach($dtakun as $akun){
				if(@$listakun[$afd][$nourut][$akun]!=''){
					$stream.="<tr class=rowcontent>";
						$stream.="<td></td>";
						$stream.="<td>".$akun."</td>";
						$stream.="<td colspan=2>".@$nmakun[$akun]." </td>";
						$stream.="<td align=right>".@number_format($realhi[$afd][$nourut][$akun],2)."</td>";
						$stream.="<td align=right>".@number_format($realhi[$afd][$nourut][$akun]/$palmhi,2)."</td>";
						$stream.="<td align=right>".@number_format($realhi[$afd][$nourut][$akun]/$tbshi,2)."</td>";
						
						$stream.="<td align=right>".@number_format($realbi[$afd][$nourut][$akun],2)."</td>";
						$stream.="<td align=right>".@number_format($realbi[$afd][$nourut][$akun]/$palmbi,2)."</td>";
						$stream.="<td align=right>".@number_format($realbi[$afd][$nourut][$akun]/$tbsbi,2)."</td>";
						
							$stream.="<td align=right>".@number_format($bgtbi[$afd][$nourut][$akun],2)."</td>";
							$stream.="<td align=right>".@number_format($bgtbi[$afd][$nourut][$akun]/$bgtpalmbi,2)."</td>";
							$stream.="<td align=right>".@number_format($bgtbi[$afd][$nourut][$akun]/$bgttbsbi,2)."</td>";
							
						$stream.="<td align=right>".@number_format($realti[$afd][$nourut][$akun],2)."</td>";
						$stream.="<td align=right>".@number_format($realti[$afd][$nourut][$akun]/$palmti,2)."</td>";
						$stream.="<td align=right>".@number_format($realti[$afd][$nourut][$akun]/$tbsti,2)."</td>";
						
							$stream.="<td align=right>".@number_format($bgtsdbi[$afd][$nourut][$akun],2)."</td>";
							$stream.="<td align=right>".@number_format($bgtsdbi[$afd][$nourut][$akun]/$bgtpalmsdbi,2)."</td>";
							$stream.="<td align=right>".@number_format($bgtsdbi[$afd][$nourut][$akun]/$bgttbssdbi,2)."</td>";	
							
							$stream.="<td align=right>".@number_format($bgtthn[$afd][$nourut][$akun],2)."</td>";
							$stream.="<td align=right>".@number_format($bgtthn[$afd][$nourut][$akun]/$bgtpalmthn,2)."</td>";
							$stream.="<td align=right>".@number_format($bgtthn[$afd][$nourut][$akun]/$bgttbsthn,2)."</td>";	
						
					$stream.="</tr>";	
				}
			}
		}		
	}
}

#= Gran Total

$stream.="<tr class=rowcontent>";
	
	$stream.="<td colspan=4><b>Grand Total</td>";
	$stream.="<td align=right><b>".@number_format($gtrealhi,2)."</td>";
	$stream.="<td align=right><b>".@number_format($gtrealhi/$palmhi,2)."</td>";
	$stream.="<td align=right><b>".@number_format($gtrealhi/$tbshi,2)."</td>";
	
	$stream.="<td align=right><b>".@number_format($gtrealbi,2)."</td>";
	$stream.="<td align=right><b>".@number_format($gtrealbi/$palmbi,2)."</td>";
	$stream.="<td align=right><b>".@number_format($gtrealbi/$tbsbi,2)."</td>";
	
		$stream.="<td align=right><b>".@number_format($gtbgtbi,2)."</td>";
		$stream.="<td align=right><b>".@number_format($gtbgtbi/$bgtpalmbi,2)."</td>";
		$stream.="<td align=right><b>".@number_format($gtbgtbi/$bgttbsbi,2)."</td>";
		
	$stream.="<td align=right><b>".@number_format($gtrealti,2)."</td>";
	$stream.="<td align=right><b>".@number_format($gtrealti/$palmti,2)."</td>";
	$stream.="<td align=right><b>".@number_format($gtrealti/$tbsti,2)."</td>";
	
		$stream.="<td align=right><b>".@number_format($gtbgtsdbi,2)."</td>";
		$stream.="<td align=right><b>".@number_format($gtbgtsdbi/$bgtpalmsdbi,2)."</td>";
		$stream.="<td align=right><b>".@number_format($gtbgtsdbi/$bgttbssdbi,2)."</td>";	
		
		$stream.="<td align=right><b>".@number_format($gtbgtthn,2)."</td>";
		$stream.="<td align=right><b>".@number_format($gtbgtthn/$bgtpalmthn,2)."</td>";
		$stream.="<td align=right><b>".@number_format($gtbgtthn/$bgttbsthn,2)."</td>";	
	
$stream.="</tr>";	





$stream.="</table>";

switch($proses){
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="laporan_biaya_pabrik ".$kdorg."_".$per1."_sd_".$per2;
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }     
        break;	
}
?>