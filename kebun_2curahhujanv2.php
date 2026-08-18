<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
require_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
?>
<script language="javascript" src="js/kebun_2curahhujanv2.js?ver=<?=time()?>"></script>

<?php 
// Organisasi
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
    $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING' order by namaorganisasi asc";
} else {
    $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc";
}
$resOrg = fetchData($sOrg);
foreach ($resOrg as $bar) {
    $optOrg .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

// PT
$optPt = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qPt = selectQuery($dbname, 'organisasi', "*", "tipe = 'PT' ORDER BY namaorganisasi");
$resPt = fetchData($qPt);
foreach ($resPt as $row) {
    $optPt .= "<option value='".$row['kodeorganisasi']."'>".$row['namaorganisasi']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2curahhujanv2').'</span>');
echo "
    <fieldset style='clear:both'>
        <div id='container' style='overflow:auto;height:400px;max-width:100%'>
        
        </div>
    </fieldset>
";

CLOSE_BOX();

echo close_body();
?>

