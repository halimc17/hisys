<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript1.2 src="js/zSelect2.js?ver=1.9"></script>
<script>
    function getUnit() {
        pro = document.getElementById('ptId');
        prod = pro.options[pro.selectedIndex].value;
        param = 'proses=getUnit' + '&kdPt=' + prod;
        tujuan = 'log_slave_2gdangAccounting.php';
        post_response_text(tujuan, param, respog);
        function respog() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //alert(con.responseText);
                        document.getElementById('unitId').innerHTML = con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }

    }
    function getUnit2() {
        pro = document.getElementById('ptId2');
        prod = pro.options[pro.selectedIndex].value;
        param = 'proses=getUnit' + '&ptId2=' + prod;
        tujuan = 'log_slave_2gdangAccounting2.php';
        post_response_text(tujuan, param, respog);
        function respog() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //alert(con.responseText);
                        document.getElementById('unitId2').innerHTML = con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
    }
</script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_2gdangAccounting').'</span></br>');
$listOrg = getOrgDetail(3);
$optPt="<option value=''>Pilih Data</option>";
foreach($listOrg as $key => $value){
	$optPt .= '<option value="'.$key.'">'.$value.'</option>';
}

$optUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
// $sdr = "select distinct left(tanggal,7) as periode from " . $dbname . ".log_transaksiht where post=1 order by tanggal desc";
// $qdr=$owlPDO->query($sdr) or die(print " Gagal: ".PDOException::getMessage());
// $qdr->setFetchMode(PDO::FETCH_ASSOC);
// $optPrdDr = "";
// while ($rdr = $qdr->fetch()) {
// }

$optPrdDr = "";
$sdr = "select distinct left(tanggal,7) as periode from " . $dbname . ".log_transaksiht where post=1 order by tanggal desc";
$qdr=$owlPDO->query($sdr) or die(print " Gagal: ".PDOException::getMessage());
$qdr->setFetchMode(PDO::FETCH_ASSOC);
$optPrdSmp = "";
while ($rdr = $qdr->fetch()) {
    $optPrdDr.="<option value='" . $rdr['periode'] . "'>" . $rdr['periode'] . "</option>";
    $optPrdSmp.="<option value='" . $rdr['periode'] . "'>" . $rdr['periode'] . "</option>";
}
$arr = "##ptId##unitId##prdIdDr##prdIdSmp";

$frm[0] = "<fieldset style=float:left>
          <legend>" . $_SESSION['lang']['laporangudang'] . "</legend>
        <table cellpadding=1 cellspacing=1 border=0>
        <tr><td>" . $_SESSION['lang']['pt'] . "</td><td>:</td>";
$frm[0].="<td><select class=select2 id=ptId onchange='getUnit()' style=width:150px;>" . $optPt . "</select></td>";
$frm[0].="</tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td>
          <td><select class=select2 id=unitId style=width:150px;>" . $optUnit . "</select></td>
          </tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['periode'] . " " . $_SESSION['lang']['dari'] . "</td><td>:</td>
          <td><select class=select2 id=prdIdDr style=width:150px;>" . $optPrdDr . "</select></td>
          </tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['periode'] . " " . $_SESSION['lang']['sampai'] . "</td><td>:</td>
          <td><select class=select2 id=prdIdSmp style=width:150px;>" . $optPrdSmp . "</select></td>
          </tr><tr><td><td><td>";
$frm[0].="<button class=mybutton onclick=zPreview('log_slave_2gdangAccounting','" . $arr . "','printContainer')>" . $_SESSION['lang']['preview'] . "</button>
          <button class=mybutton onclick=zExcel(event,'log_slave_2gdangAccounting.php','" . $arr . "')>" . $_SESSION['lang']['excel'] . "</button>
         </td></td></td></tr></table></fieldset>";
$frm[0].="<div style=clear:both></div>";

$frm[0].="
<div style='width:100%;height:359px;overflow:auto;' class='table-scroll' id=printContainer></div>
";
// $frm[0].="</fieldset>";


$arr2 = "##ptId2##unitId2##prdIdDr2##prdIdSmp2";
$frm[1] = "<div style=clear:both></div><fieldset style=float:left>
          <legend>" . $_SESSION['lang']['laporangudang'] . "</legend>
        <table cellpadding=1 cellspacing=1 border=0>
        <tr><td>" . $_SESSION['lang']['pt'] . "</td><td>:</td>";
$frm[1].="<td><select class=select2 id=ptId2  onchange='getUnit2()'  style=width:150px;>" . $optPt . "</select></td>";
$frm[1].="</tr>";
$frm[1].="<tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td>
          <td><select class=select2 id=unitId2 style=width:150px;>" . $optUnit . "</select></td>
          </tr>";
$frm[1].="<tr><td>" . $_SESSION['lang']['periode'] . " " . $_SESSION['lang']['dari'] . "</td><td>:</td>
          <td><select class=select2 id=prdIdDr2 style=width:150px;>" . $optPrdDr . "</select></td>
          </tr>";
$frm[1].="<tr><td>" . $_SESSION['lang']['periode'] . " " . $_SESSION['lang']['sampai'] . "</td><td>:</td>
          <td><select class=select2 id=prdIdSmp2 style=width:150px;>" . $optPrdSmp . "</select></td>
          </tr><tr><td><td><td>";
$frm[1].="<button class=mybutton onclick=zPreview('log_slave_2gdangAccounting2','" . $arr2 . "','printContainer2')>" . $_SESSION['lang']['proses'] . "</button>
          <button class=mybutton onclick=zExcel(event,'log_slave_2gdangAccounting2.php','" . $arr2 . "')>" . $_SESSION['lang']['excel'] . "</button>
         </td></td></td></tr></table></fieldset>";
$frm[1].="<div style=clear:both></div>";
$frm[1].="
<div style='width:100%;height:359px;overflow:auto;' class='table-scroll'  id=printContainer2></div>
";
// $frm[1].="</fieldset>";

//========================
$hfrm[0] = $_SESSION['lang']['laporangudang'];
$hfrm[1] = $_SESSION['lang']['laporangudangakunting'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 200, "100%");
//===============================================

close_body();
?>