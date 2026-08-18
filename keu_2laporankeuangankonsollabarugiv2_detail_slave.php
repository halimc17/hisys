<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method','');
$param = $_POST;
$cparam=count($param);
if($cparam==0){
	$param=$_GET;
}
$kodelaporan='LABARUGINEW';
$dgt=$param['digit'];


$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	$namamesinlaporan[$bar['nourut']]=$bar['keterangandisplay'];
	@$tipeunit[$bar['nourut']]=$bar['tipeunit'];
}

$stream='';

if($param['kodeunit']!=''){
	$wherekodeunit=" and kodeorg='".$param['kodeunit']."'";
}

$where="  and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' ".@$wherekodeunit.")";

switch($method){
	case'preview':
	
		$stream.="
			<table cellpading=1 cellspacing=1 ".@$border." class=sortable>";
			if($param['tipe']=='html'){
				$stream.="<caption style='text-align:left'><button class='button verify' onclick=\"detail('".$param['kodeurut']."','".$param['periode']."','".@$param['kodept']."','".$param['regional']."','".$param['kodeunit']."','excel','event','".$param['digit']."')\">Excel</button></caption>";
			}
			$stream.="<thead>
				<tr class=rowheader>";
					$stream.="<td align='center'>".$_SESSION['lang']['nourut']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['nojurnal']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['tanggal']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['periode']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['nourut']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['noakun']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['noaruskas']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['keterangan']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['jumlah']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['matauang']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kurs']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodeorg']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodekegiatan']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodeasset']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodebarang']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['nik']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodecustomer']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodesupplier']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['nodok']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['noreferensi']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['debet']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kredit']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodevhc']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodeblok']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['revisi']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['autojurnal']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodejurnal']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['tanggalentry']."</td>";
					$stream.="<td align='center'>".$_SESSION['lang']['kodesegment']."</td>";
				$stream.="</tr>
			</thead>";
		$no=0;
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$param['kodeurut']."' and namalaporan='".$kodelaporan."'";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnoakun[$bar['noakun']]=$bar['noakun'];
		}
		
		/*
		if (substr($param['periode'],5,2)=='01') {
			$perdata=periodeberikut($param['periode']);
			$explodeperdata=explode('-',$perdata);
			$bulanperdata=$explodeperdata[1];
			$kolomthnini="awal".$bulanperdata;
		}else {
			$perdata=($param['periode']);
			$explodeperdata=explode('-',$perdata);
			$bulanperdata=$explodeperdata[1];
			$kolomthnini="debet".$bulanperdata."-"."kredit".$bulanperdata;
		}
		*/

		// $perdata=str_replace("-", "",$perdata);	//periode depan karna diambil dari saldo akhir berjalan, misal data periode 3, maka ambil sawal periode 4
		$str="select * from ".$dbname.".keu_jurnaldt_vw where 1=1  ".$where."  and noakun in ('".implode("','",$arrnoakun)."')  and periode='".$param['periode']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align='center'>".$no."</td>";
				$stream.="<td>".$bar['nojurnal']."</td>";
				$stream.="<td>".$bar['tanggal']."</td>";
				$stream.="<td>".$bar['periode']."</td>";
				$stream.="<td>".$bar['nourut']."</td>";
				$stream.="<td>".$bar['noakun']."</td>";
				$stream.="<td>".$bar['noaruskas']."</td>";
				$stream.="<td>".$bar['keterangan']."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['jumlah'],$dgt)."</td>";
				$stream.="<td>".$bar['matauang']."</td>";
				$stream.="<td>".$bar['kurs']."</td>";
				$stream.="<td>".$bar['kodeorg']."</td>";
				$stream.="<td>".$bar['kodekegiatan']."</td>";
				$stream.="<td>".$bar['kodeasset']."</td>";
				$stream.="<td>".$bar['kodebarang']."</td>";
				$stream.="<td>".$bar['nik']."</td>";
				$stream.="<td>".$bar['kodecustomer']."</td>";
				$stream.="<td>".$bar['kodesupplier']."</td>";
				$stream.="<td>".$bar['nodok']."</td>";
				$stream.="<td>".$bar['noreferensi']."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['debet'],$dgt)."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['kredit'],$dgt)."</td>";
				$stream.="<td>".$bar['kodevhc']."</td>";
				$stream.="<td>".$bar['kodeblok']."</td>";
				$stream.="<td>".$bar['revisi']."</td>";
				$stream.="<td>".$bar['autojurnal']."</td>";
				$stream.="<td>".$bar['kodejurnal']."</td>";
				$stream.="<td>".$bar['tanggalentry']."</td>";
				$stream.="<td>".$bar['kodesegment']."</td>";
			$stream.="</tr>";
			@$tjumlah+=$bar['jumlah'];
			@$tdebet+=$bar['debet'];
			@$tkredit+=$bar['kredit'];
		}	
		
		
		$stream.="<tr class=rowcontent>
					<td align=center colspan=8>Total</td>
					<td align=right>".hidezerodecimal($tjumlah,$dgt)."</td>
					<td align=center colspan=11></td>
					<td align=right>".hidezerodecimal($tdebet,$dgt)."</td>
					<td align=right>".hidezerodecimal($tkredit,$dgt)."</td>
					<td align=center colspan=7></td>
				</tr></table>";
				
		if($param['tipe']=='excel'){
			$nop="LABARUGIKONSOL_DETAIL.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("LABARUGIKONSOL_DETAIL", $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $stream;			
		}
	
	break;
	
	
	case'previewsubtotal':
		$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
		
			
			// noakundisplay
			$str="select * from ".$dbname.".keu_5mesinlaporandt where  namalaporan='".$kodelaporan."' and nourut='".$kodeurut."' ";
			$res=fetchdata($str);
			foreach($res as $bar){
				$noakundisplay=$bar['noakundisplay'];
			}
			$explodenoakundisplay=explode(',',$noakundisplay);
			foreach($explodenoakundisplay as $nourut){
				$arrnourut[$nourut]=$nourut;
			}
			
			
			foreach($arrnourut as $dtnourut){
				$arrkodearuskas=array();
				$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$dtnourut."' and namalaporan='".$kodelaporan."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$arrkodearuskas[$bar['noakun']]=$bar['noakun'];
				}
				
				$str="select * from ".$dbname.".keu_kasbankdtht_vw where 1=1 and pembayaran=1 and tanggal like '".$per."%'  ".$where." and noaruskas in ('".implode("','",$arrkodearuskas)."')  ";
				$res=fetchdata($str);
				foreach($res as $bar){
					if($bar['tipetransaksi']=='K'){
						$bar['jumlah']=$bar['jumlah']*-1;
					}
					$dtjumlah[$dtnourut]+=$bar['jumlah'];
				}
			}
			$stream.=" ".$kodeurut." : ".$namamesinlaporan[$kodeurut]."<br>";
			$stream.=" ".$_SESSION['lang']['periode']." : ".$per."<br><br>";
			$stream.="
			<table cellpading=1 cellspacing=1 ".$border." class=sortable width=100%>
			<thead>
				<tr class=rowheader>
					<tr class=rowheader>
						<td align='center'>".$_SESSION['lang']['kode']."</td>
						<td align='center'>".$_SESSION['lang']['nama']."</td>
						<td align='center'>".$_SESSION['lang']['nilai']."</td>
					</tr>
			</thead>";	
			
			foreach($arrnourut as $dtnourut){
				$stream.="<tr class=rowcontent  title='Click untuk melihat detail' onclick=\"detail('".$dtnourut."','".$per."','".$pt."','".$regional."','".$unit."','html','event');\">";
					$stream.="<td align=right>".$dtnourut."</td>";
					$stream.="<td>".$namamesinlaporan[$dtnourut]."</td>";
					$stream.="<td align=right>".hidezerodecimal($dtjumlah[$dtnourut],2)."</td>";
				$stream.="</tr>";
				@$tdtjumlah+=$dtjumlah[$dtnourut];
			}
			$stream.="<tr class=rowcontent>";
				$stream.="<td colspan=2 align=center>".$_SESSION['lang']['total']."</td>";
				$stream.="<td align=right>".hidezerodecimal($tdtjumlah,2)."</td>";
			$stream.="</tr>";
			$stream.="<table>";
			// echo $per._.$kodeurut;
		echo $stream;
	break;
	
	
}












?>