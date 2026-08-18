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

    $str1 = "select a.*,b.namakaryawan,b.tanggalmasuk,b.lokasitugas as locTugas,b.tipekaryawan,b.nik,c.tipe,COALESCE(ROUND(DATEDIFF('".$tglAbis."',tanggalmasuk)/365.25,3),0) as masakerja  
	from " . $dbname . ".sdm_5cutiadjsment a 
	left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid 
	left join " . $dbname . ".sdm_5tipekaryawan c on b.tipekaryawan = c.id 
	where b.lokasitugas='".$kodeorg . "' 
	and (a.periodecuti='" . $periode . "')  and b.statuskaryawan != 'Keluar' 
	and (b.tanggalkeluar='0000-00-00' or b.tanggalkeluar>='".$tglAbis."') ".$tpData;
} else {
	$tpData=" and b.tipekaryawan in (0,1,2,3,4,5,6,7,8,9,10,11,12)";  
    if ($tipekaryawan!=''){
		$tpData=" and b.tipekaryawan='".$tipekaryawan."'";  
    }
    
    $str1 = "select a.*,b.namakaryawan,b.tanggalmasuk,b.lokasitugas  as locTugas,b.tipekaryawan,b.nik,c.tipe,COALESCE(ROUND(DATEDIFF('".$tglAbis."',tanggalmasuk)/365.25,3),0) as masakerja 
	from " . $dbname . ".sdm_5cutiadjsment a 
	left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid 
	left join " . $dbname . ".sdm_5tipekaryawan c on b.tipekaryawan = c.id 
	where b.lokasitugas='" . $kodeorg . "'
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
        <th style='text-align:center;'>" . $_SESSION['lang']['periode'] . "</th>			
        <th style='text-align:center;'>Adjs " . $_SESSION['lang']['hakcuti'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['keterangan'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['createby'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['createtime'] . "</th>
        <th style='text-align:center;'>" . $_SESSION['lang']['action'] . "</th>
	</tr>
	</thead>
	<tbody id=container>";

    $no=0;

    $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while ($bar1 = $res1->fetch()){
        $no+=1;
        echo"<tr class=rowcontent>
			<td align=center>" . $no . "</td>
			<td align=center>" . substr($bar1->locTugas, 0, 4) . "</td>
			<td align=center>" . $bar1->nik . "</td>
			<td align=center>" . $bar1->namakaryawan . "</td>
			<td align=center>" . $bar1->tipe . "</td>
			<td align=center>" . tanggalnormal($bar1->tanggalmasuk) . "</td>
			<td align=center>" . $bar1->periodecuti . "</td>
			<td align=center>" . $bar1->adjs_hakcuti . "</td>
			<td align=center>" . $bar1->keterangan . "</td>
			<td align=center>" . getKary($bar1->createdby,'namakaryawan') . "</td>
			<td align=center>" . $bar1->createtime . "</td>";
		if($bar1->flag == 0){
			echo"<td align=center>
					<button class=mybutton onclick=deleteAdjsmen('".$bar1->id."','".substr($bar1->locTugas, 0, 4)."','".$bar1->karyawanid."','".$bar1->periodecuti."','".$bar1->adjs_hakcuti."')>".$_SESSION['lang']['delete']."</button>
				</td>";
		}else{
			echo"<td align=center></td>";
		}

		echo"</tr>";
    }

    echo"</tbody>
	<tfoot>
	</tfoot>
</table>";

?>
