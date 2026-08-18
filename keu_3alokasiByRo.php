<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/alokasiByRo.js'></script>
<?
include('master_mainMenu.php');
if(substr($_SESSION['empl']['lokasitugas'],3,1)!='O'){
    exit("Error: Hanya RO dan HO yang dapat mengalokasi");
}
OPEN_BOX('','<span class=judul>'.getMenu('keu_3alokasiByRo').'</span><br>');
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$optOrg='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
echo"<fieldset style=float:left><legend>".$_SESSION['lang']['sumber']."</legend><table border=0>
       <tr><td>".$_SESSION['lang']['kodeorg']."</td><td>:</td>
              <td><select style='width:250px;' id=kodeorg>".$optOrg."</select></td></tr>
        <tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><input type=text size=12 id=periode disabled  class=myinputtext value='".$_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan']."'></td></tr>
	<tr><td><td><td>";
if($_SESSION['language']=='ID'){
  echo"Proses ini hanya boleh dilakukan 1 kali untuk 1 bulan.";
}else{
  echo"This process can only proceed onces a month";  
}  
echo"</table></fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<table border=0><tr><td><fieldset><legend>".$_SESSION['lang']['tujuan']."</legend><table>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
$no=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $no+=1;
    echo "<tr><td><select style=width:200px id=pt".$no."><option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option></select></td>
                <td>".$_SESSION['lang']['logouang'].".<input type=text class=myinputtextnumber id=jumlah".$no." onkeypress=\"return angka_doang(event)\" maxlength=15 size=15 onblur=hitungTotal(".  owlBaris($res).")><button onclick=alokasiKan(".$no.") class=mybutton id=button".$no.">".$_SESSION['lang']['proses']."</button></td></tr>";
}
echo"<tr><td>".$_SESSION['lang']['total']."</td><td>".$_SESSION['lang']['logouang'].".<input type=text class=myinputtextnumber size=15 maxlength=15 id=total></td></tr>";
if($_SESSION['language']=='EN'){
            echo"</table></fieldset>
         </td><td valign=top>
         <fieldset style='width:250px;'><legend>Info:</legend>
           The allocation process will only apply to estate units in the destination company, 
           divided by the area of the estate in the company, and in the one unit will be divided based on the extent of TBM and TM (if any).
        </fieldset>
         </td></tr></table>";
}else{
        echo"</table></fieldset>
         </td><td valign=top>
         <fieldset style='width:250px;'><legend>Info:</legend>
         Proses alokasi ini hanya akan berlaku untuk unit kebun dalam PT tujuan, dibagi berdasarkan luasan areal per unit kebun dalam satu PT, dan di dalam satu unit akan dibagi ber
         dasarkan luasan TBM dan TM (jika ada).
        </fieldset>
         </td></tr></table>";
}
CLOSE_BOX();
close_body();
?>