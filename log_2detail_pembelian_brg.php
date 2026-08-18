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
    //exit('error'.$rOrg['kode']); 
    //echo $rOrg['kode'];
}


$optsubKlmpk = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sOrg = "select kode,kode,namasubkelompok from " . $dbname . ".log_5subklbarang order by kode asc";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optsubKlmpk.="<option value=" . $rOrg['kode'] . ">" . $rOrg['kode'] . " - " . $rOrg['namasubkelompok'] . "</option>";
}

$optSup = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sSup = "select a.supplierid, a.namasupplier from ".$dbname.".log_5supplier a 
left join ".$dbname.".log_5supkelompok b on a.supplierid = b.supplierid
where tipe='SUPPLIER' order by a.namasupplier asc";
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
$arr = "##klmpkBrg##subklmpkBrg##kdBrg##tglDr##tanggalSampai##lokBeli";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>

<script>
    function getBrg()
    {
        klmpkBrg = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
        //subklmpkBrg = document.getElementById('subklmpkBrg').options[document.getElementById('subklmpkBrg').selectedIndex].value;
        param = 'klmpkBrg=' + klmpkBrg+ '&proses=subklmpkBrg'+ '&proses=getBrg';
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
    function subklmpkBrg()
    {
        klmpkBrg = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
        //subklmpkBrg = document.getElementById('subklmpkBrg').options[document.getElementById('subklmpkBrg').selectedIndex].value;
        param = 'klmpkBrg=' + klmpkBrg+ '&proses=subklmpkBrg';
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
                        document.getElementById('subklmpkBrg').innerHTML = con.responseText;
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
    function searchBrg(title, content, ev)
    {
        klmpk = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
        if (klmpk == '')
        {
            alert("Kelompok Barang Tidak Boleh Kosong!!");
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
        subklmpkBrg = document.getElementById('subklmpkBrg').value;
        nmBrg = document.getElementById('nmBrg').value;
        param = 'klmpkBrg=' + klmpkBrg + '&nmBrg=' + nmBrg + '&proses=getBarang';
        tujuan = 'log_slave_2detail_pembelian_brg.php';
        post_response_text(tujuan, param, respog);
        //alert(subklmpkBrg);
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
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['detPembBrg']).'</span>');
?>
<div>
    <fieldset style="float: left;">
		<legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
		<table cellspacing="1" border="0" >
            <tr><td><label><?php echo $_SESSION['lang']['kelompokbarang'] ?></label></td><td>:</td><td colspan='3'><select id="klmpkBrg" name="klmpkBrg" style="width:185px" onchange="subklmpkBrg()"><?php echo $optKlmpk ?></select></td></tr>
			<tr><td><label>Sub <?php echo $_SESSION['lang']['kelompokbarang'] ?></label></td><td>:</td><td colspan='3'><select id="subklmpkBrg" name="subklmpkBrg" style="width:185px" onchange="getBrg()"><option value=''><?php echo $_SESSION['lang']['all'] ?></select></td></tr>
			<tr><td><label><?php echo $_SESSION['lang']['namabarang'] ?></label></td><td>:</td><td colspan='3'><select id="kdBrg" name="kdBrg" style="width:185px"><option value=''><?php echo $_SESSION['lang']['all'] ?></option></select>&nbsp;<img src="images/search.png" class="resicon" title='<?php echo $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] ?>' onclick="searchBrg('<?php echo $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] ?>', '<fieldset style=min-width:93%><?php echo $_SESSION['lang']['find']; ?>&nbsp;<input type=text class=myinputtext id=nmBrg><button class=mybutton onclick=findBrg()><?php echo $_SESSION['lang']['find'] ?></button></fieldset><div id=containerBarang style=overflow=auto;height=380;width=485></div>', event);"></td>
			</tr>
			<tr><td><label><?php echo $_SESSION['lang']['lokasiBeli'] ?></label></td><td>:</td><td colspan='3'><select id="lokBeli" name="lokBeli" style="width:185px"><?php echo $optLokal ?></select></td></tr>
			<tr><td><label><?php echo $_SESSION['lang']['tanggal'] ?></label></td><td>:</td><td><input type="text" class="myinputtext" id="tglDr" name="tglDr" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:65px;" /></td>
			<td><?php echo $_SESSION['lang']['sd'] ?></td><td><input type="text" class="myinputtext" id="tanggalSampai" name="tanggalSampai" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:65px;" /></td></tr>

			
			<tr><td></td><td><td colspan="3"><button onclick="zPreview('log_slave_2detail_pembelian_brg', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('log_slave_2detail_pembelian_brg', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event, 'log_slave_2detail_pembelian_brg.php', '<?php echo $arr ?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

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