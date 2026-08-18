<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
$proses    = checkPostGet('proses','');
$unit      = checkPostGet('unit','');
$afd       = checkPostGet('afd','');
$prd       = checkPostGet('prd','');
$kontanan  = checkPostGet('kontanan','');
$tglmulai  = tanggalsystemn(checkPostGet('tglmulai',''));
$tglakhir  = tanggalsystemn(checkPostGet('tglakhir',''));
$tahap     = checkPostGet('tahap','');
$jab       = checkPostGet('jabatan','');
$tgl1      = $tglmulai;
$tgl2      = $tglakhir;

$baris     = checkPostGet('baris','');
$mandor    = checkPostGet('mandor','');
$tgl       = checkPostGet('tgl','');
$premiawal = checkPostGet('premiawal','');
$pembagi   = checkPostGet('pembagi','');
$pengali   = checkPostGet('pengali','');
$premi     = checkPostGet('premi','');
$dendasave = checkPostGet('denda','');
$premitotal= checkPostGet('premitotal','');
$kgbrdsave = checkPostGet('kgbrd','');
$premiawal =str_replace(',','',$premiawal);
$pembagi   =str_replace(',','',$pembagi);
$pengali   =str_replace(',','',$pengali);
$premi     =str_replace(',','',$premi);
$dendasave =str_replace(',','',$dendasave);
$premitotal=str_replace(',','',$premitotal);
$kgbrdsave =str_replace(',','',$kgbrdsave);

$arrJab=array(
	"mandorpnn"   =>"Mandor Panen",
	"kranipnn"    =>"Kerani Panen",
	"mandor1pnn"  =>"Mandor 1 Panen",
	"mandortus"   =>"Mandor TUS",
	"mandortup"   =>"Mandor TUP",
	"kranikirim"  =>"Kerani Kirim",
	"mandortraksi"=>"Mandor Traksi",
	"mandor1tus"  =>"Mandor 1 TUS",
	"mandor1tup"  =>"Mandor 1 TUP"
);

switch($proses){
	case'gettgl':
		if($param['tahap']==1){		
			$tglawal = tanggalnormal($param['prd']."-01");
			$tglakhir= tanggalnormal($param['prd']."-15");
		}else{
			$tglawal = tanggalnormal($param['prd']."-16");
			$tglakhir= tanggalnormal(tglakhir($param['prd']."-01"));
		}
		
		$tglawal = tanggalnormal($param['prd']."-01");
		$tglakhir= tanggalnormal(tglakhir($param['prd']."-01"));
		echo $tglawal."####".$tglakhir;
	break;
	case'getdivisi':
		$optafd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['unit']."' and tipe='AFDELING'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optafd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		echo $optafd;
	break;
	case'preview':
		if($tglmulai=='--' and $tglakhir=='--'){
			exit("Warning : Tanggal wajib diisi.");
		}
		if(substr($tglmulai,0,7)!=$prd){
			exit("Warning : Tanggal tidak sesuai dengan periode.");
		}
		if($tgl1>$tgl2){
			exit("Warning : Tanggal pertama lebih besar dari tanggal kedua.");
		}
		$tanggal = rangetanggal($tgl1,$tgl2);
		
		//Cek Periode gaji
		$str="select max(sudahproses) as prd from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' and periode='".$prd."' and jenisgaji!='S' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$prdgaji=$bar['prd'];
		}
		//Cek Periode akutansi
		$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and periode='".$prd."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$prdakt=$bar['tutupbuku'];
		}

		if($afd=='' || $unit=='' || $prd==''){
			exit("Warning : Periode, Unit Kerja dan Afdeling wajib di isi !");
		}


		if($prdgaji=='1' || @$prdakt=='1'){
			exit ("Warning : Periode Gaji atau Periode Akutansi sudah ditutup !");
		}

		## Cek datakaryawan
		$str="select * from ".$dbname.".datakaryawan_hist where lokasitugas='".$unit."' and subbagian='".$afd."' and periodegaji ='".$prd."' and version_type = 'B'";
		$res=fetchdata($str);

		$whereKerani='';
		$whereMandor='';
		$whereMandor1='';
		if(count($res) > 0){
			$whereKerani.=" and kerani in (select karyawanid from ".$dbname.".datakaryawan_hist where lokasitugas='".$unit."' and subbagian='".$afd."' and periodegaji ='".$prd."' and version_type = 'B' and tipekaryawan != 0)";
			$whereMandor.=" and mandor in (select karyawanid from ".$dbname.".datakaryawan_hist where lokasitugas='".$unit."' and subbagian='".$afd."' and periodegaji ='".$prd."' and version_type = 'B' and tipekaryawan != 0)";
			$whereMandor1.=" and mandor1 in (select karyawanid from ".$dbname.".datakaryawan_hist where lokasitugas='".$unit."' and subbagian='".$afd."' and periodegaji ='".$prd."' and version_type = 'B' and tipekaryawan != 0)";
		}else{
			$whereKerani.=" and kerani in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."' and tipekaryawan != 0)";
			$whereMandor.=" and mandor in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."' and tipekaryawan != 0)";
			$whereMandor1.=" and mandor1 in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."' and tipekaryawan != 0)";
		}

		switch($jab){
			case'KERANI':

				$str='';$w='';
				$premilb = $pembagi = $arrbagi = [];
				$w=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";

				if(getindukPT($unit) == 'PPP'){
					$str="select * from ".$dbname.".kebun_3premipemanen where periode='".$prd."' ".$whereKerani." order by tanggalpanen";
					$res=fetchdata($str);
					foreach($res as $bar){
						$dtkerani[$bar['kerani']]=$bar['kerani'];
						$jabatan[$bar['kerani']]='KERANI';
						$listkar[$bar['kerani']][$bar['tanggalpanen']]=$bar['tanggalpanen'];
						@$kg[$bar['kerani']][$bar['tanggalpanen']]+=($bar['kgbuahbesar']+$bar['kgbuahkecil']);
						$tt[$bar['kerani']][$bar['tanggalpanen']]=$bar['tahuntanam'];
						$premilb[$bar['kerani']][$bar['tanggalpanen']]+=$bar['rplbbuahbesar']+$bar['rplbbuahkecil']+$bar['rpbrondolan'];
						$arrbagi[$bar['kerani']][$bar['tanggalpanen']][$bar['karyawanid']]=$bar['karyawanid'];
					}
				}else{
					$str="select * from ".$dbname.".kebun_3premipemanen_v2 where periode='".$prd."' ".$whereKerani." order by tanggalpanen";
					$res=fetchdata($str);
					foreach($res as $bar){
						$dtkerani[$bar['kerani']]=$bar['kerani'];
						$jabatan[$bar['kerani']]='KERANI';
						$listkar[$bar['kerani']][$bar['tanggalpanen']]=$bar['tanggalpanen'];
						@$kg[$bar['kerani']][$bar['tanggalpanen']]+=$bar['kg'];
						$tt[$bar['kerani']][$bar['tanggalpanen']]=$bar['tahuntanam'];
						$premilb[$bar['kerani']][$bar['tanggalpanen']]+=$bar['premilb'];
						$arrbagi[$bar['kerani']][$bar['tanggalpanen']][$bar['karyawanid']]=$bar['karyawanid'];
					}
				}
				

				foreach($arrbagi as $mdr => $val1){
					foreach($val1 as $tgl => $val2){
						foreach($val2 as $kary){
							$pembagi[$mdr][$tgl]++;
						}
					}
				}

				if(empty($dtkerani)){
					exit("Warning : Silahkan lakukan proses premi pemanen terlebih dahulu.");
				}
				$tab='';
				if(!empty($dtkerani)){
					$no=0;
					foreach($dtkerani as $kerani){
						if ($proses == 'excel') {
							$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=1>";
						} else 	{
							$tab.="<table class=sortable cellspacing=1 cellpadding=5 style=min-width:700px>";
						}
						
						$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kerani."'");
						$tab.="<thead>";
						$no++;
						$tab.="<tr class=rowcontent id=row".$no.">";
							$tab.="<th colspan=10 align=left bgcolor=#CCCCCC align=center>Kerani : <b>".$nmkar[$kerani]."</b></th>"; 
							$tab.="<th hidden id=mandor".$no.">".$kerani."</th>";
							$tab.="<th hidden id=jabatan".$no.">".$param['jabatan']."</th>";
						$tab.="</tr>";
						
						$tab.="<tr class=rowheader>";
						$tab.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
						$tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
						$tab.="<th align=center>".$_SESSION['lang']['hari']."</th>";
						$tab.="<th align=center width=100px>Premi LB Kary</th>";
						$tab.="<th align=center width=70px>Pembagi</th>";
						$tab.="<th align=center width=70px>Rata Rata Premi</th>";
						$tab.="<th align=center width=70px>Nilai Pengali</th>";
						$tab.="<th align=center width=70px>Premi Bruto</th>";
						$tab.="<th align=center width=70px>".$_SESSION['lang']['denda']."</th>";
						$tab.="<th align=center width=70px>".$_SESSION['lang']['total']." pendapatan</th>";
						$tab.="</tr>";
						$tab.="</thead>";
						
						$nokar=0;
						$color='';
						#ambil setup premi mandor
						$str="select * from ".$dbname.".kebun_5premimandor where kodeorg='".$unit."' and jenis='".$jab."'";
						$res=fetchdata($str)[0];			
						$nilai=$res['nilaipengali'];
						$minimalpembagi=$res['minimalpembagi'];
						foreach($tanggal as $tgl){
							if($pembagi[$kerani][$tgl]<$minimalpembagi){
								$pembagi[$kerani][$tgl]=$minimalpembagi;
							}
							#if(@$listkar[$mandor][$tgl]!=''){
								$nokar++;
								$hari = getjenisharikerja($unit,$tgl);
								
								$tab.="<tr class=rowcontent id=baris".$no."_".$nokar.">";
								$tab.="<td align=center>".$nokar."</td>";
								$tab.="<td align=center id=tgl_".$no."_".$nokar.">".$tgl."</td>";
								$tab.="<td align=center id=hari_".$no."_".$nokar.">".$hari."</td>";
								$tab.="<td align=right id=rupiah_".$no."_".$nokar.">".@numb_format($premilb[$kerani][$tgl])."</td>";

								if($premilb[$kerani][$tgl] == '' || $premilb[$kerani][$tgl] == 0){
									$pembagi[$kerani][$tgl] = 0;
									$disbled = "disabled";
								}else{
									$disbled = "";
								}

								$tab.="<td align=center><input ".$disbled." type=text value='".$pembagi[$kerani][$tgl]."' onkeyup=\"z.numberFormat('pembagi_".$no."_".$nokar."',0);gettotalpertama(".$no.",'rupiah_".$no."_".$nokar."','pembagi_".$no."_".$nokar."','premibrutorata_".$no."_".$nokar."','nilaipengali_".$no."_".$nokar."','premibruto_".$no."_".$nokar."','ttlblmdenda_".$no."_".$nokar."','denda_".$no."_".$nokar."','premiinput_".$no."_".$nokar."');\" id=pembagi_".$no."_".$nokar." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";


								$premirata=@fixnan(round($premilb[$kerani][$tgl]/$pembagi[$kerani][$tgl],0));
								$tab.="<td align=right id=premibrutorata_".$no."_".$nokar.">".@numb_format($premirata)."</td>";
								$tab.="<td align=right id=nilaipengali_".$no."_".$nokar.">".$nilai."</td>";
								$premibruto=($nilai*$premirata);
								$tab.="<td align=right id=premibruto_".$no."_".$nokar.">".@numb_format($premibruto)."</td>";
								$tab.="<td align=right style=display:none; id=ttlblmdenda_".$no."_".$nokar.">".@numb_format($premibruto)."</td>";
								$tab.="<td align=center><input type=text  value=0 onkeyup=\"z.numberFormat('denda_".$no."_".$nokar."',0);gettotal(".$no.",'ttlblmdenda_".$no."_".$nokar."','denda_".$no."_".$nokar."','premiinput_".$no."_".$nokar."');\" id=denda_".$no."_".$nokar." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";
								
								$tab.="<td align=right id=premiinput_".$no."_".$nokar.">".@numb_format($premibruto)."</td>";
								$tab.="</tr>";

								@$premilbtot[$kerani]+=$premilb[$kerani][$tgl];
								@$pembagitot[$kerani]+=$pembagi[$kerani][$tgl];
								@$tpremirata[$kerani]+=$premirata;
								@$tpremibruto[$kerani]+=$premibruto;
								@$tpremitot[$kerani]+=$premibruto;
								
							#}
						}
						$tab.="<input hidden value=".$nokar." id=totalbaris".$no.">";
						$tab.="<tr class=rowcontent>";
						$tab.="<td></td><td colspan=2>Total Premi</td>";
							$tab.="<td align=right>".@number_format($premilbtot[$kerani])."</td>";
							$tab.="<td align=right id=ttlpembagi".$no.">".@number_format($pembagitot[$kerani])."</td>";
							$tab.="<td align=right id=ttlprebrutorata".$no.">".@number_format($tpremirata[$kerani])."</td>";
							$tab.="<td align=right></td>";
							$tab.="<td align=right id=ttlprebruto".$no.">".@number_format($tpremibruto[$kerani])."</td>";
							$tab.="<td align=right id=ttldenda".$no."></td>";
							$tab.="<td align=right id=ttlprenetto".$no.">".@number_format($tpremibruto[$kerani])."</td>";
						$tab.="</tr>";
						$tab.="</tbody></table><br>";
					}
				}
				
				$tab.="<button class=mybutton onclick=saveAll('1','1','".$no."','".$nokar."');>".$_SESSION['lang']['proses']."</button>";
				echo $tab;
			break;

			case'MANDORPANEN':
				$str='';$w='';
				$premilb = $pembagi = $arrbagi = [];
				$w=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";

				if(getindukPT($unit) == 'PPP'){
					$str="select * from ".$dbname.".kebun_3premipemanen where periode='".$prd."' ".$w." ".$whereMandor." order by tanggalpanen";
					$res=fetchdata($str);
					foreach($res as $bar){
						$dtmandor[$bar['mandor']]=$bar['mandor'];
						$jabatan[$bar['mandor']]='MANDOR';
						//$tanggal[$bar['tanggalpanen']]=$bar['tanggalpanen'];
						$listkar[$bar['mandor']][$bar['tanggalpanen']]=$bar['tanggalpanen'];
						@$kg[$bar['mandor']][$bar['tanggalpanen']]+=($bar['kgbuahbesar']+$bar['kgbuahkecil']);
						$tt[$bar['mandor']][$bar['tanggalpanen']]=$bar['tahuntanam'];
						$premilb[$bar['mandor']][$bar['tanggalpanen']]+=$bar['rplbbuahbesar']+$bar['rplbbuahkecil']+$bar['rpbrondolan'];
						$arrbagi[$bar['mandor']][$bar['tanggalpanen']][$bar['karyawanid']]=$bar['karyawanid'];
					}
				}else{
					$str="select * from ".$dbname.".kebun_3premipemanen_v2 where periode='".$prd."' ".$w." ".$whereMandor." order by tanggalpanen";
					$res=fetchdata($str);
					foreach($res as $bar){
						$dtmandor[$bar['mandor']]=$bar['mandor'];
						$jabatan[$bar['mandor']]='MANDOR';
						//$tanggal[$bar['tanggalpanen']]=$bar['tanggalpanen'];
						$listkar[$bar['mandor']][$bar['tanggalpanen']]=$bar['tanggalpanen'];
						@$kg[$bar['mandor']][$bar['tanggalpanen']]+=$bar['kg'];
						$tt[$bar['mandor']][$bar['tanggalpanen']]=$bar['tahuntanam'];
						$premilb[$bar['mandor']][$bar['tanggalpanen']]+=$bar['premilb'];
						$arrbagi[$bar['mandor']][$bar['tanggalpanen']][$bar['karyawanid']]=$bar['karyawanid'];
					}
				}

				foreach($arrbagi as $mdr => $val1){
					foreach($val1 as $tgl => $val2){
						foreach($val2 as $kary){
							$pembagi[$mdr][$tgl]++;
						}
					}
				}

				if(empty($dtmandor)){
					exit("Warning : Silahkan lakukan proses premi pemanen terlebih dahulu.");
				}
				$tab='';
				if(!empty($dtmandor)){
					$no=0;
					foreach($dtmandor as $mandor){
						if ($proses == 'excel') {
							$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=1>";
						} else 	{
							$tab.="<table class=sortable cellspacing=1 cellpadding=5 style=min-width:700px>";
						}
						
						$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$mandor."'");
						$tab.="<thead>";
						$no++;
						$tab.="<tr class=rowcontent id=row".$no.">";
							$tab.="<th colspan=10 align=left bgcolor=#CCCCCC align=center>".$_SESSION['lang']['mandor']." : <b>".$nmkar[$mandor]."</b></th>"; 
							$tab.="<th hidden id=mandor".$no.">".$mandor."</th>";
							$tab.="<th hidden id=jabatan".$no.">".$param['jabatan']."</th>";
						$tab.="</tr>";
						
						$tab.="<tr class=rowheader>";
						$tab.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
						$tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
						$tab.="<th align=center>".$_SESSION['lang']['hari']."</th>";
						$tab.="<th align=center width=100px>Premi LB Kary</th>";
						$tab.="<th align=center width=70px>Pembagi</th>";
						$tab.="<th align=center width=70px>Rata Rata Premi</th>";
						$tab.="<th align=center width=70px>Nilai Pengali</th>";
						$tab.="<th align=center width=70px>Premi Bruto</th>";
						$tab.="<th align=center width=70px>".$_SESSION['lang']['denda']."</th>";
						$tab.="<th align=center width=70px>".$_SESSION['lang']['total']." pendapatan</th>";
						$tab.="</tr>";
						$tab.="</thead>";
						$nokar=0;
						$color='';
						#ambil setup premi mandor
						$str="select * from ".$dbname.".kebun_5premimandor where kodeorg='".$unit."' and jenis='".$jab."'";
						$res=fetchdata($str)[0];			
						$nilai=$res['nilaipengali'];
						$minimalpembagi=$res['minimalpembagi'];
						foreach($tanggal as $tgl){
							if($pembagi[$mandor][$tgl]<$minimalpembagi){
								$pembagi[$mandor][$tgl]=$minimalpembagi;
							}
							#if(@$listkar[$mandor][$tgl]!=''){
								$nokar++;
								$hari = getjenisharikerja($unit,$tgl);
								
								$tab.="<tr class=rowcontent id=baris".$no."_".$nokar.">";
								$tab.="<td align=center>".$nokar."</td>";
								$tab.="<td align=center id=tgl_".$no."_".$nokar.">".$tgl."</td>";
								$tab.="<td align=center id=hari_".$no."_".$nokar.">".$hari."</td>";
								$tab.="<td align=right id=rupiah_".$no."_".$nokar.">".@numb_format($premilb[$mandor][$tgl])."</td>";

								if($premilb[$mandor][$tgl] == '' || $premilb[$mandor][$tgl] == 0){
									$pembagi[$mandor][$tgl] = 0;
									$disbled = "disabled";
								}else{
									$disbled = "";
								}

								$tab.="<td align=center><input ".$disbled." type=text value='".$pembagi[$mandor][$tgl]."' onkeyup=\"z.numberFormat('pembagi_".$no."_".$nokar."',0);gettotalpertama(".$no.",'rupiah_".$no."_".$nokar."','pembagi_".$no."_".$nokar."','premibrutorata_".$no."_".$nokar."','nilaipengali_".$no."_".$nokar."','premibruto_".$no."_".$nokar."','ttlblmdenda_".$no."_".$nokar."','denda_".$no."_".$nokar."','premiinput_".$no."_".$nokar."');\" id=pembagi_".$no."_".$nokar." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";


								$premirata=@fixnan(round($premilb[$mandor][$tgl]/$pembagi[$mandor][$tgl],0));
								$tab.="<td align=right id=premibrutorata_".$no."_".$nokar.">".@numb_format($premirata)."</td>";
								$tab.="<td align=right id=nilaipengali_".$no."_".$nokar.">".$nilai."</td>";
								$premibruto=($nilai*$premirata);
								$tab.="<td align=right id=premibruto_".$no."_".$nokar.">".@numb_format($premibruto)."</td>";
								$tab.="<td align=right style=display:none; id=ttlblmdenda_".$no."_".$nokar.">".@numb_format($premibruto)."</td>";

								$tab.="<td align=center><input ".$disbled."  type=text  value=0 onkeyup=\"z.numberFormat('denda_".$no."_".$nokar."',0);gettotal(".$no.",'ttlblmdenda_".$no."_".$nokar."','denda_".$no."_".$nokar."','premiinput_".$no."_".$nokar."');\" id=denda_".$no."_".$nokar." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";
								
								$tab.="<td align=right id=premiinput_".$no."_".$nokar.">".@numb_format($premibruto)."</td>";
								$tab.="</tr>";

								@$premilbtot[$mandor]+=$premilb[$mandor][$tgl];
								@$pembagitot[$mandor]+=$pembagi[$mandor][$tgl];
								@$tpremirata[$mandor]+=$premirata;
								@$tpremibruto[$mandor]+=$premibruto;
								@$tpremitot[$mandor]+=$premibruto;
								
							#}
						}
						$tab.="<input hidden value=".$nokar." id=totalbaris".$no.">";
						$tab.="<tr class=rowcontent>";
							$tab.="<td></td><td colspan=2>Total Premi</td>";
							$tab.="<td align=right>".@number_format($premilbtot[$mandor])."</td>";
							$tab.="<td align=right id=ttlpembagi".$no.">".@number_format($pembagitot[$mandor])."</td>";
							$tab.="<td align=right id=ttlprebrutorata".$no.">".@number_format($tpremirata[$mandor])."</td>";
							$tab.="<td align=right></td>";
							$tab.="<td align=right id=ttlprebruto".$no.">".@number_format($tpremibruto[$mandor])."</td>";
							$tab.="<td align=right id=ttldenda".$no."></td>";
							$tab.="<td align=right id=ttlprenetto".$no.">".@number_format($tpremibruto[$mandor])."</td>";
						$tab.="</tr>";
						$tab.="</tbody></table><br>";
					}
				}
				
				$tab.="<button class=mybutton onclick=saveAll('1','1','".$no."','".$nokar."');>".$_SESSION['lang']['proses']."</button>";
				echo $tab;
			break;
			
			case'MANDOR1':
				$where='';
				if($kontanan=='KONTAN'){
					$where=" and a.tanggalkontanan = '".$tglmulai."'";
					$whtgl=$tglmulai;
				}else{
					$whtgl=$prd;
				}
				#ambil mandor1

				$w="";
				$w=" and a.tahap ='".$tahap."'";
				$str="select a.*,b.nikmandor1 from ".$dbname.".kebun_premikemandoran a 
				left join ".$dbname.".kebun_aktifitas b on a.karyawanid=b.nikmandor  and b.nikmandor1!='' and a.tanggal=b.tanggal
				where b.tipetransaksi='PNN' and b.tanggal like '".$whtgl."%' and a.jabatan='MANDORPANEN' 
				and a.periode='".$prd."' and a.kodeorg='".$unit."' and a.kontanan='".$kontanan."' ".$where." ".$w." ".$whereMandor1." and (b.noreferensi='' or (b.deviceid!='' and b.flag='1'))";		
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtmandor[$bar['nikmandor1']][$bar['tanggal']]=$bar['nikmandor1'];
					$karyawanid[$bar['tanggal']][$bar['karyawanid']]=$bar['karyawanid'];
					$listkar[$bar['nikmandor1']][$bar['tanggal']][$bar['karyawanid']]=$bar['karyawanid'];
					@$premisumber[$bar['nikmandor1']][$bar['tanggal']][$bar['karyawanid']]+=$bar['premisumber'];
					@$premihitung[$bar['nikmandor1']][$bar['tanggal']][$bar['karyawanid']]+=$bar['premikomputer']+$bar['kehadiran'];
					@$denda[$bar['nikmandor1']][$bar['tanggal']][$bar['karyawanid']]+=$bar['denda'];
					@$premidapat[$bar['nikmandor1']][$bar['tanggal']][$bar['karyawanid']]+=$bar['premiinput'];
				}
				
				#jika sudah disimpan ambil datanya
				$str="select * from ".$dbname.".kebun_premikemandoran a 
				where a.jabatan='MANDOR1' and a.periode='".$prd."' and a.kodeorg='".$unit."' and a.kontanan='".$kontanan."' ".$where." ".$w." and a.karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."') ";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dendam1[$bar['karyawanid']][$bar['tanggal']][$bar['idmandor']]+=$bar['denda'];
				}
				if(empty($dtmandor)){
					exit("Warning : Silahkan lakukan proses premi mandor panen terlebih dahulu.");
				}
			
				$stream='';
				if(!empty($dtmandor)){

						$no=0;
					foreach($dtmandor as $mandor => $arrtanggal){
							if ($proses == 'excel') {
							$stream.="<table class=sortable cellspacing=1 cellpadding=5 border=1>";
							} else 	{
								$stream.="<table class=sortable cellspacing=1 cellpadding=5 style=min-width:700px>";
							}

							$stream.="<thead>";
							$no++;
							$stream.="<tr class=rowcontent id=rowsatu".$no.">";
							$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$mandor."'");
							$stream.="<td colspan=12 align=left bgcolor=#CCCCCC align=center>".$_SESSION['lang']['mandor']." I : ".$nmkar[$mandor]."</td>"; 
							$stream.="<td hidden id=mandor".$no.">".$mandor."</td>";
							$stream.="<td hidden id=jabatan".$no.">".$param['jabatan']."</td>";
							$stream.="</tr>";
							
							$stream.="<tr class=rowheader>";
							$stream.="<td align=center rowspan='2'>".$_SESSION['lang']['nourut']."</td>";
							$stream.="<td align=center rowspan='2'>".$_SESSION['lang']['nik2']."</td>";
							$stream.="<td align=center rowspan='2'>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['mandor']."</td>";
							$stream.="<td align=center rowspan='2'>Tanggal</td>";
							$stream.="<td align=center colspan=3>Premi Mandor Panen</td>";
							$stream.="<td align=center colspan=5>Premi Mandor Satu</td>";
							
							$stream.="</tr>";
							$stream.="<tr class=rowheader>";
							
							$stream.="<td align=center>Bruto</td>";
							$stream.="<td align=center>Denda</td>";
							$stream.="<td align=center>Netto</td>";
							$stream.="<td align=center>Pembagi</td>";
							$stream.="<td align=center>Pengali</td>";
							$stream.="<td align=center>Bruto</td>";
							$stream.="<td align=center width=50px>Denda</td>";
							$stream.="<td align=center>Netto</td>";
							
							//pembagi	premi sumber	premi komputer	denda	premi input
							$stream.="</tr>";
							$stream.="</thead>";
							$nokar=0;
						foreach ($arrtanggal as $tglx => $mandorx) {
							
							#ambil setup premi mandor
							$str="select * from ".$dbname.".kebun_5premimandor where kodeorg='".$unit."' and jenis='".$jab."'";
							$res=fetchdata($str)[0];			
							$nilai=$res['nilaipengali'];
							$minimalpembagi=$res['minimalpembagi'];
							
							foreach($karyawanid[$tglx] as $karid){
								if($listkar[$mandor][$tglx][$karid]!=''){
									$pembagi=count($karyawanid[$tglx]);
									if($pembagi<$minimalpembagi){
										$pembagi=$minimalpembagi;
									}
									$nokar+=1;
									$stream.="<tr class=rowcontent id=baris".$no."_".$nokar.">";
									$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$karid."'");
									$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karid."'");
									$stream.="<td align=center>".$nokar."</td>";
									$stream.="<td align=center hidden id=idmandor_".$no."_".$nokar.">".$karid."</td>";
									$stream.="<td align=center>".$nikkar[$karid]."</td>";
									$stream.="<td>".$nmkar[$karid]."</td>";
									$stream.="<td id=tgl_".$no."_".$nokar.">".$tglx."</td>";
									$stream.="<td align=right>".@number_format($premihitung[$mandor][$tglx][$karid])."</td>";
									$stream.="<td align=right>".@number_format($denda[$mandor][$tglx][$karid])."</td>";
									$stream.="<td align=right id=premisumber_".$no."_".$nokar.">".@number_format($premidapat[$mandor][$tglx][$karid])."</td>";
									$stream.="<td align=right id=bagi_".$no."_".$nokar.">".$pembagi."</td>";
									$stream.="<td align=right id=kali_".$no."_".$nokar.">".$nilai."</td>";
									$premikotor=$premidapat[$mandor][$tglx][$karid]/$pembagi*$nilai;
									$stream.="<td align=right id=premisatu".$no."_".$nokar.">".@number_format($premikotor)."</td>";
									
									$stream.="<td align=right><input type=text onkeyup=\"z.numberFormat('denda_".$no."_".$nokar."',0);gettotal(".$no.",'premisatu".$no."_".$nokar."','denda_".$no."_".$nokar."','premitotalsatu".$no."_".$nokar."');\" id=denda_".$no."_".$nokar."  size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:70px;\" value=".$dendam1[$mandor][$tglx][$karid]."></td>";
									
									$premibersih=$premikotor-$dendam1[$mandor][$tglx][$karid];
									$stream.="<td align=right id=premitotalsatu".$no."_".$nokar.">".@number_format($premibersih)."</td>";
									$stream.="</tr>";
									
									$tpremikotor[$mandor]+=$premikotor;
									@$tpremihitung[$mandor]+=$premihitung[$mandor][$tglx][$karid];
									@$tdenda[$mandor]+=$denda[$mandor][$tglx][$karid];
									@$tdendam1[$mandor]+=$dendam1[$mandor][$tglx][$karid];
									@$tpremidapat[$mandor]+=$premidapat[$mandor][$tglx][$karid];
								}
							}




						}
						
					}
							$stream.="<tr class=rowcontent>";
							$stream.="<td><input hidden id=totalbaris".$no." value=".$nokar."></td><td colspan=3>Total Premi Mandor</td>";
							$stream.="<td align=right>".@number_format($tpremihitung[$mandor])."</td>";
							$stream.="<td align=right>".@number_format($tdenda[$mandor])."</td>";
							$stream.="<td align=right id=ttlpresumber".$no.">".@number_format($tpremidapat[$mandor])."</td>";
							$stream.="<td align=right>".count($karyawanid)."</td>";
							$stream.="<td align=right>".$nilai."</td>";
							$stream.="<td align=right id=ttlprebruto".$no.">".@number_format($tpremikotor[$mandor])."</td>";
							$stream.="<td align=right id=ttldenda".$no.">".@number_format($tdendam1[$mandor])."</td>";
							$stream.="<td align=right id=ttlprenetto".$no.">".@number_format($tpremikotor[$mandor]-$tdendam1[$mandor])."</td>";
							$stream.="</tr>";
							$stream.="</tbody></table><br>";
				}

				if($posting==0){
					$stream.="<button class=mybutton onclick=saveAll('1','1','".$no."','".$nokar."');>".$_SESSION['lang']['proses']."</button>";
				}
				echo $stream;
			break;
			
			// case'MANDORDERES':
			// 	$y.=" and b.nikmandor in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."')";
			// 	$str = "select a.upahkerja, a.nikpemel, b.nikmandor from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.kodeorg='".$unit."' and b.tanggal like '".$prd."%' and a.kodekegiatan='611040201' and kodesegment='0000000002' ".$y." order by tanggal";
			// 	$res=fetchdata($str);
			// 	foreach($res as $val){
			// 		$upah[$val['nikmandor']][$val['nikpemel']]+=fixnan($val['upahkerja']);
			// 	}
				
			// 	$str='';$w='';
			// 	$premilb = $pembagi = $arrbagi = [];
			// 	$w.=" and mandor in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."')";
			// 	$str="select * from ".$dbname.".kebun_3premipemanenkaret where periode='".$prd."' ".$w." and posting='1' order by mandor";
			// 	$res=fetchdata($str);
			// 	foreach($res as $bar){
			// 		$dtmandor[$bar['mandor']]=$bar['mandor'];
			// 		$jabatan[$bar['mandor']]='MANDOR DERES';
			// 		//$tanggal[$bar['tanggalpanen']]=$bar['tanggalpanen'];
			// 		$listkar[$bar['mandor']][$bar['karyawanid']]=$bar['karyawanid'];
			// 		$listblok[$bar['mandor']][$bar['karyawanid']]=$bar['blok'];
			// 		@$kg[$bar['mandor']][$bar['karyawanid']]+=$bar['kgwb'];
			// 		$tt[$bar['mandor']][$bar['karyawanid']]=$bar['tahuntanam'];
			// 		// $upah[$bar['mandor']][$bar['karyawanid']]+=fixnan(getupahharian($prd,$bar['karyawanid'])*$bar['hk']);
			// 		$premilb[$bar['mandor']][$bar['karyawanid']]+=$bar['totalpreminetto'];
			// 		$arrbagi[$bar['mandor']][$bar['karyawanid']][$bar['karyawanid']]=$bar['karyawanid'];
			// 	}

			// 	foreach($arrbagi as $mdr => $val1){
			// 		foreach($val1 as $tgl => $val2){
			// 			foreach($val2 as $kary){
			// 				$pembagi[$mdr][$tgl]++;
			// 			}
			// 		}
			// 	}

			// 	if(empty($dtmandor)){
			// 		exit("Warning : Silahkan lakukan proses premi pemanen terlebih dahulu.");
			// 	}
			// 	$tab='';
			// 	if(!empty($dtmandor)){
			// 		$no=0;
			// 		foreach($dtmandor as $mandor){
			// 			if ($proses == 'excel') {
			// 				$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=1>";
			// 			} else 	{
			// 				$tab.="<table class=sortable cellspacing=1 cellpadding=5 style=min-width:700px>";
			// 			}
						
			// 			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$mandor."'");
			// 			$tab.="<thead>";
			// 			$no++;
			// 			$tab.="<tr class=rowcontent id=row".$no.">";
			// 				$tab.="<th colspan=11 align=left bgcolor=#CCCCCC align=center>".$_SESSION['lang']['mandor']." : <b>".$nmkar[$mandor]."</b></th>"; 
			// 				$tab.="<th hidden id=mandor".$no.">".$mandor."</th>";
			// 				$tab.="<th hidden id=jabatan".$no.">".$param['jabatan']."</th>";
			// 			$tab.="</tr>";
						
			// 			$tab.="<tr class=rowheader>";
			// 			$tab.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
			// 			$tab.="<th align=center>".$_SESSION['lang']['namakaryawan']."</th>";
			// 			$tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
			// 			$tab.="<th align=center>".$_SESSION['lang']['upah']."</th>";
			// 				$tab.="<th align=center width=100px>Total Premi LB Kary</th>";
			// 				$tab.="<th align=center width=70px>Pembagi</th>";
			// 				$tab.="<th align=center width=70px>Rata Rata Premi</th>";
			// 				$tab.="<th align=center width=70px>Nilai Pengali</th>";
			// 				$tab.="<th align=center width=70px>Premi Bruto</th>";
			// 				$tab.="<th align=center width=70px>".$_SESSION['lang']['denda']."</th>";
			// 				$tab.="<th align=center width=70px>".$_SESSION['lang']['total']." pendapatan</th>";
			// 			$tab.="</tr>";
			// 			$tab.="</thead>";
			// 			$nokar=0;
			// 			$color='';
			// 			#ambil setup premi mandor
			// 			$str="select * from ".$dbname.".kebun_5premimandor where kodeorg='".$unit."' and jenis='".$jab."'";
			// 			$res=fetchdata($str)[0];			
			// 			$nilai=$res['nilaipengali'];
			// 			$minimalpembagi=$res['minimalpembagi'];
			// 			foreach($listkar[$mandor] as $karyawanid){
			// 				$nokar++;
			// 				$tab.="<tr class=rowcontent id=baris".$no."_".$nokar.">";
			// 				$tab.="<td align=center>".$nokar."</td>";
			// 				$tab.="<td align=left>".getNamaKaryawan($karyawanid)."</td>";
			// 				$tab.="<td align=left>".$listblok[$mandor][$karyawanid]."</td>";
			// 				$tab.="<td align=right id=upah_".$no."_".$nokar.">".@numb_format($upah[$mandor][$karyawanid])."</td>";
			// 				$tab.="<td align=right id=premibruto_".$no."_".$nokar.">".@numb_format($premilb[$mandor][$karyawanid])."</td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="</tr>";
							
			// 				@$upahtot[$mandor]+=$upah[$mandor][$karyawanid];
			// 				@$premilbtot[$mandor]+=$premilb[$mandor][$karyawanid];
			// 			}
			// 			$tab.="<input hidden value=".$nokar." id=totalbaris".$no."".$nokar.">";
			// 			$tpremirata=(($upahtot[$mandor]+$premilbtot[$mandor])/$nokar);
			// 			$tpremibruto=($tpremirata*$nilai);
			// 			$tpreminetto=($tpremirata*$nilai);
			// 			$tab.="<tr class=rowcontent>";
			// 				$tab.="<td></td><td colspan=2>Total Premi</td>";
			// 				$tab.="<td align=right id=ttlupah_".$no."_".$nokar.">".@number_format($upahtot[$mandor])."</td>";
			// 				$tab.="<td align=right id=ttlpremilb_".$no."_".$nokar.">".@number_format($premilbtot[$mandor])."</td>";
			// 				$tab.="<td align=right id=ttlpembagi_".$no."_".$nokar.">".$nokar."</td>";
			// 				$tab.="<td align=right id=ttlprerata_".$no."_".$nokar.">".@number_format($tpremirata)."</td>";
			// 				$tab.="<td align=right id=pengali_".$no."_".$nokar.">".@hidezerodecimal($nilai,3)."</td>";
			// 				$tab.="<td align=right id=ttlprebruto_".$no."_".$nokar.">".@number_format($tpremibruto)."</td>";
			// 				$tab.="<td align=center><input type=text  value=0 onkeyup=\"z.numberFormat('denda_".$no."_".$nokar."',0);gettotalderes('".$no."','".$nokar."');\" id=denda_".$no."_".$nokar." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";
			// 				$tab.="<td align=right id=ttlprenetto_".$no."_".$nokar.">".@number_format($tpreminetto)."</td>";
			// 			$tab.="</tr>";
			// 			$tab.="</tbody></table><br>";
			// 		}
			// 	}
				
			// 	$tab.="<button class=mybutton onclick=saveAll('1','1','".$no."','".$nokar."');>".$_SESSION['lang']['proses']."</button>";
			// 	echo $tab;
			// break;
			
			// case'KERANIDERES':
			// 	$str='';$w='';
			// 	$premilb = $pembagi = $arrbagi = [];
			// 	$w.=" and kerani in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."')";
			// 	$str="select * from ".$dbname.".kebun_3premipemanenkaret where periode='".$prd."' ".$w." and posting='1' order by mandor";
			// 	$res=fetchdata($str);
			// 	foreach($res as $bar){
			// 		$dtmandor[$bar['kerani']]=$bar['kerani'];
			// 		$jabatan[$bar['kerani']]='KERANI DERES';
			// 		//$tanggal[$bar['tanggalpanen']]=$bar['tanggalpanen'];
			// 		$listkar[$bar['kerani']][$bar['karyawanid']]=$bar['karyawanid'];
			// 		$listblok[$bar['kerani']][$bar['karyawanid']]=$bar['blok'];
			// 		@$kg[$bar['kerani']][$bar['karyawanid']]+=$bar['kgwb'];
			// 		$tt[$bar['kerani']][$bar['karyawanid']]=$bar['tahuntanam'];
			// 		// $upah[$bar['kerani']][$bar['karyawanid']]+=fixnan(getupahharian($prd,$bar['karyawanid'])*$bar['hk']);
			// 		$premilb[$bar['kerani']][$bar['karyawanid']]+=$bar['totalpreminetto'];
			// 		$arrbagi[$bar['kerani']][$bar['karyawanid']][$bar['karyawanid']]=$bar['karyawanid'];
					
			// 		$strx = "select sum(a.upahkerja) as upahkerja from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.kodeorg='".$unit."' and b.tanggal like '".$prd."%' and a.kodekegiatan='611040201' and kodesegment='0000000002' and a.nikpemel='".$bar['karyawanid']."' order by tanggal";
			// 		$resx=fetchdata($strx);
			// 		$upah[$bar['kerani']][$bar['karyawanid']]+=fixnan($resx[0]['upahkerja']);
			// 	}

			// 	foreach($arrbagi as $mdr => $val1){
			// 		foreach($val1 as $tgl => $val2){
			// 			foreach($val2 as $kary){
			// 				$pembagi[$mdr][$tgl]++;
			// 			}
			// 		}
			// 	}

			// 	if(empty($dtmandor)){
			// 		exit("Warning : Silahkan lakukan proses premi pemanen terlebih dahulu.");
			// 	}
			// 	$tab='';
			// 	if(!empty($dtmandor)){
			// 		$no=0;
			// 		foreach($dtmandor as $mandor){
			// 			if ($proses == 'excel') {
			// 				$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=1>";
			// 			} else 	{
			// 				$tab.="<table class=sortable cellspacing=1 cellpadding=5 style=min-width:700px>";
			// 			}
						
			// 			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$mandor."'");
			// 			$tab.="<thead>";
			// 			$no++;
			// 			$tab.="<tr class=rowcontent id=row".$no.">";
			// 				$tab.="<th colspan=11 align=left bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kerani']." : <b>".$nmkar[$mandor]."</b></th>"; 
			// 				$tab.="<th hidden id=mandor".$no.">".$mandor."</th>";
			// 				$tab.="<th hidden id=jabatan".$no.">".$param['jabatan']."</th>";
			// 			$tab.="</tr>";
						
			// 			$tab.="<tr class=rowheader>";
			// 			$tab.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
			// 			$tab.="<th align=center>".$_SESSION['lang']['namakaryawan']."</th>";
			// 			$tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
			// 			$tab.="<th align=center>".$_SESSION['lang']['upah']."</th>";
			// 				$tab.="<th align=center width=100px>Total Premi LB Kary</th>";
			// 				$tab.="<th align=center width=70px>Pembagi</th>";
			// 				$tab.="<th align=center width=70px>Rata Rata Premi</th>";
			// 				$tab.="<th align=center width=70px>Nilai Pengali</th>";
			// 				$tab.="<th align=center width=70px>Premi Bruto</th>";
			// 				$tab.="<th align=center width=70px>".$_SESSION['lang']['denda']."</th>";
			// 				$tab.="<th align=center width=70px>".$_SESSION['lang']['total']." pendapatan</th>";
			// 			$tab.="</tr>";
			// 			$tab.="</thead>";
			// 			$nokar=0;
			// 			$color='';
			// 			#ambil setup premi mandor
			// 			$str="select * from ".$dbname.".kebun_5premimandor where kodeorg='".$unit."' and jenis='".$jab."'";
			// 			$res=fetchdata($str)[0];			
			// 			$nilai=$res['nilaipengali'];
			// 			$minimalpembagi=$res['minimalpembagi'];
			// 			foreach($listkar[$mandor] as $karyawanid){
			// 				$nokar++;
			// 				$tab.="<tr class=rowcontent id=baris".$no."_".$nokar.">";
			// 				$tab.="<td align=center>".$nokar."</td>";
			// 				$tab.="<td align=left>".getNamaKaryawan($karyawanid)."</td>";
			// 				$tab.="<td align=left>".$listblok[$mandor][$karyawanid]."</td>";
			// 				$tab.="<td align=right id=upah_".$no."_".$nokar.">".@numb_format($upah[$mandor][$karyawanid])."</td>";
			// 				$tab.="<td align=right id=premibruto_".$no."_".$nokar.">".@numb_format($premilb[$mandor][$karyawanid])."</td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="<td align=left></td>";
			// 				$tab.="</tr>";
							
			// 				@$upahtot[$mandor]+=$upah[$mandor][$karyawanid];
			// 				@$premilbtot[$mandor]+=$premilb[$mandor][$karyawanid];
			// 			}
			// 			$tab.="<input hidden value=".$nokar." id=totalbaris".$no."".$nokar.">";
			// 			$tpremirata=(($upahtot[$mandor]+$premilbtot[$mandor])/$nokar);
			// 			$tpremibruto=($tpremirata*$nilai);
			// 			$tpreminetto=($tpremirata*$nilai);
			// 			$tab.="<tr class=rowcontent>";
			// 				$tab.="<td></td><td colspan=2>Total Premi</td>";
			// 				$tab.="<td align=right id=ttlupah_".$no."_".$nokar.">".@number_format($upahtot[$mandor])."</td>";
			// 				$tab.="<td align=right id=ttlpremilb_".$no."_".$nokar.">".@number_format($premilbtot[$mandor])."</td>";
			// 				$tab.="<td align=right id=ttlpembagi_".$no."_".$nokar.">".$nokar."</td>";
			// 				$tab.="<td align=right id=ttlprerata_".$no."_".$nokar.">".@number_format($tpremirata)."</td>";
			// 				$tab.="<td align=right id=pengali_".$no."_".$nokar.">".@hidezerodecimal($nilai,3)."</td>";
			// 				$tab.="<td align=right id=ttlprebruto_".$no."_".$nokar.">".@number_format($tpremibruto)."</td>";
			// 				$tab.="<td align=center><input type=text  value=0 onkeyup=\"z.numberFormat('denda_".$no."_".$nokar."',0);gettotalderes('".$no."','".$nokar."');\" id=denda_".$no."_".$nokar." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";
			// 				$tab.="<td align=right id=ttlprenetto_".$no."_".$nokar.">".@number_format($tpreminetto)."</td>";
			// 			$tab.="</tr>";
			// 			$tab.="</tbody></table><br>";
			// 		}
			// 	}
				
			// 	$tab.="<button class=mybutton onclick=saveAll('1','1','".$no."','".$nokar."');>".$_SESSION['lang']['proses']."</button>";
			// 	echo $tab;
			// break;
			
			default:
				echo "Inprogress <br><br>";
			break;
		}
	break;
	case'previewdetail':
		
		#ambil setup premi mandor
		$str="select * from ".$dbname.".kebun_5premimandor where kodeorg='".$unit."' and jenis='".$jab."'";
		$res=fetchdata($str)[0];			
		$nilai=$res['nilaipengali'];

		$no = 0;
		if ($proses == 'excel') {
			$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=1>";
		} else 	{
			$tab .= "<table class='sortable' cellspacing='1' cellpadding='5' style='width:700px; margin:auto; text-align:center;'>";
		}

			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$mandor."'");

		$tab.="<thead>";

			$tab.="<tr class=rowcontent >";
				$tab.="<th colspan=11 align=left bgcolor=#CCCCCC align=center>".$jab." : <b>".$nmkar[$mandor]."</b></th>"; 
			$tab.="</tr>";

			$tab.="<tr class=rowheader>";
			$tab.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
			$tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
			$tab.="<th align=center>".$_SESSION['lang']['hari']."</th>";
			$tab.="<th align=center width=100px>Premi LB Kary</th>";
			$tab.="<th align=center width=70px>Pembagi</th>";
			$tab.="<th align=center width=70px>Rata Rata Premi</th>";
			$tab.="<th align=center width=70px>Nilai Pengali</th>";
			$tab.="<th align=center width=70px>Premi Bruto</th>";
			$tab.="<th align=center width=70px>".$_SESSION['lang']['denda']."</th>";
			$tab.="<th align=center width=70px>".$_SESSION['lang']['total']." pendapatan</th>";
		$tab.="</tr>";
		$tab.="</thead>";
						
		$str="select * from ".$dbname.".kebun_premikemandoran where periode = '".$prd."' and karyawanid = '".$mandor."' and jabatan = '".$jab."' and kodeorg = '".$unit."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent >";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=center>".$bar['tanggal']."</td>";
				$tab.="<td align=center>".$bar['jenishari']."</td>";
				$tab.="<td align=center>".number_format($bar['premikomputer'],2)."</td>";
				$tab.="<td align=center>".$bar['pembagi']."</td>";

				$premirata=@fixnan(round($bar['premikomputer']/$bar['pembagi'],0));

				$tab.="<td align=center>".number_format($premirata,2)."</td>";
				$tab.="<td align=center>".$nilai."</td>";
				$tab.="<td align=center>".number_format($bar['premisumber'],2)."</td>";
				$tab.="<td align=center>".number_format($bar['denda'],2)."</td>";
				$tab.="<td align=center>".number_format($bar['premiinput'],2)."</td>";
			$tab.="</tr>";
		}


		
	echo $tab;
	break;

	case'savedata':
	try {
		$owlPDO->beginTransaction();
		#cek posting
		$str = "select * from ".$dbname.".kebun_premikemandoran where `kodeorg`='".$unit."' and `karyawanid`='".$mandor."' and `periode`='".$prd."' and tahap='".$tahap."' and jabatan='".$jab."' and kontanan='".$kontanan."' and posting='1'";
		$res=fetchdata($str);
		if(count($res)>0){
			throw new PDOException("Transaksi Sudah di POSTING.");
		}
		#cek apakah periode gaji sudah di tutup
		$str = "select * from ".$dbname.".sdm_5periodegaji where periode like '" . $prd. "' and kodeorg='".$unit."'  and jenisgaji!='S'";
		$res=fetchdata($str);
		$jumdata=0;
		foreach($res as $bar){
			$jumdata+=$bar['sudahproses'];
		}
		if($jumdata>0){
			throw new PDOException("Periode Gaji " . $prd. " untuk Unit ".$unit." sudah di tutup.");
		}
		
		#delete 1st
		if($baris==1){			
			$str="delete from ".$dbname.".kebun_premikemandoran where `kodeorg`='".$unit."' and `karyawanid`='".$mandor."' and `periode`='".$prd."' and tahap='".$tahap."' and jabatan='".$jab."' and kontanan='".$kontanan."'"; #and tanggal between '".$tgl1."' and '".$tgl2."'";
			$owlPDO->exec($str);
		}
		
		$param['premi']      =str_replace(',','',$param['premi']);
		$param['denda']      =str_replace(',','',$param['denda']);
		$param['premitotal'] =str_replace(',','',$param['premitotal']);
		$param['premisumber']=str_replace(',','',$param['premisumber']);
		
		if($tgl==''){$tgl=$tgl1;}
		#insert
		$data = array(
			'kodeorg'      => $unit,
			'divisi'       => $afd,
			'periode'      => $prd,
			'tanggal'      => $tgl,
			'tahap'        => $tahap,
			'idmandor'     => $param['idmandor'],
			'karyawanid'   => $mandor,
			'jabatan'      => $jab,
			'jenishari'    => $param['hari'],
			'tahuntanam'   => '',
			'kg'           => '',
			'harga'        => '',
			'pembagi'      => $param['bagi'],
			'kehadiran'    => '',
			'premisumber'  => $param['premisumber'],
			'premikomputer'=> $param['premi'],
			'denda'        => $param['denda'],
			'premiinput'   => $param['premitotal'],
			'updateby'     => $_SESSION['standard']['userid'],
			'kontanan'     => $kontanan
		);
		$cols = array();
		foreach($data as $key=>$row) {
				$cols[] = $key;
		}
		$str = insertQuery($dbname,'kebun_premikemandoran',$data,$cols);
		if($param['bagi']>0 or $param['kehadiran']>0 or $param['premisumber']>0 or $param['premi']>0 or $param['denda']>0 or $param['premitotal']>0){
			if($jab=='MANDORDERES' or $jab=='KERANIDERES'){
				if($param['baris']==$param['maxrowdet']){
					$owlPDO->exec($str);
				}
			}else{
				$owlPDO->exec($str);
			}
		}
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
    break; 
	case'delete':
		
		#cek apakah periode gaji sudah di tutup
		$str = "select * from ".$dbname.".sdm_5periodegaji where periode like '".$param['periode']."' and kodeorg='".$unit."'  and jenisgaji!='S' ";
		$res=fetchdata($str);
		$jumdata=0;
		foreach($res as $bar){
			$jumdata+=$bar['sudahproses'];
		}
		if($jumdata>0){
			exit("Warning : Periode Gaji " .$param['periode']. " untuk Unit ".$unit." sudah di tutup.");
		}
		
		#cek apakah periode akutansi sudah di tutup
		$str = "select * from ".$dbname.".setup_periodeakuntansi where periode like '" .$param['periode']. "' and kodeorg='".$unit."'  ";
		$res=fetchdata($str);
		$jumdata=0;
		foreach($res as $bar){
			$jumdata+=$bar['tutupbuku'];
		}
		if($jumdata>0){
			exit("Warning : Periode akuntansi " .$param['periode']. " untuk Unit ".$unit." sudah di tutup.");
		}

        $str = "delete from ".$dbname.".kebun_premikemandoran where kodeorg='".$unit."' and periode='".$param['periode']."' and karyawanid='".$param['karyid']."' and jabatan='".$param['jabatan']."' and tahap='".$param['tahap']."'"; #and tanggal between '".$tglmulai."' and '".$tglakhir."'";
		// exit("error".$str);
		
		try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>