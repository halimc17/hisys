<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method     = checkPostGet('method', '');
$kodeurut   = checkPostGet('kodeurut', '');
$per        = checkPostGet('per', '');
$tipe       = checkPostGet('tipe', '');
$pt         = checkPostGet('pt', '');
$unit       = checkPostGet('unit', '');
$regional   = checkPostGet('regional', '');
$sumber     = checkPostGet('sumber', '');
$kodelaporan='CASH FLOW';


$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmaruskas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	$namamesinlaporan[$bar['nourut']]=$bar['keterangandisplay'];
}

$stream='';

if($regional=='' && $unit=='' && $pt==''){
	$where='';
} else if($regional=='' && $unit=='' && $pt!=''){
	$where="  and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
} else if($regional!='' && $unit=='') {
	$where="  and left(kodeorg,4) in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
} else {
	$where="  and left(kodeorg,4)='".$unit."'";
}


switch($method){
	case'preview':
		switch($sumber){
			case'actual':
				$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
				$stream.="
					<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
					<thead>
							<tr class=rowheader>
								<th align='center' >".$_SESSION['lang']['nourut']."</th>
								<th align='center' >".$_SESSION['lang']['tanggalinput']."</th>
								<th align='center' >".$_SESSION['lang']['tanggal']."<br>".$_SESSION['lang']['novoucher']."</th>
								<th align='center' >".$_SESSION['lang']['noakun']."</th>
								<th align='center' >".$_SESSION['lang']['noakun']."</th>
								<th align='center' >".$_SESSION['lang']['namaakun']."<br>Detail</th>
								<th align='center' >".$_SESSION['lang']['namaakun']."<br>Detail</th>
								<th align='center' >".$_SESSION['lang']['notransaksi']."</th>
								<th align='center' >".$_SESSION['lang']['novoucher']."</th>
								<th align='center' >".$_SESSION['lang']['tipetransaksi']." I</th>
								<th align='center' >".$_SESSION['lang']['tipetransaksi']." II</th>
								<th align='center' >".$_SESSION['lang']['matauang']."</th>
								<th align='center' >".$_SESSION['lang']['namabank']."</th>
								<th align='center' >".$_SESSION['lang']['rekening']."</th>
								
								<th align='center' >".$_SESSION['lang']['noaruskas']."</th>
								<th align='center' >".$_SESSION['lang']['nama']."<br>".$_SESSION['lang']['noaruskas']."</th>
								<th align='center' >".$_SESSION['lang']['jumlah']."</th>
								<th align='center' >".$_SESSION['lang']['kodesupplier']."</th>
								<th align='center' >".$_SESSION['lang']['nik']."</th>
								<th align='center' >".$_SESSION['lang']['keterangan']." I</th>
								<th align='center' >".$_SESSION['lang']['keterangan']." II</th>
								<th align='center' >".$_SESSION['lang']['noinvoice']."</th>
								<th align='center' >".$_SESSION['lang']['nodok']."</th>
								<th align='center' >".$_SESSION['lang']['status']."</th>
							</tr>
						
					</thead>";
				$no=0;
				


				$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$kodeurut."' and namalaporan='".$kodelaporan."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrkodearuskas[$bar['noakun']]=$bar['noakun'];
				}
				
				
				$str="select * from ".$dbname.".keu_kasbankdtht_vw where 1=1 and pembayaran=1 and tanggal like '".$per."%'  ".$where." and noaruskas in ('".implode("','",$arrkodearuskas)."')  ";
				$res=fetchdata($str);
				foreach($res as $bar){
					$bar['jumlah']=$bar['jumlah']*$bar['kurs'];
					$no++;
					$nmka=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['nik']."'");
					$stream.="<tr class=rowcontent>";
						$stream.="<td align='center'>".$no."</td>";
						$stream.="<td align='left'>".tanggalnormal($bar['tanggalinput'])."</td>";
						$stream.="<td align='left'>".tanggalnormal($bar['tanggal'])."</td>";
						$stream.="<td align='left'>".$bar['noakun2a']."</td>";
						$stream.="<td align='left'>".$nmakun[$bar['noakun2a']]."</td>";
						$stream.="<td align='left'>".$bar['noakun']."</td>";
						$stream.="<td align='left'>".$nmakun[$bar['noakun']]."</td>";
						$stream.="<td align='left'>".$bar['notransaksi']."</td>";
						$stream.="<td align='left'>".$bar['novoucher']."</td>";
						$stream.="<td align='center'>".$bar['tipetransaksi']."</td>";
						$stream.="<td align='center'>".$bar['kode']."</td>";
						$stream.="<td align='center'>".$bar['matauang']."</td>";
						$stream.="<td align='left'>".$nmbankrekening[$bar['rekening']]."</td>";
						$stream.="<td align='left'>".$kdrekening[$bar['rekening']]."</td>";
						$stream.="<td align='left'>".$bar['noaruskas']."</td>";
						$stream.="<td align='left'>".$nmaruskas[$bar['noaruskas']]."</td>";
						if($bar['tipetransaksi']=='K'){
							$bar['jumlah']=$bar['jumlah']*-1;
						}
						$stream.="<td align='right'>".number_format($bar['jumlah'],2)."</td>";
						$stream.="<td align='left'>".$nmsupplier[$bar['kodesupplier']]."</td>";
						$stream.="<td align='left'>".$nmka[$bar['nik']]."</td>";
						$stream.="<td align='left'>".$bar['keterangan2']."</td>";
						$stream.="<td align='left'>".$bar['keterangan3']."</td>";
						$stream.="<td align='left'>".$bar['keterangan1']."</td>";
						$stream.="<td align='left'>".$bar['nodok']."</td>";
						$stream.="<td align='left'>".$optposting[$bar['posting']]."</td>";
					$stream.="</tr>";
					$tjumlah+=$bar['jumlah'];
				}	
				
				
				$stream.="<tr class=rowcontent>
							<td align=center colspan=16>Total</td>
							<td align=right>".number_format($tjumlah,2)."</td>
							<td align=right colspan=7></td>
						</tr></table>";
				echo $stream;
			break;
			case'budget':
				$tab="<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
					<thead>
						<tr class=rowheader>
							<th align='center' >".$_SESSION['lang']['nourut']."</th>
							<th align='center' >".$_SESSION['lang']['tipeBudget']."</th>
							<th align='center' >".$_SESSION['lang']['kodebudget']."</th>
							<th align='center' >".$_SESSION['lang']['aruskas']."</th>
							<th align='center' >".$_SESSION['lang']['namaaruskas']."</th>
							<th align='center' >".$_SESSION['lang']['noakun']."</th>
							<th align='center' >".$_SESSION['lang']['namaakun']."</th>
							<th align='center' >".$_SESSION['lang']['kodekegiatan']."</th>
							<th align='center' >".$_SESSION['lang']['namakegiatan']."</th>
							<th align='center' >".$_SESSION['lang']['kodevhc']."</th>
							<th align='center' >".$_SESSION['lang']['nopol']."</th>
							<th align='center' >".$_SESSION['lang']['kodebarang']."</th>
							<th align='center' >".$_SESSION['lang']['namabarang']."</th>
							<th align='center' >".$_SESSION['lang']['rupiah']."</th>
						</tr>
					</thead>";
					
					
				$str="select * from ".$dbname.".keu_5mesinlaporandt_kodejurnal where namalaporan='".$kodelaporan."' and nourut='".$kodeurut."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					if($bar['tipe']=='budget'){		
						if($nouruttempbgt==$bar['nourut']){
							$kodejurnalbgt.=",'".trim($bar['kodejurnal'])."'";
						}else{
							$kodejurnalbgt.="'".trim($bar['kodejurnal'])."'";
						}
						$nouruttempbgt=$bar['nourut'];
					}
					if($bar['tipe']=='realisasi'){		
						if($nouruttempact==$bar['nourut']){
							$kodejurnalact.=",'".trim($bar['kodejurnal'])."'";
						}else{
							$kodejurnalact.="'".trim($bar['kodejurnal'])."'";
						}
						$nouruttempact=$bar['nourut'];
					}
				}
				
				if($kodejurnalbgt!=''){
					$where.=" and kodebudget in (".$kodejurnalbgt.")";			
				}
				
				$namabgt = makeOption($dbname,'bgt_kode','kodebudget,nama');	
				$str="select * from ".$dbname.".bgt_budget_detail where 1=1 ".$where." and tahunbudget='".substr($per,0,4)."' and aruskas in (select noakun from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$kodeurut."' and namalaporan='".$kodelaporan."') order by tipebudget, kodebudget, noakun";
				$res=fetchdata($str);
				foreach($res as $bar){
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align='center'>".$no."</td>";
					$tab.="<td align='center'>".$bar['tipebudget']."</td>";
					$tab.="<td align='left'>".$namabgt[$bar['kodebudget']]."</td>";
					$tab.="<td align='center'>".$bar['aruskas']."</td>";
					$tab.="<td align='left'>".getNamaAruskas($bar['aruskas'])."</td>";
					$tab.="<td align='center'>".$bar['noakun']."</td>";
					$tab.="<td align='left'>".getNamaAkun($bar['noakun'])."</td>";
					$tab.="<td align='center'>".$bar['kegiatan']."</td>";
					$tab.="<td align='left'>".getNamaKeg($bar['kegiatan'])."</td>";
					$tab.="<td align='center'>".$bar['kodevhc']."</td>";
					$tab.="<td align='left'>".getNopol($bar['kodevhc'],'x')."</td>";
					$tab.="<td align='center'>".$bar['kodebarang']."</td>";
					$tab.="<td align='left'>".getNamaBrg($bar['kodebarang'])."</td>";
					$tab.="<td align='right'>".hidezerodecimal($bar['rp'.substr($per,-2)])."</td>";
					
					$tab.="</tr>";
					$total+=$bar['rp'.substr($per,-2)];
				}
				$tab.="<tr class=rowcontent>";
				$tab.="<td align='center' colspan=13>TOTAL</td>";
				$tab.="<td align='right'>".hidezerodecimal($total)."</td>";
				
				$tab.="</tr>";
				echo $tab;
			break;
		}
	break;
	
	
	case'previewsubtotal':
		switch($sumber){
			case'actual':
				$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
			
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
					$res=fetchdata($str);
					foreach($res as $bar){
						$arrkodearuskas[$bar['noakun']]=$bar['noakun'];
					}
					
					$str="select * from ".$dbname.".keu_kasbankdtht_vw where 1=1 and pembayaran=1 and tanggal like '".$per."%'  ".$where." and noaruskas in ('".implode("','",$arrkodearuskas)."')  ";
					//echo $str."<br>";
					$res=fetchdata($str);
					foreach($res as $bar){
						$bar['jumlah']=$bar['jumlah']*$bar['kurs'];
						if($bar['tipetransaksi']=='K'){
							$bar['jumlah']=$bar['jumlah']*-1;
						}
						$dtjumlah[$dtnourut]+=$bar['jumlah'];
					}
				}
				$stream.=" ".$kodeurut." : ".$namamesinlaporan[$kodeurut]."<br>";
				$stream.=" ".$_SESSION['lang']['periode']." : ".$per."<br><br>";
				$stream.="
				<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
				<thead>
					<tr class=rowheader>
						<tr class=rowheader>
							<th align='center'>".$_SESSION['lang']['kode']."</th>
							<th align='center'>".$_SESSION['lang']['nama']."</th>
							<th align='center'>".$_SESSION['lang']['nilai']."</th>
						</tr>
				</thead>";	
				
				foreach($arrnourut as $dtnourut){
					$stream.="<tr class=rowcontent  title='Click untuk melihat detail' style=cursor:pointer onclick=\"detail('".$dtnourut."','".$per."','".$pt."','".$regional."','".$unit."','html','event');\">";
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
			case'budget':
				$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
			
				$str="select * from ".$dbname.".keu_5mesinlaporandt where  namalaporan='".$kodelaporan."' and nourut='".$kodeurut."' ";
				$res=fetchdata($str);
				foreach($res as $bar){
					$noakundisplay=$bar['noakundisplay'];
				}
				$explodenoakundisplay=explode(',',$noakundisplay);
				foreach($explodenoakundisplay as $nourut){
					$arrnourut[$nourut]=$nourut;
				}
				
				$dtjumlah=array();
				foreach($arrnourut as $dtnourut){
					$arrkodearuskas=array();
					$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where nourut='".$dtnourut."' and namalaporan='".$kodelaporan."'";
					// echo $str."<br>";
					$res=fetchdata($str);
					foreach($res as $bar){
						$arrkodearuskas[$bar['noakun']]=$bar['noakun'];
					}
					$kodejurnalbgt="";
					$str="select * from ".$dbname.".keu_5mesinlaporandt_kodejurnal where namalaporan='".$kodelaporan."' and nourut='".$dtnourut."' and tipe='budget'";
					$res=fetchdata($str);
					foreach($res as $bar){
						if($nouruttempbgt==$bar['nourut']){
							$kodejurnalbgt.=",'".trim($bar['kodejurnal'])."'";
						}else{
							$kodejurnalbgt.="'".trim($bar['kodejurnal'])."'";
						}
						$nouruttempbgt=$bar['nourut'];
					}
					$wh="";
					if($kodejurnalbgt!=''){
						$wh=" and kodebudget in (".$kodejurnalbgt.")";			
					}
					$namabgt = makeOption($dbname,'bgt_kode','kodebudget,nama');
					if(!empty($arrkodearuskas)){						
						$str="select * from ".$dbname.".bgt_budget_detail where 1=1 ".$where." ".$wh." and tahunbudget='".substr($per,0,4)."' and aruskas in ('".implode("','",$arrkodearuskas)."') order by tipebudget, kodebudget, noakun";
						$res=fetchdata($str);
						foreach($res as $bar){
							$dtjumlah[$dtnourut]+=$bar['rp'.substr($per,-2)];
						}
					}
				}
				$stream.=" ".$kodeurut." : ".$namamesinlaporan[$kodeurut]."<br>";
				$stream.=" ".$_SESSION['lang']['periode']." : ".$per."<br><br>";
				$stream.="
				<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
				<thead>
					<tr class=rowheader>
						<tr class=rowheader>
							<th align='center'>".$_SESSION['lang']['kode']."</th>
							<th align='center'>".$_SESSION['lang']['nama']."</th>
							<th align='center'>".$_SESSION['lang']['nilai']."</th>
						</tr>
				</thead>";	
				
				foreach($arrnourut as $dtnourut){
					$stream.="<tr class=rowcontent  title='Click untuk melihat detail' style=cursor:pointer onclick=\"detail('".$dtnourut."','".$per."','".$pt."','".$regional."','".$unit."','html','event','budget');\">";
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
	break;
	
	
}












?>