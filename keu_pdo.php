<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/keu_pdo.js?v=1.6'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<!--deklarasi untuk option-->
<?php
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pdo']).'</span>');
$optunit=$optdivisi=$optthnsch=$optper=$optkeg=$optsat=$opttk=$optblok="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    //$optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}
for($x=(-2);$x<12;$x++){
    $dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
    $optper.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}
// $optper.="<option value='2018-01'>2018-01</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where id in ('4','6')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())   
{
    $opttk.="<option value=" . $bar['id'] . ">" . $bar['tipe'] . "</option>";
}
$str="select * from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'"
        . " and length(kodeorganisasi)<=6   order by kodeorganisasi asc ";//and tipe in ('AFDELING','KEBUN')
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optdivisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}
$str="select * from ".$dbname.".setup_kegiatan order by kodekegiatan asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())   
{
    $optkeg.="<option value=" . $bar['kodekegiatan'] . ">" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
}
$str="select * from ".$dbname.".setup_satuan  order by satuan asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())   
{
    $optsat.="<option value='".$bar['satuan']."'>".$bar['satuan']."</option>";
}  
$str="select distinct(substr(periode,1,4)) as tahun from ".$dbname.".keu_pdoht where kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())   
{
    $optthnsch.="<option value='".$bar['tahun']."'>".$bar['tahun']."</option>";
}
$optunitpjd='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL' or tipe='HOLDING')  and induk!=''";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);        
    $optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
    while($bar= $res->fetch())
    {
        $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
        $optunitpjd.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
    }
}else{
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'
             order by kodeorganisasi asc ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
        $optunitpjd.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
    }
}



$optrek=$optkas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select noakun,namaakun from ".$dbname.".keu_5akun where noakun in ('1112101','1112102','1110101')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optkas.="<option value='".$bar['noakun']."'>".$bar['namaakun']."</option>";
}

$str = "select * from ".$dbname.".keu_5akunbank";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $wheredz =" kodebank='".$bar['namabank']."'";
    $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
    $optrek.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
}

$optbagian="<option value='I'>I</option>";
$optbagian.="<option value='II'>II</option>";
?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
     <td align=center style='width:70px;cursor:pointer;' onclick=newdata()>
       <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
     <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
       <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
     <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
    echo"<table>";
    echo"
    <tr>
            <td>".$_SESSION['lang']['tahun']."</td>
            <td>:</td>
            <td><select id=thnsch style=\"width:85px;\">'".$optthnsch."'</select></td>
    </tr>
            <td colspan=3><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
        </tr>
    ";
        echo "</table>";
echo"</fieldset></td>";
echo"
     </tr>
     </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>
<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php 
echo"
<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
    echo "
    <fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['unit']."</td>
                    <td  align=center>".$_SESSION['lang']['periode']."</td>
                    <td  align=center>PDO</td>
                    <td  align=center>".$_SESSION['lang']['action']."</td>    
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
    </fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data
?>
<!--UNTUK BUAT FORM INPUT HEADER-->
<?php
echo "<div id=header style=display:none>";//buka diff
OPEN_BOX();// 
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";    
@$arrtipe=getOrgDetail(9);
foreach($arrtipe as $kei=>$fal){
    $scek="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$kei."' limit 1";
    $rcek=fetchData($scek);
    if(count($rcek)!=0){
        $optorg.="<option value='".$kei."'>".$fal."</option>";    
    }
}
echo "
<fieldset>
<legend>Header</legend>
<table cellspacing=1 border=0>
    <tr>
        <td>No. PDO</td>
        <td>:</td>
        <td><input type=text id=nopdo disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr> 
    <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit  style=\"width:150px;\">".$optorg."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['bulan']."-".$_SESSION['lang']['tahun']."</td>
        <td>:</td>
        <td><select id=per style=\"width:150px;\">'".$optper."'</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td><select id=bag style=\"width:150px;\">'".$optbagian."'</select></td>
    </tr>
    <tr><td colspan=2></td>
        <td colspan=20>
            <button id=savehead class=mybutton onclick=savehead()>".$_SESSION['lang']['save']."</button>
            <button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
        </td>
    </tr>
</table>
</fieldset>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>
<?php
echo "<div id=detail style=display:none>";//buka diff
OPEN_BOX();

## Begin Initial Form ##
$frm[0]='';
$frm[1]='';
$frm[2]='';
$frm[3]='';
$frm[4]='';
$frm[5]='';
$frm[6]='';
$frm[7]='';
$frm[8]='';
$frm[9]='';
$frm[10]='';
## End Initial Form ##

########################
## Begin Tab 0 (rekap)##
########################
$frm[0].="
<button class=mybutton onclick=htmlrekap('html')>".$_SESSION['lang']['preview']."</button>
<button class=mybutton onclick=excelrekap('excel',event)>".$_SESSION['lang']['excel']."</button>
<br><br>
<fieldset><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='listrekap'></div>
</fieldset>";

##################################
## Begin Tab 1 (incoming founds)##
##################################
$frm[1].="<div id='detailincome'></div>
<div id='listincome'></div>";

######################
## Begin Tab 2 (BBM)##
######################
$frm[2].="<fieldset><legend><b>Form ".$_SESSION['lang']['bbm']."</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td><input type=text id=notranbbm disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr>  
    <tr>
        <td>".$_SESSION['lang']['noakun']."</td>
        <td>:</td>
        <td><select id=noakunbbm onchange='getrekeningbbm()' style=\"width:150px;\">".$optkas."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['rekening']."</td>
        <td>:</td>
        <td><select id=rekeningbankbbm style=\"width:150px;\">".$optrek."</select></td>
    </tr>   
    </tr>
        <td colspan=2></td>
        <td colspan=100>
        <button id=prevbbm class=mybutton onclick=prevbbm()>".$_SESSION['lang']['preview']."</button>
        <button onclick=batalbbm() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
        </td>
    </tr>
</table>
</fieldset>
<div id='detailbbm'></div>
<div id='listbbm'></div>";

###################################
## Begin Tab 3 (Perjalanan Dinas)##
###################################
$frm[3].="<fieldset><legend><b>Form Perjalanan Dinas</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td><input type=text id=notranpjd disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr>   
    <tr>
        <td hidden>".$_SESSION['lang']['unit']."</td>
        <td hidden>:</td>
        <td hidden>
            <select id=unitpjd style=\"width:155px;\">".$optunitpjd."</select>
        </td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['noakun']."</td>
        <td>:</td>
        <td><select id=noakunpjd onchange='getrekeningpjd()' style=\"width:150px;\">".$optkas."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['rekening']."</td>
        <td>:</td>
        <td><select id=rekeningbankpjd style=\"width:150px;\">".$optrek."</select></td>
    </tr>  
    <tr>
        <td>".$_SESSION['lang']['total']."</td>
        <td>:</td>
        <td>
            <input type=text id=totalpjd onkeypress='return angka_doang(event)' class=myinputtextnumber  style=width:100px value='0'>
        </td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['keterangan']."</td>
        <td>:</td>
        <td>
            <input type=text id=ketpjd class=myinputtext style=width:200px value=''>
        </td>
    </tr>
    </tr>
        <td colspan=2></td>
        <td colspan=100>
            <input type=hidden id=methodpjd value='insertpjd'>
        <button id=prevbbm class=mybutton onclick=simpanpjd()>".$_SESSION['lang']['save']."</button>
        <button onclick=batalpjd() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
        </td>
    </tr>
</table>
</fieldset>
<div id='detailpjd'></div>
<div id='listpjd'></div>";

######################
## Begin Tab 4 (kas)##
######################
// $notrankas=$explnopdo[0].'/'.$explnopdo[2].'/KAS/'.$explnopdo[3].'/001';
        $opt=$optkas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select noakun,namaakun from ".$dbname.".keu_5akun where noakun in ('1112101','1112102','1110101')";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $optkas.="<option value='".$bar['noakun']."'>".$bar['namaakun']."</option>";
        }

$opt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$frm[4].="<fieldset><legend><b>Form Input</b></legend > 
    ".$_SESSION['lang']['notransaksi']." : <input type=text id=notrankas disabled value='' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext>
            ".$_SESSION['lang']['noakun']." : <select onchange='datajumlah()' id=noakunkas style=\"width:150px;\">".$optkas."</select>
            ".$_SESSION['lang']['rekening']." : <select id=rekeningbank style=\"width:150px;\">".$opt."</select>
            <button id='prcrkas' class='mybutton' onclick='detailkas()'' >Proses</button><hr>
            </fieldset>
               <div id='detailkas'></div>
    <div id='listkas'></div>";

#######################
## Begin Tab 5 (upah)##
#######################
$frm[5].="<fieldset><legend><b>Form ".$_SESSION['lang']['upah']."</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td><input type=text id=noupah disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
        <td>".$_SESSION['lang']['tipekaryawan']."</td>
        <td>:</td>
        <td><select id=tkupah style='width:150px;'>".$opttk."</select></td>
    </tr> 
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=divisiupah style='width:150px;'>".$optdivisi."</select></td>
        <td>".$_SESSION['lang']['rekening']."</td>
        <td>:</td>
        <td><select id=rekeningbankupah style=\"width:150px;\">".$optrek."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['noakun']."</td>
        <td>:</td>
        <td><select id=noakunupah onchange='getrekeningupah()' style=\"width:150px;\">".$optkas."</select></td>
    </tr>   
        <td colspan=2></td>
        <td colspan=100>
        <button id=prevupah class=mybutton onclick=prevupah()>".$_SESSION['lang']['save']."</button>
        <button onclick=batalupah() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
        </td>
    </tr>
</table>
</fieldset>";
$frm[5].="
<div id='detailupah'>
</div>";
$frm[5].="
<div id='listupah'>
</div>";

#########################
## Begin Tab 6 (hutang)##
#########################
    $frm[9].="<fieldset><legend><b>Form Hutang</b></legend>
    <table>
        <tr>
            <td>".$_SESSION['lang']['notransaksi']."</td>
            <td>:</td>
            <td><input type=text id=notranhutang disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['noakun']."</td>
            <td>:</td>
            <td><select id=noakunkashutang onchange='getrekeninghutang()' style=\"width:150px;\">".$optkas."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['rekening']."</td>
            <td>:</td>
            <td><select id=rekeningbankhutang style=\"width:150px;\">".$optrek."</select></td>
        </tr>";     
    if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
        $frm[9].="<tr id=hutangTombol style='display:block' >
                    <td colspan=2></td>
                    <td colspan=100>
                    <button id=prevupah class=mybutton  onclick=prevhutang()>".$_SESSION['lang']['preview']."</button>
                    <button onclick=batalhutang()  class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
                    </td>
                </tr>";
    }
    $frm[9].="
    </table>
    </fieldset>";
    $frm[9].="<div id='detailhutang'></div>
    <div id='listhutang'></div>";

    #######################
    ## Begin Tab 7 (bapp)##
    #######################
    $frm[10].="<fieldset><legend><b>Form BAPP</b></legend>
    <table>
        <tr>
            <td>".$_SESSION['lang']['notransaksi']."</td>
            <td>:</td>
            <td><input type=text id=notranbapp disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
            <td><b>".@$info."</b></td>
        </tr>
        <tr>
            <td hidden>".$_SESSION['lang']['divisi']."</td>
            <td hidden>:</td>
            <td hidden><select id=divisibapp onchange=getnobapp() style='width:150px;'>".$optunit."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['noakun']."</td>
            <td>:</td>
            <td><select id=noakunkasbapp onchange='getrekeningbapp()' style=\"width:150px;\">".$optkas."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['rekening']."</td>
            <td>:</td>
            <td><select id=rekeningbankbapp style=\"width:150px;\">".$optrek."</select></td>
        </tr>";
    if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
        $frm[10].="<tr id=bappTombol>
            <td colspan=2></td>
            <td colspan=100>
            <button id=prevbapp class=mybutton onclick=detailbapp()>".$_SESSION['lang']['preview']."</button>
            <button onclick=batalbapp() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
            </td>
        </tr>";
    }
    $frm[10].="</table>
    </fieldset>";
    $frm[10].="
    <div id='detailbapp'></div>
    <div id='listbapp'></div>";
###################################
## Begin Tab 8 (Ijin Operasional)##
###################################
$frm[6].="<fieldset><legend><b>Form Ijin Operasional</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td><input type=text id=notranio disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr> 
    <tr>
        <td>".$_SESSION['lang']['noakun']."</td>
        <td>:</td>
        <td><select id=noakunio onchange='getrekeningio()' style=\"width:150px;\">".$optkas."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['rekening']."</td>
        <td>:</td>
        <td><select id=rekeningbankio style=\"width:150px;\">".$optrek."</select></td>
    </tr>   
    </tr>
        <td colspan=2></td>
        <td colspan=100>
        <button id=prevbbm class=mybutton onclick=previo()>".$_SESSION['lang']['preview']."</button>
        <button onclick=batalio() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
        </td>
    </tr>
</table>
</fieldset>
<div id='detailio'></div>
<div id='listio'></div>";

######################
## Begin Tab 9 (pad)##
######################
$frm[7].="<div id='detailpad'></div>
<div id='listpad'></div>";

###########################
## Begin Tab 10 (lainnya)##
###########################
$frm[8].="<div id='detaillainnya'></div>
<div id='listlainnya'></div>";


### HEADER TAB ###
$hfrm[0]=strtoupper($_SESSION['lang']['rekap']);
$hfrm[1]=strtoupper($_SESSION['lang']['penerimaandana']);
$hfrm[2]=strtoupper($_SESSION['lang']['bbm']);
$hfrm[3]=strtoupper('Perjalanan Dinas');
$hfrm[4]=strtoupper($_SESSION['lang']['kas']);
$hfrm[5]=strtoupper($_SESSION['lang']['upah']);
$hfrm[6]=strtoupper('Izin Operasional');
$hfrm[7]='PAD';
$hfrm[8]=strtoupper('Lainnya');
$hfrm[9]=strtoupper($_SESSION['lang']['hutang']);
$hfrm[10]='BAPP';

### HEADER TAB ###

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,100,'auto');   

CLOSE_BOX();
echo"</div>";
?>
<?php
echo close_body();          
?>