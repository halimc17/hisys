<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$kdBrgRep=checkPostGet('kdBrgRep','');
$kdOrgRep=checkPostGet('kdOrgRep','');
$kodeorg=checkPostGet('kodeorg','');
$tanggal=checkPostGet('tanggal','');
$kodebarang=checkPostGet('kodebarang','');
$tgl1Rep=tanggalsystemn(checkPostGet('tgl1Rep',''));
$tgl2Rep=tanggalsystemn(checkPostGet('tgl2Rep',''));

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');



if(($proses=='preview')or($proses=='excel')or($proses=='pdf'))
{
    if(($tgl1Rep=='')or($tgl2Rep==''))
	{
		echo"Error: Tanggal tidak boleh kosong"; 
		exit;
    }

    else if($tgl1Rep>$tgl2Rep)
	{
        echo"Error: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
		exit;
    }
	
}//bgcolor=#CCCCCC
    if($proses=='excel'){
		$stream="<table cellspacing='1' border='1' class='sortable' >";
	}else{
		$stream="<table cellspacing='1' border='0' class='sortable' width=80%>";
	}
		$stream.="<thead class=rowheader>
        <tr>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['gudang']."</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>    
			<td align=center>".$_SESSION['lang']['kodebarang']."</td>
			<td align=center>".$_SESSION['lang']['namabarang']."</td>
			<td align=center>".$_SESSION['lang']['saldoawal']."</td>
			<td align=center>".$_SESSION['lang']['masuk']."</td>
			<td align=center>".$_SESSION['lang']['keluar']."</td>
			<td align=center>".$_SESSION['lang']['saldoakhir']."</td>    
			<td align=center>".$_SESSION['lang']['keterangan']."</td>
        </tr></thead>
      <tbody>";
$iList="SELECT sum(masuk) as masuk, sum(keluar) as keluar, kodeorg, tanggal, tanggaljam, kodebarang, keterangan FROM ".$dbname.".vhc_stokbarangbekas WHERE kodeorg='".$kdOrgRep."' and "
        . " kodebarang like '%".$kdBrgRep."%' and tanggal between '".$tgl1Rep."' and '".$tgl2Rep."'  group by kodebarang, tanggal  order by kodebarang asc, tanggal asc ";
$nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
$nList->setFetchMode(PDO::FETCH_ASSOC);
$no=0;
while($dList=$nList->fetch()){
    $stream.="<tr class=rowcontent style=cursor:pointer onclick=detail('".$dList['kodeorg']."','".$dList['tanggal']."','".$dList['kodebarang']."')>";
    $no+=1;
    $stream.="
        <td align=center>".$no."</td>
		<td align=left>".$nmOrg[$dList['kodeorg']]."</td>
		<td align=left>".tanggalnormal($dList['tanggal'])."</td>
		<td align=left>".$dList['kodebarang']."</td>
		<td align=left>".$nmBrg[$dList['kodebarang']]."</td>";
		
		$str="select sum(masuk-keluar) as sawal from ".$dbname.".vhc_stokbarangbekas where kodebarang='".$dList['kodebarang']."' and tanggal<'".$dList['tanggal']."' and kodeorg='".$dList['kodeorg']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$sawal=$bar['sawal']+$dList['masuk'];
		$sakhir=$bar['sawal']+$dList['masuk']-$dList['keluar'];
	
	$stream.="<td align=right>".number_format($sawal)."</td>
		<td align=right>".number_format($dList['masuk'])."</td>
		<td align=right>".number_format($dList['keluar'])."</td>
		<td align=right>".number_format($sakhir)."</td>
		<td align=left>".$dList['keterangan']."</td>
        </tr>";		

	}

	$stream.="</tbody></table>";

	//detail
	$tab="<table class=sortable cellspacing=1 border=0 width=100%>
		 <thead>
			 <tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['gudang']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>    
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center>".$_SESSION['lang']['saldoawal']."</td>
				<td align=center>".$_SESSION['lang']['masuk']."</td>
				<td align=center>".$_SESSION['lang']['keluar']."</td>
				<td align=center>".$_SESSION['lang']['saldoakhir']."</td>    
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
			 </tr>
		</thead>
		<tbody>";
	
	$str="select * from ".$dbname.".vhc_stokbarangbekas where kodeorg ='".$kodeorg."' and kodebarang = '".$kodebarang."' and tanggal = '".$tanggal."' order by tanggaljam asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($dList=$res->fetch()){
	$no+=1;
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=left>".$nmOrg[$dList['kodeorg']]."</td>";
		$tab.="<td align=left>".tanggalnormal($dList['tanggal'])."</td>";
		$tab.="<td align=left>".$dList['kodebarang']."</td>";
		$tab.="<td align=left>".$nmBrg[$dList['kodebarang']]."</td>";
		
		$str="select sum(masuk-keluar) as sawal from ".$dbname.".vhc_stokbarangbekas where kodebarang='".$dList['kodebarang']."' and tanggaljam<'".$dList['tanggaljam']."' and kodeorg='".$dList['kodeorg']."'";
		$resv=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$resv->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$resv->fetch();
		$sawal=$bar['sawal']+$dList['masuk'];
		$sakhir=$bar['sawal']+$dList['masuk']-$dList['keluar'];
		
		$tab.="<td align=right>".number_format($sawal)."</td>";
		$tab.="<td align=right>".number_format($dList['masuk'])."</td>";
		$tab.="<td align=right>".number_format($dList['keluar'])."</td>";
		$tab.="<td align=right>".number_format($sakhir)."</td>";
		$tab.="<td align=left>".$dList['keterangan']."</td>";
		
		$tab.="</tr>";
	}
	$tab.="</tbody></table>";
#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses){
	case 'detail':
		echo $tab;
    break;
	
	case 'preview':
		echo $stream;
    break;

	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_stok_barang_bekas ".$tglSkrg;
		if(strlen($stream)>0)
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
			if(!fwrite($handle,$stream))
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
	break;
		
	

	
	
	default:
	break;
}

?>