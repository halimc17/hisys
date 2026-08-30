<? //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul>' . getMenu('keu_penagihan') . '</span></br>');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script type="text/javascript">
	notifnoinvoiceafiliasi = "<?php echo $_SESSION['lang']['notifnoinvoiceafiliasi']; ?>";
	notifkontrak = "<?php echo $_SESSION['lang']['notifkontrak']; ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/keu_penagihan.js?v=<?php echo time(); ?>'></script>

<?php
$optPeriode = $optStat = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arrStat = array("0" => $_SESSION['lang']['belumposting'], "1" => $_SESSION['lang']['posted']);
foreach ($arrStat as $key => $val) {
	$optStat .= "<option value='" . $key . "'>" . $val . "</option>";
}
$sPeriode = "select distinct left(tanggal,7) as periode from " . $dbname . ".keu_penagihanht order by left(tanggal,7) desc";
$rPeriode = fetchData($sPeriode);
foreach ($rPeriode as $key2 => $val2) {
	$optPeriode .= "<option value='" . $val2['periode'] . "'>" . $val2['periode'] . "</option>";
}

#byr ke
$nmbank = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
$nmsupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optCust = $optAkun = $optunit = $optkodebarang = $optkodebarangx = $opttipearuskas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
// $sakun=$owlPDO->query("select distinct noakun,rekening,atasnama,namabank from ".$dbname.".keu_5akunbank 
// where  status='1'");
// $sakun->setFetchMode(PDO::FETCH_ASSOC);
// while($rakun=  $sakun->fetch()){
// @$optAkun.="<option value='".$rakun['noakun']."'>".$nmbank[$rakun['namabank']].", Rek: ".$rakun['rekening'].", An. ".$rakun['atasnama']."</option>";
// }
#kodepelanggan
$sakun = $owlPDO->query("select distinct kodecustomer,namacustomer from " . $dbname . ".pmn_4customer  order by namacustomer asc");
$sakun->setFetchMode(PDO::FETCH_ASSOC);
$optCust .= "<optgroup label='CUSTOMER'>";
while ($rakun =  $sakun->fetch()) {
	$optCust .= "<option value='" . $rakun['kodecustomer'] . "'> [" . $rakun['kodecustomer'] . "] - " . $rakun['namacustomer'] . "</option>";
}
$optCust .= "</optgroup>";

$sakunkud = selectQuery($dbname, "kebun_5namakud", "*");
$reskud = fetchData($sakunkud, "OBJECT");
$optCust .= "<optgroup label='SUPPLIER KUD'>";
foreach ($reskud as $key => $d):
	$optCust .= "<option value='" . $d->kodesupplier . "'>[" . $d->afdeling . "] - " . $nmsupp[$d->kodesupplier] . "</option>";
endforeach;
$optCust .= "</optgroup>";


### GSW
$optUnKerja = $optnpwp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$sunkerja = $owlPDO->query("select distinct kodeorg from " . $dbname . ".keu_penagihanht where kodeorg in (".getOrgDetail(2).") order by kodeorg asc");
$sunkerja->setFetchMode(PDO::FETCH_ASSOC);
while ($runkerja =  $sunkerja->fetch()) {
	$optUnKerja .= "<option value='" . $runkerja['kodeorg'] . "'>" . $runkerja['kodeorg'] . " - ".getNamaOrg($runkerja['kodeorg'])."</option>";
}

#unit

$sakun = $owlPDO->query("select * from " . $dbname . ".organisasi where ((tipe='KANWIL' or tipe='HOLDING') or kodeorganisasi IN (select kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' and inti=1)) and length(kodeorganisasi)=4 and kodeorganisasi in (".getOrgDetail(2).")");
$sakun->setFetchMode(PDO::FETCH_ASSOC);
while ($rakun =  $sakun->fetch()) {
	$optunit .= "<option value='" . $rakun['kodeorganisasi'] . "'>" . $rakun['kodeorganisasi'] . "-" . $rakun['namaorganisasi'] . "</option>";
}

#kodebarang
$sBrg = "select namabarang,kodebarang from " . $dbname . ".log_5masterbarang where `kelompokbarang`='400'";
$qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rBrg = $qBrg->fetch()) {
	$optkodebarang .= "<option value=" . $rBrg['kodebarang'] . ">" . $rBrg['namabarang'] . "</option>";
}


$iMt = $owlPDO->query(" select * from " . $dbname . ".setup_matauang order by matauang asc ");
$iMt->setFetchMode(PDO::FETCH_ASSOC);
while ($dMt =  $iMt->fetch()) {
	@$optMtuang .= "<option value='" . $dMt['kode'] . "'>" . $dMt['matauang'] . "</option>";
}

#akuun debet
$sakundbt = $owlPDO->query("select distinct noakun,namaakun from " . $dbname . ".keu_5akun where noakun like '21401%' and detail=1
        order by namaakun asc");
$sakundbt->setFetchMode(PDO::FETCH_ASSOC);
$optDebet = '';
while ($rakun =  $sakundbt->fetch()) {
	$optDebet .= "<option value='" . $rakun['noakun'] . "'>" . $rakun['noakun'] . "-" . $rakun['namaakun'] . "</option>";
}
$sakundbt = $owlPDO->query("select distinct noakun,namaakun from " . $dbname . ".keu_5akun where noakun like '1130100%' and char_length(noakun)=7
        order by namaakun asc");
$sakundbt->setFetchMode(PDO::FETCH_ASSOC);
$optKredit = '';
while ($rakun = $sakundbt->fetch()) {
	$optKredit .= "<option value='" . $rakun['noakun'] . "'>" . $rakun['noakun'] . "-" . $rakun['namaakun'] . "</option>";
}

$arrbyr = array("0" => "Dibayar sendiri", "1" => "Dipungut pihak lain");
$optcrbyr = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach ($arrbyr as $brs1 => $isi1) {
	$optcrbyr .= "<option value=" . $brs1 . ">" . $isi1 . "</option>";
}

$optKarid = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optjnspenghasilan = $optpph = $optJenis = $optTtdjual = $opttipeinv = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$iTtd = $owlPDO->query("select * from " . $dbname . ".pmn_5ttd");
$iTtd->setFetchMode(PDO::FETCH_ASSOC);
while ($dTtd = $iTtd->fetch()) {
	$optTtdjual .= "<option value='" . $dTtd['nama'] . "'>" . $optKarid[$dTtd['nama']] . "</option>";
}

$optPrd = "<option value=''></option>";

for ($x = 0; $x <= 8; $x++) {
	$dte = mktime(0, 0, 0, (date('m') + 2) - $x, 15, date('Y'));
	$optPrd .= "<option value=" . date("Y-m", $dte) . ">" . date("Y-m", $dte) . "</option>";
}

$str = "select noakun,namaakun from " . $dbname . ".keu_5akun where char_length(noakun)=7 and substr(fieldaktif,7,1)='1' order by noakun";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optpph .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

$strq = "select * from " . $dbname . ".pmn_5jenispenghasilan order by idpenghasilan";
$resq = $owlPDO->query($strq) or die(print " Gagal: " . PDOException::getMessage());
$resq->setFetchMode(PDO::FETCH_ASSOC);
while ($barq = $resq->fetch()) {
	$optjnspenghasilan .= "<option value='" . $barq['idpenghasilan'] . "'>" . $barq['idpenghasilan'] . " - " . $barq['namapenghasilan'] . "</option>";
}

$opttahuntanam = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$strq = "select tahuntanam from " . $dbname . ".kebun_spb_detail_vw group by tahuntanam order by tahuntanam";
$resq = $owlPDO->query($strq) or die(print " Gagal: " . PDOException::getMessage());
$resq->setFetchMode(PDO::FETCH_ASSOC);
while ($barq = $resq->fetch()) {
	$opttahuntanam .= "<option value='" . $barq['tahuntanam'] . "'>" . $barq['tahuntanam'] . " </option>";
}

$sql = selectQuery($dbname, "keu_5jenispenagihan", "*", "status='1'");
$res = fetchData($sql, "OBJECT");

foreach ($res as $v) {
	$opttipeinv .= "<option value='" . $v->kodejenis . "'>" . $v->namajenis . "</option>";
}

// $opttipeinv .= "<option value='others'>Others</option>";

$ketArr = array("PM" => "Pengiriman", "PK" => "Pemenuhan Kontrak", "UM" => "Uang Muka", "DS" => "Disposal", "OT" => "Others");

$optJenis .= "<option value='BA'>Berita Acara Serah Terima</option>";
$optJenis .= "<option value='Termin'>Termin</option>";

$opttipetbs = $optjenisinvoice = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$opttipetbs .= "<option value='inti'>Inti</option>";
$opttipetbs .= "<option value='kud'>KUD</option>";
$opttipetbs .= "<option value='ext'>External</option>";


$opttransport = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$opttransport .= "<option value='DARAT'>Darat</option>";
$opttransport .= "<option value='AIR'>Air</option>";

@$optjenisinvoice .= "<option value='UM'>Uang Muka</option>";
@$optjenisinvoice .= "<option value='PL'>Pelunasan</option>";

$arrtipearuskas = array('budget' => 'BUDGET', 'nonbudget' => 'NON BUDGET');
foreach ($arrtipearuskas as $key => $val) {
	$opttipearuskas .= "<option value='" . $key . "'>" . $val . "</option>";
}


$arr = "##noinvoice##jatuhtempo##kodeorganisasi##nofakturpajak##tanggal##bayarke##proses";
$arr .= "##tipeinvoice";
$arr .= "##noref##nopo";
$arr .= "##kodecustomer##noorder##nilaippn##nilaipph##nilaiinvoice##debet##kredit##keterangan1##keterangan2##keterangan3";
$arr .= "##keterangan4##keterangan5##rupiah1##rupiah2##rupiah3##rupiah4##rupiah5##matauang##kurs##keterangan6##rupiah6";
$arr .= "##keterangan7##keterangan8##rupiah7##rupiah8##ttd##jenis##jenisinvoice##kuantitas";
$arr .= "##periode##nobuktipotong##tglbuktipotong##jenispph##pphrupiah##jenispenghasilan##carabayar##npwp##berikat##keterangantambahan";
$arr .= "##notransaksikasbank##kodebarang##transport##npwpunit";
$arr .= "##tipearuskasht##tipearuskashtold";

$hidden = "hidden";

echo "<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=loadData2(0)>
           <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
         <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
echo "<table>";

echo "<tr>
    <td style='padding:3px 0; white-space:nowrap;'>

        <span style='display:inline-block; width:75px;'>
            " . $_SESSION['lang']['noinvoice'] . " :
        </span>
        <input
            type='text'
            id='txtsearch'
            style='width:150px; margin-right:15px;'
            onkeypress='enterkey(event,cariData)'
            maxlength='30'
            class='myinputtext'
        >

        <span style='display:inline-block; width:55px;'>
            " . $_SESSION['lang']['periode'] . " :
        </span>
        <select
            id='tgl_cari'
            style='width:150px; margin-right:15px;'
        >
            " . $optPeriode . "
        </select>

        <span style='display:inline-block; width:65px;'>
            " . $_SESSION['lang']['customer'] . " :
        </span>
        <select
            id='customersch'
            style='width:150px;'
        >
            " . $optCust . "
        </select>

    </td>
</tr>";

echo "<tr>
    <td style='padding:3px 0; white-space:nowrap;'>

        <span style='display:inline-block; width:75px;'>
            " . $_SESSION['lang']['NoKontrak'] . " :
        </span>
        <input
            type='text'
            id='nokontraksch'
            style='width:150px; margin-right:15px;'
            onkeypress='enterkey(event,cariData)'
            maxlength='30'
            class='myinputtext'
        >

        <span style='display:inline-block; width:55px;'>
            " . $_SESSION['lang']['status'] . " :
        </span>
        <select
            id='statId'
            style='width:150px; margin-right:15px;'
        >
            " . $optStat . "
        </select>

        <span style='display:inline-block; width:65px;'>
            " . $_SESSION['lang']['unit'] . " :
        </span>
        <select
            id='unitkerjasch'
            style='width:150px;'
        >
            " . $optUnKerja . "
        </select>

    </td>
</tr>";

echo "</table><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button>";
echo "</fieldset></td>
     </tr>
         </table> ";

CLOSE_BOX();

OPEN_BOX();
echo "<div id=listData>";
// echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo "<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100% >";
echo "<thead>";
echo "<tr align=center><th>No</th><th>" . $_SESSION['lang']['noinvoice'] . "</th>";
echo "<th>" . $_SESSION['lang']['jenis'] . "<br>" . $_SESSION['lang']['noinvoice'] . "</th>";
echo "<th>" . $_SESSION['lang']['unitkerja'] . "</th>";
echo "<th>" . $_SESSION['lang']['tanggal'] . "</th>";
echo "<th>" . $_SESSION['lang']['nodok'] . "</th>";
echo "<th>" . $_SESSION['lang']['nmcust'] . "</th>";
echo "<th>" . $_SESSION['lang']['namasupplier'] . "</th>";
echo "<th>" . $_SESSION['lang']['namabarang'] . "</th>";
echo "<th>" . $_SESSION['lang']['kuantitas'] . "</th>";
echo "<th>" . $_SESSION['lang']['jumlah'] . "</th>";
echo "<th>" . $_SESSION['lang']['ppn'] . "</th>";
echo "<th>" . $_SESSION['lang']['total'] . "</th>";
echo "<th>" . $_SESSION['lang']['transport'] . "</th>";
echo "<th colspan=9>" . $_SESSION['lang']['action'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['createby'] . "</th> 
					 <th align=center>" . $_SESSION['lang']['createtime'] . "</th> 
					 <th align=center>" . $_SESSION['lang']['updateby'] . "</th> 
					 <th align=center>" . $_SESSION['lang']['updatetime'] . "</th>";

echo "</tr></thead><tbody id=continerlist>";
echo "<script>loadData(0)</script>";
echo "</tbody>";



$skeupenagih = $owlPDO->query("select count(*) as rowd from " . $dbname . ".keu_penagihanht where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "'");
$skeupenagih->setFetchMode(PDO::FETCH_ASSOC);
$rkeupenagih = owlBaris($skeupenagih);
$totrows = ceil($rkeupenagih / 10);
if ($totrows == 0) {
	$totrows = 1;
}
$isiRow = '';
for ($er = 1; $er <= $totrows; $er++) {
	$isiRow .= "<option value='" . $er . "'>" . $er . "</option>";
}
echo "<tfoot id=footData>";

echo "</tfoot></table></fieldset>";
echo "</div><input type=hidden id=proses value=insert />";

echo "<div id=formInput style=display:none;>";
echo "<fieldset  style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend><table border=0>";
echo "<tr>
		<td class='bintang'>" . $_SESSION['lang']['noinvoice'] . "</td><td>:</td>
		<td><input type=text id=noinvoice class=myinputtext style=width:150px;  ></td>
		
		<td class='bintang'>" . $_SESSION['lang']['nilaiinvoice'] . "</td><td>:</td>
			<td><input type=text id=nilaiinvoice class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('nilaiinvoice',2);getrpppn()\" /></td>		
	</tr>
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['tanggal'] . "</td><td>:</td>
		<td><input type=text class=myinputtext id=tanggal readonly=readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:150px; onchange=getdate30() maxlength=10 /></td>

		<td>" . $_SESSION['lang']['nilaippn'] . "</td><td>:</td>
		<td><input type=text id=nilaippn class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('nilaippn',2)\" onkeypress='return angka_doang(event)' /></td>		

		
		
	</tr>
	<tr>
		<td> </td><td></td>
		<td></td>

		<td>" . $_SESSION['lang']['pph'] . "</td><td>:</td>
		<td><input type=text id=nilaipph class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('nilaipph',2)\" onkeypress='return angka_doang(event)' /></td>
		
	</tr>
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['tipe'] . " " . $_SESSION['lang']['invoice'] . "</td><td>:</td>
		<td><select id=tipeinvoice onchange='getBarang()' style=width:155px;>" . $opttipeinv . "</select></td>

		<!--
		<td class='bintang'>" . $_SESSION['lang']['jenis'] . " Kontrak</td><td>:</td>
		<td><select id=jenis  style=width:155px; onchange='getdis()'>" . $optJenis . "</select></td>
		-->
		
		<td class='bintang'>" . $_SESSION['lang']['matauang'] . "</td><td>:</td>
		<td><select id=matauang onchange=getKursInvoice() style=width:155px; >" . $optMtuang . "</select></td>

	
		
	</tr>
	
	<tr>		
		<td class='bintang'>" . $_SESSION['lang']['jenis'] . " Kontrak</td><td>:</td>
		<td><select id=jenis  style=width:155px; onchange='getdis()'>" . $optJenis . "</select></td>

		<!--
		<td class='bintang'>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['invoice'] . "</td><td>:</td>
		<td><select id=jenisinvoice style=width:155px;>" . $optjenisinvoice . "</select></td>
		-->

		<td class='bintang'>" . $_SESSION['lang']['kurs'] . "</td><td>:</td>
		<td><input type=text id=kurs class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value='1' /></td>
	</tr>
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['invoice'] . "</td><td>:</td>
		<td><select id=jenisinvoice style=width:155px;>" . $optjenisinvoice . "</select></td>

		<!--
		<td class='bintang'>" . $_SESSION['lang']['tipe'] . " " . $_SESSION['lang']['invoice'] . "</td><td>:</td>
		<td><select id=tipeinvoice onchange='getBarang()' style=width:155px;>" . $opttipeinv . "</select></td>
		-->

		<td class='bintang'>" . $_SESSION['lang']['bayarke'] . "</td><td>:</td>
		<td><select id=bayarke  style=width:155px;>" . $optAkun . "</select><img id='bayarke' onclick=z.elSearch('bayarke',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
	
	</tr>
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['kodebarang'] . "</td>
		<td>:</td>
		<td><select id=kodebarang onchange=getnodok(); style=width:155px>" . $optkodebarangx . "</select></td>
		
		<td>" . $_SESSION['lang']['nofaktur'] . " Pajak</td><td>:</td>
		<td><input type=text class=myinputtext  id=nofakturpajak  style=width:150px; onkeypress='return tanpa_kutip(event)'/>
		<img src=images/skyblue/zoom.png class=zImgBtn onclick=\"searchfaktur(event)\" title=Cari No Faktur ></td>
	</tr>
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['nmcust'] . "</td><td>:</td>
		<td><select id=kodecustomer style=width:155px onchange='getnpwp()' >" . $optCust . "</select></td>

		<td>" . $_SESSION['lang']['nobuktipotong'] . "</td><td>:</td>
		<td><input type=text class=myinputtext id=nobuktipotong style=width:150px;  maxlength=24 /></td>
	
	</tr>
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['statusberikat'] . "</td><td>:</td>
		<td><input title='check => berikat, uncheck => tidak berikat' type=checkbox id=berikat>Berikat / Tidak Berikat</td>
		
		<td>" . $_SESSION['lang']['tglbuktipotong'] . "</td><td>:</td>
		<td><input type=text class=myinputtext id=tglbuktipotong onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:150px;  maxlength=10 readonly/></td>

	</tr>
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['npwp'] . " " . $_SESSION['lang']['customer'] . "</td><td>:</td>
		<td><input type=text class=myinputtext  id=npwp disabled  style=width:150px;  onkeypress='return tanpa_kutip(event)' /></td>
		
		<td class='bintang'>" . $_SESSION['lang']['jatuhtempo'] . "</td><td>:</td>
		<td><input type=text class=myinputtext  readonly=readonly id=jatuhtempo onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:150px;  maxlength=10 /></td>
	</tr>
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['unitkerja'] . "</td>
		<td>:</td>
		<td><select id=kodeorganisasi style=width:155px onchange='getnpwpunit()' >" . $optunit . "</select></td>

		<td class='bintang'>" . $_SESSION['lang']['periode'] . "</td><td>:</td>
		<td>
			<input type=text id=periode class=myinputtext style=width:150px;  >
		</td>
	</tr>
	
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['npwp'] . " " . $_SESSION['lang']['unit'] . "</td>
		<td>:</td>
		<td><select id=npwpunit  style=width:155px;>" . $optnpwp . "</select></td>

		<td class='bintang'>" . $_SESSION['lang']['penandatangan'] . "</td><td>:</td>
		<td><select id=ttd  style=width:155px; >" . $optTtdjual . "</select></td>
	</tr>
	
	<tr>			
		<td class='bintang'>" . $_SESSION['lang']['nodok'] . "</td><td>:</td>
		<td><input type=text id=noorder class=myinputtext style=width:150px; placeholder='click to add' onclick=\"searchNosibp('" . $_SESSION['lang']['find'] . "','<div id=formPencariandata></div>',event)\" /></td>
		
		<td>" . $_SESSION['lang']['notransaksi'] . " Kas/Bank</td>
		<td>:</td>
		<td><input type=text readonly id=notransaksikasbank class=myinputtext style=width:150px; placeholder='click to add' 
			onclick=\"searchnotransaksikasbank('" . $_SESSION['lang']['notransaksi'] . "',event)\"</td>
			
	</tr>

	
	<tr>
		
		<td class='bintang'>" . $_SESSION['lang']['kuantitas'] . "</td><td>:</td><td>
		<input type=text class=myinputtextnumber  id=kuantitas  style=width:150px; onkeyup=\"z.numberFormat('kuantitas',2)\" onkeypress='return angka_doang(event)' value='0'/>
		
		<td class='bintang'>" . $_SESSION['lang']['transport'] . "</td><td>:</td>
		<td><select id=transport  style=width:155px;>" . $opttransport . "</select></td>
	</tr>

	<tr>
        <td class=bintang label=tipearuskasht>Tipe Aruskas (Budget/Non)</td>
        <td>:</td>
        <td><select id=tipearuskasht name=tipearuskasht style=width:150px;>" . $opttipearuskas . "</select><select hidden id=tipearuskashtold name=tipearuskashtold style=width:150px;>" . $opttipearuskas . "</select></td>
	</tr>

	
	<tr " . $hidden . ">
		<td>" . $_SESSION['lang']['carapembayaran'] . "</td><td>:</td>
		<td><select id=carabayar  style=width:155px;>" . $optcrbyr . "</select></td>
		
	
		<td>" . $_SESSION['lang']['pph'] . "&nbsp; [%] [rupiah]</td><td>:</td>
		<td><input type=text id=pphpersen onkeyup=\"getrppph()\" placeholder='%' class=myinputtextnumber style=width:50px; onkeypress='return angka_doang(event)'/>
			<input type=text id=pphrupiah onkeyup=\"z.numberFormat('pphrupiah',2);getpersenpph()\" placeholder='rupiah' class=myinputtextnumber style=width:93px; onkeypress='return angka_doang(event)'/></td>
		
	
		<td>" . $_SESSION['lang']['jenispph'] . "</td><td>:</td>
		<td colspan=2><select id=jenispph  style=width:155px;>" . $optpph . "</select></td>
		
		<td>" . $_SESSION['lang']['jenispenghasilan'] . "</td><td>:</td>
		<td><select id=jenispenghasilan style=width:155px; >" . $optjnspenghasilan . "</select></td>
	</tr>
	
	<tr " . $hidden . ">
		<td >Nomor Refrensi</td><td >:</td>
		<td ><input type=text class=myinputtext id=noref style=width:150px;  maxlength=100 /></td>
		
		<td >Nomor PO/SO</td>
		<td >:</td>
		<td ><input type=text class=myinputtext id=nopo style=width:150px;  maxlength=100 /></td>
	</tr>
	<tr  " . $hidden . ">	
		<td>" . $_SESSION['lang']['keterangan'] . "</td><td>:</td>
		<td colspan=10><input type=text id=keterangantambahan disabled class=myinputtext style=width:720px; onkeypress='return tanpa_kutip(event)' placeholder='Keterangan tambahan untuk jenis others'/></td>
	</tr>
	
	
	<tr><td colspan=6><hr></td></tr>
	<tr hidden>
		<td colspan=6><b><i>" . $_SESSION['lang']['potongan'] . "</i></b></td>
	</tr>
	<tr hidden><td colspan=6><hr></td></tr>
	<tr hidden>
		<td><input type=text id=keterangan1 disabled class=myinputtext style=width:110px; onkeypress='return tanpa_kutip(event)' value='Mutu'  /></td><td>:</td>
		<td><input type=text id=rupiah1 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0 /></td>
		
		<td><input type=text id=keterangan2 disabled class=myinputtext style=width:110px; onkeypress='return tanpa_kutip(event)' value='FFA'  /></td><td>:</td>
		<td><input type=text id=rupiah2 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'   value=0 /></td>

		
	</tr>
	<tr hidden>
		<td><input type=text id=keterangan3 disabled class=myinputtext style=width:110px; onkeypress='return tanpa_kutip(event)' value='M and I'  /></td><td>:</td>
		<td><input type=text id=rupiah3 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0  /></td>
		
		<td><input type=text id=keterangan6 disabled class=myinputtext style=width:110px; onkeypress='return tanpa_kutip(event)' value='Impurities'  /></td><td>:</td>
		<td><input type=text id=rupiah6 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0  /></td>
	</tr>
	<tr hidden>
		
		<td><input type=text id=keterangan4 disabled class=myinputtext style=width:110px; onkeypress='return tanpa_kutip(event)' value='DOBI'  /></td><td>:</td>
		<td><input type=text id=rupiah4 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0  /></td>

		<td><input type=text id=keterangan5 disabled class=myinputtext style=width:110px; onkeypress='return tanpa_kutip(event)' value='Moisture'  /></td><td>:</td>
		<td><input type=text id=rupiah5 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value=0  /></td>

	
	</tr>

	<tr hidden>
		<td><input type=text id=keterangan7 disabled class=myinputtext style=width:110px; onkeypress='return tanpa_kutip(event)' value='Kesusutan Timbangan'  /></td><td>:</td>
		<td><input type=text id=rupiah7 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0  /></td>
		
		<td><input type=text id=keterangan8 disabled class=myinputtext style=width:110px; onkeypress='return tanpa_kutip(event)' value='Kelebihan Timbangan'  /></td><td>:</td>
		<td><input type=text id=rupiah8 class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0  /></td>
		
	</tr>
	<tr hidden>
		<td>" . $_SESSION['lang']['debet'] . "</td><td>:</td>
		<td><select id=debet style=width:155px;>" . $optKredit . "</select></td>
		
		<td>" . $_SESSION['lang']['kredit'] . "</td><td>:</td>
		<td><select id=kredit style=width:155px;>" . $optDebet . "</select></td>

	</tr>
	<tr>
		<td><td>
		<td><button class=mybutton onclick=saveData('keu_slave_penagihan','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>&nbsp;
         <button class=mybutton onclick=cancelData()>" . $_SESSION['lang']['cancel'] . "</button>
		 
		 </td></tr>";
echo "</table></fieldset>";


echo "<fieldset><legend>" . $_SESSION['lang']['form'] . "</legend>";
echo "<table border=0>";
echo "<tr>";
echo "<td>";
echo "<li>Jenis Kontrak :
			<ol>BAST : Digunakan untuk penjualan produk <b>selain TBS</b></ol>
			<ol>Termin : Digunakan untuk penjualan produk <b>hanya TBS</b></ol>
		</li>";
echo "<li>Jenis Penagihan :
			<ol>Uang Muka : Digunakan untuk penjualan produk <b>selain TBS</b></ol>
			<ol>Pelunasan : Digunakan untuk penjualan untuk semua produk</ol>
		</li>";
echo "<li>Detail data transaksi :
			<ol>TBS : <b>terdapat</b> detail data setelah melakukan <i>save</i> atau penyimpanan data, yang bersumber dari transaksi proses tbs</ol>
			<ol>Selain TBS : <b>tidak</b> terdapat detail data setelah melakukan <i>save</i> atau penyimpanan data</ol>
		</li>";
echo "<li>Nomor Dokumen :
			<ol>TBS : Akan muncul saat transaksi detail</ol>
			<ol>Selain TBS : harus memilih nomor dokume/kontrak</ol>
		</li>";
echo "<li>Nilai Invoice :
			<ol>TBS : Akan muncul saat transaksi detail</ol>
			<ol>Selain TBS : muncul saat pemilihan nomor dokumen</ol>
		</li>";
echo "<td>";
echo "</tr>";
echo "</table></fieldset>";




echo "</div>";
CLOSE_BOX();




echo "<div id=detail style=display:none>";
OPEN_BOX('', '<span class=judul>' . $_SESSION['lang']['detail'] . '</span><br>');
$frm[0] = '';
$frm[1] = '';

$emodul = "PGH";
@$arrmodul = getmodulefil($emodul);
foreach ($arrmodul as $key => $val) {
	@$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
}


$frm[0] .= "<fieldset>
		<legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['upload'] . "</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td>" . $_SESSION['lang']['kriteria'] . "</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>" . $optkriteria . "</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick='submitfile()'>Submit</button>
					<button class=mybutton onclick='loadfiles()'>Selesai</button>
				</td>
				
			</tr>
		</table></fieldset>";

$frm[0] .= "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<table class='sortable' cellspacing='1' border='0' width=100%>
			<thead>
			<tr class=rowheader>
				<td align='center'>" . $_SESSION['lang']['nourut'] . "</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		</fieldset>";

$frm[1] .= "<fieldset><legend><b>" . $_SESSION['lang']['form'] . "</b></legend>
			<table>
				<tr>
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tanggal1detail onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:75px; maxlength=10 readonly/>
						s/d
						<input type=text class=myinputtext id=tanggal2detail onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:75px; maxlength=10 readonly/>
					</td>
				</tr>  
				<tr>
					<td>Tipe TBS</td>
					<td>:</td>
					<td>
						<select id=tipetbsdetail style=width:155px;>" . $opttipetbs . "</select>
					</td>
				</tr> 
				<tr>
					<td>" . $_SESSION['lang']['tahuntanam'] . "</td>
					<td>:</td>
					<td>
						<select id=tahuntanam style=width:155px;>" . $opttahuntanam . "</select>
					</td>
				</tr> 
				</tr>
					<td colspan=2></td>
					<td colspan=100>
					<button id=prevbbm class=mybutton onclick=previewdetail()>" . $_SESSION['lang']['preview'] . "</button>
					<button hidden onclick=bataldetail() class=mybutton name=btnBatal id=btnBatal>" . $_SESSION['lang']['cancel'] . "</button>
					<button id=selesaidt class=mybutton onclick=loadData()>" . $_SESSION['lang']['selesai'] . "</button>
					</td>
				</tr>
			</table>
			</fieldset>
			<div id='datadetail'></div>
			<div id='listdatadetail'></div>";





$hfrm[1] = strtoupper($_SESSION['lang']['detail']);
$hfrm[0] = strtoupper($_SESSION['lang']['file']);
drawTab('FRM', $hfrm, $frm, 100, 'auto');
CLOSE_BOX();
echo "</div>";




echo close_body(); ?>