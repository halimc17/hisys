<?
//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script language=javascript>


    function batal()
    {
        document.getElementById('kdsup').value = '';
        document.getElementById('tgl2').value = '';
        document.getElementById('tgl1').value = '';
        document.getElementById('printContainer').innerHTML = '';
    }


</script>

<?

// $optsup = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
// $sql = "SELECT a.namasupplier,a.supplierid FROM ".$dbname.".log_5supplier a 
	// left join ".$dbname.".log_5supkelompok b on a.supplierid=b.supplierid 
	// WHERE b.tipe='TRANSPORTIR' order by a.namasupplier asc";
// $qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
// $qry->setFetchMode(PDO::FETCH_ASSOC);
// while ($data = $qry->fetch()) {
    // $optsup.="<option value=" . $data['supplierid'] . ">" . $data['namasupplier'] . "</option>";
// }

$optsup = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$ha = "SELECT a.namasupplier, a.supplierid FROM ".$dbname.".log_5supplier a 
		left join ".$dbname.".log_5supkelompok b on a.supplierid=b.supplierid 
		WHERE a.status='1' and b.tipe='TRANSPORTIR' 
		order by a.namasupplier asc";
$hi = $owlPDO->query($ha) or die(print " Gagal: " . PDOException::getMessage());
$hi->setFetchMode(PDO::FETCH_ASSOC);
while ($hu = $hi->fetch()) {
    $optsup.="<option value=" . $hu['supplierid'] . ">" . $hu['namasupplier'] . "</option>";
}

$optPt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$ha = "SELECT * FROM " . $dbname . ".organisasi WHERE length(kodeorganisasi)=3 "
        . " ";

$hi = $owlPDO->query($ha) or die(print " Gagal: " . PDOException::getMessage());
$hi->setFetchMode(PDO::FETCH_ASSOC);
while ($hu = $hi->fetch()) {
    $optPt.="<option value=" . $hu['kodeorganisasi'] . ">" . $hu['namaorganisasi'] . "</option>";
}
?>


<?

include('master_mainMenu.php');
if($_SESSION['language']=='EN'){
    OPEN_BOX('','<span class=judul>'.strtoupper('Delivery detail by Transporter').'</span>');
}else{
    OPEN_BOX('','<span class=judul>'.strtoupper('Laporan Pengiriman Per Transportir').'</span>');
}
$arr = "##kdsup##pt##nokontrak##tgl1##tgl2";

echo "<br><fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
        
	<tr>
            <td>" . $_SESSION['lang']['transportir'] . "</td>
            <td>:</td>
            <td><select id=kdsup style='width:164px;'>" . $optsup . "</select></td>
	</tr>
        <tr>
            <td>PT</td>
            <td>:</td>
            <td><select id=pt style='width:164px;'>" . $optPt . "</select></td>
	</tr>
        <tr>
            <td>" . $_SESSION['lang']['nodo'] . " </td> 
            <td>:</td>
            <td><input type=text maxlength=50 id=nokontrak nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:160px;\"></td>
        </tr>
	<tr>
		<td>" . $_SESSION['lang']['tanggal'] . "</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
		s/d
		<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>
	</tr>
        

	<tr>
		<td colspan=100></td>
	</tr>
	<tr>
		<td><td><td>
		<button onclick=zPreview('pmn_slave_2transportir','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
		<button onclick=zExcel(event,'pmn_slave_2transportir.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr>
</table>
</fieldset>"; //<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

echo "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();
?>