<?php
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script type="text/javascript" src="js/vhc_2upahPremiOperatorHelper.js" />
</script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownAutoWidth: true
        });
    });
</script>
<style type="text/css">
    #contain {
        --vhc-summary-height: 0px;
    }

    #contain .vhc-summary-freeze {
        position: sticky;
        top: 0;
        z-index: 30;
        background: #ffffff;
        padding-bottom: 10px;
    }

    #contain .vhc-summary-freeze table {
        width: 100%;
        margin-bottom: 0 !important;
    }

    #contain .vhc-summary-freeze thead,
    #contain .vhc-summary-freeze thead tr,
    #contain .vhc-summary-freeze thead th {
        position: static !important;
        top: auto !important;
    }

    #contain table.vhc-data-table thead {
        position: sticky;
        top: var(--vhc-summary-height);
        z-index: 20;
    }

    #contain table.vhc-data-table thead th {
        position: sticky !important;
        top: var(--vhc-summary-height) !important;
        z-index: 20;
    }
</style>
<div id="action_list">
    <?php

    $optKodeorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
    $n = '';
    foreach (getOrgDetail(18) as $key => $val) {
        $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi', "kodeorganisasi='" . $key . "'");
        $d = $induk[$key];
        if ($d != $n) {
            if ($n != '') {
                $optKodeorg .= "</optgroup>";
            }
            $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
            $optKodeorg .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
        }
        $optKodeorg .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
        $n = $d;
    }
    if ($n != '') {
        $optKodeorg .= "</optgroup>";
    }

    $iAlokasi = "select distinct(alokasibiaya) as alokasibiaya from " . $dbname . ".vhc_rundt where alokasibiaya not like 'AK-%' order by alokasibiaya";
    $nAlokasi = $owlPDO->query($iAlokasi) or die(print " Gagal: " . PDOException::getMessage());
    $nAlokasi->setFetchMode(PDO::FETCH_ASSOC);
    while ($dAlokasi = $nAlokasi->fetch()) {
        $optLokasi .= "<option value='" . $dAlokasi['alokasibiaya'] . "'>" . $dAlokasi['alokasibiaya'] . " - " . getNamaOrg($dAlokasi['alokasibiaya']) . "</option>";
    }

    $sjnskrj = "select * from " . $dbname . ".vhc_kegiatan where tipe='traksi' order by noakun,kodekegiatan asc";
    $optAkun = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
    $res = $owlPDO->query($sjnskrj) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $n = '';
    while ($rjnskrj = $res->fetch()) {
        $d = $rjnskrj['noakun'];
        if ($d != $n) {
            if ($n != '') {
                $optAkun .= "</optgroup>";
            }
            $nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $d . "'");
            $optAkun .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
        }
        $optAkun .= "<option value=" . $rjnskrj['kodekegiatan'] . ">" . $rjnskrj['kodekegiatan'] . " - " . $rjnskrj['namakegiatan'] . "</option>";
        $n = $d;
    }
    if ($n != '') {
        $optAkun .= "</optgroup>";
    }

    OPEN_BOX('', '<span class=judul>' . getMenu('vhc_2upahPremiOperatorHelper') . '</span><br>');
    echo "<fieldset style=float:left><table><legend>" . $_SESSION['lang']['pilihdata'] . "</legend> ";
    echo "
        <tr>
            <td>" . $_SESSION['lang']['unitkerja'] . "</td>
            <td>:</td>
            <td><select class='select2' id=company_id name=company_id onChange=get_jnsVhc() style=width:200px>" . $optKodeorg . "</select></td>
        </tr>
        <tr>
            <td>" . $_SESSION['lang']['jenisvch'] . "</td>
            <td>:</td>
            <td><select class='select2' id=jnsVhc name=jnsVhc onchange=\"getKdVhc()\" style=width:200px><option value=''>" . $_SESSION['lang']['all'] . "</option></select></td>
        </tr>
        <tr>
            <td>" . $_SESSION['lang']['kodevhc'] . "</td>
            <td>:</td>
            <td><select class='select2' id=kdVhc name=kdVhc style=width:200px><option value=''>" . $_SESSION['lang']['all'] . "</option></select></td>
        </tr>
        <tr>
            <td>" . $_SESSION['lang']['alokasi'] . "</td>
            <td>:</td>
            <td><select class='select2' id=alokasi name=alokasi style=width:200px><option value=''>" . $_SESSION['lang']['all'] . "</option>" . $optLokasi . "</select></td>
        </tr>
        <tr>
            <td>" . $_SESSION['lang']['tanggal'] . "</td>
            <td>:</td>
            <td><input type=\"text\" class=\"myinputtext\" id=\"tglAwal\" name=\"tglAwal\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\" maxlength=\"10\" style=\"width:82px;\" readonly/> S/D
            <input type=\"text\" class=\"myinputtext\" id=\"tglAkhir\" name=\"tglAkhir\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\" maxlength=\"10\" style=\"width:82px;\" readonly/></td>
        </tr>
        <tr>
            <td>" . $_SESSION['lang']['noakun'] . " & " . $_SESSION['lang']['pekerjaan'] . " </td>
            <td>:</td>
            <td><select class='select2' id=akun name=akun style=width:200px>" . $optAkun . "</select></td>
        </tr>
        <tr>
            <td>Tipe Laporan</td>
            <td>:</td>
            <td><select class='select2' id=tipeReport name=tipeReport style=width:200px>
                <option value=rekap>Rekap</option>
                <option value=detail>Detail</option>
            </select></td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td>
                <button class=mybutton onclick=save_pil()>" . $_SESSION['lang']['preview'] . "</button>
                <button class=mybutton onclick=dataKeExcel(event,'vhc_slave_2upahPremiOperatorHelper.php')>" . $_SESSION['lang']['excel'] . "</button>
                <button class=mybutton onclick=ganti_pil()>" . $_SESSION['lang']['cancel'] . "</button>
            </td>
        </tr>";

    echo "</table></fieldset> ";
    ?>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>
<div id="cari_barang" name="cari_barang"></div>
<div id="hasil_cari" name="hasil_cari"></div>
<div id="contain" class='table-scroll' style='overflow:auto;height:550px;max-width:100%'></div>
<?php
CLOSE_BOX();
echo close_body();
?>