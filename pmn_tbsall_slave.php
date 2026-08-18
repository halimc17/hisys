<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/utilities.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;
// error_reporting(0);

$param = $_POST;
if(count($param) == 0){
	$param = $_GET;
}

$method = checkPostGet('method','');
$tanggalmulaisch=checkPostGet('tanggalmulaisch','');
$tanggalselesaisch=checkPostGet('tanggalselesaisch','');
$notransaksisch=checkPostGet('notransaksisch','');
$notransaksi = checkPostGet('notransaksi','');
$tanggal = tanggalsystemn(checkPostGet('tanggal',''));	
$tipeapp = checkPostGet('tipeapp','');
$maxaproval = checkPostGet('maxaproval','');
$tanggalpengajuan = checkPostGet('tanggalpengajuan','');

$unitsch=checkPostGet('unitsch','');
$suppliersch=checkPostGet('suppliersch','');
$postingsch=checkPostGet('postingsch','');
$jenissch=checkPostGet('jenissch','');
$tp=checkPostGet('tp','');

if($tanggalmulaisch==''){
	$tanggalmulaisch='';
}else{
	$tanggalmulaisch = tanggalsystemn(checkPostGet('tanggalmulaisch',''));	
}

if($tanggalselesaisch==''){
	$tanggalselesaisch='';
}else{
	$tanggalselesaisch=tanggalsystemn(checkPostGet('tanggalselesaisch',''));
}
$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

#= ambil daftar unit didalam pt bentukan array
$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 or length(kodeorganisasi)=3 ";
$res=fetchdata($str);
foreach($res as $bar){
	$kodept[$bar['kodeorganisasi']]=$bar['induk'];
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}


#= ambil daftar kantor RO didalam pt bentukan array
$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='HOLDING' ";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$kodeho[$bar['induk']]=$bar['kodeorganisasi'];
}


// print_r($param);

switch ($method) {
	case 'getVendor':
		$whrjns="";
		// if($param['jenisx']=='PLS' || $param['jenisx']=='KUD'){
		// 	$whrjns=" and tipe='TBSPLS' or tipe='TBSKUD'";
		// }
		// if($param['jenisx']=='EXT'){
		// 	$whrjns=" and tipe='TBSEXT'";			
		// }
		// if($param['jenisx']=='AFI'){
		// 	$whrjns=" and tipe='TBSAFI'";			
		// }
		// if($param['jenisx']=='internal'  || $param['jenisx']=='INT'){
		// 	$whrjns=" and tipe='TBSINT'";			
		// }
		$str="select distinct(kodesupplier) as kodesupplier from ".$dbname.".pmn_kontrakbeli where unit='".$param['unit']."' and kodesupplier in (select supplierid from ".$dbname.".log_5supkelompok where 1=1 ".$whrjns.")";
		// exit("Error".$str);
		$res=fetchdata($str);
		$optsupp="";
		$optsupp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";    
		foreach ($res as $key => $val) {
			$optsupp.="<option value='".$val['kodesupplier']."'>[".$val['kodesupplier']."] ".$nmsupplier[$val['kodesupplier']]."</option>";
		}
		echo $optsupp;
	break;
	
	case 'getNokontrak':
		$str="select * from ".$dbname.".pmn_kontrakbeli where unit='".$param['unit']."' and kodesupplier = '".$param['divisi']."'";
		$res=fetchdata($str);
		$optkontrak="";
		$optkontrak="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";       
		foreach ($res as $key => $val) {
			$optkontrak.="<option value='".$val['notransaksi']."'>".$val['notransaksi']."</option>";
		}
		echo $optkontrak;

	break;
	
	case'geteditht':
		$str = "select * from ".$dbname.".pmn_tbs where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		$res[0]['tanggal']=tanggalnormal($res[0]['tanggal']);
		$res[0]['tanggaltbs1']=tanggalnormal($res[0]['tanggaltbs1']);
		$res[0]['tanggaltbs2']=tanggalnormal($res[0]['tanggaltbs2']);
			
		echo json_encode($res[0]);	
	break;
	
	case'saveht':
		$tglterakhir = date('Y-m-t', strtotime($param['tanggal']));
		$periode=substr($param['tanggal'],0,7);
		$tglper11=$periode.'-01';
		$tglper12=$periode.'-15';
		
		$tglper21=$periode.'-16';
		$tglper22=$tglterakhir;
		
		$str = "select count(*) as jumlah from ".$dbname.".pmn_5hargabelitbs where unit='".$param['unit']."' and tanggaldari <='".tanggalsystemn($param['tanggaltbs1'])."' and tanggalsampai>='".tanggalsystemn($param['tanggaltbs2'])."'";	
		$res=fetchdata($str);
		$jumlah=$res[0]['jumlah'];
			
		if($jumlah<1){
			// exit("Warning:Harga TBS untuk periode ini belum di-input");
		}		
		
		
		
		$arrtanggal=rangeTanggalarr(tanggalsystemn($param['tanggaltbs1']),tanggalsystemn($param['tanggaltbs2']));
		// echo"<pre>";
		// print_r($arrtanggal);
		// exit("Error:A");
		$texterror='';
		foreach($arrtanggal as $tglcek){ //'supplier'=>$param['divisi'],
			$str="select count(*) as jumlah,notransaksi from ".$dbname.".pmn_tbs where supplier='".$param['divisi']."' and unit='".$param['unit']."' and tanggalpks='".$tglcek."'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if($bar['jumlah']>0){
				$texterror.="sudah ada data supplier ".$nmsupplier[$param['divisi']]." untuk unit ".$param['unit']." ditanggal tbs ".tanggalnormal($tglcek)." dengan nomor transaksi ".$bar['notransaksi']."<br><br>";
			}
		}
		
		if($texterror!=''){
			echo $texterror;
			exit("Warning:Gagal Proses");
		}	
		
		
		
		
		

		$tipetbs=$param['jenisx'];
		$notransaksi = generatenotransaksitbs();

		// this is
		if($notransaksi == ''){
			echo $notransaksi;
			exit("Warning : ".$notransaksi." No Transaksi belum tergenarate ");
		}
		
		echo $notransaksi."###".'';
	break;
	
	case'deleteht':
		try{
			$owlPDO->beginTransaction();
			
			$str = "delete from ".$dbname.".pmn_tbs where notransaksi='".$param['notransaksi']."' ";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
	
	case'loaddatadt':
     	$no         =0;
	    $beratj     =0;
	    $beratl     =0;
	    $jumlaht    =0;
	    $potonganu  =0;
		$adjustx    =0;
	    $totadjy    =0;
	    $brtadjz    =0;
        $rpadjaa    =0;
        $jrpadjab   =0;
        $bjr        =0;

  		$noerr=0;
		
		$tanggaltbs1=tanggalsystemn($param['tanggaltbs1']);
		$tanggaltbs2=tanggalsystemn($param['tanggaltbs2']);
		// echo"<pre>";
		// print_r($param);
		// exit("Error:");
		
		if($param['jenisx']=='AFI'){
			#= ambil kodesupplier
			#= ambil kodeorg, karna dipabrik_timbangan memakai kodeorg tidak kodesupplier
			#= param kiriman kodesupplier jadi query balik ke log_5supplier ambil kodept
			$str="select kodept from ".$dbname.".log_5supplier where supplierid='".$param['divisi']."' ";
			$res=fetchdata($str);
			foreach($res as $bar){
				$dtkodept=$bar['kodept'];
			}
		}
        
		# cek timbangan
		$sql = selectQuery($dbname,"pmn_kontrakbeli","*","notransaksi='".$param['nokontrak']."'");
		$res = fetchData($sql);
		foreach($res as $row):
			# 1 => Timbangan Pembeli
			# 2 => Timbangan Penjual
			$flagtimbangan[$row['notransaksi']] = $row['timbangan'];
		endforeach;

		$whkontrak='';
		if($param['nokontrak'] != '' && $flagtimbangan[$param['nokontrak']] =='2') {
			$whkontrak=" and nokontrak='".$param['nokontrak']."' and kgpenjual > 0";
		}
		
		$str = "select * from ".$dbname.".pabrik_timbangan_vw where millcode='".$param['unit']."' and kodebarang='40000003' and tanggal between '".$tanggaltbs1."' and '".$tanggaltbs2."'  
		and (kodecustomer='".$param['divisi']."' or kodesupplier='".$param['divisi']."' or kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$dtkodept."')) ".$whkontrak." order by tanggal asc";

		if($param['jenisx']=='AFI') {
			$whnew = "AND substr(blok,1,4) IN (select kodeorganisasi from ".$dbname.".organisasi where induk='".$dtkodept."')";

			$str = "SELECT * FROM {$dbname}.kebun_spb_vw4 WHERE tanggal BETWEEN '{$tanggaltbs1}' AND '{$tanggaltbs2}' {$whnew} ORDER BY tanggal ASC";
		}
            // exit("warning :".$str);
        // echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){

			#== harga rp/kg
            
			// if($param['jenisx']=='AFI') {
				$query = "SELECT * FROM ".$dbname.".pmn_kontrakbeli WHERE notransaksi='".$param['nokontrak']."' AND unit='".$param['unit']."' AND kodesupplier='".$param['divisi']."'";
				$whnewthntnm = "and tahuntanam='".getBlok($bar['blok'],'tahuntanam')."'";
			// } else {
			// 	$query = "SELECT * FROM ".$dbname.".pmn_kontrakbeli WHERE notransaksi='".$param['nokontrak']."' AND unit='".$bar['millcode']."' AND kodesupplier='".$param['divisi']."'";
			// }
            // echo $query."<br/>";
			
			$result = fetchData($query);
			if($param['jenisx']=='AFI') {
				if ($result[0]['flagall'] == 0) {
					if($bar['jjg']>0){
						$bjrxx=$bar['kgwbnetto']/$bar['jjg'];
					}else{
						$bjrxx=0;
					}
					$whklb = "a.kodeklsbuah!='S' and b.minbjr<='".$bjrxx."' and b.maxbjr>'".$bjrxx."' ";
					$str1="select a.* from ".$dbname.".pmn_5hargabelitbs a 
					left join ".$dbname.".pmn_5kelasbuahdt b on a.kodeklsbuah=b.kode 
					where 
						a.unit='".$param['unit']."' and a.kodesupplier='".$param['divisi']."' 
						and  a.tanggaldari<='".$bar['tanggal']."' and a.tanggalsampai>='".$bar['tanggal']."'
						and tahuntanam='".getBlok($bar['blok'],'tahuntanam')."'
						and ".$whklb." order by a.tanggaldari desc limit 1";
						
					$res1=fetchdata($str1);
					@$hargaperkg=$res1[0]['harga'];
	
					$kelasbuah = $res1[0]['namakelas'];
				} else {
					if($bar['jjg']>0){
						$bjrxx=$bar['kgwbnetto']/$bar['jjg'];
					}else{
						$bjrxx=0;
					}
					$whklb = "kodeklsbuah='S'";
					$str1="select * from ".$dbname.".pmn_5hargabelitbs where 
						unit='".($bar['millcode'] == '' ? $param['unit'] : $param['unit'])."' and kodesupplier='".$param['divisi']."' 
						and  tanggaldari<='".$bar['tanggal']."' and tanggalsampai>='".$bar['tanggal']."'
						and tahuntanam='".getBlok($bar['blok'],'tahuntanam')."'
						and ".$whklb." order by tanggaldari desc limit 1";
						
					$res1=fetchdata($str1);
					@$hargaperkg=$res1[0]['harga'];
	
					$kelasbuah = 'Seluruhnya';
				}
			} else {
				if ($result[0]['flagall'] == 0) {
					if($flagtimbangan[$bar['nokontrak']] == '2' && $param['jenisx'] == 'EXT') {
						$bjrxx=0;
					} else {
						$bjrxx=@fixnan($bar['beratbersih']/$bar['jjg']);
					}
					if($bjrxx == 0 && $bar['jjg'] == 0){
						$whklb = "a.kodeklsbuah!='S'";
					}else{
						$whklb = "a.kodeklsbuah!='S' and b.minbjr<='".$bjrxx."' and b.maxbjr>'".$bjrxx."' ";
					}
					$str1="select a.* from ".$dbname.".pmn_5hargabelitbs a 
					left join ".$dbname.".pmn_5kelasbuahdt b on a.kodeklsbuah=b.kode 
					where 
						a.unit='".$bar['millcode']."' and a.kodesupplier='".$param['divisi']."' 
						and  a.tanggaldari<='".$bar['tanggal']."' and a.tanggalsampai>='".$bar['tanggal']."'
						".$whnewthntnm."
						and ".$whklb." order by a.tanggaldari desc limit 1";
						
					$res1=fetchdata($str1);
					@$hargaperkg=$res1[0]['harga'];
				} else {
					if($flagtimbangan[$bar['nokontrak']] == '2' && $param['jenisx'] == 'EXT') {
						$bjrxx=0;
					} else {
						$bjrxx=$bar['beratbersih']/$bar['jjg'];
					}

					// $whklb = "kodeklsbuah='S'";
					$str1="select * from ".$dbname.".pmn_5hargabelitbs where 
						unit='".$bar['millcode']."' and kodesupplier='".$param['divisi']."' 
						and  tanggaldari<='".$bar['tanggal']."' and tanggalsampai>='".$bar['tanggal']."'
						 order by tanggaldari desc limit 1";
						
					$res1=fetchdata($str1);
					@$hargaperkg=$res1[0]['harga'];
				}
			}
			// echo $query."<br/>";
			// echo $result[0]['flagall']."<br/>";
			// echo $str1."<br/>";
			
			
			// echo $str1.";<br/>";
			// $pesanerr='';
			// if($hargaperkg==0){
			// 	$noerr++;
			// 	$pesanerr=" Harga untuk kelas buah ".$kelasbuah." (".$nmkelasbuah[$kelasbuah]."), belum ada, hubungi pihak commercial";
			// }
            $no++;
			if($flagtimbangan[$bar['nokontrak']] == '2' && $param['jenisx'] == 'EXT') {
				$tab.="<tr class=rowcontent id=row".$no.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td id=notiket".$no.">".$bar['notiket']."</td>";
					$tab.="<td id=kelasbuah".$no.">".$kelasbuah."</td>";
					$tab.="<td id=kodevhc".$no.">".$bar['nokendaraan']."</td>";
					$tab.="<td id=driver".$no.">".$bar['supir']."</td>";
					$tab.="<td style='min-width:180px;'>".$param['divisi']." - ".getNamaSupplier($param['divisi'])."</td>";
					$tab.="<td id=tanggalpks".$no." align=center style='min-width:80px;'>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td id=tanggalspb".$no." align=center style='min-width:80px;'>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td id=kgmasuk".$no." align=right>0</td>";
					$tab.="<td id=kgkeluar".$no." align=right>0</td>";
					#rumus J=I-H
					
					$tab.="<td id=kgbruto".$no." align=right>".$bar['kgpenjual']."</td>";
					$tab.="<td id=kgpotongan".$no." align=right>0</td>";
					
					#rumus L=J-K
					$beratl = $bar['kgpenjual'];

					$tab.="<td id=kgnetto".$no." align=right>".$beratl."</td>";
					$tab.="<td id=jjg".$no." align=right>0</td>";
					$tab.="<td id=jjgsortasi".$no." align=right>0</td>";
					$tab.="<td id=bjr".$no." align=right>0</td>";
					$tab.="<td id=nospb".$no.">EXT</td>";
					$tab.="<td id=kodeblok".$no.">EXT</td>";
					$tab.="<td style='min-width:180px'></td>";
					$tab.="<td id=tahuntanam".$no." align=center>EXT</td>";
					//if($param['jenisx']!='EXT'){
					$tab.="<td  id=rpkg".$no." align=right>".hidezerodecimal($hargaperkg,2)."</td>"; ### ROWCONTENT HARGA
					#rumus U=L*T
					$jumlahu = floor($hargaperkg * $beratl);
					$tab.="<td   id=totalrp".$no." align=right>".hidezerodecimal($jumlahu)."</td>";
					//}
					#rumus V= K/J
					$potonganv = $bar['kgpotsortasi'] / $bar['beratbersih']*100;
					$potonganv=hidezerodecimal($potonganv,2);
					
					$tab.="<td hidden id=potpersenaktual".$no." align=right>".$potonganv."</td>";
					
					#= fix grading
					$str1 = "select * from ".$dbname.".pmn_5fixgrading  
								where notransaksi='".$nokontrak."'
								and kodesupplier='".$divisi."'
								and unit='".$bar['millcode']."'
								and tanggaldari<='".$bar['tanggal']."'
								and tanggalsampai>='".$bar['tanggal']."'
								order by tanggaldari desc limit 1";
					$res1=fetchdata($str1);
					$adjustx = 0;

					if(count($res1) > 0){
						$adjustx = $res1[0]['fixgrading'];
					}
					
					
					// $tab.="<td id=potpersensetup".$no." align=right>".hidezerodecimal($fixgrading,2)."</td>";
					$tab.="<td hidden id=potpersensetup".$no." align=right>".hidezerodecimal($adjustx,2)."</td>";
					#Round(If(V>W,V-W,0))
					
					// $adjustx=0;
					// if($potonganv > $fixgrading and $res1[0]['fixgrading']!=''){
					// 	$adjustx = round($potonganv - $fixgrading,2);
					// }else{
					// 	$adjustx=$fixgrading;
					// }

					$fixgrading=0;
					if (count($res1) > 0) {
						$fixgrading = (($bar['kgpotsortasi'] / $bar['beratbersih']) - ($res1[0]['batasatas']/100)) * 100;

						if ($fixgrading < 0) {
							$fixgrading = 0;
						}

						// if($grading>$res1[0]['batasatas']){
						// 	$fixgrading = $grading - ($res1[0]['batasatas']/100)) * 100;
						// } else if($grading<$res1[0]['batasbawah']) {
						// 	$fixgrading=0;
						// }
					
						// if($res1[0]['fixgrading']=='0'){
						// 	$fixgrading=$potonganv;
						// } else {
						// 	$fixgrading=$res1[0]['fixgrading'];
						// }
					}
					
				
					// $tab.="<td  id=potpersen".$no." align=right>".hidezerodecimal($fixgrading,2)."</td>";
					$tab.="<td hidden id=potpersen".$no." align=right>".$fixgrading."</td>";
					#Round(X*L)
					// $adjreal = hidezerodecimal($fixgrading,2);
					$adjreal = $fixgrading;
					$totadjy = ($bar['beratbersih'] * $adjreal / 100);
					$tab.="<td hidden id=kgadjust".$no." align=right>".$totadjy."</td>";
					#Z=J-Y
					$brtadjz = $bar['beratbersih'] - $totadjy;
					$tab.="<td hidden id=kgnettoadjust".$no." align=right>".$brtadjz."</td>";
					#AA=T*Y
					$rpadjaa =$hargaperkg * $totadjy;
					$tab.="<td hidden id=rpadjust".$no." align=right>".hidezerodecimal($rpadjaa,10)."</td>";
					#AB=U+AA
					$jrpadjab = $jumlahu/* + $rpadjaa;*/;
					$tab.="<td hidden id=jumrpadjust".$no." align=right>".hidezerodecimal($jrpadjab,10)."</td>";
					$tab.="<td align=left>".$pesanerr."</td>";
				$tab.="</tr>";

				#rumus total dibawah 
				#= bentuk total
				@$totalh    +=0;
				@$totali    +=0;
				@$totalj    +=$bar['kgpenjual'];
				@$totalk    +=0;
				@$totall    +=$beratl;
				@$totalu+=$jumlahu;
	
				// @$totalt    +=$bar1['harga'];
	
				@$totalz    +=$brtadjz;
				@$totalaa   +=$rpadjaa;
				@$totalab   +=$jrpadjab;#=AC
			} else if($param['jenisx'] == 'AFI') {

				if($param['jenisx']=='AFI') {
					$bar['notiket'] = $bar['notiket'];
					$bar['beratbersih'] = $bar['kgwbnetto'];
					$bar['kgpotsortasi'] = ($bar['kgwb']-$bar['kgwbnetto']); # Konfirmasi ke Sendi
				}

				$tab.="<tr class=rowcontent id=row".$no.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td id=notiket".$no.">".$bar['notiket']."</td>";
					$tab.="<td id=kelasbuah".$no.">".$kelasbuah."</td>";
					$tab.="<td id=kodevhc".$no.">".$bar['nokendaraan']."</td>";
					$tab.="<td id=driver".$no.">".$bar['supir']."</td>";
					$tab.="<td style='min-width:180px;'>".$param['divisi']." - ".getNamaSupplier($param['divisi'])."</td>";
					$tab.="<td id=tanggalpks".$no." align=center style='min-width:80px;'>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td id=tanggalspb".$no." align=center style='min-width:80px;'>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td id=kgmasuk".$no." align=right>".$bar['beratmasuk']."</td>";
					$tab.="<td id=kgkeluar".$no." align=right>".$bar['beratkeluar']."</td>";
					#rumus J=I-H
					
					$tab.="<td id=kgbruto".$no." align=right>".($bar['beratbersih'] + $bar['kgpotsortasi'])."</td>";
					$tab.="<td id=kgpotongan".$no." align=right>".$bar['kgpotsortasi']."</td>";
					#rumus L=J-K
					$beratl = $bar['beratbersih'];
					$tab.="<td id=kgnetto".$no." align=right>".$beratl."</td>";
					$tab.="<td id=jjg".$no." align=right>".$bar['jjg']."</td>";
					$tab.="<td id=jjgsortasi".$no." align=right>".$bar['jjgsortasi']."</td>";
					$tab.="<td id=bjr".$no." align=right>".$bjrxx."</td>";
					$tab.="<td id=nospb".$no.">".$bar['nospb']."</td>";
					$tab.="<td id=kodeblok".$no.">".$bar['blok']."</td>";
					$tab.="<td style='min-width:180px'>".getNamaOrg($bar['blok'])."</td>";
					$tab.="<td id=tahuntanam".$no." align=center>".($bar['thntm1'] == '' ? getBlok($bar['blok'],'tahuntanam') : $bar['thntnm1'])."</td>";
					//if($param['jenisx']!='EXT'){
					$tab.="<td  id=rpkg".$no." align=right>".hidezerodecimal($hargaperkg,2)."</td>"; ### ROWCONTENT HARGA
					#rumus U=L*T
					$jumlahu = floor($hargaperkg * $beratl);
					$tab.="<td   id=totalrp".$no." align=right>".hidezerodecimal($jumlahu)."</td>";
					//}
					#rumus V= K/J
					$potonganv = $bar['kgpotsortasi'] / $bar['beratbersih']*100;
					$potonganv=hidezerodecimal($potonganv,2);
					
					$tab.="<td hidden id=potpersenaktual".$no." align=right>".$potonganv."</td>";
					
					#= fix grading
					$str1 = "select * from ".$dbname.".pmn_5fixgrading  
								where notransaksi='".$nokontrak."'
								and kodesupplier='".$divisi."'
								and unit='".$bar['millcode']."'
								and tanggaldari<='".$bar['tanggal']."'
								and tanggalsampai>='".$bar['tanggal']."'
								order by tanggaldari desc limit 1";
					$res1=fetchdata($str1);
					$adjustx = 0;

					if(count($res1) > 0){
						$adjustx = $res1[0]['fixgrading'];
					}
					
					
					// $tab.="<td id=potpersensetup".$no." align=right>".hidezerodecimal($fixgrading,2)."</td>";
					$tab.="<td hidden id=potpersensetup".$no." align=right>".hidezerodecimal($adjustx,2)."</td>";
					#Round(If(V>W,V-W,0))
					
					// $adjustx=0;
					// if($potonganv > $fixgrading and $res1[0]['fixgrading']!=''){
					// 	$adjustx = round($potonganv - $fixgrading,2);
					// }else{
					// 	$adjustx=$fixgrading;
					// }

					$fixgrading=0;
					if (count($res1) > 0) {
						$fixgrading = (($bar['kgpotsortasi'] / $bar['beratbersih']) - ($res1[0]['batasatas']/100)) * 100;

						if ($fixgrading < 0) {
							$fixgrading = 0;
						}

						// if($grading>$res1[0]['batasatas']){
						// 	$fixgrading = $grading - ($res1[0]['batasatas']/100)) * 100;
						// } else if($grading<$res1[0]['batasbawah']) {
						// 	$fixgrading=0;
						// }
					
						// if($res1[0]['fixgrading']=='0'){
						// 	$fixgrading=$potonganv;
						// } else {
						// 	$fixgrading=$res1[0]['fixgrading'];
						// }
					}
					
				
					// $tab.="<td  id=potpersen".$no." align=right>".hidezerodecimal($fixgrading,2)."</td>";
					$tab.="<td hidden id=potpersen".$no." align=right>".$fixgrading."</td>";
					#Round(X*L)
					// $adjreal = hidezerodecimal($fixgrading,2);
					$adjreal = $fixgrading;
					$totadjy = ($bar['beratbersih'] * $adjreal / 100);
					$tab.="<td hidden id=kgadjust".$no." align=right>".$totadjy."</td>";
					#Z=J-Y
					$brtadjz = $bar['beratbersih'] - $totadjy;
					$tab.="<td hidden id=kgnettoadjust".$no." align=right>".$brtadjz."</td>";
					#AA=T*Y
					$rpadjaa =$hargaperkg * $totadjy;
					$tab.="<td hidden id=rpadjust".$no." align=right>".hidezerodecimal($rpadjaa,10)."</td>";
					#AB=U+AA
					$jrpadjab = $jumlahu/* + $rpadjaa;*/;
					$tab.="<td hidden id=jumrpadjust".$no." align=right>".hidezerodecimal($jrpadjab,10)."</td>";
					$tab.="<td align=left>".$pesanerr."</td>";
				$tab.="</tr>";

				#rumus total dibawah 
				#= bentuk total
				@$totalh    +=$bar['beratmasuk'];
				@$totali    +=$bar['beratkeluar'];
				@$totalj    +=$bar['beratbersih'];
				@$totalk    +=$bar['kgpotsortasi'];
				@$totall    +=$beratl;
				@$totalu+=$jumlahu;
	
				// @$totalt    +=$bar1['harga'];
	
				@$totalz    +=$brtadjz;
				@$totalaa   +=$rpadjaa;
				@$totalab   +=$jrpadjab;#=AC
			} else {
				$tab.="<tr class=rowcontent id=row".$no.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td id=notiket".$no.">".$bar['notiket']."</td>";
					$tab.="<td id=kelasbuah".$no.">".$kelasbuah."</td>";
					$tab.="<td id=kodevhc".$no.">".$bar['nokendaraan']."</td>";
					$tab.="<td id=driver".$no.">".$bar['supir']."</td>";
					$tab.="<td style='min-width:180px;'>".$param['divisi']." - ".getNamaSupplier($param['divisi'])."</td>";
					$tab.="<td id=tanggalpks".$no." align=center style='min-width:80px;'>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td id=tanggalspb".$no." align=center style='min-width:80px;'>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td id=kgmasuk".$no." align=right>".$bar['beratmasuk']."</td>";
					$tab.="<td id=kgkeluar".$no." align=right>".$bar['beratkeluar']."</td>";
					#rumus J=I-H
					
					$tab.="<td id=kgbruto".$no." align=right>".($bar['beratbersih'] + $bar['kgpotsortasi'])."</td>";
					$tab.="<td id=kgpotongan".$no." align=right>".$bar['kgpotsortasi']."</td>";
					#rumus L=J-K
					$beratl = $bar['beratbersih'];
					$tab.="<td id=kgnetto".$no." align=right>".$beratl."</td>";
					$tab.="<td id=jjg".$no." align=right>".$bar['jjg']."</td>";
					$tab.="<td id=jjgsortasi".$no." align=right>".$bar['jjgsortasi']."</td>";
					$tab.="<td id=bjr".$no." align=right>".$bjrxx."</td>";
					$tab.="<td id=nospb".$no.">".$bar['nospb']."</td>";
					$tab.="<td id=kodeblok".$no.">".($bar['blok'] == '' ? getNamaOrg($bar['blok']) : $bar['blok'])."</td>";
					$tab.="<td style='min-width:180px'></td>";
					$tab.="<td id=tahuntanam".$no." align=center>".($bar['thntm1'] == '' ? getBlok($bar['blok'],'tahuntanam') : $bar['thntnm1'])."</td>";
					//if($param['jenisx']!='EXT'){
					$tab.="<td  id=rpkg".$no." align=right>".hidezerodecimal($hargaperkg,2)."</td>"; ### ROWCONTENT HARGA
					#rumus U=L*T
					$jumlahu = floor($hargaperkg * $beratl);
					$tab.="<td   id=totalrp".$no." align=right>".hidezerodecimal($jumlahu)."</td>";
					//}
					#rumus V= K/J
					$potonganv = $bar['kgpotsortasi'] / $bar['beratbersih']*100;
					$potonganv=hidezerodecimal($potonganv,2);
					
					$tab.="<td hidden id=potpersenaktual".$no." align=right>".$potonganv."</td>";
					
					#= fix grading
					$str1 = "select * from ".$dbname.".pmn_5fixgrading  
								where notransaksi='".$nokontrak."'
								and kodesupplier='".$divisi."'
								and unit='".$bar['millcode']."'
								and tanggaldari<='".$bar['tanggal']."'
								and tanggalsampai>='".$bar['tanggal']."'
								order by tanggaldari desc limit 1";
					$res1=fetchdata($str1);
					$adjustx = 0;

					if(count($res1) > 0){
						$adjustx = $res1[0]['fixgrading'];
					}
					
					
					// $tab.="<td id=potpersensetup".$no." align=right>".hidezerodecimal($fixgrading,2)."</td>";
					$tab.="<td hidden id=potpersensetup".$no." align=right>".hidezerodecimal($adjustx,2)."</td>";
					#Round(If(V>W,V-W,0))
					
					// $adjustx=0;
					// if($potonganv > $fixgrading and $res1[0]['fixgrading']!=''){
					// 	$adjustx = round($potonganv - $fixgrading,2);
					// }else{
					// 	$adjustx=$fixgrading;
					// }

					$fixgrading=0;
					if (count($res1) > 0) {
						$fixgrading = (($bar['kgpotsortasi'] / $bar['beratbersih']) - ($res1[0]['batasatas']/100)) * 100;

						if ($fixgrading < 0) {
							$fixgrading = 0;
						}

						// if($grading>$res1[0]['batasatas']){
						// 	$fixgrading = $grading - ($res1[0]['batasatas']/100)) * 100;
						// } else if($grading<$res1[0]['batasbawah']) {
						// 	$fixgrading=0;
						// }
					
						// if($res1[0]['fixgrading']=='0'){
						// 	$fixgrading=$potonganv;
						// } else {
						// 	$fixgrading=$res1[0]['fixgrading'];
						// }
					}
					
				
					// $tab.="<td  id=potpersen".$no." align=right>".hidezerodecimal($fixgrading,2)."</td>";
					$tab.="<td hidden id=potpersen".$no." align=right>".$fixgrading."</td>";
					#Round(X*L)
					// $adjreal = hidezerodecimal($fixgrading,2);
					$adjreal = $fixgrading;
					$totadjy = ($bar['beratbersih'] * $adjreal / 100);
					$tab.="<td hidden id=kgadjust".$no." align=right>".$totadjy."</td>";
					#Z=J-Y
					$brtadjz = $bar['beratbersih'] - $totadjy;
					$tab.="<td hidden id=kgnettoadjust".$no." align=right>".$brtadjz."</td>";
					#AA=T*Y
					$rpadjaa =$hargaperkg * $totadjy;
					$tab.="<td hidden id=rpadjust".$no." align=right>".hidezerodecimal($rpadjaa,10)."</td>";
					#AB=U+AA
					$jrpadjab = $jumlahu/* + $rpadjaa;*/;
					$tab.="<td hidden id=jumrpadjust".$no." align=right>".hidezerodecimal($jrpadjab,10)."</td>";
					$tab.="<td align=left>".$pesanerr."</td>";
				$tab.="</tr>";

				#rumus total dibawah 
				#= bentuk total
				@$totalh    +=$bar['beratmasuk'];
				@$totali    +=$bar['beratkeluar'];
				@$totalj    +=$bar['beratbersih'];
				@$totalk    +=$bar['kgpotsortasi'];
				@$totall    +=$beratl;
				@$totalu+=$jumlahu;
	
				// @$totalt    +=$bar1['harga'];
	
				@$totalz    +=$brtadjz;
				@$totalaa   +=$rpadjaa;
				@$totalab   +=$jrpadjab;#=AC
			}
        }
		### TUTUP WHILE
	
		
		$tab.="<tr class=rowheader bgcolor=#B0C4DE>";
		$tab.="<td align=center colspan=8>".$_SESSION['lang']['total']."</td>";
		$tab.="<td align=right>".@hidezerodecimal($totalh,2)."</td>";
		$tab.="<td align=right>".@hidezerodecimal($totali,2)."</td>";
		$tab.="<td align=right>".@hidezerodecimal($totalj,2)."</td>";
		$tab.="<td align=right>".@hidezerodecimal($totalk,2)."</td>";
		$tab.="<td align=right>".@hidezerodecimal($totall,2)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td ></td>";
		$tab.="<td  id=ttotalrp align=right>".@hidezerodecimal($totalu,2)."</td>";
		$tab.="<td hidden></td>";
		$tab.="<td hidden></td>";
		$tab.="<td hidden></td>";
		$tab.="<td hidden></td>";
		$tab.="<td hidden align=right>".@hidezerodecimal($totalz,2)."</td>";
		$tab.="<td hidden align=right>".@hidezerodecimal($totalaa,2)."</td>";
		$tab.="<td hidden align=right>".@hidezerodecimal($totalab,2)."</td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		
	
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=30>";
		if($noerr==0){
			$tab.="<button  id=save class=mybutton onclick=savedt(".$no.")>".$_SESSION['lang']['save']."</button>";
		}else{
			$tab.="Masih ada data yang belum tepat, tombol simpan tidak akan muncul &nbsp;&nbsp;";
		}
		 $tab.="<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
		<button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button>
		</td>";	
		// <button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button></td>";	
		
		echo $tab;
	break;
	
	case 'savedt':
		try{
			$owlPDO->beginTransaction();
			
			## cek noakun ambil dari log_5supkelompok
			$str="select * from ".$dbname.".log_5supkelompok where supplierid='".$param['divisi']."' and (tipe like 'TBS%' or tipe like 'SUPP%') and status=1 ";
			$res=fetchdata($str);
			foreach($res as $bar){
				@$noakun=$bar['noakun'];
				@$tipesupplier=$bar['tipe'];
				@$noakungrir=$bar['noakungrir'];
			}
			
			## cek apakah flag gr/ir aktif
			$noakunhutang = $noakun;
			
			if($noakunhutang==''){
				throw new PDOException("Akun Hutang belum disetting, silahkan setting di menu Pengadaan->Setup->Master Supplier");
			}
				
			$pemilik=$rounit=$ropemilik='';
			
			if($param['notiket']==''){
				throw new PDOException("Nomor tiket timbang pabrik tidak terdaftar");
			}
			
			if($currRow=='1'){
				## delete 1st
				$str = "delete from ".$dbname.".pmn_tbs where  notransaksi='".$param['notransaksi']."' ";
				$owlPDO->exec($str);	
			}
			
			#= buat kode unit
			$str="select kodept from ".$dbname.".log_5supplier where supplierid='".$param['divisi']."' ";
			$res=fetchdata($str);
			foreach($res as $bar){
				$dtkodept=$bar['kodept'];
			}
			
			/*
			$tarifppn = makeOption($dbname,"log_5pphsup","supplierid,tarif","left(noakun,3)='117'");
			$tarifpph = makeOption($dbname,"log_5pphsup","supplierid,tarif","left(noakun,3)='213'");

			if($param['persenppn'] == '' || $param['persenppn'] == 0){
				$param['persenppn'] = $tarifppn[$param['divisi']];
			}
			if($param['persenpph'] == '' || $param['persenpph'] == 0){
				$param['persenpph'] = $tarifpph[$divisi];
			}
			
			if($param['persenppn'] == ''){
				throw new PDOException('Tarif ppn belum terdaftar , silahkan di daftarkan pada master vendor');
			}

			if($param['persenpph'] == ''){
				throw new PDOException('Tarif pph belum terdaftar , silahkan di daftarkan pada master vendor');
			}
			*/
			if($param['currRow']=='1'){
				$str="delete from ".$dbname.".pmn_tbs where notransaksi='".$param['notransaksi']."'";
				$owlPDO->exec($str);
			}
			
			// exit("Error".$kodeho[$kodept[$param['unit']]]);
			
			#= ro unit dan ro pemilik diganti honya
			
			$data = array(
				'notransaksi'=>$param['notransaksi'],
				'unit'=>$param['unit'],
				'tanggal'=>tanggalsystemn($param['tanggal']),
				'divisi'=>'',
				'tanggaltbs1'=>tanggalsystemn($param['tanggaltbs1']),
				'tanggaltbs2'=>tanggalsystemn($param['tanggaltbs2']),
				'persenppn'=>$param['persenppn'],
				'persenpph'=>$param['persenpph'],
				'keteranganht'=>$param['keteranganht'],
				'tanggalspb'=>tanggalsystemn($param['tanggalspb']),
				'tanggalpks'=>tanggalsystemn($param['tanggalpks']),
				'nospb'=>$param['nospb'],
				'notiket'=>$param['notiket'],
				'blok'=>$param['kodeblok'],
				'kgbruto'=>$param['kgbruto'],
				'kgpotongan'=>$param['kgpotongan'],
				'kgnetto'=>$param['kgnetto'],
				'bjr'=>$param['bjr'],
				'tahuntanam'=>$param['tahuntanam'],
				'rpkg'=>$param['rpkg'],
				'totalrp'=>$param['totalrp'],
				'supplier'=>$param['divisi'],
				'createby' => $_SESSION['standard']['userid'],
				'createtime' => date('Y-m-d H:i'),
				'updateby' => $_SESSION['standard']['userid'],
				'pemilik' =>'',
				'rounit' =>$kodeho[$kodept[$param['unit']]],
				'ropemilik' =>$kodeho[$dtkodept],
				'noreferensi'=>'',
				'tipetbs'=>$param['jenisx'],
				'noakunhutang'=>@$noakunhutang,
				'noakunbiaya'=>'',
				'tipesupplier'=>@$tipesupplier,
				'jjg'=>$param['jjg'],
				'jjgsortasi'=>$param['jjgsortasi'],
				'kgmasuk'=>$param['kgmasuk'],
				'kgkeluar'=>$param['kgkeluar'],
				'potpersenaktual'=>$param['potpersenaktual'],
				'potpersensetup'=>$param['potpersensetup'],
				'potpersen'=>$param['potpersen'],
				'kgadjust'=>$param['kgadjust'],
				'kgnettoadjust'=>$param['kgnettoadjust'],
				'rpadjust'=>$param['rpadjust'],
				'jumrpadjust'=>$param['jumrpadjust'],
				'nokontrak'=>$param['nokontrak'],
				'dibuat'=>$param['dibuat'],
				'disetujui'=>$param['disetujui'],
				'diperiksa'=>$param['diperiksa'],
				'kodevhc'=>$param['kodevhc'],
				'driver'=>$param['driver'],
				'kelasbuah'=>(is_null($param['kelasbuah']) ? "S" : $param['kelasbuah'])
			);

			if ($param['unithutang'] != '') {
				$data['unithutang'] = $param['unithutang'];
			}
			

			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$str = insertQuery($dbname,'pmn_tbs',$data,$cols); 	
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;

	 case'loaddata':
		$where=" 1=1 ";
		if($tanggalselesaisch!='' and $tanggalmulaisch!=''){
			$where.=" and tanggal between '".$tanggalmulaisch."' and '".$tanggalselesaisch."'";
		}
		if($notransaksisch!=''){
			$where.=" and notransaksi like '%".$notransaksisch."%'";
		}
		
		if($unitsch!=''){
			$where.=" and unit= '".$unitsch."'";
		}
		
		if($suppliersch!=''){
			$where.=" and supplier='".$suppliersch."'";
		}
		if($postingsch!=''){
			$where.=" and posting='".$postingsch."'";
		}

		if($jenissch!=''){
			$where.=" and tipetbs='".$jenissch."'";
		}else{
			$where.=" and tipetbs in ('KUD','EXT','AFI','INT')";
		}
		
		$limit=10;
        $page=0;
        if(isset($param['page'])){
			$page=$param['page'];
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$no=(($page*$limit));
		$colspan=24;
		
		$offset = $page * $limit;
		$str = "select count(distinct(notransaksi)) as jumrow from ".$dbname.".pmn_tbs where 1=1 and  ".$where."";
        $res=fetchdata($str);
		$jlhbrs = $res[0]['jumrow'];
		
		$tab=$footd="";
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td align=center colspan='".$colspan."'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			if($tp=='excel'){
				$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable  width=100%>
					<thead>
					<tr class=rowheader>
						<td  align=center>".$_SESSION['lang']['nourut']."</td>
						<td  align=center>".$_SESSION['lang']['notransaksi']."</td>
						<td  align=center>Jenis</td>
						<td  align=center>".$_SESSION['lang']['tanggal']."</td>
						<td  align=center>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['pabrik']."</td>
						<td  align=center>".$_SESSION['lang']['supplier']."</td>
						<td  align=center hidden>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['induk']."</td>
						<td align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</td>
						<td  align=center>".$_SESSION['lang']['berat']."</td>
						<td  align=center>".$_SESSION['lang']['potongan']."</td>
						<td  align=center>".$_SESSION['lang']['netto']."</td>
						<td  align=center>".$_SESSION['lang']['rpkg']."</td>
						<td  align=center>".$_SESSION['lang']['total']."</td>
						<td  align=center>".$_SESSION['lang']['ppn']."</td>
						<td  align=center>".$_SESSION['lang']['pph']."</td>
						<td  align=center>".$_SESSION['lang']['grnd_total']."</td>
						<td  align=center>".$_SESSION['lang']['keterangan']."</td>
						<td  align=center>".$_SESSION['lang']['updateby']."</td>
					</tr>  
					</thead>";
			}else{
				$tpxx=" limit " . $offset . "," . $limit . "";
			}
			$str = "select 	sum(kgbruto) as kgbruto,
							sum(kgpotongan) as kgpotongan,
							sum(kgnetto) as kgnetto,
							sum(totalrp) as totalrp,
							sum(rpadjust) as rpadjust,
							sum(kgadjust) as kgadjust,
							sum(jumrpadjust) as jumrpadjust,
							persenpph,
							persenppn,
							rpkg as rpkg,
							tipetbs as tipetbs,
							updateby as updateby,
							postingby as postingby,
							updatetime as updatetime,
							notransaksi,unit,supplier,tanggal,posting,tanggaltbs1,tanggaltbs2,pemilik,divisi,keteranganht 	 
					from ".$dbname.".pmn_tbs  
					where ".$where." 
				group by notransaksi order by tanggal desc,notransaksi desc  ".$tpxx." ";
			$res=fetchdata($str);
			foreach($res as $bar){
				
				$jumlahperiode=0;
				$dtperiode='';
				#= cek apakah transaksi ini ada tbs yang berbeda periode
				$strdt="select distinct(substr(tanggalpks,1,7)) as periode from ".$dbname.".pmn_tbs where notransaksi='".$bar['notransaksi']."' ";
				$resdt=fetchdata($strdt);
				foreach($resdt as $bardt){
					// $dtperiode=
					$jumlahperiode++;
					$dtperiode.=$bardt['periode'].' ';
				}
				// @$jumlahperiode = $resdt[0]['jumlahperiode'];
				
				
				$no++;
				$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$bar['notransaksi']."</td>";
					$tab.="<td style='text-align:center'>".$arrjenistbs[$bar['tipetbs']]."</td>";
					$tab.="<td style='text-align:center' nowrap>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td>".getNamaOrg($bar['unit'])."</td>";
					$tab.="<td>".getNamaSupplier($bar['supplier'])."</td>";
					$tab.="<td hidden>".$bar['pemilik']."</td>";
					$tab.="<td style='text-align:center' nowrap>".tanggalnormal($bar['tanggaltbs1'])." s/d ".tanggalnormal($bar['tanggaltbs2'])."</td>";
					$tab.="<td align=right>".hidezerodecimal($bar['kgbruto'],2)."</td>";
					$tab.="<td align=right>".hidezerodecimal($bar['kgpotongan'],2)."</td>";
					$tab.="<td align=right>".hidezerodecimal($bar['kgnetto'],2)."</td>";
					$tab.="<td align=right>".hidezerodecimal($bar['rpkg'],2)."</td>";
					$tab.="<td align=right>".hidezerodecimal($bar['totalrp'],2)."</td>";
					$tab.="<td align=right>".hidezerodecimal($bar['persenppn']/100*$bar['totalrp'],0)."</td>";
					$tab.="<td align=right>".hidezerodecimal($bar['persenpph']/100*$bar['totalrp'],0)."</td>";
					$tab.="<td align=right>".hidezerodecimal($bar['totalrp']+($bar['persenppn']/100*$bar['totalrp'])-($bar['persenpph']/100*$bar['totalrp']),0)."</td>";
					$tab.="<td align=left>".$bar['keteranganht']."</td>";
					$tab.="<td>".getNamaKaryawan($bar['updateby'])."</td>";
					// $tab.="<td style='text-align:center;vertical-align:middle'><label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('".$bar['notransaksi']."',event)\">History Approval</label></td>";
					
					if($tp!='excel'){
						if($bar['posting']==0 || $bar['posting']==3){
							$tab.="<td align=center>
								<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editht('".$bar['notransaksi']."');\">
							</td>";
							$tab.="<td align=center>
								<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"deleteht('".$bar['notransaksi']."');\">
							</td>";	
							$tab.="<td align=center>
								<img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"posting('".$bar['notransaksi']."');\">
								<img style='display:none' src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick='form_ajukan(`".$bar['notransaksi']."`)'>
							</td>";
						} else if ($bar['posting'] == 9){
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td align=center>
								<img src='images/icons/04/16/04.png' class='zImgBtn' height='30' title='On Progress Approval'>
							</td>";
						} else if ($bar['posting'] == 2){
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td align=center>
								<img src='images/icons/04/16/01.png' class='zImgBtn' height='30' title='Approval Rejected'>
							</td>";
						} else {
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td align=center>
								<img src=images/icons/04/16/02.png class=resicon  title='Posted'>
							</td>";
						}
						
						$tab.="<td align=center>
							<img onclick=\"formdetail('".$bar['notransaksi']."');\" class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;'>
						</td>";
						$tab.="<td align=center>
							<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print ".$bar['notransaksi']."' onclick=\"pdf('".$bar['notransaksi']."');\">
						</td>";
						$tab.="<td align=center>
							<img src=images/excel.jpg class=resicon caption='Excel' ".$hide." title='Excel  ".$bar['notransaksi']."' onclick=\"excel('".$bar['notransaksi']."');\">
						</td>";
						if($jumlahperiode>1){
							$tab.="<td bgcolor=red>Terdapat sebrang bulan dalam 1 transaksi. Periode ".$dtperiode."</td>";
						}else{
							$tab.="<td></td>";
						}
					}
					
					
					
				$tab.="</tr>";
			}
				
			## PAGING
			$footd.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		}
			
		if($tp=='excel'){
			$nop = "BA PEMBELIAN TBS.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("BA PEMBELIAN TBS", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $tab."####".$footd;			
		}
	break;

	//Umar
	case 'form_ajukan':
		$query  = "SELECT unit FROM $dbname.pmn_tbs WHERE notransaksi = '".$param['notransaksi']."'";
		$result = fetchData($query, 'OBJECT');
		$unit   = $result[0]->unit;

        $opt    = array();
        $query  = "SELECT * FROM $dbname.setup_approval WHERE jenispersetujuan = 'BABELITBS' AND kodeunit = '$unit' ORDER BY level";
        $result = fetchData($query, 'OBJECT');        
        foreach ($result as $key => $value) {
            $opt['approver'][$value->level][$value->karyawanid] = "<option value='".$value->karyawanid."'>".$utilities['worker']['Name'][$value->karyawanid]."</option>";
            $opt['level'][$value->level] = $value->level;
        }

        $jumlahlevel = count($opt["level"]);
        $stream .= "<input type='hidden' id='notransaksi_ajukan' value='".$param['notransaksi']."'/>";
        $stream .= "<input type='hidden' id='jlh' value='".$jumlahlevel."'/>";
        $optShow = "";
        foreach ($opt['approver'][1] as $key => $value) {
            $optShow .= $value;
        }

        $stream .= "<tr class='rowcontent'>";
            $stream .= "<td> Approval ke - 1</td>";
            $stream .= "<td style='width:5px'> : </td>";
            $stream .= "<td>";
                $stream .= "<select id='kepada1' style='width:99%'>".$optShow."</select>";
            $strean .= "</td>";
        $stream .= "</tr>";

        $stream .= "<tr class='rowcontent'>";
            $stream .= "<td></td>";
            $stream .= "<td></td>";
            $stream .= "<td>";
                $stream .= "<button id='tomboldetail' class='mybutton' onclick='ajukan()'>" . $_SESSION['lang']['diajukan'] . "</button>";
            $strean .= "</td>";
        $stream .= "</tr>";

        echo $stream;
    break;

    case 'ajukan':
        for ($i = 1; $i <= $param['jlh'] ; $i++) { 
            $per['persetujuan'.$i] = checkPostGet("kepada".$i, '');
            if($per['persetujuan'.$i] == '' or $param['notransaksi'] == ''){
                exit('Warning : Isikan nama penyetuju.');
            }
        }

        $query = "UPDATE $dbname.pmn_tbs SET posting = '9' WHERE notransaksi = '".$param['notransaksi']."'";
        
        try {
            $owlPDO->exec($query);

			$query  = "SELECT unit FROM $dbname.pmn_tbs WHERE notransaksi = '".$param['notransaksi']."'";
			$result = fetchData($query, 'OBJECT');
			$unit   = $result[0]->unit;

            $jenispersetujuan = 'BABELITBS';
            for($i = 1; $i <= $param['jlh']; $i++){
                $query  = "SELECT * FROM $dbname.setup_approval WHERE jenispersetujuan = '$jenispersetujuan' AND level = '$i' AND kodeunit = '$lokasitugas'";
                $result = fetchData($query, 'OBJECT');
                $tipeapp            = $result[0]->tipe;
                $departemenapp      = $result[0]->departemen;
                $tipekaryawanapp    = $result[0]->tipekaryawan;
                $jabatanapp         = $result[0]->jabatan;

                if ($tipeapp == 1) {
                    if ($departemenapp != "") {
                        $query = "SELECT * FROM $dbname.datakaryawan WHERE bagian = '".$departemenapp."'";
                    }
                    
                    if ($tipekaryawanapp != "") {
                        $query = "SELECT * FROM $dbname.datakaryawan WHERE tipekaryawan = '".$tipekaryawanapp."'";
                    }
                    
                    if ($jabatanapp != "") {
                        $query = "SELECT * FROM $dbname.datakaryawan WHERE kodejabatan = '".$jabatanapp."'";
                    }
                    
                    $result = fetchData($query, 'OBJECT');
                    foreach($result as $key => $value){
                        $query = "INSERT INTO $dbname.approval (notransaksi,jenispersetujuan,level,karyawanid,status) VALUES ('".$param['notransaksi']."', '".$jenispersetujuan."', '".$i."', '".$valx['karyawanid']."', '0')";

                        $owlPDO->exec($query);
                    }

                    break;
                } else {
                    if($per['persetujuan'.$i] != ''){
                        $query  = "INSERT INTO $dbname.approval (notransaksi,jenispersetujuan,level,karyawanid,status) VALUES ('".$param['notransaksi']."', '".$jenispersetujuan."', '".$i."', '".$per['persetujuan'.$i]."', '0')";
                    }
                }

                try {
                    $owlPDO->exec($query);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
	//End Umar
	
	case'posting':
		try {
			$owlPDO->beginTransaction();
			
			
			cekperiodeakuntansi($unit,$tanggal);
			
			#= delete 1st
			$str="delete from ".$dbname.".keu_jurnalht  where noreferensi='".$param['notransaksi']."'";
			$owlPDO->exec($str); 
			
			

			#= bentuk query data untuk posting
			$str = "SELECT sum(kgnetto) as kgnetto, sum(totalrp) as totalrp,sum(jumrpadjust) as jumrpadjust,sum(rpadjust) as rpadjust,notransaksi, unit, unithutang, divisi, tanggal,posting, supplier, tanggal,tanggaltbs1, tanggaltbs2,rounit, ropemilik, tipetbs FROM ".$dbname.".pmn_tbs WHERE notransaksi ='".$param['notransaksi']."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$tipetbs = $bar['tipetbs'];
				$rounit = $bar['unit'];
				$unithutang = $bar['unithutang'];
				$tanggal = $bar['tanggaltbs2'];
				$totalrp = $bar['totalrp'];
				$rpadjust = $bar['rpadjust'];
				$jumrpadjust = $bar['jumrpadjust'];
				$notransaksi = $bar['notransaksi'];
				$supplier = $bar['supplier'];
			}
			
			
			#====notransaksi jurnal akun debet serta kredit dari parameter jurnal
			if ($tipetbs == 'EXT' || $tipetbs == 'EXTERNAL') {
				$kodejurnal = 'INVTB';
			} else if ($tipetbs == 'AFI' || $tipetbs == 'AFILIASI') {
				$kodejurnal = 'INVTA';
			} else if ($tipetbs == 'KUD' || $tipetbs == 'KOPLAS') {
				$kodejurnal = 'INVTK';

				if ($unithutang != null) {
					$rounit = $unithutang;
				}
			}else if ($tipetbs == 'INT') {
				$kodejurnal = 'INVTI';
			}else{
				exit("Warning:Tidak ada kelompok jurnal untuk ".$tipetbs." ");
			}
			

			if ($tipetbs != 'INT') {
				$optInduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$rounit."'");

				$whereNoindukph = "kodekelompok='".$kodejurnal."' and kodeorg='".$kodept[$rounit]."' and kodeunit='".$rounit."' and periode='".substr($tanggal,0,7)."'";						
				$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNoindukph);
				$noKon = fetchData($query);

				$tmpC = $noKon[0]['nokounter'];
				$tmpC++;

				$counterjurnal = addZero($tmpC,4);
				$nojurnal = str_replace("-","",$tanggal)."/".$rounit."/".$kodejurnal."/".$counterjurnal;
				
				#akun debet serta krdit
				$query2 = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',"jurnalid='".$kodejurnal."' and aktif=1");
				$dtnoakun = fetchData($query2);

				#nama supplier
				$optsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
				$namasupplier = $optsup[$supplier];
				
				#=== Transform Data ===
				$dataRes['header'] = array();
				$dataRes['detail'] = array();
				
				# Prep Header
				$dataRes['header'] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>$kodejurnal,
					'tanggal'=>$tanggal,
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>round($totalrp),
					'totalkredit'=>round($totalrp)*-1,
					'amountkoreksi'=>'0',
					'noreferensi'=>$notransaksi,
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);
				
				#= debet
				$noUrut=1;
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>$noUrut,
					'noakun'=>$dtnoakun[0]['noakundebet'],
					'keterangan'=>'Penerimaan TBS unit '.$rounit.' dari '.$namasupplier.' pada tanggal '.$tanggal,
					'jumlah'=>round($totalrp,0),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$rounit,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$supplier,
					'noreferensi'=>$notransaksi,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$notransaksi,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => '0000000001'
				);
				
				#= debet
				if($rpadjust>0){
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$tanggal,
						'nourut'=>$noUrut,
						'noakun'=>$dtnoakun[0]['noakundebet'],
						'keterangan'=>'Penerimaan TBS fix grading unit '.$rounit.' dari '.$namasupplier.' pada tanggal '.$tanggal,
						'jumlah'=>round($rpadjust,0),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$rounit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>$supplier,
						'noreferensi'=>$notransaksi,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$notransaksi,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => '0000000001'
					);
				}
				
				$noUrut++;
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>$noUrut,
					'noakun'=>$dtnoakun[0]['noakunkredit'],
					'keterangan'=>'Penerimaan TBS unit '.$rounit.' dari '.$namasupplier.' pada tanggal '.$tanggal,
					'jumlah'=>round($jumrpadjust,0)*-1,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$rounit,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$supplier,
					'noreferensi'=>$notransaksi,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$notransaksi,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => '0000000001'
				);						
			
				/*,
					'createby' => $_SESSION['standard']['userid'],
					'createtime' => date('Y-m-d H:i:s'),
					'updateby' => $_SESSION['standard']['userid'],
					'updatetime' => date('Y-m-d H:i:s')*/
			
				$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
				
				$owlPDO->exec($queryH);
				
				
				foreach($dataRes['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					$owlPDO->exec($queryD);
				}
				
				$errCounter = "";
				$queryJ = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $tmpC),$whereNoindukph);
				$owlPDO->exec($queryJ); 
				
			}
		
		
			$str="update ".$dbname.".pmn_tbs set posting='1', postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
	
	
	case'saveajukan':
		try {
			$owlPDO->beginTransaction();
			
			
			#= cek tipe transaksi tbs int/ext/kud/afi
			#= selain int harus ada rp-nya
			#= jika tidak ada trap error tidak bisa diajukan
			
			$str = "select sum(totalrp) as totalrp,tipetbs from " . $dbname . ".pmn_tbs where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$tipetbs=$bar['tipetbs'];
				$totalrp=$bar['totalrp'];
			}
			
			if($tipetbs!='INT'){
				if($totalrp=='0'){
					throw new PDOException("Nilai rupiah masih kosong, silahkan edit transaksi, dan lakukan penyimpanan ulang");
				}
			}
			
		
			if($tanggalpengajuan==''){
				throw new PDOException("Tanggal pengajuan masih kosong");
				
			}
			
			for($i=1; $i<=$maxaproval; $i++){
				if($param['persetujuan'][$i]==''){
					throw new PDOException("Persetujuan ".$i." belum dipilih.");
				}
			}
			
			#= delete 1st untuk aprovalnya
			$str = "delete from " . $dbname . ".approval where notransaksi='".$notransaksi."' and jenispersetujuan = 'BASTTBS'";
			$owlPDO->exec($str);
			
			$str = "update " . $dbname . ".pmn_tbs set posting=9, postingby = '".$_SESSION['standard']['userid']."', tanggalpengajuan='".tanggalsystemn($param['tanggalpengajuan'])."' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);

			for($i=1; $i<=$maxaproval; $i++){
				#= insert
				$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal,requester,tanggalrequest)
					   values('".$notransaksi."','BASTTBS','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";	
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
		$owlPDO->rollback();
			echo "Warning: Gagal melakukan pengajuan, \n" . addslashes($e->getMessage());

		}
	break;
	
	case'pdf':
		$tab="<style>
			@page {
				margin-top: 150px;
				margin-left: 20px;
				margin-right: 20px;
				margin-bottom: 20px;
			}
			body {
				font-family: Tahoma, Verdana, Segoe, sans-serif;
			}
			
			footer {
				position: fixed; 
				bottom: -20px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
			div.page_break {
				page-break-before: always;
			}
			
			
			
			header {
				position: fixed;
				top: -125px;
				left: 0px;
				right: -5px;
				height: 0px;
			}
			
			.page:after {
			  content: counter(page, decimal);
				font-weight: bold;
			}
		</style>";
		
		$str = "select * from ".$dbname.".pmn_tbs where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		foreach($res as $bar){

			$arrtgl[$bar['tanggalpks']]=$bar['tanggalpks'];
			$arrnotiket[$bar['notiket']]=$bar['notiket'];
			
			$listnotiket[$bar['tanggalpks']][$bar['notiket']]=$bar['notiket'];
			$listtgl[$bar['tanggalpks']]=$bar['tanggalpks'];
			
			
			$lsbjr[$bar['tanggalpks']][$bar['notiket']]=$bar['notiket'];
			@$lskodeblok[$bar['tanggalpks']][$bar['notiket']]=$bar['kodeblok'];
			$lsnospb[$bar['tanggalpks']][$bar['notiket']]=$bar['nospb'];
			$lskodevhc[$bar['tanggalpks']][$bar['notiket']]=$bar['kodevhc'];
			$lsdriver[$bar['tanggalpks']][$bar['notiket']]=$bar['driver'];
			$lstahuntanam[$bar['tanggalpks']][$bar['notiket']]=$bar['tahuntanam'];
			@$lskgbruto[$bar['tanggalpks']][$bar['notiket']]+=$bar['kgbruto'];
			@$lskgpotongan[$bar['tanggalpks']][$bar['notiket']]+=$bar['kgpotongan'];
			@$lskgnetto[$bar['tanggalpks']][$bar['notiket']]+=$bar['kgnetto'];
			@$lsjjg[$bar['tanggalpks']][$bar['notiket']]+=$bar['jjg'];
			@$lskelasbuah[$bar['tanggalpks']][$bar['notiket']]=$bar['kelasbuah'];
			@$lstotalrp[$bar['tanggalpks']][$bar['notiket']]=$bar['totalrp'];
			@$lsrpkg[$bar['tanggalpks']][$bar['notiket']]=$bar['rpkg'];
			
			
			@$ttlskgbruto+=$bar['kgbruto'];
			@$ttlskgpotongan+=$bar['kgpotongan'];
			@$ttlskgnetto+=$bar['kgnetto'];
			@$ttlstotalrp+=$bar['totalrp'];
			
			@$tlskgbruto[$bar['tanggalpks']]+=$bar['kgbruto'];
			@$tlskgpotongan[$bar['tanggalpks']]+=$bar['kgpotongan'];
			@$tlskgnetto[$bar['tanggalpks']]+=$bar['kgnetto'];
			@$tlstotalrp[$bar['tanggalpks']]+=$bar['totalrp'];
			
			$createby=$bar['createby'];
			$dibuat=$bar['dibuat'];
			$diperiksa=$bar['diperiksa'];
			$disetujui=$bar['disetujui'];
			$unit=$bar['unit'];
			$tanggal=$bar['tanggal'];
			$tanggal=$bar['tanggal'];
			$tanggaltbs1=$bar['tanggaltbs1'];
			$tanggaltbs2=$bar['tanggaltbs2'];
			$supplier=$bar['supplier'];
			$nokontrak=$bar['nokontrak'];
			$keteranganht=$bar['keteranganht'];
			$tptbs=$bar['tipetbs'];
			$persenppn=$bar['persenppn'];
			$persenpph=$bar['persenpph'];			
			
		}
		
		
		$str="select * from ".$dbname.".datakaryawan where 
			karyawanid in ('".$createby."','".$dibuat."','".$diperiksa."','".$disetujui."') ";
		$res=fetchdata($str);
		foreach($res as $bar){
			@$nikkaryawan[$bar['karyawanid']] = $bar['nik'];
			@$nmkaryawan[$bar['karyawanid']] = $bar['namakaryawan'];
			@$kodejabatan[$bar['karyawanid']] = $bar['kodejabatan'];
		}
		
		
		$str="select * from ".$dbname.".pmn_kontrakbeli where notransaksi='".$nokontrak."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			@$tanggalkirim= $bar['tanggal'];
			@$tglsampai = $bar['tanggalsampai'];
			@$tgldari = $bar['tanggaldari'];
			@$volume = $bar['volume'];
		}
		
		
		// $tab .= "<br>";
		$tab.="<body>";
				
		if ($param['tipe']=='pdf'){
			$tab.="<header>";
		}
		
		@$countpage+=1;
		$cellpadding=1;	
		$fontsize="11";	
		
		
		
		if ($param['tipe']=='pdf'){
			$tab.="<table cellpadding=".$cellpadding."  width=100%  cellspacing=0 border=0 style='font-size:".$fontsize."px;'>";
		}else{
			$tab.="<table cellpadding=".$cellpadding."   width=100% cellspacing=0 border=0 style='font-size:".$fontsize."px;'>";
		}
		
			$tab.="<tr>";
				$tab.="<td align=left  style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' ><b>".$nmorg[$kodept[$unit]]."</b></td>"; 	
				$tab.="<td align=right  style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' class=page></td>"; 	
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td align=left colspan=2 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' ><b>BERITA ACARA SERAH TERIMA TBS</b></td>"; 	
			$tab.="</tr>";	
		$tab.="</table>";	
		
		$tab.="<table cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
			$tab.="<tr>";
				$tab.="<td style='width:100px'>No. BAST</td>"; 	
				$tab.="<td>:</td>"; 	
				$tab.="<td style='width:350px'>".$param['notransaksi']."</td>"; 	
				$tab.="<td>".$_SESSION['lang']['kontrak']."</td>"; 	
				$tab.="<td>:</td>"; 	
				$tab.="<td>".$nokontrak."</td>"; 	
				
			$tab.="</tr>";	
			
			$tab.="<tr>";
				$tab.="<td>".$_SESSION['lang']['tanggal']." BAST</td>"; 	
				$tab.="<td>:</td>"; 	
				$tab.="<td>".tglnmbln($tanggal,'','')."</td>"; 	
				$tab.="<td>".$_SESSION['lang']['supplier']."</td>"; 	
				$tab.="<td>:</td>"; 	
				$tab.="<td>".$nmsupplier[$supplier]."</td>"; 	
				
			$tab.="</tr>";	
			
			$tab.="<tr>";
				$tab.="<td>".$_SESSION['lang']['periode']." BAST</td>"; 	
				$tab.="<td>:</td>"; 	
				$tab.="<td>".tglnmbln($tanggaltbs1,'','')." s/d ".tglnmbln($tanggaltbs2,'','')."</td>"; 	
				
				
			$tab.="</tr>";	
			
		
			$tab.="<tr>";
				$tab.="<td colspan=11>Terlampir kami sampaikan bahwa TBS telah diterima dari ".$nmsupplier[$supplier].", dengan perincian sebagai berikut:</td>"; 	
			$tab.="</tr>";	
			
		$tab.="</table>";	
		
		
		// $tab.="<table cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:".$fontsize."px'>";
		// $tab.="<tr>";
			
		// $tab.="</tr>";
		// $tab.="</table>";	
		
		if ($param['tipe']=='pdf'){
			$tab.="</header>";
		}	
	
		
		$tab.="<table  cellpadding=".$cellpadding." cellspacing=0   width=100% border=0 style='font-size:".$fontsize."px'>";
		
			// $tab.="<tr>";
				// $tab.="<td colspan=12 style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left>".@$nmorg[$kddiv]."</td>"; 
			// $tab.="</tr>";
			$tab.="<tr>";
			
		
			$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['nourut']."</td>"; 
			$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['tanggal']."</td>"; 
			$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['nospb']."</td>"; 
			$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['nopol']."</td>"; 
			$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['noTiket']."</td>"; 
			$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['berat']."</td>"; 
			$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['sortasi']."</td>"; 
			$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['netto']."</td>"; 
			if($tptbs != 'EXT'){
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['rpkg']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000' align=center>".$_SESSION['lang']['total']."</td>"; 
			}
			$tab.="</tr>";
			foreach($arrtgl as $kdtgl){
					foreach($arrnotiket as $kdtiket){
						if(@$listnotiket[$kdtgl][$kdtiket]!=''){
							@$no++;
							@$lsbjr[$kdtgl][$kdtiket]=$lskgbruto[$kdtgl][$kdtiket]/$lsjjg[$kdtgl][$kdtiket];
							$tab.="<tr>";
								$tab.="<td  style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=center>".$no."</td>"; 
								$tab.="<td nowrap style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=center>".tglnmbln($kdtgl,'','')."</td>"; 
								$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=center>".$lsnospb[$kdtgl][$kdtiket]."</td>"; 
								$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=center>".$lskodevhc[$kdtgl][$kdtiket]."</td>"; 
								$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=center>".$kdtiket."</td>"; 
								$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right>".hidezerodecimal($lskgbruto[$kdtgl][$kdtiket])."</td>"; 
								$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right>".hidezerodecimal($lskgpotongan[$kdtgl][$kdtiket])."</td>"; 
								$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right>".hidezerodecimal($lskgnetto[$kdtgl][$kdtiket])."</td>"; 
								if($tptbs != 'EXT'){
								$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right>".hidezerodecimal($lsrpkg[$kdtgl][$kdtiket])."</td>"; 
								$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right>".hidezerodecimal($lstotalrp[$kdtgl][$kdtiket])."</td>"; 
								}
							$tab.="</tr>";
						}
					}
				$tab.="<tr>";
					$tab.="<td colspan=5 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left><b>".$_SESSION['lang']['total']." ".tglnmbln($kdtgl,'','')."</b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($tlskgbruto[$kdtgl])."</b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($tlskgpotongan[$kdtgl])."</b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($tlskgnetto[$kdtgl])."</b></td>"; 
					if($tptbs != 'EXT'){
						$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
						$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($tlstotalrp[$kdtgl])."</b></td>"; 
					}
				$tab.="</tr>";
				
			}
			if($tptbs != 'EXT'){
			$tab.="<tr>";
				$tab.="<td colspan=5 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left><b>".$_SESSION['lang']['total']."</b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($ttlskgbruto)."</b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($ttlskgpotongan)."</b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($ttlskgnetto)."</b></td>"; 
				if($tptbs != 'EXT'){
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($ttlstotalrp)."</b></td>"; 
				}
			$tab.="</tr>";
			
			$ttlstotalppn=$ttlstotalrp*$persenppn/100;
			$ttlstotalpph=$ttlstotalrp*$persenpph/100;
			$tttlstotalppn=$ttlstotalrp+$ttlstotalppn-$ttlstotalpph;
			
			
			$tab.="<tr>";
				$tab.="<td colspan=5 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left><b>".$_SESSION['lang']['ppn']." </b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>";
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($ttlstotalppn)."</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=5 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left><b>".$_SESSION['lang']['pph']."</b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($ttlstotalpph)."</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=5 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left><b>".$_SESSION['lang']['grnd_total']."</b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>";
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($tttlstotalppn)."</b></td>"; 
			$tab.="</tr>";
			}
			## CEK ADA UANG MUKA
			$str="select noinvoice from ".$dbname.".keu_tagihanht where nopo='".$param['notransaksi']."' and tipeinvoice='um'";
			$res=fetchdata($str);
			$noinvoice=$res[0]['noinvoice'];
			if($noinvoice!=''){
				$uangmuka=0;
				$pajak=0;
				$str="select * from ".$dbname.".keu_tagihandt where noinvoice='".$noinvoice."'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($val['noakun']=='1180102'){
						$uangmuka+=$val['nilai'];
					}else{
						$pajak+=$val['nilai'];
					}
				}
				$tab.="<tr>";
					$tab.="<td colspan=5 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left><b>".$_SESSION['lang']['uangmuka']."</b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>";
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($uangmuka)."</b></td>"; 
				$tab.="</tr>";
				
				$tab.="<tr>";
					$tab.="<td colspan=5 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left><b>".$_SESSION['lang']['uangmuka']." (PPn)</b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>";
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($pajak)."</b></td>"; 
				$tab.="</tr>";
				
				$ttlx=$tttlstotalppn-$uangmuka-$pajak;
				$tab.="<tr>";
					$tab.="<td colspan=5 style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left><b>".$_SESSION['lang']['total']."</b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b></b></td>";
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=right><b>".hidezerodecimal($ttlx)."</b></td>"; 
				$tab.="</tr>";
			}

		$tab.="</table>";
		
		
			$isibawah="<br>".$keteranganht.", Demikian berita acara ini dibuat, dan ditandatangani oleh kedua belah pihak tanpa ada paksaan oleh pihak manapun yang digunakan untuk sebagaimana semestinya";
			
			$tab.="<table cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:".$fontsize."px'>";
			$tab.="<tr>";
			$tab.="<td style='border-top:0px solid #000000;border-bottom:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;' align=left>".$isibawah."</td>"; 
			$tab.="<tr>";
			$tab.="</table>";
			$tab.="<br>";

		
		
		//Diterima  		Diketahui				Disetujui oleh,		Dibuat/Diserahkan oleh,			
			if ($param['tipe']=='pdf'){
				$tab.="<br><table style='width:100%;font-size:".$fontsize."px' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
			}else{
				$tab.="<br><table style='font-size:".$fontsize."px' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
			}
			$tab.="<tr>";
				$tab.="<td align=center style='width:100px;'>".$_SESSION['lang']['diterima']."</td>";
				$tab.="<td align=center style='width:100px;'>".$_SESSION['lang']['diperiksa']."</td>";  
				$tab.="<td align=center style='width:100px;'>".$_SESSION['lang']['disetujui']."</td>";  
				$tab.="<td align=center style='width:100px;'>".$_SESSION['lang']['dibuat']."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td valign=bottom  align=center style='height:75px;width:60px; solid #000000'><u></u></td>"; 
				$tab.="<td valign=bottom  align=center style='height:75px;width:60px; solid #000000'>".getNamaKaryawan($diperiksa)."</td>"; 
				$tab.="<td valign=bottom  align=center style='height:75px;width:60px; solid #000000'>".getNamaKaryawan($disetujui)."</td>"; 
				$tab.="<td valign=bottom  align=center style='height:75px;width:60px; solid #00000C'>".getNamaKaryawan($createby)."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		
	
		
	$tab.="</body>";	
		
		if ($param['tipe']=='pdf'){
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$dompdf->stream("TBS",array("Attachment"=>0));	
		} else {
			echo $tab;
		}
	break;
	
	case'excel':
	
		$str = "select * from ".$dbname.".pmn_tbs  where notransaksi='".$notransaksi."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$namasupplierexcel=$nmsupplier[$bar['supplier']];
			$tgl1excel=tanggalnormal($bar['tanggaltbs1']);
			$tgl2excel=tanggalnormal($bar['tanggaltbs2']);
			$tanggaltbs1=$bar['tanggaltbs1'];
			$tanggaltbs2=$bar['tanggaltbs2'];
			$persenppn=$bar['persenppn'];
			$persenpph=$bar['persenpph'];
			$unit=$bar['unit'];
			$supplier=$bar['supplier'];
			$keteranganht=$bar['keteranganht'];
			
			$dibuat=$bar['createby'];
			$disetujui=$bar['disetujui'];
			$diperiksa=$bar['diperiksa'];
					
				
		$str="select * from ".$dbname.".datakaryawan where 
			karyawanid in ('".$dibuat."','".$disetujui."','".$diperiksa."') ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$nmkaryawan[$bar['karyawanid']] = $bar['namakaryawan'];
			$kodejabatan[$bar['karyawanid']] = $bar['kodejabatan'];
		}	
		
		$str="select * from ".$dbname.".sdm_5jabatan";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namajabatan[$bar['kodejabatan']] = $bar['namajabatan'];
		}
		
		$tab1.="<table border=0>";
			$tab1.="<tr>";
				$tab1.="<td colspan=26 ><b>".$nmpt[$kodept[$unit]]."</td>";
			$tab1.="</tr>";
			$tab1.="<tr>";
				$tab1.="<td colspan=26 ><b>Commercial Division</td>";
			$tab1.="</tr>";
			$tab1.="<tr>";
				$tab1.="<td colspan=26 align=center><b>Rekapan Penerimaan TBS di PKS ".$kodept[$unit]." Periode Tanggal ".tglnmbln($tanggaltbs1,'','')." - ".tglnmbln($tanggaltbs2,'','')."</td>";
			$tab1.="</tr>";
			$tab1.="<tr>";
				$tab1.="<td colspan=26 align=center><b>".$nmsupplier[$supplier]."</td>";
			$tab1.="</tr>";
		$tab1.="</table>";
		$tab1.="<br>";
		
	
		$tab1.="<table border=1>";
		$tab1.="
		<tr class=rowheader  bgcolor=#D3D3D3>
			 <td  align=center>".$_SESSION['lang']['nourut']."</td>
			 <td  align=center>".$_SESSION['lang']['noTiket']."</td>
			 <td  align=center>No. Pol</td>
			 <td  align=center>".$_SESSION['lang']['supir']."</td>
			 <td  align=center>".$_SESSION['lang']['supplier']."<br></td>
			 <td  align=center>".$_SESSION['lang']['tanggal']."<br>PKS</td>
			 <td  align=center>".$_SESSION['lang']['tanggal']."<br>SPB</td>
			 
			 <td  align=center>".$_SESSION['lang']['berat']." I</td>
			 <td  align=center>".$_SESSION['lang']['berat']." II</td>
			 <td  align=center>".$_SESSION['lang']['berat']." TBS</td>
			 <td  align=center>".$_SESSION['lang']['potongan']."</td>
			 <td  align=center>".$_SESSION['lang']['netto']."</td> 
			 
			 <td  align=center>".$_SESSION['lang']['jjg']."</td> 
			 <td  align=center>".$_SESSION['lang']['sample']."</td> 
			 <td  align=center>".$_SESSION['lang']['bjr']."</td> 
			 
			  <td  align=center>".$_SESSION['lang']['nospb']."</td>
			 <td  align=center>".$_SESSION['lang']['tahuntanam']."</td>   
			 <td  align=center>".$_SESSION['lang']['harga']."</td>   
			 <td  align=center>".$_SESSION['lang']['jumlah']."<br>".$_SESSION['lang']['aktual']."</td>  
			 
			 <td  align=center>".$_SESSION['lang']['potongan']." %<br>(".$_SESSION['lang']['aktual'].")</td>   
			 <td  align=center>".$_SESSION['lang']['adjust']." %<br>(Setup)</td>   
			 <td  align=center>".$_SESSION['lang']['adjust']." %<br>(Perhitungan)</td>   
			 <td  align=center>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."</td>   
			 <td  align=center>".$_SESSION['lang']['netto']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."</td>   
			 <td  align=center>".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."</td>   
			 <td  align=center>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."</td>  
	
					 
		</tr>";
		
		
		$no=0;
		$str = "select * from ".$dbname.".pmn_tbs  where notransaksi='".$notransaksi."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab1.="<tr>";
				$tab1.="<td>".$no."</td>";
				$tab1.="<td>".$bar['notiket']."</td>";
				$tab1.="<td>".$bar['kodevhc']."</td>";
				$tab1.="<td>".$bar['driver']."</td>";
				$tab1.="<td>".$bar['supplier']."</td>";
				$tab1.="<td>".$bar['tanggalpks']."</td>";
				$tab1.="<td></td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['kgmasuk'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['kgkeluar'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['kgbruto'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['kgpotongan'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['kgnetto'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['jjg'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['jjgsortasi'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['bjr'],2)."</td>";
				$tab1.="<td>".$bar['nospb']."</td>";
				$tab1.="<td align=right>".$bar['tahuntanam']."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['rpkg'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['totalrp'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['potpersenaktual'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['potpersensetup'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['potpersen'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['kgadjust'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['kgnettoadjust'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['rpadjust'],2)."</td>";
				$tab1.="<td align=right>".hidezerodecimal($bar['jumrpadjust'],2)."</td>";
			$tab1.="</tr>";
			
				@$ttotalrp+=$bar['totalrp'];
				#= bentuk total
				@$tkgwbmasuk+=$bar['kgmasuk'];
				@$tkgwbkeluar+=$bar['kgkeluar'];
				@$tkgwbbruto+=$bar['kgbruto'];
				@$tkgwbpotongan+=$bar['kgpotongan'];
				@$tkgwbnetto+=$bar['kgnetto'];
				@$tkgnettoadjust+=$bar['kgnettoadjust'];
				@$trpadjust+=$bar['rpadjust'];
				@$tjumrpadjust+=$bar['jumrpadjust'];#=AC
		}
		
		
		$tab1.="<tr class=rowheader bgcolor=#D3D3D3>";
			$tab1.="<td align=center colspan=7>".$_SESSION['lang']['total']."</td>";
			$tab1.="<td align=right>".@hidezerodecimal($tkgwbmasuk,2)."</td>";
			$tab1.="<td align=right>".@hidezerodecimal($tkgwbkeluar,2)."</td>";
			$tab1.="<td align=right>".@hidezerodecimal($tkgwbbruto,2)."</td>";
			$tab1.="<td align=right>".@hidezerodecimal($tkgwbpotongan,2)."</td>";
			$tab1.="<td align=right>".@hidezerodecimal($tkgwbnetto,2)."</td>";
			$tab1.="<td></td>";
			$tab1.="<td></td>";
			$tab1.="<td></td>";
			$tab1.="<td></td>";
			$tab1.="<td></td>";
			$tab1.="<td></td>";
			$tab1.="<td id=ttotalrp align=right>".@hidezerodecimal($ttotalrp,2)."</td>";
			$tab1.="<td></td>";
			$tab1.="<td></td>";
			$tab1.="<td></td>";
			$tab1.="<td></td>";
			$tab1.="<td align=right>".hidezerodecimal($tkgnettoadjust)."</td>";
			$tab1.="<td align=right>".hidezerodecimal($trpadjust)."</td>";
			$tab1.="<td align=right>".hidezerodecimal($tjumrpadjust)."</td>";
		$tab1.="</tr>";
		
		$tab1.="</table>";
		$tab1.="<br>";
		
		$tab1.="<table border=0>";
			$tab1.="<tr class=rowcontent>";
				$tab1.="<td colspan=26>".$keteranganht."</td>";
			$tab1.="</tr>";
		$tab1.="</table>";
		
		$tab1.="<br>";
		
		$span='6';
		$tab1.="<table border=0>";
			$tab1.="<tr>";
				$tab1.="<td colspan=6></td>";
				$tab1.="<td colspan=5>Disetujui Oleh,</td>";
				$tab1.="<td colspan=5>Diperiksa Oleh,</td>";
				$tab1.="<td colspan=4>Dibuat Oleh,</td>";
			$tab1.="</tr>";
			for($i=1;$i<=3;$i++){
				$tab1.="<tr>";
					$tab1.="<td></td>";
				$tab1.="</tr>";
			}
			$tab1.="<tr>";
				$tab1.="<td colspan=6></td>";
				$tab1.="<td colspan=5><u>".ucwords(strtolower($nmkaryawan[$disetujui]))."</u></td>";
				$tab1.="<td colspan=5><u>".ucwords(strtolower($nmkaryawan[$diperiksa]))."</u></td>";
				$tab1.="<td colspan=4><u>".ucwords(strtolower($nmkaryawan[$dibuat]))."</u></td>";
			$tab1.="</tr>";
			$tab1.="<tr>";
				$tab1.="<td colspan=6></td>";
				$tab1.="<td colspan=5>".ucwords(strtolower($namajabatan[$kodejabatan[$disetujui]]))."</td>";
				$tab1.="<td colspan=5>".ucwords(strtolower($namajabatan[$kodejabatan[$diperiksa]]))."</td>";
				$tab1.="<td colspan=4>".ucwords(strtolower($namajabatan[$kodejabatan[$dibuat]]))."</td>";
			$tab1.="</tr>";

			
		$tab1.="</table>";
		$tab1.="<br>";
		
		
		#===================================
		#===================================
		#===================================
		#===================================
		
			
			$nop = "Printout_excel.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("data", $tab1);
			$xls->headers($nop);
			echo $xls->buildFile();
			
	break;
}

function generatenotransaksitbs(){
	global $dbname;
    global $owlPDO;
    global $param;
	
	$tahun=explode('-',tanggalsystemn($param['tanggal']));
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(notransaksi) as nomor from ".$dbname.".pmn_tbs where unit='".$param['unit']."' and tanggal like '".$tahun."%' ";
	$res=fetchdata($str);
	$nomor=$res[0]['nomor'];
	if($nomor==''){
		$nourut=1;
	}else{
		$explnotran=explode('/',$res[0]['nomor']);
		$nourut=$explnotran[0]+1;
	}
	
	$tbs="TBS";
	if($param['jenisx']=='EXT'){
		$tbs="TBSEXT";
	}
	if($param['jenisx']=='PLS' || $param['jenisx']=='KUD'){
		$tbs="TBSKUD";
	}
	if($param['jenisx']=='AFI'){
		$tbs="TBSAFI";
	}
	if($param['jenisx']=='INT'){
		$tbs="TBSINT";
	}
		
	$noba=addZero($nourut,4)."/".$tbs."/".$param['unit']."/".romawi($bulan)."/".$tahun;
	
	return $noba;
}

?>