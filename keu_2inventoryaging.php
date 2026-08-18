<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/keu_2inventoryaging.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_2inventoryaging')."</span>");

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{   
    //=================ambil PT;  
    $str="select distinct kodeorg,namaorganisasi from ".$dbname.".log_5saldobulanan a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi order by namaorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);                   
    $optpt="";
    while($bar=$res->fetch())
    {
      $optpt.="<option value='".$bar->kodeorg."'>".$bar->kodeorg." - ".$bar->namaorganisasi."</option>";
    }

   //=================ambil gudang;  
    $str="select distinct left(kodegudang,4) as unit,namaorganisasi from ".$dbname.".log_transaksi_vw a left join ".$dbname.".organisasi b on left(a.kodegudang,4)=b.kodeorganisasi 
          where kodegudang not like '%-%' order by namaorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);        
    $optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar= $res->fetch())
    {
      $optunit.="<option value='".$bar->unit."'>".$bar->unit." - ".$bar->namaorganisasi."</option>";
    }
}
else
{
    //=================ambil PT;  
    $str="select distinct kodeorg,namaorganisasi from ".$dbname.".log_5saldobulanan a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);                   
    $bar=$res->fetch();
    $optpt.="<option value='".$bar->kodeorg."'>".$bar->kodeorg." - ".$bar->namaorganisasi."</option>";

    //=================ambil gudang;  
    $str="select distinct left(kodegudang,4) as unit,namaorganisasi from ".$dbname.".log_transaksi_vw a 
          left join ".$dbname.".organisasi b on left(a.kodegudang,4)=b.kodeorganisasi 
          where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);        
    $bar= $res->fetch();
    $optunit.="<option value='".$bar->unit."'>".$bar->unit." - ".$bar->namaorganisasi."</option>";
}

//Get Kelompok Barang
$optgudang=$optKlBarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sKlBarang="select kode,kelompok from ".$dbname.".log_5klbarang where status='1' order by kode";
$qKlBarang=$owlPDO->query($sKlBarang) or die(print " Gagal: ".PDOException::getMessage());
$qKlBarang->setFetchMode(PDO::FETCH_ASSOC);
while($rKlBarang=$qKlBarang->fetch())
{
	$optKlBarang.="<option value='".$rKlBarang['kode']."'>".$rKlBarang['kode']." - ".$rKlBarang['kelompok']."</option>";
}

echo"<fieldset style='width:350px;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
      <tr>
        <td>".$_SESSION['lang']['pt']."</td>
        <td>:</td>
        <td><select id=pt style='width:157px;' onchange=getunit(this.options[this.selectedIndex].value)>".$optpt."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit style='width:157px;' onchange=getgudang(this.options[this.selectedIndex].value)>".$optunit."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['gudang']."</td>
        <td>:</td>
        <td><select id=gudang style='width:157px;' onchange='getklbarang()'>".$optgudang."</select></td>
      </tr>
	  <tr>
		<td>".$_SESSION['lang']['kelompokbarang']."</td>
		<td>:</td>
		<td>
			<select style='width:157px' id='klbarang' onchange='getKodeSub()'>".$optKlBarang."</select>
			<img id='klbarang' onclick=z.elSearch('klbarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
	  </tr>
	  <tr>
		<td>".$_SESSION['lang']['kodesubkelompokbarang']."</td>
		<td>:</td>
		<td>
			<select style='width:157px' id='klsubbarang' onchange='getkodebarang()'>".$optgudang."</select>
			<img id='klsubbarang' onclick=z.elSearch('klsubbarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
	  </tr>
	  <tr>
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td>
			<select style='width:157px' id='kdbarang'>".$optgudang."</select>
			<img id='kdbarang' onclick=z.elSearch('kdbarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
	  </tr>
      <tr>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/></td>
      </tr>
      <tr>
        <td colspan=2></td>
        <td><button class=mybutton onclick=getLaporan()>".$_SESSION['lang']['preview']."</button>
            <button class=mybutton onclick=getExcel(event,'keu_slave_2inventoryaging.php')>".$_SESSION['lang']['excel']."</button></td>
      <tr>
     </table>
   
   </fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"<div style='width:100%;height:359px;overflow:scroll;' id=container></div>";
CLOSE_BOX();
close_body();
?>