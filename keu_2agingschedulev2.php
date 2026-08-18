<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
?>

<script language=javascript src='js/keu_2agingSchedulev2.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<?
$opt=$optorg=$opttipe=$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opt="<option value=''>".$_SESSION['lang']['all']."</option>";
//get unit
$arrtipe=getOrgDetail(3);
foreach($arrtipe as $kei=>$fal){
    $scek="select distinct * from ".$dbname.".keu_jurnaldt where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kei."') limit 5";
    $rcek=fetchData($scek);
    if(count($rcek)!=0){
        $optorg.="<option value='".$kei."'>".$fal."</option>";    
    }
}

#Tipe Form
$opttipe.="<option value='rnv'>Receive not vouchered (RNV)</option>";
$opttipe.="<option value='inv'>Invoice</option>";

#Jenis
$optjenis.="<option value='detail'>Detail</option>";
$optjenis.="<option value='summary'>Summary</option>";

OPEN_BOX('','<span class=judul>'.getMenu('keu_2agingschedulev2').'</span>');
echo"<fieldset style='width:450px;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select id=pt style=\"width:159px;\" onchange=getunit()>".$optorg."</select></td>
                </tr> 
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:159px;\">".$opt."</select></td>
                </tr> 
		        <tr>
		            <td>".$_SESSION['lang']['tanggal']."</td>
		            <td>:</td>
		            <td><input type=text value=".date('d-m-Y')." class=myinputtext id=tanggal onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style='width:75px;' /></td>
		        </tr>
				<tr>
                    <td>".$_SESSION['lang']['tipe']."</td>
                    <td>:</td>
                    <td><select id=tipeform style=\"width:159px;\">".$opttipe."</select></td>
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