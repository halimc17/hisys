<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/umum_laporanpenghuni.js></script>
<?

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['penghuni']).'</span>');
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi 
      where tipe not in('STENGINE','BLOK','PT','HOLDING','GUDANG','STATION')
	  order by kodeorganisasi";
$optorg = "";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optorg.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}

echo"<fieldset style='width:450px;'>";
echo $_SESSION['lang']['kodeorganisasi'] . "<select id=kodeorg>" . $optorg . "</select>
    <button class=mybutton onclick=showPenguhi()>" . $_SESSION['lang']['tampilkan'] . "</button>";
echo"</fieldset>";
echo"<fieldset>
      <legend>" . $_SESSION['lang']['list'] . "</legend>
	  <table class=sortable border=0 cellspacing=1>
	  		<thead>
	  		 <tr class=rowheader>
			 <td>No</td>
			 <td>" . $_SESSION['lang']['kodeorg'] . "</td>
			 <td>" . $_SESSION['lang']['komplek_rmh'] . "</td>
			 <td>" . $_SESSION['lang']['blok'] . "</td>
			 <td>" . $_SESSION['lang']['no_rmh'] . "</td>
			 <td>" . $_SESSION['lang']['tipe'] . "</td>
			 <td>" . $_SESSION['lang']['jumlahpenghuni'] . "</td>
			 <td></td>
			 </tr>
			</thead>
			<tbody id=container>
			</tbody>
			<tfoot>
			</tfoot>
	  </table>
	  ";
echo"</fieldset>";
CLOSE_BOX();
echo close_body();
?>