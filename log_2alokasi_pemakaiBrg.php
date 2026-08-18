<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%'";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
}

$arr = "##kdGudang##periode";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script>
    function getPeriode()
    {
        kdGudang = document.getElementById('kdGudang').options[document.getElementById('kdGudang').selectedIndex].value;
        param = 'kdGudang=' + kdGudang + '&proses=getPeriode';
        tujuan = "log_slave_2alokasi_pemakaiBrg.php";
        //alert(param);	

        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                    } else {
                        // Success Response
                        document.getElementById('periode').innerHTML = con.responseText;
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
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['lapAlokasiBrg']).'</span><br>');
?>
<div>
    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0" >
            <tr><td><label><?php echo $_SESSION['lang']['pilihgudang'] ?></label></td><td>:</td><td><select id="kdGudang" name="kdGudang" style="width:150px" onchange="getPeriode()"><?php echo $optOrg ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['periode'] ?></label></td><td>:</td><td><select id="periode" name="periode" style="width:150px"><option value=''><? echo $_SESSION['lang']['all']?></option></select></td></tr>
            <tr><td><td><td>
            <button onclick="zPreview('log_slave_2alokasi_pemakaiBrg', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event, 'log_slave_2alokasi_pemakaiBrg.php', '<?php echo $arr ?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

        </table>
    </fieldset>
</div>
<!-- 
<button onclick="zPdf('log_slave_2alokasi_pemakaiBrg', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
-->
<?php
CLOSE_BOX();
OPEN_BOX();
/*?>
<fieldset style='clear:both;'><legend><b>Print Area</b></legend>
    <div id='printContainer' style='overflow:auto;height:400px;'>

    </div></fieldset>

<?php*/

echo"<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<table class=sortable style='position:absolut;' cellspacing=1 border=0 >
	     <thead>
		    <tr>
			<td align=center style='width:50px;'>No.</td>
			<td align=center style='width:200px;'>" . $_SESSION['lang']['nojurnal'] . "</td>
			<td align=center style='width:100px;'>" . $_SESSION['lang']['tanggal'] . "</td>
			<td align=center style='width:200px;'>" . $_SESSION['lang']['notransaksi'] . "</td>
			<td align=center style='width:300px;'>" . $_SESSION['lang']['keterangan'] . "</td>
			<td align=center style='width:100px;'>" . $_SESSION['lang']['noakundisplay'] . "</td>
			<td align=center style='width:100px;'>" . $_SESSION['lang']['akun'] . "</td>
			<td align=center style='width:100px;'>" . $_SESSION['lang']['debet'] . "</td>
			<td align=center style='width:100px;'>" . $_SESSION['lang']['kredit'] . "</td>
			<td align=center style='width:100px;'>" . $_SESSION['lang']['station'] . "</td>
			<td align=center style='width:100px;'>" . $_SESSION['lang']['mesin'] . "</td>
			<td align=center style='width:100px;'>" . $_SESSION['lang']['kodevhc'] . "</td>
        </tr></thead>
      </table>";
 echo"<div style='width:1800px;height:400px;overflow: auto;'>
		<table class=sortable border=0 cellspacing=1>
		<tbody id=printContainer>
		</tbody>
	</table>
</div>";


CLOSE_BOX();
echo close_body();
?>