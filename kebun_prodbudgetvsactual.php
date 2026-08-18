<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_prodbudgetvsactual').'</span><br>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/kebun_prodbudgetvsactual.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select * from ".$dbname.".bgt_regional_assignment";
$res = fetchdata($str);
foreach($res as $bar){
	$myregional="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeunit']){
		$myregional=$bar['subregional'];
	}
	if(getNamaOrg($bar['kodeunit'],'tipe')=='KEBUN'){		
		$datareg[$bar['subregional']]=$bar['subregional'];
	}
}
foreach($datareg as $region){
	$s="";
	if($myregional==$region){
		$s="selected";
	}
    $optPT.="<option value=" . $region . " ".$s.">".$region."</option>";
}

$str="select * from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."'";
$res = fetchdata($str);
foreach($res as $bar){
	$s="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
		$s="selected";
	}
}

$str="select distinct substr(tanggal,1,7) as periode from ".$dbname.".pabrik_timbangan order by tanggal desc limit 25";
$res = fetchdata($str);
foreach($res as $bar){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}


$arr1 = "##regional##kdorg##prd##kapasitas##jam";

echo"<div id=tableheader>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['regional'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=regional onchange=\"getUnitRegional(this,'kdorg','divisi','".$_SESSION['lang']['all']."');getkapasitas(this.value)\"  style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:164px;\">" . $optper . "</select></td>
                </tr>
				<tr>
                    <td>Mill Kapasitas (TPH)</td>
                    <td>:</td>
                    <td><input type=text id=kapasitas style=\"width:155px;padding-right:5px\" class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" value='140' /></td>
                </tr>
				<tr>
                    <td>Jalan per hari (Jam)</td>
                    <td>:</td>
                    <td><input type=text id=jam style=\"width:155px;padding-right:5px\" class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" value='20' /></td>
                </tr>
				<tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=\"zPreview('kebun_slave_prodbudgetvsactual','" . $arr1 . "','printContainer');showheader();\" class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_prodbudgetvsactual.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</div>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
	
echo"<div id='printContainer' class='table-scroll' style='overflow:auto;height:73vh;'></div>";

CLOSE_BOX();
echo close_body();
?>