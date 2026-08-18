<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');

use Dompdf\Dompdf;


# INSIALISASI
$stream = '';
$entitas = '';
$rupiahplasma = array();

$proses = checkPostGet('proses','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}

#==============#
# MAKE OPTION
#==============#
$nmkegiatan = makeOption($dbname,"setup_kegiatan","kodekegiatan,namakegiatan");
$nmakun = makeOption($dbname,"keu_5akun","noakun,namaakun");
$nikkaryawan = makeOption($dbname,"datakaryawan","karyawanid,nik");
$nmkaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan");
$lokasitugaskaryawan = makeOption($dbname,"datakaryawan","karyawanid,lokasitugas");
$nmorganisasi = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi");
$nmbarang = makeOption($dbname,"log_5masterbarang","kodebarang,namabarang");
$namasupplier = makeOption($dbname,"log_5supplier","supplierid,namasupplier");
$badanusaha = makeOption($dbname,"log_5supplier","supplierid,badanusaha");

# Nama KUD
$kud = makeOption($dbname,"kebun_5namakud","afdeling,kodesupplier");
$kudsupplier = makeOption($dbname,"log_5supplier","supplierid,namasupplier");


switch($proses) {
    case 'preview':

        # Get
        if($param['kodeunit'] == '') {
            $arrunit = getOrgDetail(2);
        } else {
            $arrunit = "'".$param['kodeunit']."'";
        }
        
        $sql = "SELECT kodeorganisasi, inti, tipe FROM ".$dbname.".organisasi WHERE kodeorganisasi IN (".$arrunit.")";
        $res = fetchData($sql,"OBJECT");

        foreach($res as $val):
            $listunit[$val->kodeorganisasi] = $val->kodeorganisasi;
        endforeach;

        # Cek Filter
        $where = "";
        $wherepo = "";
        $whereinv = "";
        if($param['kodeunit'] != '') {
            $wherepo .= " AND kodeunit='".$param['kodeunit']."'";
            $whereinv .= " AND unit='".$param['kodeunit']."'";
        } else {
            $wherepo .= " AND kodeunit IN ('".implode("','",$listunit)."')";
            $whereinv .= " AND unit IN ('".implode("','",$listunit)."')";
        }

        if($param['supplier'] != '') {
            $wherepo = " AND kodesupplier='".$param['supplier']."'";
            $whereinv = " AND kodesupplier='".$param['supplier']."'";
        }

        $wherepo .= " AND tanggal LIKE '".$param['periode']."%'";
        $whereinv .= " AND tanggal LIKE '".$param['periode']."%'";


        # Get Data
        $sql = "SELECT * FROM ".$dbname.".log_po_vw WHERE 5=5 ".$wherepo." order by kodesupplier asc";
        $res = fetchData($sql);

        foreach($res as $val):
            $arrpo[$val['nopo']] = $val['nopo'];
            $tanggalpo[$val['nopo']] = $val['tanggal'];
            $supplierpo[$val['nopo']] = $val['kodesupplier'];
        endforeach;

        $sql = selectQuery($dbname,"keu_tagihan_vw","*","5=5".$whereinv);
        $res = fetchData($sql);

        foreach($res as $val):
            $arrinv[$val['nopo']] = $val['noinvoice'];
            $tanggalinv[$val['nopo']] = $val['tanggal'];
            $nilaiinv[$val['nopo']] += $val['nilai'];
        endforeach;

        $filterstatus = $param['statuslaporan'];
        $totalinv = 0;
        
        if($param['tipe'] == 'excel') {
            $stream .= "<b>REKAP PEMBAYARAN INVOICE</b>";
            $stream .= "<br/>";
            $stream .= "<b>BULAN ".$param['periode']."</b>";
            $stream .= "<br/><br/>";

            $entitas = "border=1";
        } else {
            $entitas = "border='0' width='100%' cellpadding='2' cellspacing='1' style='background:skyblue;'";
        }

        $stream .= "<table class='sortable' ".$entitas.">";

            $stream .= "<thead>";
                $stream .= "<tr class=rowheader>";
                    $stream .= "<th align=center>".$_SESSION['lang']['nourut']."</th>";
                    $stream .= "<th align=center>".$_SESSION['lang']['nopo']."</th>";
                    $stream .= "<th align=center>Tanggal PO</th>";
                    $stream .= "<th align=center>Nomor Invoice</th>";
                    $stream .= "<th align=center>Tanggal Invoice</th>";
                    $stream .= "<th align=center>".$_SESSION['lang']['jumlah']."</th>";
                    $stream .= "<th align=center>".$_SESSION['lang']['keterangan']."</th>";
                $stream .= "</tr>";
            $stream .= "</thead>";

            # CONTENT #
            $stream .= "<tbody>";
                $no = 0;
                foreach($arrpo as $nopo => $val) {
                    $no++;

                    if($filterstatus == "1") {
                        $stream .= "<tr class=rowcontent>";
                            $stream .= "<td align=center>".$no."</td>";
                            $stream .= "<td align=center>".$nopo."</td>";
                            $stream .= "<td align=center>".tanggalnormal($tanggalpo[$nopo])."</td>";
                            $stream .= "<td align=center>".$arrinv[$nopo]."</td>";
                            $stream .= "<td align=center>".($tanggalinv[$nopo] == '' ? '' : tanggalnormal($tanggalinv[$nopo]))."</td>";
                            $stream .= "<td align=right>".number_format($nilaiinv[$nopo],2)."</td>";
                            $stream .= "<td align=left>".$badanusaha[$supplierpo[$nopo]].". ".$namasupplier[$supplierpo[$nopo]]."</td>";
                        $stream .= "</tr>";

                        # Sum Total
                        $totalinv += $nilaiinv[$nopo];
                    } else if($filterstatus == "2") {
                        if($arrinv[$nopo] != '') {
                            $stream .= "<tr class=rowcontent>";
                                $stream .= "<td align=center>".$no."</td>";
                                $stream .= "<td align=center>".$nopo."</td>";
                                $stream .= "<td align=center>".tanggalnormal($tanggalpo[$nopo])."</td>";
                                $stream .= "<td align=center>".$arrinv[$nopo]."</td>";
                                $stream .= "<td align=center>".($tanggalinv[$nopo] == '' ? '' : tanggalnormal($tanggalinv[$nopo]))."</td>";
                                $stream .= "<td align=right>".number_format($nilaiinv[$nopo],2)."</td>";
                                $stream .= "<td align=left>".$badanusaha[$supplierpo[$nopo]].". ".$namasupplier[$supplierpo[$nopo]]."</td>";
                            $stream .= "</tr>";

                            # Sum Total
                            $totalinv += $nilaiinv[$nopo];
                        } 
                    } else if($filterstatus == "3") {
                        if($arrinv[$nopo] == '') {
                            $stream .= "<tr class=rowcontent>";
                                $stream .= "<td align=center>".$no."</td>";
                                $stream .= "<td align=center>".$nopo."</td>";
                                $stream .= "<td align=center>".tanggalnormal($tanggalpo[$nopo])."</td>";
                                $stream .= "<td align=center>".$arrinv[$nopo]."</td>";
                                $stream .= "<td align=center>".($tanggalinv[$nopo] == '' ? '' : tanggalnormal($tanggalinv[$nopo]))."</td>";
                                $stream .= "<td align=right>".number_format($nilaiinv[$nopo],2)."</td>";
                                $stream .= "<td align=left>".$badanusaha[$supplierpo[$nopo]].". ".$namasupplier[$supplierpo[$nopo]]."</td>";
                            $stream .= "</tr>";

                            # Sum Total
                            $totalinv += $nilaiinv[$nopo];
                        } 
                    }
                }

                # Jumlah
                $stream .= "<tr class=rowcontent>";
                    $stream .= "<td align=center colspan=5><b>Jumlah</b></td>";
                    $stream .= "<td align=right><b>".number_format($totalinv,2)."</b></td>";
                    $stream .= "<td align=right></td>";
                $stream .= "</tr>";
            $stream .= "<tbody>";

        $stream .= "</table>";

        if($param['tipe']=='excel') {
            $nop = "Printout_excel.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("data", $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
        } else {
            echo $stream;
        }
    break;
}

?>