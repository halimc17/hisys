<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_2penerimaan').'</span><br>');

require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src="js/zSelect2.js?ver=1.9"></script>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_2penerimaan.js"></script>
<div id="action_list">
    <?php
    $optGudang = "<option value=''>" . $_SESSION['lang']['pilihgudang'] . "</option>";
    // $optNma = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
    $unitDetailAkses = getOrgDetail(2);
    $sGudang = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi where tipe = 'GUDANG' and induk in (".$unitDetailAkses.") and kodeorganisasi in (select kodegudang  from " . $dbname . ".log_transaksiht) order by kodeorganisasi";
	$qGudang=$owlPDO->query($sGudang) or die(print " Gagal: ".PDOException::getMessage());
	$qGudang->setFetchMode(PDO::FETCH_ASSOC);
    while ($rGudang = $qGudang->fetch()) {
        $optGudang.="<option value=" . $rGudang['kodeorganisasi'] . ">" .$rGudang['namaorganisasi'] . "</option>";
    }
    echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=loadData()>
	   <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
    echo "<table cellpadding=1 cellspacing=1 border=0><tr><td>" . $_SESSION['lang']['pilihgudang'] . "</td><td>:</td><td><select class=select2 style=width:150px id=kdGdng>" . $optGudang . "</select></td>";
    echo "<td>" . $_SESSION['lang']['nopp'] . "</td><td>:</td><td><input placeholder='" . $_SESSION['lang']['input'] . " " . $_SESSION['lang']['nopp'] . " min 3 char' style=width:150px type=text id=txtsearch2 size=25 maxlength=30 class=myinputtext onkeypress='return tanpa_kutip(event)'></td>";
    echo "<td>" . $_SESSION['lang']['nopo'] . "</td><td>:</td><td><input placeholder='" . $_SESSION['lang']['input'] . " " . $_SESSION['lang']['nopo'] . "' style=width:150px type=text id=txtsearch size=25 maxlength=30 class=myinputtext onkeypress='return tanpa_kutip(event)'></td></tr><tr>";
    echo "<td>" . $_SESSION['lang']['namabarang'] . "</td><td>:</td><td><input placeholder='" . $_SESSION['lang']['input'] . " " . $_SESSION['lang']['namabarang'] . "' style=width:145px type=text class=myinputtext id=nmBrg onkeypress='return tanpa_kutip(event)' /></td>";
    echo "<td>" . $_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input placeholder='" . $_SESSION['lang']['input'] . " " . $_SESSION['lang']['tanggal'] . "' type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/></td><td><td></tr>";
    echo"<tr><td><td><td><button class=mybutton onclick=cariData()>" . $_SESSION['lang']['find'] . "</button></td></td></td></tr></table>";
    echo"</fieldset></td>
     </tr>
	 </table> ";
    ?>
</div>
<?php
CLOSE_BOX(); //1 C //2 O
?>
<div id=list_pp_verication>
    <?php OPEN_BOX(); ?>
        <div style="overflow:auto; height:450px;">
            <table class="sortable" cellpadding=5 cellspacing="1" border="0">
                <thead>
                    <tr class=rowheader>
                        <th align='center'>No.</th>
                        <th align='center'><?php echo $_SESSION['lang']['notransaksi']; ?></th>
                        <th align='center'><?php echo $_SESSION['lang']['tanggal']; ?></th> 
                        <th align='center'><?php echo $_SESSION['lang']['nopo'] ?></th>
                        <th align='center'><?php echo $_SESSION['lang']['namaorganisasi']; ?></th>
                        <th align='center'>Action</th>
                    </tr>
                </thead>
                <tbody id="contain">
                <script>loadData()</script>
                </tbody>
                <tfoot>
                </tfoot>
            </table></div>
    <?php
    CLOSE_BOX();
    ?>
</div>
<input type="hidden" name="method" id="method"  /> 
<input type="hidden" id="no_po" name="no_po" />
<input type="hidden" name="user_login" id="user_login" value="<?php echo $_SESSION['standard']['userid'] ?>" />

<?
echo close_body();
?>