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
<script language=javascript>
function getdivisi()
{ 	unit= document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param='unit='+unit+'&proses=getdivisi';	
	tujuan='sdm_slave_2gajiharianOpt.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					document.getElementById('divisi').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}
</script>

<?

$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
		where length(kodeorganisasi)=4 ".$vOrg." order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$optPer="";
$iPer="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch()){
	$optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
}

if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Rekap Pengobatan').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Medical List').'</span>');
}



echo"<br>";
$arr="##unit##tgl1##tgl2##veriv##bayar";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:168px;\">".$optunit."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td>
						<input type=text class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' />
						<input type=text class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' />
					</td>	
                </tr>
                <tr>
                    <td>Verivikasi</td>
                    <td>:</td>
                    <td><input type=checkbox id=veriv></td>
                </tr>
				<tr>
                    <td>Bayar</td>
                    <td>:</td>
                    <td><input type=checkbox id=bayar></td>
                </tr>

                <tr>
                    <td><td><td>
                    <button onclick=zPreview('sdm_slave_2rekapmedical','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'sdm_slave_2rekapmedical.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();








?>