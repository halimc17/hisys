<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//=============================================
//pencarian pada table masterbarang

$txtcari = $_POST['txtcari'];
$str = "select a.kodebarang,a.namabarang,a.satuan from
		      " . $dbname . ".log_5masterbarang a where a.namabarang like '%" . $txtcari . "%' or a.kodebarang like '%" . $txtcari . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);

if ($numrows < 1) {
    echo"Error: " . $_SESSION['lang']['tidakditemukan'];
} else {
    echo"
		<fieldset>
		<legend>" . $_SESSION['lang']['result'] . "</legend>
		<div style=\"max-width:650px; max-height:300px; overflow:auto;\">
			<table class=sortable cellspacing=1 border=0>
		     <thead>
			      <tr class=rowheader>
				      <td align=center>No</td>
					  <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
					  <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
					  <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
				  </tr>
		     </thead>
			 <tbody>";
    $no = 0;
    while ($bar = $res->fetch()) {
        $no+=1;
        echo"<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"throwThisRow('".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."');\">
				   <td align=center>" . $no . "</td>
				  <td>" . $bar->kodebarang . "</td>
				  <td>" . $bar->namabarang . "</td>
				  <td>" . $bar->satuan . "</td>
			      </tr>";
    }
    echo "
				 </tbody>
				 <tfoot></tfoot>
				 </table></div></fieldset>";
}

?>