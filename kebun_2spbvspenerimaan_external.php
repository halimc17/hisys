<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php
 
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$sOrg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}
 
$arr="##traksiId##afdId##periode";


$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

for($x=0;$x<=24;$x++){
    $t=mktime(0,0,0,date('m')-$x,15,date('Y'));
    $optPeriode.="<option value='".date('Y-m',$t)."'>".date('Y-m',$t)."</option>";
}
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>
function getPeriode() {
	trksi = document.getElementById('traksiId').options[document.getElementById('traksiId').selectedIndex].value;

	param = 'traksiId=' + trksi + '&proses=getPrd';
	//alert(param);
	tujuan = 'kebun_slave_2spbvspenerimaan_external.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					// Success Response
					//alert(con.responseText);
					cor = con.responseText.split("####");
					//document.getElementById('periode').innerHTML=cor[0];
					document.getElementById('afdId').innerHTML = cor[1];

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<?
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2spbvspenerimaan_external').'</span><br>');
?>
<fieldset style="float: left;">
<legend><?echo $_SESSION['lang']['form'];?></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['kebun']?></label></td><td>:</td><td><select class='select2' id="traksiId" name="traksiId"  style="width:200px" onchange=getPeriode()><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['divisi']?></label></td><td>:</td><td><select class='select2' id="afdId" name="afdId"  style="width:200px"><?php echo $optAfd?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td>
<select  class='select2' id="periode"  style=width:200px><?php echo $optPeriode?></select></td></tr>


<tr><td colspan="2"><td colspan="2" align=left ><button onclick="zPreview('kebun_slave_2spbvspenerimaan_external','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
                    <!--<button onclick="zPdf('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
                        <button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>-->
                    <button onclick="zExcel(event,'kebun_slave_2spbvspenerimaan_external.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
                    

</table>
</fieldset>

<?php
CLOSE_BOX();
OPEN_BOX();
?>
<div id='both_report'>
    <div id='head_tableboth' align=right>
        <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
            <img title='Full Screen' class='resicon' src='images/full-screen.png'>
        </a>
        <a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
            <img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
        </a>
    </div>
<div id='printContainer' style='overflow:auto;min-height:400px;width:100%'>
<?php
//echo"<pre>";
//print_r($_SESSION);
//echo"</pre>";
?>


<?php

CLOSE_BOX();
echo close_body();
?>