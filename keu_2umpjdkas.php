<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/keu_2umpjdkas.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_2umpjdkas')."</span>");

//option tipe
$opttipe.="<option value='upd'>".$_SESSION['lang']['perdin']."</option>";	
$opttipe.="<option value='kk'>".$_SESSION['lang']['kaskecil']."</option>";	

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{   
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' and kodeorganisasi!='BOD' order by namaorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);                   
    $optpt="";
    while($bar=$res->fetch())
    {
      $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
    }

    //=================ambil gudang;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL' or tipe='HOLDING')  and induk!=''";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);        
    $optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
    while($bar= $res->fetch())
    {
      $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
    }
}
else
{
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);                   
    $bar=$res->fetch();
    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";

    //=================ambil gudang;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);        
    $bar= $res->fetch();
    $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

echo"<fieldset style='width:300px;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
      <tr>
        <td>".$_SESSION['lang']['pt']."</td>
        <td>:</td>
        <td><select id=pt style='width:157px;'  onchange=getunit(this.options[this.selectedIndex].value)>".$optpt."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit style='width:157px;' onchange=hideById('printPanel')>".$optgudang."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['tipe']."</td>
        <td>:</td>
        <td>
            <select id=tipe style='width:157px;'>".$opttipe."</select>
        </td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>
      </tr>
      <tr>
        <td colspan=2></td>
        <td><button class=mybutton onclick=getLaporan()>".$_SESSION['lang']['preview']."</button>
            <button class=mybutton onclick=getExcel(event,'keu_slave_2umpjdkas.php')>".$_SESSION['lang']['excel']."</button></td>
      <tr>
     </table>
	 
	 </fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"<div style='width:100%;height:359px;overflow:scroll;' id=container></div>";
CLOSE_BOX();
close_body();
?>