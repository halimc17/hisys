<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX(); 

// INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('laporanPembelian', 'Laporan Pembelian TBS', 'pemasaran', NULL, 'Laporan Pembelian TBS', 'FFB Purchase Report');

$optPabrik0="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOpt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
$qOpt->setFetchMode(PDO::FETCH_ASSOC);
while($rOpt=$qOpt->fetch())
{
    $optPabrik0.="<option value=".$rOpt['kodeorganisasi'].">".$rOpt['namaorganisasi']."</option>";
}

$optSupplier0="<option value=''>".$_SESSION['lang']['all']."</option>";
$ssupplier="select distinct kodetimbangan,namasupplier from ".$dbname.".log_5supplier 
    where kodetimbangan IS NOT NULL and kodetimbangan like '1%' order by namasupplier";
$qsupplier=$owlPDO->query($ssupplier) or die(print " Gagal: ".PDOException::getMessage());
$qsupplier->setFetchMode(PDO::FETCH_ASSOC);
while($rsupplier=$qsupplier->fetch())
{
    $optSupplier0.="<option value='".$rsupplier['kodetimbangan']."'>".$rsupplier['namasupplier']." [".$rsupplier['kodetimbangan']."]</option>";
}

$arr0="##pabrik0##supplier0##tgl01##tgl02";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2pembeliantbs.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?      

$frm[0].="<div style=\"margin-bottom: 30px;\">
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['laporanPembelian']."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr><td><label>".$_SESSION['lang']['pabrik']."</label></td><td><select id=\"pabrik0\" name=\"pabrik0\" style=\"width:150px\">".$optPabrik0."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['supplier']."</label></td><td><select id=\"supplier0\" name=\"supplier0\" style=\"width:150px\">".$optSupplier0."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['tanggal']."</label></td><td>
<input type=text class=myinputtext id=tgl01 onchange=bersih0() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=9 maxlength=10> - 
<input type=text class=myinputtext id=tgl02 onchange=bersih0() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=9 maxlength=10>
</td></tr>
<tr><td colspan=\"2\"><button onclick=\"zPreview('pmn_slave_2pembeliantbs','".$arr0."','printContainer0')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'pmn_slave_2pembeliantbs.php','".$arr0."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer0' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>";
//    <button onclick=\"zPdf('pmn_slave_2penjualan_harian','".$arr1."','printContainer1')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button>

//========================
$hfrm[0]=$_SESSION['lang']['laporanPembelian'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,900);
//===============================================

CLOSE_BOX();
echo close_body();
?>