<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOXX('','');

echo"<table style=\"width:99.5%;border-collapse:collapse;margin-left:auto;margin-right:auto;\">
	<tr>
		<td>
			<strong>
				<font size=5 color=#191919 font-family: Verdana, Arial, Helvetica, sans-serif>Weighbridge</font>
			</strong>
		</td>
		<td style='vertical-align:top;padding:5px;font-weight:bold;text-align:center'>
			<table border=0>
				<tr>
					<td style='text-align:center'><u>Jumlah Kendaraan</u></td>
				</tr>
				<tr>
					<td style='text-align:center'>Masuk</td>
					<td>:</td>
					<td style='text-align:right'>".@$jlhkendaraan['0']."</td>
				</tr>
				<tr>
					<td style='text-align:center'>Keluar</td>
					<td>:</td>
					<td style='text-align:right'>".@$jlhkendaraan['1']."</td>
				</tr>
			</table>
		</td>
		<td align='right'>
			<img src='images/E1205web.gif' class='wbic' title='Refresh'>
			<input class=myinputtext type=text name=weight id=weight style='background-color:#2AFFD4;width:350px;height:60px;font-size:50px;text-align:right' maxlength=7 value='0' onkeypress='return false;' disabled>
		</td>
	</tr>
</table>";
CLOSE_BOX();

OPEN_BOX('','<span class=judul>Input Data</span><br>');
$str="select compcode,descmillcode from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['descmillcode'];
$compcode=$res[0]['compcode'];

$opttipe="<option value=''>Silahkan pilih</option>";
$opttipe.="<option value='I'>Terima</option>";
$opttipe.="<option value='O'>Kirim</option>";
$opttipe.="<option value='II'>Terima TO/Blending</option>";
$opttipe.="<option value='OO'>Kirim TO/Blending</option>";

$optproduk="<option value=''>Silahkan pilih</option>";
$str="select * from ".$dbname.".msproduk where statusproduk='1'";
$res=fetchdata($str);
foreach($res as $val){
	$optproduk.="<option value='".$val['kodeproduk']."'>".$val['namaproduk']."</option>";	
}

$optsupplier="<option value=''>Silahkan pilih</option>";
$opttransportir="<option value=''>Silahkan pilih</option>";
$str="select * from ".$dbname.".msvendor where vendorstatus='1'";
$res=fetchdata($str);
foreach($res as $val){
	if($val['transportir']=='1'){
		$opttransportir.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";			
	}else{
		$optsupplier.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";			
	}
}

$optcustomer=$optkendaraan="<option value=''>Silahkan pilih</option>";
$str="select * from ".$dbname.".mscustomer where custstatus='1'";
$res=fetchdata($str);
foreach($res as $val){
	$optcustomer.="<option value='".$val['custcode']."'>".$val['custname']."</option>";	
}

## GET BLENDING CUSTOMER
// $optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
// $str="select distinct(kodept) as kodept, koderekanan from ".$dbname.".pmn_kontrakbeli where koderekanan like '%".$millcode."'";
// $res=fetchdata($str);
// foreach($res as $val){
	// $optcustomer.="<option value='".$val['koderekanan']."'>".$optorg[$val['kodept']]."</option>";	
// }

$optso="<option value=''>Silahkan pilih</option>";
$optsambungso="<option value=''>Silahkan pilih</option>";

echo"<table border=0 cellpadding=3 style=width:100%>
	<tr>
	<td style=vertical-align:top;width:50%>
		<table class=viewtable border=0 align=left style='table-layout: fixed;width:90%;'>
		<tr>
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>No Tiket</label><br>
				<input class=myinputtext type=text name=ticketno id=ticketno style='width:80%;background-color:#2AFFD4;font-size:22px;height:28px' value='' disabled>
			</td>
			
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>QR Code / SPB</label><br>
				<input tabindex=6 class=myinputtext style='width:80%;height:28px' type=text name=qrcode id=qrcode onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='11' value=''>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Tipe</label><br>
				<select class='select2' style='width:85%;height:160px;' id='tipe' tabindex=1>".$opttipe."</select>
			</td>
			
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No Kendaraan</label><br>
				<input tabindex=7 class=myinputtext style='width:80%;height:28px' type=text name=nokendaraan id=nokendaraan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='11' value=''>
				<!--<select class='select2' style='width:85%;height:160px;' id='nokendaraan' tabindex=1>".$optkendaraan."</select>-->
			</td>
		</tr>

		<tr>
		    <td>
		        <label class=label style='font-size:12px;font-weight:bold'>Produk</label><br>
		        <select class='select2' style='width:85%;height:32px;' id='produk' tabindex=2>".$optproduk."</select>
		    </td>
			
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Supir</label><br>
				<input tabindex=8 class=myinputtext style='width:80%;height:28px;' type=text name=supir id=supir onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Supplier</label><br>
				<select class='select2' style='width:85%;height:32px;' name='supplier' id='supplier' tabindex=3 disabled>".$optsupplier."</select>
			</td>
			
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No. SIM</label><br>
				<input tabindex=8 class=myinputtext style='width:80%;height:28px;' type=text name=nosim id=nosim onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Customer</label><br>
				<select class='select2' style='width:85%;height:32px;' name='customer' id='customer' tabindex=4 disabled>".$optcustomer."</select>
			</td>
			
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No PO / STO / CONTRACT / SO</label><br>
				<select class='select2' style='width:85%;' id='so' tabindex=9>".$optso."</select>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Transportir</label><br>
				<select class='select2' style='width:85%;height:32px;' id='transportir' tabindex=5>".$opttransportir."</select>
			</td>
			
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Split SO</label><br>
				<select class='select2' style='width:85%;height:160px;' id='sambungso' tabindex=10 disabled>".$optsambungso."</select>
			</td>
		</tr>
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Keterangan</label><br>
				<input tabindex=11 class=myinputtext style='width:80%;height:28px;' type=text id=keterangan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
			
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No Segel</label><br>
				<input tabindex=12 class=myinputtext style='width:80%;height:28px;' type=text id=segel onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>
		</table>
	</td>
	<td style=vertical-align:bottom;width:50%>
		<div id='showquality'></div><input type='hidden' id='ttlkg' value='0'>
		<button tabindex=111110 class=mybutton id=tabquality style=width:25%;height:40px;display:none>Kualitas Produk</button>
		<button tabindex=111110 class=mybutton id=tabgrading style=width:25%;height:40px;display:none>Grading</button>
		<button tabindex=111110 class=mybutton id=tabsortasi style=width:25%;height:40px;display:none>Sortasi</button>
	</td>
	</tr>
</table>";
CLOSE_BOX();

OPEN_BOX('');
echo"<table width=100% border=0 align=center>
	<tr>
		<td align='center' style='vertical-align:top;width:32%'>
			<fieldset style='height:120px'>
			<legend>
			<b>Timbang 1</b>
			</legend>
			<table border=0 width=90% cellspacing=0>
				<tr>
					<td>Tanggal</td>
					<td>:</td>
					<td>
						<input class=myinputtext type=text name=datein id=datein  size=20 onkeypress='return false;' style='font-size:18px;text-align:right;' disabled>
					</td>
					<td rowspan=2>
						<button tabindex=10 class=mybutton id=getweight1 style=width:100%;height:70px>Get Weight</button>
					</td>
				</tr>
				<tr>
					<td>Berat</td>
					<td>:</td>
					<td>
						<input class=myinputtext type=text size=20 id=wei1st  onkeypress='return false;' style='font-size:18px;text-align:right;' disabled>
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td align='center' style='vertical-align:top;width:32%'>
			<fieldset style='height:120px'>
			<legend>
			<b>Timbang 2</b>
			</legend>
			<table border=0 width=90% cellspacing=0>
				<tr>
					<td>Tanggal</td>
					<td>:</td>
					<td>
						<input class=myinputtext type=text name=dateout id=dateout size=20 onkeypress='return false;' style='font-size:18px;text-align:right;' disabled>
					</td>
					<td rowspan=2>
						<button tabindex=11 class=mybutton id=getweight2 style=width:100%;height:70px disabled>Get Weight</button>
					</td>
				</tr>
				<tr>
					<td>Berat</td>
					<td>:</td>
					<td>
						<input class=myinputtext type=text size=20 id=wei2nd  onkeypress='return false;' style='font-size:18px;text-align:right;' disabled>
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td align='center' style='width:36%;vertical-align:top'>
			<fieldset style='height:120px'>
			<legend>
			<b>Simpan</b>
			</legend>
			<table border=0 width=100% cellspacing=0>
				<tr>
					<td valign='middle'>Bruto</td>
					<td style='width:3%'>:</td>
					<td> 
						<input class=myinputtext type=text id=bruto size=12 onkeypress='return false;' style='font-size:20px;text-align:right;'  disabled>
					</td>
					
					<td rowspan=3 style='width:20%'>
						<button tabindex=12 class=mybutton id=simpan style=width:100%;height:70px>Simpan</button>
					</td>
					<td style='width:3%'>&nbsp</td>
					<td rowspan=3 style='width:20%'>
						<button tabindex=13 class=mybutton id=batal onclick=window.location.reload() style=width:100%;height:70px>Batal</button>
						<input type=hidden id=method value='timbang1'>
					</td>
					<td style='width:3%'>&nbsp</td>
				</tr>
				<tr style='display:none'>
					<td valign='middle'>Potongan</td>
					<td>:</td>
					<td> 
						<input class=myinputtext type=text id=kgpotongan size=12 onkeypress='return false;' style='font-size:20px;text-align:right;'  disabled>
					</td>
				</tr>
				<tr>
					<td valign='middle'>Netto</td>
					<td>:</td>
					<td> 
						<input class=myinputtext type=text id=netto size=12 onkeypress='return false;' style='font-size:20px;text-align:right;'  disabled>
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>";
CLOSE_BOX();

OPEN_BOX('','<b>LIST KENDARAAN TBS YG BELUM TIMBANG KELUAR<b>');
echo"<div id=container> 
	</div>
";
CLOSE_BOX();
?>

<script language=javascript1.2 src='js/trx_generic.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/wb.js?v=<?php echo time(); ?>'></script>
<script>
	getSelect2();
</script>
<?php
echo close_body();
?>