<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('master_koreksi').'</span><br><br>');
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

$optsupplier="<option value=''>Silahkan pilih</option>";

## GET SUPPLIER & TRANSPORTIR
$str="select * from ".$dbname.".msvendor where vendorstatus='1'";
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
$optproductionorder="<option value=''>Silahkan pilih</option>";

$jam='';
$mnt='';
for ($j=0; $j < 24; $j++) {
	if (strlen($j)==1) {
		$j='0'.$j;
	}; 
	$jam .= "<option value=".$j.">".$j."</option>;";
}
for ($m=0; $m < 60; $m++) {
	if (strlen($m)==1) {
		$m='0'.$m;
	}; 
	$mnt .= "<option value=".$m.">".$m."</option>;";
}

$frm[0]="<table border=0 cellpadding=3 style=width:100%>
<tr>
	<td style=vertical-align:top;width:50%>
		<table class=viewtable border=0 align=left  style='table-layout: fixed;width:90%;'>
		<tr>
			<td style='width:50%'>
				<label class=label style='font-weight:bold'>No Tiket</label>
				<input class=myinputtext type=text name=ticketno id=ticketno style='width:80%;background-color:#2AFFD4;font-size:22px' value=''>
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
			<td>
				<label class=label style='font-weight:bold'>Jumlah Segel</label>
				<input tabindex=10 class=myinputtext style='width:80%;text-align:right' type=text id=qtysegel onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Divisi</label>
				<select class='select2' style='width:85%;height:32px;' id='divisi' tabindex=3 disabled>".$optdivisi."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>No Segel</label>
				<input tabindex=11 class=myinputtext style='width:80%' type=text id=segel onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Production Order</label><br>
				<select class='select2' style='width:85%;' id='productionorder' tabindex=4 disabled>".$optproductionorder."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Jumlah Janjang</label>
				<input tabindex=12 class=myinputtext style='width:80%;text-align:right' type=text id=jjg onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Kontrak</label><br>
				<select class='select2' style='width:85%;' id='so' tabindex=4 disabled>".$optso."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Brondolan</label>
				<input tabindex=13 class=myinputtext style='width:80%;text-align:right' type=text id=brondol onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Transportir</label>
				<select class='select2' style='width:85%;height:32px;' id='transportir' tabindex=5 disabled>".$opttransportir."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Keterangan</label>
				<input tabindex=14 class=myinputtext style='width:80%' type=text id=keterangan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>No Kendaraan</label>
				<input tabindex=6 class=myinputtext style='width:80%' type=text name=nokendaraan id=nokendaraan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='11' value='' disabled>
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
		<td>&nbsp</td>
		</tr>
		<tr>
		<td><button tabindex=1007 class=mybutton id=simpan style=width:41.5%;height:35px>Simpan</button>
		<button tabindex=1008 class=mybutton id=batal style=width:41.5%;height:35px>Batal</button></td>
		<td></td>
		</tr>
		</table>
	</td>
	<td style='width:30%'>";
		$frm[0].="<div id='showgrading'></div>";
		
		$frm[0].="<div id='showsortasi'></div>";
	$frm[0].="</td>
	</tr>
</table>";

$frm[1]="<table border=0 cellpadding=3 style=width:100%>
	<tr>
	<td style=vertical-align:top;width:50%>
		<table class=viewtable border=0 align=left  style='table-layout: fixed;width:90%;'>
		<tr>
			<td style='width:50%'>
				<label class=label style='font-weight:bold'>No Tiket</label>
				<input class=myinputtext type=text name=ticketno1 id=ticketno1 style='width:80%;background-color:#2AFFD4;font-size:22px' value=''>
			</td>
			<td style='width:50%;'>
				<label class=label style='font-weight:bold'>Jumlah Segel</label>
				<input tabindex=10 class=myinputtext style='width:80%;text-align:right' type=text id=qtysegel1 onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>

		<tr>
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>No. SPB</label><br>
				<input tabindex=1 class=myinputtext style='width:80%;height:28px' type=text name=qrcode1 id=qrcode1 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='50' value='' disabled>
			</td>
			<td>
				<label class=label style='font-weight:bold'>No Segel</label>
				<input tabindex=11 class=myinputtext style='width:80%' type=text id=segel1 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		
		<tr>
			<td style='width:50%;'>
				<label class=label style='font-weight:bold'>Supplier</label>
				<select class='select2' style='width:85%;height:32px;' id='supplier1' tabindex=3 disabled>".$optsupplier."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Jumlah Janjang</label>
				<input tabindex=12 class=myinputtext style='width:80%;text-align:right' type=text id=jjg1 onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Kontrak</label><br>
				<select class='select2' style='width:85%;' id='so1' tabindex=4 disabled>".$optso."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Brondolan</label>
				<input tabindex=13 class=myinputtext style='width:80%;text-align:right' type=text id=brondol1 onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Transportir</label>
				<select class='select2' style='width:85%;height:32px;' id='transportir1' tabindex=5 disabled>".$opttransportir."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Keterangan</label>
				<input tabindex=14 class=myinputtext style='width:80%' type=text id=keterangan1 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>No Kendaraan</label>
				<input tabindex=6 class=myinputtext style='width:80%' type=text name=nokendaraan1 id=nokendaraan1 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='11' value='' disabled>
			</td>			
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>Supir</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=supir1 id=supir1 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Bruto</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=bruto id=bruto1 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Potongan</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=kgpotongan id=kgpotongan1 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Netto</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=netto id=netto1 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		
		<tr>
		<td>&nbsp</td>
		</tr>
		<tr>
		<td><button tabindex=1007 class=mybutton id=simpan1 style=width:41.5%;height:35px>Simpan</button>
		<button tabindex=1008 class=mybutton id=batal1 style=width:41.5%;height:35px>Batal</button></td>
		<td></td>
		</tr>
		</table>
	</td>
	<td style='width:50%'>";
		$frm[1].="<div id='showsortasi1'></div>";
			
		$frm[1].="
	</td>
	</tr>
</table>";


$optcustomer=$optso=$opttransportir=$optsambungso="<option value=''>Silahkan pilih</option>";
$optstorage="";
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

$arrunit="select descode1 from ".$dbname.".msunit where compcode='".$compcode."'";

## GET SUPPLIER & TRANSPORTIR
$optsupplier="";
$str="select * from ".$dbname.".msvendor where vendorstatus='1' and transportir='1' and right(vendorcode,4) not in (".$arrunit.")";
$res=fetchdata($str);
foreach ($res as $val) {
	$opttransportir.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";
}

$optkendaraan="<option value=''>Silahkan pilih</option>";
$str="select vhccode from ".$dbname.".msvhc where vhcstatus='1'";
$res=fetchdata($str);
foreach ($res as $val) {
	$optkendaraan.="<option value='".$val['vhccode']."'>".$val['vhccode']."</option>";				
}

$arrwbcond = array('Normal' => 'Normal', 'Return' => 'Return');
$optwbcond="";
foreach ($arrwbcond as $value) {
	$optwbcond.="<option value='".$value."'>".$value."</option>";				
}

$jam='';
$mnt='';
for ($j=0; $j < 24; $j++) {
	if (strlen($j)==1) {
		$j='0'.$j;
	}; 
	$jam .= "<option value=".$j.">".$j."</option>;";
}

for ($m=0; $m < 60; $m++) {
	if (strlen($m)==1) {
		$m='0'.$m;
	}; 
	$mnt .= "<option value=".$m.">".$m."</option>;";
}

$frm[2]="<table border=0 cellpadding=3 style=width:100%>
	<tr>
	<td style=vertical-align:top;width:50%>
		<table class=viewtable border=0 align=left  style='table-layout: fixed;width:90%;'>
		<tr>
			<td style='width:50%'>
				<label class=label style='font-weight:bold'>No Tiket</label>
				<input class=myinputtext type=text name=ticketno id=ticketno2 style='width:80%;background-color:#2AFFD4;font-size:18px' value=''>
			</td>
			<td>
				<label class=label style='font-weight:bold'>No Kendaraan</label>
				<select tabindex=5 class='select2' style='width:85%;height:32px;' id='nokendaraan2' disabled>".$optkendaraan."</select>
			</td>
		</tr>
		<tr>
			<td style='width:50%;'>
				<label class=label style='font-weight:bold'>WB Cond</label>
				<select tabindex=2 class='select2' style='width:85%;height:32px;' id='wbcond2' disabled>".$optwbcond."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Supir</label>
				<input tabindex=6 class=myinputtext style='width:80%' type=text name=supir id=supir2 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>

		<tr>
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>Produk</label><br>
				<input tabindex='1' type=radio id=product12 name=product value=CPO disabled>CPO
				<input tabindex='1' type=radio id=product22 name=product value=KER disabled>PK(Palm Kernel)
			</td>
			<td>
				<label class=label style='font-weight:bold'>No. SIM</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=nosim id=nosim2 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		
		<tr>
			<td style='width:50%;'>
				<label class=label style='font-weight:bold'>Customer</label>
				<select tabindex=2 class='select2' style='width:85%;height:32px;' id='customer2'>".$optcustomer."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Keterangan</label>
				<input tabindex=8 class=myinputtext style='width:80%' type=text id=keterangan2 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
			
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No. Sales Order</label><br>
				<select tabindex=3 class='select2' style='width:85%;' id='so2'>".$optso."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>WB Ref</label>
				<input tabindex=8 class=myinputtext style='width:80%' type=text id=tiketref2 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
			
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Sisa Sales Order</label>
				<input class=myinputtext style='width:80%;text-align:right' type=text id=sisaso2 onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Netto</label>
				<input tabindex=9 class=myinputtext style='width:80%;text-align:right' type=text name=netto id=netto2 onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
			
		</tr>
		
		<tr>
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>No. Kontrak</label><br>
				<input class=myinputtext style='width:80%;height:28px' type=text id=nokontrak2 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='50' value='' disabled>
			</td>

			<td>
				<label class=label style='font-size:12px;font-weight:bold;width:100%'>Tanggal Masuk</label>
				<input type=text id=tanggalmasuk2 tabindex='10' style='width:35%;' class=myinputtext size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"  value='' readonly>
				<select id=jammasuk2 class='select2' style='width:21%;'>".$jam."</select>
				<select id=mntmasuk2 class='select2' style='width:21%;'>".$mnt."</select>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Transporter</label>
				<select tabindex=4 class='select2' style='width:85%;height:32px;' id='transportir2'>".$opttransportir."</select>
			</td>
			<td>
				<label class=label style='font-size:12px;font-weight:bold;width:100%'>Tanggal Keluar</label>
				<input type=text id=tanggalkeluar2 tabindex='11' style='width:35%;' class=myinputtext size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\"  value='' readonly>
				<select id=jamkeluar2 class='select2' style='width:21%;'>".$jam."</select>
				<select id=mntkeluar2 class='select2' style='width:21%;'>".$mnt."</select>
			</td>
		</tr>

		<tr>
		<td>&nbsp</td>
		</tr>
		<tr>
			<td>
				<button tabindex=1007 class=mybutton id=simpan2 style=width:41.5%;height:35px>Simpan</button>
				<button tabindex=1008 class=mybutton id=batal2 style=width:41.5%;height:35px>Batal</button>
			</td>
		</tr>
		</table>
	</td>
	<td style='vertical-align:top'>
		<div id='showkualitas' style='display:block'>";
			$frm[2].="<table cellpadding=3>
			<tr>
				<td>Storage</td>
				<td>:</td>
				<td>
					<select tabindex=9 class='select2' style='width:65%;height:32px;' id='storage2'>".$optstorage."</select>
				</td>
			</tr>
			<tr>
				<td>FFA</td>
				<td>:</td>
				<td>
					<input tabindex=10 class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=ffa2 onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>Moist</td>
				<td>:</td>
				<td>
					<input tabindex=11 class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=moist2 onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>Dirt</td>
				<td>:</td>
				<td>
					<input tabindex=12 class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=dirt2 onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>Dobi</td>
				<td>:</td>
				<td>
					<input tabindex=13 class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=dobi2 onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>Jumlah Segel</td>
				<td>:</td>
				<td>
					<input tabindex=14 class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=qtysegel2 onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>No. Segel</td>
				<td>:</td>
				<td>
					<input tabindex=15 class=myinputtext style='width:250px' type=text id=segel2 onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
				</td>
			</tr>
			<tr>
				<td>Split Sales Order</td>
				<td>:</td>
				<td>
					<select tabindex=16 class='select2' style='width:82%;height:160px;' id='sambungso2' tabindex=10>".$optsambungso."</select>
				</td>
			</tr>
			<tr>
				<td colspan='2'></td>
				<td>
					<label id='detailsambungso2'></label>
				</td>
			</tr>
		</table>
		</div>
	</td>
	</tr>
</table>";

$hfrm[0] = "TBS Inti / Plasma";
$hfrm[1] = "TBS Eksternal";
$hfrm[2] = "CPO / PK";

drawTab('FRM', $hfrm, $frm, 120, "");
CLOSE_BOX();
?>
<script language=javascript src='js/master_koreksi.js?v=<?php echo time(); ?>'></script>
<script>
	getSelect2();
</script>
<?
echo close_body();
?>