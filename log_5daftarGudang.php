<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/kelompok_barang.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['daftargudang']).'</span>');
echo"<fieldset><table class=sortable cellspacing=1 border-0>
     <thead>
	   <tr class=rowheader>
	     <td align=center>No.</td>
		 <td>" . $_SESSION['lang']['kodeorg'] . "</td>
		 <td>" . $_SESSION['lang']['namaorganisasi'] . "</td>
		 <td>" . $_SESSION['lang']['parent'] . "</td>
		 <td>" . $_SESSION['lang']['alamat'] . "</td>
	   </tr>
	 </thead>
	 <tbody>";
$str = "select * from " . $dbname . ".organisasi where tipe='GUDANG' order by kodeorganisasi";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $res->fetch()) {
    $no+=1;
    echo" <tr class=rowcontent>
	     <td align=center>" . $no . "</td>
		 <td>" . $bar->kodeorganisasi . "</td>
		 <td>" . $bar->namaorganisasi . "</td>
		 <td>" . $bar->induk . "</td>
		 <td>" . $bar->alamat . ", " . $bar->wilayahkota . ", " . $bar->negara . ", " . $bar->kodepos . "</td>
	   </tr>";
}

echo"</tbody>
	 <tfoot>
	 </tfoot>
	 </table></fieldset>
	 ";
CLOSE_BOX();
echo close_body();
?>