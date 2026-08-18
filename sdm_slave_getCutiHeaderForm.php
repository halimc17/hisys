<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include('lib/zLib.php');

$kodeorg = $_POST['kodeorg'];
$periode = $_POST['periode'];
$tipekaryawan = $_POST['tipekaryawan'];

$tglAbis=date('Y-m-d');
if ($_SESSION['empl']['tipelokasitugas']!='HOLDING') 
{
	$tpData=" and b.tipekaryawan in (0,1,2,3,4,5,6,7,8,9,10,11,12)"; 
	if($tipekaryawan!=''){
		$tpData=" and b.tipekaryawan='".$tipekaryawan."'";
	}

    $str1 = "select a.*,b.namakaryawan,b.tanggalmasuk,b.lokasitugas as locTugas,b.tipekaryawan,b.nik,c.tipe,COALESCE(ROUND(DATEDIFF('".$tglAbis."',tanggalmasuk)/365.25,3),0) as masakerja, a.hakcuti,a.kodeorg 
	from " . $dbname . ".sdm_cutiht a 
	left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid 
	left join " . $dbname . ".sdm_5tipekaryawan c on b.tipekaryawan = c.id 
	where b.lokasitugas='".$kodeorg . "'  and a.kodeorg = '".$kodeorg."'
	and (a.periodecuti='" . $periode . "')  and b.statuskaryawan != 'Keluar' 
	and (b.tanggalkeluar='0000-00-00' or b.tanggalkeluar>='".$tglAbis."') ".$tpData;
} else {
	$tpData=" and b.tipekaryawan in (0,1,2,3,4,5,6,7,8,9,10,11,12)";  
    if ($tipekaryawan!=''){
		$tpData=" and b.tipekaryawan='".$tipekaryawan."'";  
    }
    
    $str1 = "select a.*,b.namakaryawan,b.tanggalmasuk,b.lokasitugas  as locTugas,b.tipekaryawan,b.nik,c.tipe,COALESCE(ROUND(DATEDIFF('".$tglAbis."',tanggalmasuk)/365.25,3),0) as masakerja,a.hakcuti , a.kodeorg
	from " . $dbname . ".sdm_cutiht a 
	left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid 
	left join " . $dbname . ".sdm_5tipekaryawan c on b.tipekaryawan = c.id 
	where b.lokasitugas='" . $kodeorg . "' and a.kodeorg = '".$kodeorg."'
	and (a.periodecuti='" . $periode . "')  and b.statuskaryawan != 'Keluar' 
    and (b.tanggalkeluar='0000-00-00' or b.tanggalkeluar>='".$tglAbis."') ".$tpData;
}

echo"<table class=sortable cellspacing=1 cellpadding=7 style='width:100%;' border=0>
	<thead>
	<tr class=rowheader>
		<th style='text-align:center;'>No</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['kodeorganisasi'] . "</th>		 
        <th style='text-align:center;'>" . $_SESSION['lang']['nik'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['namakaryawan'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['tipekaryawan'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['tanggalmasuk'] . "</th>
        <th style='text-align:center;'>Masa Kerja (Tahun-Bulan)</th>			
        <th style='text-align:center;'>" . $_SESSION['lang']['periode'] . "</th>			
        <th style='text-align:center;'>" . $_SESSION['lang']['dari'] . " Tanggal</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['tanggalsampai'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['hakcuti'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['hakcuti'] . " Tambahan</th>
        <th style='text-align:center;'>Adjs " . $_SESSION['lang']['hakcuti'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['diambil'] . " (" . $_SESSION['lang']['hari'] . ")</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['sisa'] . "</th>
		<th hidden style='text-align:center;'>" . $_SESSION['lang']['sisa'] . " Cuti Tahun lalu</th>
	</tr>
	</thead>
	<tbody id=container>";

$no = 0;

// Get RangeTanggal
function getRangeTanggal($tglAwal, $tglAkhir) {
    $jlh = strtotime($tglAkhir) - strtotime($tglAwal);
    $jlhHari = $jlh / (3600 * 24);
    return $jlhHari + 1;
}	

function adddate($vardate, $added) {
    $data = explode("-", $vardate);
    $date = new DateTime();
    $date->setDate($data[0], $data[1], $data[2]);
    $date->modify("" . $added . "");
    $day = $date->format("Y-m-d");
    return $day;
}

$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch()) 
{
	$no+=1;
	$penambahTanggal = $bar1->sampai;

	$tipekaryawan = makeOption($dbname, "datakaryawan", "karyawanid,tipekaryawan");
	if ($tipekaryawan[$bar1->karyawanid] == 0) {
		$table = "sdm_ijin";
	} else {
		$table = "sdm_ijinnonstaff";
	}
    
	//Masa kerja
    $date1 = $bar1->tanggalmasuk;
    $date2 = date('Y-m-d');

    $diff = abs(strtotime($date2) - strtotime($date1));

    $years = floor($diff / (365 * 60 * 60 * 24));
    $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
    $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
    $blnMasa=0;
    $lamaKerja = " " . $years . " Tahun " . $months . " Bulan";
    @$blnMasa=$months/12;
    
	## Disini
	$hakcuti=$bar1->hakcuti;
	$tambahan_hakcuti=$bar1->cutitambahan;
	$adjsment_hakcuti=$bar1->adjs_hakcuti;
	$diambil_hakcuti=$bar1->diambil;
	$sisa_hakcuti=$bar1->sisa;
	
	// if ($hakcuti>0){
		echo"<tr class=rowcontent>
			<td align=center>" . $no . "</td>
			<td align=center>" . substr($bar1->locTugas, 0, 4) . "</td>
			<td align=center>" . $bar1->nik . "</td>
			<td class=firsttd id=nama" . $no . "  title='Click for detail' style='cursor:pointer'  onclick=showByUser('" . $bar1->karyawanid . "',event)>" . $bar1->namakaryawan . "</td>
			<td align=center>" . $bar1->tipe . "</td>
			<td align=center>" . tanggalnormal($bar1->tanggalmasuk) . "</td>
			<td align=right>" . $lamaKerja . "</td>
			<td align=center>" . $bar1->periodecuti . "</td>				   
			<td align=center>" . tanggalnormal($bar1->dari) . "</td>
			<td align=center>" . tanggalnormal($penambahTanggal) . "</td>
			<td disabled readonly style='text-align:right' size=2 align=center value='" . $hakcuti . "' id=hakcuti" . $no . ">" . $hakcuti . "</td>
			<td disabled readonly style='text-align:right' size=2 align=center value='" . $tambahan_hakcuti . "' id=cutitambahan" . $no . ">" . $tambahan_hakcuti . "</td>
			<td disabled readonly style='text-align:right' size=2 align=center value='" . $adjsment_hakcuti . "' id=adjscuti" . $no . ">" . $adjsment_hakcuti . "</td>
			<td disabled readonly style='text-align:right' size=2 align=center value='" . $diambil_hakcuti . "' id=diambil_hakcuti" . $no . ">" . $diambil_hakcuti . "</td>
			<td disabled readonly style='text-align:right' size=2 align=center value='" . $sisa_hakcuti . "' id=sisa_hakcuti" . $no . ">" . $sisa_hakcuti . "</td>
		</tr>";
	// }
}
// echo $xxx; exit;
echo"</tbody>
	<tfoot>
	</tfoot>
</table>";
?>