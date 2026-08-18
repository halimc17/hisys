<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/keu_2neraca_v3.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul><b>'.getMenu('keu_2neraca_v3').'</b></span><br>');

//get existing period

$str="select distinct periode from ".$dbname.".setup_periodeakuntansi where left(periode,4) >= '2021'
    order by periode desc";  
$optper='';

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
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

$opttipelaporan="<option value='rekap'>".$_SESSION['lang']['rekap']."</option>";
$opttipelaporan.="<option value='default'>".$_SESSION['lang']['default']."</option>";
$opttipelaporan.="<option value='detail'>".$_SESSION['lang']['detail']."</option>";

$arrTampilan=array("0"=>"Tampilkan Nol","1"=>"Tidak Tampilkan Nol");
foreach ($arrTampilan as $key => $value) {
    @$optTampilan.="<option value='".$key."'>".$value."</option>";
}

 
echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select id=pt style='width:180px;' class='select2' onchange=getUnit();>".$optpt."</select></td>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id=periode style='width:180px;'  class='select2'> ".$optper."</select></td>
				
				<td>".$_SESSION['lang']['statussaldo']."</td>
				<td>:</td>
				<td><select id=tampilannol style='width:180px;'  class='select2'> ".$optTampilan."</select> *Terpakai untuk tipelaporan detail</td>
			</tr>
			<tr >
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td><select id=unit style='width:180px;'  class='select2'>".$optgudang."</select></td>
				<td>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['laporan']."</td>
				<td>:</td>
				<td><select id=tipelaporan style='width:180px;'  class='select2'>".$opttipelaporan."</select></td>
			</tr>
			
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
echo"
    <div id=container style='width:100%;height:350px;overflow:auto;'>
    </div>";
CLOSE_BOX();
close_body();
?>