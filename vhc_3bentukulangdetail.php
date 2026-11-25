<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/vhc_3bentukulangdetail.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('vhc_3bentukulangdetail')."<br></span>");

#Bentuk ulang vhc_rundt_detail transaksi Pekerjaan Kendaraan yang sudah diposting (logika di lib/zDetailPekerjaanTraksi.php).
#Hanya untuk user Sendi.bagaskara; slave memeriksa ulang.
if(strtolower(trim($_SESSION['standard']['username']))!=='sendi.bagaskara'){
	echo "<div style='padding:12px;'>Menu ini hanya dapat dibuka oleh user Sendi.bagaskara.</div>";
	CLOSE_BOX();
	close_body();
	exit;
}

$optunit=$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=fetchdata("select distinct a.kodeorg,b.namaorganisasi from ".$dbname.".vhc_runht a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
	where a.tanggal>=date_sub(curdate(), interval 2 year) order by a.kodeorg");
foreach($res as $bar){
	$optunit.="<option value='".htmlspecialchars($bar['kodeorg'],ENT_QUOTES)."'>".htmlspecialchars($bar['kodeorg']." - ".$bar['namaorganisasi'])."</option>";
}
$res=fetchdata("select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 24");
foreach($res as $bar){
	$optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

echo"<fieldset style='float:left;'>
	<legend>".$_SESSION['lang']['form']."</legend>
	<table>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select class='select2' id=unit style='width:220px;'>".$optunit."</select></td>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select class='select2' id=periode style='width:120px;'>".$optperiode."</select></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td colspan=4>
			<button class=mybutton onclick=cekDetail()>Cek</button>
			<button class=mybutton onclick=prosesDetail()>Proses</button>
			<button class=mybutton onclick=batalDetail()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
	</table>
	<div style='max-width:620px;font-size:11px;color:#555;margin-top:6px;'>
		Membentuk ulang detail jam per blok kecil (vhc_rundt_detail) untuk transaksi Pekerjaan Kendaraan yang sudah diposting,
		dengan logika posting yang sama. Status dan waktu posting tidak berubah. <b>Cek</b> hanya menampilkan transaksi yang akan berubah.
		<b>Proses</b> membentuk ulang detail transaksi yang berubah; periode akuntansi unit harus masih terbuka. Backup database dulu sebelum Proses.
		Sesudah itu jalankan Proses Akhir Bulan no 3 lalu no 8.
	</div>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"<div class='table-scroll' style='height:65vh;overflow:auto;' id=container></div>";
CLOSE_BOX();
close_body();
?>
