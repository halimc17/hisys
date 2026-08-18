<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

require_once('lib/zSelect2.php');

$lokasitugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$arr="##tanggal1##tanggal2##karyawanid";
$arr1="##tahun";
$arr2="##tanggal21##tanggal22##karyawanid2";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/sdm_2rekapabsenho.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel=stylesheet type='text/css' href='style/zTable.css'>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2laporanKehadiranHO').'</span><br>');
?>
<div>

<?php    

$daritahun=9999;
$sampaitahun=0;

// karyawan ijin & cuti
$optTahun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOpt="select darijam, sampaijam from ".$dbname.".sdm_ijin where stpersetujuan1 = '1' and stpersetujuanhrd = '1'";
$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
$qOpt->setFetchMode(PDO::FETCH_ASSOC);
while($rOpt=$qOpt->fetch())
{
    if(substr($rOpt['darijam'],0,4)!='0000')if($daritahun>substr($rOpt['darijam'],0,4))$daritahun=substr($rOpt['darijam'],0,4);
    if(substr($rOpt['sampaijam'],0,4)!='0000')if($sampaitahun<substr($rOpt['sampaijam'],0,4))$sampaitahun=substr($rOpt['sampaijam'],0,4);
}

// karyawan dinas
// $sOpt="select tgldinasdari as tanggalperjalanan, tgldinassampai as tanggalkembali from ".$dbname.".sdm_pjdinasht where statuspersetujuan='1' and statushrd='1'";
$sOpt="select tgldinasdari as tanggalperjalanan, tgldinassampai as tanggalkembali from ".$dbname.".sdm_pjdinasht where statuspengajuan='1'";
$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
$qOpt->setFetchMode(PDO::FETCH_ASSOC);
while($rOpt=$qOpt->fetch())
{
    if(substr($rOpt['tanggalperjalanan'],0,4)!='0000')if($daritahun>substr($rOpt['tanggalperjalanan'],0,4))$daritahun=substr($rOpt['tanggalperjalanan'],0,4);
    if(substr($rOpt['tanggalkembali'],0,4)!='0000')if($sampaitahun<substr($rOpt['tanggalkembali'],0,4))$sampaitahun=substr($rOpt['tanggalkembali'],0,4);    
}

// karyawan absen
$sOpt="select scan_date from ".$dbname.".att_log where 1";
$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
$qOpt->setFetchMode(PDO::FETCH_ASSOC);
while($rOpt=$qOpt->fetch())
{
    if(substr($rOpt['scan_date'],0,4)!='0000')if($daritahun>substr($rOpt['scan_date'],0,4))$daritahun=substr($rOpt['scan_date'],0,4);
    if(substr($rOpt['scan_date'],0,4)!='0000')if($sampaitahun<substr($rOpt['scan_date'],0,4))$sampaitahun=substr($rOpt['scan_date'],0,4);    
}

//echo $daritahun."-".$sampaitahun;

for ($i = $daritahun; $i <= $sampaitahun; $i++) {
    $optTahun.="<option value=".$i.">".$i."</option>";
}

//ambil query untuk data karyawan
$skaryawan="select a.karyawanid, b.namajabatan, a.namakaryawan, c.nama from ".$dbname.".datakaryawan a 
    left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan 
    left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode 
    where a.lokasitugas like '%HO' 
    order by namakaryawan asc";    
//    where a.lokasitugas like '%HO' and ((a.tanggalkeluar>= '".$tangsys1."' and a.tanggalkeluar< '".$tangsys2."') or a.tanggalkeluar='0000-00-00')
//echo $skaryawan;
$rkaryawan=fetchData($skaryawan);
$optkaryawan="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($rkaryawan as $row => $kar)
{
    $optkaryawan.="<option value='".$kar['karyawanid']."'>".$kar['namakaryawan']." - ".$kar['namajabatan']."</option>";
}  


echo"<div style=margin-bottom: 30px;>";
echo"<fieldset style=float:left>
<legend><b>".$_SESSION['lang']['form']."</b></legend>";
echo"<table cellspacing=1 border=0>
    <tr>
        <td><label>".$_SESSION['lang']['tanggalmulai']."</label></td><td>:</td>
        <td><input type=\"text\" class=\"myinputtext\" id=\"tanggal1\" name=\"tanggal1\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:200px;\" value='".date('01-m-Y')."' readonly /></td>
    </tr>
    <tr>
        <td><label>".$_SESSION['lang']['tanggalsampai']."</label></td><td>:</td>
        <td><input type=\"text\" class=\"myinputtext\" id=\"tanggal2\" name=\"tanggal2\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:200px;\" value='".date('d-m-Y')."' readonly /></td>
    </tr>
    <tr><td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
        <td><select id=karyawanid class=select2 name=karyawanid style='width:205px;'>".$optkaryawan."</select></td>
    </tr>
    <tr>
        <td colspan=\"2\"></td>
        <td colspan=\"2\">
            <button onclick=\"zPreview('sdm_slave_2rekapabsenho','".$arr."','container')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
            <button style='display:none' onclick=\"zPdf('sdm_slave_2rekapabsenho','".$arr."','container')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">PDF</button>
            <button onclick=\"zExcel(event,'sdm_slave_2rekapabsenho.php','".$arr."')\" class=\"mybutton\" name=\"excel\" id=\"excel\">Excel</button>
            <button onclick=\"Clear1()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>
        </td>
    </tr>
</table>
</fieldset>";
echo"</div>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id='container' style='overflow:auto;height:440px'></div>";   

// $frm[1]="<div style=margin-bottom: 30px;>";
// $frm[1].="<fieldset>
// <legend><b>".$_SESSION['lang']['rkpAbsen']." HO Annually</b></legend>";
// $frm[1].="<table cellspacing=1 border=0>
    // <tr>
        // <td><label>".$_SESSION['lang']['tahun']."</label></td>
        // <td><select id=tahun name=tahun style=width:100px>".$optTahun."</select></td>
    // </tr>
    // <tr height=\"20\"><td colspan=\"2\">&nbsp;</td></tr>
    // <tr>
        // <td colspan=\"2\">
            // <button onclick=\"zPreview('sdm_slave_2rekapabsenho1','".$arr1."','container1')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
            // <button onclick=\"zPdf('sdm_slave_2rekapabsenho1','".$arr1."','container1')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">PDF</button>
            // <button onclick=\"zExcel(event,'sdm_slave_2rekapabsenho1.php','".$arr1."')\" class=\"mybutton\" name=\"excel\" id=\"excel\">Excel</button>
            // <button onclick=\"Clear2()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>
        // </td>
    // </tr>
// </table>
// </fieldset>";
// $frm[1].="</div>

// <fieldset style='clear:both'><legend><b>Print Area</b></legend>
// <div id='container1' style='overflow:auto;height:350px;max-width:1220px'>
// </div></fieldset>";   
    
// $frm[2]="<div style=margin-bottom: 30px;>";
// $frm[2].="<fieldset>
// <legend><b>".$_SESSION['lang']['laporanLembur']." HO</b></legend>";
// $frm[2].="<table cellspacing=1 border=0>
    // <tr>
        // <td><label>".$_SESSION['lang']['tanggalmulai']."</label></td>
        // <td><input type=\"text\" class=\"myinputtext\" id=\"tanggal21\" name=\"tanggal21\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:150px;\" /></td>
    // </tr>
    // <tr>
        // <td><label>".$_SESSION['lang']['tanggalsampai']."</label></td>
        // <td><input type=\"text\" class=\"myinputtext\" id=\"tanggal22\" name=\"tanggal22\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:150px;\" /></td>
    // </tr>
    // <tr><td>".$_SESSION['lang']['namakaryawan']."</td>
        // <td><select id=karyawanid2 name=karyawanid2 style='width:300px;'>".$optkaryawan."</select></td>
    // </tr>
    // <tr height=\"20\"><td colspan=\"2\">&nbsp;</td></tr>
    // <tr>
        // <td colspan=\"2\"> 
            // <button onclick=\"zPreview('sdm_slave_2rekapabsenho2','".$arr2."','container2')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
            // <!--<button onclick=\"zPdf('sdm_slave_2rekapabsenho2','".$arr2."','container2')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">PDF</button>-->
            // <button onclick=\"zExcel(event,'sdm_slave_2rekapabsenho2.php','".$arr2."')\" class=\"mybutton\" name=\"excel\" id=\"excel\">Excel</button>
            // <button onclick=\"Clear3()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>
        // </td>
    // </tr>
// </table>
// </fieldset>";
// $frm[2].="</div>

// <fieldset style='clear:both'><legend><b>Print Area</b></legend>
// <div id='container2' style='overflow:auto;height:350px;max-width:1220px'>
// </div></fieldset>";   


// //========================
// $hfrm[0]=$_SESSION['lang']['rkpAbsen'].' HO';
// // $hfrm[1]=$_SESSION['lang']['rkpAbsen'].' HO Annually';
// // $hfrm[2]=$_SESSION['lang']['laporanLembur'].' HO';

// //$hfrm[1]=$_SESSION['lang']['list'];
// //draw tab, jangan ganti parameter pertama, krn dipakai di javascript
// drawTab('FRM',$hfrm,$frm,200,900);
//========================    
   
?>

<?php
CLOSE_BOX();
echo close_body();
?>