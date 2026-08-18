<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','');

echo"
<table style=\"width:99.5%;border-collapse:collapse;margin-left:auto;margin-right:auto;\">
	<tr>
		<td>
			<img src='images/E1205web.gif' width=70 height=70 align=absmiddle>
			<strong>
				<font size=5 color=#191919 font-family: Verdana, Arial, Helvetica, sans-serif>Timbang Penerimaan</font>
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
			<button onclick=getReminderData()>P</button>
			<input class=myinputtext type=text name=weight id=weight style='background-color:#2AFFD4;width:350px;height:60px;font-size:50px;text-align:right' maxlength=7 value='0' onkeypress='return false;' disabled>
		</td>
	</tr>
</table>";
CLOSE_BOX();

OPEN_BOX('');
$optkontrak=$optproduk="<option value=''>Silahkan pilih</option>";
## GET PRODUK
$str="select * from ".$dbname.".msproduk where kriteria='Penerimaan'";
$res=fetchdata($str);
foreach ($res as $val) {
    $optproduk.="<option value='".$val['kodeproduk']."'>".$val['namaproduk']."</option>";
}

echo"
<legend><b>Input data</b></legend>
<table border=0 cellpadding=3 style=width:100%>
	<tr>
	<td style=vertical-align:top;width:50%>
		<table class=viewtable border=0 align=left style='width:90%'>
		<tr>
			<td style='width:50%'>
				<label class=label>No Tiket</label>
				<input tabindex=1 class=myinputtext type=text name=ticketno id=ticketno style='width:80%;background-color:#2AFFD4;font-size:22px' value='' disabled>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label>Produk</label>
				<select class='select2' style='width:85%;height:32px;' id='kodeproduk' tabindex=1>".$optproduk."</select>
			</td>

			<td>
				<label class=label>No Kendaraan</label>
				<input tabindex=6 class=myinputtext style='width:80%' type=text name=nokendaraan id=nokendaraan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>

		<tr>
		    <td>
		        <label class=label>No SPB</label>
		        <input tabindex=2 class=myinputtext style='width:80%' type=text name=nospb id=nospb onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
		    </td>

		    <td>
				<label class=label>Supir</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=supir id=supir onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label>No Kontrak</label>
				<select class='select2' style='width:85%;height:32px;' id='nokontrak' tabindex=3>".$optkontrak."</select>
			</td>

			<td>
				<label class=label>Keterangan</label>
				<input tabindex=7 class=myinputtext style='width:80%' type=text name=deliverynote id=deliverynote onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
			
		</tr>

		<tr>
			<td>
				<label class=label>No PO</label>
				<input tabindex=4 class=myinputtext style='width:80%' type=text name=noproductionorder id=noproductionorder onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>

		<tr>
			<td>
				<label class=label>Transportir</label>
				<input tabindex=5 class=myinputtext style='width:80%' type=text name=transportir id=transportir onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' value=''>
			</td>
		</tr>
		</table>
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td id=tdsortasi style=vertical-align:top;display:none;>
		<fieldset style=width:500px><legend>Sortasi</legend>
				<table cellspacing=4 style='border:1px solid #FFFFFF'>
                    <tr>
                        <td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
                        <td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>%</td>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
                    </tr>";
                    	$rowsortasi = count($resx);
                    	foreach ($resx as $valx) {
                    		echo "
                    		<tr>
                    			<td style=width:50%><label class=label>".$valx['kriteria']."</label></td>
                    			<td><input class=myinputtext type=text style=text-align:right; onkeypress='return angka_doang(event);' name=persensortasi".$valx['id']." id=persensortasi".$valx['id']." value='' onblur=hitungsortasi(this.id)></td>
                    			<td><input class=myinputtext type=text style=text-align:right; onkeypress='return angka_doang(event);' name=beratsortasi".$valx['id']." id=beratsortasi".$valx['id']." value='' disabled></td>
                    		</tr>
                    		";
                    	}
                    	echo"<td><input id=rowsortasi value=$rowsortasi hidden></td>";
                    echo "</table>
		</fieldset>
	</td>

	<td id=tdkualitas style=vertical-align:top;display:none;>
		<fieldset style=width:350px><legend>Kualitas</legend>
				<table cellspacing=4 style='border:1px solid #FFFFFF'>
                    <tr>
                        <td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kualitas</td>
						<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Nilai</td>
                    </tr>";
                    	$rowkualitas = count($resy);
                    	foreach ($resy as $valy) {
                    		echo "
                    		<tr>
                    			<td style=width:50%><label class=label>".$valy['kualitas']."</label></td>
                    			<td><input class=myinputtext type=text style=text-align:right; onkeypress='return angka_doang(event);' name=nilaikualitas".$valy['id']." id=nilaikualitas".$valy['id']." value=''></td>
                    		</tr>
                    		";
                    	}
                    	echo"<td><input id=rowkualitas value=$rowkualitas hidden></td>";
                    echo "</table>
		</fieldset>
	</td>
	</td>
	</tr>
</table>
";
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
				<tr>
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

<script language=javascript1.2 src='js/trx_in.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/trx_generic.js?v=<?php echo time(); ?>'></script>

<?php
echo close_body();
?>