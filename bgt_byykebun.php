<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/bgt_byykebun.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/option.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php
$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optorgsch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach (getOrgDetail(23) as $key => $val) {
	if ($key == $_SESSION['empl']['lokasitugas']) {
		// $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
		// $d = $induk[$key];
		// if ($d != $n) {
		// 	$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
		// 	$optorg .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
		// 	$optorgsch .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
		// }
		$optorg .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
		$optorgsch .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
		// $n = $d;
		// if ($d != $n) {
		// 	$optorg .= "</optgroup>";
		// 	$optorgsch .= "</optgroup>";
		// }
	}
}

$optdiv = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optdivsch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach (getOrgDetail(19) as $key => $val) {
	// $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	// $d = $induk[$key];
	// if ($d != $n) {
	// 	$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
	// 	$optdiv .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	// 	$optdivsch .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	// }
	$optdiv .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
	$optdivsch .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
	// $n = $d;
	// if ($d != $n) {
	// 	$optdiv .= "</optgroup>";
	// 	$optdivsch .= "</optgroup>";
	// }
}

$opttt = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct thntnm from " . $dbname . ".bgt_blok order by thntnm asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$opttt .= "<option value='" . $bar['thntnm'] . "'>" . $bar['thntnm'] . "</option>";
	$optt .= "<option value='" . $bar['thntnm'] . "'>" . $bar['thntnm'] . "</option>";
}

$optthnpost = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select distinct tahunbudget from " . $dbname . ".bgt_budget order by tahunbudget desc";
$res = fetchdata($str);
foreach ($res as $bar) {
	@$optthn .= "<option value='" . $bar['tahunbudget'] . "'>" . $bar['tahunbudget'] . "</option>";
	$optthnpost .= "<option value='" . $bar['tahunbudget'] . "'>" . $bar['tahunbudget'] . "</option>";
}

$kdkel = array('126' => 'TBM', '128' => 'BBT', '611' => 'PNN', '621' => 'TM');
$optkeg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select * from " . $dbname . ".setup_kegiatan where status='1' and namakegiatan not like '%NON AKTIF%' and namakegiatan not like '%TIDAK DIPAKAI%' and substr(noakun,1,3) in ('126','128','611','621') order by kodekegiatan asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	// $d=$kdkel[substr($bar['noakun'],0,3)];
	// if($d!=$n){			
	// 	$nmkel = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok',"kodeklp='".$d."'");
	// 	$optkeg.="<optgroup label='".$d." - ".$nmkel[$d]."'>";
	// }
	$optkeg .= "<option value='" . $bar['kodekegiatan'] . "'>" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
	$n = $d;
	// if($d!=$n){			
	// 	$optkeg.="</optgroup>";
	// }
	$kodeakun[$bar['noakun']] = $bar['noakun'];
}

$optakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach ($kodeakun as $akun) {
	// $d = $kdkel[substr($akun, 0, 3)];
	// if ($d != $n) {
	// 	$nmkel = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok', "kodeklp='" . $d . "'");
	// 	$optakun .= "<optgroup label='" . $d . " - " . $nmkel[$d] . "'>";
	// }
	$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $akun . "'");
	$optakun .= "<option value='" . $akun . "'>" . $akun . " - " . $nmakun[$akun] . "</option>";
	// $n = $d;
	// if ($d != $n) {
	// 	$optakun .= "</optgroup>";
	// }
}

$optip = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$datatipe = array('I' => 'INTI', 'P' => 'PLASMA');
foreach ($datatipe as $d => $v) {
	$optip .= "<option value=" . $d . ">" . $v . "</option>";
}

$optblok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select distinct kodeblok from " . $dbname . ".bgt_blok where substr(kodeblok,1,4) in (" . getOrgDetail(2) . ")  and closed='1' order by kodeblok asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optblok .= "<option value='" . $bar['kodeblok'] . "'>" . $bar['kodeblok'] . "</option>";
}

$optjenis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct statusblok from " . $dbname . ".bgt_blok where substr(kodeblok,1,4) in (" . getOrgDetail(2) . ")  and closed='1' order by statusblok asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmkel = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok', "kodeklp='" . $bar['statusblok'] . "'");
	$optjenis .= "<option value='" . $bar['statusblok'] . "'>" . $nmkel[$bar['statusblok']] . "</option>";
}

$arropt = array('' => $_SESSION['lang']['all'], '1' => 'Yes', '2' => 'No');
foreach ($arropt as $key => $val) {
	@$optsebar .= "<option value='" . $key . "'>" . $val . "</option>";
}
$arrtampil = array('1' => 'Rekap Tahun Tanam', '2' => 'Detail Transaksi');
foreach ($arrtampil as $key => $val) {
	@$opttampil .= "<option value='" . $key . "'>" . $val . "</option>";
}

OPEN_BOX('', '<span class=judul>' . getMenu('bgt_byykebun') . '</span>');
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
							<td><input class=myinputtext id=kodeorgsch style=\"width:145px;\"></td>
							
							<td>" . $_SESSION['lang']['divisi'] . "</td>
							<td>:</td>
							<td><input class=myinputtext id=divisisch style=\"width:145px;\"></td>
							
							
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['tahuntanam'] . "</td>
							<td>:</td>
							<td><select id=ttsch onchange=loaddata(0); style=\"width:150px;\">" . $opttt . "</select></td>
							
							<td>" . $_SESSION['lang']['noakun'] . "</td>
							<td>:</td>
							<td><input type=text title=\"Tips : \nIsikan :\n128 = Untuk menampilkan akun BBT\n126 = Untuk menampilkan akun TBM\n621 = Untuk menampilkan akun TM\n611 = Untuk menampilkan akun PNN\" class=myinputtext placeholder='" . $_SESSION['lang']['all'] . "' id=noakunsch onkeypress='enterkey(event,loaddata)' style=width:145px;></td>
							
							<td>" . $_SESSION['lang']['kegiatan'] . "</td>
							<td>:</td>
							<td><input type=text class=myinputtext title=\"Tips : \nIsikan :\n128 = Untuk menampilkan akun BBT\n126 = Untuk menampilkan akun TBM\n621 = Untuk menampilkan akun TM\n611 = Untuk menampilkan akun PNN\" placeholder='" . $_SESSION['lang']['all'] . "' id=kegiatansch onkeypress='enterkey(event,loaddata)' style=width:145px;></td>
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
							<td><select id=tahunsbr onchange=showsebaran(0); style=\"width:100px;\">" . $optthn . "</select></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select id=kodeorgsbr onchange=showsebaran(0); style=\"width:150px;\">" . $optorgsch . "</select></td>
							
							<td>" . $_SESSION['lang']['divisi'] . " / " . $_SESSION['lang']['blok'] . "</td>
							<td>:</td>
							<td>
								<input type=text title=\"Tips : \n6 = Untuk filter divisi\n10 digit = Untuk filter blok\" class=myinputtext placeholder='" . $_SESSION['lang']['all'] . "' id=divisisbr onkeypress='enterkey(event,showsebaran)' style=width:145px;>
								
								<!--<select id=divisisbr onchange=showsebaran(0); onblur=getThnTnm(this,'ttsbr','" . $_SESSION['lang']['all'] . "'); style=\"width:150px;\">" . $optdivsch . "</select>-->
							</td>
							
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['tahuntanam'] . "</td>
							<td>:</td>
							<td>
								<input class=myinputtext type=text placeholder='" . $_SESSION['lang']['all'] . "' id=ttsbr onkeypress='enterkey(event,showsebaran)' style=width:95px;>
								<!--<select id=ttsbr onchange=showsebaran(0); style=\"width:50px;\">" . $opttt . "</select>-->
							</td>
							
							<td>" . $_SESSION['lang']['noakun'] . "</td>
							<td>:</td>
							<td><input type=text title=\"Tips : \nIsikan :\n128 = Untuk menampilkan akun BBT\n126 = Untuk menampilkan akun TBM\n621 = Untuk menampilkan akun TM\n611 = Untuk menampilkan akun PNN\" class=myinputtext placeholder='" . $_SESSION['lang']['all'] . "' id=noakunsbr onkeypress='enterkey(event,showsebaran)' style=width:145px;></td>
							
							<td>" . $_SESSION['lang']['kegiatan'] . "</td>
							<td>:</td>
							<td><input type=text class=myinputtext title=\"Tips : \nIsikan :\n128 = Untuk menampilkan akun BBT\n126 = Untuk menampilkan akun TBM\n621 = Untuk menampilkan akun TM\n611 = Untuk menampilkan akun PNN\" placeholder='" . $_SESSION['lang']['all'] . "' id=kegiatansbr onkeypress='enterkey(event,showsebaran)' style=width:145px;></td>
							
						</tr>
						<tr>
							<td>Jumlah Baris</td>
							<td>:</td>
							<td><input style=width:95px value='50' type='text' id='jlhbaris' onkeypress='enterkey(event,showsebaran)' class='myinputtextnumber' /></td>
							
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
				<td><input type=text tabindex='1' class=myinputtextnumber id=tahun maxlength=4 onkeypress=\"return angka_doang(event);\" style=width:175px;></td>
				
				<td hidden>" . $_SESSION['lang']['carinoakun'] . "</td>
				<td hidden align=right style='color:blue;font-size:10px;' >(6)</td>
				<td hidden>:</td>
				<td hidden><select id=noakun style=\"width:180px;\">" . $optakun . "</select>
					<img id='noakun' onclick=z.elSearch('noakun',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
				
				<td>" . $_SESSION['lang']['jenis'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(6)</td>
				<td>:</td>
				<td colspan=5><select  tabindex='6'  id=jenis style=\"width:180px;\" onchange=get_kegiatan();>" . $optjenis . "</select></td>
					
			</tr>
			<tr>	
				<td>" . $_SESSION['lang']['unit'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(2)</td>
				<td>:</td>
				<td><select  tabindex='2' id=kodeorg onchange=\"get_div_tt_blok(this,'divisi,tt,blok','" . $_SESSION['lang']['pilihdata'] . "');\" style=\"width:180px;\">" . $optorg . "</select></td>
				
				<td>" . $_SESSION['lang']['kegiatan'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(7)</td>
				<td>:</td>
				<td colspan=5><select class=select2 id=kegiatan  tabindex='7'  style=\"width:180px;\" onchange=get_noakun();>" . $optkeg . "</select></td>
				<td hidden><img id='kegiatan' onclick=z.elSearch('kegiatan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['divisi'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(3)</td>
				<td>:</td>
				<td><select id=divisi  tabindex='3' onchange=\"get_tt_blok(this,'tt,blok,kegiatan,noakun,jenis','" . $_SESSION['lang']['all'] . "');\" style=\"width:180px;\">" . $optdiv . "</select></td>

				<td>" . $_SESSION['lang']['fisik'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(8)</td>
				<td>:</td>
				<td style=width:75px;><input type=text  tabindex='8' class=myinputtextnumber onkeyup=gettotalfisik('fis'); id=volume onkeypress=\"return angka_doang(event);\" style=width:75px;></td>
				
				<td style=width:20px;>Sat</td>
				<td style=width:5px;>:</td>
				<td style=width:60px;><input type=text class=myinputtext id=satuan disabled style=width:60px;></td>
			</tr>
			<tr>	
				<td>" . $_SESSION['lang']['tahuntanam'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(4)</td>
				<td>:</td>
				<td><select id=tt  tabindex='4' style=\"width:180px;\" onchange=getblok('tt');>" . $optt . "</select></td>
				
				<td>" . $_SESSION['lang']['rotasi'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(9)</td>
				<td>:</td>
				<td colspan=5><input type=text class=myinputtextnumber  tabindex='9'  onkeyup=gettotalfisik('rot'); id=rotasi onkeypress=\"return angka_doang(event);\" style=width:175px;></td>
			</tr>
			<tr>	
				<td>" . $_SESSION['lang']['blok'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(5)</td>
				<td>:</td>
				<td><select class=select2 id=blok  tabindex='5' onchange=getblok('blok'); style=\"width:180px;\">" . $optblok . "</select>
					<img hidden id='blok' onclick=z.elSearch('blok',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
				</td>
				
				<td>" . $_SESSION['lang']['total'] . "</td>
				<td align=right style='color:blue;font-size:10px;' >(10)</td>
				<td>:</td>
				<td colspan=5><input type=text class=myinputtextnumber  tabindex='10' onkeyup=gettotalfisik('ttl'); id=totalvolume onkeypress=\"return angka_doang(event);\" style=width:175px;></td>
			</tr>
			<tr>
				<td colspan=3></td>
				<td colspan=9>
					<button class=mybutton  tabindex='11'  onclick=simpanheader()>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton  tabindex='12' onclick=batalheader()>" . $_SESSION['lang']['cancel'] . "</button>
					<button class=mybutton style=display:none tabindex='13' onclick=formatupload()>" . $_SESSION['lang']['form'] . "</button>
				</td>
					<input hidden id=update>
					<input hidden id=index>
			</tr>
		</table>
	</fieldset>
	
	</td>
	<td valign=top>
		<fieldset style=float:left><legend>" . $_SESSION['lang']['info'] . "</legend>
			<label>Jika Tahun Tanam dan Blok dipilih seluruhnya, maka data detail<br>budget perblok akan di proporsi secara otomatis<br>berdasarkan luasan blok masing - masing.</label>
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
$str = "select distinct a.noaruskas, a.nama_aruskas from " . $dbname . ".keu_5aruskas a left join " . $dbname . ".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' order by a.noaruskas asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optaruskas .= "<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
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
				<td>" . $_SESSION['lang']['aruskas'] . "</td>
				<td>:</td>
				<td><select id='aruskassdm'  tabindex='13' style='width:155px;'>" . $optaruskas . "</select></td>
			
				<td>" . $_SESSION['lang']['tipekaryawan'] . "</td><td>:</td>
				<td><select id='kdbudgetsdm'  tabindex='14'  onchange=getharga('sdm'); style='width:155px;'>" . $optupah . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['hkefektif'] . "</td><td>:</td><td><input disabled class='myinputtextnumber'  style='width:150px;' id='hkesdm' /></td>
			
				<td>Norma / Sat / Rot</td><td>:</td><td><input  tabindex='15' type='text' class='myinputtextnumber' style='width:150px;' id='normasdm' onkeyup=getharga('sdm',this.id); onkeypress='return angka_doang(event)' value='0' /></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['jhk'] . "</td><td>:</td><td><input type='text' class='myinputtextnumber' style='width:150px;' id='jhksdm' onkeyup=getharga('sdm',this.id); onkeypress='return angka_doang(event)' value='0' /></td>
			
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber'  tabindex='16' style='width:150px;' id='ttlbyysdm' value='0' onkeypress='return false' /></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=6>
					<button class=mybutton  tabindex='17' onclick=simpandetail('sdm')  >" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton  tabindex='18' onclick=loaddatasdm()  >" . $_SESSION['lang']['preview'] . "</button>
				</td>
			</tr>
		</table>
	</fieldset>";

$frm[0] .= "<fieldset><legend>" . $_SESSION['lang']['sdm'] . "</legend>
			<div id=listdatasdm></div>
		</fieldset>";
#== SDM ==

#== MATERIAL ==
$optkdmat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where substr(kodebudget,1,1)='M' order by kodebudget asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optkdmat .= "<option value='" . $bar['kodebudget'] . "'>" . $bar['kodebudget'] . " - " . $bar['nama'] . "</option>";
}

$frm[1] .= "<fieldset><legend>" . $_SESSION['lang']['material'] . "</legend>";
$frm[1] .= "<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['aruskas'] . "</td>
				<td>:</td>
				<td><select id='aruskasmat'  tabindex='19'style='width:155px;'>" . $optaruskas . "</select></td>
				
				<td>" . $_SESSION['lang']['namabarang'] . "</td>
				<td>:</td>
				<td><span id='namabarang'></span></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kelompokbarang'] . "</td>
				<td>:</td>
				<td><select id='kdbudgetmat' tabindex='20' style='width:155px;' onchange=\"formcaribarang('mat');\">" . $optkdmat . "</select>
					<img id='kdbudgetmat' onclick=z.elSearch('kdbudgetmat',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
				</td>
			
				<td>" . $_SESSION['lang']['kodebarang'] . "</td>
				<td>:</td>
				<td><input type='text' class='myinputtext'  tabindex='21' readonly onclick=\"formcaribarang('mat');\" id='kodebarang' style='width:150px;' onkeypress='return angka_doang(event)' />&nbsp;<img src='images/skyblue/zoom.png' style='position:relative;top:2px;' class=\"zImgBtn\" title='" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "' onclick=\"formcaribarang('mat');\">
				</td>
			</tr>				
			<tr>
				<td>Norma / Sat / Rot</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' tabindex='22' style='width:150px;' id='normamat' onkeyup=getharga('mat',this.id); onkeypress='return angka_doang(event)' value='' /></td>
			
			
				<td>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['setahun'] . "&nbsp;<span id='satuanmat'></span></td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='jumlahmat' onkeyup=getharga('mat',this.id); style='width:150px;' onkeypress='return angka_doang(event)' onkeyup=getharga('mat');></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['hargasatuan'] . "</span></td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='hargamat' disabled style='width:150px;' onkeypress='return angka_doang(event)' /></td>
			
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='ttlbyymat' style='width:150px;' onkeypress='return false'  value='0' /></td>
			</tr>        
			<tr>
				<td></td>
				<td></td>
				<td colspan=6>
					<button class=mybutton  tabindex='22' onclick=simpandetail('mat');>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton  tabindex='23' onclick=loaddatamat();>" . $_SESSION['lang']['preview'] . "</button>
				</td>
			</tr>
		</table>
	</fieldset>
";

$frm[1] .= "<fieldset><legend>" . $_SESSION['lang']['material'] . "</legend>
			<div id=listdatamat></div>
		</fieldset>";
#== MATERIAL ==
#== TOOL ==

$kdbgtalat = "";
$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where kodebudget='TOOL' order by kodebudget asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kdbgtalat .= "<option value='" . $bar['kodebudget'] . "'>" . $bar['kodebudget'] . " - " . $bar['nama'] . "</option>";
}
$frm[2] .= "<fieldset><legend>" . $_SESSION['lang']['peralatan'] . "</legend>";
$frm[2] .= "<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['aruskas'] . "</td><td>:</td>
				<td><select id='aruskasalat' style='width:155px;'>" . $optaruskas . "</select></td>
			
				<td>" . $_SESSION['lang']['kodeanggaran'] . "</td>
				<td>:</td>
				<td><select id='kdbudgetalat' style='width:153px;' disabled>" . $kdbgtalat . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kelompokbarang'] . "</td>
				<td>:</td>
				<td><select id='klbarangalat' style='width:155px;' onchange=\"formcaribarang('alat');\">" . $optkdmat . "</select>
					<img id='klbarangalat' onclick=z.elSearch('klbarangalat',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
				</td>
			
				<td>" . $_SESSION['lang']['kodebarang'] . "</td>
				<td>:</td>
				<td><input type='text' class='myinputtext' readonly onclick=\"formcaribarang('alat');\" id='kodebarangalat' style='width:150px;' onkeypress='return angka_doang(event)' />&nbsp;<img src='images/skyblue/zoom.png' style='position:relative;top:2px;' class=\"zImgBtn\" title='" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "' onclick=\"formcaribarang('alat');\">
				<span id='namabarangalat'></span></td>
			</tr>				
			<tr>
				<td>Norma / Sat / Rot</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' style='width:150px;' id='normaalat' onkeyup=getharga('alat',this.id); onkeypress='return angka_doang(event)' value='' /></td>
			
				<td>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['setahun'] . "&nbsp;<span id='satuanalat'></span></td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='jumlahalat' onkeyup=getharga('alat',this.id); style='width:150px;' onkeypress='return angka_doang(event)' onkeyup=getharga('alat');></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['hargasatuan'] . "</span></td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='hargaalat' disabled style='width:150px;' onkeypress='return angka_doang(event)' /></td>
			
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='ttlbyyalat' style='width:150px;' onkeypress='return false'  value='0' /></td>
			</tr>        
			<tr>
				<td></td>
				<td></td>
				<td colspan=6>
					<button class=mybutton onclick=simpandetail('alat');>" . $_SESSION['lang']['save'] . "</button>
					<button class=mybutton onclick=loaddataalat();>" . $_SESSION['lang']['preview'] . "</button>
				</td>
			
			</tr>
		</table>
	</fieldset>";

$frm[2] .= "<fieldset><legend>" . $_SESSION['lang']['peralatan'] . "</legend>
			<div id=listdataalat></div>
		</fieldset>";
#== TOOL ==
#== KONTRAK ==
$kdbgtkont = "";
$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where kodebudget like '%KONTRAK%' order by nama asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kdbgtkont .= "<option value='" . $bar['kodebudget'] . "'>" . $bar['nama'] . "</option>";
}

$frm[3] = "<fieldset><legend>" . $_SESSION['lang']['kontrak'] . "</legend>";
$frm[3] .= "<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['aruskas'] . "</td>
				<td>:</td>
				<td><select id='aruskaskont' style='width:155px;'>" . $optaruskas . "</select></td>
			
				<td>" . $_SESSION['lang']['kodeanggaran'] . "</td>
				<td>:</td>
				<td><select id='kodebudgetkont' style='width:155px;' disabled>" . $kdbgtkont . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['volume'] . " (%)</td>
				<td>:</td>
				<td><input type='text' id='volpersen' onkeyup=getharga('kont') class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' /></td>
			
				<td>" . $_SESSION['lang']['volume'] . " " . $_SESSION['lang']['total'] . "</td>
				<td>:</td>
				<td><input type='text' id='volkont' disabled class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' /></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['harga'] . " / " . $_SESSION['lang']['satuan'] . "</td>
				<td>:</td>
				<td><input type='text' id='hargakontrak' onkeyup=getharga('kont') class='myinputtextnumber' onkeypress='return tanpa_kutip(event)' style='width:150px;' /></td>
			
				<td>" . $_SESSION['lang']['satuan'] . "</td>
				<td>:</td>
				<td><input type='text' disabled id='satuankont' class='myinputtext' onkeypress='return tanpa_kutip(event)' style='width:150px;' /></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td>:</td>
				<td><input type='text' disabled class='myinputtextnumber' id='ttlbyykont' style='width:150px;' onkeypress='return angka_doang(event)' value='0' /></td>
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
			<div id=listdatakont></div>
		</fieldset>";
#== KONTRAK ==
#== VHC ==

$optkdbgtvhc = "";
$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where kodebudget like '%VHC%' order by nama asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optkdbgtvhc .= "<option value='" . $bar['kodebudget'] . "'>" . $bar['nama'] . "</option>";
}

$optvhc = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$frm[4] = "<fieldset><legend>" . $_SESSION['lang']['kndran'] . "</legend>";
$frm[4] .= "<table cellspacing=1 border=0>
			<tr>
				<td colspan=2>" . $_SESSION['lang']['aruskas'] . "</td>
				<td>:</td>
				<td><select id='aruskasvhc' style='width:155px;'>" . $optaruskas . "</select></td>
			
				<td>" . $_SESSION['lang']['kodeanggaran'] . "</td>
				<td>:</td>
				<td><select id='kdbudgetvhc' style='width:155px;' disabled>" . $optkdbgtvhc . "</select></td>
			</tr>
			<tr>
				<td colspan=2>" . $_SESSION['lang']['kodevhc'] . "</td>
				<td>:</td>
				<td><select id='kodevhc' style='width:155px;' onchange=getharga('vhc','norma');>" . $optvhc . "</select>
					<img id='kodevhc' onclick=z.elSearch('kodevhc',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
			
				<td>Norma / Sat / Rot</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' style='width:150px;' id='normavhc' onkeyup=getharga('vhc','norma');  onkeypress='return angka_doang(event)' value='' /></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['jumlah'] . "</td>
				<td>(<span id=satuanvhc></span>)</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='jlhvhc' style='width:150px;' onkeypress='return angka_doang(event)'   onkeyup=getharga('vhc','jlh'); /></td>
			
				<td>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['setahun'] . "</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='ttlbyyvhc' style='width:150px;' onkeypress='return false' value=0 /></td>
			</tr>
			<tr>
				<td></td>
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

$hfrm[0] = $_SESSION['lang']['sdm'];
$hfrm[1] = $_SESSION['lang']['material'];
$hfrm[2] = $_SESSION['lang']['peralatan'];
$hfrm[3] = $_SESSION['lang']['kontrak'];
$hfrm[4] = $_SESSION['lang']['kndran'];
drawTab('FRM', $hfrm, $frm, '', '100%');
echo "</div>";



#cont posting
echo "<div id=contposting style=display:none;>";
echo "<div id=contpostingdata class='table-scroll' style=height:65vh>
	</div>";
echo "</div>";

#list data
echo "<div id=listData style=display:block class='table-scroll' style=height:65vh>";
// echo"<div class='table-scroll'>";
echo "<table class='sortable' cellspacing=1 cellpadding=5 border=0 width=100%>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=center style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>
			<th align=center style='width:50px'>" . $_SESSION['lang']['kodeorg'] . "</th>
			<th align=center>" . $_SESSION['lang']['divisi'] . "</th>
			<th align=centers style='width:50px'>" . $_SESSION['lang']['tahuntanam'] . "</th>
			<!--<th align=center>" . $_SESSION['lang']['luas'] . "</th>
			<th align=center>" . $_SESSION['lang']['pokok'] . "</th>
			<th align=center style='width:50px'>" . $_SESSION['lang']['noakun'] . "</th>
			<th align=center>" . $_SESSION['lang']['akun'] . "</th>-->
			<th align=center style='width:70px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
			<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>
			<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
			<th align=center>" . $_SESSION['lang']['volume'] . "</th>
			<th align=center>" . $_SESSION['lang']['rotasi'] . "</th>
			<th align=center>" . $_SESSION['lang']['sdm'] . "</th>
			<th align=center>" . $_SESSION['lang']['material'] . "</th>
			<th align=center>" . $_SESSION['lang']['peralatan'] . "</th>
			<th align=center>" . $_SESSION['lang']['kontrak'] . "</th>
			<th align=center>" . $_SESSION['lang']['kndran'] . "</th>
			<th align=center>" . $_SESSION['lang']['total'] . "</th>
			<th align=center>" . $_SESSION['lang']['rpsat'] . "</th>
			<th align=center colspan=3>Action</th>
		</tr>
	</thead>
	
	<tbody id=contain><script>loaddata(0)</script></tbody>
	<tfoot id=footData></tfoot>
	</table></div>";
// echo "</div>";

$bulan = range(1, 12);
#list data sebaran
echo "<div id=listsebaran style=display:none class='table-scroll' style=height:65vh>";
// echo"<div class='table-scroll'>";
echo "<table class='sortable' cellspacing=1 cellpadding=5 border=0 width=100%>
		<thead>
			<tr class=rowheader style=height:25px>
				<th rowspan=2 align=center width=25px>#</th>
				<th rowspan=2 align=center width=30px>No.</th>
				<th rowspan=2 align=center style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['divisi'] . "</th>
				<th rowspan=2 align=center id=bloksebar style=display:none>" . $_SESSION['lang']['blok'] . "</th>
				<th rowspan=2 align=center width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
				<th rowspan=2 align=center style='width:60px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>";
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
	echo "<th align=center><input type=text class=myinputtextnumberdt id=persen_" . $bln . "  onkeypress=\"return angka_doang(event);\" style=width:45px;border:blue; value='1'></th>";
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