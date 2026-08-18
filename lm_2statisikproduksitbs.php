<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language="javascript1.2" src="js/lm_2statisikproduksitbs.js?v=<?php echo time(); ?>"></script>
<script language="javascript1.2" src="js/zSelect2.js?ver=1.9"></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul>' . getMenu('lm_2statisikproduksitbs') . '</span><br>');

//Perusahaan
$optPT = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

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

//Periode
$optPer = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$iPer = "SELECT DISTINCT periode
         FROM {$dbname}.setup_periodeakuntansi
         ORDER BY periode DESC
         LIMIT 12";
$nPer = $owlPDO->query($iPer) or die(print "Gagal: " . PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);

while ($dPer = $nPer->fetch()) {
    $optPer .= "<option value='" . $dPer['periode'] . "'>" . $dPer['periode'] . "</option>";
}

$intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
$intiplasma.="<option value='I'>Inti</option>";
$intiplasma.="<option value='P'>Plasma</option>";

//form
echo "
<fieldset style='float:left'>
<legend>" . $_SESSION['lang']['form'] . "</legend>
<table cellspacing=1 border=0>

<tr>
  <td>" . $_SESSION['lang']['pt'] . "</td><td>:</td>
  <td>
    <select class='select2' id='pt' style='width:220px'>
        {$optPT}
    </select>
  </td>
</tr>
<tr>
    <td>".$_SESSION['lang']['intiplasma']."</td>
    <td>:</td>
    <td>
        <select class=select2 id='intiplasma' style='width:220px'>".$intiplasma."</select>
    </td>
</tr>
<tr>
  <td>".$_SESSION['lang']['periode']."</td>
  <td>:</td>
  <td style='white-space:nowrap'>

    <span style='display:inline-block; margin-right:4px;'>
      <select id='periode' class='select2' style='width:220px'>
        {$optPer}
      </select>
    </span>
  </td>
</tr>


<tr>
  <td></td>
  <td></td>
  <td colspan='3'>
    <button class='mybutton' onclick='return previewData();'>
        " . $_SESSION['lang']['preview'] . "
    </button>
    <button class=\"mybutton\" onclick=\"return previewData('excel');\">
        " . $_SESSION['lang']['excel'] . "
    </button>
  </td>
</tr>

</table>
</fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo "<div id='printContainer' class='table-scroll' style='height:63vh;overflow:auto;'></div>";
CLOSE_BOX();

close_body();
?>