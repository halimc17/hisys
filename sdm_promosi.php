<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/sdm_promosi.js?v=<?php echo time(); ?>'></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_promosi').'</span>');
$pos= getEnum($dbname,'sdm_riwayatjabatan','tipesk');
$opts="<option value=''></option>";
foreach($pos as $key=>$val){
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		if($val=='Promosi'){
			$caption=$_SESSION['lang']['Promosi'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}
		else if($val=='Mutasi'){
			$caption=$_SESSION['lang']['Mutasi1'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}
		else if($val=='Demosi'){
			$caption=$_SESSION['lang']['Demosi'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}

	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		if($val=='Promosi'){
			$caption=$_SESSION['lang']['Promosi'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}
		else if($val=='Mutasi'){
			$caption=$_SESSION['lang']['Mutasi1'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}
		else if($val=='Demosi'){
			$caption=$_SESSION['lang']['Demosi'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}

	}else{
		if($val=='Promosi'){
			$caption=$_SESSION['lang']['Promosi'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}
		else if($val=='Mutasi'){
			$caption=$_SESSION['lang']['Mutasi1'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}
		else if($val=='Demosi'){
			$caption=$_SESSION['lang']['Demosi'];
			$opts.="<option value='".$val."'>".$caption."</option>";
		}
	}
}

//get karyawan
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str=" select nik,karyawanid,namakaryawan,bagian,subbagian from ".$dbname.".datakaryawan
	where ( (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','1','2','3','4') ) OR (statuskaryawan = 'Kontrak' OR statuskaryawan = 'Percobaan') and lokasitugas in (".getOrgDetail('2').") order by namakaryawan asc";	
// }else{
// 	$str=" select nik,karyawanid,namakaryawan,subbagian from ".$dbname.".datakaryawan
// 	where left(lokasitugas,4)='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
// 	and ( (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','1','2','3','4') or statuskaryawan = 'Kontrak' )
// 	order by namakaryawan asc";   
// }

$optkar="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if($bar->subbagian!=''){
		$sub=" - [".$bar->subbagian."]";
	}else{
		$sub="";
	}
	
	$optkar.="<option value='".$bar->karyawanid."'>".$bar->nik." - ".$bar->namakaryawan."</option>";
}
//=================
//ambil daftar lokasi tugas
$optlokasitugas='';
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
and length(kodeorganisasi)=4 order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optlokasitugas.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch()){
	$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
}

$optsubbagian='';
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in('AFDELING','STATION','TRAKSI','WORKSHOP','BIBITAN') 
and length(kodeorganisasi)=6 order by induk,namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsubbagian.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch()){
	$optsubbagian.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
}
//============================
//jabatan
$optjabatan='';
$optjabatan="<option value=''></option>";
$str="select * from ".$dbname.".sdm_5jabatan where aktif='1' and namajabatan not like '%available' 
order by namajabatan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optjabatan.="<option value='".$bar->kodejabatan."'>".$bar->namajabatan."</option>";	
}
//===================================
$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif='1' order by tipe";
$opttipekaryawan='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opttipekaryawan.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch()){
	$opttipekaryawan.="<option value='".$bar->id."'>".$bar->tipe."</option>";	
}
$str="select * from ".$dbname.".sdm_5departemen where aktif='1' order by nama";
$optdepartemen='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optdepartemen.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
	$optdepartemen.="<option value='".$bar->kode."'>".$bar->nama."</option>";	
}
//========================================
$optgolongan='';
$str="select * from ".$dbname.".sdm_5golongan where aktif='1' order by kodegolongan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optgolongan.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch()){
	$optgolongan.="<option value='".$bar->kodegolongan."'>".$bar->namagolongan."</option>";	
}

$optKomponen='';
$str="select * from ".$dbname.".sdm_ho_component  order by id asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optKomponen.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch()){
	$optKomponen.="<option value='".$bar->id."###".$bar->name."'>".$bar->name."</option>";	
}

$optAtasan = "";
$str="select * from ".$dbname.".datakaryawan where tipekaryawan=0 order by namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optAtasan.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." (".$bar->nik.")</option>";	
}

// $optlokasitugas = "<option value=''>".$_SESSION['lang']['all']."</option>";
// $qLokasitugas = selectQuery($dbname, 'organisasi', "*", "tipe not in('BLOK','PT','STENGINE','STATION') and length(kodeorganisasi)=4 ORDER BY namaorganisasi ASC");
// $resLokasitugas = fetchData($qLokasitugas);
// foreach ($resLokasitugas as $bar) {
// 	$optlokasitugas .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// }

$optTipekaryawan = "<option value=''>".$_SESSION['lang']['all']."</option>";
$qTipekaryawan = selectQuery($dbname, 'sdm_5tipekaryawan', "*", "aktif = '1' AND id IN (SELECT id FROM ".$dbname.".sdm_5tipekaryawan_detail WHERE unittipe='".$_SESSION['empl']['tipelokasitugas']."') ORDER BY tipe");
$resTipekaryawan = fetchData($qTipekaryawan);
foreach ($resTipekaryawan as $bar) {
	$optTipekaryawan .= "<option value='".$bar['id']."'>".$bar['tipe']."</option>";
}
$optTipekaryawan .= "<option value='0,1'>STAFF dan NON STAFF</option>";

$optJnstransaksi = "<option value=''>".$_SESSION['lang']['all']."</option>";
$arrJnstransaksi = ['Mutasi', 'Promosi', 'Demosi'];
foreach ($arrJnstransaksi as $bar) {
	$optJnstransaksi .= "<option value='".$bar."'>".$bar."</option>";
}

$optBlnberlaku = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qThnpromosi = selectQuery($dbname, 'sdm_riwayatjabatan', "distinct mulaiberlaku", "left(mulaiberlaku,4) GROUP BY left(mulaiberlaku,4) ORDER BY mulaiberlaku DESC");
$resThnpromosi = fetchData($qThnpromosi);
foreach ($resThnpromosi as $thn) {
	$thnpromosi = substr($thn['mulaiberlaku'], 0, 4);
	
	for ($i = 1; $i <= 12; $i++) {
		$i = strlen($i) == 1 ? "0".$i : $i;
		$periode = $thnpromosi."-".$i;
		$optBlnberlaku .= "<option value='".$periode."'>".$periode."</option>";
	}
}

//=========================

echo"<div id=action_list>";
echo"<table border=0>
     <tr valign=middle>	 
		<td align=center style='width:75px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:75px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td valign=middle>
			<div id=formcari>
				<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td>:</td>
							<td><input class=myinputtext id=txtbabp style=\"width:145px;\"></td>
							
							<td>" . $_SESSION['lang']['nama'] . "</td>
							<td>:</td>
							<td><input class=myinputtext id=namasch style=\"width:145px;\"></td>

							<td>".$_SESSION['lang']['lokasitugas']."</td>
							<td>:</td>
							<td><select id=lokasitugas style=\"width:145px\">".$optlokasitugas."</select></td>
						</tr>
						<tr>
							<td>Jenis Transaksi</td>
							<td>:</td>
							<td><select id=jnstransaksi style=\"width:145px\">".$optJnstransaksi."</select></td>

							<td>Bulan Berlaku</td>
							<td>:</td>
							<td><select id=blnberlaku style=\"width:145px\">".$optBlnberlaku."</select></td>

							<td>Tipe Karyawan</td>
							<td>:</td>
							<td><select id=tipekaryawan style=\"width:145px\">".$optTipekaryawan."</select></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td colspan=3>
								<button class=mybutton id=btnprev onclick=cariSK()>" . $_SESSION['lang']['preview'] . "</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
		</td>
		</tr></table>";
		
echo "</div>";
CLOSE_BOX();
OPEN_BOX();

echo"<div id=inputdata style=display:none>
<fieldset>
<legend>".$_SESSION['lang']['form']."</legend>
<table border=0>
<tr> 	 
	<td style=width:120px >".$_SESSION['lang']['tipetransaksi']."</td>
	<td>:</td>
	<td><select  style=width:268px id=tipetransaksi onchange=getdata('#jenis',this.value);>".$opts."</select></td>

	<td style=width:200px>Tanggal Pengajuan</td>
	<td>:</td>
	<td><input type=text style=width:150px id=tanggalpen maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly></td>
</tr>
<tr> 	 
	<td>".$_SESSION['lang']['tanggalsurat']."</td>
	<td>:</td>
	<td><input style=width:265px type=text id=tanggalsk maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly></td>
	
	<td>".$_SESSION['lang']['tanggalberlaku']."</td>
	<td>:</td>
	<td><input type=text id=tanggalberlaku  onchange=getdata('#tanggal',this.value); style=width:150px maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly></td>
</tr>
<tr> 	 
	<td>".$_SESSION['lang']['karyawan']."</td>
	<td>:</td>
	<td><select  style=width:268px id=karyawanid onchange=cekKomponenGaji(this.options[this.selectedIndex].value)>".$optkar."</select>
	<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
	
	<td>Bertanggung Jawab Kepada</td>
	<td>:</td>
	<td><select  style=width:155px id=tanggungjawab  onchange=getdata('#tanggungjawab',this);>".$optjabatan."</select>
	<img id='tanggungjawab' onclick=z.elSearch('tanggungjawab',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>

	
</tr>
<tr> 	 
	<td valign=top>".$_SESSION['lang']['menimbang']."</td>
	<td valign=top>:</td>
	<td colspan=4>
		<textarea cols=90 rows=4 id=menimbang>1. Usaha - usaha Manajemen untuk meningkatkan efisiensi dan efektifitas kerja di ".getNamaOrg(getindukPT($_SESSION['empl']['lokasitugas']),'namaorganisasi')." .\n2. Pentingnya perencanaan strategis pada ".getNamaOrg(getindukPT($_SESSION['empl']['lokasitugas']),'namaorganisasi').".</textarea>
	</td>
</tr>
<tr> 	 
	<td valign=top>".$_SESSION['lang']['memperhatikan']."</td>
	<td valign=top>:</td>
	<td colspan=4>
		<textarea cols=90 rows=2 id=mengingat>Keterangan dan saran dari Manajemen pada tanggal [tanggal hasil evaluasi] terkait hasil evaluasi karyawan</textarea>
	</td>
</tr>
</table>

<hr>

<table border=0>
<tr>
	<td valign=top>
<fieldset>
<legend>".$_SESSION['lang']['lama']."</legend>
<table border=0>
<tbody id='x1'>
<tr>
	<td>".$_SESSION['lang']['lokasitugas']."</td>
	<td>:</td>
	<td><select disabled style=width:200px id=oldokasitugas>".$optlokasitugas."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['subbagian']."</td>
	<td>:</td>
	<td><select disabled style=width:200px id=oldsubbagian>".$optsubbagian."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['functionname']."</td><td> : </td>
	<td><select disabled style=width:200px  id=oldjabatan>".$optjabatan."</select></td>
</tr>				 
<tr>
	<td>".$_SESSION['lang']['tipekaryawan']."</td><td> : </td>
	<td><select disabled style=width:200px  id=oldtipekaryawan>".$opttipekaryawan."</select></td>
</tr>	
<tr>
	<td>".$_SESSION['lang']['departemen']."</td><td> : </td>
	<td><select disabled style=width:200px  id=olddepartemen>".$optdepartemen."</select></td>
</tr>                                 
<tr>
	<td hidden id=oldkomponx></td><td>".$_SESSION['lang']['grade']."</td><td> : </td>
	<td><select disabled style=width:200px  id=oldgolongan>".$optgolongan."</select></td>
</tr>
</tbody>
<tbody id='x2'>
</tbody>
</table>
</fieldset>
</td>
<td valign=top>
<fieldset>
<legend>".$_SESSION['lang']['baru']."</legend>
<table>
<tbody id='x3'>
<tr>
	<td>".$_SESSION['lang']['lokasitugas']."</td><td> : </td>
	<td><select style=width:200px  id=newlokasitugas >".$optlokasitugas."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['subbagian']."</td>
	<td>:</td>
	<td><select style=width:200px id=newsubbagian>".$optsubbagian."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['functionname']."</td><td> : </td>
	<td><select style=width:200px  id=newjabatan >".$optjabatan."</select></td>
</tr>				 
<tr>
	<td>".$_SESSION['lang']['tipekaryawan']."</td><td> : </td>
	<td><select style=width:200px  id=newtipekaryawan>".$opttipekaryawan."</select></td>
</tr>	
<tr>
	<td>".$_SESSION['lang']['departemen']."</td><td> : </td>
	<td><select style=width:200px  id=newdepartemen>".$optdepartemen."</select></td>
</tr>	
<tr>
	<td>".$_SESSION['lang']['grade']."</td><td> : </td>
	<td><select style=width:200px  id=newgolongan>".$optgolongan."</select></td>
</tr>
<tr>
	<td hidden id=newkomponx></td><td><select hidden style=width:200px  id=komponen>".$optKomponen."</select></td><td hidden> : </td>
	<td hidden><input id=jumlah type=text class=myinputtextnumber  value=0 size=15 maxlength=15 onkeypress=\"return angka_doang(event);\" onblur=change_number(this)> &nbsp; <img src=images/plus.gif class=resicon  title='Add' onclick='tambahKomponen()'> </td>
</tr>
</tbody>
<tbody id='x4'>
</tbody>
</table>
</fieldset>
</td>
</tr>
</table> 
<hr>          

<table border=0>  
<tr style='display:none'> 	 
	<td valign=top>".$_SESSION['lang']['paragraf1']."</td>
	<td valign=top>:</td>
	<td><textarea cols=80 rows=3 id=paragraf1></textarea></td>
</tr>  
<tr> 	 
	<td valign=top>".$_SESSION['lang']['paragraf2']."</td>
	<td valign=top>:</td>
	<td><textarea cols=100 rows=10 id=paragraf2>1. Tugas, wewenang dan tanggung jawab Saudara/i ditentukan oleh #tanggungjawab sesuai dengan Job Description pada jabatan tersebut dan yang diperintahkan atasan sesuai terlampir.\n2. Dalam melaksanakan tugas dan jabatannya bertanggung jawab kepada #tanggungjawab.\n3. Semua Surat Keputusan sebelumnya yang isinya bertentangan dengan Surat Keputusan ini dinyatakan Batal.\n4. Surat Keputusan ini berlaku mulai tanggal #tanggal dan akan ditinjau kembali apabila terdapat kekeliruan atau perkembangan lain di perusahaan.\n5. Surat Keputusan ini disampaikan kepada ybs. untuk dapat diketahui dan dilaksanakan dengan sebaik - baiknya. </textarea></td>
</tr>  
<tr hidden> 	 
	<td valign=top>".$_SESSION['lang']['paragraf3']."</td>
	<td valign=top>:</td>
	<td><textarea cols=100 rows=4 id=paragraf3></textarea></td>
</tr>
<tr hidden> 	 
	<td valign=top>".$_SESSION['lang']['paragraf4']."</td>
	<td valign=top>:</td>
	<td><textarea cols=100 rows=4 id=paragraf4></textarea></td>
</tr>
<tr style='display:none'> 	 
	<td valign=top>".$_SESSION['lang']['paragraf5']."</td>
	<td valign=top>:</td>
	<td><textarea cols=100 rows=4 id=paragraf5></textarea></td>
</tr>
<tr style='display:none'> 	 
	<td valign=top>".$_SESSION['lang']['paragraf6']."</td>
	<td valign=top>:</td>
	<td><textarea cols=100 rows=3 id=paragraf6></textarea></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['penandatangan']."</td>
	<td>:</td>
	<td><input style=border-color:red type=text class=myinputtext id=penandatangan size=35 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['functionname']."</td>
	<td> : </td>
	<td>
		<input  type=text class=myinputtext id=namajabatan size=35 maxlength=45 onkeypress=\"return tanpa_kutip(event);\">
	</td> 
</tr>
<tr>
	<td>".$_SESSION['lang']['tembusan']." (i)</td>
	<td> : </td>
	<td>
		<input type=text class=myinputtext id=tembusan1 size=35 maxlength=35 onkeypress=\"return tanpa_kutip(event);\">
	</td> 
</tr>
<tr>
	<td>".$_SESSION['lang']['tembusan']." (ii)</td>
	<td> : </td>
	<td>
		<input type=text class=myinputtext id=tembusan2 size=35 maxlength=35 onkeypress=\"return tanpa_kutip(event);\">
	</td> 
</tr>
<tr>
	<td>".$_SESSION['lang']['tembusan']." (iii)</td>
	<td> : </td>
	<td>
		<input type=text class=myinputtext id=tembusan3 size=35 maxlength=35 onkeypress=\"return tanpa_kutip(event);\">
	</td> 
</tr>
<tr>
	<td>".$_SESSION['lang']['tembusan']." (iv)</td>
	<td> : </td>
	<td>
		<input  type=text class=myinputtext id=tembusan4 size=35 maxlength=35 onkeypress=\"return tanpa_kutip(event);\">
	</td> 
</tr>
<tr>
	<td>".$_SESSION['lang']['tembusan']." (v)</td>
	<td> : </td>
	<td>
		<input type=text class=myinputtext id=tembusan5 size=35 maxlength=35 onkeypress=\"return tanpa_kutip(event);\">
	</td> 
</tr>	 	 	 	 	 
<tr>
	<td>
	<td>
	<td>

	<input type=hidden id=method value='insert'>
	<input type=hidden id=nosk value=''>
	<button class=mybutton onclick=savePromosi()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=clearForm()>".$_SESSION['lang']['new']."</button>

</table></fieldset>
</div>
";


#list data
echo"<div id=listdata>
	<table class=sortable cellspacing=1 border=0 cellpadding=5 width = 100%>
		<thead>
			<tr class=rowheader>
			<th align=center>No.</th>
			<th align=center>".$_SESSION['lang']['nomorsk']."</th>
			<th align=center>".$_SESSION['lang']['karyawan']."</th>
			<th align=center>".$_SESSION['lang']['tanggalsurat']."</th>
			<th align=center>".$_SESSION['lang']['tanggalberlaku']."</th>
			<th align=center>".$_SESSION['lang']['tipetransaksi']."</th>
			<th align=center>".$_SESSION['lang']['status']."</th>
			<th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
			<th align=center>".$_SESSION['lang']['createtime']."</th>
			<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
			</tr>
		</head>
		<tbody id=containerlist>
			<script>loadList();</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</div>
";

CLOSE_BOX();
echo close_body('');
?> 