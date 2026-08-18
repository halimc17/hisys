<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/bgt_byypabrik.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/option.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php
$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optorgsch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach (@getOrgDetail(13) as $key => $val) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$d = $induk[$key];
	if ($d != $n) {
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
		$optorg .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
		$optorgsch .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	}

	if ($key == $_SESSION['empl']['lokasitugas']) {
		$whthn = "and kodeorg like '" . $key . "%'";
		$optorgsch .= "<option value=" . $key . " selected>" . $key . " - " . $val . "</option>";
	} else {
		$optorgsch .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
	}
	$optorg .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
	$n = $d;
	if ($d != $n) {
		$optorg .= "</optgroup>";
		$optorgsch .= "</optgroup>";
	}
}

$optdiv = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optdivsch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach (@getOrgDetail(21) as $key => $val) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$d = $induk[$key];
	if ($d != $n) {
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
		$optdiv .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
		$optdivsch .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	}
	$optdiv .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
	$optdivsch .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";

	$n = $d;
	if ($d != $n) {
		$optdiv .= "</optgroup>";
		$optdivsch .= "</optgroup>";
	}
}
$optmesin = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$str = "select * from " . $dbname . ".organisasi where 1=1 and tipe='STENGINE' order by kodeorganisasi asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $bar['kodeorganisasi'] . "'");
	$d = $induk[$bar['kodeorganisasi']];
	if ($d != $n) {
		$optmesin .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	}
	$optmesin .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	$n = $d;
	if ($d != $n) {
		$optmesin .= "</optgroup>";
	}
}


$optthnpost = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select distinct tahunbudget from " . $dbname . ".bgt_budget where 1=1 " . $whthn . " order by tahunbudget desc";
$res = fetchdata($str);
foreach ($res as $bar) {
	@$optthn .= "<option value='" . $bar['tahunbudget'] . "'>" . $bar['tahunbudget'] . "</option>";
	$optthnpost .= "<option value='" . $bar['tahunbudget'] . "'>" . $bar['tahunbudget'] . "</option>";
}

$arropt = array('' => $_SESSION['lang']['all'], '1' => 'Yes', '2' => 'No');
foreach ($arropt as $key => $val) {
	@$optsebar .= "<option value='" . $key . "'>" . $val . "</option>";
}
$arrtampil = array('1' => 'Rekap Station', '2' => 'Detail Transaksi', '3' => 'Rekap per Station');
foreach ($arrtampil as $key => $val) {
	@$opttampil .= "<option value='" . $key . "'>" . $val . "</option>";
}

OPEN_BOX('', '<span class=judul>' . getMenu('bgt_byypabrik') . '</span>');
echo "<div id=action_list>";
echo "<table border=0>
     <tr valign=middle>	 
		<td align=center style='width:75px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "
		</td>
		<td align=center style='width:75px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td align=center style='width:75px;cursor:pointer;' onclick=add_sebaran()>
			<img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['sebaran'] . "'><br>" . $_SESSION['lang']['sebaran'] . "
		</td>
		<td align=center style='width:75px;cursor:pointer;' onclick=add_posting()>
			<img class=delliconBig src=images/archive.png title='" . $_SESSION['lang']['posting'] . "'><br>" . $_SESSION['lang']['posting'] . "
		</td>
		<td valign=middle>
			<div id=formcari>
				<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['budgetyear'] . "</td>
							<td>:</td>
							<td><select id=tahunsch onchange=loaddata(0); style=\"width:150px;\">" . $optthn . "</select></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select id=kodeorgsch onchange=loaddata(0); onblur=\"getstation(this,'stationsch','" . $_SESSION['lang']['all'] . "');\" style=\"width:150px;\">" . $optorgsch . "</select></td>
							
							<td>" . $_SESSION['lang']['station'] . "</td>
							<td>:</td>
							<td><select id=stationsch onchange=loaddata(0); style=\"width:150px;\">" . $optdivsch . "</select></td>
							
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td colspan=3>
								<button class=mybutton id=btnprev onclick=loaddata(0)>" . $_SESSION['lang']['preview'] . "</button>
								<!--<button class=mybutton id=btnexcel onclick=loadexcel(0)>" . $_SESSION['lang']['excel'] . "</button>-->
								<button class=mybutton id=btncari onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			
			<div id=formcariposting style=display:none>
				<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['budgetyear'] . "</td>
							<td>:</td>
							<td><select id=tahunpostsch onchange=showposting(0); style=\"width:150px;\">" . $optthnpost . "</select></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select id=kodeorgpostsch onchange=showposting(0); style=\"width:150px;\">" . $optorgsch . "</select></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td colspan=3>
								<button class=mybutton onclick=showposting(0)>" . $_SESSION['lang']['preview'] . "</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			
			<div id=formcarisebaran style=display:none>
				<fieldset><legend>Filter</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['budgetyear'] . "</td>
							<td>:</td>
							<td><select id=tahunsbr onchange=showsebaran(0); style=\"width:50px;\">" . $optthn . "</select></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select id=kodeorgsbr onchange=showsebaran(0); style=\"width:150px;\">" . $optorgsch . "</select></td>
							
							<td><b>Kode</b> " . $_SESSION['lang']['station'] . " / " . $_SESSION['lang']['mesin'] . "</td>
							<td>:</td>
							<td>
								<input type=text title=\"Tips : \n6 = Untuk filter Station\n10 digit = Untuk filter Mesin\n\nIsikan KODE bukan nama.\" class=myinputtext placeholder='" . $_SESSION['lang']['all'] . "' id=stationsbr onkeypress='enterkey(event,showsebaran)' style=width:145px;>
							</td>
							
						</tr>
						<tr>
							<td>Jumlah Baris</td>
							<td>:</td>
							<td><input style=width:45px value='50' type='text' id='jlhbaris' onkeypress='enterkey(event,showsebaran)' class='myinputtextnumber' /></td>
							
							<td>" . $_SESSION['lang']['sebaran'] . "</td>
							<td>:</td>
							<td><select style=width:150px  id=sebaran onchange='showsebaran(0)'>" . $optsebar . "</select></td>
							
							<td>" . $_SESSION['lang']['tampilkan'] . "</td>
							<td>:</td>
							<td><select style=width:150px  id=tampilkan onchange='showsebaran(0)'>" . $opttampil . "</select></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td colspan=30>
								<button class=mybutton id=btnprev onclick=showsebaran(0)>" . $_SESSION['lang']['preview'] . "</button>
							</td>
						</tr>
					</table>
				</fieldset>
		</div>
		</td>
		</tr></table>";

echo "</div>";
CLOSE_BOX();

echo "<div id=inputdata style=display:none>";
OPEN_BOX();
echo "<table><tr><td valign=top>
	<fieldset style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>
		<table border=0>
			<tr>
				<td>" . $_SESSION['lang']['budgetyear'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(1)</td>
				<td>:</td>
				<td><input type=text tabindex='1' class=myinputtextnumber id=tahun maxlength=4 onkeypress=\"return angka_doang(event);\" style=width:145px;></td>
				
				<td>" . $_SESSION['lang']['station'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(3)</td>
				<td>:</td>
				<td><select class=select2 id=station  tabindex='3' style=\"width:150px;\" onchange=\"getmesin(this,'mesin','" . $_SESSION['lang']['pilihdata'] . "');\">" . $optdiv . "</select></td>
			</tr><tr>
					
				<td>" . $_SESSION['lang']['unit'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(2)</td>
				<td>:</td>
				<td><select class=select2 tabindex='2' id=kodeorg onchange=\"getstation(this,'station,mesin','" . $_SESSION['lang']['pilihdata'] . "');\" style=\"width:150px;\">" . $optorg . "</select></td>

				<td>" . $_SESSION['lang']['mesin'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(4)</td>
				<td>:</td>
				<td><select class=select2 id=mesin  tabindex='4' onchange=getmesin('mesin'); style=\"width:150px;\">" . $optmesin . "</select></td>
			</tr>
			<tr>
				<td colspan=3></td>
				<td colspan=9>
					<button class=mybutton  tabindex='5'  onclick=simpanheader()>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton  tabindex='6' onclick=batalheader()>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
					<input hidden id=update>
					<input hidden id=index>
			</tr>
		</table>
	</fieldset>
	
	</td>
	
	</tr></table>
	";
CLOSE_BOX();
echo "</div>";
OPEN_BOX();
echo "<div id=contdetail style=display:none;>";

#untuk inputan baru
#== SDM ==
$optaruskas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct a.noaruskas, a.nama_aruskas from " . $dbname . ".keu_5aruskas a left join " . $dbname . ".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' order by a.tipetransaksi, a.noaruskas asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$e = substr($bar['noaruskas'], 0, 3);
	if ($e != $m) {
		$optaruskas .= "<optgroup label='" . getNamaAruskas($e) . "'>";
	}

	$optaruskas .= "<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";

	$m = $e;
	if ($e != $m) {
		$optaruskas .= "</optgroup>";
	}
}

$nmkode = makeOption($dbname, 'bgt_kode', 'kodebudget,nama');
$optupah = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct golongan from " . $dbname . ".bgt_upah where jumlah>0";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optupah .= "<option value='" . $bar['golongan'] . "'>" . strtoupper($nmkode[$bar['golongan']]) . "</option>";
}

$frm[0] = $frm[1] = $frm[2] = $frm[3] = $frm[4] = "";

$frm[0] .= "<fieldset><legend>" . $_SESSION['lang']['sdm'] . "</legend>
		<table cellspacing=1 border=0>
			<tr>			
				<td>" . $_SESSION['lang']['tipekaryawan'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(1)</font> :</td>
				<td><select class=select2 id='kdbudgetsdm'  tabindex='13'  onchange=getaruskas('sdm','aruskassdm','x'); style='width:155px;'>" . $optupah . "</select></td>

				<td>" . $_SESSION['lang']['aruskas'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(2)</font> :</td>
				<td><select class=select2 id='aruskassdm'  tabindex='14' style='width:155px;'>" . $optaruskas . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['hkefektif'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(3)</font> :</td>
				<td><input onclick=delnol(this); disabled class='myinputtextnumber'  style='width:150px;' id='hkesdm' /></td>
			
				<td>Jumlah TK</td>
				<td nowrap><font style=color:blue;font-size:10;>(4)</font> :</td>
				<td><input  tabindex='15' type='text' class='myinputtextnumber' style='width:150px;' id='jlhtk' onkeyup=getharga('sdm');  onclick=delnol(this); onkeypress='return angka_doang(event)' value='0' /></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['jhk'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(5)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtextnumber' style='width:150px;' id='jhksdm' onkeyup='getharga('sdm')' disabled onkeypress='return angka_doang(event)' value='0' /></td>
			
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(6)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtextnumber'  tabindex='16' style='width:150px;' id='ttlbyysdm' value='0' onkeypress='return false' /></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=3>
					<button class=mybutton  tabindex='17' onclick=simpandetail('sdm')  >" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton  tabindex='18' onclick=loaddatasdm()  >" . $_SESSION['lang']['preview'] . "</button>
				</td>
				<td align=center><button class=mybutton  tabindex='19' style=border-color:green; onclick=getTk()>Ambil Karyawan</button></td>
			</tr>
		</table>
	</fieldset>";

$frm[0] .= "<fieldset><legend>" . $_SESSION['lang']['sdm'] . "</legend>
			<div id=listdatasdm></div>
		</fieldset>";
#== SDM ==

#== MATERIAL ==
$kelompokMatPabrik = fetchData(selectQuery($dbname, "setup_parameterappl", "nilai", "kodeparameter='BGTMATPB'"))[0]['nilai'];
$kelompokMatPabrik = implode(",", array_map(fn($x) => "'" . trim(addslashes($x)) . "'", explode(",", $kelompokMatPabrik)));
$optkdmat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where substr(kodebudget,1,1)='M' AND kodebudget IN ({$kelompokMatPabrik}) order by kodebudget asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optkdmat .= "<option value='" . $bar['kodebudget'] . "'>" . $bar['kodebudget'] . " - " . $bar['nama'] . "</option>";
}


$optnoakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
if ($_SESSION['language'] == 'EN') {
	$dd = 'namaakun1 as namaakun';
} else {
	$dd = 'namaakun as namaakun';
}

$where = "";
if ($_SESSION['empl']['tipelokasitugas'] == 'BULKING') {
	$where = " and substr(noakun, 1, 3) IN ('812', '813')";
} else {
	$where = " and substr(noakun, 1, 2) IN ('63', '64')";
}

$str = "SELECT distinct noakun," . $dd . " FROM " . $dbname . ".`keu_5akun` WHERE 1=1 " . $where . " and length(noakun)='7' and detail=1 and namaakun not like '%YANG DIALOKASIKAN%'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$e = substr($bar['noakun'], 0, 3);
	if ($e != $m) {
		$optnoakun .= "<optgroup label='" . $e . " - " . getNamaAkun($e) . "'>";
	}

	$d = substr($bar['noakun'], 0, 5);
	if ($d != $n) {
		$optnoakun .= "<optgroup label='" . $d . " - " . getNamaAkun($d) . "'>";
	}
	$optnoakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
	$n = $d;
	if ($d != $n) {
		$optnoakun .= "</optgroup>";
	}
	$m = $e;
	if ($e != $m) {
		$optnoakun .= "</optgroup>";
	}
}

$optjenis1 = "";
$optjenis1 .= "<option value='consumables'>Consumables</option>";
$optjenis1 .= "<option value='recurrent'>Recurrent</option>";
$optjenis1 .= "<option value='nonrecurrent'>Non Recurrent</option>";


$frm[1] .= "<fieldset><legend>" . $_SESSION['lang']['material'] . "</legend>";
$frm[1] .= "<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['noakun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(1)</font> :</td>
				<td><select class=select2 id='noakunmat' onchange=getaruskas('mat','aruskasmat',this.value); tabindex='19'style='width:155px;'>" . $optnoakun . "</select></td>
				
				<td>" . $_SESSION['lang']['aruskas'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(2)</font> :</td>
				<td><select class=select2 id='aruskasmat'  tabindex='19'style='width:155px;'>" . $optaruskas . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kelompokbarang'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(3)</font> :</td>
				<td><select class=select2 id='kdbudgetmat' tabindex='20' style='width:155px;' onchange=\"formcaribarang('mat');\">" . $optkdmat . "</select></td>
			
				<td>" . $_SESSION['lang']['kodebarang'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(4)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtext'  tabindex='21' readonly onclick=\"formcaribarang('mat');\" id='kodebarang' style='width:150px;' onkeypress='return angka_doang(event)' />&nbsp;<img src='images/skyblue/zoom.png' style='position:relative;top:2px;' class=\"zImgBtn\" title='" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "' onclick=\"formcaribarang('mat');\">
				<span id='namabarang'></span>
				</td>
			</tr>				
			<tr>
				<td>" . $_SESSION['lang']['jenis'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(5)</font> :</td>
				<td><select class=select2 id=jenismat name=jenismat style='width:155px;'><option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>" . $optjenis1 . "</select></td>
		
				<td>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(6)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtextnumber' id='jumlahmat' onkeyup=getharga('mat'); style='width:150px;' onkeypress='return angka_doang(event)' onkeyup=getharga('mat');>&nbsp;<span id='satuanmat'></span>
				</td>

			</tr>
			<tr>
				<td>" . $_SESSION['lang']['hargasatuan'] . "</span></td>
				<td nowrap><font style=color:blue;font-size:10;>(7)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtextnumber' id='hargamat' disabled style='width:150px;' onkeypress='return angka_doang(event)' />
				</td>
			
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(8)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtextnumber' id='ttlbyymat' style='width:150px;' onkeypress='return false'  value='0' /></td>
			</tr>        
			<tr>
				<td></td>
				<td></td>
				<td colspan=6>
					<button class=mybutton  tabindex='22' onclick=simpandetail('mat');>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton onclick='showupload()'   >" . $_SESSION['lang']['upload'] . "</button>
					<button class=mybutton  tabindex='23' onclick=loaddatamat();>" . $_SESSION['lang']['preview'] . "</button>
				</td>
			</tr>
		</table>
	</fieldset>
";

$frm[1] .= "<fieldset><legend>" . $_SESSION['lang']['material'] . "</legend>
			<div id=listdatamat class='table-scroll' style=height:65vh></div>
		</fieldset>";
#== MATERIAL ==
#== MAINTENANCE ==

$where = "";
if ($_SESSION['empl']['tipelokasitugas'] == 'BULKING') {
	$where = " and kodebudget in ('BULKING','SERVICEBULK')";
} else {
	$where = " and kodebudget in ('PKSM','SERVICE')";
}


$sOrgs = "select kodebudget,nama from " . $dbname . ".bgt_kode where 1=1 " . $where . " order by kodebudget asc";
$res = fetchdata($sOrgs);
$optKdbdgt_S = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach ($res as $rOrgs) {
	$optKdbdgt_S .= "<option value='" . $rOrgs['kodebudget'] . "'>" . $rOrgs['nama'] . "</option>";
}

$frm[2] = "<fieldset><legend>" . $_SESSION['lang']['pemeliharaan'] . "</legend>";
$frm[2] .= "<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['kodeanggaran'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(1)</font> :</td>
				<td><select id='kdbudgetmain' class=select2 style='width:155px;' onchange=getaruskas('main','aruskasmain','x');>" . $optKdbdgt_S . "</select></td>
			
				<td>" . $_SESSION['lang']['aruskas'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(2)</font> :</td>
				<td><select class=select2 id='aruskasmain' style='width:155px;'>" . $optaruskas . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kdWorks'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(3)</font> :</td>
				<td><select class=select2 id='kodews' style='width:155px;' disabled></select></td>
			
				<td>Jam " . $_SESSION['lang']['setahun'] . " </td>
				<td nowrap><font style=color:blue;font-size:10;>(4)</font> :</td>
				<td><input onclick=delnol(this); type=text class=myinputtextnumber onkeyup=getjumlahws(); id=jamws name=jamws disabled onkeypress=\"return angka_doang(event);\" maxlength=20 style=width:150px;></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(5)</font> :</td>
				<td><input onclick=delnol(this); type=text class=myinputtextnumber id=ttlbyymain onkeypress=\"return angka_doang(event);\" maxlength=20 style=width:150px; /></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=6>
					<button class=mybutton onclick=simpandetail('main');>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton onclick=loaddatamain();>" . $_SESSION['lang']['preview'] . "</button>
				</td>
			</tr>
		</table>
	</fieldset>";

$frm[2] .= "<fieldset><legend>" . $_SESSION['lang']['pemeliharaan'] . "</legend>
			<div id=listdatakont></div>
		</fieldset>";
#== VHC ==

$optkdbgtvhc = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";


$where = "";
if ($_SESSION['empl']['tipelokasitugas'] == 'BULKING') {
	$where = " and kodebudget like 'VHCBULK%'";
} else {
	$where = " and kodebudget like 'VHC%' and kodebudget not like 'VHCBULK%'";
}

$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where 1=1 " . $where . " order by nama asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optkdbgtvhc .= "<option value='" . $bar['kodebudget'] . "'>" . $bar['nama'] . "</option>";
}

$optvhc = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$frm[4] = "<fieldset><legend>" . $_SESSION['lang']['kndran'] . "</legend>";
$frm[4] .= "<table cellspacing=1 border=0>
			<tr>
				<td colspan=2>" . $_SESSION['lang']['kodeanggaran'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(1)</font> :</td>
				<td><select class=select2 id='kdbudgetvhc' style='width:155px;'>" . $optkdbgtvhc . "</select></td>
				
				<td colspan=2 style=display:none>" . $_SESSION['lang']['aruskas'] . "</td>
				<td style=display:none>:</td>
				<td style=display:none><select class=select2 id='aruskasvhc' style='width:155px;'>" . $optaruskas . "</select></td>
			
				<td>" . $_SESSION['lang']['kodevhc'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(2)</font> :</td>
				<td><select class=select2 id='kodevhc' style='width:155px;' onchange=getharga('vhc');>" . $optvhc . "</select></td>
			</tr>
			<tr>
			
				<td>" . $_SESSION['lang']['jumlah'] . "</td>
				<td>(<span id=satuanvhc></span>)</td>
				<td nowrap><font style=color:blue;font-size:10;>(3)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtextnumber' id='jlhvhc' style='width:150px;' onkeypress='return angka_doang(event)'   onkeyup=getharga('vhc'); /></td>
			
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(4)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtextnumber' id='ttlbyyvhc' style='width:150px;' onkeypress='return false' value=0 /></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				
				<td colspan=6>
					<button class=mybutton onclick=simpandetail('vhc');>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton onclick=loaddatavhc();>" . $_SESSION['lang']['preview'] . "</button>
				</td>
			</tr>
		</table>
	</fieldset>";
$frm[4] .= "<fieldset><legend>" . $_SESSION['lang']['kndran'] . "</legend>
			<div id=listdatavhc></div>
		</fieldset>";
#== VHC ==
#== KONTRAK ==
$kdbgtkont = "";
$kdbgtkont = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where kodebudget like '%KONTRAK%' order by nama asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kdbgtkont .= "<option value='" . $bar['kodebudget'] . "'>" . $bar['nama'] . "</option>";
}

$optsatuan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct(UPPER(satuan)) as satuan from " . $dbname . ".setup_satuan order by satuan asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optsatuan .= "<option value='" . $bar['satuan'] . "'>" . $bar['satuan'] . "</option>";
}

$frm[3] = "<fieldset><legend>" . $_SESSION['lang']['kontrak'] . "</legend>";
$frm[3] .= "<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['kodeanggaran'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(1)</font> :</td>
				<td><select class=select2 id='kodebudgetkont' onchange=getaruskas('main','aruskaskont','x','kontpks'); style='width:155px;'>" . $kdbgtkont . "</select></td>
				
				<td>" . $_SESSION['lang']['aruskas'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(2)</font> :</td>
				<td><select class=select2 id='aruskaskont' onchange=getakunfromak(this.value,'noakunkont'); style='width:155px;'>" . $optaruskas . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['noakun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(3)</font> :</td>
				<td><select class=select2 id='noakunkont' style='width:155px;'>" . $optnoakun . "</select></td>
				
				<td>" . $_SESSION['lang']['keterangan'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(4)</font> :</td>
				<td><input onclick=delnol(this); type='text' id='keterangankontrak' class='myinputtext' style='width:150px;'></td>
				
				<td hidden>" . $_SESSION['lang']['volume'] . " (%)</td>
				<td hidden>:</td>
				<td hidden><input type='text' id='volpersen' onkeyup=getharga('kont') class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' /></td>
			</tr>
			<tr>
				<td>Kode Jasa</td>
				<td nowrap><font style=color:blue;font-size:10;>(5)</font> :</td>
				<td>
					<input type='text' class='myinputtext'  tabindex='21' readonly onclick=\"formcaribarang('kont');\" id='kodebarangkont' style='width:150px;' onkeypress='return angka_doang(event)' />
				</td>
				<td>Nama Jasa</td>
				<td nowrap><font style=color:blue;font-size:10;>(6)</font> :</td>
				<td>
					<span id='namabarangkont'></span>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['satuan'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(7)</font> :</td>
				<td><select class=select2 id='satuankont' style='width:155px;'>" . $optsatuan . "</select></td>
			
				<td>" . $_SESSION['lang']['volume'] . " " . $_SESSION['lang']['total'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(8)</font> :</td>
				<td><input onclick=delnol(this); type='text' id='volkont' onkeyup=getharga('kont') class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' /></td>
			</tr>
			<tr>
				
				<td>" . $_SESSION['lang']['harga'] . " / " . $_SESSION['lang']['satuan'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(9)</font> :</td>
				<td><input onclick=delnol(this); type='text' id='hargakontrak' onkeyup=getharga('kont') class='myinputtextnumber' onkeypress='return tanpa_kutip(event)' style='width:150px;' /></td>
				
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(10)</font> :</td>
				<td><input onclick=delnol(this); type='text' disabled class='myinputtextnumber' id='ttlbyykont' style='width:150px;' onkeypress='return angka_doang(event)' value='0' /></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=6>
					<button class=mybutton onclick=simpandetail('kont');>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton onclick=loaddatakont();>" . $_SESSION['lang']['preview'] . "</button>
				</td>
			</tr>
		</table>
	</fieldset>";

$frm[3] .= "<fieldset><legend>" . $_SESSION['lang']['kontrak'] . "</legend>
			<div id=listdatakontrak></div>
		</fieldset>";
#== KONTRAK ==
$kdbgtlain = "";
$kdbgtlain = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where kodebudget = 'LAIN' order by nama asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kdbgtlain .= "<option value='" . $bar['kodebudget'] . "'>" . $bar['nama'] . "</option>";
}

$frm[5] = "<fieldset><legend>" . $_SESSION['lang']['lain'] . "</legend>";
$frm[5] .= "<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['kodeanggaran'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(1)</font> :</td>
				<td><select class=select2 id='kodebudgetlain' onchange=getaruskas('lain','aruskaslain','x','kontpks'); style='width:155px;'>" . $kdbgtlain . "</select></td>
				
				<td>" . $_SESSION['lang']['aruskas'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(2)</font> :</td>
				<td><select class=select2 id='aruskaslain' onchange=getakunfromak(this.value,'noakunlain'); style='width:155px;'>" . $optaruskas . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['noakun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(3)</font> :</td>
				<td><select class=select2 id='noakunlain' style='width:155px;'>" . $optnoakun . "</select></td>
				
				<td>" . $_SESSION['lang']['keterangan'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(4)</font> :</td>
				<td><input onclick=delnol(this); type='text' id='keteranganlain' class='myinputtext' style='width:150px;'></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td nowrap><font style=color:blue;font-size:10;>(5)</font> :</td>
				<td><input onclick=delnol(this); type='text' class='myinputtextnumber' id='ttlbyylain' style='width:150px;' onkeypress='return angka_doang(event)'></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=6>
					<button class=mybutton onclick=simpandetail('lain');>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton onclick=loaddatalain();>" . $_SESSION['lang']['preview'] . "</button>
				</td>
			</tr>
		</table>
	</fieldset>";

$frm[5] .= "<fieldset><legend>" . $_SESSION['lang']['lain'] . "</legend>
			<div id=loaddatalain></div>
		</fieldset>";
$hfrm[0] = $_SESSION['lang']['sdm'];
$hfrm[1] = $_SESSION['lang']['material'];
$hfrm[2] = $_SESSION['lang']['pemeliharaan'];
$hfrm[3] = $_SESSION['lang']['kontrak'];
$hfrm[4] = $_SESSION['lang']['kndran'];
$hfrm[5] = $_SESSION['lang']['lain'];
drawTab('FRM', $hfrm, $frm, '', '100%');
echo "</div>";



#cont posting
echo "<div id=contposting style=display:none; class='table-scroll' style=height:65vh>";
// echo"<div class='table-scroll'>";
echo "<table class='sortable' cellspacing=1 cellpadding=5 border=0>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center rowspan=2 width=30px>No.</th>
			<th align=centers rowspan=2 style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</th>
			<th align=center colspan=3>" . $_SESSION['lang']['kg'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['sdm'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['material'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['pemeliharaan'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['kontrak'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['kndran'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['lain'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['total'] . "</th>
			<th align=center colspan=3>" . $_SESSION['lang']['rpperkg'] . "</th>
			<th width=30px rowspan=2 align=center>Action</th>
		</tr>
		<tr class=rowheader style=height:25px>
			<th align=center>" . $_SESSION['lang']['tbs'] . "</th>
			<th align=center>" . $_SESSION['lang']['cpo'] . "</th>
			<th align=center>" . $_SESSION['lang']['kernel'] . "</th>
			<th align=center>" . $_SESSION['lang']['tbs'] . "</th>
			<th align=center>" . $_SESSION['lang']['cpo'] . "</th>
			<th align=center>" . $_SESSION['lang']['kernel'] . "</th>
		</tr>
	</thead>
	<tbody id=contpostingdata></tbody>
	</table></div>";
// echo"</div>";

#list data
echo "<div id=listData style=display:block class='table-scroll' style=height:65vh>";
// echo"<div class='table-scroll'>";
echo "<table class='sortable' cellspacing=1 cellpadding=5 border=0>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center rowspan=2 width=30px>No.</th>
			<th align=center rowspan=2 style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['unit'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['station'] . "</th>
			<th align=center colspan=3>" . $_SESSION['lang']['kg'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['sdm'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['material'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['pemeliharaan'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['kontrak'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['kndran'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['lain'] . "</th>
			<th align=center rowspan=2>" . $_SESSION['lang']['total'] . "</th>
			<th align=center colspan=3>" . $_SESSION['lang']['rpperkg'] . "</th>
			<th align=center rowspan=2 colspan=4>Action</th>
		</tr>
		<tr class=rowheader style=height:25px>
			<th align=center>" . $_SESSION['lang']['tbs'] . "</th>
			<th align=center>" . $_SESSION['lang']['cpo'] . "</th>
			<th align=center>" . $_SESSION['lang']['kernel'] . "</th>
			<th align=center>" . $_SESSION['lang']['tbs'] . "</th>
			<th align=center>" . $_SESSION['lang']['cpo'] . "</th>
			<th align=center>" . $_SESSION['lang']['kernel'] . "</th>
		</tr>
	</thead>
	
	<tbody id=contain><script>loaddata(0)</script></tbody>
	<tfoot id=footData></tfoot>
	</table></div>";
// echo "</div>";

$bulan = range(1, 12);
#list data sebaran
echo "<div id=listsebaran style=display:none class='table-scroll' style=height:65vh>";
//echo"<div class='table-scroll'>";
echo "<table class='sortable' cellspacing=1 cellpadding=3 border=0>
		<thead>
			<tr class=rowheader style=height:25px>
				<th rowspan=2 align=center width=25px>#</th>
				<th rowspan=2 align=center width=30px>No.</th>
				<th rowspan=2 align=center style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['station'] . "</th>
				<th rowspan=2 align=center id=mesinsebar style=display:none>" . $_SESSION['lang']['mesin'] . "</th>";
echo "<th rowspan=2 align=center id=kdbgtsebar style=display:none>" . $_SESSION['lang']['kodebudget'] . "</th>";
echo "<th rowspan=2 align=center id=kdbrgsebar style=display:none>" . $_SESSION['lang']['kodebarang'] . "</th>";
echo "<th rowspan=2 align=center id=kdvhcsebar style=display:none>" . $_SESSION['lang']['kodevhc'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
foreach ($bulan as $bln) {
	echo "<th align=center>" . numToMonth($bln, 'E', 'short') . "</th>";
}
echo "</tr>";
echo "<tr class=rowheader style=height:25px>";
echo "<th align=center>
				<button class=mybutton onclick=hapuspersen()>" . $_SESSION['lang']['delete'] . "</button>
				</th>";
foreach ($bulan as $bln) {
	echo "<th align=center><input type=text class=myinputtextnumber id=persen_" . $bln . "  onkeypress=\"return angka_doang(event);\" style=width:45px;border:blue; value='1'></th>";
}
echo "</tr>";
echo "</thead>
			<tbody id=containsebar></tbody>
			<tfoot id=footDatasebar></tfoot>
	</table></div>";
// echo"</div>";

CLOSE_BOX();
echo close_body();
?>