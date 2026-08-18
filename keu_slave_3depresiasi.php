<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$kodeorg  = $param['kodeorg'];
$dataorg = array();
$dtstr="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$kodeorg."'";
$str=$owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
	$dataorg[$bar->kodeorganisasi] = $bar;
 }
$tahunbulan = implode("",explode('-',$param['periode']));
#1. Ambil semua aktiva yang aktif
if($_SESSION['language']=='EN'){
    $zz="b.namatipe1 as namatipe";
}else{
    $zz="b.namatipe";
}
$rinci = array();
/*
$dtstr="select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,".$zz.",a.jenis_biaya,
left(a.tanggaldisposal,7) as periodenonaktif
       from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
       on a.tipeasset=b.kodetipe    
       where a.kodeorg='".$kodeorg."' 
       and status=1 and a.awalpenyusutan <= '".$param['periode']."'  and persendecline=0";
*/	   
$dtstr="select a.namasset,a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,a.hargaperolehan,".$zz.",a.jenis_biaya,
left(a.tanggaldisposal,7) as periodenonaktif
       from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
       on a.tipeasset=b.kodetipe    
       where a.kodeorg='".$kodeorg."' 
       and status=1 and a.awalpenyusutan <= '".$param['periode']."'  and persendecline=0";	   
	   
 $str=$owlPDO->query($dtstr);

 //echo $dtstr;
 $ass=array();
 $nama=array();
 $pass=array();
 $totalsudahsusut=0;
 $selisihsusut=0;
 $str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
     $x=mktime(0,0,0,  intval(substr($bar->awalpenyusutan,5,2)+($bar->jlhblnpenyusutan)),15,substr($bar->awalpenyusutan,0,4));
     $maxperiod=date('Y-m',$x);
     if($bar->periodenonaktif!="0000-00"){
     	if($param['periode']>=$bar->periodenonaktif){
	     	continue;
	     }	
     }
     
	 
	 #= ambil data selisih
	
	 $totalsudahsusut=0;
	 $selisihsusut=0;
	 if($param['periode']==periodelalu($maxperiod)){
		$totalsudahsusut=$bar->bulanan*$bar->jlhblnpenyusutan;
		$selisihsusut=$bar->hargaperolehan-$totalsudahsusut;
		// $ass[$bar->tipeasset.$bar->jenis_biaya]+=$selisihsusut;	
		// $ass[$bar->tipeasset]+=$selisihsusut;	
	 }
	 
	 
     if($param['periode']<$maxperiod){
		if(!isset($ass[$bar->tipeasset])) $ass[$bar->tipeasset]=0;
        $ass[$bar->tipeasset]+=$bar->bulanan+$selisihsusut;
        // if(substr($bar->kodeasset,4,4)=='MS01'){
        // 	$ass[substr($bar->tipeasset,0,2).]+=$bar->bulanan;
        // }
        if($bar->jenis_biaya=='1'){
        	$ass[$bar->tipeasset.$bar->jenis_biaya]+=$bar->bulanan+$selisihsusut;	
        }
		
		
		
		
		
		
		//$rinci[] = array($bar->kodeasset, $bar->bulanan);
     }
	 
	 
	 
	
	 
	 
	 
	 
	 
	 
     
     $nama[$bar->tipeasset]=$bar->namatipe;
	 
	 if(@$dataorg[$kodeorg]->tipe == 'HOLDING' || @$dataorg[$kodeorg]->tipe == 'KANWIL'  || @$dataorg[$kodeorg]->tipe == 'RND'  || @$dataorg[$kodeorg]->tipe == 'TC'){
		$pass[$bar->tipeasset]='DPH'.substr($bar->tipeasset,0,2);
	 }else if(@$dataorg[$kodeorg]->tipe == 'BULKING'){
		$pass[$bar->tipeasset]='DEB'.substr($bar->tipeasset,0,2);
	 }else{
		$pass[$bar->tipeasset]='DEP'.substr($bar->tipeasset,0,2);
	 }
 }
 


 
//Ambil double declining
  $str=$owlPDO->query("select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,a.persendecline,a.hargaperolehan,".$zz.",a.jenis_biaya 
       from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
       on a.tipeasset=b.kodetipe    
       where a.kodeorg='".$kodeorg."' 
       and status=1 and a.awalpenyusutan <= '".$param['periode']."' and a.tipeasset<>'MS'  and a.persendecline>'0'");
 $str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
	$thnawal=substr($bar->awalpenyusutan,0,4);
	$blnawal=substr($bar->awalpenyusutan,5,2);
	$total=($thnawal*12)+$blnawal;

	$thnNow=substr($param['periode'],0,4);
	$blnNow=substr($param['periode'],5,2);
	
	$totalBulanAwal = 12-$blnawal+1;
	$totalTahun = $thnNow-$thnawal-1;
	
	$totalNow=($thnNow*12)+$blnNow+1;
	$selisih=$totalNow-$total;
	$out=0;
	$akumNow = $sekarang = 0;
	
	// Depresiasi s/d akhir tahun
	$before = $sekarang = $bar->hargaperolehan;
	if($totalTahun>-1) {
		$akumNow += $totalBulanAwal/12 * $bar->persendecline/100 * $sekarang;
	}
	$sekarang -= $akumNow;
	
	// Depresiasi per Tahun
	if($totalTahun>0) {
		for($i=0;$i<$totalTahun;$i++) {
			$before = $sekarang;
			$akumNow += $sekarang*$bar->persendecline/100;
			$sekarang -= $sekarang*$bar->persendecline/100;
		}
	}
	
	// Depresiasi per Bulan
	$out = $sekarang*($bar->persendecline/100)/12;
	//if($bar->jlhblnpenyusutan==$selisihNow) {
	if($bar->jlhblnpenyusutan<$selisih) {
		$sekarang = $out = 0;
		//if($totalTahun>-1) {
		//	$out = $sekarang - ($blnNow*$sekarang);
		//} else {
		//	$out = $sekarang - (($blnNow-$bulanawal+1)*$sekarang);
		//}
		//if(intval($blnNow)>0) {
		//	$out = $sekarang - ($sisaBulan*$out);
		//} else {
		//	$out = $before/12;
		//}
	}
	
	if(isset($ass[$bar->tipeasset])) {
		$ass[$bar->tipeasset]+=$out;
	} else {
		$ass[$bar->tipeasset]=$out;
	}
	if($bar->jenis_biaya=='1'){//MS01=index array untuk ambil jenis biaya langsung
        	$ass[$bar->tipeasset.$bar->jenis_biaya]+=$out;
    }
	//$rinci[] = array($bar->kodeasset, $out);
	$nama[$bar->tipeasset]=$bar->namatipe;
	// $pass[$bar->tipeasset]='DEP'.substr($bar->tipeasset,0,2);      
	 if(@$dataorg[$kodeorg]->tipe == 'HOLDING' || @$dataorg[$kodeorg]->tipe == 'KANWIL'  || @$dataorg[$kodeorg]->tipe == 'RND'){
		$pass[$bar->tipeasset]='DPH'.substr($bar->tipeasset,0,2);
	 }else if(@$dataorg[$kodeorg]->tipe == 'BULKING'){
		$pass[$bar->tipeasset]='DEB'.substr($bar->tipeasset,0,2);
	 }else{
		$pass[$bar->tipeasset]='DEP'.substr($bar->tipeasset,0,2);
	 }
}

$tabledt="<button class=mybutton onclick=prosesPenyusutan(1) id=btnproses>Process</button><button class=mybutton onclick=exportTableToExcel()>Excel</button>
	<table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable>
	<thead>
	<tr class=rowheader>
	<th>No</th>
	<th>Asset Type</th>
	<th>Journal Code</th>
	<th>Period</th>
	<th>".$_SESSION['lang']['keterangan']."</th>
	<th>".$_SESSION['lang']['jumlah']."</th>
	</tr>
	</thead>
	<tbody>";

$no=0;
$tabdt="";
$jmlRowLang=0;//var untuk ambil banyaknya biaya langsung
foreach($ass as $key=>$val){ 
	if(substr($key,2,1)=='1'){
		$jmlRowLang+=1;
		$tabdt.="<input type=hidden id='nilaiisi_".$jmlRowLang."' value='".$ass[$key]."' /><input type=hidden id='tipeisi_".$jmlRowLang."' value='".substr($key,0,2)."' />";
		continue;	
	}
	$no+=1;
	$tabledt.="<tr class=rowcontent id='row".$no."'>
	<td>".$no."</td>
	<td id='tipeasset".$no."'>".$key."</td>
	<td id='kodejurnal".$no."'>".$pass[$key]."</td>    
	<td id='periode".$no."'>".$param['periode']."</td>
	<td id='keterangan".$no."'>".$nama[$key]."</td>
	<td align=right id='jumlah".$no."'>".number_format($ass[$key],2,'.','')."</td>
	</tr>";
}
$tabledt.="</tbody><tfoot></tfoot></table><input type=hidden id='jmlRowLang' value='".$jmlRowLang."' />
<input type=hidden id='totRowData' value='".$no."' />";
echo $tabledt.$tabdt;
?>