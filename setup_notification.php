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
<script languange=javascript1.2 src='js/setup_notification.js'></script>
<!-- <script languange=javascript1.2>
    //zGrid.column.push(1);
    theGrid[1].addColumn('kodejenis','<?php echo $_SESSION['lang']['kode'].' '.$_SESSION['lang']['jenis']?>','text','-','L',14);
    theGrid[1].addPrimColumn('id','id');
    theGrid[1].target = "keu_slave_jurnal_manage_detail";
</script> -->

<link rel=stylesheet type=text/css href='style/zTable.css'>
<?

OPEN_BOX('','<span class=judul>'.getMenu('setup_notification').'</span>');

//Get Tipe Jenis

$optTipeJenis = getEnum($dbname,"setup_notification_ht","tipejenis");


//Get Status
$optStat=array("1"=>"Aktif","0"=>"Tidak Aktif");


#== Get Journal Header

$query = selectQuery($dbname,"setup_notification_ht","*","","kodejenis asc");
$resTab = fetchData($query);
#== Prep List Header
$header = array($_SESSION['lang']['nourut'],$_SESSION['lang']['kode']." ".$_SESSION['lang']['jenis'],$_SESSION['lang']['tipe']." ".$_SESSION['lang']['jenis'],$_SESSION['lang']['nama']." ".$_SESSION['lang']['jenis'],$_SESSION['lang']['sumber']." ".$_SESSION['lang']['jenis'],$_SESSION['lang']['status']);

$table = "<table id='listHeader' class='sortable'>";
$table .= "<thead><tr class='rowheader'>";
$table .= "<td colspan='2'>".$_SESSION['lang']['action']."</td>";
foreach($header as $head) {
    $table .= "<td>".$head."</td>";
}
$table .= "</tr></thead>";
$table .= "<tbody id='bodyListHeader'>";
foreach($resTab as $key=>$row) {
	@$no++;
    $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
    $table .= "<td id='delHead_".$key."' colspan='2'>";
    $table .= "<img src='images/".$_SESSION['theme']."/delete.png' ";
    $table .= "class='zImgBtn' onclick='delHead(".$key.")'></td>";
    foreach($row as $col=>$dat) {
		
		if($col=='id'){
			$dat=$no;
		}
		
                                if($col=='status') {
                                        if($dat == 1){
                                            $dat = "Aktif";
                                        }
                                        else{
                                            $dat = "Tidak Aktif";
                                        }
                                }
                                
						            $table .= "<td id='".$col."_".$key."' onclick='passEditHeader(".$key.")'>".$dat."</td>";
                                

                        }
	
    $table .= "</tr>";
}
$table .= "</tbody>";
$table .= "<tfoot></tfoot></table>";

#== Prep Form Header

# Elements
$els = array();
$els[] = array(
    makeElement('kodejenis','label',$_SESSION['lang']['kode']." ".$_SESSION['lang']['jenis']),
    makeElement('kodejenis','text','',array('style'=>'width:145px','maxlength'=>'10',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
//print_r($optTipeJenis);
$els[] = array(
    makeElement('tipejenis','label',$_SESSION['lang']['tipe']." ".$_SESSION['lang']['jenis']),
    makeElement('tipejenis','select','',array('style'=>'width:150px'),$optTipeJenis)
);
$els[] = array(
    makeElement('namajenis','label',$_SESSION['lang']['nama']." ".$_SESSION['lang']['jenis']),
    makeElement('namajenis','text','',array('style'=>'width:145px','maxlength'=>'255',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
$els[] = array(
    makeElement('sumberjenis','label',$_SESSION['lang']['sumber']." ".$_SESSION['lang']['jenis']),
    makeElement('sumberjenis','text','',array('style'=>'width:145px','maxlength'=>'255',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
$els[] = array(
    makeElement('status','label',$_SESSION['lang']['status']),
    makeElement('status','select','',array('style'=>'width:150px'),$optStat)
);

// echo"<pre>";
// print_r($els);
// echo"</pre>";

$els['btn'] = array(
    makeElement('saveButton','button',$_SESSION['lang']['save'],
        array('disabled'=>'disabled'))
);
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
#===== Show =======


# Active Form
echo "<fieldset id='fieldFormHeader' style='clear:right;min-height:auto;'>";
echo "<legend><b>New Header</b></span></legend>";
echo "<img id='addHeadBtn' src='images/".$_SESSION['theme']."/plus.png' style='cursor:pointer;height:17px;' title='Create new header notification' onclick=\"addModeForm('".$_SESSION['theme']."')\" />";
echo genElement($els);
echo "</fieldset>";
# Detail List
echo "<fieldset id='fieldListDetail' style='clear:both;'>";
echo "<legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['detail']."</b></legend>";
echo "<div id='divDetail'></div>";
echo "</fieldset>";

# Table
echo "<fieldset id='fieldListTable' clear:left;min-height:200px;height:100%;overflow:auto'>";
echo "<legend><b>Header List</b></legend>";
//echo "<img id='addHeadBtn' src='images/".$_SESSION['theme']."/plus.png' style='cursor:pointer' onclick=\"addModeForm()\" />".
//    "<a style='cursor:pointer' onclick=\"addModeForm('".$_SESSION['theme']."')\">Tambah Header</a>";
echo"<div style='height:350px;width:auto;overflow:auto;'>";
echo $table;
echo "</div>";
echo "</fieldset>";

CLOSE_BOX();
echo close_body();
?>