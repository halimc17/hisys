<?//@Copy nangkoelframework //-----------------ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/sdm_3premi.js'></script>

<?
$frm[0]='';

$optper=$optorg=$optkomponen="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="SELECT distinct periode FROM ".$dbname.".sdm_5periodegaji order by periode desc limit 10";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optper.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}			

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}


$str="select * from ".$dbname.".sdm_ho_component where plus=1 and type='basic' and id='62'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optkomponen.="<option value=".$bar['id'].">".$bar['name']."</option>";
}

include('master_mainMenu.php');
$frm[0]='';
OPEN_BOX('','<span class=judul>'.getMenu('sdm_3premi').'<br></span>');
if($_SESSION['language']=='EN'){
	$hfrm[0]='Absence Insentif';
}else{
	$hfrm[0]='Premi Kehadiran';
}
$arr="##per##unit##kom";
$frm[0].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=per style='width:125px;'>".$optper."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unitkerja']."</td>
		<td>:</td>
		<td><select id=unit  style='width:125px;'>".$optorg."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['namakomponen']."</td>
		<td>:</td>
		<td><select id=kom  style='width:125px;'>".$optkomponen."</select></td>
	</tr>
	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('sdm_slave_3premi','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>		
		<button onclick=batalpremitetap() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>
<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";

drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();


?>
