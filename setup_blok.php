<?//@Copy nangkoelframework
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zConfig.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('Setup Block').'</span>');
?>
<script language=javascript src='js/zMaster.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/setup_blok.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
#======Select Prep======
# Get Data
// $tmpKlsTanah = readLst("./config/kelastanah.lst");
// $optKlsTanah = lst2opt($tmpKlsTanah,0,1);
$optKlsTanah = makeOption($dbname,'setup_kelaslahan','kode,nama', 'aktif=1','2');
$tmpJenisTanah = readLst("./config/jenistanah.lst");
$optJenisTanah = lst2opt($tmpJenisTanah,0,1);
$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
$optOrg = array();
$optMonth = optionMonth('I','long');
$optBlokStat = getEnum($dbname,'setup_blok','statusblok');
$optIP = getEnum($dbname,'setup_blok','intiplasma');
$optIP['I'] = 'Inti';
$optIP['P'] = 'Plasma';
$optBuahKecil = array('0'=>'Basis Buah Besar','1'=>'Basis Buah Kecil',''=>'-');

#======End Select Prep======

#=======Search==============
# Get Options
if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
  $tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN'");
} elseif($_SESSION['empl']['tipelokasitugas']=='KEBUN') {
  $tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
    "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
} else {
  $tmpOpt = getOrgBelow($dbname,$_SESSION['empl']['lokasitugas'],false,'kebunonly');
}
$sKebun = array(''=>'');
foreach($tmpOpt as $key=>$row) {
  $sKebun[$key] = $row;
}
$optBibit = makeOption($dbname,'setup_jenisbibit','jenisbibit,jenisbibit');
//$getKlsPhn = getEnum($dbname,'kebun_5kelaspohon','kelas');
$strKlsPhn = $owlPDO->query("select * from ".$dbname.".kebun_5kelaspohon");
$strKlsPhn->setFetchMode(PDO::FETCH_ASSOC);
while($rowKlsPhn=$strKlsPhn->fetch())
{
	$optKlsPohon[$rowKlsPhn['kelas']]=$rowKlsPhn['kelas'].' - '.$rowKlsPhn['nama'];
}

# Create Elements
$searchEls = $_SESSION['lang']['kebun']."  : ";
$searchEls .= makeElement('sKebun','select','',
  array('onchange'=>"getAfdeling(this,'sAfdeling')",'style'=>'width:200px'),$sKebun)." ";
$searchEls .= $_SESSION['lang']['afdeling']." : ";
$searchEls .= makeElement('sAfdeling','select','',array('style'=>'width:200px'),array())." ";
$searchEls .= makeElement('searchIt','button',$_SESSION['lang']['find'],array('onclick'=>'showData()'))." ";

# Render Search Element
echo "<fieldset id='search' style='margin-bottom:10px;width:600px;clear:both'>";
echo "<legend><b>".$_SESSION['lang']['searchdata']."</b></legend>";
echo $searchEls;
echo "</fieldset>";
#=======End Search==========

#=======Form============
echo "<div id='formBlok' style='display:none;margin-bottom:10px;clear:both'>";
$els = array();
# Fields
$els[] = array(
  makeElement('kodeorg','label',$_SESSION['lang']['blok']),
  makeElement('kodeorg','select','',array('style'=>'width:105px','onchange'=>'isiinduk()'),$optOrg)
);

$els[] = array(
  makeElement('indukblok','label',$_SESSION['lang']['blok'].' '.$_SESSION['lang']['induk']),
  makeElement('indukblok','text','',array('style'=>'width:100px','maxlength'=>'9','disabled'=>true))
);

$els[] = array(
  makeElement('tahuntanam','label',$_SESSION['lang']['tahuntanam']),
  makeElement('tahuntanam','textnumber','',array('style'=>'width:100px','maxlength'=>'6')).
  makeElement('tahuntanamCurr','hidden','')
);
// $els[] = array(
  // makeElement('kelaspohon','label',$_SESSION['lang']['kelaspohon']),
  // makeElement('kelaspohon','select','',array('style'=>'width:105px'),$optKlsPohon)
// );
$els[] = array(
  makeElement('luasareaproduktif','label',$_SESSION['lang']['luasareaproduktif']),
  makeElement('luasareaproduktif','textnumber','0',array('style'=>'width:100px','maxlength'=>'30',
    'onkeypress'=>'return angka_doang(event)'))." Ha"
);
$els[] = array(
  makeElement('luasareanonproduktif','label',$_SESSION['lang']['luasareanonproduktif']),
  makeElement('luasareanonproduktif','textnumber','0',array('style'=>'width:100px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','readonly'=>'readonly'))." Ha"
);
$els[] = array(
  makeElement('jumlahpokok','label',$_SESSION['lang']['jumlahpokok']),
  makeElement('jumlahpokok','textnumber','0',array('style'=>'width:100px','maxlength'=>'10','onkeypress'=>'return angka_doang(event)'))
);
$els[] = array(
  makeElement('statusblok','label',$_SESSION['lang']['statusblok']),
  makeElement('statusblok','select','',array('style'=>'width:127px'),$optBlokStat)
);
$els[] = array(
  makeElement('tahunmulaipanen','label',$_SESSION['lang']['mulaipanen']),
  makeElement('bulanmulaipanen','select','',array('style'=>'width:70px'),$optMonth)." / ".
  makeElement('tahunmulaipanen','textnumber','',array('style'=>'width:40px','maxlength'=>'4','onkeypress'=>'return angka_doang(event)'))
);
$els[] = array(
  makeElement('kodetanah','label',$_SESSION['lang']['kodetanah']),
  makeElement('kodetanah','select','',array('style'=>'width:127px'),$optJenisTanah)
);
$els[] = array(
  makeElement('klasifikasitanah','label',$_SESSION['lang']['klasifikasitanah']),
  makeElement('klasifikasitanah','select','',array('style'=>'width:127px'),$optKlsTanah)
);
$els[] = array(
  makeElement('topografi','label',$_SESSION['lang']['topografi']),
  makeElement('topografi','select','',array('style'=>'width:127px'),$optTopografi)
);
$els[] = array(
  makeElement('intiplasma','label',$_SESSION['lang']['intiplasma']),
  makeElement('intiplasma','select','',array('style'=>'width:127px'),$optIP)
);
$els[] = array(
  makeElement('jenisbibit','label',$_SESSION['lang']['jenisbibit']),
  makeElement('jenisbibit','select','',array('style'=>'width:85px'),$optBibit)
);
// $els[] = array(
  // makeElement('tanggalpengakuan','label',''),
  // makeElement('tanggalpengakuan','text','',array('style'=>'width:80px;display:none',
  // 'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)'))
// );
$els[] = array(
  makeElement('buahkecil','label',"Basis Buah"),
  makeElement('buahkecil','select','',array('style'=>'width:127px'),$optBuahKecil)
);
$els[] = array(
  makeElement('cadangan','label',$_SESSION['lang']['cadangan']),
  makeElement('cadangan','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('okupasi','label',$_SESSION['lang']['okupasi']),
  makeElement('okupasi','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('rendahan','label',$_SESSION['lang']['rendahan']),
  makeElement('rendahan','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('sungai','label',$_SESSION['lang']['sungai']),
  makeElement('sungai','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('rumah','label',$_SESSION['lang']['rumah']),
  makeElement('rumah','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('kantor','label',$_SESSION['lang']['kantor']),
  makeElement('kantor','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('pabrik','label',$_SESSION['lang']['pabrik']),
  makeElement('pabrik','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('jalan','label',$_SESSION['lang']['jalan']),
  makeElement('jalan','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('kolam','label',$_SESSION['lang']['kolam']),
  makeElement('kolam','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);
$els[] = array(
  makeElement('umum','label',$_SESSION['lang']['umum']),
  makeElement('umum','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)','onblur'=>'itungUnplan()'))." Ha"
);

$els[] = array(
  makeElement('arealberbatu','label',$_SESSION['lang']['arealberbatu']),
  makeElement('arealberbatu','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)'))." Ha"
);

$els[] = array(
  makeElement('konservasi','label',$_SESSION['lang']['konservasi']),
  makeElement('konservasi','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)'))." Ha"
);

$els[] = array(
  makeElement('enclave','label',$_SESSION['lang']['enclave']),
  makeElement('enclave','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)'))." Ha"
);

$els[] = array(
  makeElement('lc','label',$_SESSION['lang']['lc']),
  makeElement('lc','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)'))." Ha"
);

$els[] = array(
  makeElement('luasbloking','label',$_SESSION['lang']['luasbloking']),
  makeElement('luasbloking','textnumber','0',array('style'=>'width:80px','maxlength'=>'10'
    ,'onkeypress'=>'return angka_doang(event)'))." Ha"
);

# Fields
$fieldStr = '##kodeorg##indukblok##tahuntanam##tahuntanamCurr##luasareaproduktif##luasareanonproduktif';
$fieldStr .= '##jumlahpokok##statusblok##bulanmulaipanen##tahunmulaipanen';
$fieldStr .= '##kodetanah##klasifikasitanah##topografi##intiplasma##jenisbibit##buahkecil';
$fieldStr .= '##cadangan##okupasi##rendahan##sungai##rumah##kantor##pabrik##jalan##kolam##umum##arealberbatu##konservasi##enclave##lc##luasbloking';
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

# Button
$els['btn'] = array(
  genFormBtn($fieldStr,
    'setup_blok',"##kodeorg##tahuntanam",'setup_slave_blok_add',null,null,
    'setup_slave_blok_edit','##tahuntanamCurr')
);

# Generate Field
echo genElementMultiDim('Block',$els,4);
echo "</div>";
#=======End Form============

#=======Table===============
# Display Table
echo "<div id='blokTable' style='float:left;clear:both;'>";
#echo masterTable($dbname,'setup_blok',"*",array(),array(),array(),array(),'setup_slave_blok_pdf');
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>