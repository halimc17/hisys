<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');


OPEN_BOX('','<span class=judul>Koreksi Data TBS Inti / Plasma</span><br>');
$str="select compcode,descmillcode from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['descmillcode'];
$compcode=$res[0]['compcode'];

$optunit=$optdivisi=$opttransportir="<option value=''>Silahkan pilih</option>";
## GET UNIT
$str="select unitcode,unitname from ".$dbname.".msunit where compcode='".$compcode."' and tipeunit!='' and unitstatus='1'";
$res=fetchdata($str);
foreach ($res as $val) {
    $optunit.="<option value='".$val['unitcode']."'>".$val['unitname']."</option>";
}

$optkendaraan="<option value=''>Silahkan pilih</option>";
$str="select vhccode from ".$dbname.".msvhc where vhcstatus='1'";
$res=fetchdata($str);
foreach ($res as $val) {
	$optkendaraan.="<option value='".$val['vhccode']."'>".$val['vhccode']."</option>";				
}

## GET TRANSPORTIR
$str="select vendorcode,vendorname from ".$dbname.".msvendor where transportir='1' and vendorstatus='1'";
$res=fetchdata($str);
foreach ($res as $val) {
    $opttransportir.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";
}
$optso="<option value=''>Silahkan pilih</option>";
$optproductionorder="<option value=''>Silahkan pilih</option>";

echo"<table border=0 cellpadding=3 style=width:100%>
	<tr>
	<td style=vertical-align:top;width:40%>
		<table class=viewtable border=0 align=left  style='table-layout: fixed;width:90%;'>
		<tr>
			<td style='width:50%'>
				<label class=label style='font-weight:bold'>No Tiket</label>
				<input class=myinputtext type=text name=ticketno id=ticketno style='width:80%;background-color:#2AFFD4;font-size:18px' value=''>
			</td>
			
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>QR Code / SPB</label><br>
				<input tabindex=1 class=myinputtext style='width:80%;height:28px' type=text name=qrcode id=qrcode onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='50' value='' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>Unit</label>
				<select class='select2' style='width:85%;height:32px;' id='unit' tabindex=2 disabled>".$optunit."</select>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Divisi</label>
				<select class='select2' style='width:85%;height:32px;' id='divisi' tabindex=3 disabled>".$optdivisi."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Jumlah Segel</label>
				<input tabindex=10 class=myinputtext style='width:80%;text-align:right' type=text id=qtysegel onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Production Order</label><br>
				<select class='select2' style='width:85%;' id='productionorder' tabindex=4 disabled>".$optproductionorder."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>No Segel</label>
				<input tabindex=11 class=myinputtext style='width:80%' type=text id=segel onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>


		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Kontrak</label><br>
				<select class='select2' style='width:85%;' id='so' tabindex=4 disabled>".$optso."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Jumlah Janjang</label>
				<input tabindex=12 class=myinputtext style='width:80%;text-align:right' type=text id=jjg onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' onblur=calcgrad() value='' placeholder='0' disabled>
			</td>
			
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Transportir</label>
				<select class='select2' style='width:85%;height:32px;' id='transportir' tabindex=5 disabled>".$opttransportir."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Brondolan</label>
				<input tabindex=13 class=myinputtext style='width:80%;text-align:right' type=text id=brondol onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
			
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>No Kendaraan</label>
				<select class='select2tags' style='width:85%;height:32px;' id='nokendaraan' tabindex=6 disabled>".$optkendaraan."</select>
			</td>			
			<td>
				<label class=label style='font-weight:bold'>Keterangan</label>
				<input tabindex=14 class=myinputtext style='width:80%' type=text id=keterangan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Supir</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=supir id=supir onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Bruto</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=bruto id=bruto onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Potongan</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=kgpotongan id=kgpotongan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Netto</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=netto id=netto onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		<tr>
		<td><button tabindex=1007 class=mybutton id=simpan style=width:41.5%;height:35px>Simpan</button>
		<button tabindex=1008 class=mybutton id=batal style=width:41.5%;height:35px>Batal</button></td>
		</tr>
		</table>
	</td>
	<td style='width:30%'>
		<div id='showgrading'></div>
		
		<div id='showsortasi'></div>
	</td>
	</tr>
</table>";
CLOSE_BOX();
?>
<script language=javascript1.2 src='js/koreksi_intbsint.js?v=<?php echo time(); ?>'></script>
<script>
	getSelect2();
</script>
<?php
echo close_body();
?>