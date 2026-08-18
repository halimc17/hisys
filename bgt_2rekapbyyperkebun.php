<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('bgt_2rekapbyyperkebun').'</span><br>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/bgt_2rekapbyyperkebun.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTt="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah=''";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select * from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."' and inti=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
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

$str="select distinct(tahunbudget) as periode from ".$dbname.".bgt_blok order by tahunbudget desc limit 25";
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

$arrtipe=array("group"=>"Per Job Group","code"=>"Per Job Code","kegiatan"=>"Per Kode Kegiatan");
foreach($arrtipe as $res => $bar){
	@$opttipe.="<option value=".$res.">".$bar."</option>";
}

$arr1 = "##pt##kdorg##prd##divisi##tt##ip##tipe";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','".$_SESSION['lang']['all']."','1')  style=\"width:164px;\">" .$optPT . "</select></td>
               
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr hidden>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=divisi onchange=getThnTnm(this,'tt','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optDiv . "</select></td>
               
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tt style=\"width:164px;\">" . $optTt . "</select></td>
                </tr>
				<tr>
                    <td hidden>" . $_SESSION['lang']['intiplasma'] . "</td>
                    <td hidden>:</td>
                    <td hidden><select class=select2 id=ip style=\"width:164px;\">" . $optIP . "</select></td>
                
                    <td>" . $_SESSION['lang']['tahun'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:164px;\">" . $optper . "</select></td>
                
                    <td>" . $_SESSION['lang']['tipe'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tipe style=\"width:164px;\">" . $opttipe . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('bgt_slave_2rekapbyyperkebun','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'bgt_slave_2rekapbyyperkebun.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
//echo"<div class='table-scroll' id='printContainer'></div>";
echo"<div style=clear:both></div>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			<img title='Full Screen' class='zImgBtn' src='images/full-screen.png'>
		</a>
	</div>
	<div style=clear:both></div>
	<div id='printContainer'></div>
</div>";

// echo"<div id='printContainer'></div>";

CLOSE_BOX();
echo close_body();
?>