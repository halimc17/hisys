<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/log_transaksi_pengeluaran.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/vhc_detailkmhm.js'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>

<?
include('master_mainMenu.php');

$_SESSION['pic'] = array();
# check if transaction period is normal
if(isTransactionPeriod()){
OPEN_BOX('','<span class=judul>'.getMenu('log_pakaibarang').'</span>');
$frm[0]='';
$frm[1]='';
echo "<fieldset><legend>";

echo" <b>".$_SESSION['lang']['periode'].": <span id=displayperiod>".tanggalnormal($_SESSION['org']['period']['start'])." - ".tanggalnormal($_SESSION['org']['period']['end'])."</span></b>";
echo"</legend>";

#kodeorganisasi untuk klinik harus berakhiran PK
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANG' and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') order by namaorganisasi";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL' and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANG' and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') order by namaorganisasi";// and kodeorganisasi not in ('SENE10', 'SKNE10', 'SOGE30') order by namaorganisasi";
}else{

	// $unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
	// $gudang_detailAkses=" and induk IN (".$unitDetailAkses.") ";

	// if(count($unitDetailAkses) > 0){
	// 	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$gudang_detailAkses." and tipe like 'GUDANG%' order by kodeorganisasi";
	// }else{
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') and tipe = 'GUDANG' order by namaorganisasi";
	// }


}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsloc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch()){
	$d=substr($bar->kodeorganisasi,0,4);
	if($d!=$n){	
		$optsloc.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optsloc.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	$n=$d;

	if($d!=$n){		
		$optsloc.="</optgroup>";
	}
}

echo"<fieldset style=float:left>
     <legend>
	 ".$_SESSION['lang']['daftargudang']."
     </legend>
	  ".$_SESSION['lang']['pilihgudang']." : <select class='select2' id=sloc onchange=getPT(this.options[this.selectedIndex].value)>".$optsloc."</select>
	   ".$_SESSION['lang']['ptpemilikbarang']." : <select class='select2' id=pemilikbarang style='width:200px;'>
	   <option value=''>".$_SESSION['lang']['pilihdata']."</option>
	   </select>
	   <button onclick=setSloc('simpan') class=mybutton id=btnsloc>".$_SESSION['lang']['save']."</button>
	   <button onclick=setSloc('ganti') class=mybutton>".$_SESSION['lang']['ganti']."</button>	  
	 </fieldset>";
	
echo"<div style=clear:both></div>";
//==================masukkan variable periode gudang
foreach($_SESSION['gudang'] as $key=>$val){
	echo"<input type=hidden id='".$key."_start' value='".$_SESSION['gudang'][$key]['start']."'>
	     <input type=hidden id='".$key."_end' value='".$_SESSION['gudang'][$key]['end']."'>
		";
}	 

$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$optlokasitujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $optlokasitujuan.=ambilUnitPembebananBarang('',$_SESSION['empl']['lokasitugas']);
$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and induk!='' order by induk, kodeorganisasi";
$res=fetchdata($str);
$optlokasitujuan.="<optgroup label='UNIT SENDIRI'>";
$optlokasitujuan.="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']." - ".getNamaOrg($_SESSION['empl']['lokasitugas'])."</option>";		
$optlokasitujuan.="</optgroup>";
$n="";
foreach($res as $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$val['kodeorganisasi']."'");
	$d=$induk[$val['kodeorganisasi']];

	if($d!==$n && $n!==""){			
		$optlokasitujuan.="</optgroup>";
	}

	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optlokasitujuan.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optlokasitujuan.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";		
	$n=$d;
	if($d!=$n){			
		$optlokasitujuan.="</optgroup>";
	}
	
}
$optsubunit="<option value=''></option>";

//Get Departemen
$optdepartemen = "<option value=''></option>";
$str="select * from ".$dbname.".sdm_5departemen order by nama asc";
$res=fetchData($str);
foreach($res as $val){
	$optdepartemen.="<option value='".$val['kode']."'>".$val['nama']."</option>";
}

//get Kegiatan
$optKegiatan="<option value=''></option>";
$optKary="<option value=''></option>";
$strf="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where status = '1' order by kelompok,namakegiatan";
$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
$resf->setFetchMode(PDO::FETCH_OBJ);
while($barf=$resf->fetch()){
	$optKegiatan.="<option value='".$barf->kodekegiatan."'>[".$barf->kelompok."] -  ".$barf->namakegiatan."</option>";
}

$str="select karyawanid, namakaryawan, nik from ".$dbname.".datakaryawan where tanggalkeluar='0000-00-00' and statuskaryawan != 'Keluar' order by namakaryawan";
$resf=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$resf->setFetchMode(PDO::FETCH_OBJ);
while($barf=$resf->fetch()){
	$optKary.="<option value='".$barf->karyawanid."'>".$barf->nik." - ".$barf->namakaryawan."</option>";
}		

##TAMBAH DEPARTEMEN 
// $sdept="select * from ".$dbname.".sdm_5departemen where aktif ='1' order by nama asc";
$optdept="<option value=''>&nbsp;</option>";
// $res=$owlPDO->query($sdept) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($rdept=$res->fetch()){
// 	$optdept.="<option value=".$rdept['kode'].">".$rdept['kode']." - ".$rdept['nama']."</option>";

// }

//=================Get kendaraan
   $optionm="<option value=''></option>"; 
	$str="select * from ".$dbname.".vhc_5master where status='1' order by kodetraksi,kodevhc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res->fetch()){
		$kodetraksi = ($bar1->kodetraksi==""?"":$bar1->kodetraksi." : ");
		$detailvhc = $bar1->detailvhc." ".$bar1->nopol;
		$optionm.="<option value='".$bar1->kodevhc."'>".$kodetraksi."".$bar1->kodevhc." - ".$detailvhc."</option>";
	}
//========================================

$defaultSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',
						 "kodesegment='".$defaultSegment."'");


// status blok
$optstatusblok="<option value=''></option>"; 
$str_s="select statusblok from ".$dbname.".setup_blok group by statusblok ";
$res_s=$owlPDO->query($str_s) or die(print " Gagal: ".PDOException::getMessage());
$res_s->setFetchMode(PDO::FETCH_OBJ);
while($bar_s=$res_s->fetch()){
	$optstatusblok.="<option value='".$bar_s->statusblok."'>".$bar_s->statusblok."</option>";
}




//===================================
$frm[0].="<fieldset><legend>".$_SESSION['lang']['header']."</legend>";

$frm[0].="<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['momordok']."</td>
		<td>:</td>
		<td>
			<input type=text id=nodok size=25 style=width:200px disabled class=myinputtext>
		</td>
	
		<td class='bintang'>".$_SESSION['lang']['untukunit']."</td>
		<td>:</td>
		<td>
			<select id=untukunit class='select2' onchange=loadSubunit(this.options[this.selectedIndex].value,'0','0') style='width:205px;'>".$optlokasitujuan."</select>
			<input type='hidden' id='tipeorg'><input type=hidden value='insert' id=method>
		</td>
	</tr>
	 
	<tr>
		<td style='opacity:0'>No. Request</td>
		<td style='opacity:0'>:</td>
		<td style='opacity:0'>
			<input type=text id=norequest size=25 style=width:200px disabled class=myinputtext>
			<!--<img id='imgnorequest' onclick=searchrequest(event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;margin-right:5px'>-->
		</td>
		
		<td class='bintang'>".$_SESSION['lang']['penerima']."</td>
		<td>:</td>
		<td>
			<select  class='select2' id=penerima style='width:205px;'>".$optKary."</select>
			<!--<img id='penerima' onclick=z.elSearch('penerima',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>-->
		</td>
		
		<td style='display:none'>".$_SESSION['lang']['departemen']."</td>
		<td style='display:none'>:</td>
		<td style='display:none'>
			<select id=departemen onchange=blankdepartmen(this.options[this.selectedIndex].value) style='width:205px;'>".$optdepartemen."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" value='".date('d-m-Y')."' readonly>
		</td>
		
		<td>".$_SESSION['lang']['note']."</td>
		<td>:</td>
		<td style='padding-right:7px'>
			<input type=text id=catatan class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 style='width:202px;' maxlength=80>
		</td>
		
		<td style='display:none'>".$_SESSION['lang']['namakaryawan']."</td>
		<td style='display:none'>:</td>
		<td style='display:none'>
			<select id=karyawanid style='width:205px;' onchange=blankkaryawanid(this.options[this.selectedIndex].value)></select><img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
	</tr>
	<tr>
		<!--<td>".$_SESSION['lang']['subunit']."</td><td>:</td><td><select style='width:255px;' id=subunit onchange=loadBlock(this.options[this.selectedIndex].value)></select></td>-->
		
		<!--<td>".$_SESSION['lang']['penerima']."</td><td>:</td><td><input type=text id=penerima class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=45 size=35></td>-->
		
		<!--<td>".$_SESSION['lang']['note']."</td><td>:</td><td><input type=text id=catatan class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 style='width:250px;' maxlength=80></td>-->
	</tr>

	 </table>
    </fieldset>
    <fieldset>
	   <legend>".$_SESSION['lang']['detail']."</legend>
	   <div id=container>
	   <table class=sortable cellspacing=1 border=0>
		   <thead>
		   <tr class=rowheader style=height:25px>
		    <th class='bintang' align=center>Kode</th>
			<th class='bintang' align=center>".$_SESSION['lang']['namabarang']."</th>
			<th class='bintang' align=center>".$_SESSION['lang']['satuan']."</th>
			<th class='bintang' align=center>".$_SESSION['lang']['jumlah']."</th>
			<th class='bintang' align=center colspan=2>".$_SESSION['lang']['subunit']."</th>
			<th align=center colspan=1>Status Blok</th>
			<th class='bintang' align=center colspan=2>Blok / Mesin PKS</th>
			<th style=display:none>".$_SESSION['lang']['segment']."</th>
			<th align=center colspan=2>Kend / AB</th>
			<th align=center>KM / HM</th>
			<th class='bintang' align=center colspan=2>".$_SESSION['lang']['kegiatan']."</th>
			<th align=center colspan=2 >".$_SESSION['lang']['departemen']."</th>
			<!--<th align=center colspan=1 ><button onclick=\"getpic2(event);\" class=mybutton>PIC</button></th>-->
			<th align=center colspan=1 ></th>
			<th align=center>PIC/Dept</th>
			</tr>
		   </thead>
			   <tbody>
				   <tr class=rowcontent>
				    <td valign=top><input type=text style=width:56px maxlength=10 id=kodebarang class=myinputtext onkeypress=\"return false;\" onclick=\"showWindowBarang('".$_SESSION['lang']['namabarang']."',event);\"></td>
					<td valign=top><input type=text size=25 maxlength=100 id=namabarang class=myinputtext readonly onclick=\"showWindowBarang('".$_SESSION['lang']['namabarang']."',event);\"></td>
					<td valign=top><input type=text size=4 maxlength=5 id=satuan class=myinputtext  onkeypress=\"return false;\" onclick=\"showWindowBarang('".$_SESSION['lang']['namabarang']."',event);\"></td>
					<td valign=top><input type=text size=6 maxlength=10 id=qty class=myinputtextnumber onkeypress=\"return angka_doang(event);\"></td>
					<td valign=top colspan=2><select class=select2 id=subunit style='width:110px;' onchange=loadBlock(this.options[this.selectedIndex].value) onfocusout=getvhc()></select>
					</td><td style=display:none valign=top align=center width=15px>
						<img id='subunit' onclick=z.elSearch('subunit',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					<td colspan=1><select class=select2 id=statusblok name=statusblok style=width:110px; onchange=loadBlock(this.options[this.selectedIndex].value)>".$optstatusblok." </td> 
					<td valign=top colspan=2><input type=hidden id=olbBlok value='' /><select class=select2 id=blok style='width:110px;' onchange=getKegiatan(this.options[this.selectedIndex].value,'BLOK')></select>
					</td><td valign=top style=display:none align=center width=15px>
					<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					<td valign=top style=display:none>".makeElement('segment','select','',array('style'=>'width:100px'),$optSegment)."</td>
					<input type=hidden id=oldmesin value=''>
					<td valign=top colspan=2><select class=select2 id=mesin style='width:110px;' onchange=getKegiatan(this.options[this.selectedIndex].value,'TRAKSI')>".$optionm."</select>
					</td><td valign=top align=center width=15px style=display:none>
					<img id='mesin2' onclick=z.elSearch('mesin',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					<td valign=top><input style=width:75px disabled type=text id=kmhm class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td valign=top colspan=2><select  class=select2 id=kegiatan onchange=getdept(this.value) style='width:250px;'>".$optKegiatan."</select>
					</td>
					<td valign=top align=center width=15px style=display:none>
						<img id='kegiatan' onclick=z.elSearch('kegiatan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;vertical-align:baseline'>
					</td>
					 
					<td colspan=2><select class=select2 id=dept name=dept style=width:232px;>".$optdept." </td> 
					<td>
						<table id='tblpic2'>
						</table>
						<div style='text-align:center;width:100%'>
						<!--<img id='pic2' onclick=\"getpic2(event);\" class='resicon' src='images/user_add.png' style='position:relative;padding-right:2px;' title='Add PIC/Department'>-->
						</div>
					</td>
					<td>
						<table id='tblpic'>
						</table>
						<div style='text-align:center;width:100%'>
						<!--<img id='pic' onclick=\"getpic(event);\" class='resicon' src='images/user_add.png' style='position:relative;padding-right:2px;' title='Add PIC/Department'>-->
						<img id='pic' onclick=\"getpic2(event);\" class='resicon' src='images/user_add.png' style='position:relative;padding-right:2px;' title='Add PIC/Department'>
						<img id='showupload' onclick=\"showupload(event);\" class='resicon' src='images/upload-2-xxl.png' style='position:relative;padding-right:2px;' title='Upload File'>
						</div>
					</td>
		 		   </tr>
					<tr>
						<td colspan=16 style='text-align:right'>
							<button onclick=saveItemBast() class=mybutton>".$_SESSION['lang']['save']."</button>
							<button onclick=nextItem2() class=mybutton>".$_SESSION['lang']['cancel']."</button>	
							<button onclick=bastBaru() class=mybutton>".$_SESSION['lang']['done']."</button>	 
						</td>
					</tr>
			   </tbody>
		   <tfoot>
		   </tfoot>
	   </table>
	   </div>
	 </fieldset>

    <fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	   <table class=sortable cellspacing=1 border=0 cellpadding=5>
		   <thead>
		   <tr class=rowheader>
		   <th align=center>No</th>
		    <th align=center>".$_SESSION['lang']['kode']."</th>
			<th align=center>".$_SESSION['lang']['namabarang']."</th>
			<th align=center>".$_SESSION['lang']['satuan']."</th>
			<th align=center>".$_SESSION['lang']['jumlah']."</th>
			<th align=center hidden>".$_SESSION['lang']['pt']."</th>
			<th align=center>".$_SESSION['lang']['untukunit']."</th>
			<th align=center>".$_SESSION['lang']['kodeblok']."</th>
			<th align=center style=display:none>".$_SESSION['lang']['segment']."</th>
			<th align=center>".$_SESSION['lang']['kodenopol']."</th>
			<th align=center>KM / HM</th>
			<th align=center>".$_SESSION['lang']['kegiatan']."</th>
			<th align=center>".$_SESSION['lang']['departemen']."</th>
			<th align=center>PIC/Dept</th>
			<th align=center>Action</th>
 		   </tr>
		   </thead>
			   <tbody id=bastcontainer>			   
			   </tbody>
		   <tfoot>
		   </tfoot>
	   </table>
	 </fieldset>
	 	 
	 ";
	 
$frm[1].="
	  <fieldset style=float:left>
	  ".$_SESSION['lang']['cari_transaksi']." : 
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=21>
	  <button class=mybutton onclick=cariBast()>".$_SESSION['lang']['find']."</button>
	  </fieldset><div style=clear:both></div>
	  <table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%>
      <thead>
	  <tr class=rowheader>
	  <th align=center>No.</th>
	  <!--<th align=center>".$_SESSION['lang']['sloc']."</th>
	  <th align=center>".$_SESSION['lang']['tipe']."</th>-->
	  <th align=center>".$_SESSION['lang']['momordok']."</th>
	  <th align=center>".$_SESSION['lang']['tanggal']."</th>
	  <th align=center>".$_SESSION['lang']['pemilik']."</th>
	  <th align=center>".$_SESSION['lang']['untukunit']."</th>	  	 
	  <th align=center>".$_SESSION['lang']['catatan']."</th>	  	 
	  <th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
	  <th align=center>".$_SESSION['lang']['posted']."</th>
	  <th align=center>".$_SESSION['lang']['waktu']."</th>
	  <th align=center colspan=4>Action</th>
	  </tr>
	  </thead>
	   <tbody id=containerlist>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 
	 ";	 
//========================
$hfrm[0]=$_SESSION['lang']['pengeluaranbarang'];
$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,'');
//===============================================	 
}
else
{
	echo " Error: Transaction Period missing";
}
CLOSE_BOX();
close_body();
?>