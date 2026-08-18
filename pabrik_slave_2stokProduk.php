<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$kdBrgRep=checkPostGet('kdBrgRep','');
$kdOrgRep=checkPostGet('kdOrgRep','');
$tgl1Rep=tanggalsystemn(checkPostGet('tgl1Rep',''));
$tgl2Rep=tanggalsystemn(checkPostGet('tgl2Rep',''));

// if(($proses=='excel')or($proses=='pdf'))
// {	
    // $kdBrgRep=$_GET['kdBrgRep'];
    // $kdOrgRep=$_GET['kdOrgRep'];
    // $tgl1Rep=tanggalsystemn($_GET['tgl1Rep']);
    // $tgl2Rep=tanggalsystemn($_GET['tgl2Rep']);
// }

$optnmor=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjnvhc=makeOption($dbname, 'vhc_5jenisvhc','jenisvhc,namajenisvhc');
$optnmbar=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$optnamacostumer=makeOption($dbname,'log_5supplier','supplierid,namasupplier');


$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PABRIK'");
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang','kelompokbarang=400');



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
		$stream="<div class='table-scroll'><table cellspacing='1' border='0' class='sortable' width=80%>";
	}
		$stream.="<thead class=rowheader>
        <tr>
            <th align=center>No</th>
            <th align=center>".$_SESSION['lang']['pabrik']."</th>
            <th align=center>".$_SESSION['lang']['tanggal']."</th>    
            <th align=center>".$_SESSION['lang']['namabarang']."</th>
            <th align=center>".$_SESSION['lang']['saldoawal']."</th>
            <th align=center>".$_SESSION['lang']['produksi']."</th>
            <th align=center>".$_SESSION['lang']['jmlhPakai']."</th>
            <th align=center>".$_SESSION['lang']['penjualan']."</th>
            <th align=center>".$_SESSION['lang']['sisa']."</th>    
            <th align=center>".$_SESSION['lang']['keterangan']."</th>
        </tr></thead></div>
      <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr
$iList=" 	SELECT * FROM ".$dbname.".pabrik_stokbarang WHERE kodeorg='".$kdOrgRep."' and "
        . " kodebarang='".$kdBrgRep."' and tanggal between '".$tgl1Rep."' and '".$tgl2Rep."'   order by tanggal ";
//echo $iList;
$nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
$nList->setFetchMode(PDO::FETCH_ASSOC);
$no=0;
while($dList=$nList->fetch())
{
                    //$stream.="<tr bgcolor=#FFFFFF>";
    $stream.="<tr class=rowcontent>";
    $no+=1;
    $stream.="
        <td align=center>".$no."</td>
        <td>".$nmOrg[$dList['kodeorg']]."</td>
        <td align=center>".tanggalnormal($dList['tanggal'])."</td>
        <td>".$nmBrg[$dList['kodebarang']]."</td>
        <td align=right>".number_format($dList['saldoawal'],2)."</td>
        <td align=right>".number_format($dList['produksi'],2)."</td>
        <td align=right>".number_format($dList['pemakaian'],2)."</td>
        <td align=right>".number_format($dList['penjualan'],2)."</td>
        <td align=right>".number_format($dList['sisa'],2)."</td>    
        <td align=left>".$dList['keterangan']."</td> 
        </tr>";		

	}

	$stream.="</tbody></table>";


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
	case 'preview':
		echo $stream;
    break;

	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_stok_barang_lain ".$tglSkrg;
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