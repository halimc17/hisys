<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_consolbyyproduksi').'</span><br>');
?>

<script language="javascript" src="js/zComment.js?ver=<?php echo time(); ?>"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/kebun_consolbyyproduksi.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<link rel=stylesheet type=text/css href=style/zComment.css>
<?
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select * from ".$dbname.".bgt_regional_assignment";
$res = fetchdata($str);
foreach($res as $bar){
	$myregional="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeunit']){
		$myregional=$bar['subregional'];
	}
	if(getNamaOrg($bar['kodeunit'],'tipe')=='KEBUN'){		
		$datareg[$bar['subregional']]=$bar['subregional'];
	}
}
foreach($datareg as $region){
	$s="";
	if($myregional==$region){
		$s="selected";
	}
    $optPT.="<option value=" . $region . " ".$s.">".$region."</option>";
}

// $str="select * from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."' and inti=1";
// $res = fetchdata($str);
// foreach($res as $bar){
// 	$s="";
// 	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     //$optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

$str="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res = fetchdata($str);
foreach($res as $bar){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrtipe=array("group"=>"Per Job Group","code"=>"Per Job Code","kegiatan"=>"Per Kode Kegiatan");
foreach($arrtipe as $res => $bar){
	@$opttipe.="<option value=".$res.">".$bar."</option>";
}

$arr1 = "##regional##kdorg##prd##tipe##depre";
echo"<div id=tableheader>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['regional'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=regional onchange=getUnitRegional(this,'kdorg','divisi','".$_SESSION['lang']['all']."','1')  style=\"width:164px;\">" .$optPT . "</select></td>
               
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:164px;\">" . $optper . "</select></td>
                
                    <td>" . $_SESSION['lang']['tipe'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tipe style=\"width:164px;\">" . $opttipe . "</select></td>
                </tr>
				<tr>
                    <td>Depresiasi</td>
                    <td>:</td>
                    <td><select class=select2 id=depre style=\"width:164px;\"><option value='0'>Sembunyikan</option><option value='1'>Tampilkan</option></select></td>
                
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=\"zPreview('kebun_slave_consolbyyproduksi','" . $arr1 . "','printContainer');showheader();\" class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_consolbyyproduksi.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</div>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
	
// echo"<div style=clear:both></div>
// <div id='both_report'>
	// <div id='head_tableboth' align=right>
		// <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			// <img title='Full Screen' class='zImgBtn' src='images/full-screen.png'>
		// </a>
	// </div>
	// <div style=clear:both></div>
	// <div id='printContainer'></div>
// </div>";
echo"<div id='printContainer' class='table-scroll' style='overflow:auto;height:73vh;'></div>";
// echo"<div id='printContainer'></div>";

CLOSE_BOX();
echo close_body();
?>