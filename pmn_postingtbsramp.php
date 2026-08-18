<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<?php
$optNamaOrganisasi=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optPeriode='';
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$sPeriode="select distinct substring(tanggal,1,7) as periode from ".$dbname.".keu_tagihanht order by substring(tanggal,1,7) desc";
$res=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$res->fetch()){
    if(substr($rPeriode['periode'],5,2)=='12'){
//        $optPeriode.="<option value=".substr($rPeriode['periode'],0,4).">".substr($rPeriode['periode'],0,4)."</option>";
        $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
    }
    else{
        $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
    }
}

$optOrg="<select id=kdOrg name=kdOrg style=\"width:200px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optSupp="<select id=suppId name=suppId style=\"width:200px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' order by kodeorganisasi asc ";	
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
 	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$optOrg.="</select>";

$sSupp="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP' and status=1)";
$rSupp=fetchData($sSupp);
foreach($rSupp as $row=>$lstData){
    $optSupp.="<option value=".$lstData['supplierid'].">".$lstData['supplierid']."-".$lstData['namasupplier']."</option>";   
}
$optSupp.="</select>";

$arr="##kdOrg##suppId##statTagihan##tglData##tglData2";

$arrOpt=array("0"=>"Belum Posting","1"=>"Sudah Posting","2"=>"Normalisasi Data");
$optStatus="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOpt as $listBrs =>$dtStat)
{
    $optStatus.="<option value='".$listBrs."'>".$dtStat."</option>";
}
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript>
notifpopilih="<?php echo $_SESSION['lang']['notifpopilih']; ?>";
notiftagihtanggal="<?php echo $_SESSION['lang']['notiftagihtanggal']; ?>";
notifpostingpenagihan="<?php echo $_SESSION['lang']['notifpostingpenagihan']; ?>";
</script>
<script>
function Clear1()
{
    document.getElementById('kdOrg').value='';
    document.getElementById('periode').value='';
    document.getElementById('periode2').value='';
    document.getElementById('statTagihan').value='';
    document.getElementById('printContainer').innerHTML='';
}
function detailPDF2(numRow,ev) {
    // Prep Param
    var notran = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    notran=notran.split("####");
    var notransaksi=notran[0];
    var noakun =notran[2];
    var tipetransaksi =notran[1];
    var kodeorg  =notran[3];
    param = "proses=pdf2&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
function postingData(mulaike,statTagihan,row){    
    notiket=document.getElementById('notiket_'+mulaike).innerHTML;
    pt=document.getElementById('ptid_'+mulaike).innerHTML;
    kodepabrik=document.getElementById('pabrik_'+mulaike).innerHTML;
    tanggal=document.getElementById('tgl_'+mulaike).innerHTML;
    koderamp=document.getElementById('rampId_'+mulaike).value;
    supplierid=document.getElementById('supplierid_'+mulaike).value;
    beratBersih=document.getElementById('beratBersih_'+mulaike).value;
    hargasatuan=document.getElementById('hargasatuan_'+mulaike).value;
    document.getElementById('row'+mulaike).style.background="orange";
    
    if(mulaike==1){
        document.getElementById('tombolPosting').disabled=true;
        posting(notiket,pt,kodepabrik,koderamp,supplierid,tanggal,beratBersih,hargasatuan,mulaike,row,statTagihan);
    }else{
        if(mulaike<=row){
            //alert('lanjut ke dua');
            dtsblmnya=mulaike-1;
            document.getElementById('row'+dtsblmnya).style.background="green";
            document.getElementById('row'+dtsblmnya).style.display="none";
            posting(notiket,pt,kodepabrik,koderamp,supplierid,tanggal,beratBersih,hargasatuan,mulaike,row,statTagihan);
        }else{
            document.getElementById('row'+dtsblmnya).style.background="green";
            document.getElementById('row'+dtsblmnya).style.display="none";
            alert("Done");
            document.getElementById('printContainer').innerHTML="";
        }
    }
    
}

function posting(notiket, pt, kodepabrik, koderamp, supplier, tanggal,beratBersih,hargasatuan,mulaike,row,statTagihan){
    param='notiket='+notiket+'&pt='+pt+'&kodepabrik='+kodepabrik+'&koderamp='+koderamp+'&tanggal='+tanggal+'&supplier='+supplier;
    param+='&beratBersih='+beratBersih+'&hargasatuan='+hargasatuan+'&statTagihan='+statTagihan;
    tujuan='pmn_slave_postingramp.php';  
    post_response_text(tujuan+'?proses=posting', param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) 
                {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    if(mulaike!=row){
                        mulaike+=1;
                        postingData(mulaike,statTagihan,row);
                    }
                    
                }
            }
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


 
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('pmn_postingtbsramp')).'</span><br>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><?php echo $optOrg?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input type="text" class="myinputtext" id="tglData" name="tglData" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:85px;" /> s.d <input type="text" class="myinputtext" id="tglData2" name="tglData2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:85px;" /></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['namasupplier']?></label></td><td>:</td><td><? echo $optSupp ?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['status']?></label></td><td>:</td><td><select id="statTagihan" name="statTagihan" style="width:200px"><?php echo $optStatus?></select></td></tr>
<tr><td><td><td><button onclick="zPreview('pmn_slave_postingramp','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event,'pmn_slave_postingramp.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>

<div style="margin-bottom: 30px;">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'>

</div></fieldset>

<?php

CLOSE_BOX();
echo close_body();
?>