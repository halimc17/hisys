<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_lapalokgaji').'</span><br>');
?>
<script language="javascript" >
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/keu_lapalokgaji.js?v=<?php echo time(); ?>'></script>
<?
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select * from ".$dbname.".organisasi where namaorganisasi not like '%NON AKTI%' and length(kodeorganisasi)='4' order by induk";
$res=fetchdata($str);
foreach($res as $bar){
	$d=getNamaOrg($bar['kodeorganisasi'],'induk');
	if($d!=$n){			
		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
	
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res=fetchdata($str);
foreach($res as $bar){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arr1 = "##kdorg##prd##prdsd";
echo"<fieldset style='float:left;' id=tableheader>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:82px;\">" . $optper . "</select><select class=select2 id=prdsd style=\"width:82px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=preview('preview'); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=preview('excel') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";

#showheader();

CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer' class='table-scroll' style=height:73vh></div>";

CLOSE_BOX();
echo close_body();
?>