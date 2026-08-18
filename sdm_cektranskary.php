<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_cektranskary').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/datakaryawan.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function del(karyawanid,no) {
	param = 'proses=delete' + '&karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_cektranskary.php';
	if (confirm(' Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tr_'+no).style.display = "none";
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
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT' and kodesejarah=''",'2','0',true);

$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTt="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrIP=array("I"=>"INTI","P"=>"PLASMA");
$optIP="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrIP as $res => $bar){
	$optIP.="<option value=".$res.">".$bar."</option>";
}

$opttipe="";
$opttipe.="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where id in ('1','2','3','4') order by no asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $opttipe.="<option value=" . $bar['id'] . ">" . $bar['tipe'] . "</option>";
}

$arr1 = "##pt##kdorg##tipe";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','".$_SESSION['lang']['all']."')  style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tipe'] . "</td>
                    <td>:</td>
                    <td><select id=tipe style=\"width:164px;\">" . $opttipe . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('sdm_slave_cektranskary','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'sdm_slave_cektranskary.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
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
	<div id='printContainer' style='overflow:auto;height:380px'; ></div>
</div>
";

CLOSE_BOX();
echo close_body();
?>