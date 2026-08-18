<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('', '<span class=judul>' . getMenu('lm_2grading') . '</span><br>');

?>

<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script language=javascript src='js/lm_2grading.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<link rel=stylesheet type=text/css href='DataTables/css/buttons.dataTables.min.css'>
<?
$optorg = $optper = '';
$optorg .= "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optPT = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optTt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$sqlPT = "
    SELECT DISTINCT 
        LEFT(sbt.indukblok,3) AS kodept,
        org.namaorganisasi
    FROM {$dbname}.setup_blok_tahunan sbt
    LEFT JOIN {$dbname}.organisasi org 
        ON org.kodeorganisasi = LEFT(sbt.indukblok,3)
    WHERE org.tipe = 'PT'
    ORDER BY kodept
";
$resPT = fetchData($sqlPT);

foreach ($resPT as $r) {
    $optPT .= "<option value='{$r['kodept']}'>
                    {$r['kodept']} - {$r['namaorganisasi']}
               </option>";
}

$sqlTahun = "
    SELECT DISTINCT 
        LEFT(tahun,4) AS tahun
    FROM {$dbname}.setup_blok_tahunan
    WHERE tahun IS NOT NULL AND tahun <> 0
    ORDER BY tahun DESC
";
$resTahun = fetchData($sqlTahun);

foreach ($resTahun as $r) {
    $optTt .= "<option value='{$r['tahun']}'>{$r['tahun']}</option>";
}


$arr1 = "##pt##kdorg##tt";
echo "<fieldset style='float:left;' id=tableheader>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','" . $_SESSION['lang']['all'] . "')  style=\"width:164px;\">" . $optPT . "</select></td>
               
                    <td>" . $_SESSION['lang']['tahun'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tt style=\"width:164px;\">" . $optTt . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                   <td>
                        <button onclick=\"preview('html',event)\" class='mybutton'>" . $_SESSION['lang']['preview'] . "</button>
                        <button onclick=\"printexcel(event)\" class='mybutton'>Excel</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo "<div id='printContainer' class='table-scroll' style=height:73vh></div>";

CLOSE_BOX();
echo close_body();
?>