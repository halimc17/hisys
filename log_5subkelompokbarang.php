<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script type="text/javascript" src="js/log_5subkelompokbarang.js?v=<?php echo time(); ?>"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
$jnsapp = "SKL";

//Get Kelompok Barang
$optKlBarang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sKlBarang = "select kode,kelompok from " . $dbname . ".log_5klbarang where status='1' order by kode";
$qKlBarang = $owlPDO->query($sKlBarang) or die(print " Gagal: " . PDOException::getMessage());
$qKlBarang->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlBarang = $qKlBarang->fetch()) {
	$optKlBarang .= "<option value='" . $rKlBarang['kode'] . "'>" . $rKlBarang['kode'] . " - " . $rKlBarang['kelompok'] . "</option>";
}

#= Option Kategori Barang
$qKategoriBarang = selectQuery($dbname, "log_5kategoribarang", "*");
$resKB = fetchdata($qKategoriBarang);
$noKb = 0;
$optKB="<table>";
foreach ($resKB as $valKB) {
	$noKb += 1;
	// if (count($resKB) > 5) {
		// $optKB .= nl2br("<label><input class=jmlkb type=checkbox id=kategori" . $noKb . " value='" . $valKB['id'] . "' />" . $valKB['jenis'] . "</label> \n");
	// } else {
		// $optKB .= "<label><input class=jmlkb type=checkbox id=kategori" . $noKb . " value='" . $valKB['id'] . "' />" . $valKB['jenis'] . "</label>";
	// }
	
	$n = $noKb%2;
	if($n==0){		
		$optKB .= "<td><label><input class=jmlkb type=checkbox id=kategori" . $noKb . " value='" . $valKB['id'] . "' />" . $valKB['jenis'] . "</label></td>";
		$optKB.="</tr>";
	}else{
		$optKB.="<tr>";
		$optKB .= "<td><label><input class=jmlkb type=checkbox id=kategori" . $noKb . " value='" . $valKB['id'] . "' />" . $valKB['jenis'] . "</label><td>";
	}
}
$optKB.="</table>";

OPEN_BOX('', '<span class=judul>' . getMenu('log_5subkelompokbarang') . '</span><br>');
echo "
<br><fieldset style='float:left'>
	<legend>" . $_SESSION['lang']['form'] . "</legend>
	<table border=0>
		<td style=vertical-align:top>
			<table cellspacing='1' border='0'>
				<tr>
					<td class='bintang'>" . $_SESSION['lang']['kelompokbarang'] . "</td>
					<td>:</td>
					<td>
						<select style='width:185px' id='kdKlBarang' onchange='getKodeSubKelompok()'>" . $optKlBarang . "</select>
						<img id='kdKlBarang' onclick=z.elSearch('kdKlBarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
				<tr>
					<td class='bintang'>" . $_SESSION['lang']['kodesubkelompokbarang'] . "</td>
					<td>:</td>
					<td>
						<input type='text' id='kdSubKl' size='5'  style='width:200px' class='myinputtext' disabled='true' maxlength='5' />
					</td>
				</tr>
				<tr>
					<td class='bintang'>" . $_SESSION['lang']['namasubkelompokbarang'] . "</td>
					<td>:</td>
					<td>
						<input type='text' id='namaSubKl' class='myinputtext' maxlength='50' size='30' style='width:200px'; />
					</td>
				</tr>
				<tr>
					<td class='bintang'>" . $_SESSION['lang']['kodevhc'] . "</td>
					<td>:</td>
					<td>
						<select id='kodevhc' style='width:205px'>
							<option value='0'>Tidak</option>
							<option value='1'>Wajib Terisi</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class='bintang'>" . $_SESSION['lang']['status'] . "</td>
					<td>:</td>
					<td>
						<select id='status' style='width:205px'>
							<option value='0'>Non-Aktif</option>
							<option value='1'>Aktif</option>
						</select>
					</td>
				</tr>
				<tbody id='trapproval'>";

		## APPROVAL ##
		$countApp = getCountApproval($jnsapp);
		for ($i = 1; $i <= $countApp; $i++) {
			$optApp = "";
			$arrlistapp = listApprove($i, $jnsapp);
			foreach ($arrlistapp as $key => $val) {
				$optApp .= "<option value='" . $val['karyawanid'] . "'>" . $val['nama'] . "</option>";
			}
			echo "<tr>
						<td class='bintang'>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td>
						<td>:</td>
						<td>
							<select  style='width:205px' id='persetujuan" . $i . "'>" . $optApp . "</select>
						</td>
					</tr>";
		}

		echo "</tbody><tr>
					<td colspan='2'></td>
					<td>
					<input type='hidden' value='insert' id='method'  />
					<button class=mybutton onclick=simpan()>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton onclick=batal()>" . $_SESSION['lang']['cancel'] . "</button>
					</td>
				</tr>
			</table>
		</td>
		<td style=vertical-align:top>	
			<td style=vertical-align:top>Nama Kategori Barang</td>
			<td style=vertical-align:top>:</td>
			<td style=vertical-align:top>
				" . $optKB . "
			</td>
		</td>
	</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo "<div class='table-scroll' style=height:50vh>
	<table class='sortable' cellspacing='1' cellpadding='5' border='0' style='width:100%'>
		<thead>
			<tr class=rowheader>
				<th>" . $_SESSION['lang']['nourut'] . "</th>
				<th>" . $_SESSION['lang']['kelompokbarang'] . "</th>
				<th width=50px>" . $_SESSION['lang']['kodesubkelompokbarang'] . "</th> 
				<th>" . $_SESSION['lang']['namasubkelompokbarang'] . "</th>
				<th>Kategori Barang</th>
				<th>" . $_SESSION['lang']['kodevhc'] . "</th>
				<th>" . $_SESSION['lang']['status'] . "</th>";
for ($i = 1; $i <= $countApp; $i++) {
	echo "<th align=center>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</th>";
}
echo "<th colspan='2' style='text-align:center;'>" . $_SESSION['lang']['action'] . "</th>
			</tr>
		</thead>
		<tbody id='container'>
		<script>loadData()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</div>";

CLOSE_BOX();
echo close_body();

?>