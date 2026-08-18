<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/kebun_rekapangkutantbs.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>

<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth: true
		});
	});

	$(document).on('focus', '.select2-selection.select2-selection--single', function(e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>
<?php
#Flow transaksi Kebun - Transaksi - Rekap Angkutan TBS:
# 1. Buat Pengajuan SPK  tipe ANGKUTTBS, Hanya header (ajukan dan setujui)
# 2. Tarik di menu SPK
# 3. Buat transaksi ini
# 4. Posting transaksi ini maka akan terinsert otomatis ke :
#	4.1 table log_spkdt, log_baspk dan log_baspkdt
#	4.2 update table log_spkht pada kolom nilai kontrak
# 5. Masuk ke menu BAPP ajukan (tunggu di setujui)
# 6. Posting BAPP
# 7. saat unposting maka
#	7.1 hapus transaksi pada log_baspk dan log_baspkdt


$where = $wh = "";
$str = "SELECT * FROM " . $dbname . ".admin_list where username='" . $_SESSION['standard']['username'] . "'";
$adm = fetchData($str);
if (count($adm) == 0) {
	$wh = " and kodeorganisasi = '" . $_SESSION['empl']['lokasitugas'] . "'";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
	$wh = "";
} else if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
	$wh = "";
} else {
	$wh = " and kodeorganisasi = '" . $_SESSION['empl']['lokasitugas'] . "'";
}
$wh = " and kodeorganisasi = '" . $_SESSION['empl']['lokasitugas'] . "'";


# Organisasi
$optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optorg2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' and kodeorganisasi in (" . getOrgDetail(2) . ")";
$res = fetchData($str);
foreach ($res as $key => $val) {
	if ($_SESSION['empl']['lokasitugas'] == $val['kodeorganisasi']) {
		$optorg .= "<option value=" . $val['kodeorganisasi'] . " selected >" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
		$optorg2 .= "<option value=" . $val['kodeorganisasi'] . " selected >" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
	} else {
		$optorg .= "<option value=" . $val['kodeorganisasi'] . ">" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
	}
}

$optprd = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optprdscr = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sql = "SELECT distinct(substr(tanggal,1,7)) as periode FROM " . $dbname . ".kebun_spbht order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optprd .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
	$optprdscr .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$optsupp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT a.* FROM " . $dbname . ".log_spkht a 
left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi 
where a.posting='0' and b.jenis='ANGKUTTBS' and a.kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' order by a.notransaksi asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
$namasupp = array();
while ($bar = $qry->fetch()) {
	$namasupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $bar['koderekanan'] . "'");

	$optsupp .= "<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
}

#============================================================================================#

$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN'  ";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optunit .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
}



OPEN_BOX('', '<span class=judul>' . getMenu('kebun_rekapangkutantbs') . '</span>');
echo "<div id=action_list>"; //buka div
echo "<table>
     <tr valign=middle>	 
		<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
		<td>
            <fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
    <table>
	<tr>
		<td>" . $_SESSION['lang']['unit'] . "</td> 
		<td>:</td>
		<td><select class=select2 id=divsch  style=\"width:150px;\">" . $optorg . "</select></td>
		
		<td>" . $_SESSION['lang']['spk'] . "</td> 
		<td>:</td>
		<td><input class=myinputtext id=nospkcr onkeypress='enterkey(event,loaddata)' style=\"width:150px;\"></td>
		
		<td>" . $_SESSION['lang']['nospb'] . "</td> 
		<td>:</td>
		<td><input class=myinputtext id=nospbcr onkeypress='enterkey(event,loaddata)' style=\"width:150px;\"></td>
		
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['bulan'] . "</td> 
		<td>:</td>
		<td><select class=select2 id=tglsch  style=\"width:150px;\">" . $optprdscr . "</select></td>
		
		<td>" . $_SESSION['lang']['kontraktor'] . "</td> 
		<td>:</td>
		<td><input class=myinputtext id=kontrakcr onkeypress='enterkey(event,loaddata)' style=\"width:150px;\"></td>
		
		<td>" . $_SESSION['lang']['nobaspk'] . "</td> 
		<td>:</td>
		<td><input class=myinputtext id=bappcr onkeypress='enterkey(event,loaddata)' style=\"width:150px;\"></td>
		
	</tr>";
echo "<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo "</fieldset></td>
	</tr></table> ";
CLOSE_BOX();
echo "</div>";
echo "<div id=listData style=display:block>"; #style=display:block
OPEN_BOX();
echo "
	<div class='table-scroll'>";
$arrjenis = array('muat', 'angkut');
$arrtujuan = array('tphpks' => 'TPH - PKS', 'rampks' => 'RAMP - PKS');
$arrmuat = array('tphpks1' => 'TPH-PKS 1', 'tphpks2' => 'TPH-PKS 2', 'tphpks3' => 'TPH-PKS 3', 'tphpks4'  => 'TPH-PKS 4', 'tphpks5'  => 'TPH-PKS 5', 'tphpks6'  => 'TPH-PKS 6', 'tphpks7'  => 'TPH-PKS 7');
echo "<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['unit'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['bulan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['periode'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['spk'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kontraktor'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['notifbrutto'] . "</th>
            <th align=center rowspan='2'>Potongan<br>Brondolan<br>(Kg)</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['notifbrutto'] . "</th>";


foreach ($arrjenis as $jenis) {
	echo "<th align=center colspan='" . (count($arrmuat) + 1) . "'>" . $jenis . "</th>";
}
echo "<th align=center rowspan='2'>" . $_SESSION['lang']['potongan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['rupiah'] . "</th>
            <th align=center rowspan='2'>No BAPP</th>
            <th align=center rowspan='2' colspan=7>" . $_SESSION['lang']['action'] . "</th>
        </tr>";
echo "<tr>";
foreach ($arrjenis as $jenis) {
	foreach ($arrmuat as $keytujuan => $valtujuan) {
		echo "<th align=center>" . $valtujuan . "</th>";
	}
	echo "<th align=center>Tambahan</th>";
}
echo "</tr>";
echo "</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
		</table>
		</div>";
CLOSE_BOX();
echo "</div>";
echo "<div id=header style=display:none>"; #style=display:none
OPEN_BOX();
$res = array('1' => '1 (Pertama) ', '2' => '2 (Kedua) ', '3' => '3 (Ketiga) ', '4' => '4 (Keempat)', '5' => '5 (Kelima)');
// $res=array('0'=>'Sebulan (Tanggal : 1 s/d 30)','1'=>'Pertama (Tanggal : 1 s/d 15)','2'=>'Kedua (Tanggal : 16 s/d 30)');

$optbyr = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach ($res as $key => $val) {
	$optbyr .= "<option value=" . $key . ">" . $val . "</option>";
}

echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
    <tr>
		<td style=\"width:100px;\">" . $_SESSION['lang']['kodeorg'] . "</td> 
		<td>:</td>
		<td><select class=select2 style=\"width:230px;\" id=kodeorg>" . $optorg2 . "</select></td>
    </tr> 
    <tr>
		<td>" . $_SESSION['lang']['bulan'] . "</td> 
		<td>:</td>
		<td><select class=select2 style=\"width:230px;\" id=periode onchange=getnospk()>" . $optprd . "</select></td>
    </tr>
	<tr>
		<td>" . $_SESSION['lang']['periode'] . "</td> 
		<td>:</td>
		<td><input type='text' readonly=readonly style='width:100px;' class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false'; /> s/d <input type='text' readonly=readonly style='width:100px;' class='myinputtext' id='tglselesai' onmousemove='setCalendar(this.id)' onkeypress='return false'; /></td>
    </tr>
	<tr>
		<td>" . $_SESSION['lang']['termin'] . "</td> 
		<td>:</td>
		<td><select class=select2 style=\"width:230px;\" id=periodebyr >" . $optbyr . "</select></td>
    </tr>
	<tr style=display:none>
		<td>" . $_SESSION['lang']['tanggal'] . "</td> 
		<td>:</td>
		<td><input type=text class=myinputtext placeholder='Seluruhnya' id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:145px;\" readonly/></td>
		
    </tr>
	<tr>
		<td>No SPK</td> 
		<td>:</td>
		<td><select onchange=getnopol() class=select2 style=\"width:230px;\" id=spk>" . $optsupp . "</select>
			</td>
    </tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['save'] . "</button>
			<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
		<input type=hidden id=method value='insert'>
	</tr>
</table>
</fieldset>";

echo "<div id=tempnopol></div>";
CLOSE_BOX();
echo "</div>";
echo "<div id=detail style='display:none;';>";
OPEN_BOX();
CLOSE_BOX();
echo "</div>";
echo close_body();
?>