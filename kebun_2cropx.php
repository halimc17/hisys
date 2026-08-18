<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2cropx').'</span><br>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function mengumpul(sumber, periode,tipe,tt) {
	// width = '';
	// height = '';
	// content = "<div id=containerd style=\"width:100%;max-height:700px;overflow:auto;\"></div>";
	// ev = 'event';
	// title = "Preview";
	// showDialog5(title, content, width, height, ev);
	
	param = 'method=mengumpul' + '&sumber=' + sumber + '&periode=' + periode;
	param += '&tipe=' + tipe;
	param += '&tt=' + tt;
	tujuan = 'kebun_slave_2cropx_popup.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('600px','500px');

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
</script>
<?
$optorg=$optDiv='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$wh='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){ 
	$wh='';
} elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    $wh=" and kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
} else {
	$wh=" and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}

$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN' ".$wh." and kodesejarah=''",'2','0',true);


$arrIP=array("BBT"=>"Bibitan","TBM"=>"TB dan TBM","TM"=>"TM dan Panen");
$optIP="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrIP as $res => $bar){
	$optIP.="<option value=".$res.">".$bar."</option>";
}
$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optper="<option value=''></option>";
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}


$arr1 = "##kdorg##prd";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				
				
				<tr>
                    <td >" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd   style=\"width:164px;\">" .$optper . "</select></td>
                </tr>
				
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2cropx','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2cropx.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<div id='printContainer' style='overflow:auto;height:400px'; ></div>
</div>
";

CLOSE_BOX();
echo close_body();
?>