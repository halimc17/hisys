<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses=checkPostGet('proses','');
$type=checkPostGet('type','');

$kdpabrik=checkPostGet('kdpabrik','');
$kdbrg=checkPostGet('kdbrg','');
$cust=checkPostGet('cust','');

$tgltrans=checkPostGet('tgltrans','');
$tgltrans2=checkPostGet('tgltrans2','');
$nokontrak=checkPostGet('nokontrak','');
$tgltrans2=tanggalsystemn($tgltrans2);
$tgltrans=explode('-',$tgltrans);
$tgltrans = $tgltrans[2]."-".$tgltrans[1]."-".$tgltrans[0];
$tgl=$tgltrans;
$tgl2=$tgltrans2;
$tglawal=checkPostGet('tglawal','');
$tglakhir=checkPostGet('tglakhir','');

# Make Option
$nmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$satuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$nmsup = makeOption($dbname,"log_5supplier",'supplierid,namasupplier');
$nmcus = makeOption($dbname,"pmn_4customer",'kodecustomer,namacustomer');
$nmorg = makeOption($dbname,"organisasi",'kodeorganisasi,namaorganisasi');
$qtyktr = makeOption($dbname,"pmn_suratperintahpengiriman",'nokontrak,qty');
switch($proses)
{
	case'preview1':
		$result = "";

		# Filter
		$no = 0;
		$totberatmasuk = 0;
		$totberatkeluar = 0;
		$totberatbersih = 0;
		$totkgpotsortasi = 0;
		
		if($nokontrak!=''){
			$where.=" and nokontrak like '%".$nokontrak."%'";
		}

		if($cust!=''){
			$where.=" and (kodeorg='".$cust."' or kodecustomer='".$cust."' or kodesupplier='".$cust."')";
		}

		if($kdbrg!=''){
			$where.=" and kodebarang like '".$kdbrg."%'";
		}
		# End Filter

		# SQL
		$sql = "select * from ".$dbname.".pabrik_timbangan where left(tanggal,10) between '".$tgltrans."' and '".$tgltrans2."' and millcode='".$kdpabrik."' ".$where." order by kodesupplier asc";
		$res = fetchData($sql);
		foreach($res as $row):
			if($row['wbcond'] == 'Return'){
				$row['beratbersih'] = "-".$row['beratbersih'];
			}
			# Per Komoditi
			$komoditi[$row['kodebarang']] = $row['kodebarang'];
			if($row['wbcond'] != 'langsirgudang'){
				$qtykomoditi[$row['kodebarang']] += $row['beratbersih'];
			}
			$qtykomoditi1[$row['kodebarang']] += ($row['beratbersih']+$row['kgpotsortasi']);
			$sortasi[$row['kodebarang']] += ($row['kgpotsortasi']);
			$jumlahkomoditi[$row['kodebarang']] = count($row['kodebarang']);
			# Rekap Per Komoditi per Kontrak
			$kontrakjual[$row['kodebarang']][$row['nokontrak']]=$row['nokontrak'];
			if(substr($row['tanggal'],0,10)==$tgltrans2){
				$tonhi[$row['nokontrak']]+= $row['beratbersih'];
				$rithi[$row['nokontrak']]+=1;
			}

			if($row['wbcond'] != 'langsirgudang'){
				# Per Supplier
				# Vendor
				if($row['kodesupplier']!='') {
					$komoditisup[$row['kodesupplier']][$row['kodebarang']] = $row['kodebarang'];
					$qtykomoditisup[$row['kodesupplier']][$row['kodebarang']] += $row['beratbersih'];
					$qtykomoditisup1[$row['kodesupplier']][$row['kodebarang']] += ($row['beratbersih']+$row['kgpotsortasi']);
					$sortasisup[$row['kodesupplier']][$row['kodebarang']] += $row['kgpotsortasi'];
				}
				# Customer
				if($row['kodecustomer']!='') {
					$komoditisup[$row['kodecustomer']][$row['kodebarang']] = $row['kodebarang'];
					$qtykomoditisup[$row['kodecustomer']][$row['kodebarang']] += $row['beratbersih'];
					$qtykomoditisup1[$row['kodecustomer']][$row['kodebarang']] += ($row['beratbersih']+$row['kgpotsortasi']);
					$sortasisup[$row['kodecustomer']][$row['kodebarang']] += $row['kgpotsortasi'];
				}
				# Inti
				if($row['kodeorg']!='') {
					$komoditisup[$row['kodeorg']][$row['kodebarang']] = $row['kodebarang'];
					$qtykomoditisup[$row['kodeorg']][$row['kodebarang']] += $row['beratbersih'];
					$qtykomoditisup1[$row['kodeorg']][$row['kodebarang']] += ($row['beratbersih']+$row['kgpotsortasi']);
					$sortasisup[$row['kodeorg']][$row['kodebarang']] += $row['kgpotsortasi'];
				}
				# Per Tanggal
				# Vendor
				if($row['kodesupplier']!='') {
					$komoditisuptgl[$row['kodesupplier']][$row['kodebarang']][substr($row['tanggal'],0,10)] = $row['kodebarang'];
					$qtykomoditisuptgl[$row['kodesupplier']][$row['kodebarang']][substr($row['tanggal'],0,10)] += $row['beratbersih'];
				}
				# Customer
				if($row['kodecustomer']!='') {
					$komoditisuptgl[$row['kodecustomer']][$row['kodebarang']][substr($row['tanggal'],0,10)] = $row['kodebarang'];
					$qtykomoditisuptgl[$row['kodecustomer']][$row['kodebarang']][substr($row['tanggal'],0,10)] += $row['beratbersih'];
				}
				# Inti
				if($row['kodeorg']!='') {
					$komoditisuptgl[$row['kodeorg']][$row['kodebarang']][substr($row['tanggal'],0,10)] = $row['kodebarang'];
					$qtykomoditisuptgl[$row['kodeorg']][$row['kodebarang']][substr($row['tanggal'],0,10)] += $row['beratbersih'];
				}
			}
		endforeach;

		#==========================================================================================================#
		# REKAP
		#==========================================================================================================#
		# Rekap Per Komoditi
		if($type=='html') {
			$result .= "<fieldset style='width:30%;'>";
				$result .= "<legend><b>Rekap Per Komoditi</b></legend>";
				// $result .= "<p>".number_format($qtykomoditi[$kdbarang])."</p>";
				$result .= "<table cellspacing=1 cellpading=3 class=sortable width=100%>";
					$result .= "<thead>";
						$result .= "<tr class=rowheader>";
							$result .= "<th align=center width=40%>Komoditi</th>";
							if($kdbrg == '400000003'){
								$result .= "<th align=center width=10%>Berat Bersih 1</th>";
								$result .= "<th align=center width=10%>Potongan</th>";
								$result .= "<th align=center width=10%>Berat Bersih 2</th>";
							}else{
								$result .= "<th align=center width=10%>Berat Bersih</th>";
							}
							$result .= "<th align=center width=5%>Satuan</th>";
						$result .= "</tr>";
					$result .= "</thead>";
			
				foreach($komoditi as $kdbarang => $val):
					$result .= "<tr class=rowcontent>";
						$result .= "<td align=left>".$nmbarang[$kdbarang]."</td>";
						if($kdbrg == '400000003'){
							$result .= "<td align=right>".number_format($qtykomoditi1[$kdbarang])."</td>";
							$result .= "<td align=right>".number_format($$sortasi[$kdbarang])."</td>";
							$result .= "<td align=right>".number_format($qtykomoditi[$kdbarang])."</td>";
						}else{
							$result .= "<td align=right>".number_format($qtykomoditi[$kdbarang])."</td>";
						}
						$result .= "<td align=center>".$satuan[$kdbarang]."</td>";
					$result .= "</tr>";
				endforeach;
				
				$result .= "</table>";
			$result .= "</fieldset>";

			# Rekap Per Supplier
			$result .= "<fieldset style='width:30%;margin-top:20px!important;'>";
				$result .= "<legend><b>Rekap Per Supplier Per Komoditi</b></legend>";
				// $result .= "<p>".number_format($qtykomoditi[$kdbarang])."</p>";
				$result .= "<table cellspacing=1 cellpading=3 class=sortable width=100%>";
					$result .= "<thead>";
						$result .= "<tr class=rowheader>";
							$result .= "<th align=center width=40%>Supplier</th>";
							$result .= "<th align=center width=40%>Komoditi</th>";
							if($kdbrg == '400000003'){
								$result .= "<th align=center width=10%>Berat Bersih 1</th>";
								$result .= "<th align=center width=10%>Potongan</th>";
								$result .= "<th align=center width=10%>Berat Bersih 2</th>";
							}else{
								$result .= "<th align=center width=10%>Berat Bersih</th>";
							}
							$result .= "<th align=center width=5%>Satuan</th>";
						$result .= "</tr>";
					$result .= "</thead>";
			
				foreach($komoditisup as $kdsup => $valsup):
					foreach($valsup as $kdbarangsup => $valbrg):
						$namasupplier = '';
						$namacustomer = '';
						$namaorganisasi = '';

						if($nmsup[$kdsup]!='') {
							$namasupplier = $nmsup[$kdsup]; 
						} else if($nmcus[$kdsup]!='') {
							$namacustomer = $nmcus[$kdsup]; 
						} else if($nmorg[$kdsup]!='') {
							$namaorganisasi = $nmorg[$kdsup]; 
						} else {
							$namasupplier = '';
							$namacustomer = '';
							$namaorganisasi = '';
						}

						$namafix = ($namasupplier == '' ? ($namacustomer == '' ? ($namaorganisasi != '' ? $namaorganisasi : '') : $namacustomer) : $namasupplier);

						$result .= "<tr class=rowcontent>";
							$result .= "<td align=left>".$namafix."</td>";
							$result .= "<td align=left>".$nmbarang[$kdbarangsup]."</td>";
							if($kdbrg == '400000003'){
								$result .= "<td align=right>".number_format($qtykomoditisup1[$kdsup][$kdbarangsup])."</td>";
								$result .= "<td align=right>".number_format($sortasisup[$kdsup][$kdbarangsup])."</td>";
								$result .= "<td align=right>".number_format($qtykomoditisup[$kdsup][$kdbarangsup])."</td>";
							}else{
								$result .= "<td align=right>".number_format($qtykomoditisup[$kdsup][$kdbarangsup])."</td>";
							}
							$result .= "<td align=center>".$satuan[$kdbarangsup]."</td>";
						$result .= "</tr>";
					endforeach;
				endforeach;
				
				$result .= "</table>";
			$result .= "</fieldset>";

			# Rekap Per Supplier Per Tanggal
			$result .= "<fieldset style='width:30%;margin-top:20px!important;'>";
				$result .= "<legend><b>Rekap Per Supplier Per Komoditi Per Tanggal</b></legend>";
				// $result .= "<p>".number_format($qtykomoditi[$kdbarang])."</p>";
				$result .= "<table cellspacing=1 cellpading=3 class=sortable width=100%>";
					$result .= "<thead>";
						$result .= "<tr class=rowheader>";
							$result .= "<th align=center width=40%>Supplier</th>";
							$result .= "<th align=center width=40%>Komoditi</th>";
							$result .= "<th align=center width=40%>Tanggal</th>";
							$result .= "<th align=center width=10%>Berat Bersih</th>";
							$result .= "<th align=center width=5%>Satuan</th>";
						$result .= "</tr>";
					$result .= "</thead>";
			
				foreach($komoditisuptgl as $kdsup => $valsup):
					foreach($valsup as $kdbarangsup => $valbrg):
						foreach($valbrg as $tglsup => $valtgl):
							$namasupplier = '';
							$namacustomer = '';
							$namaorganisasi = '';

							if($nmsup[$kdsup]!='') {
								$namasupplier = $nmsup[$kdsup]; 
							} else if($nmcus[$kdsup]!='') {
								$namacustomer = $nmcus[$kdsup]; 
							} else if($nmorg[$kdsup]!='') {
								$namaorganisasi = $nmorg[$kdsup]; 
							} else {
								$namasupplier = '';
								$namacustomer = '';
								$namaorganisasi = '';
							}

							$namafix = ($namasupplier == '' ? ($namacustomer == '' ? ($namaorganisasi != '' ? $namaorganisasi : '') : $namacustomer) : $namasupplier);

							$result .= "<tr class=rowcontent>";
								$result .= "<td align=left>".$namafix."</td>";
								$result .= "<td align=left>".$nmbarang[$kdbarangsup]."</td>";
								$result .= "<td align=left>".$tglsup."</td>";
								$result .= "<td align=right>".number_format($qtykomoditisuptgl[$kdsup][$kdbarangsup][$tglsup])."</td>";
								$result .= "<td align=center>".$satuan[$kdbarangsup]."</td>";
							$result .= "</tr>";
						endforeach;
					endforeach;
				endforeach;
				
				$result .= "</table>";
			$result .= "</fieldset>";
			
			$arrloading = array('40000001','40000002','40000005','40000010');
			if($kdbrg == ''){//Rekap Kalau Seluruhnya
				$result.="<div align=left><fieldset style='width:30%;margin-top:20px!important;'><table><tr>";
				foreach ($arrloading as $brg) {
					$result.="<td><table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0'>
					<thead>
						<tr class=rowheader>
							<th style='text-align:center;' colspan=7><b>Laporan Loading ".getNamaBrg($brg)."</b></th>
						</tr>
						<tr class=rowheader>
							<th style='text-align:center;' colspan=3>".hari($tgl2,'ID')."</th>
							<th style='text-align:center;' colspan=2>".tglnmbln($tgl2,'I','short')."</th>
							<th style='text-align:center;' colspan=2>".date('H:i')."</th>
						</tr>
						<tr class=rowheader>
							<th style='text-align:center;' rowspan=2>No. Kontrak</th>
							<th style='text-align:center;' rowspan=2>Total Kontrak</th>
							<th style='text-align:center;' rowspan=2>Sisa Kontrak</th>
							<th style='text-align:center;' colspan=2>HI</th>
							<th style='text-align:center;' colspan=2>SDHI</th>
						</tr>
						<tr class=rowheader>
							<th style='text-align:center;'>Rit</th>
							<th style='text-align:center;'>Tonase</th>
							<th style='text-align:center;'>Rit</th>
							<th style='text-align:center;'>Tonase</th>
						</tr>
					</thead>
					<tbody>";$sisaktr=array();
					if(isset($kontrakjual[$brg])){
						foreach ($kontrakjual[$brg] as $ktr) {
							//ambil sdhi
							$sql = "select * from ".$dbname.".pabrik_timbangan where left(tanggal,10) >= '".$tgltrans."' and left(tanggal,10) <= '".$tgltrans2."' and millcode='".$kdpabrik."' ".$where." order by kodesupplier asc";
							$hsl = fetchData($sql);
							foreach ($hsl as $row) {
								if ($row['wbcond']=='Return') {
									$row['beratbersih']="-".$row['beratbersih'];
								}else{
									$row['beratbersih']=$row['beratbersih'];
								}
								$tonasesdhi[$row['nokontrak']]+= $row['beratbersih'];
								if($row['wbcond']=='Normal'){
									$ritsdhi[$row['nokontrak']]+=1;
								}
							}

							$sisaktr[$ktr]=$qtyktr[$ktr]-$tonasesdhi[$ktr];
							if($sisaktr[$ktr] < 0){$sisaktr[$ktr] =0;}
							$result.="<tr class=rowcontent>
							<td align=center>".($ktr == '' ? 'Langsir Gudang' : $ktr)."</td>
							<td align=center>".number_format($qtyktr[$ktr])."</td>
							<td align=center>".number_format($sisaktr[$ktr])."</td>
							<td align=center>".number_format($rithi[$ktr])."</td>
							<td align=center>".number_format($tonhi[$ktr])."</td>
							<td align=center>".number_format($ritsdhi[$ktr])."</td>
							<td align=center>".number_format($tonasesdhi[$ktr])."</td>
							</tr>";
						}
					}else{
							$result.="<tr class=rowcontent><td align=center colspan=7 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
					}
					$result.="
					</tbody>
					</table></td>";
				}
				$result.="</tr></table></fieldset></div>";
			}else{
				$result.="<fieldset style='width:30%;margin-top:20px!important;'><table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0'>
					<thead>
						<tr class=rowheader>
							<th style='text-align:center;' colspan=7><b>Laporan Loading ".getNamaBrg($kdbrg)."</b></th>
						</tr>
						<tr class=rowheader>
							<th style='text-align:center;' colspan=3>".hari($tgl2,'ID')."</th>
							<th style='text-align:center;' colspan=2>".tglnmbln($tgl2,'I','short')."</th>
							<th style='text-align:center;' colspan=2>".date('H:i')."</th>
						</tr>
						<tr class=rowheader>
							<th style='text-align:center;' rowspan=2>No. Kontrak</th>
							<th style='text-align:center;' rowspan=2>Total Kontrak</th>
							<th style='text-align:center;' rowspan=2>Sisa Kontrak</th>
							<th style='text-align:center;' colspan=2>HI</th>
							<th style='text-align:center;' colspan=2>SDHI</th>
						</tr>
						<tr class=rowheader>
							<th style='text-align:center;'>Rit</th>
							<th style='text-align:center;'>Tonase</th>
							<th style='text-align:center;'>Rit</th>
							<th style='text-align:center;'>Tonase</th>
						</tr>
					</thead>
					<tbody>";$sisaktr=array();
					if(isset($kontrakjual[$kdbrg])){
						//ambil sdhi
						$sql = "select * from ".$dbname.".pabrik_timbangan where left(tanggal,10) >= '".$tgltrans."' and left(tanggal,10) <= '".$tgltrans2."' and millcode='".$kdpabrik."' ".$where." order by kodesupplier asc";
						$hsl = fetchData($sql);
						foreach ($hsl as $row) {
							if ($row['wbcond']=='Return') {
								$row['beratbersih']="-".$row['beratbersih'];
							}else{
								$row['beratbersih']=$row['beratbersih'];
							}
							$tonasesdhi[$row['nokontrak']]+= $row['beratbersih'];
							if($row['wbcond']=='Normal'){
								$ritsdhi[$row['nokontrak']]+=1;
							}
						}
						foreach ($kontrakjual[$kdbrg] as $ktr) {

							$sisaktr[$ktr]=$qtyktr[$ktr]-$tonasesdhi[$ktr];
							if($sisaktr[$ktr] < 0){$sisaktr[$ktr] =0;}
							$result.="<tr class=rowcontent>
							<td align=center>".($ktr == '' ? 'Langsir Gudang' : $ktr)."</td>
							<td align=center>".number_format($qtyktr[$ktr])."</td>
							<td align=center>".number_format($sisaktr[$ktr])."</td>
							<td align=center>".number_format($rithi[$ktr])."</td>
							<td align=center>".number_format($tonhi[$ktr])."</td>
							<td align=center>".number_format($ritsdhi[$ktr])."</td>
							<td align=center>".number_format($tonasesdhi[$ktr])."</td>
							</tr>";
						}
					}else{
							$result.="<tr class=rowcontent><td align=center colspan=7 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
					}
					$result.="
					</tbody>
					</table></fieldset>";
			}
		}

		#==========================================================================================================#
		# END REKAP
		#==========================================================================================================#



        $optNamaPabrik = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kdpabrik."'");
		$result .= "<div></div>";
		if($type=='pdf')
		{
            if($kdbrg!=''){
                $scale = '0.80';
            }else{
                $scale = '0.73';
            }
			$border = 1;
            $whrsize="style='font-size:11px; border-collapse: collapse;transform: scale(".$scale."); transform-origin: top left;'";
			$result.="<table cellspacing=0 border='0' class=sortable align=center>
				<tr>
					<td style='font-weight:bold;text-align:center;font-size:27px'>Laporan Timbangan</td>
				</tr>
				<tr>
					<td style='text-align:center'>Pabrik : ".$optNamaPabrik[$kdpabrik]."</td>
				</tr>
				<tr>
					<td style='text-align:center'>Tanggal : ".tglnmbln($tgltrans,'I','long')." s.d ".tglnmbln($tgltrans2,'I','long')."</td>
				</tr>
			</table>";
		}else if($type=='html'){
			$border = 0;
        }
		else
		{
			$border = 1;$whrsize="style='font-size:11px'";
			$result.="<table cellspacing=1 border='0' class=sortable>
				<tr>
					<td colspan=22 style='font-weight:bold;text-align:center;font-size:15px'>Laporan Timbangan</td>
				</tr>
				<tr>
					<td colspan=22 style='text-align:center'>Pabrik : ".$optNamaPabrik[$kdpabrik]."</td>
				</tr>
				<tr>
					<td colspan=22 style='text-align:center'>Tanggal : ".tglnmbln($tgltrans,'I','long')." s.d ".tglnmbln($tgltrans2,'I','long')."</td>
				</tr>
			</table>";
		}
		$result.="<div class='table-scroll' style='height:60vh;margin-top:20px'>
			<table cellpadding=1 cellspacing=1 border='".$border."' class=sortable ".$whrsize." >
				<thead class=rowheader>
				<tr>
					<th style='text-align:center'>No.</th>
					
					<th style='text-align:center'>".$_SESSION['lang']['tanggal']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['namabarang']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['noTiket']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['NoKontrak']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['nodo']." / " .$_SESSION['lang']['nospb']."</th>
					<th style='text-align:center'>No. Refrensi</th>
					<th style='text-align:center' colspan=2>".$_SESSION['lang']['customer']."</th>    ";
					if($kdbrg == '40000003'){
                        $result.="
                        <th style='text-align:center' colspan=2>".$_SESSION['lang']['supplier']."</th>    
                        <th style='text-align:center' colspan=2>".$_SESSION['lang']['unit']."</th>    
                        <th style='text-align:center' colspan=2>".$_SESSION['lang']['divisi']."</th>   
                        <th style='text-align:center'>Kemandoran</th>";
                    }
			        if($kdbrg == '40000001'||$kdbrg == '40000002'){
						$result.="<th style='text-align:center'>".$_SESSION['lang']['transportir']."</th>";
					}
					$result.="
					<th style='text-align:center'>".$_SESSION['lang']['kodenopol']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['sopir']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['beratMasuk']."<br> (Kg)</th>
					<th style='text-align:center'>".$_SESSION['lang']['beratKeluar']."<br> (Kg)</th>
					<th style='text-align:center'>".$_SESSION['lang']['beratBersih']."<br> I (Kg)</th>
					<th style='text-align:center'>".$_SESSION['lang']['potongankg']."<br></th>
					<th style='text-align:center'>".$_SESSION['lang']['beratBersih']."<br> II (Kg)</th>
					<th style='text-align:center'>".$_SESSION['lang']['jammasuk']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['jamkeluar']."</th>";
			        if($kdbrg == '40000001'||$kdbrg == '40000002'){
						$result.="<th style='text-align:center'>".$_SESSION['lang']['cpoffa']."</th>";
						$result.="<th style='text-align:center'>".$_SESSION['lang']['kadarair']."</th>";
						$result.="<th style='text-align:center'>".$_SESSION['lang']['kotoran']."</th>";
					}
					$result.="
				</tr>
				</thead>
				<tbody>";
		
		// if($nokontrak != ''){
			// $where .= " AND nokontrak = '".$nokontrak."'";
		// }

		$str="select * from ".$dbname.".pabrik_timbangan where left(tanggal,10) between '".$tgltrans."' and '".$tgltrans2."' and millcode='".$kdpabrik."' ".$where." order by jammasuk";
		// echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=$res->rowCount();
		
		if($numrows <= 0){
			$result.="<tr class=rowcontent><td colspan=25 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			while($bar=$res->fetch()){
				$no+=1;
				
				$optNamaSupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['kodesupplier']."'");
				$optnmcus = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar['kodecustomer']."'");
				$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
				$optNamaUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				$optNamaDivisi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['divcode']."'");
				$bgcolor='';
				$beratangkut = (($bar['beratmasuk']-$bar['beratkeluar'])<0?($bar['beratmasuk']-$bar['beratkeluar'])*-1 : ($bar['beratmasuk']-$bar['beratkeluar']));
				if($bar['wbcond'] == 'Return'){
					$beratangkut = $bar['beratkeluar']-$bar['beratmasuk'];
					$bar['beratbersih'] = "-".$bar['beratbersih'];
				}
				$result.="<tr class=rowcontent style='background-color:".$bgcolor."'>
					<td style='text-align:center'>".$no."</td>
					<td style='text-align:center' nowrap>".tanggalnormal(substr($bar['tanggal'], 0,10))."</td>
					<td nowrap>".$optNamaBarang[$bar['kodebarang']]."</td>
					<td style='text-align:center'>".$bar['notransaksi']."</td>
					<td style='text-align:center'>".$bar['nokontrak']."</td>
					<td style='text-align:center'>".(($bar['nodo'] == '' ? $bar['nosipb'] : $bar['nodo']))."</td>
					<td style='text-align:center'>".$bar['norefrensi']."</td>
					<td style='text-align:center'>".$bar['kodecustomer']."</td>
					<td style='text-align:center'>".$optnmcus[$bar['kodecustomer']]."</td>";
					if($kdbrg == '40000003'){
                        $result.="
                        <td style='text-align:center'>".$bar['kodesupplier']."</td>
                        <td>".$optNamaSupplier[$bar['kodesupplier']]."</td>
                        <td style='text-align:center'>".$bar['kodeorg']."</td>
                        <td style='text-align:center'>".$optNamaUnit[$bar['kodeorg']]."</td>
                        
                        <td style='text-align:center'>".$bar['divcode']."</td>
                        <td style='text-align:center'>".$optNamaDivisi[$bar['divcode']]."</td>
                        <td style='text-align:center'>".$bar['kemandoran']."</td>";
                    }
			        if($kdbrg == '40000001'||$kdbrg == '40000002'){
						$result.="<td style='text-align:center'>".getNamaSupplier($bar['trpcode'])."</td>";
					}
					$result.="
					<td style='text-align:center'>".$bar['nokendaraan']."</td>
					<td>".$bar['supir']."</td>";
					
					// if($optNamaSupplier[$bar['kodecustomer']]==''){
						// $result.="<td>".$bar['namatransportir']."</td>";
					// }else{						
						// $result.="<td>".$optNamaSupplier[$bar['kodecustomer']]."</td>";
					// }
					$result.="<td style='text-align:right'>".number_format($bar['beratmasuk'],2)."</td>
					<td style='text-align:right'>".number_format($bar['beratkeluar'],2)."</td>
					<td style='text-align:right'>".number_format($beratangkut,2)."</td>
					<td style='text-align:right'>".number_format($bar['kgpotsortasi'],2)."</td>
					<td style='text-align:right'>".number_format($bar['beratbersih'],2)."</td>
					<td style='text-align:center'>".$bar['jammasuk']."</td>
					<td style='text-align:center'>".$bar['jamkeluar']."</td>";
			        if($kdbrg == '40000001'||$kdbrg == '40000002'){
                        $result.="<td style='text-align:center'>".$bar['bps']."</td>
                                <td style='text-align:center'>".$bar['moist']."</td>
                                <td style='text-align:center'>".$bar['dirt']."</td>";
					}
					$result.="
				</tr>";
				$totberatmasuk = $totberatmasuk + $bar['beratmasuk'];
				$totberatkeluar = $totberatkeluar + $bar['beratkeluar'];
				$totberatbersih = $totberatbersih + $bar['beratbersih'];
				$totkgpotsortasi = $totkgpotsortasi + $bar['kgpotsortasi'];
				$totberatangkut +=$beratangkut;
			}
			if($kdbrg == '40000001'||$kdbrg == '40000002'){
				$col='12';$colttl='5';
			}elseif($kdbrg == '40000004' || $kdbrg == ''){
				$col='11';$colttl='2';
            }else{
				$col='18';$colttl='2';
			}
			$result.="<tr class=rowcontent>
				<td colspan=".$col." style='font-weight:bold;text-align:center'>".$_SESSION['lang']['total']."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totberatmasuk,2)."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totberatkeluar,2)."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totberatangkut,2)."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totkgpotsortasi,2)."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totberatbersih,2)."</td>
				<td colspan=".$colttl."></td>
			</tr>";
		}
		
		if($type=='html')
		{
			echo $result;
		}else if($type=='pdf'){
            $dompdf = new Dompdf();
            $dompdf->loadHtml($result);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("Laporan_Timbangan",array("Attachment"=>0));
        }
		else
		{
			$result.="</table></div>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];
			$nop_="Laporan_Timbangan_".$tgltrans."_s.d_".$tgltrans2;
			if(strlen($result)>0)
			{
				if ($handle = opendir('tempExcel')) 
				{
					while (false !== ($file = readdir($handle))) 
					{
						if ($file != "." && $file != ".." && $file != "index.html") 
						{
							@unlink('tempExcel/'.$file);
						}
					}
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$result))
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
		}
	break;	
	
	case'preview2':
		$arrRange = rangeTanggal($tglawal,$tglakhir);
		$tglawal=explode('-',$tglawal);
		$tglakhir=explode('-',$tglakhir);
		// $tgltrans = $tgltrans[2]."-".$tgltrans[1]."-".$tgltrans[0];
		
		if($tglawal[1] != $tglakhir[1])
		{
			exit("warning : Periode bulan awal dan akhir harus sama");
		}
		
		if($tglawal[0] > $tglakhir[0])
		{
			exit("warning : Tangal awal harus lebih kecil dari tanggal akhir");
		}
		
		$tglawal = ($tglawal[2]."-".$tglawal[1]."-".$tglawal[0]);
		$tglakhir = ($tglakhir[2]."-".$tglakhir[1]."-".$tglakhir[0]);
		
		$result="";
		
		$optNamaPabrik = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kdpabrik."'");
		$optNamaProduct = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
		if($type=='html')
		{
			$border = 0;
		}
		else
		{
			$border = 1;
			$result.="<table cellspacing=1 border='0' class=sortable>
				<tr>
					<td colspan=19 style='font-weight:bold;text-align:center'>PMKS v BULKING</td>
				</tr>
				<tr>
					<td colspan=19 style='text-align:center'>Pabrik : ".$optNamaPabrik[$kdpabrik]."</td>
				</tr>
				<tr>
					<td colspan=19 style='text-align:center'>Product : ".$optNamaProduct[$kdbrg]."</td>
				</tr>
				<tr>
					<td colspan=19 style='text-align:center'>Periode : ".tanggalnormal($tglawal)." s/d ".tanggalnormal($tglakhir)."</td>
				</tr>
			</table>";
		}
		$result.="<div class='table-scroll'>
			<table cellspacing=1 border='".$border."' class=sortable>
				<thead class=rowheader>
				<tr>
					<th rowspan=2 style='text-align:center'>No. DO</th>
					
					<th rowspan=2 style='text-align:center;display:none;'>".$_SESSION['lang']['transportir']."</th>    
					<th rowspan=2 style='text-align:center;display:none;'>".$_SESSION['lang']['kodenopol']."</th>
					<th rowspan=2 style='text-align:center;display:none;'>".$_SESSION['lang']['sopir']."</th>
					<th rowspan=2 style='text-align:center;min-width:80px;'>Tanggal Muat</th>
					<th rowspan=2 style='text-align:center'>Jam Berangkat dari PMKS</th>
					<th colspan=7 style='text-align:center'>Timbangan PMKS</th>
					<th rowspan=2 style='text-align:center;min-width:80px;'>Tanggal Bongkar</th>
					<th rowspan=2 style='text-align:center'>Jam Datang ke Bulking</th>
					<th colspan=7 style='text-align:center'>Timbangan Bulking</th>
					<th rowspan=2 style='text-align:center'>Varian(kg) di Netto</th>
					<th rowspan=2 style='text-align:center'>Presentase</th>
				</tr>
				<tr>
					<th style='text-align:center'>No Tiket</th>
					<th style='text-align:center'>".$_SESSION['lang']['nosipb']."</th>
					<th style='text-align:center'>Nopol</th>
					<th style='text-align:center'>Driver</th>
					<th style='text-align:center'>Masuk</th>
					<th style='text-align:center'>Keluar</th>
					<th style='text-align:center'>Netto</th>
					<th style='text-align:center'>No Tiket</th>
					<th style='text-align:center'>".$_SESSION['lang']['nosipb']."</th>
					<th style='text-align:center'>Nopol</th>
					<th style='text-align:center'>Driver</th>
					<th style='text-align:center'>Masuk</th>
					<th style='text-align:center'>Keluar</th>
					<th style='text-align:center'>Netto</th>
				</tr>
				</thead>
				<tbody>";
		
		$noref = "";
		
		$str = "select a.norefrensi from ".$dbname.".pabrik_timbangan a
		left join ".$dbname.".pabrik_timbangan b on a.norefrensi = b.notransaksi
		where a.norefrensi!='' and left(a.tanggal,10) between '".$tglawal."' and '".$tglakhir."' and a.kodebarang='".$kdbrg."' and b.millcode='".$kdpabrik."' order by a.norefrensi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			if($noref==$bar['norefrensi'])
			{
				$arrTicket[$bar['norefrensi']]['count'] += 1;
			}
			else
			{
				$arrTicket[$bar['norefrensi']]['count'] = 0;
				$arrRefrensi[] = $bar['norefrensi']; 
			}
			$noref = $bar['norefrensi'];
		}
		
		//GET TICKET FROM PMKS
		$str = "select * from ".$dbname.".pabrik_timbangan where (left(tanggal,10) between '".$tglawal."' and '".$tglakhir."') and kodebarang='".$kdbrg."' and millcode='".$kdpabrik."' order by tanggal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while($bar=$res->fetch())
		{
			$arrticketpmks[$no]['notransaksi'] = $bar['notransaksi'];
			$arrticketpmks[$no]['tanggal'] = substr($bar['tanggal'],0,10);
			$arrticketpmks[$no]['nokendaraan'] = $bar['nokendaraan'];
			$arrticketpmks[$no]['supir'] = $bar['supir'];
			$arrticketpmks[$no]['jamkeluar'] = $bar['jamkeluar'];
			$arrticketpmks[$no]['beratmasuk'] = $bar['beratmasuk'];
			$arrticketpmks[$no]['beratkeluar'] = $bar['beratkeluar'];
			$arrticketpmks[$no]['beratbersih'] = $bar['beratbersih'];
			$arrticketpmks[$no]['nodo'] = $bar['nodo'];
			$arrticketpmks[$no]['nosipb'] = $bar['nosipb'];
			$no++;
		}
		
		if(count($arrticketpmks) <= 0)
		{
			$result.="<tr class=rowcontent><td colspan=21 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}
		else
		{
			$no = 0;
			$tempTanggal = "";
			foreach($arrRange as $key)
			{
				if(isset($arrticketpmks)){
					foreach($arrticketpmks as $key2=>$val2)
					{
						if($val2['tanggal']==$key){
							$countTicket = getCountRows($dbname,'pabrik_timbangan',"norefrensi='".$val2['notransaksi']."'");
							
							if($countTicket <= 1)
							{
								$bongkar = "";
								$rowspan = "";
								$str2 = "select * from ".$dbname.".pabrik_timbangan where norefrensi='".$val2['notransaksi']."'";
								$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
								$res2->setFetchMode(PDO::FETCH_ASSOC);
								$bar2=$res2->fetch();
							}
							else
							{
								$bongkar = "(BongkarMuat)";
								$arrbulking=array();
								$str2 = "select * from ".$dbname.".pabrik_timbangan where norefrensi='".$val2['notransaksi']."'";
								$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
								$res2->setFetchMode(PDO::FETCH_ASSOC);
								while($bar2=$res2->fetch())
								{
									$arrbulking[$nobulking]['notransaksi'] = $bar2['notransaksi'];
									$arrbulking[$nobulking]['tanggal'] = substr($bar2['tanggal'],0,10);
									$arrbulking[$nobulking]['nokendaraan'] = $bar2['nokendaraan'];
									$arrbulking[$nobulking]['supir'] = $bar2['supir'];
									$arrbulking[$nobulking]['jamkeluar'] = $bar2['jamkeluar'];
									$arrbulking[$nobulking]['beratmasuk'] = $bar2['beratmasuk'];
									$arrbulking[$nobulking]['beratkeluar'] = $bar2['beratkeluar'];
									$arrbulking[$nobulking]['beratbersih'] = $bar2['beratbersih'];
									$arrbulking[$nobulking]['tanggal2'] = $bar2['tanggal'];
									$arrbulking[$nobulking]['nosipb'] = $bar2['nosipb'];
									$nobulking++;
								}
								$rowspan = "rowspan=".count($arrbulking);
							}
							
							@$varian = ($bar2['beratbersih'] - $val2['beratbersih']);
							@$persentase = ($varian / $val2['beratbersih']) * 100;
							
							if($tempTanggal != substr($val2['tanggal'],0,10) && $tempTanggal != '')
							{
								$result.="<tr class=rowcontent>
									<td style='text-align:center;font-weight:bold' colspan=9>Total</td>
									<td style='text-align:right'><b>".number_format($berat1)."</b></td>
									<td style='text-align:right' colspan=8></td>
									<td style='text-align:right'><b>".number_format($berat2)."</b></td>
									<td style='text-align:right' colspan=2></td>
								</tr>";
								$result.="<tr class=rowcontent>
									<td colspan=21>&nbsp;</td>
								</tr>";
								$berat1 = 0;
								$berat2 = 0;
							}
							
							$result.="<tr class=rowcontent>
								<td ".$rowspan." style='display:none;'></td>
								<td ".$rowspan." style='text-align:center;display:none;'>".$val2['nokendaraan']."</td>
								<td ".$rowspan." style='display:none;'>".$val2['supir']."</td>
								<td ".$rowspan." style='text-align:center'>".$val2['nodo']."</td>
								<td ".$rowspan." style='text-align:center'>".tanggalnormal(substr($val2['tanggal'],0,10))."</td>
								<td ".$rowspan." style='text-align:center'>".$val2['jamkeluar']."</td>
								<td ".$rowspan." style='text-align:center'>".$val2['notransaksi']."</td>
								
								<td ".$rowspan." style='text-align:center'>".$val2['nosipb']."</td>
								
								<td ".$rowspan." style='text-align:center'>".$val2['nokendaraan']."</td>
								<td ".$rowspan." style='text-align:center'>".$val2['supir']."</td>
								<td ".$rowspan." style='text-align:right'>".number_format($val2['beratmasuk'])."</td>
								<td ".$rowspan." style='text-align:right'>".number_format($val2['beratkeluar'])."</td>
								<td ".$rowspan." style='text-align:right'>".number_format($val2['beratbersih'])."</td>";
							
							if($countTicket <= 1)
							{	
								$result.="<td style='text-align:center'>".tanggalnormal(substr($bar2['tanggal'],0,10))."</td>
									<td style='text-align:center'>".substr($bar2['tanggal'],11,5)."</td>
									<td style='text-align:center'>".$bar2['notransaksi']."<br>".$bongkar."</td>
									<td style='text-align:center'>".$bar2['nosipb']."</td>
									<td style='text-align:center'>".$bar2['nokendaraan']."</td>
									<td style='text-align:center'>".$bar2['supir']."</td>
									<td style='text-align:right'>".number_format($bar2['beratmasuk'])."</td>
									<td style='text-align:right'>".number_format($bar2['beratkeluar'])."</td>
									<td style='text-align:right'>".number_format($bar2['beratbersih'])."</td>
									<td style='text-align:right'>".number_format($varian)."</td>
									<td  style='text-align:center'>".number_format($persentase,2)."</td>";
							}
							else
							{
								$firstrow = 0;
								$beratbersihbulking = 0;
								foreach($arrbulking as $key3=>$val3)
								{
									$beratbersihbulking += $val3['beratbersih'];
								}
								
								@$varian = ($beratbersihbulking - $val2['beratbersih']);
								@$persentase = ($varian / $val2['beratbersih']) * 100;
								
								foreach($arrbulking as $key3=>$val3)
								{
									if($firstrow > 0)
									{
										$result.="<tr class=rowcontent>
											<td style='text-align:center'>".tanggalnormal(substr($val3['tanggal'],0,10))."</td>
											<td style='text-align:center'>".substr($val3['tanggal2'],11,5)."</td>
											<td style='text-align:center'>".$val3['notransaksi']."<br>".$bongkar."</td>
											<td style='text-align:center'>".$val3['nosipb']."</td>
											<td style='text-align:center'>".$val3['nokendaraan']."</td>
											<td style='text-align:center'>".$val3['supir']."</td>
											<td style='text-align:right'>".number_format($val3['beratmasuk'])."</td>
											<td style='text-align:right'>".number_format($val3['beratkeluar'])."</td>
											<td style='text-align:right'>".number_format($val3['beratbersih'])."</td>
											</tr>";
											$berat2 += $val3['beratbersih'];
									}
									else
									{
										$result.="<td style='text-align:center'>".tanggalnormal(substr($val3['tanggal'],0,10))."</td>
											<td style='text-align:center'>".substr($val3['tanggal2'],11,5)."</td>
											<td style='text-align:center'>".$val3['notransaksi']."<br>".$bongkar."</td>
											<td style='text-align:center'>".$val3['nosipb']."</td>
											<td style='text-align:center'>".$val3['nokendaraan']."</td>
											<td style='text-align:center'>".$val3['supir']."</td>
											<td style='text-align:right'>".number_format($val3['beratmasuk'])."</td>
											<td style='text-align:right'>".number_format($val3['beratkeluar'])."</td>
											<td style='text-align:right'>".number_format($val3['beratbersih'])."</td>
											<td rowspan='".$countTicket."' style='text-align:right'>".number_format($varian)."</td>
											<td rowspan='".$countTicket."' style='text-align:center'>".number_format($persentase,2)."</td>";
											$berat2 += $val3['beratbersih'];
									}
									$firstrow++;
								}
								
							}
							$result.="</tr>";
							
							$berat1 += $val2['beratbersih'];
							$berat2 += $bar2['beratbersih'];
							
							$tempTanggal = substr($val2['tanggal'],0,10);
						}
					}
				}
				
				// $no++;
				// $noref = "";
				
				// foreach($arrRefrensi as $key2)
				// {
					// $str = "select * from ".$dbname.".pabrik_timbangan where norefrensi='".$key2."' order by tanggal";
					// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					// $res->setFetchMode(PDO::FETCH_ASSOC);
					// while($bar=$res->fetch())
					// {
						// if($key == substr($bar['tanggal'],0,10))
						// {
							// $countTicket = getCountRows($dbname,'pabrik_timbangan',"norefrensi='".$key2."'");
							// if($countTicket <= 1)
							// {
								// $bongkar = "";
							// }
							// else
							// {
								// $bongkar = "(BongkarMuat)";
							// }
							
							// $str2 = "select * from ".$dbname.".pabrik_timbangan where notransaksi='".$bar['norefrensi']."' LIMIT 1";
							// $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
							// $res2->setFetchMode(PDO::FETCH_ASSOC);
							// $bar2=$res2->fetch();
							
							// @$varian = ($bar['beratbersih'] - $bar2['beratbersih']);
							// @$persentase = ($varian / $bar2['beratbersih']) * 100;
							
							// if($tempTanggal != substr($bar['tanggal'],0,10) && $tempTanggal != '')
							// {
								// $result.="<tr class=rowcontent>
									// <td style='text-align:right' colspan=8></td>
									// <td style='text-align:right'><b>".$berat1."</b></td>
									// <td style='text-align:right' colspan=5></td>
									// <td style='text-align:right'><b>".$berat2."</b></td>
									// <td style='text-align:right' colspan=3></td>
								// </tr>";
								// $berat1 = 0;
								// $berat2 = 0;
							// }
							
							// $result.="<tr class=rowcontent>
								// <td></td>
								// <td style='text-align:center'>".$bar2['nokendaraan']."</td>
								// <td>".$bar2['supir']."</td>
								// <td style='text-align:center'>".tanggalnormal(substr($bar2['tanggal'],0,10))."</td>
								// <td style='text-align:center'>".$bar2['jamkeluar']."</td>
								// <td style='text-align:center'>".$bar2['notransaksi']."</td>
								// <td style='text-align:center'>".$bar2['beratmasuk']."</td>
								// <td style='text-align:center'>".$bar2['beratkeluar']."</td>
								// <td style='text-align:right'>".$bar2['beratbersih']."</td>
								// <td style='text-align:center'>".tanggalnormal($key)."</td>
								// <td style='text-align:center'>".substr($bar['tanggal'],11,5)."</td>
								// <td style='text-align:center'>".$bar['notransaksi']."<br>".$bongkar."</td>
								// <td style='text-align:center'>".$bar['beratmasuk']."</td>
								// <td style='text-align:center'>".$bar['beratkeluar']."</td>
								// <td style='text-align:right'>".$bar['beratbersih']."</td>
								// <td style='text-align:right'>".$varian."</td>
								// <td>".$bar['nosipb']."</td>
								// <td  style='text-align:center'>".number_format($persentase,2)."</td>
							// </tr>";
							// $berat1 += $bar2['beratbersih'];
							// $berat2 += $bar['beratbersih'];
							
							// $tempTanggal = substr($bar['tanggal'],0,10);
						// }
					// }
				// }
			}
			
			
			
			$result.="<tr class=rowcontent>
				<td style='text-align:center;font-weight:bold' colspan=9>Total</td>
				<td style='text-align:right'><b>".number_format($berat1)."</b></td>
				<td style='text-align:right' colspan=8></td>
				<td style='text-align:right'><b>".number_format($berat2)."</b></td>
				<td style='text-align:right' colspan=2></td>
			</tr>";
		}
		
		if($type=='html')
		{
			echo $result;
		}
		else
		{
			$result.="</table></div>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			$nop_="ReportWB";
			if(strlen($result)>0)
			{
				if ($handle = opendir('tempExcel')) 
				{
					while (false !== ($file = readdir($handle))) 
					{
						if ($file != "." && $file != ".." && $file != "index.html") 
						{
							@unlink('tempExcel/'.$file);
						}
					}
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$result))
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
		}
	break;
}
?>