<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/pabrik_pemakaianbbm.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_pemakaianbbm').'</span>');
$optunit=$optkas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$optKaryawan=$optKaryawanpemohon=$optTuntas=$optStation="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

#= option unit
$str = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." &nbsp;&nbsp;&nbsp;".$bar['namaorganisasi']."</option>";
}

$str = "select noakun,namaakun from ".$dbname.".keu_5akun where length(noakun)=7 and kasbank=1 and namaakun like 'KAS KECIL%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optkas.="<option value='".$bar['noakun']."'>".$bar['noakun']." &nbsp;&nbsp;&nbsp; ".$bar['namaakun']."</option>";
}


$optPabrik='';
$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optPabrik.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi "
        . " where induk='".$_SESSION['empl']['lokasitugas']."' and tipe in ('STATION','MAINTENANCE')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optStation.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}


echo"
<br><fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
<table border=0 cellspacing=0>
  <tr hidden>
    <td>".$_SESSION['lang']['unit']."</td>
	<td>:</td>		
	<td><select id=unit disabled style=\"width:150px;\">'".$optPabrik."'</select></td>	
  </tr>
  <tr>
	<td>".$_SESSION['lang']['station']."</td>
	<td>:</td>		
	<td><select id=station onchange=getmesin() style=\"width:150px;\">'".$optStation."'</select></td>
  </tr>
  <tr>
	<td>".$_SESSION['lang']['mesin']."</td>
		<td>:</td>		
		<td><select id=mesin style=\"width:150px;\">'".$optMesin."'</select></td>
  </tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>				
	</tr>
	<tr>
		<td>".$_SESSION['lang']['saldoawal']."</td>
		<td>:</td>
		<td><input type=text id=sawal size=10 class=myinputtextnumber disabled value=0 maxlength=50 onkeypress=\"return angka_doang(event);\" style=\"width:125px;\">Ltr</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['masuk']."</td>
		<td>:</td>
		<td><input type=text id=masuk size=10 class=myinputtextnumber disabled value=0 maxlength=50 onkeypress=\"return angka_doang(event);\" style=\"width:125px;\">Ltr</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['keluar']."</td>
		<td>:</td>
		<td><input type=text id=keluar size=10 onkeyup=gethitung(); class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\" style=\"width:125px;\">Ltr</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['saldoakhir']."</td>
		<td>:</td>
		<td><input type=text id=salak size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\" style=\"width:125px;\">Ltr</td>
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
				<td align=center>".$_SESSION['lang']['station']."</td>
				<td align=center>".$_SESSION['lang']['mesin']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['saldoawal']."</td>
				<td align=center>".$_SESSION['lang']['masuk']."</td>
				<td align=center>".$_SESSION['lang']['keluar']."</td>
				<td align=center>".$_SESSION['lang']['saldoakhir']."</td>
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