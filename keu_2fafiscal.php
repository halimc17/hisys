<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/keu_2fafiscal.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_2fafiscal').'</span><br>');

$optper=$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optTipeAsset=$optsubTipeAsset=$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";

##get existing period
$str="select distinct periode from ".$dbname.".setup_periodeakuntansi where char_length(kodeorg)='4' order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
  $optper.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

##get PT
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select distinct b.induk from ".$dbname.".sdm_daftarasset a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);                   
while($bar=$res->fetch())
{
  $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

##get tipe asset
$sTipeAsset="select distinct kodetipe,namatipe from ".$dbname.".sdm_5tipeasset order by namatipe asc";
$res=$owlPDO->query($sTipeAsset) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rTipeAsset=$res->fetch())
{
  $optTipeAsset.="<option value='".$rTipeAsset['kodetipe']."'>".$rTipeAsset['namatipe']."</option>";
}

##get sub tipe asset
$sTipeAsset="select kodesub,namasub from ".$dbname.".sdm_5subtipeasset order by namasub asc";
$res=$owlPDO->query($sTipeAsset) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rTipeAsset=$res->fetch())
{
  $optsubTipeAsset.="<option value='".$rTipeAsset['kodesub']."'>".$rTipeAsset['namasub']."</option>";
}

echo"<fieldset style='width:300px;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
      <tr>
        <td>".$_SESSION['lang']['pt']."</td>
        <td>:</td>
        <td><select id=pt style='width:150px;' onchange=getUnit()>".$optpt."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit style='width:150px;' onchange=getPeriode()>".$optgudang."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td><select id=periode style='width:150px;'>".$optper."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['tipeasset']."</td>
        <td>:</td>
        <td><select id=tpAsset style='width:150px' onchange=getsubtpasset()><".$optTipeAsset."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['subtipeasset']."</td>
        <td>:</td>
        <td><select id=subtpAsset style='width:150px'><".$optsubTipeAsset."></select></td>
      </tr>
      <tr>
        <td colspan=2></td>
        <td><button class=mybutton onclick=getPreview()>".$_SESSION['lang']['preview']."</button>
            <button class=mybutton onclick=getExcel(event,'keu_slave_2fafiscal.php')>".$_SESSION['lang']['excel']."</button></td>
      <tr>
     </table>
	 
	 </fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"
	 <div style='width:100%;height:359px;overflow:scroll;' id=container></div>";
CLOSE_BOX();

close_body();
?>