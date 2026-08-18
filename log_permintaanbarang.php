<?
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
?>
<script language=javascript1.2 src='js/zTools.js'></script>
<script language=javascript1.2 src='js/log_permintaanbarang.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_permintaanbarang').'</span>');
$frm[0]='';
$frm[1]='';

$bln=$_SESSION['org']['period']['bulan'];
$thn=$_SESSION['org']['period']['tahun'];

echo "<fieldset><legend><b>Periode Akuntansi : ".$bln."-".$thn."</b></legend>";

if($_SESSION['empl']['subbagian']==''){
	$untuk = $_SESSION['empl']['lokasitugas'];
	$wh =" and kodeorganisasi like '".$untuk."%'";
} else {
	$untuk = $_SESSION['empl']['subbagian'];
	$wh =" and kodeorganisasi like '".$untuk."%'";
}

#kodeorganisasi untuk klinik harus berakhiran PK
if($_SESSION['empl']['tipelokasitugas']=='KANWIL' and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
   $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG'
   and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') order by namaorganisasi";
}else{
   $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
   where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') 
   and tipe = 'GUDANG'";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsloc="<option value=''></option>";
while($bar=$res->fetch()){
	$optsloc.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

echo"<fieldset style=float:left>
     <legend>".$_SESSION['lang']['gudang']."</legend>
	  ".$_SESSION['lang']['ptpemilikbarang']." : <input class=myinputtext disabled id=pemilikbarang style='width:50px;'>
	  ".$_SESSION['lang']['pilihgudang']." : <select id=gudang onchange=getPT(this.options[this.selectedIndex].value)>".$optsloc."</select>&nbsp;
	  ".$_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tanggal size=10 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" value='".date('d-m-Y')."'>
	   
	   <button onclick=setSloc('simpan') class=mybutton id=btnsloc>".$_SESSION['lang']['save']."</button>
	   <button onclick=setSloc('ganti') class=mybutton>".$_SESSION['lang']['cancel']."</button>	  
	 </fieldset><br><br><br><br>";
# ================== end gudang ====================

//=================Get kendaraan
$optionm="<option value=''></option>"; 
$str="select * from ".$dbname.".vhc_5master where kodeorg='".$untuk."' and status='1' order by kodetraksi,kodevhc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res->fetch()){
	$optionm.="<option value='".$bar1->kodevhc."'>".$bar1->kodetraksi." : ".$bar1->kodevhc."</option>";
}
## ================== option ====================
$optsubunit="<option value=''></option>";
$optterima="<option value=''></option>";
@$whereKary.= " and tipekaryawan in (0,2,3,4,6) and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tanggal."')";
	
$str = "select * from ".$dbname.".datakaryawan where 1=1 and lokasitugas='".$_SESSION['empl']['lokasitugas']."' ".$whereKary." order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optterima.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['subbagian']."</option>";
}
# ==== option subunit ====
# unit estate / pabrik
$str = "select * from ".$dbname.".organisasi where 1=1 ".$wh."  and length(kodeorganisasi)<=6 and tipe in('AFDELING','PABRIK','PT','HOLDING','TRAKSI','WORKSHOP','STATION','BIBITAN') order by kodeorganisasi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optsubunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
# project
$str = "select * from ".$dbname.".project where 1=1  and kodeorg='".$_SESSION['empl']['lokasitugas']."' and posting='0' order by kode asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optsubunit.="<option value='".$bar['kode']."'>Project : [".$bar['kode']."] ".$bar['nama']."</option>";
}

# kontraktor
$str = "select * from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where 1=1  and a.tipe='KONTRAKTOR' "; //exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optsubunit.="<option value='".$bar['supplierid']."'>Kontraktor : [".$bar['supplierid']."] ".$bar['namasupplier']."</option>";
}

## ==================== Form Header ===============
$frm[0].="<fieldset><legend>".$_SESSION['lang']['header']."</legend>";
$frm[0].="<table cellspacing=1 border=0>
     <tr>
		<td>".$_SESSION['lang']['momordok']."</td><td>:</td>
		<td><input type=text id=nodok size=25 style=width:200px disabled class=myinputtext></td>
		<td>&nbsp;</td>		
		<td>".$_SESSION['lang']['untukunit']."</td><td>:</td><td><input type=text style=width:50px id=untukunit class=myinputtext disabled value='".$untuk."'>
		</td>
	 </tr>
	 
	 <tr>
		<td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
        <td><select id=penerima style='width:205px;'>".$optterima."</select>
		<img id='penerima' onclick=z.elSearch('penerima',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
		<td>&nbsp;</td>
		<td>".$_SESSION['lang']['note']."</td><td>:</td><td><input type=text id=catatan class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 style='width:250px;' maxlength=80></td>
	 </tr>

	 </table>
    </fieldset>
    <fieldset>
	   <legend>".$_SESSION['lang']['detail']."</legend>
	   <div id=container>
	   <table class=sortable cellspacing=1 border=0 width=100%>
		   <thead>
		   <tr class=rowheader>
		    <td align=center style=\"width:75px;\">".$_SESSION['lang']['kodebarang']."</td>
			<td align=center style=\"width:175px;\">".$_SESSION['lang']['namabarang']."</td>
			<td align=center style=\"width:30px;\">".$_SESSION['lang']['satuan']."</td>
			<td align=center style=\"width:50px;\">".$_SESSION['lang']['jumlah']."</td>
			<td align=center style=\"width:120px;\">".$_SESSION['lang']['subunit']."</td>";
			if($_SESSION['empl']['tipelokasitugas']=='PABRIK'){
				$sub=$_SESSION['lang']['mesin'];
			}else{
				$sub=$_SESSION['lang']['blok'];
			}
$frm[0].="  <td align=center  style=\"width:120px;\">".$sub."</td>
			<td style=display:none>".$_SESSION['lang']['segment']."</td>
			<td align=center  style=\"width:120px;\">Kend / AB / Mesin</td>
			<td align=center  style=\"width:250px;\">".$_SESSION['lang']['kegiatan']."</td>
			<td align=center  style=\"width:50px;\">#</td>
			</tr>
		   </thead>
			   <tbody>
				   <tr class=rowcontent>
				    <td><input type=text style=\"width:95%;\" maxlength=10 id=kodebarang class=myinputtext onkeypress=\"return false;\" onclick=\"showWindowBarang('".$_SESSION['lang']['find']."',event);\"></td>
					<td><input type=text style=\"width:95%;\" maxlength=100 id=namabarang class=myinputtext readonly onclick=\"showWindowBarang('".$_SESSION['lang']['find']."',event);\"></td>
					<td><input type=text style=\"width:95%;\" maxlength=5 id=satuan class=myinputtext  onkeypress=\"return false;\" onclick=\"showWindowBarang('".$_SESSION['lang']['find']."',event);\"></td>
					<td><input type=text style=\"width:95%;\" maxlength=10 id=qty  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"></td>
					<td><select id=subunit style=\"width:88%;\" onchange=loadBlock(this.options[this.selectedIndex].value)>".$optsubunit."</select>
					<img id='subunit' onclick=z.elSearch('subunit',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					
					<td><input type=hidden id=olbBlok value='' />
					<select id=blok style=\"width:87%;\" onchange=getKegiatan(this.options[this.selectedIndex].value,'BLOK')></select>
					<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'></td>

					<td><select id=mesin style=\"width:87%;\" onchange=getKegiatan(this.options[this.selectedIndex].value,'TRAKSI')>".$optionm."</select>
					<img id='mesin' onclick=z.elSearch('mesin',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					<td><select id=kegiatan style=\"width:92%;\"><option value=''></option></select> 
					<img id='kegiatan' onclick=z.elSearch('kegiatan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
					<td align=center>
					<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"saveItemBast()\" src='images/save.png'/>
					<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail()\" src='images/clear.png'/>
					<img id='pic' onclick=getpic() class='resicon' src='images/orgicon.png' ></td>
		 		   </tr>			   
			   </tbody>
		   <tfoot>
			<tr><td colspan=9 align=right>
			   <input type=hidden value='insert' id=method>
			   <button onclick=bastBaru() class=mybutton>".$_SESSION['lang']['done']."</button>
			</td></tr>
		  </tfoot>
	   </table>
	   </div>
	 </fieldset>

    <fieldset>
	   <legend>".$_SESSION['lang']['datatersimpan']."</legend>
	   <table class=sortable cellspacing=1 border=0 width=100%>
		   <thead>
		   <tr class=rowheader>
		   <td align=center>No</td>
		    <td align=center>".$_SESSION['lang']['kodebarang']."</td>
			<td align=center>".$_SESSION['lang']['namabarang']."</td>
			<td align=center>".$_SESSION['lang']['satuan']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']."</td>
			<td align=center>".$_SESSION['lang']['namapt']."</td>
			<td align=center>".$_SESSION['lang']['untukunit']."</td>
			<td align=center>".$_SESSION['lang']['subunit']."</td>
			<td align=center>".$_SESSION['lang']['blok']."</td>
			<td align=center>Kend / AB / Mesin</td>
			<td align=center>".$_SESSION['lang']['kegiatan']."</td>
			<td align=center width=30px>Action</td>
 		   </tr>
		   </thead>
			   <tbody id=bastcontainer>			   
			   </tbody>
		   <tfoot>
		   </tfoot>
	   </table>
	 </fieldset>
	 	 
	 ";
	 
$frm[1].="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset style=float:left>
	  ".$_SESSION['lang']['notransaksi']." : 
	  <input type=text id=txtbabp size=25 class=myinputtext onkeyup=\"enterkey(event,loaddata);\" onkeypress=\"return tanpa_kutip(event);\" maxlength=12>
	  ".$_SESSION['lang']['tanggal']." : 
	  <input type=text class=myinputtext id=tanggalsrc size=10 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\">
	  <button class=mybutton onclick=loaddata()>".$_SESSION['lang']['find']."</button>
	  </fieldset><br><br><br>
	  <table class=sortable cellspacing=1 border=0 width=100%>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>".$_SESSION['lang']['sloc']."</td>
	  <td align=center>".$_SESSION['lang']['momordok']."</td>
	  <td align=center>".$_SESSION['lang']['tanggal']."</td>	  
	  <td align=center>".$_SESSION['lang']['untukunit']."</td>	  	 
	  <td align=center>".$_SESSION['lang']['namakaryawan']."</td>	  	 
	  <td align=center>".$_SESSION['lang']['catatan']."</td>	  	 
	  <td align=center>".$_SESSION['lang']['dibuat']."</td>
	  <td align=center>".$_SESSION['lang']['status']."</td>
	  <td align=center width=50px colspan=4>Action</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>	 
	 ";	 

$hfrm[0]=$_SESSION['lang']['formpermintaan'];
$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,'100%');

CLOSE_BOX();
close_body();
?>