<?

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src=js/pmn_5franco.js></script>
<?

$arr = "##idFranco##nmFranco##almtFranco##jual##method##aslbrg##dsrtim";
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pmn_5franco').'</span>');
$optorg=$optJual=$opttimb="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

//Loco, Franco, FOB
$optJual.="<option value='loco'>LOCO</option>";
$optJual.="<option value='franco'>FRANCO</option>";
$optJual.="<option value='fob'>FOB</option>";
$optJual.="<option value='cif'>CIF</option>";


$opttimb.="<option value='0'>Penjual</option>";
$opttimb.="<option value='1'>Pembeli</option>";

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optorg.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

echo"<fieldset>
     <legend>" . $_SESSION['lang']['form'] . "</legend>
	 <table>
	 <tr>
	   <td>" . $_SESSION['lang']['nama'] . "</td>
	   <td>:</td>
	   <td colspan=4><input type=text class=myinputtext id=nmFranco name=nmFranco onkeypress=\"return tanpa_kutip(event);\" style=\"width:219px;\" maxlength=100 /></td>
	 </tr>
	 <tr>
	   <td valign=top>" . $_SESSION['lang']['alamat'] . "</td>
	   <td valign=top>:</td>
	   <td colspan=4><textarea style=\"width:200px;\" id=almtFranco name=almtFranco></textarea></td>
	 </tr>
	 
	 <tr hidden>
	   <td valign=top>" . $_SESSION['lang']['asalbarang'] . "</td>
	   <td valign=top>:</td>
	   <td><select id=aslbrg style=\"width:200px;\">" . $optorg . "</select></td>
	 </tr>
	 
	 <tr>
	   <td valign=top>" . $_SESSION['lang']['dasartimbangan'] . "</td>
	   <td valign=top>:</td>
	   <td><select id=dsrtim style=\"width:200px;\">" . $opttimb . "</select></td>
	 </tr>

	 <tr>
		<td>" . $_SESSION['lang']['penjualan'] . "</td>
		<td>:</td>
		<td><select id=jual style=\"width:200px;\">" . $optJual . "</select></td>
	 </tr> 
	 <tr>
		<td align=left>" . $_SESSION['lang']['status'] . "</td>
		<td>:</td>
		<td><input type='checkbox' id=statFr name=statFr />" . $_SESSION['lang']['tidakaktif'] . "</td>
	 </tr>
	 <td><td colspan=3>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=saveFranco('pmn_slave_5franco','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>
	 <button class=mybutton onclick=cancelIsi()>" . $_SESSION['lang']['cancel'] . "</button>
     </table>
	 </fieldset><input type='hidden' id=idFranco name=idFranco />";
CLOSE_BOX();
OPEN_BOX();
//$str="select * from ".$dbname.".setup_franco order by id_franco desc";
//$res=mysql_query($str);
echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td align=center>No</td>
	   <td align=center>" . $_SESSION['lang']['penjualan'] . "</td>
	   <td align=center>" . $_SESSION['lang']['alamat'] . "</td>
	   <td align=center style=\"width:60px;\">" . $_SESSION['lang']['franco'] . " " . $_SESSION['lang']['penjualan'] . "</td>
	   <td align=center>" . $_SESSION['lang']['dasartimbangan'] . "</td>
	   <td align=center>" . $_SESSION['lang']['status'] . "</td>
	   <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
	   <td align=center>" . $_SESSION['lang']['action'] . "</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>loadData()</script>";
//$no=0;	 
//while($bar=mysql_fetch_object($res))
//{
//  $no+=1;	
//  echo"<tr class=rowcontent>
//	  <td>No</td>
//	   <td>Nama Franco</td>
//	   <td>".$_SESSION['lang']['alamat']."</td>
//	   <td>Kontak Person</td>
//	   <td>".$_SESSION['lang']['telp']."</td>
//	   <td>".$_SESSION['lang']['status']."</td>
//	   <td>
//		      <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kode."','".$bar->kelompok."','".$bar->kelompokbiaya."','".$bar->noakun."');\"> 
//			  <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delKelompok('".$bar->kode."','".$bar->kelompok."');\">
//		  </td>
//	   
//	  </tr>";	
//}     
echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>