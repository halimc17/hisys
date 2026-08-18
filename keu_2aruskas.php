<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/keu_laporan.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper("Cash Requisition").'</b>');

//get existing period
$str="select distinct periode from ".$dbname.".keu_pdoht order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);  
$optper="";
while($bar=$res->fetch())
{
	$optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{   
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);                   
    $optpt="";
    while($bar=$res->fetch())
    {
      $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }

    //=================ambil gudang;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL' or tipe='HOLDING')  and induk!=''";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);        
    $optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
    while($bar= $res->fetch())
    {
      $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
}
else
{
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);                   
    $bar=$res->fetch();
    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

    //=================ambil gudang;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);        
    $bar= $res->fetch();
    $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

echo"<fieldset style='width:300px;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
      <tr>
        <td>".$_SESSION['lang']['pt']."</td>
        <td>:</td>
        <td><select id=pt style='width:150px;'  onchange=ambilAnak(this.options[this.selectedIndex].value)>".$optpt."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=gudang style='width:150px;' onchange=hideById('printPanel')>".$optgudang."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td><select id=periode style='width:100px;' onchange=hideById('printPanel')>".$optper."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kas']."</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>
      </tr>
      <tr>
        <td colspan=2></td>
        <td><button class=mybutton onclick=getLaporanArusKas()>".$_SESSION['lang']['preview']."</button>
            <button class=mybutton onclick=Excelaruskas(event,'keu_slave_2aruskas.php')>".$_SESSION['lang']['excel']."</button></td>
      <tr>
     </table>
	 
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"
	 <div style='width:100%;height:359px;overflow:scroll;' id=container>
     </div>";
CLOSE_BOX();
close_body();
?>