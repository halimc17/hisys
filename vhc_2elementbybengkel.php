<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPt=$optPeriode;
$optKdvhc="<option value=''>".$_SESSION['lang']['all']."</option>";
//semua pt
$sPt="select distinct kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI'";
$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
$qPt->setFetchMode(PDO::FETCH_ASSOC);
while($rPt=$qPt->fetch()){
    $optPt.="<option value='".$rPt['kodeorganisasi']."'>".$rPt['kodeorganisasi']." - ".$rPt['namaorganisasi']."</option>";
}

$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optKodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optKodeorg.="</optgroup>";
	}
}


//periode akuntansi
$sPeriode="select distinct left(tanggal,7) as periode from ".$dbname.".vhc_penggantianht order by left(tanggal,7) desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch()){
	$optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
}


$arr="##periodeId##unitId##kdVhc";
//$arrKry="##kdeOrg##period##idKry##tgl_1##tgl_2";
?>

<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});

});
function Clear1() {
    document.getElementById('periodeId').options[0].selected = true;
    document.getElementById('unitId').options[0].selected = true;
    document.getElementById('kdVhc').options[0].selected = true;
    document.getElementById('printContainer').innerHTML = "";
}

function getBulan() {
    unitId = document.getElementById('unitId').options[document.getElementById('unitId').selectedIndex].value;
    param = 'proses=ambilkdvhc' + '&unitId=' + unitId;
    tujuan = 'vhc_slave_2elementbybengkel.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('kdVhc').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function displayDetail(kdVhc, tgl, unit, mode, jns, ev) {
    param = 'kdVhc=' + kdVhc + '&tanggal=' + tgl + '&unitId=' + unit + '&proses=DetailData' + '&mode=' + mode;
    param += '&jenis=' + jns;
    // tujuan = 'vhc_slave_2elementbybengkel.php' + "?" + param;
    // width = '750';
    // height = '250';
    // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1('Detail transaction' + kdVhc + ',' + tgl, content, width, height, ev);
	tujuan = 'vhc_slave_2elementbybengkel.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('kdVhc').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					
					// alertify.popuppdf("Detail","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_2elementbybengkel.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');	
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}
</script>
<link rel=stylesheet type='text/css' href='style/zTable.css'>
<?
OPEN_BOX('','<span class=judul>'.getMenu('vhc_2elementbybengkel').'</span><br>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']; ?></b></legend>
<table cellspacing="1" border="0" >
<tr>
    <td><label><?php echo $_SESSION['lang']['traksi']?></label></td><td>:</td>
    <td><select class='select2' id="unitId" name="unitId" style="width:200px" onchange="getBulan()">
        <?php echo $optKodeorg?>
    </select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['kodevhc']?></label></td><td>:</td>
	<td><select class='select2' id="kdVhc" name="kdVhc" style="width:200px">
		<?php echo $optKdvhc?>
	</select></td>
</tr>

<tr>
	<td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td>
	<td><select class='select2' id="periodeId" name="periodeId" style="width:200px">
        <?php echo $optPeriode?>
	</select></td>
</tr>

<tr colspan="2"></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>
	<button onclick="zPreview('vhc_slave_2elementbybengkel','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
	<!--<button onclick="zPdf('traksi_slave_2biayaBengkel','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>-->
	<button onclick="zExcel(event,'vhc_slave_2elementbybengkel.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
	<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button>
</td></tr>
</table>
</fieldset>

<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['info']; ?></b></legend>
<table cellspacing="1" border="0" >
<tr>
    <td>Bila biaya Workshop / Gaji belum tersedia / update, perlu dilakukan / update proses akhir bulan: Gaji dan Alokasi Workshop/Traksi</td>
</tr>
</table>
</fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer' style='overflow:auto;height:450px;'></div>

<?php
CLOSE_BOX();
echo close_body();
?>