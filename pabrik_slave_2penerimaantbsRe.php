<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
error_reporting(0);
$proses   = checkPostGet('proses','');
$tipeIntex= checkPostGet('tipeIntexRe','');
$unit     = checkPostGet('unitRe','');
$tgl_1    = checkPostGet('tglRe','');
$tanggl   = checkPostGet('tglRe','');
$kdPabrik = checkPostGet('kdPabrikRe','');

$kdpbr    = checkPostGet('kdpbr','');
$tgl1     = checkPostGet('tgl1','');
$type     = checkPostGet('type','');

$kdunit   = checkPostGet('kdunit','');
if($proses=='preview1')
{
	if($type=='html')
	{
		$border = 0;
	}
	else
	{
		$border = 1;
	}
	
	$tab = "<table class=sortable cellpadding=5 cellspacing=1 border=".$border.">
		<thead>
		<tr class=rowheader>
			<th align=center rowspan=2><b>No</b></th>
			<th align=center rowspan=2 style='min-width:100px;'><b>Estate</b></th>
			<th align=center colspan=24><b>JAM MASUK BUAH</b></th>
			<th align=center rowspan=2><b>NETTO<br>Kg</b></th>
			<th align=center rowspan=2><b>JANJANG</b></th>			
			<th align=center rowspan=2><b>RITASE</b></th>
		</tr>
		<tr class=rowheader>
			
			<th align=center style='min-width:50px;font-size:11px'><b>00.00-00.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>01.00-01.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>02.00-02.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>03.00-03.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>04.00-04.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>05.00-05.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>06.00-06.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>07.00-07.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>08.00-08.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>09.00-09.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>10.00-10.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>11.00-11.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>12.00-12.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>13.00-13.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>14.00-14.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>15.00-15.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>16.00-16.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>17.00-17.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>18.00-18.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>19.00-19.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>20.00-20.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>21.00-21.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>22.00-22.59</b></th>
			<th align=center style='min-width:50px;font-size:11px'><b>23.00-23.59</b></th>
			
		</tr>
		</thead>
		<tbody>";
	
	$tanggal = $tgl1;
	$tanggal = explode('-',$tanggal);
	$tanggal = $tanggal[2]."-".$tanggal[1]."-".$tanggal[0];
	
	//Get Estate
	$pt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$kdpbr."'");
	$arrEstate = array();
	// $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt[$kdpbr]."' and tipe='KEBUN' order by namaorganisasi";
    if ($kdpbr != '') {
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select kodeorg from ".$dbname.".pabrik_timbangan where millcode='".$kdpbr."' and kodebarang='40000003' and tanggal like '".$tanggal."%') and tipe='KEBUN' order by namaorganisasi";
    } else {
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select kodeorg from ".$dbname.".pabrik_timbangan where kodebarang='40000003' and tanggal like '".$tanggal."%') and tipe='KEBUN' order by namaorganisasi";
    }
    
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$arrEstate[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
		$arrB[] = $bar['kodeorganisasi'];
	}
	$arrEstate['SWADAYA'] = 'SWADAYA';

	$arrB = implode("','",$arrB);
	//Get Divisi
	$arrDivisi = array();
	$str="select induk,kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk IN ('".$arrB."') and tipe='AFDELING' order by namaorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$arrDivisi[$bar['kodeorganisasi']]['UNIT'] = $bar['induk'];
		$arrDivisi[$bar['kodeorganisasi']]['NAMA'] = $bar['namaorganisasi'];
	}
	
	
	$nmsupp = makeOption($dbname,'log_5suptimbangan_vw','kodetimbangan,namasupplier');
	$str = "select * from ".$dbname.".pabrik_timbangan where millcode='".$kdpbr."' and kodebarang='40000003' and tanggal like '".$tanggal."%' and kodecustomer!='' and kodecustomer!='0'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$arrDivisi[$bar['kodecustomer']]['UNIT'] = 'SWADAYA';
		$arrDivisi[$bar['kodecustomer']]['NAMA'] = $nmsupp[$bar['kodecustomer']];
	}
	
	
	//Get Transaksi Timbang TBS
	$arrTrans = array();
	$arrTotal = array();
	$arrSubTotal = array();

    $millcode = '';
    if($kdpbr!='') {
        $millcode = "and millcode='".$kdpbr."'";
    }

	$str="select * from ".$dbname.".pabrik_timbangan where kodebarang='40000003' and tanggal like '".$tanggal."%' ".$millcode."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		if($bar['divcode']==''){
			$bar['divcode']=$bar['kodecustomer'];
		}

		$jamkeluar = substr($bar['tanggal'],11,8);
		$jamkeluar = str_replace(':','',$jamkeluar);;
		
		$arrTotal[$bar['divcode']]['NETTO'] = $arrTotal[$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
		$arrTotal[$bar['divcode']]['JJG'] = $arrTotal[$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
		$arrTotal[$bar['divcode']]['COUNT'] = $arrTotal[$bar['divcode']]['COUNT'] + 1;
		if($jamkeluar <= 10000)
		{
			$arrTrans[1][$bar['divcode']]['NETTO'] = $arrTrans[1][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[1][$bar['divcode']]['JJG'] = $arrTrans[1][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[1][$bar['divcode']]['COUNT'] = $arrTrans[1][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 10000 && $jamkeluar <= 15959)
		{
			$arrTrans[2][$bar['divcode']]['NETTO'] = $arrTrans[2][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[2][$bar['divcode']]['JJG'] = $arrTrans[2][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[2][$bar['divcode']]['COUNT'] = $arrTrans[2][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 20000 && $jamkeluar <= 25959)
		{
			$arrTrans[3][$bar['divcode']]['NETTO'] = $arrTrans[3][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[3][$bar['divcode']]['JJG'] = $arrTrans[3][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[3][$bar['divcode']]['COUNT'] = $arrTrans[3][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 30000 && $jamkeluar <= 35959)
		{
			$arrTrans[4][$bar['divcode']]['NETTO'] = $arrTrans[4][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[4][$bar['divcode']]['JJG'] = $arrTrans[4][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[4][$bar['divcode']]['COUNT'] = $arrTrans[4][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 40000 && $jamkeluar <= 45959)
		{
			$arrTrans[5][$bar['divcode']]['NETTO'] = $arrTrans[5][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[5][$bar['divcode']]['JJG'] = $arrTrans[5][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[5][$bar['divcode']]['COUNT'] = $arrTrans[5][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 50000 && $jamkeluar <= 55959)
		{
			$arrTrans[6][$bar['divcode']]['NETTO'] = $arrTrans[6][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[6][$bar['divcode']]['JJG'] = $arrTrans[6][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[6][$bar['divcode']]['COUNT'] = $arrTrans[6][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 60000 && $jamkeluar <= 65959)
		{
			$arrTrans[7][$bar['divcode']]['NETTO'] = $arrTrans[7][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[7][$bar['divcode']]['JJG'] = $arrTrans[7][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[7][$bar['divcode']]['COUNT'] = $arrTrans[7][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 70000 && $jamkeluar <= 75959)
		{
			$arrTrans[8][$bar['divcode']]['NETTO'] = $arrTrans[8][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[8][$bar['divcode']]['JJG'] = $arrTrans[8][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[8][$bar['divcode']]['COUNT'] = $arrTrans[8][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 80000 && $jamkeluar <= 85959)
		{
			$arrTrans[9][$bar['divcode']]['NETTO'] = $arrTrans[9][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[9][$bar['divcode']]['JJG'] = $arrTrans[9][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[9][$bar['divcode']]['COUNT'] = $arrTrans[9][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 90000 && $jamkeluar <= 95959)
		{
			$arrTrans[10][$bar['divcode']]['NETTO'] = $arrTrans[11][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[10][$bar['divcode']]['JJG'] = $arrTrans[11][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[10][$bar['divcode']]['COUNT'] = $arrTrans[11][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 100000 && $jamkeluar <= 105959)
		{
			$arrTrans[11][$bar['divcode']]['NETTO'] = $arrTrans[11][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[11][$bar['divcode']]['JJG'] = $arrTrans[11][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[1][$bar['divcode']]['COUNT'] = $arrTrans[11][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 110000 && $jamkeluar <= 115959)
		{
			$arrTrans[12][$bar['divcode']]['NETTO'] = $arrTrans[12][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[12][$bar['divcode']]['JJG'] = $arrTrans[12][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[12][$bar['divcode']]['COUNT'] = $arrTrans[12][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 120000 && $jamkeluar <= 125959)
		{
			$arrTrans[13][$bar['divcode']]['NETTO'] = $arrTrans[13][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[13][$bar['divcode']]['JJG'] = $arrTrans[13][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[13][$bar['divcode']]['COUNT'] = $arrTrans[13][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 130000 && $jamkeluar <= 135959)
		{
			$arrTrans[14][$bar['divcode']]['NETTO'] = $arrTrans[14][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[14][$bar['divcode']]['JJG'] = $arrTrans[14][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[14][$bar['divcode']]['COUNT'] = $arrTrans[14][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 140000 && $jamkeluar <= 145959)
		{
			$arrTrans[15][$bar['divcode']]['NETTO'] = $arrTrans[15][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[15][$bar['divcode']]['JJG'] = $arrTrans[15][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[15][$bar['divcode']]['COUNT'] = $arrTrans[15][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 150000 && $jamkeluar <= 155959)
		{
			$arrTrans[16][$bar['divcode']]['NETTO'] = $arrTrans[16][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[16][$bar['divcode']]['JJG'] = $arrTrans[16][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[16][$bar['divcode']]['COUNT'] = $arrTrans[16][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 160000 && $jamkeluar <= 165959)
		{
			$arrTrans[17][$bar['divcode']]['NETTO'] = $arrTrans[17][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[17][$bar['divcode']]['JJG'] = $arrTrans[17][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[17][$bar['divcode']]['COUNT'] = $arrTrans[17][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 170000 && $jamkeluar <= 175959)
		{
			$arrTrans[18][$bar['divcode']]['NETTO'] = $arrTrans[18][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[18][$bar['divcode']]['JJG'] = $arrTrans[18][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[18][$bar['divcode']]['COUNT'] = $arrTrans[18][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 180000 && $jamkeluar <= 185959)
		{
			$arrTrans[19][$bar['divcode']]['NETTO'] = $arrTrans[19][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[19][$bar['divcode']]['JJG'] = $arrTrans[19][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[19][$bar['divcode']]['COUNT'] = $arrTrans[19][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 190000 && $jamkeluar <= 195959)
		{
			$arrTrans[20][$bar['divcode']]['NETTO'] = $arrTrans[20][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[20][$bar['divcode']]['JJG'] = $arrTrans[20][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[20][$bar['divcode']]['COUNT'] = $arrTrans[20][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 200000 && $jamkeluar <= 205959)
		{
			$arrTrans[21][$bar['divcode']]['NETTO'] = $arrTrans[21][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[21][$bar['divcode']]['JJG'] = $arrTrans[21][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[21][$bar['divcode']]['COUNT'] = $arrTrans[21][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 210000 && $jamkeluar <= 215959)
		{
			$arrTrans[22][$bar['divcode']]['NETTO'] = $arrTrans[22][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[22][$bar['divcode']]['JJG'] = $arrTrans[22][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[22][$bar['divcode']]['COUNT'] = $arrTrans[22][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 220000 && $jamkeluar <= 225959)
		{
			$arrTrans[23][$bar['divcode']]['NETTO'] = $arrTrans[23][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[23][$bar['divcode']]['JJG'] = $arrTrans[23][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[23][$bar['divcode']]['COUNT'] = $arrTrans[23][$bar['divcode']]['COUNT'] + 1;
		}
		if($jamkeluar > 230000 && $jamkeluar <= 235959)
		{
			$arrTrans[24][$bar['divcode']]['NETTO'] = $arrTrans[24][$bar['divcode']]['NETTO'] + ($bar['beratbersih']-$bar['kgpotsortasi']);
			$arrTrans[24][$bar['divcode']]['JJG'] = $arrTrans[24][$bar['divcode']]['JJG'] + $bar['jumlahtandan1'];
			$arrTrans[24][$bar['divcode']]['COUNT'] = $arrTrans[24][$bar['divcode']]['COUNT'] + 1;
		}




		

		
	}
	
	$subTotal = array();
	$grandTotal = array();
	foreach ($arrEstate as $key=>$val)
	{
		$tab.="<tr class=rowcontent>
			<td colspan='29' style='font-weight:bold'>".$val."</td>
		</tr>";
		$no = 0;
		foreach($arrDivisi as $keyDiv=>$valDiv)
		{
			if($key==$valDiv['UNIT'])
			{
				$no++;
				$tab.="<tr class=rowcontent>
					<td align=center rowspan=2>".$no."</td>
					<td>".$valDiv['NAMA']."</td>
					<td align=center>".number_format($arrTrans[1][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[2][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[3][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[4][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[5][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[6][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[7][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[8][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[9][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[10][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[11][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[12][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[13][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[14][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[15][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[16][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[17][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[18][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[19][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[20][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[21][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[22][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[23][$keyDiv]['NETTO'])."</td>
					<td align=center>".number_format($arrTrans[24][$keyDiv]['NETTO'])."</td>
					<td align=center rowspan=2>".number_format($arrTotal[$keyDiv]['NETTO'])."</td>
					<td align=center rowspan=2>".number_format($arrTotal[$keyDiv]['JJG'])."</td>
					<td align=center rowspan=2>".number_format($arrTotal[$keyDiv]['COUNT'])."</td>
				</tr>
				<tr class=rowcontent>
					
					<td align=center>R A T E</td>
					<td align=center>".$arrTrans[1][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[2][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[3][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[4][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[5][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[6][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[7][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[8][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[9][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[10][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[11][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[12][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[13][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[14][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[15][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[16][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[17][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[18][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[19][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[20][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[21][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[22][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[23][$keyDiv]['COUNT']."</td>
					<td align=center>".$arrTrans[24][$keyDiv]['COUNT']."</td>

				</tr>";
				for($i=1;$i<=24;$i++)
				{
					$subTotal[$i][$key] = $subTotal[$i][$key] + $arrTrans[$i][$keyDiv]['NETTO'];
				}
				$grandTotal['NETTO'] = $grandTotal['NETTO'] + $arrTotal[$keyDiv]['NETTO'];
				$grandTotal['JJG'] = $grandTotal['JJG'] + $arrTotal[$keyDiv]['JJG'];
				$grandTotal['COUNT'] = $grandTotal['COUNT'] + $arrTotal[$keyDiv]['COUNT'];
				
				$subTotal[$key]['NETTO'] += $arrTotal[$keyDiv]['NETTO'];
				$subTotal[$key]['JJG'] += $arrTotal[$keyDiv]['JJG'];
				$subTotal[$key]['COUNT'] += $arrTotal[$keyDiv]['COUNT'];
			}
		}
		$tab.="<tr class=rowcontent>
			<td colspan='2' style='font-weight:bold'>Total ".$val."</td>";
			for($i=1;$i<=24;$i++)
			{
				$tab.="<td style='font-weight:bold;text-align:center'>".number_format($subTotal[$i][$key])."</td>";
				$grandTotal[$i] = $grandTotal[$i] + $subTotal[$i][$key];
			}
		$tab.="<td style='font-weight:bold;text-align:center'>".number_format($subTotal[$key]['NETTO'])."</td>
			<td style='font-weight:bold;text-align:center'>".number_format($subTotal[$key]['JJG'])."</td>
		<td style='font-weight:bold;text-align:center'>".$subTotal[$key]['COUNT']."</td>
		</tr>
		<tr class=rowcontent>
			<td colspan='29' style='font-weight:bold'>&nbsp;</td>
		</tr>";
	}
	
	$tab.="<tr class=rowcontent>
			<td colspan='2' style='font-weight:bold;text-align:center'>GRAND TOTAL</td>";
			for($i=1;$i<=24;$i++)
			{
				$tab.="<td style='font-weight:bold;text-align:center'>".number_format($grandTotal[$i])."</td>";
			}
	$tab.="<td style='font-weight:bold;text-align:center'>".number_format($grandTotal['NETTO'])."</td>";
	$tab.="<td style='font-weight:bold;text-align:center'>".number_format($grandTotal['JJG'])."</td>";
	$tab.="<td style='font-weight:bold;text-align:center'>".$grandTotal['COUNT']."</td>";
	$tab.="</tr>";
		
	$tab.="</table>";
	
	if($type=='html'){
		echo $tab;
	}else{
		$tab.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
		$nop_="Monitoring_Perjam_TBS_INT_".date('m-d-Y');
		
		if(strlen($tab)>0)
        {
			$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			gzwrite($gztralala, $tab);
			gzclose($gztralala);
			echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls.gz';
				</script>"; 
		}   
	}
}
else
{
$optSupp=makeOption($dbname, 'log_5supplier', 'kodetimbangan,supplierid');
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$XA=  tanggalsystem($tanggl);
$thnA=substr($XA,0,4);
$blnA=substr($XA,4,2);
$tglA=substr($XA,6,2);
if($proses!='getKodeorg'){

for($x=7;$x>=0;$x--){
    $tm=mktime(0,0,0,$blnA,$tglA-$x,$thnA);
    $TGL[]=date('Y-m-d',$tm);
    if($x==7)
      $listTGL="'".date('Y-m-d',$tm)."'";
    else
      $listTGL.=",'".date('Y-m-d',$tm)."'";  
}
//ambil  pt pabrik
$str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($kdPabrik,0,4)."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $ptPks=$bar->induk;
    
}

#ambil  kebun internal;
$str="select kodeorganisasi from ".$dbname.".organisasi where induk='".substr(isset($ptPks)? trim($ptPks): '',0,3)."' and tipe='KEBUN'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $kebunInternal[]=$bar->kodeorganisasi;
}

#ambil kebun afiliasi
$str="select kodeorganisasi from ".$dbname.".organisasi where induk!='".substr(isset($ptPks)? trim($ptPks): '',0,3)."' and tipe='KEBUN'
          and kodeorganisasi not in('CKPE','MEPE')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $kebunAffiliasi[]=$bar->kodeorganisasi;
}
#ambil kebun Plasma
$kebunPlasma=Array('CKPE','MEPE');
#ambil curtommer
$str="select distinct kodecustomer from ".$dbname.".pabrik_timbangan where (kodeorg is null or kodeorg='') 
           and left(tanggal,10) in (".$listTGL.") and kodebarang='40000003'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $costomer[]=$bar->kodecustomer;
}


#ambil tbs internal
$str1="select kodeorg,left(tanggal,10) as tanggal,divcode as afd, sum(beratbersih-kgpotsortasi) as netto
          from ".$dbname.".pabrik_timbangan where millcode like '%".$kdPabrik."%' and length(kodeorg)=4 and
          left(tanggal,10) between '".$thnA."-".$blnA."-01' and '".$thnA."-".$blnA."-".$tglA."' and kodebarang='40000003' group by  left(tanggal,10), divcode
          order by kodeorg, divcode,left(tanggal,10) ";
$resInternal=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$resInternal->setFetchMode(PDO::FETCH_OBJ);
while($bar=$resInternal->fetch())
{
    $tbsInt[$bar->kodeorg][$bar->afd][$bar->tanggal]=$bar->netto;
}
#ambil tbs internal 2 (totalnya)
$str1="select kodeorg,left(tanggal,10) as tanggal,substr(nospb,9,6) as afd, sum(beratbersih-kgpotsortasi) as netto
          from ".$dbname.".pabrik_timbangan where millcode like '%".$kdPabrik."%' and length(kodeorg)=4 and
          left(tanggal,10) between '".$thnA."-".$blnA."-01' and '".$thnA."-".$blnA."-".$tglA."' and kodebarang='40000003' group by  left(tanggal,10),substr(nospb,9,6)
          order by kodeorg, substr(nospb,9,6),left(tanggal,10) ";
$resInternal=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$resInternal->setFetchMode(PDO::FETCH_OBJ);
while($bar=$resInternal->fetch())
{
	setIt($tottbsInt[$bar->kodeorg][$bar->afd],0);
    $tottbsInt[$bar->kodeorg][$bar->afd]+=$bar->netto;
}


#ambil tbs external
$str2="select kodecustomer,left(tanggal,10) as tanggal, sum(beratbersih-kgpotsortasi) as netto
          from ".$dbname.".pabrik_timbangan where millcode like '%".$kdPabrik."%' and (kodeorg is null or kodeorg='') and
          left(tanggal,10) between '".$thnA."-".$blnA."-01' and '".$thnA."-".$blnA."-".$tglA."' and kodebarang='40000003' group by  left(tanggal,10),kodecustomer
          order by left(tanggal,10),kodecustomer";
$resExternal=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$resExternal->setFetchMode(PDO::FETCH_OBJ);
while($bar=$resExternal->fetch())
{
    $tbsExt[$bar->kodecustomer][$bar->tanggal]=$bar->netto;
}
#ambil tbs external 2 (totalnya)
$str2="select kodecustomer,left(tanggal,10) as tanggal, sum(beratbersih-kgpotsortasi) as netto
          from ".$dbname.".pabrik_timbangan where millcode like '%".$kdPabrik."%' and (kodeorg is null or kodeorg='') and
          left(tanggal,10) between '".$thnA."-".$blnA."-01' and '".$thnA."-".$blnA."-".$tglA."' and kodebarang='40000003' group by  left(tanggal,10),kodecustomer
          order by left(tanggal,10),kodecustomer";
$resExternal=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$resExternal->setFetchMode(PDO::FETCH_OBJ);
while($bar=$resExternal->fetch())
{
	setIt($tottbsExt[$bar->kodecustomer],0);
    $tottbsExt[$bar->kodecustomer]+=$bar->netto;
}
#==================================create table
if($proses=='preview')
     $border=0;
else
    $border=1;
if($_SESSION['language']=='EN'){
//$stream="FFB Recieve from ".tanggalnormal($TGL[0])." to ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." <br>
//                 Netto not include deduction";    
$stream="FFB Recieve month ".$blnA." to ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." <br>
                 Netto include deduction";    
}else{
//        $stream="Penerimaan TBS  dari Tanggal ".tanggalnormal($TGL[0])." s/d Tanggal ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." <br>
//                 Berat yang tampil adalah berat bersih (belum dikurangi potongan sortasi)";
        $stream="Penerimaan TBS  Bulan ".$blnA." s/d Tanggal ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." <br>
                 Berat yang tampil adalah berat bersih (sudah dikurangi potongan sortasi)";
}
$stream.=" <table class=sortable cellpadding=5 cellspacing=1 border=".$border.">
                    <thead>
                     <tr class=rowheader><th colspan=3 align=center>".$_SESSION['lang']['tanggal']."</th>";
                    if(!empty($TGL))foreach($TGL as $key=>$tg)
                    {
                        $stream.="<th width=50px align=center>".substr($tg,8,2)."</th>";
                    }
$stream.="<th align=center>".$_SESSION['lang']['total']."</th></tr>
                    </thead>
                  <tbody>";
#inti=========================================================================================
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2 bgcolor=#dedede>A.Internal</td><td colspan=10 bgcolor=#dedede></td></tr>";
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td bgcolor=#dedede></td><td width=30px colspan=2 bgcolor=#dedede>A.1. Inti</td><td colspan=9 bgcolor=#dedede></td></tr>";
if(!empty($kebunInternal))foreach($kebunInternal as $key=>$kodekebun)
{
        if(isset($ttang)) 
                unset($ttang);
                
        // $no=$ttinti=0;
        $no=0;
        if(isset($tbsInt[$kodekebun])){
                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                    $no+=1;
                    $stream.="<tr class=rowcontent>";
                    $stream.="<td></td><td>".$no."</td><td>".$optNm[$afd]."</td>";
                    $tt=0;
                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
                        $bgwarna="";
						setIt($art[$tang],0);
						setIt($ttang[$tang],0);
                        $scek="select distinct * from ".$dbname.".kebun_spb_vw where
                           left(blok,6)='".$afd."' and tanggal='".$tang."' 
                           and substr(nospb,9,6)<>left(blok,6)";
                        
                        $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                        $rcek=owlBaris($qcek);
                        
                        if($rcek==1){
                            $bgwarna="bgcolor=yellow";
                        }
                        $stream.="<td align=right ".$bgwarna.">".number_format($art[$tang])."</td>";
                        $tt+=$art[$tang];
                        $ttang[$tang]+=$art[$tang];
                    }
                    $stream.="<td align=right>".number_format($tottbsInt[$kodekebun][$afd])."</td></tr>";
					setIt($tkebun[$kodekebun],0);
                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                }   
            $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2></td><td bgcolor=#dedede>Total ".$kodekebun."</td>";
                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
				setIt($tinti[$keei],0);
                $tinti[$keei]+=$jum;
            }
            $stream.="<td align=right bgcolor=#dedede>".number_format($tkebun[$kodekebun])."</td></tr>";
            $ttinti+=$tkebun[$kodekebun];
      }     
}
// $ttinternal=$tinti=0;
// $ttinternal=0;
if(!empty($tinti))
{
  
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td></td><td colspan=2 bgcolor=#dedede>Total Inti</td>";        
        if(!empty($tinti))foreach($tinti as $keei=>$jum){
        $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
//        $ttinti+=$jum;
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttinti)."</td></tr>";
    $ttinternal+=$ttinti;
}
#afiliasi====================================================================================
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td bgcolor=#dedede></td><td  colspan=2 bgcolor=#dedede>A.2. Afiliasi</td><td colspan=9 bgcolor=#dedede></td></tr>";
if(!empty($kebunAffiliasi))foreach($kebunAffiliasi as $key=>$kodekebun)
{
        if(isset($ttang)) 
                unset($ttang);
                
        // $no=$ttafiliasi=0;
        $no=0;
        if(isset($tbsInt[$kodekebun])){
                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                    $no+=1;
                    $stream.="<tr class=rowcontent>";
                    $stream.="<td></td><td>".$no."</td><td>".$optNm[$afd]."</td>";
                    $tt=0;
                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
                        $bgwarna="";
						setIt($art[$tang],0);
						setIt($ttang[$tang],0);
                        $scek="select distinct * from ".$dbname.".kebun_spb_vw where
                           left(blok,6)='".$afd."' and tanggal='".$tang."' 
                           and substr(nospb,9,6)<>left(blok,6)";

                        
                        $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                        $rcek=owlBaris($qcek);
                        
                        if($rcek==1){
                            $bgwarna="bgcolor=yellow";
                        }
                        $stream.="<td align=right ".$bgwarna.">".number_format($art[$tang])."</td>";
                        $tt+=$art[$tang];
                        $ttang[$tang]+=$art[$tang];
                    }
                    $stream.="<td align=right>".number_format($tottbsInt[$kodekebun][$afd])."</td></tr>";
					setIt($tkebun[$kodekebun],0);
                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                }   
            $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2></td><td bgcolor=#dedede>Total ".$kodekebun."</td>";
                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
				setIt($tafiliasi[$keei],0);
                $tafiliasi[$keei]+=$jum;
            }
            $stream.="<td align=right bgcolor=#dedede>".number_format($tkebun[$kodekebun])."</td></tr>";
            $ttafiliasi+=$tkebun[$kodekebun];
      }     
}
if(!empty($tafiliasi))
{
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td></td><td colspan=2 bgcolor=#dedede>Total Afiliasi</td>";
        if(!empty($tafiliasi))foreach($tafiliasi as $keei=>$jum){
        $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
//        $ttafiliasi+=$jum;
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttafiliasi)."</td></tr>";
    $ttinternal+=$ttafiliasi;
}
#Plasma====================================================================================
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td bgcolor=#dedede></td><td  colspan=2 bgcolor=#dedede>A.3. Plasma</td><td colspan=9 bgcolor=#dedede></td></tr>";
if(!empty($kebunPlasma))foreach($kebunPlasma as $key=>$kodekebun)
{
        if(isset($ttang)) 
                unset($ttang);
                
        $no=$ttplasma=0;
        if(isset($tbsInt[$kodekebun])){
                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                    $no+=1;
					$stream.="<tr class=rowcontent>";
                    $stream.="<td></td><td>".$no."</td><td>".$optNm[$afd]."</td>";
                    $tt=0;
                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
                    $bgwarna="";
					setIt($art[$tang],0);
					setIt($ttang[$tang],0);
                    $scek="select distinct * from ".$dbname.".kebun_spb_vw where
                       left(blok,6)='".$afd."' and tanggal='".$tang."' 
                       and substr(nospb,9,6)<>left(blok,6)";

                    $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                        $rcek=owlBaris($qcek);
                    if($rcek==1){
                        $bgwarna="bgcolor=yellow title='Ada Buah Dari Afdeling Lain'";
                    }
                        $stream.="<td align=right ".$bgwarna.">".number_format($art[$tang])."</td>";
                        $tt+=$art[$tang];
                        $ttang[$tang]+=$art[$tang];
                    }
                    $stream.="<td align=right>".number_format($tottbsInt[$kodekebun][$afd])."</td></tr>";
					setIt($tkebun[$kodekebun],0);
                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                }   
            $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2></td><td bgcolor=#dedede>Total ".$kodekebun."</td>";
                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
				setIt($tplasma[$keei],0);
                $tplasma[$keei]+=$jum;
            }
            $stream.="<td align=right bgcolor=#dedede>".number_format($tkebun[$kodekebun])."</td></tr>";
            $ttplasma+=$tkebun[$kodekebun];
      }     
}
if(!empty($tplasma))
{
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td></td><td colspan=2 bgcolor=#dedede>Total Plasma</td>";
        if(!empty($tplasma))foreach($tplasma as $keei=>$jum){
        $stream.="<td align=right bgcolor=#dedede>".number_format($jum)."</td>";
        $ttplasma+=$jum;
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttplasma)."</td></tr>";
    $ttinternal+=$ttplasma;
}
#total internal
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=3 bgcolor=#dedede>Total Internal (A)</td>";
        if(!empty($TGL))foreach($TGL as $key=>$tg){
        $stream.="<td align=right bgcolor=#dedede>".number_format($tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg])."</td>";
		setIt($tinternal[$tg],0);
        $tinternal[$tg]+=$tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg];
//        $ttinternal+=$tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg];
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttinternal)."</td></tr>";
	setIt($gtt,0);
    $gtt+=$ttinternal;
    
    
#External========================================================================================
$stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2 bgcolor=#dedede>B.External</td><td colspan=10 bgcolor=#dedede></td></tr>";
$no=$ttExt=0;
if(!empty($tbsExt))
{
if(!empty($tbsExt))foreach($tbsExt as $suppid=>$art){
		$strs="select b.namasupplier from ".$dbname.".log_5suptimbangan a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.kodetimbangan='".$suppid."'";
		$ress=fetchdata($strs);
		$namasup = $ress[0]['namasupplier'];
		$no+=1;
        $stream.="<tr class=rowcontent>";
        $stream.="<td></td><td>".$no."</td><td>".$namasup."</td>";
        $tt=0;
        if(!empty($TGL))foreach($TGL as $kei=>$tang){
			setIt($art[$tang],0);
			setIt($tExt[$tang],0);
            $stream.="<td align=right>".number_format($art[$tang])."</td>";
            $tt+=$art[$tang];
            $tExt[$tang]+=$art[$tang];
        }
        $stream.="<td align=right>".number_format($tottbsExt[$suppid])."</td></tr>";
        $ttExt+=$tottbsExt[$suppid];
    }    
}
#total External

    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=3 bgcolor=#dedede>Total External (B)</td>";
        if(!empty($TGL))foreach($TGL as $key=>$tg){
        $stream.="<td align=right bgcolor=#dedede>".number_format($tExt[$tg])."</td>";
//        $ttExt+=$tExt[$tg];
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($ttExt)."</td></tr>";
    $gtt+=$ttExt;
#Grand Total
    $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=3 bgcolor=#dedede>Grand Total (A+B)</td>";
        if(!empty($TGL))foreach($TGL as $key=>$tg){
        $stream.="<td align=right bgcolor=#dedede>".number_format($tExt[$tg]+$tinternal[$tg])."</td>";
//        $gtt+=$tExt[$tg]+$tinternal[$tg];
    }
    $stream.="<td align=right bgcolor=#dedede>".number_format($gtt)."</td></tr>";
    
$stream.="</tbody><tfoot></tfoot></table>";
}               
switch($proses)
{
        case 'preview':          
            echo $stream;
        break;
        case 'excel':          
                        $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
                        $qwe=date("YmdHms");
                        $nop_="Laporan_penerimaan_TBS_Tanggal ".tanggalnormal($TGL[0])." sd Tanggal ".tanggalnormal($TGL[count($TGL)-1])."PKS".$kdPabrik.'_'.$qwe;
                        if(strlen($stream)>0)
                        {
                             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                             gzwrite($gztralala, $stream);
                             gzclose($gztralala);
                             echo "<script language=javascript1.2>
                                window.location='tempExcel/".$nop_.".xls.gz';
                                </script>"; 
                        }    
        break;
        case 'pdf':          
        //belum dibuat
          
	 class PDF extends FPDF
        {
            function Header() {
            global $conn;
            global $dbname;
            global $align;
            global $length;
            global $colArr;
            global $title;
            global $tipeIntex;
            global $periode;
            global $unit;
            global $kdPabrik;
            global $tgl_2;
            global $tgl_1;
            global $tglPeriode;
            global $TGL;
            global $optSupp;
            global $optRamp;
            global $optNm;
			global $blnA;
			global $owlPDO;
				
				
                $tglPeriode=explode("-",$periode);
                $tanggal=count($tglPeriode)>1? $tglPeriode[1]."-".$tglPeriode[0]: '';
                
				$arrHead = setheadreport(substr($kdPabrik,0,4));
				
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(110);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(110); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(110); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
				
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['rePenerimaanTbs']),0,1,'C');	
                $this->SetFont('Arial','',8);
                $this->Ln(5);
                if($_SESSION['language']=='EN'){
//                    $this->Cell($width,$height,"FFB receive from ".tanggalnormal($TGL[0])." to ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." (not include grading deduction)",0,1,'C');
                    $this->Cell($width,$height,"FFB receive month ".$blnA." to ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." (include grading deduction)",0,1,'C');
                }else{
//                    $this->Cell($width,$height,"Penerimaan TBS  dari Tanggal ".tanggalnormal($TGL[0])." s/d Tanggal ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." (belum dikurangi potongan sortasi)",0,1,'C');
                    $this->Cell($width,$height,"Penerimaan TBS  Bulan ".$blnA." s/d Tanggal ".tanggalnormal($TGL[count($TGL)-1])." PKS:".$kdPabrik." (sudah dikurangi potongan sortasi)",0,1,'C');
                }
                $this->Ln(10);
                         
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		//$pdf->SetFillColor(255,255,255); 
                $pdf->SetFillColor(220,220,220);
		$pdf->SetFont('Arial','',7);
        $totPdf=0;
        $nor=0;
        $no=0;
 
               $coldt=count($TGL)+1;
               $coldt=9*$coldt;
                $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
                if(!empty($TGL))foreach($TGL as $key=>$tg)
                {
                   $pdf->Cell(9/100*$width,$height,substr($tg,8,2),1,0,'C',1);
                }
                $pdf->Cell(9/100*$width,$height,$_SESSION['lang']['total'],1,1,'C',1);
                $pdf->Cell(20/100*$width,$height,"A.Internal",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                #inti=========================================================================================
                $pdf->SetFillColor(255,255,255); 
                $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                $pdf->Cell(15/100*$width,$height,"A.1. Inti",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                $pdf->SetFont('Arial','',6);
                if(!empty($kebunInternal))foreach($kebunInternal as $key=>$kodekebun)
                {
                        if(isset($ttang)) 
                                unset($ttang);
                        $pdf->SetFillColor(255,255,255); 
                        $no=0;
                        if(isset($tbsInt[$kodekebun])){
                                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                                    $no+=1;
                                    $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                                    $pdf->Cell(2/100*$width,$height,$no,1,0,'L',1);
                                    $pdf->Cell(13/100*$width,$height,$optNm[$afd],1,0,'L',1);
                                    
                                    
                                    $tt=0;
                                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
                                        setIt($ttang[$tang],0);
										setIt($art[$tang],0);
										$pdf->Cell(9/100*$width,$height,number_format($art[$tang]),1,0,'R',1);
										$ttang[$tang]+=$art[$tang];
                                    }
                                    $pdf->Cell(9/100*$width,$height,number_format($tottbsInt[$kodekebun][$afd]),1,1,'R',1);
//                                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                                }   
                                 $pdf->SetFillColor(220,220,220); 
                                $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                                $pdf->Cell(13/100*$width,$height,"Total ".$kodekebun,1,0,'L',1);
                            
                                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                                $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                                $tkebun[$kodekebun]+=$jum;
//                                $tinti[$keei]+=$jum;
                            }
                            $pdf->Cell(9/100*$width,$height,number_format($tkebun[$kodekebun]),1,1,'R',1);
//                            $ttinti+=$tkebun[$kodekebun];
                            
                      }     
                }
               
                if(!empty($tinti))
                {
                    $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                    $pdf->Cell(13/100*$width,$height,"Total Inti",1,0,'L',1);
                    
                        if(!empty($tinti))foreach($tinti as $keei=>$jum){
                       $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                        $ttinti+=$jum;
                    }
                    $pdf->Cell(9/100*$width,$height,number_format($ttinti),1,1,'R',1);
//    $ttinternal+=$ttinti;
                }
                
                
                #afiliasi====================================================================================
                $pdf->SetFillColor(255,255,255); 
                $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                $pdf->Cell(15/100*$width,$height,"A.2. Afiliasi",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                $pdf->SetFont('Arial','',6);
                if(!empty($kebunAffiliasi))foreach($kebunAffiliasi as $key=>$kodekebun)
                {
                        if(isset($ttang)) 
                                unset($ttang);
                        $pdf->SetFillColor(255,255,255); 
                        $no=0;
                        if(isset($tbsInt[$kodekebun])){
                                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                                    $no+=1;           
                                    $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                                    $pdf->Cell(2/100*$width,$height,$no,1,0,'L',1);
                                    $pdf->Cell(13/100*$width,$height,$optNm[$afd],1,0,'L',1);
                                    $tt=0;
                                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
										setIt($ttang[$tang],0);
										setIt($art[$tang],0);
                                        $pdf->Cell(9/100*$width,$height,number_format($art[$tang]),1,0,'R',1);
                                        $ttang[$tang]+=$art[$tang];
                                    }
                                    $pdf->Cell(9/100*$width,$height,number_format($tottbsInt[$kodekebun][$afd]),1,1,'R',1);
//                                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                                }   
                                $pdf->SetFillColor(220,220,220); 
                                $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                                $pdf->Cell(13/100*$width,$height,"Total ".$kodekebun,1,0,'L',1);
                            
                                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                                $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                                $tkebun[$kodekebun]+=$jum;
//                                $tafiliasi[$keei]+=$jum;
                            }
                            $pdf->Cell(9/100*$width,$height,number_format($tkebun[$kodekebun]),1,1,'R',1);
//                            $ttafiliasi+=$tkebun[$kodekebun];
                            
                      }     
                }
                
                if(!empty($tafiliasi))
                {
                    $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                    $pdf->Cell(13/100*$width,$height,"Total Afiliasi",1,0,'L',1);
                    
                        if(!empty($tafiliasi))foreach($tafiliasi as $keei=>$jum){
                       $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                         $ttafiliasi+=$jum;
                    }
                    $pdf->Cell(9/100*$width,$height,number_format($ttafiliasi),1,1,'R',1);
//    $ttinternal+=$ttafiliasi;
                }
                
                #Plasma====================================================================================
                $pdf->SetFillColor(255,255,255); 
                $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                $pdf->Cell(15/100*$width,$height,"A.3. Plasma",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                $pdf->SetFont('Arial','',6);
                if(!empty($kebunPlasma))foreach($kebunPlasma as $key=>$kodekebun)
                {
                        if(isset($ttang)) 
                                unset($ttang);
                        $pdf->SetFillColor(255,255,255); 
                        $no=0;
                        if(isset($tbsInt[$kodekebun])){
                                if(!empty($tbsInt[$kodekebun]))foreach($tbsInt[$kodekebun] as $afd=>$art){
                                    $no+=1;           
                                    $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                                    $pdf->Cell(2/100*$width,$height,$no,1,0,'L',1);
                                    $pdf->Cell(13/100*$width,$height,$optNm[$afd],1,0,'L',1);
                                    $tt=0;
                                    if(!empty($TGL))foreach($TGL as $kei=>$tang){
										setIt($ttang[$tang],0);
										setIt($art[$tang],0);
                                        $pdf->Cell(9/100*$width,$height,number_format($art[$tang]),1,0,'R',1);
                                        $ttang[$tang]+=$art[$tang];
                                    }
                                    $pdf->Cell(9/100*$width,$height,number_format($tottbsInt[$kodekebun][$afd]),1,1,'R',1);
//                                    $tkebun[$kodekebun]+=$tottbsInt[$kodekebun][$afd];
                                }   
                                $pdf->SetFillColor(220,220,220); 
                                $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                                $pdf->Cell(13/100*$width,$height,"Total ".$kodekebun,1,0,'L',1);
                            
                                if(!empty($ttang))foreach($ttang as $keei=>$jum){
                                $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                                $tkebun[$kodekebun]+=$jum;
//                                $tplasma[$keei]+=$jum;
                            }
                            $pdf->Cell(9/100*$width,$height,number_format($tkebun[$kodekebun]),1,1,'R',1);
//                            $ttplasma+=$tkebun[$kodekebun];
                            
                      }     
                }
                
                if(!empty($tplasma))
                {
                    $pdf->Cell(7/100*$width,$height," ",1,0,'L',1);
                    $pdf->Cell(13/100*$width,$height,"Total Afiliasi",1,0,'L',1);
                    
                        if(!empty($tplasma))foreach($tplasma as $keei=>$jum){
                       $pdf->Cell(9/100*$width,$height,number_format($jum),1,0,'R',1);
//                         $ttplasma+=$jum;
                    }
                    $pdf->Cell(9/100*$width,$height,number_format($ttplasma),1,1,'R',1);
                }
            #total internal
            $pdf->Cell(20/100*$width,$height,"Total Internal (A)",1,0,'L',1);
            if(!empty($TGL))foreach($TGL as $key=>$tg){
                 $pdf->Cell(9/100*$width,$height,number_format($tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg]),1,0,'R',1);
//                $tinternal[$tg]+=$tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg];
//                $ttinternal+=$tinti[$tg]+$tafiliasi[$tg]+$tplasma[$tg];
            }
            $pdf->Cell(9/100*$width,$height,number_format($ttinternal),1,1,'R',1);
//    $gtt+=$ttinternal;
            
            #External========================================================================================
            $stream.="<tr class=rowcontent style='font-weight:bolder;'><td colspan=2 bgcolor=#dedede>B.External</td><td colspan=10 bgcolor=#dedede></td></tr>";
            $no=0;
                $pdf->SetFillColor(220,220,220); 
                $pdf->Cell(20/100*$width,$height,"B.External",1,0,'L',1);
                $pdf->Cell($coldt/100*$width,$height," ",1,1,'L',1);
                $pdf->SetFont('Arial','',6);
                if(!empty($tbsExt))
                {
                  $pdf->SetFillColor(255,255,255);    
                if(!empty($tbsExt))foreach($tbsExt as $suppid=>$art){
                        $no+=1;
                        
                        $pdf->Cell(5/100*$width,$height," ",1,0,'L',1);
                        $pdf->Cell(2/100*$width,$height,$no,1,0,'L',1);
                        $pdf->Cell(13/100*$width,$height,($optSupp[$suppid]==''?$optRamp[$suppid]:$optSupp[$suppid]),1,0,'L',1);
                        $tt=0;
                        if(!empty($TGL))foreach($TGL as $kei=>$tang){
							setIt($art[$tang],0);
                            $pdf->Cell(9/100*$width,$height,number_format($art[$tang]),1,0,'R',1);
                        }
                         $pdf->Cell(9/100*$width,$height,number_format($tottbsExt[$suppid]),1,1,'R',1);
//                         $ttExt+=$tottbsExt[$suppid];
                    }    
                }
                $pdf->SetFillColor(220,220,220); 
#total External
    $pdf->Cell(20/100*$width,$height,"Total External (B)",1,0,'L',1);
            if(!empty($TGL))foreach($TGL as $key=>$tg){
                 $pdf->Cell(9/100*$width,$height,number_format($tExt[$tg]),1,0,'R',1);
//                 $ttExt+=$tExt[$tg];
            }
     $pdf->Cell(9/100*$width,$height,number_format($ttExt),1,1,'R',1);
//    $gtt+=$ttExt;
#Grand Total
    $pdf->Cell(20/100*$width,$height,"Grand Total (A+B)",1,0,'L',1);
            if(!empty($TGL))foreach($TGL as $key=>$tg){
                 $pdf->Cell(9/100*$width,$height,number_format($tExt[$tg]+$tinternal[$tg]),1,0,'R',1);
//                  $gtt+=$tExt[$tg]+$tinternal[$tg];
            }
     $pdf->Cell(9/100*$width,$height,number_format($gtt),1,1,'R',1);
    $pdf->Output();
            
        break;

       

        case'getKodeorg':
        $optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
        
        $kdPabrik=$_POST['kdPabrik']; 
        $optPt=  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
        $ptnya=$optPt[$kdPabrik]; 
        if($tipeIntex==1)
        {
                //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk ='".$ptnya."' order by namaorganisasi asc";
        }
        else if($tipeIntex==0)
        {
                // $sOrg="SELECT namasupplier,`kodetimbangan` FROM ".$dbname.".log_5supplier WHERE kodekelompok='S003' order by namasupplier asc";//echo "warning:".$sOrg;
                $sOrg="SELECT distinct kodesupplier FROM ".$dbname.".pabrik_timbangan WHERE millcode='".$kdPabrik."' and kodesupplier!=''";//echo "warning:".$sOrg;
                // $sOrg="SELECT * FROM ".$dbname.".log_5suptimbangan";//echo "warning:".$sOrg;
        }
        else if($tipeIntex==2)
        {
                //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk not in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk <>'".$ptnya."' order by namaorganisasi asc";
        }
         //echo "warning:__".$sOrg."___".$tipeIntex;exit();
        if($tipeIntex!=3)
        {
            $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
            $qOrg->setFetchMode(PDO::FETCH_ASSOC);
            while($rOrg=$qOrg->fetch()){
                    if($tipeIntex!=0)
                    {
                            $optorg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
                    }
                    else
                    {
                    		$where=" supplierid='".$rOrg['kodesupplier']."'";
                    		$optsup=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier',$where);
                            $optorg.="<option value=".$rOrg['kodesupplier'].">".$optsup[$rOrg['kodesupplier']]."</option>";
                    }
            }
        }
        echo $optorg;
        break;
        default:
        break;
}
}
?>