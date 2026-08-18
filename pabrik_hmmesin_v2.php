<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
OPEN_BOX('', '<span class=judul>' . getMenu('pabrik_hmmesin_v2') . '</span>');
?>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<script type='text/javascript' language='javascript' src='js/zMaster.js'></script>
<script type='text/javascript' language='javascript' src='js/zTools.js'></script>
<script language=javascript src='js/pabrik_hmmesin_v2.js?v=<?php echo time(); ?>'></script>

<?php
### Get MILL
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='PABRIK' order by namaorganisasi";
$res = fetchdata($str);
foreach ($res as $key => $val) {
    @$optorganisasi .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['namaorganisasi'] . "</option>";
}

### Get Station
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='STATION' order by namaorganisasi";
$res = fetchdata($str);
foreach ($res as $key => $val) {
    @$optstation .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['namaorganisasi'] . "</option>";
}

echo "<table>
	<tr valign=middle>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
			<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td>
			<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";

echo "<table><tr>";

echo "</tr><tr>";

echo "<td>" . $_SESSION['lang']['tanggal'] . "</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=caritanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>
		</td>";

echo "</tr>
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=cariData(0)>" . $_SESSION['lang']['find'] . "</button></td>
		</tr>";
echo "</table>";
echo "</fieldset></td>
     </tr>
	 </table> ";

CLOSE_BOX();

OPEN_BOX();
echo "<div id=listData>";
echo "<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>";
echo "<div class='table-scroll' style=''>";
echo "<table cellpading=4 cellspacing=1 border=0 class=sortable width=100%>";
echo "<thead>";
echo "<tr align=center><td>" . $_SESSION['lang']['unit'] . "</td>";
echo "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
echo "<td>" . $_SESSION['lang']['station'] . "</td>";
echo "<td>" . $_SESSION['lang']['updateby'] . "</td>";
echo "<td>" . $_SESSION['lang']['status'] . "</td>";
echo "<td colspan=4>" . $_SESSION['lang']['action'] . "</td>";
echo "</tr></thead><tbody id=continerlist>";
echo "<script>loadData(0)</script>";
echo "</tbody>";
echo "<tfoot id=footData>";

echo "</tfoot></table></div></fieldset>";
echo "</div>";

//===========================================================================
echo "<div id=formInput style=display:none;>";
echo "<fieldset style=float:left;><legend>" . $_SESSION['lang']['form'] . "</legend>
	<table style=width:100%;>
		<tr>
			<td>" . $_SESSION['lang']['unit'] . "</td>
			<td>:</td>
			<td>
				<select id=unit>" . $optorganisasi . "</select>
			</td>	
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tanggal'] . "</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tanggal style='text-align:left' onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='" . date('d-m-Y') . "' readonly />
			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['station'] . "</td>
			<td>:</td>
			<td>
				<select id=station>" . $optstation . "</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type='hidden' id='proses' value='insertht'>
				<button class=mybutton id='simpanht' onclick=saveht()>" . $_SESSION['lang']['preview'] . "</button>&nbsp;
				<button class=mybutton id='cancelht' onclick=cancelht()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>
	</table>
</fieldset>
<div  id='formdt' style='clear:both'></div>
    </tbody>
    </table>
</fieldset>";
echo "</div>";
CLOSE_BOX();
echo close_body();
?>