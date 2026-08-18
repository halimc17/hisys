<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_laporan.js?v=<?php echo time(); ?>'></script>
<? 
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_2laporankeuanganLabaRugiv1_lg1').'</span><br>');

//get existing period
$str="select distinct left(periode,4) as periode from ".$dbname.".setup_periodeakuntansi where left(periode,4) >= '2021' group by left(periode,4) order by periode desc"; 	  
$optper='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optper.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}	

if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')||($_SESSION['empl']['tipelokasitugas']=='KANWIL'))
{   
    //ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
    $optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }

    //ambil gudang;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4";
    $optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
}
else
{ 
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";   
}

echo"<fieldset style=float:left>
    <legend>Form</legend>
        ".$_SESSION['lang']['pt']." : "."<select id=pt style='width:200px;' onchange=ambilAnak(this.options[this.selectedIndex].value)>".$optpt."</select>
        <select id=gudang style='width:150px;'>".$optgudang."</select>
        ".$_SESSION['lang']['periode']." : "."<select id=periode onchange=hideById('printPanel')>".$optper."</select>
        <button class=mybutton onclick=getLaporanKeuanganLabaRugiv1_lg1()>".$_SESSION['lang']['proses']."</button>
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset>
     <legend>Form</legend>
	 <span id=printPanel style='display:none;'>
        <img onclick=fisikKeExcel2(event,'keu_laporankeuanganLabaRugi_Excelv2_lg1.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
        <img onclick=fisikKeExcel2(event,'keu_laporankeuanganLabaRugi_Excelv3_lg1.php') src=images/excel.jpg class=resicon title='MS.Excel Rekap'> 
        <!--<img onclick=fisikKePDF(event,'keu_laporanNeraca_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>-->
    </span>    
    <div id=container style='width:100%;height:430px;overflow:auto;'>
    </div></fieldset>";
CLOSE_BOX();
close_body();
?>