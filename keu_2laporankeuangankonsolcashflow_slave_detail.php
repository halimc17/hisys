<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method','');
$param = $_POST;
$cparam=count($param);
if($cparam==0){
	$param=$_GET;
}
$kodelaporan='CASHFLOWKONSOL';

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	$namamesinlaporan[$bar['nourut']]=$bar['keterangandisplay'];
	$tipeunit[$bar['nourut']]=$bar['tipeunit'];
}

$stream='';

if($param['kodeunit']!=''){
	$wherekodeunit=" and kodeorg='".$param['kodeunit']."'";
	$wherekodeunitadjust=" and kodeunit='".$param['kodeunit']."'";
}

$where="  and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' ".$wherekodeunit.")";
$whereadjust=" and substr(kodeunit,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$wherekodeunitadjust.")";

switch($method){
	case'preview':
	
		$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
		$stream.="
			<table cellpading=1 cellspacing=1 ".$border." class=sortable>
			<thead>
				<tr class=rowheader>
					<td align='center'>".$_SESSION['lang']['nourut']."</td>
					<td align='center'>".$_SESSION['lang']['unit']."</td>
					<td align='center'>".$_SESSION['lang']['noakun']."</td>
					<td align='center'>".$_SESSION['lang']['namaakun']."</td>
					<td align='center'>".$_SESSION['lang']['saldoawal']."</td>
					<td align='center'>".$_SESSION['lang']['debet']."</td>
					<td align='center'>".$_SESSION['lang']['kredit']."</td>
					<td align='center'>".$_SESSION['lang']['saldoakhir']."</td>
					<td align='center'>".$_SESSION['lang']['Mutasi1']."</td>
					
					
				</tr>
			</thead>";
		$no=0;
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$param['kodeurut']."' and namalaporan='".$kodelaporan."'";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnoakun[$bar['noakun']]=$bar['noakun'];
		}
			
		$perdata=$param['periode'];
		$explodeperdata=explode('-',$perdata);
		$bulanperdata=$explodeperdata[1];
		$kolomthnini="awal".$bulanperdata;
		
		$awal="awal".$bulanperdata;
		$debet="debet".$bulanperdata;
		$kredit="kredit".$bulanperdata;
		
		$perdata=str_replace("-", "",$perdata);	
		$str="select ".$awal." as sawal,".$debet." as debet,".$kredit." as kredit,noakun,kodeorg from ".$dbname.".keu_saldobulanan where 1=1  ".$where."  and noakun in ('".implode("','",$arrnoakun)."')  and periode='".$perdata."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align='center'>".$no."</td>";
				$stream.="<td align='left'>".$bar['kodeorg']."</td>";
				$stream.="<td align='left'>".$bar['noakun']."</td>";
				$stream.="<td align='left'>".$nmakun[$bar['noakun']]."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['sawal'])."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['debet'])."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['kredit'])."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['sawal']+$bar['debet']-$bar['kredit'])."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['debet']-$bar['kredit'])."</td>";
			$stream.="</tr>";
			$tsawal+=$bar['sawal'];
			$tdebet+=$bar['debet'];
			$tkredit+=$bar['kredit'];
			$tsalak+=$bar['sawal']+$bar['debet']-$bar['kredit'];
		}	
		
		#= keu_adjustmentlaporankeuangan
		$str="select * from ".$dbname.".keu_adjustmentlaporankeuangan where 1=1  ".$whereadjust." and jenis='".$kodelaporan."' and code='".$param['kodeurut']."' and periode='".$param['periode']."'";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align='center'>".$no."</td>";
				$stream.="<td align='left'>".$bar['kodeunit']."</td>";
				$stream.="<td align='left'>Adjust</td>";
				$stream.="<td align='left' colspan=5>".$bar['keterangan']."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['jumlah'])."</td>";
			$stream.="</tr>";
			$tadjust+=$bar['jumlah'];
		}
		
		
		$stream.="<tr class=rowcontent>
					<td align=center colspan=4>Total</td>
					<td align=right>".hidezerodecimal($tsawal)."</td>
					<td align=right>".hidezerodecimal($tdebet)."</td>
					<td align=right>".hidezerodecimal($tkredit)."</td>
					<td align=right>".hidezerodecimal($tsalak)."</td>
					<td align=right>".hidezerodecimal($tdebet-$tkredit+$tadjust)."</td>
				</tr></table>";
		echo $stream;
	
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