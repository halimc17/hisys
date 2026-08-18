<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script languange=javascript1.2 src='js/sdm_2salarylist.js?ver=0.1'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?
// $optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

// $whereunit='';
// if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
//     $whereunit=" and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
// }
// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$whereunit." and tipe!='HOLDING' and 
//         length(kodeorganisasi)=4 order by namaorganisasi asc ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
//     $optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// }

if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optPT="";
}else{	
	$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
	$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";
}
$str="select * from ".$dbname.".organisasi where tipe='PT'  and kodesejarah=''";
// $str="select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah=''";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $s="";
    if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
        $s="selected";
    }
    $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select * from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."'";
// $str="select * from ".$dbname.".organisasi where tipe='KEBUN'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $s="";
    if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
        $s="selected";
    }
    $optunit.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$optPer="";
$str="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optPer.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}

$optTipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
#tipekaryawan
$sTipe="select distinct tipekaryawan from  ".$dbname.".sdm_gaji_vw where left(periodegaji,4)>='2021' order by tipekaryawan asc";
$rTipe=fetchData($sTipe);
if(count($rTipe)!=0){
    foreach($rTipe as $brs=>$val){
        if($val['tipekaryawan']!=''){
            $optNmTipe=makeOption($dbname,"sdm_5tipekaryawan","id,tipe");
            $optTipe.="<option value='".$val['tipekaryawan']."'>".$optNmTipe[$val['tipekaryawan']]."</option>";
        }
    }
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_2salarylist').'</span>');

// onchange=fillblank()
echo"<br>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select id=pt class=select2 style=\"width:168px;\" onchange=getunit()>".$optPT."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit class=select2 style=\"width:168px;\" >".$optunit."</select></td>
                </tr>
                
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per class=select2 style=\"width:168px;\" >".$optPer."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select id=tpKary class=select2 style=\"width:168px;\">".$optTipe."</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=\"preview('html',event)\" class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=\"preview('excel',event)\" class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"
<div id='printContainer' class='table-scroll'></div>
";

CLOSE_BOX();
echo close_body();


?>