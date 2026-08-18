<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script languange=javascript1.2 src='js/legal_5jenispreventiveprofile.js'></script>

<?


OPEN_BOX('','<span class=judul>'.strtoupper("Jenis Preventive Profile").'</span>');




$query = selectQuery($dbname,"legal_5jenispreventiveprofile");
$resTab = fetchData($query);
#== Prep List Header
$header = array("No. Urut","Jenis");

$table = "<table id='listData' class='sortable' border=0 style='width:500px;>";
$table .= "<thead><tr class='rowheader'>";
foreach($header as $head) {
    $table .= "<td>".$head."</td>";
}
$table .= "<td style='width:30px;'>*</td>";
$table .= "</tr></thead>";
$table .= "<tbody id='bodyList'>";
foreach($resTab as $key=>$row) 
{
    $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
    
    foreach($row as $col=>$dat) 
    {
        $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
    }
    $table .= "<td id='edit_".$key."'>";
    $table .= "<img src='images/application/application_edit.png' ";
    $table .= "class=resicon  caption='Edit' onclick='edit(".$key.")'></td>";                             	
    $table .= "</tr>";
}
$table .= "</tbody>";
$table .= "<tfoot></tfoot></table>";

#== Prep Form Header

# Elements
$els = array();
$els[] = array(
    makeElement('nourut','label','No. Urut'),
    makeElement('nourut','textnum','0',array('style'=>'width:145px','maxlength'=>'20',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
//print_r($optTipeJenis);
$els[] = array(
    makeElement('jenis','label','Jenis'),
    makeElement('jenis','text','',array('style'=>'width:145px','maxlength'=>'20',
        'onkeypress'=>'return tanpa_kutip(event)'))
);

$els['btn'] = array(
    makeElement('saveButton','button',$_SESSION['lang']['save'],array('onclick'=>'addData()')),
    makeElement('clearButton','button',$_SESSION['lang']['cancel'],array('onclick'=>'clearData()'))
);

//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
#===== Show =======


# Active Form
echo "<fieldset id='fieldForm' style='width:500px; clear:right;min-height:auto;'>";
echo genElement($els);
echo "</fieldset>";


# Table
echo open_theme($_SESSION['lang']['list']);
echo "<div id=container>";
echo $table;
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>