<?
error_reporting(0);
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

        # Get
        if($param['kodeunit'] == '') {
            $arrunit = getOrgDetail(2);
        } else {
            $arrunit = "'".$param['kodeunit']."'";
        }
        
        $sqlOrgPlasma = "SELECT kodeorganisasi, inti, tipe FROM ".$dbname.".organisasi WHERE kodeorganisasi IN (".$arrunit.") AND induk='{$param['kodept']}'";
        $res = fetchData($sqlOrgPlasma,"OBJECT");

        foreach($res as $val):
            if($val->inti == 0) {
                $unitplasma[$val->kodeorganisasi] = $val->kodeorganisasi;
            } else {
                if($val->tipe != 'HOLDING') {
                    $unitinti[$val->kodeorganisasi] = $val->kodeorganisasi;
                }
            }
        endforeach;
        
        $jumlahPlasmaOrg = count($unitplasma);
        # End Get

        # Cek Filter
        $where = "";
        if($param['kodeunit'] != '') {
            $where .= " AND kodeorg='".$param['kodeunit']."'";
            $whereka .= " AND kodeorg='".$param['kodeunit']."'";
        } else {
            $where .= " AND kodeorg IN ('".implode("','",$unitplasma)."')";
            $whereka .= " AND kodeorg IN ('".implode("','",$unitplasma)."')";
        }

        $whereka .= " AND tanggal like '".$param['periode']."%'";
        $where .= " AND periode='".$param['periode']."'";

        # ================================================================ #
        # GET DATA
        # ================================================================ #

        # Array Untuk Dapatkan Kodeorg Pemilik Hutang
        // $sql = "select * from ".$dbname.".kebun_aktifitas where 5=5 ".$whereka."";
        // $res = fetchData($sql);
        // foreach($res as $val):
        //     $pemilik[$val['notransaksi']] = $val['kodeorg'];
        // endforeach;

        // $sql = "select * from ".$dbname.".kebun_5namakud";
        // $res = fetchData($sql);
        // foreach($res as $val):
        //     $pemilikkud[$val['noakuninvestasi']] = $val['afdeling'];
        // endforeach;
        $pemilikkud['1210301'] = 'S1PE';
        $pemilikkud['1210302'] = 'S2PE';
        $pemilikkud['1210303'] = 'S3PE';
        $pemilikkud['1210304'] = 'BKPE';
        $pemilikkud['1210305'] = 'MLPE';
        $pemilikkud['1210306'] = 'MUPE';
        


        $sql = "SELECT noakun,jumlah, kodeorg, nojurnal
        FROM ".$dbname.".keu_jurnaldt_vw 
        WHERE noakun in ('1210301','1210302','1210303','1210304','1210305','1210306') AND kodeorg IN ('".implode("','",$unitplasma)."') AND periode='".$param['periode']."' ";
        $res = fetchData($sql,"OBJECT");
        foreach($res as $val):
            if($pemilikkud[$val->noakun] != $val->kodeorg) {
                    $dataplasma[$val->unitplasma] = $val->unitplasma;
                if($val->jumlah < 0) {
                        $rupiahhutangplasma[$val->kodeorg][$pemilikkud[$val->noakun]] += $val->jumlah;
                        $rupiahhutangplasmax[$val->kodeorg][$pemilikkud[$val->noakun]][] = $val->nojurnal;

                }else{

                        $rupiahpiutangplasma[$val->kodeorg][$pemilikkud[$val->noakun]] += $val->jumlah;
                        $rupiahpiutangplasmax[$val->kodeorg][$pemilikkud[$val->noakun]][] = $val->nojurnal;
                    
                }
            }
        endforeach;
            # Debug 13-05-2026
            # Karena ada bug rp/hk lebih besar dari nilai 1hk
            if($_SESSION['standard']['username']=='tim.owl3') {
                echo "===================================";
                echo "S1PE - BKPE";
                echo "===================================";
                echo '<pre>';
                print_r($rupiahhutangplasma['S1PE']['BKPE']);
                echo '</pre>';

                echo "===================================";
                echo "BKPE - S1PE";
                echo "===================================";
                echo '<pre>';
                print_r($rupiahpiutangplasma['BKPE']['S1PE']);
                echo '</pre>';
            }

        // $sql = "SELECT noakun,jumlah, kodeorg, nojurnal
        // FROM ".$dbname.".keu_jurnaldt_vw 
        // WHERE noakun in ('1210304') AND kodeorg IN ('S1PE')";
        // $res = fetchData($sql,"OBJECT");
        // foreach($res as $val):
        //     $dataplasma[$val->unitplasma] = $val->unitplasma;
        //     if($pemilikkud[$val->noakun] != $val->kodeorg) {
        //         $rupiahhutangplasma[$val->kodeorg][$pemilikkud[$val->noakun]] += $val->jumlah;
                
        //         $rupiahhutangplasmax[$val->kodeorg][$pemilikkud[$val->noakun]][] = $val->nojurnal;
        //     //}else{
        //         $rupiahpiutangplasma[$pemilikkud[$val->noakun]][$val->kodeorg] += $val->jumlah;
        //         $rupiahpiutangplasmax[$pemilikkud[$val->noakun]][$val->kodeorg][] = $val->nojurnal;

        //     }
        // endforeach;

        // $sql = "select * from ".$dbname.".keu_jurnaldt_vw where 5=5 and noreferensi='Reclass Piutang Plasma' and kodejurnal='M' and periode='".$param['periode']."'";
        // $res = fetchData($sql);
        // foreach($res as $val):
        //     //if($rupiahhutangplasma[$val['kodeorg']][$pemilikkud[$val['noakun']]] > 0) {
        //         $rupiahhutangplasma[$val['kodeorg']][$pemilikkud[$val['noakun']]] += $val['jumlah'];
        //     //}

        //    //if($rupiahpiutangplasma[$pemilikkud[$val['noakun']]][$val['kodeorg']] > 0) {
        //         $rupiahpiutangplasma[$pemilikkud[$val['noakun']]][$val['kodeorg']] += $val['jumlah'];
        //     //}
        // endforeach;

        // echo "<pre>";
        // print_r($rupiahhutangplasma);

        # Ini berdasarkan akun hutang
        // $sql = "SELECT LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(noreferensi,'/',2),'/',-1),4) as kodeorgasal, LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(nojurnal,'/',2),'/',-1),4) as kodeorgrk, jumlah as rupiahplasma FROM ".$dbname.".keu_jurnaldt_vw WHERE 5=5 AND noakun like '213%' and substr(noakun,1,5) < '21302' AND nik <> '' and noreferensi NOT LIKE 'ALK_POT%' and noreferensi NOT LIKE 'ALK%' and kodejurnal='M' and autojurnal='1' ".$where."";

        # Cek Piutang
        # Ini berdasarkan Akun RK
        // $sql = "SELECT LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(noreferensi,'/',2),'/',-1),4) as kodeorgasal, LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(nojurnal,'/',2),'/',-1),4) as kodeorgrk, jumlah as rupiahplasma, noreferensi FROM ".$dbname.".keu_jurnaldt_vw WHERE 5=5 AND noakun like '12103%' and noreferensi NOT LIKE 'ALK_POT%' and noreferensi NOT LIKE 'ALK%' and kodejurnal='M' AND autojurnal='1' AND noaruskas='' and kodeblok <> '' AND kodebarang='' AND noreferensi NOT LIKE '%GR%'
        // ".$where."";
        // $res = fetchData($sql,"OBJECT");

        // foreach($res as $val):
                        
        //     $dataplasma[$val->unitplasma] = $val->unitplasma;
        //     $rupiahhutangplasma[$val->kodeorgrk][$val->kodeorgasal] += abs($val->rupiahplasma);

        // endforeach;
        


        // # Cek Hutang
        // # Ini berdasarkan Akun RK
        
        // # Cari Dulu noakun Hutang di transaksi Jurnal BKM
        // $c = makeOption($dbname,"keu_jurnaldt_vw","noreferensi,kodeorg","noakun='2130101'");

        // echo $sql = "SELECT LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(noreferensi,'/',2),'/',-1),4) as kodeorgasal, LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(nojurnal,'/',2),'/',-1),4) as kodeorgrk, jumlah as rupiahplasma, noreferensi FROM ".$dbname.".keu_jurnaldt_vw WHERE 5=5 AND noakun like '12103%' and noreferensi NOT LIKE 'ALK_POT%' and noreferensi NOT LIKE 'ALK%' and kodejurnal='M' AND autojurnal='1' AND noaruskas='' and kodeblok <> '' AND kodebarang='' AND noreferensi NOT LIKE '%GR%'
        // ".$where."";
        // $res = fetchData($sql,"OBJECT");

        // foreach($res as $val):
        //     $dataplasma[$val->unitplasma] = $val->unitplasma;
        //     $rupiahpiutangplasma[$c[$val->noreferensi]][$val->kodeorgasal] += abs($val->rupiahplasma);
        // endforeach;

        # Hitung Plasma
        $jumlahPlasma = count($dataplasma);

        # GET RUPIAH PEMAKAIAN BKM
        // $sql = "SELECT kodeorg as unitplasma, noreferensi, noakun, kodekegiatan, kodebarang, SUM(jumlah) as rupiahpemakaianplasma FROM ".$dbname.".keu_jurnaldt_vw WHERE 5=5 AND kodejurnal IN ('INVK1') and (noreferensi LIKE '%TBM%' or noreferensi LIKE '%TM%' or noreferensi LIKE '%TB%') and kodekegiatan='' and kodebarang <> '' GROUP BY unitplasma";
        // $res = fetchData($sql,"OBJECT");

        // foreach($res as $val):
        //     $rupiahplasma[$val->unitplasma][$val->unitplasma] += abs($val->rupiahpemakaianplasma);
        // endforeach;

        # ================================================================ #
        # END GET DATA
        # ================================================================ #

        // echo "<pre>";
        // print_r($pemilik);
        
        if($param['tipe'] == 'excel') {
            $entitas = "border=1";
        } else {
            $entitas = "border='0' width='100%' cellpadding='3' cellspacing='1'";
        }

        $stream .= "<table class='sortable' ".$entitas.">";

            $stream .= "<thead>";
                $stream .= "<tr class=rowheader>";
                    $stream .= "<th align=center rowspan=3>".$_SESSION['lang']['nourut']."</th>";
                    $stream .= "<th align=center rowspan=3>Unit</th>";
                    $stream .= "<th colspan='".($jumlahPlasmaOrg*3)."'>Unit Plasma</th>";
                $stream .= "</tr>";
                
                $stream .= "<tr>";
                    foreach($unitplasma as $up => $val):
                        $stream .= "<th colspan=3>[".$up."] <br/> ".$kudsupplier[$kud[$up]]."</th>";
                    endforeach;
                $stream .= "</tr>";
                
                $stream .= "<tr>";
                    foreach($unitplasma as $up => $val):
                        $stream .= "<th>Hutang</th>";
                        $stream .= "<th>Piutang</th>";
                        $stream .= "<th>Selisih</th>";
                    endforeach;
                $stream .= "</tr>";
            $stream .= "</thead>";

            # CONTENT #
            $stream .= "<tbody>";
                $no = 0;
                foreach($unitplasma as $int => $valint):
                    $no++;
                    $stream .= "<tr class=rowcontent>";
                        $stream .= "<td align=center>".$no."</td>";
                        $stream .= "<td align=left>[".$int."] ".$nmorganisasi[$int]."</td>";
                        foreach($unitplasma as $up => $val):

                            // if($rupiahhutangplasma[$up][$int] > 0) {
                            //     $entitasdetail = "onclick=detaildata('".$int."','".$up."','HUTANG'); style='cursor:pointer;'";
                            // } else if($rupiahpiutangplasma[$up][$int] > 0) {
                            //     $entitasdetail = "onclick=detaildata('".$int."','".$up."','PIUTANG'); style='cursor:pointer;'";
                            // } else {
                            //     $entitasdetail = '';
                            // }

                            $stream .= "<td align=right onclick=detaildata('".$int."','".$up."','".implode(",",$rupiahhutangplasmax[$up][$int])."','HUTANG','".$param['periode']."'); style='cursor:pointer;'>".hidezerodecimal($rupiahhutangplasma[$up][$int],2)."</td>";
                            $stream .= "<td align=right onclick=detaildata('".$int."','".$up."','".implode(",",$rupiahpiutangplasmax[$up][$int])."','PIUTANG','".$param['periode']."'); style='cursor:pointer;'>".hidezerodecimal($rupiahpiutangplasma[$up][$int],2)."</td>";
                            $stream .= "<td align=right>".hidezerodecimal($rupiahhutangplasma[$up][$int]+$rupiahpiutangplasma[$up][$int],2)."</td>";

                            $gtHutang[$up]+=$rupiahhutangplasma[$up][$int];
                            $gtPiutang[$up]+=$rupiahpiutangplasma[$up][$int];
                            $gtSelisih[$up]+=$rupiahhutangplasma[$up][$int]+$rupiahpiutangplasma[$up][$int];
                        endforeach;
                    $stream .= "</tr>";
                endforeach;

                # Grandtotal
                $stream .= "<tr class=rowcontent>";
                    $stream .= "<td colspan=2 align=center><b>Grandtotal</b></td>";
                    foreach($unitplasma as $int => $valint):
                        $stream .= "<td align=right><b>".hidezerodecimal($gtHutang[$int])."</b></td>";
                        $stream .= "<td align=right><b>".hidezerodecimal($gtPiutang[$int])."</b></td>";
                        $stream .= "<td align=right><b>".hidezerodecimal($gtSelisih[$int])."</b></td>";
                    endforeach;
                $stream .= "</tr>";

            $stream .= "<tbody>";
        $stream .= "</table>";

        if($param['tipe'] == 'excel') {
            $nop = "Report Plasma.xls";
            $xls = new HtmlExcel();
            $xls->setCss($css);
            $xls->addSheet('Report Plasma', $stream);
            $xls->headers($nop);
            echo $xls->buildFile();
        } else {
            echo $stream;
        }
    break;

    case 'detaildata':

        # ================================================================ #
        # GET DATA
        # ================================================================ #
        $ex = explode(",",$param['arrdata']);
        
        $pemilikkud2['S1PE'] = '1210301';
        $pemilikkud2['S2PE'] = '1210302';
        $pemilikkud2['S3PE'] = '1210303';
        $pemilikkud2['BKPE'] = '1210304';
        $pemilikkud2['MLPE'] = '1210305';
        $pemilikkud2['MUPE'] = '1210306';

        if($param['tipelaporan'] == 'PIUTANG') {
            $where .= " AND kodeorg='".$param['unitplasma']."' and noakun='".$pemilikkud2[$param['unitinti']]."' ";

        } else {
            $where .= " AND kodeorg='".$param['unitinti']."'  and noakun='".$pemilikkud2[$param['unitplasma']]."' ";
        }
        $whereka .= " AND nojurnal IN ('".implode("','",$ex)."')";

        // $sql = "select * from ".$dbname.".kebun_aktifitas where 5=5 ".$whereka."";
        // $res = fetchData($sql);
        // foreach($res as $val):
        //     $pemilik[$val['notransaksi']] = $val['kodeorg'];
        // endforeach;

        $pemilikkud['1210301'] = 'S1PE';
        $pemilikkud['1210302'] = 'S2PE';
        $pemilikkud['1210303'] = 'S3PE';
        $pemilikkud['1210304'] = 'BKPE';
        $pemilikkud['1210305'] = 'MLPE';
        $pemilikkud['1210306'] = 'MUPE';


        

        $sql = "SELECT noakun,jumlah, kodeorg, nojurnal
        FROM ".$dbname.".keu_jurnaldt_vw 
        WHERE periode='".$param['periode']."' ".$where." ";
        $res = fetchData($sql,"OBJECT");
        foreach($res as $val):
            if($pemilikkud[$val->noakun] != $val->kodeorg) {
                if($param['tipelaporan'] == 'HUTANG') {
                    if($val->jumlah < 0) {
                       $dataplasma[$val->kodeorg][$val->nojurnal] = $val->unitplasma;
                       $rupiahplasma[$val->kodeorg][$val->nojurnal] +=$val->jumlah;
                    }
                } else {
                    if($val->jumlah > 0) {
                        $rupiahplasma[$pemilikkud[$val->noakun]][$val->nojurnal] += $val->jumlah;
                        $dataplasma[$pemilikkud[$val->noakun]][$val->nojurnal] = $val->unitplasma;
                    }
                }
           }
        endforeach;

        // echo "<pre>";
        // print_r($rupiahplasma);

        # Hitung Plasma
        $jumlahPlasma = count($dataplasma);

        # ================================================================ #
        # END GET DATA
        # ================================================================ #
        
        if($param['tipe'] == 'excel') {
            $entitas = "border=1";
        } else {
            $entitas = "border='0' width='100%' cellpadding='5' cellspacing='1'";

            $stream .= "<button onclick=excelDetail('".$param['unitinti']."','".$param['unitplasma']."','".$param['tipelaporan']."','".$param['arrdata']."','".$param['periode']."','excel') style='margin:10px 0;padding:4px 15px;cursor:pointer;border:1px solid green;border-radius:100px;'>Excel</button>";
        }

        $stream .= "<table class='sortable' ".$entitas.">";
             $stream .= "<thead>";
                $stream .= "<tr class=rowheader>";
                    $stream .= "<th style='width:10%'>".$_SESSION['lang']['nourut']."</th>";
                    $stream .= "<th>Nojurnal</th>";
                    $stream .= "<th>Rupiah</th>";
                $stream .= "</tr>";
            $stream .= "</thead>";

            # CONTENT #
            $stream .= "<tbody>";
                $nodetail = 0;
                foreach($dataplasma as $kdorg => $val):
                    if($param['tipe']!='excel'):
                        $stream .= "<tr class=rowcontent style='background-color:#66fab8!important;'>";
                            $stream .= "<td align=left colspan=3><b>".substr($kdorg,0,4)."</b></td>";
                        $stream .= "</tr>";
                    endif;
                    foreach($val as $noref => $valk):
                    $nodetail++;
                        
                        $stream .= "<tr class=rowcontent>";
                            $stream .= "<td align=center>".$nodetail."</td>";
                            $stream .= "<td align=center><b>".$noref."</b></td>";
                            $stream .= "<td align=right>".hidezerodecimal($rupiahplasma[$kdorg][$noref],2)."</td>";
                        $stream .= "</tr>";


                        // $stream .= "<tr class=rowcontent style='background-color:#66fab8!important;'>";
                        //     $stream .= "<td align=center colspan=4><b>SUBTOTAL ".$noref."</b></td>";
                        //     $stream .= "<td align=right><b>".hidezerodecimal($subtotalstblok[$stblok],2)."</b></td>";
                        //     $stream .= "<td align=center></td>";
                        //     $stream .= "<td align=center></td>";
                        //     $stream .= "<td align=center></td>";
                        //     $stream .= "<td align=center></td>";
                        // $stream .= "</tr>";

                        $grandtotal+=$rupiahplasma[$kdorg][$noref];
                    endforeach;
                endforeach;

                #==================#
                # GRAND TOTAL
                #==================#
                $stream .= "<tr class=rowcontent style='background-color:#66fab8!important;'>";
                    $stream .= "<td align=center><b>SUBTOTAL</b></td>";
                    $stream .= "<td align=right colspan=2><b>".hidezerodecimal($grandtotal,2)."</b></td>";
                $stream .= "</tr>";
                

            $stream .= "<tbody>";
        $stream .= "</table>";

        if($param['tipe'] == 'excel') {
            $nop = "Detail Data Asistensi.xls";
            $xls = new HtmlExcel();
            $xls->setCss($css);
            $xls->addSheet('Report Plasma', $stream);
            $xls->headers($nop);
            echo $xls->buildFile();
        } else {
            echo $stream;
        }
    break;
}

?>