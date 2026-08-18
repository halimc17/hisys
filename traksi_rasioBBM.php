<?
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/vhc_2ratio.js'></script>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>
<?

require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('traksi_rasioBBM').'</span><br>');
//=================ambil unit;  

$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
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

$optJnsBBMvhc="<option value=''></option>";
$where=" `kelompokbarang` in ('351','312')";
$sbrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where ".$where."";
$res=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rbrg=$res->fetch()){
	if($rbrg['kodebarang']=='351010003'){
		$optJnsBBMvhc.="<option value='".$rbrg['kodebarang']."' selected>".$rbrg['kodebarang']."-".$rbrg['namabarang']."</option>";
	}else{
		$optJnsBBMvhc.="<option value=".$rbrg['kodebarang'].">".$rbrg['kodebarang']."-".$rbrg['namabarang']."</option>";
	}
}


echo"<fieldset style=float:left>
     <legend>Form</legend><table>
		<tr>
			<td>" . $_SESSION['lang']['unit'] . "</td><td>:</td>
			<td><select class='select2' id=unit style='width:150px;'>" . $optKodeorg . "</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['vhc_jenis_bbm']."</td><td>:</td>
			<td><select class='select2' id=jns_bbm name=jns_bbm style=width:150px;>".$optJnsBBMvhc."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tahun'] . "</td><td>:</td>
			<td><select class='select2' id=tahun style='width:150px;'><option value='" . date('Y') . "'>" . date('Y') . "</option>
                         <option value='" . (date('Y') - 1) . "'>" . (date('Y') - 1) . "</option>
                         <option value='" . (date('Y') - 2) . "'>" . (date('Y') - 2) . "</option>
                      </select>
			</td>		  
		</tr>
		<tr>
			<td></td><td></td>
			<td>
				<button class=mybutton onclick=getRatioKendaraan()>" . $_SESSION['lang']['preview'] . "</button>
				<button class=mybutton onclick=printFile('vhc_slave_2ratio.php',event)>" . $_SESSION['lang']['excel'] . "</button>
			</td>
		</tr>
		</table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('', '');
//	 <img onclick=hutangSupplierKePDF(event,'log_laporanhutangsupplier_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>

echo"
	<div id='container' class='table-scroll' style='overflow:auto;height:380px'; ></div>";
CLOSE_BOX();
close_body();
?>