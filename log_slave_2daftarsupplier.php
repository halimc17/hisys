<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$namasupplier=checkPostGet('namasupplier','');
$tipe=checkPostGet('tipe','');
$kdkelompok=checkPostGet('kdkelompok','');

$str = "select distinct(tipe) ,kode from ".$dbname.".log_5klsupplier";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$arrkel = array();
while ($bar = $res->fetch()) {
   $arrkel[$bar->tipe]=$bar->kode;
}
//print_r($arrkel);
if($proses=='excel'){
	$border=1;
	$stream="<table><tr>
			<td>".$_SESSION['lang']['daftarsupplier']."</td>
			</tr></table>";
}else{
	$border=0;
	$stream='';
}

$stream.="<table cellspacing=1 cellpadding=5 border='".$border."' class=sortable>
		<thead style='font-weight:bold'>
		<tr class=rowheader>
			<th rowspan=2 style='text-align:center;'>No</th>
			<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['id']."</th>
			<th rowspan=2 style='text-align:center;width:10px'>&nbsp;</th>
			<th colspan=16 style='text-align:center;'>INFORMASI PERUSAHAAN</th>
			<th rowspan=2 style='text-align:center;width:10px'>&nbsp;</th>
			<th colspan=6 style='text-align:center;'>INFORMASI KONTAK</th>
			<th rowspan=2 style='text-align:center;width:10px'>&nbsp;</th>
			<th colspan=5 style='text-align:center;'>DATA BANK 1</th>
			<th rowspan=2 style='text-align:center;width:10px'>&nbsp;</th>
			<th colspan=5 style='text-align:center;'>DATA BANK 2</th>
			<th rowspan=2 style='text-align:center;width:10px'>&nbsp;</th>
			<th colspan=5 style='text-align:center;'>DATA BANK 3</th>
			<th rowspan=2 style='text-align:center;width:10px'>&nbsp;</th>
			<th colspan=8 style='text-align:center;'>DOKUMEN LEGAL</th>
			<th rowspan=2 style='text-align:center;width:10px'>&nbsp;</th>
			<th rowspan=2 style='text-align:center;'>KETERANGAN</th>
			<th rowspan=2 style='text-align:center'>NPWP</th>
			<th rowspan=2 style='text-align:center'>PKP</th>
			<th rowspan=2 style='text-align:center'>KTP</th>
		</tr>
		<tr>
			<th style='text-align:center'>Badan Usaha</th>
			<th style='text-align:center'>Nama Perusahaan</th>
			<th style='text-align:center'>Jenis Perusahaan</th>
			<th style='text-align:center'>Kelompok 1</th>
			<th style='text-align:center'>Kelompok 2</th>
			<th style='text-align:center'>Kelompok 3</th>
			<th style='text-align:center'>Kelompok 4</th>
			<th style='text-align:center'>Lokasi</th>
			<th style='text-align:center'>Nama Pemilik / Presiden</th>

			<th style='text-align:center;width:10px'>&nbsp;</th>
			
			<th style='text-align:center'>Alamat Perusahaan</th>
			<th style='text-align:center'>Kota</th>
			<th style='text-align:center'>Propinsi</th>
			<th style='text-align:center'>Kode Pos</th>
			<th style='text-align:center'>Negara</th>
			<th style='text-align:center'>Telepon Utama</th>
			<th style='text-align:center'>Telepon Kedua</th>
			<th style='text-align:center'>Fax</th>
			<th style='text-align:center'>Website Perusahaan</th>
			
			<th style='text-align:center'>Nama Personil</th>
			<th style='text-align:center'>Telepon</th>
			<th style='text-align:center'>No. Extn</th>
			<th style='text-align:center'>Telepon Genggam</th>
			<th style='text-align:center'>Email Koresponden</th>
			<th style='text-align:center'>Email Konfirmasi Pembayaran</th>
			
			<th style='text-align:center'>Nama Bank</th>
			<th style='text-align:center'>Cabang</th>
			<th style='text-align:center'>Nama Rekening</th>
			<th style='text-align:center'>Nomor Rekening</th>
			<th style='text-align:center'>Mata Uang</th>
			
			<th style='text-align:center'>Nama Bank</th>
			<th style='text-align:center'>Cabang</th>
			<th style='text-align:center'>Nama Rekening</th>
			<th style='text-align:center'>Nomor Rekening</th>
			<th style='text-align:center'>Mata Uang</th>
			
			<th style='text-align:center'>Nama Bank</th>
			<th style='text-align:center'>Cabang</th>
			<th style='text-align:center'>Nama Rekening</th>
			<th style='text-align:center'>Nomor Rekening</th>
			<th style='text-align:center'>Mata Uang</th>
			
			<th style='text-align:center'>Akta</th>
			<th style='text-align:center'>SKDU</th>
			<th style='text-align:center'>SIUP</th>
			<th style='text-align:center'>thP</th>
			<th style='text-align:center'>Surat Izin UU Gangguan</th>
			
		</tr>
		</thead><tbody>";
$where="";
if($tipe!=''){
	$where.=" and badanusaha='".$tipe."'";
}
if($kdkelompok!=''){
	$where.=" and supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='".$kdkelompok."')";
}
		
$arrSupplier = array();
$arrdata = array();
$arrrek = array();
$str="select * from ".$dbname.".log_5supplier where namasupplier like '%".$namasupplier."%' ".$where." order by namasupplier asc";
$res=fetchdata($str);
foreach($res as $key=>$val){
	$arrSupplier[$val['supplierid']] = $val['supplierid'];
	$arrdata[$val['supplierid']]['namasupplier'] = $val['namasupplier'];
	$arrdata[$val['supplierid']]['badanusaha'] = $val['badanusaha'];
	$arrdata[$val['supplierid']]['jenisusaha'] = "N/A";
	$arrdata[$val['supplierid']]['namapemilik'] = $val['namapemilik'];
	
	$strx="select * from ".$dbname.".log_5supnpwp where supplierid='".$val['supplierid']."' limit 1";
	$resx=fetchdata($strx);
	$arrdata[$val['supplierid']]['npwp'] = $resx[0]['npwp'];
	
	$strx="select * from ".$dbname.".log_5supalamat where supplierid='".$val['supplierid']."' order by id_alamat asc limit 1";
	$resx=fetchdata($strx);
	$arrdata[$val['supplierid']]['lokasi'] = $resx[0]['kota'];
	$arrdata[$val['supplierid']]['alamatperusahaan'] = $resx[0]['alamat'];
	$arrdata[$val['supplierid']]['kota'] = $resx[0]['kota'];
	$arrdata[$val['supplierid']]['provinsi'] = $resx[0]['provinsi'];
	$arrdata[$val['supplierid']]['kodepos'] = $resx[0]['kodepos'];
	$arrdata[$val['supplierid']]['negara'] = $resx[0]['negara'];
	$arrdata[$val['supplierid']]['teleponutama'] = $resx[0]['telepon'];
	$arrdata[$val['supplierid']]['teleponkedua'] = $resx[0]['teleponlain'];
	$arrdata[$val['supplierid']]['fax'] = $resx[0]['fax'];
	$arrdata[$val['supplierid']]['website'] = $resx[0]['website'];
	$arrdata[$val['supplierid']]['namapersonil'] = $resx[0]['kontakperson'];
	$arrdata[$val['supplierid']]['teleponkontak'] = $resx[0]['teleponkontak'];
	$arrdata[$val['supplierid']]['extm'] = $resx[0]['extm'];
	$arrdata[$val['supplierid']]['telepongenggam'] = $resx[0]['telepongenggam'];
	$arrdata[$val['supplierid']]['emailkoresponden'] = $resx[0]['email_koresponden'];
	$arrdata[$val['supplierid']]['emailkonfirmasi'] = $resx[0]['email_konfirmasi'];
	
	$strx="select * from ".$dbname.".log_5rekbank where supplierid='".$val['supplierid']."'";
	$resx=fetchdata($strx);
	$no=0;
	foreach($resx as $keyx=>$valx){
		$optnamabank = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',"kodebank='".$valx['idbank']."'");
		$arrrek[$val['supplierid']][$no]['namabank'] = $optnamabank[$valx['idbank']];
		$arrrek[$val['supplierid']][$no]['cabang'] = $valx['cabang'];
		$arrrek[$val['supplierid']][$no]['an'] = $valx['an'];
		$arrrek[$val['supplierid']][$no]['rekening'] = $valx['rekening'];
		$arrrek[$val['supplierid']][$no]['matauang'] = $valx['matauang'];
		$no++;
	}
	
	$strx="select * from ".$dbname.".log_5supkelompok where supplierid='".$val['supplierid']."'";
	$resx=fetchdata($strx);
	$xyz=0;
	foreach($resx as $keyx=>$valx){
		$xyz+=1;
		$arrdata[$val['supplierid']]['tipe'.$xyz]=$arrkel[$valx['tipe']]." ";
	}
	
	$strx="select * from ".$dbname.".log_fileupload where supplierid='".$val['supplierid']."'";
	$resx=fetchdata($strx);
	foreach($resx as $keyx=>$valx){
		if($valx['idlampiran']=='1'){
			$arrdata[$val['supplierid']]['akta'] = "Ya";
		}
		if($valx['idlampiran']=='2'){
			$arrdata[$val['supplierid']]['skdu'] = "Ya";
		}
		if($valx['idlampiran']=='3'){
			$arrdata[$val['supplierid']]['siup'] = "Ya";
		}
		if($valx['idlampiran']=='4'){
			$arrdata[$val['supplierid']]['tdp'] = "Ya";
		}
		if($valx['idlampiran']=='5'){
			$arrdata[$val['supplierid']]['si'] = "Ya";
		}
		if($valx['idlampiran']=='7'){
			$arrdata[$val['supplierid']]['pkp'] = "Ya";
		}
		if($valx['idlampiran']=='8'){
			$arrdata[$val['supplierid']]['ktp'] = "Ya";
		}
	}
}

if(count($arrSupplier)==0){
	$stream = "<tr class=rowcontent>
		<td>".$_SESSION['lang']['datanotfound']."</td>
	</tr>";
}else{
	foreach($arrSupplier as $key){
		$no++;
		$stream.="<tr class=rowcontent style='text-align:center'>
			<td>".$no."</td>
			<td>".$key."</td>
			<td></td>
			<td>".$arrdata[$key]['badanusaha']."</td>
			<td>".$arrdata[$key]['namasupplier']."</td>
			<td>".$arrdata[$key]['jenisusaha']."</td>
			<td>".$arrdata[$key]['tipe1']."</td>
			<td>".$arrdata[$key]['tipe2']."</td>
			<td>".$arrdata[$key]['tipe3']."</td>
			<td>".$arrdata[$key]['tipe4']."</td>
			<td>".$arrdata[$key]['lokasi']."</td>
			<td>".$arrdata[$key]['namapemilik']."</td>
			<td></td>
			<td>".$arrdata[$key]['alamatperusahaan']."</td>
			<td>".$arrdata[$key]['kota']."</td>
			<td>".$arrdata[$key]['provinsi']."</td>
			<td>".$arrdata[$key]['kodepos']."</td>
			<td>".$arrdata[$key]['negara']."</td>
			<td>".$arrdata[$key]['teleponutama']."</td>
			<td>".$arrdata[$key]['teleponkedua']."</td>
			<td>".$arrdata[$key]['fax']."</td>
			<td>".$arrdata[$key]['website']."</td>
			<td></td>
			<td>".$arrdata[$key]['namapersonil']."</td>
			<td>".$arrdata[$key]['teleponkontak']."</td>
			<td>".$arrdata[$key]['extm']."</td>
			<td>".$arrdata[$key]['telepongenggam']."</td>
			<td>".$arrdata[$key]['emailkoresponden']."</td>
			<td>".$arrdata[$key]['emailkonfirmasi']."</td>
			<td></td>";
			
			for($i=0;$i<=2;$i++){
				$stream.="<td>".$arrrek[$key][$i]['namabank']."</td>";
				$stream.="<td>".$arrrek[$key][$i]['cabang']."</td>";
				$stream.="<td>".$arrrek[$key][$i]['an']."</td>";
				$stream.="<td>".$arrrek[$key][$i]['rekening']."</td>";
				$stream.="<td>".$arrrek[$key][$i]['matauang']."</td>";
				$stream.="<td></td>";
			}
			
		$stream.="<td>".$arrdata[$key]['akta']."</td>
			<td>".$arrdata[$key]['skdu']."</td>
			<td>".$arrdata[$key]['siup']."</td>
			<td>".$arrdata[$key]['tdp']."</td>
			<td>".$arrdata[$key]['si']."</td>
			<td>".$arrdata[$key]['npwp']."</td>
			<td>".$arrdata[$key]['pkp']."</td>
			<td>".$arrdata[$key]['ktp']."</td>
			<td></td>
			<td></td>
		</tr>";
	}
	$stream.="</tbody>";
}

switch($proses)
{
    case'preview':
            echo $stream;    
	break;
       
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_="Supplier_List_".date('YmdHis');
             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
             gzwrite($gztralala, $stream);
             gzclose($gztralala);
             echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls.gz';
                </script>";            
        break;

    default:
        break;
}

?>