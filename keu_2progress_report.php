<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script type="text/javascript" src="js/keu_2progress_report.js"></script>
<?  
include('master_mainMenu.php');


if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT'  order by namaorganisasi";

$optStatus="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStatus.="<option value='0'>Pusat</option>";
$optStatus.="<option value='1'>Lokal</option>";

}//and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'
else
{	
	$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'  order by namaorganisasi";

@$optStatus.="<option value='1'>Lokal</option>";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        @$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length (kodeorganisasi)=4";
$optunit='';
$optunit.="<option value=''>".$_SESSION['lang']['all']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$str="select distinct periode from ".$dbname.".setup_periodeakuntansi
      order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}

$optstat="<option value=''>".$_SESSION['lang']['all']."</option>";   
$optstat.="<option value='1'>PO Belum GRN</option>";
$optstat.="<option value='2'>GRN Belum AP</option>";
$optstat.="<option value='3'>INV Belum Bayar</option>";






OPEN_BOX('','<span class=judul>'.getMenu('keu_2progress_report').'</span><br>');
echo "<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>
        <table border=0>
		 <tr >
                <td>".$_SESSION['lang']['pt']."</td>
                <td>:</td>
                <td><select id=pt style='width:180px;' onchange=getUnit()>".$optpt."</select></td><td></td>
                
            </tr>
            <tr >
                <td>".$_SESSION['lang']['unit']."</td>
                <td>:</td>
                <td><select id=unit style='width:180px;'></select></td><td></td>
                
            </tr>

            <tr >     
       
            <td>".$_SESSION['lang']['periode']."</td>
            <td>:</td>
            <td><input type=text class=myinputtext id=tgl name=tgl onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style=width:70px; /> S/D
            <input type=text class=myinputtext id=tgl1 name=tgl1 onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";  style=width:70px; /></td>
            </tr>

            <tr >     
       
            <td>".$_SESSION['lang']['status']."</td>
            <td>:</td>
            <td><select id=stts>".$optstat."</select></td>
            </tr>    
           
        <tr><td><td><td>
     <button class=mybutton onclick=preview()>".$_SESSION['lang']['proses']."</button>
     <button class=mybutton onclick=datakeexcel(event,'keu_slave_2progress_report.php')>".$_SESSION['lang']['excel']."</button>
     </table>
     </fieldset>";

CLOSE_BOX();
OPEN_BOX();     
    echo "<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:100%'; >
</div></fieldset>";


CLOSE_BOX();
echo close_body();
?>