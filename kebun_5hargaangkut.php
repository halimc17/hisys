<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');

require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/kebun_5hargaangkut.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>

<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth: true
		});
	});

	$(document).on('focus', '.select2-selection.select2-selection--single', function(e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>
<?php
$_SESSION['fee'] = array();
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
foreach (getOrgDetail(23) as $key => $val) {
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$d = $nminduk[$key];
	if ($d != $n) {
		$optunit .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	}
	$optunit .= "<option value=" . $key . ">" . $val . "</option>";
	$n = $d;
	if ($d != $n) {
		$optunit .= "</optgroup>";
	}
}

$optdivisi = "<option value=''></option>";
foreach (getOrgDetail(19) as $key => $val) {
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$d = $nminduk[$key];
	if ($d != $n) {
		$optdivisi .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	}
	$optdivisi .= "<option value=" . $key . ">" . $val . "</option>";
	$n = $d;
	if ($d != $n) {
		$optdivisi .= "</optgroup>";
	}
	$arrunit[$val] = $val;
}

$optblok = "<option value=''></option>";
$sql = "SELECT distinct indukblok FROM " . $dbname . ".setup_blok where 1=1 and substring(kodeorg,1,4) in (" . getOrgDetail(2) . ") order by kodeorg asc";
$res = fetchdata($sql);
foreach ($res as $bar) {
	$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok', "indukblok='" . $bar['indukblok'] . "'");
	$optblok .= "<option value=" . $bar['indukblok'] . ">" . $bar['indukblok'] . " - " . $nminduk[$bar['indukblok']] . "</option>";
}

$opttt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optttf = "<option value=''></option>";
$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok order by tahuntanam asc";
$res = fetchdata($sql);
foreach ($res as $bar) {
	$opttt .= "<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
	$optttf .= "<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
}

$namaisi = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='BMTBS' and kodeorg='" . $_SESSION['empl']['lokasitugas'] . "'";
$bar = fetchdata($str)[0];
$nama = explode(',', $bar['nilai']);
$optkeg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach ($nama as $list => $isi) {
	$optkeg .= "<option value=" . $isi . ">" . $namaisi[$isi] . "</option>";
}
$optpks = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe='PABRIK' and namaorganisasi not like '%BULKING%'";
$res = fetchdata($sql);
foreach ($res as $bar) {
	if ($bar['induk'] == $_SESSION['empl']['kodeorganisasi']) {
		$i = "selected";
	} else {
		$i = "";
	}
	$d = "PKS INTERNAL dan AFILIASI";
	if ($d != $n) {
		$optpks .= "<optgroup label='" . $d . "'>";
	}
	$optpks .= "<option value=" . $bar['kodeorganisasi'] . " " . $i . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	$n = $d;
	if ($d != $n) {
		$optpks .= "</optgroup>";
	}
}

// $where=" and a.kodecustomer not in ('BPJ','KBP','KSP','SDK','SNIP')";
$iPks = "select distinct b.* from " . $dbname . ".pmn_4komoditi a left join " . $dbname . ".pmn_4customer b
	ON a.kodecustomer=b.kodecustomer where a.kodebarang='40000003'  and b.kodecustomer is not null " . $where . "";
$nPks = $owlPDO->query($iPks) or die(print " Gagal: " . PDOException::getMessage());
$nPks->setFetchMode(PDO::FETCH_ASSOC);
while ($dPks = $nPks->fetch()) {
	if ($pks == $dPks['kodecustomer']) {
		$select = "selected=selected";
	} else {
		$select = "";
	}
	$d = "PKS EXTERNAL";
	if ($d != $n) {
		$optpks .= "<optgroup label='" . $d . "'>";
	}
	$optpks .= "<option " . $select . " value='" . $dPks['kodecustomer'] . "'>" . $dPks['namacustomer'] . "</option>";
	$n = $d;
	if ($d != $n) {
		$optpks .= "</optgroup>";
	}
}


$optjns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optjns .= "<option value='GLOBAL' selected>GLOBAL</option>";
$sql = "SELECT * FROM " . $dbname . ".vhc_5jenisvhc";
$res = fetchdata($sql);
foreach ($res as $bar) {
	$optjns .= "<option value=" . $bar['jenisvhc'] . ">" . $bar['jenisvhc'] . " - " . $bar['namajenisvhc'] . "</option>";
}

$namastat = array('0' => 'Belum disetujui', '9' => 'Proses persetujuan', '1' => 'Disetujui');
$namastat = array("0" => "Belum diajukan", "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['koreksi'], "3" => $_SESSION['lang']['ditolak'], '9' => 'Proses Persetujuan');
$optstat = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach ($namastat as $list => $isi) {
	$optstat .= "<option value=" . $list . ">" . $isi . "</option>";
}

$optdivisi_cari = "<option value=''></option>";
$sql = "SELECT distinct substr(blok,1,6) as divisi FROM " . $dbname . ".kebun_5hargaangkut order by divisi asc";
$res = fetchdata($sql);
foreach ($res as $bar) {
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $bar['divisi'] . "'");
	$d = $nminduk[$bar['divisi']];
	if ($d != $n) {
		$optdivisi_cari .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	}
	$optdivisi_cari .= "<option value=" . $bar['divisi'] . ">" . $bar['divisi'] . "</option>";
	$n = $d;
	if ($d != $n) {
		$optdivisi_cari .= "</optgroup>";
	}
}

OPEN_BOX('', '<span class=judul>' . getMenu('kebun_5hargaangkut') . '</span>');
echo "<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
		<tr>
			<td>" . $_SESSION['lang']['divisi'] . "</td>
			<td>:</td>
			<!--<td><input id=find_divisi class=myinputtext style=\"width:146px;\"></td>-->
			<td><select id=find_divisi class=select2 onchange=loaddata(0); onblur=getblok(); style=\"width:150px;\">" . @$optdivisi_cari . "</select>
			</td>
		
			<td>" . $_SESSION['lang']['blok'] . "</td>
			<td>:</td>
			<!--<td><input id=find_blok class=myinputtext style=\"width:145px;\"></td>-->
			<td><select id=find_blok class=select2 onchange=loaddata(0); onblur=getfindtt() style=\"width:150px;\">" . @$optblok . "</select></td>
		
			<td>" . $_SESSION['lang']['tahuntanam'] . "</td>
			<td>:</td>
			<!--<td><input id=find_tt class=myinputtext style=\"width:145px;\"></td>-->
			<td><select id=find_tt class=select2 onchange=loaddata(0) style=\"width:150px;\">" . @$optttf . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['status'] . "</td>
			<td>:</td>
			<td><select class='select2' id=find_stat  onchange=loaddata(0); style=\"width:150px;\">" . @$optstat . "</select>
			</td>
		
			<td>" . $_SESSION['lang']['nopengajuan'] . "</td>
			<td>:</td>
			<td><input id=find_nope class=myinputtext style=\"width:145px;\"></td>
			
			<td>Tanggal Berlaku</td>
			<td>:</td>
			<td><input type='text' readonly=readonly class='myinputtext' id='find_tanggalberlaku' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:145px;'> </td>
		</tr>
		
		<tr>
			<td></td>
			<td></td>
			<td colspan=100>
				<button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
				<button class=mybutton onclick=loaddatatoexcel(0)>" . $_SESSION['lang']['excel'] . "</button>
				<button onclick=batalcari() class=mybutton name=btnBatal id=btnBatal>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>
	</table>";

echo "</fieldset></table><div style=clear:both></div>";
CLOSE_BOX();

$namasupp = array();
$optsupp = "<option value=''>" . $_SESSION['lang']['default'] . "</option>";
$sql = "SELECT a.* FROM " . $dbname . ".log_spkht a left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi where a.posting='0' and b.close='0' and b.jenis='ANGKUTTBS' order by a.notransaksi asc";
$res = fetchdata($sql);

foreach ($res as $bar) {
	$namasupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $bar['koderekanan'] . "'");

	$optsupp .= "<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
}

echo "<div id=forminput style=display:none;>";

OPEN_BOX();
echo "<fieldset style='float:left;margin-right:10px'>
    <legend>" . $_SESSION['lang']['form'] . "</legend>
			<table>
				<tr>
					<td>" . $_SESSION['lang']['unit'] . "</td>
					<td>:</td>
					<td><select class='select2' id=unit onchange=gettahuntanam(this.id) style=\"width:200px;\">" . $optunit . "</select></td>
					
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td>:</td>
					<td><select class='select2' id=divisi onchange=gettahuntanam(this.id) style=\"width:200px;\">" . $optdivisi . "</select></td>
				</tr>	
				<tr>	
					
					<td>PKS Tujuan</td>
					<td>:</td>
					<td><select class='select2' id=pkstujuanht  style=\"width:200px;\">" . $optpks . "</select></td>
					
					<td>Blok</td>
					<td>:</td>
					<td><select class='select2' id=blok  style=\"width:200px;\">" . $optblok . "</select></td>
				</tr>	
				<tr>	
					<td>TT</td>
					<td>:</td>
					<td><select class='select2' id=tahuntanam onchange=gettahuntanam(this.id) style=\"width:200px;\">" . $opttt . "</select></td>
					
					<td>Jenis Kendaraan</td>
					<td>:</td>
					<td><select class='select2' id=jnskendht  style=\"width:200px;\">" . $optjns . "</select></td>
				</tr>	
				<tr>	
					<td>Tanggal Berlaku</td>
					<td>:</td>
					<td><input type='text' readonly=readonly class='myinputtext' id='tanggalberlaku' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:195px;' onchange='munculupload()'> </td>
					
					<td>" . $_SESSION['lang']['nospk'] . "</td>
					<td>:</td>
					<td><select class='select2' id=nospk  style=\"width:200px;\">" . $optsupp . "</select></td>
				</tr>	
				<tr>	
					<td hidden>" . $_SESSION['lang']['kegiatan'] . "</td>
					<td hidden>:</td>
					<td hidden><select id=kegiatan  style=\"width:200px;\">" . $optkeg . "</select></td>
				</tr>	
				<tr>	
					<td></td>
					<td></td>
					<td colspan=5>
						<button class=mybutton onclick=previewdetail()>" . $_SESSION['lang']['preview'] . "</button>
						<button class=mybutton onclick=bataldetail()>" . $_SESSION['lang']['cancel'] . "</button>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset id=formupload style='width:400px;display:none'>
			<legend>" . $_SESSION['lang']['upload'] . "</legend>
			<table>
                <tr>
                    <td>Download Template</td> 
                    <td>:</td>
                    <td><button class=mybutton style=\"width:235px;\" onclick=unduhformat()>Download Template </button></td>
                </tr>
                <tr>
                    <td>File .(xlsx)</td> 
                    <td>:</td>
                    <td><input name='upload' type='file' id='upload' size='25' class='mybutton'></td>
                </tr> 
                <tr>
                    <td></td>
                    <td></td>
                    <td>
						<button class=mybutton onclick=fileSelected('') style=width:84px;color:blue;>Preview</button>
                    </td>
                </tr> 
			</table>
		</fieldset>
	";
CLOSE_BOX();
echo "</div>";
OPEN_BOX();
echo "<div id=listinput  style=display:none;>
	<table border=0 cellpadding=1 class=sortable cellspacing=1>
		<thead>
			<tr class=rowheader>
				<th align=center rowspan=2>No</th> 
				<th align=center rowspan=2>" . $_SESSION['lang']['blok'] . "</th> 
				<th align=center rowspan=2 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th> 
				<th align=center rowspan=2>" . $_SESSION['lang']['luas'] . "</th> 
				<th align=center rowspan=2>PKS Tujuan</th> 
				<th align=center rowspan=2>Jenis<br>Kendaraan</th> 
				<th align=center colspan=7>Upah Muat</th> 
				<th align=center colspan=7>Upah Angkut</th> 
				<th align=center rowspan=2>" . $_SESSION['lang']['action'] . "</th> 
			</tr>
			<tr class=rowheader>
				<th align=center>TPH-PKS 1</th> 
				<th align=center>TPH-PKS 2</th>
				<th align=center>TPH-PKS 3</th>
				<th align=center>TPH-PKS 4</th>
				<th align=center>TPH-PKS 5</th>
				<th align=center>TPH-PKS 6</th>
				<th align=center>TPH-PKS 7</th>
				<th align=center>TPH-PKS 1</th> 
				<th align=center>TPH-PKS 2</th>
				<th align=center>TPH-PKS 3</th>
				<th align=center>TPH-PKS 4</th>
				<th align=center>TPH-PKS 5</th>
				<th align=center>TPH-PKS 6</th>
				<th align=center>TPH-PKS 7</th>
			</tr>
		</thead>
		<tbody id=detailinput> 
		</tbody>
		
	</table>
</div>
";

#untuk inputan baru
echo "<div id=contdetailex style='display:none;height:70vh'>";
echo "</div>";

echo "<div id=container><script>loaddata(0)</script></div>";
CLOSE_BOX();
echo close_body();
?>