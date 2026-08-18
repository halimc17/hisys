<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/keu_laporan.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanjurnal']).'</span><br>');

//get existing period
// $str="select distinct periode as periode from ".$dbname.".setup_periodeakuntansi
      // order by periode desc";
$str="select distinct periode as periode from ".$dbname.".keu_jurnaldt_vw
      order by periode desc limit 24";	  
$str=$owlPDO->query($str);
$str->setFetchMode(PDO::FETCH_OBJ);
$optper='';
while($bar=$str->fetch())
{
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}

$optnamakaryawan="<option value=''>".$_SESSION['lang']['all']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
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
} elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    $nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
    
    $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
    $iUnit=$owlPDO->query("select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."' ");
    $iUnit->setFetchMode(PDO::FETCH_ASSOC);
    while($dUnit=  $iUnit->fetch())
    {
        $optUnit.="<option value='".$dUnit['kodeunit']."'>".$nmOrg[$dUnit['kodeunit']]."</option>";
    }
    $optgudang = $optUnit;
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    //$optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
} else {
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
}
    

$optKel="<option value=''>".$_SESSION['lang']['all']."</option>";
$iKel=$owlPDO->query("select distinct(kodekelompok) as kodekelompok,keterangan from ".$dbname.".keu_5kelompokjurnal");
$iKel->setFetchMode(PDO::FETCH_ASSOC);
while($dKel= $iKel->fetch())
{
    $optKel.="<option value='".$dKel['kodekelompok']."'>".$dKel['kodekelompok']." - ".$dKel['keterangan']."</option>";
}



    $optrev="<option value='0'>0</option>";
    $optrev.="<option value='1'>1</option>";
    $optrev.="<option value='2'>2</option>";
    $optrev.="<option value='3'>3</option>";
    $optrev.="<option value='4'>4</option>";    
    $optrev.="<option value='5'>5</option>";     
//}	

echo"<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>
        <table border=0>
            <tr>
                <td>".$_SESSION['lang']['pt']."</td>
                <td>:</td>
                <td><select id=pt style='width:200px;'  onchange=getkaryawan();>".$optpt."</select></td><td></td>
				
				<td>".$_SESSION['lang']['kodekelompok']."</td>
                <td>:</td>
                <td><select style='width:190px;' id=kdKel>".$optKel."</select>
				<img id='kdKel' onclick=z.elSearch('kdKel',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['regional']."</td>
                <td>:</td>
                <td><select id=regional style='width:200px;' onchange=getUnit()>".$optReg."</select> </td><td></td>
				
				<td>".$_SESSION['lang']['nojurnal']."</td>
                <td>:</td>
                <td><input type=text id=nojurnal size=29 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['unit']."</td>
                <td>:</td>
                <td><select id=gudang style='width:200px;'>".$optgudang."</select></td><td></td>
				
				<td>".$_SESSION['lang']['noreferensi']."</td>
                <td>:</td>
                <td><input type=text id=ref size=29 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['periode']."</td>
                <td>:</td>
                <td><input type=text class=myinputtext  style='width:85px;' id=periode onmousemove=setCalendar(this.id) onkeypress=return false;  size=11 maxlength=10 /> s/d <input type=text class=myinputtext  style='width:80px;' id=periode1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=11 maxlength=10 /></td><td></td>
				
				<td>".$_SESSION['lang']['keterangan']."</td>
                <td>:</td>
                <td><input type=text id=ket size=29 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['revisi']."</td>
                <td>:</td>
                <td><select style='width:90px;' id=revisi onchange=hideById('printPanel')>".$optrev."</select></td>
				<td></td>
				<td>".$_SESSION['lang']['namakaryawan']."</td>
                <td>:</td>
				<td><select style='width:190px;' id=nik>".$optnamakaryawan."</select>
				<img id='nik' onclick=z.elSearch('nik',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				
            </tr>
            
		<tr><td><td><td>
	 
	 <button hidden class=mybutton onclick=getLaporanJurnal()>".$_SESSION['lang']['proses']."</button>
	 <button hidden class=mybutton onclick=fisikKeExcel(event,'keu_laporanJurnal_Excel.php')>".$_SESSION['lang']['excel']."</button>
	 <button hidden class=mybutton onclick=fisikKePDF(event,'keu_laporanJurnal_pdf.php')>".$_SESSION['lang']['pdf']."</button>
	 
	 <button class=mybutton onclick=getLaporanJurnal('html')>".$_SESSION['lang']['preview']."</button>
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
echo"<legend><b>".$_SESSION['lang']['list']."</b></legend>
	<div id='both_report'>
	    <div id='head_tableboth' style='height:20px;'>
            <span id=printPanel style='display:none;'>
                <a href='#' onclick=fisikKeExcel(event,'keu_laporanJurnal_Excel.php')><img src=images/excel.jpg class=resicon title='MS.Excel'></a> 
                <a href='#' onclick=fisikKePDF(event,'keu_laporanJurnal_pdf.php')><img title='PDF' class=resicon src=images/pdf.jpg></a>
                
                <a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='body_tableboth' table='sortable' style='float:right;margin-right:10px'>
                    <img title='Full Screen' class=resicon src='images/full-screen.png'>
                </a>
                <a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='body_tableboth' shown='0' style='float:right;margin-right:10px;' >
                    <img title='Fixed Header Table' class=resicon src=images/fix-header.gif>
                </a>
                <!--<a class='clearfixheadbtn' table='sortable' style='float:right;margin-right:10px'>
                    <img title='fix-header' class=resicon src=images/remove-fix-heder.gif>
                </a>-->
                
                
            </span>
	    </div>
        <div id='body_tableboth' style='width:100%;height:410px;overflow:auto;'>
            <div id=containerr></div>
        </div>
    </div>";
CLOSE_BOX();
close_body();
?>