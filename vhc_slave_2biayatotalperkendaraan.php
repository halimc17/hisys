<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
// error_reporting(0);

$unit    = checkPostGet('unit','');
$tglAwal = tanggalsystem(checkPostGet('tglAwal',''));
$tglAkhir= tanggalsystem(checkPostGet('tglAkhir',''));
$periode = checkPostGet('periode','');
$jenis = checkPostGet('jenis','');
$periode = substr(tanggalsystemn(checkPostGet('tglAwal','')),0,7);

$jenisVhc      =  makeOption($dbname, 'vhc_5master', 'kodevhc,jenisvhc');
$nopol         =  makeOption($dbname, 'vhc_5master', 'kodevhc,nopol');
$detail        =  makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc');
$tahunperolehan=  makeOption($dbname, 'vhc_5master', 'kodevhc,tahunperolehan');





if($unit==''){
    echo"warning : Working unit required";exit();
}
if($tglAwal==''||$tglAkhir==''){
	echo"Warning: date required"; exit;
}


#and kodeorg='".substr($unit,0,4)."'
$str = "select sum(debet)-sum(kredit) as jumlah,kodevhc,kodeorg from ".$dbname.".keu_jurnaldt_vw where tanggal>='".$tglAwal."' and tanggal<='".$tglAkhir."' and noakun='4110299' and (kodevhc in (select kodevhc from ".$dbname.".vhc_5master_hist where  kodetraksi = '".$unit."' and periode='".$periode."') or kodevhc='') group by kodevhc ";   
//echo $str;  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$teralokasi[$bar['kodevhc']]=$bar['jumlah']*-1;
}



#=========================================================
#4.5 ambilnoakun biaya kendaraan
$akunkdari='';
$akunksampai='';
$strh="select distinct noakundebet,sampaidebet  from ".$dbname.".keu_5parameterjurnal where  jurnalid='LPVHC'";
$resh=$owlPDO->query($strh) or die(print " Gagal: ".PDOException::getMessage());
$resh->setFetchMode(PDO::FETCH_OBJ);
while($barh=$resh->fetch()){
	$akunkdari=$barh->noakundebet;
	$akunksampai=$barh->sampaidebet;
}
if($akunkdari=='' or $akunksampai==''){
	exit("Error: Journal parameter for LPVHC(vehicle cost) not exist");
}
  
if($jenis=='excel'){
	$tab.="<table class=sortable cellpadding=5 cellspacing=1 border=1>
	     <thead>
		    <tr>
			  <th align=center>No.</th>
			  <th align=center style='width:50px;'>".$_SESSION['lang']['jenisvch']."</th>
			  <th align=center>".$_SESSION['lang']['kodevhc']."</th>
			  <th align=center>".$_SESSION['lang']['nopol']."</th>
			  <th align=center>".$_SESSION['lang']['detail']."</th>
			  <th align=center style='width:50px;'>".$_SESSION['lang']['tahunperolehan']."</th>   
			  <th align=center style='width:100px;'>".$_SESSION['lang']['jumlah']."</th>
			  <th align=center style='width:100px;'>".$_SESSION['lang']['jmljamkerja']."</th>  
			  <th align=center style='width:100px;'>Price / Unit</th>    
			  <th align=center>".$_SESSION['lang']['alokasirp']."</th>
			  <th align=center>".$_SESSION['lang']['blmAlokasi']."<br>(Rp)</th>
			</tr>  
		 </thead>
		 <tbody id=container>
		 ";
}  
  
	
#and kodeorg='".substr($unit,0,4)."'

$str = "select sum(jumlah) as jumlah, kodevhc from ".$dbname.".keu_jurnaldt_vw where  noakun not in (4110299,4110199)  and tanggal>='".$tglAwal."' and tanggal<='".$tglAkhir."' and (noakun between '".$akunkdari."' and '".$akunksampai."') and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL) and kodevhc in (select kodevhc from ".$dbname.".vhc_5master_hist where  kodetraksi = '".$unit."' and periode='".$periode."') group by kodevhc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=owlBaris($res);
$no=0;
if($row<1){
	$tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
}else{
	#ambil jumlah jam per kendaraan
	$str1="select sum(jumlah) as jumlah,kodevhc from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where tanggal>='".$tglAwal."' and tanggal<='".$tglAkhir."' and b.kodeorg='".substr($unit,0,4)."' group by kodevhc";
	$jumlahjam=Array();
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res1->fetch()){
	   $jumlahjam[str_replace(" ","",$bar1->kodevhc)]=$bar1->jumlah;
	}

	#loop per kendaraan        
	$res->setFetchMode(PDO::FETCH_OBJ);
	$totalbiaya=$totaljam =0;
	while($bar=$res->fetch()){
		$no+=1; $total=0;
		setIt($jumlahjam[str_replace(" ","",$bar->kodevhc)],0);
		if($jumlahjam[str_replace(" ","",$bar->kodevhc)]>0){
			@$rpunit=$bar->jumlah/$jumlahjam[str_replace(" ","",$bar->kodevhc)];
		}else{
			$rpunit=0;
		}
		if(isset($jumlahjam[str_replace(" ","",$bar->kodevhc)])){
			$color='#dedede';
			$title='Normal';
			//$tmblDetail="<img onclick=\"detailAlokasi(event,'".str_replace(" ","",$bar->kodevhc)."','".$rpunit."');\" title=\"Detail Alokasi\" class=\"resicon\" src=\"images/zoom.png\">";
			$tmblDetail=" onclick=\"detailAlokasi(event,'".str_replace(" ","",$bar->kodevhc)."','".$rpunit."','".$jumlahjam[str_replace(" ","",$bar->kodevhc)]."');\" title=\"Detail Alokasi\" style='cursor:pointer;color:blue;'";
		}else{
			$color='red';
			$title='No activity record';
			$tmblDetail="";
		}
		$ondiKlik=" style='cursor:pointer;color:blue;' title='Click' onclick=\"viewDetail(event,'".str_replace(" ","",$bar->kodevhc)."','".$tglAwal."','". $tglAkhir."','".substr($unit,0,4)."','".$periode."','".$akunkdari."','".$akunksampai."');\"";
		
		$clr="";
		if($bar->jumlah!=0 and $bar->kodevhc==''){
			$clr="style=color:red;";
			$detail[$bar->kodevhc]="Ada jurnal untuk akun transit (4xx) tetapi tidak dilengkapi dengan kode kendaraan.";
		}
		$tab.="<tr class=rowcontent ".$clr.">
			  <td align=center  >".$no."</td>
			  <td align=center >".str_replace(" ","",$jenisVhc[$bar->kodevhc])."</td>    
			  <td >".str_replace(" ","",$bar->kodevhc)."</td>
			  <td  align=left >".$nopol[$bar->kodevhc]."</td>  
			  <td  align=left >".$detail[$bar->kodevhc]."</td>  
			  <td  align=center>".str_replace(" ","",$tahunperolehan[$bar->kodevhc])."</td>  
			  <td ".$ondiKlik." align=right>".hidezerodecimal($bar->jumlah,2)."</td>
			  <td ".$tmblDetail." align=right bgcolor=".$color." title='".$title."'>".hidezerodecimal($jumlahjam[str_replace(" ","",$bar->kodevhc)],2)."</td> 
			  <td ".$ondiKlik." align=right>".hidezerodecimal(floor($rpunit))."</td> 
			  <td align=right ".$tmblDetail.">".hidezerodecimal($teralokasi[$bar->kodevhc],2)."</td>
			  <td align=right ".$tmblDetail.">".hidezerodecimal($bar->jumlah-$teralokasi[$bar->kodevhc])."</td>
			</tr>";
			$totalbiaya+=$bar->jumlah;
			$totaljam+=$jumlahjam[str_replace(" ","",$bar->kodevhc)];
			$alk+=$teralokasi[$bar->kodevhc];
	}
				
	$tab.="<thead><tr class=rowcontent>";
	$tab.="<td colspan=6 align=center>".$_SESSION['lang']['total']."</td>";
	$tab.="<td align=right>".number_format($totalbiaya,2)."</td>";
	$tab.="<td align=right>".number_format($totaljam,2)."</td>";
	$tab.="<td align=right>".@number_format($totalbiaya/$totaljam,2)."</td>";
	// $tab.="<td align=right></td>";
	$tab.="<td align=right>".hidezerodecimal($alk,2)."</td>";
	$tab.="<td align=right>".number_format($totalbiaya-$alk)."</td>";
	$tab.="</tr></thead>";
}

if($jenis=='excel'){
	$tab.="</tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>";
	$stream = $tab;
	$nop_ = "ttlbiayaperkend" . date('Ymd_His');
	if (strlen($stream) > 0) {
		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					@unlink('tempExcel/' . $file);
				}
			}
			closedir($handle);
		}
		$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
		if (!fwrite($handle, $stream)) {
			echo "<script language=javascript1.2>
						parent.window.alert('Cant convert to excel format');
						</script>";
			exit;
		} else {
			echo "<script language=javascript1.2>
						window.location='tempExcel/" . $nop_ . ".xls';
						</script>";
		}
		closedir($handle);
	}   
	   
}else{
	echo $tab;
}

?>