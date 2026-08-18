<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('', '<span class=judul>'.getMenu('kebun_2potongbuahdetail').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/generic.js'></script>

<script>
function lihatdetail(tipe,ev) {
	param = 'proses=kodedenda'+'&tipe='+tipe;
	tujuan = 'kebun_slave_2potongbuahdetail_popup.php' + "?" + param;
	width = '600';
	height = '200';
	content = "<fieldset style='height:95%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
	showDialog1('Denda Panen', content, width, height, ev);
}
function level1(karyawanid, tgl1,tgl2, unit,tipe,proses, ev) {
	param = 'karyawanid=' + karyawanid + '&tgl1=' + tgl1 + '&tgl2=' + tgl2 + '&unit=' + unit + '&tipe=' + tipe + '&proses=' + proses;
	tujuan = 'kebun_slave_2potongbuahdetail_popup.php' + "?" + param;
	width = '1000';
	height = '350';
	content = "<fieldset style='height:95%;width:98%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
	showDialog2('Detail Transaksi '+proses, content, width, height, ev);
}

function level2(karyawanid, tgl1,tgl2, unit,tipe,proses, ev) {
	alert(karyawanid);
	// param = 'karyawanid=' + karyawanid + '&tgl1=' + tgl1 + '&tgl2=' + tgl2 + '&unit=' + unit + '&tipe=' + tipe + '&proses=' + proses;
	// tujuan = 'kebun_slave_2potongbuahdetail_popup.php' + "?" + param;
	// width = '1000';
	// height = '350';
	// content = "<fieldset style='height:95%;width:98%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
	// showDialog5('Detail Transaksi '+proses, content, width, height, ev);
}
</script>

<?
$optDiv='';
$optOrg = "<option value=''></option>";
if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
	$where=" and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' ".$where." order by kodeorganisasi asc ";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
}

$arr = "##kdUnit##tgl1##tgl2##divisi";
echo"<fieldset style='float:left;height:100px'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdUnit onchange=getdivisi() style=\"width:164px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi style=\"width:164px;\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
                </tr>
				<tr></tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2potongbuahdetail','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2potongbuahdetail.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"<fieldset style=height:100px>
		<table border=0>
		<tr>
			<td>1</td>
			<td>Kolom <b>" . $_SESSION['lang']['denda'] . " ".$_SESSION['lang']['karyawan']." Rp</b></td>
			<td>:</td>
			<td colspan=4>Adalah total denda yang di potongkan ke Karyawan.</td>
			
		</tr>
		<tr>
			<td>2</td>
			<td>Kolom <b>".$_SESSION['lang']['total']." ".$_SESSION['lang']['denda']." Rp</b></td>
			<td>:</td>
			<td colspan=4>Adalah total denda yang di peroleh berdasarkan perhitungan Fisik x Rp / Denda.</td>
			
		</tr>";
		/* <tr>
			<td>3</td>
			<td colspan=6>Jika ada perbedaan antara <b>" . $_SESSION['lang']['denda'] . " ".$_SESSION['lang']['karyawan']." Rp</b> dengan <b>".$_SESSION['lang']['total']." ".$_SESSION['lang']['denda']." Rp</b> hal tersebut di sebabkan oleh : </td>
			
		</tr>
		<tr>	
			<td></td>
			<td colspan=6><b><i>Jika Denda Panen lebih besar dari Jumlah Premi maka Denda Panen = Jumlah Premi</i></b></td>
			
		</tr> */
echo"	<tr>
			<td>4</td>
			<td colspan=6>Jumlah rupiah pada menu ini hanya bersumber dari transaksi Kegiatan Panen, jadi besar kemungkinan Jumlahnya tidak sama dengan Jumlah di Rekap Gaji pada akhir bulan.</td>
			
		</tr>
		<tr>
			<td>5</td>
			<td>Daftar Kode Denda</td>
			<td>:</td>
			<td style=cursor:pointer onclick=lihatdetail('html',event)><font color=blue>Preview / Download</font></td>
			
		</tr>
		</table>
	 <legend>Info</legend>
	 </fieldset>";
	 
CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'; >
</div></fieldset>";
CLOSE_BOX();
echo close_body();
?>