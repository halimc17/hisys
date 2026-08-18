<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript src='js/pmn_spk_tkbm.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/pmn_spk_print.js?v=<?php echo time(); ?>'></script>

<?php
// OPEN_BOX('','<span class=judul>SPK BURUH BONGKAR MUAT</span><br>');

$nokontrak = $_GET['nokontrak'];
$tanggalkontrak = $_GET['tanggal'];
$kodecustomer = $_GET['kodecustomer'];
$kodebarang = $_GET['kodebarang'];
$kodept = $_GET['kodept'];
$jenis = $_GET['kdjenis'];

$str = "select * from " . $dbname . ".pmn_5jenisspk where kode='" . $jenis . "'";
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$table = $bar['file'];
$namajenis = $bar['nama'];


OPEN_BOX('', '<span class=judul>' . strtoupper($namajenis) . '</span><br><br>');

$optbuyer = $optpelayaran = $optbongkarmuat = $optkapal = $optponton = $optfranco = $optttd = $optsurveyor = $optbarang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

#= ambil data dari IPK
$str = "select * from " . $dbname . ".pmn_spk_ipk where nokontrak='" . $nokontrak . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$transportir = $bar['transportir'];
$namaponton = $bar['namaponton'];
$namakapal = $bar['namakapal'];
$kuantitas = $bar['kuantitas'];
$pelabuhanmuat = $bar['pelabuhanmuat'];
$pelabuhantujuan = $bar['pelabuhantujuan'];

$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "' order by namacustomer asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optbuyer .= "<option selected value='" . $bar['kodecustomer'] . "'>" . $bar['namacustomer'] . "</option>";
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('TRANSPORTIR') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$selected = '';
	if ($bar['supplierid'] == $transportir) {
		$selected = "selected";
	}
	$optpelayaran .= "<option value=" . $bar['supplierid'] . " " . $selected . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}

// $str = "SELECT * FROM " . $dbname . ".pmn_5kapalponton  where transportir='".$transportir."' and kode in ('".$namakapal."','".$namaponton."')";
$str = "SELECT * FROM " . $dbname . ".pmn_5kapalponton";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$selected = '';

	if ($bar['jenis'] == 'KPL' or 'TRK') {
		if ($bar['kode'] == $namakapal) {
			$selected = "selected";
		}
		$optkapal .= "<option value=" . $bar['kode'] . " " . $selected . ">" . $bar['nama'] . "</option>";
	}

	if ($bar['jenis'] == 'PNT') {
		if ($bar['kode'] == $namaponton) {
			$selected = "selected";
		}
		$optponton .= "<option value=" . $bar['kode'] . " " . $selected . ">" . $bar['nama'] . "</option>";
	}
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('JASABONGKARMUAT') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optbongkarmuat .= "<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('JASAANALISA') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optsurveyor .= "<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}

$str = "select * from " . $dbname . ".pmn_5franco order by franco_name asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optfranco .= "<option value='" . $bar['id_franco'] . "'>" . $bar['franco_name'] . "</option>";
}

$str = "select * from " . $dbname . ".datakaryawan where tanggalkeluar='0000-00-00' and tipekaryawan in ('0','7','8','9') order by namakaryawan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optttd .= "<option value='" . $bar['karyawanid'] . "'>" . $bar['nik'] . " " . $bar['namakaryawan'] . "</option>";
}

$str = "select * from " . $dbname . ".log_5masterbarang where kodebarang='" . $kodebarang . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optbarang .= "<option selected value='" . $bar['kodebarang'] . "'>" . $bar['namabarang'] . "</option>";
}



//print_r($_SESSION['empl']['regional']);
// echo"<fieldset style='width:450px;'>";
echo "<fieldset style='float:left;'>";
echo "<legend>" . $_SESSION['lang']['form'] . "</legend>";
echo "<table border=0 cellpadding=1 cellspacing=1>
                 
				<tr>
					<td>" . $_SESSION['lang']['NoKontrak'] . "</td>
					<td>:</td>		
					<td>
						<input type=text id=nokontrak value='" . $nokontrak . "' size=20 disabled class=myinputtext style=\"width:150px;\">
					</td>
					
					<td>" . $_SESSION['lang']['nospk'] . "</td>
					<td>:</td>		
					<td>
						<input type=text id=nospk size=20 placeholder='Otomatis' disabled class=myinputtext style=\"width:150px;\">
					</td>
					
					<td hidden>" . $_SESSION['lang']['rupiah'] . "</td>
					<td hidden>:</td>		
					<td hidden>
						<input type=text  id=rupiah  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					</td>
					
					
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['kodept'] . "</td>
					<td>:</td>		
					<td>
						<input type=text id=kodept value='" . $kodept . "' size=20 disabled class=myinputtext style=\"width:150px;\">
					</td>
					
					<td>" . $_SESSION['lang']['jenis'] . "</td>
					<td>:</td>		
					<td>
						<input type=text id=jenis value='" . $jenis . "' size=20 disabled class=myinputtext style=\"width:150px;\">
					</td>
					
				</tr>
				 <tr>
					<td>" . $_SESSION['lang']['tglKontrak'] . "</td>
					<td>:</td>		
					<td>
						<input type=text id=tanggalkontrak size=20 disabled  value='" . $tanggalkontrak . "'  class=myinputtext style=\"width:150px;\">
					</td>
					
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:150px;\">
					</td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['Pembeli'] . "</td>
					<td>:</td>		
					<td>
						<select id=kodecustomer disabled value='" . $kodecustomer . "'  style=\"width:154px;\">'" . $optbuyer . "'</select>
					</td>
					
					<td>" . $_SESSION['lang']['transportir'] . "</td>
					<td>:</td>		
					<td>
						<select id=transportir style=\"width:154px;\">'" . $optpelayaran . "'</select>
						<img id='transportir' onclick=z.elSearch('transportir',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['komoditi'] . "</td>
					<td>:</td>		
					<td>
						<select id=kodebarang disabled style=\"width:154px;\">'" . $optbarang . "'</select>
					</td>
					
					<td>" . $_SESSION['lang']['kuantitas'] . "</td>
					<td>:</td>		
					<td>
						<input type=text  id=kuantitas value='" . number_format($kuantitas) . "'  onkeyup=\"z.numberFormat('kuantitas');\"  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					Kg</td>
					
				</tr>
				
				<tr>
					<td>Asal Cargo</td>
					<td>:</td>		
					<td>
						<input type=text id=asalkargo  onkeypress=\"return tanpa_kutip(event)\"  class=myinputtext style=\"width:150px;\">
					</td>
				</tr>	
				
				<tr>
					<td>" . $_SESSION['lang']['namakapal'] . "</td>
					<td>:</td>		
					<td>
						
						<select id=namakapal style=\"width:154px;\">'" . $optkapal . "'</select>
						<img id='namakapal' onclick=z.elSearch('namakapal',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td>" . $_SESSION['lang']['namaponton'] . "</td>
					<td>:</td>		
					<td>
						<select id=namaponton style=\"width:154px;\">'" . $optponton . "'</select>
						<img id='namaponton' onclick=z.elSearch('namaponton',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
				</tr>	
				
				<tr>
					<td>Tanggal Kapal Datang</td>
					<td>:</td>		
					<td>
						<input type='text' class='myinputtext' id='tanggalkedatangan1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:61px;' readonly/>
						s/d
						<input type='text' class='myinputtext' id='tanggalkedatangan2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:61px;' readonly/>
					</td>
					
					<td>" . $_SESSION['lang']['bongkarmuat'] . "</td>
					<td>:</td>		
					<td>
						<select id=bongkarmuat style=\"width:154px;\">'" . $optbongkarmuat . "'</select>
						<img id='bongkarmuat' onclick=z.elSearch('bongkarmuat',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					
				</tr>
				
				<tr>
					<td>" . $_SESSION['lang']['tandatangan'] . "</td>
					<td>:</td>		
					
					<td><select style=\"width:154px;\" id=tandatangan>" . $optttd . "</select>
								<img id='tandatangan' onclick=z.elSearch('tandatangan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td>" . $_SESSION['lang']['kota'] . " " . $_SESSION['lang']['tandatangan'] . "</td>
					<td>:</td>		
					<td>
						<input type=text id=kota size=20 class=myinputtext style=\"width:150px;\">
					</td>
					
				</tr>

				  <tr>
					<td colspan=6 align=center>
                       &nbsp;
                                  </td>
                </tr>
						
                <tr>
					<td colspan=6 align=center>
                       
                                <button class=mybutton onclick=save()>Simpan</button>
                                <button class=mybutton onclick=cancel()>Hapus</button>
                                <button class=mybutton onclick=\"kembalispk('pmn_spk','" . $nokontrak . "','" . $kodept . "','" . $tanggalkontrak . "','" . $kodecustomer . "','" . $kodebarang . "')\">Kembali</button>
                        </td>
                </tr>

        </table></fieldset>
		<input type=hidden id=method value='insert'>";
// echo"<br><a href=javascript:history.back(-1)>Back</a>";      
// $attribut = "style='cursor:pointer;text-decoration: underline' title='Click to Detail' onclick=\"kembalispk('pmn_spk','".$nokontrak."','".$tanggalkontrak."','".$kodecustomer."')\";";
// echo"<table><tr><td ".$attribut.">x</td></tr></table>";


CLOSE_BOX();
?>


<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "
		<div id=container> 
			<script>loaddata()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>