<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/pabrik_5submesin.js></script>
<?
include('master_mainMenu.php');


$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='STATION' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by kodeorganisasi asc";
$optOrg="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

$optKdTangki="<option value=''></option>";

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5submesin').'</span>');

?>
<br><fieldset style="float:left">
	<legend><?echo $_SESSION['lang']['form'];?></legend>
<table border="0" cellspacing="0">
  <tr>
    <td><?echo $_SESSION['lang']['station'];?>
    </td><td> : </td>
    <td colspan=4><select style="width:200px"  id="divId" onchange="getMesin(0,0)"><?echo $optOrg;?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['mesin'];?></td><td> : </td>
    <td colspan=4><select style="width:200px" onchange="getData(0,0)" id="msnId"><? echo $optKdTangki; ?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['kode']." ".$_SESSION['lang']['submesin'];?></td><td> : </td>
	<td><input type="text" class=myinputtext id="sbMesinCode"  style="width:117px" maxlength=15 onkeypress="return tanpa_kutip(event)" disabled><input type="text"  maxlength=3 class=myinputtext id="sbMesinCode2" placeholder="Auto Generate" style="width:75px" onkeypress="return tanpa_kutip(event)" readonly disabled /></td>
    </tr>
  <tr>
	
	<td><?echo $_SESSION['lang']['nama']." ".$_SESSION['lang']['submesin'];?></td><td> : </td>
    <td><input type="text"  class=myinputtext id="sbMesinNama" style="width:195px" onkeypress="return tanpa_kutip(event)"></td>
  </tr>
   <tr>
    <td><?echo $_SESSION['lang']['status'];?>
    </td><td> : </td>
    <td colspan=4><input type=checkbox id=statusDt>Check => Nonaktif, Uncheck => Aktif</td>
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
<fieldset>
<legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
<img src='images/pdf.jpg' title='PDF Format' style='width:20px;height:20px;cursor:pointer' onclick=\"printPDF(event)\">&nbsp;

<b id=caption></b>
      <table cellspacing=1 border=0 class=sortable style=min-width:600px>
      <thead>
	  <tr class=rowheader>
	  <td align=center>".$_SESSION['lang']['nourut']."</td>
	  <td align=center>".$_SESSION['lang']['station']."</td>
	  <td align=center>".$_SESSION['lang']['mesin']."</td>
	  <td align=center>".$_SESSION['lang']['kode']." ".$_SESSION['lang']['submesin']."</td>
	  <td align=center>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['submesin']."</td>
    <td align=center>".$_SESSION['lang']['status']."</td>
    <td align=center>".$_SESSION['lang']['updateby']."</td>
	  <td style='text-align:center'>".$_SESSION['lang']['action']."</td>
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