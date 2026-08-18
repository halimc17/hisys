<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('', "<b>Penaksiran Produksi Budget</b><br /><br />");
?>


<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>


<?
$arrnmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$optjenis=$optorg=$optvhc=$optper="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4 
		and tipe='KEBUN' order by namaorganisasi asc ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}


$arr= "##unit##tgl2";

echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:159px;\">" . $optorg . "</select></td>
                </tr>
                
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td>
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='22' maxlength='10' ></td>
                </tr>

                
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
						<button onclick=zPreview('kebun_slave_2pp','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
						<button onclick=zExcel(event,'kebun_slave_2pp.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
						<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
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