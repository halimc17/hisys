<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_pjdinasnonstaff.js?v=<?php echo time(); ?>'></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_pjdinasnonstaff').'</span>');

//PT Tujuan
$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe in ('PT') order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optpt.="<option value='umum'>".$_SESSION['lang']['umum']."</option>";
while($bar=$res->fetch())
{
	$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

$lokasitugas = $_SESSION['empl']['lokasitugas'];

if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
	$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$optkdOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	while($bar=$res->fetch())
	{
		$optkdOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	}

	//nama karyawan
	$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in ('1','4','5','6') and karyawanid!='".$_SESSION['standard']['userid']."'  and statuskaryawan != 'Keluar'  and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar=$res->fetch()){
	$optkaryawan.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";
	}

}else{
	$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$optkdOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	while($bar=$res->fetch())
	{
		$optkdOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	}

	//nama karyawan
	$optkaryawan="<option value=''></option>";
	$str="select nik,karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in ('1','2','3','4','5','6') and karyawanid!='".$_SESSION['standard']['userid']."'  and lokasitugas='".$_SESSION['empl']['lokasitugas']."' and statuskaryawan != 'Keluar'  and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar=$res->fetch()){
		$optkaryawan.="<option value=".$bar['karyawanid'].">".$bar['nik']." - ".$bar['namakaryawan']."</option>";
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

$frm[0]="
     <fieldset style=float:left>
	 <legend>".$_SESSION['lang']['header']."</legend>
	 <fieldset>
	 <legend>".$_SESSION['lang']['karyawan']."</legend>
     <table border=0>
	 <tr>
	   	<input type=hidden value='insert' id=method>
	   	<input type=hidden value='' id=notransaksi>
	    <td>".$_SESSION['lang']['kodeorg']."</td>
	    <td>:</td>
		<td><select style=width:150px id='kodeorg' onchange='getpersetujuan()'>".$optkdOrg."</select></td>

	    <td width=70px>".$_SESSION['lang']['nama']."</td>
	    <td>:</td>
		<td><select  style=width:150px id='karyawanid'>".$optkaryawan."</select>
		<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
		</td>
	 </tr> 
	 <tr>
	    <td>".$_SESSION['lang']['tanggaldinas']."</td>
	    <td>:</td>
		<td><input type=text id=tanggalperjalanan class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) style='width:148px'></td>
		<td>".$_SESSION['lang']['tanggalkembali']."</td>
		<td>:</td>
		<td><input type=text id=tanggalkembali class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) style='width:148px'></td>
	 </tr>	
	 <tr>
	    <td style=width:120px>PT ".$_SESSION['lang']['tujuan']."</td>
		<td>:</td>
	    <td><select id='tujuan2' style='width:150px' onchange='getunit()'>".$optpt."</select></td>
	    <td >".$_SESSION['lang']['jenis']."</td>
		<td>:</td>
	    <td><select id='jenis' style='width:150px' onchange='getrincian()'>".$optjenis."</select></td>
	 </tr>	
	  <tr>
	 <td width=70px>".$_SESSION['lang']['unit']."</td><td>:</td>
		<td><select  style=width:150px id='unit' ></select></td>
	 </tr>
	 <tr hidden>
		<td>".$_SESSION['lang']['pemberitugas']."</td>
		<td>:</td>
	    <td>
	     <select id='tujuan1' style='width:245px' disabled='true'>".$optPemberiTgs."</select>
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

$frm[0].="
	<script> var cap_json = $cap_json; </script>
	<fieldset id=rutetujuanfield>
		<legend>".$_SESSION['lang']['tujuan']."</legend>
		<div>
			<table border=0 rute-num=0 width=100%>
				<tr> 
					<td width=120px>".$_SESSION['lang']['dari']."</td><td width=1>:</td>
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
					<td style=width:120px>".$_SESSION['lang']['tanggal']."</td><td width=1>:</td>
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
	<fieldset>
		<legend>".$_SESSION['lang']['approve']."</legend>
			<div id=formpersetujuan>	
			</div>";

			/*$jenispersetujuan='PJDINASNS';
		  	$countApp = getCountApproval($jenispersetujuan);
		  	for ($i=1; $i <=$countApp; $i++) { 

		  		if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
					//persetujuan1
					$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
					$str="select karyawanid from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						$whr=" karyawanid='".$bar['karyawanid']."'";
						$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

						$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
					}

				}else{
					//persetujuan1
					$jenispersetujuan='PJDINASNS';
					$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
					$str="select karyawanid from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						$whr=" karyawanid='".$bar['karyawanid']."'";
						$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

						$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
					}
				}

		  		$frm[0].="<tr>
						<td>".$_SESSION['lang']['approve']." ".$i."</td> 
						<td>:</td>
						<td><select id=persetujuan".$i." style='width:150px'>".$optper1."</select></td>
					</tr>";
		  	}*/	
	$frm[0].="
	</fieldset> 
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
		<tbody id=containerlist>
		<script>cariPJD(0)</script>"; 
$frm[1].="</tbody>
		<tfoot>
		</tfoot>
		</table></div>
		</fieldset>";

$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];
 	 
drawTab('FRM',$hfrm,$frm,100,'100%');
CLOSE_BOX();
echo close_body('');
?>