<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/keu_5kaskecil.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('keu_5kaskecil').'</span>');

$optrek=$optper=$optunit=$optkas =$optkas2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

#= option unit
$str = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." &nbsp;&nbsp;&nbsp;".$bar['namaorganisasi']."</option>";
}

$str = "select noakun,namaakun from ".$dbname.".keu_5akun where length(noakun)=7 and kasbank=1 and noakun='1112102'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optkas.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
}

$str = "select noakun,namaakun from ".$dbname.".keu_5akun where length(noakun)=7 and kasbank=1 and noakun in ('1112101','1110101','2140101')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optkas2.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
}

for($x=0;$x<=12;$x++){
        $dte=mktime(0,0,0,(date('m')+2)-$x,15,date('Y'));
        $optper.="<option value=".date("Y-m",$dte).">".date("m-Y",$dte)."</option>";
}

echo"
<br><fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
<table border=0 cellspacing=0>
  <tr>
    <td>".$_SESSION['lang']['unit']."</td><td> : </td>
    <td ><select style=width:150px id=unit>".$optunit."</select></td>
  </tr>
  <tr>
    <td>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['kaskecil']."</td><td> : </td>
    <td><select style=width:150px id=noakun>".$optkas."</select></td>
  </tr>
   <tr>
    <td>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['kasbesar']."</td><td> : </td>
    <td><select style=width:150px id=noakun2 onchange='getbank()'>".$optkas2."</select></td>
  </tr>
   <tr>
    <td>".$_SESSION['lang']['rekening']."</td><td> : </td>
    <td><select style=width:150px id=rekening >".$optrek."</select></td>
  </tr>
   <tr>
    <td>".$_SESSION['lang']['periode']."</td><td> : </td>
    <td><select style=width:150px id=periode>".$optper."</select></td>
  </tr>
	<tr>
		<td>".$_SESSION['lang']['tanggalmulai']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggalmulai onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>				
	</tr>
  <tr>
		<td>".$_SESSION['lang']['tanggalselesai']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggalselesai onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>				
	</tr>
   <tr>
		<td>".$_SESSION['lang']['tanggaltopup']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggaltopup onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>				
	</tr>
  
  <tr>	
	<td>".$_SESSION['lang']['plafon']."</td>
	<td> : </td>
    <td><input type=text class=myinputtextnumber id=plafon value=0  style=width:150px  onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('plafon');\"></td>
  </tr>
  
  <tr>	
	<td>".$_SESSION['lang']['saldoberjalan']."</td>
	<td> : </td>
    <td><input type=text class=myinputtextnumber id=saldoberjalan  value=0 style=width:150px  onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('saldoberjalan');\"></td>
  </tr>
  
   <tr>	
	<td>".$_SESSION['lang']['batasbawah']."</td>
	<td> : </td>
    <td><input type=text class=myinputtextnumber id=batasbawah  value=0 style=width:150px  onkeypress='return tanpa_kutip_dan_sepasi(event)'' onkeyup=\"z.numberFormat('batasbawah');\"></td>
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
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['unit']."</td>
				<td align=center>".$_SESSION['lang']['noakun']."<br> Top Up</td>
				<td align=center>".$_SESSION['lang']['periode']."</td>
				<td align=center>".$_SESSION['lang']['rekening']."</td>
				<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>
				<td align=center>".$_SESSION['lang']['tanggalselesai']."</td>
				<td align=center>".$_SESSION['lang']['tanggaltopup']."</td>
				<td align=center>".$_SESSION['lang']['plafon']."</td>
				<td align=center>".$_SESSION['lang']['saldoberjalan']."</td>
				<td align=center>".$_SESSION['lang']['batasbawah']."</td>
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