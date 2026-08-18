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
$sPeriode="select distinct substring(tanggal,1,7) as periode from ".$dbname.".keu_kasbankht order by substring(tanggal,1,7) desc";
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
$optNik="<select id=updateby name=updateby style=\"width:200px;\" ><option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select distinct kodeorg from ".$dbname.".keu_5caco order by kodeorg asc ";	
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
 	$optOrg.="<option value=".$rOrg['kodeorg'].">".$rOrg['kodeorg']."-".$optNamaOrganisasi[$rOrg['kodeorg']]."</option>";
}
$optOrg.="</select>";
$sOrg="select distinct userid from ".$dbname.".keu_kasbankht order by kodeorg asc ";  
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
    $whrby="karyawanid='".$rOrg['userid']."'";
    $optNm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrby);
    $optNik.="<option value=".$rOrg['userid'].">".$optNm[$rOrg['userid']]."</option>";
}
$optNik.="</select>";
$optVhc=$optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sAkun="select noakun,namaakun from ".$dbname.".keu_5akun where 
		char_length(noakun)=7 and (left(noakun,3)='114' or left(noakun,1)>6 or left(noakun,1)='4' or left(noakun,5)='12813') order by noakun asc";
$rAkun=fetchdata($sAkun);
foreach($rAkun as $row=>$lstAkun){
    if(($lstAkun['noakun']=='4110299')||($lstAkun['noakun']=='4110199')){
        continue;
    }
    $optAkun.="<option value='".$lstAkun['noakun']."'>".$lstAkun['noakun']."-".$lstAkun['namaakun']."</option>";
}
//$arr="##kdOrg##tgl1##tgl2##statTagihan";
$arr="##kdOrg##updateby##periode2##periode##notrans##noakundebet##kdvhc_noproj";

$arrOpt=array("0"=>"Belum Posting","1"=>"Sudah Posting");
$optStatus="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOpt as $listBrs =>$dtStat)
{
    $optStatus.="<option value='".$listBrs."'>".$dtStat."</option>";
}
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/keu_tagihan.js'></script>
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
function detailPDF(numRow,ev) {
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

function addDataKeMemorial(maxRow){
        var cekData=0;
        var dtTrans='';
        var tgl;
        var temptgl='';
        
        for(i=0;i<maxRow;i++){
            ckdt=document.getElementById('trans_'+i);
            if(ckdt.checked==true){
                cekData+=1;
                tgl=document.getElementById('tanggal_'+i).getAttribute('value');
                if(temptgl!=tgl){
                    var totRup=0;                
                }else{
                    totRup+=parseFloat(document.getElementById('rup_'+i).getAttribute('value'));
                }
                dtTrans+='&notransaksi[]='+document.getElementById('notrans_'+i).value+'&noakun[]='+document.getElementById('noakun_'+i).getAttribute('value')+'&karyid[]='+document.getElementById('nikdt_'+i).getAttribute('value')
                       +'&supplierid[]='+document.getElementById('supplierid_'+i).getAttribute('value')
                       +'&ket2[]='+document.getElementById('ket2_'+i).getAttribute('value')+'&unitdt='+document.getElementById('unitdt_'+i).getAttribute('value')
                       +'&tanggal[]='+document.getElementById('tanggal_'+i).getAttribute('value')
                       +'&rupdt[]='+document.getElementById('rup_'+i).getAttribute('value')+'&Totrupdt['+tgl+']='+totRup;
            }
        }
        if(cekData==0){
            alert("Pilih salah satu data");
            return;
        }
        noakn=document.getElementById('noakundebet');
        noakn=noakn.options[noakn.selectedIndex].value;
        kdvhc_noproj=document.getElementById('kdvhc_noproj');
        kdvhc_noproj=kdvhc_noproj.options[kdvhc_noproj.selectedIndex].value;
        param='proses=addDetail'+'&noakundebet='+noakn+'&kdvhc_noproj='+kdvhc_noproj;
        param+=dtTrans;
        tujuan = 'keu_slave_jurnalrk.php';
        post_response_text(tujuan, param, respog);          
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                    }
                    else {
                        zPreview('keu_slave_jurnalrk','##kdOrg##updateby##periode2##periode##notrans##noakundebet','printContainer');
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        } 
}
function getKdVhc(){
        noakundebet=document.getElementById('noakundebet');
        noakundebet=noakundebet.options[noakundebet.selectedIndex].value;
        kdOrg=document.getElementById('kdOrg');
        kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
        param='noakundebet='+noakundebet+'&proses=getKdVhc'+'&unit='+kdOrg;
        tujuan = 'keu_slave_jurnalrk.php';
        post_response_text(tujuan, param, respog);          
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                    }
                    else {
                        document.getElementById('kdvhc_noproj').innerHTML=con.responseText;
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
OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('keu_jurnalrk')).'</span><br>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><?php echo $optOrg?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['namakaryawan']?></label></td><td>:</td><td><?php echo $optNik?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><select id='periode' style="width:85px"><?php echo $optPeriode?> </select> <?php echo $_SESSION['lang']['sd']?> 
<select id='periode2' style="width:86px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['notransaksi']?></label></td><td>:</td><td><input type=text id=notrans class=myinputtext onkeypress='return tanpa_kutip(event)' style="width:200px" placeholder="No. Transaksi boleh kosong" /></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['noakundebet']?></label></td><td>:</td><td><select id=noakundebet style='width:200px' onchange="getKdVhc()"><?php echo $optAkun?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kodevhc'].'/'.$_SESSION['lang']['project']?></label></td><td>:</td><td><select id=kdvhc_noproj style='width:200px'><?php echo $optVhc?></select></td></tr>
<tr><td><td><td><button onclick="zPreview('keu_slave_jurnalrk','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event,'keu_slave_jurnalrk.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>

<div style="margin-bottom: 30px;">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'>

</div>

</fieldset>

<?php

CLOSE_BOX();
echo close_body();
?>