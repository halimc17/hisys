<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
?>

<script language=javascript src='js/keu_2deposito.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<?
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optjenis="<option value=''>".$_SESSION['lang']['all']."</option>";
//get unit
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal)
{
    $optorg.="<option value='".$kei."'>".$fal."</option>";
}

#Tipe Transaksi
$arrtipe=getEnum($dbname,'keu_depositoht','jnsdeposito');
foreach($arrtipe as $kei=>$fal)
{
    switch ($kei) {
        case '1':$capt=$_SESSION['lang']['depositoberjangka'].'(Automatic Roll-Over)';break;
        case '2':$capt=$_SESSION['lang']['depositoberjangka'].'(Non Roll-Over)';break;
    }

    $optjenis.="<option value='".$kei."'>".$capt."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_2deposito').'</span>');
echo"<fieldset style='width:450px;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:159px;\">".$optorg."</select></td>
                </tr> 
				<tr>
                    <td>".$_SESSION['lang']['jenis']."</td>
                    <td>:</td>
                    <td><select id=jenis style=\"width:159px;\">".$optjenis."</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
						<button class=mybutton onclick=html('html')>".$_SESSION['lang']['html']."</button>
						<button class=mybutton onclick=excel('excel',event)>".$_SESSION['lang']['excel']."</button>
						<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
					</td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

//style='overflow:auto;height:400px;max-width:1235px';
OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow-x:hidden;height:400px;'>
</div></fieldset>";
CLOSE_BOX();
echo close_body();
?>