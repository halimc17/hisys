<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_2pembayaran.js"></script>
<?php
if ($_SESSION['language'] == 'EN') {
    $zz = 'kelompok1';
} else {
    $zz = 'kelompok';
}
$optKelompok = makeOption($dbname, 'log_5klbarang', 'kode,' . $zz);
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optSupplr = $optOrg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sPeriodeCari = "select distinct substr(tanggal,1,7) as periode from " . $dbname . ".log_po_vw where statuspo=3 order by substr(tanggal,1,7) desc";
$qPeriodeCari = $owlPDO->query($sPeriodeCari) or die(print " Gagal: " . PDOException::getMessage());
$qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
while ($rPeriodeCari = $qPeriodeCari->fetch()) {
    $optPeriode.="<option value='" . $rPeriodeCari['periode'] . "'>" . $rPeriodeCari['periode'] . "</option>";
}
$optSupp = $optNopo = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optjenis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$stataPP = array("0" => "Contract", "1" => "PO");
foreach ($stataPP as $dataIni => $listNama) {
    $optjenis.="<option value='" . $dataIni . "'>" . $listNama . "</option>";
}
$sOrg = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi asc";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value='" . $rOrg['kodeorganisasi'] . "'>" . $rOrg['namaorganisasi'] . "</option>";
}
$sOrg = "select distinct supplierid,namasupplier,substr(supplierid,1,1) as tipe from " . $dbname . ".log_5supplier where namasupplier!='' order by namasupplier asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optSupplr.="<option value='" . $rOrg['supplierid'] . "'>" . $rOrg['tipe'] . "-" . $rOrg['namasupplier'] . "</option>";
}

$arr = "##lstPo##kdUnit##periode##jenisId##suppId##periode2";
$arr2 = "##tgl_cari##tgl_cari2##jenisId2##kdUnit2##cariNopo##suppId2";
?>
<script language=javascript src="js/zTools.js"></script>
<script language=javascript src="js/zReport.js"></script>

<link rel=stylesheet type="text/css href=style/zTable.css">
<div>
<?
OPEN_BOX('','<span class=judul>'.strtoupper('SUPPLIER PAYMENT HISTORY').'</span><br>');
?>
    <fieldset style="float: left;">
        <legend><b>Form</b></legend>
        <table cellspacing="1" border="0" >
            <tr><td><label><?php echo $_SESSION['lang']['pt'] ?></label></td><td>:</td><td colspan='3'><select id="kdUnit2" name="kdUnit2" style="width:175px" ><? echo $optOrg;?></select></td></tr>
			<tr><td><label><?php echo $_SESSION['lang']['jenis'] ?></label></td><td>:</td><td colspan='3'><select id="jenisId2" name="jenisId2" style="width:175px"><? echo $optjenis?></select></td></tr>
			<tr><td><label><?php echo $_SESSION['lang']['tanggal'] ?></label></td><td>:</td><td style='width:70px'><input style='width:65px' type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;   maxlength=10 /></td>
            <td><label><?php echo $_SESSION['lang']['sd'] ?></label></td><td><input style='width:65px' type=text class=myinputtext id=tgl_cari2 onmousemove=setCalendar(this.id) onkeypress=return false;   maxlength=10 /></td></tr>
            
            
            <tr><td><label><?php echo $_SESSION['lang']['nopo'] ?> / No Kontrak</label></td><td>:</td><td colspan='3'><input placeholder='Seluruhnya' type=text id="cariNopo" class="myinputtext" style="width:170px;" /></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['supplier'] ?></label></td><td>:</td><td colspan='3'  style='width:210px'><select id="suppId2" name="suppId2" style="width:175px"><? echo $optSupplr?></select>
<?php echo"<img src=images/search.png class=resicon title='" . $_SESSION['lang']['findRkn'] . "' onclick=\"searchSupplier('" . $_SESSION['lang']['findRkn'] . "','<fieldset style=min-width:93%>" . $_SESSION['lang']['find'] . "&nbsp;<input type=text class=myinputtext id=nmSupplier><button class=mybutton onclick=findSupplier()>" . $_SESSION['lang']['find'] . "</button></fieldset><div id=containerSupplier style=overflow=auto;max-height=280px;max-width=485px></div>',event);\">"; ?></td></tr>
            
            <tr><td><td><td colspan="3"><button onclick="zPreviewd('log_slave_2pembayaran2', '<?php echo $arr2 ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event, 'log_slave_2pembayaran2.php', '<?php echo $arr2 ?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
        </table>
    </fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='clear:both;max-width:1235px'><legend><b>Print Area</b></legend>
    <div id='printContainer' style='overflow:auto;height:350px;max-width:1235px'></div>
    <div id='printContainer1' style='overflow:auto;height:350px;max-width:1235px;display:none;'></div>
</div>
</fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>