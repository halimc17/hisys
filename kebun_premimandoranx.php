<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/kebun_premimandoranx.js?v=<?php echo time(); ?>'></script>
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

foreach(getOrgDetail(23) as $key => $val){
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$nminduk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optunitx.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optunit.="<option value=".$key.">".$val."</option>";
	$optunitx.="<option value=".$key.">".$val."</option>";
	$n=$d;
	if($d!=$n){
		$optunit.="</optgroup>";
		$optunitx.="</optgroup>";
	}
}

foreach(getOrgDetail(19) as $key => $val){
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$nminduk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optafd.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optafd2.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optafd.="<option value=".$key.">".$val."</option>";
	$optafd2.="<option value=".$key.">".$val."</option>";
	$n=$d;
	if($d!=$n){
		$optafd.="</optgroup>";
		$optafd2.="</optgroup>";
	}
}

$arrJab=getEnum($dbname,'kebun_premikemandoran','jabatan');


$optjabatan="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct(jabatan) as jabatan from ".$dbname.".kebun_premikemandoran";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optjabatan.="<option value=".$bar['jabatan'].">".$arrJab[$bar['jabatan']]."</option>";
}

$str="select distinct(substr(tanggal,1,7)) as periode from ".$dbname.".kebun_aktifitas order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optprd.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	$optprdx.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	$optprd2.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}

$opttahap="<option value='1'>Pertama</option>";
$opttahap.="<option value='2'>Kedua</option>";

$opttahapx="<option value=''>".$_SESSION['lang']['all']."</option>";
$opttahapx.="<option value='1'>Pertama</option>";
$opttahapx.="<option value='2'>Kedua</option>";

$optkontan1="<option value=''>".$_SESSION['lang']['all']."</option>";
@$optkontan1.="<option value='KERJA'>Kerja</option>";
@$optkontan1.="<option value='KONTAN'>Kontanan</option>";


@$optkontan.="<option value='KERJA'>Kerja</option>";
@$optkontan.="<option value='KONTAN'>Kontanan</option>";

OPEN_BOX('','<span class=judul>'.getMenu('kebun_premimandoranx').'</span><br>');
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
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td><select class=select2 id=prdlist onchange='loaddata(0)' style='width:153px;'>".$optprdx."</select>
			</td>
		
			<td>".$_SESSION['lang']['unitkerja']."</td>
			<td>:</td>
			<td><select class=select2 id=unitlist onchange='loaddata(0)' style='width:153px;'>".$optunitx."</select></td>
		
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td><select class=select2 id=afdlist onchange='loaddata(0)' style='width:153px;'>".$optafd2."</select></td>
		
		</tr>
		<tr>
			 <td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
			   <td><input type=text class=myinputtext id=namakarylist nkeypress=\"return_tanpa_kutip(event);\" style=\"width:150px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>
			   
			<td>".$_SESSION['lang']['jenispremi']."</td>
			<td>:</td>
			<td><select class=select2 id=jabatanlist onchange='loaddata(0)' style='width:153px;'>".$optjabatan."</select></td>
			
			<td>".$_SESSION['lang']['tahap']."</td>
			<td>:</td>
			<td><select class=select2 id=tahaplist onchange='loaddata(0)' style='width:153px;'>".$opttahapx."</select></td>
			
		</tr>
		
		
		";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
		<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button></td></td></tr></table>";

echo"</fieldset></table><div style=clear:both></div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();
echo"
	<div class='table-scroll'>
	<table class='sortable' cellspacing=1 cellpadding=5 border=0 width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center>".$_SESSION['lang']['divisi']."</th>
				<th align=center>".$_SESSION['lang']['periode']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<!--<th align=center>Kerja / Kontanan</th>-->
				<th align=center>".$_SESSION['lang']['nik2']."</th>
				<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center>".$_SESSION['lang']['jabatan']." ".$_SESSION['lang']['karyawan']."</th>
				<th align=center>".$_SESSION['lang']['jenispremi']."</th>
				<!--<th align=center>Total Premi<br>(Kary / Mdr)</th>-->
				<th align=center>Premi Kotor</th>
				<th align=center>Denda</th>
				<th align=center>Premi Bersih</th>
				<th align=center colspan=3>Action</th>
			</tr>
		</thead>
		 <tbody id=printContainerlist> 
			<script>loaddata(0)</script>
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
</div>"; 
CLOSE_BOX();
echo"</div>";

echo"<div id=detail style=display:none>";
OPEN_BOX();

$arrJab=getEnum($dbname,'kebun_premikemandoran','jabatan');
$optjab="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrJab as $barjab){
	@$optjab.="<option value=".$barjab.">".$barjab."</option>";
}

echo"<fieldset style=float:left;><legend><b>Form</b></legend>
<table border=0>
    <tr>
        <td>".$_SESSION['lang']['kodeorg']."</td>
        <td>:</td>
        <td colspan=3><select class=select2 id=unit onchange=getdivisi(); style='width:170px;'>".$optunit."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td colspan=3><select class=select2 id=afd  style='width:170px;'>".$optafd."</select></td>
    </tr> 
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=prd onchange=gettgl(this.id); style='width:170px;'>".$optprd."</select>
		</td>
	</tr>
	<tr hidden>
		<td>".$_SESSION['lang']['tahap']."</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=tahap onchange=gettgl(this.id);  style='width:170px;'>".$opttahap."</select>
		</td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' disabled style='width:70px;' class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false' readonly></td>
        <td>s/d</td>
		<td><input type='text' disabled style='width:70px;' class='myinputtext' id='tglakhir' onmousemove='setCalendar(this.id)' onkeypress='return false' readonly></td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['jabatan']."</td>
        <td>:</td>
        <td colspan=3><select class=select2 id=jabatan style='width:170px;'>".$optjab."</select></td>
    </tr> 	
	<tr style=display:none;>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td colspan=3><select class=select2 id=kontanan style='width:170px;'>".$optkontan."</select></td>
    </tr> 
	
	";
	
$arr="##prd##unit##afd##kontanan##tglmulai##tahap##tglakhir##jabatan";
echo"<tr>
        <td colspan=2></td>
		<td colspan=3>
		<button onclick=zPreview('kebun_slave_premimandoranx','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



echo"<table>
	<tr>
		<td>Info</td>
	</tr>
	<tr>
		<td>Urutan melakukan proses premi Mandor Panen adalah :<br>
			   1. Lakukan penginputan dan Posting hasil panen karyawan pada menu Kegiatan Panen<br>
			   2. Lakukan Proses premi Mandor Panen<br>
			   3. Lakukan Proses premi Mandor 1<br>
			   4. Unit Kerja dan Divisi adalah Lokasi Tugas Karyawan di data karyawan bukan lokasi dia bekerja<br>
			   5. Jika ada pekerjaan Asistensi contoh Mandor Afd 01 bekerja di Afd 02, maka Proses dilakukan di Afd 01
	   </td>
	</tr>
</table>";

CLOSE_BOX();
OPEN_BOX();


echo"<div id='printContainer' style=min-height:350px></div>";

CLOSE_BOX();
echo"</div>";
echo close_body();

?>