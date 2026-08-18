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
<script language=javascript src='js/pabrik_timbangan_pembeli.js?v=<?php echo time(); ?>'></script>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="headher">
<?php
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}
$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optKary.="<option value=".$rOrg['karyawanid'].">".$rOrg['namakaryawan']."</option>";
}
$optBrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sBrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang like '4%'";
$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while($rBrg=$qBrg->fetch())
{
    $optBrg.="<option value='".$rBrg['kodebarang']."'>".$rBrg['namabarang']."</option>";
}  
$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optnodo="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_timbangan_pembeli').'</span><br>');
?>
<fieldset style='width: 280px;'>
<legend><?php echo $_SESSION['lang']['form']?></legend>
<table cellspacing="1" border="0">

<tr>
<td><?php echo $_SESSION['lang']['namabarang']?></td>
<td>:</td>
<td><select id="kdBrg" name="kdBrg" style="width:150px" onchange="getCustomer(0,0,0)"><? echo $optBrg;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['nmcust']?></td>
<td>:</td>
<td><select id="custId" name="custId" style="width:150px" onchange="getKontrak(0,0)"><? echo $optJenis;?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['NoKontrak']?></td>
<td>:</td>
<td><select id="noKontrak" style="width:150px" onchange="getnodo()"><? echo $optJenis;?></select></td>
</tr>
<?php
echo"
<tr>
	<td>".$_SESSION['lang']['nodo']."</td>
	<td>:</td>
	<td>
	<select id='nodo' name='nodo'  style='width:150px;'>".$optnodo."</select>
	</td>
</tr>
";
?>

<tr>
<td><td><td id="tmblHeader">
    <button class=mybutton id=dtlFormAtas onclick=getForm()>Preview</button>
   
</td></td></td>
</tr>
</table>
</fieldset>
    <div id="formInputan" style="display: none;">
        <fieldset style='float:left'>
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
    <?php
echo"<table>
     <tr valign=moiddle>
	 
	 <td><img class=delliconBig src=images/application/application_view_list.png title='".$_SESSION['lang']['list']."' style='width:55px;cursor:pointer;' onclick=displayList()></td><td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['notransaksi']." : <input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>";
			echo $_SESSION['lang']['NoKontrak']." : <input type=text id=txtsearchKntrk size=25 maxlength=30 class=myinputtext>";
			echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>";
			echo"<button class=mybutton onclick=loadNData()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
	 </table> "; 
?>

<div id="contain">
	<script>loadNData()</script>
</div>

<?php CLOSE_BOX()?>
</div>

<?php 
echo close_body();
?>