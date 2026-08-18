<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/zMaster.js'></script> 
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/keu_2debitNote.js'></script>
<?php
OPEN_BOX('','<span class=judul>Laporan Debet Nota</span>');

$opt_kepada=$opt_unit=$opt_pt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$s_pt="select * from ".$dbname.".organisasi where tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' order by kodeorganisasi asc";
$res=$owlPDO->query($s_pt) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($r_pt=$res->fetch())
{
    $opt_pt.="<option value='".$r_pt['kodeorganisasi']."'>".$r_pt['namaorganisasi']."</option>";
}

$array = "##pt##unit##tanggal##sd";
?>
<div>
<fieldset style='float:left;'>
<legend><?php echo $_SESSION['lang']['form']?></legend>
<table cellspacing="1" border="0">
    <tr>
        <td style="width:100px"><?php echo $_SESSION['lang']['namapt']?></td><td>:</td>
        <td colspan="4"><select id='pt' style="width:200px;" onchange="load_unit_kpd()"><?php echo $opt_pt?></select></td>
    </tr>
    <tr>
        <td><?php echo $_SESSION['lang']['unitkerja']?></td><td>:</td>
        <td colspan="4"><select id='unit' style="width:200px;" ><?php echo $opt_unit?></select></td>
    </tr>
    <tr>
        <td><?php echo $_SESSION['lang']['tanggal']?></td><td>:</td>
        <td><input type='text' class='myinputtext' id='tanggal' name='tanggal' onmousemove='setCalendar(this.id);' 
             onkeypress='return false;'  maxlength=10 style='width:82px;' readonly/>
        <?php echo $_SESSION['lang']['sd']?>
        <input type='text' class='myinputtext' id='sd' name='sd' onmousemove='setCalendar(this.id);' 
             onkeypress='return false;'  maxlength=10 style='width:82px;' readonly/></td>
    </tr>
   
    <td id="tombol" align="center"><td><td>
        <?php 
        echo "<button onclick=\"zPreview('keu_slave_2debitnota','".$array."','reportcontainer')\" 
         class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
        <button onclick=\"zExcel(event,'keu_slave_2debitnota.php','".$array."','reportcontainer')\" 
         class=\"mybutton\" name=\"excel\" id=\"excel\">".$_SESSION['lang']['excel']."</button>"; 
        ?>
    </td>
    </tr>
</table>
</fieldset>
</div>
<?php CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset><legend>Debet Nota</legend>
                 <div id='reportcontainer' style='width:100%;height:400px;overflow:auto;'></div> 
                 </fieldset>"; 
CLOSE_BOX();
?>