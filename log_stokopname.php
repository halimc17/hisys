<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script>
 jdl_ats_0='<?php echo $_SESSION['lang']['find']?>';
// alert(jdl_ats_0);
 jdl_ats_1='<?php echo $_SESSION['lang']['findBrg']?>';
 content_0='<fieldset><legend><?php echo $_SESSION['lang']['findnoBrg']?></legend>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div>';

nmSaveHeader='';
nmCancelHeader='';
nmDetialDone='<?php echo $_SESSION['lang']['done']?>';
nmDetailCancel='<?php echo $_SESSION['lang']['cancel']?>';

</script>
<script type="application/javascript" src="js/log_stokopname.js?ver=5.5"></script>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="headher">
<?php
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optgdg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optklbrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi
    where tipe='PT'
    order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){		
    $optpt.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}

OPEN_BOX('','<span class=judul>Stok Opname</span>');
?>
<fieldset style='width: 280px;'>
<legend><?php echo $_SESSION['lang']['form']?></legend>
<table cellspacing="1" border="0">

<tr>
<td><?php echo $_SESSION['lang']['pt']?></td>
<td>:</td>
<td><select id="pt" name="pt" style="width:150px" onchange="getkebun()"><? echo $optpt;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['unit']?></td>
<td>:</td>
<td><select id="unit" name="unit" style="width:150px" onchange="getgudang()"><? echo $optunit;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['gudang']?></td>
<td>:</td>
<td><select id="gudang" name="gudang" style="width:150px" onchange="getperiode()" ><? echo $optgdg;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['periode']?></td>
<td>:</td>
<td><select id="periode" name="periode" style="width:150px" onchange="getklbrg()"><? echo $optperiode;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['kelompokbarang']?></td>
<td>:</td>
<td><select id="klbrg" style="width:150px" ><? echo $optklbrg;?></select></td>
</tr>



<tr>
<td><td><td id="tmblHeader">
    <button class=mybutton id=dtlFormAtas onclick=getForm()>Preview</button>
   
</td></td></td>
</tr>
</table>
</fieldset>
    <div id="formInputan" style="display: none;">
        <fieldset>
            <legend><? echo $_SESSION['lang']['list'] ?></legend>
            <div id="formTampil">
                
            </div>
        </fieldset>
    </div>
  
    </div>
<?php
CLOSE_BOX();
?>

<div id="list_ganti">
<?php OPEN_BOX()?>
    <div id="action_list">

</div>
<!--     <?php
echo"<table>
     <tr valign=moiddle>
	 
	 <td><img class=delliconBig src=images/application/application_view_list.png title='".$_SESSION['lang']['list']."' style='width:55px;cursor:pointer;' onclick=displayList()></td><td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['notransaksi']." : <input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>";
			echo $_SESSION['lang']['NoKontrak']." : <input type=text id=txtsearchKntrk size=25 maxlength=30 class=myinputtext>";
			echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
			echo"<button class=mybutton onclick=cariTransaksi()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
	 </table> "; 
?> -->
<fieldset style='float:left;'>
<legend><?php echo $_SESSION['lang']['list']?></legend>

<div id="contain">
<script>loadNData()</script>

</div>
</fieldset>
<?php CLOSE_BOX()?>
</div>

<?php 
echo close_body();
?>