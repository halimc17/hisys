<?php

require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language="JavaScript1.2" src="js/zTools.js"></script>
<script language=javascript1.2 src="js/log_2pengeluaranBarangInventaris.js"></script>
<script language="javascript1.2" src="js/zReport.js"></script>
<?php

### BEGIN GET EXITING PERIODE ###
$str = "select DISTINCT(DATE_FORMAT(tanggal,'%Y-%m')) AS periode from " . $dbname . ".log_transaksi_vw
      order by tanggal desc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$num_rows = owlBaris($res);
$optperiode = "";
if ($num_rows >= 1) {
    while ($bar = $res->fetch()) {
        $optperiode.="<option value='" . $bar->periode . "'>" . substr($bar->periode, 5, 2) . "-" . substr($bar->periode, 0, 4) . "</option>";
    }
} else {
    
}
### END GET EXITING PERIODE ###
// cari nomor po
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{ 
    $optpo="<option value=''>".$_SESSION['lang']['all']."</option>";
    
    $str="select distinct nopo as nopo from ".$dbname.".log_poht where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT')";
    $str=$owlPDO->query($str);
	$str->setFetchMode(PDO::FETCH_OBJ);
	$optpo="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
	while($bar=$str->fetch())
	{
		$optpo.="<option value='".$bar->nopo."'>".$bar->nopo."</option>";
	}
	
} else {
       
    $optpo="<option value=''>".$_SESSION['lang']['all']."</option>";
	
    $str="select distinct nopo as nopo from ".$dbname.".log_poht where kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' )";
    $str=$owlPDO->query($str);
	$str->setFetchMode(PDO::FETCH_OBJ);
	$optpo="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
	while($bar=$str->fetch())
	{
		$optpo.="<option value='".$bar->nopo."'>".$bar->nopo."</option>";
	}

}
//==========================

$arr = "##kodebarang##nopo##periode";
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['penerimaanbarang'].' (Asset / inventaris)').'</span><br>');
echo"<fieldset style='float: left;'>
		<legend><b>Form</b></legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['kodebarang'] . "</td><td>:</td>
				<td><input type=text style=width:200px size=10 maxlength=10 id=kodebarang placeholder='" . $_SESSION['lang']['all'] . "' class=myinputtext onkeypress=\"return false;\" onclick=\"showWindowBarang('" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "',event);\">
				</td>
				</td>
			</tr>
			
			<tr>
				<td><label>".$_SESSION['lang']['nopo']."</label></td><td>:</td>
				<td><select id=nopo style='width:203px'>".$optpo."</select>
				<img id='kegiatan' onclick=z.elSearch('nopo',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['periode'] . "</td><td>:</td>
				<td><select id=periode>" . $optperiode . "</select></td>
			</tr>
			<tr>
				<td></td><td></td>
				<td><button class=mybutton onclick=proses()>" . $_SESSION['lang']['proses'] . "</button>
				<button class=mybutton onclick=setAll()>" . $_SESSION['lang']['cancel'] . "</button></td>
			</tr>
		</table>
	</fieldset>";

CLOSE_BOX();

OPEN_BOX('', '');
echo"<fieldset><legend>List Data</legend><span id=printPanel style='display:none;'>
	<img onclick=\"zExcel(event,'log_slave_2pengeluaranBarangInventaris.php','" . $arr . "');\" src=images/excel.jpg class=resicon title='MS.Excel'> 
	</span>    
	<div id=container style='width:100%;height:359px;overflow:auto;'>
    </div></fieldset>";
CLOSE_BOX();
echo close_body();
?>