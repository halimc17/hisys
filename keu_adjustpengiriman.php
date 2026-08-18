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
$sPeriode="select distinct substring(tanggal,1,7) as periode from ".$dbname.".pabrik_timbangan order by substring(tanggal,1,7) desc";
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

$optOrg="<select id=kdOrg name=kdOrg style=\"width:200px;\"  onchange='getNoKontrak()' ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optNik="<select id=komoditi name=updateby style=\"width:200px;\" onchange='getNoKontrak()' ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select distinct kodeorg from ".$dbname.".keu_tagihanht order by kodeorg asc ";   
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
    $optOrg.="<option value=".$rOrg['kodeorg'].">".$optNamaOrganisasi[$rOrg['kodeorg']]."</option>";
}
$optOrg.="</select>";
$sBrg="select distinct(a.kodebarang) as millcode, b.namabarang as namaorganisasi 
                                from ".$dbname.".pabrik_timbangan a
                                left join ".$dbname.".log_5masterbarang b
                                on a.kodebarang = b.kodebarang where a.kodebarang like '4%'";
$rBrgPabrik = fetchData($sBrg);
foreach($rBrgPabrik as $key=>$row) {
        $optNik.="<option value=".$row['millcode'].">".$row['namaorganisasi']."</option>";
}
$optNik.="</select>";

//$arr="##kdOrg##tgl1##tgl2##statTagihan";
$arr="##kdOrg##komoditi##kgBongkar##nodo##periode##persenPajak##statPPh##tglJurnal";

$arrOpt=array("0"=>"Belum Posting","1"=>"Sudah Posting");
$optpph=$optStatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optDtstat=array("0"=>$_SESSION['lang']['tidak'],"1"=>$_SESSION['lang']['ya']);
foreach ($optDtstat as $key => $value) {
    $optpph.="<option value='".$key."'>".$value."</option>";
}
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript>
notifpopilih="<?php echo $_SESSION['lang']['pilihdata']; ?>";
notiftagihtanggal="<?php echo $_SESSION['lang']['notiftagihtanggal']; ?>";
notifpostingpenagihan="<?php echo $_SESSION['lang']['notifpostingpenagihan']; ?>";
</script>
<script>
function getNoKontrak(){
    kom=document.getElementById('komoditi');
    kom=kom.options[kom.selectedIndex].value;
    ptId=document.getElementById('kdOrg');
    ptId=ptId.options[ptId.selectedIndex].value;
    prd=document.getElementById('periode');
    prd=prd.options[prd.selectedIndex].value;
    // nokontrak=document.getElementById('nokontrak');
    // nokontrak=nokontrak.options[nokontrak.selectedIndex].value;
    param='komoditi='+kom+'&ptId='+ptId+'&periode='+prd;
    tujuan='keu_slave_adjustpengiriman.php';
    post_response_text(tujuan+'?'+'proses=getNokontrak', param, respog);
    function respog(){
        if(con.readyState==4){
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    // if(nokontrak==''){
                    //     document.getElementById('nokontrak').innerHTML=con.responseText;
                    //     document.getElementById('nodo').innerHTML="<option value=''>"+notifpopilih+"</option>";    
                    // }else{
                        document.getElementById('nodo').innerHTML=con.responseText;
                    //}
                }
            }else{
                    busy_off();
                    error_catch(con.status);
            }
        }
    }    
}
function postingData(jrow){
    strUrl = '';
    tgl=document.getElementById('tglJurnal').value;
    nodo=document.getElementById('nodoData').innerHTML;
    prd=document.getElementById('periode');
    prd=prd.options[prd.selectedIndex].value;
    statPPh=document.getElementById('statPPh');
    statPPh=statPPh.options[statPPh.selectedIndex].value;
    param='nodo='+nodo+'&tglJurnal='+tgl+'&periode='+prd+'&statPPh='+statPPh;
    for(dataAw=0;dataAw<=jrow;dataAw++){
        if(strUrl != ''){
            strUrl +='&noakun[]='+trim(document.getElementById('noakun_'+dataAw).innerHTML)
                   +'&rpSelisih[]='+trim(document.getElementById('selisih_'+dataAw).innerHTML);
        }
        else{
            strUrl +='&noakun[]='+trim(document.getElementById('noakun_'+dataAw).innerHTML)
                   +'&rpSelisih[]='+trim(document.getElementById('selisih_'+dataAw).innerHTML);
        }
    }
    param+=strUrl;
    tujuan='keu_slave_adjustpengiriman.php';
    post_response_text(tujuan+'?'+'proses=addDetail', param, respog);
    function respog(){
        if(con.readyState==4){
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('printContainer').innerHTML=con.responseText;
                }
            }else{
                    busy_off();
                    error_catch(con.status);
            }
        }
    }
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('keu_adjustpengiriman')).'</span><br>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td><td><?php echo $optOrg?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><select id='periode' style="width:85px"  onchange='getNoKontrak()'><?php echo $optPeriode?> </select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['jurnal']?></label></td><td>:</td><td><input type=text class=myinputtext id="tglJurnal" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10" /></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['komoditi']?></label></td><td>:</td><td><?php echo $optNik?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['nodo']?></label></td><td>:</td><td><select id="nodo" name="nodo" style="width:200px"><?php echo $optStatus?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kgbongkar']?></label></td><td>:</td><td><input type="text" id="kgBongkar" style="width:200px" onkeypress="return angka_doang(event)" class="myinputtextnumber" /></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['persen']." ".$_SESSION['lang']['pajak']?></label></td><td>:</td><td><input type="text" id="persenPajak" style="width:200px" onkeypress="return angka_doang(event)" class="myinputtextnumber" /></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['pphditanggung']?></label></td><td>:</td><td><select id="statPPh" name="statPPh" style="width:200px"><?php echo $optpph?></select></td></tr>
<tr><td><td><td><button onclick="zPreview('keu_slave_adjustpengiriman','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button></td></tr>

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