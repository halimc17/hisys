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
$kodelaporan='MPI';

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	$namamesinlaporan[$bar['nourut']]=$bar['keterangandisplay'];
	$tipeunit[$bar['nourut']]=$bar['tipeunit'];
}

$stream='';


if($tipeunit[$param['kodeurut']]!=''){
	$notipeunit=0;
	$arrtipeunit=explode(',',$tipeunit[$param['kodeurut']]);
	foreach($arrtipeunit as $key){
		if($notipeunit>0){
			@$daftartipeunit[$param['kodeurut']].=",'".trim($key)."'";
		}else{
			@$daftartipeunit[$param['kodeurut']].="'".trim($key)."'";
		}
		$notipeunit++;
	}
	$wheretipunit=" and tipe in (".$daftartipeunit[$param['kodeurut']].")";
	$wherejurnal=$wheretipunit;
}


if($param['kodeunit']!=''){
	$wherekodeunit=" and kodeorg='".$param['kodeunit']."'";
}

$where="  and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' ".$wherekodeunit." ".$wheretipunit.")";

switch($method){
	case'preview':
	
		$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
		$stream.="
			<table cellpadding=3 cellspacing=1 ".$border." class=sortable>
			<thead>
				<tr class=rowheader>
					<th align='center'>".$_SESSION['lang']['nourut']."</th>
					<th align='center'>".$_SESSION['lang']['nojurnal']."</th>
					<th align='center'>".$_SESSION['lang']['tanggal']."</th>
					<th align='center'>".$_SESSION['lang']['noakun']."</th>
					<th align='center'>".$_SESSION['lang']['keterangan']."</th>
					<th align='center'>".$_SESSION['lang']['jumlah']."</th>
					<th align='center'>".$_SESSION['lang']['kodeorg']."</th>
					<th align='center'>".$_SESSION['lang']['kodekegiatan']."</th>
					<th align='center'>".$_SESSION['lang']['kodeasset']."</th>
					<th align='center'>".$_SESSION['lang']['kodebarang']."</th>
					<th align='center'>".$_SESSION['lang']['nik']."</th>
					<th align='center'>".$_SESSION['lang']['kodecustomer']."</th>
					<th align='center'>".$_SESSION['lang']['kodesupplier']."</th>
					<th align='center'>".$_SESSION['lang']['nodok']."</th>
					<th align='center'>".$_SESSION['lang']['noreferensi']."</th>
					<th align='center'>".$_SESSION['lang']['kodevhc']."</th>
					<th align='center'>".$_SESSION['lang']['kodeblok']."</th>
					<th align='center'>".$_SESSION['lang']['kodejurnal']."</th>
					<th align='center'>".$_SESSION['lang']['tanggalinput']."</th>
				</tr>
			</thead>";
		$no=0;
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$param['kodeurut']."' and namalaporan='".$kodelaporan."'";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnoakun[$bar['noakun']]=$bar['noakun'];
		}
		
		$wherekodejurnal='';
		$nokodejurnal=0;
		$arrkodejurnal=array();
		$str="select * from ".$dbname.".keu_5mesinlaporandt_kodejurnal where nourut='".$param['kodeurut']."' and namalaporan='".$kodelaporan."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrkodejurnal[$bar['kodejurnal']]=$bar['kodejurnal'];
			$nokodejurnal++;
		}
		
		// echo"<pre>";
		// print_r($arrkodejurnal);
		// echo"</pre>";
		
		
		if($tipeunit[$nourut]!=''){
			$notipeunit=0;
			$arrtipeunit=explode(',',$tipeunit[$nourut]);
			foreach($arrtipeunit as $key){
				if($notipeunit>0){
					@$daftartipeunit[$nourut].=",'".trim($key)."'";
				}else{
					@$daftartipeunit[$nourut].="'".trim($key)."'";
				}
				$notipeunit++;
			}
			$wheretipunit=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['pt']."'  and tipe in (".$daftartipeunit[$nourut]."))";
			$wherejurnal=$wheretipunit;
		}
		
		if($nokodejurnal>0){
			$wherekodejurnal.=" and kodejurnal in ('".implode("','",$arrkodejurnal)."')";
		}
		
		$str="select * from ".$dbname.".keu_jurnaldt_vw where 1=1 and periode='".$param['periode']."'  ".$where." and noakun in ('".implode("','",$arrnoakun)."') ".$wherekodejurnal."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align='center'>".$no."</td>";
				$stream.="<td align='left'>".$bar['nojurnal']."</td>";
				$stream.="<td align='left'>".$bar['tanggal']."</td>";
				$stream.="<td align='left'>".$bar['noakun']."</td>";
				$stream.="<td align='left'>".$bar['keterangan']."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['jumlah'])."</td>";
				$stream.="<td align='left'>".$bar['kodeorg']."</td>";
				$stream.="<td align='left'>".$bar['kodekegiatan']." - ".getNamaKeg($bar['kodekegiatan'])."</td>";
				$stream.="<td align='left'>".$bar['kodeasset']."</td>";
				$stream.="<td align='left'>".$bar['kodebarang']."</td>";
				$stream.="<td align='left'>".getKary($bar['nik'])."</td>";
				$stream.="<td align='left'>".$bar['kodecustomer']."</td>";
				$stream.="<td align='left'>".getNamaSupplier($bar['kodesupplier'])."</td>";
				$stream.="<td align='left'>".$bar['nodok']."</td>";
				$stream.="<td align='left'>".$bar['noreferensi']."</td>";
				$stream.="<td align='left'>".$bar['kodevhc']."</td>";
				$stream.="<td align='left'>".getNamaOrg($bar['kodeblok'])."</td>";
				$stream.="<td align='left'>".$bar['kodejurnal']."</td>";
				$stream.="<td align='left'>".$bar['tanggalentry']."</td>";
			$stream.="</tr>";
			$tjumlah+=$bar['jumlah'];
		}	
		
		$str="select * from ".$dbname.".keu_jurnaldetailbyyoh where 1=1 and periode='".$param['periode']."'  ".$where." and noakun in ('".implode("','",$arrnoakun)."')";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align='center'>".$no."</td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'>".$bar['noakun']."</td>";
				$stream.="<td align='left'>".$bar['keterangan']."</td>";
				$stream.="<td align='right'>".hidezerodecimal($bar['jumlah'])."</td>";
				$stream.="<td align='left'>".$bar['kodeorg']."</td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
				$stream.="<td align='left'></td>";
			$stream.="</tr>";
			$tjumlah+=$bar['jumlah'];
		}	
		
		
		$stream.="<tr class=rowcontent>
					<td align=center colspan=5>Total</td>
					<td align=right>".hidezerodecimal($tjumlah)."</td>
					<td align=right colspan=13></td>
				</tr>";
		$stream.="</table>";
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