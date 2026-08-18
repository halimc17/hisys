<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/kebun_premimandoran.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';
$frm[2]='';
$frm[3]='';
$frm[4]='';

$optTipe=$optgol=$optunitx=$optprdx="<option value=''>".$_SESSION['lang']['all']."</option>";
$optunit=$optafd=$optprd=$optprd2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optafd2="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
	$optunitx.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$whr='';
if($_SESSION['empl']['subbagian']!=''){
	$whr=" and kodeorganisasi='".$_SESSION['empl']['subbagian']."'";
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='AFDELING' ".$whr."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optafd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$optafd2.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}


$optjabatan="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrJab=array("MANDORPANEN"=>"Mandor Panen","MANDOR1"=>"Mandor 1","KERANIPANEN"=>"Kerani Panen","MANDORTRAKSI"=>"Mandor Traksi");
$str="select distinct(jabatan) as jabatan from ".$dbname.".kebun_premikemandoran where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optjabatan.="<option value=".$bar['jabatan'].">".$arrJab[$bar['jabatan']]."</option>";
}

$str="select distinct(substr(tanggal,1,7)) as periode from ".$dbname.".kebun_aktifitas order by periode desc limit 13";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optprd.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	$optprdx.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	$optprd2.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}

$opttahap="<option value='1'>Pertama</option>";
$opttahap.="<option value='2'>Kedua</option>";


@$optkontan.="<option value='KERJA'>Kerja</option>";
@$optkontan.="<option value='KONTAN'>Kontanan</option>";

OPEN_BOX('','<span class=judul>'.getMenu('kebun_premimandoran').'</span><br>');
$arr="##prd##unit##afd##kontanan##tglmulai##tahap";
$frm[0].= "<fieldset style=float:left;height:140px><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=prd  style='width:140px;'>".$optprd."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tahap']."</td>
		<td>:</td>
		<td colspan=3><select id=tahap  style='width:140px;'>".$opttahap."</select>
		</td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unit  style='width:140px;'>".$optunit."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=afd  style='width:140px;'>".$optafd."</select></td>
    </tr> 
	<tr style=display:none>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td><select id=kontanan onchange=gettglmdr(this.value,'tanggalmandor') style='width:140px;'>".$optkontan."</select></td>
    </tr> 
	
	<tr id=tanggalmandor style=display:none>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' style='width:135px;' class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false'>
		
		</td>
    </tr> 
	
	";
$frm[0].= "<tr>
		<td colspan=3 align=right>
		<button onclick=zPreview('kebun_slave_premimandoran','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'kebun_slave_premimandoran.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=batalmandor() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[0].= "<fieldset  style=height:140px><legend><b>Info</b></legend>
<table>
	<tr>
		<td>Urutan melakukan proses premi Mandor Panen adalah :<br>
			   1. Lakukan penginputan dan Posting hasil panen karyawan pada menu Kegiatan Panen<br>
			   2. Lakukan Proses premi Mandor Panen<br>
			   3. Lakukan Proses premi Mandor 1<br>
			   4. Unit Kerja dan Divisi adalah Lokasi Tugas Karyawan di data karyawan bukan lokasi dia bekerja<br>
			   5. Jika ada pekerjaan Asistensi contoh Mandor Afd 01 bekerja di Afd 02, maka Proses dilakukan di Afd 01<br>
			   6. Ketentuan perhitungan mengacu ke <a href=fileupload/simulasibiayapanen.xlsx download>Simulasi Biaya Panen (Point B)</a>
			   </td>
		
	</tr>
</table>
</fieldset>";

$frm[0].= "
<hr><fieldset  style=min-height:350px ><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";



//mandor 1
$arrsatu="##prdsatu##unitsatu##afdsatu##kontanansatu##tglmulaisatu##tahapsatu";
$frm[1].= "<fieldset style=float:left;height:140px><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=prdsatu  style='width:140px;'>".$optprd."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tahap']."</td>
		<td>:</td>
		<td colspan=3><select id=tahapsatu  style='width:140px;'>".$opttahap."</select>
		</td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unitsatu  style='width:140px;'>".$optunit."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=afdsatu  style='width:140px;'>".$optafd."</select></td>
    </tr>
	<tr style=display:none>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td><select id=kontanansatu onchange=gettglmdr(this.value,'tanggalmandorsatu') style='width:140px;'>".$optkontan."</select></td>
    </tr> 
	
	<tr id=tanggalmandorsatu style=display:none>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' style='width:135px;' class='myinputtext' id='tglmulaisatu' onmousemove='setCalendar(this.id)' onkeypress='return false'>
		
		</td>
	</tr> 	
	";


$frm[1].= "<tr>
		<td colspan=3 align=right>
		<button onclick=zPreview('kebun_slave_premimandoransatu','".$arrsatu."','printContainersatu') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'kebun_slave_premimandoransatu.php','".$arrsatu."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=batalmandor1() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";


$frm[1].= "<fieldset style=height:140px><legend><b>Info</b></legend>
<table>
	<tr>
		<td>Urutan melakukan proses premi Mandor Panen I adalah :<br>
			   1. Lakukan penginputan dan Posting hasil panen karyawan pada menu Kegiatan Panen<br>
			   2. Lakukan Proses premi Mandor Panen<br>
			   3. Lakukan Proses premi Mandor 1<br>
			   4. Unit Kerja dan Divisi adalah Lokasi Tugas Karyawan di data karyawan bukan lokasi dia bekerja<br>
			   5. Jika ada pekerjaan Asistensi contoh Mandor 1 Afd 01 bekerja di Afd 02, maka Proses dilakukan di Afd 01</td><br>
			   6. Ketentuan perhitungan mengacu ke <a href=fileupload/simulasibiayapanen.xlsx download>Simulasi Biaya Panen (Point B)</a>
		
	</tr>
</table>
</fieldset>";

$frm[1].= "
<hr><fieldset  style=min-height:350px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainersatu'>
</div></fieldset>";



##kerani
$arrkerani="##prdkerani##unitkerani##afdkerani##kontanankerani##tglmulaikerani##tahapkerani";
$frm[2].= "<fieldset style=float:left;height:140px><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=prdkerani  style='width:140px;'>".$optprd."</select>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tahap']."</td>
		<td>:</td>
		<td colspan=3><select id=tahapkerani  style='width:140px;'>".$opttahap."</select>
		</td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unitkerani  style='width:140px;'>".$optunit."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=afdkerani  style='width:140px;'>".$optafd."</select></td>
    </tr>
	
	<tr style=display:none>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td><select id=kontanankerani onchange=gettglmdr(this.value,'tanggalkerani') style='width:140px;'>".$optkontan."</select></td>
    </tr> 
	
	<tr id=tanggalkerani style=display:none>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' style='width:135px;' class='myinputtext' id='tglmulaikerani' onmousemove='setCalendar(this.id)' onkeypress='return false'>
		
		</td>
	</tr> 	
	";
$frm[2].="<tr>
		<td colspan=3 align=right>
		<button onclick=zPreview('kebun_slave_premimandorankerani','".$arrkerani."','printContainerkerani') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'kebun_slave_premimandorankerani.php','".$arrkerani."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=batalkerani() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[2].= "<fieldset style=height:140px><legend><b>Info</b></legend>
<table>
	<tr>
		<td>
			   1. Lakukan penginputan dan Posting hasil panen karyawan pada menu Kegiatan Panen<br>
			   2. Unit Kerja dan Divisi adalah Lokasi Tugas Karyawan di data karyawan bukan lokasi dia bekerja<br>
			   3. Jika ada pekerjaan Asistensi contoh Mandor Afd 01 bekerja di Afd 02, maka Proses dilakukan di Afd 01<br>
			   4. Ketentuan perhitungan mengacu ke <a href=fileupload/simulasibiayapanen.xlsx download>Simulasi Biaya Panen (Point B)</a>
			
	   </td>
		
	</tr>
</table>
</fieldset>";	


$frm[2].="
<hr><fieldset  style=min-height:350px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainerkerani'>
</div></fieldset>";
##tutup kerani
$disabled='';
if($_SESSION['empl']['lokasitugas']!='ASRE'){
	$disabled=" disabled ";
}
$arrkeranitrk="##prdkeranitrk##unitkeranitrk##afdkeranitrk##tglmulaitrk##kontanantrk";
$frm[4].= "<fieldset style=float:left;><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select ".$disabled." id=prdkeranitrk  style='width:140px;'>".$optprd."</select>
	</tr>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select ".$disabled." id=unitkeranitrk  style='width:140px;'>".$optunit."</select></td>
    </tr>
    <tr hidden>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=afdkeranitrk  style='width:140px;'>".$optafd."</select></td>
    </tr> 
	<tr>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td><select ".$disabled." id=kontanantrk onchange=gettglmdr(this.value,'tanggaltrk') style='width:140px;'>".$optkontan."</select></td>
    </tr> 
	
	<tr id=tanggaltrk style=display:none>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' style='width:135px;' class='myinputtext' id='tglmulaitrk' onmousemove='setCalendar(this.id)' onkeypress='return false'>
		
		</td>
	</tr> 	
	";
$frm[4].="<tr>
		<td colspan=3 align=right>
		<button ".$disabled." onclick=zPreview('kebun_slave_premimandorankeranitrk','".$arrkeranitrk."','printContainerkeranitrk') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button ".$disabled." onclick=zExcel(event,'kebun_slave_premimandorankeranitrk.php','".$arrkeranitrk."') class=mybutton name=preview id=previewtrk>".$_SESSION['lang']['excel']."</button>
		<button ".$disabled." onclick=batalkeranitrk() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[4].= "<fieldset style=height:120px;display:none ><legend><b>Info</b></legend>
<table>
	<tr>
		<td>
			   1. Lakukan penginputan dan Posting hasil panen karyawan pada menu Kegiatan Panen<br>
			   2. Unit Kerja dan Divisi adalah Lokasi Tugas Karyawan di data karyawan bukan lokasi dia bekerja<br>
			   3. Jika ada pekerjaan Asistensi contoh Mandor Afd 01 bekerja di Afd 02, maka Proses dilakukan di Afd 01<br>
			   4. Ketentuan perhitungan mengacu ke <a href=fileupload/simulasibiayapanen.xlsx download>Simulasi Biaya Panen (Point B)</a>
			
	   </td>
		
	</tr>
</table>
</fieldset>";	


$frm[4].="<div style=clear:both></div>
<hr><fieldset  style=min-height:350px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainerkeranitrk'>
</div></fieldset>";

$arrlist="##prdlist##unitlist##jabatanlist##afdlist";
$frm[3].= "<fieldset style=float:left><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=prdlist  style='width:153px;'>".$optprdx."</select>
		</td>
	</tr>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unitlist  style='width:153px;'>".$optunitx."</select></td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=afdlist  style='width:153px;'>".$optafd2."</select></td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['jenispremi']."</td>
        <td>:</td>
        <td><select id=jabatanlist  style='width:153px;'>".$optjabatan."</select></td>
    </tr> ";
    

$frm[3].= "<tr>
		<td colspan=3 align=right>
		<button onclick=loaddata(0) class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'kebun_slave_premimandoranlist.php','".$arrlist."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset><div style=clear:both></div><hr>";

$frm[3].= "
<fieldset style=min-height:350px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainerlist'><script>loaddata()</script>
</div></fieldset>";


$hfrm[0]=$_SESSION['lang']['mandorpanen'];
$hfrm[1]=$_SESSION['lang']['nikmandor1'];
$hfrm[2]=$_SESSION['lang']['keranipanen'];
$hfrm[3]=$_SESSION['lang']['list'];
#$hfrm[4]="Mandor Traksi";

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,150,'100%');	

CLOSE_BOX();
echo close_body();




?>