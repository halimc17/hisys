<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
//for($x=0;$x<=3;$x++)
//{
//	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
//	$optPeriode.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
//}

$optPabrik="<option value=''>KSP AGRO (".$_SESSION['lang']['all'].")</option>";
// $optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOpt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
$qOpt->setFetchMode(PDO::FETCH_ASSOC);
while($rOpt=$qOpt->fetch())
{
	$optPabrik.="<option value=".$rOpt['kodeorganisasi'].">".$rOpt['namaorganisasi']."</option>";
}
//$optProduk="<option value=''>".$_SESSION['lang']['all']."</option>";
$optProduk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPrd="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang like '4%'";
$qPrd=$owlPDO->query($sPrd) or die(print " Gagal: ".PDOException::getMessage());
$qPrd->setFetchMode(PDO::FETCH_ASSOC);
while($rPrd=$qPrd->fetch())
{
	$optProduk.="<option value=".$rPrd['kodebarang'].">".$rPrd['namabarang']."</option>";
}
$optPeriode="";
$sGp="select DISTINCT substr(tanggal,1,7) as periode  from ".$dbname.".pabrik_produksi order by tanggal desc";
$qGp=$owlPDO->query($sGp) or die(print " Gagal: ".PDOException::getMessage());
$qGp->setFetchMode(PDO::FETCH_ASSOC);
while($rGp=$qGp->fetch())
{
   $thn=explode("-", $rGp['periode']);
   if($thn[1]=='12')
   {
     $optPeriode.="<option value='".substr($rGp['periode'],0,4)."'>".substr($rGp['periode'],0,4)."</option>";
   }
    $optPeriode.="<option value='".$rGp['periode']."'>".substr($rGp['periode'],5,2)."-".substr($rGp['periode'],0,4)."</option>";
}

$arr="##kdPbrik##kdTangki##tgl_1##tgl_2";
$arr1="##kodeorg1##tanggal1##tanggal2";


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language="javascript" src="js/pabrik_4persediaan.js"></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
$frm[0]="<div style=margin-bottom: 30px;>";
$frm[0].="<fieldset>
<legend><b>".$_SESSION['lang']['stok']."</b></legend>";
$frm[0].="<table cellspacing=1 border=0>
<tr><td><label>".$_SESSION['lang']['unit']."</label></td><td><select id=kdPbrik name=kdPbrik style=width:165px onchange=getTangki()>".$optPabrik."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['kodetangki']."</label></td><td><select id=kdTangki name=kdTangki style=width:165px><option value=\"\">".$_SESSION['lang']['all']."</option></select></td></tr>
<tr><td><label>".$_SESSION['lang']['tanggal']."</label></td><td><input type=text class=myinputtext id=tgl_1 onmousemove=setCalendar(this.id); onkeypress=return false;  size=7 maxlength=10 onblur=cleart() readonly/> 
    s.d. <input type=text class=myinputtext id=tgl_2 onmousemove=setCalendar(this.id); onkeypress=return false;  size=7 maxlength=10  onblur=cleart() readonly/></td></tr>
<tr><td><td><button onclick=zPreview('pabrik_slave_4persediaan','".$arr."','printContainer') class=mybutton name=preview id=preview>Preview</button>
    <button onclick=zPdf('pabrik_slave_4persediaan','".$arr."','printContainer') class=mybutton name=preview id=preview>PDF</button>
    <button onclick=zExcel(event,'pabrik_slave_4persediaan.php','".$arr."') class=mybutton name=preview id=preview>Excel</button></td></tr>
</table>
</fieldset>";
$frm[0].="</div>

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;'>
</div></fieldset>";


$frm[1]="<div style=margin-bottom: 30px;>";
$frm[1].="<fieldset>
<legend><b>".$_SESSION['lang']['stok']." vs ".$_SESSION['lang']['pengiriman']."</b></legend>";
$frm[1].="<table cellspacing=1 border=0>
<tr><td><label>".$_SESSION['lang']['unit']."</label></td><td><select id=kodeorg1 name=kodeorg1 style=width:165px>".$optPabrik."</select></td></tr>

<tr><td><label>".$_SESSION['lang']['tanggal']."</label></td><td><input type=text class=myinputtext id=tanggal1 onmousemove=setCalendar(this.id); onkeypress=return false;  size=7 maxlength=10 readonly/> 
    s.d. <input type=text class=myinputtext id=tanggal2 onmousemove=setCalendar(this.id); onkeypress=return false;  size=7 maxlength=10  onblur=cleart() readonly/></td></tr>

<tr><td><td><button onclick=zPreview('pabrik_slave_4persediaan_kirim','".$arr1."','printContainer1') class=mybutton name=preview id=preview>Preview</button>
</table>
</fieldset>";
$frm[1].="</div>

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer1' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>";
//<tr><td><label>".$_SESSION['lang']['produk']."</label></td><td><select id=produk1 name=produk1 style=width:150px>".$optProduk."</select></td></tr>
//    <button onclick=zPdf('pabrik_slave_4persediaan_kirim','".$arr1."','printContainer1') class=mybutton name=preview id=preview>PDF</button>
//    <button onclick=zExcel(event,'pabrik_slave_4persediaan_kirim.php','".$arr1."') class=mybutton name=preview id=preview>Excel</button></td></tr>

OPEN_BOX('','<span class=judul>'.strtoupper('PRODUCT STOCK').'</span>');

//========================
$hfrm[0]=$_SESSION['lang']['stok'];
$hfrm[1]=$_SESSION['lang']['stok']." vs ".$_SESSION['lang']['pengiriman'];
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,'100%');
//===============================================

/*#======Select Prep======
# Get Data
//$where = " length(kodeorganisasi)='4'";
$optOrg = makeOption($dbname,'vhc_5master','kodevhc,kodevhc','','0');
#======End Select Prep======
#=======Form============
$els = array();
# Fields
$els[] = array(
  makeElement('tahun','label',$_SESSION['lang']['tahun']),
  makeElement('tahun','textnum',date(Y),array('style'=>'width:150px','maxlength'=>'16',
    'onkeypress'=>'return tanpa_kutip(event)'))
);
$els[] = array(
  makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg','select','',array('style'=>'width:150px'),$optOrg)
);
$els[] = array(
  makeElement('revisi','label',$_SESSION['lang']['revisi']),
  makeElement('revisi','textnum','0',array('style'=>'width:150px','maxlength'=>'80',
    'onkeypress'=>'return tanpa_kutip(event)'))
);

# Button
$param = '##tahun##kodeorg';
$container = 'printContainer';
$els['btn'] = array(
  makeElement('preview','btn','Preview',array('onclick'=>
    "zPreview('keu_slave_2laporanAnggaranKebun_print','".$param."','".$container."')")).
  makeElement('printPdf','btn','PDF',array('onclick'=>
    "zPdf('keu_slave_2laporanAnggaranKebun_print','".$param."','".$container."')")).
  makeElement('printExcel','btn','Excel',array('onclick'=>"excelBudKebun()"))
);

# Generate Field
echo "<div style='margin-bottom:30px'>";
echo genElTitle('Laporan Anggaran Traksi',$els);
echo "</div>";
echo "<fieldset style='clear:both'><legend><b>Print Area</b></legend>";
echo "<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'></div></fieldset>";
#=======End Form============*/

CLOSE_BOX();
echo close_body();
?>