<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('bibit_2_keluar_masuk').'</span><br>');

$frm[0]='';
$frm[1]='';
$frm[2]='';

$optBatch="<option value=''>".$_SESSION['lang']['all']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $sBatch="select distinct batch from ".$dbname.".bibitan_mutasi order by batch desc";
    $sKodeorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' order by namaorganisasi asc";
}
else
{
    $sBatch="select distinct batch from ".$dbname.".bibitan_mutasi where kodeorg like '%".$_SESSION['empl']['lokasitugas']."%' order by batch desc";
    $sKodeorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and kodeorganisasi like '%".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi asc";
}
$qBatch=$owlPDO->query($sBatch) or die(print " Gagal: ".PDOException::getMessage());
$qBatch->setFetchMode(PDO::FETCH_ASSOC);
while($rBatch=$qBatch->fetch())
{
    $optBatch.="<option value='".$rBatch['batch']."'>".$rBatch['batch']."</option>";
}

$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qKodeOrg=$owlPDO->query($sKodeorg) or die(print " Gagal: ".PDOException::getMessage());
$qKodeOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rKodeorg=$qKodeOrg->fetch())
{
    $optKodeorg.="<option value='".$rKodeorg['kodeorganisasi']."'>".$rKodeorg['kodeorganisasi']." - ".$rKodeorg['namaorganisasi']."</option>";
}
$arr="##kdUnit##kdBatch";
?>
<script language=javascript src="js/zTools.js"></script>
<script language=javascript src="js/zReport.js"></script>
<script type="text/javascript" src="js/bibit_2_keluar_masuk.js"></script>

<link rel=stylesheet type=text/css href=style/zTable.css>

<?    
// $frm[0].="<fieldset style=\"float: left;\">
// <legend><b>".$_SESSION['lang']['form']."</b></legend>
// <table cellspacing=\"1\" border=\"0\" >
// <tr><td><label>".$_SESSION['lang']['unit']."</label></td><td>:</td><td><select id=\"kdUnit\" name=\"kdUnit\" style=\"width:150px\">
// ".$optKodeorg."</select></td></tr>
// <tr><td><label>".$_SESSION['lang']['batch']."</label></td><td>:</td><td><select id=\"kdBatch\" name=\"kdBatch\" style=\"width:150px\">
// ".$optBatch."</select></td></tr>

// <tr><td></td><td><td><button onclick=\"zPreview('bibit_2_slave_keluar_masuk','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button><button onclick=\"zPdf('bibit_2_slave_keluar_masuk','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button><button onclick=\"zExcel(event,'bibit_2_slave_keluar_masuk.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr>
// </table>
// </fieldset>
// <fieldset style='clear:both'><legend><b>Print Area</b></legend>
// <div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>
// </div></fieldset>";

// $sOrg2="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi 
//     where tipe ='pt'
//     order by namaorganisasi asc";
// $qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg2->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg2=$qOrg2->fetch()){
//     $sData="select distinct kodeorg from ".$dbname.".bibitan_mutasi 
//             where left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$rOrg2['kodeorganisasi']."')";
// 	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
// 	$rData=owlBaris($qData);
//     if($rData!=0){
//         $optpt1.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['namaorganisasi']."</option>";    
//     }
// }

$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(3) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optPT.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optPT.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optPT.="</optgroup>";
	}
}

$optkebun1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

echo"<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>".$_SESSION['lang']['pt']."</label></td><td>:</td>
    <td><select id=\"pt1\" name=\"pt1\" style=\"width:150px\" onchange=getkebun()>".$optPT."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td>
    <td><select id=\"kebun1\" name=\"kebun1\" style=\"width:150px\">".$optkebun1."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['sampai']." ".$_SESSION['lang']['tanggal']."</label></td><td>:</td>
    <td><input type='text' class='myinputtext' id='tanggal1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  
    size='10' maxlength='10' style=\"width:145px;\"/></td>
</tr>
<tr>
    <td></td><td><td>
        <button class=mybutton id=preview1 name=preview1 onclick=previewdata1()>".$_SESSION['lang']['preview']."</button>
        <button class=mybutton id=excel1 name=excel1 onclick=exceldata1(event,'bibit_2_slave_keluar_masuk.php')>".$_SESSION['lang']['excel']."</button>
    </td>
</tr>
</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id='container1' style='overflow:auto;height:450px;'></div>";


// $optbatch="<option value=''>".$_SESSION['lang']['all']."</option>";

// $optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $kodeorg="select distinct kodeorganisasi,namaorganisasi 
          // from ".$dbname.".bibitan_mutasi c inner join ".$dbname.".organisasi a on left(c.kodeorg,4)=a.kodeorganisasi
          // where tipe='KEBUN' order by namaorganisasi asc";
// $query=$owlPDO->query($kodeorg) or die(print " Gagal: ".PDOException::getMessage());
// $query->setFetchMode(PDO::FETCH_ASSOC);
// while($result=$query->fetch())
// {
    // $optkodeorg.="<option value='".$result['kodeorganisasi']."'>".$result['namaorganisasi']."</option>";
// }


// $frm[2].="

    // <fieldset style=\"float: left;\">
    // <legend><b>".$_SESSION['lang']['form']."</b></legend>
    // <table cellspacing=\"1\" border=\"0\" >
    // <tr>
        // <td><label>".$_SESSION['lang']['unit']."</label></td><td>:</td>
        // <td><select id=\"kodeunit\" name=\"kodeunit\" onchange=\"ambilbatch(this.value);\" style=\"width:150px\">".$optkodeorg."</select>
        // </td>
    // </tr>
    // <tr>
        // <td><label>".$_SESSION['lang']['batch']."</label></td><td>:</td>
        // <td><select id=\"kodebatch\" name=\"kodebatch\" style=\"width:150px\">".$optbatch."</select></td>
    // </tr>
    // <tr>
        // <td></td><td><td>
        // <button onclick=\"previewdata2()\" class=\"mybutton\" >Preview</button>
        // <button onclick=\"exceldata2(event,'bibit_slave_2kartu.php')\" class=\"mybutton\">Excel</button>
        // </td>
    // </tr>
    // </table>
    // </fieldset>

// <fieldset style='clear:both'><legend><b>Print Area</b></legend>
    // <div id='printContainer3' style='overflow:auto;height:350px;max-width:1220px'>
    // </div>
// </fieldset>";

/* 
//========================
$hfrm[0]=$_SESSION['lang']['laporanStockBIbit'];
$hfrm[1]=$_SESSION['lang']['rekapstockbibit'];
// $hfrm[2]=$_SESSION['lang']['seedcard'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,1200);
//========================
 */



CLOSE_BOX();
echo close_body();
?>