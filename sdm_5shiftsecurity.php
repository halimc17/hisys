<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script languange=javascript1.2 src='js/sdm_5shiftsecurity.js'></script>

<?


OPEN_BOX('','<span class=judul>'.strtoupper("Master Shift Security").'</span>');




$query = selectQuery($dbname,"sdm_5shiftsecurity","kodeshift,namashift,jamawal, jamakhir","","createdtime desc");
$resTab = fetchData($query);
$arName = getenum($dbname,"sdm_5shiftsecurity","namashift");
$arShift = array('1'=>"Malam",'2'=>"Siang",'3'=>"Pagi",'4'=>"Libur");
$optShift=array();
$no=1;
foreach ($arName as $key => $value) {
    $optShift[$value]=$value." - ".$arShift[$no];
    $no++;
}
//print_r($optShift);
#== Prep List Header
$header = array("Kode Shift","Nama Shift","Jam Awal","Jam Akhir");

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
        if($col=='namashift')
        {
            $dat=$optShift[$dat];
        }
        if($col=='jamawal' || $col== 'jamakhir')
        {
            $dat=substr($dat, 0,5);
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
    makeElement('kodeshift','label','Kode Shift'),
    makeElement('kodeshift','textnum','',array('style'=>'width:145px','maxlength'=>'10','disabled'=>'disabled'))
);

$els[] = array(
    makeElement('namashift','label','Nama Shift'),
    makeElement('namashift','select','',array('style'=>'width:145px'),$optShift)
);

$els[] = array(
    makeElement('jamawal','label','Jam Awal'),
    makeElement('jamawal','text','00:00',array('style'=>'width:60px','maxlength'=>'5',
        'onkeypress'=>'return tanpa_kutip(event)','onblur'=>'updtjam(this)'))
);
$els[] = array(
    makeElement('jamakhir','label','Jam Akhir'),
    makeElement('jamakhir','text','00:00',array('style'=>'width:60px','maxlength'=>'5',
        'onkeypress'=>'return tanpa_kutip(event)'))
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