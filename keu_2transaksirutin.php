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
<script languange=javascript1.2 src='js/keu_2transaksirutin.js'></script>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('keu_2transaksirutin').'</span>');

$opt_unit=$opt_pt=$opt_per=$opt_perakhir="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$s_pt="select * from ".$dbname.".organisasi where tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' order by kodeorganisasi asc";
$res=$owlPDO->query($s_pt) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($r_pt=$res->fetch())
{
    $opt_pt.="<option value='".$r_pt['kodeorganisasi']."'>".$r_pt['namaorganisasi']."</option>";
}

$optper="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode asc";
$res=$owlPDO->query($optper) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rper=$res->fetch())
{
    $opt_per.="<option value='".$rper['periode']."'>".$rper['periode']."</option>";
}

$optper="select distinct(left(tanggalselesai,7)) as periode from ".$dbname.".keu_transaksi_rutin order by left(tanggalselesai,7) asc";
$res=$owlPDO->query($optper) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rper=$res->fetch())
{
    $opt_perakhir.="<option value='".$rper['periode']."'>".$rper['periode']."</option>";
}

#Tipe Transaksi
$opttipewkt=$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrtipe=getEnum($dbname,'keu_transaksi_rutin','tipe_transaksi');
foreach($arrtipe as $kei=>$fal)
{
    $opttipe.="<option value='".$kei."'>".$fal."</option>";
}

#Tipe waktu
$arrtipe=getEnum($dbname,'keu_transaksi_rutin','tipewaktu');
foreach($arrtipe as $kei=>$fal)
{
    $opttipewkt.="<option value='".$kei."'>".$fal."</option>";
}

$array = "##pt##unit##periode1##periode2##tipe##tipewkt";
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
        <td colspan="4"><select id='unit' style="width:200px;"><?php echo $opt_unit?></select></td>
    </tr>
    <tr>
        <td><?php echo $_SESSION['lang']['periode']?></td><td>:</td>
        <td><select id='periode1' style="width:85px;" ><?php echo $opt_per?></select>
        <?php echo $_SESSION['lang']['sd']?>
        <select id='periode2' style="width:85px;" ><?php echo $opt_perakhir?></select></td>
    </tr>
    <tr>
        <td><?php echo $_SESSION['lang']['tipe']?>  Transaksi</td><td>:</td>
        <td colspan="4">
            <select id='tipe' style="width:200px;">
            <?php echo $opttipe?>
            </select></td>
    </tr>
     <tr>
        <td><?php echo $_SESSION['lang']['tipe']?>  Waktu</td><td>:</td>
        <td colspan="4">
            <select id='tipewkt' style="width:200px;">
            <?php echo $opttipewkt?>
            </select></td>
    </tr>
    <td id="tombol" align="center"><td><td>
        <?php 
        echo "<button onclick=\"zPreview('keu_slave_2transaksirutin','".$array."','reportcontainer')\" 
         class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
        <button onclick=\"zExcel(event,'keu_slave_2transaksirutin.php','".$array."','reportcontainer')\" 
         class=\"mybutton\" name=\"excel\" id=\"excel\">".$_SESSION['lang']['excel']."</button>"; 
        ?>
    </td>
    </tr>
</table>
</fieldset>
</div>
<?php CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset><legend>Transaksi Berulang</legend>
                 <div id='reportcontainer' style='width:100%;height:400px;overflow:auto;'></div> 
                 </fieldset>"; 
CLOSE_BOX();
?>