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
<script language=javascript src='js/keu_jurnalplasma.js'></script>



<?
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="SELECT distinct periode FROM ".$dbname.".setup_periodeakuntansi where 
		tutupbuku=0 and kodeorg='".$_SESSION['empl']['lokasitugas']."' order by periode desc limit 10";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optper.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}
	
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
		(namaorganisasi like '%plasma%' or namaorganisasi like '%PLASMA%') and tipe='AFDELING' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
        $optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

?>



<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_jurnalplasma').'<br></span>');
$arr="##unit##per##noakun";	

echo "<fieldset style=float:left><legend><b>Form</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unit style='width:140px;' onchange='getakun()'>".$optorg."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td><select id=per style='width:140px;'>".$optper."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['akun']."</td>
        <td>:</td>
        <td><select id=noakun  style='width:140px;'>".$optakun."</select></td>
    </tr>
	<tr>
		<td colspan=3 align=right>
		<button onclick=zPreview('keu_slave_jurnalplasma','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
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