<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
	<link rel=stylesheet type=text/css href="style/zTable.css">
	<script language="javascript" src="js/zMaster.js"></script>
	<script language=javascript src='js/keu_5mesinlaporan.js?v=<?php echo time(); ?>'></script>
<?php
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where tipe='HOLDING' and induk=''";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_5mesinlaporan').'</span>');
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
             <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
               <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
             <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
               <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
             <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
        echo "<table border=0><tr><td>".$_SESSION['lang']['namalaporan']."</td><td>:</td><td><input type=text id='namalaporan' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>";
        echo"<td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";
        echo"</table>";
        echo"</fieldset></td>
     </tr>
     </table>";

CLOSE_BOX();
?>
<!--Form Add Data-->
<?php OPEN_BOX() ?>
<!--  style="float:left;width:400px;" -->
<div id="header" style='display:none'>
    
    <fieldset>
        <legend><?php echo $_SESSION['lang']['header'] ?></legend>
        <table border="0" cellspacing="0">
            <tr>
                <td><?php echo $_SESSION['lang']['ho']?></td>
                <td>:</td>
                <td><select id="kodeorg" style='width:200px;'><?php echo $optunit; ?></select> </td>
				
				  <td><?php echo $_SESSION['lang']['kodelaporan']?></td>
                <td>:</td>
                <td><input type="text" id="nmLaporan" class="myinputtext" style='width:195px;' /></td>
				
				  <td><?php echo $_SESSION['lang']['namalaporan']?></td>
                <td>:</td>
                <td><input type="text" id="ketDt1" class="myinputtext" style='width:195px;' /></td>
            </tr>
           
            <!-- <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 2"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt2" class="myinputtext" style='width:250px;' /></td>
            </tr>   
            <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 3"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt3" class="myinputtext" style='width:250px;' /></td>
            </tr> 
            <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 4"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt4" class="myinputtext" style='width:250px;' /></td>
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 5"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt5" class="myinputtext" style='width:250px;' /></td>
            </tr>  
            <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 6"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt6" class="myinputtext" style='width:250px;' /></td>
            </tr>   -->
            <tr>
                <td><td><td>
                <input type="hidden" id="method" value="insertht" />
                <button class=mybutton id="dtl_ajuan" onclick="saveData()"><?php echo $_SESSION['lang']['save']?></button></td>
            </tr>
        </table>
    </fieldset>
    
</div>
<div style='clear:both'></div>
<div id="detail" style='display:none'>
    
</div>
<?php CLOSE_BOX() ?>


<div id=listdata style='display:block'>
<?
OPEN_BOX();
echo"
            <table class=sortable cellpadding=5 cellspacing=1 border=0>
            <thead>
            <tr class=rowheader>    
                <th align=center>" . $_SESSION['lang']['nomor'] . "</th>
                <th align=center>" . $_SESSION['lang']['kodelaporan'] . "</th>
                <th align=center>" . $_SESSION['lang']['namalaporan'] . "</th>
                <th>" . $_SESSION['lang']['dbuat_oleh'] . "</th>
                <th>" . $_SESSION['lang']['perubahan'] . "</th>
                <th align=center colspan=2>" . $_SESSION['lang']['action'] . "</th>
            </thead>
            <tbody  id=contain>";

    echo"<tfoot id='footData'>
          </tfoot></tbody></table>";
CLOSE_BOX();
echo "</div>";
echo close_body('');
?><script type="text/javascript">loadData(0);</script>