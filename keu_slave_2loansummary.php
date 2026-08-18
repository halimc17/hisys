<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/nangkoelib.php');
// require_once('dompdf/autoload.inc.php');
// use Dompdf\Dompdf;

$stream='';
$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');



switch ($method) {
	case 'getperiode':
		$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str="SELECT a.*,b.namabank as kodebank,b.rekening as noaccount from ".$dbname.".keu_pmpeminjamanht a 
			left join keu_5akunbank	b on a.noakun = b.noakun where a.notransaksi='".$notransaksi."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$pinjaman=$res->fetch();

			$sAwalCair="select * from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."' order by tanggal asc limit 1";
			//echo $sAwalCair;
			$rAwalCair=fetchData($sAwalCair);
			$awalcair=strtotime($rAwalCair[0]['tanggal']);
			for($awal=1;$awal<=$pinjaman['jumlahbulan'];$awal++){
				if($awal==1){
					$isiTgl=explode("-", $tglBuatDpnnya);
					if($isiTgl[1]=="01"){
						$tglBuatDpnnya=$isiTgl[0]."-".$isiTgl[1]."-01";
					}
					$tglBuatDpnnya=date('Y-m-d',strtotime('+1 month', $awalcair));
					$tglBerikut=date('Y-m', strtotime($tglBuatDpnnya));
					$listPeriode[$tglBerikut]=$tglBerikut;
				}else{
					$isiTgl=explode("-", $tglBuatDpnnya);
					if($isiTgl[1]=="01"){
						$tglBuatDpnnya=$isiTgl[0]."-".$isiTgl[1]."-01";
					}
					$tglMaju=strtotime($tglBuatDpnnya);
					$tglBuatDpnnya=date('Y-m-d', strtotime('+1 month', $tglMaju));
					$tglBerikut=date('Y-m', strtotime($tglBuatDpnnya));
					$listPeriode[$tglBerikut]=$tglBerikut;
					
				}
			}
			foreach ($listPeriode as $key) {
				if($periode==$key){
					$optPeriode.="<option value='".$key."' selected>".$key."</option>";
				}else{
					$optPeriode.="<option value='".$key."'>".$key."</option>";	
				}
			}
			$optPeriode.="</select>";
			echo $optPeriode;
	break;
	case'preview':
	$brd=0;
	$bgclr="";
	if($tipe=='excel'){
		$brd=1;
		$bgclr="bgcolor=#7FFFD4";
	}
		$tab.="
				<table cellpading=1 cellspacing=1 border=".$brd." class=sortable>
				<thead>
					<tr>
					<td colspan=12 align=center  ".$bgclr.">".$_SESSION['lang']['angsuran']."</td>
					</tr>
					<tr class=rowheader>
						<td align=center ".$bgclr.">Ang. Ke</td>
						<td align=center ".$bgclr.">".$_SESSION['lang']['nopeminjaman']."</td>
						<td align=center ".$bgclr.">".$_SESSION['lang']['tanggalpencairan']."</td>
						<td align=center ".$bgclr.">".$_SESSION['lang']['outstandingloan']."</td>
						<td align=center ".$bgclr.">".$_SESSION['lang']['pokokhutang']."</td>
						<td align=center ".$bgclr.">".$_SESSION['lang']['sukubunga']."</td>
						<td align=center  ".$bgclr." colspan=2>".$_SESSION['lang']['periode']."</td>
						<td align=center ".$bgclr.">".$_SESSION['lang']['harihutang']."</td>
						<td align=center ".$bgclr.">Bunga</td>
						<td align=center ".$bgclr.">".$_SESSION['lang']['totalbunga']."</td>
						<td align=center ".$bgclr.">".$_SESSION['lang']['pokokhutang']."+".$_SESSION['lang']['totalbunga']."</td>
					</tr>	
				</thead>";
				$daftarjumlahhari=  makeOption($dbname, 'keu_5daftarbank', 'kodebank,jumlah_hari');
				$str="SELECT a.*,b.namabank as kodebank,b.rekening as noaccount from ".$dbname.".keu_pmpeminjamanht a 
				left join keu_5akunbank	b on a.noakun = b.noakun where a.notransaksi='".$notransaksi."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$pinjaman=$res->fetch();
				$sAwalCair="select * from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."' order by tanggal asc limit 1";
				//echo $sAwalCair;
				$rAwalCair=fetchData($sAwalCair);
				$awalcair=strtotime($rAwalCair[0]['tanggal']);
				for($awal=1;$awal<=$pinjaman['jumlahbulan'];$awal++){
					if($awal==1){
						$isiTgl=explode("-", $tglBuatDpnnya);
						if($isiTgl[1]=="01"){
							$tglBuatDpnnya=$isiTgl[0]."-".$isiTgl[1]."-01";
						}
						$tglBuatDpnnya=date('Y-m-d',strtotime('+1 month', $awalcair));
						$tglBerikut=date('Y-m', strtotime($tglBuatDpnnya));
						$listPeriode[$tglBerikut]=$tglBerikut;
					}else{
						$isiTgl=explode("-", $tglBuatDpnnya);
						if($isiTgl[1]=="01"){
							$tglBuatDpnnya=$isiTgl[0]."-".$isiTgl[1]."-01";
						}
						$tglMaju=strtotime($tglBuatDpnnya);
						$tglBuatDpnnya=date('Y-m-d', strtotime('+1 month', $tglMaju));
						$tglBerikut=date('Y-m', strtotime($tglBuatDpnnya));
						$listPeriode[$tglBerikut]=$tglBerikut;
					}
					
				}
				
				#ambill kodebank
				$sBank="select * from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$notransaksi."'";
				//exit('Warning'.$sBank);
				$rBank=fetchData($sBank);

				foreach ($listPeriode as $key => $prd) {
					if($prd>$periode){
						continue;
					}
						$periodedate = $prd."-".$rBank[0]['jatuhtempo'];#bulan berjalan
						$sTglAkhir="select * from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."' order by tanggal asc limit 1";
						//echo $sTglAkhir;
						$rTglAkhir=fetchData($sTglAkhir);

						$tglAwalPencairan=$_POST['tglAkhir']=$rTglAkhir[0]['tanggal'];
						$tanggaljatuhtempo = $pinjaman['jatuhtempo'];
						// if periode 2012-11
						$periodedate = $prd."-".$tanggaljatuhtempo;#bulan berjalan
						$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
						$periodedate2 = date("Y-m-d",strtotime("+2 Month",strtotime($periodedate1)));
						
						$totaldayHari = datediff($periodedate1,$periodedate);#plafon jumlah hari
						$jmlhari = 0;
						if(isset($daftarjumlahhari[$pinjaman['kodebank']])){
							$jmlhari = $daftarjumlahhari[$pinjaman['kodebank']];
						}
						//echo $_POST['tglAkhir'];
						#ambil nilai pokok per noloan per periode
						#periode sebenernya dimundurin sebulan,karena pemotongan nilai pokok dibulan berikutnya
						//$sPokok="select noloan,sum(pokok) as angPokok from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and periode<='".substr($periodedate1,0,7)."' group by noloan";
						$sPokok="select noloan,sum(rupiahangsuran) as angPokok from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and periode<='".substr($periodedate1,0,7)."' group by noloan";
						$rPokok=fetchData($sPokok);
						foreach ($rPokok as $key => $val) {
							$rupPokok[$val['noloan']]=$val['angPokok'];#angka untuk pengurang hutang
						}

						//$sPokok="select noloan,pokok as angPokok,tenor,sukubunga from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and periode='".$periode."' ";
						#ambil nilai pokok per pencairan atau per noloan
						$sPokok="select noloan,rupiahangsuran as angPokok,bulanke as tenor from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and periode='".$prd."' ";
						//echo $sPokok;
						$rPokok=fetchData($sPokok);
						foreach ($rPokok as $key => $val) {
							$rupPokokDisplay[$val['noloan']]=$val['angPokok'];#angka untuk kepentingan display
							$ruptenDisplay[$val['noloan']]=$val['tenor'];#angka untuk kepentingan display
							$bungaNya[$val['noloan']]=$val['sukubunga'];
						}
						
						#realisasi bunga
						$sRealisasi="select sum(bunga) as bungarealisasi from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and periode='".$prd."'";
						$rRealisasi=fetchData($sRealisasi);
						$totBungaRealisasi=$rRealisasi[0];
						#query ambiil data pencairan
						$str=" select * from ".$dbname.".keu_pmpeminjamandt_pencairan 
						where  notransaksi='".$notransaksi."' and tanggal between '".$_POST['tglAkhir']."' and '".$periodedate."' order by tanggal asc";
						//echo $str;
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$jumlah = 0;
						$Alltotalbunga = 0;
						
						$awlItung=0;
						//$sukub = getbunga($databunga,$pinjaman['kodebank'],$periode,$pinjaman['jenis']);

						while($bar=$res->fetch()){
							// $sukub=$bungaNya[$bar['noloan']];
							if($bar['tgl_jatuhtempo']!='00'){
								$periodedate = $prd."-".$bar['tgl_jatuhtempo'];#bulan berjalan
								$periodedate1=date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
							}else{
								$periodedate = $prd."-".$tanggaljatuhtempo;#bulan berjalan
								$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
							}
							
							#cari tanggal bunga dibawah range
							$rBungaKisi=$arrBunga=array();
							$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <='".$periodedate1."' and notransaksipm='".$notransaksi."' order by periode asc";
							//echo $sBungaKisi;
							$rBungaKisi=fetchData($sBungaKisi);
							if(count($rBungaKisi)!=0){
								foreach($rBungaKisi as $row=>$isi){
									if($periodedate1>$isi['periode']){
										$arrBunga['tanggal']=array(); #reset selalu hanya satu paling terakhir
										$arrBunga['bunga']=array();#reset selalu hanya satu paling terakhir
										$isi['periode']=$periodedate1;
									}
									$arrBunga['tanggal'][]=$isi['periode'];
									$arrBunga['bunga'][]=$isi['nilai'];	
								}
							}
							 
							$str=" select nilai from ".$dbname.".keu_pmsukubunga where  left(periode,7)>='".substr($tglAwalPencairan,0,7)."' and left(periode,7)<='".substr($periodedate,0,7)."' and notransaksipm='".$notransaksi."' order by periode desc limit 1";
							//echo $str;
							$rSukuB=fetchData($str);
							$sukub=$rSukuB[0]['nilai'];
							$hasilbunga = (($bar['jumlah']-$rupPokok[$bar['noloan']])*$sukub/100);
							$jumlah += $bar['jumlah'];
							$rowspanDt=1;
							$pokBunga[$bar['noloan']]=$rupPokokDisplay[$bar['noloan']];
							if(count($arrBunga['tanggal'])<2){
								
								@$nopencairan+=1;
								$totalday_ = array();
								if($bar['tanggal']<$periodedate&&$bar['tanggal']>$periodedate1){
									// jika tanggal lebih kecil atau lebih besar dari tangal jatuh tempo tiap bulannya
									//maka jumlah hari bukan sebulan alias harus proporsi
									$periodedate1_ = $bar['tanggal'];
									$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
									$totalday_ = datediff($periodedate1_,$periodedate2_);
								}else{
									$periodedate1_ = $periodedate1;
									$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
									$totalday_ = datediff($periodedate1_,$periodedate2_);
								}
								//exit('Warning'.$hasilbunga.'__'.$totalday_['days_total'].'__'.$jmlhari);
								$totalbunga = ($hasilbunga*$totalday_['days_total'])/(int)$jmlhari;
								$Alltotalbunga += $totalbunga;
								$pokBunga[$bar['noloan']]+=$totalbunga;
								$totBunga[$bar['noloan']]=$totalbunga;
								$tab.="<tr class=rowcontent>
									<td rowspan=".$rowspanDt.">".$ruptenDisplay[$bar['noloan']]."</td>
									<td rowspan=".$rowspanDt.">".$bar['noloan']."</td>
									<td rowspan=".$rowspanDt.">".tglnmbln($bar['tanggal'],'','')."</td>
									<td align=right rowspan=".$rowspanDt.">".number_format(($bar['jumlah']-$rupPokok[$bar['noloan']]))."</td>
									<td align=right rowspan=".$rowspanDt.">".number_format($rupPokokDisplay[$bar['noloan']])."</td>";
									$tab.="<td align=right>".number_format($sukub,2)."%</td>
									<td align=right>".tglnmbln($periodedate1_,'','')."</td>
									<td align=right>".tglnmbln($periodedate2_,'','')."</td>
									<td align=right>".$totalday_['days_total']."</td>
									<td align=right>".number_format($totalbunga)."</td>
									<td align=right>".number_format($totalbunga)."</td>";
							$tab.="<td align=right rowspan=".$rowspanDt.">".number_format($pokBunga[$bar['noloan']])."</td></tr>";
							}else{#jika tanggal bunga lebih dari satu
								$rowspanDt=count($arrBunga['tanggal']);
								$totalday_ = array();
								$totHari=count($arrBunga['tanggal'])-1;
								#foreach buat cari total bunga
								foreach ($arrBunga['tanggal'] as $key => $val){
											if($totHari==$key){#jika tothari sama dengan index array,mencari index terakhir
												$indSblm=$totHari-1;
												if($tglBerikutNya==$val){
													$periodedate1_ = $tglBerikutNya;	
												}else{
													$periodedate1_ = $arrBunga['tanggal'][$indSblm];	
												}
												
												if($periodedate!=$val){#jika tanggal terakhir tidak sama dengan nilai array terakhir maka diisi dengan tanggal akhir
													$val=$periodedate;
												}
											}else{
												if($key!=0){
													$periodedate1_ = $tglBerikutNya;	
												}else{
													$periodedate1_ = $periodedate1;	
													if($periodedate1>$val){
														$val=$arrBunga['tanggal'][($key+1)];
														$tglBerikutNya=$arrBunga['tanggal'][($key+1)];
													}
													else if($periodedate1==$val){#jika tanggal bunga sama dengan tanggal mulai continue row dikurangi 1
														$val=$arrBunga['tanggal'][($key+1)];
														$tglBerikutNya=$arrBunga['tanggal'][($key+1)];
														// $tglBerikutNya=$val;
														// $rowspanDt=$rowspanDt-1;
														// continue;
													}else{
														$tglBerikutNya=$val;
													}
												}						
												//$tglBerikutNya=$val;
											}
											$periodedate2_ = date("Y-m-d",strtotime($val));
											//$periodedate2_=date("Y-m-d",strtotime($val));
											$totalday_ = datediff($periodedate1_,$periodedate2_);
										
										$hasilbunga = (($bar['jumlah']-$rupPokok[$bar['noloan']])*$arrBunga['bunga'][$key]/100);
										$totalbunga = ($hasilbunga*$totalday_['days_total'])/(int)$jmlhari;
										$totBunga[$bar['noloan']]+=$totalbunga;
								}
								#foreach buat display bunga
								foreach ($arrBunga['tanggal'] as $key => $val) {
											if($totHari==$key){#jika tothari sama dengan index array,mencari index terakhir
												$indSblm=$totHari-1;
												if($tglBerikutNya==$val){
													$periodedate1_ = $tglBerikutNya;	
												}else{
													$periodedate1_ = $arrBunga['tanggal'][$indSblm];	
												}
												
												if($periodedate!=$val){#jika tanggal terakhir tidak sama dengan nilai array terakhir maka diisi dengan tanggal akhir
													$val=$periodedate;
												}
											}else{
												if($key!=0){
													$periodedate1_ = $tglBerikutNya;	
												}else{
													$periodedate1_ = $periodedate1;	
													if($periodedate1>$val){
														$val=$arrBunga['tanggal'][($key+1)];
														$tglBerikutNya=$arrBunga['tanggal'][($key+1)];
													}
													else if($periodedate1==$val){#jika tanggal bunga sama dengan tanggal mulai continue row dikurangi 1
														$val=$arrBunga['tanggal'][($key+1)];
														$tglBerikutNya=$arrBunga['tanggal'][($key+1)];
														// $tglBerikutNya=$val;
														// $rowspanDt=$rowspanDt-1;
														// continue;
													}else{
														$tglBerikutNya=$val;
													}
												}						
												//$tglBerikutNya=$val;
											}
											$periodedate2_ = date("Y-m-d",strtotime($val));
											//$periodedate2_=date("Y-m-d",strtotime($val));
											$totalday_ = datediff($periodedate1_,$periodedate2_);
										
										$hasilbunga = (($bar['jumlah']-$rupPokok[$bar['noloan']])*$arrBunga['bunga'][$key]/100);
										$totalbunga = ($hasilbunga*$totalday_['days_total'])/(int)$jmlhari;
										
										$Alltotalbunga += $totalbunga;
										$pokBunga[$bar['noloan']]=$totBunga[$bar['noloan']]+$rupPokokDisplay[$bar['noloan']];
										$tab.="<tr class=rowcontent>";
										if($tempLoan!=$bar['noloan']){
											$tempLoan=$bar['noloan'];
										     $tab.="<td rowspan=".$rowspanDt.">".$ruptenDisplay[$bar['noloan']]."</td>
									          <td rowspan=".$rowspanDt.">".$bar['noloan']."</td>
									          <td rowspan=".$rowspanDt.">".tglnmbln($bar['tanggal'],'','')."</td>
									          <td align=right rowspan=".$rowspanDt.">".number_format(($bar['jumlah']-$rupPokok[$bar['noloan']]))."</td>
									          <td align=right rowspan=".$rowspanDt.">".number_format($rupPokokDisplay[$bar['noloan']])."</td>";
										
											$tab.="<td align=right>".number_format($arrBunga['bunga'][$key],2)."%</td>
													<td>".tglnmbln($periodedate1_,'','')."</td>
													<td>".tglnmbln($periodedate2_,'','')."</td>
													<td align=right>".$totalday_['days_total']."</td>
													<td align=right>".number_format($totalbunga)."</td>
													<td align=right rowspan=".$rowspanDt.">".number_format($totBunga[$bar['noloan']])."</td>
													<td align=right rowspan=".$rowspanDt.">".number_format($pokBunga[$bar['noloan']])."</td>";											
										}else{
											$tab.="<td align=right>".number_format($arrBunga['bunga'][$key],2)."%</td>
													<td>".tglnmbln($periodedate1_,'','')."</td>
													<td>".tglnmbln($periodedate2_,'','')."</td>
													<td align=right>".$totalday_['days_total']."</td>
													<td align=right>".number_format($totalbunga)."</td>";
										}
										$tab.="</tr>";	
								}
							}
							$totCaira+=($bar['jumlah']-$rupPokok[$bar['noloan']]);
							$totPokok+=$rupPokokDisplay[$bar['noloan']];
							$totPokBunga+=$pokBunga[$bar['noloan']];
							$totBungaAll+=$totBunga[$bar['noloan']];
							
						}
						$tab.="<tr class=rowcontent>
							<td colspan='3'  ".$bgclr." align=right>TOTAL</td>
							<td align=right ".$bgclr.">".number_format($totCaira)."</td>
							<td align=right ".$bgclr.">".number_format($totPokok)."</td>
							<td colspan='5' align=right ".$bgclr.">&nbsp;</td>
							<td align=right ".$bgclr.">".number_format($totBungaAll)."</td>
							<td align=right ".$bgclr.">".number_format($totPokBunga)."</td>
						</tr>";
						$tab.="<tr>
							<td colspan='9' align=right>Bunga (Rekening Koran)</td>
							<td align=right>".number_format($totBungaRealisasi['bungarealisasi'])."</td>
						</tr>";
						$tab.="<tr>
							<td colspan='9' align=right>Variance</td>
							<td align=right>".number_format($totBungaRealisasi['bungarealisasi']-$Alltotalbunga)."</td>
						</tr>";
				}
				$tab.="</table>";	
			

			if($tipe=='excel'){
				$tglSkrg=date("YmdHis");
				$nop_="summary_loan_kisi_per_pencairan_".$notransaksi."__".$tglSkrg;
				if(strlen($tab)>0)
				{
						if ($handle = opendir('tempExcel')) {
								while (false !== ($file = readdir($handle))) {
								if ($file != "." && $file != ".." && $file != "index.html") {
										@unlink('tempExcel/'.$file);
								}
								}	
								closedir($handle);
						}
						$handle=fopen("tempExcel/".$nop_.".xls",'w');
						if(!fwrite($handle,$tab))
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
		}else if($tipe=='pdf'){
			$dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("summary loan Kisi Per Pencairan".$notransaksi,array("Attachment"=>0));
		}else{
			echo $tab;
		}
	break;
	
}














// $noakunnotransaksi = checkPostGet('noakun', '');
// $bank = checkPostGet('bank', '');
// $tipe = checkPostGet('tipe', '');
// $rek = checkPostGet('rek', '');

// $tgl1=tanggalsystemn(checkPostGet('tgl1',''));
// $tgl2=tanggalsystemn(checkPostGet('tgl2',''));

// if($tgl1=='--'){
//     $tgl1='';
// }
// if($tgl2=='--'){
//     $tgl2='';
// }
// $wherebank="";
// if($tgl1==''  or $tgl2==''){
// 	exit("Warning:Tanggal kosong");
// }
// if($bank!='') {
// 	$wherebank=" and rekening='".$bank."'";
// }
// $whererek="";
// if($rek!=''){
// 	$whererek=" and a.rekening = '".$rek."'";
// }else{
// 	$whererek=" and a.rekening in (select noakun from keu_5akunbank where pemilik='".$unit."')";
// }


// $per1=substr($tgl1,0,7);
// $tglawalbln=$per1.'-01';
// $per1=str_replace('-','',$per1);
// $dtper1=substr($per1,4,2);
// $sawal=$tawalkm=$tawalkk=0;
// $sawall=$tawalkmm=$tawalkkk=0;

// switch ($method) {
// ######PREVIEW
//     case 'preview':
	
// 		if($excel=='pdf' or $tipe=='pdf'){
// 			$border=1;
// 		} else {
// 			$border=0;
// 		}
		
// 		$stream.="<table class=sortable cellspacing=1 border='".$border."' width=100%>";
// 		$stream.="
// 			<thead>
// 				<tr class=rowheader>
// 					<td align='center'>".$_SESSION['lang']['nourut']."</td>
// 					<td align='center'>".$_SESSION['lang']['tanggal']."</td>
// 					<td align='center'>".$_SESSION['lang']['notransaksi']."</td>
// 					<td align='center'>".$_SESSION['lang']['keterangan']."</td>
// 					<td align='center'>".$_SESSION['lang']['penerimaan']."</td>
// 					<td align='center'>".$_SESSION['lang']['pengeluaran']."</td>
// 					<td align='center'>".$_SESSION['lang']['saldo']."</td>
// 				</tr>
// 			</thead>
// 		 <tbody>";
		 
		
// 		#= data 
// 		$str="select * from ".$dbname.".keu_kasbankht where kodeorg='".$unit."' and tanggal between '".$tgl1."' and '".$tgl2."' 
// 			and noakun='".$noakun."' ".$wherebank." and posting='1' order by tanggal asc,tipetransaksi asc,notransaksi asc";	
// 		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 		$res->setFetchMode(PDO::FETCH_ASSOC);
// 		while($bar=$res->fetch()){
// 			$dttgl[$bar['tanggal']]=$bar['tanggal'];
// 			$dtnotran[$bar['notransaksi']]=$bar['notransaksi'];
// 			$lsnotran[$bar['tanggal']][$bar['notransaksi']]=$bar['notransaksi'];
// 			$ket[$bar['tanggal']][$bar['notransaksi']]=$bar['keterangan'];
// 			if($bar['tipetransaksi']=='M'){
// 				$km[$bar['tanggal']][$bar['notransaksi']]=$bar['jumlah'];
// 			} else {
// 				$kk[$bar['tanggal']][$bar['notransaksi']]=$bar['jumlah'];
// 			}
// 		}
		
// 		@$cdata=count($dtnotran);
// 		if($cdata<1 or $cdata==''){
// 			exit("Warning:Tidak ada transaksi");
// 		}
		
		
		
// 		#= bentuk sawal
// 		#= ambil dari keu_saldo (untuk kas) / keu_keu_saldobank (jika kolom bank terisi)
// 		if ($noakun=='1110101' or $noakun=='1111101') {
// 			if($bank!='') {
// 				$str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobank where 
// 				kodeorg='".$unit."' and periode='".$per1."' and norek='".$bank."'";	
// 			}else{
// 				$str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobank where 
// 				kodeorg='".$unit."' and periode='".$per1."'";	
// 			}
// 		}else{
// 			$str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobulanan where 
// 			kodeorg='".$unit."' and periode='".$per1."' and noakun='".$noakun."'";
// 		}

// 		// if($bank!='') {
// 		// 	$str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobank where 
// 		// 	kodeorg='".$unit."' and periode='".$per1."' and norek='".$bank."'";	
// 		// }else{
// 		// 	$str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobulanan where 
// 		// 	kodeorg='".$unit."' and periode='".$per1."' and noakun='".$noakun."'";
// 		// }
// 		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 		$res->setFetchMode(PDO::FETCH_ASSOC);
// 			$bar=$res->fetch();
// 			$sawal=$bar['jumlah'];

			
		
// 		#= ambil transaksi s/d tanggal pertama u/ mendapatkan real saldo awal
// 		#= if disini mencegah jika ada transaksi di tgl 1, agar maka saldo awal tidak menjumlah, melainkan mengambil dr awal
// 		if($tgl1!=$tglawalbln){
// 			$str="select * from ".$dbname.".keu_kasbankht where kodeorg='".$unit."' and tanggal between '".$tglawalbln."' and '".$tgl1."' 
// 				and noakun='".$noakun."'  and posting='1' order by tanggal asc,tipetransaksi asc,notransaksi asc";	
// 			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 			$res->setFetchMode(PDO::FETCH_ASSOC);
// 			while($bar=$res->fetch()){
// 				if($bar['tipetransaksi']=='M'){
// 					@$tawalkm+=$bar['jumlah'];
// 				} else {
// 					@$tawalkk+=$bar['jumlah'];
// 				}
// 			}
// 		}
		
// 		$sawal=$sawal+$tawalkm-$tawalkk;


		
// 		#= sawal
		
// 		$stream.="<tr class=rowcontent>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td>Saldo Awal ".tanggalnormal($tgl1)."</td>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td align=right><b>".number_format($sawal,2)."</b></td>";
// 		$stream.="</tr>";
		 
		
	
// 		#= data
// 		foreach($dttgl as $tgl){
// 			foreach($dtnotran as $notran){
// 				if($lsnotran[$tgl][$notran]){
// 					@$no+=1;
// 					$stream.="<tr class=rowcontent>";
// 						$stream.="<td>".$no."</td>";
// 						$stream.="<td>".tanggalnormal($tgl)."</td>";
// 						$stream.="<td>".$notran."</td>";
// 						$stream.="<td style:width=10%>".$ket[$tgl][$notran]."</td>";
// 						$stream.="<td align=right>".number_format($km[$tgl][$notran],2)."</td>";
// 						$stream.="<td align=right>".number_format($kk[$tgl][$notran],2)."</td>";
// 							$salak=$sawal+$km[$tgl][$notran]-$kk[$tgl][$notran];
// 						$stream.="<td align=right>".number_format($salak,2)."</td>";
// 							$sawal=$salak;
// 							@$tkm+=$km[$tgl][$notran];
// 							@$tkk+=$kk[$tgl][$notran];
// 					$stream.="</tr>";
// 				}
// 			}
// 		}
		
// 		$stream.="<tr class=rowcontent>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td>Jumlah</td>";
// 			$stream.="<td align=right>".number_format($tkm,2)."</td>";
// 			$stream.="<td align=right>".number_format($tkk,2)."</td>";
// 			$stream.="<td align=right><b>".number_format($salak,2)."</td>";
// 		$stream.="</tr>";
		
		

// 		$stream.="
// 		 </tbody>
// 			 </table>";
			
			
		
		
		
// 		if($tipe=='excel'){
// 			$tglSkrg=date("Ymd");
// 			$nop_="laporan_kas";
// 			if(strlen($stream)>0)
// 			{
// 					if ($handle = opendir('tempExcel')) {
// 							while (false !== ($file = readdir($handle))) {
// 							if ($file != "." && $file != ".." && $file != "index.html") {
// 									@unlink('tempExcel/'.$file);
// 							}
// 							}	
// 							closedir($handle);
// 					}
// 					$handle=fopen("tempExcel/".$nop_.".xls",'w');
// 					if(!fwrite($handle,$stream))
// 					{
// 							echo "<script language=javascript1.2>
// 							parent.window.alert('Can't convert to excel format');
// 							</script>";
// 							exit;
// 					}
// 					else
// 					{
// 							echo "<script language=javascript1.2>
// 							window.location='tempExcel/".$nop_.".xls';
// 							</script>";
// 					}
// 					fclose($handle);
// 			}     
// 		}else if($tipe=='pdf'){
// 			$dompdf = new Dompdf();
//             $dompdf->loadHtml($stream);
//             $dompdf->setPaper('A4', 'landscape');
//             $dompdf->render();
//             $dompdf->stream("form survey",array("Attachment"=>0));
// 		}else{
			
// 			echo $stream;
// 		}
// 	break;

//     case 'getbank':
// 		$optbank="<option value=''>".$_SESSION['lang']['all']."</option>";
// 		if($noakun=='1110101' or $noakun=='1111101'){	
// 			if ($noakun=='1110101') {
// 				$whr=" and matauang='IDR'";
// 			}else{
// 				$whr=" and matauang!='IDR'";
// 			}

// 			$str="select * from ".$dbname.".keu_5akunbank where pemilik='".$unit."'".$whr;
// 			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 			$res->setFetchMode(PDO::FETCH_ASSOC);
// 			while($bar=$res->fetch()){
// 				$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$bar['namabank']."'");
// 				$optbank.="<option value=".$bar['noakun'].">".$bar['pemilik'].":".$optNamaBank[$bar['namabank']]." ".$bar['rekening']."</option>";
// 			}
// 		}
// 		echo $optbank;
		
//     break;

//     case 'getrekening':
//     $optrek="<option value=''>".$_SESSION['lang']['all']."</option>";
//     $str="select a.noakun,a.rekening, b.namabank, a.pemilik from ".$dbname.".keu_5akunbank a
//     left join keu_5daftarbank b on a.namabank = b.kodebank
//     where a.pemilik='".$unit."'";
//     $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//     $res->setFetchMode(PDO::FETCH_ASSOC);
//     while($bar=$res->fetch()){
//     	$optrek.="<option value=".$bar['noakun'].">".$bar['pemilik'].":".$bar['namabank']." - ".$bar['noakun']."</option>";
//     }
//     echo $optrek;
    
//     break;
	
// 	#========================= KK ====================================
	
// 	case 'previewkk':
	
// 		if($excel=='pdf' or $tipe=='pdf'){
// 			$border=1;
// 		} else {
// 			$border=0;
// 		}
		
// 		$stream.="<table class=sortable cellspacing=1 border='".$border."' width=100%>";
// 		$stream.="
// 			<thead>
// 				<tr class=rowheader>
// 					<td align='center'>".$_SESSION['lang']['nourut']."</td>
// 					<td align='center'>".$_SESSION['lang']['tanggal']."</td>
// 					<td align='center'>".$_SESSION['lang']['notransaksi']."</td>
// 					<td align='center'>".$_SESSION['lang']['keterangan']."</td>
// 					<td align='center'>".$_SESSION['lang']['penerimaan']."</td>
// 					<td align='center'>".$_SESSION['lang']['pengeluaran']."</td>
// 					<td align='center'>".$_SESSION['lang']['saldo']."</td>
// 				</tr>
// 			</thead>
// 		 <tbody>";
		 
		
// 		#= data 
// 		$str="select * from ".$dbname.".keu_kaskecil_vw where unit='".$unit."' and posting='1' and tanggal between '".$tgl1."' and '".$tgl2."' 
// 			order by tanggal asc,tipe desc,notransaksi asc,createtime asc";	
// 		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 		$res->setFetchMode(PDO::FETCH_ASSOC);
// 		while($bar=$res->fetch()){
// 			$dttgl[$bar['tanggal']]=$bar['tanggal'];
// 			$dtnotran[$bar['notransaksi']]=$bar['notransaksi'];
// 			$dtnourut[$bar['nourut']]=$bar['nourut'];
// 			$lsnotran[$bar['tanggal']][$bar['notransaksi']][$bar['nourut']]=$bar['notransaksi'];
// 			$ket[$bar['tanggal']][$bar['notransaksi']][$bar['nourut']]=$bar['keterangan2'];
// 			if($bar['tipe']=='M'){
// 				$km[$bar['tanggal']][$bar['notransaksi']][$bar['nourut']]=$bar['jumlah'];
// 			} else {
// 				$kk[$bar['tanggal']][$bar['notransaksi']][$bar['nourut']]=$bar['jumlah'];
// 			}
// 		}
	
// 		@$cdata=count($dtnotran);
// 		if($cdata<1 or $cdata==''){
// 			exit("Warning:Tidak ada transaksi");
// 		}
		
// 		#= bentuk sawal
// 		#= ambil dari keu_saldo (untuk kas) / keu_keu_saldobank (jika kolom bank terisi)

// 		// $str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobulanan where 
// 		// kodeorg='".$unit."' and periode='".$per1."' and noakun='".$noakun."'";
// 		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 		// $res->setFetchMode(PDO::FETCH_ASSOC);
// 		// 	$bar=$res->fetch();
// 		// 	$sawal=$bar['jumlah'];

// 		$periodekaskecil=substr($per1,0,4)."-".substr($per1,4,2);
// 		$str="select saldoawal from ".$dbname.".keu_5kaskecil where 
// 		unit='".$unit."' and periode='".$periodekaskecil."' and noakun='".$noakun."'";
// 		// exit('Warning : '.$str);
// 		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 		$res->setFetchMode(PDO::FETCH_ASSOC);
// 			$bar=$res->fetch();
// 			$sawal=$bar['saldoawal'];
			
		
// 		#= ambil transaksi s/d tanggal pertama u/ mendapatkan real saldo awal
// 		#= if disini mencegah jika ada transaksi di tgl 1, agar maka saldo awal tidak menjumlah, melainkan mengambil dr awal
// 		if($tgl1!=$tglawalbln){
// 			$str="select * from ".$dbname.".keu_kasbankht where kodeorg='".$unit."' and tanggal between '".$tglawalbln."' and '".$tgl1."' 
// 				and noakun='".$noakun."'  and posting='1' order by tanggal asc,tipetransaksi asc,notransaksi asc";	
// 			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 			$res->setFetchMode(PDO::FETCH_ASSOC);
// 			while($bar=$res->fetch()){
// 				if($bar['tipetransaksi']=='M'){
// 					@$tawalkm+=$bar['jumlah'];
// 				} else {
// 					@$tawalkk+=$bar['jumlah'];
// 				}
// 			}
// 		}
		
// 		$sawal=$sawal+$tawalkm-$tawalkk;
		
// 		#= sawal
		
// 		$stream.="<tr class=rowcontent>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td>Saldo Awal ".tanggalnormal($tgl1)."</td>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td align=right><b>".number_format($sawal,2)."</b></td>";
// 		$stream.="</tr>";
		 
		
	
// 		#= data
// 		foreach($dttgl as $tgl){
// 			foreach($dtnotran as $notran){
// 				foreach($dtnourut as $nourut){
// 					if($lsnotran[$tgl][$notran][$nourut]){
// 						@$no+=1;
// 						$stream.="<tr class=rowcontent>";
// 							$stream.="<td>".$no."</td>";
// 							$stream.="<td>".tanggalnormal($tgl)."</td>";
// 							$stream.="<td>".$notran."</td>";
// 							$stream.="<td style:width=10%>".$ket[$tgl][$notran][$nourut]."</td>";
// 							$stream.="<td align=right>".number_format($km[$tgl][$notran][$nourut],2)."</td>";
// 							$stream.="<td align=right>".number_format($kk[$tgl][$notran][$nourut],2)."</td>";

// 						        $whrjenis="notransaksi='".$notran."'";
// 						        $optjenis=makeOption($dbname,'keu_kaskecil_vw','notransaksi,jenis',$whrjenis);
// 						        if ($optjenis[$notran]!=1) {
// 						        	$salak=$sawal+$km[$tgl][$notran][$nourut]-$kk[$tgl][$notran][$nourut];
// 						        }

// 							$stream.="<td align=right>".number_format($salak,2)."</td>";
// 								$sawal=$salak;
// 								@$tkm+=$km[$tgl][$notran][$nourut];
// 								@$tkk+=$kk[$tgl][$notran][$nourut];
// 						$stream.="</tr>";
// 					}
// 				}					
// 			}
// 		}
		
// 		$stream.="<tr class=rowcontent>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td></td>";
// 			$stream.="<td>Jumlah</td>";
// 			$stream.="<td align=right>".number_format($tkm,2)."</td>";
// 			$stream.="<td align=right>".number_format($tkk,2)."</td>";
// 			$stream.="<td align=right><b>".number_format($salak,2)."</td>";
// 		$stream.="</tr>";
// 		$stream.="
// 		 </tbody>
// 			 </table>";
			
		
// 		if($tipe=='excel'){
// 			$tglSkrg=date("Ymd");
// 			$nop_="laporan_kaskecil";
// 			if(strlen($stream)>0)
// 			{
// 					if ($handle = opendir('tempExcel')) {
// 							while (false !== ($file = readdir($handle))) {
// 							if ($file != "." && $file != ".." && $file != "index.html") {
// 									@unlink('tempExcel/'.$file);
// 							}
// 							}	
// 							closedir($handle);
// 					}
// 					$handle=fopen("tempExcel/".$nop_.".xls",'w');
// 					if(!fwrite($handle,$stream))
// 					{
// 							echo "<script language=javascript1.2>
// 							parent.window.alert('Can't convert to excel format');
// 							</script>";
// 							exit;
// 					}
// 					else
// 					{
// 							echo "<script language=javascript1.2>
// 							window.location='tempExcel/".$nop_.".xls';
// 							</script>";
// 					}
// 					fclose($handle);
// 			}     
// 		}else if($tipe=='pdf'){
// 			$dompdf = new Dompdf();
//             $dompdf->loadHtml($stream);
//             $dompdf->setPaper('A4', 'landscape');
//             $dompdf->render();
//             $dompdf->stream("form survey",array("Attachment"=>0));
// 		}else{
			
// 			echo $stream;
// 		}
// 	break;


// 	case 'previewsum':
	
// 		if($excel=='pdf' or $tipe=='pdf'){
// 			$border=1;
// 		} else {
// 			$border=0;
// 		}
// 		$stream.="<table class=sortable cellspacing=1 border='".$border."' width=100%>";
// 		$stream.="
// 			<thead>
// 				<tr class=rowheader>
// 					<td align='center'>".$_SESSION['lang']['nourut']."</td>
// 					<td align='center'>".$_SESSION['lang']['rekening']."</td>
// 					<td align='center'>".$_SESSION['lang']['saldo']." awal</td>
// 					<td align='center'>".$_SESSION['lang']['penerimaan']."</td>
// 					<td align='center'>".$_SESSION['lang']['pengeluaran']."</td>
// 					<td align='center'>".$_SESSION['lang']['saldo']." akhir</td>
// 				</tr>
// 			</thead>
// 		 <tbody>";

// 		 $str="select a.rekening, sum(a.jumlah) as jumlah,a.tipetransaksi,a.kurs,b.namabank, c.namabank as bank, b.pemilik  
// 		 from ".$dbname.".keu_kasbankht a
// 		 left join keu_5akunbank b on b.noakun = a.rekening 
// 		 left join keu_5daftarbank c on c.kodebank = b.namabank
// 		 where a.tanggal between '".$tgl1."' and '".$tgl2."' and a.kodeorg = '".$unit."' ".$whererek." 
// 		 group by a.rekening";
// 		 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 		 $res->setFetchMode(PDO::FETCH_ASSOC);
// 		 while($bar=$res->fetch()){
// 		 	$namarekening[$bar['bank']]=$bar['rekening'];
// 		 	if($bar['tipetransaksi']=='M'){
// 				$summ[$bar['rekening']]=$bar['jumlah'];
// 			} else {
// 				$sumk[$bar['rekening']]=$bar['jumlah'];
// 			}
// 		}

// 		@$cdata=count($namarekening);
// 		if($cdata<1 or $cdata==''){
// 			exit("Warning:Tidak ada transaksi");
// 		}

// 		#= bentuk sawal
// 		#= ambil dari keu_saldo (untuk kas) / keu_keu_saldobank (jika kolom bank terisi)

// 			if($rek!='') {
// 				$str="select sum(awal".$dtper1.") as jumlah, norek from ".$dbname.".keu_saldobank where 
// 				kodeorg='".$unit."' and periode='".$per1."' and norek='".$rek."'";	
// 			}else{
// 				$str="select sum(awal".$dtper1.") as jumlah, norek from ".$dbname.".keu_saldobank where 
// 				kodeorg='".$unit."' and periode='".$per1."'";	
// 			}
// 		// if($bank!='') {
// 		// 	$str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobank where 
// 		// 	kodeorg='".$unit."' and periode='".$per1."' and norek='".$bank."'";	
// 		// }else{
// 		// 	$str="select sum(awal".$dtper1.") as jumlah from ".$dbname.".keu_saldobulanan where 
// 		// 	kodeorg='".$unit."' and periode='".$per1."' and noakun='".$noakun."'";
// 		// }
// 		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 		$res->setFetchMode(PDO::FETCH_ASSOC);
// 			while($bar=$res->fetch()){
// 			$sawalll[$bar['norek']]=$bar['jumlah'];

// 		}

			
			
		
// 		#= ambil transaksi s/d tanggal pertama u/ mendapatkan real saldo awal
// 		#= if disini mencegah jika ada transaksi di tgl 1, agar maka saldo awal tidak menjumlah, melainkan mengambil dr awal
// 		if($tgl1!=$tglawalbln){
// 			$str="select * from ".$dbname.".keu_kasbankht where kodeorg='".$unit."' and tanggal between '".$tglawalbln."' and '".$tgl1."' 
// 				and noakun='".$noakun."' order by tanggal asc,tipetransaksi asc,notransaksi asc";	
// 			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 			$res->setFetchMode(PDO::FETCH_ASSOC);
// 			while($bar=$res->fetch()){
// 				if($bar['tipetransaksi']=='M'){
// 					@$tawalkmm+=$bar['jumlah'];
// 				} else {
// 					@$tawalkkk+=$bar['jumlah'];
// 				}
// 			}
// 		}

		
// 		@$sawalll=$sawall+$tawalkmm-$tawalkkk;
		
// 		#= sawal
		
// 			foreach ($namarekening as $keyrek => $valrek) {
		
// 		$salakk=0;
// 			@$salakk+=$sawalll+$summ[$valrek]-$sumk[$valrek];
// 			@$no+=1;
// 			$stream.="<tr class=rowcontent>";
// 			$stream.="<td align='center'>".$no."</td>";
// 			$stream.="<td align='left'>".$keyrek." - ".$valrek."</td>";
// 			$stream.="<td align='right'>".number_format($sawalll[$valrek],2)."</td>";
// 			$stream.="<td align='right'>".number_format($summ[$valrek],2)."</td>";
// 			$stream.="<td align='right'>".number_format($sumk[$valrek],2)."</td>";
// 			$stream.="<td align='right'>".number_format($salakk,2)."</td>";
// 			@$tsalakk+=$salakk;
// 			@$tkmm+=$summ[$valrek];
// 			@$tkkk+=$sumk[$valrek];
// 			}
// 			$stream.="<tr class=rowcontent>";
// 			$stream.="<td></td>";
			
// 			$stream.="<td>Jumlah</td>";
// 			$stream.="<td></td>";
// 			$stream.="<td align=right>".number_format($tkmm,2)."</td>";
// 			$stream.="<td align=right>".number_format($tkkk,2)."</td>";
// 			$stream.="<td align=right><b>".number_format($tsalakk,2)."</td>";
// 			$stream.="</tr>";

// 		$stream.="
// 		 </tbody>
// 			 </table>";
			
			
// 		if($tipe=='excel'){
// 			$tglSkrg=date("Ymd");
// 			$nop_="Summary KasBank";
// 			if(strlen($stream)>0)
// 			{
// 					if ($handle = opendir('tempExcel')) {
// 							while (false !== ($file = readdir($handle))) {
// 							if ($file != "." && $file != ".." && $file != "index.html") {
// 									@unlink('tempExcel/'.$file);
// 							}
// 							}	
// 							closedir($handle);
// 					}
// 					$handle=fopen("tempExcel/".$nop_.".xls",'w');
// 					if(!fwrite($handle,$stream))
// 					{
// 							echo "<script language=javascript1.2>
// 							parent.window.alert('Can't convert to excel format');
// 							</script>";
// 							exit;
// 					}
// 					else
// 					{
// 							echo "<script language=javascript1.2>
// 							window.location='tempExcel/".$nop_.".xls';
// 							</script>";
// 					}
// 					fclose($handle);
// 			}     
// 		}else if($tipe=='pdf'){
// 			$dompdf = new Dompdf();
//             $dompdf->loadHtml($stream);
//             $dompdf->setPaper('A4', 'landscape');
//             $dompdf->render();
//             $dompdf->stream("form survey",array("Attachment"=>0));
// 		}else{
			
// 			echo $stream;
// 		}
// 	break;
	
	
	
	
	
	
	
	
	
	
	
	
// }
?>