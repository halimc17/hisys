<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/pmn_estimasipenerimaan.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('pmn_estimasipenerimaan').'</span>');

#= option unit
$optkodebarang=$optper=$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." &nbsp;&nbsp;&nbsp;".$bar['namaorganisasi']."</option>";
}

for($x=0;$x<=2;$x++){
	$dte=mktime(0,0,0,(date('m')+2)-$x,15,date('Y'));
	@$optper.="<option value=".date("Y-m",$dte).">".date("Y-m",$dte)."</option>";
}


$str="SELECT kodebarang,namabarang FROM ".$dbname.".log_5masterbarang WHERE inactive='0' and kelompokbarang='400' order by namabarang asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optkodebarang.="<option value=".$bar['kodebarang'].">".$bar['namabarang']."</option>";
}


echo"
<br><fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
<table border=0 cellspacing=0>
 <tr>
    <td>".$_SESSION['lang']['periode']."</td><td> : </td>
    <td><select style=width:150px id=periode>".$optper."</select></td>
  </tr>
  <tr>
    <td>".$_SESSION['lang']['pt']."</td><td> : </td>
    <td ><select style=width:150px id=pt>".$optpt."</select></td>
  </tr>
  
	<tr>
		<td>".$_SESSION['lang']['komoditi']."</td>
		<td>:</td>
		<td><select id=kodebarang style=\"width:150px;\">".$optkodebarang."</select></td>
	</tr>
 
	<tr>	
		<td>Qty</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=qty value=0  style=width:150px maxlength=8 onkeypress='return angka_doang(event)' > Kg</td>
	</tr>
	<tr>	
		<td>".$_SESSION['lang']['harga']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=harga value=0  style=width:150px maxlength=8 onkeypress='return angka_doang(event)' ></td>
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
	<table class=sortable cellspacing=1 cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				
				<td align=center>".$_SESSION['lang']['periode']."</td>
				<td align=center>".$_SESSION['lang']['pt']."</td>
				<td align=center>".$_SESSION['lang']['komoditi']."</td>
				<td align=center>Qty<br>(Kg)</td>
				<td align=center>".$_SESSION['lang']['harga']."<br>(Rp)</td>
				<td align=center>".$_SESSION['lang']['total']."<br>(Rp)</td>
				<td style='text-align:center' colspan=3>".$_SESSION['lang']['action']."</td>
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