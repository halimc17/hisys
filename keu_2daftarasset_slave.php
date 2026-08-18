<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/nangkoelib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$stream='';
$method = checkPostGet('method', '');
$kdunit = checkPostGet('kdunit', '');
$kdpt = checkPostGet('kdpt', '');
$kdperiode = checkPostGet('kdperiode', '');
$kdtipeasset = checkPostGet('kdtipeasset', '');
$kdsubtipeasset = checkPostGet('kdsubtipeasset', '');
$tipe = checkPostGet('tipe', '');

$arrjenisbiaya=array("2"=>"Biaya Umum");
$arrstatus=array("1"=>"Aktif","0"=>"Tidak Aktif");

$optunit=$optsubtipe="<option value=''>".$_SESSION['lang']['all']."</option>";
// print_r($arrperiode);

switch ($method) {

	case'getunit':
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$kdpt."' order by namaorganisasi asc ";	
		$res=fetchdata($str);
		foreach($res as $bar){
			$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
		}
		echo $optunit;
	break;

	case'getsubtipeasset':
		$str="select kodesub,namasub from ".$dbname.".sdm_5subtipeasset where kodetipe='".$kdtipeasset."' order by namasub asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optsubtipe.="<option value='".$bar['kodesub']."'>".$bar['namasub']."</option>";
		}
		echo $optsubtipe;
	break;
	
######PREVIEW
    case 'preview':
	
		$where='';
		$whereorg=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kdpt."')";
		if($kdunit!='') {
			$whereorg=" and kodeorg='".$kdunit."'";
		}

		if($kdtipeasset!='') {
			$where.=" and tipeasset='".$kdtipeasset."'";
		}

		if($kdsubtipeasset!='') {
			$where.=" and subtipe='".$kdsubtipeasset."'";
		}
		#= bentuk periode thn ini dari parameter periode kirim
			// echo $kdperiode;
	
		$thnsekarang=explode('-',$kdperiode);
		$thnsekarang=$thnsekarang[0];
		$thnlalu=$thnsekarang-1;
	
		// echo $thnsekarang;
		// $arrperiode=month_inbetween($thnsekarang.'-01',$thnsekarang.'-12');
		$arrperiode=month_inbetween($thnsekarang.'-01',$kdperiode);
		// $arrperiode=month_inbetween($thnsekarang.'-01',$thnsekarang.'-12');
		
		$cspan=count($arrperiode)+1;
	
		if($tipe=='excel' or $tipe=='pdf'){
			$border=1;
		} else {
			$border=0;
		}
		
		// $stream.="Laporan Uang Muka<br><br>";
	
		//<td align='center'>".$_SESSION['lang']['pembayaran']."</td>
		$stream.="<table class=sortable cellspacing=1 border='".$border."' width=100%>";
		$stream.="<thead>";
				$stream.="<tr class=rowcontent>";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</td>";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kodeasset']."</td> ";
					
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namaasset']."</td>";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kodeasset']." Lama</td> ";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakelompok']."</td> ";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['induk']."</td> ";
					$stream.="<td align=center rowspan=2>Tanggal Perolehan</td>";
					$stream.="<td align=center rowspan=2>Tanggal Disposal</td> ";	
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nomorrangka']."</td>";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nomormesin']."</td>";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jenisbiaya']."</td>";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['status']."</td> ";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['hargaperolehan']."</td> ";
					
					$stream.="<td align=center rowspan=2 >".$_SESSION['lang']['awalpenyusutan']."</td> ";
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jumlahbulanpenyusutan']."</td> "; 
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['bulanan']." (Rp)</td> "; 
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['usia']." (".$_SESSION['lang']['bulan'].")</td> "; 
					$stream.="<td align=center rowspan=2>".$_SESSION['lang']['sisa']." (".$_SESSION['lang']['bulan'].")</td> "; 
					$stream.="<td align=center rowspan=2 >".$_SESSION['lang']['akumulasipenyusutan']."<br>Awal (".substr($kdperiode,0,4).")</td> ";
					$stream.="<td align=center colspan=".$cspan.">".$thnsekarang."</td>";
					$stream.="<td align=center rowspan=2 >".$_SESSION['lang']['akumulasipenyusutan']."<br>Akhir (".substr($kdperiode,0,4).")</td> ";
					$stream.="<td align=center rowspan=2 >".$_SESSION['lang']['nilaibuku']."</td> ";
				$stream.="</tr>";	
				$stream.="<tr class=rowcontent>";
				foreach($arrperiode as $per){
					$stream.="<td align=center>".numToMonth(intval(substr($per,5,2)),'I','long')."</td>";
				}
				$stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
				$stream.="</tr>";	
			$stream.="</thead>
		 <tbody>";
		
		
		$str="select distinct kodetipe,namatipe from ".$dbname.".sdm_5tipeasset order by namatipe asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrtipe[$bar['kodetipe']]=$bar['kodetipe'];
		}

		$str="select * from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='DEP'";
			// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			// $noakundep[$bar['noakunkredit']]=$bar['noakunkredit'];
			$noakundep[$bar['noakundebet']]=$bar['noakundebet'];
		}
		
	
		$arrassetanak=array();
	
		#= akumulasi thn sekarang
		$str="select * from ".$dbname.".keu_jurnaldt_vw where noakun in ('".implode("','",$noakundep)."') and periode in ('".implode("','",$arrperiode)."')";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$jumlahperbulan[$bar['kodeasset']][$bar['periode']]=$bar['jumlah'];
			@$jumlahtahunini[$bar['kodeasset']]+=$bar['jumlah'];
		}
		
		// echo"<pre>";
		// print_r($jumlahtahunini);
		
		#= akumulasi tahun lalu
		$str="select * from ".$dbname.".keu_jurnaldt_vw where noakun in ('".implode("','",$noakundep)."') and periode < '".$thnsekarang."-01' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$akumulasipenyusutantahunlalu[$bar['kodeasset']]+=$bar['jumlah'];
		}
		
		
		// print_r($jumlahperbulan);
		$noaset=0;
		#= arr induk 
		$str="select * from ".$dbname.".sdm_daftarasset where 1=1 ".$whereorg." ".$where." and induk=''";
		$res=fetchdata($str);
		foreach($res as $bar){
			$noaset++;
			$arrasset[$bar['kodeasset']]=$bar['kodeasset'];
			$kodeorg[$bar['kodeasset']]=$bar['kodeorg'];
			$tipeasset[$bar['kodeasset']]=$bar['tipeasset'];
			$kodeassetlama[$bar['kodeasset']]=$bar['kodeassetlama'];
			$tanggalperolehan[$bar['kodeasset']]=$bar['tanggalperolehan'];
			$tanggaldisposal[$bar['kodeasset']]=$bar['tanggaldisposal'];
			$namasset[$bar['kodeasset']]=$bar['namasset'];
			$norangka[$bar['kodeasset']]=$bar['norangka'];
			$nomesin[$bar['kodeasset']]=$bar['nomesin'];
			$jenis_biaya[$bar['kodeasset']]=$bar['jenis_biaya'];
			$bulanan[$bar['kodeasset']]=$bar['bulanan'];
			$status[$bar['kodeasset']]=$bar['status'];
			$hargaperolehan[$bar['kodeasset']]=$bar['hargaperolehan'];
			$jlhblnpenyusutan[$bar['kodeasset']]=$bar['jlhblnpenyusutan'];
			$awalpenyusutan[$bar['kodeasset']]=$bar['awalpenyusutan'];
			$jumlahsudahsusut[$bar['kodeasset']]=(selisihbulan($bar['awalpenyusutan'],$kdperiode)+1);//di +1 karna periode awal susut sudah dihitung 1
			
			if($jumlahsudahsusut[$bar['kodeasset']]>=$bar['jlhblnpenyusutan']){
				$jumlahsudahsusut[$bar['kodeasset']]=$bar['jlhblnpenyusutan'];
			}
			
			@$sisabelumsusut[$bar['kodeasset']]=$bar['jlhblnpenyusutan']-$jumlahsudahsusut[$bar['kodeasset']];
			@$akumulasipenyusutantahunini[$bar['kodeasset']]=$akumulasipenyusutantahunlalu[$bar['kodeasset']]+$jumlahtahunini[$bar['kodeasset']];
			@$nilaibuku[$bar['kodeasset']]=$bar['hargaperolehan']-$akumulasipenyusutantahunini[$bar['kodeasset']];
		}
		
		#= arr anak
		$str="select * from ".$dbname.".sdm_daftarasset where 1=1  ".$whereorg." ".$where." and induk!=''";
		$res=fetchdata($str);
		foreach($res as $bar){
			$noaset++;
			$listassetanak[$bar['induk']][$bar['kodeasset']]=$bar['kodeasset'];
			$arrassetanak[$bar['kodeasset']]=$bar['kodeasset'];
			
			$kodeorg[$bar['kodeasset']]=$bar['kodeorg'];
			$indukasset[$bar['kodeasset']]=$bar['induk'];
			$tipeasset[$bar['kodeasset']]=$bar['tipeasset'];
			$kodeassetlama[$bar['kodeasset']]=$bar['kodeassetlama'];
			$tanggalperolehan[$bar['kodeasset']]=$bar['tanggalperolehan'];
			$tanggaldisposal[$bar['kodeasset']]=$bar['tanggaldisposal'];
			$namasset[$bar['kodeasset']]=$bar['namasset'];
			$norangka[$bar['kodeasset']]=$bar['norangka'];
			$nomesin[$bar['kodeasset']]=$bar['nomesin'];
			$bulanan[$bar['kodeasset']]=$bar['bulanan'];
			$jenis_biaya[$bar['kodeasset']]=$bar['jenis_biaya'];
			$status[$bar['kodeasset']]=$bar['status'];
			$hargaperolehan[$bar['kodeasset']]=$bar['hargaperolehan'];
			$jlhblnpenyusutan[$bar['kodeasset']]=$bar['jlhblnpenyusutan'];
			$awalpenyusutan[$bar['kodeasset']]=$bar['awalpenyusutan'];
			$jumlahsudahsusut[$bar['kodeasset']]=(selisihbulan($bar['awalpenyusutan'],$kdperiode)+1);//di +1 karna periode awal susut sudah dihitung 1	
			if($jumlahsudahsusut[$bar['kodeasset']]>=$bar['jlhblnpenyusutan']){
				$jumlahsudahsusut[$bar['kodeasset']]=$bar['jlhblnpenyusutan'];
			}
			@$sisabelumsusut[$bar['kodeasset']]=$bar['jlhblnpenyusutan']-$jumlahsudahsusut[$bar['kodeasset']];
			@$akumulasipenyusutantahunini[$bar['kodeasset']]=$akumulasipenyusutantahunlalu[$bar['kodeasset']]+$jumlahtahunini[$bar['kodeasset']];
			@$nilaibuku[$bar['kodeasset']]=$bar['hargaperolehan']-$akumulasipenyusutantahunini[$bar['kodeasset']];
		}
		
		if($noaset==0){
			exit("Warning:Data Kosong");
		}
		
		
		foreach($arrasset as $kodeasset){
			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td>".$no."</td>";
				$stream.="<td>".$kodeorg[$kodeasset]."</td>";
				$stream.="<td>".$kodeasset."</td>";
				$stream.="<td>".$namasset[$kodeasset]."</td>";
				$stream.="<td>".$kodeassetlama[$kodeasset]."</td>";
				$stream.="<td>".$tipeasset[$kodeasset]."</td>";
				$stream.="<td></td>";
				if($tipe=='excel'){
					$stream.="<td>".$tanggalperolehan[$kodeasset]."</td>";
				}else{
					$stream.="<td>".tanggalnormal($tanggalperolehan[$kodeasset])."</td>";
				}
				$stream.="<td>".$tanggaldisposal[$kodeasset]."</td>";
				$stream.="<td>".$norangka[$kodeasset]."</td>";
				$stream.="<td>".$nomesin[$kodeasset]."</td>";
				$stream.="<td>".$arrjenisbiaya[$jenis_biaya[$kodeasset]]."</td>";
				$stream.="<td>".$arrstatus[$status[$kodeasset]]."</td>";
				$stream.="<td align=right>".number_format($hargaperolehan[$kodeasset],2)."</td>";
				$stream.="<td align=right>".$awalpenyusutan[$kodeasset]."</td>";
				$stream.="<td align=right>".$jlhblnpenyusutan[$kodeasset]."</td>";
				$stream.="<td align=right>".number_format($bulanan[$kodeasset],2)."</td>";
				$stream.="<td align=right>".$jumlahsudahsusut[$kodeasset]."</td>";
				$stream.="<td align=right>".$sisabelumsusut[$kodeasset]."</td>";
				$stream.="<td align=right>".@number_format($akumulasipenyusutantahunlalu[$kodeasset],2)."</td>";
				foreach($arrperiode as $per){
					$stream.="<td align=right>".@number_format($jumlahperbulan[$kodeasset][$per],2)."</td>";
					@$tjumlahperbulan[$per]+=$jumlahperbulan[$kodeasset][$per];
				}
				$stream.="<td align=right>".@number_format($jumlahtahunini[$kodeasset],2)."</td>";
				$stream.="<td align=right>".@number_format($akumulasipenyusutantahunini[$kodeasset],2)."</td>";
				$stream.="<td align=right>".@number_format($nilaibuku[$kodeasset],2)."</td>";
				// $stream.="<td>".[$kodeasset]."</td>";
			$stream.="</tr>";
			foreach($arrassetanak as $kodeassetanak){
				if(@$listassetanak[$kodeasset][$kodeassetanak]!=''){
					$no++;
					$stream.="<tr class=rowcontent>";
						$stream.="<td>".$no."</td>";
						$stream.="<td>".$kodeorg[$kodeassetanak]."</td>";
						$stream.="<td>".$kodeassetanak."</td>";
						$stream.="<td>".$namasset[$kodeassetanak]."</td>";
						$stream.="<td>".$kodeassetlama[$kodeassetanak]."</td>";
						$stream.="<td>".$tipeasset[$kodeassetanak]."</td>";
						$stream.="<td>".$indukasset[$kodeassetanak]."</td>";
						// $stream.="<td>".$tanggalperolehan[$kodeassetanak]."</td>";
						
						if($tipe=='excel'){
							$stream.="<td>".$tanggalperolehan[$kodeassetanak]."</td>";
						}else{
							$stream.="<td>".tanggalnormal($tanggalperolehan[$kodeassetanak])."</td>";
						}
						
						$stream.="<td>".$tanggaldisposal[$kodeassetanak]."</td>";
						$stream.="<td>".$norangka[$kodeassetanak]."</td>";
						$stream.="<td>".$nomesin[$kodeassetanak]."</td>";
						$stream.="<td>".$arrjenisbiaya[$jenis_biaya[$kodeassetanak]]."</td>";
						$stream.="<td>".$arrstatus[$status[$kodeassetanak]]."</td>";
						$stream.="<td align=right>".number_format($hargaperolehan[$kodeassetanak],2)."</td>";
						$stream.="<td align=right>".$awalpenyusutan[$kodeassetanak]."</td>";
						$stream.="<td align=right>".$jlhblnpenyusutan[$kodeassetanak]."</td>";
						$stream.="<td align=right>".number_format($bulanan[$kodeassetanak],2)."</td>";
						$stream.="<td align=right>".$jumlahsudahsusut[$kodeassetanak]."</td>";
						$stream.="<td align=right>".$sisabelumsusut[$kodeassetanak]."</td>";
						$stream.="<td align=right>".@number_format($akumulasipenyusutantahunlalu[$kodeassetanak],2)."</td>";
						foreach($arrperiode as $per){
							$stream.="<td align=right align=right>".@number_format($jumlahperbulan[$kodeassetanak][$per],2)."</td>";
							@$tjumlahperbulan[$per]+=$jumlahperbulan[$kodeassetanak][$per];
						}
						$stream.="<td align=right>".@number_format($jumlahtahunini[$kodeassetanak],2)."</td>";
						$stream.="<td align=right>".@number_format($akumulasipenyusutantahunini[$kodeassetanak],2)."</td>";
						$stream.="<td align=right>".@number_format($nilaibuku[$kodeassetanak],2)."</td>";
					$stream.="</tr>";
					
					@$thargaperolehan+=$hargaperolehan[$kodeassetanak];
					@$takumulasipenyusutantahunlalu+=$akumulasipenyusutantahunlalu[$kodeassetanak];
					@$tjumlahtahunini+=$jumlahtahunini[$kodeassetanak];
					@$takumulasipenyusutantahunini+=$akumulasipenyusutantahunini[$kodeassetanak];
					@$tnilaibuku+=$nilaibuku[$kodeassetanak];
					
				}
			}
			@$thargaperolehan+=$hargaperolehan[$kodeasset];
			@$takumulasipenyusutantahunlalu+=$akumulasipenyusutantahunlalu[$kodeasset];
			@$tjumlahtahunini+=$jumlahtahunini[$kodeasset];
			@$takumulasipenyusutantahunini+=$akumulasipenyusutantahunini[$kodeasset];
			@$tnilaibuku+=$nilaibuku[$kodeasset];
		}
		$stream.="<tr class=rowcontent>";
			$stream.="<td colspan=13>".$_SESSION['lang']['total']."</td>";
			$stream.="<td align=right>".number_format($thargaperolehan,2)."</td>";
			$stream.="<td></td>";
			$stream.="<td></td>";
			$stream.="<td></td>";
			$stream.="<td></td>";
			$stream.="<td align=right>".number_format($takumulasipenyusutantahunlalu,2)."</td>";
			foreach($arrperiode as $per){
				$stream.="<td align=right>".number_format($tjumlahperbulan[$per],2)."</td>";
			}
			$stream.="<td align=right>".number_format($tjumlahtahunini,2)."</td>";
			$stream.="<td align=right>".number_format($takumulasipenyusutantahunini,2)."</td>";
			$stream.="<td align=right>".number_format($tnilaibuku,2)."</td>";
		$stream.="</tr>";
		$stream.="</table>";
		
		if($tipe=='excel'){
			$nop = "laporan_daftar_asset_.".$kdpt.".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("Asset", $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else if($tipe=='pdf'){
			$dompdf = new Dompdf();
            $dompdf->loadHtml($stream);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("form survey",array("Attachment"=>0));
		}else{
			echo $stream;
		}
	break;
	
	case'':
	break;
}
?>