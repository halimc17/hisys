<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$param=$_POST;
if(count($param)==0){
	$param=$_GET;
}
if($_GET['proses']!=''){
	$param['proses']=$_GET['proses'];
}else{
	$param['proses']=$_POST['proses'];
}

$whr='';

	if($param['proses']!='getKodeAfd'){
		#cek2 variable kiriman
		if($param['kbnId']!=''){
			
		}
		if($param['afdId']!=''){
			$whr.=" and nospb like '%".$param['afdId']."%'";
		}
		if($param['kodecustomer']!=''){
			$whr.=" and kodecustomer like '%".$param['kodecustomer']."%'";
		}
		if(($param['tgl1']=='')||($param['tgl2']=='')){
			exit("warning: Tanggal tidak boleh kosong");
		}
		if(tanggalsystem($param['tgl1'])>tanggalsystem($param['tgl2'])){
			exit("warning: Tanggal yang dipilih salah");
		}
		$tgl1=explode("-",$param['tgl1']);
		$tanggal1=$tgl1[2]."-".$tgl1[1]."-".$tgl1[0];
		$tgl2=explode("-",$param['tgl2']);
		$tanggal2=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];
		$bgclr="class=rowheader";
	    $grs=0;
		if($param['proses']=='excel'){
			$bgclr="bgcolor=#dedede align=center";
			$grs=1;
		}
		#jika timbang menggunakan 2 data
		$strap = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='ES' and kodeparameter='ESEXT'";
		@$resap = fetchData($strap);
		$nilaix=0;
		@$lokasi = explode(',', $resap[0]['nilai']);
		foreach (@$lokasi as $key => $val) {
			if($val==$param['kbnId']){
				$nilaix=1;
			}
		}
		
		#persiapan di tampilkan
		$stream="
		<style>
		.sticky-header {
			position: -webkit-sticky; 
			position: sticky; 
			top: 0; 
			background-color: white;
			z-index: 1000; 
		}
		
		.table-container {
			overflow: auto;
		}  
		</style>

		<table cellpadding=1 cellspacing=1 border=".$grs." class=sortable>";
		$stream.="<thead class='sticky-header'><tr ".$bgclr.">";
		$stream.="<th align=center rowspan=2 >No.</th>";
		$stream.="<th align=center rowspan=2 >".$_SESSION['lang']['tanggal']."</th>";
		$stream.="<th align=center rowspan=2 >".$_SESSION['lang']['noTiket']."</th>";
		$stream.="<th align=center rowspan=2 >".$_SESSION['lang']['nospb']."</th>";
		$stream.="<th align=center rowspan=2 >".$_SESSION['lang']['nospb']." Pabrik</th>";
		$stream.="<th align=center rowspan=2 >".$_SESSION['lang']['tahuntanam']."</th>";
		$stream.="<th align=center rowspan=2 >".$_SESSION['lang']['customer']."</th>";
		$stream.="<th align=center rowspan=2 >PKS Tujuan</th>";
		$stream.="<th align=center rowspan=2 >".$_SESSION['lang']['nopol']."</th>";
		$stream.="<th align=center rowspan=2 >".$_SESSION['lang']['supir']."</th>";
		if($nilaix==1){ #ASRE
			$stream.="<th align=center colspan=4 >".$_SESSION['lang']['timbangan']." ".$_SESSION['lang']['kebun']."</th>";			
			$stream.="<th align=center colspan=7 >".$_SESSION['lang']['timbangan']." ".$_SESSION['lang']['pabrik']." ".$_SESSION['lang']['external']."</th>";
			$stream.="<th align=center colspan=2 >".$_SESSION['lang']['varian']."</th>";

			$stream.="</tr>";
			$stream.="<tr ".$bgclr.">";
			$stream.="<th align=center >".$_SESSION['lang']['beratMasuk']."</th>";
			$stream.="<th align=center >".$_SESSION['lang']['beratKeluar']."</th>";
			$stream.="<th align=center >".$_SESSION['lang']['beratBersih']."</th>";
			$stream.="<th align=center >".$_SESSION['lang']['jjg']."</th>";

			
			$stream.="<th align=center >".$_SESSION['lang']['tanggal']."</th>";
			$stream.="<th align=center >".$_SESSION['lang']['beratMasuk']."</th>";
			$stream.="<th align=center >".$_SESSION['lang']['beratKeluar']."</th>";
			$stream.="<th align=center >".$_SESSION['lang']['beratBersih']."</th>";
	
			// $stream.="<th align=center >".$_SESSION['lang']['jjg']." Sortasi</th>";
			$stream.="<th align=center >".$_SESSION['lang']['potongan']."</th>";
			$stream.="<th align=center >".$_SESSION['lang']['beratnormal']."</th>";
	
			
			$stream.="<th align=center >".$_SESSION['lang']['kg']."</th>";
			$stream.="<th align=center >%</th>";			
			
			$stream.="</tr></thead><tbody>";
		}else{
			$stream.="<td align=center colspan=6 >".$_SESSION['lang']['pabrik']."</td>";
			$stream.="</tr>";
			$stream.="<tr ".$bgclr.">";
			$stream.="<td align=center >".$_SESSION['lang']['beratMasuk']."</td>";
			$stream.="<td align=center >".$_SESSION['lang']['beratKeluar']."</td>";
			$stream.="<td align=center >".$_SESSION['lang']['beratBersih']."</td>";
			$stream.="<td align=center >".$_SESSION['lang']['jjg']."</td>";
			// $stream.="<td align=center >".$_SESSION['lang']['jjg']." Sortasi</td>";
			$stream.="<td align=center >".$_SESSION['lang']['potongan']."</td>";
			$stream.="<td align=center >".$_SESSION['lang']['beratnormal']."</td>";
			$stream.="</tr></thead><tbody>";
		}
		

		$sData="select tahuntanam,spbpabrik,pabriktujuan,notransaksi,left(tanggal,10) as tanggal,nospb,supir,jumlahtandan1,beratmasuk,beratkeluar,
				nokendaraan,beratbersih,jjgsortasi,kgpotsortasi,kodecustomer,nokontrak,
				tanggalpks,beratmasukpmks,beratkeluarpmks,beratbersihpmks
				from ".$dbname.".pabrik_timbangan where millcode='EXTM' and kodeorg='".$param['kbnId']."' 
				and left(tanggal,10) between '".$tanggal1."' and '".$tanggal2."' and kodebarang='40000003' ".$whr." order by tanggal,nospb";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$qData->fetch()){
			$dtSpb[$bar['nospb']]=$bar['nospb'];
			$arrAfd=explode("/",$bar['nospb']);
			$afdId[$arrAfd[1]]=$arrAfd[1];
			$arrtiket[$bar['notransaksi']]=$bar['notransaksi'];
			$tgl[$arrAfd[1]][$bar['notransaksi']]=$bar['tanggal'];
			$notiket[$arrAfd[1]][$bar['notransaksi']]=$bar['notransaksi'];
			$spb[$arrAfd[1]][$bar['notransaksi']]=$bar['nospb'];
			$kodecustomer[$arrAfd[1]][$bar['notransaksi']]=$bar['kodecustomer'];
			$pabriktujuan[$arrAfd[1]][$bar['notransaksi']]=$bar['pabriktujuan'];
			$tahuntanam[$arrAfd[1]][$bar['notransaksi']]=$bar['tahuntanam'];
			$spbpabrik[$arrAfd[1]][$bar['notransaksi']]=$bar['spbpabrik'];
			$nokontrak[$arrAfd[1]][$bar['notransaksi']]=$bar['nokontrak'];
			$nopol[$arrAfd[1]][$bar['notransaksi']]=$bar['nokendaraan'];
			$supir[$arrAfd[1]][$bar['notransaksi']]=$bar['supir'];

			$kgin[$arrAfd[1]][$bar['notransaksi']]=$bar['beratmasuk'];
			$kgout[$arrAfd[1]][$bar['notransaksi']]=$bar['beratkeluar'];
			$kgnetto[$arrAfd[1]][$bar['notransaksi']]=$bar['beratbersih'];
			$jjg[$arrAfd[1]][$bar['notransaksi']]=$bar['jumlahtandan1'];
			$jjgsortasi[$arrAfd[1]][$bar['notransaksi']]=$bar['jjgsortasi'];
			$kgsortasi[$arrAfd[1]][$bar['notransaksi']]=$bar['kgpotsortasi'];
			
			
			$kginpks[$arrAfd[1]][$bar['notransaksi']]=$bar['beratmasukpmks'];
			$kgoutpks[$arrAfd[1]][$bar['notransaksi']]=$bar['beratkeluarpmks'];
			$kgnettopks[$arrAfd[1]][$bar['notransaksi']]=$bar['beratbersihpmks'];
			$tglpks[$arrAfd[1]][$bar['notransaksi']]=$bar['tanggalpks'];
			
			$kgnormal[$arrAfd[1]][$bar['notransaksi']]=$bar['beratbersih']-$bar['kgpotsortasi'];
			// $kgnormal[$arrAfd[1]][$bar['notransaksi']]=$bar['beratbersih']-$bar['kgpotsortasi'];
			
		}
		
		
		$varian=$tvarian='';
		$varian=array();
		foreach($afdId as $afd){
			foreach($arrtiket as $tiket){
				if(@$notiket[$afd][$tiket]!=''){
					@$no+=1;
					$pabrik=makeOption($dbname,'kebun_spbht','nospb,penerimatbs',"nospb='".$spb[$afd][$tiket]."'");
					// $nmcust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$pabrik[$spb[$afd][$tiket]]."'");
					$nmcust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
					$stream.="<tr class=rowcontent>";
					$stream.="<td align=center >".$no."</td>";
					if($param['proses']=='excel'){
						$stream.="<td>".$tgl[$afd][$tiket]."</td>";
					}else{
						$stream.="<td>".tanggalnormal($tgl[$afd][$tiket])."</td>";
					}
					
					$stream.="<td>".$notiket[$afd][$tiket]."</td>";
					$stream.="<td>".$spb[$afd][$tiket]."</td>";
					$stream.="<td>".$spbpabrik[$afd][$tiket]."</td>";
					$stream.="<td>".$tahuntanam[$afd][$tiket]."</td>";
					// $stream.="<td>".$nmcust[$pabrik[$spb[$afd][$tiket]]]."</td>";
					$stream.="<td>".$nmcust[$kodecustomer[$afd][$tiket]]." - ".$kodecustomer[$afd][$tiket]."</td>";
					$stream.="<td>".$nmcust[$pabriktujuan[$afd][$tiket]]." - ".$pabriktujuan[$afd][$tiket]."</td>";
					$stream.="<td>".$nopol[$afd][$tiket]."</td>";
					$stream.="<td>".$supir[$afd][$tiket]."</td>";
					if($nilaix==1){ #ASRE
						$stream.="<td align=right>".number_format($kgin[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgout[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgnetto[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($jjg[$afd][$tiket])."</td>";
						
						if($param['proses']=='excel'){
							$stream.="<td align=right>".$tglpks[$afd][$tiket]."</td>";
						}else{
							$stream.="<td align=right>".tanggalnormal($tglpks[$afd][$tiket])."</td>";
						}
						
						$stream.="<td align=right>".number_format($kginpks[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgoutpks[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgnettopks[$afd][$tiket])."</td>";
						
						
						// $stream.="<td align=right>".number_format($jjgsortasi[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgsortasi[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgnormal[$afd][$tiket])."</td>";
						
						
						
						$stream.="<td align=right>".number_format($kgnetto[$afd][$tiket]-$kgnettopks[$afd][$tiket])."</td>";
						if ($kgnetto[$afd][$tiket]==0) {
							$varian=0;
						}
						else
						{
							$varian=((($kgnetto[$afd][$tiket]-$kgnettopks[$afd][$tiket])/$kgnetto[$afd][$tiket])*100);
						}
						
						$color='';
						if($varian >= 5 or $varian <= -5){
							$color=" bgcolor=red";
						}
						$stream.="<td align=right ".$color.">".number_format($varian,2)."</td>";
						
						@$tkginpks[$afd]+=$kginpks[$afd][$tiket];
						@$tkgoutpks[$afd]+=$kgoutpks[$afd][$tiket];
						@$tkgnettopks[$afd]+=$kgnettopks[$afd][$tiket];
						
					}else{
						$stream.="<td align=right>".number_format($kgin[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgout[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgnetto[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($jjg[$afd][$tiket])."</td>";
						// $stream.="<td align=right>".number_format($jjgsortasi[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgsortasi[$afd][$tiket])."</td>";
						$stream.="<td align=right>".number_format($kgnormal[$afd][$tiket])."</td>";					
					}

					$stream.="</tr>";			
					@$tjjg[$afd]+=$jjg[$afd][$tiket];
					@$tjjgsortasi[$afd]+=$jjgsortasi[$afd][$tiket];
					@$tkgnetto[$afd]+=$kgnetto[$afd][$tiket];
					@$tkgsortasi[$afd]+=$kgsortasi[$afd][$tiket];
					@$tkgnormal[$afd]+=$kgnormal[$afd][$tiket];
				}
			}
			
			if($nilaix==1){ #ASRE
				$stream.="<tr  class=rowcontent>";
				$stream.="<td colspan=12  align=right><b>".$_SESSION['lang']['subtotal']." ".$afd."</b></td>";
				$stream.="<td align=right>".number_format($tkgnetto[$afd],0)."</td>";
				$stream.="<td align=right>".number_format($tjjg[$afd],0)."</td>";
				
				
				$stream.="<td align=right></td>";
				$stream.="<td align=right>".number_format($tkginpks[$afd],0)."</td>";
				$stream.="<td align=right>".number_format($tkgoutpks[$afd],0)."</td>";
				$stream.="<td align=right>".number_format($tkgnettopks[$afd],0)."</td>";
				
				// $stream.="<td align=right>".number_format($tjjgsortasi[$afd],0)."</td>";
				$stream.="<td align=right>".number_format($tkgsortasi[$afd],0)."</td>";
				$stream.="<td align=right>".number_format($tkgnormal[$afd],0)."</td>";
				
				$stream.="<td align=right>".number_format($tkgnetto[$afd]-$tkgnettopks[$afd],0)."</td>";
				if ($tkgnetto[$afd]==0) {
							$tvarian=0;
						}
						else
						{
				$tvarian=((($tkgnetto[$afd]-$tkgnettopks[$afd])/$tkgnetto[$afd])*100);
						}
				$tcolor='';

				if($tvarian >= 5 or $tvarian <= -5){
					$tcolor=" bgcolor=red";
				}
				$stream.="<td align=right ".$tcolor.">".number_format($tvarian,2)."</td>";
				$stream.="</tr>";
				
				@$ttkginpks+=$tkginpks[$afd];
				@$ttkgoutpks+=$tkgoutpks[$afd];
				@$ttkgnettopks+=$tkgnettopks[$afd];
			}else{
				$stream.="<tr  class=rowcontent>";
				$stream.="<td colspan=12  align=right><b>".$_SESSION['lang']['subtotal']." ".$afd."</b></td>";
				$stream.="<td align=right>".number_format($tkgnetto[$afd],0)."</td>";
				$stream.="<td align=right>".number_format($tjjg[$afd],0)."</td>";
				// $stream.="<td align=right>".number_format($tjjgsortasi[$afd],0)."</td>";
				$stream.="<td align=right>".number_format($tkgsortasi[$afd],0)."</td>";
				$stream.="<td align=right>".number_format($tkgnormal[$afd],0)."</td>";
				$stream.="</tr>";				
			}
			
			@$ttjjg+=$tjjg[$afd];
			@$ttjjgsortasi+=$tjjgsortasi[$afd];
			@$ttkgnetto+=$tkgnetto[$afd];
			@$ttkgsortasi+=$tkgsortasi[$afd];
			@$ttkgnormal+=$tkgnormal[$afd];
			
		}
		
		if($nilaix==1){ #ASRE

			$stream.="<tr  class=rowcontent>";
			$stream.="<td colspan=12 align=right><b>".$_SESSION['lang']['grnd_total']."</b></td>";
			$stream.="<td align=right>".number_format($ttkgnetto,0)."</td>";
			$stream.="<td align=right>".number_format($ttjjg,0)."</td>";
			
			
			$stream.="<td align=right></td>";
			$stream.="<td align=right>".number_format($ttkginpks,0)."</td>";
			$stream.="<td align=right>".number_format($ttkgoutpks,0)."</td>";
			$stream.="<td align=right>".number_format($ttkgnettopks,0)."</td>";
				
			// $stream.="<td align=right>".number_format($ttjjgsortasi,0)."</td>";
			$stream.="<td align=right>".number_format($ttkgsortasi,0)."</td>";
			$stream.="<td align=right>".number_format($ttkgnormal,0)."</td>";
			
			$stream.="<td align=right>".number_format($ttkgnetto-$ttkgnettopks,0)."</td>";
			if ($ttkgnetto==0) {
				$stream.="<td align=right>0</td>";
			}
			else
			{
			$stream.="<td align=right>".number_format(((($ttkgnetto-$ttkgnettopks)/$ttkgnetto)*100),2)."</td>";
		}
			
			
			$stream.="</tr>";		
			$stream.="</tbody></table>";
		}else{
			$stream.="<tr  class=rowcontent>";
			$stream.="<td colspan=12 align=right><b>".$_SESSION['lang']['grnd_total']."</b></td>";
			$stream.="<td align=right>".number_format($ttkgnetto,0)."</td>";
			$stream.="<td align=right>".number_format($ttjjg,0)."</td>";
			// $stream.="<td align=right>".number_format($ttjjgsortasi,0)."</td>";
			$stream.="<td align=right>".number_format($ttkgsortasi,0)."</td>";
			$stream.="<td align=right>".number_format($ttkgnormal,0)."</td>";
			$stream.="</tr>";		
			$stream.="</tbody></table>";
		}
	}
switch($param['proses']){
	 
        case 'preview':          
            echo $stream;
        break;
        case 'excel':          
                        $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
                        $qwe=date("YmdHms");
                        $nop_="timbanganEksternal_".$param['kbnId']."__".$qwe;
						if(strlen($stream)>0){
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

						  
        break;
        case'getKodeAfd':
			$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
			$sAfd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['kbnId']."' and tipe='AFDELING' order by namaorganisasi asc";
			$qAfd=$owlPDO->query($sAfd) or die(print " Gagal: ".PDOException::getMessage());
			$qAfd->setFetchMode(PDO::FETCH_ASSOC);
			while($rAfd=$qAfd->fetch()){
				$optorg.="<option value='".$rAfd['kodeorganisasi']."'>".$rAfd['namaorganisasi']."</option>";
			}
        echo $optorg;
        break;
        default:
        break;
}
?>