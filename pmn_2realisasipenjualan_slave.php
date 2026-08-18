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

# Nama KUD
$kud = makeOption($dbname,"kebun_5namakud","afdeling,kodesupplier");
$kudsupplier = makeOption($dbname,"log_5supplier","supplierid,namasupplier");


switch($proses) {
    case 'preview':

        # {Prepare}
        $getdata = "OBJECT";
        
        # Get
        if($param['kodeunit'] == '') {
            $arrunit = getOrgDetail(2);
        } else {
            $arrunit = "'".$param['kodeunit']."'";
        }
        
        $sql = "SELECT kodeorganisasi, inti, tipe FROM ".$dbname.".organisasi WHERE kodeorganisasi IN (".$arrunit.")";
        $res = fetchData($sql,$getdata);

        foreach($res as $val):
            if($val->inti == 0) {
                $unitplasma[$val->kodeorganisasi] = $val->kodeorganisasi;
            } else {
                if($val->tipe != 'HOLDING') {
                    $unitinti[$val->kodeorganisasi] = $val->kodeorganisasi;
                }
            }
        endforeach;
        # End Get

        # Cek Filter
        $where = "";
        if($param['kodeunit'] != '') {
            $where .= " AND kodeorg='".$param['kodeunit']."'";
        } else {
            $where .= " AND kodeorg IN ('".implode("','",$unitinti)."')";
        }

        $where .= " AND periode='".$param['tahun']."'";

        # Periode
        $periode = $param['tahun']."-01";
        $periodesd = $param['tahun']."-12";
        $rangeperiode = month_inbetween($periode,$periodesd);

        // echo "<pre>";
        // print_r($rangeperiode);

        # Data Penjualan { Penagihan }
        $sql = "SELECT a.periode,a.noinvoice,sum(kgbruto) as kgbruto,sum(kgnetto) as kgnetto, sum(kgpotongan) as potongan, sum(totalrp) as totalrp, rpkg FROM ".$dbname.".keu_penagihanht a LEFT JOIN ".$dbname.".keu_penagihandt b ON a.noinvoice=b.noinvoice WHERE tanggal like '".$param['tahun']."%'";
        $res = fetchData($sql,$getdata);
        foreach($res as $v):
            $pkgbruto[$v->periode]['ar'] = $v->kgbruto;
            $pkgnetto[$v->periode]['ar'] = $v->kgnetto;
            $pkgpotongan[$v->periode]['ar'] = $v->potongan;
            $ptotalrp[$v->periode]['ar'] = $v->totalrp;
            $prpkg[$v->periode]['ar'] = $v->rpkg;
            $ptotalrp[$v->periode]['ar'] = $v->totalrp;
        endforeach;

        // echo "<pre>";
        // print_r($arrdata);
        
        if($param['tipe'] == 'excel') {
            $entitas = "border=1";
        } else {
            $entitas = "border='0' width='100%' cellpadding='5' cellspacing='1'";
        }

        $stream .= "<table class='sortable' ".$entitas.">";

            $stream .= "<thead>";
                $stream .= "<tr class=rowheader>";
                    $stream .= "<th align=center rowspan=2>".$_SESSION['lang']['bulan']."</th>";
                    $stream .= "<th align=center colspan=6>RAT</th>";
                    $stream .= "<th align=center colspan=6>Realisasi</th>";
                    $stream .= "<th align=center colspan=3>Var</th>";
                $stream .= "</tr>";
                
                $stream .= "<tr class=rowheader>";
                    # Rat
                    $stream .= "<td align=center>Bruto - Kg</td>";
                    $stream .= "<td align=center>Grading</td>";
                    $stream .= "<td align=center>%</td>";
                    $stream .= "<td align=center>Netto - Kg</td>";
                    $stream .= "<td align=center>Harga Sat</td>";
                    $stream .= "<td align=center>Nominal - Rp</td>";

                    # Realisasi
                    $stream .= "<td align=center>Bruto - Kg</td>";
                    $stream .= "<td align=center>Grading</td>";
                    $stream .= "<td align=center>%</td>";
                    $stream .= "<td align=center>Netto - Kg</td>";
                    $stream .= "<td align=center>Harga Sat</td>";
                    $stream .= "<td align=center>Nominal (Excl Tax)</td>";

                    # Var
                    $stream .= "<td align=center>Qty</td>";
                    $stream .= "<td align=center>Harga</td>";
                    $stream .= "<td align=center>Nominal</td>";
                $stream .= "</tr>";
            $stream .= "</thead>";

            # CONTENT #
            $stream .= "<tbody>";
                $no = 0;
                foreach($rangeperiode as $bulan => $b):
                    $stream .= "<tr class=rowcontent>";
                        $stream .= "<td align=center>".numToMonth(substr($bulan,5,2),'I','short')."-".substr($bulan,0,4)."</td>";
                        # Rat
                        $stream .= "<td align=right>".number_format(0)."</td>";
                        $stream .= "<td align=right>".number_format(0)."</td>";
                        $stream .= "<td align=right>".hidezerodecimal(0,2)."%</td>";
                        $stream .= "<td align=right>".number_format(0)."</td>";
                        $stream .= "<td align=right>".number_format(0)."</td>";
                        $stream .= "<td align=right>".number_format(0)."</td>";
                        # Realisasi
                        $stream .= "<td align=right>".hidezerodecimal($pkgbruto[$bulan]['ar'],2)."</td>";
                        $stream .= "<td align=right>".hidezerodecimal($pkgpotongan[$bulan]['ar'],2)."</td>";
                        $stream .= "<td align=right>".hidezerodecimal(fixnan($pkgpotongan[$bulan]['ar']/$pkgbruto[$bulan]['ar']),2)."%</td>";
                        $stream .= "<td align=right>".hidezerodecimal($pkgnetto[$bulan]['ar'],2)."</td>";
                        $stream .= "<td align=right>".hidezerodecimal($prpkg[$bulan]['ar'],2)."</td>";
                        $stream .= "<td align=right>".hidezerodecimal($ptotalrp[$bulan]['ar'],2)."</td>";

                        # Total Realisasi
                        $totalkgbrutto['ar']+=$pkgbruto[$bulan]['ar']; # Brutto
                        $totalgrading['ar']+=$pkgpotongan[$bulan]['ar']; # Potongan (Grading)
                        $totalkgnetto['ar']+=$pkgnetto[$bulan]['ar']; # Netto
                        $totalnominal['ar']+=$ptotalrp[$bulan]['ar']; # Total Rupiah
                        $totalpersen['ar']=($totalgrading['ar']/$totalkgbrutto['ar']); # Persen
                        $totalhargaperkg['ar']=($totalnominal['ar']/$totalkgnetto['ar']); # Harga per Kg

                        # Var
                        $stream .= "<td align=right>".number_format(0)."%</td>";
                        $stream .= "<td align=right>".number_format(0)."%</td>";
                        $stream .= "<td align=right>".number_format(0)."%</td>";
                    $stream .= "</tr>";
                endforeach;

                # Hitung Var
                $totalqtyvar['var']=fixnan($totalkgbrutto['ar']/$totalkgbrutto['rat']);
                $totalrpvar['var']=fixnan($totalhargaperkg['ar']/$totalhargaperkg['rat']);
                $totalnominalvar['var']=fixnan($totalnominal['ar']/$totalnominal['rat']);

                # Total
                $stream .= "<tr class=rowcontent>";
                    $stream .= "<td align=center><b>Total</b></td>";
                    # Rat
                    $stream .= "<td align=right><b>".number_format(0)."</b></td>";
                    $stream .= "<td align=right><b>".number_format(0)."</b></td>";
                    $stream .= "<td align=right><b>".hidezerodecimal(0,2)."%</b></td>";
                    $stream .= "<td align=right><b>".number_format(0)."</b></td>";
                    $stream .= "<td align=right><b>".number_format(0)."</b></td>";
                    $stream .= "<td align=right><b>".number_format(0)."</b></td>";
                    # Realisasi
                    $stream .= "<td align=right><b>".hidezerodecimal($totalkgbrutto['ar'],2)."</b></td>";
                    $stream .= "<td align=right><b>".hidezerodecimal($totalgrading['ar'],2)."</b></td>";
                    $stream .= "<td align=right><b>".hidezerodecimal($totalpersen['ar'],2)."%</b></td>";
                    $stream .= "<td align=right><b>".hidezerodecimal($totalkgnetto['ar'],2)."</b></td>";
                    $stream .= "<td align=right><b>".hidezerodecimal($totalhargaperkg['ar'],2)."</b></td>";
                    $stream .= "<td align=right><b>".hidezerodecimal($totalnominal['ar'],2)."</b></td>";
                    # Var
                    $stream .= "<td align=right><b>".hidezerodecimal($totalqtyvar['var'],2)."%</b></td>";
                    $stream .= "<td align=right><b>".hidezerodecimal($totalrpvar['var'],2)."%</b></td>";
                    $stream .= "<td align=right><b>".hidezerodecimal($totalnominalvar['var'],2)."%</b></td>";
                $stream .= "</tr>";

            $stream .= "<tbody>";

        $stream .= "</table>";

        echo $stream;
    break;

}

?>