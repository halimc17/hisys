kdpa<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_2penerimaantbs').'</span><br>'); 
?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optOrg.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optOrg.="</optgroup>";
	}
}
// $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch())
// {
// 	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
// }

$intex=array('0'=>'External','1'=>'Internal','2'=>'Afiliasi');
$optTbs="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optTbsRe="<option value='3'>".$_SESSION['lang']['all']."</option>";
foreach($intex as $dt => $rw)
{
	$optTbs.="<option value=".$dt.">".$rw."</option>";
        $optTbsRe.="<option value=".$dt.">".$rw."</option>";
}
$arr="##kdPabrik##tgl_1##tgl_2##tipeIntex##unit##pilTamp##divisi##intiplasma";
$arr2="##kdPabrik__2##tgl__2##kdUnit__2##kdAfdeling__2";
$arr3="##kdPabrik__3##kdUnit__3##periode__3";
$arrRe="##kdPabrikRe##tglRe";
$optPabrik1="<option value=''>".$_SESSION['lang']['all']."</option>";

$optPabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
// $sOrg2="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
// $qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg2->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg2=$qOrg2->fetch())
// {
// 	$optPabrik1.="<option value='".$rOrg2['kodeorganisasi']."'>".$rOrg2['namaorganisasi']."</option>";
// 	$optPabrik.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['namaorganisasi']."</option>";
// }
$arrunitPabrik = getOrgDetail(13);
if($arrunitPabrik == 0){
    $esqiel="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
    $hasile=fetchData($esqiel);
    foreach ($hasile as $v) {
        $arrunitPabrik[$v['kodeorganisasi']]=$v['namaorganisasi'];
    }
}
foreach ($arrunitPabrik as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optPabrik1.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		$optPabrik.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optPabrik1.="<option value='".$key."'>".$key." - ".$val."</option>";			
    $optPabrik.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optPabrik1.="</optgroup>";
		$optPabrik.="</optgroup>";
	}
}


$sOrg="select distinct kodeorg from ".$dbname.".pabrik_timbangan where kodeorg!='' and millcode like '%%' order by kodeorg";
$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$unitintimbangan='(';
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qOrg->fetch())
{
        $optUnit.="<option value=".$rData['kodeorg'].">".$rData['kodeorg']."</option>";
        $unitintimbangan.="'".$rData['kodeorg']."',";
}

$unitintimbangan=substr($unitintimbangan,0,-1);
$unitintimbangan.=')';
$sOrg="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' ";
if(!empty($unittimbangan)) {
	$sOrg.="and induk in ".$unitintimbangan." ";
}

$sOrg.="order by kodeorganisasi";
$optAfdeling2="<option value=''>".$_SESSION['lang']['all']."</option>";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qOrg->fetch())
{

        $optAfdeling2.="<option value=".$rData['kodeorganisasi'].">".$rData['kodeorganisasi']."</option>";
}

$optPeriode="<option value=''></option>";
$sOrg="select distinct substr(tanggal,1,7) as periode from ".$dbname.".pabrik_timbangan where kodeorg!='' and millcode like '%%' order by periode desc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qOrg->fetch()){
    $optPeriode.="<option value=".$rData['periode'].">".$rData['periode']."</option>";
}

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script>
function getKode() {
    kdPabrik = document.getElementById('kdPabrik').options[document.getElementById('kdPabrik').selectedIndex].value;
    tipeIntex = document.getElementById('tipeIntex').options[document.getElementById('tipeIntex').selectedIndex].value;

    param = 'tipeIntexRe=' + tipeIntex + '&kdPabrik=' + kdPabrik + '&proses=getKodeorg';
    tujuan = "pabrik_slave_2penerimaantbsRe.php";

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('unit').innerHTML = con.responseText;
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

function getdiv() {
    kdunit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;

    param = 'unit=' + kdunit + '&proses=getdiv';
    tujuan = "pabrik_slave_2penerimaantbs.php";

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('divisi').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function getKodeRe() {
    tipeIntex = document.getElementById('tipeIntexRe').options[document.getElementById('tipeIntexRe').selectedIndex].value;
    param = 'tipeIntexRe=' + tipeIntex + '&proses=getKodeorg';
    tujuan = "pabrik_slave_2penerimaantbsRe.php";
    // alert(param);
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('unitRe').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
    //  alert(fileTarget+'.php?proses=preview', param, respon);


}
function getAfd(id) {
    kdOrg = document.getElementById('kdOrg_' + id).getAttribute('value');
    tglAfd = document.getElementById('tanggal_' + id).getAttribute('value');
    param = "kodeOrg=" + kdOrg + "&proses=getAfdeling" + "&brsKe=" + id + "&tglAfd=" + tglAfd;
    tujuan = "kebun_slave_3laporanProduksi.php";
    //alert(param);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //	alert(con.responseText);
                    document.getElementById('detail_' + id).innerHTML = con.responseText;
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
function getUnit(n) {
    kdPabrik = document.getElementById('kdPabrik__' + n).options[document.getElementById('kdPabrik__' + n).selectedIndex].value;
    param = "kodePabrik=" + kdPabrik + "&proses=getUnit";
    tujuan = "kebun_slave_3laporanProduksi.php";
    //alert(param);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //	alert(con.responseText);
                    document.getElementById('kdUnit__' + n).innerHTML = con.responseText;
                    getAfdeling2();
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
function getAfdeling2() {
    kdPabrik = document.getElementById('kdPabrik__2').options[document.getElementById('kdPabrik__2').selectedIndex].value;
    kdUnit = document.getElementById('kdUnit__2').options[document.getElementById('kdUnit__2').selectedIndex].value;
    param = "kodePabrik=" + kdPabrik + "&kodeUnit=" + kdUnit + "&proses=getAfdeling2";
    tujuan = "kebun_slave_3laporanProduksi.php";
    //	alert(param);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //	alert(con.responseText);
                    document.getElementById('kdAfdeling__2').innerHTML = con.responseText;
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
function detailBlok(idAwal, id) {
    kdBlok = document.getElementById('kdBlok_' + idAwal + '_' + id).innerHTML;
    nospb = document.getElementById('nospb_' + idAwal + '_' + id).innerHTML;
    tgl = document.getElementById('tanggal_' + idAwal).innerHTML;

    param = 'kdBlok=' + kdBlok + '&proses=getPrestasi' + '&tgl=' + tgl + '&brsKe=' + idAwal + '&endKe=' + id + '&nospb=' + nospb;
    tujuan = "kebun_slave_3laporanProduksi.php";

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //	alert(con.responseText);
                    document.getElementById('detailBlok_' + idAwal + '_' + id).innerHTML = con.responseText;
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
function closeBlok(idAwal, id) {
    document.getElementById('detailBlok_' + idAwal + '_' + id).innerHTML = '';
}
function closeAfd(id) {
    document.getElementById('detail_' + id).innerHTML = '';
}

function preview1(type) {
    kdpbr = document.getElementById('kdpbr').value;
    tgl1 = document.getElementById('tgl1').value;

    param = 'kdpbr=' + kdpbr + '&tgl1=' + tgl1 + '&type=' + type + '&proses=preview1';
    tujuan = "pabrik_slave_2penerimaantbsRe.php";

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('printContainer').innerHTML = con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function printexcel1(ev) {
    kdpbr = document.getElementById('kdpbr').value;
    tgl1 = document.getElementById('tgl1').value;

    param = 'kdpbr=' + kdpbr + '&tgl1=' + tgl1 + '&proses=preview1&type=excel';

    tujuan = 'pabrik_slave_2penerimaantbsRe.php';
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev)
}

function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<div>
<fieldset style="float: left;min-height:126px">
<legend><b><?php echo $_SESSION['lang']['rePenerimaanTbs']?> Per Jam</b></legend>
<table cellspacing="1" border="0" >
    <tr>
		<td>
			<label><?php echo $_SESSION['lang']['pabrik']?></label></td>
		<td>:</td>
		<td>
			<select id="kdpbr" name="kdpbr" style="width:165px"><? echo $optPabrik1 ?></select>
		</td>
	</tr>
	<tr>
		<td>
			<label><?php echo $_SESSION['lang']['tanggal']?></label>
		</td>
		<td>:</td>
		<td>
			<input type="text" class="myinputtext" id="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  style="width:160px" maxlength="10" readonly value='<?= date("d-m-Y") ?>' readonly />
		</td>
	</tr>
	<tr>
		<td></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button onclick="preview1('html')" class="mybutton">Preview</button>
			<button onclick="printexcel1(event)" class="mybutton">Excel</button>
		</td>
	</tr>
</table>
</fieldset>
</div>      
	  
<div>
<fieldset style="float: left;min-height:126px">
<legend><b><?php echo $_SESSION['lang']['rePenerimaanTbs']?></b></legend>
<table cellspacing="1" border="0" >
    <tr><td><label><?php echo $_SESSION['lang']['pabrik']?></label></td><td>:</td><td><select  id="kdPabrikRe" name="kdPabrikRe"  style="width:165px"><? echo $optPabrik?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input type="text" class="myinputtext" id="tglRe" onmousemove="setCalendar(this.id)" onkeypress="return false;"  style="width:160px" maxlength="10" value='<?= date('d-m-Y') ?>'  readonly/>
</td></tr>
<tr><td></td></tr>
<tr><td><td><td><button onclick="zPreview('pabrik_slave_2penerimaantbsRe','<?php echo $arrRe?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
        <button onclick="zPdf('pabrik_slave_2penerimaantbsRe','<?php echo $arrRe?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
        <button onclick="zExcel(event,'pabrik_slave_2penerimaantbsRe.php','<?php echo $arrRe?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>
<?php
$arrTampilan=array("0"=>"Default","1"=>$_SESSION['lang']['harian']);
$optTampilan="";
foreach($arrTampilan as $lstTampilan=>$disTamp)
{
	$optTampilan.="<option value='".$lstTampilan."'>".$disTamp."</option>";
}

$optip="<option value=''>".$_SESSION['lang']['all']."</option>";
$optip.="<option value='1'>Inti</option>";
$optip.="<option value='0'>Plasma</option>";
?>
<div>
<fieldset style="float: left;min-height:126px">
<legend><b><?php echo $_SESSION['lang']['rPenerimaanTbs']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pabrik']?></label></td><td>:</td><td><select onchange="getKode()" id="kdPabrik" name="kdPabrik"  style="width:165px"><? echo $optPabrik?></select></td>
<td><label><?php echo $_SESSION['lang']['tbs']?></label></td><td>:</td><td><select id="tipeIntex" name="tipeIntex" onchange="getKode()" style="width:80px"><? echo $optTbsRe?></select></td>

<td><label><?php echo $_SESSION['lang']['intiplasma']?></label></td><td>:</td><td><select id="intiplasma" name="intiplasma" style="width:80px"><? echo $optip?></select></td>
</tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input type="text" class="myinputtext" id="tgl_1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="7" maxlength="10" value='<?= date('01-m-Y') ?>' readonly/> s.d. <input type="text" class="myinputtext" id="tgl_2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="7" maxlength="10" value='<?= date('d-m-Y') ?>'  readonly/>
</td><td><?php echo $_SESSION['lang']['unit']." / ".$_SESSION['lang']['supplier']?></td><td>:</td><td colspan=4><select id="unit" onchange="getdiv()" style="width:245px"><option value=""><? echo $_SESSION['lang']['all'] ?></option></select></td></tr>
<tr></tr>
<tr></tr>
<tr><td><?php echo $_SESSION['lang']['tampilkan']?></td><td>:</td><td><select id="pilTamp" style="width:165px"><? echo $optTampilan ?></select></td>
<td><?php echo $_SESSION['lang']['divisi']?></td><td>:</td><td  colspan=4><select id="divisi" style="width:245px"><option value=""><? echo $_SESSION['lang']['all'] ?></option></select></td>
</tr>
<tr><td></td></tr>
<tr><td><td><td><button onclick="zPreview('pabrik_slave_2penerimaantbs','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event,'pabrik_slave_2penerimaantbs.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>


<!--
<button onclick="zPdf('pabrik_slave_2penerimaantbs','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
-->
   
<div>
<fieldset style="float: left;" hidden>
<legend><b><?php echo $_SESSION['lang']['rPenerimaanTbs']."/".$_SESSION['lang']['afdeling']."/".$_SESSION['lang']['tanggal']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pabrik']?></label></td><td><select id="kdPabrik__2" name="kdPabrik__2" onchange="getUnit(2)" style="width:165px"><? echo $optPabrik?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td><input type="text" class="myinputtext" id="tgl__2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="23" maxlength="10"  readonly/></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><select id="kdUnit__2" name="kdUnit__2" onchange="getAfdeling2()" style="width:165px"><? echo $optUnit?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['afdeling']?></label></td><td><select id="kdAfdeling__2" name="kdAfdeling__2" style="width:165px"><? echo $optAfdeling2?></select></td></tr>
<tr><td></td><td></td></tr>
<tr><td colspan="2"></td></tr>
<tr><td><td><button onclick="zPreview('pabrik_slave_2penerimaantbs2','<?php echo $arr2?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('pabrik_slave_2penerimaantbs2','<?php echo $arr2?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'pabrik_slave_2penerimaantbs2.php','<?php echo $arr2?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>      

<div>
<fieldset style="float: left;" hidden>
<legend><b><?php echo $_SESSION['lang']['rPenerimaanTbs']."/".$_SESSION['lang']['afdeling']."/".$_SESSION['lang']['bulan']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pabrik']?></label></td><td><select id="kdPabrik__3" name="kdPabrik__3" onchange="getUnit(3)" style="width:169px"><? echo $optPabrik?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><select id="kdUnit__3" name="kdUnit__3" style="width:169px"><? echo $optUnit?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="periode__3" name="periode__3" style="width:169px"><? echo $optPeriode?></select></td></tr>
<tr><td></td><td></td></tr>
<tr><td colspan="2"></td></tr>
<tr><td><td><button onclick="zPreview('pabrik_slave_2penerimaantbs3','<?php echo $arr3?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('pabrik_slave_2penerimaantbs3','<?php echo $arr3?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'pabrik_slave_2penerimaantbs3.php','<?php echo $arr3?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>      
<?php
      
CLOSE_BOX();
OPEN_BOX();
?>

<div style=clear:both></div>

<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			<img title='Full Screen' class='zImgBtn' src='images/full-screen.png'>
		</a>
		<!--<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
			<img title='Fixed Header Table' class='zImgBtn' src='images/fix-header.gif'>
		</a>-->
	</div>
	<div id='printContainer' class='table-scroll'></div>
</div>
<?php
CLOSE_BOX();
echo close_body();
?>