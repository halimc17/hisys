<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/pabrik_uploadfilesortasi.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?

$optorg="";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
		$optorg.="<option value='".$bar['kodeorganisasi']."' selected>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}else{
		$optorg.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
}

$optsup="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct(kodecustomer) as kodecustomer from ".$dbname.".pabrik_timbangan where kodeorg='' and kodebarang='40000003'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optsup.="<option value='".$bar['kodecustomer']."'>".getSupNameWb($bar['kodecustomer'])."</option>";
}


// OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['sortasi'] ." ".$_SESSION['lang']['external']).'</span>');
OPEN_BOX('','<span class=judul><b>'.getMenu('pabrik_2sortasiexternal').'</b></span><br>');
// echo"<br>";
$arr="##unit##tgl1##tgl2##sup";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=width:200px>".$optorg."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><select id=sup  style=width:200px>".$optsup."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td>
						<input type=text class='myinputtext' readonly id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'   style=width:84px maxlength='10' value='".date('01-m-Y')."'/>
						s/d
						<input type=text class='myinputtext' readonly id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'   style=width:84px maxlength='10' value='".date('d-m-Y')."' />
					</td>	
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('pabrik_slave_2sortasiexternal','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'pabrik_slave_2sortasiexternal.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";

echo"
<div id='printContainer'>
</div>";//<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>

CLOSE_BOX();
echo close_body();








?>