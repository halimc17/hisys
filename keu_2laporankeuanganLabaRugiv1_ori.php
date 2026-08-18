<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_laporan_ori.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporankeuangan']." - ".$_SESSION['lang']['labarugi']." BEFORE AUDIT ADJUSTMENT").'</span><br>');

//get existing period
$str=$owlPDO->query("select distinct periode from ".$dbname.".setup_periodeakuntansi
    order by periode desc"); 	  
$str->setFetchMode(PDO::FETCH_OBJ);   
$optper='';
while($bar=$str->fetch()){
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{   
    //ambil PT;  
    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT'
        order by namaorganisasi");
    $str->setFetchMode(PDO::FETCH_OBJ);  
    $optpt="";
    while($bar=$str->fetch())
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }

    //ambil gudang;  
    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where length(kodeorganisasi)=4");
    $str->setFetchMode(PDO::FETCH_OBJ); 
    $optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
    while($bar=$str->fetch())
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
        <button class=mybutton onclick=getLaporanKeuanganLabaRugiv1()>".$_SESSION['lang']['proses']."</button>
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset>
     <legend>Form</legend>
	 <span id=printPanel style='display:none;'>
        <img onclick=fisikKeExcel2(event,'keu_laporankeuanganLabaRugi_Excelv1_Ori.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
    </span>    
    <div id=container style='width:100%;height:430px;overflow:auto;'>
    </div></fieldset>";
CLOSE_BOX();
close_body();
?>