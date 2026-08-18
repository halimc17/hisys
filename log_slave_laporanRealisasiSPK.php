<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$afd = checkPostGet('afd', '');
$tglAkhir = checkPostGet('tglAkhir', '');
$tglAwal = checkPostGet('tglAwal', '');
$posting = checkPostGet('posting', '');
$whr = "supplierid='K001'";
$optNamkont = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', $whr);
$nmkeg= makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');

// exit("Error:$posting");

if($proses!='getafd'){
	if ($proses == 'excel') {
    $border = 1;
} else {
    $border = 0;
}

	$stream = "
       <table class=sortable cellspacing=1 border=" . $border . ">
             <thead>
                    <tr class=rowheader>
                       <td align=center rowspan=2>No.</td>
                       <td align=center colspan=10>" . $_SESSION['lang']['kontrak'] . "</td>
                       <td align=center colspan=4>" . $_SESSION['lang']['realisasi'] . "</td>
				   </tr>
				   <tr>
                       <td align=center>" . $_SESSION['lang']['nospk'] . "</td>
                       <td align=center>" . $_SESSION['lang']['blok'] . "</td>
                       <td align=center>" . $_SESSION['lang']['kontraktor'] . "</td>
                       <td align=center>" . $_SESSION['lang']['kegiatan'] . "</td>
                       <td align=center>" . $_SESSION['lang']['namakegiatan'] . "</td>                       
                       <td align=center>" . $_SESSION['lang']['jhk'] . "</td>
                       <td align=center>" . $_SESSION['lang']['hasilkerjad'] . "</td>
                       <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
                       <td align=center>" . $_SESSION['lang']['hargasatuan'] . "</td>
                       <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
                       <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                       <td align=center>" . $_SESSION['lang']['jhk'] . "</td>
                       <td align=center>" . $_SESSION['lang']['hasilkerjad'] . "</td>
                       <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
                     </tr>  
                 </thead>
                 <tbody>";
	
	
	if($afd!=''){
		$wherebaspk=" and kodeblok like '" . $afd . "%' ";
		$wherespk=" and b.kodeblok like '" . $afd . "%'  ";
	}else{
		$wherebaspk=" and kodeblok like '" . $unit . "%' ";
		$wherespk=" and b.kodeblok like '" . $unit . "%'  ";
	}
	
	if($posting!=''){
		$whpost=" and statusjurnal='".$posting."'";
		
	}
				 
	$str=" select * from ".$dbname.".log_baspk where 1=1 ".$wherebaspk." ".$whpost."
			and tanggal between '" . tanggalsystem($tglAwal) . "' and '" . tanggalsystem($tglAkhir) . "'  ";	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);				 
	while ($bar = $res->fetch()) {		 
		@$arrnotran[$bar['notransaksi']]=$bar['notransaksi'];
		@$arrblok[$bar['kodeblok']]=$bar['kodeblok'];
		@$arrkeg[$bar['kodekegiatan']]=$bar['kodekegiatan'];
		@$arrtgl[$bar['tanggal']]=$bar['tanggal'];	
		
		@$listblok[$bar['notransaksi']][$bar['kodeblok']]=$bar['kodeblok'];
		@$listkeg[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
		@$listtgl[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['tanggal'];
		
		@$bahk[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['hkrealisasi'];
		@$bahasilkerja[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['hasilkerjarealisasi'];
		@$bajumlah[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['jumlahrealisasi'];
		
	}


	$str=" select b.*,a.tanggal,c.koderekanan from ".$dbname.".log_baspk a left join ".$dbname.".log_spkdt b 
			on a.notransaksi=b.notransaksi and a.kodekegiatan=b.kodekegiatan and a.kodeblok=b.kodeblok
			left join ".$dbname.".log_spkht c on a.notransaksi=c.notransaksi
			where 1=1 ".$wherespk."
			and a.tanggal between '" . tanggalsystem($tglAwal) . "' and '" . tanggalsystem($tglAkhir) . "'  
			group by b.notransaksi,b.kodeblok,b.kodekegiatan";	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);				 
	while ($bar = $res->fetch()) {		 
		$spknotran[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['notransaksi'];
		$spkblok[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['kodeblok'];
		$spkkeg[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['kodekegiatan'];
		$spkhk[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['hk'];
		$spkhasilkerja[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['hasilkerjajumlah'];
		$spksatuan[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['satuan'];
		$spkjumlah[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['jumlahrp'];
		$spkhargasatuan[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['rupiahpersatuan'];
		$spkkontraktor[$bar['notransaksi']][$bar['kodeblok']][$bar['kodekegiatan']][$bar['tanggal']]=$bar['koderekanan'];
	}

	foreach($arrnotran as $notran){
		foreach($arrblok as $blok){
			if(@$listblok[$notran][$blok]!=''){
				foreach($arrkeg as $keg){
					if(@$listkeg[$notran][$blok][$keg]!=''){
						foreach($arrtgl as $tgl){
							if(@$listtgl[$notran][$blok][$keg][$tgl]!=''){
								@$no+=1;
								$stream.="<tr class=rowcontent>";
									$stream.="<td align=center>" . $no . "</td>";
									$stream.="<td align=left>".@$spknotran[$notran][$blok][$keg][$tgl]."</td>";
									$stream.="<td align=left>".@$spkblok[$notran][$blok][$keg][$tgl]."</td>";
									$stream.="<td align=left>".@$optNamkont[$spkkontraktor[$notran][$blok][$keg][$tgl]]."</td>";
									$stream.="<td align=left>".@$spkkeg[$notran][$blok][$keg][$tgl]."</td>";
									$stream.="<td align=left>".@$nmkeg[$spkkeg[$notran][$blok][$keg][$tgl]]."</td>";
									$stream.="<td align=right>".@$spkhk[$notran][$blok][$keg][$tgl]."</td>";
									$stream.="<td align=right>".@$spkhasilkerja[$notran][$blok][$keg][$tgl]."</td>";
									$stream.="<td align=left>".@$spksatuan[$notran][$blok][$keg][$tgl]."</td>";
									
									
									if(@$spkhargasatuan[$notran][$blok][$keg][$tgl]==''){
										$stream.="<td align=center></td>";
									}else{
										$stream.="<td align=right>".number_format($spkhargasatuan[$notran][$blok][$keg][$tgl],2)."</td>";
									}
									
									if(@$spkhargasatuan[$notran][$blok][$keg][$tgl]==''){
										$stream.="<td align=center></td>";
									}else{
										$stream.="<td align=right>".number_format($spkjumlah[$notran][$blok][$keg][$tgl])."</td>";
									}
									
									
									$stream.="<td align=center>".tanggalnormal($tgl)."</td>";
									$stream.="<td align=right>".$bahk[$notran][$blok][$keg][$tgl]."</td>";
									$stream.="<td align=right>".$bahasilkerja[$notran][$blok][$keg][$tgl]."</td>";
									$stream.="<td align=right>".number_format($bajumlah[$notran][$blok][$keg][$tgl])."</td>";
								 $stream.="</tr>";
								 
								#buat subtotal
								@$stbajumlah[$notran][$blok][$keg]+=$bajumlah[$notran][$blok][$keg][$tgl];	
								@$stbahk[$notran][$blok][$keg]+=$bahk[$notran][$blok][$keg][$tgl];
								@$stbahasilkerja[$notran][$blok][$keg]+=$bahasilkerja[$notran][$blok][$keg][$tgl];

								
								#buat grand total 
								@$gtspkhk+=$spkhk[$notran][$blok][$keg][$tgl];
								@$gtbahk+=$bahk[$notran][$blok][$keg][$tgl];
								@$gtspkhasilkerja+=$spkhasilkerja[$notran][$blok][$keg][$tgl];
								@$gtbahasilkerja+=$bahasilkerja[$notran][$blok][$keg][$tgl];
								@$gtspkjumlah+=$spkjumlah[$notran][$blok][$keg][$tgl];
								@$gtbajumlah+=$bajumlah[$notran][$blok][$keg][$tgl];
								
								
							}
						}
						$stream.="<tr class=rowcontent>
							<td colspan=12 style='text-align:right; font-weight:bold'>" . $_SESSION['lang']['subtotal'] . "</td>
							<td style='text-align:right; font-weight:bold''>".$stbahk[$notran][$blok][$keg]."</td>
							<td style='text-align:right; font-weight:bold''>".$stbahasilkerja[$notran][$blok][$keg]."</td>
							<td style='text-align:right; font-weight:bold''>" . number_format($stbajumlah[$notran][$blok][$keg]) . "</td>
						</tr>";
						
						
					}
				}
			}
		}	
	}
	$stream.="<tr class=rowcontent>
				<td colspan=12 style='text-align:right; font-weight:bold'>" . $_SESSION['lang']['grnd_total'] . "</td>
				<td style='text-align:right; font-weight:bold''>".number_format($gtbahk)."</td>
				<td style='text-align:right; font-weight:bold''>".number_format($gtbahasilkerja,2)."</td>
				<td style='text-align:right; font-weight:bold''>" . number_format($gtbajumlah) . "</td>
			</tr>";
	$stream.="</tbody>
					 <tfoot>
					 </tfoot>		 
			   </table>";

		
}

switch ($proses) {
	case'getafd':
			$opt="<option value=''>".$_SESSION['lang']['all']."</option>";
			$str="select * from ".$dbname.".organisasi where induk='".$unit."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$opt.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
			}
			echo $opt;
	break;
	
    case 'html':
        echo $stream;
        break;
    case 'excel':
        $nop_ = "RealisasiSPK_" . $unit . "_" . tanggalsystem($tglAwal) . "_" . tanggalsystem($tglAkhir);
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $stream);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";
        break;

    default:
        break;
}
?>