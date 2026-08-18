<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script type="text/javascript" src="js/pajak_suratpenunjukanpajak.js?ver=2.0"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">

<?php
 
$optkary="";
$skary = "select karyawanid, namakaryawan from " . $dbname . ".datakaryawan where tipekaryawan='7' and namakaryawan !='administrator' order by namakaryawan asc ";
$qkary = $owlPDO->query($skary) or die(print " Gagal: " . PDOException::getMessage());
$qkary->setFetchMode(PDO::FETCH_ASSOC);
$optkary.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($rkary = $qkary->fetch()) {
    $optkary.="<option value=" . $rkary['karyawanid'] . ">".$rkary['karyawanid']."-" . $rkary['namakaryawan'] . "</option>";
}

$optpenerimakuasa="";
$str = "select karyawanid,namakaryawan from " . $dbname . ".datakaryawan where bagian='FNC' and tipekaryawan not in (1,3,6)  order by namakaryawan asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optpenerimakuasa.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch()) {
    $optpenerimakuasa.="<option value=" . $bar['karyawanid'] . ">".$bar['karyawanid']."-".$bar['namakaryawan']."</option>";
}

  
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['surat']).' PENUNJUKAN KUASA PAJAK</span>');
  echo "<fieldset style='width:400px;'>
    <legend>".$_SESSION['lang']['form']."</legend>
    <table cellpadding=1>
      <td>Pemberi Kuasa</td>
  <td>:</td>
  <td><select id=pemberikuasa style='width:175px'>".$optkary."</select></td>
  <tr>
  <td>Kuasa dari wajib pajak</td>
  <td>:</td>
  <td><input type=text id=kuasadariwajibpajak style='width:175px' class=myinputtext></td>
  </tr>
  <tr>
  <td>Nomor Surat Kuasa Khusus</td>
  <td>:</td>
  <td><input type=text id=nomorsuratkhusus style='width:175px' class=myinputtext></td>
  </tr>
   <tr>
  <td>Tanggal Surat Kuasa Khusus</td>
  <td>:</td>
   <td><input type=text class=myinputtext id=tanggalsuratkhusus onmousemove=setCalendar(this.id) onkeypress=return false;  size=25 maxlength=10 /></td>
  </tr>
  <td>Penerima Kuasa</td>
  <td>:</td>
  <td><select id=penerimakuasa style='width:175px'>".$optpenerimakuasa."</select></td>
  <tr>
   <td>Berupa (7)</td>
  <td>:</td>
  <td><input type=text id=berupa7 style='width:175px' class=myinputtext></td>
  </tr>
  <tr>
  <td>Berupa (8)</td>
  <td>:</td>
  <td><input type=text id=berupa8 style='width:175px' class=myinputtext></td>
  </tr>
  <tr>
  <td>Kota</td>
  <td>:</td>
  <td><input type=text id=kota style='width:175px' class=myinputtext></td>
  </tr>
  <tr>
  <td>Tanggal Surat Kuasa</td>
  <td>:</td>
  <td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=25 maxlength=10 />
  </tr>
  <tr>
    <td><input type=hidden id=id style='width:175px' class=myinputtext></td>
  </tr>
      <tr>
        <td colspan=2></td>
        <td>
          <input type=hidden id='method' value='insert' />
          <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
          <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
        </td>
      </tr>
    </table>
  </fieldset>";

echo "<fieldset style='width:400px;'><legend>".$_SESSION['lang']['find']."</legend>"; 
echo "Tanggal Surat Kuasa : <input type=text class=myinputtext id=tanggal1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /> s/d  <input type=text class=myinputtext id=tanggal2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
echo" <button class=mybutton onclick=caridata()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset>";

CLOSE_BOX();

OPEN_BOX();

  echo "<fieldset style='width:99%;'>
    <legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['surat']."</legend>
    <table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
      <thead>
        <tr class=rowheader>
          <td>".$_SESSION['lang']['nourut']."</td>
          <td align=center>Pemberi Kuasa</td>
          <td align=center>".$_SESSION['lang']['npwp']."</td>
          <td align=center>Kuasa dari wajib pajak</td>
          <td align=center>Nomor Surat Kuasa Khusus</td>
          <td align=center>Tanggal Surat Kuasa Khusus</td>
          <td align=center>Penerima Kuasa</td>
          <td align=center>".$_SESSION['lang']['npwp']."</td>
          <td align=center>".$_SESSION['lang']['jabatan']."</td>
          <td align=center>Tanggal Surat Kuasa</td>
          <td colspan=3 style='text-align:center;'>".$_SESSION['lang']['action']."</td>
        </tr>
      </thead>
      <tbody id=container>
        <script>loadData()</script>
      </tbody>
      <tfoot>

      </tfoot>
    </table>
  </fieldset>";
CLOSE_BOX();
echo close_body();
?>