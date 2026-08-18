<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_3tutupbukubank').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/keu_3tutupbukubank.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg=$optperiode=$where='';
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$where = "and kodeorganisasi in (".getOrgDetail(2).")";
$str="select * from ".$dbname.".organisasi where 1=1 ".$where." order by induk, kodeorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$d=$bar['induk'];
	if($d!=$n){			
		$optorg.="<optgroup label='".$bar['induk']." - ".getNamaOrg($bar['induk'])."'>";
	}
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kodeorg onchange=getperiode(this.value) style=\"width:150px;\">" .$optorg . "</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=periode style=\"width:150px;\"></select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=preview('show') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>
<div style=clear:both></div>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id='container' style='overflow:auto;height:63vh'; ></div>";

CLOSE_BOX();
echo close_body();
?>