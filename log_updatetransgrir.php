<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('log_updatetransgrir').'</span><br>');
?>
<script language=javascript src='js/log_updatetransgrir.js?v=<?php echo time(); ?>'></script>
<?
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
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

$str="select DISTINCT tahunbudget as prd from ".$dbname.".bgt_budget order by tahunbudget desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$optprd.="<option value=".$val['prd'].">".$val['prd']."</option>";			
}


echo"<table border=0  id=tableheader><td style=vertical-align:top>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>" . $_SESSION['lang']['kodeorg'] . "</td> 
					<td>:</td>
					<td><select id=kodeorg style=\"width:130px;\">".$optorg."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['tahun'] . "</td> 
					<td>:</td>
					<td><select id=tahun style=\"width:130px;\">".$optprd."</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=preview(); class=mybutton>Preview</button>
                    <button onclick=excel('excel'); class=mybutton>Excel</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</td><td style=vertical-align:top>";
echo"<div id=info></div>";
echo"</td></table>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id='output' style=min-height:400px;></div>";
CLOSE_BOX();
echo close_body();
?>