<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt = isset($_GET['pt']) ? $_GET['pt'] : '';
$gudang = isset($_GET['gudang']) ? $_GET['gudang'] : '';
$periode = isset($_GET['periode']) ? $_GET['periode'] : '';
$stream = '';
//=======================================	
if (isset($_GET['unitDt'])) {//ini dari tab laporan stok per unit (tab 3)
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
                      where kodegudang like '" . $_GET['unitDt'] . "%' 
                      and periode='" . $periode . "' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
                      group by a.kodebarang order by a.kodebarang";
} else if ($gudang == '') {
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
$stream.=$_SESSION['lang']['laporanstok'] . ": " . $pt . "-" . $gudang . ":" . $periode . "<br>    
        <table border=1>
                <tr>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >No.</td>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['periode'] . "</td>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kodebarang'] . "</td>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['namabarang'] . "</td>
                  <td rowspan=2 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['satuan'] . "</td>
                  <td colspan=3 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['saldoawal'] . "</td>
                  <td colspan=3 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['masuk'] . "</td>
                  <td colspan=3 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['keluar'] . "</td>
                  <td colspan=3 align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['saldo'] . "</td>
                </tr>
                <tr>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kuantitas'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['hargasatuan'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['totalharga'] . "</td>	   
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kuantitas'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['hargasatuan'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['totalharga'] . "</td>	   
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kuantitas'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['hargasatuan'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['totalharga'] . "</td>	   
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['kuantitas'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['hargasatuan'] . "</td>
                   <td align=center bgcolor=#DEDEDE >" . $_SESSION['lang']['totalharga'] . "</td>	   
                </tr>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;

$gsawalrp=0;
$gmasukrp=0;
$gkeluarrp=0;
$gsalakrp=0;

while ($bar = $res->fetch()) {
    $no+=1;
    $kodebarang = $bar->kodebarang;
    $namabarang = $bar->namabarang;


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

    $stream.="<tr>
                          <td>" . $no . "</td>
                          <td>" . $periode . "</td>
                          <td>" . $kodebarang . "</td>
                          <td>" . $namabarang . "</td>
                          <td>" . $bar->satuan . "</td>
                           <td align=right class=firsttd>" . number_format($sawalQTY, 2, '.', '') . "</td>
                           <td align=right>" . number_format($sawalharat, 2, '.', '') . "</td>
                           <td align=right>" . number_format($sawalrp, 2, '.', '') . "</td>
                           <td align=right class=firsttd>" . number_format($masukqty, 2, '.', '') . "</td>
                           <td align=right>" . number_format($haratmasuk, 2, '.', '') . "</td>
                           <td align=right>" . number_format($masukrp, 2, '.', '') . "</td>
                           <td align=right class=firsttd>" . number_format($keluarqty, 2, '.', '') . "</td>
                           <td align=right>" . number_format($haratkeluar, 2, '.', '') . "</td>
                           <td align=right>" . number_format($keluarrp, 2, '.', '') . "</td>
                           <td align=right class=firsttd>" . number_format($salakqty, 2, '.', '') . "</td>
                           <td align=right>" . number_format($harat, 2, '.', '') . "</td>
                           <td align=right>" . number_format($salakrp, 2, '.', '') . "</td>			   
                        </tr>";


    // Tambahan Total --- 
    $gsawalrp+=$sawalrp;
    $gmasukrp+=$masukrp;
    $gkeluarrp+=$keluarrp;
    $gsalakrp+=$salakrp;
}

$stream.="
<tr class=rowcontent>
<td colspan=7>TOTAL</td>
<td align=right>" . number_format($gsawalrp, 2) . "</td><td><td></td></td>
<td align=right>" . number_format($gmasukrp, 2) . "</td><td><td></td></td>
<td align=right>" . number_format($gkeluarrp, 2) . "</td><td><td></td></td>
<td align=right>" . number_format($gsalakrp, 2) . "</td>


</tr>";

$stream.="</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];

$nop_ = "MaterialBalanceWPrice";
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
?>