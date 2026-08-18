<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/pabrik_5suhu.js></script>
<?
include('master_mainMenu.php');
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc";
$optOrg="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

        

$sKdTangki="select kodetangki, keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by keterangan asc";
$optKdTangki="<option value=''></option>";

$qKdTangki=$owlPDO->query($sKdTangki) or die(print " Gagal: ".PDOException::getMessage());
$qKdTangki->setFetchMode(PDO::FETCH_ASSOC);
while($rKdTangki=$qKdTangki->fetch())
{
    $optKdTangki.="<option value='".$rKdTangki['kodetangki']."'>".$rKdTangki['kodetangki']." - ".$rKdTangki['keterangan']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5suhu').'</span>');

?>
<fieldset style="width:500px;">
	<legend><?echo $_SESSION['lang']['suhu'];?></legend>
<table border="0" cellspacing="0">
  <tr>
    <td><?echo $_SESSION['lang']['kodeorg'];?>
    </td><td> : </td>
    <td colspan=4><select style="width:128px"  id="kodeorg"><?echo $optOrg;?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['kodetangki'];?></td><td> : </td>
    <td colspan=4><select style="width:194px"  id="kodetangki"><? echo $optKdTangki; ?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['suhu'];?></td><td> : </td>
	<td><input type="text"  class=myinputtextnumber id="suhu" value=0 size=5 maxlength=10 onkeypress="return angka_doang(event)"></td>
  
    <td><?echo $_SESSION['lang']['beratjenis'];?></td><td> : </td>
    <td><input type="text"  class=myinputtextnumber id="beratjenis" value=0 size=5 maxlength=10 onkeypress="return angka_doang(event)"></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['varian'];?></td><td> : </td>
	<td colspan=2><input type="text"  class=myinputtextnumber id="varian" value=0 size=17 maxlength=10 onkeypress="return angka_doang(event)"></td>
  </tr>
  <input type=hidden value='insert' id=method>

<tr><td><td><td colspan=3>
<button class=mybutton onclick=simpan()><?echo $_SESSION['lang']['save'];?></button>
<button class=mybutton onclick=cancel()><?echo $_SESSION['lang']['cancel'];?></button>
</td></td></td></tr>
</table>
</fieldset>
<?
CLOSE_BOX();
OPEN_BOX();

echo "
<fieldset style='width:500px;'>
<legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
<img src='images/pdf.jpg' title='PDF Format' style='width:20px;height:20px;cursor:pointer' onclick=\"printPDF(event)\">&nbsp;

<b id=caption></b>
      <table cellspacing=1 border=0 class=sortable>
      <thead>
	  <tr class=rowheader>
	  <td align=center>".$_SESSION['lang']['nourut']."</td>
	  <td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
	  <td align=center>".$_SESSION['lang']['kodetangki']."</td>
	  <td align=center>".$_SESSION['lang']['suhu']."</td>
	  <td align=center>".$_SESSION['lang']['beratjenis']."</td>
	  <td align=center>".$_SESSION['lang']['varian']."</td>
	  <td align=center>".$_SESSION['lang']['updateby']."</td>
	  <td colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</td>
	  </tr>
	  </thead>
	  <tbody id=container>
	  <script>loadData()</script>
	  </tbody>
	  <tfoot>
	  </tfoot>
	  </table>
</div>
</div>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>