<?
require_once 'master_validation.php';
require_once 'lib/nangkoelib.php';
require_once 'config/connection.php';
require_once 'lib/zLib.php';
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="JavaScript1.2" src="js/formReport.js"></script>
<script language=javascript src='js/keu_2kasharianv2.js?v=<?php echo time(); ?>'></script>

<?
require_once 'master_mainMenu.php';

$frm[0] = '';
$frm[1] = '';

OPEN_BOX('', '<span class=judul>' . getMenu('keu_2kasharianv2') . '</span><br>');
$optunit = $optakunkk = $optakun = $optPer = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optunit2 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optbank = "<option value=''></option>";
$optrek = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

// $optOrg=array();
// $optOrg = getOrgDetail(10);
// ksort($optOrg);

// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
// $where.="and kodeorganisasi in ('".implode("','",$optOrg)."')";
// }else{
// $where.= "and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
// }

// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$where." order by namaorganisasi asc ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// $optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
// }

$arrunit = array();
$arrunit = getOrgDetail(1);
foreach ($arrunit as $val => $nama) {
    if ($val == $_SESSION['empl']['lokasitugas']) {
        $optunit .= "<option value='" . $val . "' selected>" . $val . " - " . $nama . "</option>";
        $optunit2 .= "<option value='" . $val . "' selected>" . $val . " - " . $nama . "</option>";
    } else {
        $optunit .= "<option value='" . $val . "' >" . $val . " - " . $nama . "</option>";
        $optunit2 .= "<option value='" . $val . "' >" . $val . " - " . $nama . "</option>";
    }
    $arrkodeunit[$val] = $val;
}

// $pemilik = orgDetailuser($_SESSION['standard']['username'], '2');

// if ($_SESSION['empl']['lokasitugas'] != 'WTHO') {
//     $whrKas = "and (pemilik='GLOBAL' or pemilik IN (" . $pemilik . "))";
//     // $whrKas = "and (pemilik='".$_SESSION['empl']['lokasitugas']."')";
//     // $whrKas = "and pemilik IN (" . $pemilik . ")";
// }

// $str = "select * from " . $dbname . ".keu_5akun where kasbank=1 and (noakun like '1110%') and level ='5' " . $whrKas . "";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $res->fetch()) {
//     $optakun .= "<option value=" . $bar['noakun'] . ">" . $bar['namaakun'] . "</option>";
// }

$arrtipeunit = getOrgDetail(10);
$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbank = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . implode("','", $arrtipeunit) . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$_SESSION['empl']['tipelokasitugas']}' OR a.pemilik = '{$_SESSION['empl']['lokasitugas']}'))) GROUP BY a.noakun";
$res = fetchdata($str);
foreach ($res as $bar) {
    $optakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

$optposting = $optpembayaran = $opttipetransaksi = "<option value=''>Seluruhnya</option>";
$optposting .= "<option value=0>Belum Diajukan</option>";
$optposting .= "<option value=1>Disetujui</option>";
$optposting .= "<option value=3>Ditolak</option>";
$optposting .= "<option value=9>Proses Persetujuan</option>";

$opttipetransaksi .= "<option value='M'>Masuk</option>";
$opttipetransaksi .= "<option value='K'>Keluar</option>";

$arrtipe = array('0' => 'Belum Dibayar', '1' => 'Sudah Dibayar');
foreach ($arrtipe as $key => $data) {
    $optpembayaran .= "<option value='" . $key . "'>" . $data . "</option>";
}

$arrgroup = array('0' => 'None', '1' => 'Grouping');
foreach ($arrgroup as $key => $data) {
    $optgroup .= "<option value='" . $key . "'>" . $data . "</option>";
}

$frm[0] .= "
	<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['novoucher'] . "</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' id='tgl1' style='text-align:center' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
						s/d
						<input type='text' class='myinputtext' id='tgl2' style='text-align:center' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
					</td>

					<td style='padding-left:10px'>" . $_SESSION['lang']['noakun'] . "</td>
                    <td>:</td>
                    <td>
						<select id=noakun style=\"width:158px;\" onchange=getbank()>" . $optakun . "</select>
					</td>

					<td style='padding-left:10px'>" . $_SESSION['lang']['posting'] . " / " . $_SESSION['lang']['pembayaran'] . "</td>
					<td>:</td>
					<td>
						<select id=pembayaran style=\"width:150px;\">'" . $optpembayaran . "'</select>
					</td>
				</tr>

				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td>
						<select id=unit style=\"width:158px;\" onchange=clearopt()>" . $optunit . "</select>
					</td>

					<td style='padding-left:10px'>" . $_SESSION['lang']['bank'] . "</td>
                    <td>:</td>
                    <td>
						<select id=bank style=\"width:158px;\" onchange=getgroup()>" . $optbank . "</select>
					</td>

					<td hidden style='padding-left:10px;'>" . $_SESSION['lang']['tipe'] . "</td>
                    <td hidden>:</td>
                    <td hidden>
						<select id=group style=\"width:150px;\">" . $optgroup . "</select>
					</td>
                </tr>

                <tr>
                    <td colspan=2></td>
					<td>
						<button id=preview class=mybutton onclick=preview()>" . $_SESSION['lang']['preview'] . "</button>
						<button id=excel class=mybutton onclick=excel('event')>" . $_SESSION['lang']['excel'] . "</button>
                        <button id=pdf class=mybutton onclick=pdf('event')>" . $_SESSION['lang']['pdf'] . "</button>
						<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
$frm[0] .= "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div class=table-scroll>
<div id='printContainer' style='overflow:auto;height:340px;width:100%'; >
</div>
</div></fieldset>";

$frm[1] .= "
    <fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>

                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "  " . $_SESSION['lang']['novoucher'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1sum' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
                    s/d
                    <input type='text' class='myinputtext' id='tgl2sum' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>
                </tr>

                 <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=unitsum style=\"width:158px;\" onchange=getrekening()>" . $optunit2 . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['rekening'] . "</td>
                    <td>:</td>
                    <td><select id=rek style=\"width:158px;\">" . $optrek . "</select></td>
                </tr>

                <tr>
                    <td><td><td>
                   <button id=preview class=mybutton onclick=previewsum()>" . $_SESSION['lang']['preview'] . "</button>
                    <button id=excel class=mybutton onclick=excelsum('event')>" . $_SESSION['lang']['excel'] . "</button>
                    <button id=excel class=mybutton onclick=pdfsum('event')>" . $_SESSION['lang']['pdf'] . "</button>
                    <button id=batal class=mybutton onclick=cancelsum()>" . $_SESSION['lang']['cancel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";

$frm[1] .= "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainersum' style='overflow:auto;height:325px;width:100%';>
</div></fieldset>";

// $frm[2] .= "
//     <fieldset style='float:left;'>
//         <legend>Form</legend>
//             <table border=0 cellpadding=1 cellspacing=1>

//                 <tr>
//                     <td>" . $_SESSION['lang']['tanggal'] . "  " . $_SESSION['lang']['novoucher'] . "</td>
//                     <td>:</td>
//                     <td><input type='text' class='myinputtext' id='tglvoc1kas' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
//                     s/d
//                     <input type='text' class='myinputtext' id='tglvoc2kas' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>

//                     <td>" . $_SESSION['lang']['unit'] . "</td>
//                     <td>:</td>
//                     <td><select id=unitkas style=\"width:150px;\">" . $optunit . "</select></td>

//                 <td>" . $_SESSION['lang']['pembayaran'] . "</td>
//                 <td>:</td>
//                 <td>
//                 <select id=pembayarankas style=\"width:150px;\">'" . $optpembayaran . "'</select>
//                 </tr>

//                   <tr>
//                     <td>" . $_SESSION['lang']['tanggalinput'] . "</td>
//                     <td>:</td>
//                     <td><input type='text' class='myinputtext' id='tglinput1kas' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
//                     s/d
//                     <input type='text' class='myinputtext' id='tglinput2kas' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>

//                     <td>" . $_SESSION['lang']['noakun'] . "</td>
//                     <td>:</td>
//                     <td><select id=noakunkas style=\"width:150px;\">" . $optakun . "</select></td>

//                     <td>" . $_SESSION['lang']['status'] . "</td>
//                     <td>:</td>
//                     <td><select id=postingkas style=\"width:150px;\">" . $optposting . "</select></td>
//                 </tr>

//                 <tr>
//                      <td>" . $_SESSION['lang']['tipetransaksi'] . "</td>
//                         <td>:</td>
//                         <td><select id=tipetransaksikas style=\"width:150px;\">" . $opttipetransaksi . "</select></td>
//                 </tr>
//                 <tr>
//                      <td><td>
//                 </tr>

//                 <tr>
//                      <td><td>
//                 </tr>

//                 <tr>
//                     <td><td><td colspan=9>
//                    <button id=preview class=mybutton onclick=previewkas()>" . $_SESSION['lang']['preview'] . "</button>
//                     <button id=excel class=mybutton onclick=excelkas('event')>" . $_SESSION['lang']['excel'] . "</button>
//                     <button id=excel class=mybutton onclick=pdfkas('event')>" . $_SESSION['lang']['pdf'] . "</button>
//                     <button id=batal class=mybutton onclick=cancelkas()>" . $_SESSION['lang']['cancel'] . "</button>
//                     </td>
//                 </tr>
//             </table>
// </fieldset>";

// $frm[2] .= "
// <fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
// <div id='printContainerkas' style='overflow:auto;height:315px;width:100%'; >
// </div></fieldset>";

$hfrm[0] = $_SESSION['lang']['kas'] . ' & ' . $_SESSION['lang']['bank'];
$hfrm[1] = $_SESSION['lang']['summary'] . ' ' . $_SESSION['lang']['kas'] . ' ' . $_SESSION['lang']['bank'] . ' / ' . $_SESSION['lang']['rekening'];
// $hfrm[2]='Kas';

drawTab('FRM', $hfrm, $frm, 250, '100%');

CLOSE_BOX();
echo close_body();

?>