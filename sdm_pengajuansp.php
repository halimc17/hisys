<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zLib.php');
?>
<script language=javascript src='js/sdm_pengajuansp.js'></script>
<?php

//get optorg
$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
if (substr($_SESSION['empl']['lokasitugas'], 2, 2) == 'HO') {
	$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
}else{
	$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

//get jenis surat peringatan
$optsp = '';
$str = "select * from " . $dbname . ".sdm_5jenissp  where not kode='BAPP' order by kode";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while ($bar = $res->fetch()) {
    $optsp.="<option value='" . $bar->kode . "'>" . $bar->keterangan . "</option>";
}

#Sifat Pelanggaran
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrtipe=getEnum($dbname,'sdm_pengajuanspht','sifatpelanggaran');
foreach($arrtipe as $kei=>$fal)
{
    $opttipe.="<option value='".$fal."'>".$fal."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_pengajuansp').'</span>');
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
			 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
			 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
			 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
	    echo "<table border=0><tr><td>".$_SESSION['lang']['nopengajuan']."</td><td>:</td><td><input type=text id='nopengajuancr' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>";
		echo "<td>".$_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class='myinputtext' id='tglcr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /></td>";
	    echo"<td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";
	    echo"</table>";
	    echo"</fieldset></td>
	 </tr>
	 </table>";

CLOSE_BOX();
?>
<!--Form Add Data-->
<div id="header" style="display:none">
    <?php OPEN_BOX() ?>
    <fieldset style="float:left">
        <legend><?php echo $_SESSION['lang']['header'] ?></legend>
        <table border="0" cellspacing="0">
			<tr>
			    <td><?echo $_SESSION['lang']['nopengajuan'];?></td>
			    <td> : </td>
			    <td><input type="text" class=myinputtext id="nopengajuan"  style="width:177px" maxlength=15 disabled></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td><?echo $_SESSION['lang']['lokasitugas'];?></td>
			    <td> : </td>
			    <td><select style="width:180px"  id="kodeorg" onchange="getkar(0,0)"  ><?echo $optorg;?></select></td>
			</tr>

	  		<tr>
	  			<td><?echo $_SESSION['lang']['tanggalpengajuan'];?></td>
			    <td> : </td>
				<td><input type="text" style="width:177px" value="<? echo date("d-m-Y");?>" class=myinputtext id="tglpengajuan" readonly size="12" maxlength="10" ></td>
			    <td>&nbsp;&nbsp;&nbsp;</td>
	  			<td><?echo "Dikeluarkan oleh";?></td>
			    <td> : </td>
			    <td><select style="width:180px" onchange="getkar(0,0)" id="pembuat" ></select></td>
			</tr>
			<tr>
	  			<td><?echo $_SESSION['lang']['jenis']." ".$_SESSION['lang']['surat'];?></td>
			    <td> : </td>   
                <td><select id="jenissurat" style="width:180px" onchange='tgldis()'><?php echo $optsp ?></select></td>
                <td>&nbsp;&nbsp;&nbsp;</td>
	  			<td><?echo $_SESSION['lang']['nama']." ".$_SESSION['lang']['karyawan'];?></td>
			    <td> : </td>
			    <td><select style="width:180px"  id="karyawan" ></select></td>
			</tr>
			<tr>
	  			<td><?echo $_SESSION['lang']['sifatpelanggaran'];?></td>
			    <td> : </td>   
                <td><select style="width:180px"  id="sifatpelanggaran" ><?php echo $opttipe ?></select></td>
                <td>&nbsp;&nbsp;&nbsp;</td>
				<td><?echo $_SESSION['lang']['tanggal']." Skorsing";?></td>
			    <td> : </td>
				<td><input type="text" style="width:75px" value=""  onmousemove="setCalendar(this.id)" onkeypress="return false;"  class=myinputtext id="tanggaldari" readonly size="12" maxlength="10" placeholder="Tanggal Dari"> s/d
					<input type="text" style="width:75px" value=""  onmousemove="setCalendar(this.id)" onkeypress="return false;"  class=myinputtext id="tanggalsampai" readonly size="12" maxlength="10" placeholder="Tanggal Sampai"></td>
			</tr>
			<tr>
	            <td colspan=2></td>
	            <td><button id=tomboldetail class=mybutton onclick=savepengajuan()><?php echo $_SESSION['lang']['save'] ?></button>
			</tr>
		</table>
	</fieldset>
	<?php CLOSE_BOX() ?>
</div>

<div id="detail" style='display:none'>
	
</div>
<?php CLOSE_BOX() ?>
<div id="persetujuan" style='display:none'>	
	
</div>

<div id="listdata" style='display:block'>
<?
OPEN_BOX();
echo"<fieldset>
		<legend><b>".$_SESSION['lang']['list']."</legend>
		    <table class=sortable cellspacing=1 cellspacing=1 border=0>
            <thead>
            <tr class=rowheader>    
                <td align=center>".$_SESSION['lang']['nourut']."</td>
           		<td align=center>" . $_SESSION['lang']['nopengajuan'] . "</td>
           		<td align=center>" . $_SESSION['lang']['tanggalpengajuan'] . "</td>
           		<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
                <td align=center>" . $_SESSION['lang']['dbuat_oleh'] . "</td>
                <td align=center>" . $_SESSION['lang']['status'] . "</td>
                <td align=center>" . $_SESSION['lang']['action'] . "</td>
        	</thead>
        	<tbody  id=container>";

	echo"<tfoot id='footData'>
          </tfoot></tbody></table></fieldset>";
CLOSE_BOX();
echo "</div>";
echo close_body('');
?><script type="text/javascript">loadData(0);</script>