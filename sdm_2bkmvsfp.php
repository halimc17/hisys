<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<script>

function changediv(unit) {
	param = 'unit='+unit.value;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('divisi').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('slave_option.php?proses=changediv', param, respon);
}

</script>

<?php
##untuk pilihan pabrik 	
$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPabrik="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$nPabrik=$owlPDO->query($iPabrik) or die(print " Gagal: ".PDOException::getMessage());
$nPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($dPabrik=$nPabrik->fetch())
{
    $optPabrik.="<option value=".$dPabrik['kodeorganisasi'].">".$dPabrik['namaorganisasi']."</option>";
}
$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";		


$optDiv="";

?>



<?
include('master_mainMenu.php');
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.getMenu('sdm_2bkmvsfp').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.getMenu('sdm_2bkmvsfp').'</span>');
}
$arr="##unit##tgl1##tgl2##divisi";	

echo "<br><fieldset style='float:left;'><legend><b>Form</b></legend>
<table border=0>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select onchange='changediv(this)' id=unit style=\"width:164px;\" >".$optPabrik."</select></td>
        </tr>
		<tr>
			<td>" . $_SESSION['lang']['divisi'] . "</td>
			<td>:</td>
			<td><select id=divisi style=\"width:164px;\">" . $optDiv . "</select></td>
        </tr>
		<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
            s/d
            <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>	

	<tr>
		<td colspan=2></td><td>
		<button onclick=zPreview('sdm_slave_2bkmvsfp','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'sdm_slave_2bkmvsfp.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>

		<button onclick=batalLaporan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>
CLOSE_BOX();
OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();


?>