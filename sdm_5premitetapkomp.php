<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include_once('lib/zLib.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zTools.js"></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth: true
		});
	});
</script>
<script language=javascript1.2 src='js/sdm_5premitetapkomp.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
#Setup Komponen Tetap (khusus PT PPP): nominal tetap per bulan per karyawan untuk komponen gaji tertentu (pendapatan maupun potongan), semua unit PPP.
#Tampilan Buat Baru + List Data: List Data per organisasi (unit) dan per komponen; Buat Baru / Buka = header (organisasi + komponen) lalu detail (karyawan + nominal).
#Penggajian Bulanan/Harian menarik setup aktif otomatis setiap bulan sebagai komponen yang dipilih.
$unit = $_SESSION['empl']['lokasitugas'];
if (getindukPT($unit) != 'PPP') {
	OPEN_BOX('', '<span class=judul>' . getMenu('sdm_5premitetapkomp') . '</span>');
	echo "<div style='padding:8px'>Menu ini khusus PT PPP.</div>";
	CLOSE_BOX();
	echo close_body();
	exit;
}

$pilih = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$semua = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

#organisasi (unit) milik PT PPP
$optUnit = '';
foreach (fetchData("select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='PPP' order by kodeorganisasi") as $bar) {
	$optUnit .= "<option value='" . htmlspecialchars($bar['kodeorganisasi'], ENT_QUOTES) . "'>" . htmlspecialchars($bar['kodeorganisasi'] . " - " . $bar['namaorganisasi']) . "</option>";
}

#komponen tetap per karyawan: pendapatan (mis. Premi Tetap) dan potongan (mis. Iuran Koperasi); komponen bertipe basic diatur di Setup Upah, komponen terkunci (lock) milik sistem
$kelompokKomp = array(1 => '', 0 => '');
foreach (fetchData("select id,name,plus from " . $dbname . ".sdm_ho_component where `lock`=0 and type<>'basic' order by trim(name), id") as $bar) {
	$kelompokKomp[(int)$bar['plus']] .= "<option value='" . $bar['id'] . "'>" . htmlspecialchars(trim($bar['name'])) . " (" . $bar['id'] . ")</option>";
}
$optKomponenGrup = "<optgroup label='Pendapatan'>" . $kelompokKomp[1] . "</optgroup><optgroup label='Potongan'>" . $kelompokKomp[0] . "</optgroup>";

OPEN_BOX('', '<span class=judul>' . getMenu('sdm_5premitetapkomp') . '</span>');
echo "<style>
	#formHeaderKT td.lbl, #formDetailKT td.lbl {white-space:nowrap}
	#formHeaderKT td.lbl {width:110px}
	#formHeaderKT td.sep, #formDetailKT td.sep {width:10px;text-align:center}
	#formHeaderKT td, #formDetailKT td {padding:3px 4px;vertical-align:middle}
	#filterTabelKT td {padding:2px 3px;vertical-align:middle}
	#filterTabelKT td.lbl {white-space:nowrap}
	#filterTabelKT td.fld {width:260px}
	#ringkasDetailKT {font-size:12px;margin:6px 0 4px 0}
	#barDetailKT {display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin:2px 0 4px 0;font-size:12px}
	#containDetailKT input.myinputtextnumber {width:150px}
	#containDetailKT tr.belumKT td {color:#555}
	.rpKT {display:inline-block;width:22px}
</style>
<input type=hidden id=unitAktifKT value=''>
<input type=hidden id=komponenAktifKT value=''>
<div>
	<table cellspacing=1 border=0>
		<tbody>
			<tr valign=middle>
				<td style=width:100px;cursor:pointer; onclick=\"formbaruKT();\" align=center>
					<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>
					" . $_SESSION['lang']['new'] . "
				</td>
				<td style=width:100px;cursor:pointer; onclick=\"displayListKT();\" align=center>
					<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>
					" . $_SESSION['lang']['list'] . "
				</td>
				<td id=listcariKT style='display:block'>
					<fieldset style='width:auto;'>
						<legend>" . $_SESSION['lang']['find'] . " Data</legend>
							<table id=filterTabelKT cellspacing=1 border=0>
								<tr>
									<td class=lbl>Organisasi</td><td>:</td>
									<td class=fld><select id=unitCariKT class=select2 onchange='loaddataKT()' style='width:100%'>" . $semua . $optUnit . "</select></td>
									<td class=lbl style='padding-left:15px'>Komponen</td><td>:</td>
									<td class=fld><select id=komponenCariKT class=select2 onchange='loaddataKT()' style='width:100%'>" . $semua . $optKomponenGrup . "</select></td>
								</tr>
								<tr>
									<td class=lbl>Status</td><td>:</td>
									<td class=fld><select id=aktifCariKT class=select2 onchange='loaddataKT()' style='width:100%'>" . $semua . "<option value='1'>Aktif</option><option value='0'>Tidak Aktif</option></select></td>
									<td class=lbl style='padding-left:15px'>Karyawan</td><td>:</td>
									<td class=fld><input type=text id=txtCariKT class=myinputtext onkeyup='loaddataKT()' placeholder='NIK / nama' maxlength=40 style='width:100%;box-sizing:border-box'></td>
								</tr>
								<tr>
									<td colspan=6 style='padding-top:4px'>
										<button class=mybutton onclick=loaddataKT()>" . $_SESSION['lang']['find'] . "</button>
										<button class=mybutton onclick=exportExcelKT()>" . $_SESSION['lang']['excel'] . "</button>
										<button class=mybutton onclick=exportPdfKT()>PDF</button>
										<button class=mybutton onclick=batalcariKT()>" . $_SESSION['lang']['cancel'] . "</button>
									</td>
								</tr>
							</table>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>
</div>";
CLOSE_BOX();

#================ List Data: per organisasi dan per komponen ================
echo "<div id=listDataKT>";
OPEN_BOX();
echo "<div class=table-scroll style='max-height:60vh;'>
	<table cellspacing=1 class=sortable cellpadding=5 width=99%>
		<thead>
			<tr class=rowheader>
				<th align=center style='width:40px'>" . $_SESSION['lang']['nourut'] . "</th>
				<th align=center>Organisasi</th>
				<th align=center>Komponen</th>
				<th align=center>Jenis</th>
				<th align=center>Jumlah Karyawan</th>
				<th align=center>Karyawan Aktif</th>
				<th align=center>Total Aktif / Bulan</th>
				<th align=center style='width:70px' colspan=2>" . $_SESSION['lang']['action'] . "</th>
			</tr>
		</thead>
		<tbody id=containKT></tbody>
	</table>
</div>";
CLOSE_BOX();
echo "</div>";

#================ Buat Baru / Buka: header (organisasi + komponen), lalu detail (karyawan) ================
echo "<div id=formnewKT style='display:none'>";
OPEN_BOX();
echo "<fieldset style='width:auto;min-width:470px;float:left'><legend>" . $_SESSION['lang']['header'] . "</legend>
	<table id=formHeaderKT border=0 cellpadding=0>
		<tr>
			<td class=lbl>Organisasi</td><td class=sep>:</td>
			<td><select id=unitKT class=select2 style='width:320px'>" . $pilih . $optUnit . "</select></td>
		</tr>
		<tr>
			<td class=lbl>Komponen</td><td class=sep>:</td>
			<td><select id=komponenKT class=select2 style='width:320px'>" . $pilih . $optKomponenGrup . "</select></td>
		</tr>
		<tr><td></td><td></td><td>
			<button class=mybutton id=tombolBukaKT onclick=bukaHeaderKT()>Lanjut ke Detail</button>
			<button class=mybutton onclick=displayListKT()>" . $_SESSION['lang']['cancel'] . "</button>
		</td></tr>
	</table>
</fieldset>";
CLOSE_BOX();
echo "</div>";

echo "<div id=detailKT style='display:none'>";
OPEN_BOX();
echo "<fieldset><legend id=legendDetailKT>Detail Karyawan</legend>
	<div id=barDetailKT>
		<span>Cari: <input type=text id=cariDetailKT class=myinputtext placeholder='NIK / nama / jabatan' onkeyup='saringDetailKT()' style='width:200px'></span>
		<label><input type=checkbox id=hanyaSetupKT onclick='saringDetailKT()'> Hanya yang sudah disetup</label>
		<button class=mybutton onclick=simpanSemuaKT()>" . $_SESSION['lang']['save'] . "</button>
		<button class=mybutton title='Muat ulang dari data tersimpan' onclick=loadDetailKT()>Refresh</button>
		<button class=mybutton onclick=displayListKT()>Kembali ke List</button>
	</div>
	<div id=ringkasDetailKT></div>
	<div class=table-scroll style='max-height:62vh;overflow:auto'>
	<table class=sortable cellspacing=1 cellpadding=5 border=0 style='width:100%'>
		<thead>
			<tr class=rowheader>
				<td align=center style='width:40px'>No</td>
				<td align=center>NIK</td>
				<td align=center>Nama Karyawan</td>
				<td align=center>Jabatan</td>
				<td align=center style='width:190px'>Nominal / Bulan (Rp)</td>
				<td align=center style='width:60px'>Aktif</td>
				<td align=center style='width:150px'>Waktu Diubah</td>
				<td align=center style='width:50px'>" . $_SESSION['lang']['action'] . "</td>
			</tr>
		</thead>
		<tbody id=containDetailKT></tbody>
	</table>
	</div>
	<div style='font-size:11px;color:#555;margin-top:4px'>Isi nominal pada karyawan yang berhak, lalu klik Simpan. Nominal yang dikosongkan tidak diproses (karyawan tidak disetup). Untuk menghentikan, hilangkan centang Aktif atau klik ikon hapus.</div>
</fieldset>";
CLOSE_BOX();
echo "</div>";
echo "<script>loaddataKT()</script>";
echo close_body();
?>
