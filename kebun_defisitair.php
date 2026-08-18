<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_defisitair').'</span>');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/kebun_defisitair.js?v=<?php echo time(); ?>"></script>
<script language="javascript">

$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});

</script>
<div id="action_list">
    <?php
    $optPeriode = "";
    for ($x = 0; $x <= 24; $x++) {
        $dt = mktime(0, 0, 0, date('m') - $x, 15, date('Y'));
        $optPeriode.="<option value=" . date("Y-m", $dt) . ">" . date("Y-m", $dt) . "</option>";
    }
    $lokasi = $_SESSION['empl']['lokasitugas'];
    $sql = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where (tipe='AFDELING' or tipe='BIBITAN') and induk='" . $lokasi . "'";
    $query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $optOrg = "";
    while ($res = $query->fetch()) {
        $optOrg.="<option value=" . $res['kodeorganisasi'] . ">" . $res['namaorganisasi'] . "</option>";
    }
    echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/newfile.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
    echo $_SESSION['lang']['kodeorg'] . " : <select class=select2 id=unitOrg name=unitOrg><option value=''></option>" . $optOrg . "</select>&nbsp;";
    // echo $_SESSION['lang']['periode'] . " : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>";
    echo"<button class=mybutton onclick=cariCurah()>" . $_SESSION['lang']['find'] . "</button>";
    echo"</fieldset></td>
	
	 </tr>
	 </table> ";
    ?>
</div>
    <?php
    CLOSE_BOX();
    ?>
<div id="listData">
<?php OPEN_BOX() ?>
    
        <table cellspacing="1" cellpadding=5 border="0" class="sortable">
            <thead>
                <tr class="rowheader">
                    <th align='center'>No.</th>
                    <th align='center'><?php echo $_SESSION['lang']['kebun'] ?></th>
                    <th align='center'><?php echo $_SESSION['lang']['periode']; ?></th> 
                    <th align='center'>MM</th>
                    <th hidden align='center'><?php echo $_SESSION['lang']['siang']; ?></th>
                    <th hidden align='center'><?php echo $_SESSION['lang']['malam']; ?></th>	 
                    <th hidden align='center' style='display:none'><?php echo $_SESSION['lang']['malam']; ?></th>	 
                    <th align='center'><?php echo $_SESSION['lang']['note']; ?></th>
                    <th align='center' colspan=2>Action</th>
                </tr>
            </thead>
            <tbody id="contain">
<?php
$limit = 20;
$page = 0;
if (isset($_POST['page'])) {
    $page = $_POST['page'];
    if ($page < 0)
        $page = 0;
}
$offset = $page * $limit;
$maxdisplay=($page*$limit);
$no = 0;
$no=$maxdisplay;

$ql2 = "select count(*) as jmlhrow from " . $dbname . ".kebun_defisitair where `kodeorg` like '" . $lokasi . "%'  order by `periode` desc";
$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
$query2->setFetchMode(PDO::FETCH_OBJ);
while ($jsl = $query2->fetch()) {
    $jlhbrs = $jsl->jmlhrow;
}


$str = "select * from " . $dbname . ".kebun_defisitair where `kodeorg` like  '" . $lokasi . "%' order by periode desc  limit " . $offset . "," . $limit . "";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

while ($bar = $res->fetch()) {
	$spr = "select namaorganisasi from  " . $dbname . ".organisasi where  kodeorganisasi='" . $bar->kodeorg . "'";
	$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
	$rep->setFetchMode(PDO::FETCH_OBJ);
	$bas = $rep->fetch();
	$no+=1;

	//echo $minute_selesai; exit();
	echo"<tr class=rowcontent id='tr_" . $no . "'>
	<td align=center>" . $no . "</td>
	<td id='nmorg_" . $no . "'>" . $bas->namaorganisasi . "</td>
	<td id='kpsits_" . $no . "'>" . $bar->periode . "</td>
	<td align=right id='strt_" . $no . "'>" . $bar->mm . "</td>
	<td hidden align=right id='siang_" . $no . "'>" . $bar->siang . "</td>
	<td hidden align=right id='end_" . $no . "'>" . $bar->sore . "</td>
	<td hidden align=right id='mlm_" . $no . "' style='display:none'>" . $bar->malam . "</td>
	<td id='tglex_" . $no . "'>" . $bar->catatan . "</td>
	<td align=center width=30px><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->kodeorg . "','" . $bar->periode . "');\">
	</td>
	<td align=center width=30px>
	<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('" . $bar->kodeorg . "','" . $bar->periode . "');\">
	</td>
	<td hidden align=center width=30px>
	<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"printPDF('" . $bar->kodeorg . "','" . $bar->periode . "',event);\"></td>
	</tr>";
}
echo"
	<tr><td colspan=9 align=center>
	" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
	<button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
	<button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
	</td>
	</tr>"; 
?>
                <?php
                ?>

            </tbody>
        </table>
    

                <?php CLOSE_BOX() ?>
</div>



<div id="headher" style="display:none">
<?php
OPEN_BOX();
$optPrd = "";
for ($x = 0; $x <= 12; $x++) {
    $dte = mktime(0, 0, 0, date('m') - $x, 15, date('Y'));
    $optPrd.="<option value=" . date("Y-m", $dte) . ">" . date("Y-m", $dte) . "</option>";
}
?>
    <fieldset style="float:left">
        <legend><?php echo $_SESSION['lang']['entryForm'] ?></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td><?php echo $_SESSION['lang']['kodeorg'] ?></td>
                <td>:</td>
                <td>
                    <select class=select2 id="kodeOrg" name="kodeOrg" style="width:150px;" ><option value=""></option><?php echo $optOrg; ?></select>
                    <!--<input type="text"  id="noSpb" name="noSpb" class="myinputtext" style="width:120px;" disabled="disabled" />--></td>
					
					<td> MM </td>
                <td>:</td>
                <td>
                    <input type="text" class="myinputtextnumber" id="pg"  onkeypress="return angka_doang(event)" size="10" maxlength="10" value="0" style="width:50px;" />
                </td>
				
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['periode'] ?></td>
                <td>:</td>
                <td>
                    <!-- <input type="text" class="myinputtext" id="tgl" onmousemove="setCalendar(this.id)" onkeypress="return false;
                            " size="10" maxlength="10" style="width:145px;" readonly/> -->
                        <select class=select2 id="tgl" name="kodeOrg" style="width:150px;" ><option value=""></option><?php echo $optPeriode; ?></select>

                </td>
				
				<td style='display:none'><?php echo $_SESSION['lang']['siang'] ?>   (mm)</td>
                <td style='display:none'>:</td>
                <td style='display:none'>
                    <input type="text" class="myinputtextnumber" id="sg"  onkeypress="return angka_doang(event)" size="10" maxlength="10" value="0" style="width:50px;" /></td>
					
            </tr>

            <tr>
                <td><?php echo $_SESSION['lang']['note'] ?></td>
                <td>:</td>
                <td><input type="text" class="myinputtext" id="cttn" name="cttn" onkeypress="return tanpa_kutip(event)" style="width:145px;" maxlength="45" /></td>

                <td style='display:none'><?php echo $_SESSION['lang']['malam'] ?> (mm)</td>
                <td style='display:none'>:</td>
                <td style='display:none'>
                    <input type="text" class="myinputtextnumber" id="sr"  onkeypress="return angka_doang(event)" size="10" maxlength="10" value="0" style="width:50px;" /></td>
				

				<td style='display:none'><?php echo $_SESSION['lang']['malam'] ?> (mm)</td>
                <td style='display:none'>:</td>
                <td style='display:none'>
                    <input type="text" class="myinputtextnumber" id="ml"  onkeypress="return angka_doang(event)" size="10" maxlength="10" value="0" style="width:50px;" /></td>
					
            </tr>
            <tr>
                <td><td><td id="tmbLheader">
                    <button class="mybutton" id="dtlAbn" onclick="saveData()"><?php echo $_SESSION['lang']['save'] ?></button><button class="mybutton" id="cancelAbn" onclick="cancelSave()"><?php echo $_SESSION['lang']['cancel'] ?></button><input type="hidden" id="proses" name="proses" value="insert"  />
                </td></td></td>
            </tr>
        </table>
    </fieldset>

<?php
CLOSE_BOX();
?>
</div>
<?php
echo close_body();
?>