<?
#= Panggil Library PHP
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('', '<span class=judul>' . getMenu('log_2procurement') . '</span><br>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_2procurement.js?v=<?php echo time(); ?>'></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
<script src="https://unpkg.com/chart.js-plugin-labels-dv/dist/chartjs-plugin-labels.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.1.0/chartjs-plugin-datalabels.min.js" integrity="sha512-Tfw6etYMUhL4RTki37niav99C6OHwMDB2iBT5S5piyHO+ltK2YX8Hjy9TXxhE1Gm/TmAV0uaykSpnHKFIAif/A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<style>
    .freezetbl {
        position: relative;
        max-height: 350px;
    }

    .freezetbl thead {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .freezetblload {
        position: relative;
        //max-height: 550px;
    }

    .freezetblload thead {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .detailfix {
        position: relative;
        max-height: 550px;
    }

    .detailfix thead {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .select {
        color: red !important;
    }

    .unselect {
        color: black !important;
    }
</style>

<?
$optRegional = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optPt = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optTipeUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optTipeReport = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optTahun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

#= Ambil data Dropdown dari tabel Organisasi
$listOrg = getOrgDetail(4);
$qPt = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi", "tipe='PT' AND kodeorganisasi IN (" . $listOrg . ") AND kodeorganisasi NOT IN ('SDP')", "namaorganisasi asc");
$resPt = fetchData($qPt);

foreach ($resPt as $valPt) :
    $optPt .= "<option value=" . $valPt['kodeorganisasi'] . ">" . $valPt['namaorganisasi'] . "</option>";
endforeach;

#= Ambil data Dropdown dari tabel Organisasi
$qTipeUnit = selectQuery($dbname, "organisasi", "tipe", "length(kodeorganisasi)=4", "", TRUE);
$resTipeUnit = fetchData($qTipeUnit);

foreach ($resTipeUnit as $valTU) :
#= Isi Optionnya
// $optTipeUnit .= "<option value=" . $valTU['tipe'] . ">" . $valTU['tipe'] . "</option>";
endforeach;

#= Ambil data Dropdown dari tabel Bgt_Regional_Assignment
// $qRegional = selectQuery($dbname, "bgt_regional_assignment", "regional", "", "", TRUE);
$qRegional = selectQuery($dbname, "log_5regionalprocurement", "namaregional", "", "", TRUE);
$resRegional = fetchData($qRegional);

// foreach ($resRegional as $valRegional) :
//     $optRegional .= "<option value=" . $valRegional['regional'] . ">" . $valRegional['regional'] . "</option>";
// endforeach;
foreach ($resRegional as $valRegional) :
    $optRegional .= "<option value=" . $valRegional['namaregional'] . ">" . strtoupper($valRegional['namaregional']) . "</option>";
endforeach;

#= Option Tipe Report
$optTipeReport .= "<option value='summary'>Summary</option>";
$optTipeReport .= "<option value='quaterly'>Quaterly</option>";

#= Option Periode Awal & Akhir ambil dari tabel log_po_vw
$qPeriode = selectQuery($dbname, "log_po_vw", "substr(tanggal,1,7) as periode", "", "periode desc", TRUE);
$resPeriode = fetchData($qPeriode);

foreach ($resPeriode as $valPeriode) :
    $optPeriode .= "<option value=" . $valPeriode['periode'] . ">" . $valPeriode['periode'] . "</option>";
endforeach;

#= Option Periode Awal & Akhir ambil dari tabel log_po_vw
$qTahun = selectQuery($dbname, "log_po_vw", "substr(tanggal,1,4) as tahun", "", "tahun desc", TRUE);
$resTahun = fetchData($qTahun);

foreach ($resTahun as $valTahun) :
    $optTahun .= "<option value=" . $valTahun['tahun'] . ">" . $valTahun['tahun'] . "</option>";
endforeach;

#= Deklarasi View kosong
$view = "";

#= Awal View

$view .= "<fieldset style='float:left' id=tableheader>";
$view .= "<legend>Form</legend>";

$view .= "<table border=0 cellpadding=1 cellspacing=1>";

#= Awal Dropdown Regional
$view .= "<tr>";
$view .= "<td>" . $_SESSION['lang']['regional'] . "</td>";
$view .= "<td>:</td>";
$view .= "<td>";
$view .= "<select id='regional' class='select2' style='width:150px;' onchange='getPt()'>";
$view .= $optRegional;
$view .= "</select>";
$view .= "</td>";
//$view .= "</tr>";

#= Awal Dropdown PT
//$view .= "<tr>";
$view .= "<td>" . $_SESSION['lang']['pt'] . "</td>";
$view .= "<td>:</td>";
$view .= "<td>";
$view .= "<select id='pt' class='select2' style='width:150px;' onchange=getTipeUnit()>";
$view .= $optPt;
$view .= "</select>";
$view .= "</td>";
$view .= "</tr>";

#= Awal Dropdown Tipe Unit
$view .= "<tr>";
$view .= "<td>" . $_SESSION['lang']['tipe'] . " Unit</td>";
$view .= "<td>:</td>";
$view .= "<td>";
$view .= "<select id='tipeunit' class='select2' style='width:150px;' onchange='getUnit()'>";
// $view .= "<select id='tipeunit' class='select2' style='width:150px;'>";
$view .= $optTipeUnit;
$view .= "</select>";
$view .= "</td>";
// $view .= "</tr>";

#= Awal Dropdown Unit
// $view .= "<tr>";
$view .= "<td>" . $_SESSION['lang']['unit'] . "</td>";
$view .= "<td>:</td>";
$view .= "<td>";
$view .= "<select id='unit' class='select2' style='width:150px;'>";
$view .= $optUnit;
$view .= "</select>";
$view .= "</td>";
$view .= "</tr>";

#= Awal Dropdown Tipe Report
$view .= "<tr>";
$view .= "<td>" . $_SESSION['lang']['tipe'] . " Report</td>";
$view .= "<td>:</td>";
$view .= "<td>";
$view .= "<select id='tipereport' class='select2' style='width:150px;' onchange=formatReport()>";
$view .= $optTipeReport;
$view .= "</select>";
$view .= "</td>";
$view .= "</tr>";


#= Awal Dropdown Periode
$view .= "<tr id=quaterly style=display:none;>";
$view .= "<td>" . $_SESSION['lang']['periode'] . "</td>";
$view .= "<td>:</td>";
$view .= "<td>";
$view .= "<select id='periodeawal' class='select2' style='width:150px;'>";
$view .= $optPeriode;
$view .= "</select>";
$view .= "</td>";

$view .= "<td colspan=2>";
$view .= "&nbsp;";
$view .= "s/d";
$view .= "&nbsp;";
$view .= "</td>";

$view .= "<td>";
$view .= "<select id='periodeakhir' class='select2' style='width:150px;'>";
$view .= $optPeriode;
$view .= "</select>";
$view .= "</td>";

$view .= "</tr>";


#= Awal Dropdown Tahun 
$view .= "<tr id=summary style=display:none;>";
$view .= "<td>" . $_SESSION['lang']['tahun'] . "</td>";
$view .= "<td>:</td>";
$view .= "<td>";
$view .= "<select id='tahun' class='select2' style='width:150px;'>";
$view .= $optTahun;
$view .= "</select>";
$view .= "</td>";

$view .= "</tr>";


$view .= "<tr>";
$view .= "<td colspan=2></td>";
$view .= "<td colspan=4>";
$view .= "<button onclick=preview('html'); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>";
// $view .= "<button onclick=excel(event,'log_2procurement_slave.php.php','" . @$arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>";
$view .= "<button onclick=preview('excel') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>";
$view .= "</td>";
$view .= "</tr>";


$view .= "</table>";

$view .= "</fieldset>";
#= Akhir View

#= Tampilkan View
echo $view;
CLOSE_BOX();

#= Open Box Result Data
OPEN_BOX();
echo "<div id=tombolexport style=display:none;>
        <table>
            <tr><td>
                <button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
            </td>
        </table>
        </div>";
// echo "<div id='printContainer' class='table-scroll' style=height:73vh></div>";
echo "<div id='printContainer' class='table-scroll' style=height:100%!important></div>";
echo "<div style=width:100%;postion:relative;display:flex;justify-content:center;align-items:center;><div id=canvasDev style=width:500px;>
  <canvas id=myChart></canvas>
  <!--<button onclick=download()>Download</button>-->
</div></div>";
CLOSE_BOX();

echo close_body();
?>
<script>
    // const ctx = document.getElementById('myChart');

    // new Chart(ctx, {
    //     type: 'pie',
    //     data: {
    //         labels: ['Mill', 'Spare', 'Pupuk', 'Consumable', 'Logistic'],
    //         datasets: [{
    //             label: "Data",
    //             data: [12, 19, 3, 5, 2, 3],
    //             borderWidth: 1
    //         }]
    //     },
    //     options: {
    //         scales: {
    //             y: {
    //                 beginAtZero: true
    //             }
    //         }
    //     }
    // });
</script>