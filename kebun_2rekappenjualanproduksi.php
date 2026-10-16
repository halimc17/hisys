<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
function getmark(id){
	dis = document.getElementById(id).style.backgroundColor;
	if(dis!=''){
		document.getElementById(id).style.backgroundColor="";		
	}else{		
		document.getElementById(id).style.backgroundColor="cyan";
	}
}

function laporanPreview() {
    if(getValue('kdpt') == '') {
        alert('PT harus dipilih');
    } else if(getValue('periode') == '') {
        alert('Periode harus dipilih');
    } else {
        zPreview('kebun_slave_2rekappenjualanproduksi', '##kdpt##periode', 'printContainer');
    }
}

function laporanExcel(ev) {
    if(getValue('kdpt') == '') {
        alert('PT harus dipilih');
    } else if(getValue('periode') == '') {
        alert('Periode harus dipilih');
    } else {
        zExcel(ev, 'kebun_slave_2rekappenjualanproduksi.php', '##kdpt##periode');
    }
}
</script>

<?php
$title = getMenu('kebun_2rekappenjualanproduksi');
if($title == '' || $title == 'kebun_2rekappenjualanproduksi'){
    $title = 'Rekap Penjualan Produksi';
}
OPEN_BOX('', '<span class=judul>' . $title . '</span><br>');

$optPT = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT kodeorganisasi, namaorganisasi FROM " . $dbname . ".organisasi WHERE tipe='PT' ORDER BY kodeorganisasi ASC";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optPT .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 25";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optper .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

echo "<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select class=select2 id=kdpt style=\"width:250px;\">" . $optPT . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=periode style=\"width:250px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=laporanPreview() class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=laporanExcel(event) class=mybutton name=excel id=excel>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<style>
#printContainer th, #printContainer thead th {
    position: static !important;
}
</style>";
echo "<div id='printContainer' style='min-height:560px;max-width:100%; overflow:auto;'></div>";
CLOSE_BOX();
echo close_body();
?>