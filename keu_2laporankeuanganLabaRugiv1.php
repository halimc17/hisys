<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_2laporankeuanganLabaRugiv1.js?v=<?php echo time(); ?>'></script>
<? 
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_2laporankeuanganLabaRugiv1').'</span><br>');

//get existing period
$str="select distinct periode from ".$dbname.".setup_periodeakuntansi
    order by periode desc"; 	  
$optper='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	

if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')||($_SESSION['empl']['tipelokasitugas']=='KANWIL'))
{   
    //ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
    $optpt="";
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
	<table>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select id=pt style='width:150px;'>".$optpt."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td><select id=periode onchange=hideById('printPanel')  style='width:150px;'>".$optper."</select></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td><button class=mybutton onclick=getLaporanKeuanganLabaRugiv1()>".$_SESSION['lang']['preview']."</button></td>
		</tr>
		<tr hidden><td> <select id=gudang style='width:150px;'>".$optgudang."</select></td></tr>
	</table>
    </fieldset>";//	<td><select id=pt style='width:200px;' onchange=ambilAnak(this.options[this.selectedIndex].value)>".$optpt."</select></td>
CLOSE_BOX();
OPEN_BOX('','');
echo"
	 <span id=printPanel style='display:none;'>
        <img onclick=fisikKeExcel2(event,'keu_laporankeuanganLabaRugi_Excelv2.php') src=images/excel.jpg class=resicon title='MS.Excel'>&nbsp;&nbsp;
        <img onclick=fisikKeExcel2(event,'keu_laporankeuanganLabaRugi_Excelv3.php') src=images/excel.jpg class=resicon title='MS.Excel Rekap'>&nbsp;&nbsp;
		<img title='Click untuk melihat detail HPP' style=cursor:pointer; onclick=\"previewhpp();\" src=images/info.png class=resicon title='HPP'>
        <!--<img onclick=fisikKePDF(event,'keu_laporanNeraca_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>-->
    </span><br><br>
    <div id=container style='width:100%;height:450px;overflow:auto;'>
    </div></fieldset>";
CLOSE_BOX();
close_body();
?>