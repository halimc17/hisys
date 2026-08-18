<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optAfd=$optkodecustomer="<option value=''>".$_SESSION['lang']['all']."</option>";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	// $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
// }else{
	// $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
// }
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch())
// {
	// $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
// }

$arrunit=array();
$arrunit=getOrgDetail(1);
foreach($arrunit as $val=>$nama){
	if($val==$_SESSION['empl']['lokasitugas']){
		$optOrg.="<option value='".$val."' selected>".$val." - ".$nama."</option>";
	}else{
		$optOrg.="<option value='".$val."' >".$val." - ".$nama."</option>";
	}
} 

$str="select * from ".$dbname.".pmn_4customer order by namacustomer asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optkodecustomer.="<option value=".$bar['kodecustomer'].">".$bar['kodecustomer']." - ".$bar['namacustomer']."</option>";
}

$arr="##kbnId##afdId##tgl1##tgl2##kodecustomer";

?>
<script language=Javascript1.2 src=js/zTools.js></script>
<script>
function getKode()
{
	tipeIntex=document.getElementById('kbnId').options[document.getElementById('kbnId').selectedIndex].value;
	param='kbnId='+tipeIntex+'&proses=getKodeAfd';
	tujuan="kebun_slave_2hasiltimbanganeksternal.php";
    post_response_text(tujuan, param, respon);
	 function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                  	document.getElementById('afdId').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
</script>
<script language=Javascript1.2 src=js/zReport.js></script>

<link rel=stylesheet type=text/css href=style/zTable.css>

<?
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2hasiltimbangeksternal').'</span><br>');
?>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['kebun']?></label></td><td>:</td><td><select id="kbnId" name="kbnId" onchange="getKode()" style="width:193px"><? echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['customer']?></label></td><td>:</td><td><select id="kodecustomer" name="kodecustomer" style="width:193px"><? echo $optkodecustomer?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['afdeling']?></label></td><td>:</td><td><select id="afdId" name="afdId"  style="width:193px"><? echo $optAfd?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input type="text" class="myinputtext" id="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10"  readonly/> s.d <input type="text" class="myinputtext" id="tgl2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10"  readonly/>
</td></tr>
<tr height="2"><td colspan="2"></td></tr>
<tr><td colspan="2" align=center>
<td colspan="2" align=left>
<button onclick="zPreview('kebun_slave_2hasiltimbanganeksternal','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
<button onclick="zExcel(event,'kebun_slave_2hasiltimbanganeksternal.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

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
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:370px;width:100%'>

</div></fieldset></div>

<?php
CLOSE_BOX();
echo close_body();
?>