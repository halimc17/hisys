<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_5bpjs.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5bpjs').'</span>');

$optjenisplus=$optjenis=$optlokasi= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

#= option unit
$str = "select * from ".$dbname.".sdm_ho_component where id in (70,71,72,73,80) and plus=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optjenisplus.="<option value='".$bar['id']."'>".$bar['name']."</option>";
}

$str = "select * from ".$dbname.".sdm_ho_component where id in (3,61,67,44,81) and plus=0";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optjenis.="<option value='".$bar['id']."'>".$bar['name']."</option>";
}

$arrenum = getEnum($dbname, 'sdm_5bpjs', 'lokasibpjs');
foreach ($arrenum as $key => $val) {
    $optlokasi.="<option value='" . $key . "'>" . $val . "</option>";
}


echo"
<br><fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend><table border=0  cellspacing=1 cellpadding =3>

	<tr>
		<td>".$_SESSION['lang']['lokasi']."</td>
		<td> : </td>
		<td><select style=width:150px id=lokasibpjs>".$optlokasi."</select></td>
		<td>Beban Karyawan ( % )</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=bebankaryawan value=0  style=width:100px maxlength=8 onkeypress='return angka_doang(event)' ></td>
		<td hidden>Beban Karyawan Normal (%)</td>
		<td hidden> : </td>
		<td hidden><input type=text class=myinputtextnumber id=bebankaryawantpdiskon value=0  style=width:100px maxlength=8 onkeypress='return angka_doang(event)' ></td>
	</tr>
  
	
	<tr>
		<td>".$_SESSION['lang']['jenis']." +</td>
		<td> : </td>
		<td><select style=width:150px id=jenisbpjsplus>".$optjenisplus."</select></td>
		<td>Beban Perusahaan ( % )</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=bebanperusahaan value=0  style=width:100px maxlength=8 onkeypress='return angka_doang(event)' ></td>
		<td hidden>Beban Perusahaan Normal (%)</td>
		<td hidden> : </td>
		<td hidden><input type=text class=myinputtextnumber id=bebanperusahaantpdiskon value=0  style=width:100px maxlength=8 onkeypress='return angka_doang(event)' ></td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['jenis']." -</td>
		<td> : </td>
		<td><select style=width:150px id=jenisbpjs>".$optjenis."</select></td>
		<td>Gaji Maksimal (Rp)</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=maxgaji value=0  style=width:100px maxlength=8 onkeypress='return angka_doang(event)' ></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
 
  <input type=hidden id=method value='insert'>
  <tr>
  	<td><td>
  	<td><button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td>
  </tr>
	 
</table>";

CLOSE_BOX();
OPEN_BOX();

echo "
<fieldset>
	<legend><b>".$_SESSION['lang']['list']."</legend>
	<table class=sortable cellspacing=1 cellpadding = 5 style='width:100%;'  border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['lokasi']."</td>
				<td align=center>".$_SESSION['lang']['jenis']."<br>+</td>
				<td align=center>".$_SESSION['lang']['jenis']."<br>-</td>
				<td align=center>Beban<br>Karyawan ( % )</td>
				<td align=center>Beban<br>Perusahaan ( % )</td>
				<td hidden align=center>Beban<br>Karyawan Normal (%)</td>
				<td hidden align=center>Beban<br>Perusahaan Normal (%)</td>
				<td align=center>Gaji<br>Maksimal ( Rp )</td>
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