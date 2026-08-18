<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/pmn_feetbs.js?v=<?php echo time(); ?>></script>

<?


$optunit = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optposting = "<option value=''>".$_SESSION['lang']['all']."</option>";
$optposting.="<option value='0'>".$_SESSION['lang']['belumposting']."</option>";
$optposting.="<option value='1'>".$_SESSION['lang']['posting']."</option>";
// $arrunit = array();
// $arrunit = getOrgDetail(13);
// foreach ($arrunit as $val => $nama) {
//     $optunit .= "<option value='" . $val . "'>" . $nama . "</option>";
// }

$nmUnit  = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$str = "SELECT DISTINCT kodeunit FROM ".$dbname.".pmn_5feetbs ORDER BY kodeunit ASC";
$res = fetchdata($str);
foreach($res as $val){
	$optunit .= "<option value='".$val['kodeunit']."'>".$nmUnit[$val['kodeunit']]."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('pmn_feetbs').'</span>');

echo "<table>
   		<tr valign=middle>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=newdata()>
	  			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
	  		</td>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
	  			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
	  		</td>
	 		<td>

			<fieldset>
 			<legend id=legend>".$_SESSION['lang']['find']."</legend>
	 			<table>
	 				<tr>
						<td>".$_SESSION['lang']['notransaksi']."</td>
						<td>:</td>		
						<td><input type=text id=notranshr class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:155px'></td>		
						<td>".$_SESSION['lang']['unit']."</td>
					  	<td>:</td>
					  	<td>
					  		<select id='unithr' style='width:158px;' onchange=getTipe()>
					  			".$optunit."
					  		</select>
					  	</td>

						<td>".$_SESSION['lang']['tanggal']."</td>
					  	<td>:</td>
					  	<td>
					  		<input type=text id=tglhr class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:155px;>
					  	</td>
						
						<td>".$_SESSION['lang']['posting']."</td>
					  	<td>:</td>
					  	<td>
					  		<select id='postinghr' style='width:158px;' onchange=getTipe()>
					  			".$optposting."
					  		</select>
					  	</td>


						<td>
							<button class=mybutton id=cari onclick=\"loaddata();\">".$_SESSION['lang']['find']."</button>
						</td>
					</tr>
				</table>
			</fieldset>

			</td>
		</tr>
	 </table>"; 

CLOSE_BOX();

echo "<div id=entry style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['entryForm'].'</span>');

echo "<br><br>";
echo "<fieldset>";
echo "<table>";
echo "<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
	  	<td>:</td>
	  	<td><input type=text id=notrans class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:155px' readonly>&nbsp;</td>

		<td>Supplier</td>
	  	<td>:</td>
	  	<td>
	  		<select id='supplier' style='width:158px;'>
	  		</select>
            <img id=supplier onclick=z.elSearch('supplier',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
	  	</td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['unit']."</td>
	  	<td>:</td>
	  	<td>
	  		<select id='unit' style='width:158px;' onchange=getTipe()>
	  			".$optunit."
	  		</select>&nbsp;
	  	</td>

		<td>".$_SESSION['lang']['tanggal']."</td>
	  	<td>:</td>
	  	<td>
	  		<input type=text id=tgl class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:155px;>&nbsp;
	  	</td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['tipe']." TBS</td>
	  	<td>:</td>
	  	<td>
	  		<select id='tipetbs' style='width:158px;' onchange=getSup()>
	  			".$opttipe."
	  		</select>&nbsp;
	  	</td>

		<td>".$_SESSION['lang']['tanggal']." TBS</td>
	  	<td>:</td>
	  	<td>
	  		<input type=text id=tgl1 class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:63px;>
	  		s/d
	  		<input type=text id=tgl2 class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:64px;>&nbsp;
	  	</td>
	</tr>";
$optjenis="<option value='harian'>Harian</option>";
$optjenis.="<option value='bulanan'>Bulanan</option>";
echo "<tr>
		<td>".$_SESSION['lang']['jenis']."</td>
	  	<td>:</td>
	  	<td>
	  		<select id='jenis' style='width:158px;'>".$optjenis."</select>&nbsp;
	  	</td>
		
		<td colspan=6>Bulanan digunakan untuk menghitung fee bertingkat.</td>
	</tr>";
echo "<tr>
		<td colspan=2></td>
		<td>
			<button class=mybutton onclick=\"proses();\">".$_SESSION['lang']['proses']."</button>
		</td>
	</tr>";
echo "</table>";
echo "</fieldset>";

CLOSE_BOX();
echo "</div>";


#= buat data tersimpan
echo"<div id=loadpreview style=display:none>";
OPEN_BOX();

echo "<div id=listdata><script>loaddata();</script></div>";
// echo"<br>";

CLOSE_BOX();
echo"</div>";
#= tutup data tersimpan

echo close_body();
?>