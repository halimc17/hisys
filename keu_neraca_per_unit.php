<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src="js/keu_neraca_per_unit.js"></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['neracasaldo']).' PER UNIT</span><br>');

$arrTampilan=array("0"=>"Seluruhnya","1"=>"Tampilkan Debet","2"=>"Tampilkan Kredit","3"=>"Tidak Tampilkan Debet Kredit");
foreach ($arrTampilan as $key => $value) {
    @$optTampilan.="<option value='".$key."'>".$value."</option>";
}

//get existing period
$str="select distinct substr(tanggal,1,7) as periode from ".$dbname.".keu_jurnaldt
      order by periode desc";
$optper="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL')
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
                        where tipe='KEBUN'  and induk!=''";
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
	 ".$_SESSION['lang']['periode']." : "."<select id=periode onchange=hideById('printPanel')>".$optper."</select>
         ".$_SESSION['lang']['tglcutisampai']."
         ".$_SESSION['lang']['periode']." : "."<select id=periode1 onchange=hideById('printPanel')>".$optper."</select>
     <td>".$_SESSION['lang']['statussaldo']."</td><td>:</td><td><select id=tampilanId style='width:100px'  onchange=hideById('printPanel') >".$optTampilan."</select></td></table>
	 <button class=mybutton onclick=getLaporanBukuBesar()>".$_SESSION['lang']['proses']."</button>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
echo"<span id=printPanel style='display:none;'>
     <img onclick=fisikKeExcel(event,'keu_2slave_neraca_per_unit.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 </span>  
	     
     <fieldset><legend>Print Area</legend><div style='overflow:auto;height:440px;width:1240px' id=container>
     </div>
     </fieldset>";
CLOSE_BOX();
close_body();
?>