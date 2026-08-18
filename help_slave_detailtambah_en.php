<?php

require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/help_tambah.js"></script>
<?php
  $theme=$_SESSION['theme'];
  if($theme=='skyblue' || $theme==''){
    $gen='generic.css';
  }else if($theme=='red'){
    $gen='genericRed.css';  
  }else{
    $gen='genericGray.css';  
  }  
echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>";

$proses = isset($_GET['proses']) ? $_GET['proses'] : '';
$param = $_GET;

$where = "kode='" . $param['index'] . "' and modul='" . $param['modul'] . "'";
$query = selectQuery($dbname, 'owl_help_en', '*', $where);
$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $isi = $bar->isi;
    $html = $bar->tujuan;
}
$isi = str_replace("<##", "<image src='image/", $isi);
$isi = str_replace("##>", "'>", $isi);
$stream = "$isi";
echo "<fieldset><legend>" . $param['modul'] . "</legend>";
echo $stream;
echo "<hr>";
$dd = str_replace("help/en", "", $html);
if ($dd == 'null') {
    
} else {
    include($html);
}
echo "</fieldset>";
?>
