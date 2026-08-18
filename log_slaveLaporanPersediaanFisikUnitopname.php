<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
include_once('lib/HtmlExcel.php');
#
//FILE INI SUDAH TIDAK FULL TERPAKAI, GANYA PENGAMBILAN GUDANG SAJA YANG TERPAKAI
#
empty($_POST['proses']) ? $proses = isset($_GET['proses']) ? $_GET['proses'] : '' : $proses = $_POST['proses'];
empty($_POST['unitDt']) ? $unitDt = isset($_GET['unitDt']) ? $_GET['unitDt'] : '' : $unitDt = $_POST['unitDt'];
empty($_POST['gudang']) ? $gudang = isset($_GET['gudang']) ? $_GET['gudang'] : '' : $gudang = $_POST['gudang'];
empty($_POST['optshow']) ? $optshow = isset($_GET['optshow']) ? $_GET['optshow'] : '' : $optshow = $_POST['optshow'];
empty($_POST['periode']) ? $periode = isset($_GET['periode']) ? $_GET['periode'] : '' : $periode = $_POST['periode'];
$optNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNmSat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$where = '';
if ($proses != 'getGudang') {
    if ($unitDt == '') {
        exit("Error : Unit Tidak Boleh Kosong");
    }
    if ($gudang != '') {
        $where.="and kodegudang like '" . $gudang . "%'";
    } else {
        exit("Error : Gudang Tidak Boleh Kosong");
    }
    if ($periode != '') {
        $where.=" and periode='" . $periode . "'";
    }
    if ($proses == 'excel') {
		$tab = " <table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr></tr>
				<tr>	
					<td align=center colspan=12><b>FORM STOCK OPNAME</b></td>
				</tr>	
				<tr></tr>
			</thead>";
				
        $tab.= " <table class=sortable cellspacing=1 border=1 width=100%>
			<thead>
                <tr>
					<td  bgcolor=#DEDEDE  align=center>No.</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['unit'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['sloc'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['periode'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['namabarang'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['satuan'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['minstok'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['saldoawal'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['masuk'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['keluar'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['saldo'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>Nilai Saldo Kurang</td>
					
                </tr>
            </thead><tbody>";
    }

    if($optshow == '1'){
        // $wh=" and (a.kodebarang like '%".$textcari."%' or a.namabarang like '%".$textcari."%')";
                $sData = "SELECT 
                b.*, 
                COALESCE(b.qtymasuk, 0) as qtymasuk,
                COALESCE(b.qtykeluar, 0) as qtykeluar,
                COALESCE(b.saldoakhirqty, 0) as saldoakhirqty,
                COALESCE(b.saldoawalqty, 0) as saldoawalqty
                FROM 
                    ".$dbname.".log_5masterbarang a 
                LEFT JOIN 
                    log_5saldobulanan b 
                ON 
                    a.kodebarang = b.kodebarang
                AND 
                    b.periode = '".$periode."'
                WHERE 
                    b.kodegudang != '' 
                AND 
                    b.kodegudang LIKE '".$gudang."%' and substr(a.kodebarang,1,1)='3' ";

    }else{
        $sData = "select distinct * from " . $dbname . ".log_5saldobulanan a 
        where kodegudang!='' " . $where . " and (qtymasuk !='0' or qtykeluar !='0' or saldoakhirqty !='0')";
    }


    // $sData = "select distinct * from " . $dbname . ".log_5saldobulanan a 
	// 		where kodegudang!='' " . $where . " and (qtymasuk !='0' or qtykeluar !='0' or saldoakhirqty !='0')";
    $qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
    $qData->setFetchMode(PDO::FETCH_ASSOC);
    while ($rData = $qData->fetch()) {
        $dtPeriode[$rData['periode']] = $rData['periode'];
        $lstKdBrg[$rData['kodebarang']] = $rData['kodebarang'];
        $dtKdBarang[$rData['periode']][$rData['kodebarang']] = $rData['kodebarang'];
        $dtAwal[$rData['periode'] . $rData['kodebarang']] = $rData['saldoawalqty'];
        $dtMasuk[$rData['periode'] . $rData['kodebarang']] = $rData['qtymasuk'];
        $dtKeluar[$rData['periode'] . $rData['kodebarang']] = $rData['qtykeluar'];
        $dtAkhir[$rData['periode'] . $rData['kodebarang']] = $rData['saldoakhirqty'];
    }

    $chekDt = count($dtPeriode);
    if ($chekDt == 0) {
        exit("Error:Data Kosong");
    }

    $no = 0;
    $tab.='';
    foreach ($dtPeriode as $dtIsi) {
        foreach ($lstKdBrg as $dtBrg) {
            if (!empty($dtKdBarang[$dtIsi][$dtBrg])) {
				$strmin="select stok from ".$dbname.".log_5minimunstok where gudang='".$gudang."' and kodebarang='".$dtKdBarang[$dtIsi][$dtBrg]."'";
				$resmin=fetchdata($strmin);
				$stokmin = ($resmin[0]['stok']==''?0:$resmin[0]['stok']);
				$vstokmin = "";
				$skstok = "";
				$bgcolormin = "";
				if($stokmin > 0){
					$vstokmin = ($stokmin==0?'':$stokmin);						
					$skstok = $dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]] - $vstokmin;
					if($skstok < 0){
						$bgcolormin="red";
					}
				}
                $no+=1;
                $tglSkrg = date('Y-m-d H:i:s');
                $tab.="<tr class=rowcontent style='cursor:pointer;background-color:".$bgcolormin."' title='Click' onclick=\"detailMutasiBarang2(event,'" . $unitDt . "','" . $dtIsi . "','" . $gudang . "','" . $dtKdBarang[$dtIsi][$dtBrg] . "','" . $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]] . "','" . $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]] . "');\">";
                $tab.="<td align=center style='width:50px;'>" . $no . "</td>";
                $tab.="<td align=center style='width:100px;'>" . $unitDt . "</td>";
                $tab.="<td align=center style='width:100px;'>" . $gudang . "</td>";
                $tab.="<td align=center style='width:100px;'>" . $dtIsi . "</td>";
                $tab.="<td align=center style='width:100px;'>" . $dtKdBarang[$dtIsi][$dtBrg] . "</td>";
                $tab.="<td style='width:200px;'>" . $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]] . "</td>";
                $tab.="<td align=center style='width:100px;'>" . $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]] . "</td>";
				$tab.="<td align=right>".numberformat_kasih_koma($vstokmin,2)."</td>";
                $tab.="<td align=right class=firsttd style='width:100px;'>" . numberformat_kasih_koma($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]]) . "</td>"; //saldo awal
                $tab.="<td align=right style='width:100px;'>" . numberformat_kasih_koma($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]]) . "</td>"; //saldo masuk
                $tab.="<td align=right  class=firsttd style='width:100px;'>" . numberformat_kasih_koma($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]]) . "</td>"; //saldo keluar
                $tab.="<td align=right  class=firsttd style='width:100px;'>" . numberformat_kasih_koma($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]]) . "</td>"; //saldo akhir  
				$tab.="<td align=right style='width:100px;'>" . numberformat_kasih_koma($skstok) . "</td>";
				
			}
        }
    }
	$tab.= " <table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr>
				<td colspan=4>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name']." <td>
				</tr>
				<tr>
				</tr>
				<tr>
					<td></td>	
					<td align=center colspan=4>Approve By,</td>
					<td colspan=3></td>
					<td align=center colspan=3>Opname By,</td>
				</tr>	
				<tr></tr>
				<tr></tr>
				<tr></tr>
				<tr></tr>
				<tr>
					<td></td>	
					<td align=center colspan=4>______________</td>
					<td colspan=3></td>
					<td align=center colspan=3>______________</td>
				</tr>	

			</thead>";
	
}
switch ($proses) {
    case'getGudang':
        $optUnit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sUnit = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where 
				kodeorganisasi like '" . $unitDt . "%' and tipe like 'GUDANG%' order by namaorganisasi asc";
        $qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
		$qUnit->setFetchMode(PDO::FETCH_ASSOC);
		while ($rUnit = $qUnit->fetch()) {
            $optUnit.="<option value='" . $rUnit['kodeorganisasi'] . "'>" . $rUnit['namaorganisasi'] . "</option>";
        }
        echo $optUnit;
        break;
    case'preview':
        echo $tab;
        break;
    // case'excel':
        // // $tab.="</tbody></table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
        // $dte = date("Hms");
        // $nop_ = "lapPersediaanFisikUnit_" . $dte;
        // $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        // gzwrite($gztralala, $tab);
        // gzclose($gztralala);
        // echo "<script language=javascript1.2>
			// window.location='tempExcel/" . $nop_ . ".xls.gz';
			// </script>";
        // break;
		
	case'excel':
		$nop = "lapPersediaanFisikUnit_".$gudang."_".$periode.".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("datagudang", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
	break;
		

}