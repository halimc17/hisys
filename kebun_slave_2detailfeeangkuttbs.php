<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$tipelaporan = checkPostGet('tipelaporan','');

$kebun = checkPostGet('kebun','');
$periode = checkPostGet('periode','');
$tgl1 = checkPostGet('tgl1','');
$tgl2 = checkPostGet('tgl2','');
$penerimafee = checkPostGet('penerimafee','');

switch ($method) {
	case'getlaporan':
		if(substr($tgl1,3,2)!=substr($tgl2,3,2)){
			exit("Warning : Periode harus dalam bulan dan tahun yang sama");
		}
		$where="";
		if($penerimafee!=''){
			$where=" and id='".$penerimafee."'";
		}
	
		$tab="";
		if($tipelaporan=='html'){
			$border=0;
			$vwidth="cellspacing=1 cellpadding=3";
		}elseif($tipelaporan=='pdf'){
			$border=1;
			$vwidth="width=100%  cellspacing=0 cellpadding=3";
		}else{
			$border=1;
			$vwidth="cellspacing=1 cellpadding=3";
		}
		
		$arrdata=array();
		$jnsfee=array();
		$arrtgl=array();
		$arrblok=array();
		$arrspb=array();
		$data=array();
		$datablok=array();
		$datafee=array();
		/* $str="select a.id,a.jenisfee, a.rupiah as rpfee, a.id, a.kgwb, a.potonganrp,a.blok, b.tanggal, a.nospb, a.divisi,a.jenis from ".$dbname.".kebun_rekapangkutantbsdtfee a
		left join kebun_rekapangkutantbsht b on b.nospb=a.nospb
		where b.kodeorg='".$kebun."' and (b.tanggal between '".tanggaldb($tgl1)."' and '".tanggaldb($tgl2)."') ".$where." order by b.tanggal asc"; */
		
		
		/* $res=fetchdata($str);
		foreach($res as $val){
			## GET Rp/Kg
			$strx="select rp from ".$dbname.".kebun_5daftarfee where blok='".$val['blok']."' and id='".$val['id']."' and jenisvhc='".$val['jenis']."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				if($val['jenis']=='FT' || $val['jenis']=='TR'){
					$j=$val['jenisfee']."int";
					$arrblok[$val['tanggal']][$val['nospb']][$val['arrblok']][$j]=$val['rp'];
				}else{
					$arrblok[$val['tanggal']][$val['nospb']][$val['arrblok']][$val['jenisfee']]=$valx['rp'];
				}
			}
			
			if($val['jenisfee']=='transport'){
				## INT -> FT & TR
				$jnsfee[$val['jenisfee']]=$val['jenisfee']." External";
				$jnsfee[$val['jenisfee']."int"]=$val['jenisfee']." Internal";
			}else{
				$jnsfee[$val['jenisfee']]=$val['jenisfee'];				
			}
			$arrblok[$val['blok']]=$val['blok'];
			$feerp[$val['id']][$val['jenisfee']]+=$val['rpfee'];
			$totfeerp[$val['jenisfee']]+=$val['rpfee'];
			$blokk[$val['id']][$val['blok']]=$val['blok'];

			$arrdata[$val['id']]['id']=$val['id'];
			$arrdata[$val['id']]['kgwb']+=$val['kgwb'];
			$arrdata[$val['id']]['rpangkut']+=$val['rupiah'];				
			$arrdata[$val['id']]['rpfee']+=$val['rpfee'];
			$arrdata[$val['id']]['potonganrp']+=$val['potonganrp'];
			
			$data[$val['id']][$val['tanggal']][$val['nospb']][$val['blok']]=$val['blok'];
			
			if($val['jenis']=='FT' || $val['jenis']=='TR'){
				$j=$val['jenisfee']."int";
				$datafee[$val['id']][$val['tanggal']][$val['nospb']][$val['blok']][$j]+=$val['rpfee'];
			}else{
				$datafee[$val['id']][$val['tanggal']][$val['nospb']][$val['blok']][$val['jenisfee']]+=$val['rpfee'];
			}
				
			
			
			##GET FROM PABRIK TIMBANGAN
			#$strx="select notransaksi,nokendaraan,beratbersih,kgpotsortasi from ".$dbname.".pabrik_timbangan where nospb='".$val['nospb']."'";
			$strx="select notiket as notransaksi,nokendaraan,sum(kgwbnetto) as kgwbnetto from ".$dbname.".kebun_spb_vw where nospb='".$val['nospb']."'";
			$resx=fetchdata($strx);
			$arrblok[$val['tanggal']][$val['nospb']][$val['arrblok']]['ticket']=$resx[0]['notransaksi'];
			$arrblok[$val['tanggal']][$val['nospb']][$val['arrblok']]['nokendaraan']=$resx[0]['nokendaraan'];
			$arrblok[$val['tanggal']][$val['nospb']][$val['arrblok']]['nettoawal']=$val['kgwb'];
			$arrblok[$val['tanggal']][$val['nospb']][$val['arrblok']]['nettoakhir']=$resx[0]['kgwbnetto'];
			
			$datablok[$val['tanggal']][$val['nospb']][$val['blok']]['nettoawal']=$val['kgwb'];
			$datablok[$val['tanggal']][$val['nospb']][$val['blok']]['nettoakhir']=$resx[0]['kgwbnetto'];
			$datablok[$val['tanggal']][$val['nospb']][$val['blok']]['ticket']=$resx[0]['notransaksi'];
			$datablok[$val['tanggal']][$val['nospb']][$val['blok']]['nokendaraan']=$resx[0]['nokendaraan'];
			
			
			$arrtgl[$val['tanggal']]=$val['tanggal'];
			$arrspb[$val['tanggal']][$val['nospb']]=$val['nospb'];
			$arrblok[$val['tanggal']][$val['nospb']][$val['arrblok']]['blok']=$val['blok'];
			$arrblok[$val['tanggal']][$val['nospb']][$val['arrblok']]['divisi']=$val['divisi'];
		}
		 */
		$str="select * from ".$dbname.".kebun_rekapangkutantbsdtfee where kodeorg='" . $kebun . "' and (tanggal between '".tanggaldb($tgl1)."' and '".tanggaldb($tgl2)."') ".$where." order by tanggal asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$data[$val['id']][$val['tanggal']][$val['nospb']][$val['blok']]=$val['blok'];
			
			if(($val['jenis']=='FT' || $val['jenis']=='TR') and $val['jenisfee']=='transport'){
				$j=$val['jenisfee']."int";
				$datafee[$val['id']][$val['tanggal']][$val['nospb']][$val['blok']][$j]+=$val['rupiah'];
			}else{
				$datafee[$val['id']][$val['tanggal']][$val['nospb']][$val['blok']][$val['jenisfee']]+=$val['rupiah'];
			}
			
			if($val['jenisfee']=='transport'){
				## INT -> FT & TR
				$jnsfee[$val['jenisfee']]=$val['jenisfee']." External";
				$jnsfee[$val['jenisfee']."int"]=$val['jenisfee']." Internal";
			}else{
				$jnsfee[$val['jenisfee']]=$val['jenisfee'];				
			}
			
			##GET FROM PABRIK TIMBANGAN
			#$strx="select notransaksi,nokendaraan,beratbersih,kgpotsortasi from ".$dbname.".pabrik_timbangan where nospb='".$val['nospb']."'";
			$strx="select notiket as notransaksi,nokendaraan,sum(kgwbnetto) as sumkgwbnetto, sum(kgwb) as sumkgwb from ".$dbname.".kebun_spb_vw where nospb='".$val['nospb']."'";
			$resx=fetchdata($strx);
			$datablok[$val['tanggal']][$val['nospb']][$val['blok']]['nettoawal']=$val['kgtotal'];
			$datablok[$val['tanggal']][$val['nospb']][$val['blok']]['nettoakhir']=$val['kgwb'];
			$datablok[$val['tanggal']][$val['nospb']][$val['blok']]['ticket']=$resx[0]['notransaksi'];
			$datablok[$val['tanggal']][$val['nospb']][$val['blok']]['nokendaraan']=$resx[0]['nokendaraan'];
			
		}
		
		if(count($jnsfee) > 0){
			$tab.="<table class=sortable  border='".$border."' ".$vwidth.">
				<thead>
				<tr class=rowheader style='text-align:center'>
					<th rowspan=3>".$_SESSION['lang']['nourut']."</th>
					<th rowspan=3>".$_SESSION['lang']['nama']."</th>
					<th rowspan=3>".$_SESSION['lang']['tanggal']."</th>
					<th rowspan=3>".$_SESSION['lang']['ticket']."</th>
					<th rowspan=3>".$_SESSION['lang']['nospb']."</th>
					<th rowspan=3>".$_SESSION['lang']['nokendaraan']."</th>
					<th rowspan=3>".$_SESSION['lang']['blok']."</th>
					<th rowspan=3>".$_SESSION['lang']['divisi']."</th>
					<th colspan=2>Berat TBS</th>
					<th colspan='".@((count($jnsfee)*2)+1)."'>Ongkos Angkut</th>
					<th rowspan=3>".$_SESSION['lang']['keterangan']."</th>
				</tr>
				
				<tr>
					<th rowspan=2>Sebelum Grading (Kg)</th>
					<th rowspan=2>Setelah Grading (Kg)</th>";
				
				foreach ($jnsfee as $key => $fee) {
					$tab.="<th colspan=2>".$fee."</th>";
				}
				$tab.="<th rowspan=2>Total Pembayaran (Rp)</th>
				</tr>
				
				<tr>";

				foreach ($jnsfee as $key => $fee) {
					$tab.="<th>Rp/Kg</th>";
					$tab.="<th>Jumlah Bayar (Rp)</th>";
				}

				$tab.="</tr>
				</thead>
				<tbody>";
				
				$no=0;
				$totall=0;
				$totnettoawal=0;
				$totnettoawalid=array();
				$totnettoakhir=0;
				$totnettoakhirid=array();
				$totfee=array();
				$totfeeid=array();
				$totallid=array();
				/* foreach($arrtgl as $key){
					foreach($arrspb[$key] as $key2){
						foreach($arrblok[$key][$key2] as $key3=>$val){
							$no++;
							$tab.="<tr class=rowcontent style='vertical-align:top'>";
							$tab.="<td align=right>".$no."</td>";
							$tab.="<td align=center style='min-width:70px'>".tanggalnormal($key)."</td>";
							$tab.="<td>".$val['ticket']."</td>";
							$tab.="<td>".$key2."</td>";
							$tab.="<td align=center>".$val['nokendaraan']."</td>";
							$tab.="<td>".$val['blok']."</td>";
							$tab.="<td>".$val['divisi']."</td>";
							$tab.="<td align=right>".hidezerodecimal($val['nettoawal'],2)."</td>";
							$tab.="<td align=right>".hidezerodecimal($val['nettoakhir'],2)."</td>";
							
							$totjlhbyr=0;
							$jlhbyr=0;
							foreach ($jnsfee as $key4 => $fee){
								$jlhbyr=$val[$key4]*$val['nettoakhir'];
								$tab.="<td align=right>".hidezerodecimal($val[$key4],2)."</td>";
								$tab.="<td align=right>".hidezerodecimal($jlhbyr,2)."</td>";
								$totjlhbyr+=$jlhbyr;
								$totfee[$key4]+=$jlhbyr;
							}
							$tab.="<td align=right>".hidezerodecimal($totjlhbyr,2)."</td>";
							$tab.="<td align=right></td>";
							
							$totnettoawal+=$val['nettoawal'];
							$totnettoakhir+=$val['nettoakhir'];
							$totall+=$totjlhbyr;
							
							$tab.="</tr>";
						}
					}
				}
				
				$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
					<td colspan=7 align=center>T O T A L</td>";
				$tab.="<td align=right>".hidezerodecimal($totnettoawal,2)."</td>";
				$tab.="<td align=right>".hidezerodecimal($totnettoakhir,2)."</td>";
					
				foreach ($jnsfee as $key4 => $fee) {
					$tab.="<td align=right></td>";
					$tab.="<td align=right>".hidezerodecimal($totfee[$key4],2)."</td>";
				}

					$tab.="<td align=right>".hidezerodecimal($totall,0)."</td>
					<td></td>
				</tr>";
				 */
				$no=0;
				$optnmfee=makeOption($dbname,'kebun_5namafee','id,nama');
				foreach($data as $idpenerima => $vtgl){
					foreach($vtgl as $tanggal => $vspb){
						foreach($vspb as $nospb => $vblok){
							foreach($vblok as $blok){
								$no++;
								$tab.="<tr class=rowcontent style='vertical-align:top'>";
								$tab.="<td align=right>".$no."</td>";
								$tab.="<td align=left>".$optnmfee[$idpenerima]."</td>";
								$tab.="<td align=center style='min-width:70px'>".tanggalnormal($tanggal)."</td>";
								$tab.="<td>".$datablok[$tanggal][$nospb][$blok]['ticket']."</td>";
								$tab.="<td>".$nospb."</td>";
								$tab.="<td>".$datablok[$tanggal][$nospb][$blok]['nokendaraan']."</td>";
								$tab.="<td>".$blok."</td>";
								$tab.="<td>".substr($blok,0,6)."</td>";
								$tab.="<td align=right>".hidezerodecimal($datablok[$tanggal][$nospb][$blok]['nettoawal'],2)."</td>";
								$tab.="<td align=right>".hidezerodecimal($datablok[$tanggal][$nospb][$blok]['nettoakhir'],2)."</td>";
								$totjlhbyr=0;
								foreach ($jnsfee as $key4 => $fee) {
									$tab.="<td align=right>".hidezerodecimal($datafee[$idpenerima][$tanggal][$nospb][$blok][$key4]/$datablok[$tanggal][$nospb][$blok]['nettoakhir'],2)."</td>";
									$tab.="<td align=right>".hidezerodecimal($datafee[$idpenerima][$tanggal][$nospb][$blok][$key4],2)."</td>";
									$totjlhbyr+=$datafee[$idpenerima][$tanggal][$nospb][$blok][$key4];
									$totfee[$key4]+=$datafee[$idpenerima][$tanggal][$nospb][$blok][$key4];
									$totfeeid[$key4][$idpenerima]+=$datafee[$idpenerima][$tanggal][$nospb][$blok][$key4];
								}
								$tab.="<td align=right>".hidezerodecimal($totjlhbyr,2)."</td>";
								$tab.="<td align=right></td>";
								
								$totnettoawal+=$datablok[$tanggal][$nospb][$blok]['nettoawal'];
								$totnettoawalid[$idpenerima]+=$datablok[$tanggal][$nospb][$blok]['nettoawal'];
								$totnettoakhir+=$datablok[$tanggal][$nospb][$blok]['nettoakhir'];
								$totnettoakhirid[$idpenerima]+=$datablok[$tanggal][$nospb][$blok]['nettoakhir'];
								$totall+=$totjlhbyr;
								$totallid[$idpenerima]+=$totjlhbyr;
								
								$tab.="</tr>";
							}
						}						
					}
					$tab.="<tr class=rowcontent style='background-color:cyan;font-weight:bold'>
					<td colspan=8 align=center>SUB TOTAL ".$optnmfee[$idpenerima]."</td>";
					$tab.="<td align=right>".hidezerodecimal($totnettoawalid[$idpenerima],2)."</td>";
					$tab.="<td align=right>".hidezerodecimal($totnettoakhirid[$idpenerima],2)."</td>";
						
					foreach ($jnsfee as $key4 => $fee) {
						$tab.="<td align=right></td>";
						$tab.="<td align=right>".hidezerodecimal($totfeeid[$key4][$idpenerima],2)."</td>";
					}

						$tab.="<td align=right>".hidezerodecimal($totallid[$idpenerima],0)."</td>
						<td></td>
					</tr>";
					
				}
				
				$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
					<td colspan=8 align=center>T O T A L</td>";
				$tab.="<td align=right>".hidezerodecimal($totnettoawal,2)."</td>";
				$tab.="<td align=right>".hidezerodecimal($totnettoakhir,2)."</td>";
					
				foreach ($jnsfee as $key4 => $fee) {
					$tab.="<td align=right></td>";
					$tab.="<td align=right>".hidezerodecimal($totfee[$key4],2)."</td>";
				}

					$tab.="<td align=right>".hidezerodecimal($totall,0)."</td>
					<td></td>
				</tr>";
				
				// foreach($arrdata as $key=>$val){
					// $no++;
					// $optnmfee=makeOption($dbname,'kebun_5namafee','id,nama');
					// $tab.="<tr class=rowcontent style='vertical-align:top'>";
					// $tab.="<td align=center>".$no."</td>";
					// $tab.="<td>".$optnmfee[$val['id']]."</td>";
					// $tab.="<td align=right>".hidezerodecimal($val['kgwb'],0)."</td>";

					// foreach ($jnsfee as $key => $fee) {
						// $tab.="<td align=right>".hidezerodecimal($feerp[$val['id']][$fee])."</td>";
					// }
					
					// $tab.="<td align=right>";
					// foreach ($arrblok as $keyblok => $valblok) {
						// if($blokk[$val['id']][$valblok]!=''){

						
						// $tab.= $blokk[$val['id']][$valblok]."</br>";
					// }
					// }
					// $tab.="</td>";
					// $tab.="<td align=right>".hidezerodecimal($val['rpfee'],0)."</td>";
					// $tab.="<td align=right>".hidezerodecimal($val['potonganrp'],0)."</td>";
					// $tab.="<td align=right>".hidezerodecimal($val['rpfee']-$val['potonganrp'],0)."</td>";
					// $tab.="<td></td>";
					// $tab.="</tr>";
					
					// $totrpfee+=$val['rpfee'];
					// $totallrpfee+=$val['rpfee']-$val['potonganrp'];
					// $totkgwb+=$val['kgwb'];
					// $totpotonganrp+=$val['potonganrp'];
					
				// }
				
				
				
				
			$tab.="</tbody>
			</table>";
			
			if($tipelaporan=='html'){
				echo $tab;
			}elseif($tipelaporan=='pdf'){
				$arrHead = setheadreport('',$kebun);
				$path=$arrHead['logo'];
				$header="<div>
					<table cellspacing=0 border=0 width=100% align=center>
						<tr>
							<td rowspan=3 style='font-weight:bold;width:100px'><img src='".$path."' height='80' /></td>
							<td style=font-weight:bold;>".$arrHead['nama']."</td>
						</tr>
						<tr>
							<td style=font-weight:bold;>".$arrHead['alamat']."</td>
						</tr>
						<tr>
							<td style=font-weight:bold;>".$arrHead['telepon']."</td>
						</tr>
					</table>
				<hr>
				<table cellspacing=0 border=0 width=100% style='text-align:center'>
					<tr>
						<td style=font-weight:bold;>DAFTAR SLIP GAJI FEE ANGKUT TBS</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>Tanggal : ".$tgl1." s/d ".$tgl2."</td>
					</tr>
				</table>";
				
				$footer="<br><table cellspacing=0 border=0 width=100% style='font-weight:bold;text-align:center'>
					<tr>
						<td>Disetujui Oleh</td>
						<td>Diketahui Oleh</td>
						<td>Diperiksa Oleh</td>
						<td>Dibuat Oleh</td>
					</tr>
					<tr>
						<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
						<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
						<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
						<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					</tr>
				</table>";
				
				$hasil=$header;
				$hasil.=$tab;
				$hasil.=$footer;
				$dompdf = new Dompdf();
				$dompdf->loadHtml($hasil);
				$dompdf->setPaper('A4', 'portrait');
				$dompdf->render();
				$dompdf->stream("DAFTAR SLIP GAJI PREMI ANGKUT TBS(FEE)", array("Attachment" => false));
			}else{
				$titlelaporan="DAFTAR SLIP GAJI PREMI ANGKUT TBS";
				if($handle = opendir('tempExcel')){
					while(false !== ($file = readdir($handle))){
						if($file != "." && $file != ".." && $file != "index.html"){
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/".$titlelaporan.".xls",'w');
				if(!fwrite($handle, $tab)){
					echo "<script language=javascript1.2>
						parent.window.alert('Cant convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
						window.location='tempExcel/".$titlelaporan.".xls';
						</script>";
				}
				closedir($handle); 
			}
		}else{
			echo $_SESSION['lang']['datanotfound'];
		}
	break;
}
?>
