<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/bgt_5capex.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('bgt_5capex').'</span>');

//get kelompok barang
$optkelbrg = '';
$str = "select * from " . $dbname . ".log_5subklbarang where left(kelompok,1)='9' order by kode";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optaset=$optkelbrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while ($bar = $res->fetch()) {
    $optkelbrg.="<option value='" . $bar->kode . "'>" . $bar->kode. " - " . $bar->namasubkelompok. "</option>";
}

//get kode capex dari aset
$wh=" and kodetipe not in ('BG')";

$str="select kodetipe,namatipe from ".$dbname.".sdm_5tipeasset where 1=1 ".$wh." order by kodetipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optaset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
    $optaset.="<option value='".$bar->kodetipe."'>".$bar->kodetipe." - ".$bar->namatipe."</option>";
}


?>
<br><fieldset style="float:left">
	<legend><?echo $_SESSION['lang']['form'];?></legend>
<table border="0" cellspacing="0">
  <tr>
    <td><?echo $_SESSION['lang']['kodecapex'];?></td><td> : </td>
    <td colspan=4><select style="width:200px" id="kdcapex"><? echo $optaset; ?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['kelompokbarang'];?>
    </td><td> : </td>
    <td colspan=4><select style="width:200px" id="kelbrg"><? echo $optkelbrg; ?></select></td>
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

	<table class=sortable cellpadding=5 cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>Capex</td>
				<td align=center>".$_SESSION['lang']['kelompokbarang']."</td>
			</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
		<tfoot id='footData'>
		</tfoot>
		</tbody>
	</table>
";
CLOSE_BOX();
echo close_body();
?>
<!-- <td style='text-align:center'>".$_SESSION['lang']['action']."</td> -->