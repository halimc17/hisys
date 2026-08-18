<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zMaster.js"></script>
<script>
    function getDetail(kdProj) {
        param = 'kdProj=' + kdProj + '&proses=getDetail';
        tujuan = 'vhc_slave_2project.php';
        post_response_text(tujuan, param, respog);

        function respog() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //alert(con.responseText);

                        document.getElementById('detailData').style.display = 'block';
                        document.getElementById('isiData').innerHTML = con.responseText;
                        document.getElementById('awal').style.display = 'none';
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
    }
    function kembaliAja() {
        document.getElementById('detailData').style.display = 'none';
        document.getElementById('awal').style.display = 'block';
    }
    /* Function zPreview
     * Fungsi untuk preview sebuah report
     * I : target file, parameter yang akan dilempar, id container
     * O : report dalam bentuk HTML
     */
    function zPreview(fileTarget, passParam, idCont) {
        var passP = passParam.split('##');
        var param = "";
        for (i = 1; i < passP.length; i++) {
            var tmp = document.getElementById(passP[i]);
            if (i == 1) {
                param += passP[i] + "=" + getValue(passP[i]);
            } else {
                param += "&" + passP[i] + "=" + getValue(passP[i]);
            }
        }
        // alert(param);
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        var res = document.getElementById(idCont);
                        res.innerHTML = con.responseText;
                        document.getElementById('detailData').style.display = 'none';
                        document.getElementById('awal').style.display = 'block';
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        //
        //  alert(fileTarget+'.php?proses=preview', param, respon);
        post_response_text(fileTarget + '.php?proses=preview', param, respon);

    }
    function zExcel(ev, tujuan, passParam) {
        judul = 'Report Excel';
        //alert(param);
        var passP = passParam.split('##');
        var param = "";
        for (i = 1; i < passP.length; i++) {
            var tmp = document.getElementById(passP[i]);
            if (i == 1) {
                param += passP[i] + "=" + getValue(passP[i]);
            } else {
                param += "&" + passP[i] + "=" + getValue(passP[i]);
            }
        }
        param += '&proses=excel';
        //alert(param);
        printFile(param, tujuan, judul, ev)
    }
    function printFile(param, tujuan, title, ev) {
        tujuan = tujuan + "?" + param;
        width = '700';
        height = '250';
        content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
            showDialog1(title, content, width, height, ev);
    }

    function fisikKeExcel(ev, tujuan, kdProj) {
        judul = 'Report Ms.Excel';
        param = 'proses=getdetailexcel' + '&kdProj=' + kdProj;
        printFile(param, tujuan, judul, ev)
    }
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>
<?php
$optKelompok = makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$optOrg = $optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$sPeriodeCari = "select distinct substr(tanggalmulai,1,4) as tahun from " . $dbname . ".project order by substr(tanggalmulai,1,4) desc limit 10";
//$sPeriodeCari="select distinct substr(periode,1,4) as tahun from ".$dbname.".setup_periodeakuntansi order by substr(periode,1,4) desc";
$qPeriodeCari = $owlPDO->query($sPeriodeCari) or die(print " Gagal: " . PDOException::getMessage());
$qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
while ($rPeriodeCari = $qPeriodeCari->fetch()) {
    $optPeriode.="<option value='" . $rPeriodeCari['tahun'] . "'>" . $rPeriodeCari['tahun'] . "</option>";
}

$sOrg = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where
      char_length(kodeorganisasi)='4' order by namaorganisasi asc";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    //$optOrg.="<option value='" . $rOrg['kodeorganisasi'] . "'>" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
}

foreach(getOrgDetail(1) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}


$arr = "##kdOrg##thnId";
?>
<script language=javascript src=js/zTools.js></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('vhc_2project').'</span>');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<div>
    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0" >
            <tr><td><label><?php echo $_SESSION['lang']['kodeorg'] ?></label></td><td><select class='select2' id="kdOrg" name="kdOrg" style="width:150px"><? echo $optOrg ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['tahun'] ?></label></td><td><select class='select2' id="thnId" name="thnId" style="width:150px" ><? echo $optPeriode ?></select></td></tr>
            <tr><td></td></tr>
            <tr><td><td><button onclick="zPreview('vhc_slave_2project', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event, 'vhc_slave_2project.php', '<?php echo $arr ?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
        </table>
    </fieldset>
</div>


<?php
CLOSE_BOX();
OPEN_BOX();
?>

<div id=awal>
	<div id='printContainer' style='overflow:auto;height:60vh;'>

	</div>
</div>
<div id=detailData style=display:none>
	<div id=isiData>
	</div>
</div>

<?php
CLOSE_BOX();
echo close_body();
?>