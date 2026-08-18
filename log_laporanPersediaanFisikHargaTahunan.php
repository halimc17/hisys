<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$pt = $_POST['pt'];
$gudang = $_POST['gudang'];
$periode = $_POST['periode'];

$pt      =checkPostGet('pt','');
$gudang  =checkPostGet('gudang','');
$periode =checkPostGet('periode','');
$tipe=checkPostGet('tipe','');

if ($pt == '') {
    echo"Error: Perusahaan Tidak Boleh Kosong.";
    exit;
}

if ($periode == '') {
    echo"Error: Please choose Periode.";
    exit;
}

$arrBarang = array();
$arrAwal = array();
$kamussatuan = array();
$kamusnamabarang = array();

//nyari barang
if ($gudang == '') {
    $str = "select a.kodebarang, b.satuan, b.namabarang from " . $dbname . ".log_5saldobulanan a
    left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang
    where a.kodeorg='" . $pt . "' 
    and a.periode like '" . $periode . "%' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
    group by a.kodebarang order by a.kodebarang";
} else {
    $str = "select a.kodebarang, b.satuan, b.namabarang from " . $dbname . ".log_5saldobulanan a
    left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang
    where a.kodeorg='" . $pt . "' and kodegudang = '" . $gudang . "'
    and a.periode like '" . $periode . "%' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
    group by a.kodebarang order by a.kodebarang";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $arrBarang[$bar->kodebarang] = $bar->kodebarang;
    $kamussatuan[$bar->kodebarang] = $bar->satuan;
    $kamusnamabarang[$bar->kodebarang] = $bar->namabarang;
}
$sAwal="select distinct right(periode,2) as prd from ".$dbname.".setup_periodeakuntansi where periode like '".$periode."%' 
        and kodeorg in (select distinct kodegudang from ".$dbname.".log_5saldobulanan where kodeorg='".$pt."') order by periode asc";
$rAwal=fetchData($sAwal);
$prdbln=$rAwal[0]['prd'];
//echo $sAwal;

//nyari saldoawal


   if ($gudang == '') {
    $str = "
    select a.kodebarang,sum(a.saldoawalqty) as saldoawalqty,sum(a.nilaisaldoawal) as nilaisaldoawal from
    (select kodebarang ,kodegudang,min(periode),saldoawalqty , nilaisaldoawal  from " . $dbname . ".log_5saldobulanan
    where kodeorg='" . $pt . "' 
    and periode like '%" . $periode ."%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    group by kodebarang ,kodegudang order by kodebarang) a group by a.kodebarang order by a.kodebarang";
} else {

    


    $str = " select a.kodebarang,sum(a.saldoawalqty) as saldoawalqty,sum(a.nilaisaldoawal) as nilaisaldoawal from
    (select kodebarang ,kodegudang,min(periode),saldoawalqty , nilaisaldoawal  from " . $dbname . ".log_5saldobulanan
    where kodeorg='" . $pt . "' and kodegudang = '" . $gudang . "'
    and periode like '%" . $periode ."%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    group by kodebarang ,kodegudang order by kodebarang) a group by a.kodebarang order by a.kodebarang";
}

          //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $arrAwal[$bar->kodebarang]['saldoawalqty'] = $bar->saldoawalqty;
            @$arrAwal[$bar->kodebarang]['hargaratasaldoawal'] = $bar->nilaisaldoawal / $bar->saldoawalqty;
            $arrAwal[$bar->kodebarang]['nilaisaldoawal'] = $bar->nilaisaldoawal;
        }





//nyari tahun berjalan
if ($gudang == '') {
    $str = "select kodebarang, sum(qtymasuk) as qtymasuk, sum(qtykeluar) as qtykeluar, sum(qtymasukxharga) as qtymasukxharga, sum(qtykeluarxharga) as qtykeluarxharga 
    from " . $dbname . ".log_5saldobulanan
    where kodeorg='" . $pt . "' 
    and periode like '" . $periode . "%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    group by kodebarang
    order by kodebarang";
} else {
    $str = "select kodebarang, sum(qtymasuk) as qtymasuk, sum(qtykeluar) as qtykeluar, sum(qtymasukxharga) as qtymasukxharga, sum(qtykeluarxharga) as qtykeluarxharga 
    from " . $dbname . ".log_5saldobulanan 
    where kodeorg='" . $pt . "' and kodegudang = '" . $gudang . "'
    and periode like '" . $periode . "%' and (qtymasuk!=0 or qtykeluar!=0 or saldoakhirqty!=0)
    group by kodebarang
    order by kodebarang";
}
//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $arrAwal[$bar->kodebarang]['qtymasuk'] = $bar->qtymasuk;
    $arrAwal[$bar->kodebarang]['qtykeluar'] = $bar->qtykeluar;
    $arrAwal[$bar->kodebarang]['qtymasukxharga'] = $bar->qtymasukxharga;
    $arrAwal[$bar->kodebarang]['qtykeluarxharga'] = $bar->qtykeluarxharga;
}

if($tipe=='excel'){
	$tab.="<table class=sortable style='position:absolut;' cellspacing=1 border=1>
	     <thead>
		    <tr>
			  <th rowspan=2 align=center style='width:50px;'>No.</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['periode'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['satuan'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['saldoawal'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['masuk'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['keluar'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['saldoakhir'] . "</th>
			</tr>
			<tr>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>  
			</tr>   
		 </thead>";
}

$no = 0;
if (!empty($arrBarang)) {
    $totalSaldoAwal = 0;
    $totalMasuk = 0;
    $totalKeluar = 0;
    $totalSaldo = 0;
    foreach ($arrBarang as $barang) {
        $no+=1;
        $hargamasuk = 0;
        $hargakeluar = 0;
        @$hargamasuk = $arrAwal[$barang]['qtymasukxharga'] / $arrAwal[$barang]['qtymasuk'];
        @$hargakeluar = $arrAwal[$barang]['qtykeluarxharga'] / $arrAwal[$barang]['qtykeluar'];

        @$salakqty = $arrAwal[$barang]['saldoawalqty'] + $arrAwal[$barang]['qtymasuk'] - $arrAwal[$barang]['qtykeluar'];
        @$salakrp = $arrAwal[$barang]['nilaisaldoawal'] + $arrAwal[$barang]['qtymasukxharga'] - $arrAwal[$barang]['qtykeluarxharga'];
        @$salakhar = $salakrp / $salakqty;
        $tab.="<tr class=rowcontent>
        <td style='width:50px;'>" . $no . "</td>
        <td style='width:100px;'>" . $periode . "</td>
        <td style='width:100px;'>" . $barang . "</td>
        <td style='width:300px;'>" . $kamusnamabarang[$barang] . "</td>
        <td style='width:50px;'>" . $kamussatuan[$barang] . "</td>
        <td align=right class=firsttd style='width:100px;'>" . hidezerodecimalv2(isset($arrAwal[$barang]['saldoawalqty']) ? $arrAwal[$barang]['saldoawalqty'] : 0, 2) . "</td>
        <td align=right style='width:100px;'>" . hidezerodecimalv2(isset($arrAwal[$barang]['hargaratasaldoawal']) ? $arrAwal[$barang]['hargaratasaldoawal'] : 0, 2) . "</td>
        <td align=right style='width:100px;'>" . hidezerodecimalv2(isset($arrAwal[$barang]['nilaisaldoawal']) ? $arrAwal[$barang]['nilaisaldoawal'] : 0, 2) . "</td>
        <td align=right class=firsttd style='width:100px;'>" . hidezerodecimalv2($arrAwal[$barang]['qtymasuk'], 2) . "</td>
        <td align=right style='width:100px;'>" . hidezerodecimalv2($hargamasuk, 2) . "</td>
        <td align=right style='width:100px;'>" . hidezerodecimalv2($arrAwal[$barang]['qtymasukxharga'], 2) . "</td>
        <td align=right class=firsttd style='width:100px;'>" . hidezerodecimalv2($arrAwal[$barang]['qtykeluar'], 2) . "</td>
        <td align=right style='width:100px;'>" . hidezerodecimalv2($hargakeluar, 2) . "</td>
        <td align=right style='width:100px;'>" . hidezerodecimalv2($arrAwal[$barang]['qtykeluarxharga'], 2) . "</td>
        <td align=right class=firsttd style='width:100px;'>" . hidezerodecimalv2($salakqty, 2) . "</td>
        <td align=right style='width:100px;'>" . hidezerodecimalv2($salakhar, 2) . "</td>
        <td align=right style='width:100px;'>" . hidezerodecimalv2($salakrp, 2) . "</td>
    </tr>";
        $totalSaldoAwal += (isset($arrAwal[$barang]['nilaisaldoawal']) ? $arrAwal[$barang]['nilaisaldoawal'] : 0);
        $totalMasuk += $arrAwal[$barang]['qtymasukxharga'];
        $totalKeluar += $arrAwal[$barang]['qtykeluarxharga'];
        $totalSaldo += $salakrp;
    }
    $tab.="<tr class=rowcontent>
        <td colspan=5 style='text-align:center; font-weight:bold'>" . strtoupper($_SESSION['lang']['total']) . "</td>
        <td colspan=2></td>
		<td style='text-align:right; font-weight:bold'>" . hidezerodecimalv2($totalSaldoAwal, 0) . "</td>
		<td colspan=2></td>
		<td style='text-align:right; font-weight:bold'>" . hidezerodecimalv2($totalMasuk, 0) . "</td>
		<td colspan=2></td>
		<td style='text-align:right; font-weight:bold'>" . hidezerodecimalv2($totalKeluar, 0) . "</td>
		<td colspan=2></td>
		<td style='text-align:right; font-weight:bold'>" . hidezerodecimalv2($totalSaldo, 0) . "</td>
    </tr>";
}
if (empty($arrBarang)) {
    $tab.="<tr class=rowcontent>
        <td colspan=17>no data.</td>
    </tr>";
}
$tab.="</table>";

if($tipe=='excel'){
	$stream.=$tab;
	$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];
	$tempnm = explode("/",$_SERVER['PHP_SELF']);
	$nop_ = substr($tempnm[2],0,strripos($tempnm[2],'.'));
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
			if (!fwrite($handle, $stream)) {
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
}else{			
	echo $tab;
}

function hidezerodecimalv2($val,$no=0){
	if($no==0){
		$hasil = @number_format(@$val);
	}else{
		if($val==''){
			$hasil=rtrim(rtrim(@number_format(0, $no), '0'), '.');
		}else{
			$hasil = rtrim(rtrim(@number_format(@$val, $no), '0'), '.');			
		}
	}
	if($hasil==0){
		$hasil='';
	}
	return $hasil;
}
?>