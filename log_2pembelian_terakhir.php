<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
if ($_SESSION['language'] == 'EN') {
    $zz = 'kelompok1 as kelompok';
} else {
    $zz = 'kelompok';
}

$optKlmpk = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sOrg = "select " . $zz . ",kode from " . $dbname . ".log_5klbarang order by kode asc";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optKlmpk.="<option value=" . $rOrg['kode'] . ">" . $rOrg['kode'] . " - " . $rOrg['kelompok'] . "</option>";
}

$optSup = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sSup = "select supplierid,namasupplier from " . $dbname . ".log_5supplier where substring(supplierid,1,1)='S' order by namasupplier asc";
$qSup=$owlPDO->query($sSup) or die(print " Gagal: ".PDOException::getMessage());
$qSup->setFetchMode(PDO::FETCH_ASSOC);
while ($rSup = $qSup->fetch()) {
    $optSup.="<option value=" . $rSup['supplierid'] . ">" . $rSup['namasupplier'] . "</option>";
}
$optLokal = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arrPo = array("0" => "Head Office", "1" => "Local");
foreach ($arrPo as $brsLokal => $isiLokal) {
    $optLokal.="<option value=" . $brsLokal . ">" . $isiLokal . "</option>";
}
$arr = "##klmpkBrg##kdBrg##tglDr##tanggalSampai##lokBeli";
$optPeriodePo = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sPeriodePo = "select distinct substring(tanggal,1,7) as periode from " . $dbname . ".log_poht order by tanggal desc";
$qPeriodePo=$owlPDO->query($sPeriodePo) or die(print " Gagal: ".PDOException::getMessage());
$qPeriodePo->setFetchMode(PDO::FETCH_ASSOC);
while ($rPeriodePo = $qPeriodePo->fetch()) {
    if ($rPeriodePo['periode'] != '0000-00') {
        if (substr($rPeriodePo['periode'], 5, 2) == '12') {
            $optPeriodePo.="<option value=" . substr($rPeriodePo['periode'], 0, 4) . ">" . substr($rPeriodePo['periode'], 0, 4) . "</option>";
        } else {
            $optPeriodePo.="<option value=" . $rPeriodePo['periode'] . ">" . substr(tanggalnormal($rPeriodePo['periode']), 1, 7) . "</option>";
        }
    }
    //echo substr($rPeriodePo['periode'],5,5);
}
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>

<script>

    function getBrg()
    {
        klmpkBrg = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
        param = 'klmpkBrg=' + klmpkBrg + '&proses=getBrg';
        tujuan = "log_slave_2detail_pembelian_brg.php";
        //alert(param);	

        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                    } else {
                        // Success Response
                        document.getElementById('kdBrg').innerHTML = con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        //
        //  alert(fileTarget+'.php?proses=preview', param, respon);
        post_response_text(tujuan, param, respon);
    }
    semua = "<?php echo $_SESSION['lang']['all'] ?>";
    function batal()
    {
        document.getElementById('klmpkBrg').value = '';
        document.getElementById('kdBrg').innerHTML = '';
        document.getElementById('kdBrg').innerHTML = "<option value=''>" + semua + "</option>";
        document.getElementById('lokBeli').value = '';
        document.getElementById('tglDr').value = '';
        document.getElementById('tanggalSampai').value = '';
        document.getElementById('printContainer').innerHTML = '';
    }
    function searchBrg(title, content, ev)
    {
        klmpk = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
        if (klmpk == '')
        {
            alert("Metrial group required!!");
            return;
        }

        width = '';
        height = '';
        showDialog5(title, content, width, height, ev);
        //alert('asdasd');
    }
    function findBrg()
    {
        klmpkBrg = document.getElementById('klmpkBrg').value;
        nmBrg = document.getElementById('nmBrg').value;
        param = 'klmpkBrg=' + klmpkBrg + '&nmBrg=' + nmBrg + '&proses=getBarang';
        tujuan = 'log_slave_2detail_pembelian_brg.php';
        post_response_text(tujuan, param, respog);
        function respog()
        {
            if (con.readyState == 4)
            {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                    }
                    else {
                        //	alert(con.responseText);
                        document.getElementById('containerBarang').innerHTML = con.responseText;
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }

    }
    function setData(kdbrg)
    {
        document.getElementById('kdBrg').value = kdbrg;
        //document.getElementById('namaBrg').value=namaBarang;
        //document.getElementById('satuan').innerHTML=sat;
        closeDialog5();
    }
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['detPembTakhir']).'</span>');
?>
<div>
    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0" >
            <tr><td><label><?php echo $_SESSION['lang']['kelompokbarang'] ?></label></td><td>:</td><td colspan='3'><select id="klmpkBrg" name="klmpkBrg" style="width:190px" onchange="getBrg()"><?php echo $optKlmpk ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['namabarang'] ?></label></td><td>:</td><td colspan='3'><select id="kdBrg" name="kdBrg" style="width:190px"><option value=''><?php echo $_SESSION['lang']['all'] ?></option></select>&nbsp;<img src="images/search.png" class="resicon" title='<?php echo $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] ?>' onclick="searchBrg('<?php echo $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] ?>', '<fieldset style=min-width:93%><?php echo $_SESSION['lang']['find']; ?>&nbsp;<input type=text class=myinputtext id=nmBrg><button class=mybutton onclick=findBrg()><?php echo $_SESSION['lang']['find'] ?></button></fieldset><div id=containerBarang style=overflow=auto;max-height=380;width=485></div>', event);"></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['lokasiBeli'] ?></label></td><td>:</td><td colspan='3'><select id="lokBeli" name="lokBeli" style="width:190px"><?php echo $optLokal ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['periode'] ?></label></td><td>:</td><td><select id="tglDr" style="width:75px;"><?php echo $optPeriodePo ?></select><!--<input type="text" class="myinputtext" id="tglDr" name="tglDr" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10"  />--></td>
            <td><?php echo $_SESSION['lang']['sd']?></td><td><select id="tanggalSampai" style="width:75px;"><?php echo $optPeriodePo ?></select><!--<input type="text" class="myinputtext" id="tanggalSampai" name="tanggalSampai" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:150px;" />--></td></tr>

            
            <tr><td><td><td colspan="3"><button onclick="zPreview('log_slave_2pembelian_terakhir', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
                    <!--<button onclick="zPdf('log_slave_2detail_pembelian_brg','<?php echo $arr ?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>-->
                    <button onclick="zExcel(event, 'log_slave_2pembelian_terakhir.php', '<?php echo $arr ?>')" class="mybutton" name="preview" id="preview">Excel</button>
                    <button onclick="batal()" class="mybutton" name="btl" id="btl"><?php echo $_SESSION['lang']['cancel'] ?></button>
                </td></tr>

        </table>
    </fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>

<legend><b>Print Area</b></legend>
   <div id='both_report'>
    <div id='head_tableboth' style='height:30px;'>
      <a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' style='float:right;margin-right:10px'>
        <img title='Full Screen' class=resicon src='images/full-screen.png'>
    </a>
    <a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' style='float:right;margin-right:10px;' >
        <img title='Fixed Header Table' class=resicon src=images/fix-header.gif>
    </a>
</div>
<div id='printContainer' style='overflow:auto;height:375px;max-width:100%'></div>
</div>


<?php
CLOSE_BOX();
echo close_body();
?>