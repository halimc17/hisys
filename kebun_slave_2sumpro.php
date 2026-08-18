<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
error_reporting(0);

use Dompdf\Dompdf;

$region    = checkPostGet('region', '');
$kebun     = checkPostGet('kebun', '');
$unit      = checkPostGet('unit', '');
$method    = checkPostGet('method', '');
$tanggal   = checkPostGet('tanggal', '');
$tipeprint = checkPostGet('tipeprint', '');

$nmdiv = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmInduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');

$bulan = substr($tanggal, 3, 2);
$tahun = substr($tanggal, 6, 4);

$arrbln = array(
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
);

switch ($method) {

    case 'getkebun':
        $optkebun = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $str = "select * from " . $dbname . ".bgt_regional_assignment where  subregional='" . $region . "' ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optkebun .= "<option value='" . $bar['kodeunit'] . "'>" . $bar['kodeunit'] . "</option>";
        }
        echo $optkebun;
    break;

    case 'preview':
        // $where = '';
        // $tab = '';
        // $rowspan = '';

        // if ($tanggal == '') {
        //     exit('Warning Periode tidak boleh kosong');
        // }

        ###Q1###

        if ($region == '') {
            $whr1 .= "AND subregional='" . $region . "' ";
        }
        if ($region != '') {
            $whr1 .= "AND subregional = '" . $region . "'";
        }  
            
        $str = "SELECT kodeunit, subregional FROM " . $dbname . ".bgt_regional_assignment where 1=1 " . $whr1 . "  ";
        $res = fetchdata($str);
        foreach ($res as $bar) {

            @$estate[$bar['kodeunit']] = $bar['kodeunit'];
            @$arrReg[$bar['subregional']] [$nmInduk[$bar['kodeunit']]] = $bar['subregional'];
            @$arrRegTITIT[$bar['subregional']] [$bar['kodeunit']] = $bar['kodeunit'];
        }
        

        ###Q2 INTI###
        if ($region == '') {
            $whri2 .= "AND substr(a.kodeorg,1,4) LIKE '" . $kebun . "%'";
        }

        if ($region != '') {
            $whri2 .= "AND substr(a.kodeorg,1,4) LIKE '" . $region . "%'";
        } 


        $str = "SELECT substr(kodeorg,1,4) as kodeorg, luasareaproduktif, intiplasma FROM " . $dbname . ".setup_blok ";
        $res = fetchdata($str);
        foreach ($res as $bar) { 
            $luasHA[$nmInduk[$bar['kodeorg']]][$bar['intiplasma']] += $bar['luasareaproduktif'];
            $subTotalHA[$nmInduk[$bar['kodeorg']]]+= $bar['luasareaproduktif'];
        }




        ###Q3 BUDGET INTI###
        if ($tanggal != '') {
            $whri3 .= "AND  tahunbudget = '" . $tanggal . "'";
        }
        

        $str = "SELECT kodeunit, tahunbudget, kg01, kg02, kg03, kg04, kg05, kg06, kg07, kg08, kg09, kg10, kg11, kg12, intiplasma FROM " . $dbname . ".bgt_produksi_kebun WHERE 1=1 " . $whri3 . " ";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $noo++; 
            for ($i=01; $i <= count($arrbln) ; $i++) { 
                if(strlen($i) == 1){
                    $ii = '0'.$i;
                }
                $arrbgt[$nmInduk[$bar['kodeunit']]][$bar['intiplasma']][$ii] += $bar['kg'.$ii];
                $arrbgtall[$nmInduk[$bar['kodeunit']]][$bar['intiplasma']] += $bar['kg'.$ii];
                $subTotal[$nmInduk[$bar['kodeunit']]][$ii] += $bar['kg'.$ii];

            }


        }
        // echo"<pre>";
        // print_r($arrbgt);
        // echo"</pre>";    

        ###Q3 TBS SWADAYA ###
        if ($tanggal != '') {
            $whrs3 .= "AND  tahunbudget = '" . $tanggal . "'";
        }


        $str = "SELECT a.kodeunit, a.tahunbudget, a.millcode, a.olah01, a.olah02, a.olah03, a.olah04, a.olah05, a.olah06, a.olah07, a.olah08, a.olah09, a.olah10, a.olah11, a.olah12, b.kodeunit, b.subregional  FROM " . $dbname . ".bgt_produksi_pks a LEFT JOIN  " . $dbname . ".bgt_regional_assignment b  ON a.millcode=b.kodeunit WHERE 1=1 " . $whrs3 . " ";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $noo++; 

            for ($i = 01; $i <= count($arrbln); $i++) {
                if (strlen($i) == 1) {
                    $ii = '0' . $i;
                }
                $arrwsdbgt[$bar['subregional']][$ii] += $bar['olah' . $ii];
                $totalSwdbgt[$bar['subregional']] += $bar['olah' . $ii];
            }
        }
 
        ###Q4 ACTUAL ####
        
        $str = "SELECT substr(tanggal,6,2) as bulan, kodeorg, kodebarang, beratbersih, intiplasma FROM " . $dbname . ".pabrik_timbangan WHERE kodebarang=40000003 AND kodeorg != '' AND left(tanggal,4) ='".$tanggal."'";
   
        $res = fetchdata($str);
        foreach ($res as $bar) {

            if ($bar['intiplasma'] == 'INTI' ) {
                $baru='I';
            } else {
                $baru='P';
            }
            $arrAct[$nmInduk[$bar['kodeorg']]][$baru][$bar['bulan']] += $bar['beratbersih'];
            $subTotalAct[$nmInduk[$bar['kodeorg']]][$bar['bulan']] += $bar['beratbersih'];
            $arrActall[$nmInduk[$bar['kodeorg']]][$baru] += $bar['beratbersih'];
        }

        $arrip = array('I' => 'Inti','P' => 'Plasma');

        ###Q4 ###
        $str = "SELECT substr(a.tanggal,6,2) as bulan,  a.kodebarang, a.beratbersih, b.kodeunit, b.subregional FROM " . $dbname . ".pabrik_timbangan a  LEFT JOIN  " . $dbname . ".bgt_regional_assignment b  ON a.millcode=b.kodeunit WHERE kodebarang=40000003 AND kodeorg = '' AND left(tanggal,4) ='" . $tanggal . "'";
        
        $res = fetchdata($str);
        foreach ($res as $bar) {
            
            $arrSwdAct[$bar['subregional']][$bar['bulan']] += $bar['beratbersih'];
            $totalSwdAct[$bar['subregional']] += $bar['beratbersih'];
        }
        // echo"<pre>";
        // print_r($arrSwdAct);
        // echo"</pre>";
        
        ####################################### LIST DATA ################################################
        
        $tab .= "<div class=table-scroll>";
        if (@$tipeprint == 'excel') {
            $tab .= "<table border=1 class=sortable cellpading=1 cellspacing=1>";
        } else {
            $tab .= "<table border=0 class=sortable cellpading=0 cellspacing=1>";
        }
        $tab .= "<thead>
        <tr class=rowheader >";
        $tab .= "<th align=center rowspan=3>" . $_SESSION['lang']['nourut'] . "</th>";
        $tab .= "<th align=center rowspan=3>Region</th>";
        $tab .= "<th align=center rowspan=3>Estate</th>";
        $tab .= "<th align=center rowspan=3>inti/plasma</th>";
        $tab .= "<th align=center rowspan=3>" . $_SESSION['lang']['ha'] . "</th>";
        $tab .= "<th align=center colspan=36>" . $_SESSION['lang']['bulan'] . "</th>";
        $tab .= "<th align=center rowspan=2 colspan=3>" . $_SESSION['lang']['total'] . "</th>";
        $tab .= "<th align=center rowspan=3>YPH</th>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        foreach ($arrbln as $key => $value) {
            $tab .= "<th align=center colspan=3 >" . $value . "</th>";
        }
        $tab .= "</tr>";

        $tab .= "<tr>";
        for ($i=0; $i <= 12; $i++) { 
            
            $tab .= "<th align=center>" . $_SESSION['lang']['budget'] . "</th>";
            $tab .= "<th align=center> Actual </th>";
            $tab .= "<th align=center>'+/- (%)</th>";
        }   
        $tab .= "</tr>";
        $tab.="</thead>";
        $tab.="<tbody>";

        if (count($arrReg) == 0) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td  colspan=45 >" . $_SESSION['lang']['dataempty'] . "</td>";
            $tab .= "</tr>";
        } else {
            
            
            // foreach ($arrReg as $reg => $row) {
            //     $rowReg1[$dv] = 0;
            //     foreach ($thtanam[$dv] as $thnTnm) {
            //         $rowReg1[$dv] += 1;
            //         foreach ($blok[$dv][$thnTnm] as $kblok) {
            //             $rowReg1[$dv] += 1;
            //         }
            //     }
            // }
            foreach ($arrReg as $reg => $arrunit) {
                @$no2++;
                $rowunit=(count($arrunit)*3)+2;
                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=center rowspan='" . $rowunit . "'>" . $no2 . "</td>";
                $tab .= "<td align=left rowspan='". $rowunit."'> " . $reg . "</td>";
                foreach ($arrunit as $keyunit => $valunit) {
                    
                    $tab .= "<td align=left rowspan=3> " . $keyunit . "</td>";
                        foreach ($arrip as $ip => $nm) {
                            $ttlHaInti[$ip]+= $luasHA[$keyunit][$ip];
                            
                            
                            $tab .= "<td align=left > " . $nm . "</td>";
                            $tab .= "<td align=right > " . number_format($luasHA[$keyunit][$ip],2) . "</td>";
                            $ttlHa+= $luasHA[$keyunit][$ip];
                            foreach ($arrbln as $keybln => $valbln) { 
                                
                                $ttlbgtInti[$ip][$keybln]+= $arrbgt[$keyunit][$ip][$keybln];
                                $ttlActInti[$ip][$keybln]+= $arrAct[$keyunit][$ip][$keybln];
                                $ttlprsnInti[$ip][$keybln]+= ($arrAct[$keyunit][$ip][$keybln] - $arrbgt[$keyunit][$ip][$keybln]) / $arrbgt[$keyunit][$ip][$keybln] * 100;
                                
                                $tab .= "<td align=right > " . number_format($arrbgt[$keyunit][$ip][$keybln],2) . "  </td>";
                                $tab .= "<td align=right > " . number_format($arrAct[$keyunit][$ip][$keybln],2) . "  </td>";

                                $ttlBgt[$keybln]+= $arrbgt[$keyunit][$ip][$keybln];
                                $ttlAct[$keybln]+= $arrAct[$keyunit][$ip][$keybln];
                                $prsn[$keybln]+= ($arrAct[$keyunit][$ip][$keybln]- $arrbgt[$keyunit][$ip][$keybln])/ $arrbgt[$keyunit][$ip][$keybln]*100;
                                $tab .= "<td align=right > " . number_format(fixnan(($arrAct[$keyunit][$ip][$keybln]- $arrbgt[$keyunit][$ip][$keybln])/ $arrbgt[$keyunit][$ip][$keybln])*100,2) . "</td>";
                                
                            }
                            #TOTAL SEMUA BULAN / PERTAHUN
                            $tllBgtThn[$keyunit][$ip]+= $arrbgtall[$keyunit][$ip];
                            $tllActThn[$keyunit][$ip]+= $arrActall[$keyunit][$ip];

                            $ttlSubbgt += $tllBgtThn[$keyunit][$ip];
                            $ttlSubAct += $tllActThn[$keyunit][$ip];
                            $tab .= "<td align=right >". number_format($tllBgtThn[$keyunit][$ip])."</td>";
                            $tab .= "<td align=right >". number_format($tllActThn[$keyunit][$ip])."</td>";
                            $tab .= "<td align=right >". number_format(fixnan(($tllActThn[$keyunit][$ip]- $tllBgtThn[$keyunit][$ip])/ $tllBgtThn[$keyunit][$ip])*100,2)."</td>";
                            $tab .= "<td align=right >" . number_format($tllBgtThn[$keyunit][$ip]/ $luasHA[$keyunit][$ip],2) . "</td>";
                            
                        $tab .= "</tr>";
                        }
                        #HTUNG SUBTOTAL INTI/PLASMA 
                        $tab .= "<td align=left >Sub Total</td>";
                        $tab .= "<td align=right >". number_format($subTotalHA[$keyunit],2)." </td>";
                            foreach ($arrbln as $keybln => $valbln) {
                                $tab .= "<td align=right >". number_format($subTotal[$keyunit][$keybln],2)." </td>";
                                $tab .= "<td align=right >". number_format(fixnan($subTotalAct[$keyunit][$keybln]),2)." </td>";
                                $tab .= "<td align=right >" . number_format(fixnan(($subTotalAct[$keyunit][$keybln] - $subTotal[$keyunit][$keybln]) / $subTotal[$keyunit][$keybln]) * 100, 2) . " </td>";
                            }

                        $tab .= "<td align=right >" . number_format($ttlSubbgt)." </td>";
                        $tab .= "<td align=right >" . number_format($ttlSubAct)." </td>";
                        $tab .= "<td align=right >" . number_format(fixnan(($ttlSubAct- $ttlSubbgt)/ $ttlSubbgt)*100,2)." </td>";
                        $tab .= "<td align=right >" . number_format($ttlSubbgt/ $ttlHa,2)." </td>";
                        $tab .= "</tr>";
                    
                }
                ## SUBTOTAL INTI & PLASMA
                foreach ($arrip as $ip => $nm) {
                    
                    $tab .= "<tr class=rowcontent>";
                        $tab .= "<td align=left colspan=2>Sub Total ".$nm." </td>";
                        $tab .= "<td align=right >" . number_format($ttlHaInti[$ip],2) . " </td>";
                        foreach ($arrbln as $keybln => $valbln) {
                            $tab .= "<td align=right >" . number_format(fixnan($ttlbgtInti[$ip][$keybln] ),2). " </td>";
                            $tab .= "<td align=right >" . number_format(fixnan($ttlActInti[$ip][$keybln] ),2). "  </td>";
                            $tab .= "<td align=right >" .  number_format(fixnan(($ttlActInti[$ip][$keybln] - $ttlbgtInti[$ip][$keybln]) / $ttlbgtInti[$ip][$keybln]) * 100, 2) . " </td>";
                            
                            $ttlbgtRegion[$keybln]+= $ttlbgtInti[$ip][$keybln];
                            $ttlActRegion[$keybln]+= $ttlActInti[$ip][$keybln];

                            ##Array hitung Inti/plasma ujung
                            $TTLbgtall[$ip]+= $ttlbgtInti[$ip][$keybln];
                            $TTLactall[$ip]+= $ttlActInti[$ip][$keybln];
                        }
                        # # # TOTAL INTI PLASMA KESELURUHAN /UJUNG # # #
                        $tab .= "<td align=right >". number_format($TTLbgtall[$ip])." </td>";
                        $tab .= "<td align=right >". number_format($TTLactall[$ip])." </td>";
                        $tab .= "<td align=right >". number_format(fixnan($TTLactall[$ip]/ $TTLbgtall[$ip])*100,2)."</td>";
                        $tab .= "<td align=right >". number_format($TTLbgtall[$ip]/ $ttlHaInti[$ip],2)."</td>";
                        $ttlHaRegion+=   $ttlHaInti[$ip];
                    $tab .= "</tr>";
                }

                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=left colspan=4><b>TOTAL REGION ".$reg."</b></td>";
                $tab .= "<td align=right ><b>". number_format($ttlHaRegion,2)."</b></td>";
                foreach ($arrbln as $keybln => $valbln) {
                    
                    $tab .= "<td align=right ><b>". number_format($ttlbgtRegion[$keybln],2).  "  </b></td>";
                    $tab .= "<td align=right ><b>". number_format($ttlActRegion[$keybln],2).  "  </b></td>";
                    $tab .= "<td align=right ><b>". number_format(fixnan(($ttlActRegion[$keybln] - $ttlbgtRegion[$keybln])/$ttlbgtRegion[$keybln])*100, 2). "</b></td>";

                    $totalAnggaran+= $ttlbgtRegion[$keybln];
                    $totalActual+= $ttlActRegion[$keybln];

                }
            
                ##total ujung bawah
                $tab .= "<td align=right ><b>". number_format($totalAnggaran)." </b></td>";
                $tab .= "<td align=right ><b>". number_format($totalActual)." </b></td>";
                $tab .= "<td align=right ><b>". number_format(fixnan(($totalActual- $totalAnggaran)/ $totalAnggaran)*100,2)."</b></td>";
                $tab .= "<td align=right ><b>". number_format($totalAnggaran/ $ttlHaRegion,2)."</b></td>";
                $tab .= "</tr>";
            }
            
        }

        ###TBS SWADAYA###
        $tab .= "</tbody>";
        $tab .= "</table>";
        $tab .= "</div>";

        $tab .= "<div  class=table-scroll style=padding-top:30px>";
        if (@$tipeprint == 'excel'
        ) {
            $tab .= "<table border=1 class=sortable cellpading=1 cellspacing=1>";
        } else {
            $tab .= "<table border=0 class=sortable cellpading=0 cellspacing=1>";
        }
        $tab .= "<thead>
        <tr class=rowheader >";
        $tab .= "<th align=center rowspan=3>" . $_SESSION['lang']['nourut'] . "</th>";
        $tab .= "<th align=center rowspan=3>Region</th>";
        $tab .= "<th align=center rowspan=3>Estate</th>";
        $tab .= "<th align=center rowspan=3> Inti/Plasma</th>";
        $tab .= "<th align=center rowspan=3>" . $_SESSION['lang']['ha'] . "</th>";
        $tab .= "<th align=center colspan=36>" . $_SESSION['lang']['bulan'] . "</th>";
        $tab .= "<th align=center rowspan=2 colspan=3>" . $_SESSION['lang']['total'] . "</th>"; 
        $tab .= "</tr>";
        $tab .= "<tr>";
        foreach ($arrbln as $key => $value) {
            $tab .= "<th align=center colspan=3 >" . $value . "</th>";
        }
        $tab .= "</tr>";

        $tab .= "<tr>";
        for ($i = 0; $i <= 12; $i++) {

            $tab .= "<th align=center>" . $_SESSION['lang']['budget'] . "</th>";
            $tab .= "<th align=center> Actual </th>";
            $tab .= "<th align=center>'+/- (%)</th>";
        }

        $tab .= "</tr>";
        $tab .= "</thead>";

        if (count($arrReg) == 0) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td  colspan=45 >" . $_SESSION['lang']['dataempty'] . "</td>";
            $tab .= "</tr>";
        } else {
            foreach ($arrReg as $reg => $arrunit) {
                @$no3++;
                $rowunit = count($arrunit) ;
                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=center  >" . $no3 . "</td>";
                $tab .= "<td align=left>TBS SWADAYA </td>";
                $tab .= "<td align=left  > " . $reg . "</td>";
                $tab .= "<td align=left colspan=2 style=background-color:Grey;> </td>";
                foreach ($arrbln as $keybln => $keybln) {
                    
                    $tab .= "<td align=right > " . number_format($arrwsdbgt[$reg][$keybln], 2) . "</td>";
                    $tab .= "<td align=right > " . number_format($arrSwdAct[$reg][$keybln], 2) . "</td>";
                    $tab .= "<td align=right > " . number_format(fixnan(($arrSwdAct[$reg][$keybln]-$arrwsdbgt[$reg][$keybln])/ $arrwsdbgt[$reg][$keybln]*100 ), 2) . "</td>";

                    ##ARRAY HITUNG TOTAL SWADAYA bawah##
                    $ttlswdBGT[$keybln]+= $arrwsdbgt[$reg][$keybln];
                    $ttlswdACT[$keybln]+= $arrSwdAct[$reg][$keybln];
                }
                
                $tab .= "<td align=right > " . number_format($totalSwdbgt[$reg], 2) . "</td>";
                $tab .= "<td align=right > " . number_format($totalSwdAct[$reg], 2) . "</td>";
                $tab .= "<td align=right>" . number_format(fixnan(($totalSwdAct[$reg] - $totalSwdbgt[$reg]) / $totalSwdbgt[$reg] * 100), 2) . " </td>";
                $tab .= "</tr>";
                ##ARRAY HITUNG TOTAL SWADAYA samping##
                $ttlBgtSwd+=$totalSwdbgt[$reg];
                $ttlActSwd+=$totalSwdAct[$reg];
                    
            }
            
        }
        ##HITUNG TOTAL SWADAYA bawah###
        $tab .= "<td align=left colspan=5><b> TOTAL SWADAYA </b></td>";
        foreach ($arrbln as $keybln => $keybln) {
            
            $tab .= "<td align=right ><b> ". number_format($ttlswdBGT[$keybln],2)." </b></td>";
            $tab .= "<td align=right ><b> ". number_format($ttlswdACT[$keybln],2)." </b></td>";
            $tab .= "<td align=right ><b> ". number_format(fixnan(($ttlswdACT[$keybln]-$ttlswdBGT[$keybln])/ $ttlswdBGT[$keybln]*100),2)." </b></td>";
        }
        $tab .= "<td align=right ><b> ". number_format($ttlBgtSwd,2)." </b></td>";
        $tab .= "<td align=right ><b> ". number_format($ttlActSwd,2)." </b></td>";
        $tab .= "<td align=right ><b> ". number_format(fixnan(($ttlActSwd- $ttlBgtSwd)/ $ttlBgtSwd*100),2)." </b></td>";

        $tab .= "</table>";
        $tab .= "</div>";



        ###TOTAL KESELURUHAN###
        $tab.= "<div class=table-scroll style=margin-bottom:30px;margin-top:20px>";
       
        if (@$tipeprint == 'excel') {
            $tab .= "<table border=1 class=sortable cellpading=1 cellspacing=1>";
        } else {
            $tab .= "<table border=0 class=sortable cellpading=0 cellspacing=1>";
        }
        $tab .= "<thead>
        <tr class=rowheader >";
        $tab .= "<th align=center rowspan=3 > </th>";  
        $tab .= "</tr>";
        $tab .= "<tr>";
        foreach ($arrbln as $key => $value) {
            $tab .= "<th align=center colspan=3 >" . $value . "</th>";
        }
        $tab .= "<th align=center colspan=3>  TOTAL</th>"; ##totalbawah
        $tab .= "</tr>";

        $tab .= "<tr>";
        for ($i = 0; $i <= 12; $i++) {

            $tab .= "<th align=center>" . $_SESSION['lang']['budget'] . "</th>";
            $tab .= "<th align=center> Actual </th>";
            $tab .= "<th align=center>'+/- (%)</th>";
        }
        $tab .= "</tr>";
        $tab .= "</thead>";
        $tab.="<tbody>"; 
        foreach ($arrReg as $reg => $arrunit) {
        
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=left  ><b>TOTAL</b></td>";
            foreach ($arrbln as $keybln => $valbln) {

                $tab .= "<td align=right ><b> " . number_format(($ttlbgtRegion[$keybln]+ $ttlswdBGT[$keybln]), 2) . "</b></td>";
                $tab .= "<td align=right ><b> " . number_format(($ttlActRegion[$keybln]+ $ttlswdACT[$keybln]), 2) . "</b></td>";
                $p= $ttlbgtRegion[$keybln] + $ttlswdBGT[$keybln];
                $m= $ttlActRegion[$keybln] + $ttlswdACT[$keybln];
                $tab .= "<td align=right ><b> " . number_format(fixnan(($m-$p)/$p*100), 2) . "</b></td>";
                
            }
            $tab .= "<td align=right ><b> " . number_format($totalAnggaran+ $ttlBgtSwd, 2) . "</b></td>";
            $tab .= "<td align=right ><b> " . number_format($totalActual+ $ttlActSwd, 2) . "</b></td>";
            $T= $totalAnggaran + $ttlBgtSwd;
            $F= $totalActual + $ttlActSwd;
            $tab .= "<td align=right ><b> " . number_format(fixnan(($F-$T)/$T*100), 2) . "</b></td>";
            $tab .= "</tr>";
        }
         
        $tab.="</tbody>";
        $tab.="</table>"; 
        $tab.="</div>";


        if ($tipeprint == 'html') {
            echo $tab;
        } else if ($tipeprint == 'excel') {
            $tab .= "</tbody></table>";
            $nop = "SUMMARY PRODUKSI.xls";
            $xls = new HtmlExcel();
            $xls->setCss($css);
            $xls->addSheet("1", $tab);
            $xls->headers($nop);
            echo $xls->buildFile();
        }

    break;
}
