<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script>pilih = "<?php echo $_SESSION['lang']['pilihdata'] ?>"</script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/sdm_prasarana.js'></script>
<?php
$arr = "##kdOrg##idKlmpk##idJenis##idLokasi##jmlhSarana##method##thnPerolehan##blnPerolehan##statFr##idData";
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_prasarana').'</span><br>','');
$optKlmpk = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optJns = $optKlmpk;
$sKlmpk = "select distinct * from " . $dbname . ".sdm_5kl_prasarana order by kode asc";
$qKlmpk = $owlPDO->query($sKlmpk) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk = $qKlmpk->fetch()) {
    $orgNmKlmpk[$rKlmpk['kode']] = $rKlmpk['nama'];
    $optKlmpk.="<option value='" . $rKlmpk['kode'] . "'>" . $rKlmpk['nama'] . "</option>";
}
$optKlmpk2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optbulan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$range = range(1,12);
foreach($range as $bulan){
	$optbulan .= "<option value='".addzero($bulan,2)."'>".numToMonth(addzero($bulan,2),'E','long')."</option>";	
}

$optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where namaorganisasi not like '%NON AKTI%' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by kodeorganisasi asc";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value='" . $rOrg['kodeorganisasi'] . "'>" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
}
echo"<fieldset style=float:left;>
     <legend>" . $_SESSION['lang']['prasarana'] . "</legend>
	 <table>
	 <tr>
	   <td>" . $_SESSION['lang']['unitkerja'] . "</td>
	   <td><input type=text class=myinputtext id=kdOrg name=kdOrg onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\" disabled value='" . $_SESSION['empl']['lokasitugas'] . "' /></td>
	 
	   <td>" . $_SESSION['lang']['kodekelompok'] . "</td>
	   <td><select id=idKlmpk style=\"width:155px;\" onchange=getJenis(0,0)>" . $optKlmpk . "</select></td>
	 </tr>
	 <tr>
	   <td>" . $_SESSION['lang']['jenis'] . "</td>
	   <td><select id=idJenis style=\"width:155px;\" onchange=getSatuan(0)>" . $optKlmpk2 . "</select></td>
	 
	   <td>" . $_SESSION['lang']['lokasi'] . "</td>
	   <td><select id=idLokasi style=\"width:155px;\">" . $optOrg . "</select></td>
	 </tr>	 
	  <tr>
	   <td>" . $_SESSION['lang']['jumlah'] . " (<span id=satuan></span>)</td>
	   <td><input type=text class=myinputtext id=jmlhSarana name=jmlhSarana onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20 /></td>
	 
	   <td>" . $_SESSION['lang']['tahunperolehan'] . "</td>
	   <td><input type=text class=myinputtext id=thnPerolehan name=thnPerolehan onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=4 /></td>
	 </tr>
	  <tr>
	   <td>" . $_SESSION['lang']['blnperolehan'] . "</td>
	   <td><select id=blnPerolehan style=\"width:155px;\">" . $optbulan . "</select></td>
	  
	   <td>" . $_SESSION['lang']['status'] . "</td>
	   <td><input type='checkbox' checked id=statFr name=statFr /> Tidak Aktif</td>
	 </tr> 
	  <tr>
		<td></td>
		 <input type=hidden value=insert id=method>
		 <td>
		 <button class=mybutton onclick=saveFranco('sdm_slave_prasarana','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>
		 <button class=mybutton onclick=cancelIsi()>" . $_SESSION['lang']['cancel'] . "</button>
		 </td>
	 </tr> 
	 </table>
     </fieldset><input type=hidden id=idData />";
CLOSE_BOX();
OPEN_BOX();
echo"<table class=sortable cellspacing=1 cellpadding=5 border=0>
     <thead>
	  <tr class=rowheader>
	   <th>No</th>
	   <th>" . $_SESSION['lang']['kodeorg'] . "</th>
	   <th>" . $_SESSION['lang']['kodekelompok'] . "</th>
	   <th>" . $_SESSION['lang']['jenis'] . "</th>
	   <th>" . $_SESSION['lang']['lokasi'] . "</th>
	   <th>" . $_SESSION['lang']['jumlah'] . "</th>
           <th>" . $_SESSION['lang']['tahunperolehan'] . "</th>
           <th>" . $_SESSION['lang']['blnperolehan'] . "</th>
           <th>" . $_SESSION['lang']['status'] . "</th>
	   <th colspan=2>Action</th>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>loadData()</script>";

echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table>";
CLOSE_BOX();
echo close_body();
?>