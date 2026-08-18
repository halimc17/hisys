<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script languange=javascript1.2 src='js/lgl_5checklist.js'></script>

<?


OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['setup']." Checklist").'</span>');

$optJenis=array();;
$arrayJenis=getenum($dbname,"lgl_5checklist","jenis");
foreach ($arrayJenis as $key) {
    $optJenis[$key] = $key;  
}

$query = selectQuery($dbname,"lgl_5checklist","kode,jenis,status");
$resTab = fetchData($query);
#== Prep List Header
$header = array("Kode","Jenis Checklist","Status");

$table = "<table id='listData' class='sortable' border=0 style='width:500px;>";
$table .= "<thead><tr class='rowheader'>";
foreach($header as $head) {
    $table .= "<td align='center'>".$head."</td>";
}
$table .= "<td style='width:30px;' colspan=3 align='center'>*</td>";
$table .= "</tr></thead>";
$table .= "<tbody id='bodyList'>";
foreach($resTab as $key=>$row) 
{
    $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
    
    foreach($row as $col=>$dat) 
    {
        if($col == 'status')
        {
            if($dat==1)
            {
                $dat='Aktif';
            }
            else
            {
                $dat='Tidak Aktif';
            }
        }
        
        $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
    }
    $table .= "<td id='edit_".$key."'>";
    $table .= "<img src='images/application/application_edit.png' ";
    $table .= "class=resicon  title='Edit' onclick='edit(".$key.")'></td>";   
    $table .= "<td id='adddetail_".$key."'>";
    $table .= "<img src=images/plus.png ";
    $table .= "class=resicon  title='Add Detail ' onclick='addDetail(".$key.",event)'></td>";
    $table .= "<td id='previewDetail_".$key."'>";
    $table .= "<img src=images/zoom.png ";
    $table .= "class=resicon  title='Detail ' onclick='previewDetail(".$key.",event)'></td>";

    $table .= "</tr>";
}
$table .= "</tbody>";
$table .= "<tfoot></tfoot></table>";

#== Prep Form Header

# Elements
$els = array();
$els[] = array(
    makeElement('kode','label','Kode'),
    makeElement('kode','textnum','0',array('style'=>'width:145px','maxlength'=>'20',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);

$els[] = array(
    makeElement('jenis','label','Jenis Checklist'),
    makeElement('jenis','select','',array('style'=>'width:150px'),$optJenis)
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

CLOSE_BOX();

OPEN_BOX();

# Table
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>";
echo "<div id=container>";
echo $table;
echo "</div>";
echo "</fieldset>";
CLOSE_BOX();
echo close_body();
?>