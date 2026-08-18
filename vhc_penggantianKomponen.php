<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<b>".$_SESSION['lang']['penggantianKomponen']."</b>");
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script>
 jdl_ats_0='<?php echo $_SESSION['lang']['find']?>';
// alert(jdl_ats_0);
 jdl_ats_1='<?php echo $_SESSION['lang']['findBrg']?>';
 content_0='<fieldset><legend><?php echo $_SESSION['lang']['findnoBrg']?></legend>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div>';
tmblSave='<?php echo $_SESSION['lang']['save']?>';
tmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
tmblDone='<?php echo $_SESSION['lang']['done']?>';
tmblCancelDetail='<?php echo $_SESSION['lang']['cancel']?>';
</script>
<script type="application/javascript" src="js/vhc_penggantianKomponen.js"></script>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
<?php
echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
           <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
           <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
                        echo $_SESSION['lang']['notransaksi'].":<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>";
                        echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
                        echo"<button class=mybutton onclick=cariTransaksi()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
         </table> "; 
?>
</div>
<?php
CLOSE_BOX();
?>
<div id="list_ganti">
<script>load_new_data();</script>
</div>



<div id="headher" style="display:none">
<?php
OPEN_BOX();
$svhc="select kodevhc,jenisvhc,tahunperolehan from ".$dbname.".vhc_5master  order by kodevhc"; //echo $svhc;
$qvhc=$owlPDO->query($svhc) or die(print " Gagal: ".PDOException::getMessage());
$qvhc->setFetchMode(PDO::FETCH_ASSOC);
$optVhc="";
while($rvhc=$qvhc->fetch()){
    $optVhc.="<option value='".$rvhc['kodevhc']."'>".$rvhc['kodevhc']."[".$rvhc['tahunperolehan']."]</option>";
}
$svhc2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='WORKSHOP' 
                and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi asc"; //echo $svhc;
$qvhc2=$owlPDO->query($svhc2) or die(print " Gagal: ".PDOException::getMessage());
$qvhc2->setFetchMode(PDO::FETCH_ASSOC);
$optOrg="";
while($rvhc2=$qvhc2->fetch()){
    $optOrg.="<option value='".$rvhc2['kodeorganisasi']."'>".$rvhc2['namaorganisasi']."</option>";
}
$optOrgTr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$svhc23="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI'  
//                 and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi asc"; //echo $svhc;
$svhc23="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI'  
                 order by namaorganisasi asc"; //echo $svhc;
$qvhc23=$owlPDO->query($svhc23) or die(print " Gagal: ".PDOException::getMessage());
$qvhc23->setFetchMode(PDO::FETCH_ASSOC);
while($rvhc23=$qvhc23->fetch()){
        $optOrgTr.="<option value='".$rvhc23['kodeorganisasi']."'>".$rvhc23['namaorganisasi']."</option>";
}

?>
<fieldset>
<legend><?php echo $_SESSION['lang']['header']?></legend>
<table cellspacing="1" border="0">
<tr>
<td><?php echo $_SESSION['lang']['workshop']?></td>
<td>:</td>
<td><select id="codeOrg" name="codeOrg" style="width:150px;"  onchange="getNotrans(0)"><option value=""></option><?php echo $optOrg;?></select></td>
</tr>

<tr>
<td><?php echo $_SESSION['lang']['notransaksi']?></td>
<td>:</td>
<td><input type="text"  id="trans_no" name="trans_no" class="myinputtext" style="width:150px;" /></td>
</tr>
<tr>
<tr>
<td><?php echo $_SESSION['lang']['kodetraksi']?></td>
<td>:</td>
<td><select id="kdTraksi" name="kdTraksi" style="width:150px;" onchange="getKdVhc(0,0)" ><?php echo $optOrgTr;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['kodevhc']?></td>
<td>:</td>
<td><select id="vhc_code" name="vhc_code" style="width:150px;"><?php //echo $optVhc;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['tanggal']?></td>
<td>:</td>
<td><input type="text" class="myinputtext" id="tgl_ganti" name="tgl_ganti" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10" style="width:150px;" /></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['downtime']?></td>
<td>:</td>
<td><input type="text" class="myinputtextnumber" id="dwnTime" name="dwnTime" onkeypress="return angka_doang(event);"  value="0"  maxlength="10" style="width:150px;" />&nbsp;<?php echo $_SESSION['lang']['jmlhJam']?></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['descDamage']?></td>
<td>:</td>
<td><input type="text" class="myinputtext" id="descDmg" name="descDmg" onkeypress="return tanpa_kutip(event);" maxlength="45" style="width:150px;" /></td>
</tr>
<tr>
<td colspan="3" id="tmblHeader">
</td>
</tr>
</table>
</fieldset>

<?php
CLOSE_BOX();
?>
</div>
<div id="detail_ganti" style="display:none">
<?php 
OPEN_BOX();
?>
<div id="addRow_table">
<table cellspacing="1" border="0">
<tbody id="detail_isi">
<?php echo "<b>".$_SESSION['lang']['notransaksi']."</b> : <input type=\"text\" id='detail_kode' name='detail_kode' disabled=\"disabled\" style=\"width:150px\" />";?>
<table id="ppDetailTable" >
</table>
</tbody>
<tr><td>
<div  id="tmblDetail">
</div>
</td></tr>
</table>
</div>
<?php
CLOSE_BOX();
?>
</div>
<?php 
echo close_body();
?>