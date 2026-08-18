<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript1.2>var notif="<? echo $_SESSION['lang']['notifandayakin']; ?>";</script>
<script language=javascript1.2 src='js/pabrik_penerimaankomoditi.js'></script>
<?php
include('master_mainMenu.php');
$frm = array('','','');
//ambil periode penggajian
$str = "select distinct left(tanggal,7) as periode from ".$dbname.".pabrik_timbangan
        where millcode='".$_SESSION['empl']['lokasitugas']."' and kodebarang='40000001'  order by left(tanggal,7) desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch()) {
    $optPeriode.="<option value='" . $bar->periode . "'>" . $bar->periode . "</option>";
}
$optUnitId="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
       where tipe='PT' order by kodeorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
    $optUnitId.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$optTgl="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrOpt=array("40000001"=>"CPO");
foreach($arrOpt as $row=>$isiDt){
  $optData.="<option value='".$row."'>".$isiDt."</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('pabrik_penerimaankomoditi')).'</span>');
$frm[0].="<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
              <table border=0>
              <tr><td>".$_SESSION['lang']['pt']."<td>:<td><select id=unitId style='width:150px;' onchange=getTanggal()>".$optUnitId."</select></td></tr>
              <tr><td>".$_SESSION['lang']['tanggal']."<td>:<td><select  id='tgl' style='width:150px;' onchange=getTanggal()>".$optTgl."</select></td></tr> 
              <tr><td>".$_SESSION['lang']['komoditi']."<td>:<td><select style='width:150px' id=komoditi>".$optData."</select></td></tr> 
              <tr><td>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['kirim']."<td>:<td><input type=text class=myinputtextnumber style='width:150px' id=kgKirim onckeypress='return angka_doang(event)' disabled /></td></tr> 
              <tr><td>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['diterima']."<td>:<td><input type=text class=myinputtextnumber style='width:150px' id=kgTrima onckeypress='return angka_doang(event)' disabled /></td></tr> 
              <tr><td><td><td>
             <button class=mybutton onclick=saveData()>" . $_SESSION['lang']['save'] . "</button>
             </table>
</fieldset>";
$frm[0].="<div id=showDt style=display:none><fieldset style=width:560px;clear:both><legend>".$_SESSION['lang']['result']."</legend>";
$frm[0].="<div id=container></div></fieldset></div>";
$frm[1].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
$frm[1].="<fieldset style=float:left><legend>".$_SESSION['lang']['find']."</legend>";
$frm[1].="<table border=0 cellspacing=1 cellpadding=1>";
$frm[1].="<tr><td>".$_SESSION['lang']['pt']."</td><td><select id=ptCr style=width:150px onchange=loadData(0)>".$optUnitId."</select></td>";
$frm[1].="<td>".$_SESSION['lang']['periode']."</td><td><select id=periode style=width:150px onchange=loadData(0)>".$optPeriode."</select></td><td><select id=periode2 style=width:150px onchange=loadData(0)>".$optPeriode."</select></td>";
$frm[1].="</tr>";
$frm[1].="</table>";
$frm[1].="</fieldset><div style=clear:both;></div>";
$frm[1].="<table border=0 cellspacing=1 cellpadding=1 class=sortable>";
$frm[1].="<thead><tr class=rowheader align=center>";
$frm[1].="<td rowspan=2>No.</td>
          <td  rowspan=2>".$_SESSION['lang']['pt']."</td>
          <td  rowspan=2>".$_SESSION['lang']['tanggal']."</td>";
$frm[1].="<td colspan=3>".$_SESSION['lang']['berat']."</td>
          <td  rowspan=2>".$_SESSION['lang']['action']."</td></tr>";
$frm[1].="<tr class=rowheader align=center><td>".$_SESSION['lang']['kirim']."</td>";
$frm[1].="<td>".$_SESSION['lang']['diterima']."</td>";
$frm[1].="<td>".$_SESSION['lang']['selisih']."</td>";                       
$frm[1].="</tr></thead><tbody id=containerlist> ";
$frm[1].="</tbody><tfoot id='footData'></tfoot></table>";

$frm[1].="</fieldset><script type=\"text/javascript\">loadData(0);</script>";

//========================
$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,900);
CLOSE_BOX();
echo close_body();
?>