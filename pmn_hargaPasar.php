<? //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/pmn_hargapasar.js'></script>
<?
$arr = "##tglHarga##kdBarang##satuan##idPasar##idMatauang##hrgPasar##status##ffa##mni##proses##keterangan";
include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul>' . getMenu('pmn_hargaPasar') . '</span>');
$optBrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optKodeSat = $optKode = $optPasar = $optBrg;

$sBrng = "select distinct kodebarang,namabarang from " . $dbname . ".log_5masterbarang where kelompokbarang='400' order by namabarang asc";
//$qBrng=mysql_query($sBrng) or die(mysql_error($conn));
//while($rBarang=mysql_fetch_assoc($qBrng))
$qBrng = $owlPDO->query($sBrng) or die(print " Gagal: " . PDOException::getMessage());
$qBrng->setFetchMode(PDO::FETCH_ASSOC);
while ($rBarang = $qBrng->fetch()) {
	$optBrg .= "<option value='" . $rBarang['kodebarang'] . "'>" . $rBarang['kodebarang'] . " - " . $rBarang['namabarang'] . "</option>";
}
$sData = "select distinct kode  from " . $dbname . ".setup_matauang order by kode asc";
//$qData=mysql_query($sData) or die(mysql_error($conn));
$optKode .= "<option value='IDR'>IDR</option>";
//while($rData=mysql_fetch_assoc($qData))
$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while ($rData = $qData->fetch()) {
	$optKode .= "<option value='" . $rData['kode'] . "'>" . $rData['kode'] . "</option>";
}
/*$arrenum=getEnum($dbname,'pmn_hargapasar','pasar');
foreach($arrenum as $key=>$val)
{
	$optGoldar.="<option value='".$key."'>".$val."</option>";
} */

$iPasar = "select * from " . $dbname . ".pmn_5pasar order by pasar asc ";
//$nPasar=  mysql_query($iPasar) or die (mysql_errn($conn));
//while($dPasar=  mysql_fetch_assoc($nPasar))
$nPasar = $owlPDO->query($iPasar) or die(print " Gagal: " . PDOException::getMessage());
$nPasar->setFetchMode(PDO::FETCH_ASSOC);
while ($dPasar = $nPasar->fetch()) {
	$optPasar .= "<option value='" . $dPasar['pasar'] . "'>" . $dPasar['pasar'] . "</option> ";
}

$arrSatuan = array("KG", "TON");
foreach ($arrSatuan as $der) {
	$optKodeSat .= "<option value='" . $der . "'>" . $der . "</option>";
}
$optStatus = "<option value='Best Bidder'>Best Bidder</option>";
// $optStatus.="<option value='Price Idea'>Price Idea</option>";
$optStatus .= "<option value='Traded'>Traded</option>";

echo "<fieldset>
     <legend>" . $_SESSION['lang']['hargapasar'] . "</legend>
	 <table>
	 <tr>
	   <td>" . $_SESSION['lang']['tanggal'] . "</td>
	   <td>:</td>
	   <td ><input type=text class=myinputtext id=tglHarga onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\" readonly/></td>
	   
	   <td>" . $_SESSION['lang']['komoditi'] . "</td>
	   <td>:</td>
	   <td><select id=kdBarang style=\"width:150px;\" onchange=getpasar(this.options[this.selectedIndex].value) >" . $optBrg . "</select></td>
	   
	    <td>" . $_SESSION['lang']['harga'] . "</td>
	   <td>:</td>
	   <td ><input type=text class=myinputtextnumber id=hrgPasar onkeypress=\"return angka_doang(event);\" style=\"width:150px;\"  /> </td>
	   
	    <td>" . $_SESSION['lang']['keterangan'] . "</td>
		   <td>:</td>
		   <td colspan=6><input type=text class=myinputtext id=keterangan onkeypress=\"return tanpa_kutip(event);\" style=\"width:300px;\"  /> </td>
	  
	 </tr>
	 <tr>
	   <td>" . $_SESSION['lang']['satuan'] . "</td>
	   <td>:</td>
	   <td><select id=satuan style=\"width:150px;\">" . $optKodeSat . "</select></td>
	 
	   <td>" . $_SESSION['lang']['pasar'] . "</td>
	   <td>:</td>
	   <td ><select id=idPasar style=\"width:150px;\">" . $optPasar . "</select></td>
	   
	   <td>FFA</td>
	   <td>:</td>
	   <td ><input type=text class=myinputtextnumber id=ffa onkeypress=\"return angka_doang(event);\" style=\"width:150px;\"  /> </td>
	 </tr>
	 
	  <tr>
	   <td>" . $_SESSION['lang']['matauang'] . "</td>
	   <td>:</td>
	   <td ><select id=idMatauang style=\"width:150px;\">" . $optKode . "</select></td>
	 
	   <td>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['harga'] . "</td>
	   <td>:</td>
	   <td><select id=status style=\"width:150px;\">" . $optStatus . "</select></td>
	   
	   <td>M & I</td>
	   <td>:</td>
	   <td ><input type=text class=myinputtextnumber id=mni onkeypress=\"return angka_doang(event);\" style=\"width:150px;\"  /> </td>
	 </tr>	
       <tr>
		  
	 </tr>	


	 <input type=hidden value=insert id=proses>
	<tr>
	<td colspan=2><td colspan=4>
	 <button class=mybutton onclick=saveFranco('pmn_slave_hargapasar','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>
	 <button class=mybutton onclick=cancelIsi()>" . $_SESSION['lang']['cancel'] . "</button>
	 </td>
	 </tr>
     	 </table>
	 </fieldset><input type='hidden' id=idFranco name=idFranco />";
CLOSE_BOX();
OPEN_BOX();

// echo "<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>";
echo "<table cellpadding=1 cellspacing=1 border=0<tr><td>" . $_SESSION['lang']['tanggal'] . " : <input type=text class=myinputtext id=tglCri onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10  readonly/>";
echo "&nbsp;" . $_SESSION['lang']['komoditi'] . " : <select id=kdBrgCari style=\"width:150px;\">" . $optBrg . "</select>";
echo "&nbsp;" . $_SESSION['lang']['pasar'] . " : <select id=idPsrCari style=\"width:150px;\">" . $optPasar . "</select><button class=mybutton onclick=cariTransaksi()>" . $_SESSION['lang']['find'] . "</button></td></tr></table>";
echo "
    <table class=sortable cellspacing=1 border=0  width=100% cellpadding=5>
     <thead>
	  <tr class=rowheader>
	   <th align=center>No</th>
	   <th align=center hidden>" . $_SESSION['lang']['notransaksi'] . "</th>
	   <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
	   <th align=center>" . $_SESSION['lang']['komoditi'] . "</th>
	   <th align=center>" . $_SESSION['lang']['satuan'] . "</th>
	   <th align=center>" . $_SESSION['lang']['pasar'] . "</th>
	   <th align=center>" . $_SESSION['lang']['matauang'] . "</th>
	   <th align=center>" . $_SESSION['lang']['harga'] . "</th>
	   <th align=center>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['harga'] . "</th>  
	   <th align=center>FFA</th>
	   <th align=center>M & I</th>
	   <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
	   <th align=center>" . $_SESSION['lang']['updateby'] . "</th>
	   <th align=center colspan=3>" . $_SESSION['lang']['action'] . "</th>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo "<script>loadData()</script>";

echo "</tbody>
     <tfoot>
	 </tfoot>
	 </table>";
	 // </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>