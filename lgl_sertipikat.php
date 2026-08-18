<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/lgl_sertipikat.js?v=<?php echo time();?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php


$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
##deklarasi untuk option##
$optorg =$optun=$optjns= "<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$optunit =$optunit2= "<option value=''></option>";
$sql = "SELECT distinct(kodept) as kodeorganisasi FROM " . $dbname . ".lgl_sertipikat";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $nmorg[$bar['kodeorganisasi']] . "</option>";
}

$sql = "SELECT distinct(unit) as kodeorganisasi FROM " . $dbname . ".lgl_sertipikat";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    @$optunit2.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $nmorg[$bar['kodeorganisasi']] . "</option>";
}

$str="select * from ".$dbname.".lgl_5jenissertipikat order by kode";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optjns.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['nama']."</option>";
}

$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc "; //exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$optPemilikSerti = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qPemilikSerti = selectQuery($dbname, 'lgl_sertipikat', '*', "1=1 GROUP BY pemiliksert ORDER BY pemiliksert ASC");
$resPemilikSerti = fetchData($qPemilikSerti);
foreach ($resPemilikSerti as $row) {
	$optPemilikSerti .= "<option value='".$row['pemiliksert']."'>".$row['pemiliksert']."</option>";
}

$optTglTerbitSertifikat = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qTglTerbitSertifikat = "SELECT distinct left(tglterbitsertifikat,4) as tahunsertifikat FROM `lgl_sertipikat`order by tglterbitsertifikat desc";
$resTglTerbitSertifikat = fetchData($qTglTerbitSertifikat);
foreach ($resTglTerbitSertifikat as $row) {
	$optTglTerbitSertifikat .= "<option value='".$row['tahunsertifikat']."'>".$row['tahunsertifikat']."</option>";
}

##HEADER UNTUK BUAT BARU SAMA LIST
OPEN_BOX('','<span class=judul>SERTIFIKAT DAN PBB</span>');
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
				
				<td>" . $_SESSION['lang']['jenis'] . "</td> 
				<td>:</td>
				<td><select id=jenissch onchange='loaddata()' style=\"width:150px;\">" . $optjns . "</select></td>

				<td>".$_SESSION['lang']['luas']."</td>
				<td>:</td>
				<td><input type=text id=luassch class=myinputtextnumber style=\"width:147px\" onkeypress='return angka_doang(event)' /></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=unitsch onchange='loaddata()' style=\"width:150px;\">" . $optunit2 . "</select></td>
				
				<td>" . $_SESSION['lang']['nomor'] . "</td> 
				<td>:</td>
				<td><input id=nohaksch onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:147px;\"></td>

				<td>Tahun Sertifikat</td> 
				<td>:</td>
				<td><select id=thnsertisch onchange='loaddata()' style=\"width:150px;\">" . $optTglTerbitSertifikat . "</select></td>

			</tr>
			<tr>
				<td>" . $_SESSION['lang']['lokasi'] . " Tanah</td> 
				<td>:</td>
				<td><input id=lokasisch onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:147px;\"></td>";

				// <td>Pemilik Sertifkat</td>
				// <td>:</td>
				// <td><select id=pemiliksertisch style=\"width:150px\">".$optPemilikSerti."</select></td>
				
			echo "
				<td>Pemilik Sertifkat</td>
				<td>:</td>
				<td><input style=\"width:147px;\" type=text class=myinputtext id=pemiliksertisch onkeyup=loaddata() /></td>

				<td>&nbsp;</td> 
				<td>&nbsp;</td>
				<td colspan=4>&nbsp;</td>
			</tr>
			";

echo"<tr>
<td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=excel_list()>" . $_SESSION['lang']['excel'] . "</button></td>
</td></tr></table>";

echo"</td></tr></table> ";
echo "</div>";
CLOSE_BOX();
##UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER

echo"<div id=listData style=display:block>"; # buka list data
OPEN_BOX();
echo "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['pt'] . "</td>
				<td align=center>" . $_SESSION['lang']['unit'] . "</td>
				<td align=center>" . $_SESSION['lang']['jenis'] . "</td>
				<td align=center>" . $_SESSION['lang']['nomor'] . " Hak</td>
				<td align=center>" . $_SESSION['lang']['nomor'] . " Nop</td>

				<td align=center>" . $_SESSION['lang']['lokasi'] . " Tanah</td>
				<td align=center>Luas (M2)</td>
				<td align=center>Pemilik Sertifikat</td>
				<td align=center>Tanggal Terbit Sertifikat</td>
				<td align=center>Masa Berlaku</td>
				<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
				<td align=center>" . $_SESSION['lang']['status'] . "</td>

				<td align=center colspan='5'>" . $_SESSION['lang']['action'] . "</td>
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
	<fieldset>
	<legend><i>Status Hak</i></legend>
	<table cellspacing=1 border=0  >
		<tr>
			<td class=bintang>".$_SESSION['lang']['pt']."</td> 
			<td>:</td>
			<td>
				<input type='hidden' id='id'>
				<input type='hidden' id='tipe' value=1>
				<select onchange=getunit() style=\"width:215px;\" id=pt>".$optorg."</select></td>
			
			<td class=bintang>".$_SESSION['lang']['unit']."</td> 
			<td>:</td>
			<td><select style=\"width:215px;\" id=unit>".$optun."</select></td>
		</tr>
		<tr>
			<td class=bintang>Jenis Status Hak</td> 
			<td>:</td>
			<td><select style=\"width:215px;\" id=jenis>".$optjns."</select></td>
			
			<td class=bintang>No. Hak</td> 
			<td>:</td>
			<td><input id=nohak type=text class=myinputtext onkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:80px;\">
			No. NOP :<input id=nonop type=text class=myinputtext onkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:75px;\">
			<input type=hidden id=nohakold value='' >
			</td>
		</tr>
		<tr>
			<td>Lokasi Tanah</td> 
			<td>:</td>
			<td><input id=lokasi class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:215px;\"></td>
		
			<td>Masa Berlaku</td> 
			<td>:</td>
			<td><input id='masaberlaku' type='text' style='width:215px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
		</tr>
		<tr>
			<td>Luas (M2)</td> 
			<td>:</td>
			<td>
			<table>
				<tr>
					<td><input id=luas onkeyup=\"z.numberFormat('luas',2)\" onkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:70px;\"></td>
					<td>No.NIB</td>
					<td>:<td>
					<td><input id=nib class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:80px;\"></td>
				</tr>
			</table>
			</td>
		
			<td>Nama Pemilik Sertifikat</td> 
			<td>:</td>
			<td><input id=pemiliksert class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:215px;\"></td>
		</tr>
		<tr>
			<td>No Surat Ukur</td> 
			<td>:</td>
			<td style='width:100px;'><input id=nosuratukur nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style='width:215px;'></td>

			<td>Tanggal Surat Ukur</td> 
			<td>:</td>
			<td colspan=1><input id='tglsrtukur' type='text' style='width:215px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
		</tr>
		<tr>
			<td>No Peta Pendaftaran</td> 
			<td>:</td>
			<td style='width:200px;'><input id=nopeta nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style='width:215px;'></td>

			<td>Tanggal Terbit Sertifikat</td> 
			<td>:</td>
			<td><input id='tglterbit' type='text' style='width:215px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
		</tr>
		<tr>
			<td>No Pengecekan Sertifikat</td> 
			<td>:</td>
			<td style='width:200px;'><input id=nocek nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style='width:215px;'></td>

			<td>Tanggal Pengecekan Sertifikat</td> 
			<td>:</td>
			<td><input id='tglcek' type='text' style='width:215px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
		</tr>
		<tr>
			<td>Keterangan</td> 
			<td>:</td>
			<td colspan=4><input id=ketstatushak class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:605px;\"></td>
		</tr>
		<tr><td></td></tr>
		<tr>
			<td colspan=2></td>
			<td colspan=10><input type=hidden id=method value='insert'>
			<!--<button class=mybutton onclick='showupload(event)'>Upload Files</button>-->
				<button id=tomboldetail class=mybutton onclick=save()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batal class=mybutton onclick=cleardetail()>" . $_SESSION['lang']['cancel'] . "</button>
				
			</td>
		</tr>
	</table>

	

		</fieldset>";
CLOSE_BOX();
echo"</div>";
echo"<div  id=detailpajakdanhak style=display:none>";
#---------Detail Pajak---------#
OPEN_BOX();
echo"<fieldset><legend><i>Pajak</i></legend>
		<table>
		<tr><input type=hidden id=idpajak >
			<td width=150px>Tahun Pajak</td> 
			<td>:</td>
			<td><input id=thnpajak class=myinputtextnumber maxlength=4 onkeypress=\"return angka_doang(event);\"  onkeypress=\"return_tanpa_kutip(event);\" style=\"width:98%;\"></td>
		</tr>
		
		<tr>
			<td width=150px>Nomor SPPT PBB</td> 
			<td>:</td>
			<td colspan=4><input id=nospptpbb class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
			
			<td>Nama Wajib Pajak</td> 
			<td>:</td>
			<td colspan=4><input id=namawp class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
		</tr>
		<tr>
			<td>Luas Tanah (M2)</td> 
			<td>:</td>
			<td style='width:100px;'><input id=nilaitanah onkeyup=\"z.numberFormat('nilaitanah',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:96%;\"></td>
			
			<td>Nilai NJOP<br>Tanah</td> 
			<td>:</td>
			<td style='width:100px;'><input id=nilainjoptanah onkeyup=\"z.numberFormat('nilainjoptanah',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:96%;\"></td>
			
			<td>Luas Bangunan (M2)</td> 
			<td>:</td>
			<td style='width:100px;'><input id=nilaibangunan onkeyup=\"z.numberFormat('nilaibangunan',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:96%;\"></td>
		
			
			<td>Nilai NJOP<br>Bangunan</td> 
			<td>:</td>
			<td style='width:100px;'><input id=nilainjopbangunan onkeyup=\"z.numberFormat('nilainjopbangunan',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:95%;\"></td>
		</tr>
		
		<tr>
			<td>PBB</td> 
			<td>:</td>
			<td style='width:100px;'><input id=pbb onkeyup=\"z.numberFormat('pbb',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:95%;\"></td>
			
			<td>Denda</td> 
			<td>:</td>
			<td style='width:100px;'><input id=denda onkeyup=\"z.numberFormat('denda',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:95%;\"></td>
			
			<td>Jatuh Tempo</td> 
			<td>:</td>
			<td colspan=1><input id='jatuhtempo' type='text' style='width:98%;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
			
			<td>Kurang Bayar</td> 
			<td>:</td>
			<td style='width:100px;'><input id=kurangbayar onkeyup=\"z.numberFormat('kurangbayar',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:95%;\"></td>
		</tr>
		
		<tr>
			<td>Letak Objek Pajak</td> 
			<td>:</td>
			<td colspan=4><input id=letakobjekpajak class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
			
			<td>Keterangan</td> 
			<td>:</td>
			<td colspan=4><input id=keterangan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
		</tr>
		<tr>
			<td>Status Bayar</td> 
			<td>:</td>
			<td colspan=4><input id=statusbayar class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:98%;\"></td>
			
			<td hidden>Status</td> 
			<td hidden>:</td>
			<td hidden colspan=4><input id=status type=checkbox checked onclick=getstatus()>
						  <label  hidden id=lstatus style='font-weight:bold'>Aktif</label></td>
			
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td colspan=10><input type=hidden id=methodpajak value='insertpajak'>
				<button id=tomboldetailpajak class=mybutton onclick=savepajak()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batalpajak class=mybutton onclick=cleardetailpajak()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>
		
		</table>
			
		</fieldset>";
echo"
	<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:950px>
		<thead>
		<tr class=rowheader>
			<td align=center width=30px>No</td> 
			<td align=center>Tahun<br>Pajak</td> 
			<td align=center>Nomor<br>SPPT PBB</td> 
			<td align=center>Nama<br>Wajib Pajak</td> 
			<td align=center>Luas<br>Tanah</td> 
			<td align=center>Nilai<br>NJOP Tanah</td> 
			<td align=center>Luas<br>Bangunan</td> 
			<td align=center>Nilai<br>NJOP Bangunan</td> 
			<td align=center>PBB</td> 
			<td align=center>Denda</td> 
			<td align=center>Jatuh<br>Tempo</td> 
			<td align=center>Kurang<br>Bayar</td> 
			<td align=center>Letak<br>Objek Pajak</td> 
			<td align=center>Keterangan</td> 
			<td align=center>Status<br>Bayar</td> 
			<td align=center colspan=3 width=40px>Action</td> 
		</tr>
		</thead>
		<tbody id=loaddatapajak></tbody>
	</table>
	</fieldset>
	";
	
CLOSE_BOX();

#---------Detail Pengalihan Hak---------#
OPEN_BOX();
$optjnsakta="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrJns=getEnum($dbname,'lgl_sertipikatdt','jenis');
foreach($arrJns as $key => $val){
	$optjnsakta.="<option value='".$val."'>".$val."</option>";
}
echo"
	<fieldset><legend><i>Pengalihan Hak</i></legend>
	<table>
		<tr style=display:none>
			<td  width=150px>Nomor ID</td> 
			<td>:</td>
			<td colspan=4><input id=nodetail class=myinputtext disabled style=\"width:98%;\"></td>

		</tr>
		
		<tr>
			<td  width=150px>Jenis Hak</td> 
			<td>:</td>
			<td colspan=4><select id=jenisakta style=\"width:99%;\">".$optjnsakta."</select></td>
			
			<td>Nama Pembuat Hak</td> 
			<td>:</td>
			<td colspan=4  width=250px><input id=pembuat class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
		</tr>
		
		<tr>
			<td>Nama Penjual</td> 
			<td>:</td>
			<td colspan=4><input id=namadetailakta class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
			
			<td>Nomor</td> 
			<td>:</td>
			<td colspan=4><input id=nodetailakta class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
		</tr>
		<tr>
			<td>Nama Pembeli</td> 
			<td>:</td>
			<td colspan=4><input id=namapembeli class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
			
			<td>Tanggal</td> 
			<td>:</td>
			<td style='width:75px;'><input id='tgldetailakta' type='text' style='width:97%;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>

			<td>Nilai</td> 
			<td>:</td>
			<td style='width:100px;'><input id=nilaidetailakta onkeyup=\"z.numberFormat('nilaiajb',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:95%;\"></td>
		</tr>
		<tr>
			<td>Keterangan</td> 
			<td>:</td>
			<td colspan=4><input id=ketdetailakta class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:98%;\"></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=10><input type=hidden id=methodakta value='insertakta'>
				<button id=tomboldetailakta class=mybutton onclick=saveakta()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batalakta class=mybutton onclick=cleardetailakta()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>

	</table>
		
	</fieldset>
	";
	
echo"
	<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
	<table cellpadding=1 cellspacing=1 border=0 class=sortable style=min-width:950px>
		<thead>
		<tr class=rowheader>
			<td align=center width=30px>No</td> 
			<td align=center>Jenis</td> 
			<td align=center>Nama Pembuat Hak</td> 
			<td align=center>Nama Penjual</td> 
			<td align=center>Nama Pembeli</td> 
			<td align=center>Nomor</td> 
			<td align=center>Tanggal</td> 
			<td align=center>Nilai</td> 
			<td align=center>Keterangan</td> 
			<td align=center colspan=2 width=40px>Action</td> 
		</tr>
		</thead>
		<tbody id=loaddataakta></tbody>
	</table>
	</fieldset>";
CLOSE_BOX();
OPEN_BOX();



CLOSE_BOX();
echo"</div>";
echo"<div id=persetujuan style=display:none>";
OPEN_BOX();
    echo "<div id=persetujuandata></div>";
CLOSE_BOX();
echo close_body();
?>