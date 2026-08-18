<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zMysql.php');
echo open_body();
?>
<script language=javascript1.2 src=js/klsupplier.js?ver=2.0></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul>' . getMenu('log_5kelompoksupplier') . '</span></br>');

$optkel = "<option value''></option>";
$optkel .= "<option value'SUPPLIER'>SUPPLIER</option>";

?>

<fieldset style='float:left'>
	<legend>
		<?php echo $_SESSION['lang']['form']; ?>
	</legend>
	<table>
		<tr>
			<td class="bintang"><?php echo $_SESSION['lang']['tipe']; ?></td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tipe onkeydown='upperCaseF(this)' onkeypress='return tanpa_kutip(event)' maxlength=40 style='width:200px'><input id=tipe2 style='display:none'>
			</td>
		</tr>
		<tr>
			<td class="bintang"><?php echo "Nama" ?></td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=nama maxlength=200 style='width:200px'>
			</td>
		</tr>
		</tr>
		<?php
		if ($_SESSION['language'] == 'EN') {
			$zz = 'namaakun1 as namaakun';
		} else {
			$zz = 'namaakun';
		}
		$str = "select noakun," . $zz . " from " . $dbname . ".keu_5akun where detail=1 and (LEFT(noakun, 3) IN ('211', '218'))";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$opt = "";
		while ($bar = $res->fetch()) {
			$opt .= "<option value='" . $bar->noakun . "'>" . $bar->noakun . " - " . $bar->namaakun . "</option>";
		}
		echo " <tr><td class=bintang>" . $_SESSION['lang']['noakun'] . "</td>
					  <td>:</td>
					  <td><select style=width:205px id=akun>" . $opt . "</select></td></tr>";

		echo " 
				<tr>
					<td class=bintang>Kelompok</td>
					<td>:</td>
					<td><select style=width:205px id=kelompok>" . $optkel . "</select></td>
				</tr>";


		?>
		<input type=hidden value='insert' id=method>
		<tr>
			<td>
			<td>
			<td>
				<button class=mybutton onclick=saveKelSup()><?php echo $_SESSION['lang']['save']; ?></button>
				<button class=mybutton onclick=cancelKelSup()><?php echo $_SESSION['lang']['cancel']; ?></button>
			</td>
			</td>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset style='float:left'>
	<legend>Keterangan</legend>
	Kelompok hanya digunakan untuk tipe <b>SUPPLIER</b>, karena adanya pengelompokan lagi untuk sub suppplier.
</fieldset>

<?
CLOSE_BOX();
OPEN_BOX();
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['list']; ?></legend>
	<div style="width:100%;overflow:auto;height:350px;">
		<table class=sortable cellspacing=1 border=0>
			<thead>
				<tr>
					<td align=center><?php echo $_SESSION['lang']['nourut']; ?></td>
					<td align=center><?php echo $_SESSION['lang']['Type']; ?></td>
					<td align=center><?php echo $_SESSION['lang']['nama']; ?></td>
					<td align=center><?php echo $_SESSION['lang']['noakun']; ?></td>
					<td align=center>Kelompok</td>
					<td align=center>Action</td>
				</tr>
			</thead>
			<tbody id=container>
				<?php
				$str = " select * from " . $dbname . ".log_5klsupplier order by tipe";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);

				$no = 0;
				while ($bar = $res->fetch()) {
					$no += 1;
					echo "<tr class=rowcontent>
		  <td align=center>" . $no . "</td>
			  <td>" . $bar->tipe . "</td>
			  <td>" . $bar->kode . "</td>
			  <td align=center>" . $bar->noakun . "</td>
			  <td align=center>" . $bar->kelompok . "</td>
			  <td align=center><img src=images/application/application_edit.png class=resicon  title='Update' onclick=\"editKlSupplier('" . $bar->tipe . "','" . $bar->kode . "','" . $bar->noakun . "','" . $bar->kelompok . "');\"></td>
			
			 </tr>";
				}
				?>
			</tbody>
			<tfoot></tfoot>
		</table>
	</div>
</fieldset>
<?php
CLOSE_BOX();
echo close_body();
?>