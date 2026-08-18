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
				<font size=5 color=#191919 font-family: Verdana, Arial, Helvetica, sans-serif>Kirim Produk Lain-Lain</font>
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
					<td style='text-align:right'><label id='jlhkendaraan0'></label></td>
				</tr>
				<tr>
					<td style='text-align:center'>Keluar</td>
					<td>:</td>
					<td style='text-align:right'><label id='jlhkendaraan1'></label></td>
				</tr>
			</table>
		</td>
		<td align='right'>
			<img src='images/E1205web.gif' class='wbic' title='Refresh'>
			<input class=myinputtext type=text name=weight id=weight style='background-color:#2AFFD4;width:350px;height:60px;font-size:50px;text-align:right' maxlength=7 value='0'>
		</td>
	</tr>
</table>";
CLOSE_BOX();

OPEN_BOX('','<span class=judul>Input Data</span><br>');
$str="select compcode,descmillcode from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['descmillcode'];
$compcode=$res[0]['compcode'];

$arrunit="select descode1 from ".$dbname.".msunit where compcode='".$compcode."'";
$optproduk=$optcustomer=$optso=$opttransportir=$optsambungso=$optsupplier=$optdivisi="<option value=''>Silahkan pilih</option>";

## GET PRODUK
$str="select kodeproduk,namaproduk from ".$dbname.".msproduk where statusproduk='0' and kodeproduk not in ('".$kodeprodukcpo."','".$kodeprodukpk."') order by namaproduk asc";
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
	// if($val['supplier']=='1'){
		$optsupplier.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";
	// }
	// if($val['transportir']=='1'){
		$opttransportir.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";
	// }
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
		<table class=viewtable border=0 align=left  style='table-layout: fixed;width:90%;'>
		<tr>
			<td style='width:50%'>
				<label class=label style='font-weight:bold'>No Tiket</label>
				<input class=myinputtext type=text name=ticketno id=ticketno style='width:80%;background-color:#2AFFD4;font-size:18px' value='' disabled>
			</td>
		</tr>

		<tr>
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>Produk</label><br>
				<select tabindex=2 class='select2' style='width:85%;height:32px;' id='produk'>".$optproduk."</select>
			</td>
		</tr>
		
		<tr>
			<td style='width:50%;'>
				<label class=label style='font-weight:bold'>Customer</label>
				<select tabindex=2 class='select2' style='width:85%;height:32px;' id='customer'>".$optcustomer."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Transporter</label>
				<select tabindex=4 class='select2' style='width:85%;height:32px;' id='transportir'>".$opttransportir."</select>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Divisi/Afdeling</label>
				<select class='select2' style='width:85%;height:32px;' id='divisi' disabled tabindex=3>".$optdivisi."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>No Kendaraan</label>
				<input tabindex=5 class=myinputtext style='width:80%' type=text name=nokendaraan id=nokendaraan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='11' value=''>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No. Sales Order</label><br>
				<select tabindex=3 class='select2' style='width:85%;' id='so'>".$optso."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Supir</label>
				<input tabindex=6 class=myinputtext style='width:80%' type=text name=supir id=supir onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Sisa Sales Order</label>
				<input class=myinputtext style='width:80%;text-align:right' type=text id=sisaso onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>No. SIM</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=nosim id=nosim onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>No. Kontrak</label><br>
				<input class=myinputtext style='width:80%;height:28px' type=text id=nokontrak onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='50' value='' disabled>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Keterangan</label>
				<input tabindex=8 class=myinputtext style='width:80%' type=text id=keterangan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>
		</table>
	</td>
	<td style='vertical-align:bottom'>
		<div id='showkualitas' style='display:none'>";
			echo"<table cellpadding=3>
			<tr>
				<td>Jumlah Segel</td>
				<td>:</td>
				<td>
					<input tabindex=14 class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=qtysegel onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>No. Segel</td>
				<td>:</td>
				<td>
					<input tabindex=15 class=myinputtext style='width:250px' type=text id=segel onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
				</td>
			</tr>
			<tr>
				<td>Split Sales Order</td>
				<td>:</td>
				<td>
					<select tabindex=16 class='select2' style='width:82%;height:160px;' id='sambungso' tabindex=10>".$optsambungso."</select>
				</td>
			</tr>
			<tr>
				<td colspan='2'></td>
				<td>
					<label id='detailsambungso'></label>
				</td>
			</tr>
		</table>
		</div>
	</td>
	</tr>
</table>";
CLOSE_BOX();

OPEN_BOX('');
echo"<table border=0 align=center style='width:100%'>
	<tr>
		<td align='center' style='vertical-align:top;'>
			<fieldset style='height:120px'>
			<legend>
			<b>Timbang 1</b>
			</legend>
			<table border=0 cellspacing=0>
				<tr>
					<td>Tanggal</td>
					<td>:</td>
					<td>
						<input class=myinputtext type=text name=datein id=datein  onkeypress='return false;' style='width:85%;font-size:18px;text-align:right;' disabled>
					</td>
					<td rowspan=2>
						<button tabindex=1005 class=mybutton id=getweight1 style='height:70px;margin:5px'>Get Weight</button>
					</td>
				</tr>
				<tr>
					<td>Berat</td>
					<td>:</td>
					<td>
						<input class=myinputtext type=text id=wei1st  onkeypress='return false;' style='width:85%;font-size:18px;text-align:right;' disabled>
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td align='center' style='vertical-align:top;'>
			<fieldset style='height:120px'>
			<legend>
			<b>Timbang 2</b>
			</legend>
			<table border=0 cellspacing=0>
				<tr>
					<td>Tanggal</td>
					<td>:</td>
					<td>
						<input class=myinputtext type=text name=dateout id=dateout onkeypress='return false;' style='width:85%;font-size:18px;text-align:right;' disabled>
					</td>
					<td rowspan=2>
						<button tabindex=1006 class=mybutton id=getweight2 style='height:70px;margin:5px' disabled>Get Weight</button>
					</td>
				</tr>
				<tr>
					<td>Berat</td>
					<td>:</td>
					<td>
						<input class=myinputtext type=text id=wei2nd  onkeypress='return false;' style='width:85%;font-size:18px;text-align:right;' disabled>
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td align='center' style='vertical-align:top'>
			<fieldset style='height:120px'>
			<legend>
			<b>Simpan</b>
			</legend>
			<table border=0 cellspacing=0>
				<tr>
					<td valign='middle'>Bruto</td>
					<td>:</td>
					<td> 
						<input class=myinputtext type=text id=bruto onkeypress='return false;' style='width:85%;font-size:20px;text-align:right;'  disabled>
					</td>
					
					<td rowspan=3>
						<button tabindex=1007 class=mybutton id=simpan style='height:70px;width:80px;margin:5px'>Simpan</button>
					</td>
					<td>&nbsp</td>
					<td rowspan=3>
						<!--<button tabindex=1008 class=mybutton id=batal onclick=window.location.reload() style=width:80%;height:70px>Batal</button>-->
						<button tabindex=1008 class=mybutton id=batal onclick=batal() style='height:70px;width:80px;margin:5px'>Batal</button>
						<input type=hidden id=method value='timbang1'>
					</td>
					<td>&nbsp</td>
				</tr>
				<tr style=display:none>
					<td>Potongan</td>
					<td>:</td>
					<td> 
						<input class=myinputtext type=text id=kgpotongan onkeypress='return false;' style='width:85%;font-size:20px;text-align:right;'  disabled>
					</td>
				</tr>
				<tr>
					<td valign='middle'>Netto</td>
					<td>:</td>
					<td> 
						<input class=myinputtext type=text id=netto onkeypress='return false;' style='width:85%;font-size:20px;text-align:right;'  disabled>
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
<script language=javascript1.2 src='js/trx_outothers.js?v=<?php echo time(); ?>'></script>
<script>
	getSelect2();
</script>
<?php
echo close_body();
?>