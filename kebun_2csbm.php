<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2csbm').'</span><br>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/kebun_2csbm.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTt="<option value=''>".$_SESSION['lang']['all']."</option>";

// $str="select * from ".$dbname.".organisasi where tipe='PT'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$s="";
// 	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

// $str="select * from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$s="";
// 	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

foreach(getOrgDetail(3) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optPT.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optPT.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optPT.="</optgroup>";
	}
}

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

$str="select * from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select distinct tahuntanam from ".$dbname.".setup_blok where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by tahuntanam asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optTt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrIP=array("I"=>"INTI","P"=>"PLASMA");
$optIP="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrIP as $res => $bar){
	$optIP.="<option value=".$res.">".$bar."</option>";
}
$arrprod=array("2"=>"Sebelum Sortasi","1"=>"Setelah Sortasi");
foreach($arrprod as $res => $bar){
	$optprod.="<option value=".$res.">".$bar."</option>";
}

$arr=array(
	"rp"   =>"Rupiah (000)",
	"rpha" =>"Rupiah (000) / Ha",
	"rppkk"=>"Rupiah (000) / Pokok",
	"rpprd"=>"Rupiah / Kg"
);
foreach($arr as $res => $bar){
	@$optjns.="<option value=".$res.">".$bar."</option>";
}



$arr1 = "##pt##kdorg##prd##divisi##tt##ip##prod";
echo"<fieldset style='float:left;'  id=formfilter>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','".$_SESSION['lang']['all']."')  style=\"width:164px;\">" .$optPT . "</select></td>
                
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=divisi onchange=getThnTnm(this,'tt','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optDiv . "</select></td>
                
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tt style=\"width:164px;\">" . $optTt . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['intiplasma'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=ip style=\"width:164px;\">" . $optIP . "</select></td>
                
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:164px;\">" . $optper . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['produksi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prod style=\"width:164px;\">" . $optprod . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=postPreview('" . $arr1 . "','printContainer'); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <!--<button onclick=postExcel('" . $arr1 . "','printContainer'); class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>-->
                    <button onclick=zExcel(event,'kebun_slave_2csbm.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
                
				
            </table>
	<center hidden><button style=display:none id=buttonshow onclick=showfilter('show'); class=mybutton name=preview>Show Form Filter</button></center>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id='printContainer'></div>";

echo"<div id='getdetail' style=display:none></div>";
CLOSE_BOX();
echo close_body();
?>