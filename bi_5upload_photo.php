<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/bi_5upload_photo.js'></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('Business inteligent Upload photo Doc.').'</span>');

//Get Tipe Dokumen
$optTipeDokumen = $optKegiatan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select id_tipedok, nama_tipe from ".$dbname.".bi_5tipedok order by nama_tipe asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optTipeDokumen .= "<option value='".$bar['id_tipedok']."'>".$bar['nama_tipe']."</option>";
}

$_SESSION['nodokphoto'] = array();
echo"<fieldset>
	<legend>Form</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['tipedokumen']."</td>
			<td>:</td>
			<td>
				<select id=tipedok style='width:150px;' onchange='getkegiatan()'>".$optTipeDokumen."</select>
				<img id=tipepeta_find onclick=\"z.elSearch('tipedok',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kegiatan']."</td>
			<td>:</td>
			<td>
				<select id=kegiatan onchange='getnodok()'>".$optKegiatan."</select>
				<img id=tipepeta_find onclick=\"z.elSearch('kegiatan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nodok']."</td>
			<td>:</td>
			<td>
				<select id=nodok>".$optKegiatan."</select>
				<img id=tipepeta_find onclick=\"z.elSearch('nodok',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top'>".$_SESSION['lang']['photo']."</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<table>
					<thead>
					<tr>
						<td style='text-align:center'>".$_SESSION['lang']['namafile']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
					</tr>
					</thead>
					<tbody id='newmaster'>
					</tbody>
					<tr>
						<td>
							<input name=upload type=file id=upload size=25 class=mybutton>
						</td>
						<td style='text-align:center'>
							<img title='Tambah' class=resicon onclick=\"adddok()\" src='images/plus.png'/>
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
	<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:60%>
		<thead>
		<tr align=center>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['tipedokumen']."</td>
			<td>".$_SESSION['lang']['kegiatan']."</td>
			<td>".$_SESSION['lang']['nodok']."</td>
			<td colspan=3>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id='container'>
		</tbody>
	</table>
	<script>loaddata()</script>";
CLOSE_BOX();
echo close_body();
?>