<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>


<?
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>Laporan Data Karyawan</span><br>');

/*
$optorg=$optthn="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}
*/

//$arr = "##unit";
$arr = "##nama";
//$frm[0]="<fieldset style='float:left;'>
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>Nama Karyawan</td>
                    <td>:</td>
                    <td><input type=text id=nama class=myinputtext onkeypress=\"return tanpa_kutip(event)\"></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('laporan_slave_2ikaryawan','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'laporan_slave_2ikaryawan.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    <button onclick=zPdf('laporan_slave_2ikaryawan','" . $arr . "','printContainer') class=mybutton name=preview id=preview>PDF</button>
                   </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1200px'; >
</div></fieldset>"; 
CLOSE_BOX();
echo close_body();
?>