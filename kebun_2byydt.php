<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2byydt').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$wh=$whpt='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){ 
	$whpt=''; $wh='';
} elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
	$whpt=" and kodeorganisasi ='".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$whpt=" and kodeorganisasi ='".$_SESSION['empl']['kodeorganisasi']."'";
}

# Option PT
$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='PT' ".$whpt." order by kodeorganisasi asc ";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($rOrg = $qOrg->fetch()) {
    $optpt.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
}

$arrip=array('I'=>'INTI','P'=>'PLASMA');
$optip="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrip as $key => $val){
	$optip.="<option value=".$key.">".$val."</option>";
}
$arrst=array('PNN'=>'PANEN','TM'=>'TM','TBM'=>'TBM');
$optst="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrst as $key => $val){
	$optst.="<option value=".$key.">".$val."</option>";
}

$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";

$str = "select distinct periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 13 ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$prd1="<option value=''></option>";
while ($bar = $res->fetch()) {
    $prd1.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arr = "##pt##kdorg##div##tt##ip##prd";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td colspan=3><select id=pt onchange=\"getEstTTIP(this.value,'kdorg','tt','all')\" style=\"width:150px;\">" . $optpt . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td colspan=3><select id=kdorg onchange=\"getdivtt(this.value,'div','tt','all')\" style=\"width:150px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td colspan=3><select id=div onchange=gettt(this.value,'tt','all') style=\"width:150px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td colspan=3><select id=tt style=\"width:150px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>Inti / Plasma</td>
                    <td>:</td>
                    <td colspan=3><select id=ip style=\"width:150px;\">" . $optip . "</select></td>
                </tr>
				<tr>
                    <td>Status</td>
                    <td>:</td>
                    <td colspan=3><select id=status style=\"width:150px;\">" . $optst . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td colspan=3><select id=prd style=\"width:150px;\">" . $prd1 . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2byydt','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2byydt.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
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