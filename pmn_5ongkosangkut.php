<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript src='js/pmn_5ongkosangkut.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
$optsuppl = $optOrg = $opttipesupplier = $optcust = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optkomoditisch = $optsuppsch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$namasuppl = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
## GET MILL
$optmill = $optkomoditi = $optaktif = '';
$optmill .= "<option value=''>{$_SESSION['lang']['pilihdata']}</option>";
$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where tipe in ('PABRIK') order by kodeorganisasi asc";
$res = fetchdata($str);
foreach ($res as $val) {
	$optmill .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
}



## GET CUSTOMER
$str = "SELECT kodecustomer,namacustomer FROM " . $dbname . ".pmn_4customer  order by namacustomer asc";
$res = fetchdata($str);
foreach ($res as $val) {
	$optcust .= "<option value='" . $val['kodecustomer'] . "'>" . $val['kodecustomer'] . " - " . $val['namacustomer'] . "</option>";
}

$optOrg .= orgDetailuser($_SESSION['standard']['username'], '1');

$str = "SELECT kodebarang,namabarang FROM " . $dbname . ".log_5masterbarang where kelompokbarang='400' order by kodebarang asc";
$res = fetchdata($str);
foreach ($res as $val) {
	$optkomoditi .= "<option value='" . $val['kodebarang'] . "'> " . $val['namabarang'] . "</option>";
	$optkomoditisch .= "<option value='" . $val['kodebarang'] . "'> " . $val['namabarang'] . "</option>";
}

## GET SUPPLIER
$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$nmsupplier[$bar['supplierid']] = $bar['namasupplier'];
}

## GET SUPPLIER
$str = "SELECT distinct(supplierid) as supplierid FROM " . $dbname . ".pmn_hargabelitbs";
$res = fetchdata($str);
foreach ($res as $val) {
	if (strlen($val['supplierid']) > 6) {
		$optsuppsch .= "<option value='" . $val['supplierid'] . "'>" . $val['supplierid'] . " - " . $nmsupplier[$val['supplierid']] . "</option>";
	} else {
		$optsuppsch .= "<option value='" . $val['supplierid'] . "'>" . $val['supplierid'] . " - " . $nmorganisasi[$val['supplierid']] . "</option>";
	}
}

## GET SUPPLIER
$str = "SELECT supplierid FROM " . $dbname . ".log_5supkelompok where tipe='TRANSPORTIR' ";
// $str = "SELECT supplierid FROM ".$dbname.".log_5supkelompok   ";
$res = fetchdata($str);
foreach ($res as $val) {
	$optsuppl .= "<option value='" . $val['supplierid'] . "'>" . $val['supplierid'] . " - " . $namasuppl[$val['supplierid']] . "</option>";
}


$optjam = $optmenit = "<option value='00'>00</option>";
for ($i = 1; $i <= 23; $i++) {
	if (strlen($i) < 2) {
		$i = "0" . $i;
	}
	$optjam .= "<option value=" . $i . ">" . $i . "</option>";
}

for ($i = 1; $i <= 59; $i++) {
	if (strlen($i) < 2) {
		$i = "0" . $i;
	}
	$optmenit .= "<option value=" . $i . ">" . $i . "</option>";
}

$optaktif .= "<option value='1'>" . $_SESSION['lang']['ya'] . "</option>";
$optaktif .= "<option value='0'>" . $_SESSION['lang']['tidak'] . "</option>";

$frm[0] = '';
$frm[1] = '';
$frm[2] = '';

OPEN_BOX('', '<span class=judul>' . getMenu('pmn_5ongkosangkut') . '</span>');


$frm[0] .= "<fieldset>
    <legend>" . $_SESSION['lang']['form'] . "</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
 
		<tr>
			<td> " . $_SESSION['lang']['notransaksi'] . "  </td> 
			<td>:</td>
			<td>
				<input disabled class='myinputtext' id='kdtrans' style=\"width:205px;\">
			</td>
		</tr>
 
		<tr>
			<td> " . $_SESSION['lang']['unit'] . "  </td> 
			<td>:</td>
			<td>
				<select class='select2' id='kodeunitmaster' onchange=bentuknotrans()  style=\"width:205px;\">" . $optmill . "</select>
			</td>
		</tr>
		<tr>
			<td> Transportir </td> 
			<td>:</td>
			<td>
				<select class='select2' id='trpcode' style=\"width:205px;\">" . $optsuppl . "</select>
			</td>
		</tr>
		<tr hidden>
			<td> Asal </td> 
			<td>:</td>
			<td>
				<select class='select2' id='lokasi' onchange=getlokasi() style=\"width:205px;\">" . $optsuppl . "</select>
			</td>
		</tr>
		<tr>
			<td> " . $_SESSION['lang']['tujuan'] . "</td> 
			<td>:</td>
			<td>
				<select class='select2' id='lokasi2' onchange=getlokasi() style=\"width:205px;\">" . $optcust . "</select>
			</td>
		</tr>
		<tr hidden>
			<td>" . $_SESSION['lang']['lokasi'] . "</td> 
			<td>:</td>
			<td>
                <input type=text id=lokasixx size=20 class=myinputtext style=\"width:200px;\" ></td>
			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['komoditi'] . "</td> 
			<td>:</td>
			<td>
				<select class='select2' id='komoditi' style=\"width:205px;\">" . $optkomoditi . "</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=simpanmaster()>" . $_SESSION['lang']['save'] . "</button>
				<button class=mybutton onclick=batalmaster()>" . $_SESSION['lang']['cancel'] . "</button>
				<input  hidden id=methodmaster value='insertmaster'>
				<input  hidden id=idht value=''>
			</td>
		</tr>
	</table>

</fieldset>";


$frm[0] .= "<fieldset>
        <legend>" . $_SESSION['lang']['find'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>							 
							<table>
								<tr>
									<td>&nbsp" . $_SESSION['lang']['unit'] . "</td>
									<td>:</td>
									<td><select class='select2' id='kodeunitmastercari' onchange=loaddatamaster() style=\"width:205px;\">" . $optOrg . "</select></td>
									
									<td>&nbsp" . $_SESSION['lang']['komoditi'] . "</td>
									<td>:</td>
									<td><select class='select2' id='komoditicari' onchange=loaddatamaster() style=\"width:205px;\">" . $optkomoditisch . "</select></td>
								</tr>
								<tr>
									<td>&nbsp" . $_SESSION['lang']['notransaksi'] . "</td>
									<td>:</td>
									<td><input id='notransaksicari' type='text' class='myinputtext' placeholder='Cari Notransaksi' onkeyup=loaddatamaster() style=\"width:200px;\" /></td>
								</tr>
							</table>
							<!--
							<button class=mybutton onclick=loaddatamaster(0)>" . $_SESSION['lang']['find'] . "</button>
							<button class=mybutton onclick=batalcarimaster()>" . $_SESSION['lang']['cancel'] . "</button>
							-->
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=containermaster style='width:100%;'> 
            <script>loaddatamaster(0)</script>
        </div>
    </fieldset>";


$frm[1] .= "<fieldset style=float:left>
    <legend>" . $_SESSION['lang']['form'] . " Input</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>" . $_SESSION['lang']['unit'] . "</td> 
			<td>:</td>
			<td coslpan=2><select id=kodeunitharga  onchange=gettipesupplier() style=\"width:205px;\">" . $optOrg . "</select>
		</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tipe'] . " " . $_SESSION['lang']['supplier'] . "</td> 
			<td>:</td>
			<td coslpan=2><select id=tipeharga style=\"width:205px;\">" . $opttipesupplier . "</select>
		</td>
		<tr>
			<td>" . $_SESSION['lang']['tanggalmulai'] . "</td> 
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' readonly=readonly id='tanggalharga' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;' value='" . date('d-m-Y') . "' />
				<select id=jamharga style=\"width:50px;\">" . $optjam . "</select>:<select id=menitharga style=\"width:50px;\">" . $optmenit . "</select>
			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tanggalsampai'] . "</td> 
			<td>:</td>
			<td>
			<input type='text' class='myinputtext' readonly=readonly id='tanggalharga2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;' value='" . date('d-m-Y') . "' />
			<select id=jamharga2 style=\"width:50px;\">" . $optjam . "</select>:<select id=menitharga2 style=\"width:50px;\">" . $optmenit . "</select>
			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tahuntanam'] . " / " . $_SESSION['lang']['grade'] . "</td> 
			<td>:</td>
			<td coslpan=2><input type=text id=tahuntanamharga onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext  style=\"width:200px;\" maxlength=100 ></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['harga'] . " Awal Disbun</td> 
			<td>:</td>
			<td coslpan=2><input type=text  id=awaldisbunharga class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:200px;\" maxlength=100 ></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['harga'] . " Awal Realisasi</td> 
			<td>:</td>
			<td coslpan=2><input type=text id=awalrealisasiharga class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:200px;\" maxlength=100 ></td>
		</tr>
		
		
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=previewharga() id=buttonpreviewharga>" . $_SESSION['lang']['save'] . "</button>
				<button class=mybutton onclick=batalharga()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>
	</table>
</fieldset>";

$frm[1] .= "<fieldset style=float:left>
    <legend>" . $_SESSION['lang']['form'] . " Copy Data</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>" . $_SESSION['lang']['unit'] . "</td> 
			<td>:</td>
			<td coslpan=2><select id=kodeunithargacopy onchange=gettipesuppliercopy()   style=\"width:200px;\">" . $optOrg . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tipe'] . " " . $_SESSION['lang']['supplier'] . "</td> 
			<td>:</td>
			<td coslpan=2><select id=tipehargacopy style=\"width:205px;\">" . $opttipesupplier . "</select>
		</td>
		<tr>
			<td>" . $_SESSION['lang']['tanggalmulai'] . " Awal</td> 
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' readonly=readonly id='tanggalhargacopy' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;'/>
				<select id=jamhargacopy style=\"width:50px;\">" . $optjam . "</select>:<select id=menithargacopy style=\"width:50px;\">" . $optmenit . "</select>
			
			</td>
		</tr>
			<tr>
			<td>" . $_SESSION['lang']['tanggalsampai'] . " Awal</td> 
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' readonly=readonly id='tanggalharga2copy' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;'/>
				<select id=jamharga2copy style=\"width:50px;\">" . $optjam . "</select>:<select id=menitharga2copy style=\"width:50px;\">" . $optmenit . "</select>
			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tahuntanam'] . "</td> 
			<td>:</td>
			<td coslpan=2>
			
			<input type=text  id=tahuntanamhargacopy onkeypress=\"return_tanpa_kutip(event);\"  placeholder='Seluruhnya' class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:197px;\" maxlength=100 ></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tanggal'] . " Berikutnya</td> 
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggalhargatujuancopy' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;' readonly/>
				<select id=jamhargatujuancopy style=\"width:50px;\">" . $optjam . "</select>:<select id=menithargatujuancopy style=\"width:50px;\">" . $optmenit . "</select>
			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tanggal'] . " Berikutnya</td> 
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggalhargatujuan2copy' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;' readonly/>
				<select id=jamhargatujuan2copy style=\"width:50px;\">" . $optjam . "</select>:<select id=menithargatujuan2copy style=\"width:50px;\">" . $optmenit . "</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=prosescopy() id=buttonpreviewharga>" . $_SESSION['lang']['proses'] . "</button>
				<button class=mybutton onclick=batalprosescopy()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>
	</table>
</fieldset>";

$frm[1] .= "<div style=clear:both></div>
		<fieldset id=detaildataharga style=display:none>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <div id=detailharga> 
        </div>
    </fieldset>";

$frm[1] .= "<div style=clear:both></div>
		<fieldset id=listdataharga style='display:block;'>
        <legend>" . $_SESSION['lang']['list'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>" . $_SESSION['lang']['find'] . "</legend>
								<table>
									<tr>
										<td>" . $_SESSION['lang']['unit'] . "</td>
										<td>:</td>
										<td><select id=kodeunithargacari style=\"width:100px;\">" . $optOrg . "</select></td>
										
										<td>" . $_SESSION['lang']['tahuntanam'] . " / " . $_SESSION['lang']['grade'] . "</td>
										<td>:</td>
										<td><input type=text  id=tahuntanamhargacari nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:100px;\" maxlength=100 ></td>
									
										<td>" . $_SESSION['lang']['tanggal'] . "</td>
										<td>:</td>
										<td><input type='text' class='myinputtext' id='tanggalhargacari' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:100px;' readonly/></td>
									
										
										<td>
										<button class=mybutton onclick=loaddataharga(0)>" . $_SESSION['lang']['find'] . "</button>
											<button class=mybutton onclick=batalcariharga()>" . $_SESSION['lang']['cancel'] . "</button>
										</td>
									</tr>
								</table>
							
							
							
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=containerharga> 
           
        </div>
    </fieldset>"; // <script>loaddataharga(0)</script>



$frm[2] .= "<fieldset>
    <legend>" . $_SESSION['lang']['form'] . "</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
	<tr>
		<td>" . $_SESSION['lang']['unit'] . "</td>
		<td>:</td>
		<td><select id=kodeunitgrade style=\"width:150px;\">" . $optOrg . "</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['grade'] . "</td>
		<td>:</td>
		<td><input type=text maxlength=10  class=myinputtext id=kodegrade  style=\"width:145px;\" onkeypress=\"return_tanpa_kutip(event);\" style=\"width:116px;\"></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['batasbawah'] . "</td>
		<td>: >=</td>
		<td><input type=text maxlength=10 value=0 class=myinputtextnumber id=batasbawahgrade onkeydown=upperCaseF(this) size=26 onblur='change_number(this);' onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['batasatas'] . "</td>
		<td>: <</td>
		<td><input type=text maxlength=10 value=0 class=myinputtextnumber id=batasatasgrade onkeydown=upperCaseF(this) size=26 onblur='change_number(this);' onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"></td>
	</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=simpangrade()>" . $_SESSION['lang']['save'] . "</button>
				<button class=mybutton onclick=batalgrade()>" . $_SESSION['lang']['cancel'] . "</button>
				<input type=hidden id=methodgrade value='insertgrade'>
			</td>
		</tr>
	</table>

</fieldset><div style=clear:both></div>";


$frm[2] .= "<fieldset>
        <legend>" . $_SESSION['lang']['list'] . "</legend>
         <div id=containergrade> 
          
        </div>
    </fieldset>";


### HEADER TAB ###
$hfrm[0] = strtoupper('Form');
// $hfrm[1]=strtoupper('Daftar Harga');
// $hfrm[2]=strtoupper('Grade External');


### HEADER TAB ###

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 200, 'auto');

CLOSE_BOX();
?>
<script>
	getSelect2();
</script>
<?php
echo close_body();
?>