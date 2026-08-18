<?php
//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/kebun_lpjkud.js'></script>

<?php
$optPer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPer="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 12 ";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch())
{
    $optPer.="<option value='".$dPer['periode']."'>".$dPer['periode']."</option>";
}

$optKud = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sKud = "select * from ".$dbname.".log_5supplier where kodekelompok = 'S004'";
$qKud=$owlPDO->query($sKud) or die(print " Gagal: ".PDOException::getMessage());
$qKud->setFetchMode(PDO::FETCH_ASSOC);
while($rKud = $qKud->fetch()){
	$optKud .= "<option value='".$rKud['supplierid']."'>".$rKud['namasupplier']."</option>";
}


OPEN_BOX();
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='float:left;'>";
    echo"<legend>LPJ KUD</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=periode style=\"width:175px;\">".$optPer."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['namakud']."</td>
                    <td>:</td>
                    <td><select id=namakud style=\"width:175px;\">".$optKud."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['upah']."</td>
                    <td>:</td>
                    <td><input type=text id=upah  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber value='0' style=\"width:120px;\"> </td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['material']."</td>
                    <td>:</td>
                    <td><input type=text id=material  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber value='0'  style=\"width:120px;\"> </td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['transport']."</td>
                    <td>:</td>
                    <td><input type=text id=transport  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber value='0'  style=\"width:120px;\"> </td>
                </tr>
				<tr>
                    <td style='vertical-align:top'>".$_SESSION['lang']['lain']."</td>
                    <td style='vertical-align:top'>:</td>
                    <td><input type=text id=lain  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber value='0'  style=\"width:120px;\"></td>
                </tr>
                <tr>
					<td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                                <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                        </td>
                </tr>

        </table></fieldset>
                        <input type=hidden id=method value='insert'>";
        


CLOSE_BOX();
?>



<?php
OPEN_BOX();
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>