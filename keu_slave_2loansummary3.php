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
		#BRI Satu Pokok
		$tab="";
		$daftarjumlahhari=  makeOption($dbname, 'keu_5daftarbank', 'kodebank,jumlah_hari');
		$str="SELECT a.*,b.namabank as kodebank,b.rekening as noaccount from ".$dbname.".keu_pmpeminjamanht a 
		left join keu_5akunbank	b on a.noakun = b.noakun where a.notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$pinjaman=$res->fetch();
		$noAccount=$pinjaman['noaccount'];
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
		$tanggaljatuhtempo = $pinjaman['jatuhtempo'];
		

		#ambil nilai pokok per pencairan atau per noloan
		$sPokok="select noloan,rupiahangsuran as angPokok,bulanke as tenor,notransaksi from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and periode='".$periode."' ";
		//echo $sPokok;
		$rPokok=fetchData($sPokok);
		foreach ($rPokok as $key => $val) {
			$rupPokokDisplay[$val['notransaksi']]=$val['angPokok'];#angka untuk kepentingan display
			$ruptenDisplay[$val['notransaksi']]=$val['tenor'];#angka tenor untuk kepentingan display
		}
		$jmlhari = 0;
		if(isset($daftarjumlahhari[$pinjaman['kodebank']])){
			$jmlhari = $daftarjumlahhari[$pinjaman['kodebank']];
		}
		$tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable>
						<thead>
							<tr>
							<td colspan=14 align=center>".$_SESSION['lang']['angsuran']."</td>
							</tr>
							<tr class=rowheader>
								<td align=center>No.</td>
								<td align=center>No. Account</td>
								<td align=center>Outstanding</td>
								<td align=center>Pencairan</td>
								<td align=center>Sisa Hutang</td>
								<td align=center>Pembayaran Pokok</td>
								<td align=center>Tanggal Pencairan</td>
								<td align=center>".$_SESSION['lang']['sukubunga']."</td>
								<td align=center colspan=2>".$_SESSION['lang']['periode']."</td>
								<td align=center>".$_SESSION['lang']['harihutang']."</td>
								<td align=center>Bunga</td>
								<td align=center>Pokok+Bunga</td>
								<td align=center>Jatuh Tempo Fasilitas</td>
							</tr>	
						</thead>";
		foreach ($listPeriode as $key => $prd) {
				if($prd>$periode){
					continue;
				}
				// if periode 2012-11
				$periodedate = $prd."-".$tanggaljatuhtempo;#bulan berjalan
				$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
				$periodedate2 = date("Y-m-d",strtotime("+2 Month",strtotime($periodedate1)));

				$sPokok="select sum(rupiahangsuran) as angPokok from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and periode<='".substr($periodedate1,0,7)."'";
				$rPokok=fetchData($sPokok);

				foreach ($rPokok as $key => $val) {
					$rupPokok=$val['angPokok'];#angka untuk pengurang hutang
				}
						$sData="select * from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."' and tanggal<='".$periodedate."'";
						//echo $sData;
						$rData=fetchData($sData);
						$totRowData=$rwSpn=count($rData);
						$cairOutstanding=0;
						$lstRpPencairan=$outStand=array();
						$totOustanding=0;
						$nilSblmnya=0;
						$nourt=0;
						$tempLoan="";
						if(!empty($rData)){
							$statAkhirBkn=0;#untuk cek apakah ada pencairan di akhir loan
							foreach ($rData as $key => $val){
								if($totRowData!=0){
									$totRowData-=1;
								}
								$displaycairOutstand+=$val['jumlah'];#untuk display
								$lstRpPencairan[$val['noloan']]=$val['jumlah'];
								if(($val['tanggal']<=$periodedate)&&($val['tanggal']>=$periodedate1)){
									$periodedate1_ = $val['tanggal'];
									$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));	
									$totalday_ = datediff($periodedate1_,$periodedate2_);
									$outStand[$val['noloan']]=$val['jumlah'];
									$bnykoutstand+=1;
									if($totRowData==0){
										$statAkhirBkn=1;
										$lastLoan=$key;
									}
								}else{
									$periodedate1_ = $periodedate1;
									$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
									$totalday_ = datediff($periodedate1_,$periodedate2_);
									$totOustanding+=$val['jumlah'];
									$displayTotOustand+=$val['jumlah'];
									$testasaja+=1;
								}
							}
							$totRowData=count($rData);
							#cari nilai untuk bunga display
							foreach ($rData as $key => $val){
								if($totRowData!=0){
									$totRowData-=1;
								}
								#cari bunga yang aktif start
								if($val['tgl_jatuhtempo']!='00'){
									    #jika jatuh temponya memiliki tanggal berbeda dengan header
										$periodedate = $prd."-".$val['tgl_jatuhtempo'];#bulan berjalan
										$periodedate1=date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
										$tgljatuhtempoBri[$val['noloan']]=$prd."-".$val['tgl_jatuhtempo'];#bulan berjalan
								}else{
									$periodedate = $prd."-".$tanggaljatuhtempo;#bulan berjalan
									$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
									$tgljatuhtempoBri[$val['noloan']]=$prd."-".$tanggaljatuhtempo;#bulan berjalan
								}
								#cari tanggal bunga dibawah range
								$rBungaKisi=$arrBunga=array();
								$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <'".$periodedate1."' and notransaksipm='".$notransaksi."' order by periode asc";
								//echo $sBungaKisi;
								$rBungaKisi=fetchData($sBungaKisi);
								if(count($rBungaKisi)!=0){
									foreach($rBungaKisi as $row=>$isi){
										if($periodedate1>$isi['periode']){
											$arrBunga['tanggal']=array(); #reset selalu hanya satu paling terakhir
											$arrBunga['bunga']=array();#reset selalu hanya satu paling terakhir
											$isi['periode']=$periodedate1;
										}
										if($val['tanggal']>$isi['periode']){
											continue;
										}
										$arrBunga['tanggal'][]=$isi['periode'];
										$arrBunga['bunga'][]=$isi['nilai'];	
									}
								}
								$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <'".$periodedate."' and notransaksipm='".$notransaksi."' order by periode asc";
								//echo $sBungaKisi;
								$rBungaKisi=fetchData($sBungaKisi);
								if(count($rBungaKisi)!=0){
									foreach($rBungaKisi as $row=>$isi){
										if(count($arrBunga['tanggal'])!=0){
											$crTgl=array_search($isi['periode'], $arrBunga['tanggal']);
											if($arrBunga['tanggal'][$crTgl]==$isi['periode']){
												continue;
											}	
										}
										if($periodedate1>$isi['periode']){
											continue;
										}
										if($val['tanggal']>$isi['periode']){
											continue;
										}
										 
										$arrBunga['tanggal'][]=$isi['periode'];
										$arrBunga['bunga'][]=$isi['nilai'];
									}
								}
								#filter tanggal klo1 ada yang sama
								foreach ($arrBunga['tanggal'] as $keydt => $valTanggal) {
									if($valTanggal==$arrBunga['tanggal'][$keydt+1]){
										unset($arrBunga['tanggal'][$keydt+1]);
									}
								}
								$totalday_ = array();
								$totalbunga=0;
								
								if(count($arrBunga['tanggal'])<2){	
									if(($val['tanggal']<=$periodedate)&&($val['tanggal']>=$periodedate1)){
										// jika tanggal lebih kecil atau lebih besar dari tangal jatuh tempo tiap bulannya
										//maka jumlah hari bukan sebulan alias harus proporsi
										$periodedate1_ = $val['tanggal'];
										$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));	
										$totalday_ = datediff($periodedate1_,$periodedate2_);
										//$outStand[$val['noloan']]=$val['jumlah'];
									}else{
										$periodedate1_ = $periodedate1;
										$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
										$totalday_ = datediff($periodedate1_,$periodedate2_);
										//$totOustanding+=$val['jumlah'];
									}
									$str=" select nilai from ".$dbname.".keu_pmsukubunga where periode<'".$periodedate."' and notransaksipm='".$notransaksi."' order by periode desc limit 1";
									// echo $str;
									$rSukuB=fetchData($str);
									$sukub=$rSukuB[0]['nilai'];
									if(isset($outStand[$val['noloan']])){

										$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($sukub/100))/(int)$jmlhari;	
										$tampilan.="(((".$outStand[$val['noloan']].")*".$totalday_['days_total']."*(".$sukub."/100)))/".(int)$jmlhari."<br />";
									}else{
										if($totRowData==0){
											$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($sukub/100)))/(int)$jmlhari;	
											$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$sukub."/100)))/".(int)$jmlhari." gak ada statAkhirBkn \n\n\n";
										}
									}
									
									if($statAkhirBkn==1){
										$itung=$rwSpn-$testasaja;
										if($key==$itung){
											$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($sukub/100)))/(int)$jmlhari;	
											$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$sukub."/100)))/".(int)$jmlhari." adda statAkhirBkn \n\n\n";
										}
									}
									$grandTotBungaDisplay+=$totalbunga;
									
								}else{
									$rowspanDt=count($arrBunga['tanggal']);
									$totalday_ = array();
									$totHari=count($arrBunga['tanggal'])-1;
									foreach ($arrBunga['tanggal'] as $key2 => $val2){
										if($totHari==$key2){#jika tothari sama dengan index array,mencari index terakhir
											$indSblm=$totHari-1;
											if($tglBerikutNya==$val2){
												$periodedate1_ = $tglBerikutNya;
												$val2 = $periodedate;	
											}else{
												//exit('warning:'.$tglBerikutNya."__".$val2);
												if($tglBerikutNya<$val2){
													$periodedate1_ = $tglBerikutNya;	
													$val2 = $periodedate;
												}else{
													$periodedate1_ = $arrBunga['tanggal'][$indSblm];		
													$val2 = $periodedate;
												}
												
											}
											
											if($periodedate!=$val2){#jika tanggal terakhir tidak sama dengan nilai array terakhir maka diisi dengan tanggal akhir
												$val2=$periodedate;
											}
										}else{
											if($key2!=0){
												$periodedate1_ = $tglBerikutNya;	
											}else{
												$periodedate1_ = $periodedate1;	
												if($periodedate1>$val2){
													$val2=$arrBunga['tanggal'][($key2+1)];
													$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
												}
												else if($periodedate1==$val2){#jika tanggal bunga sama dengan tanggal mulai continue row dikurangi 1
													$val2=$arrBunga['tanggal'][($key2+1)];
													$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
													$val2=$arrBunga['tanggal'][($key2+1)];
													$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
													if($periodedate==$val2){
														$val2 = date("Y-m-d",strtotime("-1 Days",strtotime($val2)));
														$tglBerikutNya=$val2;
													}
												}else{
													$tglBerikutNya=$val2;
												}
											}						
											//$tglBerikutNya=$val;
										}
										$periodedate2_ = date("Y-m-d",strtotime($val2));
										//$periodedate2_=date("Y-m-d",strtotime($val));
										$totalday_ = datediff($periodedate1_,$periodedate2_);
										$nourt+=1;
										if($val['jumlah']!=$nilSblmnya){
											$nilSblmnya=$val['jumlah'];
											$cairOutstanding+=$val['jumlah'];		
											//$totOustanding=$cairOutstanding;	
										}
										 
										if(isset($outStand[$val['noloan']])){
											$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100))/(int)$jmlhari;	
											$tampilan.="(((".$outStand[$val['noloan']]."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
										}else{
											if($totRowData==0){
												$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;
												$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
											}
										}
										if($statAkhirBkn==1){
											$itung=(($rwSpn-1)-$testasaja);
											if($key==$itung){
												$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;	
												$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." adda statAkhirBkn \n\n\n";
											}
										}
										 
										$grandTotBungaDisplay+=$totalbunga;
									}
								}
							}
						}
						#display kalkulasinya
						if(!empty($rData)){
							$totRowData=count($rData);
							$nourt=0;
							$cairOutstanding=0;
							foreach ($rData as $key => $val) {
								if($totRowData!=0){
									$totRowData-=1;
								}
								#cari bunga yang aktif start
								if($val['tgl_jatuhtempo']!='00'){#jika jatuh temponya memiliki tanggal berbeda dengan header
										$periodedate = $prd."-".$val['tgl_jatuhtempo'];#bulan berjalan
										$periodedate1=date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
										$tgljatuhtempoBri[$val['noloan']]=$prd."-".$val['tgl_jatuhtempo'];#bulan berjalan
								}else{
									$periodedate = $prd."-".$tanggaljatuhtempo;#bulan berjalan
									$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
									$tgljatuhtempoBri[$val['noloan']]=$prd."-".$tanggaljatuhtempo;#bulan berjalan
								}
								#cari tanggal bunga dibawah range
								$rBungaKisi=$arrBunga=array();
								$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <'".$periodedate1."' and notransaksipm='".$notransaksi."' order by periode asc";
								//echo $sBungaKisi;
								$rBungaKisi=fetchData($sBungaKisi);
								if(count($rBungaKisi)!=0){
									foreach($rBungaKisi as $row=>$isi){
										if($periodedate1>$isi['periode']){
											$arrBunga['tanggal']=array(); #reset selalu hanya satu paling terakhir
											$arrBunga['bunga']=array();#reset selalu hanya satu paling terakhir
											$isi['periode']=$periodedate1;
										}
										if($val['tanggal']>$isi['periode']){
											continue;
										}
										$arrBunga['tanggal'][]=$isi['periode'];
										$arrBunga['bunga'][]=$isi['nilai'];	
									}
								}
								$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <'".$periodedate."' and notransaksipm='".$notransaksi."' order by periode asc";
								//echo $sBungaKisi;
								$rBungaKisi=fetchData($sBungaKisi);
								if(count($rBungaKisi)!=0){
									foreach($rBungaKisi as $row=>$isi){
										if(count($arrBunga['tanggal'])!=0){
											$crTgl=array_search($isi['periode'], $arrBunga['tanggal']);
											if($arrBunga['tanggal'][$crTgl]==$isi['periode']){
												continue;
											}	
										}
										
										if($periodedate1>$isi['periode']){
											continue;
										}
										if($val['tanggal']>$isi['periode']){
											continue;
										}
										 
										$arrBunga['tanggal'][]=$isi['periode'];
										$arrBunga['bunga'][]=$isi['nilai'];
									}
								}
								#filter tanggal klo1 ada yang sama
								foreach ($arrBunga['tanggal'] as $keydt => $valTanggal) {
									if($valTanggal==$arrBunga['tanggal'][$keydt+1]){
										unset($arrBunga['tanggal'][$keydt+1]);
									}
								}
								#proses loannya start
								if(count($arrBunga['tanggal'])<2){	
									$totalday_ = array();
									$totalbunga=0;
									if(($val['tanggal']<=$periodedate)&&($val['tanggal']>=$periodedate1)){
										// jika tanggal lebih kecil atau lebih besar dari tangal jatuh tempo tiap bulannya
										//maka jumlah hari bukan sebulan alias harus proporsi
										$periodedate1_ = $val['tanggal'];
										$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));	
										$totalday_ = datediff($periodedate1_,$periodedate2_);
										$outStand[$val['noloan']]=$val['jumlah'];
									}else{
										$periodedate1_ = $periodedate1;
										$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
										$totalday_ = datediff($periodedate1_,$periodedate2_);
										//$totOustanding+=$val['jumlah'];
									}
									$str=" select nilai from ".$dbname.".keu_pmsukubunga where  periode<'".$periodedate."' and notransaksipm='".$notransaksi."' order by periode desc limit 1";
										//echo $str;
									$rSukuB=fetchData($str);
									$sukub=$rSukuB[0]['nilai'];
									 
									$nourt+=1;
									$tab.="<tr class=rowcontent>";
									$tab.="<td>".$nourt."</td>";
									if($key==0){
										$tab.="<td rowspan='".$rwSpn."'>".$noAccount."</td>";	
										
									}
									
									$cairOutstandingDisply+=$val['jumlah'];	
									
									$tab.="<td align=right>".number_format($cairOutstandingDisply)."</td>";
									$tab.="<td align=right>".number_format($val['jumlah'])."</td>";
									if($key==0){
										$tab.="<td rowspan='".$rwSpn."'>".number_format(($displaycairOutstand-$rupPokok))."</td>";	
										$tab.="<td align=right rowspan='".$rwSpn."'>".number_format($rupPokokDisplay[$notransaksi])."</td>";	
									}
									$tab.="<td>".tglnmbln($val['tanggal'],'','')."</td>";
									$tab.="<td align=right>".number_format($sukub,2)."%</td>";
									$tab.="<td>".tglnmbln($periodedate1_,'','')."</td>
										   <td>".tglnmbln($periodedate2_,'','')."</td>
										   <td align=right>".$totalday_['days_total']."</td>";

									if(isset($outStand[$val['noloan']])){
										$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($sukub/100))/(int)$jmlhari;	
									}else{
										if($totRowData==0){
											$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($sukub/100)))/(int)$jmlhari;	
										}
									}
									
									if($statAkhirBkn==1){
										$itung=$rwSpn-($bnykoutstand+1);
										if($key==$itung){
											$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($sukub/100)))/(int)$jmlhari;	
											//__(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$sukub."/100)))/".(int)$jmlhari." klo mau test itungan
										}
									}
									$grandTotBunga+=$totalbunga;
									$tab.="<td align=right>".number_format($totalbunga)."</td>";
									if($key==0){
										$tab.="<td align=right rowspan='".$rwSpn."'>".number_format($grandTotBungaDisplay+$rupPokokDisplay[$notransaksi])."</td>";
									}
									$tab.="<td align=right>".tglnmbln($tgljatuhtempoBri[$val['noloan']],'','')."</td>";
									$tab.="</tr>";
								}else{
									$rowspanDt=count($arrBunga['tanggal']);
									$totalday_ = array();
									$totHari=count($arrBunga['tanggal'])-1;
									// echo"<pre>";
									// print_r($arrBunga['tanggal']);
									// echo"</pre>";
									foreach ($arrBunga['tanggal'] as $key2 => $val2){
										if($totHari==$key2){#jika tothari sama dengan index array,mencari index terakhir
											$indSblm=$totHari-1;
											if($tglBerikutNya==$val2){
												$periodedate1_ = $tglBerikutNya;
												$val2 = $periodedate;	
											}else{
												//exit('warning:'.$tglBerikutNya."__".$val2);
												if($tglBerikutNya<$val2){
													$periodedate1_ = $tglBerikutNya;	
													$val2 = $periodedate;
												}else{
													$periodedate1_ = $arrBunga['tanggal'][$indSblm];		
													$val2 = $periodedate;
												}
												
											}
											
											if($periodedate!=$val2){#jika tanggal terakhir tidak sama dengan nilai array terakhir maka diisi dengan tanggal akhir
												$val2=$periodedate;
											}
										}else{
											if($key2!=0){
												$periodedate1_ = $tglBerikutNya;	
											}else{
												 
												$periodedate1_ = $periodedate1;	
												if($periodedate1>$val2){
													$val2=$arrBunga['tanggal'][($key2+1)];
													$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
												}
												else if($periodedate1==$val2){#jika tanggal bunga sama dengan tanggal mulai continue row dikurangi 1
													$val2=$arrBunga['tanggal'][($key2+1)];
													//exit('warning:masuk'.$periodedate1);
													$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
													if($periodedate==$val2){
														$val2 = date("Y-m-d",strtotime("-1 Days",strtotime($val2)));
														$tglBerikutNya=$val2;
													}
												}else{
													$tglBerikutNya=$val2;
												}
											}						
											//$tglBerikutNya=$val;
										}
										$periodedate2_ = date("Y-m-d",strtotime($val2));
										//$periodedate2_=date("Y-m-d",strtotime($val));
										$totalday_ = datediff($periodedate1_,$periodedate2_);
										$nourt+=1;
										if($val['noloan']!=$tempNoloan){
											$cairOutstanding+=$lstRpPencairan[$val['noloan']];		
											$tempNoloan=$val['noloan'];
											//$totOustanding=$cairOutstanding;	
										}
										$totalbunga=0;
										$tab.="<tr class=rowcontent>";
										if($tempLoan!=$notransaksi){
											$tempLoan=$notransaksi;
											$tab.="<td>".$nourt."</td>";
											if($key==0){
												$tab.="<td rowspan='".(($testasaja*$rowspanDt)+$bnykoutstand)."'>".$noAccount."</td>";	
											}
											$tab.="<td align=right>".number_format($cairOutstanding)."</td>";
											$tab.="<td align=right>".number_format($lstRpPencairan[$val['noloan']])."</td>";
											if($key==0){
												$tab.="<td rowspan='".(($testasaja*$rowspanDt)+$bnykoutstand)."'>".number_format(($displaycairOutstand-$rupPokok))."</td>";	
												$tab.="<td align=right rowspan='".(($testasaja*$rowspanDt)+$bnykoutstand)."'>".number_format($rupPokokDisplay[$notransaksi])."</td>";	
											}
											$tab.="<td>".tglnmbln($val['tanggal'],'','')."</td>";
											$tab.="<td align=right>".number_format($arrBunga['bunga'][$key],2)."%</td>";
											$tab.="<td>".tglnmbln($periodedate1_,'','')."</td>
												   <td>".tglnmbln($periodedate2_,'','')."</td>
												   <td align=right>".$totalday_['days_total']."</td>";
											//$totalbunga = ($totOustanding*$totalday_['days_total'])/(int)$jmlhari;	
											if(isset($outStand[$val['noloan']])){
												$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100))/(int)$jmlhari;	
												$tampilan.="(((".$outStand[$val['noloan']]."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
											}else{
												if($totRowData==0){
													$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;
													$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
												}
											}
											if($statAkhirBkn==1){
												$totalbunga=0;
												$itung=$rwSpn-($bnykoutstand+1);
												if($key==$itung){
													$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;	
													$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." adda statAkhirBkn \n\n\n";
												}
											}
											$grandTotBunga+=$totalbunga;
											$tab.="<td align=right>".number_format($totalbunga)."</td>";
											if($key==0){
												$tab.="<td align=right rowspan='".(($testasaja*$rowspanDt)+$bnykoutstand)."'>".number_format(($grandTotBungaDisplay+$rupPokokDisplay[$notransaksi]))."</td>";
											}
											$tab.="<td align=right>".tglnmbln($tgljatuhtempoBri[$val['noloan']],'','')."</td>";
										}else{
											$tab.="<td>".$nourt."</td>";
											$tab.="<td align=right>".number_format($cairOutstanding)."</td>";
											$tab.="<td align=right>".number_format($val['jumlah'])."</td>";
											$tab.="<td>".tglnmbln($val['tanggal'],'','')."</td>";
											$tab.="<td align=right>".number_format($arrBunga['bunga'][$key2],2)."%</td>";
											$tab.="<td>".tglnmbln($periodedate1_,'','')."</td>
												   <td>".tglnmbln($periodedate2_,'','')."</td>
												   <td align=right>".$totalday_['days_total']."</td>";
											if(isset($outStand[$val['noloan']])){
												$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100))/(int)$jmlhari;	
												$tampilan.="(((".$outStand[$val['noloan']]."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
											}else{
												if($totRowData==0){
													$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;
													$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
												}
											}
											if($statAkhirBkn==1){
												$totalbunga=0;
												$itung=$rwSpn-($bnykoutstand+1);
												if($key==$itung){
													$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;	
													$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." adda statAkhirBkn \n\n\n";
												}
											}
											$grandTotBunga+=$totalbunga;
											$tab.="<td align=right>".number_format($totalbunga)."</td>";
											$tab.="<td align=right>".tglnmbln($tgljatuhtempoBri[$val['noloan']],'','')." </td>";
										}
										$tab.="</tr>";
									}
								}
							}
							$tab.="<tr class=rowcontent>";
							$tab.="<td colspan=2>".$_SESSION['lang']['total']."</td>";
							$tab.="<td align=right>".number_format($displaycairOutstand)."</td>";
							$tab.="<td>&nbsp;</td>";
							$tab.="<td align=right>".number_format($displaycairOutstand-$rupPokok)."</td>";
							$tab.="<td align=right>".number_format($rupPokokDisplay[$notransaksi])."</td>";
							$tab.="<td  colspan=5>&nbsp;</td>";
							$tab.="<td align=right>".number_format($grandTotBunga)."</td>";
							$tab.="<td align=right>".number_format($grandTotBunga+$rupPokokDisplay[$notransaksi])."</td>";
							$tab.="<td>&nbsp;</td>";
							$tab.="</tr>";
						}
				}
				
				$tab.="</table>";	
			

			if($tipe=='excel'){
				$tglSkrg=date("YmdHis");
				$nop_="summary_loan_kisi_total_pencairan".$notransaksi."__".$tglSkrg;
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
            $dompdf->stream("summary Loan Kisi Total Pencairan ".$notransaksi,array("Attachment"=>0));
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