<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$tanggalmulai = tanggalsystem(checkPostGet('tanggalmulai', ''));
$tanggalakhir = tanggalsystem(checkPostGet('tanggalakhir', ''));
$proses = checkPostGet('proses', '');
$kdBrg = checkPostGet('kdBrg', '');
$idPabrik = checkPostGet('idPabrik', '');

function dates_inbetween($date1, $date2){
    $day = 60*60*24;
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between
    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);
    for($x = 1; $x < $days_diff; $x++)
        {
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }
    $dates_array[] = date('Y-m-d',$date2);
    return $dates_array;
}

if(($tanggalmulai!='')&&($tanggalakhir!=''))
{	
    $tgl1=$tanggalmulai;
    $tgl2=$tanggalakhir;
}
$tanggal = dates_inbetween($tgl1, $tgl2);

$sOrg="select namaorganisasi, kodeorganisasi from ".$dbname.".organisasi where induk ='".$idPabrik."' and (tipe!='HOLDING' or tipe!='KANWIL')";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($res=$qOrg->fetch()){
	$kdorganisasi=$res['kodeorganisasi'];
}

$sOrg="select kodeorg, tanggal, tbsmasuknetto, tbsdiolahnetto, sisatbskemarinnetto, sisahariininetto,oer, oerpk 
	  from ".$dbname.".pabrik_produksi where kodeorg='".$kdorganisasi."' and tanggal between ".$tanggalmulai." and ".$tanggalakhir." order by tanggal asc ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($res=$qOrg->fetch()){
	// $kodeorg[$res['kodeorg']]=$res['kodeorg'];
	// $tbsmasuk[$res['kodeorg'].$res['tanggal']]=$res['tbsmasuknetto'];
	// $tbsdiolah[$res['kodeorg'].$res['tanggal']]=$res['tbsdiolahnetto'];
	// $tbslalu[$res['kodeorg'].$res['tanggal']]=$res['sisatbskemarinnetto'];
	// $tbsini[$res['kodeorg'].$res['tanggal']]=$res['sisahariininetto'];

	$arrnilai[$res['kodeorg']]['40000003'][$res['tanggal']][0] = $res['sisatbskemarinnetto'];
	$arrnilai[$res['kodeorg']]['40000003'][$res['tanggal']][1] = $res['tbsmasuknetto'];
	$arrnilai[$res['kodeorg']]['40000003'][$res['tanggal']][2] = $res['tbsdiolahnetto'];
	$arrnilai[$res['kodeorg']]['40000003'][$res['tanggal']][3] = $res['sisahariininetto'];
	$arrnilai[$res['kodeorg']]['40000001'][$res['tanggal']][1] = $res['oer'];
	$arrnilai[$res['kodeorg']]['40000002'][$res['tanggal']][1] = $res['oerpk'];
}

$sOrg="select sum(beratbersih) as netto, left(tanggal,10) as tanggal,millcode,kodebarang  from ".$dbname.".pabrik_timbangan where millcode='".$kdorganisasi."' and tanggal between ".$tanggalmulai." and ".$tanggalakhir." group by left(tanggal,10),kodebarang,kodeorg order by tanggal asc ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($res=$qOrg->fetch()){
	$arrnilai[$res['millcode']][$res['kodebarang']][$res['tanggal']][2] = $res['netto'];
}

$sOrg="select saldoawal, produksi, penjualan, sisa, tanggal, kodeorg, kodebarang  from ".$dbname.".pabrik_stokbarang where kodeorg='".$kdorganisasi."' and tanggal between '".tglkemarin($tanggalmulai)."' and '".$tanggalakhir."' order by tanggal asc ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($res=$qOrg->fetch()){
	// $exptgl = substr($res['tanggal'],0,4)."-".substr($res['tanggal'],5,2)."-".substr($res['tanggal'],8,2);
	// $tgltr = strtotime ('+1 day', strtotime($exptgl)) ;
	// $tgltr = date ( 'Y-m-d' , $tgltr );
	$tgltr = tglbesok($res['tanggal']);
	$arrnilai[$res['kodeorg']][$res['kodebarang']][$tgltr][0] = $res['saldoawal'];
	$arrnilai[$res['kodeorg']][$res['kodebarang']][$tgltr][1] = $res['produksi'];
	$arrnilai[$res['kodeorg']][$res['kodebarang']][$tgltr][2] = $res['penjualan'];
	$arrnilai[$res['kodeorg']][$res['kodebarang']][$tgltr][4] = $res['sisa'];
}

$sOrg="select sum(a.kuantitas) as saldoawal, sum(a.kernelquantity) as saldoawal2, a.tanggal, a.kodeorg, b.komoditi  from ".$dbname.".pabrik_masukkeluartangki a left join ".$dbname.".pabrik_5tangki b on a.kodetangki=b.kodetangki and b.kodeorg='".$kdorganisasi."' where a.kodeorg='".$kdorganisasi."' and a.tanggal between ".tglkemarin($tanggalmulai)." and ".$tanggalakhir." group by a.tanggal,a.kodeorg,b.komoditi order by a.tanggal asc ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($res=$qOrg->fetch()){
	// $exptgl = substr($res['tanggal'],0,4)."-".substr($res['tanggal'],5,2)."-".substr($res['tanggal'],8,2);
	// $tgltr = strtotime ('+1 day', strtotime($exptgl)) ;
	// $tgltr = date ( 'Y-m-d' , $tgltr );
	$tgltr = tglbesok($res['tanggal']);
	$arrnilai[$res['kodeorg']][($res['komoditi']=='CPO'?'40000001':'40000002')][$tgltr][0] = 
	($res['komoditi']=='CPO'?$res['saldoawal']:$res['saldoawal2']);
}

$arrProduk = array();
$arrKet = array();
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang in ('40000001', '40000002', '40000003', '40000005') and kodebarang like '%".$kdBrg."%'";
$qOrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rBrg=$qOrg->fetch())
{
	$arrProduk[$rBrg['kodebarang']]['kodebarang'] = $rBrg['kodebarang'];
	$arrProduk[$rBrg['kodebarang']]['namabarang'] = $rBrg['namabarang'];
	if($rBrg['kodebarang']=='40000003')
	{
		$arrKet[$rBrg['kodebarang']][] = "Restan Lalu";
		$arrKet[$rBrg['kodebarang']][] = "Penerimaan";
		$arrKet[$rBrg['kodebarang']][] = "Olah";
		$arrKet[$rBrg['kodebarang']][] = "Restan Hari Ini";
	}
	else
	{
		$arrKet[$rBrg['kodebarang']][] = "Stok Awal";
		$arrKet[$rBrg['kodebarang']][] = "Produksi";
		$arrKet[$rBrg['kodebarang']][] = "Pengeluaran";
		$arrKet[$rBrg['kodebarang']][] = "Penyusutan";
		$arrKet[$rBrg['kodebarang']][] = "Stok Akhir";
	}
}

if (($tanggalakhir - $tanggalmulai) < 0) {
    echo " Gagal: Periksa kembali periode tanggal, Tanggal akhir lebih kecil dari tanggal mulai.";
} else {
    if ($proses=='preview' || $proses=='excel') {

    	$stream="";
	    if($proses=='excel'){
	        $border=1;
	        $colatas=count($tanggal)+8;
	        $stream.="<table border='0'>
	        <tr>
	        <td colspan='".$colatas."' align=center>".strtoupper("Overtime Recapitulation")." : ".$idPabrik." (".$kdorganisasi.")</td>
	        </tr>
	        <tr>
	        <td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tanggalmulai)." s.d ". tanggalnormal($tanggalakhir)."</td>
	        </tr></table>";
	    }else{
	       $border=0; 
	       $stream="";
	    }

	    // preview: nampilin header ================================================================================
	    $stream.="<table cellspacing='1' border='".$border."' class='sortable'  >
	    <thead class=rowheader>
	    <tr>
	    <td align=center colspan=2>QUANTITY - STOCK BARANG JADI</td>";
	    
	    foreach($tanggal as $tgl )
	    {
	        $day=date('D', strtotime($tgl));
	        if($day=='Sun'){
	            $stream.="<td width=5px align=center ><font color=red>".substr($tgl,8,2)."</font></td>"; 
	        }else {
	            $stream.="<td width=5px align=center >".substr($tgl,8,2)."</td>"; 
	        }
	    }

	    $stream.="<td align=center >".$_SESSION['lang']['jumlah']."</td>";
	    $stream.="</tr></thead><tbody>";
		
	    
	    if(isset($arrProduk))
		foreach($arrProduk as $key=>$val)
		{
			$stream.="<tr class=rowcontent>";
			$stream.="<td colspan=".(count($tanggal)+3)."><b>".$val['namabarang']."</b></td>";
			$stream.="</tr>";
			
			$no = 0;
			foreach($arrKet[$val['kodebarang']] as $key2)
			{
				$stream.="<tr class=rowcontent>";
				$stream.="<td style='width:10px'></td>";
				$stream.="<td>".$key2."</td>";
				
				foreach($tanggal as $tgl )
			    {

		            $stream.="<td width=5px align=right >".number_format($arrnilai[$kdorganisasi][$val['kodebarang']][$tgl][$no],2)."</td>"; 

		            $arrnilai[$kdorganisasi][$val['kodebarang']][$tgl][4]=$arrnilai[$kdorganisasi][$val['kodebarang']][$tgl][0]+$arrnilai[$kdorganisasi][$val['kodebarang']][$tgl][1]-$arrnilai[$kdorganisasi][$val['kodebarang']][$tgl][2];

		         	$total[$kdorg][$val['kodebarang']][$no]+=$arrnilai[$kdorganisasi][$val['kodebarang']][$tgl][$no];
			    }
				
				
				$stream.="<td align=right>".number_format($total[$kdorg][$val['kodebarang']][$no],2)."</td>";
				$stream.="</tr>";
				$no++;
			}
		}

	    // echo $stream;

		if ($proses=='excel'){

	    	$nop_="Lap.Stokbarangjadi"."_".$idPabrik;
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
	    } else{
	        echo $stream;
	    }

    }
}

?>