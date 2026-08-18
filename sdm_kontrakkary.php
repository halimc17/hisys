<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/sdm_kontrakkary.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
##deklarasi untuk option##
$optsat=$optjenis=$optorg =$optkary= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$where='';
if ($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$where=" and lokasitugas ='".$_SESSION['empl']['lokasitugas']."'";
}
$str="select * from ".$dbname.".datakaryawan where tanggalkeluar='0000-00-00'  and statuskaryawan != 'Keluar'  ".$where." order by namakaryawan asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optkary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." ".$bar['lokasitugas']."</option>";
}

$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(kodept) as kodeorganisasi FROM " . $dbname . ".lgl_dokumenlegal";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $nmorg[$bar['kodeorganisasi']] . "</option>";
}

$tempjns = getEnum($dbname,'sdm_kontrakkary','jenis');
foreach($tempjns as $key){
	$optjenis.="<option value=" . $key. ">" . $key. "</option>";
}
$tempsat=array('yy'=>'Tahun','mm'=>'Bulan','dd'=>'Hari');
foreach($tempsat as $key){
	$optsat.="<option value=" . $key. ">" . $key. "</option>";
}

##HEADER UNTUK BUAT BARU SAMA LIST
OPEN_BOX('','<span class=judul>'.getMenu('sdm_kontrakkary').'</span>');
echo"<div id=action_list>"; //buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
			<tr>
				<td>" . $_SESSION['lang']['pt'] . "</td> 
				<td>:</td>
				<td><select id=divsch onchange='loaddata()' style=\"width:150px;\">" . $optunit . "</select></td>
				</tr>
				";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";

echo"</td></tr></table> ";
echo "</div>";
CLOSE_BOX();
##UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER

echo"<div id=listData style=display:block>"; # buka list data
OPEN_BOX();
echo "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable style=min-width:50%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['pt'] . "</td>
				<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
				<td align=center>" . $_SESSION['lang']['jenis'] . "</td>
				<td align=center>" . $_SESSION['lang']['updateby'] . "</td>

				<td align=center colspan='3'>" . $_SESSION['lang']['action'] . "</td>
		</thead>
		 <tbody id=contain> 
			<script>loaddata(0)</script>
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
		 </div>
	</fieldset>";
CLOSE_BOX();
echo "</div>"; //tutup list data
##UNTUK BUAT FORM INPUT HEADER

echo "<div id=header style=display:none>";
OPEN_BOX();
echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
    <tr>
		<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
		<td>:</td>
		<td><input id=notransaksi disabled placeholder='otomatis' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:195px;\"></td>
		
		<td>" . $_SESSION['lang']['gajipokok'] . "</td> 
		<td>:</td>
		<td><input id=gajipokok onkeyup=\"z.numberFormat('gajipokok',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:195px;\"></td>
    </tr>
	<tr>
		<td>" . $_SESSION['lang']['pt'] . "</td> 
		<td>:</td>
		<td><select style=\"width:200px;\" onchange=getkary(this.value) id=pt>" . $optorg . "</select></td>
		
		
		<td>" . $_SESSION['lang']['tjjabatan'] . "</td> 
		<td>:</td>
		<td><input id=tunjjabatan onblur=\"z.numberFormat('tunjjabatan',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:195px;\"></td>
		
    </tr>
	<tr>
		<td>" . $_SESSION['lang']['jenis'] . "</td> 
		<td>:</td>
		<td><select style=\"width:200px;\" onchange=gettglkont(this.value); id=jenis>" . $optjenis . "</select></td>
		
		
		<td>Tunjangan Konsumsi</td> 
		<td>:</td>
		<td><input id=konsumsi onblur=\"z.numberFormat('konsumsi',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:195px;\"></td>
	
    </tr>
	<tr>
		<td>Pihak Pertama</td> 
		<td>:</td>
		<td><select style=\"width:200px;\" id=pihakpertama>" . $optkary . "</select>
			<img id='pihakpertama' onclick=z.elSearch('pihakpertama',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
		
		
		<td>Tunjangan Transportasi</td> 
		<td>:</td>
		<td><input id=transport onblur=\"z.numberFormat('transport',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:195px;\"></td>
	
    </tr>
	<tr>
		<td>" . $_SESSION['lang']['namakaryawan'] . "</td> 
		<td>:</td>
		<td><select style=\"width:200px;\" id=karyawanid onchange='getpoh()'>" . $optkary . "</select>
			<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
		
		<td>Tunjangan Daerah</td> 
		<td>:</td>
		<td><input id=uangdaerah onblur=\"z.numberFormat('uangdaerah',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:195px;\"></td>
	
    </tr>
	<tr>
		<td>Atasan Langsung</td> 
		<td>:</td>
		<td><input id=atasanlangsung class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:195px;\"></td>
		
		<td>Jumlah Hak Cuti</td> 
		<td>:</td>
		<td><input id=cuti maxlength=2 class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:50px;\">
			&nbsp;POH :<input id=poh class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:103px;\">
		</td>
    </tr>
	<tr>
		<td>Dikeluarkan</td> 
		<td>:</td>
		<td><input id=dikeluarkan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:95px;\">
		<input type='text' style='width:92px;' class='myinputtext' id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
		
		<td>Tiket Transport Cuti</td> 
		<td>:</td>
		<td><input id=tiketcuti placeholder='format isian : 1/2 atau 2/3' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:195px;\">
		</td>
    </tr>
	<tr>
		<td>Tanggal Kontrak</td> 
		<td>:</td>
		<td><input type='text' style='width:95px;' class='myinputtext' id='tanggaldari' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
			<input type='text' style='width:92px;' class='myinputtext' id='tanggalsampai' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
		</td>
		
		<td>Perumahan dan Utilitas</td> 
		<td>:</td>
		<td><input id=perumahan onblur=\"z.numberFormat('perumahan',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:195px;\"></td>

    </tr>
	<tr>	
		<td>Jangka Waktu</td> 
		<td>:</td>
		<td><input id=jangkawaktu class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:95px;\">
			<select style=\"width:95px;\" id=satjangka>" . $optsat . "</select>
		</td>
		
		
		<td>Telekomunikasi</td> 
		<td>:</td>
		<td><input id=telekomunikasi onblur=\"z.numberFormat('telekomunikasi',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:195px;\"></td>

    </tr>
	<tr>
		<td colspan=2></td>
		<td><input type=hidden id=method value='insert'>
			<button id=tombolsimpan class=mybutton onclick=save()>" . $_SESSION['lang']['save'] . "</button>
			<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td colspan=4 id=hasil></td>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>