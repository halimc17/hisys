<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include_once('lib/zLib.php');
require_once('lib/zSelect2.php');
?>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth: true
		});
	});
</script>
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
##filter pencarian: kode organisasi dan jenis pendapatan yang ada di data (sesuai PT yang login)
$optOrgSch = $optKomSch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$whrSch = "left(kodeorg,4) in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $_SESSION['empl']['kodeorganisasi'] . "')";
$resSch = fetchData("select distinct kodeorg from " . $dbname . ".sdm_pendapatanlainht where " . $whrSch . " order by kodeorg");
foreach ($resSch as $barSch) {
	$optOrgSch .= "<option value='" . $barSch['kodeorg'] . "'>" . $barSch['kodeorg'] . " - " . $nmOrg[$barSch['kodeorg']] . "</option>";
}
$nmKomSch = makeOption($dbname, 'sdm_ho_component', 'id,name');
$arrKomSch = array();
$resSch = fetchData("select distinct idkomponen from " . $dbname . ".sdm_pendapatanlainht where " . $whrSch);
foreach ($resSch as $barSch) {
	$arrKomSch[$barSch['idkomponen']] = $nmKomSch[$barSch['idkomponen']];
}
$optPostSch = "<option value=''>" . $_SESSION['lang']['all'] . "</option><option value='1'>Posted</option><option value='0'>Belum Posting</option>";
asort($arrKomSch);
foreach ($arrKomSch as $idKomSch => $namaKomSch) {
	$optKomSch .= "<option value='" . $idKomSch . "'>" . $namaKomSch . "</option>";
}
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
			<td nowrap>" . $_SESSION['lang']['kodeorg'] . "</td>
			<td>:</td>
			<td><select id=orgSch class=select2 onchange='loadData(0)' style=width:180px;>" . $optOrgSch . "</select></td>
			<td nowrap>" . $_SESSION['lang']['periode'] . "</td>
			<td>:</td>
			<td><input type=text class=myinputtext id=perSch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:180px;\" onkeypress='enterkey(event,loadData)' />
			</td>
		</tr>
		<tr>
			<td nowrap>Jenis Pendapatan</td>
			<td>:</td>
			<td><select id=komSch class=select2 onchange='loadData(0)' style=width:180px;>" . $optKomSch . "</select></td>
			<td nowrap>Status Posting</td>
			<td>:</td>
			<td><select id=postSch class=select2 onchange='loadData(0)' style=width:180px;>" . $optPostSch . "</select></td>
		</tr>";
echo "<tr>
		<td colspan=6>
			<button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button>
			<button class=mybutton onclick=exportExcel3pl()>" . $_SESSION['lang']['excel'] . "</button>
			<button class=mybutton onclick=exportPdf3pl()>PDF</button>
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
			<select id=org class=select2 style=\"width:180px;\" onchange=getPrd() >" . $optOrg . "</select>
		</td>

		<td>" . $_SESSION['lang']['jabatan'] . "</td> 
		<td>:</td>
		<td>
			<select id=jabatan class=select2 style=\"width:180px;\">" . $optjab . "</select>
		</td>
	</tr> 

	<tr>
		<td>" . $_SESSION['lang']['tipekaryawan'] . "</td> 
		<td>:</td>
		<td>
			<select id=tipekar class=select2 style=\"width:180px;\">" . $opttpkar . "</select>
		</td>

		<td>" . $_SESSION['lang']['periodegaji'] . "</td> 
		<td>:</td>
		<td><select id=per class=select2 style=\"width:180px;\">" . $optPer . "</select></td>
	</tr> 
	<tr>
		<td>" . $_SESSION['lang']['jenis'] . "</td> 
		<td>:</td>
		<td>
			<select id=kom class=select2 style=\"width:180px;\">" . $optJns . "</select>

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
				<select style='width:320px;' id=kar class=select2>" . $optKar . "</select>
			</td>
			<td align=center>
				<input type=text maxlength=20 class=myinputtextnumber style='width:100%;' id=jum onkeypress='return angka_doang(event)' onkeyup=\"if(this.value!=''){z.numberFormat(this.id,2);}\">
			</td>
			<td align=center>
				<input type=text maxlength=100 class=myinputtext style='width:100%;' id=ket>
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