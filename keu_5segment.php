<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
#=======Form============
echo "<div style='margin-bottom:30px'>";
$els = array();
# Fields
$els[] = array(
	makeElement('kodesegment','label',$_SESSION['lang']['kodesegment']),
	makeElement('kodesegment','text','',array('style'=>'width:70px','maxlength'=>10))
);
$els[] = array(
	makeElement('namasegment','label',$_SESSION['lang']['namasegment']),
	makeElement('namasegment','text','',array('style'=>'width:300px','maxlength'=>'70'))
);

# Fields
$fieldStr = '##kodesegment##namasegment'; // Field yang akan diambil
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

$fieldId = '##kodesegment'; // Field yang menjadi ID (tidak boleh diubah)

# Button
$els['btn'] = array(
	genFormBtnConfig($fieldStr,'keu_5segment',$fieldId)
);

# Generate Field
echo genElTitle($_SESSION['lang']['segment'],$els);
echo "</div>";
#=======End Form============

#=======Table============
# Display Table
echo "<div style='clear:both;float:left'>";
$configTable = array(
	'column' 		=> $fieldArr, 					// Kolom yang akan ditampilkan, jika tidak diassign kolom pada tabel tidak ada
	'freezeField' 	=> 'kodesegment', 				// Kolom yang freeze ketika edit
	'listName' 		=> $_SESSION['lang']['segment']	// Custom String disebelah kanan 'List Data: '
);
echo masterTableConfig($dbname,'keu_5segment',$configTable);
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>