<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');


OPEN_BOX('','<span class=judul>'.getMenu('kebun_2borongansendiri').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$wh='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){ 
	$wh='';
} elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    $wh=" and kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
} else {
	$wh=" and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}

$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' ".$wh." order by kodeorganisasi asc ";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optOrg="<option value=''></option>";
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
}

$arr = "##kdorg##tgl1##tgl2";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:160px;\">" . $optOrg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2borongansendiri','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2borongansendiri.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "<div style=clear:both></div>
		<div id='both_report'>
			<div id='head_tableboth' align=right>
				<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
					<img title='Full Screen' class='resicon' src='images/full-screen.png'>
				</a>
				<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
					<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
				</a>
			</div>
		<div id='printContainer' style='overflow:auto;height:450px;max-width:100%'; >
		</div></div>";
CLOSE_BOX();
echo close_body();
?>