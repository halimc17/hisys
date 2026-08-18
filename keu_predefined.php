<?//@Copy nangkoelframework
//-----------------ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();


?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/keu_predefined.js'></script>



<?
include('master_mainMenu.php');
$optpt=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","tipe='PT'",'',2);
$optstat= array('Domestic'=>'Domestic','Inhouse'=>'Inhouse');
OPEN_BOX('','<span class=judul>'.getMenu('keu_predefined').'<br></span>');
$arr="##pt##unit##tanggalmulai##tanggalselesai##status";	

echo "<fieldset style=float:left><legend><b>Form</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['pt']."</td>
        <td>:</td>
        <td>".makeElement('pt','select','',array('style'=>'width:100px','onchange'=>'getunit()'),$optpt)."</td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td>".makeElement('unit','select','',array('style'=>'width:100px'))."</td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['tanggalmulai']."</td>
        <td>:</td>
        <td>".makeElement('tanggalmulai','tanggal','',array('style'=>'width:100px'))."</td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['tanggalselesai']."</td>
        <td>:</td>
        <td>".makeElement('tanggalselesai','tanggal','',array('style'=>'width:100px'))."</td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['status']."</td>
        <td>:</td>
        <td>".makeElement('status','select','',array('style'=>'width:100px'),$optstat)."</td>
    </tr>
	<tr>
		<td colspan=3 align=right>
		<button onclick=zPreview('keu_slave_predefined','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

echo "
<fieldset ><legend><b>".$_SESSION['lang']['list']." Fined</b></legend>
<div id='listdata' style='overflow:auto;height:150px;max-width:790px'>
<script>loadpredefined()</script>
</div></fieldset>";
/*<button onclick=zExcel(event,'keu_slave_jurnalplasma.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>	
		*/
CLOSE_BOX();
OPEN_BOX();
echo "
<fieldset ><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1190px'>
</div></fieldset>";// ; 

CLOSE_BOX();
echo close_body();




?>