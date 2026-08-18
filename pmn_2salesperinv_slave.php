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

        # Data Customer
        $sql = "select * from ".$dbname.".pmn_4customer a left join ".$dbname.".pmn_4komoditi b on a.kodecustomer=b.kodecustomer where b.kodekomoditi='TBS'";
        $res = fetchdata($sql,$getdata);
        foreach($res as $v):
            $arrkdcust[$v->kodecustomer] = $v->kodecustomer;
            $nmcust[$v->kodecustomer] = $v->namacustomer;
        endforeach;

        # Range Periode
        foreach($rangeperiode as $bulan => $b):
            foreach($arrkdcust as $kdcust => $k):
                $arrkdcustnew[$kdcust][$bulan] = $kdcust;
            endforeach;
        endforeach;

        # Data Penjualan { Penagihan }
        $sql = "SELECT a.periode,a.noinvoice,sum(kgbruto) as kgbruto,sum(kgnetto) as kgnetto, sum(kgpotongan) as potongan, sum(totalrp) as totalrp, rpkg, kodecustomer, tahuntanam, tanggal FROM ".$dbname.".keu_penagihanht a LEFT JOIN ".$dbname.".keu_penagihandt b ON a.noinvoice=b.noinvoice WHERE tanggal like '".$param['tahun']."%' GROUP BY noinvoice, kodecustomer, tahuntanam";
        $res = fetchData($sql,$getdata);
        foreach($res as $v):
            # Data Looping
            $arrdatanew[$v->periode][$v->kodecustomer] = $v->kodecustomer;
            $arrdata[$v->periode][$v->kodecustomer][$v->noinvoice][$v->tahuntanam] = $v->kodecustomer;
            $tanggalinv[$v->periode][$v->kodecustomer] = $v->tanggal;

            # Invoice
            $arrinv[$v->periode][$v->kodecustomer][$v->noinvoice]= $v->noinvoice;

            # Get Data
            $nomorinv[$v->noinvoice] = $v->noinvoice;
            $tahuntanam[$v->tahuntanam] = $v->tahuntanam;
            
            $ttnya[$v->periode][$v->kodecustomer][$v->noinvoice][$v->tahuntanam] = $v->tahuntanam;

            $pkgbruto[$v->periode][$v->kodecustomer][$v->noinvoice][$v->tahuntanam] = $v->kgbruto;
            $pkgnetto[$v->periode][$v->kodecustomer][$v->noinvoice][$v->tahuntanam] = $v->kgnetto;
            $pkgpotongan[$v->periode][$v->kodecustomer][$v->noinvoice][$v->tahuntanam] = $v->potongan;
            $ptotalrp[$v->periode][$v->kodecustomer][$v->noinvoice][$v->tahuntanam] = $v->totalrp;
            $prpkg[$v->periode][$v->kodecustomer][$v->noinvoice][$v->tahuntanam] = $v->rpkg;
            $ptotalrp[$v->periode][$v->kodecustomer][$v->noinvoice][$v->tahuntanam] = $v->totalrp;
            

            # Hitung Customer 
            # yang mempunyai data
            $arrcountcust[$v->periode][$v->kodecustomer]=count($arrdata[$v->periode][$v->kodecustomer]);

            # Perhitungan
            $arrcount[$v->periode]=((count($arrdata[$v->periode][$v->kodecustomer][$v->noinvoice])+1)*$arrcountcust[$v->periode][$v->kodecustomer]);
        endforeach;

        
        // echo "<pre>";
        // print_r($ttnya);

        
        if($param['tipe'] == 'excel') {
            $entitas = "border=1";
        } else {
            $entitas = "border='0' width='100%' cellpadding='3' cellspacing='1'";
        }

        $stream .= "<table class='sortable' ".$entitas.">";

            $stream .= "<thead>";
                $stream .= "<tr class=rowheader>";
                    $stream .= "<th align=center rowspan=4>".$_SESSION['lang']['bulan']."</th>";
                    foreach($arrkdcust as $kdcust => $nmcust):
                        $stream .= "<th align=center colspan=9>PT. ".$kdcust."</th>";
                    endforeach;
                $stream .= "</tr>";
                
                $stream .= "<tr class=rowheader>";
                    foreach($arrkdcust as $kdcust => $nmcust):
                        $stream .= "<td align=center>".$_SESSION['lang']['tanggalinvoice']."</td>";
                        $stream .= "<td align=center>".$_SESSION['lang']['noinvoice']."</td>";
                        $stream .= "<td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
                        $stream .= "<td align=center>".$_SESSION['lang']['kuantitas']."</td>";
                        $stream .= "<td align=center>".$_SESSION['lang']['harga']."</td>";
                        $stream .= "<td align=center>".$_SESSION['lang']['jumlah']."</td>";
                        $stream .= "<td align=center>".$_SESSION['lang']['ppn']."</td>";
                        $stream .= "<td align=center>".$_SESSION['lang']['pph']." 22</td>";
                        $stream .= "<td align=center>Net Penjualan</td>";

                    endforeach;
                $stream .= "</tr>";

            $stream .= "</thead>";

            # CONTENT #
            $stream .= "<tbody>";
                $no = 0;

                foreach($rangeperiode as $bulan => $val):

                    if($arrcount[$bulan]!=''):
                        $stream .= "<tr class=rowcontent>";
                            $stream .= "<td align=center rowspan=".($arrcount[$bulan]+1).">".numToMonth(substr($bulan,5,2),'I','long')."</td>";
                        $stream .= "</tr>";
                    else:
                        $stream .= "<tr class=rowcontent>";
                            $stream .= "<td align=center rowspan=2>".numToMonth(substr($bulan,5,2),'I','long')."</td>";
                        $stream .= "</tr>";
                    endif;

                    $stream .= "<tr class=rowcontent>";
                        foreach($arrkdcust as $kdcust => $vals):
                            $stream .= "<td align=center rowspan=".($arrcount[$bulan]).">".($tanggalinv[$bulan][$kdcust] == '' ? '' : tanggalnormal($tanggalinv[$bulan][$kdcust]))."</td>";
                            
                            foreach($nomorinv as $noinv => $vali):
                                $stream .= "<td align=center rowspan=".($arrcount[$bulan]).">".$arrinv[$bulan][$kdcust][$noinv]."</td>";
                                
                                // foreach($tahuntanam as $tt => $valt):
                                    // $stream .= "<td align=center>".$kdcust."x1</td>";
                                    // $stream .= "<td align=center>".$kdcust."x2</td>";
                                    // $stream .= "<td align=center>".$ttnya[$bulan][$kdcust][$noinv][$tt]."x3</td>";
                                    // $stream .= "<td align=center>".$kdcust."x3</td>";
                                    // $stream .= "<td align=center>".$kdcust."x4</td>";
                                    // $stream .= "<td align=center>".$kdcust."x5</td>";
                                    // $stream .= "<td align=center>".$kdcust."x6</td>";
                                    // $stream .= "<td align=center>".$kdcust."x7</td>";
                                    // $stream .= "<td align=center>".$kdcust."x8</td>";
                                    // $stream .= "<td align=center>".$kdcust."x9</td>";
                                // endforeach;
                                    
                                if(!empty($arrdata[$bulan][$kdcust])): # Tidak Ada Datanya
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                else: # Ada datanya
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                    $stream .= "<td align=center></td>";
                                endif;

                            endforeach;
                        endforeach;
                    $stream .= "</tr>";
                    
                    
                    foreach($arrkdcust as $kdcust => $vals):
                        foreach($ttnya[$bulan][$kdcust] as $noinv => $vali):
                            foreach($tahuntanam as $tt => $valt):
                                # Get PPN
                                $ppn[$bulan][$kdcust][$noinv][$tt] = $ptotalrp[$bulan][$kdcust][$noinv][$tt]*0.11;
                                $pph[$bulan][$kdcust][$noinv][$tt] = $ptotalrp[$bulan][$kdcust][$noinv][$tt]*0.25/100;
                                $totalnetto[$bulan][$kdcust][$noinv][$tt] = ($ptotalrp[$bulan][$kdcust][$noinv][$tt]-$pph[$bulan][$kdcust][$noinv][$tt]);

                                $stream .= "<tr class=rowcontent>";
                                    $stream .= "<td align=center>".$ttnya[$bulan][$kdcust][$noinv][$tt]."</td>";
                                    $stream .= "<td align=right>".number_format($pkgnetto[$bulan][$kdcust][$noinv][$tt])."</td>";
                                    $stream .= "<td align=right>".number_format($prpkg[$bulan][$kdcust][$noinv][$tt])."</td>";
                                    $stream .= "<td align=right>".number_format($ptotalrp[$bulan][$kdcust][$noinv][$tt])."</td>";
                                    $stream .= "<td align=right>".number_format($ppn[$bulan][$kdcust][$noinv][$tt])."</td>";
                                    $stream .= "<td align=right>".number_format($pph[$bulan][$kdcust][$noinv][$tt])."</td>";
                                    $stream .= "<td align=right>".number_format($totalnetto[$bulan][$kdcust][$noinv][$tt])."</td>";
                                $stream .= "</tr>";
                            endforeach;
                        endforeach;
                    endforeach;
                    
                endforeach;      
                
                // foreach($arrkdcust as $kdcust => $vals):
                //     if($arrdata[$bulan][$kdcust]==''):
                //         $stream .= "<tr class=rowcontent>";
                //             $stream .= "<td align=center>".$kdcust."x3</td>";
                //             $stream .= "<td align=center>".$kdcust."x4</td>";
                //             $stream .= "<td align=center>".$kdcust."x5</td>";
                //             $stream .= "<td align=center>".$kdcust."x6</td>";
                //             $stream .= "<td align=center>".$kdcust."x7</td>";
                //             $stream .= "<td align=center>".$kdcust."x8</td>";
                //             $stream .= "<td align=center>".$kdcust."x9</td>";
                //         $stream .= "</tr>";
                //     endif;
                // endforeach;
                    
                // foreach($rangeperiode as $bulan => $b):
                    
                //     if($arrdata[$bulan] != ''):
                //         $stream .= "<tr class=rowcontent>";
                //             $stream .= "<td align=center rowspan=".($arrcount[$bulan]+1).">".numToMonth(substr($bulan,5,2),'I','long')."</td>";
                //         $stream .= "</tr>";

                //         // foreach($arrdata[$bulan] as $kdcustv => $vk):
                //         //     foreach($vk as $noinv => $vn):
                //         //             $stream .= "<tr class=rowcontent>";
                //         //                 $stream .= "<td align=center rowspan=".($arrcount[$bulan]).">".tanggalnormal($tanggalinv[$bulan][$kdcustv][$noinv])."</td>";
                //         //                 $stream .= "<td align=center rowspan=".($arrcount[$bulan]).">".$nomorinv[$bulan][$kdcust][$noinv]."</td>";
                //         //             $stream .= "</tr>";
                //         //     endforeach;
                //         // endforeach;

                //         foreach($arrdata[$bulan] as $kdcustv => $vk):
                //             foreach($vk as $noinv => $vn):
                //                  $stream .= "<tr class=rowcontent>";
                //                         $stream .= "<td align=center rowspan=".($arrcount[$bulan]).">".tanggalnormal($tanggalinv[$bulan][$kdcustv][$noinv])."</td>";
                //                         $stream .= "<td align=center rowspan=".($arrcount[$bulan]).">".$nomorinv[$bulan][$kdcustv][$noinv]."</td>";
                //                     $stream .= "</tr>";

                //                 foreach($vn as $tt => $vt):

                //                     # Get PPN
                //                     $ppn[$bulan][$kdcustv][$noinv][$tt] = $ptotalrp[$bulan][$kdcustv][$noinv][$tt]*0.11;
                //                     $pph[$bulan][$kdcustv][$noinv][$tt] = $ptotalrp[$bulan][$kdcustv][$noinv][$tt]*0.25/100;
                //                     $totalnetto[$bulan][$kdcustv][$noinv][$tt] = ($ptotalrp[$bulan][$kdcustv][$noinv][$tt]-$pph[$bulan][$kdcustv][$noinv][$tt]);
                                    
                //                     $stream .= "<tr class=rowcontent>";
                //                         $stream .= "<td align=center>".$tt."</td>";
                //                         $stream .= "<td align=right>".number_format($pkgnetto[$bulan][$kdcustv][$noinv][$tt])."</td>";
                //                         $stream .= "<td align=right>".number_format($prpkg[$bulan][$kdcustv][$noinv][$tt])."</td>";
                //                         $stream .= "<td align=right>".number_format($ptotalrp[$bulan][$kdcustv][$noinv][$tt])."</td>";
                //                         $stream .= "<td align=right>".number_format($ppn[$bulan][$kdcustv][$noinv][$tt])."</td>";
                //                         $stream .= "<td align=right>".number_format($pph[$bulan][$kdcustv][$noinv][$tt])."</td>";
                //                         $stream .= "<td align=right>".number_format($totalnetto[$bulan][$kdcustv][$noinv][$tt])."</td>";
                //                     $stream .= "</tr>";
                //                 endforeach;
                //             endforeach;
                //         endforeach;
                //     else:
                //         $stream .= "<tr class=rowcontent>";
                //             $stream .= "<td align=center rowspan=2>".numToMonth(substr($bulan,5,2),'I','long')."</td>";
                //         $stream .= "</tr>";

                //         $stream .= "<tr class=rowcontent>";
                //             $stream .= "<td align=center></td>";
                //             $stream .= "<td align=center></td>";
                //             $stream .= "<td align=center></td>";
                //             $stream .= "<td align=center></td>";
                //             $stream .= "<td align=center></td>";
                //             $stream .= "<td align=center></td>";
                //             $stream .= "<td align=center></td>";
                //             $stream .= "<td align=center></td>";
                //             $stream .= "<td align=center></td>";
                //         $stream .= "</tr>";
                //     endif;

                // endforeach;


            $stream .= "<tbody>";

        $stream .= "</table>";

        echo $stream;
    break;

}

?>