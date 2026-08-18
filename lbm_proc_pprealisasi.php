<?php

require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zMaster.js></script> 
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/lbm_main_procurement.js'></script>
<?php

$arr = "##periode##judul";
$judul = checkPostGet('judul','');
?>


<?php

$arrTipe = array("1" => "Capital", "2" => "Non Capital");
$optTipe = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach ($arrTipe as $lstTipe => $dtTipe) {
    $optTipe.="<option value='" . $lstTipe . "'>" . $dtTipe . "</option>";
}

$optperiode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg = "select distinct substr(tanggal,1,4) as tahun from  " . $dbname . ".log_poht  where tanggal!='0000-00-00'  and purchaser!='0000000000'
       order by substr(tanggal,1,4) desc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optperiode.="<option value=" . $rOrg['tahun'] . ">" . $rOrg['tahun'] . "</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper('PP Belum Realisasi / Pending PR').'</span>');

echo"<fieldset style=width:350px;><legend>Form</legend>
<table cellspacing=\"1\" border=\"0\" >
   <tr type=hidden><td colspan=2>" . $judul . "</td></tr>
    <tr><td><label>" . $_SESSION['lang']['tahun'] . "</label></td><td>:</td><td><select id='periode' style=\"width:150px;\">" . $optperiode . "</select></td></tr>";

echo"<tr><td colspan=\"2\"><input type=hidden id=judul name=judul value='" . $judul . "'></td></tr>
    <tr><td><td><td colspan=\"2\"> 
    <button onclick=\"zPreview('lbm_slave_proc_ppblmrealisasi','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
    <button onclick=\"zExcel(event,'lbm_slave_proc_ppblmrealisasi.php','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>    
   <!--<button onclick=\"zPdf('lbm_slave_proc_pupuk','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">" . $_SESSION['lang']['pdf'] . "</button>
    <button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">" . $_SESSION['lang']['cancel'] . "</button>--></td></tr>
</table></fieldset>
";


CLOSE_BOX();
OPEN_BOX('', '');
echo"<div id=mainPrint><fieldset><legend><span id=isiJdlBawah>Pending PR</span></legend>
                 <div id='both_report'>
                 <div id='head_tableboth' style='height:30px;'>
                 <a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='reportcontainer' table='sortable' style='float:right;margin-right:10px'>
                 <img title='Full Screen' class=resicon src='images/full-screen.png'>
                 </a>
                 <a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='reportcontainer' shown='0' style='float:right;margin-right:10px;' >
                 <img title='Fixed Header Table' class=resicon src=images/fix-header.gif>
                 </a>
                 </div>
                 <div id='reportcontainer' style='width:100%;height:400px;overflow:auto;'></div> 
                 </div> 
                 
                 </fieldset></div>";
echo"<div id=lyrPertama style=display:none;>
                 <fieldset><legend>Detail<span id=isiJdlBawah1></span></legend>
                 <div id='reportcontainer1' style='width:100%;height:400px;overflow:auto;'></div> 
                 </fieldset>
                 </div>";
echo"<div id=lyrKedua style=display:none;>
                 <fieldset><legend>Detail<span id=isiJdlBawah2></span></legend>
                 <div id='reportcontainer2' style='width:100%;height:400px;overflow:auto;'></div> 
                 </fieldset>
                 </div>";
CLOSE_BOX();
CLOSE_BOX();
close_body();
?>