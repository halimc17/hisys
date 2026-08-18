<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul>' . strtoupper($_SESSION['lang']['slipGaji'] . " " . $_SESSION['lang']['bulanan'] . "") . '</span>');
?>
<?php
//ambil periode gaji sesuai dengan lokasi tugas
$optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$lksiTugas = substr($_SESSION['empl']['lokasitugas'], 0, 4);
$sPeriode = "select distinct periode from " . $dbname . ".sdm_5periodegaji where jenisgaji='B' order by periode desc limit 12";
$qPeriode = $owlPDO->query($sPeriode) or die(print " Gagal: " . PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while ($rPeriode = $qPeriode->fetch()) {
	$optPeriode .= "<option value=" . $rPeriode['periode'] . ">" . $rPeriode['periode'] . "</option>";
}

// ambil kodeorganisasi
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
	$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi 
               where CHAR_LENGTH(kodeorganisasi)=4 order by namaorganisasi asc";
} else if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
	$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi 
               where CHAR_LENGTH(kodeorganisasi)=4 and tipe<>'HOLDING' order by namaorganisasi asc";
} else {
	$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where induk='" . $_SESSION['empl']['lokasitugas'] . "' or kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'";
}
$optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
	$sCek = "select distinct * from " . $dbname . ".sdm_gaji where kodeorg='" . $rOrg['kodeorganisasi'] . "'";
	$rCek = fetchData($sCek);
	$sCek2 = "select distinct * from " . $dbname . ".sdm_gajiho where kodeorg='" . $rOrg['kodeorganisasi'] . "'";
	$rCek2 = fetchData($sCek2);
	if ((count($rCek) != 0) || (count($rCek2) != 0)) {
		$optOrg .= "<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['kodeorganisasi'] . "-" . $rOrg['namaorganisasi'] . "</option>";
	}
}

//ambil dept
$optDept = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optTipe = $optDept;
$sDept = "select * from " . $dbname . ".sdm_5departemen order by nama asc";
$qDept = $owlPDO->query($sDept) or die(print " Gagal: " . PDOException::getMessage());
$qDept->setFetchMode(PDO::FETCH_ASSOC);
while ($rDept = $qDept->fetch()) {
	$optDept .= "<option value=" . $rDept['kode'] . ">" . $rDept['nama'] . "</option>";
}

//ambil tipekaryawan 
$sTipeKary = "select distinct * from " . $dbname . ".sdm_5tipekaryawan where aktif=1 order by tipe asc";
$qTipeKary = $owlPDO->query($sTipeKary) or die(print " Gagal: " . PDOException::getMessage());
$qTipeKary->setFetchMode(PDO::FETCH_ASSOC);
while ($rTipeKary = $qTipeKary->fetch()) {
	$optTipe .= "<option value='" . $rTipeKary['id'] . "'>" . $rTipeKary['tipe'] . "</option>";
}
$arr = "##periode##idAfd##idDivisi##kdBag##tPkary";
$arrKry = "##period##idAfd2##idKry";
$arrAfd = "##perod##idAfd##kdBag2##tPkary2";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script>
	function getPeriode() {
		kdOrg = document.getElementById('idAfd').options[document.getElementById('idAfd').selectedIndex].value;
		tujuan = 'sdm_slave_2slipGajiBulananAfd';
		param = 'idAfd=' + kdOrg;
		post_response_text(tujuan + '.php?proses=getPeriode', param, respog);

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						//alert(con.responseText);
						document.getElementById('perod').innerHTML = con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}

	function getDivisi() {
		kdOrg = document.getElementById('idAfd').options[document.getElementById('idAfd').selectedIndex].value;
		document.getElementById('idDivisi').innerHTML = "<option value=''><?php echo $_SESSION['lang']['all']; ?></option>";

		if (kdOrg == '') {
			return;
		}

		tujuan = 'sdm_slave_2slipGajiBulananAfd';
		param = 'idAfd=' + encodeURIComponent(kdOrg);
		post_response_text(tujuan + '.php?proses=getDivisi', param, respDivisi);

		function respDivisi() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						document.getElementById('idDivisi').innerHTML = con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}

	function getnama() {
		period = document.getElementById('period').options[document.getElementById('period').selectedIndex].value;
		kdOrg = document.getElementById('idAfd2').options[document.getElementById('idAfd2').selectedIndex].value;
		tujuan = 'sdm_slave_2slipGajiBulananAfd';
		param = 'idAfd=' + kdOrg + '&period=' + period;
		post_response_text(tujuan + '.php?proses=getnama', param, respog);

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						//alert(con.responseText);
						document.getElementById('idKry').innerHTML = con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}

	function hapusperperiode() {
		document.getElementById('periode').value = '';
		document.getElementById('idAfd').value = '';
		document.getElementById('idDivisi').innerHTML = "<option value=''><?php echo $_SESSION['lang']['all']; ?></option>";
		document.getElementById('kdBag').value = '';
		document.getElementById('tPkary').value = '';
	}

	function hapusperkaryawan() {
		document.getElementById('period').value = '';
		document.getElementById('idAfd2').value = '';
		document.getElementById('idKry').innerHTML = '';
	}
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<div>
	<fieldset style="float: left;">
		<legend><b><? echo $_SESSION['lang']['slipgajibulananper'] . "/" . $_SESSION['lang']['periode']; ?></b></legend>
		<table cellspacing="1" cellpadding="2" border="0">
			<tr>
				<td>
					<label><?php echo $_SESSION['lang']['periode'] ?></label>
				</td>
				<td>
					<select id="periode" name="periode" style="width:200px"><?php echo $optPeriode ?></select>
				</td>
			</tr>
			<tr>
				<td>
					<label><?php echo $_SESSION['lang']['unit'] ?></label>
				</td>
				<td>
					<select id="idAfd" name="idAfd" style="width:200px" onchange="hapusperkaryawan();getDivisi()"><?php echo $optOrg ?></select>
				</td>
			</tr>
			<tr>
				<td>
					<label><?php echo $_SESSION['lang']['divisi'] ?></label>
				</td>
				<td>
					<select id="idDivisi" name="idDivisi" style="width:200px">
						<option value=""><?php echo $_SESSION['lang']['all'] ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label><?php echo $_SESSION['lang']['bagian'] ?></label>
				</td>
				<td><select id="kdBag" name="kdBag" style="width:200px"><?php echo $optDept ?></select>
				</td>
			</tr>
			<tr>
				<td>
					<label><?php echo $_SESSION['lang']['tipekaryawan'] ?></label>
				</td>
				<td><select id="tPkary" name="tPkary" style="width:200px"><?php echo $optTipe ?></select>
				</td>
			</tr>
			<tr>
				<td colspan=2 align=center>
					<button onclick="zPreview('sdm_slave_2slipGajiBulanan','<?php echo $arr ?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zPdf('sdm_slave_2slipGajiBulanan','<?php echo $arr ?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
				</td>
			</tr>

		</table>
	</fieldset>
</div>

<div hidden>
	<fieldset style="float: left;height:120px">
		<legend><b><? echo $_SESSION['lang']['slipgajibulananper'] . "/" . $_SESSION['lang']['karyawan']; ?></legend>
		<table cellspacing="1" border="0">
			<tr>
				<td>
					<label><?php echo $_SESSION['lang']['periode'] ?></label>
				</td>
				<td>
					<select id="period" name="period" style="width:150px"><?php echo $optPeriode ?></select>
				</td>
			</tr>
			<tr>
				<td>
					<label><?php echo $_SESSION['lang']['unit'] ?></label>
				</td>
				<td>
					<select id="idAfd2" name="idAfd2" style="width:150px" onclick="hapusperperiode()" onchange="getnama()"><?php echo $optOrg ?></select>
				</td>
			</tr>
			<tr>
				<td>
					<label><?php echo $_SESSION['lang']['namakaryawan'] ?></label>
				</td>
				<td>
					<select id="idKry" name="idKry" style="width:150px"><?php echo $optKry ?></select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center"><button onclick="zPreview('sdm_slave_2slipGajiBulanan','<?php echo $arrKry ?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
					<button onclick="zPdf('sdm_slave_2slipGajiBulanan','<?php echo $arrKry ?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
				</td>
			</tr>
		</table>
	</fieldset>
</div>

<?php

CLOSE_BOX();
OPEN_BOX();
?>
<fieldset style='clear:both'>
	<legend><b>Print Area</b></legend>
	<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'>

	</div>
</fieldset>
<?php

CLOSE_BOX();
echo close_body();
?>