<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script languange=javascript1.2>
notiftanggalakutansi="<?php echo $_SESSION['lang']['notiftanggalakutansi']; ?>";
notifandayakin="<?php echo $_SESSION['lang']['notifandayakin']; ?>";
notifremovejurnalheader="<?php echo $_SESSION['lang']['notifremovejurnalheader']; ?>";
notiftanggal="<?php echo $_SESSION['lang']['notiftanggal']; ?>";
notifperiodeadjusment="<?php echo $_SESSION['lang']['notifperiodeadjusment']; ?>";
</script>
<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script languange=javascript1.2 src='js/keu_jurnal_audit.js'></script>
<script languange=javascript1.2>
    //zGrid.column.push(1);
    theGrid[1].addColumn('nourut','<?php echo $_SESSION['lang']['nourut']?>','textnum',0,'R',10);
    theGrid[1].addColumn('noakun','<?php echo $_SESSION['lang']['noakun']?>','text','-','L',14);
    theGrid[1].addColumn('keterangan','<?php echo $_SESSION['lang']['keterangan']?>','text','-','L',50);
    theGrid[1].addPrimColumn('nojurnal','nojurnal');
    theGrid[1].target = "keu_slave_jurnal_manage_detail";
</script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?
OPEN_BOX('','<span class=judul>'.strtoupper('AFTER AUDIT ADJUSTMENT').'</span>');
#== Prep Periode Akuntansi
$org = $_SESSION['org'];
$period = $_SESSION['org']['period'];
if($period=='') {
    echo "Warning : ".$_SESSION['lang']['notifperiodeakunting'];
    CLOSE_BOX();
    echo close_body();
    exit;
}

#== Get Journal Header
$where = " YEAR(tanggal)>=".intval(date('Y')-1)." and month(tanggal)=12 and day(tanggal)=31".
    " and substr(nojurnal,10,4)='".$_SESSION['empl']['lokasitugas'].
    "' and kodejurnal='M'".
    " and revisi!=0";
$query = selectQuery($dbname,'keu_jurnalht',"kodejurnal,nojurnal,tanggal,noreferensi,matauang,totaldebet,totalkredit,revisi",$where);
$resTab = fetchData($query);

#== Prep List Header
$header = array($_SESSION['lang']['kodeabs'],$_SESSION['lang']['no'],$_SESSION['lang']['tanggal'],$_SESSION['lang']['noreferensi'],$_SESSION['lang']['matauang'],$_SESSION['lang']['debet'],$_SESSION['lang']['kredit'],$_SESSION['lang']['revisi']);

$table = "<table id='listHeader' class='sortable'>";
$table .= "<thead><tr class='rowheader'>";
$table .= "<td colspan='2'>".$_SESSION['lang']['action']."</td>";
foreach($header as $head) {
    $table .= "<td>".$head."</td>";
}
$table .= "</tr></thead>";
$table .= "<tbody id='bodyListHeader'>";
foreach($resTab as $key=>$row) {
    $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
    $table .= "<td id='pdf_".$key."'><img src='images/".$_SESSION['theme']."/pdf.jpg' ";
    $table .= "class='zImgBtn' onclick='detailPDF(".$key.",event)'></td>";
    $table .= "<td id='delHead_".$key."'>";
    $table .= "<img src='images/".$_SESSION['theme']."/delete.png' ";
    $table .= "class='zImgBtn' onclick='delHead(".$key.")'></td>";
    
    foreach($row as $col=>$dat) {
        if($col=='tanggal') {
            $dat = tanggalnormal($dat);
        }
        $dtplus=0;
        $dtmin=0;
        $krngan=0;
        $sData=$owlPDO->query("select distinct sum(jumlah) as plus from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."' and jumlah>0");
        //exit("Error:".$sData);
        $sData->setFetchMode(PDO::FETCH_ASSOC);
        $rData= $sData->fetch();
        $dtplus=$rData['plus'];
        
        $sData=$owlPDO->query("select distinct sum(jumlah) as min from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."' and jumlah<0");
        //exit("Error:".$sData);
        $sData->setFetchMode(PDO::FETCH_ASSOC);
        $rData= $sData->fetch();
        $dtmin=$rData['min']*(-1);
        
        $sCekData=$owlPDO->query("select sum(jumlah) as selisih from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."'");
        $sCekData->setFetchMode(PDO::FETCH_ASSOC);
        $rCekData= $sCekData->fetch();
      
        $dbgr="";
        if(intval($rCekData['selisih'])!=0)
        {
         $dbgr="bgcolor='red'";
        }
        if($col=='totaldebet')
        {
            $table .= "<td id='".$col."_".$key."' onclick='passEditHeader(".$key.")' align=right ".$dbgr." title='".$_SESSION['lang']['selisih']." ".intval($rCekData['selisih'])."'>".number_format($dtplus,0)."</td>";
        }
        elseif($col=='totalkredit')
        {
            $table .= "<td id='".$col."_".$key."' onclick='passEditHeader(".$key.")' align=right ".$dbgr." title='".$_SESSION['lang']['selisih']." ".intval($rCekData['selisih'])."'>".number_format($dtmin,0)."</td>";
        }
        else
        {
            $table .= "<td id='".$col."_".$key."' onclick='passEditHeader(".$key.")' ".$dbgr.">".$dat."</td>";
        }
        
    }
    $table .= "</tr>";
}
$table .= "</tbody>";
$table .= "<tfoot></tfoot></table>";

#== Prep Form Header
# Options
$optCurr = makeOption($dbname,'setup_matauang','kode,matauang',"kode='IDR'");
$optJCode = makeOption($dbname,'keu_5kelompokjurnal','kodekelompok,keterangan',
    "kodeorg='".$org['kodeorganisasi']."' and kodekelompok='M'");
for ($i = 1; $i <= 5; $i++) {
    $optRev[$i]=$i;
}

# Elements
$els = array();
$els[] = array(
    makeElement('nojurnal','label',$_SESSION['lang']['nojurnal']),
    makeElement('nojurnal','text','',array('style'=>'width:120px',
        'readonly'=>'readonly','disabled'=>'disabled'))." *)  <i>Journal Number Automatic</i>"
);
$els[] = array(
    makeElement('kodejurnal','label',$_SESSION['lang']['kodejurnal']),
    makeElement('kodejurnal','select','',array('style'=>'width:150px',
        'disabled'=>'disabled'),$optJCode)
);
$els[] = array(
    makeElement('tanggal','label',$_SESSION['lang']['tanggal']),
    makeElement('tanggal','text','',array('style'=>'width:80px','readonly'=>'readonly',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
$els[] = array(
    makeElement('noreferensi','label',$_SESSION['lang']['nodok']),
    makeElement('noreferensi','text','',array('style'=>'width:80px','maxlength'=>'20',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
$els[] = array(
    makeElement('matauang','label',$_SESSION['lang']['matauang']),
    makeElement('matauang','select','',array('style'=>'width:70px',
        'disabled'=>'disabled'),$optCurr)
);
//$els[] = array(
//    makeElement('revisi','label',$_SESSION['lang']['revisi']),
//    makeElement('revisi','textnumber','1',array('style'=>'width:80px','maxlength'=>'2',
//        'disabled'=>'disabled'))
//);
$els[] = array(
    makeElement('revisi','label',$_SESSION['lang']['revisi']),
    makeElement('revisi','select','',array('style'=>'width:70px',
        'disabled'=>'disabled'),$optRev)
);

$els['btn'] = array(
    makeElement('saveButton','button',$_SESSION['lang']['save'],
        array('disabled'=>'disabled'))
);
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
#===== Show =======


# Active Form
echo makeElement('startPeriod','hidden',$_SESSION['org']['period']['start']);
echo makeElement('endPeriod','hidden',$_SESSION['org']['period']['end']);
echo "<fieldset id='fieldFormHeader' style='clear:right;min-height:200px;'>";
echo "<legend><b>Form Header</b></legend>";
echo "<img id='addHeadBtn' src='images/".$_SESSION['theme']."/plus.png' style='cursor:pointer' onclick=\"addModeForm('".$_SESSION['theme']."')\" />";
echo genElement($els);
echo "</fieldset>";

# Detail List
echo "<fieldset id='fieldListDetail' style='clear:both'>";
echo "<legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['detail']."</b></legend>";
echo "<div id='divDetail'></div>";
echo "</fieldset>";

# Table
echo "<fieldset id='fieldListTable' style='float:left;clear:left;min-height:200px;height:85%;overflow:auto'>";
echo "<legend><b> Header List</b></legend>";
echo $table;
echo "</fieldset>";
CLOSE_BOX();
echo close_body();
?>