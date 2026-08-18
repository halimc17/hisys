<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/pabrik_5db_komponen.js'></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5db_komponen').'</span>');

//Get Tipe Dokumen
$optTipe.= "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kode, nama from ".$dbname.".pabrik_5db_ksf order by kode asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optTipe.= "<option value='".$bar['kode']."'>".$bar['nama']."</option>";
}

$_SESSION['mswarna'] = array();

echo"<fieldset>
	<legend>Form</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select id='tipe'>".$optTipe."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kode']."</td>
			<td>:</td>
			<td>
				<input id=kode class=myinputtext maxlength='3'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nama']."</td>
			<td>:</td>
			<td>
				<input id=nama class=myinputtext maxlength='30'>
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top'>".$_SESSION['lang']['warna']."</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<table>
					<thead>
					<tr>
						<td style='text-align:center'>".$_SESSION['lang']['warna']."</td>
						<td style='text-align:center'>Operator Awal</td>
						<td style='text-align:center'>Nilai Awal</td>
						<td style='text-align:center'>Operator Akhir</td>
						<td style='text-align:center'>Nilai Akhir</td>
						<td style='text-align:center'>".$_SESSION['lang']['keterangan']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
					</tr>
					</thead>
					<tbody id='newmaster'>
					</tbody>
					<tr>
						<td style='vertical-align:top'>
							<input disabled type=text class=myinputtext id=kodefill name=kode onkeypress=\"return tanpa_kutip(event);\" style=\"width:50px;background:#FFFFFF\" />
							<img  class=resicon src=images/color_fill.png style=position:relative;top:5px title='".$_SESSION['lang']['find']."' onclick=cariwarna(event)>
						</td>
						<td style='text-align:center;vertical-align:top'>
							<select id='operationawal'>
								<option value='='>=</option>
								<option value='<='><=</option>
								<option value='>='>>=</option>
								<option value='<'><</option>
								<option value='>'>></option>
							</select>
						</td>
						<td style='vertical-align:top'>
							<input id=nilaiawal class=myinputtextnumber onKeyPress=\"return angka_doang(event);\" onblur=\"display_number(this.id,event);\" value=0 style=\"width:80px;\" >
						</td>
						<td style='text-align:center;vertical-align:top'>
							<select id='operationakhir'>
								<option value='='>=</option>
								<option value='<='><=</option>
								<option value='>='>>=</option>
								<option value='<'><</option>
								<option value='>'>></option>
							</select>
						</td>
						<td style='vertical-align:top'>
							<input id=nilaiakhir class=myinputtextnumber onKeyPress=\"return angka_doang(event);\" onblur=\"display_number(this.id,event);\" value=0 style=\"width:80px;\" >
						</td>
						<td style='vertical-align:top'>
							<textarea id=keterangan></textarea>
						</td>
						<td style='text-align:center;vertical-align:top'>
							<img title='Tambah' class=resicon onclick=\"addwarna()\" src='images/plus.png'/>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type='hidden' id='method' value='insert'>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><div id=anggota style='display:none'></td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
	<table cellpading=1 cellspacing=1 border=0 class=sortable >
		<thead>
		<tr align=center>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>".$_SESSION['lang']['kode']."</td>
			<td>".$_SESSION['lang']['nama']."</td>
			<td>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
			<tfoot id='footData'>
			</tfoot>
		</tbody>
	</table>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>

