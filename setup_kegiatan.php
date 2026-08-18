<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('setup_kegiatan').'</span>');
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zSearch.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/setup_kegiatan.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
#======Select Prep======
# Get Data
$optKlpAkun = array();
if($_SESSION['language']=='EN'){
	$qKlp = selectQuery($dbname,'setup_klpkegiatan','kodeklp,namakelompok1,noakun',"","namakelompok1");
	$resKlp = fetchData($qKlp);
	foreach($resKlp as $row) {
		$optKlpKeg[$row['kodeklp']] = $row['namakelompok1'];
	}
}else{
    $qKlp = selectQuery($dbname,'setup_klpkegiatan','kodeklp,namakelompok,noakun',"","namakelompok");
	$resKlp = fetchData($qKlp);
	foreach($resKlp as $row) {
		$optKlpKeg[$row['kodeklp']] = $row['namakelompok'];
	}
}
foreach($resKlp as $row) {
	$optKlpAkun[$row['kodeklp']] = $row['noakun'];
}

$where = "`detail`=1 and noakun like '".reset($optKlpAkun)."%'";
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$where,'2',true);
$whereOrg = "tipe='HOLDING' and induk is null or induk = ''";
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg,'1');


$optAktif['1']=$_SESSION['lang']['aktif'];
$optAktif['0']=$_SESSION['lang']['tidakaktif'];

$opttipeluasan['0']='Luas bloking';
$opttipeluasan['1']='Luas lahan sudah dilc';
$opttipeluasan['2']='Luas lahan sudah ditanam';

//#======End Select Prep======
#=======Form============
echo "<div style='margin-bottom:30px'>";
$els = array();
# Fields
$els[] = array(
  makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg','select','',array('style'=>'width:250px'),$optOrg)
);
$els[] = array(
  makeElement('kelompok','label',$_SESSION['lang']['kelompok']),
  makeElement('kelompok','select','',array('style'=>'width:250px','onchange'=>'cekAkun()'),$optKlpKeg)
);
$els[] = array(
  makeElement('noakun','label',$_SESSION['lang']['noakun']),
  makeElement('noakun','select','',array('style'=>'width:250px','onchange'=>'cekAkun();ambilkegiatan()'),$optAkun)
);
$els[] = array(
  makeElement('kodekegiatan','label',$_SESSION['lang']['kodekegiatan']),
  makeElement('kodekegiatan','text','',array('style'=>'width:60px','maxlength'=>'9','disabled'=>'disabled'))
);
$els[] = array(
  makeElement('namakegiatan','label',$_SESSION['lang']['namakegiatan']),
  makeElement('namakegiatan','text','',array('style'=>'width:250px','maxlength'=>'80'))
);

$els[] = array(
  makeElement('namakegiatan1','label',$_SESSION['lang']['namakegiatan']."(EN)"),
  makeElement('namakegiatan1','text','',array('style'=>'width:250px','maxlength'=>'80'))
);

$els[] = array(
  makeElement('satuan','label',$_SESSION['lang']['satuan']),
  makeElement('satuan','text','',array('style'=>'width:250px','maxlength'=>'8'))
);

$els[] = array(
  makeElement('pilihanluas','label',$_SESSION['lang']['pilihanluas']),
  makeElement('pilihanluas','select','',array('style'=>'width:250px'),$opttipeluasan)
);

$els[] = array(
  makeElement('status','label',$_SESSION['lang']['status']),
  makeElement('status','select','',array('style'=>'width:250px'),$optAktif)
);

$els[] = array(
  makeElement('premi','label',$_SESSION['lang']['premi']),
  makeElement('premi','checkbox',@$data['premi'],array('style'=>'width:15px'))
);



# Fields
$fieldStr = '##kodeorg##kodekegiatan##namakegiatan##namakegiatan1##kelompok##satuan##noakun##status##premi';
//$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

# Button
$els['btn'] = array(
  genFormBtn($fieldStr,
    'setup_kegiatan',"##kodekegiatan##kodeorg##kelompok##premi",null,null,null,null,
	'##','##kodekegiatan')
);

//$optKlp = makeOption($dbname,'setup_klpkegiatan',"kodeklp,noakun",
//					 "kodeorg in ('".array_keys($optOrg)."')");
$qKlp = selectQuery($dbname,'setup_klpkegiatan','kodeorg,kodeklp,noakun',
					"kodeorg in ('".implode("','",array_keys($optOrg))."')");
$resKlp = fetchData($qKlp);
$optKlp = array();
foreach($resKlp as $row) {
	$optKlp[$row['kodeorg']][$row['kodeklp']] = $row['noakun'];
}

# Generate Field
echo genElTitle($_SESSION['lang']['kegiatan'],$els);
echo "<input id=klpAkun type=hidden value='".json_encode($optKlp)."'>";
echo "</div>";
#=======End Form============

#=======Prepare Table=======

$table = 'setup_kegiatan';

# Extract Data
$query = $owlPDO->query("select * from ".$dbname.".".$table);
$query->setFetchMode(PDO::FETCH_ASSOC);
$jlh_Kolom=$query->columnCount();
$primary = array();
    for($x=0;$x<$jlh_Kolom;$x++){
            $test=$query->getColumnMeta($x);
              $field[] = strtolower($test['name']);
              $fieldStr .= "##".strtolower($test['name']);
                if(is_array($test['flags'])){
                            foreach($test['flags'] as $key=>$val){
                                   $val=strtolower($val);
                                   $pos = strpos($val, 'primary');
                                   if ($pos !== false) {
                                        $primary[]=$test['name'];
                                        @$primaryStr .= "##".strtolower($test['name']);
                                   }
                            }
                }
    } 

$fForm = $field;

# Rearrange Result and Extract Values
$result = array();
while($bar=$query->fetch()) {
  $result[] = $bar;
}

CLOSE_BOX();
OPEN_BOX();
#======Create Table======
# Create Print
$tables = "<fieldset><legend><b>".$_SESSION['lang']['list']." : ".$table."</b></legend>";
$tables .= "<img src='images/pdf.jpg' title='PDF Format'
  style='width:20px;height:20px;cursor:pointer' onclick=\"masterPDF('".$table."','*',null,'setup_slave_kegiatan_pdf',event)\">&nbsp;";
$tables .= "<img src='images/printer.png' title='Print Page'
  style='width:20px;height:20px;cursor:pointer' onclick='javascript:print()'>";

# Start Table
$tables .= "<div style='height:300px;overflow:auto;'>";
$tables .= "<table id='masterTable' class='sortable' cellspacing='1' border='0' >";

# Create Header
$tables .= "<thead><tr class='rowheader'>";
foreach($field as $hName) {
  $tables .= "<td>".$_SESSION['lang'][$hName]."</td>";
}

$tables .= "<td colspan='3'></td>";
$tables .= "</tr></thead>";

# Iterate Content
$tables .= "<tbody id='mTabBody'>";
$i=0;
foreach($result as $row) {
  $tables .= "<tr id='tr_".$i."' class='rowcontent'>";
  $tmpVal = "";
  $tmpKey = "";
  $noakun = $row['noakun'];
  $j=0;
  foreach($row as $b=>$c) {
    # For Tipe Tanggal
    $tmpC = explode("-",$c);
    if(count($tmpC)==3) {
      $c = $tmpC[2]."-".$tmpC[1]."-".$tmpC[0];
    }
    
    $tables .= "<td id='".$fForm[$j]."_".$i."' value='".$c."'>".$c."</td>";
    $tmpVal .= "##".$c;
    if(in_array($fForm[$j],$primary)) {
		$tmpKey .= "##".$c;
    }
    $j++;
  }
  # Edit, Delete Row
  $tables .= "<td><img id='editRow".$i."' title='Edit' onclick=\"editRow(".$i.",'".$fieldStr."','".$tmpVal."');cekAkun('".$noakun."')\"
    class='zImgBtn' src='images/001_45.png' /></td>";
  $tables .= "<td><img id='delRow".$i."' title='Hapus' onclick=\"delRow(".$i.",'".$primaryStr."','".$tmpKey."',null,'".$table."')\"
    class='zImgBtn' src='images/delete_32.png' /></td>";
  $tables .= "<td><img id='norma".$i."' title='Material dan Master Premi' onclick=\"showNorma(".$i.",'".$primaryStr."##namakegiatan##satuan',event)\"
    class='zImgBtn' src='images/application/application_view_xp.png' /></td>";
  $tables .= "</tr>";
  $i++;
}
$tables .= "</tbody>";

# Create Footer
$tables .= "<tfoot></tfoot>";

# End Table
$tables .= "</table></div></fieldset>";

#=======End Prepare Table=======

# Display Table
echo "<div style='clear:both;float:left'>";
echo $tables;
#echo masterTable($dbname,'setup_kegiatan',"*",array(),array(),array(),array(),'setup_slave_kegiatan_pdf');
echo "</div>";

CLOSE_BOX();
echo close_body();
?>