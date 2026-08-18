<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');


OPEN_BOX('','<span class=judul>Koreksi Data TBS Eksternal</span><br>');
$str="select compcode,descmillcode from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['descmillcode'];
$compcode=$res[0]['compcode'];

$arrunit="select descode1 from ".$dbname.".msunit where compcode='".$compcode."'";
$optsupplier=$opttransportir="<option value=''>Silahkan pilih</option>";

## GET SUPPLIER & TRANSPORTIR
$str="select * from ".$dbname.".msvendor where vendorstatus='1' and right(vendorcode,4) not in (".$arrunit.")";
$res=fetchdata($str);
foreach ($res as $val) {
	if($val['supplier']=='1'){
		$optsupplier.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";
	}
	if($val['transportir']=='1'){
		$opttransportir.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";
	}
}

$optso="<option value=''>Silahkan pilih</option>";

echo"<table border=0 cellpadding=3 style=width:100%>
	<tr>
	<td style=vertical-align:top;width:50%>
		<table class=viewtable border=0 align=left  style='table-layout: fixed;width:90%;'>
		<tr>
			<td style='width:50%'>
				<label class=label style='font-weight:bold'>No Tiket</label>
				<input class=myinputtext type=text name=ticketno id=ticketno style='width:80%;background-color:#2AFFD4;font-size:18px' value=''>
			</td>
		</tr>

		<tr>
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>No. SPB</label><br>
				<input tabindex=1 class=myinputtext style='width:80%;height:28px' type=text name=qrcode id=qrcode onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='50' value='' disabled>
			</td>

			<td style='width:50%;display:none'>
				<label class=label style='font-weight:bold'>Potongan Wajib</label>
				<input tabindex=10 class=myinputtext style='width:80%;text-align:right' type=text id=potonganwajib onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>
		
		<tr>
			<td style='width:50%;'>
				<label class=label style='font-weight:bold'>Supplier</label>
				<select class='select2' style='width:85%;height:32px;' id='supplier' tabindex=3 disabled>".$optsupplier."</select>
			</td>
			
			<td style='width:50%;'>
				<label class=label style='font-weight:bold'>Jumlah Segel</label>
				<input tabindex=10 class=myinputtext style='width:80%;text-align:right' type=text id=qtysegel onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Kontrak</label><br>
				<select class='select2' style='width:85%;' id='so' tabindex=4 disabled>".$optso."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>No Segel</label>
				<input tabindex=11 class=myinputtext style='width:80%' type=text id=segel onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Transportir</label>
				<select class='select2' style='width:85%;height:32px;' id='transportir' tabindex=5 disabled>".$opttransportir."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Jumlah Janjang</label>
				<input tabindex=12 class=myinputtext style='width:80%;text-align:right' type=text id=jjg onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>No Kendaraan</label>
				<input tabindex=6 class=myinputtext style='width:80%' type=text name=nokendaraan id=nokendaraan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='11' value='' disabled>
			</td>			
			
			<td>
				<label class=label style='font-weight:bold'>Brondolan</label>
				<input tabindex=13 class=myinputtext style='width:80%;text-align:right' type=text id=brondol onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>Supir</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=supir id=supir onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Keterangan</label>
				<input tabindex=14 class=myinputtext style='width:80%' type=text id=keterangan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
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
				<label class=label style='font-weight:bold'>Potongan Wajib</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=kgpotonganwajib id=kgpotonganwajib onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
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
<script language=javascript1.2 src='js/koreksi_intbseks.js?v=<?php echo time(); ?>'></script>
<script>
	getSelect2();
</script>
<?php
echo close_body();
?>