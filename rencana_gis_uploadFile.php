<?
require_once('master_validation.php');
require_once('config/connection.php');
#ambil kode
if ($_SESSION['empl']['bagian'] == 'HRD') {
    $str = "select * from " . $dbname . ".rencana_gis_jenis where left(namajenis,3) in ('HRD','SOP')   order by namajenis";
} else {
    $str = "select * from " . $dbname . ".rencana_gis_jenis where left(namajenis,3) not in ('HRD','SOP') order by namajenis";
}
$optjenis = $optOrg = "";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optjenis.="<option value='" . $bar->kode . "'>" . $bar->namajenis . "</option>";
}
#ambil unit
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4
           order by namaorganisasi";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optOrg.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}
?>
<script   language=javascript1.2 src=js/generic.js></script>
<form id=frmUpload enctype=multipart/form-data method=post action=rencana_slave_simpan_gisfile.php target=frame>	
    <table><tr><td><?echo $_SESSION['lang']['unitkerja'];?></td><td>:<select style="width:175px;"name="kodeorg"><? echo $optOrg; ?></select></td></tr>
        <tr><td><?echo $_SESSION['lang']['tipe'];?></td><td>:<select name="kode"><? echo $optjenis; ?></select></td></tr>
        <tr><td><?echo $_SESSION['lang']['keterangan'];?></td><td>:<input type="text" name="keterangan" id="keterangan" size="35" onkeypress="return tanpa_kutip(event)"></td></tr>
        <tr><td><input type=hidden name=MAX_FILE_SIZE value=513000>                     
                File:</td><td>:<input name=photo type=file id=gambar size=35></td></tr></table>
    <font size="2pt">File type support: .zip/.tar/.gz/.rar/.7z/pdf/jpg</font>
</form>