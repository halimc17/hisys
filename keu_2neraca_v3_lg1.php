<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/keu_2neraca_v3_lg1.js?v=<?php echo time(); ?>'></script>

<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul><b>'.getMenu('keu_2neraca_v3_lg1').'</b></span><br>');

//get existing period

$str="select distinct left(periode,4) as periode from ".$dbname.".setup_periodeakuntansi where left(periode,4) >= '2021' group by left(periode,4) order by periode desc"; // echo $str;
$optper='';

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optper.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}	
 
//=================ambil PT;  
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

$opttipelaporan="<option value='default'>".$_SESSION['lang']['default']."</option>";
// $opttipelaporan.="<option value='detail'>".$_SESSION['lang']['detail']."</option>";
 
echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select id=pt style='width:180px;' onchange=getUnit();>".$optpt."</select></td>
			</tr>
			<tr >
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td><select id=unit style='width:180px;'>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id=periode style='width:87px;'> ".$optper."</select>
				</td>
			</tr>
			<!--tr>
				<td>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['laporan']."</td>
				<td>:</td>
				<td><select id=tipelaporan style='width:180px;'>".$opttipelaporan."</select></td>
			</tr-->
			<tr>
				<td></td>
				<td></td>
				<td colspan=3><button class=mybutton onclick=getLaporanNeraca('html')>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=getLaporanNeraca('excel')>".$_SESSION['lang']['excel']."</button>
				<!--button class=mybutton onclick=getLaporanNeraca('pdf')>".$_SESSION['lang']['pdf']."</button--></td>
			</tr>
		</table>
	
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
/*
<span id=printPanel style='display:none;'>
        <img onclick=fisikKeExcel(event,'keu_laporanNeraca_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
        <img hidden onclick=fisikKePDFneraca(event,'keu_laporanNeraca_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
  </span>    */
echo"<fieldset>
     <legend>".$_SESSION['lang']['form']."</legend>
    <div id=container style='width:100%;height:300px;overflow:auto;'>
    </div></fieldset>";
CLOSE_BOX();
close_body();
?>