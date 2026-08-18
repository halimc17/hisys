<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');

if($_SESSION['org']['period']['start']==''){
	$val1="<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Silahkan buat periode akutansi untuk unit ".$_SESSION['empl']['lokasitugas']." terlebih dahulu</span>";
	exit($val1);
}
if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
	$val2="<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Lokasi tugas anda di : ".$_SESSION['empl']['tipelokasitugas'].", silahkan pindah ke KEBUN terlebih dahulu.</span>";
	exit($val2);
}
?>
<script language=javascript1.2 src='js/temp_temp.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
$where="";
$where= " and induk = '".$_SESSION['empl']['lokasitugas']."'";
$wh= " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
# Organisasi
$optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' ".$wh."";
$res = fetchData($str);
foreach($res as $key => $val){
	if($_SESSION['empl']['lokasitugas']==$val['kodeorganisasi']){
		$optorg.="<option value=".$val['kodeorganisasi']." selected >".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}else{		
		$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}
}

# Posting
$arrPos=array("0"=>"Not Posted","1"=>"Posted");
$optPos="<option value=''></option>";
foreach($arrPos as $key => $val){
	@$optPos.="<option value=".$key.">".$val."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('temp_temp').'</span>','judul_header');
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
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['posting'] . "</td> 
				<td>:</td>
				<td><select id=postingsrc onchange='loaddata()' style=\"width:130px;\">".$optPos."</select>
				</td>
				
			</tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();

echo "<fieldset>
		<legend>".$_SESSION['lang']['list'] . "</legend>
		<div>    
			<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
					<tr class=rowheader>
						<td align=center width=50px>" . $_SESSION['lang']['nourut'] . "</td>
						<td align=center >" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center >" . $_SESSION['lang']['updateby'] . "</td>
						<td align=center colspan='6'>" . $_SESSION['lang']['action'] . "</td>
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