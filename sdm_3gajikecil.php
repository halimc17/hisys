<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript1.2>var notif="<? echo $_SESSION['lang']['notifandayakin']; ?>";</script>
<script language=javascript1.2 src='js/sdm_3gajikecil.js'></script>

<?php

include('master_mainMenu.php');
//ambil periode penggajian
$str = "select distinct periode from " . $dbname . ".sdm_5periodegaji 
        where kodeorg='".$_SESSION['empl']['lokasitugas']."' and sudahproses=0  order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optPeriode = "";
while ($bar = $res->fetch()) {
    $optPeriode.="<option value='" . $bar->periode . "'>" . $bar->periode . "</option>";
}
$optUnitId="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
       where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' or induk='".$_SESSION['empl']['lokasitugas']."' and tipe in ('KEBUN','KANWIL','PABRIK','AFDELING','STATION','TRAKSI','WORKSHOP')order by kodeorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
    $optUnitId.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']."-".$rOrg['namaorganisasi']."</option>";
}
$optTpKary="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTp="select * from ".$dbname.".sdm_5tipekaryawan where id>=1 and id<7 ";
$rTp=fetchdata($sTp);
foreach($rTp as $row=>$lstTp){
  $optTpKary.="<option value='".$lstTp['id']."'>".$lstTp['tipe']."</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper("Gaji Kecil").'</span>');
$frm[0] = "<fieldset><legend>Form</legend>
              <table border=0>
              <tr><td>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['divisi']."<td>:<td><select id=unitId style='width:150px;'>".$optUnitId."</select></td></tr>
              <tr><td>".$_SESSION['lang']['periodegaji']."<td>:<td><select style='width:150px' id=periode>".$optPeriode."</select></td></tr> 
              <tr style=display:none><td>".$_SESSION['lang']['tanggal']."<td>:<td><input type=text class=myinputtext id=tglctoff name=tglctoff onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"   maxlength=\"10\"  style=\"width:150px;\" /></td></tr>     
              <tr><td>".$_SESSION['lang']['tipekaryawan']."<td>:<td><select style='width:150px' id=tpKary>".$optTpKary."</select></td></tr> 
              <tr><td><td><td>
             <button class=mybutton onclick=tampilkanCatu()>" . $_SESSION['lang']['preview'] . "</button>
             </table>
			 </fieldset>
             
			<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
             <div id=container style=height:350px;overflow:auto;'>
             </div></fieldset>";



$frm[1] = "<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
              <div id=container style=height:460px;overflow:auto;'>
			  <table class=sortable cellspacing=1 border=0>
              <thead>
              <tr class=rowheader>
              <td align=center>".$_SESSION['lang']['nomor']."</td>
              <td align=center>". $_SESSION['lang']['periode']."</td>
              <td align=center>". $_SESSION['lang']['divisi']."</td>
              <td align=center style=display:none>".$_SESSION['lang']['tipekaryawan']."</td>    
              <td align=center>". $_SESSION['lang']['rupiah']."(Rp)</td>    
              <td align=center>". $_SESSION['lang']['action']."</td>
               </tr>
               <tbody id=containerlist>
               <script languange=javascript1.2>loaddata(0)</script>";

$frm[1].="</tbody>
              <tfoot id=footData>
             </tfoot>
             </table>";



//========================
$hfrm[0] = $_SESSION['lang']['form'];
$hfrm[1] = $_SESSION['lang']['list'];

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 300, 900);
//===============================================
CLOSE_BOX();
echo close_body();
?>