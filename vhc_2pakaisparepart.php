<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('vhc_2pakaisparepart').'</span></br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<script language=javascript src='js/vhc_detailkmhm.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});

});
</script>
<?
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe in('KEBUN','PABRIK')",'2','1',true);
}else{
	$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe in('KEBUN','PABRIK') and induk='".$_SESSION['empl']['kodeorganisasi']."' ",'2','1',true);
}

$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(1) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optKodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optKodeorg.="</optgroup>";
	}
}


#$optprd = makeOption($dbname,'setup_periodeakuntansi','periode,periode','','0','1',true);

$str = "select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 24 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($row=$res->fetch()){
	@$optprd.="<option value=".$row['periode'].">".$row['periode']."</option>";
}


$arr = "##kdorg##prddari##prdsampai##kdvhc";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg onchange=getkodevhsunit() style=\"width:190px;\">" . $optKodeorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['kodevhc'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdvhc style=\"width:190px;\">" . $optvhc . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prddari style=\"width:80px;\">" . $optprd . "</select>
                    &nbsps/d&nbsp<select class=select2 id=prdsampai style=\"width:80px;\">" . $optprd . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('vhc_slave_2pakaisparepart','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'vhc_slave_2pakaisparepart.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "
<div id='printContainer'  style='overflow:auto;height:400px;width:100%';></div>";
CLOSE_BOX();
echo close_body();
?>