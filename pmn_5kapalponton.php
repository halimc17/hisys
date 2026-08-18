<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>
<script language=javascript src='js/pmn_5kapalponton.js?v=<?php echo time(); ?>'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	


$opttransportir=$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$optpks.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('TRANSPORTIR') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$opttransportir.="<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}

$optjenis.="<option value='KPL'>".$_SESSION['lang']['kapal']."</option>";
$optjenis.="<option value='PNT'>".$_SESSION['lang']['ponton']."</option>";
$optjenis.="<option value='TRK'>Truck</option>";
	

OPEN_BOX('','<span class=judul>'.getMenu('pmn_5kapalponton').'</span><br>');
// style=\"width:550px;float:left\" 
echo "<br>";

echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				
				<tr>
					<td>".$_SESSION['lang']['jenis']."</td>
					<td>:</td>
					<td><select id=jenis style=\"width:150px;\" onchange=\"getkode()\" >".$optjenis."</select></td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['kode']."</td>
					<td>:</td>
					<td><input type=text disabled id=kode class=myinputtext style=\"width:150px;\"></td>
				</tr>	
				<tr>
					<td width=100>".$_SESSION['lang']['nama']."</td>
					<td>:</td>
					<td><input type=text id=nama  class=myinputtext style=\"width:150px;\"></td>
				</tr>	
				<tr>
					<td width=100>".$_SESSION['lang']['keterangan']."</td>
					<td>:</td>
					<td><input type=text id=keterangan  class=myinputtext style=\"width:150px;\"></td>
				</tr>					
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>Simpan</button>
					<button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
				</table></fieldset>
					<input type=hidden id=method value='insert'>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset style=float:left;>
	<legend>Form Search Data</legend> 
		<table border=0 cellpadding=1 cellspacing=1>
			
			<tr>
				<!--<td>".$_SESSION['lang']['jenis']."</td>
				<td>:</td>
				<td><select id=jenis style=\"width:150px;\" onchange=\"getkode()\" >".$optjenis."</select></td>
				-->
				<td>".$_SESSION['lang']['kode']."</td>
				<td>:</td>
				<td><input type=text id=kodesch class=myinputtext onkeyup=loaddata(0) style=\"width:150px;\"></td>

				<td>".$_SESSION['lang']['nama']." </td>
				<td>:</td>
				<td><input type=text id=namasch class=myinputtext onkeyup=loaddata(0) style=\"width:150px;\"></td>

				<td><button class=mybutton onclick=search()>Cari</button>
				<!--<button class=mybutton title='Bersihkan Riwayat Pencarian' onclick=cancelsch()>Bersihkan</button>--></td>
			</tr>
			<tr>
				<!--<td width=100>".$_SESSION['lang']['nama']." </td>
				<td>:</td>
				<td><input type=text id=namasch class=myinputtext onkeyup=loaddata(0) style=\"width:150px;\"></td>
				-->
				<!--<td width=100>".$_SESSION['lang']['keterangan']."</td>
				<td>:</td>
				<td><input type=text id=keterangan  class=myinputtext style=\"width:150px;\"></td>-->
			</tr>	
			<tr>
				<!--<td></td><td></td>
				<td><button class=mybutton onclick=search()>Cari</button>
				<button class=mybutton onclick=cancelsch()>Batal</button></td>-->
			</tr>
			</table></fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loaddata(0)</script>
		</div>
	</fieldset>";


CLOSE_BOX();
echo close_body();

?>