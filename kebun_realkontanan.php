<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/kebun_realkontanan.js?v=1.3'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
# Organisasi
$optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
$res = fetchData($str);
$optorg.="<option value=".$res[0]['kodeorganisasi'].">".$res[0]['kodeorganisasi']." - ".$res[0]['namaorganisasi']."</option>";

# Divisi
$optdiv = "<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' and induk = '".$_SESSION['empl']['lokasitugas']."'";
$res = fetchData($str);
foreach($res as $key => $val){
	$optdiv.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']."</option>";	
}

# Posting
$arrPos=array("0"=>"Belum di Ajukan","1"=>"Disetujui","2"=>"Dikembalikan / Koreksi","3"=>"Ditolak");
$optPos="<option value=''></option>";
foreach($arrPos as $key => $val){
	@$optPos.="<option value=".$key.">".$val."</option>";
}

# Periode
$optprd = "<option value=''></option>";
$str="select DISTINCT (substr(tanggal,1,7)) as prd from ".$dbname.".kebun_aktifitas where kodeorg = '".$_SESSION['empl']['lokasitugas']."' order by prd desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$optprd.="<option value=".$val['prd'].">".$val['prd']."</option>";	
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_realkontanan').'</span>','judul_header');
# === Header dan Pencarian data ===
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
			   <td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
			   <td><input type=text class=myinputtext id=notransaksisch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>

				<td>Persetujuan</td> 
				<td>:</td>
				<td><select id=postingsrc onchange='loaddata()' style=\"width:130px;\">".$optPos."</select>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:130px;' class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
				</td>
				
				
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=periodesch onchange='loaddata()' style=\"width:130px;\">".$optprd."</select>
				</td>
				
			</tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();
$rows=" rowspan=2";
echo "<fieldset>
		<legend>".$_SESSION['lang']['list'] . "</legend>
		<div>    
			<table cellpading=1 cellspacing=1 border=0 class=sortable >
				<thead>
					<tr class=rowheader>
						<td align=center ".$rows." width=20px>No</td>
						<td align=center ".$rows." >Notransaksi</td>
						<td align=center ".$rows.">".$_SESSION['lang']['unit']."</td>
						<td align=center ".$rows.">".$_SESSION['lang']['tanggal']."</td>
						<td align=center ".$rows.">Kg Kirim ke PKS</td>
						<td align=center colspan=3>Upah</td>
						<td align=center ".$rows.">Total</td>
						<td align=center ".$rows.">Status</td>
						<td align=center colspan=5 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
					</tr>
					<tr>
						<td align=center>Pemanen</td>
						<td align=center>Pengawas</td>
						<td align=center>BM TBS</td>
					</tr>
				</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>
	 </fieldset>";
CLOSE_BOX();
echo "</div>";

# === Form header input data ===
echo "<div id=header style=display:none>";
OPEN_BOX('','','header_trans');
echo "<fieldset style=float:left>
		<legend>Header</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input id=notransaksi style='width:145px;' class='myinputtext' disabled/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select onchange=getnotransaksi() style=\"width:150px;\" id=kodeorg>" . $optorg . "</select></td>
				
				<td>".$_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text onchange=getnotransaksi() class=myinputtext style='width:145px;' id=tgl onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 /></td>
				
			</tr> 
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=addHeader()>" . $_SESSION['lang']['save'] . "</button>
					<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=mode value='baru'>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";

# === Form Detail Input Data ===
echo"<div id=detailx style=display:none>";
OPEN_BOX();
echo"<div id=detail style=display:none>";
echo"</div>";
CLOSE_BOX();
echo"</div>";

echo close_body();
?>