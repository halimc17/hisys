<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');

echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('', '<span class=judul>'.getMenu('kebun_2daftarpremi').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>


<?

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$optPT.="<option value=''>".$_SESSION['lang']['all']."</option>";
	$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'",'2','0',true);
	
	$optOrg = "<option value=''>".$_SESSION['lang']['all']."</option>";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$optPT.="<option value='xxx'>".$_SESSION['lang']['pilihdata']."</option>";
	$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT' and alokasi ='".$_SESSION['empl']['kodeorganisasi']."'",'2','0',true);
	
	$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
}else{
	$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT' and alokasi ='".$_SESSION['empl']['kodeorganisasi']."'",'2','0',true);

	$nmOrg= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	$optOrg = "<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']." - ".$nmOrg[$_SESSION['empl']['lokasitugas']]."</option>";

}


$optDiv='';
$arr = "##pt##kdorg";
echo"<fieldset style='float:left'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt onchange=getUnitLapPremi() style=\"width:174px;\">" . $optPT . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:174px;\">" . $optOrg . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2daftarpremi','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2daftarpremi.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'; >
</div></fieldset>";
CLOSE_BOX();
echo close_body();
?>