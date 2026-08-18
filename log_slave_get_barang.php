<?php

require_once('master_validation.php');
require_once('config/connection.php');

$txtfind = $_POST['txtfind'];

$str = " select * from " . $dbname . ".log_5masterbarang where namabarang like '%" . $txtfind . "%' or kodebarang like '%" . $txtfind . "%' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
echo"<fieldset><legend>Result</legend>
	<div style='height:330px;width:480px;overflow:auto'>
	<table class=data cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		 <td class=firsttd>
		 No.
		 </td>
		 <td style=width:50px align=center>Kode Kelompok</td>
		 <td align=center>Kode Barang</td>
		 <td align=center>Nama Barang</td>
		 <td align=center>Satuan</td>
		 </tr>
		 </thead>
		 <tbody>";
$no = 0;
while ($bar = $res->fetch()) {
    $no+=1;
    echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setKodeBarang('" . $bar->kelompokbarang . "','" . $bar->kodebarang . "','" . $bar->namabarang . "','" . $bar->satuan . "')\" title='Click' >
		      <td class=firsttd>" . $no . "</td>
		      <td>" . $bar->kelompokbarang . "</td>
			  <td>" . $bar->kodebarang . "</td><td>" . $bar->namabarang . "</td>
			  <td>" . $bar->satuan . "</td>
			 </tr>";
}
echo "</tbody>
	      <tfoot>
		  </tfoot>
		  </table></div></fieldset>";
?>