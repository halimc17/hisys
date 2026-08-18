<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/kebun_curahhujanperpt.js?v=<?php echo time(); ?>'></script>
<!-- <script src='https://cdn.jsdelivr.net/npm/chart.js'></script> -->
<script src='js_chart/Chart.min.js'></script>

<?php

OPEN_BOX('','<span class=judul>'.getMenu('kebun_curahhujanperpt').'</span><br>');

## GET UNIT
$optUnit='';
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	$opttipeorg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$key."'");

	if($opttipeorg[$key] == 'KEBUN'){
		if($key==$_SESSION['empl']['lokasitugas']){
			$optUnit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
			$unit=$key;
		}else{
			$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
		}
		$n=$d;
		if($d!=$n){			
			$optUnit.="</optgroup>";
		}
	}
}

##GET PT
$optPt="";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'PT' and length(kodeorganisasi) = 3 order by kodeorganisasi";;
$res=fetchdata($str);
foreach($res as $val){
	$optPt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}	

## GET PERIODE
$optPeriode="";
$str="select DATE_FORMAT(tanggal, '%Y-%m')  as periode from ".$dbname.".kebun_curahhujan group by periode order by periode desc";
$res=fetchdata($str);
foreach($res as $val){
	$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}

$hfrm[0] = "HARIAN";
$hfrm[1] = "BULANAN";

$frm[0] = "
<fieldset style='float:left;'>
    <legend>HARIAN</legend>
    <table cellspacing=1 cellpadding=2>
        <tr>
            <td>".$_SESSION['lang']['pt']."</td>
            <td>:</td>
            <td>
                <select class=select2 id='pt' multiple style='width:400px'>".$optPt."</select>
            </td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['intiplasma']."</td>
            <td>:</td>
            <td>
                <select id='intiplasma' style='width:400px'>
                    <option value=''>".$_SESSION['lang']['all']."</option>
                    <option value='1'>Inti</option>
                    <option value='0'>Plasma</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['periode']."</td>
            <td>:</td>
            <td>
                <select class=select2 id='periode' style='width:400px'>".$optPeriode."</select>
            </td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td>
                <button onclick=\"preview('html',event)\" class='mybutton'>".$_SESSION['lang']['preview']."</button>
                <button onclick=\"preview('excel',event)\" class='mybutton'>".$_SESSION['lang']['excel']."</button>
            </td>
        </tr>
    </table>
</fieldset>
<div style='clear:both;'></div>
<div id='printContainer0' class='table-scroll' style='overflow:auto;height:450px;width:100% !important;'></div>
";

$frm[1] = "
<fieldset style='float:left;'>
    <legend>BULANAN</legend>
    <table cellspacing=1 cellpadding=2>
        <tr>
            <td>".$_SESSION['lang']['pt']."</td>
            <td>:</td>
            <td>
                <select class=select2 id='pt2' multiple style='width:400px'>".$optPt."</select>
            </td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['intiplasma']."</td>
            <td>:</td>
            <td>
                <select id='intiplasma2' style='width:400px'>
                    <option value=''>".$_SESSION['lang']['all']."</option>
                    <option value='1'>Inti</option>
                    <option value='0'>Plasma</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td>
                <button onclick=\"preview2('html',event)\" class='mybutton'>".$_SESSION['lang']['preview']."</button>
                <button onclick=\"preview2('excel',event)\" class='mybutton'>".$_SESSION['lang']['excel']."</button>
            </td>
        </tr>
    </table>
</fieldset>
<div style='clear:both;'></div>
<div id='printContainer1' class='table-scroll' style='overflow:auto;height:450px;width:100% !important;'></div>
<div id='chart-container' style='display:none; width:100%; height:400px; margin-top:20px;'>
    <canvas id='myChartCanvas'></canvas>
</div>
";

drawTab('FRM', $hfrm, $frm, 150, '100%');

CLOSE_BOX();

echo close_body();
?>