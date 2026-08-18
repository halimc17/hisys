<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/keu_2cashflowbgt.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul><b>'.getMenu('keu_2cashflowbgt').'</b></span><br>');

//get existing period

$str="select distinct(substr(periode,1,4)) as tahun from ".$dbname.".setup_periodeakuntansi order by tahun desc";  
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value='".$bar['tahun']."'>".$bar['tahun']."</option>";
}	

$optpt=$optregional=$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select id=pt class=select2 style='width:180px;' onchange=getReg();>".$optpt."</select></td>
				
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id=periode  class=select2 style='width:180px;'> ".$optper."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['regional']."</td>
				<td>:</td>
				<td><select id=regional class=select2 style='width:180px;'  onchange=getUnit()>".$optregional."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td><select id=gudang class=select2 style='width:180px;'>".$optunit."</select></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=3><button class=mybutton onclick=getlaporanaruskas('html')>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=getlaporanaruskas('excel')>".$_SESSION['lang']['excel']."</button>
				<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
				<button hidden class=mybutton onclick=getlaporanaruskas('pdf')>".$_SESSION['lang']['pdf']."</button></td>
			</tr>
		</table>
	
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');

// echo"
	// <div id='both_report'>
	// <div id='head_tableboth' align=right>
		// <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container' table='sortable' >
			// <img title='Full Screen' class='zImgBtn' src='images/full-screen.png'>
		// </a>
	// </div>
		// <div id='container' style='overflow:auto;height:60vh'; ></div>
	// </div>
// ";
echo"<div  class='table-scroll' style='width:100%;height:350px;overflow:auto;' id=container></div>";
CLOSE_BOX();
close_body();
?>