<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_5kriteriapenilaian.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5kriteriapenilaian').'</span>');

//get jenis surat peringatan
$optkriteria = '';
$str = "select * from " . $dbname . ".sdm_5jeniskriteria order by kode";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optkriteria = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while ($bar = $res->fetch()) {
    $optkriteria.="<option value='" . $bar->kode . "'>" . $bar->kriteria. "</option>";
}

?>
<br><fieldset style="float:left">
	<legend><?echo $_SESSION['lang']['form'];?></legend>
<table border="0" cellspacing="0">
  <tr>
    <td><?echo $_SESSION['lang']['kriteria'];?></td><td> : </td>
    <td colspan=4><select style="width:200px" onchange="getkode(0,0)" id="kriteria"><? echo $optkriteria; ?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['id']." ".$_SESSION['lang']['penilaian'];?></td><td> : </td>
	<td><input type="text" class=myinputtext id="kodekriteria"  style="width:112px" maxlength=15 onkeypress="return tanpa_kutip(event)" disabled>
		<input type="text"  maxlength=3 class=myinputtext id="idnilai" placeholder="Auto Generate" style="width:75px" onkeypress="return tanpa_kutip(event)" readonly disabled /></td>
    </tr>
  <tr>	
	<td><?echo $_SESSION['lang']['penilaian'];?></td><td> : </td>
    <td><textarea id="penilaian" style="width:178px" rowspan="2" onkeypress="return tanpa_kutip(event)"></textarea></td>
  </tr>
  <input type=hidden id=method value='insert'>
  <tr>
  	<td><td>
  	<td><button class=mybutton onclick=simpan()><?php echo $_SESSION['lang']['save'] ?></button>
	<button class=mybutton onclick=cancel()><?php echo $_SESSION['lang']['cancel'] ?></button></td>
  </tr>
	 
</table>
<?php
CLOSE_BOX();
OPEN_BOX();

echo "
<fieldset>
	<legend><b>".$_SESSION['lang']['list']."</legend>
	<table class=sortable cellspacing=1 cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kriteria']."</td>
				<td align=center>".$_SESSION['lang']['penilaian']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
		<tfoot id='footData'>
		</tfoot>
		</tbody>
	</table>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>