<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_pengajuan_formcapex.js" /></script>


<?php

$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
if (substr($_SESSION['empl']['lokasitugas'], 2, 2) == 'HO') {
	$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
}else{
	$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('log_pengajuan_formcapex').'</span>');
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
			 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
			 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			   <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
			 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
	    echo "<table border=0><tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td><td><input type=text id='notranscr' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>";
		echo "<td>".$_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class='myinputtext' id='tglcr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /></td>";
	    echo"<td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";
	    echo"</table>";
	    echo"</fieldset></td>
	 </tr>
	 </table>";

CLOSE_BOX();
?>
<!--Form Add Data-->
<div id="header" style='display:none'>
    <?php OPEN_BOX() ?>
    <fieldset style="float:left">
        <legend><?php echo $_SESSION['lang']['header'] ?></legend>
        <table border="0" cellspacing="0">
			<tr>
				<td><?php echo $_SESSION['lang']['unit']?></td>
				<td>:</td>
			  	<td><select id="unit" style='width:150px;'><?php echo $optunit; ?></select>	</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['notransaksi']?></td>
				<td>:</td>
				<td><input type="text" id="notrans" class="myinputtext" disabled="disabled" style='width:145px;' /></td>
			</tr>		
			<tr>
				<td><?php echo $_SESSION['lang']['tanggal']?></td>
				<td>:</td>
				<td><input type="text" class="myinputtext" id="tgltrans" name="tgltrans" value="<?php echo date("d-m-Y");?>" readonly="readonly" style='width:145px;' /></td>
			</tr>
			<tr>
				<td><td><td>
				<input type="hidden" id="method" value="insertht" />
				<button class=mybutton id="dtl_ajuan" onclick="get_notrans()"><?php echo $_SESSION['lang']['save']?></button></td>
			</tr>
		</table>
	</fieldset>
    <?php CLOSE_BOX() ?>
</div>

<div id="detail" style='display:none'>
	
</div>

<div id="persetujuan" style='display:none'>
	
</div>

<div id=listdata style='display:block'>
<?
OPEN_BOX();
echo"<fieldset>
		<legend><b>".$_SESSION['lang']['list']."</legend>
		    <table class=sortable cellspacing=1 cellspacing=1 border=0>
            <thead>
            <tr class=rowheader>    
                <td align=center>".$_SESSION['lang']['nourut']."</td>
           		<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
           		<td align=center>" . $_SESSION['lang']['tanggalpengajuan'] . "</td>
           		<td align=center>" . $_SESSION['lang']['namaorganisasi'] . "</td>
                <td align=center>" . $_SESSION['lang']['dbuat_oleh'] . "</td>
                <td align=center>" . $_SESSION['lang']['status'] . "</td>
                <td align=center>" . $_SESSION['lang']['action'] . "</td>
        	</thead>
        	<tbody  id=contain>";

	echo"<tfoot id='footData'>
          </tfoot></tbody></table></fieldset>";
CLOSE_BOX();
echo "</div>";
echo close_body('');
?><script type="text/javascript">loadData(0);</script>