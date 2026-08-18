<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
$frm[0] = '';
$frm[1] = '';
$frm[2] = '';
$frm[3] = '';
$frm[4] = '';
$frm[5] = '';
$frm[6] = '';
?>
<script>
    pilh = " <? echo $_SESSION['lang']['pilihdata'] ?>";
</script>
<script>plh = "<?php echo $_SESSION['lang']['pilihdata']; ?>";</script>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/bibit_keluar_masuk.js?v=<?php echo time(); ?>"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
$optBlok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optKeg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optKdorg2 = $optKdorg = $optKdorg3 = $optKdBatch = $optKdBatchOld = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optKdorgmn2 = $optKdorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg2 = "select kodeorg from " . $dbname . ".setup_blok where  statusblok='BBT' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' order by kodeorg desc";
$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg2 = $qOrg2->fetch()) {
    $optKdorg.="<option value=" . $rOrg2['kodeorg'] . ">" . $optNmOrg[$rOrg2['kodeorg']] . "</option>";
}

$sBatch = "SELECT * FROM $dbname.kebun_5batchbibit ORDER BY kode asc, nama asc";
$rBatch = fetchData($sBatch);
foreach ($rBatch as $bth) {
    $optKdBatch .= "<option value='".$bth['kode']."'>".$bth['kode']." - ".$bth['nama']."</option>";
}

$sBatch = "SELECT a.* FROM $dbname.bibitan_batch a JOIN $dbname.bibitan_mutasi b ON a.batch = b.batch WHERE b.kodetransaksi='TMB' and b.post='1'";
$rBatch = fetchData($sBatch);
foreach ($rBatch as $bth) {
    $optKdBatchOld .= "<option value='".$bth['batch']."'>".$bth['batch']."</option>";
}

$str = "select * from " . $dbname . ".organisasi where  tipe='BIBITAN' and kodeorganisasi like '%MN%' and kodeorganisasi like '" . $_SESSION['empl']['lokasitugas'] . "%' order by kodeorganisasi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($barx = $res->fetch()) {
    @$optMN.="<option value=" . $barx['kodeorganisasi'] . ">" . $optNmOrg[$barx['kodeorganisasi']] . "</option>";
}

$sOrg22 = "select kodeorg from " . $dbname . ".setup_blok where  statusblok='BBT' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%MN%' order by kodeorg asc";
$qOrg22=$owlPDO->query($sOrg22) or die(print " Gagal: ".PDOException::getMessage());
$qOrg22->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg22 = $qOrg22->fetch()) {
    $optKdorg2.="<option value=" . $rOrg22['kodeorg'] . ">" . $optNmOrg[$rOrg22['kodeorg']] . "</option>";
    $optKdorgmn2.="<option value=" . $rOrg22['kodeorg'] . ">" . $optNmOrg[$rOrg22['kodeorg']] . "</option>";
}

$optKdorg2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg3 = "select kodeorg from " . $dbname . ".setup_blok where  statusblok='BBT'  order by kodeorg asc";
$qOrg3=$owlPDO->query($sOrg3) or die(print " Gagal: ".PDOException::getMessage());
$qOrg3->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg3 = $qOrg3->fetch()) {
    $optKdorg2.="<option value=" . $rOrg3['kodeorg'] . ">" . $optNmOrg[$rOrg3['kodeorg']] . "</option>";
}
$optJnsBbt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sBbt = "select distinct  jenisbibit from  " . $dbname . ".setup_jenisbibit order by jenisbibit";
$qBbt=$owlPDO->query($sBbt) or die(print " Gagal: ".PDOException::getMessage());
$qBbt->setFetchMode(PDO::FETCH_ASSOC);
while ($rBbt = $qBbt->fetch()) {
    $optJnsBbt.="<option value='" . $rBbt['jenisbibit'] . "'>" . $rBbt['jenisbibit'] . "</option>";
}
$optSup = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optStatPos = $optSup;
$arrStata = array("0" => "Not Posted", "1" => "Posted");
foreach ($arrStata as $lstStat => $dtstat) {
    $optStatPos.="<option value='" . $lstStat . "'>" . $dtstat . "</option>";
}

$sSupplier = "select distinct supplierid,namasupplier from " . $dbname . ".log_5supplier where supplierid like 'S%' and status=1 order by namasupplier asc";
$qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
$qSupplier->setFetchMode(PDO::FETCH_ASSOC);
while ($rSupplier = $qSupplier->fetch()) {
    $optSup.="<option value='" . $rSupplier['supplierid'] . "'>" . $rSupplier['namasupplier'] . "</option>";
}

$tglHrini = date("Ymd");

echo"<div id='formIsian' style='display:block;'>";
OPEN_BOX('','<span class=judul>'.getMenu('bibit_keluar_masuk').'</span>');
$frm[0].="<input type='hidden' id='proses1' value='saveTab1' /><input type='hidden' id='oldJnsbibit'  />
<fieldset><legend>" . $_SESSION['lang']['form'] . "</legend><fieldset style='width:350px;float:left;height:210px;'><legend>" . $_SESSION['lang']['tnmbibit'] . "</legend>";
if ($_SESSION['language'] == 'EN') {
    $frm[0].="Including receipt of seeds directly in the Main Nursery (from other sources)<br>";
} else {
    $frm[0].="Termasuk penerimaan bibit langsung ke MN dari tempat lain<br>";
}
$frm[0].="<table cellspacing=1 border=0>
<tr><td>" . $_SESSION['lang']['kodetransaksi'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:150px;' id='kdTransaksi' value='TMB'  disabled /></td></tr>
<tr><td>" . $_SESSION['lang']['kode'] . " " . $_SESSION['lang']['batch'] . "</td><td>:</td><td><select class=select2 id=kodeBatch style=width:155px>" . $optKdBatch . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['batch'] . "</td><td>:</td><td><input type='text' class='myinputtext' style='width:150px;' id='batch'  disabled /></td></tr>
<tr><td>" . $_SESSION['lang']['blok'] . "</td><td>:</td><td><select class=select2 id=kodeorgBibitan style=width:155px>" . $optKdorg . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['tanam'] . " (seed)</td><td>:</td><td><input type='text' class='myinputtextnumber'  style='width:150px;' id='jmlhBibitan' onkeypress='return angka_doang(event)' value='0' /></td></tr>
<tr><td>" . $_SESSION['lang']['keterangan'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:150px;' id='ket' onkeypress='return tanpa_kutip(event)' maxlength=45 /></td></tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['tgltanam'] . "</td><td>:</td><td><input type=text class=myinputtext id=tglTnm autocomplete=off style='width:150px;' onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>";
$frm[0].="<tr><td colspan=3>&nbsp;</td></tr></table>";
$optKebun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sKebun = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='KEBUN'";
$qKebun=$owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
$qKebun->setFetchMode(PDO::FETCH_ASSOC);
while ($rKebun = $qKebun->fetch()) {
    $optKebun.="<option value='" . $rKebun['kodeorganisasi'] . "'>" . $rKebun['namaorganisasi'] . "</option>";
}
$frm[0].="</fieldset>";
$frm[0].="<fieldset style='width:350px;height:210px;'><legend>" . $_SESSION['lang']['sumber'] . "</legend><table cellspacing=1 border=0>";
$frm[0].="<tr><td>" . $_SESSION['lang']['jenisbibit'] . "</td><td>:</td><td><select class=select2 id=jnsBibitan style=width:155px>" . $optJnsBbt . "</select></td></tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['supplier'] . "</td><td>:</td><td><select class=select2 id=supplier_id style=width:155px>" . $optSup . "</select><img hidden src=\"images/onebit_02.png\" class=\"resicon\" title='" . $_SESSION['lang']['findRkn'] . "' onclick=\"searchSupplier('" . $_SESSION['lang']['findRkn'] . "','<fieldset>" . $_SESSION['lang']['namasupplier'] . "&nbsp; : <input type=text class=myinputtext id=nmSupplier><button class=mybutton onclick=findSupplier()>" . $_SESSION['lang']['find'] . "</button></fieldset><div id=containerSupplier style=overflow=auto;height=380;width=485></div>',event);\"></td></tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['tglproduksi'] . "</td><td>:</td><td><input type=text class=myinputtext id=tgl2 autocomplete=off onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\" /></td></tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['nodo'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:150px;' id='nodo' autocomplete=off onkeypress='return tanpa_kutip(event)' /></td></tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['jumlah'] . " DO (seed)</td><td>:</td><td><input type='text' class='myinputtextnumber'  style='width:150px;' id='jmlh' onkeypress='return angka_doang(event)' value='0' /></td></tr>";
$frm[0].="<tr><td> " . $_SESSION['lang']['diterima'] . " (seed)</td><td>:</td><td><input type='text' class='myinputtextnumber'  style='width:150px;' id='jmlhTrima' onkeypress='return angka_doang(event)' value='0' /></td></tr>";
$frm[0].="<tr><td>" . $_SESSION['lang']['afkirbibit'] . "</td><td>:</td><td><input type='text' class='myinputtextnumber'  style='width:150px;' id='afkirKcmbh' onkeypress='return angka_doang(event)' value='0' /></td></tr>";
$frm[0].="</table></fieldset>";
$frm[0].="<div style=float:left;><button class=mybutton id=btlTmbl name=btlTmbl onclick=saveData(1)  >" . $_SESSION['lang']['save'] . "</button><button class=mybutton id=canbtlTmbl name=canbtlTmbl onclick=cancelData1()  >" . $_SESSION['lang']['cancel'] . "</button></div></fieldset>";
// $frm[0].="<div style=clear:both;>&nbsp;</div>";
$frm[0].="<fieldset><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['tanggal'] . " : </td>
        <td><input type='text' class='myinputtext' id='tglCari2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['batch'] . " : </td>
        <td><input type='text' class='myinputtext' id='batchCari2'  style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['status'] . " : </td>
        <td><select id=statCari2  style=\"width:100px;\">" . $optStatPos . "</select></td>
    <td>
    <button class=mybutton id=btlTmbl name=btlTmbl onclick=loadData1()  >" . $_SESSION['lang']['find'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center>No</th>
            <th align=center>" . $_SESSION['lang']['kodetransaksi'] . "</th>
            <th align=center>" . $_SESSION['lang']['batch'] . "</th>
            <th align=center>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
            <th align=center>" . $_SESSION['lang']['tgltanam'] . "</th>
            <th align=center>" . $_SESSION['lang']['jenisbibit'] . "</th>
            <th align=center>" . $_SESSION['lang']['supplier'] . "</th>
            <th align=center>" . $_SESSION['lang']['tglproduksi'] . "</th>
            <th align=center colspan=3>Action</th>
            </tr>
            </thead><tbody id=containData1><script>loadData1()</script> 
		";
$frm[0].="</tbody></table></fieldset>";

#################################################

$frm[4].="<input type='hidden' id='proses4' value='saveTab4' /><input type='hidden' id='oldBibit'  />
<fieldset>
    <legend>" . $_SESSION['lang']['form'] . "</legend>
    <fieldset style='width:350px;float:left;height:240px;'>
        <legend>Seleksi Bibit</legend>";
        if ($_SESSION['language'] == 'EN') {
            $frm[4].="Seed Selection From Old Batch<br>";
        } else {
            $frm[4].="Seleksi Bibit Dari Batch Lama<br>";
        }
        $frm[4].="<table cellspacing=1 border=0>
        <tr><td>" . $_SESSION['lang']['kodetransaksi'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:150px;' id='kdTransaksiSE' value='SEB'  disabled /></td></tr>
        <tr><td>" . $_SESSION['lang']['kode'] . " " . $_SESSION['lang']['batch'] . " " . $_SESSION['lang']['lama'] . "</td><td>:</td><td><select class=select2 id=kdBatchOld style=width:155px onchange=\"getBlokSEB();\">" . $optKdBatchOld . "</select></td></tr>
        <tr><td>" . $_SESSION['lang']['kode'] . " " . $_SESSION['lang']['batch'] . " " . $_SESSION['lang']['baru'] . "</td><td>:</td><td><select class=select2 id=kodeBatch2 style=width:155px>" . $optKdBatch . "</select></td></tr>
        <tr><td>" . $_SESSION['lang']['batch'] . "</td><td>:</td><td><input type='text' class='myinputtext' style='width:150px;' id='batch2'  disabled /></td></tr>
        <tr><td>" . $_SESSION['lang']['blok'] . "</td><td>:</td><td><select class=select2 id=kodeorgBibitan2 style=width:155px>" . $optKdorg3 . "</select></td></tr>
        <tr><td>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['tanam'] . " (seed)</td><td>:</td><td><input type='text' class='myinputtextnumber'  style='width:150px;' id='jmlhBibitan2' onkeypress='return angka_doang(event)' value='0' /></td></tr>";
        $frm[4].="<tr><td>" . $_SESSION['lang']['tgltanam'] . "</td><td>:</td><td><input type=text class=myinputtext id=tglTnm2 autocomplete=off style='width:150px;' onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>";
        $frm[4].="<tr><td>
            <button style='margin:10px 0;' class=mybutton id=btlTmbl name=btlTmbl onclick=saveData(4)>" . $_SESSION['lang']['save'] . "</button>
            <button style='margin:10px 0;' class=mybutton id=canbtlTmbl name=canbtlTmbl onclick=cancelData4()  >" . $_SESSION['lang']['cancel'] . "</button>
        </td></tr>";
        $frm[4].="<tr><td colspan=3>&nbsp;</td></tr>";
        $frm[4].="</table>";
    $frm[4].="</fieldset>";
$frm[4].="</fieldset>";
$frm[4].="<div style=clear:both;>&nbsp;</div>";
$frm[4].="<fieldset><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['tanggal'] . " : </td>
        <td><input type='text' class='myinputtext' id='tglCari1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['batch'] . " : </td>
        <td><input type='text' class='myinputtext' id='batchCari1'  style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['status'] . " : </td>
        <td><select id=statCari1  style=\"width:100px;\">" . $optStatPos . "</select></td>
    <td>
    <button class=mybutton id=btlTmbl name=btlTmbl onclick=loadData4()  >" . $_SESSION['lang']['find'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center>No</th>
            <th align=center>" . $_SESSION['lang']['kodetransaksi'] . "</th>
            <th align=center>" . $_SESSION['lang']['batch'] . " " . $_SESSION['lang']['baru'] . "</th>
            <th align=center>" . $_SESSION['lang']['batch'] . " " . $_SESSION['lang']['lama'] . "</th>
            <th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
            <th align=center>" . $_SESSION['lang']['tgltanam'] . "</th>
            <th align=center>" . $_SESSION['lang']['jenisbibit'] . "</th>
            <th align=center>" . $_SESSION['lang']['supplier'] . "</th>
            <th align=center>" . $_SESSION['lang']['tglproduksi'] . "</th>
            <th align=center colspan=3>Action</th>
            </tr>
            </thead><tbody id=containData4> 
		";
$frm[4].="</tbody></table></fieldset>";

#################################################

$optbatch = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$xbatch = "select distinct batch from " . $dbname . ".bibitan_mutasi where kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' order by batch desc";
$ybatch=$owlPDO->query($xbatch) or die(print " Gagal: ".PDOException::getMessage());
$ybatch->setFetchMode(PDO::FETCH_ASSOC);
while ($zbatch = $ybatch->fetch()) {
    $optbatch.="<option value='" . $zbatch['batch'] . "'>" . $zbatch['batch'] . "</option>";
}

////
$nott = "Termasuk Pemindahan dari PN ke PN tempat lain, maupun ke MN tempat lain";
if ($_SESSION['language'] == 'EN') {
    $nott = "Include seed movement from Pre Nursery to other Pre Nursery, or from Main Nursery to other Nursery";
}
$frm[3].="<input type='hidden' id='proses2' value='saveTab2' /><fieldset><legend>" . $_SESSION['lang']['transplatingbibit'] . "</legend>
<table cellspacing=1 border=0>
 <tr><td width=350px>
<table cellspacing=1 border=0>

<tr><td>" . $_SESSION['lang']['kodetransaksi'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:145px;' id='kdTransaksiTp' value='TPB'  disabled /></td></tr>

<tr><td>" . $_SESSION['lang']['batch'] . "</td><td>:</td><td><select class=select2 id='batchTp' style=width:150px onchange='getKodeorg()'>" . $optbatch . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['blok'] . "</td><td>:</td><td><select class=select2 id=kodeOrgTp style=width:150px onchange='cekSamaGak()'>" . $optKdorg . "</select></td></tr>

<tr><td>" . $_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class=myinputtext id=tglTp style='width:145px;' onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>



<tr><td>" . $_SESSION['lang']['tujuan'] . "</td><td>:</td><td><select class=select2 id=kodeOrgTjnTp style=width:150px onchange='cekSamaGak()'>" . $optKdorgmn2 . "</select></td></tr>

<tr><td>" . $_SESSION['lang']['jumlah'] . " &nbsp;(seed)</td><td>:</td><td><input type='text' class='myinputtextnumber'  style='width:145px;' id='jmlhTpBbtn' onkeypress='return angka_doang(event)' value='0' /></td></tr>

<tr><td>" . $_SESSION['lang']['keterangan'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:145px;' id='ketTp' onkeypress='return tanpa_kutip(event)' maxlength=45 /></td></tr>";

$frm[3].="<tr><td><td><td><button class=mybutton id=btlTmbl name=btlTmbl onclick=saveData(2)  >" . $_SESSION['lang']['save'] . "</button><button class=mybutton id=canbtlTmbl name=canbtlTmbl onclick=cancelData2()  >" . $_SESSION['lang']['cancel'] . "</button></td></tr></table>

 </td>
 <td valign=top>
   <fieldset style='text-align:left;width:300px;float:right;'>
   <legend><b><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
   <p>" . $nott . " 
   </p>
   </fieldset>	 
 </td></tr>
 
</table></fieldset>";

$frm[3].="<fieldset ><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
    <fieldset style=float:left>
	<table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['tanggal'] . "</td>
        <td><input type='text' class='myinputtext' id='tglCari3' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['batch'] . "</td>
        <td><input type='text' class='myinputtext' id='batchCari3'  style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['status'] . "</td>
        <td><select id=statCari3  style=\"width:100px;\">" . $optStatPos . "</select></td>
    <td>
    <button class=mybutton id=btlTmbl name=btlTmbl onclick=loadData2()  >" . $_SESSION['lang']['find'] . "</button>
    </td></tr>
    </table></fieldset ><div style=clear:both></div>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nomor'] . "</th>
            <th align=center>" . $_SESSION['lang']['kodetransaksi'] . "</th>
            <th align=center>" . $_SESSION['lang']['batch'] . "</th>
            <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center>" . $_SESSION['lang']['tujuan'] . "</th>
            <th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
            <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
            <th align=center colspan=3>" . $_SESSION['lang']['action'] . "</th>
            </tr>
            </thead><tbody id=containData2>
		";
$frm[3].="</tbody></table></fieldset>";


############################################################################




$frm[2].="<input type='hidden' id='proses3' value='saveTab3' /><fieldset><legend>" . $_SESSION['lang']['afkirbibit'] . "</legend>
<table cellspacing=1 border=0>

<tr><td>" . $_SESSION['lang']['kodetransaksi'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:145px;' id='kdTransAfk' value='AFB'  disabled /></td></tr>

<tr><td>" . $_SESSION['lang']['batch'] . "</td><td>:</td><td><select class=select2 id='batchAfk' style='width:150px' onchange='getKodeorg2()'>" . $optbatch . "</select></td></tr>

<tr><td>" . $_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class=myinputtext id='tglAfkirBibit' style='width:145px;' onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>

<tr><td>" . $_SESSION['lang']['blok'] . "</td><td>:</td><td><select class=select2 id='kdOrgAfk' style=width:150px>" . $optKdorg . "</select></td></tr>

<tr><td>" . $_SESSION['lang']['jumlah'] . "&nbsp;(seed)</td><td>:</td><td><input type='text' class='myinputtextnumber'  style='width:145px;' id='jmlhAfk' onkeypress='return angka_doang(event)' value='0' /></td></tr>

<tr><td>" . $_SESSION['lang']['keterangan'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:145px;' id='ketAfk' onkeypress='return tanpa_kutip(event)' maxlength=45 /></td></tr>";

$frm[2].="<tr><td>".$_SESSION['lang']['dokumen']."</td>
			<td>:</td>
			<td><input name=fileupload type=file id=fileupload class=mybutton style=width:160px></td></tr>";

$frm[2].="<tr><td><td><td><button class=mybutton   name=btlTmbl onclick=saveData(3)  >" . $_SESSION['lang']['save'] . "</button><button class=mybutton   name=canbtlTmbl onclick=cancelData3()  >" . $_SESSION['lang']['cancel'] . "</button></td></tr></table></fieldset>
";

$frm[2].="<fieldset><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['tanggal'] . "</td>
        <td><input type='text' class='myinputtext' id='tglCari4' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['batch'] . "</td>
        <td><input type='text' class='myinputtext' id='batchCari4'  style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['status'] . "</td>
        <td><select id=statCari4  style=\"width:100px;\">" . $optStatPos . "</select></td>
    <td>
    <button class=mybutton id=btlTmbl name=btlTmbl onclick=loadData3()  >" . $_SESSION['lang']['find'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nomor'] . "</th>
            <th align=center>" . $_SESSION['lang']['kodetransaksi'] . "</th>
            <th align=center>" . $_SESSION['lang']['batch'] . "</th>
			<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
            <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
            <th align=center colspan=4>" . $_SESSION['lang']['action'] . "</th>
            </tr>
            </thead><tbody id=containData3>
		";
$frm[2].="</tbody></table></fieldset>";


###############################################################################################
###################################################################################################

$frm[1].="<input type='hidden' id='proses5' value='saveTab5' /><fieldset><legend>" . $_SESSION['lang']['doubletoon'] . "</legend>
<table cellspacing=1 border=0>

<tr><td>" . $_SESSION['lang']['kodetransaksi'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:145px;' id='kdTransaksiDbt' value='DBT'  disabled /></td></tr>

<tr><td>" . $_SESSION['lang']['batch'] . "</td><td>:</td><td><select class=select2 id='batchDbt' style='width:150px' onchange='getKodeorg3()'>" . $optbatch . "</select></td></tr>

<tr><td>" . $_SESSION['lang']['blok'] . "</td><td>:</td><td><select class=select2 id='kdOrgDbt' style=width:150px>" . $optKdorg . "</select></td></tr>

<tr><td>" . $_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class=myinputtext id='tglDbt' style='width:145px;' onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>

<tr><td>" . $_SESSION['lang']['jumlah'] . "&nbsp;(seed)</td><td>:</td><td><input type='text' class='myinputtextnumber'  style='width:145px;' id='jmlhDbt' onkeypress='return angka_doang(event)' value='0' /></td></tr>

<tr><td>" . $_SESSION['lang']['keterangan'] . "</td><td>:</td><td><input type='text' class='myinputtext'  style='width:145px;' id='ketDbt' onkeypress='return tanpa_kutip(event)' maxlength=45 /></td></tr>";

$frm[1].="<tr><td><td><td><button class=mybutton id=btlTmbl name=btlTmbl onclick=saveData(5)  >" . $_SESSION['lang']['save'] . "</button><button class=mybutton id='' name=canbtlTmbl onclick=cancelData5()  >" . $_SESSION['lang']['cancel'] . "</button></td></tr></table></fieldset>
";

$frm[1].="<fieldset><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['tanggal'] . " : </td>
        <td><input type='text' class='myinputtext' id='tglCari5' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['batch'] . " : </td>
        <td><input type='text' class='myinputtext' id='batchCari5'  style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['status'] . " : </td>
        <td><select id=statCari5  style=\"width:100px;\">" . $optStatPos . "</select></td>
    <td>
    <button class=mybutton id=btlTmbl name=btlTmbl onclick=loadData5()  >" . $_SESSION['lang']['find'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nomor'] . "</th>
            <th align=center>" . $_SESSION['lang']['kodetransaksi'] . "</th>
            <th align=center>" . $_SESSION['lang']['batch'] . "</th>
			<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
            <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
            <th align=center colspan=3>" . $_SESSION['lang']['action'] . "</th>
            </tr>
            </thead><tbody id=containData5>
		";
$frm[1].="</tbody></table></fieldset>";

###################################################################################################
$optKegiatan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arragama = getEnum($dbname, 'bibitan_mutasi', 'jenistanam');
foreach ($arragama as $kei => $fal) {
    $optKegiatan.="<option value='" . $kei . "'>" . $fal . "</option>";
}
$arr = array(
    "Ditanam (INTI)",
    "Disisip (INTI)",
    "Ditanam (PLASMA)",
    "Disisip (PLASMA)",
    "Dijual (External)",
    "Afiliasi",
    "Disulam (INTI)",
    "Disulam (PLASMA)",
    "Non Penjualan (External)"
);
$optintex = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach ($arr as $isi => $eia) {
    $optintex.="<option value=" . $isi . " >" . $eia . "</option>";
}
$optKode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optAfd = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$optKaryawan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sKaryawan = "select distinct karyawanid,namakaryawan from " . $dbname . ".datakaryawan a
	left join " . $dbname . ".sdm_5jabatan b ON a.kodejabatan = b.kodejabatan
	where tipekaryawan='0' and karyawanid!='" . $_SESSION['standard']['userid'] . "'
	AND substr(lokasitugas,3,2) != 'HO'
	AND a.kodejabatan IN('5','9')";
$qKaryawan=$owlPDO->query($sKaryawan) or die(print " Gagal: ".PDOException::getMessage());
$qKaryawan->setFetchMode(PDO::FETCH_ASSOC);
while ($rKaryawan = $qKaryawan->fetch()) {
    $optKaryawan.="<option value='" . $rKaryawan['karyawanid'] . "'>" . $rKaryawan['namakaryawan'] . "</option>";
}

$optKaryawan2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sKaryawan = "select distinct karyawanid,namakaryawan from " . $dbname . ".datakaryawan a
	left join " . $dbname . ".sdm_5jabatan b ON a.kodejabatan = b.kodejabatan
	where tipekaryawan='0' and karyawanid!='" . $_SESSION['standard']['userid'] . "'
	AND substr(lokasitugas,3,2) != 'HO'
	AND a.kodejabatan='5'";
$qKaryawan=$owlPDO->query($sKaryawan) or die(print " Gagal: ".PDOException::getMessage());
$qKaryawan->setFetchMode(PDO::FETCH_ASSOC);
while ($rKaryawan = $qKaryawan->fetch()) {
    $optKaryawan2.="<option value='" . $rKaryawan['karyawanid'] . "'>" . $rKaryawan['namakaryawan'] . "</option>";
}

$optKaryawan3 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
// $sKaryawan = "select distinct karyawanid,namakaryawan from " . $dbname . ".datakaryawan a
// 	left join " . $dbname . ".sdm_5jabatan b ON a.kodejabatan = b.kodejabatan
// 	where tipekaryawan='0' and karyawanid!='" . $_SESSION['standard']['userid'] . "'
// 	AND substr(lokasitugas,3,2) != 'HO'
// 	AND a.kodejabatan='4'";
// $qKaryawan=$owlPDO->query($sKaryawan) or die(print " Gagal: ".PDOException::getMessage());
// $qKaryawan->setFetchMode(PDO::FETCH_ASSOC);
// while ($rKaryawan = $qKaryawan->fetch()) {
//     $optKaryawan3.="<option value='" . $rKaryawan['karyawanid'] . "'>" . $rKaryawan['namakaryawan'] . "</option>";
// }

$frm[5].="<input type='hidden' id='proses7' value='saveTab7' /><fieldset><legend>" . $_SESSION['lang']['pengirimanBibit'] . "</legend>
<table cellspacing=1 border=0>

<tr>
	<td>" . $_SESSION['lang']['kodetransaksi'] . "</td><td>:</td>
	<td><input type='text' class='myinputtext'  style='width:145px;' id='kdTransPnb' value='PNB'  disabled /></td>
	
	<td>" . $_SESSION['lang']['nomorspb'] . "</td><td>:</td>
	<td><input type='text' class='myinputtext'  style='width:145px;' id='ketPnb' disabled onkeypress='return tanpa_kutip(event)' maxlength=45 /></td>
	
	<td>" . $_SESSION['lang']['tujuan'] . "</td><td>:</td>
	<td><select class=select2 id='custId' style=width:150px onchange='getKodeorgBlok()'>" . $optKode . "</select></td>
	
    <td>" . $_SESSION['lang']['disetujuioleh'] . "</td><td>:</td>
	<td><select class=select2 id='kplDivBbt' style=width:150px>" . $optKaryawan2 . "</select></td>
</tr>
<tr>
	<td>" . $_SESSION['lang']['batch'] . "</td><td>:</td>
	<td><select class=select2 id='batchPnb' style=width:150px onchange='getKodeorgN()'>" . $optbatch . "</select></td>

	<td>" . $_SESSION['lang']['kodevhc'] . "</td><td>:</td>
	<td><input type='text' class='myinputtext'  style='width:145px;' id='kdvhc' onkeypress='return tanpa_kutip(event)' maxlength=8 /></td>
	
	<td>" . $_SESSION['lang']['kodeblok'] . "</td><td>:</td>
	<td><select class=select2 id='kdAfdeling' style=width:150px disabled >" . $optAfd . "</select></td>
	
    <td>" . $_SESSION['lang']['penerima'] . "</td><td>:</td>
	<td><select class=select2 id='kplDivKbn' style=width:150px>" . $optKaryawan3 . "</select></td>
</tr>
<tr>
	<td>" . $_SESSION['lang']['tanggal'] . "</td><td>:</td>
	<td><input type=text onchange=getNumberSPB() class=myinputtext id='tglPnb' style='width:145px;' onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>
	
	<td>Rit Ke</td><td>:</td>
	<td><input type='text' class='myinputtextnumber'  style='width:145px;' id='jmlRit' onkeypress='return angka_doang(event)' maxlength=20 /></td>
	
	<td>" . $_SESSION['lang']['lokasi'] . " " . $_SESSION['lang']['detailPengiriman'] . "</td><td>:</td>
	<td><input type='text' class='myinputtext'  style='width:145px;' id='detPeng' onkeypress='return tanpa_kutip(event)' maxlength=45 /></td>
	
</tr>
<tr>
	<td>" . $_SESSION['lang']['dari'] . "</td><td>:</td>
	<td><select class=select2 id='kdOrgPnb' onchange=getNumberSPB() style=width:150px>" . $optMN . "</select></td>
	
	<td>" . $_SESSION['lang']['sopir'] . "</td><td>:</td>
	<td><input type='text' class='myinputtext'  style='width:145px;' id='nmSupir' onkeypress='return tanpa_kutip(event)' maxlength=20 /></td>
	
	<td>" . $_SESSION['lang']['kegiatan'] . "</td><td>:</td>
	<td><select class=select2 id='kegId' style=width:150px>" . $optKegiatan . "</select></td>
	
</tr>
<tr>
	<td>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['bibit'] . "</td><td>:</td>
	<td><input type='text' class='myinputtextnumber'  style='width:145px;' id='jmlhPnb' onkeypress='return angka_doang(event)' value='0' /></td>
	
	<td>" . $_SESSION['lang']['Intex'] . "</td><td>:</td>
	<td><select class=select2 id='intexDt' style=width:150px onchange='getCustdata(0,0,0)'>" . $optintex . "</select></td>
	
	<td>" . $_SESSION['lang']['dibuatoleh'] . "</td><td>:</td>
	<td><select class=select2 id='assistenPnb' style=width:150px>" . $optKaryawan . "</select></td>
	
</tr>";

$frm[5].="<tr><td><td><td><button class=mybutton id='' name=btlTmbl onclick=saveData(7)  >" . $_SESSION['lang']['save'] . "</button><button class=mybutton id='' name=canbtlTmbl onclick=cancelData7()  >" . $_SESSION['lang']['cancel'] . "</button></td></tr></table></fieldset>
";

$frm[5].="<fieldset><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['tanggal'] . "</td>
        <td><input type='text' class='myinputtext' id='tglCari7' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['batch'] . "</td>
        <td><input type='text' class='myinputtext' id='batchCari7'  style=\"width:100px;\" /></td>
        <td>" . $_SESSION['lang']['status'] . "</td>
        <td><select id=statCari7  style=\"width:100px;\">" . $optStatPos . "</select></td>
    <td>
    <button class=mybutton id=btlTmbl name=btlTmbl onclick=loadData7()  >" . $_SESSION['lang']['find'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
            <tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nomor'] . "</th>
            <th align=center>" . $_SESSION['lang']['kodetransaksi'] . "</th>
            <th align=center>" . $_SESSION['lang']['batch'] . "</th>
            <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
            <th align=center>" . $_SESSION['lang']['kegiatan'] . "</th>
            <th align=center>" . $_SESSION['lang']['nomorspb'] . "</th>
            <th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
            <th align=center>" . $_SESSION['lang']['customerlist'] . "</th>
            <th align=center>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center>" . $_SESSION['lang']['asisten'] . "</th>
            <th align=center colspan=4>" . $_SESSION['lang']['action'] . "</th>
            </tr>
            </thead><tbody id=containData7>
		";
$frm[5].="</tbody></table></fieldset>";


###################################################################################################



$frm[6].="<fieldset><legend>" . $_SESSION['lang']['stockdetail'] . "</legend>

    <table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nomor'] . "</th>
            <th align=center>" . $_SESSION['lang']['batch'] . "</th>
            <th align=center>" . $_SESSION['lang']['blok'] . "</th>
			<th align=center>" . $_SESSION['lang']['saldo'] . "</th>
			<th align=center>" . $_SESSION['lang']['supplier'] . "</th>
            <th align=center>" . $_SESSION['lang']['umur'] . "(" . $_SESSION['lang']['bulan'] . ")</th>
            </tr>
            </thead><tbody id=containDataStock>
		";
$frm[6].="</tbody></table></fieldset>";







###################################################################################################
//========================
$hfrm[0] = $_SESSION['lang']['tnmbibit'];
$hfrm[1] = $_SESSION['lang']['doubletoon'];
$hfrm[2] = $_SESSION['lang']['afkirbibit'];
$hfrm[3] = $_SESSION['lang']['transplatingbibit'];
$hfrm[4] = "Seleksi Bibit";
$hfrm[5] = $_SESSION['lang']['pengirimanBibit'];
$hfrm[6] = $_SESSION['lang']['stockdetail'];
//$hfrm[6]=$_SESSION['lang']['prosesUlang'];
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 180, '100%');
//===============================================	
?>


<?php
CLOSE_BOX();

echo"</div>";

echo close_body();
?>