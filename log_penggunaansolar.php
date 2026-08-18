<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_penggunaansolar').'</span><br>');
?>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/log_penggunaansolar.js?v=<?php echo time(); ?>'></script>
<?
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select distinct substr(kodeorganisasi,1,4) as kodeorganisasi from ".$dbname.".organisasi where tipe in ('GUDANG','GUDANGTEMP') order by induk";
$res = fetchdata($str);
foreach($res as $bar){
	$d=getNamaOrg($bar['kodeorganisasi'],'induk');
	if($d!=$n){			
		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . getNamaOrg($bar['kodeorganisasi']) . "</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

$str="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res = fetchdata($str);
foreach($res as $bar){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arr1 = "##kdorg##prd##prdsd";
echo"<div id=tableheader>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg style=\"width:170px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:77px;\">" . $optper . "</select>s/d<select class=select2 id=prdsd style=\"width:77px;\">" . $optper . "</select></td>
                </tr>
				<tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=\"zPreview('log_slave_penggunaansolar','" . $arr1 . "','printContainer');showheader();\" class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'log_slave_penggunaansolar.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</div>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
	
// echo"<div style=clear:both></div>
// <div id='both_report'>
	// <div id='head_tableboth' align=right>
		// <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			// <img title='Full Screen' class='zImgBtn' src='images/full-screen.png'>
		// </a>
	// </div>
	// <div style=clear:both></div>
	// <div id='printContainer'></div>
// </div>";
echo"<div id='printContainer' class='table-scroll' style='overflow:auto;height:73vh;'></div>";
// echo"<div id='printContainer'></div>";

CLOSE_BOX();
echo close_body();
?>