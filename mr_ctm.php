<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('mr_ctm').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>

    function lihatdetail(blok, thn, tipe, ev) {
        param = 'tipe=' + tipe + '&blok=' + blok + '&thn=' + thn;
        tujuan = 'mr_slave_ctm_detail_jurnal.php' + "?" + param;
        width = '500';
        height = '200';
        content = "<iframe frameborder=0 width=100% height=90% src='" + tujuan + "'></iframe>"
        showDialog1('Detail Transaksi' + blok, content, width, height, ev);
    }
    
    function lihatdetail2(blok, thn, tipe, ev) {
        param = 'tipe=' + tipe + '&blok=' + blok + '&thn=' + thn;
        tujuan = 'mr_slave_ctm_detail_kg.php' + "?" + param;
        width = '500';
        height = '200';
        content = "<iframe frameborder=0 width=100% height=90% src='" + tujuan + "'></iframe>"
        showDialog1('Detail Transaksi' + blok, content, width, height, ev);
    }


</script>

<?
$optOrg="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optOrg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$arr = "##kdorg";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:159px;\">" . $optOrg . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('mr_slave_ctm','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'mr_slave_ctm.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
</div></fieldset>"; //<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>
CLOSE_BOX();
echo close_body();
?>