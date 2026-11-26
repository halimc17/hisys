<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript1.2 src='js/legal_kgpnmodal.js?v=<?=time();?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
$L = $_SESSION['lang'];
$mandatory = "<span style='color: red;'>*</span>";

##deklarasi untuk option##
$optpt = "<option value=''>" . $L['pilihdata'] . "</option>";
$optptsrc = "<option value=''>" . $L['all'] . "</option>";

#PT diambil dari organisasi (tipe='PT'), bukan tabel sendiri
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optpt .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	$optptsrc .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

#tahun: 4 tahun ke belakang s/d 1 tahun ke depan, terbaru di atas
$optTahun = "<option value=''>" . $L['pilihdata'] . "</option>";
$optTahunSrc = "<option value=''>" . $L['all'] . "</option>";
$thnSekarang = (int)date('Y');
for ($th = $thnSekarang + 1; $th >= $thnSekarang - 4; $th--) {
	$sel = ($th == $thnSekarang) ? " selected" : "";
	$optTahun .= "<option value='" . $th . "'" . $sel . ">" . $th . "</option>";
	$optTahunSrc .= "<option value='" . $th . "'>" . $th . "</option>";
}

$optTriwulan = "<option value=''>" . $L['pilihdata'] . "</option>";
$optTriwulanSrc = "<option value=''>" . $L['all'] . "</option>";
$arrTW = array(1 => 'Triwulan I (Januari - Maret)', 2 => 'Triwulan II (April - Juni)', 3 => 'Triwulan III (Juli - September)', 4 => 'Triwulan IV (Oktober - Desember)');
foreach ($arrTW as $k => $v) {
	$optTriwulan .= "<option value='" . $k . "'>" . $v . "</option>";
	$optTriwulanSrc .= "<option value='" . $k . "'>" . $v . "</option>";
}

$optStatus = "<option value=''>" . $L['all'] . "</option>";
$optStatus .= "<option value='0'>Belum Posting</option>";
$optStatus .= "<option value='1'>Sudah Posting</option>";

$optJenis = "<option value='1'>Modal Tetap</option><option value='2'>Modal Kerja</option>";
$optMode = "<option value='TAMBAH'>TAMBAH (+)</option><option value='KURANGI'>KURANGI (-)</option>";

#=================== TABEL TENAGA KERJA (input langsung) ===================
$arrTK = array(
	'lk' => 'Indonesia - Laki-laki',
	'pr' => 'Indonesia - Perempuan',
	'asg' => 'Asing'
);
$tabTK = "";
$tabTK .= "<table cellspacing=1 border=0 class=sortable style='width:100%;'>";
$tabTK .= "<thead><tr class=rowheader>";
$tabTK .= "<td align=center style='width:220px;'>Tenaga Kerja Perusahaan</td>";
$tabTK .= "<td align=center>Total tenaga kerja<br>sebelum periode pelaporan</td>";
$tabTK .= "<td align=center>Tambahan tenaga kerja<br>periode pelaporan</td>";
$tabTK .= "<td align=center>Pengurangan tenaga kerja<br>periode pelaporan</td>";
$tabTK .= "<td align=center>Total tenaga kerja sampai<br>dengan periode pelaporan</td>";
$tabTK .= "</tr></thead><tbody>";
foreach ($arrTK as $kat => $label) {
	$tabTK .= "<tr class=rowcontent>";
	$tabTK .= "<td>" . $label . "</td>";
	foreach (array('sebelum', 'tambahan', 'pengurangan', 'sampai') as $kol) {
		$tabTK .= "<td align=center><input id=tki_" . $kat . "_" . $kol
			. " class=myinputtext style='width:70px;text-align:right;' value='0'"
			. " onkeypress='return angka_doang(event);' onblur=kgpnHitungTK()> Orang</td>";
	}
	$tabTK .= "</tr>";
}
$tabTK .= "<tr class=rowcontent style='font-weight:bold;'>";
$tabTK .= "<td>Jumlah</td>";
foreach (array('sebelum', 'tambahan', 'pengurangan', 'sampai') as $kol) {
	$tabTK .= "<td align=center><span id=tkj_" . $kol . ">0</span> Orang</td>";
}
$tabTK .= "</tr>";
$tabTK .= "<tr class=rowcontent style='font-style:italic;color:#666;'>";
$tabTK .= "<td>Gender tidak diketahui (data SDM belum lengkap)</td>";
foreach (array('sebelum', 'tambahan', 'pengurangan', 'sampai') as $kol) {
	$tabTK .= "<td align=center><input id=tki_unk_" . $kol
		. " class=myinputtext style='width:70px;text-align:right;' value='0'"
		. " onkeypress='return angka_doang(event);' onblur=kgpnHitungTK()> Orang</td>";
}
$tabTK .= "</tr>";
$tabTK .= "</tbody></table>";

#=================== TAB 1 - IDENTITAS & PERIODE ===================
$frm[0] = <<<HTML
<fieldset><legend>Identitas Dokumen &amp; Periode Pelaporan</legend>
<table cellspacing=1 border=0>
	<tr>
		<td style='width:200px;'>{$L['notransaksi']}</td>
		<td style='width:10px;'>:</td>
		<td><input id=notransaksi class=myinputtext style='width:220px;' readonly/></td>

		<td style='width:150px;'>PT {$mandatory}</td>
		<td style='width:10px;'>:</td>
		<td><select class=select2 style="width:280px;" id=kodept onchange=getprofilpt()>{$optpt}</select></td>
	</tr>
	<tr>
		<td>{$L['tahun']} {$mandatory}</td>
		<td>:</td>
		<td><select class=select2 style="width:220px;" id=tahun onchange=kgpnSetPeriode()>{$optTahun}</select></td>

		<td>PERIODE (Triwulan) {$mandatory}</td>
		<td>:</td>
		<td><select class=select2 style="width:280px;" id=triwulan onchange=kgpnSetPeriode()>{$optTriwulan}</select></td>
	</tr>
	<tr>
		<td>Periode Pelaporan Dari {$mandatory}</td>
		<td>:</td>
		<td><input type=text class=myinputtext style='width:220px;' id=periodedari onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 readonly/></td>

		<td>Periode Pelaporan Sampai {$mandatory}</td>
		<td>:</td>
		<td><input type=text class=myinputtext style='width:280px;' id=periodesampai onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 readonly/></td>
	</tr>
	<tr>
		<td>Akumulasi Mulai</td>
		<td>:</td>
		<td colspan=4><input type=text class=myinputtext style='width:220px;' id=akumulasidari onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 readonly/>
			<span style='font-size:11px;color:#555;'>(kosong = awal tahun berjalan)</span></td>
	</tr>
</table>
</fieldset>

<fieldset style='margin-top:6px;'><legend>I. KETERANGAN PERUSAHAAN</legend>
<table cellspacing=1 border=0>
	<tr>
		<td style='width:220px;'>1. Nama Pelaku Usaha/Perusahaan</td>
		<td style='width:10px;'>:</td>
		<td><input id=namaperusahaan class=myinputtext style='width:640px;' readonly/></td>
	</tr>
	<tr>
		<td>2. Nomor Induk Berusaha (NIB)</td>
		<td>:</td>
		<td><input id=nib class=myinputtext style='width:640px;' maxlength=30></td>
	</tr>
	<tr>
		<td>3. Nomor Kegiatan Usaha</td>
		<td>:</td>
		<td><input id=nokegiatanusaha class=myinputtext style='width:640px;' maxlength=60></td>
	</tr>
	<tr>
		<td>4. No Izin</td>
		<td>:</td>
		<td><input id=noizin class=myinputtext style='width:640px;' maxlength=60></td>
	</tr>
	<tr>
		<td>5. Kegiatan Usaha</td>
		<td>:</td>
		<td><input id=kegiatanusaha class=myinputtext style='width:640px;' maxlength=160></td>
	</tr>
	<tr>
		<td>6. Bidang Usaha</td>
		<td>:</td>
		<td><input id=bidangusaha class=myinputtext style='width:640px;' maxlength=160></td>
	</tr>
	<tr>
		<td valign=top>7. Alamat lokasi proyek</td>
		<td valign=top>:</td>
		<td><textarea rows='2' cols='80' maxlength='500' id='alamatproyek' style='width:640px;'></textarea></td>
	</tr>
	<tr>
		<td valign=top>8. Alamat Kantor</td>
		<td valign=top>:</td>
		<td><textarea rows='2' cols='80' maxlength='500' id='alamatkantor' style='width:640px;'></textarea></td>
	</tr>
</table>
</fieldset>
HTML;

#=================== TAB 2 - REALISASI MODAL ===================
$frm[1] = <<<HTML
<fieldset><legend>II. REALISASI PENANAMAN MODAL</legend>
<div style='padding:2px 0 6px 0;'>
	<button class=mybutton onclick=kgpnDtTambah()>+ Tambah Baris</button>
	<button class=mybutton onclick=kgpnGenerate(1)>Generate Modal Tetap dari COA</button>
	<button class=mybutton onclick=kgpnGenerate(2)>Generate Modal Kerja dari Jurnal</button>
	<button class=mybutton onclick=kgpnHapusGenerate()>Hapus Hasil Generate</button>
</div>
<div style='padding:0 0 6px 0;'>
	<input type=checkbox id=sertakan value='1' onclick=kgpnCekSertakan()>
	<label for=sertakan style='font-size:11px;'>Sertakan jurnal yang <b>belum diposting</b> (khusus Modal Tetap)</label>
	<div id=kgpnSertakanInfo style='font-size:11px;color:#990000;padding:2px 0 0 20px;display:none;'>
		Angka Modal Tetap akan memakai seluruh jurnal akun aset apa pun status postingnya.
		Pakai ini kalau jurnal aset memang tidak pernah diposting di modul Keuangan.
		Angka jadi lebih besar dan tidak sama dengan laporan keuangan.
	</div>
</div>
<div style='font-size:11px;color:#555;padding-bottom:4px;'>
	Modal Tetap = akun bertanda Modal Tetap di menu Setup COA (debet jurnal, nilai perolehan).
	Modal Kerja = akun Modal Kerja di menu Setup COA (netto jurnal terposting).
	Generate butuh PT dan periode pada Tab Identitas sudah terisi.
	Baris manual tetap bisa ditambah lewat tombol + Tambah Baris.
</div>
<div id=kgpnDtGrid style='max-height:300px;overflow:auto;'>
</div>

<div id=kgpnFormBaris style='display:none;margin-top:8px;border:1px solid #bbb;padding:6px;background:#FAFAFA;'>
	<b id=kgpnFormJudul>Baris Baru</b>
	<table cellspacing=1 border=0 style='margin-top:4px;'>
		<tr>
			<td style='width:110px;'>Jenis</td><td>:</td>
			<td><select id=dt_jenis style='width:150px;'>{$optJenis}</select></td>
			<td style='width:100px;'>Mode</td><td>:</td>
			<td><select id=dt_mode style='width:150px;'>{$optMode}</select></td>
		</tr>
		<tr>
			<td>Uraian</td><td>:</td>
			<td colspan=4><input id=dt_uraian class=myinputtext style='width:470px;' maxlength=255></td>
		</tr>
		<tr>
			<td>Nilai Periode</td><td>:</td>
			<td><input id=dt_nilaiperiode class=myinputtext style='width:150px;text-align:right;' value='0' onkeypress='return angka_doang(event);'></td>
			<td>Nilai Akumulasi</td><td>:</td>
			<td><input id=dt_nilaiakumulasi class=myinputtext style='width:150px;text-align:right;' value='0' onkeypress='return angka_doang(event);'></td>
		</tr>
		<tr>
			<td colspan=5></td>
			<td>
				<button class=mybutton onclick=kgpnDtSimpanBaris()>Simpan Baris</button>
				<button class=mybutton onclick=kgpnDtTutupBaris()>Batal</button>
				<input type=hidden id=dt_idx value=''>
			</td>
		</tr>
	</table>
</div>

<div id=kgpnPreview style='display:none;margin-top:8px;border:1px solid #4a7;padding:6px;background:#F2FBF5;'></div>

<div style='margin-top:8px;font-size:11px;color:#555;'>
	Perhatian: Apabila perusahaan memiliki lebih dari satu lokasi proyek/bidang usaha, investasi wajib dirinci untuk masing-masing lokasi proyek/bidang usaha.
</div>
<table cellspacing=1 border=0 style='margin-top:6px;'>
	<tr>
		<td valign=top style='width:220px;'>Penjelasan</td>
		<td valign=top style='width:10px;'>:</td>
		<td><textarea rows='5' cols='90' maxlength='1000' id='penjelasan' style='width:640px;'></textarea></td>
	</tr>
</table>
</fieldset>
HTML;

#=================== TAB 3 - TENAGA KERJA ===================
$frm[2] = <<<HTML
<fieldset><legend>III. PENGGUNAAN TENAGA KERJA *)</legend>
<div style='padding:2px 0 6px 0;'>
	<button class=mybutton onclick=kgpnGenerateTK()>Generate dari SDM</button>
</div>
<div id=kgpnTkInfo style='font-size:11px;color:#444;padding-bottom:4px;'></div>
{$tabTK}
<table cellspacing=1 border=0 style='margin-top:8px;'>
	<tr>
		<td style='width:250px;'>Tenaga kerja lokal setempat yang diserap</td>
		<td style='width:10px;'>:</td>
		<td><input id=tklokalsetempat class=myinputtext style='width:80px;text-align:right;' value='0' onkeypress='return angka_doang(event);'> Orang</td>
		<td style='width:100px;'>Catatan</td>
		<td style='width:10px;'>:</td>
		<td><input id=catatantk class=myinputtext style='width:300px;' maxlength=255></td>
	</tr>
</table>
<div style='margin-top:5px;font-size:11px;color:#555;'>
	Jumlah = Laki-laki + Perempuan + Asing. Baris &quot;Gender tidak diketahui&quot; tidak dijumlahkan
	(data SDM belum lengkap)  -  lengkapi jenis kelamin di menu Data Karyawan.
</div>
<div style='margin-top:4px;font-size:11px;color:#555;'>
	*) Tidak termasuk Komisaris dan Direksi, termasuk tenaga kerja kontrak.
	Jabatan yang dikecualikan diatur di tabel lgl_kgpnmodal_5jabatanexclude.
</div>
</fieldset>
HTML;

#=================== TAB 4 - PERMASALAHAN ===================
$frm[3] = <<<HTML
<fieldset><legend>IV. PERMASALAHAN YANG DIHADAPI PERUSAHAAN</legend>
<div style='padding:2px 0 6px 0;'>
	<button class=mybutton onclick=kgpnMsTambah()>+ Tambah Permasalahan</button>
</div>
<div id=kgpnMsGrid style='max-height:300px;overflow:auto;'>
</div>

<div id=kgpnFormMasalah style='display:none;margin-top:8px;border:1px solid #bbb;padding:6px;background:#FAFAFA;'>
	<b id=kgpnMasalahJudul>Permasalahan Baru</b>
	<table cellspacing=1 border=0 style='margin-top:4px;'>
		<tr>
			<td style='width:130px;'>Tanggal</td><td>:</td>
			<td><input type=text class=myinputtext style='width:150px;' id=ms_tanggal onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 readonly></td>
			<td style='width:110px;'>Jenis Masalah</td><td>:</td>
			<td><input id=ms_jenis class=myinputtext style='width:250px;' maxlength=200></td>
		</tr>
		<tr>
			<td valign=top>Detail Masalah</td><td valign=top>:</td>
			<td colspan=4><textarea rows='2' cols='70' maxlength='500' id='ms_detail' style='width:520px;'></textarea></td>
		</tr>
		<tr>
			<td valign=top>Keterangan (Kronologis)</td><td valign=top>:</td>
			<td colspan=4><textarea rows='2' cols='70' maxlength='500' id='ms_keterangan' style='width:520px;'></textarea></td>
		</tr>
		<tr>
			<td colspan=5></td>
			<td>
				<button class=mybutton onclick=kgpnMsSimpanBaris()>Simpan Permasalahan</button>
				<button class=mybutton onclick=kgpnMsTutup()>Batal</button>
				<input type=hidden id=ms_idx value=''>
			</td>
		</tr>
	</table>
</div>
</fieldset>
HTML;

#=================== TAB 5 - PENGESAHAN ===================
$frm[4] = <<<HTML
<fieldset><legend>Pengesahan</legend>
<div style='padding:2px 0 6px 0;font-style:italic;'>Laporan ini disusun dengan sebenarnya</div>
<table cellspacing=1 border=0>
	<tr>
		<td style='width:220px;'>Kota</td>
		<td style='width:10px;'>:</td>
		<td><input id=kota class=myinputtext style='width:250px;' maxlength=60></td>

		<td style='width:110px;'>Tanggal Lapor {$mandatory}</td>
		<td style='width:10px;'>:</td>
		<td><input type=text class=myinputtext style='width:250px;' id=tanggallapor onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 readonly/></td>
	</tr>
</table>
<div style='padding:8px 0 4px 0;'>Direksi/Kuasa Direksi Selaku Penanggung Jawab</div>
<table cellspacing=1 border=0>
	<tr>
		<td style='width:220px;'>Nama Jelas {$mandatory}</td>
		<td style='width:10px;'>:</td>
		<td><input id=namapenandatangan class=myinputtext style='width:250px;' maxlength=100></td>

		<td style='width:110px;'>Jabatan {$mandatory}</td>
		<td style='width:10px;'>:</td>
		<td><input id=jabatan class=myinputtext style='width:250px;' maxlength=100></td>
	</tr>
	<tr>
		<td>No. Telepon</td>
		<td>:</td>
		<td><input id=notelepon class=myinputtext style='width:250px;' maxlength=30></td>

		<td>Email</td>
		<td>:</td>
		<td><input id=email class=myinputtext style='width:250px;' maxlength=100></td>
	</tr>
</table>
</fieldset>

<fieldset style='margin-top:6px;'><legend>Status Dokumen</legend>
<div id=kgpnStatus style='padding:2px 0;font-size:12px;color:#333;'>Dokumen baru  -  belum disimpan.</div>
<div style='margin-top:6px;'>
	<button id=kgpnBtnPosting class=mybutton onclick=kgpnPostingDok() style='display:none;'>Posting Dokumen</button>
	<button id=kgpnBtnUnposting class=mybutton onclick=kgpnUnpostingDok() style='display:none;'>Batalkan Posting</button>
</div>
</fieldset>
HTML;

$hfrm[0] = "1. Identitas &amp; Periode";
$hfrm[1] = "2. Realisasi Modal";
$hfrm[2] = "3. Tenaga Kerja";
$hfrm[3] = "4. Permasalahan";
$hfrm[4] = "5. Pengesahan";

OPEN_BOX('', '<span class=judul>' . getMenu('legal_kgpnmodal') . '</span>');
echo "<div id=action_list>";
echo "<table>
     <tr valign=middle>

	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $L['new'] . "'><br>" . $L['new'] . "</td>

	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $L['list'] . "'><br>" . $L['list'] . "</td>
	 <td>

		<fieldset><legend>" . $L['find'] . "</legend>
         <table>
			<tr>
				<td>" . $L['pt'] . "</td>
				<td>:</td>
				<td><select class=select2 id=ptsrc style=\"width:170px;\">" . $optptsrc . "</select></td>

				<td>" . $L['tahun'] . "</td>
				<td>:</td>
				<td><select class=select2 id=tahunsrc style=\"width:90px;\">" . $optTahunSrc . "</select></td>

				<td>Triwulan</td>
				<td>:</td>
				<td><select class=select2 id=triwulansrc style=\"width:170px;\">" . $optTriwulanSrc . "</select></td>
			</tr>
			<tr>
				<td>" . $L['status'] . "</td>
				<td>:</td>
				<td><select class=select2 id=statussrc style=\"width:170px;\">" . $optStatus . "</select></td>

				<td>" . $L['tanggalmulai'] . "</td>
				<td>:</td>
				<td><input id='tanggalmulai' type='text' style='width:95px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/></td>

				<td>" . $L['tanggalsampai'] . "</td>
				<td>:</td>
				<td><input id='tanggalsampai' type='text' style='width:95px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/></td>
			</tr>
			";

echo "<tr><td></td><td></td><td colspan=7><button class=mybutton onclick=loaddata(0)>" . $L['find'] . "</button></td></tr></table>";
echo "</fieldset></td>
     </tr>
	 </table> ";
echo "</div>";
CLOSE_BOX();

#=================== LIST DOKUMEN ===================
echo "<div id=listData style=display:block>";
OPEN_BOX();
echo "<fieldset>
		<legend>" . $L['list'] . "</legend>
		<div style='overflow-x:auto;'>
		<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $L['nourut'] . "</td>
				<td align=center>" . $L['notransaksi'] . "</td>
				<td align=center>" . $L['pt'] . "</td>
				<td align=center>Periode</td>
				<td align=center>" . $L['tahun'] . "</td>
				<td align=center>TW</td>
				<td align=center>Modal Tetap</td>
				<td align=center>Modal Kerja</td>
				<td align=center>Status Posting</td>
				<td align=center>Dibuat Oleh</td>
				<td align=center>Dibuat Pada</td>
				<td align=center>Diposting Oleh</td>
				<td align=center>Diposting Pada</td>
				<td align=center colspan='3'>" . $L['action'] . "</td>
			</tr>
		</thead>
		 <tbody id=contain>
			<script>loaddata(0)</script>
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
		 </div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";

#=================== FORM 5 TAB ===================
echo "<div id=header style=display:none>";
OPEN_BOX();
#draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 175, '100%');

echo "<table cellspacing=1 border=0 style='margin-top:8px;'>
		<tr>
			<td>
				<button id=tombolSimpan class=mybutton onclick=kgpnSimpanSemua()>" . $L['save'] . "</button>
				<button id=batal class=mybutton onclick=cancel()>" . $L['cancel'] . "</button>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=kgpn_posting value='0'>
			</td>
		</tr>
	</table>";
CLOSE_BOX();
echo "</div>";

echo close_body();
?>
