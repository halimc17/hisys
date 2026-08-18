<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
 
$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg2="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg2=$qOrg2->fetch())
{
	$optPabrik.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['namaorganisasi']."</option>";
}
$arr="##kdPabrik##periode";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script>
function getPeriode()
{
    kdPabrik=document.getElementById('kdPabrik').options[document.getElementById('kdPabrik').selectedIndex].value;
    param='proses=getPeriode'+'&kdPabrik='+kdPabrik;
    tujuan='pabrik_slave_2pengolahanv2.php';
    //alert(param);
    post_response_text(tujuan, param, respog);
    function respog()
    {
          if(con.readyState==4)
          {
            if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    //alert(con.responseText);
                                    document.getElementById('periode').innerHTML=con.responseText;
                                    //load_data();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }	
    }
}
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Mill Pcrocessing Report Detail').'</span><br>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Laporan Pengolahan Detail').'</span><br>');
}

?>
<div>
<fieldset style="float: left;">
    
<legend><b>Form</b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit'];?></label></td><td>:</td><td><select id="kdPabrik" name="kdPabrik"  style="width:169px" onchange="getPeriode()"><? echo $optPabrik;?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode'];?></label></td><td>:</td><td><select id="periode" name="periode" style="width:169px;"><? echo $optPeriode;?></select></td></tr>
<tr><td></td></tr>
<tr><td><td><td>
        <button onclick="zPreview('pabrik_slave_2pengolahanv2','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
        <!--<button onclick="zPdf('pabrik_slave_2perawatanv2','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>-->
        <button onclick="zExcel(event,'pabrik_slave_2pengolahanv2.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='clear:both;'><legend><b>Print Area</b></legend>
<!-- <div id='printContainer' style='overflow:auto;height:360px;'> -->
<div id='printContainer'>

</div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>