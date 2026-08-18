<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

?>
<script language=javascript src='js/keu_laporanxx.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<!--
<script type=text/javascript src=pivottable-master/dist/jquery.min.js></script>
<script type=text/javascript src=pivottable-master/dist/jquery-ui.min.js></script>
<script type=text/javascript src=DataTables/js/jquery.dataTables.min.js></script>
<script type=text/javascript src=DataTables/js/dataTables.responsive.min.js></script>

<link rel=stylesheet type=text/css href=DataTables/css/jquery.dataTables.min.css>
<link rel=stylesheet type=text/css href=DataTables/css/responsive.dataTables.min.css>
-->
<!--
<script type="text/javascript" src="pivottable-master/dist/jquery.min.js"></script>
<script type="text/javascript" src="pivottable-master/dist/jquery-ui.min.js"></script>

<script type="text/javascript" src="tablejstoexcel/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="tablejstoexcel/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/jszip.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/buttons.print.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/buttons.html5.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/pdfmake.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/vfs_fonts.js"></script>


<link rel="stylesheet" type="text/css" href="tablejstoexcel/buttons.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="tablejstoexcel/jquery.dataTables.min.css">

<script type="text/javascript" src="tablejstoexcel/dataTables.fixedHeader.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/dataTables.colReorder.min.js"></script>

<link rel="stylesheet" type="text/css" href="tablejstoexcel/fixedHeader.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="tablejstoexcel/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="tablejstoexcel/colReorder.dataTables.min.css">

-->

<?

OPEN_BOX('','<span class=judul>'.getMenu('keu_2jurnalxx').'</span><br>');

//get existing period
// $str="select distinct periode as periode from ".$dbname.".keu_jurnaldt_vw
      // order by periode desc limit 24";	  
// $str=$owlPDO->query($str);
// $str->setFetchMode(PDO::FETCH_OBJ);
// $optper='';
// while($bar=$str->fetch())
// {
    // $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
// }

$optnamakaryawan="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
$res=fetchdata($str);
foreach($res as $bar){
	 $optnamakaryawan.="<option value='".$bar['karyawanid']."'>".$bar['nik']." - ".$bar['namakaryawan']."</option>";
}
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL')
{ 
    $optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optgudang=$optReg="<option value=''>".$_SESSION['lang']['all']."</option>";


    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
          where tipe='PT'
          order by namaorganisasi");
    $str->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$str->fetch()){
        /*$scek="select distinct * from ".$dbname.".setup_periodeakuntansi where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$bar->kodeorganisasi."')";
        $rcek=fetchData($scek);
        if(count($rcek)==0){
            continue;
        }*/
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi."-".$bar->namaorganisasi."</option>";
    }
} 
// elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    // $nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
    
    // $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
    // $iUnit=$owlPDO->query("select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."' ");
    // $iUnit->setFetchMode(PDO::FETCH_ASSOC);
    // while($dUnit=  $iUnit->fetch())
    // {
        // $optUnit.="<option value='".$dUnit['kodeunit']."'>".$nmOrg[$dUnit['kodeunit']]."</option>";
    // }
    // $optgudang = $optUnit;
    // $optpt="";
    // $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    // //$optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    // $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
// } 
else {
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
}
    

$optKel="<option value=''>".$_SESSION['lang']['all']."</option>";
$optKel.="<option value='HIS'>HIS - History Jurnal</option>";
$iKel=$owlPDO->query("select distinct(kodekelompok) as kodekelompok,keterangan from ".$dbname.".keu_5kelompokjurnal");
$iKel->setFetchMode(PDO::FETCH_ASSOC);
while($dKel= $iKel->fetch())
{
    $optKel.="<option value='".$dKel['kodekelompok']."'>".$dKel['kodekelompok']." - ".$dKel['keterangan']."</option>";
}
 

$optAkun = "<option value=''>".$_SESSION['lang']['all']."</option>";
$str = "SELECT noakun, namaakun FROM ".$dbname.".keu_5akun WHERE level='5' ORDER BY noakun ASC";
$res = fetchdata($str);
foreach($res as $val){
    $optAkun .= "<option value='".$val['noakun']."'>".$val['noakun']." - ".$val['namaakun']."</option>";
}



    $optrev="<option value='0'>0</option>";
    $optrev.="<option value='1'>1</option>";
    $optrev.="<option value='2'>2</option>";
    $optrev.="<option value='3'>3</option>";
    $optrev.="<option value='4'>4</option>";    
    $optrev.="<option value='5'>5</option>";     
//}	

echo"<fieldset style=float:left id=formfilter style=display:block;>
     <legend>".$_SESSION['lang']['form']."</legend>
        <table border=0>
            <tr>
                <td>".$_SESSION['lang']['pt']."</td>
                <td>:</td>
                <td><select class='select2' id=pt style='width:200px;'  onchange=getkaryawan();>".$optpt."</select></td><td></td>
				
				<td>".$_SESSION['lang']['kodekelompok']."</td>
                <td>:</td>
                <td><select class='select2' style='width:202px;' id=kdKel>".$optKel."</select>
				
				</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['regional']."</td>
                <td>:</td>
                <td><select class='select2' id=regional style='width:200px;' onchange=getUnit()>".$optReg."</select> </td><td></td>
				
				<td>".$_SESSION['lang']['nojurnal']."</td>
                <td>:</td>
                <td><input type=text id=nojurnal style='width:200px;' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['unit']."</td>
                <td>:</td>
                <td><select class='select2' id=gudang style='width:200px;'>".$optgudang."</select></td><td></td>
				
				<td>".$_SESSION['lang']['noreferensi']."</td>
                <td>:</td>
                <td><input type=text id=ref style='width:200px;' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['periode']."</td>
                <td>:</td>
                <td><input type=text class=myinputtext  style='width:85px;' readonly id=periode onmousemove=setCalendar(this.id) onkeypress=return false;  size=11 maxlength=10 > s/d <input type=text class=myinputtext  style='width:84px;' readonly id=periode1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=11 maxlength=10 ></td><td></td>
				
				<td>".$_SESSION['lang']['keterangan']."</td>
                <td>:</td>
                <td><input type=text id=ket style='width:200px;' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['revisi']."</td>
                <td>:</td>
                <td><select class='select2' style='width:202px;' id=revisi onchange=hideById('printPanel')>".$optrev."</select></td>
				<td></td>
				<td>".$_SESSION['lang']['namakaryawan']."</td>
                <td>:</td>
				<td><select class='select2' style='width:202px;' id=nik>".$optnamakaryawan."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['noakun']."</td>
                <td>:</td>
                <td><select class='select2' style='width:202px;' id=noakun>".$optAkun."</select></td>
                <td></td>
                <td>".$_SESSION['lang']['nodok']."</td>
                <td>:</td>
                <td><input class=myinputtext style='width:200px' id=nodok></td>
                
            </tr>
            
		<tr><td><td><td>
	 
	 <button hidden class=mybutton onclick=getLaporanJurnal()>".$_SESSION['lang']['proses']."</button>
	 <button hidden class=mybutton onclick=fisikKeExcel(event,'keu_laporanJurnal_Excel.php')>".$_SESSION['lang']['excel']."</button>
	 <button hidden class=mybutton onclick=fisikKePDF(event,'keu_laporanJurnal_pdf.php')>".$_SESSION['lang']['pdf']."</button>
	 
	<button hidden class=mybutton onclick=getLaporanJurnal('html')>".$_SESSION['lang']['preview']."</button>
	<button class=mybutton onclick=lapjurnal()>".$_SESSION['lang']['preview']."</button>
	<button class=mybutton onclick=getLaporanJurnal('excel')>".$_SESSION['lang']['excel']."</button>
	 
	 <select style='display:none' id=tampilanId>
		<option value=''></option>
	 </select>
	 </table>
	 </fieldset>";
     //<select style='width:75px;' id=periode onchange=hideById('printPanel')>".$optper."</select>
     //<select style='width:75px;' id=periode1 onchange=hideById('printPanel')>".$optper."</select>
CLOSE_BOX();
OPEN_BOX('','');
// echo"<legend><b>".$_SESSION['lang']['list']."</b></legend>
	// <div id='both_report'>
	    // <div id='head_tableboth' style='height:20px;'>
            // <span id=printPanel style='display:none;'>
                // <a href='#' onclick=fisikKeExcel(event,'keu_laporanJurnal_Excel.php')><img src=images/excel.jpg class=zImgBtn title='MS.Excel'></a> 
                // <a href='#' onclick=fisikKePDF(event,'keu_laporanJurnal_pdf.php')><img title='PDF' class=zImgBtn src=images/pdf.jpg></a>
                
                // <a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='body_tableboth' table='sortable' style='float:right;margin-right:10px'>
                    // <img title='Full Screen' class=zImgBtn src='images/full-screen.png'>
                // </a>
                // <a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='body_tableboth' shown='0' style='float:right;margin-right:10px;' >
                    // <img title='Fixed Header Table' class=zImgBtn src=images/fix-header.gif>
                // </a>
                // <!--<a class='clearfixheadbtn' table='sortable' style='float:right;margin-right:10px'>
                    // <img title='fix-header' class=zImgBtn src=images/remove-fix-heder.gif>
                // </a>-->
                
                
            // </span>
	    // </div>
        // <div id='body_tableboth' style='width:100%;height:410px;overflow:auto;'>
            // <div id=containerr></div>
        // </div>
    // </div>";
echo"<div id=containerr style=min-height:400px></div>";	
CLOSE_BOX();
close_body();
?>