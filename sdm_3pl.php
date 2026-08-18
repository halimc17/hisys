<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src='js/sdm_3pl.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?
$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');


$lstorg = array();

$optOrg2 = getOrgDetail(1);
$dtisi = 1;
$lstorg = array();
$optTipePot = $optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach ($optOrg2 as $key => $nmorg) {
	$sGaji = "select distinct * from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $key . "'";
	$rGaji = fetchData($sGaji);
	if (count($rGaji) > 0) {
		$lstorg[$key] = $key;
		$optOrg .= "<option value=" . $key . ">" . $key . "-" . $nmorg . "</option>";
	}
}

##periode
$optPer = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sGet = "select distinct periode from " . $dbname . ".sdm_5periodegaji where kodeorg in ('" . implode("','", $lstorg) . "')
		 and sudahproses=0 and jenisgaji='H' order by periode desc";
$qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
$qGet->setFetchMode(PDO::FETCH_ASSOC);
while ($rGet = $qGet->fetch()) {
	$optPer .= "<option value=" . $rGet['periode'] . ">" . $rGet['periode'] . "</option>";
}


##jabatan
$optjab = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select distinct a.kodejabatan,b.namajabatan from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "' and tipekaryawan not in (0) order by namajabatan";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optjab .= "<option value='" . $bar['kodejabatan'] . "'>" . $bar['namajabatan'] . "</option>";
}

##tipekaryawan
$opttpkar = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
	$str = "select * from " . $dbname . ".sdm_5tipekaryawan where  aktif='1' order by id";
} else {
	$str = "select * from " . $dbname . ".sdm_5tipekaryawan where id!='0' and aktif='1' order by id";
}
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$opttpkar .= "<option value='" . $bar['id'] . "'>" . $bar['tipe'] . "</option>";
}

##jenis komponen
$optJns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select id,name from " . $dbname . ".sdm_ho_component where plus='1' and type='additional' and `lock`='0' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optJns .= "<option value='" . $bar['id'] . "'>" . $bar['name'] . "</option>";
}
$optJns .= "<option value='1'>Gaji Pokok KHL/PHL</option>";
##karyawan
$iKar = "select namakaryawan,karyawanid,nik,subbagian,lokasitugas from " . $dbname . ".datakaryawan where  1=1 and tipekaryawan not in ('0')  order by namakaryawan";
$nKar = $owlPDO->query($iKar) or die(print " Gagal: " . PDOException::getMessage());
$nKar->setFetchMode(PDO::FETCH_ASSOC);
$optKar = "<option value=''>Pilih Data</option>";
while ($dKar = $nKar->fetch()) {
	$optKar .= "<option value='" . $dKar['karyawanid'] . "'>" . $dKar['nik'] . " - " . $dKar['namakaryawan'] . "</option>";
}


?>
<?php
OPEN_BOX('', '<span class=judul>' . getMenu('sdm_3pl') . '</span>');
echo "<table>
     <tr valign=middle>";
echo "<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>";
echo "<td align=center style='width:100px;cursor:pointer;' onclick=add_upload()>
	<img class=delliconBig src=images/skyblue/upload.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['upload'] . " Data </td>";
echo "<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset id=formpencarianheader><legend>" . $_SESSION['lang']['find'] . "</legend> 
        <table>
		<tr>
			<td>" . $_SESSION['lang']['periode'] . "</td>
			<td>:</td>
			<td><input type=text class=myinputtext id=perSch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:150px;\" onkeypress='enterkey(event,loadData)' />
			</td>
		</tr>";
echo "<tr>
		<td colspan=2></td>
		<td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button>
			<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr>
</table>";

echo "</fieldset></table>";
CLOSE_BOX();

echo "<div id=listData style='display:block'>";
OPEN_BOX();
echo "<fieldset style=min-height:400px><legend><b>" . $_SESSION['lang']['list'] . "</b></legend>
	<div>    
	<table class=sortable cellspacing=1 cellpadding =5 border=0 style='width:100%;'>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
				<td align=center>" . $_SESSION['lang']['periodegaji'] . "</td>
				<td align=center>" . $_SESSION['lang']['jenis'] . "</td>
				<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
				<td align=center>" . $_SESSION['lang']['dibuat'] . "</td>
				<td align=center>" . $_SESSION['lang']['updatetime'] . "</td>
				<td align=center colspan=6>" . $_SESSION['lang']['action'] . "</td>
			</tr>
		</thead>
			<tbody id=container> 
				<script>loadData(0)</script>
			</tbody>
			<tfoot id=footData>
			</tfoot>
		 </table>
		 </div>
		 
</div></fieldset>";
CLOSE_BOX();
echo "</div>";

echo "<div id=detail style=display:none>";
OPEN_BOX();


echo "<fieldset><legend><b>Form</b></legend>
<table border=0 cellpadding=3 cellspacing=1 style='display: inline-block;vertical-align:top'>
	<input hidden id=stsawal value=''>
	<input hidden id=methodheader value='insertheader'>
    <tr>
		<td>" . $_SESSION['lang']['kodeorg'] . "</td> 
		<td>:</td>
		<td>
			<select id=org style=\"width:150px;\" onchange=getPrd() >" . $optOrg . "</select>
		</td>

		<td>" . $_SESSION['lang']['jabatan'] . "</td> 
		<td>:</td>
		<td>
			<select id=jabatan style=\"width:150px;\">" . $optjab . "</select>
			<img id='jabatan' onclick=z.elSearch('jabatan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
	</tr> 

	<tr>
		<td>" . $_SESSION['lang']['tipekaryawan'] . "</td> 
		<td>:</td>
		<td>
			<select id=tipekar style=\"width:150px;\">" . $opttpkar . "</select>
			<img id='tipekar' onclick=z.elSearch('tipekar',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>

		<td>" . $_SESSION['lang']['periodegaji'] . "</td> 
		<td>:</td>
		<td><select id=per style=\"width:150px;\">" . $optPer . "</select></td>
	</tr> 
	<tr>
		<td>" . $_SESSION['lang']['jenis'] . "</td> 
		<td>:</td>
		<td>
			<select id=kom style=\"width:150px;\">" . $optJns . "</select>
			<img id='kom' onclick=z.elSearch('kom',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>

		</td>

	</tr> 
	<tr>
		<td><td><td>
		<button class=mybutton id=saveHeader onclick=saveHeader()>" . $_SESSION['lang']['save'] . "</button>
		<button class=mybutton id=cancelHeader  onclick=cancelHeader()>" . $_SESSION['lang']['cancel'] . "</button>	
		</td>
	</tr> 
	";
echo "</table>";
echo "</fieldset>";

echo "<div id='displayinsert' style=display:none></div>";
#echo"</div>";
echo "<div id='inputdetail' style=display:none>
		<fieldset><legend><b>" . $_SESSION['lang']['detail'] . "</b></legend>
		<table class=sortable cellspacing=1 cellpadding =5 border=0 >

		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
				<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
                <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
			</tr>
		</thead>

		<tr class=rowcontent>
			<td align=center> 
				<select style='width:70%;' id=kar>" . $optKar . "</select>
				<img id='kar' onclick=z.elSearch('kar',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
			<td align=center>
				<input type=number class=myinputtextnumber style='width:100%;' id=jum onkeypress='return angka_doang(event)'>
			</td>
			<td align=center>
				<input type=text class=myinputtext style='width:100%;' id=ket>
			</td>
		</tr>	
		<tr>	
			<td hidden><input id=saveDetail value='saveDetail' hidden></td>
			<td align=center colspan=3><button class=mybutton onclick=saveDetail()>" . $_SESSION['lang']['save'] . "</button></td>
		</tr> 
		</table></fieldset>
	</div>";

echo "<div id='loaddatadetail' style=display:none></div>";

CLOSE_BOX();
echo "</div>";
echo "<div id='displayupload' style='display:none'>";
OPEN_BOX();
echo "<div id='formuploaddata'></div>";
CLOSE_BOX();
echo "</div>";
echo close_body();
?>