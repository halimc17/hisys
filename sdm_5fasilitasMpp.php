<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/sdm_5fasilitasMpp.js'></script>
<?php

$arr = "##thnBudget##kdJabatan##kdBarang##hrgSat##sat##jmlhBrng##method##totBrg##oldKdBrg";
include('master_mainMenu.php');
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('MPP FACILITY').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('FASILITAS MPP').'</span>');
}
$optGol = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sGol = "select * from " . $dbname . ".sdm_5jabatan order by namajabatan asc";
$qGol=$owlPDO->query($sGol) or die(print " Gagal: ".PDOException::getMessage());
$qGol->setFetchMode(PDO::FETCH_ASSOC);
while ($rGol = $qGol->fetch()) {
    $optGol.="<option value='" . $rGol['kodejabatan'] . "'>" . $rGol['namajabatan'] . "</option>";
}
$optReg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sReg = "select distinct namabarang,kodebarang from " . $dbname . ".log_5masterbarang order by namabarang asc";
$qReg=$owlPDO->query($sReg) or die(print " Gagal: ".PDOException::getMessage());
$qReg->setFetchMode(PDO::FETCH_ASSOC);
while ($rReg = $qReg->fetch()) {
    $optReg.="<option value='" . $rReg['kodebarang'] . "'>" . $rReg['kodebarang'] . " [" . $rReg['namabarang'] . "]</option>";
}
echo"<input type='hidden' id='method' name='method' value='insert' />";

echo"<fieldset style=width:290px;>
     <legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['fasiltasmpp'] . "</legend>
	 <table>
	 <tr>
	   <td>" . $_SESSION['lang']['budgetyear'] . "</td>
	   <td><input type=text class=myinputtextnumber id=thnBudget name=thnBudget onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=4 /></td>
	 </tr>
	 <tr>
	   <td>" . $_SESSION['lang']['kodejabatan'] . "</td>
	   <td><select id=kdJabatan name=kdJabatan style=\"width:150px;\" >" . $optGol . "</select></td>
	 </tr>
	 <tr>
       <tr><td>" . $_SESSION['lang']['namabarang'] . "</td>
	   <td><select id=kdBarang name=kdBarang style=\"width:150px;\" onchange='getSatuan()' >" . $optReg . "</select>&nbsp;<img src=\"images/search.png\" class=\"resicon\" title='" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "' onclick=\"searchBrg('" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "','<fieldset><legend>" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "</legend>" . $_SESSION['lang']['find'] . "&nbsp;<input type=text class=myinputtext id=nmBrg><button class=mybutton onclick=findBrg()>" . $_SESSION['lang']['find'] . "</button></fieldset><div id=containerBarang style=overflow=auto;height=380;width=485></div>',event);\"></td>
	 </tr>	
	 <tr>
	   <td>" . $_SESSION['lang']['hargasatuan'] . "</td>
	   <td><input type=text class=myinputtextnumber id=hrgSat name=hrgSat  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20 onblur=\"kalikan()\"></td>
	 </tr>	 
	  <tr>
	   <td>" . $_SESSION['lang']['satuan'] . "</td>
	   <td><input type=text class=myinputtextnumber id=sat name=sat disabled onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20></td>
	 </tr>	
           <tr>
	   <td>" . $_SESSION['lang']['jumlah'] . "</td>
	   <td><input type=text class=myinputtextnumber id=jmlhBrng name=jmlhBrng  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20 onblur=\"kalikan()\"></td>
	 </tr>	
           <tr>
	   <td>" . $_SESSION['lang']['total'] . "</td>
	   <td><input type=text class=myinputtextnumber disabled id=totBrg name=totBrg  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20></td>
	 </tr>	

	 </table>
	 <button class=mybutton onclick=saveFranco('sdm_slave_5fasilitasMpp','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>
        <button class=mybutton onclick=cancelIsi()>" . $_SESSION['lang']['cancel'] . "</button>
<input type=hidden id=oldKdBrg value='' />
     </fieldset>";
echo"</div>";
CLOSE_BOX();
OPEN_BOX();
$optData = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str = "select distinct tahunbudget from " . $dbname . ".sdm_5transportpjd order by tahunbudget desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($rData = $res->fetch()) {
    $optData.="<option value='" . $rData['tahunbudget'] . "'>" . $rData['tahunbudget'] . "</option>";
}
echo"<table><tr>
    <td>" . $_SESSION['lang']['budgetyear'] . " <select id=thnBudgetHead style='width:100px' onchange='loadData()'>" . $optData . "</select></td>
    <td>" . $_SESSION['lang']['kodejabatan'] . " <select id=kdJabtanHead style='width:100px' onchange='loadData()'>" . $optGol . "</select></td>
   
    </tr></table>";
echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>" . $_SESSION['lang']['budgetyear'] . "</td>
	   <td>" . $_SESSION['lang']['kodejabatan'] . "</td>
	   <td>" . $_SESSION['lang']['namabarang'] . "</td>
	   <td>" . $_SESSION['lang']['hargasatuan'] . "</td>
	   <td>" . $_SESSION['lang']['satuan'] . "</td>
            <td>" . $_SESSION['lang']['jumlah'] . "</td>
            <td>" . $_SESSION['lang']['total'] . "</td>

	   <td>Action</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>loadData()</script>";
echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>