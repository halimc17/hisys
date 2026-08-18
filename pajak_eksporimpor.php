<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>


<script type="text/javascript1.2" src="js/pajak_eksporimpor.js?ver=2.9"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">

<?php


 
$optunit="";
$sunit = "select distinct a.kodept, b.namaorganisasi from " . $dbname . ".keu_penagihanht a 
left join organisasi b on a.kodept = b.kodeorganisasi ";
$qunit = $owlPDO->query($sunit) or die(print " Gagal: " . PDOException::getMessage());
$qunit->setFetchMode(PDO::FETCH_ASSOC);
$optunit.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($runit = $qunit->fetch()) {
    $optunit.="<option value=" . $runit['kodept'] . ">".$runit['namaorganisasi']."</option>";
}


  
OPEN_BOX('','<span class=judul>'.strtoupper('Ekspor Impor '.$_SESSION['lang']['transaksi']).' PERPAJAKAN</span>');
  echo "<div style=clear:both;></div><fieldset style='width:400px;float:left'>
    <legend>".$_SESSION['lang']['form']." </legend>
    <table cellpadding=1>
      <td>".$_SESSION['lang']['pt']."</td>
  <td>:</td>
  <td><select id=unit style='width:165px';  onchange=getnpwp()>".$optunit."</select></td>
  <tr>
  <td>".$_SESSION['lang']['npwp']."</td>
  <td>:</td>
  <td><select id=npwp style='width:165px'; onchange=getnoakun()>".$optnpwp."</select></td>
  </tr>
  <tr>
  <td>".$_SESSION['lang']['noakun']."</td>
  <td>:</td>
  <td><select id=noakun style='width:165px'>".$optnoakun."</select></td>
  </tr>
   <tr>
  <td>".$_SESSION['lang']['tanggal']."</td>
  <td>:</td>
   <td><input type=text class=myinputtext id=tanggal1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=9 maxlength=10 />
   <input type=text class=myinputtext id=tanggal2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=9 maxlength=10 /></td>
  </tr>
  
      <tr>
        <td colspan=2></td>
        <td>
          <button class=mybutton style='width:80px'; onclick=prosesdata()>".$_SESSION['lang']['proses']."</button>
          <button class=mybutton style='width:80px'; onclick=csv()>CSV</button>
        </td>
      </tr>
    </table>
  </fieldset>
  ";


CLOSE_BOX();

OPEN_BOX();

  echo "<fieldset style='width:99%;'>
    <legend>".$_SESSION['lang']['data']."</legend>
    <table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
      <thead>
        <tr class=rowheader>
          <td>".$_SESSION['lang']['nourut']."</td>
          <td align=center>Jenis PPh yang dipotong</td>
          <td align=center>Cara pembayaran</td>
          <td align=center>Jenis Penghasilan</td>
          <td align=center>".$_SESSION['lang']['nobuktipotong']."</td>
          <td align=center>Jenis Penghasilan</td>
          <td align=center>Objek Pemotong</td>
          <td align=center>PPh yang dipotong/ dipungut</td>
          <td align=center>Tanggal Bukti Potong</td>
          <td align=center>".$_SESSION['lang']['npwp']." pemotong/ pemungut</td>
          <td align=center>Nama pemotong/ pemungut</td>
          <td align=center>Alamat pemotong/ pemungut</td>
          <td align=center>Kode MAP/ iuran pembayaran</td>
          <td align=center>NTPP</td>
          <td align=center>Jumlah pembayaran</td>
          <td align=center>Tanggal setor</td>
          
        </tr>
      </thead>
      <tbody id=container>
       
      </tbody>
      <tfoot>

      </tfoot>
    </table>
  </fieldset>";
CLOSE_BOX();
echo close_body();
?>