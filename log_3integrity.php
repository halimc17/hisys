<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=Javascript1.2 src=js/log_5integrity.js></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_3integrity').'</span>');
#organisasi
$optOrg = '';
$unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
$gudang_detailAkses=" and kodeorganisasi IN (".$unitDetailAkses.") ";
//if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
//   $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi";
//}
//else{
// $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "' order by namaorganisasi";
//}


if(count($unitDetailAkses) > 0){
    $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where 1=1  ".$gudang_detailAkses." order by namaorganisasi";
}else{
    $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "' order by namaorganisasi";
}


$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optOrg.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->kodeorganisasi . " - " . $bar->namaorganisasi . "</option>";
}
#periode
$optPeriode = '';
$str = "select distinct periode from " . $dbname . ".setup_periodeakuntansi where kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optPeriode.="<option value='" . $bar->periode . "'>" . $bar->periode . "</option>";
}
echo"<br><fieldset style=float:left><legend>Form</legend>";
echo "<table>
    <tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td><td><select id=kodeorg>" . $optOrg . "</select></td></tr>
    <tr><td>" . $_SESSION['lang']['periode'] . "</td><td>:</td><td><select id=periode>" . $optPeriode . "</select></td></tr>";

echo "<tr><td colspan=2></td><td><button class=mybutton onclick=getNotSync()>" . $_SESSION['lang']['preview'] . "</button>
            <button class=mybutton onclick=saveNotSync()>" . $_SESSION['lang']['save'] . "</button>    
   </td></tr></table></fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><div id=container></div></fieldset>";
CLOSE_BOX();
echo close_body();
?>
<script>
    setValue('kodeorg', '<?php echo $_SESSION['empl']['lokasitugas'] ?>');
</script>