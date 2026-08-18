<?php

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$method        = checkPostGet('method', '');
$periode       = checkPostGet('periode', '');
$kodePabrik    = checkPostGet('kode_pabrik', '');

$tab .= "<table width=100% cellpadding=3 cellspacing=0 border=1 style='border-collapse: collapse;'>";
    $tab .= "<thead>";
        $tab .= "<tr class=rowcontent style='font-weight: bold;'>";
            $tab .= "<td rowspan='2' style='text-align: center; vertical-align: middle;'>TANGGAL</td>";
            $tab .= "<td colspan='13' style='text-align: center;'>TBS MASUK</td>";
            $tab .= "<td colspan='5' style='text-align: center;'>TBS DIOLAH</td>";
            $tab .= "<td colspan='2' style='text-align: center;'>HASIL PRODUKSI</td>";
            $tab .= "<td colspan='3' style='text-align: center;'>RENDEMENT</td>";
            $tab .= "<td colspan='2' style='text-align: center;'>PENGIRIMAN</td>";
            $tab .= "<td colspan='2' style='text-align: center;'>STOCK PRODUKSI</td>";
            $tab .= "<td colspan='2' style='text-align: center;'>JAM OLAH</td>";
            $tab .= "<td colspan='2' style='text-align: center;'>KAP.PABRIK</td>";
        $tab .= "</tr>";
        
        // Sub Headers
        $tab .= "<tr class=rowcontent style='font-weight: bold;'>";
            // TBS MASUK
            $tab .= "<td style='text-align: center;'>TBS PT. CA</td>";
            $tab .= "<td style='text-align: center;'>TBS PT. LA</td>";
            $tab .= "<td style='text-align: center;'>TBS LUAR PT. SBG</td>";
            $tab .= "<td style='text-align: center;'>TBS PT. P3</td>";
            $tab .= "<td style='text-align: center;'>TBS KEL</td>";
            $tab .= "<td style='text-align: center;'>TBS LUAR PT. P3</td>";
            $tab .= "<td style='text-align: center;'>TBS LUAR L4 P3NS</td>";
            $tab .= "<td style='text-align: center;'>TBS LUAR L4 P3NS</td>";
            $tab .= "<td style='text-align: center;'>TOTAL</td>";
            $tab .= "<td style='text-align: center;'>TBS DIOLAH</td>";
            $tab .= "<td style='text-align: center;'>%</td>";
            $tab .= "<td style='text-align: center;'>TBS LUAR</td>";
            $tab .= "<td style='text-align: center;'>%</td>";
            // TBS DIOLAH
            $tab .= "<td style='text-align: center;'>Awal</td>";
            $tab .= "<td style='text-align: center;'>Tersedia</td>";
            $tab .= "<td style='text-align: center;'>TBS Grading Thermbal</td>";
            $tab .= "<td style='text-align: center;'>Diolah</td>";
            $tab .= "<td style='text-align: center;'>Aktif</td>";
            // HASIL PRODUKSI
            $tab .= "<td style='text-align: center;'>CPO</td>";
            $tab .= "<td style='text-align: center;'>Kernel</td>";
            // RENDEMENT
            $tab .= "<td style='text-align: center;'>FFA</td>";
            $tab .= "<td style='text-align: center;'>CPO</td>";
            $tab .= "<td style='text-align: center;'>Kernel</td>";
            // PENGIRIMAN
            $tab .= "<td style='text-align: center;'>CPO</td>";
            $tab .= "<td style='text-align: center;'>Kernel</td>";
            // STOCK PRODUKSI
            $tab .= "<td style='text-align: center;'>CPO</td>";
            $tab .= "<td style='text-align: center;'>Kernel</td>";
            // JAM OLAH
            $tab .= "<td style='text-align: center;'>CBC</td>";
            $tab .= "<td style='text-align: center;'>PRESS</td>";
            // KAP.PABRIK
            $tab .= "<td style='text-align: center;'>CBC</td>";
            $tab .= "<td style='text-align: center;'>PRESS</td>";
        $tab .= "</tr>";
    $tab .= "</thead>";
    
    // Dummy data
    $dummyData = [
        ['Januari', '31,780', '116,370', '44,460', '18,240', '', '', '', '', '260,500', '238,040', '91.4%', '44,460', '17.1%', '43,780', '43,160', '', '268,330', '43,560', '53,624', '11,090', '5,04', '22,22', '4,18', '', '', '11,02', '8,88', '24,350', '', '', ''],
        ['Februari', '35,300', '140,970', '116,300', '39,940', '', '', '', '', '332,510', '267,730', '80.5%', '115,980', '34.9%', '48,390', '300,850', '', '435,280', '43,930', '###########', '29,840', '3,07', '20,67', '4,20', '', '', '###########', '14,73', '27,930', '', '', ''],
        ['Maret', '54,800', '0', '56,500', '33,800', '', '', '', '', '145,100', '370,530', '255.3%', '50,500', '34.8%', '45,600', '477,860', '', '132,540', '45,420', '64,485', '16,000', '1,97', '18,53', '4,18', '', '', '###########', '16,73', '0', '', '', ''],
        ['April', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ['Mei', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ['Juni', '37,460', '153,300', '221,530', '27,010', '', '', '', '', '439,300', '247,730', '56.4%', '221,530', '50.4%', '45,420', '514,800', '', '463,580', '44,620', '86,514', '99,600', '4,45', '20,56', '4,17', '', '', '###########', '14,35', '25,400', '', '', ''],
        ['Juli', '67,170', '36,390', '98,430', '35,700', '', '', '', '', '237,690', '259,600', '109.2%', '199,430', '83.9%', '46,820', '498,180', '', '420,950', '44,330', '84,786', '17,320', '3,30', '20,16', '4,03', '161,780', '36,580', '###########', '13,06', '26,370', '', '', ''],
        ['Agustus', '18,360', '194,180', '190,450', '23,610', '', '', '', '', '426,600', '273,320', '64.1%', '190,450', '44.7%', '44,330', '510,200', '', '463,900', '46,400', '86,508', '13,350', '3,53', '20,01', '4,17', '37,570', '40,260', '1,778,101', '###########', '16,41', '13,63', '23,260', ''],
        ['Septemeber', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ['Oktober', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ['November', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ['Desember', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']
    ];
    
    foreach ($dummyData as $row) {
        $tab .= "<tr class=rowcontent>";
        foreach ($row as $cell) {
            $tab .= "<td style='text-align: center; padding: 5px;'>" . $cell . "</td>";
        }
        $tab .= "</tr>";
    }

    $tab .= "<tr class=rowcontent style='font-weight: bold;'>";
        $tab .= "<td style='text-align: center;'>TOTAL</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>244,870</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>641,210</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>727,170</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>178,290</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>-</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>-</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>-</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>-</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>1,841,700</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>1,356,950</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>73.68%</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>722,350</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>44.67%</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>273,740</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>2,345,850</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>-</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>2,155,860</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>267,860</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>375,917</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>153,320</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>20.36</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>129.17</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>25.02</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>199,350</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>76,840</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>1,789,127</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>73.25</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>117,180</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>13.63</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>46,520</td>";
        $tab .= "<td style='text-align: center; padding: 5px;'>-</td>";
    $tab .= "</tr>";
$tab .= "</table>";

switch ($method)
{
    case 'html': 
        echo $tab;
        break;
    case 'excel':
        echo $tab;  
        break;
}
