<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/pabrik_2stokbulkingpt.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('pabrik_2stokbulkingpt')."<br></span>");


$optunit=$optkodebarang="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";


$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('BULKING')";
$res=fetchdata($str);
foreach($res as $bar){
  $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
} 

$str="select * from ".$dbname.".log_5masterbarang where kodebarang in ('40000001','40000002')";
$res=fetchdata($str);
foreach($res as $bar){
  $optkodebarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
} 

echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit style='width:200px;'>".$optunit."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['komoditi']." </td>
        <td>:</td>
        <td><select id=kodebarang style='width:200px;'>".$optkodebarang."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td>
            <input type=text class=myinputtext id=tanggal1 style='width:80px;' readonly onmousemove=setCalendar(this.id)  onkeypress=return false;  size=10 maxlength=10 /> S/D
            <input type=text class=myinputtext id=tanggal2 style='width:80px;' readonly onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />
        </td>
      </tr>
      <tr>
        <td colspan=2></td>
        <td colspan=4>
			<button class=mybutton onclick=preview('html')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=preview('excel')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=preview('pdf') hidden>".$_SESSION['lang']['pdf']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
      <tr>
     </table>
   </fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"<div class='table-scroll' style='height:55vh;' id=container></div>";
CLOSE_BOX();
close_body();
?>