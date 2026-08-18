<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>


<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});

});
function detail(vhc, per, tipe, ev) {
    param = 'vhc=' + vhc + '&per=' + per + '&tipe=' + tipe
	// tujuan = 'vhc_slave_2wsvhc_detail.php' + "?" + param;
    // width = '800';
    // height = '400';
    // content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1('Detail Transaksi' + vhc, content, width, height, ev);
	
	alertify.popuppdf("Detail","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_2wsvhc_detail.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}

function getvhc() {
    jenis = document.getElementById('jenis').value;
    ws = document.getElementById('ws').value;
    param = 'proses=getvhc' + '&jenis=' + jenis + '&ws=' + ws;
    tujuan = 'vhc_slave_2wsvhc.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('vhc').innerHTML = con.responseText;
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

$optjenis=$optvhc="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optws=$optper="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='WORKSHOP' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optws.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(17) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optKodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optKodeorg.="</optgroup>";
	}
}


$str = "select * from " . $dbname . ".vhc_5jenisvhc order by namajenisvhc asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optjenis.="<option value=" . $bar['jenisvhc'] . ">" . $bar['namajenisvhc'] . "</option>";
}


$str = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('vhc_2wsvhc').'</span><br>');
$arr = "##ws##jenis##vhc##per";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unitkerja'] . "</td>
                    <td>:</td>
                    <td><select class='select2' id=ws style=\"width:159px;\">" . $optKodeorg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['jenisvch'] . "</td>
                    <td>:</td>
                    <td><select class='select2' id=jenis style=\"width:159px;\" onchange=getvhc()>" . $optjenis . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['kendaraan'] . "</td>
                    <td>:</td>
                    <td><select class='select2' id=vhc style=\"width:159px;\">" . $optvhc . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class='select2' id=per style=\"width:159px;\">" . $optper . "</select></td>
                </tr>                

                
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('vhc_slave_2wsvhc','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'vhc_slave_2wsvhc.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<div id='printContainer' class='table-scroll' style='overflow:auto;height:400px;'; ></div>";
CLOSE_BOX();
echo close_body();
?>