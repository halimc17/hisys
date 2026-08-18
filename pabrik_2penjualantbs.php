<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');

echo open_body(); 
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['laporan']." ".$_SESSION['lang']['penjualan']." ".$_SESSION['lang']['tbs'].'</span><br>');
 // $str=$owlPDO->query("select distinct induk from ".$dbname.".organisasi where kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_periodeakuntansi where char_length(kodeorg)=4)   order by namaorganisasi");
$str=$owlPDO->query("select distinct kodeorg from ".$dbname.".pabrik_timbangan where millcode='EXTM'");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $optpt="";
  
    while($bar=$str->fetch()){
    	$optNm=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$bar->kodeorg."'");
    	$optNmKdPT=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$optNm[$bar->kodeorg]."'");
        $optpt.="<option value='".$optNm[$bar->kodeorg]."'>".$optNm[$bar->kodeorg]." - ".$optNmKdPT[$optNm[$bar->kodeorg]]."</option>";
    }



$arr = "##pt##tanggal1##tanggal2";
	echo"<fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
	<table border=0><tr>
	<td>".$_SESSION['lang']['pt']."</td>
	<td>:</td>
	<td><select id=pt style='width:175px' onchange='changediv(this)'>".$optpt."</select></td>
	<tr>
	<td>".$_SESSION['lang']['periode']."</td>
	<td>:</td>
	<td><input type=text class=myinputtext id=tanggal1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 /> S/D
	<input type=text class=myinputtext id=tanggal2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 /></td>
	</tr>
	<td colspan=2></td>
    <td colspan=4>
    <button onclick=zPreview('pabrik_slave_2penjualantbs','" . $arr . "','container') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
    <button onclick=zExcel(event,'pabrik_slave_2penjualantbs.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
	
	</td></tr>
	<tr>
	</tr></table>
	</fieldset>";
	CLOSE_BOX();
	OPEN_BOX('','');
	echo"<fieldset><legend>Result :</legend>
<div id=container>
</div>
	</fieldset>";
	CLOSE_BOX();
	close_body();
	?>