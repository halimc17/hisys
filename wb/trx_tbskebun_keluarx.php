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
$tanggal=date("Y-m-d");
$jlhkendaraan=array();
$str="select waktumasuk, waktukeluar from ".$dbname.".wb where in_out='O' and (waktumasuk LIKE '".$tanggal."%' or waktukeluar = '".$tanggal."%') and kodebarang = '".$kodeproduktbs."' and tipeunit in ('INTERNAL','PLASMA') and sumber='KEBUN'";
$res=fetchdata($str);
foreach($res as $val){
	if($val['waktumasuk']!=''){
		@$jlhkendaraan['0']+=1;
	}
	
	if($val['waktukeluar']!='0000-00-00 00:00:00'){
		@$jlhkendaraan['1']+=1;
	}
}

echo"<table style=\"width:99.5%;border-collapse:collapse;margin-left:auto;margin-right:auto;\">
	<tr>
		<td>
			<strong>
				<font size=5 color=#191919 font-family: Verdana, Arial, Helvetica, sans-serif>Kirim TBS</font>
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

$optpabrik=$optunit=$optdivisi=$opttransportir="<option value=''>Silahkan pilih</option>";
## GET UNIT
$str="select unitcode,unitname from ".$dbname.".msunit where compcode='".$compcode."' and tipeunit!='' and unitstatus='1'";
$res=fetchdata($str);
foreach ($res as $val) {
    $optunit.="<option value='".$val['unitcode']."'>".$val['unitname']."</option>";
}

## GET Pabrik
$str="select unitcode,unitname from ".$dbname.".msunit where tipeunit!='' and unitstatus='1' and tipe='PABRIK'";
$res=fetchdata($str);
foreach ($res as $val) {
    $optpabrik.="<option value='".$val['unitcode']."'>".$val['unitname']."</option>";
}


## GET TRANSPORTIR
$str="select vendorcode,vendorname from ".$dbname.".msvendor where transportir='1' and vendorstatus='1'";
$res=fetchdata($str);
foreach ($res as $val) {
    $opttransportir.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";
}
$optso="<option value=''>Silahkan pilih</option>";

echo"<table border=0 cellpadding=3 style=width:100%>
	<tr>
	<td style=vertical-align:top;width:50%>
		<table class=viewtable border=0 align=left  style='table-layout: fixed;width:90%;'>
		<tr>
			<td style='width:50%'>
				<label class=label style='font-weight:bold'>No Tiket</label>
				<input class=myinputtext type=text name=ticketno id=ticketno style='width:80%;background-color:#2AFFD4;font-size:22px' value='' disabled>
			</td>
			
			<td style='width:50%;'>
				<label class=label style='font-size:12px;font-weight:bold'>QR Code / SPB</label><br>
				<input tabindex=1 class=myinputtext style='width:80%;height:28px' type=text name=qrcode id=qrcode onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='50' value='' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>Unit</label>
				<select class='select2' style='width:85%;height:32px;' id='unit' tabindex=2>".$optunit."</select>
			</td>
			<td>
				<label class=label style='font-weight:bold'>Tujuan</label>
				<select class='select2' style='width:85%;height:32px;' id='tujuan' tabindex=2>".$optpabrik."</select>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Divisi</label>
				<select class='select2' style='width:85%;height:32px;' id='divisi' tabindex=3>".$optdivisi."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Jumlah Segel</label>
				<input tabindex=10 class=myinputtext style='width:80%;text-align:right' type=text id=qtysegel onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-size:12px;font-weight:bold'>Kontrak</label><br>
				<select class='select2' style='width:85%;' id='so' tabindex=4>".$optso."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>No Segel</label>
				<input tabindex=11 class=myinputtext style='width:80%' type=text id=segel onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		
		<tr>
			<td>
				<label class=label style='font-weight:bold'>Transportir</label>
				<select class='select2' style='width:85%;height:32px;' id='transportir' tabindex=5>".$opttransportir."</select>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Jumlah Janjang</label>
				<input tabindex=12 class=myinputtext style='width:80%;text-align:right' type=text id=jjg onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>No Kendaraan</label>
				<input tabindex=6 class=myinputtext style='width:80%' type=text name=nokendaraan id=nokendaraan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='11' value=''>
			</td>			
			
			<td>
				<label class=label style='font-weight:bold'>Brondolan</label>
				<input tabindex=13 class=myinputtext style='width:80%;text-align:right' type=text id=brondol onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label style='font-weight:bold'>Supir</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=supir id=supir onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
			
			<td>
				<label class=label style='font-weight:bold'>Keterangan</label>
				<input tabindex=14 class=myinputtext style='width:80%' type=text id=keterangan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value='' disabled>
			</td>
		</tr>
		</table>
	</td>
	<td style='width:50%'>
		<div id='showgrading' style='display:none'>";
			$tabindx=14;
			$str="select * from ".$dbname.".msgrading where status='1'";
			$res=fetchdata($str);
			if(count($res) > 0){
				echo"<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF'>
					<tr>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Jjg</td>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Persen (%)</td>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
					</tr>";
							
					foreach ($res as $valx) {
						if($valx['jjg']!='' and $valx['persen']!='' and $valx['kg']!=''){
							echo"<tr>
								<td><label class=label>".$valx['deskripsi']."</label></td>";
							if($valx['jjg']!=''){
								$tabindx++;
								echo"<td style='text-align:center'>
									<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='jjg' id='".$valx['kode']."__".$valx['jjg']."' value='' onblur=hitungpr(this.id) placeholder='0'>
								</td>";
							}
							
							if($valx['persen']!=''){
								$tabindx++;
								echo"<td style='text-align:center'>
									<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='persen' id='".$valx['kode']."__".$valx['persen']."' value='' onblur=hitungkg(this.id) placeholder='0'>
								</td>";
							}
							
							if($valx['kg']!=''){
								$tabindx++;
								echo"<td style='text-align:center'>
									<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='kg' id='".$valx['kode']."__".$valx['kg']."' value='' onblur=hitungpr(this.id) placeholder='0'>
								</td>";
							}
							echo"</tr>";
						}
					}
					
				echo"<tr>
						<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>T O T A L</td>
						<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlgrdjjg' value='' placeholder='0' disabled>
						</td>
						<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlgrdpersen' value='' placeholder='0' disabled>
						</td>
						<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlgrdkg' value='' placeholder='0' disabled>
						</td>
					</tr>";
				echo"</table>";
			}else{
				echo"<label style='font-size:20px;font-weight:bold;color:red'>Master data Grading belum ada!!</label>";
			}
		echo"</div>";
		echo"<div id='showsortasi' style='display:none'>";
			$tabindx=14;
			$str="select * from ".$dbname.".mssortasi where status='1'";
			$res=fetchdata($str);
			if(count($res) > 0){
				echo"<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF'>
					<tr>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Persen (%)</td>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
					</tr>";
							
					foreach ($res as $valx) {
						if($valx['persen']!='' and $valx['kg']!=''){
							echo"<tr>
								<td><label class=label>".$valx['deskripsi']."</label></td>";
							if($valx['persen']!=''){
								$tabindx++;
								echo"<td style='text-align:center'>
									<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='persen' id='".$valx['kode']."__".$valx['persen']."' value='' onblur=hitungkg(this.id) placeholder='0'>
								</td>";
							}
							
							if($valx['kg']!=''){
								$tabindx++;
								echo"<td style='text-align:center'>
									<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='kg' id='".$valx['kode']."__".$valx['kg']."' value='' onblur=hitungpr(this.id) placeholder='0'>
								</td>";
							}
							echo"</tr>";
						}
					}
				echo"<tr>
						<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>T O T A L</td>
						<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlsorpersen' value='' placeholder='0' disabled>
						</td>
						<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlsorkg' value='' placeholder='0' disabled>
						</td>
					</tr>";
				echo"</table>";
			}else{
				echo"<label style='font-size:20px;font-weight:bold;color:red'>Master data Sortasi belum ada!!</label>";
			}
		echo"</div>
	</td>
	</tr>
</table>";
CLOSE_BOX();

OPEN_BOX('');
echo"<table border=0 align=center style='max-width:100%'>
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
						<input class=myinputtext type=text name=datein id=datein style='width:85%;font-size:18px;text-align:right;'>
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
						<input class=myinputtext type=text name=dateout id=dateout style='width:85%;font-size:18px;text-align:right;'>
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
				<tr>
					<td valign='middle'>Potongan</td>
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
<script language=javascript1.2 src='js/trx_tbskebun_keluarx.js?v=<?php echo time(); ?>'></script>
<script>
	getSelect2();
	stopreminder();
</script>
<?php
echo close_body();
?>