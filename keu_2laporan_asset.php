<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');



$optstatus="<option value=''>".$_SESSION['lang']['all']."</option>";
$optjb=$optOrg=$optlaporan= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($data=$res->fetch())
{
    $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}

$optAst="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodetipe,namatipe FROM ".$dbname.".sdm_5tipeasset";
$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($data=$res->fetch())
{
        $optAst.="<option value=".$data['kodetipe'].">".$data['namatipe']."</option>";
}
$optBatch="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
 $sBatch="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=$owlPDO->query($sBatch) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rBatch=$res->fetch())
{
    $optBatch.="<option value='".$rBatch['periode']."'>".$rBatch['periode']."</option>";
}
$optTipeAsset=$optsubTipeAsset="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTipeAsset="select distinct kodetipe,namatipe from ".$dbname.".sdm_5tipeasset order by namatipe asc";
$res=$owlPDO->query($sTipeAsset) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rTipeAsset=$res->fetch())
{
    $optTipeAsset.="<option value='".$rTipeAsset['kodetipe']."'>".$rTipeAsset['namatipe']."</option>";
}


$sTipeAsset="select kodesub,namasub from ".$dbname.".sdm_5subtipeasset order by namasub asc";
$res=$owlPDO->query($sTipeAsset) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rTipeAsset=$res->fetch())
{
    $optsubTipeAsset.="<option value='".$rTipeAsset['kodesub']."'>".$rTipeAsset['namasub']."</option>";
}

//jenis biaya

# Tipe Laporan
$arrtipelaporan = array('1' => 'Rekap Aktiva', '2' => 'Detail Aktiva');
foreach($arrtipelaporan as $key => $val):
    $optlaporan .= "<option value='".$key."'>".$val."</option>";
endforeach;

$optstatus.="<option value='1'>" . $_SESSION['lang']['aktif'] . "</option>";
$optstatus.="<option value='0'>" . $_SESSION['lang']['tidakaktif'] . "</option>";

$arr="##kdOrg##unit##kdAst##tpAsset##subtpAsset##jenisbiaya##status##kodeproject##kodeasset##tipelaporan";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>


<script language=javascript>
        function batal() {
            document.getElementById('kdOrg').value = '';
            document.getElementById('kdAst').value = '';
            document.getElementById('printContainer').innerHTML = '';
        }

        function getsubtpasset() {
            tpAsset = document.getElementById('tpAsset').value;
            param = 'method=getsubtpasset' + '&tpAsset=' + tpAsset;
            tujuan = 'keu_slave_2laporanAsset_unit.php';
            post_response_text(tujuan, param, respog);
            function respog() {
                if (con.readyState == 4) {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert('ERROR TRANSACTION,\n' + con.responseText);
                        } else {
                            //alert(con.responseText);
                            document.getElementById('subtpAsset').innerHTML = con.responseText;
                            //.value=trim(con.responseText);
                        }
                    } else {
                        busy_off();
                        error_catch(con.status);
                    }
                }
            }
        }

        function getUnit(obj) {
            // var pt = obj.options[obj.selectedIndex].value;
            // param='pt='+pt,

            pt = document.getElementById('kdOrg').value;

            param = 'method=getUnit';
            param += '&pt=' + pt;
            tujuan = 'keu_slave_2laporanAsset_unit.php';
            if (pt == '') {
                unit.disabled = true;
            } else {
                post_response_text(tujuan, param, respog);
            }
            function respog() {
                if (con.readyState == 4) {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert('ERROR TRANSACTION,\n' + con.responseText);
                        } else {
                            var unit = document.getElementById('unit');
                            unit.innerHTML = con.responseText;
                            unit.disabled = false;
                        }
                    } else {
                        busy_off();
                        error_catch(con.status);
                    }
                }
            }
        }

        function getjbiaya(jenisbiaya) {

            unit = document.getElementById('unit').value;
            param = 'method=getjbiaya' + '&unit=' + unit + '&jenisbiaya=' + jenisbiaya;

            //alert(param);
            tujuan = 'keu_slave_2laporanAsset_unit.php';
            post_response_text(tujuan, param, respog);
            function respog() {
                if (con.readyState == 4) {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert('ERROR TRANSACTION,\n' + con.responseText);

                        } else {
                            //alert(con.responseText);
                            document.getElementById('jenisbiaya').innerHTML = con.responseText;
                        }
                    } else {
                        busy_off();
                        error_catch(con.status);

                    }
                }
            }
        }

</script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('keu_2laporan_asset').'</span><br>');
?>
<div style="margin-bottom: 30px;">
<fieldset style="float: left;" >
<legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>

<table cellspacing="1" border="0" >
    <tr>
        <td><label><?php echo $_SESSION['lang']['pt']?></label></td><td width="10">:</td><td width="155"><select class='select2' id="kdOrg" name="kdOrg" style="width:200px;" onchange="getUnit(this)"><?php echo $optOrg?></select></td>
        <td><label><?php echo $_SESSION['lang']['tipeasset']?></label></td>
        <td>:</td>
        <td><select class='select2' id="tpAsset" name="tpAsset" style="width:200px;"  onchange=getsubtpasset()><?php echo $optTipeAsset?></select></td>
        <td><?php echo $_SESSION['lang']['project']?></td><td>:</td><td><input type=text id=kodeproject size=50 class=myinputtext style="width:200px;" ></td>
	</tr>

    <tr>
        <td><label><?php echo $_SESSION['lang']['unit']?></label></td>
        <td width="10">:</td>
        <td width="155">
            <select class='select2' id="unit" name="unit" style="width:200px;" onchange="getjbiaya()" disabled ><option><?php echo $_SESSION['lang']['pilihdata']?></option></select>
        </td>
    
        <td><label><?php echo $_SESSION['lang']['subtipeasset']?></label></td><td>:</td><td><select class='select2' id="subtpAsset" name="subtpAsset" style="width:200px;"><?php echo $optsubTipeAsset?></select></td>
        <td><?php echo $_SESSION['lang']['kodeasset']?></td>
        <td>:</td>
        <td><input type=text id=kodeasset size=50 class=myinputtext style="width:200px;" ></td>
	</tr>

    <tr>
        <td><label><?php echo $_SESSION['lang']['sdbulan']?></label></td>
        <td>:</td>
        <td><select class='select2' id="kdAst" name="kdAst" style="width:200px;"><?php echo $optBatch?></select></td>
	
        <td><label><?php echo $_SESSION['lang']['status']?></label></td>
        <td>:</td>
        <td><select class='select2' id="status" name="status" style="width:200px;"><?php echo $optstatus?></select></td>

        <td><label>Tipe Laporan</label></td>
        <td>:</td>
        <td><select class='select2' id="tipelaporan" name="tipelaporann" style="width:204px;"><?php echo $optlaporan?></select></td>
	</tr>
   
	<tr></tr>
    <tr style=display:none>
        <td><?php echo $_SESSION['lang']['jenisbiaya']?></td><td>:</td><td><select class='select2' style="width:200px" id="jenisbiaya"><?php echo $optjb?></select></td>
    </tr>
    <tr></tr>

    
    <td><td><td>
        <button onclick="zPreview('keu_slave_2laporanAsset','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
        <button onclick="zExcel(event,'keu_slave_2laporanAsset.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
        <button onclick="batal()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button></td></tr>
</table>
</fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer'></div>
<?php
CLOSE_BOX();
echo close_body();
?>