<?
//Laporan Daftar Aset dan Penyusutan (Keuangan): daftar aset, hasil penyusutan terposting, nilai buku (sisa nilai), sisa umur
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zTools.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/keu_2dafasetsusut.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$ptLogin = $_SESSION['empl']['kodeorganisasi'];
$optPt = '';
foreach (fetchData("select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi") as $bar) {
	$optPt .= "<option value='" . htmlspecialchars($bar['kodeorganisasi'], ENT_QUOTES) . "'" . ($bar['kodeorganisasi'] == $ptLogin ? ' selected' : '') . ">" . htmlspecialchars($bar['kodeorganisasi'] . ' - ' . $bar['namaorganisasi']) . "</option>";
}
#periode yang dipilih awal = periode terakhir yang sudah tutup buku (penyusutan bulan berjalan biasanya belum diproses); kalau belum ada, periode terbaru
$periodeAwal = '';
$rAwal = fetchData("select max(periode) as p from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1");
if (isset($rAwal[0]['p'])) {
	$periodeAwal = $rAwal[0]['p'];
}
$optPeriode = '';
foreach (fetchData("select distinct periode from " . $dbname . ".setup_periodeakuntansi order by periode desc") as $bar) {
	$optPeriode .= "<option value='" . $bar['periode'] . "'" . ($bar['periode'] == $periodeAwal ? ' selected' : '') . ">" . substr($bar['periode'], 5, 2) . "-" . substr($bar['periode'], 0, 4) . "</option>";
}
$optTipe = "<option value=''>Semua Tipe</option>";
foreach (fetchData("select kodetipe,namatipe from " . $dbname . ".sdm_5tipeasset order by namatipe") as $bar) {
	$optTipe .= "<option value='" . htmlspecialchars($bar['kodetipe'], ENT_QUOTES) . "'>" . htmlspecialchars($bar['namatipe']) . "</option>";
}

OPEN_BOX('', '<span class=judul>' . getMenu('keu_2dafasetsusut') . '</span>');
echo "<style>
	#filterTabelAD td {padding:2px 3px;vertical-align:middle}
	#filterTabelAD td.lbl {white-space:nowrap}
	#filterTabelAD td.fld {width:260px}
</style>
<table><tr valign=top>
	<td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
		<table id=filterTabelAD border=0 cellspacing=1>
			<tr>
				<td class=lbl>PT</td><td>:</td>
				<td class=fld><select id=ptAD class=select2 style='width:100%' onchange=ubahPtAD()>" . $optPt . "</select></td>
				<td class=lbl style='padding-left:15px'>Unit</td><td>:</td>
				<td class=fld><select id=unitAD class=select2 style='width:100%'><option value=''>Semua Unit</option></select></td>
			</tr>
			<tr>
				<td class=lbl>Periode</td><td>:</td>
				<td class=fld><select id=periodeAD class=select2 style='width:100%'>" . $optPeriode . "</select></td>
				<td class=lbl style='padding-left:15px'>Tipe Aset</td><td>:</td>
				<td class=fld><select id=tipeAD class=select2 style='width:100%'>" . $optTipe . "</select></td>
			</tr>
			<tr>
				<td class=lbl>Status</td><td>:</td>
				<td class=fld><select id=statusAD class=select2 style='width:100%'><option value=''>Semua</option><option value='1'>Aktif</option><option value='0'>Tidak Aktif</option></select></td>
				<td class=lbl style='padding-left:15px'>Kode / Nama</td><td>:</td>
				<td class=fld><input type=text id=cariAD class=myinputtext maxlength=40 placeholder='kode atau nama aset' style='width:100%;box-sizing:border-box' onkeypress=\"if(event.keyCode==13){cariAD();}\"></td>
			</tr>
			<tr>
				<td colspan=6 style='padding-top:4px'>
					<button class=mybutton onclick=cariAD()>" . $_SESSION['lang']['find'] . "</button>
					<button class=mybutton onclick=exportExcelAD()>" . $_SESSION['lang']['excel'] . "</button>
					<button class=mybutton onclick=exportPdfAD()>PDF</button>
				</td>
			</tr>
		</table>
	</fieldset></td>
</tr></table>";
CLOSE_BOX();
OPEN_BOX();
echo "<div id=containAD style='overflow:auto;height:68vh;width:100%'></div>";
CLOSE_BOX();
echo "<script>mulaiAD()</script>";
echo close_body();
?>
