<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script languange=javascript1.2 src='js/lgl_checklist.js'></script>

<?


OPEN_BOX('','<span class=judul>'.strtoupper("Checklist").'</span>');
#== Prep Option & Query

$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);

$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","NOT (tipe='PT' or tipe='HOLDING') and CHAR_LENGTH(kodeorganisasi)=4",'2',true);
$optjenis = makeOption($dbname,"lgl_5checklist","kode,jenis","status=1");
//$optMinggu = array(''=>'','1'=>'Ke-1','2'=>'Ke-2','3'=>'Ke-3','4'=>'Ke-4');

#== Prep List


$tblCr="<table cellspacing=1 border=0>";
$tblCr.="<tr valign=moiddle>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=adddataform()>";
$tblCr.="<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=loadData()>";
$tblCr.="<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>";

#=== prep table

$header = array("No. Transaksi","Nama Organisasi","Tanggal Mulai","Tanggal Selesai","Jenis Chekclist");
$table ="<fieldset id='fieldForm' style='min-width:500px; clear:right;min-height:auto;'>";
$table .="<legend>".$_SESSION['lang']['list']."</legend>";
$table .= "<table cellspacing='1' border='0' class='sortable'>";
$table .= "<thead><tr class='rowheader'>";
foreach($header as $head) {
    $table .= "<td>".$head."</td>";
}
$table .= "<td>*</td>";
$table .= "</tr></thead>";
$table .= "<tbody id='container'>";
$table .= "<script>loadData()</script>";
$table .= "</tbody>";
$table .= "<tfoot id='containerfoot'>";
$table .= "<script>loadData()</script>";
$table .= "</tfoot>";
$table .= "</table>";
$table .= "</fieldset>";


#== Prep Form 

# Elements
$elscari = array();
$elscari[] = array(
    makeElement('kodeorgcr','label','Kode Organisasi'),
    makeElement(':','label',':'),
    makeElement('kodeorgcr','select','',array('style'=>'width:190px'),$optOrg),
    makeElement('caricheklist','button',$_SESSION['lang']['find'],array('onclick'=>"loadData('cari')"))
);

$elshead = array();
$elshead[] = array(
    makeElement('notransaksi','hidden','',array('style'=>'width:190px'))
);
$elshead[] = array(
    makeElement('kodeorg','label','Kode Organisasi'),
    makeElement(':','label',':'),
    makeElement('kodeorg','select','',array('style'=>'width:190px'),$optOrg)
);
$elshead[] = array(
    makeElement('tanggalmulai','label','Tanggal Mulai'),
    makeElement(':','label',':'),
    makeElement('tanggalmulai','tanggal','',array('style'=>'width:100px'))
);
$elshead[] = array(
    makeElement('tanggalselesai','label','Tanggal Selesai'),
    makeElement(':','label',':'),
    makeElement('tanggalselesai','tanggal','',array('style'=>'width:100px'))
);
$elshead[] = array(
    makeElement('jenis','label','Jenis Checklist'),
    makeElement(':','label',':'),
    makeElement('jenis','select','',array('style'=>'width:100px'),$optjenis)
);
$elsheadbutton['btn'] = array(
    makeElement('saveButton','button',$_SESSION['lang']['save'],array('onclick'=>'checkHeader()')),
    makeElement('clearButton','button',$_SESSION['lang']['clear'],array('onclick'=>'clearDetail()'))
);

#===== Show =======
echo "<div id=headerjudul>";
echo $tblCr;
echo "<td><fieldset id='formcari' style='width:500px; clear:right;min-height:auto;'>";
echo "<legend>".$_SESSION['lang']['find']."</legend>";
echo genElement($elscari);
echo "</fieldset></td></table>";
echo "</div>";

CLOSE_BOX();
# Active Form


echo "<div id=header style='display:none'>";
OPEN_BOX();
echo "<fieldset id='formheader' style='float:left'>";
echo "<legend>".$_SESSION['lang']['header']."</legend>";
echo genElement($elshead);
echo "<div id=hbutton>";
echo genElement($elsheadbutton);
echo "</div>";
echo "</fieldset>";
CLOSE_BOX();
echo "</div>";


echo "<div id=Detail style='display:none'>";
OPEN_BOX();
echo "<div id=detailform >";
echo "</div>";
CLOSE_BOX();
echo "</div>";

echo "<div id=listtabledetail style='display:none'>";
OPEN_BOX();
echo "<div id=tabledetail >";
echo "</div>";
CLOSE_BOX();
echo "</div>";

# Table
echo "<div id=containerx>";
OPEN_BOX();
echo $table;
CLOSE_BOX();
echo "</div>";

echo close_body();
?>