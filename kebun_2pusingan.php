<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2pusingan').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function update() {
	tgl1 = document.getElementById('tgl1').value;
	tgl2 = document.getElementById('tgl2').value;
	kdorg = document.getElementById('kdorg').value;
	
	param = '';
	param += '&tgl1='+tgl1;
	param += '&tgl2='+tgl2;
	param += '&kdorg='+kdorg;
	if(kdorg==''){
		alert('Kode Unit harus diisi.'); return;
	}
	
	if(tgl1=='' || tgl2==''){
		alert('Tanggal pertama dan tanggal kedua harus diisi.'); return;
	}
	
	if(tgl1>tgl2){
		alert('Tanggal pertama tidak boleh lebih besar dari tanggal kedua.'); return;
	}
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    alert("Done");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }    
    post_response_text('kebun_slave_3pusingan_otomatis.php?', param, respon);
}
</script>
<?
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." and tipe='KEBUN' order by namaorganisasi asc ";
$res=fetchdata($str);
foreach($res as $bar){
	$optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}


$arr = "##kdorg##tgl1##tgl2##divisi";

echo"<table><tr><td valign=top>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg onchange=getdivisipusing() style=\"width:164px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi style=\"width:164px;\"></select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10'  readonly>
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10'  readonly></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2pusingan','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2pusingan.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<button onclick=update() class=mybutton name=preview id=update>Update</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</td>";
echo"<td valign=top>";
echo"<fieldset style='float:left;'>
		<legend>Info</legend>
		<table>
			<tr><td>Jika data yang di tampilkan belum / tidak update silahkan click tombol Update dan tunggu sampai proses selesai kemudian click tombol Preview</td></tr>
			<tr><td>j = Janjang Panen, h = Ha Panen</td></tr>
		</table>
	</fieldset>";


echo"</td></tr>";
echo"</table>";

CLOSE_BOX();

OPEN_BOX();
echo "
<div style=clear:both></div>
<div id='printContainer' class='table-scroll'></div>";
CLOSE_BOX();
echo close_body();
?>