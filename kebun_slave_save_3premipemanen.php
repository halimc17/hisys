<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');


$prdlist     =checkPostGet('prdlist','');
$unitlist    =checkPostGet('unitlist','');
$afdlist     =checkPostGet('afdlist','');
$divisi      =checkPostGet('divisi','');
$tipe        =checkPostGet('tipe','');
$proses      =checkPostGet('proses','');
$notransaksi =checkPostGet('notransaksi','');
$prd         =checkPostGet('prd','');
$unit        =checkPostGet('unit','');
$afd         =checkPostGet('afd','');
$kary        =checkPostGet('rowkary','');
$mdr         =checkPostGet('rowmdr','');
$krn         =checkPostGet('rowkrn','');
$tt          =checkPostGet('rowtt','');
$hk          =checkPostGet('rowhk','');
$jjg         =checkPostGet('rowjjg','');
$kg          =checkPostGet('rowkg','');
$kgbss       =checkPostGet('rowkgbss','');
$kglb1       =checkPostGet('rowkglb1','');
$rplb1       =checkPostGet('rowrplb1','');
$kglb2       =checkPostGet('rowkglb2','');
$rplb2       =checkPostGet('rowrplb2','');
$kgbrd       =checkPostGet('rowkgbrd','');
$rpbrd       =checkPostGet('rowrpbrd','');
$denda       =checkPostGet('rowdenda','');
$banjir      =checkPostGet('banjir','');
$tglpnn      =checkPostGet('tglpnn','');
$topografi   =checkPostGet('topografi','');
$rptopo   =checkPostGet('rowtopo','');
$tahap   =checkPostGet('tahap','');

$tgl1        =tanggalsystemn(checkPostGet('tgl1',''));
$tgl2        =tanggalsystemn(checkPostGet('tgl2',''));

/* if($tahap=='1'){
	$tgl1 = $prd."-01";
	$tgl2 = $prd."-15";
}else{
	$tgl1 = $prd."-16";
	$tgl2 = tglakhir($tgl1);
}
 */
$hk          =str_replace(',','',$hk);
$jjg         =str_replace(',','',$jjg);
$kg          =str_replace(',','',$kg);
$kgbss       =str_replace(',','',$kgbss);
$kglb1       =str_replace(',','',$kglb1);
$rplb1       =str_replace(',','',$rplb1);
$kglb2       =str_replace(',','',$kglb2);
$rplb2       =str_replace(',','',$rplb2);
$kgbrd       =str_replace(',','',$kgbrd);
$rpbrd       =str_replace(',','',$rpbrd);
$denda       =str_replace(',','',$denda);
$rptopo       =str_replace(',','',$rptopo);

$nikkar      =makeOption($dbname,'datakaryawan','karyawanid,nik');
$nmorg       =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$jab         =getPostingJabatan('premipanen');
$tglEntry    =date('Ymd'); 
switch($proses){
	case'deleteTrans':
		#Validasi :
		#1. Cek Prd Akuntansi
		$str="select * from ".$dbname.".setup_periodeakuntansi
		where periode = '".$prd."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['tutupbuku']=='1'){
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		
		#2. Cek Prd Gaji
		$str="select * from ".$dbname.".sdm_5periodegaji
		where periode = '".$prd."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['sudahproses']=='1'){
			exit('Error : Periode Gaji Sudah di Tutup.');
		}
		#3. Cek Transaksi sudah di posting belum
		$str="select * from ".$dbname.".kebun_3premipemanen
		where periode = '".$prd."' and kodeorg='".$unit."' and notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['posting']=='1'){
			exit('Error : Transaksi notransaksi : '.$notransaksi.' unit : '.$unit.' periode : '.$prd.' sudah di Posting.');
		}
		
		#Hapus Transaksi
		$str="delete from ".$dbname.".kebun_3premipemanen where `notransaksi` ='".$notransaksi."' and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		
	break;
    case'savedata':
		$str="select * from ".$dbname.".kebun_spb_vw where tanggalpanen between '".$tgl1."' and '".$tgl2."' and kodeorg='".$unit."' and divisi like '".$afd."%' and posting='0'";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			exit('Error : Ada SPB yang belum di Posting');
		}
		
		$str="select * from ".$dbname.".kebun_prestasi_vs_hk
		where tanggal between '".$tgl1."' and '".$tgl2."' and unit='".$unit."' and kodeorg like '".$afd."%' and jurnal='0'";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			exit('Error : Ada transaksi Kegiatan Panen yang belum di Posting');
		}
		$str="insert into ".$dbname.".kebun_3premipemanen (`notransaksi`,`kodeorg`,`divisi`,`periode`,`mandor`,
			 `kerani`,`tahuntanam`,`karyawanid`,`hk`,`jjgpanen`,`kgwb`,`basiskg`,`kglb1`,`rplb1`,`kglb2`,`rplb2`,
			 `kgbrd`,`rpbrd`,`kehadiran`,`denda`,`updateby`,`status`,`tanggalpanen`,`tahap`)
			  values ('".$notransaksi."','".$unit."','".$afd."','".$prd."','".$mdr."','".$krn."','".$tt."','".$kary."',
			  '".$hk."','".$jjg."','".$kg."','".$kgbss."','".$kglb1."','".$rplb1."','".$kglb2."','".$rplb2."',
			  '".$kgbrd."','".$rpbrd."','".$rptopo."','".$denda."','".$_SESSION['standard']['userid']."','".$topografi."','".$tglpnn."','".$tahap."')";
		
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}			

    break;
	
	case'unposting':
	#========================= Validasi Data ===========================
	#1. Cek Prd Akuntansi
	$str="select * from ".$dbname.".setup_periodeakuntansi where periode = '".$prd."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['tutupbuku']=='1'){
		exit('Error : Periode Akuntansi Sudah di Tutup.');
	}
	#2. Cek Prd Gaji
	$str="select * from ".$dbname.".sdm_5periodegaji where periode = '".$prd."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['sudahproses']=='1'){
		exit('Error : Periode Gaji Sudah di Tutup.');
	}
	#========================= End Validasi Data ===========================
	#============================= Update ==================================
	$errorDB='';
	# Ambil no jurnal
	$queryParam = selectQuery($dbname,'kebun_3premipemanen','distinct (jurnal) as jurnal',"notransaksi='".$notransaksi."'");
	$resParam = fetchData($queryParam);
	# Hapus Jurnal
	$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$resParam[0]['jurnal']."' and noreferensi='".$notransaksi."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Delete Jurnal !: " . $e->getMessage() . "\n"; die();}
	# Update flag transaksi
	$str="update ".$dbname.".kebun_3premipemanen set posting='0', jurnal = '', postingby ='".$_SESSION['standard']['userid']."', postingdate='".$tglEntry."' where notransaksi='".$notransaksi."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Update Flag !: " . $e->getMessage() . "\n"; die();}
	# Hapus Kebun_Aktifitas
	$str="delete from ".$dbname.".kebun_aktifitas where noreferensi ='".$notransaksi."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Delete kebun_aktifitas !: " . $e->getMessage() . "\n"; die();}
	# Jika gagal
	if ($errorDB!=''){
		exit('Error : Unposting gagal di lakukan, '.$errorDB);
	}
	#=========================== End Update ===============================
	break;
	case'view':
	
	$listkary=array();
	$basisk=$rplb=$rpbr=$rptop=$arrtop=$jlhtop=array();
	$listkary=$hk=$jjgpanen=$kgwb=$basiskg=$kglb1=$rplb1=$kglb2=$rplb2=$kgbrd=$rpbrd=$denda=$kehadiran=array();
	$thk=$tjjgpanen=$tkgwb=$tbasiskg=$tkglb1=$trplb1=$tkglb2=$trplb2=$tkgbrd=$trpbrd=$tdenda=$tkehadiran=$ttotal=array();


	# ambil data
	$str="select * from ".$dbname.".kebun_3premipemanen where notransaksi='".$notransaksi."' and periode='".$prd."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$blok=$bar['divisi'];
	}
	
	#ambil basis wb
	$str="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$divisi."' and tahun='".$prd."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$basiskg[$bar['tahuntanam']][$bar['topografi']]=$bar['basis'];
		$rplb1[$bar['tahuntanam']][$bar['topografi']]=$bar['premilebihbasis'];
		$rpbrd[$bar['tahuntanam']][$bar['topografi']]=$bar['premibrondolan'];
		$rptopo[$bar['tahuntanam']][$bar['topografi']]=$bar['premitopografi'];
		$arrtopo[$bar['tahuntanam']][$bar['topografi']]=$bar['topografi'];
		$jlhtopo[$bar['topografi']]=$bar['topografi'];
	}

	$stream='';
	$stream.="<img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='excel' onclick=\"previewexcel('".$notransaksi."','".$prd."','".$unit."','".$divisi."','excel');\" >";
	$stream.="<table><td colspan=2><b>".$_SESSION['lang']['notransaksi']." &nbsp;:</b></td>
					 <td colspan=2><b>".$notransaksi."</b></td>
			  </table>";
			  
	
	if ($tipe == 'excel') {
		$stream.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$stream.="<table class=sortable cellspacing=1 width=100%>";
	}
	


	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
		$stream.="<td align=center rowspan=2>No</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['mandor']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kerani']."</td>";
		$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahuntanam']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nik2']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
		$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['topografi']."</td>";
		$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['hk']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>";
		$stream.="<td align=center rowspan=2>Total Kg</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['basic']." Kg</td>";
		$stream.="<td align=center colspan=2>".$_SESSION['lang']['premi']."</td>";
		$stream.="<td align=center colspan=2>".$_SESSION['lang']['brondol']."</td>";
		$stream.="<td align=center rowspan=2>Kehadiran</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['denda']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>";
	$stream.="</tr>";
	$stream.="<tr>";
		$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
	$stream.="</tr>";
	$stream.="</thead><tbody>";
	# ambil data
	$str="select a.*, b.namakaryawan from ".$dbname.".kebun_3premipemanen a left join datakaryawan b 
	on a.karyawanid=b.karyawanid where a.notransaksi ='".$notransaksi."' order by a.mandor asc, a.tahuntanam asc, b.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$listkary[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]=$bar['status'];
		@$hk[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['hk'];
		@$jjgpanen[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['jjgpanen'];
		@$kgwb[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kgwb'];
		@$basiskg[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['basiskg'];
		@$kglb1[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kglb1'];
		@$rplb1[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rplb1'];
		@$kglb2[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kglb2'];
		@$rplb2[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rplb2'];
		@$kgbrd[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kgbrd'];
		@$rpbrd[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rpbrd'];
		@$denda[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['denda'];
		@$kehadiran[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kehadiran'];
	}
	$no='';
	$optTopografi =makeOption($dbname,'setup_topografi','topografi,keterangan');
	foreach($listkary as $mdr => $val){
		foreach($val as $krn => $key){
			foreach($key as $tt => $key1){
				foreach($key1 as $kary => $key2){
					foreach($key2 as $status){
						$no++;
						$stream.="<tr class=rowcontent>";
						$stream.="<td align=right>".$no."</td>";
						$stream.="<td align=left>".@getNamaKaryawan($mdr)."</td>";
						$stream.="<td align=left>".@getNamaKaryawan($krn)."</td>";
						$stream.="<td align=center>".$tt."</td>";
						$stream.="<td align=center>".@$nikkar[$kary]."</td>";
						$stream.="<td align=left>".@getNamaKaryawan($kary)."</td>";
						$stream.="<td align=left>".$optTopografi[$status]."</td>";
						$stream.="<td align=right>".@hidezerodecimal($hk[$mdr][$krn][$tt][$kary][$status],1)."</td>";
						$stream.="<td align=right>".@hidezerodecimal($jjgpanen[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($kgwb[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($basiskg[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($kglb1[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($rplb1[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($kgbrd[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($rpbrd[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($kehadiran[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($denda[$mdr][$krn][$tt][$kary][$status])."</td>";
						$total=(($rplb1[$mdr][$krn][$tt][$kary][$status]+$rplb2[$mdr][$krn][$tt][$kary][$status]+$rpbrd[$mdr][$krn][$tt][$kary][$status] +$kehadiran[$mdr][$krn][$tt][$kary][$status])-$denda[$mdr][$krn][$tt][$kary][$status]);
						$stream.="<td align=right>".@hidezerodecimal($total)."</td>";
						$stream.="</tr>";
						@$thk[$mdr]+=$hk[$mdr][$krn][$tt][$kary][$status];
						@$tjjgpanen[$mdr]+=$jjgpanen[$mdr][$krn][$tt][$kary][$status];
						@$tkgwb[$mdr]+=$kgwb[$mdr][$krn][$tt][$kary][$status];
						@$tbasiskg[$mdr]+=$basiskg[$mdr][$krn][$tt][$kary][$status];
						@$tkglb1[$mdr]+=$kglb1[$mdr][$krn][$tt][$kary][$status];
						@$trplb1[$mdr]+=$rplb1[$mdr][$krn][$tt][$kary][$status];
						@$tkglb2[$mdr]+=$kglb2[$mdr][$krn][$tt][$kary][$status];
						@$trplb2[$mdr]+=$rplb2[$mdr][$krn][$tt][$kary][$status];
						@$tkgbrd[$mdr]+=$kgbrd[$mdr][$krn][$tt][$kary][$status];
						@$trpbrd[$mdr]+=$rpbrd[$mdr][$krn][$tt][$kary][$status];
						@$tdenda[$mdr]+=$denda[$mdr][$krn][$tt][$kary][$status];
						@$tkehadiran[$mdr]+=$kehadiran[$mdr][$krn][$tt][$kary][$status];
						@$ttotal[$mdr]+=$total;
					}
				}
			}
		}		
			$stream.="<tr class=rowcontent>";
			$stream.="<td colspan=7 bgcolor='cyan'>Sub Total Kemandoran ".getNamaKaryawan($mdr)."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($thk[$mdr],1)."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tjjgpanen[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgwb[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tbasiskg[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkglb1[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trplb1[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgbrd[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trpbrd[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkehadiran[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tdenda[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($ttotal[$mdr])."</td>";
			$stream.="</tr>";
			@$gthk+=$thk[$mdr];
			@$gtjjgpanen+=$tjjgpanen[$mdr];
			@$gtkgwb+=$tkgwb[$mdr];
			@$gtbasiskg+=$tbasiskg[$mdr];
			@$gtkglb1+=$tkglb1[$mdr];
			@$gtrplb1+=$trplb1[$mdr];
			@$gtkglb2+=$tkglb2[$mdr];
			@$gtrplb2+=$trplb2[$mdr];
			@$gtkgbrd+=$tkgbrd[$mdr];
			@$gtrpbrd+=$trpbrd[$mdr];
			@$gtdenda+=$tdenda[$mdr];
			@$gtkehadiran+=$tkehadiran[$mdr];
			@$gttotal+=$ttotal[$mdr];
	}	
		$stream.="<tr class=rowcontent>";
		$stream.="<td bgcolor='cyan' colspan=7>Grand Total</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gthk)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtjjgpanen)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkgwb)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtbasiskg)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkglb1)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtrplb1)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkgbrd)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtrpbrd)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkehadiran)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtdenda)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gttotal)."</td>";
		$stream.="</tr>";
		$stream.="</tbody></table>";
		
		
		if($tipe!='excel'){
			echo $stream;
		}else{
			$nop_="daftar_premi";
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
				if(!fwrite($handle,$stream)){
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				fclose($handle);
			}       
		}
	break;
	case'loaddata':
        $where = "";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = "";
		} else if($_SESSION['empl']['subbagian']!=''){
			$where = " and divisi = '".$_SESSION['empl']['subbagian']."'";
		}else{
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
        if ($prdlist != '') {
            $where.=" and periode='" . $prdlist . "' ";
        }
        if ($unitlist != '') {
            $where.=" and kodeorg='" . $unitlist . "' ";
        }
		if ($afdlist != '') {
			$where.=" and divisi='" . $afdlist . "' ";
		}
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$tab = "";
        $no = $maxdisplay;
        $str = "SELECT kodeorg, notransaksi, divisi, periode, sum(hk) as hk, sum(jjgpanen) as jjgpanen, sum(kgwb) as kgwb, sum(basiskg) as basiskg, sum(kglb1) as kglb1, sum(rplb1) as rplb1, sum(kglb2) as kglb2, sum(rplb2) as rplb2, sum(kgbrd) as kgbrd, sum(rpbrd) as rpbrd, sum(denda) as denda,sum(kehadiran) as kehadiran, jurnal, posting, updateby FROM " . $dbname . ".kebun_3premipemanen
		where 1=1 ".$where." group by notransaksi order by periode desc, kodeorg asc, divisi asc limit " . $offset . "," . $limit . "";
		$resx=fetchdata($str);
		$jlhbrs=count($resx);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $no = 0;
        while ($bar = $res->fetch()) {
			$notofj=$color='';
			$totalpre=(($bar['rplb1']+$bar['rplb2']+$bar['rpbrd']+$bar['kehadiran'])-$bar['denda']);
			#cek jurnal
			$str="select sum(debet) as rpj from ".$dbname.".keu_jurnaldt_vw where nojurnal = '".$bar['jurnal']."' and noreferensi='".$bar['notransaksi']."'";
			$cekj=fetchData($str);
			$rpjurnal=$cekj[0]['rpj'];
			#vs jurnal
			$valjurnal=($totalpre - $rpjurnal);
			if(($valjurnal > 2 or $valjurnal < (-2)) and $bar['posting']==1){
				$notofj="Nilai di Jurnal tidak sama,<br>silahkan unposting kemudian posting ulang<br>Varian : ".number_format($valjurnal)."";
				$color=" style=background-color:red;";
			}else if($bar['posting']==1){
				$notofj="Posted";
			}else{
				$notofj="Not Posted";
			}
			
			#cek kebun prestasi
			$str="select sum(upahpremilebihbasis) as prelb from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.noreferensi='".$bar['notransaksi']."'"; 
			$cekpres=fetchData($str);
			$rppres=$cekpres[0]['prelb'];
			#vs pres
			$valpres=($totalpre - $rppres);
			if(($valpres > 2 or $valpres < (-2)) and $bar['posting']==1){
				$notofp="Nilai di Kegiatan Panen tidak sama,<br>silahkan unposting kemudian posting ulang<br>Varian : ".number_format($valpres)."";
				$color=" style=background-color:red;";
			}else if($bar['posting']==1){
				$notofp="Posted";
			}else{
				$notofp="Not Posted";
			}
			
            $isi = '';
            $no+=1;
            $tab.="<tr class=rowcontent ".$color." id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=center>".$bar['periode']."</td>";
            $tab.="<td>" . $bar['notransaksi'] . "</td>";            
            $tab.="<td>" . $bar['kodeorg'] . "</td>";            
            $tab.="<td>" . $bar['divisi'] . "</td>";            
            $tab.="<td align=right>".@number_format($bar['hk'])."</td>";
            $tab.="<td align=right>".@number_format($bar['jjgpanen']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['kgwb']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['basiskg']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['kglb1']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['rplb1']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['kgbrd']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['rpbrd']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['kehadiran']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['denda']) . "</td>";
            $tab.="<td align=right>".@number_format(($bar['rplb1']+$bar['rpbrd']+$bar['kehadiran'])-$bar['denda']) . "</td>";
            $tab.="<td>" . getNamaKaryawan($bar['updateby']) . "</td>";
            $tab.="<td>".$notofj."</td>";
            $tab.="<td>".$notofp."</td>";
			if ($bar['posting'] == 0) {
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."');\" ></td>";
				$post='';
				if(in_array($_SESSION['empl']['jabatan'],$jab,true)){
					$post=" onclick=\"posting('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$no."');\" ";
				}
			   $isi.="<td align=center><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30' ".$post." title='Posting'></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab,true)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$no."');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$isi.="<td></td>";
                $isi.="<td align=center><img src=".$icon." class=resicon class=zImgBtn height='30' title='".$title."' ".$unpost." ></td>";
            }
            $isi.="<td align=right><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View' 
                    onclick=\"view('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['divisi']."','html');\" >
					
					<img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View' 
                    onclick=\"previewexcel('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['divisi']."','excel');\" >
					
					</td>";
            $tab.=$isi;
            $tab.="</tr>";
        }
        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr>
                     <tr><td colspan=22 align=center>";
        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }
        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td>
            </tr>";
	echo $tab . "####" . $footd;
	break;
    ######EXCEL	
	case 'excel':
		$tglSkrg=date("Ymd");
		$nop_="daftar_premi_mandor";
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
			if(!fwrite($handle,$stream)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
		break;
    default:
}

?>