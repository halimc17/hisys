<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/sdm_5harilibur.js'></script>

<?php

$arr = "##kebun##tanggal##keterangan##catatan";

$optKebun = "";
$optKebun.="<option value='GLOBAL'>GLOBAL</option>";
$optKebun.="<option value='HOLDING'>HOLDING</option>";
$strKebun = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
$resKebun=$owlPDO->query($strKebun) or die(print " Gagal: ".PDOException::getMessage());
$resKebun->setFetchMode(PDO::FETCH_OBJ);
while ($barKebun = $resKebun->fetch()) {
    $optKebun.="<option value='" . $barKebun->kodeorganisasi . "'>" . $barKebun->kodeorganisasi . " - " . $barKebun->namaorganisasi . "</option>";
}

$arrketerangan = getEnum($dbname, 'sdm_5harilibur', 'keterangan');
$optketerangan = "";
foreach ($arrketerangan as $kei => $fal) {
    $optketerangan.="<option value='" . $kei . "'>" . $fal . "</option>";
}

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['harilibur']).'</span>');

echo"<fieldset>
     <legend>" . $_SESSION['lang']['harilibur'] . "</legend>
	 <table cellspacing=2>
	 <tr>
	   <td>" . $_SESSION['lang']['kebun'] . "</td>
	   <td><select id=\"kebun\" name=\"kebun\" style=\"width:200px\">" . $optKebun . "</select></td>
	 </tr>
	 <tr>
	   <td>" . $_SESSION['lang']['tanggal'] . "</td>
	   <td><input id=\"tanggal\" name=\"tanggal\" class=\"myinputtext\" onkeypress=\"return tanpa_kutip(event)\" style=\"width:197px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\"></td>
	 </tr>
        <tr>
            <td><label>" . $_SESSION['lang']['keterangan'] . "</label></td>
            <td><select id=\"keterangan\" name=\"keterangan\" style=\"width:200px\">" . $optketerangan . "</select></td>
            <td>&nbsp;</td>
        </tr>         
	 <tr>
	   <td>" . $_SESSION['lang']['catatan'] . "</td>
	   <td><input type=text class=myinputtext id=catatan name=catatan onkeypress=\"return tanpa_kutip(event);\" style=\"width:197px;\" /></td>
	 </tr>
	 
	 </table>
         <input type=hidden value=insert id=method>
         <button  class=mybutton onclick=savehk('sdm_slave_5harilibur','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>
         <button class=mybutton onclick=cancelIsi()>" . $_SESSION['lang']['cancel'] . "</button>
     </fieldset><input type='hidden' id=oldtanggal name=oldtanggal />";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset style='width:99%;' ><legend>" . $_SESSION['lang']['find'] . "</legend>";
echo "<td>".$_SESSION['lang']['tanggal'] . "</td>
		<td>:</td>
		<td>
			<input style='width:100px;' type=text class='myinputtext' id='tglcr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' readonly/>
	 	</td>";
echo"<td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";
echo"</table>";
echo"</fieldset>";
echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend><table class=sortable cellspacing=1 cellpadding=7 style='width:35%;' border=0>
     <thead>
	  <tr align=center class=rowheader>
	   <td>No</td>
	   <td>" . $_SESSION['lang']['kebun'] . "</td>
	   <td>" . $_SESSION['lang']['tanggal'] . "</td>
	   <td>" . $_SESSION['lang']['keterangan'] . "</td>
	   <td>" . $_SESSION['lang']['catatan'] . "</td>
	   <td>" . $_SESSION['lang']['action'] . "</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>loadData(0)</script>";
echo"</tbody>
     <tfoot id='footData'>
     </tfoot>
     </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>