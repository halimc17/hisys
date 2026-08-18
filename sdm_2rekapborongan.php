<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
$frm[0]='';

?>
<script>
pilh=" <? echo $_SESSION['lang']['pilihdata'] ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script type="text/javascript" src="js/sdm_2rekapborong.js?v=<? echo time();?>" /></script>

<script>
dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";
function Clear1(){
    document.getElementById('thnBudget').value='';
    document.getElementById('kdUnit').value='';
    document.getElementById('printContainer').innerHTML='';
}

</script>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optOrg2=$optOrg;
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' order by namaorganisasi asc";
    $sOrg2="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' order by namaorganisasi asc";
}else{
    $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc";
    $sOrg2="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc";
}
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
    $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}
$qOrg=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
        $optOrg2.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}
 $optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


$sper="select distinct substr(b.tanggal,1,7) as tanggalinput from ".$dbname.".kebun_kehadiran_vw a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi like '%bor%' and b.tanggalinput !='0000-00-00' order by b.tanggalinput desc";
$qper=$owlPDO->query($sper) or die(print " Gagal: ".PDOException::getMessage());
$qper->setFetchMode(PDO::FETCH_ASSOC);
while($rper=$qper->fetch()){
        //$optper.="<option value=".$rper['tanggalinput'].">".$rper['tanggalinput']."</option>";
}

for($x=-2;$x<24;$x++){
	$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
	@$optper.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}

$optmndr="<option value=''>".$_SESSION['lang']['all']."</option>";
$smndr="select distinct noreferensi from ".$dbname.".kebun_kehadiran_vw where notransaksi like '%bor%' and noreferensi !='' order by noreferensi";
$qmndr=$owlPDO->query($smndr) or die(print " Gagal: ".PDOException::getMessage());
$qmndr->setFetchMode(PDO::FETCH_ASSOC);
while($rmndr=$qmndr->fetch()){
        $optmndr.="<option value=".$rmndr['noreferensi'].">".strtoupper($rmndr['noreferensi'])."</option>";
}

$arrjns=array('posting'=>'Berdasarkan Tanggal Posting','input'=>'Berdasarkan Tanggal Input');
foreach($arrjns as $key => $val){
	@$optjns.="<option value=".$key.">".$val."</option>";
}


$arr="##periode##kdUnit##kebun##mandor##jenis";
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2rekapborongan').'</span><br>');

echo"<fieldset style=\"float: left;\"><legend>".$_SESSION['lang']['form']."</legend>";
echo"<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['kebun']."</td><td>:</td><td><select id='kebun'  style=\"width:175px;\" onchange=\"getdivisi()\">".$optOrg."</select></td></tr>

<tr><td>".$_SESSION['lang']['divisi']."</td><td>:</td><td><select id='kdUnit'  style=\"width:175px;\" ></select></td></tr>
<tr><td>".$_SESSION['lang']['mandor']."</td><td>:</td><td><select id='mandor'  style=\"width:175px;\" >".$optmndr."</select></td></tr>
<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td>
<select id='periode' style='width:175px;'>".$optper."</select></td></tr>

<tr><td>".$_SESSION['lang']['jenis']."</td><td>:</td><td>
<select id='jenis' style='width:175px;'>".$optjns."</select></td></tr>


<tr><td></td><td></td><td>
<button onclick=\"zPreview('sdm_slave_2rekapborong','".$arr."','printContainer')\" class=\"mybutton\" >Preview</button>
   
    <button onclick=\"zExcel(event,'sdm_slave_2rekapborong.php','".$arr."')\" class=\"mybutton\" >Excel</button></td></tr></table>
";

CLOSE_BOX();
OPEN_BOX();
echo "<div style=clear:both></div>
		<div id='both_report'>
			<div id='head_tableboth' align=right>
				<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
					<img title='Full Screen' class='resicon' src='images/full-screen.png'>
				</a>
				<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
					<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
				</a>
			</div>
		<div id='printContainer' style='overflow:auto;height:450px;max-width:100%'; >
		</div></div>";
CLOSE_BOX();
echo close_body();

?>
<?php
CLOSE_BOX();
echo"</div>";
echo close_body();
?>