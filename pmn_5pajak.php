<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script languange=javascript1.2 src='js/pmn_5pajak.js'></script>

<link rel=stylesheet type=text/css href='style/zTable.css'>
<?


OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pajak']).'</span>');


//get pph from keu_5akun
$optPph=makeOption($dbname,"pmn_5jenispajak","kodepajak,namapajak","",0,true);
$optPenghasilan=makeOption($dbname,"pmn_5jenispenghasilan","idpenghasilan,namapenghasilan");
//array cara pembayaran
$arrayPembayaran = getEnum($dbname,"pmn_5pajak","carapembayaran");
$optPembayaran=array();
foreach ($arrayPembayaran as $key => $val) {
    if($val== 1)
    {
        $tval="Dibayar Sendiri";
    }
    else if($val== 2)
    {
        $tval="Dipungut Pihak Lain";
    }
    $optPembayaran[$key]=$tval;
}

//array jenis penghasilan



//Get NPWP from customer

#== Get data

$query = selectQuery($dbname,"pmn_5pajak");
$resTab = fetchData($query);
#== Prep List Header
$header = array($_SESSION['lang']['jenispph'],$_SESSION['lang']['carapembayaran'],$_SESSION['lang']['jenispenghasilan']);

$table = "<table id='listData' class='sortable'>";
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
        if($col != 'id')
        {
            //print_r($row['id']);
            if($col == 'jenispph')
            {
                $dat=$optPph[$dat];
            }
            if($col == 'jenispenghasilan')
            {
                $dat=$optPenghasilan[$dat];
            }
            if($col=='carapembayaran')
            {
                $dat=$optPembayaran[$dat];
            }
            $table .= "<td id='".$col."_".$key."' nowrap>".$dat."</td>";
        }
    
    }
                                

    $table .= "<td id='edit_".$key."'>";
    $table .= "<img src='images/application/application_edit.png' ";
    $table .= "class=resicon  caption='Edit' onclick='passEditHeader(".$key.",".$row['id'].",".$row['jenispph'].",".$row['carapembayaran'].",".$row['jenispenghasilan'].")'></td>";
	
    $table .= "</tr>";
}
$table .= "</tbody>";
$table .= "<tfoot></tfoot></table>";

#== Prep Form Header

# Elements
$els = array();
$els['hid'] = array(
    makeElement('id','hidden')
);
$els[] = array(
    makeElement('jenispph','label',$_SESSION['lang']['jenispph']),
    makeElement('jenispph','select','',array('style'=>'width:150px','onchange'=>'getpenghasilan(this.value)'),$optPph)
);
//print_r($optTipeJenis);
$els[] = array(
    makeElement('carapembayaran','label',$_SESSION['lang']['carapembayaran']),
    makeElement('carapembayaran','select','',array('style'=>'width:150px'),$optPembayaran)
);
$els[] = array(
    makeElement('jenispenghasilan','label',$_SESSION['lang']['jenispenghasilan']),
    makeElement('jenispenghasilan','select','',array('style'=>'width:150px'))
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