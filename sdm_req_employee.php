 <?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include('lib/zFunction.php');
	include('lib/rTable.php');
	echo open_body();
	include('master_mainMenu.php');
	?>

 <link rel=stylesheet type=text/css href=style/zTable.css>
 <script language=javascript src=js/zReport.js></script>
 <script language=javascript src=js/zMaster.js></script>
 <script language=javascript src=js/zTools.js></script>
 <script language=javascript src=js/zSearch.js></script>
 <script language=javascript1.2 src=js/formTable.js></script>
 <script language=javascript src=js/sdm_req_employee.js?v=<?php echo time(); ?>></script>

 <!----------------------------------- Deklarasi ------------------------------------>
 <?php
	$optjab = $optdep = $optalasan = $optstatus = $optgol = $optdivisi = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	#==================================Ambil Jabatan======================================#
	$sjab	= "SELECT * FROM " . $dbname . ".sdm_5jabatan WHERE aktif='1' ORDER BY namajabatan";
	$qjab	= $owlPDO->query($sjab) or die(print " Gagal: " . PDOException::getMessage());
	$qjab->setFetchMode(PDO::FETCH_ASSOC);
	while ($rjab = $qjab->fetch()) {
		$optjab	   .= "<option value='" . $rjab['kodejabatan'] . "'>" . $rjab['namajabatan'] . "</option>";
	}
	#=================================Ambil Departemen=====================================#
	$sdep	= "SELECT * FROM " . $dbname . ".sdm_5departemen";
	$qdep	= $owlPDO->query($sdep) or die(print " Gagal: " . PDOException::getMessage());
	$qdep->setFetchMode(PDO::FETCH_ASSOC);
	while ($rdep = $qdep->fetch()) {
		$optdep	   .= "<option value='" . $rdep['kode'] . "'>" . $rdep['nama'] . "</option>";
	}
	#=================================Ambil divisi=====================================#
	$sdivisi	= "SELECT * FROM " . $dbname . ".organisasi where induk = '" . $_SESSION['empl']['lokasitugas'] . "' ORDER BY namaorganisasi";
	$qdivisi	= $owlPDO->query($sdivisi) or die(print " Gagal: " . PDOException::getMessage());
	$qdivisi->setFetchMode(PDO::FETCH_ASSOC);
	while ($rdivisi = $qdivisi->fetch()) {
		$optdivisi	   .= "<option value='" . $rdivisi['kodeorganisasi'] . "'>" . $rdivisi['namaorganisasi'] . "</option>";
	}
	#=======================================Alasan=========================================#
	$arralasan = array("Penambahan sesuai MPP", "Penambahan diluar MPP", "Replacement");
	foreach ($arralasan as $val) {
		$optalasan .= "<option value='" . $val . "'>" . $val . "</option>";
	}
	#=======================================Status Pernikahan=========================================#
	$arrstatuspernikahan = array(
		"single" => "Belum Menikah",
		"menikah" => "Menikah",
		"duda" => "Duda",
		"janda" => "Janda"
	);
	foreach($arrstatuspernikahan as $key => $val){
		$optstatuspernikahan .= "<option value='".$key."'>".$val."</option>";
	}
	#===============================Ambil Status Karyawan==================================#
	$arrgetenum		= getEnum($dbname, 'sdm_req_employee', 'statuskaryawan');
	foreach ($arrgetenum as $hasil) {
		$optstatus   .= "<option value='" . $hasil . "'>" . ucfirst($hasil) . "</option>";
	}
	#===================================Ambil Golongan=====================================#
	$sgol	= "SELECT * FROM " . $dbname . ".sdm_5golongan";
	$qgol	= $owlPDO->query($sgol) or die(print " Gagal: " . PDOException::getMessage());
	$qgol->setFetchMode(PDO::FETCH_ASSOC);
	while ($rgol = $qgol->fetch()) {
		$optgol	   .= "<option value='" . $rgol['kodegolongan'] . "'>" . $rgol['namagolongan'] . "</option>";
	}

	$arrJenisKelamin = [
		'L' => 'Laki-laki',
		'P' => 'Perempuan',
	];

	$optJenisKelamin = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	foreach ($arrJenisKelamin as $key => $value) {
		$optJenisKelamin .= "<option value='" . $key . "'>" . $value . "</option>";
	}

	$arrStatusPernikahan = [
		"single" => "Belum Menikah",
		"menikah" => "Menikah",
		"duda" => "Duda",
		"janda" => "Janda"
	];

	$optStatusPernikahan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	foreach ($arrStatusPernikahan as $key => $value) {
		$optStatusPernikahan .= "<option value='" . $key . "'>" . $value . "</option>";
	}


	$arrPendidikan = [
		'SMP',
		'SMA/SMK',
		'D1/D2/D3',
		'S1',
		'S2',
		'S3',
	];

	$optPendidikan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	foreach ($arrPendidikan as $value) {
		$optPendidikan .= "<option value='" . $value . "'>" . $value . "</option>";
	}

	$arrJenisTes = [
		'Psikotes',
		'Tes Bahasa Indonesia',
		'Tes Bahasa Inggris',
		'Tes Komputer / Mengetik',
		'Tes Lain-lain'
	];
	$optJenisTes = "";
	foreach ($arrJenisTes as $value) {
		$optJenisTes .= "<input type='checkbox' name='jenis_tes[]' value='{$value}' style='margin-right:5px;'> {$value}<br>";
	}

	$arrJenisInterview = [
		'Exploratory Interview',
		'User / Teknikal Interview',
		'Panel Interview',
		'Management Interview'
	];
	$optJenisInterview = "";
	foreach ($arrJenisInterview as $value) {
		$optJenisInterview .= "<input type='checkbox' name='jenis_interview[]' value='{$value}' style='margin-right:5px;'> {$value}<br>";
	}
	?>

 <!------------------- HEADER untuk BUAT BARU, LIST DATA dan CARI ------------------->
 <?php
	OPEN_BOX('', '<span class=judul>' . getMenu('sdm_req_employee') . '</span>');
	echo "<div>";
	echo   "<table cellspacing=1 border=0>
				<tbody>
					<tr valign=middle>
						<td style=width:100px;cursor:pointer; onclick=createNew() align=center>
							<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>
							" . $_SESSION['lang']['new'] . "
						</td>
						<td style=width:100px;cursor:pointer; onclick=displayList() align=center>
							<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>
							" . $_SESSION['lang']['list'] . "
							<td>
						</td>
						<td>
							<fieldset style='width:auto;'>
								<legend>" . $_SESSION['lang']['find'] . "</legend>
								<table>
									<tr>
										<td align=left>" . $_SESSION['lang']['notransaksi'] . "</td>
										<td>:</td>
										<td><input type=text id=notransaksisch onkeyup=loadData(); maxlength=20 class=myinputtext size=26 style=\"width:200px;\"></td>

										<td></td>
										<td></td>
										<td>
											<button class=mybutton onclick=loadData()>" . $_SESSION['lang']['find'] . "</button>
											<button class=mybutton onclick=displayList()>" . $_SESSION['lang']['cancel'] . "</button>
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>";
	echo "</div>";
	CLOSE_BOX();
	?>

 <!-------------------------------- LIST DATA --------------------------------------->
 <?php
	echo "<div id=listData>";
	OPEN_BOX();
	echo " 	<fieldset style='width:auto;'>
				<legend>" . $_SESSION['lang']['list'] . "</legend>
				<div>
					<table class=sortable cellspacing=1 cellpadding=7 border=0 style='width:100%;'>
						<thead>
							<tr class=rowheader>
								<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
								<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
								<td align=center>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['jabatan'] . "</td>
								<td align=center>" . $_SESSION['lang']['pekerjasekarang'] . "</td>
								<td align=center>" . $_SESSION['lang']['pekerjadibutuhkan'] . "</td>
								<td align=center>" . $_SESSION['lang']['departemen'] . "</td>
								<td align=center>" . $_SESSION['lang']['kodegolongan'] . "</td>    
								<td align=center>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['karyawan'] . "</td>    
								<td align=center>" . $_SESSION['lang']['createby'] . "</td>    
								<td align=center colspan=5>" . $_SESSION['lang']['action'] . "</td>
							</tr>
						</thead>
						<tbody id=container>
							<script>loadData(0)</script>
						</tbody>
						<tfoot id=footData>
						</tfoot>
					</table>
				</div>
			</fieldset>";
	CLOSE_BOX();
	echo "</div>";
	?>

 <!------------------------ Buat Baru Permintaan Karyawan -------------------------->
 <?php
	echo "<div id=addNew style=display:none>";
	OPEN_BOX();
	echo 	"<fieldset style='float:left; widht:auto;'>
				<legend>" . $_SESSION['lang']['entryForm'] . "</b></legend> 
				<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
					<tr>
						<td>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td>:</td>
						<td><input type=text maxlength=20 id=notransaksi class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:158px;\" disabled> </td>
						<td></td>
						
						<td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['jabatan'] . "</td>
						<td>:</td>
						<td><select id=namajabatan style='width:160px;' onchange=hitungpekerja()>" . $optjab . "</select> <img id='namajabatan' onclick=z.elSearch('namajabatan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['pekerjasekarang'] . "</td>
						<td>:</td>
						<td><input type=text maxlength=4 class=myinputtextnumber id=jumlahpekerjasekarang onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" onblur=\"change_number(this);\" style=\"width:158px;\" disabled></td>
						<td></td>
						
						<td>" . $_SESSION['lang']['pekerjadibutuhkan'] . "</td>
						<td>:</td>
						<td><input type=text maxlength=4 class=myinputtextnumber id=jumlahpekerjadibutuhkan onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" style=\"width:158px;\"></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['departemen'] . "</td>
						<td>:</td>
						<td><select id=departemen style='width:158px;'>" . $optdep . "</select> <img id='departemen' onclick=z.elSearch('departemen',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
						<td></td>

						<td>" . $_SESSION['lang']['kodegolongan'] . "</td>
						<td>:</td>
						<td><select id=golongan style='width:158px;'>" . $optgol . "</select> <img id='golongan' onclick=z.elSearch('golongan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['jeniskelamin'] . "</td>
						<td>:</td>
						<td><select id=jeniskelamin style='width:158px;'>" . $optJenisKelamin . "</select> <img id='jeniskelamin' onclick=z.elSearch('jeniskelamin',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
						<td></td>

						<td>" . $_SESSION['lang']['statusperkawinan'] . "</td>
						<td>:</td>
						<td><select id=statuspernikahan style='width:158px;'>" . $optStatusPernikahan . "</select> <img id='statuspernikahan' onclick=z.elSearch('statuspernikahan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
						<td></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['alasanminta'] . "</td>
						<td>:</td>
						<td><select id=alasan onchange='getAlasan()' style='width:158px;'>" . $optalasan . "</select> <img id='alasan' onclick=z.elSearch('alasan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
						<td></td>
						
						<td>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['karyawan'] . "</td>
						<td>:</td>
						<td><select id=statuskaryawan style='width:158px;'>" . $optstatus . "</select> <img id='statuskaryawan' onclick=z.elSearch('statuskaryawan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
					</tr>
					<tr'>
						<td></td>
						<td></td>
						<td valign=top>
							<textarea id=alasanganti  class=myinputtext  style=height:50px;width:158px onkeypress=\"return tanpa_kutip(event);\"></textarea>
						</td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['mulaikerja'] . "</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=mulaibekerja name=mulaibekerja onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10  maxlength=10 style=width:58px;/ readonly></td>
						<td></td>
						
						<td>" . $_SESSION['lang']['lokasi'] . " Kerja</td>
						<td>:</td>
						<td><input type=text maxlength=20 id=lokasikerja class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:58px;\" value='" . $_SESSION['empl']['lokasitugas'] . "' disabled> </td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>" . $_SESSION['lang']['divisi'] . "</td>
						<td>:</td>
						<td><select id=divisi style='width:158px;'>" . $optdivisi . "</select> <img id='divisi' onclick=z.elSearch('divisi',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
						<td></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['pendidikanmin'] . "</td>
						<td>:</td>
						<td><select id=pendidikanminimal style='width:158px;'>" . $optPendidikan . "</select> <img id='pendidikanminimal' onclick=z.elSearch('pendidikanminimal',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
						<td></td>

						<td>" . $_SESSION['lang']['pengalamanmin'] . "</td>
						<td>:</td>
						<td><input type=text maxlength=12 class=myinputtextnumber id=pengalamanminimal onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" style=\"width:158px;\"></td>
					</tr>
					<tr>
						<td valign=top>Pengalaman di bidang</td>
						<td valign=top>:</td>
						<td valign=top>
							<textarea id=bidangpengalaman  class=myinputtext  style=height:50px;width:158px onkeypress=\"return tanpa_kutip(event);\"></textarea>
						</td>	
						<td></td>

						<td valign=top>Keterampilan khusus</td>
						<td valign=top>:</td>
						<td valign=top>
							<textarea id=kualifikasi  class=myinputtext  style=height:50px;width:158px onkeypress=\"return tanpa_kutip(event);\"></textarea>
						</td>	
					</tr>
					<tr>
						<td valign=top>" . $_SESSION['lang']['uraiankerja'] . "</td>
						<td valign=top>:</td>
						<td valign=top>
							<textarea id=uraiankerja  class=myinputtext  style=height:50px;width:158px onkeypress=\"return tanpa_kutip(event);\"></textarea>
						</td>	
						<td></td>

						<td valign=top>Sertifikasi/Keahlian Khusus</td>
						<td valign=top>:</td>
						<td valign=top>
							<textarea id=sertifikasi  class=myinputtext  style=height:50px;width:158px onkeypress=\"return tanpa_kutip(event);\"></textarea>
						</td>	
					</tr>
					<tr>
						<td valign='top'>Usia</td>
						<td valign='top'>:</td>
						<td valign='top'>
							<input type=text maxlength=12 class=myinputtextnumber id=usiamin onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" style=\"width:65px;\">
							s.d
							<input type=text maxlength=12 class=myinputtextnumber id=usiamax onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" style=\"width:65px;\">
						</td>
						<td></td>
					</tr>
					<tr>
						<td valign='top'>Jenis tes yang diberikan</td>
						<td valign='top'>:</td>
						<td valign='top'>
							<div style='width:100%;'>
								" . $optJenisTes . "
							</div>
						</td>
						<td></td>
					</tr>
					<tr>
						<td valign='top'>Jenis interview yang digunakan</td>
						<td valign='top'>:</td>
						<td valign='top'>
							<div style='width:100%;'>
								" . $optJenisInterview . "
							</div>
						</td>
						<td></td>
					</tr>
					<tr>
						<td valign=top>" . $_SESSION['lang']['note'] . "</td>
						<td valign=top>:</td>
						<td valign=top>
							<textarea id=note  class=myinputtext  style=height:50px;width:158px onkeypress=\"return tanpa_kutip(event);\"></textarea>
						</td>	
						<td></td>
					</tr>
					<tr>
						<td align=center colspan=7>
							<hr>
							<input type=hidden id=method value='insert'>
							<button class=mybutton onclick=simpan()>" . $_SESSION['lang']['save'] . "</button>
							<button class=mybutton onclick=hapus()>" . $_SESSION['lang']['cancel'] . "</button>
						</td>
					</tr>
				</table>
			</fieldset>";
	CLOSE_BOX();
	echo "</div>";
	echo close_body();
	?>