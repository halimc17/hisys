<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

#=== Start ===
echo open_body();
?>
<!-- Includes -->
<script language=javascript1.2 src=js/zTools.js></script>
<script language=javascript1.2 src=js/keu_3tutupbulan.js?ver=1.5></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
#====== Controller ======
# Options
$optOrg = getOrgDetail(9);
$optOrg2=array();
$optPeriod= array(""=>$_SESSION['lang']['pilihdata']);
$optOrg2['']=$_SESSION['lang']['pilihdata'];
foreach ($optOrg as $key => $val) {
	$optTipe=makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$key."'");
	if($optTipe[$key]!='HOLDING'){
		$rPrdAkn=array();
		$sPrdAkn="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$key."' and tutupbuku=0 order by periode desc limit 1";
		$rPrdAkn=fetchData($sPrdAkn);
		if(!empty($rPrdAkn)){
			$optOrg2[$key]=$val;
		}
	}
}
# Fields
$els = array();
$els[] = array(
  makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg','selectsearch','',array('style'=>'width:200px','onchange'=>'changeperiode(this)'),$optOrg2)
);
$els[] = array(
  makeElement('periode','label',$_SESSION['lang']['periode']),
  makeElement('periode','select','',array('style'=>'width:200px'),$optPeriod)
);

# Button
$els['btn'] = array(
  makeElement('btnList','button',$_SESSION['lang']['tutupbuku'],
    array('onclick'=>'tutupBuku()'))
);

#====== View ======
# Menu
include('master_mainMenu.php');

# Form
OPEN_BOX('','<span class=judul>'.getMenu('keu_3tutupbulan').'</span><br>');
echo genElTitle($_SESSION['lang']['form'],$els);
CLOSE_BOX();
#=== End ===
close_body();
?>