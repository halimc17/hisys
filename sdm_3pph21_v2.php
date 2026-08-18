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
<link rel=stylesheet type=text/css href=style/zTable.css>
<script languange=javascript1.2 src='js/sdm_3pph21_v2.js?v=<?php echo time(); ?>'></script>

<?
## GET UNIT
$optunit="<option value=''>Pilih Data</option>";
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optunit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}


$optPer="";
$str="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optPer.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_3pph21').'</span>');


echo"<br>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1 cellpadding= 3>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:200px;\">".$optunit."</select></td>
                </tr>
				
				<tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:200px;\">".$optPer."</select></td>
                </tr>
                <tr hidden>
                    <td>Tunjangan Pph21 </td>
                    <td>:</td>
                    <td><input type='checkbox' id='istjpph21' onclick='makeenabled()'></td>
                </tr>
                <tr hidden>
                    <td>Nilai Batas Pendapatan (Pertahun)</td>
                    <td>:</td>
                    <td><input type=text class=myinputtextnumber style=\"width:164px;\" id='nilaitjpph21' onkeypress=\"return angka_doang(event);\" disabled></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=preview('previewawal') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=excel() class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
			
	</fieldset>
		<span>
			<ol style='margin-left:20%'>INFO :</ol>
				<li style='margin-left:25%'>Sebelum melakukan proses PPH21 harap dipastikan :</li>			
				<li style='margin-left:25%'>1. Lakukan proses gaji Harian dan Bulan, melalui SDM - Proses - Penggajian Harian / Bulanan.</li>			
				<li style='margin-left:25%'>2. Tutup periode penggajian unit, melalui menu SDM - Setup - Periode Penggajian Unit.</li>			
				<li style='margin-left:25%'>3. Jalankan proses PPH21</li><br>
		</span>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id='printContainer'></div>";

CLOSE_BOX();
echo close_body();


?>