<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/keu_2laporankeuangankonsolmpi.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul><b>'.getMenu('keu_2laporankeuangankonsolmpi').'</b></span><br>');
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";  
$res=fetchdata($str);
foreach($res as $bar){
    $optper.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}	

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
$res=fetchdata($str);
foreach($res as $bar){
	$optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


echo"<table border=0 id=tableheader><td style=vertical-align:top>";
echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select class='select2' id=kodept onchange=getunit(); style='width:200px;'>".$optpt."</select></td>
			</tr>	
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td><select class='select2' id=kodeunit style='width:200px;'>".$optunit."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select class='select2' id=periode style='width:200px;'>".$optper."</select></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=3><button class=mybutton onclick=getlaporan('html')>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=getlaporan('excel')>".$_SESSION['lang']['excel']."</button>
				<button class=mybutton onclick=getlaporan('pdf')>".$_SESSION['lang']['pdf']."</button></td>
			</tr>
		</table>
    </fieldset>
</table>";
CLOSE_BOX();
OPEN_BOX('','');

echo"<div id=tombolexport style=display:none>
	<table>
		<tr>
			<td>
				<button onclick='showheader()' class=\"mybutton\" id=showhead>Show</button>
			</td>
		</tr>
	</table>
	</div>";
echo"<div id=container  class='table-scroll'  style='height:75vh;overflow:auto;'></div>";
CLOSE_BOX();
close_body();
?>