<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_laporan.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanrugilabaperiodik']).'</span><br>');

//get existing period
$str="select distinct substr(periode,1,4) as periode from ".$dbname.".setup_periodeakuntansi
      order by periode desc";
$optper="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optper.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}	
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{   
        //=================ambil PT;  
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
        $optpt="";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
                $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

        }

        //=================ambil gudang;  
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL'
                        or tipe='HOLDING')  and induk!=''";
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
     <legend>".$_SESSION['lang']['form']."</legend>
	 ".$_SESSION['lang']['pt']." : "."<select id=pt style='width:200px;'  onchange=ambilAnak(this.options[this.selectedIndex].value)>".$optpt."</select>
	 <select id=gudang style='width:150px;' onchange=hideById('printPanel')>".$optgudang."</select>
	 ".$_SESSION['lang']['periode']." : "."<select id=periode onchange=hideById('printPanel')>".$optper."</select>
	 <button class=mybutton onclick=getLaporanRugiLabaPeriodik()>".$_SESSION['lang']['proses']."</button>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset>
     <legend>Result :</legend>
	 <span id=printPanel style='display:none;'>
     <img onclick=fisikKeExcel(event,'keu_laporanRugiLabaPeriodik_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=fisikKePDF(event,'keu_laporanRugiLabaPeriodik_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>    
	 <div style='width:100%;height:430px;overflow:auto;' id=container>
     </div></fieldset>";
CLOSE_BOX();
close_body();
?>