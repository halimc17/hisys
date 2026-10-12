<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/sdm_5plafonpjd.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5plafonpjd').'</span><br>');

#akses sesuai detail unit user (sama seperti halaman lain): unit = lokasi tugas + detail unit user, PT = induk unit-unit tersebut
$unitAkses = getOrgDetail(1);
$ptAkses = getOrgDetail(3);

$optRegional="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optRegionalx="<option value=''>".$_SESSION['lang']['all']."</option>";
$optptx="<option value=''>".$_SESSION['lang']['all']."</option>";
$optunx="<option value=''>".$_SESSION['lang']['all']."</option>";

$optpt = "<option value=''>Pilih Data</option>";
foreach($ptAkses as $kodept=>$namapt){
	$optpt.="<option value='".$kodept."'>".$kodept." - ".$namapt."</option>";
	$optptx.="<option value='".$kodept."'>".$kodept." - ".$namapt."</option>";
}

$optun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($unitAkses as $kodeunit=>$namaunit){
	$optun.="<option value='".$kodeunit."'>".$kodeunit." - ".$namaunit."</option>";
	$optunx.="<option value='".$kodeunit."'>".$kodeunit." - ".$namaunit."</option>";
}

$sql="select distinct(regional) as regional, nama from ".$dbname.".sdm_5regionalpjd";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_OBJ);
while($res=$qry->fetch()){
	$optRegional.="<option value='".$res->regional."'>".$res->nama."</option>";
	$optRegionalx.="<option value='".$res->regional."'>".$res->nama."</option>";
}

$optRegional.="<option value='OTH'>LAIN-LAIN</option>";
$optRegionalx.="<option value='OTH'>LAIN-LAIN</option>";

$optLevel="<option value=''>".$_SESSION['lang']['all']."</option>";
$optLevelx="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql2="select distinct kode, nama from ".$dbname.".sdm_5levelkaryawan order by kode";
$qry2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
$qry2->setFetchMode(PDO::FETCH_OBJ);
while($res2=$qry2->fetch()){
	$optLevel.="<option value='".$res2->kode."'>".$res2->nama."</option>";
	$optLevelx.="<option value='".$res2->kode."'>".$res2->nama."</option>";
}

$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTipex="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql2="select distinct id, tipe from ".$dbname.".sdm_5tipekaryawan order by tipe";
$qry2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
$qry2->setFetchMode(PDO::FETCH_OBJ);
while($res2=$qry2->fetch()){
	$optTipe.="<option value='".$res2->id."'>".$res2->tipe."</option>";
	$optTipex.="<option value='".$res2->id."'>".$res2->tipe."</option>";
}

$optgol="<option value=''>".$_SESSION['lang']['all']."</option>";
$optgolx="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql2="select distinct kodegolongan, namagolongan from ".$dbname.".sdm_5golongan order by kodegolongan";
$qry2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
$qry2->setFetchMode(PDO::FETCH_OBJ);
while($res2=$qry2->fetch()){
	$optgol.="<option value='".$res2->kodegolongan."'>".$res2->namagolongan."</option>";
	$optgolx.="<option value='".$res2->kodegolongan."'>".$res2->namagolongan."</option>";
}

$optJenisx="<option value=''>".$_SESSION['lang']['all']."</option>";
$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".sdm_5jenisbiayapjdinas order by id asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optJenis.="<option value='".$bar['id']."'>".$bar['id']." - ".$bar['keterangan']."</option>";
	$optJenisx.="<option value='".$bar['id']."'>".$bar['id']." - ".$bar['keterangan']."</option>";
}

echo"<fieldset>
     <legend><b>".$_SESSION['lang']['form']."</b></legend>
	 <table>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select id='pt' class='select2' onchange=getunit() style='width:200px'>".$optpt."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td><select id='unit' class='select2' style='width:200px'>".$optun."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['regional']." ".$_SESSION['lang']['tujuan']."</td>
			<td>:</td>
			<td><select id='regional' class='select2' style='width:200px'>".$optRegional."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipekaryawan']." </td>
			<td>:</td>
			<td><select id='tipekaryawan' class='select2' style='width:200px'>".$optTipe."</select></td>
		</tr>
		<tr>
			<td>Level ".$_SESSION['lang']['karyawan']."</td>
			<td>:</td>
			<td><select id='levelkaryawan' class='select2' style='width:200px'>".$optLevel."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodegolongan']." </td>
			<td>:</td>
			<td><select id='kodegolongan' class='select2' style='width:200px'>".$optgol."</select></td>
		</tr>
		<tr>
			<td style='vertical-align:top;'>".$_SESSION['lang']['jenis']."</td>
			<td style='vertical-align:top;'>:</td>
			<td><select id='jenis' class='select2' style='width:200px'>".$optJenis."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['rupiah']." (Rp)</td>
			<td>:</td>
			<td><input type='text' style='width:195px' id='rupiah' maxlength='20' class='myinputtextnumber' onKeyPress='return angka_doang(event);' onkeyup=\"if(this.value!=''){z.numberFormat(this.id,2);}\" value='0' /></td>
		</tr>
		<tr>
			<td colspan='2'></td>
			<td>
			<input type=hidden value=insert id=method>
			<input type=hidden id=kode>
			<button class=mybutton onclick=save()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td>
		</tr>
	 </table>
	 <span>Kosongkan nilai rupiah jika plafon berupa voucher atau yang tidak bernilai rupiah.</span>
     </fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"<fieldset>
	<legend><b>".$_SESSION['lang']['list']."</b></legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select id='ptsch' class='select2' onchange=loadData(0) style='width:150px'>".$optptx."</select></td>

			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td><select id='unitsch' class='select2' onchange=loadData(0) style='width:150px'>".$optunx."</select></td>

			<td>".$_SESSION['lang']['regional']." ".$_SESSION['lang']['tujuan']."</td>
			<td>:</td>
			<td><select id='regionalsch' class='select2' onchange=loadData(0) style='width:150px'>".$optRegionalx."</select></td>

			<td>".$_SESSION['lang']['tipekaryawan']." </td>
			<td>:</td>
			<td><select id='tipekaryawansch' class='select2' onchange=loadData(0) style='width:150px'>".$optTipex."</select></td>

			<td>Level ".$_SESSION['lang']['karyawan']." </td>
			<td>:</td>
			<td><select id='levelkaryawansch' class='select2' onchange=loadData(0) style='width:150px'>".$optLevelx."</select></td>

			<td>".$_SESSION['lang']['kodegolongan']." </td>
			<td>:</td>
			<td><select id='kodegolongansch' class='select2' onchange=loadData(0) style='width:150px'>".$optgolx."</select></td>

			<td style='vertical-align:top;'>".$_SESSION['lang']['jenis']."</td>
			<td style='vertical-align:top;'>:</td>
			<td><select id='jenissch' class='select2' onchange=loadData(0) style='width:150px'>".$optJenisx."</select></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['preview']."</button></td>
		</tr>
	</table><hr>
	<div class='table-scroll' style=height:60vh>
	<table class=sortable cellspacing=1 border=0 cellpadding=3 style='width:100%;'>
     <thead>
		<tr class=rowheader style='text-align:center'>
			<th >".$_SESSION['lang']['nomor']."</th>
			<th >".$_SESSION['lang']['pt']."</th>
			<th >".$_SESSION['lang']['unit']."</th>
			<th>".$_SESSION['lang']['regional']."<br>".$_SESSION['lang']['tujuan']."</th>
			<th>".$_SESSION['lang']['tipekaryawan']."</th>
			<th>Level ".$_SESSION['lang']['karyawan']."</th>
			<th>".$_SESSION['lang']['kodegolongan']."</th>
			<th>".$_SESSION['lang']['jenis']."</th>
			<th>".$_SESSION['lang']['rupiah']." (Rp)</th>
			<th colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</th>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>loadData(0)</script>";
echo"</tbody>
	<tfoot id=footData>
	</tfoot>
	</table>
	</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>
