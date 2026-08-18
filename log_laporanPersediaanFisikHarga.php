<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt     =checkPostGet('pt','');
$gudang =checkPostGet('gudang','');
$periode=checkPostGet('periode','');
$unitDt=checkPostGet('unitDt','');


if (isset($unitDt)) {//ini dari tab laporan stok per unit (tab 3)
    if ($unitDt == '') {
        exit("Error: Unit Tidak Boleh Kosong");
    }
    $str = "select 
              a.kodeorg,
              a.kodebarang,
              sum(a.saldoakhirqty) as salakqty,
              sum(a.nilaisaldoakhir) as salakrp,
              sum(a.qtymasuk) as masukqty,
              sum(a.qtykeluar) as keluarqty,
              sum(qtymasukxharga) as masukrp,
              sum(qtykeluarxharga) as keluarrp,                      
              sum(a.saldoawalqty) as sawalqty,
              sum(a.nilaisaldoawal) as sawalrp,
                b.namabarang,b.satuan    
                from " . $dbname . ".log_5saldobulanan a
                left join " . $dbname . ".log_5masterbarang b
                on a.kodebarang=b.kodebarang
              where kodegudang like '" . $unitDt . "%' 
              and periode='" . $periode . "' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
              group by a.kodebarang order by a.kodebarang";
} else if ($gudang == '') {
    if ($pt == '') {
        exit("Error: Perusahaan Tidak Boleh Kosong");
    }
    $str = "select 
                      a.kodeorg,
                      a.kodebarang,
                      sum(a.saldoakhirqty) as salakqty,
                      sum(a.nilaisaldoakhir) as salakrp,
                      sum(a.qtymasuk) as masukqty,
                      sum(a.qtykeluar) as keluarqty,
                      sum(qtymasukxharga) as masukrp,
                      sum(qtykeluarxharga) as keluarrp,                      
                      sum(a.saldoawalqty) as sawalqty,
                      sum(a.nilaisaldoawal) as sawalrp,
                        b.namabarang,b.satuan    
                        from " . $dbname . ".log_5saldobulanan a
                        left join " . $dbname . ".log_5masterbarang b
                        on a.kodebarang=b.kodebarang
                      where kodeorg='" . $pt . "' 
                      and periode='" . $periode . "' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
                      group by a.kodebarang order by a.kodebarang";
} else {
    if ($pt == '') {
        exit("Error: Perusahaan Tidak Boleh Kosong");
    }
    $exgdg = substr($gudang,0,4);
    $str = "select
				a.kodeorg,
				a.kodebarang,
				sum(a.saldoakhirqty) as salakqty,
				a.hargarata as harat,
				sum(a.nilaisaldoakhir) as salakrp,
				sum(a.qtymasuk) as masukqty,
				sum(a.qtykeluar) as keluarqty,
				sum(a.qtymasukxharga) as masukrp,
				sum(a.qtykeluarxharga) as keluarrp,
				sum(a.saldoawalqty) as sawalqty,
				a.hargaratasaldoawal as sawalharat,
				sum(a.nilaisaldoawal) as sawalrp,
				b.namabarang,b.satuan 		 		      
				from " . $dbname . ".log_5saldobulanan a
				left join " . $dbname . ".log_5masterbarang b
				on a.kodebarang=b.kodebarang
				where kodeorg='" . $pt . "' 
				and periode='" . $periode . "'
				and kodegudang like '" . $exgdg . "%'  and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
				group by a.kodebarang 
				order by a.kodebarang";
}
//exit("error: ".$str);
//=================================================
$salakqty = 0;
$harat = 0;
$salakrp = 0;
$masukqty = 0;
$keluarqty = 0;
$masukrp = 0;
$keluarrp = 0;
$sawalQTY = 0;
$sawalharat = 0;
$sawalrp = 0;
$namabarang = 0;

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);
$no = 0;

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

if ($numrows < 1) {
    $tab.="<tr class=rowcontent><td colspan=17>" . $_SESSION['lang']['tidakditemukan'] . "</td></tr>";
} else {
    $totkeluarrp = $totmasukrp = $totsawalrp = $totsalakrp = 0;
    while ($bar = $res->fetch()) {
        $no+=1;
        $kodebarang = $bar->kodebarang;
        $namabarang = $bar->namabarang;
        $salakqty = $bar->salakqty;
        $salakrp = $bar->salakrp;
        $masukqty = $bar->masukqty;
        $keluarqty = $bar->keluarqty;
        $masukrp = $bar->masukrp;
        $keluarrp = $bar->keluarrp;
        $sawalQTY = $bar->sawalqty;
        $sawalrp = $bar->sawalrp;

        @$sawalharat = $bar->sawalrp / $bar->sawalqty;
        @$haratmasuk = $bar->masukrp / $bar->masukqty;
        @$haratkeluar = $bar->keluarrp / $bar->keluarqty;
        @$harat = $bar->salakrp / $bar->salakqty;
		
		
		
        if (isset($unitDt)) {//ini dari tab laporan stok per unit (tab 3)
            $tab.="<tr class=rowcontent> ";
        } else {
            echo"<tr class=rowcontent  style='cursor:pointer;' title='Click' onclick=\"detailMutasiBarangHargaExcel(event,'" . $pt . "','" . $periode . "','" . $gudang . "','" . $kodebarang . "','" . $namabarang . "','" . $bar->satuan . "','log_laporanMutasiDetailPerBarangHarga_Excel.php');\"> ";
        }

		$tab.="<td style='width:50px;'>" . $no . "</td>
			<td style='width:100px;'>" . $periode . "</td>
			<td style='width:100px;'>" . $kodebarang . "</td>
			<td style='width:300px;'>" . $namabarang . "</td>
			<td style='width:50px;'>" . $bar->satuan . "</td>
			<td align=right class=firsttd style='width:100px;'>" . hidezerodecimalv2($sawalQTY, 2, '.', ',') . "</td>
			<td align=right style='width:100px;'>" . hidezerodecimalv2($sawalharat, 2, '.', ',') . "</td>
			<td align=right style='width:100px;'>" . hidezerodecimalv2($sawalrp, 2, '.', ',') . "</td>
			<td align=right class=firsttd style='width:100px;'>" . hidezerodecimalv2($masukqty, 2, '.', ',') . "</td>
			<td align=right style='width:100px;'>" . hidezerodecimalv2($haratmasuk, 2, '.', ',') . "</td>
			<td align=right style='width:100px;'>" . hidezerodecimalv2($masukrp, 2, '.', ',') . "</td>
			<td align=right class=firsttd style='width:100px;'>" . hidezerodecimalv2($keluarqty, 2, '.', ',') . "</td>
			<td align=right style='width:100px;'>" . hidezerodecimalv2($haratkeluar, 2, '.', ',') . "</td>
			<td align=right style='width:100px;'>" . hidezerodecimalv2($keluarrp, 2, '.', ',') . "</td>
			<td align=right class=firsttd style='width:100px;'>" . hidezerodecimalv2($salakqty, 2, '.', ',') . "</td>
			<td align=right style='width:100px;'>" . hidezerodecimalv2($harat, 2, '.', ',') . "</td>
			<td align=right style='width:100px;'>" . hidezerodecimalv2($salakrp, 2, '.', ',') . "</td>			   
		</tr>";

        //while total
        $totsawalrp+=$sawalrp;
        $totmasukrp+=$masukrp;
        $totkeluarrp+=$keluarrp;
        $totsalakrp+=$salakrp;
    }

    $tab.="<tr class=rowcontent>";
    $tab.="<td colspan=4 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
    $tab.="<td colspan=3></td>";
    $tab.="<td colspan align=right><b>" . hidezerodecimalv2($totsawalrp, 0) . "</b></td>";
    $tab.="<td colspan=2></td>";
    $tab.="<td colspan align=right><b>" . hidezerodecimalv2($totmasukrp, 0) . "</b></td>";
    $tab.="<td colspan=2></td>";
    $tab.="<td colspan align=right><b>" . hidezerodecimalv2($totkeluarrp, 0) . "</b></td>";
    $tab.="<td colspan=2></td>";
    $tab.="<td colspan align=right><b>" . hidezerodecimalv2($totsalakrp, 0) . "</b></td>";
    $tab.="</tr>";
	
	
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