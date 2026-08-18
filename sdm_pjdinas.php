<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_pjdinas.js?v=1.0'></script>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['perjalanandinas']).'</span>');

//ambil karyawan permanen
$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and
	  tipekaryawan in ('0','7','8') and bagian = 'HCGA' and
	  karyawanid <>".$_SESSION['standard']['userid']. " order by namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optKar = "";
$optKar2="<option value=''></option>";
while($bar=$res->fetch())
{
	$optKar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
	$optKar2.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
}	  


$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and
	  tipekaryawan in ('0','7','8')  and
	  karyawanid <> ".$_SESSION['standard']['userid']. " order by namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optKarAtas = "";
$optKarAtas2="<option value=''></option>";
while($bar=$res->fetch())
{
	$optKarAtas.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
	$optKarAtas2.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
}		

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
      where length(kodeorganisasi)=4 order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optOrg="<option value=''></option>";
$optPemberiTgs="";
while($bar=$res->fetch())
{
	$optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	if($bar->kodeorganisasi==$_SESSION['empl']['lokasitugas']){
		$optPemberiTgs.="<option value='".$bar->kodeorganisasi."' selected>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	}
}

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optpt.="<option value='umum'>".$_SESSION['lang']['umum']."</option>";
while($bar=$res->fetch())
{
	$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

$optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//persetujuan1
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PJDINAS' and level=1 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

//persetujuan2
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PJDINAS' and level=2 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper2.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

//persetujuan3
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PJDINAS' and level=3 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper3.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

//persetujuan4
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PJDINAS' and level=4 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper4.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

$optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

$lokasitugas = $_SESSION['empl']['lokasitugas'];

$optkaryawan='';
//nama karyawan
$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$optkaryawan="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";

//kode hrd
$sC="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='PJDHRD'";
$tC=$owlPDO->query($sC) or die(print " Gagal: ".PDOException::getMessage());
$tC->setFetchMode(PDO::FETCH_ASSOC);
$rC = $tC->fetch();
$nilai=$rC['nilai'];

if ($nilai==$_SESSION['empl']['bagian']){
	//nama karyawan BOD
	$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan='7'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar=$res->fetch()){
	$optkaryawan.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";
	}
}

#Enum jenis
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrtipe=getEnum($dbname,'sdm_pjdinasht','jenis');
foreach($arrtipe as $kei=>$fal)
{

	if ($fal=='PD') {
		$capt=$_SESSION['lang']['perdin'];
	}
	if ($fal=='ST') {
		$capt=$_SESSION['lang']['surattugas'];
	}

    $optjenis.="<option value='".$kei."'>".$capt."</option>";
}

$frm[0]="
     <fieldset  style=float:left>
	  <legend>".$_SESSION['lang']['header']."</legend>
	  <fieldset>
	  <legend>".$_SESSION['lang']['karyawan']."</legend>
     <table border=0>
	 <tr>
	   <input type=hidden value='insert' id=method>
	   <input type=hidden value='' id=notransaksi>
	    <td width=70px>".$_SESSION['lang']['nama']."</td><td>:</td>
		<td><select  style=width:150px id='karyawanid' onchange='getpersetujuan()'>".$optkaryawan."</select></td>
		 <td width=70px>".$_SESSION['lang']['unit']."</td><td>:</td>
		<td><select  style=width:150px id='unit' ></select></td>
	 </tr>
	 <tr hidden>
	    <td>".$_SESSION['lang']['kodeorg']."</td><td>:</td>
		<td><select  style=width:68px id='kodeorg'><option value='".$lokasitugas."'>".$lokasitugas."</option></select></td>
	 </tr>	 
	 <tr>
	    <td>".$_SESSION['lang']['tanggaldinas']."</td><td>:</td>
		<td><input type=text id=tanggalperjalanan class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) style='width:148px'></td>
		<td>".$_SESSION['lang']['tanggalkembali']."</td><td>:</td>
		<td>
			<input type=text id=tanggalkembali class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) style='width:148px'>
		</td>
	 </tr>	
	 <tr> 
	     <td style=width:50px>PT ".$_SESSION['lang']['tujuan']."</td>
		 <td>:</td>
	     <td><select id='tujuan2' style='width:150px' onchange='getunit()'>".$optpt."</select></td>
	     <td >".$_SESSION['lang']['jenis']."</td>
		 <td>:</td>
	     <td><select id='jenis' style='width:150px' onchange='getrincian()'>".$optjenis."</select></td>
	 </tr>
	 <tr style='display:none;'>
	    <td>".$_SESSION['lang']['transportasi']."/".$_SESSION['lang']['akomodasi']."</td><td>:</td>
		<td>
		     <input type=checkbox id=pesawat> ".$_SESSION['lang']['pesawatudara']."
			 <input type=checkbox id=darat> ".$_SESSION['lang']['transportasidarat']."
			 <input type=checkbox id=laut> ".$_SESSION['lang']['transportasiair']."
			 <input type=checkbox id=mess> ".$_SESSION['lang']['mess']."
			 <input type=checkbox id=hotel> ".$_SESSION['lang']['hotel']."
			 <br>
			  <input type=checkbox id=kendaraandinas> ".$_SESSION['lang']['kendaraandinas']."
			 <input type=checkbox id=kendaraanpribadi> ".$_SESSION['lang']['kendaraanpribadi']."
			 <input type=checkbox id=kendaraanumum> ".$_SESSION['lang']['kendaraanumum']."<br>
			 Lainnya <input type=text id=tempatlain class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50>
        </td>
	 </tr>		
	 <tr>
	   <td style='display:none;'>
	      ".$_SESSION['lang']['uangmuka']."
	   </td>
	   <td style='display:none;'>
	     <input type=text class=myinputtextnumber onblur=change_number(this) id=uangmuka value='0' onkeypress=\"return angka_doang(event);\" size=15 maxlength=15>
	   </td>
	 </tr>
	  <tr hidden>
	   <td>
	      ".$_SESSION['lang']['pemberitugas']."
	   </td><td>:</td>
	   <td>
	     <select id='tujuan1' style='width:245px' disabled='true'>".$optPemberiTgs."</select>
		 <input type=hidden id=tugas1 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=254>
	   </td>
	 </tr>
	 </table>
	 </fieldset>";
	$captiontujuan = array(
		'dari' 				=> $_SESSION['lang']['dari'],
		'tujuan' 			=> $_SESSION['lang']['tujuan'],
		'waktu' 			=> $_SESSION['lang']['waktu'],
		'transportasi' 		=> $_SESSION['lang']['transportasi'],
		'rencanakegiatan'	=> $_SESSION['lang']['rencanakegiatan'],
		'tanggal' 			=> $_SESSION['lang']['tanggal'],
		'delete'			=> $_SESSION['lang']['delete']
	);
	$cap_json = json_encode($captiontujuan);


	// if($_SESSION['empl']['tipekaryawan']==7){
	// 	$display="style='display:none'";
	// }

	// if($_SESSION['empl']['tipekaryawan']==0){
	// 	$atasan="<tr id=atasan>
	// 				<td>Atasan</td> 
	// 				<td>:</td>
	// 				<td><select id=persetujuan1 style='width:150px'>".$optper1."</select></td>
	// 			</tr>";
	// 	$display="";
	// }



$frm[0].="
	<script> var cap_json = $cap_json; </script>
	<fieldset id=rutetujuanfield>
		<legend>".$_SESSION['lang']['tujuan']."</legend>
		<div>
			<table border=0 rute-num=0 width=100%>
				<tr> 
					 <td style=width:70px>".$_SESSION['lang']['dari']."</td><td width=1>:</td>
					 <td><input type=text name=rutedari[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=254 required=required for=".$_SESSION['lang']['dari']."></td>
					 <td>".$_SESSION['lang']['tujuan']."</td><td width=1>:</td>
					 <td><input type=text name=rutetujuan[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=254 required=required for=".$_SESSION['lang']['tujuan']."></td>		 		 		 
					<td style=width:70px>".$_SESSION['lang']['waktu']."</td><td width=1>:</td>
					 <td><input type=text name=rutewaktu[] class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this,'%d-%m-%Y_%H:%M:00') size=20 placeholder=d-m-y h:m required=required readonly=readonly for=".$_SESSION['lang']['waktu']."></td>
					 <td><a style=float:right;cursor:pointer; title=".$_SESSION['lang']['tambah']."  onclick=\"create_new_field('rutetujuan',cap_json);\"><img src=images/plus.png></a></td>
				</tr>
				<tr> 
					 <td>".$_SESSION['lang']['transportasi']."</td><td width=1>:</td>
					 <td colspan=8><input type=text name=rutetrans[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=width:100%;  maxlength=254 required=required for=".$_SESSION['lang']['transportasi']."></td>		 		 		 
				</tr>
			</table>
		</div>
		<div id=rutetujuan rute-count=0>	
		</div>
	</fieldset>	
	<fieldset id=rencanafield>
		<legend>".$_SESSION['lang']['rencanakegiatan']."</legend>
			<div>
			<table border=0 rute-num=0 width=100%>
				<tr> 
					 <td style=width:70px>".$_SESSION['lang']['tanggal']."</td><td width=1>:</td>
					 <td><input type=text name=rencanatanggal[] class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=15 placeholder=d-m-y required=required readonly=readonly for=".$_SESSION['lang']['tanggal']."></td>
					 <td colspan=3></td>
					 <td><a style=float:right;cursor:pointer; title=".$_SESSION['lang']['tambah']."  onclick=create_new_field('rencana',cap_json);><img src=images/plus.png></a></td>
				</tr>
				<tr> 
					 <td>".$_SESSION['lang']['rencanakegiatan']."</td><td width=1>:</td>
					 <td colspan=5><input type=text name=rencanakegiatan[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\"  maxlength=254 required=required style=width:100%; for=".$_SESSION['lang']['rencanakegiatan']."></td>		 		 		 
				</tr>
			</table>
		</div>
		<div id=rencana rute-count=0>	
		</div>
		
	</fieldset>	
	<fieldset style='display:none;'>
	<legend>".$_SESSION['lang']['tujuan']."</legend>
	<table border=0>
	<tr> 
	     <td style=width:50px>".$_SESSION['lang']['tujuan']." 1</td>
		 <td>:</td>
	     <td><select id='tujuan2' style='width:150px'>".$optOrg."</select></td>
		 <td>".$_SESSION['lang']['tugas']."</td>
		 <td>:</td>
		 <td><input type=text id=tugas2 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=254></td>		 		 		 
	</tr>
	<tr>	   
	     <td style=width:50px>".$_SESSION['lang']['tujuan']." 2</td>
		 <td>:</td>
	     <td><select id='tujuan3' style='width:150px'>".$optOrg."</select></td>
		 <td>".$_SESSION['lang']['tugas']."</td>
		 <td>:</td>
		 <td><input type=text id=tugas3 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=254></td>
	</tr>
	<tr>		 
	     <td style=width:50px>".$_SESSION['lang']['tujuan']." 3</td>
		 <td>:</td>
	     <td><input type=text style='width:145px' id=tujuanlain class=myinputtext onkeypress=\"return tanpa_kutip(event)\" maxlength=45></td>
		 <td>".$_SESSION['lang']['tugas']."</td>
		 <td>:</td>
		 <td><input type=text id=tugaslain class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=50 maxlength=254></td>		 		 		 
    </tr>
	</table>
	</fieldset>
	<div id=formpersetujuan ".$display.">
		<fieldset>
		  <legend>".$_SESSION['lang']['approve']."</legend>
		  <table>
		  	".$atasan."
		  	<tr>
				<td>Atasan</td> 
				<td>:</td>
				<td><select id=persetujuan1 style='width:150px'>".$optper1."</select></td>
			</tr>
		   	<tr>
			    <td>Division Head</td> 
			    <td> : </td>
				<td><select id=persetujuan2 style='width:150px'>".$optper2."</select></td>
			</tr>
			<tr>	
			    <td>HRD</td>
				<td> : </td>
				<td><select id=persetujuan3 style='width:150px'>".$optper3."</select></td>					 
			</tr>
			<tr style='display:none;'>	
			    <td>Direksi</td>
				<td> : </td>
				<td><select id=persetujuan4 style='width:150px'>".$optper4."</select></td>					 
			</tr>
		   </table>
		</fieldset>
	 </div>	 
	 <table>
	 <tr><td style=width:67px><td>
	   <button class=mybutton onclick=simpanPJD()>".$_SESSION['lang']['save']."</button>
	   <button class=mybutton onclick=clearForm()>".$_SESSION['lang']['new']."</button>
	 </table>
	 </fieldset>";
	 
//query kelompok by
$str="select * from ".$dbname.".sdm_5jenisbiayapjdinas";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optkel ="";
while($bar=$res->fetch()){
	$optkel.="<option id=jenisbiaya".$bar['id']." value=".$bar['id'].">".$bar['keterangan']."</option>";
}
	 
	 
	 
$frm[0].="
     <fieldset id='detailrincian'> <legend>".$_SESSION['lang']['detail']."</legend>
	 <fieldset>
	  <legend>".$_SESSION['lang']['form']."</legend>
	  <table>
		<tr>
			<td>".$_SESSION['lang']['namakelompok']."</td>
			<td>:</td>
			<td><select id=bykel style=\"width:168px;\">".$optkel."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['rupiah']."</td>
			<td>:</td>
			<td><input id=\"byrp\" name=\"byrp\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"  style=\"width:70px\" type=\"text\"></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td>:</td>
			<td><input type=text id=byket class=myinputtext placeholder='maksimal 50 karakter'  maxlength=50 onkeypress=\"return tanpa_kutip(event);\" size=40 style='width:250px;' maxlength=80></td>
		</tr>
		<tr><td colspan=3>
			<button class=mybutton onclick=bysimpan()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=byclear()>".$_SESSION['lang']['new']."</button>
			</td>
	   </tr>
	  </table>
		</fieldset>
		
		<fieldset>
		<legend>".$_SESSION['lang']['datatersimpan']."</legend>
		<div id=bycontainer> 
		<script>byloaddata();</script>
		</div>
		</fieldset>
		</fieldset>";
	 
		// <tr>
		// 	<td>".$_SESSION['lang']['tanggal']."</td>
		// 	<td>:</td>
		// 	<td>		
		// 		<input type=text class='myinputtext' id='bytgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' />
		// 		s/d
		// 		<input type=text class='myinputtext' id='bytgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' />
		// 	</td>
		// </tr>
		// <tr>
		// 	<td>".$_SESSION['lang']['frekuensi']."</td>
		// 	<td>:</td>
		// 	<td><input id=\"frekuensi\" name=\"frekuensi\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"  style=\"width:30px\" type=\"text\"></td>
		// </tr>
		// <tr>
		// 	<td>".$_SESSION['lang']['detail']."</td>
		// 	<td>:</td>
		// 	<td><input type=text id=bydet placeholder='maksimal 50 karakter' maxlength=50 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 style='width:250px;' maxlength=80></td>
		// </tr>




//
$frm[1]="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset><legend>".$_SESSION['lang']['find']."</legend>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=13>
	  <button class=mybutton onclick=cariPJD(0)>".$_SESSION['lang']['find']."</button>
	  </fieldset>
	  <fieldset><legend>".$_SESSION['lang']['list']."</legend>
	  <div  style='overflow:auto;max-width:900px'; >
	  <table class=sortable cellspacing=1 border=0 style=width:890px>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>".$_SESSION['lang']['notransaksi']."</td>
	  <td align=center>".$_SESSION['lang']['karyawan']."</td>
	  <td align=center>".$_SESSION['lang']['tanggalsurat']."</td>
	  <td align=center>".$_SESSION['lang']['approval_status']."</td>
	  <td align=center>".$_SESSION['lang']['action']."</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>";
$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini
$notransaksi="";
  if(isset($_POST['tex']))
  {
  	$notransaksi.=" and notransaksi like '%".$_POST['tex']."'";
  }
$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht 
        where createdby=".$_SESSION['standard']['userid'].$notransaksi." 
		and jeniskaryawan=0 and namatamu=''
		order by jlhbrs desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$jlhbrs=$bar->jlhbrs;
}		
//==================
		 
  if(isset($_POST['page']))
     {
	 	$page=$_POST['page'];
	    if($page<0)
		  $page=0;
	 }
	 
  
  $offset=$page*$limit;  

  $str="select * from ".$dbname.".sdm_pjdinasht 
        where createdby=".$_SESSION['standard']['userid'].$notransaksi." 
		and jeniskaryawan=0 and namatamu=''
		order by tanggalbuat desc, notransaksi desc limit ".$offset.",20";	
// echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
  $no=$page*$limit;
  while($bar=$res->fetch())
  {
  	$no+=1;

	  $namakaryawan='';
	  $strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->karyawanid;
	  $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	  $resx->setFetchMode(PDO::FETCH_OBJ);
	  while($barx=$resx->fetch())
	  {
	  	$namakaryawan=$barx->namakaryawan;
	  }
	  $add='';
	  if($bar->statuspersetujuan==0)
	  {
	  	$add.="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">
		 &nbsp <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">
         ";
	  }
   if($bar->statuspersetujuan==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspersetujuan==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else {
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	
	// $stpersetujuan.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select  style='width:100px;' onchange=ganti(this.options[this.selectedIndex].value,'persetujuan','".$bar->notransaksi."')>".$optKar."</select>";
   }

   if($bar->statushrd==2)
     $sthrd=$_SESSION['lang']['ditolak'];
  else if($bar->statushrd==1)
     $sthrd=$_SESSION['lang']['disetujui'];
  else{
     $sthrd=$_SESSION['lang']['wait_approve'];
	 $sthrd.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select   style='width:100px;' onchange=ganti(this.options[this.selectedIndex].value,'hrd','".$bar->notransaksi."')>".$optKar2."</select>";
  }


	$jenispersetujuan='PJDINAS';
  	$stat = array('0' =>$_SESSION['lang']['wait_approve'],'1' =>$_SESSION['lang']['disetujui'],'3'=>$_SESSION['lang']['ditolak'] );
  	$strap="select * from ".$dbname.".approval 
        where notransaksi='".$bar->notransaksi."'  and  jenispersetujuan='".$jenispersetujuan."'
		order by level asc";	
	$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
	$resap->setFetchMode(PDO::FETCH_ASSOC);
	while($barap=$resap->fetch())
	{
		$ttl.="Persetujuan ".$barap['level']." : ".$stat[$barap['status']]."\n";
	}
  
  
  
	$frm[1].="<tr class=rowcontent>
	  <td align=center>".$no."</td>
	  <td>".$bar->notransaksi."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggalbuat)."</td>
	  <td align=center title='".$ttl."'>".$stpersetujuan."</td>	
	  <td align=center>
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."','".$bar->jeniskaryawan."',event);\"> 
       ".$add."
	  </td>
	  </tr>";
  }
	$frm[1].="<tr>
		<td colspan=11 align=center>".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
		<br>
		<button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	</tr>";	   
$frm[1].="</tbody>
	   <tfoot>
	   </tfoot>
	   </table></div>
	 </fieldset>";

$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];
 	 
drawTab('FRM',$hfrm,$frm,100,1200);
CLOSE_BOX();
echo close_body('');
?>