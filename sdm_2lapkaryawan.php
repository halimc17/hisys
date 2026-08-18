<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_2lapkaryawan').'</span><br>');
?>
<script language=javascript src='js/sdm_2lapkaryawan.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
	
	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>
<?

$str="select DISTINCT periodegaji as prd from ".$dbname.".datakaryawan_hist where periodegaji!='' order by periodegaji desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
}
$no=0;
$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $optprd.="<optgroup label=\"Saat Ini\">";
// $optprd.="<option value='bi'>Saat Ini</option>";			
$optprd.="</optgroup>";

foreach($data as $thn => $vprd){
	$d=$thn;
	if($d!=$n){			
		$optprd.="<optgroup label='".$d."'>";
	}
	foreach($vprd as $prd){
		$optprd.="<option value=".$prd.">".$prd."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optprd.="</optgroup>";
	}
}
$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
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

$stat=array('0'=>'NON AKTIF','1'=>'AKTIF');
$wh="";
if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
	// $wh="and id in ('4')";
}
$opttipekary="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where 1=1 ".$wh." order by aktif desc, no asc";
$res = fetchData($str);
foreach($res as $bar){
	$d=$bar['aktif'];
	if($d!=$n){			
		$opttipekary.="<optgroup label='".$stat[$d]."'>";
	}
    $opttipekary.="<option value='".$bar['id']."'>".$bar['tipe']."</option>";
	$n=$d;
	if($d!=$n){			
		$opttipekary.="</optgroup>";
	}
}

$val="##kodeorg##tipekaryawan##periode";
echo"<table id=formfilter style=display:block; border=0><td style=vertical-align:top>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td colspan=3><select class='select2' id=kodeorg style='width:163px;'>".$optorg."</select></td><td></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['tipekaryawan'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=tipekaryawan style=\"width:163px;\">".$opttipekary."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=periode style=\"width:163px;\">".$optprd."</select></td>
                </tr>
				<td>".$_SESSION['lang']['tanggal']." <br> Status Aktif/Tidak Aktif</td>
				<td>:</td>
				<td nowrap>
					<input type=text class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:163px;\" maxlength='10' readonly/>
					<!--<input type=text class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' readonly/>-->
				</td>	
				
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    
					<button onclick=\"postPreview('".$val."')\" class=mybutton>Preview</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</td><td style=vertical-align:top>";
echo"<div id=info></div>";
echo"</td></table>";

CLOSE_BOX();
OPEN_BOX();	
echo"<div id='output'></div>";
CLOSE_BOX();
echo close_body();
?>