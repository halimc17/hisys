<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optOrg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='PT'";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['namaorganisasi'] . "</option>";
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
$arr = "##kdPt##kdSup##kdUnit##tglDr##tanggalSampai##lokBeli";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script>
    function getKdorg()
    {
        kdPt = document.getElementById('kdPt').options[document.getElementById('kdPt').selectedIndex].value;
        param = 'kdPt=' + kdPt + '&proses=getKdorg';
        tujuan = "log_slave_2detail_pembelian.php";
        //alert(param);	

        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                    } else {
                        // Success Response
                        document.getElementById('kdUnit').innerHTML = con.responseText;
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
    function searchSupplier(title, content, ev)
    {
        width = '';
        height = '';
        showDialog5(title, content, width, height, ev);
        //alert('asdasd');
    }
    function findSupplier()
    {
        nmSupplier = document.getElementById('nmSupplier').value;
        param = 'proses=getSupplierNm' + '&nmSupplier=' + nmSupplier;
        tujuan = 'log_slave_save_po.php';
        post_response_text(tujuan, param, respog);

        function respog() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                    }
                    else {
                        document.getElementById('containerSupplier').innerHTML = con.responseText;
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
    }
    function setData(kdSupp)
    {
        l = document.getElementById('kdSup');

        for (a = 0; a < l.length; a++)
        {
            if (l.options[a].value == kdSupp)
            {
                l.options[a].selected = true;
            }
        }

        closeDialog5();
        get_supplier();
    }
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['detPemb']).'</span>');
?>
<div>
    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0" >
            <tr><td><label><?php echo $_SESSION['lang']['pt'] ?></label></td><td>:</td><td colspan='3'><select id="kdPt" name="kdPt" style="width:180px" onchange="getKdorg()"><?php echo $optOrg ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['unit'] ?></label></td><td>:</td><td colspan='3'><select id="kdUnit" name="kdUnit" style="width:180px"><option value=''><?php echo $_SESSION['lang']['all'] ?></option></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['supplier'] ?></label></td><td>:</td><td colspan='3'><select id="kdSup" name="kdSup" style="width:180px"><?php echo $optSup ?></select>&nbsp;<img id='kdSup' onclick=z.elSearch('kdSup',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['lokasiBeli'] ?></label></td><td>:</td><td colspan='3'><select id="lokBeli" name="lokBeli" style="width:180px"><?php echo $optLokal ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['tanggal'] ?></label></td><td>:</td><td ><input type="text" class="myinputtext" id="tglDr" name="tglDr" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:65px;" /></td>
            <td><?php echo $_SESSION['lang']['sd'] ?></td><td><input type="text" class="myinputtext" id="tanggalSampai" name="tanggalSampai" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:65px;" /></td>
            <tr><td><td><td colspan=3><button onclick="zPreview('log_slave_2detail_pembelian', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('log_slave_2detail_pembelian', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event, 'log_slave_2detail_pembelian.php', '<?php echo $arr ?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

        </table>
    </fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
   <div id='both_report'>
    <div id='head_tableboth' style='height:30px;'>
      <a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' style='float:right;margin-right:10px'>
        <img title='Full Screen' class=resicon src='images/full-screen.png'>
    </a>
    <a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' style='float:right;margin-right:10px;' >
        <img title='Fixed Header Table' class=resicon src=images/fix-header.gif>
    </a>
</div>
<div id='printContainer' style='overflow:auto;height:350px'></div>
</div>
</fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>