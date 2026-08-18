<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/konversi.js'></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['uomconversion']).'</span><br>');
$str = "select distinct satuan from " . $dbname . ".setup_satuan order by satuan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsatuan = '';
while ($bar = $res->fetch()) {
    $optsatuan.="<option value='" . $bar->satuan . "'>" . $bar->satuan . "</option>";
}
echo"<fieldset style=width:600px>
     <legend><b>" . $_SESSION['lang']['form'] . "</b></legend>
	 <table>
	 <tr>
	    <td class='bintang'>" . $_SESSION['lang']['materialname'] . "</td><td>:</td><td><span id=kodebarang></span><input type=text id=namadisabled size=43 class=myinputtext disabled>
		<img src=images/search.png class=dellicon title='" . $_SESSION['lang']['find'] . "' onclick=\"searchBarang('" . $_SESSION['lang']['findmaterial'] . "','<fieldset><legend>" . $_SESSION['lang']['findmaterial'] . "</legend>Find : <input type=text class=myinputtext id=namabrg> <button class=mybutton onclick=findBarang()>Find</button></fieldset><div id=container></div>',event);\">
		</td>
	 </tr> 
	 </table>
     </fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=width:600px>
     <legend><b>" . $_SESSION['lang']['newconversion'] . "</b></legend>
	 <table border=0>
	 <tr>
	 <td>".$_SESSION['lang']['materialname'] . "<b><td>:</td><td colspan=3><span id=captionbarang></span></b></td>
	 </tr>
	 <tr>
	 <td>".$_SESSION['lang']['smallestuom'] . "<b><td>:</td><td><span id=captionsatuan></span></b></td>
	 </tr>
	 <tr>
	 <td class='bintang'>".$_SESSION['lang']['uomsource'] . " <td>:</td><td> 1 <input type=text class=myinputtext id=satuansource disabled size=7 maxlength=10 onkeypress=\"return tanpa_kutip(event);\">
	 = <input type=text class=myinputtextnumber id=jumlah size=9 maxlength=100 onkeypress=\"return angka_doang(event);\">
	 
     " . $_SESSION['lang']['satuan'] . "<td><select id=satuandest>" . $optsatuan . "</select></td><tr><td>
	 " . $_SESSION['lang']['keterangan'] . " <td>:</td><td colspan=3><input type=text class=myinputtext id=keterangan size=45 maxlength=30 onkeypress=\"return tanpa_kutip(event);\"></td></tr>
     <tr><td><td><td>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=simpanKonversi()>" . $_SESSION['lang']['save'] . "</button>
	 <button class=mybutton onclick=batalKonversi()>" . $_SESSION['lang']['cancel'] . "</button>
	 
	 </td></tr></table>
	 </fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset  style=width:600px>
     <legend><b>" . $_SESSION['lang']['conversionlist'] . "</b></legend>
	 <table><tr><td>
	 " . $_SESSION['lang']['materialname'] . "</td><td>:</td><td><b><span id=captionbarang1></span></b><td></tr>
	 <tr><td>" . $_SESSION['lang']['smallestuom'] . "</td><td>:</td><td><b><span id=captionsatuan1></span></b><td></tr>
	 </table>
	 <table class=sortable cellspacing=1 border=0 style=width:100%>
	 <thead>
	 <tr class=rowheader>
	 <td align=center>No.</td>
	 <td align=center>" . $_SESSION['lang']['uomsource'] . "</td>
	 <td align=center>" . $_SESSION['lang']['uomdestination'] . "</td>
	 <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
	 <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
	 <td align=center>Action</td>
	 </tr>
	 </thead>
	 <tbody id=containersatuan>

	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>
     </fieldset>";
if ($_SESSION['language'] == 'EN') {
    $zz = 'kelompok1 as kelompok';
} else {
    $zz = 'kelompok';
}
$str = "select kode," . $zz . " from " . $dbname . ".log_5klbarang order by kelompok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optkelompok = "<option value=''></option>";
while ($bar = $res->fetch()) {
    $optkelompok.="<option value='" . $bar->kode . "'>" . $bar->kelompok . " [ " . $bar->kode . " ] </option>";
}
echo"<fieldset style=width:600px>
     <legend><b>" . $_SESSION['lang']['daftarbarang'] . "</b></legend>
      " . $_SESSION['lang']['pilihdata'] . " : <select id=kelompok onchange=ambilBarang(this.options[this.selectedIndex].value)>" . $optkelompok . "</select> 
     <div style='height:300px;width:600px;overflow:auto'>
     <table class=data cellspacing=1 border=0>
     <thead>
         <tr class=rowheader>
         <td class=firsttd  align=center>No.</td>
         <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
         <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
         <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
         <td align=center>" . $_SESSION['lang']['ke'] . " " . $_SESSION['lang']['satuan'] . "</td>
         <td align=center>Vol</td>
         </tr>
         </thead>
	 <tbody id=containerdetail>";
$str = "select a.*,b.namabarang,b.satuan as satuanori from " . $dbname . ".log_5stkonversi a
      left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $res->fetch()) {
    $no+=1;
    echo "<tr class=rowcontent>
         <td class=firsttd align=center>" . $no . "</td>
         <td>" . $bar->kodebarang . "</td>
         <td>" . $bar->namabarang . "</td>
         <td>" . $bar->satuanori . "</td>
         <td>" . $bar->satuankonversi . "</td>
         <td align=right>" . $bar->jumlah . "</td>
         </tr>";
}
echo"</tbody>
	 <tfoot>
	 </tfoot>
	 </table></div>
     </fieldset>";
CLOSE_BOX();
echo close_body();
?>