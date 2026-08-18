<? //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("", "<span class=judul>" . getMenu('keu_tagihanv2') . "</span>"); //1 O

require_once('lib/zSelect2.php');
?>
<script language=javascript>
    notifpopilih = "<?php echo $_SESSION['lang']['notifpopilih']; ?>";
    notiftagihtanggal = "<?php echo $_SESSION['lang']['notiftagihtanggal']; ?>";
    notifpostingpenagihan = "<?php echo $_SESSION['lang']['notifpostingpenagihan']; ?>";
</script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/keu_tagihanv2.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php
//<script type="text/javascript" src="js/keu_tagihanv2.js?v=1.5" /></script>
$_SESSION['efiltgh'] = array();

#jenisinvoice
$optrek = $optjenispajak = $optNpwp = $optMtUang = $optUnit = $optSupplier = $optPt = $optJenis = $optbagian = $optsup = $opttipearuskas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sJenis = "select * from " . $dbname . ".keu_5jenistagihan where status=1";
$rJenis = fetchData($sJenis);
foreach ($rJenis as $row => $data) {
    if ($data['jurnal'] == 1) {
        $optJenis .= "<option value='" . $data['kode'] . "'>NVM : " . $data['namajenis'] . "</option>";
    } else {
        $optJenis .= "<option value='" . $data['kode'] . "'>VM : " . $data['namajenis'] . "</option>";
    }
}

#untuk list PT
$optOrg = array();
$optOrg = getOrgDetail(3);
foreach ($optOrg as $row => $data) {
    if ($row != '') {
        $optPt .= "<option value='" . $row . "'>" . $data . "</option>";
    }
}

# ambil unit
$arrUnit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$lstUnit = getOrgDetail(1);
$dtMul = 0;
$listOrg = '';
foreach ($lstUnit as $row => $isiDt) {
    if (substr($row, 0, 5) == 'Pilih') {
        continue;
    }
    if ($dtMul == 0) {
        $listOrg = "'" . $row . "'";
        $dtMul = 1;
    } else {
        $listOrg .= ",'" . $row . "'";
    }
}

// $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".$listOrg.")";
$str = "select induk, kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi in (" . $listOrg . ") order by induk";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $d = $bar['induk'];
    if ($d != $n) {
        $optUnit .= "<optgroup label='" . $bar['induk'] . " - " . getNamaOrg($bar['induk']) . "'>";
    }
    $optUnit .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
    $n = $d;
    if ($d != $n) {
        $optUnit .= "</optgroup>";
    }
}

# ambil npwp
$str = "select npwp from " . $dbname . ".setup_org_npwp";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optNpwp .= "<option value='" . $bar['npwp'] . "'>" . $bar['npwp'] . "</option>";
}

#ambil list supplier
$str = "select * from " . $dbname . ".log_5supplier where status=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optSupplier .= "<option value='" . $bar['supplierid'] . "'>" . $bar['namasupplier'] . " (" . $bar['supplierid'] . ")</option>";
}

#matauang
$str = "select * from " . $dbname . ".setup_matauang";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optMtUang .= "<option value='IDR'>IDR</option>";
while ($bar = $res->fetch()) {
    if ($bar['kode'] != 'IDR') {
        $optMtUang .= "<option value='" . $bar['kode'] . "'>" . $bar['kode'] . "</option>";
    }
}

#transaksipajak
$str = "select * from " . $dbname . ".keu_5transaksipajak where status=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$no += 1;
    if ($no == 1) {
        $optjenispajak .= "<option value='" . $bar['id'] . "' selected>" . $bar['id'] . " - " . $bar['jenis'] . "</option>";
    } else {
        $optjenispajak .= "<option value='" . $bar['id'] . "'>" . $bar['id'] . " - " . $bar['jenis'] . "</option>";
    }
}

$arrtipearuskas = array('budget' => 'BUDGET', 'nonbudget' => 'NON BUDGET');
foreach ($arrtipearuskas as $key => $val) {
    $opttipearuskas .= "<option value='" . $key . "'>" . $val . "</option>";
}

#untuk bagian(departemen)

$str = "select kode,nama from " . $dbname . ".sdm_5departemen where aktif='1' order by kode";
$res = fetchdata($str);
foreach ($res as $val) {
    $optbagian .= "<option value='" . $val['kode'] . "'>" . $val['nama'] . "</option>";
}


$optposting = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optposting .= "<option value='0'>" . $_SESSION['lang']['belumposting'] . "</option>";
$optposting .= "<option value='1'>" . $_SESSION['lang']['posting'] . "</option>";
/*
$jenisSearch = array(
    'noinvoice' => $_SESSION['lang']['noinvoice'],
    'noinvoicesupplier' => $_SESSION['lang']['noinvoice']." Supplier",
    // 'namasupplier' => $_SESSION['lang']['supplier'],
    'nopo' => $_SESSION['lang']['nopo'],
);

foreach($jenisSearch as $row=>$jns){
    $optjenisSearch.="<option value='".$row."'>".$jns."</option>";
}
*/

echo "<table border=0>
     <tr valign=moiddle>
         <td align=center style='width:70px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
         <td align=center style='width:70px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
         <td>";

echo "<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
echo "<table border=0>";
echo "
			<tr>
				<td>" . $_SESSION['lang']['noinvoice'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=noinvoicesch onblur='enterkey(event,loadData(0))' size=50 class=myinputtext style=\"width:140px;\">
				</td>
				
				<td>" . $_SESSION['lang']['nodok'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=noposch onblur='enterkey(event,loadData(0))' size=50 class=myinputtext style=\"width:140px;\">
				</td>
				
				<td>" . $_SESSION['lang']['unit'] . "</td>
				<td>:</td>		
				<td>
					<select id=unitsch onchange=loadData(0); class=select2 style=\"width:140px;\">'" . $optUnit . "'</select>
				</td>
				
				<td>" . $_SESSION['lang']['tanggal'] . "</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext onblur='enterkey(event,loadData(0))'  id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:57px;/>
					s/d
					<input type=text class=myinputtext onblur='enterkey(event,loadData(0))'  id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:57px;/>			
				</td>
			</tr>	
			<tr>
				<td>" . $_SESSION['lang']['noinvoice'] . " " . $_SESSION['lang']['supplier'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=noinvoicesuppliersch onblur='enterkey(event,loadData(0))'  size=50 class=myinputtext style=\"width:140px;\">
				</td>
				
				<td>" . $_SESSION['lang']['tipeinvoice'] . "</td>
				<td>:</td>		
				<td><select id=tipeinvoicesch class=select2 onchange=loadData(0); style=\"width:145px;\">'" . $optJenis . "'</select></td>			
		
				
				<td>" . $_SESSION['lang']['supplier'] . "</td>
				<td>:</td>		
				<td><select id=kodesuppliersch class=select2 onchange=loadData(0); style=\"width:140px;\">'" . $optSupplier . "'</select></td>
				
				<td>" . $_SESSION['lang']['posting'] . "</td>
				<td>:</td>		
				<td>
					<select id=postingsch onchange=loadData(0); class=select2 style=\"width:145px;\">'" . $optposting . "'</select>
				</td>
			</tr>	
			
			<tr>
			<td></td><td></td>
            <td colspan=3><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td>
        </tr>
	</table>";


// echo"<select id=sJenis style=width:150px;>".$optjenisSearch."</select> &nbsp;";
// echo"<input type=text id=sNoTrans class=myinputtext /> &nbsp;";
// echo"<select id=ssupplier style=width:150px;>".$optSupplier."</select>
// <img id=ssupplier onclick=z.elSearch('ssupplier',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'> &nbsp;";
// echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";


echo "</fieldset></td>";





echo "</tr>";
echo "</table> ";
CLOSE_BOX();

OPEN_BOX();
echo "<div id=listData>";
// echo"<fieldset style=float:left;clear:right><legend><b>".$_SESSION['lang']['print']."</b></legend>";
// echo"<img class=\"zImgBtn\" src=\"images/skyblue/print.png\" style=\"cursor:pointer\" onclick=\"print()\" title=\"Print Page\">
// <img class=\"zImgBtn\" src=\"images/skyblue/pdf.jpg\" style=\"cursor:pointer\" onclick=\"printPDF(event)\" title=\"Print PDF\">";
// echo"</fieldset>";
$cols = "noinvoice,noinvoicesupplier,tipeinvoice,noakun,pt,unit,tanggalinvoice,tanggaldokumen,nodok,supplier,keterangan,nilaiinvoice,selisih,dibuat,updateby,dipostingoleh";
$listTitle = explode(",", $cols);
// echo"<fieldset style=clear:left><legend><b>".$_SESSION['lang']['list']."</b></legend>";
echo "<div class=table-scroll style='height:60vh'>";
echo "<table cellpading=3 width=100% cellspacing=1 border=0 class=sortable>";
echo "<thead>";
echo "<tr align=center>";
foreach ($listTitle as $key => $value) {
    echo "<th>" . $_SESSION['lang'][$value] . "</th>";
}
echo "<th colspan=7>" . $_SESSION['lang']['action'] . "</th>";
echo "</tr></thead><tbody id=continerlist>";
echo "<script>loadData(0)</script>";
echo "</tbody>";
echo "<tfoot id=footData>";
echo "</tfoot></table></div>";
// echo"</fieldset>";
echo "</div><input type=hidden id=proses value='add' />";


echo "<div id=formInput style=display:none;>"; //style=display:none;
// echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
echo "<fieldset><legend>" . $_SESSION['lang']['form'] . "</legend>
    <table border=0 >";
/*
	 <td><select id=tipeinvoice onchange=disnopo() style=width:150px;>".$optJenis."</select><img id=tipeinvoice_find onclick=z.elSearch('tipeinvoice',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'></td>
       
	*/



$arr = "###noinvoice###noinvoicesupplier###kodeorg###unit###npwp###npwppph###tanggal###tanggalinvoice###jatuhtempo";
$arr .= "###tipeinvoice###nopo###supplier###reksupplier###jenissupplier###noakun###matauang###kurs";
$arr .= "###nilaidpp###nilaiinvoice###nofp###tanggalnofp###keterangan###bagian###nosj";
$arr .= "###tipearuskasht###tipearuskashtold";

echo "<tr>	
		<td>" . $_SESSION['lang']['noinvoice'] . "</td>
		<td>:</td>
		<td><input type=text id=noinvoice style=width:146px; class=myinputtext disabled></td>
		
		<td class=bintang>Tanggal Terima Dokumen</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tanggalinvoice placeholder='tanggal terima dokumen invoice' title='tanggal terima dokumen invoice' readonly onmousemove=setCalendar(this.id) onkeypress=return false; style=width:146px; maxlength=10 /></td>
        
		<td class=plus>" . $_SESSION['lang']['noakun'] . " " . $_SESSION['lang']['hutang'] . "</td>
        <td>:</td>
        <td><input type=text id=noakun style=width:150px; class=myinputtext disabled></td>

		<td class=bintang>" . $_SESSION['lang']['tanggalinvoice'] . "</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tanggal placeholder='tanggal dokumen ' onchange=getdate30()  onchange=gettglfp() readonly onmousemove=setCalendar(this.id) onkeypress=return false; style=width:150px; maxlength=10 /></td>
		
        </tr>";

echo "<tr>  
        <td class='bintang'>" . $_SESSION['lang']['noinvoicesupplier'] . "</td>
        <td>:</td>
        <td><input type=text id=noinvoicesupplier style=width:146px; class=myinputtext onkeypress='return tanpa_kutip(event)'></td>
        
		<td class=bintang>" . $_SESSION['lang']['tipeinvoice'] . "</td>
        <td>:</td>
        <td><select id=tipeinvoice onchange=showhidesearchnodok() style=width:150px;>" . $optJenis . "</select>
		<img id=tipeinvoice_find onclick=z.elSearch('tipeinvoice',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'></td>
		
		<td class=bintang>" . $_SESSION['lang']['matauang'] . "</td>
        <td>:</td>
        <td><select id=matauang onchange='getkurs()' style=width:155px; >" . $optMtUang . "</select></td>	
		
		<td>" . $_SESSION['lang']['jatuhtempo'] . "</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=jatuhtempo placeholder='Otomatis jika tanggal terima terisi' onmousemove=setCalendar(this.id) onkeypress=return false; style=width:150px; maxlength=10 readonly/></td>
		
   </tr>";

echo "<tr>  
        <td class=bintang>" . $_SESSION['lang']['pt'] . "</td>
        <td>:</td>
        <td><select id=kodeorg style=width:150px; onchange=getunit(this,0,0,0)>" . $optPt . "</select></td>
        
		
		<td class=bintang>" . $_SESSION['lang']['nodok'] . "</td><td>:</td>
		<td><input type=text id=nopo onclick=getnodok(); class=myinputtext style=width:145px;cursor:pointer; placeholder='nomor dokumen' />
		<img id=buttonsearchnodok onclick=getnodok() class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
		</td>
		
		<td class=bintang>" . $_SESSION['lang']['kurs'] . "</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber id=kurs value='1' onkeypress=return angka_doang(event); style=width:150px; disabled=disabled /></td>
   
     
        <td>" . $_SESSION['lang']['nofp'] . "</td>
        <td>:</td>
        <td><input type=text id=nofp style=width:150px; class=myinputtext onkeypress='return tanpa_kutip(event)'></select></td>
		
		
     </tr>";
echo "<tr>  
        <td class=bintang>" . $_SESSION['lang']['unit'] . "</td>
        <td>:</td>
		<td><select id=unit style=width:150px;>" . $optUnit . "</select>
		<img id=unit onclick=z.elSearch('unit',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
		</td>
		
		<td class=bintang>" . $_SESSION['lang']['supplier'] . "</td>
        <td>:</td>
		<td><select id=supplier style=width:150px; onchange=getrek(this.value,0)>" . $optSupplier . "</select>
		<img id=supplier onclick=z.elSearch('supplier',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
		</td> 
		
		<td class=bintang>" . $_SESSION['lang']['nilaidpp'] . "</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber id=nilaidpp onkeyup=\"z.numberFormat('nilaidpp',2);\"  onkeypress=return angka_doang(event); style=width:150px; /></td>

        
        
        <td>" . $_SESSION['lang']['tanggalnofp'] . "</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tanggalnofp readonly onmousemove=setCalendar(this.id) onkeypress=return false; style=width:150px; maxlength=10 /></td>
   
		
    </tr>";
echo "<tr>
        <td class=bintang>NPWP PPn</td>
        <td>:</td>
        <td><select id=npwp style=width:150px; >" . $optNpwp . "</select></td>
		
		<td class=bintang>Rekening supplier</td>
        <td>:</td>
        <td><select id=reksupplier style=width:150px; >" . $optrek . "</select></td>
        
        <td class=bintang>" . $_SESSION['lang']['nilaiinvoice'] . "</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber id=nilaiinvoice onkeyup=\"z.numberFormat('nilaiinvoice',2);\"  onkeypress=return angka_doang(event); style=width:150px; /></td>

        <td>" . $_SESSION['lang']['suratjalan'] . "</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=nosj style=width:150px; disabled /></td>
       
     </tr>";
echo "<tr>  

		<td class=bintang>NPWP PPH</td>
        <td>:</td>
        <td><select id=npwppph style=width:150px; >" . $optNpwp . "</select></td>
        
		<td class=bintang>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['supplier'] . "</td>
        <td>:</td>
        <td><select id=jenissupplier onchange=getnoakunsup() style=width:150px; >" . $optsup . "</select></td>
		
        <td class=bintang>" . $_SESSION['lang']['deskripsipembelian'] . "</td>
        <td>:</td>
        <td><input type=text id=keterangan style=width:150px;  placeholder='keterangan invoice' class=myinputtext onkeypress='return tanpa_kutip(event)'></td>
		
        <td class=bintang label=tipearuskasht>Tipe Aruskas (Budget/Non)</td>
        <td>:</td>
        <td><select id=tipearuskasht name=tipearuskasht style=width:150px;>" . $opttipearuskas . "</select><select hidden id=tipearuskashtold name=tipearuskashtold style=width:150px;>" . $opttipearuskas . "</select></td>
		
     
     </tr>";
echo "<tr>  

		
	
		   
       
        
		
		
		
          </tr>";
echo "<tr>

	

	
       
	
     </tr>";


echo "
		<tr hidden>
		
		 <td>" . $_SESSION['lang']['departemen'] . "</td>
		<td>:</td>
		<td><select id=bagian style=width:150px; >" . $optbagian . "</select></td>
		
		<td>Upload File</td>
        <td>:</td>
        <td>
			<button class=mybutton onclick='showupload(event)'>Upload Files</button>
		</td>
		 
        <td>Jenis Transaksi Pajak</td>
        <td>:</td>
        <td><select id=jenistransaksi style=width:150px; >" . $optjenispajak . "</select></td>
		
		</tr>
	 ";

echo "<tr>
        <td><input type=hidden id=uangmuka /><input type=hidden id=notransaksi_gr /><input type=hidden id=termin /></td><td></td>
        <td><button class=mybutton onclick=saveht('" . $arr . "')>" . $_SESSION['lang']['save'] . "</button></td>
     </tr>
     </table>";
echo "</fieldset>";
echo "</div>";
CLOSE_BOX();
// <td><button class=mybutton onclick=addDataTable()>".$_SESSION['lang']['save']."</button></td>
// echo"</fieldset>"; 

echo "<div id=detailField style='display:none'>";
echo "<fieldset><legend>" . $_SESSION['lang']['detail'] . "</legend>";
echo "<fieldset>";
echo "<table boder=0>";

echo "</table></fieldset>";
echo "</fieldset>";
echo "</div>";
echo close_body(); ?>