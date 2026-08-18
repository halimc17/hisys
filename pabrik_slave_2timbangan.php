<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$type=checkPostGet('type','');

$kdpabrik=checkPostGet('kdpabrik','');
$kdbrg=checkPostGet('kdbrg','');

$tgltrans=checkPostGet('tgltrans','');
$tgltrans2=checkPostGet('tgltrans2','');
$tgltrans2=tanggalsystemn($tgltrans2);
$tgltrans=explode('-',$tgltrans);
$tgltrans = $tgltrans[2]."-".$tgltrans[1]."-".$tgltrans[0];

$tglawal=checkPostGet('tglawal','');
$tglakhir=checkPostGet('tglakhir','');

switch($proses){
	case'preview1':

		$result="";
		if($type=='html'){
			$border = 0;
		}else{
			$optNamaPabrik = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kdpabrik."'");
			$border = 1;
			$result.="<table cellspacing='2' cellpadding ='5' border='0' class=sortable>
				<tr>
					<td colspan=15 style='font-weight:bold;text-align:center'>Report WeighBridge</td>
				</tr>
				<tr>
					<td colspan=15 style='text-align:center'>Pabrik : ".$optNamaPabrik[$kdpabrik]."</td>
				</tr>
				<tr>
					<td colspan=15 style='text-align:center'>Tanggal : ".$tgltrans."</td>
				</tr>
			</table>";
		}
		$result.="<div class='table-scroll'>
			<table cellspacing=1 cellpadding=5 border='".$border."' width=100% class=sortable>
				<thead class=rowheader>
				<tr>
					<th style='text-align:center'>No.</th>
					<th style='text-align:center'>".$_SESSION['lang']['namabarang']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['nosipb']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['noTiket']."</th>
					<!--<th style='text-align:center'>No. Refrensi</th>-->
					<th style='text-align:center'>".$_SESSION['lang']['kodenopol']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['sopir']."</th>    
					<th style='text-align:center'>Jjg</th>
					<th style='text-align:center'>Brondolan</th>
					<th style='text-align:center'>Bruto (Kg)</th>
					<th style='text-align:center'>Tara (Kg)</th>
					<th style='text-align:center'>Netto I (Kg)</th>
					<th style='text-align:center'>".$_SESSION['lang']['tanggal']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['jammasuk']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['jamkeluar']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['unit']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['afdeling']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['supplier']."</th>
					<th style='text-align:center'>".$_SESSION['lang']['transportir']."</th>
					<th style='text-align:center'>No.Referensi Mobile</th>
					<th style='text-align:center'>Kode Kemandoran</th>
					<th style='text-align:center'>".$_SESSION['lang']['action']."</th>
				</tr>
				</thead>
				<tbody>";
		
		$no = 0;
		$totberatmasuk = 0;
		$totberatkeluar = 0;
		$totberatbersih = 0;
		
		$str="select * from ".$dbname.".pabrik_timbangan where left(tanggal,10) between '".$tgltrans."' and '".$tgltrans2."' and kodeorg='".$kdpabrik."' and millcode!='EXTM' and kodebarang like '".$kdbrg."%' order by tanggal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=$res->rowCount();
		
		if($numrows <= 0){
			$result.="<tr class=rowcontent><td colspan=15 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			while($bar=$res->fetch()){
				$no+=1;
				$optNamaSupplier = array();
				if($bar['kodebarang']=='40000003' || $bar['kodebarang']=='400000003'){
					$optNamaSupplier = makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier',"kodetimbangan='".$bar['kodecustomer']."'");
					$optramp = makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$bar['kodecustomer']."'");
					$transport = $bar['pengirim'];
					$tbsup = ($optNamaSupplier[$bar['kodecustomer']]==''?$optramp[$bar['kodecustomer']]:$optNamaSupplier[$bar['kodecustomer']]);
					$optnmcus = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
					$optNamaSupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier');
					
					$namasupplier = $optNamaSupplier[$bar['trpcode']];
					
				}else{
					$optNamaSupplier = makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier');
					// $optcus = makeOption($dbname,'pmn_kontrakjual_vw','nokontrak,koderekanan',"nokontrak='".$bar['nokontrak']."'");
					// $kdcus = $optcus[$bar['nokontrak']]; 
					$optnmcus = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
					// $transport = ($optNamaSupplier[$bar['kodecustomer']]);
					// $tbsup = $optnmcus[$kdcus];					
					$namasupplier = $optNamaSupplier[$bar['kodesupplier']];
				}
				
				$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
				$optNamaUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				$optNamaDivisi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['divcode']."'");
				$bgcolor='';
				if($bar['kodebarang']=='40000001' || $bar['kodebarang']=='40000002'){
					if($kdpabrik=='BP9M'){
						if($bar['norefrensi']==''){
							$bgcolor = 'red';
						}else{
							$optNoRef = makeOption($dbname,'pabrik_timbangan','notransaksi,notransaksi',"notransaksi='".$bar['norefrensi']."'");
							// echo $bar['norefrensi']."__".$optNoRef[$bar['norefrensi']]."<br>";
							if($optNoRef[$bar['norefrensi']]==''){
								$bgcolor = 'red';
							}
						}
					}
				}
				
				if($bar['nokontrak'] != 0 && $bar['nokontrak'] != "") {
					$temp = $bar['beratmasuk'];
					$bar['beratmasuk'] = $bar['beratkeluar'];
					$bar['beratkeluar'] = $temp;
				}

				$result.="<tr class=rowcontent style='background-color:".$bgcolor."'>
					<td style='text-align:right'>".$no."</td>
					<td>".$optNamaBarang[$bar['kodebarang']]."</td>
					<td style='text-align:center'>".$bar['nospb']."</td>
					<td style='text-align:center'>".$bar['notransaksi']."</td>
					<!--<td style='text-align:center'>".$bar['norefrensi']."</td>-->
					<td style='text-align:center'>".$bar['nokendaraan']."</td>
					<td style='text-align:center'>".$bar['supir']."</td>
					<td style='text-align:center'>".$bar['jumlahtandan1']."</td>
					<td style='text-align:center'>".$bar['brondolan']."</td>";
					
					$result.="<td style='text-align:right'>".number_format($bar['beratmasuk'],2)."</td>
					<td style='text-align:right'>".number_format($bar['beratkeluar'],2)."</td>
					<td style='text-align:right'>".number_format($bar['beratbersih'],2)."</td>
					<td style='text-align:center'>".substr($bar['tanggal'], 0,10)."</td>
					<td style='text-align:center'>".$bar['jammasuk']."</td>
					<td style='text-align:center'>".$bar['jamkeluar']."</td>
					<td>".($bar['kodeorg']==''?'':$optNamaUnit[$bar['kodeorg']].' ('.$bar['kodeorg'].')')."</td>
					<td>".($bar['divcode']==''?'':$optNamaDivisi[$bar['divcode']].' ('.$bar['divcode'].')')."</td>
					<td>".$optnmcus[$bar['kodecustomer']]."</td>
					<td>".$namasupplier."</td>
					<td>".$bar['nospbmobile']."</td>
					<td align=center>".$bar['kemandoran']."</td>
					<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_timbangan','".$bar['notransaksi']."','','log_slave_print_timbangan',event);\"></td>
				</tr>";
				
				$totjjg = $totjjg + $bar['jumlahtandan1'];
				$totbro = $totbro + $bar['brondolan'];
				$totberatmasuk = $totberatmasuk + $bar['beratmasuk'];
				$totberatkeluar = $totberatkeluar + $bar['beratkeluar'];
				$totberatbersih = $totberatbersih + $bar['beratbersih'];
			}
			$result.="<tr class=rowcontent>
				<td colspan=6 style='font-weight:bold;text-align:center'>".$_SESSION['lang']['total']."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totjjg,2)."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totbro,2)."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totberatmasuk,2)."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totberatkeluar,2)."</td>
				<td style='text-align:right;font-weight:bold;'>".number_format($totberatbersih,2)."</td>
				<td colspan=10></td>
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
							$grandtotalbulking += $bar2['beratbersih'];
							$grandtotalpmks += $val2['beratbersih'];
							
							$tempTanggal = substr($val2['tanggal'],0,10);
						}
					}
				}
			}
			
			
			
			$result.="<tr class=rowcontent>
				<td style='text-align:center;font-weight:bold' colspan=9>Total</td>
				<td style='text-align:right'><b>".number_format($berat1)."</b></td>
				<td style='text-align:right' colspan=8></td>
				<td style='text-align:right'><b>".number_format($berat2)."</b></td>
				<td style='text-align:right' colspan=2></td>
			</tr>";
			if ($kdbrg == '40000002') {
				$result.= "<tr class=rowcontent>
					<td style='text-align:center;font-weight:bold' colspan=9>Grand Total</td>
					<td style='text-align:right'><b>".number_format($grandtotalpmks)."</b></td>
					<td style='text-align:right' colspan=8></td>
					<td style='text-align:right'><b>".number_format($grandtotalbulking)."</b></td>
					<td style='text-align:right' colspan=2></td>
				</tr>";
			}
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