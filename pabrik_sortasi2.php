<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script>
tmblPilih='<?php echo $_SESSION['lang']['proses']?>';
canForm='<?php echo $_SESSION['lang']['done']?>';
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/pabrik_sortasi2.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<div id="action_list">
<?php
        $optFraksi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sFraksi="select * from ".$dbname.".pabrik_5fraksi order by kode";
        $qFraksi=$owlPDO->query($sFraksi) or die(print " Gagal: ".PDOException::getMessage());
        $qFraksi->setFetchMode(PDO::FETCH_ASSOC);
        while($rFraksi=$qFraksi->fetch()){
                $optFraksi.="<option value=".$rFraksi['kode'].">".$rFraksi['keterangan']."</option>";
        }
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['sortasi']).'</span>');

echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
           <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
           <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>";
echo "<div id=kodePt value='".$_SESSION['org']['kodeorganisasi']."'></div>";
echo $_SESSION['lang']['noTiket'].":<input type=text id=noTiketcr name=noTiketcr class=myinputtext onkeypress=return(tanpa_kutip)  style=width:150px; />&nbsp;";
//echo $_SESSION['lang']['tanggal'].":<input type=\"text\" class=\"myinputtext\" id=\"tglCari\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false; \" size=\"10\" maxlength=\"4\" style=\"width:150px;\" />";
echo"<button class=mybutton onclick=cariTiket()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>

         </tr>
         </table> "; 
?>
</div>
<?php
CLOSE_BOX();
?>
<div id="listData">
<?php OPEN_BOX('','')?>
<fieldset>
<legend><?php echo $_SESSION['lang']['list']?></legend>
<div id="contain">
<script>loadData()</script>
</div>
</fieldset>

<?php CLOSE_BOX()?>
</div>



<div id="headher" style="display:none">
<?php
OPEN_BOX();
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $sorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
       where tipe='PABRIK' order by namaorganisasi asc";
}else{
    $sorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
           where tipe='PABRIK' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' 
           order by namaorganisasi asc";
}
$optOrg="";
$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
$qorg->setFetchMode(PDO::FETCH_ASSOC);
while($rorg=$qorg->fetch())
{    
    
    $optOrg.="<option value='".$rorg['kodeorganisasi']."'>".$rorg['namaorganisasi']."</option>";
}

$optFraksi = makeOption($dbname,'pabrik_5fraksi2','kode,type');

$optPotFraksi = makeOption($dbname,'pabrik_5pot_fraksi','kodefraksi,potongan');
?>
<fieldset>
<legend><?php echo $_SESSION['lang']['entryForm']?></legend>
<input type='hidden' id='fraksi' value='<?php echo json_encode($optFraksi)?>'>
<input type='hidden' id='potFraksi' value='<?php echo json_encode($optPotFraksi)?>'>
<div id="pilih">
<table cellspacing="1" border="0">
<tr>
<td><?php echo $_SESSION['lang']['unit']?></td>
<td>:</td>
<td>
<select id="unitdt" style="width:150px;"><?php echo $optOrg?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['pilihTanggal']?></td>
<td>:</td>
<td>
<input type="text" class="myinputtext" id="tgl" onmousemove="setCalendar(this.id);" onkeypress="return false; " size="10" maxlength="4" style="width:145px;" /></td>
</tr>
<tr>
<td ><td><td><div id="tmblPilih"><button class="mybutton" id="dtlAbn" onclick="addData(0,0)"><?php echo $_SESSION['lang']['save']?></button></div></td>
</tr>
</table>
</div>
<br />

<div id="formInput" style="display:none">
<?php 
echo $_SESSION['lang']['noTiket'];
echo $_SESSION['lang']['cancel']
?>
</div>
<input type="hidden" id="proses" name="proses" value="insert"  />
<br />
<div id="showFormBwh" style="display:none;">
    <fieldset style=width:1000px>
        <legend><?php echo $_SESSION['lang']['detail']?></legend>
        <div id="formDetail" style=" width:auto;"></div>
    </fieldset>
    <br />
    <fieldset style=width:1000px>
        <legend><?php echo $_SESSION['lang']['data'];?> : <span id="tanggalForm"></span></legend>
        <div id="isiDetail" style="height:350px;overflow:scroll;">

        </div>
    </fieldset>
</div>

</fieldset>

<?php
CLOSE_BOX();
?>
</div>
<?php 
echo close_body();

?>