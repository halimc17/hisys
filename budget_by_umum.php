<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
$frm[0] = '';
$frm[1] = '';

?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/budget_by_umum.js?v=<?php echo time(); ?>"></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth: true
		});
	});
</script>
<?php

//pilihan tipebudget
$tipebudget = substr($_SESSION['empl']['lokasitugas'], 3, 1);
$akuntambah = '';
if ($tipebudget == 'M') {
	$tipebudget = 'MILL';
	$akuntambah = "or noakun='8211916' or noakun like '9%'";
	$akuntambah = "or noakun like '64%'";
} else if ($tipebudget == 'E') {
	$tipebudget = 'ESTATE';
	$akuntambah = "or noakun='8211916' or noakun like '9%'";
} else {
	$tipebudget = $_SESSION['empl']['tipelokasitugas'];
	if ($tipebudget == 'HOLDING') {
		$akuntambah = "or noakun like '8%' or noakun like '9%'";
	} else {
		$akuntambah = "or noakun='8211916' or noakun like '9%'";
	}
}
$kodeorg = substr($_SESSION['empl']['lokasitugas'], 0, 4);

//pilihan kodebudget
$str = "select kodebudget,nama from " . $dbname . ".bgt_kode where kodebudget like 'UMUM%'";
$optkodebudget = "";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$optkodebudget .= "<option value='" . $bar->kodebudget . "'>" . strtoupper(strtolower($bar->nama)) . "</option>";
}


//pilihan tahunbudget
$str = "select distinct tahunbudget from " . $dbname . ".bgt_budget
	where tipebudget='" . $tipebudget . "' and kodeorg like '" . $kodeorg . "%' and kodebudget like 'UMUM%'
		order by tahunbudget desc ";
$opttahunbudget = "";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	if (date("Y") == $bar->tahunbudget) {
		$opttahunbudget .= "<option value='" . $bar->tahunbudget . "' selected>" . $bar->tahunbudget . "</option>";
	} else {
		$opttahunbudget .= "<option value='" . $bar->tahunbudget . "'>" . $bar->tahunbudget . "</option>";
	}
	$opttahunbudgetx .= "<option value='" . $bar->tahunbudget . "'>" . $bar->tahunbudget . "</option>";
}


//pilihan kodeakun    

$optak = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct a.noaruskas, a.nama_aruskas from " . $dbname . ".keu_5aruskas a left join " . $dbname . ".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1  and a.level='3' and a.status='1' order by a.noaruskas asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	#$optak.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
}


if ($_SESSION['language'] == 'ID') {
	$dd = 'namaakun as namaakun';
} else {
	$dd = 'namaakun1 as namaakun';
}
$str = "select noakun," . $dd . " from " . $dbname . ".keu_5akun where detail=1 and tipeakun in ('Biaya','Penjualan') order by noakun";
$optakun = "";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$optakun .= "<option value='" . $bar->noakun . "'>" . $bar->noakun . " - " . $bar->namaakun . "</option>";
	$akun[$bar->noakun] = $bar->namaakun;
}


$where = "";
if ($_SESSION['empl']['tipelokasitugas'] == 'KEBUN') {
	$where .= " and noakun like '7%'";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'PABRIK') {
	$where .= " and noakun like '7%'";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'TC') {
	$where .= " and noakun like '82%'";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'RND') {
	$where .= " and noakun like '82%'";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
	$where .= " and noakun like '82%'";
}
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
	$where .= " and noakun like '82%'";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'BULKING') {
	$where .= " and noakun like '81%'";
}

$where .= " or noakun like '9%' and aktif='1' and level='5'";


$optakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select * from " . $dbname . ".keu_5akun where 1=1 and aktif='1' and level='5' " . $where . " order by noakun";
$res = fetchdata($str);
foreach ($res as $val) {
	// $d = substr($val['noakun'], 0, 3);
	// if ($d != $n) {
	// 	$optakun .= "<optgroup label='" . getNamaAkun($d) . "'>";
	// }
	$optakun .= "<option value=" . $val['noakun'] . " " . $b . ">" . $val['noakun'] . " - " . $val['namaakun'] . "</option>";
	// $n = $d;
	// if ($d != $n) {
	// 	$optakun .= "</optgroup>";
	// }
}


// ambil subregional
$str = "select subregional from " . $dbname . ".bgt_regional_assignment where kodeunit = '" . $kodeorg . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$subreg = $bar->subregional;
}

$optVhc = "<option value=''>&nbsp;</option>";
$str = "select distinct(kodevhc) as kodevhc, jumlahjam from " . $dbname . ".bgt_vhc_jam where unitalokasi like '" . $kodeorg . "%' and kodevhc!='' order by kodevhc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$e = "";
	if (getNopol($bar->kodevhc) != '') {
		$e .= " - " . getNopol($bar->kodevhc);
	}
	if (getNopol($bar->kodevhc, 'd') != '') {
		$e .= " - " . getNopol($bar->kodevhc, 'd');
	}


	$optVhc .= "<option value='" . $bar->kodevhc . "'>" . $bar->kodevhc . $e . "</option>";
}

$optkm = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL' or $_SESSION['empl']['lokasitugas'] == 'KSBW') {
	$optkm .= "<option value='K'>" . $_SESSION['lang']['keluar'] . "</option>";
	$optkm .= "<option value='M'>" . $_SESSION['lang']['masuk'] . "</option>";
	$tipetransaksi = " and a.tipetransaksi in ('K','M')";
} else {
	$optkm .= "<option value='K'>" . $_SESSION['lang']['keluar'] . "</option>";
	$tipetransaksi = " and a.tipetransaksi in ('K')";
}

if ($tipebudget == 'MILL') {
	$akuntambah = "b.noakun like '7%' or b.noakun like '9%'";
} else if ($tipebudget == 'ESTATE' or $tipebudget == 'RND') {
	$akuntambah = "b.noakun like'7%' or b.noakun like '9%'";
} else if ($tipebudget == 'HOLDING') {
	$akuntambah = "b.noakun like'8%' or b.noakun like '9%' or b.noakun like '5%'";
} else {
	$akuntambah = "b.noakun like'8%' or b.noakun like '9%' or b.noakun like '5%'";
}
$str = "select distinct a.noaruskas, a.nama_aruskas from " . $dbname . ".keu_5aruskas a left join " . $dbname . ".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 " . $tipetransaksi . " and a.level='3' and a.status='1' and (" . $akuntambah . ") order by a.noaruskas asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$option .= "<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
	//  $where = " and tipe ='KANWIL'";
	// // $where = " and tipe ='KANWIL'";
	$where = " and induk = '" . $_SESSION['empl']['kodeorganisasi'] . "'";
} else {
	$where = " and kodeorganisasi = '" . $_SESSION['empl']['lokasitugas'] . "'";
}
//$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){		
// $optorg.="<option value=".$bar['kodeorganisasi']." selected>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// }else{
// $optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// }
// }

$optkddept = makeOption($dbname, 'datakaryawan', 'karyawanid,bagian', "karyawanid='" . $_SESSION['standard']['userid'] . "'");
$optnmdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama');
#$optdept.="<option value=".$optkddept[$_SESSION['standard']['userid']]." selected>".$optnmdept[$optkddept[$_SESSION['standard']['userid']]]."</option>";
#$optdeptx.="<option value=".$optkddept[$_SESSION['standard']['userid']].">".$optnmdept[$optkddept[$_SESSION['standard']['userid']]]."</option>";

$optdept = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$tipebgtmapping = array(
	'BULKING' => 'BULKING',
	'KEBUN' => 'ESTATE',
	'HOLDING' => 'HOLDING',
	'KANWIL' => 'KANWIL',
	'PABRIK' => 'MILL',
	'RND' => 'RND',
	'TC' => 'TC',
	'TRAKSI' => 'TRK',
	'WORKSHOP' => 'WS'
);


$str = "select * from " . $dbname . ".sdm_5departemen_detail a left join " . $dbname . ".sdm_5departemen b on a.kode=b.kode where aktif='1' and (unittipe='" . $_SESSION['empl']['tipelokasitugas'] . "' or unittipe='GLOBAL') order by a.kode";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optdept .= "<option value=" . $bar['kode'] . ">" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
}

$str = "select * from " . $dbname . ".sdm_5departemen order by nama asc ";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optdeptx .= "<option value=" . $bar['kode'] . ">" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
}



$arropt = array('' => $_SESSION['lang']['all'], '1' => 'Yes', '2' => 'No');
foreach ($arropt as $key => $val) {
	@$optsebar .= "<option value='" . $key . "'>" . $val . "</option>";
}
$arrtampil = array('2' => 'Rekap Dept', '1' => 'Rekap Arus Kas', '3' => 'Detail Transaksi');
foreach ($arrtampil as $key => $val) {
	@$opttampil .= "<option value='" . $key . "'>" . $val . "</option>";
}

//$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach (getOrgDetail(1) as $key => $val) {
	if ($_SESSION['empl']['lokasitugas'] == $key) {
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

#=========================
OPEN_BOX('', '<span class=judul>' . getMenu('budget_by_umum') . '</span><br>');
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
							<td>" . $_SESSION['lang']['budgetyear'] . "</td><td>:</td>
							<td><select class=select2 name=pilihtahun0 style=width:150px id=pilihtahun0 onchange=\"updateTab();\"><option value=''>" . $_SESSION['lang']['all'] . "</option>" . $opttahunbudget . "</select></td>

							<td>" . $_SESSION['lang']['kodeorg'] . "</td><td>:</td>
							<td><select class=select2 name=kodeorgsch style=width:150px id=kodeorgsch onchange=\"updateTab();\">" . $optorgsch . "</select></td>
							
							<td>" . $_SESSION['lang']['departemen'] . "</td><td>:</td>
							<td><select class=select2 name=deptsch style=width:150px id=deptsch onchange=\"updateTab();\"><option value=''>" . $_SESSION['lang']['all'] . "</option>" . $optdeptx . "</select></td>
						</tr><tr>
							<td>" . $_SESSION['lang']['aruskas'] . "</td><td>:</td>
							<td><input type=text class=myinputtext id=aruskassch  onkeypress='enterkey(event,updateTab)' onkeypress=\"return tanpa_kutip(event);\" maxlength=45 style=width:145px; /></td>
						
						
							<td>" . $_SESSION['lang']['noakun'] . "</td><td>:</td>
							<td><input type=text class=myinputtext id=pilihakun0  onkeypress='enterkey(event,updateTab)' onkeypress=\"return tanpa_kutip(event);\" maxlength=45 style=width:145px; /></td>

							<td>" . $_SESSION['lang']['keterangan'] . "</td><td>:</td>
							<td><input type=text class=myinputtext id=pilihket0  onkeypress='enterkey(event,updateTab)' onkeypress=\"return tanpa_kutip(event);\" maxlength=45 style=width:145px; /></td>

						</tr>
						<tr>
							<td colspan=2></td><td><button class=mybutton onclick=updateTab()>" . $_SESSION['lang']['preview'] . "</button></td>

						</tr>
					</table>
				</fieldset>
			</div>
			
			<div id=formcariposting style=display:none>
				<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td class='bintang'>" . $_SESSION['lang']['budgetyear'] . "</td>
							<td>:</td>
							<td><select id=tahunpostsch onchange=showposting(0); style=\"width:150px;\">
							<option value='' selected>" . $_SESSION['lang']['all'] . "</option>" . $opttahunbudgetx . "</select></td>
							
							<td class='bintang'>" . $_SESSION['lang']['kodeorg'] . "</td>
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
							<td class='bintang'>" . $_SESSION['lang']['budgetyear'] . "</td><td>:</td>
							<td><select style=width:150px id=tahunbudgetsbr onchange='showsebaran(0)'><option value=''>" . $_SESSION['lang']['all'] . "</option>" . $opttahunbudget . "</select></td>

							<td class='bintang'>" . $_SESSION['lang']['kodeorg'] . "</td><td>:</td>
							<td><select name=kodeorgsch style=width:150px id=kodeorgsbr  onchange='showsebaran(0)'>" . $optorgsch . "</select></td>
							
							<td>" . $_SESSION['lang']['departemen'] . "</td><td>:</td>
							<td><select name=deptsch style=width:150px id=deptsbr onchange=\"showsebaran();\"><option value=''>" . $_SESSION['lang']['all'] . "</option>" . $optdeptx . "</select></td>
						</tr><tr>
							<td class='bintang'>" . $_SESSION['lang']['aruskas'] . "</td><td>:</td>
							<td><input type=text class=myinputtext id=aruskassbr  onkeypress='enterkey(event,showsebaran)' onkeypress=\"return tanpa_kutip(event);\" maxlength=45 style=width:145px; /></td>
						
						
							<td class='bintang'>" . $_SESSION['lang']['noakun'] . "</td><td>:</td>
							<td><input type=text class=myinputtext id=akunsbr  onkeypress='enterkey(event,showsebaran)' onkeypress=\"return tanpa_kutip(event);\" maxlength=45 style=width:145px; /></td>

							<td class='bintang'>" . $_SESSION['lang']['keterangan'] . "</td><td>:</td>
							<td><input type=text class=myinputtext id=ketsbd  onkeypress='enterkey(event,showsebaran)' onkeypress=\"return tanpa_kutip(event);\" maxlength=45 style=width:145px; /></td>

						</tr>
						<tr>
							<td>Jumlah Baris</td>
							<td>:</td>
							<td><input style=width:145px value='50' type='text' id='jlhbaris' onkeypress='enterkey(event,showsebaran)' class='myinputtextnumber' /></td>
							
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
#=========================
$str = "select distinct regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "' ";
$res = fetchdata($str);
$region = $res[0]['regional'];

$optbarang = "<option value=''></option>";
// $str = "select distinct kodebarang, hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$region."' and closed=1 and kodebarang like '3%'";
// $res = fetchData($str);
// foreach($res as $bar){
// $s = "select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$bar['kodebarang']."'";
// $nm = fetchData($s)[0];
// if($bar['hargasatuan']>0){				
// $optbarang.="<option value='".$bar['kodebarang']."'>".$bar['kodebarang']." - ".$nm['namabarang']."</option>";
// }else{
// $optbarang.="<option value='".$bar['kodebarang']."' disabled>".$bar['kodebarang']." - ".$nm['namabarang']."</option>";
// }
// }	

echo "<div id=inputdata style=display:none;>";
OPEN_BOX();
echo "<fieldset style=float:left><legend>Input</legend><table cellspacing=1 border=0>
    <tr><td class='bintang'>" . $_SESSION['lang']['tipeanggaran'] . " <font style=font-size:10px;color:blue;>(1)</font></td><td>:</td><td>
        <input type=text class=myinputtext id=tipebudget name=tipebudget onkeypress=\"return angka_doang(event);\" maxlength=2 disabled=true style=width:145px; value=\"" . $tipebudget . "\"/></td>
		
	
		<td class='bintang'>" . $_SESSION['lang']['budgetyear'] . " <font style=font-size:10px;color:blue;>(2)</font></td><td>:</td><td>
        <input type=text class=myinputtext id=tahunbudget onchange=getkodevhc(); name=tahunbudget onkeypress=\"return angka_doang(event);\" maxlength=4 style=width:145px; /></td>
		<td class='bintang'>" . $_SESSION['lang']['kodeanggaran'] . "  <font style=font-size:10px;color:blue;>(3)</font></td><td>:</td><td>
			<select id=kodebudget name=kodebudget style='width:150px;'>" . $optkodebudget . "</select>
		</td>
	</tr>
    <tr>
		
		<td class='bintang'>" . $_SESSION['lang']['kodeorg'] . "  <font style=font-size:10px;color:blue;>(4)</font></td><td>:</td><td>
			<select id=kodeorg onchange=\"getkodevhc();\" name=kodeorg style='width:150px;'>" . $optorg . "</select>
		</td>
	
		<td>" . $_SESSION['lang']['departemen'] . "  <font style=font-size:10px;color:blue;>(5)</font></td><td>:</td><td>
        <select class=select2 id=dept onchange=\"getnoakun();\" name=dept style='width:150px;'>" . $optdept . "</select></td>
		

	</tr>
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['jenis'] . "  <font style=font-size:10px;color:blue;>(6)</font></td><td>:</td><td>
			<select name=keluarmasuk id=keluarmasuk style='width:150px;' onchange=getaruskas(); >" . $optkm . "</select>
		</td>	
		
		<td class='bintang'>" . $_SESSION['lang']['noakun'] . "  <font style=font-size:10px;color:blue;>(7)</font></td><td>:</td><td>
			<select class=select2 name=jenisbiaya id=jenisbiaya onchange=getaruskas(); style='width:150px;'>" . $optakun . "</select>
		</td>
		
		<td class='bintang'>" . $_SESSION['lang']['aruskas'] . "  <font style=font-size:10px;color:blue;>(8)</font></td><td>:</td><td>
			<select class=select2 name=aruskas id=aruskas style='width:150px;'><option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>" . $option . "</select>
		</td>	
		
	</tr>
	<tr>
		<td>Kendaraan <font style=font-size:10px;color:blue;>(9)</font></td>
		<td>:</td>
		<td><select class=select2  id=kodevhc style='width:150px;' onchange=\"kalikanRp('kodevhc')\" >" . $optVhc . "</select></td>
		
		<td>KM/HM Setahun <font style=font-size:10px;color:blue;>(10)</font></td><td>:</td>
		<td><input disabled  class=myinputtextnumber style='width:145px' onkeyup=kalikanRp('kodevhc') id=jamperthn onkeypress='return angka_doang(event)'><input hidden id=jamperthnold>
        </td>
	
		<td>Rp Kendaraan <font style=font-size:10px;color:blue;>(11)</font></td><td>:</td>
		<td><input disabled  class=myinputtextnumber style='width:145px' id=rpvra onkeypress='return angka_doang(event)'>
        </td>
    </tr>
	
	<tr>
		<td>Kode Barang <font style=font-size:10px;color:blue;>(12)</font></td>
		<td>:</td>
		<td>
			<input hidden type=text readonly class=myinputtext id=kodebarang name=kodebarang onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:145px; onclick=\"searchBrg(1,'" . $_SESSION['lang']['findBrg'] . "','<center>Find : <input type=text class=myinputtext id=no_brg ><button class=mybutton onclick=findBrg(1)>Find</button></center><div id=container></div><input type=hidden id=nomor name=nomor>',event)\">
			
			
			<input type=text readonly class=myinputtext id=namabarang name=namabarang onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:145px; onclick=\"searchBrg(1,'" . $_SESSION['lang']['findBrg'] . "','<center>Find : <input type=text class=myinputtext id=no_brg ><button class=mybutton onclick=findBrg(1)>Find</button></center><div id=container></div><input type=hidden id=nomor name=nomor>',event)\">
		</td>
		
		<td>Jumlah Barang <font style=font-size:10px;color:blue;>(13)</font></td><td>:</td>
		<td><input disabled type=text class=myinputtextnumber style='width:145px' onkeyup=kalikanRp('kodebarang') id=jlhbarang onkeypress='return angka_doang(event)'>
        </td>
	
		<td>Rp Barang <font style=font-size:10px;color:blue;>(14)</font></td><td>:</td>
		<td><input disabled type=text class=myinputtextnumber style='width:145px' id=rpbarang onkeyup=hitungrupiah(); onkeypress='return angka_doang(event)'>
        </td>
    </tr>
	
	<tr>
		<td class='bintang'>Jumlah Setahun <font style=font-size:10px;color:blue;>(15)</font></td><td>:</td><td>
		<input type=text class=myinputtextnumber style='width:145px' id=jumlahbiaya onkeypress='return angka_doang(event)'>
		</td>
		
	</tr>
    <tr>
		<td class='bintang' valign=top>" . $_SESSION['lang']['keterangan'] . "  <font style=font-size:10px;color:blue;>(16)</font></td><td valign=top>:</td>
	
	<td colspan=7>
        <textarea rows='2' maxlength='124' id='ketUmum' type='text' onkeypress='return tanpa_kutip(event)' style='width:700px;'></textarea>
		<!--
		<input type=text class=myinputtext id=ketUmum name=ketUmum onkeypress=\"return tanpa_kutip(event);\" maxlength=45 style=width:145px; />-->
		
	</td>
		
	</tr>
    <tr><td></td><td></td><td colspan=4>
        <button class=mybutton id=simpan name=simpan onclick=simpan()>" . $_SESSION['lang']['save'] . "</button>
        <input type=hidden id=tersembunyi name=tersembunyi value=tersembunyi >
        <input type=hidden id=idbgt>
        <input type=hidden id=method value='saveatas'>
    </td></tr></table></fieldset>
	
	";
CLOSE_BOX();
echo "</div>";

OPEN_BOX();

echo "<div class='table-scroll' style=overflow:auto;width:100%;height:450px; id=container0><script>updateTab()</script></div>";

$bulan = range(1, 12);
echo "<div id=listsebaran style=display:none>";
echo "<div class='table-scroll'>
		<table class='sortable' cellspacing=1 cellpadding=3 border=0>
		<thead>
			<tr class=rowheader style=height:25px>
				<th rowspan=2 align=center width=25px>#</th>
				<th rowspan=2 align=center width=30px>No.</th>
				<th rowspan=2 align=center style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>";
echo "<th rowspan=2 align=center id=headdept colspan=3>" . $_SESSION['lang']['departemen'] . "</th>";
echo "<th rowspan=2 align=center style=display:none id=arusdept colspan=3>" . $_SESSION['lang']['aruskas'] . "</th>";
echo "<th rowspan=2 align=center style=display:none id=akundept colspan=3>" . $_SESSION['lang']['noakun'] . "</th>";
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
echo "</div>";

#cont posting
echo "<div id=contposting style=display:none;>";
echo "<div id=contpostingdata >
	</div>";
echo "</div>";


CLOSE_BOX();

echo close_body();
?>