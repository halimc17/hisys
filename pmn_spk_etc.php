<? //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pmn_spk_etc.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/pmn_spk_print.js?v=<?php echo time(); ?>'></script>

<?php

// OPEN_BOX('','<span class=judul>Instruksi Pemuatan Kargo</span><br><br>');

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
$keterangan = $bar['keterangan'];



OPEN_BOX('', '<span class=judul>' . strtoupper($keterangan) . '</span><br><br>');


$optbuyer = $optpelayaran = $optfranco = $optttd = $optsurveyor = $optbarang = $opttransportirdarat = $optkapal = $optponton = $optnoakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "' order by namacustomer asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optbuyer .= "<option selected value='" . $bar['kodecustomer'] . "'>" . $bar['namacustomer'] . "</option>";
}

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
$tanggal = $bar['tanggal'];


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
	$opttransportirdarat .= "<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}


// $str = "SELECT * FROM " . $dbname . ".pmn_5kapalponton  where transportir='".$transportir."' and kode in ('".$namakapal."','".$namaponton."')";
$str = "SELECT * FROM " . $dbname . ".pmn_5kapalponton";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$selected = '';

	if ($bar['jenis'] == 'KPL') {
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
	$arrinisial[$bar['kodebarang']] = $bar['inisial'];
	$optbarang .= "<option selected value='" . $bar['kodebarang'] . "'>" . $bar['namabarang'] . "</option>";
}

$isitext = "";
if ($kodebarang == '40000001') {
	$isitext .= "- Toleransi susut max - 0.5% per truck dari angka timbangan pabrik PT. " . $kodept . " dengan angka timbangan IBW.";
	$isitext .= "\n- Segel atau locis yang dipasang pada tempat yang telah ditentukan ditruck tangki " . $arrinisial[$kodebarang] . " sebagai upaya pengamanan " . $arrinisial[$kodebarang] . " wajib dijaga oleh pihak transportir/supir truk. ";
	$isitext .= "\n- Batas lama toleransi perjalanan dari PMKS PT. " . $kodept . " ke IBW max - 20 Jam. Bongkar kembali kepabrik PT. " . $kodept . ", kecuali terjadi kecelakaan dijalan raya dengan bukti surat dari pihak kepolisian terdekat atau keadaan force majeur.";
	$isitext .= "\n-Pengangkutan " . $arrinisial[$kodebarang] . " agar dilakukan dengan penuh tanggung jawab, sebagaimana dengan 
	syarat - syarat yang telah disepakati.";
}

$str = "SELECT noakun, namaakun FROM ".$dbname.".keu_5akun WHERE noakun LIKE '81101%' OR noakun LIKE '81102%'";
$res = fetchdata($str);
foreach($res as $key=>$val){
	$optnoakun .= "<option value=".$val['noakun'].">(".$val['noakun'].") ".$val['namaakun']."</option>";
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
					
					<td>" . $_SESSION['lang']['kota'] . " " . $_SESSION['lang']['tandatangan'] . "</td>
					<td>:</td>		
					<td>
						<input type=text value='" . $kota . "' id=kota size=20 class=myinputtext style=\"width:150px;\">
					</td>				
						
					<td>Rp/Kg</td>
					<td>:</td>		
					<td>
						<input type=text id=rpkg value='".number_format(@$rpkg)."' onkeyup=\"z.numberFormat('rpkg');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
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
					
						<td>" . $_SESSION['lang']['tandatangan'] . " I</td>
					<td>:</td>	
					<td><select style=\"width:150px;\" id=tandatangan1>" . $optttd . "</select>
								<img id='tandatangan1' onclick=z.elSearch('tandatangan1',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
					
					<td>Toleransi</td>
					<td>:</td>		
					<td>
						<input type=text id=toleransi onkeypress=empty1() class=myinputtextnumber style=\"width:150px;\" value=0> %
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
						<input type=text value='" . tanggalnormal($tanggal) . "' class=myinputtext readonly id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:150px;\">
					</td>
					
						<td>" . $_SESSION['lang']['tandatangan'] . " II</td>
					<td>:</td>	
					<td><select style=\"width:150px;\" id=tandatangan2>" . $optttd . "</select>
								<img id='tandatangan2' onclick=z.elSearch('tandatangan2',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>		
									
					<td>Kg Toleransi</td>
					<td>:</td>		
					<td>
						<input type=text id=kgtoleransi onkeypress=empty2() class=myinputtextnumber style=\"width:150px;\" value=0>
					</td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['Pembeli'] . "</td>
					<td>:</td>		
					<td>
						<select id=kodecustomer disabled value='" . $kodecustomer . "'  style=\"width:150px;\">'" . $optbuyer . "'</select>
					</td>
					
					<td>" . $_SESSION['lang']['transportir'] . " " . $_SESSION['lang']['kapal'] . "</td>
					<td>:</td>		
					<td>
						<select id=transportir style=\"width:154px;\" onchange=getkapalponton()>'" . $optpelayaran . "'</select>
						<img id='transportir' onclick=z.elSearch('transportir',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td>" . $_SESSION['lang']['transportir'] . " " . $_SESSION['lang']['darat'] . "</td>
					<td>:</td>		
					<td>
						<select id=transportirdarat style=\"width:150px;\">'" . $opttransportirdarat . "'</select>
						<img id='transportirdarat' onclick=z.elSearch('transportirdarat',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>
								
					<td valign=top rowspan=4>" . $_SESSION['lang']['lain'] . "</td> 
					<td valign=top rowspan=4>:</td>
					<td  rowspan=4><textarea rows='4' id=lain type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:250px;\">" . $isitext . "</textarea>
					</td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['komoditi'] . "</td>
					<td>:</td>		
					<td>
						<select id=kodebarang disabled style=\"width:150px;\">'" . $optbarang . "'</select>
					</td>
					
					<td>" . $_SESSION['lang']['kuantitas'] . "</td>
					<td>:</td>		
					<td>
						<input type=text  id=kuantitas value='" . number_format($kuantitas) . "' onkeyup=\"z.numberFormat('kuantitas');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					Kg</td>
					
					
					<td valign=top rowspan=3>" . $_SESSION['lang']['harga'] . "</td> 
					<td valign=top rowspan=3>:</td>
					<td  rowspan=3><textarea rows='3' id=harga type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:150px;\">Rp    ,- per Kg\n(Harga diatas belum termasuk PPN dan sudah termasuk PPH)</textarea>
					</td>
				
				</tr>
				
				
			
			
	
				
				<tr>
					<td>" . $_SESSION['lang']['asalbarang'] . "</td>
					<td>:</td>		
					<td>
						<select id=pelabuhanmuat style=\"width:150px;\">'" . $optfranco1 . "'</select>
						<img id='pelabuhanmuat' onclick=z.elSearch('pelabuhanmuat',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td>" . $_SESSION['lang']['kuantitaskemasan'] . "</td>
					<td>:</td>		
					<td>
						<input type=text  id=kuantitaskemasan  onkeyup=\"z.numberFormat('kuantitaskemasan');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					Kg</td>
					
					
				
					
				</tr>	
				
				<tr>
					<td>" . $_SESSION['lang']['tujuan'] . "</td>
					<td>:</td>		
					<td>
						<select id=pelabuhantujuan style=\"width:150px;\">'" . $optfranco2 . "'</select>
						<img id='pelabuhantujuan' onclick=z.elSearch('pelabuhantujuan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td>" . $_SESSION['lang']['tanggalmuat'] . "</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext  style=\"width:60px;\" value='" . tanggalnormal($tanggalmuat1) . "' readonly id=tanggalmuat1 size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\">
						s/d
						<input type=text class=myinputtext  style=\"width:60px;\" value='" . tanggalnormal($tanggalmuat2) . "'  readonly id=tanggalmuat2 size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\">
					</td>
				
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['namakapal'] . "</td>
					<td>:</td>
					<td>
						
						<select id=namakapal style=\"width:150px;\">'" . $optkapal . "'</select>
						<img id='namakapal' onclick=z.elSearch('namakapal',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td>" . $_SESSION['lang']['namaponton'] . "</td>
					<td>:</td>		
					<td>
						<select id=namaponton style=\"width:153px;\">'" . $optponton . "'</select>
						<img id='namaponton' onclick=z.elSearch('namaponton',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td>" . $_SESSION['lang']['noakun'] . "</td>
					<td>:</td>		
					<td>
						<select id=noakun style=\"width:153px;\">'" . $optnoakun . "'</select>
						<img id='noakun' onclick=z.elSearch('noakun',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td hidden>" . $_SESSION['lang']['rupiah'] . "</td>
					<td hidden>:</td>		
					<td hidden>
						<input type=text  id=rupiah  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					</td>
						
					
				</tr>
				<tr>
					
					<td>
						&nbsp;
					</td>
						
					
				</tr>
						
				
                <tr><td colspan=12 align=center>
                       
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
	";
CLOSE_BOX();
echo close_body();
?>