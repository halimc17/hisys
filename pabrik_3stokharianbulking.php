
<?//@Copy nangkoelframework
//-----------------ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();


?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/pabrik_3stokharianbulking.js?v=<?php echo time(); ?>'></script>



<?

include('master_mainMenu.php');		

OPEN_BOX('','<span class=judul><b>'.getMenu('pabrik_3stokharianbulking').'</b><br><br></span>');
	
$optper=$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

// $str="SELECT * FROM ".$dbname.".organisasi where tipe='PABRIK' and length(kodeorganisasi)=4";
$str="SELECT * FROM ".$dbname.".organisasi where tipe='bulking'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$str="SELECT distinct(periode) as periode FROM ".$dbname.".setup_periodeakuntansi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optper.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}
$str = "select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}


$str="SELECT * FROM ".$dbname.".pabrik_5tangki where kodeorg ='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$opttangki.="<option value='".$bar['kodetangki']."'>".$bar['kodetangki']."</option>";
}
/*

	<tr>
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td><select id=kodebarang style=\"width:130px;\" >".$optbarang."</select></td>
	</tr>
*/
echo"<fieldset style=width:300px;float:left><legend><b>Form Tangki</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal name=tanggal readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
		</td>
	</tr>
	
	
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select id=unit style=\"width:130px;\" >".$optunit."</select></td>
	</tr>
	";/*
	<tr>
		<td>".$_SESSION['lang']['kodetangki']."</td>
		<td>:</td>
		<td><select id=kodetangki style=\"width:130px;\" >".$opttangki."</select></td>
	</tr>
	*/
echo"<tr>
		<td colspan=3 align=right>
		<button onclick=preview() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=excel() class=mybutton name=preview id=excel>".$_SESSION['lang']['excel']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";





echo"<fieldset style=width:300px;float:left><legend><b>Form PT</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggalpt name=tanggal readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
		</td>
	</tr>
	
	
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select id=unitpt style=\"width:130px;\" >".$optunit."</select></td>
	</tr><tr>
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td><select id=kodebarangpt style=\"width:130px;\" >".$optbarang."</select></td>
	</tr>";
echo"<tr>
		<td colspan=3 align=right>
		<button onclick=previewpt() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=excelpt() class=mybutton name=preview id=excel>".$_SESSION['lang']['excel']."</button>
		<button onclick=batalpt() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";


echo"<fieldset style=width:500px><legend><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
			<li>Pastikan transaksi BA Penerimaan sudah terinput dan terposting</li>
			<li>Pastikan transaksi BA Pengiriman sudah terinput dan terposting</li>
			<li>Pastikan transaksi BA Transfer Produk sudah terinput dan terposting</li>
			
</fieldset>";



echo"
<fieldset style=width:1150px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'  style='height:360px;width:1050px'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 

CLOSE_BOX();
echo close_body();

?>