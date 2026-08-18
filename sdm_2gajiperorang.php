<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

 
//ambil periode gaji sesuai dengan lokasi tugas
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$optKary=$optPeriode;
$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())	
{
	$optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
}
 
//ambil kodeorgannisasi dan organisasi dibawahnya
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
               where kodeorganisasi in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
               order by namaorganisasi asc";	
        //$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
}
else if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
               where tipe='KEBUN' and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') 
               order by namaorganisasi asc";	
}
else{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi "
            . "where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())	
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']."-".$rOrg['namaorganisasi']."</option>";
}
$optAfd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optpKary="<option value=''>".$_SESSION['lang']['all']."</option>";
$stpKar="select * from ".$dbname.".sdm_5tipekaryawan where id>=2 and id<=6";
$qtpKar=$owlPDO->query($stpKar) or die(print " Gagal: ".PDOException::getMessage());
$qtpKar->setFetchMode(PDO::FETCH_ASSOC);
while($rTpkar=$qtpKar->fetch())		
{
    $scek="select distinct a.karyawanid from ".$dbname.".datakaryawan a 
           inner join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid
           where tipekaryawan='".$rTpkar['id']."'";
	$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
	$rcek=owlBaris($qcek);
    if($rcek!=0){
        $optpKary.="<option value='".$rTpkar['id']."'>".$rTpkar['tipe']."</option>";    
    }
}


$arr="##periode##kdUnit##karyId##afdId##karyId##tgl1##tgl2##tPkary";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
    function getKary(idr){
                if(idr==1){
                    unitId=document.getElementById('kdUnit');
                    unitId=unitId.options[unitId.selectedIndex].value;
                }
                if(idr==2){
                    unitId=document.getElementById('afdId');
                    unitId=unitId.options[unitId.selectedIndex].value;
                }
                param='kdUnit='+unitId;
                if(idr==3){
                    unitId=document.getElementById('afdId');
                    unitId=unitId.options[unitId.selectedIndex].value;
                    if(unitId==''){
                        unitId2=document.getElementById('kdUnit');
                        unitId2=unitId2.options[unitId2.selectedIndex].value;
                        unitId=unitId2;
                    }
                    tpkary=document.getElementById('tPkary');
                    tpkary=tpkary.options[tpkary.selectedIndex].value;
                    param="";
                    param='kdUnit='+unitId+'&tPkary='+tpkary;

                }
                
                post_response_text('sdm_slave_2gajiperorang.php?proses=getKary', param, respon);
                function respon() {
                if (con.readyState == 4) {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert('ERROR TRANSACTION,\n' + con.responseText);
                        } else {
                            //=== Success Response
        					if(idr==1){
        						dtdipisah=con.responseText.split("####");
        						document.getElementById('afdId').innerHTML = dtdipisah[0];
        						document.getElementById('karyId').innerHTML = dtdipisah[1];
        					}else{
        						document.getElementById('karyId').innerHTML = con.responseText;
        					}
                        }
                    } else {
                        busy_off();
                        error_catch(con.status);
                    }
                }
            }
    }
    function getTgl(){
            prd=document.getElementById('periode');
            prd=prd.options[prd.selectedIndex].value;
            kdnit=document.getElementById('kdUnit');
            kdnit=kdnit.options[kdnit.selectedIndex].value;
            param='periode='+prd+'&kdUnit='+kdnit;
            post_response_text('sdm_slave_2gajiperorang.php?proses=getTglGaji', param, respon);
            function respon(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert('ERROR TRANSACTION,\n' + con.responseText);
                        } else {
                            //=== Success Response
                            dtdipisah=con.responseText.split("####");
                            document.getElementById('tgl1').value = dtdipisah[0];
                            document.getElementById('tgl2').value = dtdipisah[1];
                        }
                    } else {
                        busy_off();
                        error_catch(con.status);
                    }
                }
            }
    }
</script>
<?php    
if($_SESSION['language']=='EN'){
    OPEN_BOX('','<span class=judul>'.strtoupper('Daily Salary recap by employee').'</span>');
}else{
    OPEN_BOX('','<span class=judul>'.strtoupper('Rekap Gaji harian per karyawan').'</span>');
}
echo"<div>
<fieldset style=\"float: left;\">
<legend><b>Form</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr><td><label>".$_SESSION['lang']['unit']."</label></td><td><select id=\"kdUnit\" name=\"kdUnit\" style=\"width:150px\" onchange=getKary(1)>".$optOrg."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['afdeling']."</label></td><td><select id=\"afdId\" name=\"afdId\" style=\"width:150px\" onchange=getKary(2)>".$optAfd."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['tipekaryawan']."</label></td><td><select id=\"tPkary\" name=\"tPkary\" style=\"width:150px\"  onchange=getKary(3)>".$optpKary."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['namakaryawan']."</label></td><td><select id=\"karyId\" name=\"karyId\" style=\"width:150px\">".$optKary."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['periode']."</label></td><td><select id=\"periode\" name=\"periode\" style=\"width:150px\" onchange=getTgl()>".$optPeriode."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['tanggalmulai']."</label></td><td><input type=text class=myinputtext id=tgl1 onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:150px\" maxlength=10 disabled /></td></tr>
<tr><td><label>".$_SESSION['lang']['tanggalsampai']."</label></td><td><input type=text class=myinputtext id=tgl2 onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:150px\" maxlength=10 /></td></tr>
<tr height=\"20\"><td colspan=\"2\">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>
<button onclick=\"zPreview('sdm_slave_2gajiperorang','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>"
."<button onclick=\"zExcel(event,'sdm_slave_2gajiperorang.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>";
CLOSE_BOX();
echo close_body();
?>