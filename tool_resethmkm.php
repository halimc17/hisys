<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
	<script language=javascript src='js/zTools.js'></script>
	<script language=javascript1.2 src='js/tool_resethmkm.js?v=<?php echo time(); ?>'></script>
	<script>
		$(document).ready(function() {
			$('.select2').select2({
				dropdownAutoWidth:true
			});
		});
	</script>

<?
$arr="##kodevhc##kmhmakhir##method";
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('tool_resethmkm').'</span><br>');

$optVhc = makeOption($dbname,'vhc_5master','kodevhc,kodevhc');

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select * from ".$dbname.".vhc_5master order by kodetraksi asc, kodevhc";
$res=fetchdata($str);
foreach($res as $val){
	$d=$val['kodetraksi'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$e="";
	if(getNopol($val['kodevhc'])!=''){
		$e.=" - ".getNopol($val['kodevhc']);
	}
	if(getNopol($val['kodevhc'],'d')!=''){
		$e.=" - ".getNopol($val['kodevhc'],'d');
	}
	
	$optorg.="<option value=".$val['kodevhc'].">".$val['kodevhc'].$e."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

// KM/HM Akhir
$optKmAkhir = makeOption($dbname,'vhc_kmhm_track','kodevhc,kmhmakhir');
setIt($optKmAkhir[key($optVhc)],0);

echo"<fieldset style=float:left>
	<legend>Reset HM/KM</legend>
	<table>
	<tr>
		<td>".$_SESSION['lang']['kodevhc']."</td><td>:</td>
		<td><select class=select2 id='kodevhc' style='width:250px;' onchange=getKmHmAkhir();>".$optorg."</select></td>
	</tr>
	<td>".$_SESSION['lang']['tanggal']."</td><td>:</td>
	<td><input type=text class=myinputtext id=tanggal onchange=getKmHmAkhir(); onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:245px; readonly/></td>
	<tr style=display:none>
		<td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
		<td><select class=select2 id='notransaksi' style='width:250px;'></select></td>
	</tr>
	
    <tr style=display:none>
		<td>".$_SESSION['lang']['vhc_kmhm_akhir']."</td><td>:</td>
		<td><input class=myinputtextnumber style=width:150px id=kmhmakhir value=".$optKmAkhir[key($optVhc)]."></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td>			
			<button  style=display:none class=mybutton id=tmblDt onclick=resetDt()>".$_SESSION['lang']['proses']."</button>
			<button class=mybutton id=tmblDt onclick=preview()>".$_SESSION['lang']['preview']."</button>
		</td>
		
	</tr>
	</table>
	</fieldset><input type=hidden id=method value=getData />";


CLOSE_BOX();
OPEN_BOX();
echo"
    <div id=container>";
echo"</div>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>