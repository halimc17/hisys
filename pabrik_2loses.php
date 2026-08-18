<? //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
// $optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
// $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";

// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch())
// {
// $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
// }

$intex = array('0' => 'External', '1' => 'Internal', '2' => 'Afiliasi');
$optTbs = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optTbsRe = "<option value='3'>" . $_SESSION['lang']['all'] . "</option>";
foreach ($intex as $dt => $rw) {
        $optTbs .= "<option value=" . $dt . ">" . $rw . "</option>";
        $optTbsRe .= "<option value=" . $dt . ">" . $rw . "</option>";
}

$arrRe = "##kdPabrik##tgl1##tgl2";

$optPabrik = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg2 = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='PABRIK'";
$qOrg2 = $owlPDO->query($sOrg2) or die(print " Gagal: " . PDOException::getMessage());
$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg2 = $qOrg2->fetch()) {
        $optPabrik .= "<option value=" . $rOrg2['kodeorganisasi'] . ">" . $rOrg2['namaorganisasi'] . "</option>";
}

$optUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$unitintimbangan = '(';
$sOrg = "select distinct kodeorg from " . $dbname . ".pabrik_timbangan where kodeorg!='' and millcode like '%%' order by kodeorg";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rData = $qOrg->fetch()) {
        $optUnit .= "<option value=" . $rData['kodeorg'] . ">" . $rData['kodeorg'] . "</option>";
        $unitintimbangan .= "'" . $rData['kodeorg'] . "',";
}
$unitintimbangan = substr($unitintimbangan, 0, -1);
$unitintimbangan .= ')';
// $optAfdeling2="<option value=''>".$_SESSION['lang']['all']."</option>";
// $sOrg="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' and induk in ".$unitintimbangan." order by kodeorganisasi";
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rData=$qOrg->fetch())
// {
// $optAfdeling2.="<option value=".$rData['kodeorganisasi'].">".$rData['kodeorganisasi']."</option>";
// }
$optPeriode = "<option value=''></option>";
$sOrg = "select distinct substr(tanggal,1,7) as periode from " . $dbname . ".pabrik_timbangan where kodeorg!='' and millcode like '%%' order by periode desc";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rData = $qOrg->fetch()) {
        $optPeriode .= "<option value=" . $rData['periode'] . ">" . $rData['periode'] . "</option>";
}

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('', '<span class=judul>' . getMenu('pabrik_2loses') . '</span>');
?>
<div>
        <fieldset style="float: left;">
                <legend><b>Form</b></legend>
                <table cellspacing="1" border="0">
                        <tr>
                                <td><label><?php echo $_SESSION['lang']['pabrik'] ?></label></td>
                                <td>:</td>
                                <td><select id="kdPabrik" name="kdPabrik" style="width:202px"><? echo $optPabrik ?></select></td>
                        </tr>
                        <tr>
                                <td><label><?php echo $_SESSION['lang']['tanggal'] ?></label></td>
                                <td>:</td>
                                <td><input type="text" class="myinputtext" id="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;" size="10" maxlength="10" readonly />
                                        s.d. <input type="text" class="myinputtext" id="tgl2" onmousemove="setCalendar(this.id)" onkeypress="return false;" size="10" maxlength="10" readonly />
                                </td>
                        </tr>

                        <tr height="2">
                                <td colspan="2"></td>
                        </tr>
                        <tr>
                                <td>
                                <td>
                                <td><button onclick="zPreview('pabrik_slave_2loses','<?php echo $arrRe ?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
                                        <!--<button onclick="zPdf('pabrik_slave_2loses','<?php echo $arrRe ?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>-->
                                        <button onclick="zExcel(event,'pabrik_slave_2loses.php','<?php echo $arrRe ?>')" class="mybutton" name="preview" id="preview">Excel</button>
                                </td>
                        </tr>

                </table>
        </fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>


<!-- <fieldset style='clear:both;max-width:1235px'><legend><b>Print Area</b></legend> -->
<div id='printContainer'>

</div>
<!-- </fieldset> -->

<?php
CLOSE_BOX();
echo close_body();
?>