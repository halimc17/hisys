<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method                         = checkPostGet('method','');

$notransaksi = checkPostGet('notransaksi','');





$kodept = checkPostGet('kodept','');
$tanggal1 = tanggalsystemn(checkPostGet('tanggal1',''));	
$tanggal2 = tanggalsystemn(checkPostGet('tanggal2',''));	
$kodebarang = checkPostGet('kodebarang','');
$tipe = checkPostGet('tipe','');



$optbuyer=$optbarang=$opttipe=$optunit=$opttangki="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";



$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
$namaorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PABRIK'");
$nmcustsomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmkapalponton=makeOption($dbname,'pmn_5kapalponton','kode,nama');
$nmfranco=makeOption($dbname,'pmn_5franco','id_franco,franco_name');

// exit("Error:".$method);
switch ($method) {
	
	case'preview':
	
		// $str = "select * from ".$dbname.".pmn_kontrakjual  where tanggalkontrak  between '".$tanggal1."' and '".$tanggal2."' ";
		$str = "select * from ".$dbname.".pmn_kontrakjual  where tanggalkontrak  between '".$tanggal1."' and '".$tanggal2."' and kodept like '".$kodept."%' and kodebarang like '".$kodebarang."%' ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
			$tanggalkontrak[$bar['nokontrak']]=$bar['tanggalkontrak'];
			$penjual[$bar['nokontrak']]=$bar['kodept'];
			$customer[$bar['nokontrak']]=$bar['koderekanan'];
			$komoditi[$bar['nokontrak']]=$bar['kodebarang'];
			$kuantitas[$bar['nokontrak']]=$bar['kuantitaskontrak'];
			$hargasatuan[$bar['nokontrak']]=$bar['hargasatuan'];
			$tipepenjualan[$bar['nokontrak']]=$bar['tipepenjualan'];
			$franco[$bar['nokontrak']]=$bar['franco'];
			$tanggalkirim1[$bar['nokontrak']]=$bar['tanggalkirim'];
			$tanggalkirim2[$bar['nokontrak']]=$bar['sdtanggal'];

			$moist[$bar['nokontrak']]=$bar['moist'];
			$dirt[$bar['nokontrak']]=$bar['dirt'];
			$ffa[$bar['nokontrak']]=$bar['ffa'];
			$mdani[$bar['nokontrak']]=$bar['mdani'];
			



		}
		
		
		$str = "select * from ".$dbname.".keu_penagihanht  where  nokontrak in ('".implode("','",$arrnokontrak)."')";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			if($bar['jenisinvoice']=='UM'){
				$noinvoiceum[$bar['nokontrak']]=$bar['noinvoice'];
				$tanggalinvoiceum[$bar['nokontrak']]=$bar['tanggal'];
				$nilaiinvoiceum[$bar['nokontrak']]=$bar['nilaiinvoice'];
				$nilaippninvoiceum[$bar['nokontrak']]=$bar['nilaippn'];
				$nofakturpajakinvoiceum[$bar['nokontrak']]=$bar['nofakturpajak'];
			}
			if($bar['jenisinvoice']=='PL'){
				$noinvoicepl[$bar['nokontrak']]=$bar['noinvoice'];
				$tanggalinvoicepl[$bar['nokontrak']]=$bar['tanggal'];
				$nilaiinvoicepl[$bar['nokontrak']]=$bar['nilaiinvoice'];
				$nilaippninvoicepl[$bar['nokontrak']]=$bar['nilaippn'];
				$nofakturpajakinvoicepl[$bar['nokontrak']]=$bar['nofakturpajak'];
			}
			$arrnoinvoice[$bar['noinvoice']]=$bar['noinvoice'];
		}
		
		$str = "select * from ".$dbname.".keu_kasbankdtht_vw  where  keterangan1 in ('".implode("','",$arrnoinvoice)."')";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$arrnobank[$bar['notransaksi']]=$bar['notransaksi'];
			$listnobank[$bar['keterangan1']][$bar['notransaksi']]=$bar['notransaksi'];
			$tanggalbank[$bar['keterangan1']][$bar['notransaksi']]=$bar['tanggal'];
			$nilaibank[$bar['keterangan1']][$bar['notransaksi']]=$bar['jumlah'];
			$novoucherbank[$bar['keterangan1']][$bar['notransaksi']]=$bar['novoucher'];
		}
		
		// echo"<pre>";
		// print_r($listnobank);
		// echo"</pre>";
		
		
		
		if($tipe=='excel'){
			$border='border=1';
		} else {
			
			$border='';
		}

            $stream.=" <table cellpading=0 cellspacing=1 ".$border." class=sortable  style=width:3000px;>";
            $stream.=" <thead>";
			$stream.="  <tr class=rowheader>";
				$stream.="  <td  align=center rowspan=3>".$_SESSION['lang']['NoKontrak']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['tanggal']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['penjual']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['customer']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['kodebarang']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['kuantitas']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['mutu']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['hargasatuan']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['tipe']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['franco']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['tanggalkirim']."</td>";
				$stream.=" <td  align=center rowspan=3>".$_SESSION['lang']['jumlah']."</td>";
				
				$stream.=" <td  align=center colspan=6>".$_SESSION['lang']['uangmuka']."</td>";
				$stream.=" <td  align=center colspan=6>".$_SESSION['lang']['lunas']."</td>";
				// $stream.=" <td  align=center colspan=8>".$_SESSION['lang']['uangmuka']."</td>";
				// $stream.=" <td  align=center colspan=8>".$_SESSION['lang']['lunas']."</td>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";
			for($i=1;$i<=2;$i++){
						$stream.="<td  align=center colspan=5>".$_SESSION['lang']['invoice']."</td>";
						$stream.="<td  align=center>".$_SESSION['lang']['bank']."</td>";
						// $stream.="<td  align=center colspan=3>".$_SESSION['lang']['bank']."</td>";
			}
			$stream.="</tr>";	
			$stream.="<tr class=rowheader>";
				for($i=1;$i<=2;$i++){
					$stream.="<td  align=center>".$_SESSION['lang']['noinvoice']."</td>";
					$stream.="<td  align=center>".$_SESSION['lang']['nofaktur']."</td>";
					$stream.="<td  align=center>".$_SESSION['lang']['tanggal']."</td>";
					$stream.="<td  align=center>".$_SESSION['lang']['rupiah']."</td>";
					$stream.="<td  align=center>".$_SESSION['lang']['ppn']."</td>";
					
					$stream.="<td>";
					
						$stream.=" <table cellpading=0 cellspacing=0 ".$border." class=sortable   style='width:550px;'>";
							$stream.="<tr class=rowcontent>";
								$stream.="<td  style='border-right:0.5px solid #76B1DA;width:200px' align=center>".$_SESSION['lang']['nodok']."</td>";
								$stream.="<td  style='border-left:0.5px solid #76B1DA;border-right:0.5px solid #76B1DA;width:200px;' align=center>".$_SESSION['lang']['novoucher']."</td>";
								$stream.="<td style='border-left:0.5px solid #76B1DA;border-right:0.5px solid #76B1DA;width:100px;' align=center>".$_SESSION['lang']['tanggal']."</td>";
								$stream.="<td  align=center style='width:100px'>".$_SESSION['lang']['rupiah']."</td>";
							$stream.="</tr>";
									
						$stream.="</table>";
					
					$stream.="</td>";
					
					// $stream.="<td  align=center>".$_SESSION['lang']['novoucher']."</td>";
					// $stream.="<td  align=center>".$_SESSION['lang']['tanggal']."</td>";
					// $stream.="<td  align=center>".$_SESSION['lang']['rupiah']."</td>";
				}
			$stream.="</tr>";	
				
               	$stream.="</thead>";


		foreach(@$arrnokontrak as $nokontrak){

              
			
						$stream.="<tr class=rowcontent>";
							$stream.="<td>".$nokontrak."</td>";
							$stream.="<td>".tanggalnormal($tanggalkontrak[$nokontrak])."</td>";
							$stream.="<td>".$penjual[$nokontrak]."</td>";
							$stream.="<td>".$nmcustsomer[$customer[$nokontrak]]."</td>";
							$stream.="<td>".$arrinisial[$komoditi[$nokontrak]]."</td>";
							$stream.="<td align=right>".number_format($kuantitas[$nokontrak])."</td>";
							
							if ($komoditi[$nokontrak]=='40000002') {
								$stream.="<td>Kadar Air : ".$moist[$nokontrak].", Kadar Kotoran : ".$dirt[$nokontrak]."</td>";
							}else if ($komoditi[$nokontrak]=='40000001'){
								$stream.="<td>FFA : ".$ffa[$nokontrak].", M&I : ".$mdani[$nokontrak]."</td>";
							}
							
							$stream.="<td align=right>".number_format($hargasatuan[$nokontrak])."</td>";
							$stream.="<td>".$tipepenjualan[$nokontrak]."</td>";
							$stream.="<td>".$franco[$nokontrak]."</td>";
							$stream.="<td>".tanggalnormal($tanggalkirim1[$nokontrak])." s/d ".tanggalnormal($tanggalkirim2[$nokontrak])."</td>";
							$stream.="<td align=right>".number_format($hargasatuan[$nokontrak]*$kuantitas[$nokontrak])."</td>";
							
							$stream.="<td>".$noinvoiceum[$nokontrak]."</td>";
							$stream.="<td>".$nofakturpajakinvoiceum[$nokontrak]."</td>";
							$stream.="<td>".tanggalnormal($tanggalinvoiceum[$nokontrak])."</td>";
							$stream.="<td align=right>".number_format($nilaiinvoiceum[$nokontrak])."</td>";
							$stream.="<td align=right>".number_format($nilaippninvoiceum[$nokontrak])."</td>";
							
							$stream.="<td>";
								$stream.=" <table cellpading=0 cellspacing=0 ".$border." style='width:550px;'>";
								foreach($arrnoinvoice as $noinvoice){
									foreach($arrnobank as $nobank){
										if($listnobank[$noinvoice][$nobank]!=''){
											if($noinvoiceum[$nokontrak]==$noinvoice){
												$stream.="<tr class=rowcontent>";
													$stream.="<td style='border-right:0.5px solid #76B1DA;width:200px;' align=center>".$nobank."</td>";
													$stream.="<td style='border-right:0.5px solid #76B1DA;width:200px;' >".$novoucherbank[$noinvoice][$nobank]."</td>";
													$stream.="<td style='border-right:0.5px solid #76B1DA;width:100px;' >".tanggalnormal($tanggalbank[$noinvoice][$nobank])."</td>";
													$stream.="<td align=right style=';width:100px'>".number_format($nilaibank[$noinvoice][$nobank])."</td>";
												$stream.="</tr>";
											}
										}
									}
								}
								$stream.="</table>";
							$stream.="</td>";
							$stream.="<td>".$noinvoicepl[$nokontrak]."</td>";
							$stream.="<td>".$nofakturpajakinvoicepl[$nokontrak]."</td>";
							$stream.="<td>".tanggalnormal($tanggalinvoicepl[$nokontrak])."</td>";
							$stream.="<td align=right>".number_format($nilaiinvoicepl[$nokontrak])."</td>";
							$stream.="<td align=right>".number_format($nilaippninvoicepl[$nokontrak])."</td>";
							$stream.="<td>";
								 $stream.=" <table cellpading=0 cellspacing=0 ".$border." class=sortable  style=width:100%;>";
								foreach($arrnoinvoice as $noinvoice){
									foreach($arrnobank as $nobank){
											// if($listnobank[$noinvoice][$nobank]!=''){
											// if($listnobank[$noinvoice][$nobank]!='' and $noinvoiceum[$nokontrak]==$noinvoice){
											if($listnobank[$noinvoice][$nobank]!=''){
												if($noinvoicepl[$nokontrak]==$noinvoice){
													$stream.="<tr class=rowcontent>";
														$stream.="<td style='border-right:0.5px solid #76B1DA;' align=center>".$nobank."</td>";
														$stream.="<td style='border-right:0.5px solid #76B1DA;' >".$novoucherbank[$noinvoice][$nobank]."</td>";
														$stream.="<td style='border-right:0.5px solid #76B1DA;' >".tanggalnormal($tanggalbank[$noinvoice][$nobank])."</td>";
														$stream.="<td align=right>".number_format($nilaibank[$noinvoice][$nobank])."</td>";
													$stream.="</tr>";
												}
											}
										}
								}
								$stream.="</table>";
							$stream.="</td>";
							
					
		}
		
		// $stream.="<tr class=rowheader bgcolor=#B0C4DE>";
		// $stream.="<td align=center colspan=9>".$_SESSION['lang']['total']."</td>";
		// $stream.="<td align=right>".@number_format($tkgwbnetto,2)."</td>";
		// $stream.="<td></td>";
		// $stream.="<td id=ttotalrp align=right>".@number_format($ttotalrp,2)."</td>";
		// $stream.="<td></td>";
		// $stream.="</tr>";
		// <button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button></td>";	
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="rekap_kontrak_".$kodept."_".$tglSkrg;
			if(strlen($stream)>0){
                if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
								@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
                } else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
                }
                fclose($handle);
			}     
		} else {
			echo $stream;
		}
	break;
	

	
    default:
	break;
}
?>
