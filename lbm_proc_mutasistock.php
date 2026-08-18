<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$arr = "##unit##tahun##kelompok##pilih##mayor##urut##asc##judul";
$_POST['judul'] == '' ? $judul = $_GET['judul'] : $judul = $_POST['judul'];


//=================ambil unit;  
$str = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi
    where tipe = 'GUDANG'
    order by kodeorganisasi";
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optunit.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}

//=================ambil unit temp;  
$str = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi
    where tipe = 'GUDANGTEMP'
    order by kodeorganisasi";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optunit.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}

//=================ambil periode;  
$opttahun = $optpilih = $optmayor = $opturut = $optasc = "";
$str = "select distinct SUBSTR(tanggal, 1, 4) as tahun from " . $dbname . ".log_transaksiht
    order by tahun";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $opttahun.="<option value='" . $bar->tahun . "'>" . $bar->tahun . "</option>";
}

//=================ambil kelompok;  
$str = "select kode, kelompok from " . $dbname . ".log_5klbarang
      order by kode";
$optkelompok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optkelompok.="<option value='" . $bar->kode . "'>" . $bar->kode . " - " . $bar->kelompok . "</option>";
}

$optpilih.="<option value='volume'>" . $_SESSION['lang']['volume'] . "</option>";
$optpilih.="<option value='nilai'>" . $_SESSION['lang']['nilai'] . "</option>";

$optmayor.="<option value=''></option>";
$optmayor.="<option value='mayor'>" . $_SESSION['lang']['yes'] . "</option>";

$opturut.="<option value='kodebarang'>Kode Barang</option>";
$opturut.="<option value='awal'>Saldo Awal</option>";
$opturut.="<option value='masuk'>Penerimaan</option>";
$opturut.="<option value='keluar'>Saldo Awal</option>";
$opturut.="<option value='akhir'>Saldo Akhir</option>";
$opturut.="<option value='harga'>Harga</option>";

$optasc.="<option value='asc'>Asc</option>";
$optasc.="<option value='desc'>Desc</option>";

echo"<table cellspacing=1 border=0>
    <tr>
    <td>" . $_SESSION['lang']['pilihgudang'] . "<input type=hidden id=judul name=judul value='" . $judul . "'></td>
    <td><select id=unit style='width:150px;'>" . $optunit . "</select></td>
    <td>Display</td>
    <td><select id=pilih style='width:150px;'>" . $optpilih . "</select></td>
    </tr>
    <tr>
    <td>" . $_SESSION['lang']['kelompokbarang'] . "</td>
    <td><select id=kelompok style='width:150px;'>" . $optkelompok . "</select></td>
    <td>Per Mayor</td>
    <td><select id=mayor style='width:150px;' onchange=pilihmayor()>" . $optmayor . "</select></td>
    </tr>
    <tr>
    <td>" . $_SESSION['lang']['periode'] . "</td>
    <td><select id=tahun style='width:150px;'>" . $opttahun . "</select></td>
    <td>Urut Berdasarkan</td>
    <td>
        <select id=urut style='width:96px;'>" . $opturut . "</select>
        <select id=asc style='width:50px;'>" . $optasc . "</select>
    </td>
    </tr>
<!--    <tr>
    <td>Per Mayor</td>
    <td><input type=\"checkbox\" name=mayor id=mayor value=\"Mayor\" onclick=pilihMayor()></td>
    </tr>
-->    <tr>
    <td colspan=2><button class=mybutton onclick=\"zPreview('lbm_slave_proc_mutasistock','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['proses'] . "</button></td>
    <td colspan=2><button class=mybutton onclick=\"zExcel(event,'lbm_slave_proc_mutasistock.php','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button></td>
    </tr></table>";
?>