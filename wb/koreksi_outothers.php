<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');


OPEN_BOX('','<span class=judul>Koreksi Data Kirim Lain-lain</span><br>');
$str="select compcode,descmillcode from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['descmillcode'];
$compcode=$res[0]['compcode'];

$arrunit="select descode1 from ".$dbname.".msunit where compcode='".$compcode."'";
$optproduk=$optcustomer=$optso=$opttransportir=$optsambungso=$optsupplier=$optdivisi="<option value=''>Silahkan pilih</option>";

## GET PRODUK
$str="select kodeproduk,namaproduk from ".$dbname.".msproduk where statusproduk='1' and kodeproduk not in ('".$kodeprodukcpo."','".$kodeprodukpk."') order by namaproduk asc";
$res=fetchdata($str);
foreach ($res as $val) {
    $optproduk.="<option value='".$val['kodeproduk']."'>".$val['namaproduk']."</option>";
}

## GET CUSTOMER
$str="select * from ".$dbname.".mscustomer where custstatus='1'";
$res=fetchdata($str);
foreach ($res as $val) {
	if (isset($param['so'])) {
		if($param['so']==$val['custcode']){
			$optcustomer.="<option value='".$val['custcode']."' selected>".$val['custname']."</option>";
		}else{
			$optcustomer.="<option value='".$val['custcode']."'>".$val['custname']."</option>";
		}
	}else{
		$optcustomer.="<option value='".$val['custcode']."'>".$val['custname']."</option>";
	}
}

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

## GET UNIT
$optpemilik="";
$str="select unitcode,unitname from ".$dbname.".msunit where unitstatus='1' order by unitname asc";
$res=fetchdata($str);
foreach ($res as $val) {
    $optpemilik.="<option value='".$val['unitcode']."'>".$val['unitname']."</option>";
}

echo"<table border=0 cellpadding=3 style=width:100%>
	<tr>
	<td style=vertical-align:top;width:50%>
		<table class=viewtable border=0 align=left  style='table-layout: fixed;width:50%;'>
		<tr>
			<td style='width:50%'>
				<label class=label style='font-weight:bold'>No Tiket</label>
				<input class=myinputtext type=text name=ticketno id=ticketno style='width:80%;background-color:#2AFFD4;font-size:18px' value=''>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Transporter</label>
				<select tabindex=4 class='select2' style='width:85%;height:32px;' id='transportir' disabled>".$opttransportir."</select>
			</td>
		</tr>

		<tr>
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>Produk</label><br>
				<select tabindex=2 class='select2' style='width:85%;height:32px;' id='produk' disabled>".$optproduk."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>No Kendaraan</label>
				<input tabindex=5 class=myinputtext style='width:80%' type=text name=nokendaraan id=nokendaraan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='11' value='' disabled>
			</td>
		</tr>
		
		<tr>
			<td style='width:50%;'>
				<label class=label style='font-weight:bold'>Customer</label>
				<select tabindex=2 class='select2' style='width:85%;height:32px;' id='customer' disabled>".$optcustomer."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Supir</label>
				<input tabindex=6 class=myinputtext style='width:80%' type=text name=supir id=supir onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Divisi/Afdeling</label>
				<select class='select2' style='width:85%;height:32px;' id='divisi' tabindex=3 disabled>".$optdivisi."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>No. SIM</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=nosim id=nosim onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No. Sales Order</label><br>
				<select tabindex=3 class='select2' style='width:85%;' id='so' disabled>".$optso."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Keterangan</label>
				<input tabindex=8 class=myinputtext style='width:80%' type=text id=keterangan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Sisa Sales Order</label>
				<input class=myinputtext style='width:80%;text-align:right' type=text id=sisaso onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Jumlah Segel</label>
				<input tabindex=14 class=myinputtext style='width:80%;text-align:right;height:28px' type=text id=qtysegel onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
			
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No. Kontrak</label><br>
				<input class=myinputtext style='width:80%;height:28px' type=text id=nokontrak onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='50' value='' disabled>
			</td>
			<td>
				<label class=label style='font-weight:bold'>No. Segel</label>
				<input tabindex=15 class=myinputtext style='width:250px' type=text id=segel onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Netto</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=netto id=netto onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Split Sales Order</label>
				<select tabindex=16 class='select2' style='width:85%;height:160px;' id='sambungso' tabindex=10 disabled>".$optsambungso."</select>
				<label id='detailsambungso'></label>
			</td>
			
		</tr>
		<tr>
		<td><button tabindex=1007 class=mybutton id=simpan style=width:41.5%;height:35px>Simpan</button>
		<button tabindex=1008 class=mybutton id=batal style=width:41.5%;height:35px>Batal</button></td>
		</tr>
		</table>
	</td>
	</tr>
</table>";
CLOSE_BOX();

?>
<script language=javascript1.2 src='js/koreksi_outothers.js?v=<?php echo time(); ?>'></script>
<script>
	getSelect2();
</script>
<?php
echo close_body();
?>