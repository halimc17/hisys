<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript1.2 src='js/keu_cashopname.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language="javascript" src="js/generic.js?v=<?php echo time(); ?>"></script>
<?php
##deklarasi untuk option##
$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$sql = "SELECT distinct kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where 1=1 and kodeorganisasi IN (".getOrgDetail(2).")
ORDER BY induk ASC, kodeorganisasi ASC";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optorg.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$optperiode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$optperiodesch = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT DISTINCT periode FROM " . $dbname . ".keu_cashopnameht order by periode desc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optperiodesch.="<option value='" . $bar['periode'] . "'>" . $bar['periode'] . "</option>";
}

$optakunht = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$arrtipeunit = getOrgDetail(10);
$optakunsch = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun 
WHERE a.noakun LIKE '11101%' AND a.kasbank = 1 AND a.detail = '1' AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . implode("','", $arrtipeunit) . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik IN ('" . implode("','", $arrtipeunit) . "')))) GROUP BY a.noakun";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optakunsch .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

$optminggu = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$optminggusch = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrminggusch = array(
    "1" => "1",
    "2" => "2",
    "3" => "3",
    "4" => "4",
    "5" => "5",
);
foreach ($arrminggusch as $key => $val) {
    $optminggusch .= "<option value='".$key."'>" . $val . "</option>";
}

##HEADER UNTUK BUAT BARU SAMA LIST-->
OPEN_BOX('','<span class=judul>'.getMenu('keu_cashopname').'</span>');
echo"<div id=action_list>"; //buka div
echo"<table>
    <tr valign=middle>

    <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
        <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "
    </td>

    <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
        <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
    </td>
    <td>
        <fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
        <table>
            <tr>
                <td>" . $_SESSION['lang']['notransaksi'] . "</td> 
                <td>:</td>
                <td>
                    <input type='text' class='myinputtext' style='width:115px;' id='notranssch'>
                </td>

                <td>" . $_SESSION['lang']['unit'] . "</td> 
                <td>:</td>
                <td>
                    <select class='select2' style='width:115px' id='unitsch'>".$optorg."</select>
                </td>

                <td>" . $_SESSION['lang']['periode'] . "</td> 
                <td>:</td>
                <td>
                    <select class='select2' style='width:115px' id='periodesch'>".$optperiodesch."</select>
                </td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['noakun'] . "</td> 
                <td>:</td>
                <td>
                    <select class='select2' style='width:115px' id='noakunsch'>".$optakunsch."</select>
                </td>

                <td>Minggu Ke</td> 
                <td>:</td>
                <td>
                    <select class='select2' style='width:115px' id='minggusch'>".$optminggusch."</select>
                </td>
            </tr>
";

echo"<tr>
    <td><td>
    <td>
        <button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
    </td
</tr></table>";
echo"</fieldset></td>";
echo "</tr>
</table> ";

CLOSE_BOX();
echo "</div>"; //tutup div
##UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->


echo"
<div id=listData style=display:block>"; //buka list data
OPEN_BOX(); //<div style=overflow:scroll>
//<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%
echo "<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:900px>
    <thead>
        <tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['notransaksi'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['unit'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['periode'] . "</td>
            <td align=center rowspan='2'>Minggu Ke</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['noakun'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['posted'] . "</td>
            <td align=center rowspan='2' colspan='4'>" . $_SESSION['lang']['action'] . "</td>
        </tr> 
    </thead>
    <tbody id=contain> 
        <script>loaddata(0)</script>
    </tbody>
    <tfoot id=footData>
    </tfoot>
</table>";
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
        <td>" . $_SESSION['lang']['notransaksi'] . "</td> 
        <td>:</td>
        <td>
            <input type='text' class='myinputtext' style='width:150px;' id='notrans' disabled>
        </td>
    </tr>

    <tr>
        <td>" . $_SESSION['lang']['unit'] . "</td> 
        <td>:</td>
        <td>
            <select class='select2' style='width:150px' id='unit' onchange=\"getPeriode();\">".$optorg."</select>
        </td>
    </tr>

    <tr>
        <td>" . $_SESSION['lang']['periode'] . "</td> 
        <td>:</td>
        <td>
            <select class='select2' style='width:150px' id='periode' onchange=\"getMinggu();\">".$optperiode."</select>
        </td>
    </tr>

    <tr>
        <td>" . $_SESSION['lang']['noakun'] . "</td> 
        <td>:</td>
        <td>
            <select class='select2' style='width:150px' id='noakun'>".$optakunht."</select>
        </td>
    </tr>

    <tr>
        <td>Minggu Ke</td> 
        <td>:</td>
        <td>
            <select class='select2' style='width:150px' id='minggu'>".$optminggu."</select>
        </td>
    </tr>

	<tr>
        <td colspan=2></td>
        <td>
            <button id=tomboldetail class=mybutton onclick=\"saveHeader()\">" . $_SESSION['lang']['save'] . "</button>
            <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
        </td>
        <input type=hidden id=method value='insertHeader'>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";



echo"
<div id=detail style=display:none>"; //buka list data
OPEN_BOX();

CLOSE_BOX();
echo"</div>";




echo close_body();
?>