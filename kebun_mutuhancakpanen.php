<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript1.2 src='js/kebun_mutuhancakpanen.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language="javascript" src="js/generic.js?v=<?php echo time(); ?>"></script>
<link rel="stylesheet" type="text/css" href="lib/MagnificPopup/magnific-popup.css">
<script type="text/javascript" src="lib/MagnificPopup/jquery.magnific-popup.js"></script>
<script>
    function popupimage() {
        alertify.closeAll();
        $('.popup-img').magnificPopup({
            type: 'image',
            removalDelay: 300,
            mainClass: 'mfp-fade',
            mainClass: 'mfp-fade',
            gallery: {
                enabled: true
            },
            zoom: {
                enabled: true,
                duration: 300,
                easing: 'ease-in-out',
                opener: function(openerElement) {
                    return openerElement.is('img') ? openerElement : openerElement.find('img');
                }
            },
        });
    }
</script>
<?php
##deklarasi untuk option##
##mandor: hanya unit yang boleh diakses user dan yang belum keluar
$startKel = ($_SESSION['org']['period']['start'] != '') ? $_SESSION['org']['period']['start'] : date('Ymd');
$optKary = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT karyawanid, nik, namakaryawan,subbagian FROM " . $dbname . ".datakaryawan where 1=1 and kodejabatan IN ('6','7','8') and lokasitugas in (" . getOrgDetail(2) . ") and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '" . $startKel . "') order by subbagian asc, namakaryawan asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
$group = null;
while ($bar = $qry->fetch()) {
    $div = $bar['subbagian'];
    if ($div !== $group) {
        if ($group !== null) {
            $optKary .= "</optgroup>";
        }
        $optKary .= "<optgroup label='" . getNamaOrg($div) . "'>";
        $group = $div;
    }
    $optKary .= "<option value='" . $bar['karyawanid'] . "'>" . $bar['nik'] . " - " . $bar['namakaryawan'] . "</option>";
}
if ($group !== null) {
    $optKary .= "</optgroup>";
}

##filter unit & periode untuk list (data umumnya hasil download dari mobile)
$optUnitSch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$resUnit = fetchdata("select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' and kodeorganisasi in (" . getOrgDetail(2) . ") order by kodeorganisasi");
foreach ($resUnit as $vu) {
    $optUnitSch .= "<option value='" . $vu['kodeorganisasi'] . "'>" . $vu['kodeorganisasi'] . " - " . $vu['namaorganisasi'] . "</option>";
}
$periodeNow = date('Y-m');
$listPeriode = array();
$resPeriode = fetchdata("select distinct date_format(tanggal,'%Y-%m') as periode from " . $dbname . ".kebun_rekapmutuhancakpanen order by periode desc");
foreach ($resPeriode as $vp) {
    $listPeriode[$vp['periode']] = $vp['periode'];
}
$listPeriode[$periodeNow] = $periodeNow;
krsort($listPeriode);
$optPeriodeSch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach ($listPeriode as $vp) {
    $sel = ($vp == $periodeNow) ? " selected" : "";
    $optPeriodeSch .= "<option value='" . $vp . "'" . $sel . ">" . $vp . "</option>";
}


##HEADER UNTUK BUAT BARU SAMA LIST-->
OPEN_BOX('', '<span class=judul>' . getMenu('kebun_mutuhancakpanen') . '</span>');
echo "<div id=action_list>"; //buka div
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
						<td>" . $_SESSION['lang']['mandorpanen'] . "</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=karyawansch onkeypress='return tanpa_kutip(event)' style=\"width:194px;\"/></td>
						<td style='padding-left:20px;'>" . $_SESSION['lang']['tanggal'] . "</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=tglsch onmousemove=setCalendar(this.id) onkeypress=return false; style=\"width:194px;\" readonly/></td>
					</tr>
					<tr>
						<td>Unit</td>
						<td>:</td>
						<td><select id=unitsch style=\"width:200px;\" onchange=loaddata(0)>" . $optUnitSch . "</select></td>
						<td style='padding-left:20px;'>" . $_SESSION['lang']['periode'] . "</td>
						<td>:</td>
						<td><select id=periodesch data-default='" . $periodeNow . "' style=\"width:200px;\" onchange=loaddata(0)>" . $optPeriodeSch . "</select></td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td colspan=4>
							<button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
							<button class=mybutton onclick=exportPdf()>PDF</button>
							<button class=mybutton onclick=exportExcel()>Excel</button>
						</td>
					</tr></table>";
echo "</fieldset></td>";
echo "</tr>
</table> ";

CLOSE_BOX();
echo "</div>"; //tutup div
##UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->


echo "
<div id=listData style=display:block>"; //buka list data
OPEN_BOX(); //<div style=overflow:scroll>
//<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%
echo "
	
            <table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:900px>
            <thead>
                <tr class=rowheader>
					<td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
					<td align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align=center rowspan='2'>" . $_SESSION['lang']['divisi'] . "</td>
					<td align=center rowspan='2'>" . $_SESSION['lang']['mandorpanen'] . "</td>
					<td align=center rowspan='2'>" . $_SESSION['lang']['posted'] . "</td>
					<td align=center rowspan='2' colspan='4'>" . $_SESSION['lang']['action'] . "</td>
                </tr> 
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
             ";
CLOSE_BOX();
echo "</div>"; //tutup list data
##UNTUK BUAT FORM INPUT HEADER-->


echo "<div id=header style=display:none>"; //buka diff
OPEN_BOX();
echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
     <tr>
            <td>" . $_SESSION['lang']['tanggal'] . "</td> 
            <td>:</td>
            <td><input type=text style=\"width:145px;\" class=myinputtext id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/></td>
    </tr>
     <tr>
            <td>" . $_SESSION['lang']['mandorpanen'] . "</td> 
            <td>:</td>
            <td><select class=select2 style=\"width:150px;\" id=namamandor>" . $optKary . "</select></td>
    </tr>

	<tr>
            <td colspan=2></td>
            <td>
                <button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['save'] . "</button>
                <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
            </td>
            <input type=hidden id=method value='insert'>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo "</div>";



echo "
<div id=detail style=display:none>"; //buka list data
OPEN_BOX();

CLOSE_BOX();
echo "</div>";




echo close_body();
?>