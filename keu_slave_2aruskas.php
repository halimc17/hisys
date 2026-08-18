<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=checkPostGet('pt','');
$proses=checkPostGet('proses','');
$gudang=checkPostGet('gudang','');
$periode=checkPostGet('periode','');
$nextperiode= date("Y-m",strtotime("+1 Month",strtotime($periode)));
$tglhariini=tanggalsystemn(checkPostGet('tanggal',''));


## Filter data
$whr="";
$whrbank="";
if ($gudang=='') {
	$whr=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
	$whrbank=" and a.pemilik in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}else{
	$whr=" and a.kodeorg='".$gudang."'";
	$whrbank=" and a.pemilik='".$gudang."'";
}


## Inisialisasi array
$arrlist=array();
$noakun=array();
$rekbank=array();
$arrflow=array();
$datefilter=array();

## Cek apakah PDO periode tersebut sudah terposting atau belum
$str="select distinct posting, kodeorg, left(postingtime,10) as tanggalpost from ".$dbname.".keu_pdoht a where periode='".$periode."' ".$whr."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	// if ($bar['posting']==1) {
	// 	$datefilter[$bar['kodeorg']]['periode']=substr($bar['tanggalpost'],0,7);
	// 	$datefilter[$bar['kodeorg']]['ending']=$bar['tanggalpost'];
	// }else{
		$datefilter[$bar['kodeorg']]['periode']=substr($tglhariini,0,7);
		$datefilter[$bar['kodeorg']]['ending']=$tglhariini;		
	//}
}

## Ambil data Cash Flow
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='CASHFLOWV2' order by nourut ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrflow[$bar['nourut']]['nourut']=$bar['nourut'];
	$arrflow[$bar['nourut']]['tipe']=$bar['tipe'];
	$arrflow[$bar['nourut']]['ket']=$bar['keterangandisplay'];
	$arrflow[$bar['nourut']]['colspan']=$bar['variableoutput'];
	$arrflow[$bar['nourut']]['noarustotal']=$bar['noakundisplay'];
}


											###############################
											####Kas Besar dan Kas Kecil####
											###############################

## Ambil noakun kas besar dan kas kecil
$str="select a.noakun,a.kodeorg,b.namaakun from ".$dbname.".`keu_kasbankht` a left join ".$dbname.".`keu_5akun` b on a.noakun=b.noakun where a.noakun!='1110101' ".$whr." group by a.kodeorg, a.noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrlist[$bar['kodeorg']][$bar['noakun']]['namaakun']=$bar['namaakun'];
	$arrlist[$bar['kodeorg']][$bar['noakun']]['noakun']=$bar['noakun'];
	$noakun[$bar['noakun']]=$bar['noakun'];
}
foreach($datefilter as $kdorg => $arrdate){
	
	$bulan=substr($arrdate['periode'], 5,2);
	$persaldo=str_replace('-', '', $arrdate['periode']);
	$tglbeginning=$arrdate['periode']."-01";

	# Ambil saldo awal
	$str="select kodeorg,noakun,awal".$bulan." as sawal from ".$dbname.".keu_saldobulanan a where noakun in ('".implode("','",$noakun)."') and periode='".$persaldo."' and kodeorg='".$kdorg."'";
	//exit('warning'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$arrlist[$bar['kodeorg']][$bar['noakun']]['sawal']=$bar['sawal'];
	}

	# Ambil rupiah transaksi masuk dan keluar
	$str="select kodeorg,noakun,sum(jumlah) as jumlah,tipetransaksi from ".$dbname.".keu_kasbankht a where noakun in ('".implode("','",$noakun)."') and rekening ='' and tanggal>='".$tglbeginning."' and tanggal<='".$arrdate['ending']."' and kodeorg='".$kdorg."' group by kodeorg,noakun,tipetransaksi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$arrlist[$bar['kodeorg']][$bar['noakun']][$bar['tipetransaksi']]=$bar['jumlah'];
	}
}

## Ambil rupiah actual berdasarkan transaksi kasbank
$str="select sum(jumlah) as jumlah,a.kodeorg,nourut,noakun2a as noakun from ".$dbname.".keu_kasbankdtht_vw a left join ".$dbname.".`keu_5mesinlaporandt_akun` b on a.noaruskas=b.noakun  or a.noakun=b.noakun
 where noakun2a in ('".implode("','",$noakun)."') and namalaporan='CASHFLOWV2' and left(tanggal,7)='".$nextperiode."' ".$whr." and posting=1 group by kodeorg,noakun2a,nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrlist[$bar['kodeorg']][$bar['noakun']][$bar['nourut']]['actual']=$bar['jumlah'];
}

## Ambil rupiah estimasi berdasarkan transaksi PDO
$str="select sum(rupiah) as jumlah,a.kodeorg,b.nourut,noakunkas as noakun from ".$dbname.".keu_pdo_vw a left join ".$dbname.".`keu_5mesinlaporandt_akun` b on a.noakun=b.noakun 
 where noakunkas in ('".implode("','",$noakun)."') and namalaporan='CASHFLOWV2' and left(tanggal,7)='".$periode."' ".$whr." group by a.kodeorg,noakunkas,b.nourut";
 //exit('warning'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrlist[$bar['kodeorg']][$bar['noakun']][$bar['nourut']]['estimate']=$bar['jumlah'];
}

											################################
											############# End ##############
											################################





											################################
											######### Rekening Bank#########
											################################

# Ambil rekening bank
$str="select a.noakun,a.pemilik,a.rekening,b.namabank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank 
where 1=1 ".$whrbank." order by a.pemilik,b.namabank ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrlist[$bar['pemilik']][$bar['noakun']]['namaakun']=$bar['namabank'];
	$arrlist[$bar['pemilik']][$bar['noakun']]['noakun']=$bar['rekening'];
	$rekbank[$bar['noakun']]=$bar['noakun'];
}

foreach($datefilter as $kdorg => $arrdate){
	
	$bulan=substr($arrdate['periode'], 5,2);
	$persaldo=str_replace('-', '', $arrdate['periode']);
	$tglbeginning=$arrdate['periode']."-01";

	# Ambil saldo awal
	$str="select kodeorg,norek as noakun,awal".$bulan." as sawal from ".$dbname.".keu_saldobank a where norek in ('".implode("','",$rekbank)."') and periode='".$persaldo."' and kodeorg='".$kdorg."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$arrlist[$bar['kodeorg']][$bar['noakun']]['sawal']=$bar['sawal'];
	}

	# Ambil rupiah transaksi masuk dan keluar
	$str="select kodeorg,rekening as noakun,sum(jumlah) as jumlah,tipetransaksi from ".$dbname.".keu_kasbankht a where rekening in ('".implode("','",$rekbank)."') and rekening !='' and tanggal>='".$tglbeginning."' and tanggal<='".$arrdate['ending']."' and kodeorg='".$kdorg."' group by kodeorg,rekening,tipetransaksi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$arrlist[$bar['kodeorg']][$bar['noakun']][$bar['tipetransaksi']]=$bar['jumlah'];
	}
}

# Ambil rupiah actual berdasarkan transaksi kasbank
$str="select sum(jumlah) as jumlah,a.kodeorg,nourut,rekening as noakun from ".$dbname.".keu_kasbankdtht_vw a left join ".$dbname.".`keu_5mesinlaporandt_akun` b on a.noaruskas=b.noakun  or a.noakun=b.noakun
where rekening in ('".implode("','",$rekbank)."') and rekening !='' and namalaporan='CASHFLOWV2' and left(tanggal,7)='".$nextperiode."' ".$whr." and posting=1 group by kodeorg,rekening,nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrlist[$bar['kodeorg']][$bar['noakun']][$bar['nourut']]['actual']=$bar['jumlah'];
}

# Ambil rupiah estimasi berdasarkan transaksi PDO
$str="select sum(rupiah) as jumlah,a.kodeorg,b.nourut,rekeningbank as noakun from ".$dbname.".keu_pdo_vw a left join ".$dbname.".`keu_5mesinlaporandt_akun` b on a.noakun=b.noakun
where rekeningbank in ('".implode("','",$rekbank)."') and rekeningbank !='' and namalaporan='CASHFLOWV2' and left(tanggal,7)='".$periode."' ".$whr." and posting=1 group by a.kodeorg,rekeningbank,b.nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrlist[$bar['kodeorg']][$bar['noakun']][$bar['nourut']]['estimate']=$bar['jumlah'];
}

											################################
											############# End ##############
											################################

// echo "<pre>";
// print_r($arrlist);
// echo "</pre>";
// exit('warning : ');

## Hitung Total
foreach($arrflow as $flow){
	if($flow['noarustotal']!=''){
		## explode noaruskas
		$arrdata=explode(',',$flow['noarustotal']);
		foreach($arrdata as $key){
			foreach ($arrlist as $unit => $listakun) {
				foreach ($listakun as $akun => $data) {
			 		$arrlist[$unit][$akun][$flow['nourut']]['estimate']+=$data[$key]['estimate'];
			 		$arrlist[$unit][$akun][$flow['nourut']]['actual']+=$data[$key]['actual'];
				}
			}
		}
	}
}

$border=0;
if ($proses=='excel') {
	$border=1;
}

## Display Data
$display.="<table class=sortable cellspacing=1 border=".$border.">
			<thead>
				<tr>
					<td align=center rowspan=2>CASH POSITION</td>";
					foreach ($arrlist as $unit => $listakun) {
						$colspan[$unit]=count($arrlist[$unit])*2;
						$display.="<td colspan='".$colspan[$unit]."' align='center'>".$unit."</td>";
					}
	$display.="</tr>
				<tr>";
					foreach ($arrlist as $unit => $listakun) {
						foreach ($listakun as $akun => $data) {
							$display.="<td colspan='2' align='center'>".$data['namaakun']." <br>".$data['noakun']."</td>";
						}
					}
	$display.="</tr> 
			</thead>";
	   
	$display.="<tr class=rowcontent>
					<td>Cash-on-hand (Beginning Balance)</td>";
					foreach ($arrlist as $unit => $listakun) {
						foreach ($listakun as $akun => $data) {
							$display.="<td colspan='2' align='right'>".number_format($data['sawal'],2)." </td>";
						}
					}
	$display.="</tr>";
	   
	$display.="<tr class=rowcontent>
					<td>Cash-on-hand (Ending Balance)</td>";
					foreach ($arrlist as $unit => $listakun) {
						foreach ($listakun as $akun => $data) {
							$data['salak']=$data['sawal']+$data['M']-$data['K'];
							$display.="<td colspan='2' align='right'>".number_format($data['salak'],2)." </td>";
						}
					}
	$display.="</tr>";

foreach ($arrflow as $flow) {

	$display.="<tr class=rowcontent >";

	## Tampilan Header detail
	if ($flow['colspan']==3) {
	    $bg="bgcolor=#66CCFF";
	    $display.="<td ".$bg."><b>".$flow['ket']."</b></td>";

	    foreach ($arrlist as $unit => $listakun) {
			foreach ($listakun as $akun => $data) {
				$display.="<td ".$bg." align='center'><b>Estimate</b></td>";
	 			$display.="<td ".$bg." align='center'><b>Actual</b></td>";
			}
		}
	}

	## Tampilan Sub Header detail dan Total
	if ($flow['colspan']==2) {
	    $bg="bgcolor=#CCCCFF";
		$display.="<td ".$bg."><b>".$flow['ket']."</b></td>";
		foreach ($arrlist as $unit => $listakun) {
			foreach ($listakun as $akun => $data) {
				$display.="<td align='right' ".$bg."><b>".number_format($data[$flow['nourut']]['estimate'],2)."</b></td>";
		 		$display.="<td align='right' ".$bg."><b>".number_format($data[$flow['nourut']]['actual'],2)."</b></td>";
			}
		}
		
	}

	## Tampilan Detail
	if ($flow['colspan']==1) {
		$display.="<td>".$flow['ket']."</td>";		
		foreach ($arrlist as $unit => $listakun) {
			foreach ($listakun as $akun => $data) {
				$display.="<td align='right'>".number_format($data[$flow['nourut']]['estimate'],2)."</td>";
		 		$display.="<td align='right'>".number_format($data[$flow['nourut']]['actual'],2)."</td>";
			}
		}
	}
	
	$display.="</tr>";
}

$display.="</table>";
	 
switch ($proses) {
    case 'preview':
        echo $display;
    break;

    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "Aruskas ".$gudang." Periode ".$periode;
        if (strlen($display) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $display)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
    break;		
}

?>