<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['managerialreport']) . ' : '.getMenu('mr_produksiTbs').'</span>');
?>
<?php
$optKlmpk="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optKlmpk.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$optKlmpk2="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' order by namaorganisasi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optKlmpk2.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}
$optDtTp='';
$optTp = array(""=>$_SESSION['lang']['all'],"I"=>"Inti","P"=>"Plasma");
foreach($optTp as $lst=>$lstNm){
    $optDtTp.="<option value='".$lst."'>".$lstNm."</option>";
}
//$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optPeriode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}
$arr="##kdPt##kdUnit##periodeDt##intiplasma";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReportUpd.js'></script>

<script>
function getUnit()
{
	pt=document.getElementById('kdPt').options[document.getElementById('kdPt').selectedIndex].value;
        prd=document.getElementById('periodeDt').options[document.getElementById('periodeDt').selectedIndex].value;
	param='kdPt='+pt+'&proses=getData'+'&periodeDt='+prd;
	tujuan="mr_slaveproduksiTbs.php";
	 
         post_response_text(tujuan, param, respog);
	 function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    
                  	document.getElementById('kdUnit').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td><select id="kdPt" name="kdPt" style="width:150px" onchange="getUnit()"><?php echo $optKlmpk?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><select id="kdUnit" name="kdUnit" style="width:150px"><? echo $optKlmpk2?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['intiplasma']?></label></td><td><select id="intiplasma" name="intiplasma" style="width:150px"><? echo $optDtTp?></select></td></tr>
    <tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="periodeDt" style="width:150px"><?php echo $optPeriode;?></select></td></tr>



<tr><td></td><td colspan="2"><button onclick="zPreview('mr_slaveproduksiTbs','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('mr_slaveproduksiTbs','<?php echo $arr?>','printContainer2')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'mr_slaveproduksiTbs.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>
<?php CLOSE_BOX(); 

OPEN_BOX('','');?>
<fieldset style=min-height:350px>
<legend><b><?php echo $_SESSION['lang']['list']?></b></legend>
<div id="excPrev">

<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>
<thead><tr class=rowheader>
<td rowspan=4 align=center   style='width:40px;'><? echo $_SESSION['lang']['tahuntanam'] ?></td>
<td rowspan=4 align=center  style='width:40px;'><? echo $_SESSION['lang']['pt'] ?></td>
<td rowspan=4 align=center  style='width:40px;'><? echo $_SESSION['lang']['afdeling'] ?></td>
<td colspan=2  rowspan=2  align=center  style='width:100px;'>Luas TM (Ha)</td>
<td colspan=5   align=center   style='width:270px;'>Total Produksi (Kg)</td>
<td colspan=3   align=center   style='width:150px;'>Kg / Ha</td></tr>
<tr class=rowheader>
<td colspan=2  align=center style='width:50px;'>Bulan Ini</td>
<td colspan=2  align=center style='width:50px;'>s/d Bulan Ini</td>
<td rowspan=3  align=center  style='width:70px;'>Setahun</td> 
<td align=center  style='width:40px;'>BI</td>
<td align=center  style='width:40px;'>s/d BI</td>
<td rowspan=3  align=center  style='width:70px;'>Setahun<br>Kg / Ha</td></tr>
<tr class=rowheader>
<td align=center rowspan=2 style='width:50px;'>Realisasi</td>
<td align=center rowspan=2 style='width:50px;'>Anggaran</td>
<td align=center rowspan=2 style='width:50px;'>Realisasi</td>
<td align=center rowspan=2 style='width:50px;'>Anggaran</td>
<td align=center rowspan=2 style='width:50px;'>Realisasi</td>
<td align=center rowspan=2 style='width:50px;'>Anggaran</td>
<td align=center rowspan=2 style='width:40px;'>Kg / Ha</td>
<td align=center rowspan=2 style='width:40px;'>Kg / Ha</td></tr></thead>

<tbody  id="printContainer"> </tbody>

</table>
</div>

      <div id="pdfData" style="display: none;">
      <div id='printContainer2' style='overflow:auto;height:350px;max-width:1220px'>

</div>
</div>
</fieldset>
     
<?php
CLOSE_BOX();
echo close_body();
?>