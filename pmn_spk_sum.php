<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript src='js/pmn_spk_sum.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/pmn_spk_print.js?v=<?php echo time(); ?>'></script>
<?php


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

$optbuyer = $optpelayaran = $optfranco1 = $optfranco2 = $optkapal = $optponton = $optttd = $optsurveyor = $optbarang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";


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
$tanggalmuat1 = $bar['tanggalmuat1'];
$tanggalmuat2 = $bar['tanggalmuat2'];
$kota = $bar['kota'];

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

// if($transportir!=''){
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
// }

$str = "select * from " . $dbname . ".pmn_5franco order by franco_name asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$selected = '';
	if ($bar['id_franco'] == $pelabuhanmuat) {
		$selected = "selected";
	}
	$optfranco1 .= "<option value='" . $bar['id_franco'] . "' " . $selected . ">" . $bar['franco_name'] . "</option>";

	$selected = '';
	if ($bar['id_franco'] == $pelabuhantujuan) {
		$selected = "selected";
	}
	$optfranco2 .= "<option value='" . $bar['id_franco'] . "' " . $selected . ">" . $bar['franco_name'] . "</option>";
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
/*
if($kodebarang=='40000001'){
	// $isitexarea1="1. Penentuan Quality\n2. Sampling dan Uji Mutu\n3. Sealing";
	// $isitexarea2="Rp 1,250,- / mt ( survey )\n2. Rp 400,000,- / sample ( Analisa FFA M and I )";
	$isitexarea2="Survey Rp 1.300 / MT\nAnalisa FFA, M dan I = Rp 450.000 / sample";
	$isitexarea1="1. Penentuan Quantity (sounding tanki)\n2. Sampling dan Uji Mutu (FFA, M dan I)\n3. Sealing";
}else{
	$isitexarea1="1. Penentuan Quality\n2. Sampling dan Uji Mutu\n3.";
	$isitexarea2="Rp 1,300,- / mt ( survey )\n2. Rp 450,000,- / sample";
}
*/


$isitexarea2 = "Survey Rp 1.300 / MT\nAnalisa FFA, M dan I = Rp 450.000 / sample";
$isitexarea1 = "1. Penentuan Quantity ( pengawasan pemuatan )\n2. Sampling & Analisa mutu composite kapal KM Bunsho\n3. Penyegelan di kapal setelah pemuatan\n4. Menerbitkan laporan Survey yang berhubungan dengan";


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
						<input type=text id=nospk size=20 placeholder='Otomatis'  class=myinputtext style=\"width:150px;\">
					</td>
					
					<td valign=top rowspan=4>Jenis<br>Pekerjaan</td> 
					<td valign=top rowspan=4>:</td>
					<td valign=top rowspan=4><textarea rows='5' maxlength=1024 id=jenispekerjaan type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:305px;\">" . $isitexarea1 . "</textarea>
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
						<img id='transportir' onclick=z.elSearch('transportir',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px'>
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
						<input type=text  id=kuantitas value='" . number_format($kuantitas) . "'  onkeyup=\"z.numberFormat('kuantitas');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					Kg</td>
					
					<td valign=top rowspan=3>FEE</td> 
					<td valign=top rowspan=3>:</td>
					<td valign=top rowspan=3><textarea rows='3' maxlength=1024 id=fee type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:305px;\">" . $isitexarea2 . "</textarea>
					</td>
					
				</tr>
				<tr>
				
					<td>" . $_SESSION['lang']['surveyor'] . "</td>
					<td>:</td>		
					<td>
						<select id=surveyor style=\"width:154px;\">'" . $optsurveyor . "'</select>
						<img id='surveyor' onclick=z.elSearch('surveyor',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
				
					<td>Kota <i>(dibuat di)</i></td>
					<td>:</td>		
					<td>
						<input type=text id=kota value='" . $kota . "'  onkeypress=\"return tanpa_kutip(event)\"  class=myinputtext style=\"width:150px;\">
					</td>
					
					
					
				</tr>	
				<tr>
					<td>Tanggal Kapal Datang</td>
					<td>:</td>		
					<td>
						<input type='text' class='myinputtext' id='tanggalkedatangan' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:150px;' readonly/>
					</td>
					
					
					<td>Jadwal Keg Survey</td>
					<td>:</td>		
					<td>
						<input type='text' class='myinputtext' id='tanggalsurvey1' value='" . tanggalnormal($tanggalmuat1) . "' placeholder='Tgl Dari' onmousemove='setCalendar(this.id)' onkeypress='return false;' maxlength='10' style='width:70px;' readonly/>
						<input type='text' class='myinputtext' id='tanggalsurvey2'  value='" . tanggalnormal($tanggalmuat2) . "' placeholder='Tgl Sampai'  onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:72px;' readonly/>
					</td>
					
				</tr>	
				
				<tr>
					

				
					<td>" . $_SESSION['lang']['pelabuhanmuat'] . "</td>
					<td>:</td>		
					<td>
						<select id=pelabuhanmuat style=\"width:154px;\">'" . $optfranco1 . "'</select>
						<img id='pelabuhanmuat' onclick=z.elSearch('pelabuhanmuat',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px'>
					
					<td>" . $_SESSION['lang']['pelabuhantujuan'] . "</td>
					<td>:</td>		
					<td>
						<select id=pelabuhantujuan style=\"width:154px;\">'" . $optfranco2 . "'</select>
						<img id='pelabuhantujuan' onclick=z.elSearch('pelabuhantujuan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px'>
					</td>
					
					<td valign=top rowspan=3>Note</td> 
					<td valign=top rowspan=3>:</td>
					<td valign=top rowspan=3><textarea rows='3' maxlength=1024 id=note type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:305px;\"></textarea>
					</td>
					
					
				</tr>
				
				<tr>
				
						<td>" . $_SESSION['lang']['namakapal'] . "</td>
					<td>:</td>		
					<td>
						
						<select id=namakapal style=\"width:154px;\">'" . $optkapal . "'</select>
						<img id='namakapal' onclick=z.elSearch('namakapal',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px'>
					</td>
					
					
					
					<td>" . $_SESSION['lang']['namaponton'] . "</td>
					<td>:</td>		
					<td>
						<select id=namaponton style=\"width:154px;\">'" . $optponton . "'</select>
						<img id='namaponton' onclick=z.elSearch('namaponton',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px'>
					</td>
					
					<td hidden>" . $_SESSION['lang']['rupiah'] . "</td>
					<td hidden>:</td>		
					<td hidden>
						<input type=text  id=rupiah  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					</td>
					
					
					
				</tr>	
				
				<tr>
					<td>" . $_SESSION['lang']['tandatangan'] . " <i><b>I</b></i></td>
					<td>:</td>		
					
					<td><select style=\"width:154px;\" id=tandatangan1>" . $optttd . "</select>
						<img id='tandatangan1' onclick=z.elSearch('tandatangan1',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>&nbsp;
					</td>
					
					<td>" . $_SESSION['lang']['tandatangan'] . " <i><b>II</b></i></td>
					<td>:</td>		
					
					<td><select style=\"width:154px;\" id=tandatangan2>" . $optttd . "</select>
						<img id='tandatangan2' onclick=z.elSearch('tandatangan2',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
				</tr>	
					
				 <tr>
					<td colspan=6 align=center>
                       &nbsp;
                                  </td>
                </tr>	
				 <tr>
					<td colspan=6 align=center>
                       &nbsp;
                                  </td>
                </tr>	
					
					
                <tr><td colspan=9 align=center>
						<button class=mybutton onclick=save()>Simpan</button>
						<button class=mybutton onclick=cancel()>Hapus</button>
						<button class=mybutton onclick=\"kembalispk('pmn_spk','" . $nokontrak . "','" . $kodept . "','" . $tanggalkontrak . "','" . $kodecustomer . "','" . $kodebarang . "')\">Kembali</button>
					</td>
                </tr>

        </table></fieldset>
		<input type=hidden id=method value='insert'>";
CLOSE_BOX();
?>
<?php
OPEN_BOX();
echo "
		<div id=container > 
			<script>loaddata()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>