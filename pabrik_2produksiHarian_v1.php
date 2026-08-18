<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_produksi_v1.js'></script>
<?
include('master_mainMenu.php');
$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'
      order by kodeorganisasi desc";
$optpabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optpabrik.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi."</option>";
}	
$optper="";
$sPeriode="select distinct substring(tanggal,1,7) as periode from ".$dbname.".pabrik_produksi order by tanggal desc ";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
    $optper.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['rprodksiPabrik']." ".$_SESSION['lang']['bulanan']).'</span>');
echo "<fieldset style='width:500px'>
      ".$_SESSION['lang']['kodeorganisasi']." : <select id=pabrik>".$optpabrik."</select>
      ".$_SESSION['lang']['periode']." : <select id=periode>".$optper."</select>
	  <button class=mybutton onclick=getLaporanPrdPabrik()>".$_SESSION['lang']['preview']."</button>
	 ";

CLOSE_BOX();
OPEN_BOX('','');
echo"<div id=container style='width:100%;max-height:500px;overflow:auto'>
     </div>"; 
CLOSE_BOX();
close_body();
?>