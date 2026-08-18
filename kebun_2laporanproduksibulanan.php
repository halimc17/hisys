<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('', '<span class=judul>' . getMenu('kebun_2laporanproduksibulanan') . '</span><br>');

$arr="##pt##prd_start##prd_end";

$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$sTgl="SELECT DISTINCT substr(notransaksi,1,6) as periode from ".$dbname.".kebun_prestasi_detail ORDER BY periode desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while($rTgl=$qTgl->fetch())
{
   $optper.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'], 0, 4) . '-' . substr($rTgl['periode'], 4, 2)."</option>";
}

$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

foreach(getOrgDetail(3) as $key => $val){	
	$optpt.="<option value=".$key.">".$key." - ".$val."</option>";
}

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<script>
    function batal() {
    document.getElementById('printContainer2').innerHTML = '';
}
</script>
<script language=javascript src=js/Chart.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
echo "<div id=tableheader>";
echo "<fieldset style=float:left><legend>Form</legend>";
echo "<table cellspacing=1 border=0>";
echo "<tr><td>".$_SESSION['lang']['pt']."</td><td>:</td><td><select class=select2 id=pt style=width:200px>".$optpt."</select></td></tr>";
echo "<tr><td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td>
                        <div style=\"display:inline-block\">
                            <select class=\"select2\" id=\"prd_start\" style=\"width:80px;\">
                                 " . $optper . "
                            </select>
                        </div>

                        s/d

                        <div style=\"display:inline-block\">
                            <select class=\"select2\" id=\"prd_end\" style=\"width:80px;\">
                                " . $optper . "
                            </select>
                        </div>
                    </td></tr>";
echo "<tr><td></td><td></td><td colspan=3>

<button onclick=\"zPreview('kebun_slave_2laporanproduksibulanan','".$arr."','printContainer2');batal();\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>

<button onclick=\"zExcel(event,'kebun_slave_2laporanproduksibulanan.php','".$arr."');\" class=\"mybutton\" name=\"excel2\" id=\"excel2\">".$_SESSION['lang']['excel']."</button>

<button onclick=batal() class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['cancel']."</button></td></tr>";
echo "</table></fieldset>";
echo "<div style='clear:both'></div>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer2' class='table-scroll' style='overflow:auto;height:73vh;'></div>";
CLOSE_BOX();
echo close_body();
?>