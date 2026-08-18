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
$kodelaporan='NERACAKONSOL';

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
}

$where="  and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' ".$wherekodeunit.")";

switch($method){
	case'preview':
	
		$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
		$stream.="
			<table cellpading=1 cellspacing=1 ".$border." class=sortable>
			<thead>
				<tr class=rowheader>
					<td align='center'>".$_SESSION['lang']['nourut']."</td>
					<td align='center'>".$_SESSION['lang']['noakun']."</td>
					<td align='center'>".$_SESSION['lang']['namaakun']."</td>
					<td align='center'>".$_SESSION['lang']['jumlah']."</td>
					<td align='center'>".$_SESSION['lang']['unit']."</td>
					
				</tr>
			</thead>";
		$no=0;
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$param['kodeurut']."' and namalaporan='".$kodelaporan."'";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnoakun[$bar['noakun']]=$bar['noakun'];
		}
			
		$perdata=periodeberikut($param['periode']);
		$explodeperdata=explode('-',$perdata);
		$bulanperdata=$explodeperdata[1];
		$kolomthnini="awal".$bulanperdata;
		$perdata=str_replace("-", "",$perdata);	//periode depan karna diambil dari saldo akhir berjalan, misal data periode 3, maka ambil sawal periode 4
		$str="select ".$kolomthnini." as jumlah,noakun,kodeorg from ".$dbname.".keu_saldobulanan where 1=1  ".$where."  and noakun in ('".implode("','",$arrnoakun)."')  and periode='".$perdata."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align='center'>".$no."</td>";
				$stream.="<td align='left'>".$bar['noakun']."</td>";
				$stream.="<td align='left'>".$nmakun[$bar['noakun']]."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['jumlah'])."</td>";
				$stream.="<td align='left'>".$bar['kodeorg']."</td>";
				
			$stream.="</tr>";
			$tjumlah+=$bar['jumlah'];
		}	
		
		
		$stream.="<tr class=rowcontent>
					<td align=center colspan=3>Total</td>
					<td align=right>".hidezerodecimal($tjumlah)."</td>
					<td align=center></td>
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