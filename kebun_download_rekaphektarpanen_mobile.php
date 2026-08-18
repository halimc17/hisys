<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript1.2 src='js/kebun_download_rekaphektarpanen_mobile.js?v=<?php echo time(); ?>'></script>
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
$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optprd = "<option value=''>&nbsp;</option>";
$where = "";
if ($_SESSION['empl']['subbagian'] != "") {
    $where .= " and induk='" . $_SESSION['empl']['subbagian'] . "'";
} else {
    $where .= " and induk like '" . $_SESSION['empl']['lokasitugas'] . "%'";
}
$sql = "SELECT distinct indukblok,namaindukblok FROM " . $dbname . ".organisasi where 1=1 and tipe='BLOK' " . $where . "";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $div = substr($bar['indukblok'], 0, 6);
    if ($div != $group) {
        $optorg .= "<optgroup label='" . getNamaOrg($div) . "'>";
    }

    $optorg .= "<option value=" . $bar['indukblok'] . ">" . $bar['indukblok'] . " - " . $bar['namaindukblok'] . "</option>";

    $group = $div;
    if ($div != $group) {
        $optorg . "</optgroup>";
    }
}

$optKary = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT karyawanid, nik, namakaryawan,subbagian FROM " . $dbname . ".datakaryawan where 1=1 and kodejabatan IN ('7') order by subbagian asc, namakaryawan asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $div = $bar['subbagian'];
    if ($div != $group) {
        $optKary .= "<optgroup label='" . getNamaOrg($div) . "'>";
    }
    if ($nik == $bar['karyawanid']) {
        $optKary .= "<option value=" . $bar['karyawanid'] . " selected>" . $bar['nik'] . " - " . $bar['namakaryawan'] . "</option>";
    } else {
        $optKary .= "<option value=" . $bar['karyawanid'] . ">" . $bar['nik'] . " - " . $bar['namakaryawan'] . "</option>";
    }

    $group = $div;
    if ($div != $group) {
        $optKary . "</optgroup>";
    }
}

for ($x = 0; $x < 20; $x++) {
    $dt = mktime(0, 0, 0, date('m') - $x, 12, date('Y'));
    if (date("Y-m", $dt) == date("Y-m")) {
        $select = "selected";
    } else {
        $select = "";
    }

    $optprd .= "<option value=" . date("Y-m", $dt) . " " . $select . ">" . date("m-Y", $dt) . "</option>";
}

##HEADER UNTUK BUAT BARU SAMA LIST-->
OPEN_BOX('', '<span class=judul>' . getMenu('kebun_download_rekaphektarpanen_mobile') . '</span>');
echo "<div id=action_list>"; //buka div
echo "<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
            <fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
	
         <table>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td> 
					<td>:</td>
					<td>
                        <select class=select2 id=periodesch onchange='loaddata(0)' style=\"width:130px;\">" . $optprd . "</select>
                    </td>
				</tr>";

echo "<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
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
					<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
					<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align=center>" . $_SESSION['lang']['divisi'] . "</td>
					<td align=center>" . $_SESSION['lang']['mandorpanen'] . "</td>
					<td align=center>" . $_SESSION['lang']['kodeblok'] . "</td>
					<td align=center colspan='1'>" . $_SESSION['lang']['action'] . "</td>
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