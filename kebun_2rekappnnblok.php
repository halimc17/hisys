<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
    function lihatdetail(blok, tgl, tipe, ev) {
        param = 'tipe=' + tipe + '&blok=' + blok + '&tgl=' + tgl;
        tujuan = 'kebun_slave_2rekappnnblok_detail.php' + "?" + param;
        width = '500';
        height = '200';
        content = "<fieldset style='height:93%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
        showDialog1('Detail Transaksi' + blok, content, width, height, ev);
    }

    function changediv(unit) {
        param = 'unit=' + unit.value;

        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //=== Success Response
                        document.getElementById('divisi').innerHTML = con.responseText;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }

        post_response_text('kebun_slave_2rekappnnblok_detail.php?proses=changediv', param, respon);
    }

    function getmark(id) {
        dis = document.getElementById(id).style.backgroundColor;
        if (dis != '') {
            document.getElementById(id).style.backgroundColor = "";
        } else {
            document.getElementById(id).style.backgroundColor = "cyan";
        }
    }
</script>

<?
OPEN_BOX('', '<span class=judul>' . getMenu('kebun_2rekappnnblok') . '</span><br>');

$optOrg = "";
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $where = "";
} else if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
    $where = " and induk = '" . $_SESSION['empl']['kodeorganisasi'] . "'";
} else {
    $where = " and kodeorganisasi = '" . $_SESSION['empl']['lokasitugas'] . "'";
}
// $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." and tipe='KEBUN' order by namaorganisasi asc ";
// $res=fetchdata($str);
// foreach($res as $bar){
// 	$optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// }

$optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optorgsch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach (getOrgDetail(23) as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
    $d = $induk[$key];
    if ($d != $n) {
        $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
        $optOrg .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
        $optorgsch .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
    }
    $optOrg .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
    $optorgsch .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
    $n = $d;
    if ($d != $n) {
        $optOrg .= "</optgroup>";
        $optorgsch .= "</optgroup>";
    }
}

$optDiv = "";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='" . $_SESSION['empl']['lokasitugas'] . "' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optDiv .= "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
while ($bar = $res->fetch()) {
    $i = "";
    if ($bar['kodeorganisasi'] == $_SESSION['empl']['subbagian']) {
        $i = "selected";
    }
    $optDiv .= "<option value=" . $bar['kodeorganisasi'] . " " . $i . ">" . $bar['namaorganisasi'] . "</option>";
}


$arr = "##kdorg##tgl1##tgl2##divisi";
echo "<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg style=\"width:175px;\" onchange='changediv(this)'>" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=divisi style=\"width:175px;\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10'  readonly>
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10'  readonly></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2rekappnnblok','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2rekappnnblok.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<div id='printContainer' class='table-scroll' style='min-height:400px;max-width:100%;' ></div>";
CLOSE_BOX();
echo close_body();
?>