<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script languange=javascript1.2 src='js/sdm_5possecurity.js'></script>

<?


OPEN_BOX('','<span class=judul>'.strtoupper("Master Pos Security").'</span>');




$query = selectQuery($dbname,"sdm_5possecurity","nopos, namapos, unit, status","","createdtime desc");
$resTab = fetchData($query);
$optUnit = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","NOT (tipe='PT' or tipe='HOLDING') and length(kodeorganisasi)=4");
$arStat = array('0'=>'Tidak Aktif','1'=>'Aktif');
#== Prep List Header
$header = array("No. Pos","Nama Pos","Unit","Status");

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
        if($col=='unit')
        {
            $table .= "<td hidden id='".$col."_id_".$key."'>".$dat."</td>";
            $dat=$optUnit[$dat];
        }
        if($col=='status')
        {
            $dat=$arStat[$dat];
        }

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
    makeElement('nopos','label','No. Pos'),
    makeElement('nopos','textnum','0',array('style'=>'width:145px','maxlength'=>'10',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
//print_r($optTipeJenis);
$els[] = array(
    makeElement('namapos','label','Nama Pos'),
    makeElement('namapos','text','',array('style'=>'width:145px','maxlength'=>'80',
        'onkeypress'=>'return tanpa_kutip(event)'))
);

$els[] = array(
    makeElement('unit','label','Unit'),
    makeElement('unit','select','',array('style'=>'width:145px'),$optUnit)
);

$els[] = array(
    makeElement('status','label','Aktif/Tidak Aktif'),
    makeElement('status','checkbox','1')
);

$els2['btn'] = array(
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
echo genElement($els2);
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