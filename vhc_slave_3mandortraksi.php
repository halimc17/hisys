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
$karyid    = checkPostGet('karyid','');
$tglmulai  = tanggalsystemn(checkPostGet('tglmulai',''));
$tglakhir  = tanggalsystemn(checkPostGet('tglakhir',''));
$tgl1      = $tglmulai;
$tgl2      = $tglakhir;

$baris     = checkPostGet('baris','');
$mandor    = checkPostGet('mandor','');
$tgl       = checkPostGet('tgl','');


$unitlist          =checkPostGet('unitlist','');
$afdlist           =checkPostGet('afdlist','');
$prdlist           =checkPostGet('prdlist','');


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
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['unit']."' and tipe='TRAKSI'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$optafd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
	echo $optafd;
break;
case'delete':
	try {
		$owlPDO->beginTransaction();

		$str = "delete from ".$dbname.".vhc_premimandortraksi where 1=1 and kodeorg ='".$unit."' and karyawanid='".$karyid."' and periode='".$prd."'";
		$owlPDO->exec($str);

	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
break;
case'posting':
		try {
		$owlPDO->beginTransaction();

		$str = "update ".$dbname.".vhc_premimandortraksi set posting='1',postingby='".$_SESSION['standard']['userid']."',postingtime='". date('Y-m-d H:i:s')."' where 1=1 and kodeorg ='".$unit."' and karyawanid='".$karyid."' and periode='".$prd."'";
		$owlPDO->exec($str);

		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
break;

case'unposting':

	#cek apakah periode gaji sudah di tutup
		$str = "select * from ".$dbname.".sdm_5periodegaji where periode like '" . $prd. "' and kodeorg='".$unit."' ";
		$res=fetchdata($str);
		$jumdata=0;
		foreach($res as $bar){
			$jumdata+=$bar['sudahproses'];
		}

		if($jumdata>0){
			throw new PDOException("Periode Gaji " . $prd. " untuk Unit ".$unit." sudah di tutup.");
		}

		try {
		$owlPDO->beginTransaction();

		$str = "update ".$dbname.".vhc_premimandortraksi set posting='0',updateby = '".$_SESSION['standard']['userid']."' where 1=1 and kodeorg ='".$unit."' and karyawanid='".$karyid."' and periode='".$prd."'";
		$owlPDO->exec($str);

		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}

break;

case'loaddata':

	$wh = "";
	$where.= "and kodeorg in (".getOrgDetail(2).")";

	if($prdlist!=''){
		$where.=" and periode = '".$prdlist."'";
	}
	if($unitlist!=''){
		$where.=" and kodeorg='".$unitlist."'";
	}
	if($afdlist!=''){
		$wh.=" and divisi like '%".$afdlist."%'";
	}


	$limit = 15;
	$page = 0;
	$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
	if (isset($_POST['page'])) {
		$page = intval($_POST['page']);
		if ($page < 0)
			$page = 0;
	}

	$sql="select * from ".$dbname.".vhc_premimandortraksi ";
	$res = fetchData($sql);
	foreach($res as $bar){	
		$totalPremi[$bar['karyawanid']][$bar['periode']] += $bar['premi'];
	}

	$sql="select * from ".$dbname.".vhc_premimandortraksi where 1=1 ".$where."  group by karyawanid,periode order by periode desc";
	$res = fetchData($sql);
	$jlhbrs = count($res);
	if($jlhbrs==0){
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=15 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
		$stream.="</tr>";
	}	

	foreach($res as $bar){	
		$nokar++;
		$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$nokar."</td>";
			$stream.="<td align=center>".$bar['kodeorg']." - ".getNamaOrg($bar['kodeorg'])."</td>";
			$stream.="<td align=center>".$bar['periode']."</td>";
			$stream.="<td>".getNik($bar['karyawanid'])."</td>";
			$stream.="<td>".getNamaKaryawan($bar['karyawanid'])."</td>";
			$stream.="<td align=right>".number_format($totalPremi[$bar['karyawanid']][$bar['periode']],2)."</td>";
			$stream.="<td align=center>".getNamaKaryawan($bar['updateby'])."</td>";
			$stream.="<td align=center>".$bar['updatetime']."</td>";
			$stream.="<td align=center>".getNamaKaryawan($bar['postingby'])."</td>";
			$stream.="<td align=center>".$bar['postingtime']."</td>";


			if($bar['posting'] == '0'){
				$stream.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['periode']."','".$bar['karyawanid']."','".$bar['kodeorg']."');\" ></td>";
				
				$stream.="<td align=center width=20px><img src=images/icons/04/16/01.png class=zImgBtn  title='Posting' onclick=\"posting('".$bar['periode']."','".$bar['karyawanid']."','".$bar['kodeorg']."');\" ></td>";
			}else{
				$stream.="<td></td>";
				$stream.="<td align=center width=20px><img src=images/icons/04/16/04.png class=zImgBtn  title='Unposting' onclick=\"unposting('".$bar['periode']."','".$bar['karyawanid']."','".$bar['kodeorg']."');\" ></td>";

			}
		$stream.="</tr>";
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
	$stream.="</tr><tr><td colspan=15 align=center>";
	if ($page == '0') {
		$stream.="<button class=mybutton disabled=true>Prev</button>";
	} else {
		$stream.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
	}
	$stream.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

	if (($page + 1) == $totrows) {
		$stream.="<button class=mybutton disabled=true>Next</button>";
	} else {
		$stream.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
	}
	$stream.="</td>
		</tr>";



	echo $stream;


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
	$str="select max(sudahproses) as prd from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' and periode='".$prd."' ";
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


    ## Ambil Setup Kegiatan Mandor
    $str="select * from ".$dbname.".vhc_5premikegiatanmandor where pt = '".getindukPT($unit)."'";
    $res=fetchdata($str);
    if(count($res) > 0 ){
        foreach($res as $bar){
            $kegiatanSetup[$bar['kodekegiatan']] = $bar['kodekegiatan'];
            $rupiahSatuan = $bar['rupiah'];
        }
    }else{  
        exit("Warning : Setup kegiatan mandor traksi tidak ada, silahkan setup terlebih dahulu" );
    }

    $str='';$whereTanggal='';
    $whereTanggal =" and tanggal between '".$tgl1."' and '".$tgl2."'";
    $whereKegiatan = "and jenispekerjaan in ('".implode("','",$kegiatanSetup)."') ";
    $str="select * from ".$dbname.".vhc_rundt_vw where kodeorg = '".$unit."' and mandor !='0000000000' ".$whereTanggal." ".$whereKegiatan." order by tanggal";
    $res=fetchdata($str);
    foreach($res as $bar){
        $dtmandor[$bar['mandor']]=$bar['mandor'];
        $listkar[$bar['mandor']][$bar['tanggal']]=$bar['tanggal'];
        $totalAngkutan[$bar['mandor']][$bar['tanggal']]+=$bar['beratmuatan'];
    }

    if(empty($dtmandor)){
        exit("Warning : Data mandor tidak ada, pastikan di pekerjaan sudah terinput nama mandor.");
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
                $tab.="<th colspan=6 align=left bgcolor=#CCCCCC align=center>MANDOR TRAKSI : <b>[".getNik($mandor)."] ".$nmkar[$mandor]."</b></th>"; 
                $tab.="<th hidden id=mandor".$no.">".$mandor."</th>";
            $tab.="</tr>";
            $tab.="<tr class=rowheader>";
                $tab.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
                $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
                $tab.="<th align=center width=100px>Total Angkutan (Kg)</th>";
                $tab.="<th align=center width=50px>Rp/Kg</th>";
                $tab.="<th align=center width=70px>".$_SESSION['lang']['total']." pendapatan</th>";
            $tab.="</tr>";
            $tab.="</thead>";
            
            $nokar=0;
            foreach($tanggal as $tgl){
                $nokar++;
                $tab.="<tr class=rowcontent id=baris".$no."_".$nokar.">";
                    $tab.="<td  width=50px align=center>".$nokar."</td>";
                    $tab.="<td  width=50px align=center id=tgl_".$no."_".$nokar.">".$tgl."</td>";
                    $tab.="<td align=right id=hasilkerja_".$no."_".$nokar.">".@numb_format($totalAngkutan[$mandor][$tgl])."</td>";

                    if($totalAngkutan[$mandor][$tgl] > 0){
                        $hargRpKg = $rupiahSatuan;
                    }else{
                        $hargRpKg = 0;
                    }

                    $tab.="<td  width=50px align=right id=harga_".$no."_".$nokar.">".@numb_format($hargRpKg,2)."</td>";

                    $totalPendapatan[$mandor][$tgl] = $hargRpKg * $totalAngkutan[$mandor][$tgl];
                    $tab.="<td  width=50px align=right id=premi_".$no."_".$nokar.">".@numb_format($totalPendapatan[$mandor][$tgl],2)."</td>";
                    
                    $gtAngkutan[$mandor] += $totalAngkutan[$mandor][$tgl];
                    $gtPendapatan[$mandor] += $totalPendapatan[$mandor][$tgl];
            }

            $tab.="<tr class=rowcontent>";
                $tab.="<td align=center colspan=2><b>Total Premi</b></td>";
                $tab.="<td align=right>".@numb_format($gtAngkutan[$mandor],2)."</td>";
                $tab.="<td align=right></td>";
                $tab.="<td align=right>".@numb_format($gtPendapatan[$mandor],2)."</td>";
            $tab.="</tr>";
            $tab.="</tbody></table><br>";
        }
    }
    
    $tab.="<button class=mybutton onclick=saveAll('1','1','".$no."','".$nokar."');>".$_SESSION['lang']['proses']."</button>";
    echo $tab;
break;
case'savedata':
	try {
		$owlPDO->beginTransaction();

		#cek posting
		$str = "select * from ".$dbname.".vhc_premimandortraksi where `kodeorg`='".$unit."' and `karyawanid`='".$mandor."' and `periode`='".$prd."' and posting='1'";
		$res=fetchdata($str);
		if(count($res)>0){
			throw new PDOException("Transaksi Sudah di POSTING.");
		}

		#cek apakah periode gaji sudah di tutup
		$str = "select * from ".$dbname.".sdm_5periodegaji where periode like '" . $prd. "' and kodeorg='".$unit."' ";
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
			$str="delete from ".$dbname.".vhc_premimandortraksi where `kodeorg`='".$unit."' and `karyawanid`='".$mandor."' and `periode`='".$prd."' "; 
			$owlPDO->exec($str);
		}
		
		if($tgl==''){
            $tgl=$tgl1;
        }


        $param['hasilkerja']         =str_replace(',','',$param['hasilkerja']);
		$param['harga']              =str_replace(',','',$param['harga']);
		$param['premi']              =str_replace(',','',$param['premi']);
        
		#insert
		$data = array(
			'kodeorg'      => $unit,
			'periode'      => $prd,
			'tanggal'      => $tgl,
			'karyawanid'   => $mandor,
			'hasilkerja'   => $param['hasilkerja']  ,
			'harga'        => $param['harga']   ,
			'premi'        => $param['premi']  ,
			'posting'      => '0',
			'updateby'     => $_SESSION['standard']['userid']
		);

		$cols = array();
		foreach($data as $key=>$row) {
				$cols[] = $key;
		}

		$str = insertQuery($dbname,'vhc_premimandortraksi',$data,$cols);
		if($param['premi'] > 0 || $param['premi'] !=''){
			$owlPDO->exec($str);			
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